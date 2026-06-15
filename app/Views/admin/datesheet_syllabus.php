<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  /** @var object|null $exam */
  /** @var object|null $currentSession */
  /** @var array<int, array<string, mixed>> $sections */
  $eid         = (int)($exam->eid ?? 0);
  $session_id  = (int)($currentSession->session_id ?? 0);
?>

<style>
  .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered { line-height: 1.9rem; }
  .select2-container--bootstrap-5 .select2-selection--single { height: calc(1.9rem + 2px); }
  /* Match Top Level Planning add.php alert tone for inline hints */
  .syllabus-page-hint.alert-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    margin-bottom: 1rem;
  }
  .syllabus-page-hint.alert-info i { margin-right: 10px; }
</style>

<?= view('components/page_header', [
    'title' => 'Add Syllabus',
    'icon' => 'fas fa-book',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Datesheet', 'url' => base_url('admin/datesheet')],
        ['label' => 'Add Syllabus', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">

        <div class="card sms-card card-primary card-outline">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-layer-group me-2"></i>
              Class section &amp; navigation
            </h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs mb-3">
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url('admin/datesheet') ?>">
                  <i class="fas fa-id-card-alt me-1"></i> Admit Card
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url('admin/datesheet?mode=without_syllabus') ?>">
                  <i class="fas fa-table me-1"></i> Admit Card Without Syllabus
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link active" href="<?= base_url('admin/datesheet/add-syllabus') ?>">
                  <i class="fas fa-list-ul me-1"></i> Add Syllabus
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url('admin/datesheet/add') ?>">
                  <i class="far fa-calendar-plus me-1"></i> Add Datesheet
                </a>
              </li>
            </ul>

            <div class="row">
              <div class="col-md-5 col-lg-4">
                <div class="form-group mb-0">
                  <label for="cls_sec_id">Class section <span class="text-danger">*</span></label>
                  <select class="form-control select2" id="cls_sec_id" required>
                    <option value="">Select class section</option>
                    <?php foreach (($sections ?? []) as $s): ?>
                      <option value="<?= (int) $s['cls_sec_id'] ?>"><?= esc($s['label'] ?? ('Sec#'.(int)$s['cls_sec_id'])) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

            <input type="hidden" id="eid" value="<?= $eid ?>">
            <input type="hidden" id="session_id" value="<?= $session_id ?>">
          </div>
        </div>

        <div class="card sms-card card-primary card-outline">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-tasks me-2"></i>
              Syllabus by subject
            </h3>
            <div class="card-tools">
              <?php if ($eid <= 0): ?>
                <span class="badge text-bg-warning me-2">No active exam for this session</span>
              <?php else: ?>
                <span class="badge text-bg-light border text-muted me-2">Exam ID <?= (int) $eid ?></span>
              <?php endif; ?>
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <div id="syllabusGrid" class="text-center text-muted py-4 border rounded bg-light">
              <i class="far fa-hand-pointer me-1"></i> Select a class section above to load subjects…
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<script>
(function () {
  $(function () {
    if ($.fn.select2) {
      $('#cls_sec_id').select2({
        theme: 'bootstrap-5',
        width: '100%',
        allowClear: true,
        placeholder: 'Select class section'
      });
    }
  });

  <?php if (function_exists('csrf_token')): ?>
    const __csrfName = '<?= csrf_token() ?>';
    const __csrfHash = '<?= csrf_hash() ?>';
  <?php else: ?>
    const __csrfName = null, __csrfHash = null;
  <?php endif; ?>
  function withCsrf(d){ if(__csrfName && __csrfHash){ d[__csrfName] = __csrfHash; } return d; }

  function spinner() {
    return '<div class="text-center p-4 text-muted"><span class="spinner-border spinner-border-sm"></span> Loading…</div>';
  }

  function loadSyllabusGrid() {
    const cls_sec_id = $('#cls_sec_id').val();
    const eid        = parseInt($('#eid').val(), 10) || 0;

    if (!cls_sec_id) {
      $('#syllabusGrid').removeClass('border rounded bg-light').html('<div class="alert alert-warning mb-0">Please select a class section.</div>');
      return;
    }
    if (eid <= 0) {
      $('#syllabusGrid').removeClass('border rounded bg-light').html('<div class="alert alert-danger mb-0">No active (unannounced) exam for this session. Go to <strong>Admin → Exams</strong> to create one, then add datesheet entries.</div>');
      return;
    }

    $('#syllabusGrid').removeClass('bg-light border text-center text-muted rounded py-4').html(spinner());

    $.post("<?= base_url('admin/datesheet/fetch-syllabus-grid') ?>", withCsrf({ cls_sec_id }))
      .done(function (html) {
        $('#syllabusGrid').html(html);
      })
      .fail(function (xhr) {
        let msg = 'Failed to load syllabus grid.';
        if (xhr && xhr.responseText) {
          msg = xhr.responseText;
        }
        $('#syllabusGrid').html('<div class="alert alert-danger mb-0">' + msg + '</div>');
      });
  }

  $(document).on('change', '#cls_sec_id', loadSyllabusGrid);

  $(document).ready(function(){
    if ($('#cls_sec_id').val()) {
      loadSyllabusGrid();
    }
  });
})();
</script>

<?= $this->endSection() ?>
