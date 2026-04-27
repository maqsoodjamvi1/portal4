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
  /* Let columns size to content, keep Subject minimal */
  #syllabusTbl { table-layout: auto !important; width: 100%; }
  #syllabusTbl.table-sm td, #syllabusTbl.table-sm th { padding: .38rem .42rem; font-size: 12.5px; }

  /* Column widths (Subject tries to stay as small as possible) */
  #syllabusTbl col.col-idx      { width: 44px; }
  #syllabusTbl col.col-subject  { width: 1%; }    /* 1% trick → shrink to content */
  #syllabusTbl col.col-syllabus { width: auto; }

  /* Subject cell layout */
  .subject-cell { min-width: 160px; } /* safety floor on very narrow screens */
  .subject-top {
    display:flex; align-items:center; gap:.35rem; min-width: 0; /* allow ellipsis */
  }
  .subject-name {
    font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    max-width: 100%;
  }
  .row-actions { margin-left:auto; display:flex; gap:.25rem; }
  .btn-tiny {
    border:1px solid #e1e5ee; background:#fff; font-size:11px; padding:.15rem .35rem; line-height:1.1;
    border-radius:6px; cursor:pointer;
  }
  .btn-tiny:hover { background:#f7f9ff; }

  /* Second line — date/day + marks chips (compact) */
  .subject-meta { margin-top:.2rem; display:flex; flex-wrap:wrap; gap:.25rem; }
  .chip {
    display:inline-flex; align-items:center; border:1px solid #e7eaf3; border-radius:999px;
    padding:1px 6px; background:#f7f9ff; font-weight:600; font-size:10.5px; color:#364fc7;
    line-height:1.3;
  }
  .chip.marks { color:#0b7285; }

  .syll-cell textarea { width: 100%; min-height: 56px; resize: vertical; }
  .badge-lite { background:#eef3ff; color:#3857d8; border:1px solid #dbe4ff; border-radius:999px; padding:2px 6px; font-size:11px; font-weight:600; }

  /* Responsive tweaks */
  @media (max-width: 768px) {
    .subject-cell { min-width: 140px; }
  }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
  <div class="small text-muted">
    <i class="far fa-info-circle mr-1"></i>
    Type syllabus and it auto-saves on blur. Use “Load TLP” to prefill. “Save All” commits everything.
  </div>
  <div class="d-flex align-items-center">
    <button id="loadTlpAll" class="btn btn-outline-secondary btn-sm mr-2">
      <i class="fas fa-cloud-download-alt mr-1"></i> Load TLP
    </button>
    <button id="saveAllBtn" class="btn btn-primary btn-sm">
      <i class="fas fa-save mr-1"></i> Save All
    </button>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-sm table-bordered mb-0" id="syllabusTbl"
         data-eid="<?= $eid ?>" data-cls-sec-id="<?= $cls_sec_id ?>">
    <colgroup>
      <col class="col-idx">
      <col class="col-subject">
      <col class="col-syllabus">
    </colgroup>
    <thead class="thead-light">
      <tr>
        <th>#</th>
        <th>Subject · Date/Day · Marks</th>
        <th>Syllabus</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($subjects)): $i = 1; foreach ($subjects as $sub):
        $sec_sub_id = (int)$sub->sec_sub_id;
        $rawSyl     = $existingMap[$sec_sub_id] ?? '';
        $syllabus   = normalize_syllabus_for_textarea($rawSyl);
        $loaded     = ($syllabus !== '');

        $ds        = $dsMap[$sec_sub_id] ?? null; // ['exam_date','total_marks']
        $dateChip  = '';
        $marksChip = '';
        if ($ds && !empty($ds['exam_date'])) {
          $ts = strtotime((string)$ds['exam_date']);
          if ($ts) {
            $dateChip  = date('D', $ts) . ' ' . date('j M', $ts);
          }
          $m = (int)($ds['total_marks'] ?? 0);
          if ($m > 0) $marksChip = (string)$m;
        }
      ?>
      <tr data-sec-sub-id="<?= $sec_sub_id ?>" data-subject-id="<?= (int)($sub->subject_id ?? 0) ?>">
        <td class="text-center align-middle"><?= $i++ ?></td>

        <td class="align-middle subject-cell" title="<?= esc($sub->subject_name) ?>">
          <!-- Row 1: Subject (ellipsis) + actions -->
          <div class="subject-top">
            <span class="subject-name"><?= esc($sub->subject_name) ?></span>
            <span class="row-actions">
              <button class="btn-tiny tlp-one" title="Load TLP for this subject"><i class="fas fa-download"></i></button>
            </span>
          </div>
          <!-- Row 2: Date/Day + Marks (if any) -->
          <?php if ($dateChip || $marksChip): ?>
            <div class="subject-meta">
              <?php if ($dateChip):  ?><span class="chip"><?= esc($dateChip) ?></span><?php endif; ?>
              <?php if ($marksChip): ?><span class="chip marks">M <?= esc($marksChip) ?></span><?php endif; ?>
            </div>
          <?php endif; ?>
        </td>

        <td class="syll-cell">
          <textarea class="form-control form-control-sm syll-input"
                    placeholder="Enter syllabus…"
                    data-loaded="<?= $loaded ? '1':'0' ?>"><?= esc($syllabus) ?></textarea>
          <?php if ($loaded): ?>
            <div class="mt-1"><span class="badge-lite">loaded</span></div>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; else: ?>
      <tr><td colspan="3" class="text-center text-muted">No subjects in this section.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script>
(function(){
  const $tbl = $('#syllabusTbl');
  const eid  = Number($tbl.data('eid') || 0);
  const cls  = Number($tbl.data('cls-sec-id') || 0);

  toastr.options = { positionClass:'toast-bottom-right', newestOnTop:false, preventDuplicates:true,
    closeButton:false, progressBar:true, timeOut:1200, extendedTimeOut:600 };
  let lastToast=null; function toastOnce(t,m){ if(lastToast) toastr.clear(lastToast); lastToast=toastr[t](m); }
  function addCsrf(d){ const n='<?= csrf_token() ?>', h='<?= csrf_hash() ?>'; if(n && h) d[n]=h; return d; }

  // Autosave on blur
  let blurTimer=null;
  $tbl.on('blur','.syll-input',function(){
    const $ta=$(this); clearTimeout(blurTimer); blurTimer=setTimeout(()=>saveOne($ta),60);
  });

  function saveOne($ta){
    const $row=$ta.closest('tr');
    const sec_sub_id=Number($row.data('sec-sub-id')||0);
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
    const $badge=$ta.next('.mt-1');
    if(loaded){ if($badge.length===0) $ta.after('<div class="mt-1"><span class="badge-lite">loaded</span></div>'); }
    else{ $badge.remove(); }
  }

  // Save all
  $('#saveAllBtn').on('click',function(){
    const rows=[];
    $tbl.find('tbody tr').each(function(){
      const sec_sub_id=Number($(this).data('sec-sub-id')||0);
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
      .always(()=> $btn.prop('disabled',false).html('<i class="fas fa-save mr-1"></i> Save All'));
  });

  // Load TLP per row
  $tbl.on('click','.tlp-one',function(e){
    e.preventDefault();
    loadTlpForRow($(this).closest('tr'), { replace:false });
  });

  // Load TLP all (fills only empty)
  $('#loadTlpAll').on('click',function(){
    $tbl.find('tbody tr').each(function(){ loadTlpForRow($(this), { replace:false, quiet:true }); });
  });

  function loadTlpForRow($row, opts){
    const replace=!!(opts && opts.replace), quiet=!!(opts && opts.quiet);
    const subject_id=Number($row.data('subject-id')||0);
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
