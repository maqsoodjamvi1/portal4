<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\StudentsModel;

class StudentDataVerificationForm extends BaseController
{
    protected $students;
    protected $db;

    public function __construct()
    {
        helper(['form', 'url', 'server']);
        $this->db = \Config\Database::connect();
        $this->students = new StudentsModel();
    }

    public function index()
    {
        $sectionsclassinfo = in_array(5, currentUserRoles())
            ? teacherSubjectSections()
            : userClassSections();

        return view('admin/student_data_verification_form', [
            'sectionsclassinfo' => $sectionsclassinfo,
        ]);
    }

    public function data()
    {
        $campus_id  = (int) session()->get('member_campusid');
        $schoolinfo = getSchoolInfo();
        $parents    = $this->students->getActiveParentsWithStudents($campus_id);
        $schoolName = esc($schoolinfo->system_name ?? 'School');

        if ($parents === []) {
            return $this->response->setBody(
                '<div class="alert alert-info mb-0">No active students found for this campus.</div>'
            );
        }

        $html = '<div class=""><page class="col-lg-12"><div class="">';
        $i = 1;

        foreach ($parents as $parent) {
            $students = $this->students->getStudentsByParent($parent->parent_id, $campus_id);
            if ($students === []) {
                continue;
            }

            $html .= '<div class="col-lg-4 mb-5" style="width:33%;float:left;">';
            $html .= "<h1 style='text-align:center;font-size:18px;font-weight:bold;'>{$schoolName}</h1>";
            $html .= "<h1 style='text-align:center;font-size:14px;font-weight:bold;'>Data Verification Form</h1>";
            $html .= "<table class='table table-bordered mb-0'>";
            $html .= '<tr><td style="width:30%">Father Name:</td><td>' . esc($parent->f_name ?? '') . '</td></tr>';
            $html .= '<tr><td>Father CNIC:</td><td>' . esc($parent->father_cnicnew ?? '') . '</td></tr>';
            $html .= '<tr><td>Mother Name:</td><td>' . esc($parent->m_name ?? '') . '</td></tr>';
            $html .= '<tr><td>Address:</td><td>' . esc($parent->address_line1 ?? '') . '</td></tr>';
            $html .= '<tr><td>Father Contact:</td><td>' . esc($parent->father_contact ?? '') . '</td></tr>';
            $html .= '<tr><td>Mother Contact:</td><td>' . esc($parent->mother_contact ?? '') . '</td></tr>';
            $html .= '<tr><td>Whatsapp:</td><td>' . esc($parent->whatsapp ?? '') . '</td></tr>';
            $html .= '<tr><td>Emergency Contact:</td><td>' . esc($parent->emergency_contact ?? '') . '</td></tr>';

            foreach ($students as $student) {
                $html .= '<tr><td><strong>Student Name:</strong></td><td><strong>'
                    . esc(trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')))
                    . '</strong></td></tr>';
                $html .= '<tr><td>Date Of Birth:</td><td>' . esc($student->date_of_birth ?? '') . '</td></tr>';
                $html .= '<tr><td>Student CNIC:</td><td>' . esc($student->std_cnic ?? '') . '</td></tr>';
            }

            $html .= '</table></div>';

            if ($i === 3) {
                $html .= '<div style="clear:both;page-break-after: always;"></div>';
                $i = 0;
            }
            $i++;
        }

        $html .= '</div></div>';
        return $this->response->setBody($html);
    }

    public function student_fee_verification()
    {
        $sectionsclassinfo = in_array(5, currentUserRoles())
            ? teacherSubjectSections()
            : userClassSections();

        return view('admin/student_fee_verification_form', [
            'sectionsclassinfo' => $sectionsclassinfo,
        ]);
    }

    public function data2()
    {
        $campus_id  = (int) session()->get('member_campusid');
        $session_id = (int) session()->get('member_sessionid');
        $schoolinfo = getSchoolInfo();
        $system_id  = (int) ($schoolinfo->system_id ?? 0);
        $parents    = $this->students->getActiveParentsWithStudents($campus_id);

        if ($parents === []) {
            return $this->response->setBody(
                '<div class="alert alert-info mb-0">No active students found for this campus.</div>'
            );
        }

        $html = '<div class=""><page class="col-lg-12"><div class="">';
        $i = 1;

        foreach ($parents as $parent) {
            $students = $this->students->getStudentsByParent($parent->parent_id, $campus_id);
            if ($students === []) {
                continue;
            }

            $html .= '<div class="col-lg-4 mb-5" style="width:33%;float:left;">';
            $html .= "<table class='table table-bordered mb-0'><tr><td style='width:30%'>&nbsp;&nbsp;</td><td style='width:30%'></td><td>Correction</td></tr>";
            $html .= '<tr><td>Father Name:</td><td>' . esc($parent->f_name ?? '') . '</td><td></td></tr>';
            $html .= '<tr><td>Father CNIC:</td><td>' . esc($parent->father_cnicnew ?? '') . '</td><td></td></tr>';
            $html .= '<tr><td>Mother Name:</td><td>' . esc($parent->m_name ?? '') . '</td><td></td></tr>';
            $html .= '<tr><td>Address:</td><td>' . esc($parent->address_line1 ?? '') . '</td><td></td></tr>';
            $html .= '<tr><td>Father Contact:</td><td>' . esc($parent->father_contact ?? '') . '</td><td></td></tr>';
            $html .= '<tr><td>Mother Contact:</td><td>' . esc($parent->mother_contact ?? '') . '</td><td></td></tr>';
            $html .= '<tr><td>Whatsapp:</td><td>' . esc($parent->whatsapp ?? '') . '</td><td></td></tr>';
            $html .= '<tr><td>Emergency Contact:</td><td>' . esc($parent->emergency_contact ?? '') . '</td><td></td></tr>';

            $totalProjected = 0;

            foreach ($students as $student) {
                $studentClass = $this->db->table('student_class')
                    ->where('student_id', (int) $student->student_id)
                    ->where('session_id', $session_id)
                    ->where('status', 1)
                    ->get()
                    ->getRow();

                $className   = '';
                $sectionName = '';
                $classId     = 0;

                if ($studentClass) {
                    $classSection = $this->db->table('class_section')
                        ->where('cls_sec_id', (int) $studentClass->cls_sec_id)
                        ->get()
                        ->getRow();

                    if ($classSection) {
                        $classId = (int) $classSection->class_id;
                        $class   = $this->db->table('classes')
                            ->where('class_id', $classId)
                            ->get()
                            ->getRow();
                        $section = $this->db->table('sections')
                            ->where('section_id', (int) $classSection->section_id)
                            ->get()
                            ->getRow();
                        $className   = $class->class_name ?? '';
                        $sectionName = $section->section_name ?? '';
                    }
                }

                $feeRow = null;
                if ($classId > 0) {
                    $feeRow = $this->db->query(
                        'SELECT amount FROM fee_amount
                         WHERE class_id = ?
                           AND fee_type_id IN (
                               SELECT fee_type_id FROM fee_type
                               WHERE is_monthly_fee = 1 AND s_flag = 1 AND system_id = ?
                           )
                           AND session_id = ?
                           AND campus_id = ?
                         LIMIT 1',
                        [$classId, $system_id, $session_id, $campus_id]
                    )->getRow();
                }

                $projected = (float) ($feeRow->amount ?? 0) - (float) ($student->discounted_amount ?? 0);
                $totalProjected += $projected;

                $html .= '<tr><td>Student Name:</td><td>'
                    . esc(trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')))
                    . '</td><td></td></tr>';
                $html .= '<tr><td>Student Class:</td><td>'
                    . esc(trim($className . ($sectionName !== '' ? ' (' . $sectionName . ')' : '')))
                    . '</td><td></td></tr>';
                $html .= '<tr><td>Projected Fee:</td><td>' . esc((string) $projected) . '</td><td></td></tr>';
            }

            $html .= '<tr><td>Total:</td><td>' . esc((string) $totalProjected) . '</td><td></td></tr>';
            $html .= '</table></div>';

            if ($i === 3) {
                $html .= '<div style="clear:both;page-break-after: always;"></div>';
                $i = 0;
            }
            $i++;
        }

        $html .= '</div></div>';
        return $this->response->setBody($html);
    }
}
