<?php
/**
 * @var array $employees
 * @var int   $missing_timing_count
 * @var string $day_label
 */
$employees = $employees ?? [];
$missing_timing_count = (int) ($missing_timing_count ?? 0);
$dayLabel = esc($day_label ?? '');
?>

<?php if ($missing_timing_count > 0): ?>
    <div class="alert alert-info py-2 mb-3">
        <i class="fas fa-info-circle me-1"></i>
        <?= $missing_timing_count ?> employee(s) have no personal timing for <?= $dayLabel ?> — campus default hours are used.
        <a href="<?= base_url('admin/emp_timing/add') ?>" class="alert-link">Set employee timing</a>
    </div>
<?php endif; ?>

<?php if ($employees === []): ?>
    <div class="alert alert-danger mb-0">
        <i class="fas fa-users me-1"></i>
        No active employees found for this campus.
    </div>
<?php else: ?>

    <div class="emp-att-summary mb-3" id="emp-att-summary" aria-live="polite">
        <span class="badge text-bg-success emp-att-count" data-status="P">P: 0</span>
        <span class="badge text-bg-danger emp-att-count" data-status="A">A: 0</span>
        <span class="badge text-bg-warning emp-att-count" data-status="LC">LC: 0</span>
        <span class="badge text-bg-info emp-att-count" data-status="EL">EL: 0</span>
        <span class="badge text-bg-secondary emp-att-count" data-status="L">L: 0</span>
        <span class="badge text-bg-light border emp-att-count" data-status="_none">—: <?= count($employees) ?></span>
    </div>

    <div class="emp-att-list-head">
        <span>Employee</span>
        <span class="text-center">Status</span>
        <span>Check in (Late)</span>
        <span>Check out (Early)</span>
    </div>

    <div class="emp-att-list">
        <?php foreach ($employees as $emp):
            $eid = (int) $emp['id'];
            $status = (string) ($emp['status'] ?? '');
            $hasSaved = ! empty($emp['has_saved']);
            $showIn = $status === 'LC';
            $showOut = $status === 'EL';
            $showRemarks = in_array($status, ['A', 'L'], true);
        ?>
        <div class="emp-att-item emp-att-row"
             data-name="<?= esc(strtolower($emp['name'])) ?>"
             data-emp-id="<?= $eid ?>"
             data-has-saved="<?= $hasSaved ? '1' : '0' ?>"
             data-status="<?= esc($status) ?>">
            <input type="hidden" name="employee_id[]" value="<?= $eid ?>">

            <div class="emp-att-item-person">
                <img src="<?= esc($emp['photo_url']) ?>" alt="" class="emp-att-avatar">
                <div class="emp-att-item-meta">
                    <strong class="emp-att-name"><?= esc($emp['name']) ?></strong>
                    <?php if (! empty($emp['designation'])): ?>
                        <small class="text-muted d-block"><?= esc($emp['designation']) ?></small>
                    <?php endif; ?>
                    <small class="text-muted d-block">
                        Schedule<?= empty($emp['has_custom_timing']) ? ' (default)' : '' ?>:
                        <?= esc($emp['scheduled_in']) ?> – <?= esc($emp['scheduled_out']) ?>
                    </small>
                    <?php if ($hasSaved): ?>
                        <span class="badge text-bg-primary rounded-pill mt-1 emp-att-saved-badge">Saved</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="emp-att-item-status">
                <span class="d-md-none small text-muted fw-bold mb-1 d-block">Status</span>
                <?= view('admin/partials/employees_attendance_status_buttons', ['emp_id' => $eid, 'status' => $status]) ?>
                <div class="emp-att-remarks-wrap <?= $showRemarks ? '' : 'd-none' ?>">
                    <label class="small text-muted mb-0 mt-2"><?= $status === 'L' ? 'Leave note' : 'Remarks (required for absent)' ?></label>
                    <input type="text" name="<?= $eid ?>_remarks" value="<?= esc($emp['remarks'] ?? '') ?>"
                           class="form-control form-control-sm emp-att-remarks"
                           placeholder="<?= $status === 'L' ? 'Optional leave note…' : 'Reason for absence…' ?>"
                        <?= $status === 'A' ? 'required' : '' ?>>
                </div>
            </div>

            <div class="emp-att-item-time emp-att-time-in <?= $showIn ? '' : 'd-none' ?>">
                <label class="small text-muted mb-0">Late check-in</label>
                <input type="time" name="<?= $eid ?>_checkin_date" value="<?= esc($emp['checkin'] ?? '') ?>"
                       class="form-control form-control-sm emp-att-checkin">
            </div>

            <div class="emp-att-item-time emp-att-time-out <?= $showOut ? '' : 'd-none' ?>">
                <label class="small text-muted mb-0">Early check-out</label>
                <input type="time" name="<?= $eid ?>_checkout_date" value="<?= esc($emp['checkout'] ?? '') ?>"
                       class="form-control form-control-sm emp-att-checkout">
            </div>
        </div>
        <?php endforeach; ?>
    </div>

<?php endif; ?>
