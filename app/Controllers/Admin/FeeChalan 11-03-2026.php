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

    public function index()
    {
        return view('admin/fee_chalan');
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
// In your controller (e.g., app/Controllers/Admin/FeeChalan.php)

public function add()
{
    $db         = \Config\Database::connect();
    $campusInfo = getCampusInfo();
    $schoolInfo = getSchoolInfo();
    $system_id  = (int) ($schoolInfo->system_id ?? 0);

    // dropdown data
    $fee_type_info = $db->table('fee_type')->where('s_flag',1)->where('system_id',$system_id)->where('status',1)->get()->getResult();
    $a_fee_type_info = $db->table('fee_type')->where('a_flag',1)->where('system_id',$system_id)->where('status',1)->get()->getResult();
    $t_fee_type_info = $db->table('fee_type')->where('t_flag',1)->where('system_id',$system_id)->where('status',1)->get()->getResult();
    $h_fee_type_info = $db->table('fee_type')->where('h_flag',1)->where('system_id',$system_id)->where('status',1)->get()->getResult();

    $data = [
        'mode'                  => 'add',
        'isEdit'                => false,                 // explicitly tell the view it's add mode
        'pageTitle'             => 'Generate Fee Chalan',
        'campusInfo'            => $campusInfo,
        'fee_type_info'         => $fee_type_info,
        'a_fee_type_info'       => $a_fee_type_info,
        't_fee_type_info'       => $t_fee_type_info,
        'h_fee_type_info'       => $h_fee_type_info,
        'fee_chalan'            => null,                  // no record in add mode
        'selected_fee_type_ids' => [],                    // nothing selected by default
    ];

    // Wrapper view that includes fee_chalan_add.php inside
    return view('admin/fee_chalan_add', $data);
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
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

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
    $force_month    = (int) $request->getGet('force_month') === 1; // <-- NEW

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

// private function handleInvoiceAndFee(
//     int $student_id, int $class_id, int $std_type, int $campus_id, int $session_id,
//     string $issue_date, string $due_date, string $fee_month, int $user_id, string $date,
//     array $feeTypes, $monthly_discount
// ) {
//     $db = \Config\Database::connect();
//     $db->transBegin();

//     try {
//         // Existing invoice?
//         $invRes = $db->table('invoices')
//             ->where('student_id', $student_id)
//             ->where('fee_month',  $fee_month)
//             ->where('issue_date', $issue_date)
//             ->get();
//         $this->dbError($db, 'invoice_lookup');

//         $existingInvoice = $invRes ? $invRes->getRow() : null;
//         $invoice_no = $existingInvoice ? $existingInvoice->invoice_no : $this->generateInvoiceNumber($fee_month);

//         if (!$existingInvoice) {
//             $db->table('invoices')->insert([
//                 'student_id' => $student_id,
//                 'issue_date' => $issue_date,
//                 'fee_month'  => $fee_month,
//                 'yr'         => date('y', strtotime($fee_month . '-01')),
//                 'invoice_no' => $invoice_no,
//                  'created_date' => $date,
//             'updated_date' => $date,
//             'user_id' => $user_id
//             ]);
//             if ($this->dbError($db, 'invoice_insert')) {
//                 $db->transRollback();
//                 return false;
//             }
//             $this->debug('invoice_insert_ok', ['student_id' => $student_id, 'invoice_no' => $invoice_no]);
//         }

//         $insertedCount = 0;

//         foreach ($feeTypes as $fee) {
//             $fee_type_id = (int)$fee['fee_type_id'];
//             $isMonthly   = !empty($fee['is_monthly_fee']);

//             // Already exists?
//             $exists = $db->table('fee_chalan')
//                 ->where('student_id', $student_id)
//                 ->where('fee_month',  $fee_month)
//                 ->where('fee_type_id',$fee_type_id)
//                 ->where('invoice_no', $invoice_no)
//                 ->countAllResults();
//             $this->dbError($db, 'chalan_exists_check');

//             if ((int)$exists > 0) {
//                 $this->debug('chalan_skip_exists', [
//                     'student_id' => $student_id,
//                     'fee_type_id'=> $fee_type_id,
//                     'invoice_no' => $invoice_no
//                 ]);
//                 continue;
//             }

//             // Amount lookup (allow default/null flag)
//             $amountRes = $db->table('fee_amount')->select('amount')
//                 ->where('class_id',   $class_id)
//                 ->where('campus_id',  $campus_id)
//                 ->where('session_id', $session_id)
//                 ->where('fee_type_id',$fee_type_id)
               
//                 ->get();
//             $this->dbError($db, 'amount_lookup');

//             $amountRow = $amountRes ? $amountRes->getRow() : null;
//             if (!$amountRow) {
//                 $this->debug('amount_not_found', [
//                     'student_id' => $student_id, 'class_id' => $class_id,
//                     'fee_type_id'=> $fee_type_id, 
//                 ]);
//                 continue;
//             }

//             $default_amount = (float) $amountRow->amount;
//             $pv             = max(1, (int) ($fee['plan_value'] ?? 1));   // plan value
//             $isMonthly      = !empty($fee['is_monthly_fee']);

//             // Multiply monthly fee and discount by plan_value
//             $final_amount   = $isMonthly ? ($default_amount * $pv) : $default_amount;

//             $perUnitDiscount = (float) ($monthly_discount ?? 0);         // discount for one unit/month
//             $discount        = $isMonthly ? ($perUnitDiscount * $pv) : 0.0;

//             // (Recommended) prevent discount from exceeding amount
//             if ($discount > $final_amount) {
//                 $discount = $final_amount;
//             }

//             if ($final_amount <= 0) {
//                 $this->debug('amount_non_positive', [
//                     'student_id' => $student_id,
//                     'fee_type_id'=> $fee_type_id,
//                     'amount'     => $final_amount,
//                     'plan_value' => $pv,
//                     'is_monthly' => $isMonthly
//                 ]);
//                 continue;
//             }
//             // Insert chalan
//             $db->table('fee_chalan')->insert([
//                 'student_id'     => $student_id,
//                 'due_date'       => $due_date,
//                 'issue_date'     => $issue_date,
//                 'fee_month'      => $fee_month,
//                 'fee_month_old'  => date('F Y', strtotime($fee_month . '-01')),
//                 'amount'         => $final_amount,
//                 'discount'       => $discount,
//                 'status'         => 'Unpaid',   // ensure matches ENUM casing if used
//                 'payment_status' => 'Pending',
//                 'fee_type_id'    => $fee_type_id,
//                 'paid_date'      => 0000-00-00,       // avoid '0000-00-00'
//                 'created_date'   => $date,
//                 'updated_date'   => $date,
//                 'user_id'        => $user_id,
//                 'acc_id'         => 0,
//                 'currency_code'  => 'PKR',
//                 'invoice_no'     => $invoice_no
//             ]);
//             if ($this->dbError($db, 'chalan_insert')) {
//                 $db->transRollback();
//                 return false;
//             }

//             $insertedCount++;
//             $this->debug('chalan_insert_ok', [
//                 'student_id'  => $student_id,
//                 'fee_type_id' => $fee_type_id,
//                 'invoice_no'  => $invoice_no,
//                 'amount'      => $final_amount,
//                 'discount'    => $discount
//             ]);
//         }

//         if ($insertedCount > 0 && $db->transStatus() === true) {
//             $db->transCommit();
//             return true;
//         }

//         $this->debug('no_rows_inserted_for_student', [
//             'student_id' => $student_id,
//             'invoice_no' => $invoice_no
//         ]);
//         $db->transRollback();
//         return false;

//     } catch (\Throwable $e) {
//         $db->transRollback();
//         log_message('error', 'handleInvoiceAndFee exception: ' . $e->getMessage());
//         $this->sendEvent([
//             'type'  => 'debug',
//             'level' => 'error',
//             'tag'   => 'exception',
//             'msg'   => $e->getMessage()
//         ], 'debug');
//         return false;
//     }
// }

/**
 * Consume advance (fee_type_id=194) against the student's newly created
 * unpaid chalans for a given month. If partial coverage is needed, split
 * the chalan (update original to the paid part, insert remainder as unpaid).
 *
 * Keeps all other logic intact.
 */


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






/** Emit SSE event (also used for debug). */
private function sendEvent(array $data, string $event = 'message'): void
{
    // Named event *and* data, so you can use addEventListener('debug', ...)
    echo "event: {$event}\n";
    echo 'data: ' . json_encode($data) . "\n\n";
    @ob_flush();
    @flush();
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
        return $this->renderChalan('admin/chalanview/fee_chalan_thermal_copy');
    }

    public function singleCopy()
    {
        return $this->renderChalan('admin/chalanview/fee_chalan_single_copy');
    }

    public function threeCopyPdf()
    {
        return $this->renderChalan('admin/chalanview/fee_chalan_pdf');
    }

    public function withoutDiscount()
    {
        return $this->renderChalan('admin/chalanview/fee_chalan_without_discount');
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



    public function familywise()
    {
        return $this->renderChalan('admin/chalanview/fee_chalan_familywise', true);
    }

    public function familywiseSingleCopy()
    {
        return $this->renderChalan('admin/chalanview/single_copy_fee_chalan_familywise', true);
    }

    public function hostel()
    {
        return $this->renderChalan('admin/chalanview/fee_chalan_hostel', false, true);
    }

    public function withHeader()
    {
        return $this->renderChalan('admin/fee_chalan_with_header');
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
        // <=7 -> pad blanks; >7 -> first 6 latest, 7th = Arrears (sum of the rest)
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

        // Save for the view
        $row['unpaid_rows']          = $unpaid;    // raw list (if needed)
        $row['unpaid_display_rows']  = $display;   // exactly 7 rows for rendering
        $row['unpaid_total_payable'] = $totalAll;  // for "Payable Within Due Date"
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



    private function fetchFamilywiseChalanData(bool $isFamilywise = true): array
    {
        $session = session();
        $campus_id = $session->get('member_campusid');
        $session_id = $session->get('member_sessionid');
        $db = db_connect();


        $systemInfo = getSchoolInfo();
        $campusinfo = $db->table('campus')->where('campus_id', $campus_id)->get()->getRow();

        $parents = $db->query("SELECT DISTINCT p.* FROM parents p JOIN students s ON p.parent_id = s.parent_id WHERE s.status = 1 AND s.campus_id = {$campus_id}")->getResult();


        $student_data = [];
        foreach ($parents as $parent) {
            $students = $db->table('students')->where(['status' => 1, 'parent_id' => $parent->parent_id])->get()->getResult();
            $student_ids = array_column($students, 'student_id');

            if (empty($student_ids)) continue;

            $chalans = $db->table('fee_chalan')->whereIn('student_id', $student_ids)->where('status', 'unpaid')->where('fee_type_id !=', 0)->get()->getResult();

            if (empty($chalans)) continue;

            $fees = [];
            $fine_total = 0;
            $latest = null;
            foreach ($chalans as $chalan) {
                $key = $chalan->fee_month . '|' . $chalan->fee_type_id;
                if (!isset($fees[$key])) {
                    $fees[$key] = ['amount' => 0, 'discount' => 0, 'fee_month' => $chalan->fee_month, 'fee_type_id' => $chalan->fee_type_id];
                }
                $fees[$key]['amount'] += $chalan->amount;
                $fees[$key]['discount'] += $chalan->discount;

                if ($chalan->chalan_id > ($latest->chalan_id ?? 0)) {
                    $latest = $chalan;
                }
            }

            $fee_types = $db->table('fee_type')->get()->getResult();
            $fee_type_map = array_column($fee_types, 'fee_type_name', 'fee_type_id');

            $student_fee = [];
            //$student_fee = [];
            foreach ($fees as $entry) {
                $month_name = '';
                $year = '';

                if (strpos($entry['fee_month'], '/') !== false) {
                    [$month, $year] = explode('/', $entry['fee_month']);
                    $month_name = DateTime::createFromFormat('!m', $month)->format('F');
                } else {
                    $month_name = $entry['fee_month'];
                    $year = '';
                }

                $student_fee[] = [
                    'amount' => $entry['amount'],
                    'discount' => $entry['discount'],
                    'fee_month' => ($fee_type_map[$entry['fee_type_id']] ?? '') . " ($month_name-$year)"
                ];
            }


            $stdinfo = implode(', ', array_map(fn($s) => $s->first_name . ' ' . $s->last_name, $students));

            $month = '';
            $year = '';
            $month_name = '';

            if (!empty($latest->fee_month) && strpos($latest->fee_month, '/') !== false) {
                [$month, $year] = explode('/', $latest->fee_month);
                $month_name = DateTime::createFromFormat('!m', $month)->format('F');
            } else {
                $month_name = $latest->fee_month ?? '';
                $year = '';
            }

            $student_data[] = [
                'campus_name' => $campusinfo->campus_name ?? '',
                'chalan_no' => $latest->chalan_id ?? '',
                'system_name' => $systemInfo->system_name ?? '',
                'logo' => $systemInfo->logo ?? '',
                'location' => $campusinfo->location ?? '',
                'bank_name' => $campusinfo->bank_name ?? '',
                'bank_address' => $campusinfo->bank_address ?? '',
                'bank_code' => $campusinfo->bank_code ?? '',
                'bank_acc' => $campusinfo->bank_acc ?? '',
                'chalan_h_msg' => $campusinfo->chalan_h_msg ?? '',
                'chalan_f_msg' => $campusinfo->chalan_f_msg ?? '',
                'stdinfo' => $stdinfo,
                'f_name' => $parent->f_name,
                'family_no' => $parent->parent_id,
                'father_contact' => $parent->father_contact,
                'mother_contact' => $parent->mother_contact,
                'emergency_contact' => $parent->emergency_contact,
                'fee_month'   => "$month_name-$year",
                'issue_date' => date('j-M-Y', strtotime($latest->issue_date)),
                'due_date' => date('j-M-Y', strtotime($latest->due_date)),
                'student_fee' => $student_fee,
                'fee_fine' => [['amount' => $fine_total]]
            ];
        }

        return $student_data;
    }

    private function fetchHostelChalanData(bool $isFamilywise = false, bool $isHostel = true): array
    {
        $session = session();
        $campus_id = $session->get('member_campusid');
        $db = db_connect();
        $systemInfo = getSchoolInfo();

        $campusinfo = $db->table('campus')->where('campus_id', $campus_id)->get()->getRow();

        $beds = $db->query('SELECT * FROM h_student_bed WHERE status = 1 AND student_id IN (SELECT student_id FROM students WHERE status = 1 AND campus_id = ?) ORDER BY block_room_id ASC', [$campus_id])->getResult();

        $student_data = [];

        foreach ($beds as $bed) {
            $chalan_id = $db->query('SELECT MAX(chalan_id) AS chalan_id FROM fee_chalan WHERE student_id = ? AND fee_type_id != 0 AND status = "unpaid"', [$bed->student_id])->getRow('chalan_id');
            if (!$chalan_id) continue;

            $chalan = $db->table('fee_chalan')->where(['chalan_id' => $chalan_id, 'status' => 'unpaid'])->get()->getRow();
            if (!$chalan) continue;

            $fee_chalans = $db->query('SELECT SUM(amount) AS amount, SUM(discount) AS discount, fee_type_id, fee_month FROM fee_chalan WHERE status = "unpaid" AND student_id = ? GROUP BY fee_month, fee_type_id', [$bed->student_id])->getResult();

            $fees = [];
            foreach ($fee_chalans as $fc) {
                $type = $db->table('fee_type')->where('fee_type_id', $fc->fee_type_id)->get()->getRow();
                $fees[] = [
                    'amount' => money_from_base($fc->amount),
                    'discount' => money_from_base($fc->discount),
                    'fee_month' => ($type->fee_type_name ?? '') . " (" . $fc->fee_month . ")"
                ];
            }

            [$month, $year] = explode('/', $chalan->fee_month);
            $month_name = DateTime::createFromFormat('!m', $month)->format('F');

            $student = $db->table('students')->where(['status' => 1, 'campus_id' => $campus_id, 'student_id' => $bed->student_id])->get()->getRow();
            $room = $db->table('h_block_rooms')->where('block_room_id', $bed->block_room_id)->get()->getRow();
            $parent = $db->table('parents')->where('parent_id', $student->parent_id)->get()->getRow();

            $others = $db->query('SELECT first_name, last_name FROM students WHERE student_id != ? AND student_id IN (SELECT student_id FROM h_student_bed WHERE status = 1 AND block_room_id = ?) AND status = 1 AND campus_id = ?', [$student->student_id, $bed->block_room_id, $campus_id])->getResult();
            $stdinfo = implode(', ', array_map(fn($s) => $s->first_name . ' ' . $s->last_name, $others));

            $student_data[] = [
                'campus_name' => $campusinfo->campus_name ?? '',
                'chalan_no' => $chalan->chalan_id,
                'system_name' => $systemInfo->system_name ?? '',
                'logo' => $systemInfo->logo ?? '',
                'location' => $campusinfo->location ?? '',
                'bank_name' => $campusinfo->bank_name ?? '',
                'bank_address' => $campusinfo->bank_address ?? '',
                'bank_code' => $campusinfo->bank_code ?? '',
                'bank_acc' => $campusinfo->bank_acc ?? '',
                'chalan_h_msg' => $campusinfo->chalan_h_msg ?? '',
                'chalan_f_msg' => $campusinfo->chalan_f_msg ?? '',
                'stdinfo' => $stdinfo,
                'student_name' => $student->first_name . ' ' . $student->last_name . ' (Room # ' . ($room->room_no ?? '-') . ')',
                'f_name' => $parent->f_name ?? '',
                'father_contact' => $parent->father_contact ?? '',
                'mother_contact' => $parent->mother_contact ?? '',
                'emergency_contact' => $parent->emergency_contact ?? '',
                'whatsapp' => $parent->whatsapp ?? '',
                'fee_month' => "$month_name-$year",
                'issue_date' => date('j-M-Y', strtotime($chalan->issue_date)),
                'due_date' => date('j-M-Y', strtotime($chalan->due_date)),
                'student_fee' => $fees,
                'fee_fine' => []
            ];
        }

        return $student_data;
    }
}
