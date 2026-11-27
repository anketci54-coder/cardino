@extends('layouts.app')
@section('title', ($vehicle->exists ? 'Aracı düzenle' : 'Araç ekle').' · Cardino')
@push('head')
<style>
    form.card { padding: 22px; display: grid; gap: 22px; }
    fieldset { border: 0; margin: 0; padding: 0; display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
    legend { font-weight: 800; font-size: 15px; margin-bottom: 10px; color: var(--brand); }
    .full { grid-column: 1 / -1; }
    select, textarea, input[type=date], input[type=number] { width: 100%; padding: .7rem .8rem; border: 1px solid var(--line); border-radius: 8px;
           font: 16px var(--font); background: #fff; color: var(--ink); }
    textarea { min-height: 90px; resize: vertical; }
    .hint { font-size: 13px; color: var(--muted); margin-top: 4px; }
    .err { font-size: 13px; color: var(--danger); margin-top: 4px; }
    .actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .is-invalid { border-color: var(--danger) !important; }
</style>
@endpush
@section('content')
@php $v = $vehicle; @endphp
<div class="page-head">
    <h1>{{ $v->exists ? 'Aracı düzenle' : 'Araç ekle' }}</h1>
    <a class="btn btn-ghost" href="{{ $v->exists ? route('araclar.show', $v) : route('araclar.index') }}">Vazgeç</a>
</div>

@if ($errors->any())
    <div class="error" role="alert" style="margin-bottom:14px">Kaydedilemedi, işaretli alanları kontrol edin.</div>
@endif

<form class="panel card" method="POST" action="{{ $v->exists ? route('araclar.update', $v) : route('araclar.store') }}" novalidate>
    @csrf
    @if ($v->exists) @method('PUT') @endif

    @if (!empty($kostTaslak))
        <input type="hidden" name="tahminden" value="1">
        <div style="grid-column:1/-1;background:#e6f4ea;border:1px solid #b7e1c3;border-radius:10px;padding:14px 16px">
            <strong style="color:#1e6b37">Maliyet tahmininden geldi.</strong>
            <p style="margin:6px 0 10px;font-size:13px;color:#2f5d3c">Araç kaydedilince şu {{ count($kostTaslak) }} maliyet kalemi otomatik eklenecek. Yakıt, motor hacmi, yıl ve rota önceden dolduruldu; marka, model ve şasi numarasını girin.</p>
            <table style="width:100%;border-collapse:collapse;font-size:13px">
                @foreach ($kostTaslak as $k)
                    <tr>
                        <td style="padding:.25rem .3rem;border-bottom:1px solid #cfe6d4">{{ $k['description'] ?? (\App\Models\VehicleCost::CATEGORIES[$k['category']] ?? $k['category']) }}</td>
                        <td style="padding:.25rem .3rem;border-bottom:1px solid #cfe6d4;text-align:right;white-space:nowrap">{{ \App\Support\Para::format($k['amount'], $k['currency']) }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    @if (auth()->user()->isAdmin())
    <fieldset>
        <legend>Galeri</legend>
        <div>
            <label for="gallery_id">Galeri</label>
            <select id="gallery_id" name="gallery_id" @class(['is-invalid' => $errors->has('gallery_id')])>
                <option value="">Seçin</option>
                @foreach ($galleries as $g)
                    <option value="{{ $g->id }}" @selected(old('gallery_id', $v->gallery_id) == $g->id)>{{ $g->name }}</option>
                @endforeach
            </select>
            @error('gallery_id')<div class="err">{{ $message }}</div>@enderror
        </div>
    </fieldset>
    @endif

    <fieldset>
        <legend>Kimlik</legend>
        <div>
            <label for="chassis_no">Şasi no</label>
            <input id="chassis_no" name="chassis_no" type="text" value="{{ old('chassis_no', $v->chassis_no) }}" class="mono @error('chassis_no') is-invalid @enderror" autocapitalize="characters" required>
            <div class="hint" id="mense-hint">Japon araçta ör. NZE161-1234567, İngiliz araçta 17 haneli VIN.</div>
            @error('chassis_no')<div class="err">{{ $message }}</div>@enderror
        </div>
        <div>
            <label for="engine_no">Motor no</label>
            <input id="engine_no" name="engine_no" type="text" value="{{ old('engine_no', $v->engine_no) }}" class="mono" autocapitalize="characters">
        </div>
        <div>
            <label for="origin">Menşe</label>
            <select id="origin" name="origin">
                <option value="">Şasi numarasından bul</option>
                @foreach (\App\Models\Vehicle::ORIGINS as $key => $label)
                    <option value="{{ $key }}" @selected(old('origin', $v->origin) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="shipping_route">Geliş yolu</label>
            <select id="shipping_route" name="shipping_route">
                <option value="">—</option>
                @foreach (\App\Models\Vehicle::ROUTES as $key => $label)
                    <option value="{{ $key }}" @selected(old('shipping_route', $v->shipping_route) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status">Durum</label>
            <select id="status" name="status">
                @foreach (\App\Models\Vehicle::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(old('status', $v->status) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </fieldset>

    <fieldset>
        <legend>Araç</legend>
        <div>
            <label for="make">Marka</label>
            <input id="make" name="make" type="text" value="{{ old('make', $v->make) }}" list="markalar" @class(['is-invalid' => $errors->has('make')]) required>
            <datalist id="markalar">
                @foreach (['Toyota','Honda','Nissan','Mazda','Suzuki','Subaru','Mitsubishi','Daihatsu','Lexus','Volkswagen','BMW','Mercedes-Benz','Audi','Ford','Hyundai','Kia','Land Rover','Mini','Peugeot','Renault'] as $m)<option value="{{ $m }}">@endforeach
            </datalist>
            @error('make')<div class="err">{{ $message }}</div>@enderror
        </div>
        <div>
            <label for="model">Model</label>
            <input id="model" name="model" type="text" value="{{ old('model', $v->model) }}" @class(['is-invalid' => $errors->has('model')]) required>
            @error('model')<div class="err">{{ $message }}</div>@enderror
        </div>
        <div>
            <label for="grade">Paket / donanım</label>
            <input id="grade" name="grade" type="text" value="{{ old('grade', $v->grade) }}" placeholder="ör. Hybrid G, 15S Touring">
        </div>
        <div>
            <label for="model_year">Model yılı</label>
            <input id="model_year" name="model_year" type="number" inputmode="numeric" value="{{ old('model_year', $v->model_year) }}" @class(['is-invalid' => $errors->has('model_year')])>
            @error('model_year')<div class="err">{{ $message }}</div>@enderror
        </div>
        <div>
            <label for="first_registration">İlk kayıt tarihi</label>
            <input id="first_registration" name="first_registration" type="date" value="{{ old('first_registration', $v->first_registration?->format('Y-m-d')) }}">
            <div class="hint">5 yaş ithalat sınırı bu tarihten hesaplanır.</div>
            @error('first_registration')<div class="err">{{ $message }}</div>@enderror
        </div>
        <div>
            <label for="color">Renk</label>
            <input id="color" name="color" type="text" value="{{ old('color', $v->color) }}">
        </div>
        <div>
            <label for="mileage_km">Kilometre</label>
            <input id="mileage_km" name="mileage_km" type="text" inputmode="numeric" value="{{ old('mileage_km', $v->mileage_km) }}" @class(['is-invalid' => $errors->has('mileage_km')])>
            @error('mileage_km')<div class="err">{{ $message }}</div>@enderror
        </div>
        <div>
            <label for="engine_cc">Motor hacmi (cc)</label>
            <input id="engine_cc" name="engine_cc" type="number" inputmode="numeric" value="{{ old('engine_cc', $v->engine_cc) }}" @class(['is-invalid' => $errors->has('engine_cc')])>
            @error('engine_cc')<div class="err">{{ $message }}</div>@enderror
        </div>
        <div>
            <label for="fuel">Yakıt</label>
            <select id="fuel" name="fuel">
                <option value="">—</option>
                @foreach (\App\Models\Vehicle::FUELS as $key => $label)
                    <option value="{{ $key }}" @selected(old('fuel', $v->fuel) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="transmission">Vites</label>
            <select id="transmission" name="transmission">
                <option value="">—</option>
                @foreach (\App\Models\Vehicle::TRANSMISSIONS as $key => $label)
                    <option value="{{ $key }}" @selected(old('transmission', $v->transmission) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </fieldset>

    <fieldset>
        <legend>Alış</legend>
        <div>
            <label for="purchase_date">Alış tarihi</label>
            <input id="purchase_date" name="purchase_date" type="date" value="{{ old('purchase_date', $v->purchase_date?->format('Y-m-d')) }}">
            <div class="hint">Stokta kalma süresi bu tarihten hesaplanır. Fiyat ve masraflar bir sonraki adımda eklenecek.</div>
        </div>
        <div class="full">
            <label for="notes">Notlar</label>
            <textarea id="notes" name="notes">{{ old('notes', $v->notes) }}</textarea>
        </div>
    </fieldset>

    <div class="actions">
        <button class="btn" type="submit">{{ $v->exists ? 'Değişiklikleri kaydet' : 'Aracı kaydet' }}</button>
    </div>
</form>

<script>
(function () {
    var ch = document.getElementById('chassis_no'), or = document.getElementById('origin'), hint = document.getElementById('mense-hint');
    var base = hint.textContent;
    function guess(v) {
        v = v.replace(/\s+/g, '').toUpperCase();
        if (/^[A-HJ-NPR-Z0-9]{17}$/.test(v)) return ['ingiltere', 'İngiltere (17 haneli VIN)'];
        if (/^[A-Z0-9]{2,10}-\d{4,8}$/.test(v)) return ['japonya', 'Japonya (model kodlu şasi no)'];
        return null;
    }
    ch.addEventListener('input', function () {
        var g = guess(ch.value);
        hint.textContent = g ? 'Menşe tahmini: ' + g[1] : base;
        if (g && !or.dataset.touched) or.value = g[0];
    });
    or.addEventListener('change', function () { or.dataset.touched = '1'; });
})();
</script>
@endsection
