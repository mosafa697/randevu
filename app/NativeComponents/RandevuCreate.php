<?php

namespace App\NativeComponents;

use App\Models\Randevu;
use App\Services\RandevuTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class RandevuCreate extends NativeComponent
{
    public string $title = '';

    public string $note = '';

    public string $day = '';

    public string $month = '';

    public string $year = '';

    /** @var list<string> */
    public array $dayOptions = [];

    /** @var list<string> */
    public array $monthOptions = [];

    /** @var list<string> */
    public array $yearOptions = [];

    /** @var array<string,string> */
    public array $errors = [];

    public function mount(): void
    {
        $today = now();
        $this->fillDateOptions();
        $this->day = (string) $today->day;
        $this->month = $today->format('F');
        $this->year = (string) $today->year;
    }

    public function navTitle(): string
    {
        return 'New randevu';
    }

    /** @return list<string> */
    public static function dayOptions(): array
    {
        return array_map(strval(...), range(1, 31));
    }

    /** @return list<string> */
    public static function yearOptions(): array
    {
        $year = now()->year;

        return array_map(strval(...), range($year - 100, $year + 30));
    }

    protected function fillDateOptions(): void
    {
        $this->dayOptions = self::dayOptions();
        $this->monthOptions = RandevuTime::MONTH_NAMES;
        $this->yearOptions = self::yearOptions();
    }

    /** Y-m-d string, or null when the selected combination is not a real date. */
    public function dateString(): ?string
    {
        $month = RandevuTime::monthNumber($this->month);
        $day = (int) $this->day;
        $year = (int) $this->year;

        if ($month === null || ! checkdate($month, $day, $year)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    public function save(): void
    {
        $validator = Validator::make([
            'title' => $this->title,
            'occurs_on' => $this->dateString(),
            'note' => $this->note ?: null,
        ], Randevu::rules());

        if ($validator->fails()) {
            $this->errors = collect($validator->errors()->messages())
                ->mapWithKeys(fn ($msgs, $field) => [$field => (string) $msgs[0]])
                ->all();

            return;
        }

        $this->errors = [];

        Randevu::create([
            'title' => trim($this->title),
            'occurs_on' => $this->dateString(),
            'note' => $this->note !== '' ? trim($this->note) : null,
        ]);

        $this->replace('/');
    }

    public function render(): View
    {
        return view('native.randevu-create');
    }
}
