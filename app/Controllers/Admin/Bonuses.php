<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Bonuses extends BaseController
{
    protected $db;
    protected $session;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
        helper(['form', 'url', 'alert']);
    }
    
    public function index()
    {
        $campusId = $this->session->get('member_campusid');
        
        // Get all bonuses with employee details
        $bonuses = $this->db->table('bonuses')
            ->select('bonuses.*, users.first_name, users.last_name, users.designation')
            ->join('users', 'users.id = bonuses.user_id')
            ->where('bonuses.campus_id', $campusId)
            ->orderBy('bonuses.created_date', 'DESC')
            ->get()
            ->getResult();
        
        // Get employees for dropdown
        $employees = $this->db->table('users')
            ->select('id, first_name, last_name, basic_salary')
            ->where('campus_id', $campusId)
            ->where('status', 1)
            ->orderBy('first_name')
            ->get()
            ->getResult();
        
        return view('admin/bonuses', [
            'bonuses' => $bonuses,
            'employees' => $employees
        ]);
    }
    
    public function add()
    {
        $userId = $this->request->getPost('user_id');
        $amount = $this->request->getPost('amount');
        $bonusType = $this->request->getPost('bonus_type');
        $reason = $this->request->getPost('reason');
        $bonusMonth = $this->request->getPost('bonus_month');
        
        if (!$userId || !$amount) {
            return redirect()->back()->with('error', 'Please select employee and enter amount');
        }
        
        // Insert bonus record
        $this->db->table('bonuses')->insert([
            'user_id' => $userId,
            'campus_id' => $this->session->get('member_campusid'),
            'bonus_type' => $bonusType,
            'amount' => $amount,
            'bonus_month' => $bonusMonth ?: date('Y-m-01'),
            'reason' => $reason,
            'approved_by' => $this->session->get('member_userid'),
            'created_date' => date('Y-m-d H:i:s')
        ]);
        
        return redirect()->to('admin/bonuses')
            ->with('success', 'Bonus added successfully');
    }
    
    public function delete($bonusId = null)
    {
        if (!$bonusId) {
            return redirect()->to('admin/bonuses')->with('error', 'Invalid bonus ID');
        }
        
        $this->db->table('bonuses')
            ->where('bonus_id', $bonusId)
            ->delete();
        
        return redirect()->to('admin/bonuses')
            ->with('success', 'Bonus deleted successfully');
    }
    
    public function getEmployeeBonus($userId)
    {
        $bonuses = $this->db->table('bonuses')
            ->where('user_id', $userId)
            ->orderBy('created_date', 'DESC')
            ->get()
            ->getResult();
        
        return $this->response->setJSON($bonuses);
    }
}