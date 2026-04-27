<?php
// test_view_direct.php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('APPPATH', FCPATH . 'app' . DIRECTORY_SEPARATOR);

// Disable ALL output buffering
while (ob_get_level()) ob_end_clean();

// No CodeIgniter, just pure PHP
echo "<!DOCTYPE html><html><body>";
echo "<h1>Direct View Test</h1>";

// Include the view file directly
$viewFile = APPPATH . 'Views/admin/quizzes/print_all_key.php';

if (!file_exists($viewFile)) {
    die("View file not found: " . $viewFile);
}

echo "View file exists. Contents:<hr>";
echo "<pre>" . htmlspecialchars(file_get_contents($viewFile)) . "</pre>";
echo "<hr>Trying to include view...<br>";

// Include the view with test data
$quiz = (object)['title' => 'Test'];
$questions = [];

// Capture any errors during include
ob_start();
try {
    include $viewFile;
    $content = ob_get_clean();
    echo "View included successfully!<hr>";
    echo $content;
} catch (Throwable $e) {
    ob_end_clean();
    echo "<h2>FATAL ERROR IN VIEW:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";