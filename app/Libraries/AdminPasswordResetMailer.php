<?php

namespace App\Libraries;

use Config\Email as EmailConfig;
use Config\Trial;

class AdminPasswordResetMailer
{
    protected Trial $trialConfig;

    protected EmailConfig $emailConfig;

    public function __construct(?Trial $trialConfig = null, ?EmailConfig $emailConfig = null)
    {
        $this->trialConfig = $trialConfig ?? config('Trial');
        $this->emailConfig = $emailConfig ?? config('Email');
    }

    public function sendResetLink(string $email, string $resetUrl, string $displayName = ''): bool
    {
        $email = trim($email);
        if ($email === '' || $resetUrl === '') {
            return false;
        }

        $productName = $this->trialConfig->productName;
        $fromEmail   = $this->trialConfig->mailFromEmail !== ''
            ? $this->trialConfig->mailFromEmail
            : $this->emailConfig->fromEmail;
        $fromName    = $this->trialConfig->mailFromName !== ''
            ? $this->trialConfig->mailFromName
            : $this->emailConfig->fromName;

        if ($fromEmail === '') {
            log_message('error', 'AdminPasswordResetMailer: fromEmail is not configured.');

            return false;
        }

        $greeting = $displayName !== '' ? "Hi {$displayName}," : 'Hi,';
        $html     = '<p>' . esc($greeting) . '</p>'
            . '<p>You requested a password reset for your ' . esc($productName) . ' admin account.</p>'
            . '<p><a href="' . esc($resetUrl) . '">Reset your password</a></p>'
            . '<p>This link expires in 24 hours. If you did not request this, you can ignore this email.</p>';
        $text     = "{$greeting}\n\n"
            . "Reset your password: {$resetUrl}\n\n"
            . "This link expires in 24 hours.\n";

        $mailer = \Config\Services::email();
        $mailer->clear(true);
        $mailer->setFrom($fromEmail, $fromName !== '' ? $fromName : $productName);
        $mailer->setTo($email);
        $mailer->setSubject($productName . ' — Password reset');
        $mailer->setMessage($html);
        $mailer->setAltMessage($text);
        $mailer->setMailType('html');

        if (! $mailer->send(false)) {
            log_message('error', 'AdminPasswordResetMailer: ' . $mailer->printDebugger(['headers', 'subject', 'body']));

            return false;
        }

        return true;
    }
}
