<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
class GameStatsCalculator
{
    /**
     * Convenience: compute all stats in one call.
     *
     * @param array $games Array of games data
     * @param int $topN Number of top games to return
     * @return array All computed game statistics
     */
    public function computeAll(array $games, int $topN = 5): array
    {
        $total = $this->getGameCount($games);
        $ownedGameIDs = $this->getGameIds($games);
        $totalPlaytime = $this->getTotalPlaytimeMinutes($games);
        $average = $this->getAveragePlaytimeMinutes($games);
        $top = $this->getTopGames($games, $topN);
        $playedPercentage = $this->getPlayedPercentage($games);

        return [
            'game_count' => $total,
            'game_ids' => $ownedGameIDs,
            'total_playtime_minutes' => $totalPlaytime,
            'average_playtime_minutes' => $average,
            'top_games' => $top,
            'played_percentage' => $playedPercentage,
        ];
    }

    /**
     * Return number of games.
     *
     * @param array $games
     * @return int
     */
    public function getGameCount(array $games): int
    {
        return count($games);
    }

    /**
     * Extract appids from the raw games payload.
     *
     * @param array $games
     * @return int[]
     */
    public function getGameIds(array $games): array
    {
        $ids = [];
        foreach ($games as $game) {
            if (isset($game['appid'])) {
                $ids[] = (int) $game['appid'];
            }
        }

        return $ids;
    }

    /**
     * Sum playtime_forever for all games (minutes).
     *
     * @param array $games
     * @return int
     */
    public function getTotalPlaytimeMinutes(array $games): int
    {
        $total = 0;
        foreach ($games as $game) {
            $total += (int) ($game['playtime_forever'] ?? 0);
        }

        return $total;
    }

    /**
     * Average playtime per owned game.
     *
     * @param array $games
     * @return int $average
     */
    public function getAveragePlaytimeMinutes(array $games): int
    {
        $total = $this->getTotalPlaytimeMinutes($games);
        $count = $this->getGameCount($games);
        return ($count > 0 ? ($total / $count) : 0);
    }

    /**
     * Return top N games by playtime_forever. Each item contains appid, name, playtime_forever.
     *
     * @param array $games
     * @param int $topN
     * @return array
     */
    public function getTopGames(array $games, int $topN = 5): array
    {
        usort($games, function ($a, $b) {
            return ((int) ($b['playtime_forever'] ?? 0)) <=> ((int) ($a['playtime_forever'] ?? 0));
        });

        $topGames = array_slice($games, 0, $topN);
        $result = [];
        foreach ($topGames as $topGame) {
            $result[] = [
                'appid' => $topGame['appid'] ?? null,
                'name' => $topGame['name'] ?? null,
                'playtime_forever' => (int) ($topGame['playtime_forever'] ?? 0),
                'cover_url' => "https://steamcdn-a.akamaihd.net/steam/apps/{$topGame['appid']}/capsule_616x353.jpg" ?? null,
            ];
        }

        return $result;
    }

    /**
     * Fraction of games that have playtime > 0 (0..100)
     *
     * @param array $games
     * @return int $playedPercentage
     */
    public function getPlayedPercentage(array $games): int
    {
        $total = $this->getGameCount($games);
        
        if ($total === 0) {
            return 0;
        }
        
        $played = 0;
        foreach ($games as $game) {
            if (((int) ($game['playtime_forever'] ?? 0)) > 0) {
                $played++;
            }
        }

        return (int) round(($played / $total) * 100);
    }
}