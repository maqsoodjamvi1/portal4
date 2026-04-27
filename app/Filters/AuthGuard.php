<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthGuard implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if (!$session->get('auth.logged_in')) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
