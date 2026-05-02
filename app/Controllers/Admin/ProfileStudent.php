<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;
use DateInterval;
use DatePeriod;

class ProfileStudent extends BaseController
{
    protected $db;

    public function __construct()
    {
        helper(['form', 'url', 'session']);
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $student_id = $this->request->getGet('id') ?? $this->request->getGet('student_id');
        return view('admin/profile_student', ['student_id' => $student_id]);
    }

     public function studentHealthData()
    {
        $student_id = $this->request->getPost('student_id');
        
        if (!$student_id) {
            return '<div class="alert alert-danger">Student ID required</div>';
        }
        
        $student = $this->db->table('students')
            ->select('height, weight, bmi, bmi_category, bmi_updated_date')
            ->where('student_id', $student_id)
            ->get()
            ->getRow();

        if (!$student) {
            return '<div class="alert alert-warning"><i class="fas fa-user-slash mr-1"></i> Student record not found.</div>';
        }

        $bmiHistory = $this->db->table('bmi_history')
            ->where('student_id', $student_id)
            ->orderBy('recorded_date', 'DESC')
            ->limit(5)
            ->get()
            ->getResult();
        
        $html = '
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Current Measurements</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tr><th width="50%">Height</th><td>' . ($student->height ? $student->height . ' cm' : 'Not recorded') . '</td></tr>
                            <tr><th>Weight</th><td>' . ($student->weight ? $student->weight . ' kg' : 'Not recorded') . '</td></tr>
                            <tr><th>BMI</th><td>' . ($student->bmi ? $student->bmi : 'Not calculated') . '</td></tr>
                            <tr><th>Category</th><td>' . ($student->bmi_category ? ucfirst($student->bmi_category) : 'N/A') . '</td></tr>
                            <tr><th>Last Updated</th><td>' . ($student->bmi_updated_date ? date('d-M-Y', strtotime($student->bmi_updated_date)) : 'Never') . '</td></tr>
                        </table>
                        <button class="btn btn-primary" id="recordBmiBtn"><i class="fas fa-plus"></i> Record New Measurement</button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Recent BMI History</h5>
                    </div>
                    <div class="card-body">';
        
        if (empty($bmiHistory)) {
            $html .= '<p class="text-muted">No history records found</p>';
        } else {
            $html .= '<table class="table table-sm">
                <thead><tr><th>Date</th><th>Height</th><th>Weight</th><th>BMI</th><th>Category</th></tr></thead>
                <tbody>';
            foreach ($bmiHistory as $history) {
                $categoryClass = $history->bmi_category == 'normal' ? 'success' : ($history->bmi_category == 'underweight' ? 'info' : ($history->bmi_category == 'overweight' ? 'warning' : 'danger'));
                $html .= '<tr>
                    <td>' . date('d-M-Y', strtotime($history->recorded_date)) . '</td>
                    <td>' . ($history->height ?? '-') . ' cm</td>
                    <td>' . ($history->weight ?? '-') . ' kg</td>
                    <td><strong>' . ($history->bmi ?? '-') . '</strong></td>
                    <td><span class="badge badge-' . $categoryClass . '">' . ucfirst($history->bmi_category ?? 'N/A') . '</span></td>
                </tr>';
            }
            $html .= '</tbody></table>';
        }
        
        $html .= '</div></div></div>';
        
        return $html;
    }

    public function data()
    {
        $student_id = (int) $this->request->getPost('student_id');
        $schoolinfo = getSchoolInfo();

        $student = $this->db->table('students')->where('student_id', $student_id)->get()->getRow();
        if (!$student) {
            return $this->response->setBody('<div class="alert alert-danger">Student not found.</div>');
        }

        $parent = null;
        if (!empty($student->parent_id)) {
            $parent = $this->db->table('parents')->where('parent_id', $student->parent_id)->get()->getRow();
        }

        $profile_photo = '';
        $imgurl = FCPATH . 'uploads/' . ($student->profile_photo ?? '');
        if (!empty($student->profile_photo) && file_exists($imgurl)) {
            $profile_photo = '<img src="' . base_url('uploads/' . $student->profile_photo) . '" alt="" width="112" height="112">';
        } else {
            $profile_photo = '<i class="fa fa-user"></i>';
        }

        $currentClass = null;
        $sessionName = null;
        $sessionId = (int) session()->get('member_sessionid');
        if ($sessionId > 0) {
            $sessRow = $this->db->table('academic_session')->where('session_id', $sessionId)->get()->getRow();
            $sessionName = $sessRow->session_name ?? null;

            $classRow = $this->db->table('student_class sc')
                ->select('c.class_name, sec.section_name')
                ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
                ->join('classes c', 'c.class_id = cs.class_id')
                ->join('sections sec', 'sec.section_id = cs.section_id')
                ->where('sc.student_id', $student_id)
                ->where('sc.session_id', $sessionId)
                ->get()
                ->getRow();

            if ($classRow) {
                $currentClass = trim(($classRow->class_name ?? '') . ' — ' . ($classRow->section_name ?? ''));
            }
        }

        $html = view('admin/partials/profile_student_overview', [
            'student' => $student,
            'parent' => $parent ?: new \stdClass(),
            'schoolinfo' => $schoolinfo,
            'current_class_label' => $currentClass,
            'current_session_name' => $sessionName,
            'profile_photo_html' => $profile_photo,
            'edit_student_url' => base_url('admin/students/edit?id=' . $student_id),
        ]);

        return $this->response->setBody($html);
    }

    /**
     * Subject-wise exam results for the Results tab (groups by exam).
     */
    public function studentResultData()
    {
        $student_id = (int) $this->request->getPost('student_id');
        if ($student_id <= 0) {
            return $this->response->setBody('<div class="alert alert-danger">Student ID required.</div>');
        }

        $exists = $this->db->table('students')->where('student_id', $student_id)->countAllResults();
        if ($exists < 1) {
            return $this->response->setBody('<div class="alert alert-danger">Student not found.</div>');
        }

        $rows = $this->db->query(
            'SELECT sr.result_id, sr.eid, sr.obtained_marks, sr.student_id, sr.session_id,
                    e.exam_name,
                    sub.subject_name AS subject_name,
                    ac.session_name,
                    ds.total_marks AS total_marks
             FROM subject_results sr
             LEFT JOIN exam e ON e.eid = sr.eid
             LEFT JOIN datesheet ds ON ds.eid = sr.eid AND ds.sec_sub_id = sr.sec_sub_id
             LEFT JOIN section_subjects ss ON ss.sec_sub_id = sr.sec_sub_id
             LEFT JOIN allsubject sub ON sub.sid = ss.subject_id
             LEFT JOIN academic_session ac ON ac.session_id = sr.session_id
             WHERE sr.student_id = ?
             ORDER BY sr.eid DESC, sub.subject_name ASC',
            [$student_id]
        )->getResult();

        $exam_groups = [];
        foreach ($rows as $r) {
            $eid = (int) ($r->eid ?? 0);
            if (! isset($exam_groups[$eid])) {
                $exam_groups[$eid] = [
                    'exam_name' => $r->exam_name ?: ('Exam #' . $eid),
                    'session_name' => $r->session_name ?? '',
                    'rows' => [],
                ];
            }
            $exam_groups[$eid]['rows'][] = $r;
        }

        return $this->response->setBody(view('admin/partials/profile_student_results', [
            'exam_groups' => $exam_groups,
        ]));
    }

public function singleStudentFeedata()
{
    $data = '';
    $student_id = $this->request->getPost('student_id');
    $schoolinfo = getSchoolInfo();

    // Get academic sessions
    $academicSession = $this->db->table('academic_session')
        ->where('system_id', $schoolinfo->system_id)
        ->orderBy('start_date', 'DESC')
        ->get()
        ->getResult();

    foreach ($academicSession as $sessionValue) {
        // Get student class info for this session
        $studentClass = $this->db->query("
            SELECT sc.*, c.class_name, sec.section_name 
            FROM student_class sc
            LEFT JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
            LEFT JOIN classes c ON c.class_id = cs.class_id
            LEFT JOIN sections sec ON sec.section_id = cs.section_id
            WHERE sc.session_id = ? AND sc.student_id = ?
        ", [$sessionValue->session_id, $student_id])->getResult();
        
        if (empty($studentClass)) {
            continue;
        }

        // First check if this session has any MONTHLY fee records
        $checkMonthlyFeeQuery = $this->db->query("
            SELECT COUNT(*) as count 
            FROM fee_chalan fc
            INNER JOIN fee_type ft ON ft.fee_type_id = fc.fee_type_id
            WHERE fc.student_id = ? 
            AND fc.fee_month BETWEEN ? AND ?
            AND ft.is_monthly_fee = 1
        ", [
            $student_id, 
            date('Y-m', strtotime($sessionValue->start_date)), 
            date('Y-m', strtotime($sessionValue->end_date))
        ]);
        
        if (!$checkMonthlyFeeQuery || $checkMonthlyFeeQuery->getRow()->count == 0) {
            continue; // Skip sessions with no monthly fees
        }

        // Calculate sum of OTHER fees (non-monthly) for this session - FIXED QUERY
        $otherFeesQuery = $this->db->query("
            SELECT COALESCE(SUM(fc.amount - fc.discount), 0) as total_other_fees
            FROM fee_chalan fc
            INNER JOIN fee_type ft ON ft.fee_type_id = fc.fee_type_id
            WHERE fc.student_id = ? 
            AND fc.fee_month BETWEEN ? AND ?
            AND ft.is_monthly_fee != 1
        ", [
            $student_id, 
            date('Y-m', strtotime($sessionValue->start_date)), 
            date('Y-m', strtotime($sessionValue->end_date))
        ]);
        
        $otherFeesTotal = $otherFeesQuery ? $otherFeesQuery->getRow()->total_other_fees : 0;
        
        // Format the other fees total in Pakistani Rupees
        $otherFeesFormatted = number_format($otherFeesTotal, 0);

        // Generate months between session dates
        $start = new DateTime($sessionValue->start_date);
        $end = new DateTime($sessionValue->end_date);
        $end->modify('first day of next month');
        $period = new DatePeriod($start->modify('first day of this month'), DateInterval::createFromDateString('1 month'), $end);

        // Session header with other fees total
        $data .= '<div class="card mb-4">';
        $data .= '<div class="card-header bg-info text-white">';
        $data .= '<div class="d-flex justify-content-between align-items-center">';
        $data .= '<h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Session: ' . $sessionValue->session_name . '</h5>';
        
        // Show other fees total if > 0
        if ($otherFeesTotal > 0) {
            $data .= '<div class="session-total"><strong>Other Fees: PKR ' . $otherFeesFormatted . '</strong></div>';
        } else {
            $data .= '<div class="session-total"><strong>Other Fees: PKR 0</strong></div>';
        }
        
        $data .= '</div>';
        
        // Show class information
        foreach ($studentClass as $classInfo) {
            $className = $classInfo->class_name ?? 'N/A';
            $sectionName = $classInfo->section_name ?? 'N/A';
            $data .= '<div class="mt-2"><small><i class="fas fa-graduation-cap"></i> Class: ' . $className . ' - ' . $sectionName . '</small></div>';
        }
        $data .= '</div>';
        
        $data .= '<div class="card-body">';
        $data .= '<div class="table-responsive">';
        $data .= '<table class="table table-bordered table-hover">';
        $data .= '<thead class="thead-light">';
        $data .= '<tr>';
        $data .= '<th style="width: 115px;">Month</th>';
        
        foreach ($period as $dt) {
            $data .= '<th class="text-center">' . $dt->format("M Y") . '</th>';
        }
        $data .= '</tr>';
        $data .= '</thead>';
        $data .= '<tbody>';

        // Row for monthly fee amounts
        $data .= '<tr>';
        $data .= '<td><strong>Monthly Fee</strong></td>';
        foreach ($period as $dt) {
            $ym = $dt->format("Y-m");
            
            $totalQuery = $this->db->query("
                SELECT COALESCE(SUM(fc.amount), 0) as total 
                FROM fee_chalan fc
                INNER JOIN fee_type ft ON ft.fee_type_id = fc.fee_type_id
                WHERE fc.student_id = ? 
                AND fc.fee_month = ? 
                AND ft.is_monthly_fee = 1
            ", [$student_id, $ym]);
            
            $total = $totalQuery ? $totalQuery->getRow()->total : 0;
            $data .= '<td class="text-right">' . ($total > 0 ? round($total) : '-') . '</td>';
        }
        $data .= '</tr>';

        $data .= '<tr>';
        $data .= '<td><strong class="text-success">Paid</strong></td>';
        foreach ($period as $dt) {
            $ym = $dt->format("Y-m");
            
            $paidQuery = $this->db->query("
                SELECT COALESCE(SUM(fc.amount), 0) as paid 
                FROM fee_chalan fc
                INNER JOIN fee_type ft ON ft.fee_type_id = fc.fee_type_id
                WHERE fc.student_id = ? 
                AND fc.fee_month = ? 
                AND fc.status = 'paid'
                AND ft.is_monthly_fee = 1
            ", [$student_id, $ym]);
            
            $paid = $paidQuery ? $paidQuery->getRow()->paid : 0;
            $data .= '<td class="text-right text-success">' . ($paid > 0 ? round($paid) : '-') . '</td>';
        }
        $data .= '</tr>';

        $data .= '<tr>';
        $data .= '<td><strong class="text-warning">Discount</strong></td>';
        foreach ($period as $dt) {
            $ym = $dt->format("Y-m");
            
            $discountQuery = $this->db->query("
                SELECT COALESCE(SUM(fc.discount), 0) as discount 
                FROM fee_chalan fc
                INNER JOIN fee_type ft ON ft.fee_type_id = fc.fee_type_id
                WHERE fc.student_id = ? 
                AND fc.fee_month = ? 
                AND ft.is_monthly_fee = 1
            ", [$student_id, $ym]);
            
            $discount = $discountQuery ? $discountQuery->getRow()->discount : 0;
            $data .= '<td class="text-right text-warning">' . ($discount > 0 ? round($discount) : '-') . '</td>';
        }
        $data .= '</tr>';

        $data .= '<tr class="table-active">';
        $data .= '<td><strong class="text-primary">Balance</strong></td>';
        foreach ($period as $dt) {
            $ym = $dt->format("Y-m");
            
            $totalQuery = $this->db->query("
                SELECT COALESCE(SUM(fc.amount), 0) as total 
                FROM fee_chalan fc
                INNER JOIN fee_type ft ON ft.fee_type_id = fc.fee_type_id
                WHERE fc.student_id = ? 
                AND fc.fee_month = ? 
                AND ft.is_monthly_fee = 1
            ", [$student_id, $ym]);
            
            $paidQuery = $this->db->query("
                SELECT COALESCE(SUM(fc.amount), 0) as paid 
                FROM fee_chalan fc
                INNER JOIN fee_type ft ON ft.fee_type_id = fc.fee_type_id
                WHERE fc.student_id = ? 
                AND fc.fee_month = ? 
                AND fc.status = 'paid'
                AND ft.is_monthly_fee = 1
            ", [$student_id, $ym]);
            
            $total = $totalQuery ? $totalQuery->getRow()->total : 0;
            $paid = $paidQuery ? $paidQuery->getRow()->paid : 0;
            
            $balance = $total - $paid;
            $balanceClass = $balance > 0 ? 'text-danger' : ($balance < 0 ? 'text-info' : 'text-success');
            
            $data .= '<td class="text-right ' . $balanceClass . ' font-weight-bold">' . 
                     ($total > 0 ? round($balance) : '-') . '</td>';
        }
        $data .= '</tr>';
        
        $data .= '</tbody>';
        $data .= '</table>';
        $data .= '</div>';
        $data .= '</div>';
        $data .= '</div>';
    }

    if (empty($data)) {
        $data = '<div class="alert alert-info">';
        $data .= '<i class="fas fa-info-circle"></i> No fee records found for this student.';
        $data .= '</div>';
    }

    return $this->response->setBody($data);
}


public function singleStudentAttendancedata()
{
    $data = '';
    $student_id = $this->request->getPost('student_id');
    $schoolinfo = getSchoolInfo();

    // Get academic sessions
    $academicSession = $this->db->table('academic_session')
        ->where('system_id', $schoolinfo->system_id)
        ->orderBy('start_date', 'DESC')
        ->get()
        ->getResult();

    foreach ($academicSession as $sessionValue) {
        // Get student class info for this session
        $studentClass = $this->db->query("
            SELECT sc.*, c.class_name, sec.section_name 
            FROM student_class sc
            LEFT JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
            LEFT JOIN classes c ON c.class_id = cs.class_id
            LEFT JOIN sections sec ON sec.section_id = cs.section_id
            WHERE sc.session_id = ? AND sc.student_id = ?
        ", [$sessionValue->session_id, $student_id])->getResult();
        
        if (empty($studentClass)) {
            continue;
        }

        // Generate months between session dates
        $start = new DateTime($sessionValue->start_date);
        $end = new DateTime($sessionValue->end_date);
        $end->modify('first day of next month');
        $period = new DatePeriod($start->modify('first day of this month'), DateInterval::createFromDateString('1 month'), $end);

        // Session header with class info
        $data .= '<div class="card mb-4">';
        $data .= '<div class="card-header bg-success text-white">';
        $data .= '<div class="d-flex justify-content-between align-items-center">';
        $data .= '<h5 class="mb-0"><i class="fas fa-calendar-check"></i> Session: ' . $sessionValue->session_name . '</h5>';
        
        // Calculate overall attendance percentage for the session
        $totalDays = 0;
        $presentDays = 0;
        
        foreach ($period as $dt) {
            $year = $dt->format("Y");
            $month = $dt->format("m");
            
            // Get working days (total records) for this month
            $workingDaysQuery = $this->db->query("
                SELECT COUNT(*) as total
                FROM attendance 
                WHERE student_id = ? 
                AND YEAR(date) = ? 
                AND MONTH(date) = ?
            ", [$student_id, $year, $month]);
            
            $workingDays = $workingDaysQuery ? $workingDaysQuery->getRow()->total : 0;
            
            // Get present count
            $presentQuery = $this->db->query("
                SELECT COUNT(*) as present 
                FROM attendance 
                WHERE student_id = ? 
                AND YEAR(date) = ? 
                AND MONTH(date) = ?
                AND status = 'P'
            ", [$student_id, $year, $month]);
            
            $present = $presentQuery ? $presentQuery->getRow()->present : 0;
            
            $presentDays += $present;
            $totalDays += $workingDays;
        }
        
        $attendancePercentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;
        
        // Color code overall percentage
        $overallClass = 'text-white';
        if ($attendancePercentage >= 90) $overallClass = 'text-white';
        else if ($attendancePercentage >= 75) $overallClass = 'text-warning';
        else $overallClass = 'text-danger';
        
        $data .= '<div class="session-total"><strong>Overall: <span class="' . $overallClass . '">' . $attendancePercentage . '%</span></strong></div>';
        $data .= '</div>';
        
        // Show class information
        foreach ($studentClass as $classInfo) {
            $className = $classInfo->class_name ?? 'N/A';
            $sectionName = $classInfo->section_name ?? 'N/A';
            $data .= '<div class="mt-2"><small><i class="fas fa-graduation-cap"></i> Class: ' . $className . ' - ' . $sectionName . '</small></div>';
        }
        $data .= '</div>';
        
        $data .= '<div class="card-body">';
        $data .= '<div class="table-responsive">';
        $data .= '<table class="table table-bordered table-hover">';
        $data .= '<thead class="thead-light">';
        $data .= '<tr>';
        $data .= '<th style="width: 115px;">Month</th>';
        
        foreach ($period as $dt) {
            $data .= '<th class="text-center">' . $dt->format("M Y") . '</th>';
        }
        $data .= '</tr>';
        $data .= '</thead>';
        $data .= '<tbody>';

        // Row for Working Days (Total Records)
        $data .= '<tr>';
        $data .= '<td><strong class="text-primary">Total</strong></td>';
        foreach ($period as $dt) {
            $year = $dt->format("Y");
            $month = $dt->format("m");
            
            $workingDaysQuery = $this->db->query("
                SELECT COUNT(*) as total
                FROM attendance 
                WHERE student_id = ? 
                AND YEAR(date) = ? 
                AND MONTH(date) = ?
            ", [$student_id, $year, $month]);
            
            $workingDays = $workingDaysQuery ? $workingDaysQuery->getRow()->total : 0;
            
            $data .= '<td class="text-center">' . ($workingDays > 0 ? $workingDays : '-') . '</td>';
        }
        $data .= '</tr>';

        // Row for Present Days
        $data .= '<tr>';
        $data .= '<td><strong class="text-success">Present</strong></td>';
        foreach ($period as $dt) {
            $year = $dt->format("Y");
            $month = $dt->format("m");
            
            $presentQuery = $this->db->query("
                SELECT COUNT(*) as present 
                FROM attendance 
                WHERE student_id = ? 
                AND YEAR(date) = ? 
                AND MONTH(date) = ?
                AND status = 'P'
            ", [$student_id, $year, $month]);
            
            $present = $presentQuery ? $presentQuery->getRow()->present : 0;
            
            $data .= '<td class="text-center text-success">' . ($present > 0 ? $present : '-') . '</td>';
        }
        $data .= '</tr>';

        // Row for Attendance Percentage
        $data .= '<tr class="table-active">';
        $data .= '<td><strong class="text-primary">Per %</strong></td>';
        foreach ($period as $dt) {
            $year = $dt->format("Y");
            $month = $dt->format("m");
            
            $workingDaysQuery = $this->db->query("
                SELECT COUNT(*) as total
                FROM attendance 
                WHERE student_id = ? 
                AND YEAR(date) = ? 
                AND MONTH(date) = ?
            ", [$student_id, $year, $month]);
            
            $presentQuery = $this->db->query("
                SELECT COUNT(*) as present 
                FROM attendance 
                WHERE student_id = ? 
                AND YEAR(date) = ? 
                AND MONTH(date) = ?
                AND status = 'P'
            ", [$student_id, $year, $month]);
            
            $workingDays = $workingDaysQuery ? $workingDaysQuery->getRow()->total : 0;
            $present = $presentQuery ? $presentQuery->getRow()->present : 0;
            
            $rate = $workingDays > 0 ? round(($present / $workingDays) * 100, 1) : 0;
            
            // Color code the rate
            $rateClass = 'text-success';
            if ($rate < 75) $rateClass = 'text-danger';
            else if ($rate < 90) $rateClass = 'text-warning';
            
            $data .= '<td class="text-center ' . $rateClass . ' font-weight-bold">' . ($workingDays > 0 ? $rate . '%' : '-') . '</td>';
        }
        $data .= '</tr>';
        
        $data .= '</tbody>';
        $data .= '</table>';
        $data .= '</div>';
        $data .= '</div>';
        $data .= '</div>';
    }

    if (empty($data)) {
        $data = '<div class="alert alert-info">';
        $data .= '<i class="fas fa-info-circle"></i> No attendance records found for this student.';
        $data .= '</div>';
    }

    return $this->response->setBody($data);
}


    public function save()
    {
        $id = (int) $this->request->getPost('id');
        $user_id = session('member_userid');
        $date = date('Y-m-d H:i:s');
        $schoolinfo = getSchoolInfo();
        $imageName = '';

        $validationRule = [
            'image' => [
                'label' => 'Image File',
                'rules' => 'uploaded[image]|max_size[image,1024]|ext_in[image,jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,rar]',
            ],
        ];

        if ($this->validate($validationRule)) {
            $file = $this->request->getFile('image');
            if ($file->isValid() && !$file->hasMoved()) {
                $imageName = $file->getRandomName();
                $file->move(ROOTPATH . 'public/system-logo', $imageName);
            }
        }

        $data = [
            'system_name'     => trim($this->request->getPost('system_name')),
            'address'         => trim($this->request->getPost('address')),
            'city'            => trim($this->request->getPost('city')),
            'state'           => trim($this->request->getPost('state')),
            'zip'             => trim($this->request->getPost('zip')),
            'country'         => trim($this->request->getPost('country')),
            'owner_name'      => trim($this->request->getPost('owner_name')),
            'landline_number' => trim($this->request->getPost('landline_number')),
            'mob_number'      => trim($this->request->getPost('mob_number')),
            'reg_text'        => trim($this->request->getPost('reg_text')),
            'slogan'          => trim($this->request->getPost('slogan')),
            'updated_date'    => $date,
            'user_id'         => $user_id
        ];

        if ($imageName) {
            $data['logo'] = $imageName;
        }

        $this->db->transBegin();
        $this->db->table('system')->where('system_id', $id)->update($data);
        $this->db->transComplete();

        $academic_session_info = $this->db->table('academic_session')->where('system_id', $schoolinfo->system_id)->get()->getRow();

        if (empty($academic_session_info->session_id)) {
            return $this->response->setJSON(['session_id' => false, 'msg' => 'Update System Success']);
        } else {
            return $this->response->setJSON(['success' => true, 'msg' => 'Update System Success']);
        }
    }

    public function updatePassword()
    {
        $rules = ['password' => 'required'];
        if (!$this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'msg' => $this->validator->getErrors()]);
        }

        $user_id = $this->request->getPost('user_id');
        $password = password_hash($this->request->getPost('password'), PASSWORD_BCRYPT);

        $this->db->table('users')->where('id', $user_id)->update(['password' => $password]);

        return $this->response->setJSON(['success' => true, 'msg' => 'Change Password Success']);
    }
}