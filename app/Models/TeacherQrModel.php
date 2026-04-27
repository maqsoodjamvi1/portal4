<?php

namespace App\Models;

use CodeIgniter\Model;

class TeacherQrModel extends Model
{
    protected $table = 'teacher_qr_codes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'teacher_id',
        'qr_code',
        'is_active',
        'campus_id'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'generated_at';
    protected $updatedField = null; // No updated field in this table
    
    protected $validationRules = [
        'teacher_id' => 'required|is_natural_no_zero',
        'qr_code' => 'required|is_unique[teacher_qr_codes.qr_code,id,{id}]'
    ];
    
    /**
     * Generate unique QR code for teacher
     */
    public function generateQRCode($teacher_id, $email, $campus_id)
    {
        $qr_string = 'TCHR_' . $teacher_id . '_' . md5($email . time() . rand(1000, 9999));
        
        $data = [
            'teacher_id' => $teacher_id,
            'qr_code' => $qr_string,
            'campus_id' => $campus_id,
            'is_active' => 1
        ];
        
        return $this->insert($data);
    }
    
    /**
     * Get QR code by teacher ID
     */
    public function getByTeacherId($teacher_id)
    {
        return $this->where('teacher_id', $teacher_id)
                    ->where('is_active', 1)
                    ->first();
    }
    
    /**
     * Get teacher by QR code
     */
    public function getTeacherByQR($qr_code)
    {
        return $this->select('teacher_qr_codes.*, users.first_name, users.last_name, users.email, users.designation')
                    ->join('users', 'users.id = teacher_qr_codes.teacher_id')
                    ->where('teacher_qr_codes.qr_code', $qr_code)
                    ->where('teacher_qr_codes.is_active', 1)
                    ->where('users.status', 1)
                    ->first();
    }
}