<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class StudentsBulkMakeCurrent extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db      = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        check_permission('admin-students');
    }

    public function index()
    {
        $campusId = (int) $this->session->get('member_campusid');

        // Class/section list for the modal dropdown
        $classSections = $this->db->table('class_section cs')
            ->select('cs.cls_sec_id, cs.class_id, CONCAT(c.class_name," (",s.section_name,")") AS label')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->where('cs.campus_id', $campusId)
            ->where('cs.status', 1)
            ->orderBy('c.class_name','ASC')
            ->orderBy('s.section_name','ASC')
            ->get()->getResultArray();

        return view('admin/students_bulk_make_current', [
            'classSections' => $classSections,
        ]);
    }

    /**
     * Select2: search inactive students by name within campus (and session if provided),
     * no cls_sec filter, return minimal payload.
     */
    public function searchByName()
    {
        $q     = trim((string) $this->request->getGet('q'));
        $limit = max(1, min((int) ($this->request->getGet('limit') ?: 20), 50));
        if ($q === '') return $this->response->setJSON(['results' => []]);

        $campusId  = (int) ($this->session->get('member_campusid') ?: 0);
        $sessionId = (int) (
            $this->request->getGet('session_id')
            ?: $this->session->get('member_sessionid')
            ?: $this->session->get('session_id')
            ?: 0
        );

        // Only students whose status != 1
        $b = $this->db->table('students s')->where('s.status !=', 1);
        if ($campusId > 0) $b->where('s.campus_id', $campusId);

        // If session is known, (optionally) join student_class to ensure they belong to that session
        if ($sessionId > 0) {
            $b->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = '.$sessionId, 'left');
        }

        $b->groupStart()
              ->like('s.first_name', $q)
              ->orLike('s.last_name',  $q)
          ->groupEnd();

        $rows = $b->select('s.student_id, s.reg_no, s.first_name, s.last_name, s.status')
                  ->orderBy('s.first_name','ASC')->orderBy('s.last_name','ASC')
                  ->limit($limit)->get()->getResult();

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id'    => (int) $r->student_id,
                'text'  => trim(($r->first_name ?? '').' '.($r->last_name ?? '')),
                'badge' => 'Reg: '.($r->reg_no ?? ''),
            ];
        }
        return $this->response->setJSON(['results' => $out]);
    }

    /**
     * Table body HTML: list of inactive students (status != 1)
     * Optional filter: if "student_id" passed, show just that one (from search).
     */
    public function data()
    {
        $campusId  = (int) $this->session->get('member_campusid');
        $studentId = (int) $this->request->getPost('student_id');

        $b = $this->db->table('students s')
            ->select('s.student_id, s.reg_no, s.first_name, s.last_name, s.status, s.cls_sec_id')
            ->where('s.campus_id', $campusId)
            ->where('s.status !=', 1);

        if ($studentId > 0) $b->where('s.student_id', $studentId);

        $rows = $b->orderBy('s.first_name','ASC')->orderBy('s.last_name','ASC')->get()->getResult();

        if (!$rows) {
            return $this->response->setBody('<tr><td colspan="5" class="text-center text-info">No inactive students found.</td></tr>');
        }

        $i=1; $html='';
        foreach ($rows as $r) {
            $name = trim(($r->first_name ?? '').' '.($r->last_name ?? ''));
            $html .= '<tr>';
            $html .= '<td class="sticky-col" style="width:70px;">'.($i++).'</td>';
            $html .= '<td class="sticky-col-2">'.esc($name ?: 'Student').'</td>';
            $html .= '<td>'.esc($r->reg_no ?? '').'</td>';
            $html .= '<td><span class="badge text-bg-secondary">'.esc($r->status ?? '').'</span></td>';
            $html .= '<td class="text-end">
                        <button type="button" class="btn btn-sm btn-success makeCurrentBtn"
                                data-student-id="'.(int)$r->student_id.'"
                                data-student-name="'.esc($name, 'attr').'">
                          Make current
                        </button>
                      </td>';
            $html .= '</tr>';
        }

        return $this->response->setBody($html);
    }

    /**
     * Make Current:
     * - require: student_id, cls_sec_id, fee
     * - upsert student_class for current session
     * - set students.status = 1, students.class_id/cls_sec_id, and store fee (using discounted_amount)
     */
    public function makeCurrent()
    {
        $studentId = (int) $this->request->getPost('student_id');
        $clsSecId  = (int) $this->request->getPost('cls_sec_id');
        $fee       = trim((string) $this->request->getPost('fee'));

        if ($studentId <= 0 || $clsSecId <= 0 || $fee === '') {
            return $this->response->setJSON(['success'=>false,'msg'=>'Student, class/section and fee are required.']);
        }

        $campusId  = (int) ($this->session->get('member_campusid') ?: 0);
        $sessionId = (int) ($this->session->get('member_sessionid') ?: $this->session->get('session_id') ?: 0);
        $userId    = (int) ($this->session->get('member_userid') ?: 0);
        if ($campusId <= 0 || $sessionId <= 0) {
            return $this->response->setJSON(['success'=>false,'msg'=>'Campus or Session not set in session.']);
        }

        // Resolve class_id from cls_sec_id
        $cs = $this->db->table('class_section')->select('class_id')->where('cls_sec_id', $clsSecId)->get()->getRow();
        if (!$cs) return $this->response->setJSON(['success'=>false,'msg'=>'Invalid class/section.']);
        $classId = (int)$cs->class_id;

        $student = $this->db->table('students')->where('student_id',$studentId)->where('campus_id',$campusId)->get()->getRow();
        if (!$student) return $this->response->setJSON(['success'=>false,'msg'=>'Student not found.']);

        $this->db->transBegin();

        // Upsert student_class for current session
        $scRow = $this->db->table('student_class')
            ->where('student_id',$studentId)
            ->where('session_id',$sessionId)
            ->get(1)->getRow();

        if ($scRow) {
            $ok = $this->db->table('student_class')
                ->where('student_id',$studentId)
                ->where('session_id',$sessionId)
                ->update([
                    'cls_sec_id'   => $clsSecId,
                    'status'       => 1,
                    'updated_date' => date('Y-m-d H:i:s'),
                    'user_id'      => $userId
                ]);
        } else {
            $ok = $this->db->table('student_class')->insert([
                'student_id'   => $studentId,
                'session_id'   => $sessionId,
                'cls_sec_id'   => $clsSecId,
                'status'       => 1,
                'created_date' => date('Y-m-d H:i:s'),
                'updated_date' => date('Y-m-d H:i:s'),
                'user_id'      => $userId
            ]);
        }
        if (!$ok) {
            $err = $this->db->error();
            $this->db->transRollback();
            return $this->response->setJSON(['success'=>false,'msg'=>'Failed to save class mapping: '.($err['message'] ?? '')]);
        }

        // Update student to active + set class/section + store fee on student (discounted_amount here)
        $ok = $this->db->table('students')->where('student_id',$studentId)->update([
            'status'        => 1,
            'class_id'      => $classId,
            'cls_sec_id'    => $clsSecId,
            'discounted_amount' => $fee, // ← adjust if you keep fee elsewhere
            'updated_date'  => date('Y-m-d H:i:s'),
            'user_id'       => $userId
        ]);
        if (!$ok) {
            $err = $this->db->error();
            $this->db->transRollback();
            return $this->response->setJSON(['success'=>false,'msg'=>'Failed to update student: '.($err['message'] ?? '')]);
        }

        $this->db->transCommit();
        return $this->response->setJSON(['success'=>true, 'msg'=>'Student made current successfully.']);
    }
}
