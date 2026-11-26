@extends('layouts.app')
@section('title', 'Tarife · Cardino')
@php use App\Models\VergiOrani; use App\Models\GumrukAyar; @endphp
@push('head')
<style>
    .box { padding: 18px 20px; margin-bottom: 18px; }
    .box h2 { font-size: 15px; margin: 0 0 6px; }
    .box .alt { color: var(--muted); font-size: 13px; margin: 0 0 14px; }
    table.t { width: 100%; border-collapse: collapse; }
    table.t th, table.t td { text-align: left; padding: .5rem .5rem; border-bottom: 1px solid var(--line); font-size: 14px; vertical-align: middle; }
    table.t th { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); }
    input.s, select.s { width: 100%; max-width: 110px; padding: .4rem .5rem; border: 1px solid var(--line); border-radius: 7px; font: 14px var(--font); background: #fff; color: var(--ink); }
    .ayar-izgara { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 12px; }
    .ayar-izgara label { font-size: 12px; color: var(--muted); display: block; margin-bottom: 3px; }
    .satir-form { display: inline; }
    .sil { background: none; border: 0; color: var(--danger); cursor: pointer; font-size: 18px; }
    .ekle td { background: #f7f7f5; }
    .uyari { background: #fdf6e3; border: 1px solid #f0e0b0; border-radius: 10px; padding: 12px 14px; font-size: 13px; color: #7a5300; margin-bottom: 16px; }
</style>
@endpush
@section('content')
<div class="page-head"><h1>Tarife ve vergi oranları</h1></div>

@if ($errors->any())
    <div class="error" role="alert" style="background:#fdecea;color:#8a1c12;border:1px solid #f1c4c0;border-radius:8px;padding:.6rem .9rem;margin-bottom:14px">{{ $errors->first() }}</div>
@endif
<div class="uyari">
    Hesaplama sırası: CIF → Gümrük vergisi → FİF → Rıhtım → GKK → KDV (önceki tümü matraha girer) → İlk kayıt harcı.
    Tümü CIF üzerinden yüzdesel. Oranları komisyoncu/gümrük teyidiyle girin. (İthalat: UK/Japonya = 3. ülke.)
</div>

<form method="POST" action="{{ route('yonetim.tarife.ayar') }}">
    @csrf @method('PUT')

    <section class="panel box">
        <h2>Yakıt × kullanım oranları (%)</h2>
        <p class="alt">Gümrük vergisi, ilk kayıt (plaka) harcı ve KDV — hepsi CIF üzerinden. Elektrikte KDV 0 (muaf).</p>
        <table class="t">
            <thead><tr><th>Yakıt</th><th>Kullanım</th><th>Gümrük %</th><th>İlk kayıt %</th><th>KDV %</th></tr></thead>
            <tbody>
            @foreach ($oranlar as $o)
                <tr>
                    <td>{{ VergiOrani::YAKITLAR[$o->yakit] ?? $o->yakit }}</td>
                    <td>{{ VergiOrani::KULLANIMLAR[$o->kullanim] ?? $o->kullanim }}</td>
                    <td><input class="s" type="number" step="0.001" name="oran[{{ $o->id }}][gumruk_orani]" value="{{ rtrim(rtrim($o->gumruk_orani, '0'), '.') }}"></td>
                    <td><input class="s" type="number" step="0.001" name="oran[{{ $o->id }}][kayit_orani]" value="{{ rtrim(rtrim($o->kayit_orani, '0'), '.') }}"></td>
                    <td><input class="s" type="number" step="0.001" name="oran[{{ $o->id }}][kdv_orani]" value="{{ rtrim(rtrim($o->kdv_orani, '0'), '.') }}"></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section class="panel box">
        <h2>Genel ayarlar</h2>
        <p class="alt">Rıhtım, GKK ve varsayılan masraflar.</p>
        <div class="ayar-izgara">
            @foreach (GumrukAyar::TANIMLAR as $anahtar => $etiket)
                <div>
                    <label>{{ $etiket }}</label>
                    <input class="s" style="max-width:100%" type="text" name="ayar[{{ $anahtar }}]" value="{{ $ayarlar[$anahtar]->deger ?? '' }}">
                </div>
            @endforeach
        </div>
        <button class="btn" type="submit" style="margin-top:16px">Oranları ve ayarları kaydet</button>
    </section>
</form>

<section class="panel box">
    <h2>FİF — Fiyat İstikrar Fonu (motor hacmine göre %)</h2>
    <p class="alt">Yakıt ve motor hacmi (cc) kademesine göre FİF oranı. Örn. benzin/dizel ≤1600 %20, 1601–2000 %35, ≥2001 %50; elektrik %5.</p>
    <table class="t">
        <thead><tr><th>Yakıt</th><th>Min cc</th><th>Max cc</th><th>Oran %</th><th>Sıra</th><th></th></tr></thead>
        <tbody>
        @foreach ($fifler as $f)
            <tr>
                <form method="POST" action="{{ route('yonetim.tarife.fif.update', $f) }}" class="satir-form" id="fif{{ $f->id }}">@csrf @method('PUT')</form>
                <td>
                    <select class="s" form="fif{{ $f->id }}" name="yakit">
                        @foreach (VergiOrani::YAKITLAR as $k => $v)
                            <option value="{{ $k }}" @selected($f->yakit === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input class="s" form="fif{{ $f->id }}" type="number" name="min_cc" value="{{ $f->min_cc }}"></td>
                <td><input class="s" form="fif{{ $f->id }}" type="number" name="max_cc" value="{{ $f->max_cc }}" placeholder="∞"></td>
                <td><input class="s" form="fif{{ $f->id }}" type="number" step="0.001" name="oran" value="{{ rtrim(rtrim($f->oran,'0'),'.') }}"></td>
                <td><input class="s" form="fif{{ $f->id }}" type="number" name="sira" value="{{ $f->sira }}" style="max-width:70px"></td>
                <td style="white-space:nowrap">
                    <button class="btn" form="fif{{ $f->id }}" type="submit" style="padding:.3rem .7rem;font-size:13px">Kaydet</button>
                    <form method="POST" action="{{ route('yonetim.tarife.fif.destroy', $f) }}" class="satir-form" onsubmit="return confirm('Bu kademe silinsin mi?')">
                        @csrf @method('DELETE')
                        <button class="sil" type="submit" title="Sil">×</button>
                    </form>
                </td>
            </tr>
        @endforeach
        <tr class="ekle">
            <form method="POST" action="{{ route('yonetim.tarife.fif.store') }}" class="satir-form" id="yeni-fif">@csrf</form>
            <td>
                <select class="s" form="yeni-fif" name="yakit">
                    @foreach (VergiOrani::YAKITLAR as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </td>
            <td><input class="s" form="yeni-fif" type="number" name="min_cc" value="0"></td>
            <td><input class="s" form="yeni-fif" type="number" name="max_cc" placeholder="∞"></td>
            <td><input class="s" form="yeni-fif" type="number" step="0.001" name="oran" placeholder="%"></td>
            <td><input class="s" form="yeni-fif" type="number" name="sira" value="0" style="max-width:70px"></td>
            <td><button class="btn" form="yeni-fif" type="submit" style="padding:.3rem .7rem;font-size:13px">Ekle</button></td>
        </tr>
        </tbody>
    </table>
</section>
@endsection
