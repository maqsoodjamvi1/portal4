<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Terms extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'text', 'custom']); // includes getSchoolInfo, json_response
        check_permission('admin-terms');
    }

    public function index()
    {
        return view('admin/terms');
    }

    public function data()
    {
        $request = service('request');
        $response = new \stdClass();
        $response->draw = $request->getPost('draw');
        $schoolinfo = getSchoolInfo();

        $keyword = $request->getPost('search')['value'] ?? '';

        $builder = $this->db->table('terms A');
        $builder->select('count(A.term_id) as ccount', false)
                ->where('A.system_id', $schoolinfo->system_id);

        if ($keyword) {
            $builder->like('A.name', $keyword);
        }

        $response->recordsTotal = $builder->get()->getRow()->ccount;
        $response->recordsFiltered = $response->recordsTotal;

        $builder = $this->db->table('terms A');
        $builder->select('A.*')
                ->where('A.system_id', $schoolinfo->system_id);

        if ($keyword) {
            $builder->like('A.name', $keyword);
        }

        $builder->orderBy('A.term_id', 'desc')
                ->limit($request->getPost('length'), $request->getPost('start'));

        $results = $builder->get()->getResult();

        $response->data = [];

        foreach ($results as $row) {
            $response->data[] = [
                'id'         => $row->term_id,
                'name'       => $row->name,
                'short_name' => $row->short_name
            ];
        }

        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-terms');
        $schoolinfo = getSchoolInfo();

        $info = $this->db->table('terms')
            ->where('system_id', $schoolinfo->system_id)
            ->get()
            ->getResult();

        return view('admin/term_edit', ['info' => $info]);
    }

    public function edit()
    {
        check_permission('admin-edit-terms');
        $schoolinfo = getSchoolInfo();

        $info = $this->db->table('terms')
            ->where('system_id', $schoolinfo->system_id)
            ->get()
            ->getResult();

        return view('admin/term_edit', ['info' => $info]);
    }

    public function save()
    {
        $request = $this->request;
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d');
        $schoolinfo = getSchoolInfo();
        $rowscount = $request->getPost('rowscount');

        if (!is_array($rowscount)) {
            return json_response(['success' => false, 'msg' => 'Invalid row data']);
        }

        for ($i = 0; $i < count($rowscount); $i++) {
            $id = (int)$request->getPost("id{$i}");
            $data = [
                'name'        => trim($request->getPost("name{$i}")),
                'short_name'  => trim($request->getPost("short_name{$i}")),
                'system_id'   => $schoolinfo->system_id,
                'user_id'          => $user_id,
            'status'           => 1,
            'created_date'     => $date
            ];

            if ($id === 0) {
                $data['created_date'] = $date;
                $this->db->table('terms')->insert($data);
            } else {
                $data['updated_date'] = $date;
                $this->db->table('terms')->where('term_id', $id)->update($data);
            }

            $this->db->transComplete();
        }

        $terms_session_info = $this->db->table('terms_session')
            ->where('system_id', $schoolinfo->system_id)
            ->get()
            ->getRow();

        if (empty($terms_session_info->term_id)) {
            return $this->response->setJSON(['term_session_id' => false, 'msg' => 'Terms Success']);
        }

        return json_response(['success' => true, 'msg' => 'Add Term Success']);
    }

    public function delete()
    {
        check_permission('admin-del-terms');
        $id = (int)$this->request->getGet('id');

        $this->db->transBegin();
        $this->db->table('terms')->where('term_id', $id)->delete();
        $this->db->transComplete();

        return json_response(['success' => true, 'msg' => 'Delete Term Success']);
    }
}
