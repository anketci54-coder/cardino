<?php

namespace App\Models;

use App\Models\Concerns\BelongsToGallery;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Açık artırma fırsatı (aday araç): değerlendirilir, AL/GEÇ kararı verilir,
 * izleme listesinde tutulur; alınırsa araca çevrilir.
 */
#[Fillable([
    'gallery_id', 'kaynak', 'make', 'model', 'chassis_no', 'origin', 'shipping_route', 'model_year',
    'first_registration', 'engine_cc', 'fuel', 'usage', 'buy_price', 'buy_currency', 'customs_value', 'shipping', 'expected_sale',
    'landed_cost', 'suggested_sale', 'expected_profit', 'margin', 'importable', 'import_deadline',
    'verdict', 'status', 'vehicle_id', 'notes', 'created_by',
])]
class Firsat extends Model
{
    use BelongsToGallery, SoftDeletes;

    protected $table = 'firsatlar';

    /** Yakıt (vergi sınıfı — MaliyetHesaplayici ile aynı) */
    public const FUELS = ['benzin_dizel' => 'Benzin / Dizel', 'hibrit' => 'Hibrit', 'elektrik' => 'Elektrik'];

    public const USAGES = ['ozel' => 'Özel', 'ticari' => 'Ticari (T)'];

    public const VERDICTS = ['al' => 'AL', 'gec' => 'GEÇ', 'dusun' => 'DÜŞÜN'];

    public const STATUSES = ['izleniyor' => 'İzleniyor', 'alindi' => 'Alındı', 'gecildi' => 'Geçildi'];

    protected function casts(): array
    {
        return [
            'buy_price' => 'decimal:2',
            'customs_value' => 'decimal:2',
            'shipping' => 'decimal:2',
            'expected_sale' => 'decimal:2',
            'landed_cost' => 'decimal:2',
            'suggested_sale' => 'decimal:2',
            'expected_profit' => 'decimal:2',
            'margin' => 'decimal:2',
            'importable' => 'boolean',
            'import_deadline' => 'date',
            'first_registration' => 'date',
            'model_year' => 'integer',
            'engine_cc' => 'integer',
        ];
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function baslik(): string
    {
        $ad = trim(($this->model_year ? $this->model_year.' ' : '').($this->make ? $this->make.' ' : '').$this->model);

        return $ad !== '' ? $ad : 'Aday araç #'.$this->id;
    }

    public function verdictLabel(): string
    {
        return self::VERDICTS[$this->verdict] ?? $this->verdict;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
