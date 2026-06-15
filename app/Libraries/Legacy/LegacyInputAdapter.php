<?php

namespace App\Libraries\Legacy;

use CodeIgniter\HTTP\IncomingRequest;

class LegacyInputAdapter
{
    protected IncomingRequest $request;

    public function __construct(IncomingRequest $request)
    {
        $this->request = $request;
    }

    public function post($key = null, $default = null)
    {
        if ($key === null) {
            return $this->request->getPost();
        }

        return $this->request->getPost($key) ?? $default;
    }

    public function get($key = null, $default = null)
    {
        if ($key === null) {
            return $this->request->getGet();
        }

        return $this->request->getGet($key) ?? $default;
    }
}
