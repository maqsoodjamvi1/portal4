<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class FeeAmount extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form']);
        check_permission('admin-fee-amount');
    }

public function add(): string
{
    check_permission('admin-add-fee-amount');
 
    $campus_id = $this->session->get('member_campusid');
    $session_id = $this->session->get('member_sessionid');
    $schoolinfo = getSchoolInfo();

    // Check if session exists
    if (!$session_id) {
        // Try to get the latest session
        $latestSession = $this->db->table('academic_session')
            ->where('system_id', $schoolinfo->system_id)
            ->orderBy('session_id', 'DESC')
            ->get()->getRow();
        
        if ($latestSession) {
            $session_id = $latestSession->session_id;
            // Store in session for future use
            $this->session->set('member_sessionid', $session_id);
        }
    }

    // Get campus flags
    $campus_flags = $this->db->table('campus')
        ->select('daycare_flag, boarding_flag')
        ->where('campus_id', $campus_id)
        ->get()
        ->getRow();
    
    // If no campus flags found, create default object
    if (!$campus_flags) {
        $campus_flags = new \stdClass();
        $campus_flags->daycare_flag = 0;
        $campus_flags->boarding_flag = 0;
    }
    
    // Determine which fee type to show (1=daycare, 2=boarding)
    $flag = 0;
    $show_selector = false;
    
    if ($this->request->getGet('force_flag')) {
        $flag = (int)$this->request->getGet('force_flag');
        $show_selector = true;
    } else {
        if ($campus_flags->daycare_flag == 1) {
            $flag = 1;
        } 
        if ($campus_flags->boarding_flag == 1) {
            $flag = 2;
        }
    }

    // Get classes and fee types
    $classesinfo = $this->db->table('classes')
        ->where('system_id', $schoolinfo->system_id)
        ->where('status', 1)
        ->orderBy('class_name', 'ASC')
        ->get()->getResult();

    // Get fee types filtered by std_type based on flag
    $fee_type_info = $this->db->table('fee_type')
        ->where('system_id', $schoolinfo->system_id)
        ->where('status', 1)
        ->where('std_type', $flag > 0 ? $flag : 1)  // Default to 1 (daycare) if flag not set
        ->orderBy('fee_type_name', 'ASC')
        ->get()->getResult();

    // Order fee types with monthly fee first
    $monthly_fee = null;
    $other_fees = [];
    foreach ($fee_type_info as $fee) {
        if ($fee->is_monthly_fee) {
            $monthly_fee = $fee;
        } else {
            $other_fees[] = $fee;
        }
    }
    $fee_type_ordered = $monthly_fee ? array_merge([$monthly_fee], $other_fees) : $other_fees;

    // Get academic session information
    $academic_sessioninfo = $this->db->table('academic_session')
        ->where('system_id', $schoolinfo->system_id)
        ->orderBy('session_id', 'DESC')
        ->get()->getResult();

    // Get current academic session
    $current_academic_sessioninfo = null;
    if ($session_id) {
        $current_academic_sessioninfo = $this->db->table('academic_session')
            ->where('system_id', $schoolinfo->system_id)
            ->where('session_id', $session_id)
            ->get()->getRow();
    }
    
    // If still no current session, get the latest session
    if (!$current_academic_sessioninfo && !empty($academic_sessioninfo)) {
        $current_academic_sessioninfo = $academic_sessioninfo[0];
        $session_id = $current_academic_sessioninfo->session_id;
    }

    // Check if fee amount exists for this campus
    $is_first_time = !$this->db->table('fee_amount')
        ->where('campus_id', $campus_id)
        ->countAllResults();

    // Get previous session fees if available
    $prev_fees = [];
    $prev_session = null;
    
    if ($session_id) {
        $prev_session = $this->db->table('academic_session')
            ->where('system_id', $schoolinfo->system_id)
            ->where('session_id <', $session_id)
            ->orderBy('session_id', 'DESC')
            ->get()->getRow();

        if ($prev_session) {
            $result = $this->db->table('fee_amount')
                ->where('campus_id', $campus_id)
                ->where('session_id', $prev_session->session_id)
                ->get()->getResult();
            foreach ($result as $item) {
                $prev_fees[$item->fee_type_id][$item->class_id] = $item->amount;
            }
        }
    }

    // Get current fee amounts - ONLY FOR CURRENT FLAG TYPE
    $current_fees = [];
    $amount_ids = [];
    $result = [];
    
    if ($session_id) {
        $result = $this->db->table('fee_amount')
            ->where('campus_id', $campus_id)
            ->where('session_id', $session_id)
            ->get()->getResult();
        foreach ($result as $item) {
            $current_fees[$item->fee_type_id][$item->class_id] = $item->amount;
            $amount_ids[$item->fee_type_id][$item->class_id] = $item->amount_id;
        }
    }

    // Get max fee limit for monthly fees
    $max_fee = $this->db->table('campus_bills')
        ->where('campus_id', $campus_id)
        ->where('status', 1)
        ->get()->getRow('max_fee');

    return view('admin/fee_amount_edit', [
        'classesinfo' => $classesinfo,
        'session_id' => $session_id,
        'academic_sessioninfo' => $academic_sessioninfo,
        'current_academic_sessioninfo' => $current_academic_sessioninfo,
        'prev_fees' => $prev_fees,
        'current_fees' => $current_fees,
        'is_first_time' => $is_first_time,
        'fee_type_info' => $fee_type_ordered,
        'amount_ids' => $amount_ids,
        'max_fee' => $max_fee,
        'campus_flags' => $campus_flags,
        'show_flag_selector' => $show_selector,
        'default_flag' => $flag,
        'fee_flag' => $flag,
        'result' => $result,
        'has_session' => ($current_academic_sessioninfo !== null),
        'has_fee_types' => !empty($fee_type_ordered)
    ]);
}
public function get_fees()
{
    $campus_id = $this->session->get('member_campusid');
    $session_id = $this->request->getPost('session_id');
    $flag = $this->request->getPost('flag');

    $fees = $this->db->table('fee_amount')
        ->where('campus_id', $campus_id)
        ->where('session_id', $session_id)
        ->where('flag', $flag)
        ->get()
        ->getResult();

    $result = [];
    foreach ($fees as $fee) {
        $key = 'ftv' . $fee->fee_type_id . '_ci' . $fee->class_id . '_amount';
        $result[$key] = $fee->amount;
    }

    return $this->response->setJSON(['success' => true, 'fees' => $result]);
}

public function save(): ResponseInterface
{
    $isAjax     = $this->request->isAJAX();
    $debug      = (bool) ($this->request->getGet('debug') ?? $this->request->getPost('debug') ?? 0);

    $campus_id  = (int) $this->session->get('member_campusid');
    $session_id = (int) $this->request->getPost('session_id');
    $user_id    = (int) $this->session->get('member_userid');
    $schoolinfo = getSchoolInfo();
    $date       = date('Y-m-d');

    $std_type = (int) ($this->request->getPost('std_type') ?? 0);

    $campus_flags = $this->db->table('campus')
        ->where('campus_id', $campus_id)
        ->get()->getRow();

    if ($std_type === 1 && (int)($campus_flags->daycare_flag ?? 0) !== 1) {
        return $this->response->setJSON(['error' => true, 'msg' => 'Daycare fee is not allowed for this campus.']);
    }
    if ($std_type === 2 && (int)($campus_flags->boarding_flag ?? 0) !== 1) {
        return $this->response->setJSON(['error' => true, 'msg' => 'Boarding fee is not allowed for this campus.']);
    }

    // ---- DEBUG: log key inputs
    log_message('debug', '[fee_amount.save] system_id={system_id}, std_type={std}, daycare_flag={d}, boarding_flag={b}', [
        'system_id' => (int) $schoolinfo->system_id,
        'std'       => $std_type,
        'd'         => (int)($campus_flags->daycare_flag ?? 0),
        'b'         => (int)($campus_flags->boarding_flag ?? 0),
    ]);

    // Build the exact query we run (so we can log/return it)
    $ftBuilder = $this->db->table('fee_type')
        ->select('fee_type_id, fee_type_name, is_monthly_fee, std_type')
        ->where('system_id', $schoolinfo->system_id)
        ->where('is_monthly_fee', 1)
        ->where('std_type', $std_type);

    $compiled = $ftBuilder->getCompiledSelect(); // DEBUG: the raw SQL
    log_message('debug', '[fee_amount.save] monthly_fee_type SQL: {sql}', ['sql' => $compiled]);

    $monthly_fee_type = $ftBuilder->get()->getRow();

    if (!$monthly_fee_type) {
        // ---- DEBUG payload: show what monthly candidates exist at all (same system_id)
        $monthlyCandidates = $this->db->table('fee_type')
            ->select('fee_type_id, fee_type_name, is_monthly_fee, std_type')
            ->where('system_id', $schoolinfo->system_id)
            ->where('is_monthly_fee', 1)
            ->orderBy('std_type', 'ASC')
            ->get()->getResultArray();

        $payload = ['error' => true, 'msg' => 'Please set a monthly fee type.'];

        if ($debug) {
            $payload['debug'] = [
                'system_id'             => (int) $schoolinfo->system_id,
                'std_type_posted'       => $std_type,
                'campus_flags'          => [
                    'daycare_flag'  => (int)($campus_flags->daycare_flag ?? 0),
                    'boarding_flag' => (int)($campus_flags->boarding_flag ?? 0),
                ],
                'query_sql'             => $compiled,
                'monthly_fee_candidates'=> $monthlyCandidates, // each has is_monthly_fee and std_type
            ];
        }
        return $this->response->setJSON($payload);
    }

    $monthly_fee_type_id = (int) $monthly_fee_type->fee_type_id;

    $max_fee = $this->db->table('campus_bills')
        ->where('campus_id', $campus_id)
        ->where('status', 1)
        ->get()->getRow('max_fee');

    foreach ($_POST as $key => $value) {
        if (preg_match('/^ftv(\d+)_ci(\d+)_amount$/', $key, $m)) {
            $fee_type_id = (int) $m[1];
            $class_id    = (int) $m[2];
            $amount      = (float) $value;

            $amount_id_key = $fee_type_id . '_' . $class_id . '_amount_id';
            $amount_id     = (int) ($_POST[$amount_id_key] ?? 0);

            if ($fee_type_id === $monthly_fee_type_id && $max_fee !== null && $amount > (float)$max_fee) {
                return $this->response->setJSON([
                    'error' => true,
                    'msg'   => "Monthly fee for class ID {$class_id} exceeds max limit: {$max_fee}",
                ]);
            }

            $data = [
                'class_id'    => $class_id,
                'fee_type_id' => $fee_type_id,
                'campus_id'   => $campus_id,
                'amount'      => $amount,
                'session_id'  => $session_id,
                'user_id'     => $user_id,
            ];

            if ($amount_id > 0) {
                $data['updated_date'] = $date;
                $this->db->table('fee_amount')->where('amount_id', $amount_id)->update($data);
            } else {
                $exists = $this->db->table('fee_amount')->where([
                    'class_id'    => $class_id,
                    'fee_type_id' => $fee_type_id,
                    'campus_id'   => $campus_id,
                    'session_id'  => $session_id,
                ])->get()->getRow();

                if ($exists) {
                    $this->db->table('fee_amount')->where('amount_id', $exists->amount_id)->update($data);
                } else {
                    $data['created_date'] = $date;
                    $this->db->table('fee_amount')->insert($data);
                }
            }
        }
    }

    // Check if there are any students in the campus
    $studentCount = $this->db->table('students')
        ->where('campus_id', $campus_id)
        ->countAllResults();

    // Determine redirect URL based on student count
    $redirectUrl = ($studentCount > 0) 
        ? base_url('admin/dashboard') 
        : base_url('admin/addbulkstudents/add');

    if ($isAjax) {
        return $this->response->setJSON([
            'success' => true, 
            'msg' => 'Fee amount updated successfully.',
            'redirect_url' => $redirectUrl,
            'has_students' => $studentCount > 0,
            'student_count' => $studentCount
        ]);
    }
    
    return redirect()->to($redirectUrl)->with('success', 'Fee amount updated successfully.');
}

    public function data(): ResponseInterface
    {
        $campus_id = $this->session->get('member_campusid');
        $session_id = $this->request->getPost('session_id');
        $schoolinfo = getSchoolInfo();

        $classes = $this->db->table('classes')
            ->where('system_id', $schoolinfo->system_id)
            ->where('status', 1)->get()->getResult();

        $fee_types = $this->db->table('fee_type')
            ->where('system_id', $schoolinfo->system_id)
            ->where('status', 1)->get()->getResult();

        $data = [];
        foreach ($classes as $class) {
            $row = ['class_name' => $class->class_name];
            foreach ($fee_types as $fee_type) {
                $fee = $this->db->table('fee_amount')->where([
                    'class_id' => $class->class_id,
                    'fee_type_id' => $fee_type->fee_type_id,
                    'session_id' => $session_id,
                    'campus_id' => $campus_id
                ])->get()->getRow();
                $row[$fee_type->fee_type_name] = $fee ? $fee->amount : '-';
            }
            $data[] = $row;
        }

        return $this->response->setJSON(['data' => $data]);
    }
}
