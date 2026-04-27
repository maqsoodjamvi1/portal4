<?php
namespace App\Controllers\Admin;


/**
 * Top Level Planning Manage
 *
 * @author      Maqsood Jamvi
 * @copyright   Copyright (c) 2008~2099 timesoftsol.com
 * @email       maqsoodjamvi@gmail.com
 * @filesource
 */


class Top_level_planning_gradewise extends MY_Controller {

    function __construct(){
        parent::__construct();
        check_permission('admin-top-level-planning');
    } 

    /**
     * Index Page for this controller.
     */
    public function index() {
        $campus_id = $this->session->userdata('member_campusid');
        $sessionid = $this->session->userdata('member_sessionid');
        
        // Get filter parameters from POST or initialize with all available options
        $selected_terms = $this->input->post('terms') ? $this->input->post('terms') : [];
        $selected_classes = $this->input->post('classes') ? $this->input->post('classes') : [];
        $selected_subjects = $this->input->post('subjects') ? $this->input->post('subjects') : [];

        // Get all filter options
        $filter_data = [
            'all_terms' => $this->db->query("
                SELECT ts.term_session_id, t.name AS term_name 
                FROM terms_session ts
                JOIN terms t ON t.term_id = ts.term_id
                WHERE ts.session_id = ?
                ORDER BY ts.term_id ASC
            ", [$sessionid])->result_array(),
            
            'all_classes' => $this->db->query("
                SELECT DISTINCT c.class_id, c.class_name 
                FROM classes c
                JOIN class_section cs ON cs.class_id = c.class_id
                WHERE cs.campus_id = ? AND cs.status = 1
                ORDER BY c.class_id ASC
            ", [$campus_id])->result_array(),
            
            'all_subjects' => $this->db->query("
                SELECT DISTINCT s.sid, s.subject_name 
                FROM section_subjects ss
                JOIN allsubject s ON s.sid = ss.subject_id
                JOIN class_section cs ON cs.cls_sec_id = ss.cls_sec_id
                WHERE cs.campus_id = ? AND cs.status = 1
                ORDER BY s.subject_name ASC
            ", [$campus_id])->result_array()
        ];

        // If no filters selected, select all by default
        if (empty($selected_terms) && empty($selected_classes) && empty($selected_subjects)) {
            $selected_terms = array_column($filter_data['all_terms'], 'term_session_id');
            $selected_classes = array_column($filter_data['all_classes'], 'class_id');
            $selected_subjects = array_column($filter_data['all_subjects'], 'sid');
        }

        // Prepare WHERE conditions for filters
        $where_conditions = [
            "tlp.campus_id = $campus_id",
            "ts.session_id = $sessionid"
        ];

        if (!empty($selected_terms)) {
            $terms_list = implode(',', array_map('intval', $selected_terms));
            $where_conditions[] = "ts.term_session_id IN ($terms_list)";
        }

        if (!empty($selected_classes)) {
            $classes_list = implode(',', array_map('intval', $selected_classes));
            $where_conditions[] = "tlp.class_id IN ($classes_list)";
        }

        if (!empty($selected_subjects)) {
            $subjects_list = implode(',', array_map('intval', $selected_subjects));
            $where_conditions[] = "tlp.subject_id IN ($subjects_list)";
        }

        $where_clause = implode(' AND ', $where_conditions);

        // Main data query
        $query = $this->db->query("
            SELECT 
                c.class_id,
                c.class_name,
                s.sid AS subject_id,
                s.subject_name,
                tlp.objective,
                ts.term_session_id,
                t.name AS term_name
            FROM top_level_planning tlp
            JOIN classes c ON c.class_id = tlp.class_id
            JOIN allsubject s ON s.sid = tlp.subject_id
            JOIN terms_session ts ON ts.term_session_id = tlp.term_session_id
            JOIN terms t ON t.term_id = ts.term_id
            WHERE $where_clause
            ORDER BY c.class_id ASC, s.subject_name ASC, ts.term_id ASC
        ");

        $grouped_data = [];
        $all_terms_in_data = [];
        
        foreach ($query->result() as $row) {
            $key = $row->class_id;
            
            // Clean the objective - strip HTML tags, remove &nbsp; and trim whitespace
            $cleaned_objective = trim(strip_tags(str_replace('&nbsp;', ' ', html_entity_decode($row->objective))));
            
            if (!isset($grouped_data[$key])) {
                $grouped_data[$key] = [
                    'class_id' => $row->class_id,
                    'class_name' => $row->class_name,
                    'subjects' => [],
                    'terms' => []
                ];
            }
            
            if (!isset($grouped_data[$key]['subjects'][$row->subject_id])) {
                $grouped_data[$key]['subjects'][$row->subject_id] = [
                    'subject_name' => $row->subject_name,
                    'objectives' => []
                ];
            }
            
            // Only store non-empty objectives
            if (!empty($cleaned_objective)) {
                $grouped_data[$key]['subjects'][$row->subject_id]['objectives'][$row->term_session_id] = $cleaned_objective;
            }
            
            if (!isset($grouped_data[$key]['terms'][$row->term_session_id])) {
                $grouped_data[$key]['terms'][$row->term_session_id] = $row->term_name;
                $all_terms_in_data[$row->term_session_id] = $row->term_name;
            }
        }

        // Remove terms that have no objectives in any subject for any class
        foreach ($grouped_data as &$class_data) {
            $terms_with_data = [];
            
            // First find which terms have data in this class
            foreach ($class_data['subjects'] as $subject) {
                foreach ($subject['objectives'] as $term_id => $objective) {
                    $terms_with_data[$term_id] = true;
                }
            }
            
            // Then remove terms that don't have any data
            foreach ($class_data['terms'] as $term_id => $term_name) {
                if (!isset($terms_with_data[$term_id])) {
                    unset($class_data['terms'][$term_id]);
                    
                    // Also remove this term from all subjects
                    foreach ($class_data['subjects'] as &$subject) {
                        if (isset($subject['objectives'][$term_id])) {
                            unset($subject['objectives'][$term_id]);
                        }
                    }
                }
            }
            
            // Remove subjects that have no objectives for any term
            foreach ($class_data['subjects'] as $subject_id => $subject) {
                if (empty($subject['objectives'])) {
                    unset($class_data['subjects'][$subject_id]);
                }
            }
        }
        
        // Remove classes that have no subjects with objectives
        foreach ($grouped_data as $class_id => $class_data) {
            if (empty($class_data['subjects']) || empty($class_data['terms'])) {
                unset($grouped_data[$class_id]);
            }
        }

        // Sort classes by class_id
        ksort($grouped_data);

        $this->template_data['filter_data'] = $filter_data;
        $this->template_data['selected_filters'] = [
            'terms' => $selected_terms,
            'classes' => $selected_classes,
            'subjects' => $selected_subjects
        ];
        $this->template_data['grouped_data'] = $grouped_data;
        $this->template_data['session_name'] = $this->db->get_where('academic_session', 
            ['session_id' => $sessionid])->row()->session_name;

        if ($this->input->post('is_ajax')) {
            $this->load->view('top_level_planning_gradewise_result_view', $this->template_data);
            return;
        }

        $this->load->view('top_level_planning_gradewise_full_view', $this->template_data);

        //$this->load->view('top_level_planning_gradewise', $this->template_data);
    }
}