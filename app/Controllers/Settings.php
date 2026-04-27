<?php
namespace App\Controllers;

class Settings extends BaseController
{
    public function setCurrency()
    {
        $code = $this->request->getPost('currency_code');
        if ($code) {
            session()->set('currency_code', $code);
        }
        return redirect()->back();
    }
}
