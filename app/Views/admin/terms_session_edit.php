<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  // Prefill (edit vs add)
  if (isset($info)) {
    $header      = 'Edit Term Session';
    $id          = $info->term_session_id;
    $term_id     = $info->term_id;      // also used as term code
    $session_id  = $info->session_id;   // also used as session code
    $start_date  = $info->start_date;
    $end_date    = $info->end_date;
  } else {
    $header      = 'Add Term Session';
    $id = $term_id = $session_id = $start_date = $end_date = '';
  }
?>

<?= view('components/page_header', [
    'title' => 'Terms Session',
    'icon' => 'fas fa-calendar',
    'subtitle' => $header ?? null,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Terms Session', 'url' => base_url('admin/terms_session')],
        ['label' => isset($info) ? 'Edit' : 'Add', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-body">

        <!-- ✅ FORM wraps everything so session_id & term arrays are posted -->
        <?= form_open(base_url('admin/terms_session/save'), 'role="form" id="user-edit-form"') ?>
        <?= form_hidden('id', $id) ?>

        <!-- Controls (smart-sized) -->
        <div class="row">
          <div class="col-md-5 col-lg-4 mb-3">
            <label class="mb-1">Session</label>
            <select class="form-control form-control-sm" id="session_id" name="session_id">
              <option value="">Select Session</option>
              <?php if (!empty($academic_session)) : foreach ($academic_session as $session) :
                $sId    = $session->session_id;          // session code
                $sName  = $session->session_name ?? '';
                $sStart = $session->start_date ?? '';
                $sEnd   = $session->end_date ?? '';
              ?>
                <option
                  value="<?= esc($sId) ?>"
                  data-start="<?= esc($sStart) ?>"
                  data-end="<?= esc($sEnd) ?>"
                  data-code="<?= esc($sId) ?>"
                  <?= ($session_id == $sId ? 'selected' : '') ?>
                ><?= esc($sName) ?> </option>
              <?php endforeach; endif; ?>
            </select>
          </div>

          <!-- Session summary -->
          <div class="col-md-7 col-lg-8 mb-3">
            <div class="row">
              <div class="col-sm-6 mb-2">
                <div class="border rounded p-2 h-100">
                  <div class="small text-muted mb-1">Selected Session</div>
                  <div class="d-flex flex-wrap align-items-center">
                    <span id="sessionName" class="fw-semibold me-2">—</span>
                    <span id="sessionCode" class="badge text-bg-info">—</span>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 mb-2">
                <div class="border rounded p-2 h-100">
                  <div class="small text-muted mb-1">Session Dates & Weeks</div>
                  <div class="d-flex flex-wrap align-items-center">
                    <span class="me-3">
                      <span class="text-muted">Start:</span>
                      <span id="sessionStart" class="fw-semibold me-1">—</span>
                    </span>
                    <span class="me-3">
                      <span class="text-muted">End:</span>
                      <span id="sessionEnd" class="fw-semibold me-1">—</span>
                    </span>
                    <span>
                      <span class="text-muted">Weeks:</span>
                      <span id="sessionWeeks" class="fw-semibold">0</span>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div><!-- /row -->

        <!-- Loader + Terms area (inside the form so inputs are posted) -->
        <div class="position-relative">
          <div id="loader-1" class="overlay d-none">
            <i class="fas fa-2x fa-sync-alt fa-spin"></i>
          </div>
          <div id="termssessionarea"></div>
        </div>

        <!-- Actions (inside the form) -->
        <div class="mt-3 d-flex flex-wrap">
          <button type="submit" id="submitBtn" class="btn btn-primary btn-sm me-2">Save</button>
          <button type="reset" class="btn btn-secondary btn-sm me-2">Reset</button>
          <button type="button" class="btn btn-secondary btn-sm" onclick="history.back()">Cancel</button>
        </div>

        </form><!-- /user-edit-form -->

      </div>
    </div>
  </div>
</section>

<style>
  .overlay{position:absolute;inset:0;background:rgba(255,255,255,.6);display:flex;align-items:center;justify-content:center;z-index:10}
  
</style>

<script>
(function($){
  const isEdit = <?= json_encode(!empty($id)) ?>;

  // --- Date helpers ---
  function parseDateFlexible(v){
    if(!v) return null;
    // for session dates in YYYY-MM-DD
    if (v.includes('-') && v.split('-')[0].length === 4) {
      const [y,m,d] = v.split('-').map(n=>parseInt(n,10));
      if([y,m,d].every(n=>!isNaN(n))) return new Date(y,m-1,d);
    }
    return null;
  }
  function parseDMY(s){ // for term inputs in dd-mm-yy
    if(!s) return null;
    const a=s.split('-'); if(a.length!==3) return null;
    let d=parseInt(a[0],10), m=parseInt(a[1],10)-1, y=parseInt(a[2],10);
    if (y < 100) { y = y + (y >= 70 ? 1900 : 2000); } // handle two-digit year
    const dt=new Date(y,m,d);
    return isNaN(dt)?null:dt;
  }
  function weeksInclusive(s,e){
    if(!s||!e) return 0;
    const ms=86400000, diffDays=Math.floor((e-s)/ms)+1;
    return diffDays>0?Math.ceil(diffDays/7):0;
  }
  function setText(sel, val){ $(sel).text(val ?? '—'); }

  // --- Session summary & default pick ---
  function updateSessionSummaryFromOption($opt){
    const name = $opt.text().trim();
    const code = $opt.data('code') || '';
    const s = parseDateFlexible($opt.data('start'));
    const e = parseDateFlexible($opt.data('end'));
    setText('#sessionName', name || '—');
    setText('#sessionCode', code || 'N/A');
    setText('#sessionStart', s ? `${s.getFullYear()}-${String(s.getMonth()+1).padStart(2,'0')}-${String(s.getDate()).padStart(2,'0')}` : '—');
    setText('#sessionEnd',   e ? `${e.getFullYear()}-${String(e.getMonth()+1).padStart(2,'0')}-${String(e.getDate()).padStart(2,'0')}`   : '—');
    setText('#sessionWeeks', (s && e) ? weeksInclusive(s, e) : 0);
  }

  function autoSelectCurrentSession(){
    const $sel = $('#session_id');
    if (isEdit && $sel.val()) return; // keep edit preselection
    const today = new Date();
    let chosen = null;
    $sel.find('option[value!=""]').each(function(){
      const $o=$(this), s=parseDateFlexible($o.data('start')), e=parseDateFlexible($o.data('end'));
      if(s && e && s<=today && today<=e){ chosen=$o; return false; }
    });
    if(chosen){ $sel.val(chosen.val()); }
  }

  // Weeks-only enhancement (no day badges here; data2 handles that)
  function recomputeRowWeeks($row){
    let idx = $row.find('input.term-start[data-idx]').data('idx')
           || $row.find('input.term-end[data-idx]').data('idx');
    if(!idx){
      const id1=$row.find('input[id^="startdatepicker"]').attr('id');
      const id2=$row.find('input[id^="enddatepicker"]').attr('id');
      const m = (id1||id2||'').match(/\d+$/);
      idx = m ? parseInt(m[0],10) : null;
    }
    const sStr = $row.find('#startdatepicker'+idx).val();
    const eStr = $row.find('#enddatepicker'+idx).val();
    const s = parseDMY(sStr), e = parseDMY(eStr);
    const w = (s && e) ? weeksInclusive(s,e) : 0;
    $row.find('#weeks'+idx+', .tm-weeks').first().text(w);
  }

  // Any term input change => recompute weeks only
  $(document).on('change dp.change input',
    '#termssessionarea input.term-start, #termssessionarea input.term-end, #termssessionarea input.datepicker',
    function(){
      const $row=$(this).closest('[data-term-id], .term-row, tr');
      if($row.length){ recomputeRowWeeks($row); }
    }
  );

  // Load terms partial when session changes
  $('#session_id').on('change', function(){
    const $opt = $(this).find('option:selected');
    updateSessionSummaryFromOption($opt);

    const session_id = $(this).val();
    if (!session_id) { $('#termssessionarea').empty(); return; }

    $('#loader-1').removeClass('d-none');

    $.ajax({
      url: '<?= base_url('admin/terms_session/data2'); ?>',
      type: 'POST',
      data: { session_id: session_id },
      success: function(res){
        $('#termssessionarea').html(res);
        $('#loader-1').addClass('d-none');

        // Initial weeks compute per row
        $('#termssessionarea').find('[data-term-id]').each(function(){
          recomputeRowWeeks($(this));
        });
      },
      error: function(){
        $('#loader-1').addClass('d-none');
        if (typeof toastr !== 'undefined') toastr.error('Failed to load terms for selected session.');
      }
    });
  });

  // Init: choose current session by default, then load terms
  (function init(){
    autoSelectCurrentSession();
    const $sel = $('#session_id');
    if ($sel.val()) {
      updateSessionSummaryFromOption($sel.find('option:selected'));
      $sel.trigger('change');
    }
  })();

})(jQuery);
</script>

<?= $this->endSection() ?>
