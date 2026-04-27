<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class StudentFeeReport extends BaseController
{
    protected $db;
    protected $session;
    protected $template_data = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        check_permission('admin-student-fee-report');
    }

    public function index()
    {
        $campusid = $this->session->get('member_campusid');
        $classsectioninfo = $this->db->table('class_section')
            ->where('campus_id', $campusid)
            ->where('status', 1)
            ->get()->getResult();
        $sectionsclassinfo = [];
        foreach ($classsectioninfo as $section) {
            $classinfo = $this->db->table('classes')->where('class_id', $section->class_id)->get()->getRow();
            $sectioninfo = $this->db->table('sections')->where('section_id', $section->section_id)->get()->getRow();
            $sectionsclassinfo[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => $classinfo->class_name . " (" . $sectioninfo->section_name . ")"
            ];
        }
        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

        return view('admin/student_fee_report', $this->template_data);
    }

    // Single Student Full-Year Fee Data (All Sessions)
    public function singleStudentFeedata()
    {
        $data = '';
        $session_id = $this->session->get('member_sessionid');
        $campus_id = $this->session->get('member_campusid');
        $schoolinfo = getSchoolInfo();
        $student_id = $this->request->getPost('student_id');
        $academicSession = $this->db->table('academic_session')
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getResult();

        $headerRep = reportHeader();
        $data .= $headerRep;
        $data .= '<div class="reportHeading">All Sessions Student Fee Report</div>';

        foreach ($academicSession as $sessionValue) {
            $strQuery = 'SELECT * FROM student_class WHERE session_id=' . $sessionValue->session_id . ' AND student_id IN(' . $student_id . ')';
            $data .= '<table class="resultReport table table-bordered">';
            $studentClass = $this->db->query($strQuery)->getResult();

            $start = new \DateTime($sessionValue->start_date);
            $start->modify('first day of next month');
            $end = new \DateTime($sessionValue->end_date);
            $end->modify('first day of next month');
            $interval = \DateInterval::createFromDateString('1 month');
            $period = new \DatePeriod($start, $interval, $end);

            if (!empty($studentClass)) {
                $data .= '<tr><th></th><th style="width: 115px;"></th>';
                foreach ($period as $dt) {
                    $Yearmonths = $dt->format("m/Y");
                    $data .= '<th>' . $Yearmonths . '</th>';
                }
                $data .= '</tr>';

                foreach ($studentClass as $students) {
                    $studentInfo = $this->db->table('students')->where('student_id', $students->student_id)->get()->getRow();
                    $data .= '<tr><th>' . $sessionValue->session_name . '</th>';
                    $data .= '<td>
                                <div style="color:#000;border-bottom:1px solid #000;">Total</div>
                                <div style="color:#000;border-bottom:1px solid #000;">Paid</div>
                                <div style="color:#000;border-bottom:1px solid #000;">Discount</div>
                                <div style="color:#000;">Balance</div>
                              </td>';
                    foreach ($period as $dt) {
                        $Yearmonths = $dt->format("m/Y");
                        $feeInfo = $this->db->query(
                            'SELECT SUM(amount-discount) as total FROM fee_chalan WHERE fee_type_id IN (SELECT fee_type_id FROM fee_type WHERE is_monthly_fee=1) AND student_id=' . $students->student_id . ' AND fee_month="' . $Yearmonths . '"'
                        )->getRow();

                        $paidInfo = $this->db->query(
                            'SELECT SUM(amount-discount) as total FROM fee_chalan WHERE fee_type_id IN (SELECT fee_type_id FROM fee_type WHERE is_monthly_fee=1) AND status="paid" AND student_id=' . $students->student_id . ' AND fee_month="' . $Yearmonths . '"'
                        )->getRow();

                        $unpaidInfo = $this->db->query(
                            'SELECT SUM(amount-discount) as total FROM fee_chalan WHERE fee_type_id IN (SELECT fee_type_id FROM fee_type WHERE is_monthly_fee=1) AND status="unpaid" AND student_id=' . $students->student_id . ' AND fee_month="' . $Yearmonths . '"'
                        )->getRow();

                        $discountInfo = $this->db->query(
                            'SELECT SUM(amount-discount) as total FROM fee_chalan WHERE fee_type_id IN (SELECT fee_type_id FROM fee_type WHERE is_monthly_fee=1) AND status="discounted" AND student_id=' . $students->student_id . ' AND fee_month="' . $Yearmonths . '"'
                        )->getRow();

                        $data .= '<td style="">';
                        $data .= '<div style="color:#000;border-bottom:1px solid #000;">' . ($feeInfo->total ?? '-') . '</div>';
                        $data .= '<div style="color:#000;border-bottom:1px solid #000;">' . ($paidInfo->total ?? '-') . '</div>';
                        $data .= '<div style="color:#000;border-bottom:1px solid #000;">' . ($discountInfo->total ?? '-') . '</div>';
                        if (($unpaidInfo->total ?? 0) != 0) {
                            $data .= '<div style="yellow;color:#000;">' . (($feeInfo->total ?? 0) - ($paidInfo->total ?? 0)) . '</div>';
                        } else {
                            $data .= '<div style="color:#000;">-</div>';
                        }
                        $data .= '</td>';
                    }
                    $data .= '</tr>';
                }
            }
            $data .= '</table>';
        }
        return $this->response->setBody($data);
    }

    // Data2: Fee report by section/class for current session, monthly breakdown
    public function data2()
{
    $data = '';
    $session_id = $this->session->get('member_sessionid');
    $campus_id = $this->session->get('member_campusid');
    $schoolinfo = getSchoolInfo();
    $cls_sec_id = $this->request->getPost('cls_sec_id');

    // Get academic session
    $academic_session = $this->db->table('academic_session')
        ->where('session_id', $session_id)
        ->where('system_id', $schoolinfo->system_id)
        ->get()->getRow();

    // Get all students in section/class
    $students = $this->db->query("
        SELECT s.student_id, s.first_name, s.last_name 
        FROM students s
        JOIN student_class sc ON s.student_id = sc.student_id
        WHERE sc.session_id = ? 
        AND s.status = 1 
        AND s.campus_id = ?
        AND sc.cls_sec_id = ?
    ", [$session_id, $campus_id, $cls_sec_id])->getResult();

    if (empty($students)) {
        $data = 'No students found.';
        return $this->response->setBody($data);
    }

    // Generate all months in session
    $start = new \DateTime($academic_session->start_date);
    $start->modify('first day of this month');
    $end = new \DateTime($academic_session->end_date);
    $end->modify('first day of next month');
    $interval = new \DateInterval('P1M');
    $period = new \DatePeriod($start, $interval, $end);

    $student_ids = array_column($students, 'student_id');
    $months = [];
    foreach ($period as $dt) {
        $months[] = $dt->format('m/Y');
    }

    // Fetch all fee data at once using CI4 query builder
    $builder = $this->db->table('fee_chalan');
    $builder->select("
        student_id,
        fee_month,
        SUM(amount - discount) AS total,
        SUM(CASE WHEN status = 'paid' THEN amount - discount ELSE 0 END) AS paid,
        SUM(CASE WHEN status = 'discounted' THEN amount - discount ELSE 0 END) AS discount,
        SUM(CASE WHEN status = 'unpaid' THEN amount - discount ELSE 0 END) AS balance
    ", false);
    $builder->whereIn('student_id', $student_ids);
    $builder->whereIn('fee_month', $months);
    $builder->where("fee_type_id IN (SELECT fee_type_id FROM fee_type WHERE is_monthly_fee = 1 AND s_flag = 1)", null, false);
    $builder->groupBy(['student_id', 'fee_month']);
    $fee_data = $builder->get()->getResult();

    // Organize results for easy lookup
    $fee_by_student = [];
    foreach ($fee_data as $row) {
        $fee_by_student[$row->student_id][$row->fee_month] = $row;
    }

    // Start table
    $headerRep = reportHeader();
    $data .= $headerRep;
    $data .= '<style> .table td div{border-bottom:1px solid #000 !important;}</style><div class="reportHeading">Students Fee Report</div><table class="resultReport table table-bordered"><tr><th></th><th style="width: 115px;"></th>';

    foreach ($period as $dt) {
        $data .= '<th>' . $dt->format('m/Y') . '</th>';
    }
    $data .= '</tr>';

    // Student-wise rows
    foreach ($students as $student) {
        $data .= '<tr><th>' . esc($student->first_name . ' ' . $student->last_name) . '</th><td>';
        $data .= '<div>Total</div><div>Paid</div><div>Discount</div><div>Balance</div></td>';

        foreach ($period as $dt) {
            $month = $dt->format('m/Y');
            $row = $fee_by_student[$student->student_id][$month] ?? null;
            $data .= '<td>';
            $data .= '<div>' . ($row ? $row->total : '-') . '</div>';
            $data .= '<div>' . ($row ? $row->paid : '-') . '</div>';
            $data .= '<div>' . ($row ? $row->discount : '-') . '</div>';
            $balance = $row ? ($row->total - $row->paid - $row->discount) : '-';
            $data .= '<div>' . ($balance !== '-' && $balance > 0 ? $balance : '-') . '</div>';
            $data .= '</td>';
        }
        $data .= '</tr>';
    }

    // Totals row per month
    $data .= '<tr><td>Total</td><td><div>Total</div><div>Paid</div><div>Discount</div><div>Balance</div></td>';
    foreach ($period as $dt) {
        $month = $dt->format('m/Y');
        $totalBuilder = $this->db->table('fee_chalan');
        $totalBuilder->select("
            SUM(amount - discount) AS total,
            SUM(CASE WHEN status = 'paid' THEN amount - discount ELSE 0 END) AS paid,
            SUM(CASE WHEN status = 'discounted' THEN amount - discount ELSE 0 END) AS discount
        ", false);
        $totalBuilder->whereIn('student_id', $student_ids);
        $totalBuilder->where('fee_month', $month);
        $totalBuilder->where("fee_type_id IN (SELECT fee_type_id FROM fee_type WHERE is_monthly_fee = 1 AND s_flag = 1)", null, false);
        $total_row = $totalBuilder->get()->getRow();

        $total = $total_row->total ?? 0;
        $paid = $total_row->paid ?? 0;
        $discount = $total_row->discount ?? 0;
        $balance = $total - $paid - $discount;

        $data .= '<td>';
        $data .= '<div>' . ($total ?: '-') . '</div>';
        $data .= '<div>' . ($paid ?: '-') . '</div>';
        $data .= '<div>' . ($discount ?: '-') . '</div>';
        $data .= '<div>' . ($balance > 0 ? $balance : '-') . '</div>';
        $data .= '</td>';
    }
    $data .= '</tr></table>';

    return $this->response->setBody($data);
}


    // Get student autocomplete info for select2/etc
    public function get_studentinfo()
    {
        $campusid = $this->session->get('member_campusid');
        $term = $this->request->getPost('term')['term'] ?? '';
        $status = 1;

        $studentsinfo = $this->db->query("SELECT * FROM students WHERE (first_name LIKE '%$term%' OR last_name LIKE '%$term%') AND status=$status AND campus_id=$campusid")->getResultArray();

        $data = [];
        foreach ($studentsinfo as $student) {
            $classstudents = $this->db->query("SELECT * FROM student_class WHERE student_id = " . $student['student_id'])->getRow();
            $parentsInfo = $this->db->query("SELECT f_name FROM parents WHERE parent_id = " . $student['parent_id'])->getRow();
            $stdInfotxt = $student['first_name'] . " " . $student['last_name'] . " c/o " . ($parentsInfo->f_name ?? '');

            if ($classstudents) {
                $data[] = ["id" => $student['student_id'], "text" => $stdInfotxt];
            }
        }

        return $this->response->setJSON($data);
    }

    // Place this *below* the methods I provided in the previous message in the same controller file.

    // Report By Fee Type (non-monthly fees)
    public function report_by_fee_type()
    {
        check_permission('admin-report-by-fee-type');
        $schoolinfo = getSchoolInfo();
        $campusid = $this->session->get('member_campusid');

        // Section/class dropdowns
        $classsectioninfo = $this->db->table('class_section')->where('campus_id', $campusid)->where('status', 1)->get()->getResult();
        $sectionsclassinfo = [];
        foreach ($classsectioninfo as $section) {
            $classinfo = $this->db->table('classes')->where('class_id', $section->class_id)->get()->getRow();
            $sectioninfo = $this->db->table('sections')->where('section_id', $section->section_id)->get()->getRow();
            $sectionsclassinfo[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => $classinfo->class_name . " (" . $sectioninfo->section_name . ")"
            ];
        }
        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;
        $this->template_data['termsinfo'] = $this->db->table('terms')->get()->getResult();
        $this->template_data['academic_session'] = $this->db->table('academic_session')->where('system_id', $schoolinfo->system_id)->get()->getResult();

        return view('admin/student_report_by_fee_type', $this->template_data);
    }

    // Report By Fee Student (single student, all fee types)
    public function report_by_fee_student()
    {
        check_permission('admin-report-by-student-fee');
        $schoolinfo = getSchoolInfo();
        $campusid = $this->session->get('member_campusid');

        $classsectioninfo = $this->db->table('class_section')->where('campus_id', $campusid)->where('status', 1)->get()->getResult();
        $sectionsclassinfo = [];
        foreach ($classsectioninfo as $section) {
            $classinfo = $this->db->table('classes')->where('class_id', $section->class_id)->get()->getRow();
            $sectioninfo = $this->db->table('sections')->where('section_id', $section->section_id)->get()->getRow();
            $sectionsclassinfo[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => $classinfo->class_name . " (" . $sectioninfo->section_name . ")"
            ];
        }
        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;
        $this->template_data['termsinfo'] = $this->db->table('terms')->get()->getResult();
        $this->template_data['academic_session'] = $this->db->table('academic_session')->where('system_id', $schoolinfo->system_id)->get()->getResult();

        return view('admin/single_student_fee_report', $this->template_data);
    }

    // Edit (for session/term update)
    public function edit()
    {
        check_permission('admin-student-fee-report');
        $id = intval($this->request->getGet('id'));
        $info = $this->db->table('terms_session')->where('term_session_id', $id)->get()->getRow();
        $this->template_data['info'] = $info;
        $this->template_data['termsinfo'] = $this->db->table('terms')->get()->getResult();
        $this->template_data['academic_session'] = $this->db->table('academic_session')->get()->getResult();

        return view('admin/student_fee_report', $this->template_data);
    }



}
