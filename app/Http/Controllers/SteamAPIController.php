<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SteamAPIClient;
use App\Services\SteamIdentityService;
use App\Services\SteamStatsService;
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
    public function validateUser(Request $request, SteamIdentityService $identity, SteamAPIClient $client, SteamStatsService $stats)
    {
        // Validate the request
        $request->validate([
            'userSteamID' => 'required|string',
            'isCustomID' => 'required|boolean'
        ]);

        // Remove all session keys except a small preserve list (keeps `_token` so we avoid 419s).
        $allKeys = array_keys($request->session()->all());
        $preserve = ['_token'];
        $keysToForget = array_diff($allKeys, $preserve);
        if (!empty($keysToForget)) {
            $request->session()->forget($keysToForget);
        }

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
                            // Compute stats via the dedicated service
                            $ownedStats = $stats->getOwnedGamesStats($userSteamID, 5);

                            // Store game stats and account creation date in session. account creation is derived
                            // from the player payload and normalized by the stats service.
                            $timeCreated = $stats->getAccountCreationDate($player);

                            $request->session()->put([
                                'userSteamID' => $userSteamID,
                                'publicProfile' => $isPublicProfile,
                                'profileURL' => $player['avatarfull'] ?? null,
                                'username' => $player['personaname'] ?? null,
                                'timeCreated' => $timeCreated,
                                'totalGamesOwned' => $ownedStats['game_count'] ?? 0,
                                'gameIDsOwned' => $ownedStats['game_ids'] ?? [],
                                'totalPlaytimeMinutes' => $ownedStats['total_playtime_minutes'] ?? 0,
                                'avgPlaytimeMinutes' => $ownedStats['avg_playtime_minutes'] ?? 0.0,
                                'medianPlaytimeMinutes' => $ownedStats['median_playtime_minutes'] ?? 0.0,
                                'topGames' => $ownedStats['top_games'] ?? [],
                                'playedPercentage' => $ownedStats['played_percentage'] ?? 0.0,
                            ]);
                        } catch (\Throwable $exception) {
                            Log::error('Failed to write session data', [
                                'exception' => $exception->getMessage(),
                            ]);
                        }
                    }
                }
            }

            return response()->json($this->getResponseCode($userSteamID, $isPublicProfile));
        } catch (\Throwable $e) {
            Log::error('validateUser error: ' . $e->getMessage());
            return response()->json(1);
        }
    }

    /**
     * Determine the response code based on player data and profile visibility.
     *
     * @param string|null $userSteamID Numeric SteamID
     * @param bool $isPublicProfile Whether the profile is public
     * @return int Response code (1: invalid, 2: private, 3: public)
     */
    private function getResponseCode(?string $userSteamID = null, ?bool $isPublicProfile = false): int
    {
        if (!$userSteamID) {
            return self::RESPONSE_INVALID;
        }

        if (!$isPublicProfile) {
            return self::RESPONSE_PRIVATE;
        }

        return self::RESPONSE_PUBLIC;
    }
}