<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\SteamAPIController;
use App\Http\Controllers\ClassicGamemodeController;

Route::get('/', function () {
    return Inertia::render('Landing');
});

// Group protected routes that require a SteamID in the session
Route::middleware('steam.auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    });

    Route::post('/api/classic', [ClassicGamemodeController::class, 'index']);
});

Route::post('/initiate-user', [SteamAPIController::class, 'validateUser']);