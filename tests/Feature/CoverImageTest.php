<?php

namespace Tests\Feature;

use App\Models\Randevu;
use App\Models\Setting;
use App\NativeComponents\Follow;
use App\NativeComponents\RandevuCreate;
use App\NativeComponents\RandevuDetails;
use App\NativeComponents\RandevuEdit;
use App\Services\RandevuTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\Testing\Native;
use Tests\TestCase;

class CoverImageTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }

    private function tempImage(string $ext = 'jpg', int $bytes = 1024): string
    {
        $base = tempnam(sys_get_temp_dir(), 'randevu-cover-');
        $path = $base.'.'.$ext;
        rename($base, $path);
        file_put_contents($path, str_repeat('a', $bytes));
        $this->tempFiles[] = $path;

        return $path;
    }

    /**
     * Android-shaped gallery copy: real JPEG bytes, NO file suffix —
     * exactly what `Camera::pickImages()` delivers on device.
     */
    private function tempGalleryCopy(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'gallery_selected_');
        file_put_contents($path, "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00".str_repeat("\x00", 512));
        $this->tempFiles[] = $path;

        return $path;
    }

    public function test_create_saves_cover_path(): void
    {
        Setting::set('locale', 'en');
        $cover = $this->tempImage();
        $date = today()->addDay();

        Native::test(RandevuCreate::class)
            ->set('title', 'Covered')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('cover_path', $cover)
            ->call('save')
            ->assertReplacedWith('/follow');

        $this->assertSame($cover, Randevu::where('title', 'Covered')->firstOrFail()->cover_path);
    }

    public function test_create_rejects_bad_cover_type(): void
    {
        Setting::set('locale', 'en');
        $date = today()->addDay();

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Bad cover')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('cover_path', '/tmp/evil.exe')
            ->call('save');

        $screen->assertNotSet('errors', []);
        $this->assertArrayHasKey('cover', $screen->get('errors'));
        $this->assertDatabaseCount('randevus', 0);
    }

    public function test_create_rejects_oversize_cover(): void
    {
        Setting::set('locale', 'en');
        $cover = $this->tempImage('jpg', 6 * 1024 * 1024);
        $date = today()->addDay();

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Huge cover')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('cover_path', $cover)
            ->call('save');

        $screen->assertNotSet('errors', []);
        $this->assertArrayHasKey('cover', $screen->get('errors'));
        $this->assertDatabaseCount('randevus', 0);
    }

    public function test_media_selected_event_sets_cover_and_rejects_bad_files(): void
    {
        $cover = $this->tempImage();

        $screen = Native::test(RandevuCreate::class)
            ->call('handleMediaSelected', true, [$cover], 1);

        $this->assertSame($cover, $screen->get('cover_path'));

        $screen->call('handleMediaSelected', true, ['/tmp/evil.exe'], 1);

        $this->assertSame($cover, $screen->get('cover_path'));
        $this->assertArrayHasKey('cover', $screen->get('errors'));

        $screen->call('removeCover');

        $this->assertSame('', $screen->get('cover_path'));
        $this->assertArrayNotHasKey('cover', $screen->get('errors'));
    }

    public function test_android_gallery_copy_without_extension_is_accepted(): void
    {
        $cover = $this->tempGalleryCopy();

        $screen = Native::test(RandevuCreate::class)
            ->call('handleMediaSelected', true, [[
                'path' => $cover,
                'mimeType' => 'image/jpeg',
                'extension' => 'jpg',
                'type' => 'image',
            ]], 1);

        $this->assertSame($cover, $screen->get('cover_path'));
        $this->assertArrayNotHasKey('cover', $screen->get('errors'));
    }

    public function test_create_saves_extensionless_gallery_copy(): void
    {
        Setting::set('locale', 'en');
        $cover = $this->tempGalleryCopy();
        $date = today()->addDay();

        Native::test(RandevuCreate::class)
            ->set('title', 'Gallery cover')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('cover_path', $cover)
            ->call('save')
            ->assertReplacedWith('/follow');

        $this->assertSame($cover, Randevu::where('title', 'Gallery cover')->firstOrFail()->cover_path);
    }

    public function test_create_saves_unverifiable_gallery_path_without_mime(): void
    {
        // The reported bug: picked on device (extensionless, invisible to
        // PHP under Jump, no MIME carried) — save must succeed, not raise
        // "Could not read that file".
        Setting::set('locale', 'en');
        $cover = '/data/data/app/files/Gallery/gallery_selected_1759000000000';
        $date = today()->addDay();

        Native::test(RandevuCreate::class)
            ->set('title', 'Jump cover')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('cover_path', $cover)
            ->call('save')
            ->assertReplacedWith('/follow');

        $this->assertSame($cover, Randevu::where('title', 'Jump cover')->firstOrFail()->cover_path);
    }

    public function test_media_selected_rejection_names_detected_type(): void
    {
        $bad = $this->tempImage('pdf', 128);

        $screen = Native::test(RandevuCreate::class)
            ->call('handleMediaSelected', true, [[
                'path' => $bad,
                'mimeType' => 'application/pdf',
                'extension' => 'pdf',
                'type' => 'other',
            ]], 1);

        $this->assertSame('', $screen->get('cover_path'));
        $this->assertArrayHasKey('cover', $screen->get('errors'));
        $this->assertStringContainsString('application/pdf', $screen->get('errors')['cover']);
    }

    public function test_pick_cover_does_not_crash_without_bridge(): void
    {
        Native::test(RandevuCreate::class)
            ->call('pickCover')
            ->assertSet('cover_path', '');
    }

    public function test_follow_renders_cover_image_and_falls_back_when_missing(): void
    {
        Setting::set('locale', 'en');
        $cover = $this->tempImage();

        Randevu::create(['title' => 'Covered', 'occurs_on' => today()->addDay(), 'cover_path' => $cover]);
        Randevu::create(['title' => 'Gone', 'occurs_on' => today()->addDays(2), 'cover_path' => '/tmp/randevu-missing-cover.jpg']);
        Randevu::create(['title' => 'Plain', 'occurs_on' => today()->addDays(3)]);

        $screen = Native::test(Follow::class);

        $byTitle = collect($screen->get('appointments'))->keyBy('title');
        $this->assertSame($cover, $byTitle['Covered']['cover']);
        $this->assertNull($byTitle['Gone']['cover']);
        $this->assertNull($byTitle['Plain']['cover']);

        $screen->assertElement('image', fn ($n) => ($n['props']['src'] ?? null) === $cover);
        $screen->assertMissingElement('image', fn ($n) => ($n['props']['src'] ?? null) === '/tmp/randevu-missing-cover.jpg');
    }

    public function test_details_renders_cover_image(): void
    {
        $cover = $this->tempImage();
        $randevu = Randevu::create(['title' => 'Covered', 'occurs_on' => today()->addDay(), 'cover_path' => $cover]);

        Native::test(RandevuDetails::class, ['id' => $randevu->id])
            ->assertElement('image', fn ($n) => ($n['props']['src'] ?? null) === $cover);
    }

    public function test_edit_prefills_cover_and_persists_changes(): void
    {
        $cover = $this->tempImage();
        $randevu = Randevu::create(['title' => 'Keep', 'occurs_on' => today(), 'cover_path' => $cover]);

        Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->assertSet('cover_path', $cover)
            ->call('removeCover')
            ->call('update')
            ->assertReplacedWith('/follow');

        $this->assertNull($randevu->fresh()->cover_path);
    }
}
