<?php

namespace App\Libraries;

use Config\Database;

class StudentSessionEnrollment
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Ensure all current students in a class have active student_class enrollment for the session.
     *
     * @return array{success: bool, activated: int, updated: int, skipped: int, total: int, messages: list<string>}
     */
    public function activateClassForSession(
        int $classId,
        int $sessionId,
        int $campusId,
        int $userId,
        int $forceClsSecId = 0
    ): array {
        if ($classId <= 0 || $sessionId <= 0 || $campusId <= 0) {
            return [
                'success'  => false,
                'activated' => 0,
                'updated'   => 0,
                'skipped'   => 0,
                'total'     => 0,
                'messages'  => ['Invalid class, session, or campus.'],
            ];
        }

        $classRow = $this->db->table('classes')
            ->select('class_id, class_name')
            ->where('class_id', $classId)
            ->get()
            ->getRow();

        if (! $classRow) {
            return [
                'success'  => false,
                'activated' => 0,
                'updated'   => 0,
                'skipped'   => 0,
                'total'     => 0,
                'messages'  => ['Class not found.'],
            ];
        }

        $sessionRow = $this->db->table('academic_session')
            ->select('session_id, session_name')
            ->where('session_id', $sessionId)
            ->get()
            ->getRow();

        if (! $sessionRow) {
            return [
                'success'  => false,
                'activated' => 0,
                'updated'   => 0,
                'skipped'   => 0,
                'total'     => 0,
                'messages'  => ['Academic session not found.'],
            ];
        }

        $students = $this->findClassStudents($classId, $campusId, $sessionId);
        $activated = 0;
        $updated   = 0;
        $skipped   = 0;
        $messages  = [];

        $this->db->transStart();

        try {
            foreach ($students as $student) {
                $studentId = (int) ($student->student_id ?? 0);
                if ($studentId <= 0) {
                    continue;
                }

                $clsSecId = $forceClsSecId > 0
                    ? $forceClsSecId
                    : $this->resolveClsSecIdForStudent(
                        $studentId,
                        $classId,
                        $campusId,
                        $sessionId,
                        (int) ($student->cls_sec_id ?? 0),
                        (int) ($student->class_id ?? 0)
                    );

                if ($clsSecId <= 0) {
                    $skipped++;
                    $messages[] = trim(($student->reg_no ?? '') . ' ' . ($student->first_name ?? '') . ' ' . ($student->last_name ?? ''))
                        . ': could not determine class section.';
                    continue;
                }

                $result = $this->ensureEnrollment($studentId, $sessionId, $clsSecId, $classId, $userId);

                if ($result === 'created') {
                    $activated++;
                } elseif ($result === 'updated') {
                    $updated++;
                } else {
                    $skipped++;
                }
            }

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Database transaction failed.');
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();

            return [
                'success'  => false,
                'activated' => $activated,
                'updated'   => $updated,
                'skipped'   => $skipped,
                'total'     => count($students),
                'messages'  => array_merge($messages, [$e->getMessage()]),
            ];
        }

        return [
            'success'   => true,
            'activated' => $activated,
            'updated'   => $updated,
            'skipped'   => $skipped,
            'total'     => count($students),
            'class_name' => (string) ($classRow->class_name ?? ''),
            'session_name' => (string) ($sessionRow->session_name ?? ''),
            'messages'  => $messages,
        ];
    }

    /**
     * @return list<object>
     */
    private function findClassStudents(int $classId, int $campusId, int $sessionId): array
    {
        return $this->db->table('students s')
            ->select('s.student_id, s.reg_no, s.first_name, s.last_name, s.cls_sec_id, s.class_id', false)
            ->where('s.campus_id', $campusId)
            ->where('s.status', 1)
            ->groupStart()
                ->where('s.class_id', $classId)
                ->orWhere(
                    'EXISTS (
                        SELECT 1 FROM class_section cs
                        WHERE cs.cls_sec_id = s.cls_sec_id
                          AND cs.class_id = ' . (int) $classId . '
                          AND cs.campus_id = ' . (int) $campusId . '
                    )',
                    null,
                    false
                )
                ->orWhere(
                    'EXISTS (
                        SELECT 1 FROM student_class sc
                        INNER JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
                        WHERE sc.student_id = s.student_id
                          AND cs.class_id = ' . (int) $classId . '
                          AND cs.campus_id = ' . (int) $campusId . '
                    )',
                    null,
                    false
                )
                ->orWhere(
                    'EXISTS (
                        SELECT 1 FROM student_class sc
                        INNER JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
                        WHERE sc.student_id = s.student_id
                          AND sc.session_id = ' . (int) $sessionId . '
                          AND cs.class_id = ' . (int) $classId . '
                          AND cs.campus_id = ' . (int) $campusId . '
                    )',
                    null,
                    false
                )
            ->groupEnd()
            ->orderBy('s.first_name', 'ASC')
            ->orderBy('s.last_name', 'ASC')
            ->get()
            ->getResult();
    }

    private function resolveClsSecIdForStudent(
        int $studentId,
        int $classId,
        int $campusId,
        int $sessionId,
        int $studentClsSecId,
        int $studentClassId
    ): int {
        if ($studentClsSecId > 0) {
            $section = $this->db->table('class_section')
                ->select('cls_sec_id')
                ->where('cls_sec_id', $studentClsSecId)
                ->where('class_id', $classId)
                ->where('campus_id', $campusId)
                ->where('status', 1)
                ->get()
                ->getRow();

            if ($section) {
                return (int) $section->cls_sec_id;
            }
        }

        $priorEnrollment = $this->db->table('student_class sc')
            ->select('sc.cls_sec_id')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'inner')
            ->where('sc.student_id', $studentId)
            ->where('cs.class_id', $classId)
            ->where('cs.campus_id', $campusId)
            ->orderBy('sc.session_id', 'DESC')
            ->orderBy('sc.status', 'DESC')
            ->orderBy('sc.sc_id', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();

        if ($priorEnrollment) {
            return (int) $priorEnrollment->cls_sec_id;
        }

        if ($studentClassId === $classId) {
            $defaultSection = $this->db->table('class_section')
                ->select('cls_sec_id')
                ->where('class_id', $classId)
                ->where('campus_id', $campusId)
                ->where('status', 1)
                ->orderBy('section_id', 'ASC')
                ->limit(1)
                ->get()
                ->getRow();

            if ($defaultSection) {
                return (int) $defaultSection->cls_sec_id;
            }
        }

        return 0;
    }

    private function ensureEnrollment(
        int $studentId,
        int $sessionId,
        int $clsSecId,
        int $classId,
        int $userId
    ): string {
        $now = date('Y-m-d H:i:s');

        $rows = $this->db->table('student_class')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->orderBy('status', 'DESC')
            ->orderBy('sc_id', 'DESC')
            ->get()
            ->getResult();

        $targetRow = null;
        foreach ($rows as $row) {
            if ((int) ($row->cls_sec_id ?? 0) === $clsSecId) {
                $targetRow = $row;
                break;
            }
        }

        if ($targetRow === null && $rows !== []) {
            $targetRow = $rows[0];
        }

        if ($targetRow !== null) {
            $needsUpdate = ((int) ($targetRow->status ?? 0) !== 1)
                || ((int) ($targetRow->cls_sec_id ?? 0) !== $clsSecId);

            if ($needsUpdate) {
                $this->db->table('student_class')
                    ->where('sc_id', (int) $targetRow->sc_id)
                    ->update([
                        'cls_sec_id'   => $clsSecId,
                        'status'       => 1,
                        'updated_date' => $now,
                        'user_id'      => $userId,
                    ]);
            }

            foreach ($rows as $row) {
                if ((int) $row->sc_id === (int) $targetRow->sc_id) {
                    continue;
                }

                if ((int) ($row->status ?? 0) === 1) {
                    $this->db->table('student_class')
                        ->where('sc_id', (int) $row->sc_id)
                        ->update([
                            'status'       => 0,
                            'updated_date' => $now,
                            'user_id'      => $userId,
                        ]);
                }
            }
        } else {
            $this->db->table('student_class')->insert([
                'student_id'   => $studentId,
                'session_id'   => $sessionId,
                'cls_sec_id'   => $clsSecId,
                'status'       => 1,
                'created_date' => $now,
                'updated_date' => $now,
                'user_id'      => $userId,
            ]);
        }

        $this->db->table('students')
            ->where('student_id', $studentId)
            ->update([
                'class_id'     => $classId,
                'cls_sec_id'   => $clsSecId,
                'session_id'   => $sessionId,
                'updated_date' => $now,
            ]);

        if ($targetRow === null) {
            return 'created';
        }

        $needsUpdate = ((int) ($targetRow->status ?? 0) !== 1)
            || ((int) ($targetRow->cls_sec_id ?? 0) !== $clsSecId);

        return $needsUpdate ? 'updated' : 'skipped';
    }
}
