<?php

namespace Tests\Unit;

use App\Models\Randevu;
use App\NativeComponents\Follow;
use Tests\TestCase;

class FiltersEntriesTest extends TestCase
{
    public function test_search_adds_title_like_filter(): void
    {
        $query = Follow::filterQuery(Randevu::query(), 'den', 'nearest');

        $this->assertStringContainsString('ESCAPE', $query->toSql());
        $this->assertSame(['%den%', '\\'], $query->getBindings());
    }

    public function test_search_escapes_like_wildcards(): void
    {
        $query = Follow::filterQuery(Randevu::query(), '100%_x\y', 'nearest');

        $this->assertSame(['%100\\%\\_x\\\\y%', '\\'], $query->getBindings());
    }

    public function test_blank_search_adds_no_filter(): void
    {
        foreach (['', '   '] as $search) {
            $this->assertStringNotContainsString(
                'like',
                strtolower(Follow::filterQuery(Randevu::query(), $search, 'nearest')->toSql())
            );
        }
    }

    public function test_nearest_keeps_scope_order(): void
    {
        $sql = Follow::filterQuery(Randevu::upcoming(), '', 'nearest')->toSql();

        $this->assertStringContainsString('order by "occurs_on" asc', $sql);
    }

    public function test_newest_orders_by_id_desc(): void
    {
        $sql = Follow::filterQuery(Randevu::upcoming(), '', 'newest')->toSql();

        $this->assertStringContainsString('order by "id" desc', $sql);
        $this->assertStringNotContainsString('order by "occurs_on"', $sql);
    }

    public function test_alpha_orders_title_case_insensitive(): void
    {
        $sql = Follow::filterQuery(Randevu::upcoming(), '', 'alpha')->toSql();

        $this->assertStringContainsString('COLLATE NOCASE', $sql);
        $this->assertStringNotContainsString('order by "occurs_on"', $sql);
    }
}
