<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class Grades extends BaseController
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
        check_permission('admin-grades');
        return view('admin/grades', []);
    }

    public function data()
    {
        $request = $this->request;
        $response = new stdClass;

        $response->draw = $request->getPost('draw');
        $schoolinfo = getSchoolInfo();

        $search = $request->getPost('search');
        $keyword = '';
        if ($search && isset($search['value'])) {
            $keyword = $search['value'];
        }

        // Total count
        $builder = $this->db->table('grades A');
        $builder->select('COUNT(A.gid) as ccount', false);
        $builder->where('A.system_id', $schoolinfo->system_id);
        if ($keyword) {
            $builder->where('A.name', $keyword);
        }
        $q = $builder->get()->getRow();
        $response->recordsTotal = $q->ccount;

        // Paginated results
        $builder2 = $this->db->table('grades A');
        $builder2->select('A.*');
        $builder2->where('A.system_id', $schoolinfo->system_id);
        if ($keyword) {
            $builder2->where('A.name', $keyword);
        }
        $builder2->orderBy('A.gid', 'desc');
        $length = (int) $request->getPost('length');
        $start = (int) $request->getPost('start');
        $builder2->limit($length, $start);
        $results = $builder2->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;

        $response->data = [];
        foreach ($results as $row) {
            $data = [];
            $data['id'] = $row->gid;
            $data['name'] = $row->name;
            $data['detail'] = $row->detail;
            $response->data[] = $data;
        }

        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-grades');
        $schoolinfo = getSchoolInfo();
        $info = $this->db->table('grades')
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getResult();

        $data['info'] = $info;
        return view('admin/grades_edit', $data);
    }

    public function edit()
    {
        check_permission('admin-edit-grades');
        $id = (int) $this->request->getGet('id');
        $info = $this->db->table('grades')->where('gid', $id)->get()->getRow();
        $data['info'] = $info;
        return view('admin/grades_edit', $data);
    }

    public function save()
    {
        $request = $this->request;
        $id = (int) $request->getPost('id');
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');
        $schoolinfo = getSchoolInfo();
        $rowscount = $request->getPost('rowscount');
        $is_f = $request->getPost('is_f');

        for ($i = 0; $i < count($rowscount); $i++) {
            $id = $request->getPost('id' . $i);

            if ('is_f_' . $i == $is_f) {
                $isF = 1;
            } else {
                $isF = 0;
            }

            $this->db->transBegin();

            $data = [
                'name'        => trim($request->getPost('name' . $i)),
                'detail'      => trim($request->getPost('detail' . $i)),
                'system_id'   => $schoolinfo->system_id,
                'is_f'        => $isF,
                'user_id'     => $user_id,
                'created_date'=> $date,
            ];

            if ($id == 0) {
                $this->db->table('grades')->insert($data);
            } else {
                $this->db->table('grades')->where('gid', $id)->update($data);
            }

            $this->db->transComplete();
        }

        // Use your own json_response helper or CI4 style below
        return $this->response->setJSON(['success' => true, 'msg' => 'Add Class Success']);
    }
}
