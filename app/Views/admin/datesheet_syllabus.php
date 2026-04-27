<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  // Provided by controller (hidden from user)
  /** @var object|null $exam */
  /** @var object|null $currentSession */
  /** @var array $sections */
  $eid         = (int)($exam->eid ?? 0);
  $session_id  = (int)($currentSession->session_id ?? 0);
?>

<style>
  .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered { line-height: 1.9rem; }
  .select2-container--bootstrap4 .select2-selection--single { height: calc(1.9rem + 2px); }
</style>

<section class="content">
  <div class="card card-outline card-primary">
    <div class="card-header py-2" style="background: linear-gradient(135deg,#3a7bd5 0%,#00d2ff 100%);">
      <div class="row align-items-center">
        <div class="col-md-7">
          <h4 class="text-white mb-0">
            <i class="fas fa-tasks mr-2"></i> Add Syllabus
          </h4>
          <small class="text-white-50">Select a class section; enter syllabus per subject. Current session & active exam are handled automatically.</small>
        </div>
        <div class="col-md-5">
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text bg-white"><i class="fas fa-layer-group text-primary"></i></span>
            </div>
            <select id="cls_sec_id" class="form-control form-control-sm select2" required>
              <option value="">-- Select Class Section --</option>
              <?php foreach (($sections ?? []) as $s): ?>
                <option value="<?= (int)$s['cls_sec_id'] ?>"><?= esc($s['label'] ?? ('Sec#'.(int)$s['cls_sec_id'])) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Hidden computed inputs (not shown to user) -->
      <input type="hidden" id="eid" value="<?= $eid ?>">
      <input type="hidden" id="session_id" value="<?= $session_id ?>">
    </div>

    <div class="card-body p-2">
      <div id="syllabusGrid" class="border rounded p-3 text-center text-muted">
        <i class="far fa-hand-pointer"></i> Select a class section to load subjects…
      </div>
    </div>
  </div>
</section>

<script>
(function () {
  // init Select2
  $(function () {
    if ($.fn.select2) $('.select2').select2({ theme: 'bootstrap4', width: '100%', placeholder: 'Select…', allowClear: true });
  });

  // CSRF helper (works even if CSRF disabled)
  <?php if (function_exists('csrf_token')): ?>
    const __csrfName = '<?= csrf_token() ?>';
    const __csrfHash = '<?= csrf_hash() ?>';
  <?php else: ?>
    const __csrfName = null, __csrfHash = null;
  <?php endif; ?>
  function withCsrf(d){ if(__csrfName && __csrfHash){ d[__csrfName] = __csrfHash; } return d; }

  function spinner() {
    return '<div class="text-center p-3 text-muted"><span class="spinner-border spinner-border-sm"></span> Loading…</div>';
  }

  // Load syllabus grid for chosen class section
  function loadSyllabusGrid() {
    const cls_sec_id = $('#cls_sec_id').val();
    const eid        = $('#eid').val();

    if (!cls_sec_id) {
      $('#syllabusGrid').html('<div class="alert alert-warning mb-0">Please select a class section.</div>');
      return;
    }
    if (!eid) {
      $('#syllabusGrid').html('<div class="alert alert-danger mb-0">No active exam found for the current session.</div>');
      return;
    }

    $('#syllabusGrid').html(spinner());

    $.post("<?= base_url('admin/datesheet/fetch-syllabus-grid') ?>", withCsrf({ cls_sec_id }))
      .done(function (html) {
        $('#syllabusGrid').html(html);
      })
      .fail(function () {
        $('#syllabusGrid').html('<div class="alert alert-danger mb-0">Failed to load.</div>');
      });
  }

  $(document).on('change', '#cls_sec_id', loadSyllabusGrid);

  // If controller preselects a section later, auto-load here
  $(document).ready(function(){
    if ($('#cls_sec_id').val()) loadSyllabusGrid();
  });
})();
</script>

<?= $this->endSection() ?>
