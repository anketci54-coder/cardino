@extends('layouts.base')
@push('head')
<style>
    .top { background: var(--brand); color: #fff; }
    .top-in { max-width: 1200px; margin: 0 auto; display: flex; align-items: center; gap: 20px; flex-wrap: wrap;
              padding: 12px max(16px, env(safe-area-inset-right, 0px)) 12px max(16px, env(safe-area-inset-left, 0px)); }
    .top .wordmark { color: #fff; font-size: 22px; text-decoration: none; }
    .nav { display: flex; gap: 4px; flex: 1; flex-wrap: wrap; }
    .nav a { color: #c9d5e3; text-decoration: none; padding: .4rem .7rem; border-radius: 6px; font-weight: 600; font-size: 14px; }
    .nav a:hover, .nav a.on { color: #fff; background: rgba(255,255,255,.12); }
    .who { display: flex; align-items: center; gap: 12px; font-size: 14px; }
    .who small { color: #b9c6d6; display: block; }
    .who .btn-ghost { color: #fff; border-color: rgba(255,255,255,.35); padding: .4rem .75rem; }
    .page { max-width: 1200px; margin: 0 auto; padding: 24px max(16px, env(safe-area-inset-right, 0px)) 48px max(16px, env(safe-area-inset-left, 0px)); }
    .page-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }
    h1 { font-size: 24px; margin: 0; text-wrap: balance; }
    .muted { color: var(--muted); }
    .flash { background: #e8f5ec; color: #14532d; border: 1px solid #b7e1c3; border-radius: 8px; padding: .6rem .9rem; margin-bottom: 16px; }
    .panel { background: var(--surface); border: 1px solid var(--line); border-radius: 12px; }
    .pill { display: inline-block; font-size: 12px; font-weight: 700; border-radius: 999px; padding: 2px 10px; white-space: nowrap; }
    .s-yolda { background: #e7f0fb; color: #1d4f8a; } .s-gumrukte { background: #fdf1d6; color: #7a5300; }
    .s-stokta { background: #e6f4ea; color: #1e6b37; } .s-satildi { background: #eceef1; color: #4a5361; }
    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 13.5px; }
    .num { font-variant-numeric: tabular-nums; }
</style>
@endpush
@section('body')
<header class="top">
    <div class="top-in">
        <a class="wordmark" href="{{ route('panel') }}">Car<span>d</span>ino</a>
        <nav class="nav">
            <a href="{{ route('panel') }}" @class(['on' => request()->routeIs('panel')])>Panel</a>
            <a href="{{ route('araclar.index') }}" @class(['on' => request()->routeIs('araclar.*')])>Araçlar</a>
        </nav>
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
<main class="page">
    @if (session('ok'))
        <div class="flash" role="status">{{ session('ok') }}</div>
    @endif
    @yield('content')
</main>
@endsection
