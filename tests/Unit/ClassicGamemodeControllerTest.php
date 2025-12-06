<?php

namespace Tests\Unit;

use App\Http\Controllers\ClassicGamemodeController;
use App\Services\HintService;
use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\TestCase;

class ClassicGamemodeControllerTest extends TestCase
{
    /**
     * Tests successful hint generation when session contains valid games
     * @return void
     */
    public function testIndexReturnsHintsForValidSession(): void
    {
        $games = [
            ['id' => 123, 'name' => 'Test Game', 'cover_url' => '', 'playtime' => 0],
        ];

        $hints = ['easy' => ['hint_name' => 'foo', 'needed_data_keys' => []]];
        $dataForHints = ['easy' => ['hint_name' => 'foo', 'needed_data_keys' => [], 'data' => []]];

        $hintServiceMock = $this->createMock(HintService::class);
        $hintServiceMock->method('getRandomHints')->willReturn($hints);
        $hintServiceMock->method('getDataForHints')->with($hints, $games[0])->willReturn($dataForHints);

        // Create a small test double to override session access and inject games
        $controller = new class($hintServiceMock, $games) extends ClassicGamemodeController {
            private array $gamesOverride;

            public function __construct(HintService $hintService, array $games)
            {
                parent::__construct($hintService);
                $this->gamesOverride = $games;
            }

            protected function getAllGamesFromSession(): array
            {
                return $this->gamesOverride;
            }
        };

        $response = $controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($dataForHints, $response->getData(true));
    }

    /**
     * Tests 404 when session is empty
     * @return void
     */
    public function testIndexReturns404WhenSessionEmpty(): void
    {
        $hintServiceMock = $this->createMock(HintService::class);

        $controller = new class($hintServiceMock) extends ClassicGamemodeController {
            public function __construct(HintService $hintService)
            {
                parent::__construct($hintService);
            }

            protected function getAllGamesFromSession(): array
            {
                return [];
            }
        };

        $response = $controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['error' => 'No games found in session'], $response->getData(true));
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Tests 404 when no valid games exist in session
     * @return void
     */
    public function testIndexReturns404WhenNoValidGames(): void
    {
        $hintServiceMock = $this->createMock(HintService::class);

        $controller = new class($hintServiceMock) extends ClassicGamemodeController {
            public function __construct(HintService $hintService)
            {
                parent::__construct($hintService);
            }

            protected function getAllGamesFromSession(): array
            {
                return [
                    ['id' => null, 'name' => null],
                    ['id' => '', 'name' => ''],
                ];
            }
        };

        $response = $controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['error' => 'No valid games found in session'], $response->getData(true));
        $this->assertEquals(404, $response->getStatusCode());
    }
}
