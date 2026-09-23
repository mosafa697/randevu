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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Device language for every request (validation messages included);
        // screens re-apply it on mount so in-app switches take effect at once.
        \App\Services\AppLocale::apply();
    }
}
