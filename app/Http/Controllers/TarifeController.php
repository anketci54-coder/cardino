<?php

namespace App\Http\Controllers;

use App\Models\FifTarifesi;
use App\Models\GumrukAyar;
use App\Models\VergiOrani;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TarifeController extends Controller
{
    public function index(): View
    {
        return view('yonetim.tarife', [
            'oranlar' => VergiOrani::query()->orderBy('yakit')->orderBy('kullanim')->get(),
            'fifler' => FifTarifesi::query()->orderBy('yakit')->orderBy('sira')->orderBy('min_cc')->get(),
            'ayarlar' => GumrukAyar::query()->get()->keyBy('anahtar'),
        ]);
    }

    /** Yakıt×kullanım oranları + genel ayarları birlikte günceller. */
    public function ayarlariGuncelle(Request $request): RedirectResponse
    {
        $request->validate([
            'oran' => ['array'],
            'ayar' => ['array'],
            'ayar.*' => ['nullable', 'string', 'max:100'],
        ]);

        foreach (($request->input('oran', [])) as $id => $alanlar) {
            $satir = VergiOrani::query()->find($id);
            if (! $satir) {
                continue;
            }
            foreach (['gumruk_orani', 'kayit_orani', 'kdv_orani'] as $alan) {
                if (isset($alanlar[$alan]) && $alanlar[$alan] !== '') {
                    $satir->{$alan} = (float) str_replace(',', '.', $alanlar[$alan]);
                }
            }
            $satir->save();
        }

        foreach (($request->input('ayar', [])) as $anahtar => $deger) {
            if (array_key_exists($anahtar, GumrukAyar::TANIMLAR)) {
                GumrukAyar::ayarla($anahtar, $deger === '' ? null : $deger);
            }
        }

        return back()->with('ok', 'Oranlar ve ayarlar güncellendi.');
    }

    public function fifStore(Request $request): RedirectResponse
    {
        FifTarifesi::query()->create($this->fifKurallari($request));

        return back()->with('ok', 'FİF kademesi eklendi.');
    }

    public function fifUpdate(Request $request, FifTarifesi $fif): RedirectResponse
    {
        $fif->update($this->fifKurallari($request));

        return back()->with('ok', 'FİF kademesi güncellendi.');
    }

    public function fifDestroy(FifTarifesi $fif): RedirectResponse
    {
        $fif->delete();

        return back()->with('ok', 'FİF kademesi silindi.');
    }

    private function fifKurallari(Request $request): array
    {
        return $request->validate([
            'yakit' => ['required', 'in:'.implode(',', array_keys(VergiOrani::YAKITLAR))],
            'min_cc' => ['required', 'integer', 'min:0'],
            'max_cc' => ['nullable', 'integer', 'min:0'],
            'oran' => ['required', 'numeric', 'min:0', 'max:100'],
            'sira' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
