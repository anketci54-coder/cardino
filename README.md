# Cardino

KKTC oto galerileri için araç yönetim sistemi: Japonya ve İngiltere'den ithal edilen araçların alıştan satışa tüm maliyetini, kârını ve takas değerini tek yerde tutar.

## Modüller

| Sıra | Modül | Durum |
| --- | --- | --- |
| 1 | **Araç arşivi ve maliyet motoru** — araç kartı, alış/navlun/gümrük/masraf kayıtları, kâr-zarar, gümrüklenmiş maliyet tahmini | Öncelikli |
| 2 | **Takas değerleme** — fotoğraf + şasi/km okuma, KKTC piyasasındaki benzerlerin min–max ve ortalama fiyatı, önerilen takas teklifi | Sırada |
| 3 | **Araç istihbaratı** — şasi/VIN ile Japonya açık artırma ve İngiltere geçmiş raporu | Ertelendi (bkz. `docs/modul-istihbarat.md`) |

## Ekip ve ortam

- Kürşat Akyol (ürün) + Claude (geliştirme)
- Barındırma: mevcut VPS
- Pilot: 1 galeri

## Belgeler

- `docs/kararlar.md` — alınan kararlar
- `docs/modul-istihbarat.md` — ertelenen istihbarat modülü araştırma notları
- `docs/vergi-kaynaklari.md` — KKTC araç ithalat vergileri için kaynaklar
