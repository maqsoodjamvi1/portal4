<?php
namespace App\Controllers\Admin;


/**
 * Fee Chalan Family Wise Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */
 


class Fee_chalan_familywise extends MY_Controller {

function __construct(){
		parent::__construct();
		check_permission('admin-fee-chalan-familywise');
	}

/**
 * Index Page for this controller.
 */
public function index()
{
  $data =  $this->data();
  $this->template_data['data'] = $data;
  $this->load->view('fee_chalan_familywise', $this->template_data);
}

// Controller Function
function data() {
    $campus_id = $this->session->userdata('member_campusid');
    $sessionid = $this->session->userdata('member_sessionid');
    $student_data = array();

    // Get campus and system info first
    $campusinfo = $this->db->get_where('campus', array('campus_id' => $campus_id))->row();
    $systemInfo = getSchoolInfo();

    // Get all parents with active students
    $this->db->select('p.*')
        ->from('parents p')
        ->join('students s', 'p.parent_id = s.parent_id')
        ->where('s.status', 1)
        ->where('s.campus_id', $campus_id)
        ->group_by('p.parent_id')->order_by("parent_id", "asc");
    $parents = $this->db->get()->result();

    if (empty($parents)) return $student_data;

    $parent_ids = array_column($parents, 'parent_id');

    // Get all students for these parents
    $students = $this->db->select('*')
        ->from('students')
        ->where_in('parent_id', $parent_ids)
        ->where('status', 1)
        ->get()->result();

    $student_ids = array_column($students, 'student_id');

    // Get class sections in one query
    $class_sec_info = $this->db->select('sc.student_id, cs.class_id, sec.section_name, c.class_name')
        ->from('student_class sc')
        ->join('class_section cs', 'sc.cls_sec_id = cs.cls_sec_id')
        ->join('classes c', 'cs.class_id = c.class_id')
        ->join('sections sec', 'cs.section_id = sec.section_id')
        ->where('sc.session_id', $sessionid)
        ->where_in('sc.student_id', $student_ids)
        ->get()->result_array();

    $class_sections = [];
    foreach ($class_sec_info as $cs) {
        $class_sections[$cs['student_id']] = $cs['class_name'].' '.$cs['section_name'];
    }

    // Get all unpaid fee chalans with fines
    $this->db->select('fc.*, fd.amount as fine_amount')
        ->from('fee_chalan fc')
        ->join('fine_detailold fd', 'fc.chalan_id = fd.chalan_id', 'left')
        ->where_in('fc.student_id', $student_ids)
        ->where('fc.status', 'unpaid')
        ->where('fc.fee_type_id !=', 0);
    $fee_chalans = $this->db->get()->result();

    // Organize chalans by parent and student
    $chalans_by_parent = [];
    foreach ($fee_chalans as $chalan) {
        $parent_id = array_column(
            array_filter($students, function($s) use ($chalan) { 
                return $s->student_id == $chalan->student_id; 
            }), 
            'parent_id'
        )[0];
        
        if (!isset($chalans_by_parent[$parent_id])) {
            $chalans_by_parent[$parent_id] = [];
        }
        $chalans_by_parent[$parent_id][] = $chalan;
    }

    // Get fee types
    $fee_types = $this->db->get('fee_type')->result();
    $fee_type_map = array_column($fee_types, 'fee_type_name', 'fee_type_id');

    // Process each parent
    foreach ($parents as $parent) {
        if (!isset($chalans_by_parent[$parent->parent_id])) continue;

        $parent_chalans = $chalans_by_parent[$parent->parent_id];
        $student_ids = array_unique(array_column($parent_chalans, 'student_id'));

        // Build student info
        $stdinfo = '';
        foreach ($students as $student) {
            if ($student->parent_id == $parent->parent_id) {
                $section = $class_sections[$student->student_id] ?? '';
                $stdinfo .= "{$student->first_name} {$student->last_name} <strong>{$section}</strong>, ";
            }
        }
        $stdinfo = rtrim($stdinfo, ', ');

        // Process fees and fines
        $fee_data = [];
        $fine_total = 0;
        $latest_chalan = null;
        
        foreach ($parent_chalans as $chalan) {
            // Track latest chalan
            if (!$latest_chalan || $chalan->chalan_id > $latest_chalan->chalan_id) {
                $latest_chalan = $chalan;
            }

            // Aggregate fees
            $key = $chalan->fee_month.'|'.$chalan->fee_type_id;
            if (!isset($fee_data[$key])) {
                $fee_data[$key] = [
                    'amount' => 0,
                    'discount' => 0,
                    'fee_month' => $chalan->fee_month,
                    'fee_type_id' => $chalan->fee_type_id
                ];
            }
            $fee_data[$key]['amount'] += $chalan->amount;
            $fee_data[$key]['discount'] += $chalan->discount;

            // Aggregate fines
            $fine_total += $chalan->fine_amount ?? 0;
        }

        // Format fee months
        $student_fee = [];
        foreach ($fee_data as $entry) {
            list($month, $year) = explode('/', $entry['fee_month']);
            $month_name = DateTime::createFromFormat('!m', $month)->format('F');
            $student_fee[] = [
                'amount' => $entry['amount'],
                'discount' => $entry['discount'],
                'fee_month' => $fee_type_map[$entry['fee_type_id']]." ($month_name-$year)"
            ];
        }

        // Format dates from latest chalan
        $issue_date = date('j-M-Y', strtotime($latest_chalan->issue_date));
        $due_date = date('j-M-Y', strtotime($latest_chalan->due_date));
        list($month, $year) = explode('/', $latest_chalan->fee_month);
        $month_name = DateTime::createFromFormat('!m', $month)->format('F');

        // Build final data array
        $student_data[] = [
            'campus_name' => $campusinfo->campus_name ?? '',
            'chalan_no' => $latest_chalan->chalan_id,
            'system_name' => $systemInfo->system_name ?? '',
            'logo' => $systemInfo->logo ?? '',
            'location' => $campusinfo->location ?? '',
            'bank_name' => $campusinfo->bank_name ?? '',
            'bank_address' => $campusinfo->bank_address ?? '',
            'bank_code' => $campusinfo->bank_code ?? '',
            'bank_acc' => $campusinfo->bank_acc ?? '',
            'chalan_h_msg' => $campusinfo->chalan_h_msg ?? '',
            'chalan_f_msg' => $campusinfo->chalan_f_msg ?? '',
            'stdinfo' => $stdinfo,
            'f_name' => $parent->f_name,
            'family_no' => $parent->parent_id,
            'father_contact' => $parent->father_contact,
            'mother_contact' => $parent->mother_contact,
            'emergency_contact' => $parent->emergency_contact,
            'fee_month' => "$month_name-$year",
            'issue_date' => $issue_date,
            'due_date' => $due_date,
            'student_fee' => $student_fee,
            'fee_fine' => [['amount' => $fine_total]]
        ];
    }

    return $student_data;
    //print_r($student_data);
    //exit;
} 


// Helper function to format bank information
private function get_bank_info($campusinfo) {
    return [
        'name' => $campusinfo->bank_name ?? '',
        'address' => $campusinfo->bank_address ?? '',
        'code' => $campusinfo->bank_code ?? '',
        'account' => $campusinfo->bank_acc ?? ''
    ];
}

// Helper function to format contact information
private function format_contacts($row) {
    return [
        'emergency' => $row->emergency_contact ?? '',
        'mother' => $row->mother_contact ?? '',
        'father' => $row->father_contact ?? ''
    ];
}

// Helper function to format dates
private function format_dates($row) {
    return [
        'issue' => date('j-M-Y', strtotime($row->issue_date)),
        'due' => date('j-M-Y', strtotime($row->due_date)),
        'month' => $this->format_fee_month($row->fee_month)
    ];
}

// Helper function to format fee month
private function format_fee_month($fee_month) {
    if (!$fee_month) return '';
    $parts = explode('/', $fee_month);
    return date('F Y', mktime(0, 0, 0, $parts[0], 1, $parts[1]));
}

/**
 * Index Page for this controller.
*/

public function single_copy()
{
  $data =  $this->data();
  $this->template_data['data'] = $data;
  $this->load->view('single_copy_fee_chalan_familywise', $this->template_data);
}

}
// end this file