<?php
/** @var object $emp */
$sno = $sno ?? 1;
$v = static function ($x) {
    return esc($x ?? '');
};
$g = strtolower((string) ($emp->gender ?? ''));
$ms = strtolower((string) ($emp->marital_status ?? ''));
$ct = (string) ($emp->contract_type ?? 'permanent');
$pm = (string) ($emp->salary_payment_method ?? 'bank');
$salaryVal = $salaryValue ?? '';
if ($salaryVal !== '' && $salaryVal !== null && is_numeric($salaryVal)) {
    $salaryVal = number_format((float) $salaryVal, 2, '.', '');
}
$editUrl = base_url('admin/users/edit/' . (int) $emp->id);
$eid = (int) $emp->id;
$fullName = trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? ''));
?>
<div class="emp-bulk-card card shadow-sm mb-4 border-primary" style="border-start-width:4px!important;border-start-style:solid!important;" id="emp-card-<?= $eid ?>" data-emp-id="<?= $eid ?>">
  <div class="card-header bg-light d-flex flex-wrap align-items-center justify-content-between py-2">
    <div class="d-flex flex-wrap align-items-center pe-2" style="min-width:0;">
      <span class="badge text-bg-secondary me-2"><?= (int) $sno ?></span>
      <div style="min-width:0;">
        <div class="fw-bold text-truncate" title="<?= $v($fullName) ?>"><?= $v($fullName ?: ($emp->username ?? 'Employee')) ?></div>
        <div class="small text-muted text-truncate">@<?= $v($emp->username ?? '') ?> · <span class="text-break d-inline"><?= $v($emp->email ?? '') ?></span></div>
      </div>
    </div>
    <div class="d-flex flex-wrap align-items-center mt-2 mt-sm-0">
      <a href="<?= esc($editUrl) ?>" class="btn btn-sm btn-outline-secondary me-2" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i> Full form</a>
    </div>
  </div>
  <div class="card-body">
    <input type="hidden" name="emp_id" value="<?= $eid ?>">

    <h6 class="text-primary border-bottom pb-1 mb-3 small text-uppercase">Basic information</h6>
    <div class="row">
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="first_name">
          <label class="small text-muted mb-0">First name</label>
          <input type="text" name="first_name" class="form-control form-control-sm" value="<?= $v($emp->first_name) ?>">
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="last_name">
          <label class="small text-muted mb-0">Last name</label>
          <input type="text" name="last_name" class="form-control form-control-sm" value="<?= $v($emp->last_name) ?>">
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="designation">
          <label class="small text-muted mb-0">Designation</label>
          <input type="text" name="designation" class="form-control form-control-sm" value="<?= $v($emp->designation) ?>">
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="cnic">
          <label class="small text-muted mb-0">CNIC</label>
          <input type="text" name="cnic" class="form-control form-control-sm" value="<?= $v($emp->cnic) ?>" placeholder="35201-1234567-1">
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="f_name">
          <label class="small text-muted mb-0">Father name</label>
          <input type="text" name="f_name" class="form-control form-control-sm" value="<?= $v($emp->f_name) ?>">
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="dob">
          <label class="small text-muted mb-0">Date of birth</label>
          <input type="date" name="dob" class="form-control form-control-sm" value="<?= $v($emp->dob) ?>">
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="gender">
          <label class="small text-muted mb-0">Gender</label>
          <select name="gender" class="form-control form-control-sm">
            <option value="">—</option>
            <option value="male" <?= $g === 'male' ? 'selected' : '' ?>>Male</option>
            <option value="female" <?= $g === 'female' ? 'selected' : '' ?>>Female</option>
          </select>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="marital_status">
          <label class="small text-muted mb-0">Marital status</label>
          <select name="marital_status" class="form-control form-control-sm">
            <option value="">—</option>
            <option value="single" <?= $ms === 'single' ? 'selected' : '' ?>>Single</option>
            <option value="married" <?= $ms === 'married' ? 'selected' : '' ?>>Married</option>
            <option value="divorced" <?= $ms === 'divorced' ? 'selected' : '' ?>>Divorced</option>
          </select>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="qualification">
          <label class="small text-muted mb-0">Qualification</label>
          <input type="text" name="qualification" class="form-control form-control-sm" value="<?= $v($emp->qualification) ?>">
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="experience">
          <label class="small text-muted mb-0">Experience</label>
          <input type="text" name="experience" class="form-control form-control-sm" value="<?= $v($emp->experience) ?>">
        </div>
      </div>
      <div class="col-12">
        <div class="form-group mb-2 emp-field-wrap" data-col="skills">
          <label class="small text-muted mb-0">Skills</label>
          <input type="text" name="skills" class="form-control form-control-sm" value="<?= $v($emp->skills) ?>" placeholder="Comma separated">
        </div>
      </div>
      <div class="col-12">
        <div class="form-group mb-2 emp-field-wrap" data-col="address">
          <label class="small text-muted mb-0">Address</label>
          <textarea name="address" rows="2" class="form-control form-control-sm"><?= $v($emp->address) ?></textarea>
        </div>
      </div>
    </div>

    <h6 class="text-primary border-bottom pb-1 mb-3 mt-4 small text-uppercase">Contact &amp; emergency</h6>
    <div class="row">
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="mobile_no">
          <label class="small text-muted mb-0">Mobile</label>
          <input type="text" name="mobile_no" class="form-control form-control-sm" value="<?= $v($emp->mobile_no) ?>">
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="mobile_no2">
          <label class="small text-muted mb-0">Alternate mobile</label>
          <input type="text" name="mobile_no2" class="form-control form-control-sm" value="<?= $v($emp->mobile_no2) ?>">
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="emergency_contact_person">
          <label class="small text-muted mb-0">Emergency contact person</label>
          <input type="text" name="emergency_contact_person" class="form-control form-control-sm" value="<?= $v($emp->emergency_contact_person) ?>">
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="emergency_contact_no">
          <label class="small text-muted mb-0">Emergency contact no</label>
          <input type="text" name="emergency_contact_no" class="form-control form-control-sm" value="<?= $v($emp->emergency_contact_no) ?>">
        </div>
      </div>
    </div>

    <h6 class="text-primary border-bottom pb-1 mb-3 mt-4 small text-uppercase">Bank account</h6>
    <div class="row">
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="bank_name">
          <label class="small text-muted mb-0">Bank name</label>
          <input type="text" name="bank_name" class="form-control form-control-sm" value="<?= $v($emp->bank_name) ?>">
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="account_title">
          <label class="small text-muted mb-0">Account title</label>
          <input type="text" name="account_title" class="form-control form-control-sm" value="<?= $v($emp->account_title) ?>">
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="account_number">
          <label class="small text-muted mb-0">Account number</label>
          <input type="text" name="account_number" class="form-control form-control-sm" value="<?= $v($emp->account_number) ?>">
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="branch_code">
          <label class="small text-muted mb-0">Branch code</label>
          <input type="text" name="branch_code" class="form-control form-control-sm" value="<?= $v($emp->branch_code) ?>">
        </div>
      </div>
      <div class="col-12">
        <div class="form-group mb-2 emp-field-wrap" data-col="bank_address">
          <label class="small text-muted mb-0">Bank address</label>
          <input type="text" name="bank_address" class="form-control form-control-sm" value="<?= $v($emp->bank_address) ?>">
        </div>
      </div>
    </div>

    <h6 class="text-primary border-bottom pb-1 mb-3 mt-4 small text-uppercase">Employment</h6>
    <div class="row">
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="joining_date">
          <label class="small text-muted mb-0">Joining date</label>
          <input type="date" name="joining_date" class="form-control form-control-sm" value="<?= $v($emp->joining_date) ?>">
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="salary">
          <label class="small text-muted mb-0">Basic salary (PKR)</label>
          <input type="number" step="0.01" name="salary" class="form-control form-control-sm" value="<?= $v($salaryVal) ?>">
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="contract_type">
          <label class="small text-muted mb-0">Contract type</label>
          <select name="contract_type" class="form-control form-control-sm">
            <option value="permanent" <?= $ct === 'permanent' ? 'selected' : '' ?>>Permanent</option>
            <option value="contract" <?= $ct === 'contract' ? 'selected' : '' ?>>Contract</option>
            <option value="probation" <?= $ct === 'probation' ? 'selected' : '' ?>>Probation</option>
          </select>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="salary_payment_method">
          <label class="small text-muted mb-0">Payment method</label>
          <select name="salary_payment_method" class="form-control form-control-sm">
            <option value="bank" <?= $pm === 'bank' ? 'selected' : '' ?>>Bank transfer</option>
            <option value="cash" <?= $pm === 'cash' ? 'selected' : '' ?>>Cash</option>
            <option value="cheque" <?= $pm === 'cheque' ? 'selected' : '' ?>>Cheque</option>
          </select>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="contract_start">
          <label class="small text-muted mb-0">Contract start</label>
          <input type="date" name="contract_start" class="form-control form-control-sm" value="<?= $v($emp->contract_start) ?>">
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="form-group mb-2 emp-field-wrap" data-col="contract_end">
          <label class="small text-muted mb-0">Contract end</label>
          <input type="date" name="contract_end" class="form-control form-control-sm" value="<?= $v($emp->contract_end) ?>">
        </div>
      </div>
    </div>
  </div>
  <div class="card-footer bg-light d-flex flex-wrap justify-content-between align-items-center py-2">
    <span class="small text-muted">Updates only the fields you selected at the top.</span>
    <button type="button" class="btn btn-success btn-save-emp-row"><i class="fas fa-save"></i> Save changes</button>
  </div>
</div>
