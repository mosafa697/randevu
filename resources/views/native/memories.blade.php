<native:column class="w-full h-full p-4 gap-4 bg-theme-background">
    @if(count($memories) === 0)
        <native:column class="w-full items-center gap-2 p-6">
            <native:button :label="__('randevu.empty_cta')" @navigate="'/create'" />
        </native:column>
    @endif

    @foreach($memories as $item)
        <native:pressable @navigate="'/details/'.$item['id']" class="w-full p-3 rounded-2xl bg-theme-surface">
            <native:column class="w-full gap-1">
                <native:row class="w-full items-center justify-between">
                    <native:text class="text-base font-semibold text-theme-on-surface">{{ $item['title'] }}</native:text>
                    <native:text class="text-sm text-theme-on-surface-variant">{{ $item['phrase'] }}</native:text>
                </native:row>
                <native:text class="text-sm text-theme-on-surface-variant">{{ $item['absolute'] }} · {{ $item['exact'] }}</native:text>
                @if(!empty($item['hijri']))
                    <native:text class="text-sm text-theme-on-surface-variant">{{ $item['hijri'] }} هـ</native:text>
                @endif
                @if(!empty($item['note']))
                    <native:text class="text-sm text-theme-on-surface-variant">{{ $item['note'] }}</native:text>
                @endif
            </native:column>
        </native:pressable>
    @endforeach
</native:column>
