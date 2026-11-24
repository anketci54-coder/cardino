<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['kind', 'doc_type', 'original_name', 'path', 'mime', 'size', 'created_by'])]
class VehicleFile extends Model
{
    /** Belge türleri */
    public const DOC_TYPES = [
        'fatura' => 'Fatura',
        'konsimento' => 'Konşimento (B/L)',
        'gumruk' => 'Gümrük beyannamesi',
        'acikartirma' => 'Açık artırma kağıdı',
        'ruhsat' => 'Ruhsat / muayene',
        'diger' => 'Diğer belge',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    public function docTypeLabel(): string
    {
        return self::DOC_TYPES[$this->doc_type] ?? 'Belge';
    }

    /** İnsan okunur boyut */
    public function humanSize(): string
    {
        $b = (int) $this->size;
        if ($b < 1024) {
            return $b.' B';
        }
        if ($b < 1048576) {
            return number_format($b / 1024, 0, ',', '.').' KB';
        }

        return number_format($b / 1048576, 1, ',', '.').' MB';
    }
}
