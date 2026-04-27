<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Salary Reports</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/salary-settings') ?>">Salary Settings</a></li>
                    <li class="breadcrumb-item active">Reports</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Month Selector -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-alt mr-1"></i> Select Month
                        </h3>
                    </div>
                    <div class="card-body">
                        <form method="get" action="<?= base_url('admin/salary-reports') ?>" class="form-inline">
                            <div class="form-group mr-2">
                                <label class="mr-2">Year:</label>
                                <select name="year" class="form-control">
                                    <?php for($y = date('Y')-2; $y <= date('Y'); $y++): ?>
                                        <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group mr-2">
                                <label class="mr-2">Month:</label>
                                <select name="month" class="form-control">
                                    <?php for($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>><?= date('F', strtotime("2024-$m-01")) ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-1"></i> View Report
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mt-3">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= number_format($summary->total_employees ?? 0) ?></h3>
                        <p>Total Employees</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= number_format($summary->total_payable ?? 0, 2) ?></h3>
                        <p>Total Payable</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= number_format($summary->total_pending ?? 0, 2) ?></h3>
                        <p>Pending Payment</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= number_format($summary->total_paid ?? 0, 2) ?></h3>
                        <p>Paid Amount</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-1"></i> 
                            Salary Details - <?= date('F Y', strtotime("$year-$month-01")) ?>
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-success" id="exportExcel">
                                <i class="fas fa-file-excel mr-1"></i> Export Excel
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" id="exportPDF">
                                <i class="fas fa-file-pdf mr-1"></i> Export PDF
                            </button>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-hover" id="salaryReportTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee Name</th>
                                    <th>Designation</th>
                                    <th>Basic Salary</th>
                                    <th>Bonus</th>
                                    <th>Deductions</th>
                                    <th>Net Salary</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $slips = $salarySlips ?? [];
                                if (!empty($slips)): 
                                    foreach ($slips as $index => $slip):
                                ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <strong><?= esc($slip->first_name ?? '') ?> <?= esc($slip->last_name ?? '') ?></strong><br>
                                        <small class="text-muted"><?= esc($slip->designation ?? 'N/A') ?></small>
                                    </td>
                                    <td><?= esc($slip->designation ?? 'N/A') ?></td>
                                    <td class="text-right"><?= number_format($slip->basic_salary, 2) ?></td>
                                    <td class="text-right">
                                        <?= number_format(($slip->attendance_bonus ?? 0) + ($slip->other_bonus ?? 0), 2) ?>
                                    </td>
                                    <td class="text-right">
                                        <?= number_format($slip->total_deductions ?? 0, 2) ?>
                                    </td>
                                    <td class="text-right font-weight-bold">
                                        <?= number_format($slip->net_salary ?? 0, 2) ?>
                                    </td>
                                    <td>
                                        <?php if (($slip->payment_status ?? 'pending') == 'paid'): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check-circle"></i> Paid
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('admin/users/view-salary-slip/' . $slip->user_id . '/' . $slip->slip_id) ?>" 
                                           class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-eye"></i> View Slip
                                        </a>
                                    </td>
                                </tr>
                                <?php 
                                    endforeach;
                                else: 
                                ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        <i class="fas fa-info-circle mr-1"></i> No salary records found for <?= date('F Y', strtotime("$year-$month-01")) ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($slips)): ?>
                            <tfoot>
                                <tr class="bg-light">
                                    <th colspan="3" class="text-right">Total:</th>
                                    <th class="text-right">
                                        <?= number_format(array_sum(array_column($slips, 'basic_salary')), 2) ?>
                                    </th>
                                    <th class="text-right">
                                        <?= number_format(array_sum(array_column($slips, 'attendance_bonus')) + array_sum(array_column($slips, 'other_bonus')), 2) ?>
                                    </th>
                                    <th class="text-right">
                                        <?= number_format(array_sum(array_column($slips, 'total_deductions')), 2) ?>
                                    </th>
                                    <th class="text-right font-weight-bold">
                                        <?= number_format(array_sum(array_column($slips, 'net_salary')), 2) ?>
                                    </th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    // Export to Excel
    $('#exportExcel').on('click', function() {
        var table = document.getElementById('salaryReportTable');
        var html = table.outerHTML;
        var url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
        var link = document.createElement('a');
        link.download = 'salary_report_<?= $year ?>_<?= $month ?>.xls';
        link.href = url;
        link.click();
    });
    
    // Export to PDF (print)
    $('#exportPDF').on('click', function() {
        window.print();
    });
});
</script>

<style>
@media print {
    .btn, .navbar, .main-sidebar, .breadcrumb, .card-header .card-tools {
        display: none !important;
    }
    .content-wrapper {
        margin-left: 0 !important;
    }
    .small-box {
        border: 1px solid #ddd;
    }
    table {
        width: 100%;
    }
}
</style>

<?= $this->endSection() ?>