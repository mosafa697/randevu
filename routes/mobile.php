<?php

use App\NativeComponents\Follow;
use App\NativeComponents\RandevuCreate;
use Illuminate\Support\Facades\Route;

Route::native('/', Follow::class)->name('randevu.follow');
Route::native('/create', RandevuCreate::class)->name('randevu.create');
