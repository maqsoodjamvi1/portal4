<?php

namespace App\Models;

use CodeIgniter\Model;

class HifzStudentModel extends Model
{
    protected $table            = 'hifz_students';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'student_id',
        'hifz_section_id',
        'campus_id',
        'session_id',
        'status',
        'enrolled_at',
        'withdrawn_at',
    ];

    public function findActiveForSection(int $sectionId, int $campusId): array
    {
        if ($sectionId <= 0 || $campusId <= 0) {
            return [];
        }

        return $this->where('hifz_section_id', $sectionId)
            ->where('campus_id', $campusId)
            ->where('status', 'active')
            ->findAll();
    }
}
