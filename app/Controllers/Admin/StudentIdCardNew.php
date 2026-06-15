<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class StudentIdCardNew extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url', 'text']);
        $this->db = \Config\Database::connect();
        $this->session = session();
        check_permission('admin-student-id-cards');
    }

    public function index()
    {
        $campusId = (int) $this->session->get('member_campusid');
        $sessionId = (int) $this->session->get('member_sessionid');
        
        // Get class sections that actually have students in current session
        $classSections = $this->getClassSectionsWithStudents($campusId, $sessionId);
        
        // Extract unique classes
        $classes = [];
        $classesMap = [];
        foreach ($classSections as $section) {
            $classId = (int) ($section['class_id'] ?? 0);
            if ($classId > 0 && !isset($classesMap[$classId])) {
                $classesMap[$classId] = true;
                $classes[] = [
                    'class_id' => $classId,
                    'class_name' => (string) ($section['class_name'] ?? 'Class ' . $classId),
                ];
            }
        }

        return view('admin/student_id_card_new', [
            'classesinfo' => $classes,
            'classSections' => $classSections,
            'selected_session_id' => $sessionId,
        ]);
    }

    /**
     * Get class sections that actually have students in the current session
     */
    private function getClassSectionsWithStudents(int $campusId, int $sessionId): array
    {
        $sql = "
            SELECT DISTINCT 
                cs.cls_sec_id, 
                cs.class_id, 
                cs.section_id, 
                c.class_name, 
                sec.section_name,
                CONCAT(c.class_name, ' - ', sec.section_name) as sectionclassname
            FROM student_class sc
            INNER JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
            INNER JOIN classes c ON c.class_id = cs.class_id
            INNER JOIN sections sec ON sec.section_id = cs.section_id
            WHERE sc.status = 1
                AND sc.session_id = ?
                AND cs.campus_id = ?
                AND cs.status = 1
            ORDER BY c.class_name ASC, sec.section_name ASC
        ";
        
        $query = $this->db->query($sql, [$sessionId, $campusId]);
        return $query->getResultArray();
    }

    /**
     * Generate QR code as base64
     */
    private function generateQRCodeBase64(string $studentId, string $regNo): string
    {
        try {
            $payload = 'SID:' . $studentId . '|REG:' . $regNo;
            
            $qrCode = new QrCode($payload);
            
            if (method_exists($qrCode, 'setSize')) {
                $qrCode->setSize(200);
            }
            if (method_exists($qrCode, 'setMargin')) {
                $qrCode->setMargin(10);
            }
            
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            
            return 'data:image/png;base64,' . base64_encode($result->getString());
            
        } catch (\Exception $e) {
            log_message('error', 'QR generation error: ' . $e->getMessage());
            return '';
        }
    }

    public function data()
    {
        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        try {
            $campusId = (int) $this->session->get('member_campusid');
            $sessionId = (int) $this->session->get('member_sessionid');
            $classId = (int) $this->request->getPost('class_id');
            $clsSecId = (int) ($this->request->getPost('cls_sec_id') ?? $this->request->getPost('section_id'));
            $studentIdsInput = trim((string) $this->request->getPost('student_ids'));
            $limitedStudentIds = $this->parseStudentIds($studentIdsInput);
            
            // Log request parameters
            log_message('debug', 'ID Card Request - Campus: ' . $campusId . ', Session: ' . $sessionId . ', Class: ' . $classId . ', Section: ' . $clsSecId);
            
            $studentClassRows = $this->loadStudentClassRows($campusId, $sessionId, $classId, $clsSecId);
            
            if (empty($studentClassRows)) {
                log_message('debug', 'No student class rows found');
                return $this->response->setContentType('text/html')->setBody('<div class="alert alert-info mb-0">No students found for selected filters.</div>');
            }
            
            log_message('debug', 'Found ' . count($studentClassRows) . ' student class rows');

            $studentIds = [];
            foreach ($studentClassRows as $row) {
                $sid = (int) ($row['student_id'] ?? 0);
                if ($sid > 0) {
                    $studentIds[] = $sid;
                }
            }
            $studentIds = array_values(array_unique($studentIds));
            
            log_message('debug', 'Unique student IDs: ' . json_encode($studentIds));

            if (!empty($limitedStudentIds)) {
                $studentIds = array_values(array_intersect($studentIds, $limitedStudentIds));
                log_message('debug', 'After limited filter: ' . json_encode($studentIds));
            }
            
            if (empty($studentIds)) {
                return $this->response->setContentType('text/html')->setBody('<div class="alert alert-info mb-0">No students found for selected filters.</div>');
            }

            // Create placeholders for IN clause
            $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
            
            // Get student details with class and section info
            $sql = "
                SELECT 
                    s.student_id, 
                    s.first_name, 
                    s.last_name, 
                    s.reg_no, 
                    s.profile_photo, 
                    p.f_name, 
                    c.class_name, 
                    sec.section_name
                FROM students s
                LEFT JOIN parents p ON p.parent_id = s.parent_id
                LEFT JOIN student_class sc ON sc.student_id = s.student_id AND sc.session_id = ? AND sc.status = 1
                LEFT JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id AND cs.status = 1
                LEFT JOIN classes c ON c.class_id = cs.class_id
                LEFT JOIN sections sec ON sec.section_id = cs.section_id
                WHERE s.student_id IN ({$placeholders})
                    AND s.status = '1'
                    AND s.campus_id = ?
            ";
            
            $params = array_merge([$sessionId], $studentIds, [$campusId]);
            
            log_message('debug', 'SQL Query: ' . $sql);
            log_message('debug', 'SQL Params: ' . json_encode($params));
            
            $query = $this->db->query($sql, $params);
            $students = $query->getResultArray();
            
            log_message('debug', 'Found ' . count($students) . ' students with details');

            $school = getSchoolInfo();
            $campus = $this->db->table('campus')
                ->select('campus_name, landline, location')
                ->where('campus_id', $campusId)
                ->get()
                ->getRow();

            $schoolName = trim((string) ($school->system_name ?? 'School'));
            $schoolAddress = trim((string) ($campus->location ?? $school->address ?? 'Address not set'));
            $schoolPhone = trim((string) ($campus->landline ?? 'N/A'));
            $logoUrl = $this->logoUrl((string) ($school->logo ?? ''));
            $avatar = $this->defaultAvatarUrl();

            $html = $this->styleBlock() . '<div class="id-cards-grid">';
            
            foreach ($students as $s) {
                $sid = (int) ($s['student_id'] ?? 0);
                $classSection = trim((string) (($s['class_name'] ?? '') . ' - ' . ($s['section_name'] ?? '')));
                $name = trim((string) (($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')));
                $father = trim((string) ($s['f_name'] ?? ''));
                $regNo = trim((string) ($s['reg_no'] ?? ''));
                $photo = $this->photoUrl((string) ($s['profile_photo'] ?? ''), $avatar);
                
                $qrBase64 = $this->generateQRCodeBase64((string)$sid, $regNo);
                $qrHtml = '<div class="qr-box"><img src="' . esc($qrBase64) . '" alt="QR Code"></div>';

                $html .= '<div class="id-card-pair">
                    <div class="id-side front-side">
                        
                        <div class="front-header">
                            <div class="front-logo"><img src="' . esc($logoUrl) . '" alt="Logo"></div>
                            <div class="front-school">' . esc($schoolName) . '</div>
                        </div>
                        <div class="front-body">
                            <div class="photo-wrap">
                                <img src="' . esc($photo) . '" alt="Student">
                            </div>
                            <div class="student-name">
                                <span class="label">Student Name</span>
                                <span class="value">' . esc($name) . '</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Father\'s Name</span>
                                <span class="value">' . esc($father) . '</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Class</span>
                                <span class="value">' . esc($classSection !== '' ? $classSection : 'N/A') . '</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Registration No</span>
                                <span class="value">' . esc($regNo) . '</span>
                            </div>
                        </div>
                        
                    </div>
                    <div class="id-side back-side">
                        <div class="back-header">IDENTITY CARD</div>
                        <div class="back-body">
                            <div class="qr-container">
                                ' . $qrHtml . '
                            </div>
                            <div class="back-school">' . esc($schoolName) . '</div>
                            <div class="back-address">' . esc($schoolAddress) . '</div>
                            <div class="contact-info">
                                <span>📞 ' . esc($schoolPhone) . '</span>
                            </div>
                        </div>
                        <div class="back-footer">Valid ID Card · Keep Safe</div>
                    </div>
                </div>';
            }
            $html .= '</div>';
            
            log_message('debug', 'HTML generated successfully');
            return $this->response->setContentType('text/html')->setBody($html);
            
        } catch (\Exception $e) {
            log_message('error', 'ID Card Generation Error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setContentType('text/html')->setBody('<div class="alert alert-danger mb-0">Error: ' . $e->getMessage() . '</div>');
        }
    }

    private function loadStudentClassRows(int $campusId, int $sessionId, int $classId, int $clsSecId): array
    {
        $sql = "
            SELECT sc.student_id
            FROM student_class sc
            INNER JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
            WHERE sc.status = 1
                AND sc.session_id = ?
                AND cs.campus_id = ?
                AND cs.status = 1
        ";
        
        $params = [$sessionId, $campusId];
        
        if ($clsSecId > 0) {
            $sql .= " AND cs.cls_sec_id = ?";
            $params[] = $clsSecId;
        } elseif ($classId > 0) {
            $sql .= " AND cs.class_id = ?";
            $params[] = $classId;
        }
        
        $query = $this->db->query($sql, $params);
        return $query->getResultArray();
    }

    private function parseStudentIds(string $raw): array
    {
        if ($raw === '') return [];
        $parts = preg_split('/[\s,]+/', $raw);
        $ids = [];
        foreach ($parts as $part) {
            if ($part !== '' && ctype_digit($part)) $ids[] = (int) $part;
        }
        return array_values(array_unique($ids));
    }

    private function photoUrl(string $photo, string $fallback): string
    {
        $photo = trim($photo);
        if ($photo === '') return $fallback;
        $path = ltrim($photo, '/\\');
        if (stripos($path, 'uploads/') !== 0) $path = 'uploads/' . $path;
        $disk = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        return is_file($disk) ? base_url(str_replace('\\', '/', $path)) : $fallback;
    }

    private function logoUrl(string $logo): string
    {
        $logo = trim($logo);
        if ($logo !== '') {
            $path = 'system-logo/' . ltrim($logo, '/\\');
            $disk = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            if (is_file($disk)) return base_url($path);
        }
        return $this->defaultAvatarUrl();
    }

    private function defaultAvatarUrl(): string
    {
        $candidates = ['assets/img/avatar-student.png', 'assets/img/avatar.png', 'assets/images/avatar.png'];
        foreach ($candidates as $rel) {
            $disk = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel);
            if (is_file($disk)) return base_url($rel);
        }
        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 120"><rect width="100" height="120" fill="#eef2f7"/><circle cx="50" cy="38" r="18" fill="#c9d1dc"/><rect x="18" y="65" width="64" height="40" rx="10" fill="#dbe2ec"/></svg>');
    }

    private function styleBlock(): string
    {
        return '<style>
    .id-cards-grid {
        display: grid;
        grid-template-columns: repeat(2, 85.6mm);
        gap: 8mm 6mm;
        justify-content: center;
        padding: 10px;
        background: #f0f2f5;
    }
    
    .id-card-pair {
        width: 85.6mm;
        display: grid;
        grid-template-columns: 1fr 1fr;
        column-gap: 1.5mm;
        break-inside: avoid;
        page-break-inside: avoid;
        transition: transform 0.3s ease;
    }
    
    .id-card-pair:hover {
        transform: translateY(-2px);
    }
    
    .id-side {
        width: 42.8mm;
        height: 54mm;
        border-radius: 3mm;
        overflow: hidden;
        background: #fff;
        position: relative;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: box-shadow 0.3s ease;
    }
    
    .id-side:hover {
        box-shadow: 0 6px 16px rgba(0,0,0,0.15);
    }
    
    /* Front Side Styles */
    .front-side {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
    }
    
    .front-side::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url(\'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.1"><circle cx="20" cy="20" r="15" fill="white"/><circle cx="80" cy="80" r="25" fill="white"/><circle cx="50" cy="50" r="8" fill="white"/></svg>\') repeat;
        pointer-events: none;
    }
    
    .front-header {
        background: rgba(255,255,255,0.95);
        padding: 2mm 2.5mm;
        display: flex;
        align-items: center;
        gap: 2mm;
        border-bottom: 2px solid #fbbf24;
    }
    
    .front-logo {
        width: 7mm;
        height: 7mm;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .front-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .front-school {
        font-size: 2.4mm;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: 0.3px;
        line-height: 1.3;
        flex: 1;
    }
    
    .card-badge {
        position: absolute;
        top: 2mm;
        right: 2mm;
        background: #fbbf24;
        color: #1e293b;
        padding: 0.5mm 1.5mm;
        border-radius: 2mm;
        font-size: 1.8mm;
        font-weight: 700;
        z-index: 1;
    }
    
    .front-body {
        padding: 2.5mm;
        position: relative;
        z-index: 1;
    }
    
    .photo-wrap {
        width: 14mm;
        height: 18mm;
        border-radius: 2mm;
        overflow: hidden;
        background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
        border: 2px solid white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin: 0 auto 2mm;
    }
    
    .photo-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .student-name {
        text-align: center;
        margin-bottom: 2mm;
    }
    
    .student-name .label {
        font-size: 1.8mm;
        color: rgba(255,255,255,0.8);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
    }
    
    .student-name .value {
        font-size: 2.8mm;
        font-weight: 800;
        color: white;
        line-height: 1.3;
        text-transform: uppercase;
    }
    
    .info-row {
        margin-bottom: 1.5mm;
        background: rgba(255,255,255,0.15);
        padding: 1mm 1.5mm;
        border-radius: 1.5mm;
    }
    
    .info-row .label {
        font-size: 1.6mm;
        color: rgba(255,255,255,0.8);
        display: block;
        letter-spacing: 0.3px;
    }
    
    .info-row .value {
        font-size: 2.2mm;
        font-weight: 600;
        color: white;
        display: block;
        line-height: 1.2;
    }
    
    /* Back Side Styles */
    .back-side {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        position: relative;
    }
    
    .back-header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: white;
        padding: 2.5mm;
        text-align: center;
        font-weight: 800;
        font-size: 2.5mm;
        letter-spacing: 1px;
        text-transform: uppercase;
        border-bottom: 3px solid #fbbf24;
    }
    
    .back-body {
        padding: 2.5mm;
        text-align: center;
    }
    
    .qr-container {
        background: white;
        border-radius: 2.5mm;
        padding: 1.5mm;
        margin-bottom: 2mm;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        display: inline-block;
    }
    
    .qr-box {
        width: 16mm;
        height: 16mm;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .qr-box img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    
    .back-school {
        font-size: 2.4mm;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 1.5mm;
        text-transform: uppercase;
    }
    
    .back-address {
        font-size: 1.8mm;
        color: #475569;
        line-height: 1.4;
        margin-bottom: 1.5mm;
    }
    
    .contact-info {
        background: rgba(255,255,255,0.8);
        padding: 1mm;
        border-radius: 1.5mm;
        margin-top: 1mm;
    }
    
    .contact-info span {
        font-size: 1.6mm;
        color: #475569;
        display: block;
    }
    
    .back-footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: white;
        text-align: center;
        padding: 1.2mm;
        font-size: 1.6mm;
        font-weight: 600;
    }
    
    /* Holographic effect */
    .id-side {
        position: relative;
        overflow: hidden;
    }
    
    .id-side::after {
        content: "";
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(
            45deg,
            transparent 30%,
            rgba(255,255,255,0.1) 50%,
            transparent 70%
        );
        transform: rotate(45deg);
        pointer-events: none;
    }
    
    @media print {
        .id-cards-grid {
            grid-template-columns: repeat(2, 85.6mm);
            gap: 5mm 6mm;
            justify-content: start;
            background: white;
            padding: 0;
        }
        
        .id-card-pair:hover {
            transform: none;
        }
        
        .id-side {
            box-shadow: none;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        
        .id-side:hover {
            box-shadow: none;
        }
    }
    
    /* Status badge */
    .status-badge {
        position: absolute;
        bottom: 2mm;
        left: 2mm;
        background: rgba(34,197,94,0.9);
        color: white;
        padding: 0.5mm 1.5mm;
        border-radius: 1.5mm;
        font-size: 1.4mm;
        font-weight: 600;
        z-index: 1;
    }
    </style>';
    }
}