# Yeni pencerede devam

Yeni bir Claude sohbeti açınca aşağıdaki metni kopyalayıp ilk mesaj olarak gönderin. Sohbeti bilgisayarınıza bağlı açın (Claude masaüstü uygulaması).

```
Cardino projesine devam ediyoruz. GitHub'daki anketci54-coder/cardino deposunu ekle,
CLAUDE.md ve docs/DEVAM.md dosyalarını oku. Yapılacaklar listesi:
https://claude.ai/artifact/LpLmJiRu7VakXM989uo5mW
VPS'e bilgisayarımdan cardino_vps SSH anahtarıyla bağlan. Sıradaki adımdan devam et;
biten adımları listede işaretle, GitHub ve sunucuyu eşit tut.
```

## Şu an neredeyiz

- Son güncelleme: 2026-11-24
- Bitti: Faz 0 altyapı (alan adı, DNS, SSL, PHP 8.4, MariaDB, Laravel 13, GitHub ↔ sunucu eşit, `cardino-guncelle`, günlük yedek `cardino-yedek`, fail2ban, otomatik güvenlik güncellemeleri).
- Bitti (Faz 1): kullanıcı adıyla Türkçe giriş, galeriler + roller (admin, sahip, personel), galeri veri ayrımı (`BelongsToGallery` global scope, test edildi), araç kartı (liste/arama/filtre, ekle, düzenle, detay, sil), şasi numarasından menşe tahmini, 5 yaş sınırı uyarısı.
- Bitti (Faz 1): **maliyet kalemleri + çok para birimi + satış/kâr**. Araç detayında 16 masraf kategorisi, her biri kendi para biriminde + kur + GBP karşılığı; toplam maliyet, satış ve kâr-zarar panelleri. `shipping_route` (doğrudan / Mersin aktarmalı). Test edildi: 2.431.000 JPY alış + 2.750 USD navlun + 180.000 TRY gümrük → toplam £18.773,70; 22.500 GBP satış → kâr £3.726,30 (doğru).
- Bitti (Faz 1): **yönetim ekranı**. Yönetici panelden galeri ve kullanıcı ekler/düzenler (rol, galeri, şifre, aktiflik); `admin` middleware ile korunur, personel 403 alır; yönetici kendini kilitleyemez. Smoke test + Feature testleri ile doğrulandı.
- Bitti (Faz 1): **özet ekranı (panel panosu)**. Elde (satılmamış) araç, bağlı sermaye (GBP), bu ay satış/kâr; durum dağılımı, 5 yaş sınırı yaklaşanlar, son araçlar. `PanelController` ile galeri bazlı. Boş veriyle render HTTP 200 doğrulandı.
- Bitti (Faz 1): **fotoğraf ve belge yükleme**. Araç detayında fotoğraf galerisi + belge listesi (fatura, konşimento, gümrük beyannamesi, açık artırma kağıdı, ruhsat, diğer). Gizli diskte, galeri yetkisiyle sunulur (başka galeri 404). JPG/PNG/WEBP/HEIC/PDF, en çok 15 MB. PHP limitleri `public/.user.ini` (upload 20M, post 24M), nginx 25M. Feature testleri + `deploy/smoke-dosya.sh` ile uçtan uca doğrulandı; test verisi temizlendi (araç/dosya/masraf = 0).
- Komutlar (sunucuda, `/var/www/cardino/app` içinde, `sudo -u www-data php artisan ...`): `cardino:galeri "<ad>"`, `cardino:kullanici <ad> --rol=admin|sahip|personel --galeri=<no> --sifre=...`, `cardino:kur <para> <kur> [--tarih=]`.
- Veri: kullanıcı `admin1` (yönetici), galeri #1 "Pilot Galeri" (gerçek adı Kürşat'tan öğrenilecek). Araç yok. Kurlar: yer tutucu (JPY 0,0052 / USD 0,79 / EUR 0,86 / TRY 0,022) — gerçek kurlar `cardino:kur` ile güncellenecek.
- Kalan Faz 0: yedeğin sunucu dışı kopyası, e-posta gönderimi, SSH şifreli girişi kapatma (Kürşat onayı), www yönlendirmesi (Kürşat).
- Not: Sunucudaki radar_* Docker konteynerleri Cardino'dan bağımsız, nisandan beri "postgres" bulunamadığı için yeniden başlayıp duruyor. Cardino bunlara dokunmaz.
- Sıradaki: Faz 1 kalan tek madde **şifre sıfırlama** (e-posta gönderimi gerektirir — önce Faz 0'daki e-posta altyapısı kurulmalı). Sonra Faz 2 (maliyet hesaplayıcı).
