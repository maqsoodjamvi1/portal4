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

    public function bulkUpdatePaymentStatus(array $slipIds, string $paymentMethod = 'bank'): array
    {
        $db = $this->db;
        $updated = 0;
        $failed = 0;

        foreach ($slipIds as $slipId) {
            $result = $db->table('salary_slips')
                ->where('slip_id', (int) $slipId)
                ->where('payment_status', 'pending')
                ->update([
                    'payment_status' => 'paid',
                    'payment_method' => $paymentMethod,
                    'payment_date' => date('Y-m-d H:i:s'),
                ]);

            if ($result) {
                $updated++;
            } else {
                $failed++;
            }
        }

        return ['updated' => $updated, 'failed' => $failed];
    }

    /**
     * Sum approved bonuses for an employee in a given month.
     */
    public function getMonthlyBonusTotal(int $userId, int $year, int $month): float
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-t', strtotime($start));

        $row = $this->db->table('bonuses')
            ->selectSum('amount', 'total')
            ->where('user_id', $userId)
            ->where('bonus_month >=', $start)
            ->where('bonus_month <=', $end)
            ->get()
            ->getRow();

        return (float) ($row->total ?? 0);
    }

    /**
     * Calculate advance salary deduction and apply repayment to approved advances.
     */
    public function calculateAdvanceDeduction(int $userId): array
    {
        $advances = $this->db->table('advance_salaries')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->where('remaining_amount >', 0)
            ->orderBy('approved_date', 'ASC')
            ->get()
            ->getResult();

        $totalDeduction = 0.0;
        $repayments = [];

        foreach ($advances as $advance) {
            $remaining = (float) $advance->remaining_amount;
            if ($remaining <= 0) {
                continue;
            }

            $deduct = min((float) $advance->monthly_deduction, $remaining);
            if ($deduct <= 0) {
                continue;
            }

            $totalDeduction += $deduct;
            $repayments[] = [
                'advance_id' => (int) $advance->advance_id,
                'amount' => $deduct,
                'new_remaining' => max(0, $remaining - $deduct),
            ];
        }

        return ['total' => $totalDeduction, 'repayments' => $repayments];
    }

    /**
     * Persist advance repayments after a slip is generated.
     */
    public function applyAdvanceRepayments(array $repayments): void
    {
        foreach ($repayments as $item) {
            $this->db->table('advance_salaries')
                ->where('advance_id', $item['advance_id'])
                ->update([
                    'remaining_amount' => $item['new_remaining'],
                    'updated_date' => date('Y-m-d H:i:s'),
                ]);
        }
    }

    /**
     * Calculate attendance deductions from editable day/count inputs.
     *
     * @return array<string, float|int>
     */
    public function calculateDeductionsFromCounts(
        float $basicSalary,
        object $settings,
        ?object $empRules,
        int $offDays,
        int $leaveDays,
        int $lateCount,
        int $earlyLeftCount,
        int $lateMinutesPerOccurrence = 0
    ): array {
        $workingDays = (int) ($settings->working_days_per_month ?? 26);
        $dailySalary = $workingDays > 0 ? $basicSalary / $workingDays : 0;

        if ($empRules && ! empty($empRules->custom_daily_salary) && (float) $empRules->custom_daily_salary > 0) {
            $dailySalary = (float) $empRules->custom_daily_salary;
        }

        $applyDeduction = ! $empRules || (int) ($empRules->apply_deduction ?? 1) !== 0;

        $dayDeduction = static function (int $days) use ($applyDeduction, $settings, $dailySalary, $basicSalary): float {
            if ($days <= 0 || ! $applyDeduction) {
                return 0.0;
            }
            if ($settings->deduction_type === 'per_day_salary') {
                return $days * $dailySalary;
            }
            if ($settings->deduction_type === 'fixed_amount') {
                return $days * (float) ($settings->deduction_per_day_amount ?? 0);
            }
            if ($settings->deduction_type === 'percentage') {
                return $days * ($basicSalary * ((float) ($settings->deduction_per_day_percentage ?? 0) / 100));
            }

            return 0.0;
        };

        $absentDeduction = $dayDeduction($offDays);
        $leaveDeduction = $dayDeduction($leaveDays);

        $lateDeduction = 0.0;
        if (! empty($settings->late_deduction_enabled) && $lateCount > 0) {
            $perMinute = (float) ($settings->late_deduction_amount ?? 0);
            if ($lateMinutesPerOccurrence > 0) {
                $lateDeduction = $lateCount * $lateMinutesPerOccurrence * $perMinute;
            } else {
                $lateDeduction = $lateCount * $perMinute;
            }
        }

        $earlyLeaveDeduction = 0.0;
        if (! empty($settings->early_leave_deduction_enabled) && $earlyLeftCount > 0) {
            $earlyLeaveDeduction = $earlyLeftCount * (float) ($settings->early_leave_deduction_amount ?? 0);
        }

        $totalAttendanceDeductions = $absentDeduction + $leaveDeduction + $lateDeduction + $earlyLeaveDeduction;

        return [
            'absent_deduction' => round($absentDeduction, 2),
            'leave_deduction' => round($leaveDeduction, 2),
            'late_deduction' => round($lateDeduction, 2),
            'early_leave_deduction' => round($earlyLeaveDeduction, 2),
            'total_attendance_deductions' => round($totalAttendanceDeductions, 2),
            'daily_salary' => round($dailySalary, 2),
        ];
    }

    /**
     * Campus rules exported for bulk adjustment UI calculations.
     */
    public function exportBulkCalcSettings(?object $settings): array
    {
        if (! $settings) {
            return [];
        }

        return [
            'deduction_type' => $settings->deduction_type ?? 'per_day_salary',
            'deduction_per_day_amount' => (float) ($settings->deduction_per_day_amount ?? 0),
            'deduction_per_day_percentage' => (float) ($settings->deduction_per_day_percentage ?? 0),
            'late_deduction_enabled' => (int) ($settings->late_deduction_enabled ?? 0),
            'late_deduction_amount' => (float) ($settings->late_deduction_amount ?? 0),
            'early_leave_deduction_enabled' => (int) ($settings->early_leave_deduction_enabled ?? 0),
            'early_leave_deduction_amount' => (float) ($settings->early_leave_deduction_amount ?? 0),
            'working_days_per_month' => (int) ($settings->working_days_per_month ?? 26),
        ];
    }

    /**
     * Compute default attendance-based deductions for one employee/month.
     *
     * @return array<string, float|int>
     */
    public function computeEmployeeDeductionDefaults(object $summary, object $employee, object $settings, ?object $empRules): array
    {
        $basicSalaryValue = (float) ($employee->basic_salary ?? 0);

        $offDays = (int) ($summary->unpaid_leaves ?? 0);
        $leaveDays = (int) ($summary->approved_leaves ?? 0);
        $lateCount = (int) ($summary->late_days ?? 0);
        $earlyLeftCount = (int) ($summary->early_leave_days ?? 0);
        $totalLateMinutes = (int) ($summary->total_late_minutes ?? 0);
        $lateMinutesPerOccurrence = $lateCount > 0 ? (int) round($totalLateMinutes / $lateCount) : 0;

        $fromCounts = $this->calculateDeductionsFromCounts(
            $basicSalaryValue,
            $settings,
            $empRules,
            $offDays,
            $leaveDays,
            $lateCount,
            $earlyLeftCount,
            $lateMinutesPerOccurrence
        );

        $securityDeduction = 0.0;
        if (! empty($settings->security_deduction_enabled) && (! $empRules || ! $empRules->security_deduction_waived)) {
            if ($settings->security_deduction_type === 'fixed_amount') {
                $securityDeduction = (float) ($settings->security_deduction_value ?? 0);
            } elseif ($settings->security_deduction_type === 'percentage') {
                $securityDeduction = $basicSalaryValue * ((float) ($settings->security_deduction_value ?? 0) / 100);
            }
        }

        $advanceResult = $this->calculateAdvanceDeduction((int) $employee->id);
        $advanceDeduction = (float) $advanceResult['total'];

        $dailySalary = (float) $fromCounts['daily_salary'];

        $attendanceBonus = 0.0;
        if (! empty($settings->attendance_bonus_enabled) && (! $empRules || $empRules->bonus_eligible)) {
            if ((int) ($summary->present_days ?? 0) >= (int) ($settings->attendance_bonus_days_required ?? 26)) {
                if ($settings->attendance_bonus_type === 'per_day_salary') {
                    $attendanceBonus = ((int) $summary->present_days - (int) $settings->attendance_bonus_days_required) * $dailySalary;
                } elseif ($settings->attendance_bonus_type === 'fixed_amount') {
                    $attendanceBonus = (float) ($settings->attendance_bonus_amount ?? 0);
                }
            }
        }

        $year = (int) ($summary->year ?? date('Y'));
        $month = (int) ($summary->month ?? date('n'));
        $otherBonus = $this->getMonthlyBonusTotal((int) $employee->id, $year, $month);

        return [
            'off_days' => $offDays,
            'leave_days' => $leaveDays,
            'late_count' => $lateCount,
            'early_left_count' => $earlyLeftCount,
            'late_minutes_per_occurrence' => $lateMinutesPerOccurrence,
            'apply_deduction' => ! $empRules || (int) ($empRules->apply_deduction ?? 1) !== 0 ? 1 : 0,
            'absent_deduction' => $fromCounts['absent_deduction'],
            'leave_deduction' => $fromCounts['leave_deduction'],
            'late_deduction' => $fromCounts['late_deduction'],
            'early_leave_deduction' => $fromCounts['early_leave_deduction'],
            'total_attendance_deductions' => $fromCounts['total_attendance_deductions'],
            'security_deduction' => round($securityDeduction, 2),
            'advance_deduction' => round($advanceDeduction, 2),
            'attendance_bonus' => round($attendanceBonus, 2),
            'other_bonus' => round($otherBonus, 2),
            'daily_salary' => $fromCounts['daily_salary'],
            'advance_repayments' => $advanceResult['repayments'],
        ];
    }

    /**
     * Build bulk salary adjustment rows for a campus/month (refreshes attendance summary).
     */
    public function getBulkAdjustmentRows(int $campusId, int $year, int $month): array
    {
        $this->generateAttendanceSummary($campusId, $year, $month);

        $settings = $this->getCampusSettings($campusId);
        if (! $settings) {
            return ['success' => false, 'message' => 'Campus salary settings not configured', 'rows' => []];
        }

        $summaries = $this->db->table('monthly_attendance_summary mas')
            ->select('mas.*, users.first_name, users.last_name, users.basic_salary, users.designation')
            ->join('users', 'users.id = mas.user_id')
            ->where('mas.campus_id', $campusId)
            ->where('mas.year', $year)
            ->where('mas.month', $month)
            ->where('users.status', 1)
            ->orderBy('users.first_name', 'ASC')
            ->orderBy('users.last_name', 'ASC')
            ->get()
            ->getResult();

        $rows = [];
        foreach ($summaries as $summary) {
            if ((float) ($summary->basic_salary ?? 0) <= 0) {
                continue;
            }

            $empRules = $this->getEmployeeRules((int) $summary->user_id);
            $employee = (object) [
                'id' => (int) $summary->user_id,
                'basic_salary' => (float) $summary->basic_salary,
                'first_name' => $summary->first_name,
                'last_name' => $summary->last_name,
            ];

            $defaults = $this->computeEmployeeDeductionDefaults($summary, $employee, $settings, $empRules);

            $existingSlip = $this->db->table('salary_slips')
                ->select('slip_id, payment_status')
                ->where('user_id', $summary->user_id)
                ->where('year', $year)
                ->where('month', $month)
                ->get()
                ->getRow();

            $totalDeductions = $defaults['total_attendance_deductions']
                + $defaults['security_deduction']
                + $defaults['advance_deduction'];

            $totalEarnings = (float) $summary->basic_salary + $defaults['attendance_bonus'] + $defaults['other_bonus'];

            $rows[] = [
                'user_id' => (int) $summary->user_id,
                'name' => trim($summary->first_name . ' ' . $summary->last_name),
                'designation' => $summary->designation ?? '',
                'basic_salary' => (float) $summary->basic_salary,
                'off_days' => $defaults['off_days'],
                'leave_days' => $defaults['leave_days'],
                'late_count' => $defaults['late_count'],
                'early_left_count' => $defaults['early_left_count'],
                'late_minutes_per_occurrence' => $defaults['late_minutes_per_occurrence'],
                'apply_deduction' => $defaults['apply_deduction'],
                'daily_salary' => $defaults['daily_salary'],
                'total_attendance_deductions' => $defaults['total_attendance_deductions'],
                'total_full_deductions' => round($totalDeductions, 2),
                'security_deduction' => $defaults['security_deduction'],
                'advance_deduction' => $defaults['advance_deduction'],
                'attendance_bonus' => $defaults['attendance_bonus'],
                'other_bonus' => $defaults['other_bonus'],
                'net_salary' => round($totalEarnings - $totalDeductions, 2),
                'has_existing_slip' => (bool) $existingSlip,
                'existing_slip_id' => $existingSlip->slip_id ?? null,
                'existing_payment_status' => $existingSlip->payment_status ?? null,
            ];
        }

        return [
            'success' => true,
            'rows' => $rows,
            'calc_settings' => $this->exportBulkCalcSettings($settings),
        ];
    }

    /**
     * Restore advance balance when a pending slip is deleted for regeneration.
     */
    public function restoreAdvanceDeductionForUser(int $userId, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $advances = $this->db->table('advance_salaries')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->orderBy('approved_date', 'DESC')
            ->get()
            ->getResult();

        $left = $amount;
        foreach ($advances as $adv) {
            $maxRestore = (float) $adv->amount - (float) $adv->remaining_amount;
            if ($maxRestore <= 0) {
                continue;
            }
            $restore = min($left, $maxRestore);
            $this->db->table('advance_salaries')
                ->where('advance_id', $adv->advance_id)
                ->update([
                    'remaining_amount' => (float) $adv->remaining_amount + $restore,
                    'updated_date' => date('Y-m-d H:i:s'),
                ]);
            $left -= $restore;
            if ($left <= 0) {
                break;
            }
        }
    }

    /**
     * Delete a pending slip so it can be regenerated.
     */
    public function deletePendingSlip(object $slip): bool
    {
        if (($slip->payment_status ?? '') === 'paid') {
            return false;
        }

        $this->restoreAdvanceDeductionForUser((int) $slip->user_id, (float) ($slip->advance_deduction ?? 0));

        return (bool) $this->db->table('salary_slips')
            ->where('slip_id', (int) $slip->slip_id)
            ->where('payment_status !=', 'paid')
            ->delete();
    }

    /**
     * Salary slips for a campus/month (print/export).
     */
    public function getMonthSlipsDetailed(int $campusId, int $year, int $month): array
    {
        return $this->db->table('salary_slips ss')
            ->select('ss.*, users.first_name, users.last_name, users.designation, users.bank_account')
            ->join('users', 'users.id = ss.user_id')
            ->where('ss.campus_id', $campusId)
            ->where('ss.year', $year)
            ->where('ss.month', $month)
            ->orderBy('users.first_name', 'ASC')
            ->orderBy('users.last_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Monthly slip totals for summary cards.
     */
    public function getMonthSlipTotals(int $campusId, int $year, int $month): ?object
    {
        return $this->db->table('salary_slips')
            ->select('COUNT(*) as slip_count,
                SUM(basic_salary) as total_basic,
                SUM(total_deductions) as total_deductions,
                SUM(net_salary) as total_net')
            ->where('campus_id', $campusId)
            ->where('year', $year)
            ->where('month', $month)
            ->get()
            ->getRow();
    }
    /**
     * Generate salary slips for selected employees with admin-edited deductions.
     *
     * @param list<array<string, mixed>> $items
     */
    public function generateSalarySlipsWithOverrides(
        int $campusId,
        int $year,
        int $month,
        int $generatedBy,
        array $items,
        bool $regenerate = false
    ): array {
        $settings = $this->getCampusSettings($campusId);
        if (! $settings) {
            return [
                'success' => false,
                'message' => 'Campus salary settings not configured',
                'generated' => 0,
                'skipped' => 0,
                'regenerated' => 0,
                'errors' => ['Campus salary settings not configured'],
            ];
        }

        $generated = 0;
        $skipped = 0;
        $regenerated = 0;
        $errors = [];

        foreach ($items as $item) {
            $userId = (int) ($item['user_id'] ?? 0);
            if ($userId <= 0) {
                continue;
            }

            $existing = $this->db->table('salary_slips')
                ->where('user_id', $userId)
                ->where('year', $year)
                ->where('month', $month)
                ->get()
                ->getRow();

            if ($existing) {
                if (($existing->payment_status ?? '') === 'paid') {
                    $skipped++;
                    $errors[] = "Paid slip exists for employee ID {$userId} — cannot regenerate";
                    continue;
                }
                if ($regenerate) {
                    if (! $this->deletePendingSlip($existing)) {
                        $skipped++;
                        $errors[] = "Could not delete pending slip for employee ID {$userId}";
                        continue;
                    }
                    $regenerated++;
                } else {
                    $skipped++;
                    $errors[] = "Slip already exists for employee ID {$userId}";
                    continue;
                }
            }

            $summary = $this->db->table('monthly_attendance_summary')
                ->where('campus_id', $campusId)
                ->where('user_id', $userId)
                ->where('year', $year)
                ->where('month', $month)
                ->get()
                ->getRow();

            if (! $summary) {
                $errors[] = "No attendance summary for employee ID {$userId}";
                continue;
            }

            $employee = $this->db->table('users')
                ->select('id, basic_salary, first_name, last_name')
                ->where('id', $userId)
                ->get()
                ->getRow();

            if (! $employee || (float) ($employee->basic_salary ?? 0) <= 0) {
                $errors[] = "Employee ID {$userId} has no basic salary";
                continue;
            }

            $empRules = $this->getEmployeeRules($userId);
            $defaults = $this->computeEmployeeDeductionDefaults($summary, $employee, $settings, $empRules);

            $offDays = (int) ($item['off_days'] ?? $defaults['off_days']);
            $leaveDays = (int) ($item['leave_days'] ?? $defaults['leave_days']);
            $lateCount = (int) ($item['late_count'] ?? $defaults['late_count']);
            $earlyLeftCount = (int) ($item['early_left_count'] ?? $defaults['early_left_count']);
            $lateMinutesPerOccurrence = (int) ($item['late_minutes_per_occurrence'] ?? $defaults['late_minutes_per_occurrence']);

            $fromCounts = $this->calculateDeductionsFromCounts(
                (float) $employee->basic_salary,
                $settings,
                $empRules,
                $offDays,
                $leaveDays,
                $lateCount,
                $earlyLeftCount,
                $lateMinutesPerOccurrence
            );

            $absentDeduction = (float) $fromCounts['absent_deduction'];
            $leaveDeduction = (float) $fromCounts['leave_deduction'];
            $lateDeduction = (float) $fromCounts['late_deduction'];
            $earlyLeaveDeduction = (float) $fromCounts['early_leave_deduction'];
            $securityDeduction = (float) ($defaults['security_deduction'] ?? 0);
            $advanceDeduction = (float) ($defaults['advance_deduction'] ?? 0);
            $attendanceBonus = (float) ($defaults['attendance_bonus'] ?? 0);
            $otherBonus = (float) ($defaults['other_bonus'] ?? 0);
            $dailySalary = (float) ($fromCounts['daily_salary'] ?? 0);
            $workingDays = (int) ($settings->working_days_per_month ?? 26);
            $basicSalaryValue = (float) $employee->basic_salary;

            $totalEarnings = $basicSalaryValue + $attendanceBonus + $otherBonus;
            $totalDeductions = $absentDeduction + $leaveDeduction + $lateDeduction + $earlyLeaveDeduction
                + $securityDeduction + $advanceDeduction;
            $netSalary = $totalEarnings - $totalDeductions;

            $slipNo = 'SAL-' . $year . '-' . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . '-'
                . str_pad((string) $employee->id, 6, '0', STR_PAD_LEFT);

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
                    'other_bonus' => $otherBonus,
                    'total_earnings' => $totalEarnings,
                    'absent_deduction' => $absentDeduction,
                    'late_deduction' => $lateDeduction,
                    'early_leave_deduction' => $earlyLeaveDeduction,
                    'short_leave_deduction' => 0,
                    'security_deduction' => $securityDeduction,
                    'advance_deduction' => $advanceDeduction,
                    'other_deduction' => $leaveDeduction,
                    'total_deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                    'payment_status' => 'pending',
                    'generated_by' => $generatedBy,
                    'generated_date' => date('Y-m-d H:i:s'),
                ];

                if ($this->db->table('salary_slips')->insert($insertData)) {
                    $generated++;
                    if ($advanceDeduction > 0 && ! empty($defaults['advance_repayments'])) {
                        $this->applyAdvanceRepayments($defaults['advance_repayments']);
                    }
                } else {
                    $errors[] = "Failed to insert slip for {$employee->first_name} {$employee->last_name}";
                }
            } catch (\Exception $e) {
                $errors[] = "Error for {$employee->first_name} {$employee->last_name}: " . $e->getMessage();
            }
        }

        $msg = "Generated {$generated} salary slip(s)";
        if ($regenerated > 0) {
            $msg .= ", regenerated {$regenerated}";
        }
        if ($skipped > 0) {
            $msg .= ", skipped {$skipped}";
        }

        return [
            'success' => true,
            'generated' => $generated,
            'skipped' => $skipped,
            'regenerated' => $regenerated,
            'errors' => $errors,
            'message' => $msg,
        ];
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
            if ($empRules && !empty($empRules->custom_daily_salary) && (float) $empRules->custom_daily_salary > 0) {
                $dailySalary = (float) $empRules->custom_daily_salary;
            }

            // Calculate deductions
            $absentDeduction = 0;
            $lateDeduction = 0;
            $earlyLeaveDeduction = 0;
            $securityDeduction = 0;
            $advanceDeduction = 0;

            // Absent deduction
            if ($summary->unpaid_leaves > 0 && (!$empRules || $empRules->apply_deduction != 0)) {
                if ($settings->deduction_type == 'per_day_salary') {
                    $absentDeduction = $summary->unpaid_leaves * $dailySalary;
                } elseif ($settings->deduction_type == 'fixed_amount') {
                    $absentDeduction = $summary->unpaid_leaves * ($settings->deduction_per_day_amount ?? 0);
                } elseif ($settings->deduction_type == 'percentage') {
                    $absentDeduction = $summary->unpaid_leaves * ($basicSalaryValue * (($settings->deduction_per_day_percentage ?? 0) / 100));
                }
            }

            // Late deduction
            if ($settings->late_deduction_enabled && $summary->total_late_minutes > 0) {
                $lateDeduction = $summary->total_late_minutes * ($settings->late_deduction_amount ?? 0);
            }

            // Early leave deduction
            if (!empty($settings->early_leave_deduction_enabled) && $summary->early_leave_days > 0) {
                $earlyLeaveDeduction = $summary->early_leave_days * ($settings->early_leave_deduction_amount ?? 0);
            }

            // Security deduction
            if ($settings->security_deduction_enabled && (!$empRules || !$empRules->security_deduction_waived)) {
                if ($settings->security_deduction_type == 'fixed_amount') {
                    $securityDeduction = $settings->security_deduction_value ?? 0;
                } elseif ($settings->security_deduction_type == 'percentage') {
                    $securityDeduction = $basicSalaryValue * (($settings->security_deduction_value ?? 0) / 100);
                }
            }

            // Advance salary deduction
            $advanceResult = $this->calculateAdvanceDeduction((int) $employee->id);
            $advanceDeduction = $advanceResult['total'];

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

            $otherBonus = $this->getMonthlyBonusTotal((int) $employee->id, (int) $year, (int) $month);

            $totalEarnings = $basicSalaryValue + $attendanceBonus + $otherBonus;
            $totalDeductions = $absentDeduction + $lateDeduction + $earlyLeaveDeduction + $securityDeduction + $advanceDeduction;
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
                    'other_bonus' => $otherBonus,
                    'total_earnings' => $totalEarnings,
                    'absent_deduction' => $absentDeduction,
                    'late_deduction' => $lateDeduction,
                    'early_leave_deduction' => $earlyLeaveDeduction,
                    'short_leave_deduction' => 0,
                    'security_deduction' => $securityDeduction,
                    'advance_deduction' => $advanceDeduction,
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
                    if ($advanceDeduction > 0 && !empty($advanceResult['repayments'])) {
                        $this->applyAdvanceRepayments($advanceResult['repayments']);
                    }
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
