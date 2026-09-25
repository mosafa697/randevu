<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\NativeComponents\Follow;
use App\NativeComponents\Layouts\RandevuLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\Testing\Native;
use Tests\TestCase;

/**
 * The header sun/moon action flips light ↔ dark. Two regressions are
 * guarded here:
 *  - TailwindParser's class cache is process-lifetime, so a toggle must
 *    invalidate it or the first render's colors win forever.
 *  - The drawn chrome must carry explicit palette colors, otherwise the
 *    native bars fall back to the OS scheme (white text on a light app
 *    palette when the device is dark).
 */
class ThemeToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_action_flips_palette_icon_and_chrome_colors(): void
    {
        $screen = Native::test(Follow::class, layout: RandevuLayout::class, platform: 'android');

        $this->assertNull(Setting::get('theme'));

        // Default light: forced light palette in both blocks (no distinct
        // dark companion), moon icon inviting the switch to dark.
        $tree = $screen->tree();
        $this->assertSame('#FBF9F4', $this->effectiveBackground($tree));
        $this->assertSame('dark_mode', $this->actionIcon($tree));
        $this->assertSame('#FBF9F4', $this->topBarProp($tree, 'background_color'));
        $this->assertSame('#2B2740', $this->topBarProp($tree, 'text_color'));

        // light → dark: forced palette lands in both blocks, chrome follows.
        $screen->press('toggleTheme');
        $this->assertSame('dark', Setting::get('theme'));
        $tree = $screen->tree();
        $this->assertSame('#14142A', $this->effectiveBackground($tree));
        $this->assertSame('light_mode', $this->actionIcon($tree));
        $this->assertSame('#14142A', $this->topBarProp($tree, 'background_color'));
        $this->assertSame('#F3F1FB', $this->topBarProp($tree, 'text_color'));
        $this->assertSame('#14142A', $this->bottomNavProp($tree, 'background_color'));
        $this->assertSame('#8E8AB5', $this->bottomNavProp($tree, 'text_color'));
        $this->assertSame('#9C90F5', $this->bottomNavProp($tree, 'active_color'));

        // dark → light: the authored light palette returns losslessly.
        $screen->press('toggleTheme');
        $this->assertSame('light', Setting::get('theme'));
        $tree = $screen->tree();
        $this->assertSame('#FBF9F4', $this->effectiveBackground($tree));
        $this->assertSame('dark_mode', $this->actionIcon($tree));
        $this->assertSame('#FBF9F4', $this->topBarProp($tree, 'background_color'));
        $this->assertSame('#2B2740', $this->topBarProp($tree, 'text_color'));
    }

    /**
     * Background the renderer will actually paint in either system scheme:
     * the dark companion when the collector emitted one, otherwise the
     * light value (it drops redundant dark props).
     */
    private function effectiveBackground(array $tree): ?string
    {
        $content = $tree['children'][1] ?? [];

        return $content['props']['dark_bg_color'] ?? $content['style']['bg_color'] ?? null;
    }

    private function actionIcon(array $tree): ?string
    {
        return $tree['children'][0]['children'][0]['props']['icon'] ?? null;
    }

    private function topBarProp(array $tree, string $key): mixed
    {
        return $tree['children'][0]['props'][$key] ?? null;
    }

    private function bottomNavProp(array $tree, string $key): mixed
    {
        return $tree['children'][2]['props'][$key] ?? null;
    }
}
