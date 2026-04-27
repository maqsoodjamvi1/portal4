<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use stdClass;

class AdmissionEnquiry extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db      = \Config\Database::connect();
        $this->session = \Config\Services::session();
        helper(['form', 'server']); // Assuming check_permission and json_response are defined
        check_permission('admin-enquiry');
    }

    public function index()
    {
        return view('admin/admission_enquiry', []);
    }

    public function data()
    {
        $campusid = $this->session->get('member_campusid');
        $request  = service('request');

        $response = new stdClass();
        $response->draw = $request->getPost('draw');
        $search = $request->getPost('search');
        $keyword = $search['value'] ?? '';

        $builder = $this->db->table('admission_enquiry A');
        $builder->select('COUNT(A.enquiry_id) AS ccount', false);
        $builder->where('A.campus_id', $campusid);
        if ($keyword) {
            $builder->where('A.name', $keyword);
        }
        $q = $builder->get()->getRow();
        $response->recordsTotal = (int) $q->ccount;

        $builder = $this->db->table('admission_enquiry A');
        $builder->select('A.*');
        $builder->where('A.campus_id', $campusid);
        if ($keyword) {
            $builder->where('A.name', $keyword);
        }
        $builder->orderBy('A.enquiry_id', 'DESC');
        $builder->limit((int)$request->getPost('length'), (int)$request->getPost('start'));
        $results = $builder->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];

        foreach ($results as $row) {
            $response->data[] = [
                'id'           => $row->enquiry_id,
                'name'         => $row->student_name,
                'father_name'  => $row->father_name,
                'contact'      => $row->father_phone,
                'mother_phone' => $row->mother_phone,
                'address'      => $row->address,
                'description'  => $row->description,
                'date'         => $row->date,
            ];
        }

        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-enquiry');
        return view('admin/admission_enquiry_edit', []);
    }

    public function edit()
    {
        check_permission('admin-edit-enquiry');
        $id = (int) $this->request->getGet('id');

        $info = $this->db->table('admission_enquiry')->where('enquiry_id', $id)->get()->getRow();
        return view('admin/admission_enquiry_edit', ['info' => $info]);
    }

    public function save()
    {
        $request  = service('request');
        $id       = (int) $request->getPost('id');
        $campusid = $this->session->get('member_campusid');
        $date     = date('Y-m-d H:i:s');
        $enqdate  = $request->getPost('date');

        $campusinfo = $this->db->table('campus')->where('campus_id', $campusid)->get()->getRow();

        $txtmessage = 'Dear ' . trim($request->getPost('student_name')) . ', ' .
                      ($campusinfo->welcome_sms ?? 'Welcome to Campus. Thanks for visiting us.');

        $data = [
            'student_name'    => trim($request->getPost('student_name')),
            'father_name'     => trim($request->getPost('father_name')),
            'student_age'     => trim($request->getPost('student_age')),
            'father_phone'    => trim($request->getPost('father_phone')),
            'mother_phone'    => trim($request->getPost('mother_phone')),
            'previous_school' => trim($request->getPost('previous_school')),
            'previous_fee'    => trim($request->getPost('previous_fee')),
            'address'         => trim($request->getPost('address')),
            'description'     => trim($request->getPost('description')),
            'campus_id'       => $campusid,
        ];

        if ($id === 0) {
            check_permission('admin-add-enquiry');
            $data['date']         = $enqdate;
            $data['created_date'] = $date;
            $this->db->table('admission_enquiry')->insert($data);
            json_response(['success' => true, 'msg' => 'Add Enquiry Success']);
        } else {
            check_permission('admin-edit-enquiry');
            $data['updated_date'] = $date;
            $this->db->table('admission_enquiry')->where('enquiry_id', $id)->update($data);
            json_response(['success' => true, 'msg' => 'Edit Enquiry Success']);
        }
    }

    public function delete()
    {
        check_permission('admin-del-enquiry');
        $id = (int) $this->request->getGet('id');

        $this->db->table('classes')->where('id', $id)->delete();
        json_response(['success' => true, 'msg' => 'Delete Question Quiz Success']);
    }
}
