<?php

namespace App\NativeComponents\Concerns;

use App\Models\Randevu;
use Illuminate\Database\Eloquent\Builder;

trait FiltersEntries
{
    public string $search = '';

    public string $sort = 'nearest';

    public function sortByNearest(): void
    {
        $this->sort = 'nearest';
        $this->refresh();
    }

    public function sortByNewest(): void
    {
        $this->sort = 'newest';
        $this->refresh();
    }

    public function sortByAlpha(): void
    {
        $this->sort = 'alpha';
        $this->refresh();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->refresh();
    }

    /**
     * Model-sync hook: every keystroke in the search field flows through
     * ComponentState::set(), which fires updated{Studly} — on device and
     * in tests alike.
     */
    public function updatedSearch(): void
    {
        $this->refresh();
    }

    /**
     * Compose the current search + sort onto an entry query. The caller
     * passes a query built from one of Randevu's direction scopes
     * (upcoming/memories stay the source of truth for which rows belong
     * on the screen); only the title filter and the ordering change.
     *
     * @param Builder<Randevu> $query
     * @return Builder<Randevu>
     */
    protected function applyEntryFilters(Builder $query): Builder
    {
        return self::filterQuery($query, $this->search, $this->sort);
    }

    /**
     * Pure query composition behind applyEntryFilters(), kept static so
     * the SQL stays unit-testable without mounting a screen.
     *
     * Sort meanings (shared by both list screens): nearest keeps the
     * scope's own date order (proximity to today in that screen's
     * direction); newest is recently-added first; alpha is title order,
     * case-insensitive for Latin titles (SQLite NOCASE is ASCII-only;
     * Arabic has no case so it orders by code point either way).
     *
     * @param Builder<Randevu> $query
     * @return Builder<Randevu>
     */
    public static function filterQuery(Builder $query, string $search, string $sort): Builder
    {
        $search = trim($search);

        if ($search !== '') {
            // Escape LIKE wildcards so a typed % or _ matches literally.
            // SQLite declares no default LIKE escape, so state the
            // backslash explicitly. Column is hardcoded, values are bound.
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
            $query->whereRaw('title LIKE ? ESCAPE ?', ['%'.$escaped.'%', '\\']);
        }

        if ($sort === 'newest') {
            $query->reorder()->orderByDesc('id');
        } elseif ($sort === 'alpha') {
            $query->reorder()->orderByRaw('title COLLATE NOCASE');
        }

        return $query;
    }
}
