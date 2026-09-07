<?php

namespace App\Providers;

use App\Notifications\Channels\TelegramAlertChannel;
use App\Services\AdminAlertService;
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
                mode: 'FAST_FAIL'
            );
        });

        $this->app->bind('provider.fallback', function () {
            return new \App\Services\Providers\FakeProviderAdapter(
                name: 'FAKE_FALLBACK',
                mode: 'SUCCESS'
            );
        });

        $this->app->singleton(AdminAlertService::class, function ($app) {
            return new AdminAlertService([
                $app->make(TelegramAlertChannel::class),
            ]);
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
