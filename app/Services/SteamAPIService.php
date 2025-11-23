<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SteamAPIService
{
    // Steam Web API endpoints
    private string $numericIDendpoint = 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/';
    private string $customURLEndpoint = 'https://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/';
    private string $ownedGamesEndpoint = 'https://api.steampowered.com/IPlayerService/GetOwnedGames/v1/';
    private string $apiKey;

    // Constructor to initialize API key
    public function __construct()
    {
        $this->apiKey = config('steam.key');
    }

    /**
     * Fetch player summary from Steam API.
     *
     * @param string $input Raw input (URL, numeric ID, or custom ID)
     * @param bool $isCustomID Whether the provided input is a custom Steam ID
     * @return \Illuminate\Http\Client\Response
     */
    public function fetchPlayerSummary(string $input, bool $isCustomID = false)
    {
        $userSteamID = $this->sanitizeInput($input, $isCustomID);

        return Http::get($this->numericIDendpoint, [
                'key' => $this->apiKey,
                'steamids' => $userSteamID,
            ]);
    }

    /**
     * Sanitize input
     *
     * @param string $input Raw input (URL, numeric ID, or custom ID)
     * @param bool $isCustomID Whether the provided input is a custom Steam ID
     * @return string The sanitized Steam ID (numeric)
     */
    private function sanitizeInput(string $input, bool $isCustomID): string
    {
        $input = trim($input);

        // Remove everything from the input except for the content after the last '/'. 
        // This will get the ID, no matter what format is submitted
        if (preg_match('#/([^/]+)/?$#', $input, $matches)) {
            $input = $matches[1];
        }

        // If input is a custom id, resolve to numeric SteamID. Otherwise use input directly
        return $isCustomID ? $this->resolveCustomID($input) : $input;
    }

    /**
     * Resolve a vanity name to a numeric SteamID using Steam's ResolveVanityURL endpoint.
     * Throws an exception if resolution fails.
     *
     * @param string $vanityName
     * @return string
     * @throws \Exception
     */
    private function resolveCustomID(string $vanityName): string
    {
        $response = Http::get($this->customURLEndpoint, [
                'key' => $this->apiKey,
                'vanityurl' => $vanityName,
            ]);

        if ($response->successful() && $response->json('response.success') === 1) {
            return $response->json('response.steamid');
        }

        throw new \Exception("Failed to resolve vanity '{$vanityName}' to a numeric SteamID.");
    }

    /**
     * Get the total number of games owned by the user.
     *
     * @param string $steamid The numeric Steam ID
     * @return int
     */
    public function getGameAmount(string $steamid): int
    {
        $data = $this->fetchOwnedGamesData($steamid);
        return $data['game_count'] ?? 0;
    }

    /**
     * Get an array of owned game IDs.
     *
     * @param string $steamid The numeric Steam ID
     * @return array<int>
     */
    public function getGameIDs(string $steamid): array
    {
        $data = $this->fetchOwnedGamesData($steamid);
        $games = $data['games'] ?? [];
        
        return array_column($games, 'appid');
    }

    /**
     * Fetch owned games data from Steam API.
     *
     * @param string $steamid The numeric Steam ID
     * @return array The `response` array from Steam on success, or an empty array on failure
     */
    private function fetchOwnedGamesData(string $steamid): array
    {
        if (empty($steamid)) {
            Log::warning('Empty Steam ID provided to fetchOwnedGamesData');
            return [];
        }

        $params = [
            'key' => $this->apiKey,
            'steamid' => $steamid,
            'include_appinfo' => false,
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
                'steamid' => $steamid
            ]);
        } catch (\Throwable $e) {
            Log::error('fetchOwnedGames exception', [
                'error' => $e->getMessage(),
                'steamid' => $steamid
            ]);
        }

        return [];
    }
}