<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class PreferredCurrencyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $s = session();
        if (!$s->has('currency_code')) {
            // Fallback; real default is set at login based on campus (see below)
            $s->set('currency_code', config('Currency')->defaultDisplay);
        }
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
