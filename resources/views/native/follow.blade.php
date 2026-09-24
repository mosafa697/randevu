<native:column class="w-full h-full p-4 gap-4 bg-theme-background">
    @if(count($appointments) === 0)
        <native:column class="w-full items-center gap-2 p-6">
            <native:button :label="__('randevu.empty_cta')" @navigate="'/create'" />
        </native:column>
    @endif

    @foreach($appointments as $item)
        <native:pressable @navigate="'/details/'.$item['id']" class="w-full p-3 rounded-2xl bg-theme-surface">
            <native:column class="w-full gap-1">
                <native:row class="w-full items-center gap-2">
                    <native:column class="rounded-full w-3 h-3 {{ !empty($item['color']) ? 'bg-[#'.ltrim($item['color'], '#').']' : 'bg-theme-outline' }}" />
                    <native:text class="flex-1 text-base font-semibold text-theme-on-surface">{{ $item['title'] }}</native:text>
                    @if($item['is_today'])
                        <native:badge :label="__('randevu.today')" variant="accent" />
                    @else
                        <native:text class="text-sm text-theme-on-surface-variant">{{ $item['phrase'] }}</native:text>
                    @endif
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
