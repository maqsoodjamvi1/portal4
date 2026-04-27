<?php
namespace App\Models;

use CodeIgniter\Model;

class StudentModel extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'student_id';
    
    protected $allowedFields = [
        'reg_no', 'full_name', 'cls_sec_id', 'father_name', 'mother_name',
        'dob', 'religion', 'nationality', 'admission_date', 'student_status',
        'leaving_date', 'leaving_reason', 'session', 'created_at', 'updated_at'
    ];
    
    // Add these fields if they don't exist in your table
    // Run migration to add these columns
}