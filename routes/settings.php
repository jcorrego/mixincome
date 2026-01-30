<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('settings')->group(function (): void {
    Route::redirect('/', 'settings/profile');
    Route::view('profile', 'settings.profile')->name('settings.profile');
    Route::view('password', 'settings.password')->name('settings.password');
    Route::view('appearance', 'settings.appearance')->name('settings.appearance');
});
