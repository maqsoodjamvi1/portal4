<?php
namespace App\Controllers\Admin;


/**
 * Students Complaint Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 *filesource
 */



class Emp_attendance_monthlyreport extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-emp-attendance-monthly-report');
	}

	/**
	 * Index Page for this controller.
	 */
	 
	public function index()
	{
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$sessionData = array(
		'campusid' => $campusid,
		'sessionid' => $sessionid
		);
		$this->template_data['sessionData'] = $sessionData;

		$infostudents = $this->db->get('students')->result();
		$this->template_data['infostudents'] = $infostudents;

		$classesinfo = $this->db->get('classes')->result();
		$this->template_data['classesinfo'] = $classesinfo;

		$currentrole = currentUserRoles();

		if(in_array(5, $currentrole)){
			$sectionsclassinfo = teacherSubjectSections();
		}else{
			$sectionsclassinfo = userClassSections();
		}
		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;		

		$this->load->view('emp_attendance_monthlyreport', $this->template_data);
	}

	function get_students_byclass(){
	   
	   $eid = $this->input->post('eid');
	   $session_id = $this->input->post('session_id');
	   $campus_id = $this->input->post('campus_id');
	   $datevalue = $this->input->post('date'); 

		$timestamp = strtotime($datevalue);
		$month = date('m', $timestamp);
		$year = date('Y', $timestamp);


		$list = array();
		$d = date('d', strtotime('last day of this month', strtotime($datevalue))); 

		for ($i = 1; $i <= $d; $i++) {
			//$list[] = $datevalue . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
			$list[] = $datevalue . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
		}		
	   
	   $data = array();
	   $studentsList = '';
	   
		$studentsList .= '<input type="hidden" name="campus_id"  value="'.$campus_id.'">';
	    //$studentsList .= '<input type="hidden" name="class_id"  value="'.$id.'">';
	   
	   {
	      	
	 $users = $this->db->query("select * from users where status=1 AND campus_id=".$campus_id)->result();


	$classstudentsTotal = $this->db->query("select count(id) as totalStudents from users where  status=1 and campus_id = ".$campus_id)->row();
	
	   
	   $timestamp = strtotime($datevalue);
	   $monthyear = date('F Y', $timestamp);	
	
	   $studentsList .= '<div class="">
	   <h1 style="text-align:center;font-size:24px;">Monthly Attendance Report</h1>
	   <div class="row"><div class="col-lg-4"></div><div class="col-lg-4"><h6 style="text-align:center;margin-top:0px;margin-bottom:20px;">'.$monthyear.'</h6></div></div><table class="table table-bordered" style="width:98%;">';
	   $studentsList .= '<tr>';
	   $studentsList .= '<td colspan="2" style=" vertical-align:middle;">Presents Of The Day</td>';
	    foreach ($list as $key => $date) {

	    	$timestamp = strtotime($date);
	   		$currentday = strtotime(date('d-m-Y'));
	   		$month = date('m', $timestamp);
	   		$year = date('Y', $timestamp);
	   		if($timestamp > $currentday) break;

	   		$resulttotalP = $this->db->query("select count(STATUS) as totalP from attendance_employee where status = 'P' AND emp_id IN(SELECT id FROM users WHERE  status=1 AND campus_id = ".$campus_id.") AND date = '".$date."'")->row();
	 
		$studentsList .='<td><span style=" background: green;display: block;color: #fff;text-align: center;margin: 0 auto;">'.$resulttotalP->totalP.'</span></td>';
		}		
		$studentsList .= '</tr>'; 
 		$studentsList .= '<tr>';
		$studentsList .= '<td colspan="2" style=" vertical-align:middle;">Absents Of The Day</td>';
	    foreach ($list as $key => $date) {

	    	$timestamp = strtotime($date);
	   		$currentday = strtotime(date('d-m-Y'));
	   		$month = date('m', $timestamp);
	   		$year = date('Y', $timestamp);
	   		if($timestamp > $currentday) break;
	   		
	   		$resulttotalA = $this->db->query("select count(STATUS) as totalA from attendance_employee where status = 'A' AND emp_id IN(SELECT id FROM users WHERE  status=1 AND campus_id = ".$campus_id.") AND date = '".$date."'")->row();
	 
		$studentsList .='<td><span style=" background: red;display: block;color: #fff;text-align: center;margin: 0 auto;">'.$resulttotalA->totalA.'</span></td>';
		}		
		$studentsList .= '</tr>'; 
 		$studentsList .= '<tr>';
		 $studentsList .= '<td colspan="2" style=" vertical-align:middle;">Leaves Of The Day</td>';
	    foreach ($list as $key => $date) {

	    	$timestamp = strtotime($date);
	   		$currentday = strtotime(date('d-m-Y'));
	   		$month = date('m', $timestamp);
	   		$year = date('Y', $timestamp);
	   		//$day = date('d-m-Y D', $timestamp);
	   		if($timestamp > $currentday) break;

	   		$resulttotalL = $this->db->query("select count(STATUS) as totalL from attendance_employee where status = 'L' AND emp_id IN(SELECT id FROM users WHERE  status=1 AND campus_id = ".$campus_id.") AND date = '".$date."'")->row();
	 
		$studentsList .='<td><span style=" background: #ffc107;display: block;color: #000;text-align: center;margin: 0 auto;">'.$resulttotalL->totalL.'</span></td>';
		}		
	   $studentsList .= '</tr>';
	   $studentsList .= '<tr style="background: #204d74;color: #fff;"><th style="width: 6%; text-align: center;">Photo</th><th style="width:15%;">Name</th>';
	   foreach ($list as $key => $date) {
	   	$timestamp = strtotime($date);
	   	$currentday = strtotime(date('d-m-Y'));
	   	$daydate = date('d', $timestamp);
	   	$dayName = date('D', $timestamp);
	   	if($timestamp > $currentday) break; 
	   		$studentsList .= '<th>'.$daydate.'<br>'.substr($dayName, 0, -1).'</th>';  	
	     }  
	   $studentsList .= '<th>P</th><th>A</th><th>LC</th><th>EL</th><th>L</th></tr>';
  	   $i=1;
	    		  		  
	foreach($users as $row){   
		 
		if($row){

		   $studentName = $row->first_name." ".$row->last_name;	

		   $imgurl = FCPATH."uploads/".$row->photo;
			if($row->photo){   
			if(file_exists($imgurl)){

				$profile_photo = "<img style='width:50px;height:50px;text-align:center;display: block;border-radius: 30px;margin: 0 auto;' src='".base_url("uploads/".$row->photo)."' >";
						
			}else{

				$profile_photo = "<i style='font-size:40px;text-align:center;display:block;' class='fa fa-user'></i>";
			}
			}else{
				
				$profile_photo = "<i style='font-size:40px;text-align:center;display:block;' class='fa fa-user'></i>";
			}

		  $studentsList .= '<tr><td style=" vertical-align:middle; word-break: break-word;"> '.$profile_photo.'</td>';
		  $studentsList .= '<td style=" vertical-align:middle;">'.$studentName.'</td>';
	    foreach ($list as $key => $date) {

	    	$timestamp = strtotime($date);
	   		$currentday = strtotime(date('d-m-Y'));
	   		$month = date('m', $timestamp);
	   		$year = date('Y', $timestamp);
	   		//$day = date('d-m-Y D', $timestamp);
	   		if($timestamp > $currentday) break;

			$this->db->where('emp_id', $row->id);
			$this->db->where('date', $date);
			$attendance_info = $this->db->get('attendance_employee')->row(); 
			//print_r($attendance_info);	
			$attendance_status = '-';
			if($attendance_info){
				if(empty($attendance_info->el_duration) && empty($attendance_info->lc_duration)){
					if($attendance_info->status == 'A'){
						$attendance_status = '<span style="background: red;display: block;color: #fff;width: 20px;
    text-align: center;margin: 0 auto;">'.$attendance_info->status.'</span>';	
					}else{
						$attendance_status = '<span style="background: green;display: block;color: #fff;width: 20px;
    text-align: center;margin: 0 auto;">'.$attendance_info->status.'</span>';
					}
						
				}else{

				if($attendance_info->el_duration > 0 && empty($attendance_info->lc_duration)){
					$attendance_status = '<span style=" background: #ffc107;display: block;color: #000;width: 20px;
    text-align: center;margin: 0 auto;">EL</span>';
				}

				if($attendance_info->lc_duration > 0 && empty($attendance_info->el_duration))
				{
					$attendance_status = '<span style=" background: #ffc107;display: block;color: #000;width: 20px;
    text-align: center;margin: 0 auto;">LC</span>';
				}

				if($attendance_info->lc_duration > 0 && $attendance_info->el_duration > 0)
				{
					$attendance_status = '<span style=" background: #ffc107;display: block;color: #000;width: 20px;
    text-align: center;margin: 0 auto;">LE</span>';
				}

			 }
				

			}
			$studentsList .='<td>'.$attendance_status.'</td>';
		}	
		
		
	$resultP = $this->db->query("select count(STATUS) as totalP from attendance_employee where emp_id =".$row->id." and STATUS = 'P' and Month(date) = ".$month." and Year(date) =".$year)->row();
	
	$resultLC = $this->db->query("select count(STATUS) as totalLC from attendance_employee where emp_id =".$row->id." AND lc_duration > 0 AND STATUS = 'P' and Month(date) = ".$month." and Year(date) =".$year)->row();
	
	$resultL = $this->db->query("select count(STATUS) as totalL from attendance_employee where emp_id =".$row->id." and STATUS = 'L' and Month(date) = ".$month." and Year(date) =".$year)->row();

	$resultEL = $this->db->query("select count(STATUS) as totalEL from attendance_employee where emp_id =".$row->id." AND el_duration > 0 AND STATUS = 'P' and Month(date) = ".$month." and Year(date) =".$year)->row();
	
	$resultA = $this->db->query("select count(STATUS) as totalA from attendance_employee where emp_id =".$row->id." and STATUS = 'A' and Month(date) = ".$month." and Year(date) =".$year)->row();
	//print_r($resultP);
     $studentsList .='<td><span style=" background: #204d74;display: block;color: #fff;width: 20px;text-align: center;margin: 0 auto;border-radius: 10px;">'.$resultP->totalP.'</span></td>
    <td><span style=" background: #204d74;display: block;color: #fff;width: 20px;text-align: center;margin: 0 auto;border-radius: 10px;">'.$resultA->totalA.'</span></td>
    <td><span style=" background: #204d74;display: block;color: #fff;width: 20px;text-align: center;margin: 0 auto;border-radius: 10px;">'.$resultLC->totalLC.'</span></td>
    <td><span style=" background: #204d74;display: block;color: #fff;width: 20px;text-align: center;margin: 0 auto;border-radius: 10px;">'.$resultEL->totalEL.'</span></td>
    <td><span style=" background: #204d74;display: block;color: #fff;width: 20px;text-align: center;margin: 0 auto;border-radius: 10px;">'.$resultL->totalL.'</span></td></tr>';
  }
	$i++; 
 } 
}
$studentsList .= '</table></div><script>
$(function(){
$(".clockpicker").clockpicker();
});	
</script>'; 
$this->output->set_output($studentsList);	
	
}
}
// end this file
