<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GirisController extends Controller
{
    public function create(): View
    {
        return view('auth.giris');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Kullanıcı adını yazın.',
            'password.required' => 'Şifreyi yazın.',
        ]);

        $ok = Auth::attempt(
            ['username' => $data['username'], 'password' => $data['password'], 'is_active' => true],
            $request->boolean('remember')
        );

        if (! $ok) {
            throw ValidationException::withMessages([
                'username' => 'Kullanıcı adı veya şifre hatalı.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('panel'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
