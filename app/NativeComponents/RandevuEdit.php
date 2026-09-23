<?php

namespace App\NativeComponents;

use App\Models\Randevu;
use App\Services\RandevuTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class RandevuEdit extends NativeComponent
{
    /**
     * Only scalar state lives on the component — a full Eloquent model in
     * public state may not survive native shared-memory sync, so the row
     * is reloaded for every action.
     */
    public int $randevuId = 0;

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

    public bool $confirmingDelete = false;

    /** @var array<string,string> */
    public array $errors = [];

    public function mount(): void
    {
        $this->randevuId = (int) $this->param('id');
        $randevu = $this->findOrFail();
        $this->title = $randevu->title;
        $this->note = (string) ($randevu->note ?? '');
        $this->dayOptions = RandevuCreate::dayOptions();
        $this->monthOptions = RandevuTime::MONTH_NAMES;
        $this->yearOptions = RandevuCreate::yearOptions();
        $this->day = (string) $randevu->occurs_on->day;
        $this->month = $randevu->occurs_on->format('F');
        $this->year = (string) $randevu->occurs_on->year;
    }

    public function navTitle(): string
    {
        return 'Edit randevu';
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

    public function update(): void
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

        $this->findOrFail()->update([
            'title' => trim($this->title),
            'occurs_on' => $this->dateString(),
            'note' => $this->note !== '' ? trim($this->note) : null,
        ]);

        $this->replace('/');
    }

    public function askDelete(): void
    {
        $this->confirmingDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = false;
    }

    public function destroy(): void
    {
        $this->findOrFail()->delete();
        $this->replace('/');
    }

    private function findOrFail(): Randevu
    {
        return Randevu::findOrFail($this->randevuId);
    }

    public function render(): View
    {
        return view('native.randevu-edit');
    }
}
