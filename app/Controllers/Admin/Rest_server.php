<?php
namespace App\Controllers\Admin;





class Rest_server extends BaseController {

    public function index()
    {
        $this->load->helper('url');

        $this->load->view('rest_server');
    }
}
