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
 


class Students_bulk_academy_fee extends MY_Controller {

	function __construct(){

		parent::__construct();
		check_permission('admin-academy-students');
		$this->load->helper(array('form', 'url'));
	
	}

	/**
	 * Index Page for this controller.
	*/

	public function index()
	{	
	
	$campus_id = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');
	$schoolinfo = getSchoolInfo();

	$currentrole = currentUserRoles();

	if(in_array(5, $currentrole)){
		$sectionsclassinfo = teacherSubjectSections();
	}else{
		$sectionsclassinfo = userClassSections();
	}

	$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

	$campus_info = $this->db->query('select * from campus WHERE campus_id='.$campus_id)->row();
	$this->template_data['campus_info'] = $campus_info;

	$this->load->view('students_bulk_academy_fee', $this->template_data);

	}

function data(){

	$cls_sec_id = $this->input->post('cls_sec_id');
	$campusid = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');
	$schoolinfo = getSchoolInfo();

	$currentrole = currentUserRoles();

	if(in_array(5, $currentrole)){
		$sectionsclassinfo = teacherSubjectSections();
	}else{
		$sectionsclassinfo = userClassSections();
	}

	$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

	if($cls_sec_id){
		$student_class = $this->db->query('SELECT * FROM student_class WHERE student_id IN(SELECT student_id FROM students WHERE status=1  AND campus_id='.$campusid.') AND session_id ='.$sessionid.' AND cls_sec_id ='.$cls_sec_id.' order by cls_sec_id asc')->result();	
	}else{
		$student_class = $this->db->query('SELECT * FROM student_class WHERE student_id IN(SELECT student_id FROM students WHERE status=1 AND campus_id='.$campusid.') AND session_id ='.$sessionid.' order by cls_sec_id asc')->result();	
	}

	$classSection = $this->db->query('SELECT * FROM class_section WHERE cls_sec_id='.$cls_sec_id)->row();	

	$feeinfoAmount = $this->db->query('select * from fee_amount where fee_type_id = (SELECT fee_type_id from fee_type where is_monthly_fee=1 AND s_flag=1 AND system_id='.$schoolinfo->system_id.') AND campus_id = '.$campusid.' AND  session_id='.$sessionid.' AND class_id='.$classSection->class_id)->row();
	
	$studentsList = '';
	$currentMonthDisplay = date("M Y");
	$prevMonthDisplay = date("M Y", strtotime("-1 months"));
	
	$studentsList .= '<table class="table table-striped table-bordered table-hover" id="students-datatable"  style="font-size:10px;width: 100%;"><thead><tr><th style="width: 55px !important;" nowrap>#</th><th nowrap>Name</th>';

	$subjectClassInfo = $this->db->query('select * from a_class_subjects where class_id='.$classSection->class_id)->result();
         
   foreach ($subjectClassInfo as $key => $groupvalue) {

        $this->db->where('subject_id', $groupvalue->subject_id);
        $this->db->where('class_id', $classSection->class_id);
        $this->db->where('status', 1);
				$classSubjectinfo = $this->db->get('a_class_subjects')->row(); 
				
			if($classSubjectinfo){	

				$subjectGroupInfo = $this->db->query('select * from a_subject_group where cls_sub_id IN(select cls_sub_id from a_class_subjects where cls_sub_id='.$classSubjectinfo->cls_sub_id.')')->result();

				foreach ($subjectGroupInfo as $key => $subjectGroupval) {
				
					$this->db->where('sid', $classSubjectinfo->subject_id);
					$subjectinfo = $this->db->get('allsubject')->row();

					$this->db->where('class_id', $classSubjectinfo->class_id);
					$classinfo = $this->db->get('classes')->row();

					$this->db->where('group_id', $subjectGroupval->group_id);
					$groupinfo = $this->db->get('a_groups')->row();	
         
         	$this->db->where('cls_sub_group_id', $subjectGroupval->cls_sub_group_id);
					$info = $this->db->get('a_group_teacher')->row();

          if($info){

          	$teachersecionArr = $info->tid;
						$tg_id = $info->gt_id;
						$cls_sub_group_id = $info->cls_sub_group_id;
				
						$this->db->where('id', $teachersecionArr);
						$this->db->where('campus_id', $campusid);
						$teacherInfo = $this->db->get('users')->row();

          	$studentsList .= '<td style="font-size:11px;width:150px;">'.$teacherInfo->first_name.' '.$teacherInfo->last_name.' '.$classinfo->class_name." (".$subjectinfo->subject_name." ".$groupinfo->group_name.')</td>';

        }
      

      }

    }

   }

  $studentsList .= '</tr></thead><tbody>';
	
	$fee_plans = $this->db->get('fee_plans')->result();

	foreach ($student_class as $studentinfo) {

			$this->db->where('campus_id', $campusid);
			$this->db->where('student_id', $studentinfo->student_id);
			$this->db->where('status', 1);
			$list = $this->db->get('students')->result();

      foreach ($list as $key => $value) {   

      if($feeinfoAmount){
				$feeAmount = ($feeinfoAmount->amount - $value->discounted_amount);
			}else{
				$feeAmount = '';
			}

        $this->db->where('parent_id', $value->parent_id);
				$parentinfo = $this->db->get('parents')->row();
				//$currentMonth = date('m/Y');
				$currentMonth = date("m/Y");
				$prevMonth = date("m/Y", strtotime("-1 months"));
				
				$feeinfo = $this->db->query('select * from fee_chalan where fee_type_id = (SELECT fee_type_id from fee_type where is_monthly_fee=1 AND s_flag=1 AND system_id='.$schoolinfo->system_id.') AND status="unpaid" AND fee_month="'.$currentMonth.'" AND student_id='.$value->student_id.'  ORDER BY chalan_id DESC')->row();

				$prevfeeinfo = $this->db->query('select * from fee_chalan where fee_type_id = (SELECT fee_type_id from fee_type where is_monthly_fee=1 AND s_flag=1 AND system_id='.$schoolinfo->system_id.') AND status="unpaid" AND fee_month="'.$prevMonth.'" AND student_id='.$value->student_id.'  ORDER BY chalan_id DESC')->row();

				 $f_name = '';
				 $father_contact = '';
				 $mother_contact = '';
				 $emergency_contact = '';
				 $whatsapp_contact = '';
				 $address = '';
				 $balance = 0;
				 $prevbalance = 0;

				 if($feeinfo){
				 	$balance = $feeinfo->amount - $feeinfo->discount;
				 }

				 if($prevfeeinfo){
				 	$prevbalance = $prevfeeinfo->amount - $prevfeeinfo->discount;
				 }
				 
				if($parentinfo){
					$address = $parentinfo->address_line1;
					$f_name = $parentinfo->f_name;
					$father_contact = $parentinfo->father_contact;
					$mother_contact = $parentinfo->mother_contact;
					$whatsapp_contact = $parentinfo->whatsapp;
					$emergency_contact = $parentinfo->emergency_contact;
				} 

        $studentsList .= '<tr><th><input type="hidden" value="'.$value->student_id.'" id="student_id'.$value->student_id.'" name="student_id">'.$value->student_id.'</th>';
        $studentsList .= '<th nowrap>'.$value->first_name.' '.$value->last_name.'<br>c/o '.$f_name.'</th>';

        $subjectClassInfo = $this->db->query('select * from a_class_subjects where class_id='.$classSection->class_id)->result();

         
   	foreach ($subjectClassInfo as $key => $groupvalue) {

        $this->db->where('subject_id', $groupvalue->subject_id);
        $this->db->where('class_id', $classSection->class_id);
				$classSubjectinfo = $this->db->get('a_class_subjects')->row();
				
			if($classSubjectinfo){	

				$subjectGroupInfo = $this->db->query('select * from a_subject_group where cls_sub_id IN(select cls_sub_id from a_class_subjects where cls_sub_id='.$classSubjectinfo->cls_sub_id.')')->result();

				foreach ($subjectGroupInfo as $key => $subjectGroupval) {
				
					$this->db->where('sid', $classSubjectinfo->subject_id);
					$subjectinfo = $this->db->get('allsubject')->row();

					$this->db->where('class_id', $classSubjectinfo->class_id);
					$classinfo = $this->db->get('classes')->row();

					$this->db->where('group_id', $subjectGroupval->group_id);
					$groupinfo = $this->db->get('a_groups')->row();	
         
         	$this->db->where('cls_sub_group_id', $subjectGroupval->cls_sub_group_id);
					$info = $this->db->get('a_group_teacher')->row();

        if($info){
          	$teachersecionArr = $info->tid;
						$tg_id = $info->gt_id;
						$cls_sub_group_id = $info->cls_sub_group_id;
						$group_fee = $info->group_fee;
				
						$this->db->where('id', $teachersecionArr);
						$this->db->where('campus_id', $campusid);
						$teacherInfo = $this->db->get('users')->row();

						$this->db->where('student_id', $value->student_id);
						$this->db->where('session_id', $sessionid);
						$this->db->where('gt_id', $tg_id);
						$a_student_subjects = $this->db->get('a_student_subjects')->row();
						
						$strChecked = '';
						$strReadonly = 'readonly';
						if($a_student_subjects){
							$group_fee = $group_fee - $a_student_subjects->discount_amount;
							if($a_student_subjects->status == 1){
	          		 $strChecked = 'checked';
	          		 $strReadonly = '';
	          	}	
	          	
						}
						

          $studentsList .= '<td style="font-size:11px;"><input id="enable_a_fee_'.$value->student_id.'_'.$tg_id.'" name="enable_a_fee_'.$value->student_id.'_'.$tg_id.'" value="1" id="enable_com_'.$value->student_id.'" type="checkbox" '.$strChecked.' ><input type="text" id="student_a_discount_'.$value->student_id.'_'.$tg_id.'" name="student_a_discount_'.$value->student_id.'_'.$tg_id.'" value="'.$group_fee.'" '.$strReadonly.'  class="form-control">
          	<a id="save'.$value->student_id.'_'.$tg_id.'" data-tg="'.$tg_id.'" data-id="'.$value->student_id.'" class="btn btn-primary btn-xs">Enroll</a>
          <script type="text/javascript">
       			
       			$("#enable_a_fee_'.$value->student_id.'_'.$tg_id.'").on("click", function () {
				        if ($(this).prop("checked")) {
				          $("#student_a_discount_'.$value->student_id.'_'.$tg_id.'").prop("readonly", false);
				        } else {
				          $("#student_a_discount_'.$value->student_id.'_'.$tg_id.'").prop("readonly", true);
				        }
				    }); 

				  $("#save'.$value->student_id.'_'.$tg_id.'").click(function(){
				  	if ($("#enable_a_fee_'.$value->student_id.'_'.$tg_id.'").prop("checked")) {
				  		var status = 1;
				  	}else{
				  		var status = 0;
				  	}
            var student_a_discount = $("#student_a_discount_'.$value->student_id.'_'.$tg_id.'").val();
            var student_id = $(this).data("id");
            var tg_id = $(this).data("tg");
              
              $.ajax({
                url: "admin.php?c=students_bulk_academy_fee&m=saveStudent",
                type: "POST",
                data:{status:status,student_id: student_id,student_a_discount:student_a_discount,tg_id:tg_id}, 
                success:function(res){
                  var json = $.parseJSON(res);
                      if(json.success){
                          toastr.success(json.msg);
                          //location.reload();
                      }else{
                          toastr.error(json.msg);
                      }
                    }
                });
           });

				   </script></td>';

				  }
				  
				  }

				  }

			}
             
      $studentsList .= '</tr>';
      //$studentsList .= '<script type="text/javascript"></script>';
     } 
     }
		$studentsList .= '</tbody></table>';

	echo $studentsList;			
}	

function saveStudent(){

	$user_id = $this->session->userdata['member_userid'];
	$date = date('Y-m-d H:i:s');

	$schoolinfo = getSchoolInfo();
	
	$campusid = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');
	
	$now = date('Y-m-d H:i:s');

	$studentsInfo = $this->input->post('student_id');
	$status = $this->input->post('status');
	$tg_id = $this->input->post('tg_id');
	$student_a_discount = $this->input->post('student_a_discount');

	$this->db->where('student_id', $studentsInfo);
	$this->db->where('session_id', $sessionid);
	$this->db->where('gt_id', $tg_id);
	$a_student_subjects = $this->db->get('a_student_subjects')->row();

	$this->db->where('gt_id', $tg_id);
	$group_teacher_info = $this->db->get('a_group_teacher')->row();

	if(!empty($a_student_subjects)){
		$data = array(
				'discount_amount' => trim($group_teacher_info->group_fee - $student_a_discount),
				'status' => trim($status),
				'updated_date' => $date,
				'user_id' => $user_id
			);

		$this->db->where('student_id', $studentsInfo);
		$this->db->where('session_id', $sessionid);
		$this->db->where('gt_id', $tg_id);
		$this->db->update('a_student_subjects', $data);
	}else{
		$data = array(
			'discount_amount' => trim($group_teacher_info->group_fee - $student_a_discount),
			'status' => trim($status),
			'session_id' => trim($sessionid),
			'student_id' => trim($studentsInfo),
			'gt_id' => $tg_id,
			'status' => trim($status),
			'created_date' => $date,
			'user_id' => $user_id
		);

		$this->db->insert('a_student_subjects', $data);
	}	

	json_response(array('success' => TRUE, 'msg' => 'Edit Student Success'));
}

}
// end this file
