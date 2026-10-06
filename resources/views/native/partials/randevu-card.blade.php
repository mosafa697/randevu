@php
    // Single block on purpose: keep every raw-PHP assignment in this one
    // block. Laravel pairs the first opener with the first closer it
    // finds anywhere in the file (even inside a comment), so a second
    // pair must never appear here.
    $accent = isset($item['color']) && is_string($item['color']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $item['color']) ? $item['color'] : null;
    $isToday = (bool) ($item['is_today'] ?? false);
    $showBadge = (bool) ($badge ?? true);
    $tier = $item['tier'] ?? 'muted';
    if ($tier === 'accent') {
        $pillClass = 'bg-theme-accent text-theme-on-accent';
    } elseif ($tier === 'strong') {
        $pillClass = 'bg-theme-secondary text-theme-on-secondary';
    } else {
        $pillClass = 'bg-theme-surface-variant text-theme-on-surface-variant';
    }
    if ($isToday) {
        $cardClass = 'w-full p-4 gap-3 rounded-2xl border bg-theme-accent-soft border-theme-accent';
    } else {
        $cardClass = 'w-full p-4 gap-3 rounded-2xl border bg-theme-surface border-theme-outline-variant';
    }
@endphp
<native:pressable @navigate="'/details/'.$item['id']" class="{{ $cardClass }}">
    @if(!empty($item['cover']))
        <native:image :src="$item['cover']" class="w-full rounded-xl" :height="160" :fit="2" />
    @endif
    <native:row class="w-full items-center gap-3">
        @if($accent !== null)
            <native:column :bg="$accent" class="w-1 rounded-full self-stretch" />
        @endif
        <native:column class="flex-1 gap-1">
            <native:row class="w-full items-center gap-2">
                <native:text font="heading" class="flex-1 text-lg text-theme-on-surface leading-relaxed">{{ $item['title'] }}</native:text>
                @if($isToday && $showBadge)
                    <native:badge :label="__('randevu.today')" variant="accent" />
                @endif
            </native:row>
            <native:text class="text-sm text-theme-on-surface-variant leading-relaxed">{{ $item['absolute'] }}</native:text>
            @if(!empty($item['hijri']))
                <native:text class="text-sm text-theme-on-surface-variant leading-relaxed">{{ $item['hijri'] }} {{ \App\Services\RandevuHijri::hijriSuffix() }}</native:text>
            @endif
            @if(!empty($item['note']))
                <native:text class="text-sm text-theme-on-surface-variant leading-relaxed">{{ $item['note'] }}</native:text>
            @endif
            @if(!$isToday)
                <native:text class="text-sm px-3 py-1 rounded-full self-start {{ $pillClass }}">{{ $item['phrase'] }}</native:text>
            @endif
        </native:column>
        @include('native.components.ring', [
            'pct' => $item['pct'],
            'days' => $item['days'],
            'tier' => $item['tier'] ?? null,
            'entered_in' => $item['entered_in'],
            'index' => $index ?? 0,
            'color' => $accent,
        ])
    </native:row>
</native:pressable>
