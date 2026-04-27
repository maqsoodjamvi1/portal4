<?php
// Save as test_ci_session_data.php in your public folder
// Bootstrap CodeIgniter to access its session

// Find the index.php file (adjust path as needed)
$paths = [
    '../index.php',
    '../../index.php',
    '../../../index.php',
    '../../public/index.php'
];

$indexFile = null;
foreach($paths as $path) {
    if(file_exists($path)) {
        $indexFile = $path;
        break;
    }
}

if($indexFile) {
    // Bootstrap CodeIgniter
    require $indexFile;
    
    // Now use CodeIgniter's session
    $session = session();
    
    echo "<h2>CodeIgniter Session Data</h2>";
    echo "<p><strong>Session ID:</strong> " . $session->getSessionId() . "</p>";
    
    $allData = $session->get();
    echo "<h3>All Session Data:</h3>";
    echo "<pre>";
    print_r($allData);
    echo "</pre>";
    
    // Check specific keys
    $keys = [
        'user_id',
        'member_id',
        'emp_id',
        'id',
        'member_campusid',
        'member_reg_text',
        'member_termid',
        'member_name',
        'member_email',
        'member_username'
    ];
    
    echo "<h3>Key Session Values:</h3>";
    foreach($keys as $key) {
        $value = $session->get($key);
        if($value !== null) {
            echo "<p style='color:green'>✅ <strong>$key:</strong> " . print_r($value, true) . "</p>";
        } else {
            echo "<p style='color:red'>❌ <strong>$key:</strong> NOT SET</p>";
        }
    }
    
    // Check session configuration
    echo "<h3>Session Configuration:</h3>";
    echo "<pre>";
    echo "Session Driver: " . (config('App')->sessionDriver ?? 'Not set') . "\n";
    echo "Session Save Path: " . (config('App')->sessionSavePath ?? 'Not set') . "\n";
    echo "Session Cookie Name: " . (config('App')->sessionCookieName ?? 'Not set') . "\n";
    echo "</pre>";
    
    // Check if session files exist in CodeIgniter's save path
    $savePath = config('App')->sessionSavePath ?? null;
    if($savePath && strpos($savePath, 'WRITEPATH') !== false) {
        $savePath = str_replace('WRITEPATH', WRITEPATH, $savePath);
        echo "<h3>Session Files in: $savePath</h3>";
        if(is_dir($savePath)) {
            $files = glob($savePath . "/*");
            if(empty($files)) {
                echo "<p>No session files found</p>";
            } else {
                echo "<pre>";
                print_r($files);
                echo "</pre>";
            }
        } else {
            echo "<p style='color:red'>Session directory does not exist!</p>";
        }
    }
    
} else {
    echo "<p>Could not find CodeIgniter index.php</p>";
    echo "<p>Current directory: " . __DIR__ . "</p>";
    echo "<p>Please adjust the path in the script</p>";
}
?>