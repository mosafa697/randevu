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
 *  - The native chrome must carry the current palette (explicit nav-bar
 *    colors plus the tab bar's dark flag and token colors), otherwise the
 *    bars fall back to the OS scheme (white text on a light app palette
 *    when the device is dark).
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
        $this->assertSame('native_root_tabs', $tree['type']);
        $this->assertSame('#FBF9F4', $this->effectiveBackground($tree));
        $this->assertSame('dark_mode', $this->actionIcon($tree));
        $this->assertSame('#FBF9F4', $this->chromeProp($tree, 'nav_background_color'));
        $this->assertSame('#2B2740', $this->chromeProp($tree, 'nav_text_color'));
        $this->assertSame('#6F63DB', $this->chromeProp($tree, 'active_color'));
        $this->assertSame('#69647D', $this->chromeProp($tree, 'text_color'));
        $this->assertSame('label', $this->chromeProp($tree, 'font_name'));
        $this->assertSame('heading', $this->chromeProp($tree, 'nav_font_name'));
        $this->assertNull($this->chromeProp($tree, 'dark'));
        $this->assertNull($this->chromeProp($tree, 'background_color'));

        // light → dark: forced palette lands in both blocks, chrome follows.
        $screen->press('toggleTheme');
        $this->assertSame('dark', Setting::get('theme'));
        $tree = $screen->tree();
        $this->assertSame('#14142A', $this->effectiveBackground($tree));
        $this->assertSame('light_mode', $this->actionIcon($tree));
        $this->assertSame('#14142A', $this->chromeProp($tree, 'nav_background_color'));
        $this->assertSame('#F3F1FB', $this->chromeProp($tree, 'nav_text_color'));
        $this->assertTrue($this->chromeProp($tree, 'dark'));
        $this->assertSame('#8E8AB5', $this->chromeProp($tree, 'text_color'));
        $this->assertSame('#9C90F5', $this->chromeProp($tree, 'active_color'));

        // dark → light: the authored light palette returns losslessly.
        $screen->press('toggleTheme');
        $this->assertSame('light', Setting::get('theme'));
        $tree = $screen->tree();
        $this->assertSame('#FBF9F4', $this->effectiveBackground($tree));
        $this->assertSame('dark_mode', $this->actionIcon($tree));
        $this->assertSame('#FBF9F4', $this->chromeProp($tree, 'nav_background_color'));
        $this->assertSame('#2B2740', $this->chromeProp($tree, 'nav_text_color'));
    }

    /**
     * Background the renderer will actually paint in either system scheme:
     * the dark companion when the collector emitted one, otherwise the
     * light value (it drops redundant dark props).
     */
    private function effectiveBackground(array $tree): ?string
    {
        $content = $tree['children'][count($tree['children']) - 1] ?? [];

        return $content['props']['dark_bg_color'] ?? $content['style']['bg_color'] ?? null;
    }

    private function actionIcon(array $tree): ?string
    {
        foreach ($tree['children'] ?? [] as $child) {
            if (($child['type'] ?? null) === 'top_bar_action') {
                return $child['props']['icon'] ?? null;
            }
        }

        return null;
    }

    private function chromeProp(array $tree, string $key): mixed
    {
        return $tree['props'][$key] ?? null;
    }
}
