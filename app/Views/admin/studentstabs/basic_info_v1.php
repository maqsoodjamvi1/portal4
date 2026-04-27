<meta name="csrf-token" content="<?= csrf_hash() ?>">

<input type="hidden" id="campus_id" name="campus_id" value="<?= session('member_campusid') ?>">
<?= form_open_multipart(
    site_url('admin/students/save_admission'),  // not base_url
    ['id' => 'student-admission-form', 'novalidate' => 'novalidate']
) ?>

<div class="container-fluid px-3">
  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white">
      <h5 class="mb-0"><i class="fas fa-user-graduate mr-2"></i> Student Admission Registration</h5>
      <button type="button" class="btn btn-light btn-sm" onclick="window.print();">
        <i class="fas fa-print"></i> Print / Save PDF
      </button>
    </div>

    <div class="card-body">
      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        </div>
      <?php endif; ?>

     
<div class="card card-primary card-outline shadow-sm">
  <div class="card-header py-2">
   <h3 class="card-title mb-0"><i class="fas fa-user-graduate mr-2"></i>Student Admission</h3>
</div>

<div class="card-body pt-3">

  <!-- Row 1: Registration no, G.R. no, GR Date, Admission Date -->
  <div class="form-row">
    <div class="col-12 col-md-3 mb-3 ad-field" data-field="reg_no" data-required="1" >
      <label for="reg_no" class="mb-1 font-weight-600">
        <i class="fas fa-hashtag mr-1 text-primary"></i> Registration No
      </label>
      <div class="input-group input-group-sm">
        <div class="input-group-prepend">
          <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
        </div>
        <input
          type="text"
          class="form-control form-control-sm"
          id="reg_no"
          name="reg_no"
          value="<?= esc(old('reg_no', $reg_no ?? '')) ?>"
          readonly
          
        >
      </div>
    </div>

    <div class="col-12 col-md-3 mb-3 ad-field" data-field="gr_no" >
      <label for="gr_no" class="mb-1 font-weight-600">
        <i class="far fa-id-card mr-1 text-primary"></i> G.R. Number
      </label>
      <div class="input-group input-group-sm">
        <div class="input-group-prepend">
          <span class="input-group-text"><i class="far fa-id-card"></i></span>
        </div>
        <input
          type="text"
          class="form-control form-control-sm"
          id="gr_no"
          name="gr_no"
          value="<?= esc(old('gr_no', $gr_no ?? '')) ?>"
          placeholder="School GR No"
          required
        >
      </div>
    </div>

    <div class="col-12 col-md-3 mb-3 ad-field" data-field="gr_date" >
      <label for="gr_date" class="mb-1 font-weight-600">
        <i class="far fa-calendar-alt mr-1 text-primary"></i> G.R. Date
      </label>
      <div class="input-group input-group-sm">
        <div class="input-group-prepend">
          <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
        </div>
        <input
          type="text"
          class="form-control form-control-sm datepicker"
          id="gr_date"
          name="gr_date"
          value="<?= esc(old('gr_date', $gr_date ?? '')) ?>"
          placeholder="dd/mm/yyyy"
          autocomplete="off"
          required
        >
      </div>
    </div>

    <div class="col-12 col-md-3 mb-3 ad-field" data-field="date_of_admission" data-required="1" >
      <label for="date_of_admission" class="mb-1 font-weight-600">
        <i class="far fa-calendar-check mr-1 text-primary"></i> Admission Date
      </label>
      <div class="input-group input-group-sm">
        <div class="input-group-prepend">
          <span class="input-group-text"><i class="far fa-calendar-check"></i></span>
        </div>
        <input
          type="text"
          class="form-control form-control-sm datepicker"
          id="date_of_admission"
          name="date_of_admission"
          value="<?= esc(old('date_of_admission', $date_of_admission ?? '')) ?>"
          placeholder="dd/mm/yyyy"
          autocomplete="off"
          
        >
      </div>
    </div>
  </div>

  <!-- Row 2: Full name, DOB, Gender, CNIC/B-Form -->
  <div class="row">
    <div class="col-md-3 mb-3 ad-field" data-field="full_name" data-required="1">
      <label for="full_name" class="mb-1 font-weight-600">
        <i class="fas fa-user mr-1 text-primary"></i> Full Name
      </label>
      <div class="input-group input-group-sm">
        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
        <input type="text" class="form-control form-control-sm" id="full_name" name="full_name" placeholder="Student full name" required>
      </div>
    </div>

    <div class="col-md-3 mb-3 ad-field" data-field="date_of_birth" data-required="1">
      <label for="date_of_birth" class="mb-1 font-weight-600">
        <i class="fas fa-birthday-cake mr-1 text-primary"></i> Date of Birth
      </label>
      <div class="input-group input-group-sm">
        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-birthday-cake"></i></span></div>
        <input type="text" class="form-control form-control-sm datepicker" id="date_of_birth" name="date_of_birth" placeholder="dd/mm/yyyy" autocomplete="off" required>
      </div>
    </div>

    <div class="col-md-3 mb-3 ad-field" data-field="gender" data-required="1"  data-label="Gender">
      <label class="mb-1 font-weight-600">
        <i class="fas fa-venus-mars mr-1 text-primary"></i> Gender
      </label>
      <div class="btn-group btn-group-sm d-flex w-100" id="genderToggle" data-toggle="buttons">
        <label class="btn btn-outline-primary flex-fill">
          <input type="radio" name="gender" id="gender_male" value="male" autocomplete="off" required>
          <i class="fas fa-mars mr-1"></i> Male
        </label>
        <label class="btn btn-outline-info flex-fill">
          <input type="radio" name="gender" id="gender_female" value="female" autocomplete="off" required>
          <i class="fas fa-venus mr-1"></i> Female
        </label>
      </div>
      
    </div>

    <div class="col-md-3 mb-3 ad-field" data-field="student_cnic">
      <label for="student_cnic" class="mb-1 font-weight-600">
        <i class="far fa-id-badge mr-1 text-primary"></i> CNIC / B-Form
      </label>
      <div class="input-group input-group-sm">
        <div class="input-group-prepend"><span class="input-group-text"><i class="far fa-id-badge"></i></span></div>
        <input type="text" class="form-control form-control-sm cnic-mask" id="student_cnic" name="student_cnic" placeholder="XXXXX-XXXXXXX-X">
      </div>
    </div>
  </div>

  <!-- Row 3: Previous school, city, health condition, major injuries/illness -->
  <div class="row">
    <div class="col-md-3 mb-3 ad-field" data-field="previous_school">
      <label for="previous_school" class="mb-1 font-weight-600">
        <i class="fas fa-school mr-1 text-primary"></i> Previous School
      </label>
      <div class="input-group input-group-sm">
        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-school"></i></span></div>
        <input type="text" class="form-control form-control-sm" id="previous_school" name="previous_school" placeholder="School name">
      </div>
    </div>

    <div class="col-md-3 mb-3 ad-field" data-field="previous_school_city">
      <label for="previous_school_city" class="mb-1 font-weight-600">
        <i class="fas fa-city mr-1 text-primary"></i> Previous School City
      </label>
      <div class="input-group input-group-sm">
        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-city"></i></span></div>
        <input type="text" class="form-control form-control-sm" id="previous_school_city" name="previous_school_city" placeholder="City">
      </div>
    </div>

    <div class="col-md-3 mb-3 ad-field" data-field="health_condition">
      <label for="health_condition" class="mb-1 font-weight-600">
        <i class="fas fa-heartbeat mr-1 text-primary"></i> Health Condition
      </label>
      <div class="input-group input-group-sm">
        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-heartbeat"></i></span></div>
        <input type="text" class="form-control form-control-sm" id="health_condition" name="health_condition" placeholder="e.g. Normal">
      </div>
    </div>

    <div class="col-md-3 mb-3 ad-field" data-field="major_injuries">
      <label for="major_injuries" class="mb-1 font-weight-600">
        <i class="fas fa-first-aid mr-1 text-primary"></i> Major Injuries / Illness
      </label>
      <div class="input-group input-group-sm">
        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-first-aid"></i></span></div>
        <input type="text" class="form-control form-control-sm" id="major_injuries" name="major_injuries" placeholder="If any">
      </div>
    </div>
  </div>

</div>
</div>

<!-- =========================
     Parent / Guardian Info
     ========================= -->
<h5 class="mb-3 text-info"><i class="fas fa-users mr-2"></i> Parent/Guardian Information</h5>

<div class="row">
  <input type="hidden" id="parent_id" name="parent_id" value="">

  <div class="col-md-4 mb-3 ad-field" data-field="father_cnic" data-required="1">
    <label for="father_cnic" class="form-label required">
      <i class="fas fa-id-card me-1"></i> Father's CNIC
    </label>
    <div class="input-group">
      <span class="input-group-text"><i class="fas fa-id-card"></i></span>
      <input type="text" required class="form-control form-control-sm cnic-mask parent-field" name="father_cnic" id="father_cnic" value="<?= esc($father_cnic) ?>" placeholder="XXXXX-XXXXXXX-X" >
      <span id="cnic-spinner" class="input-group-text d-none">
        <i class="fas fa-spinner fa-spin"></i>
      </span>
    </div>
    <div id="children-info-container" class="mt-2"></div>
  </div>

  <?php
    $fields = [
      ['f_name', 'Father\'s Full Name', $f_name ?? '', true],
      ['father_contact', 'Father\'s Contact', $father_contact ?? ''],
      ['father_email', 'Father\'s Email', $father_email ?? ''],
      ['father_occupation', 'Father\'s Occupation', $father_occupation ?? ''],
      ['father_office_address', 'Father\'s Office Address', $father_office_address ?? ''],
      ['m_name', 'Mother\'s Full Name', $m_name ?? ''],
      ['mother_contact', 'Mother\'s Contact', $mother_contact ?? ''],
      ['whatsapp_contact', 'WhatsApp Number', $whatsapp_contact ?? ''],
      ['address_line1', 'Residential Address', $address_line1 ?? ''],
      ['city', 'City', $city ?? ''],
      ['hear_source', 'How did you hear about us?', $hear_source ?? ''],
      ['emergency_contact_person', 'Emergency Contact Person', $emergency_contact_person ?? ''],
      ['emergency_contact', 'Emergency Contact Number', $emergency_contact ?? ''],
      ['a_address', 'Emergency Contact Address', $a_address ?? ''],
    ];

    foreach ($fields as $field) {
      [$name, $label, $value, $required] = array_pad($field, 4, false);
      $icon = '';
      $placeholder = '';

      switch($name) {
        case 'f_name':
        case 'm_name':
          $icon = 'fas fa-user';
          break;
        case 'father_contact':
        case 'mother_contact':
        case 'emergency_contact':
          $icon = 'fas fa-phone';
          $placeholder = '+92 XXX XXXXXXX';
          break;
        case 'father_email':
          $icon = 'fas fa-envelope';
          $placeholder = 'example@domain.com';
          break;
        case 'father_occupation':
          $icon = 'fas fa-briefcase';
          break;
        case 'address_line1':
        case 'a_address':
        case 'father_office_address':
          $icon = 'fas fa-map-marker-alt';
          break;
        case 'city':
          $icon = 'fas fa-city';
          break;
        case 'hear_source':
          $icon = 'fas fa-bullhorn';
          break;
        case 'whatsapp_contact':
          $icon = 'fab fa-whatsapp';
          $placeholder = '+92 XXX XXXXXXX';
          break;
        default:
          $icon = 'fas fa-info-circle';
      }
  ?>
  <div class="col-md-4 mb-3 ad-field" data-field="<?= $name ?>" <?= $required ? 'data-required="1"' : '' ?>>
    <label for="<?= $name ?>" class="form-label <?= $required ? 'required' : '' ?>">
      <i class="<?= $icon ?> me-1"></i> <?= $label ?>
    </label>
    <?php if(in_array($name, ['father_contact', 'mother_contact', 'emergency_contact', 'whatsapp_contact'])): ?>
      <div class="input-group">
        <span class="input-group-text"><i class="<?= $icon ?>"></i></span>
        <input type="text" class="form-control form-control-sm phone-mask parent-field" name="<?= $name ?>" id="<?= $name ?>"
               value="<?= esc($value) ?>" <?= $required ? 'required' : '' ?>
               placeholder="<?= $placeholder ?>" >
      </div>
    <?php else: ?>
      <input type="text" class="form-control form-control-sm parent-field" name="<?= $name ?>" id="<?= $name ?>"
             value="<?= esc($value) ?>" <?= $required ? 'required' : '' ?>
             placeholder="<?= $placeholder ?>" >
    <?php endif; ?>
  </div>
  <?php } ?>
</div>

<hr class="my-4">

 <!-- =========================
           Fee Structure & Financial
           ========================= -->

           <!-- Five-in-a-row: Class Section, Fee Month, Issue Date, Due Date, Invoice No -->
<div class="card card-primary card-outline shadow-sm">
  <div class="card-body py-3">

    <div class="row five-cols align-items-end">
      <!-- 1) Class Section -->
      <div class="col mb-3">
        <label for="section_id" class="mb-1 font-weight-600">
          <i class="fas fa-layer-group mr-1 text-primary"></i> Class Section
        </label>
        <select id="section_id" name="section_id" class="form-control form-control-sm select2" required>
          <option value="">-- Select --</option>
 <?php foreach ($sectionsclassinfo as $sectionvalue): ?>
              <option value="<?= $sectionvalue['section_id'] ?>" <?= ($sectionvalue['section_id'] == ($section_id ?? 0)) ? 'selected' : '' ?>>
                <?= esc($sectionvalue['sectionclassname']) ?>
              </option>
            <?php endforeach; ?>
          </select>
           </div>

      <!-- 2) Fee Month (YYYY-MM) -->
      <div class="col mb-3">
        <label for="fee_month" class="mb-1 font-weight-600">
          <i class="far fa-calendar-alt mr-1 text-primary"></i> Fee Month
        </label>
        <div class="input-group input-group-sm">
          <div class="input-group-prepend"><span class="input-group-text"><i class="far fa-calendar-alt"></i></span></div>
          <input type="month" class="form-control form-control-sm" id="fee_month" name="fee_month" required>
        </div>
      </div>

      <!-- 3) Issue Date (dd/mm/yyyy) -->
      <div class="col mb-3">
        <label for="fee_issue_date" class="mb-1 font-weight-600">
          <i class="far fa-calendar-check mr-1 text-primary"></i> Issue Date
        </label>
        <div class="input-group input-group-sm">
          <div class="input-group-prepend"><span class="input-group-text"><i class="far fa-calendar-check"></i></span></div>
          <input type="text" class="form-control form-control-sm datepicker" id="fee_issue_date" name="fee_issue_date" placeholder="dd/mm/yyyy" autocomplete="off" required>
        </div>
      </div>

      <!-- 4) Due Date (dd/mm/yyyy) -->
      <div class="col mb-3">
        <label for="fee_due_date" class="mb-1 font-weight-600">
          <i class="far fa-calendar-minus mr-1 text-primary"></i> Due Date
        </label>
        <div class="input-group input-group-sm">
          <div class="input-group-prepend"><span class="input-group-text"><i class="far fa-calendar-minus"></i></span></div>
          <input type="text" class="form-control form-control-sm datepicker" id="fee_due_date" name="fee_due_date" placeholder="dd/mm/yyyy" autocomplete="off" required>
        </div>
      </div>

      <!-- 5) Invoice Number (read-only preview + hidden real) -->
      <div class="col mb-3">
        <label for="invoice_number_preview" class="mb-1 font-weight-600">
          <i class="far fa-file-alt mr-1 text-primary"></i> Invoice No.
        </label>
        <div class="input-group input-group-sm">
          <div class="input-group-prepend"><span class="input-group-text"><i class="far fa-file-alt"></i></span></div>
          <input type="text" class="form-control form-control-sm" id="invoice_number_preview" placeholder="Auto" readonly>
          <input type="hidden" id="invoice_number" name="invoice_number">
        </div>
      </div>
    </div>

  </div>
</div>




      <div class="table-responsive">
        <table class="table table-sm table-bordered">
         <thead class="table-light">
  <tr>
    <th>
      <span class="text-nowrap" data-toggle="tooltip" data-bs-toggle="tooltip" title="Fee Type">
        <i class="fas fa-list me-1"></i> Fee
      </span>
    </th>
    <th width="20%">
      <span class="text-nowrap" data-toggle="tooltip" data-bs-toggle="tooltip" title="Standard Amount">
        <i class="fas fa-money-bill me-1"></i> Std
      </span>
    </th>
    <th width="20%">
      <span class="text-nowrap" data-toggle="tooltip" data-bs-toggle="tooltip" title="Payable Amount">
        <i class="fas fa-hand-holding-usd me-1"></i> Pay
      </span>
    </th>
    <th width="20%">
      <span class="text-nowrap" data-toggle="tooltip" data-bs-toggle="tooltip" title="Discount / Adjustment">
        <i class="fas fa-tag me-1"></i> Disc
      </span>
    </th>
  </tr>
</thead>
          <tbody id="fee-type-container">
            <tr>
              <td colspan="4" class="text-center py-4 text-muted">
                <i class="fas fa-info-circle me-2"></i>Select class section to load fee structure
              </td>
            </tr>
          </tbody>
        </table>
      </div>
<?php
// Only show the section if there are attachment types
$types = $attachementTypesInfo ?? [];
if (!empty($types)): 
?>

<hr class="my-4">
<h5 class="mb-3 text-warning"><i class="fas fa-file-upload mr-2"></i> Required Documents</h5>

<?php
  // Preload existing attachments for this student and index by a_type_id
  $db = \Config\Database::connect();
  $rows = $db->table('attachements')
            ->select('attachement_id, a_type_id, attachement_path')
            ->where('student_id', $id ?? 0)
            ->get()
            ->getResultArray();

  $attachmentsByType = [];
  foreach ($rows as $r) {
      $attachmentsByType[(int)$r['a_type_id']] = (object) $r;
  }
?>

<div class="row">
  <?php foreach ($types as $value):
        $attachement = $attachmentsByType[(int)$value->a_type_id] ?? null;
  ?>
  <div class="col-md-6 col-lg-4 mb-4">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-header bg-light">
        <h6 class="card-title mb-0 text-primary">
          <i class="fas fa-file me-2"></i><?= esc($value->a_type_name) ?>
        </h6>
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
          <input type="file" class="form-control d-none attachment-file"
                 data-typeid="<?= $value->a_type_id ?>"
                 id="attachment_<?= $value->a_type_id ?>" accept="image/*,.pdf">
          <button class="btn btn-sm btn-outline-primary"
                  onclick="document.getElementById('attachment_<?= $value->a_type_id ?>').click()">
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
        <small class="text-muted">
          <i class="fas fa-info-circle me-1"></i>
          <?= $value->description ?? 'Required for admission processing' ?>
        </small>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php endif; // end: show section only when there are types ?>
      <!-- =========================
           Submit / Actions
           ========================= -->
      <div class="card mt-4 border-secondary">
        <div class="card-body text-center">
         

          <div class="d-flex justify-content-center flex-wrap">
            <button type="submit" class="btn btn-success px-4 py-2 me-3 mb-2">
              <i class="fas fa-save me-2"></i>Submit Admission
            </button>
           <button type="button"
        id="btnNewAdmission"
        class="btn btn-outline-secondary px-4 py-2 me-3 mb-2"
        data-new-url="<?= base_url('admin/students/add'); ?>">
  <i class="fas fa-undo me-2"></i>New Admission
</button>

<button type="button" id="btnCustomizeForm" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#customizeModal">
  <i class="fas fa-sliders-h"></i> Customize form
</button>

<!-- Customize modal -->
<div class="modal fade" id="customizeModal" tabindex="-1" role="dialog" aria-labelledby="customizeLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="customizeLabel"><i class="fas fa-sliders-h mr-2"></i> Customize Admission Form</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <p class="text-muted mb-2">Show/hide fields. Items with a lock are always required.</p>
        <div id="fieldList" class="list-group"></div>
      </div>
      <div class="modal-footer">
        <button type="button" id="btnSavePrefs" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save preferences</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

            <?php if (isset($student_id) && !empty($student_id)): ?>
              <a href="<?= base_url('admin/students_print/print_admission_form/' . $student_id) ?>"
                 class="btn btn-outline-secondary px-4 py-2 me-3 mb-2" target="_blank">
                <i class="fas fa-print me-2"></i>Print Form
              </a>
            <?php else: ?>
              <button class="btn btn-outline-secondary px-4 py-2 me-3 mb-2" disabled>
                <i class="fas fa-print me-2"></i>Print Form
              </button>
            <?php endif; ?>

            <a href="<?= base_url('admin/students') ?>" class="btn btn-outline-danger px-4 py-2 mb-2">
              <i class="fas fa-times me-2"></i>Cancel
            </a>
          </div>

          <div class="mt-3 text-muted">
            <small><i class="fas fa-lock me-1"></i> Your information is secure and protected</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= form_close() ?>



<!-- Include necessary JS libraries -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/4.0.9/jquery.inputmask.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<!-- Bootstrap 4.6 JS bundle (Popper included) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom Styles -->
<style>
.required:after {
    content: " *";
    color: #dc3545;
}

.accordion-button {
    font-weight: 500;
}

.attachment-preview {
    max-height: 200px;
    object-fit: contain;
    border: 1px solid #dee2e6;
    background: #f8f9fa;
}

.document-preview-container {
    height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 4px;
    overflow: hidden;
}

.badge-custom {
    padding: 0.5em 0.75em;
    font-size: 0.85em;
}

.children-info {
    border-left: 3px solid #0dcaf0;
    font-size: 0.85rem;
    padding: 0.75rem;
    background-color: #f8f9fa;
}

.children-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.child-badge {
    font-size: 0.75rem;
    font-weight: normal;
    white-space: nowrap;
    padding: 0.35em 0.65em;
}

.bg-light-primary {
    background-color: #e7f5ff;
}

.bg-light-success {
    background-color: #ebfbee;
}

.bg-light-info {
    background-color: #e7f5ff;
}

.bg-light-warning {
    background-color: #fff9db;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .document-preview-container {
        height: 150px;
    }
}

 

  .font-weight-600 { font-weight: 600; }
  .input-group-text { background: #f8f9fa; }
  .card .form-control-sm { min-height: 30px; }
  #genderToggle .btn { white-space: nowrap; }
</style>


<script>
/* ============================================================
   A) FIELD VISIBILITY PREFERENCES (Customize Fields) — USE EXISTING MODAL
============================================================ */
(function ($) {
  'use strict';

  const FORM_SELECTOR = '#student-admission-form';
  const CAMPUS_ID = $('#campus_id').val() || '0';
  const PREFS_KEY  = 'admissionFieldPrefs:' + CAMPUS_ID;

  // -------- utils --------
  function htmlEscape(s){ return String(s ?? '')
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;')
    .replace(/'/g,'&#39;'); }

  // Find all field blocks; prefer inside form, fallback to document (so we never get [])
  function getFieldBlocks() {
    const list = [];
    const $root = $(FORM_SELECTOR);
    const $scope = $root.length ? $root : $(document);
    $scope.find('.ad-field[data-field]').each(function () {
      const $el = $(this);
      const key = String($el.data('field') || '').trim();
      if (!key) return;

      const lockedRequired = String($el.data('required') || '') === '1';

      let title = $el.data('label');
      if (!title) title = $.trim($el.find('label').first().text());
      if (!title) title = key.replace(/_/g,' ').replace(/\b\w/g, s => s.toUpperCase());

      list.push({ key, title, required: lockedRequired, $el });
    });
    return list;
  }

  // Remember original required/disabled flags once
  function snapshot($block){
    if ($block.attr('data-snap') === '1') return;
    $block.find(':input').each(function(){
      $(this)
        .attr('data-orig-required', this.required ? '1':'0')
        .attr('data-orig-disabled', this.disabled ? '1':'0');
    });
    $block.attr('data-snap','1');
  }

  // Show/hide a block and toggle required/disabled correctly
  function setBlockVisible(block, show){
    const $b = block.$el;
    snapshot($b);

    if (show){
      $b.removeClass('d-none');
      $b.find(':input').each(function(){
        const origReq = $(this).attr('data-orig-required') === '1';
        const origDis = $(this).attr('data-orig-disabled') === '1';
        // Required if originally required OR field is locked-required
        this.required = !!(origReq || block.required);
        // Restore original disabled state (not disabled by our toggle)
        this.disabled = !!origDis;
      });
      const anyOrigReq = $b.find(':input[data-orig-required="1"]').length > 0;
      $b.find('label').first().toggleClass('required', block.required || anyOrigReq);
    } else {
      $b.addClass('d-none');
      $b.find(':input').each(function(){
        this.required = false;   // don’t block client validation
        this.disabled = true;    // don’t submit hidden values
      });
      $b.find('label').first().removeClass('required');
    }
  }

  function defaultPrefs(blocks){ return { visible: blocks.map(b => b.key) }; }

  function loadPrefs(blocks){
    try {
      const raw = localStorage.getItem(PREFS_KEY);
      if (!raw) return defaultPrefs(blocks);
      const p = JSON.parse(raw);
      if (!p || !Array.isArray(p.visible)) return defaultPrefs(blocks);
      return p;
    } catch { return defaultPrefs(blocks); }
  }

  function savePrefs(prefs){
    try { localStorage.setItem(PREFS_KEY, JSON.stringify(prefs)); } catch {}
    // Optional: persist to server (safe to keep; ignore errors)
    $.ajax({
      url: '<?= site_url('admin/admission/field_prefs/save') ?>',
      method: 'POST',
      dataType: 'json',
      data: {
        campus_id: CAMPUS_ID,
        visible: JSON.stringify(prefs.visible),
        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
      }
    });
  }

  function applyPrefs(prefs, blocks){
    const requiredKeys = new Set(blocks.filter(b => b.required).map(b => b.key));
    const chosen = new Set([].concat(prefs.visible || [], Array.from(requiredKeys)));
    blocks.forEach(b => setBlockVisible(b, chosen.has(b.key)));
  }

  // Build the checklist into #fieldList inside #customizeModal
  function renderList(blocks, prefs){
    const $list = $('#fieldList').empty();
    const chosen = new Set(prefs.visible || []);

    if (!blocks.length){
      $list.append('<div class="text-muted small">No customizable fields were found.</div>');
      return;
    }

    blocks.forEach(b => {
      const id = 'cf_' + b.key.replace(/[^a-z0-9_:-]/gi,'_');
      const checked  = chosen.has(b.key);
      const disabled = b.required;
      const lockIcon = b.required ? '<i class="fas fa-lock ml-2 text-muted" title="Always required"></i>' : '';
      // BS4/BS5 neutral markup: form-check works in both
      $list.append(
        '<label class="list-group-item d-flex align-items-center">' +
          '<input type="checkbox" class="form-check-input mr-2 fld-toggle" ' +
                  'style="position:static;margin-right:.5rem" ' +
                  'id="'+id+'" value="'+b.key+'" '+(checked?'checked':'')+' '+(disabled?'disabled':'')+'>' +
          '<span class="flex-grow-1">'+htmlEscape(b.title)+'</span>' +
          lockIcon +
        '</label>'
      );
    });
  }

  // Apply prefs immediately on load (so hidden fields are hidden on first paint)
  $(function(){
    const blocks = getFieldBlocks();
    const prefs  = loadPrefs(blocks);
    applyPrefs(prefs, blocks);
    // Helpful debug:
    console.log('Customize (init): found blocks =>', blocks.map(b => b.key));
  });

  // Rebuild the list every time the modal is opened (so late DOM is handled too)
  $('#customizeModal').on('show.bs.modal', function(){
    const blocks = getFieldBlocks();
    const prefs  = loadPrefs(blocks);
    renderList(blocks, prefs);
    console.log('Customize (open): found blocks =>', blocks.map(b => b.key));
  });

  // Save button inside your modal
  $('#btnSavePrefs').on('click', function(){
    const blocks = getFieldBlocks();
    const visible = [];

    // Read checkboxes from the modal
    $('#fieldList .fld-toggle:checked').each(function(){ visible.push($(this).val()); });

    // Force-include locked required
    blocks.filter(b => b.required).forEach(b => { if (!visible.includes(b.key)) visible.push(b.key); });

    const prefs = { visible };
    savePrefs(prefs);
    applyPrefs(prefs, blocks);

    // Close the modal (works for BS4 & BS5 if JS is loaded)
    $('#customizeModal').modal ? $('#customizeModal').modal('hide') : null;
    // (If BS5-only: new bootstrap.Modal(document.getElementById('customizeModal')).hide();)
    toastr.success('Your field visibility has been saved.');
  });

  // As a safety net, if fields are injected later (AJAX), re-apply prefs once
  const mo = new MutationObserver(function(){
    const blocks = getFieldBlocks();
    if (blocks.length){
      applyPrefs(loadPrefs(blocks), blocks);
    }
  });
  mo.observe(document.body, { childList:true, subtree:true });

})(jQuery);
/* ============================================================
   B) YOUR EXISTING LOGIC (unchanged)
============================================================ */
(function ($) {
  'use strict';

  $(function () {
    // ---------------------------
    // Helpers
    // ---------------------------
    function normalize(d) { return new Date(d.getFullYear(), d.getMonth(), d.getDate()); }
    function addDays(d, n) { const x = normalize(d); x.setDate(x.getDate() + n); return x; }
    function fmt(d) {
      const dd = String(d.getDate()).padStart(2, '0');
      const mm = String(d.getMonth() + 1).padStart(2, '0');
      return dd + '/' + mm + '/' + d.getFullYear();
    }

    const today = normalize(new Date());
    const due10 = addDays(today, 10);

    // ---------------------------
    // Gender toggle status
    // ---------------------------
    var $group = $('#genderToggle');
    var $status = $('#genderStatus');

    function updateGenderStatus() {
      var $checked = $group.find('input[name="gender"]:checked');
      if ($checked.length) {
        var labelText = $checked.closest('label').text().replace(/\s+/g, ' ').trim();
        $status.text('Selected: ' + (labelText || '—'));
      } else {
        $status.text('Selected: —');
      }
    }
    if ($group.length && $status.length) {
      $group.on('change', 'input[name="gender"]', updateGenderStatus);
      updateGenderStatus();
    }

    // ---------------------------
    // New Admission button
    // ---------------------------
    var $form = $('#admissionForm'); // change if your form uses a different id

    $(document).on('click', '#btnNewAdmission', function (e) {
      e.preventDefault();
      try { sessionStorage.removeItem('admissionForm'); localStorage.removeItem('admissionForm'); } catch (_) {}
      var url = $(this).data('new-url') || $('#newAdmissionUrl').val() || null;
      if (!url) {
        var base = location.origin + location.pathname.split('?')[0].split('#')[0];
        url = base.replace(/\/edit(\/\d+)?$/i, '/add');
      }
      window.location.assign(url);
    });

    if ($form.length) {
      $form.on('reset', function (e) {
        e.preventDefault();
        $('#btnNewAdmission').trigger('click');
      });
    }

    // ---------------------------
    // Datepickers & defaults
    // ---------------------------
    if ($.fn.datepicker) {
      var common = { format: 'dd/mm/yyyy', autoclose: true, todayHighlight: true };

      // Issue Date (max = today)
      if ($('#fee_issue_date').data('datepicker')) $('#fee_issue_date').datepicker('destroy');
      $('#fee_issue_date').datepicker($.extend({}, common, { endDate: today }))
        .attr('readonly', 'readonly')
        .datepicker('setDate', today);

      // Due Date (start at today, allow future)
      if ($('#fee_due_date').data('datepicker')) $('#fee_due_date').datepicker('destroy');
      $('#fee_due_date').datepicker($.extend({}, common, { startDate: today }))
        .attr('readonly', 'readonly')
        .datepicker('setDate', due10)
        .datepicker('setEndDate', null);

      // GR Date
      if ($('#gr_date').length) {
        if ($('#gr_date').data('datepicker')) $('#gr_date').datepicker('destroy');
        $('#gr_date').datepicker(common).attr('readonly', 'readonly');
        if (!$('#gr_date').val()) $('#gr_date').datepicker('setDate', today);
      }

      // Admission Date
      if ($('#date_of_admission').length) {
        if ($('#date_of_admission').data('datepicker')) $('#date_of_admission').datepicker('destroy');
        $('#date_of_admission').datepicker(common).attr('readonly', 'readonly');
        if (!$('#date_of_admission').val()) $('#date_of_admission').datepicker('setDate', today);
      }

      // DOB (cannot be in the future)
      if ($('#date_of_birth').length) {
        if ($('#date_of_birth').data('datepicker')) $('#date_of_birth').datepicker('destroy');
        $('#date_of_birth').datepicker($.extend({}, common, { endDate: today }))
          .attr('readonly', 'readonly');
      }
    } else {
      // Fallback: set raw values if datepicker plugin isn't present
      $('#fee_issue_date').val(fmt(today));
      $('#fee_due_date').val(fmt(due10));
      if (!$('#gr_date').val()) $('#gr_date').val(fmt(today));
      if (!$('#date_of_admission').val()) $('#date_of_admission').val(fmt(today));
    }

    // Fee month = current month (YYYY-MM)
    (function setFeeMonth() {
      const mm = String(today.getMonth() + 1).padStart(2, '0');
      $('#fee_month').val(today.getFullYear() + '-' + mm);
    })();

    // ---------------------------
    // Input masks
    // ---------------------------
    if ($.fn.inputmask) {
      $('.cnic-mask').inputmask('99999-9999999-9');
      $('.phone-mask').inputmask('+99 999 9999999');
    }

    // ---------------------------
    // Document preview handlers
    // ---------------------------
    $('.attachment-file').on('change', function () {
      const typeId = $(this).data('typeid');
      const file = this.files[0];
      if (!file) return;

      if (file.size > 2 * 1024 * 1024) { toastr.error('File size should not exceed 2MB'); return; }
      const valid = ['image/jpeg', 'image/png', 'application/pdf'];
      if (valid.indexOf(file.type) === -1) { toastr.error('Only JPG, PNG, and PDF files are allowed'); return; }

      const reader = new FileReader();
      reader.onload = function (e) {
        $('#preview_' + typeId).attr('src', e.target.result);
        $('#attachment_' + typeId).closest('.card-body').find('.btn-outline-success, .remove-document').removeClass('d-none');
      };
      reader.readAsDataURL(file);
    });

    $('.remove-document').on('click', function () {
      const typeId = $(this).data('typeid');
      $('#preview_' + typeId).attr('src', 'https://via.placeholder.com/300x200?text=Upload+Document');
      $('#attachment_' + typeId).val('');
      $(this).closest('.card-body').find('.btn-outline-success, .remove-document').addClass('d-none');
    });

    // ---------------------------
    // Age calculation (DOB)
    // ---------------------------
    var $ageBadge = $('#age-badge');
    if (!$ageBadge.length && $('#date_of_birth').length) {
      $('#date_of_birth').closest('.mb-3, .form-group').append('<div id="age-badge" class="mt-2"></div>');
      $ageBadge = $('#age-badge');
    }

    function calculateAge(dobStr) {
      if (!dobStr) return '';
      var parts = dobStr.split('/');
      if (parts.length !== 3) return '';
      var dob = new Date(+parts[2], parts[1] - 1, +parts[0]);
      if (isNaN(dob.getTime())) return '';

      var t = new Date();
      var years = t.getFullYear() - dob.getFullYear();
      var months = t.getMonth() - dob.getMonth();
      var days = t.getDate() - dob.getDate();

      if (days < 0) {
        months--;
        var lastMonthDays = new Date(t.getFullYear(), t.getMonth(), 0).getDate();
        days += lastMonthDays;
      }
      if (months < 0) { years--; months += 12; }

      return years + ' year' + (years !== 1 ? 's' : '') + ', ' +
             months + ' month' + (months !== 1 ? 's' : '') + ', ' +
             days + ' day' + (days !== 1 ? 's' : '');
    }

    function updateAgeBadge() {
      if (!$ageBadge.length) return;
      var dob = $('#date_of_birth').val();
      var age = calculateAge(dob);
      if (age) {
        $ageBadge.html(
          '<span class="badge bg-primary text-white"><i class="fas fa-user-clock me-1"></i> Age: ' + age + '</span>'
        );
      } else {
        $ageBadge.empty();
      }
    }

    $('#date_of_birth').on('change', updateAgeBadge);
    $('#date_of_birth').on('changeDate', updateAgeBadge);
    updateAgeBadge();

    // ---------------------------
    // Parent info by CNIC
    // ---------------------------
    $('#father_cnic').on('blur', function () {
      const cnic = $(this).val();
      const campus_id = $('#campus_id').val();

      if (!cnic || cnic.length < 15) return;
      if (!campus_id) { toastr.error('Campus information is missing'); return; }

      $.ajax({
        url: '<?= site_url('admin/students/check_parent_cnic') ?>',
        method: 'POST',
        data: {
          cnic: cnic,
          campus_id: campus_id,
          <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        beforeSend: function () { $('#cnic-spinner').removeClass('d-none'); },
        complete: function () { $('#cnic-spinner').addClass('d-none'); },
        success: function (res) {
          if (res.exists) {
            Object.keys(res.parent).forEach(function (key) {
              var $f = $('#' + key);
              if ($f.length) { $f.val(res.parent[key]).trigger('change'); }
            });
            $('#parent_id').val(res.parent.parent_id);

            $('#children-info-container').remove();
            if (res.children && res.children.length) {
              var html = '<div id="children-info-container" class="mt-2">' +
                '<small class="text-muted d-block mb-1"><i class="fas fa-child me-1"></i> Existing children in our system:</small>' +
                '<div class="children-list">' +
                res.children.map(function (child) {
                  return '<span class="child-badge badge bg-info text-dark"><i class="fas fa-user-graduate me-1"></i>' +
                    child.name + ' (' + child.class + ')' +
                    '</span>';
                }).join('') +
                '</div></div>';
              $('#father_cnic').closest('.mb-3, .form-group').append(html);
            }
          }
        },
        error: function (xhr) {
          try {
            const response = JSON.parse(xhr.responseText);
            toastr.error(response.error || 'Error checking parent information');
          } catch (e) {
            toastr.error('Error checking parent information. Please try again.');
          }
        }
      });
    });

    // ---------------------------
    // Load fee structure on section change
    // ---------------------------
    $('#section_id').on('change', function () {
      const cls_sec_id = $(this).val();
      if (!cls_sec_id) return;

      $.ajax({
        url: '<?= site_url("admin/ajax/get_class_fee_amounts") ?>',
        method: 'POST',
        data: { cls_sec_id: cls_sec_id },
        dataType: 'json',
        beforeSend: function () {
          $('#fee-type-container').html(
            '<tr><td colspan="4" class="text-center py-4">' +
              '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>' +
              '<p class="mt-2 mb-0">Loading fee structure...</p></td></tr>'
          );
        },
        success: function (res) {
          if (res.status === 'success') {
            let html = '', totalDefault = 0, totalStudent = 0;

            res.data.forEach(function (fee) {
              const defAmt = parseFloat(fee.default_amount) || 0;
              totalDefault += defAmt;
              totalStudent += defAmt;

              const rowClass = fee.is_monthly ? 'table-primary monthly-fee-row' : '';
              const icon = fee.is_monthly ? 'fa-calendar-alt' : getFeeTypeIcon(fee.fee_type_title);

              html +=
                '<tr class="fee-row ' + rowClass + '">' +
                  '<td><i class="fas ' + icon + ' me-2"></i>' + fee.fee_type_title +
                    (fee.is_monthly ? ' <span class="badge bg-info ms-2">Monthly</span>' : '') +
                    '<input type="hidden" name="fee_type_id[]" value="' + fee.fee_type_id + '">' +
                    '<input type="hidden" name="is_monthly[]" value="' + (fee.is_monthly ? '1' : '0') + '">' +
                  '</td>' +
                  '<td><div class="input-group input-group-sm">' +
                        '<span class="input-group-text">Rs.</span>' +
                        '<input type="number" class="form-control form-control-sm default-amount text-end" value="' + defAmt + '" readonly>' +
                      '</div></td>' +
                  '<td><div class="input-group input-group-sm">' +
                        '<span class="input-group-text">Rs.</span>' +
                        '<input type="number" name="student_amount[]" class="form-control form-control-sm student-amount text-end" value="' + defAmt + '" data-default="' + defAmt + '">' +
                      '</div></td>' +
                  '<td class="text-danger discount-info text-end">Rs. 0.00</td>' +
                '</tr>';
            });

            html +=
              '<tr class="table-active">' +
                '<th>Total Fees</th>' +
                '<th class="text-end">Rs. <span class="total-default">' + totalDefault.toFixed(2) + '</span></th>' +
                '<th class="text-end">Rs. <span class="total-student">' + totalStudent.toFixed(2) + '</span></th>' +
                '<th class="text-danger text-end">Rs. <span class="total-discount">0.00</span></th>' +
              '</tr>';

            $('#fee-type-container').html(html);
            updateTotals();

            $('.student-amount').on('input', function () {
              const defVal = parseFloat($(this).data('default')) || 0;
              const stuVal = parseFloat($(this).val()) || 0;
              const disc = defVal - stuVal;
              $(this).closest('.fee-row').find('.discount-info').html('Rs. ' + disc.toFixed(2));
              updateTotals();
            });

            toastr.success('Fee structure loaded successfully');
          } else {
            $('#fee-type-container').html(
              '<tr><td colspan="4" class="text-center py-4 text-danger">' +
                '<i class="fas fa-exclamation-triangle me-2"></i>Failed to load fee structure</td></tr>'
            );
            toastr.error('Failed to load fee structure for selected class');
          }
        },
        error: function () {
          $('#fee-type-container').html(
            '<tr><td colspan="4" class="text-center py-4 text-danger">' +
              '<i class="fas fa-exclamation-triangle me-2"></i>Error loading fee structure</td></tr>'
          );
          toastr.error('Error loading fee structure. Please try again.');
        }
      });
    });

    // Initialize immediately if section is pre-selected
    <?php if (!empty($section_id)): ?>
      $('#section_id').trigger('change');
    <?php endif; ?>

    function updateTotals() {
      let totalDefault = 0, totalStudent = 0;
      $('.fee-row').each(function () {
        const defVal = parseFloat($(this).find('.default-amount').val()) || 0;
        const stuVal = parseFloat($(this).find('.student-amount').val()) || 0;
        totalDefault += defVal; totalStudent += stuVal;
      });
      const totalDiscount = totalDefault - totalStudent;
      $('.total-default').text(totalDefault.toFixed(2));
      $('.total-student').text(totalStudent.toFixed(2));
      $('.total-discount').text(totalDiscount.toFixed(2));
    }

    function getFeeTypeIcon(title) {
      const t = String(title || '').toLowerCase();
      if (t.includes('admission')) return 'fa-user-plus';
      if (t.includes('exam'))      return 'fa-file-alt';
      if (t.includes('activity'))  return 'fa-running';
      if (t.includes('transport')) return 'fa-bus';
      if (t.includes('uniform'))   return 'fa-tshirt';
      if (t.includes('book'))      return 'fa-book';
      return 'fa-money-bill-wave';
    }

    // ---------------------------
    // Form validation & submit
    // ---------------------------
    if ($.fn.validate) {
      $('#student-admission-form').validate({
        rules: {
          first_name: 'required',
          father_cnic: 'required',
          f_name: 'required',
          
          date_of_birth: 'required',
          section_id: 'required',
          fee_issue_date: 'required',
          fee_due_date: 'required',
          fee_month: 'required',
          gender: 'required'
        },
        messages: {
          first_name: 'Student full name is required',
          father_cnic: 'Father CNIC is required for verification',
          f_name: 'Father name is required',
          date_of_admission: 'Please select admission date',
          date_of_birth: 'Please provide date of birth',
          section_id: 'Please select class section',
          fee_issue_date: 'Please select fee issue date',
          fee_due_date: 'Please select fee due date',
          fee_month: 'Please select fee month',
          gender: 'Please select gender'
        },
        errorElement: 'div',
        errorClass: 'invalid-feedback',
        highlight: function (el) { $(el).addClass('is-invalid').removeClass('is-valid'); $(el).closest('.form-group').find('.input-group-text').addClass('border-danger'); },
        unhighlight: function (el) { $(el).removeClass('is-invalid').addClass('is-valid'); $(el).closest('.form-group').find('.input-group-text').removeClass('border-danger'); },
        errorPlacement: function (error, el) {
          if ($(el).hasClass('datepicker') || $(el).hasClass('phone-mask') || $(el).hasClass('cnic-mask')) {
            error.insertAfter($(el).closest('.input-group'));
          } else {
            error.insertAfter(el);
          }
        },
        submitHandler: function (form) {
          const formData = new FormData(form);
          $.ajax({
            url: $(form).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function () {
              $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Processing Admission...');
            },
            success: function (res) {
              if (res.success) {
                toastr.success(res.msg || 'Saved');
                if (res.pdf_url) window.open(res.pdf_url, '_blank');
                setTimeout(function () { window.location.href = '<?= base_url('admin/students/edit?id=') ?>' + res.student_id; }, 1500);
              } else {
                toastr.error(res.msg || 'Save failed');
                $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Submit Admission');
              }
            },
            error: function (xhr) {
              try { var j = JSON.parse(xhr.responseText); toastr.error(j.message || 'An error occurred. Please try again.'); }
              catch (e) { toastr.error('An error occurred. Please try again.'); }
              $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Submit Admission');
            }
          });
        }
      });
    }
  });
})(jQuery);
</script>



