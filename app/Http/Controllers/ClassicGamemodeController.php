<?php

namespace App\Http\Controllers;

use App\Services\HintService;

class ClassicGamemodeController extends Controller
{
    public function __construct(
        private HintService $hintService
    ) {}

    /**
     * Get a random game and its hints for the classic gamemode.
     */
    public function index(): array
    {
        $allGames = session('allGames', []);

        if (empty($allGames)) {
            return ['error' => 'No games found in session'];
        }

        // Get random game from session
        $randomGame = $allGames[array_rand($allGames)];

        // Get random hints (one per difficulty)
        $hints = $this->hintService->getRandomHints();

        // Fetch data for those hints
        return $this->hintService->getDataForHints($hints, $randomGame);
    }
}