<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceSettingsModel extends Model
{
    protected $table = 'attendance_settings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'campus_id', 'late_threshold', 'half_day_threshold', 
        'grace_period_minutes', 'allow_self_checkout', 
        'qr_code_expiry_days', 'user_id'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_date';
    protected $updatedField = 'updated_date';
    
    /**
     * Get settings for a campus
     */
    public function getByCampus($campus_id)
    {
        $settings = $this->where('campus_id', $campus_id)->first();
        
        if (!$settings) {
            // Create default settings
            $settings = [
                'campus_id' => $campus_id,
                'late_threshold' => '08:15:00',
                'half_day_threshold' => '12:00:00',
                'grace_period_minutes' => 5,
                'allow_self_checkout' => 1,
                'qr_code_expiry_days' => 365,
                'user_id' => session()->get('user_id') ?? 1
            ];
            $this->insert($settings);
            $settings = $this->where('campus_id', $campus_id)->first();
        }
        
        return $settings;
    }
}