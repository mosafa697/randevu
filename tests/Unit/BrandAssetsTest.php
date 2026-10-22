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

    public function test_launcher_icon_is_at_least_1024_square_and_transparent(): void
    {
        $path = public_path('icon.png');

        $this->assertFileExists($path);

        $size = getimagesize($path);

        $this->assertSame($size[0], $size[1]);
        $this->assertGreaterThanOrEqual(1024, $size[0]);
        $this->assertSame('image/png', $size['mime']);

        $icon = imagecreatefrompng($path);
        $topLeft = imagecolorsforindex($icon, imagecolorat($icon, 0, 0));
        imagedestroy($icon);

        $this->assertSame(127, $topLeft['alpha']);
    }

    public function test_favicon_is_a_multi_size_ico(): void
    {
        $path = public_path('favicon.ico');

        $this->assertFileExists($path);

        $size = getimagesize($path);

        $this->assertSame('image/vnd.microsoft.icon', $size['mime']);
        $iconCount = unpack('vcount', substr(file_get_contents($path), 4, 2))['count'];

        $this->assertSame(3, $iconCount);
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
