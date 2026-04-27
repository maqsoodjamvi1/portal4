<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  $title    = $quiz->title        ?? 'Quiz';
  $clsName  = $quiz->cls_sec_name ?? '';
  $subName  = $quiz->sec_sub_name ?? '';
  $timeMin  = !empty($quiz->time_limit_sec)
              ? (int) ceil(((int)$quiz->time_limit_sec) / 60)
              : null;

  $topics   = $topics ?? [];
  $school   = $system ?? null;
  $campus   = $campus ?? null;

  $schoolName = $school->system_name ?? '';
  $schoolLogo = !empty($school->logo) ? base_url($school->logo) : '';
  $campusName = $campus->campus_name ?? '';
  $location   = $campus->location    ?? '';
  
  $defaultAvatar = base_url('resource/img/avatar-student.png');
  $uploadsBase = base_url('uploads/');
?>

<!-- Print and Back Buttons at Top (Only Once) -->
<div class="no-print mb-4">
  <button class="btn btn-primary btn-lg" id="printBtn">
    <i class="fas fa-print"></i> Print All Quiz Versions
  </button>
  <a href="<?= base_url('admin/quizzes') ?>" class="btn btn-secondary btn-lg">Back to Quizzes</a>
</div>

<section class="content">

  <style>
  /* ========== PRINT STYLES ========== */
  @media print {
    @page {
      size: A4;
      margin: 15mm;
    }

    @page :first {
      margin-top: 15mm;
    }

    @page :not(:first) {
      margin-top: 15mm; /* Consistent margin on all pages */
    }

    body, .content-wrapper {
      margin: 0 !important;
      padding: 0 !important;
      background: #fff !important;
      font-size: 12px !important;
    }

    .no-print,
    .content-header,
    .main-header,
    .main-sidebar,
    .main-footer,
    .btn,
    .breadcrumb {
      display: none !important;
    }

    /* Each student's quiz wrapper */
    .exam-page {
      margin: 0;
      padding: 0;
      border: none;
      page-break-after: always;
      page-break-inside: auto;
    }

    /* Ensure proper spacing on second page */
    .exam-page:not(:first-of-type) {
      page-break-before: always;
    }

    /* Keep header on each page */
    .printable-header {
      page-break-inside: avoid;
      page-break-after: avoid;
    }

    /* Allow question groups to split naturally */
    .question-type-group,
    .q-grid,
    .type-heading {
      break-inside: auto !important;
      page-break-inside: auto !important;
      page-break-before: auto !important;
    }

    /* Keep question cards together */
    .q-card {
      break-inside: avoid !important;
      page-break-inside: avoid !important;
    }

    /* Ensure signature stays at bottom */
    .signature-section {
      page-break-inside: avoid;
      page-break-before: avoid;
    }
  }

  /* ========== SCREEN STYLES ========== */
  .exam-page {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    box-sizing: border-box;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }

  /* ========== HEADER STYLES ========== */
  .printable-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 15px;
    margin-bottom: 15px;
    border-bottom: 2px solid #000;
    page-break-inside: avoid;
  }

  .header-left {
    width: 150px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
  }

  .student-photo-container {
    width: 120px;
    height: 120px;
    border: 2px solid #004085;
    border-radius: 50% / 60%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
  }

  .student-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .student-name-header {
    font-size: 16px;
    font-weight: 800;
    color: #004085;
    text-transform: uppercase;
    text-align: center;
    margin-top: 5px;
  }

  .header-center {
    flex: 1;
    text-align: center;
    padding: 0 20px;
  }

  .school-name {
    font-size: 28px;
    font-weight: 900;
    text-transform: uppercase;
    color: #800000;
    margin-bottom: 5px;
    font-family: 'Bebas Neue', cursive;
    letter-spacing: 1px;
  }

  .campus-name {
    font-size: 20px;
    font-weight: 700;
    color: #004085;
    margin-bottom: 3px;
  }

  .campus-location {
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
  }

  .quiz-main-title {
    font-size: 22px;
    font-weight: 800;
    color: #17a2b8;
    margin: 10px 0 5px 0;
    border-bottom: 1px solid #17a2b8;
    padding-bottom: 5px;
    display: inline-block;
  }

  .quiz-meta {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin: 5px 0;
  }

  .quiz-topics {
    font-size: 13px;
    color: #666;
    margin-top: 3px;
  }

  .header-right {
    width: 150px;
    text-align: center;
    display: flex;
    flex-direction: row-reverse;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
  }

  .school-logo-container {
    width: 100px;
    height: 100px;
    border: 2px solid #004085;
    border-radius: 4px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
  }

  .school-logo {
    max-width: 90px;
    max-height: 90px;
    object-fit: contain;
  }

  .qr-code-container {
    width: 100px;
    height: 100px;
    border: 1px solid #ddd;
    padding: 5px;
    background: #fff;
  }

  .qr-code {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }

  /* ========== STUDENT INFO ROW ========== */
  .student-info-row {
    display: flex;
    justify-content: space-between;
    background: #f8f9fa;
    padding: 10px 15px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    margin-bottom: 20px;
    font-size: 14px;
    font-weight: 600;
  }

  .student-info-item {
    display: flex;
    align-items: center;
    gap: 5px;
  }

  .info-label {
    color: #800000;
    font-weight: 700;
  }

  /* ========== 2-COLUMN QUESTION LAYOUT ========== */
  .q-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }

  .question-type-group {
    grid-column: 1 / -1;
    margin-bottom: 15px;
  }

  .type-heading {
    background: #004085;
    color: white;
    padding: 8px 12px;
    font-size: 16px;
    font-weight: 700;
    border-radius: 4px;
    margin-bottom: 10px;
    text-transform: uppercase;
  }

  /* ========== QUESTION CARD STYLES ========== */
  .q-card {
    border: 1px solid #004085;
    border-radius: 6px;
    padding: 12px;
    background: #fff;
    display: flex;
    flex-direction: column;
  }

  .q-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
    padding-bottom: 5px;
    border-bottom: 1px solid #e5e7eb;
  }

  .q-top-row {
    display: grid;
    grid-template-columns: auto 1fr;
    column-gap: 6px;
    align-items: baseline;
  }

  .q-no-text {
    font-size: 16px;
    font-weight: 800;
    color: #800000;
    min-width: 38px;
    text-align: right;
    margin: 0;
    line-height: 1.4;
  }

  .q-type-badge {
    font-size: 11px;
    background: #17a2b8;
    color: white;
    padding: 3px 8px;
    border-radius: 12px;
    font-weight: 600;
    text-transform: uppercase;
  }

  .q-content {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1;
  }

  .blank-inline {
    display: inline-block;
    border-bottom: 2px solid #000;
    min-width: 80px;
    margin: 0 5px;
    height: 20px;
  }

  .q-text {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.4;
    color: #333;
    margin: 0;
    white-space: normal;
  }

  /* ========== OPTIONS LAYOUT ========== */
  .q-options {
    margin-top: 8px;
    margin-bottom: 0;
  }

  .q-opt {
    display: flex;
    align-items: flex-start;
    gap: 4px;
    margin-bottom: 4px;
  }

  .q-opt:last-child {
    margin-bottom: 0;
  }

  .q-opt-label {
    font-weight: 700;
    color: #004085;
    width: 22px;
    flex-shrink: 0;
    text-align: right;
    line-height: 1.4;
    margin: 0;
  }

  .q-opt-text {
    font-size: 13px;
    line-height: 1.4;
    word-wrap: break-word;
    word-break: break-word;
    white-space: normal;
    flex: 1;
  }

  .q-options-cols-1 { display: block; }
  .q-options-cols-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }
  .q-options-cols-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 6px;
  }

  /* ========== SHORT ANSWER LINES ========== */
  .short-lines { margin-top: 10px; }
  .short-line {
    display: block;
    border-bottom: 1px solid #000;
    height: 20px;
    margin-bottom: 8px;
  }

  /* ========== MATCH TABLE ========== */
  .match-table {
    margin-top: 10px;
    border: 1px solid #004085;
    border-radius: 4px;
    overflow: hidden;
  }

  .match-header {
    display: flex;
    background: #004085;
    color: white;
    font-weight: 700;
  }

  .match-header div {
    flex: 1;
    padding: 8px;
    text-align: center;
  }

  .match-row {
    display: flex;
    border-top: 1px solid #dee2e6;
  }

  .match-row div {
    flex: 1;
    padding: 8px;
    text-align: center;
  }

  /* ========== SIGNATURE SECTION ========== */
  .signature-section {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #000;
    display: flex;
    justify-content: space-between;
    page-break-inside: avoid;
  }

  .signature-box {
    text-align: center;
    width: 30%;
  }

  .signature-line {
    margin-top: 40px;
    border-top: 1px solid #000;
    width: 100%;
  }

  .signature-label {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
  }

  /* ========== RESPONSIVE ========== */
  @media (max-width: 1200px) {
    .q-grid { grid-template-columns: repeat(2, 1fr); }
  }

  @media (max-width: 768px) {
    .q-grid { grid-template-columns: 1fr; }

    .printable-header {
      flex-direction: column;
      gap: 15px;
    }

    .header-left,
    .header-center,
    .header-right {
      width: 100%;
    }
  }
</style>

  <?php 
  // Group questions by type for better organization
  function groupQuestionsByType($versions) {
    $grouped = [];
    foreach ($versions as $version) {
      $questionsByType = [];
      foreach ($version['questions'] as $q) {
        $type = strtolower($q->question_type ?? 'mcq');
        if (!isset($questionsByType[$type])) {
          $questionsByType[$type] = [];
        }
        $questionsByType[$type][] = $q;
      }
      $grouped[] = [
        'student' => $version['student'],
        'qr_url' => $version['qr_url'],
        'questions_by_type' => $questionsByType
      ];
    }
    return $grouped;
  }

  $groupedVersions = groupQuestionsByType($versions);
  $page = 0; 
  ?>

  <?php foreach ($groupedVersions as $version): $page++; ?>
    <?php
      $student   = $version['student'];
      $questionsByType = $version['questions_by_type'];
      $qrUrl     = $version['qr_url'];

      $sFirst = trim((string)($student->first_name ?? ''));
      $sLast  = trim((string)($student->last_name ?? ''));
      $sName  = trim($sFirst.' '.$sLast);
      if ($sName === '') {
          $sName = 'Student #'.$student->student_id;
      }

      // Profile photo logic
      $photoRaw = trim((string)($student->profile_photo ?? ''));
      if ($photoRaw !== '') {
          if (preg_match('#^https?://#i', $photoRaw)) {
              $studentPhoto = $photoRaw;
          } else {
              $studentPhoto = $uploadsBase . ltrim($photoRaw, '/');
          }
      } else {
          $studentPhoto = $defaultAvatar;
      }
    ?>

    <div class="exam-page" id="quiz-page-<?= $page ?>">
      
      <!-- HEADER (Result Card Style) -->
      <div class="printable-header">
        <!-- LEFT: Student Photo -->
        <div class="header-left">
          <div class="student-photo-container">
            <img src="<?= esc($studentPhoto) ?>" alt="Student Photo" class="student-photo" 
                 onerror="this.src='<?= esc($defaultAvatar) ?>'">
          </div>
          <div class="student-name-header"><?= esc($sName) ?></div>
        </div>

        <!-- CENTER: School & Quiz Info -->
        <div class="header-center">
          <?php if ($schoolName): ?>
            <div class="school-name"><?= esc($schoolName) ?></div>
          <?php endif; ?>

          <?php if ($campusName): ?>
            <div class="campus-name"><?= esc($campusName) ?></div>
          <?php endif; ?>

          <?php if ($location): ?>
            <div class="campus-location"><?= esc($location) ?></div>
          <?php endif; ?>

          <div class="quiz-main-title"><?= esc($title) ?></div>

          <div class="quiz-meta">
            <?php if ($clsName): ?>
              Class/Section: <strong><?= esc($clsName) ?></strong>
            <?php endif; ?>
            <?php if ($subName): ?>
              &nbsp;|&nbsp; Subject: <strong><?= esc($subName) ?></strong>
            <?php endif; ?>
            <?php if ($timeMin): ?>
              &nbsp;|&nbsp; Time: <strong><?= $timeMin ?> min</strong>
            <?php endif; ?>
          </div>

          <?php if (!empty($topics)): ?>
            <div class="quiz-topics">
              Topics: <em><?= esc(implode(', ', $topics)) ?></em>
            </div>
          <?php endif; ?>
        </div>

        <!-- RIGHT: School Logo & QR Code -->
        <div class="header-right">
          <?php if ($schoolLogo): ?>
            <div class="school-logo-container">
              <img src="<?= esc($schoolLogo) ?>" alt="School Logo" class="school-logo">
            </div>
          <?php endif; ?>
          
          <?php if (!empty($qrUrl)): ?>
            <div class="qr-code-container">
              <img src="<?= esc($qrUrl) ?>" class="qr-code" alt="QR Code">
            </div>
          <?php endif; ?>
        </div>
        
      </div>

      <!-- STUDENT INFO ROW -->
      <div class="student-info-row">
        <div class="student-info-item">
          <span class="info-label">Roll No:</span>
          <span><?= esc($student->roll_no ?? '__________') ?></span>
        </div>
        <div class="student-info-item">
          <span class="info-label">Date:</span>
          <span><?= date('d/m/Y') ?></span>
        </div>
        <div class="student-info-item">
          <span class="info-label">Time:</span>
          <span><?= $timeMin ?? '--' ?> Minutes</span>
        </div>
        <div class="student-info-item">
          <span class="info-label">Marks:</span>
          <span>__________</span>
        </div>
      </div>

      <!-- QUESTIONS GROUPED BY TYPE -->
      <?php 
      $typeLabels = [
        'mcq'          => 'Multiple Choice Questions',
        'mcq_single'   => 'Single Choice Questions',
        'mcq_multi'    => 'Multiple Choice Questions',
        'true_false'   => 'True/False Questions',
        'tf'           => 'True/False Questions',
        'short'        => 'Short Answer Questions',
        'short_answer' => 'Short Answer Questions',
        'fill'         => 'Fill in the Blanks',
        'fill_blank'   => 'Fill in the Blanks',
        'match'        => 'Match the Columns',
      ];

      $questionCount = 1;
      ?>

      <?php foreach ($questionsByType as $type => $questions): ?>
        <?php 
          $typeLabel = $typeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type));
        ?>

        <div class="question-type-group">
          <div class="type-heading"><?= esc($typeLabel) ?></div>

          <div class="q-grid">
           <?php foreach ($questions as $q): ?>
  <?php
    $typeText = $q->type_label ?? $typeLabel;

    // Format question text
    $raw = (string) ($q->question ?? '');
    $raw = preg_replace('/\s+/', ' ', $raw);
    $raw = trim($raw);

    $before = $raw;
    $after  = '';
    if (strpos($raw, ':') !== false) {
        [$before, $after] = explode(':', $raw, 2);
        $before = rtrim($before) . ':';
        $after  = ltrim($after);
    }

    $after = trim($after, " \t\n\r\0\x0B'‘’\"");

    $rebuilt = $before;
    if ($after !== '') {
        $rebuilt .= "\n" . $after;
    }

    $textEsc = esc($rebuilt);
    $textEsc = preg_replace(
        '/_{3,}/',
        '<span class="blank-inline"></span>',
        $textEsc
    );

    $textHtml = nl2br($textEsc, false);

    // Match pairs
    $pairs = [];
    if ($type === 'match' && !empty($q->options_json)) {
        $tmp = json_decode($q->options_json, true);
        if (is_array($tmp)) {
            $pairs = $tmp;
        }
    }
  ?>

  <div class="q-card">
    <!-- NUMBER + QUESTION TEXT on same row -->
    <div class="q-top-row">
      <div class="q-no-text">Q<?= $questionCount++; ?>.</div>
      <div class="q-text"><?= $textHtml ?></div>
    </div>

    <!-- OPTIONS / INPUT AREA BY TYPE -->
    <?php if (in_array($type, ['mcq','mcq_single','mcq_multi'], true) && !empty($q->print_options)): ?>

      <?php
        $maxLen = 0;
        foreach ($q->print_options as $optLenCheck) {
            $txt = (string)($optLenCheck['text'] ?? '');
            $len = function_exists('mb_strlen')
                ? mb_strlen($txt, 'UTF-8')
                : strlen($txt);
            if ($len > $maxLen) {
                $maxLen = $len;
            }
        }

        if ($maxLen > 40) {
            $optCols = 1;
        } elseif ($maxLen > 20) {
            $optCols = 2;
        } else {
            $optCols = 4;
        }
      ?>

      <div class="q-options q-options-cols-<?= $optCols ?>">
        <?php foreach ($q->print_options as $opt): ?>
          <div class="q-opt">
            <span class="q-opt-label"><?= esc($opt['label']) ?>)</span>
            <span class="q-opt-text"><?= esc($opt['text']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>

    <?php elseif (in_array($type, ['true_false','tf'], true)): ?>

      <div class="q-options q-options-cols-2">
        <div class="q-opt">
          <span class="q-opt-label">A)</span>
          <span class="q-opt-text">True</span>
        </div>
        <div class="q-opt">
          <span class="q-opt-label">B)</span>
          <span class="q-opt-text">False</span>
        </div>
      </div>

    <?php elseif (in_array($type, ['short','short_answer'], true)): ?>

      <div class="short-lines">
        <span class="short-line"></span>
        <span class="short-line"></span>
        <span class="short-line"></span>
      </div>

    <?php elseif (in_array($type, ['fill','fill_blank'], true)): ?>
      <!-- blanks already inline via blank-inline -->

    <?php elseif ($type === 'match'): ?>

      <div class="match-table">
        <div class="match-header">
          <div>Column A</div>
          <div>Column B</div>
        </div>
        <?php if (!empty($pairs)): ?>
          <?php foreach ($pairs as $p): ?>
            <?php
              $left  = $p['left']  ?? '';
              $right = $p['right'] ?? '';
              if ($left === '' && $right === '') continue;
            ?>
            <div class="match-row">
              <div><?= esc($left) ?></div>
              <div><?= esc($right) ?></div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="match-row">
            <div>&nbsp;</div>
            <div>&nbsp;</div>
          </div>
        <?php endif; ?>
      </div>

    <?php endif; ?>
  </div>
<?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <!-- SIGNATURE SECTION -->
      <div class="signature-section">
        <div class="signature-box">
          <div class="signature-line"></div>
          <div class="signature-label">Student Signature</div>
        </div>
        <div class="signature-box">
          <div class="signature-line"></div>
          <div class="signature-label">Teacher Signature</div>
        </div>
        <div class="signature-box">
          <div class="signature-line"></div>
          <div class="signature-label">Principal Signature</div>
        </div>
      </div>

    </div> <!-- /.exam-page -->
  <?php endforeach; ?>

</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const printBtn = document.getElementById('printBtn');
  if (printBtn) {
    printBtn.addEventListener('click', function() {
      window.print();
    });
  }
});
</script>

<?= $this->endSection() ?>