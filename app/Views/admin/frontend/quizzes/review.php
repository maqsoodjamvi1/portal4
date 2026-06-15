<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<?php
// Normalize answers array
$answers = $answersByQ ?? $answers ?? [];

// Submitted date in "25-11-2025 Tuesday 9:30PM"
$submittedAtRaw  = $attempt->submitted_at ?? '';
$submittedAtNice = $submittedAtRaw
    ? date('d-m-Y l g:iA', strtotime($submittedAtRaw))
    : '';

$studentName       = $studentName       ?? '';
$classSectionLabel = $classSectionLabel ?? '';
$subjectName       = $subjectName       ?? '';
$topics            = $topics            ?? [];
$attemptsSummary   = $attemptsSummary   ?? [];
$studentPhotoUrl   = $studentPhotoUrl   ?? '';

$stats       = $stats       ?? ['total_questions' => 0, 'correct' => 0, 'wrong' => 0, 'unattempted' => 0, 'total_marks' => 0];
$percentage  = $percentage  ?? null;
$durationTxt = $durationText ?? '';

$typeLabelMap = [
    'mcq'          => 'MCQ (Single)',
    'mcq_single'   => 'MCQ (Single)',
    'mcq_multi'    => 'MCQ (Multiple)',
    'tf'           => 'True / False',
    'true_false'   => 'True / False',
    'fill'         => 'Fill in the Blanks',
    'fill_blank'   => 'Fill in the Blanks',
    'short'        => 'Short Answer',
    'short_answer' => 'Short Answer',
    'match'        => 'Matching',
];


$studentPhotoRaw = $studentPhotoUrl ?? ($studentPhoto ?? '');
$studentPhotoSrc = '';

if ($studentPhotoRaw) {
    // If it already looks like a full URL, keep it
    if (preg_match('~^https?://~i', $studentPhotoRaw)) {
        $studentPhotoSrc = $studentPhotoRaw;
    } else {
        // Treat as file under /uploads/
        $studentPhotoSrc = base_url('uploads/' . ltrim($studentPhotoRaw, '/'));
    }
} else {
    // Fallback avatar
    $studentPhotoSrc = base_url('resource/img/avatar-student.png');
}


// ====== PER-TYPE SUMMARY (for summary grid under header) ======
$perTypeStats  = [];
$questionsList = $qq ?? [];

if (!empty($questionsList)) {
    foreach ($questionsList as $row) {
        $rawType = strtolower($row->question_type ?? 'mcq');

        // Normalize type key
        if (in_array($rawType, ['mcq', 'mcq_single'], true)) {
            $typeKey = 'mcq_single';
        } elseif ($rawType === 'mcq_multi') {
            $typeKey = 'mcq_multi';
        } elseif (in_array($rawType, ['tf', 'true_false'], true)) {
            $typeKey = 'tf';
        } elseif (in_array($rawType, ['fill', 'fill_blank'], true)) {
            $typeKey = 'fill';
        } elseif (in_array($rawType, ['short', 'short_answer'], true)) {
            $typeKey = 'short';
        } else {
            $typeKey = $rawType;
        }

        $label = $typeLabelMap[$typeKey] ?? strtoupper($typeKey);

        if (!isset($perTypeStats[$typeKey])) {
            $perTypeStats[$typeKey] = [
                'label'        => $label,
                'total_q'      => 0,
                'correct'      => 0,
                'score'        => 0.0,
                'max_score'    => 0.0,
            ];
        }

        $qid = (int) $row->question_id;
        $ans = $answers[$qid] ?? null;

        $perTypeStats[$typeKey]['total_q']++;

        if ($ans) {
            $perTypeStats[$typeKey]['score'] += (float) ($ans->marks_awarded ?? 0);
            if (!empty($ans->is_correct)) {
                $perTypeStats[$typeKey]['correct']++;
            }
        }

        $perTypeStats[$typeKey]['max_score'] += (float) ($row->marks ?? 0);
    }
}

// Overall totals for summary
$totalQs   = (int)($stats['total_questions'] ?? 0);
$correct   = (int)($stats['correct'] ?? 0);
$wrong     = (int)($stats['wrong'] ?? 0);
$unattempt = (int)($stats['unattempted'] ?? 0);
$attempted = max(0, $totalQs - $unattempt);

$totalMaxMarks = !empty($stats['total_marks'])
    ? (float)$stats['total_marks']
    : (float)($attempt->total_marks ?? 0);

$totalScore = (float)($attempt->score_obtained ?? 0);

// Fixed order for compact 6-column summary
$typeOrder = ['mcq_single','mcq_multi','tf','fill','short','match'];
?>

<style>
  /* ---------- PAGE & PRINT LAYOUT ---------- */
  @page {
      size: A4 portrait;
      margin: 12mm;
  }

  .quiz-review-page {
      max-width: 960px;
      margin: 1.5rem auto 2.5rem;
      padding: 1.25rem;
      background: #f5f7fa;
      border-radius: .75rem;
      box-shadow: 0 2px 8px rgba(0,0,0,.05);
  }

  @media print {
      html, body {
          background: #fff !important;
      }

      body {
          margin: 0 !important;
          padding: 0 !important;
      }

      .quiz-review-page {
          box-shadow: none !important;
          border-radius: 0;
          background: #fff !important;
          margin: 0 auto !important;
          padding: 8mm 8mm 10mm !important;
      }

      .main-sidebar,
      .main-header,
      .main-footer,
      .navbar,
      .sidebar,
      .no-print {
          display: none !important;
      }

      .content-wrapper,
      .content {
          margin: 0 !important;
          padding: 0 !important;
      }

      /* Make sure header is visible in print */
      .content-header {
          display: block !important;
          margin: 0 !important;
          padding: 0 !important;
      }
  }

  /* ---------- GENERAL HEADER / SUMMARY ---------- */
  .quiz-review-header {
      margin-bottom: 1rem;
  }

  .quiz-header-card {
      border-radius: .6rem;
      border: 1px solid #dee2e6;
  }

  .student-photo-wrap {
      display: flex;
      align-items: center;
  }

  .student-photo {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #6c757d;
      margin-right: .75rem;
  }

  .quiz-title {
      font-size: 1.1rem;
      font-weight: 700;
  }

  .stat-pill {
      display: inline-block;
      padding: .25rem .6rem;
      border-radius: 999px;
      font-size: .78rem;
      margin: 0 .2rem .2rem 0;
      background: #f1f3f5;
      border: 1px solid #dee2e6;
  }

  .stat-pill.main {
      background: #e7f1ff;
      border-color: #b6d4fe;
      font-weight: 600;
  }

  .score-big {
      font-size: 1.15rem;
      font-weight: 700;
  }

  .percentage-inline {
      font-size: .9rem;
      color: #28a745;
      font-weight: 600;
      margin-left: .25rem;
  }

  .review-meta-small {
      font-size: .82rem;
  }

  /* ---------- ATTEMPT SUMMARY MINI CARDS ---------- */
  .attempt-mini-card {
      border-radius: .4rem;
      border: 1px solid #007bff;
      padding: .35rem .5rem;
      font-size: 12px;
      line-height: 1.3;
      background: #f8fbff;
  }
  .attempt-mini-card.current {
      border-width: 2px;
      background: #e7f1ff;
  }
  .attempt-mini-card .attempt-label {
      font-weight: 600;
  }

  /* ---------- COMPACT TYPE SUMMARY GRID ---------- */
  .type-summary-card {
      border-radius: .6rem;
      border: 1px solid #dee2e6;
      background: #ffffff;
      margin-bottom: .9rem;
  }

  .type-summary-card .card-header {
      padding: .35rem .75rem;
      border-bottom: 1px solid #dee2e6;
      font-weight: 600;
      font-size: .9rem;
  }

  .type-summary-card .card-body {
      padding: .4rem .75rem .4rem;
  }

  .type-summary-compact-table {
      width: 100%;
      table-layout: fixed;
      text-align: center;
      margin-bottom: .25rem;
  }

  .type-summary-compact-table th,
  .type-summary-compact-table td {
      font-size: .75rem;
      padding: .25rem .15rem;
      vertical-align: middle;
      border-top: none;
      border-bottom: none;
  }

  .type-summary-compact-table th {
      font-weight: 600;
      white-space: nowrap;
  }

  .type-summary-label {
      display: inline-block;
      padding: 2px 4px;
      border-radius: 999px;
      background: #e9f2ff;
      color: #084298;
  }

  .type-summary-metrics {
      font-family: "Roboto Mono", "Courier New", monospace;
      font-size: .72rem;
  }

  .type-summary-total {
      font-size: .78rem;
      text-align: right;
      color: #495057;
  }

  /* ---------- QUESTION BLOCKS ---------- */
  .question-block {
      border-radius: .6rem;
      border: 1px solid #d0d7de;
      background: #ffffff;
      box-shadow: 0 1px 4px rgba(15,23,42,.06);
      margin-bottom: 1.1rem;
      overflow: hidden;
      page-break-inside: avoid;
  }

  .question-header-bar {
      background: #f1f3f5;
      border-bottom: 1px solid #d0d7de;
      padding: .5rem .9rem;
  }

  .question-header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:10px;
  }

  .question-header-left{
      display:flex;
      align-items:center;
      flex-wrap:wrap;
      gap:.5rem;
  }

  .question-number{
      font-weight:700;
      margin-right:.25rem;
  }

  .question-text{
      font-size:.95rem;
      font-weight:600;
  }

  .question-header-right{
      display:flex;
      align-items:center;
      justify-content:flex-end;
      gap:.4rem;
      text-align:right;
  }

  .question-marks{
      font-weight:700;
      font-size:.95rem;
  }

  .question-type-badge{
      display:inline-block;
      padding:2px 8px;
      border-radius:999px;
      background:#e3f2ff;
      color:#00509e;
      font-size:.72rem;
      font-weight:600;
      white-space:nowrap;
  }

  .question-icon-badge{
      width:34px;
      height:34px;
      border-radius:50%;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      font-size:1.35rem;
  }

  .question-icon-badge.correct{
      background:#e6fbf3;
      color:#1cc88a;
      box-shadow:0 0 0 2px rgba(28,200,138,.25);
  }

  .question-icon-badge.wrong{
      background:#ffe8e6;
      color:#e74a3b;
      box-shadow:0 0 0 2px rgba(231,74,59,.25);
  }

  .question-body {
      padding: .7rem .9rem .8rem;
  }

  /* ---------- OPTIONS AS FLEX CARDS ---------- */
  .options-wrap {
      display: flex;
      flex-wrap: wrap;
      margin-top: .3rem;
  }

  .option-pill {
      border-radius: .5rem;
      border: 1px solid rgba(0,0,0,.12);
      padding: .35rem .6rem;
      margin: 0 .5rem .5rem 0;
      display: inline-flex;
      align-items: flex-start;
      font-size: 14px;
      background: #ffffff;
      min-width: 120px;
      max-width: 100%;
  }

  /* Color discrimination for MCQ */
  .option-correct-selected {
      background: #e6fbf3;
      border-color: #28a745;
  }

  .option-correct-only {
      background: #e8f7fb;
      border-color: #17a2b8;
  }

  .option-selected-wrong {
      background: #ffecec;
      border-color: #dc3545;
  }

  .option-label {
      font-weight: 600;
      margin-right: .35rem;
      flex-shrink: 0;
  }

  .option-text {
      flex: 1;
      word-wrap: break-word;
      white-space: normal;
  }

  .option-icons {
      margin-left: .5rem;
      flex-shrink: 0;
      font-size: .8rem;
      text-align:right;
  }

  .option-icons span {
      display:block;
      line-height: 1.1;
  }

  .match-table td,
  .match-table th {
      font-size: .85rem;
      vertical-align: middle;
  }

  /* Inline answer row for TF / Fill / Short & MCQ summary */
  .answer-inline-row {
      font-size: .9rem;
  }
  .answer-inline-row strong {
      font-weight: 600;
  }
  .answer-separator {
      margin: 0 .75rem;
  }

  .ans-your {
      font-weight: 500;
  }

  .ans-correct {
      font-weight: 500;
  }

  

  /* --- Mobile-friendly type summary cards --- */
.type-summary-mobile-list {
    display: flex;
    flex-direction: column;
    gap: .35rem;
}

.type-summary-mobile-item {
    border-radius: .45rem;
    border: 1px solid #dee2e6;
    padding: .35rem .6rem;
    background: #ffffff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: .78rem;
}

.type-summary-mobile-label {
    font-weight: 600;
    margin-bottom: 2px;
}

.type-summary-mobile-metrics {
    font-family: "Roboto Mono","Courier New",monospace;
    text-align: right;
    line-height: 1.3;
}

/* Slightly smaller font for the desktop table on very small screens if shown */
@media (max-width: 575.98px) {
    .type-summary-compact-table th,
    .type-summary-compact-table td {
        font-size: .7rem;
    }

    .answer-inline-row {
          display: block;
      }
      .answer-separator {
          display: block;
          margin: .2rem 0;
      }
}



</style>

<div class="quiz-review-page">

  <section class="content-header mb-3 quiz-review-header">
    <div class="card quiz-header-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap">

          <!-- LEFT: Student info + photo -->
          <div class="col-md-4 col-sm-12 ps-0 pe-md-2 mb-2 mb-md-0">
            <div class="student-photo-wrap">
             <?php if (!empty($studentPhotoSrc)): ?>
  <img src="<?= esc($studentPhotoSrc) ?>"
       alt="Student Photo"
       class="student-photo">
<?php else: ?>
  <img src="<?= base_url('resource/img/avatar-student.png') ?>"
       alt="Student Photo"
       class="student-photo">
<?php endif; ?>

              <div>
                <div class="quiz-title mb-1">
                  <?= esc($quiz->title) ?>
                </div>

                <?php if ($studentName): ?>
                  <div><strong>Student:</strong> <?= esc($studentName) ?></div>
                <?php endif; ?>

                <?php if ($classSectionLabel): ?>
                  <div><strong>Class:</strong> <?= esc($classSectionLabel) ?></div>
                <?php endif; ?>

                <?php if ($subjectName): ?>
                  <div><strong>Subject:</strong> <?= esc($subjectName) ?></div>
                <?php endif; ?>

                <?php if (!empty($topics) && is_array($topics)): ?>
                  <div class="mb-1 mt-1">
                    <strong>Topics:</strong><br>
                    <?php foreach ($topics as $t): ?>
                      <span class="badge text-bg-info me-1 mt-1"><?= esc($t) ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- MIDDLE: Attempt info -->
          <div class="col-md-4 col-sm-12 px-md-2 mb-2 mb-md-0">
            <div class="review-meta-small mt-1">
              <div>
                <span class="stat-pill main">
                  Attempt #<?= (int)$attempt->attempt_no ?>
                </span>
              </div>

              <?php if ($submittedAtNice): ?>
                <div>
                  <span class="stat-pill">
                    Submitted: <?= esc($submittedAtNice) ?>
                  </span>
                </div>
              <?php endif; ?>

              <?php if ($durationTxt): ?>
                <div>
                  <span class="stat-pill">
                    Duration: <?= esc($durationTxt) ?>
                  </span>
                </div>
              <?php endif; ?>

              <?php if (!empty($quiz->negative_mark_per_q) && (float)$quiz->negative_mark_per_q > 0): ?>
                <div>
                  <span class="stat-pill">
                    −<?= (float)$quiz->negative_mark_per_q ?> per wrong answer
                  </span>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- RIGHT: Score + counts + print button -->
          <div class="col-md-4 col-sm-12 pe-0 ps-md-2 text-md-end text-start">

            <!-- Score + percentage in same line -->
            <div class="mb-1">
              <span class="score-big">
                <?= $totalScore ?>
              </span>

              <?php if ($totalMaxMarks > 0): ?>
                / <span class="score-big"><?= $totalMaxMarks ?></span>
              <?php endif; ?>

              <?php if ($percentage !== null): ?>
                <span class="percentage-inline">
                  (<?= $percentage ?>%)
                </span>
              <?php endif; ?>
            </div>

            <!-- Stats -->
            <div class="review-meta-small mb-3">
              <div class="d-flex flex-wrap justify-content-md-end mb-1" style="gap:.35rem;">
                <span class="stat-pill main">
                  <i class="fas fa-list-ol me-1"></i>
                  <?= $totalQs ?> Total
                </span>
                <span class="stat-pill">
                  <i class="fas fa-pencil-alt me-1"></i>
                  <?= $attempted ?> Attempted
                </span>
              </div>

              <div class="d-flex flex-wrap justify-content-md-end" style="gap:.35rem;">
                <span class="stat-pill text-success">
                  <i class="fas fa-check-circle me-1"></i>
                  <?= $correct ?> Correct
                </span>
                <span class="stat-pill text-danger">
                  <i class="fas fa-times-circle me-1"></i>
                  <?= $wrong ?> Wrong
                </span>
              </div>
            </div>

             <a href="<?= base_url('student/dashboard') ?>"
         class="btn btn-sm btn-outline-primary mb-1 me-1">
        <i class="fas fa-home"></i> Home
      </a>

      <button type="button"
              onclick="window.print()"
              class="btn btn-sm btn-outline-secondary mb-1">
        <i class="fas fa-print"></i> Print
      </button>
          </div>

        </div>
      </div>
    </div>
  </section>

  <section class="content">

        <!-- ===== QUESTION TYPE SUMMARY (desktop table + mobile cards) ===== -->
    <div class="card type-summary-card">
      <div class="card-header">
        Question Type Summary
      </div>
      <div class="card-body">

        <!-- Desktop / tablet: 6-column compact table -->
        <div class="table-responsive d-none d-sm-block">
          <table class="type-summary-compact-table">
            <thead>
              <tr>
                <?php foreach ($typeOrder as $tKey): ?>
                  <?php
                    $label = isset($perTypeStats[$tKey])
                        ? $perTypeStats[$tKey]['label']
                        : ($typeLabelMap[$tKey] ?? strtoupper($tKey));
                  ?>
                  <th>
                    <span class="type-summary-label"><?= esc($label) ?></span>
                  </th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <tr>
                <?php foreach ($typeOrder as $tKey): ?>
                  <?php
                    $s = $perTypeStats[$tKey] ?? [
                        'total_q'   => 0,
                        'correct'   => 0,
                        'score'     => 0.0,
                        'max_score' => 0.0,
                    ];
                  ?>
                  <td>
                    <div class="type-summary-metrics">
                      Q: <?= (int)$s['total_q'] ?>
                      &nbsp; C: <?= (int)$s['correct'] ?><br>
                      S: <?= number_format($s['score'], 2) ?>
                      <?php if ($s['max_score'] > 0): ?>
                        / <?= number_format($s['max_score'], 2) ?>
                      <?php endif; ?>
                    </div>
                  </td>
                <?php endforeach; ?>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile: stacked cards, one per type -->
        <div class="type-summary-mobile-list d-block d-sm-none">
          <?php foreach ($typeOrder as $tKey): ?>
            <?php
              $stat  = $perTypeStats[$tKey] ?? [
                  'label'     => $typeLabelMap[$tKey] ?? strtoupper($tKey),
                  'total_q'   => 0,
                  'correct'   => 0,
                  'score'     => 0.0,
                  'max_score' => 0.0,
              ];
              $label = $stat['label'];
            ?>
            <div class="type-summary-mobile-item">
              <div>
                <div class="type-summary-mobile-label">
                  <?= esc($label) ?>
                </div>
              </div>
              <div class="type-summary-mobile-metrics">
                Q: <?= (int)$stat['total_q'] ?><br>
                C: <?= (int)$stat['correct'] ?><br>
                S: <?= number_format($stat['score'], 2) ?>
                <?php if ($stat['max_score'] > 0): ?>
                  / <?= number_format($stat['max_score'], 2) ?>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="type-summary-total mt-2">
          Total: <?= $totalQs ?> questions,
          <?= $correct ?> correct,
          Score <?= number_format($totalScore, 2) ?>
          <?php if ($totalMaxMarks > 0): ?>
            / <?= number_format($totalMaxMarks, 2) ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- ===== Compact attempt summary cards (3 per row) ===== -->
    <div class="row mb-3">
      <?php
        $summaryList = !empty($attemptsSummary) && is_array($attemptsSummary)
            ? $attemptsSummary
            : [$attempt];
      ?>

      <?php foreach ($summaryList as $a): ?>
        <?php
          $aSubmittedRaw  = $a->submitted_at ?? '';
          $aSubmittedNice = $aSubmittedRaw
              ? date('d-m-Y l g:iA', strtotime($aSubmittedRaw))
              : '';
          $aScore   = $a->score_obtained ?? 0;
          $aTotal   = $a->total_marks    ?? null;
          $isActive = isset($attempt->attempt_id) && $attempt->attempt_id == $a->attempt_id;
        ?>
        <div class="col-md-4 col-sm-6 mb-2">
          <div class="attempt-mini-card <?= $isActive ? 'current' : '' ?>">
            <div class="attempt-label">
              Attempt #<?= (int)$a->attempt_no ?>
              <?= $isActive ? '(current)' : '' ?>
            </div>
            <div>
              Score:
              <strong><?= esc($aScore) ?></strong>
              <?php if ($aTotal !== null): ?>
                / <strong><?= esc($aTotal) ?></strong>
              <?php endif; ?>
            </div>
            <div class="text-muted">
              <?php if ($aSubmittedNice): ?>
                Submitted: <?= esc($aSubmittedNice) ?>
              <?php else: ?>
                Not submitted
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (empty($canShowSolution) || !$canShowSolution): ?>
      <div class="alert alert-info">
        Solutions are disabled for this quiz.
      </div>
    <?php else: ?>

      <?php if (empty($qq)): ?>
        <div class="alert alert-secondary">
          No questions found for this attempt.
        </div>
      <?php else: ?>

        <?php $idx = 1; ?>
        <?php foreach ($qq as $row): ?>
          <?php
            $qid       = (int) $row->question_id;
            $ans       = $answers[$qid] ?? null;
            $type      = strtolower($row->question_type ?? 'mcq');
            $typeLabel = $typeLabelMap[$type] ?? strtoupper($type);

            $isCorrect = $ans ? (int)$ans->is_correct : 0;
            $awarded   = $ans ? (float)$ans->marks_awarded : 0.0;
            $maxMarks  = (float) ($row->marks ?? 0);

            // Show awarded marks as positive even for negative marking
            $displayMarks = ($awarded < 0) ? abs($awarded) : $awarded;
          ?>

          <div class="question-block">
            <div class="question-header-bar">
              <div class="question-header">
                <!-- LEFT: Q no + question text -->
                <div class="question-header-left">
                  <div class="question-number">Q<?= $idx++ ?>.</div>
                  <div class="question-text">
                    <?= nl2br(esc($row->question)) ?>
                  </div>
                </div>

                <!-- RIGHT: score | icon | type (right aligned) -->
                <div class="question-header-right">
                  <div class="question-marks">
                    <?= $displayMarks ?>
                  </div>

                  <?php if ($isCorrect): ?>
                    <span class="question-icon-badge correct" title="Correct">
                      <i class="fas fa-check"></i>
                    </span>
                  <?php else: ?>
                    <span class="question-icon-badge wrong" title="Incorrect">
                      <i class="fas fa-times"></i>
                    </span>
                  <?php endif; ?>

                  <span class="question-type-badge">
                    <?= esc($typeLabel) ?>
                  </span>
                </div>
              </div>
            </div>

            <div class="question-body">

              <!-- 1) MCQ (single & multi) -->
              <?php if (in_array($type, ['mcq','mcq_single','mcq_multi'], true)): ?>

                <?php
                  $options = [];
                  $json    = null;

                  if ($type === 'mcq_multi' && !empty($row->options_json)) {
                      $json = json_decode($row->options_json, true);
                      if (!empty($json['options']) && is_array($json['options'])) {
                          $options = $json['options'];
                      }
                  }

                  if (empty($options)) {
                      $options = [
                          'A' => $row->option_a ?? '',
                          'B' => $row->option_b ?? '',
                          'C' => $row->option_c ?? '',
                          'D' => $row->option_d ?? '',
                      ];
                  }

                  $correctKeys = [];
                  if ($type === 'mcq_multi' && !empty($json['correct_multi']) && is_array($json['correct_multi'])) {
                      $correctKeys = array_map('strtoupper', $json['correct_multi']);
                  } else {
                      $correctKeys = [ strtoupper($row->correct_option ?? '') ];
                  }

                  $selected = [];
                  if ($ans) {
                      if ($type === 'mcq_multi') {
                          $arr = json_decode($ans->selected_options ?? '[]', true) ?: [];
                          $selected = array_map('strtoupper', $arr);
                      } else {
                          $selected = [ strtoupper($ans->selected_option ?? '') ];
                      }
                  }

                  // Build text summary for your answer / correct answer
                  $yourAnswerParts    = [];
                  $correctAnswerParts = [];

                  foreach ($selected as $L) {
                      $L = strtoupper($L);
                      if (isset($options[$L])) {
                          $yourAnswerParts[] = $L . ') ' . $options[$L];
                      }
                  }
                  foreach ($correctKeys as $L) {
                      $L = strtoupper($L);
                      if (isset($options[$L])) {
                          $correctAnswerParts[] = $L . ') ' . $options[$L];
                      }
                  }

                  $yourAnswerSummary    = implode('; ', $yourAnswerParts);
                  $correctAnswerSummary = implode('; ', $correctAnswerParts);
                ?>

               <div class="options-wrap">
  <?php foreach ($options as $L => $val): ?>
    <?php if (trim((string)$val) === '') continue; ?>

    <?php
      $upperL        = strtoupper($L);
      $isOptCorrect  = in_array($upperL, $correctKeys, true);
      $isOptSelected = in_array($upperL, $selected, true);

      $classes = 'option-pill';
      if ($isOptCorrect && $isOptSelected) {
          $classes .= ' option-correct-selected';
      } elseif ($isOptCorrect) {
          $classes .= ' option-correct-only';
      } elseif ($isOptSelected) {
          $classes .= ' option-selected-wrong';
      }
    ?>
    <div class="<?= $classes ?>">
      <div class="option-label"><?= $upperL ?>)</div>
      <div class="option-text"><?= esc($val) ?></div>
    </div>
  <?php endforeach; ?>
</div>
                <!-- One-line summary for MCQ answers (better for user & print) -->
                <div class="answer-inline-row mt-2">
                  <strong>Your answer:</strong>
                  <?php if ($yourAnswerSummary !== ''): ?>
                    <span class="ans-your <?= $isCorrect ? 'text-success' : 'text-danger' ?>">
                      <?= esc($yourAnswerSummary) ?>
                    </span>
                  <?php else: ?>
                    <span class="text-muted">No answer</span>
                  <?php endif; ?>

                  <span class="answer-separator d-inline-block">|</span>

                  <strong>Correct answer:</strong>
                  <?php if ($correctAnswerSummary !== ''): ?>
                    <span class="ans-correct text-success">
                      <?= esc($correctAnswerSummary) ?>
                    </span>
                  <?php else: ?>
                    <span class="text-muted">Not configured</span>
                  <?php endif; ?>
                </div>

              <!-- 2) True/False, Fill, Short Answer -->
              <?php elseif (in_array($type, [
                  'tf','true_false','fill','fill_blank','short','short_answer'
              ], true)): ?>

                <?php
                  $yourAnswerText =
                      isset($ans->answer_text) && $ans->answer_text !== ''
                          ? $ans->answer_text
                          : '';

                  $correctAnswerText =
                      ($row->correct_answer_text ?? '') ? $row->correct_answer_text :
                      (($row->correct_answer ?? '')      ? $row->correct_answer :
                      (($row->answer_text ?? '')         ? $row->answer_text : ''));

                  $yourAnswerText    = $yourAnswerText    !== '' ? $yourAnswerText    : null;
                  $correctAnswerText = $correctAnswerText !== '' ? $correctAnswerText : null;
                ?>

                <!-- One-row inline format to save vertical space -->
                <div class="answer-inline-row">
                  <strong>Your answer:</strong>
                  <?php if ($yourAnswerText !== null): ?>
                    <span class="ans-your <?= $isCorrect ? 'text-success' : 'text-danger' ?>">
                      <?= esc($yourAnswerText) ?>
                    </span>
                  <?php else: ?>
                    <span class="text-muted">No answer</span>
                  <?php endif; ?>

                  <span class="answer-separator d-inline-block">|</span>

                  <strong>Correct answer:</strong>
                  <?php if ($correctAnswerText !== null): ?>
                    <span class="ans-correct text-success">
                      <?= esc($correctAnswerText) ?>
                    </span>
                  <?php else: ?>
                    <span class="text-muted">Not configured</span>
                  <?php endif; ?>
                </div>

              <!-- 3) Match the Column -->
              <?php elseif ($type === 'match'): ?>

                <?php
                  $pairs = json_decode($row->options_json ?? '[]', true) ?: [];
                  $userPairs = $ans ? (json_decode($ans->answer_text ?? '[]', true) ?: []) : [];

                  $userMap = [];
                  foreach ($userPairs as $p) {
                      $L = $p['left']  ?? '';
                      $V = $p['value'] ?? '';
                      if ($L !== '') {
                          $userMap[$L] = $V;
                      }
                  }
                ?>

                <?php if (!empty($pairs)): ?>
                  <table class="table table-bordered table-sm match-table mt-2">
                    <thead>
                      <tr>
                        <th style="width:35%;">Left</th>
                        <th style="width:35%;">Correct Match</th>
                        <th style="width:30%;">Your Answer</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($pairs as $p):
                          $left   = $p['left'] ?? '';
                          $right  = $p['right'] ?? '';
                          $given  = $userMap[$left] ?? '';
                          $isPairCorrect = (
                              trim(mb_strtolower($given)) === trim(mb_strtolower($right))
                          );
                      ?>
                        <tr>
                          <td><strong><?= esc($left) ?></strong></td>
                          <td><?= esc($right) ?></td>
                          <td>
                            <?php if ($given === ''): ?>
                              <span class="text-muted">No answer</span>
                            <?php else: ?>
                              <span class="<?= $isPairCorrect ? 'text-success' : 'text-danger' ?>">
                                <?= esc($given) ?>
                                <?= $isPairCorrect ? '✔' : '✗' ?>
                              </span>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php else: ?>
                  <div class="text-muted">Match pairs not configured.</div>
                <?php endif; ?>

              <!-- 4) Fallback -->
              <?php else: ?>
                <div class="text-muted">
                  Unsupported question type: <?= esc($type) ?>
                </div>

                <?php if ($ans && $ans->answer_text): ?>
                  <pre class="small mb-0"><?= esc($ans->answer_text) ?></pre>
                <?php endif; ?>

              <?php endif; ?>

              <?php if (!empty($row->explanation)): ?>
                <div class="mt-2 alert alert-secondary">
                  <strong>Explanation:</strong><br>
                  <?= nl2br(esc($row->explanation)) ?>
                </div>
              <?php endif; ?>

            </div><!-- /.question-body -->
          </div><!-- /.question-block -->

        <?php endforeach; ?>

      <?php endif; ?>

    <?php endif; ?>

  </section>

</div><!-- /.quiz-review-page -->

<?= $this->endSection() ?>
