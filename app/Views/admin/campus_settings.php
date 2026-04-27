<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-cog"></i> Campus Settings</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Campus Settings</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="card card-primary">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Salary & Attendance Settings</h3>
    </div>
    <form id="settingsForm">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h5><i class="fas fa-calendar-alt"></i> Attendance Deduction Settings</h5>
            <hr>
            <div class="form-group">
              <label>Deduction Type for Absence</label>
              <select name="deduction_type" class="form-control">
                <option value="per_day_salary" <?= isset($settings) && $settings->deduction_type == 'per_day_salary' ? 'selected' : '' ?>>Per Day Salary</option>
                <option value="fixed_amount" <?= isset($settings) && $settings->deduction_type == 'fixed_amount' ? 'selected' : '' ?>>Fixed Amount</option>
                <option value="percentage" <?= isset($settings) && $settings->deduction_type == 'percentage' ? 'selected' : '' ?>>Percentage of Salary</option>
              </select>
            </div>
            <div class="form-group">
              <label>Deduction Per Day Amount (if fixed)</label>
              <input type="number" step="0.01" name="deduction_per_day_amount" class="form-control" value="<?= $settings->deduction_per_day_amount ?? '' ?>" placeholder="Enter amount">
            </div>
            <div class="form-group">
              <label>Deduction Percentage (if percentage)</label>
              <input type="number" step="0.01" name="deduction_per_day_percentage" class="form-control" value="<?= $settings->deduction_per_day_percentage ?? '' ?>" placeholder="Enter percentage">
            </div>
            <div class="form-group">
              <label>Working Days Per Month</label>
              <input type="number" name="working_days_per_month" class="form-control" value="<?= $settings->working_days_per_month ?? 26 ?>" placeholder="Default: 26">
            </div>
          </div>
          <div class="col-md-6">
            <h5><i class="fas fa-clock"></i> Late & Early Leave Deduction</h5>
            <hr>
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="lateDeduction" name="late_deduction_enabled" value="1" <?= isset($settings) && $settings->late_deduction_enabled ? 'checked' : '' ?>>
                <label class="custom-control-label" for="lateDeduction">Enable Late Deduction</label>
              </div>
            </div>
            <div class="form-group">
              <label>Late Deduction Amount (per minute)</label>
              <input type="number" step="0.01" name="late_deduction_amount" class="form-control" value="<?= $settings->late_deduction_amount ?? '' ?>" placeholder="Enter amount per minute">
            </div>
            <div class="form-group">
              <label>Grace Period (minutes)</label>
              <input type="number" name="late_grace_minutes" class="form-control" value="<?= $settings->late_grace_minutes ?? 5 ?>" placeholder="Default: 5 minutes">
            </div>
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="earlyLeaveDeduction" name="early_leave_deduction_enabled" value="1" <?= isset($settings) && $settings->early_leave_deduction_enabled ? 'checked' : '' ?>>
                <label class="custom-control-label" for="earlyLeaveDeduction">Enable Early Leave Deduction</label>
              </div>
            </div>
            <div class="form-group">
              <label>Early Leave Deduction Amount (per minute)</label>
              <input type="number" step="0.01" name="early_leave_deduction_amount" class="form-control" value="<?= $settings->early_leave_deduction_amount ?? '' ?>" placeholder="Enter amount per minute">
            </div>
          </div>
        </div>

        <div class="row mt-4">
          <div class="col-md-6">
            <h5><i class="fas fa-gift"></i> Attendance Bonus Settings</h5>
            <hr>
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="attendanceBonus" name="attendance_bonus_enabled" value="1" <?= isset($settings) && $settings->attendance_bonus_enabled ? 'checked' : '' ?>>
                <label class="custom-control-label" for="attendanceBonus">Enable Attendance Bonus</label>
              </div>
            </div>
            <div class="form-group">
              <label>Days Required for Bonus</label>
              <input type="number" name="attendance_bonus_days_required" class="form-control" value="<?= $settings->attendance_bonus_days_required ?? 26 ?>" placeholder="Default: 26 days">
            </div>
            <div class="form-group">
              <label>Bonus Type</label>
              <select name="attendance_bonus_type" class="form-control">
                <option value="per_day_salary" <?= isset($settings) && $settings->attendance_bonus_type == 'per_day_salary' ? 'selected' : '' ?>>Per Day Salary</option>
                <option value="fixed_amount" <?= isset($settings) && $settings->attendance_bonus_type == 'fixed_amount' ? 'selected' : '' ?>>Fixed Amount</option>
              </select>
            </div>
            <div class="form-group">
              <label>Bonus Amount (if fixed)</label>
              <input type="number" step="0.01" name="attendance_bonus_amount" class="form-control" value="<?= $settings->attendance_bonus_amount ?? '' ?>" placeholder="Enter amount">
            </div>
          </div>
          <div class="col-md-6">
            <h5><i class="fas fa-shield-alt"></i> Security Deduction Settings</h5>
            <hr>
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="securityDeduction" name="security_deduction_enabled" value="1" <?= isset($settings) && $settings->security_deduction_enabled ? 'checked' : '' ?>>
                <label class="custom-control-label" for="securityDeduction">Enable Security Deduction</label>
              </div>
            </div>
            <div class="form-group">
              <label>Security Deduction Type</label>
              <select name="security_deduction_type" class="form-control">
                <option value="fixed_amount" <?= isset($settings) && $settings->security_deduction_type == 'fixed_amount' ? 'selected' : '' ?>>Fixed Amount</option>
                <option value="percentage" <?= isset($settings) && $settings->security_deduction_type == 'percentage' ? 'selected' : '' ?>>Percentage of Salary</option>
              </select>
            </div>
            <div class="form-group">
              <label>Security Deduction Value</label>
              <input type="number" step="0.01" name="security_deduction_value" class="form-control" value="<?= $settings->security_deduction_value ?? '' ?>" placeholder="Enter amount or percentage">
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
        <button type="reset" class="btn btn-default"><i class="fas fa-undo"></i> Reset</button>
      </div>
    </form>
  </div>
</section>

<script>
$(function() {
  $('#settingsForm').on('submit', function(e) {
    e.preventDefault();
    
    const btn = $(this).find('button[type="submit"]');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    
    $.post("<?= base_url('admin/campus-settings/save') ?>", $(this).serialize() + '&<?= csrf_token() ?>=<?= csrf_hash() ?>', function(response) {
      if (response.success) {
        toastr.success(response.msg);
      } else {
        toastr.error(response.msg);
      }
    }, 'json').always(function() {
      btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Settings');
    });
  });
});
</script>

<?= $this->endSection() ?>