<?php
namespace App\Controllers\Admin;


error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Students Previous Fee Manage
 *
 * @author      Maqsood Ahmed
 * @copyright   Copyright (c) 2018-2019 TIME Soft Solutions
 * @email       maqsoodjamvi@gmail.com
 * @filesource
 */
 


class Parents_balancefee extends MY_Controller {

     public function __construct() {
        parent::__construct();
        check_permission('admin-students');
        $this->load->helper(array('form', 'url'));
        $this->load->database();
    }

    /**
     * Index Page for this controller.
     */
    public function index() {    
        $currentrole = currentUserRoles();
        
        if(in_array(5, $currentrole)) {
            $sectionsclassinfo = teacherSubjectSections();
        } else {
            $sectionsclassinfo = userClassSections();
        }
        
        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;
        $this->load->view('parents_balancefee', $this->template_data);
    }
    
    private function getSchoolInfo() {
        $campusid = $this->session->userdata('member_campusid');
        $this->db->select('system_id')->from('campus')->where('campus_id', $campusid);
        return $this->db->get()->row();
    }


    function data() {
        $cls_sec_id = $this->input->post('cls_sec_id');
        $selected_months = $this->input->post('months') ?: [];
        $show_projected = $this->input->post('show_projected');
        $hide_zero = $this->input->post('hide_zero');
        $show_family_head = $this->input->post('show_family_head');
        // Column visibility controls
        $show_monthly_balance = $this->input->post('monthly_fee') ? 1 : 0;
        $show_other_balance = $this->input->post('others_fee') ? 1 : 0;

        $include_monthly_paid = $this->input->post('include_monthly_paid') ? 1 : 0;
        $include_others_paid = $this->input->post('include_others_paid') ? 1 : 0;

        $show_balance = $this->input->post('show_balance');
        $monthly_fee_defaulter = $this->input->post('monthly_fee_defaulter') ? 1 : 0; 
        $other_fee_defaulter = $this->input->post('other_fee_defaulter') ? 1 : 0; 

        $show_grand_total = $this->input->post('show_grand_total') ? 1 : 0;

        $campusid = $this->session->userdata('member_campusid');
        $sessionid = $this->session->userdata('member_sessionid');
        $system_id = $this->getSchoolInfo()->system_id;

        // Get Monthly Fee Type IDs
        $monthlyFeeTypeIds = [];
        $monthlyFeeQuery = $this->db->select('fee_type_id')
            ->from('fee_type')
            ->where('is_monthly_fee', 1)
            ->where('system_id', $system_id)
            ->get();
        if ($monthlyFeeQuery) {
            $monthlyFeeTypeIds = array_column($monthlyFeeQuery->result_array(), 'fee_type_id');
        }

        // Get Other Fee Type IDs (non-monthly)
        $otherFeeTypeIds = [];
        if (!empty($monthlyFeeTypeIds)) {
            $otherFeeQuery = $this->db->select('fee_type_id')
                ->from('fee_type')
                ->where_not_in('fee_type_id', $monthlyFeeTypeIds)
                ->where('system_id', $system_id)
                ->get();
            if ($otherFeeQuery) {
                $otherFeeTypeIds = array_column($otherFeeQuery->result_array(), 'fee_type_id');
            }
        }

        // Get Parent Data
        $parentsQuery = $this->db->select("
            p.parent_id, 
            p.f_name, 
            p.father_contact,
            GROUP_CONCAT(DISTINCT CONCAT(s.first_name, ' ', s.last_name, ' (',
                IFNULL(CONCAT(c.class_short_name, '-', sec.short_name), 'Not Enrolled'), ')')
                SEPARATOR ', '
            ) AS students
        ")->from('parents p')
          ->join('students s', 'p.parent_id = s.parent_id AND s.status = 1 AND s.campus_id = '.$campusid, 'inner');

        if ($show_family_head && !$cls_sec_id) {
            $parentsQuery->join('(SELECT st.parent_id, MAX(sc.cls_sec_id) AS max_cls_sec_id
                FROM students st
                JOIN student_class sc ON sc.student_id = st.student_id AND sc.session_id = '.$sessionid.'
                WHERE st.status = 1 AND st.campus_id = '.$campusid.'
                GROUP BY st.parent_id) fs', 'fs.parent_id = p.parent_id', 'inner')
                         ->join('student_class sc', 's.student_id = sc.student_id AND sc.cls_sec_id = fs.max_cls_sec_id', 'inner');
        } else {
            $parentsQuery->join('student_class sc', 's.student_id = sc.student_id AND sc.session_id = '.$sessionid.($cls_sec_id ? ' AND sc.cls_sec_id = '.$cls_sec_id : ''), 'inner');
        }

        $parentsQuery->join('class_section cs', 'sc.cls_sec_id = cs.cls_sec_id', 'left')
                     ->join('classes c', 'cs.class_id = c.class_id', 'left')
                     ->join('sections sec', 'cs.section_id = sec.section_id', 'left')
                     ->group_by('p.parent_id');

        $parentsResult = $parentsQuery->get();
        $parentsData = $parentsResult->result();

        // Get Monthly Unpaid Balances
        $monthlyUnpaidMap = [];
        if (!empty($monthlyFeeTypeIds)) {
            $this->db->select('s.parent_id, SUM(fc.amount - fc.discount) AS total_unpaid')
                     ->from('fee_chalan fc')
                     ->join('students s', 'fc.student_id = s.student_id')
                     ->where('fc.status', 'Unpaid')
                     ->where_in('fc.fee_type_id', $monthlyFeeTypeIds)
                     ->where('s.status', 1)
                     ->group_by('s.parent_id');
            $monthlyUnpaidResult = $this->db->get()->result_array();
            $monthlyUnpaidMap = array_column($monthlyUnpaidResult, 'total_unpaid', 'parent_id');
        }

        // Get Other Unpaid Balances
        $otherUnpaidMap = [];
        if (!empty($otherFeeTypeIds)) {
            $this->db->select('s.parent_id, SUM(fc.amount - fc.discount) AS total_unpaid')
                     ->from('fee_chalan fc')
                     ->join('students s', 'fc.student_id = s.student_id')
                     ->where('fc.status', 'Unpaid')
                     ->where_in('fc.fee_type_id', $otherFeeTypeIds)
                     ->where('s.status', 1)
                     ->group_by('s.parent_id');
            $otherUnpaidResult = $this->db->get()->result_array();
            $otherUnpaidMap = array_column($otherUnpaidResult, 'total_unpaid', 'parent_id');
        }

        // Projected fees: Always include monthly fee types if show_projected is checked
        if ($show_projected) {
            $query = $this->db->select('fee_type_id')
                ->from('fee_type')
                ->where('is_monthly_fee', 1)
                ->where('system_id', $system_id)
                ->get();
                
            if ($query) {
                $fee_types_monthly = $query->result_array();
                $monthlyFeeTypeIds = array_column($fee_types_monthly, 'fee_type_id');
            }
        }

        // Get Projected Fees (Monthly fees only)
        $projectedFees = [];
        if ($show_projected && !empty($monthlyFeeTypeIds)) {
            foreach ($parentsData as $parent) {
                $amountQuery = $this->db->select('SUM(fa.amount) as total_amount')
                                        ->from('fee_amount fa')
                                        ->join('class_section cs', 'fa.class_id = cs.class_id')
                                        ->join('student_class sc', 'cs.cls_sec_id = sc.cls_sec_id')
                                        ->join('students s', 'sc.student_id = s.student_id')
                                        ->where('fa.campus_id', $campusid)
                                        ->where('fa.session_id', $sessionid)
                                        ->where_in('fa.fee_type_id', $monthlyFeeTypeIds)
                                        ->where('s.parent_id', $parent->parent_id)
                                        ->where('s.status', 1)
                                        ->where('sc.session_id', $sessionid)
                                        ->get();
                $total_amount = $amountQuery->row()->total_amount ?? 0;

                $discountQuery = $this->db->select('SUM(discounted_amount) as total_discount')
                                          ->from('students')
                                          ->where('parent_id', $parent->parent_id)
                                          ->where('status', 1)
                                          ->get();
                $total_discount = $discountQuery->row()->total_discount ?? 0;

                $projectedFees[$parent->parent_id] = $total_amount - $total_discount;
            }
        }

        $balanceMap = [];
        $selected_fee_months = $selected_months; // Keep as 'Y-m'
        $feeTypeIds = array_merge(
        $include_monthly_paid ? $monthlyFeeTypeIds : [],
        $include_others_paid ? $otherFeeTypeIds : []
        );

        if (!empty($feeTypeIds)) 
        {
                    $this->db->select('
                    s.parent_id, 
                    fc.fee_month_new,
                    SUM(CASE WHEN ft.is_monthly_fee = 1 THEN fc.amount - fc.discount ELSE 0 END) as monthly_balance,
                    SUM(CASE WHEN ft.is_monthly_fee = 0 THEN fc.amount - fc.discount ELSE 0 END) as other_balance,
                    SUM(fc.amount - fc.discount) as total_balance
                ')
                ->from('fee_chalan fc')
                ->join('students s', 'fc.student_id = s.student_id')
                ->join('fee_type ft', 'fc.fee_type_id = ft.fee_type_id')
                ->where('fc.status', 'Unpaid')
                ->where('s.campus_id', $campusid)
                ->where('s.status', 1);
                 if (!empty($selected_fee_months)) {
                $this->db->where_in('fc.fee_month_new', $selected_fee_months);
                 if ($monthly_fee_defaulter && !$other_fee_defaulter) {
                $this->db->where('ft.is_monthly_fee', 1);
                    } elseif ($other_fee_defaulter && !$monthly_fee_defaulter) {
                        $this->db->where('ft.is_monthly_fee', 0);
                    }

                $balanceResult = $this->db->group_by('s.parent_id, fc.fee_month_new')
                ->get()
                ->result_array();

            }
            
             // foreach ($balanceResult as $row) {
             //     $balanceMap[$row->parent_id][$row->payment_month] = $row->total_paid;
             // }
        }

        // Build Table
        $rowsHtml = '';
       $months = $selected_months ?: [];  // Simplified to just use selected months or empty array
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
            $rowsHtml .= '<td>'.$count.'</td>';
            $rowsHtml .= '<td>'.$p->parent_id.'</td>';

            // Process balances into a map
            $balanceMap = [];
                        foreach ($balanceResult as $row) {
                $parent_id = $row['parent_id'];
                $fee_month_new = $row['fee_month_new']; // Already in 'Y-m'
                $month_key = $fee_month_new; // Use directly as the key
                
                // Determine balance based on fee type filters
                if ($monthly_fee_defaulter && !$other_fee_defaulter) {
                    $balance = $row['monthly_balance'];
                } elseif ($other_fee_defaulter && !$monthly_fee_defaulter) {
                    $balance = $row['other_balance'];
                } else {
                    $balance = $row['total_balance'];
                }
                
                // Assign to balanceMap
                $balanceMap[$parent_id][$month_key] = $balance;
            }


            
            $rowsHtml .= '<td>'.$p->f_name.'<br><small>'.$p->students.'</small></td>';
            
            if ($show_monthly_balance) {
                $rowsHtml .= '<td>'.number_format($monthly_balance).'</td>';
            }
            if ($show_other_balance) {
                $rowsHtml .= '<td>'.number_format($other_balance).'</td>';
            }
            if ($show_balance) {
                $rowsHtml .= '<td>'.number_format($total_balance).'</td>';
            }
            if ($show_projected) {
                $rowsHtml .= '<td>'.number_format($projected_fee).'</td>';
            }

            foreach ($months as $m) {
                $balance = $balanceMap[$p->parent_id][$m] ?? 0;
                $rowClass = $balance > 0 ? 'negative-balance' : '';
                $rowsHtml .= '<td class="'.$rowClass.'">'.number_format($balance).'</td>';
                $monthly_totals[$m] += $balance;
            }
            $rowsHtml .= '</tr>';
             $count++; // Increment only when row is rendered
        }

        // Table Header
        $colspan = 3 + ($show_monthly_balance ? 1 : 0) + ($show_other_balance ? 1 : 0) + ($show_balance ? 1 : 0) + ($show_projected ? 1 : 0);
        $output = '<table class="table table-striped table-bordered table-hover" style="font-size:12px;width:100%;">
            <thead><tr>
                <th>#</th>
                <th>F ID</th>
                <th style = "text-align: left;">Parent/Students</th>';
        
        if ($show_monthly_balance) $output .= '<th>Monthly Bal.</th>';
        if ($show_other_balance) $output .= '<th>Other Bal.</th>';
        if ($show_balance) $output .= '<th>Total Bal.</th>';
        if ($show_projected) $output .= '<th>Proj.</th>';
        
        foreach ($months as $m) {
            $output .= '<th>'.date('M y', strtotime($m)).'</th>';
        }
        
        $output .= '</tr></thead><tbody>'.$rowsHtml;

        // Grand Total Row
        if ($show_grand_total) {
            $output .= '<tr class="total-row"><td colspan="3" class="text-right font-weight-bold">Grand Total</td>';
            
            if ($show_monthly_balance) $output .= '<td>'.number_format($total_monthly_balance).'</td>';
            if ($show_other_balance) $output .= '<td>'.number_format($total_other_balance).'</td>';
            if ($show_balance) $output .= '<td>'.number_format($grand_total_balance).'</td>';
            if ($show_projected) $output .= '<td>'.number_format($total_projected).'</td>';
            
            foreach ($months as $m) {
                $output .= '<td>'.number_format($monthly_totals[$m]).'</td>';
            }
            $output .= '</tr>';
        }
        
        $output .= '</tbody></table>';
        echo $output;
    }

}
// end this file
