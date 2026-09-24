<?php

namespace App\NativeComponents\Concerns;

/**
 * Shared color picking for the Create/Edit forms: one `color` prop holds
 * the current hex (`''` = none), swatch taps set it via @press bare
 * methods. The palette shown on screen must mirror Randevu::COLOR_PRESETS.
 */
trait PicksColor
{
    public string $color = '';

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

    private function setColor(string $hex): void
    {
        $this->color = $hex;
        $this->errors = array_diff_key($this->errors, ['color' => true]);
    }
}
