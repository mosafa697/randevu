<native:row class="w-full gap-1 p-1 rounded-full bg-theme-surface-variant">
    @if(($name ?? '') === 'language')
        @if(($rtl ?? false))
            <native:button :label="__('randevu.lang_english')" :variant="($selected ?? '') === 'en' ? 'primary' : 'ghost'" @press="useEnglish" class="flex-1 min-h-12 rounded-full" />
            <native:button :label="__('randevu.lang_arabic')" :variant="($selected ?? '') === 'ar' ? 'primary' : 'ghost'" @press="useArabic" class="flex-1 min-h-12 rounded-full" />
        @else
            <native:button :label="__('randevu.lang_arabic')" :variant="($selected ?? '') === 'ar' ? 'primary' : 'ghost'" @press="useArabic" class="flex-1 min-h-12 rounded-full" />
            <native:button :label="__('randevu.lang_english')" :variant="($selected ?? '') === 'en' ? 'primary' : 'ghost'" @press="useEnglish" class="flex-1 min-h-12 rounded-full" />
        @endif
    @elseif(($name ?? '') === 'theme')
        @if(($rtl ?? false))
            <native:button :label="__('randevu.theme_dark')" :variant="($selected ?? '') === 'dark' ? 'primary' : 'ghost'" @press="useDark" class="flex-1 min-h-12 rounded-full" />
            <native:button :label="__('randevu.theme_light')" :variant="($selected ?? '') === 'light' ? 'primary' : 'ghost'" @press="useLight" class="flex-1 min-h-12 rounded-full" />
        @else
            <native:button :label="__('randevu.theme_light')" :variant="($selected ?? '') === 'light' ? 'primary' : 'ghost'" @press="useLight" class="flex-1 min-h-12 rounded-full" />
            <native:button :label="__('randevu.theme_dark')" :variant="($selected ?? '') === 'dark' ? 'primary' : 'ghost'" @press="useDark" class="flex-1 min-h-12 rounded-full" />
        @endif
    @elseif(($name ?? '') === 'calendar')
        @if(($rtl ?? false))
            <native:button :label="__('randevu.mode_hijri')" :variant="($selected ?? '') === 'hijri' ? 'primary' : 'ghost'" @press="useHijri" class="flex-1 min-h-12 rounded-full" />
            <native:button :label="__('randevu.mode_gregorian')" :variant="($selected ?? '') === 'gregorian' ? 'primary' : 'ghost'" @press="useGregorian" class="flex-1 min-h-12 rounded-full" />
        @else
            <native:button :label="__('randevu.mode_gregorian')" :variant="($selected ?? '') === 'gregorian' ? 'primary' : 'ghost'" @press="useGregorian" class="flex-1 min-h-12 rounded-full" />
            <native:button :label="__('randevu.mode_hijri')" :variant="($selected ?? '') === 'hijri' ? 'primary' : 'ghost'" @press="useHijri" class="flex-1 min-h-12 rounded-full" />
        @endif
    @elseif(($name ?? '') === 'sort')
        @if(($rtl ?? false))
            <native:button :label="__('randevu.sort_alpha')" :variant="($selected ?? '') === 'alpha' ? 'primary' : 'ghost'" @press="sortByAlpha" class="flex-1 min-h-12 rounded-full" />
            <native:button :label="__('randevu.sort_newest')" :variant="($selected ?? '') === 'newest' ? 'primary' : 'ghost'" @press="sortByNewest" class="flex-1 min-h-12 rounded-full" />
            <native:button :label="__('randevu.sort_nearest')" :variant="($selected ?? '') === 'nearest' ? 'primary' : 'ghost'" @press="sortByNearest" class="flex-1 min-h-12 rounded-full" />
        @else
            <native:button :label="__('randevu.sort_nearest')" :variant="($selected ?? '') === 'nearest' ? 'primary' : 'ghost'" @press="sortByNearest" class="flex-1 min-h-12 rounded-full" />
            <native:button :label="__('randevu.sort_newest')" :variant="($selected ?? '') === 'newest' ? 'primary' : 'ghost'" @press="sortByNewest" class="flex-1 min-h-12 rounded-full" />
            <native:button :label="__('randevu.sort_alpha')" :variant="($selected ?? '') === 'alpha' ? 'primary' : 'ghost'" @press="sortByAlpha" class="flex-1 min-h-12 rounded-full" />
        @endif
    @endif
</native:row>
