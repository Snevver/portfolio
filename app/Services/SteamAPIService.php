<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SteamAPIService
{
    // Steam Web API endpoints
    private string $numericIDendpoint = 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/';
    private string $customURLEndpoint = 'https://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/';

    /**
     * Fetch player summary from Steam API.
     *
     * @param string $input Raw input (URL, numeric ID, or custom ID)
     * @param bool $isCustomID Whether the provided input is a custom Steam ID
     * @return \Illuminate\Http\Client\Response
     */
    public function fetchPlayerSummary(string $input, bool $isCustomID)
    {
        $input = trim($input);

        // Remove everything from the input except for the content after the last '/'. 
        // This will get the ID, no matter what format is submitted
        $input = preg_replace('/.*\/([^\/]+)\/?$/', '$1', $input);

        // If input is a custom id, resolve to numeric SteamID. Otherwise use input directly
        $steamID = $isCustomID ? $this->resolveCustomID($input) : $input;

        // Fetch and return player object
        return Http::get($this->numericIDendpoint, [
            'key' => config('steam.key'),
            'steamids' => $steamID,
        ]);
    }

    /**
     * Resolve a vanity name to a numeric SteamID using Steam's ResolveVanityURL endpoint.
     * Throws an exception if resolution fails.
     *
     * @param string $vanityName
     * @return string
     * @throws \Exception
     */
    private function resolveCustomID(string $vanityName): string
    {
        $response = Http::get($this->customURLEndpoint, [
            'key' => config('steam.key'),
            'vanityurl' => $vanityName,
        ]);

        if ($response->successful() && $response->json('response.success') === 1) {
            return $response->json('response.steamid');
        }

        throw new \Exception("Failed to resolve vanity '{$vanityName}' to a numeric SteamID.");
    }
}
