<?php

namespace App\Services;

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
        $avg = $this->getAvgPlaytimeMinutes($games);
        $median = $this->getMedianPlaytimeMinutes($games);
        $top = $this->getTopGames($games, $topN);
        $playedPercentage = $this->getPlayedPercentage($games);

        return [
            'game_count' => $total,
            'game_ids' => $ownedGameIDs,
            'total_playtime_minutes' => $totalPlaytime,
            'avg_playtime_minutes' => $avg,
            'median_playtime_minutes' => $median,
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
     * @return float
     */
    public function getAvgPlaytimeMinutes(array $games): float
    {
        $total = $this->getTotalPlaytimeMinutes($games);
        $count = $this->getGameCount($games);
        return $count > 0 ? ($total / $count) : 0.0;
    }

    /**
     * Median playtime (minutes) across owned games.
     *
     * @param array $games
     * @return float
     */
    public function getMedianPlaytimeMinutes(array $games): float
    {
        $playtimes = [];
        foreach ($games as $game) {
            $playtimes[] = (int) ($game['playtime_forever'] ?? 0);
        }

        sort($playtimes, SORT_NUMERIC);
        $count = count($playtimes);
        if ($count === 0) {
            return 0.0;
        }

        $mid = intdiv($count, 2);
        if ($count % 2 === 0) {
            return ($playtimes[$mid - 1] + $playtimes[$mid]) / 2;
        }

        return (float) $playtimes[$mid];
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
            ];
        }

        return $result;
    }

    /**
     * Fraction of games that have playtime > 0 (0..1)
     *
     * @param array $games
     * @return float
     */
    public function getPlayedPercentage(array $games): float
    {
        $played = 0;
        foreach ($games as $game) {
            if (((int) ($game['playtime_forever'] ?? 0)) > 0) {
                $played++;
            }
        }

        $total = $this->getGameCount($games);
        return $total > 0 ? ($played / $total) : 0.0;
    }
}
