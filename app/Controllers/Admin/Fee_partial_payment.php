<?php
namespace App\Controllers\Admin;


/**
 * Fee Partial Payment Manage
 *
 * @author		Maqsood Jamvi
 * @copyright	Copyright (c) 2016~2099 timesoftsol.com
 * @email		maqsoodjamvi@gmail.com
 * @filesource
*/

class Fee_partial_payment extends MY_Controller {
	
	function __construct(){
		parent::__construct();
		check_permission('admin-users');
	}

	/**
	 * Index Page for this controller.
	 */

public function index()
{
	$this->load->view('fee_partial_payment', $this->template_data);
}

function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		// $this->session->set_userdata('search', $search);
		// $perpage = 10;
		$this->db->select('count(A.chalan_id) as ccount', FALSE);
		$this->db->from('fee_chalan A');
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;
		// $offset = $response->draw * $perpage;
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
		check_permission('admin-add-user');
		$fee_type_info = $this->db->get('fee_type')->result();
		$this->template_data['fee_type_info'] = $fee_type_info;
		$this->load->view('fee_partial_payment_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-user');
		$chalan_id = intval($this->input->get('chalan_id'));
		$this->db->where('chalan_id', $chalan_id);
		$info = $this->db->get('fee_chalan')->row();
		$this->template_data['info'] = $info;
		//$fee_type_info = $this->db->get('fee_type')->result();
		//$this->template_data['fee_type_info'] = $fee_type_info;
		$this->load->view('fee_partial_payment_edit', $this->template_data);
	}

	function save(){
		$chalandata = array();
		$id = $this->input->post('id');
		{
			{
			//	check_permission('admin-edit-user');
			$issue_date = DateTime::createFromFormat('d/m/Y',$this->input->post('issue_date'));
			$issuedate = $issue_date->format('Y-m-d');
			$due_date = DateTime::createFromFormat('d/m/Y',$this->input->post('due_date'));
			$duedate = $due_date->format('Y-m-d');
			$fee_month = $this->input->post('fee_month');
			$amount = $this->input->post('amount');
			$student_id = $this->input->post('student_id');
			$fee_type_id = $this->input->post('fee_type_id');
			$paid_amount = $this->input->post('paid_amount');
			$updatedamount = ($amount - $paid_amount);
			$discount = $this->input->post('discount');
			$this->db->trans_begin();
			
			$data = array(
				'issue_date' => $issuedate,
				'due_date' => $duedate,
				'fee_month' => $fee_month,
				'amount' => $updatedamount,
				'discount' => $discount,
			);
		
		$data2 = array(
			'student_id' => $student_id,
			'issue_date' => $issuedate,
			'due_date' => $duedate,
			'fee_month' => $fee_month,
			'amount' => $paid_amount,
			'discount' => 0,
			'status' => 'paid',
			'fee_type_id' => $fee_type_id,
			'paid_date' => date('Y-m-d')	
		);
	
		$this->db->where('chalan_id', $id);
		$this->db->update('fee_chalan', $data);
		
		$this->db->insert('fee_chalan', $data2);

		$new_chalan_id = $this->db->insert_id();
		$this->db->trans_complete();
		
		json_response(array('success' => TRUE, 'msg' => 'Edit Chalan Success'));
	}
	}

}



	function delete(){

		check_permission('admin-del-user');

		$id = intval($this->input->get('id'));



		$this->db->trans_begin();



		// delete user perms

		$this->db->where('userID', $id);

		$this->db->delete('user_perms');



		// delete user roles

		$this->db->where('userID', $id);

		$this->db->delete('user_roles');





		// delete user detail

		// $this->db->where('user_id', $id);

		// $this->db->delete('user_profile');



		// delete user

		$this->db->where('id', $id);

		$this->db->delete('classes');



		$this->db->trans_complete();

		json_response(array('success' => TRUE, 'msg' => 'Delete Classes Success'));

	}







	function set_perms(){

		if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST'){

			foreach ($_POST as $k => $v)

			{

				if (substr($k,0,5) == "perm_")

				{

					$permID = str_replace("perm_","",$k);

					if ($v == 'x')

					{

						$strSQL = "DELETE FROM `user_perms` WHERE `userID` = ? AND `permID` = ?";

						$this->db->query($strSQL,array($_POST['user_id'],floatval($permID)));

					} else {

						$strSQL = "REPLACE INTO `user_perms` SET `userID` = ?, `permID` = ?, `value` = ?";

						$this->db->query($strSQL,array($_POST['user_id'],floatval($permID),$v));



					}

				}

			}

			cxp_update_cache();

			json_response(array('success' => TRUE, 'msg' => 'change user permission success'));

		}else{

			$user_id = intval($this->input->get('user_id'));

			$this->db->where('id', $user_id);

			$info = $this->db->get('classes')->row();

			$this->template_data['info'] = $info;

			$this->template_data['user_id'] = $user_id;



			$this->load->view('set_perms', $this->template_data);

		}



	}



	function perm_data(){

		$permissions = permissions_list();

	  $perm_parr = array();

	  foreach($permissions as $row){

		$perm_parr[$row->parent_id][] = $row;

	  }



	  $user_id = intval($this->input->post('user_id'));

	  $this->load->library('Member_acl');

			$my_acl=new Member_acl($user_id);

			$this->template_data['my_acl'] = $my_acl;

			$rPerms = $my_acl->getPermArr();

			$this->template_data['rPerms'] = $rPerms;

	  $this->output->set_output('[' . $this->loop_parent($perm_parr, 0, 0, 0, '', $rPerms) . ']');

	}



	function loop_parent($perm_parr, $parent_id, $curloop, $curid, $html, $rPerms){

		if(isset($perm_parr[$parent_id]) && count($perm_parr[$parent_id])>0){



			  foreach($perm_parr[$parent_id] as $row){

				$permKey = $row->permKey;

				$selhtml = '';

				$selhtml .= "<select name=\"perm_" . $row->id . "\">";

				$selhtml .= "<option value=\"1\"";

				if (isset($rPerms[$permKey]) && ($rPerms[$permKey]['value'] === '1' || $rPerms[$permKey]['value'] === true) && $rPerms[$permKey]['inheritted'] != true) { $selhtml .= " selected=\"selected\""; }

				$selhtml .= ">Allow</option>";

				$selhtml .= "<option value=\"0\"";

				if(isset($rPerms[$permKey])){if ($rPerms[$permKey]['value'] === false && $rPerms[$permKey]['inheritted'] != true) { $selhtml .= " selected=\"selected\""; }}

				$selhtml .= ">Deny</option>";

				$selhtml .= "<option value=\"x\"";

				$iVal = '';

				if(isset($rPerms[$permKey])){

					if ($rPerms[$permKey]['inheritted'] == true || !array_key_exists($permKey,$rPerms))

					{

						$selhtml .= " selected=\"selected\"";

						if ($rPerms[$permKey]['value'] === true )

						{

							$iVal = '(Allow)';

						} else {

							$iVal = '(Deny)';

						}

					}

				}else{

					$selhtml .= " selected=\"selected\"";

					$iVal = '(Deny)';

				}

				$selhtml .= ">Inherit $iVal</option>";

                $selhtml .= "</select>";



				  if(isset($perm_parr[$row->id]) && count($perm_parr[$row->id])>0){

					$html .= "{id:" . $row->id . ",name:'" . $row->permName . "', select:'" . $selhtml . "', children:[";

					$html = $this->loop_parent($perm_parr, $row->id, $curloop + 1, $curid, $html, $rPerms) . ']},';



				  }else{

					  $html .= "{id:" . $row->id . ",name:'" . $row->permName . "', select:'" . $selhtml . "'},";

				  }

			  }

		}else{

			// $html .= ']},';

		}

		return $html;

	}

}

// end this file

