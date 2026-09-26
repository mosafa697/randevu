@php($rtl = $rtl ?? \App\Services\AppLocale::isRtl())
<native:column class="flex-1 gap-1">
    @if(($item['is_today'] ?? false) && ($badge ?? false))
        <native:row class="w-full items-center gap-2">
            @if($rtl)
                <native:badge :label="__('randevu.today')" variant="accent" />
            @endif
            <native:text font="heading" class="flex-1 {{ $titleClass ?? 'text-base font-semibold' }} text-theme-on-surface">{{ $item['title'] }}</native:text>
            @if(!$rtl)
                <native:badge :label="__('randevu.today')" variant="accent" />
            @endif
        </native:row>
    @else
        <native:text font="heading" class="w-full {{ $titleClass ?? 'text-base font-semibold' }} text-theme-on-surface">{{ $item['title'] }}</native:text>
    @endif
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
