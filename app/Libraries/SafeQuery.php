<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Safe query helpers — prefer query builder; use these when raw SQL is unavoidable.
 */
class SafeQuery
{
    /**
     * @param list<int|string> $ids
     * @return list<int>
     */
    public static function intIds(array $ids): array
    {
        return array_values(array_filter(array_map('intval', $ids), static fn (int $v): bool => $v > 0));
    }

    /**
     * Comma-separated int list for IN (...) — only non-negative integers.
     */
    public static function intInList(array $ids): string
    {
        $clean = self::intIds($ids);

        return $clean === [] ? '0' : implode(',', $clean);
    }

    public static function systemForCampus(BaseConnection $db, int $campusId): ?object
    {
        if ($campusId <= 0) {
            return null;
        }

        $campusRow = $db->table('campus')->select('system_id')->where('campus_id', $campusId)->get()->getRow();
        $systemId  = (int) ($campusRow->system_id ?? 0);

        if ($systemId <= 0) {
            return null;
        }

        return $db->table('system')->where('system_id', $systemId)->get()->getRow();
    }

    /**
     * Whitelist table/column names then delete rows matching IDs.
     *
     * @param list<int|string> $ids
     */
    public static function deleteWhereIn(BaseConnection $db, string $table, string $column, array $ids): void
    {
        if (! preg_match('/^[a-z][a-z0-9_]*$/', $table) || ! preg_match('/^[a-z][a-z0-9_]*$/', $column)) {
            return;
        }

        $ids = self::intIds($ids);
        if ($ids === []) {
            return;
        }

        $db->table($table)->whereIn($column, $ids)->delete();
    }

    /**
     * Delete staff-linked rows (users table uses several FK column names).
     *
     * @param list<int|string> $staffIds
     */
    public static function deleteStaffFromTable(BaseConnection $db, string $table, array $staffIds): void
    {
        if (! preg_match('/^[a-z][a-z0-9_]*$/', $table)) {
            return;
        }

        $staffIds = self::intIds($staffIds);
        if ($staffIds === []) {
            return;
        }

        $db->table($table)
            ->groupStart()
            ->whereIn('user_id', $staffIds)
            ->orWhereIn('emp_id', $staffIds)
            ->orWhereIn('teacher_id', $staffIds)
            ->orWhereIn('tid', $staffIds)
            ->groupEnd()
            ->delete();
    }

    /**
     * @param list<int|string> $staffIds
     */
    public static function deleteMessagesForUsers(BaseConnection $db, array $staffIds): void
    {
        $staffIds = self::intIds($staffIds);
        if ($staffIds === []) {
            return;
        }

        $db->table('messages')
            ->groupStart()
            ->whereIn('sender_id', $staffIds)
            ->orWhereIn('receiver_id', $staffIds)
            ->groupEnd()
            ->delete();

        $db->table('chat_box')
            ->groupStart()
            ->whereIn('sender_id', $staffIds)
            ->orWhereIn('receiver_id', $staffIds)
            ->groupEnd()
            ->delete();
    }
}
