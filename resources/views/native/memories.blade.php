<native:column class="w-full h-full p-4 gap-4 bg-theme-background">
    @if(count($memories) === 0)
        <native:column class="w-full flex-1 items-center justify-center gap-3 p-8">
            <native:text class="text-5xl text-theme-on-surface">🕰️</native:text>
            <native:text font="heading" class="text-lg font-bold text-theme-on-surface text-center">{{ __('randevu.memories_empty_headline') }}</native:text>
            <native:text class="text-sm text-theme-on-surface-variant text-center">{{ __('randevu.memories_empty_support') }}</native:text>
            <native:button :label="__('randevu.empty_cta')" @navigate="'/create'" />
        </native:column>
    @endif

    @foreach($memories as $index => $item)
        <native:pressable @navigate="'/details/'.$item['id']" class="w-full p-4 rounded-[20px] bg-theme-surface border border-theme-outline-variant">
            <native:row class="w-full items-center gap-3">
                @include('native.components.ring', [
                    'pct' => $item['pct'],
                    'days' => $item['days'],
                    'entered_in' => $item['entered_in'],
                    'index' => $index,
                ])
                <native:column class="flex-1 gap-1">
                    <native:text font="heading" class="w-full text-base font-semibold text-theme-on-surface">{{ $item['title'] }}</native:text>
                    <native:text class="text-sm text-theme-on-surface-variant">
                        {{ $item['absolute'] }}@if(!empty($item['hijri'])) · {{ $item['hijri'] }} هـ@endif
                    </native:text>
                    @if(!empty($item['note']))
                        <native:text class="text-sm text-theme-on-surface-variant">{{ $item['note'] }}</native:text>
                    @endif
                    <native:text class="text-xs px-2 py-1 rounded-full {{ ($item['entered_in'] ?? 'gregorian') === 'hijri' ? 'bg-theme-accent-soft text-theme-on-accent' : 'bg-theme-primary-soft text-theme-on-primary' }}">
                        {{ $item['phrase'] }}
                    </native:text>
                </native:column>
            </native:row>
        </native:pressable>
    @endforeach
</native:column>
