<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Timetable extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url', 'school']);
        $this->session = session();
        $this->db = \Config\Database::connect();
        check_permission('admin-timetable');
    }

    public function index()
    {
        //check_permission('admin-view-timetable');
        return $this->viewTimetable();
    }

public function add()
{
    return redirect()->to(base_url('admin/timetable/generator'));
}

public function generator()
{
    check_permission('admin-add-timetable');
    $campusId = (int) $this->session->get('member_campusid');
    if ($campusId <= 0) {
        return redirect()->back()->with('error', 'Campus not found in session.');
    }

    return view('admin/timetable_generator', [
        'title' => 'Timetable Constraints',
    ]);
}

public function generatorBootstrap()
{
    check_permission('admin-add-timetable');
    $campusId = (int) $this->session->get('member_campusid');
    if ($campusId <= 0) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Campus not found in session.',
        ]);
    }

    $this->ensureGeneratorTables();
    $this->ensureSectionSubjectsWeeklyColumn();
    $this->ensureSectionSubjectsTimetableColumn();
    $this->ensureClassSectionTimetableColumn();
    $this->ensureSlotsBreakColumn();

    $slots = $this->fetchSlotsForCampus($campusId);
    $teachingSlotIds = $this->teachingSlotIdsFromList($slots);

    $options = $this->loadGeneratorOptions($campusId, $teachingSlotIds);
    $rows = $this->buildGeneratorDemandRows($campusId);
    $capacityBySection = $this->capacityBySection($campusId, $teachingSlotIds, $options['friday_active_slots']);
    $validation = $this->validateGeneratorDemandRows(
        $this->filterIncludedGeneratorDemandRows($rows),
        $capacityBySection
    );
    $missingSections = $this->findClassSectionsMissingSubjects($campusId);

    return $this->response->setJSON([
        'success' => true,
        'options' => $options,
        'slots' => $slots,
        'teaching_slot_ids' => $teachingSlotIds,
        'rows' => $rows,
        'capacity' => $capacityBySection,
        'validation' => $validation,
        'missing_sections' => $missingSections,
        'days' => $this->canonicalWeekdayOrder(),
    ]);
}

public function saveGeneratorSlots()
{
    check_permission('admin-add-timetable');
    $campusId = (int) $this->session->get('member_campusid');
    if ($campusId <= 0) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Campus not found in session.',
        ]);
    }

    $this->ensureSlotsBreakColumn();

    $dayStart = trim((string) $this->request->getPost('day_start'));
    $raw = $this->request->getPost('slots');
    if (is_string($raw)) {
        $raw = json_decode($raw, true);
    }
    $raw = is_array($raw) ? $raw : [];
    if ($raw === []) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'No slot data provided.',
        ]);
    }

    $resolved = $this->resolveSlotTimesFromBells($dayStart, $raw);
    if (!empty($resolved['errors'])) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => implode(' ', $resolved['errors']),
        ]);
    }

    $userId = (int) ($this->session->get('member_userid') ?? 0);
    $now = date('Y-m-d H:i:s');
    $keptIds = [];
    $errors = [];

    $this->db->transBegin();
    try {
        foreach ($resolved['rows'] as $i => $row) {
            $slotId = (int) ($row['slot_id'] ?? 0);
            $slotName = trim((string) ($row['slot_name'] ?? ''));
            $startTime = trim((string) ($row['start_time'] ?? ''));
            $endTime = trim((string) ($row['end_time'] ?? ''));
            $isBreak = ((int) ($row['is_break'] ?? 0)) === 1 ? 1 : 0;

            if ($slotName === '' || $startTime === '' || $endTime === '') {
                $errors[] = 'Row ' . ($i + 1) . ': name and bell times are required.';
                continue;
            }

            $data = [
                'slot_name' => $slotName,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_break' => $isBreak,
                'slot_type' => trim((string) ($row['slot_type'] ?? 'FullDay')) ?: 'FullDay',
                'campus_id' => $campusId,
                'user_id' => $userId > 0 ? $userId : null,
            ];

            if ($slotId > 0) {
                $existing = $this->db->table('slots')
                    ->where('slot_id', $slotId)
                    ->where('campus_id', $campusId)
                    ->get()
                    ->getRowArray();
                if (!$existing) {
                    $errors[] = 'Row ' . ($i + 1) . ': slot not found for this campus.';
                    continue;
                }
                $data['updated_date'] = $now;
                $this->db->table('slots')->where('slot_id', $slotId)->update($data);
                $keptIds[] = $slotId;
            } else {
                $data['created_date'] = $now;
                $this->db->table('slots')->insert($data);
                $keptIds[] = (int) $this->db->insertID();
            }
        }

        if ($errors !== []) {
            $this->db->transRollback();

            return $this->response->setJSON([
                'success' => false,
                'msg' => implode(' ', $errors),
            ]);
        }

        $existingIds = array_map(
            static fn($r) => (int) ($r['slot_id'] ?? 0),
            $this->db->table('slots')->select('slot_id')->where('campus_id', $campusId)->get()->getResultArray()
        );
        $removeIds = array_values(array_diff($existingIds, $keptIds));
        foreach ($removeIds as $removeId) {
            $inUse = (int) $this->db->table('time_table')->where('slot_id', $removeId)->countAllResults();
            if ($inUse > 0) {
                $this->db->transRollback();

                return $this->response->setJSON([
                    'success' => false,
                    'msg' => 'Cannot remove slot #' . $removeId . ' — it is used in the timetable. Clear assignments first.',
                ]);
            }
            $this->db->table('slots')->where('slot_id', $removeId)->where('campus_id', $campusId)->delete();
        }

        $this->db->transCommit();
    } catch (\Throwable $e) {
        $this->db->transRollback();

        return $this->response->setJSON([
            'success' => false,
            'msg' => $e->getMessage(),
        ]);
    }

    $slots = $this->fetchSlotsForCampus($campusId);
    $teachingSlotIds = $this->teachingSlotIdsFromList($slots);
    $options = $this->loadGeneratorOptions($campusId, $teachingSlotIds);
    $capacityBySection = $this->capacityBySection($campusId, $teachingSlotIds, $options['friday_active_slots']);
    $rows = $this->buildGeneratorDemandRows($campusId);
    $validation = $this->validateGeneratorDemandRows(
        $this->filterIncludedGeneratorDemandRows($rows),
        $capacityBySection
    );

    return $this->response->setJSON([
        'success' => true,
        'msg' => 'Period slots saved.',
        'slots' => $slots,
        'teaching_slot_ids' => $teachingSlotIds,
        'options' => $options,
        'capacity' => $capacityBySection,
        'validation' => $validation,
    ]);
}

public function saveGeneratorConstraints()
{
    check_permission('admin-add-timetable');
    $campusId = (int) $this->session->get('member_campusid');
    if ($campusId <= 0) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Campus not found in session.',
        ]);
    }

    $this->ensureGeneratorTables();
    $this->ensureSectionSubjectsWeeklyColumn();
    $this->ensureSectionSubjectsTimetableColumn();
    $this->ensureClassSectionTimetableColumn();
    $this->ensureSlotsBreakColumn();

    $optionsRaw = $this->request->getPost('options');
    $rowsRaw = $this->request->getPost('rows');
    $sectionsRaw = $this->request->getPost('sections');
    if (is_string($optionsRaw)) {
        $optionsRaw = json_decode($optionsRaw, true);
    }
    if (is_string($rowsRaw)) {
        $rowsRaw = json_decode($rowsRaw, true);
    }
    if (is_string($sectionsRaw)) {
        $sectionsRaw = json_decode($sectionsRaw, true);
    }
    $optionsRaw = is_array($optionsRaw) ? $optionsRaw : [];
    $rowsRaw = is_array($rowsRaw) ? $rowsRaw : [];
    $sectionsRaw = is_array($sectionsRaw) ? $sectionsRaw : [];

    $sectionIncludeMap = [];
    foreach ($sectionsRaw as $sec) {
        $clsSecId = (int)($sec['cls_sec_id'] ?? 0);
        if ($clsSecId <= 0) {
            continue;
        }
        $sectionIncludeMap[$clsSecId] = ((int)($sec['include_in_timetable'] ?? 0)) === 1 ? 1 : 0;
    }

    $slots = $this->fetchSlotsForCampus($campusId);
    $slotIds = $this->teachingSlotIdsFromList($slots);

    $fridayActive = $optionsRaw['friday_active_slots'] ?? [];
    if (is_string($fridayActive)) {
        $fridayActive = json_decode($fridayActive, true);
    }
    $fridayActive = is_array($fridayActive) ? $fridayActive : [];
    $fridayActive = array_values(array_unique(array_map('intval', $fridayActive)));
    $fridayActive = array_values(array_filter($fridayActive, static fn($sid) => in_array($sid, $slotIds, true)));
    if ($fridayActive === []) {
        $fridayActive = $slotIds;
    }

    $normalizedRows = [];
    $demandRows = [];
    foreach ($rowsRaw as $r) {
        $clsSecId = (int)($r['cls_sec_id'] ?? 0);
        $subjectId = (int)($r['subject_id'] ?? 0);
        $weekly = (int)($r['weekly_classes'] ?? 0);
        $include = ((int)($r['include_in_timetable'] ?? 0)) === 1 ? 1 : 0;
        if ($clsSecId <= 0 || $subjectId <= 0) {
            continue;
        }
        if ($weekly < 0) {
            $weekly = 0;
        }
        $normalizedRows[] = [
            'cls_sec_id' => $clsSecId,
            'subject_id' => $subjectId,
            'weekly_classes' => $weekly,
            'include_in_timetable' => $include,
        ];
        if ($include === 1 && (($sectionIncludeMap[$clsSecId] ?? 1) === 1)) {
            $demandRows[] = [
                'cls_sec_id' => $clsSecId,
                'subject_id' => $subjectId,
                'weekly_classes' => $weekly,
            ];
        }
    }

    $capacityBySection = $this->capacityBySection($campusId, $slotIds, $fridayActive);
    $validation = $this->validateGeneratorDemandRows($demandRows, $capacityBySection);

    $userId = (int)($this->session->get('member_userid') ?? 0);
    $now = date('Y-m-d H:i:s');
    $useMondayTemplate = ((int)($optionsRaw['use_monday_template'] ?? 0)) === 1 ? 1 : 0;
    $allowForce = ((int)($optionsRaw['allow_force_place'] ?? 0)) === 1 ? 1 : 0;
    $strictMode = ((int)($optionsRaw['strict_mode'] ?? 1)) === 1 ? 1 : 0;

    $this->db->transBegin();
    try {
        $optRow = $this->db->table('timetable_generator_options')
            ->where('campus_id', $campusId)
            ->get()->getRowArray();

        $optData = [
            'campus_id' => $campusId,
            'use_monday_template' => $useMondayTemplate,
            'friday_active_slots' => json_encode($fridayActive),
            'allow_force_place' => $allowForce,
            'strict_mode' => $strictMode,
            'updated_by' => $userId > 0 ? $userId : null,
            'updated_at' => $now,
        ];

        if ($optRow) {
            $this->db->table('timetable_generator_options')
                ->where('campus_id', $campusId)
                ->update($optData);
        } else {
            $optData['created_at'] = $now;
            $this->db->table('timetable_generator_options')->insert($optData);
        }

        $this->db->table('timetable_generator_demands')
            ->where('campus_id', $campusId)
            ->delete();

        foreach ($normalizedRows as $r) {
            $clsSecId = (int)$r['cls_sec_id'];
            $sectionIncluded = ($sectionIncludeMap[$clsSecId] ?? 1) === 1;
            if ($sectionIncluded && (int)($r['include_in_timetable'] ?? 0) === 1) {
                $this->db->table('timetable_generator_demands')->insert([
                    'campus_id' => $campusId,
                    'cls_sec_id' => $clsSecId,
                    'subject_id' => $r['subject_id'],
                    'weekly_classes' => $r['weekly_classes'],
                    'created_at' => $now,
                    'updated_at' => $now,
                    'updated_by' => $userId > 0 ? $userId : null,
                ]);
            }

            $this->db->table('section_subjects')
                ->where('cls_sec_id', $clsSecId)
                ->where('subject_id', $r['subject_id'])
                ->update([
                    'classes_per_week' => (int)$r['weekly_classes'],
                    'include_in_timetable' => (int)($r['include_in_timetable'] ?? 0),
                    'user_id' => $userId,
                ]);
        }

        foreach ($sectionIncludeMap as $clsSecId => $sectionIncluded) {
            $this->db->table('class_section')
                ->where('cls_sec_id', (int)$clsSecId)
                ->where('campus_id', $campusId)
                ->update([
                    'include_in_timetable' => (int)$sectionIncluded,
                ]);
        }

        $this->db->transCommit();
    } catch (\Throwable $e) {
        $this->db->transRollback();
        return $this->response->setJSON([
            'success' => false,
            'msg' => $e->getMessage(),
        ]);
    }

    return $this->response->setJSON([
        'success' => true,
        'msg' => !empty($validation['overflow'])
            ? 'Constraints saved. Some sections exceed capacity; adjust before generating.'
            : 'Generator constraints saved successfully.',
        'validation' => $validation,
    ]);
}

private function ensureGeneratorTables(): void
{
    $this->db->query("
        CREATE TABLE IF NOT EXISTS timetable_generator_options (
            id INT AUTO_INCREMENT PRIMARY KEY,
            campus_id INT NOT NULL UNIQUE,
            use_monday_template TINYINT(1) NOT NULL DEFAULT 0,
            friday_active_slots TEXT NULL,
            allow_force_place TINYINT(1) NOT NULL DEFAULT 0,
            strict_mode TINYINT(1) NOT NULL DEFAULT 1,
            updated_by INT NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        )
    ");

    $this->db->query("
        CREATE TABLE IF NOT EXISTS timetable_generator_demands (
            id INT AUTO_INCREMENT PRIMARY KEY,
            campus_id INT NOT NULL,
            cls_sec_id INT NOT NULL,
            subject_id INT NOT NULL,
            weekly_classes INT NOT NULL DEFAULT 0,
            updated_by INT NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            UNIQUE KEY uniq_campus_section_subject (campus_id, cls_sec_id, subject_id)
        )
    ");
}

private function ensureSectionSubjectsWeeklyColumn(): void
{
    try {
        if (! $this->db->fieldExists('classes_per_week', 'section_subjects')) {
            $this->db->query("ALTER TABLE section_subjects ADD COLUMN classes_per_week INT NOT NULL DEFAULT 0");
        }
    } catch (\Throwable $e) {
        log_message('error', 'Timetable::ensureSectionSubjectsWeeklyColumn failed: ' . $e->getMessage());
    }
}

private function ensureClassSectionTimetableColumn(): void
{
    try {
        $added = false;
        if (! $this->db->fieldExists('include_in_timetable', 'class_section')) {
            $this->db->query("ALTER TABLE class_section ADD COLUMN include_in_timetable TINYINT(1) NOT NULL DEFAULT 1");
            $added = true;
        }

        if ($added) {
            $this->db->query("
                UPDATE class_section cs
                SET cs.include_in_timetable = 1
                WHERE EXISTS (
                    SELECT 1
                    FROM timetable_generator_demands d
                    WHERE d.cls_sec_id = cs.cls_sec_id
                )
                   OR EXISTS (
                    SELECT 1
                    FROM section_subjects ss
                    WHERE ss.cls_sec_id = cs.cls_sec_id
                      AND ss.include_in_timetable = 1
                )
            ");
        }
    } catch (\Throwable $e) {
        log_message('error', 'Timetable::ensureClassSectionTimetableColumn failed: ' . $e->getMessage());
    }
}

private function ensureSectionSubjectsTimetableColumn(): void
{
    try {
        $added = false;
        if (! $this->db->fieldExists('include_in_timetable', 'section_subjects')) {
            $this->db->query("ALTER TABLE section_subjects ADD COLUMN include_in_timetable TINYINT(1) NOT NULL DEFAULT 0");
            $added = true;
        }

        if ($added) {
            $this->db->query("
                UPDATE section_subjects ss
                SET ss.include_in_timetable = 1
                WHERE ss.classes_per_week > 0
                   OR EXISTS (
                        SELECT 1
                        FROM timetable_generator_demands d
                        WHERE d.cls_sec_id = ss.cls_sec_id
                          AND d.subject_id = ss.subject_id
                   )
            ");
        }
    } catch (\Throwable $e) {
        log_message('error', 'Timetable::ensureSectionSubjectsTimetableColumn failed: ' . $e->getMessage());
    }
}

private function findClassSectionsMissingSubjects(int $campusId): array
{
    $sections = $this->db->table('class_section cs')
        ->select('cs.cls_sec_id, c.class_name, c.class_short_name, s.section_name, s.short_name AS section_short_name')
        ->join('classes c', 'c.class_id = cs.class_id', 'inner')
        ->join('sections s', 's.section_id = cs.section_id', 'inner')
        ->where('cs.campus_id', $campusId)
        ->where('cs.status', 1)
        ->where('c.status', 1)
        ->where('s.status', 1)
        ->orderBy('cs.class_id', 'ASC')
        ->orderBy('cs.section_id', 'ASC')
        ->get()
        ->getResultArray();

    $out = [];
    foreach ($sections as $sec) {
        $cid = (int)($sec['cls_sec_id'] ?? 0);
        if ($cid <= 0) {
            continue;
        }
        $count = (int)$this->db->table('section_subjects')
            ->where('cls_sec_id', $cid)
            ->where('status', 1)
            ->countAllResults();
        if ($count === 0) {
            $out[] = [
                'cls_sec_id' => $cid,
                'label' => $this->formatClassSectionShort(
                    $sec['class_short_name'] ?? null,
                    $sec['class_name'] ?? null,
                    $sec['section_short_name'] ?? null,
                    $sec['section_name'] ?? null
                ),
            ];
        }
    }
    return $out;
}

private function loadGeneratorOptions(int $campusId, array $allSlotIds): array
{
    $row = $this->db->table('timetable_generator_options')
        ->where('campus_id', $campusId)
        ->get()->getRowArray();

    $friday = $allSlotIds;
    if ($row && !empty($row['friday_active_slots'])) {
        $raw = json_decode((string)$row['friday_active_slots'], true);
        if (is_array($raw)) {
            $friday = array_values(array_unique(array_map('intval', $raw)));
            $friday = array_values(array_filter($friday, static fn($sid) => in_array($sid, $allSlotIds, true)));
            if ($friday === []) {
                $friday = $allSlotIds;
            }
        }
    }

    return [
        'use_monday_template' => ((int)($row['use_monday_template'] ?? 0)) === 1 ? 1 : 0,
        'allow_force_place' => ((int)($row['allow_force_place'] ?? 0)) === 1 ? 1 : 0,
        'strict_mode' => ((int)($row['strict_mode'] ?? 1)) === 1 ? 1 : 0,
        'friday_active_slots' => $friday,
    ];
}

private function buildGeneratorDemandRows(int $campusId): array
{
    $sections = $this->db->table('class_section cs')
        ->select('cs.cls_sec_id, cs.class_id, cs.section_id, c.class_name, c.class_short_name, s.section_name, s.short_name AS section_short_name')
        ->join('classes c', 'c.class_id = cs.class_id', 'inner')
        ->join('sections s', 's.section_id = cs.section_id', 'inner')
        ->where('cs.campus_id', $campusId)
        ->where('cs.status', 1)
        ->where('c.status', 1)
        ->where('s.status', 1)
        ->orderBy('cs.class_id', 'ASC')
        ->orderBy('cs.section_id', 'ASC')
        ->get()
        ->getResultArray();

    if (empty($sections)) {
        return [];
    }

    $hasSectionIncludeColumn = $this->db->fieldExists('include_in_timetable', 'class_section');
    $sectionIncludeById = [];
    if ($hasSectionIncludeColumn) {
        $sectionRows = $this->db->table('class_section')
            ->select('cls_sec_id, include_in_timetable')
            ->where('campus_id', $campusId)
            ->where('status', 1)
            ->get()
            ->getResultArray();
        foreach ($sectionRows as $secRow) {
            $sectionIncludeById[(int)$secRow['cls_sec_id']] = ((int)($secRow['include_in_timetable'] ?? 1)) === 1 ? 1 : 0;
        }
    }

    $demands = $this->db->table('timetable_generator_demands')
        ->select('cls_sec_id, subject_id, weekly_classes')
        ->where('campus_id', $campusId)
        ->get()
        ->getResultArray();
    $dMap = [];
    foreach ($demands as $d) {
        $dMap[(int)$d['cls_sec_id'] . '|' . (int)$d['subject_id']] = max(0, (int)$d['weekly_classes']);
    }

    $hasIncludeColumn = $this->db->fieldExists('include_in_timetable', 'section_subjects');
    $rows = [];
    foreach ($sections as $sec) {
        $clsSecId = (int)$sec['cls_sec_id'];
        $classId = (int)($sec['class_id'] ?? 0);
        $sectionId = (int)($sec['section_id'] ?? 0);
        $select = 'ss.subject_id, COALESCE(ss.classes_per_week, 0) as default_weekly_classes, sub.subject_name, sub.subject_short_name';
        if ($hasIncludeColumn) {
            $select .= ', COALESCE(ss.include_in_timetable, 0) as include_in_timetable';
        }
        $subjects = $this->db->table('section_subjects ss')
            ->select($select)
            ->join('allsubject sub', 'sub.sid = ss.subject_id', 'inner')
            ->where('ss.cls_sec_id', $clsSecId)
            ->where('ss.status', 1)
            ->where('sub.status', 1)
            ->orderBy('sub.subject_name', 'ASC')
            ->get()
            ->getResultArray();
        if (empty($subjects)) {
            // Show only class-section assigned subjects. If none are assigned, skip this section.
            continue;
        }

        foreach ($subjects as $sub) {
            $sid = (int)$sub['subject_id'];
            $key = $clsSecId . '|' . $sid;
            $defaultWeekly = max(0, (int)($sub['default_weekly_classes'] ?? 0));
            $weekly = (int)($dMap[$key] ?? $defaultWeekly);
            if ($hasIncludeColumn) {
                $include = ((int)($sub['include_in_timetable'] ?? 0)) === 1 ? 1 : 0;
            } elseif (array_key_exists($key, $dMap)) {
                $include = 1;
            } else {
                $include = $defaultWeekly > 0 ? 1 : 0;
            }
            $rows[] = [
                'cls_sec_id' => $clsSecId,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'class_name' => (string)$sec['class_name'],
                'class_short_name' => (string)($sec['class_short_name'] ?? ''),
                'section_name' => (string)$sec['section_name'],
                'section_short_name' => (string)($sec['section_short_name'] ?? ''),
                'section_label' => $this->formatClassSectionShort(
                    $sec['class_short_name'] ?? null,
                    $sec['class_name'] ?? null,
                    $sec['section_short_name'] ?? null,
                    $sec['section_name'] ?? null
                ),
                'section_include_in_timetable' => (int)($sectionIncludeById[$clsSecId] ?? 1),
                'subject_id' => $sid,
                'subject_name' => (string)($sub['subject_name'] ?? ''),
                'subject_short_name' => (string)($sub['subject_short_name'] ?? ''),
                'include_in_timetable' => $include,
                'weekly_classes' => $weekly,
            ];
        }
    }

    return $rows;
}

private function capacityBySection(int $campusId, array $allSlotIds, array $fridayActiveSlotIds): array
{
    $sections = $this->db->table('class_section')
        ->select('cls_sec_id')
        ->where('campus_id', $campusId)
        ->where('status', 1)
        ->get()
        ->getResultArray();

    $allSlotsCount = count($allSlotIds);
    $fridayCount = count($fridayActiveSlotIds);

    $out = [];
    foreach ($sections as $sec) {
        $clsSecId = (int)$sec['cls_sec_id'];
        $days = $this->resolveWorkingDayNamesForSection($campusId, $clsSecId);
        $capacity = 0;
        foreach ($days as $day) {
            if (strcasecmp($day, 'Friday') === 0) {
                $capacity += $fridayCount;
            } else {
                $capacity += $allSlotsCount;
            }
        }
        $out[$clsSecId] = [
            'cls_sec_id' => $clsSecId,
            'days' => $days,
            'capacity' => $capacity,
        ];
    }

    return $out;
}

private function filterIncludedGeneratorDemandRows(array $rows): array
{
    $out = [];
    foreach ($rows as $r) {
        if (((int)($r['section_include_in_timetable'] ?? 1)) !== 1) {
            continue;
        }
        if (((int)($r['include_in_timetable'] ?? 0)) !== 1) {
            continue;
        }
        $out[] = [
            'cls_sec_id' => (int)($r['cls_sec_id'] ?? 0),
            'subject_id' => (int)($r['subject_id'] ?? 0),
            'weekly_classes' => (int)($r['weekly_classes'] ?? 0),
        ];
    }

    return $out;
}

private function validateGeneratorDemandRows(array $rows, array $capacityBySection): array
{
    $sumBySection = [];
    foreach ($rows as $r) {
        $cid = (int)($r['cls_sec_id'] ?? 0);
        $sumBySection[$cid] = ($sumBySection[$cid] ?? 0) + max(0, (int)($r['weekly_classes'] ?? 0));
    }

    $overflow = [];
    foreach ($sumBySection as $cid => $sum) {
        $cap = (int)($capacityBySection[$cid]['capacity'] ?? 0);
        if ($sum > $cap) {
            $overflow[] = [
                'cls_sec_id' => (int)$cid,
                'requested' => (int)$sum,
                'capacity' => $cap,
            ];
        }
    }

    return [
        'sum_by_section' => $sumBySection,
        'overflow' => $overflow,
    ];
}

public function generateFromConstraints()
{
    check_permission('admin-add-timetable');
    $campusId = (int)$this->session->get('member_campusid');
    if ($campusId <= 0) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Campus not found in session.']);
    }

    $this->ensureGeneratorTables();
    $this->ensureSectionSubjectsWeeklyColumn();
    $this->ensureSectionSubjectsTimetableColumn();
    $this->ensureClassSectionTimetableColumn();
    $this->ensureSlotsBreakColumn();
    $slots = $this->fetchSlotsForCampus($campusId);
    $slotIds = $this->teachingSlotIdsFromList($slots);
    if ($slotIds === []) {
        return $this->response->setJSON(['success' => false, 'msg' => 'No teaching period slots found. Add slots in Settings (non-break periods).']);
    }

    $options = $this->loadGeneratorOptions($campusId, $slotIds);
    $demands = $this->db->table('timetable_generator_demands')
        ->where('campus_id', $campusId)
        ->get()->getResultArray();
    if ($demands === []) {
        $missingSections = $this->findClassSectionsMissingSubjects($campusId);
        $missingLabels = array_map(static fn($m) => (string)($m['label'] ?? ''), $missingSections);
        $missingHint = '';
        if ($missingLabels !== []) {
            $preview = implode(', ', array_slice($missingLabels, 0, 8));
            if (count($missingLabels) > 8) {
                $preview .= ' ...';
            }
            $missingHint = ' Missing section-subject setup for: ' . $preview;
        }
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'No timetable subjects selected. Check "Include in timetable" for subjects, set classes per week, then save constraints.' . $missingHint,
            'missing_sections' => $missingSections,
        ]);
    }

    $capacity = $this->capacityBySection($campusId, $slotIds, $options['friday_active_slots']);
    $validation = $this->validateGeneratorDemandRows($demands, $capacity);
    if (!empty($validation['overflow'])) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Constraint overflow exists. Reduce weekly classes first.',
            'validation' => $validation,
        ]);
    }

    // Demand map and metadata
    $remaining = [];
    foreach ($demands as $d) {
        $cid = (int)$d['cls_sec_id'];
        $sid = (int)$d['subject_id'];
        $cnt = max(0, (int)$d['weekly_classes']);
        if (!isset($remaining[$cid])) {
            $remaining[$cid] = [];
        }
        $remaining[$cid][$sid] = $cnt;
    }

    // Snapshot for Monday-template: how many distinct Monday *columns* each subject may use (ceil(weekly / working_days))
    $initialWeeklyBySection = [];
    foreach ($remaining as $cid0 => $subMap0) {
        $cid0 = (int) $cid0;
        foreach ($subMap0 as $sid0 => $cnt0) {
            $initialWeeklyBySection[$cid0][(int) $sid0] = (int) $cnt0;
        }
    }

    $sections = $this->db->table('class_section cs')
        ->select('cs.cls_sec_id')
        ->join('classes c', 'c.class_id = cs.class_id', 'inner')
        ->join('sections s', 's.section_id = cs.section_id', 'inner')
        ->where('cs.campus_id', $campusId)
        ->where('cs.status', 1)
        ->where('c.status', 1)
        ->where('s.status', 1)
        ->orderBy('cs.class_id', 'ASC')
        ->orderBy('cs.section_id', 'ASC')
        ->get()->getResultArray();
    $sectionIds = array_values(array_map(static fn($r) => (int)$r['cls_sec_id'], $sections));
    if ($sectionIds === []) {
        return $this->response->setJSON(['success' => false, 'msg' => 'No active class-sections found.']);
    }

    $teacherMap = [];
    foreach ($sectionIds as $cid) {
        $rows = $this->db->query("
            SELECT ss.subject_id, ts.tid
            FROM section_subjects ss
            LEFT JOIN (
                SELECT t1.cls_sec_id, t1.sec_sub_id, t1.tid
                FROM teacher_subjects t1
                JOIN (
                    SELECT cls_sec_id, sec_sub_id, MAX(sst) AS max_sst
                    FROM teacher_subjects
                    WHERE status = 1
                    GROUP BY cls_sec_id, sec_sub_id
                ) t2
                  ON t2.cls_sec_id = t1.cls_sec_id
                 AND t2.sec_sub_id = t1.sec_sub_id
                 AND t2.max_sst = t1.sst
            ) ts ON ts.cls_sec_id = ss.cls_sec_id AND ts.sec_sub_id = ss.sec_sub_id
            WHERE ss.cls_sec_id = ? AND ss.status = 1
        ", [$cid])->getResultArray();
        foreach ($rows as $r) {
            $teacherMap[$cid . '|' . (int)$r['subject_id']] = (int)($r['tid'] ?? 0);
        }
    }

    $daysBySection = [];
    foreach ($sectionIds as $cid) {
        $daysBySection[$cid] = $this->resolveWorkingDayNamesForSection($campusId, $cid);
    }

    $fridaySlots = array_values(array_filter(array_map('intval', (array)$options['friday_active_slots']), static fn($v) => in_array($v, $slotIds, true)));
    if ($fridaySlots === []) {
        $fridaySlots = $slotIds;
    }

    $assignments = [];
    $sectionBusy = []; // [cid][day][slot] => true
    $teacherBusy = []; // [day][slot][teacher] => true
    $unplaced = [];
    /** @var array<int, array<int, array<string, int>>> How many periods of (section, subject) already on each day — used to spread across days */
    $subjectDayCount = [];
    /** @var array<int, array<int, int>> Rotate day-order ties for free placement */
    $subjectSpreadOffset = [];

    $canPlace = function (int $cid, int $sid, string $day, int $slotId) use (&$sectionBusy, &$teacherBusy, $teacherMap): bool {
        if (!empty($sectionBusy[$cid][$day][$slotId])) {
            return false;
        }
        $tid = (int)($teacherMap[$cid . '|' . $sid] ?? 0);
        if ($tid > 0 && !empty($teacherBusy[$day][$slotId][$tid])) {
            return false;
        }
        return true;
    };
    $place = function (int $cid, int $sid, string $day, int $slotId) use (&$assignments, &$sectionBusy, &$teacherBusy, $teacherMap): void {
        $assignments[] = [
            'cls_sec_id' => $cid,
            'subject_id' => $sid,
            'day' => $day,
            'slot_id' => $slotId,
        ];
        $sectionBusy[$cid][$day][$slotId] = true;
        $tid = (int)($teacherMap[$cid . '|' . $sid] ?? 0);
        if ($tid > 0) {
            $teacherBusy[$day][$slotId][$tid] = true;
        }
    };

    $bumpSubjectDay = function (int $cid, int $sid, string $day) use (&$subjectDayCount): void {
        $subjectDayCount[$cid][$sid][$day] = (int)($subjectDayCount[$cid][$sid][$day] ?? 0) + 1;
    };

    /**
     * Prefer days with fewer periods of this subject already (spread Mon–Fri).
     * Secondary: prefer template slot (Monday pattern) when provided.
     */
    $tryPlaceSpread = function (int $cid, int $sid, ?int $preferSlotId) use (
        &$daysBySection,
        $slotIds,
        $fridaySlots,
        &$subjectDayCount,
        &$subjectSpreadOffset,
        $canPlace,
        $place,
        $bumpSubjectDay
    ): bool {
        $dayList = array_values($daysBySection[$cid] ?? []);
        $nDays = max(1, count($dayList));
        $off = (int) ($subjectSpreadOffset[$cid][$sid] ?? 0);

        $candidates = [];
        foreach (($daysBySection[$cid] ?? []) as $day) {
            $allowed = (strcasecmp($day, 'Friday') === 0) ? $fridaySlots : $slotIds;
            foreach ($allowed as $slotId) {
                $slotId = (int)$slotId;
                $dayLoad = (int)($subjectDayCount[$cid][$sid][$day] ?? 0);
                $slotTier = ($preferSlotId !== null && $slotId !== $preferSlotId) ? 1 : 0;
                $candidates[] = [
                    'day' => $day,
                    'slot_id' => $slotId,
                    'day_load' => $dayLoad,
                    'slot_tier' => $slotTier,
                ];
            }
        }
        usort($candidates, static function (array $a, array $b) use ($dayList, $off, $nDays): int {
            if ($a['day_load'] !== $b['day_load']) {
                return $a['day_load'] <=> $b['day_load'];
            }
            if ($a['slot_tier'] !== $b['slot_tier']) {
                return $a['slot_tier'] <=> $b['slot_tier'];
            }
            $ia = array_search($a['day'], $dayList, true);
            $ib = array_search($b['day'], $dayList, true);
            $ia = ($ia === false ? 999 : (($ia + $off) % $nDays));
            $ib = ($ib === false ? 999 : (($ib + $off) % $nDays));
            if ($ia !== $ib) {
                return $ia <=> $ib;
            }

            return $a['slot_id'] <=> $b['slot_id'];
        });
        foreach ($candidates as $c) {
            if ($canPlace($cid, $sid, $c['day'], $c['slot_id'])) {
                $place($cid, $sid, $c['day'], $c['slot_id']);
                $bumpSubjectDay($cid, $sid, $c['day']);
                $subjectSpreadOffset[$cid][$sid] = $off + 1;

                return true;
            }
        }

        return false;
    };

    /** @var array<int, array<int, int>> Subject → slot from Monday row (for “same slot all week” preference on extra periods) */
    $templatePreferredSlot = [];

    // Monday-template mode: build Monday row, then repeat each subject in the same slot on every working day (teacher-safe).
    if ((int)$options['use_monday_template'] === 1) {
        foreach ($sectionIds as $cid) {
            $mondayPattern = [];
            $subjects = isset($remaining[$cid]) ? array_keys($remaining[$cid]) : [];
            usort($subjects, function ($a, $b) use ($remaining, $cid) {
                return ($remaining[$cid][$b] ?? 0) <=> ($remaining[$cid][$a] ?? 0);
            });
            // Each subject may occupy at most ceil(weekly_demand / working_days) slots on Monday's row.
            // Otherwise one subject (e.g. Math × 5) fills Monday slots 1–5 before replication — wrong.
            $workingDayCount = max(1, count($daysBySection[$cid] ?? []));
            $mondayColumnsUsed = []; // sid => how many Monday grid columns already assigned in pattern

            foreach ($slotIds as $slotId) {
                foreach ($subjects as $sid) {
                    $sid = (int) $sid;
                    $weeklyNeed = (int) ($initialWeeklyBySection[$cid][$sid] ?? 0);
                    if ($weeklyNeed <= 0) {
                        continue;
                    }
                    $maxMondaySlotsForSubject = (int) ceil($weeklyNeed / $workingDayCount);
                    if (($mondayColumnsUsed[$sid] ?? 0) >= $maxMondaySlotsForSubject) {
                        continue;
                    }
                    if (($remaining[$cid][$sid] ?? 0) <= 0) {
                        continue;
                    }
                    if (! $canPlace($cid, $sid, 'Monday', $slotId)) {
                        continue;
                    }
                    $mondayPattern[$slotId] = $sid;
                    if (! isset($templatePreferredSlot[$cid][$sid])) {
                        $templatePreferredSlot[$cid][$sid] = $slotId;
                    }
                    $mondayColumnsUsed[$sid] = ($mondayColumnsUsed[$sid] ?? 0) + 1;
                    break;
                }
            }

            foreach (($daysBySection[$cid] ?? []) as $day) {
                $allowed = (strcasecmp($day, 'Friday') === 0) ? $fridaySlots : $slotIds;
                foreach ($allowed as $slotId) {
                    $sid = (int)($mondayPattern[$slotId] ?? 0);
                    if ($sid <= 0) {
                        continue;
                    }
                    if (($remaining[$cid][$sid] ?? 0) <= 0) {
                        continue;
                    }
                    if ($canPlace($cid, $sid, $day, $slotId)) {
                        $place($cid, $sid, $day, $slotId);
                        $remaining[$cid][$sid]--;
                        $bumpSubjectDay($cid, $sid, $day);
                    }
                }
            }
        }

        // Extra weekly periods or cells skipped (e.g. teacher clash): spread across days; still prefer the Monday template slot.
        foreach ($sectionIds as $cid) {
            foreach (array_keys($remaining[$cid] ?? []) as $sid) {
                $sid = (int)$sid;
                while (($remaining[$cid][$sid] ?? 0) > 0) {
                    $pref = $templatePreferredSlot[$cid][$sid] ?? null;
                    if (!$tryPlaceSpread($cid, $sid, $pref)) {
                        break;
                    }
                    $remaining[$cid][$sid]--;
                }
            }
        }
    } else {
        // Free placement: spread each period across different days first; allow 2+ on same day only when needed.
        $tokens = [];
        foreach ($remaining as $cid => $subMap) {
            foreach ($subMap as $sid => $count) {
                for ($i = 0; $i < (int)$count; $i++) {
                    $tokens[] = ['cls_sec_id' => (int)$cid, 'subject_id' => (int)$sid];
                }
            }
        }
        usort($tokens, function ($a, $b) use ($remaining) {
            $ca = $remaining[$a['cls_sec_id']][$a['subject_id']] ?? 0;
            $cb = $remaining[$b['cls_sec_id']][$b['subject_id']] ?? 0;

            return $cb <=> $ca;
        });

        foreach ($tokens as $tk) {
            $cid = (int)$tk['cls_sec_id'];
            $sid = (int)$tk['subject_id'];
            if (!$tryPlaceSpread($cid, $sid, null)) {
                $unplaced[] = [
                    'cls_sec_id' => $cid,
                    'subject_id' => $sid,
                    'reason' => 'No feasible slot without clash',
                ];
            } else {
                $remaining[$cid][$sid]--;
            }
        }
    }

    // Remaining demand → unplaced list (e.g. impossible teacher/section clash)
    foreach ($remaining as $cid => $subMap) {
        foreach ($subMap as $sid => $cnt) {
            $n = (int)$cnt;
            for ($i = 0; $i < $n; $i++) {
                $unplaced[] = [
                    'cls_sec_id' => (int)$cid,
                    'subject_id' => (int)$sid,
                    'reason' => 'Not placed — reduce demand or fix teacher assignments',
                ];
            }
        }
    }

    // Replace entire campus timetable: remove all stored slots for this campus, then insert fresh generation.
    $purgeIds = $this->db->table('class_section')
        ->select('cls_sec_id')
        ->where('campus_id', $campusId)
        ->get()
        ->getResultArray();
    $purgeIds = array_values(array_unique(array_filter(
        array_map(static fn($r) => (int)($r['cls_sec_id'] ?? 0), $purgeIds),
        static fn($v) => $v > 0
    )));

    $userId = (int)($this->session->get('member_userid') ?? 0);
    $now = date('Y-m-d H:i:s');
    $this->db->transBegin();
    try {
        if ($purgeIds !== []) {
            $this->db->table('time_table')->whereIn('cls_sec_id', $purgeIds)->delete();
        }
        foreach ($assignments as $a) {
            $this->db->table('time_table')->insert([
                'cls_sec_id' => $a['cls_sec_id'],
                'day' => $a['day'],
                'slot_id' => $a['slot_id'],
                'subject_id' => $a['subject_id'],
                'created_date' => $now,
                'updated_date' => $now,
                'user_id' => $userId,
            ]);
        }
        $this->db->transCommit();
    } catch (\Throwable $e) {
        $this->db->transRollback();
        return $this->response->setJSON(['success' => false, 'msg' => $e->getMessage()]);
    }

    // Enrich unplaced for UI
    $secMeta = $this->db->table('class_section cs')
        ->select('cs.cls_sec_id, c.class_name, s.section_name')
        ->join('classes c', 'c.class_id = cs.class_id', 'inner')
        ->join('sections s', 's.section_id = cs.section_id', 'inner')
        ->whereIn('cs.cls_sec_id', $sectionIds)
        ->get()->getResultArray();
    $secMap = [];
    foreach ($secMeta as $r) {
        $secMap[(int)$r['cls_sec_id']] = trim((string)$r['class_name'] . ' - ' . (string)$r['section_name']);
    }
    $subMeta = $this->db->table('allsubject')->select('sid, subject_name')->get()->getResultArray();
    $subMap = [];
    foreach ($subMeta as $r) {
        $subMap[(int)$r['sid']] = (string)$r['subject_name'];
    }
    foreach ($unplaced as &$u) {
        $u['class_section'] = $secMap[(int)$u['cls_sec_id']] ?? ('Section #' . (int)$u['cls_sec_id']);
        $u['subject_name'] = $subMap[(int)$u['subject_id']] ?? ('Subject #' . (int)$u['subject_id']);
    }
    unset($u);

    return $this->response->setJSON([
        'success' => true,
        'msg' => 'Timetable regenerated for this campus: previous slots were cleared and replaced. Review unplaced pool if needed.',
        'placed_count' => count($assignments),
        'unplaced_count' => count($unplaced),
        'unplaced' => $unplaced,
    ]);
}

public function manualPlaceFromPool()
{
    check_permission('admin-add-timetable');
    $campusId = (int)$this->session->get('member_campusid');
    $cid = (int)$this->request->getPost('cls_sec_id');
    $sid = (int)$this->request->getPost('subject_id');
    $day = trim((string)$this->request->getPost('day'));
    $slotId = (int)$this->request->getPost('slot_id');
    $force = ((int)$this->request->getPost('force') === 1);
    if ($campusId <= 0 || $cid <= 0 || $sid <= 0 || $slotId <= 0 || $day === '') {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid input.']);
    }
    if (!in_array($day, $this->canonicalWeekdayOrder(), true)) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid day.']);
    }

    $db = \Config\Database::connect();
    if (!$force) {
        $res = $this->applyTimetableCellUpdate($db, $cid, $day, $slotId, $sid, true);
        return $this->response->setJSON($res);
    }

    return $this->response->setJSON($this->forcePlaceSubjectInCell($db, $cid, $day, $slotId, $sid));
}

// public function timetable_add()
// {
//     // Load users with teacher role
//     $teachers = $this->db->table('users u')
//         ->join('user_roles ur', 'u.id = ur.userID')
//         ->join('roles r', 'ur.roleID = r.id')
//         ->where('r.role_name_id', 'teacher') // Assuming 'teacher' is used in role_name_id
//         ->select('u.id, u.first_name, u.last_name')
//         ->orderBy('u.first_name', 'ASC')
//         ->get()
//         ->getResultArray();

//     // Build teacher list with full name
//     $teacherList = [];
//     foreach ($teachers as $teacher) {
//         $teacherList[] = [
//             'id' => $teacher['id'],
//             'name' => trim($teacher['first_name'] . ' ' . $teacher['last_name'])
//         ];
//     }

//     $data = [
//         'teachers' => $teacherList,
//         'selectedTeacher' => $this->request->getGet('teacher_id')
//     ];

//     return view('admin/timetable_add', $data);
// }

public function timetable_add()
{
    return redirect()->to(base_url('admin/timetable/generator'));
}
//     public function timetable_add()
// {
//     // Load teachers
//     $teacherQuery = $this->db->table('users u')
//         ->join('user_roles ur', 'u.id = ur.userID')
//         ->join('roles r', 'ur.roleID = r.id')
//         ->where('r.role_name_id', 'teacher')
//         ->select('u.id, u.first_name, u.last_name')
//         ->orderBy('u.first_name', 'ASC')
//         ->get()
//         ->getResultArray();

//     $teachers = [];
//     foreach ($teacherQuery as $teacher) {
//         $teachers[] = [
//             'id' => $teacher['id'],
//             'name' => trim($teacher['first_name'] . ' ' . $teacher['last_name'])
//         ];
//     }

//     // Dummy data for other required variables
//     $sections = []; // or fetch from DB
//     $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
//     $slots = []; // or fetch from DB

//     // Send to view
//     return view('admin/timetable_add', [
//         'teachers' => $teachers,
//         'selectedTeacher' => $this->request->getGet('teacher_id'),
//         'sections' => $sections,
//         'days' => $days,
//         'slots' => $slots
//     ]);
// }


    public function manage()
    {
        check_permission('admin-timetable-edit');
        $campus_id = $this->session->get('member_campusid');

        // Get class sections with eager loading
        $builder = $this->db->table('class_section cs');
        $builder->select('cs.cls_sec_id, c.class_name, s.section_name');
        $builder->join('classes c', 'c.class_id = cs.class_id');
        $builder->join('sections s', 's.section_id = cs.section_id');
        $builder->where(['cs.campus_id' => $campus_id, 'cs.status' => 1]);
        
        $sections = $builder->get()->getResultArray();

        // Get all slots for this campus
        $slots = $this->db->table('slots')
            ->where('campus_id', $campus_id)
            ->orderBy('start_time', 'ASC')
            ->get()
            ->getResult();

        $data = [
            'title' => 'Manage Timetable',
            'sections' => $sections,
            'slots' => $slots,
            'full_week_days' => $this->canonicalWeekdayOrder(),
        ];

        return view('admin/timetable_edit', $data);
    }


public function getSubjects()
{
    $cls_sec_id = $this->request->getPost('cls_sec_id');
    log_message('debug', "Received cls_sec_id = $cls_sec_id");

    try {
        // Step 1: Get all section subjects with their teacher info (if assigned)
        $results = $this->db->table('section_subjects ss')
            ->select('
                ss.subject_id, 
                ss.sec_sub_id, 
                s.subject_name, 
                u.id as teacher_id, 
                u.first_name, 
                u.last_name
            ')
            ->join('allsubject s', 's.sid = ss.subject_id')
            ->join('teacher_subjects ts', 'ts.sec_sub_id = ss.sec_sub_id AND ts.status = 1', 'left')
            ->join('users u', 'u.id = ts.tid', 'left')
            ->where('ss.cls_sec_id', $cls_sec_id)
            ->where('ss.status', 1)
            ->get()
            ->getResultArray();

       // log_message('debug', 'Fetched subjects (with possible teacher data): ' . print_r($results, true));

        return $this->response->setJSON([
            'success' => true,
            'subjects' => $results
        ]);

    } catch (\Exception $e) {
        log_message('error', 'Error fetching subjects: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Database error: ' . $e->getMessage()
        ]);
    }
}






    public function getTimetable()
    {
        $cls_sec_id = $this->request->getPost('cls_sec_id');
        $campus_id = $this->session->get('member_campusid');

        // Get all slots
        $slots = $this->db->table('slots')
            ->where('campus_id', $campus_id)
            ->orderBy('start_time', 'ASC')
            ->get()
            ->getResultArray();

        // Get timetable data
        $timetable = $this->db->table('time_table tt')
            ->select('tt.*, s.subject_name, u.first_name as teacher_first_name, u.last_name as teacher_last_name')
            ->join('allsubject s', 's.sid = tt.subject_id')
            ->join('users u', 'u.id = s.teacher_id', 'left')
            ->where('tt.cls_sec_id', $cls_sec_id)
            ->get()
            ->getResultArray();

        // Organize by day and slot
        $organized = [];
        foreach ($timetable as $entry) {
            $organized[$entry['day']][$entry['slot_id']] = $entry;
        }

        return $this->response->setJSON([
            'success' => true,
            'slots' => $slots,
            'timetable' => $organized
        ]);
    }

    public function saveSlot()
    {
        $cls_sec_id = $this->request->getPost('cls_sec_id');
        $day = $this->request->getPost('day');
        $slot_id = $this->request->getPost('slot_id');
        $subject_id = $this->request->getPost('subject_id');
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');

        // Check for teacher conflict
        $conflict = $this->checkTeacherConflict($cls_sec_id, $day, $slot_id, $subject_id);
        if ($conflict['conflict']) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => $conflict['message']
            ]);
        }

        // Check if slot already exists
        $existing = $this->db->table('time_table')
            ->where(['cls_sec_id' => $cls_sec_id, 'day' => $day, 'slot_id' => $slot_id])
            ->get()
            ->getRow();

        $data = [
            'cls_sec_id' => $cls_sec_id,
            'day' => $day,
            'slot_id' => $slot_id,
            'subject_id' => $subject_id,
            'user_id' => $user_id,
            'updated_date' => $date
        ];

        if ($existing) {
            // Update existing
            $this->db->table('time_table')
                ->where('time_table_id', $existing->time_table_id)
                ->update($data);
        } else {
            // Insert new
            $data['created_date'] = $date;
            $this->db->table('time_table')->insert($data);
        }

        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Timetable updated successfully'
        ]);
    }

   
    public function clearSlot()
    {
        $cls_sec_id = $this->request->getPost('cls_sec_id');
        $day = $this->request->getPost('day');
        $slot_id = $this->request->getPost('slot_id');

        $this->db->table('time_table')
            ->where(['cls_sec_id' => $cls_sec_id, 'day' => $day, 'slot_id' => $slot_id])
            ->delete();

        return $this->response->setJSON(['success' => true]);
    }

   

    public function getTeacherSchedule()
    {
        $teacher_id = $this->request->getPost('teacher_id');
        
        $schedule = $this->db->table('time_table tt')
            ->select('tt.day, sl.start_time, sl.end_time, c.class_name, s.section_name, sub.subject_name')
            ->join('slots sl', 'sl.slot_id = tt.slot_id')
            ->join('class_section cs', 'cs.cls_sec_id = tt.cls_sec_id')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->join('allsubject sub', 'sub.sid = tt.subject_id')
            ->where('sub.teacher_id', $teacher_id)
            ->orderBy('tt.day, sl.start_time')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'schedule' => $schedule
        ]);
    }



  public function getSubjectsTimetable()
{
    $cls_sec_id = (int) $this->request->getPost('cls_sec_id');
    
    $db = \Config\Database::connect();
    $campusId = (int) $this->session->get('member_campusid');

    if ($cls_sec_id <= 0) {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'Invalid class section.',
        ]);
    }

    try {
 $latestSql = "
  SELECT t1.*
  FROM teacher_subjects t1
  JOIN (
    SELECT cls_sec_id, sec_sub_id, MAX(sst) AS max_sst
    FROM teacher_subjects
    WHERE status = 1
    GROUP BY cls_sec_id, sec_sub_id
  ) t2
    ON t2.cls_sec_id = t1.cls_sec_id
   AND t2.sec_sub_id = t1.sec_sub_id
   AND t2.max_sst   = t1.sst
";

$subjects = $db->table('section_subjects ss')
  ->select('ss.sec_sub_id, ss.subject_id, s.subject_name, COALESCE(ts.tid, s.teacher_id) AS teacher_id, u.first_name, u.last_name')
  ->join('allsubject s', 's.sid = ss.subject_id')
  ->join("($latestSql) ts", 'ts.sec_sub_id = ss.sec_sub_id AND ts.cls_sec_id = ss.cls_sec_id', 'left')
  ->join('users u', '(u.id = COALESCE(ts.tid, s.teacher_id) OR u.user_id = COALESCE(ts.tid, s.teacher_id)) AND u.status = 1', 'left', false)
  ->where('ss.cls_sec_id', $cls_sec_id)
  ->where('ss.status', 1)
  ->orderBy('ss.sec_sub_id', 'ASC')
  ->get()->getResultArray();

        foreach ($subjects as &$subRow) {
            $teacherName = trim((string)($subRow['first_name'] ?? '') . ' ' . (string)($subRow['last_name'] ?? ''));
            $subRow['teacher_name'] = $teacherName !== '' ? $teacherName : 'No teacher';
        }
        unset($subRow);

        $workingDays = $this->resolveWorkingDayNamesForSection($campusId, $cls_sec_id);
        $dayTiming = $this->getDayTimingLabelsForSection($campusId, $cls_sec_id);
        $slotsForGrid = $this->fetchSlotsForCampus($campusId);

        // Timetable for the class section
        $timetableRows = $db->table('time_table')
            ->select('day, slot_id, subject_id')
            ->where('cls_sec_id', $cls_sec_id)
            ->get()->getResultArray();

        // Grouping timetable by day/slot
        $timetable = [];
        foreach ($timetableRows as $row) {
            $timetable[$row['day']][$row['slot_id']] = $row;
        }

        // Count teacher subject assignments
        $teacherLoad = [];
        foreach ($timetableRows as $row) {
            $sub = array_filter($subjects, fn($s) => $s['subject_id'] == $row['subject_id']);
            if (!empty($sub)) {
                $s = array_values($sub)[0];
                if (!empty($s['teacher_id'])) {
                    $key = $s['teacher_id'];
                    $name = trim($s['first_name'] . ' ' . $s['last_name']);
                    if (!isset($teacherLoad[$key])) {
                        $teacherLoad[$key] = ['name' => $name, 'count' => 0];
                    }
                    $teacherLoad[$key]['count']++;
                }
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'subjects' => $subjects,
            'timetable' => $timetable,
            'teacherLoad' => array_values($teacherLoad),
            'working_days' => $workingDays,
            'full_week_days' => $this->canonicalWeekdayOrder(),
            'day_timing' => $dayTiming,
            'slots' => $slotsForGrid,
        ]);
    } catch (\Throwable $e) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => $e->getMessage()
        ]);
    }
}

/**
 * Core save/delete for one timetable cell. Used by updateSlot and bulkUpdateSlotRow.
 *
 * @return array{success: bool, msg: string}
 */
private function applyTimetableCellUpdate($db, int $cls_sec_id, string $day, int $slot_id, ?int $subject_id, bool $allowSameSubjectDay): array
{
    if ($subject_id === null) {
        $db->table('time_table')
            ->where(['cls_sec_id' => $cls_sec_id, 'day' => $day, 'slot_id' => $slot_id])
            ->delete();

        return ['success' => true, 'msg' => 'Slot cleared'];
    }

    $secSub = $db->table('section_subjects')
        ->select('sec_sub_id')
        ->where('cls_sec_id', $cls_sec_id)
        ->where('subject_id', $subject_id)
        ->where('status', 1)
        ->get()->getRow();

    if (! $secSub) {
        return [
            'success' => false,
            'msg'     => 'Subject is not offered in this section.',
        ];
    }
    $sec_sub_id = (int) $secSub->sec_sub_id;

    if (! $allowSameSubjectDay) {
        $dup = $db->table('time_table')
            ->select('slot_id')
            ->where('cls_sec_id', $cls_sec_id)
            ->where('day', $day)
            ->where('subject_id', $subject_id)
            ->where('slot_id !=', $slot_id)
            ->get()->getRow();

        if ($dup) {
            return [
                'success' => false,
                'msg'     => "This subject is already scheduled on {$day} (slot {$dup->slot_id}).",
            ];
        }
    }

    $teacherRow = $db->query('
        SELECT ts.tid
        FROM teacher_subjects ts
        WHERE ts.cls_sec_id = ? AND ts.sec_sub_id = ? AND ts.status = 1
        ORDER BY ts.sst DESC
        LIMIT 1
    ', [$cls_sec_id, $sec_sub_id])->getRow();

    $tid = $teacherRow->tid ?? null;

    if ($tid) {
        $conflict = $db->query('
            SELECT 1
            FROM time_table tt
            JOIN section_subjects ss
              ON ss.cls_sec_id = tt.cls_sec_id
             AND ss.subject_id = tt.subject_id
            JOIN (
                SELECT t1.cls_sec_id, t1.sec_sub_id, t1.tid
                FROM teacher_subjects t1
                JOIN (
                    SELECT cls_sec_id, sec_sub_id, MAX(sst) AS max_sst
                    FROM teacher_subjects
                    WHERE status = 1
                    GROUP BY cls_sec_id, sec_sub_id
                ) t2
                  ON t2.cls_sec_id = t1.cls_sec_id
                 AND t2.sec_sub_id = t1.sec_sub_id
                 AND t2.max_sst   = t1.sst
            ) cur
              ON cur.cls_sec_id = ss.cls_sec_id
             AND cur.sec_sub_id = ss.sec_sub_id
            WHERE tt.day = ?
              AND tt.slot_id = ?
              AND cur.tid = ?
              AND NOT (tt.cls_sec_id = ? AND tt.day = ? AND tt.slot_id = ?)
            LIMIT 1
        ', [$day, $slot_id, $tid, $cls_sec_id, $day, $slot_id])->getRow();

        if ($conflict) {
            return [
                'success' => false,
                'msg'     => 'Teacher conflict: this teacher is already scheduled in another class at the same day/slot.',
            ];
        }
    }

    $db->table('time_table')
        ->where([
            'cls_sec_id' => $cls_sec_id,
            'day'        => $day,
            'slot_id'    => $slot_id,
        ])
        ->delete();

    $db->table('time_table')->insert([
        'cls_sec_id'   => $cls_sec_id,
        'day'          => $day,
        'slot_id'      => $slot_id,
        'subject_id'   => $subject_id,
        'created_date' => date('Y-m-d H:i:s'),
        'updated_date' => date('Y-m-d H:i:s'),
        'user_id'      => (int) ($this->session->get('member_userid') ?? 0),
    ]);

    return ['success' => true, 'msg' => 'Slot updated'];
}

public function updateSlot()
{
    $cls_sec_id  = (int) $this->request->getPost('cls_sec_id');
    $day         = trim((string) $this->request->getPost('day'));
    $slot_id     = (int) $this->request->getPost('slot_id');
    $subject_raw = $this->request->getPost('subject_id');
    $subject_id  = ($subject_raw === '' || $subject_raw === null) ? null : (int) $subject_raw;
    $allowSameSubjectDay = ((int) $this->request->getPost('allow_same_subject_day') === 1);

    $validDays = $this->canonicalWeekdayOrder();
    if ($cls_sec_id <= 0 || $day === '' || ! in_array($day, $validDays, true) || $slot_id <= 0) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid input.']);
    }

    $db = \Config\Database::connect();
    $result = $this->applyTimetableCellUpdate($db, $cls_sec_id, $day, $slot_id, $subject_id, $allowSameSubjectDay);
    if (! $result['success']) {
        return $this->response->setJSON($result);
    }

    return $this->response->setJSON([
        'success'     => true,
        'msg'         => $result['msg'],
        'teacherLoad' => $this->calculateTeacherLoad($cls_sec_id),
    ]);
}

/**
 * Apply the same subject (or clear) to every visible day in one slot row — atomic.
 */
public function bulkUpdateSlotRow()
{
    $cls_sec_id  = (int) $this->request->getPost('cls_sec_id');
    $slot_id     = (int) $this->request->getPost('slot_id');
    $subject_raw = $this->request->getPost('subject_id');
    $subject_id  = ($subject_raw === '' || $subject_raw === null) ? null : (int) $subject_raw;
    $allowSameSubjectDay = ((int) $this->request->getPost('allow_same_subject_day') === 1);

    $daysRaw = $this->request->getPost('days');
    if (is_string($daysRaw)) {
        $days = json_decode($daysRaw, true);
    } else {
        $days = $daysRaw;
    }

    if ($cls_sec_id <= 0 || $slot_id <= 0 || ! is_array($days)) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid input.']);
    }

    $valid = $this->canonicalWeekdayOrder();
    $orderedDays = [];
    foreach ($days as $d) {
        $d = trim((string) $d);
        if (in_array($d, $valid, true)) {
            $orderedDays[] = $d;
        }
    }
    $orderedDays = array_values(array_unique($orderedDays));

    if ($orderedDays === []) {
        return $this->response->setJSON(['success' => false, 'msg' => 'No valid days.']);
    }

    $db = \Config\Database::connect();
    $db->transStart();

    foreach ($orderedDays as $day) {
        $result = $this->applyTimetableCellUpdate($db, $cls_sec_id, $day, $slot_id, $subject_id, $allowSameSubjectDay);
        if (! $result['success']) {
            $db->transRollback();

            return $this->response->setJSON($result);
        }
    }

    $db->transComplete();

    if ($db->transStatus() === false) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Could not update row.']);
    }

    return $this->response->setJSON([
        'success'     => true,
        'msg'         => $subject_id === null ? 'Row cleared' : 'Subject applied to full row',
        'teacherLoad' => $this->calculateTeacherLoad($cls_sec_id),
    ]);
}

public function getSubjectConstraints()
{
    $cls_sec_id = (int) $this->request->getPost('cls_sec_id');
    $subject_id = (int) $this->request->getPost('subject_id');
    $allowSameSubjectDay = ((int)$this->request->getPost('allow_same_subject_day') === 1);

    if ($cls_sec_id <= 0 || $subject_id <= 0) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Invalid input.'
        ]);
    }

    $db = \Config\Database::connect();
    $blocked = [];
    $blockedKeys = [];

    // (A) Teacher conflict blocked slots (same teacher already teaching another class at day/slot)
    $teacherId = $this->resolveTeacherForSectionSubject($cls_sec_id, $subject_id);
    if ($teacherId) {
        $teacherBusy = $db->query("
            SELECT tt.day, tt.slot_id
            FROM time_table tt
            JOIN section_subjects ss
              ON ss.cls_sec_id = tt.cls_sec_id
             AND ss.subject_id = tt.subject_id
            JOIN (
                SELECT t1.cls_sec_id, t1.sec_sub_id, t1.tid
                FROM teacher_subjects t1
                JOIN (
                    SELECT cls_sec_id, sec_sub_id, MAX(sst) AS max_sst
                    FROM teacher_subjects
                    WHERE status = 1
                    GROUP BY cls_sec_id, sec_sub_id
                ) t2
                  ON t2.cls_sec_id = t1.cls_sec_id
                 AND t2.sec_sub_id = t1.sec_sub_id
                 AND t2.max_sst   = t1.sst
            ) cur
              ON cur.cls_sec_id = ss.cls_sec_id
             AND cur.sec_sub_id = ss.sec_sub_id
            WHERE cur.tid = ?
              AND tt.cls_sec_id != ?
        ", [$teacherId, $cls_sec_id])->getResultArray();

        foreach ($teacherBusy as $r) {
            $k = $r['day'] . '|' . $r['slot_id'];
            if (isset($blockedKeys[$k])) {
                continue;
            }
            $blockedKeys[$k] = true;
            $blocked[] = [
                'day' => $r['day'],
                'slot_id' => (int)$r['slot_id'],
                'reason' => 'Teacher already occupied in another class.'
            ];
        }
    }

    // (B) Same-subject-per-day restriction (optional)
    if (!$allowSameSubjectDay) {
        $sameDayRows = $db->table('time_table')
            ->select('day, slot_id')
            ->where('cls_sec_id', $cls_sec_id)
            ->where('subject_id', $subject_id)
            ->get()
            ->getResultArray();

        foreach ($sameDayRows as $r) {
            $sameDay = (string)$r['day'];
            // block all other slots on that day
            $allSlots = $db->table('slots')
                ->select('slot_id')
                ->where('campus_id', (int)$this->session->get('member_campusid'))
                ->get()
                ->getResultArray();
            foreach ($allSlots as $s) {
                if ((int)$s['slot_id'] === (int)$r['slot_id']) {
                    continue; // keep already placed slot selectable
                }
                $k = $sameDay . '|' . (int)$s['slot_id'];
                if (isset($blockedKeys[$k])) {
                    continue;
                }
                $blockedKeys[$k] = true;
                $blocked[] = [
                    'day' => $sameDay,
                    'slot_id' => (int)$s['slot_id'],
                    'reason' => 'Same subject already exists on this day.'
                ];
            }
        }
    }

    return $this->response->setJSON([
        'success' => true,
        'blocked' => $blocked
    ]);
}

// Improved teacher conflict check
protected function checkTeacherConflict($cls_sec_id, $day, $slot_id, $subject_id)
{
    // Get all teachers assigned to this subject in this class section
    $teachers = $this->db->table('teacher_subjects ts')
        ->join('section_subjects ss', 'ss.sec_sub_id = ts.sec_sub_id')
        ->where('ss.cls_sec_id', $cls_sec_id)
        ->where('ss.subject_id', $subject_id)
        ->where('ts.status', 1)
        ->select('ts.tid')
        ->get()
        ->getResult();

    if (empty($teachers)) {
        return ['conflict' => false]; // No teachers assigned
    }

    $teacherIds = array_column($teachers, 'tid');

    // Check if any of these teachers are already assigned at this time in other classes
    $conflict = $this->db->table('time_table tt')
        ->join('section_subjects ss', 'ss.cls_sec_id = tt.cls_sec_id AND ss.subject_id = tt.subject_id')
        ->join('teacher_subjects ts', 'ts.sec_sub_id = ss.sec_sub_id AND ts.status = 1')
        ->join('classes c', 'c.class_id = (SELECT class_id FROM class_section WHERE cls_sec_id = tt.cls_sec_id)')
        ->join('sections s', 's.section_id = (SELECT section_id FROM class_section WHERE cls_sec_id = tt.cls_sec_id)')
        ->join('allsubject sub', 'sub.sid = tt.subject_id')
        ->where('tt.day', $day)
        ->where('tt.slot_id', $slot_id)
        ->whereIn('ts.tid', $teacherIds)
        ->where('tt.cls_sec_id !=', $cls_sec_id)
        ->select('c.class_name, s.section_name, sub.subject_name')
        ->get()
        ->getRow();

    if ($conflict) {
        return [
            'conflict' => true,
            'message' => sprintf(
                'Teacher conflict: This teacher is already assigned to %s (%s - %s) at this time',
                $conflict->subject_name,
                $conflict->class_name,
                $conflict->section_name
            )
        ];
    }

    return ['conflict' => false];
}


private function calculateTeacherLoad($cls_sec_id)
{
    $db = \Config\Database::connect();

    $subjects = $db->table('section_subjects ss')
        ->select('ss.sec_sub_id, ss.subject_id, t.id AS teacher_id, t.first_name, t.last_name')
        ->join('teacher_subjects ts', 'ts.sec_sub_id = ss.sec_sub_id AND ts.cls_sec_id = ss.cls_sec_id', 'left')
        ->join('users t', 't.id = ts.tid AND t.status = 1', 'left')
        ->where('ss.cls_sec_id', $cls_sec_id)
        ->get()
        ->getResultArray();

    $timetableRows = $db->table('time_table')
        ->where('cls_sec_id', $cls_sec_id)
        ->get()
        ->getResultArray();

    $teacherLoad = [];

    foreach ($timetableRows as $row) {
        $match = array_filter($subjects, fn($s) => $s['subject_id'] == $row['subject_id']);
        if ($match) {
            $s = array_values($match)[0];
            if (!empty($s['teacher_id'])) {
                $key = $s['teacher_id'];
                $name = trim($s['first_name'] . ' ' . $s['last_name']);
                if (!isset($teacherLoad[$key])) {
                    $teacherLoad[$key] = ['name' => $name, 'count' => 0];
                }
                $teacherLoad[$key]['count']++;
            }
        }
    }

    return array_values($teacherLoad);
}

private function resolveTeacherForSectionSubject(int $cls_sec_id, int $subject_id): ?int
{
    $secSub = $this->db->table('section_subjects')
        ->select('sec_sub_id')
        ->where('cls_sec_id', $cls_sec_id)
        ->where('subject_id', $subject_id)
        ->where('status', 1)
        ->get()->getRow();

    if (!$secSub) {
        return null;
    }

    $row = $this->db->query("
        SELECT ts.tid
        FROM teacher_subjects ts
        WHERE ts.cls_sec_id = ? AND ts.sec_sub_id = ? AND ts.status = 1
        ORDER BY ts.sst DESC
        LIMIT 1
    ", [$cls_sec_id, (int)$secSub->sec_sub_id])->getRow();

    return $row ? (int)$row->tid : null;
}

public function viewTeacherTimetable()
{
    check_permission('admin-timetable');
    $campusId = (int)($this->session->get('member_campusid') ?? 0);

    $teachers = $this->db->table('users u')
        ->select("u.id AS tid, CONCAT(COALESCE(u.first_name,''), ' ', COALESCE(u.last_name,'')) AS name")
        ->join('user_roles ur', 'ur.userID = u.id', 'inner')
        ->where('u.status', 1)
        ->where('ur.roleID', 5) // teacher role
        ->where('u.campus_id', $campusId)
        ->orderBy('u.first_name', 'ASC')
        ->orderBy('u.last_name', 'ASC')
        ->get()
        ->getResultArray();

    return view('timetable/teacher_timetable', [
        'teachers' => $teachers,
    ]);
}

public function getTeacherTimetable($teacherId = null)
{
    $builder = $this->db->table('teacher_timetable_view');
    
    if ($teacherId) {
        $builder->where('teacher_id', $teacherId);
    }
    
    $timetable = $builder->orderBy('day_order', 'ASC')
                         ->orderBy('start_time', 'ASC')
                         ->get()
                         ->getResultArray();

    // Group by day for better presentation
    $grouped = [];
    foreach ($timetable as $row) {
        $grouped[$row['day']][] = $row;
    }

    return $this->response->setJSON([
        'success' => true,
        'data' => $grouped
    ]);
}

public function exportTeacherTimetablePDF($teacherId)
{
    $timetable = $this->db->table('teacher_timetable_view')
                         ->where('teacher_id', $teacherId)
                         ->orderBy('day_order', 'ASC')
                         ->orderBy('start_time', 'ASC')
                         ->get()
                         ->getResultArray();

    $mpdf = new \Mpdf\Mpdf();
    $html = $this->renderTimetableHTML($timetable);
    $mpdf->WriteHTML($html);
    $mpdf->Output('teacher_timetable.pdf', 'D');
}

public function exportTeacherTimetableICal($teacherId)
{
    $timetable = $this->db->table('teacher_timetable_view')
                         ->where('teacher_id', $teacherId)
                         ->get()
                         ->getResultArray();

    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="teacher_timetable.ics"');

    echo "BEGIN:VCALENDAR\n";
    echo "VERSION:2.0\n";
    echo "PRODID:-//School System//Teacher Timetable//EN\n";
    
    foreach ($timetable as $entry) {
        echo "BEGIN:VEVENT\n";
        echo "SUMMARY:{$entry['class_name']} - {$entry['subject_name']}\n";
        echo "DTSTART:".date('Ymd\THis', strtotime("next {$entry['day']} {$entry['start_time']}"))."\n";
        echo "DTEND:".date('Ymd\THis', strtotime("next {$entry['day']} {$entry['end_time']}"))."\n";
        echo "RRULE:FREQ=WEEKLY\n";
        echo "LOCATION:{$entry['section_name']}\n";
        echo "END:VEVENT\n";
    }
    
    echo "END:VCALENDAR\n";
    exit;
}

public function report()
{
    check_permission('admin-timetable');
    $campusId = (int) $this->session->get('member_campusid');

    $sections = $this->fetchSectionsForCampus($campusId);
    $teachers = $this->fetchTeachersWithTimetableForCampus($campusId);

    $timingType = ['type_name' => 'School Timing'];

    return view('admin/timetable_report', [
        'sections' => $sections,
        'teachers' => $teachers,
        'timing_type_name' => $timingType['type_name'] ?? '',
        'working_days_display' => '',
    ]);
}

/** Display options for timetable report grid (slot labels + class-wise teacher line). */
private function timetableReportDisplayOptionsFromPost(): array
{
    $post = $this->request->getPost();
    $slot = isset($post['show_slot_time']) ? (int)$post['show_slot_time'] === 1 : false;
    $teach = array_key_exists('show_teacher_with_subject', $post)
        ? ((int)$post['show_teacher_with_subject'] === 1)
        : true;

    return [
        'show_slot_time' => $slot,
        'show_teacher_with_subject' => $teach,
    ];
}

private function timetableReportDisplayOptionsFromGet(): array
{
    $st = $this->request->getGet('show_slot_time');
    $tt = $this->request->getGet('show_teacher_with_subject');

    return [
        'show_slot_time' => ($st === '1' || $st === 1),
        'show_teacher_with_subject' => ($tt === null || $tt === '')
            ? true
            : ((string)$tt === '1'),
    ];
}

public function reportData()
{
    try {
        check_permission('admin-timetable');
        $mode = trim((string)$this->request->getPost('mode'));
        $clsPost = $this->request->getPost('cls_sec_id');
        $teacherPost = $this->request->getPost('teacher_id');
        $allClasses = is_string($clsPost) && strtolower(trim((string)$clsPost)) === 'all';
        $allTeachers = is_string($teacherPost) && strtolower(trim((string)$teacherPost)) === 'all';

        $payload = $this->buildReportPayload($mode, $clsPost ?? '', $teacherPost ?? '', $allClasses, $allTeachers);
        if (!$payload['success']) {
            return $this->response->setJSON(['success' => false, 'msg' => $payload['msg']]);
        }

        $displayOpts = $this->timetableReportDisplayOptionsFromPost();

        $html = '';
        $blocks = $payload['blocks'] ?? [];
        foreach ($blocks as $i => $block) {
            $html .= view('admin/partials/timetable_report_grid', array_merge([
                'title' => $block['title'],
                'mode' => $block['mode'],
                'cls_sec_id' => (int)($block['cls_sec_id'] ?? 0),
                'days' => $block['days'],
                'slots' => $block['slots'],
                'matrix' => $block['matrix'],
                'is_export' => false,
                'sheet_position' => ($i === 0) ? 'first' : 'next',
            ], $displayOpts));
            if ($i < count($blocks) - 1) {
                $html .= '<hr class="tt-report-section-break my-4">';
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'html' => $html,
        ]);
    } catch (\Throwable $e) {
        log_message('error', 'Timetable reportData: {message}', ['message' => $e->getMessage()]);
        $msg = 'Unable to load timetable report right now.';
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            $msg .= ' ' . $e->getMessage();
        }
        return $this->response->setJSON([
            'success' => false,
            'msg' => $msg,
        ]);
    }
}

public function reportExport()
{
    check_permission('admin-timetable');

    $mode = trim((string)$this->request->getGet('mode'));
    $format = strtolower(trim((string)$this->request->getGet('format'))); // pdf|excel
    $clsGet = $this->request->getGet('cls_sec_id');
    $teacherGet = $this->request->getGet('teacher_id');
    $allClasses = is_string($clsGet) && strtolower(trim((string)$clsGet)) === 'all';
    $allTeachers = is_string($teacherGet) && strtolower(trim((string)$teacherGet)) === 'all';

    $payload = $this->buildReportPayload($mode, $clsGet ?? '', $teacherGet ?? '', $allClasses, $allTeachers);
    if (!$payload['success']) {
        return redirect()->back()->with('error', $payload['msg']);
    }

    $displayOpts = $this->timetableReportDisplayOptionsFromGet();

    $titleSafe = preg_replace('/[^A-Za-z0-9\-_]+/', '_', $payload['export_title'] ?? 'Timetable_Report');

    $html = '';
    $blocks = $payload['blocks'] ?? [];
    foreach ($blocks as $i => $block) {
        $html .= view('admin/partials/timetable_report_grid', array_merge([
            'title' => $block['title'],
            'mode' => $block['mode'],
            'cls_sec_id' => (int)($block['cls_sec_id'] ?? 0),
            'days' => $block['days'],
            'slots' => $block['slots'],
            'matrix' => $block['matrix'],
            'is_export' => true,
            'sheet_position' => ($i === 0) ? 'first' : 'next',
        ], $displayOpts));
        if ($i < count($blocks) - 1) {
            $html .= '<hr class="tt-report-section-break" style="margin:16px 0;border-top:1px solid #ccc;">';
        }
    }

    if ($format === 'excel') {
        $filename = $titleSafe . '.xls';
        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody('<html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>');
    }

    if ($format === 'pdf') {
        if (class_exists('\Mpdf\Mpdf')) {
            $mpdf = new \Mpdf\Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML('<h3>' . esc($titleSafe) . '</h3>' . $html);
            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $titleSafe . '.pdf"')
                ->setBody($mpdf->Output('', 'S'));
        }
        // Fallback: HTML print view if mPDF unavailable
        return $this->response->setBody('<html><head><meta charset="utf-8"></head><body onload="window.print()">' . $html . '</body></html>');
    }

    return redirect()->back()->with('error', 'Invalid export format.');
}

public function reportAdjustData()
{
    check_permission('admin-timetable');
    $campusId = (int)$this->session->get('member_campusid');
    $clsSecId = (int)$this->request->getPost('cls_sec_id');
    if ($campusId <= 0 || $clsSecId <= 0) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Select a single class section for manual adjustment.']);
    }

    $payload = $this->buildAdjustGridPayload($campusId, $clsSecId);
    if (!$payload['success']) {
        return $this->response->setJSON(['success' => false, 'msg' => $payload['msg'] ?? 'Could not load adjustment data.']);
    }

    return $this->response->setJSON($payload);
}

public function reportAdjustFeasibleSlots()
{
    check_permission('admin-timetable');
    $campusId = (int)$this->session->get('member_campusid');
    $clsSecId = (int)$this->request->getPost('cls_sec_id');
    $subjectId = (int)$this->request->getPost('subject_id');
    if ($campusId <= 0 || $clsSecId <= 0 || $subjectId <= 0) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid input.']);
    }

    $days = $this->resolveWorkingDayNamesForSection($campusId, $clsSecId);
    $slots = $this->fetchSlotsForCampus($campusId);
    $db = \Config\Database::connect();
    $ctx = $this->buildAdjustSlotContext($db, $campusId, $clsSecId, $subjectId, $days, $slots);

    $feasible = [];
    $blocked = [];
    $cells = [];
    $availFree = [];
    $availBusy = [];

    foreach ($days as $day) {
        foreach ($slots as $i => $slot) {
            $slotId = (int)($slot['slot_id'] ?? 0);
            if ($slotId <= 0) {
                continue;
            }
            $slotNum = (int)($ctx['slot_num_map'][$slotId] ?? ($i + 1));
            $classified = $this->classifyAdjustSlot(
                $day,
                $slotId,
                $campusId,
                $clsSecId,
                $ctx['section_cell_map'],
                $ctx['teacher_busy_map'],
                $ctx['has_teacher']
            );

            $cellPayload = [
                'day' => $day,
                'slot_id' => $slotId,
                'slot_num' => $slotNum,
                'case' => (int)$classified['case'],
                'highlight' => (string)$classified['highlight'],
            ];
            if (!empty($classified['occupied'])) {
                $cellPayload['occupied'] = $classified['occupied'];
            }
            if (!empty($classified['conflict'])) {
                $cellPayload['conflict'] = $classified['conflict'];
            }
            $cells[] = $cellPayload;

            if ($classified['highlight'] === 'friday_inactive') {
                $blocked[] = [
                    'day' => $day,
                    'slot_id' => $slotId,
                    'reason' => 'Friday half-day: permanently inactive.',
                    'code' => 'friday_inactive',
                    'case' => 0,
                ];
                continue;
            }

            if ($classified['highlight'] === 'green') {
                $feasible[] = ['day' => $day, 'slot_id' => $slotId, 'case' => (int)$classified['case']];
                $availFree[] = ['day' => $day, 'slot_id' => $slotId, 'slot_num' => $slotNum];
            } else {
                $reason = $this->adjustBlockedReason($classified, (string)($ctx['teacher_info']['teacher_name'] ?? 'Teacher'));
                $blocked[] = [
                    'day' => $day,
                    'slot_id' => $slotId,
                    'reason' => $reason,
                    'code' => 'teacher_conflict',
                    'case' => (int)$classified['case'],
                ];
            }
        }
    }

    foreach ($ctx['teacher_busy_map'] as $row) {
        if ((int)($row['cls_sec_id'] ?? 0) === $clsSecId) {
            continue;
        }
        $slotNum = (int)($ctx['slot_num_map'][(int)($row['slot_id'] ?? 0)] ?? (int)($row['slot_id'] ?? 0));
        $classLabel = trim((string)($row['class_name'] ?? '') . ' - ' . (string)($row['section_name'] ?? ''));
        $availBusy[] = [
            'day' => (string)($row['day'] ?? ''),
            'slot_id' => (int)($row['slot_id'] ?? 0),
            'slot_num' => $slotNum,
            'class_label' => $classLabel,
            'subject_name' => (string)($row['subject_name'] ?? ''),
        ];
    }

    return $this->response->setJSON([
        'success' => true,
        'subject_name' => (string)($ctx['subject_name'] ?? ''),
        'teacher_name' => (string)($ctx['teacher_info']['teacher_name'] ?? ''),
        'teacher_id' => (int)($ctx['teacher_info']['teacher_id'] ?? 0),
        'cells' => $cells,
        'feasible' => $feasible,
        'blocked' => $blocked,
        'teacher_availability' => [
            'teacher_name' => (string)($ctx['teacher_info']['teacher_name'] ?? ''),
            'free' => $availFree,
            'busy' => $availBusy,
        ],
    ]);
}

public function reportAdjustPlace()
{
    check_permission('admin-timetable');
    $campusId = (int)$this->session->get('member_campusid');
    $cid = (int)$this->request->getPost('cls_sec_id');
    $sid = (int)$this->request->getPost('subject_id');
    $day = trim((string)$this->request->getPost('day'));
    $slotId = (int)$this->request->getPost('slot_id');
    $force = ((int)$this->request->getPost('force') === 1);
    $moveFromClsSecId = (int)$this->request->getPost('move_from_cls_sec_id');
    $moveFromDay = trim((string)$this->request->getPost('move_from_day'));
    $moveFromSlotId = (int)$this->request->getPost('move_from_slot_id');
    $moveFromTimeTableId = (int)$this->request->getPost('move_from_time_table_id');
    $hasMoveFrom = $moveFromTimeTableId > 0
        && $moveFromClsSecId > 0
        && $moveFromSlotId > 0
        && $moveFromDay !== '';
    if ($campusId <= 0 || $cid <= 0 || $sid <= 0 || $slotId <= 0 || $day === '') {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid input.']);
    }
    if (!in_array($day, $this->canonicalWeekdayOrder(), true)) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid day.']);
    }
    if (strcasecmp($day, 'Friday') === 0 && !$this->isFridaySlotAllowed($campusId, $slotId)) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Friday half-day: this slot is permanently inactive and cannot be used.',
            'can_force' => false,
        ]);
    }

    $owned = $this->db->table('class_section')
        ->where('cls_sec_id', $cid)
        ->where('campus_id', $campusId)
        ->countAllResults();
    if ($owned < 1) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Class section not found for this campus.']);
    }

    $db = \Config\Database::connect();
    $days = $this->resolveWorkingDayNamesForSection($campusId, $cid);
    $slots = $this->fetchSlotsForCampus($campusId);
    $ctx = $this->buildAdjustSlotContext($db, $campusId, $cid, $sid, $days, $slots);
    if ($hasMoveFrom) {
        $moveKey = $this->adjustCellKey($moveFromDay, $moveFromSlotId);
        $busy = $ctx['teacher_busy_map'][$moveKey] ?? null;
        if ($busy && (int)($busy['time_table_id'] ?? 0) === $moveFromTimeTableId) {
            unset($ctx['teacher_busy_map'][$moveKey]);
        }
    }
    $classified = $this->classifyAdjustSlot(
        $day,
        $slotId,
        $campusId,
        $cid,
        $ctx['section_cell_map'],
        $ctx['teacher_busy_map'],
        $ctx['has_teacher']
    );

    if (!$force) {
        if ($classified['highlight'] === 'friday_inactive') {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Friday half-day: this slot is permanently inactive and cannot be used.',
                'can_force' => false,
                'code' => 'friday_inactive',
            ]);
        }

        if ($classified['direct_place']) {
            if ($hasMoveFrom) {
                $moveRes = $this->deleteAdjustMoveFromRow($db, $campusId, $moveFromTimeTableId, $moveFromClsSecId, $moveFromDay, $moveFromSlotId);
                if (!$moveRes['success']) {
                    return $this->response->setJSON($moveRes);
                }
                $ctx = $this->buildAdjustSlotContext($db, $campusId, $cid, $sid, $days, $slots);
            }
            $res = $this->applyTimetableCellUpdate($db, $cid, $day, $slotId, $sid, true);
            if (!$res['success']) {
                return $this->response->setJSON($res);
            }
            $adjust = $this->buildAdjustGridPayload($campusId, $cid);
            $newName = (string)($ctx['subject_name'] ?? 'Subject');
            $msg = ((int)$classified['case'] === 3)
                ? 'Replaced ' . (string)($classified['occupied']['subject_name'] ?? 'subject') . ' with ' . $newName . '.'
                : 'Subject placed successfully.';

            return $this->response->setJSON([
                'success' => true,
                'msg' => $msg,
                'case' => (int)$classified['case'],
                'adjust' => $adjust,
            ]);
        }

        if ($classified['needs_force']) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => $this->adjustBlockedReason($classified, (string)($ctx['teacher_info']['teacher_name'] ?? 'Teacher')),
                'code' => 'teacher_conflict',
                'case' => (int)$classified['case'],
                'can_force' => true,
                'force_preview' => $this->previewForcePlacement($campusId, $cid, $day, $slotId, $sid),
            ]);
        }

        return $this->response->setJSON(['success' => false, 'msg' => 'Could not place subject.']);
    } else {
        if ($hasMoveFrom) {
            $moveRes = $this->deleteAdjustMoveFromRow($db, $campusId, $moveFromTimeTableId, $moveFromClsSecId, $moveFromDay, $moveFromSlotId);
            if (!$moveRes['success']) {
                return $this->response->setJSON($moveRes);
            }
        }
        $forceRes = $this->forcePlaceSubjectInCell($db, $cid, $day, $slotId, $sid, $campusId, true);
        if (!$forceRes['success']) {
            return $this->response->setJSON($forceRes);
        }
    }

    $adjust = $this->buildAdjustGridPayload($campusId, $cid);
    return $this->response->setJSON([
        'success' => true,
        'msg' => $force ? 'Subject placed (force mode).' : 'Subject placed successfully.',
        'adjust' => $adjust,
    ]);
}

public function reportAdjustClear()
{
    check_permission('admin-timetable');
    $campusId = (int)$this->session->get('member_campusid');
    $cid = (int)$this->request->getPost('cls_sec_id');
    $day = trim((string)$this->request->getPost('day'));
    $slotId = (int)$this->request->getPost('slot_id');
    if ($campusId <= 0 || $cid <= 0 || $slotId <= 0 || $day === '') {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid input.']);
    }

    $owned = $this->db->table('class_section')
        ->where('cls_sec_id', $cid)
        ->where('campus_id', $campusId)
        ->countAllResults();
    if ($owned < 1) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Class section not found for this campus.']);
    }

    $db = \Config\Database::connect();
    $res = $this->applyTimetableCellUpdate($db, $cid, $day, $slotId, null, true);
    if (!$res['success']) {
        return $this->response->setJSON($res);
    }

    $adjust = $this->buildAdjustGridPayload($campusId, $cid);
    return $this->response->setJSON([
        'success' => true,
        'msg' => 'Slot cleared.',
        'adjust' => $adjust,
    ]);
}

public function reportAdjustTeacherTimetable()
{
    check_permission('admin-timetable');
    $campusId = (int)$this->session->get('member_campusid');
    $teacherId = (int)$this->request->getPost('teacher_id');
    $clsSecId = (int)$this->request->getPost('cls_sec_id');
    if ($campusId <= 0 || $teacherId <= 0 || $clsSecId <= 0) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid input.']);
    }

    $owned = $this->db->table('class_section')
        ->where('cls_sec_id', $clsSecId)
        ->where('campus_id', $campusId)
        ->countAllResults();
    if ($owned < 1) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Class section not found for this campus.']);
    }

    $teacher = $this->db->table('users')
        ->select('first_name, last_name')
        ->where('id', $teacherId)
        ->get()
        ->getRowArray();
    if ($teacher === null) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Teacher not found.']);
    }

    $days = $this->resolveWorkingDayNamesForSection($campusId, $clsSecId);
    $slots = $this->fetchSlotsForCampus($campusId);
    if ($slots === []) {
        return $this->response->setJSON(['success' => false, 'msg' => 'No slots found for this campus.']);
    }

    $slotNumMap = $this->buildSlotNumMap($slots);
    $latestTeacherSql = $this->sqlLatestTeacherAssignments();
    $assignMap = [];
    $adjustTeacherQb = $this->db->table('time_table tt')
        ->select('tt.time_table_id, tt.cls_sec_id, tt.subject_id, tt.day, tt.slot_id, sub.subject_name, c.class_name, s.section_name')
        ->join('class_section cs', 'cs.cls_sec_id = tt.cls_sec_id')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id')
        ->join('allsubject sub', 'sub.sid = tt.subject_id', 'inner')
        ->join(
            'section_subjects ss',
            'ss.cls_sec_id = tt.cls_sec_id AND ss.subject_id = tt.subject_id AND ss.status = 1',
            'inner'
        )
        ->join("($latestTeacherSql) lts", 'lts.cls_sec_id = ss.cls_sec_id AND lts.sec_sub_id = ss.sec_sub_id', 'inner')
        ->where('lts.tid', $teacherId)
        ->where('cs.campus_id', $campusId);
    $this->applyClassSectionTimetableIncludedFilter($adjustTeacherQb, 'cs');
    $rows = $adjustTeacherQb
        ->orderBy('tt.day', 'ASC')
        ->orderBy('tt.slot_id', 'ASC')
        ->orderBy('tt.time_table_id', 'DESC')
        ->get()
        ->getResultArray();

    foreach ($rows as $r) {
        $d = (string)$r['day'];
        $s = (int)$r['slot_id'];
        $k = $this->adjustCellKey($d, $s);
        if (isset($assignMap[$k])) {
            continue;
        }
        $assignMap[$k] = $r;
    }

    $cells = [];
    foreach ($days as $day) {
        foreach ($slots as $i => $slot) {
            $slotId = (int)($slot['slot_id'] ?? 0);
            if ($slotId <= 0) {
                continue;
            }
            $slotNum = (int)($slotNumMap[$slotId] ?? ($i + 1));
            $cell = [
                'day' => $day,
                'slot_id' => $slotId,
                'slot_num' => $slotNum,
            ];
            if (strcasecmp($day, 'Friday') === 0 && !$this->isFridaySlotAllowed($campusId, $slotId)) {
                $cell['empty'] = true;
                $cell['friday_inactive'] = true;
                $cells[] = $cell;
                continue;
            }

            $k = $this->adjustCellKey($day, $slotId);
            if (!isset($assignMap[$k])) {
                $cell['empty'] = true;
                $cells[] = $cell;
                continue;
            }

            $r = $assignMap[$k];
            $srcClsSecId = (int)($r['cls_sec_id'] ?? 0);
            $cell['empty'] = false;
            $cell['subject_name'] = (string)($r['subject_name'] ?? '');
            $cell['class_label'] = trim((string)($r['class_name'] ?? '') . ' - ' . (string)($r['section_name'] ?? ''));
            $cell['cls_sec_id'] = $srcClsSecId;
            $cell['subject_id'] = (int)($r['subject_id'] ?? 0);
            $cell['time_table_id'] = (int)($r['time_table_id'] ?? 0);
            $cell['is_current_section'] = ($srcClsSecId === $clsSecId);
            $cells[] = $cell;
        }
    }

    return $this->response->setJSON([
        'success' => true,
        'teacher_id' => $teacherId,
        'teacher_name' => trim((string)($teacher['first_name'] ?? '') . ' ' . (string)($teacher['last_name'] ?? '')),
        'days' => $days,
        'slots' => $slots,
        'cells' => $cells,
    ]);
}

private function buildAdjustGridPayload(int $campusId, int $clsSecId): array
{
    $slots = $this->fetchSlotsForCampus($campusId);
    if ($slots === []) {
        return ['success' => false, 'msg' => 'No slots found for this campus.'];
    }

    $days = $this->resolveWorkingDayNamesForSection($campusId, $clsSecId);
    $block = $this->buildOneClassBlock($clsSecId, $campusId, $days, $slots);
    if ($block === null) {
        return ['success' => false, 'msg' => 'Class section not found or not included on Timetable Constraints.'];
    }

    $cells = [];
    $latestTeacherSql = $this->sqlLatestTeacherAssignments();
    $rows = $this->db->table('time_table tt')
        ->select('tt.time_table_id, tt.day, tt.slot_id, tt.subject_id, sub.subject_name, lts.tid AS teacher_id, u.first_name AS teacher_first_name, u.last_name AS teacher_last_name')
        ->join('allsubject sub', 'sub.sid = tt.subject_id', 'inner')
        ->join('section_subjects ss', 'ss.cls_sec_id = tt.cls_sec_id AND ss.subject_id = tt.subject_id', 'left')
        ->join("($latestTeacherSql) lts", 'lts.cls_sec_id = ss.cls_sec_id AND lts.sec_sub_id = ss.sec_sub_id', 'left')
        ->join('users u', 'u.id = lts.tid', 'left')
        ->where('tt.cls_sec_id', $clsSecId)
        ->orderBy('tt.time_table_id', 'DESC')
        ->get()
        ->getResultArray();

    foreach ($rows as $r) {
        $d = (string)$r['day'];
        $s = (int)$r['slot_id'];
        if (!isset($cells[$d][$s])) {
            $cells[$d][$s] = [
                'time_table_id' => (int)$r['time_table_id'],
                'subject_id' => (int)$r['subject_id'],
                'subject_name' => (string)$r['subject_name'],
                'teacher_id' => (int)($r['teacher_id'] ?? 0),
                'teacher_name' => trim((string)($r['teacher_first_name'] ?? '') . ' ' . (string)($r['teacher_last_name'] ?? '')),
            ];
        }
    }

    $section = $this->db->table('class_section cs')
        ->select('c.class_name, s.section_name')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id')
        ->where('cs.cls_sec_id', $clsSecId)
        ->get()
        ->getRowArray();
    $label = trim((string)($section['class_name'] ?? 'Class') . ' - ' . (string)($section['section_name'] ?? 'Section'));

    $unplaced = $this->computeUnplacedPoolForSection($campusId, $clsSecId);
    $teacherMap = $this->getSectionSubjectTeacherMap($clsSecId);
    $fridayAllowed = $this->getFridayAllowedSlotIds($campusId);
    $fridayInactive = [];
    if (in_array('Friday', $days, true)) {
        foreach ($slots as $slot) {
            $slotId = (int)($slot['slot_id'] ?? 0);
            if ($slotId > 0 && !$this->isFridaySlotAllowed($campusId, $slotId)) {
                $fridayInactive[] = ['day' => 'Friday', 'slot_id' => $slotId];
            }
        }
    }
    $pool = [];
    foreach ($unplaced['summary'] as $s) {
        if ((int)($s['remaining'] ?? 0) <= 0) {
            continue;
        }
        $sid = (int)($s['subject_id'] ?? 0);
        $tMeta = $teacherMap[$sid] ?? ['teacher_id' => 0, 'teacher_name' => ''];
        $s['teacher_id'] = (int)($tMeta['teacher_id'] ?? 0);
        $s['teacher_name'] = trim((string)($tMeta['teacher_name'] ?? ''));
        $pool[] = $s;
    }
    $demanded = 0;
    $placed = 0;
    foreach ($unplaced['summary'] as $s) {
        $demanded += (int)$s['demanded'];
        $placed += (int)$s['placed'];
    }

    return [
        'success' => true,
        'cls_sec_id' => $clsSecId,
        'title' => $label,
        'days' => $days,
        'slots' => $slots,
        'cells' => $cells,
        'pool' => $pool,
        'friday_allowed_slot_ids' => $fridayAllowed,
        'friday_inactive' => $fridayInactive,
        'summary' => $unplaced['summary'],
        'stats' => [
            'demanded' => $demanded,
            'placed' => $placed,
            'unplaced' => max(0, $demanded - $placed),
        ],
    ];
}

private function computeUnplacedPoolForSection(int $campusId, int $clsSecId): array
{
    $demands = $this->db->table('timetable_generator_demands')
        ->select('subject_id, weekly_classes')
        ->where('campus_id', $campusId)
        ->where('cls_sec_id', $clsSecId)
        ->get()
        ->getResultArray();

    if ($demands === []) {
        $hasInclude = $this->db->fieldExists('include_in_timetable', 'section_subjects');
        $qb = $this->db->table('section_subjects ss')
            ->select('ss.subject_id, COALESCE(ss.classes_per_week, 0) AS weekly_classes')
            ->where('ss.cls_sec_id', $clsSecId)
            ->where('ss.status', 1);
        if ($hasInclude) {
            $qb->where('ss.include_in_timetable', 1);
        } else {
            $qb->where('ss.classes_per_week >', 0);
        }
        $demands = $qb->get()->getResultArray();
    }

    $placedCounts = [];
    $placedRows = $this->db->table('time_table')
        ->select('subject_id, COUNT(*) AS cnt')
        ->where('cls_sec_id', $clsSecId)
        ->groupBy('subject_id')
        ->get()
        ->getResultArray();
    foreach ($placedRows as $r) {
        $placedCounts[(int)$r['subject_id']] = (int)$r['cnt'];
    }

    $subjectIds = array_values(array_unique(array_map(static fn($d) => (int)($d['subject_id'] ?? 0), $demands)));
    $subjectIds = array_values(array_filter($subjectIds, static fn($v) => $v > 0));
    $subMap = [];
    if ($subjectIds !== []) {
        $subRows = $this->db->table('allsubject')
            ->select('sid, subject_name')
            ->whereIn('sid', $subjectIds)
            ->get()
            ->getResultArray();
        foreach ($subRows as $sr) {
            $subMap[(int)$sr['sid']] = (string)$sr['subject_name'];
        }
    }

    $summary = [];
    foreach ($demands as $d) {
        $sid = (int)($d['subject_id'] ?? 0);
        if ($sid <= 0) {
            continue;
        }
        $needed = max(0, (int)($d['weekly_classes'] ?? 0));
        $have = (int)($placedCounts[$sid] ?? 0);
        $remaining = max(0, $needed - $have);
        $summary[] = [
            'subject_id' => $sid,
            'subject_name' => $subMap[$sid] ?? ('Subject #' . $sid),
            'demanded' => $needed,
            'placed' => min($have, $needed),
            'remaining' => $remaining,
            'count' => $remaining,
        ];
    }

    return ['summary' => $summary];
}

private function getCampusIdForSection(int $clsSecId): int
{
    $row = $this->db->table('class_section')
        ->select('campus_id')
        ->where('cls_sec_id', $clsSecId)
        ->get()
        ->getRowArray();

    return (int)($row['campus_id'] ?? 0);
}

/** Slot IDs allowed on Friday (from generator half-day settings). */
private function getFridayAllowedSlotIds(int $campusId): array
{
    $this->ensureSlotsBreakColumn();
    $slots = $this->fetchSlotsForCampus($campusId);
    $slotIds = $this->teachingSlotIdsFromList($slots);
    if ($slotIds === []) {
        return [];
    }
    $options = $this->loadGeneratorOptions($campusId, $slotIds);

    return array_values(array_unique(array_map('intval', (array)($options['friday_active_slots'] ?? $slotIds))));
}

private function isFridaySlotAllowed(int $campusId, int $slotId): bool
{
    $allowed = $this->getFridayAllowedSlotIds($campusId);
    if ($allowed === []) {
        return true;
    }

    return in_array($slotId, $allowed, true);
}

/** @return array<int, array{teacher_id: int, teacher_name: string}> */
private function getSectionSubjectTeacherMap(int $clsSecId): array
{
    $latestTeacherSql = $this->sqlLatestTeacherAssignments();
    $rows = $this->db->query("
        SELECT ss.subject_id, lts.tid, u.first_name, u.last_name
        FROM section_subjects ss
        LEFT JOIN ({$latestTeacherSql}) lts
          ON lts.cls_sec_id = ss.cls_sec_id AND lts.sec_sub_id = ss.sec_sub_id
        LEFT JOIN users u ON u.id = lts.tid
        WHERE ss.cls_sec_id = ? AND ss.status = 1
    ", [$clsSecId])->getResultArray();
    $out = [];
    foreach ($rows as $r) {
        $sid = (int)($r['subject_id'] ?? 0);
        if ($sid <= 0) {
            continue;
        }
        $out[$sid] = [
            'teacher_id' => (int)($r['tid'] ?? 0),
            'teacher_name' => trim((string)($r['first_name'] ?? '') . ' ' . (string)($r['last_name'] ?? '')),
        ];
    }

    return $out;
}

private function adjustCellKey(string $day, int $slotId): string
{
    return $day . '|' . $slotId;
}

/**
 * @return array{success: bool, msg: string}
 */
private function deleteAdjustMoveFromRow($db, int $campusId, int $timeTableId, int $clsSecId, string $day, int $slotId): array
{
    if (!in_array($day, $this->canonicalWeekdayOrder(), true)) {
        return ['success' => false, 'msg' => 'Invalid source day.'];
    }

    $owned = $this->db->table('class_section')
        ->where('cls_sec_id', $clsSecId)
        ->where('campus_id', $campusId)
        ->countAllResults();
    if ($owned < 1) {
        return ['success' => false, 'msg' => 'Source class section not found for this campus.'];
    }

    $row = $db->table('time_table')
        ->where('time_table_id', $timeTableId)
        ->where('cls_sec_id', $clsSecId)
        ->where('day', $day)
        ->where('slot_id', $slotId)
        ->get()
        ->getRowArray();
    if (!$row) {
        return ['success' => false, 'msg' => 'Source assignment not found.'];
    }

    $db->table('time_table')->where('time_table_id', $timeTableId)->delete();

    return ['success' => true, 'msg' => 'Source cleared.'];
}

/** @return array<int, int> slot_id => 1-based slot number */
private function buildSlotNumMap(array $slots): array
{
    $map = [];
    foreach ($slots as $i => $slot) {
        $slotId = (int)($slot['slot_id'] ?? 0);
        if ($slotId > 0) {
            $map[$slotId] = $i + 1;
        }
    }

    return $map;
}

/**
 * @return array<string, array<string, mixed>>
 */
private function loadSectionCellMap($db, int $clsSecId): array
{
    $latestTeacherSql = $this->sqlLatestTeacherAssignments();
    $rows = $db->query("
        SELECT tt.day, tt.slot_id, tt.subject_id, sub.subject_name,
               u.first_name, u.last_name
        FROM time_table tt
        JOIN allsubject sub ON sub.sid = tt.subject_id
        LEFT JOIN section_subjects ss
          ON ss.cls_sec_id = tt.cls_sec_id AND ss.subject_id = tt.subject_id
        LEFT JOIN ({$latestTeacherSql}) lts
          ON lts.cls_sec_id = ss.cls_sec_id AND lts.sec_sub_id = ss.sec_sub_id
        LEFT JOIN users u ON u.id = lts.tid
        WHERE tt.cls_sec_id = ?
    ", [$clsSecId])->getResultArray();

    $map = [];
    foreach ($rows as $r) {
        $key = $this->adjustCellKey((string)$r['day'], (int)$r['slot_id']);
        if (!isset($map[$key])) {
            $map[$key] = [
                'subject_id' => (int)($r['subject_id'] ?? 0),
                'subject_name' => (string)($r['subject_name'] ?? ''),
                'teacher_name' => trim((string)($r['first_name'] ?? '') . ' ' . (string)($r['last_name'] ?? '')),
            ];
        }
    }

    return $map;
}

/**
 * All day/slot cells where this teacher is scheduled (campus-wide).
 *
 * @return array<string, array<string, mixed>>
 */
private function loadTeacherBusyMap($db, int $teacherId, int $campusId): array
{
    if ($teacherId <= 0 || $campusId <= 0) {
        return [];
    }

    $latestTeacherSql = $this->sqlLatestTeacherAssignments();
    $rows = $db->query("
        SELECT tt.time_table_id, tt.cls_sec_id, tt.day, tt.slot_id, tt.subject_id,
               sub.subject_name, c.class_name, s.section_name
        FROM time_table tt
        JOIN section_subjects ss
          ON ss.cls_sec_id = tt.cls_sec_id
         AND ss.subject_id = tt.subject_id
        JOIN allsubject sub ON sub.sid = tt.subject_id
        JOIN class_section cs ON cs.cls_sec_id = tt.cls_sec_id
        JOIN classes c ON c.class_id = cs.class_id
        JOIN sections s ON s.section_id = cs.section_id
        JOIN ({$latestTeacherSql}) cur
          ON cur.cls_sec_id = ss.cls_sec_id
         AND cur.sec_sub_id = ss.sec_sub_id
        WHERE cur.tid = ? AND cs.campus_id = ?
    ", [$teacherId, $campusId])->getResultArray();

    $map = [];
    foreach ($rows as $r) {
        $key = $this->adjustCellKey((string)$r['day'], (int)$r['slot_id']);
        if (!isset($map[$key])) {
            $map[$key] = [
                'time_table_id' => (int)($r['time_table_id'] ?? 0),
                'cls_sec_id' => (int)($r['cls_sec_id'] ?? 0),
                'day' => (string)($r['day'] ?? ''),
                'slot_id' => (int)($r['slot_id'] ?? 0),
                'subject_id' => (int)($r['subject_id'] ?? 0),
                'subject_name' => (string)($r['subject_name'] ?? ''),
                'class_name' => (string)($r['class_name'] ?? ''),
                'section_name' => (string)($r['section_name'] ?? ''),
            ];
        }
    }

    return $map;
}

/**
 * @return array{
 *   teacher_info: array{teacher_id: int, teacher_name: string}|null,
 *   has_teacher: bool,
 *   subject_name: string,
 *   section_cell_map: array<string, array<string, mixed>>,
 *   teacher_busy_map: array<string, array<string, mixed>>,
 *   slot_num_map: array<int, int>
 * }
 */
private function buildAdjustSlotContext($db, int $campusId, int $clsSecId, int $subjectId, array $days, array $slots): array
{
    $teacherInfo = $this->getTeacherForSectionSubject($db, $clsSecId, $subjectId);
    $subRow = $db->table('allsubject')->select('subject_name')->where('sid', $subjectId)->get()->getRowArray();
    $teacherId = (int)($teacherInfo['teacher_id'] ?? 0);

    return [
        'teacher_info' => $teacherInfo,
        'has_teacher' => $teacherId > 0,
        'subject_name' => (string)($subRow['subject_name'] ?? ''),
        'section_cell_map' => $this->loadSectionCellMap($db, $clsSecId),
        'teacher_busy_map' => $this->loadTeacherBusyMap($db, $teacherId, $campusId),
        'slot_num_map' => $this->buildSlotNumMap($slots),
    ];
}

/**
 * @param array<string, array<string, mixed>> $sectionCellMap
 * @param array<string, array<string, mixed>> $teacherBusyMap
 * @return array<string, mixed>
 */
private function classifyAdjustSlot(
    string $day,
    int $slotId,
    int $campusId,
    int $clsSecId,
    array $sectionCellMap,
    array $teacherBusyMap,
    bool $hasTeacher
): array {
    if (strcasecmp($day, 'Friday') === 0 && !$this->isFridaySlotAllowed($campusId, $slotId)) {
        return [
            'case' => 0,
            'teacher_free' => false,
            'slot_empty' => true,
            'highlight' => 'friday_inactive',
            'direct_place' => false,
            'needs_force' => false,
            'conflict' => null,
            'occupied' => null,
        ];
    }

    $key = $this->adjustCellKey($day, $slotId);
    $occupied = $sectionCellMap[$key] ?? null;
    $slotEmpty = ($occupied === null);

    $teacherFree = true;
    $conflict = null;
    if ($hasTeacher && isset($teacherBusyMap[$key])) {
        $busy = $teacherBusyMap[$key];
        $isSameCell = (int)($busy['cls_sec_id'] ?? 0) === $clsSecId
            && (string)($busy['day'] ?? '') === $day
            && (int)($busy['slot_id'] ?? 0) === $slotId;
        if (!$isSameCell) {
            $teacherFree = false;
            $classLabel = trim((string)($busy['class_name'] ?? '') . ' - ' . (string)($busy['section_name'] ?? ''));
            $conflict = [
                'cls_sec_id' => (int)($busy['cls_sec_id'] ?? 0),
                'subject_name' => (string)($busy['subject_name'] ?? ''),
                'class_label' => $classLabel,
            ];
        }
    }

    if ($teacherFree && $slotEmpty) {
        $case = 1;
    } elseif (!$teacherFree && $slotEmpty) {
        $case = 2;
    } elseif ($teacherFree && !$slotEmpty) {
        $case = 3;
    } else {
        $case = 4;
    }

    return [
        'case' => $case,
        'teacher_free' => $teacherFree,
        'slot_empty' => $slotEmpty,
        'highlight' => $teacherFree ? 'green' : 'red',
        'direct_place' => in_array($case, [1, 3], true),
        'needs_force' => in_array($case, [2, 4], true),
        'conflict' => $conflict,
        'occupied' => $occupied,
    ];
}

private function adjustBlockedReason(array $classified, string $teacherName): string
{
    $conflict = $classified['conflict'] ?? null;
    if (!$conflict) {
        return ($teacherName !== '' ? $teacherName : 'Teacher') . ' is busy at this time.';
    }
    $label = trim((string)($conflict['class_label'] ?? ''));
    $sub = (string)($conflict['subject_name'] ?? 'another subject');
    $who = $teacherName !== '' ? $teacherName : 'Teacher';
    if ($label !== '' && $label !== '-') {
        return $who . ' → ' . $sub . ' (' . $label . ')';
    }

    return $who . ' → ' . $sub;
}

/**
 * @return array{teacher_id: int, teacher_name: string}|null
 */
private function getTeacherForSectionSubject($db, int $clsSecId, int $subjectId): ?array
{
    $secSub = $db->table('section_subjects')
        ->select('sec_sub_id')
        ->where('cls_sec_id', $clsSecId)
        ->where('subject_id', $subjectId)
        ->where('status', 1)
        ->get()->getRow();
    if (!$secSub) {
        return null;
    }

    $teacherRow = $db->query('
        SELECT ts.tid
        FROM teacher_subjects ts
        WHERE ts.cls_sec_id = ? AND ts.sec_sub_id = ? AND ts.status = 1
        ORDER BY ts.sst DESC
        LIMIT 1
    ', [$clsSecId, (int)$secSub->sec_sub_id])->getRow();
    if (!$teacherRow || empty($teacherRow->tid)) {
        return null;
    }

    $user = $db->table('users')
        ->select('first_name, last_name')
        ->where('id', (int)$teacherRow->tid)
        ->get()->getRowArray();

    return [
        'teacher_id' => (int)$teacherRow->tid,
        'teacher_name' => trim((string)($user['first_name'] ?? '') . ' ' . (string)($user['last_name'] ?? '')),
    ];
}

/**
 * @return array<string, mixed>|null
 */
private function findTeacherConflictAtSlot($db, int $teacherId, string $day, int $slotId, int $excludeClsSecId): ?array
{
    if ($teacherId <= 0) {
        return null;
    }

    $latestTeacherSql = $this->sqlLatestTeacherAssignments();
    $row = $db->query("
        SELECT tt.time_table_id, tt.cls_sec_id, tt.subject_id, tt.day, tt.slot_id,
               sub.subject_name, c.class_name, s.section_name,
               u.first_name, u.last_name
        FROM time_table tt
        JOIN section_subjects ss
          ON ss.cls_sec_id = tt.cls_sec_id
         AND ss.subject_id = tt.subject_id
        JOIN allsubject sub ON sub.sid = tt.subject_id
        JOIN class_section cs ON cs.cls_sec_id = tt.cls_sec_id
        JOIN classes c ON c.class_id = cs.class_id
        JOIN sections s ON s.section_id = cs.section_id
        JOIN ({$latestTeacherSql}) cur
          ON cur.cls_sec_id = ss.cls_sec_id
         AND cur.sec_sub_id = ss.sec_sub_id
        JOIN users u ON u.id = cur.tid
        WHERE tt.day = ?
          AND tt.slot_id = ?
          AND cur.tid = ?
          AND NOT (tt.cls_sec_id = ? AND tt.day = ? AND tt.slot_id = ?)
        LIMIT 1
    ", [$day, $slotId, $teacherId, $excludeClsSecId, $day, $slotId])->getRowArray();

    if (!$row) {
        return null;
    }

    $row['teacher_name'] = trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? ''));

    return $row;
}

/**
 * @return array{subject_name: string, teacher_name: string}
 */
private function getOccupiedCellDetail($db, int $clsSecId, string $day, int $slotId): array
{
    $latestTeacherSql = $this->sqlLatestTeacherAssignments();
    $row = $db->query("
        SELECT sub.subject_name, u.first_name, u.last_name
        FROM time_table tt
        JOIN allsubject sub ON sub.sid = tt.subject_id
        LEFT JOIN section_subjects ss
          ON ss.cls_sec_id = tt.cls_sec_id AND ss.subject_id = tt.subject_id
        LEFT JOIN ({$latestTeacherSql}) lts
          ON lts.cls_sec_id = ss.cls_sec_id AND lts.sec_sub_id = ss.sec_sub_id
        LEFT JOIN users u ON u.id = lts.tid
        WHERE tt.cls_sec_id = ? AND tt.day = ? AND tt.slot_id = ?
        LIMIT 1
    ", [$clsSecId, $day, $slotId])->getRowArray();

    return [
        'subject_name' => (string)($row['subject_name'] ?? 'another subject'),
        'teacher_name' => trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? '')),
    ];
}

/**
 * @return array{success: bool, msg: string, code?: string}
 */
private function validateTimetableCellPlacement($db, int $clsSecId, string $day, int $slotId, int $subjectId, bool $allowSameSubjectDay, int $campusId = 0): array
{
    if ($campusId <= 0) {
        $campusId = $this->getCampusIdForSection($clsSecId);
    }
    if (strcasecmp($day, 'Friday') === 0 && !$this->isFridaySlotAllowed($campusId, $slotId)) {
        return [
            'success' => false,
            'msg' => 'Friday half-day: this slot is permanently inactive.',
            'code' => 'friday_inactive',
        ];
    }

    $secSub = $db->table('section_subjects')
        ->select('sec_sub_id')
        ->where('cls_sec_id', $clsSecId)
        ->where('subject_id', $subjectId)
        ->where('status', 1)
        ->get()->getRow();
    if (!$secSub) {
        return ['success' => false, 'msg' => 'Subject is not offered in this section.', 'code' => 'not_in_section'];
    }
    $secSubId = (int)$secSub->sec_sub_id;

    if (!$allowSameSubjectDay) {
        $dup = $db->table('time_table')
            ->select('slot_id')
            ->where('cls_sec_id', $clsSecId)
            ->where('day', $day)
            ->where('subject_id', $subjectId)
            ->where('slot_id !=', $slotId)
            ->get()->getRow();
        if ($dup) {
            return ['success' => false, 'msg' => "This subject is already scheduled on {$day}.", 'code' => 'duplicate_day'];
        }
    }

    $occupied = $db->table('time_table')
        ->select('subject_id')
        ->where('cls_sec_id', $clsSecId)
        ->where('day', $day)
        ->where('slot_id', $slotId)
        ->get()->getRow();
    if ($occupied && (int)$occupied->subject_id === $subjectId) {
        return ['success' => true, 'msg' => 'Already placed here.'];
    }
    if ($occupied) {
        $occDetail = $this->getOccupiedCellDetail($db, $clsSecId, $day, $slotId);
        $msg = 'Slot occupied by ' . $occDetail['subject_name'];
        if ($occDetail['teacher_name'] !== '') {
            $msg .= ' (' . $occDetail['teacher_name'] . ')';
        }
        $msg .= '. Force place to replace.';

        return ['success' => false, 'msg' => $msg, 'code' => 'occupied'];
    }

    $teacherInfo = $this->getTeacherForSectionSubject($db, $clsSecId, $subjectId);
    if ($teacherInfo) {
        $conflict = $this->findTeacherConflictAtSlot($db, $teacherInfo['teacher_id'], $day, $slotId, $clsSecId);
        if ($conflict) {
            $teacherName = $teacherInfo['teacher_name'] !== '' ? $teacherInfo['teacher_name'] : 'Teacher';
            $classLabel = trim((string)($conflict['class_name'] ?? '') . ' - ' . (string)($conflict['section_name'] ?? ''));
            $msg = $teacherName . ' is teaching ' . (string)($conflict['subject_name'] ?? 'another subject');
            if ($classLabel !== '' && $classLabel !== '-') {
                $msg .= ' in ' . $classLabel;
            }
            $msg .= ' at this time.';

            return ['success' => false, 'msg' => $msg, 'code' => 'teacher_conflict'];
        }
    }

    return ['success' => true, 'msg' => 'OK'];
}

/**
 * Describe what force placement would change (for confirmation dialog).
 */
private function previewForcePlacement(int $campusId, int $clsSecId, string $day, int $slotId, int $subjectId): array
{
    $db = $this->db;
    $validation = $this->validateTimetableCellPlacement($db, $clsSecId, $day, $slotId, $subjectId, true, $campusId);

    $subRow = $db->table('allsubject')->select('subject_name')->where('sid', $subjectId)->get()->getRowArray();
    $subjectName = (string)($subRow['subject_name'] ?? ('Subject #' . $subjectId));

    $section = $db->table('class_section cs')
        ->select('c.class_name, s.section_name')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id')
        ->where('cs.cls_sec_id', $clsSecId)
        ->get()
        ->getRowArray();
    $sectionLabel = trim((string)($section['class_name'] ?? 'Class') . ' - ' . (string)($section['section_name'] ?? 'Section'));

    $teacherInfo = $this->getTeacherForSectionSubject($db, $clsSecId, $subjectId);
    $teacherName = (string)($teacherInfo['teacher_name'] ?? '');

    $slotNum = $slotId;
    $slots = $this->fetchSlotsForCampus($campusId);
    foreach ($slots as $i => $s) {
        if ((int)($s['slot_id'] ?? 0) === $slotId) {
            $slotNum = $i + 1;
            break;
        }
    }

    $existingTarget = $db->table('time_table tt')
        ->select('tt.day, tt.slot_id, sub.subject_name')
        ->join('allsubject sub', 'sub.sid = tt.subject_id', 'inner')
        ->where('tt.cls_sec_id', $clsSecId)
        ->where('tt.day', $day)
        ->where('tt.slot_id', $slotId)
        ->get()
        ->getRowArray();

    $conflict = null;
    if ($teacherInfo) {
        $conflict = $this->findTeacherConflictAtSlot($db, $teacherInfo['teacher_id'], $day, $slotId, $clsSecId);
    }

    $replaced = null;
    if ($existingTarget) {
        $occDetail = $this->getOccupiedCellDetail($db, $clsSecId, $day, $slotId);
        $replaced = [
            'subject_name' => (string)($existingTarget['subject_name'] ?? 'Subject'),
            'teacher_name' => (string)($occDetail['teacher_name'] ?? ''),
            'section_label' => $sectionLabel,
        ];
    }

    $unplaced = [];
    if ($replaced) {
        $unplaced[] = array_merge($replaced, ['reason' => 'replace']);
    }
    if ($conflict) {
        $conflictLabel = trim((string)($conflict['class_name'] ?? '') . ' - ' . (string)($conflict['section_name'] ?? ''));
        $unplaced[] = [
            'subject_name' => (string)($conflict['subject_name'] ?? 'Subject'),
            'teacher_name' => (string)($conflict['teacher_name'] ?? $teacherName),
            'section_label' => $conflictLabel,
            'reason' => 'teacher_busy',
        ];
    }

    $kind = 'occupied';
    if ($replaced && $conflict) {
        $kind = 'both';
    } elseif ($conflict) {
        $kind = 'teacher_busy';
    } elseif (!$replaced) {
        $kind = 'other';
    }

    return [
        'code' => (string)($validation['code'] ?? 'blocked'),
        'subject_name' => $subjectName,
        'teacher_name' => $teacherName,
        'target' => ['day' => $day, 'slot_id' => $slotId, 'slot_num' => $slotNum],
        'visual' => [
            'kind' => $kind,
            'day' => $day,
            'slot_num' => $slotNum,
            'incoming' => [
                'subject_name' => $subjectName,
                'teacher_name' => $teacherName,
                'section_label' => $sectionLabel,
            ],
            'replaced' => $replaced,
            'unplaced' => $unplaced,
        ],
    ];
}

/**
 * @return array{success: bool, msg: string, code?: string}
 */
private function forcePlaceSubjectInCell($db, int $clsSecId, string $day, int $slotId, int $subjectId, int $campusId = 0, bool $poolAdd = false): array
{
    if ($campusId <= 0) {
        $campusId = $this->getCampusIdForSection($clsSecId);
    }
    if (strcasecmp($day, 'Friday') === 0 && !$this->isFridaySlotAllowed($campusId, $slotId)) {
        return [
            'success' => false,
            'msg' => 'Friday half-day: this slot is permanently inactive and cannot be used.',
            'code' => 'friday_inactive',
        ];
    }

    $db->transBegin();
    try {
        $existingTarget = $db->table('time_table')
            ->where(['cls_sec_id' => $clsSecId, 'day' => $day, 'slot_id' => $slotId])
            ->get()->getRowArray();
        if ($existingTarget) {
            $db->table('time_table')->where('time_table_id', (int)$existingTarget['time_table_id'])->delete();
        }

        $teacherInfo = $this->getTeacherForSectionSubject($db, $clsSecId, $subjectId);
        if ($teacherInfo) {
            $conflict = $this->findTeacherConflictAtSlot($db, $teacherInfo['teacher_id'], $day, $slotId, $clsSecId);
            if ($conflict) {
                $db->table('time_table')->where('time_table_id', (int)$conflict['time_table_id'])->delete();
            }
        }

        if (!$poolAdd) {
            $oldSame = $db->table('time_table')
                ->where('cls_sec_id', $clsSecId)
                ->where('subject_id', $subjectId)
                ->where('day !=', $day)
                ->orderBy('time_table_id', 'ASC')
                ->get()->getRowArray();
            if ($oldSame) {
                $db->table('time_table')->where('time_table_id', (int)$oldSame['time_table_id'])->delete();
            } else {
                $oldSame = $db->table('time_table')
                    ->where('cls_sec_id', $clsSecId)
                    ->where('subject_id', $subjectId)
                    ->where('slot_id !=', $slotId)
                    ->orderBy('time_table_id', 'ASC')
                    ->get()->getRowArray();
                if ($oldSame) {
                    $db->table('time_table')->where('time_table_id', (int)$oldSame['time_table_id'])->delete();
                }
            }
        }

        $db->table('time_table')->insert([
            'cls_sec_id' => $clsSecId,
            'day' => $day,
            'slot_id' => $slotId,
            'subject_id' => $subjectId,
            'created_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s'),
            'user_id' => (int)($this->session->get('member_userid') ?? 0),
        ]);
        $db->transCommit();

        return ['success' => true, 'msg' => 'Force placement applied.'];
    } catch (\Throwable $e) {
        $db->transRollback();

        return ['success' => false, 'msg' => $e->getMessage()];
    }
}

/**
 * Latest active teacher assignment per (class section, section-subject), matching timetable UI logic.
 */
private function sqlLatestTeacherAssignments(): string
{
    return "
        SELECT t1.cls_sec_id, t1.sec_sub_id, t1.tid
        FROM teacher_subjects t1
        JOIN (
            SELECT cls_sec_id, sec_sub_id, MAX(sst) AS max_sst
            FROM teacher_subjects
            WHERE status = 1
            GROUP BY cls_sec_id, sec_sub_id
        ) t2
          ON t2.cls_sec_id = t1.cls_sec_id
         AND t2.sec_sub_id = t1.sec_sub_id
         AND t2.max_sst   = t1.sst
    ";
}

/** Per-day school check-in / check-out labels for a section (campus-scoped). */
private function getDayTimingLabelsForSection(int $campusId, int $clsSecId): array
{
    $rows = getSchoolTimingsForSections([$clsSecId], $campusId);

    $out = [];
    foreach ($rows as $row) {
        $dn = trim((string) ($row['dayname'] ?? ''));
        $canon = null;
        foreach ($this->canonicalWeekdayOrder() as $c) {
            if (strcasecmp($dn, $c) === 0) {
                $canon = $c;
                break;
            }
        }
        if ($canon === null) {
            continue;
        }
        $out[$canon] = [
            'checkin'  => $this->formatTimeForDisplay($row['checkin_timing'] ?? null),
            'checkout' => $this->formatTimeForDisplay($row['checkout_timing'] ?? null),
        ];
    }

    return $out;
}

/** Calendar order for column headers. */
private function canonicalWeekdayOrder(): array
{
    return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
}

private function formatTimeForDisplay(?string $t): string
{
    if ($t === null || $t === '') {
        return '';
    }
    try {
        $dt = new \DateTime((string) $t);

        return $dt->format('h:i A');
    } catch (\Throwable $e) {
        return (string) $t;
    }
}

/**
 * Day columns for one class section: only days where that section's school timing
 * (active type) has check-in and check-out set and different. No campus-wide union.
 */
private function resolveWorkingDayNamesForSection(int $campusId, int $clsSecId): array
{
    $canonical = $this->canonicalWeekdayOrder();
    $rows = getSchoolTimingsForSections([$clsSecId], $campusId);

    $found = [];
    foreach ($rows as $row) {
        if (! isSchoolTimingWorkingDay($row['checkin_timing'] ?? null, $row['checkout_timing'] ?? null)) {
            continue;
        }
        $dn = trim((string) ($row['dayname'] ?? ''));
        foreach ($canonical as $c) {
            if (strcasecmp($dn, $c) === 0) {
                $found[$c] = true;
                break;
            }
        }
    }

    $ordered = [];
    foreach ($canonical as $c) {
        if (isset($found[$c])) {
            $ordered[] = $c;
        }
    }

    if ($ordered === []) {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    }

    return $ordered;
}

/**
 * For a teacher: union of working days for each class section they teach (timetable rows on this campus).
 * Saturday appears only if at least one of those sections has Saturday "on" in school timing.
 */
private function resolveWorkingDayNamesForTeacher(int $campusId, int $teacherId): array
{
    $latestTeacherSql = $this->sqlLatestTeacherAssignments();
    $sql = "
        SELECT DISTINCT tt.cls_sec_id
        FROM time_table tt
        INNER JOIN class_section cs ON cs.cls_sec_id = tt.cls_sec_id
        INNER JOIN section_subjects ss
            ON ss.cls_sec_id = tt.cls_sec_id AND ss.subject_id = tt.subject_id AND ss.status = 1
        INNER JOIN ({$latestTeacherSql}) lts
            ON lts.cls_sec_id = ss.cls_sec_id AND lts.sec_sub_id = ss.sec_sub_id
        WHERE lts.tid = ?
          AND cs.campus_id = ?
    ";
    $sectionRows = $this->db->query($sql, [$teacherId, $campusId])->getResultArray();

    $merged = [];
    foreach ($sectionRows as $sr) {
        $cid = (int)($sr['cls_sec_id'] ?? 0);
        if ($cid <= 0) {
            continue;
        }
        foreach ($this->resolveWorkingDayNamesForSection($campusId, $cid) as $d) {
            $merged[$d] = true;
        }
    }

    $canonical = $this->canonicalWeekdayOrder();
    $ordered = [];
    foreach ($canonical as $c) {
        if (isset($merged[$c])) {
            $ordered[] = $c;
        }
    }

    if (!empty($ordered)) {
        return $ordered;
    }

    return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
}

private function ensureSlotsBreakColumn(): void
{
    try {
        if (!$this->db->fieldExists('is_break', 'slots')) {
            $this->db->query('ALTER TABLE slots ADD COLUMN is_break TINYINT(1) NOT NULL DEFAULT 0');
        }
    } catch (\Throwable $e) {
        log_message('error', 'Timetable::ensureSlotsBreakColumn failed: ' . $e->getMessage());
    }
}

/**
 * Build start/end times from day start + per-row bell (end) times.
 *
 * @param list<array<string, mixed>> $rows
 * @return array{rows: list<array<string, mixed>>, errors: list<string>}
 */
private function resolveSlotTimesFromBells(string $dayStart, array $rows): array
{
    $dayStart = $this->normalizeTimeForSlot($dayStart);
    if ($dayStart === '') {
        return ['rows' => [], 'errors' => ['Day start time is required.']];
    }

    $prevBell = $dayStart;
    $out = [];
    $errors = [];

    foreach ($rows as $i => $row) {
        $bell = $this->normalizeTimeForSlot((string) ($row['bell_time'] ?? $row['end_time'] ?? ''));
        $slotName = trim((string) ($row['slot_name'] ?? ''));
        $rowNum = $i + 1;

        if ($slotName === '') {
            $errors[] = 'Slot ' . $rowNum . ': name is required.';
            continue;
        }
        if ($bell === '') {
            $errors[] = 'Slot ' . $rowNum . ': bell time is required.';
            continue;
        }

        $startTime = $i === 0 ? $dayStart : $prevBell;
        if (strtotime($startTime) === false || strtotime($bell) === false || strtotime($startTime) >= strtotime($bell)) {
            $errors[] = 'Slot ' . $rowNum . ': bell must be after ' . ($i === 0 ? 'day start' : 'the previous bell') . '.';
            continue;
        }

        $out[] = array_merge($row, [
            'start_time' => $startTime,
            'end_time' => $bell,
        ]);
        $prevBell = $bell;
    }

    return ['rows' => $out, 'errors' => $errors];
}

private function normalizeTimeForSlot(string $time): string
{
    $time = trim($time);
    if ($time === '') {
        return '';
    }
    if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $time, $m)) {
        return sprintf('%02d:%02d:00', (int) $m[1], (int) $m[2]);
    }

    $ts = strtotime($time);

    return $ts !== false ? date('H:i:s', $ts) : '';
}

private function formatClassSectionShort(
    ?string $classShort,
    ?string $className,
    ?string $sectionShort,
    ?string $sectionName
): string {
    $class = trim((string) ($classShort ?: $className ?: ''));
    $sec = trim((string) ($sectionShort ?: ''));
    if ($sec === '' && !empty($sectionName)) {
        $sec = strtoupper(mb_substr(trim((string) $sectionName), 0, 1));
    }

    return trim($class . ($sec !== '' ? '-' . $sec : ''));
}

/** @return list<int> */
private function teachingSlotIdsFromList(array $slots): array
{
    $out = [];
    foreach ($slots as $s) {
        if ((int) ($s['is_break'] ?? 0) === 1) {
            continue;
        }
        $id = (int) ($s['slot_id'] ?? 0);
        if ($id > 0) {
            $out[] = $id;
        }
    }

    return $out;
}

private function fetchSlotsForCampus(int $campusId): array
{
    $this->ensureSlotsBreakColumn();

    return $this->db->table('slots')
        ->where('campus_id', $campusId)
        ->orderBy('start_time', 'ASC')
        ->get()
        ->getResultArray();
}

private function applyClassSectionTimetableIncludedFilter($qb, string $alias = 'cs'): void
{
    if ($this->db->fieldExists('include_in_timetable', 'class_section')) {
        $qb->where($alias . '.include_in_timetable', 1);
    }
}

/** Sections enabled on Timetable Constraints (include_in_timetable), ordered by class_id. */
private function fetchSectionsForCampus(int $campusId): array
{
    $qb = $this->db->table('class_section cs')
        ->select('cs.cls_sec_id, cs.class_id, c.class_name, s.section_name')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id')
        ->where('cs.campus_id', $campusId)
        ->where('cs.status', 1);
    $this->applyClassSectionTimetableIncludedFilter($qb, 'cs');

    return $qb
        ->orderBy('cs.class_id', 'ASC')
        ->orderBy('cs.section_id', 'ASC')
        ->get()
        ->getResultArray();
}

/** Teachers with at least one placed period on an included section (current assignment). */
private function fetchTeachersWithTimetableForCampus(int $campusId): array
{
    $latestTeacherSql = $this->sqlLatestTeacherAssignments();
    $includeSectionSql = $this->db->fieldExists('include_in_timetable', 'class_section')
        ? ' AND cs.include_in_timetable = 1'
        : '';

    return $this->db->query("
        SELECT DISTINCT u.id, u.first_name, u.last_name
        FROM time_table tt
        INNER JOIN class_section cs ON cs.cls_sec_id = tt.cls_sec_id
        INNER JOIN section_subjects ss
          ON ss.cls_sec_id = tt.cls_sec_id
         AND ss.subject_id = tt.subject_id
         AND ss.status = 1
        INNER JOIN ({$latestTeacherSql}) lts
          ON lts.cls_sec_id = ss.cls_sec_id
         AND lts.sec_sub_id = ss.sec_sub_id
        INNER JOIN users u ON u.id = lts.tid
        WHERE cs.campus_id = ?
          AND cs.status = 1
          {$includeSectionSql}
        ORDER BY u.first_name ASC, u.last_name ASC
    ", [$campusId])->getResultArray();
}

private function timetableMatrixHasContent(array $matrix): bool
{
    foreach ($matrix as $daySlots) {
        if (!is_array($daySlots)) {
            continue;
        }
        foreach ($daySlots as $cells) {
            if (!empty($cells)) {
                return true;
            }
        }
    }

    return false;
}

private function initializeTimetableMatrix(array $days, array $slots): array
{
    $matrix = [];
    foreach ($days as $day) {
        foreach ($slots as $slot) {
            $matrix[$day][(int)$slot['slot_id']] = [];
        }
    }

    return $matrix;
}

private function buildOneClassBlock(int $clsSecId, int $campusId, array $days, array $slots): ?array
{
    $sectionQb = $this->db->table('class_section')
        ->where('cls_sec_id', $clsSecId)
        ->where('campus_id', $campusId)
        ->where('status', 1);
    $this->applyClassSectionTimetableIncludedFilter($sectionQb, 'class_section');
    if ($sectionQb->countAllResults() < 1) {
        return null;
    }

    $section = $this->db->table('class_section cs')
        ->select('c.class_name, s.section_name')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id')
        ->where('cs.cls_sec_id', $clsSecId)
        ->get()
        ->getRowArray();

    $title = trim((string)($section['class_name'] ?? 'Unknown') . ' - ' . (string)($section['section_name'] ?? ''));

    $matrix = $this->initializeTimetableMatrix($days, $slots);
    $latestTeacherSql = $this->sqlLatestTeacherAssignments();

    $rows = $this->db->table('time_table tt')
        ->select("tt.time_table_id, tt.day, tt.slot_id, sub.subject_name, u.first_name AS teacher_first_name, u.last_name AS teacher_last_name")
        ->join('allsubject sub', 'sub.sid = tt.subject_id', 'inner')
        ->join('section_subjects ss', 'ss.cls_sec_id = tt.cls_sec_id AND ss.subject_id = tt.subject_id', 'left')
        ->join("($latestTeacherSql) lts", 'lts.cls_sec_id = ss.cls_sec_id AND lts.sec_sub_id = ss.sec_sub_id', 'left')
        ->join('users u', 'u.id = lts.tid', 'left')
        ->where('tt.cls_sec_id', $clsSecId)
        ->orderBy('tt.day', 'ASC')
        ->orderBy('tt.slot_id', 'ASC')
        ->orderBy('tt.time_table_id', 'DESC')
        ->get()
        ->getResultArray();

    foreach ($rows as $r) {
        $d = (string)$r['day'];
        $s = (int)$r['slot_id'];
        if (!isset($matrix[$d][$s])) {
            continue;
        }
        if (!empty($matrix[$d][$s])) {
            continue;
        }
        $matrix[$d][$s][] = [
            'subject_name' => (string)$r['subject_name'],
            'teacher_name' => trim((string)($r['teacher_first_name'] ?? '') . ' ' . (string)($r['teacher_last_name'] ?? '')),
            'class_label' => '',
        ];
    }

    return [
        'title' => $title,
        'mode' => 'class',
        'cls_sec_id' => $clsSecId,
        'days' => $days,
        'slots' => $slots,
        'matrix' => $matrix,
    ];
}

private function buildOneTeacherBlock(int $teacherId, int $campusId, array $days, array $slots): ?array
{
    $teacher = $this->db->table('users')->select('first_name,last_name')->where('id', $teacherId)->get()->getRowArray();
    if ($teacher === null) {
        return null;
    }

    $title = trim((string)($teacher['first_name'] ?? '') . ' ' . (string)($teacher['last_name'] ?? ''));

    $matrix = $this->initializeTimetableMatrix($days, $slots);
    $latestTeacherSql = $this->sqlLatestTeacherAssignments();

    $teacherRowsQb = $this->db->table('time_table tt')
        ->select('tt.time_table_id, tt.day, tt.slot_id, sub.subject_name, c.class_name, s.section_name')
        ->join('class_section cs', 'cs.cls_sec_id = tt.cls_sec_id')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id')
        ->join('allsubject sub', 'sub.sid = tt.subject_id', 'inner')
        ->join(
            'section_subjects ss',
            'ss.cls_sec_id = tt.cls_sec_id AND ss.subject_id = tt.subject_id AND ss.status = 1',
            'inner'
        )
        ->join("($latestTeacherSql) lts", 'lts.cls_sec_id = ss.cls_sec_id AND lts.sec_sub_id = ss.sec_sub_id', 'inner')
        ->where('lts.tid', $teacherId)
        ->where('cs.campus_id', $campusId);
    if ($this->db->fieldExists('include_in_timetable', 'class_section')) {
        $teacherRowsQb->where('cs.include_in_timetable', 1);
    }
    $rows = $teacherRowsQb
        ->orderBy('tt.day', 'ASC')
        ->orderBy('tt.slot_id', 'ASC')
        ->orderBy('tt.time_table_id', 'DESC')
        ->get()
        ->getResultArray();

    foreach ($rows as $r) {
        $d = (string)$r['day'];
        $s = (int)$r['slot_id'];
        if (!isset($matrix[$d][$s])) {
            continue;
        }
        if (!empty($matrix[$d][$s])) {
            continue;
        }
        $matrix[$d][$s][] = [
            'subject_name' => (string)$r['subject_name'],
            'teacher_name' => '',
            'class_label' => trim((string)$r['class_name'] . ' - ' . (string)$r['section_name']),
        ];
    }

    if (!$this->timetableMatrixHasContent($matrix)) {
        return null;
    }

    return [
        'title' => $title,
        'mode' => 'teacher',
        'days' => $days,
        'slots' => $slots,
        'matrix' => $matrix,
    ];
}

private function finalizeReportPayload(array $blocks, string $mode, ?array $timingType, string $workingDaysBanner): array
{
    $timingName = $timingType['type_name'] ?? '';

    $exportTitle = 'Timetable_Report';
    if (count($blocks) === 1) {
        $exportTitle = preg_replace('/[^A-Za-z0-9\-_]+/', '_', $blocks[0]['title'] ?? 'Timetable_Report');
    } elseif ($mode === 'class') {
        $exportTitle = 'Timetable_All_Class_Sections';
    } elseif ($mode === 'teacher') {
        $exportTitle = 'Timetable_All_Teachers';
    }

    return [
        'success' => true,
        'mode' => $mode,
        'blocks' => $blocks,
        'timing_type_name' => $timingName,
        'working_days_display' => $workingDaysBanner,
        'export_title' => $exportTitle,
    ];
}

/**
 * @param int|string $clsSecId   Section id, or ignored when $allClasses is true
 * @param int|string $teacherId  User id, or ignored when $allTeachers is true
 */
private function buildReportPayload(string $mode, $clsSecId, $teacherId, bool $allClasses = false, bool $allTeachers = false): array
{
    $campusId = (int)$this->session->get('member_campusid');
    $timingType = ['type_name' => 'School Timing'];
    $slots = $this->fetchSlotsForCampus($campusId);

    if (empty($slots)) {
        return ['success' => false, 'msg' => 'No slots found for this campus.'];
    }

    if ($mode === 'class') {
        if ($allClasses) {
            $sections = $this->fetchSectionsForCampus($campusId);
            if (empty($sections)) {
                return ['success' => false, 'msg' => 'No class sections found.'];
            }
            $blocks = [];
            foreach ($sections as $sec) {
                $cid = (int)$sec['cls_sec_id'];
                $days = $this->resolveWorkingDayNamesForSection($campusId, $cid);
                $b = $this->buildOneClassBlock($cid, $campusId, $days, $slots);
                if ($b !== null) {
                    $blocks[] = $b;
                }
            }
            if (empty($blocks)) {
                return ['success' => false, 'msg' => 'Could not load class sections for this campus.'];
            }

            $banner = 'Each grid lists only that section\'s working days (check-in ≠ check-out from school timings).';

            return $this->finalizeReportPayload($blocks, 'class', $timingType, $banner);
        }

        $id = (int)$clsSecId;
        if ($id <= 0) {
            return ['success' => false, 'msg' => 'Please select a class section.'];
        }

        $days = $this->resolveWorkingDayNamesForSection($campusId, $id);
        $block = $this->buildOneClassBlock($id, $campusId, $days, $slots);
        if ($block === null) {
            return ['success' => false, 'msg' => 'Class section not found or not included on Timetable Constraints.'];
        }

        return $this->finalizeReportPayload([$block], 'class', $timingType, implode(', ', $days));
    }

    if ($mode === 'teacher') {
        if ($allTeachers) {
            $teachers = $this->fetchTeachersWithTimetableForCampus($campusId);
            if (empty($teachers)) {
                return ['success' => false, 'msg' => 'No teachers with timetable assignments found.'];
            }
            $blocks = [];
            foreach ($teachers as $t) {
                $tid = (int)$t['id'];
                $days = $this->resolveWorkingDayNamesForTeacher($campusId, $tid);
                $b = $this->buildOneTeacherBlock($tid, $campusId, $days, $slots);
                if ($b !== null) {
                    $blocks[] = $b;
                }
            }
            if (empty($blocks)) {
                return ['success' => false, 'msg' => 'No teachers with timetable assignments found.'];
            }

            $banner = 'Each grid uses that teacher\'s working days (union of sections they teach; check-in ≠ check-out).';

            return $this->finalizeReportPayload($blocks, 'teacher', $timingType, $banner);
        }

        $tid = (int)$teacherId;
        if ($tid <= 0) {
            return ['success' => false, 'msg' => 'Please select a teacher.'];
        }

        $days = $this->resolveWorkingDayNamesForTeacher($campusId, $tid);
        $block = $this->buildOneTeacherBlock($tid, $campusId, $days, $slots);
        if ($block === null) {
            return ['success' => false, 'msg' => 'This teacher has no timetable assignments to display.'];
        }

        return $this->finalizeReportPayload([$block], 'teacher', $timingType, implode(', ', $days));
    }

    return ['success' => false, 'msg' => 'Invalid report mode.'];
}

private function buildReportHeader(): array
{
    $campusId = (int)$this->session->get('member_campusid');
    $campus = $this->db->table('campus')
        ->select('campus_name')
        ->where('campus_id', $campusId)
        ->get()
        ->getRowArray();

    $schoolName = 'School Management System';
    if (function_exists('getSchoolInfo')) {
        $si = getSchoolInfo();
        if (!empty($si->school_name)) {
            $schoolName = (string)$si->school_name;
        } elseif (!empty($si->name)) {
            $schoolName = (string)$si->name;
        }
    }

    return [
        'school_name' => $schoolName,
        'campus_name' => (string)($campus['campus_name'] ?? ''),
        'generated_at' => date('d M Y h:i A'),
    ];
}

// Save entire timetable
public function save()
{
    $clsSecId = $this->request->getPost('cls_sec_id');
    $timetable = json_decode($this->request->getPost('timetable'), true);
    $allowSameSubjectDay = ((int)$this->request->getPost('allow_same_subject_day') === 1);
    $validDays = $this->canonicalWeekdayOrder();

    try {
        if (empty($clsSecId) || !is_array($timetable)) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Invalid timetable payload.'
            ]);
        }

        // Pre-validate duplicate subject per day (optional)
        if (!$allowSameSubjectDay) {
            foreach ($timetable as $day => $slots) {
                if (!in_array((string)$day, $validDays, true) || !is_array($slots)) {
                    continue;
                }
                $seen = [];
                foreach ($slots as $slotId => $data) {
                    $sid = (int)($data['subject_id'] ?? 0);
                    if ($sid <= 0) {
                        continue;
                    }
                    if (isset($seen[$sid])) {
                        return $this->response->setJSON([
                            'success' => false,
                            'msg' => "Subject duplication not allowed: same subject appears multiple times on {$day}."
                        ]);
                    }
                    $seen[$sid] = true;
                }
            }
        }

        // Start transaction
        $this->db->transStart();

        // Clear existing timetable
        $this->db->table('time_table')
            ->where('cls_sec_id', $clsSecId)
            ->delete();

        // Insert new timetable entries
        foreach ($timetable as $day => $slots) {
            foreach ($slots as $slotId => $data) {
                $this->db->table('time_table')->insert([
                    'cls_sec_id' => $clsSecId,
                    'day' => $day,
                    'slot_id' => $slotId,
                    'subject_id' => $data['subject_id'],
                    'user_id' => session()->get('user_id')
                ]);
            }
        }

        // Complete transaction
        $this->db->transComplete();

        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Timetable saved successfully'
        ]);

    } catch (\Exception $e) {
        $this->db->transRollback();
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error: ' . $e->getMessage()
        ]);
    }
}

// Clear entire timetable
public function clear()
{
    $clsSecId = $this->request->getPost('cls_sec_id');

    try {
        $this->db->table('time_table')
            ->where('cls_sec_id', $clsSecId)
            ->delete();

        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Timetable cleared successfully'
        ]);

    } catch (\Exception $e) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error: ' . $e->getMessage()
        ]);
    }
}

// Helper function to get subject name
private function getSubjectName($subjectId)
{
    $subject = $this->db->table('allsubject')
        ->select('subject_name')
        ->where('sid', $subjectId)
        ->get()
        ->getRow();

    return $subject ? $subject->subject_name : '';
}
}