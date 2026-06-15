<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Trial extends BaseConfig
{
    /** Trial subscription length in days. */
    public int $trialDays = 30;

    /** Default SaaS plan (system_plans.plan_id) — annual package only. */
    public int $defaultPlanId = 3;

    /** Billing period in months for the annual package. */
    public int $defaultInstallMonthCount = 12;

    /** Max signup attempts per IP within the throttle window. */
    public int $rateLimitAttempts = 5;

    /** Rate limit window in seconds. */
    public int $rateLimitWindow = 3600;

    /** Canonical signup URL shown on marketing CTAs (override in .env if needed). */
    public string $signupUrl = 'https://portal4.timesoftsol.com/signup';

    /** Product name shown on signup pages. */
    public string $productName = 'TLive Education';

    /**
     * When false, signup creates the school immediately (no OTP email).
     * Set trial.enableEmailVerification = true in .env when SMTP is ready.
     */
    public bool $enableEmailVerification = false;

    /** OTP length (digits). */
    public int $otpLength = 6;

    /** OTP validity in seconds. */
    public int $otpTtlSeconds = 600;

    /** Max wrong-code attempts before lockout. */
    public int $otpMaxAttempts = 5;

    /** Max OTP resend requests per pending signup. */
    public int $otpResendMax = 3;

    /** Minimum seconds between resend requests. */
    public int $otpResendCooldownSeconds = 60;

    /** Override from email (falls back to Config\Email::$fromEmail). */
    public string $mailFromEmail = '';

    /** Override from name (falls back to Config\Email::$fromName). */
    public string $mailFromName = '';
}
