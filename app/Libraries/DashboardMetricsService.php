<?php

namespace App\Libraries;

/**
 * Permission-gated dashboard metrics and action-center items.
 */
class DashboardMetricsService
{
    public const LAYOUT_TEACHER   = 'teacher';
    public const LAYOUT_FINANCE  = 'finance';
    public const LAYOUT_PRINCIPAL = 'principal';
    public const LAYOUT_DEFAULT  = 'default';

    public static function can(string $perm): bool
    {
        return function_exists('hasPermission') && hasPermission($perm);
    }

    public static function canAny(array $perms): bool
    {
        foreach ($perms as $p) {
            if (self::can($p)) {
                return true;
            }
        }

        return false;
    }

    public static function shouldLoadOverviewKpis(): bool
    {
        return self::canAny([
            'admin-db-students',
            'admin-db-teacher',
            'admin-db-attendance',
            'admin-db-fee-collection',
            'admin-db-session',
            'admin-db-term',
            'admin-db-week',
            'admin-db-exam',
        ]);
    }

    public static function shouldLoadFinanceCharts(): bool
    {
        return ! self::isTeacherLayoutCandidate()
            && self::can('admin-db-fee-collection');
    }

    public static function shouldLoadStudentAttendanceBlock(): bool
    {
        return self::can('admin-db-attendance');
    }

    public static function shouldLoadHealthBlock(): bool
    {
        return self::canAny(['admin-health-bmi', 'admin-health-bmi-dashboard', 'admin-health-alerts']);
    }

    public static function shouldLoadOperationsBlock(): bool
    {
        return self::canAny(['admin-datesheet', 'admin-classdairy', 'admin-exams', 'admin-quizzes']);
    }

    public static function isTeacherLayoutCandidate(): bool
    {
        return (bool) session()->get('dashboard_is_teacher');
    }

    /**
     * @param bool $isTeacher From campus role_name_id 5
     */
    public static function resolveLayoutRole(bool $isTeacher): string
    {
        session()->set('dashboard_is_teacher', $isTeacher);

        if ($isTeacher) {
            return self::LAYOUT_TEACHER;
        }

        if (self::can('admin-db-fee-collection') && ! self::can('admin-db-attendance')) {
            return self::LAYOUT_FINANCE;
        }

        if (self::canAny(['admin-db-attendance', 'admin-db-students', 'admin-db-teacher'])) {
            return self::LAYOUT_PRINCIPAL;
        }

        return self::LAYOUT_DEFAULT;
    }

    /**
     * @return list<array{label: string, detail: string, url: string, icon: string, badge: int, priority: int}>
     */
    public static function buildActionCenter(
        int $campusId,
        int $systemId,
        int $userId,
        ?array $setupProgress = null,
        int $pendingAttendanceCount = 0
    ): array {
        helper(['url', 'permission']);
        $items = [];

        if ($setupProgress !== null && empty($setupProgress['is_complete'])
            && ! SchoolSetupProgress::isTeacher($userId, $campusId)) {
            $items[] = [
                'label'    => lang('SchoolSetup.continue_setup'),
                'detail'   => lang('SchoolSetup.banner_of_complete', [
                    (int) ($setupProgress['completed_count'] ?? 0),
                    (int) ($setupProgress['total'] ?? 3),
                ]),
                'url'      => (string) ($setupProgress['next_step_url'] ?? base_url('admin/getting-started')),
                'icon'     => 'fas fa-tasks',
                'badge'    => 0,
                'priority' => 1,
            ];
        }

        $metrics = function_exists('getAdminHeaderMetrics')
            ? getAdminHeaderMetrics($campusId)
            : [];

        if ($pendingAttendanceCount > 0 && self::can('admin-db-attendance')) {
            $items[] = [
                'label'    => 'Pending attendance',
                'detail'   => $pendingAttendanceCount . ' section(s) need marking today',
                'url'      => base_url('admin/students_absentees/add'),
                'icon'     => 'fas fa-clipboard-check',
                'badge'    => $pendingAttendanceCount,
                'priority' => 2,
            ];
        }

        $feeTotal  = (int) ($metrics['monthly_fee_total_students'] ?? 0);
        $feePaid   = (int) ($metrics['monthly_fee_paid_students'] ?? 0);
        $feeUnpaid = (int) ($metrics['unpaid_fee_chalans'] ?? 0);
        $feeMonth  = (string) ($metrics['monthly_fee_month'] ?? date('Y-m'));
        $feeLabel  = $feeMonth !== ''
            ? date('M Y', strtotime($feeMonth . '-01'))
            : date('M Y');

        if ($feeTotal > 0 && self::canAny(['admin-fee-chalan', 'admin-db-fee-collection'])) {
            $items[] = [
                'label'    => $feeLabel . ' monthly fee',
                'detail'   => $feeTotal . ' students · ' . $feePaid . ' paid · ' . $feeUnpaid . ' unpaid',
                'url'      => base_url('admin/fee-chalan-pay'),
                'icon'     => 'fas fa-file-invoice-dollar',
                'badge'    => $feeUnpaid,
                'priority' => 3,
            ];
        }

        $empLeaves = (int) ($metrics['pending_emp_leaves'] ?? 0);
        if ($empLeaves > 0 && self::can('admin-users')) {
            $items[] = [
                'label'    => 'Employee leave requests',
                'detail'   => $empLeaves . ' pending approval(s)',
                'url'      => base_url('admin/users?status=1'),
                'icon'     => 'fas fa-plane',
                'badge'    => $empLeaves,
                'priority' => 4,
            ];
        }

        $stdLeaves = (int) ($metrics['pending_std_leaves'] ?? 0);
        if ($stdLeaves > 0 && self::can('admin-students')) {
            $items[] = [
                'label'    => 'Student leave requests',
                'detail'   => $stdLeaves . ' pending approval(s)',
                'url'      => base_url('admin/students?status=1'),
                'icon'     => 'fas fa-user-clock',
                'badge'    => $stdLeaves,
                'priority' => 5,
            ];
        }

        $unread = (int) ($metrics['unread_messages'] ?? 0);
        if ($unread > 0) {
            $items[] = [
                'label'    => 'Unread messages',
                'detail'   => $unread . ' message(s)',
                'url'      => base_url('admin/messages'),
                'icon'     => 'fas fa-envelope',
                'badge'    => $unread,
                'priority' => 6,
            ];
        }

        usort($items, static fn ($a, $b) => ($a['priority'] ?? 99) <=> ($b['priority'] ?? 99));

        return array_slice($items, 0, 5);
    }

    /**
     * Teacher-scoped action center (attendance, diary, results, quizzes, messages).
     *
     * @param array{
     *   pendingAttendanceCount?: int,
     *   pendingAttendanceUrl?: string,
     *   diaryMissingCount?: int,
     *   diaryMissingUrl?: string,
     *   resultsPendingCount?: int,
     *   quizOpenCount?: int,
     *   campusId?: int,
     *   userId?: int
     * } $ctx
     *
     * @return list<array{label: string, detail: string, url: string, icon: string, badge: int, priority: int}>
     */
    public static function buildTeacherActionCenter(array $ctx): array
    {
        helper(['url', 'permission']);
        $items = [];

        $pendingAtt = (int) ($ctx['pendingAttendanceCount'] ?? 0);
        if ($pendingAtt > 0 && self::canAny(['admin-add-student-absentees', 'admin-db-attendance'])) {
            $items[] = [
                'label'    => 'Mark student attendance',
                'detail'   => $pendingAtt . ' of your class(es) need marking today',
                'url'      => (string) ($ctx['pendingAttendanceUrl'] ?? base_url('admin/students_absentees/add')),
                'icon'     => 'fas fa-clipboard-check',
                'badge'    => $pendingAtt,
                'priority' => 1,
            ];
        }

        $diaryMissing = (int) ($ctx['diaryMissingCount'] ?? 0);
        if ($diaryMissing > 0 && self::canAny(['admin-classdairy', 'admin-add-classdairy'])) {
            $items[] = [
                'label'    => "Today's diary",
                'detail'   => $diaryMissing . ' section(s) missing diary entries',
                'url'      => (string) ($ctx['diaryMissingUrl'] ?? base_url('admin/classdiary/add')),
                'icon'     => 'fas fa-book-open',
                'badge'    => $diaryMissing,
                'priority' => 2,
            ];
        }

        $resultsPending = (int) ($ctx['resultsPendingCount'] ?? 0);
        if ($resultsPending > 0 && self::canAny(['admin-students-subject-results', 'admin-add-students-subject-results'])) {
            $items[] = [
                'label'    => 'Enter results',
                'detail'   => $resultsPending . ' subject result(s) still incomplete',
                'url'      => base_url('admin/students-subject-results/add'),
                'icon'     => 'fas fa-poll',
                'badge'    => $resultsPending,
                'priority' => 3,
            ];
        }

        $quizOpen = (int) ($ctx['quizOpenCount'] ?? 0);
        if ($quizOpen > 0 && self::canAny(['admin-quiz', 'admin-add-quiz', 'admin-quizzes'])) {
            $items[] = [
                'label'    => 'Open quizzes',
                'detail'   => $quizOpen . ' quiz(zes) currently active',
                'url'      => base_url('admin/quizzes'),
                'icon'     => 'fas fa-clipboard-list',
                'badge'    => $quizOpen,
                'priority' => 4,
            ];
        }

        $campusId = (int) ($ctx['campusId'] ?? 0);
        $metrics  = $campusId > 0 && function_exists('getAdminHeaderMetrics')
            ? getAdminHeaderMetrics($campusId)
            : [];
        $unread   = (int) ($metrics['unread_messages'] ?? 0);
        if ($unread > 0) {
            $items[] = [
                'label'    => 'Unread messages',
                'detail'   => $unread . ' message(s)',
                'url'      => base_url('admin/messages'),
                'icon'     => 'fas fa-envelope',
                'badge'    => $unread,
                'priority' => 5,
            ];
        }

        usort($items, static fn ($a, $b) => ($a['priority'] ?? 99) <=> ($b['priority'] ?? 99));

        return array_slice($items, 0, 5);
    }
}
