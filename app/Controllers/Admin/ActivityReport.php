<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ActivityReport extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
    }

    // ============================================
    // TEACHER REPORT
    // ============================================
    
    public function teacherReport()
    {
        check_permission('admin-classdairy');
        
        $teacher_id = session('member_userid');
        $campus_id = session('member_campusid');
        $session_id = session('member_sessionid');
        
        // Get all terms for the globally selected session
        $terms = $this->db->table('terms_session ts')
            ->select('ts.term_session_id, t.name as term_name, ts.start_date, ts.end_date')
            ->join('terms t', 't.term_id = ts.term_id')
            ->where('ts.session_id', $session_id)
            ->where('ts.system_id', session('member_systemid'))
            ->orderBy('ts.start_date', 'ASC')
            ->get()
            ->getResult();
        
        $selected_term = $this->request->getGet('term_id') ?? ($terms[0]->term_session_id ?? 0);
        
        // Get activities for teacher
        $activities = $this->getTeacherActivities($teacher_id, $campus_id, $session_id, $selected_term);
        
        $data = [
            'terms' => $terms,
            'selected_term' => $selected_term,
            'activities' => $activities,
            'campus_id' => $campus_id,
            'session_name' => $this->getSessionName($session_id)
        ];
        
        return view('admin/activity_report/teacher_report', $data);
    }
    
    private function getTeacherActivities($teacher_id, $campus_id, $session_id, $term_id)
    {
        // Get sections where teacher teaches
        $teacherSections = $this->db->table('teacher_subjects ts')
            ->select('ss.cls_sec_id')
            ->join('section_subjects ss', 'ss.sec_sub_id = ts.sec_sub_id')
            ->join('class_section cs', 'cs.cls_sec_id = ss.cls_sec_id')
            ->where('ts.tid', $teacher_id)
            ->where('ts.status', 1)
            ->where('cs.campus_id', $campus_id)
            ->groupBy('ss.cls_sec_id')
            ->get()
            ->getResultArray();
        
        $clsSecIds = array_column($teacherSections, 'cls_sec_id');
        
        if (empty($clsSecIds)) {
            return [];
        }
        
        // Build query - CORRECTED JOINS
        $builder = $this->db->table('classdairy cd')
            ->select('
                cd.did,
                cd.date,
                cd.activities,
                cd.has_activities,
                cd.activity_media_links,
                c.class_name,
                s.section_name,
                sub.subject_name,
                ar.rating,
                ar.feedback,
                ar.review_date,
                ar.strengths,
                ar.areas_for_improvement,
                ar.recommendations
            ')
            ->join('class_section cs', 'cs.cls_sec_id = cd.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections s', 's.section_id = cs.section_id', 'left')
            ->join('section_subjects ss', 'ss.sec_sub_id = cd.sec_sub_id', 'left')
            ->join('allsubject sub', 'sub.sid = ss.subject_id', 'left')
            ->join('term_weeks tw', 'tw.term_weeks_id = cd.term_weeks_id', 'left')
            ->join('terms_session tsess', 'tsess.term_session_id = tw.term_session_id', 'left')
            ->join('activity_reviews ar', 'ar.class_dairy_id = cd.did', 'left')
            ->where('cs.campus_id', $campus_id)
            ->whereIn('cd.cls_sec_id', $clsSecIds)
            ->where('cd.has_activities', 1);
        
        // Apply term filter if selected
        if ($term_id > 0) {
            $builder->where('tsess.term_session_id', $term_id);
        }
        
        $builder->orderBy('cd.date', 'DESC');
        
        $result = $builder->get();
        
        if (!$result) {
            return [];
        }
        
        $activities = $result->getResult();
        
        // Process activities JSON
        foreach ($activities as $activity) {
            $activity->activities_list = !empty($activity->activities) ? json_decode($activity->activities, true) : [];
            $activity->media_links = !empty($activity->activity_media_links) ? json_decode($activity->activity_media_links, true) : [];
        }
        
        return $activities;
    }
    
    private function getSessionName($session_id)
    {
        $session = $this->db->table('academic_session')
            ->select('session_name')
            ->where('session_id', $session_id)
            ->get()
            ->getRow();
        
        return $session->session_name ?? 'Current Session';
    }
    
    // ============================================
    // ADD MEDIA LINK (AJAX)
    // ============================================
    
    public function addActivityMediaLink()
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid method']);
        }
        
        $did = (int) $this->request->getPost('did');
        $activityId = $this->request->getPost('activity_id');
        $mediaUrl = $this->request->getPost('media_url');
        $caption = $this->request->getPost('caption');
        
        if (!$did || !$activityId || !$mediaUrl) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Missing required fields']);
        }
        
        // Validate URL
        if (!filter_var($mediaUrl, FILTER_VALIDATE_URL)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid URL']);
        }
        
        // Detect media type
        $mediaType = $this->detectMediaType($mediaUrl);
        
        // Get current media links
        $classdairy = $this->db->table('classdairy')
            ->select('activity_media_links')
            ->where('did', $did)
            ->get()
            ->getRow();
        
        $mediaLinks = [];
        if ($classdairy && !empty($classdairy->activity_media_links)) {
            $mediaLinks = json_decode($classdairy->activity_media_links, true);
        }
        
        if (!is_array($mediaLinks)) {
            $mediaLinks = [];
        }
        
        if (!isset($mediaLinks[$activityId])) {
            $mediaLinks[$activityId] = [];
        }
        
        $mediaLinks[$activityId][] = [
            'id' => uniqid(),
            'url' => $mediaUrl,
            'type' => $mediaType,
            'caption' => $caption,
            'added_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->table('classdairy')
            ->where('did', $did)
            ->update(['activity_media_links' => json_encode($mediaLinks)]);
        
        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Media link added successfully'
        ]);
    }
    
    private function detectMediaType($url)
    {
        if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
            return 'youtube';
        }
        if (strpos($url, 'facebook.com') !== false || strpos($url, 'fb.com') !== false) {
            return 'facebook';
        }
        if (strpos($url, 'instagram.com') !== false) {
            return 'instagram';
        }
        if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $url)) {
            return 'image';
        }
        if (preg_match('/\.(mp4|webm|ogg|mov)$/i', $url)) {
            return 'video';
        }
        return 'link';
    }
    
    // ============================================
    // PRINCIPAL REPORT
    // ============================================
    
    public function principalReport()
    {
        check_permission('admin-activity-review');
        
        $campus_id = session('member_campusid');
        $session_id = session('member_sessionid');
        
        // Get all teachers
        $teachers = $this->db->table('users')
            ->select('id, first_name, last_name')
            ->where('campus_id', $campus_id)
            ->where('status', 1)
            ->orderBy('first_name', 'ASC')
            ->get()
            ->getResult();
        
        $selected_teacher = $this->request->getGet('teacher_id') ?? 0;
        $show_unrated_only = $this->request->getGet('unrated_only') ?? 0;
        
        // Get activities based on filters
        $activities = $this->getPrincipalActivities($campus_id, $session_id, $selected_teacher, $show_unrated_only);
        
        // Get all terms for info
        $terms = $this->db->table('terms_session ts')
            ->select('ts.term_session_id, t.name as term_name')
            ->join('terms t', 't.term_id = ts.term_id')
            ->where('ts.session_id', $session_id)
            ->where('ts.system_id', session('member_systemid'))
            ->orderBy('ts.start_date', 'ASC')
            ->get()
            ->getResult();
        
        $data = [
            'teachers' => $teachers,
            'selected_teacher' => $selected_teacher,
            'show_unrated_only' => $show_unrated_only,
            'activities' => $activities,
            'terms' => $terms,
            'session_name' => $this->getSessionName($session_id)
        ];
        
        return view('admin/activity_report/principal_report', $data);
    }
    
    private function getPrincipalActivities($campus_id, $session_id, $teacher_id = 0, $unrated_only = false)
    {
        $builder = $this->db->table('classdairy cd')
            ->select('
                cd.did,
                cd.date,
                cd.activities,
                cd.has_activities,
                c.class_name,
                s.section_name,
                sub.subject_name,
                u.id as teacher_id,
                u.first_name,
                u.last_name,
                ar.rating,
                ar.feedback,
                ar.review_id,
                ar.review_date,
                ar.strengths,
                ar.areas_for_improvement,
                ar.recommendations
            ')
            ->join('class_section cs', 'cs.cls_sec_id = cd.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections s', 's.section_id = cs.section_id', 'left')
            ->join('section_subjects ss', 'ss.sec_sub_id = cd.sec_sub_id', 'left')
            ->join('allsubject sub', 'sub.sid = ss.subject_id', 'left')
            ->join('teacher_subjects ts', 'ts.sec_sub_id = ss.sec_sub_id', 'left')
            ->join('users u', 'u.id = ts.tid', 'left')
            ->join('term_weeks tw', 'tw.term_weeks_id = cd.term_weeks_id', 'left')
            ->join('terms_session tsess', 'tsess.term_session_id = tw.term_session_id', 'left')
            ->join('activity_reviews ar', 'ar.class_dairy_id = cd.did', 'left')
            ->where('cs.campus_id', $campus_id)
            ->where('cd.has_activities', 1)
            ->where('tsess.session_id', $session_id)
            ->groupBy('cd.did');
        
        if ($teacher_id > 0) {
            $builder->where('ts.tid', $teacher_id);
        }
        
        if ($unrated_only) {
            $builder->where('ar.rating IS NULL');
        }
        
        $builder->orderBy('cd.date', 'DESC');
        
        $result = $builder->get();
        
        if (!$result) {
            return [];
        }
        
        $activities = $result->getResult();
        
        // Process activities JSON
        foreach ($activities as $activity) {
            $activity->activities_list = !empty($activity->activities) ? json_decode($activity->activities, true) : [];
        }
        
        return $activities;
    }
    
    // ============================================
    // SUBMIT ACTIVITY REVIEW (AJAX)
    // ============================================
    
    public function submitReview()
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid method']);
        }
        
        $did = (int) $this->request->getPost('did');
        $activityId = $this->request->getPost('activity_id');
        $rating = (float) $this->request->getPost('rating');
        $feedback = $this->request->getPost('feedback');
        $strengths = $this->request->getPost('strengths');
        $areas = $this->request->getPost('areas_for_improvement');
        $recommendations = $this->request->getPost('recommendations');
        
        // Check if review exists
        $existing = $this->db->table('activity_reviews')
            ->where('class_dairy_id', $did)
            ->where('activity_id', $activityId)
            ->where('principal_id', session('member_userid'))
            ->get()
            ->getRow();
        
        $data = [
            'class_dairy_id' => $did,
            'activity_id' => $activityId,
            'principal_id' => session('member_userid'),
            'campus_id' => session('member_campusid'),
            'rating' => $rating,
            'feedback' => $feedback,
            'strengths' => $strengths,
            'areas_for_improvement' => $areas,
            'recommendations' => $recommendations,
            'status' => 'published',
            'review_date' => date('Y-m-d H:i:s')
        ];
        
        if ($existing) {
            $this->db->table('activity_reviews')
                ->where('review_id', $existing->review_id)
                ->update($data);
        } else {
            $this->db->table('activity_reviews')->insert($data);
        }
        
        // Also update JSON in classdairy
        $classdairy = $this->db->table('classdairy')
            ->select('activity_review')
            ->where('did', $did)
            ->get()
            ->getRow();
        
        $reviews = [];
        if ($classdairy && !empty($classdairy->activity_review)) {
            $reviews = json_decode($classdairy->activity_review, true);
        }
        
        if (!is_array($reviews)) {
            $reviews = [];
        }
        
        $reviews[$activityId] = [
            'rating' => $rating,
            'feedback' => $feedback,
            'reviewed_by' => session('member_userid'),
            'reviewed_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->table('classdairy')
            ->where('did', $did)
            ->update(['activity_review' => json_encode($reviews)]);
        
        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Review submitted successfully'
        ]);
    }
}