<?php

namespace App\Services;

use App\Models\BundleMapping;
use App\Models\Transaction;
use App\Services\Providers\ProviderAdapter;
use Illuminate\Support\Facades\Log;

class FulfillmentService
{
    public function attemptCascade(Transaction $transaction): void
    {
        if ($transaction->package_code === null) {
            $resolution = BundleMapping::resolveForAmount((float) $transaction->amount);

            if ($resolution['package_code'] === null) {
                $transaction->update([
                    'status' => 'needs_attention',
                    'raw_payload' => array_merge($transaction->raw_payload ?? [], [
                        'resolution' => $resolution,
                    ]),
                ]);
                app(AdminAlertService::class)->dispatch($transaction);

                return;
            }

            $transaction->update([
                'package_code' => $resolution['package_code'],
                'is_substituted' => $resolution['is_substituted'],
                'original_package_code' => $resolution['original_code'],
            ]);
        }

        /** @var ProviderAdapter $primaryProvider */
        $primaryProvider = app('provider.primary');
        /** @var ProviderAdapter $fallbackProvider */
        $fallbackProvider = app('provider.fallback');

        $result = $primaryProvider->topUp(
            $transaction->phone_number,
            (float) $transaction->amount,
            $transaction->package_code
        );

        if ($result->isSuccess) {
            $transaction->update([
                'status' => 'fulfilled',
                'provider_used' => $result->providerName,
                'attempt_count' => 1,
            ]);
            app(ClientSmsService::class)->sendFulfillmentConfirmation($transaction);

            return;
        }

        if ($result->isFastFail) {
            Log::info("Primary provider fast-failed for receipt {$transaction->mpesa_receipt_number}. Attempting synchronous fallback...");

            $fallbackResult = $fallbackProvider->topUp(
                $transaction->phone_number,
                (float) $transaction->amount,
                $transaction->package_code
            );

            if ($fallbackResult->isSuccess) {
                $transaction->update([
                    'status' => 'fulfilled',
                    'provider_used' => $fallbackResult->providerName,
                    'attempt_count' => 2,
                ]);
                app(ClientSmsService::class)->sendFulfillmentConfirmation($transaction);

                return;
            }
        }

        $transaction->update([
            'status' => 'queued_for_retry',
            'attempt_count' => $result->isFastFail ? 2 : 1,
            'last_attempted_provider' => $result->isFastFail
                ? 'provider.fallback'
                : 'provider.primary',
        ]);
    }
}
