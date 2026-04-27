<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Weekly_planning_progress extends BaseController
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
        check_permission('admin-add-weekly-planning');
        $sessionid = $this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();

        $currentrole = currentUserRoles();
        $sectionsclassinfo = in_array(5, $currentrole) ? teacherSubjectSections() : userClassSections();
        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

        $subjectinfo = $this->db->table('allsubject')->where('system_id', $schoolinfo->system_id)->get()->getResult();
        $this->template_data['subjectinfo'] = $subjectinfo;

        // $termsinfo = $this->db->table('terms_session')
        //     ->where('system_id', $schoolinfo->system_id)
        //     ->where('session_id', $sessionid)
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

        return view('admin/weekly_planning_overall_progress', $this->template_data);
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
            $sectionSubject_info = $this->db->table('section_subjects')->where('cls_sec_id', $section_id)->get()->getResult();

            $termweeks .= '<div class="row"><div class="col-lg-8">
            <div class="row">
                <div class="col-lg-3">
                <div class="form-group">
                    <label for="subject_name"><b>Subject</b></label>
                </div>
                </div>        
                <div class="col-lg-9">
                <div class="form-group">
                    <label for="detail"><b>Details</b></label>
                </div>
                </div>
            </div>';

            $i = 0;
            // For the donut chart overall values
            $overall = [
                'done' => 0,
                'pending' => 0,
                'skip' => 0,
                'partially-done' => 0,
                'reschedule' => 0,
            ];

            foreach ($sectionSubject_info as $value) {
                // For each subject, get counts by status
                $counts = [];
                foreach (['done', 'pending', 'skip', 'partially-done', 'reschedule'] as $status) {
                    $builder = $this->db->table('weekly_planning')
                        ->whereIn('term_week_id', function($builder) use ($term_session_id) {
                            return $builder->select('term_weeks_id')->from('term_weeks')->where('term_session_id', $term_session_id);
                        })
                        ->where('subject_id', $value->subject_id)
                        ->where('class_id', $classsectioninfo->class_id)
                        ->where('campus_id', $campusid)
                        ->where('status', $status);

                    $counts[$status] = $builder->countAllResults();
                    $overall[$status] += $counts[$status];
                }

                $subject_info = $this->db->table('allsubject')->where('sid', $value->subject_id)->get()->getRow();
                $subject_name = $subject_info->subject_name ?? '';

                $termweeks .= '<div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="subject_name">' . $subject_name . '</label><br>
                        </div>
                    </div>            
                    <div class="col-lg-9">
                        <table class="table table-bordered text-center">
                            <tr><th>Done</th><th>Pending</th><th>Skip</th><th>Partially Done</th><th>Reschedule</th></tr>
                            <tr>
                                <td>' . $counts['done'] . '</td>
                                <td>' . $counts['pending'] . '</td>
                                <td>' . $counts['skip'] . '</td>
                                <td>' . $counts['partially-done'] . '</td>
                                <td>' . $counts['reschedule'] . '</td>
                            </tr>
                        </table>
                    </div>
                </div>';

                $i++;
            }

            $termweeks .= '</div><div class="col-lg-4">Graph<br><canvas id="donutChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas></div></div>';
            $termweeks .= "<script>
                var donutChartCanvas = $('#donutChart').get(0).getContext('2d')
                var donutData        = {
                  labels: [
                      'Pending',
                      'Done',
                      'Skip',
                      'Partially Done',
                      'Reschedule',
                  ],
                  datasets: [
                    {
                      data: [".$overall['pending'].",".$overall['done'].",".$overall['skip'].",".$overall['partially-done'].",".$overall['reschedule']."],
                      backgroundColor : ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'],
                    }
                  ]
                }
                var donutOptions     = {
                  maintainAspectRatio : false,
                  responsive : true,
                }
                new Chart(donutChartCanvas, {
                  type: 'doughnut',
                  data: donutData,
                   options: {
                        elements: {
                          center: {
                            text: '".$overall['pending']."',
                            color: '#FF6384',
                            fontStyle: 'Arial',
                            sidePadding: 20,
                            minFontSize: 25,
                            lineHeight: 25
                          }
                        },
                        doughnutlabel: {
                          labels: [{
                            text: '550',
                            font: {
                              size: 20,
                              weight: 'bold'
                            }
                          }, {
                            text: 'total'
                          }]
                        }
                      }
                })
            </script>";
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
