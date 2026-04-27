<?php
namespace App\Controllers\Admin;



/**
 * Top Level Planning Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Top_level_planning extends MY_Controller {
	function __construct(){
		parent::__construct();
		check_permission('admin-top-level-planning');
	} 

	/**
	 * Index Page for this controller.
	 */

	public function index()
	{
		$this->load->view('top_level_planning', $this->template_data);

	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$search = $this->input->post('search');
		$campus_id = $this->session->userdata('member_campusid');

		$keyword = '';
		if($search) $keyword = $search['value'];

		$this->db->select('count(A.tlp_id) as ccount', FALSE);
		$this->db->from('top_level_planning A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campus_id) . ')');
		if($keyword){
			$this->db->where('(A.class_name=' . $this->db->escape($keyword) . ')');
		}
		$q = $this->db->get()->row();

		$response->recordsTotal = $q->ccount;
		$this->db->select('A.*');
		$this->db->from('top_level_planning A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campus_id) . ')');
		if($keyword){
			$this->db->where('(A.class_name=' . $this->db->escape($keyword)  . ')');
		}

		$this->db->order_by('A.tlp_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;
		$response->data = array();
		foreach($results as $row){
		
		$this->db->where('sid', $row->subject_id);
		$subjectinfo = $this->db->get('allsubject')->row();

		$this->db->where('class_id', $row->class_id);
		$classinfo = $this->db->get('classes')->row();	

		$this->db->where('term_session_id', $row->term_session_id);
		$terms_session_info = $this->db->get('terms_session')->row();
		
		$this->db->where('session_id', $terms_session_info->session_id);
		$session_info = $this->db->get('academic_session')->row();

		$this->db->where('term_id', $terms_session_info->term_id);
		$terms_info = $this->db->get('terms')->row();

			$data = array();
			$data['id'] = $row->tlp_id;
			$data['session_name'] = $session_info->session_name;	
			$data['term_name'] = $terms_info->name;
			$data['class_name'] = $classinfo->class_name;
			$data['subject'] = $subjectinfo->subject_name;
			$data['objective'] = $row->objective;
			$response->data[] = $data;

		}

		$this->output->set_output(json_encode($response));
	}

	
	function add(){

		check_permission('admin-add-top-level-planning');
		$sessionid = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();


		$terminfo = $this->db->get('terms')->result();
 		$this->template_data['terminfo'] = $terminfo;

		$currentrole = currentUserRoles();
		if(in_array(5, $currentrole)){
			$sectionsclassinfo = teacherSubjectSections();
		}else{
			$sectionsclassinfo = userClassSections();
		}

		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;
		
		//$this->db->where('session_id', $sessionid);		
		$academic_session = $this->db->query('select * from academic_session where system_id='.$schoolinfo->system_id.' AND session_id >='.$sessionid)->result();
 		$this->template_data['academic_session'] = $academic_session;

		$this->load->view('top_level_planning_edit', $this->template_data);

	}

	function edit(){
		check_permission('admin-edit-top-level-planning');
		$id = intval($this->input->get('id'));	

		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');

		$sessionData = array(
		'campusid' => $campusid,
		'sessionid' => $sessionid
		);

		$this->template_data['sessionData'] = $sessionData;

		$this->db->where('sid', $id);
		$info = $this->db->get('allsubject')->row();
		$this->template_data['info'] = $info;

		$classesinfo = $this->db->get('classes')->result();
 		$this->template_data['classesinfo'] = $classesinfo;
	
		$academic_session = $this->db->get('academic_session')->result();
 		$this->template_data['academic_session'] = $academic_session;

		$this->load->view('top_level_planning_edit', $this->template_data);

	}


function save() {
    // Enable CSRF Protection (ensure token is included in the form)
    //$this->security->csrf_verify();

    // Sanitize all inputs
    $synch = (bool)$this->input->post('synch');
    $id = ($this->input->post('eeid'));
    $term_session_ids = $this->input->post('term_session_id');
    $syllabus = $this->input->post('syllabus');
    $section_id = ($this->input->post('section_id'));
    $subject_id = ($this->input->post('subject_id'));
    $tlp_ids = $this->input->post('tlp_id');
    $campusid = $this->session->userdata('member_campusid');
    $user_id = $this->session->userdata('member_userid'); 
    $date = date('Y-m-d H:i:s');

    // Validate required fields
     $all_zero_tlp = !empty($tlp_ids) && 
                   count(array_filter($tlp_ids, function($v) { return $v != 0; })) === 0;

   
    if (empty($subject_id)) {
        json_response(['error' => true, 'msg' => 'Subject is required']);
        return;
    }

    // Fetch class info
    $classsectioninfo = $this->db->get_where('class_section', ['cls_sec_id' => $section_id])->row();
    if (!$classsectioninfo) {
        json_response(['error' => true, 'msg' => 'Invalid section']);
        return;
    }

    // Start transaction
    $this->db->trans_begin();

    try {
        //if (empty($tlp_ids)) {
    	if (empty($tlp_ids) || $all_zero_tlp) {
    		// echo "ADD";
        	// print_r($tlp_ids);
            // exit;
            // INSERT NEW ENTRIES ----------------------------------------------
            check_permission('admin-add-top-level-planning');

            // Get all campuses if synch is enabled
            $campuses = $synch 
                ? $this->db->select('campus_id')->get('campus')->result_array()
                : [['campus_id' => $campusid]];

                 //print_r($campuses);
            //exit;

            // Prepare batch data
            $batch_data = [];
            foreach ($term_session_ids as $i => $term_session_id) {

            	// print_r($term_ids);
            //exit;

                $sub_syllabus = $syllabus[$i] ?? '';
                $lock = (int)!empty($this->input->post("lock_{$term_session_id}"));
                $audio_url = $this->input->post("audio_url{$i}", true) ?? '';

                foreach ($campuses as $campus) {
                    $batch_data[] = [
                        'class_id' => $classsectioninfo->class_id,
                        'term_session_id' => $term_session_id,
                        'subject_id' => $subject_id,
                        'objective' => $sub_syllabus,
                        'campus_id' => $campus['campus_id'],
                        'set_lock' => $lock,
                        'audio_url' => $audio_url,
                        'created_date' => $date,
                        'user_id' => $user_id
                    ];
                }
            }

            //print_r($batch_data);
            //exit;
            // Batch insert
            $this->db->insert_batch('top_level_planning', $batch_data);
            //print_r($this->db->last_query());

        } else {

        	
            // UPDATE EXISTING ENTRIES ------------------------------------------
            check_permission('admin-edit-top-level-planning');

            $batch_update = [];
            foreach ($term_session_ids as $i => $term_session_id) {
                $tlp_id = $tlp_ids[$i] ?? 0;
                $sub_syllabus = $syllabus[$i] ?? '';
                $lock = (int)!empty($this->input->post("lock_{$term_session_id}"));
                $audio_url = $this->input->post("audio_url{$i}", true) ?? '';

                // Prepare update data
                $update_data = [
                    'objective' => $sub_syllabus,
                    'set_lock' => $lock,
                    'audio_url' => $audio_url,
                    'updated_date' => $date,
                    'user_id' => $user_id
                ];
               
                // Handle synch-to-all-campuses
                if ($synch) {
                    $this->db->where([
                        'class_id' => $classsectioninfo->class_id,
                        'term_session_id' => $term_session_id,
                        'subject_id' => $subject_id
                    ])->update('top_level_planning', $update_data);
                } else {
                    $this->db->where('tlp_id', $tlp_id)->update('top_level_planning', $update_data);
                }
            }
        }

        // Commit transaction
        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Database error');
        }
        $this->db->trans_commit();

        json_response(['success' => true, 'msg' => 'Saved successfully']);

    } catch (Exception $e) {
        $this->db->trans_rollback();
        json_response(['error' => true, 'msg' => 'Error: ' . $e->getMessage()]);
    }
}

	// function save(){
		
	// 	$synch = 0;
	// 	$id = intval($this->input->post('eeid'));
	// 	$term_ids = $this->input->post('term_id');
	// 	$syllabus = $this->input->post('syllabus');
	// 	$section_id = $this->input->post('section_id');
	// 	$subject_id = $this->input->post('subject_id');
	// 	$tlp_id = $this->input->post('tlp_id');
	// 	$term_id = $this->input->post('tid');
	// 	$campusid = $this->session->userdata('member_campusid');
	// 	$user_id = $this->session->userdata['member_userid'];
	// 	$date = date('Y-m-d H:i:s');
	// 	$schoolinfo = getSchoolInfo();

	// 	if(empty($subject_id)){
	// 		json_response(array('error' => TRUE, 'msg' => 'Select Subject to add weekly planning.'));
	// 		exit;
	// 	}
		
	// 	if(($this->input->post('synch'))){
	// 		$synch = $this->input->post('synch');
	// 	}


	// 	$this->db->where('cls_sec_id', $section_id);
	// 	$classsectioninfo = $this->db->get('class_section')->row();
		
	// 	if(empty($tlp_id)){
	// 		check_permission('admin-add-top-level-planning');
	// 		$this->db->trans_begin();
	// 		for($i=0; $i < count($term_ids); $i++){	
	// 		 $term_session_id = $term_ids[$i]; 
	// 		 $sub_syllabus = $syllabus[$i];
			
	// 		 if(empty($this->input->post('lock_'.$term_session_id))){
	// 		 	$campuslock = 1;
	// 		 }else{
	// 		 	$campuslock = $this->input->post('lock_'.$term_session_id);	
	// 		 }
			 
	// 		$subject_audio = $this->input->post('audio_url'.$i);

	// 		$this->db->where('system_id', $schoolinfo->system_id);
	//   		$campusinfo = $this->db->get('campus')->result();
	// 		foreach($campusinfo as $campus){

	// 				$data = array(
	// 					'class_id' => $classsectioninfo->class_id,
	// 					'term_session_id' => $term_session_id,
	// 					'subject_id' => $subject_id,
	// 					'objective' => $sub_syllabus,
	// 					'campus_id' => $campus->campus_id,
	// 					'set_lock' => 1,
	// 					'audio_url' => $subject_audio,
	// 					'created_date' => $date,
	// 					'user_id' => $user_id
	// 				);
				
	// 			$this->db->insert('top_level_planning', $data);
	// 			$new_user_id = $this->db->insert_id();
	// 		}

			
	// 	  	$data = array(
	// 				'set_lock' => $campuslock,			
	// 			);
				
	// 		$this->db->where('class_id', $classsectioninfo->class_id);
	// 		$this->db->where('term_session_id', $term_session_id);
	// 		$this->db->where('subject_id', $subject_id);
	// 		$this->db->where('campus_id', $campusid);
	// 		$this->db->where('set_lock', 0);
	// 		$this->db->update('top_level_planning', $data);
			
	// 		}
			
	// 		$this->db->trans_complete();
	// 		json_response(array('success' => TRUE, 'msg' => 'Top Level Planning Updated Successfully'));
	// 	}else{

	// 		check_permission('admin-edit-top-level-planning');
	// 		$this->db->trans_begin();
	// 		for($i=0; $i < count($term_ids); $i++){ 
			 
	// 		 $term_session_id = $term_ids[$i]; 
	// 		 //print_r($tlp_id);

	// 		 $tlpid = $tlp_id[0][$i];
	// 		 $sub_syllabus = $syllabus[$i];
			
	// 		 if(empty($this->input->post('lock_'.$term_session_id))){
	// 		 	$campuslock = 0;
	// 		 }else{
	// 		 	$campuslock = $this->input->post('lock_'.$term_session_id);	
	// 		 }
			
	// 		$subject_audio = $this->input->post('audio_url'.$i);
				
	// 		if($synch == 1){
	// 		$campusinfo = $this->db->get('campus')->result();
	// 		foreach($campusinfo as $campus){
			
	// 		if($subject_audio){
	// 			$data = array(
	// 				'objective' => $sub_syllabus,
	// 				'audio_url' => $subject_audio,
	// 				'updated_date' => $date,
	// 				'user_id' => $user_id
	// 			);
	// 		}else{
	// 			$data = array(
	// 				'objective' => $sub_syllabus,
	// 				'updated_date' => $date,
	// 				'user_id' => $user_id
	// 			);
	// 		}
				
	// 		$this->db->where('class_id', $classsectioninfo->class_id);
	// 		$this->db->where('term_session_id', $term_session_id);
	// 		$this->db->where('subject_id', $subject_id);
	// 		$this->db->where('campus_id', $campus->campus_id);
	// 		$this->db->where('set_lock', 0);
	// 		$this->db->update('top_level_planning', $data);
			
	// 	  }

	// 	  	$data = array(
	// 				'set_lock' => $campuslock,			
	// 			);
				
	// 		$this->db->where('class_id', $classsectioninfo->class_id);
	// 		$this->db->where('term_session_id', $term_session_id);
	// 		$this->db->where('subject_id', $subject_id);
	// 		$this->db->where('campus_id', $campusid);
	// 		$this->db->where('set_lock', 0);
	// 		$this->db->update('top_level_planning', $data);

	// 	 }else{
	// 	 	if($subject_audio){
	// 	 		$data = array(
	// 				'class_id' => $classsectioninfo->class_id,
	// 				'term_session_id' => $term_session_id,
	// 				'subject_id' => $subject_id, 
	// 				'objective' => $sub_syllabus,
	// 				'audio_url' => $subject_audio,
	// 				'campus_id' => $campusid,
	// 				'set_lock' => $campuslock,
	// 				'updated_date' => $date,
	// 				'user_id' => $user_id		
				
	// 			);
	// 		 }else{
	// 		 	$data = array(
	// 				'class_id' => $classsectioninfo->class_id,
	// 				'term_session_id' => $term_session_id,
	// 				'subject_id' => $subject_id, 
	// 				'objective' => $sub_syllabus,
	// 				'campus_id' => $campusid,
	// 				'set_lock' => $campuslock,
	// 				'updated_date' => $date,
	// 				'user_id' => $user_id		
				
	// 			);
	// 		 }

			
	// 		$this->db->where('tlp_id', $tlpid);
	// 		$this->db->where('campus_id', $campusid);
	// 		$this->db->update('top_level_planning', $data);
	// 	 }

	// 	}

	// 	$this->db->trans_complete();
	// 	json_response(array('success' => TRUE, 'msg' => 'Edit Top Level Planning Success'));

	// 	}

	// }

// function selectSubjectsforTopLevelPlanning(){
	
// 		$sessionid =  $this->input->post('session_id');
// 		$section_id = $this->input->post('section_id');
// 		$subject_id = $this->input->post('subject_id');
// 		$campusid = $this->session->userdata('member_campusid');

// 		$this->db->where('cls_sec_id', $section_id);
// 		$classsectioninfo = $this->db->get('class_section')->row();
		
		
// 		$this->db->where('session_id', $sessionid);
// 		$term_session_info = $this->db->get('terms_session')->result();	
// 		$subjectList = '';
// 		$subjectList = '<table class="table"><tr><th style="width:10%;">Terms</th><th>Syllabus</th><th>Lock</th></tr>';
// 		$nCount = 0;
// 		foreach ($term_session_info as $key => $value) {	

// 		$this->db->where('subject_id', $subject_id);
// 		$this->db->where('class_id', $classsectioninfo->class_id);
// 		$this->db->where('term_session_id', $value->term_session_id);
// 		$this->db->where('campus_id', $campusid);
// 		$top_level_planning_info = $this->db->get('top_level_planning')->row();	

// 		$this->db->where('term_id', $value->term_id);
// 		$terms_info = $this->db->get('terms')->row();
		
// 		if(!empty($top_level_planning_info)){			
// 			$subjectList .= "<tr><td><input type='hidden' name='tlp_id[]'  value='".$top_level_planning_info->tlp_id."'><input type='hidden' name='term_id[]'  value='".$value->term_session_id."'>".$terms_info->name."</td><td><textarea name='syllabus[]' class='form-control editor'>".$top_level_planning_info->objective."</textarea>
// 			<script> 
// 			$(document).ready(function() {
// 			  $('.editor').summernote();
// 			});	
// 			</script>
// 			</td><td><input type='checkbox' name='lock_".$value->term_session_id."'";
// 			if($top_level_planning_info->set_lock == 1){
// 				$subjectList .= "checked='checked'";
// 			}
// 			if($top_level_planning_info->audio_url){
// 				$arryoutubeurl = explode('/', $top_level_planning_info->audio_url);
// 			    $youtubeembed = $arryoutubeurl[3];
// 			}else{
// 				$youtubeembed = '';
// 			}
			
// 			$subjectList .= " value='1'><iframe frameborder='0' src='https://www.youtube.com/embed/".$youtubeembed."' width='230' height='150' class='note-video-clip'></iframe>
// 		</td></tr>";
// 			$subjectList .= "<tr><td>Video URL </td><td><input type='url' class='form-control' placeholder='Video URL' value='".$top_level_planning_info->audio_url."' name='audio_url".$nCount."'></td><td></td></tr>";
		
// 		}else{
		
// 		$subjectList .= '<input type="hidden" name="eeid"  value="0">';						
// 			$subjectList .= "<tr><td><input type='hidden' name='term_id[]'  value='".$value->term_session_id."'>".$terms_info->name."</td><td><textarea name='syllabus[]' class='form-control editor'></textarea>
// 			<script>
// 			$(document).ready(function() {
// 				  $('.editor').summernote();
// 				});	
// 			</script>
// 			</td><td><input type='checkbox' checked='checked' name='lock_".$value->term_session_id."'  value='1'></td></tr><tr><td>Video URL</td><td><input type='url' placeholder='Video URL' class='form-control' name='audio_url".$nCount."'></td></tr>";
		
// 		}
// 		$nCount++;
// 		}
// 		$subjectList .= '</table>';	
// 		$this->output->set_output($subjectList);
		
// 	}

// function selectSubjectsforTopLevelPlanning() {
//     // CSRF token for AJAX requests (ensure this is handled in the framework)
//     //$this->output->set_content_type('application/json');

//     // Sanitize inputs
//     $sessionid = $this->input->post('session_id', true);
//     $section_id = $this->input->post('section_id', true);
//     $subject_id = $this->input->post('subject_id', true);
//     $campusid = $this->session->userdata('member_campusid');

//     // Fetch class info
//     $classsectioninfo = $this->db->get_where('class_section', ['cls_sec_id' => $section_id])->row();
//     if (!$classsectioninfo) {
//         return $this->output->set_output(json_encode(['error' => 'Invalid section']));
//     }

//     // Fetch all term sessions with related data in a single query
//     $this->db->select('ts.term_session_id, ts.term_id, t.name as term_name, tlp.tlp_id, tlp.objective, tlp.set_lock, tlp.audio_url');
//     $this->db->from('terms_session ts');
//     $this->db->join('terms t', 't.term_id = ts.term_id');
//     $this->db->join('top_level_planning tlp', 'tlp.term_session_id = ts.term_session_id AND tlp.class_id = '.$classsectioninfo->class_id.' AND tlp.subject_id = '.$subject_id.' AND tlp.campus_id = '.$campusid, 'left');
//     $this->db->where('ts.session_id', $sessionid);
//     $this->db->where('tlp.class_id', $classsectioninfo->class_id);
//     $this->db->where('tlp.subject_id', $subject_id);
//     $term_sessions = $this->db->get()->result();

//     //print_r($term_sessions);

//     // Generate HTML
//     $subjectList = '<table class="table"><tr><th style="width:10%;">Terms</th><th>Syllabus</th><th>Lock</th></tr>';
//     foreach ($term_sessions as $index => $term) {
//         $tlp_id = $term->tlp_id ?? 0;
//         $objective = htmlspecialchars($term->objective ?? '');
//         $audio_url = htmlspecialchars($term->audio_url ?? '');
//         $checked = ($term->set_lock ?? 0) ? 'checked' : '';
        
//         // Extract YouTube ID if URL exists
//         $youtube_embed = '';
//         if (!empty($term->audio_url)) {
//             parse_str(parse_url($term->audio_url, PHP_URL_QUERY), $query_params);
//             $youtube_embed = $query_params['v'] ?? '';
//         }

//         $subjectList .= "<tr>
//             <td>
//                 <input type='hidden' name='tlp_id[]' value='{$tlp_id}'>
//                 <input type='hidden' name='term_id[]' value='{$term->term_session_id}'>
//                 {$term->term_name}
//             </td>
//             <td>
//                 <textarea name='syllabus[]' class='form-control editor'>{$objective}</textarea>
//             </td>
//             <td>
//                 <input type='checkbox' name='lock_{$term->term_session_id}' {$checked} value='1'>
//                 " . ($youtube_embed ? "<iframe src='https://www.youtube.com/embed/{$youtube_embed}' width='230' height='150'></iframe>" : "") . "
//             </td>
//         </tr>
//         <tr>
//             <td>Video URL</td>
//             <td colspan='2'><input type='url' class='form-control' name='audio_url_{$index}' value='{$audio_url}'></td>
//         </tr>";
//     }
//     $subjectList .= '</table>';

//     // Initialize Summernote once
//     $subjectList .= "<script>
//         $(document).ready(function() {
//             $('.editor').summernote();
//         });
//     </script>";

//     $this->output->set_output($subjectList);
// }
	

function selectSubjectsforTopLevelPlanning() {
    //$this->output->set_content_type('application/json');

    // Sanitize inputs
    $sessionid = (int)$this->input->post('session_id', true);
    $section_id = (int)$this->input->post('section_id', true);
    $subject_id = (int)$this->input->post('subject_id', true);
    $campusid = (int)$this->session->userdata('member_campusid');

    // Fetch class info
    $classsectioninfo = $this->db->get_where('class_section', ['cls_sec_id' => $section_id])->row();
    if (!$classsectioninfo) {
        return $this->output->set_output(json_encode(['error' => 'Invalid section']));
    }

    // Get term sessions with planning data
    $this->db->select('
        ts.term_session_id,
        ts.term_id,
        t.name as term_name,
        tlp.tlp_id,
        tlp.objective,
        tlp.set_lock,
        tlp.audio_url
    ');

    $this->db->from('terms_session ts');
    $this->db->join('terms t', 't.term_id = ts.term_id');
    $this->db->join('top_level_planning tlp', 
        "tlp.term_session_id = ts.term_session_id 
        AND tlp.class_id = {$classsectioninfo->class_id}
        AND tlp.subject_id = {$subject_id}
        AND tlp.campus_id = {$campusid}", 
        'left');

    $this->db->where('ts.session_id', $sessionid);
    $term_sessions = $this->db->get()->result();

    // Start HTML generation
    $html = '<div class="syllabus-container">';
    
    if (empty($term_sessions)) {
        $html .= '<div class="alert alert-warning">No term sessions found for selected academic session.</div>';
    } else {
        //$html .= '<form id="syllabus-form">';
        $html .= '<table class="table table-bordered syllabus-table">
                    <thead>
                        <tr>
                            <th style="width:15%">Term</th>
                            <th style="width:60%">Syllabus Content</th>
                            <th style="width:25%">Settings</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($term_sessions as $index => $term) {
            $tlp_id = $term->tlp_id ?? 0;
            $objective = htmlspecialchars($term->objective ?? '');
            $audio_url = htmlspecialchars($term->audio_url ?? '');
            $checked = ($term->set_lock ?? 0) ? 'checked' : '';
            
            // YouTube ID extraction
            $youtube_id = '';
            if (!empty($term->audio_url)) {
                parse_str(parse_url($term->audio_url, PHP_URL_QUERY), $params);
                $youtube_id = $params['v'] ?? '';
            }

            $html .= '<tr class="term-row">
                        <td>
                            <input type="hidden" name="tlp_id[]" value="'.$tlp_id.'">
                            <input type="hidden" name="term_session_id[]" value="'.$term->term_session_id.'">
                            <h5>'.$term->term_name.'</h5>
                        </td>
                        <td>
                            <textarea name="syllabus[]" class="form-control syllabus-editor" rows="5">'.$objective.'</textarea>
                        </td>
                        <td>
                            <div class="form-group">
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="lock_'.$term->term_session_id.'" '.$checked.' value="1">
                                    Lock Editing
                                </label>
                            </div>
                            <div class="video-preview">';
            
            if ($youtube_id) {
                $html .= '<div class="embed-responsive embed-responsive-16by9 mb-2">
                            <iframe class="embed-responsive-item" 
                                    src="https://www.youtube.com/embed/'.$youtube_id.'" 
                                    allowfullscreen></iframe>
                          </div>';
            }
            
            $html .= '<input type="url" 
                             class="form-control video-url" 
                             name="audio_url_'.$index.'" 
                             placeholder="Enter YouTube URL" 
                             value="'.$audio_url.'">
                     </div>
                 </td>
             </tr>';
        }

        $html .= '</tbody></table>';
        
        //$html .= '</form>';
    }

    $html .= '</div>'; // Close container

    // Summernote initialization
    $html .= '<script>
    $(document).ready(function() {
        $(".syllabus-editor").summernote({
            height: 200,
            toolbar: [
                ["style", ["bold", "italic", "underline"]],
                ["para", ["ul", "ol"]],
                ["insert", ["link"]],
                ["view", ["codeview"]]
            ]
        });
        
        // Handle video URL changes
        $(".video-url").on("change", function() {
            const $preview = $(this).closest(".video-preview").find("iframe");
            const url = new URLSearchParams(new URL(this.value).search);
            $preview.attr("src", "https://www.youtube.com/embed/" + url.get("v"));
        });
    });
    </script>';

    $this->output->set_output($html);
}

	function delete(){

		check_permission('admin-del-top-level-planning');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();

		$this->db->where('tlp_id', $id);
		$this->db->delete('top_level_planning');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Top Level Planning Success'));
	}
}
// end this file