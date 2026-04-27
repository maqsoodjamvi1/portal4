<?php
namespace App\Services;

class FeeService
{
    protected $db;
    protected $campusId;

    public function __construct()
    {
        $this->db = db_connect();
        $this->campusId = session('member_campusid');
    }

    public function getLast12Months()
    {
        $months = [];
        $paid = [];
        $unpaid = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $months[] = date('M Y', strtotime($date));

            $row = $this->db->table('fee_chalan')
                ->select("
                    SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) as paid,
                    SUM(CASE WHEN status='unpaid' THEN amount ELSE 0 END) as unpaid
                ")
                ->where('fee_month', $date)
                ->get()
                ->getRow();

            $paid[] = $row->paid ?? 0;
            $unpaid[] = $row->unpaid ?? 0;
        }

        return [
            'months' => $months,
            'paid' => $paid,
            'unpaid' => $unpaid
        ];
    }
}