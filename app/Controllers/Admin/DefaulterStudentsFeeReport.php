<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DefaulterStudentsFeeReport extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url', 'text']);
        $this->db = \Config\Database::connect();
        $this->session = session();
        check_permission('admin-defaulter-student-fee-report');
    }

    public function index()
    {
        $schoolinfo = getSchoolInfo();
        $campusid = $this->session->get('member_campusid');

        $classsectioninfo = $this->db->table('class_section')
            ->where('campus_id', $campusid)
            ->where('status', 1)
            ->get()
            ->getResult();

        $sectionsclassinfo = [];
        foreach ($classsectioninfo as $section) {
            $classinfo = $this->db->table('classes')->where('class_id', $section->class_id)->get()->getRow();
            $sectioninfo = $this->db->table('sections')->where('section_id', $section->section_id)->get()->getRow();
            $sectionsclassinfo[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => $classinfo->class_name . " (" . $sectioninfo->section_name . ")"
            ];
        }
        $data['sectionsclassinfo'] = $sectionsclassinfo;

        $termsinfo = $this->db->table('terms')->get()->getResult();
        $data['termsinfo'] = $termsinfo;

        $academic_session = $this->db->table('academic_session')
            ->where('system_id', $schoolinfo->system_id)
            ->get()
            ->getResult();
        $data['academic_session'] = $academic_session;

        return view('admin/defaulter_students_fee_report', $data);
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
            ->get()
            ->getRow();

if (!$academic_session) {
    return $this->response->setBody('<div class="alert alert-danger">Academic session not found for session_id='.$session_id.'</div>');
}
        $feeTypesInfo = $this->db->table('fee_type')
            ->where('s_flag', 1)
            ->where('status', 1)
            ->where('system_id', $schoolinfo->system_id)
            ->get()
            ->getResult();

        if ($cls_sec_id == 'all') {
            $studentClass = $this->db->query(
                'SELECT * FROM student_class WHERE session_id=? AND student_id IN (SELECT student_id FROM students WHERE status=1 AND campus_id=?) ORDER BY cls_sec_id',
                [$session_id, $campus_id]
            )->getResult();
        } else {
            $studentClass = $this->db->query(
                'SELECT * FROM student_class WHERE session_id=? AND student_id IN (SELECT student_id FROM students WHERE status=1 AND campus_id=?) AND cls_sec_id=? ORDER BY cls_sec_id',
                [$session_id, $campus_id, $cls_sec_id]
            )->getResult();
        }

        $start = new \DateTime($academic_session->start_date);
        $start->modify('first day of this month');
        $end = new \DateTime($academic_session->end_date);
        $end->modify('first day of next month');
        $interval = \DateInterval::createFromDateString('1 month');
        $period = new \DatePeriod($start, $interval, $end);


        $Yearmonths = '';
        foreach ($period as $dt) {
            $Yearmonths .= "'" . $dt->format("m/Y") . "',";
        }

$Yearmonths = rtrim($Yearmonths, ',');
if ($Yearmonths === '') {
    return $this->response->setBody('<div class="alert alert-warning">Month range is empty. Check start/end dates.</div>');
}
        $headerRep = reportHeader();
        $data .= $headerRep;
        $data .= '<div class="reportHeading">Fee Report By</div><table class="resultReport table table-bordered"><tr><th style="width: 200px;"></th><th style="width: 115px;">Due Month</th>';

        foreach ($feeTypesInfo as $feeType) {
            $data .= '<th>' . $feeType->fee_type_name . '</th>';
        }
        $data .= '<th>Total</th>';
        $data .= '</tr>';
        $studentTotal = 0;
        $nCount = 1;

        foreach ($studentClass as $students) {
            $class_info = getClassSection($students->cls_sec_id);

            $studentInfo = $this->db->table('students')->where('student_id', $students->student_id)->get()->getRow();
            $parentInfo = $this->db->table('parents')->where('parent_id', $studentInfo->parent_id)->get()->getRow();

            $unpaidTotalInfo = $this->db->query(
                'SELECT SUM(amount-discount) as total FROM fee_chalan WHERE fee_month IN(' . rtrim($Yearmonths, ',') . ') AND status="unpaid" AND student_id=?',
                [$students->student_id]
            )->getRow();

            if ($unpaidTotalInfo && $unpaidTotalInfo->total > 0) {
                $data .= '<tr><th style="text-align:left;padding:0 4px;">' . $nCount . '. ' . $studentInfo->first_name . ' ' . $studentInfo->last_name . ' C/O <br>' . $parentInfo->f_name . ' ' . $class_info['sectionclassname'] . '</th>';
                $data .= '<td>';

                $unpaidMonthInfo = $this->db->query(
                    'SELECT fee_month,SUM(amount-discount) as total FROM fee_chalan WHERE fee_month IN(' . rtrim($Yearmonths, ',') . ') AND status="unpaid" AND student_id=? GROUP BY fee_month,amount',
                    [$students->student_id]
                )->getResult();

                $unpaidFeeMonth = [];
                $totalAmount = 0;
                foreach ($unpaidMonthInfo as $unpaidMonthValue) {
                    if ($unpaidMonthValue->total > 0) {
                        $feeMonthArr = explode('/', $unpaidMonthValue->fee_month);
                        $dateObj = \DateTime::createFromFormat('!m', $feeMonthArr[0]);
                        $monthName = $dateObj->format('F');
                        $unpaidFeeMonth[] = $monthName;
                        $totalAmount += $unpaidMonthValue->total;
                    }
                }
                $feeMonths = array_unique($unpaidFeeMonth);

                $data .= '<div style="color:#000;">' . implode(", ", $feeMonths) . '</div>';
                $data .= '</td>';

                foreach ($feeTypesInfo as $feeType) {
                    $unpaidInfo = $this->db->query(
                        'SELECT SUM(amount-discount) as total FROM fee_chalan WHERE fee_month IN(' . rtrim($Yearmonths, ',') . ') AND status="unpaid" AND student_id=? AND fee_type_id=?',
                        [$students->student_id, $feeType->fee_type_id]
                    )->getRow();

                    $data .= '<td>';
                    if ($unpaidInfo->total != 0) {
                        $data .= '<div style="color:#000;">' . ($unpaidInfo->total) . '/- </div>';
                    } else {
                        $data .= '<div style="color:#000;">0/- </div>';
                    }
                    $data .= '</td>';
                }
                $data .= '<td>' . $totalAmount . '/-</td>';
                $data .= '</tr>';
                $studentTotal += $totalAmount;
                $nCount++;
            }
        }

        $data .= '<tr><td colspan=""></td><th colspan="">Total</th>';
        foreach ($feeTypesInfo as $feeType) {
            if ($cls_sec_id == 'all') {
                $unpaidtotalInfo = $this->db->query(
                    'SELECT SUM(amount-discount) as total FROM fee_chalan WHERE fee_month IN(' . rtrim($Yearmonths, ',') . ') AND status="unpaid" AND fee_type_id=? AND student_id IN(select student_id from student_class where session_id=? AND student_id IN(select student_id from students where status=1 AND campus_id=?))',
                    [$feeType->fee_type_id, $session_id, $campus_id]
                )->getRow();
            } else {
                $unpaidtotalInfo = $this->db->query(
                    'SELECT SUM(amount-discount) as total FROM fee_chalan WHERE fee_month IN(' . rtrim($Yearmonths, ',') . ') AND status="unpaid" AND fee_type_id=? AND student_id IN(select student_id from student_class where session_id=? AND student_id IN(select student_id from students where status=1 AND campus_id=?) AND cls_sec_id=?)',
                    [$feeType->fee_type_id, $session_id, $campus_id, $cls_sec_id]
                )->getRow();
            }
            if ($unpaidtotalInfo->total != 0) {
                $data .= '<th>' . $unpaidtotalInfo->total . '/-</th>';
            } else {
                $data .= '<th>0/-</th>';
            }
        }
        $data .= '<th>' . $studentTotal . '/-<th>';
        $data .= '</tr>';

        $data .= '</table>';

        return $this->response->setBody($data)->setContentType('text/html');
    }
}
