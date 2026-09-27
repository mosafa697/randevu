<?php

namespace App\Services;

use Native\Mobile\System;

/**
 * Optional per-randevu cover image (gallery pick, local file only).
 *
 * Gallery-only v1: the form opens the device gallery via
 * `Camera::pickImages()->images()->single()` and stores the returned device
 * file path in `randevus.cover_path` (nullable). No server upload, no
 * downscaling — files over MAX_BYTES are rejected at pick/save time.
 *
 * Docs-first (NativePHP Mobile 4.5.2, verified in vendor source):
 * - `Native\Mobile\Camera::pickImages()` → `PendingMediaPicker`
 *   (vendor/nativephp/mobile/src/Camera.php:41, PendingMediaPicker.php)
 * - Result arrives as `Native\Mobile\Events\Gallery\MediaSelected`
 *   ($success, $files, $count), listened for with
 *   `#[On(MediaSelected::class)]` (`Native\Mobile\Attributes\On` — the
 *   Livewire-free listener).
 * - Native payload contract (nativephp/mobile-camera v1.0.4, verified in
 *   plugin source CameraCoordinator.kt `getFileMetadata` + iOS
 *   CameraFunctions.swift `copyFileToCache`): each files[] entry carries
 *   `path` + `mimeType` + `extension` + `type`. WARNING: on Android the
 *   copied `path` has NO extension (`gallery_selected_<ts>`), so type must
 *   be decided from `mimeType` first — never from the path suffix alone.
 * - Rendering via `<native:image :src="..." :height="180" :fit="2" />`
 *   (docs: /docs/mobile/4/edge-components/image — "Image in a card").
 * - Plugin page: /docs/mobile/4/plugins/core/camera
 *   (nativephp/mobile-camera v1.0.4). NOTE: that plugin is not installed in
 *   this project yet (only mobile-ui is registered); the picker call safely
 *   no-ops where the bridge is unavailable until the plugin + permission are
 *   added at build time.
 */
class CoverImage
{
    /** v1 limit: reject anything bigger, store the rest as-is. */
    public const MAX_BYTES = 5 * 1024 * 1024;

    /** Gallery image extensions accepted for covers. */
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif'];

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
     * Under Jump, PHP runs on the developer's machine while the picked file
     * lives on the phone — `is_file()` would always report false and the
     * cover would never render. So in Jump we trust the stored path and let
     * the native renderer resolve it; on device `is_file()` is authoritative
     * and gives the graceful color-only fallback when a file goes missing.
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
     * Path safe to hand to `<native:image>` — null when unset or when the
     * file went missing (color-only fallback).
     */
    public static function display(?string $path): ?string
    {
        return self::exists($path) ? self::normalize($path) : null;
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
        if (! is_file($path)) {
            return false;
        }

        try {
            $mime = function_exists('mime_content_type') ? @mime_content_type($path) : false;

            if (! is_string($mime) || $mime === '') {
                if (! class_exists(\finfo::class)) {
                    return false;
                }

                $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
            }

            return is_string($mime) && str_starts_with(strtolower($mime), 'image/');
        } catch (\Throwable) {
            return false;
        }
    }
}
