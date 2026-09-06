<?php

namespace App\Services\Providers;

class TopUpResult
{
    public function __construct(
        public readonly bool $isSuccess,
        public readonly bool $isFastFail,
        public readonly ?string $providerName = null,
        public readonly ?string $errorMessage = null
    ) {}

    public static function success(string $providerName): self
    {
        return new self(isSuccess: true, isFastFail: false, providerName: $providerName);
    }

    public static function fastFail(string $errorMessage): self
    {
        return new self(isSuccess: false, isFastFail: true, errorMessage: $errorMessage);
    }

    public static function timeout(string $errorMessage = 'Network Timeout'): self
    {
        return new self(isSuccess: false, isFastFail: false, errorMessage: $errorMessage);
    }
}
