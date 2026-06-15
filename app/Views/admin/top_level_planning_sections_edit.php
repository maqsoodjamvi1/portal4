<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  $isEdit = isset($info) && !empty($info);
  $header = $isEdit ? 'Edit Scheme of Studies (Class-wise)' : 'Add Scheme of Studies (Class-wise)';
  $id     = $isEdit ? (string)($info->id ?? '') : '';
?>

<?= view('components/page_header', [
    'title' => 'Scheme of Studies',
    'icon' => 'fas fa-sitemap',
    'subtitle' => $header ?? null,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Top Level Planning', 'url' => base_url('admin/top_level_planning')],
        ['label' => 'Scheme of Studies', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid">

    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header p-2">
        <ul class="nav nav-pills" role="tablist">
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning') ?>">Scheme of Studies (List)</a></li>

          <?php if ($id === ''): ?>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning/add') ?>">Add Scheme of Studies</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="<?= site_url('admin/top_level_planning/edit?id=' . urlencode($id)) ?>">Edit Scheme of Studies</a></li>
          <?php endif; ?>

          <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/top_level_planning_sections/add') ?>">Add Scheme (Class-wise)</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning_subject/add') ?>">Add Scheme (Subject-wise)</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning_gradewise') ?>">Grade-wise Views</a></li>
        </ul>
      </div>

      <div class="card-body">
        <?php
          echo form_open(base_url('admin/top_level_planning_sections/save'), 'role="form" id="user-edit-form"');
          echo form_hidden('id', (string)$id);
          if (function_exists('csrf_field')) { echo csrf_field(); }
        ?>

        <div class="row">
          <!-- Session -->
          <div class="col-lg-4 col-md-6">
            <div class="form-group">
              <label for="session_id" class="mb-1">Session</label>
              <select name="session_id" id="session_id" class="form-control select2" data-placeholder="Select session">
                <option value=""></option>
                <?php foreach (($academic_session ?? []) as $session): ?>
                 <option value="<?= esc($session->session_id) ?>"
  <?= ((string)$session->session_id === (string)($preselect['session_id'] ?? '')) ? 'selected' : '' ?>>
  <?= esc($session->session_name) ?>
</option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Choose the academic session.</small>
            </div>
          </div>

          <!-- Term Session -->
          <div class="col-lg-4 col-md-6">
            <div class="form-group">
              <label for="term_session_id" class="mb-1">Term</label>
              <select name="term_session_id" id="term_session_id" class="form-control select2" data-placeholder="Select term">
                <option value=""></option>
                <?php foreach (($termSessionInfo ?? []) as $t): ?>
                 <option value="<?= esc($t['term_session_id']) ?>"
  <?= ((string)$t['term_session_id'] === (string)($preselect['term_session_id'] ?? '')) ? 'selected' : '' ?>>
  <?= esc($t['term_name']) ?>
</option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Pick the term session.</small>
            </div>
          </div>

          <!-- Section -->
          <div class="col-lg-3 col-md-6">
            <div class="form-group">
              <label for="section_id" class="mb-1">Section</label>
              <select class="form-control select2" name="section_id" id="section_id" data-placeholder="Select section">
                <option value=""></option>
                <?php foreach (($sectionsclassinfo ?? []) as $sec): ?>
                  <option value="<?= esc($sec['section_id']) ?>"><?= esc($sec['sectionclassname']) ?></option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Select a section to load editors.</small>
            </div>
          </div>

          <!-- Sync -->
          <div class="col-lg-1 col-md-6">
            <div class="form-group">
              <label class="mb-1 d-block">Sync</label>
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="synch" name="synch" value="1">
                <label class="form-check-label" for="synch">All campuses</label>
              </div>
            </div>
          </div>
        </div>

        <!-- Loader + dynamic list -->
        <div class="position-relative">
          <div id="loader-1"
               class="d-none position-absolute w-100 h-100"
               style="top:0;left:0;background:rgba(255,255,255,.7);z-index:10;display:flex;align-items:center;justify-content:center;">
            <div class="text-center">
              <i class="fas fa-sync-alt fa-spin fa-2x mb-2"></i>
              <div class="small text-muted">Loading classes…</div>
            </div>
          </div>
          <div id="subjects_list" class="table-responsive"></div>
        </div>

        <div class="mt-3 d-flex gap-2">
          <button type="submit" id="submitBtn" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Save
          </button>
          <button type="reset" class="btn btn-outline-secondary">Reset</button>
          <a href="javascript:history.back();" class="btn btn-outline-dark">Cancel</a>
        </div>

        <?= form_close(); ?>
      </div>
    </div>

  </div>
</section>

<!-- Select2 (use working paths) -->
<link rel="stylesheet" href="<?= base_url('plugins/select2/css/select2.min.css') ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<script src="<?= base_url('plugins/select2/js/select2.full.min.js') ?>"></script>

<script>
(function($){
  'use strict';

  const CSRF_FIELD = '<?= esc(csrf_token()) ?>';
  const CSRF_HASH  = '<?= esc(csrf_hash()) ?>';

  // Endpoints
  const SELECT_EXAM_URL = '<?= site_url('admin/ajax/select-exam') ?>';
  // Your new controller method
  const SELECT_TERM_TLP_URL = '<?= site_url('admin/top_level_planning_sections/selectTermforTopLevelPlanning') ?>';

  function showLoader(show){ $('#loader-1').toggleClass('d-none', !show); }

  function initSelect2(){
    if ($.fn.select2) {
      $('.select2').select2({ width:'100%', theme: 'bootstrap-5', allowClear:true });
    }
  }

  // Remove "Video URL" field rows + iframes/thumbnails from returned markup
  function stripVideoArtifacts($container){
    $container.find('tr, .form-group, .row').filter(function(){
      return $(this).text().toLowerCase().includes('video url');
    }).remove();
    $container.find('iframe, .video-thumb, .video-embed, .video, [data-video], .thumb, .ratio').remove();

    // Clean up empty cells/rows
    $container.find('td, th').filter(function(){ return $(this).is(':empty'); }).remove();
    $container.find('tr').filter(function(){ return $(this).children().length === 0; }).remove();
  }

  function polishReturnedTable($container){
    const $table = $container.find('table').first();
    if ($table.length){
      $table.addClass('table table-sm table-striped table-bordered align-middle');
      $table.find('th').addClass('bg-light');
      $table.find('textarea.editor, .note-editor').css({width:'100%'});
    }
  }

  function readyToLoad(){
    const sid  = $('#session_id').val();
    const tsid = $('#term_session_id').val();
    const sec  = $('#section_id').val();
    return !!(sid && tsid && sec);
  }

  $(function(){
    initSelect2();

    // Session change -> optional exam list & clear content
    $('#session_id').on('change', function(){
      $('#subjects_list').empty();
      const session_id = $(this).val() || '';
      if (!session_id) return;

      $.post(SELECT_EXAM_URL, { session_id: session_id, [CSRF_FIELD]: CSRF_HASH })
       .done(function(res){ $('#eid').html(res); /* if #eid exists */ });
    });

    // Term change clears content
    $('#term_session_id').on('change', function(){
      $('#subjects_list').empty();
    });

    // When Section changes (and session + term are selected) -> load editors
    $('#section_id').on('change', function(){
      if (!readyToLoad()){
        toastr.warning('Please select Session and Term first.');
        return;
      }

      const payload = {
        section_id:      $('#section_id').val(),
        session_id:      $('#session_id').val(),
        term_session_id: $('#term_session_id').val(),
        [CSRF_FIELD]:    CSRF_HASH
      };

      showLoader(true);
      $.post(SELECT_TERM_TLP_URL, payload)
        .done(function(res){
          $('#subjects_list').html(res);

          // Nuke video URL fields and thumbnails/iframes
          stripVideoArtifacts($('#subjects_list'));

          // Nicer table
          polishReturnedTable($('#subjects_list'));

          // Initialize Summernote editors (avoid double-init)
          if ($.fn.summernote){
            $('.editor').each(function(){
              if (!$(this).next('.note-editor').length){
                $(this).summernote();
              }
            });
          }
        })
        .fail(function(){
          toastr.error('Failed to load class-wise editors.');
        })
        .always(function(){ showLoader(false); });
    });

    // AJAX submit: push Summernote HTML back to textareas
    $('#user-edit-form').ajaxForm({
      beforeSubmit: function(){
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving');
        if ($.fn.summernote) {
          $('.editor').each(function(){
            if ($(this).next('.note-editor').length){
              $(this).val($(this).summernote('code'));
            }
          });
        }
      },
      success: function(resp){
        $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
        let json = resp;
        if (typeof resp !== 'object'){
          try { json = JSON.parse(resp); } catch(e){ json = {success:false, msg:'Unexpected response.'}; }
        }
        if (json.success){
          toastr.success(json.msg || 'Saved successfully.');
        } else {
          toastr.error(json.msg || 'Unable to save.');
        }
      },
      error: function(){
        $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
        toastr.error('Request failed.');
      }
    });

  });
})(jQuery);
</script>

<?= $this->endSection() ?>
