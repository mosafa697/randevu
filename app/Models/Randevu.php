<?php

namespace App\Models;

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
    protected $fillable = ['title', 'occurs_on', 'note'];

    protected $casts = [
        'occurs_on' => 'date',
    ];

    public static function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'occurs_on' => 'required|date',
            'note' => 'nullable|string|max:2000',
        ];
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

    public function relativePhrase(): string
    {
        return RandevuTime::phrase($this->occurs_on);
    }

    public function exactDayCount(): int
    {
        return RandevuTime::dayCount($this->occurs_on);
    }
}
