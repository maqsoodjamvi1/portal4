<?php

namespace App\Libraries;

use Config\Email as EmailConfig;
use Config\Trial;

class SignupOtpMailer
{
    protected Trial $trialConfig;

    protected EmailConfig $emailConfig;

    public function __construct(?Trial $trialConfig = null, ?EmailConfig $emailConfig = null)
    {
        $this->trialConfig = $trialConfig ?? config('Trial');
        $this->emailConfig = $emailConfig ?? config('Email');
    }

    public function sendVerificationCode(string $email, string $code, string $firstName): bool
    {
        $email = trim($email);
        if ($email === '' || $code === '') {
            return false;
        }

        $productName = $this->trialConfig->productName;
        $ttlMinutes  = (int) max(1, round($this->trialConfig->otpTtlSeconds / 60));
        $fromEmail   = $this->trialConfig->mailFromEmail !== ''
            ? $this->trialConfig->mailFromEmail
            : $this->emailConfig->fromEmail;
        $fromName    = $this->trialConfig->mailFromName !== ''
            ? $this->trialConfig->mailFromName
            : $this->emailConfig->fromName;

        if ($fromEmail === '') {
            log_message('error', 'SignupOtpMailer: fromEmail is not configured.');

            return false;
        }

        $html = view('emails/trial_signup_otp', [
            'firstName'   => $firstName,
            'code'        => $code,
            'productName' => $productName,
            'ttlMinutes'  => $ttlMinutes,
        ]);

        $text = "Hi {$firstName},\n\n"
            . "Your {$productName} verification code is: {$code}\n\n"
            . "This code expires in {$ttlMinutes} minutes. If you did not request this, you can ignore this email.\n";

        $mailer = \Config\Services::email();
        $mailer->clear(true);
        $mailer->setFrom($fromEmail, $fromName !== '' ? $fromName : $productName);
        $mailer->setTo($email);
        $mailer->setSubject($productName . ' — Email verification code');
        $mailer->setMessage($html);
        $mailer->setAltMessage($text);
        $mailer->setMailType('html');

        if (! $mailer->send()) {
            log_message('error', 'SignupOtpMailer failed for {email}: {debug}', [
                'email' => $email,
                'debug' => $mailer->printDebugger(['headers', 'subject']),
            ]);

            return false;
        }

        return true;
    }
}
