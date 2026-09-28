<?php

namespace Tests\Feature;

use App\Models\Randevu;
use App\Models\Setting;
use App\NativeComponents\Follow;
use App\NativeComponents\RandevuCreate;
use App\NativeComponents\RandevuDetails;
use App\NativeComponents\RandevuEdit;
use App\Services\CoverImage;
use App\Services\RandevuTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Native\Mobile\Testing\Native;
use Tests\TestCase;

class CoverImageTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanCoversDir();
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            @unlink($file);
        }

        $this->cleanCoversDir();

        parent::tearDown();
    }

    /** Covers storage holds no leftovers between tests. */
    private function cleanCoversDir(): void
    {
        foreach (glob(storage_path('app/covers/*')) ?: [] as $file) {
            @unlink($file);
        }
    }

    /** Every stored-cover file currently on disk. */
    private function storedCovers(): array
    {
        return glob(storage_path('app/covers/*')) ?: [];
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

    /**
     * A stored-cover row: a real file in the covers dir + its `covers/<name>`
     * reference — what an earlier save() leaves in the database.
     */
    private function storedCover(string $bytes = 'stored-cover-bytes'): string
    {
        $dir = storage_path('app/covers');
        @mkdir($dir, 0777, true);

        do {
            $ref = 'covers/'.Str::uuid()->toString().'.jpg';
            $file = storage_path('app/'.$ref);
        } while (is_file($file));

        file_put_contents($file, $bytes);

        return $ref;
    }

    public function test_create_saves_cover_path(): void
    {
        Setting::set('locale', 'en');
        $cover = $this->tempImage();
        $date = today()->addDay();

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Covered')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('cover_path', $cover)
            ->call('save');

        $randevu = Randevu::where('title', 'Covered')->firstOrFail();
        $screen->assertReplacedWith('/details/'.$randevu->id);

        $stored = $randevu->cover_path;

        $this->assertMatchesRegularExpression('#^covers/[0-9a-f-]{36}\.jpg$#', (string) $stored);
        $file = storage_path('app/'.$stored);
        $this->assertFileExists($file);
        $this->assertSame(file_get_contents($cover), file_get_contents($file));
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

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Gallery cover')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('cover_path', $cover)
            ->call('save');

        $randevu = Randevu::where('title', 'Gallery cover')->firstOrFail();
        $screen->assertReplacedWith('/details/'.$randevu->id);

        $stored = $randevu->cover_path;

        // Extensionless Android picks get named from their MIME/bytes at
        // copy time — the stored ref always carries a real suffix.
        $this->assertMatchesRegularExpression('#^covers/[0-9a-f-]{36}\.jpg$#', (string) $stored);
        $this->assertFileExists(storage_path('app/'.$stored));
    }

    public function test_create_saves_unverifiable_gallery_path_without_mime(): void
    {
        // The reported bug: picked on device (extensionless, invisible to
        // PHP under Jump, no MIME carried) — save must succeed, not raise
        // "Could not read that file". Invisible picks can't be copied, so
        // the raw phone path is stored as-is (dev-only data).
        Setting::set('locale', 'en');
        $cover = '/data/data/app/cache/Gallery/gallery_selected_1759000000000';
        $date = today()->addDay();

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Jump cover')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('cover_path', $cover)
            ->call('save');

        $randevu = Randevu::where('title', 'Jump cover')->firstOrFail();
        $screen->assertReplacedWith('/details/'.$randevu->id);

        $this->assertSame($cover, $randevu->cover_path);
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

    public function test_pick_cover_opens_the_picker(): void
    {
        // Ungated bridge: the press reaches Camera.PickMedia and no cover
        // error is raised (start()'s false is NOT a failure — the plugin
        // answers with an empty map once the gallery has opened).
        $screen = Native::test(RandevuCreate::class)->call('pickCover');

        $this->assertSame('', $screen->get('cover_path'));
        $this->assertArrayNotHasKey('cover', $screen->get('errors'));
        $this->assertNotEmpty($screen->bridge()->callsTo('Camera.PickMedia'));
        $this->assertSame('image', $screen->bridge()->callsTo('Camera.PickMedia')[0]['params']['mediaType']);
        $this->assertFalse($screen->bridge()->callsTo('Camera.PickMedia')[0]['params']['multiple']);
    }

    public function test_pick_cover_says_so_when_plugin_missing(): void
    {
        // A build without the camera plugin: the capability gate answers
        // before the press is swallowed silently — no bridge call, and the
        // form says why.
        $screen = Native::test(RandevuCreate::class);

        $screen->bridge()->withoutCapability('Camera.PickMedia');
        $screen->call('pickCover');

        $this->assertSame('', $screen->get('cover_path'));
        $this->assertSame(__('randevu.cover_error_pick'), $screen->get('errors')['cover'] ?? null);
        $this->assertEmpty($screen->bridge()->callsTo('Camera.PickMedia'));
    }

    public function test_follow_renders_cover_image_and_falls_back_when_missing(): void
    {
        Setting::set('locale', 'en');
        $cover = $this->tempImage();
        $stored = $this->storedCover('stored-cover-bytes');

        Randevu::create(['title' => 'Covered', 'occurs_on' => today()->addDay(), 'cover_path' => $cover]);
        Randevu::create(['title' => 'Stored', 'occurs_on' => today()->addDays(2), 'cover_path' => $stored]);
        Randevu::create(['title' => 'StoredGone', 'occurs_on' => today()->addDays(3), 'cover_path' => 'covers/gone.jpg']);
        Randevu::create(['title' => 'Gone', 'occurs_on' => today()->addDays(4), 'cover_path' => '/tmp/randevu-missing-cover.jpg']);
        Randevu::create(['title' => 'Plain', 'occurs_on' => today()->addDays(5)]);

        $screen = Native::test(Follow::class);

        $byTitle = collect($screen->get('appointments'))->keyBy('title');
        $this->assertSame(CoverImage::toFileUri($cover), $byTitle['Covered']['cover']);
        $this->assertSame(CoverImage::toFileUri(storage_path('app/'.$stored)), $byTitle['Stored']['cover']);
        $this->assertNull($byTitle['StoredGone']['cover']);
        $this->assertNull($byTitle['Gone']['cover']);
        $this->assertNull($byTitle['Plain']['cover']);

        $screen->assertElement('image', fn ($n) => ($n['props']['src'] ?? null) === CoverImage::toFileUri($cover));
        $screen->assertElement('image', fn ($n) => ($n['props']['src'] ?? null) === CoverImage::toFileUri(storage_path('app/'.$stored)));
        $screen->assertMissingElement('image', fn ($n) => ($n['props']['src'] ?? null) === '/tmp/randevu-missing-cover.jpg');
    }

    public function test_details_renders_cover_image(): void
    {
        $cover = $this->tempImage();
        $randevu = Randevu::create(['title' => 'Covered', 'occurs_on' => today()->addDay(), 'cover_path' => $cover]);

        Native::test(RandevuDetails::class, ['id' => $randevu->id])
            ->assertElement('image', fn ($n) => ($n['props']['src'] ?? null) === CoverImage::toFileUri($cover));
    }

    public function test_details_cover_opens_full_size_viewer(): void
    {
        Setting::set('locale', 'en');
        $cover = $this->tempImage();
        $randevu = Randevu::create(['title' => 'Covered', 'occurs_on' => today()->addDay(), 'cover_path' => $cover]);

        $screen = Native::test(RandevuDetails::class, ['id' => $randevu->id])
            ->assertSet('show_full_cover', false)
            ->assertSee('Back')
            ->assertElement('image', fn ($n) => ($n['props']['fit'] ?? null) === 2)
            ->assertMissingElement('image', fn ($n) => ($n['props']['fit'] ?? null) === 1)
            ->press('openCover')
            ->assertSet('show_full_cover', true);

        // Full image, uncropped (Fit) and tall — the actual picture, not the card crop.
        $screen->assertElement('image', fn ($n) => ($n['props']['src'] ?? null) === CoverImage::toFileUri($cover)
            && ($n['props']['fit'] ?? null) === 1
            && ($n['layout']['height'] ?? null) == 560);
        // Single exit while viewing: the Back row hides, Close is the way out.
        $screen->assertDontSee('Back');
    }

    public function test_details_cover_viewer_closes(): void
    {
        $cover = $this->tempImage();
        $randevu = Randevu::create(['title' => 'Covered', 'occurs_on' => today()->addDay(), 'cover_path' => $cover]);

        Native::test(RandevuDetails::class, ['id' => $randevu->id])
            ->press('openCover')
            ->press('closeCover')
            ->assertSet('show_full_cover', false)
            ->assertMissingElement('image', fn ($n) => ($n['props']['fit'] ?? null) === 1)
            ->assertElement('image', fn ($n) => ($n['props']['fit'] ?? null) === 2);
    }

    public function test_follow_cards_pad_the_cover_image(): void
    {
        Setting::set('locale', 'en');
        $cover = $this->tempImage();
        Randevu::create(['title' => 'Covered', 'occurs_on' => today()->addDay(), 'cover_path' => $cover]);

        $screen = Native::test(Follow::class);

        // The card separates the image from the text row below it...
        $screen->assertElement('pressable', fn ($n) => isset($n['layout']['gap']));
        // ...and the image sits in its own padded wrapper, not edge to edge.
        $screen->assertElement('column', fn ($n) => ($n['layout']['padding'] ?? null) == 8
            && collect($n['children'] ?? [])->contains(fn ($c) => ($c['type'] ?? null) === 'image'));
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

    public function test_edit_untouched_stored_cover_is_not_duplicated(): void
    {
        $stored = $this->storedCover();
        $randevu = Randevu::create(['title' => 'Keep', 'occurs_on' => today(), 'cover_path' => $stored]);

        Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->assertSet('cover_path', $stored)
            ->call('update')
            ->assertReplacedWith('/follow');

        $this->assertSame($stored, $randevu->fresh()->cover_path);
        $this->assertFileExists(storage_path('app/'.$stored));
        $this->assertCount(1, $this->storedCovers());
    }

    public function test_edit_replace_deletes_previous_stored_cover(): void
    {
        $old = $this->storedCover('old-bytes');
        $new = $this->tempImage();
        $randevu = Randevu::create(['title' => 'Swap', 'occurs_on' => today(), 'cover_path' => $old]);

        Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->set('cover_path', $new)
            ->call('update')
            ->assertReplacedWith('/follow');

        $stored = $randevu->fresh()->cover_path;
        $this->assertNotSame($old, $stored);
        $this->assertMatchesRegularExpression('#^covers/[0-9a-f-]{36}\.jpg$#', (string) $stored);
        $this->assertFileDoesNotExist(storage_path('app/'.$old));
        $this->assertFileExists(storage_path('app/'.$stored));
    }

    public function test_destroy_deletes_stored_cover(): void
    {
        $stored = $this->storedCover();
        $randevu = Randevu::create(['title' => 'Gone', 'occurs_on' => today(), 'cover_path' => $stored]);

        Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->call('destroy')
            ->assertReplacedWith('/follow');

        $this->assertNull($randevu->fresh());
        $this->assertFileDoesNotExist(storage_path('app/'.$stored));
    }
}
