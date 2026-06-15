<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<?php
  /** @var object $quiz */
  $quizTitle    = $quiz->title          ?? 'Quiz';
  $subjectName  = $quiz->subject_name   ?? '';
  $classSection = $classSection         ?? '';
  $attemptNumbers = $attemptNumbers     ?? [];
  $studentRows    = $studentRows        ?? [];
?>

<style>
.quiz-results-wrapper {
  border-radius: 18px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15,23,42,0.10);
  padding: 16px 18px;
}

.quiz-results-header {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 14px;
}

.quiz-results-title {
  font-size: 18px;
  font-weight: 700;
  color: #111827;
}

.quiz-results-subtitle {
  font-size: 13px;
  color: #6b7280;
}

.quiz-results-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  font-size: 12px;
}

.quiz-results-badge {
  border-radius: 999px;
  padding: 3px 9px;
  border: 1px solid #e5e7eb;
  background: #f9fafb;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.quiz-results-badge i {
  font-size: 11px;
}

/* sticky header, horizontal scroll for many attempts */
.quiz-results-table-wrap {
  width: 100%;
  overflow-x: auto;
}

.quiz-results-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.quiz-results-table thead th {
  position: sticky;
  top: 0;
  background: #f9fafb;
  z-index: 2;
  border-bottom: 2px solid #e5e7eb;
  padding: 6px 8px;
  white-space: nowrap;
}

.quiz-results-table tbody td {
  padding: 6px 8px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}

.quiz-results-table tbody tr:nth-child(odd) {
  background: #f9fafb;
}

.quiz-results-table tbody tr:hover {
  background: #eff6ff;
}

.quiz-results-student-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.quiz-results-avatar {
  width: 32px;
  height: 32px;
  border-radius: 999px;
  object-fit: cover;
  box-shadow: 0 1px 6px rgba(15,23,42,0.35);
}

.quiz-results-name {
  font-weight: 600;
  color: #111827;
}

.quiz-results-reg {
  font-size: 11px;
  color: #6b7280;
}

.quiz-score-cell {
  text-align: center;
  min-width: 60px;
}

.quiz-score-empty {
  color: #9ca3af;
  font-style: italic;
}

.quiz-score-total {
  font-weight: 700;
  text-align: center;
  min-width: 70px;
}

.quiz-results-footer {
  margin-top: 10px;
  font-size: 11px;
  color: #6b7280;
}

/* small “chip” for attempt column headers */
.attempt-chip {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  padding: 1px 8px;
  background: #e0f2fe;
  color: #0369a1;
  font-size: 11px;
}
</style>

<div class="card shadow-sm mb-3">
  <div class="card-body d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Quiz Results</h5>
    <div>
      <a href="<?= base_url('student/dashboard') ?>" class="btn btn-sm btn-secondary">
        <i class="fa fa-arrow-left"></i> Back to Dashboard
      </a>
    </div>
  </div>
</div>

<div class="quiz-results-wrapper">

  <div class="quiz-results-header">
    <div>
      <div class="quiz-results-title">
        <?= esc($quizTitle) ?>
      </div>
      <div class="quiz-results-subtitle">
        Class: <?= esc($classSection ?: '—') ?>
        <?php if ($subjectName): ?>
          · Subject: <?= esc($subjectName) ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="quiz-results-badges">
      <div class="quiz-results-badge">
        <i class="fa fa-users"></i>
        Students: <?= count($studentRows) ?>
      </div>
      <div class="quiz-results-badge">
        <i class="fa fa-redo-alt"></i>
        Attempts: <?= count($attemptNumbers) ?>
      </div>
      <?php if (!empty($quiz->questions_count)): ?>
        <div class="quiz-results-badge">
          <i class="fa fa-list-ol"></i>
          <?= (int)$quiz->questions_count ?> Questions
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if (empty($studentRows)): ?>
    <div class="alert alert-info mb-0">
      No attempts found for this quiz yet.
    </div>
  <?php else: ?>

    <div class="quiz-results-table-wrap">
      <table class="quiz-results-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Student</th>
            <?php foreach ($attemptNumbers as $attNo): ?>
              <th class="text-center">
                <span class="attempt-chip">Attempt <?= (int)$attNo ?></span>
              </th>
            <?php endforeach; ?>
            <th class="text-center">Total</th>
          </tr>
        </thead>
        <tbody>
          <?php $rowIndex = 1; ?>
          <?php foreach ($studentRows as $row): ?>
            <?php
              $scores = $row['scores'] ?? [];
              $total  = 0;
              foreach ($scores as $v) {
                  if (is_numeric($v)) {
                      $total += (float)$v;
                  }
              }
            ?>
            <tr>
              <td><?= $rowIndex++ ?></td>
              <td>
                <div class="quiz-results-student-cell">
                  <img src="<?= esc($row['photo_url'] ?? base_url('resource/img/avatar-student.png')) ?>"
                       alt="Avatar"
                       class="quiz-results-avatar">
                  <div>
                    <div class="quiz-results-name">
                      <?= esc($row['student_name'] ?? '—') ?>
                    </div>
                    <?php if (!empty($row['reg_no'])): ?>
                      <div class="quiz-results-reg">
                        Reg: <?= esc($row['reg_no']) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </td>

              <?php foreach ($attemptNumbers as $attNo): ?>
                <?php
                  $score = $scores[$attNo] ?? null;
                ?>
                <td class="quiz-score-cell">
                  <?php if ($score === null || $score === ''): ?>
                    <span class="quiz-score-empty">–</span>
                  <?php else: ?>
                    <?= rtrim(rtrim(number_format((float)$score, 2, '.', ''), '0'), '.') ?>
                  <?php endif; ?>
                </td>
              <?php endforeach; ?>

              <td class="quiz-score-total">
                <?= rtrim(rtrim(number_format($total, 2, '.', ''), '0'), '.') ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="quiz-results-footer">
      Scores are shown per attempt. The <strong>Total</strong> column is the sum of all attempts
      for each student in this quiz.
    </div>

  <?php endif; ?>

</div>

<?= $this->endSection() ?>
