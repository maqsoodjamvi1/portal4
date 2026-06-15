<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\StudentsModel;

class StudentsBulkPhotos extends BaseController
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

    public function searchByName()
    {
        $q          = trim((string) $this->request->getGet('q'));
        $cls_sec_id = (int) $this->request->getGet('cls_sec_id');
        $limit      = (int) ($this->request->getGet('limit') ?: 20);
        $limit      = max(1, min($limit, 50));

        if ($q === '') {
            return $this->response->setJSON(['results' => []]);
        }

        $campus_id  = (int) ($this->session->get('member_campusid') ?: 0);
        $session_id = (int) (
            $this->request->getGet('session_id')
            ?: $this->session->get('member_sessionid')
            ?: $this->session->get('session_id')
            ?: 0
        );

        $builder = $this->db->table('students s')
            ->distinct()
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

        $builder->groupStart()
            ->like('s.first_name', $q)
            ->orLike('s.last_name', $q)
            ->groupEnd();

        $rows = $builder->orderBy('s.first_name', 'ASC')
            ->orderBy('s.last_name', 'ASC')
            ->limit($limit)
            ->get()->getResult();

        $results = [];
        foreach ($rows as $r) {
            $name = trim(($r->first_name ?? '') . ' ' . ($r->last_name ?? '')) ?: (string) ($r->first_name ?? 'Student');
            $results[] = [
                'id'         => (int) $r->student_id,
                'text'       => $name,
                'parent_id'  => (int) $r->parent_id,
                'cls_sec_id' => (int) $r->cls_sec_id,
            ];
        }

        return $this->response->setJSON(['results' => $results]);
    }

    public function index()
    {
        $campus_id   = $this->session->get('member_campusid');
        $currentrole = currentUserRoles();

        $data = [
            'sectionsclassinfo' => in_array(5, $currentrole) ? teacherSubjectSections() : $this->userClassSections(),
            'campus_info'       => $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow(),
        ];

        return view('admin/students_bulk_photos', $data);
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

    /**
     * One table row for bulk photos (kept in controller so /data works even if the partial view was not deployed).
     */
    protected function renderPhotoRow(object $student): string
    {
        $name       = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) ?: (string) ($student->first_name ?? 'Student');
        $fatherName = trim((string) ($student->father_name ?? ''));
        $photo      = basename((string) ($student->profile_photo ?? ''));
        $sid        = (int) ($student->student_id ?? 0);

        $imgUrl = ($photo !== '' && $photo !== '.' && $photo !== '..')
            ? base_url('uploads/' . $photo)
            : '';

        $currentCell = $imgUrl !== ''
            ? '<img class="photoPreviewExisting photo-thumb" src="' . esc($imgUrl) . '" alt="">'
            : '<div class="photoPreview photo-thumb photo-thumb--empty text-muted d-flex align-items-center justify-content-center">No photo</div>';

        $nameCell = '<div class="student-identity">'
            . '<div class="student-display-name">' . esc($name) . '</div>';
        if ($fatherName !== '') {
            $nameCell .= '<div class="student-father-name">'
                . '<span class="student-father-label">Father</span>'
                . '<span class="student-father-value">' . esc($fatherName) . '</span>'
                . '</div>';
        } else {
            $nameCell .= '<div class="student-father-name student-father-name--empty">'
                . '<span class="student-father-label">Father</span>'
                . '<span class="student-father-value text-muted">Not set</span>'
                . '</div>';
        }
        $nameCell .= '</div>';

        $uploadCell = '<div class="photo-upload-panel">'
            . '<div class="photo-upload-panel__current">' . $currentCell . '</div>'
            . '<div class="photo-upload-panel__actions">'
            . '<input type="file" class="form-control form-control-sm fileInputPhoto" name="profile_photo" accept="image/*" aria-label="Choose photo file">'
            . '<div class="photo-upload-btns">'
            . '<button type="button" class="btn btn-outline-secondary btn-sm btnCapturePhoto" title="Open camera">'
            . '<i class="fas fa-camera me-1" aria-hidden="true"></i><span>Capture</span>'
            . '</button>'
            . '<button type="button" class="btn btn-primary btn-sm savePhotoBtn savePhotoBtn--inline d-md-none">'
            . '<i class="fas fa-cloud-upload-alt me-1" aria-hidden="true"></i>Save'
            . '</button>'
            . '</div>'
            . '</div>'
            . '</div>';

        return '<tr class="student-photo-row" data-student-name="' . esc($name) . '" data-father-name="' . esc($fatherName) . '" data-first-name="' . esc((string) ($student->first_name ?? '')) . '" data-last-name="' . esc((string) ($student->last_name ?? '')) . '">'
            . '<td class="sno-cell sticky-col" data-label="#"><input type="hidden" name="student_id" value="' . esc((string) $sid) . '"></td>'
            . '<td class="student-name-cell student-name-cell--header sticky-col-2" data-label="Student">' . $nameCell . '</td>'
            . '<td class="td-photo-upload" data-label="Update photo">' . $uploadCell . '</td>'
            . '<td class="action-cell td-save d-none d-md-table-cell" data-label="Save">'
            . '<button type="button" class="btn btn-primary btn-sm savePhotoBtn savePhotoBtn--desktop">'
            . '<i class="fas fa-save me-1 d-none d-lg-inline" aria-hidden="true"></i>Save'
            . '</button></td>'
            . '</tr>';
    }

    /**
     * HTML rows for tbody: active students in current session, filtered by class or parent.
     * Query mirrors StudentsBulkInfo::data() (student_class + class_section + students) so it behaves the same on all DBs.
     */
    public function data()
    {
        try {
            $campusid   = (int) $this->session->get('member_campusid');
            $sessionid  = (int) $this->session->get('member_sessionid');
            $parent_id  = (int) $this->request->getPost('parent_id');
            $cls_sec_id = trim((string) $this->request->getPost('cls_sec_id'));

            if ($sessionid <= 0 || $campusid <= 0) {
                return $this->response->setBody(
                    '<tr class="photos-empty-row"><td colspan="4" class="text-center text-warning">Session or campus not set. Please re-login or pick campus/session.</td></tr>'
                );
            }

            if ($parent_id <= 0 && $cls_sec_id === '') {
                return $this->response->setBody(
                    '<tr class="photos-empty-row"><td colspan="4" class="text-center text-muted">Select a class or search for a student to load the list.</td></tr>'
                );
            }

            $qb = $this->db->table('student_class sc')
                ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
                ->join('students s', 's.student_id = sc.student_id')
                ->join('parents p', 'p.parent_id = s.parent_id', 'left')
                ->where('sc.session_id', $sessionid)
                ->where('s.campus_id', $campusid)
                ->where('s.status', 1)
                ->select('s.student_id, s.first_name, s.last_name, s.profile_photo, p.f_name AS father_name');

            if ($parent_id > 0) {
                $qb->where('s.parent_id', $parent_id);
            } else {
                $qb->where('sc.cls_sec_id', (int) $cls_sec_id);
            }

            $rawRows = $qb->orderBy('s.first_name', 'ASC')
                ->orderBy('s.last_name', 'ASC')
                ->get()->getResult();

            // De-duplicate by student_id (same student can appear in multiple student_class rows).
            $byId = [];
            foreach ($rawRows as $r) {
                $sid = (int) ($r->student_id ?? 0);
                if ($sid > 0) {
                    $byId[$sid] = $r;
                }
            }
            $rows = array_values($byId);

            $html = '';
            foreach ($rows as $student) {
                $html .= $this->renderPhotoRow($student);
            }

            if ($html === '') {
                $html = '<tr class="photos-empty-row"><td colspan="4" class="text-center text-muted">No students found.</td></tr>';
            }

            return $this->response->setBody($html);
        } catch (\Throwable $e) {
            log_message('error', 'StudentsBulkPhotos::data — ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            return $this->response->setBody(
                '<tr class="photos-empty-row"><td colspan="4" class="text-center text-danger">Could not load students. Check the application log (writable/logs) for details.</td></tr>'
            );
        }
    }

    /**
     * Prefer public/uploads; if not writable, use writable/uploads/student_profiles (same URLs via UploadsProxy).
     */
    protected function resolveWritableUploadPath(): ?string
    {
        $candidates = [
            rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads',
            rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'student_profiles',
        ];

        foreach ($candidates as $dest) {
            if (! is_dir($dest)) {
                @mkdir($dest, 0755, true);
            }
            if (! is_dir($dest)) {
                continue;
            }
            @chmod($dest, 0775);
            if ($this->uploadDirectoryAcceptsWrites($dest)) {
                return $dest;
            }
            @chmod($dest, 0777);
            if ($this->uploadDirectoryAcceptsWrites($dest)) {
                return $dest;
            }
        }

        return null;
    }

    /** Remove previous profile file from either public or writable fallback. */
    protected function unlinkProfilePhotoIfExists(string $basename): void
    {
        $basename = basename($basename);
        if ($basename === '' || $basename === '.' || $basename === '..') {
            return;
        }
        $paths = [
            rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $basename,
            rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'student_profiles' . DIRECTORY_SEPARATOR . $basename,
        ];
        foreach ($paths as $p) {
            if (is_file($p)) {
                @unlink($p);
            }
        }
    }

    /**
     * True if we can create a file in this directory (handles some NFS / hosting quirks).
     */
    protected function uploadDirectoryAcceptsWrites(string $dir): bool
    {
        if (! is_dir($dir)) {
            return false;
        }
        if (is_writable($dir)) {
            return true;
        }
        $probe = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . '.perm_test_' . uniqid('', true);
        if (@file_put_contents($probe, '0') !== false) {
            @unlink($probe);

            return true;
        }

        return false;
    }

    /**
     * Update profile_photo for one student (multipart: profile_photo file + student_id).
     */
    public function savePhoto()
    {
        $student_id = (int) $this->request->getPost('student_id');
        if ($student_id <= 0) {
            return $this->response->setJSON(['success' => false, 'msg' => 'student_id is required.']);
        }

        $campusid  = (int) ($this->session->get('member_campusid') ?? 0);
        $sessionid = (int) ($this->session->get('member_sessionid') ?? 0);
        $userId    = (int) ($this->session->get('member_userid') ?? 0);

        if ($campusid <= 0 || $sessionid <= 0) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Campus or session not set in session.']);
        }

        $student = $this->db->table('students')
            ->where('student_id', $student_id)
            ->where('campus_id', $campusid)
            ->where('status', 1)
            ->get()->getRow();

        if (! $student) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Student not found or not in your campus.']);
        }

        $enrolled = $this->db->table('student_class')
            ->where('student_id', $student_id)
            ->where('session_id', $sessionid)
            ->where('status', 1)
            ->countAllResults();

        if ($enrolled < 1) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Student is not enrolled in the current session.']);
        }

        $image = $this->request->getFile('profile_photo');
        if (! $image || ! $image->isValid() || $image->hasMoved()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Please choose a valid image file.']);
        }

        $val = \Config\Services::validation();
        if (! $val->setRules([
            'profile_photo' => 'uploaded[profile_photo]|is_image[profile_photo]|max_size[profile_photo,4096]|ext_in[profile_photo,jpg,jpeg,png,webp]',
        ])->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Validation failed',
                'errors'  => $val->getErrors(),
            ]);
        }

        $dest = $this->resolveWritableUploadPath();

        if ($dest === null) {
            $pub = realpath(rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads') ?: (rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads');
            $wr  = realpath(rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'student_profiles')
                ?: (rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'student_profiles');

            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'No upload folder is writable. Fix permissions on public uploads (e.g. sudo chown -R www-data:www-data ' . $pub . ' && chmod 775 ' . $pub . ') or ensure this app can write: ' . $wr,
            ]);
        }

        $ext     = $image->getClientExtension() ?: $image->guessExtension() ?: 'jpg';
        $newName = uniqid('stu_', true) . '.' . strtolower((string) $ext);

        try {
            $image->move($dest, $newName);
        } catch (\Throwable $e) {
            log_message('error', 'Bulk photo move failed: ' . $e->getMessage() . ' dest=' . $dest);

            $hint = realpath($dest) ?: $dest;

            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Could not save the image file. Check permissions on: ' . $hint,
                'detail'  => ENVIRONMENT === 'development' ? $e->getMessage() : null,
            ]);
        }

        $oldName = isset($student->profile_photo) ? basename((string) $student->profile_photo) : '';
        if ($oldName !== '' && $oldName !== $newName) {
            $this->unlinkProfilePhotoIfExists($oldName);
        }

        $this->db->table('students')->where('student_id', $student_id)->update([
            'profile_photo' => $newName,
            'updated_date'  => date('Y-m-d H:i:s'),
            'user_id'       => $userId,
        ]);

        return $this->response->setJSON([
            'success'             => true,
            'msg'                 => 'Photo updated.',
            'profile_photo'       => $newName,
            'profile_photo_url'   => base_url('uploads/' . $newName),
        ]);
    }
}
