<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SifreSifirlamaBildirimi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class SifreSifirlamaTest extends TestCase
{
    use RefreshDatabase;

    private function kullanici(): User
    {
        return User::factory()->create([
            'username' => 'satici',
            'email' => 'satici@ornek.com',
            'role' => 'personel',
            'is_active' => true,
        ]);
    }

    public function test_sifremi_unuttum_sayfasi_acilir(): void
    {
        $this->get(route('password.request'))->assertOk()->assertSee('Şifre sıfırlama');
    }

    public function test_baglanti_gonderilir(): void
    {
        Notification::fake();
        $u = $this->kullanici();

        $this->post(route('password.email'), ['email' => 'satici@ornek.com'])->assertRedirect();

        Notification::assertSentTo($u, SifreSifirlamaBildirimi::class);
    }

    public function test_bilinmeyen_eposta_sizdirmaz(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'yok@ornek.com'])
            ->assertRedirect()
            ->assertSessionHas('ok');

        Notification::assertNothingSent();
    }

    public function test_sifre_sifirlanir(): void
    {
        $u = $this->kullanici();
        $token = Password::broker()->createToken($u);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'satici@ornek.com',
            'password' => 'yenisifre123',
            'password_confirmation' => 'yenisifre123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('yenisifre123', $u->fresh()->password));
    }

    public function test_gecersiz_token_reddedilir(): void
    {
        $this->kullanici();

        $this->post(route('password.update'), [
            'token' => 'gecersiz-token',
            'email' => 'satici@ornek.com',
            'password' => 'yenisifre123',
            'password_confirmation' => 'yenisifre123',
        ])->assertSessionHasErrors('email');
    }
}
