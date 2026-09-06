<?php

namespace App\Providers;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
