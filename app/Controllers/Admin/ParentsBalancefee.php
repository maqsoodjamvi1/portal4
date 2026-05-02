<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ParentsBalancefee extends BaseController
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
        return view('admin/parents_balancefee', $this->template_data);
    }

    private function getSchoolInfo()
    {
        $campusid = $this->session->get('member_campusid');
        return $this->db->table('campus')->select('system_id')->where('campus_id', $campusid)->get()->getRow();
    }

    public function data()
    {
        $cls_sec_id            = $this->request->getPost('cls_sec_id');
        $selected_months       = $this->request->getPost('months') ?? [];
        $show_projected        = $this->request->getPost('show_projected');
        $hide_zero             = $this->request->getPost('hide_zero');
        $show_family_head      = $this->request->getPost('show_family_head');
        $show_monthly_balance  = $this->request->getPost('monthly_fee') ? 1 : 0;
        $show_other_balance    = $this->request->getPost('others_fee') ? 1 : 0;
        $include_monthly_paid  = $this->request->getPost('include_monthly_paid') ? 1 : 0;
        $include_others_paid   = $this->request->getPost('include_others_paid') ? 1 : 0;
        $show_balance          = $this->request->getPost('show_balance');
        $monthly_fee_defaulter = $this->request->getPost('monthly_fee_defaulter') ? 1 : 0;
        $other_fee_defaulter   = $this->request->getPost('other_fee_defaulter') ? 1 : 0;
        $show_grand_total      = $this->request->getPost('show_grand_total') ? 1 : 0;

        $campusid  = $this->session->get('member_campusid');
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

        // Balance Map per month per parent
        $balanceMap = [];
        $selected_fee_months = $selected_months; // e.g. '2024-07'
        $feeTypeIds = array_merge(
            $include_monthly_paid ? $monthlyFeeTypeIds : [],
            $include_others_paid ? $otherFeeTypeIds : []
        );

        $balanceResult = [];
        if (!empty($feeTypeIds) && !empty($selected_fee_months)) {
            $balanceResult = $this->db->table('fee_chalan fc')
                ->select("
                    s.parent_id, 
                    fc.fee_month,
                    SUM(CASE WHEN ft.is_monthly_fee = 1 THEN fc.amount - fc.discount ELSE 0 END) as monthly_balance,
                    SUM(CASE WHEN ft.is_monthly_fee = 0 THEN fc.amount - fc.discount ELSE 0 END) as other_balance,
                    SUM(fc.amount - fc.discount) as total_balance
                ")
                ->join('students s', 'fc.student_id = s.student_id')
                ->join('fee_type ft', 'fc.fee_type_id = ft.fee_type_id')
                ->where('fc.status', 'Unpaid')
                ->where('s.campus_id', $campusid)
                ->where('s.status', 1)
                ->whereIn('fc.fee_month', $selected_fee_months);

            if ($monthly_fee_defaulter && !$other_fee_defaulter) {
                $balanceResult = $balanceResult->where('ft.is_monthly_fee', 1);
            } elseif ($other_fee_defaulter && !$monthly_fee_defaulter) {
                $balanceResult = $balanceResult->where('ft.is_monthly_fee', 0);
            }

            $balanceResult = $balanceResult->groupBy(['s.parent_id', 'fc.fee_month'])
                ->get()->getResultArray();
        }

        // Map: [parent_id][month] => balance
        foreach ($balanceResult as $row) {
            $parent_id = $row['parent_id'];
            $month_key = $row['fee_month'];
            if ($monthly_fee_defaulter && !$other_fee_defaulter) {
                $balance = $row['monthly_balance'];
            } elseif ($other_fee_defaulter && !$monthly_fee_defaulter) {
                $balance = $row['other_balance'];
            } else {
                $balance = $row['total_balance'];
            }
            $balanceMap[$parent_id][$month_key] = $balance;
        }

        // Paid today map for undo
        $paidTodayMap = [];
        $today = date('Y-m-d');
        $paidTodayResult = $this->db->table('fee_chalan fc')
            ->select('s.parent_id, fc.fee_month, COUNT(*) as paid_today')
            ->join('students s', 'fc.student_id = s.student_id')
            ->where('fc.status', 'Paid')
            ->where('fc.paid_date', $today)
            ->whereIn('fc.fee_type_id', $monthlyFeeTypeIds)
            ->where('s.status', 1)
            ->groupBy(['s.parent_id', 'fc.fee_month'])
            ->get()->getResultArray();
        foreach ($paidTodayResult as $row) {
            $paidTodayMap[$row['parent_id']][$row['fee_month']] = true;
        }

        // Build Table
        $months = $selected_months ?: [];
        $monthly_totals = array_fill_keys($months, 0);

        $total_projected = 0;
        $total_monthly_balance = 0;
        $total_other_balance = 0;
        $grand_total_balance = 0;

        $rowsHtml = '';
        $count = 1;
        foreach ($parentsData as $p) {
            $monthly_balance = $monthlyUnpaidMap[$p->parent_id] ?? 0;
            $other_balance = $otherUnpaidMap[$p->parent_id] ?? 0;
            $total_balance = $monthly_balance + $other_balance;
            $projected_fee = $projectedFees[$p->parent_id] ?? 0;

            $total_monthly_balance += $monthly_balance;
            $total_other_balance += $other_balance;
            $grand_total_balance += $total_balance;
            $total_projected += $projected_fee;

            if ($hide_zero && $monthly_balance == 0 && $other_balance == 0 && $projected_fee == 0) continue;
            if ($monthly_fee_defaulter && $monthly_balance <= 0) continue;
            if ($other_fee_defaulter && $other_balance <= 0) continue;
            if ($monthly_fee_defaulter && $other_fee_defaulter && $total_balance <= 0) continue;

            $rowsHtml .= '<tr>';
            $rowsHtml .= '<td>'.$count.'</td>';
            $rowsHtml .= '<td>'.$p->parent_id.'</td>';
            $rowsHtml .= '<td>'.$p->f_name.'<br><small>'.$p->students.'</small></td>';
            if ($show_monthly_balance) $rowsHtml .= '<td>'.number_format($monthly_balance).'</td>';
            if ($show_other_balance) $rowsHtml .= '<td>'.number_format($other_balance).'</td>';
            if ($show_balance) $rowsHtml .= '<td>'.number_format($total_balance).'</td>';
            if ($show_projected) $rowsHtml .= '<td>'.number_format($projected_fee).'</td>';

            foreach ($months as $m) {
                $balance = $balanceMap[$p->parent_id][$m] ?? 0;
                $rowClass = $balance > 0 ? 'negative-balance' : '';
                $paidToday = isset($paidTodayMap[$p->parent_id][$m]) ? $paidTodayMap[$p->parent_id][$m] : false;
                if ($balance > 0) {
                    $rowsHtml .= '<td class="'.$rowClass.'">';
                    $rowsHtml .= '<div class="d-flex flex-column align-items-center">';
                    $rowsHtml .= '<div class="mb-1">'.number_format($balance).'</div>';
                    if ($paidToday) {
                        $rowsHtml .= '<button class="btn btn-sm btn-warning update-fee-btn" data-parent="'.$p->parent_id.'" data-month="'.$m.'" data-balance="'.number_format($balance).'"><i class="fas fa-undo mr-1"></i> Undo</button>';
                    } else {
                        $rowsHtml .= '<button class="btn btn-sm btn-success update-fee-btn" data-parent="'.$p->parent_id.'" data-month="'.$m.'" data-balance="'.number_format($balance).'" data-action="pay"><i class="fas fa-check-circle mr-1"></i> Mark Paid</button>';
                    }
                    $rowsHtml .= '</div></td>';
                } else {
                    $rowsHtml .= '<td class="'.$rowClass.'">'.number_format($balance).'</td>';
                }
                $monthly_totals[$m] += $balance;
            }
            $rowsHtml .= '</tr>';
            $count++;
        }

        // Table Header (kept for detailed view / print / export)
        $tableHtml = '<table class="table table-striped table-bordered table-hover balance-table" style="font-size:12px;width:100%;">
            <thead><tr>
                <th>#</th>
                <th>F ID</th>
                <th style="text-align: left;">Parent/Students</th>';
        if ($show_monthly_balance) $tableHtml .= '<th>Monthly Bal.</th>';
        if ($show_other_balance) $tableHtml .= '<th>Other Bal.</th>';
        if ($show_balance) $tableHtml .= '<th>Total Bal.</th>';
        if ($show_projected) $tableHtml .= '<th>Proj.</th>';
        foreach ($months as $m) {
            $tableHtml .= '<th>'.date('M y', strtotime($m)).'</th>';
        }
        $tableHtml .= '</tr></thead><tbody>'.$rowsHtml;

        // Grand Total Row
        if ($show_grand_total) {
            $tableHtml .= '<tr class="total-row"><td colspan="3" class="text-right font-weight-bold">Grand Total</td>';
            if ($show_monthly_balance) $tableHtml .= '<td>'.number_format($total_monthly_balance).'</td>';
            if ($show_other_balance) $tableHtml .= '<td>'.number_format($total_other_balance).'</td>';
            if ($show_balance) $tableHtml .= '<td>'.number_format($grand_total_balance).'</td>';
            if ($show_projected) $tableHtml .= '<td>'.number_format($total_projected).'</td>';
            foreach ($months as $m) {
                $tableHtml .= '<td>'.number_format($monthly_totals[$m]).'</td>';
            }
            $tableHtml .= '</tr>';
        }
        $tableHtml .= '</tbody></table>';

        // Build card-first output to avoid very wide horizontal tables
        $rowsCount = max(0, $count - 1);
        $summaryHtml = '<div class="report-summary-cards mb-3">';
        $summaryHtml .= '<div class="summary-card"><div class="k">Families</div><div class="v">'.number_format($rowsCount).'</div></div>';
        if ($show_monthly_balance) {
            $summaryHtml .= '<div class="summary-card"><div class="k">Monthly Balance</div><div class="v">'.number_format($total_monthly_balance).'</div></div>';
        }
        if ($show_other_balance) {
            $summaryHtml .= '<div class="summary-card"><div class="k">Other Balance</div><div class="v">'.number_format($total_other_balance).'</div></div>';
        }
        if ($show_balance) {
            $summaryHtml .= '<div class="summary-card"><div class="k">Total Balance</div><div class="v">'.number_format($grand_total_balance).'</div></div>';
        }
        if ($show_projected) {
            $summaryHtml .= '<div class="summary-card"><div class="k">Projected</div><div class="v">'.number_format($total_projected).'</div></div>';
        }
        $summaryHtml .= '</div>';

        // Build card rows again with same filters, but compact and readable
        $cardHtml = '<div class="balance-card-grid">';
        foreach ($parentsData as $p) {
            $monthly_balance = $monthlyUnpaidMap[$p->parent_id] ?? 0;
            $other_balance = $otherUnpaidMap[$p->parent_id] ?? 0;
            $total_balance = $monthly_balance + $other_balance;
            $projected_fee = $projectedFees[$p->parent_id] ?? 0;

            if ($hide_zero && $monthly_balance == 0 && $other_balance == 0 && $projected_fee == 0) continue;
            if ($monthly_fee_defaulter && $monthly_balance <= 0) continue;
            if ($other_fee_defaulter && $other_balance <= 0) continue;
            if ($monthly_fee_defaulter && $other_fee_defaulter && $total_balance <= 0) continue;

            $cardHtml .= '<div class="balance-card" data-search="'.strtolower(htmlspecialchars($p->f_name.' '.$p->students.' '.$p->parent_id)).'">';
            $cardHtml .= '<div class="balance-card-head">';
            $cardHtml .= '<div><strong>'.htmlspecialchars($p->f_name).'</strong><div class="small text-muted">F-ID: '.$p->parent_id.'</div></div>';
            $cardHtml .= '<div class="text-right">';
            if ($show_balance) {
                $cardHtml .= '<div class="badge badge-light border">Total: '.number_format($total_balance).'</div>';
            }
            $cardHtml .= '</div></div>';
            $cardHtml .= '<div class="small text-muted mb-2">'.htmlspecialchars($p->students).'</div>';

            $cardHtml .= '<div class="balance-metrics">';
            if ($show_monthly_balance) $cardHtml .= '<span class="metric-chip">Monthly '.number_format($monthly_balance).'</span>';
            if ($show_other_balance) $cardHtml .= '<span class="metric-chip">Other '.number_format($other_balance).'</span>';
            if ($show_projected) $cardHtml .= '<span class="metric-chip">Projected '.number_format($projected_fee).'</span>';
            $cardHtml .= '</div>';

            $cardHtml .= '<div class="month-chip-wrap">';
            foreach ($months as $m) {
                $balance = $balanceMap[$p->parent_id][$m] ?? 0;
                $paidToday = isset($paidTodayMap[$p->parent_id][$m]) ? $paidTodayMap[$p->parent_id][$m] : false;
                $monthLabel = date('M y', strtotime($m));
                $chipClass = $balance > 0 ? 'chip-due' : 'chip-clear';
                $cardHtml .= '<div class="month-chip '.$chipClass.'">';
                $cardHtml .= '<div class="month-title">'.$monthLabel.'</div>';
                $cardHtml .= '<div class="month-amount">'.number_format($balance).'</div>';
                if ($balance > 0) {
                    if ($paidToday) {
                        $cardHtml .= '<button class="btn btn-xs btn-warning update-fee-btn" data-parent="'.$p->parent_id.'" data-month="'.$m.'" data-balance="'.number_format($balance).'"><i class="fas fa-undo mr-1"></i>Undo</button>';
                    } else {
                        $cardHtml .= '<button class="btn btn-xs btn-success update-fee-btn" data-parent="'.$p->parent_id.'" data-month="'.$m.'" data-balance="'.number_format($balance).'" data-action="pay"><i class="fas fa-check-circle mr-1"></i>Paid</button>';
                    }
                }
                $cardHtml .= '</div>';
            }
            $cardHtml .= '</div>';
            $cardHtml .= '</div>';
        }
        $cardHtml .= '</div>';

        $output = '<div class="balance-report-wrap">'.$summaryHtml;
        $output .= '<div id="balanceCardView">'.$cardHtml.'</div>';
        $output .= '<div id="balanceTableView" style="display:none;"><div class="table-responsive mt-2">'.$tableHtml.'</div></div>';
        $output .= '</div>';

        return $this->response->setBody($output);
    }

    public function update_fee_status()
    {
        $parent_id = $this->request->getPost('parent_id');
        $month     = $this->request->getPost('month');
        $action    = strtolower($this->request->getPost('action')); // 'pay' or 'unpay'
        $campusid  = $this->session->get('member_campusid');

        if (!$action) {
            return $this->response->setJSON(['success' => false, 'message' => 'Action parameter is required']);
        }

        $system_id = $this->getSchoolInfo()->system_id;
        $monthlyFeeTypeIds = [];
        $monthlyFeeQuery = $this->db->table('fee_type')
            ->select('fee_type_id')
            ->where('is_monthly_fee', 1)
            ->where('system_id', $system_id)
            ->get();

        if ($monthlyFeeQuery) {
            $monthlyFeeTypeIds = array_column($monthlyFeeQuery->getResultArray(), 'fee_type_id');
        }

        if (empty($monthlyFeeTypeIds)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No monthly fee types found']);
        }

        $builder = $this->db->table('fee_chalan')
            ->where('fee_month', $month)
            ->whereIn('fee_type_id', $monthlyFeeTypeIds)
            ->where('student_id IN (SELECT student_id FROM students WHERE parent_id = ' . $parent_id . ' AND campus_id = ' . $campusid . ')', null, false);

        if ($action === 'pay') {
            $updateData = [
                'status' => 'Paid',
                'paid_date' => date('Y-m-d')
            ];
            $builder->where('status', 'Unpaid');
        } elseif ($action === 'unpay') {
            $updateData = [
                'status' => 'Unpaid',
                'paid_date' => null
            ];
            $builder->where('status', 'Paid')->where('paid_date', date('Y-m-d'));
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
        }

        $builder->update($updateData);
        $affected_rows = $this->db->affectedRows();

        if ($affected_rows > 0) {
            return $this->response->setJSON([
                'success' => true,
                'message' => $action === 'pay' ? 'Fee status updated to Paid successfully' : 'Fee status reverted to Unpaid successfully',
                'action' => $action,
                'affected_rows' => $affected_rows
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => $action === 'pay'
                    ? 'No unpaid records found to update'
                    : 'No records found to revert. Note: You can only revert payments made today.'
            ]);
        }
    }
}
