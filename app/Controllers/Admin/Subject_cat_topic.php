<?php
namespace App\Controllers\Admin;



/**
 * Subject Category Topics Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Subject_cat_topic extends MY_Controller {
	
	function __construct(){
		parent::__construct();
		check_permission('admin-subject-category-topics');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('subject_cat_topic', $this->template_data);
	}

	function data(){

		$response = new stdClass;
		$response->draw = $this->input->post('draw');

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		$this->db->select('count(A.sub_cat_topic_id) as ccount', FALSE);
		$this->db->from('esub_cat_topic A');
		if($keyword){
			$this->db->where('(A.topic=' . $this->db->escape($keyword) .  ')');
		}

		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		$this->db->select('A.*');
		$this->db->from('esub_cat_topic A');
		if($keyword){
			$this->db->where('(A.topic=' . $this->db->escape($keyword) .  ')');
		}

		$this->db->order_by('A.sub_cat_topic_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;
		$response->data = array();
		foreach($results as $row){
			
			$this->db->where('sub_cat_id', $row->sub_cat_id);
			$subinfo = $this->db->get('ecategories')->row();
			$cat_title = '';
			if($subinfo){
				$cat_title = $subinfo->cat_title;
			
			
			$worksheetsCount = $this->db->query('SELECT COUNT(content_id) AS totalWorksheets FROM contents WHERE template_id=8 AND topic_id='.$row->sub_cat_topic_id)->row();	

			$this->db->where('sub_id', $subinfo->e_sub_id);
			$esubjectinfo = $this->db->get('esubjects')->row();	
			$subject = '';
			if($esubjectinfo){
				$subject = $esubjectinfo->subject;
			}


			$data = array();
			$data['id'] = $row->sub_cat_topic_id;
			$data['ws_counts'] = $worksheetsCount->totalWorksheets;
			$data['subject'] = $subject;
			$data['cat_name'] = $cat_title;
			$data['topic'] = $row->topic; 
			$data['detail'] = $row->detail;
			$response->data[] = $data;
			}

		}

		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-subject-category-topic');

		$subjectinfo = $this->db->get('esubjects')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;

		$this->load->view('subject_cat_topic_edit', $this->template_data);

	}

	function edit(){
		check_permission('admin-edit-subject-category-topic');
		$id = intval($this->input->get('id'));

		$subjectinfo = $this->db->get('esubjects')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;

		$this->db->where('sub_cat_id', $id);
		$sub_category_info = $this->db->get('ecategories')->row();
		$this->template_data['sub_category_info'] = $sub_category_info;
		
		$this->db->where('sub_cat_topic_id', $id);
		$info = $this->db->get('esub_cat_topic')->row();

		$this->template_data['info'] = $info;
		$this->load->view('subject_cat_topic_edit', $this->template_data);

	}

	function save(){
		
		$rowscount = $this->input->post('rowscount');
		$this->form_validation->set_rules('topic_name0', 'Name', 'trim|required');
		if($this->form_validation->run() === FALSE){
			json_response(array('success' => FALSE, 'msg' => validation_errors()));
		}else{
			for($i=0; $i < count($rowscount); $i++){
				$id = $this->input->post('id'.$i);
				$meta_title = $this->input->post('meta_title'.$i);
				$meta_keywords = $this->input->post('meta_keywords'.$i);
				$meta_description = $this->input->post('meta_description'.$i);
				
				if($id == 0){
					$detail = $this->input->post('detail'.$i);
					$topic_name = $this->input->post('topic_name'.$i);
					$data = array(
					'sub_cat_id' => trim($this->input->post('cat_id')),
					'topic' => trim($topic_name),
					'slug' => trim($this->input->post('slug'.$i)),
					'detail' => trim($detail),
					'meta_title' => $meta_title,
					'meta_keywords' => $meta_keywords,
					'meta_description' => $meta_description,
					);
				
					$this->db->insert('esub_cat_topic', $data);
					$new_user_id = $this->db->insert_id();
				}else{
						
					$detail = $this->input->post('detail'.$i);
					$topic_name = $this->input->post('topic_name'.$i);
					$data = array(
					'sub_cat_id' => trim($this->input->post('cat_id')),
					'topic' => trim($topic_name),
					'slug' => trim($this->input->post('slug'.$i)),
					'detail' => trim($detail),
					'meta_title' => $meta_title,
					'meta_keywords' => $meta_keywords,
					'meta_description' => $meta_description,
					);
						
					$this->db->where('sub_cat_topic_id', $id);
					$this->db->update('esub_cat_topic', $data);
				}
				
		$this->db->trans_complete();
	}	

	json_response(array('success' => TRUE, 'msg' => 'Add Subject Category Success'));
}
}

function selectcategoriesbysubject(){
	
	    $subject_id = $this->input->post('subject_id');
		
	    $this->db->where('e_sub_id', $subject_id);
	 	$subjects_category_info = $this->db->get('ecategories')->result();
	
		$subjectscategories = '<option value="">Select Categorry</option>';
		foreach($subjects_category_info as $subjects_category){
		    $subjectscategories .= "<option value='".$subjects_category->sub_cat_id."'>".$subjects_category->cat_title."</option>"; 
		 }
		$this->output->set_output($subjectscategories);
		
}

function getTopicsCat(){
		$cat_id = $this->input->post('cat_id');

		$this->db->where('sub_cat_id', $cat_id);
		$info = $this->db->get('esub_cat_topic')->result();
		$subject_list = '';
	    $subject_list .= '<div class=""><table class="table table-bordered" id="dynamic_field">'; 
	    $subject_list .= '<tr><th>Subject Name</th><th>Slug</th><th>Detail</th></tr>';
		$i = 0;
		foreach ($info as $key => $value) { 
			$subject_list .= '<tr><td><input type="hidden" name="rowscount[]" value="1" />';	
            $subject_list .= '<input type="hidden" name="id'.$i.'" value="'.$value->sub_cat_topic_id.'"><input type="text" id="slugme'.$i.'"  name="topic_name'.$i.'"  value="'.$value->topic.'" placeholder="Subject Name" class="form-control name_list" required="" /></td>'; 
      		$subject_list .= '<td><input type="text" name="slug'.$i.'"  value="'.$value->slug.'" placeholder="Slug" class="form-control name_list slug'.$i.'" required="" /></td>';
            $subject_list .= '<td><input type="text" name="detail'.$i.'"
             value="'.$value->detail.'" placeholder="Detail" class="form-control name_list"  /><input type="text" id="meta_title'.$i.'" class="form-control" placeholder="Meta Title" name="meta_title'.$i.'" value="'.$value->meta_title.'"><textarea rows="3" placeholder="Meta Keywords" name="meta_keywords'.$i.'" class="form-control">'.$value->meta_keywords.'</textarea><textarea rows="3" placeholder="Meta Description" name="meta_description'.$i.'" class="form-control">'.$value->meta_description.'</textarea></td></tr>';
            $subject_list .= '<script type="text/javascript">
			          $(function(){
			                $("#slugme'.$i.'").slugIt({
			                    output: ".slug'.$i.'"
			                });
			            });
			          </script>';
                     $i++;   
              } 
            $subject_list .= '<tr><td></td><td></td> <td><button type="button" name="add" id="add" class="btn btn-success">Add More</button></td></tr></table>       
            </div>'; 
    $subject_list .=  "<script type='text/javascript'>
    $(document).ready(function(){      
      var i = ".$i."; 
      $('#add').click(function(){  
        
           $('#dynamic_field').append(\"<tr id='row\" + i + \"' class='dynamic-added'><td><input type='hidden' name='id\" + i + \"' value='0'><input type='hidden' name='rowscount[]' value='1' /><input type='text'  id='slugme\"+ i +\"' name='topic_name\" + i + \"' placeholder='Topic Name' class='form-control name_list' required /></td><td><input type='text' name='slug\" + i + \"' placeholder='Slug' class='form-control name_list slug\"+ i +\"' required /></td><td><input type='text' name='detail\" + i + \"' placeholder='Detail' class='form-control name_list'  /><input type='text' id='meta_title\"+ i +\"' class='form-control' placeholder='Meta Title' name='meta_title\" + i + \"' value=''><textarea rows='3' placeholder='Meta Keywords' name='meta_keywords\" + i + \"' class='form-control'></textarea><textarea rows='3' placeholder='Meta Description' name='meta_description\" + i + \"' class='form-control'></textarea></td><td><button type='button' name='remove' id='\" + i + \"' class='btn btn-danger btn_remove btn-sm'>X</button></td></tr>\"); 
           	
           	$('#slugme'+ i).slugIt({
           		output: '.slug'+ i
       		});

            i++;   
      });
  
      $(document).on('click', '.btn_remove', function(){  
           var button_id = $(this).attr(\"id\");  
           $('#row'+button_id).remove();  
      });  
  
    });  
</script>";  
$this->output->set_output($subject_list);
}

function delete(){
	check_permission('admin-del-subject-category-topic');
	$id = intval($this->input->get('id'));
	$this->db->trans_begin();
	// delete user
	$this->db->where('sub_cat_id', $id);
	$this->db->delete('sub_cat_topic');
	$this->db->trans_complete();
	json_response(array('success' => TRUE, 'msg' => 'Delete Topic Success'));
}
}
// end this file
