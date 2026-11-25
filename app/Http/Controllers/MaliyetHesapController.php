<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\GumrukAyar;
use App\Models\KayitTarifesi;
use App\Models\VergiOrani;
use App\Support\Para;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class MaliyetHesapController extends Controller
{
    public function form(): View
    {
        return view('maliyet.hesapla', ['sonuc' => null, 'girdi' => null]);
    }

    public function hesapla(Request $request): View
    {
        $data = $request->validate([
            'alis_fiyat' => ['required', 'numeric', 'min:0'],
            'alis_para' => ['required', 'in:'.implode(',', Para::CURRENCIES)],
            'yakit' => ['required', 'in:'.implode(',', array_keys(VergiOrani::YAKITLAR))],
            'kullanim' => ['required', 'in:'.implode(',', array_keys(VergiOrani::KULLANIMLAR))],
            'motor_cc' => ['required', 'integer', 'min:0', 'max:20000'],
            'model_yili' => ['required', 'integer', 'min:1950', 'max:'.(now()->year + 1)],
            'rota' => ['required', 'in:direkt,mersin'],
            'navlun' => ['nullable', 'numeric', 'min:0'],
            'kar_marji' => ['nullable', 'numeric', 'min:0', 'max:500'],
        ]);

        $bugun = Carbon::today();
        $yas = max(0, $bugun->year - (int) $data['model_yili']);

        // 1) Araç değeri (GBP)
        $kur = ExchangeRate::rateFor($data['alis_para'], $bugun);
        $deger = round((float) $data['alis_fiyat'] * $kur, 2);

        // 2) Navlun
        $navlun = $data['navlun'] !== null && $data['navlun'] !== ''
            ? (float) $data['navlun']
            : GumrukAyar::sayi($data['rota'] === 'mersin' ? 'navlun_mersin' : 'navlun_direkt');

        // 3) Sigorta
        $sigorta = round($deger * GumrukAyar::sayi('sigorta_orani') / 100, 2);

        // 4) CIF
        $cif = round($deger + $navlun + $sigorta, 2);

        // 5) Gümrük vergisi (CIF üzerinden)
        $gumrukOran = VergiOrani::oranBul($data['yakit'], $data['kullanim']);
        $gumruk = round($cif * $gumrukOran / 100, 2);

        // 6) Kayıt / ruhsat harcı (cc + yaş tarifesi)
        $tarife = KayitTarifesi::bul((int) $data['motor_cc'], $yas);
        $kayitHarc = $tarife ? $tarife->harc($deger) : 0.0;

        // 7) KDV
        $kdvOran = GumrukAyar::sayi('kdv_orani');
        $matrah = GumrukAyar::deger('kdv_matrahi', 'cif_gumruk') === 'cif' ? $cif : ($cif + $gumruk);
        $kdv = round($matrah * $kdvOran / 100, 2);

        // 8) Sabit masraflar
        $liman = GumrukAyar::sayi('liman_ardiye');
        $komisyoncu = GumrukAyar::sayi('komisyoncu');
        $muayene = GumrukAyar::sayi('muayene_kayit');

        // 9) Toplam
        $toplam = round($cif + $gumruk + $kayitHarc + $kdv + $liman + $komisyoncu + $muayene, 2);

        // 10) Önerilen satış
        $marj = $data['kar_marji'] !== null && $data['kar_marji'] !== ''
            ? (float) $data['kar_marji']
            : GumrukAyar::sayi('kar_marji', 15);
        $satis = round($toplam * (1 + $marj / 100), 2);

        $sonuc = [
            'kalemler' => [
                ['Araç değeri', $deger, number_format((float) $data['alis_fiyat'], 0, ',', '.').' '.$data['alis_para'].' × '.rtrim(rtrim(number_format($kur, 6, ',', '.'), '0'), ',')],
                ['Navlun ('.($data['rota'] === 'mersin' ? 'Mersin aktarmalı' : 'doğrudan').')', $navlun, null],
                ['Sigorta', $sigorta, GumrukAyar::sayi('sigorta_orani').'%'],
                ['Gümrük vergisi', $gumruk, $gumrukOran.'% (CIF)'],
                ['Kayıt / ruhsat harcı', $kayitHarc, $tarife?->araligiMetni() ?? 'tarife yok'],
                ['KDV', $kdv, $kdvOran.'%'],
                ['Liman / ardiye', $liman, null],
                ['Komisyoncu', $komisyoncu, null],
                ['Muayene / plaka / evrak', $muayene, null],
            ],
            'cif' => $cif,
            'toplam' => $toplam,
            'satis' => $satis,
            'marj' => $marj,
            'yas' => $yas,
            'tarife_var' => $tarife !== null,
        ];

        return view('maliyet.hesapla', ['sonuc' => $sonuc, 'girdi' => $data]);
    }
}
