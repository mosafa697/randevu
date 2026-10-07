<?php

namespace Tests\Unit;

use Tests\TestCase;

class BrandAssetsTest extends TestCase
{
    public function test_eye_clock_svg_sources_exist_with_theme_strokes(): void
    {
        $light = file_get_contents(resource_path('brand/logo.svg'));
        $dark = file_get_contents(resource_path('brand/logo-dark.svg'));

        $this->assertStringContainsString('stroke="#6C63D9"', $light);
        $this->assertStringContainsString('stroke="#9488EF"', $dark);

        foreach ([$light, $dark] as $svg) {
            $this->assertStringContainsString('viewBox="0 0 1024 1024"', $svg);
            // Eye outlines + clock pupil + hands + ticks.
            $this->assertGreaterThanOrEqual(6, substr_count($svg, '<path'));
            $this->assertStringContainsString('<circle', $svg);
        }
    }

    public function test_launcher_icon_is_1024_transparent(): void
    {
        $path = public_path('icon.png');

        $this->assertFileExists($path);

        $size = getimagesize($path);

        $this->assertSame(1024, $size[0]);
        $this->assertSame(1024, $size[1]);
        $this->assertSame('image/png', $size['mime']);
    }

    public function test_splashes_match_theme_backgrounds(): void
    {
        foreach (['splash.png', 'splash-dark.png'] as $file) {
            $path = public_path($file);

            $this->assertFileExists($path);

            $size = getimagesize($path);

            $this->assertSame(1280, $size[0]);
            $this->assertSame(1920, $size[1]);
        }
    }
}
