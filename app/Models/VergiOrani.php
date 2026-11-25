<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Gümrük vergisi oranı: yakıt türü × kullanım. */
#[Fillable(['yakit', 'kullanim', 'oran'])]
class VergiOrani extends Model
{
    protected $table = 'vergi_oranlari';

    public const YAKITLAR = [
        'benzin_dizel' => 'Benzin / Dizel',
        'elektrik_hibrit' => 'Elektrik / Hibrit',
    ];

    public const KULLANIMLAR = [
        'ozel' => 'Özel',
        'ticari' => 'Ticari',
    ];

    protected function casts(): array
    {
        return ['oran' => 'decimal:3'];
    }

    /** Yakıt + kullanım için oran (%) döndürür; yoksa 0. */
    public static function oranBul(string $yakit, string $kullanim): float
    {
        $kayit = static::query()->where('yakit', $yakit)->where('kullanim', $kullanim)->first();

        return (float) ($kayit->oran ?? 0);
    }
}
