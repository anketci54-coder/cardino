# Cardino: çalışma kuralları

Cardino, KKTC oto galerileri için araç yönetim sistemi (araç arşivi, maliyet motoru, takas değerleme). Ürün sahibi Kürşat Akyol; geliştirmeyi Claude yapar. Kürşat ile Türkçe, sade dille konuş.

## Kaynaklar

- Yapılacaklar listesi (tek doğru kaynak): https://claude.ai/artifact/LpLmJiRu7VakXM989uo5mW
- Proje planı: https://claude.ai/artifact/DCxU9AYwsoiaEKkTHohgdY
- Kararlar: `docs/kararlar.md`
- Yeni pencerede devam metni: `docs/DEVAM.md`

## Ortam

- GitHub: anketci54-coder/cardino (main dalı; GitHub ana kaynaktır)
- VPS: Debian 13, 159.195.74.132, root. Kürşat'ın Windows bilgisayarından `ssh -i %USERPROFILE%\.ssh\cardino_vps root@159.195.74.132` ile erişilir (Desktop Commander).
- Uygulama: Laravel 13, PHP 8.4, MariaDB 11.8, nginx; kod `/var/www/cardino/app`
- Adresler: https://panel.cardino.space (uygulama), https://cardino.space
- Veritabanı şifresi sunucuda `/root/cardino-db.txt`; asla okuma, yazdırma, sohbete koyma.

## Çalışma akışı

1. Değişikliği yap, GitHub'a gönder.
2. Sunucuda `cardino-guncelle` çalıştır (GitHub'dan çeker, bağımlılıkları kurar, migration ve önbellek).
3. Siteyi kontrol et.
4. Biten adımı yapılacaklar listesinde işaretle; kalıcı bir karar varsa `docs/kararlar.md`'ye ekle.
5. `docs/DEVAM.md` dosyasındaki "Şu an neredeyiz" bölümünü güncelle.

## Kurallar

- Başka sitelerden izinsiz veri kazıma yok; dış veri yalnızca resmi API veya anlaşmayla.
- Vergi oranları koda gömülmez; yönetici panelindeki tablodan okunur.
- Her galerinin verisi ayrıdır; sorgular her zaman galeriye göre filtrelenir.
- Sunucudaki diğer uygulamalara dokunma.
