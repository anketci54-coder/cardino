#!/usr/bin/env bash
# Araç dosya yükleme (fotoğraf + belge) uçtan uca duman testi.
# Kullanım:  URL=https://panel.cardino.space KUL=admin1 SIFRE=... bash deploy/smoke-dosya.sh
# Şifre repoda tutulmaz; çalışma anında env ile verilir.
set -euo pipefail

URL="${URL:-https://panel.cardino.space}"
KUL="${KUL:-admin1}"
SIFRE="${SIFRE:?SIFRE env değişkeni gerekli}"
APP="${APP:-/var/www/cardino/app}"
GALERI="${GALERI:-1}"   # yönetici kullanıcı için galeri no
SASI="SMOKE-$RANDOM"

J="$(mktemp)"
PNG="$(mktemp --suffix=.png)"
PDF="$(mktemp --suffix=.pdf)"
trap 'rm -f "$J" "$PNG" "$PDF"' EXIT

# Küçük geçerli test dosyaları
printf '\x89PNG\r\n\x1a\n\x00\x00\x00\x0dIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x02\x00\x00\x00\x90wS\xde\x00\x00\x00\x0cIDATx\x9cc\xf8\xcf\xc0\x00\x00\x00\x03\x00\x01\x00\x00\x00\x00IEND\xaeB\x60\x82' > "$PNG"
printf '%%PDF-1.1\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%%%EOF\n' > "$PDF"

tok() { curl -s -c "$J" -b "$J" "$1" | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//'; }

# Giriş
T="$(tok "$URL/giris")"
curl -s -c "$J" -b "$J" -o /dev/null -d "_token=$T&username=$KUL&password=$SIFRE" "$URL/giris"

# Araç oluştur, ID'yi Location başlığından al
T="$(tok "$URL/araclar/create")"
LOC="$(curl -s -c "$J" -b "$J" -D - -o /dev/null --data-urlencode "_token=$T" \
  -d "gallery_id=$GALERI&chassis_no=$SASI&make=SmokeTest&model=Dosya&model_year=2022&origin=japonya&status=stokta" \
  "$URL/araclar" | grep -i '^location:' | tr -d '\r' | awk '{print $2}')"
VID="$(echo "$LOC" | grep -o '[0-9]*$')"
echo "Araç: $LOC"

# Fotoğraf yükle
T="$(tok "$LOC")"
curl -s -c "$J" -b "$J" -o /dev/null -F "_token=$T" -F "kind=foto" \
  -F "files[]=@$PNG;type=image/png" "$LOC/dosya"

# Belge yükle
T="$(tok "$LOC")"
curl -s -c "$J" -b "$J" -o /dev/null -F "_token=$T" -F "kind=belge" -F "doc_type=fatura" \
  -F "files[]=@$PDF;type=application/pdf" "$LOC/dosya"

echo "Show sayfası:"
curl -s -c "$J" -b "$J" "$LOC" | grep -oE 'dosya/[0-9]+|Fatura|Fotoğraflar ve belgeler' | sort -u
echo "Diskte:"
ls -1 "$APP/storage/app/private/araclar/$VID/" 2>&1 || echo "  (dizin yok)"
echo "Sunum (ilk dosya):"
FID="$(curl -s -c "$J" -b "$J" "$LOC" | grep -oE 'dosya/[0-9]+' | head -1 | grep -o '[0-9]*')"
curl -s -c "$J" -b "$J" -D - -o /dev/null "$LOC/dosya/$FID" | grep -iE '^(HTTP|content-type)' | tr -d '\r'

# Temizlik: araç + dosyalar + disk (hata olsa da devam et)
set +e
T="$(tok "$LOC/edit")"
curl -s -c "$J" -b "$J" -o /dev/null --data-urlencode "_token=$T" -d "_method=DELETE" "$LOC"
rm -rf "$APP/storage/app/private/araclar/$VID"
( cd "$APP" && sudo -u www-data HOME=/tmp php artisan tinker --execute="\$v=App\\Models\\Vehicle::withTrashed()->where('chassis_no','$SASI')->first(); if(\$v){ \$v->files()->delete(); \$v->costs()->delete(); \$v->forceDelete(); }" >/dev/null 2>&1 )
echo "Temizlendi. ✓"
