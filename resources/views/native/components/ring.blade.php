@php
    $r = 29;
    $c = 2 * M_PI * $r;
    $offset = $c * (1 - ($pct ?? 1));
    $fill = (($entered_in ?? 'gregorian') === 'hijri') ? theme('accent') : theme('primary');
    $track = theme('progress-track');
    $text = theme('on-surface');
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
        . '<text x="32" y="33" text-anchor="middle" dominant-baseline="central" font-size="18" font-weight="700" fill="' . $text . '" font-family="system-ui,sans-serif">' . $n . '</text>'
        . '</svg></body></html>';
@endphp
<native:webview class="w-16 h-16" :html="$html" />
