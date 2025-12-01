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
     *   average_playtime_minutes:int,
     *   top_games:array,
     *   played_percentage:float
     * }
     */
    public function getOwnedGamesStats(string $steamid, int $topN = 5): array
    {
        $data = $this->client->fetchOwnedGames($steamid);
        $games = $data['games'] ?? [];

        // Use the calculator service to compute individual stats
        return $this->calculator->computeAll($games, $topN);
    }

    /**
     * Normalize a Steam account creation timestamp into a structured payload.
     * Accepts an integer unix timestamp.
     * Returns the formatted date of creation and an account age breakdown.
     *
     * @param int|null $timestamp Unix timestamp or null
     * @return array{date:string|null, age:array|null}
     */
    public function getAccountAgeAndCreationDate($timestamp): array
    {
        return [
            'date' => $this->getCreationDate($timestamp),
            'age' => $this->getAccountAge($timestamp),
        ];
    }

    /**
     * Get the formatted creation date from a timestamp.
     *
     * @param int|null $timestamp Unix timestamp or null
     * @return string|null
     */
    private function getCreationDate($timestamp): ?string
    {
        if (empty($timestamp) || $timestamp <= 0) {
            return null;
        }

        $dt = Carbon::createFromTimestampUTC($timestamp);

        return $dt->format('F j, Y');
    }

    /**
     * Get the account age breakdown from a timestamp.
     *
     * @param int|null $timestamp Unix timestamp or null
     * @return array|null
     */
    private function getAccountAge($timestamp): ?array
    {
        if (empty($timestamp) || $timestamp <= 0) {
            return null;
        }

        $dt = Carbon::createFromTimestampUTC($timestamp);
        $now = Carbon::now('UTC');
        $diff = $dt->diff($now);

        return [
            'years' => $diff->y,
            'months' => $diff->m,
            'days' => $diff->d,
        ];
    }
}