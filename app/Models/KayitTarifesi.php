<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Kayıt / ruhsat harcı: motor hacmi (cc) ve yaş aralığına göre. */
#[Fillable(['min_cc', 'max_cc', 'min_yas', 'max_yas', 'tutar_gbp', 'oran', 'aciklama', 'sira'])]
class KayitTarifesi extends Model
{
    protected $table = 'kayit_tarifeleri';

    protected function casts(): array
    {
        return [
            'min_cc' => 'integer',
            'max_cc' => 'integer',
            'min_yas' => 'integer',
            'max_yas' => 'integer',
            'tutar_gbp' => 'decimal:2',
            'oran' => 'decimal:3',
            'sira' => 'integer',
        ];
    }

    /** cc ve yaşa uyan tarifeyi bulur (ilk eşleşen, sıraya göre). */
    public static function bul(int $cc, int $yas): ?self
    {
        return static::query()
            ->where('min_cc', '<=', $cc)
            ->where(fn ($q) => $q->whereNull('max_cc')->orWhere('max_cc', '>=', $cc))
            ->where('min_yas', '<=', $yas)
            ->where(fn ($q) => $q->whereNull('max_yas')->orWhere('max_yas', '>=', $yas))
            ->orderBy('sira')
            ->orderBy('min_cc')
            ->first();
    }

    /** Bu tarifenin araç değerine göre harç tutarını (GBP) hesaplar. */
    public function harc(float $aracDegeriBase): float
    {
        if ($this->tutar_gbp !== null) {
            return (float) $this->tutar_gbp;
        }
        if ($this->oran !== null) {
            return round($aracDegeriBase * (float) $this->oran / 100, 2);
        }

        return 0.0;
    }

    public function araligiMetni(): string
    {
        $cc = $this->max_cc ? "{$this->min_cc}–{$this->max_cc} cc" : "{$this->min_cc}+ cc";
        $yas = $this->max_yas !== null ? "{$this->min_yas}–{$this->max_yas} yaş" : "{$this->min_yas}+ yaş";

        return "$cc · $yas";
    }
}
