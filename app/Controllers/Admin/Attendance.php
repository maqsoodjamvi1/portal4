<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Attendance extends BaseController
{
    protected $db;
    protected $session;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
    }
    
    /**
     * Main scanner page
     */
    public function scan()
    {
        $data['title'] = 'Teacher Attendance Scanner';
        return view('admin/attendance/scanner', $data);
    }
    

    /**
 * Manual attendance entry page
 */
public function manual()
{
    helper('server_helper');
    $db = \Config\Database::connect();
    
    // Get all active teachers for the dropdown
    $campus_id = session()->get('member_campusid') ?? session()->get('campus_id');
    
    $data['teachers'] = $db->table('users')
        ->where('status', 1)
        ->where('campus_id', $campus_id)
        ->orderBy('first_name', 'ASC')
        ->get()
        ->getResult();
    
    $data['title'] = 'Manual Attendance Entry';
    
    // Handle form submission
    if ($this->request->getMethod() === 'post') {
        $teacher_id = $this->request->getPost('teacher_id');
        $action = $this->request->getPost('action'); // 'in' or 'out'
        $date = $this->request->getPost('date') ?: date('Y-m-d');
        $time = $this->request->getPost('time') ?: date('H:i:s');
        $notes = $this->request->getPost('notes');
        
        $datetime = $date . ' ' . $time;
        $today = date('Y-m-d');
        
        // Check if attendance already exists for today
        $attendance = $db->table('attendance_employee')
            ->where('emp_id', $teacher_id)
            ->where('date', $date)
            ->get()
            ->getRow();
        
        if ($action === 'in') {
            if ($attendance && $attendance->checkin) {
                return redirect()->back()->with('error', 'Check-in already recorded for this date');
            }
            
            // Determine status based on time (if today)
            $status = 'present';
            if ($date == $today) {
                $late_threshold = '08:30:00';
                if ($time > $late_threshold) {
                    $status = 'late';
                }
            }
            
            $insertData = [
                'emp_id' => $teacher_id,
                'date' => $date,
                'checkin' => $time,
                'status' => $status,
                'check_in_method' => 'manual',
                'remarks' => $notes,
                'created_date' => date('Y-m-d H:i:s'),
                'user_id' => session()->get('user_id')
            ];
            
            $db->table('attendance_employee')->insert($insertData);
            
            // Get teacher name for message
            $teacher = $db->table('users')->where('id', $teacher_id)->get()->getRow();
            $message = "✅ Check-in recorded for {$teacher->first_name} {$teacher->last_name} on " . date('d M Y', strtotime($date)) . " at " . date('h:i A', strtotime($time));
            
            return redirect()->to('admin/attendance/manual')->with('message', $message);
            
        } else if ($action === 'out') {
            if (!$attendance) {
                return redirect()->back()->with('error', 'No check-in found for this date. Please record check-in first.');
            }
            
            if ($attendance->checkout) {
                return redirect()->back()->with('error', 'Check-out already recorded for this date');
            }
            
            // Calculate duration
            $checkin_time = $attendance->checkin;
            $duration_minutes = 0;
            if ($checkin_time) {
                $checkin = new \DateTime($date . ' ' . $checkin_time);
                $checkout = new \DateTime($date . ' ' . $time);
                $interval = $checkin->diff($checkout);
                $duration_minutes = ($interval->h * 60) + $interval->i;
            }
            
            $db->table('attendance_employee')
                ->where('attendance_id', $attendance->attendance_id)
                ->update([
                    'checkout' => $time,
                    'check_out_method' => 'manual',
                    'lc_duration' => $duration_minutes,
                    'remarks' => $notes,
                    'updated_date' => date('Y-m-d H:i:s')
                ]);
            
            $teacher = $db->table('users')->where('id', $teacher_id)->get()->getRow();
            $message = "✅ Check-out recorded for {$teacher->first_name} {$teacher->last_name} on " . date('d M Y', strtotime($date)) . " at " . date('h:i A', strtotime($time));
            
            return redirect()->to('admin/attendance/manual')->with('message', $message);
        }
    }
    
    return view('admin/attendance/manual', $data);
}

/**
 * Attendance report page
 */
public function report()
{
    helper('server_helper');
    $db = \Config\Database::connect();
    
    $campus_id = session()->get('member_campusid') ?? session()->get('campus_id');
    $date_from = $this->request->getGet('date_from') ?? date('Y-m-01');
    $date_to = $this->request->getGet('date_to') ?? date('Y-m-d');
    $teacher_id = $this->request->getGet('teacher_id');
    
    $query = $db->table('attendance_employee a')
        ->select('a.*, u.first_name, u.last_name, u.designation')
        ->join('users u', 'a.emp_id = u.id')
        ->where('u.campus_id', $campus_id)
        ->where('a.date >=', $date_from)
        ->where('a.date <=', $date_to);
    
    if ($teacher_id) {
        $query->where('a.emp_id', $teacher_id);
    }
    
    $data['attendances'] = $query->orderBy('a.date', 'DESC')
        ->orderBy('a.checkin', 'DESC')
        ->get()
        ->getResult();
    
    $data['teachers'] = $db->table('users')
        ->where('status', 1)
        ->where('campus_id', $campus_id)
        ->orderBy('first_name', 'ASC')
        ->get()
        ->getResult();
    
    $data['date_from'] = $date_from;
    $data['date_to'] = $date_to;
    $data['selected_teacher'] = $teacher_id;
    $data['title'] = 'Attendance Report';
    
    return view('admin/attendance/report', $data);
}

/**
 * Daily attendance summary page
 */
public function summary()
{
    helper('server_helper');
    $db = \Config\Database::connect();
    
    $campus_id = session()->get('member_campusid') ?? session()->get('campus_id');
    $date = $this->request->getGet('date') ?? date('Y-m-d');
    
    // Get today's attendance summary
    $summary = [];
    
    // Total teachers
    $totalTeachers = $db->table('users')
        ->where('status', 1)
        ->where('campus_id', $campus_id)
        ->countAllResults();
    
    // Present today
    $present = $db->table('attendance_employee')
        ->where('date', $date)
        ->countAllResults();
    
    // Late arrivals
    $late = $db->table('attendance_employee')
        ->where('date', $date)
        ->where('status', 'late')
        ->countAllResults();
    
    // Checked out
    $checkedOut = $db->table('attendance_employee')
        ->where('date', $date)
        ->where('checkout IS NOT NULL')
        ->countAllResults();
    
    // QR scans vs manual
    $qrScans = $db->table('attendance_employee')
        ->where('date', $date)
        ->where('check_in_method', 'qr')
        ->countAllResults();
    
    $manual = $db->table('attendance_employee')
        ->where('date', $date)
        ->where('check_in_method', 'manual')
        ->countAllResults();
    
    // Get list of present teachers with details
    $presentTeachers = $db->table('attendance_employee a')
        ->select('a.*, u.first_name, u.last_name, u.designation, u.photo')
        ->join('users u', 'a.emp_id = u.id')
        ->where('a.date', $date)
        ->orderBy('a.checkin', 'ASC')
        ->get()
        ->getResult();
    
    // Get absent teachers (active teachers with no attendance today)
    $absentTeachers = $db->table('users u')
        ->select('u.id, u.first_name, u.last_name, u.designation, u.photo')
        ->where('u.status', 1)
        ->where('u.campus_id', $campus_id)
        ->whereNotIn('u.id', function($subquery) use ($db, $date) {
            $subquery->select('emp_id')
                ->from('attendance_employee')
                ->where('date', $date);
        })
        ->orderBy('u.first_name', 'ASC')
        ->get()
        ->getResult();
    
    $data = [
        'title' => 'Daily Attendance Summary',
        'date' => $date,
        'total_teachers' => $totalTeachers,
        'present' => $present,
        'late' => $late,
        'checked_out' => $checkedOut,
        'qr_scans' => $qrScans,
        'manual' => $manual,
        'present_teachers' => $presentTeachers,
        'absent_teachers' => $absentTeachers,
        'attendance_percentage' => $totalTeachers > 0 ? round(($present / $totalTeachers) * 100, 1) : 0
    ];
    
    return view('admin/attendance/summary', $data);
}
    /**
     * Process QR code scan (AJAX endpoint)
     */
    public function process()
    {
        // Only accept AJAX requests
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method']);
        }
        
        $qr_code = $this->request->getPost('qr_code');
        
        if (!$qr_code) {
            return $this->response->setJSON(['success' => false, 'message' => 'No QR code provided']);
        }
        
        // Find teacher by QR code
        $qrData = $this->db->table('teacher_qr_codes')
            ->where('qr_code', $qr_code)
            ->where('is_active', 1)
            ->get()
            ->getRow();
        
        if (!$qrData) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Invalid QR code. Please contact administrator.'
            ]);
        }
        
        // Get teacher details
        $teacher = $this->db->table('users')
            ->where('id', $qrData->teacher_id)
            ->get()
            ->getRow();
        
        if (!$teacher) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Teacher not found in system.'
            ]);
        }
        
        // Check today's attendance
        $today = date('Y-m-d');
        $attendance = $this->db->table('attendance_employee')
            ->where('emp_id', $teacher->id)
            ->where('date', $today)
            ->get()
            ->getRow();
        
        $current_time = date('H:i:s');
        $current_datetime = date('Y-m-d H:i:s');
        
        // Define late threshold (e.g., 8:30 AM)
        $late_threshold = '08:30:00';
        $status = 'present';
        
        if ($current_time > $late_threshold && !$attendance) {
            $status = 'late';
        }
        
        if (!$attendance) {
            // First scan of the day - CHECK IN
            $insertData = [
                'emp_id' => $teacher->id,
                'date' => $today,
                'checkin' => $current_time,
                'status' => $status,
                'check_in_method' => 'qr',
                'qr_code_used' => $qr_code,
                'created_date' => $current_datetime,
                'user_id' => $teacher->id
            ];
            
            $this->db->table('attendance_employee')->insert($insertData);
            
            $message = "? Check-in successful for {$teacher->first_name} {$teacher->last_name} at " . date('h:i A');
            $type = 'checkin';
            
        } else {
            // Second scan of the day - CHECK OUT
            if ($attendance->checkout) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "?? Already checked in and out today. Check-in: " . date('h:i A', strtotime($attendance->checkin))
                ]);
            }
            
            // Calculate work hours
            $check_in = new \DateTime($attendance->checkin);
            $check_out = new \DateTime($current_time);
            $interval = $check_in->diff($check_out);
            $hours = $interval->h + ($interval->i / 60);
            
            // Update with check-out
            $this->db->table('attendance_employee')
                ->where('attendance_id', $attendance->attendance_id)
                ->update([
                    'checkout' => $current_time,
                    'check_out_method' => 'qr',
                    'lc_duration' => $interval->i + ($interval->h * 60),
                    'updated_date' => $current_datetime
                ]);
            
            $message = "? Check-out successful for {$teacher->first_name} {$teacher->last_name} at " . date('h:i A') . 
                       " (Worked: " . number_format($hours, 1) . " hours)";
            $type = 'checkout';
        }
        
        // Get today's stats
        $stats = $this->getTodayStats();
        
        return $this->response->setJSON([
            'success' => true,
            'message' => $message,
            'type' => $type,
            'teacher' => [
                'name' => $teacher->first_name . ' ' . $teacher->last_name,
                'id' => $teacher->id,
                'designation' => $teacher->designation ?? ''
            ],
            'stats' => $stats
        ]);
    }
    
    /**
     * Get today's statistics
     */
    public function todayStats()
    {
        $stats = $this->getTodayStats();
        return $this->response->setJSON($stats);
    }
    
    /**
     * Get today's statistics
     */
    private function getTodayStats()
    {
        $today = date('Y-m-d');
        $campus_id = $this->session->get('member_campusid') ?? $this->session->get('campus_id');
        
        // Get all teachers in this campus
        $total = $this->db->table('users')
            ->where('status', 1)
            ->where('campus_id', $campus_id)
            ->countAllResults();
        
        // Get checked in today
        $checked_in = $this->db->table('attendance_employee')
            ->where('date', $today)
            ->countAllResults();
        
        // Get checked out
        $checked_out = $this->db->table('attendance_employee')
            ->where('date', $today)
            ->where('checkout IS NOT NULL')
            ->countAllResults();
        
        // Get late arrivals
        $late = $this->db->table('attendance_employee')
            ->where('date', $today)
            ->where('status', 'late')
            ->countAllResults();
        
        return [
            'total' => $total,
            'checked_in' => $checked_in,
            'checked_out' => $checked_out,
            'late' => $late,
            'remaining' => $checked_in - $checked_out
        ];
    }
}