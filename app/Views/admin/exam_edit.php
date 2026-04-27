<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  // ---------- Normalize ----------
  $isEdit  = isset($info);
  $header  = $isEdit ? 'Edit Exam' : 'Add Exam';
  $locked  = !empty($locked); // passed from controller (bool)

  // Session/campus from controller
  $campus_id  = (string) ($sessionData['campusid']  ?? '');
  $sess_id_in = (string) ($sessionData['sessionid'] ?? '');

  if ($isEdit) {
      $id              = (string) ($info->eid         ?? '');
      $exam_name       = (string) ($info->exam_name   ?? '');
      $short_name      = (string) ($info->short_name  ?? '');
      $term_id         = (string) ($info->term_id     ?? '');
      $session_id      = (string) ($info->session_id  ?? '');

      $exam_start_date = '';
      if (!empty($info->exam_start_date)) {
        $dt = DateTime::createFromFormat('Y-m-d', (string)$info->exam_start_date);
        $exam_start_date = $dt ? $dt->format('d/m/Y') : '';
      }
      $exam_end_date = '';
      if (!empty($info->exam_end_date)) {
        $dt = DateTime::createFromFormat('Y-m-d', (string)$info->exam_end_date);
        $exam_end_date = $dt ? $dt->format('d/m/Y') : '';
      }

      // term_session row is passed as $terms_session in controller->edit()
      $term_session_id = (string) ($terms_session->term_session_id ?? '');
  } else {
      // Add mode defaults
      $id              = '';
      $exam_name       = '';
      $short_name      = '';
      $term_id         = '';
      $session_id      = $sess_id_in;
      $exam_start_date = '';
      $exam_end_date   = '';
      $term_session_id = ''; // not used on add via this view; edit uses it
  }

  // CSRF (if enabled)
  $csrfName = function_exists('csrf_token') ? csrf_token() : '';
  $csrfHash = function_exists('csrf_hash')  ? csrf_hash()  : '';
?>

<!-- Header -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Exam</h1>
        <?php if ($locked): ?>
          <small class="text-muted">Locked (datesheet or results exist)</small>
        <?php endif; ?>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Exam</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
          <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/exam') ?>">Exams</a></li>
            <?php if (!$isEdit): ?>
              <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/exam/add') ?>"><?= esc($header) ?></a></li>
            <?php else: ?>
              <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/exam/edit?id=' . urlencode($id)) ?>"><?= esc($header) ?></a></li>
            <?php endif; ?>
          </ul>
        </div>

        <div class="card-body">
          <?php if ($locked): ?>
            <div class="alert alert-warning d-flex align-items-center" role="alert">
              <i class="fas fa-lock mr-2"></i>
              <div>
                This exam is <strong>locked</strong> because a datesheet and/or subject results exist.
                You can view details, but editing is disabled.
              </div>
            </div>
          <?php endif; ?>

          <div class="tab-content">
            <?php
              echo form_open(base_url('admin/exam/save_edit'), 'role="form" id="user-edit-form"');
              echo form_hidden('id', $id);                 // casted to string above
              echo form_hidden('campus_id', $campus_id);   // casted to string above
              if (function_exists('csrf_field')) { echo csrf_field(); }
            ?>

            <!-- Hidden term_session_id (edit view expects it) -->
            <input type="hidden" name="term_session_id" id="term_session_id" value="<?= esc($term_session_id) ?>">

            <!-- Disable all inputs when locked -->
            <fieldset <?= $locked ? 'disabled' : '' ?>>
              <div class="row">
                <div class="col-lg-4">
                  <div class="form-group">
                    <label for="exam_name">Exam Name</label>
                    <input type="text" name="exam_name" value="<?= esc($exam_name) ?>" placeholder="Exam Name" class="form-control">
                  </div>
                </div>

                <div class="col-lg-4">
                  <div class="form-group">
                    <label for="short_name">Short Name</label>
                    <input type="text" name="short_name" value="<?= esc($short_name) ?>" placeholder="Short Name" class="form-control">
                  </div>
                </div>

                <!-- Date range partial (renders start/end inputs + per-day toggles) -->
                <div class="col-lg-12" id="dateRange"></div>
              </div>
            </fieldset>

            <!-- Actions -->
            <div class="form-group mt-3">
              <?php if (!$locked): ?>
                <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
                <button type="reset" class="btn btn-default">Reset</button>
              <?php else: ?>
                <button type="button" class="btn btn-secondary" disabled><i class="fas fa-lock mr-1"></i> Locked</button>
              <?php endif; ?>
              <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
            </div>

            <?= form_close(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Scripts -->
<script>
(function($){
  'use strict';

  const IS_LOCKED   = <?= $locked ? 'true' : 'false' ?>;
  const TERM_SESS_ID= <?= json_encode($term_session_id ?? '') ?>;
  const EXAM_ID     = <?= json_encode($id) ?>;
  const CSRF_NAME   = <?= json_encode($csrfName) ?>;
  const CSRF_HASH   = <?= json_encode($csrfHash) ?>;

  // Load the date-range partial (server fills start/end and day toggles)
  function loadDateRange(){
    if(!TERM_SESS_ID){
      $('#dateRange').html('<div class="alert alert-info">No term session selected.</div>');
      return;
    }
    const payload = { term_session_id: TERM_SESS_ID, exam_id: EXAM_ID };
    if (CSRF_NAME && CSRF_HASH) payload[CSRF_NAME] = CSRF_HASH;

    $.ajax({
      url: '<?= base_url('admin/exam/getTermDateRange'); ?>',
      type: 'POST',
      data: payload
    })
    .done(function(res){
      $('#dateRange').html(res);
    })
    .fail(function(xhr){
      $('#dateRange').html('<div class="alert alert-danger">Failed to load date range.</div>');
      console.error('getTermDateRange', xhr.status, xhr.responseText);
    });
  }

  $(function(){

    // Load term date range on ready
    loadDateRange();

    // Client guard if locked
    if (IS_LOCKED) {
      $('#user-edit-form').on('submit', function(e){
        e.preventDefault();
        if (window.toastr) toastr.warning('This exam is locked for editing because a datesheet and/or results exist.');
        return false;
      });
      return; // no ajaxForm binding when locked
    }

    // jQuery Validate (basic)
    if ($.fn.validate) {
      $('#user-edit-form').validate({
        rules:{
          exam_name:{ required:true },
          exam_start_date:{ required:true }
        },
        messages:{
          exam_name:{ required:'Exam Name is required' },
          exam_start_date:{ required:'Exam start date is required' }
        }
      });
    }

    // ajaxForm submit
    if ($.fn.ajaxForm) {
      $('#user-edit-form').ajaxForm({
        beforeSubmit:function(){
          if ($.fn.validate && !$('#user-edit-form').valid()) return false;
          $('#submitBtn').html('Saving').prop('disabled', true);
        },
        success:function(responseText){
          $('#submitBtn').html('Save').prop('disabled', false);

          let json = responseText;
          if (typeof responseText !== 'object') {
            try { json = JSON.parse(responseText); } catch(e){ json = {success:false, msg:'Unexpected response'}; }
          }

          if (json && json.success) {
            if (window.toastr) toastr.success(json.msg || 'Saved');
            <?php if(!$isEdit): ?>
              location.href = '<?= base_url('admin/exam') ?>';
            <?php else: ?>
              location.href = <?= json_encode(base_url('admin/exam/edit?id=' . $id) . '&after=edit') ?>;
            <?php endif; ?>
          } else {
            if (window.toastr) toastr.error((json && json.msg) || 'Save failed');
          }
          return false;
        },
        error:function(xhr){
          $('#submitBtn').html('Save').prop('disabled', false);
          if (xhr.status === 423) {
            if (window.toastr) toastr.error('This exam is locked for editing because a datesheet and/or results exist.');
          } else {
            if (window.toastr) toastr.error('Request failed (' + xhr.status + ').');
          }
        }
      });
    }
  });
})(jQuery);
</script>

<?= $this->endSection() ?>
