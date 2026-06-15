<?php

namespace App\Libraries\MathWorksheet;

/**
 * Generates a set of unique arithmetic problems for a worksheet.
 */
class MathWorksheetGenerator
{
    private GradeConfig $config;
    private ProblemBuilder $builder;

    public function __construct(?GradeConfig $config = null, ?ProblemBuilder $builder = null)
    {
        $this->config  = $config ?? new GradeConfig();
        $this->builder = $builder ?? new ProblemBuilder();
    }

    /**
     * @param array<string, mixed> $options
     * @return list<array<string, mixed>>
     */
    public function generate(int $count, array $options): array
    {
        $count = max(1, min(100, $count));

        $operations = $this->config->resolveOperations(
            is_array($options['operations'] ?? null) ? $options['operations'] : ['+', '-']
        );

        $operationMix = (string) ($options['operation_mix'] ?? 'mixed');
        $seen         = [];
        $problems     = [];
        $maxRetries   = $count * 200;

        for ($attempt = 0; count($problems) < $count && $attempt < $maxRetries; $attempt++) {
            $operation = $this->pickOperation($operations, $operationMix, $problems, $count);
            $missingStyle = (string) ($options['missing_style'] ?? 'result');

            $built = $this->builder->build(array_merge($options, [
                'operation'     => $operation,
                'missing_style' => $missingStyle,
            ]));

            if ($built === null) {
                continue;
            }

            $hash = $this->builder->hash($built);
            if (isset($seen[$hash])) {
                continue;
            }

            $seen[$hash]       = true;
            $built['num']      = count($problems) + 1;
            $problems[]        = $built;
        }

        return $problems;
    }

    /**
     * @param string[] $operations
     * @param list<array<string, mixed>> $existing
     */
    private function pickOperation(array $operations, string $mix, array $existing, int $total): string
    {
        if ($mix === 'separate' && count($operations) > 1) {
            $perOp = (int) ceil($total / count($operations));
            $index = (int) floor(count($existing) / $perOp);
            $index = min($index, count($operations) - 1);

            return $operations[$index];
        }

        return $operations[array_rand($operations)];
    }
}
