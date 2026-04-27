<?php
namespace App\Controllers\Admin;


/**
 * Students Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */
 


class Students_defaulters_list extends MY_Controller {

	private $column_order = [null, 'first_name', 'last_name', 'father_contact', 'address_line1', 'city'];
    private $column_search = ['student_id', 'parent_id', 'status'];
    private $order = ['student_id' => 'asc'];

    function __construct() {
        parent::__construct();
        check_permission('admin-students-contact-list');
        $this->load->helper(['form', 'url']);
        $this->load->library('parser'); // Correct: Load the Parser LIBRARY
    }

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{	


		$sessionid = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();
		$currentrole = currentUserRoles();

		$sessionInfo = $this->db->query('SELECT * FROM `academic_session` WHERE session_id ='.$sessionid)->row();
		$dateArr = explode('-',$sessionInfo->start_date);
		$session_year = $dateArr[0];

		$months = $this->nb_mois2($sessionInfo->start_date,$sessionInfo->end_date);
		
	
		$this->template_data['months'] = $months;

		if(in_array(5, $currentrole)){
			$sectionsclassinfo = teacherSubjectSections();
		}else{
			$sectionsclassinfo = userClassSections();
		}

		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;	

		$fee_types = $this->db->query('SELECT * FROM `fee_type` WHERE system_id  ='.$schoolinfo->system_id)->result();
		$this->template_data['fee_types'] = $fee_types;	
		
		$this->load->helper('url');
		$this->load->helper('form');
		$this->load->view('students_defaulters_list', $this->template_data);
	}

	public function nb_mois2($date1, $date2)
	{
	    $begin = new DateTime( $date1 );
	    $end = new DateTime( $date2 );
	    $end = $end->modify( '+1 month' );

	    $interval = DateInterval::createFromDateString('1 month');

	    $period = new DatePeriod($begin, $interval, $end);
	    $counter = 0;
	    $monthList = array();
	    foreach($period as $dt) {
	        //$counter++;
	        $monthList[] = array(
	        	'id' => $dt->format("m/Y"),
	        	'value' => $dt->format("M/Y"),
	        );
	         //print_r($monthList);
	    }

	    return $monthList;
	}

	public function nb_mois($date1, $date2)
	{
	    $begin = new DateTime( $date1 );
	    $end = new DateTime( $date2 );
	    $end = $end->modify( '+1 month' );

	    $interval = DateInterval::createFromDateString('1 month');

	    $period = new DatePeriod($begin, $interval, $end);
	    $counter = 0;
	    $monthList = '';
	    foreach($period as $dt) {
	        //$counter++;
	        $monthList .= '"'.$dt->format("m/Y").'",';
	    }

	    return $monthList;
	}

	
	public function data() {
		// log_message('debug', 'Final output: '.json_encode($output));
		// $this->output->set_content_type('application/json')
  //       ->set_output(json_encode($output));
        $this->load->library('parser');
        $campusid = (int)$this->session->userdata('member_campusid');
        $sessionid = (int)$this->session->userdata('member_sessionid');
        $schoolinfo = getSchoolInfo();

        // Build main query
        $this->db->select('students.*, parents.f_name, parents.father_contact, parents.mother_contact, 
                         parents.emergency_contact, parents.whatsapp, parents.address_line1, 
                         classes.class_name, sections.section_name')
             ->from('students')
             ->join('parents', 'parents.parent_id = students.parent_id', 'left')
             ->join('student_class', 'student_class.student_id = students.student_id AND student_class.session_id = '.$sessionid, 'left')
             ->join('class_section', 'class_section.cls_sec_id = student_class.cls_sec_id', 'left')
             ->join('classes', 'classes.class_id = class_section.class_id', 'left')
             ->join('sections', 'sections.section_id = class_section.section_id', 'left')
             ->where('students.campus_id', $campusid)
             ->where('student_class.status', 1)
            ->where('students.status', 1);

        // Add filters
        $this->apply_filters();
        
        // Handle search
        if ($_POST['search']['value']) {
            $this->handle_search();
        }

        // Handle ordering
        $this->handle_ordering();

        // Pagination
        if ($_POST['length'] != -1) {
            $this->db->limit((int)$_POST['length'], (int)$_POST['start']);
        }

        $query = $this->db->get();
        $list = $query->result();

        // Get fee information
        $feeData = $this->get_fee_data();
        $response = $this->build_response($list, $feeData, $schoolinfo);

        $output = [
            "draw" => (int)$_POST['draw'],
            "recordsTotal" => $this->count_all(),
            "recordsFiltered" => $this->count_filtered(),
            "data" => $response,
        ];

        $this->output->set_output(json_encode($output)); 
    }

    private function apply_filters() {
        if ($this->input->post('status')) {
            $this->db->where('students.status', (int)$this->input->post('status'));
        }
        if ($this->input->post('student_id')) {
            $this->db->where('students.student_id', (int)$this->input->post('student_id'));
        }
        if ($this->input->post('parent_id')) {
            $this->db->where('students.parent_id', (int)$this->input->post('parent_id'));
        }
        $cls_sec_id = $this->input->post('cls_sec_id');
        if ($cls_sec_id && $cls_sec_id !== 'all') {
            $this->db->where('student_class.cls_sec_id', (int)$cls_sec_id);
        }
    }

    private function handle_search() {
        $search = $_POST['search']['value'];
        $this->db->group_start();
        foreach ($this->column_search as $item) {
            $this->db->or_like($item, $search);
        }
        $this->db->group_end();
    }

    private function handle_ordering() {
        if (isset($_POST['order'])) {
            $order_col = $this->column_order[$_POST['order']['0']['column']];
            $order_dir = $_POST['order']['0']['dir'];
            $this->db->order_by($order_col, $order_dir);
        } else {
            $this->db->order_by(key($this->order), $this->order[key($this->order)]);
        }
    }

    private function get_fee_data() {
        $feeMonth = $this->input->post('month') ?: date('m/Y');
        $fee_type_id = $this->input->post('fee_type') ?: '';

        $this->db->select('student_id, SUM(amount-discount) AS total_amount, SUM(discount) AS total_discount,
                         SUM(CASE WHEN fee_month = "'.$feeMonth.'" THEN (amount - discount) ELSE 0 END) AS current_month_unpaid')
             ->from('fee_chalan')
             ->where('status', 'UnPaid');

        if ($fee_type_id) {
            $this->db->where('fee_type_id', $fee_type_id);
        }

        return $this->db->group_by('student_id')
                      ->get()
                      ->result_array();
    }

    private function build_response($list, $feeData, $schoolinfo) {
        $feeLookup = array_column($feeData, null, 'student_id');
        $response = [];
        $no = $_POST['start'] ?? 0;

        foreach ($list as $row) {
            $fee = $feeLookup[$row->student_id] ?? null;
            if (!$fee || !$fee['current_month_unpaid']) continue;

            $no++;
            $response[] = $this->build_row($row, $fee, $schoolinfo, $no);
        }

        return $response;
    }

    private function build_row($row, $fee, $schoolinfo, $no) {
        $smsData = [
            'first_name' => $row->first_name,
            'last_name' => $row->last_name,
            'father_name' => $row->f_name,
            'class' => $row->class_name."(".$row->section_name.")",
            'balance' => $fee['total_amount'],
            'date' => date('Y-m-d')
        ];

        return [
            'id' => $no,
            'profile_photo' => $this->get_profile_image($row),
           // 'name' => "{$row->first_name} {$row->last_name}",
            'f_name' => $row->f_name,
           // 'address' => $row->address_line1,
            'class' => $row->class_name."(".$row->section_name.")",
            'section' => $row->section_name,
           'f_contacts' => $this->build_contact_link($row->father_contact, $smsData, $schoolinfo),
            'm_contacts' => $row->mother_contact,
            'e_contacts' => $row->emergency_contact,
            'w_contacts' => $row->whatsapp,
            'monthly_unpaid' => $fee['current_month_unpaid'],
            'previous_balance' => ($fee['total_amount'] - $fee['current_month_unpaid']),
            'payable' => $fee['total_amount'],
            'columnName' => $this->input->post('month') ?: date('m/Y'),
            'monthly_unpaid' => (float)$fee['current_month_unpaid'],
        'previous_balance' => (float)($fee['total_amount'] - $fee['current_month_unpaid']),
        'payable' => (float)$fee['total_amount'],
        // Ensure valid UTF-8 encoding
        'name' => utf8_encode($row->first_name . " " . $row->last_name),
        'address' => utf8_encode($row->address_line1),
        ];
    }

    private function get_profile_image($row) {
        if (!empty($row->profile_photo) && file_exists(FCPATH."uploads/".$row->profile_photo)) {
            return '<img src="'.base_url("uploads/".$row->profile_photo).'" style="width:50px;height:50px;border-radius:30px;margin:0 auto;">';
        }
        return '<i class="fa fa-user" style="font-size:40px;text-align:center;display:block;"></i>';
    }

    private function build_contact_link($contact, $smsData, $schoolinfo) {
        // Now using the properly loaded Parser library
        $campusid = (int)$this->session->userdata('member_campusid');
        
        $this->db->where('campus_id', $campusid);
		$campusinfo = $this->db->get('campus')->row();
        
        $message = $this->parser->parse_string($campusinfo->student_fee_sms, $smsData, true);
        return '<a href="https://wa.me/'.$contact.'?text='.urlencode($message).'">'.$contact.'</a>';
    }

    private function count_all() {
        return $this->db->where('campus_id', (int)$this->session->userdata('member_campusid'))
                      ->count_all_results('students');
    }

  private function count_filtered() {
    $campusid = (int)$this->session->userdata('member_campusid');
    $sessionid = (int)$this->session->userdata('member_sessionid');

    // Replicate main query structure
    $this->db->select('COUNT(DISTINCT students.student_id) as total')
        ->from('students')
        ->join('student_class', 'student_class.student_id = students.student_id AND student_class.session_id = '.$sessionid, 'left')
        ->join('parents', 'parents.parent_id = students.parent_id', 'left')
        ->where('students.campus_id', $campusid)
        ->where('student_class.status', 1)
        ->where('students.status', 1);

    $this->apply_filters();
    
    // Handle search
    if ($_POST['search']['value']) {
        $this->handle_search();
    }

    $query = $this->db->get();
    return $query->row()->total;
}


public function _remap($method) {
    try {
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            show_404();
        }
    } catch (Exception $e) {
        log_message('error', $e->getMessage());
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['error' => 'Server error']));
    }
}

	
}
// end this file
