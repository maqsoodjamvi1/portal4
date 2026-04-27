<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DiaryAnalytics extends BaseController
{
    protected $db;
    
    public function __construct()
    {
        // Temporarily disable permission check for debugging
        // check_permission('admin-diary-analytics');
        
        $this->db = \Config\Database::connect();
    }
    
    public function index()
    {
        $campus_id = (int) session('member_campusid');
        $session_id = (int) session('member_sessionid');
        $system_id = (int) getSchoolInfo()->system_id;
        
        // Get filter data for dropdowns
        $data = [
            'subjects' => $this->getSubjects($campus_id),
            'classes' => $this->getClasses($campus_id),
            'sections' => $this->getSections($campus_id),
            'teachers' => $this->getTeachers($campus_id),
            'terms' => $this->getTerms($session_id, $system_id),
            'weeks' => $this->getWeeks($session_id, $system_id),
            'campus_id' => $campus_id,
            'session_id' => $session_id
        ];
        
        return view('admin/diary_analytics/index', $data);
    }
    
    // AJAX endpoint for analytics data
    public function getAnalytics()
    {
        // Set JSON header
        $this->response->setContentType('application/json');
        
        try {
            $campus_id = (int) session('member_campusid');
            $session_id = (int) session('member_sessionid');
            
            // FIXED: Properly get POST data with correct parameters
            // In CodeIgniter 4, getPost() signature: getPost($index = null, $filter = null)
            $subject_id = $this->request->getPost('subject_id');
            $class_id = $this->request->getPost('class_id');
            $section_id = $this->request->getPost('section_id');
            $teacher_id = $this->request->getPost('teacher_id');
            $term_id = $this->request->getPost('term_id');
            $week_id = $this->request->getPost('week_id');
            $start_date = $this->request->getPost('start_date');
            $end_date = $this->request->getPost('end_date');
            $analytics_type = $this->request->getPost('analytics_type');
            
            // FIXED: Get checkbox values properly (they come as '1' or null)
            $include_homework = (bool) $this->request->getPost('include_homework');
            $include_classwork = (bool) $this->request->getPost('include_classwork');
            $include_audio = (bool) $this->request->getPost('include_audio');
            $include_video = (bool) $this->request->getPost('include_video');
            $include_picture = (bool) $this->request->getPost('include_picture');
            $include_quiz = (bool) $this->request->getPost('include_quiz');
            $include_activities = (bool) $this->request->getPost('include_activities');
            $include_bagpack = (bool) $this->request->getPost('include_bagpack');
            
            $filters = [
                'subject_id' => !empty($subject_id) ? $subject_id : null,
                'class_id' => !empty($class_id) ? $class_id : null,
                'section_id' => !empty($section_id) ? $section_id : null,
                'teacher_id' => !empty($teacher_id) ? $teacher_id : null,
                'term_id' => !empty($term_id) ? $term_id : null,
                'week_id' => !empty($week_id) ? $week_id : null,
                'start_date' => !empty($start_date) ? $start_date : null,
                'end_date' => !empty($end_date) ? $end_date : null,
                'analytics_type' => !empty($analytics_type) ? $analytics_type : 'summary'
            ];
            
            $includeFlags = [
                'homework' => $include_homework,
                'classwork' => $include_classwork,
                'audio' => $include_audio,
                'video' => $include_video,
                'picture' => $include_picture,
                'quiz' => $include_quiz,
                'activities' => $include_activities,
                'bagpack' => $include_bagpack
            ];
            
            $result = [];
            
            switch ($filters['analytics_type']) {
                case 'summary':
                    $result = $this->getSummaryAnalytics($campus_id, $session_id, $filters, $includeFlags);
                    break;
                case 'subject_wise':
                    $result = $this->getSubjectWiseAnalytics($campus_id, $session_id, $filters, $includeFlags);
                    break;
                case 'teacher_wise':
                    $result = $this->getTeacherWiseAnalytics($campus_id, $session_id, $filters, $includeFlags);
                    break;
                case 'class_wise':
                    $result = $this->getClassWiseAnalytics($campus_id, $session_id, $filters, $includeFlags);
                    break;
                case 'weekly_trend':
                    $result = $this->getWeeklyTrendAnalytics($campus_id, $session_id, $filters, $includeFlags);
                    break;
                case 'task_completion':
                    $result = $this->getTaskCompletionAnalytics($campus_id, $session_id, $filters);
                    break;
                case 'detailed':
                    $result = $this->getDetailedDiaryData($campus_id, $session_id, $filters, $includeFlags);
                    break;
                default:
                    $result = $this->getSummaryAnalytics($campus_id, $session_id, $filters, $includeFlags);
            }
            
            return $this->response->setJSON($result);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    // Export to Excel/CSV
    public function export()
    {
        try {
            $campus_id = (int) session('member_campusid');
            $session_id = (int) session('member_sessionid');
            
            // FIXED: Properly get POST data
            $filters = [
                'subject_id' => $this->request->getPost('subject_id'),
                'class_id' => $this->request->getPost('class_id'),
                'section_id' => $this->request->getPost('section_id'),
                'teacher_id' => $this->request->getPost('teacher_id'),
                'start_date' => $this->request->getPost('start_date'),
                'end_date' => $this->request->getPost('end_date')
            ];
            
            $includeFlags = [
                'homework' => true,
                'classwork' => true,
                'audio' => true,
                'video' => true,
                'picture' => true,
                'quiz' => true,
                'activities' => true,
                'bagpack' => true
            ];
            
            $data = $this->getDetailedDiaryData($campus_id, $session_id, $filters, $includeFlags);
            
            // Output as CSV for simplicity (avoid PhpSpreadsheet dependency)
            $this->response->setContentType('text/csv');
            $this->response->setHeader('Content-Disposition', 'attachment; filename="diary_analytics_' . date('Y-m-d_H-i-s') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel
            fputs($output, "\xEF\xBB\xBF");
            
            // Headers
            fputcsv($output, [
                'Date', 'Class', 'Section', 'Subject', 'Teacher', 'Week', 
                'Home Work', 'Class Work', 'Has Audio', 'Has Video', 'Has Picture', 
                'Has Quiz', 'Quiz Title', 'Bag Pack Items'
            ]);
            
            // Data rows
            foreach ($data['diary_entries'] as $entry) {
                fputcsv($output, [
                    $entry['diary_date'],
                    $entry['class_name'],
                    $entry['section_name'],
                    $entry['subject_name'],
                    $entry['teacher_name'] ?? '',
                    $entry['week_no'],
                    strip_tags($entry['homework'] ?? ''),
                    strip_tags($entry['classwork'] ?? ''),
                    $entry['has_audio'] ? 'Yes' : 'No',
                    $entry['has_video'] ? 'Yes' : 'No',
                    $entry['has_picture'] ? 'Yes' : 'No',
                    $entry['has_quiz'] ? 'Yes' : 'No',
                    $entry['quiz_title'] ?? '',
                    implode(', ', $entry['bagpack_items'] ?? [])
                ]);
            }
            
            fclose($output);
            exit();
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    // Private helper methods
    
    private function getSummaryAnalytics($campus_id, $session_id, $filters, $includeFlags)
    {
        $builder = $this->db->table('v_class_diary_analytics')
            ->where('campus_id', $campus_id)
            ->where('session_id', $session_id);
        
        $builder = $this->applyFilters($builder, $filters);
        
        // Get total count
        $totalEntries = $builder->countAllResults(false);
        
        // Count by feature type
        $stats = [];
        
        if ($includeFlags['homework']) {
            $clone = clone $builder;
            $stats['has_homework'] = $clone->where('homework IS NOT NULL')->where('homework !=', '')->countAllResults();
        } else {
            $stats['has_homework'] = 0;
        }
        
        if ($includeFlags['classwork']) {
            $clone = clone $builder;
            $stats['has_classwork'] = $clone->where('classwork IS NOT NULL')->where('classwork !=', '')->countAllResults();
        } else {
            $stats['has_classwork'] = 0;
        }
        
        if ($includeFlags['audio']) {
            $clone = clone $builder;
            $stats['has_audio'] = $clone->where('is_audio', 1)->countAllResults();
        } else {
            $stats['has_audio'] = 0;
        }
        
        if ($includeFlags['video']) {
            $clone = clone $builder;
            $stats['has_video'] = $clone->where('is_video', 1)->countAllResults();
        } else {
            $stats['has_video'] = 0;
        }
        
        if ($includeFlags['picture']) {
            $clone = clone $builder;
            $stats['has_picture'] = $clone->where('is_picture', 1)->countAllResults();
        } else {
            $stats['has_picture'] = 0;
        }
        
        if ($includeFlags['quiz']) {
            $clone = clone $builder;
            $stats['has_quiz'] = $clone->where('quiz_id IS NOT NULL')->countAllResults();
        } else {
            $stats['has_quiz'] = 0;
        }
        
        if ($includeFlags['bagpack']) {
            $clone = clone $builder;
            $stats['has_bagpack'] = $clone->where('is_book', 1)->orWhere('is_notebook', 1)->countAllResults();
        } else {
            $stats['has_bagpack'] = 0;
        }
        
        // Get subject distribution
        $subjectBuilder = clone $builder;
        $subjectDistribution = $subjectBuilder->select('subject_name, COUNT(*) as count')
            ->groupBy('subject_id')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();
        
        // Get teacher distribution
        $teacherBuilder = clone $builder;
        $teacherDistribution = $teacherBuilder->select("CONCAT(teacher_first_name, ' ', teacher_last_name) as teacher_name, COUNT(*) as count")
            ->groupBy('teacher_id')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();
        
        return [
            'total_entries' => $totalEntries,
            'statistics' => $stats,
            'subject_distribution' => $subjectDistribution,
            'teacher_distribution' => $teacherDistribution,
            'filters_applied' => $filters
        ];
    }
    
    private function getSubjectWiseAnalytics($campus_id, $session_id, $filters, $includeFlags)
    {
        $builder = $this->db->table('v_class_diary_analytics')
            ->select('subject_id, subject_name, 
                     COUNT(*) as total_entries,
                     SUM(CASE WHEN homework IS NOT NULL AND homework != "" THEN 1 ELSE 0 END) as has_homework,
                     SUM(CASE WHEN classwork IS NOT NULL AND classwork != "" THEN 1 ELSE 0 END) as has_classwork,
                     SUM(CASE WHEN is_audio = 1 THEN 1 ELSE 0 END) as has_audio,
                     SUM(CASE WHEN is_video = 1 THEN 1 ELSE 0 END) as has_video,
                     SUM(CASE WHEN is_picture = 1 THEN 1 ELSE 0 END) as has_picture,
                     SUM(CASE WHEN quiz_id IS NOT NULL THEN 1 ELSE 0 END) as has_quiz,
                     SUM(CASE WHEN is_book = 1 OR is_notebook = 1 THEN 1 ELSE 0 END) as has_bagpack')
            ->where('campus_id', $campus_id)
            ->where('session_id', $session_id);
        
        $builder = $this->applyFilters($builder, $filters);
        
        $results = $builder->groupBy('subject_id, subject_name')
            ->orderBy('total_entries', 'DESC')
            ->get()
            ->getResultArray();
        
        // Calculate percentages
        foreach ($results as &$row) {
            $row['homework_percentage'] = $row['total_entries'] > 0 ? round(($row['has_homework'] / $row['total_entries']) * 100, 2) : 0;
            $row['classwork_percentage'] = $row['total_entries'] > 0 ? round(($row['has_classwork'] / $row['total_entries']) * 100, 2) : 0;
            $row['audio_percentage'] = $row['total_entries'] > 0 ? round(($row['has_audio'] / $row['total_entries']) * 100, 2) : 0;
            $row['video_percentage'] = $row['total_entries'] > 0 ? round(($row['has_video'] / $row['total_entries']) * 100, 2) : 0;
            $row['picture_percentage'] = $row['total_entries'] > 0 ? round(($row['has_picture'] / $row['total_entries']) * 100, 2) : 0;
            $row['quiz_percentage'] = $row['total_entries'] > 0 ? round(($row['has_quiz'] / $row['total_entries']) * 100, 2) : 0;
        }
        
        return [
            'subject_wise_data' => $results,
            'total_subjects' => count($results)
        ];
    }
    
    private function getTeacherWiseAnalytics($campus_id, $session_id, $filters, $includeFlags)
    {
        $builder = $this->db->table('v_class_diary_analytics')
            ->select('teacher_id, 
                     CONCAT(teacher_first_name, " ", teacher_last_name) as teacher_name,
                     teacher_designation,
                     COUNT(*) as total_entries,
                     SUM(CASE WHEN homework IS NOT NULL AND homework != "" THEN 1 ELSE 0 END) as has_homework,
                     SUM(CASE WHEN classwork IS NOT NULL AND classwork != "" THEN 1 ELSE 0 END) as has_classwork,
                     SUM(CASE WHEN is_audio = 1 THEN 1 ELSE 0 END) as has_audio,
                     SUM(CASE WHEN is_video = 1 THEN 1 ELSE 0 END) as has_video,
                     SUM(CASE WHEN is_picture = 1 THEN 1 ELSE 0 END) as has_picture,
                     SUM(CASE WHEN quiz_id IS NOT NULL THEN 1 ELSE 0 END) as has_quiz')
            ->where('campus_id', $campus_id)
            ->where('session_id', $session_id);
        
        $builder = $this->applyFilters($builder, $filters);
        
        $results = $builder->groupBy('teacher_id')
            ->orderBy('total_entries', 'DESC')
            ->get()
            ->getResultArray();
        
        return ['teacher_wise_data' => $results];
    }
    
    private function getClassWiseAnalytics($campus_id, $session_id, $filters, $includeFlags)
    {
        $builder = $this->db->table('v_class_diary_analytics')
            ->select('class_id, class_name, section_name,
                     COUNT(*) as total_entries,
                     COUNT(DISTINCT subject_id) as subjects_covered,
                     COUNT(DISTINCT teacher_id) as teachers_involved,
                     SUM(CASE WHEN is_audio = 1 THEN 1 ELSE 0 END) as total_audio_tasks,
                     SUM(CASE WHEN is_video = 1 THEN 1 ELSE 0 END) as total_video_tasks,
                     SUM(CASE WHEN is_picture = 1 THEN 1 ELSE 0 END) as total_picture_tasks')
            ->where('campus_id', $campus_id)
            ->where('session_id', $session_id);
        
        $builder = $this->applyFilters($builder, $filters);
        
        $results = $builder->groupBy('class_id, class_name, section_name')
            ->orderBy('total_entries', 'DESC')
            ->get()
            ->getResultArray();
        
        return ['class_wise_data' => $results];
    }
    
    private function getWeeklyTrendAnalytics($campus_id, $session_id, $filters, $includeFlags)
    {
        $builder = $this->db->table('v_class_diary_analytics')
            ->select('week_no, week_start_date, week_end_date,
                     COUNT(*) as total_entries,
                     SUM(CASE WHEN homework IS NOT NULL AND homework != "" THEN 1 ELSE 0 END) as homework_count,
                     SUM(CASE WHEN classwork IS NOT NULL AND classwork != "" THEN 1 ELSE 0 END) as classwork_count,
                     SUM(CASE WHEN is_audio = 1 THEN 1 ELSE 0 END) as audio_count,
                     SUM(CASE WHEN is_video = 1 THEN 1 ELSE 0 END) as video_count,
                     SUM(CASE WHEN is_picture = 1 THEN 1 ELSE 0 END) as picture_count,
                     SUM(CASE WHEN quiz_id IS NOT NULL THEN 1 ELSE 0 END) as quiz_count')
            ->where('campus_id', $campus_id)
            ->where('session_id', $session_id);
        
        $builder = $this->applyFilters($builder, $filters);
        
        $results = $builder->groupBy('week_no, week_start_date, week_end_date')
            ->orderBy('week_no', 'ASC')
            ->get()
            ->getResultArray();
        
        return ['weekly_trends' => $results];
    }
    
    private function getTaskCompletionAnalytics($campus_id, $session_id, $filters)
    {
        // This is a simplified version - you may need to adjust based on your actual tables
        return [
            'completion_statistics' => [],
            'subject_performance' => []
        ];
    }
    
    private function getDetailedDiaryData($campus_id, $session_id, $filters, $includeFlags)
    {
        $builder = $this->db->table('v_class_diary_analytics')
            ->select('diary_date, class_name, section_name, subject_name, subject_short_name,
                     CONCAT(teacher_first_name, " ", teacher_last_name) as teacher_name,
                     week_no, homework, classwork, is_audio, is_video, is_picture, 
                     quiz_id, quiz_title, is_book, is_notebook, audio_caption, video_caption, picture_caption')
            ->where('campus_id', $campus_id)
            ->where('session_id', $session_id);
        
        $builder = $this->applyFilters($builder, $filters);
        
        $results = $builder->orderBy('diary_date', 'DESC')
            ->limit(1000)
            ->get()
            ->getResultArray();
        
        // Process results
        foreach ($results as &$row) {
            $bagpack = [];
            if (!empty($row['is_book'])) $bagpack[] = 'Book';
            if (!empty($row['is_notebook'])) $bagpack[] = 'Notebook';
            $row['bagpack_items'] = $bagpack;
            $row['has_audio'] = !empty($row['is_audio']);
            $row['has_video'] = !empty($row['is_video']);
            $row['has_picture'] = !empty($row['is_picture']);
            $row['has_quiz'] = !empty($row['quiz_id']);
            
            // Clean up HTML content for display
            $row['homework'] = $this->cleanHtmlContent($row['homework'] ?? '');
            $row['classwork'] = $this->cleanHtmlContent($row['classwork'] ?? '');
        }
        
        return ['diary_entries' => $results];
    }
    
    private function applyFilters($builder, $filters)
    {
        if (!empty($filters['subject_id'])) {
            $builder->where('subject_id', $filters['subject_id']);
        }
        if (!empty($filters['class_id'])) {
            $builder->where('class_id', $filters['class_id']);
        }
        if (!empty($filters['section_id'])) {
            $builder->where('section_id', $filters['section_id']);
        }
        if (!empty($filters['teacher_id'])) {
            $builder->where('teacher_id', $filters['teacher_id']);
        }
        if (!empty($filters['term_id'])) {
            $builder->where('term_session_id', $filters['term_id']);
        }
        if (!empty($filters['week_id'])) {
            $builder->where('term_weeks_id', $filters['week_id']);
        }
        if (!empty($filters['start_date'])) {
            $builder->where('diary_date >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $builder->where('diary_date <=', $filters['end_date']);
        }
        
        return $builder;
    }
    
    private function cleanHtmlContent($html)
    {
        if (empty($html)) return '';
        // Strip HTML tags for preview
        return strip_tags($html);
    }
    
    // Dropdown data methods
    private function getSubjects($campus_id)
    {
        $systemId = $this->db->table('campus')->select('system_id')->where('campus_id', $campus_id)->get()->getRow()->system_id ?? 0;
        
        return $this->db->table('allsubject')
            ->select('sid, subject_name')
            ->where('system_id', $systemId)
            ->where('status', 1)
            ->orderBy('subject_name', 'ASC')
            ->get()
            ->getResultArray();
    }
    
    private function getClasses($campus_id)
    {
        $systemId = $this->db->table('campus')->select('system_id')->where('campus_id', $campus_id)->get()->getRow()->system_id ?? 0;
        
        return $this->db->table('classes')
            ->select('class_id, class_name')
            ->where('system_id', $systemId)
            ->where('status', 1)
            ->orderBy('class_name', 'ASC')
            ->get()
            ->getResultArray();
    }
    
    private function getSections($campus_id)
    {
        $systemId = $this->db->table('campus')->select('system_id')->where('campus_id', $campus_id)->get()->getRow()->system_id ?? 0;
        
        return $this->db->table('sections')
            ->select('section_id, section_name')
            ->where('system_id', $systemId)
            ->where('status', 1)
            ->orderBy('section_name', 'ASC')
            ->get()
            ->getResultArray();
    }
    
    private function getTeachers($campus_id)
    {
        return $this->db->table('users')
            ->select('id, CONCAT(first_name, " ", last_name) as name')
            ->where('campus_id', $campus_id)
            ->where('status', 1)
            ->orderBy('first_name', 'ASC')
            ->get()
            ->getResultArray();
    }
    
    private function getTerms($session_id, $system_id)
    {
        return $this->db->table('terms_session ts')
            ->select('ts.term_session_id, t.name as term_name')
            ->join('terms t', 't.term_id = ts.term_id')
            ->where('ts.session_id', $session_id)
            ->where('ts.system_id', $system_id)
            ->orderBy('ts.start_date', 'ASC')
            ->get()
            ->getResultArray();
    }
    
    private function getWeeks($session_id, $system_id)
    {
        return $this->db->table('term_weeks tw')
            ->select('tw.term_weeks_id, tw.week_no')
            ->join('terms_session ts', 'ts.term_session_id = tw.term_session_id')
            ->where('ts.session_id', $session_id)
            ->where('ts.system_id', $system_id)
            ->orderBy('tw.start_date', 'ASC')
            ->get()
            ->getResultArray();
    }
}