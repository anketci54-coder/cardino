#!/usr/bin/env bash
# Yönetim ekranı duman testi: galeri/kullanıcı ekleme + yetki (personel 403).
# Kullanım:  URL=https://panel.cardino.space KUL=admin1 SIFRE=... bash deploy/smoke-yonetim.sh
set -uo pipefail

URL="${URL:-https://panel.cardino.space}"
KUL="${KUL:-admin1}"
SIFRE="${SIFRE:?SIFRE env değişkeni gerekli}"
APP="${APP:-/var/www/cardino/app}"
GALERI="${GALERI:-1}"            # yeni kullanıcının bağlanacağı mevcut galeri
GAD="YONTEST-$RANDOM"            # yeni galeri adı
KAD="yontest$RANDOM"             # yeni kullanıcı adı
KSIFRE="test12345"              # test kullanıcısının şifresi (test değeri)

JA="$(mktemp)"; JB="$(mktemp)"
trap 'rm -f "$JA" "$JB"' EXIT
tok() { curl -s -c "$1" -b "$1" "$2" | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//'; }

# Yönetici girişi
T="$(tok "$JA" "$URL/giris")"
curl -s -c "$JA" -b "$JA" -o /dev/null -d "_token=$T&username=$KUL&password=$SIFRE" "$URL/giris"

# Galeri ekle
T="$(tok "$JA" "$URL/yonetim")"
curl -s -c "$JA" -b "$JA" -o /dev/null --data-urlencode "_token=$T" -d "name=$GAD&city=Test" "$URL/yonetim/galeri"

# Kullanıcı ekle (personel, mevcut galeriye)
T="$(tok "$JA" "$URL/yonetim")"
curl -s -c "$JA" -b "$JA" -o /dev/null --data-urlencode "_token=$T" \
  -d "username=$KAD&name=Deneme+Personel&role=personel&gallery_id=$GALERI&password=$KSIFRE" "$URL/yonetim/kullanici"

echo "Yönetim listesinde:"
curl -s -c "$JA" -b "$JA" "$URL/yonetim" | grep -oE "$GAD|$KAD" | sort -u

# Yetki: yeni personel /yonetim'e giremez (403 beklenir)
T="$(tok "$JB" "$URL/giris")"
curl -s -c "$JB" -b "$JB" -o /dev/null -d "_token=$T&username=$KAD&password=$KSIFRE" "$URL/giris"
echo -n "Personel /yonetim HTTP: "
curl -s -c "$JB" -b "$JB" -o /dev/null -w "%{http_code}\n" "$URL/yonetim"

# Temizlik
( cd "$APP" && sudo -u www-data HOME=/tmp php artisan tinker --execute="App\\Models\\User::where('username','$KAD')->delete(); App\\Models\\Gallery::where('name','$GAD')->delete();" >/dev/null 2>&1 )
echo "Temizlendi. ✓"
