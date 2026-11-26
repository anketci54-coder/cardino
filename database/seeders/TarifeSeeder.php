<?php

namespace Database\Seeders;

use App\Models\GumrukAyar;
use App\Models\KayitTarifesi;
use App\Models\VergiOrani;
use Illuminate\Database\Seeder;

/**
 * Yer tutucu tarife değerleri. HEPSİ ÖRNEKTİR — gerçek oranlar yönetici
 * panelinden, komisyoncu/gümrük teyidiyle girilmelidir. Mevcut kayıtları
 * ezmez (idempotent).
 */
class TarifeSeeder extends Seeder
{
    public function run(): void
    {
        // Gümrük vergisi oranları (kktcportal 2025 düzenlemesi; komisyoncuyla teyit et)
        $vergiler = [
            ['benzin_dizel', 'ozel', 6.0],
            ['elektrik_hibrit', 'ozel', 4.0],
            ['benzin_dizel', 'ticari', 5.0],
            ['elektrik_hibrit', 'ticari', 4.0],
        ];
        foreach ($vergiler as [$yakit, $kullanim, $oran]) {
            VergiOrani::query()->firstOrCreate(
                ['yakit' => $yakit, 'kullanim' => $kullanim],
                ['oran' => $oran]
            );
        }

        // Genel ayarlar — 2025 (kktcportal, 19 Mart 2025) yapısına göre, teyit edilmeli
        $ayarlar = [
            'fon_orani' => '10',        // ~%10 (teyit et)
            'rihtim_orani' => '4.4',    // ~%4,4 (teyit et)
            'kdv_orani' => '16',        // %16
            'kdv_matrahi' => 'cif_gumruk',
            'navlun_direkt' => '900',   // ÖRNEK GBP
            'navlun_mersin' => '1100',  // ÖRNEK GBP
            'sigorta_orani' => '1',     // ÖRNEK: değerin %1'i
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

        // Kayıt/ruhsat harcı kademeleri 2025 yüzde modelinde kullanılmıyor;
        // gerekirse (cc'ye göre sabit ek harç) yönetici ekler. Varsayılan boş.
    }
}
