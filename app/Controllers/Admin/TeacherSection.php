<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Database\BaseConnection;
use Config\Services;

class TeacherSection extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = db_connect();
        $this->session = session();
        helper(['form', 'url']);
    }

    public function index()
    {
        return view('admin/teacher_section_edit');
    }

    public function data()
    {
        $campusid = $this->session->get('member_campusid');

        $infoteachers = $this->db->query("SELECT * FROM users WHERE campus_id={$campusid} AND status=1 AND id IN (SELECT userID FROM user_roles WHERE roleID=5)")->getResultArray();

        $classSections = $this->db->table('class_section')->where(['campus_id' => $campusid, 'status' => 1])->get()->getResult();

        $sectionsclassinfo = [];
        foreach ($classSections as $section) {
            $class = $this->db->table('classes')->where('class_id', $section->class_id)->get()->getRow();
            $sec = $this->db->table('sections')->where('section_id', $section->section_id)->get()->getRow();
            $sectionsclassinfo[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => ($class->class_short_name ?? '') . ' (' . ($sec->section_name ?? '') . ')'
            ];
        }

        $data = "<style>
.tdclass { padding:3px 8px;text-align:center; }
.verticalTableHeader {
    text-align:center;
    white-space:nowrap;
    -webkit-transform: rotate(90deg);
    -moz-transform: rotate(90deg);
    -ms-transform: rotate(90deg);
    -o-transform: rotate(90deg);
    transform: rotate(90deg);
}
.verticalTableHeader p { margin:0 -100%; display:inline-block; }
.verticalTableHeader p:before { content:''; width:0; padding-top:110%; display:inline-block; vertical-align:middle; }
.table-box { overflow: scroll; height: 500px; }
table { width: 100%; }
table th { padding: 7px; background-color: #ddd; }
table tr th { position: sticky; left: 0; }
</style>";

        $data .= '<div class="table-box"><table border="1"><tr><th></th>';

        foreach ($infoteachers as $teacher) {
            $data .= '<th style="height:100px;vertical-align:middle;"><p class="verticalTableHeader">' . $teacher['first_name'] . ' ' . $teacher['last_name'] . '</p></th>';
        }

        $data .= '</tr>';

        foreach ($sectionsclassinfo as $section) {
            $info = $this->db->table('teacher_section')->where(['cls_sec_id' => $section['section_id'], 'status' => 1])->get()->getRow();
            $selectedTeacher = $info->tid ?? 0;
            $ts_id = $info->ts_id ?? 0;

            $data .= '<tr><th class="tdclass"><input type="hidden" name="ts_id[]" value="' . $ts_id . '">' . $section['sectionclassname'] . '<input type="hidden" name="section_id[]" value="' . $section['section_id'] . '" /></th>';

            foreach ($infoteachers as $teacher) {
                $checked = ($selectedTeacher == $teacher['id']) ? 'checked="checked"' : '';
                $data .= '<td class="tdclass"><input style="position:relative;z-index:100;" type="radio" ' . $checked . ' value="tsvalue_' . $section['section_id'] . '_' . $teacher['id'] . '" name="' . $section['section_id'] . '_ts_id"></td>';
            }

            $data .= '</tr>';
        }

        $data .= '</table></div>';

        return $this->response->setBody($data);
    }

    public function add()
    {
        $campusid = $this->session->get('member_campusid');

        $infoteachers = $this->db->query("SELECT * FROM users WHERE campus_id={$campusid} AND id IN (SELECT userID FROM user_roles WHERE roleID=5)")->getResultArray();
        $classSections = $this->db->table('class_section')->where(['campus_id' => $campusid, 'status' => 1])->get()->getResult();

        $sectionsclassinfo = [];
        foreach ($classSections as $section) {
            $class = $this->db->table('classes')->where('class_id', $section->class_id)->get()->getRow();
            $sec = $this->db->table('sections')->where('section_id', $section->section_id)->get()->getRow();
            $sectionsclassinfo[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => ($class->class_name ?? '') . ' (' . ($sec->section_name ?? '') . ')'
            ];
        }

        return view('admin/teacher_section_edit', [
            'infoteachers' => $infoteachers,
            'sectionsclassinfo' => $sectionsclassinfo,
            'subjectinfo' => $this->db->table('allsubject')->get()->getResult()
        ]);
    }

    public function edit($id)
    {
        $campusid = $this->session->get('member_campusid');

        $info = $this->db->table('teacher_section')->where('ts_id', $id)->get()->getRow();
        $infoteachers = $this->db->table('users')->where(['campus_id' => $campusid])->get()->getResult();

        return view('admin/teacher_section_edit', [
            'info' => $info,
            'infoteachers' => $infoteachers,
            'subjectinfo' => $this->db->table('allsubject')->get()->getResult()
        ]);
    }

    public function save()
    {
        $campus_id = $this->session->get('member_campusid');
        $user_id = $this->session->get('member_userid');
        $section_ids = $this->request->getPost('section_id');
        $ids = $this->request->getPost('ts_id');
        $date = date('Y-m-d');

        $this->db->transStart();

        $this->db->query("UPDATE teacher_section SET status = 0 WHERE tid IN (SELECT id FROM users WHERE campus_id={$campus_id})");

        foreach ($section_ids as $i => $sectionid) {
            $id = $ids[$i];
            $tsvalue = $this->request->getPost("{$sectionid}_ts_id");

            if (!empty($tsvalue)) {
                [$prefix, $sec_id, $teacher_id] = explode('_', $tsvalue);

                $this->db->table('teacher_section')->insert([
                    'tid' => $teacher_id,
                    'cls_sec_id' => $sec_id,
                    'status' => 1,
                    'created_date' => $date,
                    'user_id' => $user_id
                ]);
            }
        }

        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Add Teacher Section Success']);
    }

    public function selectteachersection()
    {
        $section_id = $this->request->getPost('section_id');
        $campusid = $this->session->get('member_campusid');

        $teachers = $this->db->table('users')->where('campus_id', $campusid)->get()->getResult();
        $current = $this->db->table('teacher_section')->where('cls_sec_id', $section_id)->get()->getRow();

        $output = '';
        foreach ($teachers as $teacher) {
            $checked = ($current && $teacher->id == $current->tid) ? 'checked="checked"' : '';
            $output .= '<label class="form-control font-weight-bold"><input type="radio" name="tid" value="' . $teacher->id . '" ' . $checked . '> ' . $teacher->first_name . ' ' . $teacher->last_name . '</label>';
        }

        return $this->response->setBody($output);
    }

    public function delete($id)
    {
        $this->db->table('teacher_section')->where('ts_id', $id)->delete();
        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Teacher Section Success']);
    }
}
