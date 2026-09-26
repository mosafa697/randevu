<?php

namespace App\NativeComponents;

use App\NativeComponents\Concerns\AppliesLocale;
use App\NativeComponents\Concerns\AppliesTheme;
use App\Services\AppLocale;
use App\Services\AppTheme;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Settings extends NativeComponent
{
    use AppliesLocale;
    use AppliesTheme;

    public string $locale = 'ar';

    public function mount(): void
    {
        $this->applyLocale();
        $this->applyTheme();
        $this->locale = $this->currentLocale();
    }

    public function navTitle(): string
    {
        return __('randevu.settings_title');
    }

    public function useArabic(): void
    {
        $this->locale = AppLocale::persist('ar');
    }

    public function useEnglish(): void
    {
        $this->locale = AppLocale::persist('en');
    }

    public function useLight(): void
    {
        $this->theme = AppTheme::persist('light');
    }

    public function useDark(): void
    {
        $this->theme = AppTheme::persist('dark');
    }

    public function render(): View
    {
        return view('native.settings');
    }
}
