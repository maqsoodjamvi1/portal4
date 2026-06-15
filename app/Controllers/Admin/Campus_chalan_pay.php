<?php
namespace App\Controllers\Admin;



/**
 * Campus Chalan Pay Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Campus_chalan_pay extends MY_Controller {
	function __construct(){
		parent::__construct();
		check_permission('admin-campus-chalan-pay');
	}

	/**
	 * Index Page for this controller.
	 */

	public function index()
	{
	  $this->load->view('campus_chalan_pay', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		
		$this->db->select('count(A.chalan_id) as ccount', FALSE);
		$this->db->from('fee_chalan A');
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		$this->db->select('A.*');
		$this->db->from('fee_chalan A');
		$this->db->order_by('A.chalan_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();
		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
		$this->db->where('fee_type_id', $row->fee_type_id);
		$fee_type_info = $this->db->get('fee_type')->row();

		$this->db->where('student_id',  $row->student_id);
		$student_info = $this->db->get('students')->row();

			$data = array();
			$data['id'] = $row->chalan_id;
			$data['student_name'] = $student_info->first_name." ".$student_info->last_name;
			$data['due_date'] = $row->due_date;
			$data['issue_date'] = $row->issue_date;
			$data['fee_month'] = $row->fee_month;
			$data['amount'] = $row->amount;
			$data['status'] = $row->status;
			$data['discount'] = $row->discount;
			$data['paiddate'] = $row->paid_date;
			$data['fee_name'] = $fee_type_info->fee_type_name;
						
			$response->data[] = $data;

		}

		return $response;
		//$this->output->set_output(json_encode($response));

	}

	function add(){
		check_permission('admin-add-campus-chalan-pay');
		$fee_type_info = $this->db->get('fee_type')->result();
		$this->template_data['fee_type_info'] = $fee_type_info;

		$this->load->view('campus_chalan_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-campus-chalan-pay');
		$chalan_type_id = intval($this->input->get('id'));
		
		$this->db->where('chalan_type_id', $chalan_type_id);
		$info = $this->db->get('chalan_type')->row();
		$this->template_data['info'] = $info;
		
		$fee_type_info = $this->db->get('fee_type')->result();
		$this->template_data['fee_type_info'] = $fee_type_info;
		$this->load->view('campus_chalan_edit', $this->template_data);
	}

	function get_campus_list(){
		$campus_id = $this->input->post('campus_id');
		$payBase   = base_url('admin/campus_chalan_pay');
		$chalanBase = base_url('admin/campus_chalan');
		
		$schoolinfo = getSchoolInfo();
		$this->db->where('campus_id', $campus_id);
		$campus_info = $this->db->get('campus')->row();
		
		$feeList ='';
		
	$campuslistinfo = $this->db->query("SELECT * from campus WHERE campus_id=".$campus_id)->result();

	$unpaidsuminfo = $this->db->query("SELECT sum(bill_amount) as feeTotal from campus_chalan WHERE bill_status='unpaid' AND campus_id=".$campus_id)->row();

	$paidsuminfo = $this->db->query("SELECT sum(bill_amount) as feeTotal from campus_chalan WHERE bill_status='paid' AND paid_date='".date('Y-m-d')."' AND campus_id=".$campus_id)->row();

		$totalpaidwithfine = 0;
		$totalUnpaidfee = 0;
		$unpaidfeesum = 0;
		$unpaidfinesum = 0;
		$paidfeesum = 0;
		$paidfinesum = 0;
		$discountedsum = 0;
		
		if($unpaidsuminfo){
			$unpaidfeesum = $unpaidsuminfo->feeTotal;
		}

		if($paidsuminfo){
			$paidfeesum = $paidsuminfo->feeTotal;
		}

		$totalpaidwithfine = $paidfeesum;
		$totalFeeAmounts = $paidfeesum + $unpaidfeesum;
		$totalUnpaidfee = $unpaidfeesum;
	
	if(isset($campuslistinfo)){

		$feeList .= "<a style='margin: 30px 0;margin-bottom: 10px;float:right;'  class='btn btn-primary float-end' id='payAllFee' data-parentID=".$campus_id."  href='#'>Pay All</a>";

		$feeList .= "<script>
		$('#payAllFee').click(function(){
			 if(confirm('Are you sure you want to update this?')){
				var parentID = $(this).data('parentid');
				var datePaid = $('#datePaid').val();
				
		         $.ajax({
		            url: '{$payBase}/payFeeAll',
		            type: 'POST',
		            data:{parent_id: parentID,datePaid:datePaid},
		            success:function(res){
		             $('#feetypeinfo').html('All Fee Paid Successfully');
		             toastr.success('Updated Successfully');
		         	}
		         });

		        $('#updatediscount').modal('hide');
		    }else{
		    	return false;
		    }
			});	
		</script>";
	

		$feeList .= '<table class="table table-bordered" style="width:100%;margin-bottom:20px;"><tr style="background: #367fa9;color: #fff;font-weight: normal;"><th>Plan</th><th>Amount</th><th colspan="1">Operation</th></tr>';
		$total=0;
		$totalfine=0;
		$subtotal =0;
		$fine=0;
		$i=1;

	foreach ($campuslistinfo as $key => $campus_info) { 
		
		$where = "campus_id='".$campus_info->campus_id."' AND bill_status='unpaid'";
		$this->db->where($where);
		$campus_chalan = $this->db->get('campus_chalan')->result();	

		foreach($campus_chalan as $row){
		
		$this->db->where('plan_id', $row->plan_id);
		$plan_info = $this->db->get('bill_plans')->row();
		$planLabel = $plan_info ? $plan_info->plan_name : 'Campus Bill';
		
		$total = $total + $row->bill_amount;
			
		$nmonth = date("d M Y", strtotime($row->due_date));
		$profile_photo = '';
					
		$feeList .= "<tr id='feepaid'><th class='leftdate'>".$campus_info->campus_name."<br> ".$planLabel."<br>Due Date: ".$nmonth."</th><th class='rightdata'><input type='hidden' id='campus_id".$i."' name='student_id' value='".$campus_info->campus_id."' />".($row->bill_amount)."/-</th>";
		
		$feeList .= '<td><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#payfee" data-feeamount="'.($row->bill_amount).'" data-whatever="'.$row->bill_id.'" data-fine="" data-campus_id="'.$campus_info->campus_id.'">Pay</button> <a class="btn btn-primary" href="'.$chalanBase.'?id='.$campus_info->campus_id.'">Generate Chalan</a></td>';

		
		$feeList .= '</tr>';
		$fine=0;
		$i++;
	}
		
$Year = date('Y');
$month = date('m');

$paidfee = $this->db->query("SELECT * from campus_chalan where campus_id=".$campus_info->campus_id." AND bill_status = 'paid'")->result();
			
	foreach ($paidfee as $key => $value) {

			$pmonth = date("d M Y", strtotime($value->paid_date));

			$profile_photo = '';
		
			
			$this->db->where('plan_id', $value->plan_id);
			$plan_info = $this->db->get('bill_plans')->row();
			$planLabel = $plan_info ? $plan_info->plan_name : 'Campus Bill';

			$feeList .= "<tr><th class='leftdate' style='text-transform: capitalize;'>".$campus_info->campus_name."<br>".$planLabel." ".$value->bill_status." At: ".$pmonth."</th><th class='rightdata'>".($value->bill_amount)."/-</th>";
		if($value->paid_date == date('Y-m-d')){
			$feeList .= '<td style="text-align:center;"><button type="button" class="btn btn-primary" data-bs-toggle="modal" id="unpayfee'.$value->bill_id.'" data-feeamount="'.($value->bill_amount).'" data-whatever="'.$value->chalan_id.'" data-fine="'.$fine.'" data-campus_id="'.$campus_info->campus_id.'">Make UnPaid</button></td>';
			$feeList .= "<script>
				$('#unpayfee".$value->bill_id."').click(function(){		
				    if(confirm('Are you sure you want to update this?')){
				        $.ajax({
				            url: '{$payBase}/updatePaidFee',
				            type: 'POST',
				            data:{challan_id:$value->chalan_id},
				            success:function(res){
				             toastr.success('Updated Successfully');
				             location.reload();
				         	}
				         });
				    }
				    else{
				        return false;
				    }
				});
				
			</script>";

		}else{
			$feeList .= "<th>".ucfirst($value->bill_status)."</th>";
		}
			$feeList .= "</tr>";
		}
	} 
		$feeList .= "<tr><td colspan='1' style='border:0 none !important; padding:10px 0px;'></td></tr>";	
		$feeList .= "<tr><td  colspan='1'  style='border:0 none !important; padding:10px 0px;'></td><th style='border:0 none !important; padding:10px 0px; border-bottom:1px solid #ccc !important;'>Total </th><th style='border:0 none !important; padding:10px 0px; border-bottom:1px solid #ccc !important;' class='rightdata'>".($totalFeeAmounts)."/-</th></tr>";
		$feeList .= "<tr><td  colspan='1'  style='border:0 none !important; padding:10px 0px;'></td><th style='border:0 none !important; padding:10px 0px; border-bottom:1px solid #ccc !important;'>Paid</th><th style='border:0 none !important; padding:10px 0px; border-bottom:1px solid #ccc !important;'  class='rightdata'>".($totalpaidwithfine)."/-</th></tr>";
		$feeList .= "<tr><td  colspan='1'  style='border:0 none !important; padding:10px 0px;'></td><th style='border:0 none !important; padding:10px 0px; border-bottom:1px solid #000 !important;'>Balance</th><th style='border:0 none !important; border-bottom:1px solid #000 !important; padding:10px 0px;' class='rightdata'>".($totalUnpaidfee)."/-</th></tr>

		</table>";
$feeList .= '<div class="modal fade"  id="payfee" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Pay Fee</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      	<label id="totalAmount">Total: </label>'.($totalFeeAmounts).'
      	&nbsp;&nbsp;&nbsp;<label>Paid: </label>'.($totalpaidwithfine).'<br>
      	<label>Discount: </label>'.($discountedsum).'&nbsp;&nbsp;&nbsp;
      	<label>Balance: </label>'.($totalUnpaidfee).'
        <form id="payFeeData">
        <input type="hidden" name="chalan_id" id="ChalanID">
        <input type="hidden" name="PaidDate" id="PaidDate">
        <input type="hidden" name="campus_id" id="campusID">
        <input type="hidden" name="fineamount" id="fineamount">
          <div class="form-group">
            <label for="recipient-name" class="col-form-label">Fee Amount:</label>
            <input type="text" class="form-control" name="fee_amount" id="feeAmount">
          </div>
          <div class="form-group">
            <label for="message-text" class="col-form-label">Paid Amount:</label>
           	<input type="text" id="PaidAmount"  class="form-control" name="paid_amount">
          </div>
         <div class="form-group">
            <label for="message-text" class="col-form-label">Balance:</label>
           	<input type="text" id="balance" readonly  class="form-control" value="0" name="balance">
          </div>
          
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" id="payFee" class="btn btn-primary">Submit</button>
      </div>
    </div>
  </div>
</div>';
$feeList .= " <script>
        $(document).on('change keyup blur', '#discountAmount', function() {
            var main = $('#feeAmount').val();
            var disc = $('#discountAmount').val();
            
            var discont = main - disc;
            $('#PaidAmount').val(discont);
        });
        $(document).on('change keyup blur', '#PaidAmount', function() {

        	var feeAmount = $('#feeAmount').val();
            var main = $('#PaidAmount').val();
            
            var paid = (Number(main));
            //alert(paid);
            var unpaid = feeAmount - paid ;
            $('#balance').val(unpaid);
            if(unpaid < 0){
            	$('#balance').css('background-color', 'red');
            	$('#balance').css('color', '#fff');
            	$('#payFee').prop('disabled', true);
            }else{
            	$('#balance').css('background-color', '#eee');
            	$('#balance').css('color', '#000');
            	$('#payFee').prop('disabled', false);
            }
        });
  </script><script>
	$('#payFee').click(function(){
 	var chalan_id = $( '#ChalanID' ).val();
 	var paid_date = $( '#PaidDate' ).val();
 	var campusid = $( '#campusID' ).val();
 	
 
 	var fee_amount = $( '#feeAmount' ).val();
 	var paid_amount = $( '#PaidAmount' ).val();
 	
	if (confirm('Are you sure you want to pay?')) {	
 	      $.ajax({
            url: '{$payBase}/pay_fee',
            type: 'POST',
            data:{chalan_id: chalan_id,campusid:campusid,paid_date:paid_date,fee_amount:fee_amount,paid_amount:paid_amount}, 
    success:function(res){
 		$('#payFee').html('Paid Successfully'); 
 		$('#payFee').prop('disabled', true);  
        var campus_id = $( '#campusID' ).val();
         $.ajax({
            url: '{$payBase}/get_campus_list',
            type: 'POST',
            data:{campus_id: campus_id},
            success:function(res){
             if(res){
                $('#feetypeinfo').html(res);
             }else{
               $('#feetypeinfo').html('Record Not Found'); 
             }
         	}
         });

        $('#payfee').modal('hide'); 

		}
      });
	}
 });
			
$('#payfee').on('hidden.bs.modal', function () { 
    //location.reload();
    $('#payFeeData')[0].reset();
    $('#feeFine').hide();
    $('#payFee').html('Submit'); 
 	$('#payFee').prop('disabled', false);
 	$('#balance').css('background-color', '#eee');
    $('#balance').css('color', '#000');  

});

$('#payfee').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var recipient = button.data('whatever') // Extract info from data-* attributes
  var feeAmount = button.data('feeamount');
  var paiddate = $('#datePaid').val();
  $('#PaidDate').val(paiddate);
  var campus_id = button.data('campus_id');
  
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  var modal = $(this)
  modal.find('.modal-title').text('Pay Fee ')
  modal.find('#feeAmount').val(feeAmount)
  modal.find('#ChalanID').val(recipient)
  modal.find('#campusID').val(campus_id)
  modal.find('#PaidAmount').val(feeAmount)
});

</script><style>.modal-dialog {
          width: 360px;
          // height:700px !important;
        }
.modal-content {
    /* 80% of window height */
    height: 60%;
    background-color:#BBD6EC;
}        
.modal-header {
    background-color: #337AB7;
    padding:16px 16px;
    color:#FFF;
    border-bottom:2px dashed #337AB7;
 } </style>";		
		
	$this->output->set_output(($feeList));	
}
		 	
}	

function pay_fee(){
	$bill_id = $this->input->post('chalan_id');
	$campusid = $this->input->post('campusid');
	$paid_date = DateTime::createFromFormat('d/m/Y',$this->input->post('paid_date'));
	$paid_date = $paid_date->format('Y-m-d');

	$fee_amount = $this->input->post('fee_amount');
	$fine = $this->input->post('fine');
	$fineamount = $this->input->post('fineamount');
		
	$paid_amount = $this->input->post('paid_amount');
	$discountAmount = $this->input->post('discountAmount');
	$user_id = $this->session->userdata['member_userid'];
	$date = date('Y-m-d');
	
	$this->db->trans_begin();

	$this->db->where('bill_id', $bill_id);
	$chalaninfo = $this->db->get('campus_chalan')->row();
	/* ======================= Add Fine ===============*/
	if($paid_amount == $fee_amount){	
		/* ========== Fee Amount and paid amount is Equal ==========*/
		$data = array(
		'paid_date' => $paid_date,
		'bill_status' => 'paid',
		'user_id' => $user_id,
		'updated_date' => $date	
		);
		$this->db->where('bill_id', $bill_id);
		$this->db->update('campus_chalan', $data);
	}else{
	
	/* ====== Full paid Partial Discount ========*/

		$updatedamount = ($fee_amount - $paid_amount);	
		$paidDiscounted = $paid_amount;
		
	if($updatedamount == 0){
	
		$dbpayable =  $paid_amount;
		
		$data = array(
				'issue_date' => $chalaninfo->issue_date,
				'due_date' => $chalaninfo->due_date,
				'bill_amount' => $dbpayable,
				'bill_status' => 'paid',
				'paid_date' => $paid_date,
				'user_id' => $user_id,
				'updated_date' => $date		
			);
		$this->db->where('bill_id', $bill_id);
		$this->db->update('campus_chalan', $data);
		}else{
		/* ====== Partial paid Partial Discount ========*/	
		$dbpayable2 =  ($chalaninfo->bill_amount - $paid_amount);

			$data = array(
				'issue_date' => $chalaninfo->issue_date,
				'due_date' => $chalaninfo->due_date,
				'bill_amount' => $dbpayable2,
				'user_id' => $user_id,
				'updated_date' => $date	
			);
		
		//print_r($data); 
		//exit;
		$this->db->where('bill_id', $bill_id);
		$this->db->update('campus_chalan', $data);
		
		$data2 = array(
			'campus_id' => $chalaninfo->campus_id,
			'plan_id' => $chalaninfo->plan_id,
			'issue_date' => $chalaninfo->issue_date,
			'due_date' => $chalaninfo->due_date,
			'bill_amount' => $paid_amount,
			'bill_status' => 'paid',
			'bill_type_id' => $chalaninfo->bill_type_id,
			'paid_date' => $paid_date,
			'user_id' => $user_id,
			'created_date' => $date		
		);
		
		$this->db->insert('campus_chalan', $data2);
		
	}
		
	}
		$this->db->trans_complete();
		$this->output->set_output('Chalan Paid Successfully');			
}

function payFeeAll(){
	$user_id = $this->session->userdata['member_userid'];
	$date = date('Y-m-d H:i:s');
	
	$parent_id = $this->input->post('parent_id');
	//$datePaid = $this->input->post('datePaid');
	$paid_date = DateTime::createFromFormat('d/m/Y',$this->input->post('datePaid'));
	$paid_date = $paid_date->format('Y-m-d');

	$FeeChalanInfo = $this->db->query('SELECT * from campus_chalan where campus_id IN(SELECT campus_id from campus WHERE campus_id='.$parent_id.' AND status=1) AND status="unpaid"')->result();

	foreach ($FeeChalanInfo as $key => $value) {
	
		$data = array(
		'status' => 'paid',
		'paid_date' => $paid_date,
		'updated_date' => $date,
		'user_id' => $user_id
		);

	$this->db->where('chalan_id', $value->chalan_id);
	$this->db->update('fee_chalan', $data);

	//print_r($this->db->error());
	
	
	}
	
}

function updatePaidFee(){
	$data = array(
		'status' => 'unpaid'
	);
	$this->db->where('campus_id', $this->input->post('campus_id'));
	$this->db->where('paid_date', date('Y-m-d'));
	$this->db->update('campus_chalan', $data);
}
function get_campusinfo(){
		$termPost = $this->input->post('term');
		$search   = '';

		if (is_array($termPost)) {
			$search = trim((string) ($termPost['term'] ?? ''));
		} else {
			$search = trim((string) $termPost);
		}

		$builder = \Config\Database::connect()->table('campus');
		if ($search !== '') {
			$builder->like('campus_name', $search);
		}
		$campusinfo = $builder->orderBy('campus_name', 'ASC')->limit(25)->get()->getResultArray();

		$data = [];
		foreach ($campusinfo as $campus) {
			$data[] = [
				'id'   => $campus['campus_id'],
				'text' => $campus['campus_name'],
			];
		}

		json_response($data);
}
}

// end this file