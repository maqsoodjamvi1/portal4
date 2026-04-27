<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Manual Attendance Entry</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/attendance/scan') ?>">QR Scanner</a></li>
                    <li class="breadcrumb-item active">Manual Entry</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-pen-alt mr-2"></i>
                            Record Attendance Manually
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <?php if (session()->getFlashdata('message')): ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <?= session()->getFlashdata('message') ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <?= session()->getFlashdata('error') ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="<?= base_url('admin/attendance/manual') ?>">
                            <?= csrf_field() ?>
                            
                            <div class="form-group">
                                <label for="teacher_id">Select Employee <span class="text-danger">*</span></label>
                                <select name="teacher_id" id="teacher_id" class="form-control select2" required>
                                    <option value="">-- Select Employee --</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?= $teacher->id ?>">
                                            <?= esc($teacher->first_name . ' ' . $teacher->last_name) ?> 
                                            (<?= esc($teacher->designation ?? 'Employee') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Action <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="action_in" name="action" value="in" class="custom-control-input" required>
                                            <label class="custom-control-label" for="action_in">
                                                <i class="fas fa-sign-in-alt text-success mr-1"></i> Check In
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="action_out" name="action" value="out" class="custom-control-input">
                                            <label class="custom-control-label" for="action_out">
                                                <i class="fas fa-sign-out-alt text-danger mr-1"></i> Check Out
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="date">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" id="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="time">Time <span class="text-danger">*</span></label>
                                <input type="time" name="time" id="time" class="form-control" value="<?= date('H:i:s') ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="notes">Notes / Reason</label>
                                <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                            </div>
                            
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Save Attendance
                                </button>
                                <a href="<?= base_url('admin/attendance/scan') ?>" class="btn btn-secondary">
                                    <i class="fas fa-qrcode mr-1"></i> Use QR Scanner
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-2"></i>
                            Today's Attendance
                        </h3>
                    </div>
                    <div class="card-body table-responsive">
                        <?php
                        $today = date('Y-m-d');
                        $db = \Config\Database::connect();
                        $todayAttendance = $db->table('attendance_employee a')
                            ->select('a.*, u.first_name, u.last_name, u.designation')
                            ->join('users u', 'a.emp_id = u.id')
                            ->where('a.date', $today)
                            ->orderBy('a.checkin', 'DESC')
                            ->get()
                            ->getResult();
                        ?>
                        
                        <?php if (empty($todayAttendance)): ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle mr-2"></i> No attendance recorded for today.
                            </div>
                        <?php else: ?>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Status</th>
                                        <th>Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($todayAttendance as $att): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($att->first_name . ' ' . $att->last_name) ?></strong>
                                            <br><small class="text-muted"><?= esc($att->designation ?? '') ?></small>
                                        </td>
                                        <td>
                                            <?php if ($att->checkin): ?>
                                                <i class="fas fa-clock text-success mr-1"></i>
                                                <?= date('h:i A', strtotime($att->checkin)) ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($att->checkout): ?>
                                                <i class="fas fa-clock text-danger mr-1"></i>
                                                <?= date('h:i A', strtotime($att->checkout)) ?>
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
                                            <span class="badge badge-<?= $statusClass ?>">
                                                <?= ucfirst($att->status ?? 'unknown') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($att->check_in_method == 'qr'): ?>
                                                <span class="badge badge-info"><i class="fas fa-qrcode mr-1"></i> QR</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><i class="fas fa-pen mr-1"></i> Manual</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>