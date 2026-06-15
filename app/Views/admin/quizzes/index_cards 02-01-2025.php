<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <h1 class="mb-0">Quizzes</h1>
    <a href="<?= base_url('admin/quizzes/create') ?>" class="btn btn-primary btn-sm">+ Create Quiz</a>
  </div>
</section>

<section class="content">

  <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success mt-3"><?= esc(session()->getFlashdata('msg')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mt-3"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <style>
    /* ===== Filters row ===== */
    .filters-bar{
      display:grid;
      grid-template-columns: repeat(4, minmax(0,1fr));
      gap:.75rem;
      margin: 1rem 0 1.25rem;
    }
    @media (max-width: 992px){ .filters-bar{ grid-template-columns: repeat(2, minmax(0,1fr)); } }
    @media (max-width: 576px){ .filters-bar{ grid-template-columns: 1fr; } }

    /* ===== Pin bar ===== */
    .pin-bar{
      display:flex;
      gap:.5rem;
      align-items:center;
      margin: .75rem 0 .25rem;
      flex-wrap: wrap;
    }
    .pin-indicator{
      font-size:.8rem;
      padding:.25rem .6rem;
      border-radius:999px;
      background:#eef1f4;
      border:1px solid #e6eaee;
      user-select:none;
      cursor:pointer;
      display:inline-flex;
      align-items:center;
      gap:.35rem;
      transition: all 0.2s;
    }
    .pin-indicator:hover{
      background:#e2e7eb;
    }
    .pin-indicator.active{
      background:#28a745;
      color:#fff;
      border-color:#28a745;
    }
    .pin-hints{
      margin-left:auto;
      color:#6c757d;
      font-size:.82rem;
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }
    kbd{
      padding:.1rem .35rem;
      font-size:.78rem;
      border-radius:.25rem;
      border:1px solid #dfe3e8;
      background:#fff;
      font-family: monospace;
    }

    /* ===== Class cards ===== */
    .class-grid{
      display:grid;
      grid-template-columns: 1fr;
      gap:1rem;
    }

    .class-card{
      border:1px solid #e9ecef;
      border-radius: .9rem;
      background:#fff;
      box-shadow: 0 2px 10px rgba(0,0,0,.04);
      overflow:hidden;
    }

    .class-head{
      padding: .85rem 1rem;
      background: linear-gradient(180deg, #fafbfc, #ffffff);
      border-bottom:1px solid #eef1f4;
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: .75rem;
      flex-wrap: wrap;
    }

    .class-title{
      margin:0;
      font-weight:700;
      font-size:1.1rem;
      line-height:1.35;
      display:flex;
      align-items:center;
      gap:.5rem;
    }

    .class-metrics{
      display:flex;
      gap:.5rem;
      flex-wrap: wrap;
      align-items:center;
      justify-content:flex-end;
    }

    .metric-pill{
      display:inline-flex;
      align-items:center;
      gap:.35rem;
      padding:.25rem .6rem;
      border-radius: 999px;
      background:#f6f7f9;
      border:1px solid #eef1f4;
      font-size:.82rem;
      white-space: nowrap;
    }

    /* ===== Nested quiz cards grid inside each class ===== */
    .nested-quiz-grid{
      padding: 1rem;
      display:grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap:1rem;
    }
    @media (max-width: 1400px){ .nested-quiz-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 768px){ .nested-quiz-grid{ grid-template-columns: 1fr; } }

    /* ===== Quiz Card ===== */
    .quiz-card{
      border:1px solid #e9ecef;
      border-radius:.85rem;
      box-shadow: 0 2px 8px rgba(0,0,0,.04);
      display:flex;
      flex-direction:column;
      overflow:hidden;
      background:#fff;
      height: 100%;
      transition: all 0.2s;
    }
    .quiz-card:hover{
      box-shadow: 0 4px 12px rgba(0,0,0,.08);
      transform: translateY(-1px);
    }

    .quiz-card .card-head{
      padding:.7rem .9rem;
      background:#fbfcfe;
      border-bottom:1px solid #eef1f4;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:.6rem;
    }

    .subj-title{
      margin:0;
      font-weight:700;
      font-size:.95rem;
      line-height:1.2;
      display:flex;
      align-items:center;
      gap:.45rem;
    }

    .quiz-badges .badge{ margin-left:.35rem; }

    /* ===== Compact quiz card body ===== */
    .quiz-card .card-body{
      padding:.7rem .85rem;
      display:flex;
      flex-direction: column;
      gap:.5rem;
      flex: 1;
    }

    .quiz-title{
      font-weight:700;
      font-size:1rem;
      margin:0;
      line-height:1.3;
      color:#2d3748;
    }

    .muted{ color:#6c757d; }

    .qmeta{
      display:flex;
      flex-wrap:wrap;
      gap:.4rem;
      align-items:center;
    }
    .qpill{
      display:inline-flex;
      align-items:center;
      gap:.4rem;
      padding:.22rem .55rem;
      border-radius:999px;
      background:#f6f7f9;
      border:1px solid #eef1f4;
      font-size:.78rem;
      line-height:1;
      white-space:nowrap;
      transition: all 0.2s;
    }
    .qpill:hover{
      background:#e9ecef;
    }
    .qpill i{ opacity:.75; }
    .qpill .val{ font-weight:700; }

    .qsection-title{
      font-size:.78rem;
      letter-spacing:.02em;
      text-transform:uppercase;
      color:#6c757d;
      margin:.15rem 0 -.15rem;
      font-weight:600;
    }

    /* ===== Question type summary (compact, non-zero only) ===== */
    .qtype-summary{
      display:flex;
      flex-wrap:wrap;
      gap:.4rem;
      margin-top:.1rem;
    }
    .qtype-item{
      display:inline-flex;
      align-items:center;
      gap:.3rem;
      padding:.15rem .5rem;
      border-radius:6px;
      background:#f1f3f5;
      font-size:.75rem;
      border:1px solid rgba(0,0,0,.04);
      white-space:nowrap;
    }
    .qtype-item i{ opacity:.65; font-size:.7rem; }
    .qtype-count{ font-weight:700; color:#2d3748; }

    /* ===== Date-time row ===== */
    .datetime-row{
      display:flex;
      flex-wrap:wrap;
      gap:.4rem;
      align-items:center;
      font-size:.82rem;
      color:#4a5568;
      margin:.1rem 0;
    }
    .datetime-item{
      display:inline-flex;
      align-items:center;
      gap:.3rem;
    }

    /* ===== Student & attempts row ===== */
    .stats-row{
      display:flex;
      flex-wrap:wrap;
      gap:.6rem;
      align-items:center;
      font-size:.82rem;
      margin:.1rem 0;
    }
    .stat-item{
      display:inline-flex;
      align-items:center;
      gap:.3rem;
    }

    /* ===== Attempt progress (2 per row) ===== */
    .attempts-progress{
      display:grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap:.6rem;
      margin-top:.2rem;
    }
    @media (max-width: 1200px){ .attempts-progress{ grid-template-columns: 1fr; } }
    .attempt-item{
      display:flex;
      flex-direction:column;
      gap:.2rem;
    }
    .attempt-header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      font-size:.75rem;
    }
    .attempt-label{
      font-weight:600;
      color:#4a5568;
    }
    .attempt-count{
      font-weight:700;
      color:#2d3748;
    }
    .attempt-bar{
      height:6px;
      background:#eef1f4;
      border-radius:999px;
      overflow:hidden;
    }
    .attempt-fill{
      height:100%;
      background:#28a745;
      border-radius:999px;
    }

    .card-foot{
      padding:.75rem .9rem;
      border-top:1px solid #eef1f4;
      display:flex;
      gap:.5rem;
      justify-content:flex-end;
      background:#fff;
      flex-wrap: wrap;
    }

    .hidden{ display:none !important; }

    /* ===== Difficulty badges ===== */
    .diff-easy{ background:#e8f7ee; color:#1e7e34; border:1px solid #b6e2c6; }
    .diff-medium{ background:#fff3cd; color:#856404; border:1px solid #ffeeba; }
    .diff-hard{ background:#fdecea; color:#a71d2a; border:1px solid #f5c6cb; }

    /* ===== Keyboard navigation focus ===== */
    .quiz-card.kb-active{
      outline:3px solid #007bff;
      outline-offset:2px;
      box-shadow:0 0 0 3px rgba(0,123,255,.15);
      position: relative;
      z-index: 1;
    }

    /* ===== Floating help ===== */
    .help-btn{
      position:fixed;
      bottom:18px;
      right:18px;
      z-index:1050;
      border-radius:50%;
      width:42px;
      height:42px;
      font-size:20px;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:0;
      box-shadow: 0 2px 10px rgba(0,0,0,.15);
    }

    .report-btn {
      display: inline-flex;
      align-items: center;
      padding: 6px 16px;
      border-radius: 6px;
      font-weight: 500;
      font-size: 14px;
      transition: all 0.2s;
      white-space: nowrap;
    }

    .report-btn:hover {
      background: #3b82f6;
      color: white;
      transform: translateY(-1px);
      box-shadow: 0 2px 5px rgba(59, 130, 246, 0.3);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .class-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }
      
      .class-head .d-flex {
        width: 100%;
        justify-content: space-between;
        flex-wrap: wrap;
      }
      
      .class-metrics {
        order: 2;
        margin-top: 10px;
        width: 100%;
      }
      
      .report-btn {
        order: 1;
      }
      
      .pin-hints {
        margin-left: 0;
        margin-top: .5rem;
        width: 100%;
      }
    }

    /* Action buttons */
    .btn-sm {
      padding: .25rem .5rem;
      font-size: .75rem;
    }
  </style>

  <?php
    // ---------- Helper Functions ----------
    $nowTs = time();

    // Convert datetime to timestamp
    $toTs = function($dt){
      if (!$dt) return null;
      $ts = strtotime((string)$dt);
      return $ts ?: null;
    };

    // Check if quiz is currently open
    $isOpenQuiz = function($q) use ($toTs, $nowTs){
      $s = $toTs($q->start_at ?? null);
      $e = $toTs($q->end_at ?? null);

      if (!$s && !$e) return true;

      if (($q->start_at ?? null) && ($q->end_at ?? null) && (string)$q->start_at === (string)$q->end_at) {
        return true;
      }

      if (!$s && $e) return $e >= $nowTs;
      if ($s && !$e) return $s <= $nowTs;

      return ($s <= $nowTs && $nowTs <= $e);
    };

    // Get integer value from multiple possible fields
    $getInt = function($q, array $fields, $default = 0){
      foreach ($fields as $f) {
        if (isset($q->$f) && $q->$f !== '' && $q->$f !== null) return (int)$q->$f;
      }
      return (int)$default;
    };

    // ---------- Build filter options ----------
    $clsOptions  = [];
    $subjOptions = [];
    $termOptions = [];

    if (!empty($quizzes)) {
      foreach ($quizzes as $q) {
        $clsId  = (int)($q->cls_sec_id ?? 0);
        $clsLbl = trim($q->cls_sec_name ?? ('Class-Section #'.$clsId));
        if ($clsId) $clsOptions[$clsId] = $clsLbl;

        $subLbl = trim($q->sec_sub_name ?? 'Subject');
        if ($subLbl !== '') $subjOptions[$subLbl] = $subLbl;

        $tsid = (int)($q->term_session_id ?? 0);
        $tsName = trim($q->term_session_name ?? 'Term Session #'.$tsid);
        if ($tsid) $termOptions[$tsid] = $tsName;
      }
    }
    ksort($clsOptions);
    ksort($subjOptions);
    ksort($termOptions);

    // ---------- Group quizzes by class-section ----------
    $byClass = [];
    if (!empty($quizzes)) {
      foreach ($quizzes as $q) {
        $clsId   = (int)($q->cls_sec_id ?? 0);
        $clsName = trim($q->cls_sec_name ?? ('Class-Section #'.$clsId));
        if (!isset($byClass[$clsId])) {
          $byClass[$clsId] = [
            'cls_id'   => $clsId,
            'cls_name' => $clsName,
            'quizzes'  => [],
          ];
        }
        $byClass[$clsId]['quizzes'][] = $q;
      }
    }
    ksort($byClass);
  ?>

  <!-- ===== Pin / shortcuts bar ===== -->
  <div class="pin-bar">
    <span class="pin-indicator" id="openOnlyIndicator" data-bs-toggle="tooltip" title="Toggle only open quizzes (Shortcut: O)">
      <i class="fas fa-bolt"></i> Open Only <span class="ms-1">(O)</span>
    </span>

    <div class="pin-hints">
      <span class="me-2"><kbd>/</kbd> Search</span>
      <span class="me-2"><kbd>←</kbd><kbd>→</kbd> Navigate</span>
      <span class="me-2"><kbd>Enter</kbd> Edit</span>
      <span class="me-2"><kbd>R</kbd> Results</span>
      <span class="me-2"><kbd>P</kbd> Print</span>
      <span class="me-2"><kbd>?</kbd> Help</span>
      <span><kbd>Esc</kbd> Reset</span>
    </div>
  </div>

  <!-- ===== Filters ===== -->
  <div class="filters-bar">
    <div>
      <label class="small text-muted d-block mb-1">Class - Section</label>
      <select id="fClsSec" class="form-control form-control-sm">
        <option value="">All</option>
        <?php foreach ($clsOptions as $id => $lbl): ?>
          <option value="<?= (int)$id ?>"><?= esc($lbl) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label class="small text-muted d-block mb-1">Section Subject</label>
      <select id="fSubject" class="form-control form-control-sm">
        <option value="">All</option>
        <?php foreach ($subjOptions as $lbl): ?>
          <option value="<?= esc($lbl) ?>"><?= esc($lbl) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label class="small text-muted d-block mb-1">Term (Session)</label>
      <select id="fTerm" class="form-control form-control-sm">
        <option value="">All</option>
        <?php foreach ($termOptions as $tsid => $lbl): ?>
          <option value="<?= (int)$tsid ?>"><?= esc($lbl) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="d-flex align-items-end">
      <div class="w-100 d-flex gap-2">
        <input id="fSearch" type="text" class="form-control form-control-sm" placeholder="Search title/instructions...">
        <button id="fReset" class="btn btn-light btn-sm ms-2" type="button">Reset</button>
      </div>
    </div>
  </div>

  <!-- ===== Nested Class Cards ===== -->
  <div id="classGrid" class="class-grid">
    <?php if (!empty($byClass)): ?>
      <?php foreach ($byClass as $clsId => $bucket): ?>
        <?php
          $clsName = $bucket['cls_name'];
          $total = count($bucket['quizzes']);
          $open = 0; $closed = 0;
          foreach ($bucket['quizzes'] as $qTmp) {
            if ($isOpenQuiz($qTmp)) $open++; else $closed++;
          }
        ?>

        <div class="class-card class-wrap"
             data-class-id="<?= (int)$clsId ?>"
             id="classWrap<?= (int)$clsId ?>">

          <div class="class-head">
            <h3 class="class-title">
              <i class="fas fa-layer-group text-muted"></i>
              <?= esc($clsName) ?>
            </h3>

            <div class="d-flex align-items-center gap-3">
              <div class="class-metrics">
                <span class="metric-pill" data-bs-toggle="tooltip" title="Total quizzes in this class-section">
                  <span class="text-muted">Total:</span> <strong><?= (int)$total ?></strong>
                </span>
                <span class="metric-pill" data-bs-toggle="tooltip" title="Open quizzes in this class-section">
                  <span class="text-muted">Open:</span> <strong><?= (int)$open ?></strong>
                </span>
                <span class="metric-pill" data-bs-toggle="tooltip" title="Closed quizzes in this class-section">
                  <span class="text-muted">Closed:</span> <strong><?= (int)$closed ?></strong>
                </span>
              </div>
              
              <a href="<?= base_url('admin/quizzes/class-results/' . (int)$clsId) ?>" 
                 class="btn btn-outline-primary btn-sm report-btn"
                 target="_blank">
                <i class="fas fa-chart-bar me-1"></i> View Report
              </a>
            </div>
          </div>

          <div class="nested-quiz-grid">
          <?php foreach ($bucket['quizzes'] as $q):
  $qid      = (int)$q->quiz_id;
  $title    = (string)($q->title ?? 'Untitled Quiz');
  $subName  = trim($q->sec_sub_name ?? 'Subject');
  $tsid     = (int)($q->term_session_id ?? 0);
  $tsName   = trim($q->term_session_name ?? 'Term Session #'.$tsid);
  $secs     = (int)($q->time_limit_sec ?? 0);
  $mins     = $secs > 0 ? (int)ceil($secs / 60) : null;
  $startAt  = $q->start_at ?? null;
  $endAt    = $q->end_at ?? null;
  $pub      = (int)($q->is_published ?? 0) === 1;

  $sameWindow = ($startAt && $endAt && (string)$startAt === (string)$endAt);
  $showWindow = (!$sameWindow) && ($startAt || $endAt);

  // Topics
  $topicsCsv = (string)($q->topic_names ?? '');
  $topics    = $topicsCsv !== '' ? array_filter(array_map('trim', explode(',', $topicsCsv))) : [];

  // ✅ ACTUAL question counts from qb_questions (not planned!)
  $mcqSingle = (int)($q->actual_mcq ?? 0);
  $mcqMulti  = (int)($q->actual_mcq_multi ?? 0);
  $tf        = (int)($q->actual_tf ?? 0);
  $fib       = (int)($q->actual_fill ?? 0);
  $match     = (int)($q->actual_match ?? 0);
  $shortQ    = (int)($q->actual_short ?? 0);

  $actualTotal = $mcqSingle + $mcqMulti + $tf + $fib + $match + $shortQ;

  // ✅ Planned counts from quizzes table (for difficulty/comparison)
  $planMcqSingle = (int)($q->plan_mcq_single ?? 0);
  $planMcqMulti  = (int)($q->plan_mcq_multi ?? 0);
  $planTf        = (int)($q->plan_tf ?? 0);
  $planFib       = (int)($q->plan_fill ?? 0);
  $planMatch     = (int)($q->plan_match ?? 0);
  $planShort     = (int)($q->plan_short ?? 0);
  $plannedTotal  = $planMcqSingle + $planMcqMulti + $planTf + $planFib + $planMatch + $planShort;

  // Attempts / Students
  $attemptsAllowed = $getInt($q, ['max_attempts'], 1);
  $classStudents   = $getInt($q, ['class_student_count','students_count','student_count'], 0);
  
  // Get attempt counts per attempt (1 through max attempts) - from new query
  $attemptCounts = [];
  for ($i = 1; $i <= 10; $i++) {
    $attemptField = 'attempt_' . $i . '_count';
    $attemptCounts[$i] = $getInt($q, [$attemptField], 0);
  }
  
  $attemptedStd    = $getInt($q, ['attempted_students_count','attempted_students','attempted_count'], 0);
  $unattemptedStd  = max(0, $classStudents - $attemptedStd);

  $openNow = $isOpenQuiz($q);

  // ✅ Difficulty based on ACTUAL total (not planned)
  if ($actualTotal <= 10)      $diff = ['Easy','diff-easy','Difficulty: Easy (≤ 10 questions)'];
  elseif ($actualTotal <= 25)  $diff = ['Medium','diff-medium','Difficulty: Medium (11–25 questions)'];
  else                         $diff = ['Hard','diff-hard','Difficulty: Hard (≥ 26 questions)'];

  // Overall attempt percentage
  $pct = 0;
  if ($classStudents > 0) {
    $pct = (int) round(($attemptedStd / $classStudents) * 100);
    if ($pct < 0) $pct = 0;
    if ($pct > 100) $pct = 100;
  }

  // ✅ Build question type summary from ACTUAL counts (only non-zero types)
  $qtypeItems = [];
  if ($mcqSingle > 0) $qtypeItems[] = ['icon' => 'far fa-check-square', 'label' => 'MCQ', 'count' => $mcqSingle];
  if ($mcqMulti > 0)  $qtypeItems[] = ['icon' => 'fas fa-tasks', 'label' => 'MCQ Multi', 'count' => $mcqMulti];
  if ($tf > 0)        $qtypeItems[] = ['icon' => 'fas fa-toggle-on', 'label' => 'T/F', 'count' => $tf];
  if ($fib > 0)       $qtypeItems[] = ['icon' => 'fas fa-pen', 'label' => 'Fill', 'count' => $fib];
  if ($match > 0)     $qtypeItems[] = ['icon' => 'fas fa-random', 'label' => 'Match', 'count' => $match];
  if ($shortQ > 0)    $qtypeItems[] = ['icon' => 'fas fa-align-left', 'label' => 'Short', 'count' => $shortQ];
?>
              <div class="quiz-card"
                   data-quiz-id="<?= $qid ?>"
                   data-cls-sec-id="<?= (int)$clsId ?>"
                   data-sec-sub-name="<?= esc($subName) ?>"
                   data-sec-sub-id="<?= (int)($q->sec_sub_id ?? 0) ?>"
                   data-term-session-id="<?= (int)$tsid ?>"
                   data-title="<?= esc(strtolower($title)) ?>"
                   data-instructions="<?= esc(strtolower((string)($q->instructions ?? ''))) ?>"
                   data-open="<?= $openNow ? 1 : 0 ?>"
              >

                <div class="card-head">
                  <h4 class="subj-title">
                    <i class="fas fa-book text-muted"></i>
                    <?= esc($subName) ?>
                  </h4>

                  <div class="quiz-badges">
                    <?php if ($openNow): ?>
                      <span class="badge text-bg-success" data-bs-toggle="tooltip" title="Quiz is currently open">Open</span>
                    <?php else: ?>
                      <span class="badge text-bg-danger" data-bs-toggle="tooltip" title="Quiz is closed">Closed</span>
                    <?php endif; ?>

                    <span class="badge <?= $diff[1] ?>" data-bs-toggle="tooltip" title="<?= esc($diff[2]) ?>">
                      <?= esc($diff[0]) ?>
                    </span>

                    <?php if ($pub): ?>
                      <span class="badge text-bg-primary" data-bs-toggle="tooltip" title="Published and visible to students">Published</span>
                    <?php else: ?>
                      <span class="badge text-bg-secondary" data-bs-toggle="tooltip" title="Draft (not visible to students)">Draft</span>
                    <?php endif; ?>

                    <?php if ($mins): ?>
                      <span class="badge text-bg-info" data-bs-toggle="tooltip" title="Time limit"><?= (int)$mins ?> min</span>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="card-body">
                  <!-- Row 1: Quiz Title + Questions Count -->
                 <!-- Row 1: Quiz Title + Questions Count -->
<div class="d-flex justify-content-between align-items-start mb-1">
  <h3 class="quiz-title"><?= esc($title) ?></h3>
  <span class="qpill"
        data-bs-toggle="tooltip"
        title="Actual questions: <?= (int)$actualTotal ?> | Planned questions: <?= (int)$plannedTotal ?>">
    <i class="fas fa-list-ol"></i>
    <span class="val"><?= (int)$actualTotal ?></span>
    <span class="muted">Qs</span>
  </span>
</div>

                  <!-- Row 2: Start/End Date Time + Student Count + Max Attempts -->
                  <div class="datetime-row">
                    <?php if ($showWindow): ?>
                      <span class="datetime-item" data-bs-toggle="tooltip" title="Start Date">
                        <i class="far fa-calendar-alt"></i>
                        <?= $startAt ? date('M d, Y', strtotime($startAt)) : '—' ?>
                      </span>
                      <span class="muted">→</span>
                      <span class="datetime-item" data-bs-toggle="tooltip" title="End Date">
                        <i class="far fa-calendar-alt"></i>
                        <?= $endAt ? date('M d, Y', strtotime($endAt)) : '—' ?>
                      </span>
                    <?php else: ?>
                      <span class="datetime-item" data-bs-toggle="tooltip" title="Always open">
                        <i class="fas fa-infinity"></i>
                        Always Open
                      </span>
                    <?php endif; ?>
                  </div>

                  <div class="stats-row">
                    <span class="stat-item" data-bs-toggle="tooltip" title="Students in this class">
                      <i class="fas fa-user-graduate"></i>
                      <strong><?= (int)$classStudents ?></strong> Students
                    </span>
                    <span class="stat-item" data-bs-toggle="tooltip" title="Max attempts allowed per student">
                      <i class="fas fa-redo"></i>
                      <strong><?= (int)$attemptsAllowed ?></strong> Max Attempts
                    </span>
                  </div>

                  <!-- Row 3: Question Type Summary (non-zero only) -->
                  <?php if (!empty($qtypeItems)): ?>
                    <div class="qsection-title">Question Summary</div>
                    <div class="qtype-summary">
                      <?php foreach ($qtypeItems as $item): ?>
                        <span class="qtype-item" data-bs-toggle="tooltip" title="<?= esc($item['label']) ?> questions">
                          <i class="<?= $item['icon'] ?>"></i>
                          <span class="qtype-count"><?= $item['count'] ?></span>
                          <span class="muted"><?= esc($item['label']) ?></span>
                        </span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                  <!-- Row 4+: Attempt Progress (2 per row) -->
                  <?php if ($attemptsAllowed > 0 && $classStudents > 0): ?>
                    <div class="qsection-title">Attempt Progress</div>
                    <div class="attempts-progress">
                      <?php for ($i = 1; $i <= $attemptsAllowed; $i++): ?>
                        <?php
                          $attemptPct = $classStudents > 0 ? round(($attemptCounts[$i] / $classStudents) * 100) : 0;
                          if ($attemptPct > 100) $attemptPct = 100;
                        ?>
                        <div class="attempt-item">
                          <div class="attempt-header">
                            <span class="attempt-label">Attempt <?= $i ?></span>
                            <span class="attempt-count"><?= $attemptCounts[$i] ?>/<?= $classStudents ?></span>
                          </div>
                          <div class="attempt-bar" data-bs-toggle="tooltip" title="<?= $attemptPct ?>% of students completed attempt <?= $i ?>">
                            <div class="attempt-fill" style="width: <?= $attemptPct ?>%"></div>
                          </div>
                        </div>
                      <?php endfor; ?>
                    </div>
                  <?php endif; ?>

                  <!-- Term Session Name -->
                  <div class="text-muted small mt-2" data-bs-toggle="tooltip" title="Term session">
                    <i class="fas fa-calendar-alt"></i> <?= esc($tsName) ?>
                  </div>

                  <!-- Topics (if any) -->
                  <?php if (!empty($topics)): ?>
                    <div class="mt-2">
                      <div class="qsection-title">Topics</div>
                      <div class="qtype-summary">
                        <?php foreach ($topics as $topic): ?>
                          <span class="qtype-item" data-bs-toggle="tooltip" title="Topic: <?= esc($topic) ?>">
                            <i class="fas fa-tag"></i>
                            <?= esc($topic) ?>
                          </span>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Action Buttons -->
                <div class="card-foot">
                  <a class="btn btn-outline-secondary btn-sm btn-results"
                     href="<?= site_url('admin/quizzes/'.$qid.'/results') ?>"
                     data-bs-toggle="tooltip" title="View results (R)">
                    Results
                  </a>

                  <a class="btn btn-outline-info btn-sm btn-print"
                     href="<?= site_url('admin/quizzes/print/'.$qid) ?>"
                     target="_blank"
                     data-bs-toggle="tooltip" title="Print single version (P)">
                    Print (Single)
                  </a>

                   <a class="btn btn-outline-info btn-sm btn-print"
                     href="<?= site_url('admin/quizzes/print-all/'.$qid) ?>"
                     target="_blank">
                    Print (All)
                  </a>

                  <a class="btn btn-outline-info btn-sm btn-print-all"
                     href="<?= site_url('admin/quizzes/print-versions/'.$qid) ?>"
                     target="_blank"
                     data-bs-toggle="tooltip" title="Print all versions">
                    Print All Versions
                  </a>

                  <a class="btn btn-outline-primary btn-sm btn-edit"
                     href="<?= site_url('admin/quizzes/edit/'.$qid) ?>"
                     data-bs-toggle="tooltip" title="Edit quiz (Enter)">
                    Edit
                  </a>
                </div>

              </div>
            <?php endforeach; ?>
          </div>

        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="alert alert-light text-center py-4">
        <i class="fas fa-clipboard-list fa-2x text-muted mb-3"></i>
        <h5 class="mb-2">No quizzes found</h5>
        <p class="text-muted">Create your first quiz to get started</p>
        <a href="<?= base_url('admin/quizzes/create') ?>" class="btn btn-primary mt-2">
          <i class="fas fa-plus me-1"></i> Create Quiz
        </a>
      </div>
    <?php endif; ?>
  </div>

  <!-- ===== Icon legend modal (Help) ===== -->
  <div class="modal fade" id="iconLegendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Keyboard & Icons</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body small">
          <div class="mb-2">
            <div><b>Shortcuts</b></div>
            <div><kbd>/</kbd> Search</div>
            <div><kbd>←</kbd>/<kbd>→</kbd> Navigate cards</div>
            <div><kbd>Enter</kbd> Edit</div>
            <div><kbd>R</kbd> Results</div>
            <div><kbd>P</kbd> Print</div>
            <div><kbd>O</kbd> Open only</div>
            <div><kbd>Esc</kbd> Reset</div>
          </div>
          <hr class="my-2">
          <div>
            <div><b>Icons</b></div>
            <div><i class="fas fa-list-ol"></i> Questions (planned, tooltip shows actual)</div>
            <div><i class="fas fa-redo"></i> Attempts</div>
            <div><i class="fas fa-user-graduate"></i> Students</div>
            <div><i class="far fa-check-square"></i> MCQ</div>
            <div><i class="fas fa-tasks"></i> MCQ Multi</div>
            <div><i class="fas fa-pen"></i> Fill</div>
            <div><i class="fas fa-toggle-on"></i> T/F</div>
            <div><i class="fas fa-random"></i> Match</div>
            <div><i class="fas fa-align-left"></i> Short</div>
            <div><i class="fas fa-tag"></i> Topics</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Floating help button -->
  <button class="btn btn-primary help-btn"
          data-bs-toggle="tooltip"
          title="Help & shortcuts (?)"
          type="button"
          onclick="$('#iconLegendModal').modal('show')">
    ?
  </button>

</section>

<script>
(function(){
  const $classGrid = document.getElementById('classGrid');
  const $fClsSec  = document.getElementById('fClsSec');
  const $fSubject = document.getElementById('fSubject');
  const $fTerm    = document.getElementById('fTerm');
  const $fSearch  = document.getElementById('fSearch');
  const $fReset   = document.getElementById('fReset');
  const $openOnlyIndicator = document.getElementById('openOnlyIndicator');

  function normalize(s){ return (s || '').toString().trim().toLowerCase(); }

  function applyFilters(){
    const clsVal  = $fClsSec.value;
    const subVal  = $fSubject.value;
    const termVal = $fTerm.value;
    const q = normalize($fSearch.value);

    const quizCards  = $classGrid.querySelectorAll('.quiz-card');
    const classWraps = $classGrid.querySelectorAll('.class-wrap');

    quizCards.forEach(card => {
      const cCls   = card.getAttribute('data-cls-sec-id');
      const cSubN  = normalize(card.getAttribute('data-sec-sub-name'));
      const cTerm  = card.getAttribute('data-term-session-id');
      const cTitle = card.getAttribute('data-title');
      const cInstr = card.getAttribute('data-instructions');

      let ok = true;
      if (clsVal && cCls !== clsVal) ok = false;
      if (ok && subVal && cSubN !== normalize(subVal)) ok = false;
      if (ok && termVal && cTerm !== termVal) ok = false;

      if (ok && q){
        ok = (cTitle && cTitle.indexOf(q) !== -1) || (cInstr && cInstr.indexOf(q) !== -1);
      }

      card.classList.toggle('hidden', !ok);
    });

    classWraps.forEach(wrap => {
      const visibleInside = wrap.querySelectorAll('.quiz-card:not(.hidden)').length;
      wrap.classList.toggle('hidden', visibleInside === 0);
    });
  }

  [$fClsSec, $fSubject, $fTerm].forEach(sel => sel && sel.addEventListener('change', applyFilters));
  $fSearch && $fSearch.addEventListener('input', applyFilters);
  $fReset && $fReset.addEventListener('click', function(){
    if ($fClsSec)  $fClsSec.value = '';
    if ($fSubject) $fSubject.value = '';
    if ($fTerm)    $fTerm.value = '';
    if ($fSearch)  $fSearch.value = '';
    if ($openOnlyIndicator){
      $openOnlyIndicator.classList.remove('active');
      $openOnlyIndicator.dataset.openOnly = '0';
    }
    $classGrid.querySelectorAll('.quiz-card').forEach(c => c.classList.remove('hidden'));
    applyFilters();
    window.__kbNavRefresh && window.__kbNavRefresh();
  });

  applyFilters();

  // Open only toggle
  if ($openOnlyIndicator){
    $openOnlyIndicator.dataset.openOnly = '0';
    $openOnlyIndicator.addEventListener('click', function(){
      const on = ($openOnlyIndicator.dataset.openOnly === '1');
      toggleOpenOnly(!on);
    });
  }

  function toggleOpenOnly(enable){
    if (!$openOnlyIndicator) return;
    $openOnlyIndicator.dataset.openOnly = enable ? '1' : '0';
    $openOnlyIndicator.classList.toggle('active', enable);

    const allCards = Array.from(document.querySelectorAll('.quiz-card'));
    allCards.forEach(card => {
      const isOpen = card.getAttribute('data-open') === '1';
      if (enable && !isOpen) card.classList.add('hidden');
    });

    if (!enable) applyFilters();

    const classWraps = $classGrid.querySelectorAll('.class-wrap');
    classWraps.forEach(wrap => {
      const visibleInside = wrap.querySelectorAll('.quiz-card:not(.hidden)').length;
      wrap.classList.toggle('hidden', visibleInside === 0);
    });

    window.__kbNavRefresh && window.__kbNavRefresh();
  }

  // ===== Keyboard navigation =====
  let cards = [];
  let idx = 0;

  function refreshCards(){
    cards = Array.from(document.querySelectorAll('.quiz-card:not(.hidden)'));
    cards.forEach(c => c.classList.remove('kb-active'));
    if (!cards.length) return;
    if (idx < 0) idx = 0;
    if (idx >= cards.length) idx = cards.length - 1;
    const cur = cards[idx];
    cur.classList.add('kb-active');
    cur.scrollIntoView({behavior:'smooth', block:'nearest', inline:'nearest'});
  }
  window.__kbNavRefresh = refreshCards;

  setTimeout(refreshCards, 400);

  document.addEventListener('keydown', function(e){
    const tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
    if (tag === 'input' || tag === 'textarea' || tag === 'select') return;

    if (e.key === '/') {
      e.preventDefault();
      $fSearch && $fSearch.focus();
      return;
    }

    if (e.key === '?') {
      e.preventDefault();
      $('#iconLegendModal').modal('show');
      return;
    }

    if (e.key === 'Escape') {
      e.preventDefault();
      $fReset && $fReset.click();
      return;
    }

    if (e.key.toLowerCase() === 'o') {
      e.preventDefault();
      const on = ($openOnlyIndicator && $openOnlyIndicator.dataset.openOnly === '1');
      toggleOpenOnly(!on);
      return;
    }

    if (!cards.length) refreshCards();
    if (!cards.length) return;

    if (e.key === 'ArrowRight') {
      e.preventDefault();
      idx = Math.min(cards.length - 1, idx + 1);
      refreshCards();
      return;
    }
    if (e.key === 'ArrowLeft') {
      e.preventDefault();
      idx = Math.max(0, idx - 1);
      refreshCards();
      return;
    }

    const cur = cards[idx];

    if (e.key === 'Enter') {
      e.preventDefault();
      cur.querySelector('.btn-edit')?.click();
      return;
    }

    if (e.key.toLowerCase() === 'r') {
      e.preventDefault();
      cur.querySelector('.btn-results')?.click();
      return;
    }

    if (e.key.toLowerCase() === 'p') {
      e.preventDefault();
      cur.querySelector('.btn-print')?.click();
      return;
    }
  });

})();
</script>

<script>
$(function () {
  $('[data-bs-toggle="tooltip"]').tooltip({
    container: 'body',
    boundary: 'window',
    delay: { show: 300, hide: 100 }
  });
  
  // Initialize Bootstrap components
  $('.modal').modal({ show: false });
});
</script>

<?= $this->endSection() ?>