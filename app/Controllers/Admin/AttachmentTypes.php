<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class AttachmentTypes extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = \Config\Database::connect();
        $this->session = session();
    }

    public function index()
    {
        check_permission('admin-attachment-types');
        return view('admin/attachment_types', []);
    }

    public function data()
    {
        $response = new stdClass();
        $schoolinfo = getSchoolInfo();
        $request = $this->request->getPost();

        $draw = $request['draw'] ?? 1;
        $search = $request['search']['value'] ?? '';
        $start = $request['start'] ?? 0;
        $length = $request['length'] ?? 10;

        // Count
        $builder = $this->db->table('attachement_types A');
        $builder->selectCount('A.a_type_id', 'ccount');
        $builder->where('A.system_id', $schoolinfo->system_id);
        if ($search) {
            $builder->where('A.a_type_name', $search);
        }
        $q = $builder->get()->getRow();
        $response->recordsTotal = $q->ccount;

        // Results
        $builder = $this->db->table('attachement_types A');
        $builder->select('A.*');
        $builder->where('A.system_id', $schoolinfo->system_id);
        if ($search) {
            $builder->where('A.a_type_name', $search);
        }
        $builder->orderBy('A.a_type_id', 'desc');
        $builder->limit($length, $start);
        $results = $builder->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;
        $response->draw = $draw;
        $response->data = [];

        foreach ($results as $row) {
            $data = [
                'id' => $row->a_type_id,
                'a_type_name' => $row->a_type_name,
                'a_type_detail' => $row->a_type_detail,
            ];
            $response->data[] = $data;
        }

        return $this->response->setJSON($response);
    }

    public function add()
    {
        // check_permission('admin-add-attachment-types');
        $schoolinfo = getSchoolInfo();

        $info = $this->db->table('attachement_types')
            ->where('system_id', $schoolinfo->system_id)
            ->get()
            ->getResult();
        return view('admin/attachment_types_edit', ['info' => $info]);
    }

    public function edit()
    {
        // check_permission('admin-edit-attachment-types');
        $fee_type_id = intval($this->request->getGet('id'));
        $info = $this->db->table('fee_type')
            ->where('fee_type_id', $fee_type_id)
            ->get()
            ->getRow();
        return view('fee_type_edit', ['info' => $info]);
    }

    public function save()
    {
        $id = intval($this->request->getPost('id'));
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d');
        $schoolinfo = getSchoolInfo();
        $rowscount = $this->request->getPost('rowscount');

        // check_permission('admin-add-attachment-types');

        if (!is_array($rowscount)) {
            $rowscount = [];
        }

        foreach ($rowscount as $i) {
            $rowId = $this->request->getPost('id' . $i);

            if ($rowId == 0) {
                $data = [
                    'a_type_name' => trim($this->request->getPost('a_type_name' . $i)),
                    'a_type_detail' => trim($this->request->getPost('a_type_detail' . $i)),
                    'system_id' => $schoolinfo->system_id,
                    'user_id' => $user_id,
                    'created_date' => $date,
                ];
                $this->db->table('attachement_types')->insert($data);
            } else {
                $data = [
                    'a_type_name' => trim($this->request->getPost('a_type_name' . $i)),
                    'a_type_detail' => trim($this->request->getPost('a_type_detail' . $i)),
                    'user_id' => $user_id,
                    'updated_date' => $date,
                ];
                $this->db->table('attachement_types')
                    ->where('a_type_id', $rowId)
                    ->update($data);
            }
        }
        return $this->response->setJSON(['success' => true, 'msg' => 'Add Attachment Type Success']);
    }
}
