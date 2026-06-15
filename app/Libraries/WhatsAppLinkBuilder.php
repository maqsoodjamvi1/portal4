<?php

namespace App\Libraries;

/**
 * Builds wa.me deep links (manual WhatsApp — no Business API required).
 */
class WhatsAppLinkBuilder
{
    public static function messageLink(string $phoneE164, string $message): string
    {
        $digits = preg_replace('/\D+/', '', $phoneE164);
        if ($digits === '') {
            return '';
        }

        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($message);
    }
}
