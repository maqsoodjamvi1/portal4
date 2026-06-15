<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php 
  $status = ''; 
  if(!empty($_GET['status'])){
   $status = $_GET['status']; 
  }
?>

<link href="https://fonts.googleapis.com/css2?family=Amiri+Quran&display=swap" rel="stylesheet">

<?= view('components/bulk_students_header', [
  'title' => 'Class Change',
  'subtitle' => 'Class Change'
]) ?>

<!-- Main Content -->
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header pb-0">
        <?= view('components/bulk_students_tabs', ['active' => 'class']) ?>
      </div>

    <div class="">
    <div class="col-lg-6 form-group">
      <label for="class"><strong>Class</strong></label><br>
       <select id="filter_cls_sec_id" class="form-control">
  <option value="0">All Class Sections</option>
  <option value="-1">No Class Assigned (current session)</option>
  <?php foreach ($sectionsclassinfo as $sec): 
        $val  = (int)($sec['cls_sec_id'] ?? $sec['section_id'] ?? 0);
        $text = $sec['sectionclassname']
             ?? (($sec['class_name'] ?? '').' ('.($sec['section_name'] ?? '').')'); ?>
    <option value="<?= esc($val) ?>"><?= esc($text) ?></option>
  <?php endforeach; ?>
</select>
<input type="hidden" name="current_discount" id="current_discount" value="<?= $discounted_amount ?? 0 ?>">
    </div>
    </div>
      <div class="card-body">

      <?php if (! empty($hifz_enabled)): ?>
      <div class="alert sb-hifz-page-alert py-2 small mb-3">
        <i class="fas fa-quran me-1"></i>
        <strong>Hifz enrollment:</strong> check <em>Hifz student</em>, set section, plan order, <strong>current para</strong>, and <strong>lines done</strong> (0–320).
        Sabqi and Manzil pools are calculated automatically (lines below <?= (int) hifzParaHalfLines() ?> = two Sabqi paras).
        Daily Mutalia lines are entered on <a href="<?= base_url('admin/hifz/recitation') ?>">Daily Recitation</a>.
      </div>
      <?php endif; ?>

      <div id="studentsList"></div>
      </div>
    </div>
  </div>
</section>

<style>
.sb-bulk-table { font-size: 13px; }
.sb-bulk-table > thead > tr > th { vertical-align: middle; background: #f8f9fa; }
.sb-bulk-responsive { width: 100%; }
.sb-hifz-row-inputs { grid-template-columns: repeat(3, minmax(0, 1fr)); align-items: start; }
.sb-hifz-row-inputs .sb-hifz-field { grid-column: auto; min-width: 0; }
@media (max-width: 575px) {
  .sb-hifz-row-inputs { grid-template-columns: 1fr; }
}
.sb-bulk-card-toggle { display: none; border: 0; background: transparent; padding: 0 .35rem; color: #0d5c46; cursor: pointer; }
.sb-bulk-card-chevron { transition: transform .2s; }
@media (max-width: 767.98px) {
  .sb-bulk-responsive .sb-bulk-table thead { display: none; }
  .sb-bulk-responsive .sb-bulk-table,
  .sb-bulk-responsive .sb-bulk-table tbody,
  .sb-bulk-responsive .sb-bulk-table tr,
  .sb-bulk-responsive .sb-bulk-table td { display: block; width: 100% !important; max-width: 100%; }
  .sb-bulk-responsive .sb-bulk-table tr.sb-bulk-student-row {
    border: 1px solid #dee2e6;
    border-radius: .5rem;
    margin-bottom: .65rem;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
    overflow: hidden;
  }
  .sb-bulk-responsive .sb-bulk-table tr.sb-bulk-student-row > td.sb-bulk-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
    padding: .75rem .85rem;
    background: #f4f7fb;
    border-bottom: 1px solid #e8ecf1;
    cursor: pointer;
  }
  .sb-bulk-card-toggle { display: inline-flex; align-items: center; flex-shrink: 0; }
  .sb-bulk-responsive .sb-bulk-table tr.sb-bulk-student-row:not(.sb-bulk-expanded) > td[data-sb-bulk-detail] { display: none !important; }
  .sb-bulk-responsive .sb-bulk-table tr.sb-bulk-student-row.sb-bulk-expanded .sb-bulk-card-chevron { transform: rotate(180deg); }
  .sb-bulk-responsive .sb-bulk-table tr.sb-bulk-student-row > td[data-sb-bulk-detail] {
    padding: .65rem .85rem;
    border-top: 1px solid #f0f0f0;
  }
  .sb-bulk-responsive .sb-bulk-table tr.sb-bulk-student-row > td[data-sb-bulk-detail]::before {
    content: attr(data-label);
    display: block;
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: #6c757d;
    margin-bottom: .35rem;
  }
  .sb-bulk-responsive .sb-bulk-table tr.sb-bulk-student-row > td.sb-bulk-card-action .js-save { width: 100%; }
  .sb-bulk-responsive .sb-bulk-table tr.sb-bulk-student-row > td.sb-bulk-card-action::before { display: none; }
}
.sb-hifz-page-alert {
  background: linear-gradient(135deg, #f0faf5 0%, #faf8f3 100%);
  border: 1px solid #b8dcc8;
  color: #1e4d38;
}
.sb-hifz-panel {
  background: linear-gradient(180deg, #faf8f3 0%, #f4faf6 100%);
  border: 1px solid #c5dcc9;
  border-start: 4px solid #0d5c46;
  border-radius: .45rem;
  padding: .75rem .85rem;
}
.sb-hifz-panel-off { opacity: .65; }
.sb-hifz-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: .65rem .85rem;
  align-items: end;
  margin-bottom: .65rem;
}
.sb-hifz-row-top { grid-template-columns: auto 1fr 1fr; }
@media (max-width: 767px) {
  .sb-hifz-row-top { grid-template-columns: 1fr; }
}
.sb-hifz-pool-card {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: .35rem;
  padding: .5rem .6rem;
}
.sb-hifz-pool-manzil { border-top: 3px solid #e0a800; }
.sb-hifz-pool-sabqi { border-top: 3px solid #17a2b8; }
.sb-hifz-computed-row { margin-top: .25rem; }
.sb-hifz-summary { font-size: .85rem; font-weight: 600; color: #0d5c46; }
.sb-hifz-check {
  display: flex;
  align-items: center;
  gap: .35rem;
  font-weight: 600;
  white-space: nowrap;
  min-height: 38px;
}
.sb-hifz-field { min-width: 0; }
.sb-hifz-field-wide { grid-column: span 2; }
.sb-hifz-field-span2 { grid-column: 1 / -1; }
.sb-hifz-label {
  display: block;
  font-size: .72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .03em;
  color: #6c757d;
  margin-bottom: .2rem;
}
.sb-hifz-mode {
  margin-top: .65rem;
  padding-top: .65rem;
  border-top: 1px dashed #d6e4f0;
}
.sb-hifz-mode-title {
  font-size: .78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .04em;
  color: #495057;
  margin-bottom: .45rem;
}
.sb-hifz-help {
  font-size: .78rem;
  color: #6c757d;
  margin: 0 0 .55rem;
}
.sb-hifz-summary {
  font-size: .82rem;
  line-height: 1.45;
  color: #343a40;
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: .25rem;
  padding: .35rem .5rem;
  min-height: 2rem;
  margin-top: .55rem;
}
.hifz-para-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: .5rem; }
.hifz-para-item {
  text-align: left;
  border: 1px solid #dee2e6;
  background: #fff;
  border-radius: .35rem;
  padding: .45rem .55rem;
  cursor: pointer;
  transition: border-color .15s, background .15s;
}
.hifz-para-item:hover { border-color: #17a2b8; background: #f4fcfd; }
.hifz-para-item.is-selected {
  border-color: #28a745;
  background: #f3fff6;
  box-shadow: 0 0 0 2px rgba(40, 167, 69, .2);
}
.hifz-para-item strong { display: block; font-size: .88rem; line-height: 1.35; }
.hifz-para-item small { display: block; line-height: 1.3; margin-top: .15rem; white-space: normal; }
.hifz-ar-text {
  font-family: 'Amiri Quran', 'Traditional Arabic', 'Scheherazade New', serif;
  direction: rtl;
  unicode-bidi: plaintext;
}
.hifz-ar-text small, .hifz-ar-text.hifz-ar-sub { color: #6c757d; font-size: .78rem; }
@media (max-width: 991px) {
  .sb-hifz-field-wide, .sb-hifz-field-span2 { grid-column: 1 / -1; }
}
</style>
<script type="text/javascript">
(function(){
  function loadBySection(id){
    // show the loader if present
    var $loader = $("#loader-1");
    if ($loader.length) $loader.removeClass("d-none");

    $.ajax({
      url: "<?= base_url('admin/studentsbulk/data') ?>",
      type: "POST",
      data: {
        cls_sec_id: (id === '-1' || id === -1) ? -1 : (parseInt(id, 10) || 0),
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
      },
      success: function(html){
        $("#studentsList").html(html);
        if(typeof window.initHifzBulkEnrollmentRows === "function"){
          window.initHifzBulkEnrollmentRows();
        }
      },
      error: function(){
        toastr && toastr.error ? toastr.error("Failed to load students.") : alert("Failed to load students.");
      },
      complete: function(){
        if ($loader.length) $loader.addClass("d-none");
      }
    });
  }

  // change handler for your filter
  $("#filter_cls_sec_id").on("change", function(){
    loadBySection(this.value);
  });

  // initial load with current selection (or all if empty)
  loadBySection($("#filter_cls_sec_id").val());

  <?php if (! empty($hifz_enabled)): ?>
  var HIFZ_PARA_TOTAL_LINES = <?= (int) hifzParaTotalLines() ?>;
  var HIFZ_PARA_HALF_LINES = <?= (int) hifzParaHalfLines() ?>;

  function hifzPoolsSummary(list){
    return list.length ? ("Paras " + list.join(", ")) : "None";
  }

  function hifzComputeEnrollmentPoolsJs(sequence, currentPara, linesDone){
    currentPara = Math.max(1, Math.min(30, parseInt(currentPara, 10) || 1));
    linesDone = Math.max(0, Math.min(HIFZ_PARA_TOTAL_LINES, parseInt(linesDone, 10) || 0));
    var reverse = (String(sequence || "").toLowerCase() === "para_reverse");
    var dualSabqi = linesDone < HIFZ_PARA_HALF_LINES;
    var sabqi = [], manzil = [], p;
    if(reverse){
      if(dualSabqi){
        if(currentPara < 30){ sabqi = [currentPara, currentPara + 1]; }
        else { sabqi = [currentPara]; }
      } else {
        sabqi = [currentPara];
      }
      var manzilStart = (sabqi.length ? Math.max.apply(null, sabqi) : currentPara) + 1;
      for(p = manzilStart; p <= 30; p++){ manzil.push(p); }
    } else {
      if(dualSabqi && currentPara > 1){ sabqi = [currentPara - 1, currentPara]; }
      else { sabqi = [currentPara]; }
      var manzilEnd = currentPara - sabqi.length;
      for(p = 1; p <= manzilEnd; p++){ manzil.push(p); }
    }
    return { sabqi: sabqi, manzil: manzil };
  }

  function recalcHifzPools(sid){
    if(!sid) return;
    var sequence = $("#hifz_sequence_"+sid).val() || "para_forward";
    var currentPara = $("#hifz_current_para_"+sid).val();
    var linesDone = $("#hifz_lines_done_"+sid).val();
    var pools = hifzComputeEnrollmentPoolsJs(sequence, currentPara, linesDone);
    $("#hifz_sabqi_list_"+sid).val(pools.sabqi.join(","));
    $("#hifz_manzil_pool_"+sid).val(pools.manzil.join(","));
    $("#hifz_sabqi_summary_"+sid).text(hifzPoolsSummary(pools.sabqi));
    $("#hifz_manzil_summary_"+sid).text(hifzPoolsSummary(pools.manzil));
  }

  $(document).on("click", ".sb-bulk-card-head, .sb-bulk-card-toggle", function(e){
    if (window.matchMedia("(min-width: 768px)").matches) return;
    e.preventDefault();
    var $tr = $(this).closest("tr.sb-bulk-student-row");
    if (!$tr.length) return;
    var expanded = $tr.toggleClass("sb-bulk-expanded").hasClass("sb-bulk-expanded");
    $tr.find(".sb-bulk-card-toggle").attr("aria-expanded", expanded ? "true" : "false");
  });

  window.initHifzBulkEnrollmentRows = function(){
    $(".sb-hifz-panel").each(function(){
      var sid = parseInt($(this).attr("id").replace("hifz_panel_",""), 10);
      if(sid) recalcHifzPools(sid);
    });
  };

  $(document).on("change input", ".js-hifz-sequence, .js-hifz-current-para, .js-hifz-lines-done", function(){
    var sid = parseInt($(this).closest(".sb-hifz-panel").attr("id").replace("hifz_panel_",""), 10);
    if(sid) recalcHifzPools(sid);
  });
  <?php endif; ?>
})();
</script>

<?= $this->endSection() ?>