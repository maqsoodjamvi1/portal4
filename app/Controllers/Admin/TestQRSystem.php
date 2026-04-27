<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TeacherQrModel;
use App\Models\AttendanceEmployeeModel;

class TestQRSystem extends BaseController
{
    public function index()
    {
        echo "<h2>QR Code Attendance System Test</h2>";
        
        // Test UserModel
        $userModel = new UserModel();
        $teachers = $userModel->where('status', 1)->limit(5)->findAll();
        
        echo "<h3>? UserModel Working</h3>";
        echo "<p>Found " . count($teachers) . " active teachers</p>";
        
        // Test TeacherQrModel
        $qrModel = new TeacherQrModel();
        $qrCount = $qrModel->countAll();
        echo "<h3>? TeacherQrModel Working</h3>";
        echo "<p>QR codes generated: {$qrCount}</p>";
        
        // Test AttendanceEmployeeModel
        $attModel = new AttendanceEmployeeModel();
        $today = date('Y-m-d');
        $todayCount = $attModel->where('date', $today)->countAllResults();
        echo "<h3>? AttendanceEmployeeModel Working</h3>";
        echo "<p>Today's attendance: {$todayCount}</p>";
        
        // Test if Endroid QR is installed
        if (class_exists('\\Endroid\\QrCode\\QrCode')) {
            echo "<h3 style='color:green'>? Endroid QR Code Library Installed</h3>";
        } else {
            echo "<h3 style='color:red'>? Endroid QR Code Library Not Found</h3>";
        }
        
        echo "<br><br>";
        echo "<a href='" . base_url('admin/qr/generate-all') . "'>Generate QR Codes</a> | ";
        echo "<a href='" . base_url('admin/qr/download-all') . "'>Download All QR Codes</a> | ";
        echo "<a href='" . base_url('admin/attendance/scan') . "'>Go to Scanner</a>";
    }
}