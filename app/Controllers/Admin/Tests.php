<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use stdClass;

class Tests extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = \Config\Database::connect();
        $this->session = session();
    }

    public function index()
    {
        check_permission('admin-tests');
        return view('admin/tests', []);
    }

    public function data()
    {
        $response = new stdClass;
        $response->draw = $this->request->getPost('draw');
        $campusid = $this->session->get('member_campusid');
        $keyword = $this->request->getPost('search')['value'] ?? '';
        $user_id = $this->session->get('member_userid');

        // Count records
        $builder = $this->db->table('tests A');
        $builder->select('count(A.test_id) as ccount', false)
            ->where('A.campus_id', $campusid);
        if ($keyword) {
            $builder->where('A.text', $keyword);
        }
        $q = $builder->get()->getRow();
        $response->recordsTotal = $q->ccount;

        // Get paginated records
        $builder2 = $this->db->table('tests A');
        $builder2->select('A.*')
            ->where('A.campus_id', $campusid);
        if ($keyword) {
            $builder2->where('A.text', $keyword);
        }
        $builder2->orderBy('A.test_id', 'desc');
        $builder2->limit($this->request->getPost('length'), $this->request->getPost('start'));
        $results = $builder2->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];

        foreach ($results as $row) {
            $classSectioninfo = getClassSection($row->cls_sec_id);

            $allSubjectinfo = $this->db->table('allsubject')
                ->where('sid', $row->subject_id)->get()->getRow();

            $testSeriesinfo = $this->db->table('test_series')
                ->where('t_series_id', $row->t_series_id)->get()->getRow();

            if ($testSeriesinfo) {
                $data = [];
                $data['id'] = $row->test_id;
                $data['class'] = $classSectioninfo['sectionclassname'] ?? '';
                $data['subject'] = $allSubjectinfo->subject_name ?? '';
                $data['test_series'] = $testSeriesinfo->series_name ?? '';
                $data['test_date'] = $row->test_date;
                $data['total_marks'] = $row->total_marks;
                $data['syllabus'] = $row->syllabus;
                $response->data[] = $data;
            }
        }
        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-tests');
        $sessionid = $this->session->get('member_sessionid');
        $campusid = $this->session->get('member_campusid');
        $currentrole = currentUserRoles();

        if (in_array(5, $currentrole)) {
            $sectionsclassinfo = teacherSubjectSections();
        } else {
            $sectionsclassinfo = userClassSections();
        }

        $data = [];
        $data['session_id'] = $sessionid;
        $data['sectionsclassinfo'] = $sectionsclassinfo;
        $data['test_series'] = $this->db->table('test_series')
            ->where('session_id', $sessionid)
            ->where('campus_id', $campusid)
            ->get()->getResult();

        return view('admin/tests_edit', $data);
    }

    public function save()
    {
        check_permission('admin-add-tests');
        $user_id = $this->session->get('member_userid');
        $campusid = $this->session->get('member_campusid');
        $date = date('Y-m-d H:i:s');
        $testcount = $this->request->getPost('testcount');
        $session_id = $this->request->getPost('session_id');

        for ($i = 0; $i < count($testcount); $i++) {
            $id = $this->request->getPost('id' . $i);
            $test_date = $this->request->getPost('test_date' . $i);
            $total_marks = $this->request->getPost('total_marks' . $i);
            $syllabus = $this->request->getPost('syllabus' . $i);

            $this->db->transStart();

            if ($id == 0 || $id == '') {
                $data = [
                    'session_id' => trim($session_id),
                    't_series_id' => trim($this->request->getPost('t_series_id')),
                    'cls_sec_id' => trim($this->request->getPost('cls_sec_id')),
                    'subject_id' => trim($this->request->getPost('subject_id')),
                    'campus_id' => $campusid,
                    'test_date' => $test_date,
                    'total_marks' => $total_marks,
                    'syllabus' => $syllabus,
                    'created_date' => $date,
                    'user_id' => $user_id
                ];
                $this->db->table('tests')->insert($data);
            } else {
                $data = [
                    't_series_id' => trim($this->request->getPost('t_series_id')),
                    'cls_sec_id' => trim($this->request->getPost('cls_sec_id')),
                    'subject_id' => trim($this->request->getPost('subject_id')),
                    'test_date' => $test_date,
                    'total_marks' => $total_marks,
                    'syllabus' => $syllabus,
                    'updated_date' => $date,
                    'user_id' => $user_id
                ];
                $this->db->table('tests')->where('test_id', $id)->update($data);
            }

            $this->db->transComplete();
        }
        return $this->response->setJSON(['success' => true, 'msg' => 'Add Tests Success']);
    }

    public function selectTestsList()
    {
        $t_series_id = intval($this->request->getPost('t_series_id'));
        $cls_sec_id = intval($this->request->getPost('cls_sec_id'));
        $subject_id = intval($this->request->getPost('subject_id'));
        $campusid = $this->session->get('member_campusid');

        $testsInfo = $this->db->table('tests')
            ->where('t_series_id', $t_series_id)
            ->where('cls_sec_id', $cls_sec_id)
            ->where('subject_id', $subject_id)
            ->where('campus_id', $campusid)
            ->get()->getResult();

        $testsList = '';
        $testsList .= '<div class="">  
                <table class="table table-bordered" id="dynamic_field">';
        $testsList .= '<tr><th>Test Date</th><th>Total Marks</th><th> Syllabus</th></tr>';
        $i = 0;
        if (!empty($testsInfo)) {
            foreach ($testsInfo as $key => $value) {
                $testsList .= '<tr><td><input type="hidden" name="testcount[]" value="1" />';
                $testsList .= '<input type="hidden" name="id' . $i . '" value="' . $value->test_id . '"><input type="date" id="test_date' . $i . '" name="test_date' . $i . '"  value="' . $value->test_date . '" placeholder="Test Date" class="form-control name_list" required="" /></td>';
                $testsList .= '<td><input type="text" name="total_marks' . $i . '"  value="' . $value->total_marks . '" placeholder="Total Marks" class="form-control name_list total_marks' . $i . '" required="" /></td>';
                $testsList .= '<td><textarea class="form-control name_list" name="syllabus' . $i . '">' . $value->syllabus . '</textarea></td></tr>';
                $i++;
            }
        } else {
            $i = 1;
            $testsList .= '<tr><td><input type="hidden" name="testcount[]" value="1" />';
            $testsList .= '<input type="hidden" name="id0" value=""><input type="date" id="test_date0" name="test_date0"  value="" placeholder="Test Date" class="form-control name_list" required="" /></td>';
            $testsList .= '<td><input type="text" name="total_marks0"  value="0" placeholder="Total Marks" class="form-control name_list total_marks0" required="" /></td>';
            $testsList .= '<td><textarea class="form-control name_list" name="syllabus0"></textarea></td></tr>';
        }

        $testsList .= '<tr><td></td><td></td> <td><button type="button" name="add" id="add" class="btn btn-success">Add More</button></td></tr></table>';
        $testsList .= "<script type='text/javascript'>
	    $(document).ready(function(){      
	      var i = " . $i . "; 
	      $('#add').click(function(){  
	           $('#dynamic_field').append(\"<tr id='row\" + i + \"' class='dynamic-added'><td><input type='hidden' name='id\" + i + \"' value='0'><input type='hidden' name='testcount[]' value='1' /><input type='date' id='test_date\"+ i +\"' name='test_date\" + i + \"' placeholder='Test Date' class='form-control name_list' required /></td><td><input type='text' name='total_marks\" + i + \"' placeholder='Total Marks' class='form-control name_list total_marks\"+ i +\"' required /></td><td><input type='text' name='syllabus\" + i + \"' placeholder='Syllabus' class='form-control name_list'  /></td><td><button type='button' name='remove' id='\" + i + \"' class='btn btn-danger btn_remove btn-sm'>X</button></td></tr>\"); 
			  i++;   
	      });
	      $(document).on('click', '.btn_remove', function(){  
	           var button_id = $(this).attr(\"id\");  
	           $('#row'+button_id).remove();  
	      });  
	    });  
	    </script>";
        echo $testsList;
    }

    public function selectSubjectbySection()
    {
        $section_id = $this->request->getPost('cls_sec_id');
        $userid = $this->session->get('member_userid');
        $currentrole = currentUserRoles();
        $schoolinfo = getSchoolInfo();

        if (in_array(5, $currentrole)) {
            $section_subjects_info = $this->db->query(
                'SELECT * FROM teacher_subjects WHERE tid = ' . $userid .
                ' AND status=1 AND sec_sub_id IN(SELECT sec_sub_id FROM section_subjects WHERE status=1 AND cls_sec_id = ' . $section_id . ')'
            )->getResult();

            $classsubjects = '<option value="">Select Subject</option>';
            foreach ($section_subjects_info as $section_subjects) {
                $section_info = $this->db->query(
                    'SELECT * FROM section_subjects WHERE status=1 AND sec_sub_id = ' . $section_subjects->sec_sub_id
                )->getRow();

                $subjects_info = $this->db->table('allsubject')
                    ->where('sid', $section_info->subject_id)
                    ->where('system_id', $schoolinfo->system_id)
                    ->get()->getRow();
                if ($subjects_info) {
                    $classsubjects .= "<option value='" . $subjects_info->sid . "'>" . $subjects_info->subject_name . "</option>";
                }
            }
        } else {
            $section_subjects_info = $this->db->query(
                'SELECT * FROM section_subjects WHERE status=1 AND cls_sec_id = ' . $section_id
            )->getResult();

            $classsubjects = '<option value="">Select Subject</option>';
            foreach ($section_subjects_info as $section_subjects) {
                $subjects_info = $this->db->table('allsubject')
                    ->where('sid', $section_subjects->subject_id)
                    ->get()->getRow();
                if ($subjects_info) {
                    $classsubjects .= "<option value='" . $subjects_info->sid . "'>" . $subjects_info->subject_name . "</option>";
                }
            }
        }
        echo $classsubjects;
    }

    public function delete()
    {
        check_permission('admin-del-tests');
        $id = intval($this->request->getGet('id'));

        $this->db->transStart();
        $this->db->table('content_indexing')->where('contents_content_id', $id)->delete();
        $this->db->table('contents')->where('content_id', $id)->delete();
        $this->db->transComplete();
        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Worksheet Success']);
    }
}
