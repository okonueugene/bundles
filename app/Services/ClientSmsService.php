<?php

namespace App\Services;

use App\Models\Transaction;
use App\Services\Providers\AfricasTalkingAdapter;
use Illuminate\Support\Facades\Log;

class ClientSmsService
{
    public function __construct(private AfricasTalkingAdapter $adapter) {}

    public function sendFulfillmentConfirmation(Transaction $transaction): void
    {
        if (! config('client_sms.enabled', true) || $transaction->client_sms_sent_at !== null) {
            return;
        }

        $message = $this->buildMessage($transaction);

        try {
            $sent = $this->adapter->sendSms($transaction->phone_number, $message);

            if ($sent) {
                $transaction->update(['client_sms_sent_at' => now()]);
            } else {
                Log::warning("ClientSms: send failed (no exception) for receipt {$transaction->mpesa_receipt_number}");
            }
        } catch (\Throwable $e) {
            Log::error("ClientSms: exception sending confirmation for receipt {$transaction->mpesa_receipt_number}: {$e->getMessage()}");
        }
    }

    private function buildMessage(Transaction $transaction): string
    {
        if ($transaction->is_substituted) {
            return sprintf(
                'Hi, your %s bundle is currently paused. We sent %s instead for your KES %s. Ref: %s',
                $transaction->original_package_code,
                $transaction->package_code,
                $transaction->amount,
                $transaction->mpesa_receipt_number
            );
        }

        return sprintf(
            'Confirmed: your %s top-up has been delivered. Ref: %s',
            $transaction->package_code,
            $transaction->mpesa_receipt_number
        );
    }
}
