<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QrTest extends BaseController
{
    public function index()
    {
        echo "<h2>QR Code Library Test</h2>";
        
        // Check if class exists
        if (class_exists('\\Endroid\\QrCode\\QrCode')) {
            echo "<p style='color:green'>? Endroid QR Code library is installed!</p>";
            
            try {
                // Create a simple QR code
                $qrCode = QrCode::create('https://yourschool.com');
                $writer = new PngWriter();
                $result = $writer->write($qrCode);
                
                // Convert to base64 for display
                $dataUri = $result->getDataUri();
                
                echo "<p>? QR Code generated successfully!</p>";
                echo "<img src='{$dataUri}' alt='Test QR Code' style='border:1px solid #ccc; padding:10px;'>";
                echo "<p><small>Version: 6.0.9 installed</small></p>";
                
            } catch (\Exception $e) {
                echo "<p style='color:red'>? Error generating QR: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color:red'>? QR Code library not found!</p>";
        }
    }
}