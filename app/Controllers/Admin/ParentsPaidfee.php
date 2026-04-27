<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ParentsPaidfee extends BaseController
{
    protected $db;
    protected $session;
    protected $template_data = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        check_permission('admin-students');
    }

    public function index()
    {
        $currentrole = currentUserRoles();

        if (in_array(5, $currentrole)) {
            $sectionsclassinfo = teacherSubjectSections();
        } else {
            $sectionsclassinfo = userClassSections();
        }
        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;
        return view('admin/parents_paidfee', $this->template_data);
    }

    private function getSchoolInfo()
    {
        $campusid = $this->session->get('member_campusid');
        return $this->db->table('campus')->select('system_id')->where('campus_id', $campusid)->get()->getRow();
    }

    public function data(): ResponseInterface
    {
        $cls_sec_id = $this->request->getPost('cls_sec_id');
        $selected_months = $this->request->getPost('months') ?? [];
        $show_projected = $this->request->getPost('show_projected');
        $hide_zero = $this->request->getPost('hide_zero');
        $show_family_head = $this->request->getPost('show_family_head');
        $show_monthly_balance = $this->request->getPost('monthly_fee') ? 1 : 0;
        $show_other_balance = $this->request->getPost('others_fee') ? 1 : 0;
        $include_monthly_paid = $this->request->getPost('include_monthly_paid') ? 1 : 0;
        $include_others_paid = $this->request->getPost('include_others_paid') ? 1 : 0;
        $show_balance = $this->request->getPost('show_balance');
        $monthly_fee_defaulter = $this->request->getPost('monthly_fee_defaulter') ? 1 : 0;
        $other_fee_defaulter = $this->request->getPost('other_fee_defaulter') ? 1 : 0;
        $show_grand_total = $this->request->getPost('show_grand_total') ? 1 : 0;

        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $system_id = $this->getSchoolInfo()->system_id;

        // Get Monthly Fee Type IDs
        $monthlyFeeTypeIds = [];
        $monthlyFeeQuery = $this->db->table('fee_type')
            ->select('fee_type_id')
            ->where('is_monthly_fee', 1)
            ->where('system_id', $system_id)
            ->get();
        if ($monthlyFeeQuery) {
            $monthlyFeeTypeIds = array_column($monthlyFeeQuery->getResultArray(), 'fee_type_id');
        }

        // Get Other Fee Type IDs (non-monthly)
        $otherFeeTypeIds = [];
        if (!empty($monthlyFeeTypeIds)) {
            $otherFeeQuery = $this->db->table('fee_type')
                ->select('fee_type_id')
                ->whereNotIn('fee_type_id', $monthlyFeeTypeIds)
                ->where('system_id', $system_id)
                ->get();
            if ($otherFeeQuery) {
                $otherFeeTypeIds = array_column($otherFeeQuery->getResultArray(), 'fee_type_id');
            }
        }

        // Get Parent Data
        $parentsQuery = $this->db->table('parents p')
            ->select("
                p.parent_id, 
                p.f_name, 
                p.father_contact,
                GROUP_CONCAT(DISTINCT CONCAT(s.first_name, ' ', s.last_name, ' (',
                    IFNULL(CONCAT(c.class_short_name, '-', sec.short_name), 'Not Enrolled'), ')')
                    SEPARATOR ', '
                ) AS students
            ")
            ->join('students s', 'p.parent_id = s.parent_id AND s.status = 1 AND s.campus_id = ' . $campusid, 'inner');

        if ($show_family_head && !$cls_sec_id) {
            $parentsQuery
                ->join('(SELECT st.parent_id, MAX(sc.cls_sec_id) AS max_cls_sec_id
                    FROM students st
                    JOIN student_class sc ON sc.student_id = st.student_id AND sc.session_id = ' . $sessionid . '
                    WHERE st.status = 1 AND st.campus_id = ' . $campusid . '
                    GROUP BY st.parent_id) fs', 'fs.parent_id = p.parent_id', 'inner')
                ->join('student_class sc', 's.student_id = sc.student_id AND sc.cls_sec_id = fs.max_cls_sec_id', 'inner');
        } else {
            $parentsQuery->join('student_class sc', 's.student_id = sc.student_id AND sc.session_id = ' . $sessionid . ($cls_sec_id ? ' AND sc.cls_sec_id = ' . $cls_sec_id : ''), 'inner');
        }

        $parentsQuery
            ->join('class_section cs', 'sc.cls_sec_id = cs.cls_sec_id', 'left')
            ->join('classes c', 'cs.class_id = c.class_id', 'left')
            ->join('sections sec', 'cs.section_id = sec.section_id', 'left')
            ->groupBy('p.parent_id');

        $parentsResult = $parentsQuery->get();
        $parentsData = $parentsResult->getResult();

        // Get Monthly Unpaid Balances
        $monthlyUnpaidMap = [];
        if (!empty($monthlyFeeTypeIds)) {
            $monthlyUnpaidResult = $this->db->table('fee_chalan fc')
                ->select('s.parent_id, SUM(fc.amount - fc.discount) AS total_unpaid')
                ->join('students s', 'fc.student_id = s.student_id')
                ->where('fc.status', 'Unpaid')
                ->whereIn('fc.fee_type_id', $monthlyFeeTypeIds)
                ->where('s.status', 1)
                ->groupBy('s.parent_id')
                ->get()
                ->getResultArray();
            $monthlyUnpaidMap = array_column($monthlyUnpaidResult, 'total_unpaid', 'parent_id');
        }

        // Get Other Unpaid Balances
        $otherUnpaidMap = [];
        if (!empty($otherFeeTypeIds)) {
            $otherUnpaidResult = $this->db->table('fee_chalan fc')
                ->select('s.parent_id, SUM(fc.amount - fc.discount) AS total_unpaid')
                ->join('students s', 'fc.student_id = s.student_id')
                ->where('fc.status', 'Unpaid')
                ->whereIn('fc.fee_type_id', $otherFeeTypeIds)
                ->where('s.status', 1)
                ->groupBy('s.parent_id')
                ->get()
                ->getResultArray();
            $otherUnpaidMap = array_column($otherUnpaidResult, 'total_unpaid', 'parent_id');
        }

        // Projected fees: Always include monthly fee types if show_projected is checked
        if ($show_projected) {
            $query = $this->db->table('fee_type')
                ->select('fee_type_id')
                ->where('is_monthly_fee', 1)
                ->where('system_id', $system_id)
                ->get();
            if ($query) {
                $fee_types_monthly = $query->getResultArray();
                $monthlyFeeTypeIds = array_column($fee_types_monthly, 'fee_type_id');
            }
        }

        // Get Projected Fees (Monthly fees only)
        $projectedFees = [];
        if ($show_projected && !empty($monthlyFeeTypeIds)) {
            foreach ($parentsData as $parent) {
                $amountQuery = $this->db->table('fee_amount fa')
                    ->select('SUM(fa.amount) as total_amount')
                    ->join('class_section cs', 'fa.class_id = cs.class_id')
                    ->join('student_class sc', 'cs.cls_sec_id = sc.cls_sec_id')
                    ->join('students s', 'sc.student_id = s.student_id')
                    ->where('fa.campus_id', $campusid)
                    ->where('fa.session_id', $sessionid)
                    ->whereIn('fa.fee_type_id', $monthlyFeeTypeIds)
                    ->where('s.parent_id', $parent->parent_id)
                    ->where('s.status', 1)
                    ->where('sc.session_id', $sessionid)
                    ->get();
                $total_amount = $amountQuery->getRow()->total_amount ?? 0;

                $discountQuery = $this->db->table('students')
                    ->select('SUM(discounted_amount) as total_discount')
                    ->where('parent_id', $parent->parent_id)
                    ->where('status', 1)
                    ->get();
                $total_discount = $discountQuery->getRow()->total_discount ?? 0;

                $projectedFees[$parent->parent_id] = $total_amount - $total_discount;
            }
        }

        // Prepare months for paid map
        $months = empty($selected_months) ? array_reverse(array_map(
            fn($i) => date('Y-m', strtotime("-$i months")), range(0, 11)
        )) : $selected_months;

        $feeTypeIds = array_merge(
            $include_monthly_paid ? $monthlyFeeTypeIds : [],
            $include_others_paid ? $otherFeeTypeIds : []
        );

        $paidMap = [];
        if (!empty($feeTypeIds)) {
            $feeResult = $this->db->table('fee_chalan fc')
                ->select('s.parent_id, DATE_FORMAT(fc.paid_date, "%Y-%m") as payment_month, SUM(fc.amount - fc.discount) as total_paid')
                ->join('students s', 'fc.student_id = s.student_id')
                ->where('fc.paid_date >=', date('Y-m-01', strtotime('-12 months')))
                ->where('fc.status', 'Paid')
                ->whereIn('fc.fee_type_id', $feeTypeIds)
                ->groupBy(['s.parent_id', 'payment_month'])
                ->get()
                ->getResult();
            foreach ($feeResult as $row) {
                $paidMap[$row->parent_id][$row->payment_month] = $row->total_paid;
            }
        }

        // Build Table
        $rowsHtml = '';
        $monthly_totals = array_fill_keys($months, 0);

        $total_projected = 0;
        $total_monthly_balance = 0;
        $total_other_balance = 0;
        $grand_total_balance = 0;

        $count = 1;
        foreach ($parentsData as $i => $p) {
            $monthly_balance = $monthlyUnpaidMap[$p->parent_id] ?? 0;
            $other_balance = $otherUnpaidMap[$p->parent_id] ?? 0;
            $total_balance = $monthly_balance + $other_balance;
            $projected_fee = $projectedFees[$p->parent_id] ?? 0;

            $total_monthly_balance += $monthly_balance;
            $total_other_balance += $other_balance;
            $grand_total_balance += $total_balance;
            $total_projected += $projected_fee;

            // Skip zero rows if requested
            if ($hide_zero && $monthly_balance == 0 && $other_balance == 0 && $projected_fee == 0) continue;

            // Skip non-defaulters if defaulter filter is on
            if ($monthly_fee_defaulter && $monthly_balance <= 0) continue;
            if ($other_fee_defaulter && $other_balance <= 0) continue;
            if ($monthly_fee_defaulter && $other_fee_defaulter && $total_balance <= 0) continue;

            // Build row
            $rowsHtml .= '<tr>';
            $rowsHtml .= '<td>' . $count . '</td>';
            $rowsHtml .= '<td>' . $p->parent_id . '</td>';
            $rowsHtml .= '<td>' . $p->f_name . '<br><small>' . $p->students . '</small></td>';

            if ($show_monthly_balance) {
                $rowsHtml .= '<td>' . number_format($monthly_balance) . '</td>';
            }
            if ($show_other_balance) {
                $rowsHtml .= '<td>' . number_format($other_balance) . '</td>';
            }
            if ($show_balance) {
                $rowsHtml .= '<td>' . number_format($total_balance) . '</td>';
            }
            if ($show_projected) {
                $rowsHtml .= '<td>' . number_format($projected_fee) . '</td>';
            }

            foreach ($months as $m) {
                $paid = $paidMap[$p->parent_id][$m] ?? 0;
                $rowsHtml .= '<td>' . number_format($paid) . '</td>';
                $monthly_totals[$m] += $paid;
            }
            $rowsHtml .= '</tr>';
            $count++;
        }

        // Table Header
        $output = '<table class="table table-striped table-bordered table-hover" style="font-size:12px;width:100%;">
            <thead><tr>
                <th>#</th>
                <th>F ID</th>
                <th style="text-align: left;">Parent/Students</th>';

        if ($show_monthly_balance) $output .= '<th>Monthly Bal.</th>';
        if ($show_other_balance) $output .= '<th>Other Bal.</th>';
        if ($show_balance) $output .= '<th>Total Bal.</th>';
        if ($show_projected) $output .= '<th>Proj.</th>';

        foreach ($months as $m) {
            $output .= '<th>' . date('M y', strtotime($m)) . '</th>';
        }

        $output .= '</tr></thead><tbody>' . $rowsHtml;

        // Grand Total Row
        if ($show_grand_total) {
            $output .= '<tr class="total-row"><td colspan="3" class="text-right font-weight-bold">Grand Total</td>';

            if ($show_monthly_balance) $output .= '<td>' . number_format($total_monthly_balance) . '</td>';
            if ($show_other_balance) $output .= '<td>' . number_format($total_other_balance) . '</td>';
            if ($show_balance) $output .= '<td>' . number_format($grand_total_balance) . '</td>';
            if ($show_projected) $output .= '<td>' . number_format($total_projected) . '</td>';

            foreach ($months as $m) {
                $output .= '<td>' . number_format($monthly_totals[$m]) . '</td>';
            }
            $output .= '</tr>';
        }

        $output .= '</tbody></table>';
        return $this->response->setBody($output);
    }
}
