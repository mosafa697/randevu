<?php

namespace Tests\Unit;

use App\Services\CoverImage;
use PHPUnit\Framework\TestCase;

class CoverImageTest extends TestCase
{
    public function test_normalize_trims_and_nulls_empty(): void
    {
        $this->assertSame('/tmp/a.jpg', CoverImage::normalize('  /tmp/a.jpg  '));
        $this->assertNull(CoverImage::normalize(null));
        $this->assertNull(CoverImage::normalize(''));
        $this->assertNull(CoverImage::normalize('   '));
    }

    public function test_validate_accepts_empty_and_missing_files(): void
    {
        $this->assertNull(CoverImage::validate(null));
        $this->assertNull(CoverImage::validate(''));
        // Gone files fall back to the color-only card — not an error.
        $this->assertNull(CoverImage::validate('/tmp/randevu-missing-cover.jpg'));
    }

    public function test_validate_rejects_bad_type(): void
    {
        $this->assertSame('type', CoverImage::validate('/tmp/evil.exe'));
        $this->assertSame('type', CoverImage::validate('/tmp/notes.txt'));
        $this->assertSame('type', CoverImage::validate('/tmp/report.pdf', 'application/pdf'));
    }

    public function test_validate_trusts_signalless_invisible_gallery_path(): void
    {
        // The exact save-time failure: an Android gallery copy (no suffix,
        // no MIME carried into save, file not visible to PHP under Jump)
        // must NOT be rejected — it could only come from the picker.
        $this->assertNull(CoverImage::validate('/data/data/app/files/Gallery/gallery_selected_1759000000000'));
        $this->assertNull(CoverImage::validate('/tmp/no-extension'));
    }

    public function test_validate_accepts_allowed_extensions(): void
    {
        foreach (['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif', 'JPG'] as $ext) {
            $this->assertNull(
                CoverImage::validate("/tmp/randevu-missing-cover.{$ext}"),
                "Expected .{$ext} to validate."
            );
        }
    }

    public function test_validate_trusts_payload_mime_for_extensionless_paths(): void
    {
        // Android gallery copies arrive with no suffix at all.
        $this->assertNull(CoverImage::validate(
            '/data/data/app/files/Gallery/gallery_selected_1759000000000', 'image/jpeg'
        ));
        $this->assertSame('type', CoverImage::validate(
            '/data/data/app/files/Gallery/gallery_selected_1759000000000', 'application/pdf'
        ));
        $this->assertSame('type', CoverImage::validate('/tmp/report.pdf', 'application/pdf'));
    }

    public function test_validate_sniffs_extensionless_real_images(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'randevu-cover-');
        file_put_contents($path, "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00".str_repeat("\x00", 256));

        try {
            $this->assertNull(CoverImage::validate($path));
        } finally {
            @unlink($path);
        }
    }

    public function test_mime_from_picked_file_reads_payload_keys(): void
    {
        $this->assertSame(
            'image/jpeg',
            CoverImage::mimeFromPickedFile(['path' => '/a', 'mimeType' => 'image/jpeg', 'extension' => 'jpg', 'type' => 'image'])
        );
        $this->assertSame('image/png', CoverImage::mimeFromPickedFile(['mime' => 'IMAGE/PNG']));
        $this->assertNull(CoverImage::mimeFromPickedFile('/a.jpg'));
        $this->assertNull(CoverImage::mimeFromPickedFile([]));
    }

    public function test_type_detail_names_the_actual_problem(): void
    {
        $this->assertSame('application/pdf', CoverImage::typeDetail('/tmp/x.pdf', 'application/pdf'));
        $this->assertSame('.exe', CoverImage::typeDetail('/tmp/evil.exe'));
        $this->assertNull(CoverImage::typeDetail('/data/files/gallery_selected_1'));
    }

    public function test_to_file_uri_prefixes_device_paths_only(): void
    {
        $this->assertSame(
            'file:///data/data/app/files/Gallery/gallery_selected_1',
            CoverImage::toFileUri('/data/data/app/files/Gallery/gallery_selected_1')
        );
        $this->assertSame('file:///var/mobile/x.jpg', CoverImage::toFileUri('/var/mobile/x.jpg'));
        $this->assertSame('C:\\Users\\x\\a.jpg', CoverImage::toFileUri('C:\\Users\\x\\a.jpg'));
        $this->assertSame('https://example.com/a.jpg', CoverImage::toFileUri('https://example.com/a.jpg'));
    }

    public function test_src_returns_null_when_missing_else_the_path(): void
    {
        $this->assertNull(CoverImage::src(''));
        $this->assertNull(CoverImage::src('/tmp/randevu-missing-cover.jpg'));

        $base = tempnam(sys_get_temp_dir(), 'randevu-cover-');
        $path = $base.'.jpg';
        rename($base, $path);
        file_put_contents($path, 'x');

        try {
            $this->assertSame($path, CoverImage::src($path));
        } finally {
            @unlink($path);
        }
    }

    public function test_validate_rejects_oversize_file(): void
    {
        $base = tempnam(sys_get_temp_dir(), 'randevu-cover-');
        $path = $base.'.jpg';
        rename($base, $path);

        $handle = fopen($path, 'wb');
        $chunk = str_repeat('a', 1024 * 1024);
        for ($i = 0; $i < 6; $i++) {
            fwrite($handle, $chunk);
        }
        fclose($handle);

        try {
            $this->assertSame('size', CoverImage::validate($path));
        } finally {
            @unlink($path);
        }
    }

    public function test_validate_accepts_small_existing_file(): void
    {
        $base = tempnam(sys_get_temp_dir(), 'randevu-cover-');
        $path = $base.'.jpg';
        rename($base, $path);
        file_put_contents($path, 'fake-image-bytes');

        try {
            $this->assertNull(CoverImage::validate($path));
            $this->assertTrue(CoverImage::exists($path));
            $this->assertSame($path, CoverImage::display($path));
        } finally {
            @unlink($path);
        }
    }

    public function test_display_falls_back_when_file_missing(): void
    {
        $this->assertNull(CoverImage::display(null));
        $this->assertNull(CoverImage::display(''));
        $this->assertNull(CoverImage::display('/tmp/randevu-missing-cover.jpg'));
        $this->assertFalse(CoverImage::exists('/tmp/randevu-missing-cover.jpg'));
    }

    public function test_path_from_picked_file_handles_shapes(): void
    {
        $this->assertSame('/a.jpg', CoverImage::pathFromPickedFile('/a.jpg'));
        $this->assertSame('/a.jpg', CoverImage::pathFromPickedFile(['path' => '/a.jpg']));
        $this->assertSame('/a.jpg', CoverImage::pathFromPickedFile(['uri' => '/a.jpg']));
        $this->assertNull(CoverImage::pathFromPickedFile([]));
        $this->assertNull(CoverImage::pathFromPickedFile(null));
        $this->assertNull(CoverImage::pathFromPickedFile(['path' => '   ']));
    }
}
