@extends('layouts.base')
@section('title', 'Panel · Cardino')
@push('head')
<style>
    .top { background: var(--brand); color: #fff; }
    .top-in { max-width: 1100px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 12px;
              padding: 14px max(16px, env(safe-area-inset-left, 0px)); flex-wrap: wrap; }
    .top .wordmark { color: #fff; font-size: 22px; }
    .who { display: flex; align-items: center; gap: 12px; font-size: 14px; }
    .who small { color: #b9c6d6; display: block; }
    .who .btn-ghost { color: #fff; border-color: rgba(255,255,255,.35); padding: .45rem .8rem; }
    main { max-width: 1100px; margin: 0 auto; padding: 28px max(16px, env(safe-area-inset-left, 0px)); }
    h1 { font-size: 24px; margin: 0 0 4px; text-wrap: balance; }
    .lead { color: var(--muted); margin: 0 0 24px; }
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
    .mod { background: var(--surface); border: 1px solid var(--line); border-radius: 12px; padding: 18px; }
    .mod h2 { font-size: 16px; margin: 0 0 6px; }
    .mod p { margin: 0; color: var(--muted); font-size: 14px; }
    .tag { display: inline-block; margin-top: 12px; font-size: 12px; font-weight: 600; color: var(--muted);
           border: 1px solid var(--line); border-radius: 999px; padding: 2px 10px; }
</style>
@endpush
@section('body')
<header class="top">
    <div class="top-in">
        <div class="wordmark">Car<span>d</span>ino</div>
        <div class="who">
            <div>
                {{ auth()->user()->name }}
                <small>{{ auth()->user()->roleLabel() }}@if(auth()->user()->gallery) · {{ auth()->user()->gallery->name }}@endif</small>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-ghost" type="submit">Çıkış</button>
            </form>
        </div>
    </div>
</header>
<main>
    <h1>Hoş geldiniz</h1>
    <p class="lead">Modüller hazırlandıkça burada açılacak.</p>
    <div class="grid">
        <section class="mod">
            <h2>Araç arşivi</h2>
            <p>Araç kartı, alış, navlun, gümrük ve masraflar; satılınca kâr-zarar.</p>
            <span class="tag">Sırada</span>
        </section>
        <section class="mod">
            <h2>Maliyet hesaplayıcı</h2>
            <p>Japonya veya İngiltere'den alınacak aracın KKTC'de gümrüklenmiş maliyeti.</p>
            <span class="tag">Yakında</span>
        </section>
        <section class="mod">
            <h2>Takas değerleme</h2>
            <p>Takasa gelen aracın KKTC piyasasındaki min–max ve ortalama değeri.</p>
            <span class="tag">Yakında</span>
        </section>
    </div>
</main>
@endsection
