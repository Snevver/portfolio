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

    /**
     * Get the meaning of a Steam persona state code.
     *
     * @param int $personaState Numeric persona state code
     * @return string Human-readable persona state
     */
    public function getPersonaStateMeaning(int $personaState): string
    {
        return match ($personaState) {
            0 => 'Offline',
            1 => 'Online',
            2 => 'Busy',
            3 => 'Away',
            4 => 'Snooze',
            5 => 'Looking to trade',
            6 => 'Looking to play',
            default => 'Unknown',
        };
    }
}