<native:column class="w-full h-full bg-theme-background">
    @php($rtl = \App\Services\AppLocale::isRtl())
    @if(count($appointments) === 0 && trim($search) === '')
        <native:column class="w-full flex-1 items-center justify-center gap-3 p-8">
            <native:text class="text-5xl text-theme-on-surface-variant">📅</native:text>
            <native:text font="heading" class="text-lg text-theme-on-surface text-center leading-relaxed">{{ __('randevu.follow_empty_headline') }}</native:text>
            <native:text class="text-sm text-theme-on-surface-variant text-center leading-relaxed">{{ __('randevu.follow_empty_support') }}</native:text>
            <native:text class="text-sm text-theme-on-surface-variant text-center leading-relaxed">{{ __('randevu.follow_empty_classifier') }}</native:text>
            <native:text class="text-sm text-theme-on-surface-variant text-center leading-relaxed">{{ __('randevu.follow_empty_hint') }}</native:text>
            <native:button :label="__('randevu.empty_cta')" font="label" @navigate="'/create'" class="w-full min-h-12" />
        </native:column>
    @else
        <native:column class="w-full px-5 pt-3 gap-2">
            <native:outlined-text-input :label="__('randevu.search_label')" :placeholder="__('randevu.search_placeholder')" native:model="search" ios-leading-icon="magnifyingglass" android-leading-icon="search" />
            @include('native.partials.segmented', ['name' => 'sort', 'selected' => $sort, 'rtl' => $rtl])
        </native:column>
        @if(count($appointments) === 0)
            <native:column class="w-full flex-1 items-center justify-center gap-3 p-8">
                <native:text class="text-5xl text-theme-on-surface-variant">🔍</native:text>
                <native:text font="heading" class="text-lg text-theme-on-surface text-center leading-relaxed">{{ __('randevu.no_matches_headline') }}</native:text>
                <native:text class="text-sm text-theme-on-surface-variant text-center leading-relaxed">{{ __('randevu.no_matches_support') }}</native:text>
            </native:column>
        @else
            <native:scroll-view class="w-full flex-1">
                <native:column class="w-full px-5 py-3 gap-6">
                    @if(collect($appointments)->contains(fn ($i) => (bool) ($i['is_today'] ?? false)))
                        <native:column class="w-full gap-3">
                            <native:text font="label" class="text-sm text-theme-on-surface-variant">{{ __('randevu.today') }}</native:text>
                            @foreach($appointments as $index => $item)
                                @if((bool) ($item['is_today'] ?? false))
                                    @include('native.partials.randevu-card', ['item' => $item, 'index' => $index, 'rtl' => $rtl, 'badge' => true])
                                @endif
                            @endforeach
                        </native:column>
                    @endif
                    @if(collect($appointments)->contains(fn ($i) => !(bool) ($i['is_today'] ?? false)))
                        <native:column class="w-full gap-3">
                            <native:text font="label" class="text-sm text-theme-on-surface-variant">{{ __('randevu.upcoming') }}</native:text>
                            @foreach($appointments as $index => $item)
                                @if(!(bool) ($item['is_today'] ?? false))
                                    @include('native.partials.randevu-card', ['item' => $item, 'index' => $index, 'rtl' => $rtl, 'badge' => true])
                                @endif
                            @endforeach
                        </native:column>
                    @endif
                </native:column>
            </native:scroll-view>
        @endif
    @endif
</native:column>
