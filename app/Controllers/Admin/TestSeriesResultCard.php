<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class TestSeriesResultCard extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = \Config\Database::connect();
        $this->session = session();
    }

    public function index()
    {
        check_permission('admin-test-result-cards');

        $campus_id = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');

        $currentrole = currentUserRoles();
        $sectionsclassinfo = in_array(5, $currentrole)
            ? teacherSubjectSections()
            : userClassSections();

        $subjects = $this->db->table('allsubject')
            ->where('system_id', getSchoolInfo()->system_id)
            ->get()->getResult();

        return view('admin/test_series_result_card', [
            'sectionsclassinfo' => $sectionsclassinfo,
            'subjects' => $subjects
        ]);
    }

    public function data()
    {
           $cls_sec_id = (int) ($this->request->getVar('cls_sec_id') ?? 0);
        $start_date = $this->request->getPost('start_date');
        $end_date   = $this->request->getPost('end_date');
        $subject_id = $this->request->getPost('subject_id');
        $show_percentage = $this->request->getPost('show_percentage');

        $campus_id  = session('member_campusid');
        $session_id = session('member_sessionid');

        if (empty($cls_sec_id)) {
            return '<div class="alert alert-danger">Class section not selected.</div>';
        }

         if ($cls_sec_id === 0) {
        return '<div class="alert alert-danger">Class section not selected.</div>';
    }

        $students = $this->db->table('students s')
            ->select('s.student_id, s.first_name, s.last_name, s.reg_no')
            ->join('student_class sc', 'sc.student_id = s.student_id')
            ->where('sc.cls_sec_id', $cls_sec_id)
            ->where('sc.session_id', $session_id)
            ->where('s.status', 1)
            ->where('s.campus_id', $campus_id)
            ->orderBy('s.first_name', 'ASC')
            ->get()->getResult();

        if (!$students) {
            return '<div class="alert alert-warning">No students found for this class section.</div>';
        }

       // Read filters
$termIdsRaw  = $this->request->getPost('term_ids');   // array from multi-select (preferred)
$termIdSingle= $this->request->getPost('term_id');    // optional single select (legacy)

// Normalize to an array of ints
$termIds = [];
if (is_array($termIdsRaw)) {
    $termIds = array_values(array_filter(array_map('intval', $termIdsRaw), static fn($v) => $v > 0));
} elseif (!empty($termIdSingle)) {
    $termIds = [ (int)$termIdSingle ];
}

$builder = $this->db->table('test_results tr')
    ->select('tr.student_id, tr.obtained_marks, t.test_date, t.total_marks, a.subject_name')
    ->join('tests t', 't.test_id = tr.test_id')
    ->join('section_subjects ss', 'ss.sec_sub_id = tr.sec_sub_id')
    ->join('allsubject a', 'a.sid = ss.subject_id')
    ->where('t.cls_sec_id', $cls_sec_id)
    ->where('t.session_id', $session_id)
    ->where('tr.campus_id', $campus_id);

// Filter by selected terms (if any)
if (!empty($termIds)) {
    // assumes tests.term_id exists
    $builder->whereIn('t.term_id', $termIds);
}

if (!empty($start_date)) {
    $builder->where('t.test_date >=', $start_date);
}
if (!empty($end_date)) {
    $builder->where('t.test_date <=', $end_date);
}
if (!empty($subject_id)) {
    $builder->where('a.sid', (int)$subject_id);
}

$builder->orderBy('tr.student_id')->orderBy('t.test_date');
$testResults = $builder->get()->getResult();
        $testMeta = [];
        $scores = [];

        foreach ($testResults as $result) {
            $key = $result->subject_name . '|' . $result->test_date;
            $testMeta[$key] = [
                'subject' => $result->subject_name,
                'date' => date('d-m-Y', strtotime($result->test_date)),
                'total_marks' => $result->total_marks
            ];
            $scores[$result->student_id][$key] = $result->obtained_marks;
        }

        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-hover table-striped text-center" id="resultTable">';
        $html .= '<thead><tr><th>Sr#</th><th>Student Name</th><th>Reg #</th>';

        foreach ($testMeta as $key => $meta) {
            $html .= '<th>' . esc($meta['subject']) . '<br><small>' . esc($meta['date']) . '</small></th>';
        }
        $html .= '</tr></thead><tbody>';

        $count = 1;
        foreach ($students as $student) {
            $html .= '<tr>';
            $html .= '<td>' . $count++ . '</td>';
            $html .= '<td>' . esc($student->first_name . ' ' . $student->last_name) . '</td>';
            $html .= '<td>' . esc($student->reg_no) . '</td>';

            foreach ($testMeta as $key => $meta) {
                $mark = isset($scores[$student->student_id][$key]) ? $scores[$student->student_id][$key] : '-';
                if ($mark !== '-' && $show_percentage == '1') {
                    $percent = round(($mark / $meta['total_marks']) * 100, 1);
                    $html .= '<td>' . $percent . '%</td>';
                } else {
                    $html .= '<td>' . $mark . '</td>';
                }
            }
            $html .= '</tr>';
        }

// ✅ prepend or append, but don't overwrite
$html .= '<div class="alert alert-secondary mb-2">';
$html .= 'Selected Class → Section ID: <b>'.esc($cls_sec_id).'</b>';
$html .= '</div>';


        $html .= '</tbody></table>';
        $html .= '<button class="btn btn-secondary mt-3" onclick="window.print()"><i class="fas fa-print"></i> Print</button>';
        $html .= '<button class="btn btn-success mt-3 ms-2" onclick="exportTableToCSV()"><i class="fas fa-file-excel"></i> Export CSV</button>';
        $html .= '</div>';

        return $html;
    }
}
