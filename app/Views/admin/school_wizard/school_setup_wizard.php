<!-- school_wizard.php -->
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Stepper CSS -->
<link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/bs-stepper/css/bs-stepper.min.css') ?>">

<div class="container mt-5">
  <div class="bs-stepper" id="schoolWizard">
    <div class="bs-stepper-header">
      <div class="step" data-bs-target="#step-1">
        <button class="step-trigger"><span class="bs-stepper-label">Basic Info</span></button>
      </div>
      <div class="line"></div>
      <div class="step" data-bs-target="#step-2">
        <button class="step-trigger"><span class="bs-stepper-label">Contact Info</span></button>
      </div>
      <div class="line"></div>
      <div class="step" data-bs-target="#step-3">
        <button class="step-trigger"><span class="bs-stepper-label">Academic Setup</span></button>
      </div>
      <div class="line"></div>
      <div class="step" data-bs-target="#step-4">
        <button class="step-trigger"><span class="bs-stepper-label">Fee Setup</span></button>
      </div>
    </div>

    <div class="bs-stepper-content">
      <form id="wizard-form">
        <div id="step-1" class="content">
          <?= view('admin/school_wizard/step_basic') ?>
          <button class="btn btn-primary" type="button" onclick="stepper.next()">Next</button>
        </div>
        <div id="step-2" class="content">
          <?= view('admin/school_wizard/step_contact') ?>
          <button class="btn btn-secondary" type="button" onclick="stepper.previous()">Previous</button>
          <button class="btn btn-primary" type="button" onclick="stepper.next()">Next</button>
        </div>
        <div id="step-3" class="content">
          <?= view('admin/school_wizard/step_academic') ?>
          <button class="btn btn-secondary" type="button" onclick="stepper.previous()">Previous</button>
          <button class="btn btn-primary" type="button" onclick="stepper.next()">Next</button>
        </div>
        <div id="step-4" class="content">
          <?= view('admin/school_wizard/step_fee') ?>
          <button class="btn btn-secondary" type="button" onclick="stepper.previous()">Previous</button>
          <button class="btn btn-success" type="submit">Finish</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Stepper JS -->
<script src="<?= base_url('resource/adminlte/plugins/bs-stepper/js/bs-stepper.min.js') ?>"></script>
<script>
  let stepper;
  document.addEventListener('DOMContentLoaded', function () {
    stepper = new Stepper(document.querySelector('#schoolWizard'), {
      linear: false,
      animation: true
    });
  });

  $('#wizard-form').on('submit', function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    $.post("<?= base_url('admin/schoolsetup/saveWizardData') ?>", formData, function (res) {
      if (res.status === 'success') {
        alert(res.message);
        location.reload();
      } else {
        alert('Error: ' + res.message);
      }
    }, 'json');
  });
</script>

<?= $this->endSection() ?>
