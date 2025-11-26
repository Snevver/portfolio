<?php

namespace App\Services;

use Carbon\Carbon;
use App\Services\GameStatsCalculator;

class SteamStatsService
{
    private SteamAPIClient $client;
    private GameStatsCalculator $calculator;

    /**
     * SteamStatsService constructor.
     *
     * @param SteamAPIClient $client HTTP client for Steam API calls
     * @param GameStatsCalculator $calculator Calculator service for game statistics
     */
    public function __construct(SteamAPIClient $client, GameStatsCalculator $calculator)
    {
        $this->client = $client;
        $this->calculator = $calculator;
    }

    /**
     * Compute statistics for a user's owned games.
     *
     * @param string $steamid Numeric SteamID
     * @param int $topN Number of top games to return (defaults to 5)
     * @return array{
     *   game_count:int,
     *   game_ids:int[],
     *   total_playtime_minutes:int,
     *   avg_playtime_minutes:float,
     *   median_playtime_minutes:float,
     *   top_games:array,
     *   played_percentage:float
     * }
     */
    public function getOwnedGamesStats(string $steamid, int $topN = 5): array
    {
        $data = $this->client->fetchOwnedGames($steamid);
        $games = $data['games'] ?? [];

        // Use the calculator service to compute individual stats
        $result = $this->calculator->computeAll($games, $topN);

        return $result;
    }

    /**
     * Normalize a Steam account creation timestamp into a structured payload.
     *
     * @param int|array|null $timestamp Unix timestamp or an array containing ['timeCreated']
     * @return array{timestamp:?int, iso8601:?string, human:?string}
     */
    public function getAccountCreationDate($timestamp): array
    {
        if (empty($timestamp) || $timestamp <= 0) {
            return [
                'timestamp' => null,
                'iso8601' => null,
                'human' => null,
            ];
        }


        $dt = Carbon::createFromTimestampUTC($timestamp);

        // Calculate age parts (years, months, days)
        $now = Carbon::now('UTC');
        $diff = $dt->diff($now);

        $age = [
            'years' => $diff->y,
            'months' => $diff->m,
            'days' => $diff->d,
        ];

        return [
            'timestamp' => $timestamp,
            'iso8601' => $dt->toIso8601String(),
            'human' => $dt->toDayDateTimeString(),
            'age' => $age,
        ];
    }
}
