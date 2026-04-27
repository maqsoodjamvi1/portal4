<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
helper(['form', 'url', 'text']);

// Header + safe defaults
$header = isset($info) ? 'Edit Admission Enquiry' : 'Add Admission Enquiry';

$id               = $info->enquiry_id      ?? '';
$student_name     = $info->student_name    ?? '';
$father_name      = $info->father_name     ?? '';
$student_age      = $info->student_age     ?? '';
$father_phone     = $info->father_phone    ?? '';
$mother_phone     = $info->mother_phone    ?? '';
$previous_school  = $info->previous_school ?? '';
$previous_fee     = $info->previous_fee    ?? '';
$address          = $info->address         ?? '';
$description      = $info->description     ?? '';
$today            = date('Y-m-d');
$date             = $info->date            ?? $today; // ✅ default to today
?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2 align-items-center">
      <div class="col-sm-6">
        <h1 class="mb-0">Admission Enquiry</h1>
        <small class="text-muted"><?= esc($header) ?></small>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active"><?= esc($header) ?></li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="card card-primary card-outline shadow-sm">
        <div class="card-header d-flex flex-wrap align-items-center">
          <h3 class="card-title mr-2"><i class="fas fa-user-plus mr-1"></i> <?= esc($header) ?></h3>
          <span class="badge badge-info ml-auto">Enquiry</span>
        </div>

        <div class="card-body">
          <?= form_open(base_url('admin/admission-enquiry/save'), ['id' => 'user-edit-form', 'autocomplete' => 'off']) ?>
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= esc($id) ?>">

          <div class="row">
            <!-- Student Name -->
            <div class="col-md-6 mb-3">
              <label class="mb-1" for="student_name">Student Name <span class="text-danger">*</span></label>
              <div class="input-group input-group-sm">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                <input type="text" class="form-control" name="student_name" id="student_name"
                       placeholder="e.g. Ahmed Ali"
                       value="<?= esc($student_name) ?>" required>
              </div>
            </div>

            <!-- Father Name -->
            <div class="col-md-6 mb-3">
              <label class="mb-1" for="father_name">Father Name</label>
              <div class="input-group input-group-sm">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user-tie"></i></span></div>
                <input type="text" class="form-control" name="father_name" id="father_name"
                       placeholder="e.g. Muhammad Ali"
                       value="<?= esc($father_name) ?>">
              </div>
            </div>

            <!-- Student Age -->
            <div class="col-md-3 mb-3">
              <label class="mb-1" for="student_age">Age of Student</label>
              <div class="input-group input-group-sm">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-child"></i></span></div>
                <input type="number" min="2" max="25" step="1" class="form-control" name="student_age" id="student_age"
                       placeholder="e.g. 10"
                       value="<?= esc($student_age) ?>">
              </div>
              <small class="text-muted">Enter whole years (2–25).</small>
            </div>

            <!-- Father Phone -->
            <div class="col-md-3 mb-3">
              <label class="mb-1" for="father_phone">Father Phone Number</label>
              <div class="input-group input-group-sm">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
                <input type="text" class="form-control" name="father_phone" id="father_phone"
                       placeholder="03XX-XXXXXXX"
                       value="<?= esc($father_phone) ?>">
              </div>
              <small class="text-muted">Format: 03XX-XXXXXXX</small>
            </div>

            <!-- Mother Phone -->
            <div class="col-md-3 mb-3">
              <label class="mb-1" for="mother_phone">Mother Phone Number</label>
              <div class="input-group input-group-sm">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone-alt"></i></span></div>
                <input type="text" class="form-control" name="mother_phone" id="mother_phone"
                       placeholder="03XX-XXXXXXX"
                       value="<?= esc($mother_phone) ?>">
              </div>
            </div>

            <!-- Previous School -->
            <div class="col-md-3 mb-3">
              <label class="mb-1" for="previous_school">Previous School</label>
              <div class="input-group input-group-sm">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-school"></i></span></div>
                <input type="text" class="form-control" name="previous_school" id="previous_school"
                       placeholder="e.g. Beaconhouse"
                       value="<?= esc($previous_school) ?>" list="school_suggestions">
              </div>
              <!-- Optional: datalist for quick suggestions (keep empty or fill dynamically) -->
              <datalist id="school_suggestions"></datalist>
            </div>

            <!-- Previous Fee -->
            <div class="col-md-3 mb-3">
              <label class="mb-1" for="previous_fee">Previous Fee (PKR)</label>
              <div class="input-group input-group-sm">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-rupee-sign"></i></span></div>
                <input type="text" class="form-control" name="previous_fee" id="previous_fee"
                       placeholder="e.g. 4500"
                       value="<?= esc($previous_fee) ?>">
              </div>
            </div>

            <!-- Address -->
            <div class="col-md-6 mb-3">
              <label class="mb-1" for="address">Address</label>
              <div class="input-group input-group-sm">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span></div>
                <input type="text" class="form-control" name="address" id="address"
                       placeholder="House #, Street, Area, City"
                       value="<?= esc($address) ?>">
              </div>
            </div>

            <!-- Enquiry Date -->
            <div class="col-md-3 mb-3">
              <label class="mb-1" for="date">Enquiry Date <span class="text-success">(auto-today)</span></label>
              <div class="input-group input-group-sm date" id="enquiry_datepicker" data-target-input="nearest">
                <div class="input-group-prepend"><span class="input-group-text"><i class="far fa-calendar-alt"></i></span></div>
                <input type="text"
                       name="date"
                       id="date"
                       class="form-control datetimepicker-input"
                       data-target="#enquiry_datepicker"
                       value="<?= esc($date) ?>"
                       required>
                <div class="input-group-append" data-target="#enquiry_datepicker" data-toggle="datetimepicker">
                  <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
              </div>
              <small class="text-muted">Defaults to today; change if needed.</small>
            </div>

            <!-- Description -->
            <div class="col-12 mb-3">
              <label class="mb-1" for="description">Notes / Description</label>
              <textarea name="description" id="description" class="form-control" rows="3"
                        placeholder="Any special requirements, target class, references, etc."><?= esc($description) ?></textarea>
            </div>
          </div>

          <div class="d-flex align-items-center">
            <button type="submit" id="submitBtn" class="btn btn-primary mr-2">
              <i class="fas fa-save mr-1"></i> Save
            </button>
            <button type="reset" class="btn btn-secondary mr-2">
              <i class="fas fa-undo mr-1"></i> Reset
            </button>
            <a href="<?= base_url('admin/admission-enquiry') ?>" class="btn btn-default">
              <i class="fas fa-times mr-1"></i> Cancel
            </a>
          </div>

          <?= form_close() ?>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
$(function () {
  // --- Datepicker (Tempus Dominus in AdminLTE) ---
  // default to today if input is empty (extra safety)
  var $date = $('#date');
  if(!$date.val()){
    var today = moment().format('YYYY-MM-DD');
    $date.val(today);
  }
  $('#enquiry_datepicker').datetimepicker({
    format: 'YYYY-MM-DD',
    defaultDate: $date.val() || moment(), // ✅ shows today by default
    icons: { time: 'far fa-clock' }
  });

  // --- Input masks (AdminLTE ships with jquery.inputmask) ---
  // PK phone format 03XX-XXXXXXX
  try {
    $('#father_phone, #mother_phone').inputmask('0399-9999999', {
      placeholder: '03__-_______',
      clearIncomplete: true,
      showMaskOnHover: false
    });
  } catch(e){ /* inputmask may be missing; ignore gracefully */ }

  // Numeric money mask (simple)
  try {
    $('#previous_fee').inputmask('decimal', {
      rightAlign: false,
      digits: 0,
      allowMinus: false,
      autoGroup: true,
      groupSeparator: ',',
      groupSize: 3,
      removeMaskOnSubmit: true,
      placeholder: ''
    });
  } catch(e){}

  // Soft autosize for description (no external lib needed)
  $('#description').on('input', function(){
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
  }).trigger('input');

  // Client validation (jQuery Validate)
  $('#user-edit-form').validate({
    errorClass: 'is-invalid',
    validClass: 'is-valid',
    errorElement: 'div',
    errorPlacement: function(error, element){
      error.addClass('invalid-feedback');
      if (element.parent('.input-group').length) {
        error.insertAfter(element.parent()); // input-group
      } else {
        error.insertAfter(element);
      }
    },
    highlight: function(el){ $(el).addClass('is-invalid').removeClass('is-valid'); },
    unhighlight: function(el){ $(el).removeClass('is-invalid').addClass('is-valid'); },
    rules: {
      student_name: { required: true, minlength: 2 },
      date:         { required: true, dateISO: true },
      student_age:  { number: true, min: 2, max: 25 },
      father_phone: { minlength: 12 }, // 03XX-XXXXXXX => 12 chars incl. dash
      mother_phone: { minlength: 12 }
    },
    messages: {
      student_name: { required: 'Student name is required', minlength: 'Enter at least 2 characters' },
      date:         { required: 'Enquiry date is required',  dateISO: 'Use YYYY-MM-DD' }
    }
  });

  // AJAX submit (jQuery Form)
  $('#user-edit-form').ajaxForm({
    beforeSubmit: function(){
      if(!$('#user-edit-form').valid()) return false;
      $('#submitBtn').html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...').prop('disabled', true);
    },
    success: function(responseText){
      $('#submitBtn').html('<i class="fas fa-save mr-1"></i> Save').prop('disabled', false);
      var json = (typeof responseText === 'string') ? (function(){ try { return JSON.parse(responseText); } catch(e){ return {}; } })() : responseText;

      if (json && json.success) {
        toastr.success(json.msg || 'Saved successfully');
        window.location.href = "<?= base_url('admin/admission-enquiry') ?>";
      } else {
        toastr.error((json && json.msg) ? json.msg : 'Save failed');
      }
      return false;
    },
    error: function(){
      $('#submitBtn').html('<i class="fas fa-save mr-1"></i> Save').prop('disabled', false);
      toastr.error('Request failed. Please try again.');
    }
  });
});
</script>

<?= $this->endSection() ?>
