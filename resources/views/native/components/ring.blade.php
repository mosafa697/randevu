@php
    $r = 29;
    $c = 2 * M_PI * $r;
    $offset = $c * (1 - ($pct ?? 1));
    // Follow cards pass an urgency tier; Memories cards don't and keep
    // the entered-calendar fill exactly as before. A valid selected
    // color overrides both, so the counter matches the card accent.
    $tier = $tier ?? null;
    $colorOverride = isset($color) && is_string($color) && preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ? $color : null;
    if ($colorOverride !== null) {
        $fill = $colorOverride;
    } elseif ($tier === 'accent') {
        $fill = theme('accent');
    } elseif ($tier === 'strong') {
        $fill = theme('primary');
    } elseif ($tier === 'muted') {
        $fill = theme('on-surface-variant');
    } else {
        $fill = (($entered_in ?? 'gregorian') === 'hijri') ? theme('accent') : theme('primary');
    }
    $track = theme('progress-track');
    $unitLabel = __('randevu.ring_unit_days');
    $delay = ($index ?? 0) * 100;
    $n = abs($days ?? 0);
    $html = '<!DOCTYPE html><html><head><meta name="viewport" content="width=64,initial-scale=1"><style>'
        . 'html,body{margin:0;padding:0;background:transparent;overflow:hidden}'
        . 'svg{display:block}'
        . '.fill{animation:sweep .8s ease-out forwards;animation-delay:' . $delay . 'ms}'
        . '@keyframes sweep{from{stroke-dashoffset:' . $c . '}}'
        . '@media(prefers-reduced-motion:reduce){.fill{animation:none;stroke-dashoffset:' . $offset . '}}'
        . '</style></head><body>'
        . '<svg width="64" height="64" viewBox="0 0 64 64">'
        . '<circle cx="32" cy="32" r="' . $r . '" fill="none" stroke="' . $track . '" stroke-width="6"/>'
        . '<circle class="fill" cx="32" cy="32" r="' . $r . '" fill="none" stroke="' . $fill . '" stroke-width="6" stroke-linecap="round" stroke-dasharray="' . $c . '" stroke-dashoffset="' . $offset . '" transform="rotate(-90 32 32)"/>'
        . '</svg></body></html>';
@endphp
<native:stack class="w-16 h-16">
    <native:webview class="w-16 h-16" :html="$html" />
    <native:column class="absolute inset-0 items-center justify-center">
        <native:text font="heading" class="{{ \App\Services\AppFonts::pointClass(17) }} font-bold text-theme-on-surface">{{ $n }}</native:text>
        <native:text font="body" class="{{ \App\Services\AppFonts::pointClass(10) }} text-theme-on-surface-variant">{{ $unitLabel }}</native:text>
    </native:column>
</native:stack>
