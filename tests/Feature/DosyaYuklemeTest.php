<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DosyaYuklemeTest extends TestCase
{
    use RefreshDatabase;

    private function galeriKur(string $ad): array
    {
        $galeri = Gallery::create(['name' => $ad, 'is_active' => true]);
        $kullanici = User::factory()->create([
            'username' => 'kul'.$galeri->id,
            'role' => 'personel',
            'gallery_id' => $galeri->id,
            'is_active' => true,
        ]);
        $arac = Vehicle::create([
            'gallery_id' => $galeri->id,
            'chassis_no' => 'TEST-'.$galeri->id,
            'make' => 'Toyota',
            'model' => 'Prius',
            'status' => 'stokta',
            'origin' => 'japonya',
        ]);

        return [$galeri, $kullanici, $arac];
    }

    public function test_personel_fotograf_yukleyebilir(): void
    {
        Storage::fake('local');
        [, $kullanici, $arac] = $this->galeriKur('Pilot');

        $this->actingAs($kullanici)
            ->post(route('dosya.store', $arac), [
                'kind' => 'foto',
                'files' => [UploadedFile::fake()->create('araba.jpg', 200, 'image/jpeg')],
            ])
            ->assertRedirect(route('araclar.show', $arac));

        $dosya = VehicleFile::where('vehicle_id', $arac->id)->first();
        $this->assertNotNull($dosya);
        $this->assertSame('foto', $dosya->kind);
        $this->assertTrue($dosya->isImage());
        Storage::disk('local')->assertExists($dosya->path);
    }

    public function test_personel_belge_yukleyebilir(): void
    {
        Storage::fake('local');
        [, $kullanici, $arac] = $this->galeriKur('Pilot');

        $this->actingAs($kullanici)
            ->post(route('dosya.store', $arac), [
                'kind' => 'belge',
                'doc_type' => 'fatura',
                'files' => [UploadedFile::fake()->create('fatura.pdf', 150, 'application/pdf')],
            ])
            ->assertRedirect();

        $dosya = VehicleFile::where('vehicle_id', $arac->id)->first();
        $this->assertSame('belge', $dosya->kind);
        $this->assertSame('fatura', $dosya->doc_type);
        $this->assertSame('Fatura', $dosya->docTypeLabel());
    }

    public function test_gecersiz_dosya_turu_reddedilir(): void
    {
        Storage::fake('local');
        [, $kullanici, $arac] = $this->galeriKur('Pilot');

        $this->actingAs($kullanici)
            ->post(route('dosya.store', $arac), [
                'kind' => 'belge',
                'files' => [UploadedFile::fake()->create('zararli.exe', 10, 'application/octet-stream')],
            ])
            ->assertSessionHasErrors('files.0');

        $this->assertSame(0, VehicleFile::count());
    }

    public function test_baska_galeri_dosyaya_erisemez(): void
    {
        Storage::fake('local');
        [, $sahip, $arac] = $this->galeriKur('Pilot');
        [, $yabanci] = $this->galeriKur('Rakip');

        // Sahip yükler
        $this->actingAs($sahip)->post(route('dosya.store', $arac), [
            'kind' => 'foto',
            'files' => [UploadedFile::fake()->create('araba.jpg', 100, 'image/jpeg')],
        ]);
        $dosya = VehicleFile::where('vehicle_id', $arac->id)->first();

        // Yabancı galeri kullanıcısı erişemez (araç galeri kapsamında görünmez → 404)
        $this->actingAs($yabanci)
            ->get(route('dosya.goster', [$arac, $dosya]))
            ->assertNotFound();

        // Sahip erişebilir
        $this->actingAs($sahip)
            ->get(route('dosya.goster', [$arac, $dosya]))
            ->assertOk();
    }
}
