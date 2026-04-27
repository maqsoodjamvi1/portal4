<?php
use CodeIgniter\Database\BaseConnection;

if (! function_exists('wifi_get_client_ip')) {
    /**
     * Get client IP in a safe way. Relies on CI4's getIPAddress()
     * and your Config\App::$proxyIPs if you use a reverse proxy.
     */
    function wifi_get_client_ip(): string
    {
        $request = service('request');
        return (string) $request->getIPAddress(); // already handles proxies if configured
    }
}

if (! function_exists('wifi_is_ip_allowed_for_campus')) {
    /**
     * Check if given IP is allowed for this campus, based on campus_wifi_rules.
     * If no active rule exists for campus -> return true (no restriction configured).
     */
    function wifi_is_ip_allowed_for_campus(BaseConnection $db, int $campusId, string $ip): bool
    {
        if ($campusId <= 0 || $ip === '') {
            return false; // be strict if campus / ip is missing
        }

        // Fetch active rules
        $rules = $db->table('campus_wifi_rules')
            ->where('campus_id', $campusId)
            ->where('is_active', 1)
            ->get()
            ->getResultArray();

        // If no rule configured -> allow everything (so you don't lock out all students accidentally)
        if (empty($rules)) {
            return true;
        }

        $ipLong = ip2long($ip);
        if ($ipLong === false) {
            // Invalid IP string
            return false;
        }

        foreach ($rules as $r) {
            $type = $r['rule_type'] ?? 'single';
            $start = $r['ip_start'] ?? '';
            $end   = $r['ip_end']   ?? '';

            if ($type === 'single') {
                if ($ip === $start) {
                    return true;
                }
            } elseif ($type === 'range') {
                $startLong = ip2long($start);
                $endLong   = ip2long($end);
                if ($startLong !== false && $endLong !== false) {
                    if ($ipLong >= $startLong && $ipLong <= $endLong) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
