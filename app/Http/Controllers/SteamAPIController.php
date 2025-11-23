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

        // Initialize return vars
        $userSteamID = null;
        $isAvailable = false;

        // Fetch player summary from Steam API
        try {
            $response = $steam->fetchPlayerSummary($request->userSteamID, $request->isCustomID);

            if ($response->successful()) {
                $json = $response->json();
                $player = $json['response']['players'][0] ?? null;

                if ($player) {
                    $userSteamID = $player['steamid'] ?? null;
                    $isAvailable = ($player['communityvisibilitystate'] ?? null) === 3 && $userSteamID !== null;

                    // Store userSteamID in server session when the profile exists and is available (public)
                    if ($userSteamID !== null && $isAvailable) {
                        try {
                            $request->session()->put('userSteamID', $userSteamID);
                        } catch (\Throwable $_) {
                            Log::error('Failed to write session key', ['exception' => $_ instanceof \Throwable ? $_->getMessage() : (string)$_]);
                        }
                    }
                }
            }

            // Return the standard JSON shape
            return response()->json([
                'userSteamID' => $userSteamID,
                'isAvailable' => $isAvailable,
            ]);
        } catch (\Throwable $e) {
            // ERROR: Log and return 500
            Log::error('validateUser error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'userSteamID' => null,
                'isAvailable' => false
            ], 500);
        }
    }
}
