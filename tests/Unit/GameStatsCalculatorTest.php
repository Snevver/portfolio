<?php

namespace Tests\Unit;

use App\Services\GameStatsCalculator;
use PHPUnit\Framework\TestCase;

class GameStatsCalculatorTest extends TestCase
{
    // Initialize the GameStatsCalculator instance
    private GameStatsCalculator $calculator;

    // Set up before each test
    protected function setUp(): void
    {
        $this->calculator = new GameStatsCalculator();
    }

    /**
     * Test the getGameCount method
     * @return void
     */
    public function testGetGameCount(): void
    {
        // Empty array: edge case
        $this->assertEquals(0, $this->calculator->getGameCount([]));

        // Single game
        $this->assertEquals(1, $this->calculator->getGameCount([['appid' => 1]]));

        // Multiple games
        $this->assertEquals(3, $this->calculator->getGameCount([
            ['appid' => 1],
            ['appid' => 2],
            ['appid' => 3]
        ]));
    }

    /**
     * Test the getGameIds method
     * @return void
     */
    public function testGetGameIds(): void
    {
        $games = [
            ['appid' => 10],
            ['appid' => 20],
            ['name' => null], // Missing appid: edge case
            ['appid' => 30],
        ];

        $this->assertEquals([10, 20, 30], $this->calculator->getGameIds($games));
    }

    /**
     * Test the getTotalPlaytimeMinutes method
     * @return void
     */
    public function testGetTotalPlaytimeMinutes(): void
    {
        $games = [
            ['playtime_forever' => 60],
            ['playtime_forever' => 120],
            ['name' => null], // Missing playtime: edge case
            ['playtime_forever' => 30],
        ];

        $this->assertEquals(210, $this->calculator->getTotalPlaytimeMinutes($games));
    }

    /**
     * Test the getAveragePlaytimeMinutes method
     * @return void
     */
    public function testGetAveragePlaytimeMinutes(): void
    {
        // Empty array: edge case
        $this->assertEquals(0.0, $this->calculator->getAveragePlaytimeMinutes([]));

        // Single game
        $this->assertEquals(60.0, $this->calculator->getAveragePlaytimeMinutes([['playtime_forever' => 60]]));

        // Multiple games
        $games = [
            ['playtime_forever' => 60],
            ['playtime_forever' => 120],
            ['playtime_forever' => 30],
        ];
        $this->assertEquals(70.0, $this->calculator->getAveragePlaytimeMinutes($games));
    }

    /**
     * Test the getTopGames method
     * @return void
     */
    public function testGetTopGames(): void
    {
        $games = [
            ['appid' => 1, 'name' => 'Game A', 'playtime_forever' => 30],
            ['appid' => 2, 'name' => 'Game B', 'playtime_forever' => 60],
            ['appid' => 3, 'name' => 'Game C', 'playtime_forever' => 10],
            ['appid' => 4, 'name' => 'Game D', 'playtime_forever' => 50],
            ['appid' => 5, 'name' => 'Game E', 'playtime_forever' => 40],
        ];

        $top3 = $this->calculator->getTopGames($games, 3);

        $this->assertCount(3, $top3);
        $this->assertEquals(2, $top3[0]['appid']); // Game B, 60
        $this->assertEquals(4, $top3[1]['appid']); // Game D, 50
        $this->assertEquals(5, $top3[2]['appid']); // Game E, 40
    }

    public function testGetPlayedPercentage(): void
    {
        
        // None played: edge case
        $games = [
            ['playtime_forever' => 0],
            ['playtime_forever' => 0],
        ];
        $this->assertEquals(0.0, $this->calculator->getPlayedPercentage($games));

        // All played
        $games = [
            ['playtime_forever' => 10],
            ['playtime_forever' => 20],
        ];
        $this->assertEquals(100.0, $this->calculator->getPlayedPercentage($games));

        // Partial
        $games = [
            ['playtime_forever' => 10],
            ['playtime_forever' => 0],
            ['playtime_forever' => 20],
        ];
        $this->assertEquals(round((2/3) * 100, 2) , $this->calculator->getPlayedPercentage($games));
    }

    public function testComputeAll(): void
    {
        $games = [
            ['appid' => 1, 'name' => 'Game A', 'playtime_forever' => 30],
            ['appid' => 2, 'name' => 'Game B', 'playtime_forever' => 60],
            ['appid' => 3, 'name' => 'Game C', 'playtime_forever' => 9],
        ];

        $result = $this->calculator->computeAll($games, 2);

        $this->assertEquals(3, $result['game_count']);
        $this->assertEquals([1, 2, 3], $result['game_ids']);
        $this->assertEquals(99, $result['total_playtime_minutes']);
        $this->assertEquals(33, $result['average_playtime_minutes']);
        $this->assertCount(2, $result['top_games']);
        $this->assertEquals(2, $result['top_games'][0]['appid']); // Game B
        $this->assertEquals(1, $result['top_games'][1]['appid']); // Game A
        $this->assertEquals(100.0, $result['played_percentage']);
    }
}