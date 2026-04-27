<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  // From controller
  $academic_sessioninfo = $academic_sessioninfo ?? [];
  $sectionsclassinfo    = $sectionsclassinfo ?? [];

  // CSRF
  $csrfTokenName = csrf_token();
  $csrfHash      = csrf_hash();
?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-7">
        <h1 class="h4 mb-0">Class Promotion</h1>
        <div class="text-muted small">Click or drag students to promote; changes save immediately.</div>
      </div>
      <div class="col-sm-5">
        <ol class="breadcrumb float-sm-right mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Class Promotion</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="card card-primary card-outline">
    <div class="card-header py-2">
      <div class="d-flex flex-wrap align-items-end" style="gap:.75rem;">
        <!-- Running (Current) -->
        <div>
          <label class="small mb-1">Running Session</label>
          <select id="running_session" class="form-control form-control-sm" style="min-width:180px;">
            <option value="">Select session</option>
            <?php foreach ($academic_sessioninfo as $s): ?>
              <option value="<?= (int)$s->session_id ?>"><?= esc($s->session_name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Running Class (LEFT) — send cls_sec_id -->
        <div>
          <label class="small mb-1">Running Class</label>
          <select id="running_class" class="form-control form-control-sm" style="min-width:220px;">
            <option value="">Select class → section</option>
            <?php foreach ($sectionsclassinfo as $sec): ?>
              <option value="<?= (int)$sec['cls_sec_id'] ?>"><?= esc($sec['sectionclassname']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mx-2 d-none d-md-block" style="opacity:.4;">→</div>

        <!-- Target (Promote To) -->
        <div>
          <label class="small mb-1">New Session</label>
          <select id="new_session" class="form-control form-control-sm" style="min-width:180px;">
            <option value="">Select session</option>
            <?php foreach ($academic_sessioninfo as $s): ?>
              <option value="<?= (int)$s->session_id ?>"><?= esc($s->session_name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- New Class (RIGHT) — send cls_sec_id -->
        <div>
          <label class="small mb-1">New Class</label>
          <select id="new_class" class="form-control form-control-sm" style="min-width:220px;">
            <option value="">Select class → section</option>
            <?php foreach ($sectionsclassinfo as $sec): ?>
              <option value="<?= (int)$sec['cls_sec_id'] ?>"><?= esc($sec['sectionclassname']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <button id="btnLoad" type="button" class="btn btn-primary btn-sm">
          <span class="label">Load Students</span>
          <span class="spinner-border spinner-border-sm d-none ms-1" role="status" aria-hidden="true"></span>
        </button>
      </div>
    </div>

    <div class="card-body">
      <!-- Top tools -->
      <div class="d-flex flex-wrap align-items-center mb-2" style="gap:.5rem;">
        <div class="input-group input-group-sm" style="max-width:260px;">
          <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
          <input id="search_left" type="text" class="form-control" placeholder="Search current class…">
        </div>
        <div class="input-group input-group-sm" style="max-width:260px;">
          <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
          <input id="search_right" type="text" class="form-control" placeholder="Search new class…">
        </div>

        <div class="ml-auto d-flex" style="gap:.5rem;">
          <button id="btnMoveAll" class="btn btn-success btn-sm" disabled>
            <i class="fas fa-angle-double-right"></i> Move All
          </button>
          <button id="btnUndoAll" class="btn btn-warning btn-sm" disabled>
            <i class="fas fa-undo"></i> Undo All
          </button>
        </div>
      </div>

      <!-- Two-column canvas -->
      <div class="promotion-grid">
        <!-- Left: current -->
        <div class="promo-col">
          <div class="promo-head">
            <div class="title"><i class="far fa-list-alt"></i> Current Class</div>
            <div class="count"><span id="count_left">0</span> students</div>
          </div>
          <div id="leftList" class="promo-list" aria-label="Current class students"></div>
        </div>

        <!-- Right: new -->
        <div class="promo-col">
          <div class="promo-head">
            <div class="title"><i class="far fa-check-square"></i> New Class</div>
            <div class="count"><span id="count_right">0</span> students</div>
          </div>
          <div id="rightList" class="promo-list" aria-label="Promoted students"></div>
        </div>
      </div>

      <div id="loadState" class="text-center text-muted mt-3">
        <i class="fas fa-info-circle"></i> Choose sessions & classes, then click <b>Load Students</b>.
      </div>
    </div>
  </div>
</section>

<!-- Styles -->
<style>
  .promotion-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
  @media (max-width: 991.98px) { .promotion-grid { grid-template-columns:1fr; } }
  .promo-col { border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; background:#fff; display:flex; flex-direction:column; min-height:380px; }
  .promo-head { display:flex; justify-content:space-between; align-items:center; padding:10px 12px; background:#f8fafc; border-bottom:1px solid #e5e7eb; font-weight:600; }
  .promo-head .title { font-size:14px; }
  .promo-head .count { font-size:12px; color:#64748b; }
  .promo-list { position:relative; flex:1; overflow:auto; padding:10px; min-height:280px; }
  .student-pill { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:8px 10px; margin:6px 0; border:1px solid #e5e7eb; border-radius:10px; background:#fff; transition:transform .08s ease, box-shadow .08s ease; cursor:grab; }
  .student-pill:active { cursor:grabbing; transform:scale(.99); }
  .pill-main { display:flex; align-items:center; gap:8px; min-width:0; }
  .avatar { width:26px; height:26px; border-radius:999px; background:#f1f5f9; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; }
  .pill-name { font-size:13px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .pill-sub { font-size:11px; color:#64748b; }
  .pill-actions { display:flex; align-items:center; gap:6px; }
  .pill-btn { border:1px solid #e2e8f0; background:#f8fafc; border-radius:999px; padding:2px 8px; font-size:11px; }
  .pill-btn:hover { background:#eef2f7; }
  .ghost { opacity:.35; background:#e7f3ff; }
</style>

<!-- Scripts (load once; avoid nested/duplicate tags) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>

<script>
(function(){
  const routes = {
    fetch:    "<?= base_url('admin/student-class/fetch-students') ?>",
    move:     "<?= base_url('admin/student-class/move') ?>",
    moveBulk: "<?= base_url('admin/student-class/move-bulk') ?>"
  };

  let csrfName = "<?= $csrfTokenName ?>";
  let csrfHash = "<?= $csrfHash ?>";

  const $left  = $('#leftList');
  const $right = $('#rightList');
  const $load  = $('#loadState');
  const $btnLoad = $('#btnLoad');
  const $btnLoadLabel = $('#btnLoad .label');
  const $btnLoadSpin  = $('#btnLoad .spinner-border');
  const $btnMoveAll = $('#btnMoveAll');
  const $btnUndoAll = $('#btnUndoAll');

  // Helpers
  function selVals(){
    return {
      running_session: $('#running_session').val(),
      running_class:   $('#running_class').val(),  // cls_sec_id
      new_session:     $('#new_session').val(),
      new_class:       $('#new_class').val()       // cls_sec_id
    };
  }
  function canLoad(v){ return v.running_session && v.running_class && v.new_session && v.new_class; }

  function setLoading(on){
    $btnLoad.prop('disabled', on);
    $btnLoadSpin.toggleClass('d-none', !on);
    $btnLoadLabel.text(on ? 'Loading…' : 'Load Students');
    $btnLoad.attr('aria-busy', on ? 'true' : 'false');
  }

  function countUpdate(){
    $('#count_left').text($left.children('.student-pill:visible').length);
    $('#count_right').text($right.children('.student-pill:visible').length);
    $btnMoveAll.prop('disabled', $left.children('.student-pill').length === 0);
    $btnUndoAll.prop('disabled', $right.children('.student-pill').length === 0);
  }

  function pillHtml(s){
    const initials = (s.name || '').split(' ').map(x => x.trim()[0] || '').join('').substring(0,2).toUpperCase() || 'S';
    return `
      <div class="student-pill" data-id="${s.id}">
        <div class="pill-main">
          <span class="avatar" title="${s.reg_no || ''}">${initials}</span>
          <div>
            <div class="pill-name" title="${s.name || ''}">${s.name || ''}</div>
            ${s.reg_no ? `<div class="pill-sub">${s.reg_no}</div>` : ``}
          </div>
        </div>
        <div class="pill-actions">
          <button type="button" class="pill-btn btn-move"></button>
        </div>
      </div>
    `;
  }

  function decorateButtons(){
    $left.find('.btn-move').text('Promote →');
    $right.find('.btn-move').text('← Undo');
  }

  // Load students
  function loadStudents(){
    const v = selVals();
    if(!canLoad(v)){
      $load.html('<div class="text-danger">Select both sessions & classes first.</div>');
      return;
    }
    setLoading(true);

    $.ajax({
      url: routes.fetch,
      method: 'POST',
      dataType: 'json',
      data: {
        [csrfName]: csrfHash,
        running_session: v.running_session,
        running_class:   v.running_class,  // cls_sec_id
        new_session:     v.new_session,
        new_class:       v.new_class       // cls_sec_id
      }
    })
    .done(function (data) {
      if (!data || data.success !== true) {
        $load.html('<div class="text-danger">Failed to load students.</div>');
        return;
      }
      // Optional: update CSRF if backend returns it
      if (data.csrfName && data.csrfHash) { csrfName = data.csrfName; csrfHash = data.csrfHash; }

      $left.empty(); $right.empty();
      (data.students || []).forEach(s => $left.append(pillHtml(s)));
      (data.promoted || []).forEach(s => $right.append(pillHtml(s)));
      decorateButtons();
      $load.empty();
      countUpdate();
    })
    .fail(function () {
      $load.html('<div class="text-danger">Server error while loading students.</div>');
    })
    .always(function(){
      setLoading(false);
    });
  }

  // Search filters (client-side)
  function bindSearch($input, $list){
    $input.on('input', function(){
      const q = $(this).val().toLowerCase();
      $list.children('.student-pill').each(function(){
        const t = ($(this).text() || '').toLowerCase();
        $(this).toggle(t.indexOf(q) !== -1);
      });
      countUpdate();
    });
  }
  bindSearch($('#search_left'), $left);
  bindSearch($('#search_right'), $right);

  // Persist a single move
  function moveOne(toRight, $pill){
    const v = selVals();
    const payload = {
      [csrfName]: csrfHash,
      student_id: $pill.data('id'),
      from_session_id: toRight ? v.running_session : v.new_session,
      to_session_id:   toRight ? v.new_session     : v.running_session,
      from_cls_sec_id: toRight ? v.running_class   : v.new_class,
      to_cls_sec_id:   toRight ? v.new_class       : v.running_class
    };
    return $.ajax({ url: routes.move, method: 'POST', dataType: 'json', data: payload });
  }

  // Persist many moves
  function moveMany(toRight, $pills){
    const v = selVals();
    const ids = $pills.map(function(){ return $(this).data('id'); }).get();
    if(ids.length === 0) return $.Deferred().resolve({success:true}).promise();

    const payload = {
      [csrfName]: csrfHash,
      student_ids: ids,
      from_session_id: toRight ? v.running_session : v.new_session,
      to_session_id:   toRight ? v.new_session     : v.running_session,
      from_cls_sec_id: toRight ? v.running_class   : v.new_class,
      to_cls_sec_id:   toRight ? v.new_class       : v.running_class
    };
    return $.ajax({ url: routes.moveBulk, method: 'POST', dataType: 'json', data: payload });
  }

  // Click move
  $(document).on('click', '.promo-list .btn-move', function(){
    const $btn  = $(this);
    const $pill = $btn.closest('.student-pill');
    const inLeft = $pill.parent().is($left);
    const toRight = inLeft;

    $btn.prop('disabled', true).text('Saving…');

    moveOne(toRight, $pill).done(function(r){
      const ok = r && r.success === true;
      if(ok){
        (toRight ? $right : $left).prepend($pill);
        decorateButtons();
        countUpdate();
      } else {
        alert((r && r.message) ? r.message : 'Move failed.');
      }
    }).fail(function(){
      alert('Server error.');
    }).always(function(){
      $btn.prop('disabled', false);
      decorateButtons();
    });
  });

  // Drag & drop (SortableJS)
  function makeSortable(el){
    return new Sortable(el, {
      group: { name: 'promo', pull: true, put: true },
      animation: 120,
      ghostClass: 'ghost',
      onAdd: function(evt){
        const $pill = $(evt.item);
        const toRight = $(evt.to).is($right);
        moveOne(toRight, $pill).done(function(r){
          const ok = r && r.success === true;
          if(!ok){
            (toRight ? $left : $right).prepend($pill);
            alert((r && r.message) ? r.message : 'Move failed.');
          } else {
            decorateButtons();
            countUpdate();
          }
        }).fail(function(){
          (toRight ? $left : $right).prepend($pill);
          alert('Server error.');
        });
      }
    });
  }
  makeSortable(document.getElementById('leftList'));
  makeSortable(document.getElementById('rightList'));

  // Bulk buttons
  $btnMoveAll.on('click', function(){
    const $pills = $left.children('.student-pill');
    if(!$pills.length) return;
    const $me = $(this).prop('disabled', true).text('Saving…');
    moveMany(true, $pills).done(function(r){
      if(r && r.success === true){
        $right.prepend($pills);
        decorateButtons(); countUpdate();
      } else { alert((r && r.message) ? r.message : 'Bulk move failed.'); }
    }).fail(function(){ alert('Server error.'); })
      .always(function(){ $me.prop('disabled', false).html('<i class="fas fa-angle-double-right"></i> Move All'); });
  });

  $btnUndoAll.on('click', function(){
    const $pills = $right.children('.student-pill');
    if(!$pills.length) return;
    const $me = $(this).prop('disabled', true).text('Saving…');
    moveMany(false, $pills).done(function(r){
      if(r && r.success === true){
        $left.prepend($pills);
        decorateButtons(); countUpdate();
      } else { alert((r && r.message) ? r.message : 'Bulk undo failed.'); }
    }).fail(function(){ alert('Server error.'); })
      .always(function(){ $me.prop('disabled', false).html('<i class="fas fa-undo"></i> Undo All'); });
  });

  // Load
  $btnLoad.on('click', loadStudents);

  // Auto disable/enable load and clear lists when select changes
  $('select#running_session, select#running_class, select#new_session, select#new_class').on('change', function(){
    const v = selVals();
    $btnLoad.prop('disabled', !canLoad(v));
    $left.empty(); $right.empty(); countUpdate();
    $('#search_left, #search_right').val('');
    $load.html('<i class="fas fa-info-circle"></i> Click <b>Load Students</b> to continue.');
  });

  // Initial state
  $btnLoad.prop('disabled', true);
})();
</script>

<?= $this->endSection() ?>
