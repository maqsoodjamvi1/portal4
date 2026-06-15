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

<?= view('components/page_header', [
    'title' => 'Edit Exam',
    'icon' => 'fas fa-file-alt',
    'subtitle' => !empty($locked) ? 'Locked (datesheet or results exist)' : null,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Exams', 'url' => base_url('admin/exam')],
        ['label' => 'Edit', 'active' => true],
    ],
]) ?>

<!-- Content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card sms-card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
          <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/exam') ?>"><i class="fas fa-list me-1"></i> Exams</a></li>
            <?php if (!$isEdit): ?>
              <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/exam/add') ?>"><i class="fas fa-plus me-1"></i> <?= esc($header) ?></a></li>
            <?php else: ?>
              <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/exam/edit?id=' . urlencode($id)) ?>"><i class="fas fa-edit me-1"></i> <?= esc($header) ?></a></li>
            <?php endif; ?>
          </ul>
        </div>

        <div class="card-body">
          <?php if ($locked): ?>
            <div class="alert alert-warning d-flex align-items-center" role="alert">
              <i class="fas fa-lock me-2"></i>
              <div>
                This exam is <strong>locked</strong> because a datesheet and/or subject results exist.
                Exam name, short name and term can still be updated. Date range and exam days are locked.
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

              <div class="col-lg-4">
                <div class="form-group">
                  <label for="term_session_id">Term</label>
                  <select name="term_session_id" id="term_session_id" class="form-control">
                    <option value="">Select Term</option>
                    <?php foreach (($termsinfo ?? []) as $t):
                      $tsid = is_array($t) ? (string)($t['term_session_id'] ?? '') : (string)($t->term_session_id ?? '');
                      $tname = is_array($t) ? (string)($t['name'] ?? '') : (string)($t->name ?? '');
                    ?>
                      <option value="<?= esc($tsid) ?>" <?= ($term_session_id === $tsid ? 'selected' : '') ?>><?= esc($tname) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

            <!-- Date range partial (renders start/end inputs + per-day toggles) -->
            <fieldset <?= $locked ? 'disabled' : '' ?>>
              <div class="row">
                <div class="col-lg-12" id="dateRange"></div>
              </div>
            </fieldset>

            <!-- Actions -->
            <div class="form-group mt-3">
              <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
              <button type="reset" class="btn btn-secondary">Reset</button>
              <button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
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

    // jQuery Validate (basic)
    if ($.fn.validate) {
      $('#user-edit-form').validate({
        rules:{
          exam_name:{ required:true },
          <?php if (!$locked): ?>
          exam_start_date:{ required:true },
          <?php endif; ?>
          term_session_id:{ required:true }
        },
        messages:{
          exam_name:{ required:'Exam Name is required' },
          <?php if (!$locked): ?>
          exam_start_date:{ required:'Exam start date is required' },
          <?php endif; ?>
          term_session_id:{ required:'Term is required' }
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
          if (window.toastr) toastr.error('Request failed (' + xhr.status + ').');
        }
      });
    }
  });
})(jQuery);
</script>

<?= $this->endSection() ?>
