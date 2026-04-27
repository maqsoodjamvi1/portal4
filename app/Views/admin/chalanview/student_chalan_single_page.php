<!DOCTYPE html>
<html dir="<?= session('member_language') == 'ur' ? 'rtl' : 'ltr' ?>" lang="<?= session('member_language') ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <title>Student Fee Challan - Single Page (3 Students)</title>
    <?php include 'chalan_print_styles.php'; ?>
    <style>
        /* Any view-specific styles can go here */
        <?php if ($show_discount): ?>
        .feetable colgroup col.discount { 
            width: 20%; 
        }
        <?php endif; ?>
        
        /* Remove any extra spacing */
        .student-row {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 20px;
            gap: 15px;
        }
        
        .student-col {
            width: 32%;
            flex: 1;
            page-break-inside: avoid;
        }
        
        /* Ensure chalanwrapper has proper spacing */
        .chalanwrapper {
            margin-top: 0;
        }
        
        /* Payment History Section - Compact for student single page */
        .payment-history-section {
            margin-top: 8px;
            border-top: 2px solid #333;
            padding-top: 6px;
            page-break-inside: avoid;
        }
        
        .payment-history-title {
            font-weight: bold;
            margin-bottom: 4px;
            font-size: 10px;
        }
        
        .payment-history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            border: 1px solid #999;
        }
        
        .payment-history-table th {
            background: #e0e0e0;
            font-weight: bold;
            text-align: center;
            padding: 2px;
            border: 1px solid #999;
            white-space: nowrap;
        }
        
        .payment-history-table td {
            text-align: right;
            padding: 2px;
            border: 1px solid #999;
            direction: ltr;
        }
        
        .payment-history-table td:first-child {
            text-align: left;
            font-weight: 500;
            background: #fafafa;
        }
    </style>
</head>
<body>
    <?php if (!empty($students)): ?>
        <?php 
        // In landscape, we can fit 2 rows of 3 students = 6 students per page
        $chunks = array_chunk($students, 6);
        foreach ($chunks as $chunkIndex => $studentChunk): 
        ?>
            <?php if ($chunkIndex > 0): ?>
                <div class="pagebreak"></div>
            <?php endif; ?>
            
            <?php 
            // Split into rows of 3 students each
            $rows = array_chunk($studentChunk, 3);
            foreach ($rows as $row): 
            ?>
                <div class="student-row">
                    <?php foreach ($row as $student): ?>
                        <div class="student-col">
                            <?php 
                            // Get student payment history if available
                            $studentPaymentHistory = [];
                            if (isset($student['payment_history'])) {
                                $studentPaymentHistory = $student['payment_history'];
                            }
                            
                            $data = [
                                'student' => $student,
                                'show_discount' => $show_discount,
                                'fine_after_due_date' => $fine_after_due_date ?? 0,
                                'footer_line1' => $footer_line1 ?? '',
                                'footer_line2' => $footer_line2 ?? '',
                                'show_line1' => $show_line1 ?? 0,
                                'show_line2' => $show_line2 ?? 0,
                                'fee_month' => $fee_month ?? '',
                                'single_copy' => true,
                                'is_family' => false,
                                'show_payment_history' => $show_payment_history ?? false,
                                'payment_history' => $studentPaymentHistory
                            ];
                            echo view('admin/chalanview/partials/chalan_template', $data);
                            ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php 
                    // Fill empty columns if less than 3 students in this row
                    $remaining = 3 - count($row);
                    for ($i = 0; $i < $remaining; $i++): 
                    ?>
                        <div class="student-col"></div>
                    <?php endfor; ?>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 50px;">
            <h3>No fee challans found for the selected criteria</h3>
            <p class="no-print">Please try different filters.</p>
        </div>
    <?php endif; ?>
    
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>