<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<!-- For JQVMap -->

<!-- Debug: Teacher Role Check -->
<?php if (ENVIRONMENT === 'development'): ?>
<div class="alert alert-info mb-3">
    <strong>Debug Info:</strong><br>
    isTeacher: <?= var_export($isTeacher, true) ?><br>
    User ID: <?= session()->get('user_id') ?><br>
    <?php 
    $roles = currentUserRoles();
    echo 'Roles: ' . implode(', ', $roles);
    ?>
</div>
<?php endif; ?>
<!-- Debug Info (remove in production) -->
<?php if (ENVIRONMENT === 'development'): ?>
<script>
console.log('Dashboard Data:', {
    feeMonths: <?= json_encode($prStr ?? []) ?>,
    feePaid: <?= json_encode($paidStr ?? []) ?>,
    feeUnpaid: <?= json_encode($unpaidStr ?? []) ?>,
    classes: <?= json_encode($clsArr ?? []) ?>,
    maleCounts: <?= json_encode($stdMArr ?? []) ?>,
    femaleCounts: <?= json_encode($stdFArr ?? []) ?>,
    attendance: <?= json_encode($attendance ?? []) ?>
});
</script>
<?php endif; ?>


<?php 
// TEMPORARY DEBUG - REMOVE LATER
$userRoles = currentUserRoles();
$isTeacher = (in_array('teacher', $userRoles) || in_array('faculty', $userRoles));
// Add this line to see what roles are detected
error_log('User Roles: ' . print_r($userRoles, true));
error_log('Is Teacher: ' . ($isTeacher ? 'Yes' : 'No'));
?>



<style>


/* Attendance Status Cards */
.attendance-card {
    transition: transform 0.2s;
}
.attendance-card:hover {
    transform: translateY(-2px);
}

/* Recent Attendance Table */
#recentAttendanceList table {
    font-size: 13px;
}
#recentAttendanceList table th {
    position: sticky;
    top: 0;
    background: white;
    font-weight: 600;
}
#recentAttendanceList table td {
    vertical-align: middle;
    padding: 8px 4px;
}
#recentAttendanceList table tr:hover {
    background-color: #f8f9fa;
}

/* Current Date/Time Widget */
#currentDateTime {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    padding: 15px;
}
  /* Chart container fixes */
canvas {
    max-width: 100%;
    height: auto !important;
}

.card-body {
    position: relative;
    min-height: 300px;
}

/* Ensure charts are visible */
#pieChart, #stackedBarChart, #pieChartAttendance, #stackedBarChartSection {
    display: block !important;
    width: 100% !important;
}
  .info-box {
    min-height: 110px !important;
  }
  .config_steps li {
    color: red;
    margin-bottom: 5px;
  }
  
  /* BMI Widget Styles */
  .bmi-widget {
    transition: all 0.3s ease;
  }
  .bmi-widget:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  }
  .bmi-category-underweight { background-color: #3498db; }
  .bmi-category-normal { background-color: #2ecc71; }
  .bmi-category-overweight { background-color: #f39c12; }
  .bmi-category-obese { background-color: #e74c3c; }
  
  .bmi-stat-value {
    font-size: 24px;
    font-weight: bold;
  }
  .bmi-progress {
    height: 8px;
    border-radius: 4px;
    margin-top: 8px;
  }
  .bmi-category-label {
    font-size: 11px;
    display: inline-block;
    width: 20%;
    text-align: center;
  }
  .health-alert-item {
    padding: 8px 12px;
    border-bottom: 1px solid #eee;
    transition: background 0.2s;
  }
  .health-alert-item:hover {
    background: #f8f9fa;
  }
  .health-alert-item:last-child {
    border-bottom: none;
  }
  .alert-badge {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
  }
  .alert-badge-underweight { background-color: #3498db; }
  .alert-badge-normal { background-color: #2ecc71; }
  .alert-badge-overweight { background-color: #f39c12; }
  .alert-badge-obese { background-color: #e74c3c; }
  
  /* QR Scanner Styles */
  .qr-scanner-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }
  .qr-scanner-card .card-header {
    border-bottom: 1px solid rgba(255,255,255,0.2);
  }
  .qr-scanner-card .btn-scan {
    background: white;
    color: #667eea;
    border: none;
    padding: 12px 30px;
    font-weight: bold;
    border-radius: 50px;
    transition: all 0.3s;
  }
  .qr-scanner-card .btn-scan:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
  }
  .attendance-status-card {
    background: #f8f9fa;
    border-start: 4px solid #28a745;
  }
  .attendance-status-card.late {
    border-start-color: #ffc107;
  }
  .attendance-status-card.checkout {
    border-start-color: #17a2b8;
  }
  .attendance-status-card.pending {
    border-start-color: #6c757d;
  }
  .recent-attendance-list {
    max-height: 200px;
    overflow-y: auto;
  }
  .recent-item {
    transition: background 0.2s;
  }
  .recent-item:hover {
    background: #f8f9fa;
  }
  .modal-content {
    border-radius: 20px;
  }
  #qr-reader {
    width: 100%;
    margin: 0 auto;
  }
  #qr-reader video {
    border-radius: 10px;
  }
</style>

<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Dashboard</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main content -->
<section class="content">
  <!-- Configuration Steps -->
  <div class="mb-4">
    <ul class="config_steps">
      <?php 
      $session = \Config\Services::session();
      $campus_id = $session->get('member_campusid');
      $schoolinfo = getSchoolInfo();
      $db = \Config\Database::connect();
      
      $academic_session_info = $db->table('academic_session')
          ->where('system_id', $schoolinfo->system_id)
          ->get()
          ->getRow();
          
      if(empty($academic_session_info)): ?>
        <li>Step 1 Of 10: Add <strong>Academic Session</strong> to complete system configuration. 
          <a href="<?= base_url('admin/academic_session/add') ?>" class="text-decoration-underline">Click here</a>
        </li>
      <?php endif; ?>
    </ul>  
  </div>

<!-- Session Selector -->
<!-- Session Selector for Fee Collection Chart -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line me-2"></i>
                    Fee Collection Report
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Select Session</label>
                            <select class="form-control select2" id="sessionSelector" style="width: 100%;">
                                <option value="">Last 12 Months (Default)</option>
                                <?php foreach ($allSessions as $session): ?>
                                    <option value="<?= $session->session_id ?>" 
                                        <?= ($selectedSessionId == $session->session_id) ? 'selected' : '' ?>>
                                        <?= esc($session->session_name) ?> 
                                        (<?= date('M Y', strtotime($session->start_date)) ?> - 
                                        <?= date('M Y', strtotime($session->end_date)) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fee Collection Chart -->
<div class="row">
    <div class="col-md-12">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar me-2"></i>
                    Fee Collection - <?= esc($chartTitle) ?>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="stackedBarChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>
<!-- Temporary Debug - Remove Later -->
<div class="row mb-3">
    
</div>

<!-- ============================================ -->
<!-- QR ATTENDANCE SCANNER SECTION - Compact Design -->
<!-- ============================================ -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-qrcode me-2"></i>
                        QR Attendance Scanner
                    </h3>
                    <!-- Compact Date/Time in Header -->
                    <div class="text-end">
                        <div id="currentDateTimeCompact" class="d-flex align-items-center">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <span id="currentDayCompact" class="me-2">Monday</span>
                            <span id="currentDateCompact" class="me-2">24 Mar 2026</span>
                            <i class="fas fa-clock ms-2 me-1"></i>
                            <span id="currentTimeCompact">10:30:45 AM</span>
                        </div>
                    </div>
                </div>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Left Column - QR Scanner & Status -->
                    <div class="col-md-6">
                        <!-- Compact Attendance Status Card -->
                        <div id="attendanceStatusContainer" class="mb-3">
                            <!-- Status will be loaded here -->
                        </div>
                        
                        <!-- Scan Button -->
                        <div class="text-center">
                            <button class="btn btn-light btn-lg w-100" data-bs-toggle="modal" data-bs-target="#qrScannerModal" style="border-radius: 50px; padding: 10px 20px; font-weight: bold;">
                                <i class="fas fa-camera me-2"></i>
                                Scan QR Code
                            </button>
                            <p class="mt-2 text-white-50 mb-0">
                                <small><i class="fas fa-info-circle me-1"></i> Scan the QR code to mark attendance</small>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Right Column - Recent Attendance Table -->
                    <div class="col-md-6">
                        <div class="bg-white rounded p-2">
                            <h6 class="text-dark mb-2">
                                <i class="fas fa-history me-2 text-primary"></i>
                                Recent Attendance
                            </h6>
                            <div id="recentAttendanceList" style="max-height: 250px; overflow-y: auto;">
                                <div class="text-center text-muted py-2">
                                    <i class="fas fa-spinner fa-spin"></i> Loading...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
  <!-- Info Boxes Row 1 -->
  <div class="row">
    <?php if(hasPermission('admin-db-students')): ?>
      <div class="col-md-3 col-sm-6 col-12">
        <a href="<?= base_url('admin/students?status=1') ?>">  
          <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-user-graduate"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Students</span>
              <span class="info-box-number"><?= $noOfstudent ?? 0 ?></span>
            </div>
          </div>
        </a>
      </div>
    <?php endif; ?>

    <?php if(hasPermission('admin-db-teacher')): ?>
      <div class="col-md-3 col-sm-6 col-12">
        <a href="<?= base_url('admin/users') ?>">  
          <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-chalkboard-teacher"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Teachers</span>
              <span class="info-box-number"><?= $infoteachers ?? 0 ?></span>
            </div>
          </div>
        </a>
      </div>
    <?php endif; ?>

    <?php if(hasPermission('admin-db-fee-collection') && isset($monthlyFee)): ?>
      <div class="col-md-3 col-sm-6 col-12">
        <a href="<?= base_url('admin/fee-chalan') ?>"> 
          <div class="info-box">
            <span class="info-box-icon bg-success"><i class="far fa-money-bill-alt"></i></span>
            <div class="info-box-content">
              <span class="info-box-text"><?= date('M Y') ?> <?= $monthlyFee->fee_type_name ?></span>
              <span class="info-box-number" style="font-size: 12px; font-weight: normal;">
              Projected: <?= $prjectedFee_info ?? 0 ?><br>
              Paid: <?= $PaidFee_info ?? 0 ?><br>
              Unpaid: <?= $RemainingBalance_info ?? 0 ?>
              </span>
            </div>
          </div>
        </a>
      </div>
    <?php endif; ?>

    <?php if(hasPermission('admin-db-attendance')): ?>
      <div class="col-md-3 col-sm-6 col-12">
        <a href="<?= base_url('admin/students_attendance/add') ?>"> 
          <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-receipt"></i></span> 
            <div class="info-box-content">
              <span class="info-box-text"><?= date('D j M Y') ?></span>
              <span class="info-box-number" style="font-size: 12px; font-weight: normal;">
                Present: <?= $attendance['present'] ?? 0 ?><br>
                Absent: <?= $attendance['absent'] ?? 0 ?><br>
                Leaves: <?= $attendance['leaves'] ?? 0 ?>
              </span>
            </div>
          </div>
        </a>
      </div>
    <?php endif; ?>
  </div>

  <!-- Info Boxes Row 2 - BMI Widget -->
  <div class="row mt-3">
    <!-- BMI Health Status Widget -->
    <div class="col-md-6 col-sm-12">
      <div class="info-box bmi-widget">
        <span class="info-box-icon bg-gradient-heartbeat" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
          <i class="fas fa-heartbeat"></i>
        </span>
        <div class="info-box-content">
          <span class="info-box-text">BMI Health Status</span>
          <span class="info-box-number" style="font-size: 14px;"><?= $bmiStats->total ?? 0 ?> Students Assessed</span>
          
          <!-- BMI Distribution Bar -->
          <div class="bmi-progress mt-2">
            <div class="progress" style="height: 8px; border-radius: 4px;">
              <?php 
              $total = ($bmiStats->total ?? 1);
              $underweightPercent = ($bmiStats->underweight ?? 0) / $total * 100;
              $normalPercent = ($bmiStats->normal ?? 0) / $total * 100;
              $overweightPercent = ($bmiStats->overweight ?? 0) / $total * 100;
              $obesePercent = ($bmiStats->obese ?? 0) / $total * 100;
              ?>
              <div class="progress-bar bg-info" style="width: <?= $underweightPercent ?>%" 
                   title="Underweight: <?= $bmiStats->underweight ?? 0 ?>"></div>
              <div class="progress-bar bg-success" style="width: <?= $normalPercent ?>%" 
                   title="Normal: <?= $bmiStats->normal ?? 0 ?>"></div>
              <div class="progress-bar bg-warning" style="width: <?= $overweightPercent ?>%" 
                   title="Overweight: <?= $bmiStats->overweight ?? 0 ?>"></div>
              <div class="progress-bar bg-danger" style="width: <?= $obesePercent ?>%" 
                   title="Obese: <?= $bmiStats->obese ?? 0 ?>"></div>
            </div>
          </div>
          
          <!-- BMI Categories Labels -->
          <div class="row mt-2 text-center">
            <div class="col-3">
              <span class="bmi-category-label">
                <i class="fas fa-circle text-info" style="font-size: 10px;"></i> Underweight<br>
                <strong><?= $bmiStats->underweight ?? 0 ?></strong>
              </span>
            </div>
            <div class="col-3">
              <span class="bmi-category-label">
                <i class="fas fa-circle text-success" style="font-size: 10px;"></i> Normal<br>
                <strong><?= $bmiStats->normal ?? 0 ?></strong>
              </span>
            </div>
            <div class="col-3">
              <span class="bmi-category-label">
                <i class="fas fa-circle text-warning" style="font-size: 10px;"></i> Overweight<br>
                <strong><?= $bmiStats->overweight ?? 0 ?></strong>
              </span>
            </div>
            <div class="col-3">
              <span class="bmi-category-label">
                <i class="fas fa-circle text-danger" style="font-size: 10px;"></i> Obese<br>
                <strong><?= $bmiStats->obese ?? 0 ?></strong>
              </span>
            </div>
          </div>
          
          <div class="mt-2">
            <a href="<?= base_url('admin/students/bmi-report') ?>" class="small-box-footer">
              View Detailed Report <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Health Alerts Widget (Optional) -->
    <div class="col-md-6 col-sm-12">
      <div class="info-box bmi-widget">
        <span class="info-box-icon bg-gradient-orange" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
          <i class="fas fa-bell"></i>
        </span>
        <div class="info-box-content">
          <span class="info-box-text">Health Alerts</span>
          <span class="info-box-number" style="font-size: 14px;"><?= $healthAlertsCount ?? 0 ?> Pending Alerts</span>
          
          <?php if (!empty($recentHealthAlerts)): ?>
            <div class="mt-2" style="max-height: 80px; overflow-y: auto;">
              <?php foreach ($recentHealthAlerts as $alert): ?>
                <div class="health-alert-item">
                  <span class="alert-badge alert-badge-<?= $alert->alert_type ?>"></span>
                  <small>
                    <strong><?= esc($alert->student_name) ?></strong> - 
                    <?= esc($alert->alert_type) ?> (BMI: <?= $alert->bmi_value ?>)
                  </small>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="mt-2 text-muted text-center">
              <i class="fas fa-check-circle text-success"></i> No pending health alerts
            </div>
          <?php endif; ?>
          
          <div class="mt-2">
            <a href="<?= base_url('admin/students/health-alerts') ?>" class="small-box-footer">
              View All Alerts <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Session Info Boxes Row 3 -->
  <div class="row mt-3">
    <?php if(hasPermission('admin-db-session') && isset($academic_session)): ?>
      <div class="col-md-3 col-sm-6 col-12">
        <a href="<?= base_url('admin/academic_session') ?>">  
          <div class="info-box">
            <span class="info-box-icon bg-info"><i class="far fa-calendar-alt"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Session (<?= $academic_session->session_name ?>)</span>
              <span class="info-box-number" style="font-size: 12px; font-weight: normal;">
                Start: <?= date('M j, Y', strtotime($academic_session->start_date)) ?><br>
                End: <?= date('M j, Y', strtotime($academic_session->end_date)) ?>
              </span>
            </div>
          </div>
        </a>
      </div>
    <?php endif; ?>

    <?php if(hasPermission('admin-db-term') && isset($termInfo)): ?>
      <div class="col-md-3 col-sm-6 col-12">
        <a href="<?= base_url('admin/terms') ?>">  
          <div class="info-box">
            <span class="info-box-icon bg-purple"><i class="fas fa-book"></i></span>
            <div class="info-box-content">
              <span class="info-box-text"><?= $termInfo->name ?></span>
              <?php if(isset($termSessionInfo)): ?>
                <span class="info-box-number" style="font-size: 12px; font-weight: normal;">
                  Start: <?= date('M j, Y', strtotime($termSessionInfo->start_date)) ?><br>
                  End: <?= date('M j, Y', strtotime($termSessionInfo->end_date)) ?>
                </span>
              <?php endif; ?>
            </div>
          </div>
        </a>
      </div>
    <?php endif; ?>

    <?php if(hasPermission('admin-db-week') && isset($termWeeksInfo)): ?>
      <div class="col-md-3 col-sm-6 col-12">
        <a href="<?= base_url('admin/weeks') ?>"> 
          <div class="info-box">
            <span class="info-box-icon bg-teal"><i class="fas fa-calendar-week"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Week: <?= $termWeeksInfo->week_name ?></span>
              <span class="info-box-number" style="font-size: 12px; font-weight: normal;">
                Start: <?= date('M j, Y', strtotime($termWeeksInfo->start_date)) ?><br>
                End: <?= date('M j, Y', strtotime($termWeeksInfo->end_date)) ?>
              </span>
            </div>
          </div>
        </a>
      </div>
    <?php endif; ?>

    <?php if(hasPermission('admin-db-exam') && isset($examsInfo)): ?>
      <div class="col-md-3 col-sm-6 col-12">
        <a href="<?= base_url('admin/exams') ?>"> 
          <div class="info-box">
            <span class="info-box-icon bg-orange"><i class="fas fa-file-alt"></i></span>
            <div class="info-box-content">
              <span class="info-box-text"><?= $examsInfo->exam_name ?></span>
              <span class="info-box-number" style="font-size: 12px; font-weight: normal;">
                Start: <?= date('M j, Y', strtotime($examsInfo->exam_start_date)) ?><br>
                End: <?= date('M j, Y', strtotime($examsInfo->exam_end_date)) ?>
              </span>
            </div>
          </div>
        </a>
      </div>
    <?php endif; ?>

    <?php if(hasPermission('admin-db-expense')): ?>
      <div class="col-md-3 col-sm-6 col-12">
        <a href="<?= base_url('admin/profit_loss_report') ?>"> 
          <div class="info-box">
            <span class="info-box-icon bg-maroon"><i class="fas fa-calculator"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Expense (<?= date('M Y') ?>)</span>
              <span class="info-box-number" style="font-size: 12px; font-weight: normal;">
                Collection: <?= $totalCollection ?? 0 ?><br>
                Expense: <?= $expenseInfoTotal ?? 0 ?><br>
                Profit/Loss: <?= ($totalCollection ?? 0) - ($expenseInfoTotal ?? 0) ?>
              </span>
            </div>
          </div>
        </a>
      </div>
    <?php endif; ?>
  </div>

<?php if (hasPermission('admin-db-attendance')): ?>
<div class="row mt-3">
  <div class="col-md-12">
    <div class="card card-warning">
      <div class="card-header">
        <h3 class="card-title">Pending Attendance — <?= date('D j M Y', strtotime($attendanceDate ?? date('Y-m-d'))) ?></h3>
        <div class="card-tools">
          <span class="badge text-bg-danger"><?= (int)($pendingCount ?? 0) ?> pending</span>
        </div>
      </div>

      <div class="card-body p-0" style="max-height: 320px; overflow:auto;">
        <?php if (!empty($pendingAttendance)): ?>
          <table class="table table-sm table-striped mb-0">
            <thead>
              <tr>
                <th style="width:80px;">Section ID</th>
                <th>Class - Section</th>
                <th>Teacher</th>
                <th class="text-center" style="width:80px;">Strength</th>
                <th class="text-center" style="width:60px;">P</th>
                <th class="text-center" style="width:60px;">A</th>
                <th class="text-center" style="width:60px;">L</th>
                <th class="text-center" style="width:70px;">EL</th>
                <th class="text-center" style="width:70px;">LC</th>
                <th style="width:120px;">Action</th>
                </tr>
            </thead>
            <tbody>
              <?php foreach ($pendingAttendance as $row): ?>
                  <tr>
                    <td><?= esc($row['cls_sec_id']) ?></td>
                    <td><?= esc(($row['class_name'] ?? 'Class') . ' - ' . ($row['section_name'] ?? 'Section')) ?></td>
                    <td><?= esc($row['teacher_name'] ?? '—') ?></td>
                    <td class="text-center"><?= (int)($row['strength'] ?? 0) ?></td>
                    <td class="text-center text-success"><?= (int)($row['present_count'] ?? 0) ?></td>
                    <td class="text-center text-danger"><?= (int)($row['absent_count'] ?? 0) ?></td>
                    <td class="text-center text-warning"><?= (int)($row['leave_count'] ?? 0) ?></td>
                    <td class="text-center"><?= (int)($row['el_count'] ?? 0) ?></td>
                    <td class="text-center"><?= (int)($row['lc_count'] ?? 0) ?></td>
                    <td>
                      <a class="btn btn-sm btn-primary"
                         href="<?= base_url('admin/students_attendance/add?cls_sec_id=' . urlencode($row['cls_sec_id']) . '&date=' . urlencode($attendanceDate ?? date('Y-m-d'))) ?>">
                        Mark now
                      </a>
                    </td>
                  </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="p-3">🎉 All classes have marked attendance for today.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

  <!-- Charts Section -->
  <div class="row mt-3">
    <?php if(hasPermission('admin-db-fee-collection') && isset($monthlyFee)): ?>
      <div class="col-md-6">
        <div class="card card-danger">
          <div class="card-header">
            <h3 class="card-title"><?= $monthlyFee->fee_type_name ?> Collection (<?= date('M Y') ?>)</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
              <button type="button" class="btn btn-tool" data-card-widget="remove">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <canvas id="pieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if(isset($academic_session)): ?>
      <!-- Fee Collection Chart -->
<div class="col-md-6">
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">
                Fee Collection 
                <?php if (isset($selectedSession) && $selectedSession): ?>
                    (<?= esc($selectedSession->session_name) ?>)
                <?php else: ?>
                    (Last 12 Months)
                <?php endif; ?>
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <canvas id="stackedBarChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
        </div>
    </div>
</div>
    <?php endif; ?>
  </div>

  <!-- Attendance and Strength Charts -->
  <div class="row mt-3">
    <?php if (hasPermission('admin-db-attendance')): ?>
    <div class="col-md-6">
      <div class="card card-info">
        <div class="card-header">
          <h3 class="card-title">Attendance (<?= date('D j M Y') ?>)</h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <canvas id="pieChartAttendance" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
        </div>
      </div>
       <?php endif; ?>
    </div>

    <div class="col-md-6">
      <?php if (hasPermission('admin-db-attendance')): ?>
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">Current Student Strength</h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <canvas id="stackedBarChartSection" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- QR Scanner Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode me-2"></i>
                    Scan Campus QR Code
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qr-reader" style="width: 100%;"></div>
                <div id="qr-reader-results" class="mt-3"></div>
                <div class="mt-3 alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <small>Point your camera at the QR code displayed in the admin office</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Load scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/bootstrap5-compat.js?v=20260614') ?>"></script>


<!-- Load QR Scanner Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let html5QrCode = null;
    let isScanning = false;
    
    // ============================================
    // Update compact date/time in header
    // ============================================
    function updateDateTime() {
        const now = new Date();
        
        // Day name
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const dayName = days[now.getDay()];
        
        // Date
        const day = now.getDate();
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const month = monthNames[now.getMonth()];
        const year = now.getFullYear();
        const dateStr = `${day} ${month} ${year}`;
        
        // Time
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        const timeStr = `${hours}:${minutes}:${seconds} ${ampm}`;
        
        // Update DOM
        document.getElementById('currentDayCompact').textContent = dayName;
        document.getElementById('currentDateCompact').textContent = dateStr;
        document.getElementById('currentTimeCompact').textContent = timeStr;
    }
    
    // Update time every second
    updateDateTime();
    setInterval(updateDateTime, 1000);
    
    // ============================================
    // Load Attendance Status (Compact Design)
    // ============================================
    function loadAttendanceStatus() {
        const container = document.getElementById('attendanceStatusContainer');
        if (!container) return;
        
        container.innerHTML = `
            <div class="bg-white text-dark rounded p-3 text-center">
                <div class="spinner-border text-primary spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="ms-2">Loading status...</span>
            </div>
        `;
        
        fetch('/admin/get-recent-attendance', {
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            cache: 'no-cache'
        })
        .then(response => response.json())
        .then(data => {
            if (!container) return;
            
            // Get today's date
            const today = new Date();
            const day = today.getDate();
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const month = monthNames[today.getMonth()];
            const year = today.getFullYear();
            const todayStr = `${day} ${month} ${year}`;
            
            let attendanceArray = Array.isArray(data) ? data : (data.attendance || []);
            const todayAtt = attendanceArray.find(a => a.date === todayStr);
            
            if (todayAtt && todayAtt.checkin && todayAtt.checkin !== '-') {
                if (todayAtt.checkout && todayAtt.checkout !== '-') {
                    // Completed Attendance
                    container.innerHTML = `
                        <div class="bg-white text-dark rounded p-3" style="border-start: 4px solid #28a745;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span class="fw-bold ms-1">Completed</span>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">
                                        <i class="fas fa-sign-in-alt text-info"></i> ${todayAtt.checkin} &nbsp;
                                        <i class="fas fa-sign-out-alt text-warning"></i> ${todayAtt.checkout}
                                    </small>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    // Checked In Only
                    const isLate = todayAtt.status === 'late';
                    container.innerHTML = `
                        <div class="bg-white text-dark rounded p-3" style="border-start: 4px solid ${isLate ? '#ffc107' : '#17a2b8'};">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-clock ${isLate ? 'text-warning' : 'text-info'}"></i>
                                    <span class="fw-bold ms-1">Checked In</span>
                                    ${isLate ? '<span class="badge bg-warning ms-2">Late</span>' : '<span class="badge bg-info ms-2">On Time</span>'}
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold">${todayAtt.checkin}</span>
                                    <br><small class="text-muted">Scan again to checkout</small>
                                </div>
                            </div>
                        </div>
                    `;
                }
            } else {
                // Not Checked In
                container.innerHTML = `
                    <div class="bg-white text-dark rounded p-3" style="border-start: 4px solid #6c757d;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-hourglass-half text-secondary"></i>
                                <span class="ms-1">Not Checked In</span>
                            </div>
                            <div class="text-end">
                                <i class="fas fa-qrcode text-primary"></i>
                                <small>Scan QR code</small>
                            </div>
                        </div>
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error('Error:', err);
            if (container) {
                container.innerHTML = `
                    <div class="bg-white text-dark rounded p-3 text-center" style="border-start: 4px solid #dc3545;">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        <span class="ms-1">Unable to load status</span>
                        <button class="btn btn-sm btn-link" onclick="location.reload()">Refresh</button>
                    </div>
                `;
            }
        });
    }
    
    // ============================================
    // Load Recent Attendance (Compact Table)
    // ============================================
    function loadRecentAttendance() {
        const container = document.getElementById('recentAttendanceList');
        if (!container) return;
        
        container.innerHTML = '<div class="text-center text-muted py-2"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
        
        fetch('/admin/get-recent-attendance', {
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            cache: 'no-cache'
        })
        .then(response => response.json())
        .then(data => {
            if (!container) return;
            
            let attendanceArray = [];
            if (data && Array.isArray(data)) {
                attendanceArray = data;
            } else if (data && data.attendance && Array.isArray(data.attendance)) {
                attendanceArray = data.attendance;
            }
            
            if (attendanceArray.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-2">No records found</div>';
                return;
            }
            
            // Compact table
            let html = `
                <table class="table table-sm table-hover mb-0" style="font-size: 12px;">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            attendanceArray.slice(0, 8).forEach(att => {
                const checkin = att.checkin || '-';
                const checkout = att.checkout || '-';
                const status = att.status || 'present';
                
                let statusBadge = status === 'late' ? '<span class="badge bg-warning">Late</span>' : 
                                 status === 'present' ? '<span class="badge bg-success">Present</span>' : 
                                 '<span class="badge bg-secondary">-</span>';
                
                html += `
                    <tr>
                        <td><strong>${att.date || '-'}</strong></td>
                        <td><i class="fas fa-sign-in-alt text-info"></i> ${checkin}</td>
                        <td><i class="fas fa-sign-out-alt text-warning"></i> ${checkout}</td>
                        <td>${statusBadge}</td>
                    </tr>
                `;
            });
            
            html += `
                    </tbody>
                </table>
            `;
            
            container.innerHTML = html;
        })
        .catch(err => {
            console.error('Error:', err);
            container.innerHTML = '<div class="alert alert-danger mb-0 py-1">Error loading history</div>';
        });
    }
    
    // ============================================
    // QR Scanner Functions (Keep existing)
    // ============================================
    function startScanner() {
        if (isScanning) return;
        
        const readerElement = document.getElementById('qr-reader');
        if (!readerElement) {
            showToast('error', 'Scanner element not found');
            return;
        }
        
        if (typeof Html5Qrcode === 'undefined') {
            const resultsDiv = document.getElementById('qr-reader-results');
            if (resultsDiv) {
                resultsDiv.innerHTML = '<div class="alert alert-danger">QR Scanner library failed to load. Please refresh the page.</div>';
            }
            showToast('error', 'QR Scanner library not loaded');
            return;
        }
        
        try {
            html5QrCode = new Html5Qrcode("qr-reader");
            const config = { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };
            
            html5QrCode.start({ facingMode: "environment" }, config, 
                (decodedText) => {
                    if (html5QrCode && isScanning) {
                        html5QrCode.stop().catch(e => console.error('Stop error:', e));
                        isScanning = false;
                    }
                    
                    const resultsDiv = document.getElementById('qr-reader-results');
                    if (resultsDiv) resultsDiv.innerHTML = '<div class="alert alert-info">Processing...</div>';
                    
                    fetch('/admin/process-teacher-attendance', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: 'qr_code=' + encodeURIComponent(decodedText)
                    })
                    .then(response => response.json())
                    .then(data => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('qrScannerModal'));
                        if (modal) modal.hide();
                        
                        if (data.success) {
                            showToast('success', data.message || 'Attendance recorded');
                            loadAttendanceStatus();
                            loadRecentAttendance();
                        } else {
                            showToast('error', data.message || 'Failed');
                        }
                        if (resultsDiv) resultsDiv.innerHTML = '';
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        showToast('error', 'Network error');
                        if (resultsDiv) resultsDiv.innerHTML = '';
                    });
                },
                (errorMessage) => {
                    console.debug('QR Scan error:', errorMessage);
                }
            ).catch(err => {
                console.error('Scanner start error:', err);
                showToast('error', 'Camera error');
                isScanning = false;
                const resultsDiv = document.getElementById('qr-reader-results');
                if (resultsDiv) resultsDiv.innerHTML = '';
            });
            
            isScanning = true;
        } catch (err) {
            console.error('Scanner initialization error:', err);
            showToast('error', 'Failed to initialize scanner');
            isScanning = false;
        }
    }
    
    function stopScanner() {
        if (html5QrCode && isScanning) {
            html5QrCode.stop().catch(e => console.error('Stop error:', e));
            isScanning = false;
        }
    }
    
    function showToast(type, message) {
        if (typeof toastr !== 'undefined') {
            if (type === 'success') toastr.success(message);
            else if (type === 'error') toastr.error(message);
            else if (type === 'warning') toastr.warning(message);
        } else {
            alert(message);
        }
    }
    
    // ============================================
    // Modal Event Handlers
    // ============================================
    const modal = document.getElementById('qrScannerModal');
    if (modal) {
        modal.addEventListener('shown.bs.modal', startScanner);
        modal.addEventListener('hidden.bs.modal', function() {
            stopScanner();
            const resultsDiv = document.getElementById('qr-reader-results');
            if (resultsDiv) resultsDiv.innerHTML = '';
        });
    }
    
    // ============================================
    // Initialize
    // ============================================
    loadAttendanceStatus();
    loadRecentAttendance();
});
</script>
<?= $this->endSection() ?>