<?php
// Ensure variables are defined
$is_family = $is_family ?? true;
$show_discount = $show_discount ?? true;
$fine_after_due_date = $fine_after_due_date ?? 0;
$footer_line1 = $footer_line1 ?? '';
$footer_line2 = $footer_line2 ?? '';
$show_line1 = $show_line1 ?? 0;
$show_line2 = $show_line2 ?? 0;
$fee_month = $fee_month ?? '';
$show_payment_history = $show_payment_history ?? false;
?>
<!DOCTYPE html>
<html dir="<?= session('member_language') == 'ur' ? 'rtl' : 'ltr' ?>" lang="<?= session('member_language') ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <title>Family Fee Challan - 3 Copies</title>
    <?php include 'chalan_print_styles.php'; ?>
    <style>
        /* Family-specific styles */
        .family-head-student {
            font-weight: bold;
            color: #0066cc;
            font-size: 14px;
            display: block;
            margin-bottom: 3px;
        }
        
        .family-other-students {
            font-size: 12px;
            color: #555;
            display: block;
            line-height: 1.4;
            padding-left: 10px;
            border-left: 2px solid #ddd;
            margin-top: 3px;
            white-space: normal;
            word-wrap: break-word;
        }
        
        .family-other-students span {
            display: inline-block;
            margin-right: 8px;
            white-space: normal;
        }
        
        .student-name-container {
            width: 100%;
            white-space: normal !important;
            word-wrap: break-word !important;
            line-height: 1.4;
            padding: 8px 5px;
        }
        
        .class-badge {
            font-size: 11px;
            font-weight: normal;
            color: #666;
            background: #f0f0f0;
            padding: 2px 5px;
            border-radius: 3px;
            margin-left: 5px;
            display: inline-block;
            white-space: nowrap;
        }
        
        /* Payment History Section - Always visible */
        .payment-history-section {
            margin-top: 15px;
            border-top: 2px solid #333;
            padding-top: 10px;
            page-break-inside: avoid;
        }
        
        .payment-history-title {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 12px;
        }
        
        .payment-history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            border: 1px solid #999;
        }
        
        .payment-history-table th {
            background: #e0e0e0;
            font-weight: bold;
            text-align: center;
            padding: 4px;
            border: 1px solid #999;
            white-space: nowrap;
        }
        
        .payment-history-table td {
            text-align: right;
            padding: 4px;
            border: 1px solid #999;
            direction: ltr;
        }
        
        .payment-history-table td:first-child {
            text-align: left;
            font-weight: 500;
            background: #fafafa;
        }
        
        .payment-history-table .total-row {
            font-weight: bold;
            background: #d0e0f0;
        }
        
        .payment-history-table .total-row td {
            border-top: 2px solid #666;
        }
        
        <?php if ($show_discount): ?>
        .feetable colgroup col.discount { 
            width: 20%; 
        }
        <?php endif; ?>
        
        .feetable th,
        .feetable td {
            text-align: <?= session('member_language') == 'ur' ? 'right' : 'left' ?>;
        }
        
        .feetable td.amount-cell,
        .feetable td.discount-cell {
            text-align: <?= session('member_language') == 'ur' ? 'left' : 'right' ?>;
            direction: ltr;
        }
        
        .copy-label-inside {
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            padding: 2px 0;
            border-bottom: 1px dashed #999;
            margin-bottom: 5px;
            color: #333;
        }
        
        .info-row.auto-height {
            height: auto !important;
            min-height: 34px;
            line-height: 1.4 !important;
            padding: 8px 5px !important;
            white-space: normal !important;
        }
        
        .meta-row .family-id-duplicate {
            display: none;
        }
    </style>
</head>
<body>
    <?php if (!empty($families)): ?>
        <?php foreach ($families as $index => $family): ?>
            <?php if ($index > 0): ?>
                <div class="pagebreak"></div>
            <?php endif; ?>
            
            <div class="slip-row">
                <?php foreach (['Bank Copy', 'School Copy', 'Student Copy'] as $copyType): ?>
                    <div class="slip-col">
                        <?php
                        $system_name = $family['system_name'] ?? '';
                        if (empty($system_name)) {
                            $system_name = $family['campus_name'] ?? 'School Name';
                        }
                        
                        $logo = $family['logo'] ?? '';
                        $sortedStudents = $family['students'] ?? [];
                        $headStudent = !empty($sortedStudents) ? $sortedStudents[0] : null;
                        $otherStudents = !empty($sortedStudents) ? array_slice($sortedStudents, 1) : [];
                        
                        $elderClass = $headStudent ? ($headStudent['class_short_name'] ?? $headStudent['class_name'] ?? '') : '';
                        $elderSection = $headStudent ? ($headStudent['section_short_name'] ?? '') : '';
                        
                        $classDisplay = '';
                        if (!empty($elderClass)) {
                            $classDisplay = $elderClass;
                            if (!empty($elderSection)) {
                                $classDisplay .= $elderSection;
                            }
                        }
                        
                        $totalPayable = $family['total_payable'] ?? 0;
                        $totalDiscount = $family['total_discount'] ?? 0;
                        $totalAmount = $totalPayable + $totalDiscount;
                        $feeMonthDisplay = $family['fee_month_display'] ?? 'All Months';
                        
                        $getShortClass = function($student) {
                            if (!empty($student['class_short_name'])) {
                                return $student['class_short_name'];
                            }
                            $className = $student['class_name'] ?? '';
                            preg_match('/(\d+)/', $className, $matches);
                            return !empty($matches[1]) ? 'Grade ' . $matches[1] : $className;
                        };
                        
                        $formattedHeadStudent = '';
                        if ($headStudent) {
                            $headName = $headStudent['student_name'] ?? '';
                            $shortClass = $getShortClass($headStudent);
                            $shortSection = $headStudent['section_short_name'] ?? '';
                            
                            $classPart = $shortClass;
                            if (!empty($shortSection)) {
                                $classPart .= $shortSection;
                            }
                            
                            $formattedHeadStudent = $headName;
                            if (!empty($classPart)) {
                                $formattedHeadStudent .= ' <span class="class-badge">(' . $classPart . ')</span>';
                            }
                        }
                        
                        $formattedOtherStudents = [];
                        foreach ($otherStudents as $student) {
                            $studentName = $student['student_name'] ?? '';
                            $shortClass = $getShortClass($student);
                            $shortSection = $student['section_short_name'] ?? '';
                            
                            $classPart = $shortClass;
                            if (!empty($shortSection)) {
                                $classPart .= $shortSection;
                            }
                            
                            $formattedStudent = $studentName;
                            if (!empty($classPart)) {
                                $formattedStudent .= ' (' . $classPart . ')';
                            }
                            $formattedOtherStudents[] = $formattedStudent;
                        }
                        
                        $familyStudent = [
                            'logo' => $logo,
                            'system_name' => $system_name,
                            'campus_name' => $family['campus_name'] ?? '',
                            'location' => $family['location'] ?? '',
                            'bank_name' => $family['bank_name'] ?? '',
                            'bank_address' => $family['bank_address'] ?? '',
                            'bank_code' => $family['bank_code'] ?? '',
                            'bank_acc' => $family['bank_acc'] ?? '',
                            'chalan_f_msg' => $family['chalan_f_msg'] ?? '',
                            'late_fee_fine' => $family['late_fee_fine'] ?? '',
                            'fine_type' => $family['fine_type'] ?? '',
                            'last_chalan_id' => $family['chalan_no'] ?? 'N/A',
                            'last_issue_date' => $family['issue_date'] ?? date('d-m-y'),
                            'last_due_date' => $family['due_date'] ?? date('d-m-y', strtotime('+10 days')),
                            'last_fee_month' => $feeMonthDisplay,
                            'reg_no' => '',
                            'parent_id' => $family['parent_id'] ?? '',
                            'student_name' => $formattedHeadStudent,
                            'f_name' => $family['f_name'] ?? '',
                            'class_name' => $classDisplay,
                            'section_short_name' => '',
                            'display_rows' => $family['display_rows'] ?? [],
                            'total_payable' => $totalPayable,
                            'total_discount' => $totalDiscount,
                            'total_amount' => $totalAmount,
                            'payment_history' => $family['payment_history'] ?? ['monthly_totals' => []],
                            'copy_label' => $copyType,
                            'head_student' => $headStudent,
                            'other_students' => $otherStudents,
                            'formatted_other_students' => $formattedOtherStudents,
                            'formatted_head_student' => $formattedHeadStudent,
                            'elder_class_display' => $classDisplay
                        ];
                        
                       $data = [
    'student' => $familyStudent,
    'show_discount' => $show_discount,
    'fine_after_due_date' => $fine_after_due_date ?? 0,
    'footer_line1' => $footer_line1 ?? '',
    'footer_line2' => $footer_line2 ?? '',
    'show_line1' => $show_line1 ?? 0,
    'show_line2' => $show_line2 ?? 0,
    'fee_month' => $fee_month ?? '',
    'hide_father_name' => true,
    'show_copy_label' => true,
    'is_family' => true,
    'head_student' => $headStudent,
    'other_students' => $otherStudents,
    'formatted_other_students' => $formattedOtherStudents,
    'elder_class_display' => $classDisplay,
    'show_payment_history' => $show_payment_history ?? false,
    'payment_history' => $familyStudent['payment_history'] ?? [],
    // Add message parameters
    'message_text' => $message_text ?? '',
    'message_position' => $message_position ?? 'none'
];
                        
                        echo view('admin/chalanview/partials/chalan_template', $data);
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
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
        };
    </script>
</body>
</html>