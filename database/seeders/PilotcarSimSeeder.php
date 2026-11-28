<?php

namespace Database\Seeders;

use App\Models\Gallery;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\MaliyetHesaplayici;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Simülasyon: pilotcar galerisine Japonya (10) + İngiltere (10) gerçek araç.
 * Modeller, yıllar, motor hacimleri, yakıt ve fiyatlar gerçek ihraç ilanlarından
 * (carfromjapan, heycar, motorpoint, theAA vb.) alınmış; şasi numaraları örnektir.
 * Her araç için maliyet kalemleri MaliyetHesaplayici ile üretilir.
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
        $admin = User::where('username', 'admin1')->first();
        $createdBy = $admin?->id;

        // [marka, model, yil, cc, yakit(benzin/dizel/hibrit), menşe, fiyat, para, rota, renk, km, durum, ilk_kayit_ay, alis_once_gun]
        $araclar = [
            // --- JAPONYA (yen) --- fiyat USD ilanından ~155 ile yen'e çevrildi
            ['Toyota', 'Aqua', 2021, 1500, 'hibrit', 'japonya', 1674000, 'JPY', 'direkt', 'Beyaz', 42000, 'stokta', 4, 70],
            ['Honda', 'Fit', 2020, 1500, 'hibrit', 'japonya', 1517000, 'JPY', 'direkt', 'Gümüş', 55000, 'stokta', 6, 85],
            ['Honda', 'Fit', 2021, 1500, 'hibrit', 'japonya', 1890000, 'JPY', 'mersin', 'Mavi', 38000, 'gumrukte', 3, 40],
            ['Honda', 'Fit', 2025, 1500, 'benzin', 'japonya', 1961000, 'JPY', 'direkt', 'Siyah', 9000, 'yolda', 1, 20],
            ['Nissan', 'Note', 2021, 1200, 'hibrit', 'japonya', 948000, 'JPY', 'direkt', 'Kırmızı', 47000, 'stokta', 5, 60],
            ['Nissan', 'Note', 2019, 1200, 'hibrit', 'japonya', 939000, 'JPY', 'mersin', 'Beyaz', 68000, 'stokta', 7, 90],
            ['Toyota', 'Prius', 2023, 2000, 'hibrit', 'japonya', 3396000, 'JPY', 'direkt', 'Lacivert', 21000, 'gumrukte', 2, 35],
            ['Toyota', 'Prius', 2023, 1800, 'hibrit', 'japonya', 2883000, 'JPY', 'direkt', 'Gri', 26000, 'stokta', 2, 55],
            ['Suzuki', 'Swift', 2025, 1200, 'benzin', 'japonya', 959000, 'JPY', 'direkt', 'Turuncu', 7000, 'yolda', 1, 15],
            ['Suzuki', 'Swift', 2023, 1200, 'benzin', 'japonya', 1167000, 'JPY', 'mersin', 'Beyaz', 29000, 'stokta', 3, 50],

            // --- İNGİLTERE (sterlin) --- RHD, fiyatlar gerçek UK ilanlarından
            ['Nissan', 'Qashqai', 2021, 1300, 'benzin', 'ingiltere', 15499, 'GBP', 'direkt', 'Gri', 46000, 'stokta', 5, 65],
            ['Ford', 'Focus', 2021, 1000, 'benzin', 'ingiltere', 13899, 'GBP', 'direkt', 'Mavi', 55000, 'stokta', 4, 75],
            ['Volkswagen', 'Golf', 2021, 1500, 'benzin', 'ingiltere', 16500, 'GBP', 'direkt', 'Beyaz', 41000, 'gumrukte', 3, 45],
            ['BMW', '320d', 2020, 1995, 'dizel', 'ingiltere', 20500, 'GBP', 'direkt', 'Siyah', 62000, 'stokta', 6, 80],
            ['Audi', 'A3', 2021, 1498, 'benzin', 'ingiltere', 18500, 'GBP', 'direkt', 'Gri', 39000, 'stokta', 5, 58],
            ['Land Rover', 'Range Rover Evoque', 2020, 1999, 'dizel', 'ingiltere', 20500, 'GBP', 'direkt', 'Lacivert', 58000, 'gumrukte', 7, 38],
            ['Mercedes-Benz', 'C220d', 2020, 1950, 'dizel', 'ingiltere', 21500, 'GBP', 'direkt', 'Gümüş', 57000, 'stokta', 6, 88],
            ['MINI', 'Cooper', 2021, 1499, 'benzin', 'ingiltere', 14500, 'GBP', 'direkt', 'Kırmızı', 33000, 'yolda', 4, 18],
            ['Vauxhall', 'Corsa', 2021, 1199, 'benzin', 'ingiltere', 11000, 'GBP', 'direkt', 'Beyaz', 44000, 'stokta', 4, 72],
            ['Nissan', 'Juke', 2021, 999, 'benzin', 'ingiltere', 13500, 'GBP', 'direkt', 'Turuncu', 36000, 'stokta', 5, 63],
        ];

        $sasiPrefix = [
            'Toyota Aqua' => 'NHP10', 'Honda Fit' => 'GR8', 'Nissan Note' => 'E13', 'Toyota Prius' => 'ZVW65',
            'Suzuki Swift' => 'ZC83S',
        ];
        $vinPrefix = [
            'Nissan' => 'SJNFAAJ11U', 'Ford' => 'WF05XXGCH5', 'Volkswagen' => 'WVWZZZCDZM', 'BMW' => 'WBA5V11090',
            'Audi' => 'WAUZZZGY5M', 'Land Rover' => 'SALZA2BX8L', 'Mercedes-Benz' => 'WDD2050141', 'MINI' => 'WMWXR910X02',
            'Vauxhall' => 'W0VXXLHKBM', 'Nissan Juke' => 'SJNFBAF15U',
        ];

        $yakitVergi = ['benzin' => 'benzin_dizel', 'dizel' => 'benzin_dizel', 'hibrit' => 'hibrit', 'elektrik' => 'elektrik'];
        $sira = 1;
        $eklendi = 0;

        foreach ($araclar as $a) {
            [$marka, $model, $yil, $cc, $yakit, $mense, $fiyat, $para, $rota, $renk, $km, $durum, $ilkKayitAy, $alisOnce] = $a;

            // Benzersiz şasi / VIN
            if ($mense === 'japonya') {
                $pref = $sasiPrefix[$marka.' '.$model] ?? strtoupper(substr($model, 0, 3));
                $sasi = $pref.'-'.str_pad((string) (1000000 + $sira * 4321), 7, '0', STR_PAD_LEFT);
            } else {
                $pref = $vinPrefix[$marka.' '.$model] ?? ($vinPrefix[$marka] ?? 'SALX0000X0');
                $sasi = $pref.str_pad((string) (100000 + $sira * 7919), 7, '0', STR_PAD_LEFT);
            }
            $sasi = substr($sasi, 0, 17);

            if (Vehicle::withoutGlobalScopes()->where('gallery_id', $galeri->id)->where('chassis_no', $sasi)->exists()) {
                $sira++;

                continue;
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

            // Maliyet kalemleri (tahmin motoruyla)
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

        $this->command->info("pilotcar: {$eklendi} araç eklendi.");
    }
}
