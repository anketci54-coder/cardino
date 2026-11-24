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
- Bitti: Faz 0 altyapı: alan adı, DNS, SSL, PHP 8.4, MariaDB, Laravel 13, GitHub deposu, sunucu ile GitHub eşit (`cardino-guncelle`), günlük yedek (`cardino-yedek`, 03:30, 14 gün), fail2ban + otomatik güvenlik güncellemeleri.
- Kalan Faz 0: yedeğin sunucu dışına kopyası, e-posta gönderimi (Faz 1 ile), SSH şifreli girişi kapatma (Kürşat onayı), www yönlendirmesi (Kürşat).
- Not: Sunucudaki radar_* Docker konteynerleri Cardino'dan bağımsız, nisandan beri "postgres" bulunamadığı için yeniden başlayıp duruyor. Cardino bunlara dokunmaz.
- Sıradaki: Faz 1, giriş ekranı ve galeri hesabı.
