<?php

namespace App\Libraries;

use Config\Database;

/**
 * Computes Sabqi line ranges from student cursor and juz progress.
 */
class HifzSabqiCalculator
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * @return array{juz_no:int,start_line:int,end_line:int,total_lines:int}
     */
    public function juzBounds(string $layoutCode, int $juzNo): array
    {
        if ($juzNo < 1 || $juzNo > 30) {
            return ['juz_no' => $juzNo, 'start_line' => 0, 'end_line' => 0, 'total_lines' => 0];
        }

        $row = $this->db->table('quran_mushaf_lines')
            ->selectMin('global_line_no', 'start_line')
            ->selectMax('global_line_no', 'end_line')
            ->where('layout_code', $layoutCode)
            ->where('juz_no', $juzNo)
            ->get()
            ->getRow();

        $start = (int) ($row->start_line ?? 0);
        $end   = (int) ($row->end_line ?? 0);

        return [
            'juz_no'      => $juzNo,
            'start_line'  => $start,
            'end_line'    => $end,
            'total_lines' => $start > 0 && $end >= $start ? ($end - $start + 1) : 0,
        ];
    }

    public function juzForLine(string $layoutCode, int $globalLine): int
    {
        if ($globalLine <= 0) {
            return 1;
        }

        $row = $this->db->table('quran_mushaf_lines')
            ->select('juz_no')
            ->where('layout_code', $layoutCode)
            ->where('global_line_no', $globalLine)
            ->get()
            ->getRow();

        return (int) ($row->juz_no ?? 1);
    }

    /**
     * Sabqi range before today's new Sabaq (uses cursor = last memorized line).
     *
     * @return array{line_from:int,line_to:int,progress_pct:float,juz_no:int}
     */
    public function computeSabqi(int $cursorLine, string $layoutCode = 'indopak_16'): array
    {
        if ($cursorLine <= 0) {
            return ['line_from' => 0, 'line_to' => 0, 'progress_pct' => 0.0, 'juz_no' => 1];
        }

        $currentJuz = $this->juzForLine($layoutCode, $cursorLine);
        $bounds     = $this->juzBounds($layoutCode, $currentJuz);

        if ($bounds['start_line'] <= 0) {
            return ['line_from' => 0, 'line_to' => 0, 'progress_pct' => 0.0, 'juz_no' => $currentJuz];
        }

        $memorizedInJuz = max(0, $cursorLine - $bounds['start_line'] + 1);
        $totalInJuz     = (int) $bounds['total_lines'];
        $progressPct    = $totalInJuz > 0 ? ($memorizedInJuz / $totalInJuz) * 100 : 0.0;

        if ($progressPct >= 50) {
            return [
                'line_from'    => $bounds['start_line'],
                'line_to'      => $cursorLine,
                'progress_pct' => round($progressPct, 1),
                'juz_no'       => $currentJuz,
            ];
        }

        $lineFrom = $bounds['start_line'];
        if ($currentJuz > 1) {
            $prev = $this->juzBounds($layoutCode, $currentJuz - 1);
            if ($prev['start_line'] > 0) {
                $lineFrom = $prev['start_line'];
            }
        }

        return [
            'line_from'    => $lineFrom,
            'line_to'      => $cursorLine,
            'progress_pct' => round($progressPct, 1),
            'juz_no'       => $currentJuz,
        ];
    }

    /**
     * Update hifz_students juz tracking after Sabaq cursor moves.
     */
    public function cursorStats(string $layoutCode, int $cursorLine): array
    {
        $juz    = $this->juzForLine($layoutCode, $cursorLine);
        $bounds = $this->juzBounds($layoutCode, $juz);
        $mem    = 0;

        if ($bounds['start_line'] > 0 && $cursorLine >= $bounds['start_line']) {
            $mem = $cursorLine - $bounds['start_line'] + 1;
        }

        return [
            'current_juz'                 => $juz,
            'current_juz_memorized_lines' => $mem,
        ];
    }
}
