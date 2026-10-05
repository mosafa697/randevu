<?php

namespace Tests\Unit;

use Tests\TestCase;

class ThemeTest extends TestCase
{
    /** @var list<string> */
    private const TOKENS = [
        'primary', 'on-primary', 'primary-soft', 'primary-on-soft',
        'secondary', 'on-secondary',
        'accent', 'on-accent', 'accent-soft', 'accent-text',
        'field-border',
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
        $this->assertSame('#5F53D0', config('native-ui.authored-theme.light.primary'));
        $this->assertSame('#9488EF', config('native-ui.authored-theme.dark.primary'));
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

    public function test_new_aa_tokens_exist_in_both_modes(): void
    {
        $this->assertSame('#9A4F0E', config('native-ui.authored-theme.light.accent-text'));
        $this->assertSame('#F0A868', config('native-ui.authored-theme.dark.accent-text'));
        $this->assertSame('#8B869E', config('native-ui.authored-theme.light.field-border'));
        $this->assertSame('#6F6C98', config('native-ui.authored-theme.dark.field-border'));
        $this->assertSame('#5348C2', config('native-ui.authored-theme.light.primary-on-soft'));
        $this->assertSame('#A79DFF', config('native-ui.authored-theme.dark.primary-on-soft'));
    }

    public function test_reduced_glare_dark_on_colors(): void
    {
        $this->assertSame('#E8E5F6', config('native-ui.authored-theme.dark.on-surface'));
        $this->assertSame('#E8E5F6', config('native-ui.authored-theme.dark.on-background'));
        $this->assertSame('#A19EC6', config('native-ui.authored-theme.dark.on-surface-variant'));
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
