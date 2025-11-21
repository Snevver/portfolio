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

            // AI created this if statement. It basically makes sure the value is a real,
            // usable HTTP response (or converts a promise into one) before we call ->successful().
            // I honestly don't fully understand but it works.
            if (!is_object($response) || !method_exists($response, 'successful')) {
                if (is_object($response) && method_exists($response, 'wait')) {
                    $inner = $response->wait();

                    if ($inner instanceof PsrResponseInterface) {
                        $response = new HttpClientResponse($inner);
                    } else {
                        // Unexpected inner type; throw to be handled below
                        throw new \Exception('Unexpected inner response type from Steam API promise.');
                    }
                } else {
                    throw new \Exception('Unexpected response type from SteamAPIService.');
                }
            }

            if ($response->successful()) {
                $json = $response->json();
                $player = $json['response']['players'][0] ?? null;
                $isAvailable = $json['response']['players'][0]['communityvisibilitystate'] === 3;

                // Return the steadID (numeric) id the user exists
                if ($player) {
                    $userSteamID = $player['steamid'] ?? null;
                    return response()->json([
                        'userSteamID' => $userSteamID,
                        'isAvailable' => $isAvailable
                    ]);
                }

                return response()->json([
                    'userSteamID' => null,
                    'isAvailable' => false
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
