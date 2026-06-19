<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;

class StudentIdCard extends BaseController
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

        // Keep same reliable source as class diary page: class_section.
        $qb = $this->db->table('class_section cs')
            ->select('cs.cls_sec_id, cs.class_id, c.class_name, CONCAT(c.class_name, " - ", s.section_name) AS sectionclassname', false)
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections s', 's.section_id = cs.section_id', 'left')
            ->where('cs.status', 1);

        if ($campusId > 0) {
            $qb->where('cs.campus_id', $campusId);
        }

        $sectionsclassinfo = $qb->orderBy('c.class_name', 'ASC')
            ->orderBy('s.section_name', 'ASC')
            ->get()
            ->getResultArray();

        $classes = [];
        foreach ($sectionsclassinfo as $row) {
            $cid = (int) ($row['class_id'] ?? 0);
            if ($cid > 0) {
                $classes[$cid] = $row['class_name'] ?? ('Class ' . $cid);
            }
        }
        ksort($classes);

        return view('admin/student_id_card', [
            'sectionsclassinfo' => $sectionsclassinfo,
            'classes' => $classes,
        ]);
    }

    public function vertical()
    {
        return $this->index();
    }

    public function data()
    {
        return $this->data_vertical();
    }

    public function data_vertical()
    {
        $campusId = (int) $this->session->get('member_campusid');
        $sessionId = (int) $this->session->get('member_sessionid');
        $classId = (int) $this->request->getPost('class_id');
        $clsSecId = (int) $this->request->getPost('cls_sec_id');
        $statusFilter = strtolower(trim((string) $this->request->getPost('status')));
        $studentIds = $this->parseStudentIds((string) $this->request->getPost('student_ids'));

        if ($statusFilter === '') {
            $statusFilter = 'active';
        }

        $school = getSchoolInfo();
        $campus = $this->db->table('campus')
            ->select('campus_name, landline, location')
            ->where('campus_id', $campusId)
            ->get()
            ->getRow();

        $sessionDates = $this->db->table('academic_session')
            ->select('start_date, end_date')
            ->where('session_id', $sessionId)
            ->get()
            ->getRow();

        $debug = [];
        $students = $this->fetchStudents($campusId, $sessionId, $classId, $clsSecId, $statusFilter, $sessionDates, $studentIds, $debug);

        $schoolName = trim((string) ($school->system_name ?? 'School'));
        $schoolAddress = trim((string) ($campus->location ?? $school->address ?? ''));
        $schoolPhone = trim((string) ($campus->landline ?? ''));
        $logoUrl = $this->logoUrl((string) ($school->logo ?? ''));
        $fallbackAvatar = $this->defaultAvatarUrl();

        $showDebug = ((string) $this->request->getPost('debug') === '1')
            || ((string) $this->request->getGet('debug') === '1');

        $html = $this->stylesBlock();
        if ($showDebug) {
            $html .= '<div class="id-card-debug no-print">
                <strong>ID Card Debug</strong> | Campus: ' . (int) ($debug['campus_id'] ?? 0) .
                ' | Session: ' . (int) ($debug['session_id'] ?? 0) .
                ' | Selected class_id: ' . (int) ($debug['selected_class_id'] ?? 0) .
                ' | Selected cls_sec_id: ' . (int) ($debug['selected_cls_sec_id'] ?? 0) .
                ' | Resolved: ' . esc(implode(',', $debug['resolved_cls_sec_ids'] ?? [])) .
                ' | SC any-session: ' . (int) ($debug['sc_any_session'] ?? 0) .
                ' | SC this-session: ' . (int) ($debug['sc_this_session'] ?? 0) .
                ' | Strict count: ' . (int) ($debug['strict_count'] ?? 0) .
                ' | Fallback count: ' . (int) ($debug['fallback_count'] ?? 0) .
                ' | Legacy count: ' . (int) ($debug['legacy_count'] ?? 0) .
            '</div>';
        }
        $html .= '<div class="id-cards-grid">';

        foreach ($students as $student) {
            $studentName = trim((string) (($student->first_name ?? '') . ' ' . ($student->last_name ?? '')));
            $fatherName = trim((string) ($student->f_name ?? ''));
            $classSection = trim((string) (($student->class_name ?? '') . ' - ' . ($student->section_name ?? '')));
            $regNo = trim((string) ($student->reg_no ?? ''));
            $photoUrl = $this->studentPhotoUrl((string) ($student->profile_photo ?? ''), $fallbackAvatar);
            $qrText = 'SID:' . (int) ($student->student_id ?? 0) . '|REG:' . $regNo . '|CLS:' . $classSection;
            $qrSvg = $this->qrSvg($qrText);

            $html .= '
            <div class="id-card-pair">
                <div class="id-side front-side">
                    <div class="front-header">
                        <div class="front-logo"><img src="' . esc($logoUrl) . '" alt="Logo"></div>
                        <div class="front-school">' . esc($schoolName) . '</div>
                    </div>
                    <div class="front-body">
                        <div class="photo-wrap"><img src="' . esc($photoUrl) . '" alt="Student"></div>
                        <div class="front-details">
                            <div class="row-item"><span class="label">Name</span><span class="value">' . esc($studentName) . '</span></div>
                            <div class="row-item"><span class="label">Father</span><span class="value">' . esc($fatherName) . '</span></div>
                            <div class="row-item"><span class="label">Class</span><span class="value">' . esc($classSection) . '</span></div>
                            <div class="row-item"><span class="label">Reg #</span><span class="value">' . esc($regNo) . '</span></div>
                        </div>
                    </div>
                </div>
                <div class="id-side back-side">
                    <div class="back-header">Student ID Card</div>
                    <div class="back-body">
                        <div class="qr-box">' . $qrSvg . '</div>
                        <div class="back-school">' . esc($schoolName) . '</div>
                        <div class="back-address">' . esc($schoolAddress !== '' ? $schoolAddress : 'Address not set') . '</div>
                    </div>
                    <div class="back-footer">Contact: ' . esc($schoolPhone !== '' ? $schoolPhone : 'N/A') . '</div>
                </div>
            </div>';
        }

        $html .= '</div>';

        return $this->response->setContentType('text/html')->setBody($html);
    }

    private function fetchStudents(
        int $campusId,
        int $sessionId,
        int $classId,
        int $clsSecId,
        string $statusFilter,
        ?object $sessionDates,
        array $studentIds,
        array &$debug = []
    ): array {
        // Use same student lookup pattern as StudentsAbsentees page.
        if ($clsSecId > 0 || $classId > 0) {
            $selectedStudentIds = [];
            $resolvedForDebug = [];
            $studentSectionMap = [];
            if ($clsSecId > 0) {
                $lookupIds = [$clsSecId];
                $mappedSectionId = (int) ($this->db->table('class_section')
                    ->select('section_id')
                    ->where('cls_sec_id', $clsSecId)
                    ->where('campus_id', $campusId)
                    ->get()
                    ->getRow('section_id') ?? 0);
                if ($mappedSectionId > 0) {
                    $lookupIds[] = $mappedSectionId;
                }
                $lookupIds = array_values(array_unique(array_filter($lookupIds)));
                $resolvedForDebug = $lookupIds;

                $rows = $this->db->table('student_class')
                    ->select('student_id, cls_sec_id')
                    ->where('status', 1)
                    ->whereIn('cls_sec_id', $lookupIds)
                    ->get()
                    ->getResultArray();
            } else {
                $classSectionRows = $this->db->table('class_section')
                    ->select('cls_sec_id, section_id')
                    ->where('class_id', $classId)
                    ->where('campus_id', $campusId)
                    ->where('status', 1)
                    ->get()
                    ->getResultArray();
                foreach ($classSectionRows as $csr) {
                    $resolvedForDebug[] = (int) ($csr['cls_sec_id'] ?? 0);
                    $resolvedForDebug[] = (int) ($csr['section_id'] ?? 0);
                }
                $resolvedForDebug = array_values(array_unique(array_filter($resolvedForDebug)));

                $rows = $this->db->table('student_class sc')
                    ->select('sc.student_id, sc.cls_sec_id')
                    ->join('class_section cs', '(cs.cls_sec_id = sc.cls_sec_id OR cs.section_id = sc.cls_sec_id)', 'inner')
                    ->where('sc.status', 1)
                    ->where('cs.class_id', $classId)
                    ->where('cs.campus_id', $campusId)
                    ->get()
                    ->getResultArray();
            }

            foreach ($rows as $r) {
                $sid = (int) ($r['student_id'] ?? 0);
                $scid = (int) ($r['cls_sec_id'] ?? 0);
                if ($sid > 0) {
                    $selectedStudentIds[] = $sid;
                    if (!isset($studentSectionMap[$sid]) && $scid > 0) {
                        $studentSectionMap[$sid] = $scid;
                    }
                }
            }
            $selectedStudentIds = array_values(array_unique(array_filter($selectedStudentIds)));

            if (!empty($studentIds)) {
                $selectedStudentIds = array_values(array_intersect($selectedStudentIds, $studentIds));
            }

            $debug = [
                'campus_id' => $campusId,
                'session_id' => $sessionId,
                'selected_class_id' => $classId,
                'selected_cls_sec_id' => $clsSecId,
                'resolved_cls_sec_ids' => $resolvedForDebug,
                'strict_count' => 0,
                'fallback_count' => 0,
                'legacy_count' => 0,
                'sc_any_session' => count($selectedStudentIds),
                'sc_this_session' => 0,
            ];

            if (empty($selectedStudentIds)) {
                return [];
            }

            $builder = $this->db->table('students s');
            $builder->select('
                s.student_id, s.first_name, s.last_name, s.reg_no, s.profile_photo, s.date_of_admission, s.status,
                p.f_name
            ');
            $builder->join('parents p', 'p.parent_id = s.parent_id', 'left');
            $builder->whereIn('s.student_id', $selectedStudentIds);

            if ($statusFilter === 'new' && $sessionDates && !empty($sessionDates->start_date) && !empty($sessionDates->end_date)) {
                $builder->where('s.date_of_admission >=', $sessionDates->start_date);
                $builder->where('s.date_of_admission <=', $sessionDates->end_date);
            }

            $students = $builder
                ->orderBy('s.first_name', 'ASC')
                ->orderBy('s.last_name', 'ASC')
                ->get()
                ->getResultArray();

            $out = [];
            foreach ($students as $row) {
                $sid = (int) ($row['student_id'] ?? 0);
                $mappedClsSec = (int) ($studentSectionMap[$sid] ?? 0);
                $row['class_name'] = '';
                $row['section_name'] = '';
                if ($mappedClsSec > 0) {
                    $secInfo = getClassSection($mappedClsSec);
                    if (!empty($secInfo)) {
                        $row['class_name'] = (string) ($secInfo['class_name'] ?? '');
                        $row['section_name'] = (string) ($secInfo['section_name'] ?? '');
                    }
                }
                $out[] = (object) $row;
            }

            usort($out, static function ($a, $b) {
                $ka = (($a->class_name ?? '') . '|' . ($a->section_name ?? '') . '|' . ($a->first_name ?? '') . '|' . ($a->last_name ?? ''));
                $kb = (($b->class_name ?? '') . '|' . ($b->section_name ?? '') . '|' . ($b->first_name ?? '') . '|' . ($b->last_name ?? ''));
                return strcmp($ka, $kb);
            });

            return $out;
        }

        $resolvedClsSecIds = $this->resolveClassSectionIds($campusId, $clsSecId, $classId);
        $debug = [
            'campus_id' => $campusId,
            'session_id' => $sessionId,
            'selected_class_id' => $classId,
            'selected_cls_sec_id' => $clsSecId,
            'resolved_cls_sec_ids' => $resolvedClsSecIds,
            'strict_count' => 0,
            'fallback_count' => 0,
            'legacy_count' => 0,
            'sc_any_session' => 0,
            'sc_this_session' => 0,
        ];

        if (!empty($resolvedClsSecIds)) {
            $debug['sc_any_session'] = (int) $this->db->table('student_class')
                ->whereIn('cls_sec_id', $resolvedClsSecIds)
                ->where('status', 1)
                ->countAllResults();
            if ($sessionId > 0) {
                $debug['sc_this_session'] = (int) $this->db->table('student_class')
                    ->whereIn('cls_sec_id', $resolvedClsSecIds)
                    ->where('session_id', $sessionId)
                    ->where('status', 1)
                    ->countAllResults();
            }
        }

        // Pass 1: strict current session mapping.
        $rows = $this->runStudentsQuery($campusId, $sessionId, true, $clsSecId, $resolvedClsSecIds, $statusFilter, $sessionDates, $studentIds);
        $debug['strict_count'] = count($rows);
        if (!empty($rows) || $sessionId <= 0) {
            return $rows;
        }

        // Pass 2 (fallback): campus + active class mapping without session lock.
        $fallbackRows = $this->runStudentsQuery($campusId, $sessionId, false, $clsSecId, $resolvedClsSecIds, $statusFilter, $sessionDates, $studentIds);
        $debug['fallback_count'] = count($fallbackRows);
        if (!empty($fallbackRows)) {
            return $fallbackRows;
        }

        // Pass 3 (legacy fallback): some campuses keep class mapping directly on students.cls_sec_id.
        $legacyRows = $this->runStudentsLegacyQuery($campusId, $resolvedClsSecIds, $statusFilter, $sessionDates, $studentIds);
        $debug['legacy_count'] = count($legacyRows);
        return $legacyRows;
    }

    private function runStudentsQuery(
        int $campusId,
        int $sessionId,
        bool $enforceSession,
        int $selectedClsSecId,
        array $resolvedClsSecIds,
        string $statusFilter,
        ?object $sessionDates,
        array $studentIds
    ): array {
        $builder = $this->db->table('student_class sc');
        $builder->select('
            s.student_id, s.first_name, s.last_name, s.reg_no, s.profile_photo, s.date_of_admission, s.status,
            p.f_name,
            c.class_name, sec.section_name
        ');
        $builder->join('students s', 's.student_id = sc.student_id', 'inner');
        $builder->join('parents p', 'p.parent_id = s.parent_id', 'left');
        $builder->join('class_section cs', '(cs.cls_sec_id = sc.cls_sec_id OR cs.section_id = sc.cls_sec_id)', 'left');
        $builder->join('classes c', 'c.class_id = cs.class_id', 'left');
        $builder->join('sections sec', 'sec.section_id = cs.section_id', 'left');
        if ($campusId > 0) {
            $builder->groupStart()
                ->where('s.campus_id', $campusId)
                ->orWhere('cs.campus_id', $campusId)
                ->groupEnd();
        }
        if ($enforceSession && $sessionId > 0) {
            $builder->where('sc.session_id', $sessionId);
        }
        $builder->where('sc.status', 1);

        if ($selectedClsSecId > 0) {
            if (!empty($resolvedClsSecIds)) {
                $builder->whereIn('sc.cls_sec_id', $resolvedClsSecIds);
            } else {
                $builder->where('sc.cls_sec_id', $selectedClsSecId);
            }
        }

        if (!empty($studentIds)) {
            $builder->whereIn('s.student_id', $studentIds);
        }

        if ($statusFilter === 'new') {
            if ($sessionDates && !empty($sessionDates->start_date) && !empty($sessionDates->end_date)) {
                $builder->where('s.date_of_admission >=', $sessionDates->start_date);
                $builder->where('s.date_of_admission <=', $sessionDates->end_date);
            }
        }

        return $builder
            ->orderBy('c.class_name', 'ASC')
            ->orderBy('sec.section_name', 'ASC')
            ->orderBy('s.first_name', 'ASC')
            ->orderBy('s.last_name', 'ASC')
            ->groupBy('s.student_id')
            ->get()
            ->getResult();
    }

    private function resolveClassSectionIds(int $campusId, int $clsSecId, int $classId = 0): array
    {
        $resolvedClsSecIds = [];

        if ($clsSecId > 0) {
            $rowByClsSecId = $this->db->table('class_section')
                ->select('cls_sec_id, section_id, class_id')
                ->where('cls_sec_id', $clsSecId)
                ->where('campus_id', $campusId)
                ->get()
                ->getRow();

            if ($rowByClsSecId) {
                $resolvedClsSecIds[] = (int) $rowByClsSecId->cls_sec_id;
            } else {
                $rowsBySectionId = $this->db->table('class_section')
                    ->select('cls_sec_id, section_id')
                    ->where('section_id', $clsSecId)
                    ->where('campus_id', $campusId)
                    ->get()
                    ->getResult();

                foreach ($rowsBySectionId as $sectionRow) {
                    $resolvedClsSecIds[] = (int) $sectionRow->cls_sec_id;
                }
            }
        } elseif ($classId > 0) {
            $rowsByClass = $this->db->table('class_section')
                ->select('cls_sec_id, section_id')
                ->where('class_id', $classId)
                ->where('campus_id', $campusId)
                ->where('status', 1)
                ->get()
                ->getResult();
            foreach ($rowsByClass as $sectionRow) {
                $resolvedClsSecIds[] = (int) $sectionRow->cls_sec_id;
            }
        }

        return array_values(array_unique(array_filter($resolvedClsSecIds)));
    }

    private function runStudentsLegacyQuery(
        int $campusId,
        array $resolvedClsSecIds,
        string $statusFilter,
        ?object $sessionDates,
        array $studentIds
    ): array {
        $builder = $this->db->table('students s');
        $builder->select('
            s.student_id, s.first_name, s.last_name, s.reg_no, s.profile_photo, s.date_of_admission, s.status,
            p.f_name,
            c.class_name, sec.section_name
        ');
        $builder->join('parents p', 'p.parent_id = s.parent_id', 'left');
        $builder->join('class_section cs', '(cs.cls_sec_id = s.cls_sec_id OR cs.section_id = s.cls_sec_id)', 'left');
        $builder->join('classes c', 'c.class_id = cs.class_id', 'left');
        $builder->join('sections sec', 'sec.section_id = cs.section_id', 'left');
        $builder->where('s.campus_id', $campusId);

        if (!empty($resolvedClsSecIds)) {
            $builder->whereIn('s.cls_sec_id', $resolvedClsSecIds);
        }

        if (!empty($studentIds)) {
            $builder->whereIn('s.student_id', $studentIds);
        }

        if ($statusFilter === 'new' && $sessionDates && !empty($sessionDates->start_date) && !empty($sessionDates->end_date)) {
            $builder->where('s.date_of_admission >=', $sessionDates->start_date);
            $builder->where('s.date_of_admission <=', $sessionDates->end_date);
        }

        return $builder
            ->orderBy('c.class_name', 'ASC')
            ->orderBy('sec.section_name', 'ASC')
            ->orderBy('s.first_name', 'ASC')
            ->orderBy('s.last_name', 'ASC')
            ->groupBy('s.student_id')
            ->get()
            ->getResult();
    }

    private function parseStudentIds(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $parts = preg_split('/[\s,]+/', $raw);
        $ids = [];
        foreach ($parts as $part) {
            if ($part !== '' && ctype_digit($part)) {
                $ids[] = (int) $part;
            }
        }

        return array_values(array_unique($ids));
    }

    private function qrDataUri(string $text): string
    {
        try {
            $qrCode = QrCode::create($text)->setSize(220)->setMargin(8);
            return (new PngWriter())->write($qrCode)->getDataUri();
        } catch (\Throwable $e) {
            return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="220" height="220"><rect width="220" height="220" fill="#fff"/><text x="110" y="110" dominant-baseline="middle" text-anchor="middle" fill="#999" font-size="18">QR</text></svg>'
            );
        }
    }

    private function qrSvg(string $text): string
    {
        try {
            $qrCode = QrCode::create($text)->setSize(220)->setMargin(8);
            $svg = (new SvgWriter())->write($qrCode)->getString();
            // Keep markup compact for card rendering.
            return '<div class="qr-svg">' . $svg . '</div>';
        } catch (\Throwable $e) {
            return '<div class="qr-fallback">QR</div>';
        }
    }

    private function defaultAvatarUrl(): string
    {
        $candidates = ['assets/img/avatar-student.png', 'assets/img/avatar.png', 'assets/images/avatar.png'];
        foreach ($candidates as $rel) {
            $disk = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel);
            if (is_file($disk)) {
                return base_url($rel);
            }
        }

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 120"><rect width="100" height="120" fill="#eef2f7"/><circle cx="50" cy="38" r="18" fill="#c9d1dc"/><rect x="18" y="65" width="64" height="40" rx="10" fill="#dbe2ec"/></svg>'
        );
    }

    private function studentPhotoUrl(string $photo, string $fallback): string
    {
        $photo = trim($photo);
        if ($photo === '') {
            return $fallback;
        }

        if (preg_match('~^https?://~i', $photo)) {
            return $photo;
        }

        $path = ltrim($photo, '/\\');
        if (stripos($path, 'uploads/') !== 0) {
            $path = 'uploads/' . $path;
        }

        $disk = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        return is_file($disk) ? base_url(str_replace('\\', '/', $path)) : $fallback;
    }

    private function logoUrl(string $logo): string
    {
        $logo = trim($logo);
        if ($logo !== '') {
            $path = 'system-logo/' . ltrim($logo, '/\\');
            $disk = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            if (is_file($disk)) {
                return base_url($path);
            }
        }

        return $this->defaultAvatarUrl();
    }

    private function stylesBlock(): string
    {
        return '<style>
.id-card-debug{
  margin-bottom:10px;
  padding:8px 10px;
  border:1px solid #fde68a;
  border-radius:10px;
  background:#fffbeb;
  color:#8a5a00;
  font-size:12px;
}
.id-cards-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(85.6mm,85.6mm));
  gap:6mm;
  justify-content:center;
}
.id-card-pair{
  width:85.6mm;
  display:grid;
  grid-template-columns:1fr 1fr;
  column-gap:1.4mm;
  break-inside:avoid;
  page-break-inside:avoid;
}
.id-side{
  width:42.8mm;
  height:54mm;
  border:1px solid #163454;
  border-radius:2.2mm;
  overflow:hidden;
  background:#fff;
  position:relative;
  box-shadow:0 2mm 3mm rgba(15,23,42,0.08);
}
.front-side{
  background:linear-gradient(180deg,#ffffff 0%,#f8fbff 100%);
}
.front-header{
  min-height:10mm;
  background:linear-gradient(135deg,#163e68,#256f9f);
  color:#fff;
  display:flex;
  align-items:center;
  gap:2mm;
  padding:1.4mm 1.6mm 1.2mm;
}
.front-logo{
  width:7mm;
  height:7mm;
  border-radius:50%;
  background:#fff;
  overflow:hidden;
  flex-shrink:0;
  padding:0.45mm;
  box-shadow:0 0 0 0.35mm rgba(255,255,255,0.24);
}
.front-logo img{width:100%;height:100%;object-fit:cover;}
.front-school{
  font-size:2.25mm;
  font-weight:700;
  line-height:1.15;
  overflow:hidden;
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
}
.front-body{
  display:grid;
  grid-template-columns:14.4mm 1fr;
  gap:1.5mm;
  padding:1.6mm;
}
.photo-wrap{
  width:14.4mm;
  height:18.5mm;
  border:1px solid #d7e1ec;
  border-radius:1.4mm;
  overflow:hidden;
  background:#f8fafc;
  box-shadow:inset 0 0 0 0.4mm rgba(255,255,255,0.55);
}
.photo-wrap img{width:100%;height:100%;object-fit:cover;}
.front-details{
  font-size:2.15mm;
  line-height:1.2;
  display:flex;
  flex-direction:column;
  gap:0.9mm;
}
.row-item{
  padding-bottom:0.8mm;
  border-bottom:0.2mm dashed #d9e3ef;
  overflow:hidden;
}
.row-item:last-child{
  padding-bottom:0;
  border-bottom:0;
}
.row-item .label{
  display:block;
  font-weight:700;
  color:#5b6f84;
  letter-spacing:0.02em;
  text-transform:uppercase;
}
.row-item .value{
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow:hidden;
  color:#111827;
  font-weight:700;
}
.back-side{
  background:linear-gradient(180deg,#ffffff 0%,#f5f8fc 100%);
}
.back-header{
  height:8mm;
  background:linear-gradient(135deg,#184974,#215b90);
  color:#fff;
  font-weight:700;
  display:flex;
  align-items:center;
  justify-content:center;
  text-align:center;
  padding:0 1.2mm;
  font-size:2.2mm;
  letter-spacing:0.04em;
}
.back-body{
  padding:2mm 1.6mm 4.4mm;
  text-align:center;
}
.qr-box{
  width:18.5mm;
  height:18.5mm;
  border:1px solid #d5dee8;
  border-radius:1.6mm;
  margin:0 auto 1.8mm;
  padding:0.8mm;
  background:#fff;
  box-shadow:0 1.1mm 2mm rgba(15,23,42,0.06);
}
.qr-box img{width:100%;height:100%;object-fit:contain;}
.qr-box .qr-svg, .qr-box .qr-svg svg{
  width:100%;
  height:100%;
  display:block;
}
.qr-box .qr-fallback{
  width:100%;
  height:100%;
  display:flex;
  align-items:center;
  justify-content:center;
  color:#777;
  font-size:2.3mm;
  font-weight:600;
}
.back-school{
  font-size:2.2mm;
  font-weight:800;
  margin-bottom:0.7mm;
  color:#163454;
}
.back-address{
  font-size:1.9mm;
  line-height:1.3;
  color:#46576a;
  min-height:8mm;
}
.back-footer{
  position:absolute;
  left:0;right:0;bottom:0;
  border-top:1px solid #e1e8f0;
  background:#eef4f9;
  text-align:center;
  font-size:1.85mm;
  font-weight:700;
  color:#35536c;
  padding:1mm 0.8mm;
}
@media print{
  .id-card-debug{display:none !important;}
  .id-cards-grid{
    grid-template-columns:repeat(2,85.6mm);
    gap:5mm 6mm;
    justify-content:start;
  }
  .id-side{
    box-shadow:none;
  }
}
</style>';
    }

    private function normalizeSectionOptions(array $rows, int $campusId): array
    {
        $normalized = [];
        $seen = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $clsSecId = (int) ($row['cls_sec_id'] ?? 0);
            $sectionId = (int) ($row['section_id'] ?? 0);
            $classId = (int) ($row['class_id'] ?? 0);
            $label = trim((string) ($row['sectionclassname'] ?? ''));

            // If cls_sec_id missing, try mapping from class+section in this campus.
            if ($clsSecId <= 0 && $classId > 0 && $sectionId > 0) {
                $clsSecId = (int) ($this->db->table('class_section')
                    ->select('cls_sec_id')
                    ->where('campus_id', $campusId)
                    ->where('class_id', $classId)
                    ->where('section_id', $sectionId)
                    ->where('status', 1)
                    ->get()
                    ->getRow('cls_sec_id') ?? 0);
            }

            // Legacy helpers sometimes provide section_id but actually store cls_sec_id.
            if ($clsSecId <= 0 && $sectionId > 0) {
                $maybeClsSec = (int) ($this->db->table('class_section')
                    ->select('cls_sec_id')
                    ->where('campus_id', $campusId)
                    ->where('cls_sec_id', $sectionId)
                    ->where('status', 1)
                    ->get()
                    ->getRow('cls_sec_id') ?? 0);
                if ($maybeClsSec > 0) {
                    $clsSecId = $maybeClsSec;
                }
            }

            if ($clsSecId <= 0) {
                continue;
            }

            // Fill label from DB if helper label is missing.
            if ($label === '') {
                $dbRow = $this->db->table('class_section cs')
                    ->select('CONCAT(c.class_name, " - ", s.section_name) AS sectionclassname')
                    ->join('classes c', 'c.class_id = cs.class_id', 'left')
                    ->join('sections s', 's.section_id = cs.section_id', 'left')
                    ->where('cs.cls_sec_id', $clsSecId)
                    ->where('cs.campus_id', $campusId)
                    ->get()
                    ->getRowArray();
                $label = trim((string) ($dbRow['sectionclassname'] ?? ''));
            }

            if ($label === '') {
                $label = 'Section ' . $clsSecId;
            }

            if (isset($seen[$clsSecId])) {
                continue;
            }
            $seen[$clsSecId] = true;

            $normalized[] = [
                'cls_sec_id' => $clsSecId,
                'sectionclassname' => $label,
            ];
        }

        usort($normalized, static function ($a, $b) {
            return strcmp((string) $a['sectionclassname'], (string) $b['sectionclassname']);
        });

        return $normalized;
    }
}
