<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AcademicSession extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'text', 'server']); // custom includes: getSchoolInfo, json_response, dateFormat
        check_permission('admin-add-academic-session');
    }

    public function index()
    {
        return view('admin/academic_session', []);
    }

    public function data()
    {
        $request = service('request');
        $response = new \stdClass();
        $response->draw = $request->getPost('draw');
        $schoolinfo = getSchoolInfo();

        $search = $request->getPost('search');
        $keyword = $search['value'] ?? '';

        $builder = $this->db->table('academic_session A');
        $builder->select('count(A.session_id) as ccount', false)
                ->where('A.system_id', $schoolinfo->system_id);

        if ($keyword) {
            $builder->like('A.session_name', $keyword);
        }

        $q = $builder->get()->getRow();
        $response->recordsTotal = $q->ccount;
        $response->recordsFiltered = $q->ccount;

        $builder = $this->db->table('academic_session A');
        $builder->select('A.*')
                ->where('A.system_id', $schoolinfo->system_id);

        if ($keyword) {
            $builder->like('A.session_name', $keyword);
        }

        $builder->orderBy('A.session_id', 'desc')
                ->limit($request->getPost('length'), $request->getPost('start'));

        $results = $builder->get()->getResult();

        $response->data = [];

        foreach ($results as $row) {
            $response->data[] = [
                'id' => $row->session_id,
                'session_name' => $row->session_name,
                'start_date' => dateFormat($row->start_date),
                'end_date' => dateFormat($row->end_date)
            ];
        }

        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-academic-session');
        $schoolinfo = getSchoolInfo();
        $academic_session = $this->db->query('SELECT * FROM academic_session WHERE system_id=' . $schoolinfo->system_id . ' ORDER BY session_id DESC')->getRow();

        return view('admin/academic_session_edit', ['academic_session_info' => $academic_session]);
    }

    

    public function edit($id = null)
    {
        check_permission('admin-edit-academic-session');

        // prefer segment, fallback to ?id=
        $id = $id ?? (int) $this->request->getGet('id');
        if (!$id) {
            // no id provided: go back safely
            return redirect()->to(site_url('admin/academic_session'))
                             ->with('error', 'Missing session id.');
        }

        $info = $this->db->table('academic_session')->where('session_id', $id)->get()->getRow();
        return view('admin/academic_session_edit', ['info' => $info]);
    }


    public function save()
    {
        $request = $this->request;
        $id = (int)$request->getPost('id');
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');
        $schoolinfo = getSchoolInfo();

        $start_date = $request->getPost('start_date');
        $end_date = $request->getPost('end_date');

        if ($end_date < $start_date) {
            return json_response(['success' => false, 'msg' => 'End date should be greater than start date']);
        }

        if (!$this->validate(['session_name' => 'required'])) {
            return json_response(['success' => false, 'msg' => implode(' ', $this->validator->getErrors())]);
        }

        $data = [
            'session_name' => trim($request->getPost('session_name')),
            'start_date' => $start_date,
            'end_date' => $end_date,
            'user_id' => $user_id
        ];

        if ($id === 0) {
            check_permission('admin-add-academic-session');
            $this->db->transBegin();

            $data['system_id'] = $schoolinfo->system_id;
            $data['created_date'] = $date;

            $this->db->table('academic_session')->insert($data);
            $new_session_id = $this->db->insertID();

            $this->session->set('member_sessionid', $new_session_id);

            $this->db->transComplete();

            $terms_info = $this->db->table('terms')->where('system_id', $schoolinfo->system_id)->get()->getRow();
            if (empty($terms_info->term_id)) {
                return $this->response->setJSON(['term_id' => false, 'msg' => 'Session Success']);
            }

            return json_response(['success' => true, 'msg' => 'Add Academic Session Success']);
        } else {
            check_permission('admin-edit-academic-session');
            $data['updated_date'] = $date;

            $this->db->transBegin();
            $this->db->table('academic_session')->where('session_id', $id)->update($data);
            $this->db->transComplete();

            return json_response(['success' => true, 'msg' => 'Edit Academic Session Success']);
        }
    }

   
}
