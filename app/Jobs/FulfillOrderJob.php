<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Services\Providers\ProviderAdapter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FulfillOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $transactionId) {}

    public function handle(): void
    {
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

            if (method_exists($primaryProvider, 'checkStatus')) {
                $statusResult = $primaryProvider->checkStatus($transaction->mpesa_receipt_number);

                if ($statusResult && $statusResult->isSuccess) {
                    $transaction->update([
                        'status' => 'fulfilled',
                        'provider_used' => $primaryProvider->getName(),
                        'claimed_by' => null,
                    ]);
                    return;
                }
            }

            $transaction->increment('attempt_count');
            $transaction->increment('background_attempt_count');
            $transaction->refresh();

            $result = $primaryProvider->topUp($transaction->phone_number, (float) $transaction->amount);

            if ($result->isSuccess) {
                $transaction->update([
                    'status' => 'fulfilled',
                    'provider_used' => $result->providerName,
                    'claimed_by' => null,
                ]);
                return;
            }

            Log::info("Primary failed on background retry for receipt {$transaction->mpesa_receipt_number}. Attempting fallback...");
            $fallbackResult = $fallbackProvider->topUp($transaction->phone_number, (float) $transaction->amount);

            if ($fallbackResult->isSuccess) {
                $transaction->update([
                    'status' => 'fulfilled',
                    'provider_used' => $fallbackResult->providerName,
                    'claimed_by' => null,
                ]);
                return;
            }

            $this->requeueOrEscalate($transaction);
        } catch (\Throwable $e) {
            Log::error("FulfillOrderJob Exception for Tx {$this->transactionId}: " . $e->getMessage());
            $this->requeueOrEscalate($transaction);
            throw $e;
        }
    }

    private function requeueOrEscalate(Transaction $transaction): void
    {
        $maxBackgroundRetries = config('fulfillment.max_background_retries', 2);

        if ($transaction->background_attempt_count >= $maxBackgroundRetries) {
            $transaction->update([
                'status' => 'needs_attention',
                'claimed_by' => null,
            ]);

            Log::critical("TRANSACTION NEEDS ATTENTION: Receipt {$transaction->mpesa_receipt_number} failed after {$transaction->background_attempt_count} background attempts (attempt_count total: {$transaction->attempt_count}).");
            // TODO: AdminAlert::dispatch($transaction) — WhatsApp/Telegram webhook
            // primary, Africa's Talking SMS secondary. Separate task.
        } else {
            $transaction->update([
                'status' => 'queued_for_retry',
                'claimed_by' => null,
            ]);
        }
    }
}
