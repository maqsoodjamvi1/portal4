<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class Messages extends BaseController
{
    protected $db;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = \Config\Database::connect();
        check_permission('admin-messages');
    }

    public function index()
    {
        return view('admin/messagebox/mailbox');
    }

    public function data()
    {
        $response = new stdClass();
        $response->draw = $this->request->getPost('draw');
        $campusId = session('member_campusid');
        $keyword = $this->request->getPost('search')['value'] ?? '';

        // Total count
        $builder = $this->db->table('sms A')->selectCount('A.id', 'ccount');
        $builder->where('A.campus_id', $campusId);
        if ($keyword) {
            $builder->where('A.mobile', $keyword);
        }
        $q = $builder->get()->getRow();
        $response->recordsTotal = $q->ccount ?? 0;

        // Filtered data
        $builder = $this->db->table('sms A')->select('A.*');
        $builder->where('A.campus_id', $campusId);
        if ($keyword) {
            $builder->where('A.mobile', $keyword);
        }
        $builder->orderBy('A.id', 'desc');
        $builder->limit($this->request->getPost('length'), $this->request->getPost('start'));
        $results = $builder->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];

        foreach ($results as $row) {
            $parent = $this->db->table('parents')->where('parent_id', $row->parent_id)->get()->getRow();
            $response->data[] = [
                'id'       => $row->id,
                'sendtime' => $row->sendtime,
                'mobile'   => $row->mobile,
                'f_name'   => $parent->f_name ?? '',
                'message'  => $row->message,
                'status'   => $row->status
            ];
        }

        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-messages');
        $campusSections = getAllClassSection();
        return view('admin/messagebox/compose', ['campusSections' => $campusSections]);
    }

    public function save()
    {
        check_permission('admin-add-messages');

        $template = $this->request->getPost('message');
        $contacts = $this->request->getPost('contacts');
        $sections = $this->request->getPost('sections');
        $uniqueSms = $this->request->getPost('unique_sms') ? 1 : 0;

        $campusId  = session('member_campusid');
        $sessionId = session('member_sessionid');
        $userId    = session('member_userid');
        $date      = date('Y-m-d H:i:s');

        if (empty($contacts)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Select Contact Type']);
        }

        $students = [];

        if ($sections[0] == 'all') {
            if ($uniqueSms == 0) {
                $students = $this->db->query(
                    "SELECT * FROM student_class WHERE student_id IN (
                        SELECT student_id FROM students WHERE campus_id = $campusId
                    ) AND status = 1 AND session_id = $sessionId"
                )->getResult();
            } else {
                $students = $this->db->query(
                    "SELECT * FROM parents WHERE parent_id IN (
                        SELECT parent_id FROM students WHERE status = 1 AND campus_id = $campusId
                    )"
                )->getResult();
            }
        } else {
            $sectionList = implode(',', array_map('intval', $sections));
            if ($uniqueSms == 0) {
                $students = $this->db->query(
                    "SELECT * FROM student_class WHERE cls_sec_id IN ($sectionList) AND status = 1 AND session_id = $sessionId"
                )->getResult();
            } else {
                $students = $this->db->query(
                    "SELECT * FROM parents WHERE parent_id IN (
                        SELECT parent_id FROM students WHERE student_id IN (
                            SELECT student_id FROM student_class WHERE cls_sec_id IN ($sectionList) AND status = 1 AND session_id = $sessionId
                        )
                    )"
                )->getResult();
            }
        }

        if ($uniqueSms == 0) {
            foreach ($students as $student) {
                $this->sendSmsToStudent($student, $contacts, $template, $userId, $date, $campusId);
            }
        } else {
            foreach ($students as $parent) {
                $this->sendSmsToParent($parent, $contacts, $template, $userId, $date, $campusId);
            }
        }

        return $this->response->setJSON(['success' => true, 'msg' => 'Add Messages Success']);
    }

    protected function sendSmsToStudent($student, $contacts, $template, $userId, $date, $campusId)
    {
        $parents = $this->db->table('parents')
            ->whereIn('parent_id', function($builder) use ($student) {
                $builder->select('parent_id')
                        ->from('students')
                        ->where('student_id', $student->student_id);
            })->get()->getRow();

        $studentInfo = $this->db->table('students')->where('student_id', $student->student_id)->get()->getRow();
        $classSec = $this->db->table('class_section')->where('cls_sec_id', $student->cls_sec_id)->get()->getRow();
        $classInfo = $this->db->table('classes')->where('class_id', $classSec->class_id)->get()->getRow();
        $sectionInfo = $this->db->table('sections')->where('section_id', $classSec->section_id)->get()->getRow();

        $studentClass = $classInfo->class_name . '(' . $sectionInfo->section_name . ')';

        if ($parents) {
            foreach ($contacts as $type) {
                $mobile = $parents->$type ?? '';
                if (!empty($mobile)) {
                    $message = $this->parseTemplate($template, [
                        'first_name'  => $studentInfo->first_name,
                        'last_name'   => $studentInfo->last_name,
                        'father_name' => $parents->f_name,
                        'class'       => $studentClass
                    ]);
                    $this->db->table('sms')->insert([
                        'mobile'       => $mobile,
                        'message'      => trim($message),
                        'campus_id'    => $campusId,
                        'parent_id'    => $parents->parent_id,
                        'status'       => 0,
                        'user_id'      => $userId,
                        'created_date' => $date
                    ]);
                }
            }
        }
    }

    protected function sendSmsToParent($parent, $contacts, $template, $userId, $date, $campusId)
    {
        $student = $this->db->query(
            "SELECT * FROM student_class WHERE student_id IN (
                SELECT student_id FROM students WHERE parent_id = $parent->parent_id
            ) LIMIT 1"
        )->getRow();

        $studentInfo = $this->db->table('students')->where('student_id', $student->student_id)->get()->getRow();
        $classSec = $this->db->table('class_section')->where('cls_sec_id', $student->cls_sec_id)->get()->getRow();
        $classInfo = $this->db->table('classes')->where('class_id', $classSec->class_id)->get()->getRow();
        $sectionInfo = $this->db->table('sections')->where('section_id', $classSec->section_id)->get()->getRow();

        $studentClass = $classInfo->class_name . '(' . $sectionInfo->section_name . ')';

        foreach ($contacts as $type) {
            $mobile = $parent->$type ?? '';
            if (!empty($mobile)) {
                $message = $this->parseTemplate($template, [
                    'first_name'  => $studentInfo->first_name,
                    'last_name'   => $studentInfo->last_name,
                    'father_name' => $parent->f_name,
                    'class'       => $studentClass
                ]);
                $this->db->table('sms')->insert([
                    'mobile'       => trim($mobile),
                    'message'      => trim($message),
                    'campus_id'    => $campusId,
                    'parent_id'    => $parent->parent_id,
                    'status'       => 0,
                    'user_id'      => $userId,
                    'created_date' => $date
                ]);
            }
        }
    }

    protected function parseTemplate($template, $data)
    {
        foreach ($data as $key => $val) {
            $template = str_replace('{' . $key . '}', $val, $template);
        }
        return $template;
    }

    public function delete()
    {
        check_permission('admin-del-class');
        $id = intval($this->request->getGet('id'));

        $this->db->transStart();
        $this->db->table('classes')->where('class_id', $id)->delete();
        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Classes Success']);
    }
}
