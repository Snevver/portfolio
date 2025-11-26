<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SteamIdentityService
{
    private SteamAPIClient $client;

    /**
     * Create a new SteamIdentityService.
     *
     * @param SteamAPIClient $client
     */
    public function __construct(SteamAPIClient $client)
    {
        $this->client = $client;
    }

    /**
     * Sanitize input and resolve vanity names when needed.
     *
     * @param string $input Raw input (URL, numeric ID, or custom ID)
     * @param bool $isCustomID Whether the provided input should be treated as a custom ID
     * @return string|null Numeric SteamID or null if resolution fails
     */
    private function sanitizeInput(string $input, bool $isCustomID): ?string
    {
        $input = trim($input);

        if (preg_match('#/([^/]+)/?$#', $input, $matches)) {
            $input = $matches[1];
        }

        if ($isCustomID) {
            return $this->client->resolveVanityUrl($input);
        }

        return $input;
    }

    /**
     * Store the resolved SteamID into session.
     *
     * This method stores the resolved SteamID into session under key `userSteamID`.
     *
     * @param string $input Raw input (URL, numeric ID, or custom ID)
     * @param bool $isCustomID Whether the provided input should be treated as a custom ID
     * @return string|null Numeric SteamID or null if resolution fails
     */
    public function storeSessionSteamId(string $input, bool $isCustomID): ?string
    {
        $resolved = $this->sanitizeInput($input, $isCustomID);
        if ($resolved) {
            session(['userSteamID' => $resolved]);
        } else {
            Log::warning('Failed to resolve Steam identity', ['input' => $input]);
        }

        return $resolved;
    }
}