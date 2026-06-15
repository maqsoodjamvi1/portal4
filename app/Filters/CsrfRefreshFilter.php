<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Expose the current CSRF hash on every response so AJAX clients can stay in sync
 * when cookie-based CSRF regeneration is enabled (httponly cookie is not readable in JS).
 */
class CsrfRefreshFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return null;
        }

        $security = service('security');
        $hash     = $security->getHash();

        if ($hash !== null && $hash !== '') {
            $response->setHeader($security->getHeaderName(), $hash);
        }

        return null;
    }
}
