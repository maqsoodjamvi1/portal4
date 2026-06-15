<?php

/**
 * Reusable safe query patterns for autocomplete and lookups.
 */

use App\Libraries\SafeQuery;
use Config\Database;

if (! function_exists('searchParentsByName')) {
    /**
     * @return list<object>
     */
    function searchParentsByName(string $term, int $campusId): array
    {
        if ($campusId <= 0) {
            return [];
        }

        return Database::connect()->table('parents')
            ->like('f_name', trim($term))
            ->where('campus_id', $campusId)
            ->get()
            ->getResult();
    }
}

if (! function_exists('searchStudentsByName')) {
    /**
     * @return list<object>
     */
    function searchStudentsByName(string $term, int $status, int $campusId): array
    {
        if ($campusId <= 0) {
            return [];
        }

        return Database::connect()->table('students')
            ->groupStart()
                ->like('first_name', trim($term))
                ->orLike('last_name', trim($term))
            ->groupEnd()
            ->where('status', $status)
            ->where('campus_id', $campusId)
            ->get()
            ->getResult();
    }
}

if (! function_exists('countActiveStudentsOnCampus')) {
    function countActiveStudentsOnCampus(int $campusId): int
    {
        if ($campusId <= 0) {
            return 0;
        }

        $row = Database::connect()->table('students')
            ->selectCount('students.student_id', 'studentTotal')
            ->join('student_class', 'student_class.student_id = students.student_id AND student_class.status = 1', 'inner')
            ->where('students.campus_id', $campusId)
            ->get()
            ->getRow();

        return (int) ($row->studentTotal ?? 0);
    }
}

if (! function_exists('campusBillForCampus')) {
    function campusBillForCampus(int $campusId): ?object
    {
        if ($campusId <= 0) {
            return null;
        }

        return Database::connect()->table('campus_bills')
            ->where(['status' => 1, 'campus_id' => $campusId])
            ->get()
            ->getRow();
    }
}
