<?php

namespace App\Http\Controllers;

use App\Models\VergiOrani;
use App\Support\MaliyetHesaplayici;
use App\Support\Para;
use Illuminate\Http\Request;
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

        $h = MaliyetHesaplayici::hesapla($data);
        $sonuc = [
            'kalemler' => $h['kalemler'],
            'cif' => $h['cif'],
            'toplam' => $h['toplam'],
            'satis' => $h['satis'],
            'marj' => $h['marj'],
            'yas' => $h['yas'],
            'tarife_var' => true,
        ];

        return view('maliyet.hesapla', ['sonuc' => $sonuc, 'girdi' => $data]);
    }
}
