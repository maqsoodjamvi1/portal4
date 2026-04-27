<?php
namespace App\Controllers\Admin;


/**
 * Top Level Planning Manage
 *
 * @author		Maqsood Jamvi
 * @copyright	Copyright (c) 2008~2099 timesoftsol.com
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Top_level_planning_gradewise extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-top-level-planning');
	} 

	/**
	 * Index Page for this controller.
	 */
	public function index(){
		
			$campus_id = $this->session->userdata('member_campusid');
			$sessionid = $this->session->userdata('member_sessionid');
			$sessionData = array(
			'campusid' => $campus_id,
			'sessionid' => $sessionid
			);
			 
			$this->template_data['sessionData'] = $sessionData;		
			$this->db->where('campus_id', $campus_id);
			$this->db->where('status', 1);
			$classsection_info = $this->db->get('class_section')->result();
			
			$where = "session_id=".$sessionid;
			$this->db->where($where);	
			$terms_session = $this->db->get('terms_session')->result();
			
			foreach ($classsection_info as $classsection) {
			
			$this->db->where('cls_sec_id', $classsection->cls_sec_id);
			$section_subjects = $this->db->get('section_subjects')->result();

			$this->db->where('class_id', $classsection->class_id);
			$classes = $this->db->get('classes')->row();
			
			$terms = array();	
			$resultcard = array();	
			$resulttotal = array();
			$resulttotalpercentage = array();
			$nonacademicresultcard = array();

			foreach ($terms_session as  $value) {
				$this->db->where('term_id', $value->term_id);
			    $term_info = $this->db->get('terms')->row();
				$terms[] = array('terms_name' => $term_info->name);

			foreach($section_subjects as $subect_id){

			  $top_level_planning_data = $this->db->query('SELECT * FROM top_level_planning where  class_id = '.$classsection->class_id.' and subject_id = '.$subect_id->subject_id.' and term_session_id='.$value->term_session_id)->row(); 

	        if($top_level_planning_data){
			  $this->db->where('term_session_id', $top_level_planning_data->term_session_id);

			  $terms_session_info = $this->db->get('terms_session')->row(); 
			  $this->db->where('sid', $subect_id->subject_id);
			  $academicsubjects = $this->db->get('allsubject')->row();
			  
			if($academicsubjects){

			   $resultcard[$academicsubjects->subject_name][$terms_session_info->term_id] = $top_level_planning_data->objective;	 

			   } 

			}
			}
		}

		//$terms = array();
		$where = "session_id=".$sessionid;
		$this->db->where($where);	
		$session_info = $this->db->get('academic_session')->row();
		$data[] = array(
			'class' => $classes->class_name,
			'session_name' => $session_info->session_name,	
			'terms' => $terms,
			'result' => $resultcard,
		); 

		}
			$this->template_data['data'] = $data;
			$this->load->view('top_level_planning_gradewise', $this->template_data);
	}
}

// end this file

