<?php

namespace App\NativeComponents;

use App\Models\Randevu;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class RandevuCreate extends NativeComponent
{
    public string $title = '';

    public string $occurs_on = '';

    public string $note = '';

    /** @var array<string,string> */
    public array $errors = [];

    public function mount(): void
    {
        $this->occurs_on = now()->toDateString();
    }

    public function navTitle(): string
    {
        return 'New randevu';
    }

    public function save(): void
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

        Randevu::create([
            'title' => trim($this->title),
            'occurs_on' => $this->occurs_on,
            'note' => $this->note !== '' ? trim($this->note) : null,
        ]);

        $this->replace('/');
    }

    public function render(): View
    {
        return view('native.randevu-create');
    }
}
