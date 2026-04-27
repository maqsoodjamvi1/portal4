<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class FamilyFeeReport extends BaseController
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
        $sectionsclassinfo = [];

        $classsectioninfo = $this->db->table('class_section')->where('campus_id', $campusid)->get()->getResult();
        foreach ($classsectioninfo as $section) {
            $classinfo = $this->db->table('classes')->where('class_id', $section->class_id)->get()->getRow();
            $sectioninfo = $this->db->table('sections')->where('section_id', $section->section_id)->get()->getRow();
            $sectionsclassinfo[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => $classinfo->class_name . " (" . $sectioninfo->section_name . ")"
            ];
        }

        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

        return view('admin/family_fee_report', $this->template_data);
    }

    public function singleStudentFeedata()
    {
        $data = '';
        $student_id = $this->request->getPost('student_id');
        $schoolinfo = getSchoolInfo();

        $studentInfo = $this->db->table('students')->where('student_id', $student_id)->get()->getRow();
        $familyParentId = $studentInfo->parent_id;

        $academicSessions = $this->db->table('academic_session')->where('system_id', $schoolinfo->system_id)->get()->getResult();

        $data .= reportHeader() . '<div class="reportHeading">Family Fee Report</div>';

        foreach ($academicSessions as $session) {
            $siblings = $this->db->query("
                SELECT s.student_id, s.first_name, s.last_name, c.class_name
                FROM students s
                JOIN student_class sc ON s.student_id = sc.student_id
                JOIN class_section cs ON sc.cls_sec_id = cs.cls_sec_id
                JOIN classes c ON cs.class_id = c.class_id
                WHERE sc.session_id = ? AND s.parent_id = ?
            ", [$session->session_id, $familyParentId])->getResult();

            if (empty($siblings)) continue;

            $start = new \DateTime($session->start_date);
            $start->modify('first day of next month');
            $end = new \DateTime($session->end_date);
            $end->modify('first day of next month');
            $interval = \DateInterval::createFromDateString('1 month');
            $period = new \DatePeriod($start, $interval, $end);

            $studentIds = array_column($siblings, 'student_id');
            $monthYears = [];
            foreach ($period as $dt) {
                $monthYears[] = $dt->format("m/Y");
            }

            // Format placeholders for IN queries
            $studentIdsPlaceholders = implode(',', array_fill(0, count($studentIds), '?'));
            $monthYearsPlaceholders = implode(',', array_fill(0, count($monthYears), '?'));

            $params = array_merge($studentIds, $monthYears);

            $feeData = $this->db->query("
                SELECT 
                    student_id,
                    fee_month,
                    SUM(amount - discount) AS total,
                    SUM(CASE WHEN status = 'paid' THEN amount - discount ELSE 0 END) AS paid,
                    SUM(CASE WHEN status = 'discounted' THEN amount - discount ELSE 0 END) AS discount
                FROM fee_chalan
                WHERE fee_type_id IN (SELECT fee_type_id FROM fee_type WHERE is_monthly_fee = 1)
                AND student_id IN ($studentIdsPlaceholders)
                AND fee_month IN ($monthYearsPlaceholders)
                GROUP BY student_id, fee_month
            ", $params)->getResult();

            $feeMap = [];
            foreach ($feeData as $row) {
                $feeMap[$row->student_id][$row->fee_month] = [
                    'total'   => $row->total,
                    'paid'    => $row->paid,
                    'discount'=> $row->discount,
                    'balance' => $row->total - $row->paid
                ];
            }

            $data .= "<table class='resultReport table table-bordered'>";
            $data .= "<tr><th></th><th style='width:115px;'></th>";
            foreach ($period as $dt) {
                $data .= "<th>" . $dt->format("m/Y") . "</th>";
            }
            $data .= "</tr>";

            // Track family totals
            $familyTotals = [];
            foreach ($monthYears as $monthYear) {
                $familyTotals[$monthYear] = [
                    'total' => 0,
                    'paid' => 0,
                    'discount' => 0,
                    'balance' => 0
                ];
            }

            foreach ($siblings as $sibling) {
                $data .= "<tr><th>{$session->session_name}<br>{$sibling->first_name} {$sibling->last_name}<br>{$sibling->class_name}</th>";
                $data .= "<td>
                    <div class='border-bottom'>Total</div>
                    <div class='border-bottom'>Paid</div>
                    <div class='border-bottom'>Discount</div>
                    <div>Balance</div>
                </td>";

                foreach ($period as $dt) {
                    $monthYear = $dt->format("m/Y");
                    $fee = $feeMap[$sibling->student_id][$monthYear] ?? null;

                    if ($fee) {
                        $familyTotals[$monthYear]['total'] += $fee['total'];
                        $familyTotals[$monthYear]['paid'] += $fee['paid'];
                        $familyTotals[$monthYear]['discount'] += $fee['discount'];
                        $familyTotals[$monthYear]['balance'] += $fee['balance'];
                    }

                    $data .= "<td>";
                    $data .= $this->formatCell($fee['total'] ?? 0);
                    $data .= $this->formatCell($fee['paid'] ?? 0);
                    $data .= $this->formatCell($fee['discount'] ?? 0);
                    $data .= $this->formatCell(($fee['balance'] ?? 0) ?: '-');
                    $data .= "</td>";
                }
                $data .= "</tr>";
            }

            // Add family totals row
            $data .= "<tr><th>{$session->session_name} Family Total</th><td>
                <div class='border-bottom'>Total</div>
                <div class='border-bottom'>Paid</div>
                <div class='border-bottom'>Discount</div>
                <div>Balance</div>
            </td>";
            foreach ($period as $dt) {
                $monthYear = $dt->format("m/Y");
                $total = $familyTotals[$monthYear];
                $data .= "<td>";
                $data .= $this->formatCell($total['total']);
                $data .= $this->formatCell($total['paid']);
                $data .= $this->formatCell($total['discount']);
                $data .= $this->formatCell($total['balance']);
                $data .= "</td>";
            }
            $data .= "</tr></table>";
        }

        return $this->response->setBody($data);
    }

    private function formatCell($value)
    {
        return "<div>" . ($value ? ($value) : '-') . "</div>";
    }

    public function data2()
    {
        $data = '';
        $session_id = $this->session->get('member_sessionid');
        $campus_id = $this->session->get('member_campusid');
        $schoolinfo = getSchoolInfo();
        $cls_sec_id = $this->request->getPost('cls_sec_id');

        $academic_session = $this->db->table('academic_session')
            ->where('session_id', $session_id)
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getRow();

        $studentClass = $this->db->query(
            'SELECT * FROM student_class WHERE session_id=? AND student_id IN (SELECT student_id FROM students WHERE status=1 AND campus_id=?) AND cls_sec_id=?',
            [$session_id, $campus_id, $cls_sec_id]
        )->getResult();

        $start = new \DateTime($academic_session->start_date);
        $start->modify('first day of next month');
        $end = new \DateTime($academic_session->end_date);
        $end->modify('first day of next month');
        $interval = \DateInterval::createFromDateString('1 month');
        $period = new \DatePeriod($start, $interval, $end);

        $data .= '<div class="reportHeading">Family Fee Report</div><table class="resultReport table table-bordered"><tr><th></th><th style="width: 115px;"></th>';
        foreach ($period as $dt) {
            $Yearmonths = $dt->format("m/Y");
            $data .= '<th>' . $Yearmonths . '</th>';
        }
        $data .= '</tr>';

        foreach ($studentClass as $students) {
            $studentInfo = $this->db->table('students')->where('student_id', $students->student_id)->get()->getRow();

            $data .= '<tr><th>' . $studentInfo->first_name . ' ' . $studentInfo->last_name . '</th>';
            $data .= '<td>';
            $data .= '<div style="color:#000;border-bottom:1px solid #000;">Total</div>';
            $data .= '<div style="color:#000;border-bottom:1px solid #000;">Paid</div>';
            $data .= '<div style="color:#000;border-bottom:1px solid #000;">Discount</div>';
            $data .= '<div style="color:#000;">Balance</div>';
            $data .= '</td>';

            foreach ($period as $dt) {
                $Yearmonths = $dt->format("m/Y");

                $feeInfo = $this->db->query(
                    'SELECT SUM(amount-discount) as total from fee_chalan WHERE fee_type_id IN(SELECT fee_type_id FROM fee_type WHERE is_monthly_fee=1) AND student_id=? AND fee_month=?',
                    [$students->student_id, $Yearmonths]
                )->getRow();

                $paidInfo = $this->db->query(
                    'SELECT SUM(amount-discount) as total from fee_chalan where fee_type_id IN(select fee_type_id from fee_type where is_monthly_fee=1) AND status="paid" AND student_id=? AND fee_month=?',
                    [$students->student_id, $Yearmonths]
                )->getRow();

                $unpaidInfo = $this->db->query(
                    'SELECT SUM(amount-discount) as total from fee_chalan where fee_type_id IN(select fee_type_id from fee_type where is_monthly_fee=1) AND status="unpaid" AND student_id=? AND fee_month=?',
                    [$students->student_id, $Yearmonths]
                )->getRow();

                $discountInfo = $this->db->query(
                    'SELECT SUM(amount-discount) as total from fee_chalan where fee_type_id IN(select fee_type_id from fee_type where is_monthly_fee=1) AND status="discounted" AND student_id=? AND fee_month=?',
                    [$students->student_id, $Yearmonths]
                )->getRow();

                $data .= '<td style="">';
                $data .= '<div style="color:#000;border-bottom:1px solid #000;">' . ($feeInfo->total ?? '-') . '</div>';
                $data .= '<div style="color:#000;border-bottom:1px solid #000;">' . ($paidInfo->total ?? '-') . '</div>';
                $data .= '<div style="color:#000;border-bottom:1px solid #000;">' . ($discountInfo->total ?? '-') . '</div>';
                $data .= '<div style="color:#000;">' . (($feeInfo->total ?? 0) - ($paidInfo->total ?? 0)) . '</div>';
                $data .= '</td>';
            }
            $data .= '</tr>';
        }
        $data .= '</table>';

        return $this->response->setBody($data);
    }

    public function data()
    {
        $data = '';
        $session_id = $this->session->get('member_sessionid');
        $campus_id = $this->session->get('member_campusid');
        $schoolinfo = getSchoolInfo();
        $cls_sec_id = $this->request->getPost('cls_sec_id');

        $academic_session = $this->db->table('academic_session')
            ->where('session_id', $session_id)
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getRow();

        $feeTypesInfo = $this->db->query('SELECT * FROM fee_type WHERE is_monthly_fee != 1 AND fee_type_id !=0 AND system_id=' . $schoolinfo->system_id)->getResult();

        $studentClass = $this->db->query('SELECT * FROM student_class WHERE session_id=' . $session_id . ' AND student_id IN( SELECT student_id FROM students WHERE status=1 AND campus_id=' . $campus_id . ') AND cls_sec_id=' . $cls_sec_id)->getResult();

        $start = new \DateTime($academic_session->start_date);
        $start->modify('first day of this month');
        $end = new \DateTime($academic_session->end_date);
        $end->modify('first day of next month');
        $interval = \DateInterval::createFromDateString('1 month');
        $period = new \DatePeriod($start, $interval, $end);

        $Yearmonths = [];
        foreach ($period as $dt) {
            $Yearmonths[] = $dt->format("m/Y");
        }

        $monthsStr = "'" . implode("','", $Yearmonths) . "'";

        $data .= '<table class="table"><tr><th style="width: 115px;"></th><th style="width: 115px;"></th>';
        foreach ($feeTypesInfo as $feeType) {
            $data .= '<th>' . $feeType->fee_type_name . '</th>';
        }
        $data .= '</tr>';

        foreach ($studentClass as $students) {
            $studentInfo = $this->db->table('students')->where('student_id', $students->student_id)->get()->getRow();

            $data .= '<tr><th>' . $studentInfo->first_name . ' ' . $studentInfo->last_name . '</th>';
            $data .= '<td>';
            $data .= '<div style="color:#000;border-bottom:1px solid #000;">Total</div>';
            $data .= '<div style="color:#000;border-bottom:1px solid #000;">Paid</div>';
            $data .= '<div style="color:#000;border-bottom:1px solid #000;">Discount</div>';
            $data .= '<div style="color:#000;">Balance</div>';
            $data .= '</td>';

            foreach ($feeTypesInfo as $feeType) {
                $feeInfo = $this->db->query('SELECT SUM(amount-discount) as total from fee_chalan where fee_month IN(' . $monthsStr . ') AND student_id=' . $students->student_id . ' AND fee_type_id=' . $feeType->fee_type_id)->getRow();

                $paidInfo = $this->db->query('SELECT SUM(amount-discount) as total from fee_chalan where fee_month IN(' . $monthsStr . ') AND status="paid" AND student_id=' . $students->student_id . ' AND fee_type_id=' . $feeType->fee_type_id)->getRow();

                $unpaidInfo = $this->db->query('SELECT SUM(amount-discount) as total from fee_chalan where fee_month IN(' . $monthsStr . ') AND status="unpaid" AND student_id=' . $students->student_id . ' AND fee_type_id=' . $feeType->fee_type_id)->getRow();

                $discountInfo = $this->db->query('SELECT SUM(amount-discount) as total from fee_chalan where fee_month IN(' . $monthsStr . ') AND status="discounted" AND student_id=' . $students->student_id . ' AND fee_type_id=' . $feeType->fee_type_id)->getRow();

                $data .= '<td>';
                $data .= '<div style="color:#000;border-bottom:1px solid #000;">' . ($feeInfo->total ?? '0') . '/- </div>';
                $data .= '<div style="color:#000;border-bottom:1px solid #000;">' . ($paidInfo->total ?? '0') . '/- </div>';
                $data .= '<div style="color:#000;border-bottom:1px solid #000;">' . ($discountInfo->total ?? '0') . '/-</div>';
                $data .= '<div style="color:#000;">' . (($feeInfo->total ?? 0) - ($paidInfo->total ?? 0)) . '/- </div>';
                $data .= '</td>';
            }
            $data .= '</tr>';
        }
        $data .= '</table>';

        return $this->response->setBody($data);
    }

    public function report_by_fee_type()
    {
        check_permission('admin-add-terms-session');
        $schoolinfo = getSchoolInfo();
        $campusid = $this->session->get('member_campusid');

        $sectionsclassinfo = [];
        $classsectioninfo = $this->db->table('class_section')->where('campus_id', $campusid)->get()->getResult();
        foreach ($classsectioninfo as $section) {
            $classinfo = $this->db->table('classes')->where('class_id', $section->class_id)->get()->getRow();
            $sectioninfo = $this->db->table('sections')->where('section_id', $section->section_id)->get()->getRow();
            $sectionsclassinfo[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => $classinfo->class_name . " (" . $sectioninfo->section_name . ")"
            ];
        }
        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

        $termsinfo = $this->db->table('terms')->get()->getResult();
        $this->template_data['termsinfo'] = $termsinfo;

        $academic_session = $this->db->table('academic_session')->where('system_id', $schoolinfo->system_id)->get()->getResult();
        $this->template_data['academic_session'] = $academic_session;

        return view('admin/student_report_by_fee_type', $this->template_data);
    }

    public function report_by_fee_student()
    {
        check_permission('admin-add-terms-session');
        $schoolinfo = getSchoolInfo();
        $campusid = $this->session->get('member_campusid');

        $sectionsclassinfo = [];
        $classsectioninfo = $this->db->table('class_section')->where('campus_id', $campusid)->get()->getResult();
        foreach ($classsectioninfo as $section) {
            $classinfo = $this->db->table('classes')->where('class_id', $section->class_id)->get()->getRow();
            $sectioninfo = $this->db->table('sections')->where('section_id', $section->section_id)->get()->getRow();
            $sectionsclassinfo[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => $classinfo->class_name . " (" . $sectioninfo->section_name . ")"
            ];
        }
        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

        $termsinfo = $this->db->table('terms')->get()->getResult();
        $this->template_data['termsinfo'] = $termsinfo;

        $academic_session = $this->db->table('academic_session')->where('system_id', $schoolinfo->system_id)->get()->getResult();
        $this->template_data['academic_session'] = $academic_session;

        return view('admin/family_fee_report', $this->template_data);
    }

    public function edit()
    {
        check_permission('admin-student-fee-report');
        $id = intval($this->request->getGet('id'));
        $info = $this->db->table('terms_session')->where('term_session_id', $id)->get()->getRow();

        $termsinfo = $this->db->table('terms')->get()->getResult();
        $this->template_data['termsinfo'] = $termsinfo;

        $academic_session = $this->db->table('academic_session')->get()->getResult();
        $this->template_data['academic_session'] = $academic_session;

        $this->template_data['info'] = $info;
        return view('admin/student_fee_report', $this->template_data);
    }

    public function get_studentinfo()
    {
        $campusid = $this->session->get('member_campusid');
        $term = $this->request->getPost('term')['term'] ?? '';
        $status = 1;

        $studentsinfo = $this->db->query(
            "SELECT * FROM students WHERE (first_name LIKE ? OR last_name LIKE ?) AND status=? AND campus_id=?",
            ['%' . $term . '%', '%' . $term . '%', $status, $campusid]
        )->getResultArray();

        $data = [];
        foreach ($studentsinfo as $student) {
            $classstudents = $this->db->table('student_class')->where('student_id', $student['student_id'])->get()->getRow();
            $parentsInfo = $this->db->table('parents')->select('f_name')->where('parent_id', $student['parent_id'])->get()->getRow();
            $stdInfotxt = $student['first_name'] . " " . $student['last_name'] . " c/o " . ($parentsInfo->f_name ?? '');

            if ($classstudents) {
                $data[] = ["id" => $student['student_id'], "text" => $stdInfotxt];
            }
        }

        return $this->response->setJSON($data);
    }
}
// end this file
