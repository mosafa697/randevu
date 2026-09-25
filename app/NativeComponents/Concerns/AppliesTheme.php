<?php

namespace App\NativeComponents\Concerns;

use App\Services\AppTheme;

/**
 * Shared theme-mode state for screens: `$theme` mirrors the active mode so
 * a toggle re-renders the screen, and the header sun/moon action (wired in
 * RandevuLayout) calls `toggleTheme()`. `setTheme()` backs the settings
 * segmented control.
 */
trait AppliesTheme
{
    public string $theme = AppTheme::DEFAULT;

    protected function applyTheme(): void
    {
        $this->theme = AppTheme::apply();
    }

    public function toggleTheme(): void
    {
        $this->theme = AppTheme::toggle();
    }

    public function setTheme(string $mode): void
    {
        $this->theme = AppTheme::persist($mode);
    }
}
