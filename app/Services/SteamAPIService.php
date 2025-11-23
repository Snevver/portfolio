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

    /**
     * Fetch player summary from Steam API.
     *
     * @param string $input Raw input (URL, numeric ID, or custom ID) || Optional if session has userSteamID
     * @param bool $isCustomID Whether the provided input is a custom Steam ID || Optional if session has userSteamID
     * @return \Illuminate\Http\Client\Response
     */
    public function fetchPlayerSummary(string $input = '', bool $isCustomID = false)
    {
        // Only sanitize input if session does not already have userSteamID
        $userSteamID = session()->get('userSteamID') ?? $this->sanitizeInput($input, $isCustomID);

        // Fetch and return player object
        return Http::get($this->numericIDendpoint, [
            'key' => config('steam.key'),
            'steamids' => $userSteamID,
        ]);
    }

    /**
     * Sanitize input
     */
    public function sanitizeInput(string $input, bool $isCustomID): string
    {
        $input = trim($input);

        // Remove everything from the input except for the content after the last '/'. 
        // This will get the ID, no matter what format is submitted
        $input = preg_replace('/.*\/([^\/]+)\/?$/', '$1', $input);

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
            'key' => config('steam.key'),
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
     * @return int
     */
    public function getGameAmount()
    {
        $data = $this->fetchOwnedGames();

        Log::info('Owned games data', ['data' => $data]);

        if (is_array($data)) return $data['game_count'] ?? 0;

        return 0;
    }

    /**
     * Get an array of owned game IDs.
     *
     * @return array
     */
    public function getGameIDs()
    {
        $data = $this->fetchOwnedGames();

        Log::info('Owned games data', ['data' => $data]);

        $games = $data['games'] ?? [];
        return array_map(fn($game) => $game['appid'], $games);
    }

    /**
     * Fetch owned games from the Steam API.
     *
     * @return array|null The `response` array from Steam on success, or null on failure
     */
    private function fetchOwnedGames()
    {
        $steamid = session()->get('userSteamID');

        if (empty($steamid)) {
            return null;
        }

        $params = [
            'key' => config('steam.key'),
            'steamid' => $steamid,
            'include_appinfo' => false,
            'include_played_free_games' => true,
        ];

        try {
            $response = Http::get($this->ownedGamesEndpoint, $params);

            if ($response->successful()) {
                return $response->json('response') ?? [];
            }
        } catch (\Throwable $e) {
            Log::warning('fetchOwnedGames failed: ' . $e->getMessage());
        }

        return null;
    }
}