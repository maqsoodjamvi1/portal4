<?php 

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
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
    }

   public function index()
    {
        $currentRole        = currentUserRoles();
        $sectionsClassInfo  = in_array(5, $currentRole) ? teacherSubjectSections() : userClassSections();

        $campus_id = (int) (session('member_campusid') ?? 1);

        $data = [
            'sectionsclassinfo' => $sectionsClassInfo,
            'payFeeModal'       => $this->generatePayFeeModal(),
            'campus_info'       => session('member_campusid'),
            'session_id'        => session('member_sessionid'),
            'paidTotals'        => $this->getPaidTotals($campus_id), // <-- totals for header badges
        ];

        // If fee_scripts is a separate partial you want on this page, render both:
        return view('admin/fee_chalan_pay', $data)
             . view('admin/fee_scripts');

        // If you don't need the second view, just:
        // return view('admin/fee_chalan_pay', $data);
    }



private function getPaidTotals(int $campus_id): array
{
    $monthStart = date('Y-m-01 00:00:00');
    $monthEnd   = date('Y-m-t 23:59:59');
    $today      = date('Y-m-d');

    // --- This Month ---
    $monthRow = $this->db->table('fee_chalan fc')
        ->select('SUM(fc.amount - fc.discount) AS total', false)
        ->join('students s', 's.student_id = fc.student_id', 'inner')
        ->where('s.campus_id', $campus_id)
        ->where('fc.status', 'paid')
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
                                <h5 class="mb-1" data-toggle="tooltip" title="Family head"><i class="fas fa-user-friends text-primary"></i> ' . esc($parent->f_name ?? 'N/A') . '</h5>
                                <div class="d-flex">
                                 <span class="badge badge-info" data-toggle="tooltip" title="Monthly family fee">
                                        <i class="fas fa-calendar-alt"></i> Rs ' . number_format($totalStudentMonthlyFee, 0) . '
                                    </span>
                                    <span class="badge badge-danger mr-2" data-toggle="tooltip" title="Total family dues">
                                        <i class="fas fa-exclamation-circle"></i> Rs ' . number_format($familyTotalDue, 0) . '
                                    </span>
                                   
                                </div>
                            </div>
            
                            <div class="mt-2 mt-md-0">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="multiCurrencyFeeModal(' . $student->student_id . ')" 
                                        data-toggle="tooltip" title="Multi Currency">
                                        <i class="fas fa-money"></i> Multi Currency
                                    </button>

                                    <div class="custom-control custom-switch d-inline-block ml-2" data-toggle="tooltip" title="Toggle partial payment mode">
                                        <input type="checkbox" class="custom-control-input" id="partialToggle">
                                        <label class="custom-control-label" for="partialToggle"></label>
                                    </div>
                                    <button class="btn btn-outline-primary" onclick="showEditStudentFeeModal(' . $student->student_id . ')" 
                                        data-toggle="tooltip" title="Edit monthly fees">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info" onclick="showAdvanceFeeStudentModal(' . $student->student_id . ')" 
                                        data-toggle="tooltip" title="Pay advance fees">
                                        <i class="fas fa-forward"></i>
                                    </button>
                                    <button class="btn btn-primary btn-icon-only" onclick="addFamilyUnpaidFeesToPool(' . $student->parent_id . ')" 
                                        data-toggle="tooltip" title="Add all family fees to cart">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>

                                  <button type="button" class="btn btn-warning btn-icon-only"
                        onclick="showFamilyFeeHistoryPage(' . (int)$student->parent_id . ')"
                        data-toggle="tooltip" title="Family payment history">
                  <i class="fas fa-file-invoice-dollar"></i>
                </button>
                                    <button type="button" class="btn btn-outline-secondary btn-icon-only"
                        onclick="openChalanEditForPay(' . (int)$student->parent_id . ', 0)"
                        data-toggle="tooltip" title="Edit challan / add fee lines">
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
    ->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id')
    ->join('students s', 's.student_id = fc.student_id')
    ->where('fc.student_id', $student->student_id)
    ->where('fc.status', 'unpaid')
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
        <span class="badge badge-light">Rs ' . number_format($net_amount, 0) . '</span>
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
                <div class="mr-3">' . $profile_photo . '</div>
                <div>
                    <h5 class="mb-0">' . esc($student_display_name) . '</h5>
                    <div class="d-inline-flex align-items-center mt-1">
                        <!-- Monthly Fee -->
                        <span class="badge badge-info mr-2"
                              data-toggle="tooltip" data-placement="top"
                              title="Monthly Student Fee: Rs ' . number_format($monthlyFee, 0) . '">
                            <i class="fas fa-calendar-alt"></i>
                            Rs ' . number_format($monthlyFee, 0) . '
                        </span>

                        <!-- Total Dues -->
                        <span class="badge badge-danger mr-2"
                              data-toggle="tooltip" data-placement="top"
                              title="Total student dues">
                            <i class="fas fa-exclamation-circle"></i>
                            <span id="student-dues-' . esc($student->student_id) . '">' . number_format($student_total, 0) . '</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="ml-auto mt-2 mt-md-0 d-flex justify-content-end">
                <div class="btn-group btn-group-sm">
                    <!-- Icon-only: Move to Cart -->
                    <button type="button"
                            class="btn btn-primary btn-icon-only"
                            onclick="addAllUnpaidFeesToPool(this)"
                            data-student-id="' . esc($student->student_id) . '"
                            data-toggle="tooltip" data-placement="bottom"
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



protected function getStudentMonthlyFee($student_id, $session_id, $campus_id)
{
    // Get student discount
    $discount = $this->db->table('students')
        ->select('discounted_amount')
        ->where('student_id', $student_id)
        ->get()
        ->getRow();
    
    $discountAmount = $discount->discounted_amount ?? 0;

    // Get class fee amount
    $feeAmount = $this->db->table('fee_amount fa')
        ->select('fa.amount')
        ->join('fee_type ft', 'ft.fee_type_id = fa.fee_type_id')
        ->where('ft.system_id', 1)
        ->where('ft.is_monthly_fee', 1)
        ->where('fa.class_id', function($query) use ($student_id, $session_id) {
            $query->select('cs.class_id')
                ->from('student_class sc')
                ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
                ->where('sc.student_id', $student_id)
                ->where('sc.session_id', $session_id);
        })
        ->where('fa.session_id', $session_id)
        ->where('fa.campus_id', $campus_id)
        ->get()
        ->getRow();

    $classFee = $feeAmount->amount ?? 0;

    // Calculate student's monthly fee (class fee - discount)
    return max(0, $classFee - $discountAmount);
}



public function get_studentinfo()
{
    $search_term = trim($this->request->getPost('term') ?? '');
    $cls_sec_id  = $this->request->getPost('flag');
    $campusid    = session('member_campusid');

    $builder = $this->db->table('students')
        ->select('
            students.student_id,
            CONCAT(students.first_name, " ", COALESCE(students.last_name, "")) AS student_name,
            parents.f_name AS father_name,
            CONCAT(classes.class_name, " ", sections.section_name) AS section_name
        ')
        ->join('parents', 'parents.parent_id = students.parent_id', 'left')
        ->join('student_class', 'student_class.student_id = students.student_id AND student_class.status = 1', 'left')
        ->join('class_section', 'class_section.cls_sec_id = student_class.cls_sec_id', 'left')
        ->join('classes', 'classes.class_id = class_section.class_id', 'left')
        ->join('sections', 'sections.section_id = class_section.section_id', 'left')
        ->where('students.status', 1)
        ->where('students.campus_id', $campusid);

    // Faster search using FULLTEXT
    if ($search_term !== '') {
        $escaped = $this->db->escapeString($search_term) . '*'; // partial match
        $builder->groupStart()
            ->where("MATCH(students.first_name, students.last_name) AGAINST ('{$escaped}' IN BOOLEAN MODE)")
            ->orWhere("MATCH(parents.f_name) AGAINST ('{$escaped}' IN BOOLEAN MODE)")
        ->groupEnd();
    }

    // Optional filter by class-section
    if ($cls_sec_id && is_numeric($cls_sec_id)) {
        $builder->where('student_class.cls_sec_id', $cls_sec_id);
    }

    $query = $builder->groupBy('students.student_id')->get();

    $data = array_map(function ($row) {
        return [
            'id'   => $row['student_id'],
            'text' => "{$row['student_name']} c/o {$row['father_name']} {$row['section_name']}"
        ];
    }, $query->getResultArray());

    return $this->response->setJSON($data);
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
            'message' => 'No active students found for this parent.'
        ]);
    }

    // Today's payment: SUM(amount - discount)
   $todayPaid = $db->table('fee_chalan')
    ->select('SUM(amount - discount) AS total_paid')
    ->whereIn('student_id', $student_ids)
    ->where('DATE(updated_date)', date('Y-m-d'))
    ->where('status', 'paid')
    ->get()
    ->getRow()->total_paid ?? 0;

    // This Month's payment: SUM(amount - discount)
    $monthPaid = $db->table('fee_chalan')
        ->select('SUM(amount - discount) AS total_paid')
        ->whereIn('student_id', $student_ids)
        ->where('updated_date >=', $month_start)
        ->where('status', 'paid')
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

    return $this->response->setJSON([
        'success' => true,
        'totalToday' => floatval($todayPaid),
        'totalMonth' => floatval($monthPaid),
        'familyTotalDue' => floatval($familyDue),
        'parent_name' => $parentName,
        'student_count' => $student_count,
        'today_label' => date('l, d M Y'),
        'month_label' => date('F Y')
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

    // Step 2: Get all paid fees for this month's records of those students
    $paidFees = $this->db->table('fee_chalan fc')
        ->select('fc.*, s.first_name, s.last_name, ft.fee_type_name')
        ->join('students s', 's.student_id = fc.student_id')
        ->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id')
        ->whereIn('fc.student_id', $student_ids)
        ->where('fc.status', 'paid')
        ->where('fc.updated_date >=', $month_start)
        ->orderBy('fc.updated_date', 'DESC')
        ->get()
        ->getResult();

    return $this->response->setJSON([
        'success' => true,
        'data' => $paidFees,
        'today' => $today
    ]);
}


public function makeUnpaid()
{
    $chalan_id = $this->request->getPost('chalan_id');
    $today = date('Y-m-d');

    if (!$chalan_id) {
        return $this->response->setJSON(['success' => false, 'message' => 'Missing chalan ID']);
    }

    $db = \Config\Database::connect();
    $builder = $db->table('fee_chalan');

    // Fetch chalan to validate
    $chalan = $builder->where('chalan_id', $chalan_id)->get()->getRow();

    if (!$chalan) {
        return $this->response->setJSON(['success' => false, 'message' => 'Fee record not found']);
    }

    // ✅ Check if updated_date is today
    $updatedDate = $chalan->updated_date ? date('Y-m-d', strtotime($chalan->updated_date)) : null;
    if ($updatedDate !== $today) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Only records updated today can be marked unpaid'
        ]);
    }

    $user_id = session('member_userid');

    // ✅ Update the record
    $builder->where('chalan_id', $chalan_id)->update([
        'status'       => 'unpaid',
        'paid_date'    => null,
        'user_id'      => $user_id,
        'updated_date' => date('Y-m-d H:i:s') // refresh update timestamp
    ]);

    return $this->response->setJSON(['success' => true]);
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
                <div class="fph-fee-item-left pr-2">
                    ' . $studentPrefix . '
                    <span class="fph-fee-type-pill">' . esc($r->fee_type_name ?? 'N/A') . '</span>
                    <span class="fph-fee-period text-muted">' . esc($feeMonth) . '</span>
                    <span class="fph-fee-inv text-muted">' . $invShort . '</span>
                    ' . ($paidAt !== '' ? $paidAt : '') . '
                    ' . ($amtDetail !== '' ? '<div class="fph-fee-amt-note-wrap">' . $amtDetail . '</div>' : '') . '
                </div>
                <div class="fph-fee-item-net text-success font-weight-bold text-nowrap">Rs ' . number_format($net, 0) . '</div>
            </li>';
        }

        $linesHtml = '<ul class="list-unstyled fph-day-fee-list mb-0">' . $linesHtml . '</ul>';

        $blocks .= '
        <div class="fph-day-card card mb-3 shadow-sm border-0">
            <div class="card-header fph-day-header d-flex flex-wrap justify-content-between align-items-center py-2">
                <div>
                    <div class="fph-day-title mb-0 font-weight-bold">' . esc($titleMain) . '</div>
                    ' . ($titleSub !== '' ? '<div class="fph-day-weekday small text-muted">' . esc($titleSub) . '</div>' : '') . '
                </div>
                <div class="text-right mt-2 mt-sm-0">
                    <span class="badge badge-success badge-pill fph-day-total-pill">Day total: Rs ' . number_format($dayTotal, 0) . '</span>
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
                    <span class="font-weight-bold text-dark">Overall in this view</span>
                    <div class="small text-muted">' . count($rows) . ' fee payment line(s) across ' . $daysCount . ' payment day(s)</div>
                </div>
                <div class="text-right mt-2 mt-md-0">
                    <div class="small text-muted">Combined net paid</div>
                    <div class="h5 mb-0 text-success font-weight-bold">Rs ' . number_format($family_total, 0) . '</div>
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
        $campus_id = session('member_campusid');
        $fees = json_decode($this->request->getPost('fees'), true);
        $paid_date = $this->request->getPost('paid_date');
        $user_id = session('member_userid');
        $student = $this->db->table('students')->where('student_id', $student_id)->get()->getRow();
        $session_id = session('member_sessionid');

if (!$student) {
    return $this->response->setJSON(['success' => false, 'message' => 'Student not found']);
}

        if (!$fees || !is_array($fees)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid fee data']);
        }

        $builder = $this->db->table('fee_chalan');
        $updateData = [
            'status' => 'paid',
            'updated_date' => date('Y-m-d H:i:s'),
            'user_id' => $user_id,
            'paid_date' => $paid_date
        ];

        foreach ($fees as $fee) {
            $builder->where('chalan_id', $fee['chalan_id'])->update($updateData);
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


$last_paid_chalan_id = $this->db->table('fee_chalan')
    ->select('chalan_id')
    ->where('status', 'paid')
    ->orderBy('paid_date', 'DESC')
    ->limit(1)
    ->get()
    ->getRow('chalan_id');

return $this->response->setJSON([
    'success' => true,
    'last_chalan_id' => $last_paid_chalan_id, // the latest fee paid
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
    $students = $this->db->table('students s')
        ->select("
            s.student_id,
            CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) AS student_name,
            COALESCE((
                SELECT SUM(fc.amount - fc.discount)
                FROM fee_chalan fc
                WHERE fc.student_id = s.student_id AND fc.status = 'unpaid' AND fc.fee_type_id != 194
            ), 0) AS total_due,
            COALESCE((
                SELECT amount
                FROM fee_chalan
                WHERE student_id = s.student_id AND fee_type_id = 194 AND status = 'paid'
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
    $campus_id = session('member_campusid');
    $session_id = session('member_sessionid');
     $user_id = session('member_userid');
    

    if (!$fees || !is_array($fees)) {
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid input']);
    }

    $db = \Config\Database::connect();
    $builder = $db->table('fee_chalan');

    foreach ($fees as $student_id => $amount) {
        $amount = floatval($amount);
        if ($amount <= 0) continue;

        

        $existing = $builder->where([
            'student_id' => $student_id,
            'fee_type_id' => 194
            
        ])->get()->getRow();

        $data = [
            'amount' => $amount,
            'discount' => 0,
            'fee_type_id' => 194,
            'status' => 'paid',
            'student_id' => $student_id,        
            'fee_month' => date('Y-m'), // Format like "2025-07"
            'user_id' => $user_id,
            'created_date' => date('Y-m-d H:i:s'),
            'paid_date' => date('Y-m-d'),
        ];

        if ($existing) {
            $builder->where('chalan_id', $existing->chalan_id)->update($data);
        } else {
            $builder->insert($data);
        }
    }

    return $this->response->setJSON(['success' => true]);
}



}