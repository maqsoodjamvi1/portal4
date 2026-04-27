<?php
namespace App\Models\Admin;

use CodeIgniter\Model;




class Fee_model extends Model {

    public function get_parent_id($student_id, $reg_no, $campus_id) {
        if ($student_id) {
            $this->db->select('parent_id')
                     ->where(['student_id' => $student_id, 'campus_id' => $campus_id]);
            return $this->db->get('students')->row()->parent_id ?? null;
        }
        
        if ($reg_no) {
            $this->db->select('parent_id')
                     ->where(['reg_no' => $reg_no, 'campus_id' => $campus_id]);
            return $this->db->get('students')->row()->parent_id ?? null;
        }
        
        return null;
    }

    public function get_fee_data($parent_id, $campus_id, $session_id) {
        $data = [];
        
        // Get students with class info
        $data['students'] = $this->db
            ->select('s.*, c.class_name, sec.section_name')
            ->from('students s')
            ->join('student_class sc', 'sc.student_id = s.student_id')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections sec', 'sec.section_id = cs.section_id')
            ->where([
                's.parent_id' => $parent_id,
                's.campus_id' => $campus_id,
                's.status' => 1,
                'sc.session_id' => $session_id
            ])
            ->get()
            ->result();

        // Get fee summaries with COALESCE to handle NULL values
        $data['summaries'] = $this->db->query("
            SELECT 
                COALESCE(SUM(IF(status='unpaid' AND fee_type_id!=0, amount-discount, 0)), 0) AS unpaid_fees,
                COALESCE(SUM(IF(status='paid' AND DATE(paid_date)=CURDATE(), amount-discount, 0)), 0) AS paid_today,
                COALESCE(SUM(IF(status='discounted', amount-discount, 0)), 0) AS discounted_total,
                COALESCE(SUM(IF(status='unpaid' AND fee_type_id=0, amount, 0)), 0) AS unpaid_fines
            FROM fee_chalan
            WHERE student_id IN (
                SELECT student_id FROM students 
                WHERE parent_id = ? AND campus_id = ?
            )
        ", [$parent_id, $campus_id])->row();

        return $data;
    }
    // public function get_fee_data($parent_id, $campus_id, $session_id) {
    //     $data = [];
        
    //     // Get students with class info
    //     $data['students'] = $this->db
    //         ->select('s.*, c.class_name, sec.section_name')
    //         ->from('students s')
    //         ->join('student_class sc', 'sc.student_id = s.student_id')
    //         ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
    //         ->join('classes c', 'c.class_id = cs.class_id')
    //         ->join('sections sec', 'sec.section_id = cs.section_id')
    //         ->where([
    //             's.parent_id' => $parent_id,
    //             's.campus_id' => $campus_id,
    //             's.status' => 1,
    //             'sc.session_id' => $session_id
    //         ])
    //         ->get()
    //         ->result();

    //     // Get fee summaries
    //     $data['summaries'] = $this->db->query("
    //         SELECT 
    //             SUM(IF(status='unpaid' AND fee_type_id!=0, amount-discount, 0)) AS unpaid_fees,
    //             SUM(IF(status='paid' AND DATE(paid_date)=CURDATE(), amount-discount, 0)) AS paid_today,
    //             SUM(IF(status='discounted', amount-discount, 0)) AS discounted_total,
    //             SUM(IF(status='unpaid' AND fee_type_id=0, amount, 0)) AS unpaid_fines
    //         FROM fee_chalan
    //         WHERE student_id IN (
    //             SELECT student_id FROM students 
    //             WHERE parent_id = ? AND campus_id = ?
    //         )
    //     ", [$parent_id, $campus_id])->row();

    //     return $data;
    // }
}