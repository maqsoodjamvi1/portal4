<?php

namespace App\Libraries\Crossword;

/**
 * Same 7×7 math square but operators are blank — students fill + − × ÷.
 */
class MathMissingOperatorCrosswordGenerator implements CrosswordGeneratorInterface
{
    private MathSquareCrosswordGenerator $inner;

    public function __construct()
    {
        $this->inner = new MathSquareCrosswordGenerator();
    }

    public function type(): string
    {
        return 'missing_operator';
    }

    public function label(): string
    {
        return '7×7 Missing Operator';
    }

    public function generate(array $options): ?array
    {
        $puzzle = $this->inner->generate($options);
        if ($puzzle === null) {
            return null;
        }

        $puzzle['type'] = $this->type();

        foreach ($puzzle['cells'] as $r => $row) {
            foreach ($row as $c => $cell) {
                if (($cell['type'] ?? '') === 'operator') {
                    $puzzle['cells'][$r][$c] = [
                        'type'   => 'operator',
                        'value'  => $cell['value'],
                        'answer' => true,
                    ];
                }
            }
        }

        return $puzzle;
    }
}
