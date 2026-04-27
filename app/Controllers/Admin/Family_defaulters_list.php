<?php
namespace App\Controllers\Admin;




class Family_defaulters_list extends MY_Controller {

    function __construct(){
        parent::__construct();
        check_permission('admin-family-fee-history');
        $this->load->helper(array('form', 'url'));
        $this->load->database();
    }

   public function index() {

   	 $campus_id = $this->session->userdata('member_campusid');
     $schoolinfo = getSchoolInfo();

    try {
        // Get school/system info
        $system_id = $schoolinfo->system_id;
        if (empty($system_id)) {
            throw new Exception("System ID not found in session");
        }

        // $schoolinfo = $this->db->select('system_id')->from('system')->where('system_id', $system_id)->get();

        
        // if (!$schoolinfo) {
        //     throw new Exception("Failed to fetch system information");
        // }
        
        // $schoolinfo = $schoolinfo->row();
        // if (!$schoolinfo) {
        //     throw new Exception("System information not found");
        // }


        // Get user roles
        // $currentrole = [];
        // $roles = $this->session->userdata('member_roles');
        // if ($roles) {
        //     $currentrole = explode(',', $roles);
        // }

        // // Get class sections based on role
        // $sectionsclassinfo = [];
        // $user_id = $this->session->userdata('member_userid');
        
        // if (empty($user_id)) {
        //     throw new Exception("User ID not found in session");
        // }

        // if (in_array(5, $currentrole)) {
        //     // Teacher role
        //     $query = $this->db->query("
        //         SELECT DISTINCT cs.class_id, cs.section_id, c.class_name, s.section_name 
        //         FROM teacher_subjects ts 
        //         JOIN class_sections cs ON ts.class_id = cs.class_id AND ts.section_id = cs.section_id 
        //         JOIN classes c ON cs.class_id = c.class_id 
        //         JOIN sections s ON cs.section_id = s.section_id 
        //         WHERE ts.teacher_id = ?
        //     ", [$user_id]);
        // } else {
        //     // Other roles
        //     $query = $this->db->query("
        //         SELECT DISTINCT cs.class_id, cs.section_id, c.class_name, s.section_name 
        //         FROM user_class_sections ucs 
        //         JOIN class_sections cs ON ucs.class_id = cs.class_id AND ucs.section_id = cs.section_id 
        //         JOIN classes c ON cs.class_id = c.class_id 
        //         JOIN sections s ON cs.section_id = s.section_id 
        //         WHERE ucs.user_id = ?
        //     ", [$user_id]);
        // }

        // if (!$query) {
        //     throw new Exception("Failed to fetch class sections");
        // }
        
        // $sectionsclassinfo = $query->result();

        $currentrole = currentUserRoles();
        
        if(in_array(5, $currentrole)) {
            $sectionsclassinfo = teacherSubjectSections();
        } else {
            $sectionsclassinfo = userClassSections();
        }

        // Get fee types
        $fee_types_query = $this->db->query('SELECT * FROM fee_type WHERE system_id = ?', [$system_id]);
        if (!$fee_types_query) {
            throw new Exception("Failed to fetch fee types");
        }
        $fee_types = $fee_types_query->result();

        // Prepare template data
        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;    
        $this->template_data['fee_types'] = $fee_types;
        
        // Load view
        $this->load->view('family_defaulters_list', $this->template_data);

    } catch (Exception $e) {
        // Log the error
        log_message('error', 'Error in Family_defaulters_list::index(): ' . $e->getMessage());
        
        // Show error page or message
        show_error("An error occurred while loading the page. Please try again later.", 500);
    }
}

    public function data() {
        $filters = [
            'parent_id' => $this->input->post('parent_id'),
            'fee_type' => $this->input->post('fee_type'),
            'month' => $this->input->post('month'),
        ];

        $limit = $this->input->post('length');
        $offset = $this->input->post('start');
        $campus_id = $this->session->userdata('member_campusid');

        $year = date('Y');
        $month = (!empty($filters['month']) && is_numeric($filters['month']) && $filters['month'] > 0 && $filters['month'] <= 12)
            ? str_pad($filters['month'], 2, '0', STR_PAD_LEFT)
            : date('m');
        $month_filter = $month.'/'.$year;

        // Get records
        $this->db->select('p.parent_id,
                         MAX(s.student_id) as student_id,
                         MAX(CONCAT(s.first_name, " ", s.last_name)) as student_name,
                         p.f_name, p.father_contact, p.mother_contact,
                         MAX(curr.code) as currency_code,
                         MAX(curr.decimal_places) as decimal_places,
                         SUM(CASE WHEN fc.fee_month = "'.$month_filter.'" THEN fc.amount - fc.discount ELSE 0 END) AS current_month_due,
                         SUM(fc.amount - fc.discount) AS total_due')
               ->from('parents p')
               ->join('students s', 's.parent_id = p.parent_id')
               ->join('fee_chalan fc', 'fc.student_id = s.student_id')
               ->join('currencies curr', 'fc.currency_code = curr.code')
               ->where('s.status', 1)
               ->where('s.campus_id', $campus_id)
               ->where('fc.status', 'UnPaid')
               ->group_by('p.parent_id');

        if (!empty($filters['parent_id'])) {
            $this->db->where('p.parent_id', $filters['parent_id']);
        }

        if (!empty($filters['fee_type'])) {
            $this->db->where('fc.fee_type_id', $filters['fee_type']);
        }

        if ($limit !== null && $offset !== null) {
            $this->db->limit($limit, $offset);
        }

        $records = $this->db->get()->result();

        // Get total count
        $this->db->select('p.parent_id')
                 ->from('parents p')
                 ->join('students s', 's.parent_id = p.parent_id')
                 ->where('s.status', 1)
                 ->where('s.campus_id', $campus_id)
                 ->group_by('p.parent_id');
        $total = $this->db->count_all_results();

        $data = [];
        $no = $offset;
        foreach ($records as $record) {
            $no++;
            $current_month_due = $record->current_month_due;
            $total_due = $record->total_due;
            $prev_balance = $total_due - $current_month_due;
           // $decimal = ($record->currency_code === 'PKR') ? 0 : 2;
            $data[] = [
                'id' => $no,
                'parent_id' => $record->parent_id,
                'name' => $record->student_name,
                'f_name' => $record->f_name,
                'f_contacts' => $record->father_contact,
                'm_contacts' => $record->mother_contact,
                'unpaid_month' => $current_month_due,
                'previous_balace' => $prev_balance,
                'total_payable' => $total_due,
                'currency_code' => $record->currency_code,
                'decimal_places' => $record->decimal_places
            ];
        }

        echo json_encode([
            "draw" => intval($this->input->post('draw')),
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data
        ]);
    }

    function get_parentinfo() {
        $term = $this->input->post('term');        
        $parentssinfo = $this->db->query("SELECT * FROM parents WHERE (f_name LIKE '%".$term['term']."%')")->result_array();
        $data = array();
        
        foreach($parentssinfo as $parent) {
            $classstudents = $this->db->query("SELECT * FROM students WHERE parent_id = ".$parent['parent_id'])->row();
            if($classstudents) {
                $data[] = array("id" => $parent['parent_id'], "text" => $parent['f_name']);
            }
        }

        echo json_encode($data);
    }

    function get_studentinfo() {
        $campusid = $this->session->userdata('member_campusid');
        $term = $this->input->post('term');        
        $status = $this->input->post('status');        
        
        $studentsinfo = $this->db->query("
            SELECT * FROM students 
            WHERE (first_name LIKE '%".$term['term']."%' OR last_name LIKE '%".$term['term']."%') 
            AND status=".$status." 
            AND campus_id=".$campusid
        )->result_array();
        
        $data = array();
        foreach($studentsinfo as $student) {
            $classstudents = $this->db->query("SELECT * FROM student_class WHERE student_id = ".$student['student_id'])->row();
            $parentsInfo = $this->db->query("SELECT f_name FROM parents WHERE parent_id = ".$student['parent_id'])->row();
            
            $stdInfotxt = $student['first_name']." ".$student['last_name']." c/o ".$parentsInfo->f_name;

            if($classstudents) {
                $data[] = array("id"=>$student['student_id'], "text"=>$stdInfotxt);
            }
        }
        
        echo json_encode($data);
    }

    public function payFeeAll() {
        $user_id = $this->session->userdata('member_userid');
        $campus_id = $this->session->userdata('member_campusid');
        $parent_id = $this->input->post('parent_id');
        $date_now = date('Y-m-d H:i:s');
        $input_date = $this->input->post('datePaid');
        
        $paid_date = DateTime::createFromFormat('Y-m-d', $input_date);
        if (!$paid_date) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid date format']);
            return;
        }
        $paid_date = $paid_date->format('Y-m-d');

        // Get active student IDs for this parent
        $student_ids = $this->db->select('student_id')
            ->from('students')
            ->where('parent_id', $parent_id)
            ->where('status', 1)
            ->get()
            ->result_array();

        if (empty($student_ids)) {
            echo json_encode(['status' => 'error', 'message' => 'No active students found for this parent']);
            return;
        }

        $student_id_list = array_column($student_ids, 'student_id');

        // Get unpaid fee chalans for those students
        $this->db->where_in('student_id', $student_id_list);
        $this->db->where('status', 'unpaid');
        $fee_chalans = $this->db->get('fee_chalan')->result();

        if (empty($fee_chalans)) {
            echo json_encode(['status' => 'error', 'message' => 'No unpaid fee chalans found']);
            return;
        }

        // Update fee_chalan status
        foreach ($fee_chalans as $chalan) {
            $update_data = [
                'status' => 'paid',
                'paid_date' => $paid_date,
                'updated_date' => $date_now,
                'user_id' => $user_id,
            ];
            $this->db->where('chalan_id', $chalan->chalan_id);
            $this->db->update('fee_chalan', $update_data);
        }

        echo json_encode(['status' => 'success', 'message' => 'All unpaid fee chalans marked as paid']);
    }
}