<?php



namespace App\Libraries;



/**

 * Daily recitation UI payload and save routing (para-only, lesson + sabqi + manzil tables).

 */

class HifzDailyService

{

    protected HifzProgressService $progress;

    protected HifzManzilCalculator $manzil;



    public function __construct()

    {

        helper('hifz');

        hifz_ensure_database_schema();



        $this->progress = new HifzProgressService();

        $this->manzil   = new HifzManzilCalculator();

    }



    /**

     * @param list<array<string, mixed>> $sectionPeers

     * @return array<string, mixed>

     */

    public function buildStudentDay(

        object $student,

        object $enrollment,

        string $date,

        int $sessionId,

        array $sectionPeers = []

    ): array {

        $studentId = (int) $student->student_id;

        $state = $this->progress->studentState($enrollment);

        $this->progress->syncSabqiActiveParasIfNeeded(
            $enrollment,
            $state['current_para'],
            $state['lines_done_in_para']
        );

        $lessonToday    = $this->progress->lessonLogForDate($studentId, $date);

        $lessonPrior    = $this->progress->findLessonBeforeDate($studentId, $date);

        $sabaqSavedToday = $this->progress->sabaqSavedToday($studentId, $date);

        $sabqiLog       = $this->progress->sabqiLogForDate($studentId, $date);

        $manzilLogs     = $this->progress->manzilLogsForDate($studentId, $date);



        $poolCards = $this->progress->poolParaCards($state['manzil_pool']);

        $suggested = $this->manzil->suggestFromPool(

            $state['manzil_pool'],

            $state['manzil_rotation_index'],

            $state['manzil_paras_per_day']

        );



        $manzilJuzToday = [];

        foreach ($manzilLogs as $ml) {

            $manzilJuzToday[] = (int) $ml->para_no;

        }

        if ($manzilJuzToday === []) {

            $manzilJuzToday = $suggested['juz_list'];

        }



        $sabqiRecited = $sabqiLog

            ? $this->progress->sabqiParasForLog((int) $sabqiLog->id)

            : [];



        $mutaliaEntryAllowed = $this->mutaliaEntryAllowed($lessonPrior, $lessonToday, $date);

        $currentPara = $state['current_para'];
        $sabaqBlock = $this->buildSabaqBlock($lessonPrior, $sabaqSavedToday);

        $canAddMutalia = $mutaliaEntryAllowed['allowed'] && $lessonToday === null;
        $showSabaqForm = ($sabaqBlock['ready'] ?? false) && ! $sabaqSavedToday;

        $sequence = $state['memorization_sequence'];

        $suggestedNewPara = hifzNextParaNo($state['current_para'], $sequence);

        $paraMeta = hifzParaCatalogEntry($state['current_para']);
        $totalLines = (int) $state['para_total_lines'];
        $linesDone  = (int) $state['lines_done_in_para'];
        $progressPct = $totalLines > 0 ? (int) round(100 * $linesDone / $totalLines) : 0;
        $quranProgress = hifzOverallQuranPercent($enrollment);
        $lessonsInPara = $this->progress->paraLessonCount($studentId, $currentPara);
        $linesRemaining      = max(0, $totalLines - $linesDone);
        $lastMutaliaLines    = $lessonPrior ? max(0, (int) ($lessonPrior->lines_count ?? 0)) : 0;
        $defaultMutaliaLines = $this->progress->mutaliaDefaultLines($enrollment, $studentId, $date, $linesRemaining);

        if (! function_exists('getStudentPhotoUrl')) {
            helper('server');
        }

        $photoUrl = getStudentPhotoUrl($student->profile_photo ?? '');

        return [

            'student_id'            => $studentId,

            'student_name'          => trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')),

            'reg_no'                => $student->reg_no ?? '',

            'photo_url'             => $photoUrl,

            'current_para'          => $state['current_para'],

            'current_para_label'    => hifzJuzTitle($state['current_para'], false),

            'current_para_name_en'  => $paraMeta['name_en'],

            'current_para_name_ar'  => $paraMeta['name_ar'],

            'current_para_title'    => $paraMeta['title'],

            'current_para_title_ar' => $paraMeta['title_ar'],

            'lines_done_in_para'    => $linesDone,

            'para_total_lines'      => $totalLines,

            'progress_label'        => hifzParaProgressLabel($state['current_para'], $linesDone),

            'progress_percent'      => min(100, max(0, $progressPct)),

            'quran_progress_percent' => $quranProgress['percent'],
            'quran_progress_label'   => $quranProgress['label'],
            'quran_memorized_lines'  => $quranProgress['memorized_lines'],
            'quran_total_lines'      => $quranProgress['total_lines'],

            'memorization_sequence' => $sequence,

            'is_para_reverse'       => hifzIsParaReverseSequence($sequence),

            'suggested_new_para'    => $suggestedNewPara,

            'manzil_paras_per_day'  => $state['manzil_paras_per_day'],

            'record_sabaq'          => $sabaqSavedToday,

            'record_sabqi'          => $sabqiLog !== null,

            'record_manzil'         => $manzilLogs !== [],

            'record_mutalia'        => $lessonToday !== null,

            'mutalia'               => [

                'para_no'              => $currentPara,

                'para_label'           => hifzJuzTitle($currentPara, false),

                'para_name_ar'         => $paraMeta['name_ar'],

                'lines_count'          => $lessonToday ? (int) $lessonToday->lines_count : 0,

                'lines_done_in_para'   => $linesDone,

                'para_total_lines'     => $totalLines,

                'lines_remaining'       => $linesRemaining,

                'default_lines'         => $defaultMutaliaLines,

                'last_mutalia_lines'    => $lastMutaliaLines,

                'lessons_in_para'      => $lessonsInPara,

                'remarks'              => $lessonToday ? (string) ($lessonToday->remarks ?? '') : '',

                'entry_allowed'        => $mutaliaEntryAllowed['allowed'],

                'entry_message'        => $mutaliaEntryAllowed['message'],

                'locked'               => $mutaliaEntryAllowed['locked'],

                'can_add_mutalia'      => $canAddMutalia,

                'show_mutalia_form'    => $canAddMutalia,

                'show_sabaq_form'      => $showSabaqForm,

                'sabaq'                => $sabaqBlock,

                'para_history'         => $this->progress->lessonLogsForPara($studentId, $currentPara),

            ],

            'sabaq'                 => array_merge($sabaqBlock, [
                'calendar' => $this->progress->buildSabaqTwoWeekCalendar($studentId, $sessionId),
            ]),

            'sabqi'                 => [

                'active_paras'       => $state['sabqi_paras'],

                'active_para_labels' => array_map(static fn ($p) => hifzJuzTitle($p, false), $state['sabqi_paras']),

                'recited_paras'      => $sabqiRecited !== [] ? $sabqiRecited : $state['sabqi_paras'],

                'remarks'            => $sabqiLog ? (string) ($sabqiLog->remarks ?? '') : '',

                'quality'            => $sabqiLog ? (string) ($sabqiLog->sabqi_quality ?? '') : '',

                'hard_mistakes'      => $sabqiLog ? (int) ($sabqiLog->hard_mistakes ?? 0) : 0,

                'soft_mistakes'      => $sabqiLog ? (int) ($sabqiLog->soft_mistakes ?? 0) : 0,

                'listener_type'      => $sabqiLog ? (string) ($sabqiLog->listener_type ?? 'teacher') : 'teacher',

                'listener_student_id' => $sabqiLog ? (int) ($sabqiLog->listener_student_id ?? 0) : 0,

                'calendar'           => $this->progress->buildSabqiTwoWeekCalendar($studentId, $sessionId),

            ],

            'manzil'                => [

                'juz_list'            => $manzilJuzToday,

                'today_juz_list'      => $manzilJuzToday,

                'suggested_juz_list'  => $suggested['juz_list'],

                'label'               => $this->manzil->formatParaLabel($manzilJuzToday),

                'pool_paras'          => $poolCards,

                'pool_juz_list'       => $state['manzil_pool'],

                'listener_type'       => $manzilLogs !== [] ? (string) ($manzilLogs[0]->listener_type ?? 'teacher') : 'teacher',

                'listener_student_id' => $manzilLogs !== [] ? (int) ($manzilLogs[0]->listener_student_id ?? 0) : 0,

                'hard_mistakes'       => $manzilLogs !== [] ? (int) ($manzilLogs[0]->hard_mistakes ?? 0) : 0,

                'soft_mistakes'       => $manzilLogs !== [] ? (int) ($manzilLogs[0]->soft_mistakes ?? 0) : 0,

                'quality'             => $manzilLogs !== [] ? (string) ($manzilLogs[0]->recitation_quality ?? '') : '',

                'remarks'             => $manzilLogs !== [] ? (string) ($manzilLogs[0]->remarks ?? '') : '',

                'calendar'            => $this->progress->buildManzilTwoWeekCalendar($studentId, $sessionId),

            ],

            'section_peers'         => $sectionPeers,

        ];

    }



    /**

     * @return array{allowed:bool,message:string,locked:bool}

     */

    protected function mutaliaEntryAllowed(?object $lessonPrior, ?object $lessonToday, string $date): array

    {

        if ($lessonToday && strpos((string) ($lessonToday->remarks ?? ''), 'weak Sabaq') !== false) {

            return [

                'allowed' => false,

                'message' => 'Same Mutalia was assigned automatically after Weak Sabaq.',

                'locked'  => true,

            ];

        }



        if (! $lessonPrior) {

            return ['allowed' => true, 'message' => '', 'locked' => false];

        }



        $quality = strtolower(trim((string) ($lessonPrior->sabaq_quality ?? '')));



        if ($quality === '') {

            return [

                'allowed' => false,

                'message' => 'Save Sabaq for the previous lesson (Excellent, Good, or Average) before new Mutalia.',

                'locked'  => false,

            ];

        }



        if (! hifzSabaqQualityAllowsNewMutalia($quality)) {

            return [

                'allowed' => false,

                'message' => hifzSabaqQualityRepeatsMutalia($quality)

                    ? 'Weak Sabaq — Mutalia already set for today.'

                    : 'Enter new Mutalia only after Excellent, Good, or Average Sabaq.',

                'locked'  => $lessonToday !== null,

            ];

        }



        return ['allowed' => true, 'message' => '', 'locked' => false];

    }



    /**

     * @return array<string, mixed>

     */

    protected function buildSabaqBlock(?object $lessonPrior, bool $sabaqSavedToday): array

    {

        if (! $lessonPrior) {

            return [

                'ready'   => false,

                'note'    => 'No Mutalia on a previous day. Use the Mutalia tab to assign the first lesson.',

                'quality' => '',

                'remarks' => '',

            ];

        }



        $pNo  = (int) $lessonPrior->para_no;
        $meta = hifzParaCatalogEntry($pNo);

        return [

            'ready'       => true,

            'lesson_date' => (string) $lessonPrior->entry_date,

            'para_no'     => $pNo,

            'para_label'  => hifzJuzTitle($pNo, false),

            'para_name_en'=> $meta['name_en'],

            'para_name_ar'=> $meta['name_ar'],

            'para_title'  => $meta['title'],

            'para_title_ar'=> $meta['title_ar'],

            'lines_count' => (int) $lessonPrior->lines_count,

            'label'       => hifzLessonLabel($lessonPrior),

            'quality'             => $sabaqSavedToday ? (string) ($lessonPrior->sabaq_quality ?? '') : '',

            'remarks'             => $sabaqSavedToday ? (string) ($lessonPrior->sabaq_remarks ?? '') : '',

            'hard_mistakes'       => (int) ($lessonPrior->sabaq_hard_mistakes ?? 0),

            'soft_mistakes'       => (int) ($lessonPrior->sabaq_soft_mistakes ?? 0),

            'listener_type'       => (string) ($lessonPrior->sabaq_listener_type ?? 'teacher'),

            'listener_student_id' => (int) ($lessonPrior->sabaq_listener_student_id ?? 0),

            'note'                => 'Listen to the previous Mutalia lesson and grade Sabaq.',

        ];

    }



    /**

     * @param array<string, mixed> $input

     * @return array{success:bool,msg:string}

     */

    public function saveStudentDay(

        int $studentId,

        int $campusId,

        int $sessionId,

        int $hifzSecId,

        int $teacherId,

        int $userId,

        string $date,

        array $input,

        object $enrollment

    ): array {

        if (! empty($input['record_mutalia'])) {

            return $this->progress->saveMutalia(

                $enrollment,

                $studentId,

                $campusId,

                $sessionId,

                $hifzSecId,

                $teacherId,

                $userId,

                $date,

                $input

            );

        }



        if (! empty($input['record_sabaq'])) {

            return $this->progress->saveSabaq(

                $enrollment,

                $studentId,

                $campusId,

                $sessionId,

                $hifzSecId,

                $teacherId,

                $userId,

                $date,

                $input

            );

        }



        if (! empty($input['record_sabqi'])) {

            return $this->progress->saveSabqi(

                $enrollment,

                $studentId,

                $campusId,

                $sessionId,

                $hifzSecId,

                $teacherId,

                $userId,

                $date,

                $input

            );

        }



        if (! empty($input['record_manzil'])) {

            return $this->progress->saveManzil(

                $enrollment,

                $studentId,

                $campusId,

                $sessionId,

                $hifzSecId,

                $teacherId,

                $userId,

                $date,

                $input

            );

        }



        return ['success' => false, 'msg' => 'Nothing selected to save.'];

    }



    /**

     * @return array{success:bool,msg:string}

     */

    public function removeSabqiPara(object $enrollment, int $paraNo): array

    {

        return $this->progress->removeSabqiPara($enrollment, $paraNo);

    }

}
