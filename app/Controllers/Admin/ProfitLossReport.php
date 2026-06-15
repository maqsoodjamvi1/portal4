<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CampusFinanceService;
use App\Libraries\ReportCsvExport;
use Config\Database;

class ProfitLossReport extends BaseController
{
    /** @var \CodeIgniter\Database\BaseConnection */
    protected $db;

    public function __construct()
    {
        if (function_exists('check_permission')) {
            $ok = function_exists('hasPermission')
                && (hasPermission('admin-cash-flow-report') || hasPermission('admin-profit-loss-reports'));
            if (! $ok) {
                check_permission('admin-profit-loss-reports');
            }
        }

        $this->db      = Database::connect();
        $this->session = session();
    }

    public function index()
    {
        $campusId = (int) ($this->session->get('member_campusid') ?: 0);
        $finance  = new CampusFinanceService($this->db);

        return view('admin/profit_loss_report', [
            'months'          => $this->getMonthList(),
            'years'           => range((int) date('Y') - 5, (int) date('Y') + 1),
            'selected_month'  => date('m'),
            'selected_year'   => date('Y'),
            'finance_enabled' => $finance->campusHasFinanceAccounts($campusId),
        ]);
    }

    public function getDailyCollection()
    {
        $month = (int) $this->request->getPost('month');
        $year  = (int) $this->request->getPost('year');

        $campusId = (int) (
            $this->session->get('member_campusid')
            ?: $this->session->get('campus_id')
            ?: 0
        );

        if ($month < 1 || $month > 12 || $year < 2000) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Invalid month/year.',
            ]);
        }

        $builder = $this->db->table('fee_chalan fc');
        $builder->select("
            DATE_FORMAT(fc.paid_date,'%Y-%m-%d') AS paid_date,
            COALESCE(SUM(fc.amount),0)   AS total_amount,
            COALESCE(SUM(fc.discount),0) AS total_discount
        ", false)
            ->join('students s', 'fc.student_id = s.student_id', 'inner')
            ->whereIn('fc.status', ['paid', 'Paid']);

        if ($campusId > 0) {
            $builder->where('s.campus_id', $campusId);
        }

        $builder->where("MONTH(fc.paid_date) = {$month}", null, false)
            ->where("YEAR(fc.paid_date) = {$year}", null, false)
            ->groupBy("DATE_FORMAT(fc.paid_date,'%Y-%m-%d')", false)
            ->orderBy('fc.paid_date', 'ASC');

        $results = $builder->get()->getResultArray();

        $output        = [];
        $grandTotal    = 0.0;
        $grandDiscount = 0.0;

        foreach ($results as $row) {
            $rowTotal    = (float) ($row['total_amount'] ?? 0);
            $rowDiscount = (float) ($row['total_discount'] ?? 0);
            $net         = $rowTotal - $rowDiscount;

            $output[] = [
                'date'     => date('d-M-Y', strtotime($row['paid_date'])),
                'total'    => number_format($rowTotal),
                'discount' => number_format($rowDiscount),
                'net'      => number_format($net),
            ];

            $grandTotal    += $rowTotal;
            $grandDiscount += $rowDiscount;
        }

        return $this->response->setJSON([
            'success' => true,
            'rows'    => $output,
            'totals'  => [
                'total'    => number_format($grandTotal),
                'discount' => number_format($grandDiscount),
                'net'      => number_format($grandTotal - $grandDiscount),
            ],
        ]);
    }

    public function getMonthlySummary()
    {
        $month = (int) $this->request->getPost('month');
        $year  = (int) $this->request->getPost('year');
        $campusId = (int) ($this->session->get('member_campusid') ?: 0);

        if ($month < 1 || $month > 12 || $year < 2000) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid month/year.']);
        }

        $finance = new CampusFinanceService($this->db);
        $summary = $finance->getMonthlySummary($campusId, $year, $month);

        return $this->response->setJSON([
            'success' => true,
            'summary' => $summary,
        ]);
    }

    public function exportCsv()
    {
        $month = (int) $this->request->getPost('month');
        $year  = (int) $this->request->getPost('year');
        $campusId = (int) ($this->session->get('member_campusid') ?: 0);

        if ($month < 1 || $month > 12 || $year < 2000) {
            return $this->response->setStatusCode(400)->setBody('Invalid month/year.');
        }

        $builder = $this->db->table('fee_chalan fc');
        $builder->select("
            DATE_FORMAT(fc.paid_date,'%Y-%m-%d') AS paid_date,
            COALESCE(SUM(fc.amount),0)   AS total_amount,
            COALESCE(SUM(fc.discount),0) AS total_discount
        ", false)
            ->join('students s', 'fc.student_id = s.student_id', 'inner')
            ->whereIn('fc.status', ['paid', 'Paid']);

        if ($campusId > 0) {
            $builder->where('s.campus_id', $campusId);
        }

        $builder->where("MONTH(fc.paid_date) = {$month}", null, false)
            ->where("YEAR(fc.paid_date) = {$year}", null, false)
            ->groupBy("DATE_FORMAT(fc.paid_date,'%Y-%m-%d')", false)
            ->orderBy('fc.paid_date', 'ASC');

        $results = $builder->get()->getResultArray();
        $csvRows = [];
        foreach ($results as $row) {
            $total    = (float) ($row['total_amount'] ?? 0);
            $discount = (float) ($row['total_discount'] ?? 0);
            $csvRows[] = [
                $row['paid_date'] ?? '',
                number_format($total, 2, '.', ''),
                number_format($discount, 2, '.', ''),
                number_format($total - $discount, 2, '.', ''),
            ];
        }

        $months = $this->getMonthList();

        return ReportCsvExport::downloadResponse(
            $this->response,
            'profit-loss-' . ($months[$month] ?? $month) . '-' . $year . '.csv',
            ['Date', 'Collected', 'Discount', 'Net'],
            $csvRows
        );
    }

    private function getMonthList(): array
    {
        return [
            1  => 'January', 2 => 'February', 3 => 'March',
            4  => 'April', 5 => 'May', 6 => 'June',
            7  => 'July', 8 => 'August', 9 => 'September',
            10 => 'October', 11 => 'November', 12 => 'December',
        ];
    }
}
