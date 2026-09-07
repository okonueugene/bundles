<?php

namespace App\Services\Providers;

class StatusCheckResult
{
    public function __construct(
        public readonly string $state
    ) {}

    public static function confirmedSuccess(): self
    {
        return new self('CONFIRMED_SUCCESS');
    }

    public static function confirmedFailed(): self
    {
        return new self('CONFIRMED_FAILED');
    }

    public static function unknown(): self
    {
        return new self('UNKNOWN');
    }
}
