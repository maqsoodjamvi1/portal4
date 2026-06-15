<?php
namespace App\Controllers\Admin;


/**
 * Bill Plan Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Bill_plan_months extends MY_Controller { 

	function __construct(){
		parent::__construct();
		check_permission('admin-bill-plan-months');
		$this->load->library('session');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('bill_plan_months', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campus_id = (int) $this->session->userdata('member_campusid');

		$this->db->select('count(A.plan_month_id) as ccount', false);
		$this->db->from('bill_plan_months A');
		$this->db->where('A.campus_id', $campus_id);
		$this->db->where('A.status', 1);

		$q = $this->db->get()->row();
		$response->recordsTotal = (int) ($q->ccount ?? 0);

		$this->db->select('A.*');
		$this->db->from('bill_plan_months A');
		$this->db->where('A.campus_id', $campus_id);
		$this->db->where('A.status', 1);

		$this->db->order_by('A.plan_month_id', 'desc');
		$this->db->limit((int) $this->input->post('length'), (int) $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = [];
		foreach ($results as $row) {
			$this->db->where('plan_id', (int) ($row->bill_plan_id ?? 0));
			$billPlansinfo = $this->db->get('bill_plans')->row();

			$response->data[] = [
				'id'        => $row->plan_month_id,
				'plan_name' => $billPlansinfo->plan_name ?? '',
				'month'     => $row->month,
			];
		}

		$this->output->set_output(json_encode($response));
	}

	function months($month_format="M"){
    $months =  [];
    for ($i = 1; $i <=12; $i++) {
        $months[] = date($month_format, mktime(0,0,0,$i));
    }
    return $months;
	}

	function data2(){
		$campusid = $this->session->userdata('member_campusid');
		$schoolinfo = getSchoolInfo();
		$months = $this->months();

		$bill_plan_info = $this->db->get('bill_plans')->result();
		// print_r($bill_plan_info);
		// exit;
		$data = '<p>Select checkbox to save bill months</p>';

		$data .= '<table class="table"><tr><th></th>';
          		if(isset($months)){
				foreach ($months as  $month) { 
            		$data .= '<th><input type="hidden" name="campus_id[]"  value="'.$month.'"  />'.$month.'</th>';
            	 } 
            } 
        $data .= '</tr>';
          	if(isset($bill_plan_info)){ 
				foreach ($bill_plan_info as  $bill_plan) { 
					
					$data .= '<tr><td><input type="hidden" name="campus_id[]"  value="'.$bill_plan->plan_id.'"  />'.$bill_plan->plan_name.'</td>';
              	if(isset($months)){
					foreach ($months as  $month) { 

						$this->db->where('campus_id', $campusid);
						$this->db->where('month', $month);
						$this->db->where('bill_plan_id', $bill_plan->plan_id);
						$bill_plan_months = $this->db->get('bill_plan_months')->row();
						
						if($bill_plan_months)
						{
            				$data .= '<td><input type="checkbox" ';
            				if($bill_plan_months->status == 1){
            					$data .= ' checked ';
            				}
            				$data .= ' class="setClassSub setlock_'.$bill_plan->plan_id.'"  name="'.$month.'_'.$bill_plan->plan_id.'_campus[]"  value="'.$month.'_'.$bill_plan->plan_id.'"  /></td>';
            		}else{
            				$data .= '<td><input type="checkbox" class="setClassSub setlock_'.$bill_plan->plan_id.'"  name="'.$month.'_'.$bill_plan->plan_id.'_campus[]"  value="'.$month.'_'.$bill_plan->plan_id.'"  /></td>';
            		}
            	 	} 
             	} 
              	$data .= '</tr>';
              	} 
              } 
          	
        $data .= '</table><script type="text/javascript">
		$(function(){
         $(".setClassSub").on("change",function() {
            
            if(this.checked){
            	var status = 1;
            }else{
            	var status = 0;
            }

            var plan_month_id = $(this).val();

            $.ajax({
                type: "POST",
                url: "' . base_url('admin/bill_plan_months/updateBillPlanMonth') . '", 
                data: {plan_month_id:plan_month_id,status:status},
                success:function(res){
            		toastr.success(res.msg);
			  	} 
            });

           });  
      }); 
      </script>';  
 	print_r($data);
 	exit;
		$this->output->set_output($data);
	}

	function updateBillPlanMonth(){
		$campusid = $this->session->userdata('member_campusid');
		$status = $this->input->post('status');
		$plan_month_id = $this->input->post('plan_month_id');
		$planMonthArr = explode('_', $plan_month_id);
		

		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d H:i:s');

		$month = $planMonthArr[0];
		$plan_id = $planMonthArr[1];

		$this->db->where('bill_plan_id', $plan_id);
		$this->db->where('campus_id', $campusid);
		$this->db->where('month', $month);
		$billmonthplan = $this->db->get('bill_plan_months')->row();

		//print_r($feemonthplan);

		 if($billmonthplan){

			$data = array(
				'user_id' => $user_id,
				'updated_date' => $date,
				'status' => $status
			);

			$this->db->where('bill_plan_id', $plan_id);
			$this->db->where('campus_id', $campusid);
			$this->db->where('month', $month);
			$this->db->update('bill_plan_months', $data);

		}else{
			$data = array(
				'bill_plan_id' => $plan_id,
				'month' =>  $month,
				'campus_id' =>  $campusid,
				'user_id' => $user_id,
				'created_date' => $date,
				'status' => 1
			);
			$this->db->insert('bill_plan_months', $data);
		}
		
		json_response(array('success' => TRUE, 'msg' => 'Add Bill Plan Months Success'));
	}

	function add(){
		check_permission('admin-add-bill-plan-months');
		$campusid = $this->session->userdata('member_campusid');
		
		$this->db->where('campus_id', $campusid);
		$classsectioninfo = $this->db->get('class_section')->result();
		$sectionsclassinfo = array();

		foreach($classsectioninfo as $section){
		
		$this->db->where('class_id', $section->class_id);
		$classinfo = $this->db->get('classes')->row();

		$this->db->where('section_id', $section->section_id);
		$sectioninfo = $this->db->get('sections')->row();
		
		$sectionsclassinfo[] = array(
		'section_id' => $section->cls_sec_id,
		'sectionclassname' => $classinfo->class_name." (".$sectioninfo->section_name.")"
		);
		
		}
		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;		

	   	$subjectinfo = $this->db->get('allsubject')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;

		$this->load->view('bill_plan_months_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-bill-plan-months');
		$id = intval($this->input->get('id'));
		$campusid = $this->session->userdata('member_campusid');

		$this->db->where('cs_id', $id);
		$info = $this->db->get('section_subjects')->row();
		$this->template_data['info'] = $info;	
		
		$this->db->where('campus_id', $campusid);
		$classsectioninfo = $this->db->get('class_section')->result();
		$sectionsclassinfo = array();
		foreach($classsectioninfo as $section){
		
		$this->db->where('class_id', $section->class_id);
		$classinfo = $this->db->get('classes')->row();

		$this->db->where('section_id', $section->section_id);
		$sectioninfo = $this->db->get('sections')->row();
		
		$sectionsclassinfo[] = array(
		'section_id' => $section->cls_sec_id,
		'sectionclassname' => $classinfo->class_name." (".$sectioninfo->section_name.")"
		);
		
		}
		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;		
		
		$subjectinfo = $this->db->get('allsubject')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;
		
		$this->load->view('bill_plan_months_edit', $this->template_data);
	}



	function save(){
		$id = intval($this->input->post('id'));
		$campus_id = $this->session->userdata['member_campusid'];
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');
		$schoolinfo = getSchoolInfo();
		$section_ids = $this->input->post('section_id');	
		$class_ids = $this->input->post('class_id');
		$cls_sec_ids = $this->input->post('cls_sec_id');	
				
		check_permission('admin-add-bill-plan-months');
		$this->db->trans_begin();
		
		$new_user_id = $this->db->insert_id();
		$this->db->trans_complete();
		$this->db->where('system_id', $schoolinfo->system_id);
		$subjects_info = $this->db->get('allsubject')->row();

		json_response(array('success' => TRUE, 'msg' => 'Add Bill Plan Months Success'));
		

	}

}
// end this file
