@extends('layouts.app')
@section('title', $vehicle->title().' · Cardino')
@php use App\Support\Para; $v = $vehicle; $toplam = $v->totalCostBase(); $kar = $v->profitBase(); @endphp
@push('head')
<style>
    .grid2 { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 16px; align-items: start; }
    @media (max-width: 820px) { .grid2 { grid-template-columns: 1fr; } }
    .stack { display: grid; gap: 16px; }
    .box { padding: 18px 20px; }
    .box h2 { font-size: 14px; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); margin: 0 0 12px; }
    dl { display: grid; grid-template-columns: max-content 1fr; gap: 8px 18px; margin: 0; }
    dt { color: var(--muted); }
    dd { margin: 0; font-weight: 600; min-width: 0; overflow-wrap: anywhere; }
    .warn { background: #fdf1d6; color: #6b4a00; border-radius: 8px; padding: .6rem .8rem; font-size: 14px; margin-top: 12px; }
    .okbox { background: #e6f4ea; color: #1e6b37; border-radius: 8px; padding: .6rem .8rem; font-size: 14px; margin-top: 12px; }
    .actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .btn-danger { background: transparent; color: var(--danger); border: 1px solid #f1c4c0; }
    .tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 16px; }
    .tile { border: 1px solid var(--line); border-radius: 10px; padding: 14px 16px; }
    .tile .k { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; }
    .tile .val { font-size: 22px; font-weight: 800; font-variant-numeric: tabular-nums; margin-top: 4px; }
    .tile.good .val { color: #1e6b37; } .tile.bad .val { color: var(--danger); }
    .ctbl { width: 100%; border-collapse: collapse; }
    .ctbl th, .ctbl td { text-align: left; padding: .55rem .6rem; border-bottom: 1px solid var(--line); font-size: 14px; }
    .ctbl th { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); }
    .ctbl td.r, .ctbl th.r { text-align: right; font-variant-numeric: tabular-nums; }
    .ctbl .del { background: none; border: 0; color: var(--danger); cursor: pointer; font-size: 18px; line-height: 1; padding: 0 4px; }
    .addrow { display: grid; grid-template-columns: 1.4fr 1fr .8fr 1fr auto; gap: 8px; margin-top: 14px; align-items: end; }
    @media (max-width: 700px) { .addrow { grid-template-columns: 1fr 1fr; } }
    .addrow label { font-size: 12px; color: var(--muted); margin-bottom: 3px; }
    .addrow input, .addrow select { width: 100%; padding: .55rem .6rem; border: 1px solid var(--line); border-radius: 8px; font: 15px var(--font); background: #fff; color: var(--ink); }
    .addrow .full { grid-column: 1 / -1; }
    .rate-note { font-size: 12px; color: var(--muted); margin-top: 6px; }
    .err { font-size: 13px; color: var(--danger); margin-top: 6px; }
    .salegrid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; align-items: end; }
    .salegrid label { font-size: 12px; color: var(--muted); margin-bottom: 3px; display: block; }
    .salegrid input, .salegrid select { width: 100%; padding: .6rem .7rem; border: 1px solid var(--line); border-radius: 8px; font: 15px var(--font); background: #fff; color: var(--ink); }
    .checkrow { display: flex; align-items: center; gap: .5rem; font-size: 14px; }
    .fotos { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; margin-bottom: 8px; }
    .foto { position: relative; border: 1px solid var(--line); border-radius: 10px; overflow: hidden; aspect-ratio: 4 / 3; background: #f4f3f0; }
    .foto a { display: block; width: 100%; height: 100%; }
    .foto img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .foto .del { position: absolute; top: 6px; right: 6px; width: 26px; height: 26px; border-radius: 50%; border: 0; background: rgba(0,0,0,.55); color: #fff; cursor: pointer; font-size: 16px; line-height: 26px; padding: 0; }
    .docs { list-style: none; margin: 0 0 8px; padding: 0; display: grid; gap: 8px; }
    .docs li { display: flex; align-items: center; gap: 10px; border: 1px solid var(--line); border-radius: 8px; padding: .55rem .7rem; }
    .docs .ico { font-size: 18px; }
    .docs .meta { flex: 1; min-width: 0; }
    .docs .meta a { font-weight: 600; text-decoration: none; overflow-wrap: anywhere; }
    .docs .meta .sub { font-size: 12px; color: var(--muted); }
    .docs .del { background: none; border: 0; color: var(--danger); cursor: pointer; font-size: 18px; padding: 0 4px; }
    .uprow { display: grid; grid-template-columns: auto 1fr auto; gap: 8px; align-items: end; margin-top: 10px; }
    @media (max-width: 700px) { .uprow { grid-template-columns: 1fr; } }
    .uprow label { font-size: 12px; color: var(--muted); margin-bottom: 3px; display: block; }
    .uprow select, .uprow input[type=file] { width: 100%; padding: .5rem .6rem; border: 1px solid var(--line); border-radius: 8px; font: 14px var(--font); background: #fff; color: var(--ink); }
    .subhead { font-size: 13px; color: var(--muted); font-weight: 600; margin: 4px 0 8px; }
</style>
@endpush
@section('content')
<div class="page-head">
    <div>
        <a class="muted" href="{{ route('araclar.index') }}" style="text-decoration:none">← Araçlar</a>
        <h1 style="margin-top:4px">{{ $v->title() }}</h1>
        <div style="margin-top:6px"><span class="pill s-{{ $v->status }}">{{ $v->label('status') }}</span>
            @if($v->is_trade_in)<span class="pill s-satildi">Takasla geldi</span>@endif
            <span class="mono muted">{{ $v->chassis_no }}</span></div>
    </div>
    <div class="actions">
        <a class="btn" href="{{ route('araclar.edit', $v) }}">Düzenle</a>
        <form method="POST" action="{{ route('araclar.destroy', $v) }}" onsubmit="return confirm('Bu araç silinsin mi?')">
            @csrf @method('DELETE')
            <button class="btn btn-danger" type="submit">Sil</button>
        </form>
    </div>
</div>

@if ($errors->any())
    <div class="error" role="alert" style="margin-bottom:14px">{{ $errors->first() }}</div>
@endif

<div class="grid2">
    <section class="panel box">
        <h2>Araç bilgileri</h2>
        <dl>
            <dt>Şasi no</dt><dd class="mono">{{ $v->chassis_no }}</dd>
            <dt>Motor no</dt><dd class="mono">{{ $v->engine_no ?: '—' }}</dd>
            <dt>Menşe</dt><dd>{{ $v->label('origin') }}@if($v->shipping_route) · {{ $v->label('shipping_route') }}@endif</dd>
            <dt>Marka / model</dt><dd>{{ $v->make }} {{ $v->model }}</dd>
            <dt>Paket</dt><dd>{{ $v->grade ?: '—' }}</dd>
            <dt>Model yılı</dt><dd>{{ $v->model_year ?: '—' }}</dd>
            <dt>Renk</dt><dd>{{ $v->color ?: '—' }}</dd>
            <dt>Kilometre</dt><dd class="num">{{ $v->mileage_km !== null ? number_format($v->mileage_km, 0, ',', '.').' km' : '—' }}</dd>
            <dt>Motor</dt><dd>{{ $v->engine_cc ? number_format($v->engine_cc, 0, ',', '.').' cc' : '—' }} · {{ $v->label('fuel') }} · {{ $v->label('transmission') }}</dd>
            @if(auth()->user()->isAdmin())<dt>Galeri</dt><dd>{{ $v->gallery?->name }}</dd>@endif
        </dl>
        @if ($v->notes)
            <h2 style="margin-top:20px">Notlar</h2>
            <p style="margin:0;white-space:pre-line">{{ $v->notes }}</p>
        @endif
    </section>

    <div class="stack">
        <section class="panel box">
            <h2>Tarihler</h2>
            <dl>
                <dt>İlk kayıt</dt><dd>{{ $v->first_registration?->format('d.m.Y') ?? '—' }}</dd>
                <dt>Alış</dt><dd>{{ $v->purchase_date?->format('d.m.Y') ?? '—' }}</dd>
                <dt>{{ $v->isSold() ? 'Satış' : 'Stokta' }}</dt>
                <dd class="num">{{ $v->isSold() ? $v->sold_on?->format('d.m.Y') : ($v->daysInStock() !== null ? $v->daysInStock().' gün' : '—') }}</dd>
            </dl>
            @if ($v->first_registration && in_array($v->status, ['yolda', 'gumrukte']))
                @php $deadline = $v->importDeadline(); @endphp
                @if ($deadline->isPast())
                    <div class="warn">5 yaş sınırı {{ $deadline->format('d.m.Y') }} tarihinde doldu. Araç gümrükten çekilemeyebilir.</div>
                @elseif (today()->diffInDays($deadline) <= 90)
                    <div class="warn">5 yaş sınırına {{ (int) today()->diffInDays($deadline) }} gün kaldı ({{ $deadline->format('d.m.Y') }}).</div>
                @else
                    <div class="okbox">5 yaş sınırı: {{ $deadline->format('d.m.Y') }}</div>
                @endif
            @endif
        </section>
    </div>
</div>

@php $fotos = $v->photos(); $belgeler = $v->documents(); @endphp
<section class="panel box" style="margin-top:16px">
    <h2>Fotoğraflar ve belgeler</h2>

    <div class="subhead">Fotoğraflar</div>
    @if ($fotos->isNotEmpty())
        <div class="fotos">
            @foreach ($fotos as $f)
                <div class="foto">
                    <a href="{{ route('dosya.goster', [$v, $f]) }}" target="_blank" rel="noopener">
                        <img src="{{ route('dosya.goster', [$v, $f]) }}" alt="{{ $f->original_name }}" loading="lazy">
                    </a>
                    <form method="POST" action="{{ route('dosya.destroy', [$v, $f]) }}" onsubmit="return confirm('Bu fotoğraf silinsin mi?')">
                        @csrf @method('DELETE')
                        <button class="del" type="submit" aria-label="Sil" title="Sil">×</button>
                    </form>
                </div>
            @endforeach
        </div>
    @else
        <p class="muted" style="margin:0 0 8px">Henüz fotoğraf yok.</p>
    @endif

    <div class="subhead" style="margin-top:14px">Belgeler</div>
    @if ($belgeler->isNotEmpty())
        <ul class="docs">
            @foreach ($belgeler as $f)
                <li>
                    <span class="ico">{{ $f->isImage() ? '🖼️' : '📄' }}</span>
                    <span class="meta">
                        <a href="{{ route('dosya.goster', [$v, $f]) }}" target="_blank" rel="noopener">{{ $f->original_name }}</a>
                        <div class="sub">{{ $f->docTypeLabel() }} · {{ $f->humanSize() }} · {{ $f->created_at->format('d.m.Y') }}</div>
                    </span>
                    <form method="POST" action="{{ route('dosya.destroy', [$v, $f]) }}" onsubmit="return confirm('Bu belge silinsin mi?')">
                        @csrf @method('DELETE')
                        <button class="del" type="submit" aria-label="Sil" title="Sil">×</button>
                    </form>
                </li>
            @endforeach
        </ul>
    @else
        <p class="muted" style="margin:0 0 8px">Henüz belge yok.</p>
    @endif

    <form method="POST" action="{{ route('dosya.store', $v) }}" enctype="multipart/form-data" id="dosya-form">
        @csrf
        <div class="uprow">
            <div>
                <label for="kind">Tür</label>
                <select id="kind" name="kind">
                    <option value="foto">Fotoğraf</option>
                    <option value="belge">Belge</option>
                </select>
            </div>
            <div id="doctype-wrap" style="display:none">
                <label for="doc_type">Belge türü</label>
                <select id="doc_type" name="doc_type">
                    @foreach (\App\Models\VehicleFile::DOC_TYPES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div id="file-wrap" style="grid-column: span 1;">
                <label for="files">Dosyalar (JPG, PNG, PDF · en fazla 15 MB)</label>
                <input id="files" name="files[]" type="file" multiple accept="image/*,application/pdf" required>
            </div>
            <div>
                <button class="btn" type="submit">Yükle</button>
            </div>
        </div>
    </form>
</section>

<section class="panel box" style="margin-top:16px">
    <h2>Maliyet ve kâr</h2>
    <div class="tiles">
        <div class="tile"><div class="k">Toplam maliyet</div><div class="val">{{ Para::format($toplam, 'GBP') }}</div></div>
        <div class="tile"><div class="k">Satış</div><div class="val">{{ $v->isSold() ? Para::format($v->sale_base, 'GBP') : '—' }}</div></div>
        <div class="tile {{ $kar === null ? '' : ($kar >= 0 ? 'good' : 'bad') }}">
            <div class="k">Kâr / zarar</div>
            <div class="val">{{ $kar === null ? '—' : Para::format($kar, 'GBP') }}</div>
        </div>
    </div>

    @if ($v->costs->isNotEmpty())
    <table class="ctbl">
        <thead><tr><th>Masraf</th><th>Tutar</th><th class="r">GBP</th><th>Tarih</th><th></th></tr></thead>
        <tbody>
        @foreach ($v->costs as $c)
            <tr>
                <td>{{ $c->categoryLabel() }}@if($c->description)<div class="muted" style="font-size:13px">{{ $c->description }}</div>@endif</td>
                <td>{{ Para::format($c->amount, $c->currency) }}@if($c->currency !== 'GBP')<div class="muted" style="font-size:12px">kur {{ rtrim(rtrim(number_format($c->rate, 6, ',', '.'), '0'), ',') }}</div>@endif</td>
                <td class="r">{{ Para::format($c->base_amount, 'GBP') }}</td>
                <td>{{ $c->spent_on?->format('d.m.Y') ?? '—' }}</td>
                <td class="r">
                    <form method="POST" action="{{ route('maliyet.destroy', [$v, $c]) }}" onsubmit="return confirm('Bu masraf silinsin mi?')">
                        @csrf @method('DELETE')
                        <button class="del" type="submit" aria-label="Sil" title="Sil">×</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot><tr><th>Toplam</th><th></th><th class="r">{{ Para::format($toplam, 'GBP') }}</th><th></th><th></th></tr></tfoot>
    </table>
    @else
        <p class="muted" style="margin:0 0 4px">Henüz masraf eklenmedi. Alış fiyatı, navlun, gümrük ve diğer masrafları aşağıdan ekleyin.</p>
    @endif

    <form method="POST" action="{{ route('maliyet.store', $v) }}" id="maliyet-form">
        @csrf
        <div class="addrow">
            <div>
                <label for="category">Masraf türü</label>
                <select id="category" name="category" required>
                    @foreach (\App\Models\VehicleCost::CATEGORIES as $key => $label)
                        <option value="{{ $key }}" @selected(old('category')===$key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="amount">Tutar</label>
                <input id="amount" name="amount" type="text" inputmode="decimal" value="{{ old('amount') }}" required>
            </div>
            <div>
                <label for="currency">Para</label>
                <select id="currency" name="currency">
                    @foreach (\App\Support\Para::CURRENCIES as $key => $label)
                        <option value="{{ $key }}" @selected(old('currency', 'GBP')===$key)>{{ $key }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="spent_on">Tarih</label>
                <input id="spent_on" name="spent_on" type="date" value="{{ old('spent_on') }}">
            </div>
            <div>
                <button class="btn" type="submit">Ekle</button>
            </div>
            <div class="full" style="display:grid;grid-template-columns:2fr 1fr;gap:8px">
                <div>
                    <label for="description">Açıklama (isteğe bağlı)</label>
                    <input id="description" name="description" type="text" value="{{ old('description') }}">
                </div>
                <div>
                    <label for="rate">Kur (1 birim = ? GBP)</label>
                    <input id="rate" name="rate" type="text" inputmode="decimal" value="{{ old('rate') }}" placeholder="otomatik">
                </div>
            </div>
        </div>
        <div class="rate-note" id="rate-note"></div>
    </form>
</section>

<section class="panel box" style="margin-top:16px">
    <h2>Satış</h2>
    @if ($v->isSold())
        <dl>
            <dt>Satış fiyatı</dt><dd>{{ Para::format($v->sale_amount, $v->sale_currency) }} @if($v->sale_currency !== 'GBP')<span class="muted" style="font-weight:400">({{ Para::format($v->sale_base, 'GBP') }})</span>@endif</dd>
            <dt>Tarih</dt><dd>{{ $v->sold_on?->format('d.m.Y') }}</dd>
            <dt>Alıcı</dt><dd>{{ $v->buyer_name ?: '—' }}</dd>
            <dt>Kâr / zarar</dt><dd style="color:{{ $kar >= 0 ? '#1e6b37' : 'var(--danger)' }}">{{ Para::format($kar, 'GBP') }}</dd>
        </dl>
        <form method="POST" action="{{ route('satis.destroy', $v) }}" style="margin-top:12px" onsubmit="return confirm('Satış kaydı geri alınsın mı? Araç stoğa döner.')">
            @csrf @method('DELETE')
            <button class="btn btn-ghost" type="submit">Satışı geri al</button>
        </form>
    @else
        <form method="POST" action="{{ route('satis.store', $v) }}">
            @csrf
            <div class="salegrid">
                <div>
                    <label for="sale_amount">Satış fiyatı</label>
                    <input id="sale_amount" name="sale_amount" type="text" inputmode="decimal" value="{{ old('sale_amount') }}" required>
                </div>
                <div>
                    <label for="sale_currency">Para</label>
                    <select id="sale_currency" name="sale_currency">
                        @foreach (\App\Support\Para::CURRENCIES as $key => $label)
                            <option value="{{ $key }}" @selected(old('sale_currency', 'GBP')===$key)>{{ $key }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="sold_on">Satış tarihi</label>
                    <input id="sold_on" name="sold_on" type="date" value="{{ old('sold_on', date('Y-m-d')) }}" required>
                </div>
                <div>
                    <label for="buyer_name">Alıcı (isteğe bağlı)</label>
                    <input id="buyer_name" name="buyer_name" type="text" value="{{ old('buyer_name') }}">
                </div>
                <div>
                    <button class="btn" type="submit">Satışı kaydet</button>
                </div>
            </div>
            <label class="checkrow" style="margin-top:12px">
                <input type="checkbox" name="is_trade_in" value="1" @checked(old('is_trade_in'))>
                Bu araç karşılığında takas araç alındı
            </label>
        </form>
    @endif
</section>

<script>
(function () {
    var rates = @json($rates);
    var cur = document.getElementById('currency'), rate = document.getElementById('rate'), note = document.getElementById('rate-note');
    function refresh() {
        var c = cur.value;
        if (c === 'GBP') { note.textContent = 'Sterlin için kur gerekmez.'; return; }
        var r = rates[c];
        if (r && r.rate) {
            note.textContent = '1 ' + c + ' ≈ ' + r.rate + ' GBP' + (r.as_of ? ' (' + r.as_of + ')' : '') + '. Boş bırakırsanız bu kur kullanılır; farklıysa elle yazın.';
        } else {
            note.textContent = c + ' için kayıtlı kur yok. Kuru elle yazın.';
        }
    }
    cur.addEventListener('change', refresh);
    refresh();
})();
(function () {
    var kind = document.getElementById('kind'), wrap = document.getElementById('doctype-wrap'),
        accept = document.getElementById('files');
    function refresh() {
        var belge = kind.value === 'belge';
        wrap.style.display = belge ? '' : 'none';
        accept.setAttribute('accept', belge ? 'image/*,application/pdf' : 'image/*');
    }
    kind.addEventListener('change', refresh);
    refresh();
})();
</script>
@endsection
