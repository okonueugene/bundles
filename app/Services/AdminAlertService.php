<?php

namespace App\Services;

use App\Models\Transaction;
use App\Notifications\Channels\AlertChannelInterface;
use Illuminate\Support\Facades\Log;

class AdminAlertService
{
    /** @param AlertChannelInterface[] $channels Ordered primary -> fallback */
    public function __construct(private array $channels) {}

    public function dispatch(Transaction $transaction): void
    {
        if ($transaction->alert_sent_at !== null) {
            return;
        }

        $message = $this->buildMessage($transaction);

        foreach ($this->channels as $channel) {
            if ($channel->send($message)) {
                $transaction->update([
                    'alert_sent_at' => now(),
                    'alert_channel' => $channel->name(),
                ]);
                return;
            }
        }

        Log::critical("AdminAlert: ALL CHANNELS FAILED for receipt {$transaction->mpesa_receipt_number}. Manual DB check required.");
        $transaction->update(['alert_channel' => 'none']);
    }

    private function buildMessage(Transaction $transaction): string
    {
        return sprintf(
            "⚠️ Transaction needs attention\nReceipt: %s\nPhone: %s\nAmount: %s\nAttempts: %d\nStatus: %s",
            $transaction->mpesa_receipt_number,
            $transaction->phone_number,
            $transaction->amount,
            $transaction->background_attempt_count,
            $transaction->status
        );
    }
}
