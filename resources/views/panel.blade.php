@extends('layouts.app')
@section('title', 'Özet · Cardino')
@php use App\Support\Para; use App\Models\Vehicle; @endphp
@push('head')
<style>
    .lead { color: var(--muted); margin: 4px 0 20px; }
    .tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 14px; margin-bottom: 20px; }
    .tile { padding: 16px 18px; }
    .tile .k { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; }
    .tile .val { font-size: 26px; font-weight: 800; font-variant-numeric: tabular-nums; margin-top: 6px; }
    .tile .sub { font-size: 13px; color: var(--muted); margin-top: 2px; }
    .tile.good .val { color: #1e6b37; } .tile.bad .val { color: var(--danger); }
    .cols { display: grid; grid-template-columns: minmax(0,1fr) minmax(0,1fr); gap: 16px; align-items: start; }
    @media (max-width: 820px) { .cols { grid-template-columns: 1fr; } }
    .box { padding: 18px 20px; }
    .box h2 { font-size: 14px; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); margin: 0 0 14px; }
    .durumlar { display: flex; flex-wrap: wrap; gap: 10px; }
    .durum { display: flex; align-items: center; gap: 8px; border: 1px solid var(--line); border-radius: 999px; padding: 6px 14px; font-size: 14px; }
    .durum b { font-variant-numeric: tabular-nums; }
    .liste { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
    .liste li { display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--line); padding-bottom: 8px; }
    .liste li:last-child { border-bottom: 0; padding-bottom: 0; }
    .liste a { font-weight: 600; text-decoration: none; }
    .liste .meta { color: var(--muted); font-size: 13px; }
    .liste .right { margin-left: auto; text-align: right; white-space: nowrap; }
    .gun { font-weight: 700; font-variant-numeric: tabular-nums; }
    .gun.warn { color: #7a5300; } .gun.bad { color: var(--danger); }
    .empty { color: var(--muted); margin: 0; }
    .mods { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 14px; margin-top: 20px; }
    .mod { padding: 16px 18px; display: block; color: inherit; text-decoration: none; }
    a.mod:hover { border-color: var(--brand); }
    .mod h3 { font-size: 15px; margin: 0 0 4px; } .mod p { margin: 0; color: var(--muted); font-size: 13.5px; }
    .tag { display: inline-block; margin-top: 10px; font-size: 12px; font-weight: 600; color: var(--muted); border: 1px solid var(--line); border-radius: 999px; padding: 2px 10px; }
    .tag.on { color: #1e6b37; background: #e6f4ea; border-color: #b7e1c3; }
</style>
@endpush
@section('content')
<div class="page-head">
    <div>
        <h1>Özet</h1>
        <div class="lead">
            {{ auth()->user()->gallery?->name ?? 'Tüm galeriler' }} · {{ now()->translatedFormat('d F Y') }}
        </div>
    </div>
    <a class="btn" href="{{ route('araclar.create') }}">+ Araç ekle</a>
</div>

<div class="tiles">
    <div class="panel tile">
        <div class="k">Elde</div>
        <div class="val">{{ number_format($eldeAdet, 0, ',', '.') }}</div>
        <div class="sub">satılmamış araç</div>
    </div>
    <div class="panel tile">
        <div class="k">Bağlı sermaye</div>
        <div class="val">{{ Para::format($bagliSermaye, 'GBP') }}</div>
        <div class="sub">eldeki araçların maliyeti</div>
    </div>
    <div class="panel tile">
        <div class="k">Bu ay satış</div>
        <div class="val">{{ number_format($buAyAdet, 0, ',', '.') }}</div>
        <div class="sub">{{ $buAyCiro > 0 ? Para::format($buAyCiro, 'GBP').' ciro' : '—' }}</div>
    </div>
    <div class="panel tile {{ $buAyAdet === 0 ? '' : ($buAyKar >= 0 ? 'good' : 'bad') }}">
        <div class="k">Bu ay kâr</div>
        <div class="val">{{ $buAyAdet === 0 ? '—' : Para::format($buAyKar, 'GBP') }}</div>
        <div class="sub">{{ now()->translatedFormat('F') }}</div>
    </div>
</div>

<section class="panel box" style="margin-bottom:16px">
    <h2>Durum dağılımı</h2>
    <div class="durumlar">
        @foreach (Vehicle::STATUSES as $key => $label)
            <span class="durum"><span class="pill s-{{ $key }}">{{ $label }}</span> <b>{{ (int) ($sayim[$key] ?? 0) }}</b></span>
        @endforeach
    </div>
</section>

<div class="cols">
    <section class="panel box">
        <h2>5 yaş sınırı — dikkat</h2>
        @if ($dikkat->isNotEmpty())
            <ul class="liste">
                @foreach ($dikkat as $v)
                    <li>
                        <div>
                            <a href="{{ route('araclar.show', $v) }}">{{ $v->title() }}</a>
                            <div class="meta">{{ $v->label('status') }} · son: {{ $v->importDeadline()->format('d.m.Y') }}</div>
                        </div>
                        <div class="right">
                            @if ($v->kalan_gun < 0)
                                <span class="gun bad">doldu</span>
                            @else
                                <span class="gun {{ $v->kalan_gun <= 90 ? 'warn' : '' }}">{{ $v->kalan_gun }} gün</span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="empty">Yolda veya gümrükte, sınırı yaklaşan araç yok.</p>
        @endif
    </section>

    <section class="panel box">
        <h2>Son araçlar</h2>
        @if ($sonAraclar->isNotEmpty())
            <ul class="liste">
                @foreach ($sonAraclar as $v)
                    <li>
                        <div>
                            <a href="{{ route('araclar.show', $v) }}">{{ $v->title() }}</a>
                            <div class="meta">{{ $v->chassis_no }}@if(auth()->user()->isAdmin() && $v->gallery) · {{ $v->gallery->name }}@endif</div>
                        </div>
                        <div class="right"><span class="pill s-{{ $v->status }}">{{ $v->label('status') }}</span></div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="empty">Henüz araç eklenmedi. <a href="{{ route('araclar.create') }}">İlk aracı ekleyin.</a></p>
        @endif
    </section>
</div>

<div class="mods">
    <a class="panel mod" href="{{ route('araclar.index') }}">
        <h3>Araç arşivi</h3>
        <p>Araç kartı, masraflar, fotoğraf/belge; satılınca kâr-zarar.</p>
        <span class="tag on">Açık</span>
    </a>
    <section class="panel mod">
        <h3>Maliyet hesaplayıcı</h3>
        <p>Alınacak aracın KKTC'de gümrüklenmiş tahmini maliyeti.</p>
        <span class="tag">Yakında</span>
    </section>
    <section class="panel mod">
        <h3>Takas değerleme</h3>
        <p>Takasa gelen aracın KKTC piyasa değeri (min–max–ortalama).</p>
        <span class="tag">Yakında</span>
    </section>
</div>
@endsection
