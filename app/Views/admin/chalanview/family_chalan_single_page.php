<!DOCTYPE html>
<html dir="<?= session('member_language') == 'ur' ? 'rtl' : 'ltr' ?>" lang="<?= session('member_language') ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <title>Family Fee Challan - Single Page (3 Families)</title>
    <?php include 'chalan_print_styles.php'; ?>
    <style>
        /* Family-specific styles - only essential overrides */
        .family-row {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 20px;
            gap: 15px;
        }
        
        .family-col {
            width: 32%;
            flex: 1;
            page-break-inside: avoid;
        }
        
        /* Family head student style */
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
        
        /* Student name container */
        .student-name-container {
            width: 100%;
            white-space: normal !important;
            word-wrap: break-word !important;
            line-height: 1.4;
            padding: 8px 5px;
        }
        
        /* Class badge style */
        .class-badge {
            font-size: 11px;
            font-weight: normal;
            color: #666;
            background: #f0f0f0;
            padding: 2px 5px;
            border-radius: 3px;
            margin-left: 5px;
            display: inline-block;
        }
        
        /* Payment History Section */
        .payment-history-section {
            margin-top: 15px;
            border-top: 2px solid #333;
            padding-top: 10px;
            page-break-inside: avoid;
        }
        
        .payment-history-title {
            font-weight: bold;
            margin-bottom: 8px;
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
        
        /* Adjust table column widths */
        .feetable colgroup col.particulars { 
            width: 50% !important;
        }
        
        .feetable colgroup col.amount { 
            width: 25% !important;
        }
        
        .feetable colgroup col.discount { 
            width: 25% !important;
        }
        
        /* Ensure amount and discount cells can accommodate larger numbers */
        .feetable td.amount-cell,
        .feetable td.discount-cell,
        .feetable td:nth-child(2),
        .feetable td:nth-child(3) {
            white-space: nowrap;
            overflow: visible;
            text-overflow: clip;
            min-width: 70px;
            padding-left: 8px;
            padding-right: 8px;
        }
        
        /* Logo size - matching student chalan */
        .header-logo img {
            max-width: 64px;
            max-height: auto;
        }
        
        /* Font sizes - matching student chalan */
        .header-line.school {
            font-size: 20px;
        }
        
        .header-line.campus {
            font-size: 16px;
        }
        
        .header-line.bank,
        .header-line.acc {
            font-size: 14px;
        }
        
        .info-row {
            font-size: 14px;
        }
        
        .info-row.auto-height {
            height: auto !important;
            min-height: 34px;
            line-height: 1.4 !important;
            padding: 8px 5px !important;
            white-space: normal !important;
        }
        
        .feetable th,
        .feetable td {
            font-size: 14px;
        }
        
        .copy-label {
            font-size: 14px;
        }
        
        .footer-msg {
            font-size: 14px;
        }
        
        <?php if ($show_discount): ?>
        .feetable colgroup col.discount { 
            width: 25% !important; 
        }
        <?php endif; ?>
        
        /* Ensure proper alignment based on direction */
        .feetable th,
        .feetable td {
            text-align: <?= session('member_language') == 'ur' ? 'right' : 'left' ?>;
        }
        
        .feetable td.amount-cell,
        .feetable td.discount-cell {
            text-align: <?= session('member_language') == 'ur' ? 'left' : 'right' ?>;
            direction: ltr;
        }
        
        /* Print optimization */
        @media print {
            .family-row {
                page-break-inside: avoid;
            }
            .pagebreak {
                page-break-before: always;
            }
        }
        
        /* Remove duplicate family ID */
        .meta-row .family-id-duplicate {
            display: none;
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
    </style>
</head>
<body>
    <?php if (!empty($families)): ?>
        <?php 
        // Split families into groups of 3 for each page
        $familyGroups = array_chunk($families, 3);
        foreach ($familyGroups as $groupIndex => $familyGroup): 
        ?>
            <?php if ($groupIndex > 0): ?>
                <div class="pagebreak"></div>
            <?php endif; ?>
            
            <div class="family-row">
                <?php foreach ($familyGroup as $family): ?>
                    <div class="family-col">
                        <?php
                        // Get system name with fallback
                        $system_name = $family['system_name'] ?? '';
                        if (empty($system_name)) {
                            $system_name = $family['campus_name'] ?? 'School Name';
                        }
                        
                        $logo = $family['logo'] ?? '';
                        
                        // Get students array (already sorted by controller - descending class order)
                        $sortedStudents = $family['students'] ?? [];
                        
                        // Get head student (elder student - first in sorted list)
                        $headStudent = !empty($sortedStudents) ? $sortedStudents[0] : null;
                        
                        // Get other students (excluding head)
                        $otherStudents = !empty($sortedStudents) ? array_slice($sortedStudents, 1) : [];
                        
                        // Get the elder student's class for display in class field
                        $elderClass = $headStudent ? ($headStudent['class_name'] ?? '') : '';
                        $elderClassSection = $headStudent ? ($headStudent['section_short_name'] ?? '') : '';
                        
                        // Format class display for class field (only elder student's class)
                        $classDisplay = '';
                        if (!empty($elderClass)) {
                            $classDisplay = $elderClass;
                            if (!empty($elderClassSection)) {
                                $classDisplay .= ' ' . $elderClassSection;
                            }
                        }
                        
                        // Calculate total payable
                        $totalPayable = $family['total_payable'] ?? 0;
                        $totalDiscount = $family['total_discount'] ?? 0;
                        $totalAmount = $totalPayable + $totalDiscount;
                        
                        // Get latest fee month for display
                        $feeMonthDisplay = $family['fee_month_display'] ?? 'All Months';
                        if (empty($fee_month) && !empty($family['fee_by_particular'])) {
                            $latestMonth = '';
                            foreach ($family['fee_by_particular'] as $fee) {
                                if (!empty($fee['month_display'])) {
                                    $latestMonth = $fee['month_display'];
                                    break;
                                }
                            }
                            if (!empty($latestMonth)) {
                                $feeMonthDisplay = $latestMonth;
                            }
                        }
                        
                        // Format head student name with class in brackets
                        $formattedHeadStudent = '';
                        if ($headStudent) {
                            $headName = $headStudent['student_name'] ?? '';
                            $headClass = $headStudent['class_name'] ?? '';
                            $headSection = $headStudent['section_short_name'] ?? '';
                            
                            $classPart = '';
                            if (!empty($headClass)) {
                                $classPart = $headClass;
                                if (!empty($headSection)) {
                                    $classPart .= ' ' . $headSection;
                                }
                            }
                            
                            $formattedHeadStudent = $headName;
                            if (!empty($classPart)) {
                                $formattedHeadStudent .= ' <span class="class-badge">(' . $classPart . ')</span>';
                            }
                        }
                        
                        // Format other students with their individual classes in brackets
                        $formattedOtherStudents = [];
                        foreach ($otherStudents as $student) {
                            $studentName = $student['student_name'] ?? '';
                            $studentClass = $student['class_name'] ?? '';
                            $studentSection = $student['section_short_name'] ?? '';
                            
                            $classPart = '';
                            if (!empty($studentClass)) {
                                $classPart = $studentClass;
                                if (!empty($studentSection)) {
                                    $classPart .= ' ' . $studentSection;
                                }
                            }
                            
                            $formattedStudent = $studentName;
                            if (!empty($classPart)) {
                                $formattedStudent .= ' (' . $classPart . ')';
                            }
                            $formattedOtherStudents[] = $formattedStudent;
                        }
                        
                        // Create a student-like array with ALL required fields
                        $familyStudent = [
                            // School info
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
                            
                            // Chalan info
                            'last_chalan_id' => $family['chalan_no'] ?? 'N/A',
                            'last_issue_date' => $family['issue_date'] ?? date('d-m-y'),
                            'last_due_date' => $family['due_date'] ?? date('d-m-y', strtotime('+10 days')),
                            'last_fee_month' => $feeMonthDisplay,
                            
                            // Family info
                            'reg_no' => 'Fam: ' . ($family['parent_id'] ?? ''),
                            'parent_id' => $family['parent_id'] ?? '',
                            'student_name' => $formattedHeadStudent,
                            'f_name' => $family['f_name'] ?? '',
                            
                            // Class info - Show ONLY elder student's class
                            'class_name' => $classDisplay,
                            'section_short_name' => '',
                            
                            // Fee data
                            'display_rows' => $family['display_rows'] ?? [],
                            'total_payable' => $totalPayable,
                            'total_discount' => $totalDiscount,
                            'total_amount' => $totalAmount,
                            
                            // Payment history
                            'payment_history' => $family['payment_history'] ?? ['monthly_totals' => []],
                            
                            // Additional family data
                            'head_student' => $headStudent,
                            'other_students' => $otherStudents,
                            'formatted_other_students' => $formattedOtherStudents,
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
                            'single_copy' => true,
                            'hide_father_name' => true,
                            'is_family' => true,
                            'head_student' => $headStudent,
                            'other_students' => $otherStudents,
                            'formatted_other_students' => $formattedOtherStudents,
                            'elder_class_display' => $classDisplay,
                            'show_payment_history' => $show_payment_history ?? false,
                            'payment_history' => $family['payment_history'] ?? ['monthly_totals' => []]
                        ];
                        
                        echo view('admin/chalanview/partials/chalan_template', $data);
                        ?>
                    </div>
                <?php endforeach; ?>
                
                <?php 
                // Fill empty columns if less than 3 families
                $remaining = 3 - count($familyGroup);
                for ($i = 0; $i < $remaining; $i++): 
                ?>
                    <div class="family-col"></div>
                <?php endfor; ?>
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
        }
    </script>
</body>
</html>