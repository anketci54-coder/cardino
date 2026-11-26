<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Yakıt × kullanım için gümrük vergisi, ilk kayıt harcı ve KDV oranları (%). */
#[Fillable(['yakit', 'kullanim', 'gumruk_orani', 'kayit_orani', 'kdv_orani'])]
class VergiOrani extends Model
{
    protected $table = 'vergi_oranlari';

    public const YAKITLAR = [
        'benzin_dizel' => 'Benzin / Dizel',
        'hibrit' => 'Hibrit',
        'elektrik' => 'Elektrik',
    ];

    public const KULLANIMLAR = [
        'ozel' => 'Özel',
        'ticari' => 'Ticari (T)',
    ];

    protected function casts(): array
    {
        return [
            'gumruk_orani' => 'decimal:3',
            'kayit_orani' => 'decimal:3',
            'kdv_orani' => 'decimal:3',
        ];
    }

    /** Yakıt + kullanım satırını döndürür (yoksa null). */
    public static function bul(string $yakit, string $kullanim): ?self
    {
        return static::query()->where('yakit', $yakit)->where('kullanim', $kullanim)->first();
    }
}
