    <?= $this->extend('layouts/admin_template') ?>
    <?= $this->section('content') ?>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        <i class="fas fa-money-bill-wave mr-2"></i>
                        Configure Fee Amount
                        <?php if (!empty($is_first_time)): ?>
                            <span class="badge badge-success ml-2">System Setup Step 10/10</span>
                        <?php endif; ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Fee Amount</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="fee-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="fee-config-tab" data-toggle="pill" href="#fee-config" role="tab">
                                <i class="fas fa-cog mr-1"></i> Fee Configuration
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="info-tab" data-toggle="pill" href="#info" role="tab">
                                <i class="fas fa-info-circle mr-1"></i> Information
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="fee-tabsContent">
                        <div class="tab-pane fade show active" id="fee-config" role="tabpanel">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="info-box bg-light">
                                        <span class="info-box-icon bg-primary"><i class="fas fa-calendar-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Current Session</span>
                                            <span class="info-box-number">
                                                <?= esc($current_academic_sessioninfo->session_name) ?>
                                            </span>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" style="width: 70%"></div>
                                            </div>
                                            <span class="progress-description">
                                                <?= date('M d, Y', strtotime($current_academic_sessioninfo->start_date)) ?> 
                                                - 
                                                <?= date('M d, Y', strtotime($current_academic_sessioninfo->end_date)) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-box bg-light">
                                        <span class="info-box-icon bg-success"><i class="fas fa-money-check-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Max Monthly Fee Limit</span>
                                            <span class="info-box-number">
                                                <?= $max_fee ? number_format($max_fee) : 'Not set' ?>
                                            </span>
                                            <div class="progress">
                                                <div class="progress-bar bg-success" style="width: 50%"></div>
                                            </div>
                                            <span class="progress-description">
                                                Monthly fees cannot exceed this amount
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            
                           <?= form_open(base_url('admin/fee_amount/save'), ['id' => 'fee-amount-form']) ?>
    <?= form_hidden('session_id', (string)$session_id) ?>

    <?php
    // Use the campus_flags data passed from controller instead of querying DB
    $show_flag_selector = ($campus_flags->daycare_flag == 1 && $campus_flags->boarding_flag == 1);

    // Determine default flag based on campus flags
    if ($campus_flags->daycare_flag == 1 && $campus_flags->boarding_flag == 1) {
        $default_flag = 1; // Both flags set, default to daycare
    } elseif ($campus_flags->daycare_flag == 1) {
        $default_flag = 1; // Only daycare
    } elseif ($campus_flags->boarding_flag == 1) {
        $default_flag = 2; // Only boarding
    } else {
        $default_flag = 0; // Neither flag set
    }
    ?>

    <?php if ($show_flag_selector): ?>
    <div class="form-group row mb-4">
        <label class="col-form-label col-md-2">Fee Type:</label>
        <div class="col-md-10">
            <div class="form-check form-check-inline">
               <input type="radio" name="fee_flag" value="1" <?= $fee_flag == 1 ? 'checked' : '' ?>> Daycare
                <label class="form-check-label" for="daycare-fee">Daycare Fee</label>
            </div>
            <div class="form-check form-check-inline">
       <input type="radio" name="fee_flag" value="2" <?= $fee_flag == 2 ? 'checked' : '' ?>> Boarding
                <label class="form-check-label" for="boarding-fee">Boarding Fee</label>
            </div>
        </div>
    </div>
    <?php else: ?>
        <input type="hidden" name="fee_flag" value="<?= $default_flag ?>">
    <?php endif; ?>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th width="20%" class="align-middle">Class</th>
                                            <?php foreach ($fee_type_info as $fee): ?>
                                                <th class="text-center align-middle <?= $fee->is_monthly_fee ? 'bg-primary-light' : '' ?>">
        <div class="d-flex flex-column align-items-center">
            <span><?= esc($fee->fee_type_name) ?></span>
            <?php if ($fee->is_monthly_fee): ?>
                <small class="badge badge-pill badge-warning">Monthly Fee</small>
            <?php endif; ?>

            <input 
                type="number" 
                class="form-control form-control-sm text-center repeat-input mt-1"
                data-fee-type="<?= $fee->fee_type_id ?>"
                placeholder="Repeat to all"
                style="width: 100px;"
            >
            <small class="text-muted">Apply to all</small>
        </div>
    </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($classesinfo as $class): ?>
                                            <tr>
                                                <th class="font-weight-bold">
                                                    <i class="fas fa-graduation-cap mr-2 text-primary"></i>
                                                    <?= esc($class->class_name) ?>
                                                </th>
                                                <?php foreach ($fee_type_info as $fee): 
                                                    $amountKey = $fee->fee_type_id . '_' . $class->class_id . '_amount';
                $amountIdKey = $fee->fee_type_id . '_' . $class->class_id . '_amount_id';
                $existing = $prev_fees[$fee->fee_type_id][$class->class_id] ?? 0;
                $current_amount = $current_fees[$fee->fee_type_id][$class->class_id] ?? '';
                $amount_id = $amount_ids[$fee->fee_type_id][$class->class_id] ?? 0; 


                                                    $existingRecord = db_connect()->table('fee_amount')->where([
                                                        'session_id' => $session_id,
                                                        'campus_id' => session()->get('member_campusid'),
                                                        'fee_type_id' => $fee->fee_type_id,
                                                        'class_id' => $class->class_id,
                                                    ])->get()->getRow();
                                                ?>
                                                <td class="text-center <?= $fee->is_monthly_fee ? 'bg-primary-light' : '' ?>">
                <input type="hidden" name="fee_type_id[]" value="<?= $fee->fee_type_id ?>">
                <input type="hidden" name="class_id[]" value="<?= $class->class_id ?>">
                <input type="hidden" name="<?= $amountIdKey ?>" value="<?= $amount_id ?>">
                                                    
                                                    <div class="form-group mb-1">
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">
                                                                    <i class="fas fa-rupee-sign"></i>
                                                                </span>
                                                            </div>
                                                            <input 
    type="number" 
    class="form-control text-right <?= $fee->is_monthly_fee ? 'monthly-fee' : '' ?>"
    name="ftv<?= $fee->fee_type_id ?>_ci<?= $class->class_id ?>_amount"
    value="<?= esc((string)$current_amount) ?>"
    min="0"
    step="1"
    <?= ($fee->is_monthly_fee && $max_fee) ? "max='{$max_fee}'" : '' ?>
    placeholder="Enter amount"
>
                                                        </div>
                                                    </div>
                                                   
        <?php if (isset($prev_fees[$fee->fee_type_id][$class->class_id])): ?>
            <div class="text-center small text-muted">
                <i class="fas fa-history mr-1"></i> 
                Previous: <?= number_format($prev_fees[$fee->fee_type_id][$class->class_id]) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($fee->is_monthly_fee && $max_fee): ?>
            <div class="progress progress-xs mt-1">
                <?php 
                    $percent = $current_amount ? 
                        min(100, ($current_amount / $max_fee) * 100) : 
                        0;
                    $progressClass = ($percent > 90) ? 'bg-danger' : (($percent > 75) ? 'bg-warning' : 'bg-success');
                ?>
                <div 
                    class="progress-bar <?= $progressClass ?>" 
                    style="width: <?= $percent ?>%"
                    role="progressbar" 
                    aria-valuenow="<?= $percent ?>" 
                    aria-valuemin="0" 
                    aria-valuemax="100"
                >
                </div>
            </div>
        <?php endif; ?>
    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="<?= count($fee_type_info) + 1 ?>" class="text-right">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-save mr-1"></i> Save All Fee Amounts
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <?= form_close() ?>
                        </div>
                        
                        <div class="tab-pane fade" id="info" role="tabpanel">
                            <div class="callout callout-info">
                                <h5><i class="fas fa-info-circle mr-2"></i> Fee Configuration Information</h5>
                                <p>
                                    This page allows you to configure the fee amounts for each class and fee type. 
                                    Please note the following:
                                </p>
                                <ul>
                                    <li>
                                        <i class="fas fa-circle text-primary mr-1"></i> 
                                        <strong>Monthly Fees</strong> (highlighted in blue) have special constraints
                                    </li>
                                    <li>
                                        <i class="fas fa-exclamation-triangle text-warning mr-1"></i> 
                                        Monthly fees cannot exceed the maximum fee limit set for your campus
                                    </li>
                                    <li>
                                        <i class="fas fa-history text-secondary mr-1"></i> 
                                        Previous session amounts are shown for reference
                                    </li>
                                    <li>
                                        <i class="fas fa-chart-bar text-success mr-1"></i> 
                                        Progress bars indicate how close monthly fees are to the maximum limit
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-primary">
                                            <h3 class="card-title">
                                                <i class="fas fa-lightbulb mr-2"></i> Tips & Best Practices
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <ul class="fa-ul">
                                                <li>
                                                    <span class="fa-li"><i class="fas fa-check-circle text-success"></i></span>
                                                    Review previous session amounts before making changes
                                                </li>
                                                <li>
                                                    <span class="fa-li"><i class="fas fa-check-circle text-success"></i></span>
                                                    Set monthly fees first as they have the most constraints
                                                </li>
                                                <li>
                                                    <span class="fa-li"><i class="fas fa-check-circle text-success"></i></span>
                                                    Use the progress bars to visualize fee limits
                                                </li>
                                                <li>
                                                    <span class="fa-li"><i class="fas fa-check-circle text-success"></i></span>
                                                    Remember to save your changes before leaving the page
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-success">
                                            <h3 class="card-title">
                                                <i class="fas fa-exclamation-triangle mr-2"></i> Important Notes
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <ul class="fa-ul">
                                                <li>
                                                    <span class="fa-li"><i class="fas fa-exclamation-circle text-danger"></i></span>
                                                    Changes affect all students in the specified class
                                                </li>
                                                <li>
                                                    <span class="fa-li"><i class="fas fa-exclamation-circle text-danger"></i></span>
                                                    Fee amounts cannot be changed after invoices are generated
                                                </li>
                                                <li>
                                                    <span class="fa-li"><i class="fas fa-exclamation-circle text-danger"></i></span>
                                                    Monthly fee limits are set by your billing plan
                                                </li>
                                                <li>
                                                    <span class="fa-li"><i class="fas fa-exclamation-circle text-danger"></i></span>
                                                    Changes are applied immediately after saving
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-clock mr-2"></i>
                                
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-default mr-2">
                                <i class="fas fa-download mr-1"></i> Export
                            </button>
                            <button type="button" class="btn btn-primary">
                                <i class="fas fa-print mr-1"></i> Print Preview
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


<script>
$(document).ready(function () {
    // Handle fee_flag radio change
    $('input[name="fee_flag"]').change(function() {
        const flag = $(this).val();
        const url = new URL(window.location.href);
        url.searchParams.set('force_flag', flag);
        window.location.href = url.toString(); 
    });

    // Repeat input handling
    $('.repeat-input').on('input', function () {
        const feeTypeId = $(this).data('fee-type');
        const valueToSet = $(this).val();

        $(`input[name^='ftv${feeTypeId}_']`).each(function () {
            $(this).val(valueToSet).trigger('input');
        });
    });

    // Real-time validation
    $('.monthly-fee').on('input', function () {
        const max = parseFloat($(this).attr('max'));
        const value = parseFloat($(this).val()) || 0;

        if (value > max) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Max fee is ' + max + '</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    // AJAX submission when the save button is clicked
    $('#fee-amount-form').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnHtml = submitBtn.html();

        // Validate monthly fees
        $('.monthly-fee').removeClass('is-invalid').next('.invalid-feedback').remove();
        
        let valid = true;
        $('.monthly-fee').each(function () {
            const max = parseFloat($(this).attr('max'));
            const value = parseFloat($(this).val()) || 0;

            if (value > max) {
                $(this).addClass('is-invalid');
                $(this).after('<div class="invalid-feedback">Max fee is ' + max + '</div>');
                valid = false;
            }
        });

        if (!valid) {
            toastr.error('Some monthly fees exceed the maximum limit');
            return;
        }

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    toastr.success(res.msg);
                    
                    // Check if we have redirect_url from server
                    if (res.redirect_url) {
                        if (res.has_students) {
                            toastr.info('Fee structure saved. Redirecting to dashboard...');
                        } else {
                            toastr.info('Fee structure saved. No students found. Redirecting to add students page...');
                        }
                        setTimeout(function() {
                            window.location.href = res.redirect_url;
                        }, 1500);
                    } else {
                        // Fallback: reload the page if no redirect_url
                        setTimeout(() => location.reload(), 1200);
                    }
                } else {
                    toastr.error(res.msg);
                    submitBtn.prop('disabled', false).html(originalBtnHtml);
                }
            },
            error: function(xhr, status, error) {
                console.error('Save error:', error);
                toastr.error('An error occurred while saving. Please try again.');
                submitBtn.prop('disabled', false).html(originalBtnHtml);
            }
        });
    });
});
</script>
    <style>
      .table td:first-child,
    .table th:first-child {
        border-left: 3px solid #007bff;
    }
        .bg-primary-light {
            background-color: #e3f2fd !important;
        }
        .progress-xs {
            height: 5px;
        }
        .table th.bg-primary-light {
            border-bottom: 2px solid #2196F3;
        }
        .table td.bg-primary-light {
            background-color: #f5fbff;
        }
        .fa-ul {
            margin-left: 1.5rem;
        }
        .callout-info {
            border-left-color: #117a8b;
        }
        .input-group-text {
            background-color: #e9ecef;
        }
    </style>
    <?= $this->endSection() ?>