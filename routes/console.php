<?php

use App\Jobs\FulfillOrderJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Transaction;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $maxBackgroundRetries = config('fulfillment.max_background_retries', 2);
    $backoffs = config('fulfillment.backoff_minutes', [1 => 1, 2 => 3]);

    Transaction::where('status', 'queued_for_retry')
        ->where('background_attempt_count', '<', $maxBackgroundRetries)
        ->get()
        ->filter(function ($tx) use ($backoffs) {
            $upcomingAttempt = $tx->background_attempt_count + 1;
            $requiredDelayMinutes = $backoffs[$upcomingAttempt] ?? 1;
            return $tx->updated_at->addMinutes($requiredDelayMinutes)->isPast();
        })
        ->each(fn ($tx) => FulfillOrderJob::dispatch($tx->id));
})->everyMinute()->name('fulfill-order-sweep')->withoutOverlapping(5);
