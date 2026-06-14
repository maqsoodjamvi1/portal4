<?php
/**
 * Post-setup optional modules (dismissible).
 */
if (session()->get('optional_modules_dismissed')) {
    return;
}
?>
<div class="alert alert-info alert-dismissible fade show dash-optional-modules no-print" role="region" aria-label="Optional modules">
  <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close" id="dismissOptionalModules"><span aria-hidden="true">&times;</span></button>
  <h6 class="alert-heading mb-2"><i class="fas fa-puzzle-piece me-1"></i> Explore optional modules</h6>
  <p class="mb-2 small">Core setup is complete. You can enable these when your school is ready:</p>
  <ul class="mb-0 ps-3 small">
    <li><a href="<?= base_url('admin/hifz/sections') ?>">Hifz program</a> - sections, students, and recitation tracking</li>
    <li><a href="<?= base_url('admin/employee-face-management') ?>">Face attendance</a> - enroll staff for face recognition check-in</li>
    <li><a href="<?= base_url('admin/students?status=1') ?>">Parent portal</a> - share student login credentials with families</li>
  </ul>
</div>
<script>
$(function () {
  $('#dismissOptionalModules').on('click', function () {
    $.post('<?= base_url('admin/ajax/dismiss-optional-modules') ?>');
  });
});
</script>
