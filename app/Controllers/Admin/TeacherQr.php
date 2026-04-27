<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\TeacherQrModel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

class TeacherQr extends BaseController
{
    protected $db;
    protected $session;
    protected $userModel;
    protected $qrModel;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->userModel = new UserModel();
        $this->qrModel = new TeacherQrModel();
        helper(['form', 'url']);
        
        // Check permission if needed
        // check_permission('admin-qr-management');
    }
    
    public function testGenerate()
    {
        helper('server_helper');
        
        echo "<h2>QR Generation Test</h2>";
        
        // Get school info and session data
        $schoolinfo = getSchoolInfo();
        $campus_id = $this->session->get('member_campusid') ?? $this->session->get('campus_id');
        $session_id = $this->session->get('member_sessionid');
        
        echo "<p>School ID: " . ($schoolinfo->system_id ?? 'not set') . "</p>";
        echo "<p>Campus ID from session: " . ($campus_id ?? 'not set') . "</p>";
        echo "<p>Session ID from session: " . ($session_id ?? 'not set') . "</p>";
        
        // Get teachers using direct DB query (like your pattern)
        $teachers = $this->db->table('users')
            ->where('status', 1)
            ->where('campus_id', $campus_id)
            ->limit(5)
            ->get()
            ->getResult();
        
        echo "<p>Found " . count($teachers) . " teachers</p>";
        
        foreach ($teachers as $teacher) {
            echo "<p>Teacher: {$teacher->first_name} {$teacher->last_name} (ID: {$teacher->id})</p>";
            
            // Check if QR exists
            $qr = $this->db->table('teacher_qr_codes')
                ->where('teacher_id', $teacher->id)
                ->get()
                ->getRow();
                
            if ($qr) {
                echo "<p style='color:green'>✓ QR Code exists: {$qr->qr_code}</p>";
            } else {
                echo "<p style='color:orange'>✗ No QR code</p>";
                
                // Generate one
                $qr_string = 'TCHR_' . $teacher->id . '_' . md5($teacher->email . time());
                
                $this->db->table('teacher_qr_codes')->insert([
                    'teacher_id' => $teacher->id,
                    'qr_code' => $qr_string,
                    'campus_id' => $teacher->campus_id ?? 0
                ]);
                
                echo "<p style='color:green'>✓ Generated: {$qr_string}</p>";
            }
        }
        
        echo "<p><a href='" . site_url('admin/qr/generate-all') . "'>Try admin route</a></p>";
        exit;
    }

    public function generate($teacher_id = null)
    {
        helper('server_helper');
        
        try {
            // Log the attempt
            log_message('debug', 'QR Generate started. Teacher ID: ' . ($teacher_id ?? 'all'));
            
            // Get school info and session data - EXACTLY like your working pattern
            $schoolinfo = getSchoolInfo();
            $campus_id = $this->session->get('member_campusid');
            $session_id = $this->session->get('member_sessionid');
            $user_id = $this->session->get('user_id');
            
            log_message('debug', 'School ID: ' . ($schoolinfo->system_id ?? 'null'));
            log_message('debug', 'User ID: ' . ($user_id ?? 'null') . ', Campus ID: ' . ($campus_id ?? 'null') . ', Session ID: ' . ($session_id ?? 'null'));
            
            if (!$campus_id) {
                log_message('error', 'No campus ID in session');
                // Dump session for debugging
                log_message('debug', 'Session data: ' . print_r($_SESSION, true));
                return redirect()->to('admin/users')->with('error', 'No campus assigned to your account');
            }
            
            // Get teachers using direct DB query (like your add() method)
            if ($teacher_id) {
                $teachers = $this->db->table('users')
                    ->where('id', $teacher_id)
                    ->where('campus_id', $campus_id)
                    ->where('status', 1)
                    ->get()
                    ->getResult();
                    
                log_message('debug', 'Single teacher query result count: ' . count($teachers));
            } else {
                $teachers = $this->db->table('users')
                    ->where('status', 1)
                    ->where('campus_id', $campus_id)
                    ->get()
                    ->getResult();
                    
                log_message('debug', 'Found ' . count($teachers) . ' teachers');
            }
            
            if (empty($teachers)) {
                log_message('error', 'No teachers found for campus: ' . $campus_id);
                return redirect()->back()->with('error', 'No teachers found for this campus');
            }
            
            $generated = [];
            $skipped = [];
            
            foreach ($teachers as $teacher) {
                log_message('debug', 'Processing teacher ID: ' . $teacher->id);
                
                try {
                    // Check if QR exists using direct DB query
                    $existing = $this->db->table('teacher_qr_codes')
                        ->where('teacher_id', $teacher->id)
                        ->get()
                        ->getRow();
                    
                    if (!$existing) {
                        $qr_string = 'TCHR_' . $teacher->id . '_' . md5($teacher->email . time() . rand(1000, 9999));
                        
                        $insertData = [
                            'teacher_id' => $teacher->id,
                            'qr_code' => $qr_string,
                            'campus_id' => $teacher->campus_id,
                            'generated_at' => date('Y-m-d H:i:s'),
                            'is_active' => 1
                        ];
                        
                        log_message('debug', 'Inserting QR: ' . print_r($insertData, true));
                        
                        if ($this->db->table('teacher_qr_codes')->insert($insertData)) {
                            $generated[] = $teacher->first_name . ' ' . $teacher->last_name;
                            log_message('debug', 'Successfully inserted QR for teacher: ' . $teacher->id);
                        } else {
                            log_message('error', 'Failed to insert QR for teacher: ' . $teacher->id);
                            log_message('error', 'DB Error: ' . print_r($this->db->error(), true));
                        }
                    } else {
                        $skipped[] = $teacher->first_name . ' ' . $teacher->last_name;
                        log_message('debug', 'QR already exists for teacher: ' . $teacher->id . ' - ' . $existing->qr_code);
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Exception for teacher ' . $teacher->id . ': ' . $e->getMessage());
                }
            }
            
            $message = 'QR codes generated for: ' . implode(', ', $generated);
            if (!empty($skipped)) {
                $message .= '. Skipped (already exist): ' . implode(', ', $skipped);
            }
            
            log_message('debug', 'Generation complete. ' . $message);
            
            // Redirect to users list with message
            return redirect()->to('admin/users')->with('message', $message);
            
        } catch (\Exception $e) {
            log_message('error', 'Fatal error in generate: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return redirect()->to('admin/users')->with('error', 'Error: ' . $e->getMessage());
        }
    }
    

    public function view($teacher_id)
{
    helper('server_helper');
    
    try {
        $db = \Config\Database::connect();
        $campus_id = $this->session->get('member_campusid') ?? $this->session->get('campus_id');
        
        // Get teacher
        $teacher = $db->table('users')
            ->where('id', $teacher_id)
            ->where('campus_id', $campus_id)
            ->get()
            ->getRow();
        
        if (!$teacher) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Teacher not found');
        }
        
        // Get QR data
        $qrData = $db->table('teacher_qr_codes')
            ->where('teacher_id', $teacher_id)
            ->get()
            ->getRow();
        
        if (!$qrData) {
            $qr_string = 'TCHR_' . $teacher->id . '_' . md5($teacher->email . time());
            $db->table('teacher_qr_codes')->insert([
                'teacher_id' => $teacher->id,
                'qr_code' => $qr_string,
                'campus_id' => $teacher->campus_id
            ]);
            $qrData = $db->table('teacher_qr_codes')
                ->where('teacher_id', $teacher_id)
                ->get()
                ->getRow();
        }
        
        // Create QR code using constructor
        $qrCode = new \Endroid\QrCode\QrCode($qrData->qr_code);
        
        // Set options
        if (method_exists($qrCode, 'setSize')) {
            $qrCode->setSize(300);
        }
        if (method_exists($qrCode, 'setMargin')) {
            $qrCode->setMargin(10);
        }
        
        // Use PngWriter to output
        $writer = new \Endroid\QrCode\Writer\PngWriter();
        $result = $writer->write($qrCode);
        $imageData = $result->getString();
        
        return $this->response
            ->setHeader('Content-Type', 'image/png')
            ->setHeader('Content-Disposition', 'inline; filename="qr_' . $teacher->id . '.png"')
            ->setBody($imageData);
        
    } catch (\Exception $e) {
        log_message('error', 'QR Error: ' . $e->getMessage());
        return $this->response
            ->setHeader('Content-Type', 'text/html')
            ->setBody('<html><body style="text-align:center;padding:50px;">
                <h2>QR Code Error</h2>
                <p style="color:red">' . $e->getMessage() . '</p>
                <p>QR Code Value: ' . ($qrData->qr_code ?? 'N/A') . '</p>
                <p><a href="' . site_url('admin/users/view/' . $teacher_id . '?tab=qr') . '">Back to Profile</a></p>
            </body></html>');
    }
}


public function downloadAll()
{
    helper('server_helper');
    
    try {
        $db = \Config\Database::connect();
        $campus_id = $this->session->get('member_campusid') ?? $this->session->get('campus_id');
        
        if (!$campus_id) {
            return redirect()->back()->with('error', 'No campus ID found');
        }
        
        $teachers = $db->table('users')
            ->where('status', 1)
            ->where('campus_id', $campus_id)
            ->get()
            ->getResult();
        
        if (empty($teachers)) {
            return redirect()->back()->with('error', 'No teachers found');
        }
        
        $zip = new \ZipArchive();
        $zipFilename = WRITEPATH . 'uploads/qrcodes_' . date('Ymd_His') . '.zip';
        
        if (!is_dir(WRITEPATH . 'temp')) {
            mkdir(WRITEPATH . 'temp', 0777, true);
        }
        
        if ($zip->open($zipFilename, \ZipArchive::CREATE) !== TRUE) {
            return redirect()->back()->with('error', 'Cannot create zip file');
        }
        
        $count = 0;
        
        foreach ($teachers as $teacher) {
            $qrData = $db->table('teacher_qr_codes')
                ->where('teacher_id', $teacher->id)
                ->get()
                ->getRow();
            
            if ($qrData) {
                try {
                    // Create QR code using constructor
                    $qrCode = new \Endroid\QrCode\QrCode($qrData->qr_code);
                    
                    if (method_exists($qrCode, 'setSize')) {
                        $qrCode->setSize(300);
                    }
                    if (method_exists($qrCode, 'setMargin')) {
                        $qrCode->setMargin(10);
                    }
                    
                    $writer = new \Endroid\QrCode\Writer\PngWriter();
                    $result = $writer->write($qrCode);
                    
                    $tempFile = WRITEPATH . 'temp/qr_' . $teacher->id . '.png';
                    $result->saveToFile($tempFile);
                    
                    $filename = $teacher->first_name . '_' . $teacher->last_name . '_' . $teacher->id . '.png';
                    $zip->addFile($tempFile, $filename);
                    $count++;
                    
                } catch (\Exception $e) {
                    log_message('error', 'Failed for teacher ' . $teacher->id . ': ' . $e->getMessage());
                }
            }
        }
        
        $zip->close();
        array_map('unlink', glob(WRITEPATH . 'temp/qr_*.png'));
        
        if ($count == 0) {
            return redirect()->back()->with('error', 'No QR codes found');
        }
        
        return $this->response->download($zipFilename, null)->setFileName('teacher_qrcodes.zip');
        
    } catch (\Exception $e) {
        log_message('error', 'DownloadAll error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }
}   

   public function print($teacher_id)
{
    helper('server_helper');
    
    try {
        $db = \Config\Database::connect();
        $campus_id = session()->get('member_campusid') ?? session()->get('campus_id');
        
        $teacher = $db->table('users')
            ->where('id', $teacher_id)
            ->where('campus_id', $campus_id)
            ->get()
            ->getRow();
        
        if (!$teacher) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Teacher not found');
        }
        
        $qrData = $db->table('teacher_qr_codes')
            ->where('teacher_id', $teacher_id)
            ->get()
            ->getRow();
        
        if (!$qrData) {
            return redirect()->back()->with('error', 'QR code not generated yet');
        }
        
        // Get school info for the print view
        $schoolinfo = getSchoolInfo();
        
        $data = [
            'teacher' => $teacher,
            'qr_code' => $qrData->qr_code,
            'qr_image' => site_url('admin/qr/view/' . $teacher_id),  // This will use the view method
            'school_name' => $schoolinfo->system_name ?? 'School Name'
        ];
        
        return view('admin/teacher_qr/print', $data);
        
    } catch (\Exception $e) {
        log_message('error', 'Error in print: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }
}
}