<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class StudentNotices extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
    }

    public function index()
    {
        check_permission('admin-student-notices');
        return view('admin/student_notices');
    }

    public function data()
    {
        check_permission('admin-student-notices');
        $campusid = $this->session->get('member_campusid');
        $keyword = $this->request->getPost('search')['value'] ?? '';

        $builder = $this->db->table('student_notices A');
        $builder->selectCount('A.student_notice_id', 'ccount');
        $total = $builder->get()->getRow();

        $builder = $this->db->table('student_notices A');
        $builder->select('A.*');
        $builder->orderBy('A.student_notice_id', 'DESC');
        $builder->limit($this->request->getPost('length'), $this->request->getPost('start'));
        $results = $builder->get()->getResult();

        $data = [];
        foreach ($results as $row) {
            $notice = $this->db->table('notices')->where('notice_id', $row->notice_id)->get()->getRow();
            $student = $this->db->table('students')->where([
                'student_id' => $row->std_id,
                'campus_id' => $campusid
            ])->get()->getRow();

            if ($notice && $student) {
                $data[] = [
                    'id' => $row->student_notice_id,
                    'notice_name' => $notice->notice_name,
                    'notice_date' => $notice->notice_date,
                    'student_name' => $student->first_name . ' ' . $student->last_name
                ];
            }
        }

        return $this->response->setJSON([
            'draw' => $this->request->getPost('draw'),
            'recordsTotal' => $total->ccount,
            'recordsFiltered' => $total->ccount,
            'data' => $data
        ]);
    }

    public function add()
    {
        check_permission('admin-add-student-notices');
        $sectionsclassinfo = in_array(5, currentUserRoles()) ? teacherSubjectSections() : userClassSections();
        return view('admin/student_notices_edit', ['sectionsclassinfo' => $sectionsclassinfo]);
    }

    public function edit($id)
    {
        check_permission('admin-edit-student-notices');
        $info = $this->db->table('student_notices')->where('student_notice_id', $id)->get()->getRow();
        return view('admin/student_notices_edit', ['info' => $info]);
    }

    public function save()
    {
        $id = intval($this->request->getPost('id'));
        $sessionid = $this->session->get('member_sessionid');

        if ($id === 0) {
            check_permission('admin-add-student-notices');
            $this->db->transBegin();

            foreach ($this->request->getPost('section_id') as $section_id) {
                $students = $this->db->table('student_class')
                    ->where(['cls_sec_id' => $section_id, 'status' => 1])
                    ->get()->getResult();
                foreach ($students as $student) {
                    $this->db->table('student_notices')->insert([
                        'notice_id' => trim($this->request->getPost('notice_id')),
                        'std_id' => $student->student_id,
                        'session_id' => $sessionid
                    ]);
                }
            }

            $this->db->transComplete();
            return $this->response->setJSON(['success' => true, 'msg' => 'Add Notice Success']);
        } else {
            check_permission('admin-edit-student-notices');
            $this->db->transBegin();

            $file = $this->request->getFile('notice_audio');
            $notice_audio = '';
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $notice_audio = $file->getRandomName();
                $file->move(WRITEPATH . 'uploads', $notice_audio);
            }

            $updateData = [
                'notice_name' => trim($this->request->getPost('notice_name')),
                'notice_date' => trim($this->request->getPost('notice_date')),
                'notice_detail' => trim($this->request->getPost('notice_detail')),
                'status' => $this->request->getPost('status') ?? 0
            ];
            if ($notice_audio) {
                $updateData['notice_audio'] = $notice_audio;
            }

            $this->db->table('student_notices')
                ->where('student_notice_id', $id)
                ->update($updateData);

            $this->db->transComplete();
            return $this->response->setJSON(['success' => true, 'msg' => 'Edit Notice Success']);
        }
    }

    public function get_noticeinfo()
    {
        $campusid = $this->session->get('member_campusid');
        $term = $this->request->getPost('term')['term'] ?? '';
        $notices = $this->db->query(
            "SELECT * FROM notices WHERE (notice_name LIKE ? OR notice_date LIKE ?) AND status=1 AND campus_id=?",
            ["%$term%", "%$term%", $campusid]
        )->getResultArray();

        $data = array_map(function($n) {
            return [
                'id' => $n['notice_id'],
                'text' => $n['notice_name'] . ' ' . $n['notice_date']
            ];
        }, $notices);

        return $this->response->setJSON($data);
    }

    public function delete($id)
    {
        check_permission('admin-del-class');
        $this->db->table('student_notices')->where('student_notice_id', $id)->delete();
        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Notice Success']);
    }
}
