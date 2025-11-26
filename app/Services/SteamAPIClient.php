<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SteamAPIClient
{
    private string $numericIDendpoint = 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/';
    private string $customURLEndpoint = 'https://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/';
    private string $ownedGamesEndpoint = 'https://api.steampowered.com/IPlayerService/GetOwnedGames/v1/';
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
}