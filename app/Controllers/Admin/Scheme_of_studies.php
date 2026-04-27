<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Scheme_of_studies extends BaseController
{
    protected $db;
    protected $session;
    protected $template_data = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        check_permission('admin-weekly-planning');
    }

    public function index()
    {
        return view('admin/scheme_of_studies', $this->template_data);
    }

    public function data()
    {
        $response = new \stdClass();
        $response->draw = $this->request->getPost('draw');
        $schoolinfo = getSchoolInfo();
        $search = $this->request->getPost('search');
        $keyword = $search['value'] ?? '';

        // Count
        $builder = $this->db->table('weekly_planning A')->selectCount('A.wp_id', 'ccount');
        if ($keyword) $builder->like('A.scheme_text', $keyword);
        $q = $builder->get()->getRow();
        $response->recordsTotal = $q->ccount;

        // Data
        $builder = $this->db->table('weekly_planning A')->select('A.*');
        if ($keyword) $builder->like('A.scheme_text', $keyword);
        $builder->orderBy('A.wp_id', 'desc');
        $builder->limit($this->request->getPost('length'), $this->request->getPost('start'));
        $results = $builder->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];
        foreach ($results as $row) {
            $subjectinfo = $this->db->table('allsubject')->where('sid', $row->subject_id)->get()->getRow();
            $classinfo = $this->db->table('classes')->where('class_id', $row->class_id)->get()->getRow();
            $term_week_info = $this->db->table('term_weeks')->where('term_weeks_id', $row->term_week_id)->get()->getRow();
            $term_week_name = $term_week_info ? $term_week_info->week_name : '';

            $data = [
                'id' => $row->wp_id,
                'week_name' => $term_week_name,
                'subject_name' => $subjectinfo->subject_name ?? '',
                'class_name' => $classinfo->class_name ?? '',
                'scheme_text' => $row->scheme_text
            ];
            $response->data[] = $data;
        }
        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-weekly-planning');
        $sessionid = $this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();

        $currentrole = currentUserRoles();
        $sectionsclassinfo = in_array(5, $currentrole) ? teacherSubjectSections() : userClassSections();
        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

        $this->template_data['subjectinfo'] = $this->db->table('allsubject')->where('system_id', $schoolinfo->system_id)->get()->getResult();

        $this->template_data['termsinfo'] = $this->db->table('terms_session')
            ->where('system_id', $schoolinfo->system_id)
            ->where('session_id', $sessionid)
            ->get()->getResult();

        $this->template_data['term_weeks_info'] = $this->db->table('term_weeks')->get()->getResult();

        $this->template_data['academic_session'] = $this->db->query(
            'SELECT * FROM academic_session WHERE system_id=' . $schoolinfo->system_id . ' AND session_id >=' . $sessionid
        )->getResult();

        $this->template_data['termsinfo'] = $this->db->table('terms_session ts')
            ->select('ts.*, t.name')
            ->join('terms t', 't.term_id = ts.term_id')
            ->where('ts.system_id', $schoolinfo->system_id)
            ->where('ts.session_id', $sessionid)
            ->get()->getResult();

        return view('admin/scheme_of_studies_edit', $this->template_data);
    }

    public function edit()
    {
        check_permission('admin-edit-weekly-planning');
        $id = intval($this->request->getGet('id'));

        $info = $this->db->table('classdairy')->where('did', $id)->get()->getRow();
        $this->template_data['info'] = $info;

        $this->template_data['classesinfo'] = $this->db->table('classes')->get()->getResult();
        $this->template_data['subjectinfo'] = $this->db->table('allsubject')->get()->getResult();

        return view('admin/scheme_of_studies_edit', $this->template_data);
    }

    public function save()
    {
        $synch = $this->request->getPost('synch') ?? 0;
        $wpids = $this->request->getPost('id');
        $term_weeks_ids = $this->request->getPost('term_weeks_id');
        $campusid = $this->session->get('member_campusid');
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');
        $subject_id = $this->request->getPost('subject_id');
        $schoolinfo = getSchoolInfo();

        if (empty($subject_id)) {
            return $this->response->setJSON(['error' => TRUE, 'msg' => 'Select Subject to add weekly planning.']);
        }

        $classsectioninfo = $this->db->table('class_section')->where('cls_sec_id', $this->request->getPost('section_id'))->get()->getRow();

        $i = 0;
        if (empty($wpids)) {
            check_permission('admin-add-weekly-planning');
            $this->db->transBegin();
            foreach ($term_weeks_ids as $key => $term_weeks_id) {
                $scheme_text = $this->request->getPost('scheme_text' . $i);
                $campuslock = $this->request->getPost('lock_' . $term_weeks_id) ?: 1;

                $campusinfo = $this->db->table('campus')->where('system_id', $schoolinfo->system_id)->get()->getResult();
                foreach ($campusinfo as $campus) {
                    $data = [
                        'subject_id' => trim($subject_id),
                        'class_id' => $classsectioninfo->class_id,
                        'scheme_text' => $scheme_text,
                        'campus_id' => $campus->campus_id,
                        'term_week_id' => trim($term_weeks_id),
                        'set_lock' => 1,
                        'created_date' => $date,
                        'user_id' => $user_id
                    ];
                    $this->db->table('weekly_planning')->insert($data);
                }
                $data = ['set_lock' => $campuslock];
                $this->db->table('weekly_planning')
                    ->where('subject_id', $subject_id)
                    ->where('class_id', $classsectioninfo->class_id)
                    ->where('term_week_id', $term_weeks_id)
                    ->where('set_lock', 0)
                    ->where('campus_id', $campusid)
                    ->update($data);
                $i++;
            }
            $this->db->transComplete();
            return $this->response->setJSON(['success' => TRUE, 'msg' => 'Scheme Of Studies Added Successfully']);
        } else {
            check_permission('admin-edit-weekly-planning');
            $this->db->transBegin();
            foreach ($term_weeks_ids as $key => $term_weeks_id) {
                $scheme_text = $this->request->getPost('scheme_text' . $i);
                $wp_id = $wpids[$i];
                $campuslock = $this->request->getPost('lock_' . $term_weeks_id) ?: 0;

                if ($synch == 1) {
                    $campusinfo = $this->db->table('campus')->get()->getResult();
                    foreach ($campusinfo as $campus) {
                        $data = [
                            'scheme_text' => $scheme_text,
                            'updated_date' => $date,
                            'user_id' => $user_id
                        ];
                        $this->db->table('weekly_planning')
                            ->where('subject_id', $subject_id)
                            ->where('class_id', $classsectioninfo->class_id)
                            ->where('term_week_id', $term_weeks_id)
                            ->where('set_lock', 0)
                            ->where('campus_id', $campus->campus_id)
                            ->update($data);
                    }
                    $data = ['set_lock' => $campuslock];
                    $this->db->table('weekly_planning')
                        ->where('subject_id', $subject_id)
                        ->where('class_id', $classsectioninfo->class_id)
                        ->where('term_week_id', $term_weeks_id)
                        ->where('set_lock', 0)
                        ->where('campus_id', $campus->campus_id)
                        ->update($data);
                } else {
                    $data = [
                        'subject_id' => trim($subject_id),
                        'class_id' => $classsectioninfo->class_id,
                        'scheme_text' => $scheme_text,
                        'campus_id' => $campusid,
                        'term_week_id' => trim($term_weeks_id),
                        'set_lock' => $campuslock,
                        'updated_date' => $date,
                        'user_id' => $user_id
                    ];
                    $this->db->table('weekly_planning')
                        ->where('wp_id', $wp_id)
                        ->where('campus_id', $campusid)
                        ->update($data);
                }
                $i++;
            }
            $this->db->transComplete();
            return $this->response->setJSON(['success' => TRUE, 'msg' => 'Edit Scheme of studies Success']);
        }
    }

    public function getWeeklyPlanning()
    {
        $session_id = $this->request->getPost('session_id');
        $term_session_id = $this->request->getPost('term_session_id');
        $section_id = $this->request->getPost('section_id');
        $subject_id = $this->request->getPost('subject_id');
        $campusid = $this->session->get('member_campusid');

        $classsectioninfo = $this->db->table('class_section')->where('cls_sec_id', $section_id)->get()->getRow();

        $termweeks = '';
        if ($term_session_id) {
            $term_weeks_info = $this->db->table('term_weeks')
                ->where('term_session_id', $term_session_id)
                ->where('week_type_id', 1)
                ->get()->getResult();

            $termweeks .= '<div class="row">
                <div class="col-lg-2">
                    <div class="form-group"><label for="subject_name"><b>Week Name</b></label></div>
                </div>        
                <div class="col-lg-7">
                    <div class="form-group"><label for="detail"><b>Detail</b></label></div>
                </div>
                <div class="col-lg-1">
                    <div class="form-group"><label for="detail"><b>Lock</b></label></div>
                </div>
            </div>';
            $i = 0;
            foreach ($term_weeks_info as $key => $value) {
                $weekly_planning_info = $this->db->table('weekly_planning')
                    ->where('term_week_id', $value->term_weeks_id)
                    ->where('subject_id', $subject_id)
                    ->where('class_id', $classsectioninfo->class_id)
                    ->where('campus_id', $campusid)
                    ->get()->getRow();

                $week_name = $value->week_name;
                if (!empty($weekly_planning_info)) {
                    $termweeks .= '<div class="row">
                        <div class="col-lg-2">
                            <div class="form-group">
                                <input type="hidden" name="id[]" value="' . $weekly_planning_info->wp_id . '">
                                <input type="hidden" name="term_weeks_id[]" value="' . $value->term_weeks_id . '">
                                <label for="subject_name">' . $week_name . '</label><br>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="form-group">
                                <textarea class="form-control editor" name="scheme_text' . $i . '" id="scheme_text">' . $weekly_planning_info->scheme_text . '</textarea><br>
                            </div>
                        </div>
                        <script>$(".editor").summernote();</script>
                        <div class="col-lg-3"></div>
                    </div>';
                } else {
                    $termweeks .= '<div class="row">
                        <div class="col-lg-2">
                            <div class="form-group">
                                <input type="hidden" name="term_weeks_id[]" value="' . $value->term_weeks_id . '">
                                <label for="subject_name">' . $week_name . '</label>
                                <br>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="form-group">
                                <textarea class="form-control editor" name="scheme_text' . $i . '" id="scheme_text"></textarea>
                            </div>
                        </div>
                        <script>$(".editor").summernote();</script>
                        <div class="col-lg-3"></div>
                    </div>';
                }
                $i++;
            }
        }
        return $this->response->setBody($termweeks);
    }

    public function delete()
    {
        check_permission('admin-del-user');
        $id = intval($this->request->getGet('id'));

        $this->db->transBegin();
        $this->db->table('weekly_planning')->where('wp_id', $id)->delete();
        $this->db->transComplete();
        return $this->response->setJSON(['success' => TRUE, 'msg' => 'Delete Classes Success']);
    }
}
