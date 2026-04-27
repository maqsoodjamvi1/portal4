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

    // ===== OUTPUT =====
    $output  = "Payment Received: " . money_from_base($totals->paid_total ?? 0) . "/-<br>";
    $output .= "Payment Balance: " . money_from_base($totals->unpaid_total ?? 0) . "/-<br>";
    $output .= "<table class='table'><tr><th>#</th><th>Parent Name</th><th>Students</th><th>Paid Date</th><th>Amount</th></tr>";

    $i = 1;
    foreach ($payments as $payment) {
        $output .= sprintf(
            "<tr><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",
            $i++,
            esc($payment->f_name),
            esc($payment->students),
            date("d M Y l", strtotime($payment->paid_date)),
            money_from_base(round($payment->total))
        );
    }

    $output .= "</table>";

    return $this->response->setBody($output);
}


}
