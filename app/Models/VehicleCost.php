<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vehicle_id', 'category', 'description', 'amount', 'currency', 'rate', 'base_amount', 'spent_on', 'created_by'])]
class VehicleCost extends Model
{
    /** Kategoriler: giriş sırasına göre */
    public const CATEGORIES = [
        'alis' => 'Alış fiyatı',
        'acikartirma' => 'Açık artırma ücreti',
        'icnakliye' => 'İç nakliye (Japonya/İngiltere)',
        'navlun' => 'Navlun',
        'sigorta' => 'Sigorta',
        'mersin' => 'Mersin aktarma / feribot',
        'gumruk' => 'Gümrük vergisi',
        'digervergi' => 'Diğer vergiler (FİF, fon, KDV)',
        'liman' => 'Liman / ardiye',
        'komisyoncu' => 'Komisyoncu',
        'kayit' => 'Muayene / ruhsat / plaka',
        'tamir' => 'Tamir',
        'boya' => 'Boya',
        'temizlik' => 'Temizlik / hazırlık',
        'reklam' => 'Reklam',
        'diger' => 'Diğer',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'rate' => 'decimal:8',
            'base_amount' => 'decimal:2',
            'spent_on' => 'date',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
