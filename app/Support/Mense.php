<?php

namespace App\Support;

/**
 * Şasi numarasından menşe tahmini.
 * Japon iç pazar araçları: model kodu + tire + seri (ör. NZE161-1234567).
 * İngiltere (ve Avrupa) araçları: 17 haneli VIN (I, O, Q harfleri kullanılmaz).
 */
class Mense
{
    public static function normalize(?string $chassis): string
    {
        return strtoupper(preg_replace('/\s+/', '', (string) $chassis));
    }

    public static function guess(?string $chassis): ?string
    {
        $c = self::normalize($chassis);
        if (preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $c)) {
            return 'ingiltere';
        }
        if (preg_match('/^[A-Z0-9]{2,10}-\d{4,8}$/', $c)) {
            return 'japonya';
        }

        return null;
    }
}
