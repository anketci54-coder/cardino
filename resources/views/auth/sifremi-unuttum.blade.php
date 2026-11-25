@extends('layouts.base')
@section('title', 'Şifremi unuttum · Cardino')
@push('head')
<style>
    .wrap { min-height: 100vh; display: grid; place-items: center; padding: 24px 16px; }
    .card { width: 100%; max-width: 380px; background: var(--surface); border: 1px solid var(--line); border-radius: 14px; padding: 32px 28px; }
    .head { margin-bottom: 20px; }
    .head .wordmark { font-size: 28px; }
    .head p { margin: 4px 0 0; color: var(--muted); font-size: 14px; }
    form { display: grid; gap: 16px; }
    .btn { width: 100%; }
    .alt { margin-top: 18px; font-size: 14px; }
    .okbox { background: #e6f4ea; color: #1e6b37; border-radius: 8px; padding: .6rem .8rem; font-size: 14px; }
    input[type=email] { width: 100%; padding: .7rem .8rem; border: 1px solid var(--line); border-radius: 8px; font: 16px var(--font); background: #fff; color: var(--ink); }
</style>
@endpush
@section('body')
<main class="wrap">
    <div class="card">
        <div class="head">
            <div class="wordmark">Car<span>d</span>ino</div>
            <p>Şifre sıfırlama</p>
        </div>

        @if (session('ok'))
            <div class="okbox" role="status">{{ session('ok') }}</div>
        @else
            <form method="POST" action="{{ route('password.email') }}" novalidate>
                @csrf
                @if ($errors->any())
                    <div class="error" role="alert">{{ $errors->first() }}</div>
                @endif
                <p style="margin:0;color:var(--muted);font-size:14px">
                    Hesabınızın e-posta adresini yazın; şifre sıfırlama bağlantısını göndereceğiz.
                </p>
                <div>
                    <label for="email">E-posta</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" autocapitalize="none" required autofocus>
                </div>
                <button class="btn" type="submit">Bağlantı gönder</button>
            </form>
        @endif

        <div class="alt"><a href="{{ route('login') }}">← Girişe dön</a></div>
    </div>
</main>
@endsection
