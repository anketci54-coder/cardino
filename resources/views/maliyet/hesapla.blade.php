@extends('layouts.app')
@section('title', 'Maliyet hesapla · Cardino')
@php use App\Support\Para; use App\Models\VergiOrani; @endphp
@push('head')
<style>
    .intro { color: var(--muted); margin: 2px 0 18px; font-size: 14px; }
    .cols { display: grid; grid-template-columns: 360px minmax(0,1fr); gap: 18px; align-items: start; }
    @media (max-width: 860px) { .cols { grid-template-columns: 1fr; } }
    .box { padding: 18px 20px; }
    .box h2 { font-size: 15px; margin: 0 0 14px; }
    .alan { margin-bottom: 12px; }
    .alan label { display: block; font-size: 12px; color: var(--muted); margin-bottom: 4px; }
    .alan input, .alan select { width: 100%; padding: .55rem .65rem; border: 1px solid var(--line); border-radius: 8px; font: 15px var(--font); background: #fff; color: var(--ink); }
    .ikili { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .hesapla-btn { width: 100%; margin-top: 6px; }
    table.kalem { width: 100%; border-collapse: collapse; }
    table.kalem td { padding: .5rem .3rem; border-bottom: 1px solid var(--line); font-size: 14px; }
    table.kalem td.tut { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    table.kalem td .not { color: var(--muted); font-size: 12px; }
    .ara { font-weight: 600; }
    .toplam-sat td { border-top: 2px solid var(--ink); border-bottom: 0; font-size: 17px; font-weight: 800; padding-top: .7rem; }
    .satis-kutu { margin-top: 16px; padding: 14px 16px; background: #e6f4ea; border: 1px solid #b7e1c3; border-radius: 10px; }
    .satis-kutu .k { font-size: 12px; color: #1e6b37; text-transform: uppercase; letter-spacing: .05em; }
    .satis-kutu .v { font-size: 24px; font-weight: 800; color: #1e6b37; font-variant-numeric: tabular-nums; }
    .uyari { background: #fdf6e3; border: 1px solid #f0e0b0; border-radius: 10px; padding: 12px 14px; font-size: 13px; color: #7a5300; margin-bottom: 16px; }
    .bos { color: var(--muted); padding: 40px 0; text-align: center; }
</style>
@endpush
@section('content')
<div class="page-head"><h1>Maliyet hesapla</h1></div>
<p class="intro">Alınacak bir aracın KKTC'de gümrüklenmiş tahmini maliyetini ve önerilen satış fiyatını hesaplar.</p>

<div class="uyari">
    ⚠️ Vergi oranları ve varsayılan masraflar şu an <strong>örnek değerlerdir</strong>. Gerçek sonuç için yönetici
    panelindeki <strong>Tarife</strong> tablosundan oranları (komisyoncu/gümrük teyidiyle) girin.
</div>

<div class="cols">
    <section class="panel box">
        <h2>Araç bilgileri</h2>
        @if ($errors->any())
            <div class="uyari" style="color:#8a1c12;background:#fdecea;border-color:#f1c4c0;margin-bottom:12px">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('maliyet.hesapla.sonuc') }}">
            @csrf
            @php $g = $girdi ?? []; @endphp
            <div class="alan">
                <label>Alış fiyatı</label>
                <div class="ikili">
                    <input type="number" step="0.01" name="alis_fiyat" value="{{ $g['alis_fiyat'] ?? '' }}" required>
                    <select name="alis_para">
                        @foreach (Para::CURRENCIES as $kod => $etiket)
                            <option value="{{ $kod }}" @selected(($g['alis_para'] ?? 'GBP') === $kod)>{{ $etiket }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="alan ikili">
                <div>
                    <label>Yakıt</label>
                    <select name="yakit">
                        @foreach (VergiOrani::YAKITLAR as $k => $v)
                            <option value="{{ $k }}" @selected(($g['yakit'] ?? '') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Kullanım</label>
                    <select name="kullanim">
                        @foreach (VergiOrani::KULLANIMLAR as $k => $v)
                            <option value="{{ $k }}" @selected(($g['kullanim'] ?? 'ozel') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="alan ikili">
                <div>
                    <label>Motor hacmi (cc)</label>
                    <input type="number" name="motor_cc" value="{{ $g['motor_cc'] ?? '' }}" placeholder="1600" required>
                </div>
                <div>
                    <label>Model yılı</label>
                    <input type="number" name="model_yili" value="{{ $g['model_yili'] ?? '' }}" placeholder="{{ now()->year - 5 }}" required>
                </div>
            </div>
            <div class="alan ikili">
                <div>
                    <label>Rota</label>
                    <select name="rota">
                        <option value="direkt" @selected(($g['rota'] ?? 'direkt') === 'direkt')>Doğrudan (Gazimağusa/Girne)</option>
                        <option value="mersin" @selected(($g['rota'] ?? '') === 'mersin')>Mersin aktarmalı</option>
                    </select>
                </div>
                <div>
                    <label>Navlun (GBP, boş = varsayılan)</label>
                    <input type="number" step="0.01" name="navlun" value="{{ $g['navlun'] ?? '' }}" placeholder="otomatik">
                </div>
            </div>
            <div class="alan">
                <label>Hedef kâr marjı (%, boş = varsayılan)</label>
                <input type="number" step="0.1" name="kar_marji" value="{{ $g['kar_marji'] ?? '' }}" placeholder="{{ \App\Models\GumrukAyar::sayi('kar_marji', 15) }}">
            </div>
            <button class="btn hesapla-btn" type="submit">Hesapla</button>
        </form>
    </section>

    <section class="panel box">
        <h2>Tahmini maliyet</h2>
        @if ($sonuc)
            <table class="kalem">
                @foreach ($sonuc['kalemler'] as [$ad, $tutar, $not])
                    <tr>
                        <td>{{ $ad }} @if($not)<span class="not">· {{ $not }}</span>@endif</td>
                        <td class="tut">{{ Para::format($tutar, 'GBP') }}</td>
                    </tr>
                @endforeach
                <tr class="toplam-sat">
                    <td>Toplam gümrüklü maliyet</td>
                    <td class="tut">{{ Para::format($sonuc['toplam'], 'GBP') }}</td>
                </tr>
            </table>

            <div class="satis-kutu">
                <div class="k">Önerilen satış (%{{ rtrim(rtrim(number_format($sonuc['marj'], 1, ',', '.'), '0'), ',') }} kâr)</div>
                <div class="v">{{ Para::format($sonuc['satis'], 'GBP') }}</div>
            </div>
            <p class="intro" style="margin-top:10px">Aracın yaşı: {{ $sonuc['yas'] }} · CIF (değer+navlun+sigorta): {{ Para::format($sonuc['cif'], 'GBP') }}</p>
        @else
            <div class="bos">Soldaki bilgileri doldurup “Hesapla”ya basın.</div>
        @endif
    </section>
</div>
@endsection
