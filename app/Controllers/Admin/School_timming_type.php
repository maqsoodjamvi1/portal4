<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use stdClass;

class School_timming_type extends BaseController
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
        return view('admin/school_timming_type', $this->template_data);
    }

    public function data()
    {
        $response = new stdClass;
        $response->draw = $this->request->getPost('draw');
        $campusid = $this->session->get('member_campusid');

        $keyword = $this->request->getPost('search')['value'] ?? '';

        $builder = $this->db->table('school_timing_types A');
        $builder->selectCount('A.type_id', 'ccount');
        $builder->where('A.campus_id', $campusid);
        if ($keyword) {
            $builder->where('A.type_name', $keyword);
        }
        $response->recordsTotal = $builder->get()->getRow()->ccount;

        $builder = $this->db->table('school_timing_types A');
        $builder->select('A.*');
        $builder->where('A.campus_id', $campusid);
        if ($keyword) {
            $builder->where('A.type_name', $keyword);
        }
        $builder->orderBy('A.type_id', 'desc');
        $builder->limit($this->request->getPost('length'), $this->request->getPost('start'));

        $results = $builder->get()->getResult();
        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];

        foreach ($results as $row) {
            $data = [
                'id' => $row->type_id,
                'type_name' => $row->type_name,
                'short_name' => $row->short_name,
                'status' => $row->status
            ];
            $response->data[] = $data;
        }

        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-school-timing-type');
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');

        $this->template_data['sessionData'] = [
            'campusid' => $campusid,
            'sessionid' => $sessionid
        ];

        $this->template_data['info'] = $this->db->table('school_timing_types')
            ->where('campus_id', $campusid)
            ->get()->getResult();

        return view('admin/school_timming_type_edit', $this->template_data);
    }

    public function edit()
    {
        check_permission('admin-edit-exam');
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');

        $this->template_data['sessionData'] = [
            'campusid' => $campusid,
            'sessionid' => $sessionid
        ];

        $this->template_data['academic_session_info'] = $this->db->table('academic_session')->get()->getResult();
        $this->template_data['termsinfo'] = $this->db->table('terms')->get()->getResult();

        $id = (int) $this->request->getGet('id');
        $this->template_data['info'] = $this->db->table('exam')->where('eid', $id)->get()->getRow();

        return view('admin/exam_edit', $this->template_data);
    }

    public function save()
    {
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');
        $campusid = $this->session->get('member_campusid');
        $rowscount = $this->request->getPost('rowscount');

        foreach ($rowscount as $i => $val) {
            $id = (int) $this->request->getPost("id$i");

            $data = [
                'type_name' => trim($this->request->getPost("type_name$i")),
                'short_name' => trim($this->request->getPost("short_name$i")),
                'campus_id' => $campusid,
                'user_id' => $user_id,
                'created_date' => $date
            ];

            if ($id === 0) {
                $this->db->table('school_timing_types')->insert($data);
            } else {
                $this->db->table('school_timing_types')->where('type_id', $id)->update($data);
            }
        }

        return $this->response->setJSON(['success' => true, 'msg' => 'Record Updated Successfully']);
    }

    public function getDateRange()
    {
        $term_session_id = $this->request->getPost('term_session_id');
        $termSessioninfo = $this->db->table('terms_session')->where('term_session_id', $term_session_id)->get()->getRow();

        echo view('admin/partials/exam_date_range', ['termSessioninfo' => $termSessioninfo]);
    }

    public function delete()
    {
        check_permission('admin-del-exam');
        $id = (int) $this->request->getGet('id');

        $this->db->transBegin();
        $this->db->table('exam')->where('eid', $id)->delete();
        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Exam Success']);
    }
}