cat > /var/www/portal4/html/salary_debug.php << 'EOF'
<?php
require 'vendor/autoload.php';

$db = \Config\Database::connect();

echo "<h2>Salary System Debug</h2>";

// Check tables
$tables = ['salary_heads', 'employee_salary_structure', 'salary_processing', 'salary_details'];
echo "<h3>Table Check:</h3>";
foreach ($tables as $table) {
    $exists = $db->table($table)->get()->getRow();
    echo "$table: " . ($exists !== null ? "? Exists" : "? Not found") . "<br>";
}

// Check salary heads
echo "<h3>Salary Heads:</h3>";
$heads = $db->table('salary_heads')->get()->getResult();
if ($heads) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Code</th><th>Name</th><th>Type</th></tr>";
    foreach ($heads as $h) {
        echo "<tr>";
        echo "<td>{$h->head_id}</td>";
        echo "<td>{$h->head_code}</td>";
        echo "<td>{$h->head_name}</td>";
        echo "<td>{$h->head_type}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "? No salary heads found!<br>";
}

// Check employee structure for ID 1860
echo "<h3>Employee Structure (ID 1860):</h3>";
$structure = $db->table('employee_salary_structure ess')
    ->select('ess.*, sh.head_name, sh.head_code')
    ->join('salary_heads sh', 'sh.head_id = ess.head_id')
    ->where('ess.emp_id', 1860)
    ->get()
    ->getResult();
    
if ($structure) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Head</th><th>Amount</th><th>Percentage</th><th>Effective From</th></tr>";
    foreach ($structure as $s) {
        echo "<tr>";
        echo "<td>{$s->head_name} ({$s->head_code})</td>";
        echo "<td>" . ($s->amount ?? '-') . "</td>";
        echo "<td>" . ($s->percentage ?? '-') . "</td>";
        echo "<td>{$s->effective_from}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "? No structure found for employee 1860<br>";
}

// Check attendance for March 2026
echo "<h3>Attendance for March 2026 (ID 1860):</h3>";
$attendance = $db->table('attendance_employee')
    ->where('emp_id', 1860)
    ->where('date >=', '2026-03-01')
    ->where('date <=', '2026-03-31')
    ->countAllResults();
echo "Total attendance records: $attendance<br>";

// Check processing table
echo "<h3>Salary Processing Records:</h3>";
$processing = $db->table('salary_processing')->get()->getResult();
if ($processing) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Employee</th><th>Month</th><th>Net Salary</th><th>Status</th></tr>";
    foreach ($processing as $p) {
        echo "<tr>";
        echo "<td>{$p->process_id}</td>";
        echo "<td>{$p->emp_id}</td>";
        echo "<td>{$p->salary_month}</td>";
        echo "<td>{$p->net_salary}</td>";
        echo "<td>{$p->status}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "? No salary processing records found<br>";
}
?>
EOF