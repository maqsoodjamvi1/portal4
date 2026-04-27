<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
// Inputs expected from controller:
// $campusInfo, $fee_type_info, $a_fee_type_info, $t_fee_type_info, $h_fee_type_info
// $today_chalans - Array of chalans generated today
$isEdit = isset($isEdit) ? (bool)$isEdit : (isset($fee_chalan) && $fee_chalan);
$header = $isEdit ? 'Edit Fee Chalan' : 'Generate Fee Chalan';
$chalan_id = $isEdit ? ($fee_chalan->chalan_id ?? '') : '';

// Prefills with default values
$issue_date_val = $isEdit && !empty($fee_chalan->issue_date) ? date('d/m/Y', strtotime($fee_chalan->issue_date)) : date('d/m/Y');
$due_date_val = $isEdit && !empty($fee_chalan->due_date) ? date('d/m/Y', strtotime($fee_chalan->due_date)) : date('d/m/Y', strtotime('+10 days'));



$fine_month_val = $isEdit && !empty($fee_chalan->fine_month) ? date('Y-m', strtotime($fee_chalan->fine_month . '-01')) : '';
$selected_fee_type_ids = $selected_fee_type_ids ?? [];
$today_chalans = $today_chalans ?? [];
?>

<!-- Content Header -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><?= esc($header) ?></h1>
      </div>
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
    <!-- Left Column: Generation Form -->
    <div class="col-lg-6">
      <div class="card card-primary card-outline">
       

        <div class="card-body">
          <form role="form" id="chalanForm" method="post" action="<?= $base_url ?? base_url('admin/fee-chalan/save') ?>">
            <?php if ($isEdit): ?>
              <?= form_hidden('chalan_id', $chalan_id); ?>
            <?php endif; ?>

            <div id="loader-1" class="overlay" style="display:none;">
              <i class="fas fa-2x fa-sync-alt fa-spin"></i>
            </div>
            
            <!-- ========== FEE TYPE SELECTION ========== -->
            <div class="form-group mb-4">
              <label class="font-weight-bold">
                <i class="fas fa-tags mr-1"></i> Select Fee Types <span class="text-danger">*</span>
              </label>
              
              <div class="floating-checkbox-container">
                <?php if (!empty($fee_type_info)) : ?>
                  <?php foreach ($fee_type_info as $fee_type_value) :
                      $checked = in_array($fee_type_value->fee_type_id, $selected_fee_type_ids) ? 'checked' : '';
                  ?>
                    <div class="floating-checkbox">
                      <input type="checkbox" name="fee_type_ids[]" 
                             value="<?= $fee_type_value->fee_type_id ?>" 
                             id="float_<?= $fee_type_value->fee_type_id ?>"
                             class="float-checkbox"
                             <?= $checked ?>>
                      <label for="float_<?= $fee_type_value->fee_type_id ?>" class="float-label">
                        <i class="fas fa-tag"></i>
                        <span><?= esc($fee_type_value->fee_type_name) ?></span>
                      </label>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
                
                <!-- Add other fee types similarly... -->
              </div>
            </div>
            <!-- ========== END FEE TYPE SELECTION ========== -->

            <!-- Row 1: Issue Date & Due Date -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label><i class="fas fa-calendar-alt mr-1"></i> Issue Date <span class="text-danger">*</span></label>
                  <div class="input-group date" id="datepicker2" data-target-input="nearest">
                    <input type="text" name="issue_date" autocomplete="off"
                           class="form-control datetimepicker-input"
                           data-toggle="datetimepicker" data-target="#datepicker2"
                           value="<?= esc($issue_date_val) ?>" required/>
                    <div class="input-group-append" data-target="#datepicker2" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label><i class="fas fa-calendar-check mr-1"></i> Due Date <span class="text-danger">*</span></label>
                  <div class="input-group date" id="datepicker" data-target-input="nearest">
                    <input type="text" name="due_date" autocomplete="off"
                           class="form-control datetimepicker-input"
                           data-toggle="datetimepicker" data-target="#datepicker"
                           value="<?= esc($due_date_val) ?>" required/>
                    <div class="input-group-append" data-target="#datepicker" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

         <!-- Row 2: Fee Month & Fine Month -->
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label><i class="fas fa-calendar-week mr-1"></i> Fee Month <span class="text-danger">*</span></label>
            <input type="month" class="form-control" name="fee_month" 
                   value="<?= esc($fee_month_val) ?>" required>
            <?php if (isset($fee_month_note) && !empty($fee_month_note)): ?>
                <small class="form-text text-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    <?= esc($fee_month_note) ?>
                </small>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label><i class="fas fa-calendar-week mr-1"></i> Fine Month (Optional)</label>
            <input type="month" class="form-control" name="fine_month" 
                   value="<?= esc($fine_month_val ?? '') ?>">
            <small class="form-text text-muted">Leave empty if no fine applicable</small>
        </div>
    </div>
</div>
            <!-- Row 3: Fine Type & Fine Amount -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Fine Type</label>
                  <select name="fine_type" class="form-control">
                    <option value="per_day_fine" <?= (!empty($campusInfo->fine_type) && $campusInfo->fine_type == 'per_day_fine') ? 'selected' : '' ?>>
                      Per Day Fine
                    </option>
                    <option value="fixed_fine" <?= (!empty($campusInfo->fine_type) && $campusInfo->fine_type == 'fixed_fine') ? 'selected' : '' ?>>
                      Fixed Fine
                    </option>
                  </select>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Fine Amount</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="late_fee_fine" name="late_fee_fine"
                           value="<?= esc($campusInfo->late_fee_fine ?? '') ?>">
                    <div class="input-group-append">
                      <button class="btn btn-primary" id="btn_late_fee" type="button">
                        Save
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Row 4: Header Message -->
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Header Message</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="chalan_h_msg" name="chalan_h_msg"
                           value="<?= esc($campusInfo->chalan_h_msg ?? '') ?>">
                    <div class="input-group-append">
                      <button class="btn btn-primary" id="btn_h_msg" type="button">
                        Save
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Row 5: Footer Message -->
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Footer Message</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="chalan_f_msg" name="chalan_f_msg"
                           value="<?= esc($campusInfo->chalan_f_msg ?? '') ?>">
                    <div class="input-group-append">
                      <button class="btn btn-primary" id="btn_f_msg" type="button">
                        Save
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="form-group text-center mt-4">
              <button type="submit" id="submitBtn" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-<?= $isEdit ? 'save' : 'play' ?> mr-2"></i>
                <?= $isEdit ? 'Update Fee Chalan' : 'Generate Fee Chalans' ?>
              </button>
              <button type="button" class="btn btn-secondary btn-lg px-4 ml-2" onclick="history.go(-1);">
                <i class="fas fa-times mr-2"></i>Cancel
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>

    <!-- Right Column: Today's Generated Chalans -->
    <div class="col-lg-6">
      <div class="card card-success card-outline">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-calendar-day mr-2"></i>
            Today's Generated Chalans
            <span class="badge badge-success ml-2"><?= is_array($today_chalans) ? count($today_chalans) : 0 ?></span>
          </h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body p-0">
          <?php if (empty($today_chalans)): ?>
            <div class="text-center py-5">
              <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
              <p class="text-muted">No chalans generated today</p>
            </div>
          <?php else: ?>
            <?php
            // Split chalans into new and existing admissions
            $newAdmissions = array_filter($today_chalans, function($chalan) {
                return isset($chalan->is_new_admission) && $chalan->is_new_admission == 1;
            });
            $existingAdmissions = array_filter($today_chalans, function($chalan) {
                return isset($chalan->is_new_admission) && $chalan->is_new_admission == 0;
            });
            ?>
            
            <!-- New Admissions Section -->
            <?php if (!empty($newAdmissions)): ?>
              <div class="bg-success-light border-bottom border-success">
                <div class="p-2 bg-success text-white">
                  <i class="fas fa-star-of-life mr-1"></i>
                  <strong>New Admissions</strong>
                  <span class="badge badge-light ml-2"><?= count($newAdmissions) ?></span>
                  <small class="ml-2">(Admitted this month)</small>
                </div>
              </div>
              <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-hover table-striped mb-0">
                  <thead class="bg-light">
                    <tr>
                      <th style="width: 35%">Student Details</th>
                      <th style="width: 35%">Chalan Details</th>
                      <th style="width: 20%">Amount & Month</th>
                      <th style="width: 10%">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($newAdmissions as $chalan): ?>
                      <tr class="table-success" data-chalan-id="<?= $chalan->chalan_id ?>">
                        <td>
                          <div class="d-flex flex-column">
                            <strong class="text-success">
                              <i class="fas fa-user-graduate mr-1"></i>
                              <?= esc($chalan->student_name ?? 'N/A') ?>
                              <span class="badge badge-success ml-1">New</span>
                            </strong>
                            <small class="text-muted mt-1">
                              <i class="fas fa-calendar-alt mr-1"></i>
                              Admission: <?= date('d M Y', strtotime($chalan->date_of_admission ?? 'now')) ?>
                            </small>
                            <small class="text-muted mt-1">
                              <i class="fas fa-graduation-cap mr-1"></i>
                              <?= esc($chalan->class_display ?? 'N/A') ?>
                            </small>
                          </div>
                        </td>
                        <td>
                          <div class="d-flex flex-column">
                            <div class="mb-1">
                              <span class="badge badge-info">
                                <i class="fas fa-file-invoice mr-1"></i>
                                <?= esc($chalan->invoice_no ?? 'N/A') ?>
                              </span>
                            </div>
                            <div>
                              <span class="badge badge-primary">
                                <i class="fas fa-tag mr-1"></i>
                                <?= esc($chalan->fee_type_name ?? 'N/A') ?>
                              </span>
                            </div>
                          </div>
                        </td>
                        <td class="text-center">
                          <div class="d-flex flex-column">
                            <strong class="text-success">
                              PKR <?= number_format($chalan->amount ?? 0, 2) ?>
                            </strong>
                            <small class="text-muted mt-1">
                              <i class="fas fa-calendar-alt mr-1"></i>
                              <?= !empty($chalan->fee_month) ? date('M Y', strtotime($chalan->fee_month . '-01')) : 'N/A' ?>
                            </small>
                          </div>
                        </td>
                        <td class="text-center">
                          <button class="btn btn-sm btn-danger delete-chalan" 
                                  data-id="<?= $chalan->chalan_id ?? '' ?>"
                                  data-invoice="<?= esc($chalan->invoice_no ?? '') ?>"
                                  data-name="<?= esc($chalan->student_name ?? '') ?>"
                                  data-feetype="<?= esc($chalan->fee_type_name ?? '') ?>"
                                  title="Delete Chalan">
                            <i class="fas fa-trash"></i>
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
            
            <!-- Existing Students Section -->
            <?php if (!empty($existingAdmissions)): ?>
              <div class="bg-secondary-light border-bottom border-secondary">
                <div class="p-2 bg-secondary text-white">
                  <i class="fas fa-users mr-1"></i>
                  <strong>Existing Students</strong>
                  <span class="badge badge-light ml-2"><?= count($existingAdmissions) ?></span>
                </div>
              </div>
              <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-hover table-striped mb-0">
                  <thead class="bg-light">
                    <tr>
                      <th style="width: 35%">Student Details</th>
                      <th style="width: 35%">Chalan Details</th>
                      <th style="width: 20%">Amount & Month</th>
                      <th style="width: 10%">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($existingAdmissions as $chalan): ?>
                      <tr data-chalan-id="<?= $chalan->chalan_id ?>">
                        <td>
                          <div class="d-flex flex-column">
                            <strong class="text-primary">
                              <i class="fas fa-user-graduate mr-1"></i>
                              <?= esc($chalan->student_name ?? 'N/A') ?>
                            </strong>
                            <small class="text-muted mt-1">
                              <i class="fas fa-graduation-cap mr-1"></i>
                              <?= esc($chalan->class_display ?? 'N/A') ?>
                            </small>
                          </div>
                        </td>
                        <td>
                          <div class="d-flex flex-column">
                            <div class="mb-1">
                              <span class="badge badge-info">
                                <i class="fas fa-file-invoice mr-1"></i>
                                <?= esc($chalan->invoice_no ?? 'N/A') ?>
                              </span>
                            </div>
                            <div>
                              <span class="badge badge-primary">
                                <i class="fas fa-tag mr-1"></i>
                                <?= esc($chalan->fee_type_name ?? 'N/A') ?>
                              </span>
                            </div>
                          </div>
                        </td>
                        <td class="text-center">
                          <div class="d-flex flex-column">
                            <strong class="text-success">
                              PKR <?= number_format($chalan->amount ?? 0, 2) ?>
                            </strong>
                            <small class="text-muted mt-1">
                              <i class="fas fa-calendar-alt mr-1"></i>
                              <?= !empty($chalan->fee_month) ? date('M Y', strtotime($chalan->fee_month . '-01')) : 'N/A' ?>
                            </small>
                          </div>
                        </td>
                        <td class="text-center">
                          <button class="btn btn-sm btn-danger delete-chalan" 
                                  data-id="<?= $chalan->chalan_id ?? '' ?>"
                                  data-invoice="<?= esc($chalan->invoice_no ?? '') ?>"
                                  data-name="<?= esc($chalan->student_name ?? '') ?>"
                                  data-feetype="<?= esc($chalan->fee_type_name ?? '') ?>"
                                  title="Delete Chalan">
                            <i class="fas fa-trash"></i>
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
            
            <div class="card-footer bg-light">
              <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                  <span class="text-muted small">
                    <i class="fas fa-info-circle mr-1"></i>
                    Total: <?= count($today_chalans) ?> chalans
                  </span>
                  <?php if (!empty($newAdmissions)): ?>
                    <span class="text-success small ml-3">
                      <i class="fas fa-star-of-life mr-1"></i>
                      New: <?= count($newAdmissions) ?>
                    </span>
                  <?php endif; ?>
                </div>
                <button id="deleteAllTodayBtn" class="btn btn-danger btn-sm" 
                        data-total="<?= count($today_chalans) ?>">
                  <i class="fas fa-trash-alt mr-1"></i>
                  Delete All
                </button>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- Add Select2 CSS and JS (optional - keep for other elements if needed) -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<!-- Add SweetAlert2 CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Also add toastr if you're using it -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Confirmation Modal for Generation -->
<div class="modal fade" id="confirmGenerationModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">
          <i class="fas fa-check-circle mr-2"></i>Confirm Chalan Generation
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle mr-2"></i>
          Please review the details before proceeding:
        </div>
        
        <div class="row mb-3">
          <div class="col-md-6">
            <div class="info-box bg-light">
              <span class="info-box-icon bg-primary"><i class="fas fa-calendar-alt"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Issue Date</span>
                <span class="info-box-number" id="confirmIssueDate">-</span>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="info-box bg-light">
              <span class="info-box-icon bg-warning"><i class="fas fa-calendar-check"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Due Date</span>
                <span class="info-box-number" id="confirmDueDate">-</span>
              </div>
            </div>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <div class="info-box bg-light">
              <span class="info-box-icon bg-success"><i class="fas fa-calendar-week"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Fee Month</span>
                <span class="info-box-number" id="confirmFeeMonth">-</span>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="info-box bg-light">
              <span class="info-box-icon bg-secondary"><i class="fas fa-tags"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Fee Types</span>
                <span class="info-box-number" id="confirmFeeTypes">-</span>
              </div>
            </div>
          </div>
        </div>

        <div class="alert alert-info">
          <i class="fas fa-info-circle mr-2"></i>
          <strong>Note:</strong> This will generate chalans for all active students. 
          The process may take a few minutes depending on the number of students.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i>Cancel
        </button>
        <button type="button" id="confirmGenerateBtn" class="btn btn-success">
          <i class="fas fa-check mr-1"></i>Confirm & Generate
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Progress Modal -->
<div class="modal fade" id="progressModal" tabindex="-1" role="dialog" data-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="fas fa-spinner fa-pulse mr-2"></i>Generating Fee Chalans
        </h5>
      </div>
      <div class="modal-body">
        <div class="progress mb-3" style="height: 30px;">
          <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
               style="width: 0%">0%</div>
        </div>
        <div id="progressStats" class="text-center">
          <p>Total: <span id="totalStudents">0</span></p>
          <p>Success: <span id="successCount">0</span> | Skipped: <span id="skippedCount">0</span></p>
          <p id="currentStudent" class="text-muted small">Initializing...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button id="cancelProgressBtn" class="btn btn-secondary btn-sm">
          <i class="fas fa-stop"></i> Cancel
        </button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // ========== FORCE FEE MONTH VALUE FROM PHP ==========
    // This runs immediately to ensure the correct value is set
    const phpFeeMonth = '<?= esc($fee_month_val) ?>';
    const feeMonthInput = document.querySelector('input[name="fee_month"]');
    
    if (feeMonthInput && phpFeeMonth && phpFeeMonth !== '') {
        feeMonthInput.value = phpFeeMonth;
        console.log('Fee month set to:', feeMonthInput.value);
    }
    // ========== END FORCE FEE MONTH VALUE ==========
    
    const IS_EDIT = <?= $isEdit ? 'true' : 'false' ?>;
    let eventSource = null;
    let isProcessing = false;
    let cancelRequested = false;

    // Custom validation method for fee types
    $.validator.addMethod("feeTypesRequired", function(value, element) {
        return $('input[name="fee_type_ids[]"]:checked').length > 0;
    }, "Please select at least one fee type");

    // Update selected count
    function updateSelectedCount() {
        const count = $('.fee-card-checkbox-modern:checked').length;
        $('#selectedCount').text(count + ' selected');
    }
    
    // Card click handler
    $('.fee-card-modern').on('click', function(e) {
        const checkbox = $(this).find('.fee-card-checkbox-modern');
        checkbox.prop('checked', !checkbox.prop('checked'));
        $(this).toggleClass('selected', checkbox.prop('checked'));
        updateSelectedCount();
        $('#chalanForm').validate().element($('select[name="fee_type_ids[]"]'));
    });
    
    // Select All
    $('#selectAllCardsModern').click(function() {
        $('.fee-card-checkbox-modern').prop('checked', true);
        $('.fee-card-modern').addClass('selected');
        updateSelectedCount();
        $('#chalanForm').validate().element($('select[name="fee_type_ids[]"]'));
    });
    
    // Deselect All
    $('#deselectAllCardsModern').click(function() {
        $('.fee-card-checkbox-modern').prop('checked', false);
        $('.fee-card-modern').removeClass('selected');
        updateSelectedCount();
        $('#chalanForm').validate().element($('select[name="fee_type_ids[]"]'));
    });
    
    // Select Standard Only
    $('#selectStandardOnly').click(function() {
        $('.fee-card-checkbox-modern').prop('checked', false);
        $('.fee-card-modern').removeClass('selected');
        $('.fee-card-modern:not(.academy):not(.transport):not(.hostel)').each(function() {
            $(this).find('.fee-card-checkbox-modern').prop('checked', true);
            $(this).addClass('selected');
        });
        updateSelectedCount();
        $('#chalanForm').validate().element($('select[name="fee_type_ids[]"]'));
    });
    
    // Select Academy Only
    $('#selectAcademyOnly').click(function() {
        $('.fee-card-checkbox-modern').prop('checked', false);
        $('.fee-card-modern').removeClass('selected');
        $('.fee-card-modern.academy').each(function() {
            $(this).find('.fee-card-checkbox-modern').prop('checked', true);
            $(this).addClass('selected');
        });
        updateSelectedCount();
        $('#chalanForm').validate().element($('select[name="fee_type_ids[]"]'));
    });
    
    // Initialize date pickers
    $('#datepicker').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#datepicker2').datetimepicker({ format: 'DD/MM/YYYY' });

    // ========== RE-ENSURE FEE MONTH AFTER DATEPICKER INIT ==========
    // Sometimes datepicker can interfere, so set it again
    setTimeout(function() {
        if (feeMonthInput && phpFeeMonth && phpFeeMonth !== '') {
            feeMonthInput.value = phpFeeMonth;
            console.log('Re-set fee month to:', feeMonthInput.value);
        }
    }, 100);
    // ========== END RE-ENSURE ==========

    // Form validation and confirmation
    $('#chalanForm').validate({
        rules: {
            fee_month: { required: true },
            issue_date: { required: true },
            due_date: { required: true },
            'fee_type_ids[]': { feeTypesRequired: true }
        },
        messages: {
            fee_month: 'Please select fee month',
            issue_date: 'Please select issue date',
            due_date: 'Please select due date',
            'fee_type_ids[]': 'Please select at least one fee type'
        },
        submitHandler: function(form) {
            if (IS_EDIT) {
                form.submit();
            } else {
                showConfirmationModal(form);
            }
            return false;
        }
    });

    // Show confirmation modal with selected values
    function showConfirmationModal(form) {
        // Get form values
        const issueDate = $('input[name="issue_date"]').val();
        const dueDate = $('input[name="due_date"]').val();
        const feeMonth = $('input[name="fee_month"]').val();
        const feeTypes = [];
        
        // Get selected fee types from floating checkboxes
        $('input[name="fee_type_ids[]"]:checked').each(function() {
            const label = $(this).next('label').text().trim();
            const cleanLabel = label.replace(/\s+/g, ' ').trim();
            feeTypes.push(cleanLabel);
        });
        
        console.log("Selected fee types:", feeTypes);
        
        // Format month for display
        let formattedMonth = '-';
        if (feeMonth) {
            const date = new Date(feeMonth + '-01');
            formattedMonth = date.toLocaleString('default', { month: 'long', year: 'numeric' });
        }
        
        // Update modal content
        $('#confirmIssueDate').text(issueDate || '-');
        $('#confirmDueDate').text(dueDate || '-');
        $('#confirmFeeMonth').text(formattedMonth);
        
        // Display fee types
        if (feeTypes.length > 0) {
            if (feeTypes.length <= 5) {
                $('#confirmFeeTypes').text(feeTypes.join(', '));
            } else {
                $('#confirmFeeTypes').html(`${feeTypes.length} types selected:<br><small class="text-muted">${feeTypes.slice(0, 5).join(', ')}${feeTypes.length > 5 ? '...' : ''}</small>`);
            }
        } else {
            $('#confirmFeeTypes').text('None selected');
        }
        
        $('#confirmGenerationModal').modal('show');
    }

 $('#confirmGenerateBtn').off('click').on('click', function() {
        // Close the confirmation modal
        $('#confirmGenerationModal').modal('hide');
        
        // Show a loading state on the generate button if needed
        const submitBtn = $('#submitBtn');
        const originalHtml = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Generating...');
        submitBtn.prop('disabled', true);
        
        // Start the SSE generation process
        startGeneration();
        
        // Reset button state after a delay (though generation may take time)
        setTimeout(() => {
            submitBtn.html(originalHtml);
            submitBtn.prop('disabled', false);
        }, 1000);
    });
    
    // Start SSE generation
    function startGeneration() {
        if (isProcessing) return;
        
        cancelRequested = false;
        isProcessing = true;
        
        const feeMonth = $('input[name="fee_month"]').val();
        const issueDate = $('input[name="issue_date"]').val();
        const dueDate = $('input[name="due_date"]').val();
        const feeTypeIds = [];
        
        $('input[name="fee_type_ids[]"]:checked').each(function() {
            feeTypeIds.push($(this).val());
        });
        
        const params = new URLSearchParams();
        params.append('fee_month', feeMonth);
        params.append('issue_date', issueDate);
        params.append('due_date', dueDate);
        feeTypeIds.forEach(id => params.append('fee_type_ids[]', id));
        
        const streamUrl = '<?= base_url('admin/fee-chalan/bulk_chalan_stream') ?>?' + params.toString();
        
        $('#progressModal').modal('show');
        $('#progressBar').css('width', '0%').text('0%');
        $('#totalStudents').text('0');
        $('#successCount').text('0');
        $('#skippedCount').text('0');
        $('#currentStudent').text('Initializing...');
        
        eventSource = new EventSource(streamUrl);
        
        eventSource.onmessage = function(e) {
            const data = JSON.parse(e.data);
            
            if (data.type === 'progress') {
                const percent = Math.round((data.processed / data.total) * 100);
                $('#progressBar').css('width', percent + '%').text(percent + '%');
                $('#totalStudents').text(data.total);
                $('#successCount').text(data.success);
                $('#skippedCount').text(data.skipped);
                $('#currentStudent').text(`Processing student ${data.processed} of ${data.total}${data.current_student ? ` (ID: ${data.current_student})` : ''}`);
            } 
            else if (data.type === 'complete') {
                eventSource.close();
                isProcessing = false;
                
                $('#progressBar').css('width', '100%').text('100%').removeClass('progress-bar-animated').addClass('bg-success');
                $('#currentStudent').html('<i class="fas fa-check-circle text-success"></i> Generation completed!');
                
                setTimeout(() => {
                    $('#progressModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Generation Complete!',
                        html: `Generated ${data.success} chalans successfully<br>Skipped: ${data.skipped}`,
                        timer: 3000,
                        showConfirmButton: false
                    });
                    setTimeout(() => location.reload(), 3000);
                }, 2000);
            } 
            else if (data.type === 'error') {
                eventSource.close();
                isProcessing = false;
                
                $('#progressModal').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error Occurred',
                    text: data.message
                });
            }
        };
        
        eventSource.onerror = function() {
            if (!cancelRequested) {
                eventSource.close();
                isProcessing = false;
                $('#progressModal').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'Failed to maintain connection with the server'
                });
            }
        };
    }
    
    // Cancel generation
    $('#cancelProgressBtn').click(function() {
        cancelRequested = true;
        if (eventSource) {
            eventSource.close();
        }
        $('#progressModal').modal('hide');
        isProcessing = false;
    });
    
    // Delete single chalan
    $('.delete-chalan').click(function() {
        const chalanId = $(this).data('id');
        const invoiceNo = $(this).data('invoice');
        const studentName = $(this).data('name');
        
        Swal.fire({
            title: 'Delete Chalan?',
            html: `Are you sure you want to delete chalan for<br><strong>${studentName}</strong><br>Invoice: ${invoiceNo}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteChalan(chalanId, $(this).closest('tr'));
            }
        });
    });

    // Function to delete single chalan
    function deleteChalan(chalanId, rowElement) {
        $.ajax({
            url: '<?= base_url('admin/fee-chalan/delete') ?>',
            type: 'POST',
            data: { chalan_id: chalanId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    rowElement.fadeOut(300, function() {
                        $(this).remove();
                        updateTodayChalansCount();
                        if ($('#todayChalansList tr').length === 0) {
                            setTimeout(() => location.reload(), 500);
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to delete chalan: ' + (xhr.responseText || error)
                });
            }
        });
    }

    // Delete all today's chalans
    $('#deleteAllTodayBtn').click(function() {
        const total = $(this).data('total');
        
        Swal.fire({
            title: 'Delete All Chalans?',
            html: `
                <div class="text-left">
                    <p>You are about to delete <strong>${total}</strong> chalans.</p>
                    <p class="text-warning">This operation may take 30-60 seconds.</p>
                    <p class="text-danger mt-2">This action cannot be undone!</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete all!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting Chalans',
                    html: `
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p>Deleting <strong>${total}</strong> chalans...</p>
                            <p class="text-muted small">Please wait, this may take a moment</p>
                        </div>
                    `,
                    allowOutsideClick: false,
                    showConfirmButton: false
                });
                
                $.ajax({
                    url: '<?= base_url('admin/fee-chalan/delete-all-today') ?>',
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                html: `<p>Successfully deleted <strong>${response.deleted_count || total}</strong> chalans</p>`,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else if (response.status === 'error') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to delete chalans. Please try again.'
                        });
                    }
                });
            }
        });
    });

    // Update badge count and footer
    function updateTodayChalansCount() {
        const remainingCount = $('#todayChalansList tr').length;
        $('.badge-success').text(remainingCount);
        $('#deleteAllTodayBtn').data('total', remainingCount);
        $('#deleteAllTodayBtn').html(`<i class="fas fa-trash-alt mr-1"></i>Delete All (${remainingCount})`);
        $('.card-footer .text-muted.small').html(`<i class="fas fa-info-circle mr-1"></i>Total: ${remainingCount} chalans`);
        
        if (remainingCount === 0) {
            $('#todayChalansList').closest('.card-body').html(`
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No chalans generated today</p>
                </div>
            `);
            $('.card-footer').remove();
            setTimeout(() => location.reload(), 500);
        }
    }
    
    // Save header message
    $("#btn_h_msg").click(function() {
        const chalan_h_msg = $('#chalan_h_msg').val();
        $.post('<?= base_url('admin/fee_chalan/updateChalanSetting') ?>', { chalan_h_msg }, function(res) {
            const json = $.parseJSON(res);
            toastr[json.success ? 'success' : 'error'](json.msg);
        });
    });
    
    // Save footer message
    $("#btn_f_msg").click(function() {
        const chalan_f_msg = $('#chalan_f_msg').val();
        $.post('<?= base_url('admin/fee_chalan/updateChalanSetting') ?>', { chalan_f_msg }, function(res) {
            const json = $.parseJSON(res);
            toastr[json.success ? 'success' : 'error'](json.msg);
        });
    });
    
    // Save fine amount
    $("#btn_late_fee").click(function() {
        const late_fee_fine = $('#late_fee_fine').val();
        $.post('<?= base_url('admin/fee_chalan/updateChalanSetting') ?>', { late_fee_fine }, function(res) {
            const json = $.parseJSON(res);
            toastr[json.success ? 'success' : 'error'](json.msg);
        });
    });
    
    // Initial count
    updateSelectedCount();
});
</script>

<style>

  /* Progress bar styling */
.progress {
    border-radius: 10px;
    background-color: #f0f0f0;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.3s ease;
    line-height: 25px;
    font-weight: bold;
}

.progress-bar-striped {
    background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent);
    background-size: 1rem 1rem;
}

.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}

@keyframes progress-bar-stripes {
    from { background-position: 1rem 0; }
    to { background-position: 0 0; }
}
/* Smooth fade out animation */
.fade-out {
    animation: fadeOut 0.3s ease-out forwards;
}

@keyframes fadeOut {
    0% {
        opacity: 1;
        transform: translateX(0);
    }
    100% {
        opacity: 0;
        transform: translateX(-20px);
        display: none;
    }
}

  /* Admission Sections Styles */
.bg-success-light {
    background-color: #d4edda;
}

.bg-secondary-light {
    background-color: #e9ecef;
}

.table-success {
    background-color: #f0f9f0 !important;
}

/* New Admission Badge Animation */
@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.05); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
}

.badge-success {
    animation: pulse 1s ease-in-out 3;
}

/* Section Separators */
.border-bottom {
    border-bottom: 2px solid !important;
}

.border-success {
    border-bottom-color: #28a745 !important;
}

.border-secondary {
    border-bottom-color: #6c757d !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .table-responsive {
        max-height: 400px !important;
    }
}

  /* Floating Action Checkboxes */
.floating-checkbox-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.floating-checkbox {
    position: relative;
}

.float-checkbox {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.float-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: white;
    border-radius: 40px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    font-weight: 500;
    color: #6c757d;
    border: 2px solid transparent;
}

.float-checkbox:checked + .float-label {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    transform: translateY(-3px);
}

.float-label:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
}
.info-box {
  margin-bottom: 0;
  border-radius: 8px;
}
.info-box-icon {
  border-radius: 8px 0 0 8px;
}
.table td, .table th {
  vertical-align: middle;
}
.delete-chalan:hover {
  transform: scale(1.1);
  transition: transform 0.2s;
}
/* Toggle button styles */
.btn-group-toggle .btn {
  border-radius: 20px;
  padding: 6px 15px;
  transition: all 0.3s ease;
}
.btn-group-toggle .btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
.btn-group-toggle .btn.active {
  box-shadow: inset 0 0 0 2px rgba(255,255,255,0.5);
}
</style>

<?= $this->endSection() ?>