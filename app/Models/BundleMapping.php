<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BundleMapping extends Model
{
    protected $fillable = [
        'network',
        'amount',
        'package_code',
        'fallback_package_code',
        'available_from',
        'available_until',
        'description',
    ];

    /**
     * @return array{package_code: ?string, is_substituted: bool, original_code: ?string, reason: ?string}
     */
    public static function resolveForAmount(float $amount, string $network = 'safaricom'): array
    {
        $mapping = static::where('network', $network)
            ->where('amount', $amount)
            ->first();

        if (! $mapping) {
            return [
                'package_code' => null,
                'is_substituted' => false,
                'original_code' => null,
                'reason' => 'UNMAPPED_AMOUNT',
            ];
        }

        if ($mapping->available_from && $mapping->available_until) {
            $now = Carbon::now()->format('H:i:s');

            $isAvailable = $mapping->available_from <= $mapping->available_until
                ? ($now >= $mapping->available_from && $now <= $mapping->available_until)
                : ($now >= $mapping->available_from || $now <= $mapping->available_until);

            if (! $isAvailable) {
                if ($mapping->fallback_package_code) {
                    return [
                        'package_code' => $mapping->fallback_package_code,
                        'is_substituted' => true,
                        'original_code' => $mapping->package_code,
                        'reason' => 'TIME_RESTRICTION_SUBSTITUTION',
                    ];
                }

                return [
                    'package_code' => null,
                    'is_substituted' => false,
                    'original_code' => $mapping->package_code,
                    'reason' => 'RESTRICTED_NO_FALLBACK',
                ];
            }
        }

        return [
            'package_code' => $mapping->package_code,
            'is_substituted' => false,
            'original_code' => null,
            'reason' => null,
        ];
    }
}
