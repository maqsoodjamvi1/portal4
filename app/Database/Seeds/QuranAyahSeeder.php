<?php

namespace App\Database\Seeds;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;

/**
 * Seeds Arabic ayah text (Uthmani script) for Hifz recitation display.
 *
 * Run once: php spark db:seed QuranAyahSeeder
 */
class QuranAyahSeeder extends Seeder
{
    /** @var list<string> */
    protected array $sources = [
        'https://api.alquran.cloud/v1/quran/quran-uthmani',
        'https://cdn.jsdelivr.net/gh/fawazahmed0/quran-api@1/editions/ara-quranuthmani.json',
    ];

    public function run(): void
    {
        if (! $this->db->tableExists('quran_ayahs')) {
            throw new \RuntimeException('Run migration CreateQuranAyahsTable first.');
        }

        $existing = (int) $this->db->table('quran_ayahs')->countAllResults();
        if ($existing > 100) {
            CLI::write('Quran ayah text already seeded (' . $existing . ' ayahs).');

            return;
        }

        CLI::write('Downloading Arabic Quran text…');

        $rows = null;
        foreach ($this->sources as $url) {
            CLI::write('Trying ' . $url);
            $json = $this->fetchUrl($url);
            if ($json === '') {
                continue;
            }

            $rows = $this->parsePayload($json, $url);
            if ($rows !== []) {
                break;
            }
        }

        if ($rows === null || $rows === []) {
            throw new \RuntimeException('Could not download Quran text from any source. Check internet connection and retry.');
        }

        $batch = [];
        foreach ($rows as $row) {
            $batch[] = $row;

            if (count($batch) >= 200) {
                $this->db->table('quran_ayahs')->insertBatch($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            $this->db->table('quran_ayahs')->insertBatch($batch);
        }

        $total = (int) $this->db->table('quran_ayahs')->countAllResults();
        CLI::write('Seeded ' . $total . ' ayahs.');
    }

    protected function fetchUrl(string $url): string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 120,
                CURLOPT_USERAGENT      => 'SchoolManagementSystem/QuranAyahSeeder',
            ]);
            $body = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($body !== false && $code >= 200 && $code < 300) {
                return (string) $body;
            }
        }

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 120,
                'header'  => "User-Agent: SchoolManagementSystem/QuranAyahSeeder\r\n",
            ],
        ]);

        $body = @file_get_contents($url, false, $ctx);

        return is_string($body) ? $body : '';
    }

    /**
     * @return list<array{surah_id:int,ayah_no:int,text_ar:string,script_type:string}>
     */
    protected function parsePayload(string $json, string $url): array
    {
        $data = json_decode($json, true);
        if (! is_array($data)) {
            return [];
        }

        if (str_contains($url, 'alquran.cloud')) {
            return $this->parseAlQuranCloud($data);
        }

        return $this->parseFawazFlat($data);
    }

    /**
     * @return list<array{surah_id:int,ayah_no:int,text_ar:string,script_type:string}>
     */
    protected function parseAlQuranCloud(array $data): array
    {
        $surahs = $data['data']['surahs'] ?? null;
        if (! is_array($surahs)) {
            return [];
        }

        $rows = [];
        foreach ($surahs as $surah) {
            $surahId = (int) ($surah['number'] ?? 0);
            $ayahs   = $surah['ayahs'] ?? [];
            if ($surahId < 1 || $surahId > 114 || ! is_array($ayahs)) {
                continue;
            }

            foreach ($ayahs as $ayah) {
                $ayahNo = (int) ($ayah['numberInSurah'] ?? 0);
                $text   = $this->cleanText((string) ($ayah['text'] ?? ''));
                if ($ayahNo < 1 || $text === '') {
                    continue;
                }

                $rows[] = [
                    'surah_id'    => $surahId,
                    'ayah_no'     => $ayahNo,
                    'text_ar'     => $text,
                    'script_type' => 'uthmani',
                ];
            }
        }

        return $rows;
    }

    /**
     * @return list<array{surah_id:int,ayah_no:int,text_ar:string,script_type:string}>
     */
    protected function parseFawazFlat(array $data): array
    {
        $rows = [];

        foreach ($data as $key => $text) {
            if (! is_string($key) || ! str_contains($key, ':') || ! is_string($text)) {
                continue;
            }
            [$surah, $ayah] = array_map('intval', explode(':', $key, 2));
            if ($surah < 1 || $surah > 114 || $ayah < 1) {
                continue;
            }

            $rows[] = [
                'surah_id'    => $surah,
                'ayah_no'     => $ayah,
                'text_ar'     => $this->cleanText($text),
                'script_type' => 'uthmani',
            ];
        }

        return $rows;
    }

    protected function cleanText(string $text): string
    {
        $text = preg_replace('/^\xEF\xBB\xBF/u', '', $text) ?? $text;

        return trim($text);
    }
}
