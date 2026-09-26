<native:column class="w-full h-full bg-theme-background">
    <native:scroll-view class="w-full h-full">
        <native:column class="w-full p-4 gap-4">
            @if(!$hasData)
                <native:column class="w-full flex-1 items-center justify-center p-8">
                    <native:text class="text-sm text-theme-on-surface-variant text-center">{{ __('randevu.dash_empty_support') }}</native:text>
                </native:column>
            @else
                <native:column class="w-full p-4 rounded-[20px] bg-theme-surface border border-theme-outline-variant items-center gap-1">
                    <native:text font="heading" class="text-4xl font-bold text-theme-on-surface">{{ $stats['total'] }}</native:text>
                    <native:text class="text-sm text-theme-on-surface-variant">{{ __('randevu.dash_total') }}</native:text>
                </native:column>

                <native:row class="w-full gap-4">
                    <native:column class="flex-1 p-4 rounded-[20px] bg-theme-surface border border-theme-outline-variant items-center gap-1">
                        <native:text font="heading" class="text-2xl font-bold text-theme-on-surface">{{ $stats['upcoming'] }}</native:text>
                        <native:text class="text-sm text-theme-on-surface-variant text-center">{{ __('randevu.dash_upcoming') }}</native:text>
                    </native:column>
                    <native:column class="flex-1 p-4 rounded-[20px] bg-theme-surface border border-theme-outline-variant items-center gap-1">
                        <native:text font="heading" class="text-2xl font-bold text-theme-on-surface">{{ $stats['memories'] }}</native:text>
                        <native:text class="text-sm text-theme-on-surface-variant text-center">{{ __('randevu.dash_memories') }}</native:text>
                    </native:column>
                </native:row>

                <native:row class="w-full gap-4">
                    <native:column class="flex-1 p-4 rounded-[20px] bg-theme-surface border border-theme-outline-variant items-center gap-1">
                        <native:text font="heading" class="text-2xl font-bold text-theme-on-surface">{{ $stats['today'] }}</native:text>
                        <native:text class="text-sm text-theme-on-surface-variant text-center">{{ __('randevu.dash_today') }}</native:text>
                    </native:column>
                    <native:column class="flex-1 p-4 rounded-[20px] bg-theme-surface border border-theme-outline-variant items-center gap-1">
                        <native:text font="heading" class="text-2xl font-bold text-theme-on-surface">{{ $stats['this_month'] }}</native:text>
                        <native:text class="text-sm text-theme-on-surface-variant text-center">{{ __('randevu.dash_this_month') }}</native:text>
                    </native:column>
                </native:row>

                <native:divider class="w-full" />

                <native:column class="w-full p-4 rounded-[20px] bg-theme-surface border border-theme-outline-variant gap-1">
                    <native:text font="heading" class="text-base font-bold text-theme-on-surface">{{ __('randevu.dash_split_headline') }}</native:text>
                    <native:text class="text-sm text-theme-on-surface-variant">{{ __('randevu.dash_split_support') }}</native:text>
                    @php
                        $splitTotal = $gregorianCount + $hijriCount;
                        $r = 44;
                        $c = 2 * M_PI * $r;
                        $gregLen = $splitTotal > 0 ? $c * ($gregorianCount / $splitTotal) : 0;
                        $donutHtml = '<!DOCTYPE html><html><head><meta name="viewport" content="width=320,initial-scale=1"><style>html,body{margin:0;padding:0;background:transparent;overflow:hidden}svg{display:block}</style></head><body>'
                            . '<svg width="320" height="150" viewBox="0 0 320 150">'
                            . '<circle cx="70" cy="75" r="' . $r . '" fill="none" stroke="' . theme('progress-track') . '" stroke-width="16"/>'
                            . '<circle cx="70" cy="75" r="' . $r . '" fill="none" stroke="' . theme('primary') . '" stroke-width="16" stroke-dasharray="' . $gregLen . ' ' . $c . '" transform="rotate(-90 70 75)"/>'
                            . '<circle cx="70" cy="75" r="' . $r . '" fill="none" stroke="' . theme('accent') . '" stroke-width="16" stroke-dasharray="' . ($c - $gregLen) . ' ' . $c . '" stroke-dashoffset="' . (-$gregLen) . '" transform="rotate(-90 70 75)"/>'
                            . '<text x="70" y="76" text-anchor="middle" dominant-baseline="central" font-size="22" font-weight="700" fill="' . theme('on-surface') . '" font-family="system-ui,sans-serif">' . $splitTotal . '</text>'
                            . '<rect x="140" y="48" width="14" height="14" rx="4" fill="' . theme('primary') . '"/>'
                            . '<text x="160" y="60" font-size="12" fill="' . theme('on-surface') . '" font-family="system-ui,sans-serif">' . e(__('randevu.dash_gregorian')) . ' · ' . $gregorianCount . '</text>'
                            . '<rect x="140" y="80" width="14" height="14" rx="4" fill="' . theme('accent') . '"/>'
                            . '<text x="160" y="92" font-size="12" fill="' . theme('on-surface') . '" font-family="system-ui,sans-serif">' . e(__('randevu.dash_hijri')) . ' · ' . $hijriCount . '</text>'
                            . '</svg></body></html>';
                    @endphp
                    <native:webview class="w-full h-[160px]" :html="$donutHtml" />
                </native:column>
            @endif
        </native:column>
    </native:scroll-view>
</native:column>
