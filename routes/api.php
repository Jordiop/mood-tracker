<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\MoodEntryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    // Mood Entry routes
    Route::apiResource('moods', MoodEntryController::class);

    // Admin routes
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/users/{user}/moods', [AdminController::class, 'userMoods']);
    });
});
