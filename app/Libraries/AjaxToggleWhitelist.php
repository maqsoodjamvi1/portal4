<?php

namespace App\Libraries;

/**
 * Allowed tables/fields for generic admin/ajax boolean toggle endpoints.
 */
class AjaxToggleWhitelist
{
    /** @var array<string, list<string>> */
    private static array $tables = [
        'users'              => ['status', 'is_active', 'active'],
        'students'           => ['status', 'is_active'],
        'classes'            => ['status'],
        'sections'           => ['status'],
        'class_section'      => ['status'],
        'allsubject'         => ['status'],
        'section_subjects'   => ['status'],
        'teacher_subjects'   => ['status'],
        'teacher_section'    => ['status'],
        'fee_type'           => ['status', 's_flag', 'is_monthly_fee'],
        'fee_amount'         => ['status'],
        'exam'               => ['status'],
        'quizzes'            => ['status', 'is_published'],
        'qb_topics'          => ['status'],
        'notices'            => ['status'],
        'notice'             => ['status'],
        'attachment_types'   => ['status'],
        'academic_session'   => ['status'],
        'terms'              => ['status'],
        'terms_session'      => ['status'],
        'term_weeks'         => ['status'],
        'campus'             => ['status'],
        'messages'           => ['is_read', 'status'],
        'employee_leaves'    => ['status', 'approved'],
        'students_leaves'    => ['status', 'approved'],
        'content'            => ['status', 'is_indexable'],
        'test_series'        => ['status'],
        'lesson_plan'        => ['status'],
        'worksheet'          => ['status'],
        'worksheet_info'     => ['status'],
        'video_lecture'      => ['status'],
        'grading_policy'     => ['status'],
        'bill_plan_months'   => ['status'],
        'bill_amount'        => ['status'],
        'chalan_type'        => ['status'],
        'student_notices'    => ['status'],
        'pay_system_bill'    => ['status'],
    ];

    public static function isAllowed(string $table, string $field): bool
    {
        $table = strtolower(trim($table));
        $field = strtolower(trim($field));

        if ($table === '' || $field === '') {
            return false;
        }

        if (! preg_match('/^[a-z0-9_]+$/', $table) || ! preg_match('/^[a-z0-9_]+$/', $field)) {
            return false;
        }

        return isset(self::$tables[$table]) && in_array($field, self::$tables[$table], true);
    }

    public static function assertAllowed(string $table, string $field): void
    {
        if (! self::isAllowed($table, $field)) {
            throw new \RuntimeException('Toggle not allowed for this table/field.');
        }
    }
}
