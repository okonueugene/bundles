<?php

namespace App\Notifications\Channels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramAlertChannel implements AlertChannelInterface
{
    public function name(): string
    {
        return 'telegram';
    }

    public function send(string $message): bool
    {
        $token = config('admin_alert.telegram.bot_token');
        $chatId = config('admin_alert.telegram.chat_id');

        if (! $token || ! $chatId) {
            Log::warning('AdminAlert: Telegram not configured, skipping.');
            return false;
        }

        try {
            $response = Http::timeout(config('admin_alert.timeout_seconds', 5))
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("AdminAlert: Telegram send failed: {$e->getMessage()}");
            return false;
        }
    }
}
