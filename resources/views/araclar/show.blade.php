@extends('layouts.app')
@section('title', $vehicle->title().' · Cardino')
@push('head')
<style>
    .grid2 { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 16px; align-items: start; }
    @media (max-width: 820px) { .grid2 { grid-template-columns: 1fr; } }
    .box { padding: 18px 20px; }
    .box h2 { font-size: 14px; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); margin: 0 0 12px; }
    dl { display: grid; grid-template-columns: max-content 1fr; gap: 8px 18px; margin: 0; }
    dt { color: var(--muted); }
    dd { margin: 0; font-weight: 600; min-width: 0; overflow-wrap: anywhere; }
    .warn { background: #fdf1d6; color: #6b4a00; border-radius: 8px; padding: .6rem .8rem; font-size: 14px; margin-top: 12px; }
    .okbox { background: #e6f4ea; color: #1e6b37; border-radius: 8px; padding: .6rem .8rem; font-size: 14px; margin-top: 12px; }
    .actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .btn-danger { background: transparent; color: var(--danger); border: 1px solid #f1c4c0; }
</style>
@endpush
@section('content')
@php $v = $vehicle; @endphp
<div class="page-head">
    <div>
        <a class="muted" href="{{ route('araclar.index') }}" style="text-decoration:none">← Araçlar</a>
        <h1 style="margin-top:4px">{{ $v->title() }}</h1>
        <div style="margin-top:6px"><span class="pill s-{{ $v->status }}">{{ $v->label('status') }}</span> <span class="mono muted">{{ $v->chassis_no }}</span></div>
    </div>
    <div class="actions">
        <a class="btn" href="{{ route('araclar.edit', $v) }}">Düzenle</a>
        <form method="POST" action="{{ route('araclar.destroy', $v) }}" onsubmit="return confirm('Bu araç silinsin mi?')">
            @csrf @method('DELETE')
            <button class="btn btn-danger" type="submit">Sil</button>
        </form>
    </div>
</div>

<div class="grid2">
    <section class="panel box">
        <h2>Araç bilgileri</h2>
        <dl>
            <dt>Şasi no</dt><dd class="mono">{{ $v->chassis_no }}</dd>
            <dt>Motor no</dt><dd class="mono">{{ $v->engine_no ?: '—' }}</dd>
            <dt>Menşe</dt><dd>{{ $v->label('origin') }}</dd>
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

    <div style="display:grid;gap:16px">
        <section class="panel box">
            <h2>Tarihler</h2>
            <dl>
                <dt>İlk kayıt</dt><dd>{{ $v->first_registration?->format('d.m.Y') ?? '—' }}</dd>
                <dt>Alış</dt><dd>{{ $v->purchase_date?->format('d.m.Y') ?? '—' }}</dd>
                <dt>Stokta</dt><dd class="num">{{ $v->daysInStock() !== null ? $v->daysInStock().' gün' : '—' }}</dd>
            </dl>
            @if ($v->first_registration)
                @php $deadline = $v->importDeadline(); @endphp
                @if (in_array($v->status, ['yolda', 'gumrukte']))
                    @if ($deadline->isPast())
                        <div class="warn">5 yaş sınırı {{ $deadline->format('d.m.Y') }} tarihinde doldu. Araç gümrükten çekilemeyebilir.</div>
                    @elseif ($deadline->diffInDays(today()) <= 90)
                        <div class="warn">5 yaş sınırına {{ (int) today()->diffInDays($deadline) }} gün kaldı ({{ $deadline->format('d.m.Y') }}).</div>
                    @else
                        <div class="okbox">5 yaş sınırı: {{ $deadline->format('d.m.Y') }}</div>
                    @endif
                @endif
            @endif
        </section>
        <section class="panel box">
            <h2>Maliyet ve satış</h2>
            <p class="muted" style="margin:0">Alış fiyatı, navlun, gümrük ve masraflar bir sonraki adımda bu karta eklenecek.</p>
        </section>
    </div>
</div>
@endsection
