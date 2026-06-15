<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Quizzes',
    'icon' => 'fas fa-clipboard-check',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Quizzes', 'active' => true],
    ],
    'actionsHtml' => '<a href="' . esc(base_url('admin/quizzes/create-board-prep'), 'attr') . '" class="btn btn-success btn-sm float-sm-right ms-1"><i class="fas fa-book-reader me-1"></i> Board Prep Quizzes</a>'
        . '<a href="' . esc(base_url('admin/quizzes/create'), 'attr') . '" class="btn btn-primary btn-sm float-sm-right"><i class="fas fa-plus me-1"></i> Create Quiz</a>',
]) ?>

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
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .class-head:hover {
      background: linear-gradient(180deg, #f0f4f8, #fafbfc);
    }

    .class-head.expanded {
      background: linear-gradient(180deg, #e8f4ff, #f0f8ff);
      border-bottom: 2px solid #007bff;
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

    .class-title i.fa-chevron-down {
      transition: transform 0.3s ease;
      font-size: 0.9rem;
    }

    .class-head.expanded .class-title i.fa-chevron-down {
      transform: rotate(180deg);
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

    /* ===== Nested quiz cards grid inside each class (initially hidden) ===== */
    .nested-quiz-grid{
      padding: .75rem;
      display:grid;
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap:.65rem;
      max-height: 0;
      overflow: hidden;
      opacity: 0;
      transition: all 0.5s ease;
    }

    .nested-quiz-grid.expanded {
      max-height: 5000px;
      opacity: 1;
      overflow: visible;
    }

    @media (max-width: 1700px){ .nested-quiz-grid{ grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    @media (max-width: 1400px){ .nested-quiz-grid{ grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 1100px){ .nested-quiz-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); } }
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
      padding:.55rem .65rem;
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
      font-size:.85rem;
      line-height:1.2;
      display:flex;
      align-items:center;
      gap:.45rem;
    }

    .quiz-badges .badge{ margin-left:.35rem; }

    /* ===== Compact quiz card body ===== */
    .quiz-card .card-body{
      padding:.55rem .65rem;
      display:flex;
      flex-direction: column;
      gap:.5rem;
      flex: 1;
    }

    .quiz-title{
      font-weight:700;
      font-size:.9rem;
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
      font-size:.72rem;
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
      gap:.3rem;
      margin-top:.1rem;
    }
    .qtype-item{
      display:inline-flex;
      align-items:center;
      gap:.3rem;
      padding:.15rem .5rem;
      border-radius:6px;
      background:#f1f3f5;
      font-size:.7rem;
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
      font-size:.74rem;
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
      font-size:.74rem;
      margin:.1rem 0;
    }
    .stat-item{
      display:inline-flex;
      align-items:center;
      gap:.3rem;
    }

    /* ===== Compact attempt stats (removed detailed progress bars) ===== */
    .attempt-stats-compact {
      display: flex;
      align-items: center;
      gap: .5rem;
      font-size: .74rem;
      margin-top: .2rem;
      padding: .3rem .45rem;
      background: #f8f9fa;
      border-radius: .5rem;
      border: 1px solid #e9ecef;
    }
    .attempt-stat-item {
      display: flex;
      align-items: center;
      gap: .25rem;
    }
    .attempt-stat-label {
      color: #6c757d;
    }
    .attempt-stat-value {
      font-weight: 700;
      color: #2d3748;
    }

    /* ===== Hidden detailed attempt progress ===== */
    .attempts-progress-detailed{
      display:none;
    }

    /* ===== Modal styles for attempt progress ===== */
    .attempt-progress-modal .modal-header {
      background: linear-gradient(180deg, #fafbfc, #ffffff);
      border-bottom: 1px solid #eef1f4;
    }
    .attempt-progress-modal .modal-title {
      font-weight: 700;
      color: #2d3748;
    }
    .attempt-progress-modal .modal-body {
      padding: 1.25rem;
    }
    .progress-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: .75rem;
      padding-bottom: .5rem;
      border-bottom: 1px solid #eef1f4;
    }
    .progress-summary {
      font-size: .9rem;
      color: #4a5568;
    }
    .progress-summary strong {
      color: #2d3748;
    }
    .attempt-item-detailed {
      margin-bottom: 1rem;
      padding: .75rem;
      background: #f8f9fa;
      border-radius: .5rem;
      border: 1px solid #e9ecef;
    }
    .attempt-header-detailed {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: .5rem;
      font-size: .9rem;
    }
    .attempt-label-detailed {
      font-weight: 600;
      color: #4a5568;
    }
    .attempt-count-detailed {
      font-weight: 700;
      color: #2d3748;
    }
    .progress-bar-container {
      height: 8px;
      background: #eef1f4;
      border-radius: 999px;
      overflow: hidden;
      margin-bottom: .25rem;
    }
    .progress-bar-fill {
      height: 100%;
      background: linear-gradient(90deg, #28a745, #20c997);
      border-radius: 999px;
      transition: width 0.3s ease;
    }
    .progress-percentage {
      font-size: .75rem;
      color: #6c757d;
      text-align: right;
    }

    .card-foot{
      padding:.5rem .65rem;
      border-top:1px solid #eef1f4;
      display:flex;
      gap:.35rem;
      justify-content:flex-end;
      background:#fff;
      flex-wrap: wrap;
    }

    .hidden{ display:none !important; }

    /* Publish toggle (looks like a badge, acts like a button) */
    .pub-toggle-badge{
      cursor:pointer;
      border:0;
      font-size:.72rem;
      font-weight:600;
      padding:.28rem .45rem;
      line-height:1.2;
      vertical-align:middle;
    }
    .pub-toggle-badge:hover{
      filter:brightness(0.95);
    }
    .pub-toggle-badge:disabled{
      opacity:.65;
      cursor:wait;
      pointer-events:none;
    }

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

    /* Detail button style */
    .btn-detail {
      background: linear-gradient(135deg, #6c757d, #495057);
      color: white;
      border: none;
    }
    .btn-detail:hover {
      background: linear-gradient(135deg, #495057, #343a40);
      color: white;
      transform: translateY(-1px);
      box-shadow: 0 2px 5px rgba(108, 117, 125, 0.3);
    }

    /* Status filter dropdown */
    .status-filter {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }

    .status-filter label {
      font-weight: 600;
      color: #495057;
      font-size: 0.9rem;
      margin: 0;
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
      
      .card-foot {
        gap: .3rem;
      }
      
      .status-filter {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
      }
    }

    /* Action buttons */
    .btn-sm {
      padding: .25rem .5rem;
      font-size: .75rem;
    }
    .quiz-action-dropdown .dropdown-toggle{
      min-width: 94px;
    }
    .quiz-action-dropdown .dropdown-menu{
      min-width: 185px;
      font-size: .82rem;
    }
    .quiz-action-dropdown .dropdown-item{
      display:flex;
      align-items:center;
      gap:.45rem;
      padding:.38rem .7rem;
    }
  </style>

  <?php
    // ---------- Get status filter from URL ----------
    $status = $_GET['status'] ?? 'published'; // Default to 'published'
    
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

    // ---------- Filter quizzes based on status ----------
    $filteredQuizzes = [];
    if (!empty($quizzes)) {
      foreach ($quizzes as $q) {
        $isPublished = (int)($q->is_published ?? 0) === 1;
        $isOpen = $isOpenQuiz($q);
        
        switch ($status) {
          case 'published':
            if ($isPublished) {
              $filteredQuizzes[] = $q;
            }
            break;
          case 'unpublished':
            if (!$isPublished) {
              $filteredQuizzes[] = $q;
            }
            break;
          case 'all':
            $filteredQuizzes[] = $q;
            break;
          default:
            if ($isPublished) {
              $filteredQuizzes[] = $q;
            }
        }
      }
    }

    // ---------- Build filter options from filtered quizzes ----------
    $clsOptions  = [];
    $subjOptions = [];

    if (!empty($filteredQuizzes)) {
      foreach ($filteredQuizzes as $q) {
        $clsId  = (int)($q->cls_sec_id ?? 0);
        $clsLbl = trim($q->cls_sec_name ?? ('Class-Section #'.$clsId));
        if ($clsId) $clsOptions[$clsId] = $clsLbl;

        $subLbl = trim($q->sec_sub_name ?? 'Subject');
        if ($subLbl !== '') $subjOptions[$subLbl] = $subLbl;
      }
    }
    ksort($clsOptions);
    ksort($subjOptions);

    $termOptions = $termOptions ?? [];
    $selectedTermSessionId = (int)($selectedTermSessionId ?? 0);
    $currentTermSessionId  = (int)($currentTermSessionId ?? 0);

    // ---------- Group filtered quizzes by class-section ----------
    $byClass = [];
    if (!empty($filteredQuizzes)) {
      foreach ($filteredQuizzes as $q) {
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
    ksort($byClass); // Order by class_id
  ?>

  <!-- ===== Status Filter ===== -->
  <div class="status-filter">
    <label for="statusFilter">Show:</label>
    <select id="statusFilter" class="form-control form-control-sm" style="width: auto;">
      <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published & Active</option>
      <option value="unpublished" <?= $status === 'unpublished' ? 'selected' : '' ?>>Unpublished</option>
      <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Quizzes</option>
    </select>
  </div>

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
        <option value="" <?= $selectedTermSessionId === 0 ? 'selected' : '' ?>>All</option>
        <?php foreach ($termOptions as $tsid => $lbl): ?>
          <option value="<?= (int)$tsid ?>" <?= $selectedTermSessionId === (int)$tsid ? 'selected' : '' ?>><?= esc($lbl) ?></option>
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

          <div class="class-head" onclick="toggleClassExpand(<?= (int)$clsId ?>)">
            <h3 class="class-title">
              <i class="fas fa-chevron-down me-1"></i>
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

          <div class="nested-quiz-grid" id="quizGrid<?= (int)$clsId ?>">
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

  $createdAt = $q->created_date ?? null;
  $createdLabel = $createdAt ? date('M d, Y', strtotime((string) $createdAt)) : '';

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
  $totalAttempts = 0;
  for ($i = 1; $i <= 10; $i++) {
    $attemptField = 'attempt_' . $i . '_count';
    $count = $getInt($q, [$attemptField], 0);
    $attemptCounts[$i] = $count;
    $totalAttempts += $count;
  }
  
  $attemptedStd    = $getInt($q, ['attempted_students_count','attempted_students','attempted_count'], 0);
  $unattemptedStd  = max(0, $classStudents - $attemptedStd);

  // Calculate attempt percentages for modal
  $attemptPercentages = [];
  foreach ($attemptCounts as $attemptNum => $count) {
    $percentage = $classStudents > 0 ? round(($count / $classStudents) * 100) : 0;
    $attemptPercentages[$attemptNum] = $percentage;
  }

  $openNow = $isOpenQuiz($q);

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
                   data-published="<?= $pub ? 1 : 0 ?>"
              >

                <div class="card-head">
                  <h4 class="subj-title">
                    <i class="fas fa-book text-muted"></i>
                    <?= esc($subName) ?>
                  </h4>

                  <div class="quiz-badges">
                    <button type="button"
                            class="badge pub-toggle-badge <?= $pub ? 'text-bg-primary' : 'text-bg-secondary' ?>"
                            data-quiz-id="<?= $qid ?>"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            title="<?= $pub ? 'Click to unpublish (hide from students)' : 'Click to publish (visible to students)' ?>">
                      <?= $pub ? 'Published' : 'Unpublished' ?>
                    </button>

                    <?php if ($mins): ?>
                      <span class="badge text-bg-info" data-bs-toggle="tooltip" title="Time limit"><?= (int)$mins ?> min</span>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="card-body">
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

                  <div class="stats-row">
                    <span class="stat-item" data-bs-toggle="tooltip" title="Students who attempted this quiz">
                      <i class="fas fa-user-check"></i>
                      <strong><?= (int) $attemptedStd ?></strong>
                      <?= (int) $attemptedStd === 1 ? 'student attempted' : 'students attempted' ?>
                    </span>
                  </div>

                  <?php if (!empty($qtypeItems)): ?>
                    <div class="qtype-summary mt-1">
                      <?php foreach ($qtypeItems as $item): ?>
                        <span class="qtype-item" data-bs-toggle="tooltip" title="<?= esc($item['label']) ?> questions">
                          <i class="<?= $item['icon'] ?>"></i>
                          <span class="qtype-count"><?= $item['count'] ?></span>
                          <span class="muted"><?= esc($item['label']) ?></span>
                        </span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                  <!-- Hidden detailed attempt progress for modal -->
                  <div class="attempts-progress-detailed" id="attemptProgress_<?= $qid ?>">
                    <?php if ($attemptsAllowed > 0 && $classStudents > 0): ?>
                      <?php for ($i = 1; $i <= $attemptsAllowed; $i++): ?>
                        <?php
                          $attemptPct = $classStudents > 0 ? round(($attemptCounts[$i] / $classStudents) * 100) : 0;
                          if ($attemptPct > 100) $attemptPct = 100;
                        ?>
                        <div class="attempt-item-detailed">
                          <div class="attempt-header-detailed">
                            <span class="attempt-label-detailed">Attempt <?= $i ?></span>
                            <span class="attempt-count-detailed"><?= $attemptCounts[$i] ?>/<?= $classStudents ?></span>
                          </div>
                          <div class="progress-bar-container" data-bs-toggle="tooltip" title="<?= $attemptPct ?>% of students completed attempt <?= $i ?>">
                            <div class="progress-bar-fill" style="width: <?= $attemptPct ?>%"></div>
                          </div>
                          <div class="progress-percentage"><?= $attemptPct ?>%</div>
                        </div>
                      <?php endfor; ?>
                    <?php endif; ?>
                  </div>

                  <div class="text-muted small mt-2" data-bs-toggle="tooltip" title="Term session and quiz creation date">
                    <i class="fas fa-calendar-alt"></i>
                    <?= esc($tsName) ?>
                    <?php if ($createdLabel !== ''): ?>
                      <span class="muted">·</span> Created <?= esc($createdLabel) ?>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Compact action menu -->
                <div class="card-foot">
                  <div class="dropdown quiz-action-dropdown">
                    <button class="btn btn-outline-primary btn-sm dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                      <i class="fas fa-ellipsis-v me-1"></i> Actions
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                      <button class="dropdown-item btn-detail-modal"
                              type="button"
                              data-quiz-id="<?= $qid ?>"
                              data-quiz-title="<?= esc($title) ?>"
                              data-subject-name="<?= esc($subName) ?>"
                              data-class-students="<?= $classStudents ?>"
                              data-attempted-students="<?= $attemptedStd ?>"
                              data-total-attempts="<?= $totalAttempts ?>"
                              data-max-attempts="<?= $attemptsAllowed ?>">
                        <i class="fas fa-chart-bar text-muted"></i> Detail
                      </button>
                      <a class="dropdown-item btn-results"
                         href="<?= site_url('admin/quizzes/'.$qid.'/results') ?>">
                        <i class="fas fa-poll text-muted"></i> Results
                      </a>
                      <a class="dropdown-item btn-print"
                         href="<?= site_url('admin/quizzes/print/'.$qid) ?>"
                         target="_blank">
                        <i class="fas fa-print text-muted"></i> Print (Single)
                      </a>
                      <a class="dropdown-item btn-edit-questions"
                         href="<?= site_url('admin/quizzes/edit-questions/' . $qid) ?>">
                        <i class="fas fa-edit text-muted"></i> Edit Questions
                      </a>
                      <a class="dropdown-item btn-print"
                         href="<?= site_url('admin/quizzes/print-all/'.$qid) ?>"
                         target="_blank">
                        <i class="fas fa-print text-muted"></i> Print (All)
                      </a>
                      <a class="dropdown-item btn-print"
                         href="<?= site_url('admin/quizzes/print-all-key/'.$qid) ?>"
                         target="_blank">
                        <i class="fas fa-key text-muted"></i> Print (All Key)
                      </a>
                      <a class="dropdown-item btn-print-all"
                         href="<?= site_url('admin/quizzes/print-versions/'.$qid) ?>"
                         target="_blank">
                        <i class="fas fa-copy text-muted"></i> Print All Versions
                      </a>
                      <a class="dropdown-item btn-edit"
                         href="<?= site_url('admin/quizzes/edit/'.$qid) ?>">
                        <i class="fas fa-pen text-muted"></i> Edit
                      </a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item text-danger btn-delete-quiz"
                         href="javascript:void(0);"
                         data-quiz-id="<?= $qid ?>"
                         data-quiz-title="<?= htmlspecialchars($title) ?>">
                        <i class="fas fa-trash-alt"></i> Delete Quiz
                      </a>
                    </div>
                  </div>
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
        <p class="text-muted">
          <?php if ($status === 'published'): ?>
            No published quizzes available. Create or publish a quiz to get started.
          <?php elseif ($status === 'unpublished'): ?>
            No unpublished quizzes available.
          <?php else: ?>
            No quizzes available. Create your first quiz to get started.
          <?php endif; ?>
        </p>
        <a href="<?= base_url('admin/quizzes/create') ?>" class="btn btn-primary mt-2">
          <i class="fas fa-plus me-1"></i> Create Quiz
        </a>
      </div>
    <?php endif; ?>
  </div>

  <!-- ===== Attempt Progress Modal ===== -->
  <div class="modal fade attempt-progress-modal" id="attemptProgressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="attemptModalTitle">Attempt Progress</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="progress-header">
            <div class="progress-summary">
              <strong><span id="modalQuizTitle"></span></strong> | 
              <span id="modalSubject"></span> | 
              Students: <strong><span id="modalTotalStudents"></span></strong>
            </div>
            <div class="text-end">
              <small class="text-muted">
                Attempted: <strong><span id="modalAttemptedStudents"></span></strong> | 
                Total Attempts: <strong><span id="modalTotalAttempts"></span></strong>
              </small>
            </div>
          </div>
          
          <div id="modalProgressContent">
            <!-- Progress bars will be inserted here -->
          </div>
          
          <div class="mt-4 pt-3 border-top">
            <div class="row">
              <div class="col-md-6">
                <div class="alert alert-light small mb-0">
                  <i class="fas fa-info-circle text-primary me-1"></i>
                  <strong>Note:</strong> Each bar shows the percentage of students who completed that specific attempt.
                </div>
              </div>
              <div class="col-md-6 text-end">
                <small class="text-muted">
                  <i class="fas fa-chart-line me-1"></i>
                  Progress as of <?= date('M d, Y H:i') ?>
                </small>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <a href="#" id="modalResultsLink" class="btn btn-primary">
            <i class="fas fa-chart-bar me-1"></i> View Full Results
          </a>
        </div>
      </div>
    </div>
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
            <div><kbd>D</kbd> Detail modal</div>
            <div><kbd>Esc</kbd> Reset</div>
          </div>
          <hr class="my-2">
          <div>
            <div><b>Icons</b></div>
            <div><i class="fas fa-list-ol"></i> Questions (planned, tooltip shows actual)</div>
            <div><i class="fas fa-redo"></i> Attempts</div>
            <div><i class="fas fa-user-graduate"></i> Students</div>
            <div><i class="fas fa-chart-bar"></i> Detailed Progress</div>
            <div><i class="fas fa-users"></i> Attempted Students</div>
            <div><i class="fas fa-clipboard-check"></i> Total Attempts</div>
            <div><i class="far fa-check-square"></i> MCQ</div>
            <div><i class="fas fa-tasks"></i> MCQ Multi</div>
            <div><i class="fas fa-pen"></i> Fill</div>
            <div><i class="fas fa-toggle-on"></i> T/F</div>
            <div><i class="fas fa-random"></i> Match</div>
            <div><i class="fas fa-align-left"></i> Short</div>
            <div><i class="fas fa-tag"></i> Topics</div>
            <div><i class="fas fa-chevron-down"></i> Expand/Collapse</div>
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
  const $statusFilter = document.getElementById('statusFilter');

  // Modal elements
  const $attemptProgressModal = document.getElementById('attemptProgressModal');
  const $modalQuizTitle = document.getElementById('modalQuizTitle');
  const $modalSubject = document.getElementById('modalSubject');
  const $modalTotalStudents = document.getElementById('modalTotalStudents');
  const $modalAttemptedStudents = document.getElementById('modalAttemptedStudents');
  const $modalTotalAttempts = document.getElementById('modalTotalAttempts');
  const $modalProgressContent = document.getElementById('modalProgressContent');
  const $modalResultsLink = document.getElementById('modalResultsLink');

  // Expand/collapse state
  let expandedClassId = null;

  /* Full toggle URL from CI (includes index.php / base path — matches server routing) */
  const QUIZ_TOGGLE_PUBLISHED_TMPL = <?= json_encode(site_url('admin/quizzes/__QUIZ__/toggle-published')) ?>;
  let quizCsrfName = <?= json_encode(csrf_token()) ?>;
  let quizCsrfHash = <?= json_encode(csrf_hash()) ?>;
  const currentTermSessionId = <?= json_encode($currentTermSessionId) ?>;

  function quizTogglePublishedUrl(quizId){
    return QUIZ_TOGGLE_PUBLISHED_TMPL.replace('__QUIZ__', String(quizId));
  }

  function normalize(s){ return (s || '').toString().trim().toLowerCase(); }

  function getUrlStatusFilter(){
    const p = new URLSearchParams(window.location.search);
    const s = p.get('status');
    if (s === 'all' || s === 'unpublished' || s === 'published') return s;
    return 'published';
  }

  function buildQuizzesListUrl(status, termSessionId){
    let url = window.location.pathname + '?status=' + encodeURIComponent(status || getUrlStatusFilter());
    if (termSessionId === 'all' || termSessionId === 0 || termSessionId === '0' || termSessionId === '') {
      url += '&term_session_id=all';
    } else if (termSessionId != null && termSessionId !== '') {
      url += '&term_session_id=' + encodeURIComponent(String(termSessionId));
    }
    return url;
  }

  function applyFilters(){
    const clsVal  = $fClsSec.value;
    const subVal  = $fSubject.value;
    const termVal = $fTerm.value;
    const q = normalize($fSearch.value);
    const urlStatus = getUrlStatusFilter();

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

      if (ok && urlStatus === 'published') {
        if (card.getAttribute('data-published') !== '1') ok = false;
      }
      if (ok && urlStatus === 'unpublished') {
        if (card.getAttribute('data-published') !== '0') ok = false;
      }

      card.classList.toggle('hidden', !ok);
    });

    classWraps.forEach(wrap => {
      const visibleInside = wrap.querySelectorAll('.quiz-card:not(.hidden)').length;
      wrap.classList.toggle('hidden', visibleInside === 0);
    });

    // Refresh keyboard navigation
    window.__kbNavRefresh && window.__kbNavRefresh();
  }

  [$fClsSec, $fSubject].forEach(sel => sel && sel.addEventListener('change', applyFilters));
  $fSearch && $fSearch.addEventListener('input', applyFilters);

  $fTerm && $fTerm.addEventListener('change', function(){
    const termVal = this.value;
    const status = getUrlStatusFilter();
    window.location.href = buildQuizzesListUrl(status, termVal === '' ? 'all' : termVal);
  });

  $fReset && $fReset.addEventListener('click', function(){
    if ($fClsSec)  $fClsSec.value = '';
    if ($fSubject) $fSubject.value = '';
    if ($fSearch)  $fSearch.value = '';
    if ($openOnlyIndicator){
      $openOnlyIndicator.classList.remove('active');
      $openOnlyIndicator.dataset.openOnly = '0';
    }

    const status = getUrlStatusFilter();
    const defaultTerm = currentTermSessionId > 0 ? currentTermSessionId : 'all';
    window.location.href = buildQuizzesListUrl(status, defaultTerm);
  });

  // Status filter change
  $statusFilter && $statusFilter.addEventListener('change', function() {
    const status = this.value;
    const termVal = $fTerm ? $fTerm.value : '';
    window.location.href = buildQuizzesListUrl(status, termVal === '' ? 'all' : termVal);
  });

  document.addEventListener('click', function(e){
    const btn = e.target.closest('.pub-toggle-badge');
    if (!btn || !$classGrid.contains(btn)) return;
    e.preventDefault();
    const quizId = btn.getAttribute('data-quiz-id');
    if (!quizId) return;

    btn.disabled = true;
    const fd = new FormData();
    fd.append(quizCsrfName, quizCsrfHash);

    fetch(quizTogglePublishedUrl(quizId), {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function(r){
        if (!r.ok) {
          if (r.status === 404) {
            throw new Error('Route not found (404). Deploy app/Config/Routes.php and ensure Quizzes::togglePublished exists, then clear opcode cache.');
          }
          throw new Error('HTTP ' + r.status);
        }
        var ct = r.headers.get('content-type') || '';
        if (ct.indexOf('application/json') === -1) {
          throw new Error('Unexpected response (not JSON).');
        }
        return r.json();
      })
      .then(function(data){
        btn.disabled = false;
        if (!data || !data.ok) {
          window.alert((data && data.error) ? data.error : 'Could not update publish status.');
          return;
        }
        if (data.csrf_hash) {
          quizCsrfHash = data.csrf_hash;
        }
        var published = parseInt(data.is_published, 10) === 1;
        var card = btn.closest('.quiz-card');
        if (card) {
          card.setAttribute('data-published', published ? '1' : '0');
        }
        btn.textContent = published ? 'Published' : 'Unpublished';
        btn.classList.remove('text-bg-primary', 'text-bg-secondary');
        btn.classList.add(published ? 'text-bg-primary' : 'text-bg-secondary');
        btn.setAttribute('title', published ? 'Click to unpublish (hide from students)' : 'Click to publish (visible to students)');
        if (window.jQuery) {
          window.jQuery(btn).tooltip('dispose');
          window.jQuery(btn).tooltip({ container: 'body', boundary: 'window', delay: { show: 300, hide: 100 } });
        }
        applyFilters();
      })
      .catch(function(err){
        btn.disabled = false;
        window.alert(err && err.message ? err.message : 'Network error. Please try again.');
      });
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

  // ===== Expand/Collapse Functionality =====
  window.toggleClassExpand = function(classId) {
    const classHead = document.querySelector(`#classWrap${classId} .class-head`);
    const quizGrid = document.getElementById(`quizGrid${classId}`);
    
    // If clicking the already expanded class, collapse it
    if (expandedClassId === classId) {
      classHead.classList.remove('expanded');
      quizGrid.classList.remove('expanded');
      expandedClassId = null;
    } else {
      // Collapse previously expanded class
      if (expandedClassId !== null) {
        const prevHead = document.querySelector(`#classWrap${expandedClassId} .class-head`);
        const prevGrid = document.getElementById(`quizGrid${expandedClassId}`);
        if (prevHead && prevGrid) {
          prevHead.classList.remove('expanded');
          prevGrid.classList.remove('expanded');
        }
      }
      
      // Expand new class
      classHead.classList.add('expanded');
      quizGrid.classList.add('expanded');
      expandedClassId = classId;
    }
  };

  // ===== Detail Button Modal Functionality =====
  document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-detail-modal')) {
      const button = e.target.closest('.btn-detail-modal');
      const quizId = button.getAttribute('data-quiz-id');
      const quizTitle = button.getAttribute('data-quiz-title');
      const subjectName = button.getAttribute('data-subject-name');
      const classStudents = button.getAttribute('data-class-students');
      const attemptedStudents = button.getAttribute('data-attempted-students');
      const totalAttempts = button.getAttribute('data-total-attempts');
      const maxAttempts = button.getAttribute('data-max-attempts');

      // Set modal content
      $modalQuizTitle.textContent = quizTitle;
      $modalSubject.textContent = subjectName;
      $modalTotalStudents.textContent = classStudents;
      $modalAttemptedStudents.textContent = attemptedStudents;
      $modalTotalAttempts.textContent = totalAttempts;
      
      // Update results link
      $modalResultsLink.href = '/admin/quizzes/' + quizId + '/results';
      
      // Get detailed progress from hidden div
      const progressDiv = document.getElementById('attemptProgress_' + quizId);
      if (progressDiv) {
        $modalProgressContent.innerHTML = progressDiv.innerHTML;
      } else {
        $modalProgressContent.innerHTML = '<div class="alert alert-info">No attempt data available.</div>';
      }

      // Update modal title with attempt info
      document.getElementById('attemptModalTitle').textContent = 
        'Attempt Progress: ' + quizTitle;

      // Show modal
      $('#attemptProgressModal').modal('show');
    }
  });

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

    // New shortcut for Detail modal
    if (e.key.toLowerCase() === 'd') {
      e.preventDefault();
      if (!cards.length) refreshCards();
      if (!cards.length) return;
      const cur = cards[idx];
      cur.querySelector('.btn-detail-modal')?.click();
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

$(document).ready(function() {
    console.log("Delete script loaded");
    
    // Delete quiz confirmation - REDIRECT TO CONFIRMATION PAGE
    $(document).on('click', '.btn-delete-quiz', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation(); // Prevent other handlers
        
        const quizId = $(this).data('quiz-id');
        const quizTitle = $(this).data('quiz-title');
        
        console.log("Delete button clicked for quiz ID:", quizId);
        console.log("Generated URL:", '<?= site_url("admin/quizzes/delete-quiz") ?>/' + quizId);
        
        // Simple confirm dialog
        if (!confirm(`Are you sure you want to delete "${quizTitle}"?\n\nThis will redirect you to a confirmation page.`)) {
            console.log("Delete cancelled by user");
            return;
        }
        
        // Show loading on button
        const $button = $(this);
        const originalHtml = $button.html();
        $button.html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        $button.prop('disabled', true);
        
        // Build URL with full path to avoid any issues
        const deleteUrl = '<?= site_url("admin/quizzes/delete-quiz") ?>/' + quizId;
        console.log("Redirecting to:", deleteUrl);
        
        // Redirect to confirmation page (GET request)
        window.location.href = deleteUrl;
        
        // Restore button after 2 seconds in case redirect fails
        setTimeout(function() {
            $button.html(originalHtml).prop('disabled', false);
            console.log("Button restored - redirect may have failed");
        }, 2000);
    });
    
    // Optional: Direct delete without confirmation page (if you prefer)
    $(document).on('click', '.btn-delete-quiz-direct', function(e) {
        e.preventDefault();
        
        const quizId = $(this).data('quiz-id');
        const quizTitle = $(this).data('quiz-title');
        const $button = $(this);
        const originalHtml = $button.html();
        
        if (!confirm(`Are you sure you want to PERMANENTLY delete "${quizTitle}"?\n\nThis action cannot be undone!`)) {
            return;
        }
        
        $button.html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
        $button.prop('disabled', true);
        
        // Create form for direct deletion (POST request)
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= site_url("admin/quizzes/delete-quiz") ?>/' + quizId;
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '<?= csrf_token() ?>';
        csrfToken.value = '<?= csrf_hash() ?>';
        form.appendChild(csrfToken);
        
        // Add method spoofing for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        // Add confirmation (required by your controller)
        const confirmInput = document.createElement('input');
        confirmInput.type = 'hidden';
        confirmInput.name = 'confirmation';
        confirmInput.value = 'DELETE';
        form.appendChild(confirmInput);
        
        // Add to body and submit
        document.body.appendChild(form);
        form.submit();
    });
    
    // Toast notification function
    function showToast(type, message) {
        // Create toast container if it doesn't exist
        if ($('.toast-container').length === 0) {
            $('body').append('<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1060;"></div>');
        }
        
        const toastId = 'toast-' + Date.now();
        const toast = $(`
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-${type === 'success' ? 'success' : 'danger'} text-white">
                    <strong class="me-auto">${type === 'success' ? 'Success' : 'Error'}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>
        `);
        
        $('.toast-container').append(toast);
        
        // Initialize and show toast
        const bsToast = new bootstrap.Toast(toast[0], { delay: 3000 });
        bsToast.show();
        
        // Remove after hide
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
    
    // Update class metrics after deletion
    function updateClassMetrics() {
        $('.class-card').each(function() {
            const $classCard = $(this);
            const classId = $classCard.data('class-id');
            const quizzes = $classCard.find('.quiz-card');
            const total = quizzes.length;
            let open = 0, closed = 0;
            
            quizzes.each(function() {
                if ($(this).data('open') === 1) {
                    open++;
                } else {
                    closed++;
                }
            });
            
            // Update metrics display
            $classCard.find('.metric-pill:first strong').text(total);
            $classCard.find('.metric-pill:nth-child(2) strong').text(open);
            $classCard.find('.metric-pill:nth-child(3) strong').text(closed);
        });
    }
    
    // Debug: Check if buttons exist
    setTimeout(function() {
        const deleteButtons = $('.btn-delete-quiz');
        console.log("Found", deleteButtons.length, "delete buttons on page");
        
        deleteButtons.each(function(index) {
            console.log(`Button ${index}:`, {
                id: $(this).data('quiz-id'),
                title: $(this).data('quiz-title')
            });
        });
    }, 1000);
});
</script>
<?= $this->endSection() ?>