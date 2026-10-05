<native:column class="w-full h-full bg-theme-background">
    @php($rtl = \App\Services\AppLocale::isRtl())
    <native:scroll-view class="w-full h-full">
        <native:column class="w-full px-5 py-3 gap-6">
            <native:column class="w-full gap-4 p-4 bg-theme-surface rounded-2xl border border-theme-outline-variant">
                <native:column class="w-full gap-2">
                    <native:text font="heading" class="text-lg text-theme-on-surface leading-relaxed">{{ __('randevu.language_label') }}</native:text>
                    <native:text class="text-sm text-theme-on-surface-variant leading-relaxed">{{ __('randevu.settings_language_helper') }}</native:text>
                    @include('native.partials.segmented', ['name' => 'language', 'selected' => $locale, 'rtl' => $rtl])
                </native:column>

                <native:divider />

                <native:column class="w-full gap-2">
                    <native:text font="heading" class="text-lg text-theme-on-surface leading-relaxed">{{ __('randevu.theme_label') }}</native:text>
                    <native:text class="text-sm text-theme-on-surface-variant leading-relaxed">{{ __('randevu.settings_theme_helper') }}</native:text>
                    @include('native.partials.segmented', ['name' => 'theme', 'selected' => $theme, 'rtl' => $rtl])
                </native:column>
            </native:column>

            <native:text class="text-sm text-theme-on-surface-variant text-center">{{ __('randevu.app_version', ['version' => $this->appVersion()]) }}</native:text>
        </native:column>
    </native:scroll-view>
</native:column>
