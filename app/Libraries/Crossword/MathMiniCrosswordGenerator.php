<?php

namespace App\Libraries\Crossword;

/**
 * Generates 5×5 mini math squares (2×2 number core) for younger grades.
 */
class MathMiniCrosswordGenerator extends MathSquareCrosswordGenerator
{
    private const GRID = 5;

    public function type(): string
    {
        return 'mini_5x5';
    }

    public function label(): string
    {
        return '5×5 Mini Math Square';
    }

    /**
     * @param array{grade:int, operations?:string[], difficulty?:string} $options
     */
    public function generate(array $options): ?array
    {
        $grade = max(1, min(5, (int) ($options['grade'] ?? 1)));
        $ops   = $this->resolveOperations($options, $grade);
        [$min, $max] = $this->miniBounds($grade, $options['difficulty'] ?? 'medium');

        for ($attempt = 0; $attempt < 200; $attempt++) {
            $built = $this->tryBuildMini($min, $max, $ops, $grade);
            if ($built !== null) {
                return $built;
            }
        }

        return null;
    }

    /** @return array{int, int} */
    private function miniBounds(int $grade, string $difficulty): array
    {
        $caps = [1 => 10, 2 => 15, 3 => 25, 4 => 40, 5 => 60];
        $max  = $caps[$grade] ?? 20;
        $min  = 1;

        return match ($difficulty) {
            'easy'  => [1, (int) ($max * 0.5)],
            'hard'  => [(int) ($max * 0.4), $max],
            default => [1, $max],
        };
    }

    /**
     * @param string[] $ops
     * @return array<string, mixed>|null
     */
    private function tryBuildMini(int $min, int $max, array $ops, int $grade): ?array
    {
        $nums = [
            [random_int($min, $max), random_int($min, $max)],
            [random_int($min, $max), random_int($min, $max)],
        ];

        $hOps = [
            [$ops[array_rand($ops)], $ops[array_rand($ops)]],
            [$ops[array_rand($ops)], $ops[array_rand($ops)]],
        ];

        $vOps = [
            [$ops[array_rand($ops)], $ops[array_rand($ops)]],
            [$ops[array_rand($ops)], $ops[array_rand($ops)]],
        ];

        $rowResults = [
            $this->evalPair($nums[0][0], $hOps[0][0], $nums[0][1]),
            $this->evalPair($nums[1][0], $hOps[1][0], $nums[1][1]),
        ];

        $colResults = [
            $this->evalPair($nums[0][0], $vOps[0][0], $nums[1][0]),
            $this->evalPair($nums[0][1], $vOps[0][1], $nums[1][1]),
        ];

        $maxResult = $max * 2;
        foreach (array_merge($rowResults, $colResults) as $v) {
            if ($v < 0 || $v > $maxResult) {
                return null;
            }
        }

        $cells = array_fill(0, self::GRID, array_fill(0, self::GRID, ['type' => 'blank']));
        $set   = static function (array &$cells, int $r, int $c, string $type, $value = '', bool $answer = false): void {
            $cells[$r][$c] = ['type' => $type, 'value' => $value, 'answer' => $answer];
        };

        $set($cells, 0, 0, 'number', $nums[0][0]);
        $set($cells, 0, 1, 'operator', $hOps[0][0]);
        $set($cells, 0, 2, 'number', $nums[0][1]);
        $set($cells, 0, 3, 'equals', '=');
        $set($cells, 0, 4, 'result', $rowResults[0], true);

        $set($cells, 1, 0, 'operator', $vOps[0][0]);
        $set($cells, 1, 2, 'operator', $vOps[0][1]);

        $set($cells, 2, 0, 'number', $nums[1][0]);
        $set($cells, 2, 1, 'operator', $hOps[1][0]);
        $set($cells, 2, 2, 'number', $nums[1][1]);
        $set($cells, 2, 3, 'equals', '=');
        $set($cells, 2, 4, 'result', $rowResults[1], true);

        $set($cells, 3, 0, 'equals', '=');
        $set($cells, 3, 2, 'equals', '=');
        $set($cells, 3, 4, 'equals', '=');

        $set($cells, 4, 0, 'result', $colResults[0], true);
        $set($cells, 4, 2, 'result', $colResults[1], true);

        return [
            'type'       => $this->type(),
            'size'       => self::GRID,
            'cells'      => $cells,
            'clues'      => null,
            'meta'       => ['grade' => $grade],
            'rowResults' => $rowResults,
            'colResults' => $colResults,
        ];
    }

    private function evalPair(int $a, string $op, int $b): int
    {
        return match ($op) {
            '+' => $a + $b,
            '-' => $a - $b,
            '×' => $a * $b,
            '÷' => $b !== 0 ? (int) ($a / $b) : 0,
            default => $a + $b,
        };
    }
}
