<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;

class DatesheetWithoutSyllabus extends BaseController
{
    protected $db;
    protected $session;
    protected $template_data = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        check_permission('admin-datesheet');
    }

    public function index()
    {
        $campus_id = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();
        $currentrole = currentUserRoles();

        if (in_array(5, $currentrole)) {
            $sectionsclassinfo = teacherSubjectSections();
        } else {
            $sectionsclassinfo = userClassSections();
        }

        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;
        $data = $this->data();
        $this->template_data['data'] = $data;

        return view('admin/datesheet_without_syllabus', $this->template_data);
    }

    // You only need one index() for CI4, so skip the duplicate.

    public function data()
    {
        $cls_sec_id = $this->request->getGet('cls_sec_id');
        $schoolinfo = getSchoolInfo();
        $campus_id = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $termsessionid = $this->session->get('member_termsessionid');
        $termid = $this->session->get('member_termid');
        $data = [];

        $sessionData = [
            'campusid' => $campus_id,
            'sessionid' => $sessionid
        ];
        $this->template_data['sessionData'] = $sessionData;

        if (empty($cls_sec_id)) {
            return [];
        }

        if ($cls_sec_id) {
            $student_class = $this->db->query(
                "SELECT t1.cls_sec_id, t2.student_id, t2.campus_id, t2.reg_no, t2.first_name, t2.last_name, t2.parent_id
                 FROM student_class t1, students t2
                 WHERE t1.student_id = t2.student_id
                   AND t1.session_id = {$sessionid}
                   AND t1.cls_sec_id = {$cls_sec_id}
                   AND t2.campus_id = {$campus_id}
                 ORDER BY t1.cls_sec_id ASC"
            )->getResult();
        } else {
            $student_class = $this->db->query(
                "SELECT t1.cls_sec_id, t2.student_id, t2.campus_id, t2.reg_no, t2.first_name, t2.last_name, t2.parent_id
                 FROM student_class t1, students t2
                 WHERE t1.student_id = t2.student_id
                   AND t1.status = 1
                   AND t1.session_id = {$sessionid}
                   AND t2.campus_id = {$campus_id}
                 ORDER BY t1.cls_sec_id ASC"
            )->getResult();
        }

        foreach ($student_class as $studentinfo) {
            $class_subjects = $this->db->table('section_subjects')
                ->where('cls_sec_id', $studentinfo->cls_sec_id)
                ->where('status', 1)
                ->get()->getResult();

            $student_info = $this->db->table('students')->where('student_id', $studentinfo->student_id)->get()->getRow();
            $campus_info = $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow();

            if ($student_info) {
                $parent_info = $this->db->table('parents')->where('parent_id', $student_info->parent_id)->get()->getRow();
                $classSectioninfo = getClassSection($studentinfo->cls_sec_id);

                $subjectdatesheet = [];
                $examinfo = $this->db->table('exam')
                    ->where('status', 0)
                    ->where('session_id', $sessionid)
                    ->where('campus_id', $campus_id)
                    ->get()->getRow();

                $exam_name = '';
                if ($examinfo) {
                    $exam_name = $examinfo->exam_name;
                    $datesheetinfo = $this->db->table('datesheet')
                        ->where('eid', $examinfo->eid)
                        ->where('cls_sec_id', $studentinfo->cls_sec_id)
                        ->orderBy("exam_date", "ASC")
                        ->get()->getResult();

                    foreach ($datesheetinfo as $datesheet) {
                        $Secsubjects = $this->db->table('section_subjects')
                            ->where('sec_sub_id', $datesheet->sec_sub_id)
                            ->where('status', 1)
                            ->get()->getRow();

                        if ($Secsubjects) {
                            $academicsubjects = $this->db->table('allsubject')
                                ->where('sid', $Secsubjects->subject_id)
                                ->get()->getRow();

                            if ($academicsubjects) {
                                $exam_date = DateTime::createFromFormat('Y-m-d', $datesheet->exam_date);
                                $exam_date = $exam_date->format('j-M-Y');
                                $subjectname = $academicsubjects->subject_name;
                                $dayOfWeek = date("l", strtotime($datesheet->exam_date));
                                $subjectdatesheet[] = [
                                    'exam_date' => $exam_date,
                                    'dayOfWeek' => $dayOfWeek,
                                    'subjectname' => $subjectname,
                                    'total_marks' => $datesheet->total_marks
                                ];
                            }
                        }
                    }
                }

                $f_name = '';
                $father_contact = '';
                $mother_contact = '';
                if ($parent_info) {
                    $f_name = $parent_info->f_name;
                    $father_contact = $parent_info->father_contact;
                    $mother_contact = $parent_info->mother_contact;
                }

                $data[] = [
                    'class' => $classSectioninfo['sectionclassname'] ?? '',
                    'campus_name' => $schoolinfo->system_name ?? '',
                    'campus_location' => $campus_info->location ?? '',
                    'name' => $student_info->first_name . " " . $student_info->last_name,
                    'profile_photo' => $student_info->profile_photo ?? '',
                    'f_name' => $f_name,
                    'father_contact' => $father_contact,
                    'mother_contact' => $mother_contact,
                    'reg_no' => $student_info->reg_no,
                    'terms' => $exam_name,
                    'datesheetbysubject' => $subjectdatesheet,
                ];
            }
        }
        return $data;
    }

    public function add()
    {
        check_permission('admin-add-datesheet');
        $schoolinfo = getSchoolInfo();
        $sessionid = $this->session->get('member_sessionid');
        $campusid = $this->session->get('member_campusid');

        $terminfo = $this->db->table('terms')->where('system_id', $schoolinfo->system_id)->get()->getResult();
        $this->template_data['terminfo'] = $terminfo;

        $examinfo = $this->db->table('exam')->where('campus_id', $campusid)->where('session_id', $sessionid)->get()->getResult();
        $this->template_data['examinfo'] = $examinfo;

        $sectioninfo = userClassSections();
        $this->template_data['sectioninfo'] = $sectioninfo;

        $academic_session = $this->db->table('academic_session')->where('session_id', $sessionid)->where('system_id', $schoolinfo->system_id)->get()->getResult();
        $this->template_data['academic_session'] = $academic_session;

        return view('admin/datesheet_edit', $this->template_data);
    }

    public function edit()
    {
        check_permission('admin-edit-datesheet');
        $id = intval($this->request->getGet('id'));
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $sessionData = [
            'campusid' => $campusid,
            'sessionid' => $sessionid
        ];
        $this->template_data['sessionData'] = $sessionData;

        $info = $this->db->table('allsubject')->where('id', $id)->get()->getRow();
        $this->template_data['info'] = $info;

        $classesinfo = $this->db->table('classes')->get()->getResult();
        $this->template_data['classesinfo'] = $classesinfo;

        $academic_session = $this->db->table('academic_session')->get()->getResult();
        $this->template_data['academic_session'] = $academic_session;

        return view('admin/datesheet_edit', $this->template_data);
    }

    public function save(): ResponseInterface
    {
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');
        $id = intval($this->request->getPost('eeid'));
        $dids = $this->request->getPost('did');
        $sec_sub_ids = $this->request->getPost('sec_sub_id');
        $total_marks = $this->request->getPost('total_marks');
        $exam_date = $this->request->getPost('exam_date');
        $syllabus = $this->request->getPost('syllabus');
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();

        $this->db->transStart();
        for ($i = 0; $i < count($sec_sub_ids); $i++) {
            $sec_sub_id = $sec_sub_ids[$i];
            $examdate = $exam_date[$i];
            $did = $dids[$i];

            if ($examdate) {
                $subjectexamdate = DateTime::createFromFormat('d/m/Y', $examdate);
                $subjectexamdate = $subjectexamdate->format('Y-m-d');
            } else {
                return $this->response->setJSON(['error' => true, 'msg' => 'Select Exam Date']);
            }
            $totalmarks = $total_marks[$i];
            $sub_syllabus = $syllabus[$i];

            if ($did == 0) {
                check_permission('admin-add-datesheet');
                $data = [
                    'eid' => intval($this->request->getPost('eid')),
                    'cls_sec_id' => intval($this->request->getPost('section_id')),
                    'sec_sub_id' => $sec_sub_id,
                    'exam_date' => $subjectexamdate,
                    'total_marks' => $totalmarks,
                    'syllabus' => $sub_syllabus,
                    'created_date' => $date,
                    'user_id' => $user_id
                ];
                $this->db->table('datesheet')->insert($data);
            } else {
                check_permission('admin-edit-datesheet');
                $data = [
                    'eid' => trim($this->request->getPost('eeid')),
                    'cls_sec_id' => intval($this->request->getPost('section_id')),
                    'sec_sub_id' => $sec_sub_id,
                    'exam_date' => $subjectexamdate,
                    'total_marks' => $totalmarks,
                    'syllabus' => $sub_syllabus,
                    'updated_date' => $date,
                    'user_id' => $user_id
                ];
                $this->db->table('datesheet')->where('did', $did)->update($data);
            }
        }
        $this->db->transComplete();
        return $this->response->setJSON(['success' => true, 'msg' => 'Add Datesheet Success']);
    }

    public function selectSubjects(): ResponseInterface
    {
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $section_id = $this->request->getPost('section_id');

        $subject_info = $this->db->table('section_subjects')
            ->where('cls_sec_id', $section_id)
            ->where('status', 1)
            ->get()->getResult();

        $eid = $this->request->getPost('eid');
        if (empty($eid)) {
            return $this->response->setJSON(['error' => true, 'html' => "<div class='text-danger'>Exam is not selected</div><br>"]);
        }
        $examinfo = $this->db->table('exam')->where('eid', $eid)->get()->getRow();
        if ($examinfo) {
            $examStartDate = DateTime::createFromFormat('Y-m-d', $examinfo->exam_start_date);
            $subjectexamdate = $examStartDate->format('d/m/Y');
        } else {
            $subjectexamdate = '';
        }
        $terms_session_info = $this->db->table('terms_session')
            ->where('term_id', $examinfo->term_id)
            ->where('session_id', $sessionid)
            ->get()->getRow();

        $eeid = 0;
        $examDatesheet = $this->db->table('datesheet')
            ->where('cls_sec_id', $section_id)
            ->where('eid', $examinfo->eid)
            ->get()->getRow();
        if ($examDatesheet) {
            $eeid = $examDatesheet->eid;
        }

        $subjectList = '';
        $subjectList .= '<input type="hidden" name="eeid"  value="' . $eeid . '">';
        $subjectList .= '<table class="table"><tr><th style="width:5%;">Subject</th><th  style="width:10%;">Total Marks</th><th style="width:17%;">Exam Date</th><th  style="width:50%;">Syllabus</th></tr>';
        $i = 1;
        foreach ($subject_info as $subject) {
            $class_section_info = $this->db->table('class_section')
                ->where('cls_sec_id', $subject->cls_sec_id)
                ->where('status', 1)
                ->get()->getRow();

            $datesheet_info = $this->db->table('datesheet')
                ->where('sec_sub_id', $subject->sec_sub_id)
                ->where('eid', $examinfo->eid)
                ->get()->getRow();
            $papersyllabus = '';
            $totalmarks = '';
            $did = 0;
            if ($datesheet_info) {
                $did = $datesheet_info->did;
                $papersyllabus = $datesheet_info->syllabus;
                $totalmarks = $datesheet_info->total_marks;
                $subjectexamdate = DateTime::createFromFormat('Y-m-d', $datesheet_info->exam_date);
                $subjectexamdate = $subjectexamdate->format('d/m/Y');
            } else {
                $toplevelinfo = $this->db->table('top_level_planning')
                    ->where('subject_id', $subject->subject_id)
                    ->where('term_session_id', $terms_session_info->term_session_id ?? 0)
                    ->where('class_id', $class_section_info->class_id ?? 0)
                    ->where('campus_id', $campusid)
                    ->get()->getRow();
                if ($toplevelinfo) {
                    $papersyllabus = $toplevelinfo->objective;
                }
            }

            $subjectinfo = $this->db->table('allsubject')
                ->where('sid', $subject->subject_id)
                ->get()->getRowArray();
            if (!empty($subjectinfo)) {
                $subject_name = $subjectinfo['subject_name'];
                $subject_id = $subjectinfo['sid'];
                $subjectList .= "<tr><td><input type='hidden' name='did[]'  value='" . $did . "'><input type='hidden' name='sec_sub_id[]'  value='" . $subject->sec_sub_id . "'>" . $subject_name . "</td><td><input type='text' name='total_marks[]' value='" . $totalmarks . "' class='form-control'></td><td>
                    <div class='input-group date' id='datepicker" . $subject->sec_sub_id . "' data-target-input='nearest'>
                            <input type='text' name='exam_date[]'  value='" . $subjectexamdate . "'  class='form-control datetimepicker-input' data-target='#datepicker" . $subject->sec_sub_id . "'/>
                            <div class='input-group-append' data-target='#datepicker" . $subject->sec_sub_id . "' data-toggle='datetimepicker'>
                                <div class='input-group-text'><i class='fa fa-calendar'></i></div>
                            </div>
                      </td><td><textarea name='syllabus[]' class='form-control editor222'>" . $papersyllabus . "</textarea></td></tr>
                <script>
                    $(function(){
                     $('#datepicker" . $subject->sec_sub_id . "').datetimepicker({
                          format: 'DD/MM/YYYY',
                        });
                    });
                    $('.editor222').summernote();
                    </script>";
            }
        }
        $subjectList .= "</table><script>
        $(document).ready(function() {
            // first row checkboxes
            $('tr td:first-child input[type=\"checkbox\"]').click( function() {
               $(this).closest('tr').find(\":input:not(:first)\").attr('disabled', !this.checked);
            });
        });
        </script>";

        return $this->response->setJSON(['html' => $subjectList]);
    }
}
