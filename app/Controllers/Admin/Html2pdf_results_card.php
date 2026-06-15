<?php
namespace App\Controllers\Admin;



/**

 * Result Card Manage

 *

 * @author		Maqsood Ahmed

 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions

 * @email		maqsoodjamvi@gmail.com

 * @filesource

 */





class Html2pdf_results_card extends MY_Controller {



	function __construct(){

		parent::__construct();

		check_permission('admin-results');

	}



/**

 * Index Page for this controller.

 */



public function index()

{

	$data =  $this->data();

	$content = '';



$content = '';

  

 foreach ($data as  $value) { 

  

  $termlastkey = count($value['terms'])-1;

  $examName = $value['terms'][$termlastkey]['terms_name'];

  $content .= '<page>

    <div style="border:1px dashed #000; border-radius:10px; text-align:center;">

	<table style="width:100%;">

	<tr>

	<td style="width:30%;"></td>

	<td>

	<h1 style="margin-top:5px;margin-bottom:5px; font-size:50px; font-family:"Times New Roman", Times, serif;">TIME School System</h1>

	  <h3 style="margin:0px;padding:0px;margin-top:5px;font-family: "Orbitron";font-size: 22px;">'.$examName.' '.$value['campus_name'].' </h3>

	</td>

	</tr>

	</table>

    </div>

    <div style="float:left; width:100%; margin:10px auto;">

      <table style="width:100%;border:1px solid #000;padding:0px; margin:0px;" cellpadding="0" cellspacing="0" >

        <tr>

          <td style="border-bottom:1px solid #000;width:50%;padding-left:10px;"> Name: '.$value['name'].'</td>

          <td style="border-start:1px solid #000;border-bottom:1px solid #000;width:50%;padding-left:10px;"> Reg #: '.$value['reg_no'].'</td>

        </tr>

        <tr>

          <td style="border-bottom:1px solid #000;width:50%;padding-left:10px;"> Father Name:'.$value['f_name'].'</td>

          <td style="border-start:1px solid #000;border-bottom:1px solid #000;width:50%;padding-left:10px;"> Grade: '.$value['class'].'</td>

        </tr>

        <tr>

          <td style="width:50%;padding-left:10px;"> Contact # 1: '.$value['father_contact'].'</td>

          <td style="border-start:1px solid #000;width:50%;padding-left:10px;"> Contact # 2: '. $value['mother_contact'].'</td>

        </tr>

      </table>

    </div>

    <div style="border:2px solid #000; float:left; width:100%; margin:10px auto; padding:2px;">

      <div style="border:2px solid #000;float:left;width:100%;text-align:center;font-weight:bold;padding: 5px;font-size: 18px;color: #000;line-height: 20px;">ACADEMIC PROGRESS</div>

      <table class="table" style="width:100%;margin-top:2px;"  cellpadding="0" cellspacing="0">

        <thead>

          <tr style="border:1px solid #000;">

            <th style="width:15%;border:1px solid #000;padding:5px;">Subject</th>';

            

			$widthPercent = ((85/count($value['terms'])));           

			

			foreach($value['terms'] as $term){  

			//print_r($term);

            $content .= '<th style="width:'.$widthPercent.'%;border:1px solid #000;padding:5px;">'.$term['terms_name'].'</th>';

            

			} 

            

        $content .= '</tr></thead><tbody>';

        foreach ($value['result'] as $key => $valueresult) {     

        $content .= '<tr><td style="border-bottom:1px solid #000;padding:5px;">'.$key.'</td>';

		

		 $emptycol = (count($value['terms'])-count($valueresult));

		if($emptycol >0){

		 

		 for($i=1; $i<=$emptycol; $i++){

		 

		 $content .= '<td style="border-bottom:1px solid #000;padding:5px;">0</td>';

		 

		 }

		

		 }

          

          foreach($valueresult as $numbers){ 

          

          $content .= '<td style="border-bottom:1px solid #000;padding:5px;">'.$numbers.'</td>'; 

          } 

          

          $content .= '</tr>';

        }

		

$content .= '<tr><td style="border-bottom:1px solid #000;padding:5px;">Percentage</td>';

		

		 $emptycol1 = (count($value['terms'])-count($value['resulttotalpercentage']));

		if($emptycol1 >0){

		 

		 for($i=1; $i<=$emptycol1; $i++){

		 

		 $content .= '<td style="border-bottom:1px solid #000;padding:5px;">0</td>';

		 

		 }

		

		 }



foreach($value['resulttotalpercentage'] as $compiledresultpercentage){

$content .= '<td style="border-bottom:1px solid #000;padding:5px;">'.$compiledresultpercentage['obtain_total_mark'].'/'.$compiledresultpercentage['exam_total_mark'].' ('.round($compiledresultpercentage['exampercentage']).'% )  </td>';

}

$content .= '</tr>';



        

        $content .= '</tbody></table>';

		

		$content .= '<div style="border:2px solid #000;float:left;width:100%;text-align:center;font-weight:bold;padding: 5px;font-size: 18px;color: #000;line-height: 20px;">NON ACADEMIC</div>

<table cellpadding="0" cellspacing="0" style="width:100%;">

<thead>

<tr><th style="width:15%;border:1px solid #000;padding:5px;">Subject</th>';

  

   foreach($value['terms'] as $nonacademicterm){



  $percentagewidth =  (85/count($value['terms']));

  

$content .= '<th style="width:'.$percentagewidth.'%;border:1px solid #000;padding:5px;">'.$nonacademicterm['terms_name'].'</th>';

  } 

$content .= '</tr></thead><tbody>';



 if($value['non_academic_result']){ 

 

  foreach ($value['non_academic_result'] as $key => $nonacadmicvalue) {



$content .= '<tr ><td style="border-bottom:1px solid #000;padding:5px;">'.$key.'</td>';



 $emptycol2 = (count($value['terms'])-count($nonacadmicvalue));

		if($emptycol2 >0){

		 

		 for($i=1; $i<=$emptycol2; $i++){

		 

		 $content .= '<td style="border-bottom:1px solid #000;padding:5px;">0</td>';

		 

		 }

		

		 }



 foreach($nonacadmicvalue as $nonacadmicnumbers){ 

$content .= '<td style="border-bottom:1px solid #000;padding:5px;">'.$nonacadmicnumbers.'</td>'; 

 } 



$content .= '</tr>';

 } 

 } 

$content .= '</tbody></table>';

		

	$content .= '</div></page><div style="clear:both;"></div>';

  } 

  

 // $content .= '</div></div>';

	

  $buffer = ($content);  

// exit;

    ob_start();	

	require_once(APPPATH.'libraries/html2pdf/html2pdf.class.php');

    

	$html2pdf = new HTML2PDF('L', 'A4', 'en');

	//$html2pdf->setModeDebug();

	$html2pdf->setTestTdInOnePage(false);

	$html2pdf->pdf->SetDisplayMode('fullpage');

	$html2pdf->writeHTML($buffer, isset($_GET['vuehtml']));

	$campus_id = $this->session->userdata('member_campusid');

	// build new name and commit

	$filename= 'result'.$campus_id.'.pdf';

	$html2pdf->Output($filename, 'F');

	Header("Content-type: application/pdf"); 

    Header("Content-Disposition: attachment; filename=$filename"); 

   //readfile("$filename");	

	echo "<div style='padding:20px;color:green;'>PDF generated Successfully</div>";	

	echo "<div style='padding:20px;color:green;'><a target='_blank' href='".$filename."'> Click Here To Download PDF </a> </div>";	

	

}



public function data()

	{

		$campus_id = $this->session->userdata('member_campusid');

		$sessionid = $this->session->userdata('member_sessionid');

		$sessionData = array(

		'campusid' => $campus_id,

		'sessionid' => $sessionid

		);

		$this->template_data['sessionData'] = $sessionData;



//$campus_id = $this->input->get('campus_id');

				

$student_class = $this->db->query('SELECT t1.class_id,t2.student_id, t2.campus_id,t2.reg_no,t2.first_name,t2.last_name,t2.parent_id FROM student_class t1, students t2 WHERE t1.student_id = t2.student_id and t1.status=1 and t1.session_id='.$sessionid.' and t2.campus_id='.$campus_id.' order by t1.class_id asc')->result(); 



		$date_now = date("Y-m-d");

		$where = "session_id=".$sessionid." AND campus_id=".$campus_id." AND exam_start_date < '".$date_now."'";

		$this->db->where($where);	

		$exams = $this->db->get('exam')->result();





		$terms = array();



		foreach ($exams as  $value) {

			//print_r($value);

			$terms[] = array('terms_name' => $value->exam_name);

		}



			

		foreach ($student_class as $studentinfo) {



		$this->db->where('class_id', $studentinfo->class_id);

		$class_subjects = $this->db->get('class_subjects')->result();





		$this->db->where('student_id', $studentinfo->student_id);

		$student_info = $this->db->get('students')->row();

		

		$this->db->where('campus_id', $campus_id);

		$campus_info = $this->db->get('campus')->row();



		if($student_info){

		

		$this->db->where('parent_id', $student_info->parent_id);

		$parent_info = $this->db->get('parents')->row();





		$this->db->where('class_id', $studentinfo->class_id);

		$class_info = $this->db->get('classes')->row();



		//print_r($student_info);

		$resultcard = array();	

		$resulttotal = array();

		$resulttotalpercentage = array();

		$nonacademicresultcard = array();

		foreach($class_subjects as $subect_id){



		

		   $this->db->where('sid', $subect_id->subject_id);

		   $subjects = $this->db->get('allsubject')->row();  

			

			$where = "student_id=".$studentinfo->student_id." AND session_id=".$sessionid." AND subject_id='".$subjects->sid."'   ORDER BY eid ASC";

			$this->db->where($where);

			$stdresults = $this->db->get('studentsresults')->result();	

			

			foreach($stdresults as $termresult){



				$where = "student_id=".$studentinfo->student_id." AND eid=".$termresult->eid;

				$this->db->where($where);	

				$results = $this->db->get('results')->row();

				if($results){



				$position  = $this->addOrdinalNumberSuffix($results->position);	

				$resulttotal[$termresult->eid] = array('position' => $position);

				if($results->obtain_total_mark == 0 || $results->exam_total_mark == 0)

				{

				$exampercentage	= 0;

				}elseif($results->obtain_total_mark > 0 and $results->exam_total_mark > 0){

				$exampercentage	= ($results->obtain_total_mark/$results->exam_total_mark)*100;

				}

					

				$resulttotalpercentage[$termresult->eid] = array(

					'exam_total_mark' => $results->exam_total_mark,

					'obtain_total_mark' => $results->obtain_total_mark,

					'exampercentage'  => $exampercentage

					 ); 

					 

			}



			   $this->db->where('sid', $subjects->sid);

			   $this->db->where('subject_type', 'academic');

			   $academicsubjects = $this->db->get('allsubject')->row();

			   

			if($academicsubjects){

			$datesheetinfo = '';

			$percentage = 0;

			   $this->db->where('eid', $termresult->eid);

			   $this->db->where('class_id', $studentinfo->class_id);

			   $this->db->where('subject_id', $academicsubjects->sid);

			   $datesheetinfo = $this->db->get('datesheet')->row();

			   

			

			 if(empty($datesheetinfo)){

			 $subject_exam_total_marks =0;

			 }else{

			  $subject_exam_total_marks = $datesheetinfo->total_marks;

			 }

			

			if($termresult->obtained_marks == 0 || $subject_exam_total_marks == 0)

			{

			$percentage = 0;

			}else{

			

			//$subject_exam_total_marks = $datesheetinfo->total_marks; 

 			 $percentage = round(($termresult->obtained_marks/$subject_exam_total_marks)*100); 

			}

			

			$grade ='';

			

			

			if($percentage >= 80 and $percentage <= 100){

				$grade = 'A';

			}elseif($percentage >= 70 and $percentage <= 79)

			{

				$grade =  'B';

			}elseif($percentage >= 60 and $percentage <= 69)

			{

				$grade =  'C';

			}elseif($percentage >= 50 and $percentage <= 59)

			{

				$grade =  'D';

			}elseif($percentage >= 0 and $percentage < 50)

			{

			$grade =  'F';

			}elseif($percentage == 0)

			{

				$grade =  'F';

			}else{

				$grade =  '';

			}

			

			

			

			$resultcard[$subjects->subject_name][$termresult->eid] =  $termresult->obtained_marks."/ ".$subject_exam_total_marks; //." (".$percentage."%) ".$grade;

			

			}else{

			

			  $this->db->where('sid', $subjects->sid);

			  $this->db->where('subject_type', 'non_academic');

			  $nonacademicsubjects = $this->db->get('allsubject')->row();

			  

			   $this->db->where('eid', $termresult->eid);

			   $this->db->where('class_id', $studentinfo->class_id);

			   $this->db->where('subject_id', $nonacademicsubjects->sid);

			   $nondatesheetinfo = $this->db->get('datesheet')->row();

			   

			

			 if(empty($nondatesheetinfo)){

			 $nonsubject_exam_total_marks =0;

			 }else{

			  $nonsubject_exam_total_marks = $nondatesheetinfo->total_marks;

			 }

			

			   $nonacademicresultcard[$nonacademicsubjects->subject_name][$termresult->eid] =  $termresult->obtained_marks."/ ".$nonsubject_exam_total_marks;

			

			}



			}

			  

		

		}



		//$terms = array();





		$data[] = array(

		'class' => $class_info->class_name,	

		'campus_name' => $campus_info->campus_name,

		'name' => $student_info->first_name." ".$student_info->last_name,	

		'f_name' => $parent_info->f_name,

		'reg_no' => $student_info->reg_no,

		'father_contact' => $parent_info->father_contact,

		'mother_contact' => $parent_info->mother_contact,

		'terms' => $terms,

		'result' => $resultcard,

		'non_academic_result' => $nonacademicresultcard,

		'resulttotal' => $resulttotal,

		'resulttotalpercentage' => $resulttotalpercentage

		);

		 

		 }



		}

		

		 return $data;

	  

	}



	function addOrdinalNumberSuffix($num) {

    if (!in_array(($num % 100),array(11,12,13))){

      switch ($num % 10) {

        // Handle 1st, 2nd, 3rd

        case 1:  return $num.'st';

        case 2:  return $num.'nd';

        case 3:  return $num.'rd';

      }

    }

    return $num.'th';

  }



}

// end this file

