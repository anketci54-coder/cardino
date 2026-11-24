@extends('layouts.app')
@section('title', 'Araçlar · Cardino')
@push('head')
<style>
    .chips { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; }
    .chip { text-decoration: none; color: var(--ink); border: 1px solid var(--line); background: var(--surface); border-radius: 999px;
            padding: .3rem .8rem; font-size: 14px; }
    .chip b { font-variant-numeric: tabular-nums; margin-left: 4px; }
    .chip.on { border-color: var(--brand); box-shadow: inset 0 0 0 1px var(--brand); }
    .filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; }
    .filters input[type=text] { flex: 1 1 260px; width: auto; }
    select { padding: .65rem .7rem; border: 1px solid var(--line); border-radius: 8px; font: 15px var(--font); background: #fff; color: var(--ink); }
    .tbl-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 760px; }
    th, td { text-align: left; padding: .7rem .9rem; border-bottom: 1px solid var(--line); vertical-align: middle; }
    th { font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); font-weight: 700; background: #fafbfc; }
    tr:last-child td { border-bottom: 0; }
    td a.t { font-weight: 700; color: var(--ink); text-decoration: none; }
    td a.t:hover { text-decoration: underline; }
    .empty { padding: 40px 20px; text-align: center; }
    .pager { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; gap: 12px; flex-wrap: wrap; }
</style>
@endpush
@section('content')
<div class="page-head">
    <div>
        <h1>Araçlar</h1>
        <div class="muted">{{ $vehicles->total() }} kayıt</div>
    </div>
    <a class="btn" href="{{ route('araclar.create') }}">+ Araç ekle</a>
</div>

<div class="chips">
    <a class="chip @if(!request('durum')) on @endif" href="{{ route('araclar.index', request()->except('durum', 'page')) }}">Tümü<b>{{ $counts->sum() }}</b></a>
    @foreach (\App\Models\Vehicle::STATUSES as $key => $label)
        <a class="chip @if(request('durum') === $key) on @endif" href="{{ route('araclar.index', array_merge(request()->except('page'), ['durum' => $key])) }}">{{ $label }}<b>{{ $counts[$key] ?? 0 }}</b></a>
    @endforeach
</div>

<form class="filters" method="GET" action="{{ route('araclar.index') }}">
    @if(request('durum'))<input type="hidden" name="durum" value="{{ request('durum') }}">@endif
    <input id="q" type="text" name="q" value="{{ $q }}" placeholder="Şasi no, motor no, marka veya model ara" aria-label="Ara">
    <select id="mense" name="mense" aria-label="Menşe">
        <option value="">Tüm menşeler</option>
        @foreach (\App\Models\Vehicle::ORIGINS as $key => $label)
            <option value="{{ $key }}" @selected(request('mense') === $key)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="btn" type="submit">Ara</button>
</form>

<div class="panel tbl-wrap">
    @if ($vehicles->isEmpty())
        <div class="empty">
            <p><strong>Henüz araç yok.</strong></p>
            <p class="muted">İlk aracı eklemek için “Araç ekle”ye basın. Şasi numarasını yazınca menşe otomatik seçilir.</p>
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Araç</th><th>Şasi no</th><th>Menşe</th><th class="num">Km</th><th>Durum</th><th class="num">Stokta</th>
                    @if(auth()->user()->isAdmin())<th>Galeri</th>@endif
                </tr>
            </thead>
            <tbody>
            @foreach ($vehicles as $v)
                <tr>
                    <td><a class="t" href="{{ route('araclar.show', $v) }}">{{ $v->title() }}</a>@if($v->color)<div class="muted" style="font-size:13px">{{ $v->color }}</div>@endif</td>
                    <td class="mono">{{ $v->chassis_no }}</td>
                    <td>{{ $v->label('origin') }}</td>
                    <td class="num">{{ $v->mileage_km !== null ? number_format($v->mileage_km, 0, ',', '.') : '—' }}</td>
                    <td><span class="pill s-{{ $v->status }}">{{ $v->label('status') }}</span></td>
                    <td class="num">{{ $v->daysInStock() !== null ? $v->daysInStock().' gün' : '—' }}</td>
                    @if(auth()->user()->isAdmin())<td>{{ $v->gallery?->name }}</td>@endif
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>

@if ($vehicles->hasPages())
<div class="pager">
    <span class="muted">Sayfa {{ $vehicles->currentPage() }} / {{ $vehicles->lastPage() }}</span>
    <span style="display:flex;gap:8px">
        @if ($vehicles->previousPageUrl())<a class="btn btn-ghost" href="{{ $vehicles->previousPageUrl() }}">← Önceki</a>@endif
        @if ($vehicles->nextPageUrl())<a class="btn btn-ghost" href="{{ $vehicles->nextPageUrl() }}">Sonraki →</a>@endif
    </span>
</div>
@endif
@endsection
