<?php

return [
    'max_background_retries' => env('FULFILLMENT_MAX_BACKGROUND_RETRIES', 2),
    'backoff_minutes' => [
        1 => 1,
        2 => 3,
    ],
];
