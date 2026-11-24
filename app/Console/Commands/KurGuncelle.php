<?php

namespace App\Console\Commands;

use App\Models\ExchangeRate;
use App\Support\Para;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class KurGuncelle extends Command
{
    protected $signature = 'cardino:kur {para : JPY, USD, EUR veya TRY} {kur : 1 birimin GBP karşılığı, ör. 0.0052} {--tarih= : Kur tarihi (YYYY-AA-GG), boşsa bugün}';

    protected $description = 'Bir para biriminin GBP kurunu kaydeder';

    public function handle(): int
    {
        $cur = strtoupper($this->argument('para'));
        if (! array_key_exists($cur, Para::CURRENCIES) || $cur === Para::BASE) {
            $this->error('Para birimi JPY, USD, EUR veya TRY olmalı.');

            return self::FAILURE;
        }
        $rate = (float) str_replace(',', '.', $this->argument('kur'));
        if ($rate <= 0) {
            $this->error('Kur sıfırdan büyük olmalı.');

            return self::FAILURE;
        }
        $date = $this->option('tarih') ? Carbon::parse($this->option('tarih')) : Carbon::today();

        ExchangeRate::updateOrCreate(
            ['currency' => $cur, 'as_of' => $date->toDateString()],
            ['rate_to_gbp' => $rate]
        );

        $this->info("Kur kaydedildi: 1 {$cur} = {$rate} GBP ({$date->format('d.m.Y')})");

        return self::SUCCESS;
    }
}
