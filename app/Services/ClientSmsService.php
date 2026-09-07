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

        $message = sprintf(
            'Confirmed: your top-up of KES %s has been delivered. Ref: %s',
            $transaction->amount,
            $transaction->mpesa_receipt_number
        );

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
}
