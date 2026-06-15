<?php 

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CampusFinanceInstaller;
use App\Libraries\CampusFinanceService;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class FeeChalanPay extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {

 helper(['form', 'url']);
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
        check_any_permission(['admin-fee-chalan-pay', 'admin-fee-chalan']);
    }

   public function index()
    {
        $currentRole        = currentUserRoles();
        $sectionsClassInfo  = in_array(5, $currentRole) ? teacherSubjectSections() : userClassSections();

        $campus_id = (int) (session('member_campusid') ?? 1);

        $finance = new CampusFinanceService($this->db);
        if (! $finance->tablesReady()) {
            (new CampusFinanceInstaller($this->db))->ensureAll();
        }
        if ($finance->tablesReady()) {
            $finance->ensureCampusCashAccount($campus_id, (int) session('member_userid'));
        }

        $userId   = (int) session('member_userid');
        $financePayload = $finance->getAccountsPayload($campus_id, $userId);

        $data = [
            'sectionsclassinfo' => $sectionsClassInfo,
            'payFeeModal'       => $this->generatePayFeeModal(),
            'campus_info'       => session('member_campusid'),
            'session_id'        => session('member_sessionid'),
            'paidTotals'        => $this->getPaidTotals($campus_id),
            'finance_enabled'   => (bool) ($financePayload['enabled'] ?? false),
            'finance_accounts'  => $financePayload['accounts'] ?? [],
            'default_collection_account_id' => (int) ($financePayload['default_account_id'] ?? 0),
            'received_by_name'  => (string) ($financePayload['received_by'] ?? ''),
        ];

        return view('admin/fee_chalan_pay', $data);
    }



private function getPaidTotals(int $campus_id): array
{
    $monthStart = date('Y-m-01 00:00:00');
    $monthEnd   = date('Y-m-t 23:59:59');
    $today      = date('Y-m-d');

    // --- This Month ---
    $advanceTypeId = advance_fee_type_id();

    $monthRow = $this->db->table('fee_chalan fc')
        ->select('SUM(fc.amount - fc.discount) AS total', false)
        ->join('students s', 's.student_id = fc.student_id', 'inner')
        ->where('s.campus_id', $campus_id)
        ->where('fc.status', 'paid')
        ->where('fc.fee_type_id !=', $advanceTypeId)
        ->where('fc.updated_date >=', $monthStart)
        ->where('fc.updated_date <=', $monthEnd)
        ->get()
        ->getRow();

    // --- Today ---
    $todayRow = $this->db->table('fee_chalan fc')
        ->select('SUM(fc.amount - fc.discount) AS total', false)
        ->join('students s', 's.student_id = fc.student_id', 'inner')
        ->where('s.campus_id', $campus_id)
        ->where('fc.status', 'paid')
        ->where('fc.fee_type_id !=', $advanceTypeId)
        ->where('DATE(fc.updated_date)', $today)
        ->get()
        ->getRow();

    return [
        'month' => (float) ($monthRow->total ?? 0),
        'today' => (float) ($todayRow->total ?? 0),
    ];
}



public function familyHistory($parentId)
{
    $records = $this->db->table('fee_payment fp')
        ->select('s.student_name, fp.amount, fp.paid_date, ft.fee_type_name, fp.receipt_no')
        ->join('students s', 's.student_id = fp.student_id')
        ->join('fee_type ft', 'ft.fee_type_id = fp.fee_type_id')
        ->where('s.parent_id', $parentId)
        ->orderBy('fp.paid_date', 'DESC')
        ->get()
        ->getResult();

    return view('fee/family_history_report', ['records' => $records]);
}

/**
 * Get class monthly fee directly
 * 
 * @param int $class_id
 * @param int $campus_id  
 * @param int $session_id
 * @return float
 */
protected function getClassMonthlyFee($class_id, $campus_id, $session_id)
{
    if (empty($class_id) || empty($campus_id) || empty($session_id)) {
        return 0.0;
    }
    
    $result = $this->db->table('fee_amount fa')
        ->select('fa.amount')
        ->join('fee_type ft', 'ft.fee_type_id = fa.fee_type_id')
        ->where('fa.class_id', $class_id)
        ->where('fa.campus_id', $campus_id)
        ->where('fa.session_id', $session_id)
        ->where('ft.is_monthly_fee', 1)
        ->where('ft.status', 1)  // Only active fee types
        ->get()
        ->getRow();
    
    return (float)($result->amount ?? 0.0);
}

/**
 * Get student's monthly fee (class fee - discount)
 */
protected function getStudentMonthlyFee($student_id, $session_id, $campus_id)
{
    // Get student's discount
    $student = $this->db->table('students')
        ->select('discounted_amount')
        ->where('student_id', $student_id)
        ->get()
        ->getRow();
    
    $discountAmount = (float)($student->discounted_amount ?? 0);
    
    // Get student's current class
    $classId = $this->getStudentCurrentClassId($student_id, $session_id);
    
    if ($classId == 0) {
        return 0.0;
    }
    
    // Get class monthly fee
    $classFee = $this->getClassMonthlyFee($classId, $campus_id, $session_id);
    
    // Calculate: Class Fee - Discount
    return max(0, $classFee - $discountAmount);
}

/**
 * Helper: Get student's current class ID
 */
protected function getStudentCurrentClassId($student_id, $session_id)
{
    $result = $this->db->table('student_class sc')
        ->select('cs.class_id')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
        ->where('sc.student_id', $student_id)
        ->where('sc.session_id', $session_id)
        ->where('sc.status', 1)
        ->get()
        ->getRow();
    
    return (int)($result->class_id ?? 0);
}

public function getStudentCardAjax()
{
    $student_id = $this->request->getPost('student_id');
    $campus_id = session('member_campusid');
    $session_id = session('member_sessionid');
    
    if (!$student_id) {
        return $this->response->setJSON(['success' => false, 'html' => 'Student not selected']);
    }

    $student = $this->db->table('students')->where('student_id', $student_id)->get()->getRow();

    if (!$student || !$student->parent_id) {
        return $this->response->setJSON(['success' => false, 'html' => 'Student or parent not found']);
    }

    $siblings = $this->db->table('students')
        ->where('parent_id', $student->parent_id)
        ->where('campus_id', $campus_id)
        ->where('status', 1)
        ->get()
        ->getResult();

    if (!$siblings) {
        return $this->response->setJSON(['success' => false, 'html' => 'No siblings found']);
    }

    $parent = $this->db->table('parents')->where('parent_id', $student->parent_id)->get()->getRow();

   $studentDetails = [];
$familyTotalDue = 0;
$totalStudentMonthlyFee = 0;

foreach ($siblings as $sibling) {
    // dues
    $studentDue = $this->calculateStudentTotalDue($sibling->student_id, $session_id, $campus_id);
    $familyTotalDue += $studentDue;

    // current student monthly fee (your existing logic)
    $studentMonthlyFee = $this->getStudentMonthlyFee($sibling->student_id, $session_id, $campus_id);
    $totalStudentMonthlyFee += $studentMonthlyFee;

    // ------- Class & Section (for current session) -------
    $cs = $this->db->table('student_class sc')
        ->select('cs.class_id, c.class_name, s.section_name')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id', 'left')
        ->where('sc.student_id', $sibling->student_id)
        ->where('sc.session_id', $session_id)
        ->get()->getRow();

    $class_id     = (int)($cs->class_id ?? 0);
    $class_name   = $cs->class_name ?? null;
    $section_name = $cs->section_name ?? null;

    // ------- Class Fee (base class monthly fee) -------
    // Prefer your own helper if it exists:
    $classFee = 0.0;
    if (method_exists($this, 'getClassMonthlyFee')) {
        $classFee = (float)$this->getClassMonthlyFee($class_id, $campus_id, $session_id);
    } else {
        // Fallback example: fee_amount joined with fee_type.is_monthly_fee = 1
        $rowFee = $this->db->table('fee_amount fa')
            ->select('fa.amount')
            ->join('fee_type ft', 'ft.fee_type_id = fa.fee_type_id')
            ->where([
                'fa.class_id' => $class_id,
                'fa.campus_id' => $campus_id,
                'ft.is_monthly_fee' => 1
            ])->get()->getRow();
        $classFee = (float)($rowFee->amount ?? 0);
    }

    $studentDetails[] = [
        'student_id'   => (int)$sibling->student_id,
        'student_name' => trim(($sibling->first_name ?? '') . ' ' . ($sibling->last_name ?? '')),
        'class_id'     => $class_id,
        'class_name'   => $class_name,
        'section_name' => $section_name,
        'class_fee'    => $classFee,          // <- NEW for modal
        'monthly_fee'  => $studentMonthlyFee, // current per-student fee (editable baseline)
        'due_amount'   => $studentDue,
    ];
}

    // Compact header block with tooltips
    $parentHeader = '<div class="parent-header bg-light p-2 mb-2 rounded">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h5 class="mb-1" data-bs-toggle="tooltip" title="Family head"><i class="fas fa-user-friends text-primary"></i> ' . esc($parent->f_name ?? 'N/A') . '</h5>
                                <div class="d-flex">
                                 <span class="badge text-bg-info" data-bs-toggle="tooltip" title="Monthly family fee">
                                        <i class="fas fa-calendar-alt"></i> Rs ' . number_format($totalStudentMonthlyFee, 0) . '
                                    </span>
                                    <span class="badge text-bg-danger me-2" data-bs-toggle="tooltip" title="Total family dues">
                                        <i class="fas fa-exclamation-circle"></i> Rs ' . number_format($familyTotalDue, 0) . '
                                    </span>
                                   
                                </div>
                            </div>
            
                            <div class="mt-2 mt-md-0">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="multiCurrencyFeeModal(' . $student->student_id . ')" 
                                        data-bs-toggle="tooltip" title="Multi Currency">
                                        <i class="fas fa-money"></i> Multi Currency
                                    </button>

                                    <div class="form-check form-switch d-inline-block ms-2" data-bs-toggle="tooltip" title="Toggle partial payment mode">
                                        <input type="checkbox" class="form-check-input" id="partialToggle">
                                        <label class="form-check-label" for="partialToggle"></label>
                                    </div>
                                    <button class="btn btn-outline-primary" onclick="showEditStudentFeeModal(' . $student->student_id . ')" 
                                        data-bs-toggle="tooltip" title="Edit monthly fees">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info" onclick="showAdvanceFeeStudentModal(' . $student->student_id . ')" 
                                        data-bs-toggle="tooltip" title="Pay advance fees">
                                        <i class="fas fa-forward"></i>
                                    </button>
                                    <button class="btn btn-primary btn-icon-only" onclick="addFamilyUnpaidFeesToPool(' . $student->parent_id . ')" 
                                        data-bs-toggle="tooltip" title="Add all family fees to cart">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>

                                  <button type="button" class="btn btn-warning btn-icon-only"
                        onclick="showFamilyFeeHistoryPage(' . (int)$student->parent_id . ')"
                        data-bs-toggle="tooltip" title="Family payment history">
                  <i class="fas fa-file-invoice-dollar"></i>
                </button>
                                    <button type="button" class="btn btn-outline-secondary btn-icon-only"
                        onclick="openChalanEditForPay(' . (int)$student->parent_id . ', 0)"
                        data-bs-toggle="tooltip" title="Edit challan / add fee lines">
                  <i class="fas fa-plus-circle"></i>
                </button>
                </div>
                
               
            </div>
        </div>
    </div>';

    // Student cards
    $html = '';
    foreach ($siblings as $sibling) {
        $html .= $this->generateStudentFeeCard($sibling, $campus_id, $session_id);
    }

    return $this->response->setJSON([
        'success' => true,
        'html' => $parentHeader . $html,
        'parent_id' => $student->parent_id,
        'parent_name' => $parent->f_name ?? 'N/A',
        'family_total' => number_format($familyTotalDue, 0),
        'student_fee_total' => number_format($totalStudentMonthlyFee, 0),
        'student_details' => $studentDetails
    ]);
}



protected function generateStudentFeeCard($student, $campus_info, $session_id)
{
    // Helper: Generate hash-based bootstrap color class
    $pickColorByName = function($name) {
        $colors = [
            'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'
        ];

        // Normalize name
        $normalizedName = strtolower(trim($name));

        // Stable hash value
        $hash = crc32($normalizedName);

        // Pick index from available colors
        return $colors[$hash % count($colors)];
    };

    // Helper: Pick icon based on name keywords
    $pickIconByName = function($name) {
        $nameLower = strtolower($name);
        if (strpos($nameLower, 'tuition') !== false) return 'fas fa-school';
        if (strpos($nameLower, 'annual') !== false) return 'fas fa-bus';
        if (strpos($nameLower, 'admission') !== false) return 'fas fa-door-open';
        if (strpos($nameLower, 'stationary') !== false) return 'fas fa-clock';
        return 'fas fa-money-bill';
    };

    // Get class info
   $class_info = $this->db->table('student_class sc')
    ->select('c.class_name, c.class_short_name, s.section_name')
    ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
    ->join('classes c', 'c.class_id = cs.class_id')
    ->join('sections s', 's.section_id = cs.section_id')
    ->where('sc.student_id', $student->student_id)
    ->where('sc.session_id', $session_id)
    ->get()
    ->getRow();

    $buildClassSection = function($ci) {
    if (!$ci) return '';
    $class = $ci->class_short_name ?: $ci->class_name ?: '';
    $sec   = $ci->section_name ?: '';
    $label = trim($class);
    if ($label && $sec) $label .= '-' . $sec;
    return $label; // e.g., "Grade 1-A"
};

$classSectionLabel = $buildClassSection($class_info);

// Build the display name once
$student_display_name = trim($student->first_name . ' ' . ($student->last_name ?? ''));
if ($classSectionLabel !== '') {
    $student_display_name .= ' (' . $classSectionLabel . ')';
}

    // Monthly fee
    $monthlyFee = $this->getStudentMonthlyFee($student->student_id, $session_id, $campus_info);

    // Unpaid fees
  $fee_chalan = $this->db->table('fee_chalan fc')
    ->select('fc.*, ft.fee_type_name, CONCAT(s.first_name, " ", COALESCE(s.last_name,"")) AS student_name')
    ->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id', 'left')
    ->join('students s', 's.student_id = fc.student_id')
    ->where('fc.student_id', $student->student_id)
    ->where('fc.status', 'unpaid')
    ->where('fc.fee_type_id !=', advance_fee_type_id())
    ->orderBy('fc.due_date', 'ASC')
    ->get()
    ->getResult();

    $student_total = 0;
    $fee_buttons = '';

    foreach ($fee_chalan as $row) {
        $amount = $row->amount;
        $discount = $row->discount;
        $net_amount = $amount - $discount;

        if ($net_amount <= 0) continue;

        $student_total += $net_amount;

        $feeMonth = $this->formatFeeMonth($row->fee_month);
        $due_date = date('d M Y', strtotime($row->due_date));

        // Dynamic color and icon
        $btnColor = $pickColorByName($row->fee_type_name);
        $icon = $pickIconByName($row->fee_type_name);

        $fee_buttons .= '
       <div id="fee-row-' . esc($row->chalan_id) . '" class="fee-button-wrapper">
    <button type="button" class="btn btn-outline-' . $btnColor . ' btn-sm m-1 fee-button"
        id="fee-btn-' . esc($row->chalan_id) . '"
        onclick="paySingleFee(this)"
        data-fee-id="' . esc($row->chalan_id) . '"
        data-student="' . esc($student->student_id) . '"
        data-student-name="' . htmlspecialchars($row->student_name) . '"
        data-amount="' . esc($amount) . '"
        data-discount="' . esc($discount) . '"
        data-feetype="' . esc($row->fee_type_name) . '"
        data-feemonth="' . esc($feeMonth) . '"
        title="Due on ' . esc($due_date) . '">
        <div class="text-center">
            <i class="' . $icon . ' mb-1"></i><br>
            <strong class="d-block">' . esc($row->fee_type_name) . '</strong>
            <small class="text-muted d-block">' . esc($feeMonth) . '</small>
        </div>
        <span class="badge text-bg-light">Rs ' . number_format($net_amount, 0) . '</span>
    </button>
</div>';

    }

    $profile_photo = $this->getProfilePhotoHTML($student->profile_photo);

  // ----- Build "Class-Section" label and student display name -----
if (!isset($class_info) || !$class_info) {
    $class_info = $this->db->table('student_class sc')
        ->select('c.class_name, c.class_short_name, s.section_name')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id')
        ->where('sc.student_id', $student->student_id)
        ->where('sc.session_id', $session_id)
        ->get()
        ->getRow();
}

$buildClassSection = function($ci) {
    if (!$ci) return '';
    $class = $ci->class_short_name ?: $ci->class_name ?: '';
    $sec   = $ci->section_name ?: '';
    // If class is purely a number, prefix with "Grade "
    if ($class !== '' && preg_match('/^\d+$/', $class)) {
        $class = 'Grade ' . $class;
    }
    return $class && $sec ? ($class . '-' . $sec) : $class;
};

$classSectionLabel   = $buildClassSection($class_info);
$student_display_name = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
if ($classSectionLabel !== '') {
    $student_display_name .= ' (' . $classSectionLabel . ')';
}
// ---------------------------------------------------------------


// ----- Your existing return with the updated <h5> -----
return '
    <div class="student-card mb-4 card shadow-sm border-0"
         data-student-id="' . esc($student->student_id) . '"
         data-parent-id="' . esc($student->parent_id) . '">

        <!-- Header -->
        <div class="card-header bg-light border-bottom d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="me-3">' . $profile_photo . '</div>
                <div>
                    <h5 class="mb-0">' . esc($student_display_name) . '</h5>
                    <div class="d-inline-flex align-items-center mt-1">
                        <!-- Monthly Fee -->
                        <span class="badge text-bg-info me-2"
                              data-bs-toggle="tooltip" data-bs-placement="top"
                              title="Monthly Student Fee: Rs ' . number_format($monthlyFee, 0) . '">
                            <i class="fas fa-calendar-alt"></i>
                            Rs ' . number_format($monthlyFee, 0) . '
                        </span>

                        <!-- Total Dues -->
                        <span class="badge text-bg-danger me-2"
                              data-bs-toggle="tooltip" data-bs-placement="top"
                              title="Total student dues">
                            <i class="fas fa-exclamation-circle"></i>
                            <span id="student-dues-' . esc($student->student_id) . '">' . number_format($student_total, 0) . '</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="ms-auto mt-2 mt-md-0 d-flex justify-content-end">
                <div class="btn-group btn-group-sm">
                    <!-- Icon-only: Move to Cart -->
                    <button type="button"
                            class="btn btn-primary btn-icon-only"
                            onclick="addAllUnpaidFeesToPool(this)"
                            data-student-id="' . esc($student->student_id) . '"
                            data-bs-toggle="tooltip" data-bs-placement="bottom"
                            title="Move all unpaid fees to cart" aria-label="Move to cart">
                        <i class="fas fa-cart-plus"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="card-body">
            ' . ($fee_buttons ? '
            <div class="fee-buttons d-flex flex-wrap justify-content-center" id="fee-buttons-' . esc($student->student_id) . '">
                ' . $fee_buttons . '
            </div>
            <div class="fee-hidden d-none" id="fee-hidden-' . esc($student->student_id) . '"></div>
            ' : '<div class="text-center text-success py-3">No unpaid fees</div>') . '
        </div>
    </div>';


}




protected function calculateStudentTotalDue($student_id, $session_id, $campus_id)
{
    // Get all unpaid fees for student
    $unpaidFees = $this->db->table('fee_chalan fc')
        ->select('fc.amount, fc.discount')
        ->where('fc.student_id', $student_id)
        ->where('fc.status', 'unpaid')
        ->where('fc.fee_type_id !=', advance_fee_type_id())
        ->get()
        ->getResult();

    $totalDue = 0;
    foreach ($unpaidFees as $fee) {
        $amount = $fee->amount - $fee->discount;
        if ($amount > 0) {
            $totalDue += $amount;
        }
    }

    return $totalDue;
}


public function updateStudentDiscount()
{
    $fees = $this->request->getPost('fees');
    $session_id = session('member_sessionid');
    $campus_id = session('member_campusid');

    if (!is_array($fees) || empty($fees)) {
        return $this->response->setJSON(['success' => false, 'message' => 'No data submitted.']);
    }

    $db = \Config\Database::connect();

    foreach ($fees as $student_id => $entered_fee) {
        // Get class fee
        $builder = $db->query("
            SELECT fa.amount 
            FROM fee_amount fa
            JOIN fee_type ft ON fa.fee_type_id = ft.fee_type_id
            WHERE ft.system_id = (
                SELECT system_id FROM students WHERE student_id = ?
            )
            AND ft.is_monthly_fee = 1
            AND fa.class_id = (
                SELECT class_id FROM class_section WHERE cls_sec_id = (
                    SELECT cls_sec_id FROM student_class WHERE student_id = ? AND session_id = ?
                )
            )
            AND fa.session_id = ? AND fa.campus_id = ?
            LIMIT 1
        ", [$student_id, $student_id, $session_id, $session_id, $campus_id]);

        $row = $builder->getRow();

        if ($row && isset($row->amount)) {
            $class_fee = floatval($row->amount);
            $discount = $class_fee - floatval($entered_fee);

            // Update discounted amount
            $db->table('students')
                ->where('student_id', $student_id)
                ->update(['discounted_amount' => $discount]);
        }
    }

    return $this->response->setJSON(['success' => true]);
}



// protected function getStudentMonthlyFee($student_id, $session_id, $campus_id)
// {
//     // Get student discount
//     $discount = $this->db->table('students')
//         ->select('discounted_amount')
//         ->where('student_id', $student_id)
//         ->get()
//         ->getRow();
    
//     $discountAmount = $discount->discounted_amount ?? 0;

//     // Get class fee amount
//     $feeAmount = $this->db->table('fee_amount fa')
//         ->select('fa.amount')
//         ->join('fee_type ft', 'ft.fee_type_id = fa.fee_type_id')
//         ->where('ft.system_id', 1)
//         ->where('ft.is_monthly_fee', 1)
//         ->where('fa.class_id', function($query) use ($student_id, $session_id) {
//             $query->select('cs.class_id')
//                 ->from('student_class sc')
//                 ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
//                 ->where('sc.student_id', $student_id)
//                 ->where('sc.session_id', $session_id);
//         })
//         ->where('fa.session_id', $session_id)
//         ->where('fa.campus_id', $campus_id)
//         ->get()
//         ->getRow();

//     $classFee = $feeAmount->amount ?? 0;

//     // Calculate student's monthly fee (class fee - discount)
//     return max(0, $classFee - $discountAmount);
// }



/**
 * Select2 student search (fee pay). Alias for route definitions using camelCase.
 */
public function getStudentinfo()
{
    return $this->get_studentinfo();
}

public function get_studentinfo()
{
    $termRaw = $this->request->getPost('term') ?? $this->request->getPost('q');
    if (is_array($termRaw)) {
        $search_term = trim((string) ($termRaw['term'] ?? $termRaw['q'] ?? ''));
    } else {
        $search_term = trim((string) $termRaw);
    }

    $cls_sec_id  = $this->request->getPost('flag');
    $campusid    = (int) session('member_campusid');
    $session_id  = (int) session('member_sessionid');

    if ($search_term !== '' && strlen($search_term) < 2) {
        return $this->response->setJSON([]);
    }

    if ($campusid <= 0) {
        return $this->response->setJSON([]);
    }

    try {
        $builder = $this->db->table('students s')
            ->select('
                s.student_id,
                s.reg_no,
                s.first_name,
                s.last_name,
                CONCAT(s.first_name, " ", COALESCE(s.last_name, "")) AS student_name,
                p.f_name AS father_name,
                TRIM(CONCAT(
                    COALESCE(c.class_short_name, c.class_name, ""),
                    " ",
                    COALESCE(sec.short_name, sec.section_name, "")
                )) AS section_name
            ', false)
            ->join('parents p', 'p.parent_id = s.parent_id', 'left')
            ->join('student_class sc', 'sc.student_id = s.student_id AND sc.status = 1', 'inner')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
            ->where('s.status', 1)
            ->where('s.campus_id', $campusid);

        $currentRole = currentUserRoles();
        if (in_array(5, $currentRole, true)) {
            $teacherSections = teacherSubjectSections();
            $clsSecIds = [];
            foreach ($teacherSections as $sec) {
                if (! empty($sec['cls_sec_id'])) {
                    $clsSecIds[] = (int) $sec['cls_sec_id'];
                }
            }
            if ($clsSecIds === []) {
                return $this->response->setJSON([]);
            }
            $builder->whereIn('sc.cls_sec_id', $clsSecIds);
        }

        if ($search_term !== '') {
            $builder->groupStart()
                ->like('s.first_name', $search_term)
                ->orLike('s.last_name', $search_term)
                ->orLike('s.reg_no', $search_term)
                ->orLike('p.f_name', $search_term)
                ->orLike("CONCAT(s.first_name, ' ', COALESCE(s.last_name, ''))", $search_term)
                ->groupEnd();
        }

        if ($cls_sec_id && is_numeric($cls_sec_id)) {
            $builder->where('sc.cls_sec_id', (int) $cls_sec_id);
        }

        if ($session_id > 0) {
            $builder->orderBy(
                'CASE WHEN sc.session_id = ' . (int) $session_id . ' THEN 0 ELSE 1 END',
                'ASC',
                false
            );
        }
        $builder->orderBy('s.first_name', 'ASC');

        $rows = $builder->groupBy('s.student_id')->limit(40)->get()->getResultArray();

        if ($search_term !== '') {
            $rows = (new \App\Libraries\FeeChalanSearchService())->rankResults($rows, $search_term);
            $rows = array_slice($rows, 0, 25);
        }

        $data = array_map(static function ($row) {
            $studentName = trim($row['student_name'] ?? '');
            $fatherName  = trim($row['father_name'] ?? '');
            $sectionName = trim($row['section_name'] ?? '');
            $text        = $studentName;
            if ($fatherName !== '') {
                $text .= ' c/o ' . $fatherName;
            }
            if ($sectionName !== '') {
                $text .= ' ' . $sectionName;
            }

            return [
                'id'   => (int) ($row['student_id'] ?? 0),
                'text' => $text,
            ];
        }, $rows);

        return $this->response->setJSON($data);
    } catch (\Throwable $e) {
        log_message('error', 'FeeChalanPay::get_studentinfo failed: ' . $e->getMessage());

        return $this->response->setJSON([]);
    }
}

public function getParentFeeSummary()
{
    $parent_id = $this->request->getPost('parent_id');
    $today = date('Y-m-d');
    $month_start = date('Y-m-01');

    $db = \Config\Database::connect();

    // Get all students of the parent
    $students = $db->table('students')
        ->where('parent_id', $parent_id)
        ->where('status', 1)
        ->get()
        ->getResult();

    $student_ids = array_column($students, 'student_id');
    $student_count = count($students);

    if (empty($student_ids)) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'No active students found for this parent.',
            'last_payments' => [],
        ]);
    }

    // Today's payment: SUM(amount - discount)
   $todayPaid = $db->table('fee_chalan')
    ->select('SUM(amount - discount) AS total_paid')
    ->whereIn('student_id', $student_ids)
    ->where('DATE(updated_date)', date('Y-m-d'))
    ->whereIn('status', ['paid', 'discounted'])
    ->get()
    ->getRow()->total_paid ?? 0;

    // This Month's payment: SUM(amount - discount)
    $monthPaid = $db->table('fee_chalan')
        ->select('SUM(amount - discount) AS total_paid')
        ->whereIn('student_id', $student_ids)
        ->where('updated_date >=', $month_start)
        ->whereIn('status', ['paid', 'discounted'])
        ->get()
        ->getRow()->total_paid ?? 0;

    // Total family dues (still unpaid): SUM(amount - discount)
    $familyDue = $db->table('fee_chalan')
        ->select('SUM(amount - discount) AS total_due')
        ->whereIn('student_id', $student_ids)
        ->where('status', 'unpaid')
        ->get()
        ->getRow()->total_due ?? 0;

    $parentName = $students[0]->father_name ?? 'Unknown';

    $lastPayments = $db->table('fee_chalan fc')
        ->select('DATE(COALESCE(fc.paid_date, fc.updated_date)) AS payment_date, SUM(fc.amount - fc.discount) AS total_received', false)
        ->whereIn('fc.student_id', $student_ids)
        ->where('fc.status', 'paid')
        ->groupBy('DATE(COALESCE(fc.paid_date, fc.updated_date))', false)
        ->orderBy('payment_date', 'DESC')
        ->limit(3)
        ->get()
        ->getResult();

    $lastPaymentsFormatted = [];
    foreach ($lastPayments as $payment) {
        $paymentDate = $payment->payment_date ?? '';
        $lastPaymentsFormatted[] = [
            'payment_date' => $paymentDate,
            'payment_date_label' => $paymentDate !== ''
                ? date('d M Y', strtotime($paymentDate))
                : 'Date not recorded',
            'total_received' => floatval($payment->total_received ?? 0),
        ];
    }

    return $this->response->setJSON([
        'success' => true,
        'totalToday' => floatval($todayPaid),
        'totalMonth' => floatval($monthPaid),
        'familyTotalDue' => floatval($familyDue),
        'parent_name' => $parentName,
        'student_count' => $student_count,
        'today_label' => date('l, d M Y'),
        'month_label' => date('F Y'),
        'last_payments' => $lastPaymentsFormatted,
    ]);
}


public function getMonthlyPaidFees()
{
    $parent_id = $this->request->getPost('parent_id');
    $month_start = date('Y-m-01');
    $today = date('Y-m-d');

    // Step 1: Get all student IDs for this parent
    $studentRows = $this->db->table('students')
        ->select('student_id')
        ->where('parent_id', $parent_id)
        ->get()
        ->getResult();

    $student_ids = array_column($studentRows, 'student_id');

    if (empty($student_ids)) {
        return $this->response->setJSON([
            'success' => true,
            'data' => [],
            'today' => $today
        ]);
    }

    // Paid + discount adjustments this month (discount-only rows included for undo)
    $paidFees = $this->db->table('fee_chalan fc')
        ->select('fc.*, s.first_name, s.last_name, s.parent_id, ft.fee_type_name')
        ->join('students s', 's.student_id = fc.student_id')
        ->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id')
        ->whereIn('fc.student_id', $student_ids)
        ->where('fc.updated_date >=', $month_start)
        ->groupStart()
            ->whereIn('fc.status', ['paid', 'discounted'])
            ->orGroupStart()
                ->where('fc.status', 'unpaid')
                ->where('fc.discount >', 0)
                ->where('DATE(fc.updated_date)', $today)
            ->groupEnd()
        ->groupEnd()
        ->orderBy('fc.updated_date', 'DESC')
        ->get()
        ->getResult();

    $rows = [];
    foreach ($paidFees as $fee) {
        $rows[] = $this->enrichFeeHistoryRow($fee, $today);
    }

    return $this->response->setJSON([
        'success' => true,
        'data'    => $rows,
        'today'   => $today,
    ]);
}


public function makeUnpaid()
{
    $chalan_id = (int) $this->request->getPost('chalan_id');
    $today     = date('Y-m-d');
    $reverseDiscount = in_array(
        $this->request->getPost('reverse_discount'),
        ['1', 1, true, 'true'],
        true
    );

    if ($chalan_id <= 0) {
        return $this->response->setJSON(['success' => false, 'message' => 'Missing chalan ID']);
    }

    $db      = \Config\Database::connect();
    $chalan  = $db->table('fee_chalan')->where('chalan_id', $chalan_id)->get()->getRow();

    if (! $chalan) {
        return $this->response->setJSON(['success' => false, 'message' => 'Fee record not found']);
    }

    $updatedDate = $chalan->updated_date ? date('Y-m-d', strtotime((string) $chalan->updated_date)) : null;
    if ($updatedDate !== $today) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Only records updated today can be reversed',
        ]);
    }

    $user_id   = (int) session('member_userid');
    $campus_id = (int) session('member_campusid');
    $status    = strtolower((string) ($chalan->status ?? ''));

    $db->transStart();

    try {
        if ($status === 'discounted') {
            $this->reverseDiscountedChalan($chalan);
            $db->transComplete();

            return $this->response->setJSON([
                'success' => $db->transStatus() !== false,
                'message' => $db->transStatus() !== false ? 'Discount reversed' : 'Failed to reverse discount',
            ]);
        }

        $this->mergeSplitChalans($chalan_id, $reverseDiscount);
        $chalan = $db->table('fee_chalan')->where('chalan_id', $chalan_id)->get()->getRow();

        if (! $chalan) {
            $db->transComplete();

            return $this->response->setJSON(['success' => false, 'message' => 'Fee record not found after merge']);
        }

        $finance = new CampusFinanceService($db);
        if ($finance->campusHasFinanceAccounts($campus_id) && ! empty($chalan->finance_transaction_id)) {
            $rev = $finance->reverseFeeReceipt($chalan_id, $campus_id, $user_id);

            if (($rev['success'] ?? false) && $reverseDiscount) {
                $this->db->table('fee_chalan')->where('chalan_id', $chalan_id)->update([
                    'discount'     => 0,
                    'updated_date' => date('Y-m-d H:i:s'),
                ]);
            }

            $db->transComplete();

            return $this->response->setJSON($rev);
        }

        $update = [
            'status'       => 'unpaid',
            'paid_date'    => null,
            'user_id'      => $user_id,
            'updated_date' => date('Y-m-d H:i:s'),
        ];

        if ($reverseDiscount && (float) ($chalan->discount ?? 0) > 0) {
            $update['discount'] = 0;
        }

        $db->table('fee_chalan')->where('chalan_id', $chalan_id)->update($update);
        $db->transComplete();

        return $this->response->setJSON([
            'success' => $db->transStatus() !== false,
            'message' => $db->transStatus() !== false
                ? ($reverseDiscount ? 'Discount reversed' : 'Marked unpaid')
                : 'Failed to update fee',
        ]);
    } catch (\Throwable $e) {
        $db->transComplete();

        log_message('error', 'makeUnpaid failed: ' . $e->getMessage());

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Could not reverse fee: ' . $e->getMessage(),
        ]);
    }
}

/**
 * Classify a fee row for the payment history panel (payment vs discount).
 */
protected function enrichFeeHistoryRow(object $fee, string $today): array
{
    $amount   = (float) ($fee->amount ?? 0);
    $discount = (float) ($fee->discount ?? 0);
    $net      = $amount - $discount;
    $status   = strtolower((string) ($fee->status ?? ''));
    $updated  = $fee->updated_date ? date('Y-m-d', strtotime((string) $fee->updated_date)) : null;

    $entryType = 'payment';
    if ($status === 'discounted') {
        $entryType = 'discount';
    } elseif ($status === 'unpaid' && $discount > 0 && $updated === $today) {
        $entryType = 'discount_pending';
    } elseif ($discount > 0 && $net <= 0) {
        $entryType = 'discount';
    } elseif ($discount > 0 && $net < $amount) {
        $entryType = 'payment_with_discount';
    }

    return [
        'chalan_id'          => (int) ($fee->chalan_id ?? 0),
        'student_id'         => (int) ($fee->student_id ?? 0),
        'parent_id'          => (int) ($fee->parent_id ?? 0),
        'first_name'         => (string) ($fee->first_name ?? ''),
        'last_name'          => (string) ($fee->last_name ?? ''),
        'fee_type_name'      => (string) ($fee->fee_type_name ?? ''),
        'fee_month'          => (string) ($fee->fee_month ?? ''),
        'amount'             => $amount,
        'discount'           => $discount,
        'net_amount'         => $net,
        'status'             => $status,
        'paid_date'          => $fee->paid_date ?? null,
        'updated_date'       => $fee->updated_date ?? null,
        'entry_type'         => $entryType,
        'can_reverse_today'  => $updated === $today,
    ];
}

/**
 * Merge fee_chalan rows split by a partial payment/discount (addPartialFeeToPool).
 */
protected function mergeSplitChalans(int $chalanId, bool $clearAppliedDiscount = false): void
{
    $chalan = $this->db->table('fee_chalan')->where('chalan_id', $chalanId)->get()->getRow();
    if (! $chalan) {
        return;
    }

    $siblingQuery = $this->db->table('fee_chalan')
        ->where('student_id', $chalan->student_id)
        ->where('fee_type_id', $chalan->fee_type_id)
        ->where('fee_month', $chalan->fee_month)
        ->where('status', 'unpaid')
        ->where('chalan_id !=', $chalanId);

    if (! empty($chalan->invoice_no)) {
        $siblingQuery->where('invoice_no', $chalan->invoice_no);
    } elseif (! empty($chalan->issue_date) && $chalan->issue_date !== '0000-00-00') {
        $siblingQuery->where('issue_date', $chalan->issue_date);
    }

    $siblings = $siblingQuery->get()->getResult();

    if ($siblings === []) {
        if ($clearAppliedDiscount) {
            $this->db->table('fee_chalan')->where('chalan_id', $chalanId)->update([
                'discount'     => 0,
                'updated_date' => date('Y-m-d H:i:s'),
            ]);
        }

        return;
    }

    $totalAmount    = (float) $chalan->amount;
    $restoredDiscount = 0.0;

    foreach ($siblings as $sibling) {
        $totalAmount += (float) $sibling->amount;
        if ((float) $sibling->discount > 0) {
            $restoredDiscount = (float) $sibling->discount;
        }
        $this->db->table('fee_chalan')->where('chalan_id', $sibling->chalan_id)->delete();
    }

    if ($clearAppliedDiscount) {
        $newDiscount = $restoredDiscount;
    } else {
        $newDiscount = max((float) $chalan->discount, $restoredDiscount);
    }

    $this->db->table('fee_chalan')->where('chalan_id', $chalanId)->update([
        'amount'       => round($totalAmount, 2),
        'discount'     => round($newDiscount, 2),
        'updated_date' => date('Y-m-d H:i:s'),
    ]);
}

/**
 * Reverse a standalone discounted fee row (legacy pay_fee discount insert).
 */
protected function reverseDiscountedChalan(object $discounted): void
{
    $chalanId   = (int) ($discounted->chalan_id ?? 0);
    $amount     = (float) ($discounted->amount ?? 0);
    $studentId  = (int) ($discounted->student_id ?? 0);
    $feeTypeId  = (int) ($discounted->fee_type_id ?? 0);
    $feeMonth   = (string) ($discounted->fee_month ?? '');

    $sibling = $this->db->table('fee_chalan')
        ->where('student_id', $studentId)
        ->where('fee_type_id', $feeTypeId)
        ->where('fee_month', $feeMonth)
        ->where('status', 'unpaid')
        ->where('chalan_id !=', $chalanId)
        ->orderBy('chalan_id', 'ASC')
        ->get()
        ->getRow();

    if ($sibling) {
        $this->db->table('fee_chalan')->where('chalan_id', $sibling->chalan_id)->update([
            'amount'       => round((float) $sibling->amount + $amount, 2),
            'updated_date' => date('Y-m-d H:i:s'),
        ]);
        $this->db->table('fee_chalan')->where('chalan_id', $chalanId)->delete();

        return;
    }

    $this->db->table('fee_chalan')->where('chalan_id', $chalanId)->update([
        'status'       => 'unpaid',
        'paid_date'    => null,
        'discount'     => 0,
        'updated_date' => date('Y-m-d H:i:s'),
    ]);
}


    public function getUnpaidFees()

    {
        $student_id = $this->request->getPost('student_id');
        if (!$student_id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Student ID missing']);
        }

       $fees = $this->db->table('fee_chalan fc')
    ->select('
        fc.chalan_id,
        fc.student_id,
        CONCAT(s.first_name, " ", COALESCE(s.last_name, "")) AS student_name,
        fc.amount,
        fc.discount,
        (fc.amount - fc.discount) as net_amount,
        fc.fee_month,
        ft.fee_type_name
    ')
    ->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id')
    ->join('students s', 's.student_id = fc.student_id') // 👈 add this
    ->where('fc.student_id', $student_id)
    ->where('fc.status', 'unpaid')
    ->where('(fc.amount - fc.discount) >', 0)
    ->orderBy('fc.due_date', 'ASC')
    ->get()
    ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'fees' => $fees
        ]);
    }

    
    public function getFamilyUnpaidFees()
{
    $parent_id = $this->request->getPost('parent_id');
    $campusId = session('member_campusid');

    if (!$parent_id || !$campusId) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Missing required parameters'
        ]);
    }

    $fees = $this->db->table('fee_chalan fc')
        ->select('
            fc.chalan_id,
            fc.student_id,
            fc.amount,
            fc.discount,
            (fc.amount - fc.discount) AS net_amount,
            fc.fee_month,
            ft.fee_type_name,
            CONCAT(s.first_name, " ", COALESCE(s.last_name, "")) AS student_name
        ')
        ->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id')
        ->join('students s', 's.student_id = fc.student_id')
        ->where('s.parent_id', $parent_id)
        ->where('s.campus_id', $campusId)
        ->where('fc.status', 'unpaid')
        ->where('(fc.amount - fc.discount) >', 0)
        ->orderBy('fc.due_date', 'ASC')
        ->get()
        ->getResultArray();

    return $this->response->setJSON([
        'success' => true,
        'fees' => $fees
    ]);
}

 
    public function getFamilyFeeHistory()
{
    $parent_id = (int) $this->request->getPost('parent_id');
    $start     = $this->request->getPost('start_date'); // optional: 'YYYY-MM-DD'
    $end       = $this->request->getPost('end_date');   // optional: 'YYYY-MM-DD'
    $limit     = (int) ($this->request->getPost('limit') ?? 0); // optional: 0 = all
    $page      = (int) ($this->request->getPost('page') ?? 1);   // optional
    $campusId  = (int) (session('member_campusid') ?? 0);

    if (!$parent_id) {
        return $this->response->setJSON([
            'success' => false,
            'html'    => '<div class="alert alert-danger mb-0">Parent not specified.</div>',
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ]);
    }

    if (!$campusId) {
        return $this->response->setJSON([
            'success' => false,
            'html'    => '<div class="alert alert-danger mb-0">Campus not set in session.</div>',
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ]);
    }

    $builder = $this->db->table('fee_chalan fc')
        ->select([
            'fc.chalan_id',
            'fc.student_id',
            'fc.amount',
            'fc.discount',
            'fc.fee_month',
            'fc.paid_date',
            'fc.invoice_no',
            'ft.fee_type_name',
            'CONCAT(s.first_name, " ", COALESCE(s.last_name, "")) AS student_name'
        ])
        ->join('students s', 's.student_id = fc.student_id')
        ->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id', 'left')
        ->where('s.parent_id', $parent_id)
        ->where('s.campus_id', $campusId)
        ->where('fc.status', 'paid');

    // Optional date filter on paid_date
    if (!empty($start)) { $builder->where('fc.paid_date >=', $start); }
    if (!empty($end))   { $builder->where('fc.paid_date <=', $end);   }

    $builder->orderBy('fc.paid_date', 'DESC')
            ->orderBy('fc.chalan_id', 'DESC');

    // Pagination (optional)
    if ($limit > 0) {
        $offset = max(0, ($page - 1) * $limit);
        $builder->limit($limit, $offset);
    }

    $rows = $builder->get()->getResult();

    if (!$rows) {
        return $this->response->setJSON([
            'success' => true,
            'html'    => '<div class="alert alert-info mb-0">No paid fees found for this family.</div>',
            'family_total' => '0',
            'count'   => 0,
            'days_count' => 0,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ]);
    }

    // Group lines by calendar day (paid_date)
    $byDay = [];
    $family_total = 0.0;

    foreach ($rows as $r) {
        $amount   = (float) $r->amount;
        $discount = (float) $r->discount;
        $net      = $amount - $discount;
        $family_total += $net;

        $ts = ! empty($r->paid_date) ? strtotime((string) $r->paid_date) : false;
        $dayKey = $ts ? date('Y-m-d', $ts) : '_nodate';

        if (! isset($byDay[$dayKey])) {
            $byDay[$dayKey] = [
                'day_total' => 0.0,
                'lines'     => [],
            ];
        }
        $byDay[$dayKey]['day_total'] += $net;
        $byDay[$dayKey]['lines'][]    = [
            'r'   => $r,
            'net' => $net,
            'amount' => $amount,
            'discount' => $discount,
        ];
    }

    krsort($byDay, SORT_STRING);

    $blocks = '';
    foreach ($byDay as $dayKey => $bundle) {
        if ($dayKey === '_nodate') {
            $titleMain = 'Date not recorded';
            $titleSub  = '';
        } else {
            $ts        = strtotime($dayKey);
            $titleMain = date('j F Y', $ts);
            $titleSub  = date('l', $ts);
        }

        $dayTotal = $bundle['day_total'];
        $nLines   = count($bundle['lines']);

        $studentKeys = [];
        foreach ($bundle['lines'] as $item) {
            $r   = $item['r'];
            $sid = (int) ($r->student_id ?? 0);
            $studentKeys[$sid > 0 ? 's' . $sid : 'n' . md5((string) ($r->student_name ?? ''))] = true;
        }
        $nStudents = count($studentKeys);
        $dayMeta   = (int) $nLines . ' fee line' . ($nLines === 1 ? '' : 's')
            . ' · ' . $nStudents . ' student' . ($nStudents === 1 ? '' : 's');

        $dayLines = $bundle['lines'];
        usort($dayLines, static function ($a, $b) {
            $na = (string) ($a['r']->student_name ?? '');
            $nb = (string) ($b['r']->student_name ?? '');
            $c  = strcasecmp($na, $nb);
            if ($c !== 0) {
                return $c;
            }

            return ((int) ($a['r']->chalan_id ?? 0)) <=> ((int) ($b['r']->chalan_id ?? 0));
        });

        $linesHtml       = '';
        $prevStudentKey  = null;
        foreach ($dayLines as $item) {
            $r        = $item['r'];
            $net      = $item['net'];
            $amount   = $item['amount'];
            $discount = $item['discount'];

            $sid    = (int) ($r->student_id ?? 0);
            $skey   = $sid > 0 ? 's' . $sid : 'n' . md5((string) ($r->student_name ?? ''));
            $stuName = esc((string) ($r->student_name ?? 'Student'));

            $feeMonth = method_exists($this, 'formatFeeMonth')
                ? $this->formatFeeMonth($r->fee_month)
                : (string) $r->fee_month;

            $invShort = ! empty($r->invoice_no)
                ? esc($r->invoice_no)
                : '#' . esc($r->chalan_id);

            $paidAt = '';
            if (! empty($r->paid_date)) {
                $pt = strtotime((string) $r->paid_date);
                if ($pt && date('H:i:s', $pt) !== '00:00:00') {
                    $paidAt = '<span class="fph-fee-when">' . esc(date('g:i A', $pt)) . '</span>';
                }
            }

            $amtDetail = $discount > 0
                ? '<span class="fph-fee-amt-note">Gross ' . number_format($amount, 0) . ' · off ' . number_format($discount, 0) . '</span>'
                : '';

            if ($prevStudentKey !== null && $skey === $prevStudentKey) {
                $studentPrefix = '';
                $liExtraClass  = ' fph-fee-item-continue';
            } else {
                $studentPrefix = '<span class="fph-inline-student">' . $stuName . '</span>';
                $liExtraClass  = '';
                $prevStudentKey = $skey;
            }

            $linesHtml .= '
            <li class="fph-fee-item d-flex flex-wrap justify-content-between align-items-baseline' . $liExtraClass . '">
                <div class="fph-fee-item-left pe-2">
                    ' . $studentPrefix . '
                    <span class="fph-fee-type-pill">' . esc($r->fee_type_name ?? 'N/A') . '</span>
                    <span class="fph-fee-period text-muted">' . esc($feeMonth) . '</span>
                    <span class="fph-fee-inv text-muted">' . $invShort . '</span>
                    ' . ($paidAt !== '' ? $paidAt : '') . '
                    ' . ($amtDetail !== '' ? '<div class="fph-fee-amt-note-wrap">' . $amtDetail . '</div>' : '') . '
                </div>
                <div class="fph-fee-item-net text-success fw-bold text-nowrap">Rs ' . number_format($net, 0) . '</div>
            </li>';
        }

        $linesHtml = '<ul class="list-unstyled fph-day-fee-list mb-0">' . $linesHtml . '</ul>';

        $blocks .= '
        <div class="fph-day-card card mb-3 shadow-sm border-0">
            <div class="card-header fph-day-header d-flex flex-wrap justify-content-between align-items-center py-2">
                <div>
                    <div class="fph-day-title mb-0 fw-bold">' . esc($titleMain) . '</div>
                    ' . ($titleSub !== '' ? '<div class="fph-day-weekday small text-muted">' . esc($titleSub) . '</div>' : '') . '
                </div>
                <div class="text-end mt-2 mt-sm-0">
                    <span class="badge text-bg-success rounded-pill fph-day-total-pill">Day total: Rs ' . number_format($dayTotal, 0) . '</span>
                    <div class="small text-muted mt-1">' . esc($dayMeta) . '</div>
                </div>
            </div>
            <div class="card-body p-0 fph-day-body">
                ' . $linesHtml . '
            </div>
        </div>';
    }

    $daysCount = count($byDay);
    $html      = '
    <div class="family-payment-history">
        <div class="fph-summary alert alert-light border mb-3 py-2 px-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <span class="fw-bold text-dark">Overall in this view</span>
                    <div class="small text-muted">' . count($rows) . ' fee payment line(s) across ' . $daysCount . ' payment day(s)</div>
                </div>
                <div class="text-end mt-2 mt-md-0">
                    <div class="small text-muted">Combined net paid</div>
                    <div class="h5 mb-0 text-success fw-bold">Rs ' . number_format($family_total, 0) . '</div>
                </div>
            </div>
        </div>
        ' . $blocks . '
    </div>';

    return $this->response->setJSON([
        'success'       => true,
        'html'          => $html,
        'family_total'  => number_format($family_total, 0),
        'count'         => count($rows),
        'days_count'    => $daysCount,
        'csrfName'      => csrf_token(),
        'csrfHash'      => csrf_hash(),
    ]);
}



    public function markMultipleFeesAsPaid()
    {
        $student_id = $this->request->getPost('student_id');
        $campus_id = (int) session('member_campusid');
        $fees = json_decode($this->request->getPost('fees'), true);
        $paid_date = $this->request->getPost('paid_date');
        $user_id = (int) session('member_userid');
        $account_id = (int) $this->request->getPost('account_id');
        $student = $this->db->table('students')->where('student_id', $student_id)->get()->getRow();
        $session_id = session('member_sessionid');

if (!$student) {
    return $this->response->setJSON(['success' => false, 'message' => 'Student not found']);
}

        if (!$fees || !is_array($fees)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid fee data']);
        }

        $finance = new CampusFinanceService($this->db);

        if ($finance->campusHasFinanceAccounts($campus_id)) {
            $result = $finance->recordFeeReceipt(
                $campus_id,
                (int) $student_id,
                $fees,
                (string) $paid_date,
                $user_id,
                $account_id,
                $user_id
            );

            if (! ($result['success'] ?? false)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'] ?? 'Payment failed',
                ]);
            }

            $last_paid_chalan_id = $result['last_chalan_id'] ?? null;
        } else {
            $builder = $this->db->table('fee_chalan');
            $updateData = [
                'status' => 'paid',
                'updated_date' => date('Y-m-d H:i:s'),
                'user_id' => $user_id,
                'paid_date' => $paid_date,
            ];

            foreach ($fees as $fee) {
                $builder->where('chalan_id', $fee['chalan_id'])->update($updateData);
            }

            $last_paid_chalan_id = $this->db->table('fee_chalan')
                ->select('chalan_id')
                ->where('status', 'paid')
                ->orderBy('paid_date', 'DESC')
                ->limit(1)
                ->get()
                ->getRow('chalan_id');
        }

         $siblings = $this->db->table('students')
    ->where('parent_id', $student->parent_id)
    ->where('campus_id', $campus_id)
    ->where('status', 1)
    ->get()
    ->getResult();

$familyTotalDue = 0;
$allStudentDues = [];

foreach ($siblings as $sibling) {
    $studentDue = $this->calculateStudentTotalDue($sibling->student_id, $session_id, $campus_id);
    $familyTotalDue += $studentDue;

    $allStudentDues[] = [
        'student_id' => $sibling->student_id,
        'amount' => $studentDue
    ];
}


return $this->response->setJSON([
    'success' => true,
    'last_chalan_id' => $last_paid_chalan_id ?? null,
    'student_dues_all' => $allStudentDues,
    'family_dues' => [
        'amount' => $familyTotalDue
    ]
]);
}

 public function addPartialFeeToPool()
{
    $data = $this->request->getPost();

    $orig = $this->db->table('fee_chalan')
        ->where('chalan_id', $data['chalan_id'])
        ->get()
        ->getRow();

    if (!$orig) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Original fee not found'
        ]);
    }

    $paid = floatval($data['paid_amount']);
    $discountUsed = floatval($data['discount_amount']);
    $paidPortion = $paid + $discountUsed;
    $remainingAmount = $orig->amount - $paidPortion;

    // ✅ Update original fee_chalan as paid portion
    $this->db->table('fee_chalan')
        ->where('chalan_id', $data['chalan_id'])
        ->update([
            'amount' => $paidPortion,
            'discount' => $discountUsed,
            'status' => 'unpaid',
            
            'updated_date' => date('Y-m-d H:i:s'),
        ]);

    // ✅ Insert new unpaid record if balance exists
    if ($remainingAmount > 0) {
        $this->db->table('fee_chalan')->insert([
            'student_id' => $orig->student_id,
            'fee_type_id' => $orig->fee_type_id,
            'fee_month' => $orig->fee_month,
            'amount' => $remainingAmount,
            'discount' => $orig->discount, // 👈 carry forward full original discount
            'status' => 'unpaid',
            'due_date' => $orig->due_date,
            'created_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s'),
            'invoice_no' => $orig->invoice_no, // ✅ Include this line
            'issue_date' => $orig->issue_date, // ✅ Include this line
            'user_id' => $orig->user_id, // ✅ Include this line
        ]);
    }


    $student = $this->db->table('students')
    ->select('CONCAT(first_name, " ", COALESCE(last_name,"")) AS student_name')
    ->where('student_id', $data['student_id'])
    ->get()
    ->getRow();


    return $this->response->setJSON([
        'success' => true,
        'message' => 'Partial payment recorded',
        'fee' => [
            'chalan_id' => $data['chalan_id'],
            'student_id' => $data['student_id'],
             'student_name' => $student->student_name, // ✅ Add this
            'paid' => $paid,
            'discount' => $discountUsed,
            'feeType' => 'Partial',
            'feeMonth' => date('M', strtotime($orig->fee_month))
        ]
    ]);
}



    protected function formatFeeMonth($fee_month)
    {
        if (!empty($fee_month)) {
            $parts = explode('-', $fee_month);
            if (count($parts) === 2) {
                return date('M', mktime(0, 0, 0, $parts[1], 1)) . ' ' . substr($parts[0], -2);
            }
        }
        return '';
    }

    protected function getProfilePhotoHTML($profile_photo)
    {
        if ($profile_photo && file_exists(FCPATH."uploads/".$profile_photo)) {
            return '<img style="width:50px;height:50px;border-radius:50%;object-fit:cover;" 
                   src="'.base_url("uploads/".$profile_photo).'">';
        }
        return '<i class="fas fa-user-circle" style="font-size:40px;"></i>';
    }

    private function generatePayFeeModal()
    {
        return '<div class="modal fade" id="payfee" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Pay Fee</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <label id="totalAmount">Total: </label>0
                &nbsp;&nbsp;&nbsp;<label>Paid: </label>0<br>
                <label>Discount: </label>0&nbsp;&nbsp;&nbsp;
                <label>Balance: </label>0
                <form id="payFeeData">
                <input type="hidden" name="chalan_id" id="ChalanID">
                <input type="hidden" name="PaidDate" id="PaidDate">
                <input type="hidden" name="student_id" id="studentID">
                <input type="hidden" name="fineamount" id="fineamount">
                  <div class="form-group">
                    <label for="recipient-name" class="col-form-label">Fee Amount:</label>
                    <input type="text" class="form-control" name="fee_amount" id="feeAmount">
                  </div>
                  <div class="form-group">
                    <label for="message-text" class="col-form-label">Discount:</label>
                    <input type="text" id="discountAmount" class="form-control" value="0" name="discountamount">
                  </div>
                  <div class="form-group">
                    <label for="message-text" class="col-form-label">Paid Amount:</label>
                    <input type="text" id="PaidAmount" class="form-control" name="paid_amount">
                  </div>
                 <div class="form-group">
                    <label for="message-text" class="col-form-label">Balance:</label>
                    <input type="text" id="balance" readonly class="form-control" value="0" name="balance">
                  </div>
                   <div class="form-group" id="feeFine" style="display:none;">
                    <label for="message-text" class="col-form-label">Fine:</label><br>
                    <label><input type="radio" class="fine" value="paywithfine" name="fine"> Pay With Fine</label>
                    <label><input type="radio" value="paywithoutfine" name="fine" class="fine"> Pay Without Fine</label>
                    <label><input type="radio" checked="checked" name="fine" value="paywithdiscountfine" class="fine"> Pay With Discount Fine</label>
                   </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="payFee" class="btn btn-primary">Submit</button>
              </div>
            </div>
          </div>
        </div>';
    }


public function getAdvanceFeeStudentsAjax()
{
    $student_id = $this->request->getPost('student_id');
    $campus_id = session('member_campusid');

    if (!$student_id) {
        return $this->response->setJSON(['success' => false, 'message' => 'Student ID missing']);
    }

    // Get parent_id of the student
    $student = $this->db->table('students')
        ->select('parent_id')
        ->where('student_id', $student_id)
        ->where('campus_id', $campus_id)
        ->get()->getRow();

    if (!$student) {
        return $this->response->setJSON(['success' => false, 'message' => 'Student not found']);
    }

    $parent_id = $student->parent_id;

    // Fetch all students under this parent
    $advanceTypeId = advance_fee_type_id();

    $students = $this->db->table('students s')
        ->select("
            s.student_id,
            CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) AS student_name,
            COALESCE((
                SELECT SUM(fc.amount - fc.discount)
                FROM fee_chalan fc
                WHERE fc.student_id = s.student_id
                  AND fc.status = 'unpaid'
                  AND fc.fee_type_id != {$advanceTypeId}
            ), 0) AS total_due,
            COALESCE((
                SELECT amount
                FROM fee_chalan
                WHERE student_id = s.student_id
                  AND fee_type_id = {$advanceTypeId}
                  AND status = 'paid'
                  AND amount > 0
                ORDER BY chalan_id DESC
                LIMIT 1
            ), 0) AS advance_fee
        ")
        ->where('s.parent_id', $parent_id)
        ->where('s.status', 1)
        ->where('s.campus_id', $campus_id)
        ->get()->getResultArray();

    return $this->response->setJSON([
        'success' => true,
        'student_dues' => $students
    ]);
}


public function saveAdvanceFee()
{
    $fees = json_decode($this->request->getPost('fees'), true);
    $campus_id = (int) session('member_campusid');
    $user_id   = (int) session('member_userid');
    $account_id = (int) $this->request->getPost('account_id');
    $paid_date = (string) ($this->request->getPost('paid_date') ?: date('Y-m-d'));

    if (! $fees || ! is_array($fees)) {
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid input']);
    }

    $db = \Config\Database::connect();
    $finance = new CampusFinanceService($db);
    $db->transStart();
    $saved = [];

    foreach ($fees as $student_id => $amount) {
        $student_id = (int) $student_id;
        $amount     = round((float) $amount, 2);

        if ($student_id <= 0 || $amount <= 0) {
            continue;
        }

        $belongs = $db->table('students')
            ->where('student_id', $student_id)
            ->where('campus_id', $campus_id)
            ->where('status', 1)
            ->countAllResults();

        if ($belongs < 1) {
            continue;
        }

        $result = add_student_advance_payment($db, $student_id, $amount, $user_id, $paid_date);
        $saved[$student_id] = $result;

        if ($finance->campusHasFinanceAccounts($campus_id) && ($result['chalan_id'] ?? 0) > 0) {
            $finance->recordStandaloneCredit(
                $campus_id,
                $account_id,
                $amount,
                $paid_date,
                $user_id,
                'advance_payment',
                $student_id,
                (int) $result['chalan_id']
            );
        }
    }

    $db->transComplete();

    if ($saved === []) {
        return $this->response->setJSON(['success' => false, 'message' => 'No valid advance payments to save.']);
    }

    return $this->response->setJSON(['success' => true, 'balances' => $saved]);
}

    /**
     * Manage / update advance fee balances (list students with balance > 0).
     */
    public function advanceFee()
    {
        helper('school');
        $campus_id  = (int) session('member_campusid');
        $session_id = (int) session('member_sessionid');

        return view('admin/advance_fee/index', [
            'rows' => $this->fetchAdvanceBalanceRows($campus_id, $session_id),
        ]);
    }

    /**
     * POST balances: { "student_id": "amount", ... } — set exact balance per student.
     */
    public function saveAdvanceBalances(): ResponseInterface
    {
        helper('school');

        if (! $this->request->is('post')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request.']);
        }

        $raw = $this->request->getPost('balances');
        if (is_string($raw)) {
            $balances = json_decode($raw, true);
        } else {
            $balances = is_array($raw) ? $raw : [];
        }

        if ($balances === [] || ! is_array($balances)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No balances submitted.']);
        }

        $campus_id = (int) session('member_campusid');
        $user_id   = (int) session('member_userid');
        $updated   = 0;
        $errors    = [];

        $this->db->transStart();

        foreach ($balances as $studentId => $amount) {
            $studentId = (int) $studentId;
            $amount    = round((float) $amount, 2);

            if ($studentId <= 0) {
                continue;
            }

            $belongs = $this->db->table('students')
                ->where('student_id', $studentId)
                ->where('campus_id', $campus_id)
                ->where('status', 1)
                ->countAllResults();

            if ($belongs < 1) {
                $errors[] = 'Student #' . $studentId . ' not found.';
                continue;
            }

            set_student_advance_balance($this->db, $studentId, $amount, $user_id);
            $updated++;
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Could not save advance balances.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $updated . ' record(s) updated.',
            'updated' => $updated,
            'errors'  => $errors,
        ]);
    }

    /**
     * @return list<object>
     */
    private function fetchAdvanceBalanceRows(int $campus_id, int $session_id): array
    {
        if ($campus_id <= 0) {
            return [];
        }

        $advanceId = advance_fee_type_id();

        $sql = '
            SELECT
                fc.chalan_id,
                fc.student_id,
                fc.amount,
                fc.paid_date,
                s.reg_no,
                TRIM(CONCAT(s.first_name, " ", COALESCE(s.last_name, ""))) AS student_name,
                COALESCE(c.class_name, "") AS class_name,
                COALESCE(sec.section_name, "") AS section_name
            FROM fee_chalan fc
            INNER JOIN (
                SELECT student_id, MAX(chalan_id) AS chalan_id
                FROM fee_chalan
                WHERE fee_type_id = ?
                  AND status = "paid"
                  AND amount > 0
                GROUP BY student_id
            ) latest ON latest.chalan_id = fc.chalan_id
            INNER JOIN students s ON s.student_id = fc.student_id
            LEFT JOIN student_class sc
                ON sc.student_id = s.student_id AND sc.session_id = ?
            LEFT JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
            LEFT JOIN classes c ON c.class_id = cs.class_id
            LEFT JOIN sections sec ON sec.section_id = cs.section_id
            WHERE s.campus_id = ?
              AND s.status = 1
            ORDER BY student_name ASC, s.reg_no ASC
        ';

        $result = $this->db->query($sql, [$advanceId, $session_id, $campus_id]);

        return $result ? $result->getResult() : [];
    }
}