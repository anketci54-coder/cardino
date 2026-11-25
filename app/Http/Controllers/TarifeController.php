<?php

namespace App\Http\Controllers;

use App\Models\GumrukAyar;
use App\Models\KayitTarifesi;
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
            'tarifeler' => KayitTarifesi::query()->orderBy('sira')->orderBy('min_cc')->get(),
            'ayarlar' => GumrukAyar::query()->get()->keyBy('anahtar'),
        ]);
    }

    /** Gümrük vergisi oranları + genel ayarları birlikte günceller. */
    public function ayarlariGuncelle(Request $request): RedirectResponse
    {
        $request->validate([
            'oran' => ['array'],
            'oran.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ayar' => ['array'],
            'ayar.*' => ['nullable', 'string', 'max:100'],
        ]);

        foreach (($request->input('oran', [])) as $id => $deger) {
            if ($deger !== null && $deger !== '') {
                VergiOrani::query()->whereKey($id)->update(['oran' => (float) str_replace(',', '.', $deger)]);
            }
        }

        foreach (($request->input('ayar', [])) as $anahtar => $deger) {
            if (array_key_exists($anahtar, GumrukAyar::TANIMLAR)) {
                GumrukAyar::ayarla($anahtar, $deger === '' ? null : $deger);
            }
        }

        return back()->with('ok', 'Oranlar ve ayarlar güncellendi.');
    }

    public function kayitStore(Request $request): RedirectResponse
    {
        $data = $this->kayitKurallari($request);
        KayitTarifesi::query()->create($data);

        return back()->with('ok', 'Tarife kademesi eklendi.');
    }

    public function kayitUpdate(Request $request, KayitTarifesi $tarife): RedirectResponse
    {
        $data = $this->kayitKurallari($request);
        $tarife->update($data);

        return back()->with('ok', 'Tarife kademesi güncellendi.');
    }

    public function kayitDestroy(KayitTarifesi $tarife): RedirectResponse
    {
        $tarife->delete();

        return back()->with('ok', 'Tarife kademesi silindi.');
    }

    private function kayitKurallari(Request $request): array
    {
        return $request->validate([
            'min_cc' => ['required', 'integer', 'min:0'],
            'max_cc' => ['nullable', 'integer', 'min:0'],
            'min_yas' => ['required', 'integer', 'min:0'],
            'max_yas' => ['nullable', 'integer', 'min:0'],
            'tutar_gbp' => ['nullable', 'numeric', 'min:0'],
            'oran' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'aciklama' => ['nullable', 'string', 'max:255'],
            'sira' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
