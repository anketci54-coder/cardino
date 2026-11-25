<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class YonetimTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['username' => 'yonetici', 'role' => 'admin', 'gallery_id' => null, 'is_active' => true]);
    }

    private function personel(Gallery $g): User
    {
        return User::factory()->create(['username' => 'personel1', 'role' => 'personel', 'gallery_id' => $g->id, 'is_active' => true]);
    }

    public function test_personel_yonetime_giremez(): void
    {
        $g = Gallery::create(['name' => 'Pilot', 'is_active' => true]);
        $this->actingAs($this->personel($g))->get(route('yonetim.index'))->assertForbidden();
    }

    public function test_yonetici_yonetimi_gorur(): void
    {
        $this->actingAs($this->admin())->get(route('yonetim.index'))->assertOk();
    }

    public function test_yonetici_galeri_ekler(): void
    {
        $this->actingAs($this->admin())
            ->post(route('yonetim.galeri.store'), ['name' => 'Yeni Galeri', 'city' => 'Lefkoşa'])
            ->assertRedirect();

        $this->assertDatabaseHas('galleries', ['name' => 'Yeni Galeri', 'city' => 'Lefkoşa', 'is_active' => true]);
    }

    public function test_yonetici_kullanici_ekler(): void
    {
        $g = Gallery::create(['name' => 'Pilot', 'is_active' => true]);

        $this->actingAs($this->admin())
            ->post(route('yonetim.kullanici.store'), [
                'username' => 'satici',
                'name' => 'Satıcı Bey',
                'role' => 'personel',
                'gallery_id' => $g->id,
                'password' => 'gizli1234',
            ])
            ->assertRedirect();

        $u = User::where('username', 'satici')->first();
        $this->assertNotNull($u);
        $this->assertSame('personel', $u->role);
        $this->assertSame($g->id, $u->gallery_id);
        $this->assertTrue(password_verify('gizli1234', $u->password));
    }

    public function test_personel_rolu_galeri_ister(): void
    {
        $this->actingAs($this->admin())
            ->post(route('yonetim.kullanici.store'), [
                'username' => 'galerisiz',
                'name' => 'Galerisiz',
                'role' => 'personel',
                'password' => 'gizli1234',
            ])
            ->assertSessionHasErrors('gallery_id');
    }

    public function test_yonetici_kendini_kilitleyemez(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('yonetim.kullanici.update', $admin), [
                'name' => $admin->name,
                'role' => 'personel',   // kendini düşürmeye çalış
                'is_active' => '0',      // kendini kapatmaya çalış
            ])
            ->assertRedirect();

        $admin->refresh();
        $this->assertSame('admin', $admin->role);   // korundu
        $this->assertTrue($admin->is_active);        // korundu
    }
}
