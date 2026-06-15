<?php
if (!function_exists('monthSymbol')) {
    function monthSymbol(array $m): string {
        switch ($m['status'] ?? 'unpaid') {
            case 'paid_on_time': return '✓';
            case 'paid_late':    return '⚠';
            default:             return '✗';
        }
    }
}
