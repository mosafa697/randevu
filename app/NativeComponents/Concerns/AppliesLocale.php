<?php

namespace App\NativeComponents\Concerns;

use App\Services\AppLocale;

trait AppliesLocale
{
    protected function applyLocale(): void
    {
        AppLocale::apply();
    }

    protected function currentLocale(): string
    {
        return AppLocale::current();
    }
}
