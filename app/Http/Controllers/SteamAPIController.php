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
     * Returns the numeric userID when found, or null when not found.
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
                            // Store basic data in session
                            $request->session()->put([
                                'userSteamID' => $userSteamID,
                                'publicProfile' => $isPublicProfile,
                                'profileURL' => $player['avatarfull'] ?? null,
                                'username' => $player['personaname'] ?? null,
                                'timeCreated' => $player['timecreated'] ?? null,
                                'totalGamesOwned' => $steam->getGameAmount(),
                                'gameIDs' => $steam->getGameIDs(),
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
            Log::error('validateUser error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'userSteamID' => null,
                'isPublicProfile' => false
            ], 500);
        }
    }
}
