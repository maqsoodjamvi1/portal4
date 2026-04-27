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

  // Quiz summary metrics
  $quizPlayableCount  = 0; // not attempted & not closed
  $quizAttemptedCount = 0; // attempted by child
  $quizReviewableCount = 0; // attempted (for review)
  foreach ($quizzes as $qq) {
      $status   = $qq['quiz_status'] ?? 'live';
      $attempts = (int)($qq['attempts_count'] ?? 0);
      $isClosed = ($status === 'closed');

      if (!$isClosed && $attempts === 0) {
          $quizPlayableCount++;
      }
      if ($attempts > 0) {
          $quizAttemptedCount++;
          $quizReviewableCount++;
      }
  }

  // Sports summary metrics
  $sportsEventCount = count($sportsEvents);
  $sportsParticipationCount = 0; // total participants where student is one of your children
  foreach ($sportsEvents as $ev) {
      $parts = $ev['participants'] ?? [];
      foreach ($parts as $p) {
          if (in_array((int)$p['student_id'], $childIds, true)) {
              $sportsParticipationCount++;
          }
      }
  }
?>

<style>
.quiz-grid-parent {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 12px;
  margin-top: 10px;
}

/* Quiz cards */


.quiz-class-badge-wrap {
    width: 100%;
    text-align: center;
    margin-bottom: 6px;
}
/* class-section badge in corner */




/* Status chip */
.parent-status-chip {
  position: absolute;
  top: 8px;
  right: 10px;
  font-size: 11px;
  padding: 3px 8px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.parent-status-quiz-live {
  background: #dcfce7;
  color: #15803d;
}
.parent-status-quiz-closed {
  background: #f3f4f6;
  color: #6b7280;
}






/* Avatar + name left-aligned inside main-left */




/* quiz title & meta */
.quiz-title-parent {
  font-size: 15px;
  font-weight: 700;
  color: #111827;
  margin-top: 4px;
  margin-bottom: 2px;
  text-align: left;
}


/* Gradient subject chip */




/* Topper block */



.quiz-grid-parent {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
  margin-top: 12px;
}

.quiz-card-parent {
  border-radius: 18px;
  padding: 14px 16px 12px;
  min-height: 340px;
}


.quiz-footer {
  margin-top: auto;
  padding-top: 8px;
  border-top: 1px dashed #e5e7eb;
}

/* Review badges row */
.quiz-review-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-bottom: 6px;
  justify-content: flex-start;
}

.quiz-review-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  border-radius: 999px;
  border: 1px solid #2563eb;
  background: #eff6ff;
  color: #1d4ed8;
  font-size: 11px;
  text-decoration: none;
}
.quiz-review-badge:hover {
  background: #dbeafe;
}
.quiz-review-badge i {
  font-size: 10px;
  margin-right: 1px;
}

/* View-all button: slimmer, more like a link */
.btn-view-results-all {
  border-radius: 999px;
  font-size: 11px;
  padding: 4px 10px;
}


.quiz-review-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-bottom: 4px;
}

.quiz-review-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  border-radius: 999px;
  border: 1px solid #2563eb;
  background: #eff6ff;
  color: #1d4ed8;
  font-size: 11px;
  text-decoration: none;
}

.quiz-review-badge i {
  font-size: 10px;
  margin-right: 1px;
}

.quiz-small-info {
  font-size: 11px;
  color: #6b7280;
  margin-top: 4px;
}

.quiz-footer {
  margin-top: auto;
}
/* Summary chips row */
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

/* Colors for summary icons */
.summary-icon.children   { background:#eff6ff; color:#1d4ed8; }
.summary-icon.fees       { background:#fef2f2; color:#b91c1c; }
.summary-icon.sports     { background:#ecfdf3; color:#15803d; }
.summary-icon.quizzes    { background:#f5f3ff; color:#6d28d9; }
.summary-icon.attempted  { background:#fdf4ff; color:#a21caf; }

/* Parent fee overview card */
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

/* Child fee cards */
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

/* Sports events section */
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

/* Responsive adjustments */
@media (max-width: 767.98px) {
   .quiz-card-parent {
    height: auto;
    min-height: 320px;
  }
  
}
@media (max-width: 575.98px) {
  .quiz-card-parent {
    height: auto;
    min-height: 280px;
  }
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

.term-label {
  font-size: 10px;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: .04em;
}

/* ============ PREMIUM QUIZ CARD ============ */

.quiz-card-parent {
  background: radial-gradient(circle at top left, #eff6ff, #ffffff);
  border-radius: 18px;
  padding: 0 14px 12px;
  border: 1px solid rgba(148,163,184,0.25);
  box-shadow: 0 18px 40px rgba(15,23,42,0.10);
  position: relative;
  display: flex;
  flex-direction: column;
  min-height: 340px;
  overflow: hidden;
  transition: transform .18s ease, box-shadow .18s ease;
}

.quiz-card-parent:hover {
  transform: translateY(-4px);
  box-shadow: 0 24px 60px rgba(15,23,42,0.18);
}

.quiz-card-parent.closed {
  opacity: .85;
}

/* HEADER: class badge centered, status on right */
.quiz-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: linear-gradient(135deg, #2563eb, #22c55e);
  color: #fff;
  margin: -1px -14px 10px;
  padding: 8px 14px;
}

/* class badge – centered, pill style */
.quiz-class-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  border-radius: 999px;
  background: rgba(15,23,42,0.18);
  font-size: 0.78rem;
  font-weight: 600;
  letter-spacing: .02em;
  white-space: nowrap;
}

.quiz-class-badge i {
  font-size: 0.8rem;
}

/* status chip in header */
.quiz-card-status {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.78rem;
  padding: 3px 10px;
  border-radius: 999px;
  background: rgba(15,23,42,0.25);
  font-weight: 500;
}

.quiz-card-status.parent-status-quiz-live {
  background: rgba(22,163,74,0.25);
  color: #dcfce7;
}

.quiz-card-status.parent-status-quiz-closed {
  background: rgba(55,65,81,0.45);
  color: #e5e7eb;
}

/* MAIN AREA: left content + right toppers */
.quiz-main-block {
  display: flex;
  align-items: stretch;
  justify-content: space-between;
  gap: 12px;
  padding-top: 6px;
}

.quiz-main-left {
  flex: 1 1 auto;
  min-width: 0;
}

/* Student avatar */
.quiz-student-avatar {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 4px;
}

.quiz-student-avatar img {
  width: 72px;
  height: 72px;
  border-radius: 999px;
  object-fit: cover;
  box-shadow: 0 2px 10px rgba(15,23,42,0.35);
  border: 3px solid #e0ebff;
}

.quiz-student-name {
  font-size: 13px;
  font-weight: 700;
  color: #111827;
}

/* Title + meta */
.quiz-title-parent {
  font-size: 15px;
  font-weight: 700;
  color: #111827;
  margin-top: 6px;
  margin-bottom: 4px;
}

.quiz-meta-parent {
  font-size: 12px;
  color: #4b5563;
  margin-bottom: 4px;
}

/* Subject chip */
.subject-chip {
  display: inline-flex;
  align-items: center;
  padding: 2px 10px;
  border-radius: 999px;
  font-size: 11px;
  color: #fff;
  background: linear-gradient(135deg, #6366f1, #22c55e);
  box-shadow: 0 2px 6px rgba(0,0,0,0.18);
}

.subject-chip i {
  font-size: 11px;
  margin-right: 4px;
}

/* Qs + time pills */
.quiz-qtime-row {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 4px;
}

.quiz-qtime-pill {
  font-size: 11px;
  border-radius: 999px;
  padding: 2px 8px;
  border: 1px solid #e5e7eb;
  background: #f9fafb;
  color: #374151;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

/* Attempts info */
.quiz-attempt-row {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 6px;
}

.quiz-attempt-pill {
  font-size: 11px;
  border-radius: 999px;
  padding: 2px 8px;
  border: 1px dashed #e5e7eb;
  background: #ffffff;
  color: #374151;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

/* Toppers column (right side) */
.quiz-topper-block {
  flex: 0 0 72px;
  border-left: 1px dashed #e5e7eb;
  padding-left: 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
}

.quiz-small-info {
  font-size: 11px;
  color: #6b7280;
}

.quiz-top-avatars {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  margin-top: 4px;
}

.quiz-top-avatars .avatar-wrap {
  position: relative;
  width: 36px;
  height: 36px;
}

.quiz-top-avatars img {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  box-shadow: 0 2px 8px rgba(15,23,42,0.35);
}

.quiz-top-avatars .pos-badge {
  position: absolute;
  bottom: -4px;
  right: -4px;
  font-size: 0.6rem;
  padding: 2px 5px;
  border-radius: 999px;
  background: #facc15;
  color: #111827;
}

/* footer spacing */
.quiz-footer {
  margin-top: auto;
}

/* Responsive: stack toppers under content on small screens */
@media (max-width: 575.98px) {
  .quiz-main-block {
    flex-direction: column;
  }
  .quiz-topper-block {
    flex: 1 1 auto;
    border-left: 0;
    border-top: 1px dashed #e5e7eb;
    margin-top: 8px;
    padding-top: 8px;
    padding-left: 0;
    flex-direction: row;
    justify-content: center;
    gap: 10px;
  }
  .quiz-top-avatars {
    flex-direction: row;
  }
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
            <div class="summary-label">Playable Quizzes</div>
            <div class="summary-value"><?= $quizPlayableCount ?></div>
          </div>
        </div>

        <div class="summary-chip">
          <div class="summary-icon attempted">
            <i class="fa fa-check-circle"></i>
          </div>
          <div class="summary-content">
            <div class="summary-label">Attempted Quizzes</div>
            <div class="summary-value"><?= $quizAttemptedCount ?></div>
          </div>
        </div>
      </div>

     
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
      <div class="mb-2" style="font-size:12px;color:#6b7280;">
        Terms in this session:
      </div>
      <div class="term-grid">
        <?php foreach ($tms as $term): ?>
          <div class="term-card">
            <div class="term-label">Term</div>
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

<?php if (!empty($attendanceSummary)): ?>
  <?php
    $att = $attendanceSummary;
  ?>
  <div class="card mb-3 shadow-sm border-0" style="border-radius:16px;">
    <div class="card-body d-flex flex-column flex-md-row align-items-start align-items-md-center">
      <div class="me-md-4 mb-2 mb-md-0">
        <div style="
          width:48px;height:48px;border-radius:16px;
          background:linear-gradient(135deg,#22c55e,#16a34a);
          display:flex;align-items:center;justify-content:center;
          color:#fff;
        ">
          <i class="fa fa-user-check"></i>
        </div>
      </div>
      <div class="flex-grow-1">
        <div style="font-size:14px;font-weight:700;color:#111827;">
          Attendance – <?= esc($att['term_name'] ?: 'Current Term') ?>
        </div>
        <div style="font-size:12px;color:#6b7280;">
          <?= esc($att['start_date']) ?> &nbsp;–&nbsp; <?= esc($att['end_date']) ?>
        </div>
        <div class="mt-2 d-flex flex-wrap" style="gap:10px;font-size:12px;">
          <span class="badge bg-light text-dark border">
            Total Classes: <strong><?= (int)$att['total_classes'] ?></strong>
          </span>
          <span class="badge bg-success text-white">
            Present: <strong><?= (int)$att['present_count'] ?></strong>
          </span>
          <span class="badge bg-danger text-white">
            Absent: <strong><?= (int)$att['absent_count'] ?></strong>
          </span>
          <span class="badge bg-primary text-white">
            Present %: <strong><?= number_format($att['present_percent'], 1) ?>%</strong>
          </span>
        </div>
      </div>
    </div>
  </div>
<?php else: ?>
  <div class="card mb-3 shadow-sm border-0" style="border-radius:16px;">
    <div class="card-body" style="font-size:12px;color:#6b7280;">
      Attendance data for the current term is not available.
    </div>
  </div>
<?php endif; ?>


<?php if (!empty($activeAttendance)): ?>
<div class="alert alert-info mt-3">
    <b>Debug – Active Term</b><br>
    Term ID: <?= esc($activeAttendance['term_id']) ?><br>
    Term Name: <?= esc($activeAttendance['term_name']) ?><br>
    Term Start: <?= esc($activeAttendance['term_start']) ?><br>
    Term End: <?= esc($activeAttendance['term_end']) ?><br>
</div>
<?php endif; ?>
      <!-- PARENT FEE CARD (wraps all child fee cards) -->
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
                ? base_url('uploads/' . $pf)          // uploads/usama.jpeg
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

      <!-- QUIZZES -->
      <?php if (!empty($quizzes)): ?>
        <hr>
        <h5 class="mb-2">Quizzes for Your Children</h5>
        <small class="text-muted">Showing quizzes based on each child's current class and section.</small>

        <?php
          $playableQuizzes = [];
          $otherQuizzes    = [];

          foreach ($quizzes as $quizRow) {
              $quizStatus = $quizRow['quiz_status'] ?? 'live';
              $attempts   = (int) ($quizRow['attempts_count'] ?? 0);

              $isClosed   = ($quizStatus === 'closed');
              $isPlayable = (!$isClosed && $attempts === 0);

              // skip closed with 0 attempts
              if ($isClosed && $attempts === 0) {
                  continue;
              }

              $quizRow['_quizStatus'] = $quizStatus;
              $quizRow['_attempts']   = $attempts;

              if ($isPlayable) {
                  $playableQuizzes[] = $quizRow;
              } else {
                  $otherQuizzes[] = $quizRow;
              }
          }

          $orderedQuizzes = array_merge($playableQuizzes, $otherQuizzes);
        ?>

        <?php if (empty($orderedQuizzes)): ?>
          <div class="alert alert-light border mt-3">
            No quizzes currently available to play or review.
          </div>
        <?php else: ?>
                   <div class="quiz-grid-parent mt-3">
            <?php foreach ($orderedQuizzes as $q): ?>
              <?php
                $quizStatus = $q['_quizStatus'];
                $attempts   = (int) $q['_attempts'];

                $quizStatusClass = $quizStatus === 'closed'
                    ? 'parent-status-quiz-closed'
                    : 'parent-status-quiz-live';

                $quizStatusLabel = $quizStatus === 'closed' ? 'Closed' : 'Live';
                $quizStatusIcon  = $quizStatus === 'closed' ? 'fa-lock' : 'fa-broadcast-tower';

                $studentPhotoUrl  = $q['student_photo_url'] ?? base_url('resource/img/avatar-student.png');
                $studentName      = $q['student_name'] ?? ($q['student_full_name'] ?? '');
                $topScorers       = $q['top_scorers'] ?? [];

                $quizId        = (int) ($q['quiz_id'] ?? 0);
                $studentId     = (int) ($q['student_id'] ?? 0);
                $lastAttemptId = $q['last_attempt_id'] ?? null;

                // class-section label
                $clsLabel = trim( ($q['class_name'] ?? '') . ' ' . ($q['section_name'] ?? '') );
                if ($clsLabel === '' && !empty($q['class_section'])) {
                    $clsLabel = $q['class_section'];
                }

                // total questions & duration
                $questionCount = (int) ($q['questions_count'] ?? ($q['question_count'] ?? 0));
                $timeLimitSec  = (int) ($q['time_limit_sec'] ?? 0);
                if ($timeLimitSec > 0) {
                    $timeMinutes = max(1, round($timeLimitSec / 60));
                } else {
                    $timeMinutes = 0;
                }

                // attempts info
              $maxAttempts  = (int) ($q['max_attempts'] ?? 0);
$usedAttempts = $attempts;

/*
 Correct Rules:
 - If max_attempts = 0 → Unlimited attempts
 - If max_attempts = 1 → Only 1 attempt allowed
 - If max_attempts > 1 → Limited attempts
*/
if ($maxAttempts === 0) {
    // unlimited
    $isUnlimitedAttempts = true;
    $remainingAttempts = null;

} elseif ($maxAttempts === 1) {

    // only one attempt allowed
    $isUnlimitedAttempts = false;
    $remainingAttempts = max(1 - $usedAttempts, 0);

} else {

    // limited attempts > 1
    $isUnlimitedAttempts = false;
    $remainingAttempts = max($maxAttempts - $usedAttempts, 0);
}

              ?>
         <div class="quiz-card-parent <?= $quizStatus === 'closed' ? 'closed' : '' ?>">

  <!-- PREMIUM HEADER -->
  <div class="quiz-card-header">
    <?php if (!empty($clsLabel)): ?>
      <div class="quiz-class-badge">
        <i class="fa fa-chalkboard-teacher"></i>
        <?= esc($clsLabel) ?>
      </div>
    <?php endif; ?>

    <div class="quiz-card-status <?= $quizStatusClass ?>" data-toggle="tooltip" title="Quiz status">
      <i class="fa <?= $quizStatusIcon ?>"></i>
      <span><?= $quizStatusLabel ?></span>
    </div>
  </div>

  <!-- MAIN CONTENT: LEFT (student + quiz info) + RIGHT (toppers) -->
  <div class="quiz-main-block">
    <div class="quiz-main-left">
      <div class="quiz-student-avatar">
        <img src="<?= esc($studentPhotoUrl) ?>" alt="Student Photo">
        <?php if (!empty($studentName)): ?>
          <div class="quiz-student-name"><?= esc($studentName) ?></div>
        <?php endif; ?>
      </div>

      <div class="quiz-title-parent">
        <i class="fa fa-file-alt text-muted"></i>
        &nbsp;<?= esc($q['title']) ?>
      </div>

      <div class="quiz-meta-parent">
        <div class="mb-1">
          <span class="subject-chip">
            <i class="fa fa-book"></i>
            <?= esc($q['subject_name']) ?>
          </span>
        </div>

        <!-- questions + duration -->
        <div class="quiz-qtime-row">
          <?php if ($questionCount > 0): ?>
            <span class="quiz-qtime-pill">
              <i class="fa fa-list-ol"></i>
              <?= $questionCount ?> Qs
            </span>
          <?php endif; ?>

          <?php if ($timeMinutes > 0): ?>
            <span class="quiz-qtime-pill">
              <i class="fa fa-clock"></i>
              <?= $timeMinutes ?> min
            </span>
          <?php else: ?>
            <span class="quiz-qtime-pill">
              <i class="fa fa-clock"></i>
              Self-paced
            </span>
          <?php endif; ?>
        </div>

        <!-- attempts info: max / used / remaining -->
        <div class="quiz-attempt-row">
          <?php if ($isUnlimitedAttempts): ?>
            <span class="quiz-attempt-pill">
              <i class="fa fa-infinity"></i>
              Unlimited
            </span>
            <?php if ($usedAttempts > 0): ?>
              <span class="quiz-attempt-pill">
                <i class="fa fa-check-circle"></i>
                Used: <?= $usedAttempts ?>
              </span>
            <?php endif; ?>
          <?php else: ?>
            <span class="quiz-attempt-pill">
              <i class="fa fa-sync-alt"></i>
              Max: <?= $maxAttempts ?>
            </span>
            <span class="quiz-attempt-pill">
              <i class="fa fa-check-circle"></i>
              Used: <?= $usedAttempts ?>
            </span>
            <span class="quiz-attempt-pill">
              <i class="fa fa-hourglass-half"></i>
              Left: <?= $remainingAttempts ?>
            </span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- RIGHT SIDE: vertical toppers -->
    <div class="quiz-topper-block">
      <?php if (!empty($topScorers)): ?>
        <div class="quiz-small-info text-center mb-1">
          <i class="fa fa-trophy text-warning"></i>
          <span>Top Players</span>
        </div>
        <div class="quiz-top-avatars">
          <?php foreach (array_slice($topScorers, 0, 3) as $idx => $ts): ?>
            <?php
              $position = $idx + 1;
              $photoUrl = $ts['photo_url'] ?? base_url('resource/img/avatar-student.png');
            ?>
            <div class="avatar-wrap">
              <img src="<?= esc($photoUrl) ?>" alt="Topper">
              <span class="pos-badge"><?= $position ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div> <!-- /.quiz-main-block -->

  <!-- FOOTER: play + tiny review icons + view-all button -->
  <div class="quiz-footer mt-3">

    <?php
      $unlimited   = ($maxAttempts === 0);
      $attemptList = $q['attempt_ids'] ?? [];
      if (!is_array($attemptList)) {
          $attemptList = [];
      }
      $remaining = $unlimited ? 999 : ($maxAttempts - $usedAttempts);
    ?>

    <!-- PLAY (only if allowed) -->
    <?php if ($quizStatus !== 'closed' && ($unlimited || $remaining > 0)): ?>
      <a href="<?= base_url('student/quizzes/start/' . $quizId . '?sid=' . $studentId) ?>"
         class="btn btn-sm btn-success w-100 mb-2">
        <i class="fa fa-play"></i> Play
      </a>
    <?php endif; ?>

    <!-- SMALL REVIEW ICONS: #1, #2, #3 ... -->
    <?php if (!empty($attemptList)): ?>
      <div class="quiz-review-badges mb-1">
        <?php foreach ($attemptList as $idx => $attId): ?>
          <a href="<?= base_url('student/quizzes/review/' . (int)$attId) ?>"
             class="quiz-review-badge"
             title="Review attempt <?= $idx + 1 ?>">
            <i class="fa fa-eye"></i>
            <span>#<?= $idx + 1 ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- VIEW ALL RESULTS (tabular, for this quiz) -->
    <a href="<?= base_url('student/quizzes/results/' . $quizId) ?>"
   class="btn btn-outline-secondary btn-sm w-100 btn-view-results-all">
  <i class="fa fa-table"></i> View All Results
</a>

    <!-- If quiz is closed and no attempts at all -->
    <?php if ($quizStatus === 'closed' && empty($attemptList)): ?>
      <button class="btn btn-sm btn-secondary w-100 mt-2" disabled>
        <i class="fa fa-lock"></i> Closed
      </button>
    <?php endif; ?>
  </div>

</div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

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
  if (typeof $ !== 'undefined' && $.fn.tooltip) {
    $('[data-toggle="tooltip"]').tooltip();
  }
});
</script>

<?= $this->endSection() ?>
