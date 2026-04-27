<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<?php
if (isset($classes)) {
    echo '<!-- DEBUG: $classes found with ' . count($classes) . ' items -->';
} else {
    echo '<!-- DEBUG: $classes NOT FOUND -->';
}

if (isset($sectionsclassinfo)) {
    echo '<!-- DEBUG: $sectionsclassinfo found with ' . count($sectionsclassinfo) . ' items -->';
} else {
    echo '<!-- DEBUG: $sectionsclassinfo NOT FOUND -->';
}

if (isset($campusInfo)) {
    echo '<!-- DEBUG: $campusInfo found -->';
} else {
    echo '<!-- DEBUG: $campusInfo NOT FOUND -->';
}
?>
<?php
    // Inputs expected from controller:
    // $campusInfo, $fee_type_info, $a_fee_type_info, $t_fee_type_info, $h_fee_type_info
    // For edit: $fee_chalan (object|null), $isEdit (bool), optional $selected_fee_type_ids (array<int>)
    $isEdit = isset($isEdit) ? (bool)$isEdit : (isset($fee_chalan) && $fee_chalan);

    $header     = $isEdit ? 'Edit Fee Chalan' : 'Generate Fee Chalan';
    $chalan_id  = $isEdit ? ($fee_chalan->chalan_id ?? '') : '';

    // Prefills (dates in d/m/Y for your datetimepicker; months in Y-m for <input type="month">)
    $issue_date_val = $isEdit && !empty($fee_chalan->issue_date) ? date('d/m/Y', strtotime($fee_chalan->issue_date)) : '';
    $due_date_val   = $isEdit && !empty($fee_chalan->due_date)   ? date('d/m/Y', strtotime($fee_chalan->due_date))   : '';
    $fee_month_val  = $isEdit && !empty($fee_chalan->fee_month)  ? date('Y-m', strtotime($fee_chalan->fee_month . '-01')) : date('Y-m');
    $fine_month_val = $isEdit && !empty($fee_chalan->fine_month) ? date('Y-m', strtotime($fee_chalan->fine_month . '-01')) : '';

    $selected_fee_type_ids = $selected_fee_type_ids ?? [];
?>

<!-- Content Header -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><?= esc($header) ?></h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active"><?= esc($header) ?></li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
          <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/fee-chalan') ?>">Fee Chalan</a></li>
            <?php if (!$isEdit) { ?>
              <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/fee-chalan/add') ?>"><?= esc($header) ?></a></li>
            <?php } else { ?>
              <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/fee-chalan/edit' . $chalan_id) ?>"><?= esc($header) ?></a></li>
            <?php } ?>
          </ul>
        </div>

        <div class="card-body">
          <div class="tab-content">

          <form role="form" id="user-edit-form" method="post" action="<?= $base_url ?? base_url('admin/fee-chalan/save') ?>" accept-charset="utf-8">
              <?php if ($isEdit): ?>
                <?= form_hidden('chalan_id', $chalan_id); ?>
              <?php endif; ?>

              <div class="col-md-12 bg">
                <div id="loader-1" class="overlay" style="display:none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
              </div>

              <!-- Fee Type Selection -->
              <div class="form-group">
                <label>Choose fee type(s) to include</label><br />
                <div class="row">
                  <?php if (!empty($fee_type_info)) : ?>
                    <?php foreach ($fee_type_info as $fee_type_value) :
                      $checked = in_array($fee_type_value->fee_type_id, $selected_fee_type_ids) ? 'checked' : '';
                    ?>
                      <div class="col-lg-2 mb-3">
                        <div class="icheck-primary d-inline">
                          <input type="checkbox" name="fee_type_ids[]"
                                 id="s_<?= $fee_type_value->fee_type_id ?>"
                                 value="<?= $fee_type_value->fee_type_id ?>" <?= $checked ?>>
                          <label for="s_<?= $fee_type_value->fee_type_id ?>"><?= esc($fee_type_value->fee_type_name) ?></label>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>

                <!-- Academy Fee -->
                <?php if (!empty($campusInfo->a_flag) && (int)$campusInfo->a_flag === 1) : ?>
                  <label>Academy Fee</label>
                  <div class="row">
                    <?php if (!empty($a_fee_type_info)) : ?>
                      <?php foreach ($a_fee_type_info as $a_fee_type_value) :
                        $checked = in_array($a_fee_type_value->fee_type_id, $selected_fee_type_ids) ? 'checked' : '';
                      ?>
                        <div class="col-lg-2 mb-3">
                          <div class="icheck-primary d-inline">
                            <input type="checkbox" name="fee_type_ids[]"
                                   id="a_<?= $a_fee_type_value->fee_type_id ?>"
                                   value="<?= $a_fee_type_value->fee_type_id ?>" <?= $checked ?>>
                            <label for="a_<?= $a_fee_type_value->fee_type_id ?>"><?= esc($a_fee_type_value->fee_type_name) ?></label>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>

                <!-- Transport Fee -->
                <?php if (!empty($campusInfo->t_flag) && (int)$campusInfo->t_flag === 1) : ?>
                  <label>Transport Fee</label>
                  <div class="row">
                    <?php if (!empty($t_fee_type_info)) : ?>
                      <?php foreach ($t_fee_type_info as $t_fee_type_value) :
                        $checked = in_array($t_fee_type_value->fee_type_id, $selected_fee_type_ids) ? 'checked' : '';
                      ?>
                        <div class="col-lg-2 mb-3">
                          <div class="icheck-primary d-inline">
                            <input type="checkbox" name="fee_type_ids[]"
                                   id="t_<?= $t_fee_type_value->fee_type_id ?>"
                                   value="<?= $t_fee_type_value->fee_type_id ?>" <?= $checked ?>>
                            <label for="t_<?= $t_fee_type_value->fee_type_id ?>"><?= esc($t_fee_type_value->fee_type_name) ?></label>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>

                <!-- Hostel Fee -->
                <?php if (!empty($campusInfo->h_flag) && (int)$campusInfo->h_flag === 1) : ?>
                  <label>Hostel Fee</label>
                  <div class="row">
                    <?php if (!empty($h_fee_type_info)) : ?>
                      <?php foreach ($h_fee_type_info as $h_fee_type_value) :
                        $checked = in_array($h_fee_type_value->fee_type_id, $selected_fee_type_ids) ? 'checked' : '';
                      ?>
                        <div class="col-lg-2 mb-3">
                          <div class="icheck-primary d-inline">
                            <input type="checkbox" name="fee_type_ids[]"
                                   id="h_<?= $h_fee_type_value->fee_type_id ?>"
                                   value="<?= $h_fee_type_value->fee_type_id ?>" <?= $checked ?>>
                            <label for="h_<?= $h_fee_type_value->fee_type_id ?>"><?= esc($h_fee_type_value->fee_type_name) ?></label>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>

              </div><!-- /.form-group -->

              <!-- Dates & Months -->
              <div class="row align-items-end">
                <!-- Issue Date -->
                <div class="col-3">
                  <div class="form-group mb-0">
                    <label>Issue Date:</label>
                    <div class="input-group date" id="datepicker2" data-target-input="nearest">
                      <input type="text" name="issue_date" autocomplete="off"
                             class="form-control datetimepicker-input"
                             data-toggle="datetimepicker" data-target="#datepicker2"
                             value="<?= esc($issue_date_val) ?>"/>
                      <div class="input-group-append" data-target="#datepicker2" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Due Date -->
                <div class="col-3">
                  <div class="form-group mb-0">
                    <label>Due Date:</label>
                    <div class="input-group date" id="datepicker" data-target-input="nearest">
                      <input type="text" name="due_date" autocomplete="off"
                             class="form-control datetimepicker-input"
                             data-toggle="datetimepicker" data-target="#datepicker"
                             value="<?= esc($due_date_val) ?>"/>
                      <div class="input-group-append" data-target="#datepicker" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Fee Month -->
                <div class="col-3">
                  <div class="form-group mb-0">
                    <label>Fee Month:</label>
                    <input type="month" class="form-control" name="fee_month" value="<?= esc($fee_month_val) ?>">
                  </div>
                </div>

                <!-- Fine Month -->
                <div class="col-3">
                  <div class="form-group mb-0">
                    <label>Fine Month:</label>
                    <input type="month" class="form-control" name="fine_month" value="<?= esc($fine_month_val) ?>">
                  </div>
                </div>
              </div>

              <!-- Messages & Fine -->
              <div class="row">
                <!-- Header Message -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Chalan Header Message</label>
                    <div class="input-group mb-3">
                      <input type="text" class="form-control" id="chalan_h_msg" name="chalan_h_msg"
                             value="<?= esc($campusInfo->chalan_h_msg ?? '') ?>">
                      <div class="input-group-append">
                        <button class="btn btn-primary" id="btn_h_msg" style="height:35px; line-height:17px; margin-left:5px; width:146px;" type="button">
                          Save Message
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Footer Message -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Chalan Footer Message</label>
                    <div class="input-group mb-3">
                      <input type="text" class="form-control" id="chalan_f_msg" name="chalan_f_msg"
                             value="<?= esc($campusInfo->chalan_f_msg ?? '') ?>">
                      <div class="input-group-append">
                        <button class="btn btn-primary" id="btn_f_msg" style="height:35px; line-height:17px; margin-left:5px; width:146px;" type="button">
                          Save Message
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <!-- Fine Type -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Fine Type</label>
                    <select name="fine_type" class="form-control">
                      <option value="per_day_fine" <?= (!empty($campusInfo->fine_type) && $campusInfo->fine_type == 'per_day_fine') ? 'selected' : '' ?>>Per Day Fine</option>
                      <option value="fixed_fine"   <?= (!empty($campusInfo->fine_type) && $campusInfo->fine_type == 'fixed_fine')   ? 'selected' : '' ?>>Fixed Fine</option>
                    </select>
                  </div>
                </div>

                <!-- Fine Amount -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Fine Amount</label>
                    <div class="input-group mb-3">
                      <input type="text" class="form-control" id="late_fee_fine" name="late_fee_fine"
                             value="<?= esc($campusInfo->late_fee_fine ?? '') ?>">
                      <div class="input-group-append">
                        <button class="btn btn-primary" id="btn_late_fee" style="height:35px; line-height:17px; margin-left:5px; width:200px;" type="button">
                          Save Fine
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="col-sm-12">
                <div class="form-group">
                  <button type="submit" id="submitBtn" class="btn btn-primary mr-2">
                    <?= $isEdit ? 'Update Fee Chalan' : 'Generate Fee Chalan' ?>
                  </button>
                  <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
                </div>
              </div>

            </form>

          </div><!-- /.tab-content -->
        </div><!-- /.card-body -->
      </div><!-- /.card -->
    </div><!-- /.col -->
  </div><!-- /.row -->


  <!-- Progress Modal (only used for ADD/SSE flow) -->
  <div class="modal fade" id="progressModal" tabindex="-1" role="dialog" aria-labelledby="progressModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title font-weight-bold" id="progressModalLabel">
            <i class="fas fa-spinner fa-pulse mr-2"></i>Generating Fee Chalans
          </h5>
        </div>
        <div class="modal-body">
          <div class="progress mb-3 rounded" style="height:25px;">
            <div id="progressBar" class="progress-bar bg-primary progress-bar-striped progress-bar-animated text-center font-weight-bold"
                 role="progressbar" style="width:0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
              <span class="progress-text">0%</span>
            </div>
          </div>

          <div id="progressDetails">
            <div class="d-flex align-items-center justify-content-center mb-3">
              <div class="spinner-border spinner-border-sm text-primary mr-2" role="status"></div>
              <span class="font-weight-bold text-primary">Preparing chalan generation...</span>
            </div>
            <div class="progress-stats row text-center mb-3">
              <div class="col-4">
                <span class="badge badge-light border border-secondary">
                  <i class="fas fa-users mr-1"></i> Total: <span id="totalStudents">0</span>
                </span>
              </div>
              <div class="col-4">
                <span class="badge badge-success">
                  <i class="fas fa-check-circle mr-1"></i> Success: <span id="successCount">0</span>
                </span>
              </div>
              <div class="col-4">
                <span class="badge badge-warning">
                  <i class="fas fa-exclamation-circle mr-1"></i> Skipped: <span id="skippedCount">0</span>
                </span>
              </div>
            </div>
            <div id="currentProcessing" class="text-center small text-muted">
              <i class="fas fa-user-graduate mr-1"></i> Currently processing: <span>Initializing...</span>
            </div>
          </div>

          <div id="completeIndicator" class="text-center py-2" style="display:none;">
            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
            <p class="font-weight-bold text-success mb-0">Operation Completed Successfully!</p>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <small class="text-muted"><i class="fas fa-info-circle"></i> Do not close this window</small>
          <button id="cancelBtn" type="button" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-times mr-1"></i> Cancel
          </button>
        </div>
      </div>
    </div>
  </div>

</section>
<!-- /.content -->

<!-- Scripts -->
<script>
const IS_EDIT = <?= $isEdit ? 'true' : 'false' ?>;

$(document).ready(function () {
  let isProcessing = false;
  let cancelRequested = false;
  let eventSource = null;
  let progressAnimationInterval = null;

  $('#user-edit-form').validate({
    rules: {
      fee_month: { required: true },
      'fee_type_ids[]': { required: true, minlength: 1 }
    },
    messages: {
      fee_month: { required: 'Please select fee month' },
      'fee_type_ids[]': { required: 'Please select at least one fee type' }
    },
    submitHandler: function (form) {
      if (isProcessing) return false;

      // Branch by mode
      if (IS_EDIT) {
        // ---- EDIT: Normal AJAX to update endpoint ----
        const formData = new FormData(form);
        isProcessing = true;

        $.ajax({
          url: '<?= base_url('admin/fee_chalan/update') ?>', // Adjust if your update route differs
          type: 'POST',
          data: formData,
          dataType: 'json',
          cache: false,
          processData: false,
          contentType: false,
          beforeSend: function(){ $('#loader-1').show(); $('#submitBtn').prop('disabled', true); },
          complete:   function(){ $('#loader-1').hide(); $('#submitBtn').prop('disabled', false); isProcessing = false; },
          success: function (res) {
            if (res.status === 'success') {
              swal("Updated!", res.message || "Fee Chalan updated successfully.", "success");
            } else {
              swal("Error!", res.message || "Update failed.", "error");
            }
          },
          error: function (xhr, status, error) {
            swal("Error!", "Unexpected error: " + (error || 'Unknown'), "error");
          }
        });

      } else {
        // ---- ADD: Use your existing SSE bulk generation flow ----
        cancelRequested = false;
        isProcessing    = true;

        // Show modal and reset progress UI
        $('#progressModal').modal('show');
        $('#progressBar').css('width', '0%').attr('aria-valuenow', 0)
                        .removeClass('bg-danger bg-success').addClass('progress-bar-animated');
        $('#progressDetails').html(`
          <div class="d-flex align-items-center">
            <div class="spinner-border spinner-border-sm mr-2" role="status"></div>
            <span>Preparing chalan generation...</span>
          </div>
          <div class="progress-stats mt-2">
            <div class="row">
              <div class="col-4"><span class="badge badge-primary">Total: <span id="totalStudents">0</span></span></div>
              <div class="col-4"><span class="badge badge-success">Success: <span id="successCount">0</span></span></div>
              <div class="col-4"><span class="badge badge-warning">Skipped: <span id="skippedCount">0</span></span></div>
            </div>
          </div>
          <div id="currentProcessing" class="mt-2 small text-muted"></div>
        `);
        $('#cancelBtn').show();
        $('#completeIndicator').hide();

        // Small fake progress before first server event
        let fakeProgress = 0;
        progressAnimationInterval = setInterval(() => {
          fakeProgress += Math.random() * 2;
          if (fakeProgress > 15) fakeProgress = 15;
          $('#progressBar').css('width', fakeProgress + '%').attr('aria-valuenow', Math.round(fakeProgress));
          $('#progressBar .progress-text').text(Math.round(fakeProgress) + '%');
        }, 200);

        // Gather values
        const fee_month  = $('input[name="fee_month"]').val();
        const issue_date = $('input[name="issue_date"]').val();
        const due_date   = $('input[name="due_date"]').val();
        const fee_type_ids = [];
        $('input[name="fee_type_ids[]"]:checked').each(function(){ fee_type_ids.push($(this).val()); });

        // Build query string for SSE
        const params = new URLSearchParams();
        params.append('fee_month', fee_month);
        params.append('issue_date', issue_date);
        params.append('due_date', due_date);
        fee_type_ids.forEach(id => params.append('fee_type_ids[]', id));

        const streamUrl = '<?= base_url('admin/fee-chalan/bulk_chalan_stream') ?>?' + params.toString();
        eventSource = new EventSource(streamUrl);

        eventSource.onmessage = function (e) {
          const data = JSON.parse(e.data);
          if (data.type === 'progress') {
            if (progressAnimationInterval) { clearInterval(progressAnimationInterval); progressAnimationInterval = null; }
            const percent = Math.round((data.processed / data.total) * 100);
            animateProgressBar(percent);
            $('#totalStudents').text(data.total);
            $('#successCount').text(data.success);
            $('#skippedCount').text(data.skipped);
            $('#currentProcessing').html(`Processing student ${data.processed} of ${data.total}` + (data.current_student ? ` (ID: ${data.current_student})` : ''));
          } else if (data.type === 'complete') {
            eventSource.close();
            isProcessing = false;
            animateProgressBar(100, function() {
              $('#progressBar').removeClass('progress-bar-animated').addClass('bg-success');
              $('#progressDetails').html(`
                <div class="text-center py-3">
                  <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                  <h5>Chalan Generation Complete!</h5>
                  <p class="mb-1">Total processed: ${data.total}</p>
                  <p>Success: ${data.success} | Skipped: ${data.skipped}</p>
                </div>
              `);
              $('#cancelBtn').hide();
              $('#completeIndicator').show();
            });
            setTimeout(() => {
              $('#progressModal').modal('hide');
              swal({ title: "Success!", text: `Generated ${data.success} chalans successfully (${data.skipped} skipped)`, icon: "success", timer: 3000 });
            }, 2000);
          } else if (data.type === 'error') {
            eventSource.close();
            isProcessing = false;
            if (progressAnimationInterval) { clearInterval(progressAnimationInterval); progressAnimationInterval = null; }
            $('#progressBar').removeClass('progress-bar-animated').addClass('bg-danger');
            $('#progressDetails').html(`
              <div class="text-center py-3">
                <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                <h5>Error Occurred</h5>
                <p>${data.message}</p>
                <p class="small">Processed ${data.processed} of ${data.total}</p>
              </div>
            `);
            $('#cancelBtn').hide();
            setTimeout(() => {
              $('#progressModal').modal('hide');
              swal({ title: "Error!", text: data.message, icon: "error" });
            }, 2000);
          }
        };

        eventSource.onerror = function () {
          if (!cancelRequested) {
            if (progressAnimationInterval) { clearInterval(progressAnimationInterval); progressAnimationInterval = null; }
            $('#progressBar').removeClass('progress-bar-animated').addClass('bg-danger');
            $('#progressDetails').html(`
              <div class="text-center py-3">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h5>Connection Error</h5>
                <p>Failed to maintain connection with the server</p>
              </div>
            `);
            setTimeout(() => $('#progressModal').modal('hide'), 2000);
          }
          if (eventSource) eventSource.close();
          isProcessing = false;
        };
      }

      return false; // prevent native submit
    }
  });

  // Smooth progress bar animation (for SSE flow)
  function animateProgressBar(targetPercent, callback) {
    const progressBar = $('#progressBar');
    const now = parseInt(progressBar.attr('aria-valuenow')) || 0;
    const step = targetPercent > now ? 1 : -1;

    let current = now;
    const interval = setInterval(() => {
      current += step;
      if ((step > 0 && current >= targetPercent) || (step < 0 && current <= targetPercent)) {
        current = targetPercent;
        clearInterval(interval);
        if (callback) callback();
      }
      progressBar.css('width', current + '%').attr('aria-valuenow', current);
      progressBar.find('.progress-text').text(current + '%');
    }, 10);
  }

  $('#cancelBtn').click(function () {
    cancelRequested = true;
    if (eventSource) eventSource.close();
    if (progressAnimationInterval) { clearInterval(progressAnimationInterval); progressAnimationInterval = null; }

    $('#progressBar').removeClass('progress-bar-animated').addClass('bg-warning');
    $('#progressDetails').html(`
      <div class="text-center py-3">
        <i class="fas fa-info-circle fa-3x text-warning mb-3"></i>
        <h5>Operation Cancelled</h5>
        <p>Process was stopped by user request</p>
      </div>
    `);
    setTimeout(() => $('#progressModal').modal('hide'), 1500);
    isProcessing = false;
  });

  $('#progressModal').on('hidden.bs.modal', function () {
    if (eventSource) eventSource.close();
    if (progressAnimationInterval) { clearInterval(progressAnimationInterval); progressAnimationInterval = null; }
    isProcessing = false;
  });

  // Quick setting saves (header/footer/fine)
  $("#btn_h_msg").click(function(){
    var chalan_h_msg = $('#chalan_h_msg').val();
    $.post('<?= base_url('admin/fee_chalan/updateChalanSetting'); ?>', { chalan_h_msg }, function(res){
      var json = $.parseJSON(res);
      json.success ? toastr.success(json.msg) : toastr.error('Update Error');
    });
  });

  $("#btn_f_msg").click(function(){
    var chalan_f_msg = $('#chalan_f_msg').val();
    $.post('<?= base_url('admin/fee_chalan/updateChalanSetting'); ?>', { chalan_f_msg }, function(res){
      var json = $.parseJSON(res);
      json.success ? toastr.success(json.msg) : toastr.error('Update Error');
    });
  });

  $("#btn_late_fee").click(function(){
    var late_fee_fine = $('#late_fee_fine').val();
    $.post('<?= base_url('admin/fee_chalan/updateChalanSetting'); ?>', { late_fee_fine }, function(res){
      var json = $.parseJSON(res);
      json.success ? toastr.success(json.msg) : toastr.error('Update Error');
    });
  });

  // Date pickers: keep existing values in edit mode
  const dueVal   = $('input[name="due_date"]').val();
  const issueVal = $('input[name="issue_date"]').val();

  $('#datepicker').datetimepicker({
    format: 'DD/MM/YYYY',
    defaultDate: dueVal ? false : new Date(new Date().getTime() + (10*24*60*60*1000))
  });
  $('#datepicker2').datetimepicker({
    format: 'DD/MM/YYYY',
    defaultDate: issueVal ? false : 'now'
  });
});
</script>

<style type="text/css">
  .progress { height: 25px; }
  .progress-bar { transition: width 0.3s ease; }
  #status-message { min-height: 50px; display:flex; align-items:center; }
</style>

<?= $this->endSection() ?>
