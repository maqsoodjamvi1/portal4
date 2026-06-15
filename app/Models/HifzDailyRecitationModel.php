<?php

namespace App\Models;

use CodeIgniter\Model;

class HifzDailyRecitationModel extends Model
{
    protected $table            = 'hifz_daily_recitation';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'hifz_student_id',
        'lesson_date',
        'recitation_type',
        'quality',
        'notes',
        'campus_id',
    ];

    public function findForStudentOnDate(int $hifzStudentId, string $lessonDate): array
    {
        if ($hifzStudentId <= 0 || $lessonDate === '') {
            return [];
        }

        return $this->where('hifz_student_id', $hifzStudentId)
            ->where('lesson_date', $lessonDate)
            ->findAll();
    }
}
