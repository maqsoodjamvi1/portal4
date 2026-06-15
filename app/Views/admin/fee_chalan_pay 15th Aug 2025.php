<?php echo $this->extend('layouts/admin_template') ?>
<?php echo $this->section('content') ?>

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
    
    [data-bs-toggle="tooltip"] {
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
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-money-bill-wave me-2"></i>Fee Payments</h1>
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
                                <label data-bs-toggle="tooltip" title="Payment date"><i class="far fa-calendar-alt me-1"></i> Date</label>
                                <div class="input-group date" id="datepicker2">
                                    <input type="text" id="datePaid" class="form-control" 
                                        placeholder="Date" value="<?= date('Y-m-d') ?>"/>
                                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label data-bs-toggle="tooltip" title="Search by name or ID"><i class="fas fa-search me-1"></i> Student</label>
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
                            <span><i class="fas fa-users me-1"></i> Family</span>
                            <div class="text-end small">
                                <span data-bs-toggle="tooltip" title="Today's payments"><i class="far fa-calendar-day me-1"></i> <span id="todayPaidAmount">0</span></span>
                                <span class="ms-2" data-bs-toggle="tooltip" title="This month's payments"><i class="far fa-calendar me-1"></i> <span id="monthPaidAmount">0</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">  
                                <thead>
                                    <tr>
                                        <th data-bs-toggle="tooltip" title="Student">Std</th>
                                        <th data-bs-toggle="tooltip" title="Fee details">Fee</th>
                                        <th data-bs-toggle="tooltip" title="Amount">Amt</th>
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
                            <strong><i class="fas fa-shopping-basket me-1"></i> Pool</strong>
                            <span class="badge text-bg-light"><span id="poolItemCount">0</span> items</span>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-sm mb-2" id="paymentPoolTable">
                                <thead>
                                    <tr>
                                        <th data-bs-toggle="tooltip" title="Student">Std</th>
                                        <th data-bs-toggle="tooltip" title="Fee type">Fee</th>
                                        <th data-bs-toggle="tooltip" title="Amount">Amt</th>
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
                            <button class="btn btn-outline-danger btn-sm" id="clearPoolBtn" onclick="clearFeePool()" style="display: none;">
                                <i class="fas fa-trash-alt"></i>
                            </button>

                            <div class="text-end">
                                <span class="fw-bold">Rs <span id="poolTotalAmount">0.00</span></span>
                                <button id="confirmPaymentBtn" class="btn btn-success btn-sm ms-2" style="display:none;">
                                    <i class="fas fa-check"></i> Pay
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Fee Modal -->
    <div class="modal fade" id="editStudentFeeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white py-2">
                    <h5 class="modal-title"><i class="fas fa-edit me-1"></i> Edit Fees</h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-2">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Current</th>
                                <th>New</th>
                            </tr>
                        </thead>
                        <tbody id="studentFeeEditBody"></tbody>
                    </table>
                </div>
                <div class="modal-footer py-1">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fas fa-times"></i></button>
                    <button class="btn btn-success btn-sm" onclick="saveUpdatedStudentFees()"><i class="fas fa-save"></i></button>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->include('admin/pay_fee_modal') ?>
<?= $this->include('admin/fee_scripts') ?>

<script>
$(function () {
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>

<?php echo $this->endSection() ?>