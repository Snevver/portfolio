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
        $allGames = session('allGames', []);

        if (empty($allGames)) {
            return response()->json(['error' => 'No games found in session'], 404);
        }

        // Get random game from session
        $randomGame = $allGames[array_rand($allGames)];

        // Get random hints (one per difficulty)
        $hints = $this->hintService->getRandomHints();

        // Fetch data for those hints
        return response()->json($this->hintService->getDataForHints($hints, $randomGame));
    }
}