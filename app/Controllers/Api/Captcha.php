<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\SimpleCaptcha;

class Captcha extends BaseController
{
    /** Public endpoint — avoid admin layout / school session work. */
    protected $useLayout = false;

    public function index()
    {
        $securimagePath = FCPATH . 'resource/securimage/securimage.php';
        if (is_file($securimagePath)) {
            require_once $securimagePath;
            $img = new \Securimage();
            $img->show();
            exit;
        }

        (new SimpleCaptcha())->renderImage();
    }
}
