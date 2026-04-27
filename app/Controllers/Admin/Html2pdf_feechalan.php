<?php
namespace App\Controllers\Admin;

 

/**

 * HTML2PDF Fee Chalan Manage

 *

 * @author		Maqsood Ahmed

 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions

 * @email		maqsoodjamvi@gmail.com

 * @filesource

 */







class Html2pdf_feechalan extends BaseController {



function __construct(){

		parent::__construct();

		check_permission('admin-fee-chalan');

	}



	/**

	 * Index Page for this controller.

	 */	

	

public function index() {	

	$data =  $this->data();

    $content = '';

		

 foreach($data  as $student_info){  

 	

 	if(isset($student_info['father_contact'])){

			$father_contact = $student_info['father_contact'];

			}else{

			$father_contact = ''; 

			}

			

			if(isset($student_info['mother_contact'])){

			$mother_contact = $student_info['mother_contact']; 

			}else{

			$mother_contact = '';

			}

          $content .= '<page><table style="max-width:100%; width:100%;"><tr><td style="width:32%;">

          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$father_contact.' &nbsp;&nbsp;&nbsp; '.$mother_contact.'<div style="width:98%;display:inline; margin-left:10px;float"left;>

                <div style="border: 1px solid #000000;"><span dir="rtl" lang="ur" style="text-align:center;" >  '.($student_info['chalan_h_msg']).'</span>

                <span style="text-align:center; line-height:20px;display:block;"><br />

                <b>Fee Slip - Bank Copy</b><br />

                  TIME School System<br />  

                  '.$student_info['campus_name'].', '.$student_info['location'].'<br />

                  '.$student_info['bank_name'].'<br />

                  '.$student_info['bank_address'].', '.$student_info['bank_code'].'.<br />

                  Account No: '.$student_info['bank_acc'].'<br />

                  <br />

                  </span>

				  <span style="">

		<hr style="margin: 0;border-bottom:0.2px solid #000000;">		

   <span style="display:block;text-align:left;padding-left:5px;float:left;line-height:10px;"> Reg No :'.$student_info['reg_no'].'</span><hr style="margin: 0;border-bottom:0.2px solid #000000;">

                    <span style="display:block;text-align:left;padding-left:5px;float:left;line-height:10px;"> Name:'.$student_info['student_name'].'</span><hr style="margin: 0;border-bottom:0.2px solid #000000;float:left;">

                    <span style="display:block;text-align:left;padding-left:5px;float:left;line-height:10px;"> Father Name:'.$student_info['f_name'].'</span><hr style="margin: 0;border-bottom:0.2px solid #000000;">

                    <span style="width:50%;float:left;padding-left:10px;display:block;line-height:10px;"> Grade:'. $student_info['class_name'].'</span>

                    <span style="width:50%;float:left;padding-left:10px;display:block;line-height:10px;"> &nbsp;&nbsp;&nbsp;Fee Month:'.$student_info['fee_month'].'</span><hr style="margin: 0;border-bottom:0.2px solid #000000;clear:both;">

                    <span style=" width:50%;float:left;padding-right:10px;display:block;line-height:10px;"> Issue Date:'. $student_info['issue_date'].'</span>

                    <span style="width:50%;float:left;padding-left:10px;display:block;line-height:10px;"> &nbsp;&nbsp;&nbsp;Due Date: '.$student_info['due_date'].'</span><hr style="margin: 0;border-bottom:0.2px solid #000000;">

				  </span>';

	

	 $content .= '<table cellspacing="0"   style="margin:4px;border:1px solid #000;width:95%;line-height:20px;">

                      <tr style="border-bottom:1px solid #000;padding:5px;">

                        <th style="padding:5px;border-bottom:1px solid #000;width:31%;">Particulars</th>

                        <th style="padding:5px;border-bottom:1px solid #000;width:31%;">Amount</th>

						<th style="padding:5px;border-bottom:1px solid #000;width:30%;">Discount</th>

                      </tr>';

					 

	$total = 0;



	//echo "<pre>";

	//	print_r($student_info); 

	//	echo "</pre>";

	//	exit;

	 

	 foreach($student_info['student_fee'] as $fee_info){ 

		 

	  $total = $total + $fee_info['amount'];

	   

	 

                      $content .= '<tr style="border-bottom:1px solid #000;">

                        <td style="border-bottom:1px solid #000;padding:5px;">'.$fee_info["fee_name"].' ('.$fee_info["fee_month"].')</td>

                        <td style="border-bottom:1px solid #000;padding:5px;">'.$fee_info["amount"].'/-</td>';

						 if($fee_info["fee_name"] == "Installment"){

						 $content .= '<td style="border-bottom:1px solid #000;padding:5px;">'.$fee_info["discount"].'</td>';

						}else{

						 $content .= '<td style="border-bottom:1px solid #000;padding:5px;"></td>';

						}

                      $content .= '</tr>';

		 			if($fee_info["fee_name"] == "Installment"){

					$total = ($total-$fee_info["discount"]);

					  }

					

	 } 

					 

					 foreach($student_info['fee_fine'] as  $value) { 

                      $total = ($total + $value['fine_amount']);

                      

                     

                       $content .= '<tr><td style="border-bottom:1px solid #000;padding:5px;">Fine('.$value['fee_month'].')</td><td style="border-bottom:1px solid #000;padding:5px;">'.$value['fine_amount'].'</td><td style="border-bottom:1px solid #000;padding:5px;"></td></tr>';



                  

                    } 



                         $content .= '<tr style="border-bottom:1px solid #000;padding:5px;"><td style="padding:5px;">Total Payable</td>

                        <td style="padding:5px;">'.$total.'/-</td>

						<td ></td>

                      </tr>

                    </table>';

	 

				  $content .= '<br><br><span style="text-align:center;clear:both;display:block;style="padding:5px;""> <strong>Note: </strong>After Due Date Rs. 10/day fine will be charged</span>

				</div>

              </div>

			  </td>

			  <td style="width:32%;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$student_info['father_contact'].' &nbsp;&nbsp;&nbsp; '.$student_info['mother_contact'].'<div style="width:97%;display:inline; margin-left:10px;float:left;">

                <div style="border: 1px solid #000000;"><span dir="rtl" lang="ur" style="text-align:center;" >  '.$student_info['chalan_h_msg'].'</span>

                <span style="text-align:center; line-height:20px;display:block;"><br />

                <b>Fee Slip - School Copy</b><br />

                  TIME School System<br />

                  '.$student_info['campus_name'].', '.$student_info['location'].'<br />

                  '.$student_info['bank_name'].'<br />

                  '.$student_info['bank_address'].', '.$student_info['bank_code'].'.<br />

                  Account No: '.$student_info['bank_acc'].'<br />

                  <br />

                  </span>

				 <div style="clear:both"></div>

                  <span style="">

		<hr style="margin: 0;border-bottom:0.2px solid #000000;">		

   <span style="display:block;text-align:left;padding-left:5px;float:left;"> Reg No :'.$student_info['reg_no'].'</span><hr style="margin: 0;border-bottom:0.2px solid #000000;">

                    <span style="display:block;text-align:left;padding-left:5px;float:left;"> Name:'.$student_info['student_name'].'</span><hr style="margin: 0;border-bottom:0.2px solid #000000;float:left;">

                    <span style="display:block;text-align:left;padding-left:5px;float:left;"> Father Name:'.$student_info['f_name'].'</span><hr style="margin: 0;border-bottom:0.2px solid #000000;">

                    <span style="width:50%;float:left;padding-left:10px;display:block"> Grade:'. $student_info['class_name'].'</span>

                    <span style="width:50%;float:left;padding-left:10px;display:block;"> &nbsp;&nbsp;&nbsp;Fee Month:'.$student_info['fee_month'].'</span><hr style="margin: 0;border-bottom:0.2px solid #000000;clear:both;">

                    <span style=" width:50%;float:left;padding-right:10px;display:block"> Issue Date:'. $student_info['issue_date'].'</span>

                    <span style="width:50%;float:left;padding-left:10px;display:block"> &nbsp;&nbsp;&nbsp;Due Date: '.$student_info['due_date'].'</span><hr style="margin: 0;border-bottom:0.2px solid #000000;">

				  </span>';

	

	 $content .= '<table cellspacing="0"   style="margin:4px;border:1px solid #000;width:95%;">

                      <tr style="border-bottom:1px solid #000;padding:5px;">

                        <th style="padding:5px;line-height:20px;border-bottom:1px solid #000;width:31%;">Particulars</th>

                        <th style="padding:5px;line-height:20px;border-bottom:1px solid #000;width:31%;">Amount</th>

						<th style="padding:5px;line-height:20px;border-bottom:1px solid #000;width:30%;">Discount</th>

                      </tr>';

					 

	$total = 0;

	 

	 foreach($student_info['student_fee'] as $fee_info){ 

		 

	  $total = $total + $fee_info['amount'];

	  

	   

	 

                      $content .= '<tr style="border-bottom:1px solid #000;">

                        <td style="border-bottom:1px solid #000;padding:5px;">'.$fee_info["fee_name"].' ('.$fee_info["fee_month"].')</td>

                        <td style="border-bottom:1px solid #000;padding:5px;">'.$fee_info["amount"].'/-</td>';

						 if($fee_info["fee_name"] == "Installment"){

						 $content .= '<td style="border-bottom:1px solid #000;padding:5px;">'.$fee_info["discount"].'</td>';

						}else{

						 $content .= '<td style="border-bottom:1px solid #000;padding:5px;"></td>';

						}

                      $content .= '</tr>';

		 			if($fee_info["fee_name"] == "Installment"){

					$total = ($total-$fee_info["discount"]);

					  }

					  //$total = ($total-$fee_info["discount"]);

					  //$total = ($total-$fee_info["discount"]);

					 } 

					 foreach($student_info['fee_fine'] as  $value) { 

                      $total = ($total + $value['fine_amount']);

                      

                     

                       $content .= '<tr><td style="border-bottom:1px solid #000;padding:5px;">Fine('.$value['fee_month'].')</td><td style="border-bottom:1px solid #000;padding:5px;">'.$value['fine_amount'].'</td><td style="border-bottom:1px solid #000;padding:5px;"></td></tr>';



                  

                    } 



                         $content .= '<tr style="border-bottom:1px solid #000;padding:5px;"><td style="padding:5px;">Total Payable</td>

                        <td style="padding:5px;">'.$total.'/-</td>

						<td ></td>

                      </tr>

                    </table>';

	 

				  $content .= '<br><br><span style="text-align:center;padding-left:10px;"> <strong>Note: </strong>After Due Date Rs. 10/day fine will be charged</span>

				</div>

              </div>

			  </td>

			  <td style="width:32%;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$student_info['father_contact'].' &nbsp;&nbsp;&nbsp; '.$student_info['mother_contact'].'<div style="width:97%;display:inline; margin-left:10px;float:left;">

                <div style="border: 1px solid #000000;"><span dir="rtl" lang="ur" style="text-align:center;" >  '.$student_info['chalan_h_msg'].'</span>

               <span style="text-align:center; line-height:20px;display:block;"><br />

                <b>Fee Slip - Student Copy</b><br />

                  TIME School System<br />

                  '.$student_info['campus_name'].', '.$student_info['location'].'<br />

                  '.$student_info['bank_name'].'<br />

                  '.$student_info['bank_address'].', '.$student_info['bank_code'].'.<br />

                  Account No: '.$student_info['bank_acc'].'<br />

                  <br />

                  </span>

				 <div style="clear:both"></div>

                  <span style="">

		<hr style="margin: 0;border-bottom:0.2px solid #000000;">		

   <span style="display:block;text-align:left;padding-left:5px;float:left;"> Reg No :'.$student_info['reg_no'].'</span><hr style="margin: 0;border-bottom:0.2px solid #000000;">

                    <span style="display:block;text-align:left;padding-left:5px;float:left;"> Name:'.$student_info['student_name'].'</span><hr style="margin: 0;border-bottom:0.2px solid #000000;float:left;">

                    <span style="display:block;text-align:left;padding-left:5px;float:left;"> Father Name:'.$student_info['f_name'].'</span><hr style="margin: 0;border-bottom:0.2px solid #000000;">

                    <span style="width:50%;float:left;padding-left:10px;display:block"> Grade:'. $student_info['class_name'].'</span>

                    <span style="width:50%;float:left;padding-left:10px;display:block;"> &nbsp;&nbsp;&nbsp;Fee Month:'.$student_info['fee_month'].'</span><hr style="margin: 0;border-bottom:0.2px solid #000000;clear:both;">

                    <span style=" width:50%;float:left;padding-right:10px;display:block"> Issue Date:'. $student_info['issue_date'].'</span>

                    <span style="width:50%;float:left;padding-left:10px;display:block"> &nbsp;&nbsp;&nbsp;Due Date: '.$student_info['due_date'].'</span><hr style="margin: 0;border-bottom:0.2px solid #000000;">

				  </span>';

	

	 $content .= '<table cellspacing="0"   style="margin:4px;border:1px solid #000;width:95%;">

                      <tr style="border-bottom:1px solid #000;padding:5px;">

                        <th style="padding:5px;border-bottom:1px solid #000;width:31%;">Particulars</th>

                        <th style="padding:5px;border-bottom:1px solid #000;width:31%;">Amount</th>

						<th style="padding:5px;border-bottom:1px solid #000;width:30%;">Discount</th>

                      </tr>';

					 

		$total = 0;

	 //$total = $total-$fee_info["discount"];

	 foreach($student_info['student_fee'] as $fee_info){ 

		 

	  $total = $total + $fee_info['amount'];

	   

	 

                      $content .= '<tr style="border-bottom:1px solid #000;">

                        <td style="border-bottom:1px solid #000;padding:5px;">'.$fee_info["fee_name"].' ('.$fee_info["fee_month"].')</td>

                        <td style="border-bottom:1px solid #000;padding:5px;">'.$fee_info["amount"].'/-</td>';

					 if($fee_info["fee_name"] == "Installment"){

						 $content .= '<td style="border-bottom:1px solid #000;padding:5px;">'.$fee_info["discount"].'</td>';

						}else{

						 $content .= '<td style="border-bottom:1px solid #000;padding:5px;"></td>';

						}

                      $content .= '</tr>';

		 			if($fee_info["fee_name"] == "Installment"){

					$total = ($total-$fee_info["discount"]);

					  }

					 } 

					  foreach($student_info['fee_fine'] as  $value) { 

                      $total = ($total + $value['fine_amount']);

                      

                     

                       $content .= '<tr><td style="border-bottom:1px solid #000;padding:5px;">Fine('.$value['fee_month'].')</td><td style="border-bottom:1px solid #000;padding:5px;">'.$value['fine_amount'].'</td><td style="border-bottom:1px solid #000;padding:5px;"></td></tr>';



                  

                    } 



                         $content .= '<tr style="border-bottom:1px solid #000;padding:5px;"><td style="padding:5px;">Total Payable</td>

                        <td style="padding:5px;">'.$total.'/-</td>

						<td ></td>

                      </tr>

                    </table>';

	 

				  $content .= '<br><br><span style="text-align:center;padding-left:10px;"> <strong>Note: </strong>After Due Date Rs. 10/day fine will be charged</span>

				</div>

              </div>

			  </td>

			  </tr></table>

			  </page>

			  ';

				 

} 

	  $buffer = ($content); 

	 $currentdate = date("d-m-Y");

	  //print_r($this->session->userdata);

	 //exit;

		ob_start();	

	require_once(APPPATH.'libraries/html2pdf/html2pdf.class.php');

    

	$html2pdf = new HTML2PDF('L', 'A4', 'en');

	//$html2pdf->setModeDebug();

	$html2pdf->setTestTdInOnePage(false);

	$html2pdf->pdf->SetDisplayMode('fullpage');

	$html2pdf->writeHTML($buffer, isset($_GET['vuehtml']));

		

	// build new name and commit

	$campus_id = $this->session->userdata('member_campusid');

	$user_id = $this->session->userdata('member_userid');



	

	$filename= 'fee-'.$currentdate.'.pdf';

	$html2pdf->Output($filename, 'F');

	Header("Content-type: application/pdf"); 

    Header("Content-Disposition: attachment; filename=$filename"); 

   //readfile("$filename");	

   	$this->db->where('created_date', date("Y-m-d"));

	$chalandocumentinfo = $this->db->get('chalan_document')->row();

	//print_r($chalandocumentinfo);

	if(!isset($chalandocumentinfo)){

   $data = array(

    'name' => $filename,

	'created_date' => date("Y-m-d"),

	'campus_id' => $campus_id,

	'created_by' => $user_id

   );

   $this->db->insert('chalan_document', $data);

  }

   

echo "<div style='padding:20px;color:green;'>PDF generated Successfully <a target='_blank' href='".base_url($filename)."'>Click Here to download</a></div>";	

		

	}



function data(){

		

		$response = new stdClass;

		$response->draw = $this->input->post('draw');



		$search = $this->input->post('search');

		$campus_id = $this->session->userdata('member_campusid');

		$session_id = $this->session->userdata('member_sessionid');

		$keyword = '';

		if($search) $keyword = $search['value']; 

	



			$result = $this->db->query('SELECT t1.class_id,t2.student_id, t2.campus_id,t2.reg_no,t2.first_name,t2.last_name,t2.parent_id FROM student_class t1, students t2 WHERE t1.student_id = t2.student_id and t1.status=1 and t2.campus_id='.$campus_id.' order by parent_id asc')->result(); 





		//print_r($q);

		$response->recordsTotal = count((array)$result);

		$response->student_data = array();

		

	foreach($result as $row)

		{

				

		$where = "student_id='".$row->student_id."' AND status='unpaid'";

		$this->db->where($where);

		$this->db->order_by("issue_date", "desc");

		$chalan_info = $this->db->get('fee_chalan')->row();

		//print_r($chalan_info);

		//exit;



	 $unpaind_total = $this->db->query("SELECT sum(fee.amount)- sum(fee.discount) as total FROM fee_chalan fee where student_id=".$row->student_id." and status='unpaid'")->row();

	 

		if($unpaind_total->total){



		$this->db->where('class_id',  $row->class_id);

		$classesinfo = $this->db->get('classes')->row();

		

		$this->db->where('parent_id',  $row->parent_id);

		$parentinfo = $this->db->get('parents')->row();



		$this->db->where('campus_id',  $row->campus_id);

		$campusinfo = $this->db->get('campus')->row();



		if($campusinfo->campus_name){

		$campus_name = $campusinfo->campus_name;

		}else{

		$campus_name = '';

		}

		if($campusinfo->location){

		$location = $campusinfo->location;

		}else{

		$location = '';

		}



		if($campusinfo->bank_name){

		$bank_name = $campusinfo->bank_name;

		}else{

		$bank_name = '';

		}



		if($campusinfo->bank_address){

		$bank_address = $campusinfo->bank_address;

		}else{

		$bank_address = '';

		}



		if($campusinfo->bank_code){

		$bank_code = $campusinfo->bank_code;

		}else{

		$bank_code = '';

		}



		if($campusinfo->bank_acc){

		$bank_acc = $campusinfo->bank_acc;

		}else{

		$bank_acc = '';

		}



		if($campusinfo->chalan_h_msg){

		$chalan_h_msg = $campusinfo->chalan_h_msg;

		}else{

		$chalan_h_msg = '';

		}



		if($campusinfo->chalan_f_msg){

		$chalan_f_msg = $campusinfo->chalan_f_msg;

		}else{

		$chalan_f_msg = '';

		}



		$where = "student_id='".$row->student_id."' AND status='unpaid'";

		$this->db->where($where);

		$fee_chalan = $this->db->get('fee_chalan')->result();

	    //exit;

	    $student_fee = array();

		foreach($fee_chalan as $chalanvalue){

		

		$this->db->where('fee_type_id', $chalanvalue->fee_type_id);

		$fee_type_info = $this->db->get('fee_type')->row();

		

		$student_fee[] = array(

			'id' => $chalanvalue->chalan_id,

			'amount' => $chalanvalue->amount,

			'status' => $chalanvalue->status,

			'discount' => $chalanvalue->discount,

			'paiddate' => $chalanvalue->paid_date,

			'fee_month' => $chalanvalue->fee_month,

			'fee_name' => $fee_type_info->fee_type_name	

		   );	

		   			   

		}



		$where = "student_id='".$row->student_id."' AND status='unpaid'";

		$this->db->where($where);

		$fine_info = $this->db->get('fine_detail')->result();

		$fee_fine = array();

		foreach ($fine_info as  $value) {

			$fee_fine[] = array(

				'fine_amount' => $value->amount,

				'fee_month' => $value->fee_month

			);

		}



		if(isset($parentinfo->father_contact)){

		$father_contact = $parentinfo->father_contact;

		}else{

		$father_contact = '';

		}

		

		if(isset($parentinfo->mother_contact)){

		$mother_contact = $parentinfo->mother_contact;

		}else{

		$mother_contact = '';

		}

		

		$issue_date = date_create_from_format('Y-m-d', $chalan_info->issue_date);

		$issue_date = date_format($issue_date, 'j-M-Y');

		

		$due_date = date_create_from_format('Y-m-d', $chalan_info->due_date);

		$due_date = date_format($due_date, 'j-M-Y');

		

		$fee_month = date_create_from_format('m/Y', $chalan_info->fee_month);

		$fee_month = date_format($fee_month, 'M-Y'); 

				

		$student_data[] = array(	  

		'campus_name' => $campus_name,

		'location' => $location,	

		'bank_name' => $bank_name,	

		'bank_address' => $bank_address,	

		'bank_code' => $bank_code,	

		'bank_acc' => $bank_acc,

		'chalan_h_msg' => $chalan_h_msg,

		'chalan_f_msg' => $chalan_f_msg,	

		'student_id' => $row->student_id,

	    'reg_no' => $row->reg_no,

		'student_name' => $row->first_name." ".$row->last_name,

		'f_name' => $parentinfo->f_name,

		'father_contact' => $father_contact,

		'mother_contact' => $mother_contact,

		'class_name' => $classesinfo->class_name,

		'fee_month' => $fee_month,

		'issue_date' => $issue_date,

		'due_date' => $due_date,

		'student_fee'=> $student_fee,

		'fee_fine' =>$fee_fine

		);





		   			   

		}

		

	

   }

		

   return $student_data;

		

}



}

