<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Config\Database;

class ProfitLossReport extends BaseController
{
    /** @var \CodeIgniter\Database\BaseConnection */
    protected $db;

    public function __construct()
    {
        // If you use a helper function for permissions, keep it:
        if (function_exists('check_permission')) {
            check_permission('admin-profit-loss-reports');
        }

        $this->db      = Database::connect();
        $this->session = session();
    }

    /**
     * GET: /admin/profit-loss-report
     */
    public function index()
    {
        $data = [
            'months'         => $this->getMonthList(),
            'years'          => range((int)date('Y') - 5, (int)date('Y') + 1),
            'selected_month' => date('m'),
            'selected_year'  => date('Y'),
        ];

        // adjust view path if you keep views under /Views/admin/
        return view('admin/profit_loss_report', $data); // <- use admin/ prefix
        // return view('admin/profit_loss_report', $data);
    }

    /**
     * POST: /admin/profit-loss-report/daily
     * Expects: month (1-12), year (YYYY)
     * Returns: JSON { success, rows[{date,total,discount,net}], totals{total,discount,net} }
     */
    public function getDailyCollection()
    {
        $month = (int) $this->request->getPost('month');
        $year  = (int) $this->request->getPost('year');

        // Session campus (use your actual key name; both tried here)
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

        log_message('debug', sprintf(
            'ProfitLossReport::getDailyCollection month=%d year=%d campus=%d',
            $month, $year, $campusId
        ));

        $builder = $this->db->table('fee_chalan fc');
        $builder->select("
            DATE_FORMAT(fc.paid_date,'%Y-%m-%d') AS paid_date,
            COALESCE(SUM(fc.amount),0)   AS total_amount,
            COALESCE(SUM(fc.discount),0) AS total_discount
        ", false)
        ->join('students s', 'fc.student_id = s.student_id', 'inner')
        ->where('fc.status', 'Paid');

        if ($campusId > 0) {
            $builder->where('s.campus_id', $campusId);
        }

        // Use raw where for MONTH()/YEAR() to avoid quoting issues
        $builder->where("MONTH(fc.paid_date) = {$month}", null, false)
                ->where("YEAR(fc.paid_date) = {$year}", null, false)
                ->groupBy("DATE_FORMAT(fc.paid_date,'%Y-%m-%d')", false)
                ->orderBy('fc.paid_date', 'ASC');

        $query   = $builder->get();
        $results = $query->getResultArray();

        $output         = [];
        $grandTotal     = 0.0;
        $grandDiscount  = 0.0;

        foreach ($results as $row) {
            $rowTotal    = (float) ($row['total_amount']   ?? 0);
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

        // Log the last query (stringify CI4 Query object)
        $last = (string) $this->db->getLastQuery();
        log_message('debug', 'ProfitLossReport SQL: ' . $last);
        log_message('debug', 'ProfitLossReport rows: ' . count($results));

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

    private function getMonthList(): array
    {
        return [
            1  => 'January',   2 => 'February', 3 => 'March',
            4  => 'April',     5 => 'May',      6 => 'June',
            7  => 'July',      8 => 'August',   9 => 'September',
            10 => 'October',  11 => 'November', 12 => 'December',
        ];
    }
}
