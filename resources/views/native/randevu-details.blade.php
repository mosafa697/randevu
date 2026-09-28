<native:column class="w-full h-full p-4 gap-4 bg-theme-background">
    @php($rtl = \App\Services\AppLocale::isRtl())
    @if($randevu === null || !($show_full_cover && !empty($randevu['cover'])))
        <native:row class="w-full">
            <native:button :label="__('randevu.back')" @navigate.back />
        </native:row>
    @endif

    @if($randevu === null)
        <native:text class="text-base text-theme-on-surface-variant">{{ __('randevu.details_missing') }}</native:text>
    @elseif($show_full_cover && !empty($randevu['cover']))
        <native:column class="w-full h-full bg-theme-background">
            <native:scroll-view class="w-full h-full">
                <native:column class="w-full p-4 gap-4 items-center">
                    <native:image :src="$randevu['cover']" class="w-full rounded-2xl" :height="560" :fit="1" />
                    <native:button :label="__('randevu.cover_close')" @press="closeCover" class="w-full" />
                </native:column>
            </native:scroll-view>
        </native:column>
    @else
        <native:column class="w-full p-4 gap-2 rounded-2xl bg-theme-surface">
            @if(!empty($randevu['cover']))
                <native:pressable @press="openCover" class="w-full">
                    <native:image :src="$randevu['cover']" class="w-full rounded-xl" :height="200" :fit="2" />
                </native:pressable>
            @endif
            <native:row class="w-full items-center justify-between">
                @if($rtl && $randevu['is_today'])
                    <native:badge :label="__('randevu.today')" variant="accent" />
                @endif
                <native:text font="heading" class="text-xl font-bold text-theme-on-surface">{{ $randevu['title'] }}</native:text>
                @if(!$rtl && $randevu['is_today'])
                    <native:badge :label="__('randevu.today')" variant="accent" />
                @endif
            </native:row>
            <native:text class="text-base font-semibold text-theme-on-surface">{{ $randevu['phrase'] }}</native:text>
            @php($detailsAccent = isset($randevu['color']) && is_string($randevu['color']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $randevu['color']) ? $randevu['color'] : null)
            @if($detailsAccent !== null)
                <native:row class="w-full items-center">
                    <native:column :bg="$detailsAccent" class="w-8 h-2 rounded-full" />
                </native:row>
            @endif
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
