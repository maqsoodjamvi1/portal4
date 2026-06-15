<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link href="<?= base_url('resource/bs-stepper/css/bs-stepper.min.css') ?>" rel="stylesheet">
<script src="<?= base_url('resource/bs-stepper/js/bs-stepper.min.js') ?>"></script>

<div class="container mt-4">
  <div class="card card-primary card-outline">
    <div class="card-header"><h4>School Setup Wizard</h4></div>
    <div class="card-body">
      <form id="schoolWizardForm">
        <div id="schoolStepper" class="bs-stepper">
          <div class="bs-stepper-header" role="tablist">
            <div class="step" data-bs-target="#step1-classes-edit">
              <button type="button" class="step-trigger" role="tab">
                <span class="bs-stepper-circle">1</span>
                <span class="bs-stepper-label">Basic Info</span>
              </button>
            </div>
            <div class="line"></div>

            <div class="step" data-bs-target="#step-contact">
              <button type="button" class="step-trigger" role="tab">
                <span class="bs-stepper-circle">2</span>
                <span class="bs-stepper-label">Contact Info</span>
              </button>
            </div>
            <div class="line"></div>

            <div class="step" data-bs-target="#step-final">
              <button type="button" class="step-trigger" role="tab">
                <span class="bs-stepper-circle">3</span>
                <span class="bs-stepper-label">Confirm</span>
              </button>
            </div>
          </div>

          <div class="bs-stepper-content">
            <div id="step1-classes-edit" class="content">
              <?= view('admin/school_wizard/step1_classes_edit') ?>
            </div>

            <div id="step-contact" class="content">
              <?= view('admin/school_wizard/step2_section_edit') ?>
            </div>

            <div id="step-final" class="content">
              <?= view('admin/school_wizard/step3_class_section_edit') ?>
            </div>
          </div>
        </div>

        <div class="mt-3">
          <button type="button" class="btn btn-secondary" onclick="stepper.previous()">Previous</button>
          <button type="button" class="btn btn-primary" onclick="nextStep()">Next</button>
          <button type="submit" class="btn btn-success d-none" id="submitWizard">Finish</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let stepper;

document.addEventListener('DOMContentLoaded', function () {
  stepper = new Stepper(document.querySelector('#schoolStepper'));
});

function nextStep() {
  stepper.next();

  // Show Finish only on last step
  const isLast = stepper._currentIndex === stepper._steps.length - 1;
  document.getElementById('submitWizard').classList.toggle('d-none', !isLast);
}

$('#schoolWizardForm').submit(function (e) {
  e.preventDefault();
  const formData = $(this).serialize();

  $.ajax({
    url: "<?= base_url('admin/school_wizard/saveWizardData') ?>",
    method: "POST",
    data: formData,
    dataType: "json",
    success: function (res) {
      if (res.status === 'success') {
        Swal.fire('Success!', 'School setup completed!', 'success').then(() => {
          location.href = '<?= base_url('admin/dashboard') ?>';
        });
      } else {
        Swal.fire('Error', res.message, 'error');
      }
    },
    error: function () {
      Swal.fire('Error', 'Something went wrong while saving.', 'error');
    }
  });
});
</script>

<?= $this->endSection() ?>
