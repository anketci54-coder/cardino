<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class YonetimController extends Controller
{
    public function index(): View
    {
        return view('yonetim.index', [
            'galeriler' => Gallery::query()->withCount(['users', 'vehicles'])->orderBy('name')->get(),
            'kullanicilar' => User::query()->with('gallery')->orderBy('username')->get(),
        ]);
    }

    public function galeriStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('galleries', 'name')],
            'city' => ['nullable', 'string', 'max:60'],
            'phone' => ['nullable', 'string', 'max:30'],
        ], [
            'name.required' => 'Galeri adını yazın.',
            'name.unique' => 'Bu adda galeri zaten var.',
        ]);
        $data['is_active'] = true;
        Gallery::create($data);

        return back()->with('ok', 'Galeri eklendi.');
    }

    public function galeriUpdate(Request $request, Gallery $galeri): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('galleries', 'name')->ignore($galeri->id)],
            'city' => ['nullable', 'string', 'max:60'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $galeri->update($data);

        return back()->with('ok', 'Galeri güncellendi.');
    }

    public function kullaniciStore(Request $request): RedirectResponse
    {
        $data = $this->validateUser($request);
        User::create([
            'username' => $data['username'],
            'name' => $data['name'],
            'password' => $data['password'],
            'role' => $data['role'],
            'gallery_id' => $data['role'] === 'admin' ? null : $data['gallery_id'],
            'is_active' => true,
        ]);

        return back()->with('ok', 'Kullanıcı eklendi.');
    }

    public function kullaniciUpdate(Request $request, User $kullanici): RedirectResponse
    {
        $data = $this->validateUser($request, $kullanici);
        $kendisi = $request->user()->id === $kullanici->id;

        $yeni = [
            'name' => $data['name'],
            'role' => $data['role'],
            'gallery_id' => $data['role'] === 'admin' ? null : $data['gallery_id'],
            'is_active' => $request->boolean('is_active'),
        ];

        // Kendini kilitlememe: yönetici kendi rolünü/aktifliğini düşüremez
        if ($kendisi) {
            $yeni['role'] = $kullanici->role;
            $yeni['gallery_id'] = $kullanici->gallery_id;
            $yeni['is_active'] = true;
        }

        if (! empty($data['password'])) {
            $yeni['password'] = $data['password'];
        }

        $kullanici->update($yeni);

        return back()->with('ok', $kendisi ? 'Kendi hesabınız güncellendi (rol ve aktiflik korundu).' : 'Kullanıcı güncellendi.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $isNew = $user === null;

        return $request->validate([
            'username' => $isNew
                ? ['required', 'string', 'max:40', 'alpha_dash', Rule::unique('users', 'username')]
                : ['nullable'],
            'name' => ['required', 'string', 'max:80'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'gallery_id' => [Rule::requiredIf($request->input('role') !== 'admin'), 'nullable', 'exists:galleries,id'],
            'password' => [$isNew ? 'required' : 'nullable', 'string', 'min:8', 'max:100'],
        ], [
            'username.required' => 'Kullanıcı adını yazın.',
            'username.alpha_dash' => 'Kullanıcı adı harf, rakam, tire ve alt çizgi içerebilir.',
            'username.unique' => 'Bu kullanıcı adı zaten var.',
            'name.required' => 'Görünen adı yazın.',
            'gallery_id.required' => 'Galeri seçin (yönetici dışındaki roller için zorunlu).',
            'password.required' => 'Şifre yazın (en az 8 karakter).',
            'password.min' => 'Şifre en az 8 karakter olmalı.',
        ]);
    }
}
