<native:column class="w-full h-full bg-theme-background">
    @php($rtl = \App\Services\AppLocale::isRtl())
    @if(count($memories) === 0 && trim($search) === '')
        <native:column class="w-full flex-1 items-center justify-center gap-3 p-8">
            <native:text class="text-5xl text-theme-on-surface-variant">🕰️</native:text>
            <native:text font="heading" class="text-lg text-theme-on-surface text-center leading-relaxed">{{ __('randevu.memories_empty_headline') }}</native:text>
            <native:text class="text-sm text-theme-on-surface-variant text-center leading-relaxed">{{ __('randevu.memories_empty_support') }}</native:text>
            <native:text class="text-sm text-theme-on-surface-variant text-center leading-relaxed">{{ __('randevu.memories_empty_classifier') }}</native:text>
            <native:button :label="__('randevu.empty_cta')" font="label" @navigate="'/create'" />
        </native:column>
    @else
        <native:column class="w-full px-5 pt-3 gap-2">
            <native:outlined-text-input :label="__('randevu.search_label')" :placeholder="__('randevu.search_placeholder')" native:model="search" ios-leading-icon="magnifyingglass" android-leading-icon="search" />
            @include('native.partials.segmented', ['name' => 'sort', 'selected' => $sort, 'rtl' => $rtl])
        </native:column>
        @if(count($memories) === 0)
            <native:column class="w-full flex-1 items-center justify-center gap-3 p-8">
                <native:text class="text-5xl text-theme-on-surface-variant">🔍</native:text>
                <native:text font="heading" class="text-lg text-theme-on-surface text-center leading-relaxed">{{ __('randevu.no_matches_headline') }}</native:text>
                <native:text class="text-sm text-theme-on-surface-variant text-center leading-relaxed">{{ __('randevu.no_matches_support') }}</native:text>
            </native:column>
        @else
            <native:scroll-view class="w-full flex-1">
                <native:column class="w-full px-5 py-3 gap-3">
                    @foreach($memories as $index => $item)
                        @include('native.partials.randevu-card', ['item' => $item, 'index' => $index, 'rtl' => $rtl, 'badge' => false])
                    @endforeach
                </native:column>
            </native:scroll-view>
        @endif
    @endif
</native:column>
