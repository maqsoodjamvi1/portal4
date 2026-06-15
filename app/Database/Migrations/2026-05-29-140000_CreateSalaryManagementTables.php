<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Payroll / salary management tables used by SalaryModel and related admin controllers.
 * Uses IF NOT EXISTS so existing legacy databases are not altered.
 */
class CreateSalaryManagementTables extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('campus_salary_settings')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'campus_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'deduction_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 32,
                    'default'    => 'per_day_salary',
                ],
                'deduction_per_day_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
                'deduction_per_day_percentage' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'null' => true],
                'late_deduction_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'late_deduction_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
                'late_grace_minutes' => ['type' => 'INT', 'constraint' => 11, 'default' => 5],
                'late_threshold' => ['type' => 'TIME', 'null' => true],
                'early_leave_deduction_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'early_leave_deduction_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
                'attendance_bonus_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'attendance_bonus_days_required' => ['type' => 'INT', 'constraint' => 11, 'default' => 26],
                'attendance_bonus_type' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
                'attendance_bonus_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
                'security_deduction_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'security_deduction_type' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
                'security_deduction_value' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
                'working_days_per_month' => ['type' => 'INT', 'constraint' => 11, 'default' => 26],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
                'updated_date' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('campus_id');
            $this->forge->createTable('campus_salary_settings', true);
        }

        if (! $this->db->tableExists('employee_salary_rules')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'campus_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'apply_deduction' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'custom_daily_salary' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
                'security_deduction_waived' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'bonus_eligible' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'notes' => ['type' => 'TEXT', 'null' => true],
                'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
                'updated_date' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('user_id');
            $this->forge->createTable('employee_salary_rules', true);
        }

        if (! $this->db->tableExists('salary_history')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'campus_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'old_basic_salary' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'new_basic_salary' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'increment_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'increment_percentage' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => 0],
                'increment_date' => ['type' => 'DATE', 'null' => true],
                'increment_reason' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'approved_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['user_id', 'increment_date']);
            $this->forge->createTable('salary_history', true);
        }

        if (! $this->db->tableExists('monthly_attendance_summary')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'campus_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'year' => ['type' => 'INT', 'constraint' => 4],
                'month' => ['type' => 'INT', 'constraint' => 2],
                'total_working_days' => ['type' => 'INT', 'constraint' => 11, 'default' => 26],
                'present_days' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'absent_days' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'late_days' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'early_leave_days' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'approved_leaves' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'unpaid_leaves' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'total_late_minutes' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'generated_date' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['campus_id', 'year', 'month']);
            $this->forge->addKey(['user_id', 'year', 'month']);
            $this->forge->createTable('monthly_attendance_summary', true);
        }

        if (! $this->db->tableExists('salary_slips')) {
            $this->forge->addField([
                'slip_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'campus_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'slip_no' => ['type' => 'VARCHAR', 'constraint' => 64],
                'month' => ['type' => 'INT', 'constraint' => 2],
                'year' => ['type' => 'INT', 'constraint' => 4],
                'basic_salary' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'daily_salary' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'working_days_in_month' => ['type' => 'INT', 'constraint' => 11, 'default' => 26],
                'attendance_bonus' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'other_bonus' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'total_earnings' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'absent_deduction' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'late_deduction' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'early_leave_deduction' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'short_leave_deduction' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'security_deduction' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'advance_deduction' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'other_deduction' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'total_deductions' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'net_salary' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'payment_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
                'payment_date' => ['type' => 'DATETIME', 'null' => true],
                'payment_method' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
                'transaction_id' => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => true],
                'remarks' => ['type' => 'TEXT', 'null' => true],
                'generated_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'generated_date' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('slip_id', true);
            $this->forge->addKey(['user_id', 'year', 'month']);
            $this->forge->addKey(['campus_id', 'year', 'month']);
            $this->forge->createTable('salary_slips', true);
        }

        if (! $this->db->tableExists('advance_salaries')) {
            $this->forge->addField([
                'advance_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'campus_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'request_date' => ['type' => 'DATE', 'null' => true],
                'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
                'repayment_months' => ['type' => 'INT', 'constraint' => 11, 'default' => 3],
                'monthly_deduction' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'remaining_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'reason' => ['type' => 'TEXT', 'null' => true],
                'approved_date' => ['type' => 'DATE', 'null' => true],
                'approved_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
                'updated_date' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('advance_id', true);
            $this->forge->addKey(['user_id', 'status']);
            $this->forge->createTable('advance_salaries', true);
        }

        if (! $this->db->tableExists('bonuses')) {
            $this->forge->addField([
                'bonus_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'campus_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'bonus_type' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
                'amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'bonus_month' => ['type' => 'DATE', 'null' => true],
                'reason' => ['type' => 'TEXT', 'null' => true],
                'approved_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_date' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('bonus_id', true);
            $this->forge->addKey(['user_id', 'bonus_month']);
            $this->forge->createTable('bonuses', true);
        }
    }

    public function down(): void
    {
        $tables = [
            'bonuses',
            'advance_salaries',
            'salary_slips',
            'monthly_attendance_summary',
            'salary_history',
            'employee_salary_rules',
            'campus_salary_settings',
        ];

        foreach ($tables as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }
    }
}
