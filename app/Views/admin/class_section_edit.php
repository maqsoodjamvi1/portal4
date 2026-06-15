<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<link href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" rel="stylesheet">
<script src="<?= base_url('resource/bootstrap-switch/js/bootstrap-switch.min.js') ?>"></script>

<?php
    $header = isset($info) ? 'Edit Class Section' : 'Add Class Section';
    $id = $info->sec_id ?? 0;
    $class_id = $info->class_id ?? '';
    $subject_id = isset($info->subject_id) ? (int)$info->subject_id : '';
    $showWizardStep = empty($classSections_info->cls_sec_id ?? '');
    //print_r($classSections_info);
?>

<?php
$classSecSetupBadge = empty($classSections_info->cls_sec_id)
    ? '<span class="badge text-bg-success" data-bs-toggle="tooltip" title="Step 6 of 10 in setup wizard">Step 6 of 10 - System Configuration</span>'
    : '';
$classSecSetupAudio = empty($classSections_info->cls_sec_id)
    ? '<audio autoplay controls hidden><source src="' . esc(base_url('audio/Step8ClassSection.m4a'), 'attr') . '" type="audio/mpeg"></audio>'
    : '';
?>
<?= view('components/page_header', [
    'title' => $header,
    'icon' => 'fas fa-layer-group',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Class Sections', 'url' => base_url('admin/class_section')],
        ['label' => empty($classSections_info->cls_sec_id) ? 'Add' : 'Edit', 'active' => true],
    ],
    'actionsHtml' => $classSecSetupBadge . $classSecSetupAudio,
]) ?>

<!-- Main Content -->
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-th-large me-1"></i> Class Section Matrix</h3>
      </div>
      <div class="card-body">
        <div id="subjectsection" class="table-responsive border rounded p-3 bg-light">
          <!-- AJAX-loaded content will be injected here -->
          <div class="text-center p-5">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
            <p class="mt-2">Loading sections...</p>
          </div>
        </div>

       <?php if ($showWizardStep): ?>
  <div class="mt-4 text-end">
    <?php if (!$subjectinfo): ?>
      <a href="<?= base_url('admin/subjects/add') ?>" class="btn btn-warning btn-lg">
        No subjects found – Add Subjects <i class="fas fa-arrow-right ms-1"></i>
      </a>
    <?php else: ?>
      <a href="<?= base_url('admin/subjects/add') ?>" class="btn btn-success btn-lg">
        Next Step <i class="fas fa-arrow-right ms-1"></i>
      </a>
    <?php endif; ?>
  </div>
<?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- Scripts -->
<script>
$(document).ready(function () {
  $('[data-bs-toggle="tooltip"]').tooltip();

  $.ajax({
    url: '<?= base_url('admin/class-section/data2') ?>',
    type: 'POST',
    dataType: 'html',
    success: function (res) {
      $('#subjectsection').html(res);
    },
    error: function () {
      $('#subjectsection').html('<div class="alert alert-danger">Failed to load section data. Please try again later.</div>');
    }
  });
});
$('.setClassSub').bootstrapSwitch();

$('.setClassSub').on('switchChange.bootstrapSwitch', function (event, state) {
    // your AJAX logic
});
</script>

<?= $this->endSection() ?>
