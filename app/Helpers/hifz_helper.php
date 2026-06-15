<?php

/**
 * Hifz program helpers.
 */

require_once __DIR__ . '/hifz/schema.php';

if (! function_exists('hifzMushafLayoutCode')) {
    /**
     * Active mushaf layout (v1: Indo-Pak 16-line only).
     */
    function hifzMushafLayoutCode(): string
    {
        return 'indopak_16';
    }
}

if (! function_exists('hifzSurahNameMap')) {
    /**
     * @return array<int, array{surah_name_ar:string,surah_name_en:string}>
     */
    function hifzSurahNameMap(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $map = [];
        $db  = \Config\Database::connect();

        if ($db->tableExists('quran_surahs')) {
            foreach ($db->table('quran_surahs')->get()->getResultArray() as $row) {
                $id = (int) ($row['surah_id'] ?? 0);
                if ($id > 0) {
                    $map[$id] = [
                        'surah_name_ar' => $row['surah_name_ar'] ?? '',
                        'surah_name_en' => $row['surah_name_en'] ?? '',
                    ];
                }
            }
        }

        if ($map === []) {
            foreach (\Config\QuranReference::$surahMeta as $i => $meta) {
                $map[$i + 1] = [
                    'surah_name_ar' => $meta['name_ar'] ?? '',
                    'surah_name_en' => $meta['name_en'] ?? '',
                ];
            }
        }

        return $cache = $map;
    }
}

if (! function_exists('hifzBuildJuzCatalogEntry')) {
    /**
     * @param array<string, mixed> $juzRow
     * @return array<string, mixed>
     */
    function hifzBuildJuzCatalogEntry(int $juzNo, array $juzRow, array $surahMap): array
    {
        $idx     = $juzNo - 1;
        $bounds  = \Config\QuranReference::$juzBoundaries[$idx] ?? null;
        $juzMeta = \Config\QuranReference::$juzMeta[$idx] ?? ['name_ar' => '', 'name_en' => ''];

        $nameAr = trim((string) ($juzRow['juz_name_ar'] ?? $juzMeta['name_ar'] ?? ''));
        $nameEn = trim((string) ($juzRow['juz_name_en'] ?? $juzMeta['name_en'] ?? ''));

        $startId = (int) ($juzRow['start_surah_id'] ?? ($bounds['start']['surah'] ?? 0));
        $endId   = (int) ($juzRow['end_surah_id'] ?? ($bounds['end']['surah'] ?? 0));

        $startAr = $surahMap[$startId]['surah_name_ar'] ?? '';
        $endAr   = $surahMap[$endId]['surah_name_ar'] ?? '';
        $startEn = $surahMap[$startId]['surah_name_en'] ?? '';
        $endEn   = $surahMap[$endId]['surah_name_en'] ?? '';

        $rangeAr = $startAr === $endAr ? $startAr : ($startAr . ' … ' . $endAr);
        $rangeEn = $startEn === $endEn ? $startEn : ($startEn . ' … ' . $endEn);

        $paraAr = 'جزء ' . $juzNo;

        return [
            'juz_no'       => $juzNo,
            'label'        => 'Para ' . $juzNo,
            'name_ar'      => $nameAr,
            'name_en'      => $nameEn,
            'title'        => 'Para ' . $juzNo . ($nameEn !== '' ? ' — ' . $nameEn : ''),
            'title_ar'     => $paraAr . ($nameAr !== '' ? ' — ' . $nameAr : ''),
            'range_ar'     => $rangeAr,
            'range_en'     => $rangeEn,
            'start_surah'  => $startEn,
            'end_surah'    => $endEn,
        ];
    }
}

if (! function_exists('hifzJuzCatalog')) {
    /**
     * All 30 Quran paras with traditional Arabic names and surah ranges in Arabic.
     *
     * @return list<array<string, mixed>>
     */
    function hifzJuzCatalog(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $surahMap = hifzSurahNameMap();
        $db       = \Config\Database::connect();
        $out      = [];

        if ($db->tableExists('quran_juz_boundaries')) {
            $rows = $db->table('quran_juz_boundaries')
                ->orderBy('juz_no', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $juzNo = (int) ($row['juz_no'] ?? 0);
                if ($juzNo >= 1 && $juzNo <= 30) {
                    $out[] = hifzBuildJuzCatalogEntry($juzNo, $row, $surahMap);
                }
            }
        }

        if ($out === []) {
            for ($j = 1; $j <= 30; $j++) {
                $bounds = \Config\QuranReference::$juzBoundaries[$j - 1] ?? null;
                $out[]  = hifzBuildJuzCatalogEntry($j, [
                    'juz_no'         => $j,
                    'start_surah_id' => $bounds['start']['surah'] ?? 0,
                    'end_surah_id'   => $bounds['end']['surah'] ?? 0,
                ], $surahMap);
            }
        }

        return $cache = $out;
    }
}

if (! function_exists('hifzParaCatalogEntry')) {
    /**
     * @return array{juz_no:int,name_en:string,name_ar:string,title:string,title_ar:string}
     */
    function hifzParaCatalogEntry(int $juzNo): array
    {
        $juzNo = max(1, min(30, $juzNo));
        foreach (hifzJuzCatalog() as $item) {
            if ((int) ($item['juz_no'] ?? 0) === $juzNo) {
                return [
                    'juz_no'    => $juzNo,
                    'name_en'   => (string) ($item['name_en'] ?? ''),
                    'name_ar'   => (string) ($item['name_ar'] ?? ''),
                    'title'     => (string) ($item['title'] ?? ('Para ' . $juzNo)),
                    'title_ar'  => (string) ($item['title_ar'] ?? ('جزء ' . $juzNo)),
                ];
            }
        }

        return [
            'juz_no'    => $juzNo,
            'name_en'   => '',
            'name_ar'   => 'جزء ' . $juzNo,
            'title'     => 'Para ' . $juzNo,
            'title_ar'  => 'جزء ' . $juzNo,
        ];
    }
}

if (! function_exists('hifzJuzTitle')) {
    function hifzJuzTitle(int $juzNo, bool $preferArabic = true): string
    {
        $juzNo = max(1, min(30, $juzNo));
        foreach (hifzJuzCatalog() as $item) {
            if ((int) $item['juz_no'] === $juzNo) {
                if ($preferArabic && ! empty($item['title_ar'])) {
                    return (string) $item['title_ar'];
                }

                return (string) ($item['title'] ?? ('Para ' . $juzNo));
            }
        }

        return $preferArabic ? ('جزء ' . $juzNo) : ('Para ' . $juzNo);
    }
}

if (! function_exists('hifzParseJuzList')) {
    /**
     * @return list<int>
     */
    function hifzParseJuzList(?string $csv): array
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

        sort($out);

        return array_values(array_unique($out));
    }
}

if (! function_exists('hifzFormatJuzList')) {
    function hifzFormatJuzList(array $juzList): string
    {
        $juzList = hifzParseJuzList(implode(',', $juzList));

        return $juzList === [] ? '' : implode(',', $juzList);
    }
}

if (! function_exists('hifzCompletedBlockLooksFromEnd')) {
    function hifzCompletedBlockLooksFromEnd(array $completedJuz): bool
    {
        if ($completedJuz === []) {
            return false;
        }

        $first = $completedJuz[0];
        if ($first <= 1) {
            return false;
        }

        $expected = range($first, min(30, $first + count($completedJuz) - 1));

        if ($completedJuz === $expected) {
            return $first >= 11;
        }

        return $first >= 21;
    }
}

if (! function_exists('hifzFormatJuzRangeLabel')) {
    function hifzFormatJuzRangeLabel(int $start, int $end, array $catalog): string
    {
        $startMeta = $catalog[$start] ?? null;
        $endMeta   = $catalog[$end] ?? null;

        if ($start === $end) {
            return (string) ($startMeta['title_ar'] ?? $startMeta['title'] ?? ('جزء ' . $start));
        }

        $from = $startMeta['name_ar'] ?? '';
        $to   = $endMeta['name_ar'] ?? '';

        return 'جزء ' . $start . '–' . $end . ' (' . $from . ' … ' . $to . ')';
    }
}

if (! function_exists('hifzSuggestCurrentJuz')) {
    /**
     * Guess active Sabaq para from completed set.
     */
    function hifzSuggestCurrentJuz(array $completedJuz): int
    {
        $completedJuz = hifzParseJuzList(hifzFormatJuzList($completedJuz));
        if ($completedJuz === []) {
            return 1;
        }

        $first = $completedJuz[0];
        if ($first > 1 && hifzCompletedBlockLooksFromEnd($completedJuz)) {
            return max(1, min(30, $first - 1));
        }

        $last = $completedJuz[count($completedJuz) - 1];

        return min(30, $last + 1);
    }
}

if (! function_exists('hifzCompletedJuzSummary')) {
    function hifzCompletedJuzSummary(array $completedJuz, int $currentJuz = 1, int $linesInCurrent = 0): string
    {
        $completedJuz = hifzParseJuzList(hifzFormatJuzList($completedJuz));
        $currentJuz   = max(1, min(30, $currentJuz));

        if ($completedJuz === []) {
            return 'None selected — student will start from Para ' . $currentJuz;
        }

        $catalog = [];
        foreach (hifzJuzCatalog() as $item) {
            $catalog[(int) $item['juz_no']] = $item;
        }

        $parts    = [];
        $runStart = $completedJuz[0];
        $runEnd   = $completedJuz[0];

        for ($i = 1, $n = count($completedJuz); $i < $n; $i++) {
            if ($completedJuz[$i] === $runEnd + 1) {
                $runEnd = $completedJuz[$i];
                continue;
            }
            $parts[]  = hifzFormatJuzRangeLabel($runStart, $runEnd, $catalog);
            $runStart = $completedJuz[$i];
            $runEnd   = $completedJuz[$i];
        }
        $parts[] = hifzFormatJuzRangeLabel($runStart, $runEnd, $catalog);

        $txt = 'Completed: ' . implode(' · ', $parts);
        $txt .= ' (' . count($completedJuz) . ' para' . (count($completedJuz) === 1 ? '' : 's') . ')';
        $txt .= ' · Current: ' . hifzJuzTitle($currentJuz);
        if ($linesInCurrent > 0) {
            $txt .= ' (' . $linesInCurrent . ' lines done)';
        }

        return $txt;
    }
}

if (! function_exists('hifzIsSurahWiseSequence')) {
    function hifzIsSurahWiseSequence(?string $sequence): bool
    {
        return in_array($sequence, ['surah_reverse_full', 'surah_reverse_ayah_reverse'], true);
    }
}

if (! function_exists('hifzSurahReverseProgressSummary')) {
    function hifzSurahReverseProgressSummary(int $learnedFromSurah, int $currentSurah, int $currentAyah): string
    {
        $learnedFromSurah = max(1, min(114, $learnedFromSurah));
        $currentSurah     = max(1, min(114, $currentSurah));
        $currentAyah      = max(0, $currentAyah);
        $map              = hifzSurahNameMap();
        $fromName         = $map[$learnedFromSurah]['surah_name_en'] ?? ('Surah ' . $learnedFromSurah);

        if ($learnedFromSurah >= 114) {
            $donePart = 'No surahs marked complete after the end';
        } elseif ($learnedFromSurah + 1 === 114) {
            $donePart = 'Surah 114 (An-Nas) complete';
        } else {
            $donePart = 'Surahs ' . ($learnedFromSurah + 1) . '–114 complete (after ' . $fromName . ')';
        }

        $curName = $map[$currentSurah]['surah_name_en'] ?? ('Surah ' . $currentSurah);
        $range   = hifzSurahReverseAyahRange($learnedFromSurah, $currentSurah, $currentAyah, 'surah_reverse_full');
        $ayahPart = $currentAyah > 0
            ? (' · Current: ' . $curName . ' up to ayah ' . $currentAyah)
            : (' · Current: ' . $curName . ' (not started yet)');
        if ($range['hint'] !== '') {
            $ayahPart .= ' · ' . $range['hint'];
        }

        return $donePart . $ayahPart;
    }
}

if (! function_exists('hifzCursorFromSurahProgress')) {
    function hifzCursorFromSurahProgress(int $currentSurah, int $currentAyah): int
    {
        $currentSurah = max(1, min(114, $currentSurah));
        $currentAyah  = max(0, $currentAyah);
        if ($currentAyah <= 0) {
            return 0;
        }

        $lines  = new \App\Libraries\HifzLineResolver();
        $layout = hifzMushafLayoutCode();

        return max(0, $lines->globalLineAtAyahEnd($layout, $currentSurah, $currentAyah));
    }
}

if (! function_exists('hifzEnrollmentProgress')) {
    /**
     * @return array{completed_juz:list<int>,current_juz:int,lines_in_current:int,summary:string,legacy_done:int}
     */
    function hifzEnrollmentProgress(?object $enrollment): array
    {
        if (! $enrollment) {
            return [
                'completed_juz'    => [],
                'current_juz'      => 1,
                'lines_in_current' => 0,
                'summary'          => hifzCompletedJuzSummary([], 1, 0),
                'legacy_done'      => 0,
            ];
        }

        $list = hifzParseJuzList($enrollment->completed_juz_list ?? '');
        $curJ = (int) ($enrollment->current_juz ?? 1);
        $curL = (int) ($enrollment->current_juz_memorized_lines ?? 0);
        $seq  = (string) ($enrollment->memorization_sequence ?? 'para_forward');

        if (hifzIsSurahWiseSequence($seq)) {
            $learnedFrom = (int) ($enrollment->reverse_learned_from_surah ?? 114);
            $curSurah    = hifzSurahReverseCurrentSurah($learnedFrom);
            $curAyah     = (int) ($enrollment->current_sabaq_ayah ?? 0);

            return [
                'completed_juz'    => $list,
                'current_juz'      => $curJ,
                'lines_in_current' => $curL,
                'summary'          => hifzSurahReverseProgressSummary($learnedFrom, $curSurah, $curAyah),
                'legacy_done'      => 0,
                'learned_from_surah' => $learnedFrom,
                'current_surah'      => $curSurah,
                'current_ayah'       => $curAyah,
            ];
        }

        if ($list === [] && (int) ($enrollment->current_global_line ?? 0) > 0) {
            $prog = hifzProgressFromCursor((int) $enrollment->current_global_line);
            $legacy = (int) ($prog['completed_paras'] ?? 0);
            if ($legacy > 0) {
                $list = range(1, $legacy);
            }
            if ($curL <= 0) {
                $curL = (int) ($prog['lines_in_current'] ?? 0);
            }
            if ($curJ <= 1) {
                $curJ = (int) ($prog['current_juz'] ?? 1);
            }
        }

        if ($curJ <= 0) {
            $curJ = hifzSuggestCurrentJuz($list);
        }

        $legacyDone = 0;
        foreach (range(1, 30) as $j) {
            if (in_array($j, $list, true)) {
                $legacyDone = $j;
            } else {
                break;
            }
        }

        return [
            'completed_juz'    => $list,
            'current_juz'      => $curJ,
            'lines_in_current' => $curL,
            'summary'          => hifzCompletedJuzSummary($list, $curJ, $curL),
            'legacy_done'      => $legacyDone,
        ];
    }
}

if (! function_exists('hifzResolveEnrollmentCursor')) {
    /**
     * Global mushaf line for Sabqi/Manzil (from enrollment row or derived from current para).
     */
    function hifzResolveEnrollmentCursor(?object $enrollment): int
    {
        if (! $enrollment) {
            return 0;
        }

        $cursor = (int) ($enrollment->current_global_line ?? 0);

        if ($cursor > 0) {
            return $cursor;
        }

        $progress = hifzEnrollmentProgress($enrollment);

        return hifzCursorFromEnrollment(
            $progress['completed_juz'],
            (int) ($progress['current_juz'] ?? 1),
            (int) ($progress['lines_in_current'] ?? 0)
        );
    }
}

if (! function_exists('hifzAutoManzilPool')) {
    /**
     * Manzil paras from current memorization (no manual completed-juz list).
     *
     * @return list<int>
     */
    function hifzAutoManzilPool(?object $enrollment): array
    {
        if (! $enrollment) {
            return [];
        }

        $sequence = (string) ($enrollment->memorization_sequence ?? 'para_forward');

        if (hifzIsSurahWiseSequence($sequence)) {
            return hifzParseJuzList($enrollment->completed_juz_list ?? '');
        }

        $cursor = hifzResolveEnrollmentCursor($enrollment);

        return (new \App\Libraries\HifzManzilCalculator())->manzilPoolFromCursor($cursor);
    }
}

if (! function_exists('hifzSabqiProgressHint')) {
    function hifzSabqiProgressHint(?object $enrollment): string
    {
        $cursor = hifzResolveEnrollmentCursor($enrollment);

        if ($cursor <= 0) {
            return 'Memorization cursor not set — update current para / lines on enrollment.';
        }

        $layout = hifzMushafLayoutCode();
        $snap   = (new \App\Libraries\HifzManzilCalculator())->paraProgressSnapshot($cursor, $layout);
        $juz    = (int) ($snap['juz_no'] ?? 1);
        $pct    = (float) ($snap['progress_pct'] ?? 0);

        if ($pct >= 50) {
            return sprintf(
                'Para %d is %.0f%% memorized — Sabqi is Para %d. Manzil pool: Para %d–30.',
                $juz,
                $pct,
                $juz,
                (int) ($snap['manzil_start'] ?? $juz + 1)
            );
        }

        return sprintf(
            'Para %d is %.0f%% memorized — Sabqi is partial Para %d (from earlier lines). Manzil pool: Para %d–30.',
            $juz,
            $pct,
            $juz,
            (int) ($snap['manzil_start'] ?? $juz + 2)
        );
    }
}

if (! function_exists('hifzCursorFromEnrollment')) {
    function hifzCursorFromEnrollment(array $completedJuz, int $currentJuz, int $linesInCurrent = 0): int
    {
        $layout         = hifzMushafLayoutCode();
        $sabqi          = new \App\Libraries\HifzSabqiCalculator();
        $currentJuz     = max(1, min(30, $currentJuz));
        $linesInCurrent = max(0, $linesInCurrent);
        $completedJuz   = hifzParseJuzList(hifzFormatJuzList($completedJuz));

        $bounds = $sabqi->juzBounds($layout, $currentJuz);
        $start  = (int) ($bounds['start_line'] ?? 0);
        $end    = (int) ($bounds['end_line'] ?? 0);

        if ($start <= 0) {
            return 0;
        }

        if ($linesInCurrent > 0) {
            return min($start + $linesInCurrent - 1, $end > 0 ? $end : $start + $linesInCurrent - 1);
        }

        if (in_array($currentJuz, $completedJuz, true)) {
            return $end > 0 ? $end : $start;
        }

        return $start;
    }
}

if (! function_exists('hifzCompletedParasSummary')) {
    function hifzCompletedParasSummary(int $completedParas): string
    {
        $completedParas = max(0, min(30, $completedParas));
        if ($completedParas <= 0) {
            return 'None — student will start from Para 1';
        }

        return hifzCompletedJuzSummary(range(1, $completedParas), min(30, $completedParas + 1), 0);
    }
}

if (! function_exists('hifzManzilParasPerDayOptions')) {
    /**
     * Manzil revision: how many full paras to recite per school day (rotating).
     *
     * @return array<int, string>
     */
    function hifzManzilParasPerDayOptions(): array
    {
        return [
            1 => '1 Para / day',
            2 => '2 Paras / day',
            3 => '3 Paras / day',
        ];
    }
}

if (! function_exists('hifzParaTotalLines')) {
    function hifzParaTotalLines(): int
    {
        return 320;
    }
}

if (! function_exists('hifzParaHalfLines')) {
    /** Lines threshold: below = two Sabqi paras; at/above = one Sabqi para. */
    function hifzParaHalfLines(): int
    {
        return (int) floor(hifzParaTotalLines() / 2);
    }
}

if (! function_exists('hifzEnrollmentPoolsSummary')) {
    function hifzEnrollmentPoolsSummary(array $paras): string
    {
        if ($paras === []) {
            return 'None';
        }

        return 'Paras ' . implode(', ', $paras);
    }
}

if (! function_exists('hifzComputeEnrollmentPools')) {
    /**
     * Derive Sabqi and Manzil pools from plan order, current Mutalia para, and lines done.
     *
     * @return array{
     *   current_para_no:int,
     *   sabqi_paras:list<int>,
     *   manzil_pool_paras:list<int>,
     *   summary_sabqi:string,
     *   summary_manzil:string
     * }
     */
    function hifzComputeEnrollmentPools(string $sequence, int $currentPara, int $linesDone): array
    {
        $currentPara = max(1, min(30, $currentPara));
        $linesDone   = max(0, min(hifzParaTotalLines(), $linesDone));
        $half        = hifzParaHalfLines();
        $dualSabqi   = $linesDone < $half;

        $sabqi  = [];
        $manzil = [];

        if (hifzIsParaReverseSequence($sequence)) {
            if ($dualSabqi) {
                // Reverse + < half lines: current + next higher (e.g. current 24 → Sabqi 24, 25)
                if ($currentPara < 30) {
                    $sabqi = [$currentPara, $currentPara + 1];
                } else {
                    $sabqi = [$currentPara];
                }
            } else {
                $sabqi = [$currentPara];
            }

            $manzilStart = ($sabqi !== [] ? max($sabqi) : $currentPara) + 1;
            for ($p = $manzilStart; $p <= 30; $p++) {
                $manzil[] = $p;
            }
        } else {
            if ($dualSabqi && $currentPara > 1) {
                $sabqi = [$currentPara - 1, $currentPara];
            } else {
                $sabqi = [$currentPara];
            }
            $manzilEnd = $currentPara - count($sabqi);
            for ($p = 1; $p <= $manzilEnd; $p++) {
                $manzil[] = $p;
            }
        }

        return [
            'current_para_no'     => $currentPara,
            'sabqi_paras'         => $sabqi,
            'manzil_pool_paras'   => $manzil,
            'summary_sabqi'       => hifzEnrollmentPoolsSummary($sabqi),
            'summary_manzil'      => hifzEnrollmentPoolsSummary($manzil),
        ];
    }
}

if (! function_exists('hifzParaOnlySequenceOptions')) {
    /**
     * @return array<string, string>
     */
    function hifzParaOnlySequenceOptions(): array
    {
        return [
            'para_forward' => 'Forward (Para 1 → 30)',
            'para_reverse' => 'Reverse (Para 30 → 1)',
        ];
    }
}

if (! function_exists('hifzNextParaNo')) {
    function hifzNextParaNo(int $currentPara, string $sequence): int
    {
        $currentPara = max(1, min(30, $currentPara));
        $sequence    = strtolower(trim($sequence));

        if (hifzIsParaReverseSequence($sequence)) {
            return max(1, $currentPara - 1);
        }

        return min(30, $currentPara + 1);
    }
}

if (! function_exists('hifzNormalizeMemorizationSequence')) {
    function hifzNormalizeMemorizationSequence(?string $sequence): string
    {
        $sequence = strtolower(trim((string) $sequence));

        if (in_array($sequence, ['surah_reverse_full', 'surah_reverse_ayah_reverse', 'para_reverse'], true)) {
            return 'para_reverse';
        }

        if ($sequence === 'para_forward') {
            return 'para_forward';
        }

        return 'para_forward';
    }
}

if (! function_exists('hifzIsParaReverseSequence')) {
    function hifzIsParaReverseSequence(?string $sequence): bool
    {
        return hifzNormalizeMemorizationSequence($sequence) === 'para_reverse';
    }
}

if (! function_exists('hifzParaProgressLabel')) {
    function hifzParaProgressLabel(int $paraNo, int $linesDone): string
    {
        $total = hifzParaTotalLines();
        $linesDone = max(0, min($total, $linesDone));

        return sprintf('Para %d · %d/%d lines', max(1, min(30, $paraNo)), $linesDone, $total);
    }
}

if (! function_exists('hifzOverallQuranPercent')) {
    /**
     * Full-Quran memorization progress (30 paras × 320 lines).
     *
     * @return array{percent:int,memorized_lines:int,total_lines:int,label:string}
     */
    function hifzOverallQuranPercent(?object $enrollment): array
    {
        $perPara   = hifzParaTotalLines();
        $total     = 30 * $perPara;
        $memorized = 0;

        if ($enrollment) {
            $completed = hifzParseJuzList($enrollment->completed_juz_list ?? '');
            foreach ($completed as $j) {
                if ($j >= 1 && $j <= 30) {
                    $memorized += $perPara;
                }
            }

            $currentPara = (int) ($enrollment->current_para_no ?? $enrollment->current_juz ?? 0);
            $currentLines = max(0, min($perPara, (int) ($enrollment->current_juz_memorized_lines ?? 0)));

            if ($currentPara >= 1 && $currentPara <= 30 && ! in_array($currentPara, $completed, true)) {
                $memorized += $currentLines;
            }
        }

        $percent = $total > 0 ? (int) round(100 * $memorized / $total) : 0;
        $percent = min(100, max(0, $percent));

        return [
            'percent'          => $percent,
            'memorized_lines'  => $memorized,
            'total_lines'      => $total,
            'label'            => sprintf(
                '%d%% of Quran (%s / %s lines)',
                $percent,
                number_format($memorized),
                number_format($total)
            ),
        ];
    }
}

if (! function_exists('hifzLessonLabel')) {
    function hifzLessonLabel(object $lesson): string
    {
        $para  = (int) ($lesson->para_no ?? 0);
        $lines = (int) ($lesson->lines_count ?? 0);
        $from  = (int) ($lesson->line_from ?? 0);
        $to    = (int) ($lesson->line_to ?? 0);
        $date  = (string) ($lesson->entry_date ?? '');

        $base = sprintf('Para %d · %d lines', $para, $lines);
        if ($from > 0 && $to > 0) {
            $base .= sprintf(' (lines %d–%d)', $from, $to);
        }

        if ($date !== '') {
            $base .= ' (' . $date . ')';
        }

        return $base;
    }
}

if (! function_exists('hifzMemorizationSequenceOptions')) {
    /**
     * @return array<string, string>
     */
    function hifzMemorizationSequenceOptions(): array
    {
        return array_merge(hifzParaOnlySequenceOptions(), [
            'surah_reverse_full'        => 'Surah-wise (last surah first, ayah 1 forward)',
            'surah_reverse_ayah_reverse'  => 'Surah-wise (last surah first, last ayah backward)',
        ]);
    }
}

if (! function_exists('hifzMutaliaTargetSurah')) {
    /**
     * Surah for today's Mutalia reading (locked from enrollment).
     */
    function hifzMutaliaTargetSurah(?object $enrollment): int
    {
        if (! $enrollment) {
            return 1;
        }

        $sequence = (string) ($enrollment->memorization_sequence ?? 'para_forward');
        if (hifzIsSurahWiseSequence($sequence)) {
            return hifzSurahReverseCurrentSurah((int) ($enrollment->reverse_learned_from_surah ?? 114));
        }

        $cur = (int) ($enrollment->current_sabaq_surah_id ?? 0);

        return $cur > 0 ? $cur : 1;
    }
}

if (! function_exists('hifzSurahName')) {
    function hifzSurahName(int $surahId, bool $withArabic = true): string
    {
        $map  = hifzSurahNameMap();
        $meta = $map[$surahId] ?? ['surah_name_en' => 'Surah ' . $surahId, 'surah_name_ar' => ''];
        $en   = $meta['surah_name_en'] ?? ('Surah ' . $surahId);
        $ar   = trim($meta['surah_name_ar'] ?? '');

        if ($withArabic && $ar !== '') {
            return $surahId . '. ' . $en . ' — ' . $ar;
        }

        return $surahId . '. ' . $en;
    }
}

if (! function_exists('hifzSurahReverseCurrentSurah')) {
    /**
     * Current Sabaq surah implied by last completed reverse block (N → student is on N−1).
     */
    function hifzSurahReverseCurrentSurah(int $learnedFrom): int
    {
        $learnedFrom = max(1, min(114, $learnedFrom));

        if ($learnedFrom >= 114) {
            return 113;
        }

        return max(1, $learnedFrom - 1);
    }
}

if (! function_exists('hifzSurahReverseApplicableSegments')) {
    /**
     * Juz segments of a surah reachable when entering from completed surah N.
     *
     * @return list<array{juz:int,ayah_from:int,ayah_to:int}>
     */
    function hifzSurahReverseApplicableSegments(int $learnedFrom, int $currentSurah, string $sequence = 'surah_reverse_full'): array
    {
        if ($sequence !== 'surah_reverse_full') {
            return [];
        }

        $segments = \Config\QuranReference::surahJuzSegments($currentSurah);
        if ($segments === []) {
            return [];
        }

        $entryJuz = \Config\QuranReference::juzForAyah(max(1, min(114, $learnedFrom)), 1);
        usort($segments, static fn ($a, $b) => (int) $b['juz'] <=> (int) $a['juz']);

        $applicable = array_values(array_filter(
            $segments,
            static fn ($seg) => (int) $seg['juz'] <= $entryJuz
        ));

        return $applicable !== [] ? $applicable : $segments;
    }
}

if (! function_exists('hifzSurahReverseActiveSegment')) {
    /**
     * Active juz segment for surah-reverse (ayah 1 forward) enrollment.
     *
     * When moving from completed surah N to N−1, the student must finish the portion
     * of the current surah in the same (higher) juz before the earlier juz portion.
     *
     * @return array{juz:int,ayah_from:int,ayah_to:int}|null
     */
    function hifzSurahReverseActiveSegment(int $learnedFrom, int $currentSurah, int $currentAyah, string $sequence = 'surah_reverse_full'): ?array
    {
        if ($sequence !== 'surah_reverse_full') {
            return null;
        }

        $applicable = hifzSurahReverseApplicableSegments($learnedFrom, $currentSurah, $sequence);
        if (count($applicable) <= 1) {
            return null;
        }

        $active = $applicable[0];

        foreach ($applicable as $i => $seg) {
            $from = (int) $seg['ayah_from'];
            $to   = (int) $seg['ayah_to'];

            if ($currentAyah <= 0) {
                return $seg;
            }

            if ($currentAyah >= $from && $currentAyah < $to) {
                return $seg;
            }

            if ($currentAyah >= $to) {
                if (isset($applicable[$i + 1])) {
                    $active = $applicable[$i + 1];
                    continue;
                }

                return $seg;
            }
        }

        return $active;
    }
}

if (! function_exists('hifzSurahReverseSegmentAtAyah')) {
    /**
     * @return array{juz:int,ayah_from:int,ayah_to:int}|null
     */
    function hifzSurahReverseSegmentAtAyah(int $learnedFrom, int $currentSurah, int $ayah, string $sequence = 'surah_reverse_full'): ?array
    {
        if ($ayah <= 0) {
            return null;
        }

        foreach (hifzSurahReverseApplicableSegments($learnedFrom, $currentSurah, $sequence) as $seg) {
            if ($ayah >= (int) $seg['ayah_from'] && $ayah <= (int) $seg['ayah_to']) {
                return $seg;
            }
        }

        return null;
    }
}

if (! function_exists('hifzSurahReverseNextPosition')) {
    /**
     * Next Sabaq/Mutalia ayah for surah-reverse (ayah 1 forward).
     *
     * @return array{surah_id:int,ayah:int}
     */
    function hifzSurahReverseNextPosition(int $learnedFrom, int $surahId, int $ayah, string $sequence = 'surah_reverse_full'): array
    {
        $applicable = hifzSurahReverseApplicableSegments($learnedFrom, $surahId, $sequence);

        if ($applicable === []) {
            $max = (int) (\Config\QuranReference::$surahAyahCounts[$surahId - 1] ?? 0);
            if ($ayah > 0 && $ayah < $max) {
                return ['surah_id' => $surahId, 'ayah' => $ayah + 1];
            }
            if ($surahId > 1) {
                return ['surah_id' => $surahId - 1, 'ayah' => 1];
            }

            return ['surah_id' => 1, 'ayah' => 1];
        }

        if ($ayah <= 0) {
            return ['surah_id' => $surahId, 'ayah' => (int) $applicable[0]['ayah_from']];
        }

        $seg = hifzSurahReverseSegmentAtAyah($learnedFrom, $surahId, $ayah, $sequence);
        if ($seg === null) {
            $seg = $applicable[0];
        }

        $to = (int) $seg['ayah_to'];
        if ($ayah < $to) {
            return ['surah_id' => $surahId, 'ayah' => $ayah + 1];
        }

        foreach ($applicable as $i => $candidate) {
            if ((int) $candidate['juz'] !== (int) $seg['juz']) {
                continue;
            }
            if (isset($applicable[$i + 1])) {
                return ['surah_id' => $surahId, 'ayah' => (int) $applicable[$i + 1]['ayah_from']];
            }
            break;
        }

        if ($surahId > 1) {
            $nextSurah = $surahId - 1;

            return ['surah_id' => $nextSurah, 'ayah' => hifzSurahReverseStartAyah($surahId, $nextSurah, $sequence)];
        }

        return ['surah_id' => 1, 'ayah' => 1];
    }
}

if (! function_exists('hifzSurahReverseAyahRange')) {
    /**
     * Allowed ayah range for surah-reverse enrollment dropdowns.
     *
     * @return array{min:int,max:int,hint:string,juz:int}
     */
    function hifzSurahReverseAyahRange(int $learnedFrom, int $currentSurah, int $currentAyah, string $sequence = 'surah_reverse_full'): array
    {
        $maxAyah = (int) (\Config\QuranReference::$surahAyahCounts[$currentSurah - 1] ?? 0);
        $default = ['min' => 1, 'max' => $maxAyah, 'hint' => '', 'juz' => 0];

        $segment = hifzSurahReverseActiveSegment($learnedFrom, $currentSurah, $currentAyah, $sequence);
        if ($segment === null) {
            return $default;
        }

        $from = (int) $segment['ayah_from'];
        $to   = (int) $segment['ayah_to'];
        $juz  = (int) $segment['juz'];
        $hint = $from === 1 && $to === $maxAyah
            ? ''
            : ('Para ' . $juz . ' portion only: ayahs ' . $from . '–' . $to);

        return ['min' => $from, 'max' => $to, 'hint' => $hint, 'juz' => $juz];
    }
}

if (! function_exists('hifzSurahReverseAyahInRange')) {
    function hifzSurahReverseAyahInRange(int $learnedFrom, int $currentSurah, int $currentAyah, string $sequence = 'surah_reverse_full'): bool
    {
        if ($currentAyah <= 0) {
            return true;
        }

        $range = hifzSurahReverseAyahRange($learnedFrom, $currentSurah, $currentAyah, $sequence);

        return $currentAyah >= $range['min'] && $currentAyah <= $range['max'];
    }
}

if (! function_exists('hifzSurahReverseStartAyah')) {
    /**
     * First Sabaq ayah when entering a surah from reverse path (juz-aware).
     */
    function hifzSurahReverseStartAyah(int $learnedFrom, int $currentSurah, string $sequence = 'surah_reverse_full'): int
    {
        $range = hifzSurahReverseAyahRange($learnedFrom, $currentSurah, 0, $sequence);

        return (int) $range['min'];
    }
}

if (! function_exists('hifzSurahJuzSegmentsMap')) {
    /**
     * @return array<int, list<array{juz:int,ayah_from:int,ayah_to:int}>>
     */
    function hifzSurahJuzSegmentsMap(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $out = [];
        for ($id = 1; $id <= 114; $id++) {
            $out[$id] = \Config\QuranReference::surahJuzSegments($id);
        }

        return $cache = $out;
    }
}

if (! function_exists('hifzSurahPickerOptions')) {
    /**
     * Surah list for Mutalia start correction dropdowns.
     *
     * @return list<array{surah_id:int,name_en:string,name_ar:string,ayah_count:int}>
     */
    function hifzSurahPickerOptions(): array
    {
        $map    = hifzSurahNameMap();
        $counts = \Config\QuranReference::$surahAyahCounts;
        $out    = [];

        for ($id = 1; $id <= 114; $id++) {
            $meta = $map[$id] ?? ['surah_name_en' => 'Surah ' . $id, 'surah_name_ar' => ''];
            $out[] = [
                'surah_id'   => $id,
                'name_en'    => $meta['surah_name_en'] ?? ('Surah ' . $id),
                'name_ar'    => $meta['surah_name_ar'] ?? '',
                'ayah_count' => (int) ($counts[$id - 1] ?? 0),
            ];
        }

        return $out;
    }
}

if (! function_exists('hifzRecitationQualityOptions')) {
    /**
     * @return array<string, string>
     */
    function hifzRecitationQualityOptions(): array
    {
        return [
            'excellent' => 'Excellent',
            'good'      => 'Good',
            'average'   => 'Average',
            'weak'      => 'Weak',
            'absent'    => 'Absent',
            'leave'     => 'Leave',
        ];
    }
}

if (! function_exists('hifzSabaqQualityAllowsNewMutalia')) {
    function hifzSabaqQualityAllowsNewMutalia(?string $quality): bool
    {
        return in_array(strtolower(trim((string) $quality)), ['excellent', 'good', 'average'], true);
    }
}

if (! function_exists('hifzSabaqQualityRepeatsMutalia')) {
    function hifzSabaqQualityRepeatsMutalia(?string $quality): bool
    {
        return strtolower(trim((string) $quality)) === 'weak';
    }
}

if (! function_exists('hifzManzilListenerTypes')) {
    /**
     * Who listened to today's Manzil recitation.
     *
     * @return array<string, string>
     */
    function hifzManzilListenerTypes(): array
    {
        return [
            'teacher' => 'Teacher',
            'fellow'  => 'Class fellow',
        ];
    }
}

if (! function_exists('activeHifzSections')) {
    /**
     * @return list<array{hifz_sec_id:int,section_name:string}>
     */
    function activeHifzSections(?int $campusId = null, ?int $sessionId = null): array
    {
        $campusId  = $campusId ?? (int) session('member_campusid');
        $sessionId = $sessionId ?? (int) session('member_sessionid');

        if ($campusId <= 0 || $sessionId <= 0) {
            return [];
        }

        $db = \Config\Database::connect();

        $builder = $db->table('hifz_sections')
            ->select('hifz_sec_id, section_name')
            ->where('campus_id', $campusId)
            ->where('session_id', $sessionId)
            ->where('status', 1);

        if ($db->fieldExists('sort_order', 'hifz_sections')) {
            $builder->orderBy('sort_order', 'ASC');
        }

        return $builder->orderBy('section_name', 'ASC')->get()->getResultArray();
    }
}

if (! function_exists('hifzClassSections')) {
    /**
     * Academic class sections under the campus dedicated Hifz class.
     *
     * @return list<array<string, mixed>>
     */
    function hifzClassSections(?object $campus = null): array
    {
        $campus = $campus ?? getCampusInfo();
        if (! $campus) {
            return [];
        }

        $db        = \Config\Database::connect();
        $campusId  = (int) $campus->campus_id;
        $hifzClass = (int) ($campus->hifz_class_id ?? 0);

        $builder = $db->table('class_section cs')
            ->select('cs.cls_sec_id, cs.section_id, c.class_id, c.class_name, s.section_name, CONCAT(c.class_name, " (", s.section_name, ")") AS sectionclassname')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->where('cs.campus_id', $campusId)
            ->where('cs.status', 1);

        if ($hifzClass > 0) {
            $builder->where('cs.class_id', $hifzClass);
        } else {
            $builder->where('c.is_hifz_class', 1);
        }

        return $builder->orderBy('s.section_name', 'ASC')->get()->getResultArray();
    }
}

if (! function_exists('hifzCursorFromProgress')) {
    /**
     * Map “completed paras + lines in current para” to mushaf global line cursor.
     *
     * @param int $completedParas Fully memorized paras (0–30)
     * @param int $linesInCurrent Lines memorized in the next/current para (0 = stopped at end of last completed)
     */
    function hifzCursorFromProgress(int $completedParas, int $linesInCurrent = 0): int
    {
        $completedParas = max(0, min(30, $completedParas));
        $linesInCurrent = max(0, $linesInCurrent);
        $layout         = hifzMushafLayoutCode();
        $sabqi          = new \App\Libraries\HifzSabqiCalculator();

        if ($completedParas >= 30 && $linesInCurrent <= 0) {
            $b = $sabqi->juzBounds($layout, 30);

            return (int) ($b['end_line'] ?? 0);
        }

        if ($linesInCurrent <= 0) {
            if ($completedParas <= 0) {
                return 0;
            }
            $b = $sabqi->juzBounds($layout, $completedParas);

            return (int) ($b['end_line'] ?? 0);
        }

        $currentJuz = min(30, $completedParas + 1);
        $b          = $sabqi->juzBounds($layout, $currentJuz);
        if (($b['start_line'] ?? 0) <= 0) {
            return 0;
        }

        $cursor = (int) $b['start_line'] + $linesInCurrent - 1;

        return min($cursor, (int) ($b['end_line'] ?? $cursor));
    }
}

if (! function_exists('hifzProgressFromCursor')) {
    /**
     * @return array{completed_paras:int,lines_in_current:int,current_juz:int}
     */
    function hifzProgressFromCursor(int $cursorLine): array
    {
        if ($cursorLine <= 0) {
            return ['completed_paras' => 0, 'lines_in_current' => 0, 'current_juz' => 1];
        }

        $layout = hifzMushafLayoutCode();
        $sabqi  = new \App\Libraries\HifzSabqiCalculator();
        $juz    = $sabqi->juzForLine($layout, $cursorLine);
        $bounds = $sabqi->juzBounds($layout, $juz);

        if (($bounds['end_line'] ?? 0) > 0 && $cursorLine >= (int) $bounds['end_line']) {
            return [
                'completed_paras'  => $juz,
                'lines_in_current' => 0,
                'current_juz'      => $juz,
            ];
        }

        $linesInCurrent = max(0, $cursorLine - (int) ($bounds['start_line'] ?? 0) + 1);

        return [
            'completed_paras'  => max(0, $juz - 1),
            'lines_in_current'   => $linesInCurrent,
            'current_juz'        => $juz,
        ];
    }
}

if (! function_exists('hifzTeacherAssignedSectionIds')) {
    /**
     * Hifz section IDs assigned to a user as teacher.
     *
     * @return list<int>
     */
    function hifzTeacherAssignedSectionIds(?int $userId = null, ?int $campusId = null, ?int $sessionId = null): array
    {
        $userId    = $userId ?? (int) session('member_userid');
        $campusId  = $campusId ?? (int) session('member_campusid');
        $sessionId = $sessionId ?? (int) session('member_sessionid');

        if ($userId <= 0 || $campusId <= 0 || $sessionId <= 0) {
            return [];
        }

        $db = \Config\Database::connect();

        $rows = $db->table('hifz_teacher_sections')
            ->select('hifz_sec_id')
            ->where('teacher_id', $userId)
            ->where('campus_id', $campusId)
            ->where('session_id', $sessionId)
            ->where('status', 1)
            ->get()
            ->getResultArray();

        return array_map(static fn ($r) => (int) $r['hifz_sec_id'], $rows);
    }
}

if (! function_exists('hifzRecitationSectionsForUser')) {
    /**
     * Sections visible on Daily Recitation (teacher-scoped when applicable).
     *
     * @return list<array{hifz_sec_id:int,section_name:string}>
     */
    function hifzRecitationSectionsForUser(?int $userId = null): array
    {
        $userId = $userId ?? (int) session('member_userid');
        $all    = activeHifzSections();

        if (hasPermission('admin-hifz-sections') || hasPermission('admin-hifz-students')) {
            return $all;
        }

        $assigned = hifzTeacherAssignedSectionIds($userId);
        if ($assigned === []) {
            return $all;
        }

        return array_values(array_filter(
            $all,
            static fn ($s) => in_array((int) $s['hifz_sec_id'], $assigned, true)
        ));
    }
}

if (! function_exists('hifzStudentSessionId')) {
    /**
     * Active academic session for a student (from student_class).
     */
    function hifzStudentSessionId(int $studentId): int
    {
        if ($studentId <= 0) {
            return 0;
        }

        $db  = \Config\Database::connect();
        $row = $db->table('student_class')
            ->select('session_id')
            ->where('student_id', $studentId)
            ->where('status', 1)
            ->orderBy('sc_id', 'DESC')
            ->get()
            ->getRow();

        return (int) ($row->session_id ?? 0);
    }
}

if (! function_exists('studentHifzActive')) {
    /**
     * Active Hifz enrollment row for student, or null.
     */
    function studentHifzActive(?int $studentId = null): ?object
    {
        if (! $studentId || ! campusHifzEnabled()) {
            return null;
        }

        $sessionId = hifzStudentSessionId($studentId);
        if ($sessionId <= 0) {
            return null;
        }

        return (new \App\Libraries\HifzEnrollmentService())->getActiveEnrollment($studentId, $sessionId);
    }
}

if (! function_exists('hifzQualityLabel')) {
    function hifzQualityLabel(?string $code): string
    {
        $code = strtolower(trim((string) $code));
        $opts = hifzRecitationQualityOptions();

        return $opts[$code] ?? ($code !== '' ? ucfirst($code) : '—');
    }
}
