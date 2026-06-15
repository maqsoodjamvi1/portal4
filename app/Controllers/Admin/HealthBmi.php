<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class HealthBmi extends BaseController
{
    protected $db;
    protected $session;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
        helper(['form', 'url', 'alert']);
        check_permission('admin-students');
    }
    


    public function growthCharts()
{
    $campusId = $this->session->get('member_campusid');
    
    if (!$campusId) {
        return redirect()->to('admin/dashboard')->with('error', 'Campus not found');
    }
    
    // Get students for selection - students who have at least one BMI record
    $students = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name')
        ->join('bmi_history bh', 'bh.student_id = s.student_id', 'left')
        ->where('s.campus_id', $campusId)
        ->where('s.status', 1)
        ->groupBy('s.student_id')
        ->orderBy('s.first_name')
        ->get()
        ->getResult();
    
    // If no students with BMI history, get all active students
    if (empty($students)) {
        $students = $this->db->table('students')
            ->select('student_id, first_name, last_name')
            ->where('campus_id', $campusId)
            ->where('status', 1)
            ->orderBy('first_name')
            ->get()
            ->getResult();
    }
    
    return view('admin/health/growth_charts', [
        'students' => $students
    ]);
}

public function getGrowthChartData($studentId)
{
    $campusId = $this->session->get('member_campusid');
    
    // Verify student belongs to this campus
    $student = $this->db->table('students')
        ->where('student_id', $studentId)
        ->where('campus_id', $campusId)
        ->get()
        ->getRow();
    
    if (!$student) {
        return $this->response->setJSON([]);
    }
    
    // Get BMI history for this student
    $history = $this->db->table('bmi_history')
        ->where('student_id', $studentId)
        ->orderBy('recorded_date', 'ASC')
        ->get()
        ->getResult();
    
    return $this->response->setJSON($history);
}

    // Dashboard
    public function dashboard()
    {
        $campusId = $this->session->get('member_campusid');
        
        // Get BMI statistics
        $bmiStats = $this->getBmiStatistics($campusId);
        
        // Get recent health alerts
        $recentAlerts = $this->db->table('health_alerts ha')
            ->select('ha.*, s.first_name, s.last_name, s.reg_no')
            ->join('students s', 's.student_id = ha.student_id')
            ->where('ha.is_read', 0)
            ->orderBy('ha.created_date', 'DESC')
            ->limit(10)
            ->get()
            ->getResult();
        
        // Get BMI history trends
        $trendData = $this->getBmiTrends($campusId);
        
        return view('admin/health/bmi_dashboard', [  // Changed from admin/health/ to health/
            'bmiStats' => $bmiStats,
            'recentAlerts' => $recentAlerts,
            'trendData' => $trendData
        ]);
    }
    
    // BMI Records
    public function records()
    {
        $campusId = $this->session->get('member_campusid');
        
        // Get classes for filter
        $classes = $this->db->table('class_section cs')
            ->select('cs.cls_sec_id, c.class_name, s.section_name')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->where('cs.campus_id', $campusId)
            ->where('cs.status', 1)
            ->orderBy('c.class_name')
            ->get()
            ->getResult();
        
        return view('admin/health/bmi_records', [  // Changed from admin/health/ to health/
            'classes' => $classes
        ]);
    }
    
    public function getRecordsData()
    {
        try {
            $campusId = $this->session->get('member_campusid');
            $clsSecId = $this->request->getPost('cls_sec_id');
            $category = $this->request->getPost('category');
            $search = $this->request->getPost('search');
            
            if (!$campusId) {
                return $this->response->setJSON([]);
            }
            
            $builder = $this->db->table('students s')
                ->select('s.student_id, s.first_name, s.last_name, s.reg_no, s.gender, 
                          s.height, s.weight, s.bmi, s.bmi_category, s.bmi_updated_date,
                          c.class_name, sec.section_name')
                ->join('student_class sc', 'sc.student_id = s.student_id AND sc.status = 1')
                ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
                ->join('classes c', 'c.class_id = cs.class_id')
                ->join('sections sec', 'sec.section_id = cs.section_id')
                ->where('s.campus_id', $campusId)
                ->where('s.status', 1);
            
            if ($clsSecId && $clsSecId != '') {
                $builder->where('sc.cls_sec_id', $clsSecId);
            }
            
            if ($category && $category != '') {
                $builder->where('s.bmi_category', $category);
            }
            
            if ($search && $search != '') {
                $builder->groupStart()
                    ->like('s.first_name', $search)
                    ->orLike('s.last_name', $search)
                    ->orLike('s.reg_no', $search)
                    ->groupEnd();
            }
            
            $records = $builder->orderBy('s.first_name', 'ASC')->get()->getResult();
            
            return $this->response->setJSON($records);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getRecordsData: ' . $e->getMessage());
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }
    
    public function saveRecord()
    {
        $studentId = $this->request->getPost('student_id');
        $height = $this->request->getPost('height');
        $weight = $this->request->getPost('weight');
        
        // Calculate BMI
        $bmi = null;
        $bmiCategory = null;
        
        if ($height && $weight && $height > 0 && $weight > 0) {
            $heightInMeters = $height / 100;
            $bmi = round($weight / ($heightInMeters * $heightInMeters), 2);
            $bmiCategory = $this->determineBmiCategory($bmi);
        }
        
        $data = [
            'height' => $height,
            'weight' => $weight,
            'bmi' => $bmi,
            'bmi_category' => $bmiCategory,
            'bmi_updated_date' => date('Y-m-d'),
            'updated_date' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->db->table('students')
            ->where('student_id', $studentId)
            ->update($data);
        
        $this->checkAndCreateAlert($studentId, $bmi, $bmiCategory);
        if ($result) {
            // Record in history
            $this->recordBmiHistory($studentId, $height, $weight, $bmi, $bmiCategory);
            
            return $this->response->setJSON([
                'success' => true,
                'msg' => 'BMI record saved successfully',
                'bmi' => $bmi,
                'bmi_category' => $bmiCategory
            ]);
        }
        
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Failed to save BMI record'
        ]);
    }
    
    public function alerts()
    {
        return view('admin/health/alerts');  // Changed from admin/health/ to health/
    }
    
   public function getAlertsData()
    {
        $campusId = $this->session->get('member_campusid');
        $status = $this->request->getPost('status') ?? 'unread';
        
        $builder = $this->db->table('health_alerts ha')
            ->select('ha.*, s.first_name, s.last_name, s.reg_no, 
                      c.class_name, sec.section_name')
            ->join('students s', 's.student_id = ha.student_id')
            ->join('student_class sc', 'sc.student_id = s.student_id AND sc.status = 1', 'left')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
            ->where('s.campus_id', $campusId);
        
        if ($status == 'unread') {
            $builder->where('ha.is_read', 0);
        }
        
        $alerts = $builder->orderBy('ha.created_date', 'DESC')->get()->getResult();
        
        return $this->response->setJSON($alerts);
    }
    
   public function markAlertRead($alertId)
    {
        $result = $this->db->table('health_alerts')
            ->where('alert_id', $alertId)
            ->update(['is_read' => 1]);
        
        return $this->response->setJSON(['success' => $result]);
    }
    
    /**
     * Mark all alerts as read
     */
    public function markAllAlertsRead()
    {
        $campusId = $this->session->get('member_campusid');
        
        $result = $this->db->table('health_alerts ha')
            ->join('students s', 's.student_id = ha.student_id')
            ->where('s.campus_id', $campusId)
            ->where('ha.is_read', 0)
            ->update(['ha.is_read' => 1]);
        
        return $this->response->setJSON(['success' => true]);
    }
    
    /**
     * Create alert when BMI is recorded (called from saveRecord/updateStudentBmi)
     */
    private function checkAndCreateAlert($studentId, $bmi, $category)
    {
        $alertCategories = ['underweight', 'overweight', 'obese'];
        
        if (in_array($category, $alertCategories)) {
            // Check if unread alert already exists
            $existing = $this->db->table('health_alerts')
                ->where('student_id', $studentId)
                ->where('alert_type', $category)
                ->where('is_read', 0)
                ->get()
                ->getRow();
            
            if (!$existing) {
                $student = $this->db->table('students')
                    ->select('first_name, last_name')
                    ->where('student_id', $studentId)
                    ->get()
                    ->getRow();
                
                $message = $this->getAlertMessage($category, $bmi, $student->first_name);
                
                $this->db->table('health_alerts')->insert([
                    'student_id' => $studentId,
                    'alert_type' => $category,
                    'bmi_value' => $bmi,
                    'message' => $message,
                    'created_date' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }
    
    /**
     * Get alert message based on category
     */
    private function getAlertMessage($category, $bmi, $studentName)
    {
        $messages = [
            'underweight' => "{$studentName}'s BMI ({$bmi}) indicates underweight. Please ensure proper nutrition and consult a healthcare provider if concerned.",
            'overweight' => "{$studentName}'s BMI ({$bmi}) indicates overweight. Encourage physical activity and healthy eating habits.",
            'obese' => "{$studentName}'s BMI ({$bmi}) indicates obesity. Medical consultation recommended for a proper health plan."
        ];
        
        return $messages[$category] ?? "Health alert for {$studentName} (BMI: {$bmi})";
    }
    
    /**
     * Send alert notifications to parents (optional - can be run via cron job)
     */
    public function sendAlertNotifications()
    {
        $alerts = $this->db->table('health_alerts ha')
            ->select('ha.*, s.first_name, s.last_name, p.father_contact, p.father_email')
            ->join('students s', 's.student_id = ha.student_id')
            ->join('parents p', 'p.parent_id = s.parent_id')
            ->where('ha.is_sent', 0)
            ->where('ha.is_read', 0)
            ->limit(50)
            ->get()
            ->getResult();
        
        $sent = 0;
        foreach ($alerts as $alert) {
            // Send SMS
            if ($alert->father_contact) {
                $this->sendSms($alert->father_contact, $alert->message);
            }
            
            // Send Email
            if ($alert->father_email) {
                $this->sendEmail($alert->father_email, "Health Alert: {$alert->first_name} {$alert->last_name}", $alert->message);
            }
            
            // Mark as sent
            $this->db->table('health_alerts')
                ->where('alert_id', $alert->alert_id)
                ->update(['is_sent' => 1, 'sent_date' => date('Y-m-d H:i:s')]);
            
            $sent++;
        }
        
        return $this->response->setJSON([
            'success' => true,
            'sent' => $sent,
            'message' => "Sent {$sent} notifications"
        ]);
    }
    
    /**
     * SMS sending helper
     */
    private function sendSms($phone, $message)
    {
        // Implement your SMS gateway here
        log_message('info', "SMS to {$phone}: {$message}");
    }
    
    /**
     * Email sending helper
     */
    private function sendEmail($email, $subject, $message)
    {
        // Implement your email sending logic here
        log_message('info', "Email to {$email}: {$subject} - {$message}");
    }
    
    public function nutritionSuggestions()
    {
        $suggestions = $this->db->table('nutrition_suggestions')
            ->orderBy('bmi_category')
            ->orderBy('sort_order')
            ->get()
            ->getResult();
        
        return view('admin/health/nutrition_suggestions', [  // Changed from admin/health/ to health/
            'suggestions' => $suggestions
        ]);
    }
    
    public function getNutritionSuggestionsData()
    {
        $category = $this->request->getPost('category');
        $suggestionId = $this->request->getPost('suggestion_id');
        
        $builder = $this->db->table('nutrition_suggestions');
        
        if ($category) {
            $builder->where('bmi_category', $category);
        }
        
        if ($suggestionId) {
            $builder->where('suggestion_id', $suggestionId);
        }
        
        $suggestions = $builder->orderBy('sort_order')->get()->getResult();
        
        return $this->response->setJSON($suggestions);
    }
    
    public function addNutritionSuggestion()
    {
        $data = [
            'bmi_category' => $this->request->getPost('bmi_category'),
            'age_group' => $this->request->getPost('age_group'),
            'gender' => $this->request->getPost('gender'),
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'diet_tips' => $this->request->getPost('diet_tips'),
            'foods_to_eat' => $this->request->getPost('foods_to_eat'),
            'foods_to_avoid' => $this->request->getPost('foods_to_avoid'),
            'exercise_suggestions' => $this->request->getPost('exercise_suggestions'),
            'medical_advice' => $this->request->getPost('medical_advice'),
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
            'is_active' => 1
        ];
        
        $result = $this->db->table('nutrition_suggestions')->insert($data);
        
        if ($result) {
            return $this->response->setJSON(['success' => true, 'msg' => 'Suggestion added successfully']);
        }
        
        return $this->response->setJSON(['success' => false, 'msg' => 'Failed to add suggestion']);
    }
    
    public function updateNutritionSuggestion($id)
    {
        $data = [
            'bmi_category' => $this->request->getPost('bmi_category'),
            'age_group' => $this->request->getPost('age_group'),
            'gender' => $this->request->getPost('gender'),
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'diet_tips' => $this->request->getPost('diet_tips'),
            'foods_to_eat' => $this->request->getPost('foods_to_eat'),
            'foods_to_avoid' => $this->request->getPost('foods_to_avoid'),
            'exercise_suggestions' => $this->request->getPost('exercise_suggestions'),
            'medical_advice' => $this->request->getPost('medical_advice'),
            'sort_order' => $this->request->getPost('sort_order') ?: 0
        ];
        
        $result = $this->db->table('nutrition_suggestions')
            ->where('suggestion_id', $id)
            ->update($data);
        
        if ($result) {
            return $this->response->setJSON(['success' => true, 'msg' => 'Suggestion updated successfully']);
        }
        
        return $this->response->setJSON(['success' => false, 'msg' => 'Failed to update suggestion']);
    }
    
    public function deleteNutritionSuggestion($id)
    {
        $result = $this->db->table('nutrition_suggestions')
            ->where('suggestion_id', $id)
            ->delete();
        
        if ($result) {
            return $this->response->setJSON(['success' => true, 'msg' => 'Suggestion deleted successfully']);
        }
        
        return $this->response->setJSON(['success' => false, 'msg' => 'Failed to delete suggestion']);
    }
    
    public function reports()
    {
        $campusId = $this->session->get('member_campusid');
        
        // Get classes for filter
        $classes = $this->db->table('class_section cs')
            ->select('cs.cls_sec_id, c.class_name, s.section_name')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->where('cs.campus_id', $campusId)
            ->where('cs.status', 1)
            ->get()
            ->getResult();
        
        return view('admin/health/bmi_reports', [  // Changed from admin/health/ to health/
            'classes' => $classes
        ]);
    }
    
    public function generateReport()
    {
        $campusId = $this->session->get('member_campusid');
        $clsSecId = $this->request->getPost('cls_sec_id');
        $category = $this->request->getPost('category');
        $reportType = $this->request->getPost('report_type');
        
        $builder = $this->db->table('students s')
            ->select('s.*, c.class_name, sec.section_name')
            ->join('student_class sc', 'sc.student_id = s.student_id AND sc.status = 1')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections sec', 'sec.section_id = cs.section_id')
            ->where('s.campus_id', $campusId)
            ->where('s.status', 1)
            ->where('s.bmi IS NOT NULL');
        
        if ($clsSecId) {
            $builder->where('sc.cls_sec_id', $clsSecId);
        }
        
        if ($category) {
            $builder->where('s.bmi_category', $category);
        }
        
        $students = $builder->orderBy('s.first_name')->get()->getResult();
        
        // Generate view based on report type
        return view('admin/health/report_output', [  // Changed from admin/health/ to health/
            'students' => $students,
            'reportType' => $reportType,
            'generatedDate' => date('d-M-Y H:i:s')
        ]);
    }
    
    
    
   
    
    public function updateStudentBmi()
    {
        $studentId = $this->request->getPost('student_id');
        $height = $this->request->getPost('height');
        $weight = $this->request->getPost('weight');
        $notes = $this->request->getPost('notes');
        
        if (!$studentId) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Student ID required']);
        }
        
        // Get student age
        $student = $this->db->table('students')
            ->select('date_of_birth, db_status, date_of_birth_age')
            ->where('student_id', $studentId)
            ->get()
            ->getRow();
        
        // Calculate BMI
        $bmi = null;
        $bmiCategory = null;
        
        if ($height && $weight && $height > 0 && $weight > 0) {
            $heightInMeters = $height / 100;
            $bmi = round($weight / ($heightInMeters * $heightInMeters), 2);
            
            // Calculate age
            $age = null;
            $dob = null;
            
            if ($student && $student->db_status == 1 && $student->date_of_birth_age) {
                $dob = $student->date_of_birth_age;
            } elseif ($student && $student->date_of_birth) {
                $dob = $student->date_of_birth;
            }
            
            if ($dob) {
                $birthDate = new \DateTime($dob);
                $today = new \DateTime();
                $age = $birthDate->diff($today)->y;
            }
            
            $bmiCategory = $this->determineBmiCategory($bmi, $age);
        }
        
        $data = [
            'height' => $height,
            'weight' => $weight,
            'bmi' => $bmi,
            'bmi_category' => $bmiCategory,
            'bmi_updated_date' => date('Y-m-d'),
            'updated_date' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->db->table('students')
            ->where('student_id', $studentId)
            ->update($data);
        
        if ($result) {
            // Record history
            $this->recordBmiHistory($studentId, $height, $weight, $bmi, $bmiCategory, $notes);
            
            // Check for health alerts
            $this->checkAndCreateAlert($studentId, $bmi, $bmiCategory);
            
            return $this->response->setJSON([
                'success' => true,
                'msg' => 'BMI updated successfully',
                'bmi' => $bmi,
                'bmi_category' => $bmiCategory
            ]);
        }
        
        return $this->response->setJSON(['success' => false, 'msg' => 'Update failed']);
    }
    
    public function getStatistics()
    {
        $campusId = $this->session->get('member_campusid');
        $stats = $this->getBmiStatistics($campusId);
        
        return $this->response->setJSON($stats);
    }
    
    // Helper Methods
    private function determineBmiCategory($bmi, $age = null)
    {
        if ($age && $age < 18) {
            // Pediatric BMI categories (simplified)
            if ($bmi < 15) return 'underweight';
            if ($bmi < 19) return 'normal';
            if ($bmi < 23) return 'overweight';
            return 'obese';
        }
        
        // Adult categories
        if ($bmi < 18.5) return 'underweight';
        if ($bmi < 25) return 'normal';
        if ($bmi < 30) return 'overweight';
        return 'obese';
    }
    
    private function recordBmiHistory($studentId, $height, $weight, $bmi, $bmiCategory, $notes = null)
    {
        // Check if table exists
        $tables = $this->db->listTables();
        
        if (in_array('bmi_history', $tables)) {
            $this->db->table('bmi_history')->insert([
                'student_id' => $studentId,
                'height' => $height,
                'weight' => $weight,
                'bmi' => $bmi,
                'bmi_category' => $bmiCategory,
                'recorded_date' => date('Y-m-d'),
                'recorded_by' => $this->session->get('member_userid'),
                'notes' => $notes,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
   
    
    
    private function getBmiStatistics($campusId)
    {
        return $this->db->table('students')
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
    }
    
    private function getBmiTrends($campusId)
    {
        // Get monthly trends for the last 12 months
        return $this->db->table('bmi_history bh')
            ->select('
                DATE_FORMAT(bh.recorded_date, "%b %Y") as month,
                AVG(bh.bmi) as avg_bmi,
                SUM(CASE WHEN bh.bmi_category = "underweight" THEN 1 ELSE 0 END) as underweight_count,
                SUM(CASE WHEN bh.bmi_category = "normal" THEN 1 ELSE 0 END) as normal_count,
                SUM(CASE WHEN bh.bmi_category = "overweight" THEN 1 ELSE 0 END) as overweight_count,
                SUM(CASE WHEN bh.bmi_category = "obese" THEN 1 ELSE 0 END) as obese_count
            ')
            ->join('students s', 's.student_id = bh.student_id')
            ->where('s.campus_id', $campusId)
            ->where('bh.recorded_date >=', date('Y-m-d', strtotime('-11 months')))
            ->groupBy('DATE_FORMAT(bh.recorded_date, "%b %Y")')
            ->orderBy('bh.recorded_date', 'ASC')
            ->get()
            ->getResult();
    }
}