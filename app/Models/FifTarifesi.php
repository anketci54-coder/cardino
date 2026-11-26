<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Fiyat İstikrar Fonu (FİF): yakıt + motor hacmi (cc) kademesine göre oran (%). */
#[Fillable(['yakit', 'min_cc', 'max_cc', 'oran', 'sira'])]
class FifTarifesi extends Model
{
    protected $table = 'fif_tarifeleri';

    protected function casts(): array
    {
        return [
            'min_cc' => 'integer',
            'max_cc' => 'integer',
            'oran' => 'decimal:3',
            'sira' => 'integer',
        ];
    }

    /** Yakıt ve cc'ye uyan FİF oranını (%) döndürür; yoksa 0. */
    public static function oranBul(string $yakit, int $cc): float
    {
        $kayit = static::query()
            ->where('yakit', $yakit)
            ->where('min_cc', '<=', $cc)
            ->where(fn ($q) => $q->whereNull('max_cc')->orWhere('max_cc', '>=', $cc))
            ->orderBy('sira')
            ->orderBy('min_cc')
            ->first();

        return (float) ($kayit->oran ?? 0);
    }

    public function araligiMetni(): string
    {
        return $this->max_cc ? "{$this->min_cc}–{$this->max_cc} cc" : "{$this->min_cc}+ cc";
    }
}
