<?php

namespace App\Libraries;

use Config\Database;

/**
 * Resolves Mushaf line ranges to full-ayah boundaries (16-line Indo-Pak).
 */
class HifzLineResolver
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * @return object|null Row from quran_mushaf_lines
     */
    public function getLine(string $layoutCode, int $globalLineNo): ?object
    {
        if ($globalLineNo <= 0) {
            return null;
        }

        return $this->db->table('quran_mushaf_lines')
            ->where('layout_code', $layoutCode)
            ->where('global_line_no', $globalLineNo)
            ->get()
            ->getRow();
    }

    /**
     * Snap a raw line range so start/end align to complete ayahs only.
     *
     * @return array{
     *   line_from:int,
     *   line_to:int,
     *   surah_id_start:int,
     *   ayah_from:int,
     *   surah_id_end:int,
     *   ayah_to:int,
     *   page_from:int,
     *   line_on_page_from:int,
     *   page_to:int,
     *   line_on_page_to:int,
     *   lines_count:int,
     *   lines_requested:int
     * }
     */
    public function snapRangeToFullAyahs(string $layoutCode, int $lineFrom, int $lineTo, int $linesRequested = 0): array
    {
        if ($lineFrom > $lineTo) {
            [$lineFrom, $lineTo] = [$lineTo, $lineFrom];
        }

        $startLine = $this->getLine($layoutCode, $lineFrom);
        $endLine   = $this->getLine($layoutCode, $lineTo);

        if (! $startLine || ! $endLine) {
            return $this->emptyRange($linesRequested);
        }

        $snappedFrom = $this->findAyahStartLine($layoutCode, (int) $startLine->surah_id_start, (int) $startLine->ayah_start);
        $snappedTo   = $this->findAyahEndLine($layoutCode, (int) $endLine->surah_id_end, (int) $endLine->ayah_end);

        if ($snappedFrom > $snappedTo) {
            $snappedTo = $snappedFrom;
        }

        $fromRow = $this->getLine($layoutCode, $snappedFrom);
        $toRow   = $this->getLine($layoutCode, $snappedTo);

        return [
            'line_from'          => $snappedFrom,
            'line_to'            => $snappedTo,
            'surah_id_start'     => (int) ($fromRow->surah_id_start ?? 0),
            'ayah_from'          => (int) ($fromRow->ayah_start ?? 0),
            'surah_id_end'       => (int) ($toRow->surah_id_end ?? 0),
            'ayah_to'            => (int) ($toRow->ayah_end ?? 0),
            'page_from'          => (int) ($fromRow->page_no ?? 0),
            'line_on_page_from'  => (int) ($fromRow->line_on_page ?? 0),
            'page_to'            => (int) ($toRow->page_no ?? 0),
            'line_on_page_to'    => (int) ($toRow->line_on_page ?? 0),
            'lines_count'        => max(0, $snappedTo - $snappedFrom + 1),
            'lines_requested'    => $linesRequested > 0 ? $linesRequested : max(0, $lineTo - $lineFrom + 1),
        ];
    }

    /**
     * Build N lines forward from cursor; result uses full-ayah snap.
     */
    public function linesFromCursor(string $layoutCode, int $cursorLine, int $lineCount): array
    {
        $lineCount = max(1, $lineCount);
        $rawFrom   = $cursorLine + 1;
        $rawTo     = $cursorLine + $lineCount;

        return $this->snapRangeToFullAyahs($layoutCode, $rawFrom, $rawTo, $lineCount);
    }

    public function globalLineAtAyahStart(string $layoutCode, int $surahId, int $ayah): int
    {
        return $this->findAyahStartLine($layoutCode, $surahId, $ayah);
    }

    public function globalLineAtAyahEnd(string $layoutCode, int $surahId, int $ayah): int
    {
        return $this->findAyahEndLine($layoutCode, $surahId, $ayah);
    }

    /**
     * Mushaf page (1–611 Indo-Pak) containing the start of an ayah.
     */
    public function pageForAyah(string $layoutCode, int $surahId, int $ayah): int
    {
        $line = $this->globalLineAtAyahStart($layoutCode, $surahId, $ayah);
        $row  = $this->getLine($layoutCode, $line);

        return $row ? max(1, (int) $row->page_no) : 1;
    }

    /**
     * Surah/ayah span covered by one mushaf page.
     *
     * @return array<string, mixed>
     */
    public function getPageBounds(string $layoutCode, int $pageNo): array
    {
        $pageNo = max(1, min(\Config\QuranReference::TOTAL_PAGES, $pageNo));

        $rows = $this->db->table('quran_mushaf_lines')
            ->where('layout_code', $layoutCode)
            ->where('page_no', $pageNo)
            ->orderBy('line_on_page', 'ASC')
            ->get()
            ->getResultArray();

        if ($rows === []) {
            return $this->emptyRange(0);
        }

        $first = $rows[0];
        $last  = $rows[count($rows) - 1];

        return [
            'line_from'          => (int) ($first['global_line_no'] ?? 0),
            'line_to'            => (int) ($last['global_line_no'] ?? 0),
            'surah_id_start'     => (int) ($first['surah_id_start'] ?? 0),
            'ayah_from'          => (int) ($first['ayah_start'] ?? 0),
            'surah_id_end'       => (int) ($last['surah_id_end'] ?? 0),
            'ayah_to'            => (int) ($last['ayah_end'] ?? 0),
            'page_from'          => $pageNo,
            'line_on_page_from'  => 1,
            'page_to'            => $pageNo,
            'line_on_page_to'    => (int) ($last['line_on_page'] ?? 16),
            'lines_count'        => count($rows),
            'lines_requested'    => 0,
        ];
    }

    /**
     * Snap a surah/ayah start and end to full ayahs (for labels and validation).
     *
     * @return array<string, mixed>
     */
    public function snapAyahRange(
        string $layoutCode,
        int $startSurah,
        int $startAyah,
        int $endSurah,
        int $endAyah,
        int $linesRequested = 0
    ): array {
        $from = $this->globalLineAtAyahStart($layoutCode, $startSurah, $startAyah);
        $to   = $this->globalLineAtAyahEnd($layoutCode, $endSurah, $endAyah);

        if ($from <= 0 || $to <= 0) {
            return $this->emptyRange($linesRequested);
        }

        if ($to < $from) {
            $to = $from;
        }

        return $this->snapRangeToFullAyahs($layoutCode, $from, $to, $linesRequested);
    }

    /**
     * Estimated end ayah after N mushaf lines from a start ayah.
     *
     * @return array<string, mixed>
     */
    public function suggestedEndFromAyahStart(string $layoutCode, int $startSurah, int $startAyah, int $lineCount): array
    {
        $lineCount = max(1, $lineCount);
        $from      = $this->globalLineAtAyahStart($layoutCode, $startSurah, $startAyah);

        if ($from <= 0) {
            return $this->emptyRange($lineCount);
        }

        return $this->snapRangeToFullAyahs($layoutCode, $from, $from + $lineCount - 1, $lineCount);
    }

    /**
     * Human-readable label for a line range.
     */
    public function formatRangeLabel(array $range): string
    {
        if (empty($range['line_from'])) {
            return '—';
        }

        $pagePart = sprintf(
            'Page %d L%d – Page %d L%d',
            $range['page_from'],
            $range['line_on_page_from'],
            $range['page_to'],
            $range['line_on_page_to']
        );

        $surahStart = $this->surahName((int) ($range['surah_id_start'] ?? 0));
        $surahEnd   = $this->surahName((int) ($range['surah_id_end'] ?? 0));

        if ($range['surah_id_start'] === $range['surah_id_end']) {
            $ayahPart = sprintf('%s: Ayah %d – %d', $surahStart, $range['ayah_from'], $range['ayah_to']);
        } else {
            $ayahPart = sprintf(
                '%s %d – %s %d',
                $surahStart,
                $range['ayah_from'],
                $surahEnd,
                $range['ayah_to']
            );
        }

        return $pagePart . ' · ' . $ayahPart;
    }

    protected function findAyahStartLine(string $layoutCode, int $surahId, int $ayah): int
    {
        $row = $this->db->table('quran_mushaf_lines')
            ->where('layout_code', $layoutCode)
            ->where('surah_id_start', $surahId)
            ->where('ayah_start', $ayah)
            ->orderBy('global_line_no', 'ASC')
            ->limit(1)
            ->get()
            ->getRow();

        if ($row) {
            return (int) $row->global_line_no;
        }

        $row = $this->db->table('quran_mushaf_lines')
            ->where('layout_code', $layoutCode)
            ->groupStart()
                ->where('surah_id_start', $surahId)
                ->where('ayah_start <=', $ayah)
            ->groupEnd()
            ->groupStart()
                ->where('surah_id_end', $surahId)
                ->where('ayah_end >=', $ayah)
            ->groupEnd()
            ->orderBy('global_line_no', 'ASC')
            ->limit(1)
            ->get()
            ->getRow();

        return $row ? (int) $row->global_line_no : 1;
    }

    protected function findAyahEndLine(string $layoutCode, int $surahId, int $ayah): int
    {
        $row = $this->db->table('quran_mushaf_lines')
            ->where('layout_code', $layoutCode)
            ->where('surah_id_end', $surahId)
            ->where('ayah_end', $ayah)
            ->orderBy('global_line_no', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();

        if ($row) {
            return (int) $row->global_line_no;
        }

        $row = $this->db->table('quran_mushaf_lines')
            ->where('layout_code', $layoutCode)
            ->groupStart()
                ->where('surah_id_start', $surahId)
                ->where('ayah_start <=', $ayah)
            ->groupEnd()
            ->groupStart()
                ->where('surah_id_end', $surahId)
                ->where('ayah_end >=', $ayah)
            ->groupEnd()
            ->orderBy('global_line_no', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();

        return $row ? (int) $row->global_line_no : 1;
    }

    protected function surahName(int $surahId): string
    {
        if ($surahId <= 0) {
            return '—';
        }

        $row = $this->db->table('quran_surahs')
            ->select('surah_name_en')
            ->where('surah_id', $surahId)
            ->get()
            ->getRow();

        return $row->surah_name_en ?? ('Surah ' . $surahId);
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyRange(int $linesRequested = 0): array
    {
        return [
            'line_from'         => 0,
            'line_to'           => 0,
            'surah_id_start'    => 0,
            'ayah_from'         => 0,
            'surah_id_end'      => 0,
            'ayah_to'           => 0,
            'page_from'         => 0,
            'line_on_page_from' => 0,
            'page_to'           => 0,
            'line_on_page_to'   => 0,
            'lines_count'       => 0,
            'lines_requested'   => $linesRequested,
        ];
    }
}
