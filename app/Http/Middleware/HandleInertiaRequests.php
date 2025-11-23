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
            'auth' => [
                'user' => $request->user(),
            ],
            'steam' => [
                'steamID' => session('userSteamID'),
                'publicProfile' => session('publicProfile'),
                'profileURL' => session('profileURL'),
                'username' => session('username'),
                'timeCreated' => session('timeCreated'),
                'totalGamesOwned' => session('totalGamesOwned'),
                'gameIDs' => session('gameIDs'),
            ],
        ];
    }
}