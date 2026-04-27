<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use stdClass;

class Classdairy_audio extends BaseController
{
    protected $db;
    protected $session;
    protected $template_data = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        check_permission('admin-classdairy');
    }

    public function index()
    {
        return view('admin/classdairy_audio', $this->template_data);
    }

    public function data()
    {
        $response = new stdClass;
        $response->draw = $this->request->getPost('draw');
        $campusid = $this->session->get('member_campusid');
        $keyword = $this->request->getPost('search')['value'] ?? '';

        $builder = $this->db->table('class_dairy_audio A')->selectCount('A.did_audio', 'ccount');
        $builder->where("(A.cls_sec_id IN (select cls_sec_id from class_section where campus_id={$campusid}))");
        if ($keyword) {
            $builder->where('A.short_title', $keyword);
        }
        $q = $builder->get()->getRow();
        $response->recordsTotal = $q->ccount;

        $builder = $this->db->table('class_dairy_audio A')->select('A.*');
        $builder->where("(A.cls_sec_id IN (select cls_sec_id from class_section where campus_id={$campusid}))");
        if ($keyword) {
            $builder->where('A.short_title', $keyword);
        }
        $builder->orderBy('A.did_audio', 'desc');
        $builder->limit($this->request->getPost('length'), $this->request->getPost('start'));
        $results = $builder->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];

        foreach ($results as $row) {
            $classinfo = getClassSection($row->cls_sec_id);
            $term_weeks_info = $this->db->table('term_weeks')->where('term_weeks_id', $row->term_week_id)->get()->getRow();
            $date = $row->date;
            $nameOfDay = date('D', strtotime($date)); 

            $data = [];
            $data['id'] = $row->did_audio;
            $data['date1'] = $nameOfDay;
            $data['class_name'] = $classinfo['sectionclassname'] ?? '';
            $data['week_name'] = $term_weeks_info->week_name ?? '';
            $data['dairy_audio'] = $row->dairy_audio;
            $response->data[] = $data;
        }

        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-classdairy');
        $campus_id = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();

        $sessionData = [
            'campusid' => $campus_id,
            'sessionid' => $sessionid
        ];
        $this->template_data['sessionData'] = $sessionData;

        // JOIN to get term name for dropdown
        $terms_session_info = $this->db->table('terms_session ts')
            ->select('ts.*, t.name as term_name')
            ->join('terms t', 't.term_id = ts.term_id')
            ->where('ts.session_id', $sessionid)
            ->get()->getResult();
        $this->template_data['terms_session_info'] = $terms_session_info;

        $currentrole = currentUserRoles();
        $sectionsclassinfo = in_array(5, $currentrole) ? teacherSubjectSections() : userClassSections();
        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

        $subjectinfo = $this->db->table('allsubject')->get()->getResult();
        $this->template_data['subjectinfo'] = $subjectinfo;

        $termsinfo = $this->db->table('terms')->get()->getResult();
        $this->template_data['termsinfo'] = $termsinfo;

        $term_weeks_info = $this->db->table('term_weeks')->get()->getResult();
        $this->template_data['term_weeks_info'] = $term_weeks_info;

        return view('admin/classdairy_audio_edit', $this->template_data);
    }

    public function edit()
    {
        check_permission('admin-edit-classdairy');
        $id = intval($this->request->getGet('id'));

        $info = $this->db->table('classdairy')->where('did', $id)->get()->getRow();
        $this->template_data['info'] = $info;

        $classesinfo = $this->db->table('classes')->get()->getResult();
        $this->template_data['classesinfo'] = $classesinfo;

        $subjectinfo = $this->db->table('allsubject')->get()->getResult();
        $this->template_data['subjectinfo'] = $subjectinfo;

        return view('admin/classdairy_audio_edit', $this->template_data);
    }

    public function save()
    {
        // NOTE: You must use CodeIgniter 4 file upload logic for best practices
        $id = $this->request->getPost('did');
        $dates = $this->request->getPost('date');
        $section_id = $this->request->getPost('section_id');
        $term_weeks = $this->request->getPost('term_weeks');

        if (!$this->validate([
            'dairy_audio0' => [
                'uploaded[dairy_audio0]',
                'mime_in[dairy_audio0,audio/mpeg,audio/ogg,audio/mp3,audio/mp4,audio/x-m4a,video/mp4]',
                'max_size[dairy_audio0,10240]',
            ],
        ])) {
            // Optional: handle validation errors here
        }

        if ($id == 0) {
            check_permission('admin-add-classdairy');
            $i = 0;
            $this->db->transBegin();
            foreach ($dates as $key => $date) {
                $audioField = 'dairy_audio' . $i;
                $audioFile = $this->request->getFile($audioField);
                $audioFileName = '';
                if ($audioFile && $audioFile->isValid() && !$audioFile->hasMoved()) {
                    $audioFileName = $audioFile->getRandomName();
                    $audioFile->move(WRITEPATH . '../public/dairyaudios', $audioFileName);
                }

                $data = [
                    'date' => trim($date),
                    'cls_sec_id' => trim($section_id),
                    'dairy_audio' => $audioFileName,
                    'term_week_id' => trim($term_weeks),
                ];

                $this->db->table('class_dairy_audio')->insert($data);
                $i++;
            }
            $this->db->transComplete();
            return $this->response->setJSON(['success' => TRUE, 'msg' => 'Add Class Dairy Audio Success']);
        } else {
            check_permission('admin-edit-classdairy');
            $this->db->transBegin();
            $i = 0;
            foreach ($dates as $key => $date) {
                $did = $id[$i];
                $audioField = 'dairy_audio' . $i;
                $audioFile = $this->request->getFile($audioField);
                $audioFileName = '';
                if ($audioFile && $audioFile->isValid() && !$audioFile->hasMoved()) {
                    $audioFileName = $audioFile->getRandomName();
                    $audioFile->move(WRITEPATH . '../public/dairyaudios', $audioFileName);
                }

                if ($audioFileName) {
                    $data = [
                        'date' => trim($date),
                        'cls_sec_id' => trim($section_id),
                        'dairy_audio' => $audioFileName,
                        'term_week_id' => trim($term_weeks),
                    ];

                    $this->db->table('class_dairy_audio')
                        ->where('term_week_id', $term_weeks)
                        ->where('cls_sec_id', $section_id)
                        ->where('date', $date)
                        ->update($data);
                }
                $i++;
            }
            $this->db->transComplete();
            return $this->response->setJSON(['success' => TRUE, 'msg' => 'Edit Class Dairy Audio Success']);
        }
    }

    public function termweekdatebyclass()
    {
        $campusid = $this->session->get('member_campusid');
        $term_weeks_id = $this->request->getPost('term_weeks');
        $section_id = $this->request->getPost('section_id');
        if (empty($term_weeks_id)) {
            return $this->response->setBody("<div class='col-lg-12 bg-danger text-center'>Select Term Week </div>");
        }
        $term_weeks = $this->db->table('term_weeks')->where('term_weeks_id', $term_weeks_id)->get()->getRow();

        $begin = new \DateTime($term_weeks->start_date);
        $end = new \DateTime($term_weeks->end_date);
        $end = $end->modify('+1 day');
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($begin, $interval, $end);

        $termweekdays = '<input type="hidden" name="campus_id" value="' . $campusid . '" /><div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    <label for="subject_name">Day</label> 
                </div>
            </div>
            <div class="col-lg-9">
                <div class="form-group">
                    <label for="detail">Audio</label>
                </div>
            </div>
        </div>';
        $nCount = 0;
        foreach ($period as $key => $value) {
            $date = $value->format('Y-m-d');
            $nameOfDay = date('D', strtotime($date));

            $classdairy_info = $this->db->table('class_dairy_audio')
                ->where('term_week_id', $term_weeks_id)
                ->where('cls_sec_id', $section_id)
                ->where('date', $date)
                ->get()->getRow();

            if (!empty($classdairy_info)) {
                $termweekdays .= '<div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <input type="hidden" name="did[]" value="' . $classdairy_info->did_audio . '" />
                            <label  style="font-weight:bold !important;">' . dayDateFormat($date) . '</label>
                            <input type="hidden" class="form-control" name="date[]" id="date" value="' . $date . '">
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <div class="form-group">
                            <audio controls>
                                <source src="' . base_url('dairyaudios/' . $classdairy_info->dairy_audio) . '" type="audio/ogg">
                                <source src="' . base_url('dairyaudios/' . $classdairy_info->dairy_audio) . '" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                            <input type="file" name="dairy_audio' . $nCount . '" id="dairy_audio" />
                        </div>
                    </div>
                </div>';
            } else {
                $termweekdays .= '<div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label style="font-weight:bold !important;">' . dayDateFormat($date) . '</label>
                            <input type="hidden" class="form-control" name="date[]" id="date" value="' . $date . '">
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <div class="form-group">
                            <input type="file" name="dairy_audio' . $nCount . '" id="dairy_audio" />
                        </div>
                    </div>
                </div>';
            }
            $nCount++;
        }
        return $this->response->setBody($termweekdays);
    }

    public function delete()
    {
        check_permission('admin-del-user');
        $id = intval($this->request->getGet('id'));

        $this->db->transBegin();
        $this->db->table('class_dairy_audio')->where('did_audio', $id)->delete();
        $this->db->transComplete();
        return $this->response->setJSON(['success' => TRUE, 'msg' => 'Delete Class Dairy Audio Success']);
    }
}
