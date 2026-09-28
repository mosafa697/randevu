<native:column class="w-full h-full bg-theme-background">
    @php($rtl = \App\Services\AppLocale::isRtl())
    @if(count($appointments) === 0)
        <native:column class="w-full flex-1 items-center justify-center gap-3 p-8">
            <native:text class="text-5xl text-theme-on-surface">📅</native:text>
            <native:text font="heading" class="text-lg font-bold text-theme-on-surface text-center">{{ __('randevu.follow_empty_headline') }}</native:text>
            <native:text class="text-sm text-theme-on-surface-variant text-center">{{ __('randevu.follow_empty_support') }}</native:text>
            <native:button :label="__('randevu.empty_cta')" @navigate="'/create'" />
        </native:column>
    @else
        <native:scroll-view class="w-full h-full">
            <native:column class="w-full p-4 gap-4">
                @foreach($appointments as $index => $item)
                    <native:pressable @navigate="'/details/'.$item['id']" class="w-full p-4 gap-3 rounded-[20px] bg-theme-surface border border-theme-outline-variant">
                        @if(!empty($item['cover']))
                            <native:column class="w-full p-2">
                                <native:image :src="$item['cover']" class="w-full rounded-xl" :height="160" :fit="2" />
                            </native:column>
                        @endif
                        <native:row class="w-full items-center gap-3">
                            @if($rtl)
                                @include('native.partials.card-text', ['item' => $item, 'rtl' => $rtl, 'badge' => true])
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
                                @include('native.partials.card-text', ['item' => $item, 'rtl' => $rtl, 'badge' => true])
                            @endif
                        </native:row>
                    </native:pressable>
                @endforeach
            </native:column>
        </native:scroll-view>
    @endif
</native:column>
