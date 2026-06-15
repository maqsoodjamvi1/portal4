<?php

namespace App\Libraries;

/**
 * Student search ranking for fee chalan autocomplete.
 */
class FeeChalanSearchService
{
    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    public function rankResults(array $rows, string $searchTerm): array
    {
        $termNorm = strtolower(trim(preg_replace('/\s+/u', ' ', $searchTerm) ?? ''));

        usort($rows, function (array $a, array $b) use ($termNorm): int {
            $scoreA = $this->scoreRow($a, $termNorm);
            $scoreB = $this->scoreRow($b, $termNorm);

            if ($scoreA !== $scoreB) {
                return $scoreB <=> $scoreA;
            }

            return strcasecmp((string) ($a['student_name'] ?? ''), (string) ($b['student_name'] ?? ''));
        });

        return $rows;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function scoreRow(array $row, string $termNorm): int
    {
        $name  = strtolower(trim((string) ($row['student_name'] ?? '')));
        $first = strtolower(trim((string) ($row['first_name'] ?? '')));
        $last  = strtolower(trim((string) ($row['last_name'] ?? '')));
        $regNo = strtolower(trim((string) ($row['reg_no'] ?? '')));

        if ($termNorm === '') {
            return 0;
        }

        if ($name === $termNorm) {
            return 1000;
        }

        if ($regNo !== '' && $regNo === $termNorm) {
            return 980;
        }

        if (str_starts_with($name, $termNorm)) {
            return 920;
        }

        if (str_contains($name, $termNorm)) {
            return 860;
        }

        $words = explode(' ', $termNorm);
        if (count($words) >= 2) {
            $firstWord = $words[0];
            $lastWord  = $words[count($words) - 1];
            if (str_starts_with($first, $firstWord) && str_starts_with($last, $lastWord)) {
                return 840;
            }
            if (str_contains($first, $firstWord) && str_contains($last, $lastWord)) {
                return 820;
            }
        }

        $allWordsInName = true;
        foreach ($words as $word) {
            if ($word === '' || (! str_contains($first, $word) && ! str_contains($last, $word))) {
                $allWordsInName = false;
                break;
            }
        }
        if ($allWordsInName) {
            return 780;
        }

        if (str_contains($name, $words[0] ?? '')) {
            return 500;
        }

        return 100;
    }
}
