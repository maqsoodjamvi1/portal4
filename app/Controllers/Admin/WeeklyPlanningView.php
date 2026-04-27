<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class WeeklyPlanningView extends BaseController
{
    public function __construct()
    {
        check_permission('admin-weekly-planning');
    }

    public function index()
    {
        $campus_id = session('member_campusid');
        $sessionid = session('member_sessionid');

        $sessionData = [
            'campusid' => $campus_id,
            'sessionid' => $sessionid,
        ];

        $db = \Config\Database::connect();
        //$terms_session_info = $db->table('terms_session')->where('session_id', $sessionid)->get()->getResult();
        //$terminfo = $db->table('terms')->where('session_id', $sessionid)->get()->getResult();
        // Controller method
        $terms_session_info = $this->db->table('terms_session ts')
            ->select('ts.term_session_id, ts.term_id, t.name as term_name')
            ->join('terms t', 't.term_id = ts.term_id')
            ->where('ts.session_id', $sessionid)
            ->get()->getResult();

        $this->template_data['terms_session_info'] = $terms_session_info;

        $data = [
            'sessionData' => $sessionData,
            'terms_session_info' => $terms_session_info,
        ];



        return view('admin/weekly_planning_view', $data);
    }

    public function data()
    {
        $db = \Config\Database::connect();
        $campus_id = session('member_campusid');
        $sessionid = session('member_sessionid');
        $term_session_id = $this->request->getPost('term_id');
        $term_week_id = $this->request->getPost('term_week_id');
        $schoolinfo = getSchoolInfo();

        $sessionData = [
            'campusid' => $campus_id,
            'sessionid' => $sessionid,
        ];

        $terms_session_info = $db->table('terms_session')->where('session_id', $sessionid)->get()->getResult();
        $classes_info = $db->table('classes')->where('system_id', $schoolinfo->system_id)->get()->getResult();

        $terminfo = $db->query(
            'SELECT * FROM terms WHERE term_id IN (SELECT term_id FROM terms_session WHERE term_session_id=?)',
            [$term_session_id]
        )->getRow();

        $term_weeks_q = $db->table('term_weeks')
            ->where('term_session_id', $term_session_id)
            ->where('week_type_id', 1);

        if (!empty($term_week_id)) {
            $term_weeks_q->whereIn('term_weeks_id', explode(',', $term_week_id));
        }

        $term_weeks_info = $term_weeks_q->get()->getResult();

        $data = [];

        foreach ($classes_info as $classinfo) {
            $subjects = $db->table('allsubject')->where('system_id', $schoolinfo->system_id)->get()->getResult();

            $week_name = [];
            $resultcard = [];

            foreach ($term_weeks_info as $week) {
                $week_name[] = ['week_name' => $week->week_name];

                foreach ($subjects as $subject) {
                    $planning = $db->table('weekly_planning')
                        ->where([
                            'class_id' => $classinfo->class_id,
                            'campus_id' => $campus_id,
                            'subject_id' => $subject->sid,
                            'term_week_id' => $week->term_weeks_id
                        ])
                        ->get()
                        ->getRow();

                    if ($planning) {
                        $resultcard[$subject->subject_name][$week->term_weeks_id] = $planning->objectives;
                    }
                }
            }

            $session_info = $db->table('academic_session')->where('session_id', $sessionid)->get()->getRow();

            $data[] = [
                'class' => $classinfo->class_name,
                'term_name' => $terminfo->name ?? '',
                'session_name' => $session_info->session_name ?? '',
                'week_name' => $week_name,
                'result' => $resultcard,
            ];
        }

        return view('admin/weekly_planning_view', [
            'sessionData' => $sessionData,
            'terms_session_info' => $terms_session_info,
            'data' => $data,
        ]);
    }
}
