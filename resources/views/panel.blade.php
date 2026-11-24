@extends('layouts.app')
@section('title', 'Panel · Cardino')
@push('head')
<style>
    .lead { color: var(--muted); margin: 4px 0 24px; }
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
    .mod { padding: 18px; display: block; color: inherit; text-decoration: none; }
    a.mod:hover { border-color: var(--brand); }
    .mod h2 { font-size: 16px; margin: 0 0 6px; }
    .mod p { margin: 0; color: var(--muted); font-size: 14px; }
    .tag { display: inline-block; margin-top: 12px; font-size: 12px; font-weight: 600; color: var(--muted);
           border: 1px solid var(--line); border-radius: 999px; padding: 2px 10px; }
    .tag.on { color: var(--accent-ink); background: var(--accent); border-color: var(--accent); }
</style>
@endpush
@section('content')
<h1>Hoş geldiniz</h1>
<p class="lead">Modüller hazırlandıkça burada açılacak.</p>
<div class="grid">
    <a class="panel mod" href="{{ route('araclar.index') }}">
        <h2>Araç arşivi</h2>
        <p>Araç kartı, alış, navlun, gümrük ve masraflar; satılınca kâr-zarar.</p>
        <span class="tag on">Açık · araç kartı</span>
    </a>
    <section class="panel mod">
        <h2>Maliyet hesaplayıcı</h2>
        <p>Japonya veya İngiltere'den alınacak aracın KKTC'de gümrüklenmiş maliyeti.</p>
        <span class="tag">Yakında</span>
    </section>
    <section class="panel mod">
        <h2>Takas değerleme</h2>
        <p>Takasa gelen aracın KKTC piyasasındaki min–max ve ortalama değeri.</p>
        <span class="tag">Yakında</span>
    </section>
</div>
@endsection
