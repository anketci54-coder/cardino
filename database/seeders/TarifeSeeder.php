<?php

namespace Database\Seeders;

use App\Models\FifTarifesi;
use App\Models\GumrukAyar;
use App\Models\VergiOrani;
use Illuminate\Database\Seeder;

/**
 * 2026 KKTC gümrük tarifeleri (Kürşat'ın verdiği resmi çizelge + mevzuat).
 * Hibrit gümrük/FİF değerleri yaklaşıktır; komisyoncuyla teyit edilmeli.
 * İthalat yalnızca UK/Japonya = 3. ülke oranları.
 */
class TarifeSeeder extends Seeder
{
    public function run(): void
    {
        // Yakıt × kullanım: gümrük %, ilk kayıt %, KDV %
        $oranlar = [
            // [yakit, kullanim, gumruk, kayit, kdv]
            ['benzin_dizel', 'ozel', 10, 6, 16],
            ['benzin_dizel', 'ticari', 10, 5, 16],
            ['hibrit', 'ozel', 10, 4, 16],      // gümrük bazı aralıklarda indirimli — teyit
            ['hibrit', 'ticari', 10, 4, 16],
            ['elektrik', 'ozel', 10, 4, 0],     // 3. ülke gümrük %10; KDV muaf
            ['elektrik', 'ticari', 10, 4, 0],
        ];
        foreach ($oranlar as [$yakit, $kullanim, $g, $k, $kdv]) {
            VergiOrani::query()->updateOrCreate(
                ['yakit' => $yakit, 'kullanim' => $kullanim],
                ['gumruk_orani' => $g, 'kayit_orani' => $k, 'kdv_orani' => $kdv]
            );
        }
        // Eski yakıt değerlerini (elektrik_hibrit vb.) temizle
        VergiOrani::query()->whereNotIn('yakit', array_keys(VergiOrani::YAKITLAR))->delete();

        // FİF — yakıt + motor hacmi kademesi
        if (FifTarifesi::query()->count() === 0) {
            $fif = [
                // benzin/dizel: ≤1600 %20, 1601-2000 %35, ≥2001 %50
                ['benzin_dizel', 0, 1600, 20, 1],
                ['benzin_dizel', 1601, 2000, 35, 2],
                ['benzin_dizel', 2001, null, 50, 3],
                // hibrit: kademeli, indirimli (teyit) — şimdilik benzinle aynı kademeler
                ['hibrit', 0, 1600, 20, 1],
                ['hibrit', 1601, 2000, 35, 2],
                ['hibrit', 2001, null, 50, 3],
                // elektrik: düz %5
                ['elektrik', 0, null, 5, 1],
            ];
            foreach ($fif as [$yakit, $min, $max, $o, $s]) {
                FifTarifesi::query()->create([
                    'yakit' => $yakit, 'min_cc' => $min, 'max_cc' => $max, 'oran' => $o, 'sira' => $s,
                ]);
            }
        }

        // Genel ayarlar
        $ayarlar = [
            'rihtim_orani' => '4.4',    // %4,4
            'gkk_orani' => '2.5',       // %2,5
            'navlun_direkt' => '900',   // ÖRNEK GBP
            'navlun_mersin' => '1100',  // ÖRNEK GBP
            'sigorta_orani' => '1',     // ÖRNEK %
            'liman_ardiye' => '150',    // ÖRNEK GBP
            'komisyoncu' => '200',      // ÖRNEK GBP
            'muayene_kayit' => '250',   // ÖRNEK GBP
            'kar_marji' => '15',        // ÖRNEK %
        ];
        foreach ($ayarlar as $anahtar => $deger) {
            GumrukAyar::query()->firstOrCreate(
                ['anahtar' => $anahtar],
                ['deger' => $deger, 'aciklama' => GumrukAyar::TANIMLAR[$anahtar] ?? null]
            );
        }
    }
}
