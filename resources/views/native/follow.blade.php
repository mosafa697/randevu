<native:column class="w-full h-full p-4 gap-4 bg-theme-background">
    @php($rtl = \App\Services\AppLocale::isRtl())
    @if(count($appointments) === 0)
        <native:column class="w-full flex-1 items-center justify-center gap-3 p-8">
            <native:text class="text-5xl text-theme-on-surface">📅</native:text>
            <native:text font="heading" class="text-lg font-bold text-theme-on-surface text-center">{{ __('randevu.follow_empty_headline') }}</native:text>
            <native:text class="text-sm text-theme-on-surface-variant text-center">{{ __('randevu.follow_empty_support') }}</native:text>
            <native:button :label="__('randevu.empty_cta')" @navigate="'/create'" />
        </native:column>
    @endif

    @foreach($appointments as $index => $item)
        <native:pressable @navigate="'/details/'.$item['id']" class="w-full p-4 rounded-[20px] bg-theme-surface border border-theme-outline-variant">
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
