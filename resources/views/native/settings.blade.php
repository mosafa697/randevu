<native:column class="w-full h-full p-4 gap-4 bg-theme-background">
    <native:text font="heading" class="text-lg font-bold text-theme-on-background">{{ __('randevu.language_label') }}</native:text>

    <native:text class="text-sm text-theme-on-surface-variant">{{ $locale === 'ar' ? __('randevu.current_arabic') : __('randevu.current_english') }}</native:text>

    <native:row class="w-full gap-2">
        <native:button :label="__('randevu.lang_arabic')" @press="useArabic" />
        <native:button :label="__('randevu.lang_english')" @press="useEnglish" />
    </native:row>
</native:column>
