<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
        <h5 class="mb-0"><i class="fa fa-calendar-check me-2 text-primary"></i> This Week's Attendance</h5>
        <?php if (!empty($currentWeekAttendance)): ?>
            <div class="mt-2 mt-sm-0">
                
                <span class="badge bg-danger me-2">Absent: <?= $currentWeekAttendance['absent_days'] ?></span>
                
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (!empty($currentWeekAttendance['week_days'])): ?>
            <div class="attendance-grid">
                <?php foreach ($currentWeekAttendance['week_days'] as $day): ?>
                    <div class="attendance-day">
                        <div class="fw-bold"><?= esc($day['day_short']) ?></div>
                        <div class="attendance-status <?= $day['status_class'] ?>">
                            <?= esc($day['status']) ?>
                        </div>
                        <?php if ($day['status'] !== '—' && $day['status'] !== 'OFF'): ?>
                         
                        <?php elseif ($day['status'] === 'OFF' && !$day['is_school_day']): ?>
                            <small class="text-muted d-block mt-1">Holiday</small>
                        <?php elseif ($day['status'] === 'OFF' && $day['is_past_day']): ?>
                            
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-3 text-muted small">
                <i class="fa fa-info-circle me-1"></i>
                <?php if ($currentWeekAttendance['working_days'] > 0): ?>
                    Attendance: <strong><?= $currentWeekAttendance['attendance_percentage'] ?>%</strong>
                <?php else: ?>
                    No school days this week
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-3 text-muted">
                <i class="fa fa-calendar fa-2x mb-2 opacity-50"></i>
                <p>No attendance data available.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
