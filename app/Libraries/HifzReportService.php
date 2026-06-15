<?php

namespace App\Libraries;

use Config\Database;

/**
 * Hifz progress summaries and recitation logs (para-only, 4 log tables).
 */
class HifzReportService
{
    protected $db;
    protected HifzManzilCalculator $manzil;

    public function __construct()
    {
        helper('hifz');
        $this->db     = Database::connect();
        $this->manzil = new HifzManzilCalculator();
    }

    /**
     * @return array<string, mixed>
     */
    public function getPortalData(int $studentId, int $sessionId, int $days = 30): array
    {
        $enroll = $this->db->table('hifz_students hs')
            ->select('hs.*, hsec.section_name')
            ->select('CONCAT(u.first_name, " ", COALESCE(u.last_name, "")) AS teacher_name', false)
            ->join('hifz_sections hsec', 'hsec.hifz_sec_id = hs.hifz_sec_id', 'left')
            ->join('hifz_teacher_sections hts', 'hts.hifz_sec_id = hs.hifz_sec_id AND hts.session_id = hs.session_id AND hts.status = 1', 'left')
            ->join('users u', 'u.id = hts.teacher_id', 'left')
            ->where('hs.student_id', $studentId)
            ->where('hs.session_id', $sessionId)
            ->where('hs.status', 1)
            ->get()
            ->getRow();

        if (! $enroll) {
            return ['enrolled' => false];
        }

        $currentPara = (int) ($enroll->current_para_no ?? $enroll->current_juz ?? 1);
        $dateTo      = date('Y-m-d');
        $dateFrom    = date('Y-m-d', strtotime('-' . max(1, $days) . ' days'));

        return [
            'enrolled'          => true,
            'section_name'      => $enroll->section_name ?? '',
            'teacher_name'      => trim($enroll->teacher_name ?? '') ?: '—',
            'current_juz'       => $currentPara,
            'current_para_label'=> hifzJuzTitle($currentPara, false),
            'juz_progress_pct'  => 0,
            'sabaq_lines'       => (int) ($enroll->sabaq_lines_per_day ?? 0),
            'mutalia_lines'     => (int) ($enroll->mutalia_lines_per_day ?? 0),
            'manzil_paras_per_day' => (int) ($enroll->manzil_paras_per_day ?? 1),
            'sequence_label'    => 'Para-wise',
            'enrollment_date'   => $enroll->enrollment_date ?? '',
            'recent_log'        => $this->getStudentLog($studentId, $dateFrom, $dateTo),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getSectionReport(int $hifzSecId, int $campusId, int $sessionId, string $dateFrom, string $dateTo): array
    {
        $students = $this->db->table('hifz_students hs')
            ->select('hs.student_id, hs.current_para_no, hs.current_juz, hs.sabaq_lines_per_day, hs.mutalia_lines_per_day')
            ->select('s.first_name, s.last_name, s.reg_no')
            ->join('students s', 's.student_id = hs.student_id')
            ->where('hs.hifz_sec_id', $hifzSecId)
            ->where('hs.campus_id', $campusId)
            ->where('hs.session_id', $sessionId)
            ->where('hs.status', 1)
            ->where('s.status', 1)
            ->orderBy('s.first_name', 'ASC')
            ->orderBy('s.last_name', 'ASC')
            ->get()
            ->getResultArray();

        if ($students === []) {
            return [];
        }

        $ids      = array_map(static fn ($r) => (int) $r['student_id'], $students);
        $logStats = $this->logStatsForStudents($ids, $dateFrom, $dateTo);
        $out      = [];

        foreach ($students as $row) {
            $sid   = (int) $row['student_id'];
            $stats = $logStats[$sid] ?? ['days_logged' => 0, 'last_date' => '', 'last_quality' => ''];
            $para  = (int) ($row['current_para_no'] ?? $row['current_juz'] ?? 0);

            $out[] = [
                'student_id'    => $sid,
                'student_name'  => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                'reg_no'        => $row['reg_no'] ?? '',
                'current_juz'   => $para,
                'juz_progress'  => 0,
                'cursor_line'   => 0,
                'sabaq_lines'   => (int) ($row['sabaq_lines_per_day'] ?? 0),
                'mutalia_lines' => (int) ($row['mutalia_lines_per_day'] ?? 0),
                'days_logged'   => (int) ($stats['days_logged'] ?? 0),
                'last_date'     => $stats['last_date'] ?? '',
                'last_quality'  => hifzQualityLabel($stats['last_quality'] ?? ''),
            ];
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    public function getStudentSummary(int $studentId, int $sessionId, string $dateFrom, string $dateTo): array
    {
        $student = $this->db->table('students s')
            ->select('s.student_id, s.first_name, s.last_name, s.reg_no')
            ->select('hs.*, hsec.section_name')
            ->select('CONCAT(u.first_name, " ", COALESCE(u.last_name, "")) AS teacher_name', false)
            ->join('hifz_students hs', 'hs.student_id = s.student_id AND hs.session_id = ' . (int) $sessionId . ' AND hs.status = 1', 'inner')
            ->join('hifz_sections hsec', 'hsec.hifz_sec_id = hs.hifz_sec_id', 'left')
            ->join('hifz_teacher_sections hts', 'hts.hifz_sec_id = hs.hifz_sec_id AND hts.session_id = hs.session_id AND hts.status = 1', 'left')
            ->join('users u', 'u.id = hts.teacher_id', 'left')
            ->where('s.student_id', $studentId)
            ->where('s.status', 1)
            ->get()
            ->getRowArray();

        if (! $student) {
            return ['found' => false];
        }

        $para     = (int) ($student['current_para_no'] ?? $student['current_juz'] ?? 0);
        $logStats = $this->logStatsForStudents([$studentId], $dateFrom, $dateTo);
        $stats    = $logStats[$studentId] ?? ['days_logged' => 0, 'last_date' => ''];

        return [
            'found'            => true,
            'student_id'       => $studentId,
            'student_name'     => trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')),
            'reg_no'           => $student['reg_no'] ?? '',
            'section_name'     => $student['section_name'] ?? '',
            'teacher_name'     => trim($student['teacher_name'] ?? '') ?: '—',
            'sequence_label'   => 'Para-wise',
            'current_juz'      => $para,
            'current_para_label' => hifzJuzTitle($para, false),
            'juz_progress'     => 0,
            'sabaq_lines'      => (int) ($student['sabaq_lines_per_day'] ?? 0),
            'mutalia_lines'    => (int) ($student['mutalia_lines_per_day'] ?? 0),
            'enrollment_date'  => $student['enrollment_date'] ?? '',
            'days_logged'      => (int) ($stats['days_logged'] ?? 0),
            'last_date'        => $stats['last_date'] ?? '',
            'log'              => $this->getStudentLog($studentId, $dateFrom, $dateTo),
        ];
    }

    /**
     * Merge daily entries from four log tables into one timeline per date.
     *
     * @return list<array<string, mixed>>
     */
    public function getStudentLog(int $studentId, string $dateFrom, string $dateTo): array
    {
        $byDate = [];

        $lessons = $this->db->table('hifz_mutalia_logs')
            ->where('student_id', $studentId)
            ->where('entry_date >=', $dateFrom)
            ->where('entry_date <=', $dateTo)
            ->get()
            ->getResult();

        foreach ($lessons as $row) {
            $d = (string) $row->entry_date;
            $byDate[$d]['lesson'] = $row;

            $sabaqDate = (string) ($row->sabaq_date ?? '');
            if ($sabaqDate !== '' && $sabaqDate >= $dateFrom && $sabaqDate <= $dateTo) {
                $byDate[$sabaqDate]['sabaq_lesson'] = $row;
            }
        }

        $sabqiLogs = $this->db->table('hifz_sabqi_logs')
            ->where('student_id', $studentId)
            ->where('entry_date >=', $dateFrom)
            ->where('entry_date <=', $dateTo)
            ->get()
            ->getResult();

        foreach ($sabqiLogs as $row) {
            $d = (string) $row->entry_date;
            $paras = $this->db->table('hifz_sabqi_log_paras')
                ->where('sabqi_log_id', (int) $row->id)
                ->orderBy('para_no', 'ASC')
                ->get()
                ->getResult();
            $byDate[$d]['sabqi'] = ['log' => $row, 'paras' => array_map(static fn ($p) => (int) $p->para_no, $paras)];
        }

        $manzil = $this->db->table('hifz_manzil_logs')
            ->where('student_id', $studentId)
            ->where('entry_date >=', $dateFrom)
            ->where('entry_date <=', $dateTo)
            ->orderBy('entry_date', 'DESC')
            ->orderBy('para_no', 'ASC')
            ->get()
            ->getResult();

        foreach ($manzil as $row) {
            $d = (string) $row->entry_date;
            if (! isset($byDate[$d]['manzil'])) {
                $byDate[$d]['manzil'] = [];
            }
            $byDate[$d]['manzil'][] = $row;
        }

        krsort($byDate);

        $out = [];
        foreach ($byDate as $date => $parts) {
            $out[] = $this->formatDayLog($date, $parts);
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $parts
     * @return array<string, mixed>
     */
    protected function formatDayLog(string $date, array $parts): array
    {
        $sabaqLabel   = '—';
        $sabqiLabel   = '—';
        $manzilLabel  = '—';
        $mutaliaLabel = '—';
        $sabaqQuality = '—';
        $sabqiQuality = '—';
        $manzilQuality = '—';
        $manzilListener = '—';
        $sabaqRemarks = '';
        $mutaliaRemarks = '';

        if (! empty($parts['sabaq_lesson'])) {
            $s = $parts['sabaq_lesson'];
            $sabaqLabel = hifzLessonLabel($s);
            $sabaqQuality = hifzQualityLabel((string) ($s->sabaq_quality ?? ''));
            $sabaqRemarks = (string) ($s->sabaq_remarks ?? '');
        }

        if (! empty($parts['sabqi']['log'])) {
            $sabqiLabel = $this->manzil->formatParaLabel($parts['sabqi']['paras']);
            $sq = trim((string) ($parts['sabqi']['log']->sabqi_quality ?? ''));
            $sabqiQuality = $sq !== '' ? hifzQualityLabel($sq) : 'Recited';
        } elseif (! empty($parts['sabqi']['paras'])) {
            $sabqiLabel = $this->manzil->formatParaLabel($parts['sabqi']['paras']);
            $sabqiQuality = 'Recited';
        }

        if (! empty($parts['lesson'])) {
            $m = $parts['lesson'];
            $mutaliaLabel = hifzLessonLabel($m);
            if (! empty($m->new_para_started)) {
                $mutaliaLabel .= ' → Para ' . (int) ($m->new_para_no ?? 0);
            }
            $mutaliaRemarks = (string) ($m->remarks ?? '');
            if ($sabaqQuality === '—' && trim((string) ($m->sabaq_quality ?? '')) !== '') {
                $sabaqQuality = hifzQualityLabel((string) $m->sabaq_quality);
                $sabaqLabel   = hifzLessonLabel($m);
            }
        }

        if (! empty($parts['manzil'])) {
            $paras = array_map(static fn ($r) => (int) $r->para_no, $parts['manzil']);
            $manzilLabel = $this->manzil->formatParaLabel($paras);
            $first = $parts['manzil'][0];
            $mq = trim((string) ($first->recitation_quality ?? ''));
            $manzilQuality = $mq !== '' ? hifzQualityLabel($mq) : $this->formatManzilMistakes($first);
            $manzilListener = $this->formatManzilListener($first);
        }

        return [
            'date'            => $date,
            'sabaq_label'     => $sabaqLabel,
            'sabqi_label'     => $sabqiLabel,
            'manzil_label'    => $manzilLabel,
            'mutalia_label'   => $mutaliaLabel,
            'sabaq_quality'   => $sabaqQuality,
            'sabqi_quality'   => $sabqiQuality,
            'manzil_quality'  => $manzilQuality,
            'manzil_listener' => $manzilListener,
            'mutalia_remarks' => $mutaliaRemarks,
            'sabaq_remarks'   => $sabaqRemarks,
        ];
    }

    /**
     * @param list<int> $studentIds
     * @return array<int, array{days_logged:int,last_date:string,last_quality:string}>
     */
    protected function logStatsForStudents(array $studentIds, string $dateFrom, string $dateTo): array
    {
        if ($studentIds === []) {
            return [];
        }

        $map = [];
        $datesByStudent = [];
        foreach ($studentIds as $sid) {
            $map[$sid] = ['days_logged' => 0, 'last_date' => '', 'last_quality' => ''];
            $datesByStudent[$sid] = [];
        }

        $tables = [
            ['table' => 'hifz_mutalia_logs', 'date' => 'entry_date'],
            ['table' => 'hifz_mutalia_logs', 'date' => 'sabaq_date'],
            ['table' => 'hifz_sabqi_logs', 'date' => 'entry_date'],
            ['table' => 'hifz_manzil_logs', 'date' => 'entry_date'],
        ];

        foreach ($tables as $t) {
            $rows = $this->db->table($t['table'])
                ->select('student_id, ' . $t['date'] . ' AS log_date', false)
                ->whereIn('student_id', $studentIds)
                ->where($t['date'] . ' >=', $dateFrom)
                ->where($t['date'] . ' <=', $dateTo)
                ->get()
                ->getResult();

            foreach ($rows as $row) {
                $sid = (int) $row->student_id;
                $d   = (string) $row->log_date;
                if ($d === '') {
                    continue;
                }
                $datesByStudent[$sid][$d] = true;
                if ($map[$sid]['last_date'] === '' || $d > $map[$sid]['last_date']) {
                    $map[$sid]['last_date'] = $d;
                }
            }
        }

        foreach ($studentIds as $sid) {
            $map[$sid]['days_logged'] = count($datesByStudent[$sid]);
        }

        $lastSabaq = $this->db->table('hifz_mutalia_logs')
            ->select('student_id, sabaq_date, sabaq_quality')
            ->whereIn('student_id', $studentIds)
            ->where('sabaq_date >=', $dateFrom)
            ->where('sabaq_date <=', $dateTo)
            ->where('sabaq_quality IS NOT NULL', null, false)
            ->orderBy('sabaq_date', 'DESC')
            ->get()
            ->getResult();

        foreach ($lastSabaq as $lr) {
            $sid = (int) $lr->student_id;
            if (($map[$sid]['last_quality'] ?? '') === '') {
                $map[$sid]['last_quality'] = (string) ($lr->sabaq_quality ?? '');
            }
        }

        return $map;
    }

    protected function formatManzilMistakes(object $row): string
    {
        $hard = (int) ($row->hard_mistakes ?? 0);
        $soft = (int) ($row->soft_mistakes ?? 0);

        if ($hard === 0 && $soft === 0) {
            return '—';
        }

        return 'Hard ' . $hard . ' · Soft ' . $soft;
    }

    protected function formatManzilListener(object $row): string
    {
        $type = strtolower((string) ($row->listener_type ?? ''));

        if ($type === 'teacher') {
            return 'Teacher';
        }

        if ($type !== 'fellow') {
            return '—';
        }

        $fellowId = (int) ($row->listener_student_id ?? 0);
        if ($fellowId <= 0) {
            return 'Class fellow';
        }

        $s = $this->db->table('students')
            ->select('first_name, last_name, reg_no')
            ->where('student_id', $fellowId)
            ->get()
            ->getRow();

        if (! $s) {
            return 'Class fellow';
        }

        $name = trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? ''));

        return $name !== '' ? $name : 'Class fellow';
    }
}
