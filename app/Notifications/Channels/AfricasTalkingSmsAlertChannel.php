<?php

namespace App\Notifications\Channels;

use App\Services\Providers\AfricasTalkingAdapter;
use Illuminate\Support\Facades\Log;

class AfricasTalkingSmsAlertChannel implements AlertChannelInterface
{
    public function __construct(private AfricasTalkingAdapter $adapter) {}

    public function name(): string
    {
        return 'sms';
    }

    public function send(string $message): bool
    {
        $recipient = config('admin_alert.sms.recipient');

        if (! $recipient) {
            Log::warning('AdminAlert: SMS recipient not configured, skipping.');
            return false;
        }

        try {
            return $this->adapter->sendSms($recipient, $message);
        } catch (\Throwable $e) {
            Log::error("AdminAlert: SMS send failed: {$e->getMessage()}");
            return false;
        }
    }
}
