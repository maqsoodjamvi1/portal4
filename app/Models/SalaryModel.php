<?php

    namespace App\Models;

    use CodeIgniter\Model;

    class SalaryModel extends Model
    {
         protected $db;
    protected $table = 'salary_slips';
    protected $primaryKey = 'slip_id';
    protected $allowedFields = [
        'user_id', 'campus_id', 'slip_no', 'month', 'year',
        'basic_salary', 'daily_salary', 'working_days_in_month',
        'attendance_bonus', 'other_bonus', 'total_earnings',
        'absent_deduction', 'late_deduction', 'early_leave_deduction',
        'short_leave_deduction', 'security_deduction', 'advance_deduction',
        'other_deduction', 'total_deductions', 'net_salary',
        'payment_status', 'payment_date', 'payment_method',
        'transaction_id', 'remarks', 'generated_by', 'generated_date'
    ];
    
    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }
        
        /**
         * Get campus salary settings
         */
        public function getCampusSettings($campusId)
        {
            return $this->db->table('campus_salary_settings')
                ->where('campus_id', $campusId)
                ->get()
                ->getRow();
        }
        
        /**
         * Save campus salary settings
         */
       public function saveCampusSettings($campusId, $data, $userId)
    {
        $db = $this->db;
        
        // Debug: Log what we're trying to save
        log_message('debug', 'Saving campus settings for campus: ' . $campusId);
        log_message('debug', 'Data: ' . json_encode($data));
        
        // Check if settings exist
        $existing = $db->table('campus_salary_settings')
            ->where('campus_id', $campusId)
            ->get()
            ->getRow();
        
        // Add common fields
        $data['updated_date'] = date('Y-m-d H:i:s');
        $data['user_id'] = $userId;
        
        if ($existing) {
            // Update existing
            $result = $db->table('campus_salary_settings')
                ->where('campus_id', $campusId)
                ->update($data);
            
            log_message('debug', 'Update result: ' . ($result ? 'Success' : 'Failed'));
            return $result;
        } else {
            // Insert new
            $data['campus_id'] = $campusId;
            $data['created_date'] = date('Y-m-d H:i:s');
            
            $result = $db->table('campus_salary_settings')->insert($data);
            
            log_message('debug', 'Insert result: ' . ($result ? 'Success' : 'Failed'));
            if ($result) {
                return $db->insertID();
            }
            return false;
        }
    }
        
        /**
         * Get employee salary rules
         */
        public function getEmployeeRules($userId)
        {
            return $this->db->table('employee_salary_rules')
                ->where('user_id', $userId)
                ->get()
                ->getRow();
        }
        
        /**
         * Save employee salary rules
         */
        public function saveEmployeeRules($userId, $campusId, $data, $createdBy)
        {
            $existing = $this->getEmployeeRules($userId);
            
            $data['updated_date'] = date('Y-m-d H:i:s');
            
            if ($existing) {
                return $this->db->table('employee_salary_rules')
                    ->where('user_id', $userId)
                    ->update($data);
            } else {
                $data['user_id'] = $userId;
                $data['campus_id'] = $campusId;
                $data['created_date'] = date('Y-m-d H:i:s');
                $data['created_by'] = $createdBy;
                return $this->db->table('employee_salary_rules')
                    ->insert($data);
            }
        }
        
        /**
         * Record salary increment
         */
        public function recordIncrement($userId, $campusId, $oldSalary, $newSalary, $reason, $approvedBy)
        {
            $incrementAmount = $newSalary - $oldSalary;
            $incrementPercentage = $oldSalary > 0 ? ($incrementAmount / $oldSalary) * 100 : 0;
            
            return $this->db->table('salary_history')->insert([
                'user_id' => $userId,
                'campus_id' => $campusId,
                'old_basic_salary' => $oldSalary,
                'new_basic_salary' => $newSalary,
                'increment_amount' => $incrementAmount,
                'increment_percentage' => $incrementPercentage,
                'increment_date' => date('Y-m-d'),
                'increment_reason' => $reason,
                'approved_by' => $approvedBy,
                'created_date' => date('Y-m-d H:i:s')
            ]);
        }
        
        /**
         * Get salary history for employee
         */
        public function getSalaryHistory($userId, $limit = 10)
        {
            return $this->db->table('salary_history')
                ->select('salary_history.*, u.first_name, u.last_name as approver_name')
                ->join('users u', 'salary_history.approved_by = u.id', 'left')
                ->where('salary_history.user_id', $userId)
                ->orderBy('increment_date', 'DESC')
                ->limit($limit)
                ->get()
                ->getResult();
        }
        
        /**
         * Get salary slips for employee
         */
        public function getSalarySlips($userId, $year = null, $month = null)
        {
            $builder = $this->db->table('salary_slips')
                ->where('user_id', $userId)
                ->orderBy('year', 'DESC')
                ->orderBy('month', 'DESC');
            
            if ($year) {
                $builder->where('year', $year);
            }
            if ($month) {
                $builder->where('month', $month);
            }
            
            return $builder->get()->getResult();
        }
        
        /**
         * Get salary slip by ID
         */
        public function getSalarySlip($slipId)
        {
            return $this->db->table('salary_slips')
                ->where('slip_id', $slipId)
                ->get()
                ->getRow();
        }
        
        /**
         * Update payment status
         */
    /**
 * Update payment status
 */
public function updatePaymentStatus($slipId, $status, $paymentData = [])
{
    $db = $this->db;
    
    $data = [
        'payment_status' => $status
    ];
    
    if ($status == 'paid') {
        $data['payment_date'] = date('Y-m-d H:i:s');
        if (isset($paymentData['payment_method'])) {
            $data['payment_method'] = $paymentData['payment_method'];
        }
        if (isset($paymentData['transaction_id'])) {
            $data['transaction_id'] = $paymentData['transaction_id'];
        }
        if (isset($paymentData['remarks'])) {
            $data['remarks'] = $paymentData['remarks'];
        }
    }
    
    // Only update the slip
    return $db->table('salary_slips')
        ->where('slip_id', $slipId)
        ->update($data);
}

/**
 * Log payment transaction for audit purposes
 */
public function logPaymentTransaction($slipId, $userId, $amount, $paymentMethod, $transactionId, $remarks = null)
{
    $db = $this->db;
    
    // Check if acc_journal_entries table exists
    $tables = $db->listTables();
    
    if (in_array('acc_journal_entries', $tables)) {
        // Use existing accounting table
        $db->table('acc_journal_entries')->insert([
            'entry_date' => date('Y-m-d H:i:s'),
            'reference_no' => 'SAL-PAY-' . $slipId,
            'description' => 'Salary payment for employee ID: ' . $userId . ' - Amount: ' . number_format($amount, 2),
            'campus_id' => session()->get('member_campusid'),
            'user_id' => session()->get('member_userid'),
            'created_date' => date('Y-m-d H:i:s')
        ]);
    } else {
        // Log to file if table doesn't exist
        log_message('info', "Salary Payment - Slip: $slipId, User: $userId, Amount: $amount, Method: $paymentMethod, Trans: $transactionId");
    }
}
    /**
     * Log payment transaction for audit purposes
     */
   

    public function bulkUpdatePaymentStatus()
    {
        $db = $this->db ?? \Config\Database::connect();
        $slipIds = $this->request->getPost('slip_ids');
        $paymentMethod = $this->request->getPost('payment_method');
        
        if (empty($slipIds)) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'No salary slips selected'
            ]);
        }
        
        $updated = 0;
        $failed = 0;
        
        foreach ($slipIds as $slipId) {
            $result = $db->table('salary_slips')
                ->where('slip_id', $slipId)
                ->where('payment_status', 'pending')
                ->update([
                    'payment_status' => 'paid',
                    'payment_method' => $paymentMethod,
                    'payment_date' => date('Y-m-d H:i:s'),
                    'updated_date' => date('Y-m-d H:i:s')
                ]);
            
            if ($result) {
                $updated++;
            } else {
                $failed++;
            }
        }
        
        return $this->response->setJSON([
            'success' => true,
            'msg' => "Updated $updated salary slips. Failed: $failed"
        ]);
    }
        /**
         * Generate monthly attendance summary
         */
        public function generateAttendanceSummary($campusId, $year, $month)
        {
            $db = $this->db;
            
            // Clear existing summary
            $db->table('monthly_attendance_summary')
                ->where('campus_id', $campusId)
                ->where('year', $year)
                ->where('month', $month)
                ->delete();
            
            // Get campus settings for thresholds
            $settings = $this->getCampusSettings($campusId);
            $lateThreshold = $settings->late_threshold ?? '08:15:00';
            $graceMinutes = $settings->late_grace_minutes ?? 5;
            
            // Calculate late threshold with grace
            $lateThresholdWithGrace = date('H:i:s', strtotime($lateThreshold) + ($graceMinutes * 60));
            
            // Get all active employees
            $employees = $db->table('users')
                ->where('campus_id', $campusId)
                ->where('status', 1)
                ->where('leaving_date IS NULL OR leaving_date >', date("$year-$month-31"))
                ->get()
                ->getResult();
            
            foreach ($employees as $employee) {
                // Get attendance for the month
                $attendance = $db->table('attendance_employee')
                    ->where('emp_id', $employee->id)
                    ->where('MONTH(date)', $month)
                    ->where('YEAR(date)', $year)
                    ->get()
                    ->getResult();
                
                $present = 0;
                $absent = 0;
                $late = 0;
                $earlyLeave = 0;
                $totalLateMinutes = 0;
                
                foreach ($attendance as $att) {
                    if ($att->status == 'present') {
                        $present++;
                        
                        // Check late
                        if ($att->checkin > $lateThresholdWithGrace) {
                            $late++;
                            $lateMinutes = (strtotime($att->checkin) - strtotime($lateThreshold)) / 60;
                            $totalLateMinutes += max(0, $lateMinutes);
                        }
                        
                        // Check early leave (if checkout before 2 PM)
                        if ($att->checkout && $att->checkout < '14:00:00') {
                            $earlyLeave++;
                        }
                    } elseif ($att->status == 'absent') {
                        $absent++;
                    }
                }
                
                // Get approved leaves
                $approvedLeaves = $db->table('employees_leave_applications')
                    ->where('emp_id', $employee->id)
                    ->where('status', 'approved')
                    ->where('leave_start_date <=', date("$year-$month-31"))
                    ->where('leave_end_date >=', date("$year-$month-01"))
                    ->countAllResults();
                
                $unpaidLeaves = max(0, $absent - $approvedLeaves);
                
                // Insert summary
                $db->table('monthly_attendance_summary')->insert([
                    'user_id' => $employee->id,
                    'campus_id' => $campusId,
                    'year' => $year,
                    'month' => $month,
                    'total_working_days' => $settings->working_days_per_month ?? 26,
                    'present_days' => $present,
                    'absent_days' => $absent,
                    'late_days' => $late,
                    'early_leave_days' => $earlyLeave,
                    'approved_leaves' => $approvedLeaves,
                    'unpaid_leaves' => $unpaidLeaves,
                    'total_late_minutes' => $totalLateMinutes,
                    'generated_date' => date('Y-m-d H:i:s')
                ]);
            }
            
            return true;
        }
        
        /**
         * Generate monthly salary slips
         */
     /**
     * Generate monthly salary slips
     */

     public function generateSalarySlips($campusId, $year, $month, $generatedBy)
    {
        $db = $this->db;
        
        // Get campus settings
        $settings = $this->getCampusSettings($campusId);
        if (!$settings) {
            return [
                'success' => false, 
                'message' => 'Campus salary settings not configured',
                'generated' => 0,
                'errors' => ['Campus salary settings not configured']
            ];
        }
        
        // Get attendance summary
        $summaries = $db->table('monthly_attendance_summary')
            ->where('campus_id', $campusId)
            ->where('year', $year)
            ->where('month', $month)
            ->get()
            ->getResult();
        
        if (empty($summaries)) {
            return [
                'success' => false,
                'message' => 'No attendance summary found',
                'generated' => 0,
                'errors' => ['No attendance data available for this month']
            ];
        }
        
        $generated = 0;
        $errors = [];
        
        foreach ($summaries as $summary) {
            // Check if slip already exists
            $existing = $db->table('salary_slips')
                ->where('user_id', $summary->user_id)
                ->where('year', $year)
                ->where('month', $month)
                ->get()
                ->getRow();
            
            if ($existing) {
                continue;
            }
            
            // Get employee data
            $employee = $db->table('users')
                ->select('id, basic_salary, first_name, last_name, bank_account')
                ->where('id', $summary->user_id)
                ->get()
                ->getRow();
            
            if (!$employee) {
                $errors[] = "Employee ID {$summary->user_id} not found";
                continue;
            }
            
            // IMPORTANT: Use $employee->basic_salary, not $basicSalary
            $basicSalaryValue = $employee->basic_salary;
            
            if (!$basicSalaryValue || $basicSalaryValue <= 0) {
                $errors[] = "Employee {$employee->first_name} {$employee->last_name} (ID: {$employee->id}) has no basic salary set";
                continue;
            }
            
            // Get employee rules
            $empRules = $this->getEmployeeRules($employee->id);
            
            $workingDays = $settings->working_days_per_month ?? 26;
            $dailySalary = $basicSalaryValue / $workingDays;
            
            // Calculate deductions
            $absentDeduction = 0;
            $lateDeduction = 0;
            $earlyLeaveDeduction = 0;
            $securityDeduction = 0;
            
            // Absent deduction
            if ($summary->unpaid_leaves > 0 && (!$empRules || $empRules->apply_deduction != 0)) {
                if ($settings->deduction_type == 'per_day_salary') {
                    $absentDeduction = $summary->unpaid_leaves * $dailySalary;
                } elseif ($settings->deduction_type == 'fixed_amount') {
                    $absentDeduction = $summary->unpaid_leaves * ($settings->deduction_per_day_amount ?? 0);
                } elseif ($settings->deduction_type == 'percentage') {
                    $absentDeduction = $basicSalaryValue * (($settings->deduction_per_day_percentage ?? 0) / 100);
                }
            }
            
            // Late deduction
            if ($settings->late_deduction_enabled && $summary->total_late_minutes > 0) {
                $lateDeduction = $summary->total_late_minutes * ($settings->late_deduction_amount ?? 0);
            }
            
            // Security deduction
            if ($settings->security_deduction_enabled && (!$empRules || !$empRules->security_deduction_waived)) {
                if ($settings->security_deduction_type == 'fixed_amount') {
                    $securityDeduction = $settings->security_deduction_value ?? 0;
                } elseif ($settings->security_deduction_type == 'percentage') {
                    $securityDeduction = $basicSalaryValue * (($settings->security_deduction_value ?? 0) / 100);
                }
            }
            
            // Calculate bonuses
            $attendanceBonus = 0;
            if ($settings->attendance_bonus_enabled && (!$empRules || $empRules->bonus_eligible)) {
                if ($summary->present_days >= $settings->attendance_bonus_days_required) {
                    if ($settings->attendance_bonus_type == 'per_day_salary') {
                        $attendanceBonus = ($summary->present_days - $settings->attendance_bonus_days_required) * $dailySalary;
                    } elseif ($settings->attendance_bonus_type == 'fixed_amount') {
                        $attendanceBonus = $settings->attendance_bonus_amount ?? 0;
                    }
                }
            }
            
            $totalEarnings = $basicSalaryValue + $attendanceBonus;
            $totalDeductions = $absentDeduction + $lateDeduction + $earlyLeaveDeduction + $securityDeduction;
            $netSalary = $totalEarnings - $totalDeductions;
            
            // Generate slip number
            $slipNo = 'SAL-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($employee->id, 6, '0', STR_PAD_LEFT);
            
            // Insert salary slip
            try {
                $insertData = [
                    'user_id' => $employee->id,
                    'campus_id' => $campusId,
                    'slip_no' => $slipNo,
                    'month' => $month,
                    'year' => $year,
                    'basic_salary' => $basicSalaryValue,
                    'daily_salary' => $dailySalary,
                    'working_days_in_month' => $workingDays,
                    'attendance_bonus' => $attendanceBonus,
                    'other_bonus' => 0,
                    'total_earnings' => $totalEarnings,
                    'absent_deduction' => $absentDeduction,
                    'late_deduction' => $lateDeduction,
                    'early_leave_deduction' => $earlyLeaveDeduction,
                    'short_leave_deduction' => 0,
                    'security_deduction' => $securityDeduction,
                    'advance_deduction' => 0,
                    'other_deduction' => 0,
                    'total_deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                    'payment_status' => 'pending',
                    'generated_by' => $generatedBy,
                    'generated_date' => date('Y-m-d H:i:s')
                ];
                
                $result = $db->table('salary_slips')->insert($insertData);
                
                if ($result) {
                    $generated++;
                } else {
                    $errors[] = "Failed to insert slip for {$employee->first_name} {$employee->last_name}";
                }
                
            } catch (\Exception $e) {
                $errors[] = "Error generating slip for {$employee->first_name} {$employee->last_name}: " . $e->getMessage();
                log_message('error', 'Insert error: ' . $e->getMessage());
            }
        }
        
        return [
            'success' => true,
            'generated' => $generated,
            'errors' => $errors,
            'message' => "Generated $generated salary slips" . (count($errors) > 0 ? " with " . count($errors) . " errors" : "")
        ];
    }
        
        /**
         * Get salary summary for dashboard
         */
        public function getSalarySummary($campusId, $year, $month)
        {
            $db = $this->db;
            
            $slips = $db->table('salary_slips')
                ->select('COUNT(*) as total_employees, SUM(net_salary) as total_payable, 
                         SUM(CASE WHEN payment_status = "paid" THEN net_salary ELSE 0 END) as total_paid,
                         SUM(CASE WHEN payment_status = "pending" THEN net_salary ELSE 0 END) as total_pending')
                ->where('campus_id', $campusId)
                ->where('year', $year)
                ->where('month', $month)
                ->get()
                ->getRow();
            
            return $slips;
        }
    }