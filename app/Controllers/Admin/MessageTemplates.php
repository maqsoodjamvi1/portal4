<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class MessageTemplates extends BaseController
{
    protected $db;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $campusId = session('member_campusid');

        $info = $this->db->table('campus')
                         ->where('campus_id', $campusId)
                         ->get()
                         ->getRow();

        return view('admin/message_templates', ['info' => $info]);
    }

    public function save()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                                  ->setJSON(['success' => false, 'msg' => 'Invalid request']);
        }

        $id = intval($this->request->getPost('id'));
        $userId = session('member_userid');
        $date = date('Y-m-d H:i:s');

        $data = [
            'welcome_sms'      => trim($this->request->getPost('welcome_sms')),
            'attendance_sms'   => trim($this->request->getPost('attendance_sms')),
            'student_fee_sms'  => trim($this->request->getPost('student_fee_sms')),
            'family_fee_sms'   => trim($this->request->getPost('family_fee_sms')),
            'updated_date'     => $date,
            'user_id'          => $userId,
        ];

        $this->db->transStart();
        $this->db->table('campus')->where('campus_id', $id)->update($data);
        $this->db->transComplete();

        return $this->response->setJSON([
            'success' => true,
            'msg'     => 'Message templates updated successfully',
        ]);
    }
}
