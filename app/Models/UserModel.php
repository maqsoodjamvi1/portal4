<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'campus_id', 'email', 'username', 'cnic', 'photo', 'password',
        'last_name', 'first_name', 'dob', 'f_name', 'gender', 'marital_status',
        'joining_date', 'mobile_no', 'mobile_no2', 'address', 'emergency_contact_person',
        'emergency_contact_no', 'leaving_reason', 'leaving_date', 'qualification',
        'experience', 'skills', 'owner_sites', 'status', 'cur_login_time', 'cur_login_ip',
        'cur_login_area', 'last_login_ip', 'last_login_area', 'last_login_time',
        'reg_time', 'reg_ip', 'reg_area', 'login_times', 'parent_user_id', 'issys',
        'user_id', 'created_date', 'updated_date', 'bank_name', 'account_title',
        'branch_code', 'account_number', 'bank_address', 'contract_start', 'contract_end',
        'designation', 'wpwd'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_date';
    protected $updatedField = 'updated_date';
    
    // Only get active teachers/employees
    public function getActiveTeachers()
    {
        return $this->where('status', 1)
                    ->where('campus_id !=', 0)
                    ->findAll();
    }
    
    // Get teacher by ID with campus check
    public function getTeacher($id, $campus_id = null)
    {
        $builder = $this->where('id', $id)
                        ->where('status', 1);
        
        if ($campus_id) {
            $builder->where('campus_id', $campus_id);
        }
        
        return $builder->first();
    }
}