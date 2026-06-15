<?php

namespace App\Controllers\BoardPrep;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BoardPrepBaseController extends BaseController
{
    protected $useLayout = false;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        helper(['form', 'url', 'board_prep']);
    }

    protected function boardPrepConfig(): \Config\BoardPrep
    {
        return config('BoardPrep');
    }

    protected function requireAuth(): ?\CodeIgniter\HTTP\RedirectResponse
    {
        if (board_prep_auth()) {
            return null;
        }

        return redirect()->to(board_prep_url('login'))->with('error', 'Please log in to continue.');
    }

    protected function checkSignupRateLimit(): bool
    {
        $cfg    = $this->boardPrepConfig();
        $ip     = (string) $this->request->getIPAddress();
        $key    = 'board_prep_signup_' . md5($ip);
        $window = (int) $cfg->rateLimitWindow;
        $max    = (int) $cfg->rateLimitAttempts;

        $bucket = session()->get($key);
        if (! is_array($bucket)) {
            $bucket = ['count' => 0, 'start' => time()];
        }

        if (time() - (int) ($bucket['start'] ?? 0) > $window) {
            $bucket = ['count' => 0, 'start' => time()];
        }

        if ((int) ($bucket['count'] ?? 0) >= $max) {
            return false;
        }

        $bucket['count'] = (int) ($bucket['count'] ?? 0) + 1;
        session()->set($key, $bucket);

        return true;
    }

    protected function verifyCaptcha(): bool
    {
        $code = trim((string) ($this->request->getPost('captcha') ?? $this->request->getPost('captcha_code')));
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
}
