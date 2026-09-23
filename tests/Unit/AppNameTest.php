<?php

namespace Tests\Unit;

use Tests\TestCase;

class AppNameTest extends TestCase
{
    public function test_app_title_is_randevu(): void
    {
        $this->assertSame('Randevu', config('app.name'));
    }
}
