<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Recordings extends BaseController
{
    protected $db;
    protected $session;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'server_helper']);
    }

    /**
     * Main dashboard for reviewing recordings
     */
   public function index()
{
    check_permission('teacher-review-recordings');
    
    $campus_id = session('member_campusid');
    $teacher_id = session('member_userid');
    $schoolinfo = getSchoolInfo();
    $userRoles = currentUserRoles();
    $isTeacher = in_array(5, $userRoles);
    
    // Get sections based on user role
    if ($isTeacher) {
        $sections = teacherSubjectSections();
    } else {
        $sections = getAllClassSection();
    }
    
    // Get pending counts WITHOUT teacher filter for display
    $pendingAudio = $this->db->table('student_audio_recordings ar')
        ->join('students s', 's.student_id = ar.student_id')
        ->where('ar.status', 'pending')
        ->where('s.campus_id', $campus_id)
        ->countAllResults();
    
    $pendingVideo = $this->db->table('student_video_recordings vr')
        ->join('students s', 's.student_id = vr.student_id')
        ->where('vr.status', 'pending')
        ->where('s.campus_id', $campus_id)
        ->countAllResults();
    
    $data = [
        'title' => 'Student Recordings Review',
        'schoolinfo' => $schoolinfo,
        'pendingAudio' => $pendingAudio ?: 0,
        'pendingVideo' => $pendingVideo ?: 0,
        'recentAudio' => $this->getRecentSubmissions('audio', $teacher_id, $campus_id),
        'recentVideo' => $this->getRecentSubmissions('video', $teacher_id, $campus_id),
        'statistics' => $this->getStatistics($teacher_id, $campus_id),
        'sections' => $sections,
        'isTeacher' => $isTeacher
    ];
    
    return view('admin/recordings/index', $data);
}

    /**
     * Get pending counts for all sections (for dashboard cards)
     */
    /**
 * Get pending counts for all sections (for dashboard cards)
 */
public function getPendingCounts()
{
    check_permission('teacher-review-recordings');
    
    $campus_id = session('member_campusid');
    
    // Get counts for audio recordings - WITHOUT teacher filter
    $audioCounts = $this->db->table('student_audio_recordings ar')
        ->select('ar.cls_sec_id, COUNT(*) as count')
        ->join('students s', 's.student_id = ar.student_id')
        ->where('ar.status', 'pending')
        ->where('s.campus_id', $campus_id)
        ->groupBy('ar.cls_sec_id')
        ->get()
        ->getResultArray();
    
    // Get counts for video recordings - WITHOUT teacher filter
    $videoCounts = $this->db->table('student_video_recordings vr')
        ->select('vr.cls_sec_id, COUNT(*) as count')
        ->join('students s', 's.student_id = vr.student_id')
        ->where('vr.status', 'pending')
        ->where('s.campus_id', $campus_id)
        ->groupBy('vr.cls_sec_id')
        ->get()
        ->getResultArray();
    
    $result = [];
    foreach ($audioCounts as $ac) {
        $result[$ac['cls_sec_id']]['audio'] = (int)$ac['count'];
    }
    foreach ($videoCounts as $vc) {
        if (!isset($result[$vc['cls_sec_id']])) {
            $result[$vc['cls_sec_id']]['audio'] = 0;
        }
        $result[$vc['cls_sec_id']]['video'] = (int)$vc['count'];
    }
    
    // Also return total counts for debugging
    $result['_debug'] = [
        'total_audio_pending' => array_sum(array_column($audioCounts, 'count')),
        'total_video_pending' => array_sum(array_column($videoCounts, 'count')),
        'audio_records' => $audioCounts,
        'video_records' => $videoCounts
    ];
    
    return $this->response->setJSON($result);
}
    /**
     * Get subjects for a section (AJAX)
     * Returns HTML for subject cards
     */
   /**
 * Get subjects for a section (AJAX)
 * Returns HTML for subject cards with pending counts
 */
/**
 * Get subjects for a section (AJAX)
 * Returns HTML for subject cards with pending counts
 */
public function getSubjectsBySection()
{
    check_permission('teacher-review-recordings');
    
    $clsSecId = $this->request->getPost('cls_sec_id');
    $campus_id = session('member_campusid');
    $teacher_id = session('member_userid');
    $userRoles = currentUserRoles();
    $isTeacher = in_array(5, $userRoles);
    
    if (!$clsSecId) {
        return $this->response->setJSON(['html' => '<div class="text-center py-4 text-danger">Invalid section</div>']);
    }
    
    // Get subjects for this section
    $db = \Config\Database::connect();
    
    if ($isTeacher) {
        // Teacher: Get subjects they teach
        $subjects = $db->table('teacher_subjects ts')
            ->select('DISTINCT ss.sec_sub_id, sub.subject_name')
            ->join('section_subjects ss', 'ss.sec_sub_id = ts.sec_sub_id')
            ->join('allsubject sub', 'sub.sid = ss.subject_id')
            ->where('ts.tid', $teacher_id)
            ->where('ts.status', 1)
            ->where('ss.cls_sec_id', $clsSecId)
            ->where('ss.status', 1)
            ->orderBy('sub.subject_name', 'ASC')
            ->get()
            ->getResult();
    } else {
        // Admin: Get all subjects
        $subjects = $db->table('section_subjects ss')
            ->select('ss.sec_sub_id, sub.subject_name')
            ->join('allsubject sub', 'sub.sid = ss.subject_id')
            ->where('ss.cls_sec_id', $clsSecId)
            ->where('ss.status', 1)
            ->orderBy('sub.subject_name', 'ASC')
            ->get()
            ->getResult();
    }
    
    if (empty($subjects)) {
        $html = '<div class="text-center py-4 text-muted">No subjects found in this section</div>';
        return $this->response->setJSON(['html' => $html]);
    }
    
    // Get pending counts for audio and video by sec_sub_id
    $audioCounts = $db->table('student_audio_recordings ar')
        ->select('ar.sec_sub_id, COUNT(*) as count')
        ->join('students s', 's.student_id = ar.student_id')
        ->where('ar.status', 'pending')
        ->where('ar.cls_sec_id', $clsSecId)
        ->where('s.campus_id', $campus_id)
        ->groupBy('ar.sec_sub_id')
        ->get()
        ->getResultArray();
    
    $videoCounts = $db->table('student_video_recordings vr')
        ->select('vr.sec_sub_id, COUNT(*) as count')
        ->join('students s', 's.student_id = vr.student_id')
        ->where('vr.status', 'pending')
        ->where('vr.cls_sec_id', $clsSecId)
        ->where('s.campus_id', $campus_id)
        ->groupBy('vr.sec_sub_id')
        ->get()
        ->getResultArray();
    
    // Create lookup arrays for counts
    $audioCountMap = [];
    foreach ($audioCounts as $ac) {
        $audioCountMap[$ac['sec_sub_id']] = $ac['count'];
    }
    
    $videoCountMap = [];
    foreach ($videoCounts as $vc) {
        $videoCountMap[$vc['sec_sub_id']] = $vc['count'];
    }
    
    // Build HTML - include ALL subjects but JavaScript will hide zero-count ones
    $html = '';
    foreach ($subjects as $subject) {
        $secSubId = is_object($subject) ? $subject->sec_sub_id : $subject['sec_sub_id'];
        $subjectName = is_object($subject) ? $subject->subject_name : $subject['subject_name'];
        
        $audioCount = $audioCountMap[$secSubId] ?? 0;
        $videoCount = $videoCountMap[$secSubId] ?? 0;
        
        $html .= '<div class="subject-card" data-sec-sub-id="' . $secSubId . '">';
        $html .= '<div class="subject-name">' . esc($subjectName) . '</div>';
        $html .= '<div class="subject-stats">';
        $html .= '<span class="stat-badge audio"><i class="fa fa-microphone"></i> ' . $audioCount . '</span>';
        $html .= '<span class="stat-badge video"><i class="fa fa-video"></i> ' . $videoCount . '</span>';
        $html .= '</div></div>';
    }
    
    return $this->response->setJSON(['html' => $html]);
}
    /**
     * Get filtered pending audio submissions (AJAX)
     */
 
public function getFilteredPendingAudio()
{
    check_permission('teacher-review-recordings');
    
    $campus_id = session('member_campusid');
    $clsSecId = $this->request->getGet('cls_sec_id');
    $secSubId = $this->request->getGet('sec_sub_id');
    
    $query = $this->db->table('student_audio_recordings ar')
        ->select('ar.*, s.first_name, s.last_name, s.reg_no, s.profile_photo, 
                  sub.subject_name, cd.date as diary_date')
        ->join('students s', 's.student_id = ar.student_id')
        ->join('classdairy cd', 'cd.did = ar.class_dairy_id', 'left')
        ->join('section_subjects ss', 'ss.sec_sub_id = ar.sec_sub_id', 'left')
        ->join('allsubject sub', 'sub.sid = ss.subject_id', 'left')
        ->where('ar.status', 'pending')
        ->where('s.campus_id', $campus_id);
    
    if (!empty($clsSecId) && $clsSecId != 'null') {
        $query->where('ar.cls_sec_id', $clsSecId);
    }
    
    if (!empty($secSubId) && $secSubId != 'null') {
        $query->where('ar.sec_sub_id', $secSubId);
    }
    
    $result = $query->orderBy('ar.created_date', 'ASC')->get();
    
    if (!$result) {
        return $this->response->setJSON([]);
    }
    
    $recordings = $result->getResultArray();
    
    // Add full photo URL for each recording
    foreach ($recordings as &$rec) {
        $rec['photo_url'] = $this->getStudentPhotoUrl($rec['profile_photo'] ?? '');
    }
    
    return $this->response->setJSON($recordings);
}

public function getFilteredPendingVideo()
{
    check_permission('teacher-review-recordings');
    
    $campus_id = session('member_campusid');
    $clsSecId = $this->request->getGet('cls_sec_id');
    $secSubId = $this->request->getGet('sec_sub_id');
    
    $query = $this->db->table('student_video_recordings vr')
        ->select('vr.*, s.first_name, s.last_name, s.reg_no, s.profile_photo, 
                  sub.subject_name, cd.date as diary_date')
        ->join('students s', 's.student_id = vr.student_id')
        ->join('classdairy cd', 'cd.did = vr.class_dairy_id', 'left')
        ->join('section_subjects ss', 'ss.sec_sub_id = vr.sec_sub_id', 'left')
        ->join('allsubject sub', 'sub.sid = ss.subject_id', 'left')
        ->where('vr.status', 'pending')
        ->where('s.campus_id', $campus_id);
    
    if (!empty($clsSecId) && $clsSecId != 'null') {
        $query->where('vr.cls_sec_id', $clsSecId);
    }
    
    if (!empty($secSubId) && $secSubId != 'null') {
        $query->where('vr.sec_sub_id', $secSubId);
    }
    
    $result = $query->orderBy('vr.created_date', 'ASC')->get();
    
    if (!$result) {
        return $this->response->setJSON([]);
    }
    
    $recordings = $result->getResultArray();
    
    // Add full photo URL for each recording
    foreach ($recordings as &$rec) {
        $rec['photo_url'] = $this->getStudentPhotoUrl($rec['profile_photo'] ?? '');
    }
    
    return $this->response->setJSON($recordings);
}

/**
 * Get student photo URL from various possible locations
 */
private function getStudentPhotoUrl($photoFile)
{
    if (empty($photoFile)) {
        return base_url('assets/img/avatar-student.png');
    }
    
    $photoFile = ltrim($photoFile, '/');
    
    // Check different possible directories
    $directories = ['uploads/', 'student_photos/', 'system-logo/'];
    
    foreach ($directories as $dir) {
        $fullPath = FCPATH . $dir . $photoFile;
        if (file_exists($fullPath)) {
            return base_url($dir . $photoFile);
        }
    }
    
    // Also check if the file path itself is complete
    $fullPath = FCPATH . $photoFile;
    if (file_exists($fullPath)) {
        return base_url($photoFile);
    }
    
    return base_url('assets/img/avatar-student.png');
}

    /**
     * Review audio recording
     */
    public function reviewAudio()
    {
        check_permission('teacher-review-recordings');
        
        $recording_id = (int) $this->request->getPost('recording_id');
        $status = $this->request->getPost('status');
        $feedback = $this->request->getPost('feedback');
        $rating = (int) $this->request->getPost('rating');
        $teacher_id = session('member_userid');

        $updateData = [
            'status' => $status,
            'teacher_feedback' => $feedback,
            'rating' => $rating,
            'reviewed_by' => $teacher_id,
            'reviewed_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $updated = $this->db->table('student_audio_recordings')
            ->where('recording_id', $recording_id)
            ->update($updateData);

        if ($updated) {
            return $this->response->setJSON(['success' => true, 'message' => 'Recording reviewed successfully']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to update recording']);
    }

    /**
     * Review video recording
     */
    public function reviewVideo()
    {
        check_permission('teacher-review-recordings');
        
        $recording_id = (int) $this->request->getPost('recording_id');
        $status = $this->request->getPost('status');
        $feedback = $this->request->getPost('feedback');
        $rating = (int) $this->request->getPost('rating');
        $teacher_id = session('member_userid');

        $updateData = [
            'status' => $status,
            'teacher_feedback' => $feedback,
            'rating' => $rating,
            'reviewed_by' => $teacher_id,
            'reviewed_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $updated = $this->db->table('student_video_recordings')
            ->where('recording_id', $recording_id)
            ->update($updateData);

        if ($updated) {
            return $this->response->setJSON(['success' => true, 'message' => 'Recording reviewed successfully']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to update recording']);
    }

    // ========== Private Helper Methods ==========

    private function getPendingCount($type, $teacher_id, $campus_id)
    {
        if ($type === 'audio') {
            $result = $this->db->table('student_audio_recordings ar')
                ->join('students s', 's.student_id = ar.student_id')
                ->join('teacher_subjects ts', 'ts.sec_sub_id = ar.sec_sub_id AND ts.status = 1', 'inner')
                ->where('ar.status', 'pending')
                ->where('ts.tid', $teacher_id)
                ->where('s.campus_id', $campus_id)
                ->countAllResults();
            
            return $result ?: 0;
        }
        
        $result = $this->db->table('student_video_recordings vr')
            ->join('students s', 's.student_id = vr.student_id')
            ->join('teacher_subjects ts', 'ts.sec_sub_id = vr.sec_sub_id AND ts.status = 1', 'inner')
            ->where('vr.status', 'pending')
            ->where('ts.tid', $teacher_id)
            ->where('s.campus_id', $campus_id)
            ->countAllResults();
        
        return $result ?: 0;
    }

    private function getRecentSubmissions($type, $teacher_id, $campus_id)
    {
        if ($type === 'audio') {
            $result = $this->db->table('student_audio_recordings ar')
                ->select('ar.*, s.first_name, s.last_name, s.reg_no, s.profile_photo, sub.subject_name')
                ->join('students s', 's.student_id = ar.student_id')
                ->join('classdairy cd', 'cd.did = ar.class_dairy_id', 'left')
                ->join('section_subjects ss', 'ss.sec_sub_id = ar.sec_sub_id', 'left')
                ->join('allsubject sub', 'sub.sid = ss.subject_id', 'left')
                ->join('teacher_subjects ts', 'ts.sec_sub_id = ar.sec_sub_id AND ts.status = 1', 'inner')
                ->where('ts.tid', $teacher_id)
                ->where('s.campus_id', $campus_id)
                ->orderBy('ar.created_date', 'DESC')
                ->limit(10)
                ->get();
            
            return ($result && $result->getResultArray()) ? $result->getResultArray() : [];
        }
        
        $result = $this->db->table('student_video_recordings vr')
            ->select('vr.*, s.first_name, s.last_name, s.reg_no, s.profile_photo, sub.subject_name')
            ->join('students s', 's.student_id = vr.student_id')
            ->join('classdairy cd', 'cd.did = vr.class_dairy_id', 'left')
            ->join('section_subjects ss', 'ss.sec_sub_id = vr.sec_sub_id', 'left')
            ->join('allsubject sub', 'sub.sid = ss.subject_id', 'left')
            ->join('teacher_subjects ts', 'ts.sec_sub_id = vr.sec_sub_id AND ts.status = 1', 'inner')
            ->where('ts.tid', $teacher_id)
            ->where('s.campus_id', $campus_id)
            ->orderBy('vr.created_date', 'DESC')
            ->limit(10)
            ->get();
        
        return ($result && $result->getResultArray()) ? $result->getResultArray() : [];
    }

    private function getStatistics($teacher_id, $campus_id)
    {
        $thisMonth = date('Y-m-01');
        
        $audioReviewed = $this->db->table('student_audio_recordings ar')
            ->join('students s', 's.student_id = ar.student_id')
            ->join('teacher_subjects ts', 'ts.sec_sub_id = ar.sec_sub_id AND ts.status = 1', 'inner')
            ->where('ts.tid', $teacher_id)
            ->where('s.campus_id', $campus_id)
            ->where('ar.reviewed_date >=', $thisMonth)
            ->countAllResults();
            
        $videoReviewed = $this->db->table('student_video_recordings vr')
            ->join('students s', 's.student_id = vr.student_id')
            ->join('teacher_subjects ts', 'ts.sec_sub_id = vr.sec_sub_id AND ts.status = 1', 'inner')
            ->where('ts.tid', $teacher_id)
            ->where('s.campus_id', $campus_id)
            ->where('vr.reviewed_date >=', $thisMonth)
            ->countAllResults();
        
        $avgAudioQuery = $this->db->table('student_audio_recordings ar')
            ->select('AVG(ar.rating) as avg_rating')
            ->join('students s', 's.student_id = ar.student_id')
            ->join('teacher_subjects ts', 'ts.sec_sub_id = ar.sec_sub_id AND ts.status = 1', 'inner')
            ->where('ts.tid', $teacher_id)
            ->where('s.campus_id', $campus_id)
            ->where('ar.rating IS NOT NULL')
            ->get();
        
        $avgAudio = ($avgAudioQuery && $avgAudioQuery->getRowArray()) ? $avgAudioQuery->getRowArray() : ['avg_rating' => 0];
        
        $avgVideoQuery = $this->db->table('student_video_recordings vr')
            ->select('AVG(vr.rating) as avg_rating')
            ->join('students s', 's.student_id = vr.student_id')
            ->join('teacher_subjects ts', 'ts.sec_sub_id = vr.sec_sub_id AND ts.status = 1', 'inner')
            ->where('ts.tid', $teacher_id)
            ->where('s.campus_id', $campus_id)
            ->where('vr.rating IS NOT NULL')
            ->get();
        
        $avgVideo = ($avgVideoQuery && $avgVideoQuery->getRowArray()) ? $avgVideoQuery->getRowArray() : ['avg_rating' => 0];
            
        return [
            'total_reviewed_this_month' => ($audioReviewed ?: 0) + ($videoReviewed ?: 0),
            'avg_audio_rating' => round($avgAudio['avg_rating'] ?? 0, 1),
            'avg_video_rating' => round($avgVideo['avg_rating'] ?? 0, 1)
        ];
    }

    /**
 * View student progress list
 */
public function studentProgress()
{
    check_permission('teacher-view-progress');
    
    $campus_id = session('member_campusid');
    $teacher_id = session('member_userid');
    $schoolinfo = getSchoolInfo();
    
    // Get all students with pending submissions for this teacher's subjects
    $students = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.reg_no, s.profile_photo,
                  c.class_name, sec.section_name,
                  COUNT(DISTINCT ar.recording_id) as pending_audio,
                  COUNT(DISTINCT vr.recording_id) as pending_video')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.status = 1')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->join('student_audio_recordings ar', 'ar.student_id = s.student_id AND ar.status = "pending"', 'left')
        ->join('student_video_recordings vr', 'vr.student_id = s.student_id AND vr.status = "pending"', 'left')
        ->join('teacher_subjects ts', 'ts.sec_sub_id = ar.sec_sub_id AND ts.status = 1', 'left')
        ->where('s.campus_id', $campus_id)
        ->where('s.status', '1')
        ->groupBy('s.student_id')
        ->having('pending_audio > 0 OR pending_video > 0')
        ->get()
        ->getResultArray();
    
    $data = [
        'title' => 'Student Progress',
        'schoolinfo' => $schoolinfo,
        'students' => $students
    ];
    
    return view('admin/recordings/student_progress', $data);
}

/**
 * View individual student progress details
 */
public function studentDetails($studentId)
{
    check_permission('teacher-view-progress');
    
    $student_id = (int) $studentId;
    $campus_id = session('member_campusid');
    $schoolinfo = getSchoolInfo();
    
    // Get student info
    $student = $this->db->table('students s')
        ->select('s.*, c.class_name, sec.section_name')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.status = 1')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where('s.student_id', $student_id)
        ->where('s.campus_id', $campus_id)
        ->get()
        ->getRowArray();
    
    if (!$student) {
        return redirect()->to('/admin/recordings/student-progress')->with('error', 'Student not found');
    }
    
    // Get audio recordings
    $audioRecordings = $this->db->table('student_audio_recordings ar')
        ->select('ar.*, sub.subject_name, cd.date as diary_date')
        ->join('classdairy cd', 'cd.did = ar.class_dairy_id', 'left')
        ->join('section_subjects ss', 'ss.sec_sub_id = ar.sec_sub_id', 'left')
        ->join('allsubject sub', 'sub.sid = ss.subject_id', 'left')
        ->where('ar.student_id', $student_id)
        ->orderBy('ar.created_date', 'DESC')
        ->get()
        ->getResultArray();
    
    // Get video recordings
    $videoRecordings = $this->db->table('student_video_recordings vr')
        ->select('vr.*, sub.subject_name, cd.date as diary_date')
        ->join('classdairy cd', 'cd.did = vr.class_dairy_id', 'left')
        ->join('section_subjects ss', 'ss.sec_sub_id = vr.sec_sub_id', 'left')
        ->join('allsubject sub', 'sub.sid = ss.subject_id', 'left')
        ->where('vr.student_id', $student_id)
        ->orderBy('vr.created_date', 'DESC')
        ->get()
        ->getResultArray();
    
    $data = [
        'title' => 'Student Progress - ' . ($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''),
        'schoolinfo' => $schoolinfo,
        'student' => $student,
        'audioRecordings' => $audioRecordings,
        'videoRecordings' => $videoRecordings
    ];
    
    return view('admin/recordings/student_details', $data);
}
}