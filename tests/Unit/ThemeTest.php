<?php

namespace Tests\Unit;

use Tests\TestCase;

class ThemeTest extends TestCase
{
    public function test_randevu_brand_tokens_exist_in_both_modes(): void
    {
        foreach (['light', 'dark'] as $mode) {
            foreach (['primary', 'on-primary', 'accent', 'on-accent', 'surface', 'on-surface', 'background', 'on-background'] as $token) {
                $this->assertNotEmpty(
                    config("native-ui.theme.{$mode}.{$token}"),
                    "Missing theme token [{$mode}.{$token}]"
                );
            }
        }
    }

    public function test_brand_primary_is_randevu_violet(): void
    {
        $this->assertSame('#6D28D9', config('native-ui.theme.light.primary'));
        $this->assertSame('#A78BFA', config('native-ui.theme.dark.primary'));
    }
}
