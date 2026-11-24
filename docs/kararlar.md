# Kararlar

| Tarih | Karar |
| --- | --- |
| 2026-11-24 | Proje adı **Cardino** ("car" + "ordino"). |
| 2026-11-24 | Ekip: Kürşat Akyol + Claude. Dış ekip yok; mevcut VPS kullanılacak. |
| 2026-11-24 | Önce araç arşivi/maliyet motoru, sonra takas değerleme. İstihbarat modülü en sona ertelendi. |
| 2026-11-24 | Başka sitelerden izinsiz veri kazıma yapılmayacak; dış veri yalnızca resmi API veya anlaşmayla alınacak. |
| 2026-11-24 | Vergi oranları koda gömülmeyecek; yönetici panelinden güncellenebilir tabloda tutulacak. |
| 2026-11-24 | Alan adı **cardino.space** alındı. |
| 2026-11-24 | nginx + Let's Encrypt SSL kuruldu: https://cardino.space ve https://panel.cardino.space (otomatik yenileme açık, sertifika bitişi 2027-02-22). www Natro yönlendirmesinde kaldı. |
| 2026-11-24 | Teknoloji: PHP 8.4 + Laravel + MariaDB (Debian 13 VPS); arayüz derlemesi için mevcut Node.js 22. |
| 2026-11-24 | Sunucu kuruldu: PHP 8.4.28, Composer 2.11, MariaDB 11.8, Laravel 13.43 (/var/www/cardino/app), panel.cardino.space Laravel'e bağlandı. Dil tr, production, debug kapalı. |
| 2026-11-24 | Laravel kodu GitHub'da (depo kökü = Laravel uygulaması). Sunucu GitHub'dan deploy anahtarıyla çeker; güncelleme komutu: `cardino-guncelle`. |
| 2026-11-24 | Günlük yedek: /var/backups/cardino, her gece 03:30, 14 gün. |
| 2026-11-24 | Güvenlik: fail2ban (SSH) + otomatik güvenlik güncellemeleri. Docker ile çakışmasın diye ufw kurulmadı; dışarı yalnızca 22/80/443 açık. |
| 2026-11-24 | Giriş: e-posta yerine kullanıcı adı + şifre. Roller: admin (Cardino yöneticisi), sahip, personel. Şifreler koda/depoya yazılmaz; kullanıcılar `cardino:kullanici` komutuyla oluşturulur. |
| 2026-11-24 | Araç kartı: şasi no galeri içinde benzersiz; menşe boş bırakılırsa şasi numarasından tahmin edilir; silme geri alınabilir (soft delete). |
| 2026-11-24 | Maliyet motoru: her masraf kendi para biriminde (JPY/USD/EUR/TRY/GBP) + işlem günündeki kur + GBP karşılığı (`base_amount`) olarak saklanır. Taban para birimi **GBP**. Kur, masraf günü için `exchange_rates` tablosundan okunur (o güne yoksa en yakın önceki). Araçta `shipping_route` alanı: doğrudan (Gazimağusa/Girne) ya da Mersin aktarmalı. Kâr-zarar = satış GBP − toplam maliyet GBP. Kurlar `cardino:kur <para> <kur> [--tarih=]` ile girilir. |
