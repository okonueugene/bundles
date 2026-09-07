<?php

namespace App\Services\Providers;

interface ProviderAdapter
{
    public function getName(): string;

    /**
     * $packageCode is optional to support plain airtime providers (e.g.
     * Africa's Talking) as well as future dynamic bundle adapters.
     */
    public function topUp(string $phoneNumber, float $amount, ?string $packageCode = null): TopUpResult;

    public function checkStatus(string $phoneNumber, float $amount, ?string $packageCode = null): StatusCheckResult;
}
