<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use InvalidArgumentException;
use Native\Mobile\UI\Theme;

class AppFonts
{
    private const TEXT_SIZES = [
        'xs' => 12,
        'sm' => 14,
        'base' => 16,
        'lg' => 18,
        'xl' => 20,
        '2xl' => 24,
        '3xl' => 30,
        '4xl' => 36,
        '5xl' => 48,
        '6xl' => 60,
    ];

    public static function apply(string $locale): void
    {
        $fonts = in_array($locale, ['ar', 'ar_fusha'], true)
            ? config('native-ui.fonts', [])
            : [
                'default' => 'System',
                'body' => 'System',
                'heading' => 'System',
                'label' => 'System',
            ];

        if ((Theme::all()['fonts'] ?? []) !== $fonts) {
            Theme::fonts($fonts, push: true);
        }
    }

    public static function font(string $role): string
    {
        $font = Theme::all()['fonts'][$role] ?? null;

        if (! is_string($font)) {
            throw new InvalidArgumentException("Unknown app font role [{$role}].");
        }

        return $font;
    }

    public static function headingClass(): string
    {
        return in_array(App::getLocale(), ['ar', 'ar_fusha'], true) ? '' : 'font-semibold';
    }

    public static function textClass(string $size): string
    {
        if (! in_array(App::getLocale(), ['ar', 'ar_fusha'], true)) {
            return 'text-'.$size;
        }

        if (! isset(self::TEXT_SIZES[$size])) {
            throw new InvalidArgumentException("Unknown text size [{$size}].");
        }

        return self::pointClass(self::TEXT_SIZES[$size]);
    }

    public static function pointClass(int $size): string
    {
        if ($size < 1) {
            throw new InvalidArgumentException('Text size must be positive.');
        }

        if (in_array(App::getLocale(), ['ar', 'ar_fusha'], true)) {
            $size++;
        }

        return 'text-['.$size.'px]';
    }
}
