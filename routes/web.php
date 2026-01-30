<?php

declare(strict_types=1);

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get('/', function (): View {
    if (auth()->check()) {
        return view('dashboard');
    }

    return view('livewire.auth.login');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('about', 'about');

require __DIR__.'/settings.php';
require __DIR__.'/management.php';
