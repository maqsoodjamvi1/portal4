<?php
namespace App\Controllers\Admin;


/**
 * MCQS Questions Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Question_text_mcqs extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-questions');
	}

	/**
	 * Index Page for this controller.
	*/

	public function index()
	{
		$data = $this->data();
		$this->template_data['data'] = $data;	
		$this->load->view('question_text_mcq', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');

		$search = $this->input->post('search');
		$keyword = '';
		$getid = $_GET['topic_id'];
		if($search) $keyword = $search['value'];
		$this->db->select('count(A.content_id) as ccount', FALSE);
		$this->db->from('contents A');

		if($getid){
			$this->db->where('(A.topic_id='.$this->db->escape($getid).')');
			$this->db->where('(A.template_id!=8)');
		}

		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		$this->db->select('A.*');
		$this->db->from('contents A');
		if($getid){
			$this->db->where('(A.topic_id=' . $this->db->escape($getid) .  ')');
			$this->db->where('(A.template_id!=8)');
		}
		$this->db->order_by('A.content_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;
		$response->data = array();
	
	foreach($results as $row){
		
		$this->db->where('sub_cat_topic_id', $row->topic_id);
		$sub_cat_topic_info = $this->db->get('esub_cat_topic')->row();	
		
		if($sub_cat_topic_info){
			$this->db->where('sub_cat_id', $sub_cat_topic_info->sub_cat_id);
			$sub_category_info = $this->db->get('ecategories')->row();		
		}else{
			$sub_category_info = '';
		}	

		if($sub_category_info){
			$this->db->where('sub_id', $sub_category_info->e_sub_id);
			$subject_info = $this->db->get('esubjects')->row();		
		}else{
			$subject_info = '';
		}

		
	$this->db->where('content_id', $row->content_id);
	$questionoptions_info = $this->db->get('question_options')->result();	
	
	$questionOptions = array();
	foreach ($questionoptions_info as  $question_option) {
		$correctAns = $question_option->is_correct;
		$ans = '';
		if($correctAns == 1){
			$ans = '(True)';
		}
		$questionOptions[] = $question_option->option_text." ".$ans;	
	}
	$response->data = array();
	$data[] = array(
		'id' => $row->content_id,
		'subject' => $subject_info->subject,
		'subject_category' => $sub_category_info->cat_title,
		'topic' => $sub_cat_topic_info->topic,
		'question_eng' => $row->question,
		'question_ur' => $row->question,
		'questionOptions' => $questionOptions
	);
	$response->data[] = $data;
	}

return $data;
	//$this->output->set_output(json_encode($response));
}

function add(){
	check_permission('admin-add-question');
	$topic_id = $_GET['topic_id'];

	$this->db->where('cat_type_id', 1);
	$templateinfo = $this->db->get('type_template')->result();
	$this->template_data['templateinfo'] = $templateinfo;

	$this->db->where('topic_id', $topic_id);
	$this->db->where('template_id !=', 8);
	$info = $this->db->get('contents')->result();
	$this->template_data['info'] = $info;
	//print_r($info);
	
	if(isset($topic_id)){
		
		$this->db->where('sub_cat_topic_id', $topic_id);
		$topicinfo = $this->db->get('esub_cat_topic')->result();
		$this->template_data['topicinfo'] = $topicinfo;

		$this->db->where('sub_cat_id', $topicinfo[0]->sub_cat_id);
		$sub_category_info = $this->db->get('ecategories')->result();
		$this->template_data['sub_category_info'] = $sub_category_info;

		$this->db->where('sub_id', $sub_category_info[0]->e_sub_id);
		$subjectinfo = $this->db->get('esubjects')->result();	
		$this->template_data['subjectinfo'] = $subjectinfo;

		

	}

	
	$this->load->view('question_text_mcq_edit', $this->template_data);

}


function loadQuestion(){
	$content_id = '';
	$contentImage = '';
	$videoUrl = '';
	$questionType = $this->input->post('questionType');
	$rowNum = $this->input->post('rowcount');
	
	if($this->input->post('content_id')){
		$content_id = $this->input->post('content_id');
	}

	$this->db->where('content_id', $content_id);
	$this->db->where('question_type', $questionType);
	$contentInfo = $this->db->get('contents')->row();

	if($questionType == 'text'){
		if($contentInfo){
			if($contentInfo->question){
				$contentInfo = $contentInfo->question;
			}
		}
		echo '<textarea rows="3" class="form-control editor" name="question_text'.$rowNum.'" placeholder="Question" id="question_text'.$rowNum.'" style="margin-bottom: 4px;">'.$contentInfo.'</textarea>';
	}

	if($questionType == 'image'){
		if($contentInfo){
			if($contentInfo->question_image){
				$contentImage = $contentInfo->question_image;
			}
		}
		echo '<input type="file" rows="3" class="form-control editor" name="question_image'.$rowNum.'" placeholder="Question" id="question_image'.$rowNum.'" style="margin-bottom: 4px;"><img src="worksheets/'.$contentImage.'">';
	}

	if($questionType == 'video'){
		if($contentInfo){	
			if($contentInfo->video_url){
				$videoUrl = $contentInfo->video_url;
				$contentInfo = $contentInfo->question;
			}	
		}
		echo '<textarea rows="3" class="form-control editor" name="question_text'.$rowNum.'" placeholder="Question" id="question_text'.$rowNum.'" style="margin-bottom: 4px;">'.$contentInfo.'</textarea><input type="url" rows="3" class="form-control editor" name="video_url'.$rowNum.'" placeholder="Video URL" value="'.$videoUrl.'" id="video_url'.$rowNum.'" style="margin-bottom: 4px;">';
	}
}

function save(){

	$id = intval($this->input->post('id'));
	check_permission('admin-add-question');
	$user_id = $this->session->userdata['member_userid'];
	$date = date('Y-m-d H:i:s');
	$quetionTxt = '';
	$videoUrl = '';
	$questionImgName = '';
	header('Content-Type: application/json');
	$config['upload_path']   = './worksheets/';
	$config['allowed_types'] ="gif|jpg|jpeg|png|iso|dmg|zip|rar|doc|docx|xls|xlsx|ppt|pptx|csv|ods|odt|odp|pdf|rtf|sxc|sxi|txt|exe|avi|mpeg|mp3|mp4|3gp";  
	$config['max_size']   = 1024;
	$this->load->library('upload', $config);
	
	$this->db->trans_begin();

	$optionscount = $this->input->post('optionscount');
	if($optionscount){
	for($i=0; $i < count($optionscount); $i++){
	
	$id = $this->input->post('id'.$i);
	
	if(($this->input->post('question_text'.$i))){
		$quetionTxt = $this->input->post('question_text'.$i);
	}

	$this->upload->initialize($config);
	$this->upload->do_upload('question_image'.$i);  // File Name
	$questionImg = $this->upload->data(); 
	
	if($questionImg['file_name']){
  	 	$questionImgName = $questionImg['file_name'];
	}else{
		$questionImgName = $this->input->post('question_image'.$i);
	}

	if(($this->input->post('video_url'.$i))){
		$videoUrl = $this->input->post('video_url'.$i);
	}
	
	if($id == 0){
		
		$data = array(
			'topic_id' => trim($this->input->post('topic_id')),
			'template_id' => trim($this->input->post('template_id')),
			'question_type' => trim($this->input->post('question_type'.$i)),
			'question' => trim($quetionTxt),
			'question_image' => trim($questionImgName),
			'video_url' => trim($videoUrl),
			'explanation' => trim($this->input->post('explanation_text'.$i)),
			'user_id' => $user_id,
			'created_date' => $date
		);

		$this->db->insert('contents', $data);
		$new_question_id = $this->db->insert_id();

		$data2 = array(
			'class_id' => trim($this->input->post('class_id')),
			'esubjects_sub_id' => trim($this->input->post('subject_id')),
			'ecategories_sub_cat_id' => trim($this->input->post('cat_id')),
			'esub_cat_topics_sub_cat_topic_id' => trim($this->input->post('topic_id')),
			'template_id' => trim($this->input->post('template_id')),
			'contents_content_id' => trim($new_question_id),
			'diff_level' => 0,
			'user_id' => $user_id,
			'created_date' => $date
		);

		$this->db->insert('content_indexing', $data2);	
		
		$is_correct = 0;
		$nCount = 0;
		$optionNums = 0;
		  $template_id = $this->input->post('template_id');   
        if($template_id == 1 || $template_id ==2){ 
          $optionNums = 8;
        }

        if($template_id == 3 || $template_id ==4){ 
          $optionNums = 7;
        }

        if($template_id == 5 || $template_id ==6){ 
          $optionNums = 6;
        }

        if($template_id == 7 || $template_id ==8 || $template_id ==9){ 
          $optionNums = 5;
        }

        if($template_id == 10 || $template_id ==11){ 
          $optionNums = 4;
        }

        if($template_id == 12 || $template_id ==13){ 
          $optionNums = 3;
        }


		for($j=0; $j<=$optionNums; $j++){	

			if($nCount == 0){
				$is_correct = 1;
			}else{
				$is_correct = 0;
			}

		$optionsdata = array(
			'content_id' => $new_question_id,
			'is_correct' => $is_correct,
			'option_text' => trim($this->input->post('option_text0'.$j)),
			'user_id' => $user_id,
			'created_date' => $date
		);
		
		$this->db->insert('question_options', $optionsdata);
		$new_user_id = $this->db->insert_id();
		
		$nCount++;
	}

	}else{
		
		$data = array(
			'topic_id' => trim($this->input->post('topic_id')),
			'template_id' => trim($this->input->post('template_id')),
			'question_type' => trim($this->input->post('question_type'.$i)),
			'question' => trim($quetionTxt),
			'question_image' => trim($questionImgName),
			'video_url' => trim($videoUrl),
			'explanation' => trim($this->input->post('explanation_text'.$i)),
			'user_id' => $user_id,
			'created_date' => $date
		);
		
		$this->db->where('content_id', $id);
		$this->db->update('contents', $data);
		
		$is_correct = 0;
		$nCount = 0;
		$optionNums = 0;
		
		$this->db->where('content_id', $id);
		$questionOtions = $this->db->get('question_options')->result();
		foreach ($questionOtions as $key => $value) {
			
			if($nCount == 0){
				$is_correct = 1;
			}else{
				$is_correct = 0;
			}

		$optionsdata = array(
			'content_id' => $id,
			'is_correct' => $is_correct,
			'option_text' => trim($this->input->post('option_text0'.$key)),
			'user_id' => $user_id,
			'created_date' => $date
		);

		$this->db->where('question_option_id', $value->question_option_id);
		$this->db->update('question_options', $optionsdata);
		$new_user_id = $this->db->insert_id();
		
		$nCount++;
	}
	}
		$this->db->trans_complete();
	}
	
	}
		$this->output->set_output(json_encode(array('success' => TRUE, 'msg' => 'Add Question Success')));
	}
	}
	// end this file