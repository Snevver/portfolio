<?php

namespace App\Services\Game;

class ValidGameService
{
    /**
     * Retrieve valid games from the session.
     *
     * Returns an array of games where each item contains at least `id` and `name`.
     * If there are no games in session this returns an empty array.
     *
     * @return array<int, array>
     */
    public function getValidGamesFromSession(): array
    {
        $allGames = $this->getAllGamesFromSession();

        if (!is_array($allGames) || empty($allGames)) return [];

        // Keep only entries with a non-empty id and name, normalize array keys
        $validGames = array_values(array_filter($allGames, function ($game) {
            return is_array($game)
                && isset($game['id']) && $game['id'] !== ''
                && isset($game['name']) && $game['name'] !== '';
        }));

        return $validGames;
    }

    /**
     * Wrapper for fetching games from session so it can be overridden in tests.
     *
     * @return array
     */
    protected function getAllGamesFromSession(): array
    {
        $games = session('allGames', []);
        return is_array($games) ? $games : [];
    }
}