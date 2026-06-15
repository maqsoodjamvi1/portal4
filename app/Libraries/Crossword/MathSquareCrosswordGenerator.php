<?php

namespace App\Libraries\Crossword;

/**
 * Generates 7×7 "math square" puzzles (across + down).
 */
class MathSquareCrosswordGenerator implements CrosswordGeneratorInterface
{
    private const GRID = 7;

    /** @var array<int, array{min:int, max:int}> */
    private const GRADE_RANGES = [
        1 => ['min' => 1,  'max' => 12],
        2 => ['min' => 1,  'max' => 20],
        3 => ['min' => 1,  'max' => 50],
        4 => ['min' => 1,  'max' => 100],
        5 => ['min' => 1,  'max' => 200],
    ];

    public function type(): string
    {
        return 'math_square';
    }

    public function label(): string
    {
        return '7×7 Math Square (classic)';
    }

    /**
     * @param array{grade:int, operations?:string[], difficulty?:string} $options
     */
    public function generate(array $options): ?array
    {
        $grade = max(1, min(5, (int) ($options['grade'] ?? 1)));
        $range = self::GRADE_RANGES[$grade];
        $ops   = $this->resolveOperations($options, $grade);
        [$min, $max] = $this->difficultyBounds($range['min'], $range['max'], $options['difficulty'] ?? 'medium');

        for ($attempt = 0; $attempt < 200; $attempt++) {
            $built = $this->tryBuild($min, $max, $ops, $grade);
            if ($built !== null) {
                return $built;
            }
        }

        return null;
    }

    /**
     * @param string[] $ops
     * @return array<string, mixed>|null
     */
    protected function tryBuild(int $min, int $max, array $ops, int $grade): ?array
    {
        $nums = [];
        for ($r = 0; $r < 3; $r++) {
            for ($c = 0; $c < 3; $c++) {
                $nums[$r][$c] = random_int($min, $max);
            }
        }

        $hOps = [];
        for ($r = 0; $r < 3; $r++) {
            for ($i = 0; $i < 2; $i++) {
                $hOps[$r][$i] = $ops[array_rand($ops)];
            }
        }

        $vOps = [];
        for ($i = 0; $i < 2; $i++) {
            for ($c = 0; $c < 3; $c++) {
                $vOps[$i][$c] = $ops[array_rand($ops)];
            }
        }

        $rowResults = [];
        for ($r = 0; $r < 3; $r++) {
            $rowResults[$r] = $this->evalTriple(
                $nums[$r][0],
                $hOps[$r][0],
                $nums[$r][1],
                $hOps[$r][1],
                $nums[$r][2]
            );
        }

        $colResults = [];
        for ($c = 0; $c < 3; $c++) {
            $colResults[$c] = $this->evalTriple(
                $nums[0][$c],
                $vOps[0][$c],
                $nums[1][$c],
                $vOps[1][$c],
                $nums[2][$c]
            );
        }

        $sumOps = $this->findMatchingSumOps($rowResults, $colResults, $ops);
        if ($sumOps === null) {
            return null;
        }

        $grand = $this->evalTriple(
            $rowResults[0],
            $sumOps[0],
            $rowResults[1],
            $sumOps[1],
            $rowResults[2]
        );

        if (! $this->isValidPuzzle($rowResults, $colResults, $grand, $grade)) {
            return null;
        }

        return [
            'type'         => $this->type(),
            'size'         => self::GRID,
            'nums'         => $nums,
            'hOps'         => $hOps,
            'vOps'         => $vOps,
            'sumOps'       => $sumOps,
            'rowResults'   => $rowResults,
            'colResults'   => $colResults,
            'grandTotal'   => $grand,
            'cells'        => $this->buildCells($nums, $hOps, $vOps, $sumOps, $rowResults, $colResults, $grand),
            'clues'        => null,
            'meta'         => ['grade' => $grade],
        ];
    }

    /**
     * @param int[] $rowResults
     * @param int[] $colResults
     */
    protected function isValidPuzzle(array $rowResults, array $colResults, int $grand, int $grade): bool
    {
        $maxResult = self::GRADE_RANGES[$grade]['max'] * 3;

        foreach ($rowResults as $v) {
            if ($v < 0 || $v > $maxResult) {
                return false;
            }
        }
        foreach ($colResults as $v) {
            if ($v < 0 || $v > $maxResult) {
                return false;
            }
        }

        return $grand >= 0 && $grand <= $maxResult;
    }

    /**
     * @return array<int, array<int, array{type:string, value?:string|int, answer?:bool}>>
     */
    protected function buildCells(
        array $nums,
        array $hOps,
        array $vOps,
        array $sumOps,
        array $rowResults,
        array $colResults,
        int $grand
    ): array {
        $cells = array_fill(0, self::GRID, array_fill(0, self::GRID, ['type' => 'blank']));

        $set = static function (array &$cells, int $r, int $c, string $type, $value = '', bool $answer = false): void {
            $cells[$r][$c] = ['type' => $type, 'value' => $value, 'answer' => $answer];
        };

        $numMap = [
            [0, 0], [0, 2], [0, 4],
            [2, 0], [2, 2], [2, 4],
            [4, 0], [4, 2], [4, 4],
        ];
        foreach ($numMap as $idx => [$r, $c]) {
            $ri = (int) floor($idx / 3);
            $ci = $idx % 3;
            $set($cells, $r, $c, 'number', $nums[$ri][$ci]);
        }

        $hOpMap = [[0, 1], [0, 3], [2, 1], [2, 3], [4, 1], [4, 3]];
        foreach ($hOpMap as $idx => [$r, $c]) {
            $ri = (int) floor($idx / 2);
            $oi = $idx % 2;
            $set($cells, $r, $c, 'operator', $hOps[$ri][$oi]);
        }

        $vOpMap = [[1, 0], [1, 2], [1, 4], [3, 0], [3, 2], [3, 4]];
        foreach ($vOpMap as $idx => [$r, $c]) {
            $vi = (int) floor($idx / 3);
            $ci = $idx % 3;
            $set($cells, $r, $c, 'operator', $vOps[$vi][$ci]);
        }

        foreach ([[0, 5], [2, 5], [4, 5]] as [$r, $c]) {
            $set($cells, $r, $c, 'equals', '=');
        }

        foreach ([[5, 0], [5, 2], [5, 4], [5, 6]] as [$r, $c]) {
            $set($cells, $r, $c, 'equals', '=');
        }

        foreach ([[0, 6], [2, 6], [4, 6]] as $i => [$r, $c]) {
            $set($cells, $r, $c, 'result', $rowResults[$i], true);
        }

        foreach ([[6, 0], [6, 2], [6, 4]] as $i => [$r, $c]) {
            $set($cells, $r, $c, 'result', $colResults[$i], true);
        }

        $set($cells, 6, 1, 'operator', $sumOps[0]);
        $set($cells, 6, 3, 'operator', $sumOps[1]);
        $set($cells, 6, 5, 'equals', '=');
        $set($cells, 6, 6, 'result', $grand, true);

        return $cells;
    }

    /**
     * @param int[] $rowResults
     * @param int[] $colResults
     * @param string[] $ops
     * @return array{0:string,1:string}|null
     */
    protected function findMatchingSumOps(array $rowResults, array $colResults, array $ops): ?array
    {
        foreach ($ops as $op1) {
            foreach ($ops as $op2) {
                $fromRows = $this->evalTriple($rowResults[0], $op1, $rowResults[1], $op2, $rowResults[2]);
                $fromCols = $this->evalTriple($colResults[0], $op1, $colResults[1], $op2, $colResults[2]);
                if ($fromRows === $fromCols) {
                    return [$op1, $op2];
                }
            }
        }

        return null;
    }

    protected function evalTriple(int $a, string $op1, int $b, string $op2, int $c): int
    {
        $mid = match ($op1) {
            '+' => $a + $b,
            '-' => $a - $b,
            '×' => $a * $b,
            '÷' => $b !== 0 ? (int) ($a / $b) : 0,
            default => $a + $b,
        };

        return match ($op2) {
            '+' => $mid + $c,
            '-' => $mid - $c,
            '×' => $mid * $c,
            '÷' => $c !== 0 ? (int) ($mid / $c) : 0,
            default => $mid + $c,
        };
    }

    /** @return string[] */
    protected function resolveOperations(array $options, int $grade): array
    {
        $allowed = ['+', '-'];
        if ($grade >= 3) {
            $allowed[] = '×';
        }
        if ($grade >= 4) {
            $allowed[] = '÷';
        }

        $picked = array_values(array_intersect($options['operations'] ?? [], $allowed));

        return $picked !== [] ? $picked : ($grade <= 2 ? ['+', '-'] : $allowed);
    }

    /** @return array{int, int} */
    protected function difficultyBounds(int $min, int $max, string $difficulty): array
    {
        $span = max(1, $max - $min);

        return match ($difficulty) {
            'easy'  => [$min, (int) ($min + $span * 0.4)],
            'hard'  => [(int) ($min + $span * 0.5), $max],
            default => [(int) ($min + $span * 0.15), (int) ($min + $span * 0.85)],
        };
    }
}
