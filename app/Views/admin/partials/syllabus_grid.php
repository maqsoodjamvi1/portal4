<?php
$eid        = (int)($exam->eid ?? 0);
$cls_sec_id = (int)$cls_sec_id;
$dsMap      = $dsMap ?? []; // ['sec_sub_id' => ['exam_date'=>..., 'total_marks'=>...]]

// Normalize stored syllabus → textarea
if (!function_exists('normalize_syllabus_for_textarea')) {
  function normalize_syllabus_for_textarea($str) {
    if ($str === null || $str === '') return '';
    $s = (string) $str;
    $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = preg_replace('~<br\s*/?>~i', "\n", $s);
    $s = preg_replace('~</(p|div|li|tr|h[1-6])>~i', "\n", $s);
    $s = preg_replace('~<li[^>]*>~i', "• ", $s);
    $s = strip_tags($s);
    $s = str_replace(["\r\n", "\r"], "\n", $s);
    $s = preg_replace("/\n{3,}/", "\n\n", $s);
    $s = preg_replace("/[ \t\x{00A0}]+/u", " ", $s);
    return trim($s);
  }
}
?>
<style>
  /* Same visual language as admin/top_level_planning/add.php (.planning-card grid) */
  /* One card per row, full width (matches full-width planning rows) */
  #syllabusTbl.syllabus-planning-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 20px;
    align-items: stretch;
    width: 100%;
  }

  #syllabusTbl .planning-card {
    background: #fff;
    border: 1px solid #e0e7ef;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
    min-width: 0;
  }
  #syllabusTbl .planning-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }
  #syllabusTbl .planning-card-header {
    background: #2c3e66;
    color: white;
    padding: 15px 20px;
    border-bottom: 1px solid #e0e7ef;
  }
  #syllabusTbl .planning-card-header h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    line-height: 1.3;
  }
  #syllabusTbl .planning-card-header h5 i {
    font-size: 14px;
    opacity: 0.95;
  }
  #syllabusTbl .planning-card-header .term-date {
    display: block;
    font-size: 12px;
    opacity: 0.88;
    margin-top: 6px;
    font-weight: normal;
  }
  #syllabusTbl .planning-card-body {
    padding: 20px;
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    min-height: 0;
  }
  #syllabusTbl .planning-card-body .form-group {
    margin-bottom: 0;
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    min-height: 0;
  }
  #syllabusTbl .planning-card-body label.syll-field-label {
    font-weight: 600;
    color: #1e4663;
    margin-bottom: 10px;
    display: block;
    font-size: 14px;
  }
  /* Same editor height as Top Level Planning Summernote (height: 200) — keeps every card equal */
  #syllabusTbl .planning-card-body textarea.form-control.syll-input {
    width: 100%;
    border: 1px solid #cfdfed;
    border-radius: 8px;
    padding: 10px;
    font-size: 14px;
    transition: border-color 0.2s, box-shadow 0.2s;
    height: 200px !important;
    min-height: 200px !important;
    max-height: 200px !important;
    resize: none;
    overflow-y: auto;
    overflow-x: hidden;
    line-height: 1.45;
    box-sizing: border-box;
    flex-shrink: 0;
  }
  #syllabusTbl .planning-card-body textarea.form-control.syll-input:focus {
    border-color: #2c7da0;
    outline: none;
    box-shadow: 0 0 0 2px rgba(44,125,160,0.12);
  }

  .syllabus-toolbar-hint.alert-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    margin-bottom: 1rem;
  }
  .syllabus-toolbar-hint.alert-info i { margin-right: 10px; }

  @media (max-width: 768px) {
    #syllabusTbl .planning-card-header { padding: 12px 15px; }
    #syllabusTbl .planning-card-header h5 { font-size: 14px; }
    #syllabusTbl .planning-card-body { padding: 15px; }
    #syllabusTbl .planning-card-body label.syll-field-label { font-size: 13px; }
  }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
  <div class="alert alert-info syllabus-toolbar-hint mb-0 py-2 px-3 flex-grow-1 me-md-2">
    <i class="fas fa-info-circle"></i>
    Type syllabus — it auto-saves on blur. Use “Load TLP” to prefill. “Save All” saves everything.
  </div>
  <div class="d-flex align-items-center mt-2 mt-md-0 flex-shrink-0">
    <button type="button" id="loadTlpAll" class="btn btn-outline-primary btn-sm me-2">
      <i class="fas fa-cloud-download-alt me-1"></i> Load TLP
    </button>
    <button type="button" id="saveAllBtn" class="btn btn-primary btn-sm">
      <i class="fas fa-save me-1"></i> Save All
    </button>
  </div>
</div>

<div id="syllabusTbl"
     data-eid="<?= $eid ?>" data-cls-sec-id="<?= $cls_sec_id ?>"
     class="syllabus-planning-grid">
  <?php if (!empty($subjects)): foreach ($subjects as $sub):
    $sec_sub_id = (int)$sub->sec_sub_id;
    $rawSyl     = $existingMap[$sec_sub_id] ?? '';
    $syllabus   = normalize_syllabus_for_textarea($rawSyl);
    $loaded     = ($syllabus !== '');

    $fullName   = (string) ($sub->subject_name ?? '');
    $shortName  = trim((string) ($sub->subject_short_name ?? ''));
    $labelName  = $shortName !== '' ? $shortName : $fullName;

    $ds          = $dsMap[$sec_sub_id] ?? null;
    $monthDay    = '';
    $marksChip   = '';
    if ($ds && !empty($ds['exam_date'])) {
      $ts = strtotime((string)$ds['exam_date']);
      if ($ts) {
        $monthDay = date('M j', $ts);
      }
      $m = (int)($ds['total_marks'] ?? 0);
      if ($m > 0) {
        $marksChip = (string)$m;
      }
    }

    $metaLine = '';
    if ($monthDay !== '' || $marksChip !== '') {
      $parts = [];
      if ($monthDay !== '') {
        $parts[] = $monthDay;
      }
      if ($marksChip !== '') {
        $parts[] = 'M ' . $marksChip;
      }
      $metaLine = implode(' · ', $parts);
    }
  ?>
  <div class="planning-card syllabus-subject-card"
       data-sec-sub-id="<?= $sec_sub_id ?>"
       data-subject-id="<?= (int)($sub->subject_id ?? 0) ?>">
    <div class="planning-card-header">
      <div class="d-flex justify-content-between align-items-start">
        <div class="flex-grow-1 min-w-0 pe-2">
          <h5 title="<?= esc($fullName !== '' ? $fullName : $labelName) ?>">
            <i class="fas fa-book"></i>
            <span><?= esc($labelName) ?></span>
          </h5>
          <?php if ($metaLine !== ''): ?>
            <span class="term-date"><?= esc($metaLine) ?></span>
          <?php else: ?>
            <span class="term-date"><em>No exam date / marks on datesheet</em></span>
          <?php endif; ?>
        </div>
        <button type="button"
                class="btn btn-sm btn-outline-light tlp-one flex-shrink-0"
                title="Load TLP for this subject">
          <i class="fas fa-download"></i>
        </button>
      </div>
    </div>
    <div class="planning-card-body">
      <div class="form-group">
        <label class="syll-field-label">Exam syllabus</label>
        <textarea class="form-control syll-input" rows="8"
                  placeholder="Enter syllabus…"
                  data-loaded="<?= $loaded ? '1':'0' ?>"><?= esc($syllabus) ?></textarea>
      </div>
    </div>
  </div>
  <?php endforeach; else: ?>
  <div class="text-center text-muted py-4" style="grid-column: 1 / -1;">No subjects in this section.</div>
  <?php endif; ?>
</div>

<script>
(function(){
  const $tbl = $('#syllabusTbl');
  const eid  = Number($tbl.data('eid') || 0);
  const cls  = Number($tbl.data('cls-sec-id') || 0);

  /** Match card outer heights when headers wrap differently (fixed textarea already aligns bodies). */
  function equalizeCardHeights() {
    const $cards = $tbl.find('.syllabus-subject-card');
    if (!$cards.length) return;
    $cards.css('min-height', '');
    let maxH = 0;
    $cards.each(function () {
      maxH = Math.max(maxH, $(this).outerHeight());
    });
    if (maxH > 0) {
      $cards.css('min-height', maxH + 'px');
    }
  }

  let eqTimer = null;
  function scheduleEqualize() {
    if (eqTimer) clearTimeout(eqTimer);
    eqTimer = setTimeout(function () {
      eqTimer = null;
      equalizeCardHeights();
    }, 50);
  }

  toastr.options = { positionClass:'toast-bottom-right', newestOnTop:false, preventDuplicates:true,
    closeButton:false, progressBar:true, timeOut:1200, extendedTimeOut:600 };
  let lastToast=null; function toastOnce(t,m){ if(lastToast) toastr.clear(lastToast); lastToast=toastr[t](m); }
  function addCsrf(d){ const n='<?= csrf_token() ?>', h='<?= csrf_hash() ?>'; if(n && h) d[n]=h; return d; }

  requestAnimationFrame(function () {
    scheduleEqualize();
  });
  $(window).off('resize.syllabusCardsEq').on('resize.syllabusCardsEq', scheduleEqualize);

  let blurTimer=null;
  $tbl.on('blur','.syll-input',function(){
    const $ta=$(this); clearTimeout(blurTimer); blurTimer=setTimeout(()=>saveOne($ta),60);
  });

  function saveOne($ta){
    const $row=$ta.closest('.syllabus-subject-card');
    const sec_sub_id=Number($row.attr('data-sec-sub-id')||0);
    const syllabus=$ta.val();
    if(!eid || !cls || !sec_sub_id) return;

    $ta.prop('disabled',true);
    $.post("<?= base_url('admin/datesheet/saveSyllabus') ?>",
      addCsrf({ eid, cls_sec_id:cls, sec_sub_id, syllabus })
    ).done(function(res){
      if(res && res.success){ toastOnce('success','Saved.'); markLoaded($ta,true); }
      else{ toastOnce('error',(res && res.message)||'Save failed.'); }
    }).fail(function(){ toastOnce('error','Server error.'); })
      .always(function(){ $ta.prop('disabled',false); });
  }

  function markLoaded($ta,loaded){
    $ta.attr('data-loaded',loaded?'1':'0');
  }

  $('#saveAllBtn').on('click',function(){
    const rows=[];
    $tbl.find('.syllabus-subject-card').each(function(){
      const sec_sub_id=Number($(this).attr('data-sec-sub-id')||0);
      const syllabus=$(this).find('.syll-input').val();
      if(sec_sub_id) rows.push({sec_sub_id, syllabus});
    });
    if(!rows.length || !eid || !cls) return;

    const $btn=$(this).prop('disabled',true).html('<span class="spinner-border spinner-border-sm"></span> Saving');
    $.post("<?= base_url('admin/datesheet/saveSyllabusBulk') ?>",
      addCsrf({ eid, cls_sec_id:cls, rows })
    ).done(function(res){
      if(res && res.success){ toastOnce('success','All entries saved.'); $tbl.find('.syll-input').each(function(){ markLoaded($(this),true); }); }
      else{ toastOnce('error',(res && res.message)||'Save failed.'); }
    }).fail(()=>toastOnce('error','Server error.'))
      .always(()=> {
        $btn.prop('disabled',false).html('<i class="fas fa-save me-1"></i> Save All');
        scheduleEqualize();
      });
  });

  $tbl.on('click','.tlp-one',function(e){
    e.preventDefault();
    loadTlpForRow($(this).closest('.syllabus-subject-card'), { replace:false });
  });

  $('#loadTlpAll').on('click',function(){
    $tbl.find('.syllabus-subject-card').each(function(){ loadTlpForRow($(this), { replace:false, quiet:true }); });
  });

  function loadTlpForRow($row, opts){
    const replace=!!(opts && opts.replace), quiet=!!(opts && opts.quiet);
    const subject_id=Number($row.attr('data-subject-id')||0);
    if(!cls || !subject_id) return;

    const $ta=$row.find('.syll-input');
    if(!replace && $ta.val().trim()!==''){ if(!quiet) toastOnce('info','Already has content.'); return; }

    const $btn=$row.find('.tlp-one').prop('disabled',true);
    $.post("<?= base_url('admin/datesheet/loadTlp') ?>",
      addCsrf({ cls_sec_id:cls, subject_id })
    ).done(function(res){
      if(res && res.success){
        const text=(res.syllabus||'').trim();
        if (text) {
          $ta.val(text);
          markLoaded($ta, false);
          scheduleEqualize();
          if (!quiet) toastOnce('success', (res && res.message) || 'Syllabus updated in datesheet.');
        } else if(!quiet){
          toastOnce('warning','No TLP found for this subject.');
        }
      } else if(!quiet){
        toastOnce('error',(res && res.message)||'Failed to load TLP.');
      }
    }).fail(function(){ if(!quiet) toastOnce('error','Server error while loading TLP.'); })
      .always(function(){ $btn.prop('disabled',false); });
  }
})();
</script>
