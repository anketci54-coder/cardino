# Araç istihbaratı modülü (ertelendi)

Ayrıntılı notlar proje planı dokümanında: https://claude.ai/artifact/DCxU9AYwsoiaEKkTHohgdY ("Modül 1 veri altyapısı" bölümü).

## Özet

- Rakip: caradar.net (Girne) — Japonya ve İngiltere raporu, galeri hesapları, kredi paketleri. Koşulları otomatik sorguyu yasaklıyor; entegrasyon ancak anlaşmayla.
- Japonya veri sağlayıcıları: JPcenter API (≈1,18 $/sorgu, teyit edilecek), GlobalVIN (rapor başı, teklif gerekli).
- İngiltere: DVSA MOT ve DVLA API (ücretsiz); tam geçmiş için toptan sağlayıcılar (1,49–3,00 £).

## Planlanan akış

1. Şasi no / VIN girilir.
2. Menşe tespiti: Japon şasi formatı → Japonya; 17 haneli VIN → İngiltere (bulunamazsa Japonya da denenir).
3. Ücretsiz ön sorgu + önbellek.
4. Ödeme bize (galeri kredisi veya kart).
5. Sağlayıcı yönlendirici (her sağlayıcı ayrı modül, ön ödemeli toptan bakiye).
6. Rapor motoru (Türkçe, hasar kodları, yapay zekâ özeti).
7. Rapor araç kaydına eklenir; hata olursa kredi iade.
