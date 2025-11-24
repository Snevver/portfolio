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
// and fetch basic user data from Steam API
Route::post('/initiate-user', [
    SteamAPIController::class,
    'validateUser'
]);