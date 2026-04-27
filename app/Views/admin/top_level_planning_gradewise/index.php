<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
// ---------- Ensure ALL are checked by default ----------
$_allTerms    = array_column($filter_data['all_terms']    ?? [], 'term_session_id');
$_allClasses  = array_column($filter_data['all_classes']  ?? [], 'class_id');
$_allSubjects = array_column($filter_data['all_subjects'] ?? [], 'sid');

$checkedTerms    = !empty($selected_filters['terms'])    ? array_map('intval', (array)$selected_filters['terms'])    : $_allTerms;
$checkedClasses  = !empty($selected_filters['classes'])  ? array_map('intval', (array)$selected_filters['classes'])  : $_allClasses;
$checkedSubjects = !empty($selected_filters['subjects']) ? array_map('intval', (array)$selected_filters['subjects']) : $_allSubjects;

// Updated date toggle (default: OFF)
$showUpdatedDate = !empty($selected_filters['show_updated_date']);
?>
<style>
/* ================== Screen (default) ================== */
.table-compact { font-size: 12px; }
.table-compact th,
.table-compact td { padding:.35rem .5rem !important; line-height:1.25; vertical-align:top; }

.tlp-obj  { display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:4; overflow:hidden; }
.tlp-date { font-size:.7rem; color:#6c757d; margin-top:.15rem; }

.btn-xxs { padding:.1rem .25rem; font-size:.65rem; line-height:1; }

/* Vertical subject label cell */
.tlp-subject-vertical { width:28px; padding:0 !important; text-align:center; }
.tlp-subject-vertical .vtext {
  display:inline-block; writing-mode:vertical-rl; transform:rotate(180deg);
  white-space:nowrap; line-height:1; padding:.25rem .15rem;
}

/* ================== Print overrides ================== */
@media print {
  html, body { background:#fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .main-header, .main-sidebar, .main-footer, .control-sidebar, .no-print { display:none !important; }
  .wrapper, .content-wrapper, .content, .container-fluid, .card, .card-body, .card-header {
    background:#fff !important; box-shadow:none !important;
  }
  .content-wrapper, .container-fluid, .card, .card-body { margin:0 !important; padding:0 !important; }

  body * { visibility:hidden; }
  #tplhtmlresult, #tplhtmlresult * { visibility:visible; }
  #tplhtmlresult { position:absolute; left:0; top:0; width:100%; }

  .table { font-size:11px; }
  .table th, .table td { padding:.25rem .35rem !important; }

  body:not(.no-print-break) #tlpTable tbody tr.dtrg-start { page-break-before: always; }
  body:not(.no-print-break) #tlpTable tbody tr.dtrg-start:first-of-type { page-break-before: auto; }

  body.no-print-break * {
    page-break-before: auto !important;
    page-break-after:  auto !important;
    page-break-inside: auto !important;
    break-before: auto !important;
    break-after:  auto !important;
    break-inside: auto !important;
  }
}
</style>

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h3 class="card-title">Top Level Planning (<?= esc($session_name) ?>)</h3>
          <div class="card-tools no-print d-flex align-items-center">
            <div class="custom-control custom-switch mr-3">
              <input type="checkbox" class="custom-control-input" id="tlpToggleBreaks">
              <label class="custom-control-label" for="tlpToggleBreaks">Remove page breaks</label>
            </div>

            <button type="button" class="btn btn-tool" onclick="window.print()">
              <i class="fas fa-print"></i> Print
            </button>
            <a href="<?= site_url('admin/top_level_planning_gradewise/create') ?>" class="btn btn-tool">
              <i class="fas fa-plus"></i> Add New
            </a>
          </div>
        </div>

        <div class="card-body">

          <!-- Filters -->
          <div class="row no-print mb-2">
            <div class="col-lg-12">
              <div class="card card-info">
                <div class="card-header">
                  <h3 class="card-title">Filters</h3>
                  <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                      <i class="fas fa-minus"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body">
                  <form id="filterFormAjax" onsubmit="return false;">
                    <!-- Terms -->
                    <div class="form-group">
                      <label>Terms:</label>
                      <div class="mb-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleCheckboxes('term', true)">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleCheckboxes('term', false)">Deselect All</button>
                      </div>
                      <div class="row">
                        <?php foreach(($filter_data['all_terms'] ?? []) as $term): ?>
                          <div class="col-md-3">
                            <div class="form-check">
                              <input class="form-check-input term-checkbox" type="checkbox"
                                     name="terms[]" value="<?= (int)$term['term_session_id'] ?>"
                                     <?= in_array((int)$term['term_session_id'], $checkedTerms, true) ? 'checked' : '' ?>>
                              <label class="form-check-label"><?= esc($term['term_name']) ?></label>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>

                    <!-- Classes -->
                    <div class="form-group mt-2">
                      <label>Classes:</label>
                      <div class="mb-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleCheckboxes('class', true)">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleCheckboxes('class', false)">Deselect All</button>
                      </div>
                      <div class="row">
                        <?php foreach(($filter_data['all_classes'] ?? []) as $class): ?>
                          <div class="col-md-3">
                            <div class="form-check">
                              <input class="form-check-input class-checkbox" type="checkbox"
                                     name="classes[]" value="<?= (int)$class['class_id'] ?>"
                                     <?= in_array((int)$class['class_id'], $checkedClasses, true) ? 'checked' : '' ?>>
                              <label class="form-check-label"><?= esc($class['class_name']) ?></label>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>

                    <!-- Subjects -->
                    <div class="form-group mt-2">
                      <label>Subjects:</label>
                      <div class="mb-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleCheckboxes('subject', true)">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleCheckboxes('subject', false)">Deselect All</button>
                      </div>
                      <div class="row">
                        <?php foreach(($filter_data['all_subjects'] ?? []) as $subject): ?>
                          <div class="col-md-3">
                            <div class="form-check">
                              <input class="form-check-input subject-checkbox" type="checkbox"
                                     name="subjects[]" value="<?= (int)$subject['sid'] ?>"
                                     <?= in_array((int)$subject['sid'], $checkedSubjects, true) ? 'checked' : '' ?>>
                              <label class="form-check-label">
                                <?= esc($subject['subject_name']) ?>
                              </label>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>

                    <!-- Show Updated Date (NEW) -->
                    <div class="form-group mt-2">
                      <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="showUpdatedDate" name="show_updated_date"
                               <?= $showUpdatedDate ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="showUpdatedDate">Show Updated Date</label>
                      </div>
                    </div>

                    <!-- Buttons -->
                    <div class="form-group text-right mt-2">
                      <button type="button" id="applyFiltersBtn" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                      </button>
                      <button type="button" class="btn btn-default" onclick="resetFilters()">
                        <i class="fas fa-undo"></i> Reset
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <!-- Report meta -->
          <div class="no-print mb-2">
            <strong>Top Level Planning Report</strong>
            <div class="text-muted">
              Campus: <?= esc(session('member_campusname')) ?> |
              Session: <?= esc($session_name) ?> |
              Generated: <?= date('d M Y H:i') ?>
            </div>
          </div>

          <!-- Results (fragment gets injected here on AJAX) -->
          <div id="tplhtmlresult">
            <?= view('admin/top_level_planning_gradewise/_results_fragment', [
              'grouped_data'       => $grouped_data,
              'show_updated_date'  => $showUpdatedDate, // pass to fragment on initial load
            ]) ?>
          </div>
        </div><!-- /.card-body -->
      </div>
    </div>
  </div>
</section>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/rowgroup/1.4.1/css/rowGroup.bootstrap4.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/rowgroup/1.4.1/js/dataTables.rowGroup.min.js"></script>

<script>
"use strict";

function resetFilters() {
  document.querySelectorAll('#filterFormAjax input[type="checkbox"]').forEach(cb => cb.checked = true);
  $('#applyFiltersBtn').trigger('click');
}
function toggleCheckboxes(type, check) {
  document.querySelectorAll('.' + type + '-checkbox').forEach(cb => cb.checked = check);
}

function initTlpDataTable() {
  var $tbl = $('#tlpTable');
  if (!$tbl.length) return;

  var colSpan = $tbl.find('thead th').length;
  var allCols = Array.from({length: colSpan}, (_, i) => i);

  if ($.fn.DataTable.isDataTable($tbl)) {
    $tbl.DataTable().destroy();
  }

  $tbl.DataTable({
    paging: true,
    pageLength: 200,
    lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'All']],
    autoWidth: false,
    ordering: false,
    dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    buttons: [
      {
        extend: 'csvHtml5',
        title: 'Top_Level_Planning_<?= preg_replace("/[^A-Za-z0-9_\\-]/", "_", $session_name) ?>',
        exportOptions: { columns: allCols }
      },
      {
        extend: 'pdfHtml5',
        title: 'Top Level Planning - <?= addslashes($session_name) ?>',
        pageSize: 'A4',
        orientation: 'landscape',
        exportOptions: { columns: allCols }
      }
    ],
    columnDefs: [
      { targets: 0, visible: false, searchable: true }, // hidden "Class" for grouping/search
      { targets: 1, width: 28 }
    ],
    rowGroup: {
      dataSrc: 0,
      startRender: function (rows, group) {
        return $('<tr class="dtrg-start"/>')
          .append('<td colspan="'+colSpan+'" class="text-center font-weight-bold bg-light">'+ group +'</td>');
      }
    }
  });
}

/** Toggle page-break behavior (CSS class gate) */
function applyPageBreaks(removeBreaks) {
  $('body').toggleClass('no-print-break', !!removeBreaks);
}

/** Wire the page-break switch and restore its saved state */
function wireUiToggles() {
  var keyBreak = 'tlp_no_print_break';
  var $chkBreak = $('#tlpToggleBreaks');
  var savedBreak = (localStorage.getItem(keyBreak) === '1');

  if ($chkBreak.length) {
    $chkBreak.prop('checked', savedBreak);
    applyPageBreaks(savedBreak);
    $chkBreak.on('change', function () {
      var on = $(this).is(':checked');
      localStorage.setItem(keyBreak, on ? '1' : '0');
      applyPageBreaks(on);
    });
  }
}

$(function () {
  // Safety net: if somehow nothing is checked (edge cases), check all on load.
  const $cbs = $('#filterFormAjax input[type=checkbox]');
  if (!$cbs.filter(':checked').length) $cbs.prop('checked', true);

  initTlpDataTable();
  wireUiToggles();

  $('#applyFiltersBtn').off('click').on('click', function () {
    $('#tplhtmlresult').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');

    let terms = [];   $('input[name="terms[]"]:checked').each(function(){ terms.push($(this).val()); });
    let classes = []; $('input[name="classes[]"]:checked').each(function(){ classes.push($(this).val()); });
    let subjects = [];$('input[name="subjects[]"]:checked').each(function(){ subjects.push($(this).val()); });
    let show_updated_date = $('#showUpdatedDate').is(':checked') ? 1 : 0; // NEW

    $.ajax({
      url: '<?= site_url("admin/top_level_planning_gradewise") ?>',
      method: 'POST',
      data: { terms, classes, subjects, show_updated_date, is_ajax: 1 }, // send flag
      success: function (html) {
        $('#tplhtmlresult').html(html);
        initTlpDataTable();

        // Re-apply page-break preference to the newly injected fragment
        var noBreaks = (localStorage.getItem('tlp_no_print_break') === '1');
        applyPageBreaks(noBreaks);
      },
      error: function () {
        $('#tplhtmlresult').html('<div class="alert alert-danger">Error loading data.</div>');
      }
    });
  });
});
</script>

<?= $this->endSection() ?>
