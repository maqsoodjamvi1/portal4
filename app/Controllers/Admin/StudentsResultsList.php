<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class StudentsResultsList extends BaseController
{
   
    protected $db;
    protected $session;
    protected $campusId;
    protected $sessionId;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['url', 'form']);
        check_permission('admin-students-results');
        
        // Get current session from global session (same as Dashboard)
        $this->campusId = (int) $this->session->get('member_campusid');
        $this->sessionId = (int) $this->session->get('member_sessionid');
    }

    public function index()
    {
        $this->requireResultListAccess();

        $campusId  = $this->campusId;
        $sessionId = $this->sessionId;

        $data = [
            'sessionData'        => ['campusid' => $campusId, 'sessionid' => $sessionId],
            'infostudents'       => $this->db->table('students')->get()->getResult(),
            'sectionsclassinfo'  => $this->isCurrentTeacher()
                ? $this->getCurrentTeacherSubjectSections()
                : userClassSections(),
            'campusinfo'         => $this->db->table('campus')->where('campus_id', $campusId)->get()->getResult(),
            'examinfo'           => $this->fetchExamsForSession($campusId, $sessionId),
            'academic_session'   => $this->db->table('academic_session')->where('session_id', $sessionId)->get()->getResult(),
            'subjectinfo'        => $this->db->table('allsubject')->get()->getResult(),
        ];

        return view('admin/students_results_list', $data);
    }

    /**
     * Students enrolled in the class/section for the selected academic session.
     * When $activeOnly is true, only students with students.status = 1 are included.
     */
    private function fetchClassStudents(
        int $clsSecId,
        int $sessionId,
        bool $activeOnly = false,
        string $orderBy = 'name'
    ): array {
        $orderBy = $orderBy === 'reg_no' ? 'reg_no' : 'name';

        $builder = $this->db->table('student_class sc')
            ->select('s.student_id, s.first_name, s.last_name, s.reg_no')
            ->join('students s', 's.student_id = sc.student_id', 'inner')
            ->where('sc.cls_sec_id', $clsSecId)
            ->where('sc.session_id', $sessionId);

        if ($activeOnly) {
            $builder->where('s.status', '1');
        }

        if ($orderBy === 'reg_no') {
            $builder->orderBy('s.reg_no', 'ASC');
        } else {
            $builder->orderBy('s.first_name', 'ASC')->orderBy('s.last_name', 'ASC');
        }

        return $builder->get()->getResultArray();
    }

    /**
     * @return list<object>
     */
    private function fetchExamsForSession(int $campusId, int $sessionId): array
    {
        if ($campusId <= 0 || $sessionId <= 0) {
            return [];
        }

        return $this->db->table('exam')
            ->where(['campus_id' => $campusId, 'session_id' => $sessionId])
            ->orderBy('exam_name', 'ASC')
            ->get()
            ->getResult();
    }

    private function getCampusSystemId(int $campusId): int
    {
        if ($campusId <= 0) {
            return 0;
        }

        return (int) ($this->db->table('campus')
            ->select('system_id')
            ->where('campus_id', $campusId)
            ->get()
            ->getRow('system_id') ?? 0);
    }

    /**
     * @return list<object>
     */
    private function fetchAcademicSessionsForCampus(int $campusId): array
    {
        $systemId = $this->getCampusSystemId($campusId);
        if ($systemId <= 0) {
            return [];
        }

        return $this->db->table('academic_session')
            ->where('system_id', $systemId)
            ->orderBy('session_id', 'DESC')
            ->get()
            ->getResult();
    }

    private function sessionBelongsToCampus(int $sessionId, int $campusId): bool
    {
        if ($sessionId <= 0 || $campusId <= 0) {
            return false;
        }

        $systemId = $this->getCampusSystemId($campusId);
        if ($systemId <= 0) {
            return false;
        }

        return $this->db->table('academic_session')
            ->where(['session_id' => $sessionId, 'system_id' => $systemId])
            ->countAllResults() > 0;
    }

    private function examBelongsToSession(int $eid, int $sessionId, int $campusId): bool
    {
        if ($eid <= 0 || $sessionId <= 0 || $campusId <= 0) {
            return false;
        }

        return $this->db->table('exam')
            ->where(['eid' => $eid, 'session_id' => $sessionId, 'campus_id' => $campusId])
            ->countAllResults() > 0;
    }

    /**
     * AJAX: exams for selected academic session.
     */
    public function get_exams()
    {
        $this->requireResultListAccess();

        $campusId  = (int) $this->request->getPost('campus_id');
        $sessionId = (int) $this->request->getPost('session_id');

        if (! $this->sessionBelongsToCampus($sessionId, $campusId)) {
            return $this->response->setJSON(['ok' => false, 'options' => '', 'session_name' => '']);
        }

        $session = $this->db->table('academic_session')
            ->select('session_name')
            ->where('session_id', $sessionId)
            ->get()
            ->getRow();

        $exams = $this->fetchExamsForSession($campusId, $sessionId);
        $html  = '';
        foreach ($exams as $exam) {
            $html .= '<option value="' . (int) $exam->eid . '">' . esc($exam->exam_name) . '</option>';
        }

        return $this->response->setJSON([
            'ok'           => true,
            'options'      => $html,
            'session_name' => (string) ($session->session_name ?? ''),
        ]);
    }
    /**
     * Fetch students based on session type
     */
  
    /**
     * AJAX: Get students and results
     */
    public function get_students()
    {
        $this->requireResultListAccess();

        $r = $this->request;
        $eid = (int) $r->getPost('eid');
        $sessionId = (int) $r->getPost('session_id'); // From hidden input
        $campusId = (int) $r->getPost('campus_id');
        $clsSecId = (int) $r->getPost('cls_sec_id');
        if ($clsSecId === 0) {
            return $this->response->setBody("<div class='alert alert-warning'>Select Class Section</div>");
        }
        if ($sessionId <= 0 || ! $this->sessionBelongsToCampus($sessionId, $campusId)) {
            return $this->response->setBody("<div class='alert alert-warning'>Select a valid academic session.</div>");
        }
        if ($this->isCurrentTeacher() && ! $this->teacherHasSubjectInSection((int) $this->session->get('member_userid'), $clsSecId)) {
            return $this->response->setBody("<div class='alert alert-danger'>You can only view results for sections assigned to you.</div>");
        }
        if (!$eid) {
            return $this->response->setBody("<div class='alert alert-warning'>Select an exam.</div>");
        }

        if (! $this->examBelongsToSession($eid, $sessionId, $campusId)) {
            return $this->response->setBody("<div class='alert alert-warning'>Selected exam does not belong to the chosen session.</div>");
        }

        $activeOnly = $r->getPost('active_only') === '1' || $r->getPost('active_only') === 'on';
        if ($r->getPost('student_type') === 'current') {
            $activeOnly = true;
        } elseif ($r->getPost('student_type') === 'session_based') {
            $activeOnly = false;
        }

        $orderBy = $r->getPost('order_by');
        $orderBy = in_array($orderBy, ['name', 'reg_no'], true) ? $orderBy : 'name';
        $students = $this->fetchClassStudents($clsSecId, $sessionId, $activeOnly, $orderBy);

        if (empty($students)) {
            $message = $activeOnly
                ? 'No active students (status = 1) found for this class/section in the selected session.'
                : 'No students found for this class/section in the selected session.';
            return $this->response->setBody("<div class='alert alert-info'>{$message}</div>");
        }
        
        $studentIds = array_map(static fn($s) => (int)$s['student_id'], $students);

        // Get papers
        [$papers, $secSubIds, $maxByPaper, $examDateByPaper, $subShort] =
            $this->fetchDatesheetPapers($eid, $clsSecId);

        if (empty($papers)) {
            return $this->response->setBody("<div class='alert alert-info'>No papers found for this class/exam.</div>");
        }

        // Load marks
        $marks = $this->loadMarksMap($eid, $clsSecId, $studentIds, $secSubIds);

        // Get grading policy
        $systemId = (int) ($this->db->table('campus')->select('system_id')->where('campus_id', $campusId)->get()->getRow('system_id') ?? 0);
        $gradingPolicies = $this->loadGradingPolicy($systemId);

        $gradeSummaryLabels = $this->getGradeSummaryLabels($gradingPolicies, $systemId);

        $summary = $this->summarizeBySubject(
            $secSubIds,
            $maxByPaper,
            $marks,
            $studentIds,
            $examDateByPaper,
            $gradingPolicies,
            $gradeSummaryLabels
        );

        $styles = $this->inlineStyles();
        $tableHtml = $this->renderStudentTable(
            $students,
            $secSubIds,
            $subShort,
            $maxByPaper,
            $marks,
            $gradingPolicies,
            $summary,
            $gradeSummaryLabels
        );

        return $this->response->setBody($styles . '<div class="srl-report-sheet">' . $tableHtml . '</div>');
    }
    /**
     * Load current academic session with proper error handling
     */
    protected function loadAcademicSession(): void
    {
        $campusId = (int) $this->session->get('member_campusid');
        $this->campusId = $campusId;
        
        try {
            // Method 1: Try to get from member_sessionid in session first
            $memberSessionId = $this->session->get('member_sessionid');
            if (!empty($memberSessionId)) {
                $sessionData = $this->db->table('academic_session')
                    ->select('session_id, session_name, start_date, end_date, status')
                    ->where('session_id', $memberSessionId)
                    ->get()
                    ->getRow();
                
                if ($sessionData) {
                    $this->academic_session = $sessionData;
                    $this->sessionId = (int) $sessionData->session_id;
                    return;
                }
            }
            
            // Method 2: Get current active session for this school (system)
            $systemId = $this->getCampusSystemId($campusId);
            $builder = $this->db->table('academic_session');
            $builder->select('session_id, session_name, start_date, end_date, status');
            if ($systemId > 0) {
                $builder->where('system_id', $systemId);
            }
            $builder->where('status', 1);
            $builder->orderBy('session_id', 'DESC');
            $query = $builder->get();
            
            if ($query !== false && $query->getNumRows() > 0) {
                $sessionData = $query->getRow();
                if ($sessionData) {
                    $this->academic_session = $sessionData;
                    $this->sessionId = (int) $sessionData->session_id;
                    return;
                }
            }
            
            // Method 3: Fallback - get latest session regardless of status
            $builder = $this->db->table('academic_session');
            $builder->select('session_id, session_name, start_date, end_date, status');
            if ($systemId > 0) {
                $builder->where('system_id', $systemId);
            }
            $builder->orderBy('session_id', 'DESC');
            $builder->limit(1);
            $query = $builder->get();
            
            if ($query !== false && $query->getNumRows() > 0) {
                $sessionData = $query->getRow();
                if ($sessionData) {
                    $this->academic_session = $sessionData;
                    $this->sessionId = (int) $sessionData->session_id;
                    return;
                }
            }
            
            // Method 4: Create a default session object if no sessions exist
            $this->academic_session = new \stdClass();
            $this->academic_session->session_id = 0;
            $this->academic_session->session_name = 'No Active Session';
            $this->academic_session->start_date = date('Y-m-d');
            $this->academic_session->end_date = date('Y-m-d', strtotime('+1 year'));
            $this->academic_session->status = 0;
            $this->sessionId = 0;
            
            log_message('error', 'No academic session found for campus_id: ' . $campusId);
            
        } catch (\Exception $e) {
            log_message('error', 'Error loading academic session: ' . $e->getMessage());
            $this->academic_session = new \stdClass();
            $this->academic_session->session_id = 0;
            $this->academic_session->session_name = 'Error Loading Session';
            $this->sessionId = 0;
        }
    }
    
    /**
     * Get current session ID
     */
    protected function getCurrentSessionId(): int
    {
        return $this->sessionId ?? 0;
    }

   
    /**
     * Fetch students based on session type
     */
   
    /**
     * AJAX: Get students and results
     */
   

    /**
     * Fetch datesheet papers
     */
    private function fetchDatesheetPapers(int $eid, int $clsSecId): array
    {
        try {
            $builder = $this->db->table('datesheet d');
            $builder->select('d.sec_sub_id, d.total_marks, d.exam_date, a.subject_short_name, a.subject_name');
            $builder->join(
                'section_subjects ss',
                'ss.sec_sub_id = d.sec_sub_id AND ss.cls_sec_id = d.cls_sec_id AND ss.status = 1',
                'inner'
            );
            $builder->join('allsubject a', 'a.sid = ss.subject_id', 'inner');
            $builder->where('d.eid', $eid);
            $builder->where('d.cls_sec_id', $clsSecId);
            $builder->where('IFNULL(d.total_marks,0) >', 0);
            $builder->where('IFNULL(d.enable,1) =', 1);

            if ($this->isCurrentTeacher()) {
                $tid = (int) $this->session->get('member_userid');
                $builder->join('teacher_subjects ts',
                    "ts.sec_sub_id = d.sec_sub_id AND ts.cls_sec_id = d.cls_sec_id AND ts.status = 1 AND ts.tid = {$tid}",
                    'inner'
                );
            }

            $builder->orderBy('a.subject_name', 'ASC');
            $query = $builder->get();
            
            $rows = ($query !== false) ? $query->getResultArray() : [];

            $secSubIds = [];
            $maxByPaper = [];
            $examDateByPaper = [];
            $subShort = [];
            $seenSecSubIds = [];

            foreach ($rows as $r) {
                $sid = (int)$r['sec_sub_id'];
                if ($sid <= 0 || isset($seenSecSubIds[$sid])) {
                    continue;
                }
                $seenSecSubIds[$sid] = true;
                $secSubIds[] = $sid;
                $maxByPaper[$sid] = (int)($r['total_marks'] ?? 0);
                $examDateByPaper[$sid] = (string)($r['exam_date'] ?? '');
                $label = trim((string)($r['subject_short_name'] ?? ''));
                if ($label === '') {
                    $label = trim((string)($r['subject_name'] ?? ''));
                }
                $subShort[$sid] = $label !== '' ? $label : 'Sub';
            }

            return [$rows, $secSubIds, $maxByPaper, $examDateByPaper, $subShort];
            
        } catch (\Exception $e) {
            log_message('error', 'Error fetching datesheet papers: ' . $e->getMessage());
            return [[], [], [], [], []];
        }
    }

    /**
     * Load marks map
     */
    private function loadMarksMap(int $eid, int $clsSecId, array $studentIds, array $secSubIds): array
    {
        $map = [];
        if (empty($studentIds) || empty($secSubIds)) return $map;

        try {
            $builder = $this->db->table('subject_results');
            $builder->select('student_id, sec_sub_id, obtained_marks');
            $builder->where('eid', $eid);
            $builder->where('cls_sec_id', $clsSecId);
            $builder->whereIn('student_id', $studentIds);
            $builder->whereIn('sec_sub_id', $secSubIds);
            $query = $builder->get();
            
            if ($query !== false) {
                $rows = $query->getResultArray();
                foreach ($rows as $r) {
                    $sid = (int)$r['student_id'];
                    $ss = (int)$r['sec_sub_id'];
                    $map[$sid][$ss] = ($r['obtained_marks'] === null) ? null : (float)$r['obtained_marks'];
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error loading marks map: ' . $e->getMessage());
        }
        
        return $map;
    }

    /**
     * Load grading policy
     */
    private function loadGradingPolicy(int $systemId): array
    {
        if ($systemId <= 0) return [];

        try {
            $builder = $this->db->table('grading_policy gp');
            $builder->select('gp.mark_from, gp.mark_to, g.name, IFNULL(g.is_f,0) AS is_f');
            $builder->join('grades g', 'g.gid = gp.gid', 'left');
            $builder->where('gp.system_id', $systemId);
            $builder->orderBy('gp.mark_from', 'DESC');
            $query = $builder->get();
            
            if ($query === false) return [];

            $rows = $query->getResultArray();
            $out = [];
            foreach ($rows as $r) {
                $out[] = [
                    'from' => (float)$r['mark_from'],
                    'to' => (float)$r['mark_to'],
                    'name' => (string)$r['name'],
                    'is_f' => (int)$r['is_f'],
                ];
            }
            return $out;
            
        } catch (\Exception $e) {
            log_message('error', 'Error loading grading policy: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Grade for percentage
     */
    private function gradeForPercent(float $percent, array $policy): array
    {
        foreach ($policy as $gp) {
            if ($percent >= $gp['from'] && $percent <= $gp['to']) {
                return ['name' => $gp['name'], 'is_f' => (int)$gp['is_f']];
            }
        }
        return ['name' => '', 'is_f' => 0];
    }

    private function getGradeSummaryLabels(array $gradingPolicies, int $systemId): array
    {
        $labels = [];
        foreach ($gradingPolicies as $gp) {
            $name = trim((string) ($gp['name'] ?? ''));
            if ($name !== '' && !in_array($name, $labels, true)) {
                $labels[] = $name;
            }
        }
        if (!empty($labels)) {
            return $labels;
        }
        if ($systemId <= 0) {
            return [];
        }
        try {
            $rows = $this->db->table('grades')->select('name')->where('system_id', $systemId)->orderBy('gid', 'ASC')->get()->getResultArray();
            foreach ($rows as $r) {
                $name = trim((string) ($r['name'] ?? ''));
                if ($name !== '' && !in_array($name, $labels, true)) {
                    $labels[] = $name;
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error loading grades for summary: ' . $e->getMessage());
        }
        return $labels;
    }

    private function initGradeSummary(array $secSubIds, array $gradeLabels): array
    {
        $summary = [];
        foreach ($secSubIds as $ss) {
            $summary[$ss] = ['Absent' => 0];
            foreach ($gradeLabels as $label) {
                $summary[$ss][$label] = 0;
            }
        }
        return $summary;
    }

    private function resolvePaperGrade(?float $obtained, float $totalMarks, array $gradingPolicies): ?array
    {
        if ($obtained === null || $totalMarks <= 0) {
            return null;
        }
        $pct = (int) round(((float) $obtained / $totalMarks) * 100);
        $g = $this->gradeForPercent((float) $pct, $gradingPolicies);
        return ['pct' => $pct, 'name' => trim((string) ($g['name'] ?? ''))];
    }

    private function loadAbsentStudentsByPaper(array $secSubIds, array $examDateByPaper, array $studentIds): array
    {
        $absent = [];
        foreach ($secSubIds as $ss) {
            $absent[$ss] = [];
        }
        if (empty($studentIds)) {
            return $absent;
        }
        foreach ($secSubIds as $ss) {
            $examDate = trim((string) ($examDateByPaper[$ss] ?? ''));
            if ($examDate === '') {
                continue;
            }
            try {
                $rows = $this->db->table('attendance')->select('student_id, status')->where('date', $examDate)->whereIn('student_id', $studentIds)->get()->getResultArray();
                foreach ($rows as $r) {
                    if (strtoupper((string) ($r['status'] ?? '')) === 'A') {
                        $absent[$ss][(int) $r['student_id']] = true;
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Error loading absent students: ' . $e->getMessage());
            }
        }
        return $absent;
    }

    private function summaryRowClass(string $label): string
    {
        if ($label === 'Absent') {
            return 'absent';
        }
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $label), '-'));
        return 'summary-grade' . ($slug !== '' ? ' summary-grade-' . $slug : '');
    }

    private function summarizeBySubject(
        array $secSubIds,
        array $maxByPaper,
        array $marks,
        array $studentIds,
        array $examDateByPaper,
        array $gradingPolicies,
        array $gradeLabels
    ): array {
        $summary = $this->initGradeSummary($secSubIds, $gradeLabels);
        if (empty($studentIds)) {
            return $summary;
        }
        $absentByPaper = $this->loadAbsentStudentsByPaper($secSubIds, $examDateByPaper, $studentIds);
        foreach ($studentIds as $sid) {
            foreach ($secSubIds as $ss) {
                $ob = $marks[$sid][$ss] ?? null;
                $tm = (float) ($maxByPaper[$ss] ?? 0);
                if (!empty($absentByPaper[$ss][$sid]) && $ob === null) {
                    $summary[$ss]['Absent']++;
                    continue;
                }
                $resolved = $this->resolvePaperGrade($ob, $tm, $gradingPolicies);
                if ($resolved === null || $resolved['name'] === '') {
                    continue;
                }
                $gradeName = $resolved['name'];
                if (!array_key_exists($gradeName, $summary[$ss])) {
                    $summary[$ss][$gradeName] = 0;
                }
                $summary[$ss][$gradeName]++;
            }
        }
        return $summary;
    }

    /* ================================
     * Rendering Methods
     * ================================ */

    private function inlineStyles(): string
    {
        return <<<CSS
<style>
.srl-report-sheet {
  background: #fff;
}

.result-table {
  width: 100%;
  max-width: 100%;
  table-layout: fixed;
  border-collapse: collapse;
  border: 2px solid #1e293b;
  font-size: 12px;
  color: #111;
}

.result-table thead th {
  background: #1e3a5f;
  color: #fff;
  font-weight: 700;
  vertical-align: middle;
  padding: 8px 4px;
  border: 1px solid #334155;
  text-align: center;
}

.result-table thead th.col-num {
  width: 36px;
}

.result-table thead th.col-student {
  text-align: left;
  width: 168px;
}

.result-table thead th.subject-col {
  padding: 6px 3px;
  line-height: 1.2;
}

.subject-head-name {
  display: block;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.02em;
  word-break: break-word;
}

.subject-head-marks {
  display: block;
  margin-top: 3px;
  font-size: 10px;
  font-weight: 600;
  opacity: 0.9;
}

.result-head-title {
  display: block;
  font-size: 11px;
}

.result-head-marks {
  display: block;
  margin-top: 2px;
  font-size: 10px;
  font-weight: 600;
  opacity: 0.9;
}

.result-table tbody td {
  border: 1px solid #cbd5e1;
  padding: 4px 3px;
  vertical-align: middle;
}

.result-table tbody td.col-num {
  text-align: center;
  font-weight: 600;
  background: #f8fafc;
}

.result-table tbody td.col-student {
  text-align: left;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 168px;
}

.result-table .student-name {
  display: block;
  font-weight: 700;
}

.result-table .student-reg {
  display: block;
  font-size: 10px;
  color: #64748b;
  margin-top: 1px;
}

.result-table tbody td.mark-cell {
  text-align: center;
  font-size: 11px;
}

.result-table .mark-score {
  font-weight: 700;
  line-height: 1.2;
}

.result-table .mark-grade {
  font-size: 9px;
  color: #475569;
  line-height: 1.15;
  margin-top: 2px;
}

.result-table tbody tr.student-row:nth-child(even) td:not(.summary-title) {
  background: #fafbfc;
}

.result-table tbody tr.summary-section td.summary-title {
  background: #e2e8f0;
  color: #0f172a;
  font-weight: 700;
  text-align: left;
  padding: 6px 8px;
  border-top: 2px solid #1e293b;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.result-table tbody tr.summary-row td {
  background: #f1f5f9;
  font-weight: 700;
  font-size: 11px;
  padding: 4px 3px;
}

.result-table tbody tr.summary-row.absent td {
  background: #e2e8f0;
}

.result-table tbody td.col-result {
  text-align: center;
  background: #f8fafc;
  font-size: 11px;
}

@media print {
  @page { size: A4 portrait; margin: 8mm 6mm; }

  .result-table {
    font-size: 7.5pt !important;
    border-width: 1pt !important;
  }

  .result-table thead th {
    background: #1e3a5f !important;
    color: #fff !important;
    padding: 4px 2px !important;
    font-size: 7pt !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .result-table thead th.col-num { width: 5mm !important; }
  .result-table thead th.col-student { width: 34mm !important; }
  .subject-head-name { font-size: 6.5pt !important; }
  .subject-head-marks { font-size: 6pt !important; }

  .result-table tbody td {
    padding: 2px 1px !important;
    font-size: 7pt !important;
  }

  .result-table .mark-grade { font-size: 6pt !important; }

  .result-table thead { display: table-header-group; }

  .result-table tbody tr.summary-section,
  .result-table tbody tr.summary-row {
    break-inside: avoid;
    page-break-inside: avoid;
  }

  tr.summary-row td {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>
CSS;
    }

    private function renderStudentTable(
        array $students,
        array $secSubIds,
        array $subShort,
        array $maxByPaper,
        array $marks,
        array $gradingPolicies,
        array $summary,
        array $gradeSummaryLabels = []
    ): string {
        $totalMax = array_sum(array_map(static fn($v) => (int)$v, $maxByPaper));

        $colSpan = 2 + count($secSubIds) + 1;

        $overallSummary = ['Absent' => 0];
        foreach ($gradeSummaryLabels as $label) {
            $overallSummary[$label] = 0;
        }

        $html = '<table class="table table-bordered result-table">';
        $html .= '<thead><tr>';
        $html .= '<th class="col-num">#</th>';
        $html .= '<th class="col-student">Student</th>';

        foreach ($secSubIds as $ss) {
            $nm = esc($subShort[$ss] ?? 'Sub');
            $tm = (int) ($maxByPaper[$ss] ?? 0);
            $html .= "<th class='subject-col' title='Max marks: {$tm}'>"
                . "<span class='subject-head-name'>{$nm}</span>"
                . "<span class='subject-head-marks'>({$tm})</span>"
                . '</th>';
        }

        $html .= '<th class="col-result">'
            . '<span class="result-head-title">Total</span>'
            . '<span class="result-head-marks">Max ' . (int) $totalMax . '</span>'
            . '</th>';
        $html .= '</tr></thead><tbody>';

        // Student rows
        $i = 1;
        foreach ($students as $s) {
            $sid = (int)$s['student_id'];
            $fullName = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''));
            $sum = 0.0;
            $hasNull = false;

            $html .= '<tr class="student-row">';
            $html .= '<td class="col-num">' . ($i++) . '</td>';
            $html .= '<td class="col-student"><span class="student-name">' . esc($fullName) . '</span>'
                . '<span class="student-reg">' . esc($s['reg_no'] ?? '') . '</span></td>';

            foreach ($secSubIds as $ss) {
                $ob = $marks[$sid][$ss] ?? null;
                $tm = (float)($maxByPaper[$ss] ?? 0);

                $resolved = $this->resolvePaperGrade($ob, $tm, $gradingPolicies);
                $html .= '<td class="mark-cell">';
                if ($ob !== null) {
                    $sum += (float) $ob;
                    $show = rtrim(rtrim(number_format((float) $ob, 2), '0'), '.');
                    $html .= '<div class="mark-score">' . $show . '</div>';
                    if ($resolved !== null && $resolved['name'] !== '') {
                        $html .= '<div class="mark-grade">' . esc($resolved['name'])
                            . ($resolved['pct'] > 0 ? ' ' . $resolved['pct'] . '%' : '') . '</div>';
                    }
                } else {
                    $hasNull = true;
                }
                $html .= '</td>';
            }

            $pct = $totalMax > 0 ? round(($sum / $totalMax) * 100) : 0;
            $g = $hasNull ? ['name' => ''] : $this->gradeForPercent((float)$pct, $gradingPolicies);

            if ($hasNull) {
                $overallSummary['Absent']++;
            } else {
                $overallGradeName = trim((string) ($g['name'] ?? ''));
                if ($overallGradeName !== '') {
                    if (! array_key_exists($overallGradeName, $overallSummary)) {
                        $overallSummary[$overallGradeName] = 0;
                    }
                    $overallSummary[$overallGradeName]++;
                }
            }

            $html .= '<td class="col-result">';
            $html .= '<div class="mark-score"><b>' . rtrim(rtrim(number_format($sum, 2), '0'), '.') . '</b></div>';
            $html .= '<div class="mark-grade">' . $pct . '%</div>';
            if (!$hasNull && ($g['name'] ?? '') !== '') {
                $html .= '<div class="mark-grade">' . esc($g['name']) . '</div>';
            }
            $html .= '</td>';
            $html .= '</tr>';
        }

        $html .= '<tr class="summary-section"><td colspan="' . $colSpan . '" class="summary-title">Grade summary</td></tr>';
        $summaryRowLabels = $gradeSummaryLabels;
        $summaryRowLabels[] = 'Absent';
        foreach ($summaryRowLabels as $label) {
            $rowClass = $this->summaryRowClass($label);
            $html .= '<tr class="summary-row ' . esc($rowClass) . '">';
            $html .= '<td></td><td><strong>' . esc($label) . '</strong></td>';
            foreach ($secSubIds as $ss) {
                $cnt = (int) ($summary[$ss][$label] ?? 0);
                $html .= '<td class="mark-cell"><strong>' . $cnt . '</strong></td>';
            }
            $overallCnt = (int) ($overallSummary[$label] ?? 0);
            $html .= '<td class="col-result mark-cell"><strong>' . $overallCnt . '</strong></td></tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    private function requireResultListAccess(): void
    {
        if (!$this->isCurrentTeacher()) {
            check_permission('admin-add-students-result');
        }
    }

    private function getCurrentTeacherSubjectSections(): array
    {
        $teacherId = (int) $this->session->get('member_userid');
        $campusId = (int) $this->session->get('member_campusid');
        if ($teacherId <= 0 || $campusId <= 0) {
            return [];
        }
        return $this->db->table('teacher_subjects ts')->distinct()
            ->select('cs.cls_sec_id, CONCAT(COALESCE(NULLIF(c.class_short_name, ""), c.class_name), " (", COALESCE(NULLIF(s.short_name, ""), s.section_name), ")") AS sectionclassname')
            ->join('class_section cs', 'cs.cls_sec_id = ts.cls_sec_id', 'inner')
            ->join('classes c', 'c.class_id = cs.class_id', 'inner')
            ->join('sections s', 's.section_id = cs.section_id', 'inner')
            ->where('ts.tid', $teacherId)->where('ts.status', 1)->where('cs.status', 1)->where('cs.campus_id', $campusId)
            ->orderBy('c.class_id', 'ASC')->get()->getResultArray();
    }

    private function teacherHasSubjectInSection(int $teacherId, int $clsSecId): bool
    {
        return $teacherId > 0 && $clsSecId > 0
            && (bool) $this->db->table('teacher_subjects')->where(['tid' => $teacherId, 'cls_sec_id' => $clsSecId, 'status' => 1])->countAllResults();
    }

    private function isCurrentTeacher(): bool
    {
        return $this->currentUserHasRoleNameId(5);
    }

    private function currentUserHasRoleNameId(int $roleNameId): bool
    {
        $userId = (int) $this->session->get('member_userid');
        $campusId = (int) $this->session->get('member_campusid');
        if ($userId <= 0 || $roleNameId <= 0) {
            return false;
        }
        $planId = $this->getCampusPlanId($campusId);
        if ($this->db->table('user_roles ur')->join('roles r', 'r.id = ur.roleID' . ($planId > 0 ? ' AND r.plan_id = ' . $planId : ''), 'inner')
            ->where('ur.userID', $userId)->where('r.role_name_id', $roleNameId)->countAllResults() > 0) {
            return true;
        }
        return (bool) $this->db->table('user_roles ur')->join('roles r', 'r.role_name_id = ur.roleID' . ($planId > 0 ? ' AND r.plan_id = ' . $planId : ''), 'inner')
            ->where('ur.userID', $userId)->where('r.role_name_id', $roleNameId)->countAllResults();
    }

    private function getCampusPlanId(int $campusId): int
    {
        if ($campusId <= 0) {
            return 0;
        }
        $row = $this->db->table('campus_bills')->select('plan_id')->where(['status' => 1, 'campus_id' => $campusId])
            ->orderBy('campus_expiry', 'DESC')->get()->getRow();
        return (int) ($row->plan_id ?? 0);
    }
}