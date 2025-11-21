<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SteamAPIService;

class SteamAPIController extends Controller
{
    /**
     * Get the users basic info
     * 
     * @param Request $request
     * @param SteamAPIService $steam
     * @return \Illuminate\Http\JsonResponse 204|404
     */
    public function validateUser(Request $request, SteamAPIService $steam)
    {
        // Validate the request
        $request->validate([
            'steamID' => 'required|string',
            'isCustomID' => 'required|boolean'
        ]);

        // Fetch player summary from Steam API
        try {
            $response = $steam->fetchPlayerSummary($request->steamID, $request->isCustomID);

            if ($response->successful()) {
                $json = $response->json();
                $exists = !empty($json['response']['players'][0] ?? null);

                if ($exists) return response('', 204);

                return response('', 404);
            }

            return response('', 404);
        } catch (\Throwable $e) {
            return response('', 404);
        }
    }
}