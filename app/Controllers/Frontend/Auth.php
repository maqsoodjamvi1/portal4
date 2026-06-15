<?php
namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Libraries\PortalAuthService;
use App\Models\Frontend\AuthModel;

class Auth extends BaseController
{
    protected $authModel;
    protected $session;
    protected $db;
    protected PortalAuthService $portalAuth;

    public function __construct()
    {
        helper(['url','form']);
        $this->session    = session();
        $this->authModel  = new AuthModel();
        $this->db         = db_connect();
        $this->portalAuth = new PortalAuthService($this->authModel);
    }

    /**
     * Return the student's current class info by joining class_section.
     * Prefers rows where status=3 (active), then newest sc_id.
     *
     * @return array{cls_sec_id:int,class_id:int,section_id:int,class_name:?string,section_name:?string}
     */
    private function currentClassInfoFor(int $studentId): array
    {
        $out = [
            'cls_sec_id'    => 0,
            'class_id'      => 0,
            'section_id'    => 0,
            'class_name'    => null,
            'section_name'  => null,
        ];

        if ($studentId <= 0) {
            return $out;
        }
        if (! $this->db->tableExists('student_class') || ! $this->db->tableExists('class_section')) {
            return $out;
        }

        $qb = $this->db->table('student_class sc')
            ->select([
                'sc.cls_sec_id',
                'cs.class_id',
                'cs.section_id',
                'c.class_name',
                's.section_name',
            ])
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'inner');

        if ($this->db->tableExists('classes')) {
            $qb->join('classes c', 'c.class_id = cs.class_id', 'left');
        }
        if ($this->db->tableExists('sections')) {
            $qb->join('sections s', 's.section_id = cs.section_id', 'left');
        }

        $qb->where('sc.student_id', $studentId);
        $qb->orderBy('CASE WHEN sc.status = 1 THEN 1 ELSE 0 END', 'DESC', false);
        $qb->orderBy('sc.sc_id', 'DESC');

        $res = $qb->get();
        if ($res === false) {
            return $out;
        }

        $row = $res->getRowArray();
        if (! $row) {
            return $out;
        }

        $out['cls_sec_id']   = (int) ($row['cls_sec_id']   ?? 0);
        $out['class_id']     = (int) ($row['class_id']     ?? 0);
        $out['section_id']   = (int) ($row['section_id']   ?? 0);
        $out['class_name']   = $row['class_name']   ?? null;
        $out['section_name'] = $row['section_name'] ?? null;

        return $out;
    }

    private function currentClassIdFor(int $studentId): int
    {
        return $this->currentClassInfoFor($studentId)['class_id'];
    }

    public function showLogin()
    {
        if ($this->session->get('auth.logged_in')) {
            return redirect()->route('dashboard');
        }

        return view('frontend/auth/login_portal');
    }

    public function doLogin()
    {
        if (! $this->portalAuth->checkLoginRateLimit()) {
            return redirect()->back()->with('error', 'Too many login attempts. Please try again later.');
        }

        $login    = trim((string) $this->request->getPost('login'));
        $password = (string) $this->request->getPost('password');

        if ($login === '' || $password === '') {
            return redirect()->back()->with('error', 'Login and Password are required.');
        }

        if ($parent = $this->authModel->findParentByLogin($login)) {
            if ((int) $parent['status'] === 1) {
                if (! $this->portalAuth->verifyPassword($password, $parent['password'] ?? null)) {
                    return redirect()->back()->with('error', 'Invalid credentials or inactive account.');
                }

                $this->portalAuth->regenerateSession();

                $this->session->set('auth', [
                    'logged_in' => true,
                    'role'      => 'parent',
                    'user_id'   => (int) $parent['parent_id'],
                    'name'      => $parent['name'] ?? 'Parent',
                    'email'     => $parent['email'] ?? null,
                    'f_name'    => $parent['name'] ?? 'Parent',
                    'parent_id' => (int) $parent['parent_id'],
                ]);

                $kids = $this->authModel->getChildren((int) $parent['parent_id']);

                foreach ($kids as &$k) {
                    $info = $this->currentClassInfoFor((int) $k['student_id']);
                    $k['cls_sec_id']   = $info['cls_sec_id'];
                    $k['class_id']     = $info['class_id'];
                    $k['section_id']   = $info['section_id'];
                    $k['class_name']   = $info['class_name'];
                    $k['section_name'] = $info['section_name'];
                }
                unset($k);

                $activeId  = (int) ($kids[0]['student_id'] ?? 0);
                $activeCls = (int) ($kids[0]['class_id']   ?? 0);
                $activeCS  = (int) ($kids[0]['cls_sec_id'] ?? 0);

                $this->session->set([
                    'active_student_id' => $activeId,
                    'siblings'          => $kids,
                    'student_id'        => $activeId,
                    'student_class_id'  => $activeCls,
                    'cls_sec_id'        => $activeCS,
                ]);

                return redirect()->route('dashboard');
            }
        }

        if ($student = $this->authModel->findStudentByLogin($login)) {
            if ((int) $student['status'] === 1) {
                if (! $this->portalAuth->verifyPassword($password, $student['password'] ?? null)) {
                    return redirect()->back()->with('error', 'Invalid credentials or inactive account.');
                }

                $this->portalAuth->regenerateSession();

                $sid = (int) $student['student_id'];
                $cid = $this->currentClassIdFor($sid);

                $this->session->set('auth', [
                    'logged_in' => true,
                    'role'      => 'student',
                    'user_id'   => $sid,
                    'name'      => $student['name'] ?? 'Student',
                    'parent_id' => (int) $student['parent_id'],
                    'reg_no'    => $student['reg_no'] ?? null,
                    'f_name'    => $student['name'] ?? 'Student',
                ]);

                $this->session->set([
                    'active_student_id' => $sid,
                    'student_id'        => $sid,
                    'student_class_id'  => $cid,
                ]);

                return redirect()->route('dashboard');
            }
        }

        return redirect()->back()->with('error', 'Invalid credentials or inactive account.');
    }

    public function logout()
    {
        $this->session->remove(['auth', 'active_student_id', 'student_id', 'student_class_id', 'siblings']);
        $this->session->destroy();

        return redirect()->route('login')->with('success', 'Logged out.');
    }
}
