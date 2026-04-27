<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Fallback extends BaseController
{
    public function index()
    {
        // Load layout only, content will be injected
        return view('admin/layout');
    }
}
