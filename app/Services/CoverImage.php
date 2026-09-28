<?php

namespace App\Services;

use Illuminate\Support\Str;
use Native\Mobile\System;

/**
 * Optional per-randevu cover image (gallery pick, local file only).
 *
 * Gallery-only v1: the form opens the device gallery via
 * `Camera::pickImages()->images()->single()` (plugin nativephp/mobile-camera,
 * installed + registered in App\Providers\NativeServiceProvider) and persists
 * a copy of the pick under `storage/app/covers/`, keeping only the relative
 * `covers/<uuid>.<ext>` reference in `randevus.cover_path` (nullable). No
 * server upload, no downscaling — files over MAX_BYTES are rejected at
 * pick/save time.
 *
 * Docs-first (NativePHP Mobile 4.5 + mobile-camera 1.0, verified in vendor
 * source):
 * - `Native\Mobile\Camera::pickImages()` → `PendingMediaPicker`
 *   (vendor/nativephp/mobile/src/Camera.php:41, PendingMediaPicker.php).
 * - Result arrives as `Native\Mobile\Events\Gallery\MediaSelected`
 *   ($success, $files, $count), listened for with
 *   `#[On(MediaSelected::class)]` (`Native\Mobile\Attributes\On` — the
 *   Livewire-free listener).
 * - Native payload contract (mobile-camera, verified in plugin source
 *   CameraCoordinator.kt:683 `getFileMetadata` + iOS CameraFunctions.swift:642
 *   `copyFileToCache`): each files[] entry carries `path` + `mimeType` +
 *   `extension` + `type`. On Android the copied `path` has NO extension
 *   (`gallery_selected_<ts>`), so type must be decided from `mimeType` first —
 *   never from the path suffix alone.
 * - The plugin copies picks into OS-PURGEABLE locations: Android
 *   `cacheDir/Gallery/` (CameraCoordinator.kt:312), iOS
 *   `temporaryDirectory()/Gallery/` (CameraFunctions.swift:645) — the OS may
 *   wipe them any time, and the iOS container path moves between installs.
 *   Storing that raw path (the first iteration of this feature) is why covers
 *   "could not be read" after the fact. save()/update() therefore call
 *   store(): copy the visible pick into durable app storage and persist only
 *   the relative ref, re-resolved against the CURRENT storage dir at every
 *   render. Under Jump the pick lives on the phone and is invisible to PHP,
 *   so the raw path is kept as-is (dev-only data).
 * - Rendering via `<native:image :src="..." :height="180" :fit="2" />`: the
 *   renderers resolve absolute device paths and `file://` URIs alike
 *   (mobile-ui ImageRenderer.kt / NativeUIImageSource.swift), so src() turns
 *   the display path into a `file://` URI.
 */
class CoverImage
{
    /** v1 limit: reject anything bigger, store the rest as-is. */
    public const MAX_BYTES = 5 * 1024 * 1024;

    /** Gallery image extensions accepted for covers. */
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif'];

    /** Payload/sniffed MIME → extension for naming stored copies. */
    private const MIME_TO_EXT = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/heic' => 'heic',
        'image/heif' => 'heif',
        'image/bmp' => 'bmp',
        'image/avif' => 'avif',
    ];

    /** Where stored copies live, relative to storage_path('app/'). */
    private const COVERS_DIR = 'covers';

    /**
     * Trim the raw path; empty becomes null (color-only card).
     */
    public static function normalize(?string $raw): ?string
    {
        $raw = trim((string) $raw);

        return $raw === '' ? null : $raw;
    }

    /**
     * Validate a picked path. Returns null when OK, otherwise one of
     * 'type' | 'size' so the form can show a localized message.
     *
     * Type is decided leniently — a real image must never be rejected:
     * the payload `mimeType` wins (Android gallery copies carry no file
     * extension at all), then the path suffix, then sniffing the file's
     * actual bytes when it exists. Rejection needs positive evidence the
     * file is NOT an image (a non-image MIME, a disallowed suffix, or a
     * visible file whose bytes aren't an image). A path with no usable
     * signal at all AND no visible file — exactly an Android gallery copy
     * under Jump — is trusted, because it could only have come from the
     * images-only picker. Rejecting that was the "Could not read that
     * file" false rejection on save.
     *
     * A path whose file is gone is NOT an error when it still looks like
     * an image (allowed suffix) — cards fall back to the color-only
     * layout via display().
     */
    public static function validate(?string $path, ?string $mime = null): ?string
    {
        $path = self::normalize($path);

        if ($path === null) {
            return null;
        }

        $mime = self::normalizeMime($mime);
        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        $isImage = ($mime !== null && str_starts_with($mime, 'image/'))
            || ($ext !== '' && in_array($ext, self::ALLOWED_EXTENSIONS, true))
            || self::sniffedImage($path);

        if (! $isImage && ($mime !== null || $ext !== '' || is_file($path))) {
            return 'type';
        }

        if (is_file($path) && ($size = filesize($path)) !== false && $size > self::MAX_BYTES) {
            return 'size';
        }

        return null;
    }

    /**
     * True when the stored path points at a readable file.
     *
     * Absolute picks only — managed `covers/…` refs are resolved (and checked)
     * by display(). Under Jump, PHP runs on the developer's machine while the
     * picked file lives on the phone — `is_file()` would always report false
     * and the cover would never render. So in Jump we trust the stored path
     * and let the native renderer resolve it; on device `is_file()` is
     * authoritative and gives the graceful color-only fallback when a file
     * goes missing.
     */
    public static function exists(?string $path): bool
    {
        $path = self::normalize($path);

        if ($path === null) {
            return false;
        }

        if (System::runningInJump()) {
            return true;
        }

        return is_file($path);
    }

    /**
     * Persist a validated pick — called only on the save/update success path.
     *
     * A visible file (on device PHP shares the filesystem with the renderer;
     * in tests the temp pick) is COPIED into `storage/app/covers/<uuid>.<ext>`
     * and only the relative `covers/<uuid>.<ext>` reference is returned for
     * the database — the plugin's own copies sit in OS-purgeable dirs, so
     * keeping them would lose the cover. A managed ref passes through
     * unchanged (re-saving an untouched edit must not duplicate the file).
     * An invisible path — exactly an Android gallery copy under Jump — is
     * kept as-is: dev-only data the phone still renders. When the covers dir
     * can't be created or the copy fails, the raw path is kept too — the
     * cover rides the plugin's own copy instead of being lost outright.
     */
    public static function store(?string $path, ?string $mime = null): ?string
    {
        $path = self::normalize($path);

        if ($path === null || self::managed($path) || ! is_file($path)) {
            return $path;
        }

        $dir = storage_path('app/'.self::COVERS_DIR);

        if (! is_dir($dir) && ! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
            return $path;
        }

        $file = $dir.'/'.Str::uuid()->toString().'.'.self::extensionFor($path, $mime);

        if (! @copy($path, $file)) {
            return $path;
        }

        return self::COVERS_DIR.'/'.basename($file);
    }

    /**
     * True when the value is one of our stored `covers/<name>` references —
     * a single safe path segment, no traversal.
     */
    public static function managed(?string $value): bool
    {
        $value = self::normalize($value);
        $prefix = self::COVERS_DIR.'/';

        if ($value === null || ! str_starts_with($value, $prefix)) {
            return false;
        }

        $name = substr($value, strlen($prefix));

        return $name !== '' && ! str_contains($name, '/') && ! in_array($name, ['.', '..'], true);
    }

    /**
     * Absolute path of a managed reference, or null for anything else.
     */
    private static function managedFile(string $value): ?string
    {
        return self::managed($value)
            ? storage_path('app/'.self::COVERS_DIR.'/'.basename($value))
            : null;
    }

    /**
     * Delete a stored copy once nothing references it anymore — the row's
     * previous cover was replaced or cleared (update/destroy). Managed refs
     * only; raw picked paths are left to the OS.
     */
    public static function forget(?string $value): void
    {
        $file = self::managedFile((string) $value);

        if ($file !== null && is_file($file)) {
            @unlink($file);
        }
    }

    /**
     * Path safe to hand to `<native:image>` — null when unset or when the
     * file went missing (color-only fallback). Managed refs resolve against
     * the current `storage/app/` (survives container moves); absolute picks
     * keep the raw value with the Jump-trust rule of exists().
     */
    public static function display(?string $path): ?string
    {
        $path = self::normalize($path);

        if ($path === null) {
            return null;
        }

        if (($file = self::managedFile($path)) !== null) {
            return is_file($file) ? $file : null;
        }

        return self::exists($path) ? $path : null;
    }

    /**
     * The renderer-ready `src` for `<native:image>`: the display path
     * turned into a `file://` URI. A bare device path (`/data/...`) has no
     * scheme, so Coil/AsyncImage — which load images "from a URL" — cannot
     * resolve it. Prefixing `file://` is what makes the native image loader
     * actually load a local file.
     */
    public static function src(?string $path): ?string
    {
        $path = self::display($path);

        return $path === null ? null : self::toFileUri($path);
    }

    /** `file://` prefix for absolute device paths; everything else as-is. */
    public static function toFileUri(string $path): string
    {
        return str_starts_with($path, '/') ? 'file://'.$path : $path;
    }

    /**
     * Pull the device file path out of one MediaSelected files[] entry.
     * Entries may arrive as plain strings or as arrays with a path key
     * (native contract: `path` + `mimeType` + `extension` + `type`).
     */
    public static function pathFromPickedFile(mixed $file): ?string
    {
        if (is_string($file)) {
            return self::normalize($file);
        }

        if (is_array($file)) {
            foreach (['path', 'file', 'uri', 'src'] as $key) {
                if (isset($file[$key]) && is_string($file[$key])) {
                    return self::normalize($file[$key]);
                }
            }
        }

        return null;
    }

    /** Pull the MIME type out of one MediaSelected files[] entry, if present. */
    public static function mimeFromPickedFile(mixed $file): ?string
    {
        if (! is_array($file)) {
            return null;
        }

        foreach (['mimeType', 'mime', 'mime_type'] as $key) {
            if (isset($file[$key]) && is_string($file[$key])) {
                return self::normalizeMime($file[$key]);
            }
        }

        return null;
    }

    /**
     * What the rejected file actually looks like — the MIME when known,
     * else the `.ext` suffix, else null (nothing detectable). Feeds the
     * `:label` placeholder of the type-error message so users see the
     * real cause instead of a generic "pick a JPG".
     */
    public static function typeDetail(?string $path, ?string $mime = null): ?string
    {
        $mime = self::normalizeMime($mime);

        if ($mime !== null) {
            return $mime;
        }

        $ext = strtolower((string) pathinfo(trim((string) $path), PATHINFO_EXTENSION));

        return $ext !== '' ? '.'.$ext : null;
    }

    public static function normalizeMime(?string $mime): ?string
    {
        $mime = strtolower(trim((string) $mime));

        return $mime === '' ? null : $mime;
    }

    /** True when the file's actual bytes sniff as an image (fileinfo). */
    private static function sniffedImage(string $path): bool
    {
        $mime = self::sniffedMime($path);

        return $mime !== null && str_starts_with($mime, 'image/');
    }

    /** The file's actual MIME from its bytes, or null when undetectable. */
    private static function sniffedMime(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        try {
            $mime = function_exists('mime_content_type') ? @mime_content_type($path) : false;

            if (! is_string($mime) || $mime === '') {
                if (! class_exists(\finfo::class)) {
                    return null;
                }

                $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
            }

            $mime = self::normalizeMime(is_string($mime) ? $mime : null);

            return $mime;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Extension for a stored copy's filename: the payload MIME wins (Android
     * gallery copies carry no suffix at all), then an allowed suffix, then
     * the bytes' own sniffed MIME — 'jpg' as the last resort.
     */
    private static function extensionFor(string $path, ?string $mime = null): string
    {
        $mime = self::normalizeMime($mime);

        if ($mime !== null && isset(self::MIME_TO_EXT[$mime])) {
            return self::MIME_TO_EXT[$mime];
        }

        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        if ($ext !== '' && in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            return $ext;
        }

        $sniffed = self::sniffedMime($path);

        return $sniffed !== null ? (self::MIME_TO_EXT[$sniffed] ?? 'jpg') : 'jpg';
    }
}
