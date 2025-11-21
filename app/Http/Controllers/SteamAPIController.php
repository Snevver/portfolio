<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SteamAPIService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response as HttpClientResponse;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

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
                    $isAvailable = ($player['communityvisibilitystate'] ?? null) === 3 && $userSteamID !== null;

                    return response()->json([
                        'userSteamID' => $userSteamID,
                        'isAvailable' => $isAvailable,
                    ]);
                }

                return response()->json([
                    'userSteamID' => null,
                    'isAvailable' => false,
                ]);
            }

            return response()->json([
                'userSteamID' => null,
                'isAvailable' => false
            ], 502);
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
