@extends('layouts.app')
@section('title', 'Fırsatlar · Cardino')
@php use App\Support\Para; use App\Models\Firsat; @endphp
@push('head')
<style>
    table.tbl { width: 100%; border-collapse: collapse; }
    table.tbl th, table.tbl td { padding: .65rem .6rem; border-bottom: 1px solid var(--line); font-size: 14px; text-align: left; vertical-align: middle; }
    table.tbl th { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); }
    .num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    a.t { font-weight: 700; color: var(--ink); text-decoration: none; }
    .v-pill { font-weight: 800; font-size: 12px; padding: .2rem .6rem; border-radius: 999px; }
    .v-al { background:#dcfce7; color:#166534; } .v-gec { background:#fee2e2; color:#991b1b; } .v-dusun { background:#fef3c7; color:#92400e; }
    .chips { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; }
    .chip { text-decoration: none; border: 1px solid var(--line); border-radius: 999px; padding: .35rem .8rem; font-size: 13px; color: var(--ink); }
    .chip.on { background: var(--brand); color: #fff; border-color: var(--brand); }
    .empty { text-align: center; padding: 48px 16px; }
</style>
@endpush
@section('content')
<div class="page-head">
    <div><h1>Fırsatlar</h1><div class="muted">{{ $firsatlar->total() }} aday araç</div></div>
    <a class="btn" href="{{ route('firsat.create') }}">+ Yeni fırsat</a>
</div>

@if (session('ok'))<div class="okbox" style="margin-bottom:14px">{{ session('ok') }}</div>@endif

<div class="chips">
    <a class="chip @if(!request('karar') && !request('durum')) on @endif" href="{{ route('firsat.index') }}">Tümü</a>
    @foreach (Firsat::VERDICTS as $k => $v)
        <a class="chip @if(request('karar')===$k) on @endif" href="{{ route('firsat.index', ['karar' => $k]) }}">{{ $v }}</a>
    @endforeach
    @foreach (Firsat::STATUSES as $k => $v)
        <a class="chip @if(request('durum')===$k) on @endif" href="{{ route('firsat.index', ['durum' => $k]) }}">{{ $v }}</a>
    @endforeach
</div>

<section class="panel box" style="padding:0;overflow:hidden">
    @if ($firsatlar->isEmpty())
        <div class="empty">
            <p class="muted">Henüz fırsat yok. Açık artırmada baktığın aracı “Yeni fırsat” ile değerlendir.</p>
        </div>
    @else
        <table class="tbl">
            <thead><tr><th>Aday araç</th><th>Menşe</th><th class="num">Maliyet</th><th class="num">Bek. kâr</th><th>5 yaş</th><th>Karar</th><th>Durum</th></tr></thead>
            <tbody>
            @foreach ($firsatlar as $f)
                <tr>
                    <td><a class="t" href="{{ route('firsat.show', $f) }}">{{ $f->baslik() }}</a></td>
                    <td>{{ $f->label('origin') }}</td>
                    <td class="num">{{ Para::format($f->landed_cost, 'GBP') }}</td>
                    <td class="num">{{ $f->expected_profit !== null ? Para::format($f->expected_profit, 'GBP').($f->margin!==null ? ' ('.rtrim(rtrim(number_format($f->margin,1,',','.'),'0'),',').'%)' : '') : '—' }}</td>
                    <td>{{ $f->importable ? '✓' : '✗ dolmuş' }}</td>
                    <td><span class="v-pill v-{{ $f->verdict }}">{{ $f->verdictLabel() }}</span></td>
                    <td>{{ $f->statusLabel() }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</section>

@if ($firsatlar->hasPages())
<div class="pager" style="margin-top:14px">
    @if ($firsatlar->previousPageUrl())<a class="btn btn-ghost" href="{{ $firsatlar->previousPageUrl() }}">← Önceki</a>@endif
    @if ($firsatlar->nextPageUrl())<a class="btn btn-ghost" href="{{ $firsatlar->nextPageUrl() }}">Sonraki →</a>@endif
</div>
@endif
@endsection
