<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('management')->group(function (): void {
    Route::view('jurisdictions', 'management.jurisdictions')->name('management.jurisdictions');
});
