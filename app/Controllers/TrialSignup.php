<?php

namespace App\Controllers;

use App\Libraries\MemberCurrentUser;
use App\Libraries\TrialProvisioningService;
use App\Libraries\TrialSignupVerificationService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Trial;
use Psr\Log\LoggerInterface;

class TrialSignup extends BaseController
{
    protected Trial $trialConfig;

    /** Public page — skip admin layout / school session bootstrap. */
    protected $useLayout = false;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->trialConfig = config('Trial');
        helper(['form', 'url']);
    }

    public function index()
    {
        if (session()->get('IsAuthorized')) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        $this->syncEmbedModeFromRequest();
        $this->applyTrialSignupFramePolicy();

        return view('trial_signup/form', [
            'trialDays'                  => $this->trialConfig->trialDays,
            'productName'                => $this->trialConfig->productName,
            'emailVerificationEnabled'   => $this->isEmailVerificationEnabled(),
            'embed'                      => $this->isEmbedMode(),
            'errors'                     => session()->getFlashdata('errors') ?? [],
            'error'                      => session()->getFlashdata('error'),
        ]);
    }

    public function submit()
    {
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('signup'));
        }

        if (! $this->checkRateLimit()) {
            return redirect()->back()->withInput()->with('error', 'Too many signup attempts. Please wait an hour and try again.');
        }

        if (! $this->verifyCaptcha()) {
            return redirect()->back()->withInput()->with('error', 'Incorrect or expired security code. Enter the code from the image (click the image if you need a new one).');
        }

        $phoneNo = $this->normalizePhoneNumber((string) $this->request->getPost('phone_no'));
        if ($phoneNo === '') {
            return redirect()->back()->withInput()->with('errors', [
                'phone_no' => 'Enter a valid local or international phone number (10–15 digits).',
            ]);
        }

        $validation = service('validation');
        $validation->setRules([
            'school_name' => 'required|min_length[2]|max_length[255]',
            'first_name'  => 'required|min_length[1]|max_length[100]',
            'last_name'   => 'required|min_length[1]|max_length[100]',
            'email'       => 'required|valid_email|max_length[254]',
            'password'    => 'required|min_length[8]|max_length[15]',
            'repassword'  => 'required|matches[password]',
        ], [
            'school_name' => ['required' => 'School name is required.'],
            'first_name'  => ['required' => 'First name is required.'],
            'last_name'   => ['required' => 'Last name is required.'],
            'email'       => [
                'required'    => 'Email is required.',
                'valid_email' => 'Please enter a valid email address.',
            ],
            'password' => [
                'required'   => 'Password is required.',
                'min_length' => 'Password must be at least 8 characters.',
                'max_length' => 'Password must be at most 15 characters.',
            ],
            'repassword' => [
                'required' => 'Please confirm your password.',
                'matches'  => 'Passwords do not match.',
            ],
        ]);

        if (! $validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $signupInput = [
            'school_name' => $this->request->getPost('school_name'),
            'first_name'  => $this->request->getPost('first_name'),
            'last_name'   => $this->request->getPost('last_name'),
            'phone_no'    => $phoneNo,
            'email'       => $this->request->getPost('email'),
            'password'    => $this->request->getPost('password'),
        ];

        if ($this->isEmailVerificationEnabled()) {
            $verification = new TrialSignupVerificationService();
            $result       = $verification->createPending($signupInput, (string) $this->request->getIPAddress());

            if (! ($result['success'] ?? false)) {
                return redirect()->back()->withInput()->with('error', $result['msg'] ?? 'Registration failed.');
            }

            return redirect()->to(base_url('signup/verify?token=' . urlencode((string) $result['token']) . $this->embedQuerySuffix('&')));
        }

        $service = new TrialProvisioningService();
        $result  = $service->provision($signupInput);

        if (! ($result['success'] ?? false)) {
            return redirect()->back()->withInput()->with('error', $result['msg'] ?? 'Account could not be created.');
        }

        return $this->finishSignup(
            $result,
            trim((string) $this->request->getPost('school_name')),
            (string) $this->request->getPost('password'),
            trim((string) $this->request->getPost('email'))
        );
    }

    public function verify()
    {
        if (! $this->isEmailVerificationEnabled()) {
            return redirect()->to(base_url('signup'));
        }

        if (session()->get('IsAuthorized')) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        $this->syncEmbedModeFromRequest();

        if ($this->request->is('post')) {
            return $this->verifySubmit();
        }

        $token = trim((string) $this->request->getGet('token'));
        if ($token === '') {
            return redirect()->to(base_url('signup'))->with('error', 'Invalid verification link.');
        }

        $verification = new TrialSignupVerificationService();
        $row          = $verification->findByToken($token);
        if (! $row || ($row->verified_at !== null)) {
            if ($row && $row->verified_at !== null) {
                return redirect()->to(base_url('admin/login'))->with('error', 'This signup is already verified. Please sign in.');
            }

            return redirect()->to(base_url('signup'))->with('error', 'This verification link is invalid or has expired.');
        }

        $this->applyTrialSignupFramePolicy();

        return view('trial_signup/verify', [
            'productName'    => $this->trialConfig->productName,
            'token'          => $token,
            'maskedEmail'    => TrialSignupVerificationService::maskEmail((string) $row->email),
            'resendCooldown' => $this->trialConfig->otpResendCooldownSeconds,
            'embed'          => $this->isEmbedMode(),
            'error'          => session()->getFlashdata('error'),
            'success'        => session()->getFlashdata('success'),
        ]);
    }

    public function verifySubmit()
    {
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('signup'));
        }

        if (! $this->checkVerifyRateLimit()) {
            return redirect()->back()->with('error', 'Too many verification attempts. Please wait and try again.');
        }

        $token = trim((string) $this->request->getPost('token'));
        $code  = trim((string) $this->request->getPost('otp_code'));

        if ($token === '') {
            return redirect()->to(base_url('signup'))->with('error', 'Invalid verification link.');
        }

        $verification = new TrialSignupVerificationService();
        $verifyResult = $verification->verify($token, $code);

        if (! ($verifyResult['success'] ?? false)) {
            return redirect()->to(base_url('signup/verify?token=' . urlencode($token) . $this->embedQuerySuffix('&')))
                ->with('error', $verifyResult['msg'] ?? 'Verification failed.');
        }

        $row     = $verifyResult['row'];
        $payload = $verification->getProvisionPayload($row);
        if ($payload === null) {
            return redirect()->to(base_url('signup'))->with('error', 'Verification state is invalid. Please sign up again.');
        }

        $provision = new TrialProvisioningService();
        $result    = $provision->provision([
            'school_name' => $payload['school_name'],
            'first_name'  => $payload['first_name'],
            'last_name'   => $payload['last_name'],
            'phone_no'    => $payload['phone_no'],
            'email'       => $payload['email'],
            'password'    => $payload['password_hash'],
        ], true);

        if (! ($result['success'] ?? false)) {
            return redirect()->to(base_url('signup'))->with('error', $result['msg'] ?? 'Account could not be created.');
        }

        $verification->deletePending($token);

        return $this->finishSignup(
            $result,
            trim($payload['school_name']),
            '',
            $payload['email'],
            true
        );
    }

    public function resend()
    {
        if (! $this->isEmailVerificationEnabled()) {
            return redirect()->to(base_url('signup'));
        }

        if (! $this->request->is('post')) {
            return redirect()->to(base_url('signup'));
        }

        if (! $this->checkResendRateLimit()) {
            return redirect()->back()->with('error', 'Too many resend requests. Please wait and try again.');
        }

        $token = trim((string) $this->request->getPost('token'));
        if ($token === '') {
            return redirect()->to(base_url('signup'))->with('error', 'Invalid verification link.');
        }

        $verification = new TrialSignupVerificationService();
        $result       = $verification->resend($token);

        $redirect = redirect()->to(base_url('signup/verify?token=' . urlencode($token) . $this->embedQuerySuffix('&')));
        if ($result['success'] ?? false) {
            return $redirect->with('success', $result['msg'] ?? 'A new code has been sent.');
        }

        return $redirect->with('error', $result['msg'] ?? 'Could not resend code.');
    }

    public function success()
    {
        $this->syncEmbedModeFromRequest();
        $data = session()->get('trial_signup_success');
        if (! is_array($data) || empty($data['username'])) {
            return redirect()->to(base_url('signup'))->with('error', 'Your trial session expired. If you completed signup, use Sign in with your email and password.');
        }

        session()->remove('trial_signup_success');

        $this->applyTrialSignupFramePolicy();

        return view('trial_signup/success', [
            'schoolName'      => $data['school_name'] ?? '',
            'username'        => $data['username'] ?? '',
            'email'           => $data['email'] ?? '',
            'expiryDays'      => (int) ($data['expiry_days'] ?? $this->trialConfig->trialDays),
            'productName'     => $this->trialConfig->productName,
            'loginUrl'        => $data['login_url'] ?? base_url('admin/login'),
            'autoLoginFailed' => ! empty($data['auto_login_failed']),
            'embed'           => $this->isEmbedMode(),
        ]);
    }

    protected function syncEmbedModeFromRequest(): void
    {
        $embed = $this->request->getGet('embed');
        if ($embed === '1') {
            session()->set('trial_signup_embed', true);
        } elseif ($embed === '0') {
            session()->remove('trial_signup_embed');
        }
    }

    protected function isEmbedMode(): bool
    {
        if ($this->request->getGet('embed') === '1' || $this->request->getPost('embed') === '1') {
            return true;
        }

        return session()->get('trial_signup_embed') === true;
    }

    protected function embedQuerySuffix(string $prefix = '?'): string
    {
        return $this->isEmbedMode() ? $prefix . 'embed=1' : '';
    }

    /** Allow timesoftsol.com marketing site to embed signup in an iframe. */
    protected function applyTrialSignupFramePolicy(): void
    {
        $this->response->setHeader(
            'Content-Security-Policy',
            "frame-ancestors 'self' https://timesoftsol.com https://www.timesoftsol.com http://localhost http://127.0.0.1"
        );
    }

    protected function checkRateLimit(): bool
    {
        $throttler = service('throttler');
        $key       = 'trial_signup_' . md5((string) $this->request->getIPAddress());

        return $throttler->check(
            $key,
            (int) $this->trialConfig->rateLimitAttempts,
            (int) $this->trialConfig->rateLimitWindow
        );
    }

    protected function checkVerifyRateLimit(): bool
    {
        $throttler = service('throttler');
        $key       = 'trial_signup_verify_' . md5((string) $this->request->getIPAddress());

        return $throttler->check($key, 20, 3600);
    }

    protected function checkResendRateLimit(): bool
    {
        $throttler = service('throttler');
        $key       = 'trial_signup_resend_' . md5((string) $this->request->getIPAddress());

        return $throttler->check($key, 10, 3600);
    }

    protected function verifyCaptcha(): bool
    {
        $code = trim((string) $this->request->getPost('captcha_code'));
        if ($code === '') {
            return false;
        }

        $securimagePath = FCPATH . 'resource/securimage/securimage.php';
        if (is_file($securimagePath)) {
            require_once $securimagePath;

            return (bool) (new \Securimage())->check($code);
        }

        return (new \App\Libraries\SimpleCaptcha())->verify($code);
    }

    protected function normalizePhoneNumber(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }

        $digits   = preg_replace('/\D+/', '', $raw) ?? '';
        $digitLen = strlen($digits);

        if ($digitLen < 10 || $digitLen > 15) {
            return '';
        }

        // Pakistan local mobile: 03XXXXXXXXX
        if (str_starts_with($digits, '0') && $digitLen >= 10 && $digitLen <= 11) {
            return $digits;
        }

        // 92XXXXXXXXXX without leading +
        if (str_starts_with($digits, '92') && $digitLen >= 11 && $digitLen <= 12) {
            return '+' . $digits;
        }

        if (str_starts_with($raw, '+')) {
            return '+' . $digits;
        }

        return '+' . $digits;
    }

    protected function isEmailVerificationEnabled(): bool
    {
        return (bool) $this->trialConfig->enableEmailVerification;
    }

    /**
     * @param array<string,mixed> $result Provision result from TrialProvisioningService
     */
    protected function finishSignup(
        array $result,
        string $schoolName,
        string $plainPassword,
        string $email,
        bool $skipPasswordLogin = false
    ) {
        $username = (string) ($result['username'] ?? $email);
        $userId   = (int) ($result['user_id'] ?? $result['id'] ?? 0);
        $user     = false;

        if ($userId > 0) {
            $user = MemberCurrentUser::loginByUserId($userId);
        }

        if (! $user && ! $skipPasswordLogin && $plainPassword !== '') {
            $member = new MemberCurrentUser();
            $user   = $member->login($username, $plainPassword);
        }

        $successPayload = [
            'school_name' => $schoolName,
            'username'    => $username,
            'email'       => $email,
            'expiry_days' => $this->trialConfig->trialDays,
            'login_url'   => base_url('admin/login'),
        ];

        if ($user) {
            session()->setFlashdata('trial_welcome', [
                'school_name' => $schoolName,
                'expiry_days' => $this->trialConfig->trialDays,
            ]);

            return redirect()->to(base_url('admin/profile_system'));
        }

        $successPayload['auto_login_failed'] = true;
        session()->set('trial_signup_success', $successPayload);

        return redirect()->to(base_url('signup/success'));
    }
}
