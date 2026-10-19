<?php

namespace App\Providers;

use App\Services\AppLocale;
use App\Services\AppTheme;
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
        AppLocale::apply();

        // Device theme mode (light/dark); screens re-apply on mount so
        // in-app toggles take effect at once. Must run AFTER the native-ui
        // provider has loaded the authored config tokens.
        AppTheme::apply();
    }
}
