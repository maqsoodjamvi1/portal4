<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class School_Wizard extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {

         $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form']);
       
        
        
    }

    // Step 1: Load Wizard Page
    public function index()
    {
        $data = [
            'sessionData' => [
                'campusid' => $this->session->get('member_campusid'),
                'userid'   => $this->session->get('member_userid')

            ]
        ];
        return view('admin/school_wizard/school_wizard', $data);
    }

   

     public function saveStep1Class(): ResponseInterface
{
    $user_id = $this->session->get('member_userid');
    $schoolinfo = getSchoolInfo();
    $date = date('Y-m-d H:i:s');

    $rows = $this->request->getPost('rowscount'); // array of row indices

    if (!$rows || !is_array($rows)) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'No rows found in the request.'
        ]);
    }

    foreach ($rows as $i) {
        $class_name = trim($this->request->getPost('class_name' . $i));
        $class_short = trim($this->request->getPost('class_short_name' . $i));
        $id = (int)$this->request->getPost('id' . $i);

        if ($class_name === '' || $class_short === '') {
            continue; // skip empty rows
        }

        $data = [
            'class_name'       => $class_name,
            'class_short_name' => $class_short,
            'system_id'        => $schoolinfo->system_id,
            'user_id'          => $user_id,
            'created_date'     => $date,
            'status'           => 1
        ];

        if ($id === 0) {
            $this->db->table('classes')->insert($data);
        } else {
            $this->db->table('classes')->where('class_id', $id)->update($data);
        }
    }

    return $this->response->setJSON([
        'status' => 'success',
        'message' => 'Classes saved successfully.'
    ]);
}

 // Step 2: Handle AJAX Save
    public function saveWizardData(): ResponseInterface
    {
        $request = $this->request;
        $school_name  = $request->getPost('school_name');
        $system_code  = $request->getPost('system_code');
        $email        = $request->getPost('email');
        $phone        = $request->getPost('phone');
        $address      = $request->getPost('address');
        $user_id      = $this->session->get('member_userid');
        $campus_id    = $this->session->get('member_campusid');
        $created_date = date('Y-m-d H:i:s');

        try {
            $this->db->table('school_system')->insert([
                'system_name'   => $school_name,
                'system_code'   => $system_code,
                'email'         => $email,
                'phone'         => $phone,
                'address'       => $address,
                'created_date'  => $created_date,
                'user_id'       => $user_id,
                'campus_id'     => $campus_id
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'School information saved successfully.'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
