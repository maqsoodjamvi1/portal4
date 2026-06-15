<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SalaryDebug extends BaseController
{
    protected $db;
    protected $salaryModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->salaryModel = new \App\Models\SalaryModel();
    }

    public function index()
    {
        $campusId = 1; // Change to your campus ID
        $year = 2026;
        $month = 3;

        echo "<h2>Salary Generation Debug</h2>";

        // Check employees with basic salary
        $employees = $this->db->table('users')
            ->select('id, first_name, last_name, basic_salary, status')
            ->where('campus_id', $campusId)
            ->where('status', 1)
            ->get()
            ->getResult();

        echo "<h3>Employees with Salary:</h3>";
        echo "<pre>";
        foreach ($employees as $emp) {
            echo "ID: {$emp->id}, Name: {$emp->first_name} {$emp->last_name}, Salary: {$emp->basic_salary}\n";
        }
        echo "</pre>";

        // Check attendance for first employee
        if (!empty($employees)) {
            $attendance = $this->db->table('attendance_employee')
                ->where('emp_id', $employees[0]->id)
                ->where('MONTH(date)', $month)
                ->where('YEAR(date)', $year)
                ->get()
                ->getResult();

            echo "<h3>Attendance for {$employees[0]->first_name}:</h3>";
            echo "<pre>";
            foreach ($attendance as $att) {
                echo "Date: {$att->date}, Status: {$att->status}, Checkin: {$att->checkin}\n";
            }
            echo "</pre>";
        }

        // Generate attendance summary
        echo "<h3>Generating Attendance Summary...</h3>";
        $this->salaryModel->generateAttendanceSummary($campusId, $year, $month);

        // Check summary
        $summaries = $this->db->table('monthly_attendance_summary')
            ->where('campus_id', $campusId)
            ->where('year', $year)
            ->where('month', $month)
            ->get()
            ->getResult();

        echo "<h3>Attendance Summary Generated:</h3>";
        echo "<pre>";
        foreach ($summaries as $summary) {
            echo "User: {$summary->user_id}, Present: {$summary->present_days}, Unpaid Leaves: {$summary->unpaid_leaves}\n";
        }
        echo "</pre>";

        // Generate salary slips
        echo "<h3>Generating Salary Slips...</h3>";
        $result = $this->salaryModel->generateSalarySlips($campusId, $year, $month, 1);

        echo "<h3>Result:</h3>";
        echo "<pre>";
        print_r($result);
        echo "</pre>";

        // Check slips
        $slips = $this->db->table('salary_slips')
            ->where('campus_id', $campusId)
            ->where('year', $year)
            ->where('month', $month)
            ->get()
            ->getResult();

        echo "<h3>Salary Slips Generated:</h3>";
        echo "<pre>";
        foreach ($slips as $slip) {
            echo "Slip: {$slip->slip_no}, Net Salary: {$slip->net_salary}\n";
        }
        echo "</pre>";
    }
}
