<?php

namespace App\Libraries;

use Config\Database;

/**
 * Data-driven onboarding progress for new school setup (calendar -> academic -> fees).
 */
class SchoolSetupProgress
{
    public const STEP_CALENDAR = 'calendar';
    public const STEP_ACADEMIC = 'academic';
    public const STEP_FEE      = 'fee';

    /** @var array<string,bool>|null */
    private static ?array $runChecksCache = null;

    private static string $runChecksCacheKey = '';

    /**
     * Invalidate cached setup/teacher flags (e.g. after completing a setup step).
     */
    public static function clearCache(int $systemId, int $campusId, ?int $userId = null): void
    {
        $cache = \Config\Services::cache();
        $cache->delete('school_setup_complete_' . $systemId . '_' . $campusId);

        if ($userId !== null && $userId > 0) {
            $cache->delete('school_setup_teacher_' . $userId . '_' . $campusId);
        }

        self::$runChecksCache    = null;
        self::$runChecksCacheKey = '';
    }

    /**
     * @return array{
     *   steps: list<array<string,mixed>>,
     *   completed_count: int,
     *   total: int,
     *   percent: int,
     *   is_complete: bool,
     *   next_step_id: ?string,
     *   next_step_url: ?string
     * }
     */
    public static function getStatus(int $systemId, int $campusId): array
    {
        helper('url');
        $checks = static::runChecks($systemId, $campusId);

        $calendarUrl = base_url('admin/academic-calendar/builder');
        $academicUrl = base_url('admin/academic-setup');

        $steps = [
            [
                'id'          => self::STEP_CALENDAR,
                'number'      => 1,
                'title'       => lang('SchoolSetup.step_calendar_title'),
                'description' => lang('SchoolSetup.step_calendar_description'),
                'icon'        => 'fa-calendar-alt',
                'url'         => $calendarUrl,
                'complete'    => $checks['calendar'],
                'substeps'    => [
                    [
                        'id'       => 'calendar_session',
                        'title'    => lang('SchoolSetup.substep_calendar_session'),
                        'complete' => $checks['calendar_session'],
                        'url'      => $calendarUrl,
                    ],
                    [
                        'id'       => 'calendar_terms',
                        'title'    => lang('SchoolSetup.substep_calendar_terms'),
                        'complete' => $checks['calendar_terms'],
                        'url'      => $calendarUrl,
                    ],
                    [
                        'id'       => 'calendar_weeks',
                        'title'    => lang('SchoolSetup.substep_calendar_weeks'),
                        'complete' => $checks['calendar_weeks'],
                        'url'      => $calendarUrl,
                    ],
                ],
            ],
            [
                'id'          => self::STEP_ACADEMIC,
                'number'      => 2,
                'title'       => lang('SchoolSetup.step_academic_title'),
                'description' => lang('SchoolSetup.step_academic_description'),
                'icon'        => 'fa-layer-group',
                'url'         => $academicUrl,
                'complete'    => $checks['academic'],
                'substeps'    => [
                    [
                        'id'       => 'academic_classes',
                        'title'    => lang('SchoolSetup.substep_academic_classes'),
                        'complete' => $checks['academic_classes'],
                        'url'      => $academicUrl,
                    ],
                    [
                        'id'       => 'academic_sections',
                        'title'    => lang('SchoolSetup.substep_academic_sections'),
                        'complete' => $checks['academic_sections'],
                        'url'      => $academicUrl,
                    ],
                    [
                        'id'       => 'academic_subjects',
                        'title'    => lang('SchoolSetup.substep_academic_subjects'),
                        'complete' => $checks['academic_subjects'],
                        'url'      => $academicUrl,
                    ],
                    [
                        'id'       => 'academic_assignments',
                        'title'    => lang('SchoolSetup.substep_academic_assignments'),
                        'complete' => $checks['academic_assignments'],
                        'url'      => $academicUrl,
                    ],
                ],
            ],
            [
                'id'          => self::STEP_FEE,
                'number'      => 3,
                'title'       => lang('SchoolSetup.step_fee_title'),
                'description' => lang('SchoolSetup.step_fee_description'),
                'icon'        => 'fa-sliders-h',
                'url'         => $checks['fee_url'] ?? base_url('admin/fee_setup?tab=types'),
                'complete'    => $checks['fee'],
                'substeps'    => [
                    [
                        'id'       => 'fee_types',
                        'title'    => lang('SchoolSetup.substep_fee_types'),
                        'complete' => $checks['fee_types'],
                        'url'      => base_url('admin/fee_setup?tab=types'),
                    ],
                    [
                        'id'       => 'fee_amounts',
                        'title'    => lang('SchoolSetup.substep_fee_amounts'),
                        'complete' => $checks['fee_amounts'],
                        'url'      => base_url('admin/fee_setup?tab=amounts'),
                    ],
                ],
            ],
        ];

        $steps = static::applyStepLocks($steps);

        $completedCount = count(array_filter($steps, static fn ($s) => ! empty($s['complete'])));
        $total          = count($steps);
        $nextStep       = null;

        foreach ($steps as $step) {
            if (empty($step['complete'])) {
                $nextStep = $step;
                break;
            }
        }

        return [
            'steps'           => $steps,
            'completed_count' => $completedCount,
            'total'           => $total,
            'percent'         => $total > 0 ? (int) round(($completedCount / $total) * 100) : 0,
            'is_complete'     => $completedCount >= $total,
            'next_step_id'    => $nextStep['id'] ?? null,
            'next_step_url'   => $nextStep['url'] ?? null,
            'next_step_title' => $nextStep['title'] ?? null,
        ];
    }

    /**
     * Raw completion checks for filters and progress (no lang/view metadata).
     *
     * @return array<string,bool|string>
     */
    public static function getChecks(int $systemId, int $campusId): array
    {
        return static::runChecks($systemId, $campusId);
    }

    /**
     * @param list<array<string,mixed>> $steps
     * @return list<array<string,mixed>>
     */
    protected static function applyStepLocks(array $steps): array
    {
        $priorComplete = true;

        foreach ($steps as $idx => $step) {
            $locked = ! $priorComplete;
            $steps[$idx]['locked']      = $locked;
            $steps[$idx]['accessible']  = ! $locked;

            $substeps = $step['substeps'] ?? [];
            $subDone  = 0;
            foreach ($substeps as $sIdx => $sub) {
                $substeps[$sIdx]['locked'] = $locked;
                if (! empty($sub['complete'])) {
                    $subDone++;
                }
            }
            $steps[$idx]['substeps']         = $substeps;
            $steps[$idx]['substeps_done']    = $subDone;
            $steps[$idx]['substeps_total']   = count($substeps);

            if (empty($step['complete'])) {
                $priorComplete = false;
            }
        }

        return $steps;
    }

    public static function isComplete(int $systemId, int $campusId): bool
    {
        $cache = \Config\Services::cache();
        $key   = 'school_setup_complete_' . $systemId . '_' . $campusId;
        $hit   = $cache->get($key);

        if ($hit !== null) {
            return (bool) $hit;
        }

        $complete = static::getStatus($systemId, $campusId)['is_complete'];
        $cache->save($key, $complete ? 1 : 0, $complete ? 900 : 120);

        return $complete;
    }

    public static function nextStepUrl(int $systemId, int $campusId): ?string
    {
        return static::getStatus($systemId, $campusId)['next_step_url'];
    }

    public static function completionPercent(int $systemId, int $campusId): int
    {
        return static::getStatus($systemId, $campusId)['percent'];
    }

    /**
     * Teachers skip school-wide setup guidance.
     */
    public static function isTeacher(int $userId, int $campusId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $cache = \Config\Services::cache();
        $key   = 'school_setup_teacher_' . $userId . '_' . $campusId;
        $hit   = $cache->get($key);

        if ($hit !== null) {
            return (bool) $hit;
        }

        $db = Database::connect();
        $planId = 0;
        if ($campusId > 0) {
            $row = $db->table('campus_bills')
                ->select('plan_id')
                ->where('status', 1)
                ->where('campus_id', $campusId)
                ->orderBy('campus_expiry', 'DESC')
                ->get()
                ->getRow();
            $planId = (int) ($row->plan_id ?? 0);
        }

        $primary = $db->table('user_roles ur')
            ->join('roles r', 'r.id = ur.roleID' . ($planId > 0 ? ' AND r.plan_id = ' . $planId : ''), 'inner')
            ->where('ur.userID', $userId)
            ->where('r.role_name_id', 5)
            ->countAllResults();

        if ($primary > 0) {
            $cache->save($key, 1, 300);

            return true;
        }

        $isTeacher = $db->table('user_roles ur')
            ->join('roles r', 'r.role_name_id = ur.roleID' . ($planId > 0 ? ' AND r.plan_id = ' . $planId : ''), 'inner')
            ->where('ur.userID', $userId)
            ->where('r.role_name_id', 5)
            ->countAllResults() > 0;

        $cache->save($key, $isTeacher ? 1 : 0, 300);

        return $isTeacher;
    }

    /**
     * Resolve system/campus from session + getSchoolInfo().
     *
     * @return array{system_id:int,campus_id:int,status:array}|null
     */
    public static function forCurrentUser(): ?array
    {
        if (! session()->get('IsAuthorized')) {
            return null;
        }

        helper('server');
        $schoolinfo = function_exists('getSchoolInfo') ? getSchoolInfo() : null;
        if (! $schoolinfo || empty($schoolinfo->system_id)) {
            return null;
        }

        $systemId = (int) $schoolinfo->system_id;
        $campusId = (int) (session()->get('member_campusid') ?? 0);
        if ($campusId <= 0 && ! empty($schoolinfo->campus_id)) {
            $campusId = (int) $schoolinfo->campus_id;
        }

        return [
            'system_id' => $systemId,
            'campus_id' => $campusId,
            'status'    => static::getStatus($systemId, $campusId),
        ];
    }

    /**
     * @return array<string,bool>
     */
    protected static function runChecks(int $systemId, int $campusId): array
    {
        if ($systemId <= 0) {
            return static::emptyChecks();
        }

        $cacheKey = $systemId . ':' . $campusId;
        if (self::$runChecksCache !== null && self::$runChecksCacheKey === $cacheKey) {
            return self::$runChecksCache;
        }

        $db = Database::connect();

        $sessionCount = $db->table('academic_session')
            ->where('system_id', $systemId)
            ->countAllResults();

        $termsCount = 0;
        $weeksCount = 0;
        if ($sessionCount > 0) {
            $sessionIds = $db->table('academic_session')
                ->select('session_id')
                ->where('system_id', $systemId)
                ->get()
                ->getResultArray();
            $ids = array_column($sessionIds, 'session_id');
            if ($ids !== []) {
                $termsCount = $db->table('terms_session')
                    ->where('system_id', $systemId)
                    ->whereIn('session_id', $ids)
                    ->countAllResults();

                $termSessionIds = $db->table('terms_session')
                    ->select('term_session_id')
                    ->where('system_id', $systemId)
                    ->whereIn('session_id', $ids)
                    ->get()
                    ->getResultArray();
                if ($termSessionIds !== []) {
                    $weeksCount = $db->table('term_weeks')
                        ->where('system_id', $systemId)
                        ->whereIn('term_session_id', array_column($termSessionIds, 'term_session_id'))
                        ->countAllResults();
                }
            }
        }

        $classesCount = $db->table('classes')
            ->where('system_id', $systemId)
            ->where('status', 1)
            ->countAllResults();

        $sectionsCount = $db->table('sections')
            ->where('system_id', $systemId)
            ->where('status', 1)
            ->countAllResults();

        $subjectsCount = $db->table('allsubject')
            ->where('system_id', $systemId)
            ->where('status', 1)
            ->countAllResults();

        $classSectionCount = 0;
        $sectionSubjectsCount = 0;
        if ($campusId > 0) {
            $classSectionCount = $db->table('class_section')
                ->where('campus_id', $campusId)
                ->where('status', 1)
                ->countAllResults();

            if ($subjectsCount > 0) {
                $subjectIds = $db->table('allsubject')
                    ->select('sid')
                    ->where('system_id', $systemId)
                    ->where('status', 1)
                    ->get()
                    ->getResultArray();
                if ($subjectIds !== []) {
                    $sectionSubjectsCount = $db->table('section_subjects')
                        ->whereIn('subject_id', array_column($subjectIds, 'sid'))
                        ->countAllResults();
                }
            }
        }

        $feeTypesCount = $db->table('fee_type')
            ->where('system_id', $systemId)
            ->where('status', 1)
            ->countAllResults();

        $feeAmountsCount = 0;
        if ($campusId > 0) {
            $feeAmountsCount = $db->table('fee_amount')
                ->where('campus_id', $campusId)
                ->countAllResults();
        }

        $calendarSession = $sessionCount > 0;
        $calendarTerms   = $termsCount > 0;
        $calendarWeeks   = $weeksCount > 0;
        $calendar        = $calendarSession && $calendarTerms;

        $academicClasses         = $classesCount > 0;
        $academicSections        = $sectionsCount > 0;
        $academicSubjects        = $subjectsCount > 0;
        $academicClassSections   = $classSectionCount > 0;
        $academicSectionSubjects = $sectionSubjectsCount > 0;
        $academicAssignments     = $academicClassSections && $academicSectionSubjects;
        $academic                = $academicClasses
            && $academicSections
            && $academicSubjects
            && $academicAssignments;
        $feeTypes   = $feeTypesCount > 0;
        $feeAmounts = $feeAmountsCount > 0;
        $fee        = $feeTypes && $feeAmounts;

        helper('url');
        $feeUrl = base_url('admin/fee_setup?tab=types');
        if (! $feeTypes) {
            $feeUrl = base_url('admin/fee_setup?tab=types');
        } elseif (! $feeAmounts) {
            $feeUrl = base_url('admin/fee_setup?tab=amounts');
        }

        $result = [
            'calendar'                  => $calendar,
            'calendar_session'          => $calendarSession,
            'calendar_terms'            => $calendarTerms,
            'calendar_weeks'            => $calendarWeeks,
            'academic'                  => $academic,
            'academic_classes'          => $academicClasses,
            'academic_sections'         => $academicSections,
            'academic_subjects'         => $academicSubjects,
            'academic_class_sections'   => $academicClassSections,
            'academic_section_subjects' => $academicSectionSubjects,
            'academic_assignments'      => $academicAssignments,
            'fee'                       => $fee,
            'fee_types'                 => $feeTypes,
            'fee_amounts'               => $feeAmounts,
            'fee_url'                   => $feeUrl,
        ];

        self::$runChecksCacheKey = $cacheKey;
        self::$runChecksCache    = $result;

        return $result;
    }

    /**
     * @return array<string,bool>
     */
    protected static function emptyChecks(): array
    {
        helper('url');

        return [
            'calendar'                  => false,
            'calendar_session'          => false,
            'calendar_terms'            => false,
            'calendar_weeks'            => false,
            'academic'                  => false,
            'academic_classes'          => false,
            'academic_sections'         => false,
            'academic_subjects'         => false,
            'academic_class_sections'   => false,
            'academic_section_subjects' => false,
            'academic_assignments'      => false,
            'fee'                       => false,
            'fee_types'                 => false,
            'fee_amounts'               => false,
            'fee_url'                   => base_url('admin/fee_setup?tab=types'),
        ];
    }
}
