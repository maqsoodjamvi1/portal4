<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  // -------- Normalize (strings for CI form helpers) --------
  $isEdit   = isset($info);
  $header   = $isEdit ? 'Edit Exam' : 'Add Exam';

  $session_campus_id = (string)($sessionData['campusid']  ?? '');
  $session_id_in     = (string)($sessionData['sessionid'] ?? '');

  if ($isEdit) {
      $id              = (string)($info->eid         ?? '');
      $exam_name       = (string)($info->exam_name   ?? '');
      $short_name      = (string)($info->short_name  ?? '');
      $term_id         = (string)($info->term_id     ?? '');
      $session_id      = (string)($info->session_id  ?? '');
      $exam_start_date = '';
      if (!empty($info->exam_start_date)) {
        $dt = DateTime::createFromFormat('Y-m-d',(string)$info->exam_start_date);
        $exam_start_date = $dt ? $dt->format('d/m/Y') : '';
      }
      $exam_end_date = '';
      if (!empty($info->exam_end_date)) {
        $dt = DateTime::createFromFormat('Y-m-d',(string)$info->exam_end_date);
        $exam_end_date = $dt ? $dt->format('d/m/Y') : '';
      }
  } else {
      $id              = '';
      $exam_name       = '';
      $short_name      = '';
      $term_id         = ''; // not used on Add
      $session_id      = $session_id_in;
      $exam_start_date = '';
      $exam_end_date   = '';
  }

  // CSRF
  $csrfName = function_exists('csrf_token') ? csrf_token() : '';
  $csrfHash = function_exists('csrf_hash')  ? csrf_hash()  : '';
?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Exam</h1>
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

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
          <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/exam') ?>">Exams</a></li>
            <?php if ($id === ''): ?>
              <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/exam/add') ?>">Add Exam</a></li>
            <?php else: ?>
              <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/exam/edit?id=' . urlencode($id)) ?>">Edit Exam</a></li>
            <?php endif; ?>
          </ul>
        </div>

        <div class="card-body">
          <div class="tab-content">
            <?php
              echo form_open(base_url('admin/exam/save'), 'role="form" id="user-edit-form"');
              echo form_hidden('id', (string)$id);
              echo form_hidden('campus_id', (string)$session_campus_id);
              if (function_exists('csrf_field')) { echo csrf_field(); }
            ?>

            <div id="exam_list">
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
                      <?php if (!empty($termsinfo)): ?>
                        <?php foreach ($termsinfo as $t): 
                          // Support array or object
                          $tsid = is_array($t) ? (string)($t['term_session_id'] ?? '') : (string)($t->term_session_id ?? '');
                          $tname= is_array($t) ? (string)($t['name'] ?? '')             : (string)($t->name ?? '');
                        ?>
                          <option value="<?= esc($tsid) ?>"><?= esc($tname) ?></option>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </select>
                  </div>
                </div>

                <!-- Date range partial (loads when term changes) -->
                <div class="col-lg-12" id="dateRange"></div>
              </div>
            </div>

            <div class="form-group mt-3">
              <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
              <button type="reset" class="btn btn-default">Reset</button>
              <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
            </div>

            <?= form_close(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
$(function(){
  // Load date range when term is chosen
  $('#term_session_id').on('change', function(){
    const term_session_id = $(this).val();
    if (!term_session_id) {
      $('#dateRange').html('');
      return;
    }

    const payload = { term_session_id: term_session_id };
    <?php if ($csrfName && $csrfHash): ?>
      payload['<?= esc($csrfName) ?>'] = '<?= esc($csrfHash) ?>';
    <?php endif; ?>

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
  });

  // Basic validation
  if ($.fn.validate) {
    $('#user-edit-form').validate({
      rules:{
        exam_name:{ required:true },
        exam_start_date:{ required:true } // this input comes from the partial
      },
      messages:{
        exam_name:{ required:'Exam Name is required' },
        exam_start_date:{ required:'Exam start date is required' }
      }
    });
  }

  // Ajax submit
  if ($.fn.ajaxForm) {
    $('#user-edit-form').ajaxForm({
      beforeSubmit:function(){
        if ($.fn.validate && !$('#user-edit-form').valid()) return false;
        $('#submitBtn').text('Saving').prop('disabled', true);
      },
      success:function(responseText){
        $('#submitBtn').text('Save').prop('disabled', false);
        let json = responseText;
        if (typeof responseText !== 'object') {
          try { json = JSON.parse(responseText); } catch(e){ json = {success:false, msg:'Unexpected response'}; }
        }
        if (json && json.success) {
          if (window.toastr) toastr.success(json.msg || 'Saved');
          location.href = '<?= base_url('admin/exam') ?>';
        } else {
          if (window.toastr) toastr.error((json && json.msg) || 'Save failed');
        }
        return false;
      },
      error:function(xhr){
        $('#submitBtn').text('Save').prop('disabled', false);
        if (window.toastr) toastr.error('Request failed (' + xhr.status + ').');
      }
    });
  }
});
</script>

<?= $this->endSection() ?>
