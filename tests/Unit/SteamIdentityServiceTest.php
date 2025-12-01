<?php

namespace Tests\Unit\Services;

use App\Services\SteamAPIClient;
use App\Services\SteamIdentityService;
use PHPUnit\Framework\TestCase;

class SteamIdentityServiceTest extends TestCase
{
    private $clientMock;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->clientMock = $this->createMock(SteamAPIClient::class);
        $this->service = new SteamIdentityService($this->clientMock);
    }

    public function testGetPersonaStateMeaning(): void
    {
        $this->assertSame('Offline', $this->service->getPersonaStateMeaning(0));
        $this->assertSame('Online', $this->service->getPersonaStateMeaning(1));
        $this->assertSame('Busy', $this->service->getPersonaStateMeaning(2));
        $this->assertSame('Away', $this->service->getPersonaStateMeaning(3));
        $this->assertSame('Snooze', $this->service->getPersonaStateMeaning(4));
        $this->assertSame('Looking to trade', $this->service->getPersonaStateMeaning(5));
        $this->assertSame('Looking to play', $this->service->getPersonaStateMeaning(6));
        $this->assertSame('Unknown', $this->service->getPersonaStateMeaning(999));
    }

    public function testSanitizeInput(): void
    {
        // Test with vanity URL (custom name)
        $this->clientMock
            ->method('resolveVanityUrl')
            ->with('customname')
            ->willReturn('12345678901234567');

        $result = $this->service->sanitizeInput('https://steamcommunity.com/id/customname/', true);
        $this->assertSame('12345678901234567', $result);

        // Test with numeric ID (no API call needed)
        $result = $this->service->sanitizeInput('76561198000000000', false);
        $this->assertSame('76561198000000000', $result);
    }
}