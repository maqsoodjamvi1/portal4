<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  // ---------- Month helpers (server-side safe defaults) ----------
  $refMonthStr = isset($ref_month) && preg_match('/^\d{4}-\d{2}$/', $ref_month ?? '') ? ($ref_month . '-01') : date('Y-m-01');
  $refMonth    = new DateTime($refMonthStr);

  $prevMonth = (clone $refMonth)->modify('-1 month');
  $currMonth = (clone $refMonth);
  $nextMonth = (clone $refMonth)->modify('+1 month');

  $prevKey = $prevMonth->format('Y-m');
  $currKey = $currMonth->format('Y-m');
  $nextKey = $nextMonth->format('Y-m');

  $prevLbl = $prevMonth->format('F Y');
  $currLbl = $currMonth->format('F Y');
  $nextLbl = $nextMonth->format('F Y');
?>

<?= view('components/bulk_students_header', [
  'title' => 'Bulk Fee Update',
  'subtitle' => 'Bulk Fee Update'
]) ?>

<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline shadow-sm">

      <!-- Tabs (optional, you can keep your original bar) -->
      <div class="card-header pb-0">
        <?= view('components/bulk_students_tabs', ['active' => 'fee']) ?>
      </div>

      <!-- Class picker -->
      <div class="p-3">
        <div class="alert alert-info py-2 px-3 mb-3 d-flex justify-content-between align-items-center">
          <span><strong>Fee Bulk Editor</strong> - Updated responsive view with full fee field save support.</span>
          <span class="badge badge-primary">v2</span>
        </div>
        <div class="row">
          <div class="col-lg-6 form-group">
            <label for="cls_sec_id"><strong>Class</strong></label>
            <select class="form-control" name="cls_sec_id" id="cls_sec_id">
              <option value="">All Classes</option>
              <?php if (!empty($sectionsclassinfo)) : ?>
                <?php foreach ($sectionsclassinfo as $sectionvalue) : ?>
                  <option value="<?= esc($sectionvalue['cls_sec_id']) ?>">
                    <?= esc($sectionvalue['sectionclassname']) ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Fee Columns selector -->
      <?php
        $base   = new DateTime('first day of this month');
        $prev   = (clone $base)->modify('-1 month');
        $curr   = (clone $base);
        $next   = (clone $base)->modify('+1 month');

        $prevShort = $prev->format('M Y');   $prevYm = $prev->format('Y-m');
        $currShort = $curr->format('M Y');   $currYm = $curr->format('Y-m');
        $nextShort = $next->format('M Y');   $nextYm = $next->format('Y-m');
      ?>
      <div class="p-3 pt-2 pb-0 border-bottom bg-light">
        <div class="row">
          <div class="col-lg-12">
            <div class="card h-100 shadow-sm">
              <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <strong>Fee Columns</strong>
                <div class="btn-group btn-group-sm" role="group">
                  <button type="button" id="mobileCardModeBtn" class="btn btn-outline-secondary d-none">Expanded View</button>
                  <button type="button" id="applyColsBtn" class="btn btn-primary">Apply</button>
                </div>
              </div>
              <div class="card-body py-2">
                <div class="d-flex flex-wrap align-items-center">
                  <!-- Month toggles -->
                  <div class="custom-control custom-checkbox mr-3 mb-2">
                    <input type="checkbox" class="custom-control-input upd-col upd-month" id="col_month_prev"
                           value="month_prev" data-ym="<?= esc($prevYm) ?>" data-target=".col-month_prev" checked>
                    <label class="custom-control-label" for="col_month_prev"><?= esc($prevShort) ?></label>
                  </div>
                  <div class="custom-control custom-checkbox mr-3 mb-2">
                    <input type="checkbox" class="custom-control-input upd-col upd-month" id="col_month_curr"
                           value="month_curr" data-ym="<?= esc($currYm) ?>" data-target=".col-month_curr" checked>
                    <label class="custom-control-label" for="col_month_curr"><?= esc($currShort) ?></label>
                  </div>
                  <div class="custom-control custom-checkbox mr-3 mb-2">
                    <input type="checkbox" class="custom-control-input upd-col upd-month" id="col_month_next"
                           value="month_next" data-ym="<?= esc($nextYm) ?>" data-target=".col-month_next" checked>
                    <label class="custom-control-label" for="col_month_next"><?= esc($nextShort) ?></label>
                  </div>

                  <!-- Fee fields -->
                  <div class="custom-control custom-checkbox mr-3 mb-2">
                   <input type="checkbox" class="custom-control-input upd-col" id="col_student_fee"
       value="student_fee" data-target=".col-student_fee" checked>
<label class="custom-control-label" for="col_student_fee">Student Fee</label>
                  </div>
                  <div class="custom-control custom-checkbox mr-3 mb-2">
                    <input type="checkbox" class="custom-control-input upd-col" id="col_fee_plan"
                           value="fee_plan" data-target=".col-fee_plan" checked>
                    <label class="custom-control-label" for="col_fee_plan">Fee Plan</label>
                  </div>

                  <!-- Hidden month shims get injected here (kept in row DOM) -->
                  <div id="month-shims" class="d-none"></div>
                </div>
                <small class="text-muted d-block mt-1">
                  Check the months and fee fields you want to show and update. Only visible columns will be saved.
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="card-body">
        <div id="studentsList"
             data-prev-key="<?= esc($prevKey) ?>"
             data-curr-key="<?= esc($currKey) ?>"
             data-next-key="<?= esc($nextKey) ?>"
             data-prev-lbl="<?= esc($prevLbl) ?>"
             data-curr-lbl="<?= esc($currLbl) ?>"
             data-next-lbl="<?= esc($nextLbl) ?>">
          <div class="table-sticky-wrap table-responsive">
            <table class="table table-sm table-striped mb-0" id="studentsTable">
              <thead>
                <tr>
                  <th class="sticky-col th-sno" style="width:70px;">S.No</th>
                  <th class="sticky-col-2 th-name">Student Name</th>

                  <!-- Fee-only headers -->
                  <th data-col="month_prev" style="min-width:180px;"><?= esc($prevLbl) ?></th>
                  <th data-col="month_curr" style="min-width:180px;"><?= esc($currLbl) ?></th>
                  <th data-col="month_next" style="min-width:180px;"><?= esc($nextLbl) ?></th>
                  <th data-col="student_fee" style="min-width:160px;">Student Fee</th>
                  <th data-col="fee_plan" style="min-width:140px;">Fee Plan</th>

                  <th class="text-right" style="width:110px;">Action</th>
                </tr>
              </thead>
              <tbody id="studentsTbody">
                <tr>
                  <td colspan="8" class="text-center text-muted">Select a class to view students…</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Loader -->
      <div id="loader-1" style="display:none;position:fixed;left:0;top:0;width:100vw;height:100vh;z-index:9999;background:rgba(255,255,255,0.7);">
        <div style="position:absolute;top:45%;left:50%;transform:translate(-50%,-50%);">
          <div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>
          <div>Loading...</div>
        </div>
      </div>

    </div>
  </div>
</section>

<style>
  #studentsTable th, #studentsTable td { vertical-align: middle; }
  .table-sticky-wrap { max-height: 70vh; overflow: auto !important; -webkit-overflow-scrolling: touch; }
  #studentsTable { width:100%; table-layout:fixed; border-collapse:separate; border-spacing:0; --sno-w:80px; --action-w:110px; min-width:640px; }
  #studentsTable th, #studentsTable td { background:#fff; background-clip:padding-box; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  #studentsTable th[data-col], #studentsTable td[data-col] { min-width:120px; }
  #studentsTable thead th { position:sticky; top:0; z-index:10; box-shadow:0 1px 0 rgba(0,0,0,0.05); }
  #studentsTable th.sticky-col, #studentsTable td.sticky-col { position:sticky; left:0; z-index:6; background:#fff; }
  @media (min-width:577px){ #studentsTable th.sticky-col-2, #studentsTable td.sticky-col-2 { position:sticky; left:var(--sno-w); z-index:5; background:#fff; } }
  @media (max-width:576px){ #studentsTable th.sticky-col-2, #studentsTable td.sticky-col-2 { position:static; left:auto; z-index:auto; } }
  #studentsTable thead th.sticky-col, #studentsTable thead th.sticky-col-2 { z-index:12; }
  #studentsTable .th-sno, #studentsTable .sno-cell { width:var(--sno-w); min-width:var(--sno-w); max-width:var(--sno-w); padding-left:.5rem; padding-right:.5rem; }
  #studentsTable .action-cell { width:var(--action-w); }
  #studentsTable .th-name, #studentsTable .student-name-cell { border-right:1px solid #e9ecef; width:clamp(140px,40vw,280px); max-width:clamp(140px,40vw,280px); }
  @media (max-width:992px){ #studentsTable .th-name, #studentsTable .student-name-cell { width:clamp(140px,36vw,240px); max-width:clamp(140px,36vw,240px); } }
  @media (max-width:768px){ #studentsTable { font-size:13px; } #studentsTable .th-name, #studentsTable .student-name-cell { width:clamp(140px,52vw,220px); max-width:clamp(140px,52vw,220px); } }
  @media (max-width:576px){ #studentsTable { font-size:12px; } #studentsTable .th-name, #studentsTable .student-name-cell { width:clamp(140px,55vw,200px); max-width:clamp(140px,55vw,200px); } }
  .is-tampered { box-shadow: inset 0 0 0 2px rgba(255,0,0,.35); }
</style>

<script>
(function(){
  'use strict';

  // ---- Endpoints (adjust to your routes) ----
  const URL_DATA = "<?= base_url('admin/students_bulk_fee_info/data') ?>";
  const URL_SAVE = "<?= base_url('admin/students_bulk_fee_info/save_student_info') ?>";

  const CSRF_NAME = "<?= csrf_token() ?>";
  let   CSRF_HASH = "<?= csrf_hash() ?>";

  // ----- Column selection state (fee-only) -----
  let selectedColumns = new Set(["month_prev","month_curr","month_next","student_fee","fee_plan"]);

  function readSelectedColumns(){
    selectedColumns.clear();
    document.querySelectorAll('.upd-col:checked').forEach(cb => selectedColumns.add(cb.value));
  }

  // ----- Templates for the only editable cells we allow here -----
  const TPL = {
    student_fee: () => `<input name="student_fee" class="form-control form-control-sm text-right" placeholder="0.00" inputmode="decimal">`,
    fee_plan: () => `
      <select name="fee_plan" class="form-control form-control-sm">
        <option value="0">Monthly</option>
        <option value="1">Bi-monthly</option>
        <option value="2">Quarterly</option>
        <option value="3">Annually</option>
      </select>`
  };

  // ----- Build visible table based on selection -----
  function applySelectionToTable() {
    const headerLabelMap = {};
    document.querySelectorAll('#studentsTable thead [data-col]').forEach(th => {
      const key = th.getAttribute('data-col');
      headerLabelMap[key] = (th.textContent || '').trim() || key;
    });

    // Toggle header visibility
    document.querySelectorAll('#studentsTable thead [data-col]').forEach(th => {
      const col = th.getAttribute('data-col');
      th.classList.toggle('d-none', !selectedColumns.has(col));
    });
    // Toggle body visibility and inject inputs when missing
    document.querySelectorAll('#studentsTable tbody tr').forEach(tr => {
      // Keep only fee-related cells
      tr.querySelectorAll('td[data-col]').forEach(td => {
        const key = td.getAttribute('data-col');
        td.setAttribute('data-label', headerLabelMap[key] || key);
        // Inject only when missing (do not overwrite server-filled values)
        if (key === 'student_fee' && !td.querySelector('[name="student_fee"]')) {
          td.innerHTML = TPL.student_fee();
        }
        if (key === 'fee_plan' && !td.querySelector('[name="fee_plan"]')) {
          td.innerHTML = TPL.fee_plan();
        }
        td.classList.toggle('d-none', !selectedColumns.has(key));
      });
    });
    renumberRows();
  }

  function renumberRows(){
    document.querySelectorAll('#studentsTable tbody tr').forEach((tr, idx) => {
      const sno = tr.querySelector('td.sno-cell');
      if (!sno) return;
      sno.setAttribute('data-label', 'S.No');
      const nameCell = tr.querySelector('td.student-name-cell');
      if (nameCell) nameCell.setAttribute('data-label', 'Student Name');
      const hid = sno.querySelector('[name="student_id"]');
      sno.textContent = String(idx+1);
      if (hid) sno.appendChild(hid);
      const actionCell = tr.querySelector('td.action-cell');
      if (actionCell) actionCell.setAttribute('data-label', 'Action');
    });
  }

  function isMobileViewport() {
    return window.matchMedia('(max-width: 768px)').matches;
  }

  function refreshMobileCardModeUI() {
    const $btn = $('#mobileCardModeBtn');
    if (!$btn.length) return;

    if (!isMobileViewport()) {
      $('body').removeClass('mobile-card-compact');
      $btn.addClass('d-none');
      return;
    }

    $btn.removeClass('d-none');
    const compact = $('body').hasClass('mobile-card-compact');
    $btn.text(compact ? 'Expanded View' : 'Compact View');
    $btn.toggleClass('btn-outline-secondary', compact);
    $btn.toggleClass('btn-outline-primary', !compact);
  }

  // ----- Month helpers -----
  function getSelectedMonths(){
    const cont = document.getElementById('studentsList');
    const map = {
      month_prev: { key: cont.dataset.prevKey, label: cont.dataset.prevLbl, col: 'month_prev' },
      month_curr: { key: cont.dataset.currKey, label: cont.dataset.currLbl, col: 'month_curr' },
      month_next: { key: cont.dataset.nextKey, label: cont.dataset.nextLbl, col: 'month_next' },
    };
    const months = [];
    ['month_prev','month_curr','month_next'].forEach(k => { if (selectedColumns.has(k)) months.push(map[k]); });
    return months;
  }

  function ensureMonthShimInRow(row, ym, on){
    if (!ym) return;
    const sel = `.shim-month[data-ym="${ym}"]`;
    if (on){
      if (!row.querySelector(sel)) {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = `months[${ym}][apply]`;
        inp.value = '1';
        inp.className = 'shim-month';
        inp.dataset.ym = ym;
        row.appendChild(inp);
      }
    } else {
      row.querySelectorAll(sel).forEach(n => n.remove());
    }
  }

  function syncMonthShimsForAllRows(){
    const onSet = new Set(Array.from(document.querySelectorAll('.upd-month:checked')).map(cb => cb.dataset.ym));
    document.querySelectorAll('#studentsTable tbody tr').forEach(tr => {
      tr.querySelectorAll('.shim-month').forEach(n => n.remove());
      onSet.forEach(ym => ensureMonthShimInRow(tr, ym, true));
    });
  }

  // ----- Fee input helpers -----
  function parseNum(v){ const n = parseFloat(String(v).replace(/,/g,'')); return isFinite(n) ? n : 0; }

  function formatFees(scope){
    (scope || document).querySelectorAll('[name="student_fee"]').forEach(inp => {
      const v = inp.value.trim();
      if (v === '') return;
      inp.value = parseNum(v).toFixed(2);
    });
  }

  // ----- Load students by class -----
  function loadStudentsByClass(){
    const cls_sec_id = document.getElementById('cls_sec_id').value;
    const months     = getSelectedMonths();

    if (!cls_sec_id){
      document.getElementById('studentsTbody').innerHTML =
        '<tr><td colspan="8" class="text-center text-muted">Select a class to view students…</td></tr>';
      return;
    }

    document.getElementById('loader-1').style.display = 'block';

    $.ajax({
      url: URL_DATA,
      type: "POST",
      data: {
        cls_sec_id: cls_sec_id,
        months_json: JSON.stringify(months),
        ref_month: document.getElementById('studentsList').dataset.currKey,
        <?= session('campus_id') ? "campus_id: ".(int)session('campus_id')."," : "" ?>
        [CSRF_NAME]: CSRF_HASH
      },
      success: function(res, _status, xhr){
        const newToken = xhr.getResponseHeader && (xhr.getResponseHeader('X-CSRF-TOKEN') || xhr.getResponseHeader('X-CSRF-Token'));
        if (newToken) CSRF_HASH = newToken;

        $('#studentsTbody').html(res || '<tr><td colspan="8" class="text-center text-info">No students found.</td></tr>');
        applySelectionToTable();
        syncMonthShimsForAllRows();
        formatFees(document.getElementById('studentsTable'));
        document.getElementById('loader-1').style.display = 'none';
      },
      error: function(){
        document.getElementById('loader-1').style.display = 'none';
        $('#studentsTbody').html('<tr><td colspan="8" class="text-center text-danger">Failed to load students.</td></tr>');
      }
    });
  }

  // ----- Save per row (fee-only) -----
  $(document).on('click', '.saveStudentBtn', function(){
    const $row = $(this).closest('tr');
    const row  = $row.get(0);
    const fd   = new FormData();

    // Required identifiers
    const sidInput = row.querySelector('[name="student_id"]');
    if (!sidInput){ window.toastr && toastr.error('Missing student_id in row.'); return; }
    fd.append('student_id', sidInput.value);

    // Selected backend fields (map UI "student_fee" -> students.discounted_amount)
    if (selectedColumns.has('student_fee')) fd.append('selected_fields[]', 'discounted_amount');
    if (selectedColumns.has('fee_plan'))    fd.append('selected_fields[]', 'fee_plan');

    // Fee fields
    const feeInp = row.querySelector('[name="student_fee"]');
    if (feeInp && selectedColumns.has('student_fee')){
      const studentFee = parseNum(feeInp.value);
      const classFeeInp = row.querySelector('[name="class_fee"]');
      const classFee = classFeeInp ? parseNum(classFeeInp.value) : 0;
      const discount = Math.max(0, classFee - studentFee);
      fd.append('discounted_amount', discount.toFixed(2));
    }
    const planSel = row.querySelector('[name="fee_plan"]');
    if (planSel && selectedColumns.has('fee_plan')){
      fd.append('fee_plan', planSel.value);
    }

    // Selected months
    const selectedMonthCols = getSelectedMonths().map(m => m.col);
    fd.append('selected_month_cols_json', JSON.stringify(selectedMonthCols));
    // month shims
    $row.find(':input[name^="months["]').each(function(){
      const $inp = $(this);
      const name = $inp.attr('name');
      if ($inp.is(':checkbox')) { if ($inp.is(':checked')) fd.append(name, $inp.val() || '1'); }
      else fd.append(name, $inp.val() || '');
    });

    fd.append('ref_month', document.getElementById('studentsList').dataset.currKey || '');
    fd.append("<?= csrf_token() ?>", CSRF_HASH);

    $.ajax({
      url: URL_SAVE,
      type: "POST",
      data: fd,
      contentType: false,
      processData: false,
      beforeSend: function(){ $('#loader-1').show(); },
      success: function(res, _status, xhr){
        $('#loader-1').hide();
        const newToken = xhr.getResponseHeader && (xhr.getResponseHeader('X-CSRF-TOKEN') || xhr.getResponseHeader('X-CSRF-Token'));
        if (newToken) CSRF_HASH = newToken;

        if (res && res.success){
          window.toastr && toastr.success(res.msg || 'Fee info updated.');
        } else {
          const errs = res && res.errors ? JSON.stringify(res.errors) : '';
          window.toastr && toastr.error(((res && res.msg) || 'Error saving fee info.') + (errs ? ' ' + errs : ''));
        }
      },
      error: function(){
        $('#loader-1').hide();
        window.toastr && toastr.error('AJAX error.');
      }
    });
  });

  // ----- UI bindings -----
  $(document).on('change', '#cls_sec_id', function(){
    loadStudentsByClass();
  });

  // When toggling fee/month columns
  $(document).on('change', '.upd-col', function(){
    readSelectedColumns();
    applySelectionToTable();
    syncMonthShimsForAllRows();

    const v = this.value;
    if (v === 'month_prev' || v === 'month_curr' || v === 'month_next'){
      // Reload data so month columns reflect selected months server-side (if needed)
      if ($('#cls_sec_id').val()) loadStudentsByClass();
    }
  });

  // Apply button
  $('#applyColsBtn').on('click', function(){
    readSelectedColumns();
    applySelectionToTable();
    syncMonthShimsForAllRows();
    window.toastr && toastr.info('Column selection applied.');
    if ($('#cls_sec_id').val()) loadStudentsByClass();
  });

  // Mobile compact/expanded toggle
  $(document).on('click', '#mobileCardModeBtn', function () {
    if (!isMobileViewport()) return;
    $('body').toggleClass('mobile-card-compact');
    refreshMobileCardModeUI();
  });

  // Fee formatting on blur
  $(document).on('blur', '[name="student_fee"]', function(){
    this.value = parseNum(this.value).toFixed(2);
  });

  // Auto-load if class is preselected
  if ($('#cls_sec_id').val()){
    loadStudentsByClass();
  }
  if (isMobileViewport()) {
    $('body').addClass('mobile-card-compact');
  }
  refreshMobileCardModeUI();
  $(window).on('resize', function () { refreshMobileCardModeUI(); });
})();
</script>

<style>
  /* Mobile-first controls polish */
  @media (max-width: 768px) {
    .content .card-header .nav { flex-wrap: nowrap; overflow-x: auto; white-space: nowrap; }
    .content .card-header .nav .nav-item { float: none; display: inline-block; }
    .content .row > [class*="col-"] { margin-bottom: .75rem; }
    .content .btn-group { display: flex; width: 100%; }
    .content .btn-group .btn { flex: 1 1 auto; }
    #studentsTable td .form-control,
    #studentsTable td select,
    #studentsTable td .btn { width: 100%; max-width: 100%; }
  }

  /* Mobile card view */
  @media (max-width: 768px) {
    .table-sticky-wrap { max-height: none; overflow: visible !important; }
    #studentsTable { min-width: 100%; width: 100%; table-layout: auto; border-collapse: separate; border-spacing: 0; }
    #studentsTable thead { display: none; }
    #studentsTable tbody, #studentsTable tr, #studentsTable td { display: block; width: 100%; }
    #studentsTable tbody tr {
      border: 1px solid #dee2e6;
      border-radius: 10px;
      margin-bottom: .85rem;
      background: #fff;
      box-shadow: 0 1px 4px rgba(0,0,0,.06);
      overflow: hidden;
    }
    #studentsTable tbody td {
      border: 0;
      border-bottom: 1px solid #f1f3f5;
      padding: .6rem .75rem;
      white-space: normal;
      overflow: visible;
      text-overflow: initial;
      position: static !important;
      left: auto !important;
      z-index: auto !important;
      max-width: 100% !important;
    }
    #studentsTable tbody td:last-child { border-bottom: 0; }
    #studentsTable tbody td::before {
      content: attr(data-label);
      display: block;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      color: #6c757d;
      margin-bottom: .3rem;
      letter-spacing: .02em;
    }
    #studentsTable .sno-cell,
    #studentsTable .student-name-cell { background: #f8f9fa; }
    #studentsTable .sno-cell { font-weight: 600; }

    /* Compact mode: keep key fee-editing fields visible */
    body.mobile-card-compact #studentsTable tbody td[data-col] { display: none !important; }
    body.mobile-card-compact #studentsTable tbody td[data-col="month_prev"],
    body.mobile-card-compact #studentsTable tbody td[data-col="month_curr"],
    body.mobile-card-compact #studentsTable tbody td[data-col="month_next"],
    body.mobile-card-compact #studentsTable tbody td[data-col="student_fee"],
    body.mobile-card-compact #studentsTable tbody td[data-col="fee_plan"] {
      display: block !important;
    }
    body.mobile-card-compact #studentsTable tbody td.action-cell {
      display: block !important;
    }
  }
</style>

<?= $this->endSection() ?>
