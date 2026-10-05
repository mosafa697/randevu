@php
    $r = 29;
    $c = 2 * M_PI * $r;
    $offset = $c * (1 - ($pct ?? 1));
    // Follow cards pass an urgency tier; Memories cards don't and keep
    // the entered-calendar fill exactly as before.
    $tier = $tier ?? null;
    if ($tier === 'accent') {
        $fill = theme('accent');
    } elseif ($tier === 'strong') {
        $fill = theme('primary');
    } elseif ($tier === 'muted') {
        $fill = theme('on-surface-variant');
    } else {
        $fill = (($entered_in ?? 'gregorian') === 'hijri') ? theme('accent') : theme('primary');
    }
    $track = theme('progress-track');
    $text = theme('on-surface');
    $unitColor = theme('on-surface-variant');
    $unitLabel = e(__('randevu.ring_unit_days'));
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
        . '<text x="32" y="29" text-anchor="middle" dominant-baseline="central" font-size="17" font-weight="700" fill="' . $text . '" font-family="system-ui,sans-serif">' . $n . '</text>'
        . '<text x="32" y="45" text-anchor="middle" dominant-baseline="central" font-size="10" fill="' . $unitColor . '" font-family="system-ui,sans-serif">' . $unitLabel . '</text>'
        . '</svg></body></html>';
@endphp
<native:webview class="w-16 h-16" :html="$html" />
