<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  // Safe values
  $title   = $quiz->title        ?? '—';
  $clsName = $quiz->cls_sec_name ?? '—';
  $subName = $quiz->sec_sub_name ?? '—';
  $term    = $quiz->term_name    ?? '—';
  $created = $quiz->created_date ?? '—';
  $start   = $quiz->start_at     ?? '—';
  $end     = $quiz->end_at       ?? '—';

  // Stats from controller (fallbacks if not passed)
  $attemptCount  = $attemptCount  ?? (is_array($attempts) ? count($attempts) : 0);
  $totalStudents = isset($totalStudents) ? (int)$totalStudents : null;
  $avgScore      = $avgScore      ?? '—';
  $participation = $participation ?? '—';

  $topics = $topics ?? [];
  
  // Find max attempts per quiz
  $maxAttempts = 0;
  $studentAttempts = [];
  
  // Organize attempts by student
  foreach ($attempts as $attempt) {
    $studentId = $attempt->student_id;
    if (!isset($studentAttempts[$studentId])) {
      $studentAttempts[$studentId] = [
        'student_id' => $studentId,
        'student_name' => $attempt->student_name,
        'profile_photo' => $attempt->profile_photo,
        'attempts' => [],
        'total_percentage' => 0,
        'best_score' => 0,
        'total_attempts' => 0
      ];
    }
    
    $studentAttempts[$studentId]['attempts'][$attempt->attempt_no] = $attempt;
    $maxAttempts = max($maxAttempts, $attempt->attempt_no);
    
    // Track best score for overall percentage
    $attemptScore = (float)($attempt->score_obtained ?? 0);
    $attemptPercentage = $attempt->percentage ?? 0;
    
    if ($attemptPercentage > $studentAttempts[$studentId]['best_score']) {
      $studentAttempts[$studentId]['best_score'] = $attemptPercentage;
    }
    
    $studentAttempts[$studentId]['total_attempts']++;
  }
  
  // Calculate total percentage (using best score)
  foreach ($studentAttempts as &$student) {
    $student['total_percentage'] = $student['best_score'];
  }
  
  // Sort students by total percentage for toppers
  usort($studentAttempts, function($a, $b) {
    return $b['total_percentage'] <=> $a['total_percentage'];
  });
  
  // Get top 3 toppers
  $toppers = array_slice($studentAttempts, 0, 3);
?>

<style>
  /* ===== Report Header ===== */
  .report-wrap{
    border:1px solid #e5e7eb;border-radius:14px;background:#fff;overflow:hidden
  }
  .report-head{
    display:flex;align-items:center;gap:16px;
    background:linear-gradient(135deg,#eef6ff 0%, #ffffff 100%);
    padding:18px 18px 12px;border-bottom:1px solid #e9eef5
  }
  .report-brand{
    width:56px;height:56px;border-radius:12px;background:#fff;border:1px solid #e5e7eb;
    display:flex;align-items:center;justify-content:center;font-weight:800;color:#2563eb
  }
  .report-title{margin:0;font-weight:800;letter-spacing:.2px}
  .report-sub{color:#475569;margin-top:2px}
  .chip {
    display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;
    border:1px solid #e2e8f0;background:#f8fafc;font-size:12.5px;font-weight:600;color:#0f172a
  }
  .chip b{font-weight:800}
  .chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px}

  /* ===== Toppers Section ===== */
  .toppers-section {
    margin: 20px 0;
    padding: 20px;
    background: linear-gradient(135deg, #fef3c7 0%, #fefce8 100%);
    border-radius: 12px;
    border: 1px solid #fbbf24;
  }
  .toppers-title {
    text-align: center;
    font-weight: 800;
    font-size: 18px;
    color: #92400e;
    margin-bottom: 20px;
  }
  .toppers-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
  }
  @media (max-width: 768px) {
    .toppers-grid {
      grid-template-columns: 1fr;
    }
  }
  .topper-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    border: 2px solid #fbbf24;
    position: relative;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  }
  .topper-card.position-1 {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fff7ed 0%, #ffffff 100%);
  }
  .topper-card.position-2 {
    border-color: #94a3b8;
    background: linear-gradient(135deg, #f1f5f9 0%, #ffffff 100%);
  }
  .topper-card.position-3 {
    border-color: #d97706;
    background: linear-gradient(135deg, #fef3c7 0%, #ffffff 100%);
  }
  .position-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: #f59e0b;
    color: white;
    font-weight: bold;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 14px;
  }
  .topper-photo {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fbbf24;
    margin: 0 auto 15px;
  }
  .topper-name {
    font-weight: 800;
    font-size: 16px;
    margin-bottom: 5px;
    color: #1f2937;
  }
  .topper-percentage {
    font-size: 24px;
    font-weight: 800;
    color: #059669;
    margin: 15px 0 5px 0;
  }
  .topper-label {
    font-size: 12px;
    color: #6b7280;
    margin-top: 5px;
  }
  .topper-stats {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 10px;
  }
  .topper-stat {
    text-align: center;
  }
  .topper-stat-value {
    font-weight: 700;
    font-size: 14px;
    color: #1f2937;
  }
  .topper-stat-label {
    font-size: 11px;
    color: #6b7280;
  }

  /* ===== Student Results Section ===== */
  .student-results {
    margin-top: 30px;
  }
  .student-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    margin-bottom: 20px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  }
  .student-header {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
  }
  .student-photo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e5e7eb;
    margin-right: 15px;
  }
  .student-info {
    flex: 1;
  }
  .student-name {
    font-weight: 700;
    font-size: 16px;
    margin: 0;
    color: #1f2937;
  }
  .student-percentage {
    font-size: 24px;
    font-weight: 800;
    color: #059669;
    min-width: 80px;
    text-align: right;
  }
  .attempts-table {
    width: 100%;
    border-collapse: collapse;
  }
  .attempts-table th {
    background: #f8fafc;
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
    font-size: 13px;
  }
  .attempts-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 14px;
  }
  .attempts-table tr:last-child td {
    border-bottom: none;
  }
  .percentage-cell {
    font-weight: 700;
  }
  .percentage-cell.high {
    color: #059669;
  }
  .percentage-cell.medium {
    color: #d97706;
  }
  .percentage-cell.low {
    color: #dc2626;
  }
  .empty-cell {
    color: #9ca3af;
    font-style: italic;
  }
  
  /* ===== Stats Row ===== */
  .stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;padding:0 16px 14px}
  @media (max-width:992px){.stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
  .stat{
    border:1px solid #e5e7eb;border-radius:14px;background:#fcfdfd;padding:14px
  }
  .stat .k{font-size:13px;color:#64748b;margin-bottom:6px}
  .stat .v{font-weight:800;font-size:22px;color:#0f172a}
  .stat .hint{font-size:12px;color:#475569;margin-top:4px}

  /* ===== Print ===== */
  @media print {
    .btn,.content-header a{display:none!important}
    body{background:#fff!important}
    .report-wrap,.stat,.student-card{page-break-inside:avoid}
    .toppers-section {
      break-inside: avoid;
    }
  }

  .quiz-context-info {
  background: rgba(255, 255, 255, 0.8);
  border-radius: 8px;
  padding: 10px;
  margin: 0 20px 20px 20px;
  border: 1px solid #e5e7eb;
}

.quiz-context-info .badge {
  font-size: 14px;
  font-weight: 600;
  border-radius: 6px;
}

.quiz-context-info .badge-info {
  background: #dbeafe;
  color: #1e40af;
  border: 1px solid #93c5fd;
}

.quiz-context-info .badge-success {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #6ee7b7;
}

.quiz-context-info .badge-warning {
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fcd34d;
}
</style>

<section class="content">
  <div class="content-header d-flex align-items-center justify-content-between">
    <h1 class="mb-3">Quiz Results - Student Report</h1>
    <div>
      <a href="<?= base_url('admin/quizzes') ?>" class="btn btn-secondary btn-sm">Back</a>
      <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print"></i> Print Report</button>
    </div>
  </div>

  <!-- Report Header -->
  <div class="report-wrap">
    <div class="report-head">
      <div class="report-brand"><?= strtoupper(substr($title,0,1)) ?></div>
      <div class="flex-fill">
        <h3 class="report-title h4 mb-1"><?= esc($title) ?></h3>
        <div class="report-sub">
          <span class="chip"><b>Class</b> <?= esc($clsName) ?></span>
          <span class="chip"><b>Subject</b> <?= esc($subName) ?></span>
          <span class="chip"><b>Term</b> <?= esc($term) ?></span>
          <?php if (!empty($topics)): ?>
            <span class="chip">
              <b>Topics</b> <?= esc(implode(', ', $topics)) ?>
            </span>
          <?php endif; ?>
        </div>
        <div class="chips">
          <span class="chip">Created: <?= esc($created) ?></span>
          <span class="chip">Window: <?= esc($start) ?> — <?= esc($end) ?></span>
          <span class="chip">Max Attempts Allowed: <?= $maxAttempts ?></span>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats">
      <div class="stat">
        <div class="k">Total Students (Class Section)</div>
        <div class="v"><?= $totalStudents !== null ? (int)$totalStudents : '—' ?></div>
        <div class="hint">All active students mapped to this Class-Section</div>
      </div>
      <div class="stat">
        <div class="k">Students Attempted</div>
        <div class="v"><?= count($studentAttempts) ?></div>
        <div class="hint">Unique students with submissions</div>
      </div>
      <div class="stat">
        <div class="k">Participation</div>
        <div class="v"><?= $totalStudents ? number_format((count($studentAttempts) / $totalStudents) * 100, 1) . '%' : '—' ?></div>
        <div class="hint">Attempted ÷ Total Students</div>
      </div>
      <div class="stat">
        <div class="k">Average Score</div>
        <div class="v"><?= esc($avgScore) ?></div>
        <div class="hint">Across submitted attempts</div>
      </div>
    </div>
  </div>

 <?php if (!empty($toppers)): ?>
<div class="toppers-section">
  <div class="toppers-title">🏆 TOP PERFORMERS 🏆</div>
  
  <!-- Quiz Context Information -->
  <div class="quiz-context-info text-center mb-3">
    <div class="d-inline-flex flex-wrap justify-content-center align-items-center gap-3">
      <span class="badge badge-info px-3 py-2">
        <i class="fas fa-school mr-1"></i> Class: <?= esc($clsName) ?>
      </span>
      <span class="badge badge-success px-3 py-2">
        <i class="fas fa-book mr-1"></i> Subject: <?= esc($subName) ?>
      </span>
      <span class="badge badge-warning px-3 py-2">
        <i class="fas fa-calendar-alt mr-1"></i> Term: <?= esc($term) ?>
      </span>
    </div>
  </div>
  
  <div class="toppers-grid">
    <?php foreach ($toppers as $index => $topper): ?>
      <?php
        $position = $index + 1;
        $photo = $topper['profile_photo']
          ? base_url('uploads/'.ltrim($topper['profile_photo'],'/'))
          : base_url('resource/img/avatar-student.png');
      ?>
      <div class="topper-card position-<?= $position ?>">
        <div class="position-badge">
          <?php if ($position == 1): ?>🥇
          <?php elseif ($position == 2): ?>🥈
          <?php elseif ($position == 3): ?>🥉
          <?php endif; ?>
          Rank #<?= $position ?>
        </div>
        <img src="<?= esc($photo) ?>" alt="<?= esc($topper['student_name']) ?>" class="topper-photo">
        <div class="topper-name"><?= esc($topper['student_name']) ?></div>
        <div class="topper-percentage"><?= number_format($topper['total_percentage'], 1) ?>%</div>
        <div class="topper-label">Best Score</div>
        <div class="topper-label"><?= $topper['total_attempts'] ?> attempt(s)</div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

  <!-- All Student Results -->
  <div class="student-results">
    <h4 class="mb-3">All Student Results</h4>
    
    <?php if (!empty($studentAttempts)): ?>
      <?php foreach ($studentAttempts as $student): ?>
        <?php
          $photo = $student['profile_photo']
            ? base_url('uploads/'.ltrim($student['profile_photo'],'/'))
            : base_url('resource/img/avatar-student.png');
        ?>
        <div class="student-card">
          <div class="student-header">
            <img src="<?= esc($photo) ?>" alt="<?= esc($student['student_name']) ?>" class="student-photo">
            <div class="student-info">
              <h5 class="student-name"><?= esc($student['student_name']) ?></h5>
              <small class="text-muted">
                Student ID: <?= $student['student_id'] ?> | 
                Attempts: <?= $student['total_attempts'] ?>
              </small>
            </div>
            <div class="student-percentage">
              <?= number_format($student['total_percentage'], 1) ?>%
            </div>
          </div>
          
          <div class="table-responsive">
            <table class="attempts-table">
              <thead>
                <tr>
                  <th>Attempt #</th>
                  <th>Total Questions</th>
                  <th>Correct</th>
                  <th>Wrong</th>
                  <th>Score</th>
                  <th>Percentage</th>
                  <th>Submitted Date & Time</th>
                  <th>Duration</th>
                </tr>
              </thead>
              <tbody>
                <?php for ($attemptNo = 1; $attemptNo <= $maxAttempts; $attemptNo++): ?>
                  <tr>
                    <?php if (isset($student['attempts'][$attemptNo])): 
                      $attempt = $student['attempts'][$attemptNo];
                      $percentage = $attempt->percentage ?? 0;
                      $percentageClass = '';
                      if ($percentage >= 80) $percentageClass = 'high';
                      elseif ($percentage >= 50) $percentageClass = 'medium';
                      else $percentageClass = 'low';
                    ?>
                      <td><strong>#<?= $attemptNo ?></strong></td>
                      <td><?= $attempt->total_questions ?? 0 ?></td>
                      <td><?= $attempt->stat_correct ?? 0 ?></td>
                      <td><?= $attempt->stat_wrong ?? 0 ?></td>
                      <td><?= number_format($attempt->score_obtained ?? 0, 1) ?> / <?= $totalMarks ?></td>
                      <td class="percentage-cell <?= $percentageClass ?>">
                        <?= number_format($percentage, 1) ?>%
                      </td>
                      <td><?= $attempt->submitted_at ? date('d M Y, h:i A', strtotime($attempt->submitted_at)) : '—' ?></td>
                      <td><?= $attempt->duration_text ?? '—' ?></td>
                    <?php else: ?>
                      <td class="empty-cell">#<?= $attemptNo ?></td>
                      <td class="empty-cell">—</td>
                      <td class="empty-cell">—</td>
                      <td class="empty-cell">—</td>
                      <td class="empty-cell">—</td>
                      <td class="empty-cell">—</td>
                      <td class="empty-cell">Not attempted</td>
                      <td class="empty-cell">—</td>
                    <?php endif; ?>
                  </tr>
                <?php endfor; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="card">
        <div class="card-body text-center text-muted py-5">
          <i class="fas fa-clipboard-list fa-3x mb-3"></i>
          <h5>No students have attempted this quiz yet.</h5>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?= $this->endSection() ?>