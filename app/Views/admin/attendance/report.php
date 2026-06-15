<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Attendance Report',
    'icon' => 'fas fa-chart-bar',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'QR Scanner', 'url' => base_url('admin/attendance/scan')],
        ['label' => 'Report', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar me-2"></i>
                    Attendance Records
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Filter Form -->
                <form method="get" action="<?= base_url('admin/attendance/report') ?>" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>From Date</label>
                                <input type="date" name="date_from" class="form-control" value="<?= $date_from ?? date('Y-m-01') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>To Date</label>
                                <input type="date" name="date_to" class="form-control" value="<?= $date_to ?? date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Employee</label>
                                <select name="teacher_id" class="form-control select2">
                                    <option value="">-- All Employees --</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?= $teacher->id ?>" <?= ($selected_teacher ?? '') == $teacher->id ? 'selected' : '' ?>>
                                            <?= esc($teacher->first_name . ' ' . $teacher->last_name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                
                <!-- Export Buttons -->
                <div class="mb-3">
                    <button onclick="exportToExcel()" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i> Export to Excel
                    </button>
                    <button onclick="window.print()" class="btn btn-info btn-sm">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
                
                <!-- Attendance Table -->
                <div class="table-responsive">
                    <table id="attendanceTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr class="bg-light">
                                <th>#</th>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Method</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($attendances)): ?>
                                <?php $counter = 1; ?>
                                <?php foreach ($attendances as $att): ?>
                                    <tr>
                                        <td class="text-center"><?= $counter++ ?></td>
                                        <td><?= date('d M Y', strtotime($att->date)) ?></td>
                                        <td>
                                            <strong><?= esc($att->first_name . ' ' . $att->last_name) ?></strong>
                                            <br><small class="text-muted"><?= esc($att->designation ?? 'Employee') ?></small>
                                        </td>
                                        <td>
                                            <?php if ($att->checkin): ?>
                                                <i class="fas fa-clock text-success me-1"></i>
                                                <?= date('h:i A', strtotime($att->checkin)) ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($att->checkout): ?>
                                                <i class="fas fa-clock text-danger me-1"></i>
                                                <?= date('h:i A', strtotime($att->checkout)) ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($att->checkin && $att->checkout): ?>
                                                <?php 
                                                $hours = floor($att->lc_duration / 60);
                                                $minutes = $att->lc_duration % 60;
                                                echo $hours . 'h ' . $minutes . 'm';
                                                ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $statusClass = 'secondary';
                                            if ($att->status == 'present') $statusClass = 'success';
                                            elseif ($att->status == 'late') $statusClass = 'warning';
                                            elseif ($att->status == 'absent') $statusClass = 'danger';
                                            ?>
                                            <span class="badge text-bg-<?=  $statusClass ?>">
                                                <?= ucfirst($att->status ?? 'unknown') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($att->check_in_method == 'qr'): ?>
                                                <span class="badge text-bg-info"><i class="fas fa-qrcode me-1"></i> QR</span>
                                            <?php elseif ($att->check_in_method == 'manual'): ?>
                                                <span class="badge text-bg-secondary"><i class="fas fa-pen me-1"></i> Manual</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-secondary">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= esc($att->remarks ?? '-') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        <i class="fas fa-info-circle me-1"></i> No attendance records found for the selected criteria.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Summary Stats -->
                <?php if (!empty($attendances)): ?>
                    <?php 
                    $totalPresent = 0;
                    $totalLate = 0;
                    $totalQR = 0;
                    $totalManual = 0;
                    foreach ($attendances as $att) {
                        if ($att->status == 'present') $totalPresent++;
                        if ($att->status == 'late') $totalLate++;
                        if ($att->check_in_method == 'qr') $totalQR++;
                        if ($att->check_in_method == 'manual') $totalManual++;
                    }
                    ?>
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3><?= count($attendances) ?></h3>
                                    <p>Total Records</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3><?= $totalPresent ?></h3>
                                    <p>Present</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3><?= $totalLate ?></h3>
                                    <p>Late Arrivals</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3><?= $totalQR ?></h3>
                                    <p>QR Scans</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-qrcode"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
function exportToExcel() {
    const table = document.getElementById('attendanceTable');
    const wb = XLSX.utils.table_to_book(table, { sheet: "Attendance Report", raw: true });
    XLSX.writeFile(wb, `attendance_report_<?= date('Y-m-d') ?>.xlsx`);
}

// Load SheetJS for Excel export
const script = document.createElement('script');
script.src = 'https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js';
document.head.appendChild(script);
</script>

<?= $this->endSection() ?>