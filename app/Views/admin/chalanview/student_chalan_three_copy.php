<!DOCTYPE html>
<html dir="<?= session('member_language') == 'ur' ? 'rtl' : 'ltr' ?>" lang="<?= session('member_language') ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <title>Student Fee Challan - 3 Copies (A4 Landscape)</title>
    <meta name="<?= esc(csrf_token()) ?>" content="<?= esc(csrf_hash()) ?>" id="csrf-meta-print-chalan">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <?php include 'chalan_print_styles.php'; ?>
    <style>
        /* Any view-specific styles can go here */
        <?php if ($show_discount): ?>
        .feetable colgroup col.discount { 
            width: 20%; 
        }
        <?php endif; ?>
        
        /* Ensure proper spacing */
        .slip-row {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 8px;
            gap: 8px;
            position: relative;
        }
        
        .slip-col {
            width: 32%;
            flex: 1;
            page-break-inside: avoid;
        }
        
        /* Style for copy label inside chalan */
        .copy-label-inside {
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            padding: 2px 0;
            border-bottom: 1px dashed #999;
            margin-bottom: 5px;
            color: #333;
        }
        
        /* Payment History Section - Compact for student view */
        .payment-history-section {
            margin-top: 10px;
            border-top: 2px solid #333;
            padding-top: 8px;
            page-break-inside: avoid;
        }
        
        .payment-history-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 11px;
        }
        
        .payment-history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            border: 1px solid #999;
        }
        
        .payment-history-table th {
            background: #e0e0e0;
            font-weight: bold;
            text-align: center;
            padding: 3px;
            border: 1px solid #999;
            white-space: nowrap;
        }
        
        .payment-history-table td {
            text-align: right;
            padding: 3px;
            border: 1px solid #999;
            direction: ltr;
        }
        
        .payment-history-table td:first-child {
            text-align: left;
            font-weight: 500;
            background: #fafafa;
        }

        @media print {
            .slip-row {
                margin-bottom: 0;
                gap: 2px;
            }
        }
    </style>
</head>
<body class="chalan-preview-a4">
    <?php if (!empty($students)): ?>
        <div class="no-print" style="padding:8px 12px;background:#f5f5f5;border-bottom:1px solid #ddd;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">Print</button>
            <span class="text-muted small">Use <strong>Edit fees</strong> on each slip to change amounts or add lines, then print.</span>
        </div>
        <?php foreach ($students as $student): ?>
            <div class="chalan-slip-page">
                <div class="slip-row" data-student-id="<?= (int) ($student['student_id'] ?? 0) ?>">
                    <button type="button"
                            class="no-print btn btn-sm btn-light border chalan-edit-fees-btn"
                            style="position:absolute;top:2px;right:6px;z-index:10;font-size:11px;padding:2px 8px;"
                            data-student-id="<?= (int) ($student['student_id'] ?? 0) ?>"
                            data-parent-id="0">Edit fees</button>
                    <?php foreach (['Bank Copy', 'School Copy', 'Student Copy'] as $copyType): ?>
                        <div class="slip-col">
                            <?php
                            $studentPaymentHistory = [];
                            if (isset($student['payment_history'])) {
                                $studentPaymentHistory = $student['payment_history'];
                            } elseif (isset($student['student_payment_history'])) {
                                $studentPaymentHistory = $student['student_payment_history'];
                            }

                            $studentWithCopy            = $student;
                            $studentWithCopy['copy_label'] = $copyType;

                            $data = [
                                'student' => $studentWithCopy,
                                'show_discount' => $show_discount,
                                'fine_after_due_date' => $fine_after_due_date ?? 0,
                                'footer_line1' => $footer_line1 ?? '',
                                'footer_line2' => $footer_line2 ?? '',
                                'show_line1' => $show_line1 ?? 0,
                                'show_line2' => $show_line2 ?? 0,
                                'fee_month' => $fee_month ?? '',
                                'show_copy_label' => true,
                                'show_payment_history' => $show_payment_history ?? false,
                                'payment_history' => $studentPaymentHistory,
                                'is_family' => false,
                            ];
                            echo view('admin/chalanview/partials/chalan_template', $data);
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 50px;">
            <h3>No fee challans found for the selected criteria</h3>
            <p class="no-print">Please try different filters.</p>
        </div>
    <?php endif; ?>
    <?php if (! empty($students)): ?>
        <?php include __DIR__ . '/partials/chalan_print_edit_modal.php'; ?>
    <?php endif; ?>
</body>
</html>