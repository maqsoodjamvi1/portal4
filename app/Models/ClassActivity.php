<?php
namespace App\Models;

use CodeIgniter\Model;

class ClassActivity extends Model
{
    protected $table = 'class_activities';
    protected $primaryKey = 'activity_id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'campus_id', 'term_session_id', 'cls_sec_id', 'sec_sub_id',
        'activity_title', 'activity_type', 'activity_date', 'duration_minutes',
        'description', 'learning_objectives', 'resources_used', 'assessment_method',
        'student_participation', 'notes', 'attachment_path', 'status', 'user_id'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_date';
    protected $updatedField = 'updated_date';
    
    /**
     * Get activities with related data
     */
    public function getActivities($filters = [])
    {
        $builder = $this->db->table('class_activities ca')
            ->select('ca.*, t.name as term_name, ts.start_date as term_start, ts.end_date as term_end,
                      c.class_name, s.section_name, sub.subject_name, u.first_name, u.last_name')
            ->join('terms_session ts', 'ts.term_session_id = ca.term_session_id')
            ->join('terms t', 't.term_id = ts.term_id')
            ->join('class_section cls', 'cls.cls_sec_id = ca.cls_sec_id')
            ->join('classes c', 'c.class_id = cls.class_id')
            ->join('sections s', 's.section_id = cls.section_id')
            ->join('section_subjects ss', 'ss.sec_sub_id = ca.sec_sub_id')
            ->join('a_subject sub', 'sub.sid = ss.subject_id')
            ->join('users u', 'u.id = ca.user_id')
            ->where('ca.campus_id', session()->get('member_campusid'));
        
        // Apply filters
        if (!empty($filters['term_session_id'])) {
            $builder->where('ca.term_session_id', $filters['term_session_id']);
        }
        if (!empty($filters['cls_sec_id'])) {
            $builder->where('ca.cls_sec_id', $filters['cls_sec_id']);
        }
        if (!empty($filters['sec_sub_id'])) {
            $builder->where('ca.sec_sub_id', $filters['sec_sub_id']);
        }
        if (!empty($filters['activity_type'])) {
            $builder->where('ca.activity_type', $filters['activity_type']);
        }
        if (!empty($filters['start_date'])) {
            $builder->where('ca.activity_date >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $builder->where('ca.activity_date <=', $filters['end_date']);
        }
        
        return $builder->orderBy('ca.activity_date', 'DESC')
                      ->orderBy('ca.created_date', 'DESC')
                      ->get()
                      ->getResult();
    }
    
    /**
     * Get activity by ID with all related data
     */
    public function getActivityWithDetails($activity_id)
    {
        return $this->db->table('class_activities ca')
            ->select('ca.*, t.name as term_name, ts.start_date as term_start, ts.end_date as term_end,
                      c.class_name, s.section_name, sub.subject_name, u.first_name, u.last_name')
            ->join('terms_session ts', 'ts.term_session_id = ca.term_session_id')
            ->join('terms t', 't.term_id = ts.term_id')
            ->join('class_section cls', 'cls.cls_sec_id = ca.cls_sec_id')
            ->join('classes c', 'c.class_id = cls.class_id')
            ->join('sections s', 's.section_id = cls.section_id')
            ->join('section_subjects ss', 'ss.sec_sub_id = ca.sec_sub_id')
            ->join('a_subject sub', 'sub.sid = ss.subject_id')
            ->join('users u', 'u.id = ca.user_id')
            ->where('ca.activity_id', $activity_id)
            ->get()
            ->getRow();
    }
}