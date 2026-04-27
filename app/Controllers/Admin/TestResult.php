// app/Controllers/Admin/TestResults.php
<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class TestResults extends BaseController
{
    public function card()
    {
        return view('admin/test_result_card');
    }

     public function ping()
    {
        // Shows which file/class PHP actually loaded
        return $this->response->setBody('CONTROLLER OK: ' . __FILE__);
    }

    public function cardData()
    {
        return $this->response->setJSON(['ok' => true]);
    }
}
