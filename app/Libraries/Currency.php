<?php
namespace App\Libraries;

use CodeIgniter\Config\Services;
use CodeIgniter\Database\BaseConnection;

class Currency
{
    protected BaseConnection $db;
    protected string $base;
    protected string $display;
    protected string $rateDate;
    protected $cache;
    protected int $ttl;

    // public function __construct(BaseConnection $db, string $campusBase, string $displayCode, ?string $rateDate = null)
    // {
    //     $this->db       = $db;
    //     $this->base     = strtoupper($campusBase ?: 'PKR');
    //     $this->display  = strtoupper($displayCode ?: $this->base);
    //     $this->rateDate = $rateDate ? date('Y-m-d', strtotime($rateDate)) : date('Y-m-d');

    //     $this->cache = Services::cache();
    //     // Optional: read from your config('Currency')->rateCacheTtl, else 12h
    //     $this->ttl   = (int) (config('Currency')->rateCacheTtl ?? 43200);
    // }
    public function __construct(
    ?\CodeIgniter\Database\BaseConnection $db = null,
    ?string $campusBase = null,
    ?string $displayCode = null,
    ?string $rateDate = null
    ) {
        // Resolve DB
        $this->db = $db ?? db_connect();

        // Resolve campus base & display from session/config
        $session     = session();
        $cfg         = config('Currency'); // optional config/app/Config/Currency.php
        $defaultBase = $cfg->baseCode        ?? 'PKR';
        $defaultDisp = $cfg->defaultDisplay  ?? $defaultBase;

        // Try per-campus base if available in session
        $campusBaseFromSession = $session->get('campus_base_code')
            ?? $session->get('member_campus_currency'); // your earlier session key

        $this->base    = strtoupper($campusBase ?: ($campusBaseFromSession ?: $defaultBase));
        $this->display = strtoupper($displayCode ?: ($session->get('currency_code') ?: $defaultDisp));

        // Normalize the “as-of” date
        $this->rateDate = $rateDate
            ? date('Y-m-d', strtotime($rateDate))
            : date('Y-m-d');

        // Cache service + TTL
        $this->cache = \CodeIgniter\Config\Services::cache();
        $this->ttl   = (int) ($cfg->rateCacheTtl ?? 43200); // 12h default
    }


    public function displayCode(): string { return $this->display; }
    public function baseCode(): string    { return $this->base; }

    /** Public convert wrapper (kept same signature used by helper). */
    public function convert(float $amount, string $from, string $to, ?string $onDate = null): float
    {
        $from   = strtoupper($from);
        $to     = strtoupper($to);
        $onDate = $onDate ? date('Y-m-d', strtotime($onDate)) : $this->rateDate;

        if ($from === $to) return round($amount, 2);

        $rate = $this->getRate($from, $to, $onDate);
        return round($amount * $rate, 2);
    }

    /** Get 1 {from}->{to} rate on a date (with fallback to latest <= date), with safe cached key. */
    protected function getRate(string $from, string $to, string $onDate): float
    {
        $key = $this->makeCacheKey($from, $to, $onDate); // <-- sanitized key

        // Try cache (guard against invalid-key exceptions)
        try {
            $cached = $this->cache->get($key);
            if ($cached !== null) {
                return (float) $cached;
            }
        } catch (\Throwable $e) {
            // If cache backend complains, skip cache gracefully
        }

        // Exact day first
        $row = $this->db->table('currency_rates')
            ->select('rate')
            ->where([
                'rate_date'  => $onDate,
                'base_code'  => $from,
                'quote_code' => $to
            ])->get()->getRowArray();

        if (!$row) {
            // Latest <= date
            $row = $this->db->query(
                "SELECT rate FROM currency_rates
                 WHERE base_code=? AND quote_code=? AND rate_date<=?
                 ORDER BY rate_date DESC LIMIT 1",
                [$from, $to, $onDate]
            )->getRowArray();
        }

        $rate = (float)($row['rate'] ?? 1.0);

        // Save to cache with sanitized key
        try {
            $this->cache->save($key, $rate, $this->ttl);
        } catch (\Throwable $e) {
            // Ignore cache storage issues
        }

        return $rate ?: 1.0;
    }

    /** Format money with symbol/decimals from `currencies`. */
    public function format(float $amount, string $code): string
    {
        $c = $this->db->table('currencies')->select('symbol,decimal_places')
            ->where('code', strtoupper($code))->get()->getRowArray();

        $symbol = $c['symbol'] ?? '';
        $dp     = isset($c['decimal_places']) ? (int)$c['decimal_places'] : 2;

        return ($symbol !== '' ? $symbol : ($code . '  ')) . number_format($amount, $dp);
    }

    /** Build a cache-key acceptable to Redis/Memcached/File handlers. */
    protected function makeCacheKey(string $from, string $to, string $date): string
    {
        // Normalize inputs
        $from = strtoupper($from);
        $to   = strtoupper($to);
        $date = date('Y-m-d', strtotime($date));

        // Compose a raw key then sanitize to [A-Z0-9_]
        $raw  = "FX_{$from}_{$to}_{$date}";
        return preg_replace('/[^A-Z0-9_]/', '_', $raw);
    }

    /** Return active currencies for the UI dropdown (robust to missing columns). */
    public function listActive(): array
    {
        $key = 'FX_LIST_ACTIVE';

        // 1) Try cache
        try {
            $cached = $this->cache->get($key);
            if (is_array($cached)) {
                return $cached;
            }
        } catch (\Throwable $e) {
            // ignore cache issues
        }

        // Helpers
        $hasCol = function (string $table, string $col): bool {
            try {
                $q = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE ?", [$col]);
                return $q && $q->getNumRows() > 0;
            } catch (\Throwable $e) {
                return false;
            }
        };

        $builder = $this->db->table('currencies')
            ->select('code, name, symbol, decimal_places');

        // 2) Optional active flag
        if ($hasCol('currencies', 'is_active')) {
            $builder->where('is_active', 1);
        } elseif ($hasCol('currencies', 'status')) {
            $builder->where('status', 1);
        }
        // else: no active/status column, show all

        // 3) Safe ordering
        if ($hasCol('currencies', 'sort_order')) {
            $builder->orderBy('sort_order', 'ASC');
        }
        $builder->orderBy('code', 'ASC');

        // 4) Execute with fallback if the builder fails
        try {
            $query = $builder->get();
            if ($query === false) {
                throw new \RuntimeException('Query failed');
            }
            $rows = $query->getResultArray();
        } catch (\Throwable $e) {
            // Last-resort simple query (code-only order)
            $rows = $this->db->query(
                "SELECT code, name, symbol, decimal_places
                 FROM currencies
                 ORDER BY code ASC"
            )->getResultArray();
        }

        // 5) Cache the list
        try {
            $this->cache->save($key, $rows, 3600);
        } catch (\Throwable $e) {
            // ignore cache issues
        }

        return $rows ?? [];
    }


}
