<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use DateInterval;
use DatePeriod;
use DateTime;

class ClassdiaryView extends BaseController
{
    protected $db;

    public function __construct()
    {
        check_permission('admin-classdairy');
        $this->db = \Config\Database::connect();
    }

    public function index()
{
    $campus_id = (int) session('member_campusid');
    $sessionid = (int) session('member_sessionid');
    $system_id = (int) getSchoolInfo()->system_id;

    // Today in Karachi, normalized to Y-m-d
    $today = (new \CodeIgniter\I18n\Time('now', 'Asia/Karachi'))->toDateString();

    // 1) All terms for dropdown
    $terms_session_info = $this->db->table('terms_session ts')
        ->select('ts.term_session_id, ts.term_id, ts.session_id, ts.start_date, ts.end_date, ts.status, t.name AS term_name')
        ->join('terms t', 't.term_id = ts.term_id', 'inner')
        ->where('ts.session_id', $sessionid)
        ->where('ts.system_id', $system_id)
        ->orderBy('ts.start_date', 'ASC')
        ->get()->getResult();

    // 2) Current term = where today is between start/end (inclusive)
    $currentTerm = $this->db->table('terms_session')
        ->select('term_session_id, start_date, end_date')
        ->where('session_id', $sessionid)
        ->where('system_id', $system_id)
        ->where('DATE(start_date) <=', $today)
        ->where('DATE(end_date) >=', $today)
        ->orderBy('start_date', 'ASC')
        ->get()->getRowArray();

    // Fallback if no exact match:
    if (!$currentTerm) {
        // Prefer the most recent past term…
        $currentTerm = $this->db->table('terms_session')
            ->select('term_session_id, start_date, end_date')
            ->where('session_id', $sessionid)
            ->where('system_id', $system_id)
            ->where('DATE(start_date) <=', $today)
            ->orderBy('start_date', 'DESC')
            ->get()->getRowArray();

        // …or, if none in the past, pick the earliest upcoming
        if (!$currentTerm) {
            $currentTerm = $this->db->table('terms_session')
                ->select('term_session_id, start_date, end_date')
                ->where('session_id', $sessionid)
                ->where('system_id', $system_id)
                ->where('DATE(start_date) >', $today)
                ->orderBy('start_date', 'ASC')
                ->get()->getRowArray();
        }
    }

    $currentTermSessionId = $currentTerm['term_session_id'] ?? null;

    // 3) Current week inside that term (today in range)
    $currentWeek = null;
    if ($currentTermSessionId) {
        $currentWeek = $this->db->table('term_weeks')
            ->select('term_weeks_id, start_date, end_date')
            ->where('term_session_id', $currentTermSessionId)
            ->where('DATE(start_date) <=', $today)
            ->where('DATE(end_date) >=', $today)
            ->orderBy('start_date', 'ASC')
            ->get()->getRowArray();

        // Fallback: first week of the term if no exact match
        if (!$currentWeek) {
            $currentWeek = $this->db->table('term_weeks')
                ->select('term_weeks_id, start_date, end_date')
                ->where('term_session_id', $currentTermSessionId)
                ->orderBy('start_date', 'ASC')
                ->get()->getRowArray();
        }
    }

    $data = [
        'sessionData'             => ['campusid' => $campus_id, 'sessionid' => $sessionid],
        'terms_session_info'      => $terms_session_info,
        'current_term_session_id' => $currentTermSessionId,
        'current_term_week_id'    => $currentWeek['term_weeks_id'] ?? null,
    ];

    return view('admin/classdiary_view', $data);
}

    public function data()
    {
        $campusid = session('member_campusid');
        $sessionid = session('member_sessionid');
        $term_session_id = $this->request->getPost('term_id');
        $term_weeks_id = $this->request->getPost('term_weeks_id');

        if (empty($term_weeks_id)) {
            return '<div class="col-lg-12 bg-danger text-center">Select Term Week </div>';
        }

        $terms_session_info = $this->db->table('terms_session')
            ->where('session_id', $sessionid)
            ->get()->getResult();

        $class_info = $this->db->table('class_section')
            ->where(['campus_id' => $campusid, 'status' => 1])
            ->get()->getResult();

        $term_weeks = $this->db->table('term_weeks')
            ->where('term_weeks_id', $term_weeks_id)
            ->get()->getRow();

        $begin = new DateTime($term_weeks->start_date);
        $end = (new DateTime($term_weeks->end_date))->modify('+1 day');
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($begin, $interval, $end);

        $data = [];

        foreach ($class_info as $sections) {
            $section_subjects = $this->db->table('section_subjects')
                ->where(['cls_sec_id' => $sections->cls_sec_id, 'status' => 1])
                ->get()->getResult();

            $week_dates = [];
            $resultcard = [];

            foreach ($period as $value) {
                $date = $value->format('Y-m-d');
                $name_of_day = date('l', strtotime($date));

                $week_dates[] = $date;

                $schoolTimingsInfo = $this->db->query(
                    "SELECT * FROM school_timings WHERE dayname=? AND cls_sec_id=? AND type_id=(SELECT type_id FROM school_timing_types WHERE status=1 AND campus_id=?)",
                    [$name_of_day, $sections->cls_sec_id, $campusid]
                )->getRow();

                if (!$schoolTimingsInfo || $schoolTimingsInfo->checkin_timing == $schoolTimingsInfo->checkout_timing) {
                    continue;
                }

                foreach ($section_subjects as $subject) {
                    $classdairy_info = $this->db->table('classdairy')
                        ->where([
                            'term_weeks_id' => $term_weeks_id,
                            'sec_sub_id' => $subject->sec_sub_id,
                            'date' => $date
                        ])->get()->getRow();

                    if ($classdairy_info) {
                        $subject_name = $this->db->table('allsubject')
                            ->where('sid', $subject->subject_id)
                            ->get()->getRow('subject_name');

                        if ($subject_name) {
                            $resultcard[$subject_name][$date] = $classdairy_info->detail;
                        }
                    }
                }
            }

            $session_info = $this->db->table('academic_session')
                ->where('session_id', $sessionid)
                ->get()->getRow();

            $sectioninfo = getClassSection($sections->cls_sec_id);

            $data[] = [
                'class' => $sectioninfo['sectionclassname'],
                'cls_sec_id' => $sections->cls_sec_id,
                'session_name' => $session_info->session_name,
                'week_dates' => $week_dates,
                'result' => $resultcard,
            ];
        }

        return view('admin/classdiary_result', ['data' => $data]);
    }


     public function getClassDiary()
    {
        $campusid = $this->session->get('member_campusid');
        $term_weeks_id = $this->request->getPost('term_weeks');
        $sec_sub_id = $this->request->getPost('sec_sub_id');

        $term_weeks = $this->db->table('term_weeks')->where('term_weeks_id', $term_weeks_id)->get()->getRow();

        if ($term_weeks) {
            $begin = new \DateTime($term_weeks->start_date);
            $end = new \DateTime($term_weeks->end_date);
            $end = $end->modify('+1 day');
            $interval = new \DateInterval('P1D');
            $period = new \DatePeriod($begin, $interval, $end);

            $termweekdays = '<div class="row">
                <div class="col-lg-2">
                  <div class="form-group">
                    <label for="subject_name">Day</label> 
                  </div>
                </div>
                <div class="col-lg-10">
                  <div class="form-group">
                    <label for="detail">Detail</label>
                  </div>
                </div>
              </div>';

            foreach ($period as $key => $value) {
                $date = $value->format('Y-m-d');
                $nameOfDay = date('D', strtotime($date));

                if (!empty($sec_sub_id)) {
                    $classdairy_info = $this->db->table('classdairy')
                        ->where('term_weeks_id', $term_weeks_id)
                        ->where('sec_sub_id', $sec_sub_id)
                        ->where('date', $date)
                        ->get()->getRow();
                } else {
                    return $this->response->setBody('<div class="col-lg-10 text-danger text-center">Select Class Section AND Subject</div>');
                }

                if (!empty($classdairy_info)) {
                    $termweekdays .= '<div class="row">
                        <div class="col-lg-2">
                            <div class="form-group">
                                <input type="hidden" name="did[]" value="' . $classdairy_info->did . '" />
                                <label  style="font-weight:bold !important;">' . dayDateFormat($date) . '</label>
                                <input type="hidden" class="form-control" name="date[]" id="date" value="' . $date . '">
                            </div>
                        </div>
                        <div class="col-lg-10">
                            <div class="form-group">
                                <textarea class="form-control editor" name="detail[]" id="detail">' . $classdairy_info->detail . '</textarea>
                                <script>$(".editor").summernote();</script>
                                <label>Other Detail</label>
                                <textarea class="form-control editor" name="other_detail[]" id="detail">' . $classdairy_info->other_detail . '</textarea>
                                <script>$(".editor").summernote();</script>
                                <input type="url" placeholder="Video URL" class="form-control" value="' . $classdairy_info->video_url . '" name="video_url[]">
                            </div>
                        </div>
                    </div>';
                } else {
                    $termweekdays .= '<div class="row">
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label style="font-weight:bold !important;">' . dayDateFormat($date) . '</label>
                                <input type="hidden" class="form-control" name="date[]" id="date" value="' . $date . '">
                            </div>
                        </div>
                        <div class="col-lg-10">
                            <div class="form-group">
                                <textarea class="form-control editor" name="detail[]" id="detail"></textarea>
                                <script>$(".editor").summernote();</script>
                                <label>Other Detail</label>
                                <textarea class="form-control editor" name="other_detail[]" id="detail"></textarea>
                                <script>$(".editor").summernote();</script>
                                <input type="url" placeholder="Video URL" class="form-control" value="" name="video_url[]">
                            </div>
                        </div>
                    </div>';
                }
            }
        } else {
            $termweekdays = '<div class="col-lg-10 text-danger text-center">Select Term Weeks</div>';
        }
        return $this->response->setBody($termweekdays);
    }

   
}


