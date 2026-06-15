<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Salary Settings',
    'icon' => 'fas fa-cog',
    'actionsHtml' => '<div class="text-sm-right">'
        . '<a href="' . esc(base_url('admin/salary-settings/bulk-adjustment'), 'attr') . '" class="btn btn-success btn-sm me-1">'
        . '<i class="fas fa-users-cog me-1"></i> Bulk Adjustment</a>'
        . '<button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#generateModal">'
        . '<i class="fas fa-calculator me-1"></i> Generate Monthly Salary</button></div>',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Salary Settings', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="container-fluid">
        <form action="<?= base_url('admin/salary-settings/save') ?>" method="post">
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="card sms-card">
                        <div class="card-header">
                            <h3 class="card-title">Deduction Rules</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Deduction Type</label>
                                <select name="deduction_type" class="form-control">
                                    <option value="per_day_salary" <?= isset($settings->deduction_type) && $settings->deduction_type == 'per_day_salary' ? 'selected' : '' ?>>Per Day Salary</option>
                                    <option value="fixed_amount" <?= isset($settings->deduction_type) && $settings->deduction_type == 'fixed_amount' ? 'selected' : '' ?>>Fixed Amount Per Day</option>
                                    <option value="percentage" <?= isset($settings->deduction_type) && $settings->deduction_type == 'percentage' ? 'selected' : '' ?>>Percentage of Salary</option>
                                </select>
                            </div>

                            <div class="form-group deduction-fields" id="deduction_amount_field">
                                <label>Deduction Amount (per day)</label>
                                <input type="number" step="0.01" class="form-control" name="deduction_per_day_amount"
                                       value="<?= $settings->deduction_per_day_amount ?? '' ?>"
                                       placeholder="Enter amount">
                            </div>

                            <div class="form-group deduction-fields" id="deduction_percentage_field" style="display:none">
                                <label>Deduction Percentage (%)</label>
                                <input type="number" step="0.01" class="form-control" name="deduction_per_day_percentage"
                                       value="<?= $settings->deduction_per_day_percentage ?? '' ?>"
                                       placeholder="Enter percentage">
                            </div>

                            <div class="form-group">
                                <label>Working Days Per Month</label>
                                <input type="number" class="form-control" name="working_days_per_month"
                                       value="<?= $settings->working_days_per_month ?? 26 ?>">
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Late & Early Leave Rules</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="late_deduction_enabled"
                                           name="late_deduction_enabled" value="1"
                                           <?= isset($settings->late_deduction_enabled) && $settings->late_deduction_enabled ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="late_deduction_enabled">Enable Late Deduction</label>
                                </div>
                            </div>

                            <div class="late-fields" style="display: <?= isset($settings->late_deduction_enabled) && $settings->late_deduction_enabled ? 'block' : 'none' ?>">
                                <div class="form-group">
                                    <label>Late Deduction Amount (per minute)</label>
                                    <input type="number" step="0.01" class="form-control" name="late_deduction_amount"
                                           value="<?= $settings->late_deduction_amount ?? '' ?>">
                                </div>
                                <div class="form-group">
                                    <label>Grace Period (minutes)</label>
                                    <input type="number" class="form-control" name="late_grace_minutes"
                                           value="<?= $settings->late_grace_minutes ?? 5 ?>">
                                </div>
                            </div>

                            <hr>

                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="early_leave_deduction_enabled"
                                           name="early_leave_deduction_enabled" value="1"
                                           <?= isset($settings->early_leave_deduction_enabled) && $settings->early_leave_deduction_enabled ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="early_leave_deduction_enabled">Enable Early Leave Deduction</label>
                                </div>
                            </div>

                            <div class="early-fields" style="display: <?= isset($settings->early_leave_deduction_enabled) && $settings->early_leave_deduction_enabled ? 'block' : 'none' ?>">
                                <div class="form-group">
                                    <label>Early Leave Deduction (per minute)</label>
                                    <input type="number" step="0.01" class="form-control" name="early_leave_deduction_amount"
                                           value="<?= $settings->early_leave_deduction_amount ?? '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Bonus Rules</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="attendance_bonus_enabled"
                                           name="attendance_bonus_enabled" value="1"
                                           <?= isset($settings->attendance_bonus_enabled) && $settings->attendance_bonus_enabled ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="attendance_bonus_enabled">Enable Attendance Bonus</label>
                                </div>
                            </div>

                            <div class="bonus-fields" style="display: <?= isset($settings->attendance_bonus_enabled) && $settings->attendance_bonus_enabled ? 'block' : 'none' ?>">
                                <div class="form-group">
                                    <label>Days Required for Bonus</label>
                                    <input type="number" class="form-control" name="attendance_bonus_days_required"
                                           value="<?= $settings->attendance_bonus_days_required ?? 26 ?>">
                                </div>
                                <div class="form-group">
                                    <label>Bonus Type</label>
                                    <select name="attendance_bonus_type" class="form-control">
                                        <option value="per_day_salary" <?= isset($settings->attendance_bonus_type) && $settings->attendance_bonus_type == 'per_day_salary' ? 'selected' : '' ?>>Per Day Salary</option>
                                        <option value="fixed_amount" <?= isset($settings->attendance_bonus_type) && $settings->attendance_bonus_type == 'fixed_amount' ? 'selected' : '' ?>>Fixed Amount</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Bonus Amount</label>
                                    <input type="number" step="0.01" class="form-control" name="attendance_bonus_amount"
                                           value="<?= $settings->attendance_bonus_amount ?? '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Security Deduction</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="security_deduction_enabled"
                                           name="security_deduction_enabled" value="1"
                                           <?= isset($settings->security_deduction_enabled) && $settings->security_deduction_enabled ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="security_deduction_enabled">Enable Security Deduction</label>
                                </div>
                            </div>

                            <div class="security-fields" style="display: <?= isset($settings->security_deduction_enabled) && $settings->security_deduction_enabled ? 'block' : 'none' ?>">
                                <div class="form-group">
                                    <label>Deduction Type</label>
                                    <select name="security_deduction_type" class="form-control">
                                        <option value="fixed_amount" <?= isset($settings->security_deduction_type) && $settings->security_deduction_type == 'fixed_amount' ? 'selected' : '' ?>>Fixed Amount</option>
                                        <option value="percentage" <?= isset($settings->security_deduction_type) && $settings->security_deduction_type == 'percentage' ? 'selected' : '' ?>>Percentage of Salary</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Amount/Percentage Value</label>
                                    <input type="number" step="0.01" class="form-control" name="security_deduction_value"
                                           value="<?= $settings->security_deduction_value ?? '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Generate Modal -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('admin/salary-settings/generate') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Generate Monthly Salary</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Year</label>
                        <select name="year" class="form-control" required>
                            <?php for($y = date('Y')-1; $y <= date('Y'); $y++): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Month</label>
                        <select name="month" class="form-control" required>
                            <?php for($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= $m == date('m') ? 'selected' : '' ?>><?= date('F', strtotime("2024-$m-01")) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i>
                        This will generate salary slips for all active employees for the selected month.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function() {
    // Toggle deduction fields based on type
    function toggleDeductionFields() {
        var type = $('select[name="deduction_type"]').val();
        if (type == 'per_day_salary') {
            $('#deduction_amount_field').show();
            $('#deduction_percentage_field').hide();
        } else if (type == 'fixed_amount') {
            $('#deduction_amount_field').show();
            $('#deduction_percentage_field').hide();
        } else if (type == 'percentage') {
            $('#deduction_amount_field').hide();
            $('#deduction_percentage_field').show();
        }
    }

    $('select[name="deduction_type"]').change(toggleDeductionFields);
    toggleDeductionFields();

    // Toggle late fields
    $('#late_deduction_enabled').change(function() {
        $('.late-fields').toggle(this.checked);
    });

    // Toggle early fields
    $('#early_leave_deduction_enabled').change(function() {
        $('.early-fields').toggle(this.checked);
    });

    // Toggle bonus fields
    $('#attendance_bonus_enabled').change(function() {
        $('.bonus-fields').toggle(this.checked);
    });

    // Toggle security fields
    $('#security_deduction_enabled').change(function() {
        $('.security-fields').toggle(this.checked);
    });
});
</script>

<?= $this->endSection() ?>
