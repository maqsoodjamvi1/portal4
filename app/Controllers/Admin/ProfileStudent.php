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
            return '<div class="alert alert-warning"><i class="fas fa-user-slash me-1"></i> Student record not found.</div>';
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
                    <td><span class="badge text-bg-' . $categoryClass . '">' . ucfirst($history->bmi_category ?? 'N/A') . '</span></td>
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
     * Academic history for the Results tab: sessions, exam matrix, strength/weakness summary.
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

        try {
            $payload = $this->buildStudentResultProfilePayload($student_id);

            return $this->response->setBody(view('admin/partials/profile_student_results', $payload));
        } catch (\Throwable $e) {
            log_message('error', 'ProfileStudent::studentResultData student_id={id} {msg}', [
                'id'  => $student_id,
                'msg' => $e->getMessage(),
            ]);

            return $this->response->setBody(
                '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle me-1"></i> '
                . 'Could not load results. Please try again or contact support.</div>'
            );
        }
    }

    private function buildStudentResultProfilePayload(int $student_id): array
    {
        $current_session_id = (int) session()->get('member_sessionid');
        $schoolinfo = getSchoolInfo();
        $systemId = ($schoolinfo && isset($schoolinfo->system_id)) ? (int) $schoolinfo->system_id : 0;

        if (! $this->db->tableExists('subject_results')) {
            return [
                'sessions' => [],
                'insights' => ['strengths' => [], 'weaknesses' => []],
                'current_session_id' => $current_session_id,
            ];
        }

        try {
            $rows = $this->db->query(
                'SELECT sr.eid,
                        COALESCE(NULLIF(sr.session_id, 0), e.session_id) AS session_id,
                        sr.sec_sub_id, sr.obtained_marks,
                        e.exam_name, e.exam_start_date, e.term_id,
                        sub.subject_name,
                        ac.session_name, ac.start_date AS session_start,
                        ds.total_marks
                 FROM subject_results sr
                 LEFT JOIN exam e ON e.eid = sr.eid
                 LEFT JOIN datesheet ds ON ds.eid = sr.eid AND ds.sec_sub_id = sr.sec_sub_id
                 LEFT JOIN section_subjects ss ON ss.sec_sub_id = sr.sec_sub_id
                 LEFT JOIN allsubject sub ON sub.sid = ss.subject_id
                 LEFT JOIN academic_session ac ON ac.session_id = COALESCE(NULLIF(sr.session_id, 0), e.session_id)
                 WHERE sr.student_id = ?
                 ORDER BY ac.start_date DESC, e.exam_start_date ASC, sub.subject_name ASC',
                [$student_id]
            )->getResult();
        } catch (\Throwable $e) {
            log_message('error', 'buildStudentResultProfilePayload subject_results: ' . $e->getMessage());

            return [
                'sessions' => [],
                'insights' => ['strengths' => [], 'weaknesses' => []],
                'current_session_id' => $current_session_id,
            ];
        }

        $examResultByEid = $this->loadExamResultsByEid($student_id);

        $termNames = [];
        $termIds = array_values(array_filter(array_unique(array_map(static function ($r) {
            return (int) ($r->term_id ?? 0);
        }, $rows))));
        if (! empty($termIds) && $this->db->tableExists('terms')) {
            foreach ($this->db->table('terms')->whereIn('term_id', $termIds)->get()->getResult() as $term) {
                $termNames[(int) $term->term_id] = (string) ($term->name ?? '');
            }
        }

        $classBySession = [];
        $classRows = $this->db->query(
            'SELECT sc.session_id, c.class_name, s.section_name
             FROM student_class sc
             LEFT JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
             LEFT JOIN classes c ON c.class_id = cs.class_id
             LEFT JOIN sections s ON s.section_id = cs.section_id
             WHERE sc.student_id = ?
             ORDER BY sc.session_id ASC, sc.cls_sec_id ASC',
            [$student_id]
        )->getResult();
        foreach ($classRows as $cr) {
            $sid = (int) ($cr->session_id ?? 0);
            if ($sid > 0 && ! isset($classBySession[$sid])) {
                $classBySession[$sid] = trim(($cr->class_name ?? '') . (($cr->section_name ?? '') !== '' ? ' — ' . $cr->section_name : ''));
            }
        }

        if (empty($rows)) {
            return [
                'sessions' => [],
                'insights' => ['strengths' => [], 'weaknesses' => []],
                'current_session_id' => $current_session_id,
            ];
        }

        $gradeCache = [];
        $subjectPctBuckets = [];
        $sessions = [];
        $seenCells = [];

        foreach ($rows as $r) {
            $session_id = (int) ($r->session_id ?? 0);
            $eid = (int) ($r->eid ?? 0);
            $sec_sub_id = (int) ($r->sec_sub_id ?? 0);
            $cellKey = $session_id . '-' . $eid . '-' . $sec_sub_id;
            if (isset($seenCells[$cellKey])) {
                continue;
            }
            $seenCells[$cellKey] = true;
            $subject_name = trim((string) ($r->subject_name ?? ''));
            if ($subject_name === '') {
                $subject_name = 'Subject #' . $sec_sub_id;
            }

            if (! isset($sessions[$session_id])) {
                $classLabel = $classBySession[$session_id] ?? '';
                $sessions[$session_id] = [
                    'session_id' => $session_id,
                    'session_name' => $r->session_name ?: ('Session #' . $session_id),
                    'session_start' => $r->session_start ?? '',
                    'class_label' => $classLabel !== '' && $classLabel !== '—' ? $classLabel : null,
                    'is_current' => $session_id === $current_session_id,
                    'exams' => [],
                    'subjects' => [],
                ];
            }

            $obt = (float) ($r->obtained_marks ?? 0);
            $total = (float) ($r->total_marks ?? 0);
            $pct = ($total > 0) ? round(($obt / $total) * 100, 1) : null;
            $grade = ($pct !== null && $systemId > 0) ? $this->gradeLabelForPercent((int) round($pct), $systemId, $gradeCache) : null;

            if (! isset($sessions[$session_id]['exams'][$eid])) {
                $examPct = null;
                $er = $examResultByEid[$eid] ?? null;
                $examObt = $er ? (float) ($er->obtain_total_mark ?? 0) : 0;
                $examTotal = $er ? (float) ($er->exam_total_mark ?? 0) : 0;
                if ($examTotal > 0) {
                    $examPct = round(($examObt / $examTotal) * 100, 1);
                }
                $termId = (int) ($r->term_id ?? 0);

                $sessions[$session_id]['exams'][$eid] = [
                    'eid' => $eid,
                    'exam_name' => $r->exam_name ?: ('Exam #' . $eid),
                    'term_name' => $termNames[$termId] ?? '',
                    'exam_start_date' => $r->exam_start_date ?? null,
                    'overall_pct' => $examPct,
                    'overall_grade' => ($examPct !== null && $systemId > 0) ? $this->gradeLabelForPercent((int) round($examPct), $systemId, $gradeCache) : null,
                    'position' => ($er && isset($er->position) && $er->position !== '' && $er->position !== null) ? (int) $er->position : null,
                    'obtained' => $examObt > 0 ? $examObt : null,
                    'total' => $examTotal > 0 ? $examTotal : null,
                ];
            }

            if (! isset($sessions[$session_id]['subjects'][$sec_sub_id])) {
                $sessions[$session_id]['subjects'][$sec_sub_id] = [
                    'sec_sub_id' => $sec_sub_id,
                    'subject_name' => $subject_name,
                    'cells' => [],
                ];
            }

            $sessions[$session_id]['subjects'][$sec_sub_id]['cells'][$eid] = [
                'obtained' => $obt,
                'total' => $total > 0 ? $total : null,
                'pct' => $pct,
                'grade' => $grade,
            ];

            if ($pct !== null) {
                $subjectPctBuckets[$subject_name][] = $pct;
            }
        }

        foreach ($sessions as &$session) {
            $this->fillExamOverallFromSubjects($session, $systemId, $gradeCache);

            $session['exam_totals'] = $this->buildExamTotalsRow($session);

            $session['exams'] = array_values($session['exams']);
            usort($session['exams'], static function ($a, $b) {
                $da = $a['exam_start_date'] ?? '';
                $db = $b['exam_start_date'] ?? '';
                if ($da === $db) {
                    return $a['eid'] <=> $b['eid'];
                }

                return strcmp((string) $da, (string) $db);
            });

            $examOrder = array_column($session['exams'], 'eid');
            $subjects = array_values($session['subjects']);
            usort($subjects, static fn ($a, $b) => strcasecmp($a['subject_name'], $b['subject_name']));
            foreach ($subjects as &$sub) {
                $ordered = [];
                foreach ($examOrder as $examEid) {
                    if (isset($sub['cells'][$examEid])) {
                        $ordered[$examEid] = $sub['cells'][$examEid];
                    }
                }
                $sub['cells'] = $ordered;
            }
            unset($sub);
            $session['subjects'] = $subjects;
        }
        unset($session);

        uasort($sessions, static function ($a, $b) {
            $da = $a['session_start'] ?? '';
            $db = $b['session_start'] ?? '';
            if ($da === $db) {
                return ($b['session_id'] ?? 0) <=> ($a['session_id'] ?? 0);
            }

            return strcmp((string) $db, (string) $da);
        });

        return [
            'sessions' => $sessions,
            'insights' => $this->buildSubjectInsights($subjectPctBuckets),
            'current_session_id' => $current_session_id,
        ];
    }

    /**
     * When compiled exam_results are missing, derive overall % from subject marks.
     */
    private function fillExamOverallFromSubjects(array &$session, int $systemId, array &$gradeCache): void
    {
        foreach ($session['exams'] as $eid => &$exam) {
            if ($exam['overall_pct'] !== null) {
                continue;
            }

            $obt = 0.0;
            $total = 0.0;
            foreach ($session['subjects'] as $sub) {
                $cell = $sub['cells'][$eid] ?? null;
                if ($cell === null) {
                    continue;
                }
                $obt += (float) ($cell['obtained'] ?? 0);
                $total += (float) ($cell['total'] ?? 0);
            }

            if ($total <= 0) {
                continue;
            }

            $examPct = round(($obt / $total) * 100, 1);
            $exam['overall_pct'] = $examPct;
            $exam['obtained'] = $obt;
            $exam['total'] = $total;
            if ($systemId > 0) {
                $exam['overall_grade'] = $this->gradeLabelForPercent((int) round($examPct), $systemId, $gradeCache);
            }
        }
        unset($exam);
    }

    /**
     * @return array<int, array{obtained: float, total: float|null, pct: float|null, grade: string|null}>
     */
    private function buildExamTotalsRow(array $session): array
    {
        $totals = [];
        foreach ($session['exams'] as $eid => $exam) {
            if ($exam['obtained'] !== null && $exam['total'] !== null && $exam['total'] > 0) {
                $totals[$eid] = [
                    'obtained' => (float) $exam['obtained'],
                    'total' => (float) $exam['total'],
                    'pct' => $exam['overall_pct'],
                    'grade' => $exam['overall_grade'] ?? null,
                ];
                continue;
            }

            $obt = 0.0;
            $total = 0.0;
            foreach ($session['subjects'] as $sub) {
                $cell = $sub['cells'][$eid] ?? null;
                if ($cell === null) {
                    continue;
                }
                $obt += (float) ($cell['obtained'] ?? 0);
                $total += (float) ($cell['total'] ?? 0);
            }

            $totals[$eid] = [
                'obtained' => $obt,
                'total' => $total > 0 ? $total : null,
                'pct' => ($total > 0) ? round(($obt / $total) * 100, 1) : null,
                'grade' => null,
            ];
        }

        return $totals;
    }

    private function buildSubjectInsights(array $subjectPctBuckets): array
    {
        $averages = [];
        foreach ($subjectPctBuckets as $name => $pcts) {
            if (count($pcts) < 1) {
                continue;
            }
            $averages[] = [
                'subject_name' => $name,
                'avg_pct' => round(array_sum($pcts) / count($pcts), 1),
                'exam_count' => count($pcts),
            ];
        }

        if (empty($averages)) {
            return ['strengths' => [], 'weaknesses' => []];
        }

        usort($averages, static fn ($a, $b) => $b['avg_pct'] <=> $a['avg_pct']);

        $strengths = [];
        $weaknesses = [];
        foreach ($averages as $row) {
            if ($row['avg_pct'] >= 75 && count($strengths) < 5) {
                $strengths[] = $row;
            }
        }
        foreach (array_reverse($averages) as $row) {
            if ($row['avg_pct'] < 75 && count($weaknesses) < 5) {
                $weaknesses[] = $row;
            }
        }

        if (empty($strengths)) {
            $strengths = array_slice($averages, 0, min(3, count($averages)));
        }
        if (empty($weaknesses) && count($averages) > 3) {
            $weaknesses = array_slice(array_reverse($averages), 0, 3);
        }

        return ['strengths' => $strengths, 'weaknesses' => $weaknesses];
    }

    private function gradeLabelForPercent(int $pct, int $system_id, array &$cache): ?string
    {
        if (isset($cache[$pct])) {
            return $cache[$pct];
        }

        $cache[$pct] = null;
        if ($system_id <= 0 || ! $this->db->tableExists('grading_policy') || ! $this->db->tableExists('grades')) {
            return null;
        }

        try {
            $row = $this->db->table('grading_policy gp')
                ->select('g.name AS grade_name')
                ->join('grades g', 'g.gid = gp.gid', 'left')
                ->where('gp.system_id', $system_id)
                ->where("{$pct} BETWEEN gp.mark_from AND gp.mark_to", null, false)
                ->get(1)
                ->getRow();

            $cache[$pct] = ($row && ! empty($row->grade_name)) ? (string) $row->grade_name : null;
        } catch (\Throwable $e) {
            log_message('debug', 'gradeLabelForPercent: ' . $e->getMessage());
        }

        return $cache[$pct];
    }

    /**
     * @return array<int, object>
     */
    private function loadExamResultsByEid(int $student_id): array
    {
        $table = $this->resolveExamResultsTableName();
        if ($table === null || $student_id <= 0) {
            return [];
        }

        try {
            $out = [];
            foreach ($this->db->table($table)->where('student_id', $student_id)->get()->getResult() as $er) {
                $out[(int) ($er->eid ?? 0)] = $er;
            }

            return $out;
        } catch (\Throwable $e) {
            log_message('debug', 'loadExamResultsByEid: ' . $e->getMessage());

            return [];
        }
    }

    private function resolveExamResultsTableName(): ?string
    {
        foreach (['exam_results', 'd_exam_results', 'student_exam_results'] as $table) {
            if ($this->db->tableExists($table)) {
                return $table;
            }
        }

        return null;
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
        $data .= '<thead class="table-light">';
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
            $data .= '<td class="text-end">' . ($total > 0 ? round($total) : '-') . '</td>';
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
            $data .= '<td class="text-end text-success">' . ($paid > 0 ? round($paid) : '-') . '</td>';
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
            $data .= '<td class="text-end text-warning">' . ($discount > 0 ? round($discount) : '-') . '</td>';
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
            
            $data .= '<td class="text-end ' . $balanceClass . ' fw-bold">' . 
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
    helper('school');

    $data = '';
    $student_id = (int) $this->request->getPost('student_id');
    $schoolinfo = getSchoolInfo();
    $campusId = (int) ($this->db->table('students')->select('campus_id')->where('student_id', $student_id)->get()->getRow()->campus_id ?? 0);

    if ($student_id <= 0 || ! $schoolinfo) {
        return $this->response->setBody('<div class="alert alert-danger">Student not found.</div>');
    }

    $systemId = (int) ($schoolinfo->system_id ?? 0);
    $academicSession = $this->resolveStudentAttendanceSessions($student_id, $systemId);

    foreach ($academicSession as $sessionValue) {
        $sessionId = (int) $sessionValue->session_id;
        $sessionStart = (string) $sessionValue->start_date;
        $sessionEnd   = (string) $sessionValue->end_date;

        $studentClass = $this->resolveStudentSessionEnrollment($student_id, $sessionId);
        $clsSecId     = (int) ($studentClass->cls_sec_id ?? 0);

        $studentAttendanceByDate = $this->loadStudentAttendanceByDate($student_id, $sessionStart, $sessionEnd);

        if ($clsSecId <= 0 && $studentAttendanceByDate === []) {
            continue;
        }

        $sectionAttendanceDates = [];
        $timings = [];

        if ($clsSecId > 0) {
            $sectionAttendanceDates = $this->buildSectionAttendanceDates(
                $clsSecId,
                $sessionId,
                $sessionStart,
                $sessionEnd
            );
            $timings = $this->getSectionTimingsMap($clsSecId, $campusId);
        }

        if ($sectionAttendanceDates === [] && $studentAttendanceByDate !== []) {
            $sectionAttendanceDates = array_fill_keys(array_keys($studentAttendanceByDate), true);
            $timings = [];
        }

        $start = new DateTime($sessionStart);
        $end = new DateTime($sessionEnd);
        $end->modify('first day of next month');
        $period = new DatePeriod($start->modify('first day of this month'), DateInterval::createFromDateString('1 month'), $end);

        $monthStats = $this->buildSessionMonthAttendanceStats(
            $period,
            $sessionStart,
            $sessionEnd,
            $studentAttendanceByDate,
            $timings,
            $sectionAttendanceDates
        );

        $sessionWorking = array_sum(array_column($monthStats, 'working_days'));
        $sessionPresent = array_sum(array_column($monthStats, 'present'));

        if ($sessionWorking === 0 && $studentAttendanceByDate !== []) {
            $monthStats = $this->buildSessionMonthAttendanceStats(
                $period,
                $sessionStart,
                $sessionEnd,
                $studentAttendanceByDate,
                [],
                array_fill_keys(array_keys($studentAttendanceByDate), true)
            );
            $sessionWorking = array_sum(array_column($monthStats, 'working_days'));
            $sessionPresent = array_sum(array_column($monthStats, 'present'));
        }

        if ($sessionWorking === 0 && $studentAttendanceByDate === []) {
            continue;
        }

        $attendancePercentage = $sessionWorking > 0
            ? round(($sessionPresent / $sessionWorking) * 100, 1)
            : 0;
        $overallClass = $attendancePercentage >= 90
            ? 'text-white'
            : ($attendancePercentage >= 75 ? 'text-warning' : 'text-danger');

        $data .= '<div class="card mb-4">';
        $data .= '<div class="card-header bg-success text-white">';
        $data .= '<div class="d-flex justify-content-between align-items-center">';
        $data .= '<h5 class="mb-0"><i class="fas fa-calendar-check"></i> Session: ' . esc($sessionValue->session_name) . '</h5>';
        $data .= '<div class="session-total"><strong>Present: <span class="' . $overallClass . '">' . $attendancePercentage . '%</span></strong></div>';
        $data .= '</div>';
        $classLabel = $studentClass
            ? trim(($studentClass->class_name ?? 'N/A') . ' - ' . ($studentClass->section_name ?? 'N/A'))
            : 'N/A';
        $data .= '<div class="mt-2"><small><i class="fas fa-graduation-cap"></i> Class: '
            . esc($classLabel)
            . '</small></div>';
        $data .= '<div class="mt-1"><small class="text-white-50">Working days = class days with attendance marked. No class record = off day.</small></div>';
        $data .= '</div>';

        $data .= '<div class="card-body"><div class="table-responsive">';
        $data .= '<table class="table table-bordered table-hover">';
        $data .= '<thead class="table-light"><tr><th style="width: 130px;">Month</th>';
        foreach ($period as $dt) {
            $data .= '<th class="text-center">' . esc($dt->format('M Y')) . '</th>';
        }
        $data .= '</tr></thead><tbody>';

        $data .= '<tr><td><strong class="text-primary">Working Days</strong></td>';
        foreach ($period as $dt) {
            $wd = (int) ($monthStats[$dt->format('Y-m')]['working_days'] ?? 0);
            $data .= '<td class="text-center">' . ($wd > 0 ? $wd : '-') . '</td>';
        }
        $data .= '</tr>';

        $data .= '<tr><td><strong class="text-danger">Non-Present</strong></td>';
        foreach ($period as $dt) {
            $np = (int) ($monthStats[$dt->format('Y-m')]['non_present'] ?? 0);
            $data .= '<td class="text-center text-danger">' . ($np > 0 ? $np : '-') . '</td>';
        }
        $data .= '</tr>';

        $data .= '<tr class="table-active"><td><strong class="text-primary">Present %</strong></td>';
        foreach ($period as $dt) {
            $stats = $monthStats[$dt->format('Y-m')] ?? ['working_days' => 0, 'present_pct' => 0];
            $wd = (int) ($stats['working_days'] ?? 0);
            $rate = (float) ($stats['present_pct'] ?? 0);
            $rateClass = $rate >= 90 ? 'text-success' : ($rate >= 75 ? 'text-warning' : 'text-danger');
            $data .= '<td class="text-center ' . $rateClass . ' fw-bold">'
                . ($wd > 0 ? $rate . '%' : '-') . '</td>';
        }
        $data .= '</tr>';

        $data .= '</tbody></table></div></div></div>';
    }

    if ($data === '') {
        $data = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No attendance records found for this student.</div>';
    }

    return $this->response->setBody($data);
}

/**
 * Academic sessions where the student was enrolled and/or has attendance records.
 *
 * @return list<object>
 */
private function resolveStudentAttendanceSessions(int $studentId, int $systemId): array
{
    if ($studentId <= 0 || $systemId <= 0) {
        return [];
    }

    $byId = [];

    $fromClass = $this->db->query(
        'SELECT ac.session_id, ac.session_name, ac.start_date, ac.end_date
         FROM student_class sc
         INNER JOIN academic_session ac ON ac.session_id = sc.session_id
         WHERE sc.student_id = ? AND ac.system_id = ?
         GROUP BY ac.session_id, ac.session_name, ac.start_date, ac.end_date
         ORDER BY ac.start_date DESC',
        [$studentId, $systemId]
    )->getResult();

    foreach ($fromClass as $row) {
        $byId[(int) ($row->session_id ?? 0)] = $row;
    }

    $fromAttendance = $this->db->query(
        'SELECT ac.session_id, ac.session_name, ac.start_date, ac.end_date
         FROM attendance a
         INNER JOIN academic_session ac ON ac.system_id = ?
           AND a.date >= ac.start_date AND a.date <= ac.end_date
         WHERE a.student_id = ?
         GROUP BY ac.session_id, ac.session_name, ac.start_date, ac.end_date
         ORDER BY ac.start_date DESC',
        [$systemId, $studentId]
    )->getResult();

    foreach ($fromAttendance as $row) {
        $id = (int) ($row->session_id ?? 0);
        if ($id > 0 && ! isset($byId[$id])) {
            $byId[$id] = $row;
        }
    }

    $sessions = array_values($byId);
    usort($sessions, static function ($a, $b) {
        return strcmp((string) ($b->start_date ?? ''), (string) ($a->start_date ?? ''));
    });

    return $sessions;
}

/**
 * Class enrollment for a session (includes promoted/inactive rows from past sessions).
 */
private function resolveStudentSessionEnrollment(int $studentId, int $sessionId): ?object
{
    if ($studentId <= 0 || $sessionId <= 0) {
        return null;
    }

    return $this->db->query("
        SELECT sc.cls_sec_id, c.class_name, sec.section_name, sc.status
        FROM student_class sc
        LEFT JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
        LEFT JOIN classes c ON c.class_id = cs.class_id
        LEFT JOIN sections sec ON sec.section_id = cs.section_id
        WHERE sc.session_id = ? AND sc.student_id = ?
        ORDER BY sc.status DESC, sc.sc_id DESC
        LIMIT 1
    ", [$sessionId, $studentId])->getRow() ?: null;
}

/**
 * @param DatePeriod<int, int, null|\DateTime> $period
 * @param array<string, object> $studentAttendanceByDate
 * @param array<string, object> $timings
 * @param array<string, true>   $sectionAttendanceDates
 *
 * @return array<string, array{working_days: int, present: int, non_present: int, present_pct: float}>
 */
private function buildSessionMonthAttendanceStats(
    DatePeriod $period,
    string $sessionStart,
    string $sessionEnd,
    array $studentAttendanceByDate,
    array $timings,
    array $sectionAttendanceDates
): array {
    $monthStats = [];

    foreach ($period as $dt) {
        $monthStart = $dt->format('Y-m-01');
        $monthEnd   = $dt->format('Y-m-t');
        if ($monthStart < $sessionStart) {
            $monthStart = $sessionStart;
        }
        if ($monthEnd > $sessionEnd) {
            $monthEnd = $sessionEnd;
        }

        $monthKey = $dt->format('Y-m');
        $monthStats[$monthKey] = $this->computeStudentAttendanceBetween(
            $monthStart,
            $monthEnd,
            $studentAttendanceByDate,
            $timings,
            $sectionAttendanceDates
        );
    }

    return $monthStats;
}

/**
 * Dates when the class section had at least one attendance record (working days for the section).
 *
 * @return array<string, true>
 */
private function buildSectionAttendanceDates(
    int $clsSecId,
    int $sessionId,
    string $rangeStart,
    string $rangeEnd
): array {
    $dates = [];
    if ($clsSecId <= 0 || $sessionId <= 0) {
        return $dates;
    }

    $rows = $this->db->table('attendance a')
        ->select('DATE(a.date) AS att_date', false)
        ->join('student_class sc', 'sc.student_id = a.student_id', 'inner')
        ->where('sc.cls_sec_id', $clsSecId)
        ->where('sc.session_id', $sessionId)
        ->where('a.date >=', $rangeStart)
        ->where('a.date <=', $rangeEnd)
        ->groupBy('att_date')
        ->get()
        ->getResult();

    foreach ($rows as $row) {
        $dateKey = date('Y-m-d', strtotime((string) ($row->att_date ?? '')));
        if ($dateKey !== '') {
            $dates[$dateKey] = true;
        }
    }

    return $dates;
}

/**
 * @return array<string, object> keyed by Y-m-d
 */
private function loadStudentAttendanceByDate(int $studentId, string $rangeStart, string $rangeEnd): array
{
    $map = [];
    $rows = $this->db->table('attendance')
        ->select('date, status, lc_duration, el_duration')
        ->where('student_id', $studentId)
        ->where('date >=', $rangeStart)
        ->where('date <=', $rangeEnd)
        ->get()
        ->getResult();

    foreach ($rows as $row) {
        $dateKey = date('Y-m-d', strtotime((string) ($row->date ?? '')));
        if ($dateKey !== '') {
            $map[$dateKey] = $row;
        }
    }

    return $map;
}

/**
 * @return array<string, object> keyed by day name (Monday, ...)
 */
private function getSectionTimingsMap(int $clsSecId, int $campusId): array
{
    $map = [];
    foreach (getSchoolTimingsForSections([$clsSecId], $campusId) as $timing) {
        $day = (string) ($timing['dayname'] ?? '');
        if ($day !== '') {
            $map[$day] = (object) $timing;
        }
    }

    return $map;
}

/**
 * @param array<string, object> $studentAttendanceByDate
 * @param array<string, object> $timings
 * @param array<string, true>   $sectionAttendanceDates
 *
 * @return array{working_days: int, present: int, non_present: int, present_pct: float}
 */
private function computeStudentAttendanceBetween(
    string $startDate,
    string $endDate,
    array $studentAttendanceByDate,
    array $timings,
    array $sectionAttendanceDates
): array {
    $workingDays = 0;
    $presentDays = 0;
    $nonPresentDays = 0;
    $current = strtotime($startDate);
    $end = strtotime($endDate);
    $today = strtotime(date('Y-m-d'));

    while ($current <= $end) {
        if ($current > $today) {
            break;
        }

        $date = date('Y-m-d', $current);
        $dayName = date('l', $current);

        if (! $this->isClassWorkingDate($date, $dayName, $timings, $sectionAttendanceDates)) {
            $current = strtotime('+1 day', $current);
            continue;
        }

        $workingDays++;
        $attendance = $studentAttendanceByDate[$date] ?? null;

        if ($this->isProfileStudentPresent($attendance)) {
            $presentDays++;
        } else {
            $nonPresentDays++;
        }

        $current = strtotime('+1 day', $current);
    }

    return [
        'working_days' => $workingDays,
        'present'      => $presentDays,
        'non_present'  => $nonPresentDays,
        'present_pct'  => $workingDays > 0 ? round(($presentDays / $workingDays) * 100, 1) : 0.0,
    ];
}

/**
 * @param array<string, true> $sectionAttendanceDates
 */
private function isClassWorkingDate(string $dateYmd, string $dayName, array $timings, array $sectionAttendanceDates): bool
{
    if (empty($sectionAttendanceDates[$dateYmd])) {
        return false;
    }

    if ($timings === []) {
        return true;
    }

    return $this->isTimingWorkingDay($dayName, $timings);
}

private function isTimingWorkingDay(string $dayName, array $timings): bool
{
    if (! isset($timings[$dayName])) {
        return false;
    }

    $timing = $timings[$dayName];
    $checkin  = (string) ($timing->checkin_timing ?? '');
    $checkout = (string) ($timing->checkout_timing ?? '');

    if ($checkin === '' || $checkout === '') {
        return false;
    }

    return $checkin !== $checkout;
}

private function isProfileStudentPresent($attendance): bool
{
    if ($attendance === null) {
        return false;
    }

    $status = strtoupper(trim((string) ($attendance->status ?? '')));
    $lcDuration = (int) ($attendance->lc_duration ?? 0);
    $elDuration = (int) ($attendance->el_duration ?? 0);

    if (in_array($status, ['A', 'ABSENT', 'L', 'LEAVE', 'LC', 'LATE', 'LATE COMING', 'EL', 'EARLY LEAVE'], true)) {
        return false;
    }

    if ($lcDuration > 0 || $elDuration > 0) {
        return false;
    }

    return in_array($status, ['P', 'PRESENT'], true);
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