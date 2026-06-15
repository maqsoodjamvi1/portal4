<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<meta name="csrf-token" content="<?= csrf_hash() ?>">
<input type="hidden" id="campus_id" name="campus_id" value="<?= session('member_campusid') ?>">
<input type="hidden" id="session_id" name="session_id" value="<?= session('member_sessionid') ?>">

<?= form_open_multipart(
    site_url('admin/students/save_admission'),
    ['id' => 'student-admission-form', 'novalidate' => 'novalidate']
) ?>

<div class="container-fluid px-3">
  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white">
      <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i> Student Admission Registration</h5>
      <button type="button" class="btn btn-light btn-sm no-print" onclick="window.print();">
        <i class="fas fa-print"></i> Print / Save PDF
      </button>
    </div>

    <div class="card-body">
      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?></div>
      <?php endif; ?>

      <!-- ======================== STUDENT INFORMATION ======================== -->
      <div class="section-card">
        <div class="section-head">
          <h5 class="mb-0"><i class="fas fa-user-graduate text-primary me-2"></i> Student Information</h5>
        </div>
        <div class="section-body">
          <div class="row">
            <div class="col-12 col-md-3 mb-3 ad-field" data-field="reg_no">
              <label class="mb-1 fw-semibold"><i class="fas fa-hashtag me-1 text-primary"></i> Registration No</label>
              <input type="text" class="form-control form-control-sm" id="reg_no" name="reg_no" value="<?= esc(old('reg_no', $reg_no ?? '')) ?>" readonly>
            </div>

            <div class="col-12 col-md-3 mb-3 ad-field" data-field="gr_no">
              <label class="mb-1 fw-semibold"><i class="far fa-id-card me-1 text-primary"></i> G.R. Number <small class="text-muted">(Optional)</small></label>
              <input type="text" class="form-control form-control-sm" id="gr_no" name="gr_no" value="<?= esc(old('gr_no', $gr_no ?? '')) ?>" placeholder="School GR No">
            </div>

            <div class="col-12 col-md-3 mb-3 ad-field" data-field="gr_date">
              <label class="mb-1 fw-semibold"><i class="far fa-calendar-alt me-1 text-primary"></i> G.R. Date</label>
              <input type="text" class="form-control form-control-sm datepicker" id="gr_date" name="gr_date" value="<?= date('d/m/Y') ?>" placeholder="dd/mm/yyyy" autocomplete="off">
            </div>

            <div class="col-12 col-md-3 mb-3 ad-field" data-field="date_of_admission">
              <label class="mb-1 fw-semibold"><i class="far fa-calendar-check me-1 text-primary"></i> Admission Date</label>
              <input type="text" class="form-control form-control-sm datepicker" id="date_of_admission" name="date_of_admission" value="<?= date('d/m/Y') ?>" placeholder="dd/mm/yyyy" autocomplete="off">
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 mb-3 ad-field" data-field="full_name" data-required="1">
              <label class="mb-1 fw-semibold required"><i class="fas fa-user me-1 text-primary"></i> Full Name</label>
              <input type="text" class="form-control form-control-sm" id="full_name" name="full_name" placeholder="Student full name" required>
            </div>

            <div class="col-md-3 mb-3 ad-field" data-field="date_of_birth" data-required="1">
              <label class="mb-1 fw-semibold required"><i class="fas fa-birthday-cake me-1 text-primary"></i> Date of Birth</label>
              <input type="text" class="form-control form-control-sm datepicker" id="date_of_birth" name="date_of_birth" placeholder="dd/mm/yyyy" autocomplete="off" required>
            </div>

            <div class="col-md-3 mb-3 ad-field" data-field="gender" data-required="1">
              <label class="mb-1 fw-semibold required"><i class="fas fa-venus-mars me-1 text-primary"></i> Gender</label>
              <div class="btn-group btn-group-sm d-flex w-100" id="genderToggle" data-bs-toggle="buttons">
                <label class="btn btn-outline-primary flex-fill"><input type="radio" name="gender" value="male" required> <i class="fas fa-mars me-1"></i> Male</label>
                <label class="btn btn-outline-info flex-fill"><input type="radio" name="gender" value="female" required> <i class="fas fa-venus me-1"></i> Female</label>
              </div>
            </div>

            <div class="col-md-3 mb-3 ad-field" data-field="student_cnic">
              <label class="mb-1 fw-semibold"><i class="far fa-id-badge me-1 text-primary"></i> CNIC / B-Form</label>
              <input type="text" class="form-control form-control-sm cnic-mask" id="student_cnic" name="student_cnic" placeholder="XXXXX-XXXXXXX-X">
            </div>
          </div>

          <!-- Sibling Search Section -->
          <div class="ad-field" data-field="sibling_search">
            <label class="form-label"><i class="fas fa-user-friends me-1"></i> Search Sibling (Optional)</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="fas fa-search"></i></span>
              <input type="text" class="form-control form-control-sm" id="sibling_search" placeholder="Type student name (min 3 characters)..." autocomplete="off">
              <button class="btn btn-outline-secondary btn-sm" type="button" id="clearSiblingBtn"><i class="fas fa-times"></i></button>
            </div>
            <div id="sibling-results" class="mt-2" style="display: none;"><div class="list-group" id="sibling-list"></div></div>
            <div id="selected-sibling-info" class="mt-2"></div>
          </div>

          <div class="row">
            <div class="col-md-3 mb-3 ad-field" data-field="previous_school">
              <label class="mb-1 fw-semibold"><i class="fas fa-school me-1 text-primary"></i> Previous School</label>
              <input type="text" class="form-control form-control-sm" id="previous_school" name="previous_school" placeholder="School name">
            </div>
            <div class="col-md-3 mb-3 ad-field" data-field="previous_school_city">
              <label class="mb-1 fw-semibold"><i class="fas fa-city me-1 text-primary"></i> Previous School City</label>
              <input type="text" class="form-control form-control-sm" id="previous_school_city" name="previous_school_city" placeholder="City">
            </div>
            <div class="col-md-3 mb-3 ad-field" data-field="health_condition">
              <label class="mb-1 fw-semibold"><i class="fas fa-heartbeat me-1 text-primary"></i> Health Condition</label>
              <input type="text" class="form-control form-control-sm" id="health_condition" name="health_condition" placeholder="e.g. Normal">
            </div>
            <div class="col-md-3 mb-3 ad-field" data-field="major_injuries">
              <label class="mb-1 fw-semibold"><i class="fas fa-first-aid me-1 text-primary"></i> Major Injuries / Illness</label>
              <input type="text" class="form-control form-control-sm" id="major_injuries" name="major_injuries" placeholder="If any">
            </div>
          </div>
        </div>
      </div>

      <!-- ======================== PARENT / GUARDIAN INFORMATION ======================== -->
      <div class="section-card">
        <div class="section-head">
          <h5 class="mb-0"><i class="fas fa-users text-info me-2"></i> Parent / Guardian Information</h5>
        </div>
        <div class="section-body">
          <input type="hidden" id="parent_id" name="parent_id" value="">
          
          <div class="info-grid">
            <div class="ad-field" data-field="father_cnic" data-required="1">
              <label class="form-label required"><i class="fas fa-id-card me-1"></i> Father's CNIC</label>
              <div class="input-group input-group-sm">
                <input type="text" class="form-control form-control-sm cnic-mask parent-field" name="father_cnic" id="father_cnic" placeholder="XXXXX-XXXXXXX-X" required>
                <span id="cnic-spinner" class="input-group-text d-none"><i class="fas fa-spinner fa-spin"></i></span>
              </div>
              <div id="children-info-container" class="mt-2"></div>
            </div>

            <div class="ad-field" data-field="f_name" data-required="1">
              <label class="form-label required"><i class="fas fa-user me-1"></i> Father's Full Name</label>
              <input type="text" class="form-control form-control-sm parent-field" name="f_name" id="f_name" required>
            </div>

            <div class="ad-field" data-field="father_contact">
              <label class="form-label"><i class="fas fa-phone me-1"></i> Father's Contact</label>
              <div class="input-group input-group-sm">
                <input type="text" class="form-control form-control-sm phone-mask parent-field" name="father_contact" id="father_contact" placeholder="+92 XXX XXXXXXX">
                <button class="btn btn-outline-success btn-sm set-whatsapp" type="button" data-source="father_contact" title="Use as WhatsApp number">
                    <i class="fab fa-whatsapp"></i>
                  </button>
              </div>
            </div>

            <div class="ad-field" data-field="mother_contact">
              <label class="form-label"><i class="fas fa-phone me-1"></i> Mother's Contact</label>
              <div class="input-group input-group-sm">
                <input type="text" class="form-control form-control-sm phone-mask parent-field" name="mother_contact" id="mother_contact" placeholder="+92 XXX XXXXXXX">
                <button class="btn btn-outline-success btn-sm set-whatsapp" type="button" data-source="mother_contact" title="Use as WhatsApp number">
                    <i class="fab fa-whatsapp"></i>
                  </button>
              </div>
            </div>

            <div class="ad-field" data-field="whatsapp_contact">
              <label class="form-label"><i class="fab fa-whatsapp me-1 text-success"></i> WhatsApp Number</label>
              <input type="text" class="form-control form-control-sm phone-mask" name="whatsapp_contact" id="whatsapp_contact" placeholder="+92 XXX XXXXXXX">
            </div>

            <div class="ad-field" data-field="father_email">
              <label class="form-label"><i class="fas fa-envelope me-1"></i> Father's Email</label>
              <input type="email" class="form-control form-control-sm parent-field" name="father_email" id="father_email" placeholder="example@domain.com">
            </div>

            <div class="ad-field" data-field="father_occupation">
              <label class="form-label"><i class="fas fa-briefcase me-1"></i> Father's Occupation</label>
              <input type="text" class="form-control form-control-sm parent-field" name="father_occupation" id="father_occupation">
            </div>

            <div class="ad-field" data-field="father_office_address">
              <label class="form-label"><i class="fas fa-map-marker-alt me-1"></i> Father's Office Address</label>
              <input type="text" class="form-control form-control-sm parent-field" name="father_office_address" id="father_office_address">
            </div>

            <div class="ad-field" data-field="m_name">
              <label class="form-label"><i class="fas fa-user me-1"></i> Mother's Full Name</label>
              <input type="text" class="form-control form-control-sm parent-field" name="m_name" id="m_name">
            </div>

            <div class="ad-field" data-field="address_line1">
              <label class="form-label"><i class="fas fa-map-marker-alt me-1"></i> Residential Address</label>
              <input type="text" class="form-control form-control-sm parent-field" name="address_line1" id="address_line1">
            </div>

            <div class="ad-field" data-field="city">
              <label class="form-label"><i class="fas fa-city me-1"></i> City</label>
              <input type="text" class="form-control form-control-sm parent-field" name="city" id="city">
            </div>

            <div class="ad-field" data-field="hear_source">
              <label class="form-label"><i class="fas fa-bullhorn me-1"></i> How did you hear about us?</label>
              <input type="text" class="form-control form-control-sm parent-field" name="hear_source" id="hear_source">
            </div>

            <div class="ad-field" data-field="emergency_contact_person">
              <label class="form-label"><i class="fas fa-user me-1"></i> Emergency Contact Person</label>
              <input type="text" class="form-control form-control-sm parent-field" name="emergency_contact_person" id="emergency_contact_person">
            </div>

            <div class="ad-field" data-field="emergency_contact">
              <label class="form-label"><i class="fas fa-phone me-1"></i> Emergency Contact Number</label>
              <input type="text" class="form-control form-control-sm phone-mask parent-field" name="emergency_contact" id="emergency_contact" placeholder="+92 XXX XXXXXXX">
            </div>

            <div class="ad-field" data-field="a_address">
              <label class="form-label"><i class="fas fa-map-marker-alt me-1"></i> Emergency Contact Address</label>
              <input type="text" class="form-control form-control-sm parent-field" name="a_address" id="a_address">
            </div>
          </div>
        </div>
      </div>

      <!-- ======================== FEE STRUCTURE & FINANCIAL ======================== -->
      <div class="section-card">
        <div class="section-head">
          <h5 class="mb-0"><i class="fas fa-receipt text-primary me-2"></i> Fee Structure & Financial</h5>
        </div>
        <div class="section-body">
          <!-- Class Selection -->
          <div class="form-group mb-3">
            <label class="fw-semibold required"><i class="fas fa-layer-group me-1 text-primary"></i> Select Class Section</label>
            <select id="cls_sec_id" name="cls_sec_id" class="form-control" required>
              <option value="">-- Select Class Section --</option>
              <?php foreach ($sectionsclassinfo as $row): ?>
                <option value="<?= (int)$row['cls_sec_id'] ?>" <?= ((int)$row['cls_sec_id'] === (int)($cls_sec_id ?? 0) ? 'selected' : '') ?>>
                  <?= esc($row['sectionclassname']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Fee Dates Row -->
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="fw-semibold required"><i class="far fa-calendar-alt me-1 text-primary"></i> Fee Month</label>
              <input type="text" class="form-control monthpicker" id="fee_month_ui" placeholder="Select month" autocomplete="off" required>
              <input id="fee_month" name="fee_month" type="hidden" value="<?= date('Y-m') ?>">
            </div>
            <div class="col-md-4 mb-3">
              <label class="fw-semibold required"><i class="far fa-calendar-check me-1 text-primary"></i> Issue Date</label>
              <input type="text" class="form-control datepicker" id="fee_issue_date" name="fee_issue_date" value="<?= date('d/m/Y') ?>" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="fw-semibold required"><i class="far fa-calendar-minus me-1 text-primary"></i> Due Date</label>
              <input type="text" class="form-control datepicker" id="fee_due_date" name="fee_due_date" value="<?= date('d/m/Y', strtotime('+10 days')) ?>" required>
            </div>
          </div>

          <!-- Fee Cards Container -->
          <div class="fee-cards-container" id="fee-type-container">
            <div class="fee-loading-state text-center py-5">
              <div class="mb-3"><i class="fas fa-arrow-up fa-3x text-primary opacity-50"></i></div>
              <p class="text-muted mb-0">Select a class section above to load fee structure</p>
            </div>
          </div>

          <!-- Fee Summary -->
          <div class="fee-summary-card mt-3" id="fee-summary" style="display: none;">
            <div class="fee-summary-header"><i class="fas fa-calculator me-2"></i> Fee Summary</div>
            <div class="fee-summary-body">
              <div class="fee-summary-row">
                <span>Total Standard Fees:</span>
                <span class="fee-amount">Rs. <span class="total-default">0.00</span></span>
              </div>
              <div class="fee-summary-row">
                <span>Total Payable Fees:</span>
                <span class="fee-amount fee-amount-primary">Rs. <span class="total-student">0.00</span></span>
              </div>
              <div class="fee-summary-row fee-discount-row">
                <span>Total Discount:</span>
                <span class="fee-amount text-success">Rs. <span class="total-discount">0.00</span></span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ======================== REQUIRED DOCUMENTS ======================== -->
      <?php $types = $attachementTypesInfo ?? []; ?>
      <?php if (!empty($types)): ?>
      <div class="section-card">
        <div class="section-head">
          <h5 class="mb-0"><i class="fas fa-file-upload text-warning me-2"></i> Required Documents</h5>
        </div>
        <div class="section-body">
          <div class="row">
            <?php foreach ($types as $value): ?>
              <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 border shadow-sm">
                  <div class="card-header bg-light">
                    <h6 class="card-title mb-0 text-primary"><i class="fas fa-file me-2"></i><?= esc($value->a_type_name) ?></h6>
                  </div>
                  <div class="card-body text-center">
                    <input type="hidden" class="a_type_id" value="<?= $value->a_type_id ?>">
                    <div class="mb-3">
                      <img id="preview_<?= $value->a_type_id ?>" src="https://via.placeholder.com/300x200?text=Upload+Document" class="attachment-preview img-thumbnail" style="max-height: 100px; width: auto;">
                    </div>
                    <input type="file" class="d-none attachment-file" data-typeid="<?= $value->a_type_id ?>" id="attachment_<?= $value->a_type_id ?>" accept="image/*,.pdf">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-upload" data-typeid="<?= $value->a_type_id ?>">
                      <i class="fas fa-upload me-2"></i>Upload Document
                    </button>
                  </div>
                  <div class="card-footer bg-light">
                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i><?= $value->description ?? 'Required for admission processing' ?></small>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- ======================== SUBMIT ACTIONS ======================== -->
      <div class="section-card">
        <div class="section-body">
          <div class="form-actions">
            <button type="submit" class="btn btn-success btn-action"><i class="fas fa-save me-2"></i> Submit Admission</button>
            <button type="button" id="btnCustomizeForm" class="btn btn-outline-secondary btn-action no-print" data-bs-toggle="modal" data-bs-target="#customizeModal">
              <i class="fas fa-sliders-h me-2"></i> Customize Form
            </button>
          </div>
          <p class="text-center text-muted mt-3 mb-0"><small><i class="fas fa-lock me-1"></i> Your information is secure and protected</small></p>
        </div>
      </div>
    </div>
  </div>
</div>

<?= form_close() ?>

<!-- Customize Modal -->
<div class="modal fade" id="customizeModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-sliders-h me-2"></i> Customize Admission Form</h5>
        <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <p class="text-muted mb-2">Show/hide fields. Items with a lock are always required.</p>
        <div id="fieldList" class="list-group"></div>
      </div>
      <div class="modal-footer">
        <button type="button" id="btnSavePrefs" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save preferences</button>
        <button type="button" id="resetPrefsBtn" class="btn btn-outline-danger"><i class="fas fa-undo-alt me-1"></i> Reset to Default</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Styles -->
<style>
  .required:after { content: " *"; color: #dc3545; }
  .section-card { border: 1px solid #dee2e6; border-radius: .5rem; overflow: hidden; margin-bottom: 1rem; }
  .section-head { background: #f8f9fa; padding: .75rem 1rem; font-weight: 600; border-bottom: 1px solid #e9ecef; }
  .section-body { padding: 1rem; }
  .info-grid { display: grid; grid-template-columns: repeat(3, minmax(240px, 1fr)); grid-gap: .75rem 1rem; }
  @media (max-width: 991.98px) { .info-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
  @media (max-width: 575.98px) { .info-grid { grid-template-columns: 1fr; } }
  .form-actions { display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; }
  .btn-action { min-width: 220px; height: 44px; font-weight: 600; border-radius: .45rem; }
  
  /* Fee Cards Styles */
  .fee-cards-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem; }
  @media (max-width: 768px) { .fee-cards-container { grid-template-columns: 1fr; } }
  .fee-card { background: #fff; border: 1px solid #e9ecef; border-radius: 0.75rem; overflow: hidden; transition: all 0.2s; }
  .fee-card.monthly-fee { border-start: 4px solid #007bff; background: #f8fbff; }
  .fee-card-header { padding: 0.75rem 1rem; background: #f8f9fa; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; }
  .fee-card-title { display: flex; align-items: center; gap: 0.5rem; font-weight: 600; flex-wrap: wrap; }
  .monthly-badge { background: #007bff; color: white; font-size: 0.7rem; padding: 0.2rem 0.6rem; border-radius: 1rem; }
  .fee-card-body { padding: 1rem; }
  .fee-amount-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem; }
  .fee-amount-label { color: #6c757d; font-size: 0.9rem; }
  .fee-amount-value { font-weight: 600; font-size: 1rem; }
  .fee-input-group { display: flex; align-items: center; background: #f8f9fa; border-radius: 0.5rem; overflow: hidden; border: 1px solid #e9ecef; }
  .fee-input-group span { padding: 0.5rem 0.75rem; background: #e9ecef; color: #495057; font-weight: 500; }
  .fee-input-group input { flex: 1; padding: 0.5rem; border: none; background: transparent; font-size: 0.95rem; text-align: right; min-width: 0; }
  .fee-discount-badge { background: #d4edda; color: #155724; padding: 0.4rem 0.75rem; border-radius: 2rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem; width: 100%; justify-content: center; }
  .fee-summary-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.75rem; overflow: hidden; color: white; }
  .fee-summary-header { padding: 1rem; background: rgba(0,0,0,0.1); font-weight: 600; }
  .fee-summary-body { padding: 1rem; }
  .fee-summary-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; padding: 0.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.1); flex-wrap: wrap; gap: 0.5rem; }
  .fee-discount-row { color: #a3ffb5; }
  .fee-loading-state { background: #f8f9fa; border-radius: 0.75rem; border: 2px dashed #dee2e6; padding: 2rem; text-align: center; }
  
  /* Sibling Search */
  #sibling-results { position: absolute; max-height: 300px; overflow-y: auto; background: white; border: 1px solid #dee2e6; border-radius: 4px; z-index: 1000; width: calc(100% - 2px); }
  .sibling-result-item { cursor: pointer; padding: 0.5rem; border-bottom: 1px solid #f0f0f0; }
  .sibling-result-item:hover { background-color: #f8f9fa; }
  
  @media print { .no-print, .main-sidebar, .main-header, .main-footer, .modal { display: none !important; } }
</style>

<!-- Scripts -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/4.0.9/jquery.inputmask.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/bootstrap5-compat.js?v=20260614') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/plugins/monthSelect/index.js"></script>

<script>
$(document).ready(function() {
  // Set default dates
  const today = new Date();
  const due10 = new Date();
  due10.setDate(today.getDate() + 10);
  
  // Initialize flatpickr for date fields
  $('.datepicker').each(function() {
    if (!$(this).data('flatpickr')) {
      flatpickr(this, { dateFormat: 'd/m/Y', allowInput: true });
    }
  });
  
  // Initialize month picker
  if (typeof monthSelectPlugin !== 'undefined') {
    flatpickr('#fee_month_ui', {
      dateFormat: 'F Y',
      plugins: [new monthSelectPlugin({ shorthand: false })],
      defaultDate: today,
      onChange: function(d) { 
        if (d && d[0]) {
          $('#fee_month').val(d[0].getFullYear() + '-' + String(d[0].getMonth() + 1).padStart(2, '0')); 
        }
      }
    });
  }
  
  // Set default fee month
  if (!$('#fee_month').val()) {
    $('#fee_month').val(today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0'));
  }
  
  // Set default GR date and Admission date if empty
  if (!$('#gr_date').val()) $('#gr_date').val(today.toLocaleDateString('en-GB'));
  if (!$('#date_of_admission').val()) $('#date_of_admission').val(today.toLocaleDateString('en-GB'));
  
  // Input masks
  if ($.fn.inputmask) {
    $('.cnic-mask').inputmask('99999-9999999-9');
    $('.phone-mask').inputmask('+99 999 9999999');
  }
  
  // WhatsApp set button
  $('.set-whatsapp').on('click', function() {
    const sourceId = $(this).data('source');
    const sourceValue = $('#' + sourceId).val();
    if (sourceValue) {
      $('#whatsapp_contact').val(sourceValue);
      toastr.success('WhatsApp number updated');
    } else {
      toastr.warning('Source number is empty');
    }
  });
  
  // Document upload buttons
  $('.btn-upload').on('click', function() {
    const typeId = $(this).data('typeid');
    $('#attachment_' + typeId).click();
  });
  
  $('.attachment-file').on('change', function() {
    const typeId = $(this).data('typeid');
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        $('#preview_' + typeId).attr('src', e.target.result);
      };
      reader.readAsDataURL(file);
    }
  });
  
  // Fee structure loader
  $('#cls_sec_id').on('change', function() {
    const cls_sec_id = $(this).val();
    if (!cls_sec_id) { 
      $('#fee-type-container').html('<div class="fee-loading-state text-center py-5"><p class="text-muted">Select a class section to load fee structure</p></div>'); 
      $('#fee-summary').hide(); 
      return; 
    }
    $.ajax({
      url: '<?= site_url("admin/ajax/get_class_fee_amounts") ?>',
      method: 'POST',
      data: { cls_sec_id: cls_sec_id, campus_id: $('#campus_id').val(), session_id: $('#session_id').val(), '<?= csrf_token() ?>': '<?= csrf_hash() ?>' },
      dataType: 'json',
      beforeSend: function() { 
        $('#fee-type-container').html('<div class="fee-loading-state text-center py-5"><div class="spinner-border text-primary mb-3"></div><p>Loading fee structure...</p></div>'); 
      },
      success: function(res) {
        if (res.status === 'success' && res.data && res.data.length) {
          let html = '';
          res.data.forEach(function(fee) {
            html += '<div class="fee-card ' + (fee.is_monthly ? 'monthly-fee' : '') + '">' +
              '<div class="fee-card-header">' +
                '<div class="fee-card-title">' +
                  '<i class="fas ' + (fee.is_monthly ? 'fa-calendar-alt' : 'fa-money-bill-wave') + '"></i>' +
                  '<span>' + escapeHtml(fee.fee_type_title) + '</span>' +
                '</div>' +
                (fee.is_monthly ? '<span class="monthly-badge">Monthly</span>' : '') +
              '</div>' +
              '<div class="fee-card-body">' +
                '<input type="hidden" name="fee_type_id[]" value="' + fee.fee_type_id + '">' +
                '<input type="hidden" name="is_monthly[]" value="' + (fee.is_monthly ? '1' : '0') + '">' +
                '<input type="hidden" class="default-amount" value="' + fee.default_amount + '">' +
                '<div class="fee-amount-row">' +
                  '<span class="fee-amount-label"><i class="fas fa-tag"></i> Standard Amount</span>' +
                  '<span class="fee-amount-value standard">Rs. ' + parseFloat(fee.default_amount).toFixed(2) + '</span>' +
                '</div>' +
                '<div class="fee-amount-row">' +
                  '<span class="fee-amount-label"><i class="fas fa-hand-holding-usd"></i> Payable Amount</span>' +
                  '<div class="fee-input-group"><span>Rs.</span><input type="number" name="student_amount[]" class="student-amount" value="' + fee.default_amount + '" data-default="' + fee.default_amount + '" step="0.01" min="0"></div>' +
                '</div>' +
                '<div class="fee-discount-badge"><i class="fas fa-tag"></i> Discount: Rs. <span class="discount-amount">0.00</span></div>' +
              '</div>' +
            '</div>';
          });
          $('#fee-type-container').html(html);
          $('#fee-summary').show();
          updateTotals();
          $('.student-amount').on('input', function() {
            const $card = $(this).closest('.fee-card');
            const defVal = parseFloat($card.find('.default-amount').val()) || 0;
            const stuVal = parseFloat($(this).val()) || 0;
            $card.find('.discount-amount').text((defVal - stuVal).toFixed(2));
            updateTotals();
          });
          $('.student-amount').trigger('input');
        } else {
          $('#fee-type-container').html('<div class="fee-loading-state text-center py-5"><i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i><p class="text-muted">' + (res.message || 'No fee structure found') + '</p></div>');
          $('#fee-summary').hide();
        }
      },
      error: function() {
        $('#fee-type-container').html('<div class="fee-loading-state text-center py-5"><i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i><p class="text-muted">Error loading fee structure</p></div>');
      }
    });
  });
  
  function updateTotals() {
    let totalDefault = 0, totalStudent = 0;
    $('.fee-card').each(function() {
      const defVal = parseFloat($(this).find('.default-amount').val()) || 0;
      const stuVal = parseFloat($(this).find('.student-amount').val()) || 0;
      totalDefault += defVal;
      totalStudent += stuVal;
    });
    $('.total-default').text(totalDefault.toFixed(2));
    $('.total-student').text(totalStudent.toFixed(2));
    $('.total-discount').text((totalDefault - totalStudent).toFixed(2));
  }
  
  // Parent CNIC lookup
  $('#father_cnic').on('blur', function() {
    const cnic = $(this).val();
    if (!cnic || cnic.length < 15) return;
    $.ajax({
      url: '<?= site_url("admin/students/check_parent_cnic") ?>',
      method: 'POST',
      data: { cnic: cnic, campus_id: $('#campus_id').val(), '<?= csrf_token() ?>': '<?= csrf_hash() ?>' },
      dataType: 'json',
      beforeSend: function() { $('#cnic-spinner').removeClass('d-none'); },
      complete: function() { $('#cnic-spinner').addClass('d-none'); },
      success: function(res) {
        if (res.exists && res.parent) {
          Object.keys(res.parent).forEach(function(k) { 
            if ($('#' + k).length) $('#' + k).val(res.parent[k]).trigger('change');
          });
          $('#parent_id').val(res.parent.parent_id);
          $('#children-info-container').remove();
          if (res.children && res.children.length) {
            let html = '<div id="children-info-container" class="mt-2"><small class="text-muted"><i class="fas fa-child me-1"></i> Existing children:</small><div class="children-list">';
            res.children.forEach(function(c) { 
              html += '<span class="badge text-bg-info me-1 mb-1"><i class="fas fa-user-graduate me-1"></i>' + escapeHtml(c.name) + ' (' + escapeHtml(c.class) + ')</span>'; 
            });
            html += '</div></div>';
            $('#father_cnic').closest('.ad-field').append(html);
          }
          toastr.success('Parent information loaded');
        }
      },
      error: function() { console.log('Error checking parent CNIC'); }
    });
  });
  
  // Sibling search
  let searchTimeout;
  $('#sibling_search').on('input', function() {
    const searchTerm = $(this).val().trim();
    clearTimeout(searchTimeout);
    if (searchTerm.length < 3) { $('#sibling-results').hide(); return; }
    searchTimeout = setTimeout(function() {
      $.ajax({
        url: '<?= site_url("admin/students/search_siblings") ?>',
        method: 'POST',
        data: (window.adminCsrfPayload || function (x) { return x; })({
          search: searchTerm,
          campus_id: $('#campus_id').val()
        }),
        dataType: 'json',
        beforeSend: function() { $('#sibling-results').show(); $('#sibling-list').html('<div class="list-group-item text-center"><i class="fas fa-spinner fa-spin"></i> Searching...</div>'); },
        success: function(res) {
          if (res.success && res.data && res.data.length) {
            let html = '';
            res.data.forEach(function(s) {
              html += '<a href="javascript:void(0)" class="list-group-item list-group-item-action sibling-result-item" data-parent-id="' + escapeHtml(s.parent_id) + '" data-father-cnic="' + escapeHtml(s.father_cnic) + '" data-father-name="' + escapeHtml(s.f_name) + '"><div><strong><i class="fas fa-user-graduate me-1"></i> ' + escapeHtml(s.student_name) + '</strong><br><small class="text-muted"><i class="fas fa-id-card me-1"></i>' + (s.father_cnic || 'No CNIC') + '</small></div></a>';
            });
            $('#sibling-list').html(html);
            $('.sibling-result-item').on('click', function() {
              const $item = $(this);
              $('#father_cnic').val($item.data('father-cnic')).trigger('blur');
              $('#f_name').val($item.data('father-name'));
              $('#selected-sibling-info').html('<div class="alert alert-info py-2 mb-0"><i class="fas fa-info-circle me-1"></i> Sibling loaded. Parent information populated.<button type="button" class="close float-none ms-2" id="removeSiblingBtn" style="font-size:14px;">&times;</button></div>');
              $('#sibling-results').hide();
              $('#sibling_search').val('');
              $('#removeSiblingBtn').on('click', function() { $('#selected-sibling-info').empty(); });
            });
          } else {
            $('#sibling-list').html('<div class="list-group-item text-muted"><i class="fas fa-user-slash me-1"></i> No students found</div>');
          }
        },
        error: function(xhr, status) {
          if (status === 'abort') return;
          if (window.refreshAdminCsrf) refreshAdminCsrf(xhr);
          $('#sibling-list').html('<div class="list-group-item text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Error searching siblings</div>');
        }
      });
    }, 500);
  });
  
  $('#clearSiblingBtn').on('click', function() { $('#sibling_search').val(''); $('#sibling-results').hide(); });
  $(document).on('click', function(e) { if (!$(e.target).closest('#sibling_search, #sibling-results').length) $('#sibling-results').hide(); });
  
  // Form validation
  if ($.fn.validate) {
    $('#student-admission-form').validate({
      rules: { full_name: 'required', father_cnic: 'required', f_name: 'required', date_of_birth: 'required', cls_sec_id: 'required', fee_issue_date: 'required', fee_due_date: 'required', gender: 'required' },
      messages: { full_name: 'Student full name is required', father_cnic: 'Father CNIC is required', f_name: 'Father name is required', date_of_birth: 'Date of birth is required', cls_sec_id: 'Class section is required', fee_issue_date: 'Fee issue date is required', fee_due_date: 'Fee due date is required', gender: 'Gender is required' },
      errorElement: 'div', errorClass: 'invalid-feedback',
      highlight: function(el) { $(el).addClass('is-invalid'); },
      unhighlight: function(el) { $(el).removeClass('is-invalid'); },
      submitHandler: function(form) {
        const fd = new FormData(form);
        $.ajax({
          url: $(form).attr('action'), type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
          beforeSend: function() { $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Processing Admission...'); },
          success: function(res) {
            if (res.success) {
              toastr.success(res.msg || 'Admission saved successfully');
              if (res.chalan_url) window.open(res.chalan_url, '_blank');
              setTimeout(function() { window.location.reload(); }, 1500);
            } else {
              toastr.error(res.msg || 'Save failed');
              $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save me-2"></i> Submit Admission');
            }
          },
          error: function() { toastr.error('An error occurred'); $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save me-2"></i> Submit Admission'); }
        });
      }
    });
  }
  
  // Field Customization
  const customizableFields = [
    { id: 'reg_no', label: 'Registration No', required: true, defaultVisible: true },
    { id: 'gr_no', label: 'G.R. Number', required: false, defaultVisible: true },
    { id: 'gr_date', label: 'G.R. Date', required: false, defaultVisible: true },
    { id: 'date_of_admission', label: 'Admission Date', required: false, defaultVisible: true },
    { id: 'full_name', label: 'Full Name', required: true, defaultVisible: true },
    { id: 'date_of_birth', label: 'Date of Birth', required: true, defaultVisible: true },
    { id: 'gender', label: 'Gender', required: true, defaultVisible: true },
    { id: 'student_cnic', label: 'CNIC / B-Form', required: false, defaultVisible: true },
    { id: 'sibling_search', label: 'Search Sibling', required: false, defaultVisible: true },
    { id: 'previous_school', label: 'Previous School', required: false, defaultVisible: true },
    { id: 'previous_school_city', label: 'Previous School City', required: false, defaultVisible: true },
    { id: 'health_condition', label: 'Health Condition', required: false, defaultVisible: true },
    { id: 'major_injuries', label: 'Major Injuries', required: false, defaultVisible: true },
    { id: 'father_cnic', label: 'Father\'s CNIC', required: true, defaultVisible: true },
    { id: 'f_name', label: 'Father\'s Full Name', required: true, defaultVisible: true },
    { id: 'father_contact', label: 'Father\'s Contact', required: false, defaultVisible: true },
    { id: 'father_email', label: 'Father\'s Email', required: false, defaultVisible: true },
    { id: 'father_occupation', label: 'Father\'s Occupation', required: false, defaultVisible: true },
    { id: 'father_office_address', label: 'Father\'s Office Address', required: false, defaultVisible: true },
    { id: 'm_name', label: 'Mother\'s Full Name', required: false, defaultVisible: true },
    { id: 'mother_contact', label: 'Mother\'s Contact', required: false, defaultVisible: true },
    { id: 'whatsapp_contact', label: 'WhatsApp Number', required: false, defaultVisible: true },
    { id: 'address_line1', label: 'Residential Address', required: false, defaultVisible: true },
    { id: 'city', label: 'City', required: false, defaultVisible: true },
    { id: 'hear_source', label: 'How did you hear about us?', required: false, defaultVisible: true },
    { id: 'emergency_contact_person', label: 'Emergency Contact Person', required: false, defaultVisible: true },
    { id: 'emergency_contact', label: 'Emergency Contact Number', required: false, defaultVisible: true },
    { id: 'a_address', label: 'Emergency Contact Address', required: false, defaultVisible: true }
  ];
  
  let fieldPreferences = {};
  function loadPreferences() {
    let savedPrefs = localStorage.getItem('admission_form_preferences');
    if (savedPrefs) { try { fieldPreferences = JSON.parse(savedPrefs); } catch(e) { fieldPreferences = {}; } }
  }
  function generateFieldList() {
    let html = '';
    customizableFields.forEach(field => {
      const isVisible = fieldPreferences[field.id] !== undefined ? fieldPreferences[field.id] : field.defaultVisible;
      html += `<div class="list-group-item d-flex justify-content-between align-items-center"><div><strong>${escapeHtml(field.label)}</strong>${field.required ? '<i class="fas fa-lock text-danger ms-2"></i>' : ''}${field.required ? '<small class="text-muted ms-2">(Required)</small>' : ''}</div><div class="form-check form-switch"><input type="checkbox" class="form-check-input field-toggle" id="toggle_${field.id}" data-field="${field.id}"${isVisible ? ' checked' : ''}${field.required ? ' disabled' : ''}><label class="form-check-label" for="toggle_${field.id}"><span class="toggle-status">${isVisible ? 'Visible' : 'Hidden'}</span></label></div></div>`;
    });
    $('#fieldList').html(html);
    $('.field-toggle').on('change', function() {
      const fieldId = $(this).data('field');
      const isChecked = $(this).prop('checked');
      $(this).siblings('.form-check-label').find('.toggle-status').text(isChecked ? 'Visible' : 'Hidden');
      fieldPreferences[fieldId] = isChecked;
    });
  }
  function applyVisibilityPreferences() {
    customizableFields.forEach(field => {
      const isVisible = fieldPreferences[field.id] !== undefined ? fieldPreferences[field.id] : field.defaultVisible;
      const $fieldElement = $(`.ad-field[data-field="${field.id}"]`);
      if ($fieldElement.length) { if (!isVisible && !field.required) $fieldElement.hide(); else $fieldElement.show(); }
    });
  }
  function savePreferences() { localStorage.setItem('admission_form_preferences', JSON.stringify(fieldPreferences)); applyVisibilityPreferences(); toastr.success('Form customization saved'); $('#customizeModal').modal('hide'); }
  function resetToDefault() { if (confirm('Reset all fields to default visibility?')) { fieldPreferences = {}; localStorage.removeItem('admission_form_preferences'); generateFieldList(); applyVisibilityPreferences(); toastr.success('Reset to default settings'); } }
  
  loadPreferences(); generateFieldList(); applyVisibilityPreferences();
  $('#btnSavePrefs').on('click', savePreferences);
  $('#resetPrefsBtn').on('click', resetToDefault);
  
  function escapeHtml(unsafe) { if (!unsafe) return ''; return unsafe.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#39;"); }
  
  <?php if (!empty($cls_sec_id)): ?>
  setTimeout(function() { $('#cls_sec_id').trigger('change'); }, 500);
  <?php endif; ?>
});
</script>
<?= $this->endSection() ?>