<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class LocalizationFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $locale = $request->getLocale();
        service('request')->setLocale($locale);
        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}