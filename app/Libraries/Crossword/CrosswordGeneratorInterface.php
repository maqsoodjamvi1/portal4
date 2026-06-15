<?php

namespace App\Libraries\Crossword;

/**
 * Common contract for all crossword / math-grid generators.
 *
 * Returned puzzle shape:
 * [
 *   'type'   => string,
 *   'size'   => int,
 *   'cells'  => array<int, array<int, array{type:string, value?:mixed, answer?:bool}>>,
 *   'clues'  => array{across?:array, down?:array}|null,
 *   'meta'   => array,
 * ]
 */
interface CrosswordGeneratorInterface
{
    public function type(): string;

    public function label(): string;

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>|null
     */
    public function generate(array $options): ?array;
}
