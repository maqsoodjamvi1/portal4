<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class PublicBaseController extends Controller
{
    protected $request;
    protected $session;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);

        $this->session = \Config\Services::session();
        helper(['url', 'form']);
    }
}
