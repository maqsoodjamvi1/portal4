<?php
namespace App\Controllers\Frontend;


/**
 * Family Fee History
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */
 


class Family_datesheet_whatsapp extends MY_Controller {

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
		$this->load->view('family_datesheet_whatsapp', $this->template_data); 
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();


		$this->db->where('status',0);
		$this->db->where('session_id',$sessionid);	
		$this->db->where('campus_id',$campusid);	
		$examinfo = $this->db->get('exam')->row();
		$exam_id = $examinfo->eid;

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

				$student_class = $this->db->query('SELECT * FROM student_class WHERE status=1 and student_id='.$value->student_id.' AND session_id ='.$sessionid.' order by cls_sec_id asc')->row();
				$class = '';
				if($student_class){
					$classSectioninfo = getClassSection($student_class->cls_sec_id);
					$class = $classSectioninfo['sectionclassname'];
				}
				

				$strStudents .= $value->first_name.' '.$value->last_name.' - '.$class.'<br> ';
			}

			$f_name = $row->f_name;
			$pid = $row->parent_id;
			$father_contact = $row->father_contact;
			$mother_contact = $row->mother_contact;
			$emergency_contact = $row->emergency_contact;
			$whatsapp_contact = $row->whatsapp;

			$url = rawurlencode('https://'.$schoolinfo->domain.'.timesoftsol.com/students_datesheet_card/?pid='.$pid.'&session_id='.$sessionid.'&exam_id='.$exam_id);
			$data = array();
			$data['id'] = $nCount;
			$data['f_name'] = $f_name.'<br><small>'.rtrim($strStudents,', ').'</small>';
			$data['f_contacts'] = $father_contact;
			$data['w_contacts'] = '<a  target="_blank" class="btn btn-success btn-xs" href="https://wa.me/'.$whatsapp_contact.'?text='.$url.'"><i class="fab fa-whatsapp"></i> Send</a>';
			$data['m_contacts'] = $mother_contact;

			$response->data[] = $data;
			$nCount++;
		}

		$this->output->set_output(json_encode($response));
	}

	
}
// end this file
