<?php echo $this->extend('layouts/admin_template') ?>
<?php echo $this->section('content') ?>

<meta id="csrf-meta-pay-chalan" name="<?= esc(csrf_token()) ?>" content="<?= esc(csrf_hash()) ?>">

<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />
<link rel="stylesheet" href="<?= base_url('assets/plugins/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css'); ?>">

<style>
    .fee-container {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .fee-table-container {
        flex: 0 0 60%;
        min-width: 0;
    }
    
    .fee-history-container {
        flex: 0 0 38%;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .student-card {
        border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 15px;
        border: 1px solid #eaeaea;
    }
    
    .student-header {
        background: linear-gradient(135deg, #367fa9 0%, #2c6a8f 100%);
        color: white;
        padding: 10px 12px;
        border-radius: 6px 6px 0 0;
        font-weight: 600;
    }
    
    .profile-photo {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(255,255,255,0.8);
    }
    
    .fee-btn {
        width: 120px;
        height: 70px;
        margin: 4px;
        display: inline-flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        border-radius: 6px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    
    .fee-btn .badge {
        position: absolute;
        top: -6px;
        right: -6px;
        font-size: 0.6rem;
        padding: 3px 5px;
    }
    
    .empty-state {
        text-align: center;
        padding: 30px 15px;
        background: #f8f9fa;
        border-radius: 6px;
        border: 2px dashed #dee2e6;
    }
    
    .payment-pool-card {
        border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .table-responsive {
        border-radius: 6px;
    }
    
    .table th, .table td {
        padding: 0.4rem;
    }
    
    .status-badge {
        font-size: 0.7rem;
        padding: 3px 6px;
    }
    
    [data-toggle="tooltip"] {
        cursor: pointer;
        border-bottom: 1px dotted #999;
    }
    
    @media (max-width: 992px) {
        .fee-container {
            flex-direction: column;
        }
        
        .fee-table-container,
        .fee-history-container {
            flex: 1 1 100%;
        }
    }


    .btn-icon-only{
  width:36px;height:36px;border-radius:50%;
  padding:0;display:inline-flex;align-items:center;justify-content:center;
}
.btn-icon-only i{font-size:16px;}

.custom-control-input:focus ~ .custom-control-label::before {
    box-shadow: none !important;
    outline: none !important;
}

/* Also remove dotted outline on label itself */
.custom-control-label:focus {
    outline: none !important;
}

/* Family payment history (grouped by paid date, non-tabular) */
#familyHistoryContainer .family-payment-history .fph-day-card {
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e3e6ea !important;
}
#familyHistoryContainer .fph-day-header {
    background: linear-gradient(135deg, #f8f9fb 0%, #eef1f5 100%);
    border-bottom: 1px solid #dee2e6;
}
#familyHistoryContainer .fph-day-title {
    font-size: 1.05rem;
    color: #2c3e50;
}
#familyHistoryContainer .fph-day-total-pill {
    font-size: 0.9rem;
    padding: 0.35em 0.85em;
}
#familyHistoryContainer .fph-day-body {
    background: #fff;
}
#familyHistoryContainer .fph-day-fee-list {
    padding: 0.5rem 1rem 0.75rem;
    margin: 0;
}
#familyHistoryContainer .fph-inline-student {
    font-weight: 700;
    color: #2c3e50;
    font-size: 0.88rem;
}
#familyHistoryContainer .fph-inline-student::after {
    content: "·";
    margin: 0 0.4rem;
    color: #cfd4d8;
    font-weight: 400;
}
#familyHistoryContainer .fph-fee-item {
    padding: 0.45rem 0;
    border-top: 1px solid #f0f2f4;
    font-size: 0.875rem;
    gap: 0.5rem;
}
#familyHistoryContainer .fph-fee-item:first-child {
    border-top: none;
    padding-top: 0.15rem;
}
#familyHistoryContainer .fph-fee-item-continue .fph-fee-item-left {
    padding-left: 0.65rem;
    margin-left: 0.35rem;
    border-left: 3px solid #e8ecef;
}
#familyHistoryContainer .fph-fee-type-pill {
    display: inline-block;
    background: #e8f4fc;
    color: #1a6fa8;
    font-weight: 600;
    font-size: 0.78rem;
    padding: 0.12rem 0.45rem;
    border-radius: 4px;
    margin-right: 0.35rem;
}
#familyHistoryContainer .fph-fee-period::before {
    content: "·";
    margin: 0 0.35rem;
    color: #ccc;
}
#familyHistoryContainer .fph-fee-inv::before {
    content: "·";
    margin: 0 0.35rem;
    color: #ccc;
}
#familyHistoryContainer .fph-fee-when {
    font-size: 0.75rem;
    color: #888;
}
#familyHistoryContainer .fph-fee-when::before {
    content: "·";
    margin: 0 0.35rem;
    color: #ccc;
}
#familyHistoryContainer .fph-fee-amt-note-wrap {
    width: 100%;
    flex-basis: 100%;
    margin-top: 0.15rem;
}
#familyHistoryContainer .fph-fee-amt-note {
    font-size: 0.72rem;
    color: #6c757d;
}
#familyHistoryContainer .fph-summary {
    border-radius: 8px;
}
</style>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2 align-items-center">
      <div class="col-sm-6 d-flex align-items-center flex-wrap">
        <h1 class="mb-0 mr-3">
          <i class="fas fa-money-bill-wave mr-2"></i>Fee Payments
        </h1>

        <!-- This Month -->
        <span class="badge badge-success mr-2"
              data-toggle="tooltip" title="Net paid this month">
          <i class="fas fa-calendar-check mr-1"></i>
          Rs <?= number_format($paidTotals['month'] ?? 0, 0) ?>
        </span>

        <!-- Today -->
        <span class="badge badge-primary"
              data-toggle="tooltip" title="Net paid today">
          <i class="fas fa-clock mr-1"></i>
          Rs <?= number_format($paidTotals['today'] ?? 0, 0) ?>
        </span>
      </div>
    </div>
  </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div id="status-message" class="alert alert-success" style="position:fixed; top:15%; left:50%; transform:translate(-50%, -50%); display:none; z-index:9999;"></div>
        
        <div class="fee-container">
            <!-- Left Section - Search and Student Card -->
            <div class="fee-table-container">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="form-group row align-items-end mb-2">
                            <div class="col-md-4">
                                <label data-toggle="tooltip" title="Payment date"><i class="far fa-calendar-alt mr-1"></i> Date</label>
                                <div class="input-group date" id="datepicker2">
                                    <input type="text" id="datePaid" class="form-control" 
                                        placeholder="Date" value="<?= date('Y-m-d') ?>"/>
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label data-toggle="tooltip" title="Search by name or ID"><i class="fas fa-search mr-1"></i> Student</label>
                                <select class="form-control select2" id="student_id" style="width: 100%">
                                    <option value="0">Search student...</option>
                                </select>
                            </div>
                        </div>

                        <div id="student-card-container">
                            <div class="empty-state py-4">
                                <i class="fas fa-search fa-2x mb-2 text-muted"></i>
                                <p class="text-muted mb-0">Search for a student to begin</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Section - History and Pool -->
            <div class="fee-history-container">
                <!-- Family Summary -->
                <div class="card shadow-sm" id="parentSummary" style="display: none;">
                    <div class="card-header bg-primary text-white py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-users mr-1"></i> Family</span>
                            <div class="text-right small">
                                <span data-toggle="tooltip" title="Today's payments"><i class="far fa-calendar-day mr-1"></i> <span id="todayPaidAmount">0</span></span>
                                <span class="ml-2" data-toggle="tooltip" title="This month's payments"><i class="far fa-calendar mr-1"></i> <span id="monthPaidAmount">0</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">  
                                <thead>
                                    <tr>
                                        <th data-toggle="tooltip" title="Student">Std</th>
                                        <th data-toggle="tooltip" title="Fee details">Fee</th>
                                        <th data-toggle="tooltip" title="Amount">Amt</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="paidFeeTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payment Pool -->
                <div class="card payment-pool-card" id="paymentPoolCard" style="display: none;">
                    <div class="card-header py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong><i class="fas fa-shopping-basket mr-1"></i> Pool</strong>
                            <span class="badge badge-light"><span id="poolItemCount">0</span> items</span>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-sm mb-2" id="paymentPoolTable">
                                <thead>
                                    <tr>
                                        <th data-toggle="tooltip" title="Student">Std</th>
                                        <th data-toggle="tooltip" title="Fee type">Fee</th>
                                        <th data-toggle="tooltip" title="Amount">Amt</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-2">Pool is empty</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <button class="btn btn-outline-danger btn-xs" id="clearPoolBtn" onclick="clearFeePool()" style="display: none;">
                                <i class="fas fa-trash-alt"></i>
                            </button>

                            <div class="text-right">
                                <span class="font-weight-bold">Rs <span id="poolTotalAmount">0.00</span></span>
                                <button id="confirmPaymentBtn" class="btn btn-success btn-sm ml-2" style="display:none;">
                                    <i class="fas fa-check"></i> Pay
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Family paid-fee history (modal) -->
<div class="modal fade" id="familyFeeHistoryModal" tabindex="-1" role="dialog" aria-labelledby="familyFeeHistoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header py-2 bg-light">
        <h5 class="modal-title mb-0" id="familyFeeHistoryModalLabel"><i class="fas fa-history mr-1"></i> Family payment history</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body p-2">
        <div class="form-row align-items-end mb-2">
          <div class="col-sm-4 col-md-3">
            <label class="small text-muted mb-0" for="fhStart">From</label>
            <input type="date" class="form-control form-control-sm" id="fhStart" />
          </div>
          <div class="col-sm-4 col-md-3">
            <label class="small text-muted mb-0" for="fhEnd">To</label>
            <input type="date" class="form-control form-control-sm" id="fhEnd" />
          </div>
          <div class="col-sm-4 col-md-auto">
            <button type="button" class="btn btn-primary btn-sm" id="fhApplyFilter"><i class="fas fa-filter mr-1"></i> Apply</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="fhClearFilter">Clear</button>
          </div>
        </div>
        <div id="familyHistoryContainer" class="border rounded bg-white"></div>
      </div>
    </div>
  </div>
</div>

    
<!-- Edit Fee Modal -->
<div class="modal fade" id="editStudentFeeModal" tabindex="-1" role="dialog" aria-labelledby="editStudentFeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document"><!-- modal-lg is fine too -->
    <div class="modal-content">
      <div class="modal-header bg-primary text-white py-2">
        <h5 class="modal-title" id="editStudentFeeModalLabel">
          <i class="fas fa-edit mr-1"></i> Edit Monthly Fees
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
      </div>

      <div class="modal-body p-2">
        <!-- Totals / Summary -->
        <div class="row text-center mx-1 mb-2" style="gap:8px;">
          <div class="col bg-light rounded py-2">
            <div class="small text-muted">Total Class Fee</div>
            <div id="sumClassFee" class="font-weight-bold">Rs 0.00</div>
          </div>
          <div class="col bg-light rounded py-2">
            <div class="small text-muted">Total Current Fee</div>
            <div id="sumCurrentFee" class="font-weight-bold">Rs 0.00</div>
          </div>
          <div class="col bg-light rounded py-2">
            <div class="small text-muted">Total New Fee</div>
            <div id="sumNewFee" class="font-weight-bold">Rs 0.00</div>
          </div>
          <div class="col bg-light rounded py-2">
            <div class="small text-muted">Δ (New - Current)</div>
            <div id="sumDelta" class="font-weight-bold">Rs 0.00</div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
              <tr>
                <th style="width:56px;">S#</th>
                <th>Student</th>
                <th>Class</th>
                <th class="text-right">Class Fee</th>
                <th class="text-right">Current Fee</th>
                <th style="min-width:140px;">New Fee</th>
              </tr>
            </thead>
            <tbody id="studentFeeEditBody">
              <!-- rows injected by JS -->
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer py-1">
        <button class="btn btn-secondary btn-sm" data-dismiss="modal">
          <i class="fas fa-times"></i>
        </button>
        <button id="saveFeeChanges" class="btn btn-success btn-sm">
          <i class="fas fa-save"></i>
        </button>
      </div>
    </div>
  </div>
</div>
</section>

<?= $this->include('admin/pay_fee_modal') ?>
<?= $this->include('admin/fee_scripts') ?>
<?= $this->include('admin/chalanview/partials/chalan_edit_modal_shared', [
    'csrfMetaId' => 'csrf-meta-pay-chalan',
    'chalanEditAfterSave' => 'refresh_pay_card',
]) ?>

<script>
  $(function(){ $('[data-toggle="tooltip"]').tooltip({container:'body'}); });
</script>

<?php echo $this->endSection() ?>