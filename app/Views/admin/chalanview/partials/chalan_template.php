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
// Fixed layout: 4 particulars + 1 remainder row (5 body rows)
while (count($displayRows) < 5) {
    $displayRows[] = [
        'is_blank'          => true,
        'particulars_label' => '',
        'amount'            => '',
        'discount'          => '',
        'net_amount'        => 0,
        'fee_month_label'   => '',
    ];
}
$displayRows = array_slice($displayRows, 0, 5);

$totalPayable = $student['total_payable'] ?? 0;

// Ensure totalPayable is numeric
$totalPayable = floatval($totalPayable);
$fine_after_due_date = intval($fine_after_due_date);
$late_fee = isset($student['late_fee_fine']) ? floatval($student['late_fee_fine']) : 0;

$schoolNameDisplay = $student['system_name'] ?? 'SCHOOL NAME';
$schoolNameFontPt    = school_name_fit_font_size((string) $schoolNameDisplay, 22, 11.0, 6.5);

$accountsDisclaimerStd = 'If any mistakes are found in the challan, please contact the Accounts Office.';
$payableMonthly        = (float) ($student['payable_monthly'] ?? 0);
$payableOther          = (float) ($student['payable_other'] ?? 0);
if (($payableMonthly + $payableOther) <= 0 && ! empty($student['chalans'])) {
    foreach ($student['chalans'] as $c) {
        $n = (float) ($c['net_amount'] ?? 0);
        if ((int) ($c['is_monthly_fee'] ?? 0) === 1) {
            $payableMonthly += $n;
        } else {
            $payableOther += $n;
        }
    }
}
?>

<div class="chalan-wrapper">
    <!-- SECTION 1: HEADER — logo + school + campus in one balanced row -->
    <div class="chalan-header">
        <div class="header-brand">
            <div class="header-logo-box">
                <?php if (!empty($student['logo'])): ?>
                    <img src="<?= base_url('system-logo/' . $student['logo']) ?>" alt="Logo">
                <?php else: ?>
                    <div class="logo-placeholder">LOGO</div>
                <?php endif; ?>
            </div>
            <div class="header-brand-text">
                <div class="school-name" style="font-size: <?= esc((string) $schoolNameFontPt, 'attr') ?>pt;"><?= esc($schoolNameDisplay) ?></div>
                <div class="campus-line"><?= esc($student['campus_name'] ?? 'Campus Address') ?></div>
                <?php if (!empty($student['bank_name'])): ?>
                    <div class="bank-line"><?= esc($student['bank_name']) ?><?= !empty($student['bank_address']) ? ', ' . esc($student['bank_address']) : '' ?></div>
                <?php endif; ?>
                <?php if (!empty($student['bank_acc'])): ?>
                    <div class="acc-line">A/C: <?= esc($student['bank_acc']) ?></div>
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
                        <strong class="student-name-line"><?= esc($head_student['student_name'] ?? '') ?></strong>
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
                
                <!-- Row 4: Issue (left) · Due (center) · Month (right) -->
                <div class="info-row-dates-triple">
                    <div class="date-cell date-cell-left">
                        <span class="date-lbl">Iss:</span> <?= esc($issueDate) ?>
                    </div>
                    <div class="date-cell date-cell-center">
                        <span class="date-lbl">Due:</span> <span class="due-date"><?= esc($dueDate) ?></span>
                    </div>
                    <div class="date-cell date-cell-right">
                        <span class="date-lbl">Mo:</span> <?= esc($student['last_fee_month'] ?? $feeMonthLabel) ?>
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
                        <strong class="student-name-line"><?= esc($student['student_name'] ?? '') ?></strong>
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
                
                <!-- Row 3: Issue (left) · Due (center) · Month (right) -->
                <div class="info-row-dates-triple">
                    <div class="date-cell date-cell-left">
                        <span class="date-lbl">Iss:</span> <?= esc($issueDate) ?>
                    </div>
                    <div class="date-cell date-cell-center">
                        <span class="date-lbl">Due:</span> <span class="due-date"><?= esc($dueDate) ?></span>
                    </div>
                    <div class="date-cell date-cell-right">
                        <span class="date-lbl">Mo:</span> <?= esc($student['last_fee_month'] ?? $feeMonthLabel) ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- SECTION 3: FEE DETAIL TABLE — 5 rows (4 items + arrears) -->
    <div class="fee-detail-section">
        <div class="section-title">FEES</div>
        <table class="fee-table">
            <thead>
                <tr>
                    <th class="col-sr">#</th>
                    <th class="col-particulars">Item</th>
                    <?php if ($show_discount): ?>
                        <th class="col-amount">Amt</th>
                        <th class="col-discount">Disc</th>
                        <th class="col-payable">Net</th>
                    <?php else: ?>
                        <th class="col-payable-full">Payable</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($displayRows as $idx => $row):
                    $isBlank = ! empty($row['is_blank']);
                    $isAgg   = ! empty($row['is_arrears']) || ! empty($row['is_other']);
                    $sr      = (int) $idx + 1;

                    $particulars = $row['particulars_label'] ?? '';
                    $shortName   = $row['short_name'] ?? $row['particulars_short'] ?? '';
                    $displayParticulars = $shortName !== '' && $shortName !== null ? $shortName : $particulars;

                    $amount   = (float) ($row['amount'] ?? $row['total_amount'] ?? 0);
                    $discount = (float) ($row['discount'] ?? $row['total_discount'] ?? 0);
                    $payable  = $isBlank ? 0.0 : (($row['net_amount'] ?? null) !== null && $row['net_amount'] !== ''
                        ? (float) $row['net_amount']
                        : ($amount - $discount));

                    $trClass = $isBlank ? 'fee-detail-fixed blank-row' : 'fee-detail-fixed';
                    ?>
                    <tr class="<?= esc($trClass, 'attr') ?>">
                        <td class="text-center"><?= $sr ?></td>
                        <td class="particulars-cell">
                            <?php if (!$isBlank): ?>
                                <strong><?= esc($displayParticulars) ?></strong>
                                <?php if (!$isAgg && !empty($row['fee_month_label']) && empty($fee_month)): ?>
                                    <span class="fee-month-small">(<?= esc($row['fee_month_label']) ?>)</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <?php if ($show_discount): ?>
                            <td class="text-right"><?= $isBlank ? '' : number_format($amount, 0) . '/-' ?></td>
                            <td class="text-right"><?= $isBlank ? '' : ($discount > 0 ? number_format($discount, 0) . '/-' : '-') ?></td>
                            <td class="text-right payable-amount"><?= $isBlank ? '' : number_format($payable, 0) . '/-' ?></td>
                        <?php else: ?>
                            <td class="text-right payable-amount"><?= $isBlank ? '' : number_format($payable, 0) . '/-' ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- SECTION 4: FEE SUMMARY — three columns on one row -->
    <div class="fee-summary-section fee-summary-compact">
        <div class="summary-strip" role="group" aria-label="Fee totals">
            <div class="summary-col">
                <div class="summary-col-label">Monthly fee</div>
                <div class="summary-col-value">Rs. <?= number_format($payableMonthly, 0) ?>/-</div>
            </div>
            <div class="summary-col">
                <div class="summary-col-label">Other fee</div>
                <div class="summary-col-value">Rs. <?= number_format($payableOther, 0) ?>/-</div>
            </div>
            <div class="summary-col summary-col-total">
                <div class="summary-col-label">Total fee</div>
                <div class="summary-col-value summary-col-value-grand">Rs. <?= number_format($totalPayable, 0) ?>/-</div>
            </div>
        </div>
        
        <?php if ($fine_after_due_date === 1 && $late_fee > 0): ?>
            <?php
            if (isset($student['fine_type']) && $student['fine_type'] === 'per_day_fine') {
                $late_fee_total = $late_fee * 15;
            } else {
                $late_fee_total = $late_fee;
            }
            ?>
            <div class="summary-after-due">
                <span class="summary-after-due-label">Payable after due date</span>
                <span class="summary-after-due-value">Rs. <?= number_format($totalPayable + $late_fee_total, 0) ?>/-</span>
                <span class="fine-note">(incl. late fee Rs. <?= number_format($late_fee_total, 0) ?>/-)</span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Payment History (if enabled) — monthly vs other fee paid per month -->
    <?php if ($show_payment_history && isset($payment_history) && !empty($payment_history['month_keys'])): ?>
        <?php
        $allMonthKeys = $payment_history['month_keys'] ?? [];
        $mfByMonth    = $payment_history['monthly_fee_totals'] ?? [];
        $oByMonth     = $payment_history['other_fee_totals'] ?? [];
        $allMonthlyTotals = $payment_history['monthly_totals'] ?? [];
        sort($allMonthKeys);
        $latestSixMonthKeys = array_slice($allMonthKeys, -6, 6);

        $formattedMonths = [];
        foreach ($latestSixMonthKeys as $monthKey) {
            $formattedMonths[] = date('M y', strtotime($monthKey . '-01'));
        }

        $grandM = 0.0;
        $grandO = 0.0;
        foreach ($latestSixMonthKeys as $monthKey) {
            if ($mfByMonth !== [] || $oByMonth !== []) {
                $grandM += (float) ($mfByMonth[$monthKey] ?? 0);
                $grandO += (float) ($oByMonth[$monthKey] ?? 0);
            } else {
                $grandM += (float) ($allMonthlyTotals[$monthKey] ?? 0);
            }
        }
        $useSplit = $mfByMonth !== [] || $oByMonth !== [];
        $grandSumAll = $grandM + $grandO;
        ?>

        <div class="payment-history-section">
            <div class="section-title">PAYMENT HISTORY (Last 6 Months)</div>
            <table class="history-table">
                <thead>
                    <tr>
                        <th class="history-corner-cell"></th>
                        <?php foreach ($formattedMonths as $month): ?>
                            <th><?= esc($month) ?></th>
                        <?php endforeach; ?>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="history-label">Month</td>
                        <?php foreach ($latestSixMonthKeys as $monthKey):
                            $v = $useSplit ? (float) ($mfByMonth[$monthKey] ?? 0) : (float) ($allMonthlyTotals[$monthKey] ?? 0);
                            ?>
                            <td class="text-right"><?= $v > 0 ? number_format($v, 0) : '-' ?></td>
                        <?php endforeach; ?>
                        <td class="text-right total-amount"><?= number_format($useSplit ? $grandM : $grandM + $grandO, 0) ?></td>
                    </tr>
                    <tr>
                        <td class="history-label">Other</td>
                        <?php foreach ($latestSixMonthKeys as $monthKey):
                            $v = $useSplit ? (float) ($oByMonth[$monthKey] ?? 0) : 0.0;
                            ?>
                            <td class="text-right"><?= $v > 0 ? number_format($v, 0) : '-' ?></td>
                        <?php endforeach; ?>
                        <td class="text-right total-amount"><?= number_format($useSplit ? $grandO : 0, 0) ?></td>
                    </tr>
                    <tr class="history-row-sum">
                        <td class="history-label history-label-sum">Total</td>
                        <?php foreach ($latestSixMonthKeys as $monthKey):
                            if ($useSplit) {
                                $sumCol = (float) ($mfByMonth[$monthKey] ?? 0) + (float) ($oByMonth[$monthKey] ?? 0);
                            } else {
                                $sumCol = (float) ($allMonthlyTotals[$monthKey] ?? 0);
                            }
                            ?>
                            <td class="text-right history-sum-cell"><?= $sumCol > 0 ? number_format($sumCol, 0) : '-' ?></td>
                        <?php endforeach; ?>
                        <td class="text-right total-amount history-sum-cell"><?= number_format($grandSumAll, 0) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if ((int) $show_line1 === 1 && !empty($footer_line1) && !$single_copy): ?>
        <div class="footer-line"><?= esc($footer_line1) ?></div>
    <?php endif; ?>

    <?php if ((int) $show_line2 === 1 && !empty($footer_line2) && !$single_copy): ?>
        <div class="footer-line"><?= esc($footer_line2) ?></div>
    <?php endif; ?>

    <?php if ($show_copy_label && !empty($student['copy_label'])): ?>
        <div class="copy-label"><?= esc($student['copy_label']) ?></div>
    <?php endif; ?>

    <?php if (!$single_copy): ?>
        <?php
        $customFooter = trim((string) ($student['chalan_f_msg'] ?? ''));
        $footerNotice = $customFooter !== '' ? $customFooter : $accountsDisclaimerStd;
        ?>
        <div class="chalan-accounts-disclaimer slip-footer-msg"><?= esc($footerNotice) ?></div>
    <?php endif; ?>
</div>