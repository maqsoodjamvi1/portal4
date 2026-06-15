<?php

namespace App\Libraries\Legacy;

use CodeIgniter\HTTP\ResponseInterface;

class LegacyOutputAdapter
{
    protected ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function set_output(string $data): void
    {
        $this->response->setBody($data)->send();
        exit;
    }
}
