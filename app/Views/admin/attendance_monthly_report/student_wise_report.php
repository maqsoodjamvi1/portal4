<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>


<style>
    @media print {
        .no-print { display: none !important; }
        .page-break { page-break-after: always; }
        body { font-size: 12px; }
        .container { width: 100% !important; }
        .card { border: none !important; }
        .card-header { background: #f8f9fa !important; }
    }
    
    .report-page {
        margin: 20px 0;
        padding: 20px;
        background: white;
        border: 1px solid #ddd;
    }
    
    .student-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 5px;
    }
    
    .student-photo {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #dee2e6;
        margin-right: 20px;
    }
    
    .student-info h4 {
        margin: 0 0 5px 0;
        color: #2c3e50;
    }
    
    .student-info p {
        margin: 2px 0;
        color: #7f8c8d;
    }
    
    .session-info {
        text-align: center;
        margin: 20px 0;
        padding: 10px;
        background: #3498db;
        color: white;
        border-radius: 5px;
    }
    
    .attendance-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }
    
    .attendance-table th {
        background: #34495e;
        color: white;
        padding: 8px 4px;
        text-align: center;
        border: 1px solid #ddd;
    }
    
    .attendance-table td {
        padding: 4px 2px;
        text-align: center;
        border: 1px solid #ddd;
        min-width: 20px;
    }
    
    .month-row {
        background: #ecf0f1;
        font-weight: bold;
    }
    
    .present {
        background: #d5f4e6;
        color: #27ae60;
    }
    
    .absent {
        background: #fadbd8;
        color: #e74c3c;
        font-weight: bold;
    }
    
    .leave {
        background: #fef9e7;
        color: #f39c12;
    }
    
    .late {
        background: #e8f4fc;
        color: #3498db;
    }
    
    .weekend {
        background: #f8f9fa;
        color: #95a5a6;
    }
    
    .holiday {
        background: #fff5e6;
        color: #e67e22;
    }
    
    .day-header {
        background: #2c3e50;
        color: white;
    }
    
    .controls {
        margin-bottom: 20px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
    }
    
    .summary-card {
        background: #f8f9fa;
        border-left: 4px solid #3498db;
        padding: 15px;
        margin-top: 20px;
        border-radius: 5px;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .summary-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
</style>

<div class="controls no-print">
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fas fa-print"></i> Print Report
    </button>
    <button onclick="window.history.back()" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </button>
    <a href="<?= base_url('admin/attendance-monthly-report/student-session-report') ?>" class="btn btn-info">
        <i class="fas fa-search"></i> New Report
    </a>
</div>

<div class="report-page">
    <!-- Student Header -->
    <div class="student-header">
        <?php
        $photoPath = FCPATH . 'uploads/' . ($student->profile_photo ?? '');
        $photoUrl = (file_exists($photoPath) && !empty($student->profile_photo)) 
            ? base_url('uploads/' . $student->profile_photo)
            : base_url('assets/img/default-avatar.png');
        ?>
        <img src="<?= $photoUrl ?>" alt="Student Photo" class="student-photo">
        <div class="student-info">
            <h4><?= esc($student->first_name . ' ' . ($student->last_name ?? '')) ?></h4>
            <p><strong>Registration No:</strong> <?= esc($student->reg_no ?? 'N/A') ?></p>
            <p><strong>Father Name:</strong> <?= esc($student->father_name ?? 'N/A') ?></p>
            <p><strong>Class/Section:</strong> <?= esc($student->section_name ?? 'N/A') ?></p>
            <p><strong>Date of Birth:</strong> <?= !empty($student->date_of_birth) ? date('d-m-Y', strtotime($student->date_of_birth)) : 'N/A' ?></p>
        </div>
    </div>
    
    <!-- Session Info -->
    <div class="session-info">
        <h3>Session: <?= esc($session->session_name ?? 'N/A') ?></h3>
        <p>Attendance Report - Monthly Grid View</p>
        <p>
            <strong>Session Period:</strong> 
            <?= !empty($session->start_date) ? date('d-m-Y', strtotime($session->start_date)) : 'N/A' ?> 
            to 
            <?= !empty($session->end_date) ? date('d-m-Y', strtotime($session->end_date)) : 'N/A' ?>
        </p>
    </div>
    
  <!-- Attendance Grid -->
<table class="attendance-table">
    <thead>
        <tr class="day-header">
            <th>Month</th>
            <?php 
            // Determine max days needed for table
            $maxDays = 31;
            if (!empty($sessionMonths)) {
                $maxDays = 0;
                foreach ($sessionMonths as $monthData) {
                    if ($monthData['total_days_in_session'] > $maxDays) {
                        $maxDays = $monthData['total_days_in_session'];
                    }
                }
            }
            
            for ($day = 1; $day <= $maxDays; $day++): ?>
                <th><?= str_pad($day, 2, '0', STR_PAD_LEFT) ?></th>
            <?php endfor; ?>
        </tr>
    </thead>
    <tbody>
        <?php
        // Calculate summary
        $summary = [
            'total_present' => 0,
            'total_absent' => 0,
            'total_leave' => 0,
            'total_late' => 0,
            'total_el' => 0,
            'total_lc' => 0,
            'total_le' => 0,
            'total_working_days' => 0,
            'total_holidays' => 0,
        ];
        
        if (!empty($sessionMonths)): 
            foreach ($sessionMonths as $monthName => $monthData): 
                $monthYear = $monthData['year'] . '-' . $monthData['month_num'];
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthData['month_num'], $monthData['year']);
                ?>
                <tr class="month-row">
                    <td><strong><?= $monthName ?> <?= $monthData['year'] ?></strong></td>
                    <?php
                    for ($day = 1; $day <= $maxDays; $day++):
                        if ($day > $daysInMonth || !in_array($day, $monthData['days_in_session'])) {
                            // Day doesn't exist in this month or not in session
                            echo '<td class="weekend">-</td>';
                            continue;
                        }
                        
                        $currentDate = DateTime::createFromFormat('Y-m-d', $monthYear . '-' . str_pad($day, 2, '0', STR_PAD_LEFT));
                        
                        // Check if it's weekend (Saturday or Sunday)
                        $dayOfWeek = $currentDate->format('N'); // 1=Monday, 7=Sunday
                        $isWeekend = ($dayOfWeek >= 6);
                        
                        // Check if it's holiday
                        $isHoliday = isset($holidays[$monthName][$day]);
                        
                        // Get attendance status
                        $statusData = $attendanceData[$monthName][$day] ?? null;
                        $status = $statusData['status'] ?? '';
                        
                        $class = '';
                        $display = '';
                        
                        if ($isHoliday) {
                            $class = 'holiday';
                            $display = 'H';
                            $summary['total_holidays']++;
                        } elseif ($isWeekend) {
                            $class = 'weekend';
                            $display = 'W';
                        } else {
                            // Working day
                            $summary['total_working_days']++;
                            
                            if ($status == 'P') {
                                $class = 'present';
                                $display = 'P';
                                $summary['total_present']++;
                            } elseif ($status == 'A') {
                                $class = 'absent';
                                $display = 'A';
                                $summary['total_absent']++;
                            } elseif ($status == 'L') {
                                $class = 'leave';
                                $display = 'L';
                                $summary['total_leave']++;
                            } elseif ($status == 'LC') {
                                $class = 'late';
                                $display = 'LC';
                                $summary['total_lc']++;
                                $summary['total_present']++; // Count as present
                            } elseif ($status == 'EL') {
                                $class = 'late';
                                $display = 'EL';
                                $summary['total_el']++;
                                $summary['total_present']++; // Count as present
                            } elseif ($status == 'LE') {
                                $class = 'late';
                                $display = 'LE';
                                $summary['total_le']++;
                                $summary['total_present']++; // Count as present
                            } else {
                                // Working day but no attendance recorded
                                $display = '';
                            }
                        }
                        
                        // Add tooltip for remarks if available
                        $tooltip = '';
                        if ($statusData && !empty($statusData['remarks'])) {
                            $tooltip = ' title="' . htmlspecialchars($statusData['remarks']) . '"';
                        }
                        
                        echo "<td class='{$class}'{$tooltip}>{$display}</td>";
                    endfor;
                    ?>
                </tr>
            <?php endforeach;
        else: ?>
            <tr>
                <td colspan="<?= $maxDays + 1 ?>" class="text-center">No session months found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
    
  <!-- Summary -->
<div class="summary-card">
    <h5>Attendance Summary - Session: <?= esc($session->session_name) ?></h5>
    <div class="row">
        <div class="col-md-6">
            <div class="summary-item">
                <span>Session Period:</span>
                <strong><?= date('d-m-Y', strtotime($session->start_date)) ?> to <?= date('d-m-Y', strtotime($session->end_date)) ?></strong>
            </div>
            <div class="summary-item">
                <span>Total Working Days:</span>
                <strong><?= $summary['total_working_days'] ?></strong>
            </div>
            <div class="summary-item">
                <span>Total Holidays:</span>
                <strong class="text-dark"><?= $summary['total_holidays'] ?></strong>
            </div>
            <div class="summary-item">
                <span>Total Present:</span>
                <strong class="text-success"><?= $summary['total_present'] ?></strong>
            </div>
            <div class="summary-item">
                <span>Total Absent:</span>
                <strong class="text-danger"><?= $summary['total_absent'] ?></strong>
            </div>
        </div>
        <div class="col-md-6">
            <div class="summary-item">
                <span>Total Leave:</span>
                <strong class="text-warning"><?= $summary['total_leave'] ?></strong>
            </div>
            <div class="summary-item">
                <span>Late Coming (LC):</span>
                <strong class="text-info"><?= $summary['total_lc'] ?></strong>
            </div>
            <div class="summary-item">
                <span>Early Leave (EL):</span>
                <strong class="text-info"><?= $summary['total_el'] ?></strong>
            </div>
            <div class="summary-item">
                <span>Late & Early Leave (LE):</span>
                <strong class="text-info"><?= $summary['total_le'] ?></strong>
            </div>
            <?php if ($summary['total_working_days'] > 0): ?>
                <div class="summary-item">
                    <span>Attendance Percentage:</span>
                    <strong class="text-primary">
                        <?= number_format(($summary['total_present'] / $summary['total_working_days']) * 100, 2) ?>%
                    </strong>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Legend -->
<div style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px; font-size: 12px;">
    <strong>Legend:</strong>
    <span class="badge badge-success ml-2">P = Present</span>
    <span class="badge badge-danger ml-2">A = Absent</span>
    <span class="badge badge-warning ml-2">L = Leave</span>
    <span class="badge badge-info ml-2">LC = Late Coming</span>
    <span class="badge badge-info ml-2">EL = Early Leave</span>
    <span class="badge badge-info ml-2">LE = Late & Early Leave</span>
    <span class="badge badge-secondary ml-2">W = Weekend</span>
    <span class="badge badge-dark ml-2">H = Holiday</span>
    <span class="badge badge-light ml-2">- = Not in Session</span>
</div>
   
    <!-- Footer -->
    <div style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #dee2e6; text-align: center; font-size: 11px; color: #6c757d;">
        <p>Generated on: <?= date('d-m-Y H:i:s') ?></p>
        <p>Report ID: ATT-<?= strtoupper(uniqid()) ?></p>
    </div>
</div>

<?= $this->endSection() ?>