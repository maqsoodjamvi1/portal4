<?php
namespace App\Controllers\Admin;


/**
 * Hostel Room Beds Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */



class H_student_beds extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-room-beds');
		$this->load->library('session');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		
		$this->load->view('h_student_beds', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campusid = $this->session->userdata('member_campusid');
		$schoolinfo = getSchoolInfo();

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		
		$this->db->select('count(A.room_bed_id) as ccount', FALSE);
		$this->db->from('h_room_beds A');
		$this->db->where('(A.block_room_id IN(select block_room_id from h_block_rooms where status=1 AND campus_id=' . $this->db->escape($campusid) . '))');
				
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		$this->db->select('A.*');
		$this->db->from('h_room_beds A');
		$this->db->where('(A.block_room_id IN(select block_room_id from h_block_rooms where status=1 AND campus_id=' . $this->db->escape($campusid) . '))');
		
		$this->db->order_by('A.room_bed_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
			
			$this->db->where('block_room_id', $row->block_room_id);
			$blockRoomsInfo = $this->db->get('h_block_rooms')->row();

			$this->db->where('block_id', $blockRoomsInfo->block_id);
			$blockInfo = $this->db->get('h_blocks')->row();

			$this->db->where('room_id', $blockRoomsInfo->room_id);
			$roomsInfo = $this->db->get('h_rooms')->row();	
		
			$data = array();
			$data['id'] = $row->room_bed_id;
			$data['block_name'] = $blockInfo->block_name;
			$data['room_name'] = $roomsInfo->room_name;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}


	function data2(){
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();
		
		$this->db->where('campus_id', $campusid);
		$this->db->where('status', 1);
		$blockRoomsinfo = $this->db->get('h_block_rooms')->result();
		$blocksRoomsinfo = array();
		foreach($blockRoomsinfo as $blockroom){
		
		$this->db->where('block_id', $blockroom->block_id);
		$blockinfo = $this->db->get('h_blocks')->row();

	   $this->db->where('room_id', $blockroom->room_id);
	   $roominfo = $this->db->get('h_rooms')->row();
		
		$blocksRoomsinfo[] = array(
		'block_room_id' => $blockroom->block_room_id,
		'blocroomname' => $roominfo->room_name." (".$blockinfo->block_name.")",
		'room_no' => $blockroom->room_no
		);
		
		}
		//$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;		
		$this->db->where('campus_id', $campusid);
	  	$bedsinfo = $this->db->get('h_beds')->result();
		$data = '';
		$data .= '<script src="'.base_url().'resource/js/jquery.autocomplete.js"></script><section class="section2">
		<div class="table-box"><table class="table table-bordered" style="margin-bottom:0px;"><thead><tr class="header"><th style="width:10%"></th>';
          	if(isset($bedsinfo)){
				foreach ($bedsinfo as  $bedvalue) { 
            		$data .= '<th style="width:15%"><input type="hidden" name="subjects[]"  value="'.$bedvalue->bed_id.'"  />'.$bedvalue->bed_no.'</th>';
            	 } 
            } 
        $data .= '</tr></thead><tbody>';
          	if(isset($blocksRoomsinfo)){
				foreach ($blocksRoomsinfo as  $roomvalue) { 
				$data .= '<tr><th style="line-height:1;width:100px;"><input type="hidden" name="sections[]"  value="'.$roomvalue["block_room_id"].'"  />Room# '.$roomvalue["room_no"].' <br>'.$roomvalue["blocroomname"].'
				</th>';
              	if(isset($bedsinfo)){ 
					foreach ($bedsinfo as  $bedvalue) { 

						$this->db->where('bed_id', $bedvalue->bed_id);
						$this->db->where('status', 1);
						$this->db->where('block_room_id', $roomvalue["block_room_id"]);
						$roomBeds = $this->db->get('h_room_beds')->row();

					$data .= '<td  style="text-align: left;vertical-align:middle;padding:3px 8px;line-height:1;font-size: 14px !important;">';
					if($roomBeds){

						$bed_fee = '';		
							
						$this->db->where('block_room_id', $roomvalue["block_room_id"]);
						$h_block_rooms_info = $this->db->get('h_block_rooms')->row();	
							
						$h_fee_amount_info = $this->db->query('select * from h_fee_amount where block_id='.$h_block_rooms_info->block_id.' AND room_id='.$h_block_rooms_info->room_id.' AND h_fee_type_id = (select fee_type_id from fee_type where is_monthly_fee=1 and h_flag=1 and session_id="'.$sessionid.'" AND system_id='.$schoolinfo->system_id.')')->row();

						if($h_fee_amount_info){
							$bed_fee = $h_fee_amount_info->amount;
						}

						$this->db->where('status', 1);
						$this->db->where('session_id', $sessionid);
						$this->db->where('bed_id', $bedvalue->bed_id);
						$this->db->where('block_room_id', $roomvalue["block_room_id"]);
						$h_student_beds = $this->db->get('h_student_bed')->row();

						if($h_student_beds){
							if($bed_fee != 0){
								$bed_fee = ($h_fee_amount_info->amount - $h_student_beds->student_h_discount);
							}	
							
							$this->db->where('student_id', $h_student_beds->student_id);
							$studentInfo = $this->db->get('students')->row();

							$this->db->where('parent_id', $studentInfo->parent_id);
							$parentInfo = $this->db->get('parents')->row();

							$unpaidFee = $this->db->query('SELECT SUM(amount-discount) as total FROM fee_chalan WHERE student_id='.$h_student_beds->student_id.' AND status="unpaid" AND fee_type_id IN(SELECT fee_type_id FROM fee_type WHERE system_id='.$schoolinfo->system_id.')')->row();
							
							$data .= '<input type="hidden" value="'.$h_student_beds->student_id.'" id="student_id_'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'" ><i class="fa fa-bed" style="font-size:16px;color:red;display:block;float:left;"></i><p style="float: left;margin-left: 15px;"> '.$studentInfo->first_name.' '.$studentInfo->last_name.' </p>';
							if(!empty($parentInfo->father_contact)){
							$data .= '<p style="float: left;width:100%;margin-top: 10px;margin-left: 0px;">'.$parentInfo->father_contact.'<a href="/admin.php#/students?m=edit&id='.$h_student_beds->student_id.'"><i class="fas fa-edit" style="font-size:16px;color:red;display:block;margin-left: 10px;float:right;cursor:pointer;"></i></a> </p>';
							}
							$data .= '<p style="float: left;width:100%;margin-top:10px;">Bed Fee: '.$bed_fee.'/- <a data-bs-toggle="modal" id="#update_fee'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'" data-bs-target="#update_fee'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'" data-room_id="'.$roomvalue["block_room_id"].'" data-bed_id="'.$bedvalue->bed_id.'" ><i style="font-size:16px;color:red;display:block;margin-left: 10px;float:right;cursor:pointer;" class="fa fa-edit"></i></a></p>';
							if(!empty($unpaidFee->total)){
								$data .= '<p style="float: left;width:100%;margin-top:10px;">Balance: '.$unpaidFee->total.'/- <a href="/admin.php#/fee_chalan_pay"><i style="font-size:16px;color:red;display:block;margin-left: 10px;float:right;cursor:pointer;" class="far fa-money-bill-alt" aria-hidden="true"></i></a></p>';
							}
							if(!empty($studentInfo->promise_date)){
								$data .= '<p style="float: left;width:100%;margin-top:10px;">Promise: '.$studentInfo->promise_date.'</p>';
							}
							if(!empty($studentInfo->notice_date)){
								$data .= '<p style="float: left;width:100%;margin-top:10px;">Notice: '.$studentInfo->notice_date.'</p>';
							}
							$data .= '<p style="margin-top:15px;float:left;"><a id="de_allocate'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'" data-room_id="'.$roomvalue["block_room_id"].'" data-bed_id="'.$bedvalue->bed_id.'"  class="btn btn-sm btn-danger"><i class="fa fa-trash"></i> De Allocate</a></p><p style="float: right;margin-top: 14px;"><a data-bs-toggle="modal" id="#update_detail'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'" data-bs-target="#update_detail'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'" data-room_id="'.$roomvalue["block_room_id"].'" data-bed_id="'.$bedvalue->bed_id.'"  class="btn btn-sm btn-success" ><i style="font-size:14px;cursor:pointer;" class="fa fa-edit"></i> Detail</a></p>';
							$data .= '<p style="margin-top:15px;float:right;"></p><div class="modal fade" id="update_fee'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
							      <div class="modal-dialog" role="document">
							        <div class="modal-content"><div class="modal-header">
							          <h5 class="modal-title float-start" id="exampleModalLabel">Update Fee</h5>
							          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							        </div>
							        <div class="modal-body">
							          <div id="FeeInfo'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'">
							          </div>
							          </div>
							          <div class="modal-footer">
							            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            							<button type="button" id="updateFee_'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'" class="btn btn-primary">Submit</button>
							          </div>
							        </div>
							      </div>
							    </div><div class="modal fade" id="update_detail'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
							      <div class="modal-dialog" role="document">
							        <div class="modal-content"><div class="modal-header">
							          <h5 class="modal-title float-start" id="exampleModalLabel">Update Fee</h5>
							          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							        </div>
							        <div class="modal-body">
							        <div id="DetailInfo'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'">
							        </div>
							        </div>
							          <div class="modal-footer">
							            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            							<button type="button" id="updateDetail_'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'" class="btn btn-primary">Submit</button>
							          </div>
							        </div>
							      </div>
							    </div>';

							$data .= '<script type="text/javascript">
							$(document).ready(function(){
		
							$("#updateFee_'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'").click(function(){
							 	var studentID = $("#studentID").val();
							 	var bedID = $("#bedID").val();
							 	var blockRoomID = $("#blockRoomID").val();
							 	var bedFee = $("#bedFee").val();
							 
							 	$.ajax({
							      url: "admin.php?c=h_student_beds&m=updateStudentStatus",
							      type: "POST",
							      data:{studentID: studentID,bedID:bedID,blockRoomID:blockRoomID,bedFee:bedFee}, 
							      success:function(res){
							 		    var json = $.parseJSON(res);
							            if(json.success){
							                toastr.success(json.msg);
							            }else{
							                toastr.error(json.msg);
							            }
							          }
							      });
							 });	

							$("#updateDetail_'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'").click(function(){
							 	var studentID = $("#studentID").val();
							 	var bedID = $("#bedID").val();
							 	var blockRoomID = $("#blockRoomID").val();
							 	var detail = $("#detail").val();
							 	var promiseDate = $("#promiseDate").val();
							 	var noticeDate = $("#noticeDate").val();
							 
							 	$.ajax({
							      url: "admin.php?c=h_student_beds&m=updateStudentDetail",
							      type: "POST",
							      data:{studentID: studentID,bedID:bedID,blockRoomID:blockRoomID,detail:detail,promiseDate:promiseDate,noticeDate:noticeDate}, 
							      success:function(res){
							 		    var json = $.parseJSON(res);
							            if(json.success){
							                toastr.success(json.msg);
							            }else{
							                toastr.error(json.msg);
							            }
							          }
							      });
							});		 

						$("#de_allocate'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'").click(function(){
				            var student_id = $("#student_id_'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'").val();
				            
				            var block_room_id = $(this).data("room_id");
				            var bed_id = $(this).data("bed_id");
				              
				            $.ajax({
				                url: "admin.php?c=h_student_beds&m=deallocateStudent",
				                type: "POST",
				                data:{student_id: student_id,bed_id:bed_id,block_room_id:block_room_id}, 
				                success:function(res){
				                  var json = $.parseJSON(res);
				                      if(json.success){
				                          toastr.success(json.msg);
				                      }else{
				                          toastr.error(json.msg);
				                      }
				                    }

				                });
				             });
				        });

				        $("#update_fee'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'").on("show.bs.modal", function (event) {
						  var button = $(event.relatedTarget) // Button that triggered the modal
						  //var parentID = button.data("id")
						  
						  var student_id = $("#student_id_'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'").val();
				          var block_room_id = button.data("room_id");
				          var bed_id = button.data("bed_id");
						  
						   $.ajax({
						      url: "admin.php?c=h_student_beds&m=FeeInfo",
						      type: "POST",
						      data:{student_id: student_id,block_room_id:block_room_id,bed_id:bed_id}, 
						      success:function(res){
						            $("#FeeInfo'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'").html(res);
						          }
						      });

						
						  var modal = $(this)
						  
						});

						$("#update_detail'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'").on("show.bs.modal", function (event) {
						  var button = $(event.relatedTarget) // Button that triggered the modal
						  //var parentID = button.data("id")
						  
						  var student_id = $("#student_id_'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'").val();
				          var block_room_id = button.data("room_id");
				          var bed_id = button.data("bed_id");
						  
						   $.ajax({
						      url: "admin.php?c=h_student_beds&m=DetailInfo",
						      type: "POST",
						      data:{student_id: student_id,block_room_id:block_room_id,bed_id:bed_id}, 
						      success:function(res){
						            $("#DetailInfo'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'").html(res);
						          }
						      });

						
						  var modal = $(this)
						  
						});
				        </script>';

						}else{
							
						$data .= '<select class="form-control select2" name="student_id" id="student_id_'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'" ><option value="0">Select Student</option></select>
						<input placeholder="Bed Fee" type="text" id="student_h_discount_'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'" name="student_h_discount_'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'" value="'.$bed_fee.'"  class="form-control">
						<a id="save'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'" data-room_id="'.$roomvalue["block_room_id"].'" data-bed_id="'.$bedvalue->bed_id.'" class="btn btn-primary btn-sm">Assign Bed</a>
						<script type="text/javascript">
							$(document).ready(function(){

							$("#save'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'").click(function(){
				            var student_id = $("#student_id_'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'").val();
				            var student_h_discount = $("#student_h_discount_'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'").val();
				            var block_room_id = $(this).data("room_id");
				            var bed_id = $(this).data("bed_id");
				              
				            $.ajax({
				                url: "admin.php?c=h_student_beds&m=saveStudent",
				                type: "POST",
				                data:{student_id: student_id,bed_id:bed_id,block_room_id:block_room_id,student_h_discount:student_h_discount}, 
				                success:function(res){
				                  var json = $.parseJSON(res);
				                      if(json.success){
				                          toastr.success(json.msg);
				                      }else{
				                          toastr.error(json.msg);
				                      }
				                    }

				                });
				             });

							$("#student_id_'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'").select2({
									 minimumInputLength: 2,
								    tags: [],
								    ajax: {
								        url: "admin.php?c=fee_chalan_pay&m=get_studentinfo", 
								        dataType: "json",
								        type: "POST",
								        quietMillis: 50,
								        data: function (term) {
								            return {
								                term: term
								            }
								        },
								       processResults: function (response) {
								        console.log(response);
								              return {
								                 results: response
								              };
								           },
								           cache: true
								    } 
								 });
								 });
								 </script>';
							}
							
						}else{
            				// $data .= '<input type="checkbox" class="setRoomBeds setlock_'.$roomvalue["block_room_id"].' setlock_'.$bedvalue->bed_id.'"  name="'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'_room_beds[]"  value="'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'"  />';
         			}
         			$data .= '</td>';
            	 	} 
             	} 
              	$data .= '</tr>';
              	} 
              } 
          	
          $data .= '</tbody></table></div></section>';

		$this->output->set_output($data);
	}

	function FeeInfo(){
		
		$schoolinfo = getSchoolInfo();
	
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');

		$student_id = $this->input->post('student_id');
		$block_room_id = $this->input->post('block_room_id');
		$bed_id = $this->input->post('bed_id');

		$this->db->where('block_room_id', $block_room_id);
		$h_block_rooms_info = $this->db->get('h_block_rooms')->row();	

		$h_fee_amount_info = $this->db->query('select * from h_fee_amount where block_id='.$h_block_rooms_info->block_id.' AND room_id='.$h_block_rooms_info->room_id.' AND session_id="'.$sessionid.'" AND h_fee_type_id = (select fee_type_id from fee_type where is_monthly_fee=1 and h_flag=1 and system_id='.$schoolinfo->system_id.')')->row();


		if($h_fee_amount_info){
			$bed_fee = $h_fee_amount_info->amount;
		}

		$this->db->where('status', 1);
		$this->db->where('session_id', $sessionid);
		$this->db->where('bed_id', $bed_id);
		$this->db->where('block_room_id', $block_room_id);
		$h_student_beds = $this->db->get('h_student_bed')->row();
	
		if($h_student_beds){
			if($bed_fee != 0){
				$bed_fee = ($h_fee_amount_info->amount - $h_student_beds->student_h_discount);
			}
		}

		$strfeeUpdate = '<input type="hidden" id="bedID" value="'.$bed_id.'"><input type="hidden" id="blockRoomID" value="'.$block_room_id.'"><input type="hidden" id="studentID" value="'.$student_id.'"><input type="text" name="update_fee" id="bedFee" value="'.$bed_fee.'" class="form-control">';	
		echo $strfeeUpdate;

	}

	function DetailInfo(){
		
		$schoolinfo = getSchoolInfo();
	
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');

		$detail = '';
		$promise_date = '';
		$notice_date = '';

		$student_id = $this->input->post('student_id');
		$block_room_id = $this->input->post('block_room_id');
		$bed_id = $this->input->post('bed_id');

		$this->db->where('block_room_id', $block_room_id);
		$h_block_rooms_info = $this->db->get('h_block_rooms')->row();	

		$student_info = $this->db->query('select * from students where student_id='.$student_id)->row();

		if($student_info){
			$detail = $student_info->detail;
			$promise_date = $student_info->promise_date;
			$notice_date = $student_info->notice_date;
		}

		$strDetailUpdate = '<input type="hidden" id="bedID" value="'.$bed_id.'"><input type="hidden" id="blockRoomID" value="'.$block_room_id.'"><input type="hidden" id="studentID" value="'.$student_id.'"><textarea class="form-control" name="detail" id="detail">'.$detail.'</textarea><input type="date" name="promise_date" id="promiseDate" value="'.$promise_date.'" class="form-control"><input type="date" name="notice_date" id="noticeDate" value="'.$notice_date.'" class="form-control">';	
		echo $strDetailUpdate;

	}

function get_studentinfo(){

		$campusid = $this->session->userdata('member_campusid');
		$term = $this->input->post('term');		
		$studentsinfo = $this->db->query("select * from students where (first_name like '%".$term['term']."%' OR last_name like '%".$term['term']."%') AND status=1 AND campus_id=".$campusid)->result_array();
		 // Initialize Array with fetched data
     $data = array();
     foreach($studentsinfo as $student){
     	$classstudents = $this->db->query("select * from student_class where  status=1 and student_id = ".$student['student_id'])->row();
     	$studentsParents = $this->db->query("select * from parents where parent_id = ".$student['parent_id'])->row();
     	if($classstudents){

     		$classSection = getClassSection($classstudents->cls_sec_id);
     		$section = '';
     		if($classSection){
     			$section = $classSection['sectionclassname'];
     		}

     		 $data[] = array("id"=>$student['student_id'], "text"=>$student['first_name']." ".$student['last_name']." c/o ".$studentsParents->f_name." ".$section);
     	}
     }
	return json_response($data);	 
}

function updateStudentStatus(){

	$user_id = $this->session->userdata['member_userid'];
	$date = date('Y-m-d H:i:s');

	$schoolinfo = getSchoolInfo();
	
	$campusid = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');
	
	$now = date('Y-m-d H:i:s');

	$student_id = $this->input->post('studentID');
	if(empty($student_id)){
		json_response(array('error' => TRUE, 'msg' => 'Please select student'));
		exit;
	}
	$block_room_id = $this->input->post('blockRoomID');
	$bed_id = $this->input->post('bedID');
	$bed_fee = $this->input->post('bedFee');

	$this->db->where('student_id', $student_id);
	$this->db->where('session_id', $sessionid);
	$this->db->where('block_room_id', $block_room_id);
	$this->db->where('bed_id', $bed_id);
	$this->db->where('status', 1);
	$h_student_bed = $this->db->get('h_student_bed')->row();

	$this->db->where('block_room_id', $block_room_id);
	$h_block_rooms_info = $this->db->get('h_block_rooms')->row();

	$h_fee_amount_info = $this->db->query('select * from h_fee_amount where block_id='.$h_block_rooms_info->block_id.' AND room_id='.$h_block_rooms_info->room_id.' AND session_id="'.$sessionid.'" AND h_fee_type_id = (select fee_type_id from fee_type where is_monthly_fee=1 and h_flag=1 and system_id='.$schoolinfo->system_id.')')->row();

	$discount_amount = ''; 
	if($h_block_rooms_info){
		$discount_amount = $h_fee_amount_info->amount - $bed_fee;
	}else{
		json_response(array('error' => TRUE, 'msg' => 'Bed fee need to update'));
		exit;
	}

	if(!empty($h_student_bed)){
		$data = array(
				'student_h_discount' => trim($discount_amount),
				'updated_date' => $date,
				'user_id' => $user_id
			);
	

		$this->db->where('student_id', $student_id);
		$this->db->where('session_id', $sessionid);
		$this->db->where('block_room_id', $block_room_id);
		$this->db->where('bed_id', $bed_id);
		$this->db->update('h_student_bed', $data);
	
	}

	json_response(array('success' => TRUE, 'msg' => 'Update Fee Success'));
}

function updateStudentDetail(){

	$user_id = $this->session->userdata['member_userid'];
	$date = date('Y-m-d H:i:s');

	$schoolinfo = getSchoolInfo();
	
	$campusid = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');
	
	$now = date('Y-m-d H:i:s');

	$student_id = $this->input->post('studentID');
	if(empty($student_id)){
		json_response(array('error' => TRUE, 'msg' => 'Please select student'));
		exit;
	}
	$block_room_id = $this->input->post('blockRoomID');
	$bed_id = $this->input->post('bedID');
	$detail = $this->input->post('detail');
	$promiseDate = $this->input->post('promiseDate');
	$noticeDate = $this->input->post('noticeDate');

	$this->db->where('student_id', $student_id);
	$this->db->where('session_id', $sessionid);
	$this->db->where('block_room_id', $block_room_id);
	$this->db->where('bed_id', $bed_id);
	$this->db->where('status', 1);
	$h_student_bed = $this->db->get('h_student_bed')->row();

	if(!empty($h_student_bed)){
		$data = array(
				'detail' => trim($detail),
				'promise_date' => trim($promiseDate),
				'notice_date' => trim($noticeDate),
				'updated_date' => $date,
				'user_id' => $user_id
			);
	

		$this->db->where('student_id', $student_id);
		$this->db->update('students', $data);
		
	
	}

	json_response(array('success' => TRUE, 'msg' => 'Update Success'));
}

function deallocateStudent(){

	$user_id = $this->session->userdata['member_userid'];
	$date = date('Y-m-d H:i:s');

	$schoolinfo = getSchoolInfo();
	
	$campusid = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');
	
	$now = date('Y-m-d H:i:s');

	$student_id = $this->input->post('student_id');
	if(empty($student_id)){
		json_response(array('error' => TRUE, 'msg' => 'Please select student'));
		exit;
	}
	$block_room_id = $this->input->post('block_room_id');
	$bed_id = $this->input->post('bed_id');

	$this->db->where('student_id', $student_id);
	$this->db->where('session_id', $sessionid);
	$this->db->where('block_room_id', $block_room_id);
	$this->db->where('bed_id', $bed_id);
	$this->db->where('status', 1);
	$h_student_bed = $this->db->get('h_student_bed')->row();


	$this->db->where('block_room_id', $block_room_id);
	$h_block_rooms_info = $this->db->get('h_block_rooms')->row();

	if(!empty($h_student_bed)){
		$data = array(
				'status' => 0,
				'updated_date' => $date,
				'user_id' => $user_id
			);
	

		$this->db->where('student_id', $student_id);
		$this->db->where('session_id', $sessionid);
		$this->db->where('block_room_id', $block_room_id);
		$this->db->where('bed_id', $bed_id);
		$this->db->update('h_student_bed', $data);
	
	}

	json_response(array('success' => TRUE, 'msg' => 'Edit Student Success'));
}


function saveStudent(){

	$user_id = $this->session->userdata['member_userid'];
	$date = date('Y-m-d H:i:s');

	$schoolinfo = getSchoolInfo();
	
	$campusid = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');
	
	$now = date('Y-m-d H:i:s');

	$student_id = $this->input->post('student_id');
	if(empty($student_id)){
		json_response(array('error' => TRUE, 'msg' => 'Please select student'));
		exit;
	}
	$block_room_id = $this->input->post('block_room_id');
	$bed_id = $this->input->post('bed_id');
	$student_h_discount = $this->input->post('student_h_discount');

	$this->db->where('student_id', $student_id);
	$this->db->where('session_id', $sessionid);
	$this->db->where('block_room_id', $block_room_id);
	$this->db->where('bed_id', $bed_id);
	$this->db->where('status', 1);
	$h_student_bed = $this->db->get('h_student_bed')->row();


	$this->db->where('block_room_id', $block_room_id);
	$h_block_rooms_info = $this->db->get('h_block_rooms')->row();

	$h_fee_amount_info = $this->db->query('select * from h_fee_amount where block_id='.$h_block_rooms_info->block_id.' AND room_id='.$h_block_rooms_info->room_id.' AND h_fee_type_id = (select fee_type_id from fee_type where is_monthly_fee=1 and h_flag=1 and system_id='.$schoolinfo->system_id.')')->row();

	$discount_amount = ''; 
	if($h_block_rooms_info){
		$discount_amount = $h_fee_amount_info->amount - $student_h_discount;
	}else{
		json_response(array('error' => TRUE, 'msg' => 'Bed fee need to update'));
		exit;
	}

	if(!empty($h_student_bed)){
		$data = array(
				'student_h_discount' => trim($discount_amount),
				'status' => 1,
				'updated_date' => $date,
				'user_id' => $user_id
			);
	

		$this->db->where('student_id', $student_id);
		$this->db->where('session_id', $sessionid);
		$this->db->where('block_room_id', $block_room_id);
		$this->db->where('bed_id', $bed_id);
		$this->db->update('h_student_bed', $data);
	
	}else{
		
		$h_beds_info = $this->db->query('select * from h_student_bed where student_id='.$student_id)->row();
		if($h_beds_info){
			$data1 = array(
					'status' => 0,
					'updated_date' => $date,
					'user_id' => $user_id
				);

			$this->db->where('student_id', $student_id);
			$this->db->update('h_student_bed', $data1);
		}

		$data = array(
			'student_h_discount' => trim($discount_amount),
			'status' => trim(1),
			'session_id' => trim($sessionid),
			'student_id' => trim($student_id),
			'bed_id' => $bed_id,
			'block_room_id' => trim($block_room_id),
			'created_date' => $date,
			'user_id' => $user_id
		);

		$this->db->insert('h_student_bed', $data);

	}	

	json_response(array('success' => TRUE, 'msg' => 'Edit Student Success'));
}

	function updateRoomBeds(){
		$campusid = $this->session->userdata('member_campusid');
		$status = $this->input->post('status');
		$room_beds_ids = $this->input->post('room_beds_id');
		$roomBedArr = explode('_', $room_beds_ids);

		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');

		$block_room_id = $roomBedArr[0];
		$bed_id = $roomBedArr[1];

		
		$this->db->where('block_room_id', $block_room_id);
		$this->db->where('bed_id', $bed_id);
		$roomBeds = $this->db->get('h_room_beds')->row();

		if($roomBeds){

			$data = array(
				'user_id' => $user_id,
				'updated_date' => $date,
				'status' => $status
			);

			$this->db->where('block_room_id', $block_room_id);
			$this->db->where('bed_id', $bed_id);
			$this->db->update('h_room_beds', $data);

		}else{
			$data = array(
				'block_room_id' => $block_room_id,
				'bed_id' =>  $bed_id,
				'campus_id' => $campusid,
				'user_id' => $user_id,
				'created_date' => $date,
				'status' => 1
			);
			$this->db->insert('h_room_beds', $data);
		}
		
		json_response(array('success' => TRUE, 'msg' => 'Add Room Beds Success'));
	}

	function add(){
		check_permission('admin-add-room-beds');
		$campusid = $this->session->userdata('member_campusid');

		$campus_info = $this->db->query('select * from campus WHERE campus_id='.$campusid)->row();
		$this->template_data['campus_info'] = $campus_info;

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
		'sectionclassname' => $classinfo->class_name." (".$sectioninfo->short_name.")"
		);
		
		}
		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;		

	    $subjectinfo = $this->db->get('allsubject')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;

		$this->load->view('h_student_beds_edit', $this->template_data);
	}

	
}
// end this file
