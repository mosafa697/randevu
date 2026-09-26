<native:column class="w-full h-full p-4 gap-4 bg-theme-background">
    <native:column class="w-full gap-4 p-4 bg-theme-surface rounded-2xl border border-theme-outline-variant">
        <native:column class="w-full gap-2">
            <native:text font="heading" class="text-sm font-semibold text-theme-on-surface">{{ __('randevu.language_label') }}</native:text>
            <native:row class="w-full gap-2">
                <native:button :label="__('randevu.lang_arabic')" :variant="$locale === 'ar' ? 'primary' : 'ghost'" @press="useArabic" />
                <native:button :label="__('randevu.lang_english')" :variant="$locale === 'en' ? 'primary' : 'ghost'" @press="useEnglish" />
            </native:row>
        </native:column>

        <native:divider />

        <native:column class="w-full gap-2">
            <native:text font="heading" class="text-sm font-semibold text-theme-on-surface">{{ __('randevu.theme_label') }}</native:text>
            <native:row class="w-full gap-2">
                <native:button :label="__('randevu.theme_light')" :variant="$theme === 'light' ? 'primary' : 'ghost'" @press="useLight" />
                <native:button :label="__('randevu.theme_dark')" :variant="$theme === 'dark' ? 'primary' : 'ghost'" @press="useDark" />
            </native:row>
        </native:column>
    </native:column>
</native:column>
