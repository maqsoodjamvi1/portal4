<?php

namespace App\Libraries;

use Config\Database;

/**
 * Manzil = rotating revision through assigned paras (juz), derived from current para progress.
 *
 * On para N with &lt;50% memorized: Sabqi = partial N (+ prior lines via SabqiCalculator);
 * Manzil pool = paras N+2 … 30.
 * On para N with ≥50%: Sabqi = para N; Manzil pool = paras N+1 … 30.
 */
class HifzManzilCalculator
{
    protected HifzSabqiCalculator $sabqi;

    public function __construct()
    {
        $this->sabqi = new HifzSabqiCalculator();
    }

    /**
     * Manzil pool from cursor: no manual “completed paras” list required.
     *
     * @return list<int>
     */
    public function manzilPoolFromCursor(int $cursorLine, string $layoutCode = 'indopak_16'): array
    {
        if ($cursorLine <= 0) {
            return [];
        }

        $sabqi      = $this->sabqi->computeSabqi($cursorLine, $layoutCode);
        $currentJuz = max(1, min(30, (int) ($sabqi['juz_no'] ?? 1)));
        $progress   = (float) ($sabqi['progress_pct'] ?? 0);
        $startJuz   = $progress >= 50.0 ? $currentJuz + 1 : $currentJuz + 2;

        if ($startJuz > 30) {
            return [];
        }

        return range($startJuz, 30);
    }

    /**
     * @return array{juz_no:int,progress_pct:float,manzil_start:int,pool:list<int>}
     */
    public function paraProgressSnapshot(int $cursorLine, string $layoutCode = 'indopak_16'): array
    {
        if ($cursorLine <= 0) {
            return [
                'juz_no'        => 1,
                'progress_pct'  => 0.0,
                'manzil_start'  => 2,
                'pool'          => range(2, 30),
            ];
        }

        $sabqi      = $this->sabqi->computeSabqi($cursorLine, $layoutCode);
        $currentJuz = max(1, min(30, (int) ($sabqi['juz_no'] ?? 1)));
        $progress   = (float) ($sabqi['progress_pct'] ?? 0);
        $startJuz   = $progress >= 50.0 ? $currentJuz + 1 : $currentJuz + 2;
        $pool       = $startJuz <= 30 ? range($startJuz, 30) : [];

        return [
            'juz_no'       => $currentJuz,
            'progress_pct' => $progress,
            'manzil_start' => $startJuz,
            'pool'         => $pool,
        ];
    }

    /**
     * Fully memorized juz numbers (cursor has passed end of each juz).
     *
     * @return list<int>
     */
    public function completedJuzList(int $cursorLine, string $layoutCode = 'indopak_16'): array
    {
        if ($cursorLine <= 0) {
            return [];
        }

        $completed = [];
        for ($juz = 1; $juz <= 30; $juz++) {
            $bounds = $this->sabqi->juzBounds($layoutCode, $juz);
            if ($bounds['end_line'] > 0 && $cursorLine >= $bounds['end_line']) {
                $completed[] = $juz;
            }
        }

        return $completed;
    }

    /**
     * Next paras in the Manzil rotation (1–3 paras per day).
     *
     * @param list<int> $pool Completed juz numbers
     * @return list<int>
     */
    public function todaysManzilJuz(array $pool, int $rotationIndex, int $parasPerDay): array
    {
        if ($pool === []) {
            return [];
        }

        $parasPerDay = max(1, min(3, $parasPerDay));
        $count       = count($pool);
        $start       = ((int) $rotationIndex % $count + $count) % $count;
        $selected    = [];

        for ($i = 0; $i < $parasPerDay; $i++) {
            $selected[] = (int) $pool[($start + $i) % $count];
        }

        return $selected;
    }

    /**
     * Today's Manzil assignment (juz numbers only).
     *
     * @return array{juz_list:list<int>,label:string}
     */
    public function computeManzil(int $cursorLine, int $rotationIndex, int $parasPerDay, string $layoutCode = 'indopak_16'): array
    {
        return $this->suggestFromPool(
            $this->completedJuzList($cursorLine, $layoutCode),
            $rotationIndex,
            $parasPerDay
        );
    }

    /**
     * Suggest today's Manzil from an explicit learned-juz pool (e.g. completed_juz_list).
     *
     * @param list<int> $pool
     * @return array{juz_list:list<int>,label:string}
     */
    public function suggestFromPool(array $pool, int $rotationIndex, int $parasPerDay): array
    {
        $pool = array_values(array_unique(array_map('intval', $pool)));
        sort($pool);
        $juz  = $this->todaysManzilJuz($pool, $rotationIndex, $parasPerDay);

        if ($juz === []) {
            return [
                'juz_list' => [],
                'label'    => '—',
            ];
        }

        return [
            'juz_list' => $juz,
            'label'    => $this->formatParaLabel($juz),
        ];
    }

    /**
     * @param list<int> $juzList
     */
    public function formatParaLabel(array $juzList): string
    {
        if ($juzList === []) {
            return '—';
        }

        $parts = array_map(static fn ($j) => hifzJuzTitle((int) $j), $juzList);

        return implode(' · ', $parts);
    }

    /**
     * @param list<int> $juzList
     */
    public function parseJuzList(?string $csv): array
    {
        if ($csv === null || trim($csv) === '') {
            return [];
        }

        $out = [];
        foreach (explode(',', $csv) as $part) {
            $n = (int) trim($part);
            if ($n >= 1 && $n <= 30) {
                $out[] = $n;
            }
        }

        return $out;
    }

}
