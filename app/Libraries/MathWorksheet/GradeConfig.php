<?php

namespace App\Libraries\MathWorksheet;

/**
 * Grade bands, difficulty scaling, and operation recommendations.
 */
class GradeConfig
{
    /** @var array<int, array{min:int, max:int}> */
    public const GRADE_RANGES = [
        1 => ['min' => 1,   'max' => 10],
        2 => ['min' => 1,   'max' => 20],
        3 => ['min' => 1,   'max' => 50],
        4 => ['min' => 1,   'max' => 100],
        5 => ['min' => 1,   'max' => 200],
        6 => ['min' => 1,   'max' => 500],
        7 => ['min' => 1,   'max' => 750],
        8 => ['min' => 1,   'max' => 1000],
    ];

    /**
     * @return array{min:int, max:int}
     */
    public function bounds(int $grade, string $difficulty, ?int $customMin = null, ?int $customMax = null): array
    {
        if ($customMin !== null && $customMax !== null && $customMin > 0 && $customMax >= $customMin) {
            return ['min' => $customMin, 'max' => $customMax];
        }

        $grade = max(1, min(8, $grade));
        $range = self::GRADE_RANGES[$grade];

        return $this->difficultyBounds($range['min'], $range['max'], $difficulty);
    }

    /**
     * @return array{min:int, max:int}
     */
    public function difficultyBounds(int $min, int $max, string $difficulty): array
    {
        $span = max(1, $max - $min);

        return match ($difficulty) {
            'easy'   => ['min' => $min, 'max' => $min + (int) floor($span * 0.5)],
            'hard'   => ['min' => $min, 'max' => $max],
            default  => ['min' => $min, 'max' => $min + (int) floor($span * 0.75)],
        };
    }

    /**
     * @param string[] $requested
     * @return string[]
     */
    public function resolveOperations(array $requested): array
    {
        $allowed = ['+', '-', '×', '÷'];
        $ops     = array_values(array_intersect($requested, $allowed));

        return $ops !== [] ? $ops : ['+', '-'];
    }

    public function maxPerPageForLayout(string $layout): int
    {
        return $layout === 'vertical' ? 20 : 40;
    }
}
