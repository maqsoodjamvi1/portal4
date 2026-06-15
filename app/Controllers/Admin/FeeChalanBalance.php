<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class FeeChalanBalance extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url']);
        // You can implement permission checks as needed here
    }

    public function index()
    {
        $schoolinfo = getSchoolInfo();
        $db = \Config\Database::connect();

        $fee_type = $db->table('fee_type')
            ->where('system_id', $schoolinfo->system_id)
            ->get()
            ->getResult();

        return view('admin/fee_chalan_balance', [
            'fee_type' => $fee_type
        ]);
    }

    /**
     * Daily collection summary: one row per paid date with total amount only (no payer detail).
     */
    public function dailyCollection()
    {
        return view('admin/fee_chalan_daily_collection');
    }

    /**
     * POST paid_date_from, paid_date_to (DD/MM/YYYY) — returns HTML table fragment.
     */
    public function getDailyCollection()
    {
        $db      = \Config\Database::connect();
        $session = session();
        $request = service('request');

        $campusid = (int) $session->get('member_campusid');

        $paid_date_from = date('Y-m-d', strtotime(str_replace('/', '-', $request->getPost('paid_date_from'))));
        $paid_date_to   = date('Y-m-d', strtotime(str_replace('/', '-', $request->getPost('paid_date_to'))));

        $rows = $db->query(
            'SELECT DATE(fc.paid_date) AS paid_day,
                    SUM(fc.amount - fc.discount) AS day_total
             FROM fee_chalan fc
             INNER JOIN students s ON fc.student_id = s.student_id
             WHERE s.campus_id = ?
               AND fc.status = ? 
               AND fc.paid_date BETWEEN ? AND ?
             GROUP BY DATE(fc.paid_date)
             HAVING SUM(fc.amount - fc.discount) > 0
             ORDER BY paid_day ASC',
            [$campusid, 'paid', $paid_date_from, $paid_date_to]
        )->getResult();

        $campusName = '';
        $campusRow = $db->table('campus')->select('campus_name')->where('campus_id', $campusid)->get()->getRow();
        if ($campusRow) {
            $campusName = (string) $campusRow->campus_name;
        }

        $grand = 0;
        foreach ($rows as $r) {
            $grand += (float) $r->day_total;
        }
        $grand = (int) round($grand);

        $output  = '<div id="dailyCollReportInner" class="daily-coll-report-inner">';
        $output .= '<div class="daily-coll-summary mb-3 p-3 border rounded bg-light">';
        $output .= '<div class="d-flex flex-wrap justify-content-between align-items-baseline">';
        $output .= '<div><span class="text-muted text-uppercase small d-block">Total collected (range)</span>';
        $output .= '<span class="h5 mb-0 fw-bold text-success">' . esc(money_from_base($grand)) . '/-</span></div>';
        if ($campusName !== '') {
            $output .= '<div class="small text-muted">Campus: ' . esc($campusName) . '</div>';
        }
        $output .= '</div></div>';

        if ($rows === []) {
            $output .= '<p class="text-muted mb-0">No fee collections found for the selected paid date range.</p></div>';

            return $this->response->setBody($output);
        }

        $output .= '<div class="table-responsive daily-coll-table-wrap">';
        $output .= '<table class="table table-bordered table-sm fee-balance-print-table daily-coll-table">';
        $output .= '<thead><tr><th class="text-center" style="width:4rem">#</th><th>Paid date</th><th class="text-end">Collection amount</th></tr></thead><tbody>';

        $i = 1;
        foreach ($rows as $r) {
            $dayKey = (string) $r->paid_day;
            $dayLabel = date('l, d M Y', strtotime($dayKey));
            $amt = (int) round((float) $r->day_total);
            $output .= sprintf(
                '<tr><td class="text-center">%d</td><td>%s</td><td class="text-end fw-bold">%s</td></tr>',
                $i++,
                esc($dayLabel),
                esc(money_from_base($amt))
            );
        }

        $output .= '</tbody><tfoot><tr class="daily-coll-tfoot">';
        $output .= '<th colspan="2" class="text-end">Grand total</th>';
        $output .= '<th class="text-end">' . esc(money_from_base($grand)) . '/-</th>';
        $output .= '</tr></tfoot></table></div></div>';

        return $this->response->setBody($output);
    }

public function getTotalfee()
{
    $db      = \Config\Database::connect();
    $session = session();
    $request = service('request');

    $campusid  = (int) $session->get('member_campusid');
    $sessionid = (int) $session->get('member_sessionid');

    // Convert DD/MM/YYYY -> YYYY-MM-DD
    $paid_date_from = date('Y-m-d', strtotime(str_replace('/', '-', $request->getPost('paid_date_from'))));
    $paid_date_to   = date('Y-m-d', strtotime(str_replace('/', '-', $request->getPost('paid_date_to'))));

    // ===== derive month range in format "YYYY-MM" =====
    // examples:
    // Today / This Month  ->  monthFrom = monthTo = current month
    // Last Month          ->  both previous month
    // Last 3 Months       ->  3 months range, including current
    // YTD (with your current JS) -> months from Jan to current month
    $monthFrom = date('Y-m', strtotime($paid_date_from));
    $monthTo   = date('Y-m', strtotime($paid_date_to));

    // ===== TOTALS (paid + unpaid) =====
    $totals = $db->query("
        SELECT 
            -- Payment Received: by paid_date range
            SUM(
                CASE 
                    WHEN status = 'paid'
                     AND paid_date BETWEEN ? AND ?
                    THEN amount - discount 
                    ELSE 0 
                END
            ) AS paid_total,

            -- Payment Balance (unpaid): by fee_month range 'YYYY-MM'
            SUM(
                CASE 
                    WHEN status = 'unpaid'
                     AND fee_month BETWEEN ? AND ?
                    THEN amount - discount 
                    ELSE 0 
                END
            ) AS unpaid_total

        FROM fee_chalan
        WHERE student_id IN (
            SELECT student_id 
            FROM students 
            WHERE campus_id = ?
        )
    ", [
        $paid_date_from,
        $paid_date_to,
        $monthFrom,
        $monthTo,
        $campusid
    ])->getRow();

    // ===== PARENT-WISE PAID PAYMENTS (unchanged) =====
    $payments = $db->query("
        SELECT  
            p.parent_id,
            p.f_name,
            GROUP_CONCAT(
                DISTINCT CONCAT(
                    s.first_name, ' ', s.last_name,
                    ' (', cls.class_name, '-', sec.section_name, ')'
                ) SEPARATOR ', '
            ) AS students,
            fc.paid_date,
            SUM(fc.amount - fc.discount) AS total
        FROM parents p
        JOIN students s ON p.parent_id = s.parent_id
        JOIN fee_chalan fc ON s.student_id = fc.student_id
        LEFT JOIN student_class sc 
            ON s.student_id = sc.student_id 
           AND sc.session_id = ?
        LEFT JOIN class_section cs ON sc.cls_sec_id = cs.cls_sec_id
        LEFT JOIN classes cls ON cs.class_id = cls.class_id
        LEFT JOIN sections sec ON cs.section_id = sec.section_id
        WHERE s.campus_id = ?
          AND fc.status = 'paid'
          AND fc.paid_date BETWEEN ? AND ?
        GROUP BY p.parent_id, fc.paid_date
        HAVING total > 0
        ORDER BY fc.paid_date
    ", [
        $sessionid,
        $campusid,
        $paid_date_from,
        $paid_date_to
    ])->getResult();

    $campusName = '';
    $campusRow = $db->table('campus')->select('campus_name')->where('campus_id', $campusid)->get()->getRow();
    if ($campusRow) {
        $campusName = (string) $campusRow->campus_name;
    }

    // Group parent-wise rows by calendar paid_date (daily collection sections)
    $byDay = [];
    foreach ($payments as $payment) {
        $dayKey = date('Y-m-d', strtotime((string) $payment->paid_date));
        if (! isset($byDay[$dayKey])) {
            $byDay[$dayKey] = [
                'sum'  => 0,
                'rows' => [],
            ];
        }
        $amt = (float) $payment->total;
        $byDay[$dayKey]['sum'] += $amt;
        $byDay[$dayKey]['rows'][] = $payment;
    }
    ksort($byDay);

    // ===== OUTPUT (markup tuned for A4 print in fee_chalan_balance view) =====
    $output  = '<div id="feeBalanceReportInner" class="fee-balance-report-inner">';
    $output .= '<div class="fee-balance-totals mb-3">';
    $output .= '<div class="fee-balance-totals-row">';
    $output .= '<div class="fee-balance-total-item">';
    $output .= '<span class="fee-balance-total-label">Payment received (range)</span>';
    $output .= '<span class="fee-balance-total-value">' . esc(money_from_base($totals->paid_total ?? 0)) . '/-</span>';
    $output .= '</div>';
    $output .= '<div class="fee-balance-total-item fee-balance-total-item--balance">';
    $output .= '<span class="fee-balance-total-label">Payment balance (outstanding)</span>';
    $output .= '<span class="fee-balance-total-value">' . esc(money_from_base($totals->unpaid_total ?? 0)) . '/-</span>';
    $output .= '</div>';
    $output .= '</div>';
    if ($campusName !== '') {
        $output .= '<div class="small text-muted fee-balance-campus-line mt-2">Campus: ' . esc($campusName) . '</div>';
    }
    $output .= '</div>';

    if ($byDay === []) {
        $output .= '<p class="text-muted mb-0">No fee collections found for the selected paid date range.</p></div>';

        return $this->response->setBody($output);
    }

    foreach ($byDay as $dayKey => $block) {
        $dayLabel = date('l, d M Y', strtotime($dayKey));
        $daySum   = (int) round($block['sum']);
        $rowNum   = 1;

        $output .= '<section class="fee-balance-day-block mb-4">';
        $output .= '<div class="fee-balance-day-head-row">';
        $output .= '<div class="fee-balance-day-heading">' . esc($dayLabel) . '</div>';
        $output .= '<div class="fee-balance-day-balance">';
        $output .= '<span class="fee-balance-day-total-label">Day wise balance</span>';
        $output .= '<span class="fee-balance-day-total-value">' . esc(money_from_base($daySum)) . '/-</span>';
        $output .= '</div></div>';

        $output .= '<div class="table-responsive fee-balance-table-wrap">';
        $output .= '<table class="table table-bordered table-sm fee-balance-print-table fee-balance-day-table">';
        $output .= '<thead><tr><th class="text-center" style="width:3rem">#</th><th>Parent name</th><th>Students</th><th class="text-end">Amount</th></tr></thead><tbody>';

        foreach ($block['rows'] as $payment) {
            $output .= sprintf(
                '<tr><td class="text-center">%d</td><td>%s</td><td>%s</td><td class="text-end fw-bold">%s</td></tr>',
                $rowNum++,
                esc($payment->f_name),
                esc($payment->students),
                esc(money_from_base(round((float) $payment->total)))
            );
        }

        $output .= '</tbody></table></div></section>';
    }

    $output .= '</div>';

    return $this->response->setBody($output);
}


}
