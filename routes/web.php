<?php

use Illuminate\Support\Facades\Route;

// Native screens live in routes/mobile.php (auto-loaded by NativePHP).
// Keep web.php for non-native fallbacks only.

Route::get('/up', fn () => response()->json(['status' => 'ok']));
