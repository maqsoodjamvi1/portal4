<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\StudentsModel;
use CodeIgniter\Database\BaseBuilder;

class StudentDataVerificationForm extends BaseController
{
    protected $students;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->students = new StudentsModel();
    }

    public function index()
    {
        $sectionsclassinfo = in_array(5, currentUserRoles())
            ? teacherSubjectSections()
            : userClassSections();

        return view('admin/student_data_verification_form', [
            'sectionsclassinfo' => $sectionsclassinfo
        ]);
    }

    public function data()
    {
        $campus_id = session()->get('member_campusid');
        $schoolinfo = getSchoolInfo();
        $parents = $this->students->getActiveParentsWithStudents($campus_id);

        $html = '<div class=""><page class="col-lg-12"><div class="">';
        $i = 1;

        foreach ($parents as $parent) {
            $students = $this->students->getStudentsByParent($parent->parent_id, $campus_id);

            $html .= '<div class="col-lg-4 mb-5" style="width:33%;float:left;">';
            $html .= "<h1 style='text-align:center;font-size:18px;font-weight:bold;'>{$schoolinfo->system_name}</h1>";
            $html .= "<h1 style='text-align:center;font-size:14px;font-weight:bold;'>Data Verification Form</h1>";
            $html .= "<table class='table table-bordered mb-0'>";
            $html .= "<tr><td style='width:30%'>Father Name:</td><td>{$parent->f_name}</td></tr>";
            $html .= "<tr><td>Father CNIC:</td><td>{$parent->father_cnicnew}</td></tr>";
            $html .= "<tr><td>Mother Name:</td><td>{$parent->m_name}</td></tr>";
            $html .= "<tr><td>Address:</td><td>{$parent->address_line1}</td></tr>";
            $html .= "<tr><td>Father Contact:</td><td>{$parent->father_contact}</td></tr>";
            $html .= "<tr><td>Mother Contact:</td><td>{$parent->mother_contact}</td></tr>";
            $html .= "<tr><td>Whatsapp:</td><td>{$parent->whatsapp}</td></tr>";
            $html .= "<tr><td>Emergency Contact:</td><td>{$parent->emergency_contact}</td></tr>";

            foreach ($students as $student) {
                $html .= "<tr><td><strong>Student Name:</strong></td><td><strong>{$student->first_name} {$student->last_name}</strong></td></tr>";
                $html .= "<tr><td>Date Of Birth:</td><td>{$student->date_of_birth}</td></tr>";
                $html .= "<tr><td>Student CNIC:</td><td>{$student->std_cnic}</td></tr>";
            }

            $html .= '</table></div>';

            if ($i == 3) {
                $html .= '<div style="clear:both;page-break-after: always;"></div>';
                $i = 0;
            }
            $i++;
        }

        $html .= '</div>';
        return $this->response->setBody($html);
    }

    public function student_fee_verification()
    {
        $sectionsclassinfo = in_array(5, currentUserRoles())
            ? teacherSubjectSections()
            : userClassSections();

        return view('admin/student_fee_verification_form', [
            'sectionsclassinfo' => $sectionsclassinfo
        ]);
    }

    public function data2()
    {
        $campus_id = session()->get('member_campusid');
        $session_id = session()->get('member_sessionid');

        $parents = $this->students->getActiveParentsWithStudents($campus_id);

        $html = '<div class=""><page class="col-lg-12"><div class="">';
        $i = 1;

        foreach ($parents as $parent) {
            $students = $this->students->getStudentsByParent($parent->parent_id, $campus_id);

            $html .= '<div class="col-lg-4 mb-5" style="width:33%;float:left;">';
            $html .= "<table class='table table-bordered mb-0'><tr><td style='width:30%'>&nbsp;&nbsp;</td><td style='width:30%'></td><td>Correction</td></tr>";
            $html .= "<tr><td>Father Name:</td><td>{$parent->f_name}</td><td></td></tr>";
            $html .= "<tr><td>Father CNIC:</td><td>{$parent->father_cnicnew}</td><td></td></tr>";
            $html .= "<tr><td>Mother Name:</td><td>{$parent->m_name}</td><td></td></tr>";
            $html .= "<tr><td>Address:</td><td>{$parent->address_line1}</td><td></td></tr>";
            $html .= "<tr><td>Father Contact:</td><td>{$parent->father_contact}</td><td></td></tr>";
            $html .= "<tr><td>Mother Contact:</td><td>{$parent->mother_contact}</td><td></td></tr>";
            $html .= "<tr><td>Whatsapp:</td><td>{$parent->whatsapp}</td><td></td></tr>";
            $html .= "<tr><td>Emergency Contact:</td><td>{$parent->emergency_contact}</td><td></td></tr>";

            $totalProjected = 0;

            foreach ($students as $student) {
                $studentclassinfo = db()->table('student_class')
                    ->where('student_id', $student->student_id)
                    ->where('session_id', $session_id)
                    ->get()->getRow();

                $classsectioninfo = db()->table('class_section')
                    ->where('cls_sec_id', $studentclassinfo->cls_sec_id ?? 0)
                    ->get()->getRow();

                $className = $sectionName = '';
                if ($classsectioninfo) {
                    $class = db()->table('classes')->where('class_id', $classsectioninfo->class_id)->get()->getRow();
                    $section = db()->table('sections')->where('section_id', $classsectioninfo->section_id)->get()->getRow();
                    $className = $class->class_name ?? '';
                    $sectionName = $section->section_name ?? '';
                }

                $getclassfee = db()->query("SELECT * FROM fee_amount WHERE class_id = ? AND fee_type_id IN (SELECT fee_type_id FROM fee_type WHERE is_monthly_fee = 1 AND s_flag = 1) AND session_id = ? AND campus_id = ?", [
                    $classsectioninfo->class_id ?? 0,
                    $session_id,
                    $campus_id
                ])->getRow();

                $projected = ($getclassfee->amount ?? 0) - $student->discounted_amount;
                $totalProjected += $projected;

                $html .= "<tr><td>Student Name:</td><td>{$student->first_name} {$student->last_name}</td><td></td></tr>";
                $html .= "<tr><td>Student Class:</td><td>{$className} ({$sectionName})</td><td></td></tr>";
                $html .= "<tr><td>Projected Fee:</td><td>{$projected}</td><td></td></tr>";
            }

            $html .= "<tr><td>Total:</td><td>{$totalProjected}</td><td></td></tr>";
            $html .= '</table></div>';

            if ($i == 3) {
                $html .= '<div style="clear:both;page-break-after: always;"></div>';
                $i = 0;
            }
            $i++;
        }

        $html .= '</div>';
        return $this->response->setBody($html);
    }
}
