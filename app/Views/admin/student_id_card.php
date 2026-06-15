<?= $this->extend('layouts/admin_template') ?>

<?= $this->section('content') ?>
<?= view('components/page_header', [
    'title' => 'Student ID Card',
    'icon' => 'fas fa-id-card',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Students', 'url' => base_url('admin/students')],
        ['label' => 'ID Card', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="card card-primary card-outline">
        <div class="card-body">
          <div class="row no-print">
            <div class="col-md-3 form-group">
              <label for="class_id">Class</label>
              <select class="form-control" id="class_id">
                <option value="">All Classes</option>
                <?php foreach (($classes ?? []) as $classId => $className): ?>
                  <option value="<?= (int) $classId ?>"><?= esc($className) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3 form-group">
              <label for="cls_sec_id">Section</label>
              <select class="form-control" id="cls_sec_id">
                <option value="">All Sections</option>
                <?php foreach (($sectionsclassinfo ?? []) as $section): ?>
                  <option value="<?= (int) ($section['cls_sec_id'] ?? 0) ?>" data-class-id="<?= (int) ($section['class_id'] ?? 0) ?>">
                    <?= esc($section['sectionclassname'] ?? '') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2 form-group">
              <label for="statusFilter">Status</label>
              <select class="form-control" id="statusFilter">
                <option value="active">Active Students</option>
                <option value="new">New Admissions</option>
                <option value="all">All Students</option>
              </select>
            </div>
            <div class="col-md-3 form-group">
              <label for="student_ids">Reprint specific students (optional)</label>
              <input type="text" class="form-control" id="student_ids" placeholder="Enter student IDs, comma separated e.g. 1201,1208,1210">
              <small class="text-muted">Use this for lost cards or small batch reprints.</small>
            </div>
            <div class="col-md-1 form-group d-flex align-items-end">
              <button class="btn btn-primary w-100" id="generateCards">
                <i class="fas fa-id-card me-1"></i> Generate
              </button>
            </div>
          </div>

          <div class="mb-3 no-print">
            <button class="btn btn-success me-2" id="printCards" style="display:none;">
              <i class="fas fa-print me-1"></i> Print
            </button>
            <span id="cardMeta" class="text-muted small"></span>
          </div>

          <div id="cardsLoader" class="overlay d-none">
            <i class="fas fa-3x fa-sync-alt fa-spin"></i>
          </div>
          <div id="cardsResult"></div>
        </div>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<style>
#cardsResult{min-height:120px;}
@media print {
  .main-sidebar, .main-header, .content-header, .breadcrumb, .no-print, .main-footer { display:none !important; }
  .content-wrapper { margin-left:0 !important; }
  .content, .container-fluid, .card, .card-body { padding:0 !important; margin:0 !important; border:0 !important; box-shadow:none !important; }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
(function() {
  const $btn = $('#generateCards');
  const $print = $('#printCards');
  const $loader = $('#cardsLoader');
  const $result = $('#cardsResult');
  const $meta = $('#cardMeta');

  function generate() {
    $loader.removeClass('d-none');
    $result.empty();
    $print.hide();
    $meta.text('');

    $.ajax({
      url: '<?= site_url('admin/student_id_card/data_vertical') ?>',
      type: 'POST',
      data: {
        class_id: $('#class_id').val(),
        cls_sec_id: $('#cls_sec_id').val(),
        status: $('#statusFilter').val(),
        student_ids: $('#student_ids').val().trim()
      },
      success: function(res) {
        $result.html(res);
        $loader.addClass('d-none');

        const count = $result.find('.id-card-pair').length;
        $meta.text(count > 0 ? (count + ' card(s) generated') : 'No students found for selected filters.');
        if (count > 0) {
          $print.show();
        }
      },
      error: function() {
        $loader.addClass('d-none');
        $result.html('<div class="alert alert-danger mb-0">Unable to generate cards. Please try again.</div>');
      }
    });
  }

  $btn.on('click', generate);
  $('#class_id').on('change', function() {
    const selectedClass = $(this).val();
    $('#cls_sec_id option').each(function() {
      const optClass = ($(this).data('class-id') || '').toString();
      if (!$(this).val()) {
        $(this).show();
        return;
      }
      if (!selectedClass || optClass === selectedClass.toString()) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
    $('#cls_sec_id').val('');
  });
  $print.on('click', function() { window.print(); });
})();
</script>
<?= $this->endSection() ?>
