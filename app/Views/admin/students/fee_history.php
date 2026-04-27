<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <!-- Student Header -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-history mr-2"></i> 
                Fee Payment History - <?= esc($student->first_name . ' ' . $student->last_name) ?>
            </h5>
            <div>
                <button type="button" class="btn btn-light btn-sm" onclick="window.print();">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="<?= site_url('admin/students/edit?id=' . $student->student_id) ?>" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Student
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Student Info Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Registration No</span>
                            <span class="info-box-number"><?= esc($student->reg_no ?? 'N/A') ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Father Name</span>
                            <span class="info-box-number"><?= esc($student->father_name ?? 'N/A') ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Total Paid</span>
                            <span class="info-box-number text-success">
                                Rs. <?= number_format(array_sum(array_column($payment_history, 'paid_amount')), 2) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Total Discount</span>
                            <span class="info-box-number text-warning">
                                Rs. <?= number_format(array_sum(array_column($payment_history, 'discount_amount')), 2) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Class History Timeline -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-graduation-cap mr-2"></i> Academic Journey</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php foreach ($class_history as $index => $class): ?>
                            <div class="timeline-item">
                                <div class="timeline-badge <?= $index == 0 ? 'bg-success' : 'bg-primary' ?>">
                                    <i class="fas fa-<?= $index == 0 ? 'star' : 'book' ?>"></i>
                                </div>
                                <div class="timeline-panel">
                                    <div class="timeline-heading">
                                        <h6 class="timeline-title">
                                            <?= esc($class->class_name) ?> - <?= esc($class->section_name) ?>
                                            <?php if ($index == 0): ?>
                                                <span class="badge badge-success ml-2">Current/Last Class</span>
                                            <?php endif; ?>
                                        </h6>
                                        <p class="text-muted">
                                            <i class="fas fa-calendar-alt mr-1"></i> 
                                            Session: <?= esc($class->session_name ?? 'N/A') ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($class_history)): ?>
                            <p class="text-muted">No class history found</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Monthly Fee Summary -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-calendar-alt mr-2"></i> Monthly Fee Payment Summary</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Month</th>
                                    <th>Class</th>
                                    <th>Total Paid (Rs.)</th>
                                    <th>Discount (Rs.)</th>
                                    <th>Net Paid (Rs.)</th>
                                    <th>Payment Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($monthly_fee_summary as $month): ?>
                                    <tr>
                                        <td><?= esc($month->month_display) ?></td>
                                        <td><?= esc($month->class_name ?? 'N/A') ?> - <?= esc($month->section_name ?? '') ?></td>
                                        <td class="text-right"><?= number_format($month->total_paid, 2) ?></td>
                                        <td class="text-right text-warning"><?= number_format($month->total_discount, 2) ?></td>
                                        <td class="text-right text-success font-weight-bold">
                                            <?= number_format($month->total_paid - $month->total_discount, 2) ?>
                                        </td>
                                        <td class="text-center"><?= $month->payment_count ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($monthly_fee_summary)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No monthly fee payments found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Fee Type Summary -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-chart-pie mr-2"></i> Fee Type Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Fee Type</th>
                                            <th>Type</th>
                                            <th class="text-right">Total Paid (Rs.)</th>
                                            <th class="text-right">Discount (Rs.)</th>
                                            <th class="text-right">Net (Rs.)</th>
                                            <th class="text-center">Payments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($fee_type_summary as $fee): ?>
                                            <tr>
                                                <td><?= esc($fee->fee_type_name) ?></td>
                                                <td>
                                                    <?php if ($fee->is_monthly_fee == 1): ?>
                                                        <span class="badge badge-info">Monthly</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">One-time</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-right"><?= number_format($fee->total_paid, 2) ?></td>
                                                <td class="text-right text-warning"><?= number_format($fee->total_discount, 2) ?></td>
                                                <td class="text-right text-success font-weight-bold">
                                                    <?= number_format($fee->total_paid - $fee->total_discount, 2) ?>
                                                </td>
                                                <td class="text-center"><?= $fee->payment_count ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Annual Summary -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-chart-line mr-2"></i> Annual Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Year</th>
                                            <th class="text-right">Total Paid (Rs.)</th>
                                            <th class="text-right">Discount (Rs.)</th>
                                            <th class="text-right">Net (Rs.)</th>
                                            <th class="text-center">Payments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($annual_summary as $year): ?>
                                            <tr>
                                                <td><?= $year->year ?></td>
                                                <td class="text-right"><?= number_format($year->total_paid, 2) ?></td>
                                                <td class="text-right text-warning"><?= number_format($year->total_discount, 2) ?></td>
                                                <td class="text-right text-success font-weight-bold">
                                                    <?= number_format($year->total_paid - $year->total_discount, 2) ?>
                                                </td>
                                                <td class="text-center"><?= $year->payment_count ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Detailed Payment History -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-list-alt mr-2"></i> Detailed Payment History</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="paymentTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Fee Type</th>
                                    <th>Month</th>
                                    <th>Class</th>
                                    <th>Session</th>
                                    <th class="text-right">Amount (Rs.)</th>
                                    <th class="text-right">Discount (Rs.)</th>
                                    <th class="text-right">Net Paid (Rs.)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payment_history as $payment): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($payment->paid_date)) ?></td>
                                        <td><?= esc($payment->fee_type_name) ?></td>
                                        <td><?= date('M Y', strtotime($payment->fee_month . '-01')) ?></td>
                                        <td><?= esc($payment->class_name ?? 'N/A') ?> - <?= esc($payment->section_name ?? '') ?></td>
                                        <td><?= esc($payment->session_name ?? 'N/A') ?></td>
                                        <td class="text-right"><?= number_format($payment->paid_amount, 2) ?></td>
                                        <td class="text-right text-warning"><?= number_format($payment->discount_amount, 2) ?></td>
                                        <td class="text-right text-success font-weight-bold">
                                            <?= number_format($payment->paid_amount - $payment->discount_amount, 2) ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">Paid</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($payment_history)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No payment records found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="thead-light">
                                <tr class="font-weight-bold">
                                    <td colspan="5" class="text-right">TOTAL:</td>
                                    <td class="text-right"><?= number_format(array_sum(array_column($payment_history, 'paid_amount')), 2) ?></td>
                                    <td class="text-right"><?= number_format(array_sum(array_column($payment_history, 'discount_amount')), 2) ?></td>
                                    <td class="text-right text-success">
                                        <?= number_format(array_sum(array_column($payment_history, 'paid_amount')) - array_sum(array_column($payment_history, 'discount_amount')), 2) ?>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-box {
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.info-box-number {
    font-size: 20px;
    font-weight: bold;
    display: block;
    margin-top: 5px;
}
.timeline {
    position: relative;
    padding: 20px 0;
}
.timeline-item {
    position: relative;
    margin-bottom: 20px;
}
.timeline-badge {
    position: absolute;
    left: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    text-align: center;
    line-height: 40px;
    color: white;
}
.timeline-panel {
    margin-left: 60px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}
.timeline-title {
    margin-bottom: 5px;
    font-weight: bold;
}
.table td, .table th {
    vertical-align: middle;
}
@media print {
    .btn, .main-sidebar, .main-header {
        display: none !important;
    }
    .card-header {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
}
</style>

<script>
$(document).ready(function() {
    $('#paymentTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 25,
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries"
        }
    });
});
</script>

<?= $this->endSection() ?>