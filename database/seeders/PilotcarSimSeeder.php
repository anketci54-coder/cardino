<?php

namespace Database\Seeders;

use App\Models\Gallery;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\MaliyetHesaplayici;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Simülasyon: pilotcar galerisine 2022+ (KKTC 5 yaş kuralına uygun, ithal edilebilir),
 * sağdan direksiyon (RHD) premium filo. Japonya (JPY) + İngiltere (GBP).
 * Ağırlıklı Mercedes (AMG, GLA/GLC/GLE, C/E salon) + BMW + Lexus/Toyota.
 * Modeller/yıl/cc/yakıt/fiyat gerçek ihraç ilanlarından (heycar, theAA, carfromjapan);
 * bazı fiyatlar emsal ilan ortalaması, şasi/VIN örnektir.
 * Her araç için maliyet kalemleri MaliyetHesaplayici ile üretilir.
 * Çalıştırınca pilotcar galerisindeki tüm araçları silip baştan kurar.
 */
class PilotcarSimSeeder extends Seeder
{
    public function run(): void
    {
        $galeri = Gallery::where('name', 'pilotcar')->first();
        if (! $galeri) {
            $this->command->error('pilotcar galerisi yok.');

            return;
        }
        $createdBy = User::where('username', 'admin1')->value('id');

        // Önce bu galerinin mevcut araçlarını ve kalemlerini tamamen temizle
        $eskiler = Vehicle::withoutGlobalScopes()->where('gallery_id', $galeri->id)->withTrashed()->get();
        foreach ($eskiler as $e) {
            $e->costs()->delete();
            $e->forceDelete();
        }

        // [marka, model, yil, cc, yakit(benzin/dizel/hibrit), menşe, fiyat, para, rota, renk, km, durum, ilk_kayit_ay, alis_once_gun]
        $araclar = [
            // --- İNGİLTERE (sterlin, RHD) --- Mercedes/BMW ağırlıklı, gerçek UK ilanları
            ['Mercedes-Benz', 'GLC 220d 4Matic AMG Line', 2022, 1993, 'dizel', 'ingiltere', 31840, 'GBP', 'direkt', 'Siyah', 36000, 'stokta', 5, 70],
            ['Mercedes-Benz', 'GLA 200 AMG Line', 2022, 1332, 'benzin', 'ingiltere', 27060, 'GBP', 'direkt', 'Beyaz', 27000, 'stokta', 7, 55],
            ['Mercedes-Benz', 'GLE 400d 4Matic AMG Line', 2023, 2925, 'dizel', 'ingiltere', 50689, 'GBP', 'direkt', 'Lacivert', 40000, 'gumrukte', 3, 30],
            ['Mercedes-AMG', 'A45 S 4Matic+', 2022, 1991, 'benzin', 'ingiltere', 45445, 'GBP', 'direkt', 'Gri', 18000, 'stokta', 6, 48],
            ['Mercedes-Benz', 'C300d AMG Line', 2022, 1993, 'dizel', 'ingiltere', 30000, 'GBP', 'direkt', 'Siyah', 33000, 'stokta', 4, 62],
            ['Mercedes-Benz', 'C220d AMG Line', 2022, 1993, 'dizel', 'ingiltere', 26500, 'GBP', 'direkt', 'Gümüş', 41000, 'stokta', 8, 80],
            ['Mercedes-Benz', 'E220d AMG Line', 2022, 1993, 'dizel', 'ingiltere', 32000, 'GBP', 'direkt', 'Siyah', 38000, 'yolda', 5, 20],
            ['BMW', 'X3 xDrive20d M Sport', 2022, 1995, 'dizel', 'ingiltere', 30500, 'GBP', 'direkt', 'Beyaz', 35000, 'stokta', 6, 66],
            ['BMW', '520d M Sport', 2022, 1995, 'dizel', 'ingiltere', 26000, 'GBP', 'direkt', 'Lacivert', 44000, 'stokta', 4, 75],
            ['BMW', '320d M Sport', 2022, 1995, 'dizel', 'ingiltere', 24000, 'GBP', 'direkt', 'Gri', 39000, 'gumrukte', 9, 35],
            ['BMW', 'X5 xDrive30d M Sport', 2022, 2993, 'dizel', 'ingiltere', 48000, 'GBP', 'direkt', 'Siyah', 47000, 'stokta', 3, 85],
            ['Land Rover', 'Range Rover Evoque R-Dynamic', 2022, 1999, 'dizel', 'ingiltere', 32000, 'GBP', 'direkt', 'Beyaz', 31000, 'yolda', 7, 22],

            // --- JAPONYA (yen, RHD) --- Lexus/Toyota premium + JDM Avrupa; fiyat USD ilanından ~155 ile yen'e
            ['Lexus', 'NX250 F Sport', 2023, 2487, 'benzin', 'japonya', 5117000, 'JPY', 'direkt', 'Beyaz', 28000, 'stokta', 4, 58],
            ['Lexus', 'NX250', 2022, 2487, 'benzin', 'japonya', 4812000, 'JPY', 'mersin', 'Siyah', 37000, 'stokta', 8, 72],
            ['Toyota', 'Harrier Hybrid Z', 2025, 2500, 'hibrit', 'japonya', 4264000, 'JPY', 'direkt', 'Lacivert', 9000, 'yolda', 1, 16],
            ['Toyota', 'Harrier G', 2023, 2000, 'benzin', 'japonya', 2251000, 'JPY', 'direkt', 'Gümüş', 30000, 'stokta', 5, 60],
            ['Toyota', 'Prius', 2023, 2000, 'hibrit', 'japonya', 3396000, 'JPY', 'direkt', 'Beyaz', 22000, 'gumrukte', 2, 33],
            ['Toyota', 'Prius', 2023, 1800, 'hibrit', 'japonya', 2883000, 'JPY', 'mersin', 'Gri', 26000, 'stokta', 3, 50],
            ['Mercedes-Benz', 'C200 Avantgarde', 2022, 1496, 'benzin', 'japonya', 4900000, 'JPY', 'direkt', 'Siyah', 24000, 'stokta', 6, 64],
            ['BMW', '320i M Sport', 2022, 1998, 'benzin', 'japonya', 4600000, 'JPY', 'mersin', 'Beyaz', 29000, 'stokta', 7, 68],
        ];

        // Şasi/VIN ön ekleri (örnek)
        $sasiPrefix = [
            'Lexus NX250 F Sport' => 'AAZA25', 'Lexus NX250' => 'AAZA20', 'Toyota Harrier Hybrid Z' => 'AXUP85',
            'Toyota Harrier G' => 'MXUA80', 'Toyota Prius' => 'MXWH60', 'Mercedes-Benz C200 Avantgarde' => '206042',
            'BMW 320i M Sport' => '6K20',
        ];
        $vinPrefix = [
            'Mercedes-Benz' => 'W1N2470', 'Mercedes-AMG' => 'W1K1770', 'BMW' => 'WBA5V70', 'Land Rover' => 'SALZA2BX',
        ];

        $yakitVergi = ['benzin' => 'benzin_dizel', 'dizel' => 'benzin_dizel', 'hibrit' => 'hibrit', 'elektrik' => 'elektrik'];
        $sira = 1;
        $eklendi = 0;

        foreach ($araclar as $a) {
            [$marka, $model, $yil, $cc, $yakit, $mense, $fiyat, $para, $rota, $renk, $km, $durum, $ilkKayitAy, $alisOnce] = $a;

            if ($mense === 'japonya') {
                $pref = $sasiPrefix[$marka.' '.$model] ?? strtoupper(substr(preg_replace('/\s/', '', $model), 0, 5));
                $sasi = $pref.'-'.str_pad((string) (1000000 + $sira * 4321), 7, '0', STR_PAD_LEFT);
            } else {
                $pref = $vinPrefix[$marka] ?? 'SALX0000';
                $sasi = substr($pref.str_pad((string) (100000 + $sira * 7919), 10, '0', STR_PAD_LEFT), 0, 17);
            }

            $alisTarihi = Carbon::today()->subDays($alisOnce);
            $ilkKayit = Carbon::create($yil, $ilkKayitAy, 15);

            $arac = new Vehicle([
                'gallery_id' => $galeri->id,
                'chassis_no' => $sasi,
                'origin' => $mense,
                'shipping_route' => $rota,
                'make' => $marka,
                'model' => $model,
                'model_year' => $yil,
                'first_registration' => $ilkKayit->toDateString(),
                'color' => $renk,
                'mileage_km' => $km,
                'engine_cc' => $cc,
                'fuel' => $yakit,
                'transmission' => 'otomatik',
                'status' => $durum,
                'purchase_date' => $alisTarihi->toDateString(),
                'created_by' => $createdBy,
            ]);
            $arac->save();

            $h = MaliyetHesaplayici::hesapla([
                'alis_fiyat' => $fiyat,
                'alis_para' => $para,
                'yakit' => $yakitVergi[$yakit],
                'kullanim' => 'ozel',
                'motor_cc' => $cc,
                'model_yili' => $yil,
                'rota' => $rota,
            ]);
            foreach ($h['kostlar'] as $k) {
                $arac->costs()->create([
                    'category' => $k['category'],
                    'description' => $k['description'] ?? null,
                    'amount' => round((float) $k['amount'], 2),
                    'currency' => $k['currency'],
                    'rate' => (float) $k['rate'],
                    'base_amount' => round((float) $k['base'], 2),
                    'spent_on' => $alisTarihi->toDateString(),
                    'created_by' => $createdBy,
                ]);
            }

            $eklendi++;
            $sira++;
        }

        $this->command->info("pilotcar: {$eklendi} premium araç (2022+, RHD) eklendi.");
    }
}
