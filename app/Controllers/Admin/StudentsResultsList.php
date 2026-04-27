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
        
        // Get current session from global session (same as Dashboard)
        $this->campusId = (int) $this->session->get('member_campusid');
        $this->sessionId = (int) $this->session->get('member_sessionid');
    }

    public function index()
    {
        check_permission('admin-add-students-result');

        $campusId  = $this->campusId;
        $sessionId = $this->sessionId;

        $data = [
            'sessionData'        => ['campusid' => $campusId, 'sessionid' => $sessionId],
            'infostudents'       => $this->db->table('students')->get()->getResult(),
            'sectionsclassinfo'  =>
                in_array(5, (array) currentUserRoles(), true) ? teacherSubjectSections() : userClassSections(),
            'campusinfo'         => $this->db->table('campus')->where('campus_id', $campusId)->get()->getResult(),
            'examinfo'           => $this->db->table('exam')->where(['campus_id' => $campusId, 'session_id' => $sessionId])->get()->getResult(),
            'academic_session'   => $this->db->table('academic_session')->where('session_id', $sessionId)->get()->getResult(),
            'subjectinfo'        => $this->db->table('allsubject')->get()->getResult(),
        ];

        return view('admin/students_results_list', $data);
    }

/**
 * Fetch students based on session type
 */
private function fetchClassStudents(int $clsSecId, int $sessionId, bool $useCurrentStudentsOnly = false): array
{
    if ($useCurrentStudentsOnly) {
        // CASE 2: Current active students only (status=1 in students table)
        // For current session - students must be active now
        return $this->db->table('students s')
            ->select('s.student_id, s.first_name, s.last_name, s.reg_no')
            ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . $sessionId, 'inner')
            ->where('sc.cls_sec_id', $clsSecId)
            ->where('s.status', '1')
            ->groupBy('s.student_id')
            ->orderBy('s.first_name', 'ASC')
            ->get()
            ->getResultArray();
    } else {
        // CASE 1: Session-based students (historical)
        // For historical sessions - don't check student.status, 
        // and don't require student_class.status = 1 (could be 0 for past sessions)
        // Just need the student to have been enrolled in that class/section during that session
        return $this->db->table('student_class sc')
            ->select('s.student_id, s.first_name, s.last_name, s.reg_no')
            ->join('students s', 's.student_id = sc.student_id', 'left')
            ->where('sc.cls_sec_id', $clsSecId)
            ->where('sc.session_id', $sessionId)
            // Remove the status condition for historical - just check that record exists
            // ->where('sc.status', 1)  // COMMENT THIS OUT - it's causing the issue
            ->orderBy('s.first_name', 'ASC')
            ->get()
            ->getResultArray();
    }
}
    /**
     * Fetch students based on session type
     */
  
    /**
     * AJAX: Get students and results
     */
    public function get_students()
    {
        $r = $this->request;
        $eid = (int) $r->getPost('eid');
        $sessionId = (int) $r->getPost('session_id'); // From hidden input
        $campusId = (int) $r->getPost('campus_id');
        $clsSecId = (int) $r->getPost('cls_sec_id');
        $studentType = $r->getPost('student_type');

        if ($clsSecId === 0) {
            return $this->response->setBody("<div class='alert alert-warning'>Select Class Section</div>");
        }
        if (!$eid) {
            return $this->response->setBody("<div class='alert alert-warning'>Select an exam.</div>");
        }

        // Determine which student fetching logic to use
        $useCurrentStudentsOnly = ($studentType === 'current');
        
        // Fetch students based on selected type
        $students = $this->fetchClassStudents($clsSecId, $sessionId, $useCurrentStudentsOnly);
        
        if (empty($students)) {
            $message = $useCurrentStudentsOnly 
                ? "No active students found for this class/section."
                : "No students found for this class/section in selected session.";
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

        // Build summary
        $summary = $this->summarizeBySubject(
            $secSubIds,
            $maxByPaper,
            $marks,
            $studentIds,
            $examDateByPaper,
            $gradingPolicies
        );

        // Build table
        $styles = $this->inlineStyles();
        $tableHtml = $this->renderStudentTable($students, $secSubIds, $subShort, $maxByPaper, $marks, $gradingPolicies, $summary);

        return $this->response->setBody($styles . $tableHtml);
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
            
            // Method 2: Get current active session for this campus
            $builder = $this->db->table('academic_session');
            $builder->select('session_id, session_name, start_date, end_date, status');
            $builder->where('campus_id', $campusId);
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
            $builder->where('campus_id', $campusId);
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
            $builder->select('d.sec_sub_id, d.total_marks, d.exam_date, a.subject_short_name');
            $builder->join('section_subjects ss', 'ss.sec_sub_id = d.sec_sub_id', 'left');
            $builder->join('allsubject a', 'a.sid = ss.subject_id', 'left');
            $builder->where('d.eid', $eid);
            $builder->where('d.cls_sec_id', $clsSecId);
            $builder->where('IFNULL(d.total_marks,0) >', 0);
            $builder->where('IFNULL(d.enable,1) =', 1);

            // Teacher scope
            if (in_array(5, (array) currentUserRoles(), true)) {
                $tid = (int) $this->session->get('member_userid');
                $builder->join('teacher_subjects ts',
                    "ts.sec_sub_id = d.sec_sub_id AND ts.cls_sec_id = d.cls_sec_id AND ts.status = 1 AND ts.tid = {$tid}",
                    'inner'
                );
            }

            $builder->orderBy('d.sec_sub_id', 'ASC');
            $query = $builder->get();
            
            $rows = ($query !== false) ? $query->getResultArray() : [];

            $secSubIds = [];
            $maxByPaper = [];
            $examDateByPaper = [];
            $subShort = [];

            foreach ($rows as $r) {
                $sid = (int)$r['sec_sub_id'];
                $secSubIds[] = $sid;
                $maxByPaper[$sid] = (int)($r['total_marks'] ?? 0);
                $examDateByPaper[$sid] = (string)($r['exam_date'] ?? '');
                $subShort[$sid] = (string)($r['subject_short_name'] ?: 'Sub');
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

    /**
     * To canonical bucket
     */
    private function toCanonicalBucket(string $name, int $isF): string
    {
        if ($isF === 1) return 'F';
        $first = strtoupper(substr(trim($name), 0, 1));
        if (in_array($first, ['A', 'B', 'C', 'D'], true)) return $first;
        if (in_array($first, ['F'], true)) return 'F';
        return 'D';
    }

    /**
     * Summarize by subject
     */
    private function summarizeBySubject(
        array $secSubIds,
        array $maxByPaper,
        array $marks,
        array $studentIds,
        array $examDateByPaper,
        array $gradingPolicies
    ): array {
        $summary = [];
        foreach ($secSubIds as $ss) {
            $summary[$ss] = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0, 'Absent' => 0, 'Entered' => 0];
        }

        // Count absentees
        foreach ($secSubIds as $ss) {
            $examDate = $examDateByPaper[$ss] ?? '';
            if (!$examDate || empty($studentIds)) continue;

            try {
                $builder = $this->db->table('attendance');
                $builder->select('student_id, status');
                $builder->where('date', $examDate);
                $builder->whereIn('student_id', $studentIds);
                $query = $builder->get();
                
                if ($query !== false) {
                    $rows = $query->getResultArray();
                    $abs = 0;
                    foreach ($rows as $r) {
                        if (strtoupper((string)$r['status']) === 'A') $abs++;
                    }
                    $summary[$ss]['Absent'] = $abs;
                }
            } catch (\Exception $e) {
                log_message('error', 'Error counting absentees: ' . $e->getMessage());
            }
        }

        // Count grades
        foreach ($studentIds as $sid) {
            foreach ($secSubIds as $ss) {
                $ob = $marks[$sid][$ss] ?? null;
                $tm = (float)($maxByPaper[$ss] ?? 0);
                if ($ob === null || $tm <= 0) continue;

                $pct = ($ob / $tm) * 100.0;
                $g = $this->gradeForPercent($pct, $gradingPolicies);
                $bucket = $this->toCanonicalBucket($g['name'], $g['is_f']);
                $summary[$ss]['Entered']++;
                if (!isset($summary[$ss][$bucket])) $summary[$ss][$bucket] = 0;
                $summary[$ss][$bucket]++;
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
.result-table {
  width: 100%;
  max-width: 100%;
  table-layout: fixed;
  border-collapse: collapse;
  border: 1px solid #000;
  position: relative;
}

.result-table thead th.col-num {
  width: 3.5rem;
  min-width: 3.5rem;
}

.result-table thead th.col-student,
.result-table tbody td.col-student {
  width: 180px;
  min-width: 180px;
  max-width: 180px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.result-table thead th.subject-col {
  width: 62px;
  min-width: 62px;
  height: 80px;
  padding: 0;
  text-align: center;
  vertical-align: top;
  position: relative;
  overflow: visible;
  border: 1px solid #000;
}

.vertical-header {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) rotate(-90deg);
  transform-origin: center center;
  white-space: nowrap;
  font-size: 11px;
  line-height: 1;
  padding: 0;
}

.vertical-header small {
  display: block;
  font-size: 10px;
  margin-top: 4px;
}

tr.summary-row th,
tr.summary-row td {
  background: #f8fafc;
  font-weight: 700;
}
tr.summary-row.grade-A td { background: #eef2ff; }
tr.summary-row.grade-B td { background: #ecfeff; }
tr.summary-row.grade-C td { background: #ecfccb; }
tr.summary-row.grade-D td { background: #fff7ed; }
tr.summary-row.grade-F td { background: #fee2e2; }
tr.summary-row.absent td  { background: #f3f4f6; }

.result-table tbody td.text-center {
  vertical-align: middle;
  font-size: 12px;
}
.result-table td.text-center div {
  line-height: 1.1;
}
.result-table th.text-center {
  vertical-align: middle;
}

.result-table th,
.result-table td {
  border: 1px solid #000;
}

@media print {
  .result-table {
    border-collapse: separate !important;
    border-spacing: 0 !important;
  }
  .result-table th,
  .result-table td {
    border: 1px solid #000 !important;
  }
  .result-table thead tr:first-child th {
    border-top: 2px solid #000 !important;
    border-bottom: 1px solid #000 !important;
  }
  .result-table thead th.subject-col {
    height: 140px !important;
    vertical-align: middle !important;
  }
  .vertical-header {
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) rotate(-90deg) !important;
  }
  .result-table thead {
    display: table-header-group;
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
        array $summary
    ): string {
        $totalMax = array_sum(array_map(static fn($v) => (int)$v, $maxByPaper));

        $html = '<table class="table table-bordered table-striped result-table">';
        $html .= '<thead><tr>';
        $html .= '<th class="col-num">#</th>';
        $html .= '<th class="col-student"></th>';

        foreach ($secSubIds as $ss) {
            $nm = esc($subShort[$ss] ?? 'Sub');
            $tm = (int)($maxByPaper[$ss] ?? 0);
            $html .= "<th class='text-center subject-col' title='Total: {$tm}'>"
                   . "<span class='vertical-header'>{$nm}<small>{$tm}</small></span>"
                   . "</th>";
        }

        $html .= '<th class="text-center" style="width:90px">Result'
              . '<div><small>Max: ' . (int)$totalMax . '</small></div>'
              . '</th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';

        // Summary rows
        $gradeRows = [
            'A' => 'grade-A',
            'B' => 'grade-B',
            'C' => 'grade-C',
            'D' => 'grade-D',
            'F' => 'grade-F',
            'Absent' => 'absent',
        ];
        
        foreach ($gradeRows as $label => $class) {
            $html .= '<tr class="summary-row ' . $class . '">';
            $html .= '<td></td>';
            $html .= '<td><strong>' . esc($label) . '</strong></td>';
            foreach ($secSubIds as $ss) {
                $cnt = (int)($summary[$ss][$label] ?? 0);
                $html .= '<td class="text-center"><strong>' . $cnt . '</strong></td>';
            }
            $html .= '<td></td>';
            $html .= '</tr>';
        }

        // Student rows
        $i = 1;
        foreach ($students as $s) {
            $sid = (int)$s['student_id'];
            $fullName = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''));
            $sum = 0.0;
            $hasNull = false;

            $html .= '<tr>';
            $html .= '<td>' . ($i++) . '</td>';
            $html .= '<td class="col-student"><b>' . esc($fullName) . '</b><div class="text-muted small">' . esc($s['reg_no'] ?? '') . '</div></td>';

            foreach ($secSubIds as $ss) {
                $ob = $marks[$sid][$ss] ?? null;
                $tm = (float)($maxByPaper[$ss] ?? 0);

                $html .= '<td class="text-center">';
                if ($ob !== null) {
                    $sum += (float)$ob;
                    $show = rtrim(rtrim(number_format((float)$ob, 2), '0'), '.');
                    $html .= $show;
                    if ($tm > 0) {
                        $pct = round(((float)$ob / $tm) * 100);
                        $g = $this->gradeForPercent((float)$pct, $gradingPolicies);
                        $html .= "<div><small>" . esc($g['name']) . ($pct > 0 ? " {$pct}%" : '') . "</small></div>";
                    }
                } else {
                    $hasNull = true;
                }
                $html .= '</td>';
            }

            $pct = $totalMax > 0 ? round(($sum / $totalMax) * 100) : 0;
            $g = $hasNull ? ['name' => ''] : $this->gradeForPercent((float)$pct, $gradingPolicies);

            $html .= '<td class="text-center">';
            $html .= '<div><b>' . rtrim(rtrim(number_format($sum, 2), '0'), '.') . '</div>';
            $html .= '<div><small>' . $pct . '%</small></div>';
            $html .= '<div><small>' . esc($g['name']) . '</small></div>';
            $html .= '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }
}