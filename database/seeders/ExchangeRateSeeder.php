<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Başlangıç kurları (yaklaşık, Kasım 2026). Yönetici `cardino:kur` ile günceller.
     * rate_to_gbp = 1 birim bu para kaç GBP eder.
     */
    public function run(): void
    {
        $today = Carbon::today()->toDateString();
        $rates = [
            'JPY' => 0.0052,  // 1 GBP ≈ 192 JPY
            'USD' => 0.79,    // 1 GBP ≈ 1.27 USD
            'EUR' => 0.86,    // 1 GBP ≈ 1.16 EUR
            'TRY' => 0.022,   // 1 GBP ≈ 45 TRY (oynak, güncellenmeli)
        ];
        foreach ($rates as $cur => $rate) {
            ExchangeRate::firstOrCreate(
                ['currency' => $cur, 'as_of' => $today],
                ['rate_to_gbp' => $rate]
            );
        }
    }
}
