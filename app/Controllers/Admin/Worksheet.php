<?php
namespace App\Controllers\Admin;


/**
 * Worksheet Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */

class Worksheet extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-worksheet');
	}

	/**
	 * Index Page for this controller.
	*/

	public function index()
	{
		$this->load->view('worksheet', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');

		$search = $this->input->post('search');
		$keyword = '';
		$user_id = $this->session->userdata['member_userid'];
		if($search) $keyword = $search['value'];
		
		$this->db->select('count(A.content_id) as ccount', FALSE);
		$this->db->from('contents A');
		$this->db->where('(A.template_id=8)');
		//$this->db->where('(A.user_id='.$user_id.')');
		if($keyword){
			$this->db->where('(A.text=' . $this->db->escape($keyword) .  ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;
		$this->db->select('A.*');
		$this->db->from('contents A');
		//$this->db->where('(A.user_id='.$user_id.')');
		$this->db->where('(A.template_id=8)');
		if($keyword){
			$this->db->where('(A.text=' . $this->db->escape($keyword) .  ')');
		}

		$this->db->order_by('A.content_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;		

		$response->data = array();
		foreach($results as $row){

			$this->db->where('sub_cat_topic_id', $row->topic_id);
			$topicInfo = $this->db->get('esub_cat_topic')->row();

			$this->db->where('sub_cat_id', $topicInfo->sub_cat_id);
			$subinfo = $this->db->get('ecategories')->row();
			$cat_title = '';
			if($subinfo){
				$cat_title = $subinfo->cat_title;
			

			$this->db->where('sub_id', $subinfo->e_sub_id);
			$esubjectinfo = $this->db->get('esubjects')->row();	
			$subject = '';
			if($esubjectinfo){
				$subject = $esubjectinfo->subject;
			}

			$this->db->where('id', $row->user_id);
			$userinfo = $this->db->get('users')->row();	
			$user = '';
			if($userinfo){
				$user = $userinfo->first_name." ".$userinfo->last_name;
			}


			$data = array();
			$data['id'] = $row->content_id;
			$data['topic'] = $topicInfo->topic;
			$data['subject'] = $subject;
			$data['cat_name'] = $cat_title;
			$data['doc_title'] = $row->doc_title;
			$data['doc_slug'] = $row->doc_slug;
			$data['description'] = $row->doc_description;
			$data['doc_url'] = $row->doc_url;
			$data['no_index'] = $row->no_index;
			$data['user'] = $user;
			$response->data[] = $data;
			}
		}
		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-worksheet');
		$topic_id = $_GET['topic_id'];
		$user_id = $this->session->userdata['member_userid'];

		$classes_info = $this->db->get('eclasses')->result();
		$this->template_data['classes_info'] = $classes_info;

		$this->db->where('template_id', 8);
		$this->db->where('topic_id', $topic_id);
		$worksheetinfo = $this->db->get('contents')->result();
		$this->template_data['worksheetinfo'] = $worksheetinfo;
		
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

		$this->load->view('worksheet_edit', $this->template_data);

	}

	function selectWorksheetsByParent(){

		$parentSheetID = $this->input->post('parent_sheet_id');

		$this->db->where('template_id', 8);
		$this->db->where('parent_id', $parentSheetID);
		$worksheetinfo = $this->db->get('contents')->result();
		$path = '';
		$path .= '<table id="myTable" class=" table order-list">
		    <thead>
		    <tr>
			 <td style="width: 50%;" ><div style="text-align: center;font-weight: bold;">Description</div></td>
             <td><div style="text-align: center;font-weight: bold;">WorkSheet Reference</div></td>
             <td></td>
		    </tr>
		    </thead>
		    <tbody>';
  
   if(!empty($worksheetinfo)){
     $i = 0; 
    foreach ($worksheetinfo as $key => $value) { 

      $path .='<tr><td>	
		     <input type="hidden" name="questioncount[]" value="1" />
             <input type="hidden" name="id'.$i.'" 
             value="'.$value->content_id.'">
             <label>Detail</label>
             <input type="text" id="slugme'.$i.'" class="form-control" placeholder="Title" name="title'.$i.'" value="'.$value->doc_title.'">
             <input type="text" placeholder="Slug" value="'.$value->doc_slug.'" name="doc_slug'.$i.'" class="form-control slug'.$i.' doc_slug">
             <textarea rows="3" placeholder="Description"  name="text'.$i.'" class="form-control">'.$value->doc_description.'</textarea>
             <label>Meta Data</label>
             <input type="text" id="meta_title'.$i.'" class="form-control" placeholder="Meta Title" name="meta_title'.$i.'" value="'.$value->meta_title.'">
             <textarea rows="3" placeholder="Meta Keywords" name="meta_keywords'.$i.'" class="form-control">'.$value->meta_keywords.'</textarea>
             <textarea rows="3" placeholder="Meta Description" name="meta_description'.$i.'" class="form-control">'.$value->meta_description.'</textarea>
             <label>Indexable</label>
             <select class="form-control" name="no_index'.$i.'">
             	<option ';
             	 if($value->no_index == 1){ 
             	 $path .= 'selected';
             	    }  
             	$path .= 'value="1">No</option>
             	<option ';
             	if($value->no_index == 0){ 
             	 $path .= 'selected';
             	} 
             	$path .= ' value="0">Yes</option></select></td><td>';
          	$path .='<input type="file" name="document_url'.$i.'" class="form-control"><input type="hidden" name="document_url'.$i.'" size="20" value="'.$value->doc_url.'" /><small>Select Worksheet</small><br>'.$value->doc_url.'</td><td> 
			<input type="file" name="thumbnail'.$i.'" class="form-control">
            <small>Select Thumb</small><input type="hidden" name="thumbnail'.$i.'" size="20" value="'.$value->doc_thumbnail.'" /><br>';
            if($value->doc_thumbnail){ 
              $path .= '<img style="width:105%;" src="worksheets/'.$value->doc_thumbnail.'">';
           } 
	$path .= '</td><td>';
      '</td>
		  <script type="text/javascript">
			 $(function(){
                $("#slugme'.$i.'").slugIt({
                    output: ".slug'.$i.'"
                });
            });
		  </script>
      </tr>';	
         $i++;  
       } 
        }else{ 
            $i = 1; 
            
         $path .= '<tr><td>  
             <input type="hidden" name="questioncount[]" value="1" />
             <input type="hidden" name="id0" value="0">
             <label>Detail</label>
             <input type="text" id="slugme0" class="form-control" placeholder="Title" name="title0" value="">
              <input type="text" value="" placeholder="Slug" name="doc_slug0" class="form-control slug0 doc_slug">
             <textarea rows="3" placeholder="Description" name="text0" class="form-control"></textarea>
             <label>Meta Data</label>
             <input type="text" id="meta_title0" class="form-control" placeholder="Meta Title" name="meta_title0" value="">
             <textarea rows="3" placeholder="Meta Keywords" name="meta_keywords0" class="form-control"></textarea>
             <textarea rows="3" placeholder="Meta Description" name="meta_description0" class="form-control"></textarea>
             <label>Indexable</label>
             <select class="form-control" name="no_index0">
             	<option value="1">No</option>
             	<option value="0">Yes</option>
             </select>
          </td>
          <td>
             <input type="file" name="document_url0" class="form-control">
             <small>Select Worksheet</small>
          </td>
           <td> 
			<input type="file" name="thumbnail0" class="form-control">
            <small>Select Thumb</small>
          </td>
          <td></td>
          <script type="text/javascript">
          $(function(){
                $("#slugme0").slugIt({
                    output: ".slug0"
                });
            });
          </script>
          </tr>'; 

         } 

		$path .= '</tbody><tfoot><tr><td colspan="5" style="text-align: left;"><input type="button" class="btn btn-lg btn-block btn-primary"  id="addrow" value="Add Worksheet" </td></tr><tr></tr></tfoot></table>'; 

		echo $path;

	}

	function save(){
	  $user_id = $this->session->userdata['member_userid'];
	  $date = date('Y-m-d H:i:s');	
	  header('Content-Type: application/json');
	  $config['upload_path']   = './worksheets/';
	  $config['allowed_types'] ="gif|jpg|jpeg|png|iso|dmg|zip|rar|doc|docx|xls|xlsx|ppt|pptx|csv|ods|odt|odp|pdf|rtf|sxc|sxi|txt|exe|avi|mpeg|mp3|mp4|3gp";  
	  $config['max_size']   = 1024;
	  $this->load->library('upload', $config);
	  
	  check_permission('admin-add-worksheet');

		$questioncount = $this->input->post('questioncount');
		$class_ids = serialize($this->input->post('class_id'));
		for($i=0; $i < count($questioncount); $i++){
			$id = $this->input->post('id'.$i);

			$doc_slug = $this->input->post('doc_slug'.$i);
			$meta_title = $this->input->post('meta_title'.$i);
			$meta_keywords = $this->input->post('meta_keywords'.$i);
			$meta_description = $this->input->post('meta_description'.$i);
			
			
			$this->db->trans_begin();
			if($id == 0){
			 
			$this->db->where('doc_slug', $doc_slug);
			$contentInfo = $this->db->get('contents')->row();
			if($contentInfo){
				return $this->output->set_output(json_encode(array('error' => TRUE, 'msg' => 'Slug '.$doc_slug.' Already Exist')));
				exit;
			}	

			 $this->upload->initialize($config);
 			 $this->upload->do_upload('document_url'.$i);  // File Name
 			 $worksheet = $this->upload->data(); 
  			 $worksheet_name = $worksheet['file_name'];	

  			 $this->upload->initialize($config);
  			 $this->upload->do_upload('thumbnail'.$i);  // File Name
  			 $worksheetThumbnail = $this->upload->data(); 
  			 $thumbnail_name = $worksheetThumbnail['file_name'];	
 			 

			$data = array(
				'topic_id' => trim($this->input->post('topic_id')),
				'template_id' => trim($this->input->post('template_id')),
				'doc_description' => trim($this->input->post('text'.$i)),
				'doc_url' => trim($worksheet_name),
				'doc_title' => trim($this->input->post('title'.$i)),
				'doc_thumbnail' => trim($thumbnail_name),
				'doc_slug' => $doc_slug,
				'meta_title' => $meta_title,
				'meta_keywords' => $meta_keywords,
				'meta_description' => $meta_description,
				'no_index' => 1,
				'created_date' => trim($date),
				'user_id' => $user_id
			);

		$this->db->insert('contents', $data);
		$new_question_id = $this->db->insert_id();
		
		$data2 = array(
			'class_id' => trim(0),
			'esubjects_sub_id' => trim($this->input->post('subject_id')),
			'ecategories_sub_cat_id' => trim($this->input->post('cat_id')),
			'esub_cat_topics_sub_cat_topic_id' => trim($this->input->post('topic_id')),
			'template_id' => trim($this->input->post('template_id')),
			'contents_content_id' => trim($new_question_id),
			'diff_level' => 0,
			'created_date' => trim($date),
			'user_id' => $user_id
		);
		//print_r($data2);
		//	exit;
		$this->db->insert('content_indexing', $data2);	
		$new_conteint_index_id = $this->db->insert_id();

		}else{

			$this->upload->initialize($config);
 			$this->upload->do_upload('document_url'.$i);  // File Name
 			$worksheet = $this->upload->data(); 
  			
  			if($worksheet['file_name']){
			  	$worksheetFile = $worksheet['file_name'];
			}else{
				$worksheetFile = trim($this->input->post('document_url'.$i));
			}

  			$this->upload->initialize($config);
  			$this->upload->do_upload('thumbnail'.$i);  // File Name
  			$worksheetThumbnail = $this->upload->data(); 
  			$thumbnail_name = $worksheetThumbnail['file_name'];
  			
  			if($worksheetThumbnail['file_name']){
			  	$thumbnailFile = $worksheetThumbnail['file_name'];
			}else{
				$thumbnailFile = trim($this->input->post('thumbnail'.$i));
			}

				$data = array(
				'topic_id' => trim($this->input->post('topic_id')),
				'template_id' => trim($this->input->post('template_id')),
				'doc_description' => trim($this->input->post('text'.$i)),
				'doc_url' => trim($worksheetFile),
				'doc_title' => trim($this->input->post('title'.$i)),
				'doc_thumbnail' => trim($thumbnailFile),
				'doc_slug' => $doc_slug,
				'meta_title' => $meta_title,
				'meta_keywords' => $meta_keywords,
				'meta_description' => $meta_description,
				'updated_date' => trim($date),
				'user_id' => $user_id
				);
			
		$this->db->where('content_id', $id);	
		$this->db->update('contents', $data);
		
		}
		$this->db->trans_complete();
		}
		
		$this->output->set_output(json_encode(array('success' => TRUE, 'msg' => 'Add Worksheet Success')));
	}

	function delete(){
		check_permission('admin-del-worksheet');
		$id = intval($this->input->get('id'));
		
		$this->db->trans_begin();
			
		// delete user roles
		$this->db->where('contents_content_id', $id);
		$this->db->delete('content_indexing');
	
		// delete user
		$this->db->where('content_id', $id);
		$this->db->delete('contents');
		
		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Worksheet Success'));
	}

}

// end this file

