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
    input.s { width: 100%; max-width: 120px; padding: .4rem .5rem; border: 1px solid var(--line); border-radius: 7px; font: 14px var(--font); background: #fff; color: var(--ink); }
    input.g { max-width: 100%; }
    .ayar-izgara { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 12px; }
    .ayar-izgara label { font-size: 12px; color: var(--muted); display: block; margin-bottom: 3px; }
    .ornek { font-size: 11px; font-weight: 700; color: #7a5300; background: #fdf6e3; border: 1px solid #f0e0b0; border-radius: 999px; padding: 1px 8px; }
    .satir-form { display: inline; }
    .sil { background: none; border: 0; color: var(--danger); cursor: pointer; font-size: 18px; }
    .ekle td { background: #f7f7f5; }
</style>
@endpush
@section('content')
<div class="page-head"><h1>Tarife ve vergi oranları</h1></div>
<p class="alt" style="color:var(--muted);font-size:14px;margin:-6px 0 18px">
    Maliyet hesaplayıcının kullandığı oranlar. <strong>Değerleri komisyoncu/gümrük teyidiyle girin.</strong>
</p>

<form method="POST" action="{{ route('yonetim.tarife.ayar') }}">
    @csrf @method('PUT')

    <section class="panel box">
        <h2>Gümrük vergisi oranları (%)</h2>
        <p class="alt">Araç değeri + navlun + sigorta (CIF) üzerinden uygulanır.</p>
        <table class="t">
            <thead><tr><th>Yakıt</th><th>Kullanım</th><th>Oran (%)</th></tr></thead>
            <tbody>
            @foreach ($oranlar as $o)
                <tr>
                    <td>{{ VergiOrani::YAKITLAR[$o->yakit] ?? $o->yakit }}</td>
                    <td>{{ VergiOrani::KULLANIMLAR[$o->kullanim] ?? $o->kullanim }}</td>
                    <td><input class="s" type="number" step="0.001" name="oran[{{ $o->id }}]" value="{{ rtrim(rtrim($o->oran, '0'), '.') }}"></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section class="panel box">
        <h2>Genel ayarlar</h2>
        <p class="alt">KDV, varsayılan masraflar ve hedef kâr marjı.</p>
        <div class="ayar-izgara">
            @foreach (GumrukAyar::TANIMLAR as $anahtar => $etiket)
                <div>
                    <label>{{ $etiket }}</label>
                    <input class="s g" type="text" name="ayar[{{ $anahtar }}]" value="{{ $ayarlar[$anahtar]->deger ?? '' }}">
                </div>
            @endforeach
        </div>
        <button class="btn" type="submit" style="margin-top:16px">Oranları ve ayarları kaydet</button>
    </section>
</form>

<section class="panel box">
    <h2>Kayıt / ruhsat harcı tarifesi</h2>
    <p class="alt">Motor hacmi (cc) ve yaş aralığına göre harç. Tutar (sabit GBP) <em>veya</em> oran (araç değerinin %'si) girin.</p>
    <table class="t">
        <thead><tr><th>Min cc</th><th>Max cc</th><th>Min yaş</th><th>Max yaş</th><th>Tutar (GBP)</th><th>Oran (%)</th><th>Açıklama</th><th></th></tr></thead>
        <tbody>
        @foreach ($tarifeler as $t)
            <tr>
                <form method="POST" action="{{ route('yonetim.tarife.kayit.update', $t) }}" class="satir-form" id="tf{{ $t->id }}">@csrf @method('PUT')</form>
                <td><input class="s" form="tf{{ $t->id }}" type="number" name="min_cc" value="{{ $t->min_cc }}"></td>
                <td><input class="s" form="tf{{ $t->id }}" type="number" name="max_cc" value="{{ $t->max_cc }}" placeholder="∞"></td>
                <td><input class="s" form="tf{{ $t->id }}" type="number" name="min_yas" value="{{ $t->min_yas }}"></td>
                <td><input class="s" form="tf{{ $t->id }}" type="number" name="max_yas" value="{{ $t->max_yas }}" placeholder="∞"></td>
                <td><input class="s" form="tf{{ $t->id }}" type="number" step="0.01" name="tutar_gbp" value="{{ $t->tutar_gbp !== null ? rtrim(rtrim($t->tutar_gbp,'0'),'.') : '' }}"></td>
                <td><input class="s" form="tf{{ $t->id }}" type="number" step="0.001" name="oran" value="{{ $t->oran !== null ? rtrim(rtrim($t->oran,'0'),'.') : '' }}"></td>
                <td>
                    <input class="s g" form="tf{{ $t->id }}" type="text" name="aciklama" value="{{ $t->aciklama }}" style="max-width:160px">
                    @if (str_contains((string) $t->aciklama, 'ÖRNEK')) <span class="ornek">ÖRNEK</span> @endif
                </td>
                <td style="white-space:nowrap">
                    <input form="tf{{ $t->id }}" type="hidden" name="sira" value="{{ $t->sira }}">
                    <button class="btn" form="tf{{ $t->id }}" type="submit" style="padding:.3rem .7rem;font-size:13px">Kaydet</button>
                    <form method="POST" action="{{ route('yonetim.tarife.kayit.destroy', $t) }}" class="satir-form" onsubmit="return confirm('Bu kademe silinsin mi?')">
                        @csrf @method('DELETE')
                        <button class="sil" type="submit" title="Sil">×</button>
                    </form>
                </td>
            </tr>
        @endforeach
        <tr class="ekle">
            <form method="POST" action="{{ route('yonetim.tarife.kayit.store') }}" class="satir-form" id="yeni-tarife">@csrf</form>
            <td><input class="s" form="yeni-tarife" type="number" name="min_cc" value="0"></td>
            <td><input class="s" form="yeni-tarife" type="number" name="max_cc" placeholder="∞"></td>
            <td><input class="s" form="yeni-tarife" type="number" name="min_yas" value="0"></td>
            <td><input class="s" form="yeni-tarife" type="number" name="max_yas" placeholder="∞"></td>
            <td><input class="s" form="yeni-tarife" type="number" step="0.01" name="tutar_gbp" placeholder="GBP"></td>
            <td><input class="s" form="yeni-tarife" type="number" step="0.001" name="oran" placeholder="%"></td>
            <td><input class="s g" form="yeni-tarife" type="text" name="aciklama" style="max-width:160px"></td>
            <td><button class="btn" form="yeni-tarife" type="submit" style="padding:.3rem .7rem;font-size:13px">Ekle</button></td>
        </tr>
        </tbody>
    </table>
</section>
@endsection
