<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class HintService
{
    public function __construct(
        private HintDataService $dataService
    ) {}

    /**
     * Select one random hint per difficulty level.
     *
     * Reads available hints from registry/hints.json and selects one hint
     * randomly from each difficulty (easy, medium, hard).
     *
     * @return array<string, array{hint_name: string, needed_data_keys: array}>
     * @throws \RuntimeException If hints.json is missing or contains invalid JSON
     */
    public function getRandomHints(): array
    {
        $hintsPath = base_path('registry/hints.json');

        if (!File::exists($hintsPath)) {
            throw new \RuntimeException('Hints configuration file not found: registry/hints.json');
        }

        $hintsContent = File::get($hintsPath);
        $hints = json_decode($hintsContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON in hints.json: ' . json_last_error_msg());
        }

        if (!isset($hints['hints']) || !is_array($hints['hints'])) {
            throw new \RuntimeException('Invalid hints.json structure: missing or invalid "hints" key');
        }

        // Get all hints from all difficulties
        $allHints = [];
        foreach ($hints['hints'] as $difficulty => $hintsInDifficulty) {
            foreach ($hintsInDifficulty as $hintName => $hintData) {
                $allHints[] = [
                    'difficulty' => $difficulty,
                    'hint_name' => $hintName,
                    'needed_data' => $hintData['neededData']
                ];
            }
        }

        // Get 1 random hint from each difficulty
        $hintsByDifficulty = [];
        $difficulties = ['easy', 'medium', 'hard'];

        foreach ($difficulties as $difficulty) {
            $hintsInDifficulty = array_filter($allHints, fn($hint) => $hint['difficulty'] === $difficulty);

            if (empty($hintsInDifficulty)) {
                throw new \RuntimeException("No hints found for difficulty level: $difficulty");
            }

            $randomHint = $hintsInDifficulty[array_rand($hintsInDifficulty)];
            
            $hintsByDifficulty[$difficulty] = [
                'hint_name' => $randomHint['hint_name'],
                'needed_data_keys' => $randomHint['needed_data'],
            ];
        }

        return $hintsByDifficulty;
    }

    /**
     * Fetch the required data for a set of selected hints.
     *
     * @param array<string, array{hint_name: string, needed_data_keys: array}> $hints The selected hints.
     * @param array $gameData The game being guessed, containing 'id', 'name', 'playtime', etc.
     * @return array<string, array{hint_name: string, needed_data_keys: array, data: array}>
     */
    public function getDataForHints(array $hints, array $gameData): array
    {
        $hintsWithData = [];

        foreach ($hints as $difficulty => $hint) {
            $hintData = [];
            foreach ($hint['needed_data_keys'] as $key) {
                $hintData[$key] = $this->dataService->getDataByKey($key, $gameData);
            }

            $hintsWithData[$difficulty] = [
                'hint_name' => $hint['hint_name'],
                'needed_data_keys' => $hint['needed_data_keys'],
                'data' => $hintData,
            ];
        }

        return $hintsWithData;
    }
}