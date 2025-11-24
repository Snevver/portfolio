<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

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
        // Use Laravel session helper to check for stored Steam ID; sanitize and store if missing
        if (!session()->has('userSteamID')) {
            $userSteamID = $this->sanitizeInput($input, $isCustomID);
            session(['userSteamID' => $userSteamID]);
        } else {
            $userSteamID = session('userSteamID');
        }

        return Http::get($this->numericIDendpoint, [
                'key' => $this->apiKey,
                'steamids' => $userSteamID,
            ]);
    }

    /**
     * Transform various input formats into a sanitized Steam ID.
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
            'include_appinfo' => true,
            'include_played_free_games' => true,
        ];

        try {
            $response = Http::timeout(10)
                ->retry(2, 100)
                ->get($this->ownedGamesEndpoint, $params);

            if ($response->successful()) {
                Log::info($response->json());
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

    /**
     * Compute a set of stats from the owned-games payload that the
     * frontend can use.
     *
     * Returns an array with keys:
     * - `game_count` int
     * - `total_playtime_minutes` int
     * - `avg_playtime_minutes` float
     * - `median_playtime_minutes` float
     * - `top_games` array of top N games with keys `appid`, `name`, `playtime_forever`
     * - `played_percentage` float (0..1)
     *
     * @param string $steamid
     * @param int $topN
     * @return array
     */
    public function getOwnedGamesStats(string $steamid, int $topN = 5): array
    {
        // Get all owned games data
        $data = $this->fetchOwnedGamesData($steamid);

        // Initialize stats
        $games = $data['games'] ?? [];
        $total = $data['game_count'] ?? count($games);

        $playtimes = [];
        $totalPlaytime = 0;
        $playedCount = 0;

        // Get total playtime
        foreach ($games as $g) {
            $pt = (int) ($g['playtime_forever'] ?? 0);
            $playtimes[] = $pt;
            $totalPlaytime += $pt;

            if ($pt > 0) {
                $playedCount++;
            }
        }

        // Average
        $avg = $total > 0 ? ($totalPlaytime / $total) : 0.0;

        // Median
        sort($playtimes, SORT_NUMERIC);
        $median = 0.0;
        $count = count($playtimes);
        if ($count > 0) {
            $mid = intdiv($count, 2);
            if ($count % 2 === 0) {
                $median = ($playtimes[$mid - 1] + $playtimes[$mid]) / 2;
            } else {
                $median = $playtimes[$mid];
            }
        }

        // Top N games by playtime
        usort($games, function ($a, $b) {
            return ((int) ($b['playtime_forever'] ?? 0)) <=> ((int) ($a['playtime_forever'] ?? 0));
        });

        $top = array_slice($games, 0, $topN);
        $topSimplified = [];
        foreach ($top as $t) {
            $topSimplified[] = [
                'appid' => $t['appid'] ?? null,
                'name' => $t['name'] ?? null,
                'playtime_forever' => (int) ($t['playtime_forever'] ?? 0),
            ];
        }

        return [
            'game_count' => $total,
            'total_playtime_minutes' => $totalPlaytime,
            'avg_playtime_minutes' => $avg,
            'median_playtime_minutes' => $median,
            'top_games' => $topSimplified,
            'played_percentage' => $total > 0 ? ($playedCount / $total) : 0.0,
        ];
    }

    /**
     * Normalize a Steam account creation timestamp into a payload
     * that's easy for the frontend to consume.
     *
     * Example return:
     * [
     *   'timestamp' => 1441313456,
     *   'iso8601' => '2015-09-03T12:30:56+00:00',
     *   'human' => 'Sep 3, 2015, 12:30 PM',
     * ]
     *
     * @param int|array $input
     * @return array
     */
    public function getAccountCreationDate($input): array
    {
        if (is_array($input)) {
            $timestamp = isset($input['timeCreated']) ? (int) $input['timeCreated'] : null;
        } else {
            $timestamp = is_numeric($input) ? (int) $input : null;
        }

        if (empty($timestamp) || $timestamp <= 0) {
            return [
                'timestamp' => null,
                'iso8601' => null,
                'human' => null,
            ];
        }

        $dt = Carbon::createFromTimestampUTC($timestamp);

        return [
            'timestamp' => $timestamp,
            'iso8601' => $dt->toIso8601String(),
            'human' => $dt->toDayDateTimeString(),
        ];
    }
}