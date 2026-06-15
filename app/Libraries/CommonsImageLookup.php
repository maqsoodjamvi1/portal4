<?php

namespace App\Libraries;

/**
 * Cached Wikimedia Commons thumbnail lookup for vocabulary illustration.
 *
 * Uses the public MediaWiki API (no API keys). Respect cache + batching on callers.
 *
 * @see https://commons.wikimedia.org/wiki/Commons:Reusing_content_outside_Wikimedia
 * @see https://www.mediawiki.org/wiki/API:Etiquette
 */
class CommonsImageLookup
{
    private const API = 'https://commons.wikimedia.org/w/api.php';

    private const WIKI_SEARCH_API = 'https://en.wikipedia.org/w/api.php';

    private const CACHE_PREFIX = 'commons_vocab_v2_';

    private const POSITIVE_TTL = 2592000; // 30 days

    private const NEGATIVE_TTL = 86400; // 1 day — avoid hammering for bad terms

    /** @var list<string> */
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

    /**
     * @return array{url: string, file_page: string, credit: string, description?: string}|null
     */
    public function resolveThumbnail(string $term): ?array
    {
        return $this->resolveForVocabularyWord($term, null);
    }

    /**
     * Resolve a thumbnail using the vocabulary word plus optional English gloss (better Commons hits).
     * Uses free Wikimedia APIs only (no keys): Commons search, optional Wikipedia title hint.
     *
     * @return array{url: string, file_page: string, credit: string, description?: string}|null
     */
    public function resolveForVocabularyWord(string $word, ?string $meaningEn): ?array
    {
        $word = $this->normalizeTerm($word);
        if ($word === '') {
            return null;
        }

        $meaningSnippet = $this->meaningSnippetForCache($meaningEn);
        $cache = cache();
        $key = self::CACHE_PREFIX . md5(mb_strtolower($word . "\n" . $meaningSnippet, 'UTF-8'));

        $cached = $cache->get($key);
        if ($cached !== null) {
            if (is_array($cached) && array_key_exists('url', $cached)) {
                $u = $cached['url'];

                return $u === '' ? null : $cached;
            }

            return null;
        }

        $result = null;
        foreach ($this->buildCommonsSearchCandidates($word, $meaningEn) as $q) {
            $result = $this->fetchFromApi($q);
            if ($result !== null) {
                break;
            }
        }

        if ($result === null) {
            $wikiTitle = $this->fetchFirstWikipediaTitle($word . ($meaningSnippet !== '' ? ' ' . $meaningSnippet : ''));
            if ($wikiTitle !== '') {
                $result = $this->fetchFromApi($wikiTitle);
            }
        }

        if ($result === null) {
            $cache->save($key, ['url' => ''], self::NEGATIVE_TTL);

            return null;
        }

        $cache->save($key, $result, self::POSITIVE_TTL);

        return $result;
    }

    private function meaningSnippetForCache(?string $meaningEn): string
    {
        $t = $this->normalizeTerm((string) ($meaningEn ?? ''));
        if ($t === '') {
            return '';
        }
        $tok = $this->meaningKeywords($meaningEn, 6);

        return $tok !== [] ? implode(' ', $tok) : mb_substr($t, 0, 80, 'UTF-8');
    }

    /**
     * @return list<string>
     */
    private function buildCommonsSearchCandidates(string $word, ?string $meaningEn): array
    {
        $out = [$word];
        $kw = $this->meaningKeywords($meaningEn, 4);
        if ($kw !== []) {
            $out[] = $word . ' ' . implode(' ', $kw);
            $out[] = implode(' ', $kw);
        }
        $out[] = $word . ' photograph';
        $out[] = $word . ' illustration';

        $seen = [];
        $uniq = [];
        foreach ($out as $q) {
            $q = $this->normalizeTerm($q);
            if ($q === '' || isset($seen[$q])) {
                continue;
            }
            $seen[$q] = true;
            $uniq[] = $q;
        }

        return $uniq;
    }

    /**
     * @return list<string>
     */
    private function meaningKeywords(?string $meaningEn, int $max): array
    {
        $raw = trim((string) ($meaningEn ?? ''));
        if ($raw === '') {
            return [];
        }
        $parts = preg_split('/[^\p{L}\p{N}\'\-]+/u', mb_strtolower($raw, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY);
        if (! is_array($parts)) {
            return [];
        }
        $stop = [
            'a', 'an', 'the', 'to', 'of', 'and', 'or', 'in', 'on', 'at', 'for', 'with', 'by', 'from',
            'is', 'are', 'was', 'were', 'be', 'been', 'being', 'it', 'its', 'this', 'that', 'these', 'those',
            'as', 'also', 'not', 'no', 'yes', 'but', 'if', 'than', 'then', 'into', 'about', 'such',
        ];
        $out = [];
        foreach ($parts as $p) {
            $p = trim($p, '-\'');
            if ($p === '' || mb_strlen($p, 'UTF-8') < 2 || in_array($p, $stop, true)) {
                continue;
            }
            $out[] = $p;
            if (count($out) >= $max) {
                break;
            }
        }

        return $out;
    }

    private function fetchFirstWikipediaTitle(string $searchPhrase): string
    {
        $searchPhrase = $this->normalizeTerm($searchPhrase);
        if ($searchPhrase === '') {
            return '';
        }

        $client = \Config\Services::curlrequest([
            'timeout' => 12,
            'http_errors' => false,
        ]);

        try {
            $res = $client->get(self::WIKI_SEARCH_API, [
                'headers' => [
                    'User-Agent' => 'SchoolVocabularyPortal/1.0 (CodeIgniter4; educational; vocabulary thumbnails)',
                ],
                'query' => [
                    'action' => 'query',
                    'list' => 'search',
                    'srsearch' => $searchPhrase,
                    'srlimit' => '1',
                    'format' => 'json',
                    'formatversion' => '2',
                ],
            ]);
        } catch (\Throwable $e) {
            log_message('warning', 'CommonsImageLookup Wikipedia: ' . $e->getMessage());

            return '';
        }

        if ($res->getStatusCode() !== 200) {
            return '';
        }
        $json = json_decode($res->getBody(), true);
        if (! is_array($json)) {
            return '';
        }
        $hits = $json['query']['search'] ?? [];
        if (! is_array($hits) || ! isset($hits[0]) || ! is_array($hits[0])) {
            return '';
        }

        return $this->normalizeTerm((string) ($hits[0]['title'] ?? ''));
    }

    private function normalizeTerm(string $term): string
    {
        $t = trim((string) preg_replace('/\s+/u', ' ', $term));
        if ($t === '') {
            return '';
        }
        if (mb_strlen($t, 'UTF-8') > 120) {
            $t = mb_substr($t, 0, 120, 'UTF-8');
        }

        return $t;
    }

    /**
     * @return array{url: string, file_page: string, credit: string, description?: string}|null
     */
    private function fetchFromApi(string $term): ?array
    {
        $client = \Config\Services::curlrequest([
            'timeout' => 15,
            'http_errors' => false,
        ]);

        $query = [
            'action' => 'query',
            'generator' => 'search',
            'gsrnamespace' => '6',
            'gsrsearch' => $term,
            'gsrlimit' => '1',
            'prop' => 'imageinfo',
            'iiprop' => 'url|mime|extmetadata',
            'iiurlwidth' => '420',
            'format' => 'json',
            'formatversion' => '2',
        ];

        try {
            $res = $client->get(self::API, [
                'headers' => [
                    'User-Agent' => 'SchoolVocabularyPortal/1.0 (CodeIgniter4; educational; vocabulary thumbnails)',
                ],
                'query' => $query,
            ]);
        } catch (\Throwable $e) {
            log_message('warning', 'CommonsImageLookup HTTP: ' . $e->getMessage());

            return null;
        }

        if ($res->getStatusCode() !== 200) {
            return null;
        }

        $json = json_decode($res->getBody(), true);
        if (! is_array($json)) {
            return null;
        }

        $pagesRaw = $json['query']['pages'] ?? null;
        if (! is_array($pagesRaw) || $pagesRaw === []) {
            return null;
        }

        $pagesList = array_is_list($pagesRaw) ? $pagesRaw : array_values($pagesRaw);
        $page = $pagesList[0] ?? null;
        if (! is_array($page) || ! empty($page['missing'])) {
            return null;
        }

        $iiList = $page['imageinfo'] ?? [];
        $ii = is_array($iiList) && isset($iiList[0]) ? $iiList[0] : null;
        if (! is_array($ii)) {
            return null;
        }

        $mime = (string) ($ii['mime'] ?? '');
        if (! in_array($mime, self::ALLOWED_MIME, true)) {
            return null;
        }

        $thumb = (string) ($ii['thumburl'] ?? '');
        $full = (string) ($ii['url'] ?? '');
        $url = '';
        if ($this->isAllowedThumbUrl($thumb)) {
            $url = $thumb;
        } elseif ($this->isAllowedThumbUrl($full)) {
            $url = $full;
        }
        if ($url === '') {
            return null;
        }

        $title = (string) ($page['title'] ?? '');
        $filePage = '';
        if ($title !== '' && strncmp($title, 'File:', 5) === 0) {
            $filePage = 'https://commons.wikimedia.org/wiki/' . rawurlencode(str_replace(' ', '_', $title));
        }

        $ext = isset($ii['extmetadata']) && is_array($ii['extmetadata']) ? $ii['extmetadata'] : [];

        return [
            'url' => $url,
            'file_page' => $filePage,
            'credit' => $this->extractCredit($ext),
            'description' => $this->extractDescription($ext),
        ];
    }

    /** @param array<string,mixed> $ext */
    private function extractDescription(array $ext): string
    {
        foreach (['ImageDescription', 'ObjectName'] as $key) {
            if (! isset($ext[$key]) || ! is_array($ext[$key])) {
                continue;
            }
            $raw = $ext[$key]['value'] ?? '';
            if (! is_string($raw)) {
                continue;
            }
            $v = strip_tags(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $v = trim((string) preg_replace('/\s+/u', ' ', $v));
            if ($v !== '') {
                return mb_substr($v, 0, 320, 'UTF-8');
            }
        }

        return '';
    }

    private function isAllowedThumbUrl(string $u): bool
    {
        if ($u === '' || (! str_starts_with($u, 'https://'))) {
            return false;
        }
        // Thumbnails live on uploads; full file URLs often use upload.wikimedia.org as well.
        return (bool) preg_match('#^https://upload\.wikimedia\.org/#i', $u);
    }

    /** @param array<string,mixed> $ext */
    private function extractCredit(array $ext): string
    {
        $parts = [];
        foreach (['Attribution', 'Artist', 'LicenseShortName'] as $key) {
            if (! isset($ext[$key]) || ! is_array($ext[$key])) {
                continue;
            }
            $raw = $ext[$key]['value'] ?? '';
            if (! is_string($raw)) {
                continue;
            }
            $v = strip_tags(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $v = trim((string) preg_replace('/\s+/u', ' ', $v));
            if ($v !== '' && ! in_array($v, $parts, true)) {
                $parts[] = mb_substr($v, 0, 280, 'UTF-8');
            }
        }

        return implode(' · ', $parts);
    }
}
