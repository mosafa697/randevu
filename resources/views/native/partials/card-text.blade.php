@php
    // Single block on purpose: keep every raw-PHP assignment in this one
    // block. Laravel pairs the first opener with the first closer it
    // finds anywhere in the file (even inside a comment), so a second
    // pair must never appear here.
    $rtl = $rtl ?? \App\Services\AppLocale::isRtl();
    $accent = isset($item['color']) && is_string($item['color']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $item['color']) ? $item['color'] : null;
    // Follow cards carry an urgency tier; Memories cards don't and keep
    // the entered-calendar pill exactly as before.
    $tier = $item['tier'] ?? null;
    $pillClass = match ($tier) {
        'accent' => 'bg-theme-accent text-theme-on-accent',
        'strong' => 'bg-theme-primary text-theme-on-primary',
        'muted' => 'bg-theme-surface-variant text-theme-on-surface',
        // No tier (Memories): keep the entered-calendar pill, but with
        // the filled pairs — the soft-fill + on-* text pairs they
        // replaced were below 1.4:1 in one mode each.
        default => ((($item['entered_in'] ?? 'gregorian') === 'hijri')
            ? 'bg-theme-accent text-theme-on-accent'
            : 'bg-theme-primary text-theme-on-primary'),
    };
@endphp
<native:column class="flex-1 gap-1">
    @if($accent !== null)
        <native:row class="w-full items-center">
            <native:column :bg="$accent" class="w-8 h-2 rounded-full" />
        </native:row>
    @endif
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
    <native:text class="text-xs px-2 py-1 rounded-full {{ $pillClass }}">
        {{ $item['phrase'] }}
    </native:text>
</native:column>
