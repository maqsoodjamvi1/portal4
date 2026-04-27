<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class Weekly_planning_docview extends BaseController
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
        return view('admin/weekly_planning', $this->template_data);
    }

    public function data()
    {
        $response = new stdClass();
        $response->draw = $this->request->getPost('draw');
        $schoolinfo = getSchoolInfo();
        $search = $this->request->getPost('search');
        $keyword = $search['value'] ?? '';

        // Count
        $builder = $this->db->table('weekly_planning A')->selectCount('A.wp_id', 'ccount');
        if ($keyword) $builder->where('A.short_title', $keyword);
        $q = $builder->get()->getRow();
        $response->recordsTotal = $q->ccount;

        // Data
        $builder = $this->db->table('weekly_planning A')->select('A.*');
        if ($keyword) $builder->where('A.short_title', $keyword);
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
                'objectives' => $row->objectives
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

        $subjectinfo = $this->db->table('allsubject')->where('system_id', $schoolinfo->system_id)->get()->getResult();
        $this->template_data['subjectinfo'] = $subjectinfo;

        // $termsinfo = $this->db->table('terms_session ts')
        //     ->select('ts.*, t.name as term_name')
        //     ->join('terms t', 't.term_id = ts.term_id')
        //     ->where('ts.system_id', $schoolinfo->system_id)
        //     ->where('ts.session_id', $sessionid)
        //     ->get()->getResult();
        // $this->template_data['termsinfo'] = $termsinfo;

        $termsinfo = $this->db->table('terms_session ts')
				    ->select('ts.*, t.name as term_name')
				    ->join('terms t', 't.term_id = ts.term_id')
				    ->where('ts.system_id', $schoolinfo->system_id)
				    ->where('ts.session_id', $sessionid)
				    ->get()->getResult();
				$this->template_data['termsinfo'] = $termsinfo;


        $term_weeks_info = $this->db->table('term_weeks')->get()->getResult();
        $this->template_data['term_weeks_info'] = $term_weeks_info;

        $academic_session = $this->db->query('SELECT * FROM academic_session WHERE system_id=' . $schoolinfo->system_id . ' AND session_id >=' . $sessionid)->getResult();
        $this->template_data['academic_session'] = $academic_session;

        return view('admin/weekly_planning_edit_docview', $this->template_data);
    }

    public function edit()
    {
        check_permission('admin-edit-weekly-planning');
        $id = intval($this->request->getGet('id'));

        $info = $this->db->table('classdairy')->where('did', $id)->get()->getRow();
        $this->template_data['info'] = $info;

        $classesinfo = $this->db->table('classes')->get()->getResult();
        $this->template_data['classesinfo'] = $classesinfo;

        $subjectinfo = $this->db->table('allsubject')->get()->getResult();
        $this->template_data['subjectinfo'] = $subjectinfo;

        return view('admin/weekly_planning_edit', $this->template_data);
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

        $i = 0;
        $classsectioninfo = $this->db->table('class_section')->where('cls_sec_id', $this->request->getPost('section_id'))->get()->getRow();

        check_permission('admin-edit-weekly-planning');
        $this->db->transBegin();
        foreach ($term_weeks_ids as $key => $term_weeks_id) {
            $wp_id = $wpids[$i];
            $status = $this->request->getPost('status' . $i);

            $data = [
                'status' => $status,
                'updated_date' => $date,
                'user_id' => $user_id
            ];

            $this->db->table('weekly_planning')
                ->where('wp_id', $wp_id)
                ->where('campus_id', $campusid)
                ->update($data);

            $i++;
        }
        $this->db->transComplete();
        return $this->response->setJSON(['success' => TRUE, 'msg' => 'Edit Weekly Planning Success']);
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

            $termweeks = '<div class="row">
                <div class="col-lg-2"><div class="form-group"><label for="subject_name"><b>Week Name</b></label></div></div>
                <div class="col-lg-7"><div class="form-group"><label for="detail"><b>Detail</b></label></div></div>
                <div class="col-lg-1"><div class="form-group"><label for="detail"><b>Status</b></label></div></div>
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
                            <div class="form-group"><input type="hidden" name="id[]" value="' . $weekly_planning_info->wp_id . '"><input type="hidden" name="term_weeks_id[]" value="' . $value->term_weeks_id . '">
                                <label for="subject_name">' . $week_name . '</label><br>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="form-group">
                                <iframe src="/weekly_doc/' . $weekly_planning_info->doc_url . '" width="100%" height="500px"></iframe>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <select class="form-control" name="status' . $i . '">
                                <option ' . ($weekly_planning_info->status == 'done' ? 'selected="selected"' : '') . ' value="done">Done</option>
                                <option ' . ($weekly_planning_info->status == 'pending' ? 'selected="selected"' : '') . ' value="pending">Pending</option>
                                <option ' . ($weekly_planning_info->status == 'skip' ? 'selected="selected"' : '') . ' value="skip">Skip</option>
                                <option ' . ($weekly_planning_info->status == 'partially-done' ? 'selected="selected"' : '') . ' value="partially-done">Partially Done</option>
                                <option ' . ($weekly_planning_info->status == 'reschedule' ? 'selected="selected"' : '') . ' value="reschedule">Reschedule</option>
                            </select>
                        </div>
                    </div>';
                } else {
                    $termweeks .= '<div class="row">
                        <div class="col-lg-2">
                            <div class="form-group"><input type="hidden" name="term_weeks_id[]" value="' . $value->term_weeks_id . '">
                                <label for="subject_name">' . $week_name . '</label><br>
                            </div>
                        </div>
                        <div class="col-lg-7">Document not found</div>
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
