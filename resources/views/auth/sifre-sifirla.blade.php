@extends('layouts.base')
@section('title', 'Yeni şifre · Cardino')
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
    input[type=email] { width: 100%; padding: .7rem .8rem; border: 1px solid var(--line); border-radius: 8px; font: 16px var(--font); background: #fff; color: var(--ink); }
</style>
@endpush
@section('body')
<main class="wrap">
    <div class="card">
        <div class="head">
            <div class="wordmark">Car<span>d</span>ino</div>
            <p>Yeni şifre belirleyin</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            @if ($errors->any())
                <div class="error" role="alert">{{ $errors->first() }}</div>
            @endif
            <div>
                <label for="email">E-posta</label>
                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" autocapitalize="none" required>
            </div>
            <div>
                <label for="password">Yeni şifre</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required autofocus>
            </div>
            <div>
                <label for="password_confirmation">Yeni şifre (tekrar)</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>
            <button class="btn" type="submit">Şifreyi güncelle</button>
        </form>

        <div class="alt"><a href="{{ route('login') }}">← Girişe dön</a></div>
    </div>
</main>
@endsection
