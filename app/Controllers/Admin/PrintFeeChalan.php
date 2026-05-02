<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Database\BaseConnection;
use Config\Services;
use CodeIgniter\I18n\Time;  
use stdClass;
use DateTime;
use App\Libraries\FeeChalanDisplayRows;

class PrintFeeChalan extends BaseController
{ 

    protected $db;
    protected $session;

    public function __construct()
    {
        
        helper(['form', 'url']);
        $this->db = db_connect();
        $this->session = Services::session();
    }

    public function index()
    {
        return view('admin/print_fee_chalan');
    }


public function get_chalans()
{
    $campusid   = (int) session('member_campusid');
    $student_id = (int) ($this->request->getPost('student_id') ?? 0);
    $parent_id  = (int) ($this->request->getPost('parent_id') ?? 0);

    $builder = $this->db->table('fee_chalan fc')
        ->select('fc.id, fc.student_id, fc.parent_id, fc.month, fc.amount, fc.discount, fc.balance, fc.status, fc.created_at')
        ->where('fc.campus_id', $campusid);

    if ($parent_id > 0)  $builder->where('fc.parent_id', $parent_id);
    if ($student_id > 0) $builder->where('fc.student_id', $student_id);

    // show only unpaid if that’s your default
    // adjust if your schema uses a different flag/column
    $builder->where('fc.status', 0);

    $builder->orderBy('fc.created_at', 'DESC');

    $rows = $builder->get()->getResultArray();

    // Return a partial view (Bootstrap table)
    return view('admin/fee/partials/fee_chalan_list', ['rows' => $rows]);
}


// advance fee type id = 194
   public function add()
{
   

    $campusInfo = getCampusInfo();
    $schoolInfo = getSchoolInfo();

    $db = \Config\Database::connect();

    $system_id = $schoolInfo->system_id ?? 0;

    $fee_type_info = $db->table('fee_type')
        ->where('s_flag', 1)
        ->where('system_id', $system_id)
        ->where('status', 1)
        ->get()
        ->getResult();

    $a_fee_type_info = $db->table('fee_type')
        ->where('a_flag', 1)
        ->where('system_id', $system_id)
        ->where('status', 1)
        ->get()
        ->getResult();

    $t_fee_type_info = $db->table('fee_type')
        ->where('t_flag', 1)
        ->where('system_id', $system_id)
        ->where('status', 1)
        ->get()
        ->getResult();

    $h_fee_type_info = $db->table('fee_type')
        ->where('h_flag', 1)
        ->where('system_id', $system_id)
        ->where('status', 1)
        ->get()
        ->getResult();

    return view('admin/fee_chalan_add', [
        'campusInfo' => $campusInfo,
        'fee_type_info' => $fee_type_info,
        'a_fee_type_info' => $a_fee_type_info,
        't_fee_type_info' => $t_fee_type_info,
        'h_fee_type_info' => $h_fee_type_info
    ]);
}




    public function data(): ResponseInterface
    {
        $response = new stdClass();
        $campus_id = $this->session->get('member_campusid');

        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start');
        $length = $this->request->getPost('length');

        $builder = $this->db->table('fee_chalan A')
            ->select('A.*, B.reg_no, B.first_name, B.last_name, C.fee_type_name')
            ->join('students B', 'A.student_id = B.student_id')
            ->join('fee_type C', 'A.fee_type_id = C.fee_type_id', 'left')
            ->where('B.campus_id', $campus_id);

        $response->recordsTotal = $builder->countAllResults(false);

        $builder->orderBy('A.chalan_id', 'DESC')
                ->limit($length, $start);

        $query = $builder->get();
        $results = $query->getResult();

        $data = [];
        $row['unpaid_last12'] = $this->buildLast12MonthsStatus((int)$row['student_id']);
        foreach ($results as $row) {
            $data[] = [
                'id'            => $row->chalan_id,
                'reg_no'        => $row->reg_no,
                'student_name'  => trim($row->first_name . ' ' . $row->last_name),
                'fee_month'     => $row->fee_month,
                'amount'        => $row->amount - $row->discount,
                'status'        => $row->status,
                'fee_name'      => $row->fee_type_name,
                 'unpaid_last12' => $unpaidLast12, // <<< add this
            ];
        }

        $response->draw = $draw;
        $response->recordsFiltered = $response->recordsTotal;
        $response->data = $data;

        return $this->response->setJSON($response);
    }

  
 
    public function edit($id)
    {
        $builder = $this->db->table('fee_chalan');
        $chalan = $builder->where('chalan_id', $id)->get()->getRow();

        return view('admin/fee_chalan_edit', ['info' => $chalan]);
    }

   

public function get_students_for_chalan()
{
    $db = \Config\Database::connect();
    $session_id = session('member_sessionid');
    $campus_id = session('member_campusid');

    $students = $db->table('student_class sc')
        ->select('sc.student_id, cs.class_id, s.std_type, s.discounted_amount')
        ->join('students s', 's.student_id = sc.student_id')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
        ->where('sc.session_id', $session_id)
        ->where('s.campus_id', $campus_id)
        ->where('s.status', 'active')
        ->get()
        ->getResultArray();

    return $this->response->setJSON(['status' => 'success', 'students' => $students]);
}





    private function getSections(): array
    {
        $campus_id = $this->session->get('member_campusid');

        $db = \Config\Database::connect();
        $builder = $db->table('class_section cs');
        $builder->select('cs.cls_sec_id as section_id, CONCAT(c.class_name, " - ", s.section_name) as sectionclassname');
        $builder->join('classes c', 'cs.class_id = c.class_id', 'left');
        $builder->join('sections s', 'cs.section_id = s.section_id', 'left');
        $builder->where('cs.status', 1);
        $builder->where('cs.campus_id', $campus_id);
        return $builder->get()->getResultArray();
    }


    public function thermalCopy()
    {
        return $this->renderChalan('admin/printchalanview/fee_chalan_thermal_copy');
    }

    public function singleCopy()
    {
        return $this->renderChalan('admin/printchalanview/fee_chalan_single_copy');
    }

    public function threeCopyPdf()
    {
        return $this->renderChalan('admin/printchalanview/fee_chalan_pdf');
    }

    public function withoutDiscount()
    {
        return $this->renderChalan('admin/printchalanview/fee_chalan_without_discount');
    }


private function buildLast12MonthsStatus(int $student_id): array
{
    $now = new \DateTime('first day of this month');
    $start = (clone $now)->modify('-11 months')->format('Y-m-01');
    $end   = (clone $now)->modify('+1 month')->format('Y-m-01'); // exclusive upper bound

    // Pull chalans for this student across the 12-month window.
    // We rely on issue_date to bucket months.
    $rows = $this->db->table('fee_chalan')
        ->select("
            chalan_id, student_id,
            issue_date, due_date, paid_date, status,
            amount, discount
        ")
        ->where('student_id', $student_id)
        ->where("issue_date >=", $start)
        ->where("issue_date <",  $end)
        ->get()->getResultArray();

    // Prepare 12 empty buckets (oldest -> newest)
    $months = [];
    for ($i = 11; $i >= 0; $i--) {
        $dt = (clone $now)->modify("-{$i} months");
        $k  = $dt->format('Y-m');
        $months[$k] = [
            'key'          => $k,
            'label'        => $dt->format('M'),
            'paid_amount'  => 0.0,
            'unpaid_amount'=> 0.0,
            'paid_date'    => null,
            'due_date'     => null,
            'status'       => 'unpaid', // default
        ];
    }

    // Bucket and sum
    foreach ($rows as $r) {
        if (empty($r['issue_date'])) continue;
        $bucket = date('Y-m', strtotime($r['issue_date']));
        if (!isset($months[$bucket])) continue;

        $net = (float)($r['amount'] ?? 0) - (float)($r['discount'] ?? 0);
        if ($net <= 0) continue;

        $due  = !empty($r['due_date'])  ? $r['due_date']  : null;
        $paid = !empty($r['paid_date']) ? $r['paid_date'] : null;

        // Track most recent due_date/paid_date for that month (if multiple rows exist)
        if ($due && (!$months[$bucket]['due_date'] || $due > $months[$bucket]['due_date'])) {
            $months[$bucket]['due_date'] = $due;
        }
        if ($paid && (!$months[$bucket]['paid_date'] || $paid > $months[$bucket]['paid_date'])) {
            $months[$bucket]['paid_date'] = $paid;
        }

        if (strtolower((string)$r['status']) === 'paid') {
            $months[$bucket]['paid_amount'] += $net;
        } else {
            // everything else counts as unpaid (e.g., 'unpaid')
            $months[$bucket]['unpaid_amount'] += $net;
        }
    }

    // Decide final status per month
    foreach ($months as $k => $m) {
        if ($m['paid_amount'] > 0) {
            if ($m['paid_date'] && $m['due_date']) {
                $months[$k]['status'] = (strtotime($m['paid_date']) <= strtotime($m['due_date']))
                    ? 'paid_on_time'
                    : 'paid_late';
            } else {
                // paid but one of the dates missing -> treat as on-time for display sanity
                $months[$k]['status'] = 'paid_on_time';
            }
        } else {
            // No paid amount in that month; if there’s unpaid_amount > 0 => unpaid
            $months[$k]['status'] = ($m['unpaid_amount'] > 0) ? 'unpaid' : 'unpaid';
        }
    }

    // Return in chronological order (oldest -> newest)
    return array_values($months);
}


private function buildLast12MonthsUnpaid(int $studentId): array
{
    // Oldest (11 months ago) → Latest (current month)
    $months = [];
    $altKeys = []; // alternative YYYY-MM keys in case data is stored that way
    $now = new DateTime('first day of this month');

    for ($i = 11; $i >= 0; $i--) {
        $dt = (clone $now)->modify("-{$i} months");
        $key1 = $dt->format('m/Y');   // e.g. "02/2025"
        $key2 = $dt->format('Y-m');   // e.g. "2025-02" (alt stored format)
        $months[$key1] = ['key' => $key1, 'label' => $dt->format('M'), 'amount' => 0];
        $altKeys[$key1] = $key2;
    }

    $keys1 = array_keys($months);
    $keys2 = array_values($altKeys);

    // Query unpaid amounts grouped per fee_month (support both formats)
    // SUM(amount - discount). NULL discount handled by IFNULL.
    $builder = $this->db->table('fee_chalan')
        ->select('fee_month, SUM(amount - IFNULL(discount,0)) AS due', false)
        ->where('student_id', $studentId)
        ->where('status', 'unpaid');

    // (fee_month IN m/Y) OR (fee_month IN Y-m)
    $builder->groupStart()
        ->whereIn('fee_month', $keys1)
        ->orWhereIn('fee_month', $keys2)
    ->groupEnd();

    $rows = $builder->groupBy('fee_month')->get()->getResult();

    // Map results into our 12 cells; normalize to "m/Y"
    foreach ($rows as $r) {
        $fm = (string)$r->fee_month;
        $norm = '';
        if (preg_match('~^\d{2}/\d{4}$~', $fm)) {          // "MM/YYYY"
            $norm = $fm;
        } elseif (preg_match('~^\d{4}-\d{2}$~', $fm)) {    // "YYYY-MM" → "MM/YYYY"
            $norm = DateTime::createFromFormat('Y-m', $fm)->format('m/Y');
        }
        if ($norm && isset($months[$norm])) {
            $months[$norm]['amount'] = (float)$r->due;
        }
    }

    // Return in display order (oldest → latest)
    return array_values($months);
}



    public function familywise()
    {
        return $this->renderChalan('admin/printchalanview/fee_chalan_familywise', true);
    }

    public function familywiseSingleCopy()
    {
        return $this->renderChalan('admin/printchalanview/single_copy_fee_chalan_familywise', true);
    }

    public function hostel()
    {
        return $this->renderChalan('admin/printchalanview/fee_chalan_hostel', false, true);
    }

    public function withHeader()
    {
        return $this->renderChalan('admin/fee_chalan_with_header');
    }


 public function setDefaultTemplate()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(405);

        $payload = $this->request->getJSON(true);
        $key     = $payload['template_key'] ?? '';
        if (!in_array($key, ['entries','thermal','single_copy','three_copy','no_discount','familywise','family_single','hostel','with_header'], true)) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Invalid template']);
        }

        // example: save per campus in table `campus` or in a `settings` table
        $campusId = (int) ($this->session->get('member_campusid') ?? 0);
        if (!$campusId) return $this->response->setJSON(['ok' => false]);

        // persist (pseudo code; use your own model)
        $db = \Config\Database::connect();
        $db->table('campus')->where('campus_id', $campusId)->update(['default_chalan_template' => $key]);

        return $this->response->setJSON(['ok' => true]);
    }


public function get_studentinfo()
{
    $search_term = trim($this->request->getPost('term') ?? '');
    $cls_sec_id  = $this->request->getPost('flag');
    $campusid    = (int) session('member_campusid');

    $builder = $this->db->table('students s')
        ->select('
            s.student_id,
            s.parent_id,
            CONCAT(s.first_name, " ", COALESCE(s.last_name, "")) AS student_name,
            p.f_name AS father_name,
            CONCAT(c.class_name, " ", sec.section_name) AS section_name
        ')
        ->join('parents p', 'p.parent_id = s.parent_id', 'left')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.status = 1', 'left')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where('s.status', 1)
        ->where('s.campus_id', $campusid);

    if ($search_term !== '') {
        $builder->groupStart()
            ->like('s.first_name', $search_term)
            ->orLike('s.last_name', $search_term)
            ->orLike('p.f_name', $search_term)
            ->orLike('s.student_id', $search_term) // allow “search by ID”
        ->groupEnd();
    }

    if ($cls_sec_id && is_numeric($cls_sec_id)) {
        $builder->where('sc.cls_sec_id', (int)$cls_sec_id);
    }

    $rows = $builder->groupBy('s.student_id')->get()->getResultArray();

    $data = array_map(function ($r) {
        $father = $r['father_name'] ?: '';
        $sec    = $r['section_name'] ?: '';
        return [
            'id'         => (int)$r['student_id'],
            'parent_id'  => (int)$r['parent_id'],
            'text'       => "{$r['student_name']} c/o {$father} {$sec}",
        ];
    }, $rows);

    return $this->response->setJSON($data);
}




    private function renderChalan(string $viewName, bool $isFamilywise = false, bool $isHostel = false)
    {
        $request = service('request');


        $session = session();
        $campus_id = $session->get('member_campusid');


        $cls_sec_id = $request->getGet('cls_sec_id');

        $cls_sec_id = is_numeric($cls_sec_id) ? (int) $cls_sec_id : null;
        
        $fee_month = $request->getGet('fee_month') ?? '';
        $footer_line1 = $request->getGet('footer_line1') ?? '';
        $show_line1 = $request->getGet('show_line1') ?? 0;
        $footer_line2 = $request->getGet('footer_line2') ?? '';
        $show_line2 = $request->getGet('show_line2') ?? 0;

        $sectionsclassinfo = $this->getSections();
         
        $data = [
            'cls_sec_id' => $cls_sec_id,
            'fee_month' => $fee_month,
            'footer_line1' => $footer_line1,
            'show_line1' => $show_line1,
            'footer_line2' => $footer_line2,
            'show_line2' => $show_line2,
            'sectionsclassinfo' => $sectionsclassinfo,
            'data' => $isFamilywise
                ? $this->fetchFamilywiseChalanData()
                : ($isHostel ? $this->fetchHostelChalanData() : $this->fetchChalanData(false, false, $cls_sec_id, $fee_month))
        ];
        //print_r($data);
        // exit;

        echo view($viewName, $data);
        exit;
    }

    private function fetchChalanData(
    bool $isFamilywise = false,
    bool $isHostel = false,
    ?int $cls_sec_id = null,
    ?string $fee_month = null   // kept for compatibility; not used to filter unpaid list
): array
{
    if ($isFamilywise) return $this->fetchFamilywiseChalanData();
    if ($isHostel)     return $this->fetchHostelChalanData();

    $campus_id  = (int) session()->get('member_campusid');
    $session_id = (int) session()->get('member_sessionid');

    // -- Subquery: last chalan per student (ignore status)
    $lastEntrySub = "
        SELECT student_id,
               chalan_id   AS last_chalan_id,
               fee_month   AS last_fee_month,
               issue_date  AS last_issue_date,
               due_date    AS last_due_date
        FROM (
            SELECT
                fc.student_id,
                fc.chalan_id,
                fc.fee_month,
                fc.issue_date,
                fc.due_date,
                ROW_NUMBER() OVER (
                    PARTITION BY fc.student_id
                    ORDER BY
                        CASE
                          WHEN CAST(SUBSTRING_INDEX(fc.fee_month, '-', 1) AS UNSIGNED) > 12
                               THEN STR_TO_DATE(CONCAT(fc.fee_month, '-01'), '%Y-%m-%d')  /* YYYY-MM */
                          ELSE STR_TO_DATE(CONCAT(fc.fee_month, '-01'), '%m-%Y-%d')      /* MM-YYYY */
                        END DESC,
                        fc.issue_date DESC,
                        fc.chalan_id DESC
                ) AS rn
            FROM fee_chalan fc
        ) z
        WHERE rn = 1
    ";

    // -- Build a per-student list (one row per student) + header fields from last-entry subquery
    $builder = $this->db->table('students s');
    $builder->select("
        s.student_id,
        TRIM(CONCAT_WS(' ', TRIM(s.first_name), NULLIF(TRIM(s.last_name), ''))) AS student_name,
        s.reg_no,
        p.f_name AS f_name,
        cs.class_id,
        c.class_name,
        sec.short_name AS section_short_name,  /* <— from sections */
        cm.campus_name, cm.location, cm.bank_name, cm.bank_address, cm.bank_code, cm.bank_acc,
        cm.chalan_h_msg, cm.chalan_f_msg,
        sys.system_name, sys.logo,
        p.parent_id,
        lc.last_chalan_id, lc.last_fee_month, lc.last_issue_date, lc.last_due_date
    ", false);

    $builder->join('parents p',        'p.parent_id   = s.parent_id',   'left');
    $builder->join('student_class sc', 'sc.student_id = s.student_id',  'left');
    $builder->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left');
    $builder->join('classes c',        'c.class_id    = cs.class_id',   'left');

    // 🔑 join sections to get short name (A/B/C...)
    $builder->join('sections sec',     'sec.section_id = cs.section_id', 'left');

    $builder->join('campus cm',        'cm.campus_id  = s.campus_id',    'left');
    $builder->join('system sys',       'sys.system_id = cm.system_id',   'left');

    // last-entry fields
    $builder->join("($lastEntrySub) lc", 'lc.student_id = s.student_id', 'left');

    // Filters & scoping — keep these minimal to avoid unknown-column errors
    $builder->where('s.status', 1);
    // If your schema surely has sc.status, you may enable this next line. Otherwise keep it off.
    // $builder->where('sc.status', 1);
    // If you previously filtered on cs.status and it errored, leave that out.

    $builder->where('sc.session_id', $session_id);
    $builder->where('s.campus_id', $campus_id);

    if (!empty($cls_sec_id)) {
        $builder->where('sc.cls_sec_id', $cls_sec_id);
    }

    // One row per student
    $builder->groupBy('s.student_id');

    $students = $builder->get()->getResultArray();

    // Campus-level late fee setup (optional)
    $late = $this->db->table('campus')
        ->select('late_fee_fine, fine_type')
        ->where('campus_id', $campus_id)
        ->get()->getRow();

    // For each student, attach ALL unpaid records (body) and pretty labels for header
    foreach ($students as $k => &$row) {
        // Header labels (last entry — regardless of status)
        $row['last_fee_month_label']  = !empty($row['last_fee_month'])
            ? $this->formatFeeMonthLabel($row['last_fee_month'])
            : null;

        // Dates formatted as dd-mm-yy (e.g., 31-12-25)
        $row['last_issue_date_label'] = !empty($row['last_issue_date'])
            ? date('d-m-y', strtotime($row['last_issue_date']))
            : null;

        $row['last_due_date_label']   = !empty($row['last_due_date'])
            ? date('d-m-y', strtotime($row['last_due_date']))
            : null;

        // All UNPAID chalans for this student (latest first) — helper already enriches net_amount & particulars_label
        $unpaid = $this->getUnpaidChalansByStudent((int)$row['student_id']);

        // Drop zero/negative rows (amount - discount <= 0)
        $unpaid = array_values(array_filter($unpaid, static function($r) {
            $net = (float)($r['net_amount'] ?? ((float)($r['amount'] ?? 0) - (float)($r['discount'] ?? 0)));
            return $net > 0;
        }));

        // Total payable across unpaid (+ monthly vs other split)
        $totalAll       = 0.0;
        $payableMonthly = 0.0;
        $payableOther   = 0.0;
        foreach ($unpaid as $u) {
            $net = (float)($u['net_amount'] ?? ((float)($u['amount'] ?? 0) - (float)($u['discount'] ?? 0)));
            $totalAll += $net;
            if ((int)($u['is_monthly_fee'] ?? 0) === 1) {
                $payableMonthly += $net;
            } else {
                $payableOther += $net;
            }
        }

        // Skip student entirely if no payable
        if ($totalAll <= 0) {
            unset($students[$k]);
            continue;
        }

        // Late fee info
        $row['late_fee_fine'] = $late->late_fee_fine ?? null;
        $row['fine_type']     = $late->fine_type     ?? null;

        $unpaidSorted                = $this->sortUnpaidChalansForDisplay($unpaid);
        $row['unpaid_rows']          = $unpaid;
        $row['unpaid_display_rows']  = FeeChalanDisplayRows::studentRows($unpaidSorted);
        $row['unpaid_total_payable'] = $totalAll;  // for "Payable Within Due Date"
        $row['unpaid_payable_monthly'] = $payableMonthly;
        $row['unpaid_payable_other']   = $payableOther;
        $row['unpaid_last12'] = $this->buildLast12MonthsStatus((int)$row['student_id']);
    }
    unset($row);

    // Re-index in case we removed any students
    $students = array_values($students);

    return $students;
}

/**
 * Fetch all UNPAID fee_chalan rows for a student, ordered by fee_month chronology,
 * then issue_date, then chalan_id. Returns raw rows plus pretty labels.
 */
private function getUnpaidChalansByStudent(int $student_id): array
{
    $qb = $this->db->table('fee_chalan fc');
    $qb->select('
        fc.chalan_id, fc.fee_type_id, fc.fee_month, fc.issue_date, fc.due_date,
        fc.amount, fc.discount, fc.status,
        ft.fee_type_name, ft.is_monthly_fee
    ', false);
    $qb->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id', 'left');

    $qb->where('fc.student_id', $student_id);
    $qb->where('fc.status', 'unpaid');

    // Latest first
    $qb->orderBy("
        CASE
          WHEN CAST(SUBSTRING_INDEX(fc.fee_month, '-', 1) AS UNSIGNED) > 12
               THEN STR_TO_DATE(CONCAT(fc.fee_month, '-01'), '%Y-%m-%d')  /* YYYY-MM */
          ELSE STR_TO_DATE(CONCAT(fc.fee_month, '-01'), '%m-%Y-%d')      /* MM-YYYY */
        END DESC,
        fc.issue_date DESC,
        fc.chalan_id DESC
    ", '', false);

    $rows = $qb->get()->getResultArray();

    // Enrich with strict MM/YYYY, particulars label, and net
    foreach ($rows as &$r) {
        $feeMonthRaw = $r['fee_month'] ?? '';
        $compact = '';
        if ($feeMonthRaw) {
            $parts = explode('-', $feeMonthRaw);
            if (count($parts) === 2) {
                if ((int)$parts[0] > 12) { // YYYY-MM
                    $y = (int)$parts[0]; $m = (int)$parts[1];
                } else {                   // MM-YYYY
                    $m = (int)$parts[0]; $y = (int)$parts[1];
                }
                if ($y > 0 && $m >= 1 && $m <= 12) {
                    $compact = sprintf('%02d/%04d', $m, $y);
                }
            }
        }
        $r['fee_month_compact'] = $compact ?: ($r['fee_month'] ?? '');
        $r['fee_month_label']   = $r['fee_month_compact'];
        $r['particulars_label'] = trim(($r['fee_type_name'] ?? 'Fee') . ' (' . $r['fee_month_compact'] . ')');
        $r['net_amount']        = (float)($r['amount'] ?? 0) - (float)($r['discount'] ?? 0);
    }
    unset($r);

    // Filter out zero or negative net rows here (so controller logic receives only non-zero)
    $rows = array_values(array_filter($rows, static function($r) {
        return (float)($r['net_amount'] ?? 0) > 0;
    }));

    return $rows;
}

/**
 * Non-monthly fee lines first, then monthly (aligned with FeeChalan PDF output).
 */
private function sortUnpaidChalansForDisplay(array $unpaid): array
{
    $other   = [];
    $monthly = [];
    foreach ($unpaid as $r) {
        if ((int) ($r['is_monthly_fee'] ?? 0) === 1) {
            $monthly[] = $r;
        } else {
            $other[] = $r;
        }
    }

    return array_merge($other, $monthly);
}

/**
 * Turn YYYY-MM or MM-YYYY into "Month-YYYY". Otherwise returns input.
 */
private function formatFeeMonthLabel(?string $ym): ?string
{
    if (!$ym) return $ym;
    $parts = preg_split('/[-\/]/', $ym);
    if (count($parts) !== 2) return $ym;

    if ((int)$parts[0] > 12) {           // YYYY-MM
        $year  = $parts[0];
        $month = $parts[1];
    } else {                              // MM-YYYY
        $month = $parts[0];
        $year  = $parts[1];
    }

    $dt = \DateTime::createFromFormat('!m', (string)(int)$month);
    return $dt ? $dt->format('F') . '-' . $year : $ym;
}


    
   



}
