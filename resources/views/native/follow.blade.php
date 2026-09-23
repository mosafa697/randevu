<native:column class="w-full h-full p-4 gap-4 bg-theme-background text-theme-on-background">
    <native:row class="w-full items-center justify-between">
        <native:text class="text-2xl font-bold">Randevu</native:text>
        <native:button label="+ New" @navigate="'/create'" />
    </native:row>

    @if(count($today) === 0 && count($upcoming) === 0 && count($memories) === 0)
        <native:column class="w-full items-center gap-2 p-6">
            <native:text class="text-lg font-semibold text-center">No randevus yet</native:text>
            <native:text class="text-sm text-center">Add your first appointment or memory to follow it from today.</native:text>
            <native:button label="Add your first randevu" @navigate="'/create'" />
        </native:column>
    @endif

    @if(count($today) > 0)
        <native:text class="text-lg font-bold">Today</native:text>
        @foreach($today as $item)
            <native:pressable @navigate="'/edit/'.$item['id']" class="w-full p-3 rounded-lg">
                <native:row class="w-full items-center justify-between">
                    <native:text class="text-base font-semibold">{{ $item['title'] }}</native:text>
                    <native:badge label="Today" />
                </native:row>
                <native:text class="text-sm">{{ $item['absolute'] }} · {{ $item['phrase'] }}</native:text>
                @if(!empty($item['note']))
                    <native:text class="text-sm">{{ $item['note'] }}</native:text>
                @endif
            </native:pressable>
        @endforeach
    @endif

    @if(count($upcoming) > 0)
        <native:text class="text-lg font-bold">Coming up</native:text>
        @foreach($upcoming as $item)
            <native:pressable @navigate="'/edit/'.$item['id']" class="w-full p-3 rounded-lg">
                <native:column class="w-full gap-1">
                    <native:row class="w-full items-center justify-between">
                        <native:text class="text-base font-semibold">{{ $item['title'] }}</native:text>
                        <native:text class="text-sm">{{ $item['phrase'] }}</native:text>
                    </native:row>
                    <native:text class="text-sm">{{ $item['absolute'] }} · {{ $item['days'] }} days</native:text>
                    @if(!empty($item['note']))
                        <native:text class="text-sm">{{ $item['note'] }}</native:text>
                    @endif
                </native:column>
            </native:pressable>
        @endforeach
    @endif

    @if(count($memories) > 0)
        <native:text class="text-lg font-bold">Memories</native:text>
        @foreach($memories as $item)
            <native:pressable @navigate="'/edit/'.$item['id']" class="w-full p-3 rounded-lg">
                <native:column class="w-full gap-1">
                    <native:row class="w-full items-center justify-between">
                        <native:text class="text-base font-semibold">{{ $item['title'] }}</native:text>
                        <native:text class="text-sm">{{ $item['phrase'] }}</native:text>
                    </native:row>
                    <native:text class="text-sm">{{ $item['absolute'] }} · {{ abs($item['days']) }} days ago</native:text>
                    @if(!empty($item['note']))
                        <native:text class="text-sm">{{ $item['note'] }}</native:text>
                    @endif
                </native:column>
            </native:pressable>
        @endforeach
    @endif
</native:column>
