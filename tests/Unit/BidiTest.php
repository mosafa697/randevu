<?php

namespace Tests\Unit;

use App\Support\Bidi;
use Tests\TestCase;

class BidiTest extends TestCase
{
    public function test_wraps_text_in_fsi_pdi(): void
    {
        $fsi = pack('H*', 'e281a8');
        $pdi = pack('H*', 'e281a9');

        $this->assertSame($fsi.'hello'.$pdi, Bidi::isolate('hello'));
    }

    public function test_passes_null_and_empty_through(): void
    {
        $this->assertNull(Bidi::isolate(null));
        $this->assertSame('', Bidi::isolate(''));
    }

    public function test_is_idempotent(): void
    {
        $once = Bidi::isolate('Demo: today badge (appointment).');

        $this->assertSame($once, Bidi::isolate($once));
    }

    public function test_strip_removes_isolates(): void
    {
        $this->assertSame('hello', Bidi::strip(Bidi::isolate('hello')));
        $this->assertSame('plain', Bidi::strip('plain'));
        $this->assertNull(Bidi::strip(null));
    }
}
