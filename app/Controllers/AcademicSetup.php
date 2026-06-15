<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AcademicSetup extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'text']);
        check_permission('admin-academic-session');
    }

    /** Prefer admin session campus used across the app, then legacy campus_id. */
    private function resolveCampusId(): int
    {
        $fromMember = (int) ($this->session->get('member_campusid') ?? 0);
        if ($fromMember > 0) {
            return $fromMember;
        }

        return (int) ($this->session->get('campus_id') ?? 0);
    }

    /**
     * Single payload for wizard UI (one round-trip vs many nested calls).
     */
    public function bootstrapData()
    {
        try {
            $schoolinfo = getSchoolInfo();
            if ($schoolinfo === null) {
                return $this->respondJsonPayload([
                    'success' => false,
                    'msg'     => 'Campus not found in session (member_campusid). Re-open the app from campus login or set campus in profile.',
                ]);
            }

            $systemId = (int) ($schoolinfo->system_id ?? 0);
            $campusId = $this->resolveCampusId();

            $classes         = $this->getClasses($systemId);
            $sections        = $this->getSections($systemId);
            $subjects        = $this->getSubjects($systemId);
            $classSections   = $campusId > 0 ? $this->getClassSections($campusId) : [];
            $sectionSubjects = $campusId > 0 ? $this->getSectionSubjects($campusId) : [];

            $payload = [
                'success'          => true,
                'system_id'        => $systemId,
                'campus_id'        => $campusId,
                'classes'          => $this->rowsToAssocArray($classes),
                'sections'         => $this->rowsToAssocArray($sections),
                'subjects'         => $this->rowsToAssocArray($subjects),
                'class_sections'   => $this->rowsToAssocArray($classSections),
                'section_subjects' => $this->rowsToAssocArray($sectionSubjects),
            ];

            return $this->respondJsonPayload($payload);
        } catch (\Throwable $e) {
            log_message('error', 'AcademicSetup::bootstrapData: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());

            return $this->respondJsonPayload([
                'success' => false,
                'msg'     => ENVIRONMENT !== 'production'
                    ? $e->getMessage()
                    : 'Could not load setup data. Check server logs.',
            ]);
        }
    }

    /**
     * Encode JSON safely for DB text that may contain invalid UTF-8 (breaks jQuery dataType:'json').
     */
    private function respondJsonPayload(array $payload): \CodeIgniter\HTTP\ResponseInterface
    {
        $flags = JSON_UNESCAPED_UNICODE;
        if (\defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
            $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
        }
        if (\defined('JSON_PARTIAL_OUTPUT_ON_ERROR')) {
            $flags |= JSON_PARTIAL_OUTPUT_ON_ERROR;
        }

        $json = json_encode($payload, $flags);
        if ($json === false) {
            log_message('error', 'AcademicSetup JSON encode failed: ' . json_last_error_msg());

            return $this->response
                ->setContentType('application/json; charset=UTF-8')
                ->setBody('{"success":false,"msg":"Server could not encode setup data (check invalid characters in class/subject names)."}');
        }

        return $this->response
            ->setContentType('application/json; charset=UTF-8')
            ->setBody($json);
    }

    /**
     * @param array<int, object> $rows
     *
     * @return array<int, array<string, mixed>>
     */
    private function rowsToAssocArray(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $rowArr = (array) $row;
            $clean  = [];
            foreach ($rowArr as $key => $val) {
                if (\is_string($key) && $key !== '' && $key[0] === "\0") {
                    continue;
                }
                if (\is_float($val) && (\is_nan($val) || \is_infinite($val))) {
                    $val = null;
                }
                if (\is_string($val)) {
                    $val = $this->sanitizeJsonString($val);
                }
                $clean[$key] = $val;
            }
            $out[] = $clean;
        }

        return $out;
    }

    private function sanitizeJsonString(string $s): string
    {
        if ($s === '') {
            return '';
        }
        if (\function_exists('mb_convert_encoding')) {
            $converted = @mb_convert_encoding($s, 'UTF-8', 'UTF-8');

            return $converted !== false ? $converted : $s;
        }

        return $s;
    }

    private function buildWizardBootstrap(int $systemId, int $campusId): array
    {
        $classes         = $this->getClasses($systemId);
        $sections        = $this->getSections($systemId);
        $subjects        = $this->getSubjects($systemId);
        $classSections   = $campusId > 0 ? $this->getClassSections($campusId) : [];
        $sectionSubjects = $campusId > 0 ? $this->getSectionSubjects($campusId) : [];

        return [
            'system_id'        => $systemId,
            'campus_id'        => $campusId,
            'classes'          => $this->rowsToAssocArray($classes),
            'sections'         => $this->rowsToAssocArray($sections),
            'subjects'         => $this->rowsToAssocArray($subjects),
            'class_sections'   => $this->rowsToAssocArray($classSections),
            'section_subjects' => $this->rowsToAssocArray($sectionSubjects),
        ];
    }

   public function index()
{
    $schoolinfo = getSchoolInfo();
    if ($schoolinfo === null) {
        return redirect()->to(site_url('admin/dashboard'))->with('error', 'Campus not set in session. Please log in again or select a campus.');
    }
    $systemId = (int) ($schoolinfo->system_id ?? 0);
    
    $campusId = $this->resolveCampusId();
    
    if ($campusId === 0 && isset($schoolinfo->campus_id)) {
        $campusId = (int) $schoolinfo->campus_id;
    }
    
    if ($campusId === 0 && $systemId > 0) {
        try {
            $campusResult = $this->db->table('campus')
                ->where('system_id', $systemId)
                ->limit(1)
                ->get();
            
            if ($campusResult && $campusResult->getNumRows() > 0) {
                $campus = $campusResult->getFirstRow();
                if ($campus && isset($campus->campus_id)) {
                    $campusId = (int) $campus->campus_id;
                    $this->session->set('campus_id', $campusId);
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error fetching campus: ' . $e->getMessage());
        }
    }

    $data = [
        'system_id' => $systemId,
        'campus_id' => $campusId,
        'wizard_bootstrap' => $this->buildWizardBootstrap($systemId, $campusId),
    ];

    return view('admin/academic_setup', $data);
}


public function checkFeeTypes()
{
    $systemId = $this->request->getGet('system_id');
    
    if (!$systemId) {
        $schoolinfo = getSchoolInfo();
        $systemId = $schoolinfo->system_id ?? 0;
    }
    
    $count = $this->db->table('fee_type')
        ->where('system_id', $systemId)
        ->countAllResults();
    
    return $this->response->setJSON([
        'success' => true,
        'has_fee_types' => $count > 0,
        'count' => $count
    ]);
}


public function fetchClasses()
{
    $schoolinfo = getSchoolInfo();
    $systemId = $schoolinfo->system_id ?? 0;
    
    $classes = $this->getClasses($systemId);
    
    return $this->response->setJSON(['success' => true, 'data' => $classes]);
}

public function fetchSections()
{
    $schoolinfo = getSchoolInfo();
    $systemId = $schoolinfo->system_id ?? 0;
    
    $sections = $this->getSections($systemId);
    
    return $this->response->setJSON(['success' => true, 'data' => $sections]);
}

public function fetchSubjects()
{
    $schoolinfo = getSchoolInfo();
    $systemId = $schoolinfo->system_id ?? 0;
    
    $subjects = $this->getSubjects($systemId);
    
    return $this->response->setJSON(['success' => true, 'data' => $subjects]);
}

    private function getClasses($systemId)
    {
        if (! $systemId) {
            return [];
        }

        return $this->db->table('classes')
            ->where('system_id', $systemId)
            ->orderBy('status', 'DESC')
            ->orderBy('class_id', 'ASC')
            ->get()->getResult();
    }

    private function getSections($systemId)
    {
        if (! $systemId) {
            return [];
        }

        return $this->db->table('sections')
            ->where('system_id', $systemId)
            ->orderBy('status', 'DESC')
            ->orderBy('section_id', 'ASC')
            ->get()->getResult();
    }

    private function getSubjects($systemId)
    {
        if (! $systemId) {
            return [];
        }

        return $this->db->table('allsubject')
            ->where('system_id', $systemId)
            ->orderBy('status', 'DESC')
            ->orderBy('subject_name', 'ASC')
            ->get()->getResult();
    }

    private function getClassSections($campusId)
    {
        if (!$campusId) return [];
        
        return $this->db->table('class_section cs')
            ->select('cs.*, c.class_name, s.section_name')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->where('cs.campus_id', $campusId)
            ->where('cs.status', 1)
            ->orderBy('c.class_id', 'ASC')
            ->orderBy('s.section_id', 'ASC')
            ->get()->getResult();
    }

    private function getSectionSubjects($campusId)
    {
        if (!$campusId) return [];
        
        return $this->db->table('section_subjects ss')
            ->select('ss.*, c.class_name, s.section_name, sub.subject_name, cs.cls_sec_id')
            ->join('class_section cs', 'cs.cls_sec_id = ss.cls_sec_id')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->join('allsubject sub', 'sub.sid = ss.subject_id')
            ->where('cs.campus_id', $campusId)
            ->where('ss.status', 1)
            ->orderBy('c.class_id', 'ASC')
            ->orderBy('s.section_id', 'ASC')
            ->orderBy('sub.subject_name', 'ASC')
            ->get()->getResult();
    }

    public function saveClasses()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
        }

        $schoolinfo = getSchoolInfo();
        $systemId = $schoolinfo->system_id ?? 0;
        $userId = $this->session->get('member_userid');
        $now = date('Y-m-d H:i:s');

        $classes = $this->request->getPost('classes');
        
        if (empty($classes)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Please add at least one class']);
        }

        $this->db->transBegin();

        try {
            foreach ($classes as $class) {
                $name   = trim((string) ($class['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $short  = trim((string) ($class['short_name'] ?? ''));
                $detail = trim((string) ($class['detail'] ?? ''));
                $status = (int) ($class['status'] ?? 1) === 0 ? 0 : 1;
                $id     = (int) ($class['class_id'] ?? $class['id'] ?? 0);

                if ($id > 0) {
                    $row = $this->db->table('classes')
                        ->where('class_id', $id)
                        ->where('system_id', $systemId)
                        ->get()->getRow();
                    if (! $row) {
                        continue;
                    }
                    $this->db->table('classes')->where('class_id', $id)->update([
                        'class_name'       => $name,
                        'class_short_name' => $short,
                        'detail'           => $detail,
                        'status'           => $status,
                        'user_id'          => $userId,
                    ]);
                } else {
                    $this->db->table('classes')->insert([
                        'system_id'        => $systemId,
                        'class_name'       => $name,
                        'class_short_name' => $short,
                        'detail'           => $detail,
                        'status'           => $status,
                        'created_date'     => $now,
                        'user_id'          => $userId,
                    ]);
                }
            }
            
            $this->db->transCommit();
            return $this->response->setJSON(['success' => true, 'msg' => 'Classes saved successfully']);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function saveSections()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
        }

        $schoolinfo = getSchoolInfo();
        $systemId = $schoolinfo->system_id ?? 0;
        $userId = $this->session->get('member_userid');
        $now = date('Y-m-d H:i:s');

        $sections = $this->request->getPost('sections');
        
        if (empty($sections)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Please add at least one section']);
        }

        $this->db->transBegin();

        try {
            foreach ($sections as $section) {
                $name   = trim((string) ($section['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $short  = trim((string) ($section['short_name'] ?? ''));
                $status = (int) ($section['status'] ?? 1) === 0 ? 0 : 1;
                $id     = (int) ($section['section_id'] ?? $section['id'] ?? 0);

                if ($id > 0) {
                    $row = $this->db->table('sections')
                        ->where('section_id', $id)
                        ->where('system_id', $systemId)
                        ->get()->getRow();
                    if (! $row) {
                        continue;
                    }
                    $this->db->table('sections')->where('section_id', $id)->update([
                        'section_name' => $name,
                        'short_name'   => $short,
                        'status'       => $status,
                        'user_id'      => $userId,
                    ]);
                } else {
                    $this->db->table('sections')->insert([
                        'system_id'    => $systemId,
                        'section_name' => $name,
                        'short_name'   => $short,
                        'status'       => $status,
                        'created_date' => $now,
                        'user_id'      => $userId,
                    ]);
                }
            }
            
            $this->db->transCommit();
            return $this->response->setJSON(['success' => true, 'msg' => 'Sections saved successfully']);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function saveSubjects()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
        }

        $schoolinfo = getSchoolInfo();
        $systemId = $schoolinfo->system_id ?? 0;
        $userId = $this->session->get('member_userid');
        $now = date('Y-m-d H:i:s');

        $subjects = $this->request->getPost('subjects');
        
        if (empty($subjects)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Please add at least one subject']);
        }

        $this->db->transBegin();

        try {
            foreach ($subjects as $subject) {
                $name   = trim((string) ($subject['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $short  = trim((string) ($subject['short_name'] ?? ''));
                $status = (int) ($subject['status'] ?? 1) === 0 ? 0 : 1;
                $id     = (int) ($subject['subject_id'] ?? $subject['sid'] ?? $subject['id'] ?? 0);

                if ($id > 0) {
                    $row = $this->db->table('allsubject')
                        ->where('sid', $id)
                        ->where('system_id', $systemId)
                        ->get()->getRow();
                    if (! $row) {
                        continue;
                    }
                    $this->db->table('allsubject')->where('sid', $id)->update([
                        'subject_name'       => $name,
                        'subject_short_name' => $short,
                        'status'             => $status,
                        'user_id'            => $userId,
                    ]);
                } else {
                    $this->db->table('allsubject')->insert([
                        'system_id'          => $systemId,
                        'subject_name'       => $name,
                        'subject_short_name' => $short,
                        'status'             => $status,
                        'created_date'       => $now,
                        'user_id'            => $userId,
                    ]);
                }
            }
            
            $this->db->transCommit();
            return $this->response->setJSON(['success' => true, 'msg' => 'Subjects saved successfully']);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function saveClassSections()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
        }

        $campusId = $this->resolveCampusId();
        if ($campusId <= 0) {
            $schoolinfo = getSchoolInfo();
            if (isset($schoolinfo->campus_id) && (int) $schoolinfo->campus_id > 0) {
                $campusId = (int) $schoolinfo->campus_id;
            }
        }
        if ($campusId <= 0) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Campus not found. Select a campus in your profile or session.']);
        }
        
        $userId = $this->session->get('member_userid');
        $now = date('Y-m-d H:i:s');

        $assignments = $this->request->getPost('assignments');
        
        if (empty($assignments)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Please assign classes to sections']);
        }

        $this->db->transBegin();

        try {
            // Delete existing assignments for this campus
            $this->db->table('class_section')
                ->where('campus_id', $campusId)
                ->delete();
            
            // Insert new assignments
            foreach ($assignments as $assignment) {
                $classSectionData = [
                    'campus_id' => $campusId,
                    'class_id' => $assignment['class_id'],
                    'section_id' => $assignment['section_id'],
                    'status' => 1,
                    'created_date' => $now,
                    'user_id' => $userId
                ];
                
                $this->db->table('class_section')->insert($classSectionData);
            }
            
            $this->db->transCommit();
            return $this->response->setJSON(['success' => true, 'msg' => 'Class-Section assignments saved successfully']);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function saveSectionSubjects()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
        }

        $campusId = $this->resolveCampusId();
        if ($campusId <= 0) {
            $schoolinfo = getSchoolInfo();
            if (isset($schoolinfo->campus_id) && (int) $schoolinfo->campus_id > 0) {
                $campusId = (int) $schoolinfo->campus_id;
            }
        }
        if ($campusId <= 0) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Campus not found. Select a campus in your profile or session.']);
        }
        
        $userId = $this->session->get('member_userid');
        $now = date('Y-m-d H:i:s');

        $assignments = $this->request->getPost('assignments');
        
        if (empty($assignments)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Please assign subjects to sections']);
        }

        $this->db->transBegin();

        try {
            // Get all class_section IDs for this campus
            $classSections = $this->db->table('class_section')
                ->select('cls_sec_id')
                ->where('campus_id', $campusId)
                ->get()->getResult();
            
            $clsSecIds = array_map(function($cs) { return $cs->cls_sec_id; }, $classSections);
            
            if (!empty($clsSecIds)) {
                // Delete existing assignments
                $this->db->table('section_subjects')
                    ->whereIn('cls_sec_id', $clsSecIds)
                    ->delete();
            }
            
            // Insert new assignments
            foreach ($assignments as $assignment) {
                $sectionSubjectData = [
                    'cls_sec_id' => $assignment['cls_sec_id'],
                    'subject_id' => $assignment['subject_id'],
                    'status' => 1,
                    'created_date' => $now,
                    'user_id' => $userId
                ];
                
                $this->db->table('section_subjects')->insert($sectionSubjectData);
            }
            
            $this->db->transCommit();
            return $this->response->setJSON(['success' => true, 'msg' => 'Subject assignments saved successfully']);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

   


    public function getClassSectionsData()
    {
        $campusId = $this->resolveCampusId();
        
        if ($campusId <= 0) {
            return $this->response->setJSON(['success' => true, 'data' => []]);
        }
        
        $data = $this->getClassSections($campusId);
        
        return $this->response->setJSON(['success' => true, 'data' => $data]);
    }

    public function getSectionSubjectsData()
    {
        $campusId = $this->resolveCampusId();
        
        if ($campusId <= 0) {
            return $this->response->setJSON(['success' => true, 'data' => []]);
        }
        
        $data = $this->getSectionSubjects($campusId);
        
        return $this->response->setJSON(['success' => true, 'data' => $data]);
    }
}