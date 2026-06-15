<?php

namespace App\Libraries;

use Config\Database;

/**
 * Para-only Hifz state and daily log persistence (4 independent tables).
 */
class HifzProgressService
{
    protected $db;
    protected HifzManzilCalculator $manzil;

    public function __construct()
    {
        helper('hifz');
        hifz_ensure_database_schema();

        $this->db     = Database::connect();
        $this->manzil = new HifzManzilCalculator();
    }

    /**
     * @return array{current_para:int,sabqi_paras:list<int>,manzil_pool:list<int>,manzil_paras_per_day:int,manzil_rotation_index:int}
     */
    public function studentState(object $enrollment): array
    {
        $current = (int) ($enrollment->current_para_no ?? 0);
        if ($current <= 0) {
            $current = max(1, min(30, (int) ($enrollment->current_juz ?? 1)));
        }

        $linesDone = max(0, min(hifzParaTotalLines(), (int) ($enrollment->current_juz_memorized_lines ?? 0)));
        $sequence  = (string) ($enrollment->memorization_sequence ?? 'para_forward');
        $sabqi     = $this->resolveSabqiParas($enrollment, $current, $linesDone, $sequence);
        $pool      = hifzParseJuzList($enrollment->manzil_pool_paras ?? '');

        return [
            'current_para'            => $current,
            'lines_done_in_para'      => $linesDone,
            'para_total_lines'        => hifzParaTotalLines(),
            'memorization_sequence'   => $sequence,
            'sabqi_paras'             => $sabqi,
            'manzil_pool'             => $pool,
            'manzil_paras_per_day'    => max(1, min(3, (int) ($enrollment->manzil_paras_per_day ?? 1))),
            'manzil_rotation_index'   => (int) ($enrollment->manzil_rotation_index ?? 0),
            'mutalia_lines_per_day'   => max(1, min(30, (int) ($enrollment->mutalia_lines_per_day ?? 3))),
        ];
    }

    /**
     * Sabqi stack from plan rules: reverse/forward and lines in current para (&lt; or &ge; 160).
     * Excludes paras moved to the Manzil pool.
     *
     * @return list<int>
     */
    public function resolveSabqiParas(object $enrollment, int $currentPara, int $linesDone, ?string $sequence = null): array
    {
        $currentPara = max(1, min(30, $currentPara));
        $linesDone   = max(0, min(hifzParaTotalLines(), $linesDone));
        $sequence    = $sequence ?? (string) ($enrollment->memorization_sequence ?? 'para_forward');

        $pools      = hifzComputeEnrollmentPools($sequence, $currentPara, $linesDone);
        $sabqi      = $pools['sabqi_paras'];
        $manzilPool = hifzParseJuzList($enrollment->manzil_pool_paras ?? '');

        $sabqi = array_values(array_filter(
            $sabqi,
            static fn ($p) => ! in_array($p, $manzilPool, true)
        ));

        if (! in_array($currentPara, $sabqi, true)) {
            $sabqi[] = $currentPara;
        }

        sort($sabqi);

        return array_values(array_filter($sabqi, static fn ($p) => $p >= 1 && $p <= 30));
    }

    /**
     * Default Mutalia line input: latest log before $beforeDate, else campus daily default.
     */
    public function mutaliaDefaultLines(object $enrollment, int $studentId, string $beforeDate, int $linesRemaining): int
    {
        $linesRemaining = max(0, min(hifzParaTotalLines(), $linesRemaining));
        $fallback       = max(1, min(30, (int) ($enrollment->mutalia_lines_per_day ?? 3)));
        $prior          = $this->findLessonBeforeDate($studentId, $beforeDate);
        $base           = $prior ? max(1, (int) ($prior->lines_count ?? 0)) : $fallback;

        if ($linesRemaining <= 0) {
            return 1;
        }

        return max(1, min($linesRemaining, $base));
    }

    /**
     * Persist sabqi_active_paras when it no longer matches line-based rules.
     */
    public function syncSabqiActiveParasIfNeeded(object $enrollment, int $currentPara, int $linesDone): void
    {
        $sequence = (string) ($enrollment->memorization_sequence ?? 'para_forward');
        $resolved = $this->resolveSabqiParas($enrollment, $currentPara, $linesDone, $sequence);
        $stored   = hifzParseJuzList($enrollment->sabqi_active_paras ?? '');

        if ($stored === $resolved) {
            return;
        }

        $this->db->table('hifz_students')
            ->where('id', (int) $enrollment->id)
            ->update([
                'sabqi_active_paras' => hifzFormatJuzList($resolved) ?: null,
                'updated_date'       => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Sabqi stack always includes current para plus any prior paras still in revision.
     *
     * @param list<int> $sabqi
     * @return list<int>
     */
    public function normalizeSabqiStack(array $sabqi, int $currentPara): array
    {
        $sabqi = hifzParseJuzList(hifzFormatJuzList($sabqi));
        $currentPara = max(1, min(30, $currentPara));

        if (! in_array($currentPara, $sabqi, true)) {
            $sabqi[] = $currentPara;
        }

        sort($sabqi);

        return array_values(array_filter($sabqi, static fn ($p) => $p >= 1 && $p <= 30));
    }

    /**
     * @param array<string, mixed> $input
     * @return array{success:bool,msg:string}
     */
    public function saveMutalia(
        object $enrollment,
        int $studentId,
        int $campusId,
        int $sessionId,
        int $hifzSecId,
        int $teacherId,
        int $userId,
        string $date,
        array $input
    ): array {
        $linesToday = (int) ($input['mutalia_lines'] ?? $input['lines_count'] ?? 0);

        if ($linesToday <= 0) {
            return ['success' => false, 'msg' => 'Enter the number of lines for Mutalia.'];
        }

        $state         = $this->studentState($enrollment);
        $paraNo        = $state['current_para'];
        $totalLines    = hifzParaTotalLines();
        $linesDone     = $state['lines_done_in_para'];
        $sequence      = $state['memorization_sequence'];
        $existing      = $this->lessonLogForDate($studentId, $date);
        $oldTodayLines = $existing ? (int) ($existing->lines_count ?? 0) : 0;
        $remaining     = max(0, $totalLines - ($linesDone - $oldTodayLines));

        if ($existing) {
            return ['success' => false, 'msg' => 'Mutalia already added for this date and cannot be changed.'];
        }

        $prior = $this->findLessonBeforeDate($studentId, $date);
        if ($prior) {
            $priorQuality = strtolower(trim((string) ($prior->sabaq_quality ?? '')));
            if ($priorQuality === '') {
                return ['success' => false, 'msg' => 'Save Sabaq for the previous lesson (Excellent, Good, or Average) before new Mutalia.'];
            }
            if (! hifzSabaqQualityAllowsNewMutalia($priorQuality)) {
                return ['success' => false, 'msg' => 'Enter new Mutalia only after Excellent, Good, or Average Sabaq.'];
            }
        }

        $clamped = false;
        if ($linesToday > $remaining) {
            if ($remaining <= 0) {
                return ['success' => false, 'msg' => 'No lines remaining in this para (320 lines complete).'];
            }
            $linesToday = $remaining;
            $clamped    = true;
        }

        if ($linesToday <= 0) {
            return ['success' => false, 'msg' => 'No lines remaining in this para.'];
        }

        $newLinesDone = min($totalLines, ($linesDone - $oldTodayLines) + $linesToday);
        $lineFrom     = max(1, ($linesDone - $oldTodayLines) + 1);
        $lineTo       = min($totalLines, $lineFrom + $linesToday - 1);

        $autoComplete = $newLinesDone >= $totalLines;
        $paraCompleted = $autoComplete ? 1 : 0;
        $startNew      = 0;
        $newParaNo     = null;

        if ($autoComplete) {
            $newParaNo = hifzNextParaNo($paraNo, $sequence);
            if ($newParaNo !== $paraNo) {
                $startNew = 1;
            }
        }

        $now  = date('Y-m-d H:i:s');
        $data = [
            'student_id'       => $studentId,
            'session_id'       => $sessionId,
            'campus_id'        => $campusId,
            'hifz_sec_id'      => $hifzSecId,
            'teacher_id'       => $teacherId,
            'entry_date'       => $date,
            'para_no'          => $paraNo,
            'lines_count'      => $linesToday,
            'line_from'        => $lineFrom,
            'line_to'          => $lineTo,
            'para_completed'   => $paraCompleted,
            'new_para_started' => $startNew,
            'new_para_no'      => $startNew ? $newParaNo : null,
            'remarks'          => trim((string) ($input['mutalia_remarks'] ?? '')) ?: null,
            'updated_date'     => $now,
            'user_id'          => $userId,
        ];

        $data['created_date'] = $now;
        $this->db->table('hifz_mutalia_logs')->insert($data);

        if ($startNew) {
            $this->appendCompletedPara($enrollment, $paraNo);
            $this->applyStartNewPara($enrollment, $paraNo, (int) $newParaNo);
            $enrollment = $this->reloadEnrollment((int) $enrollment->id) ?? $enrollment;
            $this->syncSabqiActiveParasIfNeeded($enrollment, (int) $newParaNo, 0);

            $msg = sprintf(
                'Mutalia saved. Para %d complete (320 lines) — now on Para %d. Grade Sabaq when ready.',
                $paraNo,
                (int) $newParaNo
            );
            if ($clamped) {
                $msg = sprintf(
                    'Mutalia saved (%d lines, max for this para). Para %d complete — now on Para %d. Grade Sabaq when ready.',
                    $linesToday,
                    $paraNo,
                    (int) $newParaNo
                );
            }

            return ['success' => true, 'msg' => $msg];
        }

        $enrollment = $this->reloadEnrollment((int) $enrollment->id) ?? $enrollment;

        $this->db->table('hifz_students')
            ->where('id', (int) $enrollment->id)
            ->update([
                'current_juz_memorized_lines' => $newLinesDone,
                'updated_date'                => $now,
            ]);

        $enrollment = $this->reloadEnrollment((int) $enrollment->id) ?? $enrollment;
        $this->syncSabqiActiveParasIfNeeded($enrollment, $paraNo, $newLinesDone);

        $msg = 'Mutalia added. Listen to Sabaq below and assign a grade.';
        if ($clamped) {
            $msg = sprintf(
                'Mutalia added (%d lines — maximum remaining for this para). Listen to Sabaq below and assign a grade.',
                $linesToday
            );
        }

        return ['success' => true, 'msg' => $msg];
    }

    protected function appendCompletedPara(object $enrollment, int $paraNo): void
    {
        $paraNo = max(1, min(30, $paraNo));
        $list   = hifzParseJuzList($enrollment->completed_juz_list ?? '');

        if (in_array($paraNo, $list, true)) {
            return;
        }

        $list[] = $paraNo;
        sort($list);

        $this->db->table('hifz_students')
            ->where('id', (int) $enrollment->id)
            ->update([
                'completed_juz_list' => hifzFormatJuzList($list) ?: null,
                'updated_date'       => date('Y-m-d H:i:s'),
            ]);
    }

    protected function applyStartNewPara(object $enrollment, int $oldPara, int $newPara): void
    {
        $sequence = (string) ($enrollment->memorization_sequence ?? 'para_forward');
        $newPara  = max(1, min(30, $newPara));
        $pools    = hifzComputeEnrollmentPools($sequence, $newPara, 0);
        $sabqi    = $this->resolveSabqiParas($enrollment, $newPara, 0, $sequence);

        $this->db->table('hifz_students')
            ->where('id', (int) $enrollment->id)
            ->update([
                'current_para_no'             => $newPara,
                'current_juz'                 => $newPara,
                'current_juz_memorized_lines' => 0,
                'sabqi_active_paras'          => hifzFormatJuzList($sabqi) ?: null,
                'manzil_pool_paras'           => hifzFormatJuzList($pools['manzil_pool_paras']) ?: null,
                'updated_date'                => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * @param array<string, mixed> $input
     * @return array{success:bool,msg:string}
     */
    public function saveSabaq(
        object $enrollment,
        int $studentId,
        int $campusId,
        int $sessionId,
        int $hifzSecId,
        int $teacherId,
        int $userId,
        string $date,
        array $input
    ): array {
        $quality = strtolower(trim((string) ($input['sabaq_quality'] ?? '')));

        if ($quality === '') {
            return ['success' => false, 'msg' => 'Select Sabaq quality.'];
        }

        $lesson = $this->findLessonBeforeDate($studentId, $date);

        if (! $lesson) {
            return ['success' => false, 'msg' => 'No Mutalia lesson found for the previous day. Assign Mutalia first.'];
        }

        $listenerType = strtolower(trim((string) ($input['sabaq_listener_type'] ?? 'teacher')));
        if (! in_array($listenerType, ['teacher', 'fellow'], true)) {
            $listenerType = 'teacher';
        }
        $fellowId = (int) ($input['sabaq_listener_student_id'] ?? 0);
        if ($listenerType === 'fellow' && $fellowId <= 0) {
            return ['success' => false, 'msg' => 'Select the class fellow who listened.'];
        }

        $now  = date('Y-m-d H:i:s');
        $this->db->table('hifz_mutalia_logs')
            ->where('id', (int) $lesson->id)
            ->update([
                'sabaq_date'                => $date,
                'sabaq_quality'             => $quality,
                'sabaq_remarks'             => trim((string) ($input['sabaq_remarks'] ?? '')) ?: null,
                'sabaq_hard_mistakes'       => max(0, (int) ($input['sabaq_hard_mistakes'] ?? 0)),
                'sabaq_soft_mistakes'       => max(0, (int) ($input['sabaq_soft_mistakes'] ?? 0)),
                'sabaq_listener_type'       => $listenerType,
                'sabaq_listener_student_id' => $listenerType === 'fellow' ? $fellowId : null,
                'updated_date'              => $now,
                'user_id'                   => $userId,
            ]);

        if (hifzSabaqQualityRepeatsMutalia($quality)) {
            $this->autoRepeatLessonFromPrior($enrollment, $studentId, $campusId, $sessionId, $hifzSecId, $teacherId, $userId, $date, $lesson);

            return ['success' => true, 'msg' => 'Sabaq saved. Same Mutalia assigned for today.'];
        }

        return ['success' => true, 'msg' => 'Sabaq saved.'];
    }

    protected function autoRepeatLessonFromPrior(
        object $enrollment,
        int $studentId,
        int $campusId,
        int $sessionId,
        int $hifzSecId,
        int $teacherId,
        int $userId,
        string $date,
        object $lesson
    ): void {
        $now  = date('Y-m-d H:i:s');
        $data = [
            'student_id'       => $studentId,
            'session_id'       => $sessionId,
            'campus_id'        => $campusId,
            'hifz_sec_id'      => $hifzSecId,
            'teacher_id'       => $teacherId,
            'entry_date'       => $date,
            'para_no'          => (int) $lesson->para_no,
            'lines_count'      => (int) $lesson->lines_count,
            'line_from'        => $lesson->line_from ?? null,
            'line_to'          => $lesson->line_to ?? null,
            'para_completed'   => 0,
            'new_para_started' => 0,
            'new_para_no'      => null,
            'remarks'          => 'Auto: repeated after weak Sabaq',
            'updated_date'     => $now,
            'user_id'          => $userId,
        ];

        $existing = $this->lessonLogForDate($studentId, $date);

        if ($existing) {
            $this->db->table('hifz_mutalia_logs')->where('id', (int) $existing->id)->update($data);
        } else {
            $data['created_date'] = $now;
            $this->db->table('hifz_mutalia_logs')->insert($data);
        }
    }

    /**
     * @param array<string, mixed> $input
     * @return array{success:bool,msg:string}
     */
    public function saveSabqi(
        object $enrollment,
        int $studentId,
        int $campusId,
        int $sessionId,
        int $hifzSecId,
        int $teacherId,
        int $userId,
        string $date,
        array $input
    ): array {
        $state      = $this->studentState($enrollment);
        $recited    = $state['sabqi_paras'];

        $validRecited = array_values(array_intersect($recited, $state['sabqi_paras']));

        if ($validRecited === [] && $state['sabqi_paras'] !== []) {
            return ['success' => false, 'msg' => 'No active Sabqi paras on file for this student.'];
        }

        $quality = strtolower(trim((string) ($input['sabqi_quality'] ?? '')));
        if ($quality === '') {
            return ['success' => false, 'msg' => 'Select Sabqi quality.'];
        }

        $listenerType = strtolower(trim((string) ($input['sabqi_listener_type'] ?? 'teacher')));
        if (! in_array($listenerType, ['teacher', 'fellow'], true)) {
            $listenerType = 'teacher';
        }
        $fellowId = (int) ($input['sabqi_listener_student_id'] ?? 0);
        if ($listenerType === 'fellow' && $fellowId <= 0) {
            return ['success' => false, 'msg' => 'Select the class fellow who listened.'];
        }

        $now  = date('Y-m-d H:i:s');
        $data = [
            'student_id'            => $studentId,
            'session_id'            => $sessionId,
            'campus_id'             => $campusId,
            'hifz_sec_id'           => $hifzSecId,
            'teacher_id'            => $teacherId,
            'entry_date'            => $date,
            'sabqi_quality'         => $quality,
            'hard_mistakes'         => max(0, (int) ($input['sabqi_hard_mistakes'] ?? 0)),
            'soft_mistakes'         => max(0, (int) ($input['sabqi_soft_mistakes'] ?? 0)),
            'listener_type'         => $listenerType,
            'listener_student_id'   => $listenerType === 'fellow' ? $fellowId : null,
            'remarks'               => trim((string) ($input['sabqi_remarks'] ?? '')) ?: null,
            'updated_date'          => $now,
            'user_id'               => $userId,
        ];

        $existing = $this->sabqiLogForDate($studentId, $date);

        if ($existing) {
            $logId = (int) $existing->id;
            $this->db->table('hifz_sabqi_logs')->where('id', $logId)->update($data);
            $this->db->table('hifz_sabqi_log_paras')->where('sabqi_log_id', $logId)->delete();
        } else {
            $data['created_date'] = $now;
            $this->db->table('hifz_sabqi_logs')->insert($data);
            $logId = (int) $this->db->insertID();
        }

        foreach ($validRecited as $para) {
            $this->db->table('hifz_sabqi_log_paras')->insert([
                'sabqi_log_id' => $logId,
                'para_no'      => $para,
            ]);
        }

        return ['success' => true, 'msg' => 'Sabqi saved.'];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{success:bool,msg:string}
     */
    public function saveManzil(
        object $enrollment,
        int $studentId,
        int $campusId,
        int $sessionId,
        int $hifzSecId,
        int $teacherId,
        int $userId,
        string $date,
        array $input
    ): array {
        $state   = $this->studentState($enrollment);
        $pool    = $state['manzil_pool'];
        $juzList = $this->manzil->parseJuzList($input['manzil_juz_list'] ?? $input['manzil_paras'] ?? '');

        if ($juzList === []) {
            $suggested = $this->manzil->suggestFromPool(
                $pool,
                $state['manzil_rotation_index'],
                $state['manzil_paras_per_day']
            );
            $juzList = $suggested['juz_list'];
        }

        if ($juzList === []) {
            return ['success' => false, 'msg' => 'Manzil pool is empty. Add paras via Sabqi or enrollment.'];
        }

        $parasPerDay = $state['manzil_paras_per_day'];
        if (count($juzList) > $parasPerDay) {
            $juzList = array_slice($juzList, 0, $parasPerDay);
        }

        foreach ($juzList as $para) {
            if (! in_array($para, $pool, true)) {
                return ['success' => false, 'msg' => 'Para ' . $para . ' is not in the Manzil pool.'];
            }
        }

        $listenerType = strtolower(trim((string) ($input['manzil_listener_type'] ?? 'teacher')));
        if (! in_array($listenerType, ['teacher', 'fellow'], true)) {
            $listenerType = 'teacher';
        }

        $fellowId = (int) ($input['manzil_listener_student_id'] ?? 0);
        if ($listenerType === 'fellow' && $fellowId <= 0) {
            return ['success' => false, 'msg' => 'Select the class fellow who listened.'];
        }

        $quality = strtolower(trim((string) ($input['manzil_quality'] ?? $input['recitation_quality'] ?? '')));
        if ($quality === '') {
            return ['success' => false, 'msg' => 'Select Manzil quality.'];
        }

        $hard    = max(0, (int) ($input['manzil_hard_mistakes'] ?? 0));
        $soft    = max(0, (int) ($input['manzil_soft_mistakes'] ?? 0));
        $remarks = trim((string) ($input['manzil_remarks'] ?? '')) ?: null;
        $now     = date('Y-m-d H:i:s');

        $this->db->table('hifz_manzil_logs')
            ->where('student_id', $studentId)
            ->where('entry_date', $date)
            ->delete();

        foreach ($juzList as $para) {
            $this->db->table('hifz_manzil_logs')->insert([
                'student_id'            => $studentId,
                'session_id'            => $sessionId,
                'campus_id'             => $campusId,
                'hifz_sec_id'           => $hifzSecId,
                'teacher_id'            => $teacherId,
                'entry_date'            => $date,
                'para_no'               => $para,
                'recitation_quality'    => $quality,
                'listener_type'         => $listenerType,
                'listener_student_id'   => $listenerType === 'fellow' ? $fellowId : null,
                'hard_mistakes'         => $hard,
                'soft_mistakes'         => $soft,
                'remarks'               => $remarks,
                'created_date'          => $now,
                'updated_date'          => $now,
                'user_id'               => $userId,
            ]);
        }

        $this->advanceManzilRotation($enrollment, count($juzList));

        return ['success' => true, 'msg' => 'Manzil saved.'];
    }

    /**
     * @return array{success:bool,msg:string}
     */
    public function removeSabqiPara(object $enrollment, int $paraNo): array
    {
        $paraNo = max(1, min(30, $paraNo));
        $state  = $this->studentState($enrollment);

        if ($paraNo === $state['current_para']) {
            return ['success' => false, 'msg' => 'Cannot remove the current Mutalia para from Sabqi.'];
        }

        if (! in_array($paraNo, $state['sabqi_paras'], true)) {
            return ['success' => false, 'msg' => 'Para is not in the Sabqi stack.'];
        }

        $this->moveSabqiParaToManzil($enrollment, $paraNo);

        return ['success' => true, 'msg' => 'Para ' . $paraNo . ' moved to Manzil pool.'];
    }

    protected function moveSabqiParaToManzil(object $enrollment, int $paraNo): void
    {
        $state = $this->studentState($enrollment);
        $pool  = $state['manzil_pool'];

        if ($paraNo >= 1 && $paraNo <= 30 && ! in_array($paraNo, $pool, true)) {
            $pool[] = $paraNo;
            sort($pool);
        }

        $this->db->table('hifz_students')
            ->where('id', (int) $enrollment->id)
            ->update([
                'manzil_pool_paras' => hifzFormatJuzList($pool) ?: null,
                'updated_date'      => date('Y-m-d H:i:s'),
            ]);

        $enrollment = $this->reloadEnrollment((int) $enrollment->id) ?? $enrollment;
        $state      = $this->studentState($enrollment);
        $this->syncSabqiActiveParasIfNeeded(
            $enrollment,
            $state['current_para'],
            $state['lines_done_in_para']
        );
    }

    protected function advanceManzilRotation(object $enrollment, int $count): void
    {
        $state = $this->studentState($enrollment);
        $pool  = $state['manzil_pool'];

        if ($pool === [] || $count <= 0) {
            return;
        }

        $newIndex = ((int) $state['manzil_rotation_index'] + $count) % count($pool);

        $this->db->table('hifz_students')
            ->where('id', (int) $enrollment->id)
            ->update([
                'manzil_rotation_index' => $newIndex,
                'updated_date'          => date('Y-m-d H:i:s'),
            ]);
    }

    public function findLessonBeforeDate(int $studentId, string $date): ?object
    {
        return $this->findMutaliaBeforeDate($studentId, $date);
    }

    public function findMutaliaBeforeDate(int $studentId, string $date): ?object
    {
        return $this->db->table('hifz_mutalia_logs')
            ->where('student_id', $studentId)
            ->where('entry_date <', $date)
            ->orderBy('entry_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRow();
    }

    public function lessonLogForDate(int $studentId, string $date): ?object
    {
        return $this->mutaliaLogForDate($studentId, $date);
    }

    public function mutaliaLogForDate(int $studentId, string $date): ?object
    {
        return $this->db->table('hifz_mutalia_logs')
            ->where('student_id', $studentId)
            ->where('entry_date', $date)
            ->get()
            ->getRow();
    }

    /**
     * Lesson history for one para (Mutalia assignments on that para).
     *
     * @return list<array<string, mixed>>
     */
    public function lessonLogsForPara(int $studentId, int $paraNo, int $limit = 30): array
    {
        helper('hifz');
        $paraNo = max(1, min(30, $paraNo));

        $rows = $this->db->table('hifz_mutalia_logs')
            ->where('student_id', $studentId)
            ->where('para_no', $paraNo)
            ->orderBy('entry_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResult();

        $out = [];
        foreach ($rows as $row) {
            $quality = trim((string) ($row->sabaq_quality ?? ''));
            $out[] = [
                'entry_date'    => (string) $row->entry_date,
                'lines_count'   => (int) ($row->lines_count ?? 0),
                'line_from'     => (int) ($row->line_from ?? 0),
                'line_to'       => (int) ($row->line_to ?? 0),
                'lesson_label'  => hifzLessonLabel($row),
                'sabaq_date'    => (string) ($row->sabaq_date ?? ''),
                'sabaq_quality' => $quality,
                'sabaq_label'   => $quality !== '' ? hifzQualityLabel($quality) : '—',
                'remarks'       => (string) ($row->remarks ?? ''),
                'para_completed'=> (int) ($row->para_completed ?? 0),
            ];
        }

        return $out;
    }

    /**
     * Whether Sabaq was recorded today (updates prior day's lesson row).
     */
    public function sabaqSavedToday(int $studentId, string $date): bool
    {
        $lesson = $this->findLessonBeforeDate($studentId, $date);

        if (! $lesson) {
            return false;
        }

        return (string) ($lesson->sabaq_date ?? '') === $date
            && trim((string) ($lesson->sabaq_quality ?? '')) !== '';
    }

    /** @deprecated Use lesson row sabaq columns */
    public function sabaqLogForDate(int $studentId, string $date): ?object
    {
        if ($this->sabaqSavedToday($studentId, $date)) {
            return $this->findLessonBeforeDate($studentId, $date);
        }

        return null;
    }

    public function sabqiLogForDate(int $studentId, string $date): ?object
    {
        return $this->db->table('hifz_sabqi_logs')
            ->where('student_id', $studentId)
            ->where('entry_date', $date)
            ->get()
            ->getRow();
    }

    /**
     * @return list<object>
     */
    public function manzilLogsForDate(int $studentId, string $date): array
    {
        return $this->db->table('hifz_manzil_logs')
            ->where('student_id', $studentId)
            ->where('entry_date', $date)
            ->orderBy('para_no', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * @return list<int>
     */
    public function sabqiParasForLog(int $sabqiLogId): array
    {
        if ($sabqiLogId <= 0) {
            return [];
        }

        $rows = $this->db->table('hifz_sabqi_log_paras')
            ->where('sabqi_log_id', $sabqiLogId)
            ->orderBy('para_no', 'ASC')
            ->get()
            ->getResult();

        $out = [];
        foreach ($rows as $row) {
            $out[] = (int) $row->para_no;
        }

        return $out;
    }

    protected function reloadEnrollment(int $id): ?object
    {
        return $this->db->table('hifz_students')->where('id', $id)->get()->getRow();
    }

    /**
     * @param string|list<int>|mixed $raw
     * @return list<int>
     */
    protected function parseParaListInput($raw): array
    {
        if (is_array($raw)) {
            return hifzParseJuzList(implode(',', array_map('strval', $raw)));
        }

        return hifzParseJuzList((string) $raw);
    }

    public function findLastLessonForPara(int $studentId, int $paraNo): ?object
    {
        $paraNo = max(1, min(30, $paraNo));

        return $this->db->table('hifz_mutalia_logs')
            ->where('student_id', $studentId)
            ->where('para_no', $paraNo)
            ->orderBy('entry_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRow();
    }

    public function paraLessonCount(int $studentId, int $paraNo): int
    {
        $paraNo = max(1, min(30, $paraNo));

        return (int) $this->db->table('hifz_mutalia_logs')
            ->where('student_id', $studentId)
            ->where('para_no', $paraNo)
            ->countAllResults();
    }

    /**
     * Mon–Sat week grids for all lessons on the current para.
     *
     * @return array<string, mixed>
     */
    public function buildMutaliaParaWeekTimeline(int $studentId, int $paraNo): array
    {
        helper('hifz');
        $paraNo = max(1, min(30, $paraNo));
        $today  = date('Y-m-d');

        $rows = $this->db->table('hifz_mutalia_logs')
            ->where('student_id', $studentId)
            ->where('para_no', $paraNo)
            ->orderBy('entry_date', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();

        if ($rows === []) {
            return [
                'week_count'       => 0,
                'lessons_in_para'  => 0,
                'first_entry_date' => '',
                'last_entry_date'  => '',
                'weeks'            => [],
            ];
        }

        $byDate = [];
        $lessonNo = 0;
        foreach ($rows as $row) {
            $lessonNo++;
            $d = (string) $row->entry_date;
            $quality = trim((string) ($row->sabaq_quality ?? ''));
            $byDate[$d] = [
                'entry_date'        => $d,
                'log_id'            => (int) $row->id,
                'lines_count'       => (int) ($row->lines_count ?? 0),
                'lesson_no'         => $lessonNo,
                'sabaq_quality'     => $quality,
                'sabaq_label'       => $quality !== '' ? hifzQualityLabel($quality) : '',
                'has_entry'         => true,
                'is_pending_sabaq'  => $quality === '',
            ];
        }

        $firstDate = (string) $rows[0]->entry_date;
        $lastDate  = (string) $rows[count($rows) - 1]->entry_date;
        $weekStart = $this->mondayOfWeek($firstDate);
        $endMonday = $this->mondayOfWeek($lastDate);
        $weeks     = [];
        $weekIndex = 0;

        for ($cursor = $weekStart; $cursor <= $endMonday; $cursor = date('Y-m-d', strtotime($cursor . ' +7 days'))) {
            $weekIndex++;
            $days = [];
            for ($d = 0; $d < 6; $d++) {
                $dayDate = date('Y-m-d', strtotime($cursor . ' +' . $d . ' days'));
                $days[]  = $this->mutaliaTimelineDayCell($dayDate, $byDate, $firstDate, $lastDate, $today);
            }

            $weekEnd = date('Y-m-d', strtotime($cursor . ' +5 days'));
            $weeks[] = [
                'week_label' => sprintf(
                    'Week %d · %s – %s',
                    $weekIndex,
                    date('j M', strtotime($cursor)),
                    date('j M', strtotime($weekEnd))
                ),
                'week_start' => $cursor,
                'days'       => $days,
            ];
        }

        return [
            'week_count'       => count($weeks),
            'lessons_in_para'  => count($rows),
            'first_entry_date' => $firstDate,
            'last_entry_date'  => $lastDate,
            'weeks'            => $weeks,
        ];
    }

    protected function mondayOfWeek(string $date): string
    {
        return date('Y-m-d', strtotime('monday this week', strtotime($date)));
    }

    /**
     * @param array<string, array<string, mixed>> $byDate
     * @return array<string, mixed>
     */
    protected function mutaliaTimelineDayCell(
        string $dayDate,
        array $byDate,
        string $firstDate,
        string $lastDate,
        string $today
    ): array {
        $dayLabel = date('D', strtotime($dayDate));

        if (isset($byDate[$dayDate])) {
            $cell = $byDate[$dayDate];
            $cell['day'] = $dayLabel;

            return $cell;
        }

        $inRange = $dayDate >= $firstDate && $dayDate <= $lastDate;
        $missed  = $inRange && $dayDate < $today;

        return [
            'entry_date'       => $dayDate,
            'day'              => $dayLabel,
            'log_id'           => 0,
            'lines_count'      => 0,
            'lesson_no'        => 0,
            'sabaq_quality'    => '',
            'sabaq_label'      => '',
            'has_entry'        => false,
            'is_pending_sabaq' => false,
            'is_missed'        => $missed,
        ];
    }

    /**
     * @return array{last_week_start:string,this_week_start:string,range_end:string,weekday_columns:list<string>}
     */
    protected function twoWeekCalendarRange(): array
    {
        $today         = date('Y-m-d');
        $thisWeekStart = date('Y-m-d', strtotime('monday this week', strtotime($today)));
        $lastWeekStart = date('Y-m-d', strtotime($thisWeekStart . ' -7 days'));
        $rangeEnd      = date('Y-m-d', strtotime($thisWeekStart . ' +5 days'));

        return [
            'last_week_start'   => $lastWeekStart,
            'this_week_start'   => $thisWeekStart,
            'range_end'         => $rangeEnd,
            'weekday_columns'   => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            'last_week_label'   => 'Previous week · ' . date('j M', strtotime($lastWeekStart))
                . ' – ' . date('j M', strtotime($lastWeekStart . ' +5 days')),
            'this_week_label'   => 'This week · ' . date('j M', strtotime($thisWeekStart))
                . ' – ' . date('j M', strtotime($thisWeekStart . ' +5 days')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSabaqTwoWeekCalendar(int $studentId, int $sessionId): array
    {
        $range = $this->twoWeekCalendarRange();

        $rows = $this->db->table('hifz_mutalia_logs')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('sabaq_date >=', $range['last_week_start'])
            ->where('sabaq_date <=', $range['range_end'])
            ->where('sabaq_date IS NOT NULL', null, false)
            ->orderBy('sabaq_date', 'ASC')
            ->get()
            ->getResult();

        $byDate = [];
        foreach ($rows as $row) {
            $d = (string) ($row->sabaq_date ?? '');
            if ($d === '' || trim((string) ($row->sabaq_quality ?? '')) === '') {
                continue;
            }
            if (! isset($byDate[$d])) {
                $byDate[$d] = [];
            }
            $byDate[$d][] = $row;
        }

        $today    = date('Y-m-d');
        $lastWeek = $this->buildSabaqWeekRow($range['last_week_start'], $byDate, $today);
        $thisWeek = $this->buildSabaqWeekRow($range['this_week_start'], $byDate, $today);

        return [
            'weekday_columns' => $range['weekday_columns'],
            'last_week'       => $lastWeek,
            'this_week'       => $thisWeek,
            'last_week_label' => $range['last_week_label'],
            'this_week_label' => $range['this_week_label'],
        ];
    }

    /**
     * @param array<string, list<object>> $byDate
     * @return list<array<string, mixed>>
     */
    protected function buildSabaqWeekRow(string $weekStart, array $byDate, string $today): array
    {
        $cells = [];
        for ($d = 0; $d < 6; $d++) {
            $dateStr = date('Y-m-d', strtotime($weekStart . ' +' . $d . ' days'));
            $cells[] = $this->calendarCellForSabaq($dateStr, $byDate[$dateStr] ?? []);
        }

        return $cells;
    }

    /**
     * @param list<object> $dayRows
     * @return array<string, mixed>
     */
    protected function calendarCellForSabaq(string $dateStr, array $dayRows): array
    {
        if ($dayRows === []) {
            return [
                'date'      => $dateStr,
                'day'       => date('D', strtotime($dateStr)),
                'has_entry' => false,
                'paras'     => [],
                'listener'  => '',
                'mistakes'  => '',
                'quality'   => '',
            ];
        }

        $first   = $dayRows[0];
        $paraNo  = (int) ($first->para_no ?? 0);
        $lines   = (int) ($first->lines_count ?? 0);
        $hard    = (int) ($first->sabaq_hard_mistakes ?? 0);
        $soft    = (int) ($first->sabaq_soft_mistakes ?? 0);
        $quality = trim((string) ($first->sabaq_quality ?? ''));
        $detail  = 'Para ' . $paraNo . ' · ' . $lines . ' lines';

        return [
            'date'      => $dateStr,
            'day'       => date('D', strtotime($dateStr)),
            'has_entry' => true,
            'paras'     => [$detail],
            'listener'  => strtolower((string) ($first->sabaq_listener_type ?? '')) === 'fellow' ? 'Fellow' : 'Teacher',
            'mistakes'  => ($hard || $soft) ? ('H' . $hard . ' S' . $soft) : '',
            'quality'   => $quality !== '' ? hifzQualityLabel($quality) : '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSabqiTwoWeekCalendar(int $studentId, int $sessionId): array
    {
        $range = $this->twoWeekCalendarRange();

        $logs = $this->db->table('hifz_sabqi_logs')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('entry_date >=', $range['last_week_start'])
            ->where('entry_date <=', $range['range_end'])
            ->orderBy('entry_date', 'ASC')
            ->get()
            ->getResult();

        $logIds = array_map(static fn ($r) => (int) $r->id, $logs);
        $parasByLog = [];
        if ($logIds !== []) {
            $paraRows = $this->db->table('hifz_sabqi_log_paras')
                ->whereIn('sabqi_log_id', $logIds)
                ->orderBy('para_no', 'ASC')
                ->get()
                ->getResult();
            foreach ($paraRows as $pr) {
                $lid = (int) $pr->sabqi_log_id;
                if (! isset($parasByLog[$lid])) {
                    $parasByLog[$lid] = [];
                }
                $parasByLog[$lid][] = (int) $pr->para_no;
            }
        }

        $catalog = [];
        foreach (hifzJuzCatalog() as $item) {
            $catalog[(int) $item['juz_no']] = $item;
        }

        $byDate = [];
        foreach ($logs as $log) {
            $d = (string) $log->entry_date;
            if (! isset($byDate[$d])) {
                $byDate[$d] = [];
            }
            $byDate[$d][] = ['log' => $log, 'paras' => $parasByLog[(int) $log->id] ?? []];
        }

        $today    = date('Y-m-d');
        $lastWeek = $this->buildSabqiWeekRow($range['last_week_start'], $byDate, $catalog, $today);
        $thisWeek = $this->buildSabqiWeekRow($range['this_week_start'], $byDate, $catalog, $today);

        return [
            'weekday_columns' => $range['weekday_columns'],
            'last_week'       => $lastWeek,
            'this_week'       => $thisWeek,
            'last_week_label' => $range['last_week_label'],
            'this_week_label' => $range['this_week_label'],
        ];
    }

    /**
     * @param array<string, list<array{log:object,paras:list<int>}>> $byDate
     * @param array<int, array<string, mixed>> $catalog
     * @return list<array<string, mixed>>
     */
    protected function buildSabqiWeekRow(string $weekStart, array $byDate, array $catalog, string $today): array
    {
        $cells = [];
        for ($d = 0; $d < 6; $d++) {
            $dateStr = date('Y-m-d', strtotime($weekStart . ' +' . $d . ' days'));
            $cells[] = $this->calendarCellForSabqi($dateStr, $byDate[$dateStr] ?? [], $catalog);
        }

        return $cells;
    }

    /**
     * @param list<array{log:object,paras:list<int>}> $dayEntries
     * @param array<int, array<string, mixed>> $catalog
     * @return array<string, mixed>
     */
    protected function calendarCellForSabqi(string $dateStr, array $dayEntries, array $catalog): array
    {
        if ($dayEntries === []) {
            return [
                'date'      => $dateStr,
                'day'       => date('D', strtotime($dateStr)),
                'has_entry' => false,
                'paras'     => [],
                'listener'  => '',
                'mistakes'  => '',
                'quality'   => '',
            ];
        }

        $entry = $dayEntries[0];
        $log   = $entry['log'];
        $paras = [];
        foreach ($entry['paras'] as $p) {
            $item   = $catalog[$p] ?? null;
            $nameAr = trim((string) ($item['name_ar'] ?? ''));
            $paras[] = $nameAr !== '' ? ($p . ' · ' . $nameAr) : (string) $p;
        }

        $hard    = (int) ($log->hard_mistakes ?? 0);
        $soft    = (int) ($log->soft_mistakes ?? 0);
        $quality = trim((string) ($log->sabqi_quality ?? ''));

        return [
            'date'      => $dateStr,
            'day'       => date('D', strtotime($dateStr)),
            'has_entry' => true,
            'paras'     => $paras,
            'listener'  => strtolower((string) ($log->listener_type ?? '')) === 'fellow' ? 'Fellow' : 'Teacher',
            'mistakes'  => ($hard || $soft) ? ('H' . $hard . ' S' . $soft) : '',
            'quality'   => $quality !== '' ? hifzQualityLabel($quality) : '',
        ];
    }

    /**
     * Two calendar weeks (Mon–Sat), each starting Monday.
     *
     * @return array<string, mixed>
     */
    public function buildManzilTwoWeekCalendar(int $studentId, int $sessionId): array
    {
        $range         = $this->twoWeekCalendarRange();
        $lastWeekStart = $range['last_week_start'];
        $thisWeekStart = $range['this_week_start'];
        $rangeEnd      = $range['range_end'];
        $today         = date('Y-m-d');

        $catalog = [];
        foreach (hifzJuzCatalog() as $item) {
            $catalog[(int) $item['juz_no']] = $item;
        }

        $rows = $this->db->table('hifz_manzil_logs')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('entry_date >=', $lastWeekStart)
            ->where('entry_date <=', $rangeEnd)
            ->orderBy('entry_date', 'ASC')
            ->orderBy('para_no', 'ASC')
            ->get()
            ->getResult();

        $byDate = [];
        foreach ($rows as $row) {
            $d = (string) $row->entry_date;
            if (! isset($byDate[$d])) {
                $byDate[$d] = [];
            }
            $byDate[$d][] = $row;
        }

        $lastWeek = $this->buildManzilWeekRow($lastWeekStart, $byDate, $catalog, $today);
        $thisWeek = $this->buildManzilWeekRow($thisWeekStart, $byDate, $catalog, $today);

        return [
            'weekday_columns' => $range['weekday_columns'],
            'last_week'       => $lastWeek,
            'this_week'       => $thisWeek,
            'last_week_label' => $range['last_week_label'],
            'this_week_label' => $range['this_week_label'],
        ];
    }

    /**
     * @param array<string, list<object>> $byDate
     * @param array<int, array<string, mixed>> $catalog
     * @return list<array<string, mixed>>
     */
    protected function buildManzilWeekRow(string $weekStart, array $byDate, array $catalog, string $today): array
    {
        $cells = [];
        for ($d = 0; $d < 6; $d++) {
            $dateStr = date('Y-m-d', strtotime($weekStart . ' +' . $d . ' days'));
            $cells[] = $this->calendarCellForManzil($dateStr, $byDate[$dateStr] ?? [], $catalog);
        }

        return $cells;
    }

    /**
     * @param list<object> $dayRows
     * @param array<int, array<string, mixed>> $catalog
     * @return array<string, mixed>
     */
    protected function calendarCellForManzil(string $dateStr, array $dayRows, array $catalog): array
    {
        if ($dayRows === []) {
            return [
                'date'       => $dateStr,
                'day'        => date('D', strtotime($dateStr)),
                'has_entry'  => false,
                'paras'      => [],
                'listener'   => '',
                'mistakes'   => '',
            ];
        }

        $paras = [];
        foreach ($dayRows as $row) {
            $p = (int) $row->para_no;
            $item = $catalog[$p] ?? null;
            $nameAr = trim((string) ($item['name_ar'] ?? ''));
            $paras[] = $nameAr !== '' ? ($p . ' · ' . $nameAr) : (string) $p;
        }

        $first   = $dayRows[0];
        $hard    = (int) ($first->hard_mistakes ?? 0);
        $soft    = (int) ($first->soft_mistakes ?? 0);
        $quality = trim((string) ($first->recitation_quality ?? ''));

        return [
            'date'      => $dateStr,
            'day'       => date('D', strtotime($dateStr)),
            'has_entry' => true,
            'paras'     => $paras,
            'listener'  => strtolower((string) ($first->listener_type ?? '')) === 'fellow' ? 'Fellow' : 'Teacher',
            'mistakes'  => ($hard || $soft) ? ('H' . $hard . ' S' . $soft) : '',
            'quality'   => $quality !== '' ? hifzQualityLabel($quality) : '',
        ];
    }

    /**
     * @return list<array{juz_no:int,title:string,title_ar:string,label:string}>
     */
    public function poolParaCards(array $pool): array
    {
        $catalog = [];
        foreach (hifzJuzCatalog() as $item) {
            $catalog[(int) $item['juz_no']] = $item;
        }

        $cards = [];
        foreach ($pool as $juzNo) {
            $item = $catalog[$juzNo] ?? null;
            if ($item) {
                $cards[] = [
                    'juz_no'   => $juzNo,
                    'title'    => $item['title'] ?? ('Para ' . $juzNo),
                    'title_ar' => $item['title_ar'] ?? '',
                    'name_ar'  => $item['name_ar'] ?? '',
                    'label'    => $item['title'] ?? ('Para ' . $juzNo),
                ];
            }
        }

        return $cards;
    }
}
