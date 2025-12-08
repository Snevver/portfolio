<?php

namespace App\Http\Controllers;

use App\Services\HintService;
use Illuminate\Http\JsonResponse;

class ClassicGamemodeController extends Controller
{
    public function __construct(
        private HintService $hintService
    ) {}

    /**
     * Get a random game and its hints for the classic gamemode.
     */
    public function index(): JsonResponse
    {
        $allGames = $this->getAllGamesFromSession();

        if (empty($allGames)) {
            return new JsonResponse(['error' => 'No games found in session'], 404);
        }

        // Filter games to only those with valid id and name
        $validGames = array_filter($allGames, fn($game) => !empty($game['id']) && !empty($game['name']));

        if (empty($validGames)) {
            return new JsonResponse(['error' => 'No valid games found in session'], 404);
        }

        // Get random valid game from session
        $randomGame = $validGames[array_rand($validGames)];

        // Get random hints (one per difficulty)
        $hints = $this->hintService->getRandomHints();

        // Fetch data for those hints
        return new JsonResponse($this->hintService->getDataForHints($hints, $randomGame));
    }

    /**
     * Wrapper for fetching games from session so it can be overridden in tests.
     */
    protected function getAllGamesFromSession(): array
    {
        return session('allGames', []);
    }
}