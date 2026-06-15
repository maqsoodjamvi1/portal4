<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<meta name="csrf-token" content="<?= csrf_hash() ?>">

<input type="hidden" id="campus_id" name="campus_id" value="<?= session('member_campusid') ?>">

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
        <div class="alert alert-success">
          <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        </div>
      <?php endif; ?>

      <!-- =========================
           Student Admission
           ========================= -->
      <div class="section-card">
        <div class="section-head">
          <h5 class="mb-0"><i class="fas fa-user-graduate text-primary me-2"></i> Student Admission</h5>
        </div>
        <div class="section-body">
          <!-- Row 1 -->
          <div class="row">
            <div class="col-12 col-md-3 mb-3 ad-field" data-field="reg_no">
              <label for="reg_no" class="mb-1 fw-semibold">
                <i class="fas fa-hashtag me-1 text-primary"></i> Registration No
              </label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                <input type="text" class="form-control form-control-sm" id="reg_no" name="reg_no"
                       value="<?= esc(old('reg_no', $reg_no ?? '')) ?>" readonly>
              </div>
            </div>

            <div class="col-12 col-md-3 mb-3 ad-field" data-field="gr_no">
              <label for="gr_no" class="mb-1 fw-semibold">
                <i class="far fa-id-card me-1 text-primary"></i> G.R. Number
              </label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="far fa-id-card"></i></span>
                <input type="text" class="form-control form-control-sm" id="gr_no" name="gr_no"
                       value="<?= esc(old('gr_no', $gr_no ?? '')) ?>" placeholder="School GR No" required>
              </div>
            </div>

            <div class="col-12 col-md-3 mb-3 ad-field" data-field="gr_date">
              <label for="gr_date" class="mb-1 fw-semibold">
                <i class="far fa-calendar-alt me-1 text-primary"></i> G.R. Date
              </label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                <input type="text" class="form-control form-control-sm datepicker" id="gr_date" name="gr_date"
                       value="<?= esc(old('gr_date', $gr_date ?? '')) ?>" placeholder="dd/mm/yyyy" autocomplete="off" required>
              </div>
            </div>

            <div class="col-12 col-md-3 mb-3 ad-field" data-field="date_of_admission">
              <label for="date_of_admission" class="mb-1 fw-semibold">
                <i class="far fa-calendar-check me-1 text-primary"></i> Admission Date
              </label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="far fa-calendar-check"></i></span>
                <input type="text" class="form-control form-control-sm datepicker" id="date_of_admission" name="date_of_admission"
                       value="<?= esc(old('date_of_admission', $date_of_admission ?? '')) ?>" placeholder="dd/mm/yyyy" autocomplete="off">
              </div>
            </div>
          </div>

          <!-- Row 2 -->
          <div class="row">
            <div class="col-md-3 mb-3 ad-field" data-field="full_name" data-required="1">
              <label for="full_name" class="mb-1 fw-semibold">
                <i class="fas fa-user me-1 text-primary"></i> Full Name
              </label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control form-control-sm" id="full_name" name="full_name" placeholder="Student full name" required>
              </div>
            </div>

            <div class="col-md-3 mb-3 ad-field" data-field="date_of_birth" data-required="1">
              <label for="date_of_birth" class="mb-1 fw-semibold">
                <i class="fas fa-birthday-cake me-1 text-primary"></i> Date of Birth
              </label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fas fa-birthday-cake"></i></span>
                <input type="text" class="form-control form-control-sm datepicker" id="date_of_birth" name="date_of_birth"
                       placeholder="dd/mm/yyyy" autocomplete="off" required>
              </div>
            </div>

            <div class="col-md-3 mb-3 ad-field" data-field="gender" data-required="1" data-label="Gender">
              <label class="mb-1 fw-semibold"><i class="fas fa-venus-mars me-1 text-primary"></i> Gender</label>
              <div class="btn-group btn-group-sm d-flex w-100" id="genderToggle" data-bs-toggle="buttons">
                <label class="btn btn-outline-primary flex-fill"><input type="radio" name="gender" id="gender_male" value="male" required> <i class="fas fa-mars me-1"></i> Male</label>
                <label class="btn btn-outline-info flex-fill"><input type="radio" name="gender" id="gender_female" value="female" required> <i class="fas fa-venus me-1"></i> Female</label>
              </div>
            </div>

            <div class="col-md-3 mb-3 ad-field" data-field="student_cnic">
              <label for="student_cnic" class="mb-1 fw-semibold">
                <i class="far fa-id-badge me-1 text-primary"></i> CNIC / B-Form
              </label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="far fa-id-badge"></i></span>
                <input type="text" class="form-control form-control-sm cnic-mask" id="student_cnic" name="student_cnic" placeholder="XXXXX-XXXXXXX-X">
              </div>
            </div>
          </div>

          <!-- Row 3 -->
          <div class="row">
            <div class="col-md-3 mb-3 ad-field" data-field="previous_school">
              <label class="mb-1 fw-semibold"><i class="fas fa-school me-1 text-primary"></i> Previous School</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fas fa-school"></i></span>
                <input type="text" class="form-control form-control-sm" id="previous_school" name="previous_school" placeholder="School name">
              </div>
            </div>

            <div class="col-md-3 mb-3 ad-field" data-field="previous_school_city">
              <label class="mb-1 fw-semibold"><i class="fas fa-city me-1 text-primary"></i> Previous School City</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fas fa-city"></i></span>
                <input type="text" class="form-control form-control-sm" id="previous_school_city" name="previous_school_city" placeholder="City">
              </div>
            </div>

            <div class="col-md-3 mb-3 ad-field" data-field="health_condition">
              <label class="mb-1 fw-semibold"><i class="fas fa-heartbeat me-1 text-primary"></i> Health Condition</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fas fa-heartbeat"></i></span>
                <input type="text" class="form-control form-control-sm" id="health_condition" name="health_condition" placeholder="e.g. Normal">
              </div>
            </div>

            <div class="col-md-3 mb-3 ad-field" data-field="major_injuries">
              <label class="mb-1 fw-semibold"><i class="fas fa-first-aid me-1 text-primary"></i> Major Injuries / Illness</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fas fa-first-aid"></i></span>
                <input type="text" class="form-control form-control-sm" id="major_injuries" name="major_injuries" placeholder="If any">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- =========================
           Parent / Guardian Info
           ========================= -->
      <div class="section-card">
        <div class="section-head">
          <h5 class="mb-0"><i class="fas fa-users text-info me-2"></i> Parent / Guardian Information</h5>
        </div>
        <div class="section-body">
          <div class="info-grid">
            <input type="hidden" id="parent_id" name="parent_id" value="">
            <!-- Father's CNIC -->
            <div class="ad-field" data-field="father_cnic" data-required="1">
              <label for="father_cnic" class="form-label required"><i class="fas fa-id-card me-1"></i> Father's CNIC</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                <input type="text" class="form-control form-control-sm cnic-mask parent-field" name="father_cnic" id="father_cnic"
                       value="<?= esc($father_cnic ?? '') ?>" placeholder="XXXXX-XXXXXXX-X" required>
                <span id="cnic-spinner" class="input-group-text d-none"><i class="fas fa-spinner fa-spin"></i></span>
              </div>
              <div id="children-info-container" class="mt-2"></div>
            </div>

            <?php
              $fields = [
                ['f_name', "Father's Full Name", $f_name ?? '', true],
                ['father_contact', "Father's Contact", $father_contact ?? ''],
                ['father_email', "Father's Email", $father_email ?? ''],
                ['father_occupation', "Father's Occupation", $father_occupation ?? ''],
                ['father_office_address', "Father's Office Address", $father_office_address ?? ''],
                ['m_name', "Mother's Full Name", $m_name ?? ''],
                ['mother_contact', "Mother's Contact", $mother_contact ?? ''],
                ['whatsapp_contact', "WhatsApp Number", $whatsapp_contact ?? ''],
                ['address_line1', "Residential Address", $address_line1 ?? ''],
                ['city', "City", $city ?? ''],
                ['hear_source', "How did you hear about us?", $hear_source ?? ''],
                ['emergency_contact_person', "Emergency Contact Person", $emergency_contact_person ?? ''],
                ['emergency_contact', "Emergency Contact Number", $emergency_contact ?? ''],
                ['a_address', "Emergency Contact Address", $a_address ?? ''],
              ];

              foreach ($fields as $field) {
                [$name, $label, $value, $required] = array_pad($field, 4, false);
                $icon = 'fas fa-info-circle'; $placeholder = '';
                switch ($name) {
                  case 'f_name': case 'm_name': $icon='fas fa-user'; break;
                  case 'father_contact': case 'mother_contact': case 'emergency_contact': $icon='fas fa-phone'; $placeholder='+92 XXX XXXXXXX'; break;
                  case 'father_email': $icon='fas fa-envelope'; $placeholder='example@domain.com'; break;
                  case 'father_occupation': $icon='fas fa-briefcase'; break;
                  case 'address_line1': case 'a_address': case 'father_office_address': $icon='fas fa-map-marker-alt'; break;
                  case 'city': $icon='fas fa-city'; break;
                  case 'hear_source': $icon='fas fa-bullhorn'; break;
                  case 'whatsapp_contact': $icon='fab fa-whatsapp'; $placeholder='+92 XXX XXXXXXX'; break;
                }
            ?>
              <div class="ad-field" data-field="<?= esc($name) ?>" <?= $required ? 'data-required="1"' : '' ?>>
                <label for="<?= esc($name) ?>" class="form-label <?= $required ? 'required' : '' ?>">
                  <i class="<?= esc($icon) ?> me-1"></i> <?= esc($label) ?>
                </label>

                <?php if (in_array($name, ['father_contact','mother_contact','emergency_contact','whatsapp_contact'], true)): ?>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="<?= esc($icon) ?>"></i></span>
                    <input type="text" class="form-control form-control-sm phone-mask parent-field"
                           name="<?= esc($name) ?>" id="<?= esc($name) ?>" value="<?= esc($value) ?>"
                           <?= $required ? 'required' : '' ?> placeholder="<?= esc($placeholder) ?>">
                  </div>
                <?php else: ?>
                  <input type="text" class="form-control form-control-sm parent-field"
                         name="<?= esc($name) ?>" id="<?= esc($name) ?>" value="<?= esc($value) ?>"
                         <?= $required ? 'required' : '' ?> placeholder="<?= esc($placeholder) ?>">
                <?php endif; ?>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>

      <!-- =========================
           Fee Structure & Financial
           ========================= -->
      <div class="section-card">
        <div class="section-head">
          <h5 class="mb-0"><i class="fas fa-receipt text-primary me-2"></i> Fee Structure & Financial</h5>
        </div>
        <div class="section-body">
          <div class="row five-cols align-items-end">
            <!-- 1) Class Section -->
            <div class="col mb-3">
              <label for="cls_sec_id" class="mb-1 fw-semibold"><i class="fas fa-layer-group me-1 text-primary"></i> Class Section</label>
              <select id="cls_sec_id" name="cls_sec_id" class="form-control form-control-sm select2" required>
                <option value="">-- Select --</option>
                <?php foreach ($sectionsclassinfo as $row): ?>
                  <option
                    value="<?= (int)$row['cls_sec_id'] ?>"
                    data-class-id="<?= (int)$row['class_id'] ?>"
                    data-section-id="<?= (int)$row['section_id'] ?>"
                    <?= ((int)$row['cls_sec_id'] === (int)($cls_sec_id ?? 0) ? 'selected' : '') ?>
                  ><?= esc($row['sectionclassname']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- 2) Fee Month -->
            <div class="col mb-3">
              <label for="fee_month_ui" class="mb-1 fw-semibold"><i class="far fa-calendar-alt me-1 text-primary"></i> Fee Month</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                <input id="fee_month_ui" type="text" class="form-control" placeholder="Select month" autocomplete="off">
                <input id="fee_month" name="fee_month" type="hidden" value="<?= esc($fee_month ?? '') ?>">
              </div>
            </div>

            <!-- 3) Issue Date -->
            <div class="col mb-3">
              <label for="fee_issue_date" class="mb-1 fw-semibold"><i class="far fa-calendar-check me-1 text-primary"></i> Issue Date</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="far fa-calendar-check"></i></span>
                <input type="text" class="form-control form-control-sm datepicker" id="fee_issue_date" name="fee_issue_date" placeholder="dd/mm/yyyy" autocomplete="off" required>
              </div>
            </div>

            <!-- 4) Due Date -->
            <div class="col mb-3">
              <label for="fee_due_date" class="mb-1 fw-semibold"><i class="far fa-calendar-minus me-1 text-primary"></i> Due Date</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="far fa-calendar-minus"></i></span>
                <input type="text" class="form-control form-control-sm datepicker" id="fee_due_date" name="fee_due_date" placeholder="dd/mm/yyyy" autocomplete="off" required>
              </div>
            </div>

            <!-- 5) Invoice No -->
            <div class="col mb-3">
              <label for="invoice_number_preview" class="mb-1 fw-semibold"><i class="far fa-file-alt me-1 text-primary"></i> Invoice No.</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="far fa-file-alt"></i></span>
                <input type="text" class="form-control form-control-sm" id="invoice_number_preview" placeholder="Auto" readonly>
                <input type="hidden" id="invoice_number" name="invoice_number">
              </div>
            </div>
          </div>

          <div class="table-responsive mt-3">
            <table class="table table-sm table-bordered">
              <thead class="table-light">
                <tr>
                  <th><span class="text-nowrap" title="Fee Type"><i class="fas fa-list me-1"></i> Fee</span></th>
                  <th width="20%"><span class="text-nowrap" title="Standard Amount"><i class="fas fa-money-bill me-1"></i> Std</span></th>
                  <th width="20%"><span class="text-nowrap" title="Payable Amount"><i class="fas fa-hand-holding-usd me-1"></i> Pay</span></th>
                  <th width="20%"><span class="text-nowrap" title="Discount / Adjustment"><i class="fas fa-tag me-1"></i> Disc</span></th>
                </tr>
              </thead>
              <tbody id="fee-type-container">
                <tr>
                  <td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-info-circle me-2"></i>Select class section to load fee structure</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- =========================
           Required Documents
           ========================= -->
      <?php $types = $attachementTypesInfo ?? []; ?>
      <?php if (!empty($types)): ?>
        <div class="section-card">
          <div class="section-head">
            <h5 class="mb-0"><i class="fas fa-file-upload text-warning me-2"></i> Required Documents</h5>
          </div>
          <div class="section-body">
            <?php
              $db = \Config\Database::connect();
              $rows = $db->table('attachements')
                         ->select('attachement_id, a_type_id, attachement_path')
                         ->where('student_id', $id ?? 0)->get()->getResultArray();
              $attachmentsByType = [];
              foreach ($rows as $r) { $attachmentsByType[(int)$r['a_type_id']] = (object)$r; }
            ?>

            <div class="row">
              <?php foreach ($types as $value):
                    $attachement = $attachmentsByType[(int)$value->a_type_id] ?? null; ?>
                <div class="col-md-6 col-lg-4 mb-4">
                  <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-light">
                      <h6 class="card-title mb-0 text-primary"><i class="fas fa-file me-2"></i><?= esc($value->a_type_name) ?></h6>
                    </div>
                    <div class="card-body text-center">
                      <input type="hidden" class="a_type_id" value="<?= $value->a_type_id ?>">
                      <input type="hidden" class="attachement_id" value="<?= $attachement->attachement_id ?? 0 ?>">

                      <div class="document-preview-container mb-3">
                        <img id="preview_<?= $value->a_type_id ?>"
                             src="<?= $attachement ? base_url('studentattachements/' . $attachement->attachement_path) : 'https://via.placeholder.com/300x200?text=Upload+Document' ?>"
                             class="attachment-preview img-thumbnail">
                      </div>

                      <div class="d-grid gap-2">
                        <input type="file" class="form-control d-none attachment-file" data-typeid="<?= $value->a_type_id ?>"
                               id="attachment_<?= $value->a_type_id ?>" accept="image/*,.pdf">
                        <button class="btn btn-sm btn-outline-primary" onclick="document.getElementById('attachment_<?= $value->a_type_id ?>').click()">
                          <i class="fas fa-upload me-2"></i>Upload Document
                        </button>

                        <?php if ($attachement): ?>
                          <a href="<?= base_url('studentattachements/' . $attachement->attachement_path) ?>" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-eye me-2"></i>View Document
                          </a>
                          <button class="btn btn-sm btn-outline-danger remove-document" data-typeid="<?= $value->a_type_id ?>">
                            <i class="fas fa-trash me-2"></i>Remove
                          </button>
                        <?php endif; ?>
                      </div>
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

      <!-- =========================
           Submit / Actions
           ========================= -->
      <div class="section-card">
        <div class="section-body">
          <div class="form-actions">
            <button type="submit" class="btn btn-success btn-action">
              <i class="fas fa-save me-2"></i> Submit Admission
            </button>

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
<div class="modal fade" id="customizeModal" tabindex="-1" role="dialog" aria-labelledby="customizeLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="customizeLabel"><i class="fas fa-sliders-h me-2"></i> Customize Admission Form</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <p class="text-muted mb-2">Show/hide fields. Items with a lock are always required.</p>
        <div id="fieldList" class="list-group"></div>
      </div>
      <div class="modal-footer">
        <button type="button" id="btnSavePrefs" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save preferences</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- =============== Libraries =============== -->
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

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/plugins/monthSelect/style.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/plugins/monthSelect/index.js"></script>

<!-- =============== Styles =============== -->
<style>
  .required:after { content: " *"; color: #dc3545; }
  .attachment-preview { max-height: 200px; object-fit: contain; border: 1px solid #dee2e6; background: #f8f9fa; }
  .document-preview-container { height: 180px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; border-radius: 4px; overflow: hidden; }
  
  .input-group-text { background: #f8f9fa; }
  .card .form-control-sm { min-height: 30px; }
  #genderToggle .btn { white-space: nowrap; }

  /* Section wrapper */
  .section-card { border: 1px solid #dee2e6; border-radius: .5rem; overflow: hidden; margin-bottom: 1rem; }
  .section-head { background:#f8f9fa; padding:.75rem 1rem; font-weight:600; border-bottom:1px solid #e9ecef; }
  .section-body { padding: 1rem; }

  /* Responsive grids */
  .five-cols{ display:grid; grid-template-columns: repeat(5, minmax(180px,1fr)); grid-gap:.75rem; }
  @media (max-width:1199.98px){ .five-cols{ grid-template-columns: repeat(3, minmax(200px,1fr)); } }
  @media (max-width:767.98px){ .five-cols{ grid-template-columns: repeat(2, minmax(0,1fr)); } }
  @media (max-width:479.98px){ .five-cols{ grid-template-columns: 1fr; } }

  .info-grid{ display:grid; grid-template-columns: repeat(3, minmax(240px,1fr)); grid-gap:.75rem 1rem; }
  @media (max-width:991.98px){ .info-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); } }
  @media (max-width:575.98px){ .info-grid{ grid-template-columns: 1fr; } }

  /* Keep inputs inside */
  .row, .row { flex-wrap: wrap; }
  .input-group { width:100%; min-width:0; }
  .input-group .form-control { min-width:0; }
  .select2 { width:100% !important; }

  /* Unified action buttons */
  .form-actions { display:flex; justify-content:center; gap:1rem; flex-wrap:wrap; }
  .btn-action { min-width:220px; height:44px; font-weight:600; border-radius:.45rem; display:inline-flex; align-items:center; justify-content:center; }
  .btn-action i { margin-right:.5rem; }

  /* Print */
  @media print {
    .no-print, .main-sidebar, .main-header, .main-footer, .modal { display:none !important; }
    .section-card { border-color:#aaa; }
    .section-head { background:#fff; border-bottom-color:#bbb; }
    .container-fluid { padding:0 !important; }
  }
</style>

<!-- =============== Scripts =============== -->
<script>
/* ---------- A) Field visibility (Customize) ---------- */
(function ($) {
  'use strict';
  const FORM_SELECTOR = '#student-admission-form';
  const CAMPUS_ID = $('#campus_id').val() || '0';
  const PREFS_KEY  = 'admissionFieldPrefs:' + CAMPUS_ID;

  function htmlEscape(s){ return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
  function getFieldBlocks() {
    const list = [];
    const $root = $(FORM_SELECTOR);
    const $scope = $root.length ? $root : $(document);
    $scope.find('.ad-field[data-field]').each(function () {
      const $el = $(this);
      const key = String($el.data('field') || '').trim();
      if (!key) return;
      const lockedRequired = String($el.data('required') || '') === '1';
      let title = $el.data('label') || $.trim($el.find('label').first().text()) || key.replace(/_/g,' ').replace(/\b\w/g, s => s.toUpperCase());
      list.push({ key, title, required: lockedRequired, $el });
    });
    return list;
  }
  function snapshot($block){
    if ($block.attr('data-snap') === '1') return;
    $block.find(':input').each(function(){
      $(this).attr('data-orig-required', this.required ? '1':'0').attr('data-orig-disabled', this.disabled ? '1':'0');
    });
    $block.attr('data-snap','1');
  }
  function setBlockVisible(block, show){
    const $b = block.$el; snapshot($b);
    if (show){
      $b.removeClass('d-none');
      $b.find(':input').each(function(){
        const origReq = $(this).attr('data-orig-required') === '1';
        const origDis = $(this).attr('data-orig-disabled') === '1';
        this.required = !!(origReq || block.required);
        this.disabled = !!origDis;
      });
      const anyOrigReq = $b.find(':input[data-orig-required="1"]').length > 0;
      $b.find('label').first().toggleClass('required', block.required || anyOrigReq);
    } else {
      $b.addClass('d-none');
      $b.find(':input').each(function(){ this.required = false; this.disabled = true; });
      $b.find('label').first().removeClass('required');
    }
  }
  function defaultPrefs(blocks){ return { visible: blocks.map(b => b.key) }; }
  function loadPrefs(blocks){ try{ const raw=localStorage.getItem(PREFS_KEY); if(!raw) return defaultPrefs(blocks); const p=JSON.parse(raw)||{}; return Array.isArray(p.visible)?p:defaultPrefs(blocks);}catch{return defaultPrefs(blocks);} }
  function savePrefs(prefs){
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(prefs)); } catch {}
    $.ajax({ url: '<?= site_url('admin/admission/field_prefs/save') ?>', method: 'POST', dataType: 'json',
      data: { campus_id: CAMPUS_ID, visible: JSON.stringify(prefs.visible), <?= csrf_token() ?>: '<?= csrf_hash() ?>' } });
  }
  function applyPrefs(prefs, blocks){ const requiredKeys = new Set(blocks.filter(b=>b.required).map(b=>b.key)); const chosen = new Set([].concat(prefs.visible||[], Array.from(requiredKeys))); blocks.forEach(b=>setBlockVisible(b, chosen.has(b.key))); }
  function renderList(blocks, prefs){
    const $list=$('#fieldList').empty(); const chosen=new Set(prefs.visible||[]);
    if(!blocks.length){ $list.append('<div class="text-muted small">No customizable fields were found.</div>'); return; }
    blocks.forEach(b=>{
      const id='cf_'+b.key.replace(/[^a-z0-9_:-]/gi,'_'); const checked=chosen.has(b.key); const disabled=b.required;
      const lockIcon=b.required?'<i class="fas fa-lock ms-2 text-muted" title="Always required"></i>':'';
      $list.append('<label class="list-group-item d-flex align-items-center">'+
        '<input type="checkbox" class="form-check-input me-2 fld-toggle" style="position:static;margin-right:.5rem" id="'+id+'" value="'+b.key+'" '+(checked?'checked':'')+' '+(disabled?'disabled':'')+'>' +
        '<span class="flex-grow-1">'+htmlEscape(b.title)+'</span>'+lockIcon+'</label>');
    });
  }
  $(function(){ const blocks=getFieldBlocks(); const prefs=loadPrefs(blocks); applyPrefs(prefs, blocks); });
  $('#customizeModal').on('show.bs.modal', function(){ const blocks=getFieldBlocks(); const prefs=loadPrefs(blocks); renderList(blocks, prefs); });
  $('#btnSavePrefs').on('click', function(){
    const blocks=getFieldBlocks(); const visible=[]; $('#fieldList .fld-toggle:checked').each(function(){ visible.push($(this).val()); });
    blocks.filter(b=>b.required).forEach(b=>{ if(!visible.includes(b.key)) visible.push(b.key); });
    const prefs={ visible }; savePrefs(prefs); applyPrefs(prefs, blocks);
    $('#customizeModal').modal && $('#customizeModal').modal('hide'); toastr.success('Your field visibility has been saved.');
  });
  new MutationObserver(function(){ const blocks=getFieldBlocks(); if(blocks.length){ applyPrefs(loadPrefs(blocks), blocks);} }).observe(document.body, { childList:true, subtree:true });
})(jQuery);

/* ---------- B) Existing logic + helpers ---------- */
(function ($) {
  'use strict';
  $(function () {
    function normalize(d){ return new Date(d.getFullYear(), d.getMonth(), d.getDate()); }
    function addDays(d,n){ const x=normalize(d); x.setDate(x.getDate()+n); return x; }
    const today=normalize(new Date()); const due10=addDays(today,10);

    var $group=$('#genderToggle'), $status=$('#genderStatus');
    function updateGenderStatus(){ var $c=$group.find('input[name="gender"]:checked'); $status && $status.length && $status.text('Selected: '+($c.length?$c.closest('label').text().trim():'—')); }
    if ($group.length && $status && $status.length){ $group.on('change','input[name="gender"]',updateGenderStatus); updateGenderStatus(); }

    /* Datepickers (Flatpickr) */
    (function(){
      function fmtDDMMYYYY(d){const dd=String(d.getDate()).padStart(2,'0'); const mm=String(d.getMonth()+1).padStart(2,'0'); const yyyy=d.getFullYear(); return `${dd}/${mm}/${yyyy}`;}
      if (window.flatpickr){
        const common={ dateFormat:'d/m/Y', allowInput:true, clickOpens:true, wrap:false };
        if (document.querySelector('#fee_issue_date')) flatpickr('#fee_issue_date', Object.assign({},common,{ defaultDate: today }));
        if (document.querySelector('#fee_due_date'))   flatpickr('#fee_due_date',   Object.assign({},common,{ defaultDate: due10 }));
        if (document.querySelector('#gr_date'))        flatpickr('#gr_date',        Object.assign({},common,{ defaultDate: document.querySelector('#gr_date').value?undefined:today }));
        if (document.querySelector('#date_of_admission')) flatpickr('#date_of_admission', Object.assign({},common,{ defaultDate: document.querySelector('#date_of_admission').value?undefined:today }));
        if (document.querySelector('#date_of_birth'))  flatpickr('#date_of_birth',  Object.assign({},common));
      } else {
        const setVal=(sel,val)=>{const el=document.querySelector(sel); if(!el) return; el.removeAttribute('readonly'); el.value=val;};
        setVal('#fee_issue_date', fmtDDMMYYYY(today)); setVal('#fee_due_date', fmtDDMMYYYY(due10));
        if (document.querySelector('#gr_date') && !document.querySelector('#gr_date').value) setVal('#gr_date', fmtDDMMYYYY(today));
        if (document.querySelector('#date_of_admission') && !document.querySelector('#date_of_admission').value) setVal('#date_of_admission', fmtDDMMYYYY(today));
      }
      (function setFeeMonth(){ const el=document.querySelector('#fee_month'); if(!el) return; const mm=String(today.getMonth()+1).padStart(2,'0'); if(!el.value) el.value=`${today.getFullYear()}-${mm}`; })();
    })();

    /* Month select */
    (function(){
      var ui=document.querySelector('#fee_month_ui'), hid=document.querySelector('#fee_month'); if(!ui||!hid) return;
      function toYYYYMM(d){ return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0'); }
      var pre=(hid.value && /^\d{4}-(0[1-9]|1[0-2])$/.test(hid.value))?hid.value:null;
      var defDate = pre ? new Date(parseInt(pre.slice(0,4),10), parseInt(pre.slice(5,7),10)-1, 1) : new Date(new Date().getFullYear(), new Date().getMonth(), 1);
      if (window.flatpickr && window.monthSelectPlugin){
        flatpickr(ui, {
          allowInput:true, dateFormat:'F Y', defaultDate:defDate,
          plugins:[ new monthSelectPlugin({ shorthand:false }) ],
          onChange:function(d){ if(d && d[0]) hid.value=toYYYYMM(d[0]); },
          onReady:function(d){ if(d && d[0]) hid.value=toYYYYMM(d[0]); else if(!hid.value) hid.value=toYYYYMM(defDate); }
        });
        ui.addEventListener('blur', function(){
          var v=(ui.value||'').trim(); var m=v.match(/^([A-Za-z]{3,9})\s+(\d{4})$/);
          if(m){ var idx=new Date(Date.parse(m[1]+" 1, 2000")).getMonth(); if(!isNaN(idx)) hid.value=m[2]+'-'+String(idx+1).padStart(2,'0'); }
        });
      } else {
        try { ui.type='month'; } catch(e){}
        ui.addEventListener('change', function(){ hid.value=ui.value; });
        if(!hid.value){ var y=defDate.getFullYear(), m=String(defDate.getMonth()+1).padStart(2,'0'); ui.value=y+'-'+m; hid.value=ui.value; }
      }
    })();

    /* Masks */
    if ($.fn.inputmask){ $('.cnic-mask').inputmask('99999-9999999-9'); $('.phone-mask').inputmask('+99 999 9999999'); }

    /* Document preview */
    $('.attachment-file').on('change', function(){
      const typeId=$(this).data('typeid'); const file=this.files[0]; if(!file) return;
      if(file.size>2*1024*1024){ toastr.error('File size should not exceed 2MB'); return; }
      const valid=['image/jpeg','image/png','application/pdf']; if(valid.indexOf(file.type)===-1){ toastr.error('Only JPG, PNG, and PDF files are allowed'); return; }
      const reader=new FileReader(); reader.onload=function(e){ $('#preview_'+typeId).attr('src', e.target.result); $('#attachment_'+typeId).closest('.card-body').find('.btn-outline-success, .remove-document').removeClass('d-none'); }; reader.readAsDataURL(file);
    });
    $('.remove-document').on('click', function(){ const typeId=$(this).data('typeid'); $('#preview_'+typeId).attr('src','https://via.placeholder.com/300x200?text=Upload+Document'); $('#attachment_'+typeId).val(''); $(this).closest('.card-body').find('.btn-outline-success, .remove-document').addClass('d-none'); });

    /* Age badge */
    var $ageBadge=$('#age-badge'); if(!$ageBadge.length && $('#date_of_birth').length){ $('#date_of_birth').closest('.mb-3, .form-group').append('<div id="age-badge" class="mt-2"></div>'); $ageBadge=$('#age-badge'); }
    function calcAge(d){ if(!d) return ''; var p=d.split('/'); if(p.length!==3) return ''; var dob=new Date(+p[2], p[1]-1, +p[0]); if(isNaN(dob.getTime())) return ''; var t=new Date(); var y=t.getFullYear()-dob.getFullYear(), m=t.getMonth()-dob.getMonth(), day=t.getDate()-dob.getDate(); if(day<0){ m--; day+=new Date(t.getFullYear(), t.getMonth(), 0).getDate(); } if(m<0){ y--; m+=12; } return y+' years, '+m+' months, '+day+' days'; }
    function updateAge(){ if(!$ageBadge.length) return; var age=calcAge($('#date_of_birth').val()); $ageBadge.html(age?('<span class="badge text-bg-primary text-white"><i class="fas fa-user-clock me-1"></i> Age: '+age+'</span>'):''); }
    $('#date_of_birth').on('change', updateAge); updateAge();

    /* Parent info by CNIC */
    $('#father_cnic').on('blur', function(){
      const cnic=$(this).val(), campus_id=$('#campus_id').val();
      if(!cnic || cnic.length<15) return; if(!campus_id){ toastr.error('Campus information is missing'); return; }
      $.ajax({
        url:'<?= site_url('admin/students/check_parent_cnic') ?>', method:'POST',
        data:{ cnic, campus_id, <?= csrf_token() ?>:'<?= csrf_hash() ?>' }, dataType:'json',
        beforeSend:function(){ $('#cnic-spinner').removeClass('d-none'); }, complete:function(){ $('#cnic-spinner').addClass('d-none'); },
        success:function(res){
          if(res.exists){
            Object.keys(res.parent).forEach(function(k){ var $f=$('#'+k); if($f.length){ $f.val(res.parent[k]).trigger('change'); }});
            $('#parent_id').val(res.parent.parent_id);
            $('#children-info-container').remove();
            if(res.children && res.children.length){
              var html='<div id="children-info-container" class="mt-2"><small class="text-muted d-block mb-1"><i class="fas fa-child me-1"></i> Existing children in our system:</small><div class="children-list">'+
                res.children.map(function(c){ return '<span class="badge text-bg-info text-dark me-1 mb-1"><i class="fas fa-user-graduate me-1"></i>'+c.name+' ('+c.class+')</span>'; }).join('') +
                '</div></div>';
              $('#father_cnic').closest('.ad-field').append(html);
            }
          }
        },
        error:function(xhr){ try{ var r=JSON.parse(xhr.responseText); toastr.error(r.error||'Error checking parent information'); }catch(e){ toastr.error('Error checking parent information. Please try again.'); } }
      });
    });

    /* Fee structure loader */
    $('#cls_sec_id').on('change', function(){
      const cls_sec_id=$(this).val(); if(!cls_sec_id) return;
      $.ajax({
        url:'<?= site_url("admin/ajax/get_class_fee_amounts") ?>', method:'POST',
        data:{ cls_sec_id: cls_sec_id, campus_id:'<?= (int)($sessionData['campusid'] ?? session("member_campusid")) ?>', session_id:'<?= (int)($sessionData['sessionid'] ?? session("member_sessionid")) ?>' },
        dataType:'json', cache:false,
        beforeSend:function(){ $('#fee-type-container').html('<tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 mb-0">Loading fee structure...</p></td></tr>'); },
        success:function(res){
          if(res.status==='success'){
            let html='', totalDefault=0, totalStudent=0;
            res.data.forEach(function(fee){
              const defAmt=parseFloat(fee.default_amount)||0; totalDefault+=defAmt; totalStudent+=defAmt;
              const rowClass=fee.is_monthly ? 'table-primary monthly-fee-row' : '';
              const icon = fee.is_monthly ? 'fa-calendar-alt' : getFeeTypeIcon(fee.fee_type_title);
              html += '<tr class="fee-row '+rowClass+'">'+
                '<td><i class="fas '+icon+' me-2"></i>'+fee.fee_type_title+(fee.is_monthly?' <span class="badge text-bg-info ms-2">Monthly</span>':'')+
                '<input type="hidden" name="fee_type_id[]" value="'+fee.fee_type_id+'"><input type="hidden" name="is_monthly[]" value="'+(fee.is_monthly?'1':'0')+'"></td>'+
                '<td><div class="input-group input-group-sm"><span class="input-group-text">Rs.</span><input type="number" class="form-control form-control-sm default-amount text-end" value="'+defAmt+'" readonly></div></td>'+
                '<td><div class="input-group input-group-sm"><span class="input-group-text">Rs.</span><input type="number" name="student_amount[]" class="form-control form-control-sm student-amount text-end" value="'+defAmt+'" data-default="'+defAmt+'"></div></td>'+
                '<td class="text-danger discount-info text-end">Rs. 0.00</td></tr>';
            });
            html += '<tr class="table-active"><th>Total Fees</th>'+
                    '<th class="text-end">Rs. <span class="total-default">'+totalDefault.toFixed(2)+'</span></th>'+
                    '<th class="text-end">Rs. <span class="total-student">'+totalStudent.toFixed(2)+'</span></th>'+
                    '<th class="text-danger text-end">Rs. <span class="total-discount">0.00</span></th></tr>';
            $('#fee-type-container').html(html);
            updateTotals();
            $('.student-amount').on('input', function(){
              const defVal=parseFloat($(this).data('default'))||0; const stuVal=parseFloat($(this).val())||0; const disc=defVal-stuVal;
              $(this).closest('.fee-row').find('.discount-info').html('Rs. '+disc.toFixed(2)); updateTotals();
            });
            toastr.success('Fee structure loaded successfully');
          } else {
            $('#fee-type-container').html('<tr><td colspan="4" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>'+(res.message||'Failed to load fee structure')+'</td></tr>');
            toastr.error(res.message || 'Failed to load fee structure for selected class');
          }
        },
        error:function(){ $('#fee-type-container').html('<tr><td colspan="4" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error loading fee structure</td></tr>'); toastr.error('Error loading fee structure. Please try again.'); }
      });
    });

    <?php if (!empty($cls_sec_id)): ?>
      $('#cls_sec_id').trigger('change');
    <?php endif; ?>

    function updateTotals(){
      let totalDefault=0, totalStudent=0;
      $('.fee-row').each(function(){ const d=parseFloat($(this).find('.default-amount').val())||0; const s=parseFloat($(this).find('.student-amount').val())||0; totalDefault+=d; totalStudent+=s; });
      const totalDiscount=totalDefault-totalStudent;
      $('.total-default').text(totalDefault.toFixed(2));
      $('.total-student').text(totalStudent.toFixed(2));
      $('.total-discount').text(totalDiscount.toFixed(2));
    }
    function getFeeTypeIcon(t){ t=String(t||'').toLowerCase(); if(t.includes('admission')) return 'fa-user-plus'; if(t.includes('exam')) return 'fa-file-alt'; if(t.includes('activity')) return 'fa-running'; if(t.includes('transport')) return 'fa-bus'; if(t.includes('uniform')) return 'fa-tshirt'; if(t.includes('book')) return 'fa-book'; return 'fa-money-bill-wave'; }

    /* Validate & submit */
    if ($.fn.validate){
      $('#student-admission-form').validate({
        rules:{ first_name:'required', father_cnic:'required', f_name:'required', date_of_birth:'required', section_id:'required', fee_issue_date:'required', fee_due_date:'required', fee_month:'required', gender:'required' },
        messages:{ first_name:'Student full name is required', father_cnic:'Father CNIC is required for verification', f_name:'Father name is required', date_of_admission:'Please select admission date', date_of_birth:'Please provide date of birth', section_id:'Please select class section', fee_issue_date:'Please select fee issue date', fee_due_date:'Please select fee due date', fee_month:'Please select fee month', gender:'Please select gender' },
        errorElement:'div', errorClass:'invalid-feedback',
        highlight:function(el){ $(el).addClass('is-invalid').removeClass('is-valid'); $(el).closest('.form-group').find('.input-group-text').addClass('border-danger'); },
        unhighlight:function(el){ $(el).removeClass('is-invalid').addClass('is-valid'); $(el).closest('.form-group').find('.input-group-text').removeClass('border-danger'); },
        errorPlacement:function(error, el){ if($(el).hasClass('datepicker')||$(el).hasClass('phone-mask')||$(el).hasClass('cnic-mask')){ error.insertAfter($(el).closest('.input-group')); } else { error.insertAfter(el); } },
        submitHandler:function(form){
          const fd=new FormData(form);
          $.ajax({
            url:$(form).attr('action'), type:'POST', data:fd, processData:false, contentType:false, dataType:'json',
            beforeSend:function(){ $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Processing Admission...'); },
            success:function(res){
              if(res.success){ toastr.success(res.msg || 'Saved'); if(res.pdf_url) window.open(res.pdf_url, '_blank'); setTimeout(function(){ window.location.reload(); }, 1500); }
              else { toastr.error(res.msg || 'Save failed'); $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save me-2"></i> Submit Admission'); }
            },
            error:function(xhr){ try{ var j=JSON.parse(xhr.responseText); toastr.error(j.message || 'An error occurred. Please try again.'); }catch(e){ toastr.error('An error occurred. Please try again.'); } $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save me-2"></i> Submit Admission'); }
          });
        }
      });
    }
  });
})(jQuery);
</script>

<?= $this->endSection() ?>
