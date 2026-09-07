<?php

namespace App\Providers;

use App\Notifications\Channels\TelegramAlertChannel;
use App\Services\AdminAlertService;
use App\Services\MpesaService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('provider.primary', function () {
            return new \App\Services\Providers\FakeProviderAdapter(
                name: 'FAKE_PRIMARY',
                mode: env('FAKE_PRIMARY_MODE', 'FAST_FAIL'),
                statusCheckMode: env('FAKE_PRIMARY_STATUS_CHECK_MODE', 'CONFIRMED_FAILED')
            );
        });

        $this->app->bind('provider.fallback', function () {
            return new \App\Services\Providers\FakeProviderAdapter(
                name: 'FAKE_FALLBACK',
                mode: env('FAKE_FALLBACK_MODE', 'SUCCESS'),
                statusCheckMode: env('FAKE_FALLBACK_STATUS_CHECK_MODE', 'CONFIRMED_FAILED')
            );
        });

        $this->app->singleton(AdminAlertService::class, function ($app) {
            return new AdminAlertService([
                $app->make(TelegramAlertChannel::class),
            ]);
        });

        $this->app->singleton(MpesaService::class, function ($app) {
            return new MpesaService(
                consumerKey: config('services.mpesa.consumer_key', ''),
                consumerSecret: config('services.mpesa.consumer_secret', ''),
                shortcode: config('services.mpesa.shortcode', ''),
                passkey: config('services.mpesa.passkey', ''),
                callbackUrl: config('services.mpesa.callback_url', config('app.url') . '/api/v1/mpesa/confirm'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
