<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Validation\ValidationResponse;

class ValidationResponseTest extends TestCase
{
    /**
     * Tests the determine method for various scenarios
     * @return void
     */
    public function testDetermineWithEmptySteamId(): void
    {
        $this->assertEquals(1, ValidationResponse::determine(null));
        $this->assertEquals(1, ValidationResponse::determine(''));
    }

    /**
     * Tests the determine method for private profile
     * @return void
     */
    public function testDetermineWithPrivateProfile(): void
    {
        $this->assertEquals(2, ValidationResponse::determine('12345', false));
    }

    /**
     * Tests the determine method for public profile
     * @return void
     */
    public function testDetermineWithPublicProfile(): void
    {
        $this->assertEquals(3, ValidationResponse::determine('12345', true));
    }
}