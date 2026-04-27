<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Classwise_results extends BaseController
{
    protected $db;
    protected $session;
    protected $template_data = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'text']);
        check_permission('admin-classwise-result-report');
    }

    public function index()
    {
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();

        $allsubject = $this->db->table('allsubject')
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getResult();

        $this->template_data['allsubject'] = $allsubject;

        $exams = $this->db->table('exam')
            ->where('session_id', $sessionid)
            ->where('campus_id', $campusid)
            ->get()->getResult();

        $this->template_data['exams'] = $exams;

        $classsectioninfo = $this->db->table('class_section')
            ->where('campus_id', $campusid)
            ->where('status', 1)
            ->get()->getResult();

        $sectionsclassinfo = [];
        foreach ($classsectioninfo as $section) {
            $classinfo = $this->db->table('classes')
                ->where('class_id', $section->class_id)
                ->get()->getRow();

            $sectioninfo = $this->db->table('sections')
                ->where('section_id', $section->section_id)
                ->get()->getRow();

            $sectionsclassinfo[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => $classinfo->class_name . " (" . $sectioninfo->section_name . ")"
            ];
        }
        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

        return view('admin/classwise_results', $this->template_data);
    }

    public function grade($marks)
    {
        $schoolinfo = getSchoolInfo();
        return $this->db->table('grading_policy')
            ->where('system_id', $schoolinfo->system_id)
            ->where("{$marks} BETWEEN mark_from AND mark_to")
            ->get()->getRow();
    }

    public function data()
    {
        $request = $this->request;
        $cls_sec_id = $request->getPost('cls_sec_id');
        $subject_id = $request->getPost('subject_id');
        $schoolinfo = getSchoolInfo();

        $examids = $request->getPost('examids');
        if (empty($examids)) {
            echo "<div class='bg-danger pl-3 ml-3'>Select Exam For Result</div>";
            exit;
        }

        $campus_id = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');

        $non_academics = $request->getPost('non_academics') ?? ['study_complaints', 'discinpline_complaints', 'absentees'];
        $academic_result = $request->getPost('academic_result') ?? ['marks', 'percentage', 'grade'];

        // Student classes in this section/session
        $student_class = $this->db->table('student_class')
            ->where("student_id IN(SELECT student_id FROM students WHERE STATUS=1 AND campus_id={$campus_id})")
            ->where('session_id', $sessionid)
            ->where('cls_sec_id', $cls_sec_id)
            ->orderBy('cls_sec_id', 'asc')
            ->get()->getResult();

        // Exams
        $eids = is_array($examids) ? implode(', ', $examids) : $examids;
        $exams = $this->db->table('exam')
            ->whereIn('eid', explode(',', $eids))
            ->get()->getResultArray();

        $headerRep = reportHeader();

        $strResultCard = '';
        $strResultCard .= $headerRep;
        $strResultCard .= '<div class="reportHeading">Classwise Result Report</div><table class="resultReport table table-bordered" style="margin-bottom: 2px;">
        <thead><tr><th style="text-align: center;padding: 0 8px;line-height: 35px;width:15%;z-index: 1000;"><div style="width:100%;">Name</div></th><th style="text-align: center;padding: 0 8px;line-height: 35px;width: 15%;z-index: 1000;"><div style="width:100%;">Subject</div></th>';

        foreach ($exams as $term) {
            $strResultCard .= '<th style="text-align: center;padding: 0 8px;line-height: 35px;width:17%;">' . $term['exam_name'] . '<br>';
            if (in_array('marks', $academic_result)) {
                $strResultCard .= '<div style="width:33%;float:left;">Marks</div>';
            }
            if (in_array('percentage', $academic_result)) {
                $strResultCard .= '<div style="width:33%;float:left;border-left:1px solid #000;">Per</div>';
            }
            if (in_array('grade', $academic_result)) {
                $strResultCard .= '<div style="width:33%;float:left;border-left:1px solid #000;">Grade</div>';
            }
            $strResultCard .= '</th>';
        }

        $strResultCard .= '<th style="text-align:center;">Total<br>';
        if (in_array('marks', $academic_result)) {
            $strResultCard .= '<div style="width:33%;float:left;">Marks</div>';
        }
        if (in_array('percentage', $academic_result)) {
            $strResultCard .= '<div style="width:33%;float:left;border-left:1px solid #000;">Per</div>';
        }
        if (in_array('grade', $academic_result)) {
            $strResultCard .= '<div style="width:33%;float:left;border-left:1px solid #000;">Grade</div>';
        }
        $strResultCard .= '</th>';

        foreach ($student_class as $studentinfo) {
            if ($subject_id) {
                $subjectSubject = $this->db->table('section_subjects')
                    ->where('subject_id', $subject_id)
                    ->where('cls_sec_id', $studentinfo->cls_sec_id)
                    ->where('status', 1)
                    ->get()->getRow();

                $class_subjects = $this->db->table('datesheet')
                    ->where('cls_sec_id', $studentinfo->cls_sec_id)
                    ->where('total_marks >', 0)
                    ->whereIn('eid', explode(',', $eids))
                    ->where('sec_sub_id', $subjectSubject ? $subjectSubject->sec_sub_id : 0)
                    ->get()->getResult();
            } else {
                $class_subjects = $this->db->table('datesheet')
                    ->where('cls_sec_id', $studentinfo->cls_sec_id)
                    ->where('total_marks >', 0)
                    ->whereIn('eid', explode(',', $eids))
                    ->get()->getResult();
            }

            $subjectSubCount = $this->db->table('section_subjects')
                ->where('cls_sec_id', $studentinfo->cls_sec_id)
                ->where('status', 1)
                ->get()->getResultArray();

            $student_info = $this->db->table('students')
                ->where('student_id', $studentinfo->student_id)
                ->get()->getRow();

            $campus_info = $this->db->table('campus')
                ->where('campus_id', $campus_id)
                ->get()->getRow();

            if (!$student_info) {
                continue;
            }

            $parent_info = $this->db->table('parents')
                ->where('parent_id', $student_info->parent_id)
                ->get()->getRow();

            $f_name = $parent_info->f_name ?? '';
            $father_contact = $parent_info->father_contact ?? '';
            $mother_contact = $parent_info->mother_contact ?? '';
            $emergency_contact = $parent_info->emergency_contact ?? '';

            $totalSectionSubject = count($subjectSubCount) + 2;
            if (in_array('position', $academic_result)) {
                $totalSectionSubject += 1;
            }

            $strResultCard .= '</tr></thead><tbody><tr><th rowspan="' . $totalSectionSubject . '">' . $student_info->first_name . ' ' . $student_info->last_name . '</th>';

            foreach ($class_subjects as $subjects) {
                $sectionsubjects = $this->db->table('section_subjects')
                    ->where('sec_sub_id', $subjects->sec_sub_id)
                    ->get()->getRow();

                $academicsubjects = $this->db->table('allsubject')
                    ->where('sid', $sectionsubjects->subject_id ?? 0)
                    ->get()->getRow();

                $strResultCard .= '<tr><th style="padding: 4px 8px;">' . ($academicsubjects->subject_name ?? '') . '</th>';

                $where = [
                    'student_id' => $studentinfo->student_id,
                    'session_id' => $sessionid,
                    'sec_sub_id' => $subjects->sec_sub_id
                ];

                $stdresults = $this->db->table('subject_results')
                    ->where($where)
                    ->whereIn('eid', explode(',', $eids))
                    ->get()->getResult();

                foreach ($stdresults as $numbers) {
                    $datesheetinfo = $this->db->table('datesheet')
                        ->where('eid', $numbers->eid)
                        ->where('sec_sub_id', $numbers->sec_sub_id)
                        ->get()->getRow();

                    $subjectPercentage = ($datesheetinfo && $datesheetinfo->total_marks != 0)
                        ? round(($numbers->obtained_marks / $datesheetinfo->total_marks) * 100)
                        : 0;

                    $subjectgrade = $this->grade($subjectPercentage);

                    $strResultCard .= '<td style="padding: 0px 8px;line-height: 30px;font-size: 12px;text-align: center;">';

                    if (in_array('marks', $academic_result)) {
                        if ($datesheetinfo && $datesheetinfo->total_marks > 0) {
                            $strResultCard .= "<div style='width: 33%;float: left;'>" . $numbers->obtained_marks . '/' . $datesheetinfo->total_marks . " </div>";
                        } else {
                            $strResultCard .= "<div style='width: 33%;float: left;'>-</div>";
                        }
                    }
                    if (in_array('percentage', $academic_result)) {
                        if ($datesheetinfo && $datesheetinfo->total_marks > 0) {
                            $strResultCard .= '<div style="border-left:1px solid #000;width: 33%;float: left;">' . $subjectPercentage . '% </div>';
                        } else {
                            $strResultCard .= "<div style='border-left:1px solid #000;width: 33%;float: left;'>-</div>";
                        }
                    }
                    if (in_array('grade', $academic_result)) {
                        if ($subjectgrade) {
                            $gradeinfo = $this->db->table('grades')->where('gid', $subjectgrade->gid)->get()->getRow();
                            if ($datesheetinfo && $datesheetinfo->total_marks > 0) {
                                $strResultCard .= '<div style="border-left:1px solid #000;width: 33%;float: left;">' . $gradeinfo->name . '</div>';
                            } else {
                                $strResultCard .= "<div style='border-left:1px solid #000;width: 33%;float: left;'>-</div>";
                            }
                        }
                    }
                    $strResultCard .= '</td>';
                }
                $strResultCard .= '</tr>';
            }
        }

        $strResultCard .= '</tbody></table>';

        echo $strResultCard;
        exit;
    }

    // Ordinal number suffix
    public function addOrdinalNumberSuffix($num)
    {
        if (!in_array(($num % 100), [11, 12, 13])) {
            switch ($num % 10) {
                case 1:
                    return $num . 'st';
                case 2:
                    return $num . 'nd';
                case 3:
                    return $num . 'rd';
            }
        }
        return $num . 'th';
    }
}
