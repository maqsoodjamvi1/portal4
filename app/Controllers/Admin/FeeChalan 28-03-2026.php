<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Database\BaseConnection;
use Config\Services;
use CodeIgniter\I18n\Time;  
use stdClass;
use DateTime;

class FeeChalan extends BaseController
{ 

    protected $db;
    protected $session;

    public function __construct()
    {
        
        helper(['form', 'url']);
        $this->db = db_connect();
        $this->session = Services::session();
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
    ];
    
    return view('admin/chalanview/chalan_filter', $data);
}

    /**
     * Generate chalans based on selected options
     */
   public function generate()
{
    $request = service('request');
    
    // Get all filter parameters
    $view_type = $request->getGet('view_type') ?? 'student_three_copy';
    $show_discount = $request->getGet('show_discount') === 'yes';
    $fee_month = $request->getGet('fee_month') ?? '';
    $show_payment_history = $request->getGet('show_payment_history') == 1;
    
    // New message parameters
    $message_text = $request->getGet('message_text') ?? '';
    $message_position = $request->getGet('message_position') ?? 'none';
    
    // Parse view type to determine grouping and layout
    $view_parts = explode('_', $view_type);
    $group_by = $view_parts[0]; // 'student' or 'family'
    $layout = $view_parts[1] . '_' . $view_parts[2]; // 'three_copy' or 'single_page'
    
    $params = [
        'group_by' => $group_by,
        'layout' => $layout,
        'show_discount' => $show_discount,
        'show_payment_history' => $show_payment_history,
        'fee_month' => $fee_month,
        'class_id' => $request->getGet('class_id') ? (int) $request->getGet('class_id') : null,
        'section_id' => $request->getGet('section_id') ? (int) $request->getGet('section_id') : null,
        'search' => $request->getGet('search'),
        'family_id' => $request->getGet('family_id') ? (int) $request->getGet('family_id') : null,
        'footer_line1' => $footer_line1 ?? '',
        'footer_line2' => $footer_line2 ?? '',
        'show_line1' => $show_line1 ?? 0,
        'show_line2' => $show_line2 ?? 0,
        'fine_after_due_date' => $request->getGet('fine_after_due_date') ?? 0,
        // Add message parameters
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
        'is_family' => true,
        'message_text' => $message_text,
        'message_position' => $message_position,
    ]);
    
    // If no data found, show message
    if (empty($viewData['students'] ?? []) && empty($viewData['families'] ?? [])) {
        return view('admin/chalanview/no_data', ['params' => $params]);
    }
    
    return view($viewName, $viewData);
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

    // Build student query with all filters
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
    $builder->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . $session_id . ' AND sc.status = 1', 'inner');
    $builder->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'inner');
    $builder->join('classes c', 'c.class_id = cs.class_id', 'left');
    $builder->join('sections sec', 'sec.section_id = cs.section_id', 'left');
    $builder->join('campus cm', 'cm.campus_id = s.campus_id', 'left');
    $builder->join('system sys', 'sys.system_id = cm.system_id', 'left');

    $builder->where('s.campus_id', $campus_id);
    $builder->where('s.status', 1);

    // Apply class filter (for student-wise view)
    if (!empty($params['class_id'])) {
        $builder->where('cs.class_id', $params['class_id']);
    }
    
    // Apply section filter
    if (!empty($params['section_id'])) {
        $builder->where('cs.section_id', $params['section_id']);
    }

    // Apply search filter
    $hasSpecificSearch = false;
    if (!empty($params['search'])) {
        $searchTerm = $params['search'];
        log_message('debug', 'Applying search filter with term: ' . $searchTerm);
        
        if (is_numeric($searchTerm)) {
            log_message('debug', 'Search term is numeric, treating as student ID: ' . $searchTerm);
            $builder->where('s.student_id', (int)$searchTerm);
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

    // Filter by specific family if provided
    if (!empty($params['family_id'])) {
        $builder->where('p.parent_id', $params['family_id']);
        $hasSpecificSearch = true;
    }

    $builder->orderBy('c.class_name, sec.section_name, s.first_name');
    
    // Apply limits to prevent memory issues
    if (!$hasSpecificSearch) {
        $builder->limit(500); // Increased limit
    } else {
        if (is_numeric($params['search'] ?? '')) {
            $builder->limit(1);
        } else {
            $builder->limit(50);
        }
    }
    
    $query = $builder->get();
    
    if (!$query) {
        log_message('error', 'Student query failed: ' . $this->db->getLastQuery());
        return ['students' => []];
    }
    
    $students = $query->getResultArray();
    log_message('debug', 'Found ' . count($students) . ' students from query');

    // Fetch unpaid chalans and payment history for each student
    foreach ($students as &$studentData) {
        $student_id = $studentData['student_id'];
        log_message('debug', "Processing student ID: {$student_id}, Name: {$studentData['student_name']}");
        
        $fee_month = $params['fee_month'] ?? '';
        
        // Get unpaid chalans
        $studentData['chalans'] = $this->getStudentUnpaidChalans(
            $student_id, 
            $fee_month,
            $params['show_discount'] ?? true
        );
        
        log_message('debug', "Student {$student_id} has " . count($studentData['chalans'] ?? []) . " unpaid chalans");
        
        // Get payment history if requested
        if (!empty($params['show_payment_history'])) {
            $studentData['payment_history'] = $this->getStudentPaymentHistory($student_id);
            log_message('debug', "Student {$student_id} payment history: " . count($studentData['payment_history']['monthly_totals'] ?? []) . " months");
        } else {
            $studentData['payment_history'] = ['month_keys' => [], 'monthly_totals' => []];
        }
        
        // Process chalans to create exactly 7 display rows
        $studentData['display_rows'] = $this->processChalanRows($studentData['chalans'] ?? []);
        
        // Calculate totals
        $studentData['total_payable'] = 0;
        $studentData['total_discount'] = 0;
        
        foreach ($studentData['chalans'] ?? [] as $chalan) {
            $studentData['total_payable'] += $chalan['net_amount'] ?? 0;
            $studentData['total_discount'] += $chalan['discount'] ?? 0;
        }
        
        log_message('debug', "Student {$student_id} total_payable: {$studentData['total_payable']}, total_discount: {$studentData['total_discount']}");
        
        // Get the most recent chalan for header info
        if (!empty($studentData['chalans'])) {
            $latest = $studentData['chalans'][0];
            $studentData['last_chalan_id'] = $latest['chalan_id'] ?? '';
            $studentData['last_issue_date'] = $latest['issue_date_label'] ?? '';
            $studentData['last_due_date'] = $latest['due_date_label'] ?? '';
            
            // Get the latest fee month from the most recent chalan
            $studentData['last_fee_month'] = $latest['fee_month_label'] ?? '';
        } else {
            // If no chalans, set default values
            $studentData['last_chalan_id'] = 'N/A';
            $studentData['last_issue_date'] = date('d-m-y');
            $studentData['last_due_date'] = date('d-m-y', strtotime('+10 days'));
            $studentData['last_fee_month'] = 'No Fee';
        }
    }

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

/**
 * Fetch family-wise chalans - Shows consolidated fees with student names in header only
 */
/**
 * Fetch family-wise chalans - Shows consolidated fees with student names in header only
 */


/**
 * Fetch family-wise chalans - Shows consolidated fees with student names in header only
 */


/**
 * Fetch family-wise chalans - Shows consolidated fees with student names in header only
 */

/**
 * Fetch family-wise chalans - Shows consolidated fees with student names in header only
 */

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
            s.first_name, ' ', COALESCE(s.last_name, ''), '~', 
            s.reg_no, '~', 
            c.class_name, '~', 
            c.class_short_name, '~', 
            sec.short_name, '~', 
            c.class_id
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
    
    // Apply limits
    if (!$hasSpecificSearch) {
        $builder->limit(500);
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
                if (count($parts) >= 6) { // Now includes class_id and all class info
                    $students[] = [
                        'student_name' => $parts[0],
                        'reg_no' => $parts[1],
                        'class_name' => $parts[2],
                        'class_short_name' => $parts[3],
                        'section_short_name' => $parts[4],
                        'class_id' => (int)$parts[5]
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
        
        // Get the elder student's class for display in class field
        $elderClass = $headStudent ? ($headStudent['class_short_name'] ?? $headStudent['class_name'] ?? '') : '';
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
        
        foreach ($family['fee_by_particular'] as $fee) {
            $amount = (float)($fee['total_amount'] ?? 0);
            $discount = (float)($fee['total_discount'] ?? 0);
            
            $family['total_amount'] += $amount;
            $family['total_discount'] += $discount;
            $family['total_payable'] += ($amount - $discount);
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
            $family['payment_history'] = ['monthly_totals' => []];
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
    $sql = "SELECT 
                DATE_FORMAT(date_table.payment_date, '%Y-%m') AS month_key,
                COALESCE(ROUND(SUM(fc.amount - fc.discount), 0), 0) AS monthly_total
            FROM (
                SELECT DATE_SUB(CURDATE(), INTERVAL n MONTH) AS payment_date
                FROM (
                    SELECT 0 AS n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 
                    UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7
                    UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11
                ) numbers
            ) date_table
            LEFT JOIN fee_chalan fc ON DATE_FORMAT(fc.paid_date, '%Y-%m') = DATE_FORMAT(date_table.payment_date, '%Y-%m')
                AND fc.student_id = ?
                AND fc.status = 'paid'
                AND fc.paid_date IS NOT NULL
            GROUP BY date_table.payment_date
            ORDER BY date_table.payment_date";
    
    $query = $this->db->query($sql, [$student_id]);
    
    if (!$query) {
        return ['month_keys' => [], 'monthly_totals' => []];
    }
    
    $results = $query->getResultArray();
    
    $monthKeys = [];
    $monthlyTotals = [];
    
    foreach ($results as $row) {
        $monthKeys[] = $row['month_key'];
        $monthlyTotals[$row['month_key']] = (int)$row['monthly_total'];
    }
    
    return [
        'month_keys' => $monthKeys,
        'monthly_totals' => $monthlyTotals
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
                SELECT fc.*, s.parent_id, s.campus_id
                FROM fee_chalan fc
                INNER JOIN students s ON s.student_id = fc.student_id
                WHERE s.parent_id = ? 
                    AND s.campus_id = ?
                    AND fc.status = 'paid'
                    AND fc.paid_date IS NOT NULL
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
            'payments' => []
        ];
    }
    
    $results = $query->getResultArray();
    
    // Initialize arrays
    $displayMonths = [];
    $shortMonths = [];
    $monthKeys = [];
    $monthlyTotals = [];
    $formattedPayments = [];
    
    foreach ($results as $row) {
        $monthKey = $row['month_key'];
        $shortMonth = $row['short_month'];
        $displayMonth = $row['display_month'];
        $amount = (int)$row['family_total']; // Already rounded by SQL
        
        $monthKeys[] = $monthKey;
        $shortMonths[] = $shortMonth;
        $displayMonths[] = $displayMonth;
        $monthlyTotals[$monthKey] = $amount;
        
        $formattedPayments[] = [
            'month_key' => $monthKey,
            'short_month' => $shortMonth,
            'display_month' => $displayMonth,
            'amount' => $amount,
            'students_count' => (int)$row['students_count'],
            'transactions_count' => (int)$row['transactions_count']
        ];
    }
    
    return [
        'display_months' => $displayMonths,
        'short_months' => $shortMonths,
        'month_keys' => $monthKeys,
        'monthly_totals' => $monthlyTotals,
        'payments' => $formattedPayments
    ];
}
/**
 * Process chalan rows to create exactly 7 display rows
 * If less than 7: add blank rows
 * If more than 7: first 6 rows + 1 arrears row with sum of remaining
 */

private function processChalanRows(array $chalans): array
{
    $displayRows = [];
    $totalChalans = count($chalans);
    
    // If no chalans, return 7 blank rows
    if ($totalChalans == 0) {
        for ($i = 0; $i < 7; $i++) {
            $displayRows[] = [
                'is_blank' => true,
                'particulars_label' => '',
                'amount' => '',
                'discount' => '',
                'net_amount' => 0,
                'fee_month_label' => '',
                'amount_formatted' => '',
                'discount_formatted' => ''
            ];
        }
        return $displayRows;
    }
    
    // If 7 or fewer rows, display all with blanks
    if ($totalChalans <= 7) {
        foreach ($chalans as $chalan) {
            $netAmount = (float)($chalan['net_amount'] ?? 0);
            $amount = (float)($chalan['amount'] ?? 0);
            $discount = (float)($chalan['discount'] ?? 0);
            
            $displayRows[] = [
                'particulars_label' => $chalan['particulars_label'] ?? '',
                'amount' => $amount,
                'discount' => $discount,
                'net_amount' => $netAmount,
                'fee_month_label' => $chalan['fee_month_label'] ?? '',
                'amount_formatted' => number_format($amount, 0),
                'discount_formatted' => $discount > 0 ? number_format($discount, 0) : ''
            ];
        }
        
        // Add blank rows to make total 7
        for ($i = $totalChalans; $i < 7; $i++) {
            $displayRows[] = [
                'is_blank' => true,
                'particulars_label' => '',
                'amount' => '',
                'discount' => '',
                'net_amount' => 0,
                'fee_month_label' => '',
                'amount_formatted' => '',
                'discount_formatted' => ''
            ];
        }
    } 
    // More than 7 rows - take first 6 and combine rest into arrears
    else {
        // Process first 6 rows
        for ($i = 0; $i < 6; $i++) {
            $chalan = $chalans[$i];
            $netAmount = (float)($chalan['net_amount'] ?? 0);
            $amount = (float)($chalan['amount'] ?? 0);
            $discount = (float)($chalan['discount'] ?? 0);
            
            $displayRows[] = [
                'particulars_label' => $chalan['particulars_label'] ?? '',
                'amount' => $amount,
                'discount' => $discount,
                'net_amount' => $netAmount,
                'fee_month_label' => $chalan['fee_month_label'] ?? '',
                'amount_formatted' => number_format($amount, 0),
                'discount_formatted' => $discount > 0 ? number_format($discount, 0) : ''
            ];
        }
        
        // Calculate arrears from remaining rows (indices 6 to end)
        $arrearsTotal = 0;
        $arrearsMonths = [];
        
        for ($i = 6; $i < $totalChalans; $i++) {
            $chalan = $chalans[$i];
            $netAmount = (float)($chalan['net_amount'] ?? 0);
            $arrearsTotal += $netAmount;
            
            if (!empty($chalan['fee_month_label'])) {
                $arrearsMonths[] = $chalan['fee_month_label'];
            }
        }
        
        // Create arrears row
        $monthRange = !empty($arrearsMonths) 
            ? min($arrearsMonths) . ' - ' . max($arrearsMonths)
            : 'Previous Months';
        
        // Log for debugging (remove in production)
        log_message('debug', "Arrears calculation: total rows = $totalChalans, arrears total = $arrearsTotal");
        
        $displayRows[] = [
            'is_arrears' => true,
            'particulars_label' => 'Arrears (' . $monthRange . ')',
            'amount' => $arrearsTotal,
            'discount' => 0,
            'net_amount' => $arrearsTotal,
            'fee_month_label' => 'Arrears',
            'amount_formatted' => number_format($arrearsTotal, 0),
            'discount_formatted' => ''
        ];
    }
    
    return $displayRows;
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
            INNER JOIN fee_type ft ON ft.fee_type_id = fc.fee_type_id
            WHERE fc.student_id IN (" . $studentIdList . ")
            AND fc.status = 'unpaid'";
    
    $params = [];
    
    if (!empty($fee_month)) {
        $sql .= " AND fc.fee_month = ?";
        $params[] = $fee_month;
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
            'month_display' => $row['month_display'] ?? ''
        ];
    }
    
    return $formattedResults;
}
/**
 * Process family fee rows to create exactly 7 display rows
 */
private function processFamilyFeeRows(array $feeByParticular): array
{
    $displayRows = [];
    $totalRows = count($feeByParticular);
    
    // Log for debugging
    log_message('debug', "processFamilyFeeRows: Processing $totalRows rows");
    
    if ($totalRows == 0) {
        // No data - return 7 blank rows
        for ($i = 0; $i < 7; $i++) {
            $displayRows[] = [
                'is_blank' => true,
                'particulars_label' => '',
                'amount' => '',
                'discount' => '',
                'net_amount' => 0,
                'amount_formatted' => '',
                'discount_formatted' => ''
            ];
        }
        return $displayRows;
    }
    
    if ($totalRows <= 7) {
        // Case 1: Less than or equal to 7 rows - display all and pad with blanks
        foreach ($feeByParticular as $row) {
            $amount = (float)($row['total_amount'] ?? 0);
            $discount = (float)($row['total_discount'] ?? 0);
            $netAmount = $amount - $discount;
            
            $displayRows[] = [
                'particulars_label' => $row['particulars_label'] ?? '',
                'amount' => $amount,
                'discount' => $discount,
                'net_amount' => $netAmount,
                'amount_formatted' => number_format($amount, 0),
                'discount_formatted' => $discount > 0 ? number_format($discount, 0) : '',
                'net_amount_formatted' => number_format($netAmount, 0),
                'month_display' => $row['month_display'] ?? ''
            ];
        }
        
        // Add blank rows to make total 7
        for ($i = $totalRows; $i < 7; $i++) {
            $displayRows[] = [
                'is_blank' => true,
                'particulars_label' => '',
                'amount' => '',
                'discount' => '',
                'net_amount' => 0,
                'amount_formatted' => '',
                'discount_formatted' => ''
            ];
        }
    } else {
        // Case 2: More than 7 rows - take first 6 and combine rest into arrears
        // Process first 6 rows
        for ($i = 0; $i < 6; $i++) {
            $row = $feeByParticular[$i];
            $amount = (float)($row['total_amount'] ?? 0);
            $discount = (float)($row['total_discount'] ?? 0);
            $netAmount = $amount - $discount;
            
            $displayRows[] = [
                'particulars_label' => $row['particulars_label'] ?? '',
                'amount' => $amount,
                'discount' => $discount,
                'net_amount' => $netAmount,
                'amount_formatted' => number_format($amount, 0),
                'discount_formatted' => $discount > 0 ? number_format($discount, 0) : '',
                'net_amount_formatted' => number_format($netAmount, 0),
                'month_display' => $row['month_display'] ?? ''
            ];
        }
        
        // Calculate arrears from remaining rows (indices 6 to end)
        $arrearsAmount = 0;
        $arrearsDiscount = 0;
        $arrearsNet = 0;
        $arrearsMonths = [];
        
        for ($i = 6; $i < $totalRows; $i++) {
            $row = $feeByParticular[$i];
            $amount = (float)($row['total_amount'] ?? 0);
            $discount = (float)($row['total_discount'] ?? 0);
            $netAmount = $amount - $discount;
            
            $arrearsAmount += $amount;
            $arrearsDiscount += $discount;
            $arrearsNet += $netAmount;
            
            if (!empty($row['month_display'])) {
                $arrearsMonths[] = $row['month_display'];
            }
        }
        
        // Create month range for arrears label
        $monthRange = '';
        if (!empty($arrearsMonths)) {
            // Sort months to get min and max
            sort($arrearsMonths);
            $monthRange = $arrearsMonths[0] . ' - ' . $arrearsMonths[count($arrearsMonths) - 1];
        } else {
            $monthRange = 'Previous Months';
        }
        
        // Log for debugging
        log_message('debug', "Family Arrears: amount=$arrearsAmount, discount=$arrearsDiscount, net=$arrearsNet, months=" . implode(', ', $arrearsMonths));
        
        // Create arrears row
        $displayRows[] = [
            'is_other' => true,
            'particulars_label' => 'Arrears (' . $monthRange . ')',
            'amount' => $arrearsAmount,
            'discount' => $arrearsDiscount,
            'net_amount' => $arrearsNet,
            'amount_formatted' => number_format($arrearsAmount, 0),
            'discount_formatted' => $arrearsDiscount > 0 ? number_format($arrearsDiscount, 0) : '',
            'net_amount_formatted' => number_format($arrearsNet, 0)
        ];
    }
    
    return $displayRows;
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
                SUM(fc.amount - fc.discount) as total_amount,
                SUM(fc.discount) as total_discount
            FROM fee_chalan fc
            INNER JOIN students s ON s.student_id = fc.student_id
            WHERE fc.student_id IN (" . $studentIdList . ")
            AND fc.status = 'unpaid'";
    
    $params = [];
    
    if (!empty($fee_month)) {
        $sql .= " AND fc.fee_month = ?";
        $params[] = $fee_month;
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
 * Get unpaid chalans for a student
 * If fee_month is empty, returns ALL unpaid records
 * If fee_month is provided, returns only records for that month
 */
/**
 * Get unpaid chalans for a student
 * If fee_month is empty, returns ALL unpaid records
 * If fee_month is provided, returns only records for that month
 */

private function getStudentUnpaidChalans(int $student_id, ?string $fee_month, bool $show_discount): array
{
    $builder = $this->db->table('fee_chalan fc');
    $builder->select("
        fc.chalan_id,
        fc.fee_month,
        fc.issue_date,
        fc.due_date,
        fc.amount,
        fc.discount,
        (fc.amount - fc.discount) as net_amount,
        ft.fee_type_name as particulars_label,
        ft.is_monthly_fee,
        fc.status
    ");
    
    $builder->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id', 'left');
    $builder->where('fc.student_id', $student_id);
    $builder->where('fc.status', 'unpaid');
    
    if (!empty($fee_month)) {
        $builder->where('fc.fee_month', $fee_month);
    }
    
    $builder->orderBy('fc.fee_month', 'DESC');
    $builder->orderBy('fc.issue_date', 'DESC');
    
    $query = $builder->get();
    
    if (!$query) {
        return [];
    }
    
    $chalans = $query->getResultArray();
    
    // Format dates and numbers
    foreach ($chalans as &$chalan) {
        // Ensure net_amount is properly calculated as float
        $chalan['net_amount'] = (float)($chalan['amount'] ?? 0) - (float)($chalan['discount'] ?? 0);
        
        $chalan['issue_date_label'] = !empty($chalan['issue_date']) 
            ? date('d-m-y', strtotime($chalan['issue_date'])) 
            : '';
        $chalan['due_date_label'] = !empty($chalan['due_date']) 
            ? date('d-m-y', strtotime($chalan['due_date'])) 
            : '';
        
        // Format fee month as MM/YYYY
        $chalan['fee_month_label'] = $this->formatFeeMonthLabel($chalan['fee_month'] ?? '');
        
        $chalan['amount_formatted'] = number_format($chalan['amount'] ?? 0, 0);
        $chalan['discount_formatted'] = $show_discount && !empty($chalan['discount']) 
            ? number_format($chalan['discount'], 0) 
            : '';
        $chalan['net_amount_formatted'] = number_format($chalan['net_amount'], 0);
    }
    
    return $chalans;
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
    $builder->select('cs.section_id, sec.section_name');
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
            CONCAT(
                s.first_name, ' ', COALESCE(s.last_name, ''), 
                ' (', s.reg_no, ') - ', 
                COALESCE(c.class_name, ''), ' ', COALESCE(sec.section_name, ''),
                ' | Father: ', COALESCE(p.f_name, '')
            ) as text
        ");

        $builder->join('parents p', 'p.parent_id = s.parent_id', 'left');
        $builder->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . $session_id . ' AND sc.status = 1', 'inner');
        $builder->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'inner');
        $builder->join('classes c', 'c.class_id = cs.class_id', 'left');
        $builder->join('sections sec', 'sec.section_id = cs.section_id', 'left');

        $builder->where('s.campus_id', $campus_id);
        $builder->where('s.status', 1);

        // Search by name, reg no, or CNIC
        if (!empty($term)) {
            $builder->groupStart()
                ->like('s.first_name', $term)
                ->orLike('s.last_name', $term)
                ->orLike('CONCAT(s.first_name, " ", s.last_name)', $term)
                ->orLike('s.reg_no', $term)
                ->orLike('p.f_name', $term)
                ->orLike('p.father_cnic', $term)
                ->orLike('p.father_contact', $term)
                ->groupEnd();
        }

        if (!empty($class_id)) {
            $builder->where('cs.class_id', $class_id);
        }

        if (!empty($section_id)) {
            $builder->where('cs.section_id', $section_id);
        }

        $builder->limit(20);
        
        $query = $builder->get();
        
        if (!$query) {
            log_message('error', 'Student search query failed: ' . $this->db->getLastQuery());
            return $this->response->setJSON([]);
        }
        
        $results = $query->getResultArray();
        log_message('debug', 'Found ' . count($results) . ' results');
        
        $formatted = array_map(function($r) {
            return [
                'id' => $r['id'],
                'text' => $r['text'],
                'parent_id' => $r['parent_id'] ?? 0,
                'student_id' => $r['id']
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

    public function hostel()
    {
        return $this->renderChalan('admin/chalanview/fee_chalan_hostel', false, true);
    }

    public function withHeader()
    {
        return $this->renderChalan('admin/chalanview/fee_chalan_with_header');
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

        echo view($viewName, $data);
        exit;
    }

    // Add these methods if they don't exist (from your original code)
    private function fetchChalanData(
        bool $isFamilywise = false,
        bool $isHostel = false,
        ?int $cls_sec_id = null,
        ?string $fee_month = null
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

            // Total payable across unpaid
            $totalAll = 0.0;
            foreach ($unpaid as $u) {
                $totalAll += (float)($u['net_amount'] ?? ((float)($u['amount'] ?? 0) - (float)($u['discount'] ?? 0)));
            }

            // Skip student entirely if no payable
            if ($totalAll <= 0) {
                unset($students[$k]);
                continue;
            }

            // Late fee info
            $row['late_fee_fine'] = $late->late_fee_fine ?? null;
            $row['fine_type']     = $late->fine_type     ?? null;

            // Build exactly 7 display rows:
            if (count($unpaid) <= 5) {
                $display = $unpaid;
                for ($i = count($display); $i < 5; $i++) {
                    $display[] = [
                        'particulars_label' => '',
                        'amount'            => '',
                        'discount'          => '',
                        'net_amount'        => 0,
                        'is_blank'          => 1,
                    ];
                }
            } else {
                $latestSix = array_slice($unpaid, 0, 6);
                $older     = array_slice($unpaid, 6);

                $arrearsSum = 0.0;
                foreach ($older as $o) {
                    $arrearsSum += (float)($o['net_amount'] ?? ((float)($o['amount'] ?? 0) - (float)($o['discount'] ?? 0)));
                }

                $display = $latestSix;
                $display[] = [
                    'particulars_label' => 'Arrears',
                    'amount'            => number_format($arrearsSum, 2, '.', ''),
                    'discount'          => '',
                    'net_amount'        => $arrearsSum,
                    'is_arrears'        => 1,
                ];
            }

            $row['unpaid_rows']          = $unpaid;
            $row['unpaid_display_rows']  = $display;
            $row['unpaid_total_payable'] = $totalAll;
        }
        unset($row);

        return array_values($students);
    }

    private function fetchFamilywiseChalanData()
    {
        // Implement if needed
        return [];
    }

    private function fetchHostelChalanData()
    {
        // Implement if needed
        return [];
    }




// advance fee type id = 194
// In your controller (e.g., app/Controllers/Admin/FeeChalan.php)

public function add()
{
    $db         = \Config\Database::connect();
    $campusInfo = getCampusInfo();
    $schoolInfo = getSchoolInfo();
    $system_id  = (int) ($schoolInfo->system_id ?? 0);
    $campus_id = (int) session()->get('member_campusid');

    // Log for debugging
    log_message('debug', "ADD METHOD: campus_id = $campus_id, system_id = $system_id");

    // dropdown data
    $fee_type_info = $db->table('fee_type')->where('s_flag',1)->where('system_id',$system_id)->where('status',1)->get()->getResult();
    $a_fee_type_info = $db->table('fee_type')->where('a_flag',1)->where('system_id',$system_id)->where('status',1)->get()->getResult();
    $t_fee_type_info = $db->table('fee_type')->where('t_flag',1)->where('system_id',$system_id)->where('status',1)->get()->getResult();
    $h_fee_type_info = $db->table('fee_type')->where('h_flag',1)->where('system_id',$system_id)->where('status',1)->get()->getResult();

    // Get classes for dropdown
    $classes = [];
    try {
        $classesQuery = $db->table('class_section cs')
            ->select('DISTINCT cs.class_id, c.class_name')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->where('cs.campus_id', $campus_id)
            ->orderBy('c.class_name')
            ->get();
        
        if ($classesQuery) {
            $classes = $classesQuery->getResultArray();
        }
        log_message('debug', "ADD METHOD: Found " . count($classes) . " classes");
    } catch (\Exception $e) {
        log_message('error', "Error fetching classes: " . $e->getMessage());
    }

    // Get sections for dropdown
    $sectionsclassinfo = [];
    try {
        $sectionsQuery = $db->table('class_section cs')
            ->select('cs.cls_sec_id, cs.section_id, CONCAT(c.class_name, " - ", sec.section_name) as sectionclassname')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections sec', 'sec.section_id = cs.section_id')
            ->where('cs.campus_id', $campus_id)
            ->orderBy('c.class_name, sec.section_name')
            ->get();
        
        if ($sectionsQuery) {
            $sectionsclassinfo = $sectionsQuery->getResultArray();
        }
        log_message('debug', "ADD METHOD: Found " . count($sectionsclassinfo) . " sections");
    } catch (\Exception $e) {
        log_message('error', "Error fetching sections: " . $e->getMessage());
    }

    // Get base URL for form action
    $base_url = base_url('admin/fee-chalan/save');

    $data = [
        'mode'                  => 'add',
        'isEdit'                => false,
        'pageTitle'             => 'Generate Fee Chalan',
        'campusInfo'            => $campusInfo,
        'fee_type_info'         => $fee_type_info,
        'a_fee_type_info'       => $a_fee_type_info,
        't_fee_type_info'       => $t_fee_type_info,
        'h_fee_type_info'       => $h_fee_type_info,
        'fee_chalan'            => null,
        'selected_fee_type_ids' => [],
        // Add these missing variables
        'classes'               => $classes,
        'sectionsclassinfo'     => $sectionsclassinfo,
        'base_url'              => $base_url,
        // Add filter variables (optional, for consistency)
        'selected_view'         => 'student_three_copy',
        'show_discount'         => 'yes',
        'fee_month'             => date('Y-m'),
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
 * Get edit form for a specific student's chalan
 */
public function getEditForm()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
    }
    
    $student_id = (int) $this->request->getPost('student_id');
    $campus_id = (int) session()->get('member_campusid');
    
    if (!$student_id) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Student ID required']);
    }
    
    // Get student info
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
    
    // Get unpaid chalans for this student
    $chalans = $this->db->table('fee_chalan fc')
        ->select('fc.*, ft.fee_type_name')
        ->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id', 'left')
        ->where('fc.student_id', $student_id)
        ->where('fc.status', 'unpaid')
        ->orderBy('fc.fee_month', 'DESC')
        ->orderBy('fc.issue_date', 'DESC')
        ->get()
        ->getResultArray();
    
    // Load the edit form view
    $html = view('admin/chalanview/partials/chalan_edit_form', [
        'student' => $student,
        'chalans' => $chalans,
        'csrf_token' => csrf_token(),
        'csrf_hash' => csrf_hash()
    ]);
    
    return $this->response->setJSON([
        'success' => true,
        'html' => $html
    ]);
}

/**
 * Save edited chalan
 */
public function saveEdit()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
    }
    
    $chalan_ids = $this->request->getPost('chalan_id');
    $amounts = $this->request->getPost('amount');
    $discounts = $this->request->getPost('discount');
    $statuses = $this->request->getPost('status');
    
    if (empty($chalan_ids) || !is_array($chalan_ids)) {
        return $this->response->setJSON(['success' => false, 'msg' => 'No data to update']);
    }
    
    $this->db->transBegin();
    
    try {
        foreach ($chalan_ids as $index => $chalan_id) {
            $data = [
                'amount' => (float)($amounts[$index] ?? 0),
                'discount' => (float)($discounts[$index] ?? 0),
                'status' => $statuses[$index] ?? 'unpaid',
                'updated_date' => date('Y-m-d H:i:s')
            ];
            
            $this->db->table('fee_chalan')
                ->where('chalan_id', $chalan_id)
                ->update($data);
        }
        
        $this->db->transCommit();
        
        return $this->response->setJSON([
            'success' => true,
            'msg' => count($chalan_ids) . ' chalan(s) updated successfully'
        ]);
        
    } catch (\Exception $e) {
        $this->db->transRollback();
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error updating chalans: ' . $e->getMessage()
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
                'student_name'  => trim($row->first_name . ' ' . $row->last_name),
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
    // Increase execution time limits
    set_time_limit(300); // 5 minutes
    ini_set('max_execution_time', 300);
    ini_set('memory_limit', '512M');
    
    // Disable output buffering completely
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set correct headers (only once!)
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('X-Accel-Buffering: no'); // Disable Nginx buffering
    
    // Disable PHP output compression
    ini_set('zlib.output_compression', 0);
    
    // Send initial message to test connection
    echo "retry: 1000\n\n";
    flush();
    
    // Release session lock to prevent blocking
    session_write_close();
    
    $request = \Config\Services::request();
    $db      = \Config\Database::connect();

    // ----------------- incoming params -----------------
    $fee_type_ids = $request->getGet('fee_type_ids');
    if (!is_array($fee_type_ids)) {
        $fee_type_ids = array_filter(array_map('trim', explode(',', (string) $fee_type_ids)));
    }

    $fee_month      = $request->getGet('fee_month');   // e.g. 2025-11
    $issue_date_raw = $request->getGet('issue_date');  // e.g. 01/11/2025
    $due_date_raw   = $request->getGet('due_date');
    $force_month    = (int) $request->getGet('force_month') === 1;

    $issue_date = DateTime::createFromFormat('d/m/Y', (string) $issue_date_raw);
    $due_date   = DateTime::createFromFormat('d/m/Y', (string) $due_date_raw);

    $issue_date_formatted = $issue_date ? $issue_date->format('Y-m-d') : null;
    $due_date_formatted   = $due_date   ? $due_date->format('Y-m-d')   : null;

    if (empty($fee_month) || empty($fee_type_ids) || !$issue_date_formatted || !$due_date_formatted) {
        $this->sendEvent(['type' => 'error', 'message' => 'Missing or invalid required parameters']);
        exit;
    }

    try {
        $session_id = (int) session('member_sessionid');
        $campus_id  = (int) session('member_campusid');
        $user_id    = (int) session('member_userid');
        $system_id  = (int) (getSchoolInfo()->system_id ?? 0);
        $date       = date('Y-m-d');

        // month “tokens” for fee_plan_months
        $monthTs         = strtotime($fee_month . '-01');
        $monthFull       = date('F', $monthTs);   // 'November'
        $monthShort      = date('M', $monthTs);   // 'Nov'
        $monthNum2       = date('m', $monthTs);   // '11'
        $monthNum1       = date('n', $monthTs);   // '11' (no leading zero)
        $monthKey        = date('Y-m', $monthTs); // '2025-11'
        $monthCandidates = [$monthFull, $monthShort, $monthNum2, (string) $monthNum1, $monthKey];

        // cache for plans
        $planCache = [];

        // ----------------- load students -----------------
        $studentsRes = $db->table('student_class sc')
            ->select('sc.student_id, cs.class_id, s.std_type, s.discounted_amount, s.fee_plan')
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
            'current_student' => 'Initializing'
        ]);

        // ----------------- load fee types -----------------
        $feeTypesRes = $db->table('fee_type')
            ->select('fee_type_id, fee_type_name, is_monthly_fee')
            ->where('system_id', $system_id)
            ->where('status', 1)
            ->whereIn('fee_type_id', $fee_type_ids)
            ->get();

        $this->dbError($db, 'fetch_fee_types');

        $feeTypes = $feeTypesRes ? $feeTypesRes->getResultArray() : [];

        $successCount = 0;
        $skippedCount = 0;
        $processed    = 0;
        $batchSize    = 10;

        foreach (array_chunk($students, $batchSize) as $studentBatch) {
            if (connection_aborted()) {
                exit;
            }

            foreach ($studentBatch as $student) {
                $insertable_fee_types = [];

                foreach ($feeTypes as $feeType) {
                    $allowInsert   = true;
                    $thisPlanValue = 1;

                    // only monthly fees need plan/month check
                    if ((int) $feeType['is_monthly_fee'] === 1) {

                        if ((int) $student->fee_plan === 0) {
                            // no plan → always 1
                            $thisPlanValue = 1;

                        } else {
                            $fp = (int) $student->fee_plan;

                            // get plan_value once
                            if (!array_key_exists($fp, $planCache)) {
                                $planRow = $db->table('fee_plans')
                                    ->select('plan_value')
                                    ->where('plan_id', $fp)
                                    ->get()
                                    ->getRow();
                                $planCache[$fp] = $planRow ? (int) $planRow->plan_value : 1;
                            }
                            $thisPlanValue = $planCache[$fp];

                            // if month is not active → skip ONLY this fee type
                            if (! $force_month) {
                                $monthExists = $db->table('fee_plan_months')
                                    ->where('campus_id',  $campus_id)
                                    ->where('fee_plan_id', $fp)
                                    ->whereIn('month', $monthCandidates)
                                    ->where('status', 1)
                                    ->countAllResults();

                                $this->dbError($db, 'check_plan_month');

                                if ((int) $monthExists === 0) {
                                    $allowInsert = false;
                                }
                            }
                        }
                    }

                    if ($allowInsert) {
                        $insertable_fee_types[] = [
                            'fee_type_id'    => (int) $feeType['fee_type_id'],
                            'is_monthly_fee' => (int) $feeType['is_monthly_fee'],
                            'plan_value'     => (int) $thisPlanValue,
                        ];
                    }
                } // end foreach feeTypes

                // if after filtering there is NOTHING to insert → mark skipped
                if (empty($insertable_fee_types)) {
                    $processed++;
                    $skippedCount++;

                    $this->sendEvent([
                        'type'            => 'progress',
                        'processed'       => $processed,
                        'total'           => $totalStudents,
                        'current_student' => (int) $student->student_id,
                        'success'         => $successCount,
                        'skipped'         => $skippedCount,
                        'reason'          => 'no_active_fee_types_for_month',
                    ]);

                    continue;
                }

                // now call your existing insert logic
                $result = $this->handleInvoiceAndFee(
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
                    $student->discounted_amount
                );

                $processed++;
                if ($result === true) {
                    $successCount++;
                } else {
                    $skippedCount++;
                }

                $this->sendEvent([
                    'type'            => 'progress',
                    'processed'       => $processed,
                    'total'           => $totalStudents,
                    'current_student' => (int) $student->student_id,
                    'success'         => $successCount,
                    'skipped'         => $skippedCount
                ]);
            }

            usleep(100000);
        }

        $this->sendEvent([
            'type'    => 'complete',
            'total'   => $totalStudents,
            'success' => $successCount,
            'skipped' => $skippedCount
        ]);

    } catch (\Throwable $e) {
        log_message('error', 'Bulk chalan generation failed: ' . $e->getMessage());
        $this->sendEvent([
            'type'      => 'error',
            'message'   => 'Error: ' . $e->getMessage(),
            'processed' => $processed ?? 0,
            'total'     => $totalStudents ?? 0
        ]);
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


private function handleInvoiceAndFee(
    int $student_id, int $class_id, int $std_type, int $campus_id, int $session_id,
    string $issue_date, string $due_date, string $fee_month, int $user_id, string $date,
    array $feeTypes, $monthly_discount
) {
    $db = \Config\Database::connect();
    $db->transBegin();

    try {
        // Existing invoice?
        $invRes = $db->table('invoices')
            ->where('student_id', $student_id)
            ->where('fee_month',  $fee_month)
            ->where('issue_date', $issue_date)
            ->get();
        $this->dbError($db, 'invoice_lookup');

        $existingInvoice = $invRes ? $invRes->getRow() : null;
        $invoice_no = $existingInvoice ? $existingInvoice->invoice_no : $this->generateInvoiceNumber($fee_month);

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
                return false;
            }
            $this->debug('invoice_insert_ok', ['student_id' => $student_id, 'invoice_no' => $invoice_no]);
        }

        $insertedCount = 0;

        foreach ($feeTypes as $fee) {
            $fee_type_id = (int)$fee['fee_type_id'];
            $isMonthly   = !empty($fee['is_monthly_fee']);  // THIS IS CORRECT - checking is_monthly_fee = 1

            // Already exists?
            $exists = $db->table('fee_chalan')
                ->where('student_id', $student_id)
                ->where('fee_month',  $fee_month)
                ->where('fee_type_id',$fee_type_id)
                ->where('invoice_no', $invoice_no)
                ->countAllResults();
            $this->dbError($db, 'chalan_exists_check');

            if ((int)$exists > 0) {
                $this->debug('chalan_skip_exists', [
                    'student_id' => $student_id,
                    'fee_type_id'=> $fee_type_id,
                    'invoice_no' => $invoice_no
                ]);
                continue;
            }

            // Amount lookup (allow default/null flag)
            $amountRes = $db->table('fee_amount')->select('amount')
                ->where('class_id',   $class_id)
                ->where('campus_id',  $campus_id)
                ->where('session_id', $session_id)
                ->where('fee_type_id',$fee_type_id)
                ->get();
            $this->dbError($db, 'amount_lookup');

            $amountRow = $amountRes ? $amountRes->getRow() : null;
            if (!$amountRow) {
                $this->debug('amount_not_found', [
                    'student_id' => $student_id, 'class_id' => $class_id,
                    'fee_type_id'=> $fee_type_id, 
                ]);
                continue;
            }

            $default_amount = (float) $amountRow->amount;
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
                'status'         => 'Unpaid',   // ensure matches ENUM casing if used
                'payment_status' => 'Pending',
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
            return true;
        }

        $this->debug('no_rows_inserted_for_student', [
            'student_id' => $student_id,
            'invoice_no' => $invoice_no
        ]);
        $db->transRollback();
        return false;

    } catch (\Throwable $e) {
        $db->transRollback();
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
    $ADVANCE_FEE_TYPE_ID = 194;

    // 1) Find latest advance row with a positive remaining amount
    $adv = $db->table('fee_chalan')
        ->select('chalan_id, amount, COALESCE(discount,0) AS discount, paid_date')
        ->where('student_id', $studentId)
        ->where('fee_type_id', $ADVANCE_FEE_TYPE_ID)
        ->where('status', 'paid')                 // adjust if you store 'Paid'
        ->where('amount >', 0)
        ->orderBy('paid_date', 'DESC')
        ->orderBy('chalan_id', 'DESC')
        ->get()
        ->getRow();

    if (!$adv) {
        return; // no advance to apply
    }

    // NOTE: if you keep discount on advance rows, treat it as 0 here.
    $advanceAvail = (float) $adv->amount;

    // 2) Fetch *unpaid* chalans of this month (all fee types you just generated)
    $unpaidRows = $db->table('fee_chalan')
        ->select('chalan_id, student_id, fee_type_id, invoice_no, amount, COALESCE(discount,0) AS discount')
        ->where('student_id', $studentId)
        ->where('fee_month',  $feeMonth)
        ->where('status',     'unpaid')          // adjust if you store 'Unpaid'
        ->orderBy('chalan_id', 'ASC')
        ->get()
        ->getResultArray();

    if (!$unpaidRows || $advanceAvail <= 0) {
        return;
    }

    $db->transStart();

    foreach ($unpaidRows as $row) {
        if ($advanceAvail <= 0) break;

        $chalanId   = (int) $row['chalan_id'];
        $discount   = (float) $row['discount'];              // usually 0
        $payable    = max(0.0, (float) $row['amount'] - $discount);

        if ($payable <= 0) {
            continue;
        }

        if ($advanceAvail >= $payable) {
            // Fully cover this chalan
            $db->table('fee_chalan')->where('chalan_id', $chalanId)->update([
                'status'         => 'paid',                  // adjust to your status text
                'payment_status' => 'completed',
                'paid_date'      => $adv->paid_date,         // <-- REQUIRED: use advance paid_date
                'updated_date'   => date('Y-m-d H:i:s'),
                'user_id'        => $userId,
            ]);
            $advanceAvail -= $payable;

        } else {
            // Partial cover: split the row into (paid part) + (unpaid remainder)
            // Keep the *same discount* on the paid row (common case: 0)
            $paidPortionPayable = $advanceAvail;

            // New amount for the PAID (updated) row = (paid payable + discount)
            $newPaidAmount = round($paidPortionPayable + $discount, 2);

            $db->table('fee_chalan')->where('chalan_id', $chalanId)->update([
                'amount'         => $newPaidAmount,
                'status'         => 'paid',
                'payment_status' => 'completed',
                'paid_date'      => $adv->paid_date,
                'updated_date'   => date('Y-m-d H:i:s'),
                'user_id'        => $userId,
            ]);

            // Remainder as a new UNPAID row (discount 0 to keep accounting simple)
            $remainderPayable = round($payable - $paidPortionPayable, 2);

            $db->table('fee_chalan')->insert([
                'student_id'     => $row['student_id'],
                'due_date'       => $dueDateYmd,
                'issue_date'     => $issueDateYmd,
                'fee_month_old'  => $feeMonth,          // keep if you mirror it
                'fee_month'      => $feeMonth,
                'amount'         => $remainderPayable,   // remainder payable (no discount)
                'discount'       => 0,
                'status'         => 'unpaid',
                'payment_status' => 'pending',
                'fee_type_id'    => $row['fee_type_id'],
                'paid_date'      => $issueDateYmd,       // required NOT NULL; keep consistent with your inserts
                'created_date'   => date('Y-m-d H:i:s'),
                'updated_date'   => date('Y-m-d H:i:s'),
                'user_id'        => $userId,
                'invoice_no'     => $row['invoice_no'],  // keep same invoice_no if you want them grouped
            ]);

            // advance fully used
            $advanceAvail = 0;
            break;
        }
    }

    // 3) Write the remaining advance back to the *same* advance row
    //    (e.g. 5000 - 2800 = 2200 left)
    $db->table('fee_chalan')->where('chalan_id', (int) $adv->chalan_id)->update([
        'amount'       => round($advanceAvail, 2),
        'updated_date' => date('Y-m-d H:i:s'),
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
