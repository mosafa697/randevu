<?php

namespace App\Support;

/**
 * Bidi isolation for user-entered strings shown in cards, lists and
 * memories. Wraps text in Unicode FSI (U+2068) / PDI (U+2069) so a
 * trailing period in an Arabic paragraph holding Latin text renders at
 * the END of the line, not the start. Idempotent: already-isolated
 * strings pass through.
 */
class Bidi
{
    public static function isolate(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        // U+2068 FSI / U+2069 PDI via hex: keeps the file pure ASCII.
        $fsi = pack('H*', 'e281a8');
        $pdi = pack('H*', 'e281a9');

        if (str_starts_with($text, $fsi) && str_ends_with($text, $pdi)) {
            return $text;
        }

        return $fsi.$text.$pdi;
    }

    public static function strip(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        return str_replace(
            [pack('H*', 'e281a8'), pack('H*', 'e281a9')],
            '',
            $text
        );
    }
}
