<?php

namespace App\Support;

class KenyanPhone
{
    public static function normalize(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (preg_match('/^254[17]\d{8}$/', $digits) === 1) {
            return $digits;
        }

        if (preg_match('/^0[17]\d{8}$/', $digits) === 1) {
            return '254'.substr($digits, 1);
        }

        return null;
    }

    public static function formatLocal(string $phone): string
    {
        $normalized = static::normalize($phone) ?? preg_replace('/\D+/', '', $phone);

        if (is_string($normalized) && str_starts_with($normalized, '254') && strlen($normalized) === 12) {
            return '0'.substr($normalized, 3);
        }

        return $phone;
    }
}
