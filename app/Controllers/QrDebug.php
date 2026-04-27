<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class QrDebug extends BaseController
{
    public function index()
    {
        helper('server_helper');
        
        echo "<h2>🔍 QR Code Debug Information</h2>";
        
        // Check Composer autoloader
        echo "<h3>1. Composer Autoloader</h3>";
        $autoloadPath = FCPATH . '../vendor/autoload.php';
        echo "Autoload path: " . realpath($autoloadPath) . "<br>";
        echo "File exists: " . (file_exists($autoloadPath) ? '✅ YES' : '❌ NO') . "<br>";
        
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
            echo "✅ Autoloader loaded<br>";
        }
        
        // Check QR Code package
        echo "<h3>2. QR Code Package</h3>";
        $classExists = class_exists('\\Endroid\\QrCode\\QrCode');
        echo "Endroid\\QrCode\\QrCode exists: " . ($classExists ? '✅ YES' : '❌ NO') . "<br>";
        
        if ($classExists) {
            $methods = get_class_methods('\\Endroid\\QrCode\\QrCode');
            echo "Available methods: <pre>" . print_r($methods, true) . "</pre>";
            
            // Test instance creation
            try {
                $qrCode = new \Endroid\QrCode\QrCode('test-data');
                echo "✅ Constructor works<br>";
                
                if (method_exists($qrCode, 'setSize')) {
                    $qrCode->setSize(200);
                    echo "✅ setSize() works<br>";
                }
                
                if (method_exists($qrCode, 'writeString')) {
                    $data = $qrCode->writeString();
                    echo "✅ writeString() works (" . strlen($data) . " bytes)<br>";
                }
            } catch (\Exception $e) {
                echo "❌ Error: " . $e->getMessage() . "<br>";
            }
        }
        
        // Check teacher 1860
        echo "<h3>3. Teacher 1860</h3>";
        $db = \Config\Database::connect();
        $teacher = $db->table('users')->where('id', 1860)->get()->getRow();
        if ($teacher) {
            echo "✅ Teacher found: {$teacher->first_name} {$teacher->last_name}<br>";
        } else {
            echo "❌ Teacher not found<br>";
        }
        
        echo "<p><a href='" . site_url('admin/qr/view/1860') . "'>Test QR View</a></p>";
    }
}