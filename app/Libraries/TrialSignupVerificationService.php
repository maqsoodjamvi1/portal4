<?php

namespace App\Libraries;

use Config\Trial;

class TrialSignupVerificationService
{
    protected $db;

    protected Trial $config;

    protected SignupOtpMailer $mailer;

    protected TrialProvisioningService $provisioning;

    public function __construct(?Trial $config = null)
    {
        $this->db           = \Config\Database::connect();
        $this->config       = $config ?? config('Trial');
        $this->mailer       = new SignupOtpMailer($this->config);
        $this->provisioning = new TrialProvisioningService($this->config);
    }

    /**
     * @return array{success:bool,msg?:string,token?:string,email?:string}
     */
    public function createPending(array $input, string $ip): array
    {
        $schoolName = trim((string) ($input['school_name'] ?? ''));
        $firstName  = trim((string) ($input['first_name'] ?? ''));
        $lastName   = trim((string) ($input['last_name'] ?? ''));
        $phone      = trim((string) ($input['phone_no'] ?? ''));
        $email      = strtolower(trim((string) ($input['email'] ?? '')));
        $password   = (string) ($input['password'] ?? '');

        if ($schoolName === '' || $firstName === '' || $lastName === '' || $phone === '' || $email === '' || $password === '') {
            return ['success' => false, 'msg' => 'All fields are required.'];
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'msg' => 'Please enter a valid email address.'];
        }

        if ($this->provisioning->emailAlreadyRegistered($email)) {
            return ['success' => false, 'msg' => 'An account with this email already exists. Please sign in or use a different email.'];
        }

        $this->deleteStalePendingForEmail($email);

        $otp      = $this->generateOtp();
        $token    = bin2hex(random_bytes(32));
        $now      = date('Y-m-d H:i:s');
        $expires  = date('Y-m-d H:i:s', time() + (int) $this->config->otpTtlSeconds);
        $passHash = password_hash($password, PASSWORD_BCRYPT);

        $this->db->table('trial_signup_pending')->insert([
            'token'            => $token,
            'email'            => $email,
            'school_name'      => $schoolName,
            'first_name'       => $firstName,
            'last_name'        => $lastName,
            'phone_no'         => $phone,
            'password_hash'    => $passHash,
            'otp_hash'         => password_hash($otp, PASSWORD_BCRYPT),
            'otp_expires_at'   => $expires,
            'verify_attempts'  => 0,
            'resend_count'     => 0,
            'last_sent_at'     => $now,
            'ip_address'       => $ip,
            'created_at'       => $now,
            'verified_at'      => null,
        ]);

        if (! $this->mailer->sendVerificationCode($email, $otp, $firstName)) {
            $this->db->table('trial_signup_pending')->where('token', $token)->delete();

            return ['success' => false, 'msg' => 'We could not send the verification email. Please check your email address or try again later.'];
        }

        return [
            'success' => true,
            'token'   => $token,
            'email'   => $email,
        ];
    }

    /**
     * @return array{success:bool,msg?:string,row?:object}
     */
    public function verify(string $token, string $code): array
    {
        $row = $this->findActivePending($token);
        if (! $row) {
            return ['success' => false, 'msg' => 'This verification link is invalid or has expired. Please sign up again.'];
        }

        if ($row->verified_at !== null) {
            return ['success' => false, 'msg' => 'This signup has already been verified. Please sign in.'];
        }

        if ((int) $row->verify_attempts >= (int) $this->config->otpMaxAttempts) {
            return ['success' => false, 'msg' => 'Too many incorrect attempts. Please request a new code or start signup again.'];
        }

        if (strtotime((string) $row->otp_expires_at) < time()) {
            return ['success' => false, 'msg' => 'Your verification code has expired. Please request a new code.'];
        }

        $code = preg_replace('/\D+/', '', $code) ?? '';
        if (strlen($code) !== (int) $this->config->otpLength) {
            $this->incrementVerifyAttempts((int) $row->id, (int) $row->verify_attempts);

            return ['success' => false, 'msg' => $this->wrongCodeMessage((int) $row->verify_attempts + 1)];
        }

        if (! password_verify($code, (string) $row->otp_hash)) {
            $this->incrementVerifyAttempts((int) $row->id, (int) $row->verify_attempts);

            return ['success' => false, 'msg' => $this->wrongCodeMessage((int) $row->verify_attempts + 1)];
        }

        $this->db->table('trial_signup_pending')
            ->where('id', (int) $row->id)
            ->update(['verified_at' => date('Y-m-d H:i:s')]);

        $row->verified_at = date('Y-m-d H:i:s');

        return ['success' => true, 'row' => $row];
    }

    /**
     * @return array{success:bool,msg?:string}
     */
    public function resend(string $token): array
    {
        $row = $this->findActivePending($token);
        if (! $row) {
            return ['success' => false, 'msg' => 'This verification link is invalid or has expired. Please sign up again.'];
        }

        if ($row->verified_at !== null) {
            return ['success' => false, 'msg' => 'This signup has already been verified. Please sign in.'];
        }

        if ((int) $row->resend_count >= (int) $this->config->otpResendMax) {
            return ['success' => false, 'msg' => 'Maximum resend limit reached. Please start signup again.'];
        }

        $lastSent = $row->last_sent_at ? strtotime((string) $row->last_sent_at) : 0;
        $cooldown = (int) $this->config->otpResendCooldownSeconds;
        if ($lastSent > 0 && (time() - $lastSent) < $cooldown) {
            $wait = $cooldown - (time() - $lastSent);

            return ['success' => false, 'msg' => 'Please wait ' . $wait . ' seconds before requesting a new code.'];
        }

        $otp     = $this->generateOtp();
        $now     = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', time() + (int) $this->config->otpTtlSeconds);

        if (! $this->mailer->sendVerificationCode((string) $row->email, $otp, (string) $row->first_name)) {
            return ['success' => false, 'msg' => 'We could not send the verification email. Please try again later.'];
        }

        $this->db->table('trial_signup_pending')
            ->where('id', (int) $row->id)
            ->update([
                'otp_hash'        => password_hash($otp, PASSWORD_BCRYPT),
                'otp_expires_at'  => $expires,
                'verify_attempts' => 0,
                'resend_count'    => (int) $row->resend_count + 1,
                'last_sent_at'    => $now,
            ]);

        return ['success' => true, 'msg' => 'A new verification code has been sent to your email.'];
    }

    /**
     * @return array{school_name:string,first_name:string,last_name:string,phone_no:string,email:string,password_hash:string}|null
     */
    public function getProvisionPayload(object $row): ?array
    {
        if ($row->verified_at === null) {
            return null;
        }

        return [
            'school_name'   => (string) $row->school_name,
            'first_name'    => (string) $row->first_name,
            'last_name'     => (string) $row->last_name,
            'phone_no'      => (string) $row->phone_no,
            'email'         => (string) $row->email,
            'password_hash' => (string) $row->password_hash,
        ];
    }

    public function deletePending(string $token): void
    {
        $this->db->table('trial_signup_pending')->where('token', $token)->delete();
    }

    public function findByToken(string $token): ?object
    {
        if ($token === '') {
            return null;
        }

        return $this->db->table('trial_signup_pending')
            ->where('token', $token)
            ->get()
            ->getRow();
    }

    public static function maskEmail(string $email): string
    {
        $email = trim($email);
        if (! str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $len = strlen($local);
        if ($len <= 1) {
            $masked = '*';
        } elseif ($len <= 3) {
            $masked = substr($local, 0, 1) . str_repeat('*', $len - 1);
        } else {
            $masked = substr($local, 0, 2) . str_repeat('*', max(1, $len - 3)) . substr($local, -1);
        }

        return $masked . '@' . $domain;
    }

    protected function findActivePending(string $token): ?object
    {
        $row = $this->findByToken($token);
        if (! $row) {
            return null;
        }

        $maxAge = 86400;
        if (strtotime((string) $row->created_at) < (time() - $maxAge)) {
            $this->db->table('trial_signup_pending')->where('id', (int) $row->id)->delete();

            return null;
        }

        return $row;
    }

    protected function deleteStalePendingForEmail(string $email): void
    {
        $this->db->table('trial_signup_pending')
            ->where('email', $email)
            ->where('verified_at', null)
            ->delete();
    }

    protected function incrementVerifyAttempts(int $id, int $currentAttempts): void
    {
        $this->db->table('trial_signup_pending')
            ->where('id', $id)
            ->update(['verify_attempts' => $currentAttempts + 1]);
    }

    protected function wrongCodeMessage(int $attemptsAfter): string
    {
        $remaining = max(0, (int) $this->config->otpMaxAttempts - $attemptsAfter);
        if ($remaining <= 0) {
            return 'Too many incorrect attempts. Please request a new code or start signup again.';
        }

        return 'Incorrect verification code. ' . $remaining . ' attempt(s) remaining.';
    }

    protected function generateOtp(): string
    {
        $length = (int) $this->config->otpLength;
        if ($length !== 6) {
            $length = 6;
        }

        $min = 10 ** ($length - 1);
        $max = (10 ** $length) - 1;

        return (string) random_int($min, $max);
    }
}
