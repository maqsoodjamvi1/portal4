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
 


class Students_vehicle extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-students');
		$this->load->helper(array('form', 'url'));
		$this->load->model('students_model','students');

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

	$this->load->view('students_vehicle', $this->template_data);

	}

function data(){

	$cls_sec_id = $this->input->post('cls_sec_id');
	$campusid = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');
	$schoolinfo = getSchoolInfo();

	$vehicle_info = $this->db->query('SELECT * FROM vehicles WHERE campus_id='.$campusid)->result();	

	//$this->template_data['vechile_info'] = $vechile_info;

	if($cls_sec_id){
		$student_class = $this->db->query('SELECT * FROM student_class WHERE student_id IN(SELECT student_id FROM students WHERE status=1  AND campus_id='.$campusid.') AND session_id ='.$sessionid.' AND cls_sec_id ='.$cls_sec_id.' order by cls_sec_id asc')->result();	
	}else{
		$student_class = $this->db->query('SELECT * FROM student_class WHERE student_id IN(SELECT student_id FROM students WHERE status=1 AND campus_id='.$campusid.') AND session_id ='.$sessionid.' order by cls_sec_id asc')->result();	
	}

	$classSection = $this->db->query('SELECT * FROM class_section WHERE cls_sec_id='.$cls_sec_id)->row();	

	$studentsList = '';
	$currentMonthDisplay = date("M Y");
	$prevMonthDisplay = date("M Y", strtotime("-1 months"));
	$studentsList .= '<table class="table table-striped table-bordered table-hover" id="students-datatable"  style="font-size:10px;width: 100%;"><thead><tr><th style="width: 55px !important;" nowrap>#</th><th style="width: 100px !important;">Reg No</th><th nowrap>Name</th>
             	<th style="width:150px;">Vehicle</th>
             	<th style="width:150px;">Vehicle Fare</th>
              <th style="width:100px;">Action</th>
            </tr>
            </thead>
			<tbody>';
	
	$fee_plans = $this->db->get('fee_plans')->result();

	foreach ($student_class as $studentinfo) {

			$this->db->where('campus_id', $campusid);
			$this->db->where('student_id', $studentinfo->student_id);
			$this->db->where('status', 1);
			$list = $this->db->get('students')->result();

      foreach ($list as $key => $value) {   
      			$vehicle_fare = '';
				$students_vehicle_id = '';
        		
        		$this->db->where('parent_id', $value->parent_id);
				$parentinfo = $this->db->get('parents')->row(); 
			
				$std_vehicle_info = $this->db->query('SELECT * FROM vehicle_students WHERE status=1 and student_id='.$value->student_id)->row();
				if($std_vehicle_info){
					
					$single_vehicle_info = $this->db->query('SELECT * FROM vehicles WHERE vehicle_id='.$std_vehicle_info->vehicle_id)->row();
					$vehicle_fare = $single_vehicle_info->route_fare - $std_vehicle_info->student_t_discount;
					$students_vehicle_id = $std_vehicle_info->vehicle_id;
				}
				
				 $f_name = '';
				 $father_contact = '';
				 $mother_contact = '';
				 $emergency_contact = '';
				 $whatsapp_contact = '';
				 $address = '';
				 $balance = 0;
				 $prevbalance = 0;

				if($parentinfo){
					$address = $parentinfo->address_line1;
					$f_name = $parentinfo->f_name;
					$father_contact = $parentinfo->father_contact;
					$mother_contact = $parentinfo->mother_contact;
					$whatsapp_contact = $parentinfo->whatsapp;
					$emergency_contact = $parentinfo->emergency_contact;
				} 

        $studentsList .= '<tr>
                    <th nowrap><input type="hidden" value="'.$value->student_id.'" id="student_id'.$value->student_id.'" name="student_id">'.$value->student_id.'</th>';
        $studentsList .= '<th style="width: 55px !important;">'.$value->reg_no.'</th>
                    <th nowrap>'.$value->first_name.' '.$value->last_name.'<br>c/o '.$f_name.'</th>
                    <th nowrap>';
        $studentsList .= '<select class="form-control" id="vehicle_id'.$value->student_id.'" name="vehicle_id"><option>select vehicle</option>';

       foreach($vehicle_info as $vehicle_value){ 
        	$studentsList .= '<option ';
        	if($students_vehicle_id == $vehicle_value->vehicle_id){
        		$studentsList .= "selected";
        	}
        	$studentsList .= ' value="'.$vehicle_value->vehicle_id.'">'.$vehicle_value->vehicle_code.'</option>';
        } 

        $studentsList .= '</select></th>';
        $studentsList .= '<th><input type="text" name="vehicle_fare'.$value->student_id.'" id="vehicle_fare'.$value->student_id.'" value="'.$vehicle_fare.'" ></th>';
        $studentsList .= '<th><a  id="save'.$value->student_id.'"  data-id="'.$value->student_id.'" class="btn btn-primary btn-sm">Save</a></tr>';
        $studentsList .= '<script type="text/javascript">
	                $("#vehicle_id'.$value->student_id.'").change(function(){
	                
	                    var vehicle_id = $("#vehicle_id'.$value->student_id.'").val();
	                     $.ajax({
	                        url: "admin.php?c=students_vehicle&m=selectVehicleFee",
	                        type: "POST",
	                        data:{vehicle_id:vehicle_id },
	                        success:function(res){
	                          $("#vehicle_fare'.$value->student_id.'").val(res);
	                          $("#student_cls_fee_label'.$value->student_id.'").html(res);
	                        }
	                     });
	                }); 

	            $("#save'.$value->student_id.'").click(function(){
	                var vehicle_id = $("#vehicle_id'.$value->student_id.'").val();
	                var vehicle_fare = $("#vehicle_fare'.$value->student_id.'").val();
	                var student_id = $("#student_id'.$value->student_id.'").val();
	                  
	                  $.ajax({
	                    url: "admin.php?c=students_vehicle&m=saveStudent",
	                    type: "POST",
	                    data:{student_id: student_id,vehicle_id:vehicle_id,vehicle_fare:vehicle_fare}, 
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
	            </script>';
	           } 
	       }
				$studentsList .= '</tbody></table>';

	echo $studentsList;			
}	

function selectVehicleFee(){
		$campusid = $this->session->userdata('member_campusid');
		$vehicle_id = $this->input->post('vehicle_id');
		
		$amount = 0;
		$vehicle_fee_info = $this->db->query('SELECT route_fare FROM vehicles WHERE campus_id='.$campusid.' AND vehicle_id='.$vehicle_id)->row();
		if($vehicle_fee_info){
			$amount = $vehicle_fee_info->route_fare;
		}
		
		echo $amount; 

	}

function saveStudent(){
	$user_id = $this->session->userdata['member_userid'];
	$date = date('Y-m-d H:i:s');

	$schoolinfo = getSchoolInfo();
	
	$campusid = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');
	
	$now = date('Y-m-d H:i:s');

	$student_id = $this->input->post('student_id');
	$vehicle_fare = $this->input->post('vehicle_fare');
	$vehicle_id = $this->input->post('vehicle_id');

	$vehicle_info = $this->db->query('SELECT * FROM vehicles WHERE vehicle_id='.$vehicle_id)->row();
	$student_t_discount = ($vehicle_info->route_fare - $vehicle_fare);
	
	$this->db->where('student_id', $student_id);
	$this->db->where('vehicle_id', $vehicle_id);
	$stdVehicleInfo = $this->db->get('vehicle_students')->row();
				
	if(empty($stdVehicleInfo)){	
			$data = array(
				'student_id' => $student_id,
				'vehicle_id' => $vehicle_id,
				'student_t_discount' => $student_t_discount,
				'status' => '1',
				'created_date' => $date,
				'user_id' => $user_id
				);
		
			$this->db->insert('vehicle_students', $data);
			print_r($this->db->error());
			$new_chalan_id = $this->db->insert_id();

	}else{

		$data = array(
				'vehicle_id' => $vehicle_id,
				'student_t_discount' => $student_t_discount,
				'updated_date' => $date,
				'user_id' => $user_id
				);

			$this->db->where('student_id', $student_id);
			$this->db->where('vehicle_id', $vehicle_id);
			$this->db->update('vehicle_students', $data);
			
			$new_chalan_id = $this->db->insert_id();
	}

	json_response(array('success' => TRUE, 'msg' => 'Edit Student Success'));
}

}
// end this file
