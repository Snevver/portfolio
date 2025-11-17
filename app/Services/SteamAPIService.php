<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SteamAPIService
{
    // Define the Steam API endpoints
    private string $numericIDendpoint = 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/';
    private string $customURLEndpoint = 'https://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/';

    /**
     * Extract Steam ID from various input formats (URL, numeric ID, custom ID)
     * 
     * @param string $input
     * @return array ['id' => string, 'type' => 'numeric'|'custom']
     */
    public function extractSteamID(string $input): array
    {
        // Remove whitespace
        $input = trim($input);

        // Check if it's a Steam profile URL with numeric ID
        if (preg_match('/https?:\/\/steamcommunity\.com\/profiles\/(\d+)\/?/', $input, $matches)) {
            return ['id' => $matches[1], 'userInputType' => 'numeric'];
        }

        // Check if it's a Steam custom URL
        if (preg_match('/https?:\/\/steamcommunity\.com\/id\/([^\/?]+)\/?/', $input, $matches)) {
            return ['id' => $matches[1], 'userInputType' => 'custom'];
        }

        // Check if it's a numeric Steam ID (17 digits starting with 7656119)
        if (preg_match('/^7656119\d{10}$/', $input)) {
            return ['id' => $input, 'userInputType' => 'numeric'];
        }

        // Assume it's a custom ID if it doesn't match the above patterns
        return ['id' => $input, 'userInputType' => 'custom'];
    }

    /**
     * Fetch player summary from Steam API
     * 
     * @param string $input Raw input (URL, numeric ID, or custom ID)
     * @return \Illuminate\Http\Client\Response
     */
    public function fetchPlayerSummary(string $input)
    {
        $steamData = $this->extractSteamID($input);
        $steamID = $steamData['id'];
        $userInputType = $steamData['userInputType'];

        if ($userInputType === 'custom') {
            // Resolve custom URL to numeric SteamID
            $steamID = $this->resolveCustomURL($steamID);
        }

        return Http::get($this->numericIDendpoint, [
            'key' => config('steam.key'),
            'steamids' => $steamID,
        ]);
    }

    /**
     * Resolve custom vanity name to numeric SteamID
     * 
     * @param string $vanityName The custom vanity name (e.g., "mycustomname", not full URL)
     * @return string The numeric Steam ID
     * @throws \Exception
     */
    private function resolveCustomURL(string $vanityName) : string
    {
        $response = Http::get($this->customURLEndpoint, [
            'key' => config('steam.key'),
            'vanityurl' => $vanityName,
        ]);

        if ($response->successful() && $response->json('response.success') === 1) {
            return $response->json('response.steamid');
        }

        throw new \Exception("Failed to resolve custom vanity name '{$vanityName}' to SteamID. Please verify the custom vanity name is correct.");
    }
}
