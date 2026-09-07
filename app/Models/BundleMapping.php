<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BundleMapping extends Model
{
    protected $appends = ['is_available'];

    protected $fillable = [
        'network',
        'slug',
        'amount',
        'type',
        'package_code',
        'fallback_package_code',
        'available_from',
        'available_until',
        'description',
        'validity',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function forOrder(string $packageCode, float|string $amount, string $network = 'safaricom'): ?self
    {
        $query = static::query()
            ->where('network', $network)
            ->when($packageCode !== '', fn ($query) => $query->where('package_code', $packageCode))
            ->when($packageCode === '', fn ($query) => $query->where('amount', $amount));

        return $query->first();
    }

    public function getIsAvailableAttribute(): bool
    {
        if (! $this->available_from || ! $this->available_until) {
            return true;
        }

        $now = Carbon::now()->format('H:i:s');

        return $this->available_from <= $this->available_until
            ? ($now >= $this->available_from && $now <= $this->available_until)
            : ($now >= $this->available_from || $now <= $this->available_until);
    }

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
