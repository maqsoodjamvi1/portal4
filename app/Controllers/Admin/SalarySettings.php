<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SalaryModel;

class SalarySettings extends BaseController
{
    protected $salaryModel;
    protected $db;
    protected $session;
    
    public function __construct()
    {
        // Initialize database and session
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
        
        // Initialize the model
        $this->salaryModel = new SalaryModel();
        
        // Load helpers
        helper(['form', 'url']);
    }
    
    public function index()
    {
        $campusId = $this->session->get('member_campusid');
        $settings = $this->salaryModel->getCampusSettings($campusId);
        
        return view('admin/salary_settings', [
            'settings' => $settings
        ]);
    }
    
   public function save()
{
    $campusId = $this->session->get('member_campusid');
    
    // Debug: Log the session campus ID
    log_message('debug', 'Campus ID from session: ' . $campusId);
    
    // Validation rules
    $rules = [
        'deduction_type' => 'required|in_list[per_day_salary,fixed_amount,percentage]',
        'working_days_per_month' => 'permit_empty|numeric|greater_than[0]|less_than[32]',
        'late_grace_minutes' => 'permit_empty|numeric',
    ];
    
    if (!$this->validate($rules)) {
        $errors = $this->validator->getErrors();
        log_message('error', 'Validation errors: ' . json_encode($errors));
        return redirect()->to('admin/salary-settings')
            ->with('error', 'Invalid data: ' . implode(', ', $errors));
    }
    
    $data = [
        'deduction_type' => $this->request->getPost('deduction_type'),
        'deduction_per_day_amount' => $this->request->getPost('deduction_per_day_amount') ?: null,
        'deduction_per_day_percentage' => $this->request->getPost('deduction_per_day_percentage') ?: null,
        'late_deduction_enabled' => $this->request->getPost('late_deduction_enabled') ? 1 : 0,
        'late_deduction_amount' => $this->request->getPost('late_deduction_amount') ?: null,
        'late_grace_minutes' => $this->request->getPost('late_grace_minutes') ?: 5,
        'early_leave_deduction_enabled' => $this->request->getPost('early_leave_deduction_enabled') ? 1 : 0,
        'early_leave_deduction_amount' => $this->request->getPost('early_leave_deduction_amount') ?: null,
        'attendance_bonus_enabled' => $this->request->getPost('attendance_bonus_enabled') ? 1 : 0,
        'attendance_bonus_days_required' => $this->request->getPost('attendance_bonus_days_required') ?: 26,
        'attendance_bonus_type' => $this->request->getPost('attendance_bonus_type'),
        'attendance_bonus_amount' => $this->request->getPost('attendance_bonus_amount') ?: null,
        'security_deduction_enabled' => $this->request->getPost('security_deduction_enabled') ? 1 : 0,
        'security_deduction_type' => $this->request->getPost('security_deduction_type'),
        'security_deduction_value' => $this->request->getPost('security_deduction_value') ?: null,
        'working_days_per_month' => $this->request->getPost('working_days_per_month') ?: 26
    ];
    
    // Debug: Log the data being saved
    log_message('debug', 'Data to save: ' . json_encode($data));
    
    $result = $this->salaryModel->saveCampusSettings(
        $campusId,
        $data,
        $this->session->get('member_userid')
    );
    
    // Debug: Log the result
    log_message('debug', 'Save result: ' . ($result ? 'Success (ID: ' . $result . ')' : 'Failed'));
    
    if ($result) {
        return redirect()->to('admin/salary-settings')
            ->with('success', 'Salary settings saved successfully');
    } else {
        return redirect()->to('admin/salary-settings')
            ->with('error', 'Failed to save salary settings');
    }
}
 public function generateMonthly()
{
    $campusId = $this->session->get('member_campusid');
    $year = $this->request->getPost('year');
    $month = $this->request->getPost('month');
    
    if (!$year || !$month) {
        return redirect()->back()->with('error', 'Please select year and month');
    }
    
    // First generate attendance summary
    try {
        $this->salaryModel->generateAttendanceSummary($campusId, $year, $month);
    } catch (\Exception $e) {
        return redirect()->to('admin/salary-settings')
            ->with('error', 'Failed to generate attendance summary: ' . $e->getMessage());
    }
    
    // Then generate salary slips
    $result = $this->salaryModel->generateSalarySlips(
        $campusId,
        $year,
        $month,
        $this->session->get('member_userid')
    );
    
    if ($result['success']) {
        $message = "Generated {$result['generated']} salary slips for " . 
                   date('F Y', strtotime("$year-$month-01"));
        
        if (!empty($result['errors'])) {
            $message .= "<br><br><strong>Warnings/Errors:</strong><br>" . 
                        implode('<br>', array_slice($result['errors'], 0, 10));
            if (count($result['errors']) > 10) {
                $message .= "<br>... and " . (count($result['errors']) - 10) . " more errors";
            }
        }
        
        return redirect()->to('admin/salary-settings')
            ->with('success', $message);
    } else {
        $errorMsg = $result['message'] ?? 'Failed to generate salary slips';
        if (!empty($result['errors'])) {
            $errorMsg .= "<br><br>" . implode('<br>', array_slice($result['errors'], 0, 5));
        }
        
        return redirect()->to('admin/salary-settings')
            ->with('error', $errorMsg);
    }
}

    
   public function reports()
{
    $campusId = $this->session->get('member_campusid');
    $year = $this->request->getGet('year') ?? date('Y');
    $month = $this->request->getGet('month') ?? date('m');
    
    // Get summary
    $summary = $this->salaryModel->getSalarySummary($campusId, $year, $month);
    
    // Get detailed salary slips with employee info
    $salarySlips = $this->db->table('salary_slips')
        ->select('salary_slips.*, users.first_name, users.last_name, users.designation')
        ->join('users', 'users.id = salary_slips.user_id')
        ->where('salary_slips.campus_id', $campusId)
        ->where('salary_slips.year', $year)
        ->where('salary_slips.month', $month)
        ->orderBy('users.first_name', 'ASC')
        ->get()
        ->getResult();
    
    return view('admin/salary_reports', [
        'summary' => $summary,
        'salarySlips' => $salarySlips,
        'year' => $year,
        'month' => $month
    ]);
}
}