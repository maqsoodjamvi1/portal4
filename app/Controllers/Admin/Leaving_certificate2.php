<?php
namespace App\Controllers\Admin;


/**
 * Users Manage
 *
 * @author		Maqsood Jamvi
 * @copyright	Copyright (c) 2016~2099 timesoftsol.com
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */ 
 


class Leaving_certificate2 extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-users');
		  $this->load->helper(array('form', 'url'));
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('leaving_certificate2', $this->template_data); 
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];

		$this->db->select('count(A.student_id) as ccount', FALSE);
		$this->db->from('students A');
		if($keyword){
			$this->db->where('(A.first_name=' . $this->db->escape($keyword) . ' or A.reg_no=' . $this->db->escape($keyword) . ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;


		$this->db->select('A.*');
		$this->db->from('students A');
		if($keyword){
			$this->db->where('(A.first_name=' . $this->db->escape($keyword) . ' or A.reg_no=' . $this->db->escape($keyword) . ')');
		}
		$this->db->order_by('A.student_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
			$data = array();
			$data['id'] = $row->student_id;
			$imgurl = FCPATH."uploads/".$row->profile_photo;
			if(file_exists($imgurl)){
				$data['profile_photo'] = "<img style='width:50px;height:50px;text-align: center;display: block;border-radius: 30px;margin: 0 auto;' src='/timeschool/uploads/".$row->profile_photo."' >";
			}else{
				$data['profile_photo'] = "<i style='font-size: 40px;text-align: center;display: block;' class='fa fa-user'></i>";
			}
			$data['reg_no'] = $row->reg_no;
			$data['name'] = $row->first_name." ".$row->last_name;
			$data['f_name'] = $row->f_name;
			$data['father_contact'] = $row->father_contact;
			$data['mother_contact'] = $row->mother_contact;
			$data['emergency_contact'] = $row->emergency_contact;
			//$data['issys'] = $row->issys;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-user');
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		
		$classesinfo = $this->db->get('classes')->result();
		$this->template_data['classesinfo'] = $classesinfo;
		
		$this->db->where('session_id', $sessionid);
		$academic_session = $this->db->get('academic_session')->row();
		
		$sessionName = explode('-' , $academic_session->session_name);
		 
		$sessionYear = ($sessionName[1]-1);
		
		$this->db->where('session_id', $sessionid);
		$this->db->order_by('student_id', 'desc');
		$last_row = $this->db->get('students')->result();
		$last_id = count($last_row)+1;
		
		$reg_no =  $sessionYear.'-TSS-'.$last_id;
		$this->template_data['reg_no'] = $reg_no;

		$this->db->where('campus_id', $campusid);
		$sectioninfo = $this->db->get('sections')->result();
		$this->template_data['sectioninfo'] = $sectioninfo;
		
		$academic_sessioninfo = $this->db->get('academic_session')->result();
		$this->template_data['academic_sessioninfo'] = $academic_sessioninfo;
		
		$this->load->view('students_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-user');
		$id = intval($this->input->get('id'));

		$this->db->where('student_id', $id);
		$info = $this->db->get('students')->row();
		$this->template_data['info'] = $info;
		
		$this->db->where('parent_id', $info->parent_id);
		$parentsinfo = $this->db->get('parents')->row();
		$this->template_data['parentsinfo'] = $parentsinfo;
		
		$this->db->where('student_id', $id);
		$this->db->order_by("sc_id", "desc");
		$studentclassinfo = $this->db->get('student_class')->row();
		//print_r($studentclassinfo);

		$this->db->where('cls_sec_id', $studentclassinfo->cls_sec_id);
		$classSectioninfo = $this->db->get('class_section')->row();
			
		$this->db->where('class_id', $classSectioninfo->class_id);
		$class_info = $this->db->get('classes')->row();
		$this->template_data['class_info'] = $class_info;
		
		$academic_sessioninfo = $this->db->get('academic_session')->result();
		$this->template_data['academic_sessioninfo'] = $academic_sessioninfo;

		$this->load->view('leaving_certicate2', $this->template_data);
	}

	function save(){
			
		$name = $this->input->post('name');

		$date_of_birth = DateTime::createFromFormat('Y-m-d',$this->input->post('date_of_birth'));
		$date_of_birth = $date_of_birth->format('j M Y');
		$religion = $this->input->post('religion');
		$reg_no = $this->input->post('reg_no');
		$gr_no = $this->input->post('gr_no');
		$caste = $this->input->post('caste');
		$gender = $this->input->post('gender');
		$nationality = $this->input->post('nationality');
		$f_name = $this->input->post('f_name');
		
		$date_of_admission = DateTime::createFromFormat('Y-m-d',$this->input->post('date_of_admission'));
		$date_of_admission = $date_of_admission->format('j M Y');

		$gr_date = DateTime::createFromFormat('Y-m-d',$this->input->post('gr_date'));
	    $gr_date = $gr_date->format('j M Y');
		
		$leaving_date = DateTime::createFromFormat('Y-m-d',$this->input->post('leaving_date'));
		$leaving_date = $leaving_date->format('j M Y');
		
		
		$f_name = $this->input->post('f_name');
		$birth_date = $this->input->post('date_of_birth');
		$new_birth_date = explode('-', $birth_date);
		$year = $new_birth_date[0];
		$month = $new_birth_date[1];
		$day  = $new_birth_date[2];
		$birth_day= $this->numberTowords($day);
		$birth_year= $this->numberTowords($year);
		$monthNum = $month;
		$getMonth = DateTime::createFromFormat("m", $month);
		$month = strtoupper($getMonth->format('F'));
		$date_of_birth_in_words = $birth_day." ".$month." ".$birth_year;
		//exit;

		$class_passed = $this->input->post('class_passed');
		$mother_contact = $this->input->post('mother_contact');
		$remarks = $this->input->post('remarks');
		$campus_id = $this->input->post('campus_id');
		$id = $this->input->post('id');

		
		$this->db->where('campus_id', $campus_id);
		$campusinfo = $this->db->get('campus')->row();
		$schoolinfo = getSchoolInfo(); 
		//print_r($schoolinfo);
		$this->db->trans_begin();

		$data = array(
		'gender' => $gender,
		'date_of_admission' => $this->input->post('date_of_admission'),
		'leaving_date' => $this->input->post('leaving_date'),
		'date_of_birth' => $this->input->post('date_of_birth'),
		'caste' => trim($this->input->post('caste')),
		'gr_no' => trim($this->input->post('gr_no')),
		'gr_date' => trim($this->input->post('gr_date')),
		'status' => 3,
		);

		$data2 = array(
		'status' => 3,
		);
		
		$this->db->where('student_id', $id);
		$this->db->update('students', $data);
		
		$this->db->where('student_id', $id);
		$this->db->update('student_class', $data2);
		
		$this->db->trans_complete();			
	$content = '<html>
<head>
<title>School Leaving Certificate</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
<style>
body {
	font-family: "Times New Roman", Times, serif;
	color: #000 !important;
}
.textdata {
	text-transform: uppercase;
	border-bottom: 1px solid #000000;
	float: left;
	padding-left: 5px;
	margin-left: 5px;
}
.labeltext {
	float: left;
}
.textdata_right {
	text-transform: uppercase;
	border-bottom: 1px solid #000000;
	float: right;
	padding-left: 5px;
	margin-left: 5px;
}
.labeltext {
	float: left;
	font-weight: bold;
}
.overlay{    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    z-index: -1;
    left: 0;
    background: rgba(255,255,255,0.7);}
</style>

</head>
<body>
<div class="container" id="students_list_container" style="width:877px; margin-top:10px;font-family: "Times New Roman", Times, serif !important;">
		<page   backimgx="center" backimgy="30%" backimgw="100%" style="background:#000;">
				<div style="border:3px dotted #000; border-radius:10px; text-align:center;font-family: "Times New Roman"; background:#fff;background-repeat: no-repeat;
    background-size: contain;">
						<table style="width:100%;">
								<tr>
										<td style="width:30%;"><img style="width: 130px; margin-left:10px;" src="'.base_url().'system-logo/'.$schoolinfo->logo.'"></td>
										<td style="float: left;margin-top: 15px;"><h1 style="
    font-size: 42px;
    margin-top: 0px;
    text-align: center;
    ">'.$schoolinfo->system_name.'</h1> 
												<h3 style="margin:0px;padding:0px;font-size: 18px;text-align: center;">'.$campusinfo->campus_name.' '.$campusinfo->landline.'</h3></td>
								</tr>
						</table>
				</div>
				<div style="text-align:center;">
						<h2 tyle="text-align:center;margin:0 auto;font-family:Verdana, Geneva, sans-serif;">School Leaving Certificate</h2>
				</div>
				<div style="    float: left;
    width: 100%;
    background: url('.base_url().'system-logo/'.$schoolinfo->logo.');
    margin: 10px auto;
    background-size: contain;
    background-position: center;
    background-repeat: no-repeat;     z-index: 100;
    position: relative;"><div class="overlay"></div>
						<table style="width:100%;;padding:0px; margin:0px;" cellpadding="0" cellspacing="0" >
								<tr>
										<td style="width:60%;padding-left:10px;padding-bottom:70px;"><b>Ref No:</b> __________________</td>
										<td style="width:40%;padding-left:10px;padding-bottom:70px; text-align:right;"><b>Date:</b> __________________</td>
								</tr>
						</table>
						<table style="width:100%;;padding:0px; margin:0px;" cellpadding="0" cellspacing="0" >
						<tr>
										<td style="width: 25%;padding-bottom:40px;padding-left:10px;"><span class="labeltext">Reg #:</span>
												<div class="textdata" style="width: 78%;">'.$reg_no.'</div></td>
										<td style="width: 65%;padding-left:10px;padding-bottom:40px;"><span class="labeltext">Student Name:</span>
												<div class="textdata" style="width: 82%;">'.$name.'</div></td>
										
								</tr>
						
								
								</table><table style="width:100%;;padding:0px; margin:0px;" cellpadding="0" cellspacing="0">
								<tr>
										
										<td style="width: 40%;padding-left:10px;padding-bottom:40px;"><span class="labeltext">Father Name:</span>
												<div class="textdata_right" style="width: 74%;">'.$f_name.' </div></td>
																						<td style="width: 20%;padding-left:10px;padding-bottom:40px;"><span class="labeltext">Gender:</span>
												<div class="textdata" style="width: 61%;">'.$gender.'</div></td>
												<td style="width: 20%;padding-left:10px;padding-bottom:40px;"><span class="labeltext">Nationality:</span>
												<div class="textdata_right" style="width: 57%;">'.$nationality.'</div></td>
								</tr>
								
								</table>
 
								<table style="width:100%;">
								<tbody>
								<tr>

										
													<td style="width: 33%;padding-left:10px;padding-bottom:40px;"><span class="labeltext">Religion:</span>
												<div class="textdata" style="width: 56%;">'.$religion.'  </div></td>
												<td style="width: 33%;padding-left:10px;padding-bottom:40px;"><span class="labeltext">Cast:</span>
												<div class="textdata" style="width: 56%;">'.$caste.'  </div></td>
												<td style="width: 33%;padding-left:10px;padding-bottom:40px;"><span class="labeltext">GR No:</span>
												<div class="textdata" style="width: 56%;">'.$gr_no.'  </div></td>
												</tr><tr><td style="width: 25%;padding-left:10px;padding-bottom:40px;"><span class="labeltext">GR Date:</span>
												<div class="textdata" >'.$gr_date.'</div></td>
										<td style="width: 40%;padding-left:10px;padding-bottom:40px;"><span class="labeltext">Date Of Admission:</span>
												<div class="textdata" style="width: 35%;">'.$date_of_admission.'</div></td>
												<td style="width: 40%;padding-left:10px;padding-bottom:40px;"><span class="labeltext">Date Of Leaving:</span>
												<div class="textdata" style="width: %;">'.$leaving_date.'  </div></td>
										
								</tr>

														</tbody></table>
								
						<table style="width:100%;" >
						<tr>

												<td style="width: 23%;padding-left:10px;padding-bottom:40px;"><span class="labeltext">Date Of Birth:</span> <div class="textdata" style="width: 50%;">'.$date_of_birth.'</div> </td>
											
										<td style="width: 60%;padding-left:10px;padding-bottom:40px;"><span class="labeltext">Date Of Birth(In Words):</span> <div class="textdata" style="width: 70%;">'.$date_of_birth_in_words.'</div> 
										</td>	
								</tr>
						
						
						</table>

						<table style="width:100%;">
								<tbody>
								<tr>
										<td style="width: 50%;padding-left:10px;padding-bottom:40px;"><span class="labeltext">Class Passed:</span> <div class="textdata" style="width: 72%;">'.$class_passed.'</div>  </td>
										<td style="width:50%;padding-left:10px;padding-bottom:40px;"><span class="labeltext">Remarks:</span> <div class="textdata" style="width: 82%;">'.$remarks.'</div> 
										</td>
								</tr>

								
								
						</tbody></table>

						
						<table style="width:100%;">
								<tr>
										<td style="width:30%;padding-left:10px;padding-bottom:40px;font-family: "Times New Roman", Times, serif;"><div style="text-align:center;">
														<hr style="height:1px;border:0.5px solid #000000;padding:0 50px;">
														<b> INCHARGE:</b></div></td>
										<td style="width:30%;"></td>
										<td style="width:30%;padding-left:10px;padding-bottom:30px;font-family: "Times New Roman", Times, serif;"><div style="text-align:center;">
														<hr style="height:1px;border:0.5px solid #000000;padding:0 50px;">
														<b>PRINCIPAL:</b></div></td>
								</tr>
						</table>
				</div>
		</page>
</div>
</body>
</html>';
		
		
		$myfile = fopen("leaving_certificate2/certificate2-".$reg_no.".html", "w") or die("Unable to open file!");

		$txt = $content;
		fwrite($myfile, $txt);
		fclose($myfile);
		
		json_response(array('success' => TRUE, 'msg' => 'Add User Success'));
							
		
	}


	
	function download(){
		
		$regno = $this->input->get('regno');
		
		echo "<a class='btn btn-primary' style='margin: 50px;' href='/leaving_certificate2/certificate2-".$regno.".html' target='_blank'>Click to Download</a>";
		
	}


	function numberTowords($num)
	{ 

		$ones = array(
		0 =>"ZERO", 
		1 => "ONE", 
		2 => "TWO", 
		3 => "THREE", 
		4 => "FOUR", 
		5 => "FIVE", 
		6 => "SIX", 
		7 => "SEVEN", 
		8 => "EIGHT", 
		9 => "NINE",
		10 => "TEN", 
		11 => "ELEVEN", 
		12 => "TWELVE", 
		13 => "THIRTEEN", 
		14 => "FOURTEEN", 
		15 => "FIFTEEN", 
		16 => "SIXTEEN", 
		17 => "SEVENTEEN", 
		18 => "EIGHTEEN", 
		19 => "NINETEEN",
		"014" => "FOURTEEN" 
		); 
		$tens = array( 
		0 => "ZERO",
		1 => "TEN",
		2 => "TWENTY", 
		3 => "THIRTY", 
		4 => "FORTY", 
		5 => "FIFTY", 
		6 => "SIXTY", 
		7 => "SEVENTY", 
		8 => "EIGHTY", 
		9 => "NINETY" 
		); 
		$hundreds = array( 
		"HUNDRED", 
		"THOUSAND",
		"MILLION", 
		"BILLION", 
		"TRILLION",
		"QUARDRILLION" 
		); /* limit t quadrillion */
		$num = number_format($num,2,".",",");
		$num_arr = explode(".",$num); 
		$wholenum = $num_arr[0]; 
		$decnum = $num_arr[1]; 
		$whole_arr = array_reverse(explode(",",$wholenum)); 
		krsort($whole_arr,1); 
		$rettxt = ""; 
		foreach($whole_arr as $key => $i){
			
		while(substr($i,0,1)=="0")
				$i=substr($i,1,5);
		if($i < 20){ 
		/* echo "getting:".$i; */
		$rettxt .= $ones[$i]; 
		}elseif($i < 100){ 
		if(substr($i,0,1)!="0")  $rettxt .= $tens[substr($i,0,1)]; 
		if(substr($i,1,1)!="0") $rettxt .= " ".$ones[substr($i,1,1)]; 
		}else{ 
		if(substr($i,0,1)!="0") $rettxt .= $ones[substr($i,0,1)]." ".$hundreds[0]; 
		if(substr($i,1,1)!="0")$rettxt .= " ".$tens[substr($i,1,1)]; 
		if(substr($i,2,1)!="0")$rettxt .= " ".$ones[substr($i,2,1)]; 
		} 
		if($key > 0){ 
		$rettxt .= " ".$hundreds[$key]." "; 
		} 
		} 
		if($decnum > 0){ 
		$rettxt .= " and "; 
		if($decnum < 20){ 
		$rettxt .= $ones[$decnum]; 
		}elseif($decnum < 100){ 
		$rettxt .= $tens[substr($decnum,0,1)]; 
		$rettxt .= " ".$ones[substr($decnum,1,1)]; 
		} 
		} 
		return $rettxt; 
	} 
	

}
// end this file
