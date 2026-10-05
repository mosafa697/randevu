<native:column class="w-full h-full bg-theme-background">
    <native:scroll-view class="w-full h-full">
        <native:column class="w-full px-5 py-3 gap-6">
            @if(!$hasData)
                <native:column class="w-full flex-1 items-center justify-center gap-3 p-8">
                    <native:text class="text-5xl text-theme-on-surface-variant">📊</native:text>
                    <native:text font="heading" class="text-lg text-theme-on-surface text-center leading-relaxed">{{ __('randevu.dashboard_title') }}</native:text>
                    <native:text class="text-sm text-theme-on-surface-variant text-center leading-relaxed">{{ __('randevu.dash_empty_support') }}</native:text>
                </native:column>
            @else
                <native:column class="w-full p-6 gap-1 rounded-2xl bg-theme-primary/10 items-center">
                    <native:text font="heading" class="text-2xl text-theme-on-surface">{{ $stats['total'] }}</native:text>
                    <native:text class="text-sm text-theme-on-surface-variant">{{ __('randevu.dash_total') }}</native:text>
                </native:column>

                <native:row class="w-full gap-3">
                    <native:column class="flex-1 p-4 gap-1 rounded-2xl bg-theme-secondary items-center">
                        <native:text font="heading" class="text-xl text-theme-on-surface">{{ $stats['upcoming'] }}</native:text>
                        <native:text class="text-sm text-theme-primary-on-soft text-center leading-relaxed">{{ __('randevu.dash_upcoming') }}</native:text>
                    </native:column>
                    <native:column class="flex-1 p-4 gap-1 rounded-2xl bg-theme-surface border border-theme-outline-variant items-center">
                        <native:text font="heading" class="text-xl text-theme-on-surface">{{ $stats['memories'] }}</native:text>
                        <native:text class="text-sm text-theme-on-surface-variant text-center leading-relaxed">{{ __('randevu.dash_memories') }}</native:text>
                    </native:column>
                </native:row>

                <native:row class="w-full gap-3">
                    <native:column class="flex-1 p-4 gap-1 rounded-2xl bg-theme-accent-soft items-center">
                        <native:text font="heading" class="text-xl text-theme-on-surface">{{ $stats['today'] }}</native:text>
                        <native:text class="text-sm text-theme-accent-text text-center leading-relaxed">{{ __('randevu.dash_today') }}</native:text>
                    </native:column>
                    <native:column class="flex-1 p-4 gap-1 rounded-2xl bg-theme-surface border border-theme-outline-variant items-center">
                        <native:text font="heading" class="text-xl text-theme-on-surface">{{ $stats['this_month'] }}</native:text>
                        <native:text class="text-sm text-theme-on-surface-variant text-center leading-relaxed">{{ __('randevu.dash_this_month') }}</native:text>
                    </native:column>
                </native:row>

                <native:divider />

                <native:column class="w-full p-4 gap-3 rounded-2xl bg-theme-surface border border-theme-outline-variant">
                    <native:text font="heading" class="text-lg text-theme-on-surface leading-relaxed">{{ __('randevu.dash_split_headline') }}</native:text>
                    <native:text class="text-sm text-theme-on-surface-variant leading-relaxed">{{ __('randevu.dash_split_support') }}</native:text>
                    <native:row class="w-full items-center justify-center gap-6">
                        <native:webview class="w-28 h-28" :html="$this->splitDonutHtml()" />
                        <native:column class="gap-2">
                            <native:row class="gap-2 items-center">
                                <native:column class="w-3 h-3 rounded-sm bg-theme-primary" />
                                <native:text class="text-base text-theme-on-surface">{{ __('randevu.dash_gregorian') }} · {{ $gregorianCount }}</native:text>
                            </native:row>
                            <native:row class="gap-2 items-center">
                                <native:column class="w-3 h-3 rounded-sm bg-theme-accent" />
                                <native:text class="text-base text-theme-on-surface">{{ __('randevu.dash_hijri') }} · {{ $hijriCount }}</native:text>
                            </native:row>
                        </native:column>
                    </native:row>
                </native:column>
            @endif
        </native:column>
    </native:scroll-view>
</native:column>
