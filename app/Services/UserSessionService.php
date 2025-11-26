<?php

namespace App\Services;

use Illuminate\Session\Store;

class UserSessionService
{
    private Store $session;

    public function __construct(Store $session)
    {
        $this->session = $session;
    }

    /**
     * Persist user/session data returned from stats/identity lookups.
     *
     * @param string $steamId
     * @param array|null $player
     * @param array $ownedStats
     * @param array|null $timeCreated
     * @return void
     */
    public function storeUserSession(string $steamId, ?array $player, array $ownedStats, ?array $timeCreated): void
    {
        $this->session->put([
            'userSteamID' => $steamId,
            'publicProfile' => ($player['communityvisibilitystate'] ?? 0) === 3,
            'profileURL' => $player['avatarfull'] ?? null,
            'username' => $player['personaname'] ?? null,
            'timeCreated' => $timeCreated,
            'accountAge' => $timeCreated['age'] ?? null,
            'totalGamesOwned' => $ownedStats['game_count'] ?? 0,
            'gameIDsOwned' => $ownedStats['game_ids'] ?? [],
            'totalPlaytimeMinutes' => $ownedStats['total_playtime_minutes'] ?? 0,
            'avgPlaytimeMinutes' => $ownedStats['avg_playtime_minutes'] ?? 0.0,
            'medianPlaytimeMinutes' => $ownedStats['median_playtime_minutes'] ?? 0.0,
            'topGames' => $ownedStats['top_games'] ?? [],
            'playedPercentage' => $ownedStats['played_percentage'] ?? 0.0,
        ]);
    }

    /**
     * Clear all session keys except a small preserve list.
     * Keeps `_token` by default to avoid 419 CSRF errors.
     *
     * @param array $preserve
     * @return void
     */
    public function clearExceptPreserve(array $preserve = ['_token']): void
    {
        $allKeys = array_keys($this->session->all());
        $keysToForget = array_diff($allKeys, $preserve);
        if (!empty($keysToForget)) {
            $this->session->forget($keysToForget);
        }
    }
}
