<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Salary Slips — <?= date('F Y', mktime(0, 0, 0, (int) $month, 1, (int) $year)) ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; margin: 0; padding: 16px; }
        .no-print { margin-bottom: 16px; }
        .toolbar { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .toolbar button, .toolbar a {
            padding: 8px 14px; border: 1px solid #ccc; background: #f8f9fa;
            border-radius: 4px; text-decoration: none; color: #333; cursor: pointer;
        }
        .toolbar .primary { background: #007bff; color: #fff; border-color: #007bff; }
        .summary-bar {
            background: #f1f3f5; border: 1px solid #dee2e6; padding: 12px 16px;
            margin-bottom: 20px; border-radius: 4px;
        }
        .summary-bar span { margin-right: 24px; font-weight: bold; }
        .slip-page {
            border: 1px solid #ccc; padding: 20px; margin-bottom: 24px;
            page-break-inside: avoid;
        }
        .slip-header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 14px; }
        .slip-header h2 { margin: 0 0 4px; font-size: 18px; }
        .slip-header p { margin: 0; color: #666; font-size: 11px; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f8f9fa; }
        .text-end { text-align: right; }
        .net-row td { font-weight: bold; font-size: 14px; background: #eef6ff; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .slip-page { border: none; margin-bottom: 0; page-break-after: always; }
            .slip-page:last-child { page-break-after: auto; }
        }
    </style>
</head>
<body>
    <div class="no-print toolbar">
        <button type="button" class="primary" onclick="window.print()">Print All Slips</button>
        <a href="<?= base_url('admin/salary-settings/bulk-adjustment/export?year=' . (int) $year . '&month=' . (int) $month) ?>">Download CSV</a>
        <a href="<?= base_url('admin/salary-settings/bulk-adjustment?year=' . (int) $year . '&month=' . (int) $month) ?>">Back to Bulk Adjustment</a>
    </div>

    <?php if (empty($slips)): ?>
        <p>No salary slips generated for <?= esc(date('F Y', mktime(0, 0, 0, (int) $month, 1, (int) $year))) ?>.</p>
    <?php else: ?>
        <div class="summary-bar">
            <span>Period: <?= esc(date('F Y', mktime(0, 0, 0, (int) $month, 1, (int) $year))) ?></span>
            <span>Slips: <?= (int) ($totals->slip_count ?? count($slips)) ?></span>
            <span>Total Basic: <?= number_format((float) ($totals->total_basic ?? 0), 2) ?></span>
            <span>Total Deductions: <?= number_format((float) ($totals->total_deductions ?? 0), 2) ?></span>
            <span>Total Net: <?= number_format((float) ($totals->total_net ?? 0), 2) ?></span>
        </div>

        <?php foreach ($slips as $slip): ?>
            <?php
            $name = trim(($slip['first_name'] ?? '') . ' ' . ($slip['last_name'] ?? ''));
            $period = date('F Y', mktime(0, 0, 0, (int) $slip['month'], 1, (int) $slip['year']));
            ?>
            <div class="slip-page">
                <div class="slip-header">
                    <h2><?= esc($campus->campus_name ?? 'Salary Slip') ?></h2>
                    <p>Salary Slip — <?= esc($period) ?></p>
                </div>
                <div class="meta">
                    <div>
                        <strong><?= esc($name) ?></strong><br>
                        <?= esc($slip['designation'] ?? '') ?><br>
                        Slip No: <?= esc($slip['slip_no'] ?? '') ?>
                    </div>
                    <div class="text-end">
                        Status: <?= esc(ucfirst($slip['payment_status'] ?? 'pending')) ?><br>
                        Generated: <?= esc($slip['generated_date'] ?? '') ?>
                    </div>
                </div>
                <table>
                    <tr><th>Earnings</th><th class="text-end">Amount</th></tr>
                    <tr><td>Basic Salary</td><td class="text-end"><?= number_format((float) ($slip['basic_salary'] ?? 0), 2) ?></td></tr>
                    <?php if ((float) ($slip['attendance_bonus'] ?? 0) > 0): ?>
                    <tr><td>Attendance Bonus</td><td class="text-end"><?= number_format((float) $slip['attendance_bonus'], 2) ?></td></tr>
                    <?php endif; ?>
                    <?php if ((float) ($slip['other_bonus'] ?? 0) > 0): ?>
                    <tr><td>Other Bonus</td><td class="text-end"><?= number_format((float) $slip['other_bonus'], 2) ?></td></tr>
                    <?php endif; ?>
                    <tr><td><strong>Total Earnings</strong></td><td class="text-end"><strong><?= number_format((float) ($slip['total_earnings'] ?? 0), 2) ?></strong></td></tr>
                </table>
                <table>
                    <tr><th>Deductions</th><th class="text-end">Amount</th></tr>
                    <?php if ((float) ($slip['absent_deduction'] ?? 0) > 0): ?>
                    <tr><td>Off Days</td><td class="text-end"><?= number_format((float) $slip['absent_deduction'], 2) ?></td></tr>
                    <?php endif; ?>
                    <?php if ((float) ($slip['other_deduction'] ?? 0) > 0): ?>
                    <tr><td>Leave</td><td class="text-end"><?= number_format((float) $slip['other_deduction'], 2) ?></td></tr>
                    <?php endif; ?>
                    <?php if ((float) ($slip['late_deduction'] ?? 0) > 0): ?>
                    <tr><td>Late Coming</td><td class="text-end"><?= number_format((float) $slip['late_deduction'], 2) ?></td></tr>
                    <?php endif; ?>
                    <?php if ((float) ($slip['early_leave_deduction'] ?? 0) > 0): ?>
                    <tr><td>Early Leave</td><td class="text-end"><?= number_format((float) $slip['early_leave_deduction'], 2) ?></td></tr>
                    <?php endif; ?>
                    <?php if ((float) ($slip['security_deduction'] ?? 0) > 0): ?>
                    <tr><td>Security</td><td class="text-end"><?= number_format((float) $slip['security_deduction'], 2) ?></td></tr>
                    <?php endif; ?>
                    <?php if ((float) ($slip['advance_deduction'] ?? 0) > 0): ?>
                    <tr><td>Advance</td><td class="text-end"><?= number_format((float) $slip['advance_deduction'], 2) ?></td></tr>
                    <?php endif; ?>
                    <tr><td><strong>Total Deductions</strong></td><td class="text-end"><strong><?= number_format((float) ($slip['total_deductions'] ?? 0), 2) ?></strong></td></tr>
                </table>
                <table>
                    <tr class="net-row">
                        <td>Net Salary Payable</td>
                        <td class="text-end"><?= number_format((float) ($slip['net_salary'] ?? 0), 2) ?></td>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
