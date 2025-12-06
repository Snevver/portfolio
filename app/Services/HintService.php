<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class HintService
{
    public function __construct(
        private HintDataService $dataService = new HintDataService()
    ) {}

    /**
     * Select one random hint per difficulty level.
     *
     * Reads available hints from registry/hints.json and selects one hint
     * randomly from each difficulty (easy, medium, hard).
     *
     * @return array<string, array{hint_name: string, needed_data_keys: array}>
     */
    public function getRandomHints(): array
    {
        $hints = json_decode(File::get(base_path('registry/hints.json')), true);

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

            if (!empty($hintsInDifficulty)) {
                $randomHint = $hintsInDifficulty[array_rand($hintsInDifficulty)];

                $hintsByDifficulty[$difficulty] = [
                    'hint_name' => $randomHint['hint_name'],
                    'needed_data_keys' => $randomHint['needed_data'],
                ];
            }
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
