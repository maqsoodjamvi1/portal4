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

<?= view('components/page_header', [
    'title' => $header,
    'icon' => 'fas fa-file-alt',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Exams', 'url' => base_url('admin/exam')],
        ['label' => $isEdit ? 'Edit' : 'Add', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card sms-card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
          <ul class="nav nav-tabs">
            <?php if ($id === ''): ?>
              <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/exam/add') ?>"><i class="fas fa-plus me-1"></i> Add Exam</a></li>
            <?php else: ?>
              <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/exam/edit?id=' . urlencode($id)) ?>"><i class="fas fa-edit me-1"></i> Edit Exam</a></li>
            <?php endif; ?>
          </ul>
        </div>

        <div class="card-body">
          <div class="tab-content">
            <?php
              $currentExams = $current_session_exams ?? [];
              $latestExam = $latest_exam ?? null;
              $hasUnannouncedLatest = !empty($has_unannounced_latest);
            ?>

            <?php if (session()->getFlashdata('flash_msg')): ?>
              <div class="alert alert-success"><?= esc((string) session()->getFlashdata('flash_msg')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('flash_err')): ?>
              <div class="alert alert-danger"><?= esc((string) session()->getFlashdata('flash_err')) ?></div>
            <?php endif; ?>

            <div class="card mb-3">
              <div class="card-header">
                <strong>Current Session Exams</strong>
              </div>
              <div class="card-body p-0">
                <?php if (!empty($currentExams)): ?>
                  <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                      <thead>
                        <tr>
                          <th>Exam ID</th>
                          <th>Exam</th>
                          <th>Term</th>
                          <th>Start</th>
                          <th>End</th>
                          <th>Status</th>
                          <th>Datesheet</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($currentExams as $ex): ?>
                          <tr>
                            <td><?= esc((string)($ex['eid'] ?? '')) ?></td>
                            <td><?= esc((string)($ex['exam_name'] ?? '')) ?></td>
                            <td><?= esc((string)($ex['term_name'] ?? '')) ?></td>
                            <td><?= !empty($ex['exam_start_date']) ? esc(date('d-m-Y', strtotime((string)$ex['exam_start_date']))) : '-' ?></td>
                            <td><?= !empty($ex['exam_end_date']) ? esc(date('d-m-Y', strtotime((string)$ex['exam_end_date']))) : '-' ?></td>
                            <td>
                              <?php if ((string)($ex['status'] ?? '') === '1'): ?>
                                <span class="badge text-bg-success">Announced</span>
                              <?php else: ?>
                                <span class="badge text-bg-warning">Unannounced</span>
                              <?php endif; ?>
                            </td>
                            <td>
                              <?php $datesheetCount = (int)($ex['datesheet_count'] ?? 0); ?>
                              <?php if ($datesheetCount > 0): ?>
                                <span class="badge text-bg-info"><?= $datesheetCount ?> row(s)</span>
                              <?php else: ?>
                                <span class="badge text-bg-light">No datesheet</span>
                              <?php endif; ?>
                            </td>
                            <td>
                              <?php if ((string)($ex['status'] ?? '') === '0'): ?>
                                <a href="<?= base_url('admin/exam/edit?id=' . urlencode((string)($ex['eid'] ?? ''))) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="post" action="<?= base_url('admin/exam/announce') ?>" class="d-inline announce-form">
                                  <?= csrf_field() ?>
                                  <input type="hidden" name="exam_id" value="<?= esc((string)($ex['eid'] ?? '')) ?>">
                                  <button type="submit" class="btn btn-sm btn-outline-warning">Announce</button>
                                </form>
                              <?php endif; ?>
                              <?php if ($datesheetCount === 0): ?>
                                <form method="post" action="<?= base_url('admin/exam/delete') ?>" class="d-inline delete-exam-form">
                                  <?= csrf_field() ?>
                                  <input type="hidden" name="exam_id" value="<?= esc((string)($ex['eid'] ?? '')) ?>">
                                  <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                              <?php else: ?>
                                <span class="text-muted small">Delete blocked</span>
                              <?php endif; ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php else: ?>
                  <div class="p-3 text-muted">No exam exists in current session.</div>
                <?php endif; ?>
              </div>
            </div>

            <?php if ($hasUnannouncedLatest && !empty($latestExam)): ?>
              <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap" style="gap: .75rem;">
                <div>
                  Latest exam <strong><?= esc((string)($latestExam['exam_name'] ?? '')) ?></strong> is unannounced.
                  Please announce it before creating a new exam.
                </div>
                <form method="post" action="<?= base_url('admin/exam/announce') ?>" class="mb-0 announce-form">
                  <?= csrf_field() ?>
                  <input type="hidden" name="exam_id" value="<?= esc((string)($latestExam['eid'] ?? '')) ?>">
                  <button type="submit" class="btn btn-sm btn-warning">Announce Latest Exam</button>
                </form>
              </div>
            <?php endif; ?>

            <?php
              echo form_open(base_url('admin/exam/save'), 'role="form" id="user-edit-form"');
              echo form_hidden('id', (string)$id);
              echo form_hidden('campus_id', (string)$session_campus_id);
              if (function_exists('csrf_field')) { echo csrf_field(); }
            ?>

            <fieldset id="exam_list" <?= $hasUnannouncedLatest ? 'disabled' : '' ?>>
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
            </fieldset>

            <div class="form-group mt-3">
              <button type="submit" id="submitBtn" class="btn btn-primary" <?= $hasUnannouncedLatest ? 'disabled' : '' ?>>Save</button>
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

<script>
$(function(){
  $(document).on('submit', '.announce-form', function(e){
    const warningMessage = 'Warning: Once this exam is announced, it cannot be changed back to unannounced. Do you want to continue?';
    if (!window.confirm(warningMessage)) {
      e.preventDefault();
      return false;
    }
  });

  $(document).on('submit', '.delete-exam-form', function(e){
    const warningMessage = 'Delete this exam? This is only allowed when no datesheet exists for the exam.';
    if (!window.confirm(warningMessage)) {
      e.preventDefault();
      return false;
    }
  });

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
