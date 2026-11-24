<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class KullaniciOlustur extends Command
{
    protected $signature = 'cardino:kullanici {username : Kullanıcı adı} {--ad= : Görünen ad} {--rol=personel : admin, sahip veya personel} {--galeri= : Galeri numarası} {--sifre= : Şifre (boş bırakılırsa sorulur)}';

    protected $description = 'Cardino kullanıcısı oluşturur veya şifresini/rolünü günceller';

    public function handle(): int
    {
        $role = $this->option('rol');
        if (! array_key_exists($role, User::ROLES)) {
            $this->error('Rol admin, sahip veya personel olmalı.');

            return self::FAILURE;
        }

        $password = $this->option('sifre') ?: $this->secret('Şifre');
        if (strlen((string) $password) < 8) {
            $this->error('Şifre en az 8 karakter olmalı.');

            return self::FAILURE;
        }

        $username = $this->argument('username');
        $user = User::updateOrCreate(
            ['username' => $username],
            [
                'name' => $this->option('ad') ?: $username,
                'password' => $password,
                'role' => $role,
                'gallery_id' => $role === 'admin' ? null : $this->option('galeri'),
                'is_active' => true,
            ]
        );

        $this->info("Kullanıcı hazır: {$user->username} ({$user->roleLabel()})");

        return self::SUCCESS;
    }
}
