<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  $cls_sec_id         = $cls_sec_id         ?? '';
  $sectionsclassinfo  = $sectionsclassinfo  ?? [];
  $schoolinfo         = getSchoolInfo();
?>

<section class="content">

  <div class="card card-outline card-primary">
    <!-- Minimal, professional header -->
    <div class="card-header py-2 d-flex flex-wrap align-items-center justify-content-between">
      <div class="mr-2">
        <h5 class="mb-0 font-weight-bold">Exam Schedule</h5>
       
      </div>

      <!-- Filters (Class → Section only) -->
      <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="form-group mb-0">
          <label for="cls_sec_id" class="sr-only">Class → Section</label>
          <select name="cls_sec_id" id="cls_sec_id" class="form-control form-control-sm" required>
            <option value="">Class → Section</option>
            <?php foreach ($sectionsclassinfo as $section):
              $opt_id    = is_array($section) ? ($section['cls_sec_id'] ?? $section['section_id'] ?? '') : ($section->cls_sec_id ?? $section->section_id ?? '');
              $labelParts = [];
              if (is_array($section)) {
                $labelParts[] = $section['class_short_name'] ?? ($section['class_name'] ?? '');
                $labelParts[] = $section['section_name'] ?? '';
              } else {
                $labelParts[] = $section->class_short_name ?? ($section->class_name ?? '');
                $labelParts[] = $section->section_name ?? '';
              }
              $opt_label = trim(implode(' — ', array_filter($labelParts)));
            ?>
              <option value="<?= esc($opt_id) ?>" <?= ($cls_sec_id == $opt_id ? 'selected' : '') ?>>
                <?= esc($opt_label) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div id="datesheetGrid" class="p-2">
        <div class="alert alert-info mb-0">
          Select a Class → Section. The latest active exam will load automatically.
        </div>
      </div>
    </div>
  </div>

</section>

<style>
  /* Keep things tidy and professional */
  #datesheetGrid .table { margin-bottom: 0; }

  /* Enable smooth horizontal scroll if many dates */
  #datesheetGrid .table-responsive { overflow-x: auto; }

  /* When your partial uses .datesheet-table, we apply layout hints */
  .datesheet-table {
    table-layout: auto; /* we’ll set explicit widths via JS */
    width: 100%;
  }

  /* Sticky header (optional, looks professional with wide tables) */
  .datesheet-table thead th {
    position: sticky;
    top: 0;
    background: #f8f9fa;
    z-index: 2;
  }

  /* Keep subject cells readable if they get long */
  .datesheet-table td:first-child, .datesheet-table th:first-child {
    white-space: nowrap; /* subject column stays on one line */
  }
</style>

<script>
(function () {

  function spinner() {
    return '<div class="text-center p-4"><span class="spinner-border text-primary spinner-border-sm"></span> Loading…</div>';
  }

  // Optional: include CSRF automatically if CI4 meta tags are present
  function addCsrf(data) {
    const csrfName = document.querySelector('meta[name="X-CSRF-Name"]')?.getAttribute('content');
    const csrfHash = document.querySelector('meta[name="X-CSRF-Token"]')?.getAttribute('content');
    if (csrfName && csrfHash) data[csrfName] = csrfHash;
    return data;
  }

  function loadGrid() {
    const cls_sec_id = document.getElementById('cls_sec_id').value;

    if (!cls_sec_id) {
      document.getElementById('datesheetGrid').innerHTML =
        '<div class="alert alert-warning mb-0">Please select a Class → Section.</div>';
      return;
    }

    document.getElementById('datesheetGrid').innerHTML = spinner();

    $.ajax({
      url: "<?= base_url('admin/datesheet/fetchgrid') ?>",
      method: "POST",
      // Send ONLY class-section; backend should default to latest active exam
      data: addCsrf({ cls_sec_id }),
      success: function (html) {
        $('#datesheetGrid').html(html);

        // If your partial doesn’t wrap the table, ensure there is a responsive container
        if (!$('#datesheetGrid .table-responsive').length && $('#datesheetGrid table').length) {
          $('#datesheetGrid').wrapInner('<div class="table-responsive"></div>');
        }

        // After content loads, adjust columns
        setTimeout(adjustGridColumns, 0);

        // Recalc on resize
        $(window).off('resize.datesheet').on('resize.datesheet', throttle(adjustGridColumns, 100));
      },
      error: function () {
        $('#datesheetGrid').html('<div class="alert alert-danger mb-0">Failed to load schedule.</div>');
      }
    });
  }

  // Throttle helper for resize
  function throttle(fn, wait) {
    let t = null;
    return function () {
      if (t) return;
      t = setTimeout(function () {
        t = null;
        fn();
      }, wait);
    };
  }

  /**
   * Column sizing logic:
   * - Subject column (first col) width = measured longest subject text + padding.
   * - Remaining columns (dates) share the rest equally.
   */
  function adjustGridColumns() {
    const $table = $('#datesheetGrid .datesheet-table').first().length
      ? $('#datesheetGrid .datesheet-table').first()
      : $('#datesheetGrid table').first(); // fallback

    if (!$table.length) return;

    const $wrapper = $table.closest('.table-responsive').length ? $table.closest('.table-responsive') : $('#datesheetGrid');
    const containerWidth = $wrapper.innerWidth() || $('#datesheetGrid').innerWidth();

    // Find header cells
    const $ths = $table.find('thead th');
    if (!$ths.length) return;

    // Count date cols (all except first)
    const dateColCount = $ths.length > 1 ? ($ths.length - 1) : 0;

    // Measure longest subject text
    const subjWidth = measureSubjectColumnWidth($table);

    // Compute remaining width for date columns
    const totalPadding = 24; // a little buffer for borders/scrollbars
    let remaining = Math.max(containerWidth - subjWidth - totalPadding, 100); // never negative

    let dateColWidth = dateColCount > 0 ? Math.floor(remaining / dateColCount) : 0;

    // Reasonable min/max for date columns
    const MIN_DATE_W = 90;
    const MAX_DATE_W = 260;
    if (dateColWidth < MIN_DATE_W) dateColWidth = MIN_DATE_W;
    if (dateColWidth > MAX_DATE_W) dateColWidth = MAX_DATE_W;

    // Apply widths
    setColumnWidth($table, 1, subjWidth);       // first column (index 1) = subject
    for (let i = 2; i <= $ths.length; i++) {    // remaining columns
      setColumnWidth($table, i, dateColWidth);
    }
  }

  function setColumnWidth($table, nth, px) {
    // nth is 1-based
    $table.find('thead th:nth-child(' + nth + '), tbody td:nth-child(' + nth + '), tfoot th:nth-child(' + nth + ')')
      .css({ width: px + 'px', minWidth: px + 'px', maxWidth: px + 'px' });
  }

  function measureSubjectColumnWidth($table) {
    // Collect all subject cell texts (first column)
    const $cells = $table.find('tbody tr td:first-child');
    const headerText = $table.find('thead th:first-child').text().trim();
    let longest = headerText;
    $cells.each(function () {
      const t = ($(this).text() || '').trim();
      if (t.length > longest.length) longest = t;
    });

    // Fallback font detection from the first cell
    const $probe = $cells.first().length ? $cells.first() : $table;
    const cs = window.getComputedStyle($probe.get(0));
    const font = `${cs.fontStyle} ${cs.fontVariant} ${cs.fontWeight} ${cs.fontSize} / ${cs.lineHeight} ${cs.fontFamily}`;

    // Canvas measure
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    ctx.font = font;

    const metrics = ctx.measureText(longest || 'Subject');
    // Add padding for cell padding + sort icons, etc.
    const extra = 40; // left/right padding + a little buffer
    const minSubj = 180; // minimum so it never becomes too narrow
    const maxSubj = Math.max(minSubj, Math.ceil(metrics.width + extra));
    return maxSubj;
  }

  // Auto-load when class-section changes
  $(document).on('change', '#cls_sec_id', loadGrid);

  // Auto-load on page if preselected
  $(function () {
    if ($('#cls_sec_id').val()) loadGrid();
  });

})();
</script>

<?= $this->endSection() ?>
