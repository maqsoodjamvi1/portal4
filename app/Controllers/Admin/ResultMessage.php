<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;

class ResultMessage extends BaseController
{
    protected $db;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = \Config\Database::connect();
        check_permission('admin-result-message');
    }

    public function index()
    {
        $campusId = session('member_campusid');
        $sessionId = session('member_sessionid');
        $schoolInfo = getSchoolInfo();

        $currentRole = currentUserRoles();
        $sectionsclassinfo = in_array(5, $currentRole)
            ? teacherSubjectSections()
            : userClassSections();

        $campusInfo = $this->db->table('campus')
            ->where('campus_id', $campusId)
            ->get()
            ->getResult();

        $examInfo = $this->db->table('exam')
            ->where(['campus_id' => $campusId, 'session_id' => $sessionId])
            ->get()
            ->getResult();

        return view('admin/result_message_edit', [
            'sessionData' => [
                'campusid' => $campusId,
                'sessionid' => $sessionId
            ],
            'sectionsclassinfo' => $sectionsclassinfo,
            'campusinfo' => $campusInfo,
            'examinfo' => $examInfo,
        ]);
    }

    public function data()
    {
        $campusId = session('member_campusid');
        $sessionId = session('member_sessionid');

        $sessionInfo = $this->db->table('academic_session')
            ->where('session_id', $sessionId)
            ->get()
            ->getRow();

        $sessionYear = explode('-', $sessionInfo->start_date)[0];
        $feeMonth = $this->request->getPost('month');

        $monthdate = !empty($feeMonth) ? '"0' . $feeMonth . '/' . $sessionYear . '"' : '';

        $defaultMessage = '';
        $campusInfo = $this->db->table('campus')->where('campus_id', $campusId)->get()->getRow();
        if ($campusInfo) {
            $defaultMessage = $campusInfo->student_fee_sms;
        }

        $output = view('admin/result_message_template', [
            'defaultMessage' => $defaultMessage
        ]);

        return $this->response->setBody($output);
    }

    public function save()
    {
        $userId = session('member_userid');
        $campusId = session('member_campusid');
        $sessionId = session('member_sessionid');
        $date = date('Y-m-d H:i:s');

        $template = $this->request->getPost('message');
        $contacts = $this->request->getPost('contacts');
        $clsSecId = $this->request->getPost('cls_sec_id');
        $examId = $this->request->getPost('eid');

        if (empty($contacts)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Select Contact Type']);
        }

        $this->db->transStart();

        $students = $this->db->table('student_class')
            ->select('student_id')
            ->where([ 'status' => 1, 'session_id' => $sessionId, 'cls_sec_id' => $clsSecId ])
            ->get()->getResult();

        foreach ($students as $student) {
            $studentId = $student->student_id;

            $dateSheets = $this->db->query(
                "SELECT * FROM datesheet WHERE eid = $examId AND sec_sub_id IN (
                    SELECT sec_sub_id FROM section_subjects WHERE cls_sec_id = $clsSecId AND status = 1
                )"
            )->getResult();

            $resultList = '';

            foreach ($dateSheets as $sheet) {
                $totalMarks = $sheet->total_marks ?? 0;
                if ($totalMarks > 0) {
                    $result = $this->db->table('subject_results')
                        ->where(['student_id' => $studentId, 'sec_sub_id' => $sheet->sec_sub_id, 'eid' => $examId])
                        ->get()->getRow();

                    $subject = $this->db->table('section_subjects')
                        ->where('sec_sub_id', $sheet->sec_sub_id)
                        ->get()->getRow();

                    $subjectInfo = $this->db->table('allsubject')
                        ->where('sid', $subject->subject_id)
                        ->get()->getRow();

                    $obtained = $result->obtained_marks ?? 0;

                    $resultList .= $subjectInfo->subject_short_name . " $obtained/$totalMarks, ";
                }
            }

            $examResult = $this->db->table('exam_results')
                ->where(['student_id' => $studentId, 'eid' => $examId])
                ->get()->getRow();

            $resultList .= "Total Marks: {$examResult->obtain_total_mark}/{$examResult->exam_total_mark}, ";

            $parent = $this->db->query("SELECT * FROM parents WHERE parent_id IN (SELECT parent_id FROM students WHERE student_id = $studentId)")->getRow();
            $studentInfo = $this->db->table('students')->where('student_id', $studentId)->get()->getRow();

            $sc = $this->db->table('student_class')->where('student_id', $studentId)->get()->getRow();
            $cs = $this->db->table('class_section')->where('cls_sec_id', $sc->cls_sec_id)->get()->getRow();
            $class = $this->db->table('classes')->where('class_id', $cs->class_id)->get()->getRow();
            $section = $this->db->table('sections')->where('section_id', $cs->section_id)->get()->getRow();

            $studentClass = $class->class_name . '(' . $section->section_name . ')';

            foreach ($contacts as $type) {
                $mobile = $parent->{$type} ?? '';
                if (!empty($mobile)) {
                    $dataMessage = [
                        'first_name' => $studentInfo->first_name,
                        'last_name' => $studentInfo->last_name,
                        'father_name' => $parent->f_name,
                        'class' => $studentClass,
                        'result' => rtrim($resultList, ', '),
                        'date' => date('Y-m-d')
                    ];

                    $parsed = $this->parseTemplate($template, $dataMessage);

                    $this->db->table('sms')->insert([
                        'mobile' => $mobile,
                        'message' => trim($parsed),
                        'campus_id' => $campusId,
                        'parent_id' => $parent->parent_id,
                        'status' => 0,
                        'user_id' => $userId,
                        'created_date' => $date
                    ]);
                }
            }
        }

        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Add Message Success']);
    }

    protected function parseTemplate(string $template, array $data): string
    {
        foreach ($data as $key => $val) {
            $template = str_replace('{' . $key . '}', $val, $template);
        }
        return $template;
    }
}
