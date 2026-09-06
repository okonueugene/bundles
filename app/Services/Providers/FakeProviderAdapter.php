<?php

namespace App\Services\Providers;

class FakeProviderAdapter implements ProviderAdapter
{
    public function __construct(
        private string $name = 'FAKE_PROVIDER',
        private string $mode = 'SUCCESS'
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function topUp(string $phoneNumber, float $amount, ?string $packageCode = null): TopUpResult
    {
        return match ($this->mode) {
            'SUCCESS' => TopUpResult::success($this->name),
            'FAST_FAIL' => TopUpResult::fastFail("Simulated Insufficient Balance on {$this->name}"),
            'TIMEOUT' => TopUpResult::timeout("Simulated HTTP Timeout on {$this->name}"),
            default => TopUpResult::fastFail("Unknown mode configuration"),
        };
    }
}
