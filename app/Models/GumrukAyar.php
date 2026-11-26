<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Gümrük/maliyet ayarları: anahtar-değer (KDV, varsayılan masraflar, kâr marjı). */
#[Fillable(['anahtar', 'deger', 'aciklama'])]
class GumrukAyar extends Model
{
    protected $table = 'gumruk_ayarlari';

    /** Tanımlı ayarlar ve açıklamaları (sıra + etiket). */
    public const TANIMLAR = [
        'rihtim_orani' => 'Rıhtım resmi oranı (CIF %\'si)',
        'gkk_orani' => 'GKK (Güvenlik Kuvvetleri Fonu) oranı (CIF %\'si)',
        'navlun_direkt' => 'Varsayılan navlun — doğrudan (Gazimağusa/Girne) (GBP)',
        'navlun_mersin' => 'Varsayılan navlun — Mersin aktarmalı (GBP)',
        'sigorta_orani' => 'Sigorta oranı (araç değerinin %\'si)',
        'liman_ardiye' => 'Liman / ardiye / elleçleme (GBP)',
        'komisyoncu' => 'Komisyoncu ücreti (GBP)',
        'muayene_kayit' => 'Muayene / evrak (GBP)',
        'kar_marji' => 'Hedef kâr marjı (%)',
    ];

    public static function deger(string $anahtar, ?string $varsayilan = null): ?string
    {
        return static::query()->where('anahtar', $anahtar)->value('deger') ?? $varsayilan;
    }

    public static function sayi(string $anahtar, float $varsayilan = 0): float
    {
        $v = static::deger($anahtar);

        return $v === null || $v === '' ? $varsayilan : (float) str_replace(',', '.', $v);
    }

    public static function ayarla(string $anahtar, ?string $deger): void
    {
        static::query()->updateOrCreate(
            ['anahtar' => $anahtar],
            ['deger' => $deger, 'aciklama' => self::TANIMLAR[$anahtar] ?? null]
        );
    }
}
