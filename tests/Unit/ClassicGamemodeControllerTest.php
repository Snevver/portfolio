<?php

namespace Tests\Unit;

use App\Http\Controllers\ClassicGamemodeController;
use App\Services\Hints\HintService;
use App\Services\Game\ValidGameService;
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
            ['id' => 123, 'name' => 'Test Game'],
        ];

        $hints = ['easy' => ['hint_name' => 'foo', 'needed_data_keys' => []]];
        $dataForHints = ['easy' => ['hint_name' => 'foo', 'needed_data_keys' => [], 'data' => []]];

        $hintServiceMock = $this->createMock(HintService::class);
        $hintServiceMock->method('getRandomHints')->willReturn($hints);
        $hintServiceMock->method('getDataForHints')->with($hints, $games[0])->willReturn($dataForHints);

        $validGameServiceMock = $this->createMock(ValidGameService::class);
        $validGameServiceMock->method('getValidGamesFromSession')->willReturn($games);

        $controller = new ClassicGamemodeController($hintServiceMock, $validGameServiceMock);

        $response = $controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $expected = [
            'hints' => $hints,
            'hints_data' => $dataForHints,
            'game' => [
                'id' => 123,
                'name' => 'Test Game',
            ],
        ];

        $this->assertEquals($expected, $response->getData(true));
    }

    /**
     * Tests 404 when session is empty
     * @return void
     */
    public function testIndexReturns404WhenSessionEmpty(): void
    {
        $hintServiceMock = $this->createMock(HintService::class);
        $validGameServiceMock = $this->createMock(ValidGameService::class);
        $validGameServiceMock->method('getValidGamesFromSession')->willReturn([]);

        $controller = new ClassicGamemodeController($hintServiceMock, $validGameServiceMock);

        $response = $controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['error' => 'No valid games found in session'], $response->getData(true));
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Tests 404 when no valid games exist in session
     * @return void
     */
    public function testIndexReturns404WhenNoValidGames(): void
    {
        $hintServiceMock = $this->createMock(HintService::class);
        $validGameServiceMock = $this->createMock(ValidGameService::class);
        $validGameServiceMock->method('getValidGamesFromSession')->willReturn([]);

        $controller = new ClassicGamemodeController($hintServiceMock, $validGameServiceMock);

        $response = $controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['error' => 'No valid games found in session'], $response->getData(true));
        $this->assertEquals(404, $response->getStatusCode());
    }
}
