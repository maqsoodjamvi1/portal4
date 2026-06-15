<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  $isEdit = isset($info) && !empty($info);
  $header = $isEdit ? 'Edit Scheme of Studies' : 'Add Scheme of Studies';
  $id     = $isEdit ? (string)($info->id ?? '') : '';

  // From controller ->add()
  $pre     = $preselect ?? ['session_id'=>null,'term_session_id'=>null,'subject_id'=>null];
  $preSess = (string)($pre['session_id'] ?? '');
  $preTerm = (string)($pre['term_session_id'] ?? '');
  $preSub  = (string)($pre['subject_id'] ?? '');
?>

<?= view('components/page_header', [
    'title' => 'Scheme of Studies',
    'icon' => 'fas fa-book-open',
    'subtitle' => $header,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Scheme of Studies', 'url' => base_url('admin/top_level_planning')],
        ['label' => $header, 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid">

    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header p-2">
        <ul class="nav nav-pills" role="tablist">
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning') ?>">Scheme of Studies (List)</a></li>
          <?php if ($id === ''): ?>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning/add') ?>"><?= esc($header) ?></a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="<?= site_url('admin/top_level_planning/edit?id=' . urlencode($id)) ?>"><?= esc($header) ?></a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning_sections/add') ?>">Add Scheme (Class-wise)</a></li>
          <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/top_level_planning_subject/add') ?>">Add Scheme (Subject-wise)</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning_gradewise') ?>">Grade-wise Views</a></li>
        </ul>
      </div>

      <div class="card-body">
        <?php
          echo form_open(base_url('admin/top_level_planning_subject/save'), 'role="form" id="user-edit-form"');
          echo form_hidden('id', (string)$id);
          if (function_exists('csrf_field')) { echo csrf_field(); }
        ?>

        <!-- Filters -->
        <div class="row g-3">
          <div class="col-md-4">
            <label for="session_id" class="mb-1">Session</label>
            <select name="session_id" id="session_id" class="form-control select2" data-placeholder="Select session">
              <option value=""></option>
              <?php foreach (($academic_session ?? []) as $session): ?>
                <option
                  value="<?= esc($session->session_id) ?>"
                  data-start="<?= esc($session->start_date) ?>"
                  data-end="<?= esc($session->end_date) ?>"
                  <?= ((string)$session->session_id === $preSess ? 'selected' : '') ?>
                >
                  <?= esc($session->session_name) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Choose academic session.</small>
          </div>

          <div class="col-md-4">
            <label for="term_session_id" class="mb-1">Term</label>
            <select name="term_session_id" id="term_session_id" class="form-control select2" data-placeholder="Select term">
              <option value=""></option>
              <?php foreach (($termSessionInfo ?? []) as $t): ?>
                <option
                  value="<?= esc($t['term_session_id']) ?>"
                  <?= ((string)$t['term_session_id'] === $preTerm ? 'selected' : '') ?>
                >
                  <?= esc($t['term_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Pick the term for this plan.</small>
          </div>

          <div class="col-md-4">
            <label for="subject_id" class="mb-1">Subject</label>
            <select name="subject_id" id="subject_id" class="form-control select2" data-placeholder="Select subject" <?= ($preSess && $preTerm) ? '' : 'disabled' ?>>
              <option value=""></option>
              <?php foreach (($subjectInfo ?? []) as $s): ?>
                <option value="<?= esc($s->sid) ?>" <?= ((string)$s->sid === $preSub ? 'selected' : '') ?>>
                  <?= esc($s->subject_name) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Select subject to load class-wise editors.</small>
          </div>
        </div>

        <!-- Toolbar -->
        <div class="d-flex align-items-center justify-content-between flex-wrap mt-3 mb-2">
          <div class="d-flex align-items-center gap-2">
            <div class="form-check form-switch me-3">
              <input type="checkbox" class="form-check-input" id="synch" name="synch" value="1">
              <label class="form-check-label" for="synch">Sync to all campuses</label>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="expandAll"><i class="fas fa-expand me-1"></i>Expand all</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="collapseAll"><i class="fas fa-compress me-1"></i>Collapse all</button>
          </div>
          <div class="small text-muted">
            <i class="far fa-info-circle me-1"></i>Edits auto-save after you stop typing.
          </div>
        </div>

        <!-- Loader overlay -->
        <div class="position-relative">
          <div id="loader-1"
               class="d-none position-absolute w-100 h-100"
               style="top:0;left:0;background:rgba(255,255,255,.7);z-index:10;display:flex;align-items:center;justify-content:center;">
            <div class="text-center">
              <i class="fas fa-sync-alt fa-spin fa-2x mb-2"></i>
              <div class="small text-muted">Loading classes…</div>
            </div>
          </div>

          <!-- CARD GRID (no tables) -->
          <div id="subjects_grid" class="card-grid"></div>
        </div>

        <?= form_close(); ?>
      </div>
    </div>
  </div>
</section>

<!-- Select2 -->
<link rel="stylesheet" href="<?= base_url('resource/select2/css/select2.min.css') ?>">
<script src="<?= base_url('resource/select2/js/select2.full.min.js') ?>"></script>

<style>
  /* Responsive card grid */
  .card-grid {
    display: grid;
    grid-gap: 12px;
    grid-template-columns: repeat(1, minmax(0,1fr));
  }
  @media (min-width: 576px) { .card-grid { grid-template-columns: repeat(2, minmax(0,1fr)); } }
  @media (min-width: 992px) { .card-grid { grid-template-columns: repeat(3, minmax(0,1fr)); } }
  @media (min-width: 1400px){ .card-grid { grid-template-columns: repeat(4, minmax(0,1fr)); } }

  .tlp-card {
    border: 1px solid #e8ecf3;
    border-radius: .5rem;
    background: #fff;
    display: flex;
    flex-direction: column;
    min-height: 180px;
  }
  .tlp-card .tlp-hd {
    padding: .6rem .75rem;
    border-bottom: 1px solid #eef2f8;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .5rem;
  }
  .tlp-card .tlp-hd .title {
    font-weight: 600; font-size: .95rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
  }
  .tlp-card .tlp-bd {
    padding: .6rem .75rem;
  }
  .tlp-card .tlp-ft {
    padding: .35rem .75rem .55rem;
    display:flex;align-items:center;justify-content:space-between;
    border-top: 1px solid #f1f4fa;
  }
  .save-hint { font-size: .8rem; color: #6c757d; }
  .editor, .js-autosave { width: 100%; min-height: 140px; }
  .is-saving { opacity: .7; }
  .badge-soft {
    background:#eef3ff; color:#3751dd; border:1px solid #dbe4ff;
    padding:2px 6px; border-radius: 999px; font-size: .7rem; font-weight: 600;
  }
</style>

<script>
(function($){
  'use strict';

  const CSRF_FIELD = '<?= esc(csrf_token()) ?>';
  const CSRF_HASH  = '<?= esc(csrf_hash()) ?>';

  const SELECT_SUBJECTS_URL = '<?= site_url('admin/top_level_planning_subject/select-subjects') ?>';
  const AUTOSAVE_URL        = '<?= site_url('admin/top_level_planning_subject/autosave') ?>';

  const PRESELECT = {
    session_id:      <?= json_encode($preSess) ?>,
    term_session_id: <?= json_encode($preTerm) ?>,
    subject_id:      <?= json_encode($preSub)  ?>
  };

  let booting = true;
  const saveTimers = new Map();

  function showLoader(show){ $('#loader-1').toggleClass('d-none', !show); }

  function initSelect2(){
    $('.select2').select2({
      width: '100%',
      allowClear: true,
      placeholder: function(){ return $(this).data('placeholder') || 'Select an option'; }
    });
  }

  function enableSubjectIfReady() {
    const sid  = $('#session_id').val();
    const tsid = $('#term_session_id').val();
    const enable = (sid && tsid);
    $('#subject_id').prop('disabled', !enable).trigger('change.select2');
    if (!enable) $('#subjects_grid').empty();
  }

 function buildCardForEditor($ta, idx) {
  // Be flexible about attribute names
  const classId =
    $ta.data('class-id') ??
    $ta.closest('[data-class-id]').data('class-id') ??
    '';

  // Try several keys for class / section names
  const className =
    $ta.data('class-name') ??
    $ta.data('classname') ??
    $ta.data('class') ??
    '';

  const sectionName =
    $ta.data('section-name') ??
    $ta.data('sectionname') ??
    $ta.data('section') ??
    $ta.data('section-classname') ?? // in case you pass "A" here
    '';

  // Build the display title: "Play Group - A (ID: 123)"
  let displayTitle = '';
  if (className && sectionName) {
    displayTitle = `${escapeHtml(className)} - ${escapeHtml(sectionName)} <span class="text-muted">(ID: ${escapeHtml(String(classId || '—'))})</span>`;
  } else if (className) {
    displayTitle = `${escapeHtml(className)} <span class="text-muted">(ID: ${escapeHtml(String(classId || '—'))})</span>`;
  } else {
    displayTitle = `Class ID <span class="text-muted">${escapeHtml(String(classId || '—'))}</span>`;
  }

  const tlpId = $ta.data('tlp-id') || '';

  // Wrap textarea with a card
  const $card = $(`
    <div class="tlp-card" data-class-id="${classId || ''}">
      <div class="tlp-hd">
        <div class="title">
          <i class="fas fa-chalkboard-teacher me-1"></i>
          ${displayTitle}
        </div>
        <span class="badge-soft">#${idx}</span>
      </div>
      <div class="tlp-bd"></div>
      <div class="tlp-ft">
        <div class="save-hint js-save-hint">Idle</div>
        <div>
          <button type="button" class="btn btn-sm btn-outline-secondary js-collapse">Collapse</button>
        </div>
      </div>
    </div>
  `);

  // Move the existing editor (textarea / summernote) into the card
  const $holder = $card.find('.tlp-bd');
  $ta.attr('data-tlp-id', tlpId); // ensure attr exists
  $ta.appendTo($holder);

  // collapse/expand
  $card.on('click', '.js-collapse', function(){
    const $bd = $card.find('.tlp-bd');
    const collapsed = $bd.is(':hidden');
    $bd.slideToggle(120);
    $(this).text(collapsed ? 'Collapse' : 'Expand');
  });

  return $card;
}
  // Extract editors from returned HTML and render as cards
  function renderCardsFromHtml(html) {
    const $tmp = $('<div>').html(html);

    // Find all editors provided by server
    let $editors = $tmp.find('textarea.js-autosave');
    if ($editors.length === 0) {
      $('#subjects_grid').html('<div class="alert alert-info mb-0">No classes found for this subject/term.</div>');
      return;
    }

    const $grid = $('#subjects_grid').empty();

    // If Summernote markup is around, remove old wrappers; we will (re)init after mount
    $tmp.find('.note-editor, .note-editor.note-frame').remove();

    $editors.each(function(i){
      const $ta = $(this);

      // Ensure required dataset exists; fallback safe
      if (!$ta.attr('data-class-id')) {
        // try to sniff from nearest row/cell if server didn’t put it
        const guess = $ta.closest('[data-class-id]').data('class-id');
        if (guess) $ta.attr('data-class-id', guess);
      }

      const $card = buildCardForEditor($ta, i+1);
      $grid.append($card);
    });

    // Initialize Summernote if available
    if ($.fn.summernote) {
      $grid.find('textarea.js-autosave').each(function(){
        const $el = $(this);
        if (!$el.next('.note-editor').length) {
          $el.summernote(); // your default options
        }
      });
    }

    // Bind autosave
    rebindAutosaveHandlers($grid);
  }

  function rebindAutosaveHandlers($scope) {
    const $root = $scope || $(document);
    $root.find('textarea.js-autosave').each(function(){
      const $el = $(this);
      $el.off('.autosave');

      $el.on('input.autosave paste.autosave change.autosave', function(){
        debounceSave(this, 600);
      });

      if ($.fn.summernote && $el.next('.note-editor').length) {
        $el.off('summernote.change.autosave');
        $el.on('summernote.change.autosave', function(){
          debounceSave(this, 600);
        });
      }
    });
  }

  function debounceSave(el, wait) {
    const prev = saveTimers.get(el);
    if (prev) clearTimeout(prev);
    const t = setTimeout(() => performSave(el), wait);
    saveTimers.set(el, t);
  }

  function performSave(el) {
    const $el = $(el);
    const $card     = $el.closest('.tlp-card');
    const $saveHint = $card.find('.js-save-hint');

    const payload = {
      session_id:      $('#session_id').val(),
      term_session_id: $('#term_session_id').val(),
      subject_id:      $('#subject_id').val(),
      class_id:        $el.data('class-id'),
      tlp_id:          $el.data('tlp-id') || '',
      synch:           $('#synch').is(':checked') ? 1 : 0
    };

    if (!payload.term_session_id || !payload.subject_id || !payload.class_id) return;

    if ($.fn.summernote && $el.next('.note-editor').length) {
      payload.objective = $el.summernote('code');
    } else {
      payload.objective = $el.val();
    }

    payload[CSRF_FIELD] = CSRF_HASH;

    $card.addClass('is-saving');
    $saveHint.text('Saving…');

    $.post(AUTOSAVE_URL, payload).done(function(resp){
      let json = resp;
      if (typeof resp !== 'object') {
        try { json = JSON.parse(resp); } catch(e){ json = {success:false}; }
      }
      if (json && json.success) {
        if (json.tlp_id) $el.attr('data-tlp-id', json.tlp_id);
        $saveHint.text('Saved ✓');
      } else {
        $saveHint.text('Could not save');
        toastr.error((json && json.message) || 'Could not save.');
      }
    }).fail(function(){
      $saveHint.text('Request failed');
      toastr.error('Request failed.');
    }).always(function(){
      $card.removeClass('is-saving');
      setTimeout(() => $saveHint.text('Idle'), 850);
    });
  }

  function escapeHtml(s){
    const d = document.createElement('div');
    d.innerText = s ?? '';
    return d.innerHTML;
  }

  function loadCards(){
    const subject_id      = $('#subject_id').val() || '';
    const session_id      = $('#session_id').val() || '';
    const term_session_id = $('#term_session_id').val() || '';

    if(!subject_id || !session_id || !term_session_id){
      $('#subjects_grid').empty();
      return;
    }

    showLoader(true);

    $.ajax({
      url: SELECT_SUBJECTS_URL,
      type: 'POST',
      data: { subject_id, session_id, term_session_id, [CSRF_FIELD]: CSRF_HASH }
    })
    .done(function(res){
      renderCardsFromHtml(res); // transform server HTML → cards
    })
    .fail(function(xhr){
      toastr.error('Failed to load classes.');
      $('#subjects_grid').html('<div class="alert alert-danger mb-0">Failed to load. Try again.</div>');
      console.error('select-subjects', xhr.status, xhr.responseText);
    })
    .always(function(){
      showLoader(false);
    });
  }

  $(document).ready(function(){
    initSelect2();

    $('#session_id').on('change', function(){
      enableSubjectIfReady();
    });

    $('#term_session_id').on('change', enableSubjectIfReady);

    $('#subject_id').on('change', loadCards);

    // Expand/Collapse all
    $('#expandAll').on('click', function(){
      $('#subjects_grid .tlp-bd').slideDown(120);
      $('#subjects_grid .tlp-card .js-collapse').text('Collapse');
    });
    $('#collapseAll').on('click', function(){
      $('#subjects_grid .tlp-bd').slideUp(120);
      $('#subjects_grid .tlp-card .js-collapse').text('Expand');
    });

    // Preselects
    if (PRESELECT.session_id)      $('#session_id').val(PRESELECT.session_id).trigger('change.select2').trigger('change');
    if (PRESELECT.term_session_id) $('#term_session_id').val(PRESELECT.term_session_id).trigger('change.select2').trigger('change');
    if (PRESELECT.subject_id)      $('#subject_id').val(PRESELECT.subject_id).trigger('change.select2').trigger('change');
    else                           enableSubjectIfReady();

    // If everything preselected, load immediately
    if ($('#session_id').val() && $('#term_session_id').val() && $('#subject_id').val()) {
      loadCards();
    }

    // prevent full form submit; autosave only
    $('#user-edit-form').off('submit');
  });
})(jQuery);
</script>

<?= $this->endSection() ?>
