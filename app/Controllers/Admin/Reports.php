<?php
namespace App\Controllers\Admin;

 
/**
 * Reports
 *
 * @author		Maqsood Jamvi
 * @copyright	Copyright (c) 2016~2099 timesoftsol.com
 * @email		maqsoodjamvi@gmail.com
 * @filesource
*/



class Reports extends MY_Controller {
	
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		$this->load->view('reports', $this->template_data);
	}	

	function data(){

    $schoolinfo = getSchoolInfo();
    $student_id = $this->input->post('student_id');

    $this->db->where('student_id', $student_id);
    $info = $this->db->get('students')->row();

    $this->db->where('parent_id', $info->parent_id);
    $parentInfo = $this->db->get('parents')->row();

    // $this->template_data['schoolinfo'] = $schoolinfo;
    // $this->template_data['info'] = $studentInfo;
    // $this->template_data['parentInfo'] = $parentInfo;

    $system_name = $schoolinfo->system_name;
    $address = $schoolinfo->address;
    $city = $schoolinfo->city;
    $state = $schoolinfo->state;
    $zip = $schoolinfo->zip;
    $country = $schoolinfo->country;
    $owner_name = $schoolinfo->owner_name;
    $landline_number = $schoolinfo->landline_number;
    $mob_number = $schoolinfo->mob_number;
    $reg_text = $schoolinfo->reg_text;
    $logo = $schoolinfo->logo;
    $slogan = $schoolinfo->slogan;


	$strStdInfo = '';
	$strStdInfo .= ' <div class="container form-container">
      <div class=" col-lg-12 mx-auto login-container">
          <div class="row form-header">
              <div class="col-md-2 logocol">
                <img src="'.$logo.'" alt="">
              </div>
              <div class="col-md-10 headcol">
                <h4>'.$system_name.'</h4>
                <p>'.$slogan.'</p>
                <p class="cinfo">
                    <span><i class="fas fa-phone"></i> '. $mob_number.'</span>
                    <span><i class="fas fa-map-marker-alt"></i> '. $address.', '.$city.', '.$country.'</span>
                </p>
               
              </div>
          </div>
          <div class="form-body">
            <div class="form-title row">
              <h4>Student Information</h4>
            </div>

            <div class="row row">
              <div class="col-lg-2 col-md-4">
                <label for="">First Name</label>
                <span class="indc">:</span>
              </div>
              <div class="col-lg-4 col-md-8">
                <span class="indc float-start">'.$info->first_name.'</span>
              </div>
              <div class="col-lg-2 col-md-4">
                <label for="">Last Name</label>
                 <span class="indc">:</span>
              </div>
              <div class="col-lg-4 col-md-8">
               <span class="indc float-start"> '.$info->last_name.'</span>
              </div>
            </div>

            <div class="row row">
               <div class="col-lg-2 col-md-4">
                <label for="">Date of Birth</label>
                 <span class="indc">:</span>
              </div>
              <div class="col-lg-4 col-md-8">
                <span class="indc float-start">'. $info->date_of_birth.'</span>
              </div>
               <div class="col-lg-2 col-md-4">
                <label for="">Gender</label>
                <span class="indc">:</span>
              </div>
              <div class="col-lg-4 col-md-8 pt-1">
                <span class="indc float-start text-capitalize">'.$info->gender.'</span>
              </div>
            </div>

            <div class="form-title row">
              <h4>Parent Details</h4>
            </div>

            <div class="row row">
                <div class="col-lg-2 col-md-4">
                <label for="">Father Name</label>
                 <span class="indc">:</span>
              </div>
              <div class="col-lg-4 col-md-8">
                <span class="indc float-start text-capitalize">'.$parentInfo->f_name.'</span>
              </div>
               <div class="col-lg-2 col-md-4">
                <label for="">Father Profession</label>
                 <span class="indc">:</span>
              </div>
              <div class="col-lg-4 col-md-8">
                <span class="indc float-start text-capitalize">'.$parentInfo->father_occupation.'</span>
              </div>
            </div>
            <div class="row row">
                <div class="col-lg-2 col-md-4">
                <label for="">Father Contact No</label>
                 <span class="indc">:</span>
              </div>
              <div class="col-lg-4 col-md-8">
                <span class="indc float-start text-capitalize">'.$parentInfo->father_contact.'</span>
              </div>
                <div class="col-lg-2 col-md-4">
                <label for="">Mother Contact No</label>
                 <span class="indc">:</span>
              </div>
              <div class="col-lg-4 col-md-8">
                  <span class="indc float-start text-capitalize">'.$parentInfo->mother_contact.'</span>
              </div>
            </div>
            <div class="form-title row">
              <h4>Contact Information</h4>
            </div>
             <div class="row row">
                <div class="col-lg-2 col-md-4">
                <label for="">Whatsapp</label>
                 <span class="indc">:</span>
              </div>
              <div class="col-lg-4 col-md-8">
                <span class="indc float-start text-capitalize">'.$parentInfo->whatsapp.'</span>
              </div>
               <div class="col-lg-2 col-md-4">
                <label for="">Email Address</label>
                <span class="indc">:</span>
              </div>
               <div class="col-lg-4 col-md-8">
                <span class="indc float-start text-capitalize">'.$parentInfo->father_email.'</span>
              </div>
            </div>

            <div class="row row">
                <div class="col-lg-2 col-md-4">
                <label for="">City</label>
                 <span class="indc">:</span>
              </div>
               <div class="col-lg-4 col-md-8">
                <span class="indc float-start text-capitalize">'.$parentInfo->city.'</span>
              </div>
                <div class="col-lg-2 col-md-4">
                <label for="">Address</label>
                  <span class="indc">:</span>
              </div>
               <div class="col-lg-4 col-md-8">
                <span class="indc float-start text-capitalize">'.$parentInfo->address_line1.'</span>
              </div>
            </div>
           </div>
      </div>
    </div> <style type="text/css">
@import url("https://fonts.googleapis.com/css2?family=Besley:wght@400;600;700&display=swap");
.session-title {
  padding: 30px;
  margin: 0px;
  font-family: "Bona Nova", serif;
}
.session-title h2 {
  width: 100%;
  text-align: center;
  font-size: 1.8rem;
}
.session-title p {
  width: 100%;
  text-align: center;
  font-size: 0.9rem;
}

body {
  background-color: #e8eaef;
  font-family: "Besley", serif;
}

.form-container {
  margin-bottom: 50px;
  margin-bottom: 70px;
}

.form-container .login-container {
  box-shadow: 0 2px 6px 0 rgba(218, 218, 253, 0.65), 0 2px 6px 0 rgba(206, 206, 238, 0.54);
}

.form-container .login-container .content-part {
  background-color: #f7f9fe;
  border-top-left-radius: 8px;
  border-bottom-left-radius: 8px;
  padding: 50px;
}
.form-container .login-container .content-part img {
  max-width: 100%;
}
.form-container .login-container .content-part h2 {
  font-size: 1.7rem;
  text-align: center;
  margin-bottom: 20px;
}
.form-container .login-container .content-part p {
  font-size: 0.9rem;
  text-align: center;
}
.form-container .login-container .form-part {
  background-color: #FFF;
  border-top-right-radius: 8px;
  border-bottom-right-radius: 8px;
  padding: 50px;
}
.form-container .login-container .form-part .signinlink {
  text-align: right;
  margin-top: -20px;
}
.form-container .login-container .form-part .signinlink a {
  font-weight: 600;
}
.form-container .login-container .form-part .formcol {
  margin: auto;
}
.form-container .login-container .form-part .formcol h3 {
  text-align: center;
  margin-top: 30px;
  margin-bottom: 30px;
  font-size: 1.5rem;
}
.form-container .login-container .form-part .login {
  margin: auto;
  margin-top: 110px;
}
.form-container .login-container .form-part .form-floating .btn {
  width: 100%;
  margin-top: 15px;
}
.form-container .login-container .form-part .form-floating .form-control {
  background-color: #cccccc24;
}
.form-container .login-container .form-part .form-floating .form-control:hover {
  border: 2px solid #0d6efd;
  box-shadow: none;
}
.form-container .login-container .form-part .form-floating .form-control:focus {
  border: 2px solid #0d6efd;
  box-shadow: none;
}
.form-container .login-container .form-part .form-floating .form-control:active {
  border: 2px solid #0d6efd;
  box-shadow: none;
}

.login-container {
  background-color: #FFF;
}

.form-header {
  padding: 20px;
  border-bottom: 1px solid #CCC;
}
.form-header .logocol img {
  max-width: 100%;
}
@media screen and (max-width: 767px) {
  .form-header .logocol {
    text-align: center;
  }
}
.form-header .headcol {
  padding-top: 10px;
  padding-left: 0px;
}
.form-header .headcol h4 {
  font-size: 2.8rem;
  text-align: center;
  margin-bottom: 0px;
  color: #0e5cad;
}
.form-header .headcol p {
  margin-bottom: 0px;
  text-align: center;
  font-weight: 600;
  font-size: 1.3rem;
  color: #0e5cad;
}
@media screen and (max-width: 1075px) {
  .form-header .headcol h4 {
    font-size: 2rem;
  }
}
@media screen and (max-width: 935px) {
  .form-header .headcol p {
    font-size: 1rem;
  }
}
@media screen and (max-width: 767px) {
  .form-header .headcol p {
    font-size: 1.2rem;
  }
}

.cinfo {
  text-align: center !important;
  font-weight: 400 !important;
  font-size: 1rem !important;
  margin-top: 5px;
}
.cinfo span {
  margin-right: 5px;
}
.cinfo span i {
  font-size: 0.9rem;
}

.form-body {
  padding: 30px;
}
.form-body .form-title {
  border-bottom: 1px solid #CCC;
  margin-bottom: 30px;
}
.form-body .form-title h4 {
  font-size: 1rem;
  padding: 10px;
  border-bottom: 1px solid #0e5cad;
  width: 300px;
  margin: 0px;
  font-weight: 600;
}

.row label {
  padding-top: 4px;
}
.row .form-control {
  background-color: rgba(204, 204, 204, 0.12);
  border-radius: 0px;
  margin-bottom: 30px;
}
.row .form-control:active {
  border: 2px solid #0e5cad !important;
}
.row .form-control:focus {
  border: 2px solid #0e5cad !important;
  box-shadow: none;
}
.row input {
  margin-bottom: 30px;
}
.row .indc {
  float: right;
  padding-top: 5px;
}
@media screen and (max-width: 767px) {
  .row .indc {
    float: none;
  }
}
.row .req {
  color: #f00;
  font-size: 0.7rem;
}

/*# sourceMappingURL=style.css.map */
</style>';
    echo $strStdInfo;
	
	}


function singleStudentFeedata(){
    
    $data = '';
    //$session_id = $this->input->post('session_id');
    $session_id = $this->session->userdata('member_sessionid');
    $campus_id = $this->session->userdata('member_campusid');
    $schoolinfo = getSchoolInfo();
    //$cls_sec_id = $this->input->post('cls_sec_id');
    $student_id = $this->input->post('student_id');

    //$this->db->where('session_id', $session_id);
    $this->db->where('system_id', $schoolinfo->system_id);
    $academicSession = $this->db->get('academic_session')->result();
  
  foreach($academicSession as $sessionValue){
    
    $data .= '<table class="table">';
    $studentClass = $this->db->from('student_class')
      ->where('session_id', (int) $sessionValue->session_id)
      ->where('student_id', (int) $student_id)
      ->get()->result();

    
    $start = new DateTime($sessionValue->start_date);
    //$start->modify('first day of this month');
    $start->modify('first day of next month');
    $end   = new DateTime($sessionValue->end_date);
    $end->modify('first day of next month');
    $interval = DateInterval::createFromDateString('1 month');
    $period   = new DatePeriod($start, $interval, $end);

    
    if(!empty($studentClass)){
    $data .= '<tr><th></th><th style="width: 115px;"></th>';
    foreach ($period as $dt) {
        $Yearmonths = $dt->format("m/Y");
        $data .= '<th>'.$Yearmonths.'</th>';
    }
    
    $data .= '</tr>';
    
    foreach($studentClass as $students){
      
    $this->db->where('student_id', $students->student_id);
    $studentInfo = $this->db->get('students')->row();

    $data .= '<tr><th>'.$sessionValue->session_name.'</th>';
    $data .= '<td>';
    $data .= '<div style="color:#000;border-bottom:1px solid #000;">Total</div>';
    $data .= '<div style="color:#000;border-bottom:1px solid #000;">Paid</div>';
    $data .= '<div style="color:#000;border-bottom:1px solid #000;">Discount</div>';
    $data .= '<div style="color:#000;">Balance</div>';
    $data .= '</td>';

    foreach ($period as $dt) {
        
        $Yearmonths = $dt->format("m/Y");

        $monthlyFeeSub = $this->db->select('fee_type_id')->from('fee_type')->where('is_monthly_fee', 1)->get_compiled_select();
        $studentId = (int) $students->student_id;
        $feeChalanSum = function (?string $status = null) use ($studentId, $Yearmonths, $monthlyFeeSub) {
          $builder = $this->db->table('fee_chalan')
            ->selectSum('amount', 'total')
            ->where('student_id', $studentId)
            ->where('fee_month', $Yearmonths)
            ->where("fee_type_id IN ($monthlyFeeSub)", null, false);
          if ($status !== null) {
            $builder->where('status', $status);
          }
          return $builder->get()->row();
        };
        $feeInfo = $feeChalanSum();
        $paidInfo = $feeChalanSum('paid');
        $unpaidInfo = $feeChalanSum('unpaid');
        $discountInfo = $feeChalanSum('discounted');


    // echo "<pre>";
    // print_r($Yearmonths);
    // echo "</pre>";
    // exit;
      
  //      if($feeInfo->total == $paidInfo->total){
  //       $data .= '<td style="font-size:12px;">';
  //      if($feeInfo->total){
  //        $data .= '<div style="background:green;color:#fff;">'.$feeInfo->total.'/- </div>';
  //    }
  //    $data .= '</td>';
  //  }else if($feeInfo->total == $unpaidInfo->total){

  //    $data .= '<td style="font-size:12px;">';
  //      if($feeInfo->total){
  //        $data .= '<div style="background:red;color:#fff;">'.$feeInfo->total.'/- </div>';
  //    }
  //    $data .= '</td>';

  //  }else  if($unpaidInfo->total != 0){

  //    $data .= '<td style="font-size:12px;">';
  //      if($feeInfo->total){
  //        $data .= '<div style="">'.($feeInfo->total).'/- </div>';
  //        $data .= '<div style="background:yellow;color:#000;">'.($feeInfo->total - $paidInfo->total).'/- </div>';
  //    }
  //    $data .= '</td>';

  // }else

  {
        $data .= '<td style="">';
        if($feeInfo->total){
          $data .= '<div style="color:#000;border-bottom:1px solid #000;">'.$feeInfo->total.' </div>';
      }else{
        $data .= '<div style="color:#000;border-bottom:1px solid #000;"> - </div>';
      }
      if($paidInfo->total){
          $data .= '<div style="color:#000;border-bottom:1px solid #000;">'.$paidInfo->total.' </div>';
      }else{
        $data .= '<div style="color:#000;border-bottom:1px solid #000;"> - </div>';
      }
      // if($unpaidInfo->total){
     //     $data .= '<div style="background:red;color:#fff;">'.$unpaidInfo->total.' </div>';
      // }else{
      //  $data .= '<div style="background:red;color:#fff;">0/- </div>';
      // }
        if($discountInfo->total){
          $data .= '<div style="color:#000;border-bottom:1px solid #000;">'.$discountInfo->total.'</div>';
      }else{
        $data .= '<div style="color:#000;border-bottom:1px solid #000;"> - </div>';
      }
      if($unpaidInfo->total != 0){
        $data .= '<div style="yellow;color:#000;">'.($feeInfo->total - $paidInfo->total).' </div>';
      }else{
        $data .= '<div style="color:#000;"> - </div>';
      }

       $data .= '</td>';
    }
    }

    $data .= '</tr>';

    }
  }

  }
    $data .= '</table><style type="text/css">
  .table td, .table th{
    padding: 2px 0px;
    text-align: center;
    vertical-align: middle;
    font-size: 12px;
    border-top: 1px solid #dee2e6;
  }
  tr{border-bottom:2px solid #000;}
</style>';
    $this->output->set_output($data);
  }


function singleStudentAttendancedata(){
    
  $data = '';
  //$session_id = $this->input->post('session_id');
  $session_id = $this->session->userdata('member_sessionid');
  $campus_id = $this->session->userdata('member_campusid');
  $schoolinfo = getSchoolInfo();
  //$cls_sec_id = $this->input->post('cls_sec_id');
  $student_id = $this->input->post('student_id');
  //$this->db->where('session_id', $session_id);
  $this->db->where('system_id', $schoolinfo->system_id);
  $academicSession = $this->db->get('academic_session')->result();
    
  foreach($academicSession as $sessionValue){
    
    $data .= '<table class="table">';
    $studentClass = $this->db->from('student_class')
      ->where('session_id', (int) $sessionValue->session_id)
      ->where('student_id', (int) $student_id)
      ->get()->result();

  
    $start = new DateTime($sessionValue->start_date);
    //$start->modify('first day of this month');
    $start->modify('first day of next month');
    $end   = new DateTime($sessionValue->end_date);
    $end->modify('first day of next month');
    $interval = DateInterval::createFromDateString('1 month');
    $period   = new DatePeriod($start, $interval, $end);

    
    if(!empty($studentClass)){
    $data .= '<tr><th></th><th style="width: 115px;"></th>';
    foreach ($period as $dt) {
        $Yearmonths = $dt->format("m/Y");
        $data .= '<th>'.$Yearmonths.'</th>';
    }
    
    $data .= '</tr>';
    
    foreach($studentClass as $students){
      
    $this->db->where('student_id', $students->student_id);
    $studentInfo = $this->db->get('students')->row();

    $data .= '<tr><th>'.$sessionValue->session_name.'</th>';
    $data .= '<td>';
    $data .= '<div style="color:#000;border-bottom:1px solid #000;">Present Total</div>';
    $data .= '<div style="color:#000;border-bottom:1px solid #000;">Absent Total</div>';
    $data .= '<div style="color:#000;border-bottom:1px solid #000;">Late Comming</div>';
    $data .= '<div style="color:#000;border-bottom:1px solid #000;">Early Left</div>';
    $data .= '<div style="color:#000;">Off Days</div>';
    $data .= '</td>';

    foreach ($period as $dt) {
        
      $Year = (int) $dt->format('Y');
      $Month = (int) $dt->format('m');
      $studentId = (int) $students->student_id;
      $monthStart = sprintf('%04d-%02d-01', $Year, $Month);
      $monthEnd = date('Y-m-d', strtotime($monthStart . ' +1 month'));

      $attendanceCount = function (array $extraWhere = []) use ($studentId, $monthStart, $monthEnd) {
        $builder = $this->db->table('attendance')
          ->selectCount('attendance_id', 'total')
          ->where('student_id', $studentId)
          ->where('date >=', $monthStart)
          ->where('date <', $monthEnd);
        foreach ($extraWhere as $field => $value) {
          if (is_int($field)) {
            $builder->where($value, null, false);
          } else {
            $builder->where($field, $value);
          }
        }
        return $builder->get()->row();
      };

      $presentTotal = $attendanceCount(['status' => 'P']);
      $absTotal = $attendanceCount(['status' => 'A']);
      $lcTotal = $attendanceCount(['status' => 'A', 'lc_duration >' => 0]);
      $elTotal = $attendanceCount(['status' => 'A', 'el_duration >' => 0]);
      $offDaysTotal = $attendanceCount([0 => 'checkin = checkout']);

      {
        $data .= '<td style="">';
        if($presentTotal->total){
          $data .= '<div style="color:#000;border-bottom:1px solid #000;">'.$presentTotal->total.' </div>';
      }else{
        $data .= '<div style="color:#000;border-bottom:1px solid #000;"> - </div>';
      }
      if($absTotal->total){
          $data .= '<div style="color:#000;border-bottom:1px solid #000;">'.$absTotal->total.' </div>';
      }else{
        $data .= '<div style="color:#000;border-bottom:1px solid #000;"> - </div>';
      }
     
      if($lcTotal->total){
          $data .= '<div style="color:#000;border-bottom:1px solid #000;">'.$lc->total.'</div>';
      }else{
        $data .= '<div style="color:#000;border-bottom:1px solid #000;"> - </div>';
      }

      if($elTotal->total != 0){
        $data .= '<div style="yellow;color:#000;border-bottom:1px solid #000;">'.($elTotal->total).' </div>';
      }else{
        $data .= '<div style="color:#000;border-bottom:1px solid #000;"> - </div>';
      }

      if($offDaysTotal->total != 0){
        $data .= '<div style="yellow;color:#000;">'.($offDaysTotal->total).' </div>';
      }else{
        $data .= '<div style="color:#000;"> - </div>';
      }

      $data .= '</td>';
    }
    
    }

    $data .= '</tr>';

    }
  }

  }
    $data .= '</table><style type="text/css">
  .table td, .table th{
    padding: 2px 0px;
    text-align: center;
    vertical-align: middle;
    font-size: 12px;
    border-top: 1px solid #dee2e6;
  }
  tr{border-bottom:2px solid #000;}
</style>';
    $this->output->set_output($data);
  }


	 function result(){

	 }
	 
	function save(){
		$id = intval($this->input->post('id'));
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d H:i:s');
		$schoolinfo = getSchoolInfo();
		
		$profile_photo =  ''; //array();
		header('Content-Type: application/json');
	  	$config['upload_path']   = './system-logo/'; 
	  	$config['allowed_types'] ="gif|jpg|jpeg|png|iso|dmg|zip|rar|doc|docx|xls|xlsx|ppt|pptx|csv|ods|ogv|odt|odp|pdf|rtf|sxc|sxi|txt|exe|wav|avi|mpeg|mp3|mp4|3gp";  
	  	$config['max_size']   = 1024;
	  	$this->load->library('upload', $config);

		$this->upload->initialize($config);
		$this->upload->do_upload('image');  // File Name
		$image = $this->upload->data(); 
		$imageName = $image['file_name']; 
	  			

			$this->db->trans_begin();
			$data = array(
				'system_name' => trim($this->input->post('system_name')),
				//'short_name' => trim($this->input->post('short_name')),
				'address' => trim($this->input->post('address')),
				'city' => trim($this->input->post('city')),
				'state' => trim($this->input->post('state')),
				'zip' => trim($this->input->post('zip')),
				'country' => trim($this->input->post('country')),
				'owner_name' => trim($this->input->post('owner_name')),
				'landline_number' => trim($this->input->post('landline_number')),
				'mob_number' => trim($this->input->post('mob_number')),
				'reg_text' => trim($this->input->post('reg_text')),
				'slogan' => trim($this->input->post('slogan')),
				'logo' => trim($imageName),
				'updated_date' => trim($date),
				'user_id' => trim($user_id)
				
			);
			$this->db->where('system_id', $id);
			$this->db->update('system', $data);
			
			$this->db->trans_complete();
			
			$this->db->where('system_id', $schoolinfo->system_id);
			$academic_session_info = $this->db->get('academic_session')->row();
				
			if(empty($academic_session_info->session_id)){
				$this->output->set_output(json_encode(array('session_id' => FALSE, 'msg' => 'Update System Success')));
			}else{
				json_response(array('success' => TRUE, 'msg' => 'Update System Success'));
			}
			
	}

	function update_password(){
			$this->form_validation->set_rules('password', 'New Password', 'trim|required');
			if($this->form_validation->run() === FALSE){
				json_response(array('success' => FALSE, 'msg' => validation_errors()));
			}else{
				$user_id = intval($this->input->post('user_id'));
				$this->db->where('id', $user_id);
				$data = array(
					'password' => password_hash(trim($this->input->post('password')), PASSWORD_BCRYPT)
				);
				$this->db->update('users', $data);
				json_response(array('success' => TRUE, 'msg' => 'Change Password Success'));
			}	
		
	}

}
// end this file