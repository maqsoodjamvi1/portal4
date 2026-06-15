<?php

namespace App\Libraries;

use Config\Database;
use Config\QuranReference;

/**
 * Daily Hifz recitation: suggestions, partial saves, Manzil calendar, Mutalia ayah chain.
 */
class HifzRecitationService
{
    protected $db;
    protected HifzLineResolver $lines;
    protected HifzSabqiCalculator $sabqi;
    protected HifzManzilCalculator $manzil;
    protected QuranAyahTextService $ayahText;

    public function __construct()
    {
        helper('hifz');
        hifz_ensure_database_schema();

        $this->db       = Database::connect();
        $this->lines    = new HifzLineResolver();
        $this->sabqi    = new HifzSabqiCalculator();
        $this->manzil   = new HifzManzilCalculator();
        $this->ayahText = new QuranAyahTextService();
    }

    /**
     * @param list<array<string, mixed>> $manzilHistory
     * @return array<string, mixed>
     */
    public function buildStudentDay(
        object $student,
        object $enrollment,
        ?object $rec,
        string $date,
        array $manzilHistory = []
    ): array {
        $layout     = hifzMushafLayoutCode();
        $cursor     = hifzResolveEnrollmentCursor($enrollment);
        $sabqiRaw   = $this->sabqi->computeSabqi($cursor, $layout);
        $sessionId  = (int) ($enrollment->session_id ?? 0);
        $studentId  = (int) $student->student_id;
        $juzStats   = $this->sabqi->cursorStats($layout, $cursor);
        $currentJuz = (int) ($enrollment->current_juz ?? 0);
        if ($currentJuz <= 0) {
            $currentJuz = (int) ($juzStats['current_juz'] ?? 1);
        }

        $sabqiRange = $this->lines->snapRangeToFullAyahs(
            $layout,
            (int) ($sabqiRaw['line_from'] ?? 0),
            (int) ($sabqiRaw['line_to'] ?? 0),
            0
        );

        $progress    = hifzEnrollmentProgress($enrollment);
        $manzilPool  = hifzAutoManzilPool($enrollment);
        $paraSnap    = $this->manzil->paraProgressSnapshot($cursor, $layout);
        $manzilBlock = $this->buildManzilBlock(
            $enrollment,
            $rec,
            $manzilPool,
            $manzilHistory['calendar'] ?? []
        );

        $lessonMutalia = $this->findMutaliaBeforeDate($studentId, $sessionId, $date);
        $mutaliaBlock  = $this->buildMutaliaBlock($enrollment, $rec, $date, $sessionId, $studentId);
        $sabaqBlock    = $this->buildSabaqBlock($lessonMutalia, $rec, $enrollment);
        $mutaliaEntry  = $this->mutaliaEntryState($lessonMutalia, $rec, $date);

        $mutaliaBlock['entry_allowed'] = $mutaliaEntry['allowed'];
        $mutaliaBlock['entry_message'] = $mutaliaEntry['reason'];
        $mutaliaBlock['locked']        = $mutaliaEntry['locked'];
        $mutaliaBlock['locked_reason'] = $mutaliaEntry['locked_reason'];

        $sabqiBlock = $this->formatBlock($sabqiRange, 0);
        $sabqiBlock['auto']          = true;
        $sabqiBlock['juz_no']        = (int) ($sabqiRaw['juz_no'] ?? 0);
        $sabqiBlock['progress_pct']  = (float) ($sabqiRaw['progress_pct'] ?? 0);
        $sabqiBlock['progress_hint'] = hifzSabqiProgressHint($enrollment);

        if ($rec) {
            $sabqiBlock = $this->mergeSavedSabqi($sabqiBlock, $rec, $layout);
            $mutaliaBlock = $this->mergeSavedMutalia($mutaliaBlock, $rec, $enrollment, $date, $sessionId, $studentId);
        }

        return [
            'student_id'          => $studentId,
            'student_name'        => trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')),
            'reg_no'              => $student->reg_no ?? '',
            'saved'               => $rec !== null,
            'cursor_line'         => $cursor,
            'progress_pct'        => round((float) ($sabqiRaw['progress_pct'] ?? 0), 1),
            'current_juz'         => $currentJuz,
            'current_juz_title'   => hifzJuzTitle($currentJuz),
            'manzil_paras_per_day' => (int) ($enrollment->manzil_paras_per_day ?? 1),
            'record_sabaq'        => $rec && trim((string) ($rec->sabaq_quality ?? '')) !== '',
            'record_sabqi'        => $rec && (int) ($rec->sabqi_line_to ?? 0) > 0,
            'record_manzil'       => $rec && trim((string) ($rec->manzil_juz_list ?? '')) !== '',
            'record_mutalia'      => $rec && (int) ($rec->mutalia_surah_id_end ?? 0) > 0,
            'sabaq'               => $sabaqBlock,
            'sabqi'               => $sabqiBlock,
            'manzil'              => $manzilBlock,
            'mutalia'             => $mutaliaBlock,
            'manzil_pool_auto'    => true,
            'current_para'        => (int) ($paraSnap['juz_no'] ?? $currentJuz),
            'current_para_pct'    => (float) ($paraSnap['progress_pct'] ?? 0),
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
        array $input
    ): array {
        $enroll = $this->db->table('hifz_students')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('status', 1)
            ->get()
            ->getRow();

        if (! $enroll) {
            return ['success' => false, 'msg' => 'Hifz enrollment not found.'];
        }

        $recordSabaq   = ! empty($input['record_sabaq']);
        $recordSabqi   = ! empty($input['record_sabqi']);
        $recordManzil  = ! empty($input['record_manzil']);
        $recordMutalia = ! empty($input['record_mutalia']);

        if (! $recordSabaq && ! $recordSabqi && ! $recordManzil && ! $recordMutalia) {
            return ['success' => false, 'msg' => 'Nothing selected to save.'];
        }

        $existing = $this->db->table('hifz_daily_recitation')
            ->where('student_id', $studentId)
            ->where('recitation_date', $date)
            ->get()
            ->getRow();

        $layout = hifzMushafLayoutCode();
        $now    = date('Y-m-d H:i:s');
        $data   = [
            'student_id'      => $studentId,
            'hifz_sec_id'     => $hifzSecId,
            'recitation_date' => $date,
            'teacher_id'      => $teacherId,
            'campus_id'       => $campusId,
            'session_id'      => $sessionId,
            'updated_date'    => $now,
            'user_id'         => $userId,
        ];

        if ($existing) {
            $data = array_merge($this->rowToArray($existing), $data);
        } else {
            $data['created_date'] = $now;
        }

        if ($recordSabaq) {
            $quality = strtolower(trim((string) ($input['sabaq_quality'] ?? '')));

            if ($quality === '') {
                return ['success' => false, 'msg' => 'Select Sabaq quality for today\'s lesson.'];
            }

            $lessonMutalia = $this->findMutaliaBeforeDate($studentId, $sessionId, $date);

            if (! $lessonMutalia) {
                return ['success' => false, 'msg' => 'No previous Mutalia found. Assign Mutalia first.'];
            }

            $data['sabaq_quality'] = $quality;
            $data['sabaq_remarks'] = trim((string) ($input['sabaq_remarks'] ?? '')) ?: null;

            foreach ([
                'sabaq_lines_requested',
                'sabaq_line_from',
                'sabaq_line_to',
                'sabaq_surah_id_start',
                'sabaq_ayah_from',
                'sabaq_surah_id_end',
                'sabaq_ayah_to',
            ] as $col) {
                $data[$col] = null;
            }

            if (hifzSabaqQualityRepeatsMutalia($quality)) {
                $this->applyMutaliaRangeToData($data, $lessonMutalia, $enrollment);
            }
        }

        if ($recordSabqi) {
            $from = (int) ($input['sabqi_line_from'] ?? 0);
            $to   = (int) ($input['sabqi_line_to'] ?? 0);
            if ($from <= 0 || $to <= 0) {
                $cursor  = (int) ($enroll->current_global_line ?? 0);
                $sabqiRaw = $this->sabqi->computeSabqi($cursor, $layout);
                $from     = (int) ($sabqiRaw['line_from'] ?? 0);
                $to       = (int) ($sabqiRaw['line_to'] ?? 0);
            }
            $range = $this->lines->snapRangeToFullAyahs($layout, $from, $to, 0);

            $data['sabqi_line_from']        = (int) $range['line_from'];
            $data['sabqi_line_to']          = (int) $range['line_to'];
            $data['sabqi_auto_generated']   = ! empty($input['sabqi_auto']) ? 1 : 0;
            $data['sabqi_quality']          = trim((string) ($input['sabqi_quality'] ?? '')) ?: null;
            $data['sabqi_remarks']          = trim((string) ($input['sabqi_remarks'] ?? '')) ?: null;
        }

        if ($recordManzil) {
            $juzList = $this->manzil->parseJuzList($input['manzil_juz_list'] ?? '');
            if ($juzList === []) {
                return ['success' => false, 'msg' => 'Select at least one Manzil para.'];
            }

            $parasPerDay = (int) ($enroll->manzil_paras_per_day ?? 1);
            if (count($juzList) > $parasPerDay) {
                $juzList = array_slice($juzList, 0, $parasPerDay);
            }

            $listenerType = strtolower(trim((string) ($input['manzil_listener_type'] ?? 'teacher')));
            if (! in_array($listenerType, ['teacher', 'fellow'], true)) {
                $listenerType = 'teacher';
            }

            $fellowId = (int) ($input['manzil_listener_student_id'] ?? 0);
            if ($listenerType === 'fellow' && $fellowId <= 0) {
                return ['success' => false, 'msg' => 'Select the class fellow who listened.'];
            }

            $data['manzil_juz_list']              = implode(',', $juzList);
            $data['manzil_listener_type']         = $listenerType;
            $data['manzil_listener_student_id']   = $listenerType === 'fellow' ? $fellowId : null;
            $data['manzil_hard_mistakes']         = max(0, (int) ($input['manzil_hard_mistakes'] ?? 0));
            $data['manzil_soft_mistakes']         = max(0, (int) ($input['manzil_soft_mistakes'] ?? 0));
        }

        if ($recordMutalia) {
            $lessonMutalia = $this->findMutaliaBeforeDate($studentId, $sessionId, $date);
            $entryState    = $this->mutaliaEntryState($lessonMutalia, $existing, $date);

            if (! $entryState['allowed']) {
                return ['success' => false, 'msg' => $entryState['reason'] ?: 'Mutalia cannot be entered yet.'];
            }

            $targetSurah = hifzMutaliaTargetSurah($enroll);
            $endAyah     = (int) ($input['mutalia_ayah_to'] ?? 0);

            if ($endAyah <= 0) {
                return ['success' => false, 'msg' => 'Enter the ending ayah number for Mutalia.'];
            }

            $start    = $this->resolveMutaliaStartForSave($studentId, $sessionId, $date, $enroll, $input);
            $sequence = (string) ($enroll->memorization_sequence ?? 'para_forward');
            $endSurah = $targetSurah;

            if ($endAyah < (int) $start['ayah']) {
                return ['success' => false, 'msg' => 'Ending ayah cannot be before today\'s starting ayah (' . $start['ayah'] . ').'];
            }

            $counts = QuranReference::$surahAyahCounts;
            $maxAy  = (int) ($counts[$targetSurah - 1] ?? 0);
            if ($endAyah > $maxAy) {
                return ['success' => false, 'msg' => 'Ending ayah is too high for ' . hifzSurahName($targetSurah, false) . '.'];
            }

            if ($sequence === 'surah_reverse_full') {
                $learned = (int) ($enroll->reverse_learned_from_surah ?? 114);
                $bounds  = hifzSurahReverseAyahRange($learned, $targetSurah, (int) $start['ayah'], $sequence);
                if ($endAyah < (int) $bounds['min'] || $endAyah > (int) $bounds['max']) {
                    $hint = $bounds['hint'] !== '' ? $bounds['hint'] : ('Ayahs ' . $bounds['min'] . '–' . $bounds['max']);

                    return ['success' => false, 'msg' => 'Ending ayah must be within the allowed para block (' . $hint . ').'];
                }
            }

            if (! $this->isValidMutaliaEnd($start, $endSurah, $endAyah, $sequence)) {
                return ['success' => false, 'msg' => 'Invalid Mutalia end ayah for this student\'s reading order.'];
            }

            $startSurah = (int) $start['surah_id'];
            $startAyah  = (int) $start['ayah'];

            if ($startSurah <= 0 || $startAyah <= 0 || $endSurah <= 0 || $endAyah <= 0) {
                return ['success' => false, 'msg' => 'Mutalia must have start surah/ayah and end surah/ayah.'];
            }

            $data['mutalia_surah_id_start'] = $startSurah;
            $data['mutalia_ayah_from']      = $startAyah;
            $data['mutalia_surah_id_end']   = $endSurah;
            $data['mutalia_ayah_to']        = $endAyah;
            $data['mutalia_remarks']        = trim((string) ($input['mutalia_remarks'] ?? '')) ?: null;
            unset($data['mutalia_lines_requested']);
        }

        $data = $this->filterDailyRecitationColumns($data);

        if ($existing) {
            $this->db->table('hifz_daily_recitation')
                ->where('id', (int) $existing->id)
                ->update($data);
        } else {
            $this->db->table('hifz_daily_recitation')->insert($data);
        }

        if ($recordManzil && ! empty($data['manzil_juz_list'])) {
            $this->advanceManzilRotation($enroll, $this->manzil->parseJuzList($data['manzil_juz_list']));
        }

        $label = 'Recitation saved.';
        if ($recordMutalia) {
            $label = 'Mutalia saved.';
        } elseif ($recordManzil) {
            $label = 'Manzil saved.';
        } elseif ($recordSabqi) {
            $label = 'Sabqi saved.';
        } elseif ($recordSabaq) {
            $label = hifzSabaqQualityRepeatsMutalia($data['sabaq_quality'] ?? '')
                ? 'Sabaq saved. Same Mutalia assigned for today.'
                : 'Sabaq saved.';
        }

        return ['success' => true, 'msg' => $label];
    }

    /**
     * @param list<int> $studentIds
     * @return array<int, array{calendar:array<string,mixed>}>
     */
    public function lastManzilHistoryForStudents(array $studentIds, int $sessionId): array
    {
        $map = [];
        foreach ($studentIds as $studentId) {
            $map[(int) $studentId] = [
                'calendar' => $this->buildManzilTwoWeekCalendar((int) $studentId, $sessionId),
            ];
        }

        return $map;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildManzilTwoWeekCalendar(int $studentId, int $sessionId): array
    {
        $today    = new \DateTimeImmutable('today');
        $monday   = $today->modify('monday this week');
        $lastMon  = $monday->modify('-7 days');
        $weeks    = [];
        $weekDefs = [
            ['label' => 'Last week', 'start' => $lastMon],
            ['label' => 'This week', 'start' => $monday],
        ];

        $dateFrom = $lastMon->format('Y-m-d');
        $dateTo   = $today->format('Y-m-d');

        $rows = $this->db->table('hifz_daily_recitation')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('recitation_date >=', $dateFrom)
            ->where('recitation_date <=', $dateTo)
            ->where('manzil_juz_list IS NOT NULL')
            ->where('manzil_juz_list !=', '')
            ->orderBy('recitation_date', 'ASC')
            ->get()
            ->getResult();

        $byDate = [];
        foreach ($rows as $row) {
            $byDate[$row->recitation_date] = $row;
        }

        $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        foreach ($weekDefs as $def) {
            $days = [];
            for ($i = 0; $i < 6; $i++) {
                $d       = $def['start']->modify('+' . $i . ' days');
                $dateKey = $d->format('Y-m-d');
                $row     = $byDate[$dateKey] ?? null;
                $entry   = null;

                if ($row) {
                    $juzList = $this->manzil->parseJuzList($row->manzil_juz_list ?? '');
                    $entry   = [
                        'juz_label'      => $this->manzil->formatParaLabel($juzList),
                        'listener_label' => $this->manzilListenerLabel($row),
                        'hard'           => (int) ($row->manzil_hard_mistakes ?? 0),
                        'soft'           => (int) ($row->manzil_soft_mistakes ?? 0),
                    ];
                }

                $days[] = [
                    'day'        => $dayNames[$i],
                    'date_label' => $d->format('j M'),
                    'date'       => $dateKey,
                    'is_today'   => $dateKey === $today->format('Y-m-d'),
                    'has_entry'  => $entry !== null,
                    'entry'      => $entry,
                ];
            }

            $weeks[] = [
                'label' => $def['label'],
                'days'  => $days,
            ];
        }

        return ['weeks' => $weeks];
    }

    public function manzilListenerLabel(object $row): string
    {
        $type = strtolower((string) ($row->manzil_listener_type ?? ''));

        if ($type === 'teacher') {
            return 'Teacher';
        }

        if ($type !== 'fellow') {
            return '—';
        }

        $fellowId = (int) ($row->manzil_listener_student_id ?? 0);
        if ($fellowId <= 0) {
            return 'Class fellow';
        }

        $s = $this->db->table('students')
            ->select('first_name, last_name')
            ->where('student_id', $fellowId)
            ->get()
            ->getRow();

        if (! $s) {
            return 'Class fellow';
        }

        $name = trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? ''));

        return $name !== '' ? $name : 'Class fellow';
    }

    /**
     * @param list<int> $pool
     * @param array<string, mixed> $calendar
     * @return array<string, mixed>
     */
    protected function buildManzilBlock(object $enrollment, ?object $rec, array $pool, array $calendar): array
    {
        $rotationIndex = (int) ($enrollment->manzil_rotation_index ?? 0);
        $parasPerDay   = max(1, min(3, (int) ($enrollment->manzil_paras_per_day ?? 1)));
        $suggested     = $this->manzil->suggestFromPool($pool, $rotationIndex, $parasPerDay);
        $savedJuz      = $rec ? $this->manzil->parseJuzList($rec->manzil_juz_list ?? '') : [];
        $juzList       = $savedJuz !== [] ? $savedJuz : $suggested['juz_list'];

        $poolParas = [];
        $catalog   = [];
        foreach (hifzJuzCatalog() as $item) {
            $catalog[(int) $item['juz_no']] = $item;
        }
        foreach ($pool as $juzNo) {
            $item = $catalog[$juzNo] ?? null;
            if ($item) {
                $poolParas[] = [
                    'juz_no'   => $juzNo,
                    'title'    => $item['title'] ?? ('Para ' . $juzNo),
                    'title_ar' => $item['title_ar'] ?? '',
                    'label'    => $item['title_ar'] ?? $item['title'] ?? ('Para ' . $juzNo),
                ];
            }
        }

        $block = [
            'juz_list'           => $juzList,
            'today_juz_list'     => $juzList,
            'suggested_juz_list' => $suggested['juz_list'],
            'label'              => $this->manzil->formatParaLabel($juzList),
            'pool_paras'         => $poolParas,
            'pool_juz_list'      => array_values($pool),
            'pool_auto'          => true,
            'calendar'           => $calendar,
            'listener_type'       => $rec ? (string) ($rec->manzil_listener_type ?? 'teacher') : 'teacher',
            'listener_student_id' => $rec ? (int) ($rec->manzil_listener_student_id ?? 0) : 0,
            'hard_mistakes'       => $rec ? (int) ($rec->manzil_hard_mistakes ?? 0) : 0,
            'soft_mistakes'       => $rec ? (int) ($rec->manzil_soft_mistakes ?? 0) : 0,
        ];

        if ($juzList !== []) {
            $block['ayahs'] = $this->ayahText->getAyahsForJuzList($juzList);
            $block['ayahs_ready'] = $this->ayahText->isReady();
            if (! $block['ayahs_ready']) {
                $block['ayahs_note'] = 'Run: php spark db:seed QuranAyahSeeder';
            }
        }

        return $block;
    }

    /**
     * Preview Mutalia ayahs after teacher changes start surah/ayah.
     *
     * @return array<string, mixed>
     */
    /**
     * Preview Mutalia Arabic text for start → end ayah on the enrollment surah.
     *
     * @return array<string, mixed>
     */
    public function previewMutaliaBlock(
        object $enrollment,
        int $endAyah,
        ?object $rec,
        string $date,
        int $sessionId,
        int $studentId
    ): array {
        $block = $this->buildMutaliaBlock($enrollment, $rec, $date, $sessionId, $studentId);

        if ($endAyah > 0) {
            $block['ayah_to']      = $endAyah;
            $block['surah_id_end'] = (int) $block['current_surah_id'];
            $this->refreshMutaliaAyahs($block, $enrollment);
        }

        return ['success' => true, 'mutalia' => $block];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildMutaliaBlock(
        object $enrollment,
        ?object $rec,
        string $date,
        int $sessionId,
        int $studentId
    ): array {
        $targetSurah = hifzMutaliaTargetSurah($enrollment);
        $chainStart  = $this->mutaliaStartPosition($studentId, $sessionId, $date, $enrollment);
        $startAyah   = $this->resolveMutaliaStartAyah($enrollment, $targetSurah, $chainStart);

        if ($rec && (int) ($rec->mutalia_surah_id_start ?? 0) > 0 && (int) ($rec->mutalia_ayah_from ?? 0) > 0) {
            $savedSurah = (int) $rec->mutalia_surah_id_start;
            if ($savedSurah === $targetSurah) {
                $startAyah = (int) $rec->mutalia_ayah_from;
            }
        }

        return $this->assembleMutaliaBlock($enrollment, $rec, $targetSurah, $startAyah, $chainStart);
    }

    /**
     * Start ayah on the target surah (next after yesterday, or juz-aware entry for a new surah).
     *
     * @param array{surah_id:int,ayah:int} $chainStart
     */
    protected function resolveMutaliaStartAyah(object $enrollment, int $targetSurah, array $chainStart): int
    {
        if ((int) ($chainStart['surah_id'] ?? 0) === $targetSurah) {
            return max(1, (int) ($chainStart['ayah'] ?? 1));
        }

        $sequence = (string) ($enrollment->memorization_sequence ?? 'para_forward');
        if (hifzIsSurahWiseSequence($sequence)) {
            $learned = (int) ($enrollment->reverse_learned_from_surah ?? 114);

            return hifzSurahReverseStartAyah($learned, $targetSurah, $sequence);
        }

        return 1;
    }

    /**
     * @param array{surah_id:int,ayah:int} $chainStart
     * @return array<string, mixed>
     */
    protected function assembleMutaliaBlock(
        object $enrollment,
        ?object $rec,
        int $targetSurah,
        int $startAyah,
        array $chainStart
    ): array {
        $layout      = hifzMushafLayoutCode();
        $linesPerDay = max(1, min(30, (int) ($enrollment->mutalia_lines_per_day ?? 3)));
        $sequence    = (string) ($enrollment->memorization_sequence ?? 'para_forward');
        $maxAyah     = (int) (QuranReference::$surahAyahCounts[$targetSurah - 1] ?? 0);
        $startAyah   = max(1, min($maxAyah, $startAyah));

        $ayahBounds = ['min' => 1, 'max' => $maxAyah, 'hint' => ''];
        if ($sequence === 'surah_reverse_full') {
            $learned    = (int) ($enrollment->reverse_learned_from_surah ?? 114);
            $ayahBounds = hifzSurahReverseAyahRange($learned, $targetSurah, 0, $sequence);
            $startAyah  = max((int) $ayahBounds['min'], min((int) $ayahBounds['max'], $startAyah));
        }

        $suggested = $this->lines->suggestedEndFromAyahStart($layout, $targetSurah, $startAyah, $linesPerDay);
        $endSurah  = $targetSurah;
        $endAyah   = $rec ? (int) ($rec->mutalia_ayah_to ?? 0) : 0;
        $endMismatch = false;

        if ($endAyah > 0 && ! $this->isValidMutaliaEnd(
            ['surah_id' => $targetSurah, 'ayah' => $startAyah],
            $endSurah,
            $endAyah,
            $sequence
        )) {
            $endAyah     = 0;
            $endMismatch = true;
        }

        if ($endAyah <= 0) {
            $endAyah = min((int) ($suggested['ayah_to'] ?? $startAyah), (int) $ayahBounds['max']);
            if ((int) ($suggested['surah_id_end'] ?? $targetSurah) !== $targetSurah) {
                $endAyah = min($startAyah + $linesPerDay - 1, (int) $ayahBounds['max']);
            }
        }

        $endAyah = max($startAyah, min((int) $ayahBounds['max'], $endAyah));

        $block = [
            'lines_per_day'       => $linesPerDay,
            'current_surah_id'    => $targetSurah,
            'current_surah_label' => hifzSurahName($targetSurah),
            'start_surah_id'      => $targetSurah,
            'start_ayah'          => $startAyah,
            'surah_id_end'        => $endSurah,
            'ayah_to'             => $endAyah,
            'suggested_ayah_to'   => min((int) ($suggested['ayah_to'] ?? $endAyah), (int) $ayahBounds['max']),
            'ayah_min'            => max($startAyah, (int) $ayahBounds['min']),
            'ayah_max'            => (int) $ayahBounds['max'],
            'ayah_hint'           => (string) ($ayahBounds['hint'] ?? ''),
            'learned_from_surah'  => (int) ($enrollment->reverse_learned_from_surah ?? 0),
            'label'               => $this->mutaliaRangeLabel($layout, $targetSurah, $startAyah, $endSurah, $endAyah),
            'remarks'             => $rec ? (string) ($rec->mutalia_remarks ?? '') : '',
            'auto'                => $rec === null || (int) ($rec->mutalia_ayah_to ?? 0) <= 0 || $endMismatch,
            'end_mismatch'        => $endMismatch,
        ];

        $this->attachMutaliaAyahs($block, $targetSurah, $startAyah, $endSurah, $endAyah);

        return $block;
    }

    /**
     * @param array<string, mixed> $block
     */
    protected function refreshMutaliaAyahs(array &$block, object $enrollment): void
    {
        $startSurah = (int) ($block['start_surah_id'] ?? 0);
        $startAyah  = (int) ($block['start_ayah'] ?? 0);
        $endSurah   = (int) ($block['surah_id_end'] ?? $startSurah);
        $endAyah    = max($startAyah, (int) ($block['ayah_to'] ?? 0));
        $maxAyah    = (int) ($block['ayah_max'] ?? 0);

        if ($maxAyah > 0) {
            $endAyah = min($maxAyah, $endAyah);
        }

        $block['ayah_to'] = $endAyah;
        $layout           = hifzMushafLayoutCode();
        $block['label']   = $this->mutaliaRangeLabel($layout, $startSurah, $startAyah, $endSurah, $endAyah);
        $this->attachMutaliaAyahs($block, $startSurah, $startAyah, $endSurah, $endAyah);
    }

    /**
     * @param array<string, mixed> $block
     */
    protected function attachMutaliaAyahs(
        array &$block,
        int $startSurah,
        int $startAyah,
        int $endSurah,
        int $endAyah
    ): void {
        if (! $this->ayahText->isReady()) {
            $block['ayahs_ready'] = false;
            $block['ayahs_note']  = 'Run: php spark db:seed QuranAyahSeeder';
            $block['ayahs']       = [];

            return;
        }

        if ($startSurah <= 0 || $startAyah <= 0 || $endSurah <= 0 || $endAyah < $startAyah) {
            $block['ayahs_ready'] = true;
            $block['ayahs']       = [];

            return;
        }

        $block['ayahs']           = $this->ayahText->getAyahsForRange($startSurah, $startAyah, $endSurah, $endAyah);
        $block['ayahs_ready']     = true;
        $block['ayahs_truncated'] = count($block['ayahs']) >= 120;
    }

    /**
     * @param array<string, mixed> $input
     * @return array{surah_id:int,ayah:int}
     */
    protected function resolveMutaliaStartForSave(
        int $studentId,
        int $sessionId,
        string $date,
        object $enrollment,
        array $input
    ): array {
        $targetSurah = hifzMutaliaTargetSurah($enrollment);
        $postSurah   = (int) ($input['mutalia_surah_id_start'] ?? 0);
        $postAyah    = (int) ($input['mutalia_ayah_from'] ?? 0);

        if ($postSurah > 0 && $postAyah > 0 && $postSurah === $targetSurah) {
            return ['surah_id' => $postSurah, 'ayah' => $postAyah];
        }

        $chainStart = $this->mutaliaStartPosition($studentId, $sessionId, $date, $enrollment);
        $startAyah  = $this->resolveMutaliaStartAyah($enrollment, $targetSurah, $chainStart);

        return ['surah_id' => $targetSurah, 'ayah' => $startAyah];
    }

    /**
     * @return array{surah_id:int,ayah:int}
     */
    protected function mutaliaStartPosition(int $studentId, int $sessionId, string $date, object $enrollment): array
    {
        $prev = $this->db->table('hifz_daily_recitation')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('recitation_date <', $date)
            ->where('mutalia_surah_id_end >', 0)
            ->where('mutalia_ayah_to >', 0)
            ->orderBy('recitation_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRow();

        if ($prev) {
            $endSurah = (int) $prev->mutalia_surah_id_end;
            $endAyah  = (int) $prev->mutalia_ayah_to;

            if ($this->previousMutaliaEndIsPlausible($enrollment, $endSurah)) {
                $learnedFrom = (int) ($enrollment->reverse_learned_from_surah ?? 0);

                return $this->nextMutaliaPosition(
                    $endSurah,
                    $endAyah,
                    (string) ($enrollment->memorization_sequence ?? 'para_forward'),
                    $learnedFrom
                );
            }
        }

        return $this->initialMutaliaPosition($enrollment);
    }

    /**
     * @return array{surah_id:int,ayah:int}
     */
    protected function initialMutaliaPosition(object $enrollment): array
    {
        $sequence  = (string) ($enrollment->memorization_sequence ?? 'para_forward');
        $counts    = QuranReference::$surahAyahCounts;
        $currentJuz = (int) ($enrollment->current_juz ?? 0);

        if (hifzIsSurahWiseSequence($sequence)) {
            $learnedFrom = (int) ($enrollment->reverse_learned_from_surah ?? 0);
            $curSurah    = $learnedFrom > 0 ? hifzSurahReverseCurrentSurah($learnedFrom) : (int) ($enrollment->current_sabaq_surah_id ?? 0);
            $curAyah     = (int) ($enrollment->current_sabaq_ayah ?? 0);

            if ($learnedFrom > 0 && $curSurah > 0) {
                if ($curAyah > 0) {
                    return $this->nextMutaliaPosition($curSurah, $curAyah, $sequence, $learnedFrom);
                }

                if ($sequence === 'surah_reverse_full') {
                    return [
                        'surah_id' => $curSurah,
                        'ayah'     => hifzSurahReverseStartAyah($learnedFrom, $curSurah, $sequence),
                    ];
                }

                return ['surah_id' => $curSurah, 'ayah' => 1];
            }
        }

        if ($currentJuz <= 0) {
            $progress   = hifzEnrollmentProgress($enrollment);
            $currentJuz = (int) ($progress['current_juz'] ?? 1);
        }

        $bounds = $this->juzBoundaryRow($currentJuz);
        if ($bounds) {
            $startSurah = (int) $bounds->start_surah_id;
            $endSurah   = (int) $bounds->end_surah_id;

            if ($sequence === 'surah_reverse_full') {
                return ['surah_id' => $endSurah, 'ayah' => 1];
            }

            if ($sequence === 'surah_reverse_ayah_reverse') {
                $max = (int) ($counts[$endSurah - 1] ?? 1);

                return ['surah_id' => $endSurah, 'ayah' => $max];
            }

            return [
                'surah_id' => $startSurah,
                'ayah'     => (int) $bounds->start_ayah,
            ];
        }

        if ($sequence === 'surah_reverse_full') {
            return ['surah_id' => 114, 'ayah' => 1];
        }

        if ($sequence === 'surah_reverse_ayah_reverse') {
            $max = (int) ($counts[113] ?? 6);

            return ['surah_id' => 114, 'ayah' => $max];
        }

        return ['surah_id' => 1, 'ayah' => 1];
    }

    protected function previousMutaliaEndIsPlausible(object $enrollment, int $endSurah): bool
    {
        if ($endSurah <= 0) {
            return false;
        }

        $sequence = (string) ($enrollment->memorization_sequence ?? 'para_forward');
        if ($sequence === 'para_forward') {
            return true;
        }

        $currentJuz = (int) ($enrollment->current_juz ?? 0);
        if ($currentJuz <= 0) {
            $progress   = hifzEnrollmentProgress($enrollment);
            $currentJuz = (int) ($progress['current_juz'] ?? 0);
        }

        if ($currentJuz <= 0) {
            return true;
        }

        if ($endSurah >= 100 && $currentJuz <= 25) {
            return false;
        }

        $bounds = $this->juzBoundaryRow($currentJuz);
        if (! $bounds) {
            return true;
        }

        $juzStart = (int) $bounds->start_surah_id;
        $juzEnd   = (int) $bounds->end_surah_id;

        return $endSurah >= $juzStart - 1 && $endSurah <= $juzEnd + 1;
    }

    protected function juzBoundaryRow(int $juzNo): ?object
    {
        if ($juzNo < 1 || $juzNo > 30) {
            return null;
        }

        return $this->db->table('quran_juz_boundaries')
            ->where('juz_no', $juzNo)
            ->get()
            ->getRow();
    }

    /**
     * @return array{surah_id:int,ayah:int}
     */
    protected function nextMutaliaPosition(int $surahId, int $ayah, string $sequence, int $learnedFrom = 0): array
    {
        $counts = QuranReference::$surahAyahCounts;
        $max    = (int) ($counts[$surahId - 1] ?? 0);

        if ($sequence === 'surah_reverse_ayah_reverse') {
            if ($ayah > 1) {
                return ['surah_id' => $surahId, 'ayah' => $ayah - 1];
            }
            if ($surahId > 1) {
                $prevMax = (int) ($counts[$surahId - 2] ?? 1);

                return ['surah_id' => $surahId - 1, 'ayah' => $prevMax];
            }

            return ['surah_id' => 1, 'ayah' => 1];
        }

        if ($sequence === 'surah_reverse_full') {
            if ($learnedFrom > 0) {
                return hifzSurahReverseNextPosition($learnedFrom, $surahId, $ayah, $sequence);
            }

            if ($ayah < $max) {
                return ['surah_id' => $surahId, 'ayah' => $ayah + 1];
            }
            if ($surahId > 1) {
                return ['surah_id' => $surahId - 1, 'ayah' => 1];
            }

            return ['surah_id' => 1, 'ayah' => 1];
        }

        if ($ayah < $max) {
            return ['surah_id' => $surahId, 'ayah' => $ayah + 1];
        }
        if ($surahId < 114) {
            return ['surah_id' => $surahId + 1, 'ayah' => 1];
        }

        return ['surah_id' => 114, 'ayah' => $max];
    }

    /**
     * @param array{surah_id:int,ayah:int} $start
     */
    protected function isValidMutaliaEnd(array $start, int $endSurah, int $endAyah, string $sequence): bool
    {
        $pos   = $start;
        $guard = 0;

        while ($guard++ < 6000) {
            if ((int) $pos['surah_id'] === $endSurah && (int) $pos['ayah'] === $endAyah) {
                return true;
            }

            $pos = $this->nextMutaliaPosition((int) $pos['surah_id'], (int) $pos['ayah'], $sequence);

            if ($this->mutaliaPositionsEqual($pos, $start)) {
                break;
            }
        }

        return false;
    }

    /**
     * @param array{surah_id:int,ayah:int} $a
     * @param array{surah_id:int,ayah:int} $b
     */
    protected function mutaliaPositionsEqual(array $a, array $b): bool
    {
        return (int) $a['surah_id'] === (int) $b['surah_id'] && (int) $a['ayah'] === (int) $b['ayah'];
    }

    public function sabaqLessonLabelForRow(object $row): string
    {
        $lesson = $this->findMutaliaBeforeDate(
            (int) ($row->student_id ?? 0),
            (int) ($row->session_id ?? 0),
            (string) ($row->recitation_date ?? date('Y-m-d'))
        );

        if (! $lesson) {
            return '—';
        }

        $enroll = $this->db->table('hifz_students')
            ->where('student_id', (int) ($row->student_id ?? 0))
            ->where('session_id', (int) ($row->session_id ?? 0))
            ->where('status', 1)
            ->get()
            ->getRow();

        if (! $enroll) {
            return $this->mutaliaLabelForSavedRow($lesson);
        }

        $stored = $this->mutaliaRangeFromRecStored($lesson);

        if ($stored !== null) {
            return $this->mutaliaRangeLabel(
                hifzMushafLayoutCode(),
                (int) $stored['surah_id'],
                (int) $stored['ayah'],
                (int) $stored['surah_id_end'],
                (int) $stored['ayah_to']
            );
        }

        $range = $this->mutaliaRangeFromRec($lesson, $enroll);

        return $this->mutaliaRangeLabel(
            hifzMushafLayoutCode(),
            (int) $range['surah_id'],
            (int) $range['ayah'],
            (int) $range['surah_id_end'],
            (int) $range['ayah_to']
        );
    }

    public function mutaliaLabelForSavedRow(object $row): string
    {
        $endSurah = (int) ($row->mutalia_surah_id_end ?? 0);
        $endAyah  = (int) ($row->mutalia_ayah_to ?? 0);

        if ($endSurah <= 0 || $endAyah <= 0) {
            return '—';
        }

        $enroll = $this->db->table('hifz_students')
            ->where('student_id', (int) ($row->student_id ?? 0))
            ->where('session_id', (int) ($row->session_id ?? 0))
            ->where('status', 1)
            ->get()
            ->getRow();

        if (! $enroll) {
            return sprintf('Surah %d · Ayah %d', $endSurah, $endAyah);
        }

        $stored = $this->mutaliaRangeFromRecStored($row);

        if ($stored !== null) {
            return $this->mutaliaRangeLabel(
                hifzMushafLayoutCode(),
                (int) $stored['surah_id'],
                (int) $stored['ayah'],
                (int) $stored['surah_id_end'],
                (int) $stored['ayah_to']
            );
        }

        $start = $this->mutaliaStartPosition(
            (int) $row->student_id,
            (int) $row->session_id,
            (string) ($row->recitation_date ?? date('Y-m-d')),
            $enroll
        );

        return $this->mutaliaRangeLabel(
            hifzMushafLayoutCode(),
            (int) $start['surah_id'],
            (int) $start['ayah'],
            $endSurah,
            $endAyah
        );
    }

    protected function mutaliaRangeLabel(
        string $layout,
        int $startSurah,
        int $startAyah,
        int $endSurah,
        int $endAyah
    ): string {
        if ($startSurah <= 0 || $endSurah <= 0) {
            return '—';
        }

        $range = $this->lines->snapAyahRange($layout, $startSurah, $startAyah, $endSurah, $endAyah, 0);

        return $this->lines->formatRangeLabel($range);
    }

    protected function formatBlock(array $range, int $linesRequested): array
    {
        return [
            'line_from'       => (int) ($range['line_from'] ?? 0),
            'line_to'         => (int) ($range['line_to'] ?? 0),
            'lines_requested' => $linesRequested > 0 ? $linesRequested : (int) ($range['lines_requested'] ?? 0),
            'label'           => $this->lines->formatRangeLabel($range),
            'quality'         => '',
            'remarks'         => '',
        ];
    }

    /**
     * @param array<string, mixed> $block
     */
    protected function attachAyahsForRange(array &$block): void
    {
        if (! $this->ayahText->isReady()) {
            $block['ayahs_ready'] = false;
            $block['ayahs_note']  = 'Run: php spark db:seed QuranAyahSeeder';

            return;
        }

        $layout = hifzMushafLayoutCode();
        $from   = (int) ($block['line_from'] ?? 0);
        $to     = (int) ($block['line_to'] ?? 0);

        if ($from <= 0 || $to <= 0) {
            $block['ayahs_ready'] = true;
            $block['ayahs']       = [];

            return;
        }

        $range = $this->lines->snapRangeToFullAyahs($layout, $from, $to, 0);
        $block['ayahs'] = $this->ayahText->getAyahsForRange(
            (int) $range['surah_id_start'],
            (int) $range['ayah_from'],
            (int) $range['surah_id_end'],
            (int) $range['ayah_to']
        );
        $block['ayahs_ready']     = true;
        $block['ayahs_truncated'] = count($block['ayahs']) >= 80;
    }

    protected function findMutaliaBeforeDate(int $studentId, int $sessionId, string $date): ?object
    {
        return $this->db->table('hifz_daily_recitation')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('recitation_date <', $date)
            ->where('mutalia_surah_id_start >', 0)
            ->where('mutalia_ayah_from >', 0)
            ->where('mutalia_surah_id_end >', 0)
            ->where('mutalia_ayah_to >', 0)
            ->orderBy('recitation_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRow();
    }

    /**
     * @return array{surah_id:int,ayah:int,surah_id_end:int,ayah_to:int}|null
     */
    protected function mutaliaRangeFromRecStored(object $rec): ?array
    {
        $startSurah = (int) ($rec->mutalia_surah_id_start ?? 0);
        $startAyah  = (int) ($rec->mutalia_ayah_from ?? 0);
        $endSurah   = (int) ($rec->mutalia_surah_id_end ?? 0);
        $endAyah    = (int) ($rec->mutalia_ayah_to ?? 0);

        if ($startSurah <= 0 || $startAyah <= 0 || $endSurah <= 0 || $endAyah <= 0) {
            return null;
        }

        return [
            'surah_id'     => $startSurah,
            'ayah'         => $startAyah,
            'surah_id_end' => $endSurah,
            'ayah_to'      => $endAyah,
        ];
    }

    /**
     * @return array{surah_id:int,ayah:int,surah_id_end:int,ayah_to:int}
     */
    protected function mutaliaRangeFromRec(object $rec, object $enrollment): array
    {
        $stored = $this->mutaliaRangeFromRecStored($rec);

        if ($stored !== null) {
            return $stored;
        }

        $endSurah   = (int) ($rec->mutalia_surah_id_end ?? 0);
        $endAyah    = (int) ($rec->mutalia_ayah_to ?? 0);
        $chain      = $this->mutaliaStartPosition(
            (int) $rec->student_id,
            (int) $rec->session_id,
            (string) ($rec->recitation_date ?? date('Y-m-d')),
            $enrollment
        );
        $startSurah = (int) ($chain['surah_id'] ?? $endSurah);
        $startAyah  = (int) ($chain['ayah'] ?? 1);

        return [
            'surah_id'     => $startSurah,
            'ayah'         => $startAyah,
            'surah_id_end' => $endSurah,
            'ayah_to'      => $endAyah,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildSabaqBlock(?object $lessonMutalia, ?object $todayRec, object $enrollment): array
    {
        if (! $lessonMutalia) {
            return [
                'ready'       => false,
                'note'        => 'No Mutalia was assigned on a previous day. Use the Mutalia tab to assign the first lesson.',
                'label'       => '—',
                'quality'     => '',
                'remarks'     => '',
                'lesson_date' => '',
                'ayahs'       => [],
                'ayahs_ready' => true,
            ];
        }

        $range = $this->mutaliaRangeFromRecStored($lessonMutalia)
            ?? $this->mutaliaRangeFromRec($lessonMutalia, $enrollment);
        $layout = hifzMushafLayoutCode();

        $block = [
            'ready'              => true,
            'lesson_date'        => (string) ($lessonMutalia->recitation_date ?? ''),
            'start_surah_id'     => (int) $range['surah_id'],
            'start_ayah'         => (int) $range['ayah'],
            'surah_id_end'       => (int) $range['surah_id_end'],
            'ayah_to'            => (int) $range['ayah_to'],
            'start_surah_label'  => hifzSurahName((int) $range['surah_id']),
            'end_surah_label'    => hifzSurahName((int) $range['surah_id_end']),
            'range_summary'      => sprintf(
                '%s · Ayah %d → %s · Ayah %d',
                hifzSurahName((int) $range['surah_id'], false),
                (int) $range['ayah'],
                hifzSurahName((int) $range['surah_id_end'], false),
                (int) $range['ayah_to']
            ),
            'label'          => $this->mutaliaRangeLabel(
                $layout,
                (int) $range['surah_id'],
                (int) $range['ayah'],
                (int) $range['surah_id_end'],
                (int) $range['ayah_to']
            ),
            'quality'        => $todayRec ? (string) ($todayRec->sabaq_quality ?? '') : '',
            'remarks'        => $todayRec ? (string) ($todayRec->sabaq_remarks ?? '') : '',
            'note'           => 'Lesson from Mutalia (' . ($lessonMutalia->recitation_date ?? '') . '). Student memorized these ayahs; listen and rate quality only.',
        ];

        $this->attachAyahsForSurahRange($block);

        return $block;
    }

    /**
     * @return array{allowed:bool,reason:string,locked:bool,locked_reason:string}
     */
    protected function mutaliaEntryState(?object $lessonMutalia, ?object $todayRec, string $date): array
    {
        $todayHasMutalia = $todayRec
            && (int) ($todayRec->mutalia_surah_id_end ?? 0) > 0
            && (int) ($todayRec->mutalia_ayah_to ?? 0) > 0;

        $sabaqQuality = strtolower(trim((string) ($todayRec->sabaq_quality ?? '')));

        if ($todayHasMutalia && hifzSabaqQualityRepeatsMutalia($sabaqQuality)) {
            return [
                'allowed'        => false,
                'reason'         => 'Same Mutalia was assigned automatically after Weak Sabaq.',
                'locked'         => true,
                'locked_reason'  => 'Assigned automatically — student repeats yesterday\'s Mutalia.',
            ];
        }

        if (! $lessonMutalia) {
            return [
                'allowed'       => true,
                'reason'        => '',
                'locked'        => false,
                'locked_reason' => '',
            ];
        }

        if ($sabaqQuality === '') {
            return [
                'allowed'       => false,
                'reason'        => 'Save Sabaq first (Excellent, Good, or Average) before assigning new Mutalia.',
                'locked'        => false,
                'locked_reason' => '',
            ];
        }

        if (! hifzSabaqQualityAllowsNewMutalia($sabaqQuality)) {
            $msg = hifzSabaqQualityRepeatsMutalia($sabaqQuality)
                ? 'Weak Sabaq — same Mutalia is already assigned for today.'
                : 'New Mutalia is only entered after Excellent, Good, or Average Sabaq.';

            return [
                'allowed'       => false,
                'reason'        => $msg,
                'locked'        => $todayHasMutalia,
                'locked_reason' => $todayHasMutalia ? $msg : '',
            ];
        }

        return [
            'allowed'       => true,
            'reason'        => '',
            'locked'        => false,
            'locked_reason' => '',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function applyMutaliaRangeToData(array &$data, object $source, object $enrollment): void
    {
        $range = $this->mutaliaRangeFromRecStored($source)
            ?? $this->mutaliaRangeFromRec($source, $enrollment);

        $data['mutalia_surah_id_start'] = (int) $range['surah_id'];
        $data['mutalia_ayah_from']      = (int) $range['ayah'];
        $data['mutalia_surah_id_end']   = (int) $range['surah_id_end'];
        $data['mutalia_ayah_to']        = (int) $range['ayah_to'];
        unset($data['mutalia_lines_requested']);
    }

    /**
     * @param array<string, mixed> $block
     */
    protected function attachAyahsForSurahRange(array &$block): void
    {
        if (! $this->ayahText->isReady()) {
            $block['ayahs_ready'] = false;
            $block['ayahs_note']  = 'Run: php spark db:seed QuranAyahSeeder';
            $block['ayahs']       = [];

            return;
        }

        $startSurah = (int) ($block['start_surah_id'] ?? 0);
        $startAyah  = (int) ($block['start_ayah'] ?? 0);
        $endSurah   = (int) ($block['surah_id_end'] ?? 0);
        $endAyah    = (int) ($block['ayah_to'] ?? 0);

        if ($startSurah <= 0 || $startAyah <= 0 || $endSurah <= 0 || $endAyah < $startAyah) {
            $block['ayahs_ready'] = true;
            $block['ayahs']       = [];

            return;
        }

        $block['ayahs']           = $this->ayahText->getAyahsForRange($startSurah, $startAyah, $endSurah, $endAyah);
        $block['ayahs_ready']     = true;
        $block['ayahs_truncated'] = count($block['ayahs']) >= 120;
    }

    /**
     * @param array<string, mixed> $block
     * @return array<string, mixed>
     */
    protected function mergeSavedSabqi(array $block, object $rec, string $layout): array
    {
        $from = (int) ($rec->sabqi_line_from ?? 0);
        $to   = (int) ($rec->sabqi_line_to ?? 0);
        if ($from <= 0 || $to <= 0) {
            return $block;
        }

        $range = $this->lines->snapRangeToFullAyahs($layout, $from, $to, 0);
        $block = $this->formatBlock($range, 0);
        $block['auto']    = (int) ($rec->sabqi_auto_generated ?? 1) === 1;
        $block['quality'] = (string) ($rec->sabqi_quality ?? '');
        $block['remarks'] = (string) ($rec->sabqi_remarks ?? '');

        return $block;
    }

    /**
     * @param array<string, mixed> $block
     * @return array<string, mixed>
     */
    protected function mergeSavedMutalia(
        array $block,
        object $rec,
        object $enrollment,
        string $date,
        int $sessionId,
        int $studentId
    ): array {
        $endAyah = (int) ($rec->mutalia_ayah_to ?? 0);

        if ($endAyah <= 0) {
            return $block;
        }

        $startSurah = (int) ($rec->mutalia_surah_id_start ?? 0);
        $startAyah  = (int) ($rec->mutalia_ayah_from ?? 0);

        if ($startSurah > 0 && $startAyah > 0) {
            $block['start_surah_id'] = $startSurah;
            $block['start_ayah']     = $startAyah;
        }

        $block['surah_id_end'] = (int) ($rec->mutalia_surah_id_end ?? $block['current_surah_id'] ?? 0);
        $block['ayah_to']      = $endAyah;
        $block['auto']         = false;
        $block['remarks']      = (string) ($rec->mutalia_remarks ?? '');
        $this->refreshMutaliaAyahs($block, $enrollment);

        return $block;
    }

    protected function ayahPositionAfter(
        int $surahA,
        int $ayahA,
        int $surahB,
        int $ayahB,
        string $sequence
    ): bool {
        $pos = ['surah_id' => $surahA, 'ayah' => $ayahA];
        $guard = 0;

        while ($guard++ < 6000) {
            $pos = $this->nextMutaliaPosition((int) $pos['surah_id'], (int) $pos['ayah'], $sequence);
            if ((int) $pos['surah_id'] === $surahB && (int) $pos['ayah'] === $ayahB) {
                return true;
            }
            if ($this->mutaliaPositionsEqual($pos, ['surah_id' => $surahA, 'ayah' => $ayahA])) {
                break;
            }
        }

        return false;
    }

    protected function advanceCursorAfterSabaq(
        int $studentId,
        int $sessionId,
        int $newLineTo,
        string $layout,
        ?object $existing
    ): void {
        if ($existing && (int) ($existing->sabaq_line_to ?? 0) === $newLineTo) {
            return;
        }

        $stats = $this->sabqi->cursorStats($layout, $newLineTo);

        $this->db->table('hifz_students')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->update([
                'current_global_line'         => $newLineTo,
                'current_juz'                 => (int) ($stats['current_juz'] ?? 1),
                'current_juz_memorized_lines' => (int) ($stats['current_juz_memorized_lines'] ?? 0),
                'updated_date'                => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * @param list<int> $savedJuz
     */
    protected function advanceManzilRotation(object $enrollment, array $savedJuz): void
    {
        $pool = hifzAutoManzilPool($enrollment);

        if ($pool === []) {
            return;
        }

        $parasPerDay = max(1, min(3, (int) ($enrollment->manzil_paras_per_day ?? 1)));
        $newIndex    = ((int) ($enrollment->manzil_rotation_index ?? 0) + count($savedJuz)) % count($pool);

        $this->db->table('hifz_students')
            ->where('id', (int) $enrollment->id)
            ->update([
                'manzil_rotation_index' => $newIndex,
                'updated_date'          => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rowToArray(object $row): array
    {
        return (array) $row;
    }

    /**
     * Drop keys that are not real columns (avoids save errors when migrations lag on live).
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function filterDailyRecitationColumns(array $data): array
    {
        if (! $this->db->tableExists('hifz_daily_recitation')) {
            return $data;
        }

        $allowed = array_flip($this->db->getFieldNames('hifz_daily_recitation'));

        return array_intersect_key($data, $allowed);
    }
}
