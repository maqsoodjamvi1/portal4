<?php

namespace App\Libraries;

use Config\Database;

class HifzEnrollmentService
{
    protected $db;

    public function __construct()
    {
        helper('hifz');
        hifz_ensure_database_schema();

        $this->db = Database::connect();
    }

    /**
     * @return object|null Active hifz_students row
     */
    public function getActiveEnrollment(int $studentId, int $sessionId): ?object
    {
        return $this->db->table('hifz_students')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('status', 1)
            ->get()
            ->getRow();
    }

    /**
     * @return array<int, object> student_id => row
     */
    public function getActiveEnrollmentsForStudents(array $studentIds, int $sessionId): array
    {
        if ($studentIds === []) {
            return [];
        }

        $rows = $this->db->table('hifz_students')
            ->whereIn('student_id', $studentIds)
            ->where('session_id', $sessionId)
            ->where('status', 1)
            ->get()
            ->getResult();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->student_id] = $row;
        }

        return $map;
    }

    /**
     * Enroll, update, or withdraw a Hifz student for the session.
     *
     * @return array{success:bool,msg:string}
     */
    public function sync(int $studentId, int $campusId, int $sessionId, int $userId, array $input): array
    {
        $isHifz = ! empty($input['is_hifz']);
        $now    = date('Y-m-d H:i:s');

        if (! $isHifz) {
            $this->withdraw($studentId, $sessionId, $userId, $now);

            return ['success' => true, 'msg' => 'Hifz enrollment removed.'];
        }

        $hifzSecId = (int) ($input['hifz_sec_id'] ?? 0);

        if ($hifzSecId <= 0) {
            return ['success' => false, 'msg' => 'Select a Hifz section.'];
        }

        $section = $this->db->table('hifz_sections')
            ->where('hifz_sec_id', $hifzSecId)
            ->where('campus_id', $campusId)
            ->where('session_id', $sessionId)
            ->where('status', 1)
            ->get()
            ->getRow();

        if (! $section) {
            return ['success' => false, 'msg' => 'Hifz section not found or inactive.'];
        }

        $campus = $this->db->table('campus')->where('campus_id', $campusId)->get()->getRow();

        $existing = $this->db->table('hifz_students')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->get()
            ->getRow();

        $sequence = hifzNormalizeMemorizationSequence((string) ($input['memorization_sequence'] ?? 'para_forward'));

        $currentPara = max(1, min(30, (int) ($input['current_para_no'] ?? $input['starting_para_no'] ?? $input['hifz_current_juz'] ?? 0)));
        if ($currentPara <= 0) {
            $currentPara = max(1, min(30, (int) ($existing->current_para_no ?? $existing->current_juz ?? 1)));
        }

        if (array_key_exists('current_juz_memorized_lines', $input) || array_key_exists('lines_done_in_para', $input)) {
            $linesDone = max(0, min(hifzParaTotalLines(), (int) ($input['current_juz_memorized_lines'] ?? $input['lines_done_in_para'] ?? 0)));
        } elseif ($existing) {
            $linesDone = max(0, min(hifzParaTotalLines(), (int) ($existing->current_juz_memorized_lines ?? 0)));
        } else {
            $linesDone = 0;
        }

        $manzilParasPerDay = max(1, min(3, (int) ($input['manzil_paras_per_day'] ?? ($existing->manzil_paras_per_day ?? 1))));

        $pools = hifzComputeEnrollmentPools($sequence, $currentPara, $linesDone);
        $manzilPool = $pools['manzil_pool_paras'];
        $sabqiParas = $pools['sabqi_paras'];

        if ($existing) {
            $sabaqLines = max(1, min(30, (int) ($existing->sabaq_lines_per_day ?? ($campus->default_sabaq_lines ?? 5))));
            $mutaliaLines = max(1, min(30, (int) ($existing->mutalia_lines_per_day ?? ($campus->default_mutalia_lines ?? 3))));
        } else {
            $sabaqLines   = max(1, min(30, (int) ($campus->default_sabaq_lines ?? 5)));
            $mutaliaLines = max(1, min(30, (int) ($campus->default_mutalia_lines ?? 3)));
        }

        $poolFormatted   = hifzFormatJuzList($manzilPool) ?: null;
        $sabqiFormatted  = hifzFormatJuzList($sabqiParas) ?: null;

        $payload = [
            'hifz_sec_id'                 => $hifzSecId,
            'memorization_sequence'       => $sequence,
            'sabaq_lines_per_day'         => $sabaqLines,
            'mutalia_lines_per_day'       => $mutaliaLines,
            'manzil_paras_per_day'        => $manzilParasPerDay,
            'current_para_no'             => $currentPara,
            'current_juz'                 => $currentPara,
            'current_juz_memorized_lines' => $linesDone,
            'sabqi_active_paras'          => $sabqiFormatted,
            'manzil_pool_paras'           => $poolFormatted,
            'completed_juz_list'        => $poolFormatted,
            'status'                      => 1,
            'updated_date'                => $now,
            'user_id'                     => $userId,
        ];

        if ($existing) {
            if ((int) ($existing->status ?? 0) === 0) {
                $payload['enrollment_date']       = date('Y-m-d');
                $payload['manzil_rotation_index'] = 0;
            }

            try {
                $this->db->table('hifz_students')
                    ->where('id', $existing->id)
                    ->update($payload);
            } catch (\Throwable $e) {
                log_message('error', 'Hifz enrollment update: ' . $e->getMessage());

                return [
                    'success' => false,
                    'msg'     => 'Could not save Hifz plan order. Run database update or contact support. (' . $e->getMessage() . ')',
                ];
            }

            return ['success' => true, 'msg' => 'Hifz enrollment updated.'];
        }

        $payload['student_id']            = $studentId;
        $payload['campus_id']             = $campusId;
        $payload['session_id']            = $sessionId;
        $payload['manzil_rotation_index'] = 0;
        $payload['enrollment_date']       = date('Y-m-d');
        $payload['created_date']          = $now;

        try {
            $this->db->table('hifz_students')->insert($payload);
        } catch (\Throwable $e) {
            log_message('error', 'Hifz enrollment insert: ' . $e->getMessage());

            return [
                'success' => false,
                'msg'     => 'Could not save Hifz plan order. Run database update or contact support. (' . $e->getMessage() . ')',
            ];
        }

        return ['success' => true, 'msg' => 'Student enrolled in Hifz program.'];
    }

    public function withdraw(int $studentId, int $sessionId, int $userId, ?string $now = null): void
    {
        $now = $now ?? date('Y-m-d H:i:s');

        $this->db->table('hifz_students')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('status', 1)
            ->update([
                'status'       => 0,
                'updated_date' => $now,
                'user_id'      => $userId,
            ]);
    }

    /**
     * Validate academic class section belongs to campus Hifz class when enrolling.
     */
    public function validateHifzAcademicSection(int $clsSecId, ?object $campus): bool
    {
        if (! $campus) {
            return true;
        }

        $cs = $this->db->table('class_section cs')
            ->select('cs.class_id, c.is_hifz_class')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->where('cs.cls_sec_id', $clsSecId)
            ->get()
            ->getRow();

        if (! $cs) {
            return false;
        }

        $hifzClassId = (int) ($campus->hifz_class_id ?? 0);
        if ($hifzClassId <= 0) {
            return true;
        }

        return (int) $cs->class_id === $hifzClassId;
    }

    /**
     * @return list<int>
     */
    protected function parseParaList(string $csv): array
    {
        $list = hifzParseJuzList($csv);

        return array_values(array_unique(array_filter($list, static fn ($p) => $p >= 1 && $p <= 30)));
    }
}
