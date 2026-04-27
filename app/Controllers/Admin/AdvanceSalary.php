<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SalaryModel;

class AdvanceSalary extends BaseController
{
    protected $db;
    protected $session;
    protected $salaryModel;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
        $this->salaryModel = new SalaryModel();
        helper(['form', 'url']);
    }
    
    public function index()
    {
        $campusId = $this->session->get('member_campusid');
        
        // Get all advance salary requests with employee details
        $advances = $this->db->table('advance_salaries')
            ->select('advance_salaries.*, users.first_name, users.last_name, users.designation')
            ->join('users', 'users.id = advance_salaries.user_id')
            ->where('advance_salaries.campus_id', $campusId)
            ->orderBy('advance_salaries.request_date', 'DESC')
            ->get()
            ->getResult();
        
        // Get employees for dropdown
        $employees = $this->db->table('users')
            ->select('id, first_name, last_name, basic_salary')
            ->where('campus_id', $campusId)
            ->where('status', 1)
            ->where('basic_salary >', 0)
            ->orderBy('first_name')
            ->get()
            ->getResult();
        
        return view('admin/advance_salary', [
            'advances' => $advances,
            'employees' => $employees
        ]);
    }
    
    public function request()
    {
        $userId = $this->request->getPost('user_id');
        $amount = $this->request->getPost('amount');
        $reason = $this->request->getPost('reason');
        $repaymentMonths = $this->request->getPost('repayment_months') ?: 3;
        
        if (!$userId || !$amount) {
            return redirect()->back()->with('error', 'Please select employee and enter amount');
        }
        
        // Check if there's already a pending request
        $existing = $this->db->table('advance_salaries')
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->get()
            ->getRow();
        
        if ($existing) {
            return redirect()->back()->with('error', 'There is already a pending advance request for this employee');
        }
        
        $monthlyDeduction = $amount / $repaymentMonths;
        
        $this->db->table('advance_salaries')->insert([
            'user_id' => $userId,
            'campus_id' => $this->session->get('member_campusid'),
            'amount' => $amount,
            'request_date' => date('Y-m-d'),
            'status' => 'pending',
            'repayment_months' => $repaymentMonths,
            'monthly_deduction' => $monthlyDeduction,
            'remaining_amount' => $amount,
            'reason' => $reason,
            'created_date' => date('Y-m-d H:i:s')
        ]);
        
        return redirect()->to('admin/advance-salary')
            ->with('success', 'Advance salary request submitted successfully');
    }
    
    public function approve($advanceId = null)
    {
        // Debug: Log the request
        log_message('info', 'Advance approve called with ID: ' . $advanceId);
        
        if (!$advanceId) {
            log_message('error', 'No advance ID provided');
            return redirect()->to('admin/advance-salary')->with('error', 'Invalid advance request');
        }
        
        $advance = $this->db->table('advance_salaries')
            ->where('advance_id', $advanceId)
            ->get()
            ->getRow();
        
        if (!$advance) {
            log_message('error', 'Advance request not found for ID: ' . $advanceId);
            return redirect()->to('admin/advance-salary')->with('error', 'Advance request not found');
        }
        
        if ($advance->status != 'pending') {
            log_message('info', 'Advance request already processed: ' . $advance->status);
            return redirect()->to('admin/advance-salary')->with('warning', 'This request has already been ' . $advance->status);
        }
        
        try {
            $this->db->table('advance_salaries')
                ->where('advance_id', $advanceId)
                ->update([
                    'status' => 'approved',
                    'approved_date' => date('Y-m-d'),
                    'approved_by' => $this->session->get('member_userid'),
                    'updated_date' => date('Y-m-d H:i:s')
                ]);
            
            log_message('info', 'Advance request approved successfully: ' . $advanceId);
            return redirect()->to('admin/advance-salary')->with('success', 'Advance salary request approved successfully');
            
        } catch (\Exception $e) {
            log_message('error', 'Error approving advance: ' . $e->getMessage());
            return redirect()->to('admin/advance-salary')->with('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }
    
    public function reject($advanceId = null)
    {
        if (!$advanceId) {
            return redirect()->to('admin/advance-salary')->with('error', 'Invalid advance request');
        }
        
        $advance = $this->db->table('advance_salaries')
            ->where('advance_id', $advanceId)
            ->get()
            ->getRow();
        
        if (!$advance) {
            return redirect()->to('admin/advance-salary')->with('error', 'Advance request not found');
        }
        
        if ($advance->status != 'pending') {
            return redirect()->to('admin/advance-salary')->with('warning', 'This request has already been processed');
        }
        
        $this->db->table('advance_salaries')
            ->where('advance_id', $advanceId)
            ->update([
                'status' => 'rejected',
                'updated_date' => date('Y-m-d H:i:s')
            ]);
        
        return redirect()->to('admin/advance-salary')
            ->with('success', 'Advance salary request rejected');
    }
    
    public function details($advanceId)
    {
        $advance = $this->db->table('advance_salaries')
            ->select('advance_salaries.*, users.first_name, users.last_name, users.designation, users.basic_salary')
            ->join('users', 'users.id = advance_salaries.user_id')
            ->where('advance_salaries.advance_id', $advanceId)
            ->get()
            ->getRow();
        
        if (!$advance) {
            return $this->response->setJSON(['error' => 'Not found'], 404);
        }
        
        return $this->response->setJSON($advance);
    }
}