<?php echo $this->extend('layouts/admin_template'); ?>
<?php echo $this->section('content'); ?>

<style>
/* Professional Salary Slip Styles - A4 Portrait Optimized */
@media print {
    @page {
        size: A4 portrait;
        margin: 0.5in;
    }
    
    body, html {
        margin: 0;
        padding: 0;
        background: white;
    }
    
    .no-print, .btn, .navbar, .main-sidebar, .breadcrumb, .content-header .btn, 
    .content-header a, .card-tools, .action-buttons, .sidebar, .main-footer,
    .modal, .modal-backdrop {
        display: none !important;
    }
    
    .content-wrapper, .main-content, .card {
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        box-shadow: none !important;
        background: white !important;
    }
    
    .salary-slip-container {
        width: 100%;
        margin: 0;
        padding: 0;
    }
    
    .salary-slip {
        border: 1px solid #ddd;
        padding: 20px;
        background: white;
    }
    
    .slip-header {
        border-bottom: 2px solid #333;
        margin-bottom: 20px;
    }
    
    .slip-footer {
        border-top: 1px solid #ddd;
        margin-top: 20px;
        padding-top: 10px;
        font-size: 10px;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    table td, table th {
        padding: 8px;
        border: 1px solid #ddd;
    }
    
    .table-borderless td, .table-borderless th {
        border: none;
    }
    
    .text-right {
        text-align: right;
    }
    
    .text-center {
        text-align: center;
    }
}

/* Screen Styles */
.salary-slip-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.salary-slip {
    background: white;
    border-radius: 8px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    padding: 30px;
}

.slip-header {
    border-bottom: 3px solid #2c3e50;
    padding-bottom: 20px;
    margin-bottom: 25px;
}

.school-logo-area {
    text-align: center;
    margin-bottom: 15px;
}

.school-logo {
    max-height: 80px;
    width: auto;
    display: inline-block;
}

.school-name {
    font-size: 24px;
    font-weight: bold;
    color: #2c3e50;
    margin: 10px 0 5px;
}

.school-address {
    font-size: 12px;
    color: #7f8c8d;
    margin: 0;
}

.slip-title {
    font-size: 20px;
    font-weight: bold;
    color: #34495e;
    margin: 15px 0 5px;
}

.slip-number {
    font-size: 14px;
    color: #7f8c8d;
}

/* Info Cards */
.info-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.info-card h6 {
    color: #2c3e50;
    margin-bottom: 10px;
    border-left: 3px solid #3498db;
    padding-left: 10px;
}

/* Tables */
.earning-table, .deduction-table {
    width: 100%;
    margin-bottom: 0;
}

.earning-table tr td, .deduction-table tr td {
    padding: 8px 0;
    border-bottom: 1px solid #ecf0f1;
}

.earning-table tr td:last-child, .deduction-table tr td:last-child {
    text-align: right;
}

.total-row {
    font-weight: bold;
    border-top: 2px solid #2c3e50;
}

/* Net Salary Box */
.net-salary-box {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    color: white;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    margin: 20px 0;
}

.net-salary-box h4 {
    margin: 0 0 5px;
    font-size: 16px;
    opacity: 0.9;
}

.net-salary-box h2 {
    margin: 0;
    font-size: 36px;
    font-weight: bold;
}

/* Attendance Summary */
.attendance-stats {
    display: flex;
    justify-content: space-around;
    margin: 15px 0;
}

.stat-item {
    text-align: center;
    flex: 1;
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
    display: block;
}

.stat-label {
    font-size: 12px;
    color: #7f8c8d;
}

/* Footer */
.slip-footer {
    margin-top: 30px;
    padding-top: 15px;
    border-top: 1px solid #ecf0f1;
    font-size: 10px;
    color: #95a5a6;
    text-align: center;
}

/* Table styles */
.info-table {
    width: 100%;
    border-collapse: collapse;
}

.info-table td {
    padding: 5px 0;
    border: none;
}

.info-table td:first-child {
    width: 40%;
    font-weight: bold;
}

.info-table td:last-child {
    width: 60%;
}
</style>

<div class="salary-slip-container">
    <div class="salary-slip">
        <!-- Header with Logo and School Info -->
        <div class="slip-header">
            <div class="school-logo-area">
                <?php
                $defaultLogo = base_url('uploads/logo_school.png');
                if (!empty($finalLogo)) {
                    $logoUrl = base_url('system-logo/' . $finalLogo);
                } else {
                    $logoUrl = $defaultLogo;
                }
                ?>
                <img src="<?php echo esc($logoUrl); ?>" 
                     alt="School Logo" 
                     class="school-logo"
                     onerror="this.onerror=null; this.src='<?php echo esc($defaultLogo); ?>';">
                
                <?php if (isset($schoolinfo) && $schoolinfo): ?>
                    <?php $schoolName = $schoolinfo->system_name ?? ($schoolinfo->school_name ?? 'School Management System'); ?>
                    <div class="school-name"><?php echo esc($schoolName); ?></div>
                    <?php if ($schoolinfo->address): ?>
                        <div class="school-address"><?php echo esc($schoolinfo->address); ?></div>
                    <?php endif; ?>
                    <?php if ($schoolinfo->phone): ?>
                        <div class="school-address">Phone: <?php echo esc($schoolinfo->phone); ?></div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <div class="slip-title">SALARY SLIP</div>
                <div class="slip-number"><?php echo date('F Y', strtotime($slip->year . '-' . $slip->month . '-01')); ?></div>
                <div class="slip-number">Slip No: <strong><?php echo $slip->slip_no; ?></strong></div>
            </div>
        </div>

        <!-- Employee Information - Two Column Layout -->
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-6">
                <div class="info-card">
                    <h6><i class="fas fa-user"></i> Employee Details</h6>
                    <table class="info-table">
                        <tr>
                            <td><strong>Employee ID:</strong></td>
                            <td><?php echo $user->id; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td><?php echo $user->first_name . ' ' . $user->last_name; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Designation:</strong></td>
                            <td><?php echo $user->designation ?? 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Joining Date:</strong></td>
                            <td><?php echo isset($user->joining_date) ? date('d-M-Y', strtotime($user->joining_date)) : 'N/A'; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-card">
                    <h6><i class="fas fa-university"></i> Payment Details</h6>
                    <table class="info-table">
                        <tr>
                            <td><strong>Bank Account:</strong></td>
                            <td><?php echo $user->account_number ?? 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Payment Method:</strong></td>
                            <td><?php echo ucfirst($slip->payment_method ?? 'Bank Transfer'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Payment Status:</strong></td>
                            <td>
                                <?php if ($slip->payment_status == 'paid'): ?>
                                    <span style="color: #27ae60; font-weight: bold;">
                                        <i class="fas fa-check-circle"></i> Paid on <?php echo date('d-M-Y', strtotime($slip->payment_date)); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #e67e22; font-weight: bold;">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                    <button type="button" class="btn btn-sm btn-success ml-2 no-print" 
                                            onclick="openPaymentModal(<?php echo $slip->slip_id; ?>, <?php echo $slip->net_salary; ?>)">
                                        <i class="fas fa-check-circle"></i> Mark as Paid
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Earnings and Deductions - Side by Side -->
        <div class="row">
            <div class="col-md-6">
                <div class="info-card">
                    <h6 style="border-left-color: #27ae60;"><i class="fas fa-plus-circle"></i> Earnings</h6>
                    <table class="earning-table">
                        <tr>
                            <td>Basic Salary</td>
                            <td><?php echo number_format($slip->basic_salary, 2); ?></td>
                        </tr>
                        <?php if ($slip->attendance_bonus > 0): ?>
                        <tr>
                            <td>Attendance Bonus</td>
                            <td><?php echo number_format($slip->attendance_bonus, 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($slip->other_bonus > 0): ?>
                        <tr>
                            <td>Other Bonus</td>
                            <td><?php echo number_format($slip->other_bonus, 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="total-row">
                            <td><strong>Total Earnings</strong></td>
                            <td><strong><?php echo number_format($slip->total_earnings, 2); ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="info-card">
                    <h6 style="border-left-color: #e74c3c;"><i class="fas fa-minus-circle"></i> Deductions</h6>
                    <table class="deduction-table">
                        <?php if ($slip->absent_deduction > 0): ?>
                        <tr>
                            <td>Absent Days Deduction</td>
                            <td><?php echo number_format($slip->absent_deduction, 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($slip->late_deduction > 0): ?>
                        <tr>
                            <td>Late Arrival Deduction</td>
                            <td><?php echo number_format($slip->late_deduction, 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($slip->security_deduction > 0): ?>
                        <tr>
                            <td>Security Deduction</td>
                            <td><?php echo number_format($slip->security_deduction, 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($slip->advance_deduction > 0): ?>
                        <tr>
                            <td>Advance Salary Deduction</td>
                            <td><?php echo number_format($slip->advance_deduction, 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="total-row">
                            <td><strong>Total Deductions</strong></td>
                            <td><strong><?php echo number_format($slip->total_deductions, 2); ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Net Salary Box -->
        <div class="net-salary-box">
            <h4>NET SALARY</h4>
            <h2><?php echo number_format($slip->net_salary, 2); ?></h2>
            <div style="font-size: 12px; margin-top: 5px;">
                <?php echo ucwords(str_replace('_', ' ', $slip->payment_status ?? 'Pending')); ?>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="info-card">
            <h6><i class="fas fa-calendar-check"></i> Attendance Summary</h6>
            <div class="attendance-stats">
                <div class="stat-item">
                    <span class="stat-value" style="color: #27ae60;"><?php echo $attendance['present']; ?></span>
                    <span class="stat-label">Present Days</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" style="color: #e74c3c;"><?php echo $attendance['absent']; ?></span>
                    <span class="stat-label">Absent Days</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" style="color: #f39c12;"><?php echo $attendance['late']; ?></span>
                    <span class="stat-label">Late Days</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" style="color: #3498db;"><?php echo $attendance['total']; ?></span>
                    <span class="stat-label">Total Days</span>
                </div>
            </div>
        </div>

        <!-- Salary Breakdown Details -->
        <div class="info-card" style="margin-top: 10px;">
            <h6><i class="fas fa-chart-line"></i> Salary Calculation Details</h6>
            <table style="width: 100%; font-size: 11px;">
                <tr>
                    <td width="25%"><strong>Basic Salary:</strong></td>
                    <td width="25%"><?php echo number_format($slip->basic_salary, 2); ?></td>
                    <td width="25%"><strong>Working Days/Month:</strong></td>
                    <td width="25%"><?php echo $slip->working_days_in_month ?? 26; ?></td>
                </tr>
                <tr>
                    <td><strong>Daily Rate:</strong></td>
                    <td><?php echo number_format($slip->daily_salary ?? ($slip->basic_salary / 26), 2); ?></td>
                    <td><strong>Unpaid Leaves:</strong></td>
                    <td><?php echo $attendance['absent']; ?></td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="slip-footer">
            <p>This is a computer-generated document. No signature required.</p>
            <p>Generated on: <?php echo date('d-M-Y H:i:s'); ?></p>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade no-print" id="paymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #27ae60; color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Process Salary Payment
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <input type="hidden" id="payment_slip_id">
                    <input type="hidden" id="payment_amount">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-money-bill-wave"></i> Payment Method <span class="text-danger">*</span></label>
                                <select id="payment_method" class="form-control" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="Bank Transfer">🏦 Bank Transfer</option>
                                    <option value="Cash">💵 Cash</option>
                                    <option value="Cheque">📝 Cheque</option>
                                    <option value="Online Transfer">💻 Online Transfer</option>
                                    <option value="JazzCash">📱 JazzCash</option>
                                    <option value="EasyPaisa">📱 EasyPaisa</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-hashtag"></i> Transaction ID / Reference No</label>
                                <input type="text" id="transaction_id" class="form-control" 
                                       placeholder="Enter transaction ID, cheque number or reference">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-calendar-alt"></i> Payment Date <span class="text-danger">*</span></label>
                                <input type="date" id="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-building"></i> Bank Name (if applicable)</label>
                                <input type="text" id="bank_name" class="form-control" placeholder="Enter bank name">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><i class="fas fa-sticky-note"></i> Payment Remarks / Notes</label>
                                <textarea id="payment_remarks" class="form-control" rows="2" 
                                          placeholder="Enter any additional remarks about this payment"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Payment Summary -->
                    <div class="payment-receipt">
                        <h6><i class="fas fa-receipt"></i> Payment Summary</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td><strong>Employee:</strong></td>
                                        <td><?php echo $user->first_name . ' ' . $user->last_name; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Salary Month:</strong></td>
                                        <td><?php echo date('F Y', strtotime($slip->year . '-' . $slip->month . '-01')); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td><strong>Net Salary:</strong></td>
                                        <td class="text-right"><strong id="display_amount"><?php echo number_format($slip->net_salary, 2); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Slip No:</strong></td>
                                        <td class="text-right"><?php echo $slip->slip_no; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" onclick="submitPayment()">
                    <i class="fas fa-check-circle"></i> Confirm Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Print and Action Buttons (Hidden in Print) -->
<div class="no-print text-center" style="margin-top: 20px; padding: 20px;">
    <button onclick="window.print()" class="btn btn-primary btn-lg">
        <i class="fas fa-print mr-2"></i> Print Salary Slip
    </button>
    <a href="<?php echo base_url('admin/users/salary/' . $user->id); ?>" class="btn btn-secondary btn-lg ml-2">
        <i class="fas fa-arrow-left mr-2"></i> Back to Employee Salary
    </a>
</div>

<script>
// Open payment modal with slip details
function openPaymentModal(slipId, amount) {
    $('#payment_slip_id').val(slipId);
    $('#payment_amount').val(amount);
    $('#display_amount').text(formatNumber(amount));
    $('#paymentModal').modal('show');
    
    // Reset form
    $('#payment_method').val('');
    $('#transaction_id').val('');
    $('#payment_date').val('<?php echo date('Y-m-d'); ?>');
    $('#bank_name').val('');
    $('#payment_remarks').val('');
}

// Format number with commas
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Submit payment
function submitPayment() {
    const slipId = $('#payment_slip_id').val();
    const paymentMethod = $('#payment_method').val();
    const transactionId = $('#transaction_id').val();
    const paymentDate = $('#payment_date').val();
    const bankName = $('#bank_name').val();
    const remarks = $('#payment_remarks').val();
    
    // Validation
    if (!paymentMethod) {
        toastr.error('Please select a payment method');
        return;
    }
    
    if (!paymentDate) {
        toastr.error('Please select payment date');
        return;
    }
    
    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    btn.disabled = true;
    
    $.ajax({
        url: '<?php echo base_url("admin/users/update-payment-status"); ?>',
        type: 'POST',
        data: {
            slip_id: slipId,
            payment_status: 'paid',
            payment_method: paymentMethod,
            transaction_id: transactionId,
            payment_date: paymentDate,
            bank_name: bankName,
            remarks: remarks,
            <?php echo csrf_token(); ?>: '<?php echo csrf_hash(); ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#paymentModal').modal('hide');
                toastr.success(response.msg || 'Salary payment processed successfully!');
                
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                toastr.error(response.msg || 'Failed to update payment status');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        },
        error: function(xhr) {
            let errorMsg = 'Error updating payment status';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.msg) errorMsg = response.msg;
            } catch(e) {}
            toastr.error(errorMsg);
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
}
</script>

<?php echo $this->endSection(); ?>