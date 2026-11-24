# Pazar verisi: kaynaklar ve notlar

`ihracat-kibris-turkiye.csv`: Japonya ve İngiltere'den Kıbrıs ve Türkiye'ye ikinci el binek araç ihracatı.

- Japonya → Kıbrıs: Japonya Maliye Bakanlığı ticaret istatistikleri (e-Stat), JUMV derlemesi: https://jumv.net/basic_knowledge_usedcar_export/export_statistics/statistics?country=cyp (çekildi 2026-11-24). Japonya → Türkiye rakamı kaynakta tutarsız; e-Stat'tan teyit edilecek.
- İngiltere: HMRC Overseas Trade Statistics API (https://api.uktradeinfo.com), HS 8703, son iki hanesi 90 olan kodlar = kullanılmış araç. Adet = ek ölçü birimi (SuppUnit). 2026 yalnızca yılın verilen ayları. Çekildi 2026-11-24.
- Uyarı: İngiltere → Türkiye 2019 (33.608 adet, ortalama ~850 £) ve 2020 değerleri tutarsız görünüyor; muhtemel kayıt/sınıflandırma hatası.

## Çalışma varsayımı (Kürşat)

Türkiye ikinci el ve sağdan direksiyonlu araç almadığı için Japonya/İngiltere'den Türkiye'ye (Mersin) giden ikinci el araçların ~%90'ı KKTC'ye aktarılır. Japonya, Gazimağusa'ya doğrudan gidenleri "Kıbrıs" olarak yazıyor.

KKTC ≈ (JP + UK → Türkiye) × 0,9 + (JP + UK → Kıbrıs) − Güney Kıbrıs ikinci el tescili

Güney Kıbrıs ikinci el binek tescili: 2023 ≈ 21.965, 2024 ≈ 23.353 (Cyprus Mail, toplam × kullanılmış oranı).
