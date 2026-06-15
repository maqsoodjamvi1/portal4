<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Requires parent/student portal login on /student/* routes (except login/logout).
 */
class PortalAuthFilter implements FilterInterface
{
    /** @var list<string> */
    private array $publicPaths = [
        'student/login',
        'student/logout',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        $path = trim(service('uri')->getPath(), '/');

        foreach ($this->publicPaths as $public) {
            if ($path === $public || str_starts_with($path, $public . '/')) {
                return null;
            }
        }

        if (session()->get('auth.logged_in')) {
            return null;
        }

        $auth = session()->get('auth');
        if (is_array($auth) && ! empty($auth['logged_in'])) {
            return null;
        }

        // Admin "play quiz as student" link — no parent/student portal login required
        if (session()->get('impersonate') && (int) session()->get('impersonated_student_id') > 0) {
            if ($path === 'student/quizzes' || str_starts_with($path, 'student/quizzes/')) {
                return null;
            }
        }

        return redirect()->to(base_url('student/login'))
            ->with('error', 'Please log in to continue.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
