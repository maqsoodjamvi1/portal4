<?php
// Save as test_ci_session.php in /var/www/portal4/html/public/

// Correct path to CodeIgniter's index.php (it's in the same directory!)
// Since this file is in the public folder, index.php is right here
require __DIR__ . '/index.php';

// Now we're in CodeIgniter context
$session = session();

echo "<h2>CodeIgniter Session Data</h2>";
echo "<p><strong>Session ID:</strong> " . $session->getSessionId() . "</p>";
echo "<p><strong>Session Cookie Name:</strong> " . config('App')->sessionCookieName . "</p>";

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
    'teacher_id',
    'member_campusid',
    'campus_id',
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
        echo "<p style='color:green'>? <strong>$key:</strong> " . print_r($value, true) . "</p>";
    } else {
        echo "<p style='color:red'>? <strong>$key:</strong> NOT SET</p>";
    }
}

// Check session configuration
echo "<h3>Session Configuration:</h3>";
echo "<pre>";
echo "Session Driver: " . (config('App')->sessionDriver ?? 'Not set') . "\n";
echo "Session Save Path: " . (config('App')->sessionSavePath ?? 'Not set') . "\n";
echo "Session Cookie Name: " . (config('App')->sessionCookieName ?? 'Not set') . "\n";
echo "Session Expiration: " . (config('App')->sessionExpiration ?? 'Not set') . "\n";
echo "</pre>";

// Check writable/session directory
$writablePath = dirname(__DIR__) . '/writable';
$sessionPath = $writablePath . '/session';

echo "<h3>Session Directory Check:</h3>";
echo "<p><strong>Writable Path:</strong> " . $writablePath . "</p>";
echo "<p><strong>Session Path:</strong> " . $sessionPath . "</p>";

if(is_dir($sessionPath)) {
    echo "<p style='color:green'>? Session directory exists</p>";
    
    // Check permissions
    if(is_writable($sessionPath)) {
        echo "<p style='color:green'>? Session directory is writable</p>";
    } else {
        echo "<p style='color:red'>? Session directory is NOT writable</p>";
        echo "<p>Fix: <code>chmod 777 " . $sessionPath . "</code></p>";
    }
    
    // List session files
    $files = glob($sessionPath . "/ci_session*");
    if(empty($files)) {
        echo "<p>No session files found in " . $sessionPath . "</p>";
    } else {
        echo "<p>Found " . count($files) . " session files:</p>";
        echo "<ul>";
        foreach($files as $file) {
            echo "<li>" . basename($file) . " - " . filesize($file) . " bytes</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p style='color:red'>? Session directory does NOT exist</p>";
    echo "<p>Create it: <code>mkdir -p " . $sessionPath . " && chmod 777 " . $sessionPath . "</code></p>";
}

// Check PHP session vs CI session
echo "<h3>PHP Native Session vs CI Session:</h3>";
echo "<p><strong>PHP Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>PHP Session Data:</strong></p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>