<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Native\Mobile\Edge\TailwindParser;
use Native\Mobile\UI\Theme;

/**
 * Applies the device-stored theme mode: light or dark. Mirrors AppLocale —
 * per-device Setting, applied at boot.
 *
 * Runtime override works by merging the chosen palette into BOTH light and
 * dark blocks: the native renderer otherwise picks tokens by the system
 * colorScheme, so a forced mode must make both blocks agree. System chrome
 * (dialogs, pickers) still follows the OS — accepted caveat.
 */
class AppTheme
{
    public const KEY = 'theme';

    public const MODES = ['light', 'dark'];

    public const DEFAULT = 'light';

    /**
     * Config key holding the palette as authored, captured before any
     * forced merge. Theme::merge() mirrors its result back into
     * `native-ui.theme.*` (Theme::syncConfig), so those keys can no longer
     * be trusted after the first merge — the snapshot is what every later
     * force reads, keeping light → dark → light lossless. Lives in the
     * per-request config repository (rebuilt from files each boot), so a
     * dev edit to config/native-ui.php is picked up on the next request.
     */
    private const AUTHORED_KEY = 'native-ui.authored-theme';

    public static function current(): string
    {
        $stored = self::storeAvailable() ? Setting::get(self::KEY) : null;

        return in_array($stored, self::MODES, true) ? $stored : self::DEFAULT;
    }

    public static function apply(): string
    {
        $mode = self::current();
        self::forcePalette($mode);

        return $mode;
    }

    /**
     * Read a token from the active (forced) palette — for chrome builders
     * and anything else that needs a literal color instead of a
     * `bg-theme-*` class. Avoids the `theme()` helper's system-appearance
     * bridge call: both blocks carry the forced palette, so the active mode
     * is the right source.
     */
    public static function token(string $name, ?string $default = null): ?string
    {
        $value = config('native-ui.theme.'.self::current().'.'.$name, $default);

        return is_string($value) ? $value : $default;
    }

    public static function persist(string $mode): string
    {
        $mode = in_array($mode, self::MODES, true) ? $mode : self::DEFAULT;

        if (self::storeAvailable()) {
            Setting::set(self::KEY, $mode);
        }

        self::forcePalette($mode);

        return $mode;
    }

    /**
     * Flip light ↔ dark. Returns the new mode.
     */
    public static function toggle(): string
    {
        return self::persist(self::current() === 'light' ? 'dark' : 'light');
    }

    /**
     * Write the source palette over the other block so both system modes
     * render the forced palette. Reads the authored snapshot — never live
     * config, which Theme::merge() has already overwritten with the last
     * forced palette (so light → dark → light would otherwise be lossy).
     */
    private static function forcePalette(string $mode): void
    {
        $palette = self::authored()[$mode] ?? [];

        if ($palette === []) {
            return;
        }

        Theme::merge([
            'light' => $palette,
            'dark' => $palette,
        ]);

        // Theme::merge() only updates the token store; bg-theme-* classes are
        // resolved through TailwindParser's process-lifetime class cache.
        // Without this, the first render of the session keeps winning and a
        // runtime toggle never repaints.
        TailwindParser::clearCache();
    }

    /**
     * The palette as authored in config, captured once per request before
     * any merge. Theme::syncConfig() mirrors merge results back into
     * config(), so live config can't be trusted after the first forced mode.
     *
     * @return array<string, mixed>
     */
    private static function authored(): array
    {
        if (! config()->has(self::AUTHORED_KEY)) {
            config([self::AUTHORED_KEY => config('native-ui.theme', [])]);
        }

        return (array) config(self::AUTHORED_KEY, []);
    }

    private static function storeAvailable(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Throwable) {
            return false;
        }
    }
}
