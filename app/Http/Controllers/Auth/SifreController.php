<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class SifreController extends Controller
{
    public function istek(): View
    {
        return view('auth.sifremi-unuttum');
    }

    public function gonder(Request $request): RedirectResponse
    {
        $request->validate(
            ['email' => ['required', 'email']],
            ['email.required' => 'E-posta adresinizi yazın.', 'email.email' => 'Geçerli bir e-posta yazın.']
        );

        Password::sendResetLink($request->only('email'));

        // Kullanıcı sızdırmamak için sonuç ne olursa olsun aynı mesaj
        return back()->with('ok', 'E-posta adresi sistemde kayıtlıysa, şifre sıfırlama bağlantısı gönderildi.');
    }

    public function goster(Request $request, string $token): View
    {
        return view('auth.sifre-sifirla', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function sifirla(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'email.required' => 'E-posta adresinizi yazın.',
            'password.required' => 'Yeni şifreyi yazın.',
            'password.confirmed' => 'Şifreler eşleşmiyor.',
            'password.min' => 'Şifre en az 8 karakter olmalı.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => $password])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('ok', 'Şifreniz güncellendi. Yeni şifrenizle giriş yapabilirsiniz.')
            : back()->withErrors(['email' => 'Sıfırlama bağlantısı geçersiz veya süresi dolmuş. Yeniden isteyin.']);
    }
}
