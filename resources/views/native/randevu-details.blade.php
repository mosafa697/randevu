<native:column class="w-full h-full p-4 gap-4 bg-theme-background">
    <native:row class="w-full">
        <native:button :label="__('randevu.back')" @navigate.back />
    </native:row>

    @if($randevu === null)
        <native:text class="text-base text-theme-on-surface-variant">{{ __('randevu.details_missing') }}</native:text>
    @else
        <native:column class="w-full p-4 gap-2 rounded-2xl bg-theme-surface">
            <native:row class="w-full items-center justify-between">
                <native:text font="heading" class="text-xl font-bold text-theme-on-surface">{{ $randevu['title'] }}</native:text>
                @if($randevu['is_today'])
                    <native:badge :label="__('randevu.today')" variant="accent" />
                @endif
            </native:row>
            <native:text class="text-base font-semibold text-theme-on-surface">{{ $randevu['phrase'] }}</native:text>
            <native:text class="text-sm text-theme-on-surface-variant">{{ __('randevu.exact_label') }}: {{ $randevu['exact'] }}</native:text>
            <native:divider class="w-full" />
            <native:text class="text-sm text-theme-on-surface">{{ __('randevu.gregorian_label') }}: {{ $randevu['absolute'] }}</native:text>
            @if(!empty($randevu['hijri']))
                <native:text class="text-sm text-theme-on-surface">{{ __('randevu.hijri_label') }}: {{ $randevu['hijri'] }} هـ</native:text>
            @endif
            <native:text class="text-sm text-theme-on-surface-variant">
                {{ $randevu['is_appointment'] ? __('randevu.kind_appointment') : __('randevu.kind_memory') }}
            </native:text>
            @if(!empty($randevu['note']))
                <native:text class="text-sm text-theme-on-surface-variant">{{ $randevu['note'] }}</native:text>
            @endif
        </native:column>

        <native:button :label="__('randevu.edit_cta')" @navigate="'/edit/'.$randevu['id']" />
    @endif
</native:column>
