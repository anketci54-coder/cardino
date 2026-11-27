<?php

namespace App\Support;

use App\Models\ExchangeRate;
use App\Models\FifTarifesi;
use App\Models\GumrukAyar;
use App\Models\VergiOrani;
use Illuminate\Support\Carbon;

/**
 * KKTC gümrüklenmiş maliyet motoru (2026 yapısı). Hem /maliyet-hesapla ekranı
 * hem de "tahmini araca dönüştür" akışı buradan beslenir.
 */
class MaliyetHesaplayici
{
    /**
     * @param  array  $data  alis_fiyat, alis_para, yakit, kullanim, motor_cc, model_yili, rota,
     *                       gumruk_degeri?, navlun?, kar_marji?
     * @return array{kalemler:array,kostlar:array,arac:array,cif:float,toplam:float,satis:float,marj:float,yas:int}
     */
    public static function hesapla(array $data): array
    {
        $bugun = Carbon::today();
        $yas = max(0, $bugun->year - (int) $data['model_yili']);

        // 1) Araç değeri (GBP). Fatura = ödenen; gümrük değeri = vergi tabanı (referans)
        $kur = $data['alis_para'] === Para::BASE
            ? 1.0
            : (float) ExchangeRate::rateFor($data['alis_para'], $bugun);
        $degerFatura = round((float) $data['alis_fiyat'] * $kur, 2);
        $gumrukGirdi = $data['gumruk_degeri'] ?? null;
        $degerGumruk = $gumrukGirdi !== null && $gumrukGirdi !== ''
            ? round((float) $gumrukGirdi * $kur, 2)
            : $degerFatura;

        // 2) Navlun
        $navlunGirdi = $data['navlun'] ?? null;
        $navlun = $navlunGirdi !== null && $navlunGirdi !== ''
            ? (float) $navlunGirdi
            : GumrukAyar::sayi($data['rota'] === 'mersin' ? 'navlun_mersin' : 'navlun_direkt');

        // 3) Sigorta (gerçek değer üzerinden)
        $sigorta = round($degerFatura * GumrukAyar::sayi('sigorta_orani') / 100, 2);

        // 4) CIF: vergi tabanı (gümrük değeriyle) ve gerçek maliyet (faturayla)
        $cifVergi = round($degerGumruk + $navlun + $sigorta, 2);
        $cifGercek = round($degerFatura + $navlun + $sigorta, 2);

        // Yakıt + kullanım oranları (gümrük, ilk kayıt, KDV)
        $oran = VergiOrani::bul($data['yakit'], $data['kullanim']);
        $gumrukOran = (float) ($oran->gumruk_orani ?? 0);
        $kayitOran = (float) ($oran->kayit_orani ?? 0);
        $kdvOran = (float) ($oran->kdv_orani ?? 0);

        // 5) Gümrük vergisi (CIF)
        $gumruk = round($cifVergi * $gumrukOran / 100, 2);

        // 6) FİF — motor hacmi + yakıt kademesi (CIF)
        $fifOran = FifTarifesi::oranBul($data['yakit'], (int) $data['motor_cc']);
        $fif = round($cifVergi * $fifOran / 100, 2);

        // 7) Rıhtım resmi (CIF)
        $rihtimOran = GumrukAyar::sayi('rihtim_orani');
        $rihtim = round($cifVergi * $rihtimOran / 100, 2);

        // 8) GKK — Güvenlik Kuvvetleri Fonu (CIF)
        $gkkOran = GumrukAyar::sayi('gkk_orani');
        $gkk = round($cifVergi * $gkkOran / 100, 2);

        // 9) KDV — matrah: CIF + gümrük + FİF + rıhtım + GKK (kümülatif)
        $kdvMatrah = $cifVergi + $gumruk + $fif + $rihtim + $gkk;
        $kdv = round($kdvMatrah * $kdvOran / 100, 2);

        // 10) İlk kayıt (plaka) harcı — CIF üzerinden
        $ilkKayit = round($cifVergi * $kayitOran / 100, 2);

        // 11) Sabit masraflar
        $liman = GumrukAyar::sayi('liman_ardiye');
        $komisyoncu = GumrukAyar::sayi('komisyoncu');
        $muayene = GumrukAyar::sayi('muayene_kayit');
        $geciciKayit = GumrukAyar::sayi('gecici_kayit');

        // 12) Toplam (gerçek araç maliyeti üzerinden)
        $toplam = round($cifGercek + $gumruk + $fif + $rihtim + $gkk + $kdv + $ilkKayit + $liman + $komisyoncu + $muayene + $geciciKayit, 2);

        // 13) Önerilen satış
        $marjGirdi = $data['kar_marji'] ?? null;
        $marj = $marjGirdi !== null && $marjGirdi !== ''
            ? (float) $marjGirdi
            : GumrukAyar::sayi('kar_marji', 15);
        $satis = round($toplam * (1 + $marj / 100), 2);

        $farkli = $degerGumruk !== $degerFatura;
        $degerNot = number_format((float) $data['alis_fiyat'], 0, ',', '.').' '.$data['alis_para']
            .' × '.rtrim(rtrim(number_format($kur, 6, ',', '.'), '0'), ',')
            .($farkli ? ' · vergi tabanı: '.number_format($degerGumruk, 2, ',', '.').' GBP' : '');

        $rotaEtiket = $data['rota'] === 'mersin' ? 'Mersin aktarmalı' : 'doğrudan';

        // Ekranda gösterilecek kalemler [ad, tutar(GBP), not]
        $kalemler = [
            ['Araç değeri (fatura)', $degerFatura, $degerNot],
            ['Navlun ('.$rotaEtiket.')', $navlun, null],
            ['Sigorta', $sigorta, GumrukAyar::sayi('sigorta_orani').'%'],
            ['Gümrük vergisi', $gumruk, $gumrukOran.'% · CIF'],
            ['FİF (Fiyat İstikrar Fonu)', $fif, $fifOran.'% · '.((int) $data['motor_cc']).' cc'],
            ['Rıhtım resmi', $rihtim, $rihtimOran.'% · CIF'],
            ['GKK fonu', $gkk, $gkkOran.'% · CIF'],
            ['KDV', $kdv, ($kdvOran > 0 ? $kdvOran.'% (kümülatif matrah)' : 'muaf')],
            ['İlk kayıt (plaka) harcı', $ilkKayit, $kayitOran.'% · CIF'],
            ['Liman / ardiye', $liman, null],
            ['Komisyoncu', $komisyoncu, null],
            ['Muayene / evrak', $muayene, null],
            ['Geçici kayıt harcı', $geciciKayit, 'sabit ortalama'],
        ];

        // Araç kartına aktarılacak maliyet kalemleri (VehicleCost taslakları)
        $gbp = Para::BASE;
        $sabit = fn (string $cat, ?string $desc, float $tutar) => [
            'category' => $cat, 'description' => $desc,
            'amount' => $tutar, 'currency' => $gbp, 'rate' => 1.0, 'base' => $tutar,
        ];
        $hepsi = [
            ['category' => 'alis', 'description' => null, 'amount' => (float) $data['alis_fiyat'],
                'currency' => $data['alis_para'], 'rate' => $kur, 'base' => $degerFatura],
            $sabit('navlun', null, $navlun),
            $sabit('sigorta', null, $sigorta),
            $sabit('gumruk', 'Gümrük vergisi', $gumruk),
            $sabit('digervergi', 'FİF (Fiyat İstikrar Fonu)', $fif),
            $sabit('digervergi', 'Rıhtım resmi', $rihtim),
            $sabit('digervergi', 'GKK fonu', $gkk),
            $sabit('digervergi', 'KDV', $kdv),
            $sabit('kayit', 'İlk kayıt (plaka) harcı', $ilkKayit),
            $sabit('liman', null, $liman),
            $sabit('komisyoncu', null, $komisyoncu),
            $sabit('kayit', 'Muayene / evrak', $muayene),
            $sabit('kayit', 'Geçici kayıt harcı', $geciciKayit),
        ];
        // Yalnızca tutarı 0'dan büyük kalemler
        $kostlar = array_values(array_filter($hepsi, fn ($k) => (float) $k['amount'] > 0));

        // Araç kartı alanları (yakıt eşlemesi: benzin_dizel → benzin)
        $yakitMap = ['benzin_dizel' => 'benzin', 'hibrit' => 'hibrit', 'elektrik' => 'elektrik'];
        $arac = [
            'fuel' => $yakitMap[$data['yakit']] ?? null,
            'engine_cc' => (int) $data['motor_cc'],
            'model_year' => (int) $data['model_yili'],
            'shipping_route' => $data['rota'],
        ];

        return compact('kalemler', 'kostlar', 'arac', 'marj', 'yas') + [
            'cif' => $cifGercek,
            'toplam' => $toplam,
            'satis' => $satis,
        ];
    }
}
