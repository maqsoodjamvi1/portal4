<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\MemberAcl;
use App\Libraries\SafeQuery;

class CampusManagement extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'role']);
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

            if (! empty($studentIds)) {
                $this->purgeStudentRecords(SafeQuery::intIds($studentIds));
            }

            if (! empty($staffIds)) {
                $this->purgeStaffRecords(SafeQuery::intIds($staffIds));
            }

            $parents = $this->db->table('parents')
                ->select('parent_id')
                ->where('campus_id', $campusId)
                ->get()
                ->getResult();

            if (! empty($parents)) {
                $parentIds = SafeQuery::intIds(array_column($parents, 'parent_id'));
                SafeQuery::deleteWhereIn($this->db, 'parents', 'parent_id', $parentIds);
                SafeQuery::deleteWhereIn($this->db, 'communication_logs', 'parent_id', $parentIds);
                SafeQuery::deleteWhereIn($this->db, 'whatsapp_log', 'parent_id', $parentIds);
            }
            
            // Delete class sections
            $classSections = $this->db->table('class_section')
                ->select('cls_sec_id')
                ->where('campus_id', $campusId)
                ->get()
                ->getResult();
            
            if (! empty($classSections)) {
                $this->purgeClassSections(SafeQuery::intIds(array_column($classSections, 'cls_sec_id')));
            }
            
            // Delete other campus related records
            $deleteTables = [
                'fee_amount', 'fee_plan_months', 'invoices',
                'expenses', 'assets', 'exp_estimation', 'income_estimation',
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
                'detail' => "Campus '{$campus->campus_name}' (ID: {$campusId}) was deleted with " . count($studentIds) . " students and " . count($staffIds) . " staff members.",
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

        $campusIds = array_map(static fn ($r) => (int) $r->campus_id, $results);
        $ownersByCampus = $this->batchLoadCampusOwners($campusIds);

        $userPlanMap = [];
        foreach ($results as $row) {
            $campusId = (int) $row->campus_id;
            $owner = $ownersByCampus[$campusId] ?? null;
            $planId = (int) ($row->plan_id ?? 0);
            if ($owner && $planId > 0) {
                $userPlanMap[(int) $owner->id] = $planId;
            }
        }
        $rolesByUser = $this->batchLoadOwnerRolesForUsers($userPlanMap);

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

            $campusId = (int) $row->campus_id;
            $owner = $ownersByCampus[$campusId] ?? null;
            $roleInfo = $owner
                ? ($rolesByUser[(int) $owner->id] ?? [
                    'role_names' => [],
                    'has_director_system' => false,
                    'has_only_director_system' => false,
                ])
                : [
                    'role_names' => [],
                    'has_director_system' => false,
                    'has_only_director_system' => false,
                ];

            $ownerName = '';
            if ($owner) {
                $ownerName = trim(($owner->first_name ?? '') . ' ' . ($owner->last_name ?? ''));
            }

            $data[] = [
                'sno' => $count++,
                'campus_id' => $row->campus_id,
                'school_name' => $row->school_name ?? 'N/A',
                'campus_name' => $row->campus_name,
                'campus_address' => $row->campus_address ?? 'N/A',
                'contact' => $row->campus_landline ?: ($row->campus_mobile ?: 'N/A'),
                'campus_status' => $row->campus_status ?? 'inactive',
                'owner_username' => $owner ? (string) ($owner->username ?? '') : '',
                'owner_name' => $ownerName,
                'owner_status' => $owner ? (int) ($owner->status ?? 0) : null,
                'owner_roles' => $roleInfo['role_names'],
                'owner_has_director_system' => $roleInfo['has_director_system'],
                'owner_has_only_director_system' => $roleInfo['has_only_director_system'],
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

        if (! empty($studentIds)) {
            $this->purgeStudentRecords(SafeQuery::intIds($studentIds));
        }

        if (! empty($staffIds)) {
            $this->purgeStaffRecords(SafeQuery::intIds($staffIds));
        }

        $parents = $this->db->table('parents')
            ->select('parent_id')
            ->where('campus_id', $campusId)
            ->get()
            ->getResult();

        if (! empty($parents)) {
            $parentIds = SafeQuery::intIds(array_column($parents, 'parent_id'));
            SafeQuery::deleteWhereIn($this->db, 'parents', 'parent_id', $parentIds);
            SafeQuery::deleteWhereIn($this->db, 'communication_logs', 'parent_id', $parentIds);
            SafeQuery::deleteWhereIn($this->db, 'whatsapp_log', 'parent_id', $parentIds);
        }

        $classSections = $this->db->table('class_section')
            ->select('cls_sec_id')
            ->where('campus_id', $campusId)
            ->get()
            ->getResult();

        if (! empty($classSections)) {
            $this->purgeClassSections(SafeQuery::intIds(array_column($classSections, 'cls_sec_id')));
        }
        
        // ========== DELETE OTHER CAMPUS RELATED RECORDS ==========
        $deleteTables = [
            // Fee related
            'fee_amount',
            'fee_plan_months',
            'invoice_sequencesdd',
            'invoices',
            
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
            'detail' => "Campus '{$campus->campus_name}' (ID: {$campusId}) was deleted with all associated records including " . count($studentIds) . " students and " . count($staffIds) . " staff members.",
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

    public function getPaymentHistory()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'msg' => 'Invalid request method',
            ]);
        }

        $campusId = (int) $this->request->getPost('campus_id');
        if ($campusId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Invalid campus',
            ]);
        }

        $campus = $this->db->table('campus')->where('campus_id', $campusId)->get()->getRow();
        if (!$campus) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Campus not found',
            ]);
        }

        try {
            $history = $this->buildCampusPaymentHistory($campusId);
            $activeBill = $this->getCampusBillForExpiry($campusId);

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'campus_name' => $campus->campus_name,
                    'currency_code' => $campus->currency_code ?? 'PKR',
                    'current_expiry' => $activeBill ? ($activeBill->campus_expiry ?? null) : null,
                    'current_bill_amount' => $activeBill ? ($activeBill->bill_amount ?? null) : null,
                    'current_bill_status' => $activeBill ? ($activeBill->bill_status ?? null) : null,
                    'payments' => $history['payments'],
                    'summary' => $history['summary'],
                ],
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'getPaymentHistory campus ' . $campusId . ': ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Could not load payment history. ' . $e->getMessage(),
            ]);
        }
    }

    public function getCampusOwner()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'msg' => 'Invalid request method',
            ]);
        }

        $campusId = (int) $this->request->getPost('campus_id');
        if ($campusId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Invalid campus',
            ]);
        }

        $campus = $this->db->table('campus')->where('campus_id', $campusId)->get()->getRow();
        if (!$campus) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Campus not found',
            ]);
        }

        $owner = $this->getCampusOwnerUser($campusId);
        if (!$owner) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'No owner user found for this campus',
            ]);
        }

        $bill = $this->getCampusBillForExpiry($campusId);
        $planId = (int) ($bill->plan_id ?? 0);
        $roleInfo = $this->getOwnerRoleInfo((int) $owner->id, $planId);

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'campus_name' => $campus->campus_name,
                'plan_id' => $planId,
                'plan_name' => $this->getPlanName($planId),
                'owner' => [
                    'id' => $owner->id,
                    'first_name' => $owner->first_name,
                    'last_name' => $owner->last_name,
                    'username' => $owner->username,
                    'email' => $owner->email,
                    'status' => (int) ($owner->status ?? 0),
                    'is_active' => (int) ($owner->status ?? 0) === 1,
                ],
                'current_roles' => $roleInfo['role_names'],
                'has_director_system' => $roleInfo['has_director_system'],
                'has_only_director_system' => $roleInfo['has_only_director_system'],
                'director_system_role_id' => $roleInfo['director_system_role_id'],
                'campus_expiry' => $bill->campus_expiry ?? null,
                'campus_expired' => $bill && date('Y-m-d') > (string) ($bill->campus_expiry ?? ''),
            ],
        ]);
    }

    public function assignOwnerDirectorSystem()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'msg' => 'Invalid request method',
            ]);
        }

        $campusId = (int) $this->request->getPost('campus_id');
        if ($campusId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Invalid campus',
            ]);
        }

        $campus = $this->db->table('campus')->where('campus_id', $campusId)->get()->getRow();
        if (!$campus) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Campus not found',
            ]);
        }

        $owner = $this->getCampusOwnerUser($campusId);
        if (!$owner) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'No owner user found for this campus',
            ]);
        }

        $planId = $this->getCampusPlanId($campusId);
        if ($planId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'No active subscription plan found for this campus',
            ]);
        }

        $directorSystemRoleId = resolveRoleIdForPlan('Director System', $planId);
        if ($directorSystemRoleId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Director System role is not configured for this campus plan',
            ]);
        }

        $ownerId = (int) $owner->id;
        $roleInfo = $this->getOwnerRoleInfo($ownerId, $planId);
        $oldRolesLabel = $roleInfo['role_names'] !== [] ? implode(', ', $roleInfo['role_names']) : 'None';

        if ($roleInfo['has_only_director_system']) {
            return $this->response->setJSON([
                'success' => true,
                'msg' => 'Owner already has only the Director System role for this plan. Ask them to log out and log back in if the menu still looks limited.',
                'data' => [
                    'owner_email' => $owner->email,
                    'old_roles' => $oldRolesLabel,
                    'new_role' => 'Director System',
                    'already_assigned' => true,
                ],
            ]);
        }

        $this->db->transStart();

        $this->db->table('user_roles')->where('userID', $ownerId)->delete();
        $this->db->table('user_roles')->insert([
            'userID'  => $ownerId,
            'roleID'  => $directorSystemRoleId,
            'addDate' => date('Y-m-d H:i:s'),
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Could not assign Director System role',
            ]);
        }

        (new MemberAcl($ownerId))->clearUserCaches($ownerId);

        $ownerLabel = trim(($owner->first_name ?? '') . ' ' . ($owner->last_name ?? '')) ?: $owner->username;

        try {
            $this->db->table('changes')->insert([
                'heading' => 'Campus Owner Role Assigned',
                'detail' => "Assigned Director System (role ID {$directorSystemRoleId}, plan {$planId}) to campus owner '{$ownerLabel}' (user ID: {$ownerId}) of campus '{$campus->campus_name}' (ID: {$campusId}). Previous roles: {$oldRolesLabel}.",
                'changing_date' => date('Y-m-d H:i:s'),
                'user_name' => session('member_username') ?? '',
                'user_id' => session('member_userid') ?? 0,
                'campus_id' => $campusId,
                'created_date' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Owner role assign audit log failed: ' . $e->getMessage());
        }

        $hadExtraRoles = $roleInfo['has_director_system'] || $roleInfo['role_names'] !== [];

        return $this->response->setJSON([
            'success' => true,
            'msg' => $hadExtraRoles
                ? 'Previous roles removed. Director System is now the only role. Ask the owner to log out and log back in to refresh the menu.'
                : 'Director System role assigned. Ask the owner to log out and log back in to refresh the menu.',
            'data' => [
                'owner_email' => $owner->email,
                'old_roles' => $oldRolesLabel,
                'new_role' => 'Director System',
                'role_id' => $directorSystemRoleId,
                'plan_id' => $planId,
                'already_assigned' => false,
                'roles_stripped' => $hadExtraRoles,
            ],
        ]);
    }

    public function activateOwner()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'msg' => 'Invalid request method',
            ]);
        }

        $campusId = (int) $this->request->getPost('campus_id');
        if ($campusId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Invalid campus',
            ]);
        }

        $owner = $this->getCampusOwnerUser($campusId);
        if (!$owner) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'No owner user found for this campus',
            ]);
        }

        if ((int) ($owner->status ?? 0) === 1) {
            return $this->response->setJSON([
                'success' => true,
                'msg' => 'Owner account is already active',
            ]);
        }

        $this->db->table('users')->where('id', $owner->id)->update(['status' => 1]);

        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Campus owner account activated. They can log in now (if campus expiry is also valid).',
        ]);
    }

    public function updateExpiry()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'msg' => 'Invalid request method',
            ]);
        }

        $campusId = (int) $this->request->getPost('campus_id');
        $expiry = trim((string) $this->request->getPost('campus_expiry'));

        if ($campusId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Invalid campus',
            ]);
        }

        $date = \DateTime::createFromFormat('Y-m-d', $expiry);
        if (!$date || $date->format('Y-m-d') !== $expiry) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Please enter a valid expiry date',
            ]);
        }

        $campus = $this->db->table('campus')->where('campus_id', $campusId)->get()->getRow();
        if (!$campus) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Campus not found',
            ]);
        }

        $bill = $this->getCampusBillForExpiry($campusId);
        if (!$bill) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'No subscription bill found for this campus',
            ]);
        }

        $oldExpiry = $bill->campus_expiry;

        $billUpdate = ['campus_expiry' => $expiry];
        if ($expiry >= date('Y-m-d')) {
            $billUpdate['status'] = 1;
        }

        $this->db->table('campus_bills')
            ->where('bill_id', $bill->bill_id)
            ->update($billUpdate);

        // Extending expiry: reactivate campus owner so they can log in after subscription fix
        if ($expiry >= date('Y-m-d')) {
            $owner = $this->getCampusOwnerUser($campusId);
            if ($owner && (int) ($owner->status ?? 0) !== 1) {
                $this->db->table('users')->where('id', $owner->id)->update(['status' => 1]);
            }
        }

        $dbError = $this->db->error();
        if (! empty($dbError['code'])) {
            $dbMessage = trim((string) ($dbError['message'] ?? ''));

            return $this->response->setJSON([
                'success' => false,
                'msg' => $dbMessage !== '' ? ('Database error: ' . $dbMessage) : 'Could not update campus expiry',
            ]);
        }

        try {
            $this->db->table('changes')->insert([
                'heading' => 'Campus Expiry Updated',
                'detail' => "Campus '{$campus->campus_name}' (ID: {$campusId}) expiry changed from {$oldExpiry} to {$expiry}.",
                'changing_date' => date('Y-m-d H:i:s'),
                'user_name' => session('member_username') ?? '',
                'user_id' => session('member_userid') ?? 0,
                'campus_id' => $campusId,
                'created_date' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Campus expiry audit log failed: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Campus expiry updated successfully',
            'campus_expiry' => $expiry,
        ]);
    }

    public function resetOwnerPassword()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'msg' => 'Invalid request method',
            ]);
        }

        $campusId = (int) $this->request->getPost('campus_id');
        $password = trim((string) $this->request->getPost('password'));
        $confirm = trim((string) $this->request->getPost('confirm_password'));

        if ($campusId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Invalid campus',
            ]);
        }

        if (strlen($password) < 6) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Password must be at least 6 characters',
            ]);
        }

        if ($password !== $confirm) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Passwords do not match',
            ]);
        }

        $campus = $this->db->table('campus')->where('campus_id', $campusId)->get()->getRow();
        if (!$campus) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Campus not found',
            ]);
        }

        $owner = $this->getCampusOwnerUser($campusId);
        if (!$owner) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'No owner user found for this campus',
            ]);
        }

        $update = [
            'password' => password_hash($password, PASSWORD_BCRYPT),
        ];

        if ($this->db->fieldExists('wpwd', 'users')) {
            $update['wpwd'] = $password;
        }

        $this->db->table('users')->where('id', $owner->id)->update($update);

        $dbError = $this->db->error();
        if (! empty($dbError['code'])) {
            $dbMessage = trim((string) ($dbError['message'] ?? ''));

            return $this->response->setJSON([
                'success' => false,
                'msg' => $dbMessage !== '' ? ('Database error: ' . $dbMessage) : 'Could not reset password',
            ]);
        }

        $ownerLabel = trim($owner->first_name . ' ' . $owner->last_name) ?: $owner->username;

        try {
            $this->db->table('changes')->insert([
                'heading' => 'Campus Owner Password Reset',
                'detail' => "Password reset for campus owner '{$ownerLabel}' (user ID: {$owner->id}) of campus '{$campus->campus_name}' (ID: {$campusId}).",
                'changing_date' => date('Y-m-d H:i:s'),
                'user_name' => session('member_username') ?? '',
                'user_id' => session('member_userid') ?? 0,
                'campus_id' => $campusId,
                'created_date' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Owner password reset audit log failed: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Owner password reset successfully',
        ]);
    }

    /**
     * Subscription payment rows from campus_bills (newest first).
     */
    private function buildCampusPaymentHistory(int $campusId): array
    {
        $rows = $this->db->table('campus_bills')
            ->where('campus_id', $campusId)
            ->orderBy('bill_id', 'DESC')
            ->get()
            ->getResult();

        $planNames = $this->loadSystemPlanNames($rows);
        $payments = [];
        $totalPaid = 0.0;
        $paidCount = 0;

        foreach ($rows as $row) {
            $billAmount = (float) ($row->bill_amount ?? 0);
            $discount = isset($row->discount) ? (float) $row->discount : 0.0;
            $isPaid = strtolower((string) ($row->bill_status ?? '')) === 'paid'
                || (isset($row->paid_date) && $row->paid_date !== null && $row->paid_date !== '' && $row->paid_date !== '0000-00-00');

            $paymentDate = $this->resolveBillPaymentDate($row, $isPaid);
            $planId = (int) ($row->plan_id ?? 0);
            $planName = $planNames[$planId] ?? ('Plan #' . ($planId ?: '—'));

            if ($isPaid) {
                $totalPaid += $billAmount;
                $paidCount++;
            }

            $payments[] = [
                'bill_id' => (int) ($row->bill_id ?? 0),
                'plan_name' => $planName,
                'bill_amount' => $billAmount,
                'discount' => $discount,
                'bill_status' => $row->bill_status ?? 'unknown',
                'is_paid' => $isPaid,
                'is_active' => (int) ($row->status ?? 0) === 1,
                'bill_issue_date' => $this->formatDateOnly($row->bill_issue_date ?? null),
                'paid_date' => $this->formatDateOnly($row->paid_date ?? null),
                'payment_date' => $paymentDate,
                'campus_expiry' => $this->formatDateOnly($row->campus_expiry ?? null),
                'install_months' => (int) ($row->install_id ?? 0),
            ];
        }

        return [
            'payments' => $payments,
            'summary' => [
                'total_records' => count($payments),
                'paid_count' => $paidCount,
                'total_paid_amount' => round($totalPaid, 2),
            ],
        ];
    }

    /**
     * @param array<int, object> $billRows
     * @return array<int, string>
     */
    private function loadSystemPlanNames(array $billRows): array
    {
        $planIds = [];
        foreach ($billRows as $row) {
            $planId = (int) ($row->plan_id ?? 0);
            if ($planId > 0) {
                $planIds[$planId] = $planId;
            }
        }

        if ($planIds === []) {
            return [];
        }

        if (! $this->db->tableExists('system_plans')) {
            return [];
        }

        $plans = $this->db->table('system_plans')
            ->select('plan_id, plan_name')
            ->whereIn('plan_id', array_values($planIds))
            ->get()
            ->getResult();

        $map = [];
        foreach ($plans as $plan) {
            $map[(int) $plan->plan_id] = (string) ($plan->plan_name ?? '');
        }

        return $map;
    }

    private function resolveBillPaymentDate(object $row, bool $isPaid): ?string
    {
        if (! $isPaid) {
            return null;
        }

        if (isset($row->paid_date) && $row->paid_date !== null && $row->paid_date !== '' && $row->paid_date !== '0000-00-00') {
            return $this->formatDateOnly($row->paid_date);
        }

        if (isset($row->updated_date) && $row->updated_date !== null && $row->updated_date !== '' && $row->updated_date !== '0000-00-00') {
            return $this->formatDateOnly($row->updated_date);
        }

        return $this->formatDateOnly($row->created_date ?? null);
    }

    private function formatDateOnly($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $ts = strtotime((string) $value);

        return $ts ? date('Y-m-d', $ts) : null;
    }

    /**
     * Active campus bill, or latest bill if none is marked active.
     */
    private function getCampusBillForExpiry(int $campusId): ?object
    {
        $bill = $this->db->table('campus_bills')
            ->where('campus_id', $campusId)
            ->where('status', 1)
            ->orderBy('bill_id', 'DESC')
            ->get()
            ->getRow();

        if ($bill) {
            return $bill;
        }

        return $this->db->table('campus_bills')
            ->where('campus_id', $campusId)
            ->orderBy('bill_id', 'DESC')
            ->get()
            ->getRow();
    }

    /**
     * @param list<int> $campusIds
     * @return array<int, object>
     */
    private function batchLoadCampusOwners(array $campusIds): array
    {
        $campusIds = array_values(array_unique(array_filter(array_map('intval', $campusIds))));
        if ($campusIds === []) {
            return [];
        }

        $subRows = $this->db->table('users')
            ->select('campus_id, MIN(id) AS owner_id', false)
            ->whereIn('campus_id', $campusIds)
            ->groupBy('campus_id')
            ->get()
            ->getResult();

        if ($subRows === []) {
            return [];
        }

        $ownerIds = array_map(static fn ($r) => (int) $r->owner_id, $subRows);
        $owners = $this->db->table('users')
            ->select('id, campus_id, first_name, last_name, username, email, status')
            ->whereIn('id', $ownerIds)
            ->get()
            ->getResult();

        $map = [];
        foreach ($owners as $owner) {
            $map[(int) $owner->campus_id] = $owner;
        }

        return $map;
    }

    /**
     * Plan-aware role names for campus owners (batch; mirrors getOwnerRoleInfo).
     *
     * @param array<int, int> $userPlanMap user_id => plan_id
     * @return array<int, array{role_names: list<string>, has_director_system: bool, has_only_director_system: bool}>
     */
    private function batchLoadOwnerRolesForUsers(array $userPlanMap): array
    {
        $default = [
            'role_names' => [],
            'has_director_system' => false,
            'has_only_director_system' => false,
        ];

        if ($userPlanMap === []) {
            return [];
        }

        $userIds = array_map('intval', array_keys($userPlanMap));
        $planIds = array_values(array_unique(array_filter(array_map('intval', array_values($userPlanMap)))));

        $result = [];
        foreach ($userIds as $userId) {
            $result[$userId] = $default;
        }

        if ($planIds === []) {
            return $result;
        }

        $directorRoleByPlan = [];
        foreach ($planIds as $planId) {
            $directorRoleByPlan[$planId] = resolveRoleIdForPlan('Director System', $planId);
        }

        $roleRows = $this->db->table('roles r')
            ->select('r.id, r.role_name_id, r.plan_id, rn.rolename')
            ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'inner')
            ->whereIn('r.plan_id', $planIds)
            ->get()
            ->getResult();

        $rolesByPlanPrimary = [];
        $rolesByPlanLegacy = [];
        foreach ($roleRows as $roleRow) {
            $planId = (int) $roleRow->plan_id;
            $roleId = (int) $roleRow->id;
            $roleNameId = (int) $roleRow->role_name_id;
            $roleName = trim((string) ($roleRow->rolename ?? ''));
            if ($roleName === '') {
                continue;
            }
            $rolesByPlanPrimary[$planId][$roleId] = $roleName;
            $rolesByPlanLegacy[$planId][$roleNameId] = $roleName;
        }

        $userRoleRows = $this->db->table('user_roles')
            ->whereIn('userID', $userIds)
            ->get()
            ->getResult();

        $storedRoleIdsByUser = [];
        foreach ($userRoleRows as $userRoleRow) {
            $userId = (int) $userRoleRow->userID;
            $storedRoleIdsByUser[$userId][] = (int) ($userRoleRow->roleID ?? 0);
        }

        foreach ($userRoleRows as $userRoleRow) {
            $userId = (int) $userRoleRow->userID;
            $storedRoleId = (int) ($userRoleRow->roleID ?? 0);
            $planId = (int) ($userPlanMap[$userId] ?? 0);
            if ($planId <= 0 || $storedRoleId <= 0) {
                continue;
            }

            $roleName = $rolesByPlanPrimary[$planId][$storedRoleId]
                ?? $rolesByPlanLegacy[$planId][$storedRoleId]
                ?? null;
            if ($roleName === null || $roleName === '') {
                continue;
            }

            if (! in_array($roleName, $result[$userId]['role_names'], true)) {
                $result[$userId]['role_names'][] = $roleName;
            }

            $directorSystemRoleId = $directorRoleByPlan[$planId] ?? 0;
            if ($storedRoleId === $directorSystemRoleId || strcasecmp($roleName, 'Director System') === 0) {
                $result[$userId]['has_director_system'] = true;
            }
        }

        $directorSystemRoleNameId = getRoleNameId('Director System');
        foreach ($userIds as $userId) {
            $planId = (int) ($userPlanMap[$userId] ?? 0);
            $directorSystemRoleId = $directorRoleByPlan[$planId] ?? 0;
            $storedIds = $storedRoleIdsByUser[$userId] ?? [];
            if (count($storedIds) !== 1) {
                continue;
            }

            $storedRoleId = $storedIds[0];
            if ($storedRoleId === $directorSystemRoleId) {
                $result[$userId]['has_only_director_system'] = true;
            } elseif ($directorSystemRoleNameId > 0 && $storedRoleId === $directorSystemRoleNameId) {
                $result[$userId]['has_only_director_system'] = true;
            }
        }

        return $result;
    }

    /**
     * First user created for the campus (campus director / owner).
     */
    private function getCampusOwnerUser(int $campusId): ?object
    {
        return $this->db->table('users')
            ->select('id, first_name, last_name, username, email, status, created_date')
            ->where('campus_id', $campusId)
            ->orderBy('id', 'ASC')
            ->limit(1)
            ->get()
            ->getRow();
    }

    private function getCampusPlanId(int $campusId): int
    {
        $bill = $this->getCampusBillForExpiry($campusId);

        return (int) ($bill->plan_id ?? 0);
    }

    private function getPlanName(int $planId): string
    {
        if ($planId <= 0) {
            return '';
        }

        $row = $this->db->table('system_plans')
            ->select('plan_name')
            ->where('plan_id', $planId)
            ->limit(1)
            ->get()
            ->getRow();

        return (string) ($row->plan_name ?? '');
    }

    /**
     * @return array{role_names: list<string>, has_director_system: bool, has_only_director_system: bool, director_system_role_id: int}
     */
    private function getOwnerRoleInfo(int $userId, int $planId): array
    {
        $directorSystemRoleId = $planId > 0 ? resolveRoleIdForPlan('Director System', $planId) : 0;
        $roleNames = [];
        $hasDirectorSystem = false;

        if ($userId <= 0 || $planId <= 0) {
            return [
                'role_names' => $roleNames,
                'has_director_system' => false,
                'has_only_director_system' => false,
                'director_system_role_id' => $directorSystemRoleId,
            ];
        }

        $primary = $this->db->table('user_roles ur')
            ->select('r.id, rn.rolename')
            ->join('roles r', 'r.id = ur.roleID AND r.plan_id = ' . (int) $planId, 'inner')
            ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'inner')
            ->where('ur.userID', $userId)
            ->get()
            ->getResultArray();

        $legacy = $this->db->table('user_roles ur')
            ->select('r.id, rn.rolename')
            ->join('roles r', 'r.role_name_id = ur.roleID AND r.plan_id = ' . (int) $planId, 'inner')
            ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'inner')
            ->where('ur.userID', $userId)
            ->get()
            ->getResultArray();

        $seen = [];
        foreach (array_merge($primary, $legacy) as $row) {
            $name = trim((string) ($row['rolename'] ?? ''));
            $rid  = (int) ($row['id'] ?? 0);
            if ($name === '' || isset($seen[$name])) {
                continue;
            }
            $seen[$name] = true;
            $roleNames[] = $name;
            if ($rid === $directorSystemRoleId || strcasecmp($name, 'Director System') === 0) {
                $hasDirectorSystem = true;
            }
        }

        return [
            'role_names' => $roleNames,
            'has_director_system' => $hasDirectorSystem,
            'has_only_director_system' => $this->ownerHasExclusiveDirectorSystemRole($userId, $directorSystemRoleId),
            'director_system_role_id' => $directorSystemRoleId,
        ];
    }

    /**
     * True when user_roles contains exactly one row mapped to Director System.
     */
    private function ownerHasExclusiveDirectorSystemRole(int $userId, int $directorSystemRoleId): bool
    {
        if ($userId <= 0 || $directorSystemRoleId <= 0) {
            return false;
        }

        $rows = $this->db->table('user_roles')
            ->where('userID', $userId)
            ->get()
            ->getResultArray();

        if (count($rows) !== 1) {
            return false;
        }

        $storedRoleId = (int) ($rows[0]['roleID'] ?? 0);
        if ($storedRoleId === $directorSystemRoleId) {
            return true;
        }

        $directorSystemRoleNameId = getRoleNameId('Director System');

        return $directorSystemRoleNameId > 0 && $storedRoleId === $directorSystemRoleNameId;
    }

    private function getSchools()
    {
        return $this->db->table('system')
            ->orderBy('system_name', 'asc')
            ->get()
            ->getResult();
    }

    /**
     * @param list<int> $studentIds
     */
    private function purgeStudentRecords(array $studentIds): void
    {
        if ($studentIds === []) {
            return;
        }

        foreach ([
            'a_student_subjects', 'student_class', 'fee_chalan', 'attendance',
            'd_exam_results', 'subject_results', 'test_results', 'leave_applications',
            'vehicle_students', 'quiz_attempts',
            'student_quiz_levels', 'ai_quiz_decisions', 'complaints', 'attachements',
            'school_leaving_certificates',
        ] as $table) {
            SafeQuery::deleteWhereIn($this->db, $table, 'student_id', $studentIds);
        }

        SafeQuery::deleteWhereIn($this->db, 'student_notices', 'std_id', $studentIds);
        SafeQuery::deleteWhereIn($this->db, 'students', 'student_id', $studentIds);
    }

    /**
     * @param list<int> $staffIds
     */
    private function purgeStaffRecords(array $staffIds): void
    {
        if ($staffIds === []) {
            return;
        }

        SafeQuery::deleteWhereIn($this->db, 'attendance_employee', 'emp_id', $staffIds);
        foreach ([
            'monthly_attendance_summary', 'salary_slips', 'advance_salaries', 'bonuses',
            'deduction_exceptions', 'employee_salary_rules', 'security_deposits', 'emp_timings',
        ] as $table) {
            SafeQuery::deleteWhereIn($this->db, $table, 'user_id', $staffIds);
        }

        SafeQuery::deleteWhereIn($this->db, 'teacher_qr_codes', 'teacher_id', $staffIds);
        SafeQuery::deleteWhereIn($this->db, 'teacher_section', 'tid', $staffIds);
        SafeQuery::deleteWhereIn($this->db, 'teacher_subjects', 'tid', $staffIds);
        SafeQuery::deleteWhereIn($this->db, 'employee_leaves', 'employee_id', $staffIds);
        SafeQuery::deleteWhereIn($this->db, 'employees_leave_applications', 'emp_id', $staffIds);
        SafeQuery::deleteWhereIn($this->db, 'emp_salary', 'emp_id', $staffIds);

        foreach ([
            'user_perms', 'user_roles',
        ] as $table) {
            SafeQuery::deleteWhereIn($this->db, $table, 'userID', $staffIds);
        }

        SafeQuery::deleteMessagesForUsers($this->db, $staffIds);
        SafeQuery::deleteWhereIn($this->db, 'users', 'id', $staffIds);
    }

    /**
     * @param list<int> $clsSecIds
     */
    private function purgeClassSections(array $clsSecIds): void
    {
        if ($clsSecIds === []) {
            return;
        }

        foreach ([
            'section_subjects', 'classdairy', 'school_timings', 'time_table', 'mark_attendance',
        ] as $table) {
            SafeQuery::deleteWhereIn($this->db, $table, 'cls_sec_id', $clsSecIds);
        }

        SafeQuery::deleteWhereIn($this->db, 'class_section', 'cls_sec_id', $clsSecIds);
    }
}