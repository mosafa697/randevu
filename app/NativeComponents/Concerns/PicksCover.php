<?php

namespace App\NativeComponents\Concerns;

use App\Services\CoverImage;
use Native\Mobile\Attributes\On;
use Native\Mobile\Events\Gallery\MediaSelected;
use Native\Mobile\Facades\Camera;

/**
 * Shared gallery-cover picking for the Create/Edit forms: one `cover_path`
 * prop holds the device file path (`''` = none), `pickCover` opens the
 * gallery, the MediaSelected event stores the first valid pick, and
 * `removeCover` goes back to a color-only card.
 *
 * Gallery-only v1 (see CoverImage): Camera::pickImages()->images()->single().
 * The bridge call safely no-ops where native gallery support is unavailable
 * (tests, builds without the camera plugin) — the form keeps working.
 */
trait PicksCover
{
    public string $cover_path = '';

    /** MIME captured alongside the pick, so save() can validate with it. */
    public string $cover_mime = '';

    /** @press entry point — bare method only. */
    public function pickCover(): void
    {
        Camera::pickImages()->images()->single()->start();
    }

    public function removeCover(): void
    {
        $this->cover_path = '';
        $this->cover_mime = '';
        $this->errors = array_diff_key($this->errors, ['cover' => true]);
    }

    #[On(MediaSelected::class)]
    public function handleMediaSelected(bool $success, array $files = [], int $count = 0): void
    {
        if (! $success || $files === []) {
            return;
        }

        $path = CoverImage::pathFromPickedFile($files[0]);
        $mime = CoverImage::mimeFromPickedFile($files[0]);

        if ($path === null) {
            return;
        }

        $error = CoverImage::validate($path, $mime);

        if ($error !== null) {
            $this->errors['cover'] = $error === 'size'
                ? __('randevu.cover_error_size')
                : $this->coverTypeMessage($path, $mime);

            return;
        }

        $this->cover_path = $path;
        $this->cover_mime = $mime ?? '';
        $this->errors = array_diff_key($this->errors, ['cover' => true]);
    }

    /** Precise type-rejection message naming what the file actually is. */
    protected function coverTypeMessage(?string $path, ?string $mime = null): string
    {
        $detail = CoverImage::typeDetail($path, $mime);

        return $detail === null
            ? __('randevu.cover_error_unknown')
            : __('randevu.cover_error_type', ['label' => $detail]);
    }
}
