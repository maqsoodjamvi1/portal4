<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use DateTime;

class Datesheet extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url', 'text']);
        $this->db = \Config\Database::connect();
        $this->session = session();
    }

    /**
     * Datesheet (with syllabus)
     */
public function index()
{
    // Log the start of the request
    log_message('debug', '=== DATESHEET INDEX START ===');
    log_message('debug', 'Request URI: ' . current_url());
    log_message('debug', 'GET Parameters: ' . json_encode($_GET));
    
    $session = session();
    log_message('debug', 'Session ID: ' . $session->get('session_id'));

    // =========================
    // HANDLE INSTRUCTION SAVING VIA GET PARAMETERS
    // =========================
    if ($this->request->getGet('save_instructions') === '1') {
        log_message('debug', '=== PROCESSING SAVE INSTRUCTIONS ===');
        
        $campus_id  = (int) ($session->get('member_campusid') ?? 0);
        $sessionid  = (int) ($session->get('member_sessionid') ?? 0);
        
        log_message('debug', 'Campus ID from session: ' . $campus_id);
        log_message('debug', 'Session ID from session: ' . $sessionid);
        
        // Get instruction parameters
        $instructions = $this->request->getGet('instructions') ?? '';
        $instructions = urldecode($instructions);
        $showInstructions = (int) ($this->request->getGet('show_instructions') ?? 0);
        $instructionsPosition = $this->request->getGet('instructions_position') ?? 'after';
        
        log_message('debug', 'Instructions received: ' . substr($instructions, 0, 100) . '...');
        log_message('debug', 'Show Instructions: ' . $showInstructions);
        log_message('debug', 'Position: ' . $instructionsPosition);
        
        if ($campus_id && $sessionid) {
            log_message('debug', 'Looking for active exam...');
            
            // Find the active exam
            $exam = $this->db->table('exam')
                ->where('status', 0)
                ->where('session_id', $sessionid)
                ->where('campus_id', $campus_id)
                ->orderBy('eid', 'DESC')
                ->get()
                ->getRow();
                
            if ($exam) {
                log_message('debug', 'Found exam ID: ' . $exam->eid);
                log_message('debug', 'Current instructions in DB: ' . ($exam->instructions ?? 'EMPTY'));
                
                // Update the exam with new instructions
                $updateData = [
                    'instructions' => $instructions,
                    'show_instructions' => $showInstructions,
                    'instructions_position' => $instructionsPosition,
                    'updated_date' => date('Y-m-d H:i:s')
                ];
                
                log_message('debug', 'Update data: ' . json_encode($updateData));
                
                $result = $this->db->table('exam')
                    ->where('eid', $exam->eid)
                    ->update($updateData);
                    
                log_message('debug', 'Update result: ' . ($result ? 'SUCCESS' : 'FAILED'));
                log_message('debug', 'Affected rows: ' . $this->db->affectedRows());
                
                if ($result) {
                    log_message('info', 'Instructions saved successfully for exam ID: ' . $exam->eid);
                    
                    // Set success flash message
                    $session->setFlashdata('save_success', 'Instructions saved successfully!');
                    $session->setFlashdata('save_success_type', 'success');
                } else {
                    log_message('error', 'Database update failed for exam ID: ' . $exam->eid);
                    log_message('error', 'DB Error: ' . json_encode($this->db->error()));
                    
                    $session->setFlashdata('save_success', 'Failed to save instructions to database!');
                    $session->setFlashdata('save_success_type', 'danger');
                }
            } else {
                log_message('error', 'No active exam found!');
                
                // Check what exams do exist
                $allExams = $this->db->table('exam')
                    ->where('session_id', $sessionid)
                    ->where('campus_id', $campus_id)
                    ->get()
                    ->getResult();
                    
                log_message('error', 'Total exams for this campus/session: ' . count($allExams));
                foreach ($allExams as $e) {
                    log_message('error', 'Exam ID: ' . $e->eid . ', Status: ' . $e->status . ', Name: ' . ($e->exam_name ?? 'N/A'));
                }
                
                $session->setFlashdata('save_success', 'No active exam found! Please create an exam first.');
                $session->setFlashdata('save_success_type', 'warning');
            }
        } else {
            log_message('error', 'Missing session data for instruction saving');
            
            $session->setFlashdata('save_success', 'Session data missing. Please login again.');
            $session->setFlashdata('save_success_type', 'danger');
        }
        
        log_message('debug', '=== END SAVE INSTRUCTIONS ===');
        
        // Build redirect URL without save parameters - FIXED VERSION
       $queryParams = $_GET;
unset($queryParams['save_instructions']);
unset($queryParams['instructions']);
unset($queryParams['show_instructions']);
unset($queryParams['instructions_position']);

// Get current URL path
$currentUri = current_url(true);
$redirectUrl = $currentUri->setQuery(http_build_query($queryParams))->__toString();

log_message('debug', 'Redirecting to: ' . $redirectUrl);

return redirect()->to($redirectUrl);
    }

    // =========================
    // CORE CONTEXT
    // =========================
    $campus_id  = (int) ($session->get('member_campusid') ?? 0);
    $sessionid  = (int) ($session->get('member_sessionid') ?? 0);
    
    log_message('debug', 'Main index - Campus ID: ' . $campus_id);
    log_message('debug', 'Main index - Session ID: ' . $sessionid);

    // =========================
    // DYNAMIC SCHOOL INFO
    // =========================
    $schoolinfo = $this->getDynamicSchoolInfo($campus_id);
    log_message('debug', 'School info obtained: ' . ($schoolinfo ? 'YES' : 'NO'));

    $roles = currentUserRoles();
    log_message('debug', 'User roles: ' . json_encode($roles));

    // =========================
    // CLASS / SECTION ACCESS
    // =========================
    $sectionsclassinfo = in_array(5, (array) $roles, true)
        ? teacherSubjectSections()
        : userClassSections();
    
    log_message('debug', 'Sections/classes found: ' . count($sectionsclassinfo));

    // =========================
    // FETCH CAMPUS (FULL ROW)
    // =========================
    $campusRow = null;
    $system_id = null;

    if ($campus_id > 0) {
        $campusRow = $this->db->table('campus')
            ->select('campus_id, system_id, campus_name, location, landline')
            ->where('campus_id', $campus_id)
            ->get()
            ->getRow();

        $system_id = (int) ($campusRow->system_id ?? 0);
        log_message('debug', 'Campus row found: ' . ($campusRow ? 'YES' : 'NO'));
    }

    // =========================
    // FETCH EXAM INSTRUCTIONS
    // =========================
    $examInstructions = '';
    $showInstructions = false;
    $instructionsPosition = 'after';
    
    log_message('debug', 'Fetching exam instructions...');
    log_message('debug', 'Looking for exam with: campus_id=' . $campus_id . ', session_id=' . $sessionid);
    
    if ($sessionid > 0 && $campus_id > 0) {
        $examinfo = $this->db->table('exam')
            ->select('instructions, show_instructions, instructions_position')
            ->where('status', 0)
            ->where('session_id', $sessionid)
            ->where('campus_id', $campus_id)
            ->orderBy('eid', 'DESC')
            ->get()
            ->getRow();
        
        log_message('debug', 'Exam query executed');
        log_message('debug', 'Exam found: ' . ($examinfo ? 'YES' : 'NO'));
        
        if ($examinfo) {
            $examInstructions = $examinfo->instructions ?? '';
            $showInstructions = (bool) ($examinfo->show_instructions ?? false);
            $instructionsPosition = $examinfo->instructions_position ?? 'after';
            
            log_message('debug', 'Current instructions in DB: ' . substr($examInstructions, 0, 100));
            log_message('debug', 'Show instructions: ' . ($showInstructions ? 'YES' : 'NO'));
            log_message('debug', 'Position: ' . $instructionsPosition);
        }
    }

    // =========================
    // RESOLVE FINAL LOGO (SINGLE SOURCE)
    // =========================
    $finalLogo = null;
    log_message('debug', 'Resolving logo...');

    // 1️⃣ Campus logo
    if (!empty($campusRow->logo)) {
        $finalLogo = $campusRow->logo;
        log_message('debug', 'Using campus logo: ' . $finalLogo);
    }

    // 2️⃣ System logo (dynamic, NOT hardcoded)
    if (empty($finalLogo) && $system_id > 0) {
        $systemRow = $this->db->table('system')
            ->select('logo, system_name')
            ->where('system_id', $system_id)
            ->get()
            ->getRow();

        if (!empty($systemRow->logo)) {
            $finalLogo = $systemRow->logo;
            log_message('debug', 'Using system logo: ' . $finalLogo);
        }
        
        // Ensure schoolinfo has correct system name
        if ($schoolinfo && !empty($systemRow->system_name)) {
            $schoolinfo->system_name = $systemRow->system_name;
        }
    }

    if (!$finalLogo) {
        log_message('debug', 'No logo found, using default');
    }

    // =========================
    // ENHANCE SCHOOLINFO WITH CAMPUS DETAILS
    // =========================
    if ($schoolinfo && $campusRow) {
        // Add campus-specific details
        $schoolinfo->campus_name = $campusRow->campus_name ?? '';
        $schoolinfo->campus_location = $campusRow->location ?? '';
        $schoolinfo->campus_phone = $campusRow->landline ?? '';
        
        // Ensure these properties exist
        if (!isset($schoolinfo->address)) $schoolinfo->address = '';
        if (!isset($schoolinfo->phone)) $schoolinfo->phone = '';
        if (!isset($schoolinfo->email)) $schoolinfo->email = '';
        
        log_message('debug', 'School info enhanced with campus details');
    }

    // =========================
    // DATA TO VIEW
    // =========================
    $rawHm = $this->request->getGet('hide_marks');
    if (is_array($rawHm)) {
        $rawHm = (string) end($rawHm);
    } else {
        $rawHm = (string) ($rawHm ?? '');
    }
    $filterHideMarks = ($rawHm === '1' || strtolower($rawHm) === 'on') ? '1' : '';

    $rawFs = $this->request->getGet('full_subject');
    if (is_array($rawFs)) {
        $rawFs = (string) end($rawFs);
    } else {
        $rawFs = (string) ($rawFs ?? '');
    }
    $filterFullSubject = ($rawFs === '1' || strtolower($rawFs) === 'on') ? '1' : '';

    $filterFontSize = (string) ($this->request->getGet('font_size') ?? 'medium');
    if (! in_array($filterFontSize, ['small', 'medium', 'large'], true)) {
        $filterFontSize = 'medium';
    }

    $rawLh = $this->request->getGet('line_height');
    $filterLineHeight = ($rawLh === null || $rawLh === '') ? 2.0 : (float) (string) $rawLh;
    if ($filterLineHeight < 1.0) {
        $filterLineHeight = 1.0;
    }
    if ($filterLineHeight > 3.0) {
        $filterLineHeight = 3.0;
    }

    $mode = $this->request->getGet('mode');
    $studentId = (int) ($this->request->getGet('student_id') ?? 0);
    $selectedStudentLabel = '';
    if ($studentId > 0) {
        $stuRow = $this->db->table('students')
            ->select('first_name, last_name, reg_no')
            ->where('student_id', $studentId)
            ->where('campus_id', $campus_id)
            ->where('status', 1)
            ->get()
            ->getRow();
        if ($stuRow) {
            $selectedStudentLabel = trim(($stuRow->first_name ?? '') . ' ' . ($stuRow->last_name ?? ''));
            if (! empty($stuRow->reg_no)) {
                $selectedStudentLabel .= ' (' . $stuRow->reg_no . ')';
            }
        } else {
            $studentId = 0;
        }
    }

    $admitCardData = $mode === 'sample'
        ? $this->sampleAdmitCards($sectionsclassinfo, $filterHideMarks)
        : $this->data(null, 0, $filterHideMarks, $studentId);

    $data = [
        'sectionsclassinfo' => $sectionsclassinfo,
        'data'              => $admitCardData,
        'schoolinfo'        => $schoolinfo,
        'finalLogo'         => $finalLogo,
        'examInstructions'  => $examInstructions,
        'showInstructions'  => $showInstructions,
        'instructionsPosition' => $instructionsPosition,
        'sessionData'       => [
            'campusid'  => $campus_id,
            'sessionid' => $sessionid,
        ],
        'save_success'      => $session->getFlashdata('save_success'),
        'save_success_type' => $session->getFlashdata('save_success_type') ?? 'success',
        'admitFilters'      => [
            'hide_marks'    => $filterHideMarks,
            'full_subject'  => $filterFullSubject,
            'font_size'     => $filterFontSize,
            'line_height'   => $filterLineHeight,
        ],
        'selectedStudentId'    => $studentId,
        'selectedStudentLabel' => $selectedStudentLabel,
    ];
    
    log_message('debug', 'Data prepared for view');
    log_message('debug', 'Save success message: ' . ($data['save_success'] ?? 'NONE'));

    // =========================
    // VIEW SELECTION
    // =========================
    log_message('debug', 'View mode: ' . ($mode ?: 'default'));
    log_message('debug', '=== DATESHEET INDEX END ===');

    return view(
        $mode === 'without_syllabus'
            ? 'admin/datesheet_without_syllabus'
            : 'admin/datesheet',
        $data
    );
}
public function saveInstructions()
{
    // Set JSON response header
    header('Content-Type: application/json');
    
    // Start comprehensive logging
    log_message('debug', '=== SAVE INSTRUCTIONS REQUEST START ===');
    log_message('debug', 'Request Method: ' . $this->request->getMethod());
    log_message('debug', 'Is AJAX: ' . ($this->request->isAJAX() ? 'Yes' : 'No'));
    log_message('debug', 'Request Headers: ' . json_encode($this->request->headers()));
    
    $session = session();
    $campus_id = (int) ($session->get('member_campusid') ?? 0);
    $sessionid = (int) ($session->get('member_sessionid') ?? 0);
    
    log_message('debug', 'Session Data - Campus ID: ' . $campus_id);
    log_message('debug', 'Session Data - Session ID: ' . $sessionid);
    log_message('debug', 'All Session Data: ' . json_encode($_SESSION ?? []));
    
    // Check session data
    if (!$campus_id) {
        log_message('error', 'Missing campus_id in session. Available keys: ' . json_encode(array_keys($_SESSION ?? [])));
    }
    if (!$sessionid) {
        log_message('error', 'Missing sessionid in session');
    }
    
    if (!$campus_id || !$sessionid) {
        log_message('error', 'Save failed: Missing session data');
        return $this->response->setJSON([
            'success' => false, 
            'error' => 'Session data missing. Please login again.',
            'debug' => [
                'campus_id' => $campus_id,
                'sessionid' => $sessionid
            ]
        ]);
    }

    // Get POST data
    $postData = $this->request->getPost();
    log_message('debug', 'POST Data Received: ' . json_encode($postData));
    log_message('debug', 'Raw POST: ' . file_get_contents('php://input'));
    
    $instructions = $postData['instructions'] ?? '';
    $showInstructions = isset($postData['show_instructions']) ? (int)$postData['show_instructions'] : 0;
    $instructionsPosition = $postData['instructions_position'] ?? 'after';
    $csrf = $postData['csrf_test_name'] ?? '';
    
    log_message('debug', 'Parsed Data - Instructions length: ' . strlen($instructions));
    log_message('debug', 'Parsed Data - Show Instructions: ' . $showInstructions);
    log_message('debug', 'Parsed Data - Position: ' . $instructionsPosition);
    log_message('debug', 'Parsed Data - CSRF Token present: ' . (!empty($csrf) ? 'Yes' : 'No'));


    try {
        // Check if exam exists
        log_message('debug', 'Querying exam table...');
        log_message('debug', 'SQL Conditions: campus_id=' . $campus_id . ', session_id=' . $sessionid . ', status=0');
        
        $exam = $this->db->table('exam')
            ->where('status', 0)
            ->where('session_id', $sessionid)
            ->where('campus_id', $campus_id)
            ->orderBy('eid', 'DESC')
            ->get()
            ->getRow();

        if (!$exam) {
            log_message('error', 'No active exam found!');
            log_message('error', 'Checking if ANY exam exists with these parameters...');
            
            // Debug query to see what's in the table
            $allExams = $this->db->table('exam')
                ->where('campus_id', $campus_id)
                ->where('session_id', $sessionid)
                ->get()
                ->getResult();
                
            log_message('error', 'Total exams found for campus/session: ' . count($allExams));
            foreach ($allExams as $e) {
                log_message('error', 'Exam ID: ' . $e->eid . ', Status: ' . $e->status . ', Name: ' . ($e->exam_name ?? 'N/A'));
            }
            
            return $this->response->setJSON([
                'success' => false, 
                'error' => 'No active exam found. Please create an exam first.',
                'debug' => [
                    'campus_id' => $campus_id,
                    'session_id' => $sessionid,
                    'total_exams' => count($allExams)
                ]
            ]);
        }
        
        log_message('debug', 'Found exam ID: ' . $exam->eid);
        log_message('debug', 'Exam details: ' . json_encode([
            'exam_name' => $exam->exam_name ?? 'N/A',
            'current_instructions' => $exam->instructions ?? 'EMPTY',
            'current_show' => $exam->show_instructions ?? 0,
            'current_position' => $exam->instructions_position ?? 'after'
        ]));

        // Prepare update data
        $updateData = [
            'instructions' => $instructions,
            'show_instructions' => $showInstructions,
            'instructions_position' => $instructionsPosition,
            'updated_date' => date('Y-m-d H:i:s')
        ];
        
        log_message('debug', 'Update Data Prepared: ' . json_encode($updateData));
        
        // Execute update
        $result = $this->db->table('exam')
            ->where('eid', $exam->eid)
            ->update($updateData);
            
        log_message('debug', 'Update Query Executed: ' . ($result ? 'SUCCESS' : 'FAILED'));
        log_message('debug', 'DB Error Info: ' . json_encode($this->db->error()));
        
        if ($result) {
            // Verify the update
            $updatedExam = $this->db->table('exam')
                ->where('eid', $exam->eid)
                ->get()
                ->getRow();
                
            log_message('debug', 'Verification - Updated instructions: ' . ($updatedExam->instructions ?? 'NOT UPDATED'));
            log_message('debug', 'Verification - Updated show: ' . ($updatedExam->show_instructions ?? 'NOT UPDATED'));
            log_message('debug', 'Verification - Updated position: ' . ($updatedExam->instructions_position ?? 'NOT UPDATED'));
            
            log_message('info', 'Instructions saved successfully for exam ID: ' . $exam->eid);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Instructions saved successfully!',
                'exam_id' => $exam->eid,
                'debug' => [
                    'rows_affected' => $this->db->affectedRows(),
                    'updated_data' => [
                        'instructions' => $updatedExam->instructions ?? '',
                        'show_instructions' => $updatedExam->show_instructions ?? 0,
                        'instructions_position' => $updatedExam->instructions_position ?? 'after'
                    ]
                ]
            ]);
        } else {
            log_message('error', 'Database update failed. No rows affected.');
            log_message('error', 'Last Query: ' . $this->db->getLastQuery());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Database update failed. No changes were made.',
                'debug' => [
                    'db_error' => $this->db->error(),
                    'last_query' => $this->db->getLastQuery()
                ]
            ]);
        }

    } catch (\Exception $e) {
        log_message('error', 'Save instructions exception: ' . $e->getMessage());
        log_message('error', 'Exception Trace: ' . $e->getTraceAsString());
        return $this->response->setJSON([
            'success' => false,
            'error' => 'Server error: ' . $e->getMessage(),
            'debug' => [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]
        ]);
    } finally {
        log_message('debug', '=== SAVE INSTRUCTIONS REQUEST END ===');
    }
}

// Add this method to your controller for debugging
public function debugExamStatus()
{
    $session = session();
    $campus_id = (int) ($session->get('member_campusid') ?? 0);
    $sessionid = (int) ($session->get('member_sessionid') ?? 0);
    
    log_message('debug', '=== DEBUG EXAM STATUS ===');
    log_message('debug', 'Campus ID from session: ' . $campus_id);
    log_message('debug', 'Session ID from session: ' . $sessionid);
    
    if ($campus_id && $sessionid) {
        // Check if exam exists
        $exam = $this->db->table('exam')
            ->where('status', 0)
            ->where('session_id', $sessionid)
            ->where('campus_id', $campus_id)
            ->orderBy('eid', 'DESC')
            ->get()
            ->getRow();
            
        log_message('debug', 'Exam found: ' . ($exam ? 'YES (ID: ' . $exam->eid . ')' : 'NO'));
        
        if ($exam) {
            log_message('debug', 'Current instructions: ' . ($exam->instructions ?? 'EMPTY'));
            log_message('debug', 'Show instructions: ' . ($exam->show_instructions ?? '0'));
            log_message('debug', 'Position: ' . ($exam->instructions_position ?? 'after'));
        }
    }
    
    log_message('debug', '=== END DEBUG ===');
    return $this->response->setJSON(['debug' => 'Check logs']);
}
// =========================
// PRIVATE HELPER METHOD
// =========================
private function getDynamicSchoolInfo($campus_id)
{
    if ($campus_id > 0) {
        // Get campus to find system_id
        $campusRow = $this->db->table('campus')
            ->select('system_id')
            ->where('campus_id', $campus_id)
            ->get()
            ->getRow();
        
        $system_id = (int) ($campusRow->system_id ?? 0);
        
        if ($system_id > 0) {
            $systemInfo = $this->db->table('system')
                ->select('*')
                ->where('system_id', $system_id)
                ->get()
                ->getRow();
            
            if ($systemInfo) {
                return $systemInfo;
            }
        }
    }
    
    // Fallback: Check if global getSchoolInfo() exists
    if (function_exists('getSchoolInfo')) {
        $globalSchoolInfo = getSchoolInfo();
        if ($globalSchoolInfo) {
            return $globalSchoolInfo;
        }
    }
    
    // Ultimate fallback: empty object with expected properties
    return (object)[
        'system_id' => 0,
        'system_name' => '',
        'address' => '',
        'phone' => '',
        'email' => '',
        'campus_name' => '',
        'campus_location' => '',
        'campus_phone' => ''
    ];
}
    
public function data(?int $selectedClsSecId = null, int $studentLimit = 0, ?string $hideMarksArg = null, int $studentIdArg = 0): array
{
if ($hideMarksArg !== null) {
        $hide_marks = (string) $hideMarksArg;
    } else {
        $rawHm = $this->request->getGet('hide_marks');
        if (is_array($rawHm)) {
            $rawHm = (string) end($rawHm);
        } else {
            $rawHm = (string) ($rawHm ?? '');
        }
        $hide_marks = ($rawHm === '1' || strtolower($rawHm) === 'on') ? '1' : '';
    }
    $student_id = $studentIdArg > 0
        ? $studentIdArg
        : (int) ($this->request->getGet('student_id') ?? 0);
    $cls_sec_id  = $selectedClsSecId ?? (int) ($this->request->getGet('cls_sec_id') ?? 0);

    // DON'T use global getSchoolInfo() - it returns wrong system_id
    $campus_id     = (int) ($this->session->get('member_campusid') ?? 0);
    $sessionid     = (int) ($this->session->get('member_sessionid') ?? 0);
    
    $out = [];

    if ($student_id > 0 && $sessionid > 0) {
        $scRow = $this->db->table('student_class')
            ->select('cls_sec_id')
            ->where('student_id', $student_id)
            ->where('session_id', $sessionid)
            ->where('status', 1)
            ->get()
            ->getRow();
        if (! $scRow) {
            return $out;
        }
        $cls_sec_id = (int) $scRow->cls_sec_id;
    }

    if ($cls_sec_id === 0) return $out;

    // ----- Active exam for campus+session
    $examinfo = $this->db->table('exam')
        ->where('status', 0)
        ->where('session_id', $sessionid)
        ->where('campus_id', $campus_id)
        ->orderBy('eid', 'DESC')
        ->get()->getRow();

    $exam_name   = '';
    $eid         = 0;
    $exam_sessid = 0;
    $exam_termid = 0;
    $date_from   = null;
    $date_to     = null;

    if ($examinfo) {
        $eid         = (int) $examinfo->eid;
        $exam_name   = (string) ($examinfo->exam_name ?? '');
        $exam_sessid = (int) ($examinfo->session_id ?? 0);
        $exam_termid = (int) ($examinfo->term_id ?? 0);

        $ts = $this->db->table('terms_session')
            ->select('start_date, end_date')
            ->where('session_id', $exam_sessid)
            ->where('term_id', $exam_termid)
            ->get()->getRow();

        if ($ts && !empty($ts->start_date) && !empty($ts->end_date)) {
            $date_from = (string) $ts->start_date;
            $date_to   = (string) $ts->end_date;
        } else {
            $date_from = (string) ($examinfo->exam_start_date ?? '');
            $date_to   = (string) ($examinfo->exam_end_date   ?? '');
        }
    }

    $hasWindow = $date_from && $date_to;

    // ----- Students of this class-section
    $studentsBuilder = $this->db->table('student_class t1')
        ->select('t1.cls_sec_id, t2.student_id, t2.campus_id, t2.reg_no, t2.first_name, t2.last_name, t2.parent_id, t2.profile_photo')
        ->join('students t2', 't1.student_id = t2.student_id')
        ->where('t1.status', 1)
        ->where('t1.session_id', $sessionid)
        ->where('t2.campus_id', $campus_id)
        ->where('t1.cls_sec_id', $cls_sec_id)
        ->orderBy('t2.first_name', 'ASC');
    if ($student_id > 0) {
        $studentsBuilder->where('t1.student_id', $student_id);
    }
    if ($studentLimit > 0) {
        $studentsBuilder->limit($studentLimit);
    }
    $students = $studentsBuilder->get()->getResult();

    $campus_info = $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow();
    $campus_phone =
        $campus_info->phone
        ?? $campus_info->phone_no
        ?? $campus_info->mobile_no
        ?? $campus_info->campus_phone
        ?? '';

    // ----- Datesheet (unchanged)
    $subjectdatesheet = [];
    if ($eid > 0) {
        $datesheetinfo = $this->db->table('datesheet')
            ->where('eid', $eid)
            ->where('cls_sec_id', $cls_sec_id)
            ->orderBy('exam_date', 'ASC')
            ->orderBy('did', 'ASC')
            ->get()->getResult();

        foreach ($datesheetinfo as $ds) {
            $secSub = $this->db->table('section_subjects')
                ->where('sec_sub_id', (int) $ds->sec_sub_id)
                ->where('status', 1)
                ->get()->getRow();
            if (!$secSub) continue;

            $acadSub = $this->db->table('allsubject')
                ->where('sid', (int) $secSub->subject_id)
                ->get()->getRow();
            if (!$acadSub) continue;

            if ((int)$ds->total_marks <= 0) continue;

            $exam_date = \DateTime::createFromFormat('Y-m-d', (string)$ds->exam_date);
            $dateStr   = $exam_date ? $exam_date->format('d M Y') : (string)$ds->exam_date;
            $dayShort  = $exam_date ? $exam_date->format('D')     : date('D', strtotime((string)$ds->exam_date));
            $dateDay   = $dateStr . ' (' . $dayShort . ')';

            $subject = (string)$acadSub->subject_name;
            if ($hide_marks !== '1') {
                $subject .= ' (' . (int)$ds->total_marks . ')';
            }

            $shortName = trim((string)($acadSub->subject_short_name ?? ''));
            if ($shortName === '') {
                $shortName = (string)$acadSub->subject_name;
            }

            $subjectdatesheet[] = [
                'date_day'      => $dateDay,
                'exam_date'     => (string)$ds->exam_date,
                'subject_line'  => $subject,
                'subject_short' => $shortName,
                'subject_full'  => (string) $acadSub->subject_name,
                'marks'         => (int)$ds->total_marks,
                'syllabus'      => (string)$ds->syllabus,
            ];
        }
    }

    foreach ($students as $stu) {
        $student_id   = (int) $stu->student_id;
        $student_info = $this->db->table('students')->where('student_id', $student_id)->get()->getRow();
        if (!$student_info) continue;

        $parent_id_int = (int) ($student_info->parent_id ?? 0);
        $parent_info   = $this->db->table('parents')->where('parent_id', $parent_id_int)->get()->getRow();

        // Remaining dues
        $feeRow = $this->db->query(
            'SELECT SUM(amount - discount) AS feeTotal
             FROM fee_chalan
             WHERE status = "unpaid" 
             AND fee_month <> "2025-10"
               AND student_id IN (
                    SELECT student_id
                    FROM students
                    WHERE campus_id = ? AND parent_id = ? AND status = 1
               )',
            [$campus_id, $parent_id_int]
        )->getRow();
        $studentsFeeTotal = (float) ($feeRow->feeTotal ?? 0);

        $classSectioninfo = getClassSection($cls_sec_id);

        // Age: prefer stored age column if present, else from date_of_birth
        $ageYears = null;
        foreach (['student_age', 'age', 'current_age'] as $ageCol) {
            if (isset($student_info->{$ageCol}) && $student_info->{$ageCol} !== '' && $student_info->{$ageCol} !== null) {
                $ageYears = (int) $student_info->{$ageCol};
                break;
            }
        }
        if ($ageYears === null && ! empty($student_info->date_of_birth)) {
            try {
                $dob = new \DateTime((string) $student_info->date_of_birth);
                $ageYears = $dob->diff(new \DateTime('today'))->y;
            } catch (\Throwable $e) {
                $ageYears = null;
            }
        }

        $heightCm = isset($student_info->height) && $student_info->height !== '' && $student_info->height !== null
            ? (float) $student_info->height : null;
        $weightKg = isset($student_info->weight) && $student_info->weight !== '' && $student_info->weight !== null
            ? (float) $student_info->weight : null;
        $bmiVal = isset($student_info->bmi) && $student_info->bmi !== '' && $student_info->bmi !== null
            ? (float) $student_info->bmi : null;
        $bmiCategory = isset($student_info->bmi_category) ? trim((string) $student_info->bmi_category) : '';

        // Working days = count of attendance rows for this student in the term date range
        $working_days = null;
        $count_A = $count_L = $count_LC = $count_EL = 0;
        if ($hasWindow) {
            $wdRow = $this->db->table('attendance')
                ->select('COUNT(*) AS working_days', false)
                ->where('student_id', $student_id)
                ->where('date >=', $date_from)
                ->where('date <=', $date_to)
                ->get()->getRow();
            $working_days = (int) ($wdRow->working_days ?? 0);

            $att = $this->db->table('attendance')
                ->select([
                    'COUNT(DISTINCT CASE WHEN status = "A"  THEN `date` END) AS cnt_A',
                    'COUNT(DISTINCT CASE WHEN status = "L"  THEN `date` END) AS cnt_L',
                    'COUNT(DISTINCT CASE WHEN status = "LC" THEN `date` END) AS cnt_LC',
                    'COUNT(DISTINCT CASE WHEN status = "EL" THEN `date` END) AS cnt_EL',
                ], false)
                ->where('student_id', $student_id)
                ->where('date >=', $date_from)
                ->where('date <=', $date_to)
                ->get()->getRow();

            $count_A  = (int) ($att->cnt_A  ?? 0);
            $count_L  = (int) ($att->cnt_L  ?? 0);
            $count_LC = (int) ($att->cnt_LC ?? 0);
            $count_EL = (int) ($att->cnt_EL ?? 0);
        }

        $out[] = [
            'class'               => $classSectioninfo['sectionclassname'] ?? '',
            'campus_name'         => $campus_info->campus_name ?? '',
            'campus_location'     => $campus_info->location ?? '',
            'campus_phone'        => $campus_info->landline ?? '',
            'name'                => trim(($student_info->first_name ?? '') . ' ' . ($student_info->last_name ?? '')),
            'profile_photo'       => $student_info->profile_photo ?? '',
            'f_name'              => $parent_info->f_name ?? '',
            'father_contact'      => $parent_info->father_contact ?? '',
            'mother_contact'      => $parent_info->mother_contact ?? '',
            'reg_no'              => $student_info->reg_no ?? '',

            'student_age_years'   => $ageYears,
            'height_cm'           => $heightCm,
            'weight_kg'           => $weightKg,
            'bmi_value'           => $bmiVal,
            'bmi_category'        => $bmiCategory,
            'date_of_birth'       => $student_info->date_of_birth ?? null,
            'height'              => $heightCm,
            'weight'              => $weightKg,
            'bmi'                 => $bmiVal,

            'terms'               => $exam_name,
            'eid'                 => $eid,
            'term_session_id'     => ['session_id' => $exam_sessid, 'term_id' => $exam_termid],
            'term_window'         => $hasWindow ? [$date_from, $date_to] : null,
            'datesheetbysubject'  => $subjectdatesheet,

            'remaining_dues'      => $studentsFeeTotal,

            // Attendance in exam term window (status A=absent, L=late, LC=leave, EL=early left)
            'working_days'        => $working_days,
            'att_A'               => $count_A,
            'att_L'               => $count_L,
            'att_LC'              => $count_LC,
            'att_EL'              => $count_EL,
        ];
    }

    return $out;
}

private function sampleAdmitCards(array $sectionsclassinfo, string $hideMarks = ''): array
{
    $samples = [];
    $seenSections = [];

    foreach ($sectionsclassinfo as $section) {
        $clsSecId = (int) (is_array($section)
            ? ($section['cls_sec_id'] ?? $section['section_id'] ?? 0)
            : ($section->cls_sec_id ?? $section->section_id ?? 0));

        if ($clsSecId <= 0 || isset($seenSections[$clsSecId])) {
            continue;
        }

        $seenSections[$clsSecId] = true;
        $cards = $this->data($clsSecId, 1, $hideMarks);
        if (!empty($cards)) {
            $samples[] = $cards[0];
        }
    }

    return $samples;
}

/**
 * AJAX: search students by name for individual admit card (jQuery UI autocomplete).
 */
public function searchStudentsByName()
{
    $term = trim((string) ($this->request->getGet('term') ?? $this->request->getGet('q') ?? ''));
    $campus_id  = (int) ($this->session->get('member_campusid') ?? 0);
    $session_id = (int) ($this->session->get('member_sessionid') ?? 0);
    $limit      = max(1, min((int) ($this->request->getGet('limit') ?: 15), 30));

    if (strlen($term) < 2) {
        return $this->response->setJSON([]);
    }

    $builder = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.reg_no, sc.cls_sec_id, c.class_name, sec.section_name', false)
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . $session_id . ' AND sc.status = 1', 'inner')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where('s.campus_id', $campus_id)
        ->where('s.status', 1)
        ->groupStart()
            ->like('s.first_name', $term, 'after')
            ->orLike('s.last_name', $term, 'after')
            ->orLike('CONCAT(s.first_name, " ", s.last_name)', $term, 'after')
            ->orLike('s.reg_no', $term, 'after')
        ->groupEnd()
        ->orderBy('s.first_name', 'ASC')
        ->limit($limit);

    $rows = $builder->get()->getResultArray();

    return $this->response->setJSON($rows);
}


 public function addSyllabus()
    {
        $campus_id = (int) $this->session->get('member_campusid');
        $session_id = (int) ($this->session->get('member_sessionid') ?? 0);

        // resolve system_id for this campus (so we can find current session)
        $campusRow = $this->db->table('campus')
            ->select('system_id')
            ->where('campus_id', $campus_id)
            ->get()->getRow();

        $system_id = (int) ($campusRow->system_id ?? 0);

        // current session (status = 1)
        $currentSession = null;
        if ($session_id > 0) {
            $currentSession = $this->db->table('academic_session')
                ->select('session_id, session_name, start_date, end_date, status')
                ->where('session_id', $session_id)
                ->where('status', 1)
                ->get()->getRow();
        }
        if (!$currentSession && $system_id > 0) {
            $currentSession = $this->db->table('academic_session')
                ->select('session_id, session_name, start_date, end_date, status')
                ->where('system_id', $system_id)
                ->where('status', 1)
                ->orderBy('start_date', 'DESC')
                ->get()->getRow();
        }

        $examSessionId = $session_id > 0 ? $session_id : (int) ($currentSession->session_id ?? 0);
        $exam = $this->resolveExamForSyllabus($campus_id, $examSessionId);

        // Single combined class–section list for one dropdown
        $sections = $this->db->table('class_section cs')
            ->select("cs.cls_sec_id, CONCAT(c.class_name, ' - ', s.section_name) AS label")
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections s', 's.section_id = cs.section_id', 'left')
            ->where('cs.campus_id', $campus_id)
            ->where('cs.status', 1)
            ->orderBy('c.class_name', 'ASC')
            ->orderBy('s.section_name', 'ASC')
            ->get()
            ->getResultArray();

        return view('admin/datesheet_syllabus', [
            'sections'         => $sections,
            'exam'             => $exam,
            'currentSession'   => $currentSession,
        ]);
    }

public function fetchSyllabusGrid()
{
    if (strtolower($this->request->getMethod()) !== 'post') {
        return $this->response->setStatusCode(405)->setBody('Method Not Allowed');
    }

    try {
        $campus_id  = (int) (session('member_campusid') ?? 0);
        $cls_sec_id = (int) $this->request->getPost('cls_sec_id');
        $session_id = (int) (session('member_sessionid') ?? 0);

        if ($campus_id <= 0) {
            return $this->response->setBody('<div class="alert alert-danger mb-0">Campus not found in session.</div>');
        }
        if ($cls_sec_id <= 0) {
            return $this->response->setBody('<div class="alert alert-warning mb-0">Please select a class section.</div>');
        }

        if ($session_id <= 0) {
            $campusRow = $this->db->table('campus')
                ->select('system_id')
                ->where('campus_id', $campus_id)
                ->get()->getRow();
            $system_id = (int) ($campusRow->system_id ?? 0);

            if ($system_id > 0) {
                $row = $this->db->table('academic_session')
                    ->select('session_id')
                    ->where('system_id', $system_id)
                    ->where('status', 1)
                    ->orderBy('start_date', 'DESC')
                    ->limit(1)
                    ->get()->getRow();
                $session_id = (int) ($row->session_id ?? 0);
            }
        }

        $exam = $this->resolveExamForSyllabus($campus_id, $session_id);

        if (!$exam) {
            return $this->response->setBody('<div class="alert alert-danger mb-0">No active (unannounced) exam found for the current session. Create an exam under Admin → Exams, then add datesheet rows.</div>');
        }

        $eidInt = (int) $exam->eid;

        $subjects = $this->db->table('section_subjects ss')
            ->select('ss.sec_sub_id, ss.subject_id, a.subject_name, a.subject_short_name')
            ->join('allsubject a', 'a.sid = ss.subject_id')
            ->where('ss.cls_sec_id', $cls_sec_id)
            ->where('ss.status', 1)
            ->orderBy('a.subject_name', 'ASC')
            ->get()
            ->getResult();

        $dsRows = $this->db->table('datesheet')
            ->select('sec_sub_id, exam_date, total_marks')
            ->where('eid', $eidInt)
            ->where('cls_sec_id', $cls_sec_id)
            ->orderBy('exam_date', 'ASC')
            ->get()->getResult();

        $dsMap = [];
        foreach ($dsRows as $r) {
            $dsMap[(int) $r->sec_sub_id] = [
                'exam_date'   => (string) ($r->exam_date ?? ''),
                'total_marks' => (int) ($r->total_marks ?? 0),
            ];
        }

        usort($subjects, static function ($a, $b) use ($dsMap) {
            $dateA = $dsMap[(int) ($a->sec_sub_id ?? 0)]['exam_date'] ?? '';
            $dateB = $dsMap[(int) ($b->sec_sub_id ?? 0)]['exam_date'] ?? '';
            $validA = $dateA !== '' && $dateA !== '0000-00-00';
            $validB = $dateB !== '' && $dateB !== '0000-00-00';

            if ($validA && $validB && $dateA !== $dateB) {
                return strcmp($dateA, $dateB);
            }
            if ($validA !== $validB) {
                return $validA ? -1 : 1;
            }

            return strcasecmp((string) ($a->subject_name ?? ''), (string) ($b->subject_name ?? ''));
        });

        $existingRows = $this->db->table('datesheet')
            ->select('sec_sub_id, syllabus')
            ->where('eid', $eidInt)
            ->where('cls_sec_id', $cls_sec_id)
            ->get()->getResult();

        $existingMap = [];
        foreach ($existingRows as $r) {
            $existingMap[(int) $r->sec_sub_id] = (string) ($r->syllabus ?? '');
        }

        return view('admin/partials/syllabus_grid', compact('exam', 'cls_sec_id', 'subjects', 'existingMap', 'dsMap'));
    } catch (\Throwable $e) {
        log_message('error', 'fetchSyllabusGrid: ' . $e->getMessage());

        return $this->response->setBody('<div class="alert alert-danger mb-0">Failed to load syllabus grid. Please try again or contact support.</div>');
    }
}

/**
 * Unannounced exam (status = 0) for campus + session; campus-wide fallback.
 */
private function resolveExamForSyllabus(int $campusId, int $sessionId): ?object
{
    if ($campusId <= 0) {
        return null;
    }

    $builder = $this->db->table('exam')
        ->where('campus_id', $campusId)
        ->where('status', '0');

    if ($sessionId > 0) {
        $builder->where('session_id', $sessionId);
    }

    $exam = $builder->orderBy('exam_start_date', 'DESC')->get()->getRow();

    if (!$exam) {
        $exam = $this->db->table('exam')
            ->where('campus_id', $campusId)
            ->where('status', '0')
            ->orderBy('exam_start_date', 'DESC')
            ->get()->getRow();
    }

    return $exam ?: null;
}


public function loadTlp()
{
    if ($this->request->getMethod(true) !== 'POST') {
        return $this->response->setStatusCode(405)->setJSON([
            'success' => false, 'message' => 'Method Not Allowed'
        ]);
    }

    $campus_id  = (int) (session('member_campusid') ?? 0);
    $user_id    = (int) (session('member_userid') ?? 0);
    $cls_sec_id = (int) $this->request->getPost('cls_sec_id');
    $subject_id = (int) $this->request->getPost('subject_id');

    if ($campus_id <= 0 || $cls_sec_id <= 0 || $subject_id <= 0) {
        return $this->response->setJSON(['success'=>false,'message'=>'Missing params.']);
    }

    // 1) class_id from class_section
    $row = $this->db->table('class_section')
        ->select('class_id')
        ->where('cls_sec_id', $cls_sec_id)
        ->get()->getRow();
    $class_id = (int)($row->class_id ?? 0);
    if ($class_id <= 0) {
        return $this->response->setJSON(['success'=>false,'message'=>'Invalid class section.']);
    }

    // 2) Active exam for this campus ⇒ need eid, session_id, term_id
    $exam = $this->db->table('exam')
        ->select('eid, session_id, term_id')
        ->where('campus_id', $campus_id)
        ->where('status', '0')
        ->orderBy('exam_start_date', 'DESC')
        ->get()->getRow();
    if (!$exam) {
        return $this->response->setJSON(['success'=>false,'message'=>'No active exam found.']);
    }
    $eid        = (int)($exam->eid ?? 0);
    $session_id = (int)($exam->session_id ?? 0);
    $term_id    = (int)($exam->term_id ?? 0);
    if ($eid <= 0 || $session_id <= 0 || $term_id <= 0) {
        return $this->response->setJSON(['success'=>false,'message'=>'Exam missing eid/term/session.']);
    }

    // 3) term_session_id from terms_session
    $ts = $this->db->table('terms_session')
        ->select('term_session_id')
        ->where('session_id', $session_id)
        ->where('term_id',    $term_id)
        ->orderBy('term_session_id', 'DESC')
        ->get()->getRow();
    $term_session_id = (int)($ts->term_session_id ?? 0);
    if ($term_session_id <= 0) {
        return $this->response->setJSON(['success'=>false,'message'=>'No term_session found.']);
    }

    // 4) Objective from TLP (fallback to _v1)
    $tlp = $this->db->table('top_level_planning')
        ->select('objective')
        ->where([
            'campus_id'       => $campus_id,
            'class_id'        => $class_id,
            'subject_id'      => $subject_id,
            'term_session_id' => $term_session_id,
        ])->get()->getRow();

    if (!$tlp) {
        $tlp = $this->db->table('top_level_planning_v1')
            ->select('objective')
            ->where([
                'campus_id'       => $campus_id,
                'class_id'        => $class_id,
                'subject_id'      => $subject_id,
                'term_session_id' => $term_session_id,
            ])->get()->getRow();
    }
    if (!$tlp) {
        return $this->response->setJSON(['success'=>false,'message'=>'No TLP found for this subject.']);
    }

    $objectiveHtml = (string) $tlp->objective;

    // 5) sec_sub_id for the subject in this section
    $ss = $this->db->table('section_subjects')
        ->select('sec_sub_id')
        ->where('cls_sec_id', $cls_sec_id)
        ->where('subject_id', $subject_id)
        ->where('status', 1)
        ->orderBy('sec_sub_id', 'DESC')
        ->get()->getRow();
    $sec_sub_id = (int)($ss->sec_sub_id ?? 0);
    if ($sec_sub_id <= 0) {
        return $this->response->setJSON(['success'=>false,'message'=>'Subject not mapped to this section.']);
    }

    // 6) Upsert into datesheet (unique: eid, cls_sec_id, sec_sub_id)
    $now = date('Y-m-d H:i:s');
    $dsTbl = $this->db->table('datesheet');
    $existing = $dsTbl->select('did')
        ->where('eid', $eid)
        ->where('cls_sec_id', $cls_sec_id)
        ->where('sec_sub_id', $sec_sub_id)
        ->get()->getRow();

    if ($existing) {
        $dsTbl->where('did', (int)$existing->did)->update([
            'syllabus'    => $objectiveHtml,   // store raw HTML/text as-is
            'updated_date'=> $now,
            'user_id'     => $user_id,
        ]);
        $op = 'updated';
    } else {
        $dsTbl->insert([
            'eid'         => $eid,
            'cls_sec_id'  => $cls_sec_id,
            'sec_sub_id'  => $sec_sub_id,
            'syllabus'    => $objectiveHtml,
            'created_date'=> $now,
            'user_id'     => $user_id,
            'enable'      => 1,
        ]);
        $op = 'inserted';
    }

    // 7) Also return a textarea-friendly version
    $s = html_entity_decode($objectiveHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = preg_replace('~<br\s*/?>~i', "\n", $s);
    $s = preg_replace('~</(p|div|li|tr|h[1-6])>~i', "\n", $s);
    $s = preg_replace('~<li[^>]*>~i', "• ", $s);
    $s = strip_tags($s);
    $s = preg_replace("/[ \t\x{00A0}]+/u", " ", $s);
    $s = preg_replace("/\n{3,}/", "\n\n", $s);
    $s = trim($s);

    return $this->response->setJSON([
        'success'     => true,
        'message'     => "TLP $op in datesheet.",
        'eid'         => $eid,
        'sec_sub_id'  => $sec_sub_id,
        'syllabus'    => $s,            // put directly into <textarea>
    ]);
}


    public function saveSyllabus()
    {
        if (!$this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Method Not Allowed']);
        }

        $eid       = (int) $this->request->getPost('eid');
        $cls_sec_id= (int) $this->request->getPost('cls_sec_id');
        $sec_sub_id= (int) $this->request->getPost('sec_sub_id');
        $syllabus  = (string) $this->request->getPost('syllabus');

        if (!$eid || !$cls_sec_id || !$sec_sub_id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Missing required data.']);
        }

        try {
            // Upsert on the unique (eid, cls_sec_id, sec_sub_id)
            $exists = $this->db->table('datesheet')
                ->select('did')
                ->where('eid', $eid)
                ->where('cls_sec_id', $cls_sec_id)
                ->where('sec_sub_id', $sec_sub_id)
                ->get()->getRow();

            if ($exists) {
                $this->db->table('datesheet')
                    ->where('did', (int)$exists->did)
                    ->update(['syllabus' => $syllabus, 'updated_date' => date('Y-m-d H:i:s')]);
            } else {
                $this->db->table('datesheet')->insert([
                    'eid'         => $eid,
                    'cls_sec_id'  => $cls_sec_id,
                    'sec_sub_id'  => $sec_sub_id,
                    'syllabus'    => $syllabus,
                    'created_date'=> date('Y-m-d H:i:s'),
                    'updated_date'=> date('Y-m-d H:i:s'),
                ]);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Syllabus saved.']);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function saveSyllabusBulk()
    {
        if (!$this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Method Not Allowed']);
        }

        $eid       = (int) $this->request->getPost('eid');
        $cls_sec_id= (int) $this->request->getPost('cls_sec_id');
        $rows      = $this->request->getPost('rows'); // array of ['sec_sub_id'=>int, 'syllabus'=>string]

        if (!$eid || !$cls_sec_id || !is_array($rows)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Bad payload.']);
        }

        $now = date('Y-m-d H:i:s');

        try {
            foreach ($rows as $r) {
                $sec_sub_id = (int) ($r['sec_sub_id'] ?? 0);
                if ($sec_sub_id <= 0) continue;

                $syllabus = (string) ($r['syllabus'] ?? '');

                $exists = $this->db->table('datesheet')
                    ->select('did')
                    ->where('eid', $eid)
                    ->where('cls_sec_id', $cls_sec_id)
                    ->where('sec_sub_id', $sec_sub_id)
                    ->get()->getRow();

                if ($exists) {
                    $this->db->table('datesheet')
                        ->where('did', (int)$exists->did)
                        ->update(['syllabus' => $syllabus, 'updated_date' => $now]);
                } else {
                    $this->db->table('datesheet')->insert([
                        'eid'         => $eid,
                        'cls_sec_id'  => $cls_sec_id,
                        'sec_sub_id'  => $sec_sub_id,
                        'syllabus'    => $syllabus,
                        'created_date'=> $now,
                        'updated_date'=> $now,
                    ]);
                }
            }

            return $this->response->setJSON(['success' => true, 'message' => 'All entries saved.']);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }



function d_selectSubjects(){
    
        $campusid = $this->session->userdata('member_campusid');
        $sessionid = $this->session->userdata('member_sessionid');
        $section_id = $this->input->post('section_id');
      
        $this->db->where('cls_sec_id', $section_id);
        $this->db->where('status', 1);
        $subject_info = $this->db->get('section_subjects')->result(); 
                
        $eid = $this->input->post('eid');
        if(empty($eid)){
            echo "<div class='text-danger'>Exam is not selected</div><br>";
            exit;
        } 
        $this->db->where('eid', $eid);
        $examinfo = $this->db->get('exam')->row(); 
        if($examinfo){
        $examStartDate = DateTime::createFromFormat('Y-m-d' ,$examinfo->exam_start_date);
        $subjectexamdate = $examStartDate->format('d/m/Y');

        }else{
        $subjectexamdate = '';  
        }

        $this->db->where('term_id', $examinfo->term_id);
        $this->db->where('session_id', $sessionid);
        $terms_session_info = $this->db->get('terms_session')->row(); 

        $eeid = 0;
        $this->db->where('cls_sec_id', $section_id);
        $this->db->where('eid', $examinfo->eid);
        $examDatesheet = $this->db->get('datesheet')->row();
        if($examDatesheet){
         $eeid = $examDatesheet->eid;
        }
    
        $subjectList = '';
        {
        
        $subjectList .= '<input type="hidden" name="eeid"  value="'.$eeid.'">';
        $subjectList .= '<table class="table"><tr><th style="width:5%;">Subject</th><th  style="width:10%;">Total Marks</th><th style="width:17%;">Exam Date</th><th  style="width:50%;">Syllabus</th></tr>';
        $i = 1;

        foreach($subject_info as $subject){
            //print_r($subject);

            $this->db->where('cls_sec_id', $subject->cls_sec_id);
            $this->db->where('status', 1);
            $class_section_info = $this->db->get('class_section')->row(); 

            $this->db->where('sec_sub_id', $subject->sec_sub_id);
            $this->db->where('eid', $examinfo->eid);
            $datesheet_info = $this->db->get('datesheet')->row(); 
            $papersyllabus = '';
            $totalmarks = '';
        
            $i++;
            $did = 0;
            if($datesheet_info){
                $did = $datesheet_info->did;
                $papersyllabus = $datesheet_info->syllabus;
                $totalmarks = $datesheet_info->total_marks;
                
                
                
                $subjectexamdate = DateTime::createFromFormat('Y-m-d' ,$datesheet_info->exam_date);
                $subjectexamdate = $subjectexamdate->format('d/m/Y');

            }else{
                $this->db->where('subject_id', $subject->subject_id);
                $this->db->where('term_session_id', $terms_session_info->term_session_id);
                $this->db->where('class_id', $class_section_info->class_id);
                $this->db->where('campus_id', $campusid);
                $toplevelinfo = $this->db->get('top_level_planning')->row();
                if($toplevelinfo){
                    $papersyllabus = $toplevelinfo->objective;
                }
            }

            $this->db->where('sid', $subject->subject_id);
            $subjectinfo = $this->db->get('allsubject')->result_array();
            if(!empty($subjectinfo)){
            
            $subject_name = $subjectinfo[0]['subject_name'];
            $subject_id = $subjectinfo[0]['sid'];
                    
            $subjectList .= "<tr><td><input type='hidden' name='did[]'  value='".$did."'><input type='hidden' name='sec_sub_id[]'  value='".$subject->sec_sub_id."'>".$subject_name."</td><td><input type='text' name='total_marks[]' value='".$totalmarks."' class='form-control'></td><td>
                <div class='input-group date' id='datepicker".$subject->sec_sub_id."' data-target-input='nearest'>
                        <input type='text' name='exam_date[]'  value='".$subjectexamdate."'  class='form-control datetimepicker-input' data-bs-target='#datepicker".$subject->sec_sub_id."'/>
                        <span class='input-group-text' data-bs-target='#datepicker".$subject->sec_sub_id."' data-bs-toggle='datetimepicker'><i class='fa fa-calendar'></i></span>
                  </td><td><textarea name='syllabus[]' class='form-control editor222'>".$papersyllabus."</textarea></td></tr>
            <script>
                $(function(){
                 $('#datepicker".$subject->sec_sub_id."').datetimepicker({
                      format: 'DD/MM/YYYY',
                    });
                });
                $('.editor222').summernote();
                </script>";
            }
        
        }
    
        $subjectList .= "</table><script>
        $(document).ready(function() {
            // first row checkboxes
            $('tr td:first-child input[type=\"checkbox\"]').click( function() {
               $(this).closest('tr').find(\":input:not(:first)\").attr('disabled', !this.checked);
            });
        }); 
        </script>";
        }
        $this->output->set_output($subjectList);
        
    }

    public function add()
    { 
        check_permission('admin-datesheet');

        $campus_id = $this->session->get('member_campusid');
        $session_id = $this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();
        $currentrole = currentUserRoles();

        $sectionsclassinfo = in_array(5, $currentrole)
            ? teacherSubjectSections()
            : userClassSections();

        $cls_sec_id = $this->request->getGet('cls_sec_id') ?? ($sectionsclassinfo[0]['section_id'] ?? null);

        $exam = $this->db->table('exam')
            ->where('campus_id', $campus_id)
            ->where('session_id', $session_id)
            ->where('status', 0)
            ->orderBy('eid', 'DESC')
            ->get()->getRow();

        if (!$exam || !$cls_sec_id) {
            return view('admin/datesheet_edit', [
                'sectionsclassinfo' => $sectionsclassinfo,
                'cls_sec_id' => $cls_sec_id,
                'subjects' => [],
                'dateRange' => [],
                'existingMap' => [],
                'existingSyllabus' => [],
                'exam' => $exam,
                'schoolinfo' => $schoolinfo
            ]);
        }

        $start = strtotime($exam->exam_start_date);
        $end = strtotime($exam->exam_end_date);
        $dateRange = [];
        while ($start <= $end) {
            $dateRange[] = [
                'date' => date('Y-m-d', $start),
                'day' => date('l', $start)
            ];
            $start = strtotime('+1 day', $start);
        }

        $subjects = $this->db->table('section_subjects ss')
            ->select('ss.sec_sub_id, ss.subject_id, a.subject_name')
            ->join('allsubject a', 'a.sid = ss.subject_id')
            ->where('ss.cls_sec_id', $cls_sec_id)
            ->get()->getResult();

        foreach ($subjects as $i => $subj) {
            $teacherRow = $this->db->query("SELECT u.first_name FROM users u JOIN teacher_subjects ts ON ts.tid = u.id WHERE ts.sec_sub_id = ? AND ts.cls_sec_id = ? AND u.status = 1 LIMIT 1", [$subj->sec_sub_id, $cls_sec_id])->getRow();
            $subjects[$i]->teacher_name = $teacherRow->first_name ?? 'Not Assigned';
        }

        $existing = $this->db->table('datesheet')
            ->where('cls_sec_id', $cls_sec_id)
            ->where('eid', $exam->eid)
            ->get()->getResult();

        $existingMap = [];
        $existingSyllabus = [];
        foreach ($existing as $row) {
            $existingMap[$row->sec_sub_id] = [
                'exam_date' => $row->exam_date,
                'total_marks' => $row->total_marks
            ];
            $existingSyllabus[$row->sec_sub_id] = $row->syllabus;
        }

        return view('admin/datesheet_edit', [
            'sectionsclassinfo' => $sectionsclassinfo,
            'cls_sec_id' => $cls_sec_id,
            'subjects' => $subjects,
            'dateRange' => $dateRange,
            'existingMap' => $existingMap,
            'existingSyllabus' => $existingSyllabus,
            'exam' => $exam,
            'schoolinfo' => $schoolinfo
        ]);
    }

    public function savegrid()
    {
        check_permission('admin-add-datesheet');

        $cls_sec_id = $this->request->getPost('cls_sec_id');
        $exam_dates = $this->request->getPost('exam_date');
        $total_marks = $this->request->getPost('total_marks');

        $user_id = $this->session->get('member_userid');
        $campus_id = $this->session->get('member_campusid');
        $created_at = date('Y-m-d H:i:s');

        $exam = $this->db->table('exam')
            ->where('campus_id', $campus_id)
            ->where('session_id', $this->session->get('member_sessionid'))
            ->where('status', 0)
            ->orderBy('eid', 'DESC')
            ->get()->getRow();

        if (!$exam || !$cls_sec_id || empty($exam_dates)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Missing data or no active exam.'
            ]);
        }

        foreach ($exam_dates as $subject_id => $date) {
            $marks = $total_marks[$subject_id] ?? null;
            if (!$marks || !$date) continue;

            $exam_date = date('Y-m-d', strtotime($date));

            $sec_sub = $this->db->table('section_subjects')
                ->where('cls_sec_id', $cls_sec_id)
                ->where('subject_id', $subject_id)
                ->get()->getRow();

            if (!$sec_sub) continue;

            $existing = $this->db->table('datesheet')
                ->where('cls_sec_id', $cls_sec_id)
                ->where('sec_sub_id', $sec_sub->sec_sub_id)
                ->where('eid', $exam->eid)
                ->get()->getRow();

            $data = [
                'eid' => $exam->eid,
                'cls_sec_id' => $cls_sec_id,
                'sec_sub_id' => $sec_sub->sec_sub_id,
                'exam_date' => $exam_date,
                'total_marks' => $marks,
                'user_id' => $user_id,
                'updated_date' => $created_at
            ];

            if ($existing) {
                $this->db->table('datesheet')->where('did', $existing->did)->update($data);
            } else {
                $data['created_date'] = $created_at;
                $this->db->table('datesheet')->insert($data);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Datesheet saved successfully.'
        ]);
    }


// Admin/Datesheet.php (or your controller)
public function fetchsummary()
{
    return $this->dfetchsummary();
}

public function dfetchsummary()
{
    $exam_id   = (int) $this->request->getPost('exam_id');
    $campus_id = (int) $this->session->get('member_campusid');
    $session_id = (int) $this->session->get('member_sessionid');

    if ($exam_id <= 0) {
        // Prefer latest unannounced exam in current session/campus for insertion flow
        $active = $this->db->table('exam')
            ->select('eid')
            ->where('campus_id', $campus_id)
            ->where('session_id', $session_id)
            ->where('status', '0')
            ->orderBy('eid', 'DESC')
            ->get()->getRow();

        if ($active) {
            $exam_id = (int) ($active->eid ?? 0);
        } else {
            // Fallback: latest exam in current session/campus
            $latest = $this->db->table('exam')
                ->select('eid')
                ->where('campus_id', $campus_id)
                ->where('session_id', $session_id)
                ->orderBy('eid', 'DESC')
                ->get()->getRow();
            $exam_id = (int) ($latest->eid ?? 0);
        }
    }

    if ($exam_id <= 0) {
        return '<div class="alert alert-warning mb-0">No existing exam found for current session.</div>';
    }

    // 1) Enabled exam days for this exam
    $dayRows = $this->db->table('exam_days')
        ->select('exam_date')
        ->where('exam_id', $exam_id)
        ->where('is_on', 1)
        ->orderBy('exam_date', 'ASC')
        ->get()->getResult();

    if (empty($dayRows)) {
        return '<div class="alert alert-info mb-0">No enabled exam days defined for this exam.</div>';
    }

    $dateRange = [];
    foreach ($dayRows as $r) {
        $d = date('Y-m-d', strtotime($r->exam_date));
        $dateRange[] = ['date' => $d];
    }

    // 2) Resolve exam info for top heading
    $examInfo = $this->db->table('exam')
        ->select('eid, exam_name, status')
        ->where('eid', $exam_id)
        ->get()->getRowArray();

    // 3) All accessible class-sections (id list)
    $sectionsList = in_array(5, currentUserRoles()) ? teacherSubjectSections() : userClassSections();
    $sectionIds = [];
    foreach ($sectionsList as $row) {
        $id = is_array($row) ? ($row['cls_sec_id'] ?? $row['section_id'] ?? null) : ($row->cls_sec_id ?? $row->section_id ?? null);
        if ($id) $sectionIds[] = (int)$id;
    }
    $sectionIds = array_values(array_unique($sectionIds));
    if (empty($sectionIds)) {
        return '<div class="alert alert-info mb-0">No class sections available for summary.</div>';
    }

    // 4) Pull all scheduled entries for this exam across sections (subject names)
    $rows = $this->db->table('datesheet ds')
        ->select('ds.cls_sec_id, ds.exam_date, a.subject_name')
        ->join('section_subjects ss', 'ss.sec_sub_id = ds.sec_sub_id')
        ->join('allsubject a', 'a.sid = ss.subject_id')
        ->where('ds.eid', $exam_id)
        ->whereIn('ds.cls_sec_id', $sectionIds)
        ->get()->getResult();

    if (empty($rows)) {
        return '<div class="alert alert-info mb-0">No datesheet entries exist yet for this exam.</div>';
    }

    // Only show classes that already have at least one datesheet row
    $activeSectionIds = [];
    foreach ($rows as $r) {
        $activeSectionIds[(int) $r->cls_sec_id] = true;
    }
    $activeSectionIds = array_keys($activeSectionIds);

    // 5) Build matrix: [cls_sec_id][Y-m-d] => [subject_name, ...]
    $matrix = [];
    foreach ($activeSectionIds as $sid) {
        foreach ($dateRange as $d) {
            $matrix[$sid][$d['date']] = [];
        }
    }
    foreach ($rows as $r) {
        $day = date('Y-m-d', strtotime($r->exam_date));
        if (!isset($matrix[(int)$r->cls_sec_id][$day])) {
            $matrix[(int)$r->cls_sec_id][$day] = [];
        }
        $matrix[(int)$r->cls_sec_id][$day][] = $r->subject_name;
    }

    // Use DB short names for class-section labels
    $visibleLabels = [];
    $labelRows = $this->db->table('class_section cs')
        ->select('cs.cls_sec_id, c.class_name, c.class_short_name, sec.section_name, sec.short_name AS section_short_name')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->whereIn('cs.cls_sec_id', $activeSectionIds)
        ->get()->getResultArray();
    foreach ($labelRows as $lr) {
        $sid = (int)($lr['cls_sec_id'] ?? 0);
        if ($sid <= 0) continue;
        $cls = trim((string)($lr['class_short_name'] ?? '')) !== '' ? (string)$lr['class_short_name'] : (string)($lr['class_name'] ?? '');
        $sec = trim((string)($lr['section_short_name'] ?? '')) !== '' ? (string)$lr['section_short_name'] : (string)($lr['section_name'] ?? '');
        $visibleLabels[$sid] = trim($cls . ' - ' . $sec, ' -');
    }
    foreach ($activeSectionIds as $sid) {
        if (!isset($visibleLabels[$sid])) {
            $visibleLabels[$sid] = 'Sec#' . $sid;
        }
    }

    return view('admin/d_datesheet_summary', [
        'dateRange'     => $dateRange,
        'sectionLabels' => $visibleLabels,
        'matrix'        => $matrix,
        'examInfo'      => $examInfo,
    ]);
}



   public function fetchgrid()
{
    helper('text');

    $cls_sec_id = (int) $this->request->getPost('cls_sec_id');
    $exam_id    = (int) $this->request->getPost('exam_id'); // ✅ specific exam
    $campus_id  = (int) $this->session->get('member_campusid');
    $session_id = (int) $this->session->get('member_sessionid');

    // Pick the requested exam, or fall back to latest unannounced exam for current session/campus
    $examQB = $this->db->table('exam')
        ->where('campus_id', $campus_id)
        ->where('session_id', $session_id)
        ->where('status', 0);

    if ($exam_id > 0) {
        $examQB->where('eid', $exam_id);
    }

    $exam = $examQB->orderBy('eid', 'DESC')->get()->getRow();

    if (!$exam || $cls_sec_id <= 0) {
        return '<div class="alert alert-danger mb-0">No active exam or class section selected.</div>';
    }

    // ✅ Only the exam days that are explicitly turned ON for this exam
    // expected table schema: exam_days(eid, exam_date, is_on)
    $dayRows = $this->db->table('exam_days')
        ->select('exam_date')
        ->where('exam_id', $exam->eid)
        ->where('is_on', 1)
        ->orderBy('exam_date', 'ASC')
        ->get()->getResult();

    if (empty($dayRows)) {
        // optional fallback (comment out if you want hard fail):
        // return '<div class="alert alert-warning mb-0">No exam days are enabled for the selected exam.</div>';
        return '<div class="alert alert-warning mb-0">No exam days (is_on=1) found for this exam.</div>';
    }

    $dateRange = [];
    foreach ($dayRows as $r) {
        $ts = strtotime($r->exam_date);
        $dateRange[] = [
            'date' => date('Y-m-d', $ts),
            'day'  => date('l', $ts),
        ];
    }

    // Subjects in this class-section
    $subjects = $this->db->table('section_subjects ss')
        ->select('ss.sec_sub_id, ss.subject_id, a.subject_name, a.subject_short_name')
        ->join('allsubject a', 'a.sid = ss.subject_id')
        ->where('ss.cls_sec_id', $cls_sec_id)
        ->where('ss.status', 1)
        ->orderBy('a.subject_name', 'ASC')
        ->get()->getResult();

    // Attach teacher (if any)
    foreach ($subjects as $i => $subj) {
        $teacherRow = $this->db->query(
            "SELECT u.first_name 
               FROM users u 
               JOIN teacher_subjects ts ON ts.tid = u.id 
              WHERE ts.sec_sub_id = ? AND ts.cls_sec_id = ? AND u.status = 1 
              LIMIT 1",
            [$subj->sec_sub_id, $cls_sec_id]
        )->getRow();
        $subjects[$i]->teacher_name = $teacherRow->first_name ?? 'Not Assigned';
    }

    // Existing dates & marks saved
    $existing = $this->db->table('datesheet')
        ->where('cls_sec_id', $cls_sec_id)
        ->where('eid', $exam->eid)
        ->get()->getResult();

    $existingMap = [];
    foreach ($existing as $row) {
        // keyed by sec_sub_id
        $existingMap[$row->sec_sub_id] = [
            'exam_date'   => $row->exam_date,
            'total_marks' => $row->total_marks,
        ];
    }

    return view('admin/datesheet_grid', [
        'dateRange'   => $dateRange,
        'subjects'    => $subjects,
        'existingMap' => $existingMap,
        'exam'        => $exam,
    ]);
}



    public function saveSingle()
    {
        $cls_sec_id = $this->request->getPost('cls_sec_id');
        $sec_sub_id = $this->request->getPost('sec_sub_id');
        $exam_id = (int) $this->request->getPost('exam_id');
        $exam_date = $this->request->getPost('exam_date');
        $total_marks = $this->request->getPost('total_marks');

        $user_id = $this->session->get('member_userid');
        $campus_id = $this->session->get('member_campusid');
        $session_id = $this->session->get('member_sessionid');
        $created_at = date('Y-m-d H:i:s');

        $examQB = $this->db->table('exam')
            ->where('campus_id', $campus_id)
            ->where('session_id', $session_id)
            ->where('status', 0);

        if ($exam_id > 0) {
            $examQB->where('eid', $exam_id);
        }

        $exam = $examQB->orderBy('eid', 'DESC')->get()->getRow();

        if (!$exam) {
            return $this->response->setJSON(['success' => false, 'message' => 'No active exam found.']);
        }

        $exam_date = date('Y-m-d', strtotime($exam_date));

        $existing = $this->db->table('datesheet')
            ->where('cls_sec_id', $cls_sec_id)
            ->where('sec_sub_id', $sec_sub_id)
            ->where('eid', $exam->eid)
            ->get()->getRow();

        $data = [
            'eid' => $exam->eid,
            'cls_sec_id' => $cls_sec_id,
            'sec_sub_id' => $sec_sub_id,
            'exam_date' => $exam_date,
            'total_marks' => $total_marks,
            'user_id' => $user_id,
            'updated_date' => $created_at
        ];

        if ($existing) {
            $this->db->table('datesheet')->where('did', $existing->did)->update($data);
        } else {
            $data['created_date'] = $created_at;
            $this->db->table('datesheet')->insert($data);
        }

        
        return $this->response->setJSON(['success' => true, 'message' => 'Saved']);
    }
}
