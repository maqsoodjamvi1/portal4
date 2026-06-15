<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- CSS -->
<link href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css" rel="stylesheet">
<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>


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

            <div class="step" data-bs-target="#step2-sections-edit">
              <button type="button" class="step-trigger" role="tab">
                <span class="bs-stepper-circle">2</span>
                <span class="bs-stepper-label">Contact Info</span>
              </button>
            </div>
            <div class="line"></div>

            <div class="step" data-bs-target="#step3-class-section-edit">
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

            <div id="step2-sections-edit" class="content">
              <?= view('admin/school_wizard/step2_sections_edit') ?>
            </div>

            <div id="step3-class-section-edit" class="content">
              <?= view('admin/school_wizard/step2_sections_edit') ?>
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
  const currentIndex = stepper._currentIndex;

  if (currentIndex === 0) {
    // Step 1: Insert class
    const formData = {
      class_name: $('[name="class_name"]').val(),
      class_short_name: $('[name="class_short_name"]').val()
    };

    $.ajax({
      url: "<?= base_url('admin/schoolsetup/saveStep1Class') ?>",
      method: "POST",
      data: formData,
      dataType: "json",
      success: function (res) {
        if (res.status === 'success') {
          stepper.next(); // Move to Step 2
        } else {
          Swal.fire('Error', res.message, 'error');
        }
      },
      error: function () {
        Swal.fire('Error', 'Something went wrong in Step 1.', 'error');
      }
    });

  } else if (currentIndex === 1) {
    // Step 2 logic here (e.g. save section)
    const formData = {
      section_name: $('[name="section_name"]').val(),
    };

    $.ajax({
      url: "<?= base_url('admin/schoolsetup/saveStep2Section') ?>",
      method: "POST",
      data: formData,
      dataType: "json",
      success: function (res) {
        if (res.status === 'success') {
          stepper.next(); // Move to Step 3
          document.getElementById('submitWizard').classList.remove('d-none');
        } else {
          Swal.fire('Error', res.message, 'error');
        }
      },
      error: function () {
        Swal.fire('Error', 'Something went wrong in Step 2.', 'error');
      }
    });

  }
}

</script>

<?= $this->endSection() ?>
