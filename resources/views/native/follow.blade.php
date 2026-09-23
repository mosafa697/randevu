<native:column class="w-full h-full p-4 gap-4 bg-theme-background">
    @if(count($today) === 0 && count($upcoming) === 0 && count($memories) === 0)
        <native:column class="w-full items-center gap-2 p-6">
            <native:text class="text-lg font-semibold text-center text-theme-on-background">No randevus yet</native:text>
            <native:text class="text-sm text-center text-theme-on-surface-variant">Add your first appointment or memory to follow it from today.</native:text>
            <native:button label="Add your first randevu" @navigate="'/create'" />
        </native:column>
    @endif

    @if(count($today) > 0)
        <native:text class="text-lg font-bold text-theme-on-background">Today</native:text>
        @foreach($today as $item)
            <native:pressable @navigate="'/edit/'.$item['id']" class="w-full p-3 rounded-lg bg-theme-surface">
                <native:row class="w-full items-center justify-between">
                    <native:text class="text-base font-semibold text-theme-on-surface">{{ $item['title'] }}</native:text>
                    <native:badge label="Today" />
                </native:row>
                <native:text class="text-sm text-theme-on-surface-variant">{{ $item['absolute'] }} · {{ $item['phrase'] }}</native:text>
                @if(!empty($item['hijri']))
                    <native:text class="text-sm text-theme-on-surface-variant">{{ $item['hijri'] }} هـ</native:text>
                @endif
                @if(!empty($item['note']))
                    <native:text class="text-sm text-theme-on-surface-variant">{{ $item['note'] }}</native:text>
                @endif
            </native:pressable>
        @endforeach
    @endif

    @if(count($upcoming) > 0)
        <native:text class="text-lg font-bold text-theme-on-background">Coming up</native:text>
        @foreach($upcoming as $item)
            <native:pressable @navigate="'/edit/'.$item['id']" class="w-full p-3 rounded-lg bg-theme-surface">
                <native:column class="w-full gap-1">
                    <native:row class="w-full items-center justify-between">
                        <native:text class="text-base font-semibold text-theme-on-surface">{{ $item['title'] }}</native:text>
                        <native:text class="text-sm text-theme-on-surface-variant">{{ $item['phrase'] }}</native:text>
                    </native:row>
                    <native:text class="text-sm text-theme-on-surface-variant">{{ $item['absolute'] }} · {{ $item['days'] }} days</native:text>
                    @if(!empty($item['hijri']))
                        <native:text class="text-sm text-theme-on-surface-variant">{{ $item['hijri'] }} هـ</native:text>
                    @endif
                    @if(!empty($item['note']))
                        <native:text class="text-sm text-theme-on-surface-variant">{{ $item['note'] }}</native:text>
                    @endif
                </native:column>
            </native:pressable>
        @endforeach
    @endif

    @if(count($memories) > 0)
        <native:text class="text-lg font-bold text-theme-on-background">Memories</native:text>
        @foreach($memories as $item)
            <native:pressable @navigate="'/edit/'.$item['id']" class="w-full p-3 rounded-lg bg-theme-surface">
                <native:column class="w-full gap-1">
                    <native:row class="w-full items-center justify-between">
                        <native:text class="text-base font-semibold text-theme-on-surface">{{ $item['title'] }}</native:text>
                        <native:text class="text-sm text-theme-on-surface-variant">{{ $item['phrase'] }}</native:text>
                    </native:row>
                    <native:text class="text-sm text-theme-on-surface-variant">{{ $item['absolute'] }} · {{ abs($item['days']) }} days ago</native:text>
                    @if(!empty($item['hijri']))
                        <native:text class="text-sm text-theme-on-surface-variant">{{ $item['hijri'] }} هـ</native:text>
                    @endif
                    @if(!empty($item['note']))
                        <native:text class="text-sm text-theme-on-surface-variant">{{ $item['note'] }}</native:text>
                    @endif
                </native:column>
            </native:pressable>
        @endforeach
    @endif
</native:column>
