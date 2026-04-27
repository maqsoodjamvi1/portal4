<?php
namespace App\Controllers\Admin;


/**
 * Test Result Card Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
*/



class Test_result_card extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-test-result-cards');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index(){
		$campus_id = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();

		$where = "session_id=".$sessionid." AND campus_id=".$campus_id;
		$this->db->where($where);	
		$test_series = $this->db->get('test_series')->result();

		$currentrole = currentUserRoles();

		if(in_array(5, $currentrole)){
			$sectionsclassinfo = teacherSubjectSections();
		}else{
			$sectionsclassinfo = userClassSections();
		}

		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

		$this->template_data['test_series'] = $test_series;
		$this->load->view('test_result_card', $this->template_data);
	}


public function card()
    {
        // load any filters you need here
        return view('admin/test_result_card');
    }

    public function cardData()
    {
        // return HTML or JSON for your cards via AJAX
        return $this->response->setJSON(['ok' => true, 'items' => []]);
    }

	public function grade($marks){
		$grade ='';
		$schoolinfo = getSchoolInfo();

		$gradingPolicyInfo = $this->db->query('SELECT * FROM grading_policy WHERE system_id= '.$schoolinfo->system_id.' AND '.$marks.' BETWEEN mark_from AND mark_to ')->row();	
		return $gradingPolicyInfo;
	}

	
}
// end this file
