<?php

namespace App\Models;

use App\Services\RandevuHijri;
use App\Services\RandevuTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A future appointment (occurs_on >= today) or a historical memory (occurs_on < today).
 *
 * Stored in local SQLite per device — no server DB. NativePHP runs this
 * migration on device boot.
 */
class Randevu extends Model
{
    protected $fillable = ['title', 'occurs_on', 'note', 'color', 'hijri_year', 'hijri_month', 'hijri_day', 'entered_in', 'show_years', 'show_months', 'show_days'];

    protected $casts = [
        'occurs_on' => 'date',
        'hijri_year' => 'integer',
        'hijri_month' => 'integer',
        'hijri_day' => 'integer',
        'show_years' => 'boolean',
        'show_months' => 'boolean',
        'show_days' => 'boolean',
    ];

    /** Tappable palette offered on the form. */
    public const COLOR_PRESETS = [
        'blue' => '#2563EB',
        'indigo' => '#4F46E5',
        'purple' => '#7C3AED',
        'pink' => '#DB2777',
        'red' => '#DC2626',
        'orange' => '#EA580C',
        'amber' => '#D97706',
        'green' => '#059669',
        'teal' => '#0D9488',
        'cyan' => '#0E7490',
        'brown' => '#8C5E3C',
        'gray' => '#64748B',
    ];

    public static function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'occurs_on' => 'required|date',
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'note' => 'nullable|string|max:2000',
            'hijri_year' => 'nullable|integer|min:1',
            'hijri_month' => 'nullable|integer|min:1|max:12',
            'hijri_day' => 'nullable|integer|min:1|max:30',
            'entered_in' => 'required|in:gregorian,hijri',
            'show_years' => 'boolean',
            'show_months' => 'boolean',
            'show_days' => 'boolean',
        ];
    }

    /** At least one distance unit must stay on. */
    public static function hasAnyUnit(bool $years, bool $months, bool $days): bool
    {
        return $years || $months || $days;
    }

    /**
     * Normalize a raw hex value: trim, uppercase, prepend `#` when missing.
     * Empty input becomes null (no color). Anything malformed fails the
     * rules() regex and is rejected.
     */
    public static function normalizeColor(?string $raw): ?string
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return null;
        }

        if (! str_starts_with($raw, '#')) {
            $raw = '#'.$raw;
        }

        return strtoupper($raw);
    }

    /**
     * Fill both calendars from a Gregorian date. Gregorian stays the source
     * of truth; Hijri is stored alongside for display and entry.
     *
     * @return array{hijri_year: int, hijri_month: int, hijri_day: int}
     */
    public static function hijriTriple(string $gregorianDate): array
    {
        $parts = array_map(intval(...), explode('-', $gregorianDate));
        [$year, $month, $day] = RandevuHijri::fromGregorian($parts[0], $parts[1], $parts[2]);

        return ['hijri_year' => $year, 'hijri_month' => $month, 'hijri_day' => $day];
    }

    /** @param Builder<Randevu> $query */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('occurs_on', '>=', Carbon::today())->orderBy('occurs_on');
    }

    /** @param Builder<Randevu> $query */
    public function scopeMemories(Builder $query): Builder
    {
        return $query->whereDate('occurs_on', '<', Carbon::today())->orderByDesc('occurs_on');
    }

    /** @param Builder<Randevu> $query */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('occurs_on', Carbon::today());
    }

    public function isAppointment(): bool
    {
        return $this->occurs_on->copy()->startOfDay()->gte(Carbon::today());
    }

    public function isMemory(): bool
    {
        return ! $this->isAppointment();
    }

    public function isToday(): bool
    {
        return $this->occurs_on->isToday();
    }

    /** Distance phrase rendered in this randevu's own chosen units. */
    public function relativePhrase(): string
    {
        return RandevuTime::phraseFor($this->occurs_on, $this->show_years, $this->show_months, $this->show_days);
    }

    /** Hijri display label, e.g. "12 ربيع الثاني 1448". Null when unknown. */
    public function hijriLabel(): ?string
    {
        if ($this->hijri_year === null || $this->hijri_month === null || $this->hijri_day === null) {
            return null;
        }

        return RandevuHijri::format($this->hijri_year, $this->hijri_month, $this->hijri_day);
    }

    public function exactDayCount(): int
    {
        return RandevuTime::dayCount($this->occurs_on);
    }
}
