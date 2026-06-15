<?php

namespace App\Libraries\WordSearch;

/**
 * Builds vocabulary word-search puzzles with words hidden in a letter grid.
 */
class WordSearchGenerator
{
    /** @var array<string, array{int,int}> */
    private const DIRECTIONS_HV = [
        'E'  => [0, 1],
        'W'  => [0, -1],
        'S'  => [1, 0],
        'N'  => [-1, 0],
    ];

    /** @var array<string, array{int,int}> */
    private const DIRECTIONS_DIAG = [
        'SE' => [1, 1],
        'SW' => [1, -1],
        'NE' => [-1, 1],
        'NW' => [-1, -1],
    ];

    private WordSearchWordProvider $wordProvider;

    public function __construct(?WordSearchWordProvider $wordProvider = null)
    {
        $this->wordProvider = $wordProvider ?? new WordSearchWordProvider();
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>|null
     */
    public function generate(array $options): ?array
    {
        $entries = $this->wordProvider->fetchWords($options);
        if (count($entries) < 3) {
            return null;
        }

        $wordCount = max(3, min(20, (int) ($options['word_count'] ?? 10)));
        shuffle($entries);
        $entries = array_slice($entries, 0, $wordCount);

        $directionMode = (string) ($options['direction_mode'] ?? 'hvd');
        $directions    = $this->activeDirections($directionMode);

        $gridSize = (int) ($options['grid_size'] ?? 0);
        if ($gridSize <= 0) {
            $gridSize = $this->autoGridSize($entries);
        }
        $gridSize = max(10, min(24, $gridSize));

        for ($attempt = 0; $attempt < 80; $attempt++) {
            if ($attempt > 0 && $attempt % 10 === 0) {
                shuffle($entries);
            }

            $placed = $this->placeWords($entries, $gridSize, $directions);
            if ($placed !== null) {
                return $placed;
            }
        }

        return null;
    }

    /**
     * @param list<array{word:string, clue:string}> $entries
     * @return list<array<string, array{int,int}>>
     */
    private function activeDirections(string $mode): array
    {
        $dirs = self::DIRECTIONS_HV;
        if ($mode === 'hvd') {
            $dirs = array_merge($dirs, self::DIRECTIONS_DIAG);
        }

        return $dirs;
    }

    /**
     * @param list<array{word:string, clue:string}> $entries
     */
    private function autoGridSize(array $entries): int
    {
        $maxLen = 0;
        foreach ($entries as $e) {
            $maxLen = max($maxLen, strlen($e['word']));
        }

        $count = count($entries);

        return max(12, min(22, $maxLen + (int) ceil($count / 2) + 4));
    }

    /**
     * @param list<array{word:string, clue:string}> $entries
     * @param list<array<string, array{int,int}>> $directions
     * @return array<string, mixed>|null
     */
    private function placeWords(array $entries, int $size, array $directions): ?array
    {
        $grid       = array_fill(0, $size, array_fill(0, $size, ''));
        $placements = [];
        $words      = [];
        $dirKeys    = array_keys($directions);

        usort($entries, static fn ($a, $b) => strlen($b['word']) <=> strlen($a['word']));

        foreach ($entries as $idx => $entry) {
            $word    = $entry['word'];
            $letters = str_split($word);
            $len     = count($letters);
            $placed  = false;

            $tries = $size * $size * count($dirKeys);
            for ($t = 0; $t < $tries; $t++) {
                $dirKey = $dirKeys[array_rand($dirKeys)];
                [$dr, $dc] = $directions[$dirKey];
                $row = random_int(0, $size - 1);
                $col = random_int(0, $size - 1);

                if (! $this->canPlace($grid, $letters, $row, $col, $dr, $dc, $size)) {
                    continue;
                }

                $cells = [];
                for ($i = 0; $i < $len; $i++) {
                    $r = $row + $dr * $i;
                    $c = $col + $dc * $i;
                    $grid[$r][$c] = $letters[$i];
                    $cells[]      = [$r, $c];
                }

                $words[] = [
                    'id'    => $idx,
                    'word'  => $word,
                    'clue'  => $entry['clue'],
                ];
                $placements[] = [
                    'word_id'   => $idx,
                    'direction' => $dirKey,
                    'cells'     => $cells,
                ];
                $placed = true;
                break;
            }

            if (! $placed) {
                return null;
            }
        }

        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($grid[$r][$c] === '') {
                    $grid[$r][$c] = chr(random_int(65, 90));
                }
            }
        }

        return [
            'type'        => 'word_search',
            'rows'        => $size,
            'cols'        => $size,
            'grid'        => $grid,
            'words'       => $words,
            'placements'  => $placements,
            'directions'  => array_keys($directions),
        ];
    }

    /**
     * @param list<list<string>> $grid
     * @param list<string> $letters
     */
    private function canPlace(array $grid, array $letters, int $row, int $col, int $dr, int $dc, int $size): bool
    {
        $len = count($letters);

        for ($i = 0; $i < $len; $i++) {
            $r = $row + $dr * $i;
            $c = $col + $dc * $i;

            if ($r < 0 || $c < 0 || $r >= $size || $c >= $size) {
                return false;
            }

            $existing = $grid[$r][$c];
            if ($existing !== '' && $existing !== $letters[$i]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Strip placements for student-facing puzzle (grading uses server copy).
     *
     * @param array<string, mixed> $puzzle
     * @return array<string, mixed>
     */
    public function puzzleForStudent(array $puzzle): array
    {
        $copy = $puzzle;
        unset($copy['placements']);

        return $copy;
    }
}
