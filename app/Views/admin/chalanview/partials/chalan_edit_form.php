<?php
$scope            = $scope ?? 'student';
$headline         = $headline ?? '';
$student          = $student ?? [];
$parent_id        = (int) ($parent_id ?? 0);
$chalans          = $chalans ?? [];
$fee_types        = $fee_types ?? [];
$family_students  = $family_students ?? [];
$isFamily         = $scope === 'family';

$feeMonthInput = static function (?string $fm): string {
    $fm = trim((string) $fm);
    if ($fm === '') {
        return date('Y-m');
    }
    if (preg_match('/^(\d{4})-(\d{2})/', $fm, $m)) {
        return $m[1] . '-' . $m[2];
    }
    if (preg_match('/^(\d{1,2})[\/\-](\d{4})$/', $fm, $m)) {
        return sprintf('%04d-%02d', (int) $m[2], (int) $m[1]);
    }

    return date('Y-m');
};

$defaultStudentId = $isFamily && ! empty($family_students)
    ? (int) $family_students[0]['student_id']
    : (int) ($student['student_id'] ?? 0);
?>
<div class="container-fluid">
    <h6 class="mb-3"><?= esc($headline) ?></h6>

    <form id="chalan-edit-form">
        <?= csrf_field() ?>
        <input type="hidden" name="edit_scope" value="<?= esc($scope) ?>">
        <input type="hidden" name="student_id" value="<?= $isFamily ? 0 : (int) ($student['student_id'] ?? 0) ?>">
        <input type="hidden" name="parent_id" value="<?= $parent_id ?>">

        <div class="mb-2 d-flex flex-wrap align-items-center">
            <button type="button" class="btn btn-sm btn-primary me-2 mb-1" id="chalan-add-row">+ Add new fee item</button>
            <span class="text-muted small">Pick student (family) and fee type: <strong>Amount</strong> comes from class fee setup. For <strong>monthly</strong> fee types, <strong>discount</strong> is the student monthly discount multiplied by fee plan units (same rules as bulk challan).</span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <?php if ($isFamily): ?>
                            <th>Student</th>
                        <?php endif; ?>
                        <th>ID</th>
                        <th>Fee type</th>
                        <th>Fee month</th>
                        <th>Issue</th>
                        <th>Due</th>
                        <th>Amount</th>
                        <th>Discount</th>
                        <th>Net</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chalans as $chalan):
                        $sid = (int) ($chalan['line_student_id'] ?? $chalan['student_id'] ?? 0);
                        $issueVal = ! empty($chalan['issue_date']) ? date('Y-m-d', strtotime((string) $chalan['issue_date'])) : date('Y-m-d');
                        $dueVal   = ! empty($chalan['due_date']) ? date('Y-m-d', strtotime((string) $chalan['due_date'])) : date('Y-m-d');
                        $ftId     = (int) ($chalan['fee_type_id'] ?? 0);
                        ?>
                        <tr class="chalan-data-row">
                            <?php if ($isFamily): ?>
                                <td>
                                    <?= esc(trim(($chalan['first_name'] ?? '') . ' ' . ($chalan['last_name'] ?? ''))) ?>
                                    <small class="text-muted"><?= esc($chalan['reg_no'] ?? '') ?></small>
                                    <input type="hidden" name="line_student_id[]" value="<?= $sid ?>">
                                </td>
                            <?php endif; ?>
                            <td>
                                <?= (int) ($chalan['chalan_id'] ?? 0) ?>
                                <input type="hidden" name="chalan_id[]" value="<?= (int) ($chalan['chalan_id'] ?? 0) ?>">
                                <?php if (! $isFamily): ?>
                                    <input type="hidden" name="line_student_id[]" value="<?= $sid ?>">
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= esc($chalan['fee_type_name'] ?? 'N/A') ?>
                                <input type="hidden" name="fee_type_id[]" value="<?= $ftId ?>">
                            </td>
                            <td>
                                <input type="month" class="form-control form-control-sm" name="fee_month[]"
                                       value="<?= esc($feeMonthInput($chalan['fee_month'] ?? '')) ?>" required>
                            </td>
                            <td>
                                <input type="date" class="form-control form-control-sm" name="issue_date[]"
                                       value="<?= esc($issueVal) ?>" required>
                            </td>
                            <td>
                                <input type="date" class="form-control form-control-sm" name="due_date[]"
                                       value="<?= esc($dueVal) ?>" required>
                            </td>
                            <td>
                                <input type="number" name="amount[]" class="form-control form-control-sm amount-input"
                                       value="<?= esc($chalan['amount'] ?? '0') ?>" step="0.01" min="0" required>
                            </td>
                            <td>
                                <input type="number" name="discount[]" class="form-control form-control-sm discount-input"
                                       value="<?= esc($chalan['discount'] ?? '0') ?>" step="0.01" min="0" required>
                            </td>
                            <td class="net-amount-cell text-end">0.00</td>
                            <td>
                                <select name="status[]" class="form-control form-control-sm">
                                    <?php $st = (string) ($chalan['status'] ?? 'unpaid'); ?>
                                    <option value="unpaid" <?= $st === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                                    <option value="paid" <?= $st === 'paid' ? 'selected' : '' ?>>Paid</option>
                                    <option value="discounted" <?= $st === 'discounted' ? 'selected' : '' ?>>Discounted</option>
                                </select>
                            </td>
                            <td></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php
                    /* Template row: disabled so it is not submitted (avoids ghost chalan_id=0 + empty fee type). Clone enables fields in JS. */
                    $tplDisabled = ' disabled';
                    ?>
                    <tr class="chalan-new-template d-none">
                        <?php if ($isFamily): ?>
                            <td>
                                <select name="line_student_id[]" class="form-control form-control-sm chalan-student-select" required<?= $tplDisabled ?>>
                                    <?php foreach ($family_students as $fs):
                                        $optId = (int) $fs['student_id'];
                                        $optLb = trim(($fs['first_name'] ?? '') . ' ' . ($fs['last_name'] ?? '')) . ' (' . ($fs['reg_no'] ?? '') . ')';
                                        ?>
                                        <option value="<?= $optId ?>" <?= $optId === $defaultStudentId ? 'selected' : '' ?>><?= esc($optLb) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        <?php endif; ?>
                        <td>
                            <span class="text-muted">New</span>
                            <input type="hidden" name="chalan_id[]" value="0"<?= $tplDisabled ?>>
                            <?php if (! $isFamily): ?>
                                <input type="hidden" name="line_student_id[]" value="<?= $defaultStudentId ?>"<?= $tplDisabled ?>>
                            <?php endif; ?>
                        </td>
                        <td>
                            <select name="fee_type_id[]" class="form-control form-control-sm chalan-fee-type-select" required<?= $tplDisabled ?>>
                                <option value="" data-is-monthly="0">— Fee type —</option>
                                <?php foreach ($fee_types as $ft):
                                    $fid = (int) ($ft['fee_type_id'] ?? 0);
                                    $fn  = (string) ($ft['fee_type_name'] ?? '');
                                    $mo  = ((int) ($ft['is_monthly_fee'] ?? 0) === 1) ? '1' : '0';
                                    ?>
                                    <option value="<?= $fid ?>" data-is-monthly="<?= $mo ?>"><?= esc($fn) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="month" class="form-control form-control-sm" name="fee_month[]"
                                   value="<?= esc(date('Y-m')) ?>" required<?= $tplDisabled ?>>
                        </td>
                        <td>
                            <input type="date" class="form-control form-control-sm" name="issue_date[]"
                                   value="<?= esc(date('Y-m-d')) ?>" required<?= $tplDisabled ?>>
                        </td>
                        <td>
                            <input type="date" class="form-control form-control-sm" name="due_date[]"
                                   value="<?= esc(date('Y-m-d', strtotime('+10 days'))) ?>" required<?= $tplDisabled ?>>
                        </td>
                        <td>
                            <input type="number" name="amount[]" class="form-control form-control-sm amount-input"
                                   value="0" step="0.01" min="0" required<?= $tplDisabled ?>>
                        </td>
                        <td>
                            <input type="number" name="discount[]" class="form-control form-control-sm discount-input"
                                   value="0" step="0.01" min="0" required<?= $tplDisabled ?>>
                        </td>
                        <td class="net-amount-cell text-end">0.00</td>
                        <td>
                            <select name="status[]" class="form-control form-control-sm"<?= $tplDisabled ?>>
                                <option value="unpaid" selected>Unpaid</option>
                                <option value="paid">Paid</option>
                                <option value="discounted">Discounted</option>
                            </select>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger chalan-remove-row" title="Remove">&times;</button>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-info">
                    <tr>
                        <th colspan="<?= $isFamily ? 6 : 5 ?>" class="text-end">Totals:</th>
                        <th id="total-amount">0</th>
                        <th id="total-discount">0</th>
                        <th id="total-net">0</th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if (empty($fee_types)): ?>
            <div class="alert alert-warning mt-2">No fee types found for this campus system. You cannot add new lines until fee types exist.</div>
        <?php endif; ?>

        <div class="alert alert-info mt-3 mb-0">
            Only unpaid lines appear here. Save, then reload the print page to refresh amounts.
        </div>
    </form>
</div>
