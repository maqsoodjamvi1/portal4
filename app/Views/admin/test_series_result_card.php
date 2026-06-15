<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<?= view('components/page_header', [
    'title' => 'Test Results – Student Cards',
    'icon' => 'fas fa-poll',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Test Results', 'active' => true],
    ],
]) ?>


<section class="content">
  <div class="card card-primary card-outline">
    <div class="card-header">
      <h3 class="card-title">Filter</h3>
    </div>
    <div class="card-body">
      <form id="resultFilterForm" class="mb-2">
        <?= csrf_field() ?>
        <div class="row">
          <div class="form-group col-md-3">
            <label for="cls_sec_id">Class Section <span class="text-danger">*</span></label>
          <select class="form-control" id="cls_sec_id" name="cls_sec_id" required>
  <option value="">-- Select --</option>
  <?php foreach ($sectionsclassinfo as $section): 
        // support array/object + both keys just in case
        $value = is_array($section)
          ? ($section['cls_sec_id'] ?? $section['section_id'] ?? '')
          : ($section->cls_sec_id   ?? $section->section_id   ?? '');
  ?>
    <option value="<?= esc($value) ?>"
            <?= (string)($cls_sec_id ?? '') === (string)$value ? 'selected' : '' ?>>
      <?= esc(is_array($section) ? $section['sectionclassname'] : $section->sectionclassname) ?>
    </option>
  <?php endforeach; ?>
</select>
          </div>

          <div class="form-group col-md-2">
            <label for="start_date">Start Date</label>
            <input type="date" class="form-control" id="start_date" name="start_date">
            <small class="form-text text-muted">Blank = current term start</small>
          </div>

          <div class="form-group col-md-2">
            <label for="end_date">End Date</label>
            <input type="date" class="form-control" id="end_date" name="end_date">
            <small class="form-text text-muted">Blank = current term end</small>
          </div>

          <div class="form-group col-md-3">
            <label for="subject_id">Subject</label>
            <select class="form-control" id="subject_id" name="subject_id">
              <option value="">-- All Subjects --</option>
              <?php foreach ($subjects as $subject): ?>
                <option value="<?= (int)$subject->sid ?>"><?= esc($subject->subject_name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group col-md-2 d-flex align-items-center">
            <div class="form-check mt-3">
              <input class="form-check-input" type="checkbox" id="show_percentage" name="show_percentage" value="1">
              <label class="form-check-label" for="show_percentage">Show %</label>
            </div>
          </div>
        </div>

        <div class="d-flex">
  <button type="submit" id="btnView" class="btn btn-primary">
    <i class="fas fa-eye"></i> View Results
  </button>
  <button type="button" id="btnReset" class="btn btn-secondary ms-2">
    <i class="fas fa-undo"></i> Reset
  </button>

  <!-- NEW: Compact switch -->
  <div class="form-check form-switch ms-3 d-flex align-items-center">
    <input type="checkbox" class="form-check-input" id="toggleCompact">
    <label class="form-check-label" for="toggleCompact">Compact view</label>
  </div>

  <button type="button" id="btnPrint" class="btn btn-outline-secondary ms-auto">
    <i class="fas fa-print"></i> Print
  </button>
</div>
      </form>

      <hr/>

      <div id="loader" class="text-center" style="display:none;">
        <i class="fas fa-sync-alt fa-spin fa-2x"></i> Loading...
      </div>

      <!-- Cards come here -->
      <div id="resultsContainer" class="mt-3"></div>
    </div>
  </div>
</section>



<script>
(function(){
  const $form   = $('#resultFilterForm');
  const $loader = $('#loader');
  const $cont   = $('#resultsContainer');
  const $btn    = $('#btnView');

  function setLoading(on){
    if(on){
      $loader.show();
      $btn.prop('disabled', true).html('<i class="fas fa-sync-alt fa-spin"></i> Loading');
    } else {
      $loader.hide();
      $btn.prop('disabled', false).html('<i class="fas fa-eye"></i> View Results');
    }
  }

  function fetchCards(){
    setLoading(true);
    $cont.empty();

    $.ajax({
      url: "<?= base_url('admin/test-result-card/data') ?>",
      type: "POST",
      data: $form.serialize(),
      success: function(res){
        $cont.html(res);
        setLoading(false);
      },
      error: function(xhr){
        $cont.html('<div class="alert alert-danger">Error loading results ('+xhr.status+').</div>');
        setLoading(false);
      }
    });
  }

  // Submit
  $form.on('submit', function(e){
    e.preventDefault();
    if(!$('#cls_sec_id').val()){
      alert('Please select a Class Section.');
      return;
    }
    fetchCards();
  });

  // Reset filters
  $('#btnReset').on('click', function(){
    $('#cls_sec_id').val('');
    $('#start_date').val('');
    $('#end_date').val('');
    $('#subject_id').val('');
    $('#show_percentage').prop('checked', false);
    $cont.empty();
  });

  // Print cards
  $('#btnPrint').on('click', function(){
    window.print();
  });

  // Optional: auto-load when class-section picked
  // $('#cls_sec_id').on('change', function(){ if($(this).val()) $form.submit(); });
})();


(function(){
  const $cont = $('#resultsContainer');

  $('#toggleCompact').on('change', function(){
    $cont.toggleClass('compact', this.checked);
  });

  // Optional: remember user choice
  if (sessionStorage.getItem('tsrc_compact') === '1') {
    $('#toggleCompact').prop('checked', true);
    $cont.addClass('compact');
  }
  $('#toggleCompact').on('change', function(){
    sessionStorage.setItem('tsrc_compact', this.checked ? '1' : '0');
  });
})();

</script>

<style>
/* ===== Base / Layout ===== */
.result-cards{
  display:grid;
  gap:.6rem;
  grid-template-columns:repeat(1, minmax(0,1fr));  /* phones: 1 col */
}

/* Breakpoints → 2 / 3 / 4 / 5 columns */
@media (min-width:576px){ .result-cards{ grid-template-columns:repeat(2, minmax(0,1fr)); } }
@media (min-width:768px){ .result-cards{ grid-template-columns:repeat(3, minmax(0,1fr)); } }
@media (min-width:992px){ .result-cards{ grid-template-columns:repeat(4, minmax(0,1fr)); } }
@media (min-width:1200px){ .result-cards{ grid-template-columns:repeat(5, minmax(0,1fr)); } } /* ← always 5 per row on large screens */

.result-card{
  border:1px solid #e5e7eb;
  border-radius:.5rem;
  padding:.6rem .7rem;
  background:#fff;
}

/* ===== Card internals ===== */
.result-card .rc-head{
  display:flex;
  justify-content:space-between;
  align-items:baseline;
  margin-bottom:.35rem;
  gap:.5rem;
}
.result-card .rc-title{ font-weight:600; font-size:.95rem; margin:0; }
.result-card .rc-date{ font-size:.78rem; color:#6b7280; white-space:nowrap; }
.result-card .rc-syllabus{ font-size:.8rem; color:#6b7280; margin-bottom:.45rem; }
.result-card .rc-row{ display:flex; align-items:center; justify-content:space-between; gap:.5rem; }
.result-card .rc-marks{ font-weight:600; font-size:.9rem; padding:.12rem .4rem; border-radius:.4rem; background:#f3f4f6; }
.result-card .rc-badge{ font-size:.72rem; padding:.15rem .4rem; border-radius:.35rem; }
.result-card .rc-percent{ font-size:.78rem; padding:.12rem .4rem; border-radius:.35rem; background:#e6f2ff; color:#0b63bf; font-weight:600; }

.result-footer{
  display:flex;
  gap:.4rem;
  align-items:center;
  justify-content:flex-end;
  margin-top:.4rem;
  font-size:.8rem;
}
.result-footer .pill{ padding:.12rem .45rem; border-radius:.45rem; background:#f3f4f6; font-weight:600; }

/* ===== Compact mode (toggle by adding .compact to #resultsContainer) ===== */
#resultsContainer.compact .result-cards{ gap:.45rem; }
#resultsContainer.compact .result-card{ padding:.42rem .5rem; border-radius:.4rem; }
#resultsContainer.compact .rc-head{ margin-bottom:.25rem; gap:.35rem; }
#resultsContainer.compact .rc-title{ font-size:.85rem; }
#resultsContainer.compact .rc-date{ font-size:.7rem; }
#resultsContainer.compact .rc-syllabus{ font-size:.72rem; margin-bottom:.3rem; }
#resultsContainer.compact .rc-row{ gap:.35rem; }
#resultsContainer.compact .rc-marks{ font-size:.82rem; padding:.08rem .32rem; border-radius:.3rem; }
#resultsContainer.compact .rc-badge{ font-size:.66rem; padding:.08rem .3rem; border-radius:.28rem; }
#resultsContainer.compact .rc-percent{ font-size:.7rem; padding:.08rem .3rem; border-radius:.28rem; }
#resultsContainer.compact .result-footer{ font-size:.72rem; gap:.3rem; }
#resultsContainer.compact .result-footer .pill{ padding:.08rem .35rem; border-radius:.35rem; }

/* Tighten legacy table (if fallback markup renders a table) */
#resultsContainer.compact #resultTable { font-size:.85rem; }
#resultsContainer.compact #resultTable td,
#resultsContainer.compact #resultTable th { padding:.28rem .35rem; }
#resultsContainer.compact #resultTable thead th small { font-size:.7rem; }

/* ===== Print tweaks ===== */
@media print{
  @page { size:A4; margin:8mm; }
  body { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
  .main-header,.main-sidebar,.main-footer,.content-header,.card-header,form,hr,#btnPrint{ display:none !important; }
  .content .card{ border:none; box-shadow:none; }

  /* Always print compact + 5 columns if space allows */
  #resultsContainer .result-cards{
    gap:.45rem !important;
    grid-template-columns:repeat(5, minmax(0,1fr)) !important;
  }
  #resultsContainer .result-card{ padding:.42rem .5rem !important; }
  #resultsContainer .rc-title{ font-size:.82rem !important; }
  #resultsContainer .rc-date{ font-size:.68rem !important; }
  #resultsContainer .rc-syllabus{ font-size:.7rem !important; margin-bottom:.25rem !important; }
  #resultsContainer .rc-marks{ font-size:.78rem !important; padding:.06rem .28rem !important; }
  #resultsContainer .rc-percent{ font-size:.68rem !important; padding:.06rem .28rem !important; }
  #resultsContainer .rc-badge{ font-size:.62rem !important; padding:.06rem .26rem !important; }
  #resultsContainer .result-footer{ font-size:.7rem !important; gap:.25rem !important; }
  #resultsContainer .result-footer .pill{ padding:.06rem .3rem !important; }
}
</style>




<?= $this->endSection() ?>
