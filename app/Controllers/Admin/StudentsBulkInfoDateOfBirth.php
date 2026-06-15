<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\StudentsModel;
use CodeIgniter\HTTP\ResponseInterface as CIResponse;
use DateTime;

class StudentsBulkInfoDateOfBirth extends BaseController
{
    protected $db;
    protected $session;
    protected $students;

    public function __construct()
    {
        $this->db      = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'text']);

        check_permission('admin-students');

        $this->students = new StudentsModel();
    }

    // -------------------------------------------------------------------------
    // AJAX: search students by name (campus + active + cls_sec from student_class)
    // -------------------------------------------------------------------------
    public function searchByName()
    {
        $q          = trim((string) $this->request->getGet('q'));
        $cls_sec_id = (int) $this->request->getGet('cls_sec_id');   // optional narrow
        $limit      = (int) ($this->request->getGet('limit') ?: 20);
        $limit      = max(1, min($limit, 50));

        if ($q === '') {
            return $this->response->setJSON(['results' => []]);
        }

        // Campus from your session (adjust key if different)
        $campus_id  = (int) ($this->session->get('member_campusid') ?: 0);

        // Current academic session id (GET overrides; then session fallbacks)
        $session_id = (int) (
            $this->request->getGet('session_id')
            ?: $this->session->get('member_sessionid')
            ?: $this->session->get('session_id')
            ?: 0
        );

        // Base: active students in this campus
        $builder = $this->db->table('students s')
            ->distinct()
            ->where('s.status', 1);

        if ($campus_id > 0) {
            $builder->where('s.campus_id', $campus_id);
        }

        // Prefer cls_sec_id from student_class for the given session
        if ($session_id > 0) {
            $builder->select('s.student_id, s.first_name, s.last_name, s.parent_id, COALESCE(sc.cls_sec_id, s.cls_sec_id) AS cls_sec_id', false)
                ->join(
                    'student_class sc',
                    'sc.student_id = s.student_id AND sc.session_id = ' . (int) $session_id . ' AND sc.status = 1',
                    'left'
                );

            if ($cls_sec_id > 0) {
                $builder->where('sc.cls_sec_id', $cls_sec_id);
            }
        } else {
            // Fallback: use students.cls_sec_id when no session is known
            $builder->select('s.student_id, s.first_name, s.last_name, s.parent_id, s.cls_sec_id');

            if ($cls_sec_id > 0) {
                $builder->where('s.cls_sec_id', $cls_sec_id);
            }
        }

        // Name match (first and/or last)
        $builder->groupStart()
            ->like('s.first_name', $q)
            ->orLike('s.last_name',  $q)
            ->groupEnd();

        $rows = $builder->orderBy('s.first_name', 'ASC')
            ->orderBy('s.last_name',  'ASC')
            ->limit($limit)
            ->get()->getResult();

        $results = [];
        foreach ($rows as $r) {
            $name = trim(($r->first_name ?? '') . ' ' . ($r->last_name ?? '')) ?: (string) ($r->first_name ?? 'Student');
            $results[] = [
                'id'         => (int) $r->student_id,
                'text'       => $name,
                'parent_id'  => (int) $r->parent_id,
                'cls_sec_id' => (int) $r->cls_sec_id, // from sc if session_id provided
            ];
        }

        return $this->response->setJSON(['results' => $results]);
    }

    // -------------------------------------------------------------------------
    // AJAX: list students by parent (for DOB bulk view)
    // -------------------------------------------------------------------------
    public function byParent()
    {
        $parent_id  = (int) $this->request->getPost('parent_id');
        $cls_sec_id = (int) $this->request->getPost('cls_sec_id'); // optional narrow
        $campus_id  = (int) ($this->request->getPost('campus_id') ?: $this->session->get('member_campusid'));

        // Current academic session id (POST overrides; then session fallbacks)
        $session_id = (int) (
            $this->request->getPost('session_id')
            ?: $this->session->get('member_sessionid')
            ?: $this->session->get('session_id')
            ?: 0
        );

        if (! $parent_id) {
            return $this->response->setStatusCode(400)->setBody('<div class="alert alert-warning">Missing parent_id.</div>');
        }

        $builder = $this->db->table('students s')
            ->distinct()
            ->where('s.parent_id', $parent_id)
            ->where('s.status', 1);

        if ($campus_id > 0) {
            $builder->where('s.campus_id', $campus_id);
        }

        if ($session_id > 0) {
            $builder->select('s.student_id, s.first_name, s.last_name, s.parent_id, COALESCE(sc.cls_sec_id, s.cls_sec_id) AS cls_sec_id', false)
                ->join(
                    'student_class sc',
                    'sc.student_id = s.student_id AND sc.session_id = ' . (int) $session_id . ' AND sc.status = 1',
                    'left'
                );

            if ($cls_sec_id > 0) {
                $builder->where('sc.cls_sec_id', $cls_sec_id);
            }
        } else {
            $builder->select('s.student_id, s.first_name, s.last_name, s.parent_id, s.cls_sec_id');

            if ($cls_sec_id > 0) {
                $builder->where('s.cls_sec_id', $cls_sec_id);
            }
        }

        $students = $builder
            ->orderBy('s.first_name', 'ASC')
            ->orderBy('s.last_name',  'ASC')
            ->get()->getResult();

        if (empty($students)) {
            return $this->response->setBody('<div class="alert alert-info">No students found for this parent.</div>');
        }

        $html = '<div class="table-responsive"><table class="table table-sm table-striped mb-0">
               <thead><tr>
                 <th>#</th><th>Student</th><th>Parent ID</th><th>Class (cls_sec_id)</th>
               </tr></thead><tbody>';
        $i = 1;
        foreach ($students as $s) {
            $name = trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? '')) ?: esc($s->first_name ?? 'Student');
            $html .= '<tr>
                    <td>' . ($i++) . '</td>
                    <td>' . esc($name) . '</td>
                    <td>' . (int) $s->parent_id . '</td>
                    <td>' . (int) $s->cls_sec_id . '</td>
                  </tr>';
        }
        $html .= '</tbody></table></div>';

        return $this->response->setBody($html);
    }

    // -------------------------------------------------------------------------
    // Index (main page)
    // -------------------------------------------------------------------------
    public function index()
    {
        $campus_id   = $this->session->get('member_campusid');
        $currentrole = currentUserRoles();

        $data = [
            'sectionsclassinfo' => in_array(5, $currentrole) ? teacherSubjectSections() : $this->userClassSections(),
            'campus_info'       => $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow(),
            'campus_flags'      => $this->getCampusFlags($campus_id),
        ];

        return view('admin/students_bulk_info_date_of_birth', $data);
    }

    protected function userClassSections()
    {
        return $this->db->table('class_section cs')
            ->select('cs.cls_sec_id, cs.section_id, CONCAT(c.class_name, " (", s.section_name, ")") as sectionclassname')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->where('cs.status', 1)
            ->where('cs.campus_id', $this->session->get('member_campusid'))
            ->get()
            ->getResultArray();
    }

    protected function getCampusFlags($campus_id)
    {
        return $this->db->table('campus')
            ->select('daycare_flag, boarding_flag')
            ->where('campus_id', $campus_id)
            ->get()
            ->getRow();
    }

    // -------------------------------------------------------------------------
    // List students (tbody rows) – used for DOB bulk screen
    // -------------------------------------------------------------------------
public function data()
{
    try {
        $cls_sec_id = $this->request->getPost('cls_sec_id');
        
        // Debug: Log the received parameter
        log_message('debug', 'data() method called with cls_sec_id: ' . $cls_sec_id);
        
        if (!$cls_sec_id) {
            log_message('debug', 'No cls_sec_id provided, returning empty');
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'No class selected',
                'data' => []
            ]);
        }
        
        // Resolve class + section labels (class_section has IDs only, not names)
        $classSection = $this->db->table('class_section cs')
            ->select('cs.cls_sec_id, c.class_name, sec.section_name')
            ->join('classes c', 'c.class_id = cs.class_id', 'inner')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'inner')
            ->where('cs.cls_sec_id', $cls_sec_id)
            ->get()
            ->getRow();
        
        if (!$classSection) {
            log_message('error', 'Class section not found for ID: ' . $cls_sec_id);
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Class section not found',
                'data' => []
            ]);
        }
        
        // Get students with their BMI data
        $students = $this->db->table('students s')
            ->select('s.student_id, s.first_name, s.last_name, s.reg_no, s.profile_photo, s.date_of_birth, s.db_status, s.date_of_birth_age, s.height, s.weight, s.bmi, s.bmi_category')
            ->join('student_class sc', 'sc.student_id = s.student_id AND sc.status = 1')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections sec', 'sec.section_id = cs.section_id')
            ->where('sc.cls_sec_id', $cls_sec_id)
            ->where('s.status', 1)
            ->orderBy('s.first_name', 'ASC')
            ->get()
            ->getResult();
        
        // Debug: Log number of students found
        log_message('debug', 'Found ' . count($students) . ' students for class section: ' . $cls_sec_id);
        
        if (empty($students)) {
            return $this->response->setJSON([
                'success' => true,
                'data' => [],
                'msg' => 'No students found in this class',
                'total' => 0
            ]);
        }
        
        $data = [];
        foreach ($students as $student) {
            $data[] = [
                'student_id' => (int) $student->student_id,
                'first_name' => $student->first_name ?? '',
                'last_name' => $student->last_name ?? '',
                'reg_no' => $student->reg_no ?? '',
                'profile_photo' => $student->profile_photo ?? '',
                'date_of_birth' => $student->date_of_birth ?? '',
                'db_status' => (int) ($student->db_status ?? 0),
                'date_of_birth_age' => $student->date_of_birth_age ?? '',
                'height' => $student->height ? (float) $student->height : null,
                'weight' => $student->weight ? (float) $student->weight : null,
                'bmi' => $student->bmi ? (float) $student->bmi : null,
                'bmi_category' => $student->bmi_category ?? '',
                'class_name' => $classSection->class_name ?? '',
                'section_name' => $classSection->section_name ?? ''
            ];
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $data,
            'total' => count($data),
            'msg' => 'Students loaded successfully'
        ]);
        
    } catch (\Exception $e) {
        log_message('error', 'Error in data() method: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error loading students: ' . $e->getMessage(),
            'data' => []
        ]);
    }
}

/**
 * Helper function to get BMI category display
 */
private function getBMICategoryDisplay($category)
{
    $categories = [
        'underweight' => ['text' => 'Underweight', 'class' => 'bmi-underweight'],
        'normal' => ['text' => 'Normal', 'class' => 'bmi-normal'],
        'overweight' => ['text' => 'Overweight', 'class' => 'bmi-overweight'],
        'obese' => ['text' => 'Obese', 'class' => 'bmi-obese']
    ];
    
    return $categories[$category] ?? ['text' => 'Unknown', 'class' => 'bmi-unknown'];
}

    /**
     * Optional profile photo on bulk DOB save (JPG/PNG/WebP, max 4 MB).
     *
     * @return array{ok:bool, filename?:string|null, msg?:string}
     */
    private function saveStudentProfilePhotoFile(int $studentId, $file): array
    {
        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'filename' => null];
        }
        if (! $file->isValid()) {
            return ['ok' => false, 'msg' => 'Invalid photo upload: ' . $file->getErrorString()];
        }
        if ($file->getSize() > 4 * 1024 * 1024) {
            return ['ok' => false, 'msg' => 'Photo must be 4 MB or smaller.'];
        }
        $mime = (string) $file->getMimeType();
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (! in_array($mime, $allowedMimes, true)) {
            return ['ok' => false, 'msg' => 'Only JPG, PNG, or WebP images are allowed.'];
        }
        $ext = strtolower((string) ($file->getClientExtension() ?: $file->guessExtension() ?: 'jpg'));
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $ext = 'jpg';
        }
        $dest = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads';
        if (! is_dir($dest)) {
            if (! @mkdir($dest, 0755, true) && ! is_dir($dest)) {
                return ['ok' => false, 'msg' => 'Upload folder is not available.'];
            }
        }
        if (! is_writable($dest)) {
            @chmod($dest, 0755);
            if (! is_writable($dest)) {
                return ['ok' => false, 'msg' => 'Upload folder is not writable.'];
            }
        }
        $newName = uniqid('stu_', true) . '.' . $ext;
        try {
            $file->move($dest, $newName);
        } catch (\Throwable $e) {
            log_message('error', 'DOB bulk photo move failed: ' . $e->getMessage());

            return ['ok' => false, 'msg' => 'Could not save the photo file.'];
        }

        $row = $this->db->table('students')
            ->select('profile_photo')
            ->where('student_id', $studentId)
            ->get()
            ->getRow();
        if ($row && ! empty($row->profile_photo)) {
            $old = basename((string) $row->profile_photo);
            if ($old !== '' && $old !== $newName) {
                $oldPath = $dest . DIRECTORY_SEPARATOR . $old;
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
        }

        return ['ok' => true, 'filename' => $newName];
    }

    // -------------------------------------------------------------------------
    // Save ONLY DOB-related fields for ONE student (AJAX)
    // -------------------------------------------------------------------------
 public function saveStudentInfo()
{
    try {
        $student_id = (int) $this->request->getPost('student_id');
        $date_of_birth = $this->request->getPost('date_of_birth');
        $height = $this->request->getPost('height');
        $weight = $this->request->getPost('weight');
        $db_status = $this->request->getPost('db_status');
        $date_of_birth_age = $this->request->getPost('date_of_birth_age');
        
        if (! $student_id) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Student ID is required'
            ]);
        }
        
        // Calculate BMI
        $bmi = null;
        $bmi_category = null;
        if ($height && $weight && $height > 0 && $weight > 0) {
            $heightInMeters = $height / 100;
            $bmi = round($weight / ($heightInMeters * $heightInMeters), 2);
            $bmi_category = $this->getBMICategoryValue($bmi);
        }
        
        // Update student data
        $updateData = [
            'date_of_birth' => $date_of_birth,
            'height' => $height ?: null,
            'weight' => $weight ?: null,
            'bmi' => $bmi,
            'bmi_category' => $bmi_category,
            'db_status' => $db_status,
            'date_of_birth_age' => $date_of_birth_age,
            'updated_date' => date('Y-m-d H:i:s'),
            'user_id' => session('member_userid')
        ];

        $file = $this->request->getFile('profile_photo');
        if ($file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            $photoResult = $this->saveStudentProfilePhotoFile($student_id, $file);
            if (! $photoResult['ok']) {
                return $this->response->setJSON([
                    'success' => false,
                    'msg' => $photoResult['msg'] ?? 'Photo upload failed.',
                ]);
            }
            if (! empty($photoResult['filename'])) {
                $updateData['profile_photo'] = $photoResult['filename'];
            }
        }
        
        $this->db->table('students')
            ->where('student_id', $student_id)
            ->update($updateData);

        $photoOut = $this->db->table('students')
            ->select('profile_photo')
            ->where('student_id', $student_id)
            ->get()
            ->getRow();
        
        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Student information saved successfully',
            'data' => [
                'bmi' => $bmi,
                'bmi_category' => $bmi_category,
                'profile_photo' => $photoOut->profile_photo ?? '',
            ]
        ]);
        
    } catch (\Exception $e) {
        log_message('error', 'Error in save_student_info: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error saving student information: ' . $e->getMessage()
        ]);
    }
}

/**
 * Helper function to get BMI category value
 */
private function getBMICategoryValue($bmi)
{
    if ($bmi < 18.5) return 'underweight';
    if ($bmi < 25) return 'normal';
    if ($bmi < 30) return 'overweight';
    return 'obese';
}

/**
 * Record BMI history
 */
private function recordBmiHistory($studentId, $height, $weight, $bmi, $bmiCategory, $bmiPercentile, $userId)
{
    $db = $this->db ?? \Config\Database::connect();
    
    // Check if bmi_history table exists
    $tables = $db->listTables();
    
    if (in_array('bmi_history', $tables)) {
        $db->table('bmi_history')->insert([
            'student_id' => $studentId,
            'height' => $height,
            'weight' => $weight,
            'bmi' => $bmi,
            'bmi_category' => $bmiCategory,
            'bmi_percentile' => $bmiPercentile,
            'recorded_date' => date('Y-m-d'),
            'recorded_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}

    // -------------------------------------------------------------------------
    // Helper: normalize values (only date really needed here)
    // -------------------------------------------------------------------------
    protected function normalizeValue(string $type, $val)
    {
        if ($type === 'string' || $type === 'email') {
            $val = is_string($val) ? trim($val) : $val;
            return ($val === '' ? null : $val);
        }

        if ($type === 'int') {
            return ($val === '' || $val === null) ? null : (int) $val;
        }

        if ($type === 'date') {
            $val = is_string($val) ? trim($val) : $val;
            if ($val === '' || $val === null) {
                return null;
            }
            $ts = strtotime($val);
            return $ts ? date('Y-m-d', $ts) : null;
        }

        return $val;
    }

    // -------------------------------------------------------------------------
    // (Optional) Parent lookup by CNIC – NOT used for DOB, but if you don’t
    // need it here at all, you can safely remove this whole method.
    // -------------------------------------------------------------------------
    public function lookup_parent_by_cnic(): CIResponse
    {
        $method = strtolower($this->request->getMethod());
        if (! in_array($method, ['post', 'get'], true)) {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'msg'     => 'Method not allowed',
            ]);
        }

        $raw    = trim((string) ($this->request->getPost('cnic') ?? $this->request->getGet('cnic') ?? ''));
        $digits = preg_replace('/\D+/', '', $raw);

        if (preg_match('/^\d{5}-\d{7}-\d$/', $raw)) {
            $cnic = $raw;
        } elseif (strlen($digits) === 13) {
            $cnic = substr($digits, 0, 5) . '-' . substr($digits, 5, 7) . '-' . substr($digits, 12, 1);
        } else {
            return $this->response->setJSON([
                'success' => true,
                'found'   => false,
                'msg'     => 'Invalid CNIC. Use 13 digits or XXXXX-XXXXXXX-X.',
            ]);
        }

        $campusId = (int) (session('member_campusid') ?? 0);
        if ($campusId <= 0) {
            return $this->response->setJSON([
                'success' => true,
                'found'   => false,
                'msg'     => 'Campus not set in session.',
            ]);
        }

        $db  = \Config\Database::connect();
        $row = $db->table('parents')
            ->select('parent_id, f_name, father_cnic')
            ->where('father_cnic', $cnic)
            ->where('campus_id', $campusId)
            ->get(1)->getRowArray();

        if ($row) {
            return $this->response->setJSON([
                'success'     => true,
                'found'       => true,
                'parent_id'   => (int) $row['parent_id'],
                'f_name'      => (string) ($row['f_name'] ?? ''),
                'father_cnic' => (string) ($row['father_cnic'] ?? $cnic),
                'msg'         => 'Match found.',
            ]);
        }

        return $this->response->setJSON([
            'success'     => true,
            'found'       => false,
            'father_cnic' => $cnic,
            'msg'         => 'No matching parent for this CNIC in this campus.',
        ]);
    }
}
