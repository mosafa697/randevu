<?php

namespace Tests\Unit;

use Tests\TestCase;

class ThemeTest extends TestCase
{
    /** @var list<string> */
    private const TOKENS = [
        'primary', 'on-primary', 'primary-soft',
        'accent', 'on-accent', 'accent-soft',
        'surface', 'on-surface', 'background', 'on-background',
        'surface-variant', 'on-surface-variant',
        'outline', 'outline-variant', 'progress-track',
        'destructive', 'on-destructive',
    ];

    public function test_randevu_brand_tokens_exist_in_both_modes(): void
    {
        foreach (['light', 'dark'] as $mode) {
            foreach (self::TOKENS as $token) {
                $this->assertNotEmpty(
                    config("native-ui.authored-theme.{$mode}.{$token}"),
                    "Missing theme token [{$mode}.{$token}]"
                );
            }
        }
    }

    public function test_brand_primary_is_randevu_violet(): void
    {
        $this->assertSame('#6F63DB', config('native-ui.authored-theme.light.primary'));
        $this->assertSame('#9C90F5', config('native-ui.authored-theme.dark.primary'));
    }

    public function test_accent_is_hijri_amber(): void
    {
        $this->assertSame('#DD8A44', config('native-ui.authored-theme.light.accent'));
        $this->assertSame('#F0A868', config('native-ui.authored-theme.dark.accent'));
    }

    public function test_dark_background_is_night_violet(): void
    {
        $this->assertSame('#14142A', config('native-ui.authored-theme.dark.background'));
        $this->assertSame('#1E1E3A', config('native-ui.authored-theme.dark.surface'));
    }

    public function test_amiri_is_body_font_with_amiri_bold_headings(): void
    {
        $fonts = config('native-ui.fonts');

        $this->assertSame('Amiri-Regular', $fonts['default']);
        $this->assertSame('Amiri-Regular', $fonts['body']);
        $this->assertSame('Amiri-Bold', $fonts['heading']);
        $this->assertSame('Tajawal-Bold', $fonts['label']);

        foreach (['Amiri-Regular', 'Amiri-Bold', 'Tajawal-Bold'] as $token) {
            $this->assertFileExists(
                resource_path("fonts/{$token}.ttf"),
                "Bundled font file [{$token}.ttf] missing"
            );
        }
    }
}
