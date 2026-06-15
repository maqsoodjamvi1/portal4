<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Config\QuranReference;

/**
 * Seeds Quran surahs, juz boundaries, and Indo-Pak 16-line mushaf line map.
 *
 * Run: php spark db:seed QuranReferenceSeeder
 */
class QuranReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLayout();
        $this->seedSurahs();
        $this->seedJuzBoundaries();
        $this->syncJuzNames();
        $this->seedMushafLines();
    }

    private function seedLayout(): void
    {
        $code = QuranReference::LAYOUT_CODE;
        $totalLines = QuranReference::TOTAL_PAGES * QuranReference::LINES_PER_PAGE;

        $exists = $this->db->table('quran_mushaf_layouts')
            ->where('layout_code', $code)
            ->countAllResults();

        if ($exists > 0) {
            return;
        }

        $this->db->table('quran_mushaf_layouts')->insert([
            'layout_code'    => $code,
            'layout_name'    => 'Indo-Pak 16 Line',
            'lines_per_page' => QuranReference::LINES_PER_PAGE,
            'total_pages'    => QuranReference::TOTAL_PAGES,
            'total_lines'    => $totalLines,
        ]);
    }

    private function seedSurahs(): void
    {
        if ($this->db->table('quran_surahs')->countAllResults() > 0) {
            return;
        }

        $batch = [];
        foreach (QuranReference::$surahAyahCounts as $i => $count) {
            $meta = QuranReference::$surahMeta[$i];
            $batch[] = [
                'surah_id'         => $i + 1,
                'surah_name_en'    => $meta['name_en'],
                'surah_name_ar'    => $meta['name_ar'],
                'total_ayahs'      => $count,
                'revelation_order' => $meta['revelation'],
            ];
        }

        $this->db->table('quran_surahs')->insertBatch($batch);
    }

    private function seedJuzBoundaries(): void
    {
        if ($this->db->table('quran_juz_boundaries')->countAllResults() > 0) {
            return;
        }

        $starts = QuranReference::$juzStartPages;

        for ($j = 1; $j <= 30; $j++) {
            $b = QuranReference::$juzBoundaries[$j - 1];
            $meta = QuranReference::$juzMeta[$j - 1] ?? ['name_ar' => '', 'name_en' => ''];
            $startPage = $starts[$j - 1];
            $endPage = ($j < 30) ? ($starts[$j] - 1) : QuranReference::TOTAL_PAGES;
            $lineCount = ($endPage - $startPage + 1) * QuranReference::LINES_PER_PAGE;
            $ayahs = QuranReference::ayahsInJuz($j);

            $this->db->table('quran_juz_boundaries')->insert([
                'juz_no'           => $j,
                'juz_name_ar'      => $meta['name_ar'],
                'juz_name_en'      => $meta['name_en'],
                'start_surah_id'   => $b['start']['surah'],
                'start_ayah'       => $b['start']['ayah'],
                'end_surah_id'     => $b['end']['surah'],
                'end_ayah'         => $b['end']['ayah'],
                'total_ayahs'      => count($ayahs),
                'start_page'       => $startPage,
                'end_page'         => $endPage,
                'total_lines'      => $lineCount,
            ]);
        }
    }

    /**
     * Fill or refresh traditional para names (safe to run on existing DB).
     */
    private function syncJuzNames(): void
    {
        if (! $this->db->tableExists('quran_juz_boundaries')) {
            return;
        }

        if (! $this->db->fieldExists('juz_name_ar', 'quran_juz_boundaries')) {
            return;
        }

        for ($j = 1; $j <= 30; $j++) {
            $meta = QuranReference::$juzMeta[$j - 1] ?? null;
            if (! $meta) {
                continue;
            }

            $this->db->table('quran_juz_boundaries')
                ->where('juz_no', $j)
                ->update([
                    'juz_name_ar' => $meta['name_ar'],
                    'juz_name_en' => $meta['name_en'],
                ]);
        }
    }

    private function seedMushafLines(): void
    {
        $code = QuranReference::LAYOUT_CODE;
        if ($this->db->table('quran_mushaf_lines')->where('layout_code', $code)->countAllResults() > 0) {
            return;
        }

        $globalLine = 0;
        $batch = [];
        $batchSize = 500;

        for ($juz = 1; $juz <= 30; $juz++) {
            $startPage = QuranReference::$juzStartPages[$juz - 1];
            $endPage = ($juz < 30)
                ? (QuranReference::$juzStartPages[$juz] - 1)
                : QuranReference::TOTAL_PAGES;
            $numLines = ($endPage - $startPage + 1) * QuranReference::LINES_PER_PAGE;
            $ayahs = QuranReference::ayahsInJuz($juz);
            $ayahCount = count($ayahs);
            if ($ayahCount === 0 || $numLines === 0) {
                continue;
            }

            $ayahIndex = 0;
            $remainingInCurrentAyah = 0;
            $currentSurah = $ayahs[0]['surah'];
            $currentAyah = $ayahs[0]['ayah'];

            for ($lineInJuz = 1; $lineInJuz <= $numLines; $lineInJuz++) {
                $globalLine++;
                $pageOffset = (int) floor(($lineInJuz - 1) / QuranReference::LINES_PER_PAGE);
                $pageNo = $startPage + $pageOffset;
                $lineOnPage = (($lineInJuz - 1) % QuranReference::LINES_PER_PAGE) + 1;

                $lineStartSurah = $currentSurah;
                $lineStartAyah  = $currentAyah;

                $targetWeight = $ayahCount / $numLines;
                $weight = 0.0;

                while ($ayahIndex < $ayahCount && ($weight < $targetWeight || $weight <= 0)) {
                    if ($remainingInCurrentAyah <= 0) {
                        $currentSurah = $ayahs[$ayahIndex]['surah'];
                        $currentAyah  = $ayahs[$ayahIndex]['ayah'];
                        $remainingInCurrentAyah = 1;
                        $ayahIndex++;
                    }
                    $weight += 1;
                    $remainingInCurrentAyah--;
                }

                $lineEndSurah = $currentSurah;
                $lineEndAyah  = $currentAyah;

                if ($lineInJuz === $numLines && $ayahIndex < $ayahCount) {
                    $last = $ayahs[$ayahCount - 1];
                    $lineEndSurah = $last['surah'];
                    $lineEndAyah  = $last['ayah'];
                }

                $batch[] = [
                    'layout_code'    => $code,
                    'global_line_no' => $globalLine,
                    'page_no'        => $pageNo,
                    'line_on_page'   => $lineOnPage,
                    'juz_no'         => $juz,
                    'surah_id_start' => $lineStartSurah,
                    'ayah_start'     => $lineStartAyah,
                    'surah_id_end'   => $lineEndSurah,
                    'ayah_end'       => $lineEndAyah,
                ];

                if (count($batch) >= $batchSize) {
                    $this->db->table('quran_mushaf_lines')->insertBatch($batch);
                    $batch = [];
                }
            }
        }

        if ($batch !== []) {
            $this->db->table('quran_mushaf_lines')->insertBatch($batch);
        }

        $this->db->table('quran_mushaf_layouts')
            ->where('layout_code', $code)
            ->update(['total_lines' => $globalLine]);
    }
}
