<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<?php
  $activeId     = (int) ($activeStudentId ?? 0);
  $feeSummary   = $feeSummary ?? ['total_outstanding' => 0, 'by_student' => []];
  $byStudent    = $feeSummary['by_student'] ?? [];
  $totalDue     = (float) ($feeSummary['total_outstanding'] ?? 0);
  $sportsEvents = $sportsEvents ?? [];
  $parentName   = $name ?? '';
  $quizzes      = $quizzes ?? [];
  $children     = $children ?? [];

  // --- Helper arrays + summary metrics ---
  $childIds = [];
  foreach ($children as $c) {
      if (!empty($c['student_id'])) {
          $childIds[] = (int) $c['student_id'];
      }
  }

  // Quiz metrics
  $totalAttemptsCount = 0;
  $totalPossibleAttempts = 0;
  $totalAvailableAttempts = 0;
  $quizAttemptedCount = 0;
  
  foreach ($quizzes as $qq) {
      $status = $qq['quiz_status'] ?? 'live';
      $attempts = (int)($qq['attempts_count'] ?? 0);
      $maxAttempts = (int)($qq['max_attempts'] ?? 0);
      
      if ($status === 'closed') continue;
      
      if ($attempts > 0) $quizAttemptedCount++;
      
      $totalAttemptsCount += $attempts;
      
      if ($maxAttempts === 0) {
          $totalAvailableAttempts += 999;
      } else {
          $totalPossibleAttempts += $maxAttempts;
          $totalAvailableAttempts += max(0, $maxAttempts - $attempts);
      }
  }
  
  $quizPlayableCount = $totalAvailableAttempts;

  // Sports summary
  $sportsEventCount = count($sportsEvents);
  $sportsParticipationCount = 0;
  foreach ($sportsEvents as $ev) {
      $parts = $ev['participants'] ?? [];
      foreach ($parts as $p) {
          if (in_array((int)$p['student_id'], $childIds, true)) {
              $sportsParticipationCount++;
          }
      }
  }

  // Group quizzes by student
  $quizzesByStudent = [];
  foreach ($quizzes as $quiz) {
      $studentId = (int) ($quiz['student_id'] ?? 0);
      $quizStatus = $quiz['quiz_status'] ?? 'live';
      
      if ($quizStatus === 'closed') continue;
      
      if (!isset($quizzesByStudent[$studentId])) {
          $quizzesByStudent[$studentId] = [
              'student_info' => [
                  'student_id' => $studentId,
                  'name' => $quiz['student_name'] ?? ($quiz['student_full_name'] ?? ''),
                  'class' => trim(($quiz['class_name'] ?? '') . ' ' . ($quiz['section_name'] ?? '')),
                  'profile_photo' => $quiz['student_photo_url'] ?? base_url('resource/img/avatar-student.png'),
                  'reg_no' => $quiz['reg_no'] ?? ''
              ],
              'quizzes' => [],
              'stats' => [
                  'total_quizzes' => 0,
                  'total_attempts' => 0,
                  'available_attempts' => 0
              ]
          ];
      }
      
      $quizzesByStudent[$studentId]['quizzes'][] = $quiz;
      
      $attempts = (int)($quiz['attempts_count'] ?? 0);
      $maxAttempts = (int)($quiz['max_attempts'] ?? 0);
      
      $quizzesByStudent[$studentId]['stats']['total_quizzes']++;
      $quizzesByStudent[$studentId]['stats']['total_attempts'] += $attempts;
      
      if ($maxAttempts === 0) {
          $quizzesByStudent[$studentId]['stats']['available_attempts'] += 999;
      } else {
          $quizzesByStudent[$studentId]['stats']['available_attempts'] += max(0, $maxAttempts - $attempts);
      }
  }
?>

<style>
/* ===== IMPROVED LAYOUT ===== */
.student-grid-parent {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
  margin-top: 10px;
}

.student-card {
  background: white;
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  border: 1px solid #e5e7eb;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  position: relative;
}

.student-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
}

/* Clear separation between quiz cards */
.quiz-sub-card {
  padding: 15px;
  border-bottom: 2px solid #f1f5f9;
  margin-bottom: 10px;
  background: #ffffff;
  border-radius: 12px;
  border-start: 4px solid #4f46e5;
}

.quiz-sub-card:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

/* Quiz header - highlighted - DESKTOP */
.quiz-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  padding: 10px 12px;
  background: linear-gradient(135deg, #f8fafc, #e0e7ff);
  border-radius: 8px;
  border-start: 4px solid #4f46e5;
}

.quiz-subject {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  color: #1e293b;
  font-size: 1rem;
  flex: 1;
}

.quiz-subject i {
  color: #4f46e5;
}

.quiz-meta-center {
  display: flex;
  align-items: center;
  gap: 15px;
  font-size: 0.9rem;
}

.question-count {
  background: #4f46e5;
  color: white;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
}

.quiz-status {
  font-size: 0.85rem;
  padding: 4px 12px;
  border-radius: 999px;
  font-weight: 600;
  white-space: nowrap;
}

.status-live {
  background: #dcfce7;
  color: #15803d;
  border: 1px solid #86efac;
}

.status-upcoming {
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fde68a;
}

/* Student Header */
.student-header {
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  padding: 20px;
  color: white;
  position: relative;
  display: flex;
  align-items: center;
  gap: 15px;
}

.student-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  border: 4px solid rgba(255, 255, 255, 0.3);
  object-fit: cover;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.student-info {
  flex: 1;
}

.student-name {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 5px;
}

.student-class {
  font-size: 0.95rem;
  opacity: 0.9;
}

/* Student Stats */
.student-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  padding: 15px 20px;
  background: #f8fafc;
  border-bottom: 1px solid #e5e7eb;
}

.stat-item {
  text-align: center;
  padding: 8px;
}

.stat-value {
  font-size: 1.8rem;
  font-weight: 700;
  display: block;
  line-height: 1;
}

.stat-label {
  font-size: 0.85rem;
  color: #64748b;
  margin-top: 4px;
  font-weight: 500;
}

/* Attempts Row - Improved DESKTOP */
.attempts-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 15px 0;
  padding: 12px;
  background: #f1f5f9;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
}

.attempts-left {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
}

.attempts-center {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  border-start: 1px solid #cbd5e1;
  border-end: 1px solid #cbd5e1;
}

.attempts-right {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
}

.attempts-label {
  font-size: 0.85rem;
  color: #64748b;
  margin-bottom: 4px;
  font-weight: 500;
}

.attempts-value {
  font-size: 1.4rem;
  font-weight: 700;
}

.attempts-count {
  color: #4f46e5;
}

.remaining-count {
  color: #10b981;
}

.duration-count {
  color: #f59e0b;
}

/* Toppers - Photo, position, percentage only */
.toppers-list {
  display: flex;
  gap: 15px;
  justify-content: space-around;
  margin-top: 10px;
}

.topper-item {
  text-align: center;
  flex: 1;
}

.topper-avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  margin-bottom: 8px;
}

.topper-rank {
  font-size: 0.8rem;
  padding: 3px 10px;
  border-radius: 999px;
  font-weight: 700;
  margin-bottom: 4px;
  display: inline-block;
}

.rank-1 {
  background: linear-gradient(135deg, #fef3c7, #f59e0b);
  color: #92400e;
}

.rank-2 {
  background: linear-gradient(135deg, #e5e7eb, #6b7280);
  color: white;
}

.rank-3 {
  background: linear-gradient(135deg, #fcd34d, #f59e0b);
  color: #92400e;
}

.topper-score {
  font-size: 0.9rem;
  color: #059669;
  font-weight: 700;
  margin-top: 5px;
}

/* Actions Buttons - Always same row */
.quiz-actions {
  display: flex;
  gap: 10px;
  margin-top: 15px;
}

.btn-play, .btn-practice {
  flex: 1;
  padding: 10px 15px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.3s ease;
  cursor: pointer;
  border: none;
}

.btn-play {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.btn-play:hover {
  background: linear-gradient(135deg, #059669, #047857);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
}

.btn-practice {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
}

.btn-practice:hover {
  background: linear-gradient(135deg, #1d4ed8, #1e40af);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
}

.btn-play:disabled, .btn-practice:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

/* View Results Button */
.btn-view-results {
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #cbd5e1;
  padding: 10px 15px;
  border-radius: 8px;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.3s ease;
  cursor: pointer;
  width: 100%;
  margin-top: 10px;
}

.btn-view-results:hover {
  background: #e2e8f0;
  color: #334155;
}

/* Review Attempts - Just icon + number */
.attempt-review-section {
  margin: 12px 0;
  padding: 10px;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px dashed #cbd5e1;
}

.attempt-review-buttons {
  display: flex;
  justify-content: center;
  gap: 8px;
  flex-wrap: wrap;
}

.attempt-review-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 12px;
  background: #e0e7ff;
  color: #4f46e5;
  border-radius: 20px;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 600;
  transition: all 0.2s ease;
}

.attempt-review-btn:hover {
  background: #4f46e5;
  color: white;
  transform: translateY(-2px);
}

/* Remove score section completely */
.quiz-score-section {
  display: none;
}

/* ===== MOBILE OPTIMIZATIONS ===== */
@media (max-width: 768px) {
  .student-grid-parent {
    grid-template-columns: 1fr;
  }
  
  /* Student header mobile */
  .student-header {
    padding: 15px;
    flex-direction: row;
  }
  
  .student-avatar {
    width: 60px;
    height: 60px;
  }
  
  .student-name {
    font-size: 1.2rem;
  }
  
  /* Quiz header mobile - ALL IN ONE ROW */
  .quiz-header-row {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    padding: 8px 10px;
    margin-bottom: 10px;
    flex-wrap: nowrap;
    overflow: hidden;
  }
  
  .quiz-subject {
    flex: 1;
    font-size: 0.9rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    min-width: 0;
  }
  
  .quiz-subject i {
    font-size: 0.8rem;
    min-width: 16px;
  }
  
  .quiz-meta-center {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    margin-left: 8px;
  }
  
  .question-count {
    font-size: 0.75rem;
    padding: 3px 8px;
    white-space: nowrap;
  }
  
  .quiz-status {
    font-size: 0.75rem;
    padding: 3px 8px;
    white-space: nowrap;
    flex-shrink: 0;
  }
  
  /* Attempts row mobile - ALL IN ONE ROW */
  .attempts-row {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    margin: 12px 0;
    gap: 0;
  }
  
  .attempts-left, .attempts-center, .attempts-right {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    padding: 0 5px;
  }
  
  .attempts-center {
    border-start: 1px solid #cbd5e1;
    border-end: 1px solid #cbd5e1;
  }
  
  .attempts-label {
    font-size: 0.75rem;
    margin-bottom: 2px;
    text-align: center;
  }
  
  .attempts-value {
    font-size: 1.1rem;
  }
  
  /* Toppers - HORIZONTAL on mobile */
  .toppers-list {
    flex-direction: row;
    gap: 10px;
    justify-content: space-around;
  }
  
  .topper-item {
    flex: 1;
    min-width: 70px;
  }
  
  .topper-avatar {
    width: 50px;
    height: 50px;
  }
  
  .topper-rank {
    font-size: 0.7rem;
    padding: 2px 6px;
  }
  
  .topper-score {
    font-size: 0.8rem;
  }
  
  /* Play and Practice buttons - SAME ROW on mobile */
  .quiz-actions {
    display: flex;
    flex-direction: row;
    gap: 8px;
  }
  
  .btn-play, .btn-practice {
    padding: 8px 10px;
    font-size: 0.85rem;
    flex: 1;
  }
  
  .btn-view-results {
    padding: 8px 10px;
    font-size: 0.85rem;
  }
}

@media (max-width: 480px) {
  /* Extra small screens */
  .quiz-header-row {
    padding: 6px 8px;
  }
  
  .quiz-subject {
    font-size: 0.8rem;
  }
  
  .quiz-subject i {
    display: none; /* Hide icon on very small screens */
  }
  
  .question-count {
    font-size: 0.7rem;
    padding: 2px 6px;
  }
  
  .quiz-status {
    font-size: 0.7rem;
    padding: 2px 6px;
  }
  
  .attempts-label {
    font-size: 0.7rem;
  }
  
  .attempts-value {
    font-size: 1rem;
  }
  
  .topper-avatar {
    width: 40px;
    height: 40px;
  }
  
  .topper-rank {
    font-size: 0.65rem;
  }
  
  .topper-score {
    font-size: 0.75rem;
  }
}

/* ===== EXISTING STYLES (Keep these) ===== */
.summary-row {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 14px;
}
.summary-chip {
  flex: 1 1 130px;
  border-radius: 14px;
  padding: 8px 10px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  gap: 8px;
}
.summary-icon {
  width: 28px;
  height: 28px;
  border-radius: 999px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
}
.summary-content {
  flex: 1;
}
.summary-label {
  font-size: 11px;
  color: #6b7280;
}
.summary-value {
  font-size: 14px;
  font-weight: 700;
}
.summary-icon.children   { background:#eff6ff; color:#1d4ed8; }
.summary-icon.fees       { background:#fef2f2; color:#b91c1c; }
.summary-icon.sports     { background:#ecfdf3; color:#15803d; }
.summary-icon.quizzes    { background:#f5f3ff; color:#6d28d9; }
.summary-icon.attempted  { background:#fdf4ff; color:#a21caf; }

.parent-fee-card {
  border-radius: 16px;
  padding: 14px;
  background: radial-gradient(circle at top left, #eff6ff, #fef9c3);
  box-shadow: 0 3px 14px rgba(15,23,42,.08);
  margin-bottom: 18px;
}
.parent-fee-header {
  margin-bottom: 10px;
}
.parent-name-label {
  font-size: 11px;
  color: #6b7280;
}
.parent-name-value {
  font-size: 14px;
  font-weight: 700;
  color: #111827;
}
.parent-total-label {
  font-size: 11px;
  color: #6b7280;
}
.parent-total-value {
  font-size: 18px;
  font-weight: 800;
}

.dues-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 10px;
}
.dues-card {
  border-radius: 14px;
  padding: 10px 12px;
  display: flex;
  align-items: center;
  gap: 10px;
  background: #ffffffbb;
  box-shadow: 0 1px 8px rgba(15,23,42,.05);
}
.dues-photo {
  width: 40px;
  height: 40px;
  border-radius: 999px;
  object-fit: cover;
  box-shadow: 0 1px 6px rgba(15,23,42,.25);
}
.dues-main {
  flex: 1;
}
.dues-name {
  font-size: 13px;
  font-weight: 600;
  color: #111827;
}
.dues-reg {
  font-size: 11px;
  color: #6b7280;
}
.dues-amount {
  font-size: 14px;
  font-weight: 700;
}
.dues-amount.due {
  color: #b91c1c;
}
.dues-amount.clear {
  color: #16a34a;
}
.dues-chip {
  font-size: 10px;
  border-radius: 999px;
  padding: 2px 6px;
  background: #f97316;
  color: #fff;
}

.sports-events-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 12px;
}
.sports-event-card {
  border-radius: 14px;
  padding: 12px 14px;
  background: #ffffff;
  box-shadow: 0 2px 10px rgba(15,23,42,.06);
}
.sports-event-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 8px;
}
.sports-event-name {
  font-size: 14px;
  font-weight: 700;
  color: #111827;
}
.sports-event-date {
  font-size: 11px;
  color: #6b7280;
}
.sports-event-chip {
  font-size: 11px;
  border-radius: 999px;
  padding: 2px 8px;
  background: #e0f2fe;
  color: #0369a1;
}
.sports-participant-list {
  margin: 0;
  padding: 0;
  list-style: none;
  max-height: 210px;
  overflow-y: auto;
}
.sports-participant-row {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 0;
}
.sports-participant-avatar {
  width: 28px;
  height: 28px;
  border-radius: 999px;
  object-fit: cover;
}
.sports-participant-main {
  flex: 1;
}
.sports-participant-name {
  font-size: 12px;
  font-weight: 600;
  color: #111827;
}
.sports-participant-class {
  font-size: 11px;
  color: #6b7280;
}
.sports-participant-badge {
  font-size: 10px;
  border-radius: 999px;
  padding: 2px 6px;
  background: #eef2ff;
  color: #4338ca;
}

.academic-card {
  border-radius: 16px;
  padding: 14px;
  background: radial-gradient(circle at top left, #eef2ff, #fef9c3);
  box-shadow: 0 3px 14px rgba(15,23,42,.08);
  margin-bottom: 18px;
}
.academic-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}
.academic-title {
  font-size: 15px;
  font-weight: 700;
  color: #111827;
}
.academic-dates {
  font-size: 12px;
  color: #4b5563;
}
.academic-badge {
  font-size: 11px;
  border-radius: 999px;
  padding: 3px 8px;
  background: #22c55e;
  color: #fff;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.term-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 10px;
}
.term-card {
  border-radius: 12px;
  padding: 8px 10px;
  background: #ffffffcc;
  box-shadow: 0 1px 8px rgba(15,23,42,.05);
  border: 1px solid #e5e7eb;
}
.term-name {
  font-size: 13px;
  font-weight: 700;
  color: #111827;
  margin-bottom: 3px;
}
.term-dates {
  font-size: 11px;
  color: #6b7280;
}
</style>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">Parent Dashboard</h4>

      <?php if (!empty($children)): ?>
        <div class="d-flex align-items-center">
          <select class="form-select form-control" style="min-width:260px"
                  onchange="if(this.value){ location.href='<?= base_url('student/switch') ?>/'+this.value }">
            <?php foreach ($children as $c): ?>
              <option value="<?= (int)$c['student_id'] ?>" <?= $activeId === (int)$c['student_id'] ? 'selected' : '' ?>>
                <?= esc($c['name']); ?>  
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>
    </div>

    <?php if (empty($children)): ?>
      <div class="alert alert-info">No linked students found for your account.</div>
    <?php else: ?>

      <!-- SUMMARY CHIPS -->
      <div class="summary-row">
        <div class="summary-chip">
          <div class="summary-icon children">
            <i class="fa fa-users"></i>
          </div>
          <div class="summary-content">
            <div class="summary-label">Children</div>
            <div class="summary-value"><?= count($children) ?></div>
          </div>
        </div>

        <div class="summary-chip">
          <div class="summary-icon fees">
            <i class="fa fa-wallet"></i>
          </div>
          <div class="summary-content">
            <div class="summary-label">Total Unpaid (All)</div>
            <div class="summary-value text-danger"><?= number_format($totalDue, 0) ?></div>
          </div>
        </div>

        <div class="summary-chip">
          <div class="summary-icon sports">
            <i class="fa fa-running"></i>
          </div>
          <div class="summary-content">
            <div class="summary-label">Sports Events</div>
            <div class="summary-value"><?= $sportsEventCount ?></div>
          </div>
        </div>

        <div class="summary-chip">
          <div class="summary-icon quizzes">
            <i class="fa fa-play-circle"></i>
          </div>
          <div class="summary-content">
            <div class="summary-label">Available Attempts</div>
            <div class="summary-value"><?= $quizPlayableCount ?></div>
          </div>
        </div>

        <div class="summary-chip">
          <div class="summary-icon attempted">
            <i class="fa fa-check-circle"></i>
          </div>
          <div class="summary-content">
            <div class="summary-label">Total Attempts</div>
            <div class="summary-value"><?= $totalAttemptsCount ?></div>
          </div>
        </div>
      </div>

      <!-- Academic Session -->
      <?php if (!empty($activeSessionData)): ?>
        <?php
          $as  = $activeSessionData['session'] ?? [];
          $tms = $activeSessionData['terms']   ?? [];
        ?>
        <div class="academic-card">
          <div class="academic-header">
            <div>
              <div class="academic-title">
                Academic Session: <?= esc($as['session_name'] ?? '—') ?>
              </div>
              <div class="academic-dates">
                <?= esc($as['start_date'] ?? '') ?> &nbsp;–&nbsp; <?= esc($as['end_date'] ?? '') ?>
              </div>
            </div>
            <div>
              <span class="academic-badge">
                <i class="fa fa-calendar-check"></i> Active Now
              </span>
            </div>
          </div>

          <?php if (!empty($tms)): ?>
            <div class="term-grid">
              <?php foreach ($tms as $term): ?>
                <div class="term-card">
                  <div class="term-name"><?= esc($term['term_name'] ?? '—') ?></div>
                  <div class="term-dates">
                    <?= esc($term['start_date'] ?? '') ?> &nbsp;–&nbsp; <?= esc($term['end_date'] ?? '') ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div style="font-size:12px;color:#6b7280;">
              No terms mapped for this session yet.
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- PARENT FEE CARD -->
      <div class="parent-fee-card">
        <div class="parent-fee-header d-flex justify-content-between align-items-center">
          <div>
            <div class="parent-name-label">Parent / Guardian</div>
            <div class="parent-name-value"><?= esc($parentName) ?></div>
          </div>
          <div class="text-end">
            <div class="parent-total-label">Total Unpaid</div>
            <div class="parent-total-value <?= $totalDue > 0 ? 'text-danger' : 'text-success' ?>">
              <?= number_format($totalDue, 0) ?>
            </div>
          </div>
        </div>

        <div class="dues-grid">
          <?php foreach ($byStudent as $sid => $row): ?>
            <?php
              $due      = (float) $row['outstanding'];
              $nameStu  = $row['name'] ?? '';
              $reg      = $row['reg_no'] ?? '';
              $pf       = $row['profile_photo'] ?? '';
              $pf       = ltrim((string)$pf, '/');
              $avatarUrl = $pf !== ''
                ? base_url('uploads/' . $pf)
                : base_url('resource/img/avatar-student.png');
            ?>
            <div class="dues-card">
              <img src="<?= esc($avatarUrl) ?>" class="dues-photo" alt="Student Photo">
              <div class="dues-main">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="dues-name"><?= esc($nameStu) ?></div>
                    <div class="dues-reg">Reg: <?= esc($reg) ?></div>
                  </div>
                  <div class="text-end">
                    <div class="dues-amount <?= $due > 0 ? 'due' : 'clear' ?>">
                      <?= number_format($due, 0) ?>
                    </div>
                    <div>
                      <?php if ($due > 0): ?>
                        <span class="dues-chip">Unpaid</span>
                      <?php else: ?>
                        <span class="dues-chip" style="background:#22c55e;">Clear</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- SPORTS PARTICIPATION -->
      <?php if (!empty($sportsEvents)): ?>
        <hr>
        <h6 class="mb-1">Sports Participation</h6>
        <small class="text-muted">
          Events where your children participated, showing all participants of each event.
        </small>

        <div class="sports-events-grid mt-2">
          <?php foreach ($sportsEvents as $ev): ?>
            <?php
              $evName  = $ev['event_name'] ?? '';
              $evDate  = $ev['event_date'] ?? '';
              $evType  = $ev['event_type'] ?? '';
              $parts   = $ev['participants'] ?? [];
            ?>
            <div class="sports-event-card">
              <div class="sports-event-header">
                <div>
                  <div class="sports-event-name"><?= esc($evName) ?></div>
                  <?php if ($evDate): ?>
                    <div class="sports-event-date"><?= esc($evDate) ?></div>
                  <?php endif; ?>
                </div>
                <?php if ($evType): ?>
                  <span class="sports-event-chip"><?= esc($evType) ?></span>
                <?php endif; ?>
              </div>

              <?php if (!empty($parts)): ?>
                <ul class="sports-participant-list">
                  <?php foreach ($parts as $p): ?>
                    <?php
                      $pName   = $p['student_name'] ?? '';
                      $pClass  = $p['class_label'] ?? '';
                      $pUrl    = $p['photo_url'] ?? base_url('resource/img/avatar-student.png');
                      $pId     = (int)($p['student_id'] ?? 0);
                      $isChild = in_array($pId, $childIds, true);
                    ?>
                    <li class="sports-participant-row">
                      <img src="<?= esc($pUrl) ?>" class="sports-participant-avatar" alt="Student">
                      <div class="sports-participant-main">
                        <div class="d-flex justify-content-between align-items-center">
                          <div>
                            <div class="sports-participant-name"><?= esc($pName) ?></div>
                            <div class="sports-participant-class"><?= esc($pClass) ?></div>
                          </div>
                          <?php if ($isChild): ?>
                            <span class="sports-participant-badge">
                              <i class="fa fa-star"></i> Your child
                            </span>
                          <?php endif; ?>
                        </div>
                      </div>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <div class="text-muted" style="font-size:11px;">No participants found.</div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- QUIZZES BY STUDENT -->
      <?php if (!empty($quizzesByStudent)): ?>
        <hr>
        <h5 class="mb-2">Quizzes for Your Children</h5>
        <small class="text-muted">Showing quizzes grouped by each student.</small>

        <div class="student-grid-parent mt-3">
          <?php foreach ($quizzesByStudent as $studentId => $studentData): ?>
            <?php
              $studentInfo = $studentData['student_info'];
              $studentQuizzes = $studentData['quizzes'];
              $studentStats = $studentData['stats'];
              
              $totalQuizzes = $studentStats['total_quizzes'];
              $totalAttempts = $studentStats['total_attempts'];
              $availableAttempts = $studentStats['available_attempts'];
            ?>
            
            <div class="student-card">
              <!-- Student Header -->
              <div class="student-header">
                <img src="<?= esc($studentInfo['profile_photo']) ?>" class="student-avatar" alt="<?= esc($studentInfo['name']) ?>">
                <div class="student-info">
                  <div class="student-name"><?= esc($studentInfo['name']) ?></div>
                  <div class="student-class">
                    <i class="fa fa-graduation-cap"></i>
                    <?= esc($studentInfo['class']) ?>
                  </div>
                </div>
              </div>
              
              <!-- Student Stats -->
              <div class="student-stats">
                <div class="stat-item">
                  <span class="stat-value"><?= $totalQuizzes ?></span>
                  <span class="stat-label">Total Quizzes</span>
                </div>
                <div class="stat-item">
                  <span class="stat-value"><?= $availableAttempts ?></span>
                  <span class="stat-label">Available</span>
                </div>
                <div class="stat-item">
                  <span class="stat-value"><?= $totalAttempts ?></span>
                  <span class="stat-label">Attempted</span>
                </div>
              </div>
              
              <!-- Quizzes List -->
              <div class="quizzes-list">
                <?php if (!empty($studentQuizzes)): ?>
                  <?php foreach ($studentQuizzes as $quiz): ?>
                    <?php
                      $quizId = (int)($quiz['quiz_id'] ?? 0);
                      $quizStatus = $quiz['quiz_status'] ?? 'live';
                      $attempts = (int)($quiz['attempts_count'] ?? 0);
                      $maxAttempts = (int)($quiz['max_attempts'] ?? 0);
                      $remainingAttempts = max(0, $maxAttempts - $attempts);
                      $timeLimitSec = (int)($quiz['time_limit_sec'] ?? 0);
                      $timeMinutes = $timeLimitSec > 0 ? max(1, round($timeLimitSec / 60)) : 0;
                      $subjectName = $quiz['subject_name'] ?? '';
                      $questionCount = (int)($quiz['questions_count'] ?? 0);
                      $topScorers = $quiz['top_scorers'] ?? [];
                      
                      $isUnlimited = ($maxAttempts === 0);
                     
                       $canPlay = true;
                      // $canPlay = ($quizStatus !== 'closed' && ($isUnlimited || $remainingAttempts > 0) && $attempts === 0);
                      $canPractice = ($quizStatus !== 'closed');
                    ?>
                    
                    <div class="quiz-sub-card">
                      <!-- Quiz Header - ALL IN ONE ROW (Subject, Qs, Status) -->
                      <div class="quiz-header-row">
                        <div class="quiz-subject">
                          <i class="fa fa-book"></i>
                          <span><?= esc($subjectName) ?></span>
                        </div>
                        <div class="quiz-meta-center">
                          <?php if ($questionCount > 0): ?>
                            <span class="question-count">
                              <i class="fa fa-question-circle"></i>
                              <?= $questionCount ?> Qs
                            </span>
                          <?php endif; ?>
                          <span class="quiz-status <?= $quizStatus === 'live' ? 'status-live' : 'status-upcoming' ?>">
                            <?= $quizStatus === 'live' ? 'Live' : 'Upcoming' ?>
                          </span>
                        </div>
                      </div>
                      
                      <!-- Attempts Row: Left=Attempts, Center=Duration, Right=Remaining - ALL IN ONE ROW -->
                      <div class="attempts-row">
                        <div class="attempts-left">
                          <span class="attempts-label">Attempts</span>
                          <span class="attempts-value attempts-count"><?= $attempts ?><?= $isUnlimited ? '' : '/' . $maxAttempts ?></span>
                        </div>
                        <div class="attempts-center">
                          <span class="attempts-label">Duration</span>
                          <span class="attempts-value duration-count"><?= $timeMinutes ?> min</span>
                        </div>
                        <div class="attempts-right">
                          <span class="attempts-label">Remaining</span>
                          <span class="attempts-value remaining-count"><?= $isUnlimited ? '∞' : $remainingAttempts ?></span>
                        </div>
                      </div>
                      
                      <!-- Review Attempts - Eye icon + number only -->
                      <?php if (!empty($quiz['attempt_ids'])): ?>
                        <div class="attempt-review-section">
                          <div class="attempt-review-buttons">
                            <?php foreach ($quiz['attempt_ids'] as $index => $attemptId): ?>
                              <a href="<?= base_url('student/quizzes/review/' . (int)$attemptId) ?>" 
                                 class="attempt-review-btn"
                                 title="Review attempt <?= $index + 1 ?>">
                                <i class="fa fa-eye"></i>
                                <span>#<?= $index + 1 ?></span>
                              </a>
                            <?php endforeach; ?>
                          </div>
                        </div>
                      <?php endif; ?>
                      
                      <!-- Top 3 Students - Photo, position, percentage only - HORIZONTAL on mobile -->
                      <?php if (!empty($topScorers)): ?>
                        <div class="quiz-toppers">
                          <div class="toppers-label">
                            <i class="fa fa-trophy text-warning"></i>
                            Top Performers
                          </div>
                          <div class="toppers-list">
                            <?php foreach (array_slice($topScorers, 0, 3) as $idx => $topper): ?>
                              <?php
                                $position = $idx + 1;
                                $rankClass = 'rank-' . $position;
                                $photoUrl = $topper['photo_url'] ?? base_url('resource/img/avatar-student.png');
                                $score = $topper['score'] ?? 0;
                                $topperQuestionCount = $topper['questions_count'] ?? $questionCount;
                                $scorePercentage = ($topperQuestionCount > 0) ? ($score / $topperQuestionCount) * 100 : 0;
                              ?>
                              <div class="topper-item">
                                <img src="<?= esc($photoUrl) ?>" class="topper-avatar" alt="Topper">
                                <div class="topper-rank <?= $rankClass ?>">#<?= $position ?></div>
                                <div class="topper-score"><?= number_format($scorePercentage, 1) ?>%</div>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        </div>
                      <?php endif; ?>
                      
                      <!-- Action Buttons - ALWAYS SAME ROW (even on mobile) -->
                      <div class="quiz-actions">
                        <button class="btn-play" 
                                onclick="window.location.href='<?= base_url('student/quizzes/start/' . $quizId . '?sid=' . $studentId) ?>'"
                                <?= !$canPlay ? 'disabled' : '' ?>>
                          <i class="fa fa-play"></i> Play
                        </button>
                        <button class="btn-practice"
                                onclick="window.location.href='<?= base_url('student/quizzes/practice/' . $quizId . '?sid=' . $studentId) ?>'"
                                <?= !$canPractice ? 'disabled' : '' ?>>
                          <i class="fa fa-dumbbell"></i> Practice
                        </button>
                      </div>
                      
                      <?php if ($attempts > 0): ?>
                        <button class="btn-view-results"
                                onclick="window.location.href='<?= base_url('student/quizzes/results/' . $quizId . '?sid=' . $studentId) ?>'">
                          <i class="fa fa-chart-bar"></i> View Results
                        </button>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="empty-quizzes">
                    <i class="fa fa-inbox"></i>
                    <h4>No Quizzes</h4>
                    <p>No quizzes available for this student at the moment.</p>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="alert alert-light border mt-3">
          No quizzes found for your children's current classes.
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  // Initialize tooltips
  if (typeof $ !== 'undefined' && $.fn.tooltip) {
    $('[data-bs-toggle="tooltip"]').tooltip();
  }
});
</script>

<?= $this->endSection() ?>