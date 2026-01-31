<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('management')->group(function (): void {
    // View routes
    Route::view('jurisdictions', 'management.jurisdictions')->name('management.jurisdictions');
    Route::view('profiles', 'management.profiles')->name('management.profiles');
    Route::view('entities', 'management.entities')->name('management.entities');
    Route::view('addresses', 'management.addresses')->name('management.addresses');
});

// API routes for management resources
Route::middleware(['auth', 'verified'])->prefix('api/management')->group(function (): void {
    Route::apiResource('user-profiles', App\Http\Controllers\Management\UserProfileController::class);
    Route::apiResource('entities', App\Http\Controllers\Management\EntityController::class);
    Route::apiResource('addresses', App\Http\Controllers\Management\AddressController::class);
});
