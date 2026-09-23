<?php

namespace App\NativeComponents;

use App\Models\Randevu;
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

    public string $occurs_on = '';

    public string $note = '';

    public bool $confirmingDelete = false;

    /** @var array<string,string> */
    public array $errors = [];

    public function mount(): void
    {
        $this->randevuId = (int) $this->param('id');
        $randevu = $this->findOrFail();
        $this->title = $randevu->title;
        $this->occurs_on = $randevu->occurs_on->toDateString();
        $this->note = (string) ($randevu->note ?? '');
    }

    public function update(): void
    {
        $validator = Validator::make([
            'title' => $this->title,
            'occurs_on' => $this->occurs_on,
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
            'occurs_on' => $this->occurs_on,
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

    public function navTitle(): string
    {
        return 'Edit randevu';
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
