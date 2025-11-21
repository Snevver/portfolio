<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\SteamAPIController;

Route::get('/', function () {
    return Inertia::render('Landing');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
});

// Route to validate if a user exists
Route::post('/validate-user', [
    SteamAPIController::class,
    'validateUser'
]);