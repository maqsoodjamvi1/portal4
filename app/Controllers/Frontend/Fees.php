<?php
namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use Config\Database;

class Fees extends BaseController
{
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->session = session();
        $this->db      = Database::connect();
        helper(['url', 'number']);
    }

    public function index()
    {
        $auth = $this->session->get('auth');
        if (!$auth || empty($auth['logged_in'])) {
            return redirect()->route('login');
        }

        $role = $auth['role'] ?? '';
        $studentId = 0;

        // ===== Parent login =====
        if ($role === 'parent') {
            // use active_student_id set on parent dashboard
            $studentId = (int) ($this->session->get('active_student_id') ?? 0);

            if ($studentId <= 0) {
                // no active child – go back with a clear message
                return redirect()->route('dashboard')
                    ->with('error', 'Please select a child from the dashboard first.');
            }
        }
        // ===== Direct student login =====
        elseif ($role === 'student') {
            // adjust if your auth array stores student_id differently
            $studentId = (int) ($auth['student_id'] ?? 0);

            if ($studentId <= 0) {
                return redirect()->route('login')
                    ->with('error', 'Student information not found. Please log in again.');
            }
        }
        // ===== Any other role – send to login =====
        else {
            return redirect()->route('login');
        }

        // -----------------------------
        //  Load fee data for this student
        // -----------------------------

        // Example basic queries – keep or replace with your existing logic
        $fees = $this->db->table('fee_chalan')
            ->select('chalan_id, fee_month, due_date, amount, discount, status, created_date')
            ->where('student_id', $studentId)
            ->orderBy('due_date', 'DESC')
            ->get()
            ->getResultArray();

        $summaryRow = $this->db->table('fee_chalan')
            ->select('
                SUM(amount-discount) AS total,
                
            ')
            ->where('student_id', $studentId)
            ->where('status', 'paid')
            ->get()
            ->getRowArray() ?? [];

        $summary = [
            'total'   => (float) ($summaryRow['total']   ?? 0),
            'paid'    => (float) ($summaryRow['paid']    ?? 0),
            'balance' => (float) ($summaryRow['balance'] ?? 0),
        ];

        return view('frontend/fees/index', [
            'title'   => 'Fees',
            'fees'    => $fees,
            'summary' => $summary,
        ]);
    }
}
