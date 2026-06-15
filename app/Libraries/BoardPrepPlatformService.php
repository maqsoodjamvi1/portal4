<?php

namespace App\Libraries;

use Config\BoardPrep;

/**
 * Ensures the platform system/campus, virtual class sections, and board_prep_platform row exist.
 */
class BoardPrepPlatformService
{
    protected $db;

    protected BoardPrep $config;

    public function __construct(?BoardPrep $config = null)
    {
        $this->db     = \Config\Database::connect();
        $this->config = $config ?? config('BoardPrep');
    }

    /**
     * @return array{system_id:int,campus_id:int,session_id:int,term_session_id:int,grade_cls_sec:array<string,int>}
     */
    public function ensurePlatform(): array
    {
        $existing = $this->loadPlatformRow();
        if ($existing !== null) {
            return $existing;
        }

        $this->db->transStart();
        $now = date('Y-m-d H:i:s');

        $this->db->table('system')->insert([
            'system_name'  => 'Timesoft Board Prep Platform',
            'owner_name'   => 'Timesoft Solutions',
            'mob_number'   => '',
            'address'      => '',
            'city'         => '',
            'reg_text'     => 'BPREP',
            'created_date' => $now,
            'user_id'      => 0,
        ]);
        $systemId = (int) $this->db->insertID();

        $campusData = [
            'system_id'    => $systemId,
            'campus_name'  => 'Board Prep Platform',
            'short_name'   => 'BPREP',
            'mobile_no'    => '',
            'location'     => '',
            'created_date' => $now,
            'user_id'      => 0,
        ];
        if ($this->fieldExists('campus', 's_flag')) {
            $campusData['s_flag'] = 1;
        }
        $this->db->table('campus')->insert($campusData);
        $campusId = (int) $this->db->insertID();

        $sessionName = date('Y') . '-' . ((int) date('Y') + 1);
        $sessionInsert = [
            'session_name' => $sessionName,
            'start_date'   => date('Y-01-01'),
            'end_date'     => date('Y-12-31'),
            'status'       => 1,
            'created_date' => $now,
            'user_id'      => 0,
        ];
        if ($this->fieldExists('academic_session', 'system_id')) {
            $sessionInsert['system_id'] = $systemId;
        }
        $this->db->table('academic_session')->insert($sessionInsert);
        $sessionId = (int) $this->db->insertID();

        $termSessionId = 0;
        if ($this->db->tableExists('terms') && $this->db->tableExists('terms_session')) {
            $termId = (int) ($this->db->table('terms')
                ->where('system_id', $systemId)
                ->orderBy('term_id', 'ASC')
                ->limit(1)
                ->get()
                ->getRow('term_id') ?? 0);

            if ($termId <= 0) {
                $termInsert = [
                    'system_id'    => $systemId,
                    'name'         => 'Board Prep',
                    'short_name'   => 'BP',
                    'status'       => 1,
                    'created_date' => $now,
                    'user_id'      => 0,
                ];
                if (! $this->fieldExists('terms', 'name') && $this->fieldExists('terms', 'term_name')) {
                    unset($termInsert['name']);
                    $termInsert['term_name'] = 'Board Prep';
                }
                $this->db->table('terms')->insert($termInsert);
                $termId = (int) $this->db->insertID();
            }

            if ($termId > 0) {
                $tsInsert = [
                    'term_id'      => $termId,
                    'session_id'   => $sessionId,
                    'start_date'   => date('Y-01-01'),
                    'end_date'     => date('Y-12-31'),
                    'status'       => 1,
                    'created_date' => $now,
                    'user_id'      => 0,
                ];
                if ($this->fieldExists('terms_session', 'system_id')) {
                    $tsInsert['system_id'] = $systemId;
                }
                $this->db->table('terms_session')->insert($tsInsert);
                $termSessionId = (int) $this->db->insertID();
            }
        }

        $sectionId = 0;
        if ($this->db->tableExists('sections')) {
            $sectionRow = $this->db->table('sections')
                ->where('system_id', $systemId)
                ->where('section_name', 'General')
                ->get()
                ->getRow();
            if ($sectionRow) {
                $sectionId = (int) $sectionRow->section_id;
            } else {
                $this->db->table('sections')->insert([
                    'system_id'      => $systemId,
                    'section_name'   => 'General',
                    'short_name'     => 'G',
                    'status'         => 1,
                    'created_date'   => $now,
                    'user_id'        => 0,
                ]);
                $sectionId = (int) $this->db->insertID();
            }
        }

        $gradeClsSec = [];
        foreach ($this->config->gradeClassNames as $gradeKey => $className) {
            $classId = $this->ensureClass($systemId, $className, $gradeKey, $now);
            $clsSecId = $this->ensureClassSection($campusId, $classId, $sectionId, $now);
            $gradeClsSec[$gradeKey] = $clsSecId;
        }

        $payload = [
            'id'                 => 1,
            'system_id'          => $systemId,
            'campus_id'          => $campusId,
            'session_id'         => $sessionId,
            'term_session_id'    => $termSessionId,
            'grade_cls_sec_json' => json_encode($gradeClsSec),
            'updated_at'         => $now,
        ];
        $this->db->table('board_prep_platform')->insert($payload);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Could not initialize board prep platform.');
        }

        return [
            'system_id'        => $systemId,
            'campus_id'        => $campusId,
            'session_id'       => $sessionId,
            'term_session_id'  => $termSessionId,
            'grade_cls_sec'    => $gradeClsSec,
        ];
    }

    /**
     * @return array{system_id:int,campus_id:int,session_id:int,term_session_id:int,grade_cls_sec:array<string,int>}|null
     */
    public function loadPlatformRow(): ?array
    {
        if (! $this->db->tableExists('board_prep_platform')) {
            return null;
        }

        $row = $this->db->table('board_prep_platform')->where('id', 1)->get()->getRow();
        if (! $row || (int) $row->campus_id <= 0) {
            return null;
        }

        $gradeMap = json_decode((string) ($row->grade_cls_sec_json ?? ''), true);

        return [
            'system_id'       => (int) $row->system_id,
            'campus_id'       => (int) $row->campus_id,
            'session_id'      => (int) $row->session_id,
            'term_session_id' => (int) $row->term_session_id,
            'grade_cls_sec'   => is_array($gradeMap) ? $gradeMap : [],
        ];
    }

    public function clsSecForGrade(string $gradeLevel): int
    {
        $platform = $this->loadPlatformRow() ?? $this->ensurePlatform();
        $map      = $platform['grade_cls_sec'] ?? [];

        return (int) ($map[$gradeLevel] ?? 0);
    }

    /**
     * @return list<object>
     */
    public function listBoardPublishers(): array
    {
        if (! $this->db->tableExists('qb_board_publishers')) {
            return [];
        }

        return $this->db->table('qb_board_publishers')
            ->where('system_id', 0)
            ->where('status', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResult();
    }

    private function ensureClass(int $systemId, string $className, string $gradeKey, string $now): int
    {
        $short = strtoupper(str_replace(['-', ' '], '', $gradeKey));
        $row   = $this->db->table('classes')
            ->where('system_id', $systemId)
            ->where('class_name', $className)
            ->get()
            ->getRow();

        if ($row) {
            return (int) $row->class_id;
        }

        $this->db->table('classes')->insert([
            'system_id'        => $systemId,
            'class_name'       => $className,
            'class_short_name' => $short,
            'detail'           => 'Board prep virtual class',
            'status'           => 1,
            'created_date'     => $now,
            'user_id'          => 0,
        ]);

        return (int) $this->db->insertID();
    }

    private function ensureClassSection(int $campusId, int $classId, int $sectionId, string $now): int
    {
        if ($classId <= 0) {
            return 0;
        }

        $builder = $this->db->table('class_section')
            ->where('campus_id', $campusId)
            ->where('class_id', $classId);

        if ($sectionId > 0) {
            $builder->where('section_id', $sectionId);
        }

        $row = $builder->get()->getRow();
        if ($row) {
            return (int) $row->cls_sec_id;
        }

        $insert = [
            'campus_id'    => $campusId,
            'class_id'     => $classId,
            'section_id'   => max(0, $sectionId),
            'status'       => 1,
            'created_date' => $now,
            'user_id'      => 0,
        ];
        $this->db->table('class_section')->insert($insert);

        return (int) $this->db->insertID();
    }

    private function fieldExists(string $table, string $column): bool
    {
        try {
            return $this->db->fieldExists($column, $table);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
