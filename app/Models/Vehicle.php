<?php

namespace App\Models;

use App\Models\Concerns\BelongsToGallery;
use App\Support\Para;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

#[Fillable([
    'gallery_id', 'chassis_no', 'engine_no', 'origin', 'shipping_route', 'make', 'model', 'grade', 'model_year',
    'first_registration', 'color', 'mileage_km', 'engine_cc', 'fuel', 'transmission', 'status',
    'purchase_date', 'sold_on', 'buyer_name', 'sale_amount', 'sale_currency', 'sale_rate', 'sale_base', 'is_trade_in',
    'notes', 'created_by',
])]
class Vehicle extends Model
{
    use BelongsToGallery, SoftDeletes;

    public const ORIGINS = ['japonya' => 'Japonya', 'ingiltere' => 'İngiltere', 'diger' => 'Diğer'];

    public const ROUTES = ['direkt' => 'Doğrudan (Gazimağusa / Girne)', 'mersin' => 'Mersin aktarmalı'];

    public const STATUSES = ['yolda' => 'Yolda', 'gumrukte' => 'Gümrükte', 'stokta' => 'Stokta', 'satildi' => 'Satıldı'];

    public const FUELS = ['benzin' => 'Benzin', 'dizel' => 'Dizel', 'hibrit' => 'Hibrit', 'elektrik' => 'Elektrik'];

    public const TRANSMISSIONS = ['otomatik' => 'Otomatik', 'manuel' => 'Manuel'];

    /** KKTC ithalat yaş sınırı (yıl), ilk kayıt tarihinden itibaren */
    public const IMPORT_AGE_LIMIT = 5;

    protected function casts(): array
    {
        return [
            'first_registration' => 'date',
            'purchase_date' => 'date',
            'sold_on' => 'date',
            'mileage_km' => 'integer',
            'engine_cc' => 'integer',
            'model_year' => 'integer',
            'sale_amount' => 'decimal:2',
            'sale_rate' => 'decimal:8',
            'sale_base' => 'decimal:2',
            'is_trade_in' => 'boolean',
        ];
    }

    public function costs(): HasMany
    {
        return $this->hasMany(VehicleCost::class);
    }

    public function title(): string
    {
        return trim(collect([$this->model_year, $this->make, $this->model, $this->grade])->filter()->implode(' '));
    }

    public function label(string $field): string
    {
        $map = [
            'origin' => self::ORIGINS,
            'shipping_route' => self::ROUTES,
            'status' => self::STATUSES,
            'fuel' => self::FUELS,
            'transmission' => self::TRANSMISSIONS,
        ][$field];

        return $map[$this->{$field}] ?? '—';
    }

    /** Bugün itibarıyla ilk kayıttan beri geçen ay */
    public function ageInMonths(): ?int
    {
        return $this->first_registration ? (int) $this->first_registration->diffInMonths(Carbon::today()) : null;
    }

    /** 5 yaş sınırının dolacağı tarih */
    public function importDeadline(): ?Carbon
    {
        return $this->first_registration?->copy()->addYears(self::IMPORT_AGE_LIMIT);
    }

    public function daysInStock(): ?int
    {
        $end = $this->sold_on ?? Carbon::today();

        return $this->purchase_date ? (int) $this->purchase_date->diffInDays($end) : null;
    }

    /** Toplam maliyet (GBP). İlişki yüklüyse onu kullanır, değilse sorgular. */
    public function totalCostBase(): float
    {
        return (float) ($this->relationLoaded('costs')
            ? $this->costs->sum('base_amount')
            : $this->costs()->sum('base_amount'));
    }

    /** Satış kârı (GBP): satış − toplam maliyet. Satış yoksa null. */
    public function profitBase(): ?float
    {
        if ($this->sale_base === null) {
            return null;
        }

        return (float) $this->sale_base - $this->totalCostBase();
    }

    public function isSold(): bool
    {
        return $this->sale_base !== null;
    }

    public function baseSymbol(): string
    {
        return Para::symbol(Para::BASE);
    }
}
