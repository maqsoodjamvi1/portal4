<?php
namespace App\Controllers\Admin;



/**

 * Lesson Plan Manage

 *

 * @author		Maqsood Ahmed

 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions

 * @email		maqsoodjamvi@gmail.com

 * @filesource

 */





class Lesson_plan extends MY_Controller {



	function __construct(){

		parent::__construct();

		check_permission('admin-lesson-plan');

	}



	/**

	 * Index Page for this controller.

	 */

	public function index()

	{

		$this->load->view('lesson_plan', $this->template_data);

	}



	function data(){

		

		$response = new stdClass;

		$response->draw = $this->input->post('draw');



		$search = $this->input->post('search');

		$keyword = '';

		if($search) $keyword = $search['value'];

		

		$this->db->select('count(A.question_id) as ccount', FALSE);

		$this->db->from('question_bank_gk A');

		if($keyword){

			$this->db->where('(A.short_title=' . $this->db->escape($keyword) .  ')');

		}

		$q = $this->db->get()->row();

		$response->recordsTotal = $q->ccount;





		$this->db->select('A.*');

		$this->db->from('question_bank_gk A');

		if($keyword){

			$this->db->where('(A.short_title=' . $this->db->escape($keyword) .  ')');

		}

		$this->db->order_by('A.question_id', 'desc');

		$this->db->limit($this->input->post('length'), $this->input->post('start'));

		$results = $this->db->get()->result();



		$response->recordsFiltered = $response->recordsTotal;

		

		

		$response->data = array();

		foreach($results as $row){

		

		$this->db->where('class_id', $row->class_id);

		$class_info = $this->db->get('classes')->row();		

		//print_r($row);

		

			$data = array();

			$data['id'] = $row->question_id;

			$data['class_name'] = $class_info->class_name;

			$data['subject_id'] = $row->subject_id;

			$data['question_eng'] = $row->q_text_eng;

			$data['question_ur'] = $row->q_text_urdu;

			$response->data[] = $data;

		}



		$this->output->set_output(json_encode($response));

	}



	function add(){

		check_permission('admin-add-lesson-plan');

		

		$topic_skills_id = $_GET['topic_skill_id'];

		if(isset($topic_skills_id)){

		$this->db->where('topic_skills_id', $topic_skills_id);

		$topic_skills_info = $this->db->get('topic_skills')->result();

		$this->template_data['topic_skills_info'] = $topic_skills_info;



		$this->db->where('sub_cat_topic_id', $topic_skills_info[0]->sub_cat_topic_id);

		$topicinfo = $this->db->get('sub_cat_topic')->result();

		$this->template_data['topicinfo'] = $topicinfo;



		$this->db->where('sub_cat_id', $topicinfo[0]->sub_cat_id);

		$sub_category_info = $this->db->get('sub_category')->result();

		$this->template_data['sub_category_info'] = $sub_category_info;



		$this->db->where('cs_id', $sub_category_info[0]->class_sub_id);

		$classsubjectinfo = $this->db->get('class_subjects')->result();	

		$this->template_data['classsubjectinfo'] = $classsubjectinfo;

	

		$this->db->where('sid', $classsubjectinfo[0]->subject_id);

		$subjectinfo = $this->db->get('allsubject')->result();

		$this->template_data['subjectinfo'] = $subjectinfo;



		$this->db->where('class_id', $classsubjectinfo[0]->class_id);

		$classesinfo = $this->db->get('classes')->result();

		$this->template_data['classesinfo'] = $classesinfo;

	

		}else{

		$classesinfo = $this->db->get('classes')->result();

		$this->template_data['classesinfo'] = $classesinfo;

	   }

		$quiz_type_info = $this->db->get('content_type')->result();

		$this->template_data['quiz_type_info'] = $quiz_type_info;



		$this->load->view('lesson_plan_edit', $this->template_data);

	}



	function edit(){

		check_permission('admin-edit-lesson-plan');

		$id = intval($this->input->get('id'));



		$this->db->where('did', $id);

		$info = $this->db->get('classdairy')->row();

		$this->template_data['info'] = $info;



		$classesinfo = $this->db->get('classes')->result();

		$this->template_data['classesinfo'] = $classesinfo;



		$subjectinfo = $this->db->get('allsubject')->result();

		$this->template_data['subjectinfo'] = $subjectinfo;



		$this->load->view('lesson_plan_edit', $this->template_data);

	}





	function save(){

	

	  $id = intval($this->input->post('id'));    

	  header('Content-Type: application/json');



	  $config['upload_path']   = './lessonplan/';

	  $config['allowed_types'] ="gif|jpg|jpeg|png|iso|dmg|zip|rar|doc|docx|xls|xlsx|ppt|pptx|csv|ods|odt|odp|pdf|rtf|sxc|sxi|txt|exe|avi|mpeg|mp3|mp4|3gp";  

	  $config['max_size']   = 1024;

	  $this->load->library('upload', $config);

	

		{

			if($id === 0){

				check_permission('admin-add-lesson-plan');

				$this->db->trans_begin();

				$questioncount = $this->input->post('questioncount');

				for($i=0; $i < count($questioncount); $i++){



				 $this->upload->initialize($config);

	 			 $this->upload->do_upload('document_url'.$i);  // File Name

	 			 $worksheet = $this->upload->data(); 

	  			 $worksheet_name = $worksheet['file_name'];	

				

				$data = array(

					'topic_skills_id' => trim($this->input->post('topic_skill_id')),

					'content_type_id' => trim($this->input->post('content_type_id')),

					'document_url' => trim($worksheet_name),

					'created_date' => trim(date('Y-m-d')),

					

				);

				

				$this->db->insert('skill_document', $data);

				$new_question_id = $this->db->insert_id();



				$data2 = array(

						'class_id' => trim($this->input->post('class_id')),

						's_id' => trim($this->input->post('subject_id')),

						'sub_cat_id' => trim($this->input->post('cat_id')),

						'sub_cat_topic_id' => trim($this->input->post('topic_id')),

						'topic_skills_id' => trim($this->input->post('topic_skill_id')),

						'content_type_id' => trim($this->input->post('content_type_id')),

						'content_id' => trim($new_question_id),

						'diff_level' => 0,

						'temp_id' => 0,

						'quiz_Learning' => 0



						);

				$this->db->insert('quiz_indexing', $data2);	



				}

				

				// set user roles

				$rolesarr = $this->input->post('roles');

				$this->db->trans_complete();

				$this->output->set_output(json_encode(array('success' => TRUE, 'msg' => 'Add Learning Question Success')));

			

			}else{

				check_permission('admin-edit-lesson-plan');

				$this->db->trans_begin();

				$data = array(

					'date1' => trim($this->input->post('date1')),

					'class_id' => trim($this->input->post('class_id')),

					'subject_id' => trim($this->input->post('subject_id')),

					'short_title' => trim($this->input->post('short_title')),

					'detail' => trim($this->input->post('detail')),

					'type1' => trim($this->input->post('type1'))

				);

				$this->db->where('did', $id);

				$this->db->update('classdairy', $data);

				// User Roles

				$rolesarr = $this->input->post('roles');



				$this->db->trans_complete();

				json_response(array('success' => TRUE, 'msg' => 'Edit Class Dairy Success'));

			}



		}

	}



	function delete(){

		check_permission('admin-del-lesson-plan');

		$id = intval($this->input->get('id'));

		$this->db->trans_begin();

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

