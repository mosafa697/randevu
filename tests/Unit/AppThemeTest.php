<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\AppTheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\UI\Theme;
use Tests\TestCase;

class AppThemeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Theme::reset();

        parent::tearDown();
    }

    public function test_defaults_to_light(): void
    {
        $this->assertSame('light', AppTheme::current());
        $this->assertSame('light', AppTheme::apply());
    }

    public function test_legacy_auto_setting_falls_back_to_light(): void
    {
        Setting::set('theme', 'auto');

        $this->assertSame('light', AppTheme::current());
    }

    public function test_persists_round_trip(): void
    {
        $this->assertSame('dark', AppTheme::persist('dark'));
        $this->assertSame('dark', AppTheme::current());

        $this->assertSame('light', AppTheme::persist('light'));
        $this->assertSame('light', Setting::get('theme'));
    }

    public function test_invalid_mode_falls_back_to_light(): void
    {
        $this->assertSame('light', AppTheme::persist('neon'));
        $this->assertSame('light', Setting::get('theme'));
    }

    public function test_forced_palette_lands_in_both_blocks(): void
    {
        AppTheme::persist('dark');

        $tokens = Theme::all();

        foreach (['primary', 'surface', 'background', 'accent'] as $token) {
            $this->assertSame(
                config("native-ui.authored-theme.dark.{$token}"),
                $tokens['light'][$token],
                "Forced dark palette missing from light block [{$token}]"
            );
            $this->assertSame(
                config("native-ui.authored-theme.dark.{$token}"),
                $tokens['dark'][$token],
                "Forced dark palette missing from dark block [{$token}]"
            );
        }
    }

    public function test_toggle_flips_light_and_dark(): void
    {
        $this->assertSame('dark', AppTheme::toggle());
        $this->assertSame('light', AppTheme::toggle());
        $this->assertSame('dark', AppTheme::toggle());
    }

    public function test_toggle_is_lossless(): void
    {
        AppTheme::persist('light');
        AppTheme::persist('dark');
        AppTheme::persist('light');

        $tokens = Theme::all();

        $this->assertSame('#FBF9F4', $tokens['light']['background']);
        $this->assertSame('#FBF9F4', $tokens['dark']['background']);
        $this->assertSame('#2B2740', $tokens['light']['on-background']);
        $this->assertSame('#2B2740', $tokens['dark']['on-background']);
    }

    public function test_token_reads_the_active_palette(): void
    {
        AppTheme::persist('dark');

        $this->assertSame('#14142A', AppTheme::token('background'));
        $this->assertSame('#F3F1FB', AppTheme::token('on-background'));
        $this->assertSame('#FBF9F4', AppTheme::token('missing', '#FBF9F4'));

        AppTheme::persist('light');

        $this->assertSame('#FBF9F4', AppTheme::token('background'));
        $this->assertSame('#2B2740', AppTheme::token('on-background'));
    }
}
