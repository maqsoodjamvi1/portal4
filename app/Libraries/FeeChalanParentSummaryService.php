<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Parameterized fee summary queries for fee chalan payment screens.
 */
class FeeChalanParentSummaryService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function findParentIdByStudentId(int $studentId): ?int
    {
        if ($studentId <= 0) {
            return null;
        }

        $row = $this->db->table('students')
            ->select('parent_id')
            ->where('student_id', $studentId)
            ->get()
            ->getRow();

        return $row ? (int) $row->parent_id : null;
    }

    public function findParentIdByRegNo(string $regNo): ?int
    {
        $regNo = trim($regNo);
        if ($regNo === '') {
            return null;
        }

        $row = $this->db->table('students')
            ->select('parent_id')
            ->where('reg_no', $regNo)
            ->get()
            ->getRow();

        return $row ? (int) $row->parent_id : null;
    }

    /**
     * @return list<object>
     */
    public function activeStudentsForParent(int $campusId, int $parentId): array
    {
        if ($campusId <= 0 || $parentId <= 0) {
            return [];
        }

        return $this->db->table('students')
            ->where('campus_id', $campusId)
            ->where('status', 1)
            ->where('parent_id', $parentId)
            ->get()
            ->getResult();
    }
}
