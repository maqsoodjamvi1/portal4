<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use stdClass;

class SchoolSetup extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        //check_permission('admin-school-timing-type');
    }

    public function index()
    {
        return redirect()->to(base_url('admin/school_timing/add'));
    }

    public function saveStep1Class(): ResponseInterface
{
    $class_name = $this->request->getPost('class_name');
    $class_short = $this->request->getPost('class_short_name');
    $campus_id = $this->session->get('member_campusid');
    $user_id = $this->session->get('member_userid');

    try {
        $this->db->table('classes')->insert([
            'class_name'       => $class_name,
            'class_short_name' => $class_short,
            'campus_id'        => $campus_id,
            'user_id'          => $user_id,
            'created_date'     => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Class saved'
        ]);
    } catch (\Exception $e) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}
}
