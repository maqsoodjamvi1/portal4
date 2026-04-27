<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;
use DateTime;

class LeavingCertificate extends BaseController
{
    protected $db;

    public function __construct()
    {
        helper(['form', 'url']);
        check_permission('admin-users');
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        return view('admin/leaving_certificate', []);
    }

    public function data()
    {
        $request = service('request');
        $response = new stdClass();
        $response->draw = $request->getPost('draw');
        $keyword = $request->getPost('search')['value'] ?? '';

        $builder = $this->db->table('students A');
        $builder->select('COUNT(A.student_id) as ccount', false);
        if ($keyword) {
            $builder->where("(A.first_name=" . $this->db->escape($keyword) . " OR A.reg_no=" . $this->db->escape($keyword) . ")");
        }
        $q = $builder->get()->getRow();
        $response->recordsTotal = $q->ccount;

        $builder = $this->db->table('students A');
        $builder->select('A.*');
        if ($keyword) {
            $builder->where("(A.first_name=" . $this->db->escape($keyword) . " OR A.reg_no=" . $this->db->escape($keyword) . ")");
        }
        $builder->orderBy('A.student_id', 'desc');
        $builder->limit($request->getPost('length'), $request->getPost('start'));
        $results = $builder->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];

        foreach ($results as $row) {
            $data = [];
            $data['id'] = $row->student_id;
            $imgurl = FCPATH . "uploads/" . $row->profile_photo;
            if (file_exists($imgurl)) {
                $data['profile_photo'] = "<img style='width:50px;height:50px;text-align: center;display: block;border-radius: 30px;margin: 0 auto;' src='/timeschool/uploads/" . $row->profile_photo . "' >";
            } else {
                $data['profile_photo'] = "<i style='font-size: 40px;text-align: center;display: block;' class='fa fa-user'></i>";
            }
            $data['reg_no'] = $row->reg_no;
            $data['name'] = $row->first_name . " " . $row->last_name;
            $data['f_name'] = $row->f_name;
            $data['father_contact'] = $row->father_contact;
            $data['mother_contact'] = $row->mother_contact;
            $data['emergency_contact'] = $row->emergency_contact;
            $response->data[] = $data;
        }

        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-user');
        $campusid = session('member_campusid');
        $sessionid = session('member_sessionid');

        $data['classesinfo'] = $this->db->table('classes')->get()->getResult();

        $academic_session = $this->db->table('academic_session')->where('session_id', $sessionid)->get()->getRow();
        $sessionName = explode('-', $academic_session->session_name);
        $sessionYear = $sessionName[1] - 1;

        $last_row = $this->db->table('students')->where('session_id', $sessionid)->orderBy('student_id', 'desc')->get()->getResult();
        $last_id = count($last_row) + 1;

        $data['reg_no'] = $sessionYear . '-TSS-' . $last_id;
        $data['sectioninfo'] = $this->db->table('sections')->where('campus_id', $campusid)->get()->getResult();
        $data['academic_sessioninfo'] = $this->db->table('academic_session')->get()->getResult();

        return view('admin/students_edit', $data);
    }

    public function edit()
    {
        check_permission('admin-edit-user');
        $id = (int) $this->request->getGet('id');

        $data['info'] = $this->db->table('students')->where('student_id', $id)->get()->getRow();
        $data['parentsinfo'] = $this->db->table('parents')->where('parent_id', $data['info']->parent_id)->get()->getRow();
        $studentclassinfo = $this->db->table('student_class')->where('student_id', $id)->orderBy('sc_id', 'desc')->get()->getRow();
        $classSectioninfo = $this->db->table('class_section')->where('cls_sec_id', $studentclassinfo->cls_sec_id)->get()->getRow();
        $data['class_info'] = $this->db->table('classes')->where('class_id', $classSectioninfo->class_id)->get()->getRow();
        $data['academic_sessioninfo'] = $this->db->table('academic_session')->get()->getResult();

        return view('admin/leaving_certicate', $data);
    }

    public function save()
    {
        $request = $this->request;

        $name = $request->getPost('name');
        $date_of_birth = DateTime::createFromFormat('Y-m-d', $request->getPost('date_of_birth'))->format('j M Y');
        $religion = $request->getPost('religion');
        $reg_no = $request->getPost('reg_no');
        $gender = $request->getPost('gender');
        $nationality = $request->getPost('nationality');
        $f_name = $request->getPost('f_name');

        $date_of_admission = DateTime::createFromFormat('Y-m-d', $request->getPost('date_of_admission'))->format('j M Y');
        $gr_date = DateTime::createFromFormat('d/m/Y', $request->getPost('gr_date'))->format('Y-m-d');
        $leaving_date = DateTime::createFromFormat('Y-m-d', $request->getPost('leaving_date'))->format('j M Y');

        $birth_date = $request->getPost('date_of_birth');
        [$year, $month, $day] = explode('-', $birth_date);

        $birth_day = $this->numberTowords($day);
        $birth_year = $this->numberTowords($year);
        $monthName = strtoupper(DateTime::createFromFormat("m", $month)->format('F'));
        $date_of_birth_in_words = "$birth_day $monthName $birth_year";

        $data = [
            'gender' => $gender,
            'date_of_admission' => $request->getPost('date_of_admission'),
            'leaving_date' => $request->getPost('leaving_date'),
            'date_of_birth' => $request->getPost('date_of_birth'),
            'caste' => trim($request->getPost('caste')),
            'gr_no' => trim($request->getPost('gr_no')),
            'gr_date' => $gr_date,
            'status' => 3,
        ];

        $data2 = ['status' => 3];
        $id = $request->getPost('id');
        $campus_id = $request->getPost('campus_id');
        $class_passed = $request->getPost('class_passed');
        $remarks = $request->getPost('remarks');

        $campusinfo = $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow();
        $schoolinfo = getSchoolInfo();

        $this->db->transBegin();

        $this->db->table('students')->where('student_id', $id)->update($data);
        $this->db->table('student_class')->where('student_id', $id)->update($data2);

        $this->db->transComplete();

        $html = view('templates/leaving_certificate', [
            'schoolinfo' => $schoolinfo,
            'campusinfo' => $campusinfo,
            'reg_no' => $reg_no,
            'name' => $name,
            'f_name' => $f_name,
            'gender' => $gender,
            'nationality' => $nationality,
            'religion' => $religion,
            'date_of_admission' => $date_of_admission,
            'leaving_date' => $leaving_date,
            'date_of_birth' => $date_of_birth,
            'date_of_birth_in_words' => $date_of_birth_in_words,
            'class_passed' => $class_passed,
            'remarks' => $remarks,
        ]);

        $filename = WRITEPATH . "leaving_certificate/certificate-$reg_no.html";
        file_put_contents($filename, $html);

        return $this->response->setJSON(['success' => true, 'msg' => 'Add User Success']);
    }

    public function download()
    {
        $regno = $this->request->getGet('regno');
        $link = base_url("leaving_certificate/certificate-$regno.html");

        return $this->response->setBody("<a class='btn btn-primary' style='margin: 50px;' href='$link' target='_blank'>Click to Download</a>");
    }

    public function numberTowords($num)
    {
        $ones = [
            0 => "ZERO", 1 => "ONE", 2 => "TWO", 3 => "THREE", 4 => "FOUR",
            5 => "FIVE", 6 => "SIX", 7 => "SEVEN", 8 => "EIGHT", 9 => "NINE",
            10 => "TEN", 11 => "ELEVEN", 12 => "TWELVE", 13 => "THIRTEEN",
            14 => "FOURTEEN", 15 => "FIFTEEN", 16 => "SIXTEEN", 17 => "SEVENTEEN",
            18 => "EIGHTEEN", 19 => "NINETEEN"
        ];
        $tens = [
            0 => "ZERO", 1 => "TEN", 2 => "TWENTY", 3 => "THIRTY", 4 => "FORTY",
            5 => "FIFTY", 6 => "SIXTY", 7 => "SEVENTY", 8 => "EIGHTY", 9 => "NINETY"
        ];
        $hundreds = ["HUNDRED", "THOUSAND", "MILLION", "BILLION", "TRILLION", "QUARDRILLION"];

        $num = number_format($num, 2, ".", ",");
        [$wholenum, $decnum] = explode(".", $num);
        $whole_arr = array_reverse(explode(",", $wholenum));
        krsort($whole_arr, 1);
        $rettxt = "";

        foreach ($whole_arr as $key => $i) {
            $i = ltrim($i, '0');
            if ($i < 20) {
                $rettxt .= $ones[$i];
            } elseif ($i < 100) {
                $rettxt .= $tens[$i[0]] . ' ' . $ones[$i[1]];
            } else {
                $rettxt .= $ones[$i[0]] . ' ' . $hundreds[0] . ' ' . $tens[$i[1]] . ' ' . $ones[$i[2]];
            }
            if ($key > 0) {
                $rettxt .= ' ' . $hundreds[$key] . ' ';
            }
        }

        if ($decnum > 0) {
            $rettxt .= ' and ';
            $rettxt .= ($decnum < 20)
                ? $ones[$decnum]
                : $tens[$decnum[0]] . ' ' . $ones[$decnum[1]];
        }

        return $rettxt;
    }
}
