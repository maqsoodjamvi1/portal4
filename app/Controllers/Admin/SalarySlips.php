<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SalarySlips extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
        helper(['form', 'url']);
    }

    public function index()
    {
        $campusId = $this->session->get('member_campusid');
        $year = $this->request->getGet('year') ?? date('Y');
        $month = $this->request->getGet('month') ?? date('m');
        $employeeId = $this->request->getGet('employee_id');

        // Get all salary slips
        $builder = $this->db->table('salary_slips')
            ->select('salary_slips.*, users.first_name, users.last_name, users.designation')
            ->join('users', 'users.id = salary_slips.user_id')
            ->where('salary_slips.campus_id', $campusId);

        if ($year) {
            $builder->where('salary_slips.year', $year);
        }
        if ($month) {
            $builder->where('salary_slips.month', $month);
        }
        if ($employeeId) {
            $builder->where('salary_slips.user_id', $employeeId);
        }

        $slips = $builder->orderBy('salary_slips.year', 'DESC')
            ->orderBy('salary_slips.month', 'DESC')
            ->get()
            ->getResult();

        // Get employees for filter
        $employees = $this->db->table('users')
            ->select('id, first_name, last_name')
            ->where('campus_id', $campusId)
            ->where('status', 1)
            ->orderBy('first_name')
            ->get()
            ->getResult();

        return view('admin/salary_slips_list', [
            'slips' => $slips,
            'employees' => $employees,
            'year' => $year,
            'month' => $month,
            'selectedEmployee' => $employeeId
        ]);
    }
}
