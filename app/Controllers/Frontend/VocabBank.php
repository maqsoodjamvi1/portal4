<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Libraries\CommonsImageLookup;
use Config\Database;

class VocabBank extends BaseController
{
    protected $db;
    protected $session;
    
    public function __construct()
    {
        $this->db = Database::connect();
        $this->session = session();
        helper(['url', 'form', 'parent_portal']);
    }
    
    public function index()
    {
        // 1) Auth check
        $auth = $this->session->get('auth');

        if (!$auth || empty($auth['logged_in'])) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        $role = $auth['role'];
        $userId = (int) $auth['user_id'];
        
        // 2) For parents: same child list + selection as datesheet
        if ($role === 'parent') {
            $children = \parent_portal_get_children($userId);

            $activeStudentId = (int) ($this->session->get('active_student_id') ?? 0);
            if ($activeStudentId <= 0 && ! empty($children)) {
                $activeStudentId = (int) $children[0]['student_id'];
                $this->session->set('active_student_id', $activeStudentId);
            }
            
            $data = [
                'role' => $role,
                'name' => $auth['name'] ?? 'User',
                'title' => 'Vocabulary',
                'children' => $children,
                'active_student_id' => $activeStudentId,
                'is_parent' => true,
            ];
            
            if ($activeStudentId > 0) {
                $studentVocabulary = $this->getStudentVocabularyData($activeStudentId);
                $data = array_merge($data, $studentVocabulary);
            }
            
            return view('frontend/vocab/vocabview', $data);
            
        } 
        // 3) For students: Get their vocabulary directly
        elseif ($role === 'student') {
            $studentVocabulary = $this->getStudentVocabularyData($userId);
            
            return view('frontend/vocab/vocabview', array_merge([
                'role' => $role,
                'name' => $auth['name'] ?? 'User',
                'title' => 'My Vocabulary Bank',
                'is_parent' => false,
                'children' => [],
                'active_student_id' => $userId
            ], $studentVocabulary));
        }
        
        return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
    }
    
    /**
     * Get vocabulary data for a specific student
     */
private function getStudentVocabularyData($studentId)
{
    // Get student info (same pattern as DatesheetController)
    $student = $this->db->table('students s')
        ->select('s.*, c.class_name, sec.section_name, cs.cls_sec_id, cs.class_id,
                 campus.campus_name, campus.campus_id')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.status = 1', 'left')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->join('campus', 'campus.campus_id = s.campus_id', 'left')
        ->where('s.student_id', $studentId)
        ->get()
        ->getRowArray();
        
    if (!$student) {
        return [
            'student' => null,
            'class_id' => 0,
            'subject_ids' => [],  // Changed from subject_id to subject_ids (array)
            'cls_sec_id' => 0,
            'error' => 'Student not found'
        ];
    }
    
    // Get current session
    $sessionId = $this->getCurrentSessionId($studentId);
    
    if (!$sessionId) {
        return [
            'student' => $student,
            'class_id' => $student['class_id'] ?? 0,
            'subject_ids' => [],
            'cls_sec_id' => $student['cls_sec_id'] ?? 0,
            'error' => 'Academic session is not configured'
        ];
    }
    
    // Get class_id and cls_sec_id from student data
    $classId = (int) ($student['class_id'] ?? 0);
    $clsSecId = (int) ($student['cls_sec_id'] ?? 0);
    
    if (!$classId || !$clsSecId) {
        return [
            'student' => $student,
            'class_id' => $classId,
            'subject_ids' => [],
            'cls_sec_id' => $clsSecId,
            'error' => 'Class or section information not found'
        ];
    }
    
    // Get ALL subjects for this class section
    $subjects = $this->db->table('section_subjects ss')
        ->select('ss.subject_id, asub.subject_name')
        ->join('allsubject asub', 'asub.sid = ss.subject_id')
        ->where('ss.cls_sec_id', $clsSecId)
        ->where('ss.status', 1)
        ->orderBy('asub.subject_name', 'ASC')
        ->get()
        ->getResultArray();
    
    // Get subject IDs as array
    $subjectIds = array_column($subjects, 'subject_id');
    
    return [
        'student' => $student,
        'class_id' => $classId,
        'subject_ids' => $subjectIds,  // Array of all subject IDs
        'subjects' => $subjects,       // Array with subject_id and subject_name
        'cls_sec_id' => $clsSecId,
        'session_id' => $sessionId,
        'error' => empty($subjectIds) ? 'No subjects found for your class and section' : null
    ];
}
    /**
     * Get current session ID for student (same as DatesheetController)
     */
    private function getCurrentSessionId(int $studentId): ?int
    {
        // Get campus_id from student
        $student = $this->db->table('students')
            ->select('campus_id')
            ->where('student_id', $studentId)
            ->get()
            ->getRowArray();
        
        if (!$student || empty($student['campus_id'])) {
            return null;
        }
        
        $campusId = (int) $student['campus_id'];
        
        // Get system_id from campus
        $campus = $this->db->table('campus')
            ->select('system_id')
            ->where('campus_id', $campusId)
            ->get()
            ->getRowArray();
        
        if (!$campus || empty($campus['system_id'])) {
            return null;
        }
        
        $systemId = (int) $campus['system_id'];
        
        // Get current academic session
        $session = $this->db->table('academic_session')
            ->select('session_id')
            ->where('system_id', $systemId)
            ->where('CURDATE() BETWEEN start_date AND end_date', null, false)
            ->orderBy('start_date', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
        
        return $session['session_id'] ?? null;
    }
    
    /**
     * AJAX endpoint to get vocabulary data
     */


public function getVocabularyData()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(400)
            ->setJSON(['status' => 'error', 'msg' => 'Invalid request type.']);
    }
    
    // Get auth info
    $auth = $this->session->get('auth');
    if (!$auth || empty($auth['logged_in'])) {
        return $this->response->setJSON([
            'status' => 'error',
            'msg' => 'Please login first'
        ]);
    }
    
    $role = $auth['role'];
    $userId = (int) $auth['user_id'];
    
    // For parents, check if active student is selected
    if ($role === 'parent') {
        $activeStudentId = (int) ($this->session->get('active_student_id') ?? 0);
        if ($activeStudentId <= 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'Please select a student first'
            ]);
        }
        
        // Verify parent owns this student
        $exists = $this->db->table('students')
            ->where('student_id', $activeStudentId)
            ->where('parent_id', $userId)
            ->countAllResults();
            
        if (!$exists) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'Student not found'
            ]);
        }
        
        $studentId = $activeStudentId;
    } else {
        $studentId = $userId;
    }
    
    // Get student vocabulary data
    $studentData = $this->getStudentVocabularyData($studentId);
    
    $classId = (int) $studentData['class_id'];
    $subjectIds = $studentData['subject_ids'] ?? [];  // Array of subject IDs
    $clsSecId = (int) $studentData['cls_sec_id'];
    $subjects = $studentData['subjects'] ?? [];  // Array with subject details
    
    log_message('info', "Vocabulary AJAX - Student ID: $studentId, Class: $classId, Subjects: " . implode(',', $subjectIds));
    
    if ($classId === 0 || empty($subjectIds)) {
        return $this->response->setJSON([
            'status' => 'error',
            'msg' => 'Unable to determine your class or subjects.',
            'debug' => [
                'student_id' => $studentId,
                'class_id' => $classId,
                'subject_ids' => $subjectIds,
                'cls_sec_id' => $clsSecId,
                'error' => $studentData['error'] ?? ''
            ]
        ]);
    }
    
    // Fetch vocabulary data from database
    try {
        // Check if vocabulary tables exist
        $tables = $this->db->listTables();
        $hasVocabTopics = in_array('vocab_topics', $tables);
        $hasVocabBank = in_array('vocab_bank', $tables);
        
        if (!$hasVocabTopics || !$hasVocabBank) {
            return $this->response->setJSON([
                'status' => 'ok',
                'topics' => [],
                'vocabulary' => [],
                'message' => 'Vocabulary feature is not configured yet.',
                'summary' => [
                    'student_name' => $studentData['student']['first_name'] . ' ' . $studentData['student']['last_name'],
                    'class_name' => $studentData['student']['class_name'] ?? '',
                    'section_name' => $studentData['student']['section_name'] ?? '',
                    'available_subjects' => $subjects
                ]
            ]);
        }
        
        // Get topics for ALL subjects of this class
        $topics = $this->db->table('vocab_topics vt')
            ->select('vt.*, a.subject_name')
            ->join('allsubject a', 'a.sid = vt.subject_id')
            ->where('vt.class_id', $classId)
            ->whereIn('vt.subject_id', $subjectIds)
            
            ->orderBy('a.subject_name', 'ASC')
           
            ->get()
            ->getResultArray();
        
        log_message('info', 'Found ' . count($topics) . ' topics for class ' . $classId . ' and subjects: ' . implode(',', $subjectIds));
        
        if (empty($topics)) {
            // Check which subjects have vocabulary
            $subjectsWithVocab = $this->db->table('vocab_topics vt')
                ->select('vt.subject_id, a.subject_name, COUNT(*) as topic_count')
                ->join('allsubject a', 'a.sid = vt.subject_id')
                ->where('vt.class_id', $classId)
                ->whereIn('vt.subject_id', $subjectIds)
                ->groupBy('vt.subject_id')
                ->get()
                ->getResultArray();
            
            $studentSubjects = [];
            foreach ($subjects as $subject) {
                $hasVocab = false;
                foreach ($subjectsWithVocab as $vocabSubject) {
                    if ($vocabSubject['subject_id'] == $subject['subject_id']) {
                        $hasVocab = true;
                        break;
                    }
                }
                $studentSubjects[] = [
                    'subject_name' => $subject['subject_name'],
                    'has_vocabulary' => $hasVocab
                ];
            }
            
            return $this->response->setJSON([
                'status' => 'ok',
                'topics' => [],
                'vocabulary' => [],
                'message' => 'No vocabulary topics available for any of your subjects.',
                'student_subjects' => $studentSubjects,
                'summary' => [
                    'student_name' => $studentData['student']['first_name'] . ' ' . $studentData['student']['last_name'],
                    'class_name' => $studentData['student']['class_name'] ?? '',
                    'section_name' => $studentData['student']['section_name'] ?? '',
                    'total_subjects' => count($subjects),
                    'subjects_with_vocabulary' => count($subjectsWithVocab)
                ]
            ]);
        }
        
        $topicIds = array_column($topics, 'id');
        
        // Get vocabulary words for ALL topics (optional image columns if present)
        $vocabulary = $this->db->table('vocab_bank')
            ->select($this->getVocabBankSelectColumns())
            ->where('class_id', $classId)
            ->whereIn('subject_id', $subjectIds)
            ->whereIn('topic_id', $topicIds)
            ->orderBy('topic_id', 'ASC')
            ->orderBy('word', 'ASC')
            ->get()
            ->getResultArray();

        $vocabulary = $this->enrichVocabularyRowsWithIllustrationUrl($vocabulary);
        
        log_message('info', 'Found ' . count($vocabulary) . ' vocabulary words');
        
        // Group vocabulary by topic
        $groupedVocabulary = [];
        foreach ($vocabulary as $word) {
            $topicId = $word['topic_id'];
            if (!isset($groupedVocabulary[$topicId])) {
                $groupedVocabulary[$topicId] = [];
            }
            $groupedVocabulary[$topicId][] = $word;
        }
        
        // Organize topics by subject for frontend
        $topicsBySubject = [];
        foreach ($topics as $topic) {
            $subjectId = $topic['subject_id'];
            $subjectName = $topic['subject_name'];
            
            if (!isset($topicsBySubject[$subjectName])) {
                $topicsBySubject[$subjectName] = [];
            }
            
            $topicsBySubject[$subjectName][] = [
                'id' => $topic['id'],
                'topic_name' => $topic['topic_name'],
                
                'vocabulary' => $groupedVocabulary[$topic['id']] ?? []
            ];
        }
        
        return $this->response->setJSON([
            'status' => 'ok',
            'topics_by_subject' => $topicsBySubject,  // Grouped by subject
            'raw_topics' => $topics,  // Original topics array
            'vocabulary' => $groupedVocabulary,
            'summary' => [
                'total_topics' => count($topics),
                'total_words' => count($vocabulary),
                'class_id' => $classId,
                'subjects_count' => count($subjectIds),
                'subjects_with_vocabulary' => array_keys($topicsBySubject),
                'cls_sec_id' => $clsSecId,
                'student_name' => $studentData['student']['first_name'] . ' ' . $studentData['student']['last_name'],
                'class_name' => $studentData['student']['class_name'] ?? '',
                'section_name' => $studentData['student']['section_name'] ?? '',
                'all_subjects' => array_column($subjects, 'subject_name')
            ]
        ]);
            
    } catch (\Throwable $e) {
        log_message('error', 'Vocabulary data error: ' . $e->getMessage());
        return $this->response->setJSON([
            'status' => 'error',
            'msg' => 'Database error occurred. Please try again later.',
            'debug_error' => ENVIRONMENT === 'development' ? $e->getMessage() : null
        ]);
    }
}

    /**
     * Batch-resolve Wikimedia Commons thumbnails for words without teacher images (cached server-side).
     */
    public function commonsImagesBatch()
    {
        // Do not rely on X-Requested-With — some proxies/CDNs strip it while JSON POST is still valid.
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)
                ->setJSON(['status' => 'error', 'msg' => 'Method not allowed']);
        }

        $auth = $this->session->get('auth');
        if (! $auth || empty($auth['logged_in'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'Please login first',
            ]);
        }

        $role = $auth['role'] ?? '';
        if ($role !== 'parent' && $role !== 'student') {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Unauthorized']);
        }

        $payload = $this->request->getJSON(true);
        if (! is_array($payload)) {
            $payload = [];
        }
        $items = $payload['items'] ?? [];
        if ($items === [] || ! is_array($items)) {
            return $this->response->setJSON(['status' => 'ok', 'images' => []]);
        }

        $items = array_slice($items, 0, 40);
        $lookup = new CommonsImageLookup();
        $out = [];

        foreach ($items as $it) {
            if (! is_array($it)) {
                continue;
            }
            $id = (int) ($it['id'] ?? 0);
            $term = isset($it['term']) ? trim((string) $it['term']) : '';
            $meaningEn = isset($it['meaning_en']) ? trim((string) $it['meaning_en']) : '';
            if ($id <= 0 || $term === '') {
                continue;
            }

            $r = $lookup->resolveForVocabularyWord($term, $meaningEn !== '' ? $meaningEn : null);
            if ($r !== null) {
                $out[] = [
                    'id' => $id,
                    'url' => $r['url'],
                    'file_page' => $r['file_page'],
                    'credit' => $r['credit'],
                    'description' => $r['description'] ?? '',
                ];
            } else {
                $out[] = [
                    'id' => $id,
                    'url' => '',
                    'file_page' => '',
                    'credit' => '',
                    'description' => '',
                ];
            }
        }

        return $this->response->setJSON(['status' => 'ok', 'images' => $out]);
    }

    /**
     * AJAX endpoint to switch student (for parents)
     */
    public function switchStudent($studentId)
    {
        $auth = $this->session->get('auth');
        
        if ($auth['role'] !== 'parent') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }
        
        // Verify parent owns this student
        $exists = $this->db->table('students')
            ->where('student_id', $studentId)
            ->where('parent_id', $auth['user_id'])
            ->countAllResults();
            
        if ($exists) {
            $this->session->set('active_student_id', $studentId);
            return $this->response->setJSON(['success' => true]);
        }
        
        return $this->response->setJSON(['success' => false, 'message' => 'Student not found']);
    }
    
    /**
     * Helper method to get student name
     */
    private function getStudentName($studentId)
    {
        $query = $this->db->table('students')
            ->select('first_name, last_name')
            ->where('student_id', $studentId)
            ->get();
            
        $student = $query->getRowArray();
        
        if ($student) {
            return trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        }
        
        return '';
    }

    /**
     * Columns to load from vocab_bank; includes illustration fields only if they exist.
     *
     * @return list<string>
     */
    private function getVocabBankSelectColumns(): array
    {
        $base = [
            'id', 'topic_id', 'word', 'meaning_en', 'meaning_ur',
            'example_sentence', 'part_of_speech', 'syllables',
            'synonyms', 'antonyms', 'related_words', 'confusing_pair',
            'confusing_pair_difference', 'difficulty_level',
        ];
        foreach ($this->vocabBankOptionalImageColumnNames() as $optional) {
            if (! in_array($optional, $base, true)) {
                $base[] = $optional;
            }
        }

        return array_values(array_unique($base));
    }

    /**
     * Image columns present on vocab_bank (teacher uploads / URLs).
     *
     * @return list<string>
     */
    private function vocabBankOptionalImageColumnNames(): array
    {
        static $memo = null;
        if ($memo !== null) {
            return $memo;
        }
        $memo = [];
        try {
            $tbl = $this->db->prefixTable('vocab_bank');
            $rows = $this->db->query(
                'SHOW COLUMNS FROM ' . $this->db->escapeIdentifiers($tbl)
            )->getResultArray();
            $have = array_column($rows, 'Field');
            foreach (['question_image', 'word_image', 'image_url'] as $c) {
                if (in_array($c, $have, true)) {
                    $memo[] = $c;
                }
            }
        } catch (\Throwable $e) {
            $memo = [];
        }

        return $memo;
    }

    /**
     * Add illustration_url for the portal (teacher-uploaded image path or absolute URL).
     *
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    private function enrichVocabularyRowsWithIllustrationUrl(array $rows): array
    {
        foreach ($rows as &$row) {
            $raw = trim((string) (
                $row['question_image'] ??
                $row['word_image'] ??
                $row['image_url'] ??
                ''
            ));
            $row['illustration_url'] = $raw === '' ? '' : $this->vocabIllustrationPublicUrl($raw);
        }
        unset($row);

        return $rows;
    }

    private function vocabIllustrationPublicUrl(string $raw): string
    {
        if (preg_match('#^https?://#i', $raw)) {
            return $raw;
        }

        return base_url(ltrim(str_replace('\\', '/', $raw), '/'));
    }
}