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

class FeeChalan extends BaseController
{ 

    protected $db;
    protected $session;

    /** In-memory invoice sequence for SSE bulk generation (avoids repeated MAX queries). */
    private ?array $bulkInvoiceSeq = null;

    public function __construct()
    {
        
        helper(['form', 'url', 'school']);
        $this->db = db_connect();
        $this->session = Services::session();
        check_permission('admin-fee-chalan');
    }

      
       private const VIEW_TYPES = [
        'student_three_copy' => 'admin/chalanview/fee_chalan_pdf',
        'student_single_page' => 'admin/chalanview/fee_chalan_single_page',
        'family_three_copy' => 'admin/chalanview/family_chalan_pdf',
        'family_single_page' => 'admin/chalanview/family_chalan_single_page'
    ];

    /**
     * Main chalan view with enhanced filters
     */
  public function index()
{
    $session = session();
    $campus_id = (int) $session->get('member_campusid');
    
    $data = [
        'sectionsclassinfo' => $this->getSections(),
        'classes' => $this->getClasses($campus_id),
        'filterOptions' => [
            'view_types' => [
                ['value' => 'student_three_copy', 'label' => 'Student Wise - 3 Copies per Student'],
                ['value' => 'student_single_page', 'label' => 'Student Wise - Single Page (3 Students)'],
                ['value' => 'family_three_copy', 'label' => 'Family Wise - 3 Copies per Student'],
                ['value' => 'family_single_page', 'label' => 'Family Wise - Single Page (All Family Students)']
            ],
            'discount_options' => [
                ['value' => 'yes', 'label' => 'Show Discount Column'],
                ['value' => 'no', 'label' => 'Hide Discount Column']
            ]
        ],
        'selected_view' => $this->request->getGet('view_type') ?? 'student_three_copy',
        'show_discount' => $this->request->getGet('show_discount') ?? 'yes',
        'class_id' => $this->request->getGet('class_id'),
        'section_id' => $this->request->getGet('section_id'),
        'search' => $this->request->getGet('search'),
        // CHANGED: Removed date('Y-m') default, now empty by default
        'fee_month' => $this->request->getGet('fee_month') ?? '',
        'family_id' => $this->request->getGet('family_id'),
        'footer_line1' => $this->request->getGet('footer_line1') ?? '',
        'footer_line2' => $this->request->getGet('footer_line2') ?? '',
        'show_line1' => $this->request->getGet('show_line1') ?? 0,
        'show_line2' => $this->request->getGet('show_line2') ?? 0,
        'fine_after_due_date' => $this->request->getGet('fine_after_due_date') ?? 0,
        'message_position' => $this->request->getGet('message_position') ?? 'header',
        'message_text' => $this->request->getGet('message_text') ?? '',
        'show_payment_history' => ($this->request->getGet('show_payment_history') == 1 || $this->request->getGet('show_payment_history') === '1') ? 1 : 0,
    ];

    return view('admin/chalanview/chalan_filter', $data);
}

    /**
     * Generate chalans based on selected options
     */
   public function generate()
{
    $request = service('request');
    $g = static function (string $key, $default = null) use ($request) {
        return $request->getGetPost($key) ?? $default;
    };

    // Get all filter parameters (GET or POST — profile student challan uses POST to avoid long URLs / 404s)
    $view_type = $g('view_type', 'student_three_copy');
    $show_discount = $g('show_discount') === 'yes';
    $fee_month = (string) $g('fee_month', '');
    $histRaw = $g('show_payment_history');
    $show_payment_history = ($histRaw == 1 || $histRaw === '1');

    $message_text = (string) $g('message_text', '');
    $message_position = (string) $g('message_position', 'none');

    $view_parts = explode('_', (string) $view_type);
    if (count($view_parts) < 3) {
        $view_type = 'student_three_copy';
        $view_parts = explode('_', $view_type);
    }
    $group_by = $view_parts[0];
    $layout = $view_parts[1] . '_' . $view_parts[2];

    $search = $g('search');
    $selected_student_id = $g('selected_student_id');
    if (($search === null || $search === '') && $selected_student_id !== null && $selected_student_id !== '') {
        $search = $selected_student_id;
    }

    $rawClassId = $g('class_id');
    $rawSectionId = $g('section_id');

    $normalizeOptionalId = static function ($value): ?int {
        if ($value === null) {
            return null;
        }
        $v = strtolower(trim((string) $value));
        if ($v === '' || $v === 'undefined' || $v === 'null' || $v === 'all' || $v === '0') {
            return null;
        }
        return ctype_digit($v) ? (int) $v : null;
    };

    $classId = $normalizeOptionalId($rawClassId);
    $sectionId = $normalizeOptionalId($rawSectionId);

    // UI sometimes sends section_id=undefined with stale class_id in query string.
    // In that case treat both as "no filter applied".
    if (strtolower(trim((string) ($rawSectionId ?? ''))) === 'undefined') {
        $classId = null;
        $sectionId = null;
    }

    $params = [
        'group_by' => $group_by,
        'layout' => $layout,
        'show_discount' => $show_discount,
        'show_payment_history' => $show_payment_history,
        'fee_month' => $fee_month,
        'class_id' => $classId,
        'section_id' => $sectionId,
        'search' => $search,
        'family_id' => $g('family_id') ? (int) $g('family_id') : null,
        'footer_line1' => (string) $g('footer_line1', ''),
        'footer_line2' => (string) $g('footer_line2', ''),
        'show_line1' => (int) $g('show_line1', 0),
        'show_line2' => (int) $g('show_line2', 0),
        'fine_after_due_date' => $g('fine_after_due_date') ?? 0,
        'message_text' => $message_text,
        'message_position' => $message_position,
    ];

    // Fetch data based on grouping
    if ($params['group_by'] === 'family') {
        $data = $this->fetchFamilyChalans($params);
        $viewName = $params['layout'] === 'three_copy' 
            ? 'admin/chalanview/family_chalan_three_copy' 
            : 'admin/chalanview/family_chalan_single_page';
    } else {
        $data = $this->fetchStudentChalans($params);
        $viewName = $params['layout'] === 'three_copy' 
            ? 'admin/chalanview/student_chalan_three_copy' 
            : 'admin/chalanview/student_chalan_single_page';
    }

    // Merge all data for the view
    $viewData = array_merge($data, $params, [
        'show_discount' => $show_discount,
        'show_payment_history' => $show_payment_history,
        'fee_month' => $fee_month,
        'is_family' => ($params['group_by'] === 'family'),
        'message_text' => $message_text,
        'message_position' => $message_position,
        // Do not show campus late-fine banner or payable-after-due on generated slips
        'fine_after_due_date' => 0,
    ]);
    
    // If no data found, show message
    if (empty($viewData['students'] ?? []) && empty($viewData['families'] ?? [])) {
        return view('admin/chalanview/no_data', ['params' => $params]);
    }
    
    return view($viewName, $viewData);
}
    /**
     * Map fee-chalan filter class_id / section_id to class_section rows.
     * The UI historically sent sections.section_id while queries use cs.cls_sec_id;
     * URLs may also pass cls_sec_id as class_id when only one dimension is filtered.
     */
    private function applyClassSectionStudentJoinFilters(
        \CodeIgniter\Database\BaseBuilder $builder,
        int $campus_id,
        ?int $classId,
        ?int $sectionParam
    ): void {
        $classId = ($classId !== null && $classId > 0) ? $classId : null;
        $sectionParam = ($sectionParam !== null && $sectionParam > 0) ? $sectionParam : null;

        if ($classId === null && $sectionParam === null) {
            return;
        }

        if ($classId !== null && $sectionParam !== null) {
            $combo = $this->db->table('class_section')
                ->select('cls_sec_id')
                ->where('campus_id', $campus_id)
                ->where('status', 1)
                ->where('class_id', $classId)
                ->where('section_id', $sectionParam)
                ->limit(1)
                ->get()
                ->getRowArray();
            if ($combo) {
                $builder->where('cs.cls_sec_id', (int) $combo['cls_sec_id']);

                return;
            }

            $asClsSec = $this->db->table('class_section')
                ->select('cls_sec_id, class_id')
                ->where('campus_id', $campus_id)
                ->where('status', 1)
                ->where('cls_sec_id', $sectionParam)
                ->limit(1)
                ->get()
                ->getRowArray();
            if ($asClsSec !== null && (int) $asClsSec['class_id'] === $classId) {
                $builder->where('cs.cls_sec_id', $sectionParam);

                return;
            }

            $builder->where('cs.class_id', $classId);

            return;
        }

        if ($sectionParam !== null) {
            $byClsSec = $this->db->table('class_section')
                ->select('cls_sec_id')
                ->where('campus_id', $campus_id)
                ->where('status', 1)
                ->where('cls_sec_id', $sectionParam)
                ->limit(1)
                ->get()
                ->getRowArray();
            if ($byClsSec) {
                $builder->where('cs.cls_sec_id', (int) $byClsSec['cls_sec_id']);

                return;
            }
            $builder->where('cs.section_id', $sectionParam);

            return;
        }

        $builder->where('cs.class_id', $classId);
    }

    /**
     * Student IDs that have at least one payable fee_chalan line for this campus (fee-first; does not depend on student_class).
     */
    private function getStudentIdsWithPayableFeeLines(int $campus_id, ?string $fee_month): array
    {
        $builder = $this->db->table('fee_chalan fc');
        $builder->distinct();
        $builder->select('fc.student_id');
        $builder->join(
            'students s',
            's.student_id = fc.student_id AND s.campus_id = ' . (int) $campus_id . ' AND s.status = 1',
            'inner'
        );
        $builder->where($this->feeChalanOpenStatusSql('fc'), null, false);
        $builder->where('(COALESCE(fc.amount, 0) - COALESCE(fc.discount, 0)) > 0', null, false);
        if ($fee_month !== null && trim($fee_month) !== '') {
            $this->applyFeeChalanMonthFilter($builder, trim($fee_month));
        }
        $builder->groupBy('fc.student_id');

        $query = $builder->get();
        if ($query === false) {
            return [];
        }

        $ids = [];
        foreach ($query->getResultArray() as $row) {
            $sid = (int) ($row['student_id'] ?? 0);
            if ($sid > 0) {
                $ids[] = $sid;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Collapse duplicate rows when a student has multiple class_section rows.
     *
     * @param list<array<string,mixed>> $rows
     *
     * @return list<array<string,mixed>>
     */
    private function dedupeStudentRowsByStudentId(array $rows): array
    {
        $out = [];
        $seen = [];
        foreach ($rows as $row) {
            $sid = (int) ($row['student_id'] ?? 0);
            if ($sid <= 0 || isset($seen[$sid])) {
                continue;
            }
            $seen[$sid] = true;
            $out[] = $row;
        }

        return $out;
    }

    /**
     * Fetch student-wise chalans with filters
     */
   /**
 * Fetch student-wise chalans with filters
 */
private function fetchStudentChalans(array $params): array
{
    log_message('debug', '=== fetchStudentChalans called ===');
    log_message('debug', 'Params: ' . json_encode($params));

    $campus_id = (int) session()->get('member_campusid');
    $session_id = (int) session()->get('member_sessionid');

    log_message('debug', 'Campus ID: ' . $campus_id . ', Session ID: ' . $session_id);

    $fee_month_raw = isset($params['fee_month']) ? trim((string) $params['fee_month']) : '';
    $fee_month_for_ids = $fee_month_raw !== '' ? $fee_month_raw : null;

    // Fee-first: students who actually have payable fee lines (matches printed totals).
    $candidateIds = $this->getStudentIdsWithPayableFeeLines($campus_id, $fee_month_for_ids);
    if ($candidateIds === []) {
        return ['students' => [], 'group_by' => 'student'];
    }

    // Build student rows with optional class (LEFT join — enrollment gaps no longer hide students).
    $builder = $this->db->table('students s');
    $builder->select("
        s.student_id,
        TRIM(CONCAT_WS(' ', TRIM(s.first_name), NULLIF(TRIM(s.last_name), ''))) AS student_name,
        s.reg_no,
        s.parent_id,
        p.f_name,
        p.father_contact,
        p.father_cnic,
        cs.class_id,
        c.class_name,
        c.class_short_name,
        sec.section_id,
        sec.short_name AS section_short_name,
        sec.section_name,
        cm.campus_name,
        cm.location,
        cm.bank_name,
        cm.bank_address,
        cm.bank_code,
        cm.bank_acc,
        cm.chalan_h_msg,
        cm.chalan_f_msg,
        cm.late_fee_fine,
        cm.fine_type,
        sys.system_name,
        sys.logo
    ");

    $builder->join('parents p', 'p.parent_id = s.parent_id', 'left');
    $builder->join(
        'student_class sc',
        'sc.student_id = s.student_id AND sc.session_id = ' . $session_id . ' AND sc.status = 1',
        'left'
    );
    $builder->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left');
    $builder->join('classes c', 'c.class_id = cs.class_id', 'left');
    $builder->join('sections sec', 'sec.section_id = cs.section_id', 'left');
    $builder->join('campus cm', 'cm.campus_id = s.campus_id', 'left');
    $builder->join('system sys', 'sys.system_id = cm.system_id', 'left');

    $builder->where('s.campus_id', $campus_id);
    $builder->where('s.status', 1);
    $builder->whereIn('s.student_id', $candidateIds);

    $this->applyClassSectionStudentJoinFilters(
        $builder,
        $campus_id,
        isset($params['class_id']) ? (int) $params['class_id'] : null,
        isset($params['section_id']) ? (int) $params['section_id'] : null
    );

    // Apply search filter
    $hasSpecificSearch = false;
    if (!empty($params['search'])) {
        $searchTerm = $params['search'];
        log_message('debug', 'Applying search filter with term: ' . $searchTerm);

        if (is_numeric($searchTerm)) {
            $intTerm = (int) $searchTerm;
            $strTerm = trim((string) $searchTerm);
            $builder->groupStart()
                ->where('s.student_id', $intTerm)
                ->orWhere('s.reg_no', $strTerm)
                ->groupEnd();
            $hasSpecificSearch = true;
        } else {
            $builder->groupStart()
                ->like('s.first_name', $searchTerm)
                ->orLike('s.last_name', $searchTerm)
                ->orLike('p.f_name', $searchTerm)
                ->orLike('s.reg_no', $searchTerm)
                ->groupEnd();
            $hasSpecificSearch = true;
        }
    }

    if (!empty($params['family_id'])) {
        $builder->where('p.parent_id', $params['family_id']);
        $hasSpecificSearch = true;
    }

    $builder->orderBy('c.class_name, sec.section_name, s.first_name');

    $narrowStudentFilter = !empty($params['class_id'])
        || !empty($params['section_id'])
        || !empty($params['family_id'])
        || (isset($params['search']) && trim((string) $params['search']) !== '');

    if (!$hasSpecificSearch && !$narrowStudentFilter) {
        $builder->limit(20000);
    } elseif (!$hasSpecificSearch && $narrowStudentFilter) {
        $builder->limit(5000);
    } else {
        $builder->limit(is_numeric($params['search'] ?? '') ? 25 : 50);
    }

    $query = $builder->get();

    if (!$query) {
        log_message('error', 'Student query failed: ' . $this->db->getLastQuery());

        return ['students' => [], 'group_by' => 'student'];
    }

    $students = $this->dedupeStudentRowsByStudentId($query->getResultArray());
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        log_message('debug', 'Found ' . count($students) . ' students from query');
    }

    $fee_month = $params['fee_month'] ?? '';
    $show_discount = $params['show_discount'] ?? true;
    $show_payment_history = !empty($params['show_payment_history']);
    $studentIds = array_values(array_map(static fn ($r) => (int) $r['student_id'], $students));

    $chalansByStudent = $this->getStudentUnpaidChalansBatch($studentIds, $fee_month, $show_discount);
    $paymentByStudent = $show_payment_history
        ? $this->getStudentPaymentHistoryBatch($studentIds)
        : [];

    foreach ($students as &$studentData) {
        $student_id = (int) $studentData['student_id'];

        $studentData['student_name'] = fee_chalan_student_display_name(
            $studentData['student_name'] ?? '',
            $studentData['reg_no'] ?? null
        );

        $studentData['chalans'] = $chalansByStudent[$student_id] ?? [];

        if ($show_payment_history) {
            $studentData['payment_history'] = $paymentByStudent[$student_id] ?? [
                'month_keys'         => [],
                'monthly_totals'     => [],
                'monthly_fee_totals' => [],
                'other_fee_totals'   => [],
            ];
        } else {
            $studentData['payment_history'] = [
                'month_keys'         => [],
                'monthly_totals'     => [],
                'monthly_fee_totals' => [],
                'other_fee_totals'   => [],
            ];
        }

        $studentData['display_rows'] = $this->processChalanRows($studentData['chalans'] ?? []);

        $studentData['total_payable']   = 0;
        $studentData['total_discount']  = 0;
        $studentData['payable_monthly'] = 0.0;
        $studentData['payable_other']   = 0.0;
        foreach ($studentData['chalans'] ?? [] as $chalan) {
            $studentData['total_payable']  += $chalan['net_amount'] ?? 0;
            $studentData['total_discount'] += $chalan['discount'] ?? 0;
            $netLine = (float) ($chalan['net_amount'] ?? 0);
            if ((int) ($chalan['is_monthly_fee'] ?? 0) === 1) {
                $studentData['payable_monthly'] += $netLine;
            } else {
                $studentData['payable_other'] += $netLine;
            }
        }

        if (!empty($studentData['chalans'])) {
            $latest = $studentData['chalans'][0];
            $studentData['last_chalan_id']   = $latest['chalan_id'] ?? '';
            $studentData['last_issue_date']  = $latest['issue_date_label'] ?? '';
            $studentData['last_due_date']    = $latest['due_date_label'] ?? '';
            $studentData['last_fee_month']   = $latest['fee_month_label'] ?? '';
        } else {
            $studentData['last_chalan_id']   = 'N/A';
            $studentData['last_issue_date']  = date('d-m-y');
            $studentData['last_due_date']    = date('d-m-y', strtotime('+10 days'));
            $studentData['last_fee_month']   = 'No Fee';
        }
    }
    unset($studentData);

    // Filter out students with no payable amount
    $filteredStudents = array_filter($students, function($s) {
        return ($s['total_payable'] ?? 0) > 0;
    });

    log_message('debug', 'After filtering: ' . count($filteredStudents) . ' students remain');

    return [
        'students' => array_values($filteredStudents),
        'group_by' => 'student'
    ];
}

/**
 * Fetch family-wise chalans - Shows consolidated fees with student names in header only
 */


private function fetchFamilyChalans(array $params): array
{
    $campus_id = (int) session()->get('member_campusid');
    $session_id = (int) session()->get('member_sessionid');

    // Get system_id from campus table
    $campusInfoQuery = $this->db->table('campus')
        ->select('system_id')
        ->where('campus_id', $campus_id)
        ->get();
    
    if (!$campusInfoQuery || $campusInfoQuery->getNumRows() == 0) {
        return ['families' => []];
    }
    
    $campusInfo = $campusInfoQuery->getRowArray();
    $system_id = (int)($campusInfo['system_id'] ?? 0);

    // Get families with active students - INCLUDE STUDENT DETAILS with class info for ordering
    $builder = $this->db->table('parents p');
    $builder->select("
        p.parent_id,
        p.f_name,
        p.father_contact,
        p.mother_contact,
        p.emergency_contact,
        p.father_cnic,
        p.address_line1,
        p.city,
        COUNT(DISTINCT s.student_id) as student_count,
        GROUP_CONCAT(DISTINCT CONCAT(s.first_name, ' ', COALESCE(s.last_name, ''), ' (', s.reg_no, ')') SEPARATOR '|') as student_names,
        GROUP_CONCAT(DISTINCT s.student_id SEPARATOR ',') as student_ids,
        -- Include class information for each student (class_name, class_short_name, section, and class_id)
        GROUP_CONCAT(DISTINCT CONCAT(
            COALESCE(s.student_id, 0), '~',
            COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, ''), '~',
            COALESCE(s.reg_no, ''), '~',
            COALESCE(c.class_name, ''), '~',
            COALESCE(c.class_short_name, ''), '~',
            COALESCE(sec.short_name, ''), '~',
            COALESCE(c.class_id, 0)
        ) SEPARATOR '|') as student_details,
        -- Get the maximum class_id for ordering (family head's class)
        MAX(c.class_id) as max_class_id
    ");

    $builder->join('students s', 's.parent_id = p.parent_id AND s.campus_id = ' . $campus_id . ' AND s.status = 1', 'inner');
    $builder->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . $session_id . ' AND sc.status = 1', 'inner');
    $builder->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left');
    $builder->join('classes c', 'c.class_id = cs.class_id', 'left');
    $builder->join('sections sec', 'sec.section_id = cs.section_id', 'left');

    $builder->where('p.campus_id', $campus_id);

    // Apply search filter
    $hasSpecificSearch = false;
    if (!empty($params['search'])) {
        $searchTerm = $params['search'];
        
        if (is_numeric($searchTerm)) {
            $builder->where('p.parent_id', (int)$searchTerm);
            $hasSpecificSearch = true;
        } else {
            $builder->groupStart()
                ->like('p.f_name', $searchTerm)
                ->orLike('p.father_cnic', $searchTerm)
                ->orLike('p.father_contact', $searchTerm)
                ->groupEnd();
            $hasSpecificSearch = true;
        }
    }

    // Filter by specific family if provided
    if (!empty($params['family_id'])) {
        $builder->where('p.parent_id', $params['family_id']);
        $hasSpecificSearch = true;
    }

    $builder->groupBy('p.parent_id');
    $builder->having('student_count > 0');
    
    // Apply limits (broad campus run: all families with unpaid lines, not only first 500)
    if (!$hasSpecificSearch) {
        $builder->limit(10000);
    } else {
        if (is_numeric($params['search'] ?? '')) {
            $builder->limit(1);
        } else {
            $builder->limit(20);
        }
    }

    // Order by the maximum class_id (family head's class) in descending order
    // This puts families with older/higher class students first
    $builder->orderBy('max_class_id', 'DESC');
    
    $query = $builder->get();
    
    if (!$query) {
        return ['families' => []];
    }
    
    $families = $query->getResultArray();

    // Get campus info
    $campusQuery = $this->db->table('campus')
        ->select('campus_name, location, bank_name, bank_address, bank_code, bank_acc, chalan_h_msg, chalan_f_msg, late_fee_fine, fine_type')
        ->where('campus_id', $campus_id)
        ->get();
    
    if (!$campusQuery) {
        $campus = [];
    } else {
        $campus = $campusQuery->getRowArray();
    }

    // Get system info
    $systemSql = "SELECT system_name, logo FROM `system` WHERE system_id = ?";
    $systemQuery = $this->db->query($systemSql, [$system_id]);
    
    if (!$systemQuery || $systemQuery->getNumRows() == 0) {
        $system = [];
    } else {
        $system = $systemQuery->getRowArray();
    }

    // Process each family
    foreach ($families as &$family) {
        $studentIds = !empty($family['student_ids']) ? explode(',', $family['student_ids']) : [];
        
        // Parse student details with full class information
        $students = [];
        if (!empty($family['student_details'])) {
            $studentDetailStrings = explode('|', $family['student_details']);
            foreach ($studentDetailStrings as $detailString) {
                $parts = explode('~', $detailString);
                // New format: student_id~name~reg_no~class_name~class_short~section_short~class_id
                if (count($parts) >= 7) {
                    $students[] = [
                        'student_id' => (int) ($parts[0] ?? 0),
                        'student_name' => trim((string) ($parts[1] ?? '')),
                        'reg_no' => (string) ($parts[2] ?? ''),
                        'class_name' => (string) ($parts[3] ?? ''),
                        'class_short_name' => (string) ($parts[4] ?? ''),
                        'section_short_name' => (string) ($parts[5] ?? ''),
                        'class_id' => (int) ($parts[6] ?? 0),
                    ];
                } elseif (count($parts) >= 6) { // Backward compatibility for old format
                    $students[] = [
                        'student_name' => trim((string) ($parts[0] ?? '')),
                        'reg_no' => (string) ($parts[1] ?? ''),
                        'class_name' => (string) ($parts[2] ?? ''),
                        'class_short_name' => (string) ($parts[3] ?? ''),
                        'section_short_name' => (string) ($parts[4] ?? ''),
                        'class_id' => (int) ($parts[5] ?? 0),
                    ];
                }
            }
        }
        
        // Sort students by class_id (descending) - elder/larger class first
        usort($students, function($a, $b) {
            return ($b['class_id'] ?? 0) - ($a['class_id'] ?? 0);
        });
        
        $family['students'] = $students;
        $family['student_names_array'] = !empty($family['student_names']) ? explode('|', $family['student_names']) : [];
        
        // Get head student (elder student - first in sorted list)
        $headStudent = !empty($students) ? $students[0] : null;
        
        // Get the elder student's class for display in class field (short label)
        $elderClass = $headStudent
            ? fee_chalan_class_badge_text($headStudent['class_short_name'] ?? null, $headStudent['class_name'] ?? null)
            : '';
        $elderSection = $headStudent ? ($headStudent['section_short_name'] ?? '') : '';
        
        // Format class display for class field using short names
        $classDisplay = '';
        if (!empty($elderClass)) {
            $classDisplay = $elderClass;
            if (!empty($elderSection)) {
                $classDisplay .= $elderSection;
            }
        }
        
        // Get consolidated fee data grouped by particular
        $family['fee_by_particular'] = $this->getFamilyFeeByParticular($studentIds, $params['fee_month'] ?? '');
        
        // Calculate totals
        $family['total_payable'] = 0;
        $family['total_discount'] = 0;
        $family['total_amount'] = 0;
        
        $family['payable_monthly'] = 0.0;
        $family['payable_other']   = 0.0;
        foreach ($family['fee_by_particular'] as $fee) {
            $amount = (float)($fee['total_amount'] ?? 0);
            $discount = (float)($fee['total_discount'] ?? 0);
            $netLine  = $amount - $discount;

            $family['total_amount'] += $amount;
            $family['total_discount'] += $discount;
            $family['total_payable'] += $netLine;

            if ((int) ($fee['is_monthly_fee'] ?? 0) === 1) {
                $family['payable_monthly'] += $netLine;
            } else {
                $family['payable_other'] += $netLine;
            }
        }
        
        // Process to create exactly 7 display rows
        $family['display_rows'] = $this->processFamilyFeeRows($family['fee_by_particular']);
        
        // Get fine amount if applicable
        $family['total_fine'] = 0;
        if (!empty($params['fine_after_due_date']) && !empty($campus['late_fee_fine'])) {
            $late_fee = $campus['late_fee_fine'];
            if (($campus['fine_type'] ?? '') === 'per_day_fine') {
                $late_fee = $campus['late_fee_fine'] * 15;
            }
            $family['total_fine'] = $late_fee;
        }
        
        // Add campus and system info
        if (!empty($campus)) {
            $family = array_merge($family, $campus);
        }
        if (!empty($system)) {
            $family = array_merge($family, $system);
        } else {
            $family['system_name'] = $family['campus_name'] ?? 'School Name';
            $family['logo'] = '';
        }
        
        // Get the most recent date for family
        $latestDateInfo = $this->getLatestChalanDateForFamily($studentIds);
        $family['issue_date'] = $latestDateInfo['issue_date'] ?? date('d-m-Y');
        $family['due_date'] = $latestDateInfo['due_date'] ?? date('d-m-Y', strtotime('+10 days'));
        $family['chalan_no'] = $latestDateInfo['chalan_id'] ?? 'N/A';
        
        // Get the latest fee month from fee_by_particular
        $latestFeeMonth = '';
        if (!empty($family['fee_by_particular'])) {
            $months = [];
            foreach ($family['fee_by_particular'] as $fee) {
                if (!empty($fee['month_display'])) {
                    $months[] = $fee['month_display'];
                }
            }
            if (!empty($months)) {
                // Sort months in descending order
                usort($months, function($a, $b) {
                    $aParts = explode('/', $a);
                    $bParts = explode('/', $b);
                    if (count($aParts) == 2 && count($bParts) == 2) {
                        if ($aParts[1] != $bParts[1]) {
                            return $bParts[1] - $aParts[1];
                        }
                        return $bParts[0] - $aParts[0];
                    }
                    return 0;
                });
                $latestFeeMonth = $months[0];
            }
        }
        
        $family['fee_month_display'] = !empty($params['fee_month']) 
            ? $this->formatFeeMonthLabel($params['fee_month']) 
            : ($latestFeeMonth ?: 'All Months');
        
        // ADD PAYMENT HISTORY AT THE VERY END
        if (!empty($params['show_payment_history'])) {
            $family['payment_history'] = $this->getFamilyPaymentHistory($family['parent_id'], $campus_id);
        } else {
            $family['payment_history'] = [
                'month_keys'         => [],
                'monthly_totals'     => [],
                'monthly_fee_totals' => [],
                'other_fee_totals'   => [],
            ];
        }
    }

    // Filter out families with no payable amount
    $families = array_filter($families, function($f) {
        return ($f['total_payable'] ?? 0) > 0;
    });

    return [
        'families' => array_values($families),
        'group_by' => 'family'
    ];
}

/**
 * Get payment history for a single student for the last 12 months
 */
private function getStudentPaymentHistory(int $student_id): array
{
    $map = $this->getStudentPaymentHistoryBatch([$student_id]);

    return $map[$student_id] ?? [
        'month_keys'         => [],
        'monthly_totals'     => [],
        'monthly_fee_totals' => [],
        'other_fee_totals'   => [],
    ];
}

/**
 * Get payment history for a family for the last 12 months
 */
/**
 * Get payment history for a family for the last 12 months
 */
/**
 * Get payment history for a family for the last 12 months
 */

/**
 * Get payment history for a family for the last 12 months based on paid_date
 */
/**
 * Get payment history for a family for the last 12 months based on paid_date
 */
private function getFamilyPaymentHistory(int $parent_id, int $campus_id): array
{
    // SQL query to get payment history for the last 12 months
    $sql = "SELECT 
                DATE_FORMAT(date_table.payment_date, '%Y-%m') AS month_key,
                DATE_FORMAT(date_table.payment_date, '%m/%y') AS short_month,
                DATE_FORMAT(date_table.payment_date, '%M %Y') AS display_month,
                COALESCE(ROUND(SUM(fc.amount - fc.discount), 0), 0) AS family_total,
                COALESCE(ROUND(SUM(CASE WHEN COALESCE(fc.is_monthly_fee, 0) = 1 THEN fc.amount - fc.discount ELSE 0 END), 0), 0) AS family_monthly_fee,
                COALESCE(ROUND(SUM(CASE WHEN COALESCE(fc.is_monthly_fee, 0) = 0 THEN fc.amount - fc.discount ELSE 0 END), 0), 0) AS family_other_fee,
                COALESCE(COUNT(DISTINCT fc.student_id), 0) AS students_count,
                COALESCE(COUNT(fc.chalan_id), 0) AS transactions_count
            FROM (
                SELECT DATE_SUB(CURDATE(), INTERVAL n MONTH) AS payment_date
                FROM (
                    SELECT 0 AS n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 
                    UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7
                    UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11
                ) numbers
            ) date_table
            LEFT JOIN (
                SELECT fc_inner.*, s.parent_id, s.campus_id,
                    COALESCE(ft.is_monthly_fee, 0) AS is_monthly_fee
                FROM fee_chalan fc_inner
                INNER JOIN students s ON s.student_id = fc_inner.student_id
                LEFT JOIN fee_type ft ON ft.fee_type_id = fc_inner.fee_type_id
                WHERE s.parent_id = ? 
                    AND s.campus_id = ?
                    AND fc_inner.status = 'paid'
                    AND fc_inner.paid_date IS NOT NULL
            ) fc ON DATE_FORMAT(fc.paid_date, '%Y-%m') = DATE_FORMAT(date_table.payment_date, '%Y-%m')
            GROUP BY date_table.payment_date
            ORDER BY date_table.payment_date DESC";
    
    $query = $this->db->query($sql, [$parent_id, $campus_id]);
    
    if (!$query) {
        log_message('error', "Failed to get payment history for parent_id: $parent_id");
        return [
            'display_months' => [],
            'short_months' => [],
            'month_keys' => [],
            'monthly_totals' => [],
            'monthly_fee_totals' => [],
            'other_fee_totals' => [],
            'payments' => []
        ];
    }
    
    $results = $query->getResultArray();
    
    // Initialize arrays
    $displayMonths = [];
    $shortMonths = [];
    $monthKeys = [];
    $monthlyTotals = [];
    $monthlyFeeTotals = [];
    $otherFeeTotals = [];
    $formattedPayments = [];
    
    foreach ($results as $row) {
        $monthKey = $row['month_key'];
        $shortMonth = $row['short_month'];
        $displayMonth = $row['display_month'];
        $amount = (int)$row['family_total']; // Already rounded by SQL
        $mf = (int)($row['family_monthly_fee'] ?? 0);
        $of = (int)($row['family_other_fee'] ?? 0);
        
        $monthKeys[] = $monthKey;
        $shortMonths[] = $shortMonth;
        $displayMonths[] = $displayMonth;
        $monthlyTotals[$monthKey] = $amount;
        $monthlyFeeTotals[$monthKey] = $mf;
        $otherFeeTotals[$monthKey] = $of;
        
        $formattedPayments[] = [
            'month_key' => $monthKey,
            'short_month' => $shortMonth,
            'display_month' => $displayMonth,
            'amount' => $amount,
            'monthly_fee' => $mf,
            'other_fee' => $of,
            'students_count' => (int)$row['students_count'],
            'transactions_count' => (int)$row['transactions_count']
        ];
    }
    
    return [
        'display_months' => $displayMonths,
        'short_months' => $shortMonths,
        'month_keys' => $monthKeys,
        'monthly_totals' => $monthlyTotals,
        'monthly_fee_totals' => $monthlyFeeTotals,
        'other_fee_totals' => $otherFeeTotals,
        'payments' => $formattedPayments
    ];
}
/**
 * Five fee-table rows: 4 particulars + 1 remainder/arrears (see FeeChalanDisplayRows).
 */
private function processChalanRows(array $chalans): array
{
    return FeeChalanDisplayRows::studentRows($chalans);
}
    /**
     * Fetch family-wise chalans
     */
  /**
 * Fetch family-wise chalans
 */

 /**
 * Fetch family-wise chalans - Consolidated view showing all children
/**
 * Get family fee data grouped by month
 */

/**
 * Fetch family-wise chalans - Shows consolidated fees with student names in header only
 */

/**
 * Fetch family-wise chalans - Shows consolidated fees with student names in header only
 */


/**
 * Get family fee data grouped by fee type (particular)
 * This sums amounts across all students in the family for each fee type
 */

/**
 * Get family fee data grouped by fee type (particular)
 * Shows months instead of student count
 */
/**
 * Get family fee data grouped by fee type (particular) and month
 * Shows each particular with its month in parentheses, exactly like student chalan
 */

/**
 * Get family fee data grouped by fee type (particular) 
 * Shows each particular with its month in parentheses
 */

/**
 * Get family fee data grouped by fee type (particular)
 * Shows each particular with its month in parentheses
 */
/**
 * Get family fee data grouped by fee type (particular)
 * Shows each particular with its month in parentheses
 */
/**
 * Get family fee data grouped by fee type (particular)
 */
private function getFamilyFeeByParticular(array $studentIds, ?string $fee_month): array
{
    if (empty($studentIds)) {
        return [];
    }
    
    $studentIdList = implode(',', $studentIds);
    
    $sql = "SELECT 
                ft.fee_type_id,
                ft.fee_type_name as base_particular,
                ft.is_monthly_fee,
                fc.fee_month,
                SUM(COALESCE(fc.amount, 0)) as total_amount,
                SUM(COALESCE(fc.discount, 0)) as total_discount,
                SUM(COALESCE(fc.amount, 0) - COALESCE(fc.discount, 0)) as net_amount,
                CASE 
                    WHEN LOCATE('-', fc.fee_month) > 0 
                    THEN DATE_FORMAT(STR_TO_DATE(CONCAT(fc.fee_month, '-01'), '%Y-%m-%d'), '%m/%Y')
                    ELSE DATE_FORMAT(STR_TO_DATE(CONCAT(SUBSTRING(fc.fee_month, 4, 4), '-', SUBSTRING(fc.fee_month, 1, 2), '-01'), '%Y-%m-%d'), '%m/%Y')
                END as month_display
            FROM fee_chalan fc
            LEFT JOIN fee_type ft ON ft.fee_type_id = fc.fee_type_id
            WHERE fc.student_id IN (" . $studentIdList . ')
            AND (COALESCE(fc.amount, 0) - COALESCE(fc.discount, 0)) > 0
            AND ' . $this->feeChalanOpenStatusSql('fc');

    $params = [];

    if (!empty($fee_month)) {
        $fm = trim((string) $fee_month);
        if (preg_match('/^\d{4}-\d{2}$/', $fm)) {
            $eYm = $this->db->escape($fm);
            $eFirst = $this->db->escape($fm . '-01');
            $eLike = $this->db->escape($fm . '%');
            $sql .= " AND (fc.fee_month = {$eYm} OR fc.fee_month = {$eFirst} OR fc.fee_month LIKE {$eLike} OR DATE_FORMAT(fc.fee_month, '%Y-%m') = {$eYm})";
        } else {
            $sql .= ' AND fc.fee_month = ?';
            $params[] = $fm;
        }
    }

    $sql .= " GROUP BY ft.fee_type_id, ft.fee_type_name, ft.is_monthly_fee, fc.fee_month
              ORDER BY 
                CASE 
                    WHEN ft.is_monthly_fee = 0 THEN 1
                    WHEN ft.is_monthly_fee = 1 THEN 2
                    ELSE 3
                END,
                fc.fee_month DESC";
    
    $query = $this->db->query($sql, $params);
    
    if (!$query) {
        return [];
    }
    
    $results = $query->getResultArray();
    
    $formattedResults = [];
    foreach ($results as $row) {
        $total_amount = (float)($row['total_amount'] ?? 0);
        $total_discount = (float)($row['total_discount'] ?? 0);
        $net_amount = (float)($row['net_amount'] ?? 0);
        
        // Format the particulars label with MM/YYYY format
        $formattedResults[] = [
            'particulars_label' => $row['base_particular'] . ' (' . ($row['month_display'] ?? '') . ')',
            'total_amount' => $total_amount,
            'total_discount' => $total_discount,
            'net_amount' => $net_amount,
            'month_display' => $row['month_display'] ?? '',
            'is_monthly_fee' => (int) ($row['is_monthly_fee'] ?? 0),
        ];
    }
    
    return $formattedResults;
}
private function processFamilyFeeRows(array $feeByParticular): array
{
    return FeeChalanDisplayRows::familyRows($feeByParticular);
}

/**
 * Non-monthly fee lines first, then monthly (same order as legacy PDF view).
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
 * Get latest chalan date for family
 */
private function getLatestChalanDateForFamily(array $studentIds): array
{
    if (empty($studentIds)) {
        return [];
    }
    
    $studentIdList = implode(',', $studentIds);
    
    $sql = "SELECT chalan_id, issue_date, due_date 
            FROM fee_chalan 
            WHERE student_id IN (" . $studentIdList . ")
            ORDER BY issue_date DESC 
            LIMIT 1";
    
    $query = $this->db->query($sql);
    
    if (!$query || $query->getNumRows() == 0) {
        return [];
    }
    
    $result = $query->getRowArray();
    
    if (!empty($result)) {
        $result['issue_date'] = !empty($result['issue_date']) 
            ? date('d-m-Y', strtotime($result['issue_date'])) 
            : date('d-m-Y');
        $result['due_date'] = !empty($result['due_date']) 
            ? date('d-m-Y', strtotime($result['due_date'])) 
            : date('d-m-Y', strtotime('+10 days'));
    }
    
    return $result ?? [];
}
/**
 * Process family fee rows to create exactly 7 display rows
 */
/**
 * Process family fee rows to create exactly 7 display rows (like student chalan)
 */
/**
 * Process family fee rows to create exactly 7 display rows
 */

/**
 * Process family fee rows to create exactly 7 display rows
 * Ensure rows have the correct keys for the template (amount, discount, etc.)
 */

/**
 * Get latest chalan date for family
 */

/**
 * Get latest chalan date for family
 * FIXED: Removed campus_id which doesn't exist in fee_chalan table
 */

private function getFamilyFeeByMonth(array $studentIds, ?string $fee_month): array
{
    if (empty($studentIds)) {
        return [];
    }
    
    $studentIdList = implode(',', $studentIds);
    
    // Build the SQL query safely
    $sql = "SELECT 
                fc.fee_month,
                COUNT(DISTINCT fc.student_id) as student_count,
                GROUP_CONCAT(DISTINCT CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) SEPARATOR ', ') as student_names,
                SUM(COALESCE(fc.amount, 0) - COALESCE(fc.discount, 0)) as total_amount,
                SUM(COALESCE(fc.discount, 0)) as total_discount
            FROM fee_chalan fc
            INNER JOIN students s ON s.student_id = fc.student_id
            WHERE fc.student_id IN (" . $studentIdList . ')
            AND (COALESCE(fc.amount, 0) - COALESCE(fc.discount, 0)) > 0
            AND ' . $this->feeChalanOpenStatusSql('fc');

    $params = [];

    if (!empty($fee_month)) {
        $fm = trim((string) $fee_month);
        if (preg_match('/^\d{4}-\d{2}$/', $fm)) {
            $eYm = $this->db->escape($fm);
            $eFirst = $this->db->escape($fm . '-01');
            $eLike = $this->db->escape($fm . '%');
            $sql .= " AND (fc.fee_month = {$eYm} OR fc.fee_month = {$eFirst} OR fc.fee_month LIKE {$eLike} OR DATE_FORMAT(fc.fee_month, '%Y-%m') = {$eYm})";
        } else {
            $sql .= ' AND fc.fee_month = ?';
            $params[] = $fm;
        }
    }

    $sql .= " GROUP BY fc.fee_month
              ORDER BY 
                CASE 
                    WHEN fc.fee_month LIKE '____-__' THEN fc.fee_month
                    ELSE CONCAT(SUBSTRING_INDEX(fc.fee_month, '-', -1), '-', SUBSTRING_INDEX(fc.fee_month, '-', 1))
                END DESC";
    
    $query = $this->db->query($sql, $params);
    
    if (!$query) {
        log_message('error', 'Failed to get family fee by month: ' . $this->db->getLastQuery());
        return [];
    }
    
    $results = $query->getResultArray();
    
    // Format the results
    foreach ($results as &$row) {
        $row['fee_month_label'] = $this->formatFeeMonthLabel($row['fee_month'] ?? '');
        $row['total_amount_formatted'] = number_format($row['total_amount'] ?? 0, 0);
        $row['total_discount_formatted'] = ($row['total_discount'] ?? 0) > 0 ? number_format($row['total_discount'], 0) : '';
    }
    
    return $results;
}

/**
 * Get family fee data grouped by month
 */



    /**
     * Get family students with their chalans
     */
   /**
 * Get family students with their chalans
 */
private function getFamilyStudentsWithChalans(int $parent_id, int $campus_id, int $session_id, array $params): array
{
    $builder = $this->db->table('students s');
    $builder->select("
        s.student_id,
        TRIM(CONCAT_WS(' ', TRIM(s.first_name), NULLIF(TRIM(s.last_name), ''))) AS student_name,
        s.reg_no,
        cs.class_id,
        c.class_name,
        c.class_short_name,
        sec.short_name AS section_short_name
    ");

    $builder->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . $session_id . ' AND sc.status = 1', 'inner');
    $builder->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'inner');
    $builder->join('classes c', 'c.class_id = cs.class_id', 'left');
    $builder->join('sections sec', 'sec.section_id = cs.section_id', 'left');

    $builder->where('s.parent_id', $parent_id);
    $builder->where('s.campus_id', $campus_id);
    $builder->where('s.status', 1);

    $query = $builder->get();
    
    if (!$query) {
        log_message('error', 'Failed to get family students: ' . $this->db->getLastQuery());
        return [];
    }
    
    $students = $query->getResultArray();

    // We'll process chalans in the parent method
    return $students;
}

    /**
     * Get unpaid chalans for a student
     */
    /**
     * Only unpaid rows should print on challan generate.
     */
    private function feeChalanOpenStatusSql(string $tableAlias = 'fc'): string
    {
        // Match FeeChalanPay: only unpaid lines are payable / printable (not "discounted").
        $norm = 'REPLACE(REPLACE(LOWER(TRIM(COALESCE(' . $tableAlias . ".status,''))), ' ', ''), '-', '')";

        return '(' . $norm . " = 'unpaid'"
            . " OR {$tableAlias}.status IN ('unpaid','UnPaid','UNPAID','UNPaid'))";
    }

    /**
     * Match fee_month from UI (YYYY-MM) to common DB shapes: YYYY-MM, YYYY-MM-DD, or date column.
     */
    private function applyFeeChalanMonthFilter(\CodeIgniter\Database\BaseBuilder $builder, string $feeMonth): void
    {
        $fm = trim($feeMonth);
        if ($fm === '') {
            return;
        }
        if (preg_match('/^\d{4}-\d{2}$/', $fm)) {
            $eYm = $this->db->escape($fm);
            $eFirst = $this->db->escape($fm . '-01');
            $eLike = $this->db->escape($fm . '%');
            $builder->where(
                "(fc.fee_month = {$eYm} OR fc.fee_month = {$eFirst} OR fc.fee_month LIKE {$eLike} OR DATE_FORMAT(fc.fee_month, '%Y-%m') = {$eYm})",
                null,
                false
            );

            return;
        }
        $builder->where('fc.fee_month', $fm);
    }

/**
 * Unpaid chalans for many students in one (chunked) query; keys are student_id.
 */
private function getStudentUnpaidChalansBatch(array $studentIds, ?string $fee_month, bool $show_discount): array
{
    $studentIds = array_values(array_unique(array_filter(array_map('intval', $studentIds), static fn ($id) => $id > 0)));
    if ($studentIds === []) {
        return [];
    }

    $out = array_fill_keys($studentIds, []);
    $chunkSize = 400;

    foreach (array_chunk($studentIds, $chunkSize) as $chunk) {
        $builder = $this->db->table('fee_chalan fc');
        $builder->select("
            fc.student_id,
            fc.chalan_id,
            fc.fee_type_id,
            fc.fee_month,
            fc.issue_date,
            fc.due_date,
            fc.amount,
            fc.discount,
            (COALESCE(fc.amount, 0) - COALESCE(fc.discount, 0)) AS net_amount,
            ft.fee_type_name as particulars_label,
            ft.is_monthly_fee,
            fc.status
        ");
        $builder->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id', 'left');
        $builder->whereIn('fc.student_id', $chunk);
        $builder->where($this->feeChalanOpenStatusSql('fc'), null, false);

        if ($fee_month !== null && $fee_month !== '') {
            $this->applyFeeChalanMonthFilter($builder, (string) $fee_month);
        }

        $builder->orderBy('fc.student_id', 'ASC');
        $builder->orderBy('fc.fee_month', 'DESC');
        $builder->orderBy('fc.issue_date', 'DESC');

        $query = $builder->get();
        if (!$query) {
            continue;
        }

        foreach ($query->getResultArray() as $row) {
            $sid = (int) ($row['student_id'] ?? 0);
            if ($sid === 0) {
                continue;
            }
            unset($row['student_id']);
            $this->decorateChalanRow($row, $show_discount);
            $labelNorm = strtolower(trim((string) ($row['particulars_label'] ?? '')));
            // Hide fine / late-fee lines (legacy rows may have fee_type_id 0 — still show if not a fine label).
            if (strpos($labelNorm, 'fine') !== false || strpos($labelNorm, 'late fee') !== false) {
                continue;
            }
            // Only lines that still owe (amount minus discount > 0) belong on a payable challan
            if ((float) ($row['net_amount'] ?? 0) <= 0) {
                continue;
            }
            $out[$sid][] = $row;
        }
    }

    return $out;
}

private function decorateChalanRow(array &$chalan, bool $show_discount): void
{
    $amt  = (float) ($chalan['amount'] ?? 0);
    $disc = $chalan['discount'] ?? 0;
    if ($disc === '' || $disc === null) {
        $disc = 0.0;
    }
    $disc = (float) $disc;
    $chalan['amount']     = $amt;
    $chalan['discount']   = $disc;
    $chalan['net_amount'] = $amt - $disc;

    $chalan['issue_date_label'] = !empty($chalan['issue_date'])
        ? date('d-m-y', strtotime((string) $chalan['issue_date']))
        : '';
    $chalan['due_date_label'] = !empty($chalan['due_date'])
        ? date('d-m-y', strtotime((string) $chalan['due_date']))
        : '';

    $chalan['fee_month_label'] = $this->formatFeeMonthLabel($chalan['fee_month'] ?? '');

    $chalan['amount_formatted'] = number_format((float) ($chalan['amount'] ?? 0), 0);
    $chalan['discount_formatted'] = $show_discount
        ? number_format((float) $chalan['discount'], 0)
        : '';
    $chalan['net_amount_formatted'] = number_format($chalan['net_amount'], 0);
}

private function getStudentUnpaidChalans(int $student_id, ?string $fee_month, bool $show_discount): array
{
    $map = $this->getStudentUnpaidChalansBatch([$student_id], $fee_month, $show_discount);

    return $map[$student_id] ?? [];
}

/**
 * Last 12 calendar months of paid totals per student (batch).
 */
private function getStudentPaymentHistoryBatch(array $studentIds): array
{
    $studentIds = array_values(array_unique(array_filter(array_map('intval', $studentIds), static fn ($id) => $id > 0)));
    if ($studentIds === []) {
        return [];
    }

    $monthSlots = [];
    for ($i = 11; $i >= 0; $i--) {
        $monthSlots[] = date('Y-m', strtotime('-' . $i . ' months'));
    }

    $agg = [];
    $chunkSize = 400;

    foreach (array_chunk($studentIds, $chunkSize) as $chunk) {
        $placeholders = implode(',', array_fill(0, count($chunk), '?'));
        $sql          = "SELECT fc.student_id,
                DATE_FORMAT(fc.paid_date, '%Y-%m') AS month_key,
                COALESCE(ROUND(SUM(CASE WHEN COALESCE(ft.is_monthly_fee, 0) = 1 THEN COALESCE(fc.amount,0) - COALESCE(fc.discount,0) ELSE 0 END), 0), 0) AS monthly_fee_total,
                COALESCE(ROUND(SUM(CASE WHEN COALESCE(ft.is_monthly_fee, 0) = 0 THEN COALESCE(fc.amount,0) - COALESCE(fc.discount,0) ELSE 0 END), 0), 0) AS other_fee_total
            FROM fee_chalan fc
            LEFT JOIN fee_type ft ON ft.fee_type_id = fc.fee_type_id
            WHERE fc.student_id IN ({$placeholders})
                AND fc.fee_type_id > 0
                AND fc.status = 'paid'
                AND fc.paid_date IS NOT NULL
            GROUP BY fc.student_id, DATE_FORMAT(fc.paid_date, '%Y-%m')";

        $q = $this->db->query($sql, $chunk);
        if (!$q) {
            continue;
        }
        foreach ($q->getResultArray() as $row) {
            $sid = (int) $row['student_id'];
            $mk  = $row['month_key'];
            $agg[$sid][$mk] = [
                'm' => (int) $row['monthly_fee_total'],
                'o' => (int) $row['other_fee_total'],
            ];
        }
    }

    $out = [];
    foreach ($studentIds as $sid) {
        $monthKeys          = [];
        $monthlyTotals      = [];
        $monthlyFeeTotals   = [];
        $otherFeeTotals     = [];
        foreach ($monthSlots as $mk) {
            $monthKeys[] = $mk;
            $m           = $agg[$sid][$mk]['m'] ?? 0;
            $o           = $agg[$sid][$mk]['o'] ?? 0;
            $monthlyFeeTotals[$mk] = $m;
            $otherFeeTotals[$mk]   = $o;
            $monthlyTotals[$mk]    = $m + $o;
        }
        $out[$sid] = [
            'month_keys'           => $monthKeys,
            'monthly_totals'       => $monthlyTotals,
            'monthly_fee_totals'   => $monthlyFeeTotals,
            'other_fee_totals'     => $otherFeeTotals,
        ];
    }

    return $out;
}
    /**
     * Format fee month label
     */
    private function formatFeeMonthLabel(?string $fee_month): ?string
    {
        if (empty($fee_month)) return null;
        
        $parts = explode('-', $fee_month);
        if (count($parts) === 2) {
            if ((int)$parts[0] > 12) {
                return sprintf('%02d/%04d', $parts[1], $parts[0]);
            }
            return sprintf('%02d/%04d', $parts[0], $parts[1]);
        }
        return $fee_month;
    }

    private function parseChalanDateInput(?string $raw): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $fmt) {
            $dt = DateTime::createFromFormat($fmt, $raw);
            if ($dt instanceof DateTime) {
                $errs = DateTime::getLastErrors();
                if (is_array($errs) && (($errs['error_count'] ?? 0) > 0 || ($errs['warning_count'] ?? 0) > 0)) {
                    continue;
                }

                return $dt->format('Y-m-d');
            }
        }
        $ts = strtotime($raw);

        return $ts !== false ? date('Y-m-d', $ts) : null;
    }

    private function normalizeFeeMonthYm(?string $raw): string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return date('Y-m');
        }
        if (preg_match('/^(\d{4})-(\d{2})/', $raw, $m)) {
            return $m[1] . '-' . $m[2];
        }
        if (preg_match('/^(\d{1,2})[\/\-](\d{4})$/', $raw, $m)) {
            return sprintf('%04d-%02d', (int) $m[2], (int) $m[1]);
        }

        return date('Y-m');
    }

    /**
     * Get sections for dropdown
     */
  /**
 * Get sections for dropdown
 */
public function getSections()
{
    $campus_id = (int) session()->get('member_campusid');
    
    $builder = $this->db->table('class_section cs');
    $builder->select("
        cs.cls_sec_id,
        cs.section_id,
        CONCAT(c.class_name, ' - ', sec.section_name) as sectionclassname
    ");
    $builder->join('classes c', 'c.class_id = cs.class_id');
    $builder->join('sections sec', 'sec.section_id = cs.section_id');
    $builder->where('cs.campus_id', $campus_id);
    $builder->orderBy('c.class_name, sec.section_name');
    
    $query = $builder->get();
    
    if (!$query) {
        log_message('error', 'Failed to get sections: ' . $this->db->getLastQuery());
        return [];
    }
    
    return $query->getResultArray();
}

    /**
     * Get classes for filter dropdown
     */
   /**
 * Get classes for filter dropdown
 */
/**
 * Get classes for filter dropdown
 */

/**
 * Get classes for filter dropdown
 */
/**
 * Get classes for filter dropdown
 */

/**
 * Get classes for filter dropdown
 */
private function getClasses(int $campus_id): array
{
    // Use simple string concatenation instead of builder for complex queries
    $sql = "SELECT DISTINCT cs.class_id, c.class_name 
            FROM class_section cs 
            JOIN classes c ON c.class_id = cs.class_id 
            WHERE cs.campus_id = ? 
            ORDER BY c.class_name";
    
    $query = $this->db->query($sql, [$campus_id]);
    
    if (!$query) {
        log_message('error', 'Failed to get classes');
        return [];
    }
    
    return $query->getResultArray();
}

/**
 * AJAX endpoint to get sections for a specific class
 */
/**
 * AJAX endpoint to get sections for a specific class
 */
public function getSectionsByClass()
{
    $class_id = $this->request->getGet('class_id');
    $campus_id = (int) session()->get('member_campusid');
    
    if (empty($class_id)) {
        return $this->response->setJSON([]);
    }
    
    $builder = $this->db->table('class_section cs');
    $builder->select('cs.cls_sec_id, sec.section_name');
    $builder->join('sections sec', 'sec.section_id = cs.section_id');
    $builder->where('cs.campus_id', $campus_id);
    $builder->where('cs.class_id', $class_id);
    $builder->orderBy('sec.section_name');
    
    $query = $builder->get();
    
    if (!$query) {
        log_message('error', 'Failed to get sections by class: ' . $this->db->getLastQuery());
        return $this->response->setJSON([]);
    }
    
    return $this->response->setJSON($query->getResultArray());
}

    /**
     * AJAX endpoint for student search
     */
   /**
 * AJAX endpoint for student search
 */

  /**
 * AJAX endpoint for student search
 */

  /**
 * Check if request is AJAX
 */
protected function isAjax(): bool
{
    return $this->request->isAJAX();
}

public function searchStudents()
{
    // Set header to JSON
    $this->response->setContentType('application/json');
    
    try {
        // Get parameters from POST or GET (for testing)
        $term = $this->request->getPost('term') ?? $this->request->getGet('term');
        $class_id = $this->request->getPost('class_id') ?? $this->request->getGet('class_id');
        $section_id = $this->request->getPost('section_id') ?? $this->request->getGet('section_id');
        
        $campus_id = (int) session()->get('member_campusid');
        $session_id = (int) session()->get('member_sessionid');

        log_message('debug', 'searchStudents called with term: ' . $term);

        // Return empty array if term is too short
        if (strlen($term) < 3) {
            return $this->response->setJSON([]);
        }

        $builder = $this->db->table('students s');
        $builder->select("
            s.student_id as id,
            s.reg_no,
            CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) AS student_name,
            p.f_name AS father_name,
            c.class_name,
            sec.section_name,
            p.parent_id,
            p.father_cnic,
            CONCAT(s.first_name, ' ', COALESCE(s.last_name, ''), ' (', s.reg_no, ')') as text
        ");

        $builder->join('parents p', 'p.parent_id = s.parent_id', 'left');
        $builder->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . $session_id . ' AND sc.status = 1', 'inner');
        $builder->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'inner');
        $builder->join('classes c', 'c.class_id = cs.class_id', 'left');
        $builder->join('sections sec', 'sec.section_id = cs.section_id', 'left');

        $builder->where('s.campus_id', $campus_id);
        $builder->where('s.status', 1);

        // Apply class and section filters
        if (!empty($class_id)) {
            $builder->where('c.class_id', $class_id);
        }
        if (!empty($section_id)) {
            $builder->where('sec.section_id', $section_id);
        }

        // Search by name, reg no, or CNIC
        if (!empty($term)) {
            $builder->groupStart();
            
            // Search in first_name
            $builder->orGroupStart();
            $builder->like('s.first_name', $term);
            $builder->groupEnd();
            
            // Search in last_name
            $builder->orGroupStart();
            $builder->like('s.last_name', $term);
            $builder->groupEnd();
            
            // Search in full name (concatenated)
            $builder->orGroupStart();
            $builder->like('CONCAT(s.first_name, " ", s.last_name)', $term);
            $builder->groupEnd();
            
            // Search in registration number
            $builder->orGroupStart();
            $builder->like('s.reg_no', $term);
            $builder->groupEnd();
            
            // Search in father's name
            $builder->orGroupStart();
            $builder->like('p.f_name', $term);
            $builder->groupEnd();
            
            // Search in father's CNIC
            $builder->orGroupStart();
            $builder->like('p.father_cnic', $term);
            $builder->groupEnd();
            
            $builder->groupEnd();
        }

        $builder->limit(20);
        
        $query = $builder->get();
        
        if (!$query) {
            log_message('error', 'Student search query failed: ' . $this->db->getLastQuery());
            return $this->response->setJSON([]);
        }
        
        $results = $query->getResultArray();
        log_message('debug', 'Found ' . count($results) . ' results');
        log_message('debug', 'Last query: ' . $this->db->getLastQuery());
        
        // Format results for Select2
        $formatted = array_map(function($r) {
            return [
                'id' => $r['id'],
                'text' => $r['text'],
                'student_name' => $r['student_name'],
                'father_name' => $r['father_name'],
                'reg_no' => $r['reg_no'],
                'class_name' => $r['class_name'] ?? '',
                'section_name' => $r['section_name'] ?? '',
                'parent_id' => $r['parent_id'] ?? 0,
                'father_cnic' => $r['father_cnic'] ?? ''
            ];
        }, $results);

        return $this->response->setJSON($formatted);
        
    } catch (\Exception $e) {
        log_message('error', 'Search exception: ' . $e->getMessage());
        log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        return $this->response->setJSON(['error' => $e->getMessage()]);
    }
}

    /**
     * POST alias for bulk challan SSE (same handler as bulk_chalan_stream).
     */
    public function bulkChalanGeneration()
    {
        return $this->bulk_chalan_stream();
    }

    /**
     * AJAX endpoint for family search
     */
   /**
 * AJAX endpoint for family search
 */

   /**
 * AJAX endpoint for family search
 */
public function searchFamilies()
{
    // Ensure this is an AJAX request
    if (!$this->request->isAJAX()) {
        return $this->response->setJSON([]);
    }
    
    $term = $this->request->getPost('term');
    $campus_id = (int) session()->get('member_campusid');

    // Return empty array if term is too short
    if (strlen($term) < 3) {
        return $this->response->setJSON([]);
    }

    try {
        $builder = $this->db->table('parents p');
        $builder->select("
            p.parent_id as id,
            p.f_name,
            p.father_cnic,
            p.father_contact,
            COUNT(s.student_id) as student_count,
            GROUP_CONCAT(DISTINCT CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) SEPARATOR ', ') as student_names,
            CONCAT(
                p.f_name, ' (ID: ', p.parent_id, ')',
                COALESCE(CONCAT(' - CNIC: ', p.father_cnic), ''),
                COALESCE(CONCAT(' - ', p.father_contact), ''),
                ' - ', COUNT(s.student_id), ' students',
                COALESCE(CONCAT(' | Children: ', GROUP_CONCAT(DISTINCT CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) SEPARATOR ', ')), '')
            ) as text
        ");

        $builder->join('students s', 's.parent_id = p.parent_id AND s.campus_id = ' . $campus_id . ' AND s.status = 1', 'left');
        $builder->where('p.campus_id', $campus_id);

        // Search by father name, CNIC, or contact
        if (!empty($term)) {
            $builder->groupStart()
                ->like('p.f_name', $term)
                ->orLike('p.father_cnic', $term)
                ->orLike('p.father_contact', $term)
                ->groupEnd();
        }

        $builder->groupBy('p.parent_id');
        $builder->having('student_count > 0');
        $builder->limit(20);
        
        $query = $builder->get();
        
        if (!$query) {
            log_message('error', 'Family search query failed: ' . $this->db->getLastQuery());
            return $this->response->setJSON([]);
        }
        
        $results = $query->getResultArray();

        $formatted = array_map(function($r) {
            return [
                'id' => $r['id'],
                'text' => $r['text'],
                'parent_id' => $r['id']
            ];
        }, $results);

        return $this->response->setJSON($formatted);
        
    } catch (\Exception $e) {
        log_message('error', 'Family search exception: ' . $e->getMessage());
        return $this->response->setJSON([]);
    }
}


    // Keep your existing methods for backward compatibility
    public function threeCopyPdf()
    {
        return $this->renderChalan('admin/chalanview/fee_chalan_pdf');
    }

    public function thermalCopy()
    {
        return $this->renderChalan('admin/chalanview/fee_chalan_thermal');
    }

    public function singleCopy()
    {
        return $this->renderChalan('admin/chalanview/fee_chalan_single');
    }

    public function withoutDiscount()
    {
        return $this->renderChalan('admin/chalanview/fee_chalan_without_discount');
    }

    public function familywise()
    {
        return $this->renderChalan('admin/chalanview/fee_chalan_familywise', true);
    }

    public function familywiseSingleCopy()
    {
        return $this->renderChalan('admin/chalanview/fee_chalan_familywise_single', true);
    }

    public function withHeader()
    {
        return $this->renderChalan('admin/chalanview/fee_chalan_with_header');
    }

    private function renderChalan(string $viewName, bool $isFamilywise = false)
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
                : $this->fetchChalanData(false, $cls_sec_id, $fee_month)
        ];

        echo view($viewName, $data);
        exit;
    }

    // Add these methods if they don't exist (from your original code)
    private function fetchChalanData(
        bool $isFamilywise = false,
        ?int $cls_sec_id = null,
        ?string $fee_month = null
    ): array
    {
        if ($isFamilywise) return $this->fetchFamilywiseChalanData();

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
                                   THEN STR_TO_DATE(CONCAT(fc.fee_month, '-01'), '%Y-%m-%d')
                              ELSE STR_TO_DATE(CONCAT(fc.fee_month, '-01'), '%m-%Y-%d')
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
        c.class_short_name,
        sec.short_name AS section_short_name,
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
        $builder->join('sections sec',     'sec.section_id = cs.section_id', 'left');
        $builder->join('campus cm',        'cm.campus_id  = s.campus_id',    'left');
        $builder->join('system sys',       'sys.system_id = cm.system_id',   'left');
        $builder->join("($lastEntrySub) lc", 'lc.student_id = s.student_id', 'left');

        $builder->where('s.status', 1);
        $builder->where('sc.session_id', $session_id);
        $builder->where('s.campus_id', $campus_id);

        if (!empty($cls_sec_id)) {
            $builder->where('sc.cls_sec_id', $cls_sec_id);
        }

        $builder->groupBy('s.student_id');

        $query = $builder->get();
        
        if (!$query) {
            return [];
        }
        
        $students = $query->getResultArray();

        // Campus-level late fee setup (optional)
        $late = $this->db->table('campus')
            ->select('late_fee_fine, fine_type')
            ->where('campus_id', $campus_id)
            ->get()->getRow();

        // For each student, attach ALL unpaid records (body) and pretty labels for header
        foreach ($students as $k => &$row) {
            $row['student_name'] = fee_chalan_student_display_name(
                $row['student_name'] ?? '',
                $row['reg_no'] ?? null
            );

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

            // All UNPAID chalans for this student (latest first)
            $unpaid = $this->getStudentUnpaidChalans((int)$row['student_id'], '', true);

            // Drop zero/negative rows (amount - discount <= 0)
            $unpaid = array_values(array_filter($unpaid, static function($r) {
                $net = (float)($r['net_amount'] ?? ((float)($r['amount'] ?? 0) - (float)($r['discount'] ?? 0)));
                return $net > 0;
            }));

            // Total payable across unpaid (+ monthly vs other split)
            $totalAll      = 0.0;
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

            $unpaid                      = $this->sortUnpaidChalansForDisplay($unpaid);
            $row['unpaid_rows']          = $unpaid;
            $row['unpaid_display_rows']  = $this->processChalanRows($unpaid);
            $row['unpaid_total_payable'] = $totalAll;
            $row['unpaid_payable_monthly'] = $payableMonthly;
            $row['unpaid_payable_other']   = $payableOther;
        }
        unset($row);

        return array_values($students);
    }

    private function fetchFamilywiseChalanData()
    {
        // Implement if needed
        return [];
    }

public function add()
{
    $db = \Config\Database::connect();
    $campusInfo = getCampusInfo();
    $schoolInfo = getSchoolInfo();
    $system_id  = (int) ($schoolInfo->system_id ?? 0);
    $campus_id = (int) session()->get('member_campusid');

    // Log for debugging
    log_message('debug', "ADD METHOD: campus_id = $campus_id, system_id = $system_id");

    // ========== DYNAMIC FEE MONTH LOGIC ==========
    $currentDay = (int) date('d');
    
    if ($currentDay >= 15 && $currentDay <= 31) {
        // 15th to 31st: Show next month
        $fee_month_val = date('Y-m', strtotime('+1 month'));
        $fee_month_display = date('F Y', strtotime('+1 month'));
        $fee_month_note = "";
    } else {
        // 1st to 14th: Show current month
        $fee_month_val = date('Y-m');
        $fee_month_display = date('F Y');
        $fee_month_note = "Auto-selected current month (" . $fee_month_display . ") because today is before 15th.";
    }
    
    // Set fine month (optional - default empty)
    $fine_month_val = '';
    // ========== END DYNAMIC FEE MONTH LOGIC ==========

    // Dropdown data
    $fee_type_info = $db->table('fee_type')
        ->where('s_flag', 1)
        ->where('system_id', $system_id)
        ->where('status', 1)
        ->get()
        ->getResult();
        
    // Get classes for dropdown
    $classes = [];
    try {
        $classesQuery = $db->table('class_section cs')
            ->select('cs.class_id, c.class_name')
            ->distinct()
            ->join('classes c', 'c.class_id = cs.class_id', 'inner')
            ->where('cs.campus_id', $campus_id)
            ->where('cs.status', 1)
            ->orderBy('c.class_name', 'ASC')
            ->get();
        
        if ($classesQuery) {
            $classes = $classesQuery->getResultArray();
        }
        log_message('debug', "Found " . count($classes) . " classes");
    } catch (\Exception $e) {
        log_message('error', "Error fetching classes: " . $e->getMessage());
    }

    // Get sections for dropdown
    $sectionsclassinfo = [];
    try {
        $sectionsQuery = $db->table('class_section cs')
            ->select('cs.cls_sec_id, cs.section_id, cs.class_id, CONCAT(c.class_name, " - ", sec.section_name) as sectionclassname')
            ->join('classes c', 'c.class_id = cs.class_id', 'inner')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'inner')
            ->where('cs.campus_id', $campus_id)
            ->where('cs.status', 1)
            ->orderBy('c.class_name', 'ASC')
            ->orderBy('sec.section_name', 'ASC')
            ->get();
        
        if ($sectionsQuery) {
            $sectionsclassinfo = $sectionsQuery->getResultArray();
        }
        log_message('debug', "Found " . count($sectionsclassinfo) . " sections");
    } catch (\Exception $e) {
        log_message('error', "Error fetching sections: " . $e->getMessage());
    }

    // ========== ADD DEFAULT DATE VALUES ==========
    // Set default issue date (current date)
    $issue_date_val = date('d/m/Y');
    // Set default due date (current date + 10 days)
    $due_date_val = date('d/m/Y', strtotime('+10 days'));
    // ========== END DEFAULT DATE VALUES ==========

    // ========== Get Today's Generated Chalans ==========
    $today_chalans = $this->getTodayChalansWithAdmissionLogic();
    // ========== END ==========

    // Get base URL for form action
    $base_url = base_url('admin/fee-chalan/save');

    $data = [
        'mode'                  => 'add',
        'isEdit'                => false,
        'pageTitle'             => 'Generate Fee Chalan',
        'campusInfo'            => $campusInfo,
        'fee_type_info'         => $fee_type_info,
        'a_fee_type_info'       => [],
        't_fee_type_info'       => [],
        'fee_chalan'            => null,
        'selected_fee_type_ids' => [],
        'classes'               => $classes,
        'sectionsclassinfo'     => $sectionsclassinfo,
        'base_url'              => $base_url,
        'today_chalans'         => $today_chalans,
        'issue_date_val'        => $issue_date_val,
        'due_date_val'          => $due_date_val,
        'fee_month_val'         => $fee_month_val,
        'fee_month_display'     => $fee_month_display,
        'fee_month_note'        => $fee_month_note,
        'fine_month_val'        => $fine_month_val,  // ← ADD THIS - FIXED!
        'selected_view'         => 'student_three_copy',
        'show_discount'         => 'yes',
        'family_id'             => '',
        'class_id'              => '',
        'section_id'            => '',
        'search'                => '',
        'footer_line1'          => '',
        'footer_line2'          => '',
        'show_line1'            => 0,
        'show_line2'            => 0,
        'fine_after_due_date'   => 0,
        'show_payment_history'  => 0,
    ];

    return view('admin/fee_chalan_add', $data);
}
/**
 * Get today's chalans with newly admitted students highlighted
 */
public function getTodayChalansWithAdmissionLogic()
{
    $db = \Config\Database::connect();
    $today = date('Y-m-d');
    $campus_id = (int) session()->get('member_campusid');
    $currentDay = (int) date('d');
    
    try {
        // Determine the admission date filter based on current day
        $admissionFilter = $this->getAdmissionDateFilter($currentDay);
        
        // Get chalans with student admission details
        $sql = "
            SELECT 
                fc.chalan_id,
                fc.student_id,
                fc.invoice_no,
                fc.fee_month,
                fc.fee_type_id,
                fc.amount,
                fc.created_date,
                fc.status,
                fc.due_date,
                fc.issue_date,
                s.first_name,
                s.last_name,
                s.reg_no,
                s.date_of_admission,
                ft.fee_type_name,
                c.class_name,
                sec.section_name,
                CASE 
                    WHEN s.date_of_admission >= ? THEN 1 
                    ELSE 0 
                END as is_new_admission,
                DATEDIFF(fc.created_date, s.date_of_admission) as days_since_admission
            FROM fee_chalan fc
            INNER JOIN students s ON s.student_id = fc.student_id
            INNER JOIN fee_type ft ON ft.fee_type_id = fc.fee_type_id
            LEFT JOIN student_class sc ON sc.student_id = fc.student_id AND sc.status = 1
            LEFT JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
            LEFT JOIN classes c ON c.class_id = cs.class_id
            LEFT JOIN sections sec ON sec.section_id = cs.section_id
            WHERE DATE(fc.created_date) = ?
            AND s.campus_id = ?
            ORDER BY is_new_admission DESC, s.date_of_admission DESC, fc.created_date DESC
        ";
        
        $result = $db->query($sql, [$admissionFilter, $today, $campus_id])->getResult();
        
        // Process results
        $chalans = [];
        $newAdmissions = [];
        $existingAdmissions = [];
        
        foreach ($result as $row) {
            $chalan = $this->buildChalanObject($row);
            
            if ($row->is_new_admission == 1) {
                $newAdmissions[] = $chalan;
            } else {
                $existingAdmissions[] = $chalan;
            }
        }
        
        // Combine with new admissions first
        $chalans = array_merge($newAdmissions, $existingAdmissions);
        
        log_message('debug', "Found " . count($chalans) . " chalans - New: " . count($newAdmissions) . ", Existing: " . count($existingAdmissions));
        return $chalans;
        
    } catch (\Exception $e) {
        log_message('error', "getTodayChalansWithAdmissionLogic error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get admission date filter based on chalan generation day
 */
private function getAdmissionDateFilter($currentDay)
{
    // If chalan generated between 20th and 31st of month
    if ($currentDay >= 20 && $currentDay <= 31) {
        // Show admissions from current month only
        $admissionFilter = date('Y-m-01');
        log_message('debug', "Admission filter (20-31): From " . $admissionFilter);
        return $admissionFilter;
    } 
    // If chalan generated between 1st and 10th of month
    elseif ($currentDay >= 1 && $currentDay <= 10) {
        // Show admissions from current month and previous month
        $currentMonthStart = date('Y-m-01');
        $previousMonthStart = date('Y-m-01', strtotime('-1 month'));
        log_message('debug', "Admission filter (1-10): From " . $previousMonthStart . " onwards");
        return $previousMonthStart;
    } 
    // For days 11-19, show last 30 days admissions
    else {
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        log_message('debug', "Admission filter (11-19): From " . $thirtyDaysAgo);
        return $thirtyDaysAgo;
    }
}

/**
 * Build chalan object from database row
 */
private function buildChalanObject($row)
{
    $chalan = new stdClass();
    $chalan->chalan_id = $row->chalan_id;
    $chalan->student_id = $row->student_id;
    $chalan->invoice_no = $row->invoice_no ?? '';
    $chalan->fee_month = $row->fee_month ?? '';
    $chalan->fee_type_id = $row->fee_type_id;
    $chalan->amount = $row->amount ?? 0;
    $chalan->created_date = $row->created_date;
    $chalan->status = $row->status ?? '';
    $chalan->due_date = $row->due_date ?? '';
    $chalan->issue_date = $row->issue_date ?? '';
    $chalan->date_of_admission = $row->date_of_admission ?? '';
    $chalan->days_since_admission = $row->days_since_admission ?? 0;
    $chalan->is_new_admission = $row->is_new_admission ?? 0;
    
    // Build student name (with registration on challans)
    $firstName = $row->first_name ?? '';
    $lastName = $row->last_name ?? '';
    $baseName = trim($firstName . ' ' . $lastName);
    if ($baseName === '') {
        $baseName = 'Student #' . $row->student_id;
    }
    $chalan->student_name = fee_chalan_student_display_name($baseName, $row->reg_no ?? null);
    
    // Fee type name
    $chalan->fee_type_name = $row->fee_type_name ?? 'Fee Type #' . $row->fee_type_id;
    
    // Build class display
    $className = $row->class_name ?? '';
    $sectionName = $row->section_name ?? '';
    if (!empty($className)) {
        $chalan->class_display = $className;
        if (!empty($sectionName)) {
            $chalan->class_display .= ' (' . $sectionName . ')';
        }
    } else {
        $chalan->class_display = 'Class not assigned';
    }
    
    return $chalan;
}
// Get today's generated chalans

// Delete single chalan

public function delete()
{
    $db = \Config\Database::connect();
    $chalan_id = $this->request->getPost('chalan_id');
    $campus_id = (int) session()->get('member_campusid');
    
    // Log the request
    log_message('debug', "Delete request - chalan_id: $chalan_id, campus_id: $campus_id");
    
    if (!$chalan_id) {
        log_message('error', "Delete failed: No chalan_id provided");
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Chalan ID is required'
        ]);
    }
    
    try {
        $db->transBegin();
        
        // First, verify the chalan exists and belongs to this campus
        $chalan = $db->table('fee_chalan fc')
            ->select('fc.student_id, fc.invoice_no')
            ->join('students s', 's.student_id = fc.student_id')
            ->where('fc.chalan_id', $chalan_id)
            ->where('s.campus_id', $campus_id)
            ->get()
            ->getRow();
        
        log_message('debug', "Chalan query result: " . json_encode($chalan));
        
        if (!$chalan) {
            // Try without campus filter to see if chalan exists
            $chalanExists = $db->table('fee_chalan')
                ->where('chalan_id', $chalan_id)
                ->get()
                ->getRow();
            
            if ($chalanExists) {
                log_message('error', "Chalan exists but belongs to different campus. Chalan student_id: {$chalanExists->student_id}, Current campus: $campus_id");
                throw new \Exception('Chalan belongs to a different campus');
            } else {
                log_message('error', "Chalan not found with ID: $chalan_id");
                throw new \Exception('Chalan not found');
            }
        }
        
        // Delete the chalan
        $deleted = $db->table('fee_chalan')
            ->where('chalan_id', $chalan_id)
            ->delete();
        
        log_message('debug', "Delete result: " . ($deleted ? "Success" : "Failed"));
        
        if (!$deleted) {
            throw new \Exception('Database delete operation failed');
        }
        
        // Check if there are any remaining chalans for this invoice
        $remainingCount = $db->table('fee_chalan')
            ->where('invoice_no', $chalan->invoice_no)
            ->countAllResults();
        
        log_message('debug', "Remaining chalans for invoice {$chalan->invoice_no}: $remainingCount");
        
        // If no remaining chalans, delete the invoice
        if ($remainingCount == 0) {
            $deletedInvoice = $db->table('invoices')
                ->where('invoice_no', $chalan->invoice_no)
                ->delete();
            log_message('debug', "Invoice deletion result: " . ($deletedInvoice ? "Success" : "No invoice found or failed"));
        }
        
        $db->transCommit();
        
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Chalan deleted successfully'
        ]);
        
    } catch (\Exception $e) {
        $db->transRollback();
        log_message('error', 'Delete chalan error: ' . $e->getMessage());
        log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        
        return $this->response->setJSON([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}
/**
 * Delete all today's chalans
 */
public function deleteAllToday()
{
    $db = \Config\Database::connect();
    $today = date('Y-m-d');
    $campus_id = (int) session()->get('member_campusid');
    
    try {
        $db->transBegin();
        
        // Get all student IDs for this campus
        $studentIds = $db->table('students')
            ->select('student_id')
            ->where('campus_id', $campus_id)
            ->get()
            ->getResultArray();
        
        if (empty($studentIds)) {
            $db->transCommit();
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'No students found',
                'deleted_count' => 0
            ]);
        }
        
        $studentIdArray = array_column($studentIds, 'student_id');
        
        // FIXED: Get invoice numbers before deletion - use distinct() method
        $invoices = $db->table('fee_chalan')
            ->select('invoice_no')
            ->distinct()  // ← CORRECT: distinct as separate method
            ->where('DATE(created_date)', $today)
            ->whereIn('student_id', $studentIdArray)
            ->get()
            ->getResultArray();
        
        log_message('debug', "Found " . count($invoices) . " invoices to process");
        
        // Delete all today's chalans
        $deleted = $db->table('fee_chalan')
            ->where('DATE(created_date)', $today)
            ->whereIn('student_id', $studentIdArray)
            ->delete();
        
        log_message('debug', "Deleted $deleted chalans for today");
        
        // Delete invoices with no remaining chalans
        $invoiceDeletedCount = 0;
        foreach ($invoices as $invoice) {
            $remainingCount = $db->table('fee_chalan')
                ->where('invoice_no', $invoice['invoice_no'])
                ->countAllResults();
            
            if ($remainingCount == 0) {
                $db->table('invoices')
                    ->where('invoice_no', $invoice['invoice_no'])
                    ->delete();
                $invoiceDeletedCount++;
            }
        }
        
        log_message('debug', "Deleted $invoiceDeletedCount invoices");
        
        $db->transCommit();
        
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'All today\'s chalans deleted successfully',
            'deleted_count' => $deleted,
            'invoices_deleted' => $invoiceDeletedCount
        ]);
        
    } catch (\Exception $e) {
        $db->transRollback();
        log_message('error', 'Delete all today chalans error: ' . $e->getMessage());
        
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Failed to delete chalans: ' . $e->getMessage()
        ]);
    }
}   

    /**
     * Class standard amounts (fee_amount) and per-student monthly discount / plan for challan edit auto-fill.
     *
     * @param list<int> $studentIds
     * @param list<array<string,mixed>> $feeTypes rows with fee_type_id
     *
     * @return array{amount_map: array<string, float>, students: array<string, array{class_id:int, monthly_discount:float, plan_value:int}>}
     */
    private function buildChalanEditStdFeesPayload(int $campus_id, int $session_id, array $studentIds, array $feeTypes): array
    {
        $studentIds = array_values(array_unique(array_filter(array_map('intval', $studentIds), static fn ($id) => $id > 0)));

        if ($studentIds === [] || $session_id <= 0) {
            return ['amount_map' => [], 'students' => []];
        }

        $rows = $this->db->table('student_class sc')
            ->select('sc.student_id, cs.class_id, s.discounted_amount, s.fee_plan')
            ->join('students s', 's.student_id = sc.student_id')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
            ->where('sc.session_id', $session_id)
            ->where('s.campus_id', $campus_id)
            ->whereIn('sc.student_id', $studentIds)
            ->get()
            ->getResultArray();

        $planCache   = [];
        $studentsOut = [];

        foreach ($rows as $row) {
            $sid = (int) ($row['student_id'] ?? 0);
            if ($sid <= 0 || isset($studentsOut[(string) $sid])) {
                continue;
            }

            $classId = (int) ($row['class_id'] ?? 0);
            $fp      = (int) ($row['fee_plan'] ?? 0);

            if ($fp === 0) {
                $pv = 1;
            } else {
                if (! array_key_exists($fp, $planCache)) {
                    $pr           = $this->db->table('fee_plans')->select('plan_value')->where('plan_id', $fp)->get()->getRowArray();
                    $planCache[$fp] = $pr ? (int) $pr['plan_value'] : 1;
                }
                $pv = $planCache[$fp];
            }

            $studentsOut[(string) $sid] = [
                'class_id'         => $classId,
                'monthly_discount' => (float) ($row['discounted_amount'] ?? 0),
                'plan_value'       => max(1, (int) $pv),
            ];
        }

        $classIds = [];
        foreach ($studentsOut as $meta) {
            $c = (int) ($meta['class_id'] ?? 0);
            if ($c > 0) {
                $classIds[$c] = true;
            }
        }
        $classIds = array_keys($classIds);

        $ftIds = [];
        foreach ($feeTypes as $ft) {
            $fid = (int) ($ft['fee_type_id'] ?? 0);
            if ($fid > 0) {
                $ftIds[$fid] = true;
            }
        }
        $ftIds = array_keys($ftIds);

        $amountMap = [];

        if ($classIds !== [] && $ftIds !== []) {
            $amRows = $this->db->table('fee_amount')
                ->select('class_id, fee_type_id, amount')
                ->where('campus_id', $campus_id)
                ->where('session_id', $session_id)
                ->whereIn('class_id', $classIds)
                ->whereIn('fee_type_id', $ftIds)
                ->get()
                ->getResultArray();

            foreach ($amRows as $ar) {
                $key             = (int) $ar['class_id'] . '_' . (int) $ar['fee_type_id'];
                $amountMap[$key] = (float) ($ar['amount'] ?? 0);
            }
        }

        return [
            'amount_map' => $amountMap,
            'students'   => $studentsOut,
        ];
    }

/**
 * Get edit form for unpaid challan lines (student or whole family).
 */
public function getEditForm()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
    }

    $campus_id  = (int) session()->get('member_campusid');
    $session_id = (int) session()->get('member_sessionid');
    $student_id = (int) $this->request->getPost('student_id');
    $parent_id  = (int) $this->request->getPost('parent_id');

    $campusRow = $this->db->table('campus')->select('system_id')->where('campus_id', $campus_id)->get()->getRowArray();
    $system_id = (int) ($campusRow['system_id'] ?? 0);

    $feeTypes = [];
    if ($system_id > 0) {
        $feeTypes = $this->db->table('fee_type')
            ->select('fee_type_id, fee_type_name, is_monthly_fee')
            ->where('system_id', $system_id)
            ->where('status', 1)
            ->where('s_flag', 1)
            ->orderBy('fee_type_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    $scope         = 'student';
    $headline      = '';
    $student       = null;
    $familyStudents = [];

    if ($parent_id > 0) {
        $scope = 'family';
        $siblingCount = $this->db->table('students')
            ->where('parent_id', $parent_id)
            ->where('campus_id', $campus_id)
            ->countAllResults();
        if ($siblingCount === 0) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Family not found for this campus']);
        }

        $parentRow = $this->db->table('parents')->select('f_name')->where('parent_id', $parent_id)->get()->getRowArray();
        $headline  = 'Family challans — ' . trim((string) ($parentRow['f_name'] ?? 'Parent')) . ' (parent #' . $parent_id . ')';

        $familyStudents = $this->db->table('students')
            ->select('student_id, first_name, last_name, reg_no')
            ->where('parent_id', $parent_id)
            ->where('campus_id', $campus_id)
            ->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->get()
            ->getResultArray();

        $chalans = $this->db->table('fee_chalan fc')
            ->select('fc.*, ft.fee_type_name, s.first_name, s.last_name, s.reg_no, s.student_id AS line_student_id')
            ->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id', 'left')
            ->join('students s', 's.student_id = fc.student_id')
            ->where('s.parent_id', $parent_id)
            ->where('s.campus_id', $campus_id)
            ->where($this->feeChalanOpenStatusSql('fc'), null, false)
            ->orderBy('s.first_name', 'ASC')
            ->orderBy('fc.fee_month', 'DESC')
            ->orderBy('fc.issue_date', 'DESC')
            ->get()
            ->getResultArray();

        $student = !empty($familyStudents) ? [
            'student_id'  => (int) $familyStudents[0]['student_id'],
            'first_name'  => $familyStudents[0]['first_name'] ?? '',
            'last_name'   => $familyStudents[0]['last_name'] ?? '',
            'reg_no'      => $familyStudents[0]['reg_no'] ?? '',
            'parent_id'   => $parent_id,
            'f_name'      => $parentRow['f_name'] ?? '',
        ] : null;
    } elseif ($student_id > 0) {
        $parent_id = 0;
        $student = $this->db->table('students s')
            ->select('s.student_id, s.first_name, s.last_name, s.reg_no, s.parent_id, p.f_name')
            ->join('parents p', 'p.parent_id = s.parent_id', 'left')
            ->where('s.student_id', $student_id)
            ->where('s.campus_id', $campus_id)
            ->get()
            ->getRowArray();

        if (!$student) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Student not found']);
        }

        $headline = 'Student — ' . trim($student['first_name'] . ' ' . $student['last_name'])
            . ' (' . ($student['reg_no'] ?: 'No Reg') . ')';

        $chalans = $this->db->table('fee_chalan fc')
            ->select('fc.*, ft.fee_type_name, s.first_name, s.last_name, s.reg_no, s.student_id AS line_student_id')
            ->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id', 'left')
            ->join('students s', 's.student_id = fc.student_id')
            ->where('fc.student_id', $student_id)
            ->where('s.campus_id', $campus_id)
            ->where($this->feeChalanOpenStatusSql('fc'), null, false)
            ->orderBy('fc.fee_month', 'DESC')
            ->orderBy('fc.issue_date', 'DESC')
            ->get()
            ->getResultArray();

        $familyStudents = [[
            'student_id' => (int) $student['student_id'],
            'first_name' => $student['first_name'],
            'last_name'  => $student['last_name'],
            'reg_no'     => $student['reg_no'],
        ]];
    } else {
        return $this->response->setJSON(['success' => false, 'msg' => 'student_id or parent_id required']);
    }

    $studentIdsForPayload = array_map(static fn ($r) => (int) ($r['student_id'] ?? 0), $familyStudents);
    $stdFeesPayload       = $this->buildChalanEditStdFeesPayload($campus_id, $session_id, $studentIdsForPayload, $feeTypes);

    $html = view('admin/chalanview/partials/chalan_edit_form', [
        'scope'           => $scope,
        'headline'        => $headline,
        'student'         => $student,
        'parent_id'       => $parent_id,
        'chalans'         => $chalans,
        'fee_types'       => $feeTypes,
        'family_students' => $familyStudents,
        'csrf_token'      => csrf_token(),
        'csrf_hash'       => csrf_hash(),
    ]);

    return $this->response->setJSON([
        'success'  => true,
        'html'     => $html,
        'std_fees' => $stdFeesPayload,
    ]);
}

/**
 * Save edited challan lines (update existing, insert new rows).
 */
public function saveEdit()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
    }

    $campus_id = (int) session()->get('member_campusid');
    $user_id   = (int) session()->get('member_userid');

    $edit_scope     = (string) $this->request->getPost('edit_scope');
    $posted_student = (int) $this->request->getPost('student_id');
    $posted_parent  = (int) $this->request->getPost('parent_id');

    $chalan_ids      = $this->request->getPost('chalan_id');
    $amounts         = $this->request->getPost('amount');
    $discounts       = $this->request->getPost('discount');
    $statuses        = $this->request->getPost('status');
    $issue_dates     = $this->request->getPost('issue_date');
    $due_dates       = $this->request->getPost('due_date');
    $fee_months      = $this->request->getPost('fee_month');
    $line_student_ids = $this->request->getPost('line_student_id');
    $fee_type_ids    = $this->request->getPost('fee_type_id');

    if (! is_array($chalan_ids) || count($chalan_ids) === 0) {
        return $this->response->setJSON(['success' => false, 'msg' => 'No rows submitted']);
    }

    $allowedStatus = ['unpaid', 'paid', 'discounted'];

    $campusRow = $this->db->table('campus')->select('system_id')->where('campus_id', $campus_id)->get()->getRowArray();
    $system_id = (int) ($campusRow['system_id'] ?? 0);

    $this->db->transBegin();

    try {
        $updated = 0;
        $inserted = 0;

        foreach ($chalan_ids as $index => $chalan_id_raw) {
            $chalan_id = (int) $chalan_id_raw;
            $amount    = (float) ($amounts[$index] ?? 0);
            $discount  = (float) ($discounts[$index] ?? 0);
            $status    = (string) ($statuses[$index] ?? 'unpaid');
            if (! in_array($status, $allowedStatus, true)) {
                $status = 'unpaid';
            }
            if ($discount > $amount) {
                $discount = $amount;
            }
            if ($discount < 0) {
                $discount = 0;
            }

            $issue = $this->parseChalanDateInput($issue_dates[$index] ?? null) ?? date('Y-m-d');
            $due   = $this->parseChalanDateInput($due_dates[$index] ?? null) ?? date('Y-m-d');
            $feeYm = $this->normalizeFeeMonthYm($fee_months[$index] ?? null);
            $fee_month_old = date('F Y', strtotime($feeYm . '-01'));

            if ($chalan_id > 0) {
                $row = $this->db->table('fee_chalan fc')
                    ->select('fc.chalan_id, fc.student_id, s.campus_id, s.parent_id')
                    ->join('students s', 's.student_id = fc.student_id')
                    ->where('fc.chalan_id', $chalan_id)
                    ->get()
                    ->getRowArray();

                if (!$row || (int) $row['campus_id'] !== $campus_id) {
                    throw new \RuntimeException('Challan not found or access denied.');
                }
                if ($edit_scope === 'family' && $posted_parent > 0 && (int) $row['parent_id'] !== $posted_parent) {
                    throw new \RuntimeException('Challan does not belong to this family.');
                }
                if ($edit_scope === 'student' && $posted_student > 0 && (int) $row['student_id'] !== $posted_student) {
                    throw new \RuntimeException('Challan does not belong to this student.');
                }

                $this->db->table('fee_chalan')->where('chalan_id', $chalan_id)->update([
                    'amount'        => $amount,
                    'discount'      => $discount,
                    'status'        => $status,
                    'issue_date'    => $issue,
                    'due_date'      => $due,
                    'fee_month'     => $feeYm,
                    'fee_month_old' => $fee_month_old,
                    'updated_date'  => date('Y-m-d H:i:s'),
                ]);
                $updated++;
            } else {
                $line_sid = (int) ($line_student_ids[$index] ?? 0);
                $ftid     = (int) ($fee_type_ids[$index] ?? 0);

                if ($line_sid <= 0 || $ftid <= 0) {
                    throw new \RuntimeException('New rows require student and fee type.');
                }

                $st = $this->db->table('students')
                    ->select('student_id, parent_id, campus_id')
                    ->where('student_id', $line_sid)
                    ->where('campus_id', $campus_id)
                    ->get()
                    ->getRowArray();

                if (!$st) {
                    throw new \RuntimeException('Invalid student for new line.');
                }
                if ($edit_scope === 'family' && $posted_parent > 0 && (int) $st['parent_id'] !== $posted_parent) {
                    throw new \RuntimeException('Student is not in this family.');
                }
                if ($edit_scope === 'student' && $posted_student > 0 && $line_sid !== $posted_student) {
                    throw new \RuntimeException('New line must be for the selected student.');
                }

                $ftOk = $this->db->table('fee_type')
                    ->where('fee_type_id', $ftid)
                    ->where('system_id', $system_id)
                    ->countAllResults();
                if ($ftOk === 0) {
                    throw new \RuntimeException('Invalid fee type.');
                }

                $date = date('Y-m-d');
                $this->db->table('fee_chalan')->insert([
                    'student_id'     => $line_sid,
                    'fee_type_id'    => $ftid,
                    'amount'         => $amount,
                    'discount'       => $discount,
                    'issue_date'     => $issue,
                    'due_date'       => $due,
                    'fee_month'      => $feeYm,
                    'fee_month_old'  => $fee_month_old,
                    'status'         => $status,
                    'payment_status' => 'pending',
                    'paid_date'      => '0000-00-00',
                    'created_date'   => $date,
                    'updated_date'   => date('Y-m-d H:i:s'),
                    'user_id'        => $user_id,
                    'acc_id'         => 0,
                    'currency_code'  => 'PKR',
                    'invoice_no'     => 0,
                ]);
                $inserted++;
            }
        }

        $this->db->transCommit();

        return $this->response->setJSON([
            'success' => true,
            'msg'     => sprintf('Saved: %d updated, %d new line(s).', $updated, $inserted),
        ]);
    } catch (\Throwable $e) {
        $this->db->transRollback();

        return $this->response->setJSON([
            'success' => false,
            'msg'     => $e->getMessage(),
        ]);
    }
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
        foreach ($results as $row) {
            $data[] = [
                'id'            => $row->chalan_id,
                'reg_no'        => $row->reg_no,
                'student_name'  => fee_chalan_student_display_name(
                    trim($row->first_name . ' ' . $row->last_name),
                    $row->reg_no ?? null
                ),
                'fee_month'     => $row->fee_month,
                'amount'        => $row->amount - $row->discount,
                'status'        => $row->status,
                'fee_name'      => $row->fee_type_name
            ];
        }

        $response->draw = $draw;
        $response->recordsFiltered = $response->recordsTotal;
        $response->data = $data;

        return $this->response->setJSON($response);
    }

  
    public function dsave(): ResponseInterface
    {
        $response = [];

        $campus_id = $this->session->get('member_campusid');
        $session_id = $this->session->get('member_sessionid');
        $user_id = $this->session->get('member_userid');
        $id = intval($this->request->getPost('id'));
        $date = date('Y-m-d');

        $issue_date = DateTime::createFromFormat('d/m/Y', $this->request->getPost('issue_date'));
        $due_date = DateTime::createFromFormat('d/m/Y', $this->request->getPost('due_date'));
        $fee_month = $this->request->getPost('fee_month');

        if (!$issue_date || !$due_date) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid date format.']);
        }

        $issuedate = $issue_date->format('Y-m-d');
        $duedate = $due_date->format('Y-m-d');
        $arrFeeMonth = explode('-', $fee_month);
        $feeMonth = $arrFeeMonth[1] . '/' . $arrFeeMonth[0];

        if ($id === 0) {
            // Insert new chalan
            $data = [
                'student_id'    => $this->request->getPost('student_id'),
                'fee_type_id'   => $this->request->getPost('fee_type_id'),
                'amount'        => $this->request->getPost('amount'),
                'discount'      => $this->request->getPost('discount'),
                'issue_date'    => $issuedate,
                'due_date'      => $duedate,
                'fee_month'     => $feeMonth,
                'status'        => 'unpaid',
                'created_date'  => $date,
                'user_id'       => $user_id
            ];

            $this->db->table('fee_chalan')->insert($data);
            $response = ['success' => true, 'msg' => 'Fee Chalan Added Successfully'];
        } else {
            // Update existing chalan
            $data = [
                'amount'        => $this->request->getPost('amount'),
                'discount'      => $this->request->getPost('discount'),
                'issue_date'    => $issuedate,
                'due_date'      => $duedate,
                'fee_month'     => $feeMonth,
                'update_date'   => $date,
                'user_id'       => $user_id
            ];

            $this->db->table('fee_chalan')->where('chalan_id', $id)->update($data);
            $response = ['success' => true, 'msg' => 'Fee Chalan Updated Successfully'];
        }

        return $this->response->setJSON($response);
    }
public function bulk_chalan_stream()
{
    $processed       = 0;
    $totalStudents   = 0;
    $students        = [];
    $successCount    = 0;
    $skippedCount    = 0;

    set_time_limit(600);
    ini_set('max_execution_time', '600');
    ini_set('memory_limit', '512M');

    if (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('X-Accel-Buffering: no');
    ini_set('zlib.output_compression', '0');

    echo "retry: 1000\n\n";
    flush();

    session_write_close();

    $request = \Config\Services::request();
    $db      = \Config\Database::connect();

    $fee_type_ids = $request->getGet('fee_type_ids');
    if (! is_array($fee_type_ids)) {
        $fee_type_ids = array_filter(array_map('trim', explode(',', (string) $fee_type_ids)));
    }
    $fee_type_ids = array_values(array_filter(array_map('intval', $fee_type_ids), static fn ($id) => $id > 0));

    $fee_month       = (string) $request->getGet('fee_month');
    $issue_date_raw  = $request->getGet('issue_date');
    $due_date_raw    = $request->getGet('due_date');

    $issue_date = DateTime::createFromFormat('d/m/Y', (string) $issue_date_raw);
    $due_date   = DateTime::createFromFormat('d/m/Y', (string) $due_date_raw);

    $issue_date_formatted = $issue_date ? $issue_date->format('Y-m-d') : null;
    $due_date_formatted   = $due_date ? $due_date->format('Y-m-d') : null;

    if ($fee_month === '' || $fee_type_ids === [] || ! $issue_date_formatted || ! $due_date_formatted) {
        $msg = 'Missing or invalid parameters.';
        if ($fee_type_ids === []) {
            $msg = 'Select at least one fee type before generating challans.';
        } elseif ($fee_month === '') {
            $msg = 'Fee month is required.';
        } elseif (! $issue_date_formatted || ! $due_date_formatted) {
            $msg = 'Issue date and due date are required (use DD/MM/YYYY).';
        }
        $this->sendEvent(['type' => 'error', 'message' => $msg]);
        exit;
    }

    try {
        $session_id = (int) session('member_sessionid');
        $campus_id  = (int) session('member_campusid');
        $user_id    = (int) session('member_userid');

        if ($session_id <= 0 || $campus_id <= 0) {
            throw new \RuntimeException('Invalid session or campus.');
        }

        $system_id = (int) (getSchoolInfo()->system_id ?? 0);
        $date      = date('Y-m-d');

        $planCache = [];

        $studentsRes = $db->table('student_class sc')
            ->select('sc.student_id, cs.class_id, s.std_type, s.discounted_amount, s.fee_plan, s.reg_no, s.first_name, s.last_name')
            ->join('students s', 's.student_id = sc.student_id')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
            ->where('sc.session_id', $session_id)
            ->where('s.campus_id', $campus_id)
            ->where('s.status', 1)
            ->get();

        $this->dbError($db, 'fetch_students');

        $students      = $studentsRes ? $studentsRes->getResult() : [];
        $totalStudents = count($students);

        $this->sendEvent([
            'type'            => 'progress',
            'processed'       => 0,
            'total'           => $totalStudents,
            'success'         => 0,
            'skipped'         => 0,
            'current_student' => 'Initializing',
        ]);

        if ($totalStudents === 0) {
            $this->sendEvent([
                'type'    => 'complete',
                'message' => 'No students found',
                'total'   => 0,
                'success' => 0,
                'skipped' => 0,
            ]);

            return;
        }

        $feeTypesRes = $db->table('fee_type')
            ->select('fee_type_id, fee_type_name, is_monthly_fee')
            ->where('system_id', $system_id)
            ->where('status', 1)
            ->whereIn('fee_type_id', $fee_type_ids)
            ->get();

        $this->dbError($db, 'fetch_fee_types');

        $feeTypes = $feeTypesRes ? $feeTypesRes->getResultArray() : [];

        $preload  = $this->preloadBulkChalanData($db, $campus_id, $session_id, $students, $fee_type_ids);
        $bulkCtx  = array_merge($preload, ['use_bulk_invoice' => true]);

        $this->primeBulkInvoiceSequence($fee_month);

        $progressEvery = (int) round($totalStudents / 40);
        $progressEvery = max(5, min(50, $progressEvery));

        $skipDetails = [];
        $maxSkipLog  = 200;

        foreach ($students as $student) {
            if (connection_aborted()) {
                exit;
            }

            $insertable_fee_types = [];

            foreach ($feeTypes as $feeType) {
                $allowInsert   = true;
                $thisPlanValue = 1;

                if ((int) $feeType['is_monthly_fee'] === 1) {
                    if ((int) $student->fee_plan === 0) {
                        $thisPlanValue = 1;
                    } else {
                        $fp = (int) $student->fee_plan;

                        if (! array_key_exists($fp, $planCache)) {
                            $planRow = $db->table('fee_plans')
                                ->select('plan_value')
                                ->where('plan_id', $fp)
                                ->get()
                                ->getRow();
                            $planCache[$fp] = $planRow ? (int) $planRow->plan_value : 1;
                        }
                        $thisPlanValue = $planCache[$fp];
                    }
                }

                if ($allowInsert) {
                    $insertable_fee_types[] = [
                        'fee_type_id'    => (int) $feeType['fee_type_id'],
                        'is_monthly_fee' => (int) $feeType['is_monthly_fee'],
                        'plan_value'     => (int) $thisPlanValue,
                    ];
                }
            }

            if ($insertable_fee_types === []) {
                $processed++;
                $skippedCount++;
                if (count($skipDetails) < $maxSkipLog) {
                    $skipDetails[] = [
                        'student_id' => (int) $student->student_id,
                        'reg_no'     => (string) ($student->reg_no ?? ''),
                        'name'       => trim((string) ($student->first_name ?? '') . ' ' . (string) ($student->last_name ?? '')),
                        'code'       => 'no_fee_lines_for_month',
                        'detail'     => 'No billable fee lines could be built for this student (check fee types, class fee amounts, and fee plan configuration).',
                    ];
                }
                if ($processed % $progressEvery === 0 || $processed === $totalStudents) {
                    $this->sendEvent([
                        'type'            => 'progress',
                        'processed'       => $processed,
                        'total'           => $totalStudents,
                        'current_student' => (int) $student->student_id,
                        'success'         => $successCount,
                        'skipped'         => $skippedCount,
                        'reason'          => 'no_active_fee_types_for_month',
                    ]);
                }

                continue;
            }

            $skipRow       = new stdClass();
            $skipRow->reason = '';
            $result        = $this->handleInvoiceAndFee(
                (int) $student->student_id,
                (int) $student->class_id,
                (int) $student->std_type,
                $campus_id,
                $session_id,
                $issue_date_formatted,
                $due_date_formatted,
                $fee_month,
                $user_id,
                $date,
                $insertable_fee_types,
                $student->discounted_amount,
                $bulkCtx,
                $skipRow
            );

            $processed++;
            if ($result === true) {
                $successCount++;
            } else {
                $skippedCount++;
                if (count($skipDetails) < $maxSkipLog) {
                    $skipDetails[] = [
                        'student_id' => (int) $student->student_id,
                        'reg_no'     => (string) ($student->reg_no ?? ''),
                        'name'       => trim((string) ($student->first_name ?? '') . ' ' . (string) ($student->last_name ?? '')),
                        'code'       => 'invoice_fee_failed',
                        'detail'     => $skipRow->reason !== ''
                            ? $skipRow->reason
                            : 'No new challan lines were created for this student.',
                    ];
                }
            }

            if ($processed % $progressEvery === 0 || $processed === $totalStudents) {
                $this->sendEvent([
                    'type'            => 'progress',
                    'processed'       => $processed,
                    'total'           => $totalStudents,
                    'current_student' => (int) $student->student_id,
                    'success'         => $successCount,
                    'skipped'         => $skippedCount,
                ]);
            }
        }

        $this->sendEvent([
            'type'          => 'complete',
            'total'         => $totalStudents,
            'success'       => $successCount,
            'skipped'       => $skippedCount,
            'skip_details'  => $skipDetails,
            'skip_truncated'=> $skippedCount > $maxSkipLog,
        ]);
    } catch (\Throwable $e) {
        log_message('error', 'Bulk chalan generation failed: ' . $e->getMessage());
        $this->sendEvent([
            'type'      => 'error',
            'message'   => 'Error: ' . $e->getMessage(),
            'processed' => $processed ?? 0,
            'total'     => $totalStudents ?? 0,
        ]);
    } finally {
        $this->resetBulkInvoiceSequence();
    }

    exit;
}
/**
 * Improved sendEvent method
 */
private function sendEvent(array $data, string $event = 'message'): void
{
    // Ensure data is properly formatted
    echo "event: {$event}\n";
    echo 'data: ' . json_encode($data) . "\n\n";
    
    // Force flush
    if (ob_get_level() > 0) {
        ob_flush();
    }
    flush();
}

/**
 * Preload fee_amount rows and fee-plan month eligibility for bulk generation (removes N+1 queries).
 *
 * @param list<object> $students Rows from student_class join (student_id, class_id, fee_plan, …)
 *
 * @return array{fee_amount_map: array<string, float>}
 */
private function preloadBulkChalanData(
    BaseConnection $db,
    int $campus_id,
    int $session_id,
    array $students,
    array $fee_type_ids_int
): array {
    $feeAmountMap = [];
    $classIds     = [];

    foreach ($students as $s) {
        $cid = (int) ($s->class_id ?? 0);
        if ($cid > 0) {
            $classIds[$cid] = true;
        }
    }
    $classIds = array_keys($classIds);

    if ($classIds !== [] && $fee_type_ids_int !== []) {
        $rows = $db->table('fee_amount')
            ->select('class_id, fee_type_id, amount')
            ->where('campus_id', $campus_id)
            ->where('session_id', $session_id)
            ->whereIn('class_id', $classIds)
            ->whereIn('fee_type_id', $fee_type_ids_int)
            ->get()
            ->getResult();

        foreach ($rows as $r) {
            $feeAmountMap[(int) $r->class_id . '_' . (int) $r->fee_type_id] = (float) $r->amount;
        }
    }

    return [
        'fee_amount_map' => $feeAmountMap,
    ];
}

private function primeBulkInvoiceSequence(string $fee_month): void
{
    if ($fee_month === '' || ! preg_match('/^\d{4}-\d{2}$/', $fee_month)) {
        return;
    }

    $feeDate = DateTime::createFromFormat('Y-m', $fee_month);
    if (! $feeDate) {
        return;
    }

    $yr = $feeDate->format('y');
    $db = \Config\Database::connect();

    $lastInvoice = $db->table('invoices')
        ->select('invoice_no')
        ->like('invoice_no', $yr . '-INV-', 'after')
        ->orderBy('invoice_no', 'DESC')
        ->get()
        ->getRow();

    $nextNumber = 1;
    if ($lastInvoice) {
        $parts      = explode('-', $lastInvoice->invoice_no);
        $nextNumber = (int) end($parts) + 1;
    }

    $this->bulkInvoiceSeq = [
        'fee_month' => $fee_month,
        'yr'        => $yr,
        'next'      => $nextNumber,
    ];
}

private function resetBulkInvoiceSequence(): void
{
    $this->bulkInvoiceSeq = null;
}

private function takeNextBulkInvoiceNumber(string $fee_month): string
{
    if ($this->bulkInvoiceSeq === null || ($this->bulkInvoiceSeq['fee_month'] ?? '') !== $fee_month) {
        return $this->generateInvoiceNumber($fee_month);
    }

    $n = (int) $this->bulkInvoiceSeq['next'];
    $this->bulkInvoiceSeq['next'] = $n + 1;

    return ($this->bulkInvoiceSeq['yr'] ?? '') . '-INV-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT);
}


private function handleInvoiceAndFee(
    int $student_id, int $class_id, int $std_type, int $campus_id, int $session_id,
    string $issue_date, string $due_date, string $fee_month, int $user_id, string $date,
    array $feeTypes, $monthly_discount, array $bulkCtx = [], ?stdClass $skipDetailOut = null
) {
    $db = \Config\Database::connect();
    $db->transBegin();

    try {
        $feeAmountMap = $bulkCtx['fee_amount_map'] ?? null;
        $writeSkip    = $skipDetailOut instanceof stdClass;
        $ftTotal      = count($feeTypes);
        $nExisting    = 0;
        $nNoAmount    = 0;
        $nZeroNet     = 0;
        $nNonPos      = 0;

        // Existing invoice?
        $invRes = $db->table('invoices')
            ->where('student_id', $student_id)
            ->where('fee_month',  $fee_month)
            ->where('issue_date', $issue_date)
            ->get();
        $this->dbError($db, 'invoice_lookup');

        $existingInvoice = $invRes ? $invRes->getRow() : null;
        $useBulkInv      = ! empty($bulkCtx['use_bulk_invoice']);

        if ($existingInvoice) {
            $invoice_no = $existingInvoice->invoice_no;
        } elseif ($useBulkInv) {
            $invoice_no = $this->takeNextBulkInvoiceNumber($fee_month);
        } else {
            $invoice_no = $this->generateInvoiceNumber($fee_month);
        }

        if (!$existingInvoice) {
            $db->table('invoices')->insert([
                'student_id' => $student_id,
                'issue_date' => $issue_date,
                'fee_month'  => $fee_month,
                'yr'         => date('y', strtotime($fee_month . '-01')),
                'invoice_no' => $invoice_no,
                'created_date' => $date,
                'updated_date' => $date,
                'user_id' => $user_id
            ]);
            if ($this->dbError($db, 'invoice_insert')) {
                $db->transRollback();
                if ($writeSkip) {
                    $skipDetailOut->reason = 'Could not save the invoice header (database error).';
                }

                return false;
            }
            $this->debug('invoice_insert_ok', ['student_id' => $student_id, 'invoice_no' => $invoice_no]);
        }

        $existingFeeTypes = [];
        $exRes            = $db->table('fee_chalan')
            ->select('fee_type_id')
            ->where('student_id', $student_id)
            ->where('fee_month', $fee_month)
            ->where('invoice_no', $invoice_no)
            ->get()
            ->getResultArray();
        foreach ($exRes as $er) {
            $existingFeeTypes[(int) $er['fee_type_id']] = true;
        }

        $insertedCount = 0;

        foreach ($feeTypes as $fee) {
            $fee_type_id = (int)$fee['fee_type_id'];
            $isMonthly   = !empty($fee['is_monthly_fee']);  // THIS IS CORRECT - checking is_monthly_fee = 1

            if (isset($existingFeeTypes[$fee_type_id])) {
                $nExisting++;
                $this->debug('chalan_skip_exists', [
                    'student_id' => $student_id,
                    'fee_type_id'=> $fee_type_id,
                    'invoice_no' => $invoice_no
                ]);
                continue;
            }

            $amtKey = $class_id . '_' . $fee_type_id;
            if (is_array($feeAmountMap) && array_key_exists($amtKey, $feeAmountMap)) {
                $default_amount = (float) $feeAmountMap[$amtKey];
            } else {
                $amountRes = $db->table('fee_amount')->select('amount')
                    ->where('class_id',   $class_id)
                    ->where('campus_id',  $campus_id)
                    ->where('session_id', $session_id)
                    ->where('fee_type_id',$fee_type_id)
                    ->get();
                $this->dbError($db, 'amount_lookup');

                $amountRow = $amountRes ? $amountRes->getRow() : null;
                if (!$amountRow) {
                    $nNoAmount++;
                    $this->debug('amount_not_found', [
                        'student_id' => $student_id, 'class_id' => $class_id,
                        'fee_type_id'=> $fee_type_id,
                    ]);
                    continue;
                }

                $default_amount = (float) $amountRow->amount;
            }
            $pv             = max(1, (int) ($fee['plan_value'] ?? 1));   // plan value
            
            // FIX: Re-declare isMonthly from fee array to ensure it's properly set
            $isMonthly = !empty($fee['is_monthly_fee']);

            // Multiply monthly fee by plan_value
            $final_amount   = $isMonthly ? ($default_amount * $pv) : $default_amount;

            // FIX: Only apply discount for monthly fees (is_monthly_fee = 1)
            // Other fees (is_monthly_fee = 0) should not get discount
            $discount = 0.0;
            
            if ($isMonthly) {
                // Only monthly fees get discount
                $perUnitDiscount = (float) ($monthly_discount ?? 0);  // discount for one unit/month
                $discount = $perUnitDiscount * $pv;
                
                // Prevent discount from exceeding amount
                if ($discount > $final_amount) {
                    $discount = $final_amount;
                }
            }
            // FIX END: Else block removed - no discount for other fees

            // NEW: Calculate net amount after discount
            $net_amount = $final_amount - $discount;
            
            // NEW: Skip insertion if net amount is zero or negative
            if ($net_amount <= 0) {
                $nZeroNet++;
                $this->debug('chalan_skip_zero_amount', [
                    'student_id'    => $student_id,
                    'fee_type_id'   => $fee_type_id,
                    'fee_type_name' => $fee['fee_type_name'] ?? 'Unknown',
                    'amount'        => $final_amount,
                    'discount'      => $discount,
                    'net_amount'    => $net_amount,
                    'is_monthly'    => $isMonthly,
                    'reason'        => 'net_amount_zero_or_negative'
                ]);
                continue; // Skip inserting this fee type
            }

            if ($final_amount <= 0) {
                $nNonPos++;
                $this->debug('amount_non_positive', [
                    'student_id' => $student_id,
                    'fee_type_id'=> $fee_type_id,
                    'amount'     => $final_amount,
                    'plan_value' => $pv,
                    'is_monthly' => $isMonthly
                ]);
                continue;
            }
            
            // Insert chalan
            $db->table('fee_chalan')->insert([
                'student_id'     => $student_id,
                'due_date'       => $due_date,
                'issue_date'     => $issue_date,
                'fee_month'      => $fee_month,
                'fee_month_old'  => date('F Y', strtotime($fee_month . '-01')),
                'amount'         => $final_amount,
                'discount'       => $discount,
                'status'         => 'unpaid',
                'payment_status' => 'pending',
                'fee_type_id'    => $fee_type_id,
                'paid_date'      => '0000-00-00',       // FIX: Added quotes around date
                'created_date'   => $date,
                'updated_date'   => $date,
                'user_id'        => $user_id,
                'acc_id'         => 0,
                'currency_code'  => 'PKR',
                'invoice_no'     => $invoice_no
            ]);
            if ($this->dbError($db, 'chalan_insert')) {
                $db->transRollback();
                if ($writeSkip) {
                    $skipDetailOut->reason = 'Could not insert a fee challan line (database error).';
                }

                return false;
            }

            $insertedCount++;
            $this->debug('chalan_insert_ok', [
                'student_id'  => $student_id,
                'fee_type_id' => $fee_type_id,
                'invoice_no'  => $invoice_no,
                'amount'      => $final_amount,
                'discount'    => $discount,
                'net_amount'  => $net_amount,
                'is_monthly'  => $isMonthly
            ]);
        }

        if ($insertedCount > 0 && $db->transStatus() === true) {
            $db->transCommit();
            $this->applyAdvanceToMonth($db, $student_id, $fee_month, $issue_date, $due_date, $user_id);

            return true;
        }

        $this->debug('no_rows_inserted_for_student', [
            'student_id' => $student_id,
            'invoice_no' => $invoice_no
        ]);
        if ($writeSkip) {
            if ($ftTotal === 0) {
                $skipDetailOut->reason = 'No fee types were passed for insertion.';
            } elseif ($nExisting === $ftTotal) {
                $skipDetailOut->reason = 'All selected fee types already have challan lines for this fee month on this invoice.';
            } elseif ($nNoAmount > 0 && ($nExisting + $nNoAmount + $nZeroNet + $nNonPos) >= $ftTotal) {
                $skipDetailOut->reason = 'Missing fee amount for this class/session for one or more fee types (configure Fee Amount under fee setup).';
            } elseif ($nZeroNet > 0 || $nNonPos > 0) {
                $skipDetailOut->reason = 'Net payable is zero after discount, or gross amount is zero, for all applicable fee types.';
            } else {
                $skipDetailOut->reason = 'No new lines added (lines may already exist, amounts missing, or net is zero).';
            }
        }
        $db->transRollback();
        return false;

    } catch (\Throwable $e) {
        $db->transRollback();
        if ($writeSkip) {
            $skipDetailOut->reason = 'System error while generating challan: ' . $e->getMessage();
        }
        log_message('error', 'handleInvoiceAndFee exception: ' . $e->getMessage());
        $this->sendEvent([
            'type'  => 'debug',
            'level' => 'error',
            'tag'   => 'exception',
            'msg'   => $e->getMessage()
        ], 'debug');
        return false;
    }
}


private function applyAdvanceToMonth(
    \CodeIgniter\Database\BaseConnection $db,
    int $studentId,
    string $feeMonth,           // 'YYYY-MM'
    string $issueDateYmd,       // 'Y-m-d'
    string $dueDateYmd,         // 'Y-m-d'
    int $userId
): void {
    $advanceTypeId = advance_fee_type_id();
    $balance       = get_student_advance_balance($db, $studentId);

    if ($balance <= 0) {
        return;
    }

    $adv = $db->table('fee_chalan')
        ->select('chalan_id, paid_date')
        ->where('student_id', $studentId)
        ->where('fee_type_id', $advanceTypeId)
        ->where('status', 'paid')
        ->where('amount >', 0)
        ->orderBy('chalan_id', 'DESC')
        ->get()
        ->getRow();

    if (! $adv) {
        return;
    }

    $advancePaidDate = (string) ($adv->paid_date ?? '');
    if ($advancePaidDate === '' || $advancePaidDate === '0000-00-00') {
        $advancePaidDate = $issueDateYmd;
    }

    $advanceAvail = $balance;

    $unpaidRows = $db->table('fee_chalan')
        ->select('chalan_id, student_id, fee_type_id, invoice_no, issue_date, amount, COALESCE(discount,0) AS discount')
        ->where('student_id', $studentId)
        ->where('fee_month', $feeMonth)
        ->where('status', 'unpaid')
        ->where('fee_type_id !=', $advanceTypeId)
        ->orderBy('chalan_id', 'ASC')
        ->get()
        ->getResultArray();

    if ($unpaidRows === [] || $advanceAvail <= 0) {
        return;
    }

    $now = date('Y-m-d H:i:s');
    $db->transStart();

    foreach ($unpaidRows as $row) {
        if ($advanceAvail <= 0) {
            break;
        }

        $chalanId = (int) $row['chalan_id'];
        $discount = (float) $row['discount'];
        $payable  = max(0.0, (float) $row['amount'] - $discount);

        if ($payable <= 0) {
            continue;
        }

        $rowIssueDate = ! empty($row['issue_date']) && $row['issue_date'] !== '0000-00-00'
            ? $row['issue_date']
            : $issueDateYmd;

        if ($advanceAvail >= $payable) {
            $db->table('fee_chalan')->where('chalan_id', $chalanId)->update([
                'status'         => 'paid',
                'payment_status' => 'completed',
                'paid_date'      => $advancePaidDate,
                'updated_date'   => $now,
                'user_id'        => $userId,
            ]);
            $advanceAvail -= $payable;
        } else {
            $paidPortionPayable = $advanceAvail;
            $newPaidAmount      = round($paidPortionPayable + $discount, 2);

            $db->table('fee_chalan')->where('chalan_id', $chalanId)->update([
                'amount'         => $newPaidAmount,
                'status'         => 'paid',
                'payment_status' => 'completed',
                'paid_date'      => $advancePaidDate,
                'updated_date'   => $now,
                'user_id'        => $userId,
            ]);

            $remainderPayable = round($payable - $paidPortionPayable, 2);

            $db->table('fee_chalan')->insert([
                'student_id'     => $row['student_id'],
                'due_date'       => $dueDateYmd,
                'issue_date'     => $rowIssueDate,
                'fee_month_old'  => date('F Y', strtotime($feeMonth . '-01')),
                'fee_month'      => $feeMonth,
                'amount'         => $remainderPayable,
                'discount'       => 0,
                'status'         => 'unpaid',
                'payment_status' => 'pending',
                'fee_type_id'    => $row['fee_type_id'],
                'paid_date'      => '0000-00-00',
                'created_date'   => $now,
                'updated_date'   => $now,
                'user_id'        => $userId,
                'invoice_no'     => $row['invoice_no'],
            ]);

            $advanceAvail = 0;
            break;
        }
    }

    $db->table('fee_chalan')->where('chalan_id', (int) $adv->chalan_id)->update([
        'amount'       => round(max(0.0, $advanceAvail), 2),
        'fee_type_id'  => $advanceTypeId,
        'updated_date' => $now,
        'user_id'      => $userId,
    ]);

    $db->transComplete();
}





/** Convenience: stream a console-friendly debug event */
private function debug(string $tag, array $payload = []): void
{
    $this->sendEvent(['type' => 'debug', 'tag' => $tag, 'data' => $payload], 'debug');
}

/** Check last DB error and emit a debug event with last SQL if any. */
private function dbError(\CodeIgniter\Database\BaseConnection $db, string $tag): bool
{
    $err = $db->error(); // ['code' => int, 'message' => string]
    if (!empty($err['code'])) {
        $sql = method_exists($db, 'showLastQuery')
            ? (string)$db->showLastQuery()
            : (($db->getLastQuery() ? $db->getLastQuery()->getQuery() : ''));

        log_message('error', "[$tag] DB ERROR {$err['code']}: {$err['message']} | SQL: {$sql}");
        $this->sendEvent([
            'type' => 'debug',
            'level'=> 'error',
            'tag'  => $tag,
            'sql'  => $sql,
            'err'  => $err,
        ], 'debug');
        return true;
    }
    return false;
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

        $yr = $feeDate->format('y'); // Last 2 digits of year (e.g., "25" for 2025)

        // Find the highest existing invoice number for this year
        $lastInvoice = $db->table('invoices')
            ->select('invoice_no')
            ->like('invoice_no', $yr.'-INV-', 'after')
            ->orderBy('invoice_no', 'DESC')
            ->get()
            ->getRow();

        if ($lastInvoice) {
            // Extract the numeric part and increment
            $parts = explode('-', $lastInvoice->invoice_no);
            $lastNumber = (int)end($parts);
            $nextNumber = $lastNumber + 1;
        } else {
            // No invoices yet for this year - start from 1
            $nextNumber = 1;
        }

        // Format the invoice number (e.g., "25-INV-00001")
        $invoice_no = $yr . '-INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        return $invoice_no;

    } catch (\Exception $e) {
        log_message('error', 'Invoice number generation failed: ' . $e->getMessage());
        throw new \RuntimeException('Failed to generate invoice number: ' . $e->getMessage());
    }
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



    public function handle_student_chalan()
    {
        $data = $this->request->getPost();

        // Extract and validate data
        $student_id = $data['student_id'];
        $class_id = $data['class_id'];
        $std_type = $data['std_type'];
        $discounted_amount = $data['discounted_amount'];

        $campus_id = session('member_campusid');
        $session_id = session('member_sessionid');
        $user_id = session('member_userid');
        $fee_month = date('Y-m');
        $date = date('Y-m-d');
        $due_date = date('Y-m-d', strtotime('+10 days'));

        // Get all fee types once
        $system_id = getSchoolInfo()->system_id;
        $feeTypes = $this->db->table('fee_type')
            ->select('fee_type_id, is_monthly_fee')
            ->where(['system_id' => $system_id, 'status' => 1])
            ->get()
            ->getResultArray();

        $this->handleInvoiceAndFee(
            $student_id, $class_id, $std_type, $campus_id, $session_id,
            $date, $due_date, $fee_month, $user_id, $date, $feeTypes, $discounted_amount
        );

        return $this->response->setJSON(['status' => 'success']);
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
            s.reg_no,
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
        $label  = fee_chalan_student_display_name($r['student_name'] ?? '', $r['reg_no'] ?? null);

        return [
            'id'         => (int)$r['student_id'],
            'parent_id'  => (int)$r['parent_id'],
            'text'       => "{$label} c/o {$father} {$sec}",
        ];
    }, $rows);

    return $this->response->setJSON($data);
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
        ft.fee_type_name
    ', false);
    $qb->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id', 'left');

    $qb->where('fc.student_id', $student_id);
    $qb->where($this->feeChalanOpenStatusSql('fc'), null, false);

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
        $r['particulars_label'] = trim(($r['fee_type_name'] ?? 'Fee') . ' (' . $r['fee_month_compact'] . ')');
        $rAmt = (float) ($r['amount'] ?? 0);
        $rDisc = $r['discount'] ?? 0;
        if ($rDisc === '' || $rDisc === null) {
            $rDisc = 0.0;
        }
        $rDisc = (float) $rDisc;
        $r['amount']     = $rAmt;
        $r['discount']   = $rDisc;
        $r['net_amount'] = $rAmt - $rDisc;
    }
    unset($r);

    // Filter out fine rows and zero/negative net rows.
    $rows = array_values(array_filter($rows, static function($r) {
        $feeTypeId = (int) ($r['fee_type_id'] ?? 0);
        $labelNorm = strtolower(trim((string) ($r['fee_type_name'] ?? '')));

        if ($feeTypeId === 0 || strpos($labelNorm, 'fine') !== false || strpos($labelNorm, 'late fee') !== false) {
            return false;
        }

        return (float)($r['net_amount'] ?? 0) > 0;
    }));

    return $rows;
}



    private function getStudentFeeItems(int $student_id, string $fee_month): array
    {
        return \Config\Database::connect()
            ->table('fee_chalan')
            ->select('fee_chalan.fee_type_id, fee_chalan.amount, fee_chalan.discount, fee_chalan.fee_month, ft.is_monthly_fee, ft.fee_type_name as fee_name')
            ->join('fee_type ft', 'ft.fee_type_id = fee_chalan.fee_type_id', 'left')
            ->where('student_id', $student_id)
            ->where('fee_month', $fee_month)
            ->get()
            ->getResultArray();
    }

    private function getStudentFineItems(int $student_id, string $fee_month): array
    {
        return \Config\Database::connect()
            ->table('fee_chalan')
            ->select('amount as fine_amount, fee_month')
            ->where('student_id', $student_id)
            ->where('fee_type_id', 0)
            ->where('fee_month', $fee_month)
            ->get()
            ->getResultArray();
    }




}
