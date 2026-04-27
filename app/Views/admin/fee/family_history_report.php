<!DOCTYPE html>
<html>
<head>
    <title>Family Fee History</title>
    <style>
        body { font-family: Arial, sans-serif; padding:20px; }
        h2 { text-align: center; }
        table { width:100%; border-collapse: collapse; margin-top:20px; }
        th, td { border:1px solid #ccc; padding:8px; text-align:left; }
        th { background:#f4f4f4; }
        .print-btn { margin-top:20px; text-align:center; }
    </style>
</head>
<body>
    <h2>Family Fee History</h2>

    <?php if (empty($records)): ?>
        <p>No fee history found for this family.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Fee Type</th>
                    <th>Amount (Rs)</th>
                    <th>Paid Date</th>
                    <th>Receipt #</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                    <tr>
                        <td><?= esc($row->student_name) ?></td>
                        <td><?= esc($row->fee_type_name) ?></td>
                        <td style="text-align:right;"><?= number_format($row->amount, 0) ?></td>
                        <td><?= esc($row->paid_date) ?></td>
                        <td><?= esc($row->receipt_no) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="print-btn">
            <button onclick="window.print()">?? Print</button>
        </div>
    <?php endif; ?>
</body>
</html>