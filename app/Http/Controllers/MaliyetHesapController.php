<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\FifTarifesi;
use App\Models\GumrukAyar;
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
            'alis_para' => ['required', 'in:'.implode(',', array_keys(Para::CURRENCIES))],
            'yakit' => ['required', 'in:'.implode(',', array_keys(VergiOrani::YAKITLAR))],
            'kullanim' => ['required', 'in:'.implode(',', array_keys(VergiOrani::KULLANIMLAR))],
            'motor_cc' => ['required', 'integer', 'min:0', 'max:20000'],
            'model_yili' => ['required', 'integer', 'min:1950', 'max:'.(now()->year + 1)],
            'rota' => ['required', 'in:direkt,mersin'],
            'gumruk_degeri' => ['nullable', 'numeric', 'min:0'],
            'navlun' => ['nullable', 'numeric', 'min:0'],
            'kar_marji' => ['nullable', 'numeric', 'min:0', 'max:500'],
        ]);

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

        $sonuc = [
            'kalemler' => [
                ['Araç değeri (fatura)', $degerFatura, $degerNot],
                ['Navlun ('.($data['rota'] === 'mersin' ? 'Mersin aktarmalı' : 'doğrudan').')', $navlun, null],
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
            ],
            'cif' => $cifGercek,
            'toplam' => $toplam,
            'satis' => $satis,
            'marj' => $marj,
            'yas' => $yas,
            'tarife_var' => true,
        ];

        return view('maliyet.hesapla', ['sonuc' => $sonuc, 'girdi' => $data]);
    }
}
