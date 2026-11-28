@extends('layouts.app')
@section('title', 'Yeni fırsat · Cardino')
@php use App\Support\Para; use App\Models\Firsat; use App\Models\Vehicle; @endphp
@push('head')
<style>
    form.card { padding: 22px; display: grid; gap: 20px; max-width: 760px; }
    fieldset { border: 0; margin: 0; padding: 0; display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); }
    legend { font-weight: 800; font-size: 15px; margin-bottom: 10px; color: var(--brand); }
    .full { grid-column: 1 / -1; }
    label { display: block; font-size: 12px; color: var(--muted); margin-bottom: 4px; }
    input, select, textarea { width: 100%; padding: .6rem .7rem; border: 1px solid var(--line); border-radius: 8px; font: 15px var(--font); background: #fff; color: var(--ink); }
    textarea { min-height: 70px; resize: vertical; }
    .hint { font-size: 12px; color: var(--muted); margin-top: 4px; }
    .ikili { display: grid; grid-template-columns: 1.4fr 1fr; gap: 8px; }
    .err { font-size: 13px; color: var(--danger); margin-top: 4px; }
</style>
@endpush
@section('content')
<div class="page-head">
    <div>
        <a class="muted" href="{{ route('firsat.index') }}" style="text-decoration:none">← Fırsatlar</a>
        <h1 style="margin-top:4px">Fırsat değerlendir</h1>
    </div>
</div>
<p class="muted" style="margin:-6px 0 16px;max-width:720px">Açık artırmada baktığın aracı gir; sistem gümrüklü maliyeti, beklenen kârı ve 5 yaş uygunluğunu hesaplayıp <strong>AL / GEÇ</strong> der.</p>

@if ($errors->any())
    <div class="error" role="alert" style="margin-bottom:14px">Eksik/yanlış alanlar var, kontrol edin.</div>
@endif

<form class="panel card" method="POST" action="{{ route('firsat.store') }}">
    @csrf
    @php $f = $firsat; @endphp

    <fieldset>
        <legend>Araç</legend>
        <div class="full">
            <label>Kaynak (açık artırma linki / not)</label>
            <input type="text" name="kaynak" value="{{ old('kaynak', $f->kaynak) }}" placeholder="örn. USS Tokyo lot 40213 ya da ilan linki">
        </div>
        <div><label>Marka</label><input type="text" name="make" value="{{ old('make', $f->make) }}" placeholder="Mercedes-Benz"></div>
        <div><label>Model</label><input type="text" name="model" value="{{ old('model', $f->model) }}" placeholder="GLC 220d AMG Line" required></div>
        <div><label>Şasi no (opsiyonel)</label><input type="text" name="chassis_no" value="{{ old('chassis_no', $f->chassis_no) }}" class="mono"></div>
        <div>
            <label>Menşe</label>
            <select name="origin">@foreach (Vehicle::ORIGINS as $k => $v)<option value="{{ $k }}" @selected(old('origin', $f->origin)===$k)>{{ $v }}</option>@endforeach</select>
        </div>
        <div>
            <label>Rota</label>
            <select name="shipping_route">
                <option value="direkt" @selected(old('shipping_route', $f->shipping_route)==='direkt')>Doğrudan</option>
                <option value="mersin" @selected(old('shipping_route', $f->shipping_route)==='mersin')>Mersin aktarmalı</option>
            </select>
        </div>
        <div><label>Model yılı</label><input type="number" name="model_year" value="{{ old('model_year', $f->model_year) }}" placeholder="2022" required></div>
        <div>
            <label>İlk kayıt tarihi</label>
            <input type="date" name="first_registration" value="{{ old('first_registration', optional($f->first_registration)->toDateString()) }}">
            <div class="hint">Açık artırma kağıdından; 5 yaş kuralı bundan kesinleşir.</div>
        </div>
        <div><label>Motor hacmi (cc)</label><input type="number" name="engine_cc" value="{{ old('engine_cc', $f->engine_cc) }}" placeholder="1993" required></div>
        <div>
            <label>Yakıt</label>
            <select name="fuel">@foreach (Firsat::FUELS as $k => $v)<option value="{{ $k }}" @selected(old('fuel', $f->fuel)===$k)>{{ $v }}</option>@endforeach</select>
        </div>
        <div>
            <label>Kullanım</label>
            <select name="usage">@foreach (Firsat::USAGES as $k => $v)<option value="{{ $k }}" @selected(old('usage', $f->usage ?? 'ozel')===$k)>{{ $v }}</option>@endforeach</select>
        </div>
    </fieldset>

    <fieldset>
        <legend>Fiyat</legend>
        <div>
            <label>Alış fiyatı</label>
            <div class="ikili">
                <input type="number" step="0.01" name="buy_price" value="{{ old('buy_price', $f->buy_price) }}" placeholder="4.900.000" required>
                <select name="buy_currency">@foreach (Para::CURRENCIES as $k => $v)<option value="{{ $k }}" @selected(old('buy_currency', $f->buy_currency ?? 'JPY')===$k)>{{ $v }}</option>@endforeach</select>
            </div>
        </div>
        <div><label>Gümrük değeri (referans, ops.)</label><input type="number" step="0.01" name="customs_value" value="{{ old('customs_value', $f->customs_value) }}"></div>
        <div><label>Navlun (GBP, boş = varsayılan)</label><input type="number" step="0.01" name="shipping" value="{{ old('shipping', $f->shipping) }}"></div>
        <div>
            <label>Beklenen satış (GBP)</label>
            <input type="number" step="0.01" name="expected_sale" value="{{ old('expected_sale', $f->expected_sale) }}" placeholder="KKTC'de satış fiyatı">
            <div class="hint">AL/GEÇ kararı için bunu gir — kâr buradan çıkar.</div>
        </div>
        <div><label>Hedef kâr marjı (%, boş = varsayılan)</label><input type="number" step="0.1" name="kar_marji" value="{{ old('kar_marji') }}" placeholder="15"></div>
    </fieldset>

    <fieldset>
        <legend>Not</legend>
        <div class="full"><textarea name="notes" placeholder="Hasar durumu, açık artırma notu...">{{ old('notes', $f->notes) }}</textarea></div>
    </fieldset>

    <div><button class="btn" type="submit">Değerlendir</button></div>
</form>
@endsection
