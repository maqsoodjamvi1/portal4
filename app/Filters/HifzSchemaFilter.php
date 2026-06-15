<?php

namespace App\Filters;

use App\Libraries\HifzSchemaEnsurer;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Auto-applies Hifz DB schema when visiting admin/hifz (no CLI migrate on server).
 */
class HifzSchemaFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        (new HifzSchemaEnsurer())->ensure();
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
