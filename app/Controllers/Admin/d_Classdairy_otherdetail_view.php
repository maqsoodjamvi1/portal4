<?php
namespace App\Controllers\Admin;


/**
 * Class Dairy Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2016~2099 timesoftsol.com
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Classdairy_otherdetail_view extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-classdairy');
	} 

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$campus_id = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$term_session_id = $this->input->post('term_id');
		$sessionData = array(
		'campusid' => $campus_id,
		'sessionid' => $sessionid
		);
		$this->template_data['sessionData'] = $sessionData;
	
		$this->db->where('session_id', $sessionid);
	    $terms_session_info = $this->db->get('terms_session')->result();
	    $this->template_data['terms_session_info'] = $terms_session_info;
	
		$this->load->view('classdairy_otherdetail_view', $this->template_data);
	}

	function data(){
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$term_session_id = $this->input->post('term_id');
		$term_weeks_id = $this->input->post('term_weeks_id');

		if(empty($term_weeks_id)){
			echo "<div class='col-lg-12 bg-danger text-center'>Select Term Week </div>";	
			exit;
		}
		
	
		$this->db->where('session_id', $sessionid);
	    $terms_session_info = $this->db->get('terms_session')->result();
	    $this->template_data['terms_session_info'] = $terms_session_info;

		$this->db->where('campus_id', $campusid);	
		$this->db->where('status', 1);	
		$class_info = $this->db->get('class_section')->result();

		$this->db->where('term_weeks_id', $term_weeks_id);
		$term_weeks = $this->db->get('term_weeks')->row();
	    
	    
		$begin = new DateTime( $term_weeks->start_date );
		$end = new DateTime( $term_weeks->end_date );
		
		$end = $end->modify( '+1 day' ); 

		$interval = new DateInterval('P1D');
		
	    $period = new DatePeriod($begin,$interval,$end);

			
		foreach ($class_info as $sections) {

		$this->db->where('cls_sec_id', $sections->cls_sec_id);
		$this->db->where('status', 1);
		$section_subjects = $this->db->get('section_subjects')->result();

		$week_dates = array();
		$resultcard = array();	
		$resulttotal = array();
		$resulttotalpercentage = array();
		$nonacademicresultcard = array();
        
		foreach ($period as $key => $value) {
			//print_r($value);
		    //echo $value->format('Y-m-d')."<br>";  
			$date = $value->format('Y-m-d');
			$nameOfDay = date('D', strtotime($date));
			
			$week_dates[] =  $date;

		foreach($section_subjects as $subect_id){
			//print_r($subect_id);

			//$this->db->where('campus_id', $campusid);
			$this->db->where('term_weeks_id', $term_weeks_id);
			$this->db->where('sec_sub_id', $subect_id->sec_sub_id);
			$this->db->where('date', $date);
    		$classdairy_info = $this->db->get('classdairy')->row();
		 
          if($classdairy_info){
   
		   $this->db->where('sid', $subect_id->subject_id);
		   $academicsubjects = $this->db->get('allsubject')->row();

		   if($academicsubjects){
		   		$resultcard[$academicsubjects->subject_name][$date] = $classdairy_info->other_detail;	 
		   	} 
		}
		}
		}

		//$terms = array();
		$where = "session_id=".$sessionid;
		$this->db->where($where);	
		$session_info = $this->db->get('academic_session')->row();

		$sectioninfo = getClassSection($sections->cls_sec_id);
		//print_r($sectioninfo);
		
		$data[] = array(
		'class' => $sectioninfo['sectionclassname'],
		'session_name' => $session_info->session_name,	
		'week_dates' => $week_dates,
		'result' => $resultcard,
		); 
		
		}
	     //$data =  $this->data();
	     $this->template_data['data'] = $data;

	  
			foreach ($data as  $value) { 
			 
			  ?>
			  <p style="page-break-before: always;">&nbsp;</p>
<page>
<style type="text/css">
		p{ margin-bottom: 0px;font-size: 14px !important; }
		p span{font-size: 14px !important;}
	</style>
<div style="border:2px solid #000; float:left; width:100%; margin:10px auto; padding:2px;">
	<div style="width:100%;border:2px solid #000;float:left;width:100%;text-align:center;font-weight:bold;padding: 5px;font-size: 18px;color: #000;line-height: 20px;">Weekly Dairy ( echo $value['session_name']; ?>)
<div style="width:100%;padding-left:15px;float:left;font-size: 16px;font-weight: normal;margin-top: 0px;">   echo $value['class']; ?></div>
	</div>
			<table class="table" style="margin-bottom: 2px;">
			<thead>
			<tr><th style="width:5%;border:1px solid #000;">Subject</th>
				 $colWidth = 95/count($value['week_dates']); ?>
			   foreach($value['week_dates'] as $weekdates){ 
			  	//$date = $weekdates->format('Y-m-d');
				 $dateformate = dateFormat($weekdates);
				 $nameOfDay = date('D', strtotime($weekdates));
			  	?>
			  <th style="border:1px solid #000;width:  echo $colWidth; ?>%;">  echo $dateformate." (".$nameOfDay.")";//$weekdates['week_dates']; ?></th>
			  } ?>
			</tr>

			</thead>
			<tbody>
			 foreach ($value['result'] as $key => $valueNo) {
			 ?>
			<tr>
			<td style="border:1px solid #000;"> echo $key; ?></td>
			 
			 $emptycol = (count($value['week_dates'])-count($valueNo)); 
					if($emptycol >0){
					 
					 for($i=1; $i<=$emptycol; $i++){
					 
					 echo '<td style="padding:5px;">0</td>';
					 
					 }
					
					 }
			?>
			 foreach($valueNo as $numbers){ ?>
			<td style="border:1px solid #000; if($key == 'Urdu' || $key == 'Nazra'){ ?> direction: rtl;  } ?>"> echo $numbers; ?></td>  } ?>

			</tr>
			 } ?>

			</tbody>
			</table>
			
			</div></page><br><br><br><br>
         <div style="clear: both;margin-bottom: 60px;"></div>
     <!--      <p style="page-break-before: always;">&nbsp;</p> -->
			 //exit; ?>
        
          } 

		//$this->output->set_output(json_encode($response));
	}

}
// end this file
