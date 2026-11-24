<?php

namespace App\Models;

use App\Support\Para;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

#[Fillable(['currency', 'rate_to_gbp', 'as_of'])]
class ExchangeRate extends Model
{
    protected function casts(): array
    {
        return ['rate_to_gbp' => 'decimal:8', 'as_of' => 'date'];
    }

    /** Belirtilen tarihte (ya da en yakın önceki) 1 birim para biriminin GBP karşılığı */
    public static function rateFor(string $currency, ?Carbon $date = null): ?float
    {
        if ($currency === Para::BASE) {
            return 1.0;
        }
        $date ??= Carbon::today();

        $row = static::where('currency', $currency)
            ->whereDate('as_of', '<=', $date)
            ->orderByDesc('as_of')
            ->first()
            ?? static::where('currency', $currency)->orderBy('as_of')->first();

        return $row ? (float) $row->rate_to_gbp : null;
    }

    /** Her para biriminin en güncel kuru ve tarihi (form için) */
    public static function latest(): array
    {
        $out = [Para::BASE => ['rate' => 1.0, 'as_of' => null]];
        foreach (array_keys(Para::CURRENCIES) as $c) {
            if ($c === Para::BASE) {
                continue;
            }
            $row = static::where('currency', $c)->orderByDesc('as_of')->first();
            if ($row) {
                $out[$c] = ['rate' => (float) $row->rate_to_gbp, 'as_of' => $row->as_of->format('Y-m-d')];
            }
        }

        return $out;
    }
}
