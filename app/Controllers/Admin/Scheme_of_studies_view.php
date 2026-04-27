<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use stdClass;

class Scheme_of_studies_view extends BaseController
{
    public function index()
    {
        check_permission('admin-weekly-planning');

        $campus_id = session('member_campusid');
        $sessionid = session('member_sessionid');

        $this->template_data['sessionData'] = [
            'campusid' => $campus_id,
            'sessionid' => $sessionid
        ];

        //$this->template_data['terms_session_info'] = db()->table('terms_session')
          //  ->where('session_id', $sessionid)->get()->getResult();

        $db = \Config\Database::connect();
		$this->template_data['terms_session_info']  = $db->table('academic_session')->get()->getResult();
    

        return view('admin/scheme_of_studies_view', $this->template_data);
    }

    public function data()
    {
        check_permission('admin-weekly-planning');

        $campus_id = session('member_campusid');
        $sessionid = session('member_sessionid');
        $term_session_id = $this->request->getPost('term_id');
        $schoolinfo = getSchoolInfo();

        $this->template_data['sessionData'] = [
            'campusid' => $campus_id,
            'sessionid' => $sessionid
        ];

        $this->template_data['terms_session_info'] = db()->table('terms_session')
            ->where('session_id', $sessionid)->get()->getResult();

        $classes_info = db()->table('classes')
            ->where('system_id', $schoolinfo->system_id)->get()->getResult();

        $terminfo = db()->query(
            'SELECT * FROM terms WHERE term_id IN (SELECT term_id FROM terms_session WHERE term_session_id=' . intval($term_session_id) . ')'
        )->getRow();

        $term_weeks_info = db()->table('term_weeks')
            ->where('term_session_id', $term_session_id)
            ->where('week_type_id', 1)
            ->get()->getResult();

        $data = [];
        foreach ($classes_info as $classinfo) {
            $subjects = db()->table('allsubject')
                ->where('system_id', $schoolinfo->system_id)->get()->getResult();

            $week_name = [];
            $resultcard = [];

            foreach ($term_weeks_info as $value) {
                $week_name[] = ['week_name' => $value->week_name];
                foreach ($subjects as $subjectinfo) {
                    $weekly = db()->query(
                        'SELECT * FROM weekly_planning WHERE class_id=' . intval($classinfo->class_id) .
                        ' AND campus_id=' . intval($campus_id) .
                        ' AND subject_id=' . intval($subjectinfo->sid) .
                        ' AND term_week_id=' . intval($value->term_weeks_id)
                    )->getRow();

                    if ($weekly) {
                        $subject = db()->table('allsubject')
                            ->where('sid', $subjectinfo->sid)->get()->getRow();
                        if ($subject) {
                            $resultcard[$subject->subject_name][$value->term_weeks_id] = $weekly->scheme_text;
                        }
                    }
                }
            }

            $session_info = db()->table('academic_session')
                ->where('session_id', $sessionid)->get()->getRow();

            $data[] = [
                'class' => $classinfo->class_name,
                'term_name' => $terminfo->name ?? '',
                'session_name' => $session_info->session_name ?? '',
                'week_name' => $week_name,
                'result' => $resultcard,
            ];
        }

        $this->template_data['data'] = $data;
        return view('scheme_of_studies_result', $this->template_data);
    }
}
