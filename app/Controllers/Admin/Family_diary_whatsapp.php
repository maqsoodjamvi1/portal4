<?php
namespace App\Controllers\Admin;


/**
 * Family Fee History
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */
 


class Family_diary_whatsapp extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-classdairy');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{	
		$currentrole = currentUserRoles();

		if(in_array(5, $currentrole)){
			$sectionsclassinfo = teacherSubjectSections();
		}else{
			$sectionsclassinfo = userClassSections();
		}

		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;	
		
		$this->load->helper('url');
		$this->load->helper('form');
		$this->load->view('family_diary_whatsapp', $this->template_data); 
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		$this->db->select('count(A.parent_id) as ccount', FALSE);
		$this->db->from('parents A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campusid) . ')');
		$this->db->where('(A.parent_id IN(select parent_id from students where status=1))');
		if($keyword){
			$this->db->where('(A.f_name=' . $this->db->escape($keyword) . ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

	
		$this->db->select('A.*');
		$this->db->from('parents A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campusid) . ')');
		$this->db->where('(A.parent_id IN(select parent_id from students where status=1))');
		if($keyword){
			$this->db->where('(A.f_name=' . $this->db->escape($keyword)  . ')');
		}
		$this->db->order_by('A.parent_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		$nCount = 1;
		foreach($results as $row){

			$studentsList = $this->db->query('select * from students where status=1 and parent_id='.$row->parent_id)->result();
			$strStudents = '';
			foreach ($studentsList as $key => $value) {
				$strStudents .= $value->first_name.' '.$value->last_name.', ';
			}

			$f_name = $row->f_name;
			$father_contact = $row->father_contact;
			$mother_contact = $row->mother_contact;
			$emergency_contact = $row->emergency_contact;
			$whatsapp_contact = $row->whatsapp;
			$url = rawurlencode('https://'.$schoolinfo->domain.'.timesoftsol.com/students_diary_detail/?parent_id='.$row->parent_id.'&campus_id='.$campusid);
			$data = array();
			$data['id'] = $nCount;
			$data['f_name'] = $f_name.'<br><small>'.rtrim($strStudents,', ').'</small>';
			$data['f_contacts'] = $father_contact;
			$data['w_contacts'] = '<a href="https://wa.me/'.$whatsapp_contact.'?text='.$url.'">'.$whatsapp_contact.'</a>';
			$data['m_contacts'] = $mother_contact;

			$response->data[] = $data;
			$nCount++;
		}

		$this->output->set_output(json_encode($response));
	}

	
}
// end this file
