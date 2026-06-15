<?php

namespace App\Libraries;

use Config\Email as EmailConfig;
use Config\Trial;

class ParentNotificationMailer
{
    protected Trial $trialConfig;

    protected EmailConfig $emailConfig;

    public function __construct(?Trial $trialConfig = null, ?EmailConfig $emailConfig = null)
    {
        $this->trialConfig = $trialConfig ?? config('Trial');
        $this->emailConfig = $emailConfig ?? config('Email');
    }

    public function send(string $email, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        $email = trim($email);
        if ($email === '' || $subject === '' || $htmlBody === '') {
            return false;
        }

        $fromEmail = $this->trialConfig->mailFromEmail !== ''
            ? $this->trialConfig->mailFromEmail
            : $this->emailConfig->fromEmail;
        $fromName  = $this->trialConfig->mailFromName !== ''
            ? $this->trialConfig->mailFromName
            : $this->emailConfig->fromName;

        if ($fromEmail === '') {
            return false;
        }

        $mailer = \Config\Services::email();
        $mailer->clear(true);
        $mailer->setFrom($fromEmail, $fromName !== '' ? $fromName : 'School');
        $mailer->setTo($email);
        $mailer->setSubject($subject);
        $mailer->setMessage($htmlBody);
        $mailer->setAltMessage($textBody !== '' ? $textBody : strip_tags($htmlBody));
        $mailer->setMailType('html');

        return $mailer->send(false);
    }

    public function feeDueReminder(string $email, string $parentName, string $studentName, string $amount, string $dueDate): bool
    {
        $subject = 'Fee reminder — ' . $studentName;
        $html    = '<p>Dear ' . esc($parentName) . ',</p>'
            . '<p>This is a reminder that fee of <strong>' . esc($amount) . '</strong> for '
            . esc($studentName) . ' was due on ' . esc($dueDate) . '.</p>'
            . '<p>Please contact the school office if you have already paid.</p>';

        return $this->send($email, $subject, $html);
    }
}
