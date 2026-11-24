@extends('layouts.base')
@section('title', 'Giriş · Cardino')
@push('head')
<style>
    .wrap { min-height: 100vh; display: grid; place-items: center; padding: 24px 16px; }
    .card { width: 100%; max-width: 380px; background: var(--surface); border: 1px solid var(--line); border-radius: 14px; padding: 32px 28px; }
    .head { margin-bottom: 24px; }
    .head .wordmark { font-size: 28px; }
    .head p { margin: 4px 0 0; color: var(--muted); font-size: 14px; }
    form { display: grid; gap: 16px; }
    .row { display: flex; align-items: center; gap: .5rem; color: var(--muted); font-size: 14px; }
    .row label { margin: 0; font-weight: 400; }
    .btn { width: 100%; }
    .plate { display: inline-block; margin-top: 18px; font: 600 12px var(--font); letter-spacing: .12em; color: var(--accent-ink);
             background: var(--accent); border-radius: 4px; padding: 2px 8px; }
</style>
@endpush
@section('body')
<main class="wrap">
    <div class="card">
        <div class="head">
            <div class="wordmark">Car<span>d</span>ino</div>
            <p>Galeri yönetim paneli</p>
        </div>

        <form method="POST" action="{{ url('/giris') }}" novalidate>
            @csrf
            @if ($errors->any())
                <div class="error" role="alert">{{ $errors->first() }}</div>
            @endif
            <div>
                <label for="username">Kullanıcı adı</label>
                <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" autocapitalize="none" required autofocus>
            </div>
            <div>
                <label for="password">Şifre</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <div class="row">
                <input id="remember" name="remember" type="checkbox" value="1">
                <label for="remember">Beni hatırla</label>
            </div>
            <button class="btn" type="submit">Giriş yap</button>
        </form>
        <span class="plate">KKTC</span>
    </div>
</main>
@endsection
