<?php

namespace App\Models;

use CodeIgniter\Model;

class FeeChalanModel extends Model
{
    protected $db;
    protected $session;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->session = session();
    }

    public function getParentIdFromInput(): ?int
    {
        $request = \Config\Services::request();
        return (int) ($request->getPost('parent_id') ?? $request->getGet('parent_id') ?? 0);
    }

    public function getStudentsByParent(int $parent_id): array
    {
        return $this->db->table('students')
            ->where('parent_id', $parent_id)
            ->where('status', 1)
            ->get()
            ->getResult();
    }

    public function getParentInfo(int $parent_id): ?object
    {
        return $this->db->table('parents')
            ->where('id', $parent_id)
            ->get()
            ->getRow();
    }

    public function getFeeSummaries(int $parent_id): array
    {
        return $this->db->table('fee_chalan')
            ->select('SUM(amount) as total, status')
            ->where('parent_id', $parent_id)
            ->groupBy('status')
            ->get()
            ->getResultArray();
    }

    public function generateFeeActionButtons(?object $parentinfo): string
    {
        // Example: you can return a button group or dropdowns based on parent info
        return view('admin/fee/partials/action_buttons', ['parentinfo' => $parentinfo]);
    }

    public function generateAdvanceFeeModal(array $students): string
    {
        return view('admin/fee/partials/advance_fee_modal', ['students' => $students]);
    }

    public function generateDiscountUpdateModal(array $students): string
    {
        return view('admin/fee/partials/discount_modal', ['students' => $students]);
    }

    public function generateUnpaidFeeRows(array $students): string
    {
        return view('admin/fee/partials/unpaid_fee_rows', ['students' => $students]);
    }

    public function generatePaidFeeRows(array $students): string
    {
        return view('admin/fee/partials/paid_fee_rows', ['students' => $students]);
    }

    public function generatePayFeeModal(): string
    {
        return view('admin/fee/partials/pay_fee_modal');
    }

    public function generateFeeJavaScript(): string
    {
        return view('admin/fee/partials/fee_script');
    }
}

?>