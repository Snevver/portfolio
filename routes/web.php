<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\SteamAPIController;

Route::get('/', function () {
    return Inertia::render('Landing');
});

// Group protected routes that require a SteamID in the session
Route::middleware('steam.auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    });
});

// Route to validate if a user exists
Route::post('/validate-user', [
    SteamAPIController::class,
    'validateUser'
]);