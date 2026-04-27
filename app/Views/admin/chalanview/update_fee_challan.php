<?php
// Views/admin/chalanviews/update_fee_challan.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $fee_id = $_POST['fee_id'] ?? 0;
    $amount = $_POST['amount'] ?? 0;
    $due_date = $_POST['due_date'] ?? '';
    $status = $_POST['status'] ?? 'pending';
    
    // Database connection
    $conn = new mysqli('localhost', 'username', 'password', 'database');
    
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Connection failed']);
        exit;
    }
    
    // Update query - adjust table name and fields as needed
    $query = "UPDATE fee_chanlans SET 
              amount = ?, 
              due_date = ?, 
              status = ? 
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('dssi', $amount, $due_date, $status, $fee_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>