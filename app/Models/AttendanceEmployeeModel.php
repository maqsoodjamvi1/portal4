<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceEmployeeModel extends Model
{
    protected $table = 'attendance_employee';
    protected $primaryKey = 'attendance_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'emp_id', 'date', 'checkin', 'checkout', 'lc_duration', 'el_duration',
        'status', 'created_date', 'updated_date', 'user_id', 'check_in_method',
        'check_out_method', 'qr_code_used', 'updated_reason', 'remarks'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_date';
    protected $updatedField = 'updated_date';
    
    /**
     * Get today's attendance for an employee
     */
    public function getTodayAttendance($emp_id)
    {
        $today = date('Y-m-d');
        
        return $this->where('emp_id', $emp_id)
                    ->where('date', $today)
                    ->first();
    }
    
    /**
     * Check in employee
     */
    public function checkIn($emp_id, $method = 'qr', $qr_code = null)
    {
        $today = date('Y-m-d');
        $now = date('H:i:s');
        $datetime = date('Y-m-d H:i:s');
        
        // Check if already checked in
        $existing = $this->getTodayAttendance($emp_id);
        if ($existing) {
            return false;
        }
        
        // Determine status based on time (you can customize this)
        $late_threshold = '08:15:00';
        $status = ($now > $late_threshold) ? 'late' : 'present';
        
        $data = [
            'emp_id' => $emp_id,
            'date' => $today,
            'checkin' => $now,
            'status' => $status,
            'check_in_method' => $method,
            'qr_code_used' => $qr_code,
            'created_date' => $datetime,
            'user_id' => $emp_id // or session user ID
        ];
        
        return $this->insert($data);
    }
    
    /**
     * Check out employee
     */
    public function checkOut($emp_id, $method = 'qr', $qr_code = null)
    {
        $today = date('Y-m-d');
        $now = date('H:i:s');
        $datetime = date('Y-m-d H:i:s');
        
        $attendance = $this->getTodayAttendance($emp_id);
        
        if (!$attendance || $attendance['checkout']) {
            return false;
        }
        
        // Calculate duration
        $checkin = new \DateTime($attendance['checkin']);
        $checkout = new \DateTime($now);
        $interval = $checkin->diff($checkout);
        $duration = $interval->h * 60 + $interval->i; // in minutes
        
        return $this->update($attendance['attendance_id'], [
            'checkout' => $now,
            'lc_duration' => $duration,
            'check_out_method' => $method,
            'qr_code_used' => $qr_code,
            'updated_date' => $datetime
        ]);
    }
    
    /**
     * Get today's statistics for dashboard
     */
    public function getTodayStats($campus_id = null)
    {
        $today = date('Y-m-d');
        
        $builder = $this->select('attendance_employee.*, users.first_name, users.last_name, users.designation')
                        ->join('users', 'users.id = attendance_employee.emp_id');
        
        if ($campus_id) {
            $builder->where('users.campus_id', $campus_id);
        }
        
        $today_attendances = $builder->where('attendance_employee.date', $today)
                                     ->findAll();
        
        $total = count($today_attendances);
        $checked_in = $total;
        $checked_out = 0;
        $late = 0;
        $present = 0;
        
        foreach ($today_attendances as $att) {
            if ($att['checkout']) {
                $checked_out++;
            }
            if ($att['status'] == 'late') {
                $late++;
            }
            if ($att['status'] == 'present' || $att['status'] == 'late') {
                $present++;
            }
        }
        
        return [
            'total' => $total,
            'checked_in' => $checked_in,
            'checked_out' => $checked_out,
            'present' => $present,
            'late' => $late,
            'remaining' => $checked_in - $checked_out
        ];
    }
}