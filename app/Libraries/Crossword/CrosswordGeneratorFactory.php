<?php

namespace App\Libraries\Crossword;

class CrosswordGeneratorFactory
{
    /** @return array<string, CrosswordGeneratorInterface> */
    public static function all(): array
    {
        return [
            'math_square'       => new MathSquareCrosswordGenerator(),
            'missing_operator'  => new MathMissingOperatorCrosswordGenerator(),
            'mini_5x5'          => new MathMiniCrosswordGenerator(),
            'vocab'             => new VocabCrosswordGenerator(),
        ];
    }

    public static function make(string $type): CrosswordGeneratorInterface
    {
        $all = self::all();

        return $all[$type] ?? $all['math_square'];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        $out = [];
        foreach (self::all() as $key => $gen) {
            $out[$key] = $gen->label();
        }

        return $out;
    }
}
