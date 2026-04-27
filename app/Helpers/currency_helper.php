<?php

use App\Libraries\Currency;

if (!function_exists('currency')) {
    // function currency(): Currency { return new Currency(); }
    function currency(?string $rateDate = null) {
        $db      = db_connect();
        $session = session();
        $base    = $session->get('campus_base_code') ?? $session->get('member_campus_currency') ?? 'PKR ';
        $disp    = $session->get('currency_code') ?? (config('Currency')->defaultDisplay ?? $base);
        return new \App\Libraries\Currency($db, $base, $disp, $rateDate);
    }

}

if (!function_exists('money_convert')) {
    function money_convert(float $amount, string $from, string $to, ?string $asOfDate = null): ?float
    {
        return currency()->convert($amount, $from, $to, $asOfDate);
    }
}

if (!function_exists('money_format_disp')) {
    function money_format_disp(float $amount, string $code): string
    {
        return currency()->format($amount, $code);
    }
}

/**
 * Internal: coerce amount into float, tolerant to "1,200.50", " 1200 ", null, etc.
 */
if (!function_exists('_coerce_amount')) {
    function _coerce_amount($val): float
    {
        if ($val === null) return 0.0;

        // Already numeric?
        if (is_int($val) || is_float($val)) {
            return (float) $val;
        }

        // String: strip any non-numeric except digits, dot, minus
        if (is_string($val)) {
            // common thousand separators or currency symbols
            $clean = trim($val);
            // Remove anything that's not digit, dot or minus
            $clean = preg_replace('/[^0-9\.\-]/', '', $clean);
            if ($clean === '' || $clean === '-' || $clean === '.'
                || $clean === '-.' || $clean === '.-') {
                return 0.0;
            }
            return (float) $clean;
        }

        // Any other type
        return 0.0;
    }
}

if (!function_exists('money_from_base')) {
    /**
     * Convert a BASE amount to the user's selected display currency and format it.
     * $amountBase may be float|int|string|null; $asOfDate may be Y-m-d or any strtotime()-parsable string.
     */
    function money_from_base($amountBase, ?string $asOfDate = null): string
    {
        $amt  = _coerce_amount($amountBase);
        $svc  = currency($asOfDate);
        $disp = session('currency_code') ?? (config('Currency')->defaultDisplay ?? 'PKR');

        // Prefer campus base from session; fall back to config
        $base = session('campus_base_code')
            ?? session('member_campus_currency')
            ?? (config('Currency')->baseCode ?? 'PKR');

        $dispAmt = $svc->convert($amt, $base, $disp, $asOfDate) ?? $amt;
        return $svc->format($dispAmt, $disp);
    }
}

/**
 * Optional: raw converted float (no formatting) for DataTables sums/JS.
 */
if (!function_exists('money_from_base_float')) {
    function money_from_base_float($amountBase, ?string $asOfDate = null): float
    {
        $amt  = _coerce_amount($amountBase);
        $svc  = currency($asOfDate);
        $disp = session('currency_code') ?? (config('Currency')->defaultDisplay ?? 'PKR');
        $base = session('campus_base_code')
            ?? session('member_campus_currency')
            ?? (config('Currency')->baseCode ?? 'PKR');

        return $svc->convert($amt, $base, $disp, $asOfDate) ?? $amt;
    }
}

