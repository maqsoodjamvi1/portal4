<?php
// get_student_fee_details.php
header('Content-Type: application/json');

if (isset($_GET['student_id'])) {
    $studentId = $_GET['student_id'];
    
    // Database connection
    $conn = new mysqli('localhost', 'username', 'password', 'database');
    
    // Query to get fee details for the student
    $query = "SELECT f.*, s.name as student_name 
              FROM fees f 
              JOIN students s ON f.student_id = s.id 
              WHERE f.student_id = ? AND f.status != 'paid'
              ORDER BY f.due_date DESC 
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'feeDetails' => $row
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No fee records found'
        ]);
    }
    
    $stmt->close();
    $conn->close();
}
?>