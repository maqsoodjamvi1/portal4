<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class CampusManagement extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form']);
        check_permission('admin-permissions');
    }

    public function index()
    {
        $data['schools'] = $this->getSchools();
        $data['statuses'] = [
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'expired', 'label' => 'Expired'],
            ['value' => 'inactive', 'label' => 'Inactive']
        ];
        return view('admin/campus_management', $data);
    }


    public function bulkDelete()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(403)->setJSON([
            'success' => false,
            'msg' => 'Invalid request'
        ]);
    }

    $campusIds = $this->request->getPost('campus_ids');
    
    if (empty($campusIds) || !is_array($campusIds)) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'No campuses selected for deletion'
        ]);
    }

    // Start transaction
    $this->db->transBegin();

    try {
        $deletedCampuses = [];
        $totalStudentsDeleted = 0;
        $totalStaffDeleted = 0;
        $totalFilesDeleted = 0;
        $failedCampuses = [];

        foreach ($campusIds as $campusId) {
            $campusId = (int)$campusId;
            
            // Check if campus exists
            $campus = $this->db->table('campus')->where('campus_id', $campusId)->get()->getRow();
            if (!$campus) {
                $failedCampuses[] = "Campus ID {$campusId} not found";
                continue;
            }

            // Get campus info for logging
            $schoolInfo = $this->db->table('system')
                ->where('system_id', $campus->system_id)
                ->get()
                ->getRow();

            // ========== COLLECT FILE PATHS BEFORE DELETING RECORDS ==========
            $photoPaths = [];
            
            // Get all student photo paths
            $studentPhotos = $this->db->table('students')
                ->select('profile_photo')
                ->where('campus_id', $campusId)
                ->where('profile_photo IS NOT NULL')
                ->where('profile_photo !=', '')
                ->get()
                ->getResult();
            
            foreach ($studentPhotos as $photo) {
                if (!empty($photo->profile_photo)) {
                    $photoPaths[] = $photo->profile_photo;
                }
            }
            
            // Get all staff photo paths
            $staffPhotos = $this->db->table('users')
                ->select('photo')
                ->where('campus_id', $campusId)
                ->where('photo IS NOT NULL')
                ->where('photo !=', '')
                ->get()
                ->getResult();
            
            foreach ($staffPhotos as $photo) {
                if (!empty($photo->photo)) {
                    $photoPaths[] = $photo->photo;
                }
            }
            
            // Get all students in this campus
            $students = $this->db->table('students')
                ->select('student_id')
                ->where('campus_id', $campusId)
                ->get()
                ->getResult();
            
            $studentIds = array_column($students, 'student_id');
            $totalStudentsDeleted += count($studentIds);
            
            // Get all staff/users in this campus
            $staff = $this->db->table('users')
                ->select('id')
                ->where('campus_id', $campusId)
                ->get()
                ->getResult();
            
            $staffIds = array_column($staff, 'id');
            $totalStaffDeleted += count($staffIds);

            // Delete student related records
            if (!empty($studentIds)) {
                $studentIdsList = implode(',', $studentIds);
                
                $studentTables = [
                    'a_student_subjects', 'student_class', 'fee_chalan', 'attendance',
                    'd_exam_results', 'subject_results', 'test_results', 'leave_applications',
                    'student_notices', 'h_student_bed', 'vehicle_students', 'quiz_attempts',
                    'student_quiz_levels', 'ai_quiz_decisions', 'complaints', 'attachements',
                    'school_leaving_certificates'
                ];
                
                foreach ($studentTables as $table) {
                    $this->db->query("DELETE FROM {$table} WHERE student_id IN ({$studentIdsList})");
                }
                
                // Delete students
                $this->db->query("DELETE FROM students WHERE student_id IN ({$studentIdsList})");
            }
            
            // Delete staff related records
            if (!empty($staffIds)) {
                $staffIdsList = implode(',', $staffIds);
                
                $staffTables = [
                    'attendance_employee', 'monthly_attendance_summary', 'salary_slips',
                    'advance_salaries', 'bonuses', 'deduction_exceptions', 'employee_salary_rules',
                    'security_deposits', 'teacher_qr_codes', 'teacher_section', 'teacher_subjects',
                    'employee_leaves', 'employees_leave_applications', 'emp_timings', 'emp_salary',
                    'user_perms', 'user_roles'
                ];
                
                foreach ($staffTables as $table) {
                    $this->db->query("DELETE FROM {$table} WHERE user_id IN ({$staffIdsList}) OR emp_id IN ({$staffIdsList}) OR teacher_id IN ({$staffIdsList}) OR tid IN ({$staffIdsList})");
                }
                
                // Delete messages
                $this->db->query("DELETE FROM messages WHERE sender_id IN ({$staffIdsList}) OR receiver_id IN ({$staffIdsList})");
                $this->db->query("DELETE FROM chat_box WHERE sender_id IN ({$staffIdsList}) OR receiver_id IN ({$staffIdsList})");
                
                // Delete users
                $this->db->query("DELETE FROM users WHERE id IN ({$staffIdsList})");
            }
            
            // Delete parents
            $parents = $this->db->table('parents')
                ->select('parent_id')
                ->where('campus_id', $campusId)
                ->get()
                ->getResult();
            
            if (!empty($parents)) {
                $parentIdsList = implode(',', array_column($parents, 'parent_id'));
                $this->db->query("DELETE FROM parents WHERE parent_id IN ({$parentIdsList})");
                $this->db->query("DELETE FROM communication_logs WHERE parent_id IN ({$parentIdsList})");
                $this->db->query("DELETE FROM whatsapp_log WHERE parent_id IN ({$parentIdsList})");
            }
            
            // Delete class sections
            $classSections = $this->db->table('class_section')
                ->select('cls_sec_id')
                ->where('campus_id', $campusId)
                ->get()
                ->getResult();
            
            if (!empty($classSections)) {
                $clsSecIdsList = implode(',', array_column($classSections, 'cls_sec_id'));
                
                $sectionTables = [
                    'section_subjects', 'classdairy', 'school_timings', 'time_table', 'mark_attendance'
                ];
                
                foreach ($sectionTables as $table) {
                    $this->db->query("DELETE FROM {$table} WHERE cls_sec_id IN ({$clsSecIdsList})");
                }
                
                $this->db->query("DELETE FROM class_section WHERE cls_sec_id IN ({$clsSecIdsList})");
            }
            
            // Delete other campus related records
            $deleteTables = [
                'a_class_subjects', 'a_group_teacher', 'a_subject_group', 'a_groups', 'a_subject', 'a_classes',
                'fee_amount', 'h_fee_amount', 'fee_plan_months', 'invoices',
                'h_block_rooms', 'h_blocks', 'h_checkinout', 'h_rooms', 'h_room_beds', 'h_beds',
                'vehicles', 'expenses', 'assets', 'exp_estimation', 'income_estimation',
                'campus_chalan', 'campus_bills', 'attendance_settings', 'campus_wifi_rules',
                'campus_salary_settings', 'campus_flags', 'notices', 'meetings', 'sms', 'sms_settings',
                'pdf_documents', 'slots', 'sports_event_entries', 'sports_event_results',
                'sports_events', 'sports_team_members', 'sports_teams', 'sports_scoring_rules',
                'sports_houses', 'quiz_questions', 'quiz_attempt_answers', 'quiz_attempt_questions',
                'quiz_impersonation_tokens', 'quiz_levels', 'quiz_level_attempts', 'quiz_targets',
                'quiz_topics', 'quizzes', 'qb_questions', 'qb_topics', 'vocab_bank', 'vocab_topics',
                'weekly_planning', 'wp_std_weeekly_progress', 'wp_sub_objectives',
                'top_level_planning', 'top_level_planning_v1', 'term_weeks', 'terms_session',
                'datesheet', 'tests', 'test_series_subject_results', 'test_results_compiled',
                'exam_days', 'exam', 'admission_enquiry', 'user_form_prefs', 'user_menu_prefs',
                'user_view_prefs', 'user_privileges', 'import_jobs'
            ];
            
            foreach ($deleteTables as $table) {
                $this->db->table($table)->where('campus_id', $campusId)->delete();
            }
            
            // Delete physical files
            $deletedFiles = 0;
            foreach ($photoPaths as $filePath) {
                $cleanPath = $this->cleanFilePath($filePath);
                $fullPath = $this->uploadPath . $cleanPath;
                
                if (file_exists($fullPath) && is_file($fullPath)) {
                    if (@unlink($fullPath)) {
                        $deletedFiles++;
                    }
                }
            }
            $totalFilesDeleted += $deletedFiles;
            
            // Delete campus
            $this->db->table('campus')->where('campus_id', $campusId)->delete();
            
            // Log deletion
            $this->db->table('changes')->insert([
                'heading' => 'Campus Deleted (Bulk)',
                'detail' => "Campus '{$campus->campus_name}' (ID: {$campusId}) from School '{$schoolInfo->system_name}' was deleted with " . count($studentIds) . " students and " . count($staffIds) . " staff members.",
                'changing_date' => date('Y-m-d H:i:s'),
                'user_name' => session('member_username'),
                'user_id' => session('member_userid'),
                'campus_id' => $campusId,
                'created_date' => date('Y-m-d H:i:s')
            ]);
            
            $deletedCampuses[] = $campus->campus_name;
        }

        $this->db->transCommit();

        $message = "Successfully deleted " . count($deletedCampuses) . " campus(es):<br>" . 
                   implode("<br>", $deletedCampuses) . "<br><br>" .
                   "Total students deleted: {$totalStudentsDeleted}<br>" .
                   "Total staff deleted: {$totalStaffDeleted}<br>" .
                   "Total files deleted: {$totalFilesDeleted}";
        
        if (!empty($failedCampuses)) {
            $message .= "<br><br><span class='text-warning'>Failed: " . implode(", ", $failedCampuses) . "</span>";
        }

        return $this->response->setJSON([
            'success' => true,
            'msg' => $message
        ]);

    } catch (\Exception $e) {
        $this->db->transRollback();
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error deleting campuses: ' . $e->getMessage()
        ]);
    }
}

    public function data()
    {
        $request = $this->request;
        $schoolinfo = getSchoolInfo();
        $campusid = $this->session->get('member_campusid');

        $schoolId = $request->getPost('school_id') ?? '';
        $statusFilter = $request->getPost('status') ?? '';
        $keyword = $request->getPost('search')['value'] ?? '';

        $builder = $this->db->table('campus c')
            ->select("
                c.campus_id,
                c.campus_name,
                c.short_name AS campus_short_name,
                c.location AS campus_address,
                c.landline AS campus_landline,
                c.mobile_no AS campus_mobile,
                c.currency_code,
                c.website,
                c.created_date,
                s.system_id,
                s.system_name AS school_name,
                s.address AS school_address,
                s.city,
                s.country,
                s.landline_number,
                s.mob_number,
                s.owner_name,
                cb.bill_id,
                cb.plan_id,
                cb.bill_amount,
                cb.bill_status,
                cb.campus_expiry,
                cb.paid_date,
                cb.bill_issue_date,
                cb.status AS bill_status_value,
                CASE 
                    WHEN cb.status = 1 AND cb.campus_expiry >= CURDATE() THEN 'active'
                    WHEN cb.status = 1 AND cb.campus_expiry < CURDATE() THEN 'expired'
                    ELSE 'inactive'
                END AS campus_status
            ")
            ->join('system s', 'c.system_id = s.system_id', 'left')
            ->join('campus_bills cb', 'c.campus_id = cb.campus_id AND cb.status = 1', 'left')
            ->where('c.campus_id IS NOT NULL');

        // Apply filters
        if (!empty($schoolId)) {
            $builder->where('s.system_id', $schoolId);
        }

        if (!empty($statusFilter)) {
            if ($statusFilter === 'active') {
                $builder->where('cb.status', 1)->where('cb.campus_expiry >=', date('Y-m-d'));
            } elseif ($statusFilter === 'expired') {
                $builder->where('cb.status', 1)->where('cb.campus_expiry <', date('Y-m-d'));
            } elseif ($statusFilter === 'inactive') {
                $builder->where('(cb.status IS NULL OR cb.status != 1)');
            }
        }

        if (!empty($keyword)) {
            $builder->groupStart()
                ->like('s.system_name', $keyword)
                ->orLike('c.campus_name', $keyword)
                ->orLike('c.location', $keyword)
                ->groupEnd();
        }

        // Get total count before pagination
        $total = $builder->countAllResults(false);

        // Get paginated results
        $results = $builder
            ->orderBy('s.system_name', 'asc')
            ->orderBy('c.campus_name', 'asc')
            ->limit($request->getPost('length'), $request->getPost('start'))
            ->get()
            ->getResult();

        $data = [];
        $start = (int)$request->getPost('start');
        $count = $start + 1;

        foreach ($results as $row) {
            // Get student count
            $studentCount = $this->db->table('students')
                ->where('campus_id', $row->campus_id)
                ->where('status', '1')
                ->countAllResults();

            // Get staff count
            $staffCount = $this->db->table('users')
                ->where('campus_id', $row->campus_id)
                ->where('status', 1)
                ->countAllResults();

            $data[] = [
                'sno' => $count++,
                'campus_id' => $row->campus_id,
                'school_name' => $row->school_name ?? 'N/A',
                'campus_name' => $row->campus_name,
                'campus_address' => $row->campus_address ?? 'N/A',
                'contact' => $row->campus_landline ?: ($row->campus_mobile ?: 'N/A'),
                'campus_status' => $row->campus_status ?? 'inactive',
                'total_students' => $studentCount,
                'total_staff' => $staffCount,
                'campus_expiry' => $row->campus_expiry,
                'currency_code' => $row->currency_code ?? 'PKR',
                'website' => $row->website ?? '',
                'system_id' => $row->system_id
            ];
        }

        return $this->response->setJSON([
            'draw' => $request->getPost('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ]);
    }
public function delete()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(403)->setJSON([
            'success' => false,
            'msg' => 'Invalid request'
        ]);
    }

    $campusId = (int) $this->request->getPost('campus_id');
    
    // Check if campus exists
    $campus = $this->db->table('campus')->where('campus_id', $campusId)->get()->getRow();
    if (!$campus) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Campus not found'
        ]);
    }

    // Start transaction
    $this->db->transBegin();

    try {
        // Get campus info for logging
        $schoolInfo = $this->db->table('system')
            ->where('system_id', $campus->system_id)
            ->get()
            ->getRow();

        // Get all students in this campus (including active ones)
        $students = $this->db->table('students')
            ->select('student_id')
            ->where('campus_id', $campusId)
            ->get()
            ->getResult();
        
        $studentIds = array_column($students, 'student_id');
        
        // Get all staff/users in this campus (including active ones)
        $staff = $this->db->table('users')
            ->select('id')
            ->where('campus_id', $campusId)
            ->get()
            ->getResult();
        
        $staffIds = array_column($staff, 'id');

        // ========== DELETE STUDENT RELATED RECORDS FIRST ==========
        if (!empty($studentIds)) {
            $studentIdsList = implode(',', $studentIds);
            
            // Student subjects
            $this->db->query("DELETE FROM a_student_subjects WHERE student_id IN ({$studentIdsList})");
            
            // Student class records
            $this->db->query("DELETE FROM student_class WHERE student_id IN ({$studentIdsList})");
            
            // Fee challans
            $this->db->query("DELETE FROM fee_chalan WHERE student_id IN ({$studentIdsList})");
            
            // Attendance records
            $this->db->query("DELETE FROM attendance WHERE student_id IN ({$studentIdsList})");
            
            // Exam results
            $this->db->query("DELETE FROM d_exam_results WHERE student_id IN ({$studentIdsList})");
            
            // Subject results
            $this->db->query("DELETE FROM subject_results WHERE student_id IN ({$studentIdsList})");
            
            // Test results
            $this->db->query("DELETE FROM test_results WHERE student_id IN ({$studentIdsList})");
            
            // Leave applications
            $this->db->query("DELETE FROM leave_applications WHERE student_id IN ({$studentIdsList})");
            
            // Student notices
            $this->db->query("DELETE FROM student_notices WHERE std_id IN ({$studentIdsList})");
            
            // Hostel student beds
            $this->db->query("DELETE FROM h_student_bed WHERE student_id IN ({$studentIdsList})");
            
            // Vehicle students
            $this->db->query("DELETE FROM vehicle_students WHERE student_id IN ({$studentIdsList})");
            
            // Quiz attempts
            $this->db->query("DELETE FROM quiz_attempts WHERE student_id IN ({$studentIdsList})");
            $this->db->query("DELETE FROM student_quiz_levels WHERE student_id IN ({$studentIdsList})");
            
            // AI quiz decisions
            $this->db->query("DELETE FROM ai_quiz_decisions WHERE student_id IN ({$studentIdsList})");
            
            // Complaints
            $this->db->query("DELETE FROM complaints WHERE student_id IN ({$studentIdsList})");
            
            // Attachments
            $this->db->query("DELETE FROM attachements WHERE student_id IN ({$studentIdsList})");
            
            // School leaving certificates
            $this->db->query("DELETE FROM school_leaving_certificates WHERE student_id IN ({$studentIdsList})");
            
            // Finally delete students
            $this->db->query("DELETE FROM students WHERE student_id IN ({$studentIdsList})");
        }
        
        // ========== DELETE STAFF/USER RELATED RECORDS ==========
        if (!empty($staffIds)) {
            $staffIdsList = implode(',', $staffIds);
            
            // Employee attendance
            $this->db->query("DELETE FROM attendance_employee WHERE emp_id IN ({$staffIdsList})");
            
            // Monthly attendance summary
            $this->db->query("DELETE FROM monthly_attendance_summary WHERE user_id IN ({$staffIdsList})");
            
            // Salary slips
            $this->db->query("DELETE FROM salary_slips WHERE user_id IN ({$staffIdsList})");
            
            // Advance salaries
            $this->db->query("DELETE FROM advance_salaries WHERE user_id IN ({$staffIdsList})");
            
            // Bonuses
            $this->db->query("DELETE FROM bonuses WHERE user_id IN ({$staffIdsList})");
            
            // Deduction exceptions
            $this->db->query("DELETE FROM deduction_exceptions WHERE user_id IN ({$staffIdsList})");
            
            // Employee salary rules
            $this->db->query("DELETE FROM employee_salary_rules WHERE user_id IN ({$staffIdsList})");
            
            // Security deposits
            $this->db->query("DELETE FROM security_deposits WHERE user_id IN ({$staffIdsList})");
            
            // Teacher QR codes
            $this->db->query("DELETE FROM teacher_qr_codes WHERE teacher_id IN ({$staffIdsList})");
            
            // Teacher sections
            $this->db->query("DELETE FROM teacher_section WHERE tid IN ({$staffIdsList})");
            
            // Teacher subjects
            $this->db->query("DELETE FROM teacher_subjects WHERE tid IN ({$staffIdsList})");
            
            // Employee leaves
            $this->db->query("DELETE FROM employee_leaves WHERE employee_id IN ({$staffIdsList})");
            $this->db->query("DELETE FROM employees_leave_applications WHERE emp_id IN ({$staffIdsList})");
            
            // Employee timings
            $this->db->query("DELETE FROM emp_timings WHERE user_id IN ({$staffIdsList})");
            
            // Employee salary
            $this->db->query("DELETE FROM emp_salary WHERE emp_id IN ({$staffIdsList})");
            
            // Messages
            $this->db->query("DELETE FROM messages WHERE sender_id IN ({$staffIdsList}) OR receiver_id IN ({$staffIdsList})");
            
            // Chat box
            $this->db->query("DELETE FROM chat_box WHERE sender_id IN ({$staffIdsList}) OR receiver_id IN ({$staffIdsList})");
            
            // User permissions
            $this->db->query("DELETE FROM user_perms WHERE userID IN ({$staffIdsList})");
            $this->db->query("DELETE FROM user_roles WHERE userID IN ({$staffIdsList})");
            
            // Finally delete users
            $this->db->query("DELETE FROM users WHERE id IN ({$staffIdsList})");
        }
        
        // ========== DELETE PARENTS RELATED RECORDS ==========
        // Get parents for this campus
        $parents = $this->db->table('parents')
            ->select('parent_id')
            ->where('campus_id', $campusId)
            ->get()
            ->getResult();
        
        if (!empty($parents)) {
            $parentIdsList = implode(',', array_column($parents, 'parent_id'));
            $this->db->query("DELETE FROM parents WHERE parent_id IN ({$parentIdsList})");
            $this->db->query("DELETE FROM communication_logs WHERE parent_id IN ({$parentIdsList})");
            $this->db->query("DELETE FROM whatsapp_log WHERE parent_id IN ({$parentIdsList})");
        }
        
        // ========== DELETE CLASS/SECTION RELATED RECORDS ==========
        // Get class sections for this campus
        $classSections = $this->db->table('class_section')
            ->select('cls_sec_id')
            ->where('campus_id', $campusId)
            ->get()
            ->getResult();
        
        if (!empty($classSections)) {
            $clsSecIdsList = implode(',', array_column($classSections, 'cls_sec_id'));
            
            // Section subjects
            $this->db->query("DELETE FROM section_subjects WHERE cls_sec_id IN ({$clsSecIdsList})");
            
            // Class dairy
            $this->db->query("DELETE FROM classdairy WHERE cls_sec_id IN ({$clsSecIdsList})");
            
            // School timings
            $this->db->query("DELETE FROM school_timings WHERE cls_sec_id IN ({$clsSecIdsList})");
            
            // Time table
            $this->db->query("DELETE FROM time_table WHERE cls_sec_id IN ({$clsSecIdsList})");
            
            // Mark attendance
            $this->db->query("DELETE FROM mark_attendance WHERE cls_sec_id IN ({$clsSecIdsList})");
            
            // Delete class sections
            $this->db->query("DELETE FROM class_section WHERE cls_sec_id IN ({$clsSecIdsList})");
        }
        
        // ========== DELETE OTHER CAMPUS RELATED RECORDS ==========
        $deleteTables = [
            // Academic
            'a_class_subjects',
            'a_group_teacher',
            'a_subject_group',
            'a_groups',
            'a_subject',
            'a_classes',
            
            // Fee related
            'fee_amount',
            'h_fee_amount',
            'fee_plan_months',
            'invoice_sequencesdd',
            'invoices',
            
            // Hostel related
            'h_block_rooms',
            'h_blocks',
            'h_checkinout',
            'h_rooms',
            'h_room_beds',
            'h_beds',
            
            // Vehicle
            'vehicles',
            
            // Expenses and assets
            'expenses',
            'assets',
            'exp_estimation',
            'income_estimation',
            
            // Billing
            'campus_chalan',
            'campus_bills',
            
            // Attendance settings
            'attendance_settings',
            'campus_wifi_rules',
            'campus_salary_settings',
            'campus_flags',
            
            // Notices
            'notices',
            'meetings',
            
            // SMS logs
            'sms',
            'sms_settings',
            
            // PDF documents
            'pdf_documents',
            
            // Slots
            'slots',
            
            // Sports
            'sports_event_entries',
            'sports_event_results',
            'sports_events',
            'sports_team_members',
            'sports_teams',
            'sports_scoring_rules',
            'sports_houses',
            
            // Quiz
            'quiz_questions',
            'quiz_attempt_answers',
            'quiz_attempt_questions',
            'quiz_attempts',
            'quiz_impersonation_tokens',
            'quiz_levels',
            'quiz_level_attempts',
            'quiz_targets',
            'quiz_topics',
            'quizzes',
            
            // Question bank
            'qb_questions',
            'qb_topics',
            
            // Vocabulary
            'vocab_bank',
            'vocab_topics',
            
            // Weekly planning
            'weekly_planning',
            'wp_std_weeekly_progress',
            'wp_sub_objectives',
            'top_level_planning',
            'top_level_planning_v1',
            
            // Terms
            'term_weeks',
            'terms_session',
            
            // Datesheet
            'datesheet',
            'tests',
            'test_series_subject_results',
            'test_results_compiled',
            
            // Exams
            'exam_days',
            'exam',
            
            // Admission enquiry
            'admission_enquiry',
            
            // User preferences
            'user_form_prefs',
            'user_menu_prefs',
            'user_view_prefs',
            'user_privileges',
            
            // Import jobs
            'import_jobs',
            
            // Finally campus
            'campus'
        ];
        
        foreach ($deleteTables as $table) {
            $this->db->table($table)->where('campus_id', $campusId)->delete();
        }

        // Log deletion
        $this->db->table('changes')->insert([
            'heading' => 'Campus Deleted',
            'detail' => "Campus '{$campus->campus_name}' (ID: {$campusId}) from School '{$schoolInfo->system_name}' was deleted with all associated records including " . count($studentIds) . " students and " . count($staffIds) . " staff members.",
            'changing_date' => date('Y-m-d H:i:s'),
            'user_name' => session('member_username'),
            'user_id' => session('member_userid'),
            'campus_id' => $campusId,
            'created_date' => date('Y-m-d H:i:s')
        ]);

        $this->db->transCommit();

        return $this->response->setJSON([
            'success' => true,
            'msg' => "Campus '{$campus->campus_name}' and all associated records (including " . count($studentIds) . " students and " . count($staffIds) . " staff) have been permanently deleted."
        ]);

    } catch (\Exception $e) {
        $this->db->transRollback();
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error deleting campus: ' . $e->getMessage()
        ]);
    }
}


    public function getDetails()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'msg' => 'Invalid request'
            ]);
        }

        $campusId = (int) $this->request->getPost('campus_id');

        // Get campus details
        $campus = $this->db->table('campus c')
            ->select("
                c.*,
                s.system_name,
                s.address AS school_address,
                s.city,
                s.country,
                s.landline_number,
                s.mob_number,
                s.owner_name,
                cb.bill_amount,
                cb.bill_status,
                cb.campus_expiry,
                cb.paid_date,
                cb.bill_issue_date,
                cb.plan_id,
                CASE 
                    WHEN cb.status = 1 AND cb.campus_expiry >= CURDATE() THEN 'active'
                    WHEN cb.status = 1 AND cb.campus_expiry < CURDATE() THEN 'expired'
                    ELSE 'inactive'
                END AS campus_status
            ")
            ->join('system s', 'c.system_id = s.system_id', 'left')
            ->join('campus_bills cb', 'c.campus_id = cb.campus_id AND cb.status = 1', 'left')
            ->where('c.campus_id', $campusId)
            ->get()
            ->getRow();

        if (!$campus) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Campus not found'
            ]);
        }

        // Get statistics
        $studentCount = $this->db->table('students')
            ->where('campus_id', $campusId)
            ->where('status', '1')
            ->countAllResults();

        $staffCount = $this->db->table('users')
            ->where('campus_id', $campusId)
            ->where('status', 1)
            ->countAllResults();

        $classCount = $this->db->table('class_section')
            ->where('campus_id', $campusId)
            ->where('status', 1)
            ->countAllResults();

        // Get fee collection for last 12 months
        $feeCollection = $this->db->table('fee_chalan')
            ->select("DATE_FORMAT(paid_date, '%Y-%m') as month, SUM(amount) as total")
            ->where('campus_id', $campusId)
            ->where('status', 'paid')
            ->where('paid_date >=', date('Y-m-d', strtotime('-12 months')))
            ->groupBy("DATE_FORMAT(paid_date, '%Y-%m')")
            ->orderBy('month', 'desc')
            ->get()
            ->getResult();

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'campus' => $campus,
                'statistics' => [
                    'total_students' => $studentCount,
                    'total_staff' => $staffCount,
                    'total_classes' => $classCount
                ],
                'fee_collection' => $feeCollection
            ]
        ]);
    }

    public function export()
    {
        $schoolId = $this->request->getGet('school_id');
        $statusFilter = $this->request->getGet('status');
        $search = $this->request->getGet('search');

        $builder = $this->db->table('campus c')
            ->select("
                s.system_name AS school_name,
                c.campus_name,
                c.location AS campus_address,
                c.landline AS campus_landline,
                c.mobile_no AS campus_mobile,
                c.currency_code,
                c.website,
                CASE 
                    WHEN cb.status = 1 AND cb.campus_expiry >= CURDATE() THEN 'Active'
                    WHEN cb.status = 1 AND cb.campus_expiry < CURDATE() THEN 'Expired'
                    ELSE 'Inactive'
                END AS campus_status,
                cb.campus_expiry,
                cb.bill_amount,
                (SELECT COUNT(*) FROM students WHERE campus_id = c.campus_id AND status = '1') AS total_students,
                (SELECT COUNT(*) FROM users WHERE campus_id = c.campus_id AND status = 1) AS total_staff
            ")
            ->join('system s', 'c.system_id = s.system_id', 'left')
            ->join('campus_bills cb', 'c.campus_id = cb.campus_id AND cb.status = 1', 'left');

        // Apply filters
        if (!empty($schoolId)) {
            $builder->where('s.system_id', $schoolId);
        }

        if (!empty($statusFilter)) {
            if ($statusFilter === 'active') {
                $builder->where('cb.status', 1)->where('cb.campus_expiry >=', date('Y-m-d'));
            } elseif ($statusFilter === 'expired') {
                $builder->where('cb.status', 1)->where('cb.campus_expiry <', date('Y-m-d'));
            } elseif ($statusFilter === 'inactive') {
                $builder->where('(cb.status IS NULL OR cb.status != 1)');
            }
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('s.system_name', $search)
                ->orLike('c.campus_name', $search)
                ->orLike('c.location', $search)
                ->groupEnd();
        }

        $results = $builder->orderBy('s.system_name', 'asc')
            ->orderBy('c.campus_name', 'asc')
            ->get()
            ->getResult();

        // Generate CSV
        $filename = 'campus_report_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($output, [
            'School Name', 'Campus Name', 'Address', 'Landline', 'Mobile',
            'Status', 'Expiry Date', 'Total Students', 'Total Staff',
            'Bill Amount', 'Currency', 'Website'
        ]);

        // Add data
        foreach ($results as $row) {
            fputcsv($output, [
                $row->school_name,
                $row->campus_name,
                $row->campus_address,
                $row->campus_landline,
                $row->campus_mobile,
                $row->campus_status,
                $row->campus_expiry,
                $row->total_students,
                $row->total_staff,
                $row->bill_amount,
                $row->currency_code,
                $row->website
            ]);
        }

        fclose($output);
        exit;
    }

    public function settings()
    {
        check_permission('admin-campus-settings');
        $campusid = $this->session->get('member_campusid');
        
        $settings = $this->db->table('campus_salary_settings')
            ->where('campus_id', $campusid)
            ->get()
            ->getRow();

        return view('admin/campus_settings', ['settings' => $settings]);
    }

    public function saveSettings()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'msg' => 'Invalid request'
            ]);
        }

        $campusid = $this->session->get('member_campusid');
        $data = [
            'deduction_type' => $this->request->getPost('deduction_type'),
            'deduction_per_day_amount' => $this->request->getPost('deduction_per_day_amount'),
            'deduction_per_day_percentage' => $this->request->getPost('deduction_per_day_percentage'),
            'late_deduction_enabled' => $this->request->getPost('late_deduction_enabled') ? 1 : 0,
            'late_deduction_amount' => $this->request->getPost('late_deduction_amount'),
            'late_grace_minutes' => $this->request->getPost('late_grace_minutes'),
            'early_leave_deduction_enabled' => $this->request->getPost('early_leave_deduction_enabled') ? 1 : 0,
            'early_leave_deduction_amount' => $this->request->getPost('early_leave_deduction_amount'),
            'attendance_bonus_enabled' => $this->request->getPost('attendance_bonus_enabled') ? 1 : 0,
            'attendance_bonus_days_required' => $this->request->getPost('attendance_bonus_days_required'),
            'attendance_bonus_type' => $this->request->getPost('attendance_bonus_type'),
            'attendance_bonus_amount' => $this->request->getPost('attendance_bonus_amount'),
            'security_deduction_enabled' => $this->request->getPost('security_deduction_enabled') ? 1 : 0,
            'security_deduction_type' => $this->request->getPost('security_deduction_type'),
            'security_deduction_value' => $this->request->getPost('security_deduction_value'),
            'working_days_per_month' => $this->request->getPost('working_days_per_month'),
            'updated_date' => date('Y-m-d H:i:s'),
            'user_id' => session('member_userid')
        ];

        $existing = $this->db->table('campus_salary_settings')
            ->where('campus_id', $campusid)
            ->get()
            ->getRow();

        if ($existing) {
            $this->db->table('campus_salary_settings')
                ->where('campus_id', $campusid)
                ->update($data);
        } else {
            $data['campus_id'] = $campusid;
            $data['created_date'] = date('Y-m-d H:i:s');
            $this->db->table('campus_salary_settings')->insert($data);
        }

        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Settings saved successfully'
        ]);
    }

    private function getSchools()
    {
        return $this->db->table('system')
            ->orderBy('system_name', 'asc')
            ->get()
            ->getResult();
    }
}