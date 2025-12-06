<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SteamAPIClient
{
    private string $numericIDendpoint = 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/';
    private string $customURLEndpoint = 'https://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/';
    private string $ownedGamesEndpoint = 'https://api.steampowered.com/IPlayerService/GetOwnedGames/v1/';
    private string $storeApiEndpoint = 'https://store.steampowered.com/api/appdetails';
    private string $playerCountEndpoint = 'https://api.steampowered.com/ISteamUserStats/GetNumberOfCurrentPlayers/v1/';
    private string $apiKey;

    /**
     * Create a new SteamAPIClient.
     *
     * @return void
     */
    public function __construct()
    {
        $this->apiKey = config('steam.key');
    }

    /**
     * Fetch player summary from Steam API by numeric SteamID.
     *
     * @param string $steamId Numeric Steam ID (e.g. '76561198000000000')
     * @return \Illuminate\Http\Client\Response
     */
    public function fetchPlayerSummary(string $steamId)
    {
        return Http::get($this->numericIDendpoint, [
            'key' => $this->apiKey,
            'steamids' => $steamId,
        ]);
    }

    /**
     * Resolve a vanity URL (custom profile name) to a numeric SteamID.
     *
     * @param string $vanityName Vanity name portion of the profile URL
     * @return string|null Numeric SteamID on success, or null on failure
     */
    public function resolveVanityUrl(string $vanityName): ?string
    {
        $response = Http::get($this->customURLEndpoint, [
            'key' => $this->apiKey,
            'vanityurl' => $vanityName,
        ]);

        if ($response->successful() && $response->json('response.success') === 1) {
            return $response->json('response.steamid');
        }

        Log::warning('Failed to resolve vanity URL', ['vanity' => $vanityName]);
        return null;
    }

    /**
     * Fetch the owned games payload for a Steam user.
     *
     * @param string $steamid Numeric SteamID
     * @return array The `response` array from Steam API (keys: games, game_count) or an empty array on failure
     */
    public function fetchOwnedGames(string $steamid): array
    {
        if (empty($steamid)) {
            Log::warning('Empty Steam ID provided to fetchOwnedGames');
            return [];
        }

        $params = [
            'key' => $this->apiKey,
            'steamid' => $steamid,
            'include_appinfo' => true,
            'include_played_free_games' => true,
            'include_extended_appinfo' => true,
        ];

        try {
            $response = Http::timeout(10)
                ->retry(2, 100)
                ->get($this->ownedGamesEndpoint, $params);

            if ($response->successful()) {
                return $response->json('response') ?? [];
            }

            Log::warning('Steam API error when fetching owned games', [
                'status' => $response->status(),
                'steamid' => $steamid,
            ]);
        } catch (\Throwable $e) {
            Log::error('fetchOwnedGames exception', [
                'error' => $e->getMessage(),
                'steamid' => $steamid,
            ]);
        }

        return [];
    }

    /**
     * Fetch detailed app/game info from the Steam Store API.
     * Returns data like developer, publisher, release date, genres, etc.
     *
     * @param int $appId The Steam App ID
     * @return array|null App details or null on failure
     */
    public function fetchAppDetails(int $appId): ?array
    {
        try {
            $response = Http::timeout(10)
                ->retry(2, 100)
                ->get($this->storeApiEndpoint, [
                    'appids' => $appId,
                    'cc' => 'us',
                    'l' => 'english',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data[$appId]['success']) && $data[$appId]['success'] === true) {
                    return $data[$appId]['data'] ?? null;
                }
            }

            Log::warning('Steam Store API error when fetching app details', [
                'status' => $response->status(),
                'appId' => $appId,
            ]);
        } catch (\Throwable $e) {
            Log::error('fetchAppDetails exception', [
                'error' => $e->getMessage(),
                'appId' => $appId,
            ]);
        }

        return null;
    }

    /**
     * Fetch current player count for a game.
     *
     * @param int $appId The Steam App ID
     * @return int|null Current player count or null on failure
     */
    public function fetchCurrentPlayers(int $appId): ?int
    {
        try {
            $response = Http::timeout(10)
                ->get($this->playerCountEndpoint, [
                    'appid' => $appId,
                ]);

            if ($response->successful() && $response->json('response.result') === 1) {
                return $response->json('response.player_count');
            }
        } catch (\Throwable $e) {
            Log::error('fetchCurrentPlayers exception', [
                'error' => $e->getMessage(),
                'appId' => $appId,
            ]);
        }

        return null;
    }

    /**
     * Fetch game data from SteamSpy API.
     * Provides owner estimates, CCU, and peak player counts.
     *
     * @param int $appId The Steam App ID
     * @return array|null SteamSpy data or null on failure
     */
    public function fetchSteamSpyData(int $appId): ?array
    {
        try {
            $response = Http::timeout(10)
                ->get('https://steamspy.com/api.php', [
                    'request' => 'appdetails',
                    'appid' => $appId,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                // SteamSpy returns appid 0 for invalid/not found games
                if (isset($data['appid']) && $data['appid'] == $appId) {
                    return $data;
                }
            }
        } catch (\Throwable $e) {
            Log::error('fetchSteamSpyData exception', [
                'error' => $e->getMessage(),
                'appId' => $appId,
            ]);
        }

        return null;
    }
}