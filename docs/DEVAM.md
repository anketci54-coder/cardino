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
- Komutlar (sunucuda, `/var/www/cardino/app` içinde, `sudo -u www-data php artisan ...`): `cardino:galeri "<ad>"`, `cardino:kullanici <ad> --rol=admin|sahip|personel --galeri=<no> --sifre=...`.
- Veri: kullanıcı `admin1` (yönetici), galeri #1 "Pilot Galeri" (gerçek adı Kürşat'tan öğrenilecek). Araç yok.
- Kalan Faz 0: yedeğin sunucu dışı kopyası, e-posta gönderimi, SSH şifreli girişi kapatma (Kürşat onayı), www yönlendirmesi (Kürşat).
- Not: Sunucudaki radar_* Docker konteynerleri Cardino'dan bağımsız, nisandan beri "postgres" bulunamadığı için yeniden başlayıp duruyor. Cardino bunlara dokunmaz.
- Sıradaki: Faz 1, maliyet kalemleri + döviz kuru, sonra satış kaydı ve kâr-zarar.
