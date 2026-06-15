<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;


/**
 * Fee Amount Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class A_fee_amount extends BaseController {
	protected $session;
	protected $db;

	public function __construct(){
		$this->session = session();
		$this->db = \Config\Database::connect();
	}

	/**
	 * Index Page for this controller.
	 */

	public function index()
	{
		return view('admin/a_fee_amount');
	}

	public function data(){
		$campusid = $this->session->get('member_campusid');
		
		$infoteachers = $this->db->table('users')
		    ->where(['campus_id' => (int) $campusid, 'status' => 1])
		    ->whereIn('id', static function ($builder) {
		        return $builder->select('userID')->from('user_roles')->where('roleID', 5);
		    })
		    ->get()
		    ->getResultArray();

		$this->template_data['infoteachers'] = $infoteachers;

		$subjectClassInfo = $this->db->table('a_subject_group')
		    ->where('campus_id', $campusid)
		    ->get()
		    ->getResult();
		
		$data = "<style>
			.tdclass{
		    	padding:3px 8px;text-align:center;
		    }
		    .verticalTableHeader {
			    text-align:center;
			    white-space:nowrap;
			    g-origin:50% 50%;
			    -webkit-transform: rotate(90deg);
			    -moz-transform: rotate(90deg);
			    -ms-transform: rotate(90deg);
			    -o-transform: rotate(90deg);
			    transform: rotate(90deg);
			    
			}
			.verticalTableHeader p {
			    margin:0 -100% ;
			    display:inline-block;
			}
			.verticalTableHeader p:before{
			    content:'';
			    width:0;
			    padding-top:110%;
			    display:inline-block;
			    vertical-align:middle;
			}
			.table-box {
				overflow: scroll;
				height: 500px;	
			}
			table {width: 100%;}
			table th {	padding: 7px;background-color: #ddd;}
			table td {}
			table tr th{position: sticky;left: 0;}
		</style>";
	
		$data .= '<div class="table-box"><table border="1"><tr><th>Group</th>';
		 		$data .= '<th>Fee Amount</th>';
		 		$data .= '<th>Teacher Percentage</th>';
				
		 	$data .= '</tr>';	
		
			if(isset($subjectClassInfo)){
				foreach ($subjectClassInfo as $key => $subjectClassvalue) {

				$classSubjectinfo = $this->db->table('a_class_subjects')
				    ->where('cls_sub_id', $subjectClassvalue->cls_sub_id)
				    ->get()
				    ->getRow();


				
				$subjectinfo = $this->db->table('allsubject')
				    ->where('sid', $classSubjectinfo->subject_id)
				    ->get()
				    ->getRow();
							
				
				$classinfo = $this->db->table('classes')
				    ->where('class_id', $classSubjectinfo->class_id)
				    ->get()
				    ->getRow();

				$groupinfo = $this->db->table('a_groups')
				    ->where('group_id', $subjectClassvalue->group_id)
				    ->get()
				    ->getRow();	

				
				$info = $this->db->table('a_group_teacher')
				    ->where('cls_sub_group_id', $subjectClassvalue->cls_sub_group_id)
				    ->get()
				    ->getRow();
 
				$teachersecionArr = 0;
				if($info){
					$teachersecionArr = $info->tid;
					$tg_id = $info->gt_id;
				
				$teacherInfo = $this->db->table('users')
				    ->where('id', $teachersecionArr)
				    ->where('campus_id', $campusid)
				    ->get()
				    ->getRow();
				
				$data .= '<tr><th  class="tdclass"><input type="hidden" name="tg_id[]" value="'.$tg_id.'">'.$teacherInfo->first_name.' '.$teacherInfo->last_name.' '.$classinfo->class_name." (".$subjectinfo->subject_name." ".$groupinfo->group_name.')</th>';
			
				$data .= "<td class=\"tdclass\"><input class=\"form-control\" type=\"text\" value=\"{$info->group_fee}\" name=\"{$tg_id}_group_fee\"></td>";
				$data .= '<td   class="tdclass"><input class="form-control" type="text"  value="'.$info->teacher_per.'" name="'.$tg_id.'_teacher_percentage"></td>';
					
				$data .= '</tr>';	
				 }
				 } 
			 } 				
			$data .= '</table></div>';
		return $data;
	}

	public function add(){
		check_permission('admin-add-fee-amount');
		$campus_id = $this->session->get('member_campusid');
		$session_id = $this->session->get('member_sessionid');
		$schoolinfo = getSchoolInfo();

		$classesinfo = $this->db->table('classes')->get()->getResult();
		$this->template_data['classesinfo'] = $classesinfo;

		$info = $this->db->get('a_fee_amount')->row();
		$this->template_data['info'] = $info;

		$this->db->where('system_id', $schoolinfo->system_id);
		$academic_sessioninfo = $this->db->get('academic_session')->result();
		$this->template_data['academic_sessioninfo'] = $academic_sessioninfo;

		$fee_type_info = $this->db->table('fee_type')->get()->getResult();
		$this->template_data['fee_type_info'] = $fee_type_info;

		return view('admin/a_fee_amount_edit', $this->template_data);
	}

	public function edit(){

		check_permission('admin-edit-fee-amount');
		$amount_id = intval($this->request->getGet('id'));

		$info = $this->db->table('fee_amount')
		    ->where('amount_id', $amount_id)
		    ->get()
		    ->getRow();
		$this->template_data['info'] = $info;

		$fee_type_info = $this->db->table('fee_type')->get()->getResult();
		$this->template_data['fee_type_info'] = $fee_type_info;

		$classesinfo = $this->db->table('classes')->get()->getResult();
		$this->template_data['classesinfo'] = $classesinfo;

		return view('admin/a_fee_amount_edit', $this->template_data);
	}


	public function save(){
		$user_id = $this->session->get('member_userid');
		$date = date('Y-m-d');
		$campus_id = $this->session->get('member_campusid');
		$session_id = $this->session->get('member_sessionid');
		$schoolinfo = getSchoolInfo();
	
		$tg_ids = $this->request->getPost('tg_id');
		
		foreach($tg_ids as $tg_id){

			$teacher_percentage = $this->request->getPost($tg_id."_teacher_percentage");
			$group_fee = $this->request->getPost($tg_id."_group_fee");
			
			if($tg_id > 0){

				$data = array(
				'teacher_per' => $teacher_percentage,
				'group_fee' => $group_fee,
				'user_id' => $user_id,
				'updated_date' => $date
				);

				$this->db->where('gt_id', $tg_id);
				$this->db->update('a_group_teacher', $data);
				
			}
			
		} 

		json_response(array('success' => TRUE, 'msg' => 'Update Fee Amount Success'));
	}

}

// end this file