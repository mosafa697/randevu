<?php

use App\NativeComponents\Follow;
use App\NativeComponents\Layouts\RandevuLayout;
use App\NativeComponents\Memories;
use App\NativeComponents\RandevuCreate;
use App\NativeComponents\RandevuDetails;
use App\NativeComponents\RandevuEdit;
use App\NativeComponents\Settings;
use Illuminate\Support\Facades\Route;

Route::native('/', Follow::class)->layout(RandevuLayout::class)->name('randevu.follow');
Route::native('/create', RandevuCreate::class)->layout(RandevuLayout::class)->name('randevu.create');
Route::native('/memories', Memories::class)->layout(RandevuLayout::class)->name('randevu.memories');
Route::native('/details/{id}', RandevuDetails::class)->layout(RandevuLayout::class)->name('randevu.details');
Route::native('/edit/{id}', RandevuEdit::class)->layout(RandevuLayout::class)->name('randevu.edit');
Route::native('/settings', Settings::class)->layout(RandevuLayout::class)->name('randevu.settings');
