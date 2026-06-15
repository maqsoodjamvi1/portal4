<?php

namespace App\Libraries;

use Config\Database;

/**
 * Loads Arabic ayah text for Hifz recitation display.
 */
class QuranAyahTextService
{
    protected $db;
    protected int $maxAyahs = 80;

    protected int $maxAyahsPerPage = 120;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function isReady(): bool
    {
        if (! $this->db->tableExists('quran_ayahs')) {
            return false;
        }

        return $this->db->table('quran_ayahs')->countAllResults() > 100;
    }

    /**
     * @return list<array{surah_id:int,ayah_no:int,text_ar:string,ref:string}>
     */
    public function getAyahsForRange(int $surahStart, int $ayahStart, int $surahEnd, int $ayahEnd): array
    {
        if (! $this->isReady() || $surahStart <= 0 || $ayahStart <= 0) {
            return [];
        }

        if ($surahEnd <= 0) {
            $surahEnd = $surahStart;
        }
        if ($ayahEnd <= 0) {
            $ayahEnd = $ayahStart;
        }

        $refs = $this->expandAyahRefs($surahStart, $ayahStart, $surahEnd, $ayahEnd);
        if ($refs === []) {
            return [];
        }

        return $this->fetchAyahs($refs);
    }

    /**
     * All ayahs appearing on one Indo-Pak mushaf page.
     *
     * @return list<array{surah_id:int,ayah_no:int,text_ar:string,ref:string,page_no:int}>
     */
    public function getAyahsForPage(string $layoutCode, int $pageNo): array
    {
        if (! $this->isReady() || $pageNo < 1) {
            return [];
        }

        $resolver = new HifzLineResolver();
        $bounds   = $resolver->getPageBounds($layoutCode, $pageNo);
        $startS   = (int) ($bounds['surah_id_start'] ?? 0);
        $startA   = (int) ($bounds['ayah_from'] ?? 0);
        $endS     = (int) ($bounds['surah_id_end'] ?? 0);
        $endA     = (int) ($bounds['ayah_to'] ?? 0);

        if ($startS <= 0 || $startA <= 0 || $endS <= 0 || $endA <= 0) {
            return [];
        }

        $refs = $this->expandAyahRefs($startS, $startA, $endS, $endA);
        $refs = array_slice($refs, 0, $this->maxAyahsPerPage);
        $ayahs = $this->fetchAyahs($refs);

        foreach ($ayahs as &$ayah) {
            $ayah['page_no'] = $pageNo;
        }

        return $ayahs;
    }

    /**
     * Full paras (juz) for Manzil display.
     *
     * @param list<int> $juzList
     * @return list<array{surah_id:int,ayah_no:int,text_ar:string,ref:string,juz_no:int}>
     */
    public function getAyahsForJuzList(array $juzList): array
    {
        if (! $this->isReady() || $juzList === []) {
            return [];
        }

        $out = [];

        foreach ($juzList as $juzNo) {
            $juzNo = (int) $juzNo;
            if ($juzNo < 1 || $juzNo > 30) {
                continue;
            }

            $row = $this->db->table('quran_juz_boundaries')
                ->where('juz_no', $juzNo)
                ->get()
                ->getRow();

            if (! $row) {
                continue;
            }

            $chunk = $this->getAyahsForRange(
                (int) $row->start_surah_id,
                (int) $row->start_ayah,
                (int) $row->end_surah_id,
                (int) $row->end_ayah
            );

            foreach ($chunk as $ayah) {
                $ayah['juz_no'] = $juzNo;
                $out[]          = $ayah;
            }

            if (count($out) >= $this->maxAyahs) {
                break;
            }
        }

        return array_slice($out, 0, $this->maxAyahs);
    }

    /**
     * @param list<array{surah_id:int,ayah_no:int}> $refs
     * @return list<array{surah_id:int,ayah_no:int,text_ar:string,ref:string}>
     */
    protected function fetchAyahs(array $refs): array
    {
        if ($refs === []) {
            return [];
        }

        $limit = min(count($refs), max($this->maxAyahs, $this->maxAyahsPerPage));
        $refs  = array_slice($refs, 0, $limit);
        $builder = $this->db->table('quran_ayahs')->select('surah_id, ayah_no, text_ar');

        $builder->groupStart();
        foreach ($refs as $i => $ref) {
            if ($i === 0) {
                $builder->groupStart()
                    ->where('surah_id', $ref['surah_id'])
                    ->where('ayah_no', $ref['ayah_no'])
                ->groupEnd();
            } else {
                $builder->orGroupStart()
                    ->where('surah_id', $ref['surah_id'])
                    ->where('ayah_no', $ref['ayah_no'])
                ->groupEnd();
            }
        }
        $builder->groupEnd();

        $rows = $builder->get()->getResultArray();
        $map  = [];
        foreach ($rows as $row) {
            $key       = (int) $row['surah_id'] . ':' . (int) $row['ayah_no'];
            $map[$key] = $row['text_ar'] ?? '';
        }

        $out = [];
        foreach ($refs as $ref) {
            $key = $ref['surah_id'] . ':' . $ref['ayah_no'];
            $out[] = [
                'surah_id' => $ref['surah_id'],
                'ayah_no'  => $ref['ayah_no'],
                'text_ar'  => $map[$key] ?? '',
                'ref'      => $key,
            ];
        }

        return $out;
    }

    /**
     * @return list<array{surah_id:int,ayah_no:int}>
     */
    protected function expandAyahRefs(int $surahStart, int $ayahStart, int $surahEnd, int $ayahEnd): array
    {
        $refs = [];

        if ($surahStart === $surahEnd) {
            for ($a = $ayahStart; $a <= $ayahEnd; $a++) {
                $refs[] = ['surah_id' => $surahStart, 'ayah_no' => $a];
            }

            return $refs;
        }

        $counts = \Config\QuranReference::$surahAyahCounts;
        for ($s = $surahStart; $s <= $surahEnd; $s++) {
            $max   = (int) ($counts[$s - 1] ?? 0);
            $from  = ($s === $surahStart) ? $ayahStart : 1;
            $to    = ($s === $surahEnd) ? $ayahEnd : $max;
            for ($a = $from; $a <= $to; $a++) {
                $refs[] = ['surah_id' => $s, 'ayah_no' => $a];
            }
        }

        return $refs;
    }
}
