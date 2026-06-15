<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use Config\Database;

class PortalProfile extends BaseController
{
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->session = session();
        $this->db      = Database::connect();
        helper(['url', 'form', 'server', 'parent_portal']);
    }

    public function index()
    {
        $auth = $this->session->get('auth');
        if (! $auth || empty($auth['logged_in'])) {
            return redirect()->route('login');
        }

        $role     = $auth['role'] ?? '';
        $parentId = (int) ($auth['user_id'] ?? 0);

        $studentId = 0;
        if ($role === 'parent') {
            $studentId = (int) ($this->session->get('active_student_id') ?? 0);
            if ($studentId <= 0) {
                $kids = \parent_portal_get_children($parentId);
                if (! empty($kids)) {
                    $studentId = (int) $kids[0]['student_id'];
                    $this->session->set('active_student_id', $studentId);
                }
            }
            if ($studentId <= 0) {
                return redirect()->route('dashboard')
                    ->with('error', 'Please select a child from the dashboard first.');
            }
        } elseif ($role === 'student') {
            $studentId = (int) ($this->session->get('student_id') ?? 0);
            if ($studentId <= 0) {
                return redirect()->route('login')
                    ->with('error', 'Student information not found. Please log in again.');
            }
        } else {
            return redirect()->route('login');
        }

        $children = ($role === 'parent') ? \parent_portal_get_children($parentId) : [];

        $student = $this->db->table('students s')
            ->select(
                's.*, c.class_name, sec.section_name, cs.cls_sec_id, campus.campus_name, '
                . 'p.f_name AS father_name, p.m_name AS mother_name, p.father_contact, p.mother_contact, '
                . 'p.address_line1, p.city'
            )
            ->join('student_class sc', 'sc.student_id = s.student_id AND sc.status = 1', 'left')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
            ->join('campus', 'campus.campus_id = s.campus_id', 'left')
            ->join('parents p', 'p.parent_id = s.parent_id', 'left')
            ->where('s.student_id', $studentId)
            ->get()
            ->getRowArray();

        if (! $student) {
            return redirect()->route('dashboard')->with('error', 'Student record not found.');
        }

        if ($role === 'parent') {
            $ok = $this->db->table('students')
                ->where('student_id', $studentId)
                ->where('parent_id', $parentId)
                ->countAllResults() > 0;
            if (! $ok) {
                return redirect()->route('dashboard')->with('error', 'You do not have access to this student.');
            }
        }

        $photoUrl = '';
        if (\function_exists('getStudentPhotoUrl')) {
            $photoUrl = (string) getStudentPhotoUrl($student['profile_photo'] ?? '');
        }
        $photoFile = ltrim((string) ($student['profile_photo'] ?? ''), '/');
        if ($photoFile !== '') {
            foreach (['uploads/' . $photoFile, 'student_photos/' . $photoFile] as $path) {
                if (is_file(FCPATH . $path)) {
                    $photoUrl = base_url($path);
                    break;
                }
            }
        }

        return view('frontend/profile/index', [
            'title'             => 'Profile',
            'role'              => $role,
            'children'          => $children,
            'active_student_id' => $studentId,
            'student'           => $student,
            'photo_url'         => $photoUrl,
            'return_path'       => 'student/profile',
        ]);
    }
}
