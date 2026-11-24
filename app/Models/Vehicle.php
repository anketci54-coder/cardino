<?php

namespace App\Models;

use App\Models\Concerns\BelongsToGallery;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

#[Fillable([
    'gallery_id', 'chassis_no', 'engine_no', 'origin', 'make', 'model', 'grade', 'model_year',
    'first_registration', 'color', 'mileage_km', 'engine_cc', 'fuel', 'transmission', 'status',
    'purchase_date', 'notes', 'created_by',
])]
class Vehicle extends Model
{
    use BelongsToGallery, SoftDeletes;

    public const ORIGINS = ['japonya' => 'Japonya', 'ingiltere' => 'İngiltere', 'diger' => 'Diğer'];

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
            'mileage_km' => 'integer',
            'engine_cc' => 'integer',
            'model_year' => 'integer',
        ];
    }

    public function title(): string
    {
        return trim(collect([$this->model_year, $this->make, $this->model, $this->grade])->filter()->implode(' '));
    }

    public function label(string $field): string
    {
        $map = ['origin' => self::ORIGINS, 'status' => self::STATUSES, 'fuel' => self::FUELS, 'transmission' => self::TRANSMISSIONS][$field];

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
        return $this->purchase_date ? (int) $this->purchase_date->diffInDays(Carbon::today()) : null;
    }
}
