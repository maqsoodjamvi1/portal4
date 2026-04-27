<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use stdClass;

class Weekly_planning_subject_view extends BaseController
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
        $campus_id = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $sessionData = [
            'campusid' => $campus_id,
            'sessionid' => $sessionid
        ];
        $this->template_data['sessionData'] = $sessionData;

        // $terms_session_info = $this->db->table('terms_session')
        //     ->where('session_id', $sessionid)
        //     ->get()->getResult();
        // $this->template_data['terms_session_info'] = $terms_session_info;

        $terms_session_info = $this->db->table('terms_session ts')
		    ->select('ts.*, t.name as term_name')
		    ->join('terms t', 't.term_id = ts.term_id')
		    ->where('ts.session_id', $sessionid)
		    ->get()->getResult();
		$this->template_data['terms_session_info'] = $terms_session_info;


        return view('admin/weekly_planning_subject_view', $this->template_data);
    }

    public function data()
    {
        $campus_id = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $term_session_id = $this->request->getPost('term_id');
        $term_week_id = $this->request->getPost('term_week_id');
        $schoolinfo = getSchoolInfo();
        $sessionData = [
            'campusid' => $campus_id,
            'sessionid' => $sessionid
        ];
        $this->template_data['sessionData'] = $sessionData;

        $terms_session_info = $this->db->table('terms_session')
            ->where('session_id', $sessionid)
            ->get()->getResult();
        $this->template_data['terms_session_info'] = $terms_session_info;

        $classes_info = $this->db->table('class_section')
            ->where('campus_id', $campus_id)
            ->where('status', 1)
            ->get()->getResult();

        $terminfo = $this->db->query(
            "SELECT * FROM terms WHERE term_id IN (SELECT term_id FROM terms_session WHERE term_session_id=?)",
            [$term_session_id]
        )->getRow();

        $session_info = $this->db->table('academic_session')
            ->where('session_id', $sessionid)
            ->get()->getRow();

        $builder = $this->db->table('term_weeks')
            ->where('term_session_id', $term_session_id)
            ->where('week_type_id', 1);

        if ($term_week_id) {
            if (is_array($term_week_id)) {
                $builder->whereIn('term_weeks_id', $term_week_id);
            } else {
                $builder->where('term_weeks_id', $term_week_id);
            }
        }
        $term_weeks_info = $builder->get()->getResult();

        foreach ($classes_info as $classinfo) {
            $class_info = $this->db->table('classes')->where('class_id', $classinfo->class_id)->get()->getRow();

            $strWeeklyPlaning = '';
            $strWeeklyPlaning .= '<p style="page-break-before: always;">&nbsp;</p>
            <page>
            <div  class="weekly_planing" style="border:2px solid #000; float:left; width:100%; margin:10px auto; padding:2px;">
            <div style="width:100%;border:2px solid #000;float:left;width:100%;text-align:center;font-weight:bold;padding: 5px;font-size: 18px;color: #000;line-height: 20px;">Weekly Planning (' . ($terminfo->name ?? '') . ' ' . ($session_info->session_name ?? '') . ')
            <div style="width:100%;padding-left:15px;float:left;font-size: 16px;font-weight: normal;margin-top: 0px;">' . ($class_info->class_name ?? '') . '</div>
            </div>
            <table class="table" style="margin-bottom: 2px;">
            <thead>
            <tr><th style="width: 5%;border:1px solid #000;">Subject</th>';

            $classsubjects = $this->db->table('section_subjects')
                ->where('cls_sec_id', $classinfo->cls_sec_id)
                ->where('status', 1)
                ->get()->getResult();

            foreach ($classsubjects as $classSubjectInfo) {
                $subjectsInfo = $this->db->table('allsubject')->where('sid', $classSubjectInfo->subject_id)->get()->getRow();
                $strWeeklyPlaning .= '<th style="border:1px solid #000;">' . ($subjectsInfo->subject_name ?? '') . '</th>';
            }
            $strWeeklyPlaning .= '</tr></thead><tbody>';

            foreach ($term_weeks_info as $value) {
                $strWeeklyPlaning .= '<tr>';
                $strWeeklyPlaning .= '<th style="border:1px solid #000;font-size: 12px;">' . $value->week_name . '</th>';

                foreach ($classsubjects as $subectinfo) {
                    $academicsubjects = $this->db->table('allsubject')->where('sid', $subectinfo->subject_id)->get()->getRow();

                    $weekly_planning_data = $this->db->table('weekly_planning')
                        ->where('class_id', $classinfo->class_id)
                        ->where('campus_id', $campus_id)
                        ->where('subject_id', $academicsubjects->sid)
                        ->where('term_week_id', $value->term_weeks_id)
                        ->get()->getRow();

                    if ($weekly_planning_data) {
                        $strWeeklyPlaning .= '<td style="border:1px solid #000;font-size: 14px;direction: rtl">' . $weekly_planning_data->objectives . '</td>';
                    }
                }
                $strWeeklyPlaning .= '</tr>';
            }
            $strWeeklyPlaning .= '</tbody>
                </table>
                </div></page><br><br><br><br>
                <div style="clear: both;margin-bottom: 60px;"></div>';

            echo $strWeeklyPlaning;
        }
        // Note: In CI4, you might want to return a Response instead of echo if calling via AJAX
    }
}
