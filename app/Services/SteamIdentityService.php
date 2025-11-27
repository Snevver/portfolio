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
    public function sanitizeInput(string $input, bool $isCustomID): ?string
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
}