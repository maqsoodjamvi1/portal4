<?php
namespace App\Models;

use CodeIgniter\Model;

class AcademicSessionModel extends Model
{
    protected $table = 'academic_session';
    protected $primaryKey = 'session_id';
    protected $allowedFields = ['session_name', 'start_date', 'end_date', 'status', 'system_id'];
}