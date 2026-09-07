<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Jobs\FulfillOrderJob;
use App\Services\Providers\ProviderAdapter;
use App\Services\Providers\StatusCheckResult;
use App\Services\Providers\TopUpResult;
use Mockery;
use Tests\TestCase;

class FulfillmentSchedulerTest extends TestCase
{
    public function test_scheduler_continues_after_one_provider_exception(): void
    {
        $suffix = now()->format('YmdHisv');
        $phoneSuffix = random_int(100000, 999997);
        $phones = [
            '254712'.$phoneSuffix,
            '254712'.($phoneSuffix + 1),
            '254712'.($phoneSuffix + 2),
        ];
        $references = [];

        foreach ($phones as $index => $phone) {
            $references[] = "OKOA-S{$suffix}-{$index}";
            $transaction = Transaction::create([
                'order_reference' => $references[$index],
                'mpesa_receipt_number' => "S{$suffix}{$index}",
                'phone_number' => $phone,
                'amount' => 99,
                'package_code' => 'DATA_1GB',
                'status' => 'queued_for_retry',
                'attempt_count' => 1,
                'background_attempt_count' => 0,
                'last_attempted_provider' => 'provider.primary',
            ]);
            $transaction->update(['updated_at' => now()->subMinutes(5)]);
        }

        $primary = new class($phones[0]) implements ProviderAdapter
        {
            public function __construct(private string $exceptionPhone) {}

            public function getName(): string
            {
                return 'TEST_PRIMARY';
            }

            public function topUp(string $phoneNumber, float $amount, ?string $packageCode = null): TopUpResult
            {
                if ($phoneNumber === $this->exceptionPhone) {
                    throw new \RuntimeException('Simulated provider exception');
                }

                return TopUpResult::success($this->getName());
            }

            public function checkStatus(string $phoneNumber, float $amount, ?string $packageCode = null): StatusCheckResult
            {
                return StatusCheckResult::confirmedFailed();
            }
        };

        $fallback = new class implements ProviderAdapter
        {
            public function getName(): string
            {
                return 'TEST_FALLBACK';
            }

            public function topUp(string $phoneNumber, float $amount, ?string $packageCode = null): TopUpResult
            {
                return TopUpResult::timeout();
            }

            public function checkStatus(string $phoneNumber, float $amount, ?string $packageCode = null): StatusCheckResult
            {
                return StatusCheckResult::confirmedFailed();
            }
        };

        $this->app->instance('provider.primary', $primary);
        $this->app->instance('provider.fallback', $fallback);
        $sms = Mockery::mock(\App\Services\ClientSmsService::class);
        $sms->shouldReceive('sendFulfillmentConfirmation')->zeroOrMoreTimes();
        $this->app->instance(\App\Services\ClientSmsService::class, $sms);

        Transaction::whereIn('order_reference', $references)
            ->where('status', 'queued_for_retry')
            ->get()
            ->each(fn ($transaction) => (new FulfillOrderJob())->handle($transaction->id));

        $transactions = Transaction::whereIn('order_reference', $references)
            ->orderBy('id')
            ->get();

        $this->assertSame('queued_for_retry', $transactions[0]->status);
        $this->assertSame('fulfilled', $transactions[1]->status);
        $this->assertSame('fulfilled', $transactions[2]->status);
        $this->assertSame(1, $transactions[1]->background_attempt_count);
        $this->assertSame(1, $transactions[2]->background_attempt_count);
    }
}
