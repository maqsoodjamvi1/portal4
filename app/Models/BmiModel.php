<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Helpers\BMICalculator;

class BmiModel extends Model
{
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }
    
    /**
     * Update student BMI
     */
    public function updateStudentBmi($studentId, $height, $weight, $recordedBy = null)
    {
        $student = $this->db->table('students')
            ->select('student_id, date_of_birth')
            ->where('student_id', $studentId)
            ->get()
            ->getRow();
        
        if (!$student) {
            return false;
        }
        
        // Calculate age
        $age = null;
        if ($student->date_of_birth) {
            $dob = new \DateTime($student->date_of_birth);
            $today = new \DateTime();
            $age = $dob->diff($today)->y;
        }
        
        // Calculate BMI
        $bmi = BMICalculator::calculate($height, $weight);
        $category = BMICalculator::getCategory($bmi, $age);
        $percentile = BMICalculator::getPercentileForAge($bmi, $age);
        
        // Save to history
        $this->db->table('bmi_history')->insert([
            'student_id' => $studentId,
            'height' => $height,
            'weight' => $weight,
            'bmi' => $bmi,
            'bmi_category' => $category,
            'bmi_percentile' => $percentile,
            'recorded_date' => date('Y-m-d'),
            'recorded_by' => $recordedBy,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Update student record
        $this->db->table('students')
            ->where('student_id', $studentId)
            ->update([
                'height' => $height,
                'weight' => $weight,
                'bmi' => $bmi,
                'bmi_category' => $category,
                'bmi_percentile' => $percentile,
                'bmi_updated_date' => date('Y-m-d')
            ]);
        
        // Check and create alerts if needed
        $this->checkAndCreateAlerts($studentId, $bmi, $category);
        
        return [
            'bmi' => $bmi,
            'category' => $category,
            'percentile' => $percentile
        ];
    }
    
    /**
     * Check BMI and create alerts for concerning values
     */
    private function checkAndCreateAlerts($studentId, $bmi, $category)
    {
        $alertTypes = [
            'underweight' => ['type' => 'underweight', 'threshold' => 18.5],
            'overweight' => ['type' => 'overweight', 'threshold' => 25],
            'obese' => ['type' => 'obese', 'threshold' => 30]
        ];
        
        if (isset($alertTypes[$category])) {
            // Check if alert already exists for this student
            $existing = $this->db->table('health_alerts')
                ->where('student_id', $studentId)
                ->where('alert_type', $alertTypes[$category]['type'])
                ->where('is_read', 0)
                ->get()
                ->getRow();
            
            if (!$existing) {
                $message = $this->getAlertMessage($category, $bmi);
                
                $this->db->table('health_alerts')->insert([
                    'student_id' => $studentId,
                    'alert_type' => $alertTypes[$category]['type'],
                    'bmi_value' => $bmi,
                    'message' => $message,
                    'created_date' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }
    
    /**
     * Get alert message based on BMI category
     */
    private function getAlertMessage($category, $bmi)
    {
        $messages = [
            'underweight' => "Your child's BMI ({$bmi}) indicates underweight. Please consult a nutritionist for a healthy diet plan.",
            'overweight' => "Your child's BMI ({$bmi}) indicates overweight. Consider healthy eating habits and regular physical activity.",
            'obese' => "Your child's BMI ({$bmi}) indicates obesity. We recommend consulting a healthcare professional."
        ];
        
        return $messages[$category] ?? "Your child's BMI is {$bmi}. Please monitor their health regularly.";
    }
    
    /**
     * Get BMI history for a student
     */
    public function getBmiHistory($studentId, $limit = 12)
    {
        return $this->db->table('bmi_history')
            ->where('student_id', $studentId)
            ->orderBy('recorded_date', 'DESC')
            ->limit($limit)
            ->get()
            ->getResult();
    }
    
    /**
     * Get nutrition suggestions based on BMI category
     */
    public function getNutritionSuggestions($category, $age = null, $gender = null)
    {
        $builder = $this->db->table('nutrition_suggestions')
            ->where('bmi_category', $category)
            ->where('is_active', 1);
        
        if ($age) {
            // Determine age group
            if ($age <= 6) $ageGroup = '4-6';
            elseif ($age <= 9) $ageGroup = '7-9';
            elseif ($age <= 12) $ageGroup = '10-12';
            elseif ($age <= 15) $ageGroup = '13-15';
            else $ageGroup = '16-18';
            
            $builder->groupStart()
                ->where('age_group', $ageGroup)
                ->orWhere('age_group IS NULL')
                ->groupEnd();
        }
        
        if ($gender) {
            $builder->groupStart()
                ->where('gender', $gender)
                ->orWhere('gender', 'both')
                ->groupEnd();
        }
        
        return $builder->orderBy('sort_order', 'ASC')->get()->getResult();
    }
    
    /**
     * Get students by BMI category for reporting
     */
    public function getStudentsByBmiCategory($campusId, $category = null)
    {
        $builder = $this->db->table('students s')
            ->select('s.student_id, s.first_name, s.last_name, s.gender, s.date_of_birth, 
                      s.height, s.weight, s.bmi, s.bmi_category, c.class_name, sec.section_name')
            ->join('student_class sc', 'sc.student_id = s.student_id AND sc.status = 1')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections sec', 'sec.section_id = cs.section_id')
            ->where('s.campus_id', $campusId)
            ->where('s.status', 1)
            ->where('s.bmi IS NOT NULL');
        
        if ($category) {
            $builder->where('s.bmi_category', $category);
        }
        
        return $builder->orderBy('s.bmi', 'DESC')->get()->getResult();
    }
    
    /**
     * Get BMI statistics for dashboard
     */
    public function getBmiStatistics($campusId)
    {
        $stats = $this->db->table('students')
            ->select('
                COUNT(*) as total,
                SUM(CASE WHEN bmi_category = "underweight" THEN 1 ELSE 0 END) as underweight,
                SUM(CASE WHEN bmi_category = "normal" THEN 1 ELSE 0 END) as normal,
                SUM(CASE WHEN bmi_category = "overweight" THEN 1 ELSE 0 END) as overweight,
                SUM(CASE WHEN bmi_category = "obese" THEN 1 ELSE 0 END) as obese,
                AVG(bmi) as avg_bmi
            ')
            ->where('campus_id', $campusId)
            ->where('status', 1)
            ->where('bmi IS NOT NULL')
            ->get()
            ->getRow();
        
        return $stats;
    }
}