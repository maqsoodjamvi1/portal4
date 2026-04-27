<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class StudentsDefaultersList extends BaseController
{
    private $column_order = [null, 'first_name', 'last_name', 'father_contact', 'address_line1', 'city'];
    private $column_search = ['student_id', 'parent_id', 'status'];
    private $order = ['student_id' => 'asc'];
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url', 'text']);
        $this->db = \Config\Database::connect();
        $this->session = session();
        check_permission('admin-students-contact-list');
    }

    public function index()
    {
        $sessionid = $this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();
        $currentrole = currentUserRoles();

        $sessionInfo = $this->db->table('academic_session')->where('session_id', $sessionid)->get()->getRow();
        $dateArr = explode('-', $sessionInfo->start_date);
        $session_year = $dateArr[0];

        $months = $this->nb_mois2($sessionInfo->start_date, $sessionInfo->end_date);

        $data['months'] = $months;

        if (in_array(5, $currentrole)) {
            $sectionsclassinfo = teacherSubjectSections();
        } else {
            $sectionsclassinfo = userClassSections();
        }

        $data['sectionsclassinfo'] = $sectionsclassinfo;

        $fee_types = $this->db->table('fee_type')->where('system_id', $schoolinfo->system_id)->get()->getResult();
        $data['fee_types'] = $fee_types;

        return view('admin/students_defaulters_list', $data);
    }

    public function nb_mois2($date1, $date2)
    {
        $begin = new \DateTime($date1);
        $end = new \DateTime($date2);
        $end = $end->modify('+1 month');
        $interval = \DateInterval::createFromDateString('1 month');
        $period = new \DatePeriod($begin, $interval, $end);

        $monthList = [];
        foreach ($period as $dt) {
            $monthList[] = [
                'id' => $dt->format("m/Y"),
                'value' => $dt->format("M/Y"),
            ];
        }
        return $monthList;
    }

    // Modern data() with CI4 conventions
    public function data()
    {
        $parser = \Config\Services::parser();

        $campusid = (int)$this->session->get('member_campusid');
        $sessionid = (int)$this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();

        $builder = $this->db->table('students')
            ->select('students.*, parents.f_name, parents.parent_id, parents.father_contact, parents.mother_contact, parents.emergency_contact, parents.whatsapp, parents.address_line1, classes.class_name, sections.section_name')
            ->join('parents', 'parents.parent_id = students.parent_id', 'left')
            ->join('student_class', 'student_class.student_id = students.student_id AND student_class.session_id = ' . $sessionid, 'left')
            ->join('class_section', 'class_section.cls_sec_id = student_class.cls_sec_id', 'left')
            ->join('classes', 'classes.class_id = class_section.class_id', 'left')
            ->join('sections', 'sections.section_id = class_section.section_id', 'left')
            ->where('students.campus_id', $campusid)
            ->where('student_class.status', 1)
            ->where('students.status', 1);

        // Add filters
        $this->apply_filters($builder);

        // Handle search
        $searchValue = $this->request->getPost('search')['value'] ?? '';
        if ($searchValue) {
            $builder->groupStart();
            foreach ($this->column_search as $item) {
                $builder->orLike($item, $searchValue);
            }
            $builder->groupEnd();
        }

        // Handle ordering
        if ($order = $this->request->getPost('order')) {
            $orderCol = $this->column_order[$order[0]['column']];
            $orderDir = $order[0]['dir'];
            if ($orderCol) {
                $builder->orderBy($orderCol, $orderDir);
            }
        } else {
            $builder->orderBy(key($this->order), $this->order[key($this->order)]);
        }

        // Pagination
        $length = (int)$this->request->getPost('length');
        $start = (int)$this->request->getPost('start');
        if ($length != -1) {
            $builder->limit($length, $start);
        }

        $list = $builder->get()->getResult();

        // Get fee information
        $feeData = $this->get_fee_data();

        $response = $this->build_response($list, $feeData, $schoolinfo, $parser);

        $output = [
            "draw" => (int)$this->request->getPost('draw'),
            "recordsTotal" => $this->count_all(),
            "recordsFiltered" => $this->count_filtered(),
            "data" => $response,
        ];

        return $this->response->setJSON($output);
    }

    private function apply_filters(&$builder)
    {
        $status = $this->request->getPost('status');
        if ($status) {
            $builder->where('students.status', (int)$status);
        }
        $student_id = $this->request->getPost('student_id');
        if ($student_id) {
            $builder->where('students.student_id', (int)$student_id);
        }
        $parent_id = $this->request->getPost('parent_id');
        if ($parent_id) {
            $builder->where('students.parent_id', (int)$parent_id);
        }
        $cls_sec_id = $this->request->getPost('cls_sec_id');
        if ($cls_sec_id && $cls_sec_id !== 'all') {
            $builder->where('student_class.cls_sec_id', (int)$cls_sec_id);
        }
    }

    private function get_fee_data()
    {
        $feeMonth = $this->request->getPost('month') ?: date('m/Y');
        $fee_type_id = $this->request->getPost('fee_type') ?: '';

        $feeBuilder = $this->db->table('fee_chalan')
            ->select('student_id, SUM(amount-discount) AS total_amount, SUM(discount) AS total_discount,
                SUM(CASE WHEN fee_month = "' . $feeMonth . '" THEN (amount - discount) ELSE 0 END) AS current_month_unpaid')
            ->where('status', 'UnPaid');

        if ($fee_type_id) {
            $feeBuilder->where('fee_type_id', $fee_type_id);
        }

        return $feeBuilder->groupBy('student_id')->get()->getResultArray();
    }

    private function build_response($list, $feeData, $schoolinfo, $parser)
    {
        $feeLookup = [];
        foreach ($feeData as $row) {
            $feeLookup[$row['student_id']] = $row;
        }
        $response = [];
        $no = (int)($this->request->getPost('start') ?? 0);

        foreach ($list as $row) {
            $fee = $feeLookup[$row->student_id] ?? null;
            if (!$fee || !$fee['current_month_unpaid']) continue;

            $no++;
            $response[] = $this->build_row($row, $fee, $schoolinfo, $parser, $no);
        }
        return $response;
    }

    private function build_row($row, $fee, $schoolinfo, $parser, $no)
    {
        $smsData = [
            'first_name' => $row->first_name,
            'last_name' => $row->last_name,
            'father_name' => $row->f_name,
            'class' => $row->class_name . "(" . $row->section_name . ")",
            'balance' => $fee['total_amount'],
            'date' => date('Y-m-d')
        ];

        return [
            'id' => $no,
            'profile_photo' => $this->get_profile_image($row),
            'parent_id' => $row->parent_id,
            'f_name' => $row->f_name,
            'class' => $row->class_name . "(" . $row->section_name . ")",
            'section' => $row->section_name,
            'f_contacts' => $this->build_contact_link($row->father_contact, $smsData, $schoolinfo, $parser),
            'm_contacts' => $row->mother_contact,
            'e_contacts' => $row->emergency_contact,
            'w_contacts' => $row->whatsapp,
            'monthly_unpaid' => (float)$fee['current_month_unpaid'],
            'previous_balance' => (float)($fee['total_amount'] - $fee['current_month_unpaid']),
            'payable' => (float)$fee['total_amount'],
            'columnName' => $this->request->getPost('month') ?: date('m/Y'),
            'name' => $row->first_name . " " . $row->last_name,
            'address' => $row->address_line1,
        ];
    }

    private function get_profile_image($row)
    {
        if (!empty($row->profile_photo) && file_exists(FCPATH . "uploads/" . $row->profile_photo)) {
            return '<img src="' . base_url("uploads/" . $row->profile_photo) . '" style="width:50px;height:50px;border-radius:30px;margin:0 auto;">';
        }
        return '<i class="fa fa-user" style="font-size:40px;text-align:center;display:block;"></i>';
    }

    private function build_contact_link($contact, $smsData, $schoolinfo, $parser)
    {
        $campusid = (int)$this->session->get('member_campusid');
        $campusinfo = $this->db->table('campus')->where('campus_id', $campusid)->get()->getRow();
        $message = $parser->setData($smsData)->renderString($campusinfo->student_fee_sms ?? '');
        return '<a href="https://wa.me/' . $contact . '?text=' . urlencode($message) . '">' . $contact . '</a>';
    }

    private function count_all()
    {
        return $this->db->table('students')
            ->where('campus_id', (int)$this->session->get('member_campusid'))
            ->countAllResults();
    }

    private function count_filtered()
    {
        $campusid = (int)$this->session->get('member_campusid');
        $sessionid = (int)$this->session->get('member_sessionid');

        $builder = $this->db->table('students')
            ->select('COUNT(DISTINCT students.student_id) as total')
            ->join('student_class', 'student_class.student_id = students.student_id AND student_class.session_id = ' . $sessionid, 'left')
            ->join('parents', 'parents.parent_id = students.parent_id', 'left')
            ->where('students.campus_id', $campusid)
            ->where('student_class.status', 1)
            ->where('students.status', 1);

        $this->apply_filters($builder);

        $searchValue = $this->request->getPost('search')['value'] ?? '';
        if ($searchValue) {
            $builder->groupStart();
            foreach ($this->column_search as $item) {
                $builder->orLike($item, $searchValue);
            }
            $builder->groupEnd();
        }

        $row = $builder->get()->getRow();
        return $row ? $row->total : 0;
    }
}
