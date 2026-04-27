<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Salary Slips</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/salary-reports') ?>">Salary Reports</a></li>
                    <li class="breadcrumb-item active">All Salary Slips</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filter Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-filter mr-1"></i> Filter Salary Slips
                        </h3>
                    </div>
                    <div class="card-body">
                        <form method="get" class="form-inline">
                            <div class="form-group mr-2 mb-2">
                                <label class="mr-2">Employee:</label>
                                <select name="employee_id" class="form-control select2" style="width: 250px;">
                                    <option value="">All Employees</option>
                                    <?php foreach ($employees as $emp): ?>
                                        <option value="<?= $emp->id ?>" <?= $selectedEmployee == $emp->id ? 'selected' : '' ?>>
                                            <?= esc($emp->first_name . ' ' . $emp->last_name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group mr-2 mb-2">
                                <label class="mr-2">Year:</label>
                                <select name="year" class="form-control">
                                    <option value="">All Years</option>
                                    <?php for($y = date('Y')-2; $y <= date('Y'); $y++): ?>
                                        <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group mr-2 mb-2">
                                <label class="mr-2">Month:</label>
                                <select name="month" class="form-control">
                                    <option value="">All Months</option>
                                    <?php for($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>
                                            <?= date('F', strtotime("2024-$m-01")) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary mb-2">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                            <a href="<?= base_url('admin/salary-slips') ?>" class="btn btn-default mb-2 ml-2">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Salary Slips Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-invoice-dollar mr-1"></i> Salary Slips List
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-success" id="exportExcel">
                                <i class="fas fa-file-excel mr-1"></i> Export
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" id="printTable">
                                <i class="fas fa-print mr-1"></i> Print
                            </button>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-hover" id="salarySlipsTable">
                            <thead>
                                <tr>
                                    <th>Slip No</th>
                                    <th>Employee</th>
                                    <th>Designation</th>
                                    <th>Month/Year</th>
                                    <th>Basic Salary</th>
                                    <th>Bonus</th>
                                    <th>Deductions</th>
                                    <th>Net Salary</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($slips)): ?>
                                    <?php foreach ($slips as $slip): ?>
                                    <tr>
                                        <td><code><?= $slip->slip_no ?></code></td>
                                        <td>
                                            <strong><?= esc($slip->first_name . ' ' . $slip->last_name) ?></strong>
                                        </td>
                                        <td><?= esc($slip->designation ?? 'N/A') ?></td>
                                        <td><?= date('F Y', strtotime($slip->year . '-' . $slip->month . '-01')) ?></td>
                                        <td class="text-right"><?= number_format($slip->basic_salary, 2) ?></td>
                                        <td class="text-right text-success">
                                            <?= number_format(($slip->attendance_bonus ?? 0) + ($slip->other_bonus ?? 0), 2) ?>
                                        </td>
                                        <td class="text-right text-danger">
                                            <?= number_format($slip->total_deductions ?? 0, 2) ?>
                                        </td>
                                        <td class="text-right font-weight-bold">
                                            <?= number_format($slip->net_salary ?? 0, 2) ?>
                                        </td>
                                        <td>
                                            <?php if ($slip->payment_status == 'paid'): ?>
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
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">
                                            <i class="fas fa-info-circle mr-1"></i> No salary slips found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($slips)): ?>
                            <tfoot>
                                <tr class="bg-light">
                                    <th colspan="4" class="text-right">Total:</th>
                                    <th class="text-right">
                                        <?= number_format(array_sum(array_column($slips, 'basic_salary')), 2) ?>
                                    </th>
                                    <th class="text-right">
                                        <?= number_format(array_sum(array_column($slips, 'attendance_bonus')) + array_sum(array_column($slips, 'other_bonus')), 2) ?>
                                    </th>
                                    <th class="text-right">
                                        <?= number_format(array_sum(array_column($slips, 'total_deductions')), 2) ?>
                                    </th>
                                    <th class="text-right">
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
    // Initialize select2
    $('.select2').select2({
        width: '100%',
        placeholder: 'Select employee'
    });
    
    // Export to Excel
    $('#exportExcel').on('click', function() {
        var table = document.getElementById('salarySlipsTable');
        var html = table.outerHTML;
        var url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
        var link = document.createElement('a');
        link.download = 'salary_slips_<?= date('Y-m-d') ?>.xls';
        link.href = url;
        link.click();
    });
    
    // Print table
    $('#printTable').on('click', function() {
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
    table {
        width: 100%;
    }
}
</style>

<?= $this->endSection() ?>