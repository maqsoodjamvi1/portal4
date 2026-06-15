<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class BoardPrepAuthFilter implements FilterInterface
{
    /** @var list<string> */
    private array $publicPaths = [
        '',
        'login',
        'signup',
        'logout',
        'api/captcha',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        helper('board_prep');

        $path = trim(service('uri')->getPath(), '/');
        $cfg  = board_prep_config();

        if (! board_prep_is_prep_subdomain()) {
            $prefix = board_prep_path_prefix();
            if ($prefix !== '') {
                if ($path === $prefix) {
                    $path = '';
                } elseif (str_starts_with($path, $prefix . '/')) {
                    $path = substr($path, strlen($prefix) + 1);
                }
            }
        }

        foreach ($this->publicPaths as $public) {
            if ($path === $public || ($public !== '' && str_starts_with($path, $public . '/'))) {
                return null;
            }
        }

        if (board_prep_auth()) {
            return null;
        }

        return redirect()->to(board_prep_url('login'))
            ->with('error', 'Please log in to continue.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
