<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SteamAPIService;
use Illuminate\Support\Facades\Log;

class SteamAPIController extends Controller
{
    /**
     * Get the users basic info
     *
     * Returns JSON with:
     * - userSteamID (string|null)
     * - isPublicProfile (bool)
     *
     * @param Request $request
     * @param SteamAPIService $steam
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateUser(Request $request, SteamAPIService $steam)
    {
        // Validate the request
        $request->validate([
            'userSteamID' => 'required|string',
            'isCustomID' => 'required|boolean'
        ]);

        // Fetch player summary from Steam API
        try {
            $response = $steam->fetchPlayerSummary($request->userSteamID, $request->isCustomID);

            if ($response->successful()) {
                $json = $response->json();
                $player = $json['response']['players'][0] ?? null;

                if ($player) {
                    $userSteamID = $player['steamid'] ?? null;
                    $isPublicProfile = ($player['communityvisibilitystate'] ?? 0) === 3;

                    if ($userSteamID && $isPublicProfile) {
                        try {
                            $ownedStats = $steam->getOwnedGamesStats($userSteamID, 5);

                            // Store basic data and stats in session
                            $request->session()->put([
                                'userSteamID' => $userSteamID,
                                'publicProfile' => $isPublicProfile,
                                'profileURL' => $player['avatarfull'] ?? null,
                                'username' => $player['personaname'] ?? null,
                                'timeCreated' => $steam->getAccountCreationDate($player['timecreated'] ?? null),
                                'totalGamesOwned' => $ownedStats['game_count'] ?? 0,
                                'totalPlaytimeMinutes' => $ownedStats['total_playtime_minutes'] ?? 0,
                                'avgPlaytimeMinutes' => $ownedStats['avg_playtime_minutes'] ?? 0.0,
                                'medianPlaytimeMinutes' => $ownedStats['median_playtime_minutes'] ?? 0.0,
                                'topGames' => $ownedStats['top_games'] ?? [],
                                'playedPercentage' => $ownedStats['played_percentage'] ?? 0.0,
                            ]);
                        } catch (\Throwable $exception) {
                            Log::error('Failed to write session data', [
                                'exception' => $exception->getMessage()
                            ]);
                        }
                    }
                }
            }

            // Return the standard JSON shape
            return response()->json([
                'userSteamID' => $userSteamID,
                'isPublicProfile' => $isPublicProfile,
            ]);
        } catch (\Throwable $e) {
            // ERROR: Log and return 500
            Log::error('validateUser error: ' . $e->getMessage());
            return response()->json([
                'userSteamID' => null,
                'isPublicProfile' => false
            ], 500);
        }
    }
}