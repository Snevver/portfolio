<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SteamAPIClient;
use App\Services\SteamIdentityService;
use App\Services\SteamStatsService;
use App\Services\UserSessionService;
use App\Services\ValidationResponse;
use Illuminate\Support\Facades\Log;

/**
 * Steam API controller.
 *
 * Response code constants defined on the class indicate the possible outcomes
 * of the `validateUser` endpoint:
 * - 1: Invalid user (user not found / steamid resolution failed)
 * - 2: Valid user but private profile
 * - 3: Valid user with public profile
 */
class SteamAPIController extends Controller
{
    // Response codes
    private const RESPONSE_INVALID = 1;
    private const RESPONSE_PRIVATE = 2;
    private const RESPONSE_PUBLIC  = 3;

    /**
     * Get the users basic info and saves it to the session.
     *
     * Returns JSON with:
     * - 1 = invalid user
     * - 2 = private profile
     * - 3 = public profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateUser(Request $request, SteamIdentityService $identity, SteamAPIClient $client, SteamStatsService $stats, UserSessionService $userSession)
    {
        // Validate the request
        $request->validate([
            'userSteamID' => 'required|string',
            'isCustomID' => 'required|boolean'
        ]);

        // Clear session keys except a small preserve list (keeps `_token` so we avoid 419s).
        $userSession->clearExceptPreserve();

        // Fetch player summary from Steam API
        try {
            // Resolve and/or store SteamID in session
            $userSteamID = $identity->storeSessionSteamId($request->userSteamID, $request->isCustomID);

            // If resolution failed, return invalid immediately and avoid calling Steam with null
            if (empty($userSteamID)) {
                return response()->json(self::RESPONSE_INVALID);
            }

            $response = $client->fetchPlayerSummary($userSteamID);

            if ($response->successful()) {
                $json = $response->json();
                $player = $json['response']['players'][0] ?? null;
                $publicCommunityVisibilityState = 3;

                if ($player) {
                    $isPublicProfile = ($player['communityvisibilitystate'] ?? 0) === $publicCommunityVisibilityState;

                    if ($userSteamID && $isPublicProfile) {
                        try {
                            $ownedStats = $stats->getOwnedGamesStats($userSteamID, 5);
                            $timeCreated = $stats->getAccountCreationDate($player['timecreated'] ?? null);

                            // Put all relevant user data into the session
                            $userSession->storeUserSession($userSteamID, $player, $ownedStats, $timeCreated);
                        } catch (\Throwable $exception) {
                            Log::error('Failed to write session data', [
                                'exception' => $exception->getMessage(),
                            ]);
                        }
                    }
                }
            }

            return response()->json(ValidationResponse::determine($userSteamID, $isPublicProfile));
        } catch (\Throwable $e) {
            Log::error('validateUser error: ' . $e->getMessage());
            return response()->json(1);
        }
    }
}