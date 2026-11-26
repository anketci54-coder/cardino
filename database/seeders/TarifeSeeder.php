<?php

namespace Database\Seeders;

use App\Models\FifTarifesi;
use App\Models\GumrukAyar;
use App\Models\VergiOrani;
use Illuminate\Database\Seeder;

/**
 * 2026 KKTC gümrük tarifeleri.
 * Kaynak: getirdelim.com komisyoncu hesaplayıcısı ile çapraz kontrol (2026-11-26);
 * dayanak FİF Cetvel I (A.E. 221/2026, yürürlük 2026-08-03), Fasıl 87.03/87.80.
 * İthalat yalnızca UK/Japonya = 3. ülke (özel/binek) oranları.
 * Ticari (T/eşya) satırları ve sabit geçici kayıt harcı komisyoncuyla teyit bekliyor.
 */
class TarifeSeeder extends Seeder
{
    public function run(): void
    {
        // Yakıt × kullanım: gümrük %, ilk kayıt (tescil) %, KDV %  (özel/3.ülke, getirdelim teyitli)
        $oranlar = [
            // [yakit, kullanim, gumruk, kayit, kdv]
            ['benzin_dizel', 'ozel', 10, 6, 16],
            ['benzin_dizel', 'ticari', 10, 5, 16],   // ticari/T — teyit
            ['hibrit', 'ozel', 10, 4, 16],
            ['hibrit', 'ticari', 10, 4, 16],          // ticari — teyit
            ['elektrik', 'ozel', 10, 4, 16],          // getirdelim KDV %16 uyguluyor (muafiyet YOK) — komisyoncu teyidi
            ['elektrik', 'ticari', 10, 4, 16],
        ];
        foreach ($oranlar as [$yakit, $kullanim, $g, $k, $kdv]) {
            VergiOrani::query()->updateOrCreate(
                ['yakit' => $yakit, 'kullanim' => $kullanim],
                ['gumruk_orani' => $g, 'kayit_orani' => $k, 'kdv_orani' => $kdv]
            );
        }
        // Eski yakıt değerlerini (elektrik_hibrit vb.) temizle
        VergiOrani::query()->whereNotIn('yakit', array_keys(VergiOrani::YAKITLAR))->delete();

        // FİF — yakıt + motor hacmi kademesi (getirdelim 2026 teyitli)
        // benzin/dizel/hibrit aynı eğri: ≤2000 %3, 2001-3000 %8, ≥3001 %12; elektrik düz %5
        FifTarifesi::query()->delete();
        $fif = [
            ['benzin_dizel', 0, 2000, 3, 1],
            ['benzin_dizel', 2001, 3000, 8, 2],
            ['benzin_dizel', 3001, null, 12, 3],
            ['hibrit', 0, 2000, 3, 1],
            ['hibrit', 2001, 3000, 8, 2],
            ['hibrit', 3001, null, 12, 3],
            ['elektrik', 0, null, 5, 1],
        ];
        foreach ($fif as [$yakit, $min, $max, $o, $s]) {
            FifTarifesi::query()->create([
                'yakit' => $yakit, 'min_cc' => $min, 'max_cc' => $max, 'oran' => $o, 'sira' => $s,
            ]);
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
