<?php

namespace App\Libraries\MathWorksheet;

/**
 * Operand digit presets, range validation, and random number generation (integer & decimal).
 */
class NumberRangeConfig
{
    /**
     * @return array{min:int, max:int}
     */
    public function integerBoundsForDigits(int $digits): array
    {
        $digits = max(1, min(5, $digits));

        if ($digits === 1) {
            return ['min' => 1, 'max' => 9];
        }

        $min = (int) pow(10, $digits - 1);
        $max = (int) pow(10, $digits) - 1;

        return ['min' => $min, 'max' => $max];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{
     *   number_type:string,
     *   operand_a:array{min:float, max:float, whole_digits:int, decimal_digits:int},
     *   operand_b:array{min:float, max:float, whole_digits:int, decimal_digits:int},
     *   decimal_places:int
     * }
     */
    public function parseFromInput(array $input): array
    {
        $numberType = (string) ($input['number_type'] ?? 'integer');

        if ($numberType === 'decimal') {
            $whole = max(1, min(3, (int) ($input['operand_whole_digits'] ?? $input['operand_a_whole_digits'] ?? 2)));
            $dec   = max(0, min(4, (int) ($input['operand_decimal_digits'] ?? $input['operand_a_decimal_digits'] ?? 2)));

            $bounds = $this->decimalBoundsForDigits($whole, $dec);

            $min = $this->toFloat($input['operand_min'] ?? $input['operand_a_min'] ?? $bounds['min'], $dec);
            $max = $this->toFloat($input['operand_max'] ?? $input['operand_a_max'] ?? $bounds['max'], $dec);

            $min = max($bounds['min'], min($min, $bounds['max']));
            $max = max($min, min($max, $bounds['max']));

            $operand = [
                'min'            => $min,
                'max'            => $max,
                'whole_digits'   => $whole,
                'decimal_digits' => $dec,
            ];

            return [
                'number_type'    => 'decimal',
                'operand_a'      => $operand,
                'operand_b'      => $operand,
                'decimal_places' => max($dec, 1),
            ];
        }

        $digits = max(1, min(5, (int) ($input['operand_digits'] ?? $input['operand_a_digits'] ?? 2)));
        $preset = $this->integerBoundsForDigits($digits);

        $min = (int) ($input['operand_min'] ?? $input['operand_a_min'] ?? $preset['min']);
        $max = (int) ($input['operand_max'] ?? $input['operand_a_max'] ?? $preset['max']);

        $min = max($preset['min'], min($min, $preset['max']));
        $max = max($min, min($max, $preset['max']));

        $operand = [
            'min'            => (float) $min,
            'max'            => (float) $max,
            'whole_digits'   => $digits,
            'decimal_digits' => 0,
        ];

        return [
            'number_type'    => 'integer',
            'operand_a'      => $operand,
            'operand_b'      => $operand,
            'decimal_places' => 0,
        ];
    }

    /**
     * @return array{min:float, max:float}
     */
    public function decimalBoundsForDigits(int $wholeDigits, int $decimalDigits): array
    {
        $wholeDigits   = max(1, min(3, $wholeDigits));
        $decimalDigits = max(0, min(4, $decimalDigits));

        $wholeMax = (int) pow(10, $wholeDigits) - 1;
        $min      = $decimalDigits > 0 ? 1 / (10 ** $decimalDigits) : 1.0;
        $max      = (float) ($wholeMax + ($decimalDigits > 0 ? (pow(10, $decimalDigits) - 1) / (10 ** $decimalDigits) : 0));

        return ['min' => round($min, $decimalDigits), 'max' => round($max, $decimalDigits)];
    }

    /**
     * @param array{min:float, max:float, whole_digits:int, decimal_digits:int} $spec
     */
    public function randomOperand(array $spec, string $numberType): string
    {
        if ($numberType === 'decimal' && $spec['decimal_digits'] > 0) {
            return $this->randomDecimal(
                $spec['min'],
                $spec['max'],
                (int) $spec['decimal_digits']
            );
        }

        return (string) random_int((int) $spec['min'], (int) $spec['max']);
    }

    public function randomDecimal(float $min, float $max, int $decimalDigits): string
    {
        $decimalDigits = max(0, min(4, $decimalDigits));
        $scale         = 10 ** $decimalDigits;
        $minUnits      = (int) round($min * $scale);
        $maxUnits      = (int) round($max * $scale);

        if ($maxUnits < $minUnits) {
            [$minUnits, $maxUnits] = [$maxUnits, $minUnits];
        }

        $value = random_int($minUnits, $maxUnits) / $scale;

        return $this->formatNumber($value, $decimalDigits);
    }

    public function formatNumber(float|string $value, int $decimalPlaces): string
    {
        if ($decimalPlaces <= 0) {
            return (string) (int) round((float) $value);
        }

        return number_format((float) $value, $decimalPlaces, '.', '');
    }

    public function add(string $a, string $b, int $decimalPlaces): string
    {
        return $this->formatNumber((float) $a + (float) $b, $decimalPlaces);
    }

    public function subtract(string $a, string $b, int $decimalPlaces): string
    {
        return $this->formatNumber((float) $a - (float) $b, $decimalPlaces);
    }

    public function multiply(string $a, string $b, int $decimalPlaces): string
    {
        return $this->formatNumber((float) $a * (float) $b, $decimalPlaces);
    }

    /**
     * @return array{operand_a:string, operand_b:string, result:string}|null
     */
    public function divideWhole(string $dividend, string $divisor, int $decimalPlaces): ?array
    {
        $d = (float) $divisor;
        if ($d == 0.0) {
            return null;
        }

        $quotient = (float) $dividend / $d;

        if ($decimalPlaces <= 0 && abs($quotient - round($quotient)) > 0.0001) {
            return null;
        }

        return [
            'operand_a' => $dividend,
            'operand_b' => $divisor,
            'result'    => $this->formatNumber($quotient, $decimalPlaces),
        ];
    }

    /**
     * @param array<string, mixed> $config
     */
    public function summaryLabel(array $config): string
    {
        if (($config['number_type'] ?? 'integer') === 'decimal') {
            $spec = $config['operand_a'];

            return sprintf(
                'Decimals — %d before + %d after decimal (%s–%s)',
                (int) $spec['whole_digits'],
                (int) $spec['decimal_digits'],
                $this->formatNumber($spec['min'], (int) $spec['decimal_digits']),
                $this->formatNumber($spec['max'], (int) $spec['decimal_digits'])
            );
        }

        $spec = $config['operand_a'];

        return sprintf(
            'Integers — %d-digit (%s–%s)',
            (int) $spec['whole_digits'],
            $this->formatNumber($spec['min'], 0),
            $this->formatNumber($spec['max'], 0)
        );
    }

    private function toFloat(mixed $value, int $decimalDigits): float
    {
        return round((float) $value, max(0, $decimalDigits));
    }
}
