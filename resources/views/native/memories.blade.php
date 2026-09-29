<native:column class="w-full h-full bg-theme-background">
    @php($rtl = \App\Services\AppLocale::isRtl())
    @if(count($memories) === 0 && trim($search) === '')
        <native:column class="w-full flex-1 items-center justify-center gap-3 p-8">
            <native:text class="text-5xl text-theme-on-surface">🕰️</native:text>
            <native:text font="heading" class="text-lg font-bold text-theme-on-surface text-center">{{ __('randevu.memories_empty_headline') }}</native:text>
            <native:text class="text-sm text-theme-on-surface-variant text-center">{{ __('randevu.memories_empty_support') }}</native:text>
            <native:text class="text-sm text-theme-on-surface-variant text-center">{{ __('randevu.memories_empty_classifier') }}</native:text>
            <native:button :label="__('randevu.empty_cta')" @navigate="'/create'" />
        </native:column>
    @else
        <native:column class="w-full p-4 gap-2">
            <native:outlined-text-input :label="__('randevu.search_label')" :placeholder="__('randevu.search_placeholder')" native:model="search" class="rounded-[14px]" />
            <native:row class="w-full gap-2">
                @if($rtl)
                    <native:button :label="__('randevu.sort_alpha')" :variant="$sort === 'alpha' ? 'primary' : 'ghost'" @press="sortByAlpha" />
                    <native:button :label="__('randevu.sort_newest')" :variant="$sort === 'newest' ? 'primary' : 'ghost'" @press="sortByNewest" />
                    <native:button :label="__('randevu.sort_nearest')" :variant="$sort === 'nearest' ? 'primary' : 'ghost'" @press="sortByNearest" />
                @else
                    <native:button :label="__('randevu.sort_nearest')" :variant="$sort === 'nearest' ? 'primary' : 'ghost'" @press="sortByNearest" />
                    <native:button :label="__('randevu.sort_newest')" :variant="$sort === 'newest' ? 'primary' : 'ghost'" @press="sortByNewest" />
                    <native:button :label="__('randevu.sort_alpha')" :variant="$sort === 'alpha' ? 'primary' : 'ghost'" @press="sortByAlpha" />
                @endif
            </native:row>
        </native:column>
        @if(count($memories) === 0)
            <native:column class="w-full flex-1 items-center justify-center gap-3 p-8">
                <native:text class="text-5xl text-theme-on-surface">🔍</native:text>
                <native:text font="heading" class="text-lg font-bold text-theme-on-surface text-center">{{ __('randevu.no_matches_headline') }}</native:text>
                <native:text class="text-sm text-theme-on-surface-variant text-center">{{ __('randevu.no_matches_support') }}</native:text>
                <native:button :label="__('randevu.search_clear')" @press="clearSearch" />
            </native:column>
        @else
            <native:scroll-view class="w-full flex-1">
                <native:column class="w-full p-4 gap-4">
                    @foreach($memories as $index => $item)
                        <native:pressable @navigate="'/details/'.$item['id']" class="w-full p-4 gap-3 rounded-2xl bg-theme-surface border border-theme-outline-variant">
                            @if(!empty($item['cover']))
                                <native:column class="w-full p-2">
                                    <native:image :src="$item['cover']" class="w-full rounded-xl" :height="160" :fit="2" />
                                </native:column>
                            @endif
                            <native:row class="w-full items-center gap-3">
                                @if($rtl)
                                    @include('native.partials.card-text', ['item' => $item, 'rtl' => $rtl, 'badge' => false])
                                    @include('native.components.ring', [
                                        'pct' => $item['pct'],
                                        'days' => $item['days'],
                                        'entered_in' => $item['entered_in'],
                                        'index' => $index,
                                    ])
                                @else
                                    @include('native.components.ring', [
                                        'pct' => $item['pct'],
                                        'days' => $item['days'],
                                        'entered_in' => $item['entered_in'],
                                        'index' => $index,
                                    ])
                                    @include('native.partials.card-text', ['item' => $item, 'rtl' => $rtl, 'badge' => false])
                                @endif
                            </native:row>
                        </native:pressable>
                    @endforeach
                </native:column>
            </native:scroll-view>
        @endif
    @endif
</native:column>
