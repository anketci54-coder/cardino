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
        // Gümrük vergisi oranları (cyprus-faq kaynaklı tahmin, teyit edilmeli)
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

        // Genel ayarlar (yer tutucu)
        $ayarlar = [
            'kdv_orani' => '16',        // ÖRNEK
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

        // Kayıt / ruhsat harcı tarifesi — yalnızca tablo boşsa örnek kademeler ekle
        if (KayitTarifesi::query()->count() === 0) {
            $ornekler = [
                ['min_cc' => 0,    'max_cc' => 1300, 'min_yas' => 0, 'max_yas' => null, 'tutar_gbp' => 500,  'sira' => 1],
                ['min_cc' => 1301, 'max_cc' => 1600, 'min_yas' => 0, 'max_yas' => null, 'tutar_gbp' => 900,  'sira' => 2],
                ['min_cc' => 1601, 'max_cc' => 2000, 'min_yas' => 0, 'max_yas' => null, 'tutar_gbp' => 1600, 'sira' => 3],
                ['min_cc' => 2001, 'max_cc' => null, 'min_yas' => 0, 'max_yas' => null, 'tutar_gbp' => 3000, 'sira' => 4],
            ];
            foreach ($ornekler as $o) {
                KayitTarifesi::query()->create($o + ['aciklama' => 'ÖRNEK — komisyoncudan teyit et']);
            }
        }
    }
}
