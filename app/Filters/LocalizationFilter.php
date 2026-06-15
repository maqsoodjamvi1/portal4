<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class LocalizationFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $supported = config('App')->supportedLocales ?? ['en', 'ur', 'ar'];
        $language  = session('language');

        if (! $language && $request->getCookie('lang')) {
            $language = $request->getCookie('lang');
        }
        if (! $language && $request->getCookie('preferred_language')) {
            $language = $request->getCookie('preferred_language');
        }
        if (! $language || ! in_array($language, $supported, true)) {
            $language = config('App')->defaultLocale ?? 'en';
        }

        service('request')->setLocale($language);

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
