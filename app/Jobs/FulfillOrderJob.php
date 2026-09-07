<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Services\Providers\ProviderAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FulfillOrderJob
{
    private int $transactionId;

    public function handle(int $transactionId): void
    {
        $this->transactionId = $transactionId;
        $workerId = Str::uuid()->toString();

        $claimed = DB::table('transactions')
            ->where('id', $this->transactionId)
            ->where('status', 'queued_for_retry')
            ->update([
                'claimed_by' => $workerId,
                'status' => 'processing',
                'updated_at' => now(),
            ]);

        if ($claimed === 0) {
            return;
        }

        $transaction = Transaction::find($this->transactionId);

        try {
            /** @var ProviderAdapter $primaryProvider */
            $primaryProvider = app('provider.primary');
            /** @var ProviderAdapter $fallbackProvider */
            $fallbackProvider = app('provider.fallback');

            $providerToCheck = $transaction->last_attempted_provider
                ? app($transaction->last_attempted_provider)
                : $primaryProvider;

            $statusResult = $providerToCheck->checkStatus(
                $transaction->phone_number,
                (float) $transaction->amount,
                $transaction->package_code
            );

            if ($statusResult->state === 'CONFIRMED_SUCCESS') {
                $transaction->update([
                    'status' => 'fulfilled',
                    'provider_used' => $providerToCheck->getName(),
                    'claimed_by' => null,
                ]);
                app(\App\Services\ClientSmsService::class)->sendFulfillmentConfirmation($transaction);
                return;
            }

            $transaction->increment('attempt_count');
            $transaction->increment('background_attempt_count');
            $transaction->refresh();

            $result = $primaryProvider->topUp(
                $transaction->phone_number,
                (float) $transaction->amount,
                $transaction->package_code
            );

            if ($result->isSuccess) {
                $transaction->update([
                    'status' => 'fulfilled',
                    'provider_used' => $result->providerName,
                    'claimed_by' => null,
                ]);
                app(\App\Services\ClientSmsService::class)->sendFulfillmentConfirmation($transaction);
                return;
            }

            Log::info("Primary failed on background retry for receipt {$transaction->mpesa_receipt_number}. Attempting fallback...");
            $fallbackResult = $fallbackProvider->topUp(
                $transaction->phone_number,
                (float) $transaction->amount,
                $transaction->package_code
            );

            if ($fallbackResult->isSuccess) {
                $transaction->update([
                    'status' => 'fulfilled',
                    'provider_used' => $fallbackResult->providerName,
                    'claimed_by' => null,
                ]);
                app(\App\Services\ClientSmsService::class)->sendFulfillmentConfirmation($transaction);
                return;
            }

            $this->requeueOrEscalate($transaction, 'provider.fallback');
        } catch (\Throwable $e) {
            Log::error("FulfillOrderJob Exception for Tx {$this->transactionId}: " . $e->getMessage());
            $this->requeueOrEscalate($transaction, 'provider.fallback');
            throw $e;
        }
    }

    private function requeueOrEscalate(Transaction $transaction, string $lastAttemptedProvider): void
    {
        $maxBackgroundRetries = config('fulfillment.max_background_retries', 2);

        if ($transaction->background_attempt_count >= $maxBackgroundRetries) {
            $transaction->update([
                'status' => 'needs_attention',
                'claimed_by' => null,
            ]);

            Log::critical("TRANSACTION NEEDS ATTENTION: Receipt {$transaction->mpesa_receipt_number} failed after {$transaction->background_attempt_count} background attempts (attempt_count total: {$transaction->attempt_count}).");
            app(\App\Services\AdminAlertService::class)->dispatch($transaction);
        } else {
            $transaction->update([
                'status' => 'queued_for_retry',
                'claimed_by' => null,
                'last_attempted_provider' => $lastAttemptedProvider,
            ]);
        }
    }
}
