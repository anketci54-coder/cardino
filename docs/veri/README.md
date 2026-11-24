# Pazar verisi: kaynaklar ve notlar

`ihracat-kibris-turkiye.csv`: Japonya ve İngiltere'den Kıbrıs ve Türkiye'ye ikinci el binek araç ihracatı.

- Japonya → Kıbrıs: Japonya Maliye Bakanlığı ticaret istatistikleri (e-Stat), JUMV derlemesi: https://jumv.net/basic_knowledge_usedcar_export/export_statistics/statistics?country=cyp (çekildi 2026-11-24). Japonya → Türkiye: aynı kaynağın Türkiye sayfası (2021 değeri 2022'nin yıllık değişim oranından hesaplandı; 2016–2020 alınmadı). Ortalama FOB fiyat birimi 10.000 yen (万円).
- İngiltere: HMRC Overseas Trade Statistics API (https://api.uktradeinfo.com), HS 8703, son iki hanesi 90 olan kodlar = kullanılmış araç. Adet = ek ölçü birimi (SuppUnit). 2026 yalnızca yılın verilen ayları. Çekildi 2026-11-24.
- Uyarı: İngiltere → Türkiye 2019 (33.608 adet, ortalama ~850 £) ve 2020 değerleri tutarsız görünüyor; muhtemel kayıt/sınıflandırma hatası.

## Çalışma varsayımı (Kürşat)

Türkiye ikinci el ve sağdan direksiyonlu araç almadığı için Japonya/İngiltere'den Türkiye'ye (Mersin) giden ikinci el araçların ~%90'ı KKTC'ye aktarılır. Japonya, KKTC limanlarına (Gazimağusa ve Girne) doğrudan gidenleri "Kıbrıs" olarak yazıyor; ihracat istatistiği limanı değil ülkeyi gösterir, iki liman da aynı rakamın içinde.

KKTC ≈ (JP + UK → Türkiye) × 0,9 + (JP + UK → Kıbrıs) − Güney Kıbrıs ikinci el tescili

Güney Kıbrıs ikinci el binek tescili: 2023 ≈ 21.965, 2024 ≈ 23.353 (Cyprus Mail, toplam × kullanılmış oranı).

## Güney Kıbrıs ithalatı (eklendi 2026-11-24)

`guney-kibris-ithalat.csv`: Güney Kıbrıs'ın Japonya ve İngiltere'den ikinci el binek araç ithalatı (adet). Kaynak: Eurostat Comext DS-045409, reporter=CY, flow=ithalat, ek ölçü birimi, CN kodları 870321–870380 son iki hanesi 90. Güney'in bildirimi yalnızca kendi kontrolündeki limanları (Limasol, Larnaka, Baf) kapsar; KKTC limanları dahil değildir. Güney ile Türkiye arasında doğrudan ticaret yok.

Notlar:
- Brexit öncesi (2016–2020) İngiltere'nin AB içi ihracat kaydı eksik; Güney'in İngiltere'den ithalatı İngiltere'nin "Kıbrıs'a ihracat"ından çok yüksek. Bu yıllar İngiltere kanalı için kullanılmaz.
- AB dışı ithalatta ortak ülke menşe ülkesidir; Japon menşeli olup başka ülkeden (ör. BAE) gelen araçlar da "Japonya" sayılabilir.
- Gemi süresi yüzünden yıl sonu sevkiyatlar ertesi yıla kayar; tek yıla değil çok yıllık ortalamaya bakılmalı.

## KKTC tahmini (2022–2025 ortalaması)

| Kanal | Yıllık ortalama (adet) |
| --- | --- |
| Japonya → KKTC limanları (Gazimağusa, Girne) = JP→Kıbrıs − Güney'in JP ithalatı | ~3.700 |
| İngiltere → KKTC limanları = UK→Kıbrıs − Güney'in UK ithalatı | ~2.200 |
| İngiltere → Mersin → KKTC | ~4.900 |
| Japonya → Mersin → KKTC | ~3.100 |
| **Toplam** | **~14.000** |

Karşılaştırma: KKTC Trafik Dairesi'nin galeri ikinci el satışları 2023 ilk 8 ay 7.654 (yılda ~11.500); bu sayım yalnızca 80+ satışı olan markaları içerdiği için gerçek toplam daha yüksek. Aynı büyüklük düzeyinde.
