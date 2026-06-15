<?php
/** @var array<string, mixed> $row */
$isPaid = ($row['existing_payment_status'] ?? '') === 'paid';
$rowClass = 'bulk-row';
if (! empty($row['has_existing_slip'])) {
    $rowClass .= $isPaid ? ' table-success' : ' table-warning';
}
$viewSlipUrl = ! empty($row['existing_slip_id'])
    ? base_url('admin/users/view-salary-slip/' . (int) $row['user_id'] . '/' . (int) $row['existing_slip_id'])
    : '';
?>
<tr class="<?= $rowClass ?>"
    data-user-id="<?= (int) $row['user_id'] ?>"
    data-slip-id="<?= (int) ($row['existing_slip_id'] ?? 0) ?>"
    data-payment-status="<?= esc($row['existing_payment_status'] ?? '') ?>"
    data-basic="<?= esc($row['basic_salary']) ?>"
    data-daily-salary="<?= esc($row['daily_salary'] ?? 0) ?>"
    data-apply-deduction="<?= (int) ($row['apply_deduction'] ?? 1) ?>"
    data-late-minutes-per="<?= (int) ($row['late_minutes_per_occurrence'] ?? 0) ?>"
    data-att-bonus="<?= esc($row['attendance_bonus']) ?>"
    data-other-bonus="<?= esc($row['other_bonus']) ?>"
    data-security="<?= esc($row['security_deduction']) ?>"
    data-advance="<?= esc($row['advance_deduction']) ?>">
    <td>
        <input type="checkbox" class="row-check"
            <?= $isPaid ? 'disabled title="Paid slips cannot be regenerated"' : 'checked' ?>>
    </td>
    <td>
        <strong><?= esc($row['name']) ?></strong>
        <?php if (! empty($row['designation'])): ?>
            <br><small class="text-muted"><?= esc($row['designation']) ?></small>
        <?php endif; ?>
    </td>
    <td class="text-end col-basic"><?= number_format((float) $row['basic_salary'], 2) ?></td>
    <td>
        <input type="number" min="0" step="1" class="form-control form-control-sm text-center count-input cnt-off"
               value="<?= (int) $row['off_days'] ?>" <?= $isPaid ? 'readonly' : '' ?>>
    </td>
    <td>
        <input type="number" min="0" step="1" class="form-control form-control-sm text-center count-input cnt-leave"
               value="<?= (int) $row['leave_days'] ?>" <?= $isPaid ? 'readonly' : '' ?>>
    </td>
    <td>
        <input type="number" min="0" step="1" class="form-control form-control-sm text-center count-input cnt-late"
               value="<?= (int) $row['late_count'] ?>" <?= $isPaid ? 'readonly' : '' ?>>
    </td>
    <td>
        <input type="number" min="0" step="1" class="form-control form-control-sm text-center count-input cnt-early"
               value="<?= (int) $row['early_left_count'] ?>" <?= $isPaid ? 'readonly' : '' ?>>
    </td>
    <td class="text-end total-deductions fw-bold text-danger">
        <?= number_format((float) ($row['total_full_deductions'] ?? $row['total_attendance_deductions'] ?? 0), 2) ?>
    </td>
    <td class="text-end net-salary fw-bold">
        <?= number_format((float) $row['net_salary'], 2) ?>
    </td>
    <td class="slip-status">
        <?php if ($isPaid): ?>
            <span class="badge text-bg-success">Paid</span>
        <?php elseif (! empty($row['has_existing_slip'])): ?>
            <span class="badge text-bg-warning">Generated</span>
        <?php else: ?>
            <span class="badge text-bg-secondary">Pending</span>
        <?php endif; ?>
        <?php if ($viewSlipUrl): ?>
            <br><a href="<?= $viewSlipUrl ?>" target="_blank" class="small">View slip</a>
        <?php endif; ?>
    </td>
</tr>
