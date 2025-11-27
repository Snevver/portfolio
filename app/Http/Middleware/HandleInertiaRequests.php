<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'steam' => [
                'steamID' => session('userSteamID'),
                'publicProfile' => session('publicProfile'),
                'steamProfileURL' => session('steamProfileURL'),
                'profilePictureURL' => session('profilePictureURL'),
                'username' => session('username'),
                'timeCreated' => session('timeCreated'),
                'accountAge' => session('accountAge'),
                'totalGamesOwned' => session('totalGamesOwned'),
                'gameIDsOwned' => session('gameIDsOwned'),
                'totalPlaytimeMinutes' => session('totalPlaytimeMinutes'),
                'avgPlaytimeMinutes' => session('avgPlaytimeMinutes'),
                'medianPlaytimeMinutes' => session('medianPlaytimeMinutes'),
                'topGames' => session('topGames'),
                'playedPercentage' => session('playedPercentage'),
            ],
        ];
    }
}