<?php

namespace App\Support;

class Para
{
    /** Hesap (taban) para birimi: KKTC piyasası sterlinle fiyatlanır */
    public const BASE = 'GBP';

    public const CURRENCIES = [
        'GBP' => '£ Sterlin',
        'JPY' => '¥ Yen',
        'USD' => '$ Dolar',
        'EUR' => '€ Euro',
        'TRY' => '₺ Türk lirası',
    ];

    public const SYMBOLS = ['GBP' => '£', 'JPY' => '¥', 'USD' => '$', 'EUR' => '€', 'TRY' => '₺'];

    public static function symbol(?string $c): string
    {
        return self::SYMBOLS[$c] ?? ($c ?? '');
    }

    /** Tutarı para birimine göre biçimlendirir (yen kuruşsuz) */
    public static function format(float|int|string|null $amount, string $currency): string
    {
        $amount = (float) $amount;
        $decimals = $currency === 'JPY' ? 0 : 2;

        return self::symbol($currency).number_format($amount, $decimals, ',', '.');
    }
}
