<?php
// Ensure all variables are defined with defaults
$student = $student ?? [];
$show_discount = $show_discount ?? true;
$fine_after_due_date = $fine_after_due_date ?? 0;
$footer_line1 = $footer_line1 ?? '';
$footer_line2 = $footer_line2 ?? '';
$show_line1 = $show_line1 ?? 0;
$show_line2 = $show_line2 ?? 0;
$fee_month = $fee_month ?? '';
$single_copy = $single_copy ?? false;
$hide_father_name = $hide_father_name ?? false;
$show_copy_label = $show_copy_label ?? false;
$is_family = $is_family ?? false;
$other_students = $other_students ?? [];
$head_student = $head_student ?? [];
$elder_class_display = $elder_class_display ?? '';
$show_payment_history = $show_payment_history ?? false;
$payment_history = $payment_history ?? [];

$chalan = $student['chalans'][0] ?? [];
$issueDate = $student['last_issue_date'] ?? ($chalan['issue_date_label'] ?? date('d-m-y'));
$dueDate = $student['last_due_date'] ?? ($chalan['due_date_label'] ?? date('d-m-y', strtotime('+10 days')));
$feeMonthLabel = $student['last_fee_month'] ?? ($chalan['fee_month_label'] ?? $fee_month);

$displayRows = $student['display_rows'] ?? $student['chalans'] ?? [];
$totalPayable = $student['total_payable'] ?? 0;

// Fixed number of rows in fee detail section
$FIXED_ROWS = 7;

// Show first 6 records as individual rows
$firstSixRows = array_slice($displayRows, 0, 6);
$remainingRows = array_slice($displayRows, 6);

// Calculate total amount for remaining rows (7th row - Arrears)
$arrearsTotal = 0;
foreach ($remainingRows as $row) {
    $amount = floatval($row['amount'] ?? $row['total_amount'] ?? 0);
    $discount = floatval($row['discount'] ?? $row['total_discount'] ?? 0);
    $arrearsTotal += ($amount - $discount);
}

// Calculate how many rows we currently have
$rowsWithData = count($firstSixRows);
$hasArrears = $arrearsTotal > 0;

// If we have arrears, it will be the 7th row
// If no arrears, we need blank rows to fill up to 7
$rowsNeeded = 7;
$totalDisplayRows = $rowsWithData + ($hasArrears ? 1 : 0);
$blankRowsNeeded = max(0, $rowsNeeded - $totalDisplayRows);

// Ensure totalPayable is numeric
$totalPayable = floatval($totalPayable);
$fine_after_due_date = intval($fine_after_due_date);
$late_fee = isset($student['late_fee_fine']) ? floatval($student['late_fee_fine']) : 0;
?>

<div class="chalan-wrapper">
    <!-- SECTION 1: HEADER SECTION -->
    <div class="chalan-header">
        <!-- Top Row: School Name Only - Full Width -->
        <div class="school-name-row">
            <div class="school-name"><?= esc($student['system_name'] ?? 'SCHOOL NAME') ?></div>
        </div>
        
        <!-- Middle Section: Logo and Campus Info Only -->
        <div class="header-middle">
            <div class="header-left">
                <div class="header-logo">
                    <?php if (!empty($student['logo'])): ?>
                        <img src="<?= base_url('system-logo/' . $student['logo']) ?>" alt="Logo">
                    <?php else: ?>
                        <div class="logo-placeholder">SCHOOL</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="header-center">
                <div class="campus-name"><?= esc($student['campus_name'] ?? 'Campus Address') ?></div>
                <?php if (!empty($student['bank_name'])): ?>
                    <div class="bank-details">
                        <?= esc($student['bank_name']) ?>
                        <?= !empty($student['bank_address']) ? ', ' . esc($student['bank_address']) : '' ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($student['bank_acc'])): ?>
                    <div class="account-details">A/C: <?= esc($student['bank_acc']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SECTION 2: STUDENT INFORMATION -->
    <div class="student-info-section">
        <div class="section-title">STUDENT INFORMATION</div>
        
        <?php if ($is_family): ?>
            <!-- FAMILY CHALAN LAYOUT -->
            <div class="info-grid-family">
                <!-- Row 1: Student Name and Class -->
                <div class="info-row-single">
                    <div class="info-label">Name:</div>
                    <div class="info-value left-align">
                        <strong><?= esc($head_student['student_name'] ?? '') ?></strong>
                        <?php 
                        $headClass = $head_student['class_short_name'] ?? $head_student['class_name'] ?? '';
                        $headSection = $head_student['section_short_name'] ?? '';
                        if (!empty($headClass)):
                        ?>
                            <span class="class-badge">(<?= esc($headClass) ?><?= !empty($headSection) ? ' ' . esc($headSection) : '' ?>)</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Row 2: Father Name (Full Width) and Family ID (Right Aligned) -->
                <div class="info-row-father">
                    <span class="info-label-inline">F Name:</span>
                    <span class="father-name-value"><?= esc($student['f_name'] ?? '') ?></span>
                    <span class="family-id-right">F ID: <?= esc($student['parent_id'] ?? '') ?></span>
                </div>
                
                <!-- Row 3: Other Students (blank if no other students) -->
                <div class="info-row-single">
                    <div class="info-label">Other:</div>
                    <div class="info-value left-align">
                        <?php if (!empty($other_students)): ?>
                            <?php 
                            $otherNames = [];
                            foreach ($other_students as $s) {
                                $name = $s['student_name'] ?? '';
                                $classShort = $s['class_short_name'] ?? $s['class_name'] ?? '';
                                $sectionShort = $s['section_short_name'] ?? '';
                                if (!empty($classShort)) {
                                    $name .= ' (' . esc($classShort);
                                    if (!empty($sectionShort)) {
                                        $name .= ' ' . esc($sectionShort);
                                    }
                                    $name .= ')';
                                }
                                $otherNames[] = $name;
                            }
                            echo esc(implode(', ', $otherNames));
                            ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Row 4: Issue Date, Due Date, Fee Month (all three in one row) -->
                <div class="info-row-triple">
                    <div class="info-item">
                        <span class="info-label-inline">Issue:</span>
                        <span class="info-value-inline"><?= esc($issueDate) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label-inline">Due:</span>
                        <span class="info-value-inline due-date"><?= esc($dueDate) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label-inline">Month:</span>
                        <span class="info-value-inline"><?= esc($student['last_fee_month'] ?? $feeMonthLabel) ?></span>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- INDIVIDUAL STUDENT CHALAN LAYOUT -->
            <div class="info-grid-student">
                <!-- Row 1: Student Name and Class -->
                <div class="info-row-single">
                    <div class="info-label">Name:</div>
                    <div class="info-value left-align">
                        <strong><?= esc($student['student_name'] ?? '') ?></strong>
                        <?php 
                        $studentClass = $student['class_short_name'] ?? $student['class_name'] ?? '';
                        $studentSection = $student['section_short_name'] ?? '';
                        if (!empty($studentClass)):
                        ?>
                            <span class="class-badge">(<?= esc($studentClass) ?><?= !empty($studentSection) ? ' ' . esc($studentSection) : '' ?>)</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Row 2: Father Name (Full Width) and Family ID (Right Aligned) -->
                <div class="info-row-father">
                    <span class="info-label-inline">F Name:</span>
                    <span class="father-name-value"><?= esc($student['f_name'] ?? '') ?></span>
                    <span class="family-id-right">F ID: <?= esc($student['parent_id'] ?? '') ?></span>
                </div>
                
                <!-- Row 3: Issue Date, Due Date, Fee Month (all three in one row) -->
                <div class="info-row-triple">
                    <div class="info-item">
                        <span class="info-label-inline">Issue:</span>
                        <span class="info-value-inline"><?= esc($issueDate) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label-inline">Due:</span>
                        <span class="info-value-inline due-date"><?= esc($dueDate) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label-inline">Month:</span>
                        <span class="info-value-inline"><?= esc($student['last_fee_month'] ?? $feeMonthLabel) ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- SECTION 3: FEE DETAIL TABLE - EXACTLY 7 ROWS -->
    <div class="fee-detail-section">
        <div class="section-title">FEE DETAILS</div>
        <table class="fee-table">
            <thead>
                <tr>
                    <th class="col-sr">#</th>
                    <th class="col-particulars">PARTICULARS</th>
                    <?php if ($show_discount): ?>
                        <th class="col-amount">AMOUNT (Rs.)</th>
                        <th class="col-discount">DISCOUNT (Rs.)</th>
                        <th class="col-payable">PAYABLE (Rs.)</th>
                    <?php else: ?>
                        <th class="col-payable-full">AMOUNT PAYABLE (Rs.)</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sr = 1;
                $displayedRows = 0;
                
                // Display first 6 fee rows
                foreach ($firstSixRows as $row): 
                    if (!empty($row['is_blank'])) continue;
                    
                    // Get short name for fee type
                    $particulars = $row['particulars_label'] ?? '';
                    $shortName = $row['short_name'] ?? $row['particulars_short'] ?? '';
                    
                    // Use short name if available, otherwise use full name
                    $displayParticulars = !empty($shortName) ? $shortName : $particulars;
                    
                    // Convert to float to avoid string arithmetic errors
                    $amount = floatval($row['amount'] ?? $row['total_amount'] ?? 0);
                    $discount = floatval($row['discount'] ?? $row['total_discount'] ?? 0);
                    $payable = $amount - $discount;
                    $displayedRows++;
                ?>
                    <tr>
                        <td class="text-center"><?= $sr++ ?></td>
                        <td class="particulars-cell">
                            <strong><?= esc($displayParticulars) ?></strong>
                            <?php if (!empty($row['fee_month_label']) && empty($fee_month)): ?>
                                <span class="fee-month-small">(<?= esc($row['fee_month_label']) ?>)</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($show_discount): ?>
                            <td class="text-right"><?= number_format($amount, 0) ?>/-</td>
                            <td class="text-right"><?= $discount > 0 ? number_format($discount, 0) . '/-' : '-' ?></td>
                            <td class="text-right payable-amount"><?= number_format($payable, 0) ?>/-</td>
                        <?php else: ?>
                            <td class="text-right payable-amount"><?= number_format($payable, 0) ?>/-</td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                
                <!-- 7th Row: Arrears (if there are remaining rows) -->
                <?php if ($arrearsTotal > 0): ?>
                    <tr>
                        <td class="text-center"><?= $sr++ ?></td>
                        <td class="particulars-cell">
                            <strong>Arrears</strong>
                            <?php if (count($remainingRows) > 0): ?>
                                <span class="fee-month-small">(<?= count($remainingRows) ?> items)</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($show_discount): ?>
                            <td class="text-right"><?= number_format($arrearsTotal, 0) ?>/-</td>
                            <td class="text-right">-</td>
                            <td class="text-right payable-amount"><?= number_format($arrearsTotal, 0) ?>/-</td>
                        <?php else: ?>
                            <td class="text-right payable-amount"><?= number_format($arrearsTotal, 0) ?>/-</td>
                        <?php endif; ?>
                    </tr>
                    <?php $displayedRows++; ?>
                <?php endif; ?>
                
                <!-- Add blank rows to reach exactly 7 rows (completely empty) -->
                <?php for ($i = 0; $i < $blankRowsNeeded; $i++): ?>
                    <tr class="blank-row">
                        <td class="text-center"></td>
                        <td class="particulars-cell"></td>
                        <?php if ($show_discount): ?>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                        <?php else: ?>
                            <td class="text-right"></td>
                        <?php endif; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>

    <!-- SECTION 4: FEE SUMMARY - Only Payable Block -->
    <div class="fee-summary-section">
        <div class="summary-item total">
            <div class="summary-label">TOTAL PAYABLE AMOUNT</div>
            <div class="summary-value">Rs. <?= number_format($totalPayable, 0) ?>/-</div>
        </div>
        
        <?php if ($fine_after_due_date === 1 && $late_fee > 0): ?>
            <?php 
            // Calculate late fee based on type
            if (isset($student['fine_type']) && $student['fine_type'] === 'per_day_fine') {
                $late_fee_total = $late_fee * 15;
            } else {
                $late_fee_total = $late_fee;
            }
            ?>
            <div class="summary-item warning-total">
                <div class="summary-label">PAYABLE AFTER DUE DATE</div>
                <div class="summary-value">Rs. <?= number_format($totalPayable + $late_fee_total, 0) ?>/-</div>
                <div class="fine-note">(Including Late Fee: Rs. <?= number_format($late_fee_total, 0) ?>/-)</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Payment History (if enabled) -->
    <?php if ($show_payment_history && isset($payment_history) && !empty($payment_history['monthly_totals'])): ?>
        <?php
        $allMonthKeys = $payment_history['month_keys'] ?? [];
        $allMonthlyTotals = $payment_history['monthly_totals'] ?? [];
        sort($allMonthKeys);
        $latestSixMonthKeys = array_slice($allMonthKeys, -6, 6);
        
        $formattedMonths = [];
        foreach ($latestSixMonthKeys as $monthKey) {
            $timestamp = strtotime($monthKey . '-01');
            $formattedMonths[] = date('M y', $timestamp);
        }
        
        $grandTotal = 0;
        foreach ($latestSixMonthKeys as $monthKey) {
            $grandTotal += floatval($allMonthlyTotals[$monthKey] ?? 0);
        }
        ?>
        
        <div class="payment-history-section">
            <div class="section-title">PAYMENT HISTORY (Last 6 Months)</div>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <?php foreach ($formattedMonths as $month): ?>
                            <th><?= esc($month) ?></th>
                        <?php endforeach; ?>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="history-label">Amount Paid</td>
                        <?php foreach ($latestSixMonthKeys as $monthKey): 
                            $amount = floatval($allMonthlyTotals[$monthKey] ?? 0);
                        ?>
                            <td class="text-right"><?= $amount > 0 ? number_format($amount, 0) : '-' ?></td>
                        <?php endforeach; ?>
                        <td class="text-right total-amount"><?= number_format($grandTotal, 0) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Footer Messages -->
    <?php if (!empty($student['chalan_f_msg']) && !$single_copy): ?>
        <div class="footer-note"><?= esc($student['chalan_f_msg']) ?></div>
    <?php endif; ?>
    
    <?php if ((int)$show_line1 === 1 && !empty($footer_line1) && !$single_copy): ?>
        <div class="footer-line"><?= esc($footer_line1) ?></div>
    <?php endif; ?>
    
    <?php if ((int)$show_line2 === 1 && !empty($footer_line2) && !$single_copy): ?>
        <div class="footer-line"><?= esc($footer_line2) ?></div>
    <?php endif; ?>
    
    <!-- Copy Label -->
    <?php if ($show_copy_label && !empty($student['copy_label'])): ?>
        <div class="copy-label"><?= esc($student['copy_label']) ?></div>
    <?php endif; ?>
</div>