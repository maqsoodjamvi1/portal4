<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\StudentsModel;
use CodeIgniter\HTTP\ResponseInterface as CIResponse; 
use DateTime;

class StudentsBulkInfo extends BaseController
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
        return view('admin/students_bulk_info', $data);
    }

    protected function userClassSections()
    {
        return $this->db->table('class_section cs')
            ->select('cs.cls_sec_id, cs.section_id, cs.class_id, CONCAT(c.class_name, " (", s.section_name, ")") as sectionclassname')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->where('cs.status', 1)
            ->where('cs.campus_id', $this->session->get('member_campusid'))
            ->orderBy('c.class_id', 'ASC')
            ->orderBy('s.section_id', 'ASC')
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
    $campusid   = (int) $this->session->get('member_campusid');
    $sessionid  = (int) $this->session->get('member_sessionid');
    $systemId   = $this->resolveSystemId();

    // NEW: optional parent filter (when user searched by name and you pass parent_id)
    $parent_id  = (int) $this->request->getPost('parent_id');

    // Existing: optional class/section filter (used when picking a class)
    $cls_sec_id = trim((string) $this->request->getPost('cls_sec_id'));

    $debugMode  = ($this->request->getGet('debug') === '1');

    // --- Months (prev/curr/next) parsing stays same ---
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

    // Resolve selectedClassId only when filtering by class (in parent mode we use each row's class)
    $selectedClassId = null;
    if ($parent_id <= 0 && $cls_sec_id !== '') {
        $row = $this->db->table('class_section')
            ->select('class_id')
            ->where('cls_sec_id', (int)$cls_sec_id)
            ->get()->getRow();
        $selectedClassId = $row ? (int)$row->class_id : null;
    }

    // =========================
    // BUILD THE QUERY
    // =========================
    // Keep your original base from student_class so we only see the student's record for the CURRENT session.
    $qb = $this->db->table('student_class sc')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
        ->join('students s', 's.student_id = sc.student_id')
        ->join('parents p', 'p.parent_id = s.parent_id', 'left')
        ->where('sc.session_id', $sessionid)
        ->where('s.campus_id', $campusid)
        ->where('s.status', 1); // only active students

    // Filter mode:
    // - If parent_id provided -> ignore cls_sec_id and load all kids of that parent for this campus+session.
    // - Else if cls_sec_id provided -> filter by that section (existing behaviour).
    if ($parent_id > 0) {
        $qb->where('s.parent_id', $parent_id);
    } elseif ($cls_sec_id !== '') {
        $qb->where('sc.cls_sec_id', (int)$cls_sec_id);
    }

    $student_class = $qb->select("
            sc.*,
            s.*,
            cs.class_id AS cs_class_id,
            p.f_name                AS p_f_name,
            p.caste                 AS p_caste,
            p.father_email          AS p_father_email,
            p.father_occupation     AS p_father_occupation,
            p.father_office_address AS p_father_office_address,
            p.m_name                AS p_m_name,
            p.father_contact        AS p_father_contact,
            p.whatsapp              AS p_whatsapp,
            p.mother_contact        AS p_mother_contact,
            p.emergency_contact     AS p_emergency_contact,
            p.father_cnic           AS p_father_cnic,
            p.address_line1         AS p_address_line1,
            p.city                  AS p_city,
            p.hear_source           AS p_hear_source,
            p.emergency_contact_person AS p_emergency_contact_person,
            p.relationship          AS p_relationship,
            p.religion              AS p_religion
        ")
        // Sort by class id (not class name), then section, then student name
        ->orderBy('cs.class_id', 'ASC')
        ->orderBy('cs.section_id', 'ASC')
        ->orderBy('s.first_name', 'ASC')
        ->orderBy('s.last_name',  'ASC')
        ->get()->getResult();

    // =========================
    // RENDER ROWS
    // =========================
    $studentsList     = '';
    $campus_flags     = $this->getCampusFlags($campusid);
    $monthlyFeeTypeId = $this->getMonthlyFeeTypeId($systemId);

    foreach ($student_class as $sc) {
        // s.* already merged into $sc
        $student = $sc;

        // Fee context: if class filter used, prefer that; otherwise use each row's class_id from the join
        $classIdFromJoin = (int)($sc->cs_class_id ?? 0);
        $classIdUsed     = $selectedClassId ?: $classIdFromJoin;

        $classFee        = $classIdUsed
            ? $this->getClassFee($classIdUsed, $sessionid, $campusid, $systemId)
            : 0.0;

        $discount   = (float)($student->discounted_amount ?? 0.0);
        $studentFee = max(0.0, $classFee - $discount);

        // Month keys/labels
        $pKey = $selectedMonths['month_prev']['key']  ?? null;
        $cKey = $selectedMonths['month_curr']['key']  ?? null;
        $nKey = $selectedMonths['month_next']['key']  ?? null;

        $pLbl = $selectedMonths['month_prev']['label']?? '';
        $cLbl = $selectedMonths['month_curr']['label']?? '';
        $nLbl = $selectedMonths['month_next']['label']?? '';

        // Read unpaid aggregates for a month
        $readMonth = function (?string $yyyymm) use ($monthlyFeeTypeId, $student, $classFee) {
            if (!$yyyymm || !$monthlyFeeTypeId) {
                return ['net'=>0.0, 'amount'=>$classFee, 'has'=>false];
            }
            $agg = $this->db->table('fee_chalan')
                ->select('COUNT(*) as cnt, COALESCE(SUM(amount),0) as amt, COALESCE(SUM(discount),0) as disc')
                ->where([
                    'student_id'  => (int)$student->student_id,
                    'fee_month'   => $yyyymm,
                    'status'      => 'unpaid',
                    'fee_type_id' => $monthlyFeeTypeId,
                ])->get()->getRow();

            $cnt  = (int)($agg->cnt ?? 0);
            $amt  = (float)($agg->amt ?? 0);
            $disc = (float)($agg->disc ?? 0);

            if ($cnt > 0) {
                $net = max(0.0, $amt - $disc);
                return ['net'=>$net, 'amount'=>$amt, 'has'=>true];
            }
            return ['net'=>0.0, 'amount'=>$classFee, 'has'=>false];
        };

        $PM = $readMonth($pKey);
        $CM = $readMonth($cKey);
        $NM = $readMonth($nKey);

        // Row view
        $studentsList .= view('admin/partials/student_bulk_info_row', [
            // core
            'student'       => $student,
            'parent_name'   => (string)($sc->p_f_name ?? ''),
            'date_of_birth' => $student->date_of_birth ?? '',
            'gender'        => $student->gender ?? 'male',
            'daycare_flag'  => $student->flag ?? '',
            'profile_photo' => $student->profile_photo ?? '',
            'campus_flags'  => $campus_flags,

            // student fields (map to actual columns)
            'address'                   => $sc->p_address_line1 ?? '',
            'previous_school'           => $student->previous_school ?? '',
            'ps_city'                   => $student->ps_city ?? '',
            'health_condition'          => $student->health_conditions ?? '',
            'major_injuries'            => $student->major_injuries ?? '',
            'gr_no'                     => $student->gr_no ?? '',
            'gr_date'                   => $student->gr_date ?? '',
            'religion'                  => $sc->p_religion ?? '',
            'city'                      => $sc->p_city ?? '',
            'hear_source'               => $sc->p_hear_source ?? '',
            'emergency_contact_person'  => $sc->p_emergency_contact_person ?? '',
            'relationship'              => $sc->p_relationship ?? '',

            // parent fields
            'caste'                 => $sc->p_caste ?? '',
            'father_email'          => $sc->p_father_email ?? '',
            'father_occupation'     => $sc->p_father_occupation ?? '',
            'father_office_address' => $sc->p_father_office_address ?? '',
            'm_name'                => $sc->p_m_name ?? '',
            'father_contact'        => $sc->p_father_contact ?? '',
            'whatsapp'              => $sc->p_whatsapp ?? '',
            'mother_contact'        => $sc->p_mother_contact ?? '',
            'emergency_contact'     => $sc->p_emergency_contact ?? '',
            'father_cnic'           => $sc->p_father_cnic ?? '',
            'f_name'                => $sc->p_f_name ?? '',

            // student name / fee plan / cnic
            'first_name'        => $student->first_name ?? '',
            'last_name'         => $student->last_name ?? '',
            'date_of_admission' => $student->date_of_admission ?? '',
            'fee_plan'          => isset($student->fee_plan) ? (int)$student->fee_plan : 0,
            'std_cnic'          => $student->std_cnic ?? '',
            'std_type'          => isset($student->std_type) ? (int)$student->std_type : 0,

            // fee + ids for JS
            'class_fee'         => $classFee,
            'discounted_amount' => $discount,
            'student_fee'       => $studentFee,
            'class_id'          => $classIdUsed,
            'session_id'        => $sessionid,
            'campus_id'         => $campusid,
            'system_id'         => $systemId,
            'debug_mode'        => $debugMode,

            // monthly pack
            'monthly_fee_type_id' => $monthlyFeeTypeId,
            'prevKey' => $pKey, 'currKey' => $cKey, 'nextKey' => $nKey,
            'prevLbl' => $pLbl, 'currLbl' => $cLbl, 'nextLbl' => $nLbl,
            'pNet' => $PM['net'], 'cNet' => $CM['net'], 'nNet' => $NM['net'],
            'pAmt' => $PM['amount'], 'cAmt' => $CM['amount'], 'nAmt' => $NM['amount'],
        ]);
    }

    return $this->response->setBody($studentsList);
}


// public function data()
// {
//     $campusid  = (int) $this->session->get('member_campusid');
//     $sessionid = (int) $this->session->get('member_sessionid');
//     $systemId  = $this->resolveSystemId();

//     $cls_sec_id = (string) $this->request->getPost('cls_sec_id');
//     $debugMode  = ($this->request->getGet('debug') === '1');

//     // Parse months_json (prev/curr/next)
//     $monthsJson = $this->request->getPost('months_json');
//     $selectedMonths = [];
//     if ($monthsJson) {
//         $tmp = json_decode($monthsJson, true);
//         if (is_array($tmp)) {
//             foreach ($tmp as $m) {
//                 if (!empty($m['col']) && !empty($m['key']) && preg_match('/^\d{4}-\d{2}$/', $m['key'])) {
//                     if (in_array($m['col'], ['month_prev','month_curr','month_next'], true)) {
//                         $selectedMonths[$m['col']] = [
//                             'key'   => $m['key'],
//                             'label' => (string)($m['label'] ?? $m['key']),
//                         ];
//                     }
//                 }
//             }
//         }
//     }
//     if (empty($selectedMonths)) {
//         $refMonth = new \DateTime(date('Y-m-01'));
//         $selectedMonths = [
//             'month_prev' => ['key'=>$refMonth->modify('-1 month')->format('Y-m'), 'label'=>$refMonth->format('F Y')],
//             'month_curr' => ['key'=>date('Y-m'), 'label'=>date('F Y')],
//             'month_next' => ['key'=>(new \DateTime(date('Y-m-01')))->modify('+1 month')->format('Y-m'), 'label'=>(new \DateTime(date('Y-m-01')))->modify('+1 month')->format('F Y')],
//         ];
//     }

//     // Resolve selected class (if a section chosen)
//     $selectedClassId = null;
//     if ($cls_sec_id !== '') {
//         $row = $this->db->table('class_section')
//             ->select('class_id')
//             ->where('cls_sec_id', (int)$cls_sec_id)
//             ->get()->getRow();
//         $selectedClassId = $row ? (int)$row->class_id : null;
//     }

//     // Students + Parents (LEFT JOIN); no inline SQL comments in select!
//     $qb = $this->db->table('student_class sc')
//         ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
//         ->join('students s', 's.student_id = sc.student_id')
//         ->join('parents p', 'p.parent_id = s.parent_id', 'left')
//         ->where('s.campus_id', $campusid)
//         ->where('s.status', 1)
//         ->where('sc.session_id', $sessionid);

//     if ($cls_sec_id !== '') {
//         $qb->where('sc.cls_sec_id', $cls_sec_id);
//     }

//     $student_class = $qb->select("
//             sc.*,
//             s.*,
//             cs.class_id AS cs_class_id,
//             p.f_name                AS p_f_name,
//             p.caste                 AS p_caste,
//             p.father_email          AS p_father_email,
//             p.father_occupation     AS p_father_occupation,
//             p.father_office_address AS p_father_office_address,
//             p.m_name                AS p_m_name,
//             p.father_contact        AS p_father_contact,
//             p.whatsapp              AS p_whatsapp,
//             p.mother_contact        AS p_mother_contact,
//             p.emergency_contact     AS p_emergency_contact,
//             p.father_cnic           AS p_father_cnic,
//             p.address_line1         AS p_address_line1,
//             p.city                  AS p_city,
//             p.hear_source           AS p_hear_source,
//             p.emergency_contact_person AS p_emergency_contact_person,
//             p.relationship          AS p_relationship,
//             p.religion              AS p_religion
//         ")
//         ->orderBy('sc.cls_sec_id', 'ASC')
//         ->get()->getResult();

//     $studentsList     = '';
//     $campus_flags     = $this->getCampusFlags($campusid);
//     $monthlyFeeTypeId = $this->getMonthlyFeeTypeId($systemId);

//     foreach ($student_class as $sc) {
//         // s.* already merged into $sc
//         $student = $sc;

//         // Fee context
//         $classIdFromJoin = (int)($sc->cs_class_id ?? 0);
//         $classIdUsed     = $selectedClassId ?: $classIdFromJoin;
//         $classFee        = $classIdUsed ? $this->getClassFee($classIdUsed, $sessionid, $campusid, $systemId) : 0.0;

//         $discount   = (float)($student->discounted_amount ?? 0.0);
        
          

//         $studentFee = max(0.0, $classFee - $discount);

//         // Month keys & labels
//         $pKey = $selectedMonths['month_prev']['key']  ?? null;
//         $cKey = $selectedMonths['month_curr']['key']  ?? null;
//         $nKey = $selectedMonths['month_next']['key']  ?? null;

//         $pLbl = $selectedMonths['month_prev']['label']?? '';
//         $cLbl = $selectedMonths['month_curr']['label']?? '';
//         $nLbl = $selectedMonths['month_next']['label']?? '';

//         // Unpaid monthly aggregates
//         $readMonth = function (?string $yyyymm) use ($monthlyFeeTypeId, $student, $classFee) {
//             if (!$yyyymm || !$monthlyFeeTypeId) {
//                 return ['net'=>0.0, 'amount'=>$classFee, 'has'=>false];
//             }
//             $agg = $this->db->table('fee_chalan')
//                 ->select('COUNT(*) as cnt, COALESCE(SUM(amount),0) as amt, COALESCE(SUM(discount),0) as disc')
//                 ->where([
//                     'student_id'  => (int)$student->student_id,
//                     'fee_month'   => $yyyymm,
//                     'status'      => 'unpaid',
//                     'fee_type_id' => $monthlyFeeTypeId,
//                 ])->get()->getRow();

//             $cnt  = (int)($agg->cnt ?? 0);
//             $amt  = (float)($agg->amt ?? 0);
//             $disc = (float)($agg->disc ?? 0);

//             if ($cnt > 0) {
//                 $net = max(0.0, $amt - $disc);
//                 return ['net'=>$net, 'amount'=>$amt, 'has'=>true];
//             }
//             return ['net'=>0.0, 'amount'=>$classFee, 'has'=>false];
//         };

//         $PM = $readMonth($pKey);
//         $CM = $readMonth($cKey);
//         $NM = $readMonth($nKey);

//         // Build row for the partial (father name stays hideable in the view)
//         $studentsList .= view('admin/partials/student_bulk_info_row', [
//             // core
//             'student'       => $student,
//             'parent_name'   => (string)($sc->p_f_name ?? ''),
//             'date_of_birth' => $student->date_of_birth ?? '',
//             'gender'        => $student->gender ?? 'male',
//             'daycare_flag'  => $student->flag ?? '',
//             'profile_photo' => $student->profile_photo ?? '',
//             'campus_flags'  => $campus_flags,

//             // student fields (map to actual columns)
//             'address'                   => $sc->p_address_line1 ?? '',                 // from parents
//             'previous_school'           => $student->previous_school ?? '',           // students.prev_school
//             'ps_city'                   => $student->ps_city ?? '',                   // students.ps_city
//             'health_condition'          => $student->health_conditions ?? '',         // students.health_conditions
//             'major_injuries'            => $student->major_injuries ?? '',            // students.major_injuries
//             'gr_no'                     => $student->gr_no ?? '',                     // students.gr_no
//             'gr_date'                   => $student->gr_date ?? '',                   // students.gr_date
//             'religion'                  => $sc->p_religion ?? '',                     // from parents
//             'city'                      => $sc->p_city ?? '',                          // from parents
//             'hear_source'               => $sc->p_hear_source ?? '',                   // from parents
//             'emergency_contact_person'  => $sc->p_emergency_contact_person ?? '',     // from parents
//             'relationship'              => $sc->p_relationship ?? '',                 // from parents

//             // parent fields
//             'caste'                 => $sc->p_caste ?? '',
//             'father_email'          => $sc->p_father_email ?? '',
//             'father_occupation'     => $sc->p_father_occupation ?? '',
//             'father_office_address' => $sc->p_father_office_address ?? '',
//             'm_name'                => $sc->p_m_name ?? '',
//             'father_contact'        => $sc->p_father_contact ?? '',
//             'whatsapp'              => $sc->p_whatsapp ?? '',
//             'mother_contact'        => $sc->p_mother_contact ?? '',
//             'emergency_contact'     => $sc->p_emergency_contact ?? '',
//             'father_cnic'           => $sc->p_father_cnic ?? '',
//             'f_name'                => $sc->p_f_name ?? '',

//             // student name / fee plan / cnic
//             'first_name'        => $student->first_name ?? '',
//             'last_name'         => $student->last_name ?? '',
//             'date_of_admission' => $student->date_of_admission ?? '',
//             'fee_plan'          => isset($student->fee_plan) ? (int)$student->fee_plan : 0,
//             'std_cnic'          => $student->std_cnic ?? '',
//             'std_type'          => isset($student->std_type) ? (int)$student->std_type : 0,

//             // fee + ids for JS
//             'class_fee'         => $classFee,
//             'discounted_amount' => $discount,     // stored discount
//             'student_fee'       => $studentFee,   // classFee - discount
//             'class_id'          => $classIdUsed,
//             'session_id'        => $sessionid,
//             'campus_id'         => $campusid,
//             'system_id'         => $systemId,
//             'debug_mode'        => $debugMode,

//             // monthly pack
//             'monthly_fee_type_id' => $monthlyFeeTypeId,
//             'prevKey' => $pKey, 'currKey' => $cKey, 'nextKey' => $nKey,
//             'prevLbl' => $pLbl, 'currLbl' => $cLbl, 'nextLbl' => $nLbl,
//             'pNet' => $PM['net'], 'cNet' => $CM['net'], 'nNet' => $NM['net'],
//             'pAmt' => $PM['amount'], 'cAmt' => $CM['amount'], 'nAmt' => $NM['amount'],
//         ]);
//     }

//     return $this->response->setBody($studentsList);
// }


    protected function allowedStudentFields(): array
    {
        // table: students|parents, type: string|date|email|int|file|decimal
        return [
            // core (students)
            'date_of_birth' => ['rules'=>'permit_empty|valid_date', 'label'=>'Date of Birth', 'table'=>'students','type'=>'date'],
            'gender'        => ['rules'=>'permit_empty|in_list[male,female,other]', 'label'=>'Gender', 'table'=>'students','type'=>'string'],
            'flag'          => ['rules'=>'permit_empty|in_list[0,1,2]', 'label'=>'Student Flag', 'table'=>'students','type'=>'int'],

            // file
            'profile_photo' => [
                'rules'=>'uploaded[profile_photo]|is_image[profile_photo]|max_size[profile_photo,4096]|ext_in[profile_photo,jpg,jpeg,png,webp]',
                'label'=>'Photo','table'=>'students','type'=>'file','is_file'=>true
            ],

            // students (extended)
            
            'previous_school' => ['rules'=>'permit_empty|max_length[255]', 'label'=>'Previous School', 'table'=>'students','type'=>'string'],
            'ps_city' => ['rules'=>'permit_empty|max_length[100]', 'label'=>'PS City', 'table'=>'students','type'=>'string'],
            'health_condition' => ['rules'=>'permit_empty|max_length[255]', 'label'=>'Health Condition', 'table'=>'students','type'=>'string'],
            'major_injuries' => ['rules'=>'permit_empty|max_length[255]', 'label'=>'Major Injuries', 'table'=>'students','type'=>'string'],
            'gr_no' => ['rules'=>'permit_empty|alpha_numeric_punct|max_length[50]','label'=>'GR No', 'table'=>'students','type'=>'string'],
            'gr_date' => ['rules'=>'permit_empty|valid_date', 'label'=>'GR Date', 'table'=>'students','type'=>'date'],
            'religion' => ['rules'=>'permit_empty|max_length[50]', 'label'=>'Religion', 'table'=>'students','type'=>'string'],
            'city' => ['rules'=>'permit_empty|max_length[100]', 'label'=>'City', 'table'=>'students','type'=>'string'],
            'hear_source' => ['rules'=>'permit_empty|max_length[100]', 'label'=>'Hear Source', 'table'=>'students','type'=>'string'],
            'emergency_contact_person' => ['rules'=>'permit_empty|max_length[100]', 'label'=>'Emergency Person', 'table'=>'students','type'=>'string'],
            'relationship' => ['rules'=>'permit_empty|max_length[50]', 'label'=>'Relationship', 'table'=>'students','type'=>'string'],

            // parents
            // Keep both keys for backward compatibility; UI posts `address`.
            'address'       => ['rules'=>'permit_empty|max_length[255]', 'label'=>'Address', 'table'=>'parents','type'=>'string'],
            'Address_line1' => ['rules'=>'permit_empty|max_length[255]', 'label'=>'Address_line1', 'table'=>'parents','type'=>'string'],
            'father_email' => ['rules'=>'permit_empty|valid_email|max_length[150]', 'label'=>'Father Email', 'table'=>'parents','type'=>'email'],
            'caste' => ['rules'=>'permit_empty|max_length[100]', 'label'=>'Caste', 'table'=>'parents','type'=>'string'],
            'father_occupation' => ['rules'=>'permit_empty|max_length[100]', 'label'=>'Father Occupation', 'table'=>'parents','type'=>'string'],
            'father_office_address' => ['rules'=>'permit_empty|max_length[255]', 'label'=>'Father Office Addr', 'table'=>'parents','type'=>'string'],
            'm_name' => ['rules'=>'permit_empty|max_length[100]', 'label'=>'Mother Name', 'table'=>'parents','type'=>'string'],

            // NEW Parent fields
            'father_contact'   => ['rules'=>'permit_empty|regex_match[/^[0-9+\-\s]{6,20}$/]|max_length[20]', 'label'=>'Father Contact','table'=>'parents','type'=>'string'],
            'whatsapp'         => ['rules'=>'permit_empty|regex_match[/^[0-9+\-\s]{6,20}$/]|max_length[20]', 'label'=>'WhatsApp', 'table'=>'parents','type'=>'string'],
            'mother_contact'   => ['rules'=>'permit_empty|regex_match[/^[0-9+\-\s]{6,20}$/]|max_length[20]', 'label'=>'Mother Contact','table'=>'parents','type'=>'string'],
            'emergency_contact'=> ['rules'=>'permit_empty|regex_match[/^[0-9+\-\s]{6,20}$/]|max_length[20]', 'label'=>'Emergency Contact','table'=>'parents','type'=>'string'],
            'father_cnic'      => ['rules'=>'permit_empty|alpha_numeric_punct|max_length[25]','label'=>'Father CNIC', 'table'=>'parents','type'=>'string'],
            'f_name'           => ['rules'=>'permit_empty|max_length[100]', 'label'=>'Father Name', 'table'=>'parents','type'=>'string'],

            // NEW Student fields
            'first_name'        => ['rules'=>'permit_empty|max_length[100]', 'label'=>'First Name', 'table'=>'students','type'=>'string'],
            'last_name'         => ['rules'=>'permit_empty|max_length[100]', 'label'=>'Last Name', 'table'=>'students','type'=>'string'],
            'date_of_admission' => ['rules'=>'permit_empty|valid_date', 'label'=>'Date of Admission', 'table'=>'students','type'=>'date'],
            'discounted_amount' => ['rules'=>'permit_empty|decimal', 'label'=>'Discounted Amount', 'table'=>'students','type'=>'decimal'],
            'fee_plan'          => ['rules'=>'permit_empty|in_list[0,1,2,3]', 'label'=>'Fee Plan', 'table'=>'students','type'=>'int'],
            'std_cnic'          => ['rules'=>'permit_empty|alpha_numeric_punct|max_length[25]','label'=>'Student CNIC', 'table'=>'students','type'=>'string'],
            'std_type'          => ['rules'=>'permit_empty|in_list[1,2]', 'label'=>'Student Type', 'table'=>'students','type'=>'int'],
        ];
    }

    /**
     * Save: both generic "Other Student Info" fields AND monthly-fee edits/inserts.
     * Expects:
     *  - student_id
     *  - selected_fields[]  (as before)
     *  - months[YYYY-MM][apply|net|amount|fee_type_id|orig_net]  (per row)
     *  - ref_month (optional)
     */

private function normalizeCnic(?string $raw): ?string
{
    $raw = trim((string) $raw);
    if ($raw === '') return null;
    if (preg_match('/^\d{5}-\d{7}-\d$/', $raw)) return $raw;
    $d = preg_replace('/\D+/', '', $raw);
    if (strlen($d) !== 13) return null;
    return substr($d,0,5).'-'.substr($d,5,7).'-'.substr($d,12,1);
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


 public function lookup_parent_by_cnic(): CIResponse
    {
        // Allow POST or GET (handy for testing in browser)
        $method = strtolower($this->request->getMethod());
        if (! in_array($method, ['post','get'], true)) {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'msg'     => 'Method not allowed',
            ]);
        }

        // Get CNIC from POST first, then GET
        $raw    = trim((string) ($this->request->getPost('cnic') ?? $this->request->getGet('cnic') ?? ''));
        $digits = preg_replace('/\D+/', '', $raw);

        // Normalize to XXXXX-XXXXXXX-X
        if (preg_match('/^\d{5}-\d{7}-\d$/', $raw)) {
            $cnic = $raw;
        } elseif (strlen($digits) === 13) {
            $cnic = substr($digits, 0, 5) . '-' . substr($digits, 5, 7) . '-' . substr($digits, 12, 1);
        } else {
            return $this->response->setJSON([
                'success' => true,
                'found'   => false,
                'msg'     => 'Invalid CNIC. Use 13 digits or XXXXX-XXXXXXX-X.',
            ]);
        }

        // Campus filter (required)
        $campusId = (int) (session('member_campusid') ?? 0);
        if ($campusId <= 0) {
            return $this->response->setJSON([
                'success' => true,
                'found'   => false,
                'msg'     => 'Campus not set in session.',
            ]);
        }

        // Exact match: WHERE father_cnic = ? AND campus_id = ?
        $db  = \Config\Database::connect();
        $row = $db->table('parents')
            ->select('parent_id, f_name, father_cnic')
            ->where('father_cnic', $cnic)
            ->where('campus_id', $campusId)
            ->get(1)->getRowArray();

        if ($row) {
            return $this->response->setJSON([
                'success'     => true,
                'found'       => true,
                'parent_id'   => (int) $row['parent_id'],
                'f_name'      => (string) ($row['f_name'] ?? ''),
                'father_cnic' => (string) ($row['father_cnic'] ?? $cnic),
                'msg'         => 'Match found.',
            ]);
        }

        return $this->response->setJSON([
            'success'     => true,
            'found'       => false,
            'father_cnic' => $cnic,
            'msg'         => 'No matching parent for this CNIC in this campus.',
        ]);
    }


}
