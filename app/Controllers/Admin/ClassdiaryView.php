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
        helper(['role', 'server', 'school']);
        $this->db = \Config\Database::connect();
    }

    private function isTeacherUser(): bool
    {
        return isCurrentUserTeacher();
    }

    private function diaryAccessMessage(string $message, string $type = 'warning'): string
    {
        return '<div class="col-lg-12 bg-' . esc($type, 'attr') . ' text-center p-3">'
            . esc($message)
            . '</div>';
    }

    /**
     * Apply teacher section scoping to a class_section query builder.
     *
     * @return true|string true on success, or an HTML error message string
     */
    private function applySectionAccessFilter($classQuery, $section_id)
    {
        if (! $this->isTeacherUser()) {
            if (! empty($section_id)) {
                $classQuery->where('cls_sec_id', (int) $section_id);
            }

            return true;
        }

        $allowedIds = getTeacherAllowedClassSectionIds();
        if ($allowedIds === []) {
            return $this->diaryAccessMessage(
                'No class sections are assigned to you yet. Please contact your administrator.'
            );
        }

        $sectionId = (int) $section_id;
        if ($sectionId > 0) {
            if (! teacherCanViewClassSection($sectionId)) {
                return $this->diaryAccessMessage(
                    'You do not have access to view the diary for this class section.'
                );
            }

            $classQuery->where('cls_sec_id', $sectionId);

            return true;
        }

        $classQuery->whereIn('cls_sec_id', $allowedIds);

        return true;
    }

    public function index()
    {
        $campus_id = (int) session('member_campusid');
        $sessionid = (int) session('member_sessionid');
        $system_id = (int) getSchoolInfo()->system_id;
        $isTeacher = $this->isTeacherUser();

        // Today in Karachi, normalized to Y-m-d
        $today = (new \CodeIgniter\I18n\Time('now', 'Asia/Karachi'))->toDateString();

        // Get sections for filter dropdown
        if ($isTeacher) {
            $sections = [];
            foreach (getTeacherSubjectSections() as $row) {
                $sections[] = [
                    'cls_sec_id'   => (int) $row['cls_sec_id'],
                    'section_name' => (string) ($row['sectionclassname'] ?? ''),
                ];
            }
        } else {
            $sections = $this->db->table('class_section cs')
                ->select('cs.cls_sec_id, CONCAT(c.class_name, " - ", s.section_name) as section_name')
                ->join('classes c', 'c.class_id = cs.class_id')
                ->join('sections s', 's.section_id = cs.section_id')
                ->where('cs.campus_id', $campus_id)
                ->where('cs.status', 1)
                ->orderBy('c.class_name', 'ASC')
                ->get()
                ->getResultArray();
        }

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
            'sections'                => $sections,
            'isTeacher'               => $isTeacher,
            'sessionData'             => ['campusid' => $campus_id, 'sessionid' => $sessionid],
            'terms_session_info'      => $terms_session_info,
            'current_term_session_id' => $currentTermSessionId,
            'current_term_week_id'    => $currentWeek['term_weeks_id'] ?? null,
        ];

        return view('admin/classdiary_view', $data);
    }

    // Get weeks for a term (used by Add Class Diary + Daily Diary view)
    public function getWeeks()
    {
        return $this->response->setJSON([
            'weeks' => $this->fetchTermWeeksForSession((int) $this->request->getPost('term_id')),
        ]);
    }

    public function getAllWeeks()
    {
        return $this->response->setJSON([
            'weeks' => $this->fetchTermWeeksForSession((int) $this->request->getPost('term_id')),
        ]);
    }

    /**
     * Load weeks for a term session — match Classdiary::add() (no term_weeks.system_id filter).
     * Scope via terms_session.session_id so weeks are not hidden for non-first terms.
     *
     * @return list<object>
     */
    private function fetchTermWeeksForSession(int $termSessionId): array
    {
        if ($termSessionId <= 0) {
            return [];
        }

        $sessionId = (int) session('member_sessionid');
        if ($sessionId <= 0) {
            return [];
        }

        $termExists = $this->db->table('terms_session')
            ->where('term_session_id', $termSessionId)
            ->where('session_id', $sessionId)
            ->countAllResults() > 0;

        if (! $termExists) {
            return [];
        }

        return $this->db->table('term_weeks')
            ->select('term_weeks_id, week_no, start_date, end_date, week_name')
            ->where('term_session_id', $termSessionId)
            ->orderBy('start_date', 'ASC')
            ->get()
            ->getResult();
    }


   public function data()
    {
        try {
            return $this->renderDiaryData();
        } catch (\Throwable $e) {
            log_message('error', 'ClassdiaryView::data failed: {msg} @ {file}:{line}', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return '<div class="alert alert-danger mb-0">Unable to load diary view. '
                . 'If this persists, run <code>php spark fix:school-timings</code> on the server.</div>';
        }
    }

   private function renderDiaryData()
    {
        $campusid = session('member_campusid');
        $sessionid = session('member_sessionid');
        $term_session_id = $this->request->getPost('term_id');
        $term_weeks_id = $this->request->getPost('term_weeks_id');
        $section_id = $this->request->getPost('section_id');
        $view_mode = $this->request->getPost('view_mode'); // 'single_week' or 'all_weeks'
        
        // Get filter options from POST
        $showHomework = (bool) $this->request->getPost('show_homework');
        $showClasswork = (bool) $this->request->getPost('show_classwork');
        $showAudio = (bool) $this->request->getPost('show_audio');
        $showVideo = (bool) $this->request->getPost('show_video');
        $showPicture = (bool) $this->request->getPost('show_picture');
        $showQuiz = (bool) $this->request->getPost('show_quiz');
        $showActivities = (bool) $this->request->getPost('show_activities');
        $showBagPack = (bool) $this->request->getPost('show_bagpack');

        // If all-weeks mode, process all weeks
        if ($view_mode === 'all_weeks' && $term_session_id) {
            return $this->getAllWeeksData($campusid, $sessionid, $term_session_id, $section_id, [
                'showHomework' => $showHomework,
                'showClasswork' => $showClasswork,
                'showAudio' => $showAudio,
                'showVideo' => $showVideo,
                'showPicture' => $showPicture,
                'showQuiz' => $showQuiz,
                'showActivities' => $showActivities,
                'showBagPack' => $showBagPack
            ]);
        }

        // Single week view (original functionality)
        if (empty($term_weeks_id)) {
            return '<div class="col-lg-12 bg-danger text-center">Select Term Week </div>';
        }

        // Get class info - filter by section if selected (teachers: only assigned sections)
        $classQuery = $this->db->table('class_section')
            ->where(['campus_id' => $campusid, 'status' => 1]);

        $access = $this->applySectionAccessFilter($classQuery, $section_id);
        if ($access !== true) {
            return $access;
        }

        $class_info = $classQuery->get()->getResult();

        $term_weeks = $this->db->table('term_weeks')
            ->where('term_weeks_id', $term_weeks_id)
            ->get()->getRow();

        if (!$term_weeks) {
            return '<div class="col-lg-12 bg-danger text-center">Invalid Term Week selected</div>';
        }

        $begin = new DateTime($term_weeks->start_date);
        $end = (new DateTime($term_weeks->end_date))->modify('+1 day');
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($begin, $interval, $end);

        $data = $this->processDiaryData($class_info, $period, $term_weeks_id, $campusid, $sessionid);

        // Pass filter options to the view
        return view('admin/classdiary_result', [
            'data' => $data,
            'showHomework' => $showHomework,
            'showClasswork' => $showClasswork,
            'showAudio' => $showAudio,
            'showVideo' => $showVideo,
            'showPicture' => $showPicture,
            'showQuiz' => $showQuiz,
            'showActivities' => $showActivities,
            'showBagPack' => $showBagPack,
            'view_mode' => 'single_week',
            'week_info' => $term_weeks
        ]);
    }



private function getAllWeeksData($campusid, $sessionid, $term_session_id, $section_id, $filterOptions)
{
    // Get all weeks for the term
    $weeks = $this->db->table('term_weeks')
        ->select('term_weeks_id, week_no, start_date, end_date')
        ->where('term_session_id', $term_session_id)
        ->orderBy('start_date', 'ASC')
        ->get()
        ->getResult();

    if (empty($weeks)) {
        return '<div class="col-lg-12 bg-danger text-center">No weeks found for this term</div>';
    }

    $section_id = (int) $section_id;

    if ($this->isTeacherUser()) {
        $allowedIds = getTeacherAllowedClassSectionIds();
        if ($allowedIds === []) {
            return $this->diaryAccessMessage(
                'No class sections are assigned to you yet. Please contact your administrator.'
            );
        }

        if ($section_id <= 0) {
            return $this->diaryAccessMessage('Please select a specific class section.');
        }

        if (! teacherCanViewClassSection($section_id)) {
            return $this->diaryAccessMessage(
                'You do not have access to view the diary for this class section.'
            );
        }
    }

    // Get class info - ONLY for selected section (not all sections)
    $classInfo = $this->db->table('class_section')
        ->select('cls_sec_id')
        ->where(['campus_id' => $campusid, 'status' => 1, 'cls_sec_id' => $section_id])
        ->get()
        ->getRow();

    if (!$classInfo) {
        return '<div class="col-lg-12 bg-danger text-center">Please select a specific class section</div>';
    }

    $section_subjects = $this->db->table('section_subjects')
        ->where(['cls_sec_id' => $classInfo->cls_sec_id, 'status' => 1])
        ->get()
        ->getResult();

    $allWeeksData = [];

    foreach ($weeks as $week) {
        $begin = new DateTime($week->start_date);
        $end = (new DateTime($week->end_date))->modify('+1 day');
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($begin, $interval, $end);

        $week_dates = [];
        $resultcard = [];
        $audioTasks = [];
        $videoTasks = [];
        $pictureTasks = [];
        $quizTasks = [];
        $activitiesData = [];
        $bagPackMaterials = [];

        foreach ($period as $value) {
            $date = $value->format('Y-m-d');
            $name_of_day = date('l', strtotime($date));

            $week_dates[] = $date;

            foreach ($section_subjects as $subject) {
                $classdairy_info = $this->db->table('classdairy')
                    ->where([
                        'term_weeks_id' => $week->term_weeks_id,
                        'sec_sub_id' => $subject->sec_sub_id,
                        'date' => $date
                    ])->get()->getRow();

                if ($classdairy_info) {
                    $subject_name = $this->db->table('allsubject')
                        ->where('sid', $subject->subject_id)
                        ->get()->getRow('subject_name');

                    if ($subject_name) {
                        // Store Home Work and Class Work
                        $resultcard[$subject_name][$date] = [
                            'homework' => $classdairy_info->detail,
                            'classwork' => $classdairy_info->other_detail,
                            'is_book' => $classdairy_info->is_book ?? 0,
                            'is_notebook' => $classdairy_info->is_notebook ?? 0
                        ];
                        
                        // Store Audio Task
                        if (($classdairy_info->is_audio ?? 0) == 1) {
                            $audioTasks[$subject_name][$date] = [
                                'enabled' => true,
                                'caption' => $classdairy_info->audio_caption ?? ''
                            ];
                        }
                        
                        // Store Video Task
                        if (($classdairy_info->is_video ?? 0) == 1) {
                            $videoTasks[$subject_name][$date] = [
                                'enabled' => true,
                                'caption' => $classdairy_info->video_caption ?? ''
                            ];
                        }
                        
                        // Store Picture Task
                        if (($classdairy_info->is_picture ?? 0) == 1) {
                            $pictureTasks[$subject_name][$date] = [
                                'enabled' => true,
                                'caption' => $classdairy_info->picture_caption ?? ''
                            ];
                        }
                        
                        // Store Quiz Info
                        if (!empty($classdairy_info->quiz_id)) {
                            $quizInfo = $this->db->table('quizzes')
                                ->select('quiz_id, title, time_limit_sec, max_attempts, questions_count, instructions')
                                ->where('quiz_id', $classdairy_info->quiz_id)
                                ->get()->getRow();
                            
                            if ($quizInfo) {
                                $quizTasks[$subject_name][$date] = $quizInfo;
                            }
                        }
                        
                        // Store Activities
                        if (!empty($classdairy_info->activities)) {
                            $activities = json_decode($classdairy_info->activities, true);
                            if (!empty($activities)) {
                                $activitiesData[$subject_name][$date] = $activities;
                            }
                        }
                        
                        // Collect bag pack materials
                        $materials = [];
                        if (($classdairy_info->is_book ?? 0) == 1) {
                            $materials[] = '📖 Book';
                        }
                        if (($classdairy_info->is_notebook ?? 0) == 1) {
                            $materials[] = '📓 Notebook';
                        }
                        if (!empty($materials)) {
                            $bagPackMaterials[$subject_name][$date] = $materials;
                        }
                    }
                }
            }
        }

        $session_info = $this->db->table('academic_session')
            ->where('session_id', $sessionid)
            ->get()->getRow();

        $sectioninfo = getClassSection($classInfo->cls_sec_id);

        $classData = [
            'class' => $sectioninfo['sectionclassname'],
            'class_full_name' => $sectioninfo['sectionclassname'],
            'cls_sec_id' => $classInfo->cls_sec_id,
            'session_name' => $session_info->session_name ?? '',
            'week_dates' => $week_dates,
            'result' => $resultcard,
            'audio_tasks' => $audioTasks,
            'video_tasks' => $videoTasks,
            'picture_tasks' => $pictureTasks,
            'quiz_tasks' => $quizTasks,
            'activities' => $activitiesData,
            'bag_pack' => $bagPackMaterials,
        ];

        $allWeeksData[] = [
            'week_no' => $week->week_no,
            'week_info' => $week,
            'data' => [$classData]  // Only one class data
        ];
    }

    return view('admin/classdiary_result_all_weeks', [
        'all_weeks_data' => $allWeeksData,
        'showHomework' => $filterOptions['showHomework'],
        'showClasswork' => $filterOptions['showClasswork'],
        'showAudio' => $filterOptions['showAudio'],
        'showVideo' => $filterOptions['showVideo'],
        'showPicture' => $filterOptions['showPicture'],
        'showQuiz' => $filterOptions['showQuiz'],
        'showActivities' => $filterOptions['showActivities'],
        'showBagPack' => $filterOptions['showBagPack'],
        'view_mode' => 'all_weeks',
        'selected_subject' => $_POST['subject_id'] ?? ''
    ]);
}
     private function processDiaryData($class_info, $period, $term_weeks_id, $campusid, $sessionid)
    {
        $data = [];
        $campusId = (int) $campusid;
        $clsSecIds = array_map(static fn ($section) => (int) $section->cls_sec_id, $class_info);
        $allowedDaysBySection = buildAllowedWorkingDaysMap(
            getSchoolTimingsForSections($clsSecIds, $campusId)
        );

        foreach ($class_info as $sections) {
            $section_subjects = $this->db->table('section_subjects')
                ->where(['cls_sec_id' => $sections->cls_sec_id, 'status' => 1])
                ->get()->getResult();

            $week_dates = [];
            $resultcard = [];
            $audioTasks = [];
            $videoTasks = [];
            $pictureTasks = [];
            $quizTasks = [];
            $activitiesData = [];
            $bagPackMaterials = [];

            foreach ($period as $value) {
                $date = $value->format('Y-m-d');
                $name_of_day = date('l', strtotime($date));

                $week_dates[] = $date;

                if (! isWorkingDayForSection((int) $sections->cls_sec_id, $name_of_day, $allowedDaysBySection)) {
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
                            // Store Home Work (detail) and Class Work (other_detail)
                            $resultcard[$subject_name][$date] = [
                                'homework' => $classdairy_info->detail,
                                'classwork' => $classdairy_info->other_detail,
                                'is_book' => $classdairy_info->is_book ?? 0,
                                'is_notebook' => $classdairy_info->is_notebook ?? 0
                            ];
                            
                            // Store Audio Task
                            if (($classdairy_info->is_audio ?? 0) == 1) {
                                $audioTasks[$subject_name][$date] = [
                                    'enabled' => true,
                                    'caption' => $classdairy_info->audio_caption ?? ''
                                ];
                            }
                            
                            // Store Video Task
                            if (($classdairy_info->is_video ?? 0) == 1) {
                                $videoTasks[$subject_name][$date] = [
                                    'enabled' => true,
                                    'caption' => $classdairy_info->video_caption ?? ''
                                ];
                            }
                            
                            // Store Picture Task
                            if (($classdairy_info->is_picture ?? 0) == 1) {
                                $pictureTasks[$subject_name][$date] = [
                                    'enabled' => true,
                                    'caption' => $classdairy_info->picture_caption ?? ''
                                ];
                            }
                            
                            // Store Quiz Info
                            if (!empty($classdairy_info->quiz_id)) {
                                $quizInfo = $this->db->table('quizzes')
                                    ->select('quiz_id, title, time_limit_sec, max_attempts, questions_count, instructions')
                                    ->where('quiz_id', $classdairy_info->quiz_id)
                                    ->get()->getRow();
                                
                                if ($quizInfo) {
                                    $quizTasks[$subject_name][$date] = $quizInfo;
                                }
                            }
                            
                            // Store Activities from JSON column
                            if (!empty($classdairy_info->activities)) {
                                $activities = json_decode($classdairy_info->activities, true);
                                if (!empty($activities)) {
                                    $activitiesData[$subject_name][$date] = $activities;
                                }
                            }
                            
                            // Collect bag pack materials
                            $materials = [];
                            if (($classdairy_info->is_book ?? 0) == 1) {
                                $materials[] = '📖 Book';
                            }
                            if (($classdairy_info->is_notebook ?? 0) == 1) {
                                $materials[] = '📓 Notebook';
                            }
                            if (!empty($materials)) {
                                $bagPackMaterials[$subject_name][$date] = $materials;
                            }
                        }
                    }
                }
            }

            $session_info = $this->db->table('academic_session')
                ->where('session_id', $sessionid)
                ->get()->getRow();

            $sectioninfo = getClassSection($sections->cls_sec_id) ?: [];
            $classLabel  = (string) ($sectioninfo['sectionclassname'] ?? ('Section ' . $sections->cls_sec_id));

            $data[] = [
                'class' => $classLabel,
                'class_full_name' => $classLabel,
                'cls_sec_id' => $sections->cls_sec_id,
                'session_name' => $session_info->session_name ?? '',
                'week_dates' => $week_dates,
                'result' => $resultcard,
                'audio_tasks' => $audioTasks,
                'video_tasks' => $videoTasks,
                'picture_tasks' => $pictureTasks,
                'quiz_tasks' => $quizTasks,
                'activities' => $activitiesData,
                'bag_pack' => $bagPackMaterials,
            ];
        }

        return $data;
    }

   public function getClassDiary()
    {
        $campusid = session('member_campusid');
        $term_weeks_id = $this->request->getPost('term_weeks');
        $sec_sub_id = (int) $this->request->getPost('sec_sub_id');

        if ($sec_sub_id > 0 && $this->isTeacherUser()) {
            $secSub = $this->db->table('section_subjects')
                ->select('cls_sec_id')
                ->where('sec_sub_id', $sec_sub_id)
                ->where('status', 1)
                ->get()
                ->getRow();

            if (! $secSub || ! teacherCanViewClassSection((int) $secSub->cls_sec_id)) {
                return $this->response->setBody(
                    '<div class="col-lg-10 text-danger text-center">You do not have access to view the diary for this class section.</div>'
                );
            }
        }

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