<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\StudentsModel;
use CodeIgniter\HTTP\ResponseInterface as CIResponse; 
use DateTime;

class StudentsBulkFeeInfo extends BaseController
{
    protected $db;
    protected $session;
    protected $students;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'text']);
        check_permission('admin-students');
        $this->students = new StudentsModel();
    }

    /**
     * Generate invoice number like "25-INV-00001" for the given fee month (YYYY-MM).
     */


// --- AJAX: search students by name (campus + active + cls_sec from student_class by session) ---
public function searchByName()
{
    $q          = trim((string) $this->request->getGet('q'));
    $cls_sec_id = (int) $this->request->getGet('cls_sec_id');   // optional narrow
    $limit      = (int) ($this->request->getGet('limit') ?: 20);
    $limit      = max(1, min($limit, 50));

    if ($q === '') {
        return $this->response->setJSON(['results' => []]);
    }

    // Campus from your session (adjust key if different)
    $campus_id  = (int) ($this->session->get('member_campusid') ?: 0);

    // Current academic session id (GET overrides; then session fallbacks)
    $session_id = (int) (
        $this->request->getGet('session_id')
        ?: $this->session->get('member_sessionid')
        ?: $this->session->get('session_id')
        ?: 0
    );

    // Base: active students in this campus
    $builder = $this->db->table('students s')
        ->distinct()
        ->where('s.status', 1);

    if ($campus_id > 0) {
        $builder->where('s.campus_id', $campus_id);
    }

    // Prefer cls_sec_id from student_class for the given session
    if ($session_id > 0) {
        $builder->select('s.student_id, s.first_name, s.last_name, s.parent_id, COALESCE(sc.cls_sec_id, s.cls_sec_id) AS cls_sec_id', false)
                ->join(
                    'student_class sc',
                    'sc.student_id = s.student_id AND sc.session_id = ' . (int)$session_id . ' AND sc.status = 1',
                    'left'
                );
        if ($cls_sec_id > 0) {
            $builder->where('sc.cls_sec_id', $cls_sec_id);
        }
    } else {
        // Fallback: use students.cls_sec_id when no session is known
        $builder->select('s.student_id, s.first_name, s.last_name, s.parent_id, s.cls_sec_id');
        if ($cls_sec_id > 0) {
            $builder->where('s.cls_sec_id', $cls_sec_id);
        }
    }

    // Name match (first and/or last)
    $builder->groupStart()
                ->like('s.first_name', $q)
                ->orLike('s.last_name',  $q)
            ->groupEnd();

    $rows = $builder->orderBy('s.first_name', 'ASC')
                    ->orderBy('s.last_name',  'ASC')
                    ->limit($limit)
                    ->get()->getResult();

    $results = [];
    foreach ($rows as $r) {
        $name = trim(($r->first_name ?? '') . ' ' . ($r->last_name ?? '')) ?: (string) ($r->first_name ?? 'Student');
        $results[] = [
            'id'         => (int) $r->student_id,
            'text'       => $name,
            'parent_id'  => (int) $r->parent_id,
            'cls_sec_id' => (int) $r->cls_sec_id, // from sc if session_id provided
        ];
    }

    return $this->response->setJSON(['results' => $results]);
}


// --- AJAX: list students by parent (campus + active; cls_sec from student_class for the session) ---
public function byParent()
{
    $parent_id  = (int) $this->request->getPost('parent_id');
    $cls_sec_id = (int) $this->request->getPost('cls_sec_id'); // optional narrow
    $campus_id  = (int) ($this->request->getPost('campus_id') ?: $this->session->get('member_campusid'));

    // Current academic session id (POST overrides; then session fallbacks)
    $session_id = (int) (
        $this->request->getPost('session_id')
        ?: $this->session->get('member_sessionid')
        ?: $this->session->get('session_id')
        ?: 0
    );

    if (!$parent_id) {
        return $this->response->setStatusCode(400)->setBody('<div class="alert alert-warning">Missing parent_id.</div>');
    }

    $builder = $this->db->table('students s')
        ->distinct()
        ->where('s.parent_id', $parent_id)
        ->where('s.status', 1);

    if ($campus_id > 0) {
        $builder->where('s.campus_id', $campus_id);
    }

    if ($session_id > 0) {
        $builder->select('s.student_id, s.first_name, s.last_name, s.parent_id, COALESCE(sc.cls_sec_id, s.cls_sec_id) AS cls_sec_id', false)
                ->join(
                    'student_class sc',
                    'sc.student_id = s.student_id AND sc.session_id = ' . (int)$session_id . ' AND sc.status = 1',
                    'left'
                );
        if ($cls_sec_id > 0) {
            $builder->where('sc.cls_sec_id', $cls_sec_id);
        }
    } else {
        $builder->select('s.student_id, s.first_name, s.last_name, s.parent_id, s.cls_sec_id');
        if ($cls_sec_id > 0) {
            $builder->where('s.cls_sec_id', $cls_sec_id);
        }
    }

    $students = $builder
        ->orderBy('s.first_name', 'ASC')
        ->orderBy('s.last_name',  'ASC')
        ->get()->getResult();

    if (empty($students)) {
        return $this->response->setBody('<div class="alert alert-info">No students found for this parent.</div>');
    }

    // Simple table (adapt markup to your bulk form if needed)
    $html = '<div class="table-responsive"><table class="table table-sm table-striped mb-0">
               <thead><tr>
                 <th>#</th><th>Student</th><th>Parent ID</th><th>Class (cls_sec_id)</th>
               </tr></thead><tbody>';
    $i = 1;
    foreach ($students as $s) {
        $name = trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? '')) ?: esc($s->first_name ?? 'Student');
        $html .= '<tr>
                    <td>'.($i++).'</td>
                    <td>'.esc($name).'</td>
                    <td>'.(int)$s->parent_id.'</td>
                    <td>'.(int)$s->cls_sec_id.'</td>
                  </tr>';
    }
    $html .= '</tbody></table></div>';

    return $this->response->setBody($html);
}


    private function generateInvoiceNumber($fee_month)
    {
        $db = \Config\Database::connect();

        // Validate fee_month format (YYYY-MM)
        if (empty($fee_month) || !preg_match('/^\d{4}-\d{2}$/', $fee_month)) {
            throw new \InvalidArgumentException('Invalid fee month format');
        }

        try {
            $feeDate = DateTime::createFromFormat('Y-m', $fee_month);
            if (!$feeDate) {
                throw new \RuntimeException('Invalid fee_month format: ' . $fee_month);
            }
            $yr = $feeDate->format('y'); // e.g. "25" for 2025

            // Find the highest existing invoice number for this year
            $lastInvoice = $db->table('invoices')
                ->select('invoice_no')
                ->like('invoice_no', $yr.'-INV-', 'after')
                ->orderBy('invoice_no', 'DESC')
                ->get()
                ->getRow();

            if ($lastInvoice) {
                $parts = explode('-', $lastInvoice->invoice_no);
                $lastNumber = (int)end($parts);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }
            $invoice_no = $yr . '-INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            return $invoice_no;
        } catch (\Exception $e) {
            log_message('error', 'Invoice number generation failed: ' . $e->getMessage());
            throw new \RuntimeException('Failed to generate invoice number: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $campus_id   = $this->session->get('member_campusid');
        $currentrole = currentUserRoles();

        $data = [
            'sectionsclassinfo' => in_array(5, $currentrole) ? teacherSubjectSections() : $this->userClassSections(),
            'campus_info'       => $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow(),
            'campus_flags'      => $this->getCampusFlags($campus_id),
        ];
        return view('admin/students_bulk_fee_info', $data);
    }

    protected function userClassSections()
    {
        return $this->db->table('class_section cs')
            ->select('cs.cls_sec_id, cs.section_id, CONCAT(c.class_name, " (", s.section_name, ")") as sectionclassname')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->where('cs.status', 1)
            ->where('cs.campus_id', $this->session->get('member_campusid'))
            ->get()
            ->getResultArray();
    }

    protected function getCampusFlags($campus_id)
    {
        return $this->db->table('campus')
            ->select('daycare_flag, boarding_flag')
            ->where('campus_id', $campus_id)
            ->get()
            ->getRow();
    }

    /**
     * Build the tbody rows (HTML) for the selected class and months.
     * Reads POST:
     *  - cls_sec_id
     *  - months_json: JSON array of { col: 'month_prev|month_curr|month_next', key:'YYYY-MM', label:'Month YYYY' }
     *  - ref_month (optional)
     */

    protected function resolveSystemId(): int
{
    $sid = (int) ($this->session->get('member_systemid') ?? 0);
    if ($sid > 0) return $sid;

    $campusId = (int) $this->session->get('member_campusid');
    if ($campusId) {
        $row = $this->db->table('campus')->select('system_id')->where('campus_id', $campusId)->get()->getRow();
        if ($row && (int)$row->system_id > 0) {
            return (int) $row->system_id;
        }
    }
    return 1;
}


public function data()
{
    $campusid  = (int) $this->session->get('member_campusid');
    $sessionid = (int) $this->session->get('member_sessionid');
    $systemId  = $this->resolveSystemId();

    // Fee-only view: only class/section filter
    $cls_sec_id = trim((string) $this->request->getPost('cls_sec_id'));

    // Months parsing (same contract as the new view)
    $monthsJson     = $this->request->getPost('months_json');
    $selectedMonths = [];
    if ($monthsJson) {
        $tmp = json_decode($monthsJson, true);
        if (is_array($tmp)) {
            foreach ($tmp as $m) {
                if (!empty($m['col']) && !empty($m['key']) && preg_match('/^\d{4}-\d{2}$/', $m['key'])) {
                    if (in_array($m['col'], ['month_prev','month_curr','month_next'], true)) {
                        $selectedMonths[$m['col']] = [
                            'key'   => $m['key'],
                            'label' => (string)($m['label'] ?? $m['key']),
                        ];
                    }
                }
            }
        }
    }
    if (empty($selectedMonths)) {
        $ref = new \DateTime(date('Y-m-01'));
        $selectedMonths = [
            'month_prev' => ['key' => (clone $ref)->modify('-1 month')->format('Y-m'), 'label' => (clone $ref)->modify('-1 month')->format('F Y')],
            'month_curr' => ['key' => date('Y-m'), 'label' => date('F Y')],
            'month_next' => ['key' => (clone $ref)->modify('+1 month')->format('Y-m'), 'label' => (clone $ref)->modify('+1 month')->format('F Y')],
        ];
    }

    // Require a class/section selection (fee-only screen drives by class)
    if ($cls_sec_id === '') {
        return $this->response->setBody(
            '<tr><td colspan="8" class="text-center text-muted">Select a class to view students…</td></tr>'
        );
    }

    // Query (no parents join, fee-only)
    $qb = $this->db->table('student_class sc')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
        ->join('students s', 's.student_id = sc.student_id')
        ->where('sc.session_id', $sessionid)
        ->where('s.campus_id', $campusid)
        ->where('s.status', 1)
        ->where('sc.cls_sec_id', (int)$cls_sec_id)
        ->select("
            s.student_id,
            s.first_name,
            s.last_name,
            s.fee_plan,
            s.discounted_amount,        -- discount value stored on student row (if you use it)
            cs.class_id AS cs_class_id
        ")
        ->orderBy('s.first_name', 'ASC')
        ->orderBy('s.last_name',  'ASC');

    $rows = $qb->get()->getResult();
    if (!$rows) {
        return $this->response->setBody(
            '<tr><td colspan="8" class="text-center text-info">No students found for this class/section.</td></tr>'
        );
    }

    // Fee helpers
    $monthlyFeeTypeId = $this->getMonthlyFeeTypeId($systemId);

    $readMonth = function (int $studentId, ?string $yyyymm, float $classFee) use ($monthlyFeeTypeId) {
        if (!$yyyymm || !$monthlyFeeTypeId) {
            return ['net' => 0.0, 'amount' => $classFee, 'has' => false];
        }
        $agg = $this->db->table('fee_chalan')
            ->select('COUNT(*) as cnt, COALESCE(SUM(amount),0) as amt, COALESCE(SUM(discount),0) as disc')
            ->where([
                'student_id'  => $studentId,
                'fee_month'   => $yyyymm,
                'status'      => 'unpaid',
                'fee_type_id' => $monthlyFeeTypeId,
            ])->get()->getRow();

        $cnt  = (int)($agg->cnt ?? 0);
        $amt  = (float)($agg->amt ?? 0);
        $disc = (float)($agg->disc ?? 0);

        if ($cnt > 0) {
            $net = max(0.0, $amt - $disc);
            return ['net' => $net, 'amount' => $amt, 'has' => true];
        }
        return ['net' => 0.0, 'amount' => $classFee, 'has' => false];
    };

    $tbodyHtml = '';
    foreach ($rows as $r) {
        $studentId = (int)$r->student_id;
        $classId   = (int)($r->cs_class_id ?? 0);

        // Class fee for this student's class
        $classFee = $classId
            ? $this->getClassFee($classId, $sessionid, $campusid, $systemId)
            : 0.0;

        // If you keep "discounted_amount" on student as a discount, derive student_fee from it.
        $discount    = (float)($r->discounted_amount ?? 0.0);
        $studentFee  = max(0.0, $classFee - $discount);

        // Month nets for whichever months user selected
        $pKey = $selectedMonths['month_prev']['key'] ?? null;
        $cKey = $selectedMonths['month_curr']['key'] ?? null;
        $nKey = $selectedMonths['month_next']['key'] ?? null;

        $PM = $readMonth($studentId, $pKey, $classFee);
        $CM = $readMonth($studentId, $cKey, $classFee);
        $NM = $readMonth($studentId, $nKey, $classFee);

        // Render row (fee-only partial)
        $tbodyHtml .= view('admin/partials/student_bulk_fee_row', [
            'student_id'   => $studentId,
            'first_name'   => (string)($r->first_name ?? ''),
            'last_name'    => (string)($r->last_name ?? ''),

            // editable fee fields
            'fee_plan'     => isset($r->fee_plan) ? (int)$r->fee_plan : 0,
            'student_fee'  => $studentFee,  // prefill editable box

            // context/hidden
            'class_fee'    => $classFee,
            'class_id'     => $classId,
            'session_id'   => $sessionid,
            'campus_id'    => $campusid,
            'system_id'    => $systemId,

            // months (read-only display cells)
            'prev_net'     => $PM['net'], 'curr_net' => $CM['net'], 'next_net' => $NM['net'],
            'prev_key'     => $pKey,      'curr_key' => $cKey,      'next_key' => $nKey,
            'pAmt'         => $PM['amount'], 'cAmt'  => $CM['amount'], 'nAmt'  => $NM['amount'],
            'monthly_fee_type_id' => (int)$monthlyFeeTypeId,
        ]);
    }

    return $this->response->setBody($tbodyHtml);
}

protected function allowedStudentFields(): array
{
    // Fee screen only needs these student columns.
    return [
        // stored as discount in students table (not net fee)
        'discounted_amount' => ['rules' => 'permit_empty|decimal', 'label' => 'Discounted Amount', 'table' => 'students', 'type' => 'decimal'],
        'fee_plan'          => ['rules' => 'permit_empty|in_list[0,1,2,3]', 'label' => 'Fee Plan', 'table' => 'students', 'type' => 'int'],
    ];
}


public function saveStudentInfo()
{
    $req        = $this->request;
    $student_id = (int) $req->getPost('student_id');
    if (!$student_id) {
        return $this->response->setJSON(['success' => false, 'msg' => 'student_id is required']);
    }

    // ---- Inputs / toggles ---------------------------------------------------
    $monthsPost = $req->getPost('months');// array keyed by YYYY-MM

// --- Normalize month keys, tolerate bad/legacy keys, map relative to ref_month ---
$refMonth = trim((string)($req->getPost('ref_month') ?? ''));
if (!preg_match('/^\d{4}-\d{2}$/', $refMonth)) {
    $refMonth = date('Y-m');
}
$base   = \DateTime::createFromFormat('Y-m', $refMonth) ?: new \DateTime('first day of this month');
$prevYm = (clone $base)->modify('-1 month')->format('Y-m');
$currYm = (clone $base)->format('Y-m');
$nextYm = (clone $base)->modify('+1 month')->format('Y-m');

$mapLegacy = [
    'month_prev' => $prevYm,
    'month_curr' => $currYm,
    'month_next' => $nextYm,
];

if (!is_array($monthsPost)) { $monthsPost = []; }

$norm        = [];
$invalidKeys = [];

$parseMonY = static function (string $label): ?string {
    $label = trim($label);
    if (!preg_match('/^[A-Za-z]{3}\s+\d{4}$/', $label)) return null; // e.g. "Sep 2025"
    $ts = strtotime('01 ' . $label);
    return $ts ? date('Y-m', $ts) : null;
};

foreach ($monthsPost as $k => $v) {
    $key = trim((string)$k);
    $ym  = null;

    if (preg_match('/^\d{4}-\d{2}$/', $key)) {
        $ym = $key;
    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $key)) {
        $ym = substr($key, 0, 7);
    } elseif (isset($mapLegacy[$key])) {
        $ym = $mapLegacy[$key]; // map prev/curr/next via ref_month
    } else {
        $ym = $parseMonY($key); // "Sep 2025"
    }

    if (!$ym || !preg_match('/^\d{4}-\d{2}$/', $ym)) {
        $invalidKeys[] = $key;  // skip unrecognized keys
        continue;
    }

    $norm[$ym] = is_array($v) ? $v : ['apply' => (int)$v];
}

if (!empty($invalidKeys)) {
    log_message('debug', 'saveStudentInfo: ignored invalid fee month keys: ' . json_encode($invalidKeys));
}

$monthsPost = $norm;

    $selected = array_values(array_unique(array_filter((array) $req->getPost('selected_fields'))));
    $allowed  = $this->allowedStudentFields();         // your existing helper
    $apply    = array_intersect($selected, array_keys($allowed));

    if (empty($apply) && !is_array($monthsPost)) {
        return $this->response->setJSON(['success' => false, 'msg' => 'No columns selected.']);
    }

   $relinkFlagA = strtolower((string) ($req->getPost('parent_link_intent') ?? '')); // e.g. 'link_or_create'
$relinkFlagB = strtolower((string) ($req->getPost('father_cnic_relink') ?? ''));
$relinkFlagC = strtolower((string) ($req->getPost('relink_parent') ?? ''));

$relinkRequested = in_array($relinkFlagA, ['link_or_create','relink'], true)
                || in_array($relinkFlagB, ['1','true','on','yes'], true)
                || in_array($relinkFlagC, ['1','true','on','yes'], true);

    // CNIC normalizer (inline; feel free to move to a private helper)
   $normalizeCnic = static function (?string $raw): ?string {
    $raw = trim((string) $raw);
    if ($raw === '') return null;
    if (preg_match('/^\d{5}-\d{7}-\d$/', $raw)) return $raw;
    $d = preg_replace('/\D+/', '', $raw);
    if (strlen($d) !== 13) return null;
    return substr($d,0,5).'-'.substr($d,5,7).'-'.substr($d,12,1);
};

$postedFatherCnic = $req->getPost('father_cnic');
$postedFName      = $req->getPost('f_name');

// only treat as relink if the checkbox is ticked AND a CNIC was actually entered
$hasCnicInput   = strlen(trim((string) $postedFatherCnic)) > 0;
$normalizedCnic = $hasCnicInput ? $normalizeCnic($postedFatherCnic) : null;
$doRelink       = $relinkRequested && $hasCnicInput;

    $campusid = (int) ($this->session->get('member_campusid') ?? 0);
    $userId   = (int) ($this->session->get('member_userid') ?? 0);
    if ($campusid <= 0) {
        return $this->response->setJSON(['success'=>false,'msg'=>'Campus not set in session.']);
    }

    // ---- Validate selected (non-file/file) ---------------------------------
    if (!empty($apply)) {
        $val   = \Config\Services::validation();
        $rules = [];
        foreach ($apply as $col) {
            if (!empty($allowed[$col]['is_file'])) {
                $fileObj = $this->request->getFile($col);
                if ($fileObj && $fileObj->isValid() && !$fileObj->hasMoved()) {
                    $rules[$col] = $allowed[$col]['rules'];
                }
            } else {
                $rules[$col] = $allowed[$col]['rules'];
            }
        }
        if (!empty($rules) && !$val->setRules($rules)->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Validation failed',
                'errors'  => $val->getErrors(),
            ]);
        }
    }

    // ---- Load student (and current parent if needed) -----------------------
    $student = $this->db->table('students')->where('student_id', $student_id)->get()->getRow();
    if (!$student) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Student not found.']);
    }

    $needsParent = array_filter($apply, fn($c) => ($allowed[$c]['table'] ?? 'students') === 'parents');
    $currentParent = null;
    if (!empty($needsParent) && !empty($student->parent_id)) {
        $currentParent = $this->db->table('parents')->where('parent_id', $student->parent_id)->get()->getRow();
    }
    // For Case 1, we require current parent to exist when editing parent fields
    if (!$doRelink && !empty($needsParent) && !$currentParent) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Parent record not found for this student.']);
    }

    // ---- Build payloads (student / parent) ---------------------------------
    $studentData = [
        'updated_date' => date('Y-m-d H:i:s'),
        'user_id'      => $userId,
    ];
    $parentData  = [];

    foreach ($apply as $col) {
        if (!empty($allowed[$col]['is_file'])) continue;

        $tbl  = $allowed[$col]['table'] ?? 'students';
        $type = $allowed[$col]['type']  ?? 'string';
        $valv = $this->normalizeValue($type, $req->getPost($col)); // your existing helper

        if ($col === 'flag') {
            // (your existing student flag resolver)
            $campus_flags = $this->getCampusFlags($campusid);
            $raw = $req->getPost('flag');
            $resolved = null;
            if ($campus_flags->daycare_flag == 1 && $campus_flags->boarding_flag == 1) {
                $resolved = in_array($raw, ['1','2'], true) ? (int)$raw : null;
            } elseif ($campus_flags->daycare_flag == 1) {
                $resolved = 1;
            } elseif ($campus_flags->boarding_flag == 1) {
                $resolved = 2;
            }
            if ($resolved !== null) $studentData['flag'] = $resolved;
            continue;
        }

        if ($tbl === 'parents') {
            // If we are RELINKING (Case 2), don't modify the old parent here.
            if ($doRelink && in_array($col, ['father_cnic','f_name'], true)) {
                continue;
            }
            $parentData[$col] = $valv;
        } else {
            $studentData[$col] = $valv;
        }
    }

    // ---- Photo (file) ------------------------------------------------------
    if (in_array('profile_photo', $apply, true)) {
        $image = $this->request->getFile('profile_photo');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            $dest = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads';
            if (!is_dir($dest)) {
                if (!@mkdir($dest, 0755, true) && !is_dir($dest)) {
                    return $this->response->setJSON(['success' => false, 'msg' => 'Upload dir missing: '.$dest]);
                }
            }
            if (!is_writable($dest)) {
                @chmod($dest, 0755);
                if (!is_writable($dest)) {
                    return $this->response->setJSON(['success' => false, 'msg' => 'Upload dir not writable: '.$dest]);
                }
            }
            $ext     = $image->getClientExtension() ?: $image->guessExtension() ?: 'jpg';
            $newName = uniqid('stu_', true) . '.' . strtolower($ext);
            try {
                $image->move($dest, $newName);
                $studentData['profile_photo'] = $newName;
            } catch (\Throwable $e) {
                log_message('error', 'Photo move failed: {msg}', ['msg'=>$e->getMessage()]);
                return $this->response->setJSON(['success'=>false,'msg'=>'Failed to save photo.']);
            }
        }
    }

    // ---- Trim nulls (cosmetic) --------------------------------------------
    foreach ($studentData as $k => $v) {
        if (in_array($k, ['updated_date','user_id'], true)) continue;
        if ($v === null && $k !== 'profile_photo') unset($studentData[$k]);
    }
    foreach ($parentData as $k => $v) {
        if ($v === null) unset($parentData[$k]);
    }

    // ---- Monthly setup -----------------------------------------------------
    $hasMonthlyWork     = false;
    $monthlyOps         = [];
    $sessionid          = (int) $this->session->get('member_sessionid');
    $systemId           = $this->resolveSystemId();            // your helper
    $monthlyFeeTypeId   = $this->getMonthlyFeeTypeId($systemId); // your helper

    // ---- DB TRANSACTION ----------------------------------------------------
    $this->db->transBegin();

    /** ===================== Case 2: RELINK by CNIC ===================== */
    if ($doRelink) {
         if ($normalizedCnic === null) {
        $this->db->transRollback();
        return $this->response->setJSON([
            'success'=>false,
            'msg'=>'Enter a valid 13-digit Father CNIC (XXXXX-XXXXXXX-X).'
        ]);
    }

        // If client already sent parent_id from the lookup, verify it’s in this campus
        $postedPid = (int) ($req->getPost('parent_id') ?? 0);
        $targetParent = null;
        if ($postedPid > 0) {
            $targetParent = $this->db->table('parents')
                ->select('parent_id, f_name, father_cnic')
                ->where('parent_id', $postedPid)
                ->where('campus_id', $campusid)
                ->get(1)->getRow();
        }

        // If no valid posted parent or mismatch CNIC, search by CNIC
        if (!$targetParent || (isset($targetParent->father_cnic) && $targetParent->father_cnic !== $normalizedCnic)) {
            $targetParent = $this->db->table('parents')
                ->select('parent_id, f_name, father_cnic')
                ->where('father_cnic', $normalizedCnic)
                ->where('campus_id', $campusid)
                ->get(1)->getRow();
        }

        $newParentId = null;

        if ($targetParent) {
            // 2a) Found existing parent in this campus → switch to it
            $newParentId = (int) $targetParent->parent_id;
        } else {
            // 2b) Not found → create, requires Father Name
            $nameToUse = trim((string) $postedFName);
            if ($nameToUse === '') {
                $this->db->transRollback();
                return $this->response->setJSON(['success'=>false,'msg'=>'Father Name is required to create a new parent.']);
            }

            // Merge any other (non-name/cnic) parent fields user set
            $insertParent = array_merge(
                array_diff_key($parentData, array_flip(['father_cnic','f_name'])),
                [
                    'father_cnic'  => $normalizedCnic,
                    'f_name'       => $nameToUse,
                    'campus_id'    => $campusid,
                    'created_date' => date('Y-m-d H:i:s'),
                    'updated_date' => date('Y-m-d H:i:s'),
                    'user_id'      => $userId,
                    'status'       => 1,
                ]
            );

            $ok  = $this->db->table('parents')->insert($insertParent);
            $err = $this->db->error();
            if (!$ok || !empty($err['code'])) {
                // Handle race (duplicate): pull and reuse
                if ((int)($err['code'] ?? 0) === 1062) {
                    $dup = $this->db->table('parents')
                        ->select('parent_id')
                        ->where('father_cnic', $normalizedCnic)
                        ->where('campus_id', $campusid)
                        ->get(1)->getRow();
                    if ($dup) {
                        $newParentId = (int) $dup->parent_id;
                    } else {
                        $this->db->transRollback();
                        return $this->response->setJSON(['success'=>false,'msg'=>'Could not resolve duplicate parent.']);
                    }
                } else {
                    $this->db->transRollback();
                    return $this->response->setJSON(['success'=>false,'msg'=>'Parent insert failed: ['.($err['code']??'').'] '.($err['message']??'')]);
                }
            } else {
                $newParentId = (int) $this->db->insertID();
            }

            // If we created the parent *and* posted more parent fields, apply them now (except cnic/name which we already set)
            $extra = array_diff_key($parentData, array_flip(['father_cnic','f_name']));
            if (!empty($extra)) {
                $ok  = $this->db->table('parents')->where('parent_id', $newParentId)->update($extra + [
                    'updated_date' => date('Y-m-d H:i:s'),
                    'user_id'      => $userId,
                ]);
                $err = $this->db->error();
                if (!$ok || !empty($err['code'])) {
                    $this->db->transRollback();
                    return $this->response->setJSON(['success'=>false,'msg'=>'Failed to update new parent details: ['.($err['code']??'').'] '.($err['message']??'')]);
                }
            }
        }

        // Switch the student to the existing/new parent_id
        $ok  = $this->db->table('students')->where('student_id', $student_id)->update([
            'parent_id'    => $newParentId,
            'updated_date' => date('Y-m-d H:i:s'),
            'user_id'      => $userId,
        ]);
        $err = $this->db->error();
        if (!$ok || !empty($err['code'])) {
            $this->db->transRollback();
            return $this->response->setJSON(['success'=>false,'msg'=>'Failed to update student parent_id: ['.($err['code']??'').'] '.($err['message']??'')]);
        }

        // After relink, do NOT update the previous parent
        $parentData = [];
    }

   /** ===================== Case 1: normal field updates ===================== */
// Update students (non-parent fields)
if (!empty($studentData)) {
    $ok  = $this->db->table('students')->where('student_id', $student_id)->update($studentData);
    $err = $this->db->error();
    if (!$ok || !empty($err['code'])) {
        $this->db->transRollback();
        log_message('error', 'Students update failed [{code}] {message}', $err);
        return $this->response->setJSON([
            'success'=>false,
            'msg'=>'Students update failed: ['.($err['code']??'').'] '.($err['message']??'')
        ]);
    }
}

// Update current parent only when NOT relinking and parent fields were selected
if (!$doRelink && !empty($parentData)) {

    // If father_cnic is being updated, normalize + ensure uniqueness within campus
    if (array_key_exists('father_cnic', $parentData)) {
        $norm = $normalizeCnic((string)$parentData['father_cnic']);
        if ($norm === null) {
            $this->db->transRollback();
            return $this->response->setJSON([
                'success'=>false,
                'msg'=>'Invalid Father CNIC format. Use XXXXX-XXXXXXX-X.'
            ]);
        }

        // Make sure the CNIC isn't used by another parent in this campus
        $exists = $this->db->table('parents')
            ->select('parent_id')
            ->where('father_cnic', $norm)
            ->where('campus_id', $campusid)
            ->where('parent_id !=', (int)$student->parent_id)
            ->get(1)->getRow();

        if ($exists) {
            $this->db->transRollback();
            return $this->response->setJSON([
                'success'=>false,
                'msg'=>'This Father CNIC already belongs to another parent in this campus.'
            ]);
        }

        $parentData['father_cnic'] = $norm;
    }

    // Perform the update on the current parent record
    $ok  = $this->db->table('parents')
        ->where('parent_id', (int)$student->parent_id)
        ->update($parentData + [
            'updated_date' => date('Y-m-d H:i:s'),
            'user_id'      => $userId,
        ]);
    $err = $this->db->error();

    if (!$ok || !empty($err['code'])) {
        $this->db->transRollback();
        log_message('error', 'Parents update failed [{code}] {message}', $err);
        return $this->response->setJSON([
            'success'=>false,
            'msg'=>'Parents update failed: ['.($err['code']??'').'] '.($err['message']??'')
        ]);
    }
}

if (is_array($monthsPost)) {
    $norm = [];
    // derive prev/curr/next in case legacy keys arrive
    $base = new \DateTime('first day of this month');
    $mapLegacy = [
        'month_prev' => $base->modify('-1 month')->format('Y-m'),
        'month_curr' => (new \DateTime('first day of this month'))->format('Y-m'),
        'month_next' => (new \DateTime('first day of next month'))->format('Y-m'),
    ];

    foreach ($monthsPost as $k => $v) {
        $k = trim((string)$k);
        $ym = null;

        if (preg_match('/^\d{4}-\d{2}$/', $k)) {
            $ym = $k;
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $k)) {
            $ym = substr($k, 0, 7);
        } elseif (preg_match('/^[A-Za-z]{3}\s+\d{4}$/', $k)) { // e.g., "Sep 2025"
            $ts = strtotime('01 ' . $k);
            if ($ts !== false) $ym = date('Y-m', $ts);
        } elseif (isset($mapLegacy[$k])) {
            $ym = $mapLegacy[$k];
        }

        if ($ym === null) {
            throw new \InvalidArgumentException('Invalid fee month format'); // keep your behaviour
        }
        $norm[$ym] = is_array($v) ? $v : ['apply' => (int)$v];
    }
    $monthsPost = $norm;
}
    /** ===================== MONTHLY (unchanged logic) ===================== */
    if (is_array($monthsPost) && $monthlyFeeTypeId) {
        foreach ($monthsPost as $fee_month => $m) {
            if (!isset($m['apply'])) continue;

            $ym         = substr((string)$fee_month, 0, 7); // 'YYYY-MM'
            $desiredNet = (float) ($m['net'] ?? 0);
            $origNet    = (float) ($m['orig_net'] ?? 0);
            $amountBase = (float) ($m['amount'] ?? 0);

            // clamp desiredNet for safety (UI already clamps)
            if ($amountBase > 0) {
                if ($desiredNet < 0) $desiredNet = 0;
                if ($desiredNet > $amountBase) $desiredNet = $amountBase;
            } else {
                $desiredNet = max(0.0, $desiredNet);
            }

            // Try to find existing unpaid fee_chalan for this month/type
            $existQB = $this->db->table('fee_chalan')
                ->where('student_id', $student_id)
                ->where('fee_type_id', $monthlyFeeTypeId)
                ->where('status', 'unpaid')
                ->where('fee_month', $ym)
                ->select('chalan_id, amount, discount')
                ->limit(1);
            $existQuery = $existQB->get();
            $existRow   = $existQuery ? $existQuery->getFirstRow() : null;

            if ($existRow) {
                // Use net exactly as entered (can be same/different)
                $amountRow = (float) $existRow->amount;
                $oldDisc   = (float) $existRow->discount;
                $newDisc   = round($amountRow - $desiredNet, 2); // may be negative if net > amount

                if ($newDisc === $oldDisc) {
                    $monthlyOps[$ym] = 'no_update_discount_same';
                    continue;
                }

                $ok = $this->db->table('fee_chalan')
                    ->where('chalan_id', (int) $existRow->chalan_id)
                    ->update([
                        'discount'     => $newDisc,
                        'is_tampered'  => 1,
                        'updated_date' => date('Y-m-d H:i:s'),
                        'user_id'      => $userId,
                    ]);
                $err = $this->db->error();
                if (!$ok || !empty($err['code'])) {
                    $this->db->transRollback();
                    return $this->response->setJSON([
                        'success' => false,
                        'msg'     => 'Failed updating monthly fee for ' . $ym . ': [' . ($err['code'] ?? '') . '] ' . ($err['message'] ?? ''),
                    ]);
                }

                $monthlyOps[$ym] = ($this->db->affectedRows() > 0) ? 'updated_discount' : 'no_change_db_same';
                if ($this->db->affectedRows() > 0) $hasMonthlyWork = true;
                continue;
            }

            // No row exists -> create invoice + fee_chalan
            if ($amountBase <= 0) {
                // Get class fee if amount not provided
                $qry = $this->db->table('student_class sc')
                    ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
                    ->where('sc.student_id', $student_id)
                    ->where('sc.session_id', $sessionid)
                    ->select('cs.class_id')
                    ->limit(1)
                    ->get();

                $classId = 0;
                if ($qry !== false) {
                    $r = $qry->getFirstRow();
                    if ($r && isset($r->class_id)) $classId = (int)$r->class_id;
                }
                if (!$classId && isset($student->class_id)) {
                    $classId = (int)$student->class_id;
                }
                $amountBase = $classId ? $this->getClassFee($classId, $sessionid, $campusid, $systemId) : 0.0;
            }

            $amountBase = max(0.0, $amountBase);
            $discount   = max(0.0, $amountBase - $desiredNet);
            if ($discount > $amountBase) $discount = $amountBase;

            $invoiceNo = $this->generateInvoiceNumber($ym);
            $yrShort   = (int)DateTime::createFromFormat('Y-m', $ym)->format('y');

            $invoiceData = [
                'student_id'   => $student_id,
                'issue_date'   => date('Y-m-d'),
                'fee_month'    => $ym, // store YYYY-MM
                'yr'           => $yrShort,
                'invoice_no'   => $invoiceNo,
                'created_date' => date('Y-m-d H:i:s'),
                'updated_date' => date('Y-m-d H:i:s'),
                'user_id'      => $userId,
            ];
            $ok  = $this->db->table('invoices')->insert($invoiceData);
            $err = $this->db->error();
            if (!$ok || !empty($err['code'])) {
                $this->db->transRollback();
                return $this->response->setJSON([
                    'success'=>false,
                    'msg'=>'Failed creating invoice for '.$ym.': ['.($err['code']??'').'] '.($err['message']??'')
                ]);
            }

            $chalData = [
                'invoice_no'   => $invoiceNo,
                'student_id'   => $student_id,
                'fee_type_id'  => $monthlyFeeTypeId,
                'fee_month'    => $ym,
                'amount'       => $amountBase,
                'discount'     => $discount,
                'status'       => 'unpaid',
                'is_tampered'  => ($discount > 0 ? 1 : 0),
                'issue_date'   => date('Y-m-d'),
                'due_date'     => date('Y-m-d', strtotime('+10 days')),
                'created_date' => date('Y-m-d H:i:s'),
                'updated_date' => date('Y-m-d H:i:s'),
                'user_id'      => $userId,
                // 'campus_id' => $campusid, 'system_id' => $systemId, // if present in schema
            ];
            $ok  = $this->db->table('fee_chalan')->insert($chalData);
            $err = $this->db->error();
            if (!$ok || !empty($err['code'])) {
                $this->db->transRollback();
                return $this->response->setJSON([
                    'success'=>false,
                    'msg'=>'Failed creating fee_chalan for '.$ym.': ['.($err['code']??'').'] '.($err['message']??'')
                ]);
            }

            $monthlyOps[$ym] = 'inserted_invoice_and_fee_chalan';
            $hasMonthlyWork  = true;
        }
    }

    // ---- COMMIT ------------------------------------------------------------
    $this->db->transCommit();

    // ---- Build response ----------------------------------------------------
    $updatedFields = array_keys(array_diff_key($studentData, ['updated_date'=>1, 'user_id'=>1]));
    if (!empty($parentData)) {
        $updatedFields = array_merge($updatedFields, array_keys($parentData));
    }

    if (empty($updatedFields) && !$hasMonthlyWork && !$doRelink) {
        return $this->response->setJSON([
            'success'       => false,
            'msg'           => 'Nothing to update.',
            'updated_fields'=> [],
            'monthly_ops'   => $monthlyOps,
        ]);
    }

    return $this->response->setJSON([
        'success'       => true,
        'msg'           => $doRelink ? 'Parent relinked successfully.' : 'Update successful.',
        'updated_fields'=> $updatedFields,
        'monthly_ops'   => $monthlyOps,
    ]);
}


    // === Helpers ===
    protected function getMonthlyFeeTypeId(int $systemId = 1): ?int
    {
        $row = $this->db->table('fee_type')
            ->select('fee_type_id')
            ->where('is_monthly_fee', 1)
            ->where('system_id', $systemId)
            ->get()->getRow();

        return $row ? (int) $row->fee_type_id : null;
    }

    protected function normalizeValue(string $type, $val)
    {
        // trim strings; empty -> null
        if ($type === 'string' || $type === 'email') {
            $val = is_string($val) ? trim($val) : $val;
            return ($val === '' ? null : $val);
        }
        if ($type === 'int') {
            return ($val === '' || $val === null) ? null : (int)$val;
        }
        if ($type === 'date') {
            $val = is_string($val) ? trim($val) : $val;
            if ($val === '' || $val === null) return null;
            $ts = strtotime($val);
            return $ts ? date('Y-m-d', $ts) : null;
        }
        if ($type === 'decimal') {
            if ($val === '' || $val === null) return null;
            return (float) $val;
        }
        return $val;
    }

    protected function getClassFee(int $classId, int $sessionId, int $campusId, int $systemId = 1): float
    {
        $feeTypeId = $this->getMonthlyFeeTypeId($systemId);
        if (!$feeTypeId) return 0.0;

        $row = $this->db->table('fee_amount')
            ->select('amount')
            ->where([
                'class_id'   => $classId,
                'session_id' => $sessionId,
                'campus_id'  => $campusId,
                'fee_type_id'=> $feeTypeId,
            ])->get()->getRow();

        return $row ? (float) $row->amount : 0.0;
    }



}
