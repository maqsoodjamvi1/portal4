<?php

namespace App\Libraries\MathWorksheet;

/**
 * Builds individual arithmetic problems with operand/result missing modes.
 */
class ProblemBuilder
{
    private NumberRangeConfig $numberConfig;

    public function __construct(?NumberRangeConfig $numberConfig = null)
    {
        $this->numberConfig = $numberConfig ?? new NumberRangeConfig();
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>|null
     */
    public function build(array $options): ?array
    {
        $operation    = (string) ($options['operation'] ?? '+');
        $missingStyle = (string) ($options['missing_style'] ?? 'result');
        $layout       = (string) ($options['layout'] ?? 'horizontal');
        $divMode      = (string) ($options['division_mode'] ?? 'whole');
        $multMode     = (string) ($options['multiplication_mode'] ?? 'random');
        $noCarry      = ! empty($options['no_carry']);
        $noBorrow     = ! empty($options['no_borrow']);
        $noNegative   = ! empty($options['no_negative']);

        $numberConfig = is_array($options['number_config'] ?? null)
            ? $options['number_config']
            : $this->numberConfig->parseFromInput($options);

        $numberType    = (string) ($numberConfig['number_type'] ?? 'integer');
        $decimalPlaces = (int) ($numberConfig['decimal_places'] ?? 0);
        $specA         = $numberConfig['operand_a'];
        $specB         = $numberConfig['operand_b'];

        $problem = match ($operation) {
            '+' => $this->buildAddition($specA, $specB, $numberType, $decimalPlaces, $noCarry),
            '-' => $this->buildSubtraction($specA, $specB, $numberType, $decimalPlaces, $noBorrow, $noNegative),
            '×' => $this->buildMultiplication($specA, $specB, $numberType, $decimalPlaces, $multMode),
            '÷' => $this->buildDivision($specA, $specB, $numberType, $decimalPlaces, $divMode),
            default => null,
        };

        if ($problem === null) {
            return null;
        }

        $missing = $this->resolveMissing($missingStyle, $operation, $divMode, $numberType);
        $problem['operation']     = $operation;
        $problem['missing']       = $missing;
        $problem['layout']        = $layout;
        $problem['number_type']   = $numberType;
        $problem['decimal_places'] = $decimalPlaces;

        return $problem;
    }

    public function hash(array $problem): string
    {
        $a       = (string) ($problem['operand_a'] ?? '');
        $b       = (string) ($problem['operand_b'] ?? '');
        $op      = (string) ($problem['operation'] ?? '');
        $missing = (string) ($problem['missing'] ?? 'result');
        $res     = (string) ($problem['result'] ?? '');
        $rem     = (string) ($problem['remainder'] ?? '');

        return md5("{$op}|{$a}|{$b}|{$missing}|{$res}|{$rem}");
    }

    private function resolveMissing(string $style, string $operation, string $divMode, string $numberType): string
    {
        if ($style === 'operand_a') {
            return 'operand_a';
        }
        if ($style === 'operand_b') {
            return 'operand_b';
        }
        if ($style === 'result') {
            return 'result';
        }

        $choices = ['operand_a', 'operand_b', 'result'];
        if ($operation === '÷' && $divMode === 'remainder' && $numberType === 'integer') {
            $choices[] = 'remainder';
        }

        return $choices[array_rand($choices)];
    }

    /**
     * @param array{min:float, max:float, whole_digits:int, decimal_digits:int} $specA
     * @param array{min:float, max:float, whole_digits:int, decimal_digits:int} $specB
     * @return array<string, mixed>|null
     */
    private function buildAddition(array $specA, array $specB, string $numberType, int $decimalPlaces, bool $noCarry): ?array
    {
        for ($try = 0; $try < 100; $try++) {
            $a = $this->numberConfig->randomOperand($specA, $numberType);
            $b = $this->numberConfig->randomOperand($specB, $numberType);

            if ($noCarry && $numberType === 'integer' && ! $this->noCarryPair((int) $a, (int) $b)) {
                continue;
            }

            return [
                'operand_a' => $a,
                'operand_b' => $b,
                'result'    => $this->numberConfig->add($a, $b, $decimalPlaces),
                'remainder' => null,
            ];
        }

        return null;
    }

    /**
     * @param array{min:float, max:float, whole_digits:int, decimal_digits:int} $specA
     * @param array{min:float, max:float, whole_digits:int, decimal_digits:int} $specB
     * @return array<string, mixed>|null
     */
    private function buildSubtraction(array $specA, array $specB, string $numberType, int $decimalPlaces, bool $noBorrow, bool $noNegative): ?array
    {
        for ($try = 0; $try < 100; $try++) {
            $a = $this->numberConfig->randomOperand($specA, $numberType);
            $b = $this->numberConfig->randomOperand($specB, $numberType);

            if ($noNegative && (float) $a < (float) $b) {
                [$a, $b] = [$b, $a];
            }

            if ($noBorrow && $numberType === 'integer' && ! $this->noBorrowPair((int) $a, (int) $b)) {
                continue;
            }

            if ($noNegative && (float) $a < (float) $b) {
                continue;
            }

            return [
                'operand_a' => $a,
                'operand_b' => $b,
                'result'    => $this->numberConfig->subtract($a, $b, $decimalPlaces),
                'remainder' => null,
            ];
        }

        return null;
    }

    /**
     * @param array{min:float, max:float, whole_digits:int, decimal_digits:int} $specA
     * @param array{min:float, max:float, whole_digits:int, decimal_digits:int} $specB
     * @return array<string, mixed>|null
     */
    private function buildMultiplication(array $specA, array $specB, string $numberType, int $decimalPlaces, string $mode): ?array
    {
        if ($mode === 'times_table' && $numberType === 'integer') {
            $a = (string) random_int(2, 12);
            $b = (string) random_int(2, 12);

            return [
                'operand_a' => $a,
                'operand_b' => $b,
                'result'    => $this->numberConfig->multiply($a, $b, 0),
                'remainder' => null,
            ];
        }

        $a = $this->numberConfig->randomOperand($specA, $numberType);
        $b = $this->numberConfig->randomOperand($specB, $numberType);
        $places = $numberType === 'decimal' ? min(4, $decimalPlaces * 2) : 0;

        return [
            'operand_a' => $a,
            'operand_b' => $b,
            'result'    => $this->numberConfig->multiply($a, $b, $places),
            'remainder' => null,
        ];
    }

    /**
     * @param array{min:float, max:float, whole_digits:int, decimal_digits:int} $specA
     * @param array{min:float, max:float, whole_digits:int, decimal_digits:int} $specB
     * @return array<string, mixed>|null
     */
    private function buildDivision(array $specA, array $specB, string $numberType, int $decimalPlaces, string $mode): ?array
    {
        for ($try = 0; $try < 120; $try++) {
            $divisor = $this->numberConfig->randomOperand($specB, $numberType);
            if ((float) $divisor == 0.0) {
                continue;
            }

            if ($mode === 'remainder' && $numberType === 'integer') {
                $quotient  = $this->numberConfig->randomOperand($specA, $numberType);
                $qInt      = max(1, (int) $quotient);
                $dInt      = max(2, (int) $divisor);
                $remainder = random_int(1, $dInt - 1);
                $dividend  = (string) (($qInt * $dInt) + $remainder);

                return [
                    'operand_a' => $dividend,
                    'operand_b' => (string) $dInt,
                    'result'    => (string) $qInt,
                    'remainder' => $remainder,
                ];
            }

            $quotient = $this->numberConfig->randomOperand($specA, $numberType);
            $product  = $this->numberConfig->multiply($quotient, $divisor, $decimalPlaces);
            $built    = $this->numberConfig->divideWhole($product, $divisor, $decimalPlaces);

            if ($built !== null) {
                return [
                    'operand_a' => $built['operand_a'],
                    'operand_b' => $built['operand_b'],
                    'result'    => $built['result'],
                    'remainder' => null,
                ];
            }
        }

        return null;
    }

    private function noCarryPair(int $a, int $b): bool
    {
        return ($a % 10) + ($b % 10) < 10;
    }

    private function noBorrowPair(int $a, int $b): bool
    {
        return ($a % 10) >= ($b % 10);
    }
}
