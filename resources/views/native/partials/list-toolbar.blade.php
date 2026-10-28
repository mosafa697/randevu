<native:column class="w-full px-5 pt-3 gap-2">
    <native:row class="w-full justify-end gap-2">
        <native:pressable @press="toggleSearch" :a11y-label="$searchExpanded ? __('randevu.search_collapse') : __('randevu.search_expand')" class="w-11 h-11 items-center justify-center rounded-full bg-theme-surface">
            <native:icon ios="magnifyingglass" android="search" size="20" class="text-theme-on-surface" />
        </native:pressable>
        <native:pressable :menu="$sortMenu" :a11y-label="__('randevu.sort_label')" class="w-11 h-11 items-center justify-center rounded-full bg-theme-surface">
            <native:icon ios="arrow.up.arrow.down" android="sort" size="20" class="text-theme-on-surface" />
        </native:pressable>
    </native:row>
    @if($searchExpanded)
        <native:row class="w-full items-center gap-2">
            <native:outlined-text-input :label="__('randevu.search_label')" :placeholder="__('randevu.search_placeholder')" native:model="search" ios-leading-icon="magnifyingglass" android-leading-icon="search" class="flex-1" />
            @if(trim($search) !== '')
                <native:pressable @press="clearSearch" :a11y-label="__('randevu.search_clear')" class="w-11 h-11 items-center justify-center rounded-full bg-theme-surface">
                    <native:icon ios="xmark" android="close" size="20" class="text-theme-on-surface" />
                </native:pressable>
            @endif
        </native:row>
    @endif
</native:column>
