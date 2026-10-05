<?php

namespace App\NativeComponents\Concerns;

/**
 * Shared color picking for the Create/Edit forms: one `color` prop holds
 * the current hex (`''` = none), swatch taps set it via @press bare
 * methods. The palette shown on screen must mirror Randevu::COLOR_PRESETS.
 *
 * The R/G/B sliders bind `color_r/g/b` (0-255 ints). Slider moves flow
 * through ComponentState::set() like the date selects, so the
 * `updated{Prop}` hooks below rebuild the shared hex — the single source
 * of truth stays `color`, and save/update validation is unchanged.
 * Preset taps and clearing funnel through setColor(), which mirrors the
 * hex back into the sliders. Direct property writes never fire hooks,
 * so neither direction loops.
 */
trait PicksColor
{
    public string $color = '';

    public int $color_r = 0;

    public int $color_g = 0;

    public int $color_b = 0;

    /** Collapsible custom-color section (RGB sliders). */
    public bool $show_custom_color = false;

    /** @press entry point — bare method only. */
    public function toggleCustomColor(): void
    {
        $this->show_custom_color = ! $this->show_custom_color;
    }

    /** Swatch taps — @press needs a bare method per color. */
    public function pickBlue(): void
    {
        $this->setColor('#2563EB');
    }

    public function pickIndigo(): void
    {
        $this->setColor('#4F46E5');
    }

    public function pickPurple(): void
    {
        $this->setColor('#7C3AED');
    }

    public function pickPink(): void
    {
        $this->setColor('#DB2777');
    }

    public function pickRed(): void
    {
        $this->setColor('#DC2626');
    }

    public function pickOrange(): void
    {
        $this->setColor('#EA580C');
    }

    public function pickAmber(): void
    {
        $this->setColor('#D97706');
    }

    public function pickGreen(): void
    {
        $this->setColor('#059669');
    }

    public function pickTeal(): void
    {
        $this->setColor('#0D9488');
    }

    public function pickCyan(): void
    {
        $this->setColor('#0E7490');
    }

    public function pickBrown(): void
    {
        $this->setColor('#8C5E3C');
    }

    public function pickGray(): void
    {
        $this->setColor('#64748B');
    }

    public function clearColor(): void
    {
        $this->setColor('');
    }

    public function updatedColorR(): void
    {
        $this->syncHexFromChannels();
    }

    public function updatedColorG(): void
    {
        $this->syncHexFromChannels();
    }

    public function updatedColorB(): void
    {
        $this->syncHexFromChannels();
    }

    /** Clamp the channels to 0-255 and rebuild the shared hex. */
    protected function syncHexFromChannels(): void
    {
        $this->color_r = max(0, min(255, (int) round($this->color_r)));
        $this->color_g = max(0, min(255, (int) round($this->color_g)));
        $this->color_b = max(0, min(255, (int) round($this->color_b)));

        $this->setColor(sprintf('#%02X%02X%02X', $this->color_r, $this->color_g, $this->color_b));
    }

    private function setColor(string $hex): void
    {
        $this->color = $hex;
        $this->syncChannelsFromHex();
        $this->errors = array_diff_key($this->errors, ['color' => true]);
    }

    /** Mirror the shared hex into the slider channels (`''` → 0,0,0). */
    private function syncChannelsFromHex(): void
    {
        if (preg_match('/^#([0-9A-Fa-f]{6})$/', $this->color, $matches)) {
            $this->color_r = hexdec(substr($matches[1], 0, 2));
            $this->color_g = hexdec(substr($matches[1], 2, 2));
            $this->color_b = hexdec(substr($matches[1], 4, 2));

            return;
        }

        $this->color_r = 0;
        $this->color_g = 0;
        $this->color_b = 0;
    }
}
