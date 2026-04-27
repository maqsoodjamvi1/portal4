<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class ExpenseHead extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        check_permission('admin-account-heads');
    }

    public function index()
    {
        return view('admin/expense_head', []);
    }

    public function data(): ResponseInterface
    {
        $request = $this->request->getPost();
        $draw = $request['draw'] ?? 1;
        $length = $request['length'] ?? 10;
        $start = $request['start'] ?? 0;
        $schoolinfo = getSchoolInfo();
        $keyword = $request['search']['value'] ?? '';

        // Count
        $builder = $this->db->table('expense_heads A');
        $builder->selectCount('A.exp_head_id', 'ccount');
        $builder->where('A.system_id', $schoolinfo->system_id);
        if ($keyword) {
            $builder->where('A.head_title', $keyword);
        }
        $q = $builder->get()->getRow();
        $recordsTotal = $q->ccount ?? 0;

        // Result set
        $builder = $this->db->table('expense_heads A');
        $builder->select('A.*');
        $builder->where('A.system_id', $schoolinfo->system_id);
        if ($keyword) {
            $builder->where('A.head_title', $keyword);
        }
        $builder->orderBy('A.exp_head_id', 'desc');
        $builder->limit($length, $start);
        $results = $builder->get()->getResult();

        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'id' => $row->exp_head_id,
                'head_title' => $row->head_title,
                'detail' => $row->detail,
            ];
        }

        $response = [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-account-heads');
        $schoolinfo = getSchoolInfo();
        $info = $this->db->table('expense_heads')->where('system_id', $schoolinfo->system_id)->get()->getResult();
        return view('admin/expense_head_edit', [
            'info' => $info
        ]);
    }

    public function edit()
    {
        check_permission('admin-edit-account-heads');
        $exp_head_id = intval($this->request->getGet('id'));
        $info = $this->db->table('expense_heads')->where('exp_head_id', $exp_head_id)->get()->getRow();
        return view('admin/expense_head_edit', [
            'info' => $info
        ]);
    }

    public function save(): ResponseInterface
    {
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');
        $schoolinfo = getSchoolInfo();
        $rowscount = $this->request->getPost('rowscount');

        foreach ((array)$rowscount as $i) {
            $id = $this->request->getPost('id' . $i);
            $this->db->transStart();
            if ($id == 0) {
                $data = [
                    'head_title' => trim($this->request->getPost('head_title' . $i)),
                    'detail' => trim($this->request->getPost('detail' . $i)),
                    'system_id' => $schoolinfo->system_id,
                    'user_id' => $user_id,
                    'created_date' => $date
                ];
                $this->db->table('expense_heads')->insert($data);
            } else {
                $data = [
                    'head_title' => trim($this->request->getPost('head_title' . $i)),
                    'detail' => trim($this->request->getPost('detail' . $i)),
                    'user_id' => $user_id,
                    'updated_date' => $date
                ];
                $this->db->table('expense_heads')->where('exp_head_id', $id)->update($data);
            }
            $this->db->transComplete();
        }

        return $this->response->setJSON(['success' => true, 'msg' => 'Expense head update Success']);
    }

    public function delete(): ResponseInterface
    {
        check_permission('admin-del-account-heads');
        $id = intval($this->request->getGet('id'));
        $this->db->transStart();
        $this->db->table('expense_heads')->where('exp_head_id', $id)->delete();
        $this->db->transComplete();
        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Expense Head Success']);
    }
}
