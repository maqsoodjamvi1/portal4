<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Daily Attendance Summary</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/attendance/scan') ?>">QR Scanner</a></li>
                    <li class="breadcrumb-item active">Summary</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Date Picker -->
        <div class="row mb-3">
            <div class="col-md-4 offset-md-4">
                <div class="input-group">
                    <input type="date" id="attendanceDate" class="form-control" value="<?= $date ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary" onclick="loadSummary()">
                            <i class="fas fa-search mr-1"></i> View
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $total_teachers ?></h3>
                        <p>Total Employees</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $present ?></h3>
                        <p>Present Today</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $late ?></h3>
                        <p>Late Arrivals</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3><?= $attendance_percentage ?>%</h3>
                        <p>Attendance Rate</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Second Row - QR vs Manual -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-qrcode mr-2"></i>
                            Check-in Methods
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-6">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-info">
                                        <i class="fas fa-qrcode"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">QR Scanner</span>
                                        <span class="info-box-number"><?= $qr_scans ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-secondary">
                                        <i class="fas fa-pen"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Manual Entry</span>
                                        <span class="info-box-number"><?= $manual ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="progress mt-3">
                            <?php $qrPercent = $present > 0 ? round(($qr_scans / $present) * 100, 1) : 0; ?>
                            <div class="progress-bar bg-info" style="width: <?= $qrPercent ?>%">
                                QR: <?= $qrPercent ?>%
                            </div>
                            <div class="progress-bar bg-secondary" style="width: <?= 100 - $qrPercent ?>%">
                                Manual: <?= 100 - $qrPercent ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-2"></i>
                            Check-out Status
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-success">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Checked Out</span>
                                        <span class="info-box-number"><?= $checked_out ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-warning">
                                        <i class="fas fa-clock"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Still In</span>
                                        <span class="info-box-number"><?= $present - $checked_out ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Present Teachers List -->
        <div class="row">
            <div class="col-md-6">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-check mr-2"></i>
                            Present Employees (<?= count($present_teachers) ?>)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($present_teachers) > 0): ?>
                                    <?php foreach ($present_teachers as $teacher): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($teacher->first_name . ' ' . $teacher->last_name) ?></strong>
                                            <br><small class="text-muted"><?= esc($teacher->designation ?? 'Employee') ?></small>
                                        </td>
                                        <td>
                                            <?php if ($teacher->checkin): ?>
                                                <span class="badge badge-success">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    <?= date('h:i A', strtotime($teacher->checkin)) ?>
                                                </span>
                                                <br><small><?= $teacher->check_in_method == 'qr' ? 'QR' : 'Manual' ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($teacher->checkout): ?>
                                                <span class="badge badge-info">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    <?= date('h:i A', strtotime($teacher->checkout)) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Still In</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($teacher->status == 'late'): ?>
                                                <span class="badge badge-warning">Late</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">Present</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            No attendance recorded for this date.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Absent Teachers List -->
            <div class="col-md-6">
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-slash mr-2"></i>
                            Absent Employees (<?= count($absent_teachers) ?>)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Designation</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($absent_teachers) > 0): ?>
                                    <?php foreach ($absent_teachers as $teacher): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($teacher->first_name . ' ' . $teacher->last_name) ?></strong>
                                        </td>
                                        <td><?= esc($teacher->designation ?? 'Employee') ?></td>
                                        <td>
                                            <a href="<?= base_url('admin/attendance/manual?teacher_id=' . $teacher->id . '&date=' . $date) ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-pen mr-1"></i> Mark
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
                                            All employees are present!
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function loadSummary() {
    const date = document.getElementById('attendanceDate').value;
    if (date) {
        window.location.href = '<?= base_url('admin/attendance/summary') ?>?date=' + date;
    }
}
</script>

<?= $this->endSection() ?>