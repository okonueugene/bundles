<?php

return [
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_ADMIN_CHAT_ID'),
    ],
    'sms' => [
        'recipient' => env('ADMIN_ALERT_SMS_RECIPIENT'),
    ],
    'timeout_seconds' => 5,
];
