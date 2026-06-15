<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<style>
  :root{
    --bg: #f7fbff;
    --card: #ffffff;
    --brand: #6759ff;
    --brand-2:#00c4ff;
    --ok: #1cc88a;
    --warn: #f6c23e;
    --danger:#e74a3b;
    --ink:#223;
  }
  body{background:var(--bg)}

  /* ===== TOP BAR ===== */
  .quiz-topbar{
    position:sticky;top:0;z-index:1030;
    background: linear-gradient(90deg, var(--brand) 0%, var(--brand-2) 100%);
    padding:.7rem 1rem 0.5rem;
    color:#fff;
    box-shadow:0 2px 10px rgba(0,0,0,.08)
  }
  .quiz-title{font-weight:800;margin:0;font-size:1.15rem;letter-spacing:.3px}
  .quiz-meta{opacity:.92;font-size:.82rem}

  .pill{
    border-radius:999px;
    padding:.25rem .6rem;
    border:0;
    display:inline-flex;
    align-items:center;
    gap:.35rem;
    background:rgba(0,0,0,.18);
    font-size:.8rem;
  }

  /* BIG TIMER */
  .timer-shell{
    min-width:140px;
    padding:.35rem .9rem;
    border-radius:999px;
    background:rgba(0,0,0,.15);
    display:flex;
    align-items:center;
    justify-content:center;
    gap:.45rem;
    font-weight:800;
    box-shadow:0 0 0 2px rgba(255,255,255,.35);
  }
  .timer-shell i{font-size:1rem}
  .timer-shell span{font-size:1rem}
  .timer-ok{color:#e8fff4}
  .timer-warn{color:#fff7df}
  .timer-danger{color:#ffe5e3}

  /* Layout */
  .quiz-wrap{
    max-width:980px;
    margin:12px auto 80px;
    padding:0 12px;
    min-height:calc(100vh - 170px); /* keep area fixed so footer stays in place */
  }
  .question-block{display:none}
  .question-block.active{display:block}

  /* Question Card */
  .q-card{
    background:var(--card);
    border:0;
    border-radius:22px;
    overflow:hidden;
    box-shadow:0 8px 24px rgba(103,89,255,0.12);
    min-height:260px;
  }
  .q-header{
    background:linear-gradient(90deg, rgba(103,89,255,.12) 0%, rgba(0,196,255,.12) 100%);
    padding:14px 18px;
    display:flex;
    align-items:center;
    gap:10px
  }
  .q-bubble{
    width:36px;height:36px;border-radius:50%;
    background:linear-gradient(135deg,var(--brand),var(--brand-2));
    color:#fff;display:flex;align-items:center;justify-content:center;
    font-weight:800;box-shadow:0 4px 10px rgba(0,0,0,.08)
  }
  .q-text{font-size:1.08rem;line-height:1.6;color:var(--ink);margin:0}

  .q-body{padding:16px 18px 12px}

  /* Options */
  .option{
    border:2px solid transparent;
    border-radius:16px;
    padding:.7rem .9rem;margin-bottom:.6rem;cursor:pointer;
    background:#f9fbff; display:flex; align-items:center; gap:.55rem;
    transition:transform .05s ease, box-shadow .2s ease, background .2s ease, border-color .2s ease;
  }
  .option:hover{transform:translateY(-1px);box-shadow:0 6px 14px rgba(0,0,0,.06)}
  .option input{margin-top:2px}
  .option span{font-size:1rem}
  .option.checked{
    border-color:var(--brand);
    background:linear-gradient(0deg, rgba(103,89,255,.10), rgba(103,89,255,.10));
    box-shadow:0 6px 16px rgba(103,89,255,.18);
  }

  /* Footer (sticky, centered buttons) */
 .quiz-footer{
  position: fixed;
  bottom: 12px;          /* ⬆ move UP */
  left: 0;
  right: 0;
  background: transparent;
  border-top: none;
  padding: 0;
  z-index: 1035;
}

.quiz-footer .d-flex {
  background: #ffffff;
  padding: .55rem .75rem;
  border-radius: 999px;
  box-shadow: 0 10px 28px rgba(0,0,0,.15);
}
  .btn-pill{
    border-radius:999px; font-weight:700; padding:.55rem 1.4rem
  }
  .btn-next{
    background:linear-gradient(90deg, var(--brand) 0%, var(--brand-2) 100%);
    color:#fff;border:0;
  }
  .btn-prev{border:2px solid rgba(0,0,0,.08); background:#fff}
  .btn-submit{background:var(--ok); color:#fff; border:0}

  /* Autosave */
  .autosave-dot{
    width:9px;height:9px;border-radius:50%;
    display:inline-block;
    background:#6c757d
  }
  .autosave-ok{background:var(--ok)}
  .autosave-pending{background:var(--warn)}
  .autosave-fail{background:var(--danger)}
  .autosave-icon{font-size:.9rem}
  /* hide text label – we only use it internally */
  #autosaveText{display:none;}

  /* MATCH styles (same as before) */
  .match-wrapper{margin-top:.5rem}
  .match-row{margin-bottom:.5rem}
  .match-left-label{width:45%}
  .match-dnd .match-row{margin-bottom:8px}
  .match-bank{
    display:flex;
    flex-direction:column;
    gap:6px;
    margin-top:4px;
  }
  .match-chip{
    display:block;
    width:100%;
    padding:6px 10px;
    margin:0;
    border-radius:999px;
    border:1px solid #d1d5db;
    background:#ffffff;
    cursor:grab;
    font-size:0.9rem;
    box-shadow:0 1px 3px rgba(0,0,0,.05);
  }
  .match-dropzone{
    min-height:38px;
    border:1px dashed #cbd5e0;
    border-radius:8px;
    padding:4px 8px;
    background:#f9fbff;
    display:flex;
    align-items:center;
    justify-content:flex-start;
    font-size:0.9rem;
  }
  .match-dropzone.over{
    background:#e0f2fe;
    border-color:#60a5fa;
  }
  .match-dropzone .match-chip{margin:0}
  .match-chip.dragging{
    opacity:.7;
    box-shadow:0 4px 10px rgba(0,0,0,.12);
  }

  .question-media{
  width:100%;
}
.question-img{
  width:100%;
  max-height:420px;
  object-fit:contain;
  display:block;
  border-radius:14px;
  background:#fff;
  border:1px solid rgba(0,0,0,.06);
  cursor: zoom-in;
}

/* ===============================
   IMAGE QUESTION → HORIZONTAL OPTIONS
================================ */
.q-image-question .options-wrap{
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-top: 10px;
}

/* Make options compact */
/* Only when question is an image */
.q-image-question .options-wrap{
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr)); /* 4 columns */
  gap: 10px;
}

/* responsive fallback */
@media (max-width: 992px){
  .q-image-question .options-wrap{ grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 576px){
  .q-image-question .options-wrap{ grid-template-columns: 1fr; }
}


/* ===============================
   IMAGE QUESTION HEIGHT CONTROL
================================ */

/* Apply only to image-based questions */
.q-image-question .question-img{
  max-height: 260px;          /* 🔥 adjust if needed (220–300 safe) */
  width: auto;
  max-width: 100%;
  object-fit: contain;        /* never crop */
  display: block;
  margin: 0 auto;
}

/* Reduce extra padding when image exists */
.q-image-question .q-header{
  padding-bottom: 8px;
}

.q-image-question .q-body{
  padding-top: 10px;
}

/* Ensure card never causes scroll */
.q-image-question{
  overflow: hidden;
}

/* Smaller screens */
@media (max-width: 768px){
  .q-image-question .question-img{
    max-height: 200px;
  }
}


/* ---------------------------
   IMAGE QUESTION UI
----------------------------*/
.q-image-question .question-media{
  width:100%;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  gap:6px;
}

.q-image-question .question-img{
  max-width: 100%;
  max-height: 260px;   /* ✅ reduce height to avoid scroll */
  height: auto;
  object-fit: contain;
  border-radius: 12px;
  background: #fff;
  cursor: zoom-in;
}

/* keep header smaller for image questions */
.q-image-question .q-header{
  align-items:flex-start;
}

/* ---------------------------
   OPTIONS LAYOUT
----------------------------*/
.options-wrap{
  display:block;
}

/* Default (normal questions): vertical */
.options-wrap:not(.options-4col) .option{
  width:100%;
}

/* ✅ Image question: 4 columns in one row */
.options-wrap.options-4col{
  display:grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap:10px;
}

@media (max-width: 992px){
  .options-wrap.options-4col{
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
@media (max-width: 576px){
  .options-wrap.options-4col{
    grid-template-columns: 1fr;
  }
}

/* ---------------------------
   FIX OPTION CLICK BUG (IMPORTANT)
   Sometimes labels stop receiving click due to overlay.
----------------------------*/
.option{
  position:relative;
  cursor:pointer;
  user-select:none;
  z-index:5; /* keep options above any overlay */
}



/* but keep input clickable */
.option input{
  pointer-events:auto;
}

/* prevent question image wrapper from stealing clicks below */
.q-header, .question-media, .q-text{
  position:relative;
  z-index:1;
}
.q-body{
  position:relative;
  z-index:10;
}


/* ===============================
   FIX SCROLL – FULL SCREEN QUIZ
================================ */

/* Prevent page scrolling during quiz */
body {
  overflow: hidden;
}

/* Quiz area fits screen between topbar & footer */
.quiz-wrap {
  height: calc(100vh - 150px); /* topbar + footer */
  overflow: hidden;
  margin-bottom: 0;
}

/* Each question card fits inside viewport */
.q-card {
  max-height: calc(100vh - 190px);
  overflow: hidden;
}

.q-header {
  padding: 10px 14px;
}

.q-body {
  padding: 10px 14px 8px;
}

.option {
  padding: .45rem .7rem;
  margin-bottom: .45rem;
  border-radius: 12px;
}

.option span {
  font-size: .95rem;
}

.q-image-question .question-img {
  max-height: 200px;   /* mobile-safe */
}

@media (max-width: 576px){
  .q-image-question .question-img{
    max-height: 160px;
  }
}

@media (max-width: 576px){
  .quiz-title {
    font-size: 1rem;
  }

  .timer-shell {
    min-width: 110px;
    padding: .25rem .6rem;
  }

  .q-text {
    font-size: .98rem;
  }
}




/* Style for pre-filled answers */
.option.has-answer {
    border-color: var(--brand);
    background: linear-gradient(0deg, rgba(103,89,255,.08), rgba(103,89,255,.08));
}

input.has-answer, textarea.has-answer {
    border-color: var(--brand) !important;
    background-color: rgba(103,89,255,.05) !important;
}

.match-dropzone.has-answer {
    border-color: var(--brand) !important;
    background-color: rgba(103,89,255,.05) !important;
}
</style>

<?php
// Try to build meta line safely – adjust variable names as per your controller
$classSection = $classSection
    ?? ($class_section ?? null)
    ?? (($class_name ?? '') . (!empty($section_name) ? ' - '.$section_name : ''));
$subjectName  = $subject_name  ?? ($subjectTitle ?? null);
$topicTitle   = $topicTitle    ?? ($topic_name ?? ($topics ?? null));

$metaPieces = [];

if (!empty($classSection)) {
    $metaPieces[] = $classSection;
}

if (!empty($subjectName)) {
    $metaPieces[] = $subjectName;
}

$topicList = $topicList ?? [];
$topicSuffix = '';

if (!empty($topicList)) {
    // e.g. (Fractions, Decimals, Percentages)
    $topicSuffix = ' (' . implode(', ', $topicList) . ')';
}
?>

<section class="quiz-topbar">
  <div class="d-flex align-items-center justify-content-between flex-wrap flex-md-nowrap">
    <div class="mb-1 mb-md-0">
      <h1 class="quiz-title mb-0">
        🎮 <?= esc($quiz->title) . esc($topicSuffix) ?>
      </h1>
      <?php if ($metaPieces): ?>
        <div class="quiz-meta">
          <?= esc(implode(' · ', $metaPieces)) ?>
        </div>
      <?php endif; ?>
  


      <?php if (!empty($quiz->instructions)): ?>
        <small class="d-none d-md-inline" style="opacity:.9">
          <?= esc($quiz->instructions) ?>
        </small>
      <?php endif; ?>
    </div>

  <?php
  $totalQ = isset($totalQuestions) ? (int)$totalQuestions : count($qq ?? []);
?>
<div class="d-flex align-items-center flex-wrap justify-content-end" style="gap:.5rem">

  <?php if ($totalQ > 0): ?>
    <div class="pill pill-questions" id="quiz-question-counter"
         data-total="<?= $totalQ ?>">
      <i class="fas fa-list-ol"></i>
      <span id="quiz-q-remaining"><?= $totalQ - 1 ?></span> questions left
    </div>
  <?php endif; ?>

  <?php if (!empty($timeLimitSec) && (int)$timeLimitSec > 0): ?>
    <div id="quiz-timer"
         class="timer-shell timer-ok"
         data-remaining="<?= (int)$timeLimitSec ?>">
      <i class="fas fa-clock"></i>
      <span id="quiz-timer-minutes"></span>
    </div>
  <?php endif; ?>

  <span class="pill pill-autosave" title="Autosave status">
    <span class="autosave-dot" id="autosaveDot"></span>
    <i class="fas fa-cloud-upload-alt autosave-icon" id="autosaveIcon"></i>
    <span id="autosaveText"></span>
  </span>
</div>

  </div>
</section>

  <div class="mt-1 small">
    <?php if (!empty($quiz->negative_mark_per_q) && (float)$quiz->negative_mark_per_q > 0): ?>
      <span class="text-warning">
        Negative marking:
        <?= (float)$quiz->negative_mark_per_q ?> mark(s) deducted for each wrong answer.
      </span>
    <?php endif; ?>
  </div>
</section>

<section class="content">
  <form action="<?= base_url('student/quizzes/submit') ?>" method="post" id="attemptForm">
    <?= csrf_field() ?>
    <input type="hidden" name="attempt_id" value="<?= (int)$attemptId ?>">
    <input type="hidden" id="currentIndex" value="0">

    <div class="quiz-wrap">
      <?php
        $qNo = 1; $index = 0;
        foreach ($qq as $row):
          $qid   = (int)$row->question_id;
          $qt    = $row->question_type ?? 'mcq_single';
          $txt   = $row->question ?? 'Question text';
          $isQImg = !empty($row->question_image);
          $qImg   = $row->question_image ?? '';
          $imgName = $isQImg ? basename($qImg) : '';
      ?>

      <div class="q-card question-block mb-3 <?= $isQImg ? 'q-image-question' : '' ?>"
           data-index="<?= $index ?>"
           data-qid="<?= $qid ?>"
           id="qblock-<?= $qid ?>">

        <div class="q-header">
          <div class="q-bubble"><?= $qNo ?></div>

          <div class="q-text mb-0" style="width:100%">
            <?php if ($isQImg): ?>
              <div class="question-media">
                <img
                  src="<?= esc(base_url('media/qb/' . $imgName)) ?>"
                  alt="Question <?= (int)$qNo ?>"
                  class="question-img js-qimg-zoom"
                  loading="lazy"
                  data-full="<?= esc(base_url('media/qb/' . $imgName)) ?>">
                <div class="small text-muted">Tap image to zoom</div>
              </div>
            <?php else: ?>
              <?= nl2br(esc($txt)) ?>
            <?php endif; ?>
          </div>
        </div>

        <div class="q-body">

          <?php if (in_array($qt, ['mcq','mcq_single'])): ?>

            <?php
              if (!empty($quiz->shuffle_options) && (int)$quiz->shuffle_options === 1 && !empty($row->shuffled_options)) {
                  $optionsToShow = $row->shuffled_options;
              } else {
                  $optionsToShow = [
                      'A' => $row->option_a ?? '',
                      'B' => $row->option_b ?? '',
                      'C' => $row->option_c ?? '',
                      'D' => $row->option_d ?? '',
                  ];
              }

              // ✅ If question is image: 4 columns layout
              $wrapClass = $isQImg ? 'options-wrap options-4col' : 'options-wrap';
            ?>

            <div class="<?= $wrapClass ?>">
              <?php foreach ($optionsToShow as $letter => $label): ?>
                <?php if (trim($label) === '') continue; ?>
                <?php $id = 'q'.$qid.'_'.$letter; ?>

                <label class="option" for="<?= $id ?>">
                  <input type="radio"
                         class="answer-input"
                         id="<?= $id ?>"
                         name="ans_<?= $qid ?>"
                         data-qid="<?= $qid ?>"
                         data-type="mcq_single"
                         value="<?= esc($letter) ?>">
                  <span><?= esc($letter) ?>) <?= esc($label) ?></span>
                </label>
              <?php endforeach; ?>
            </div>

            <?php if (!empty($quiz->shuffle_options) && !empty($row->option_map)): ?>
              <?php foreach ($row->option_map as $newL => $origL): ?>
                <input type="hidden" name="optmap[<?= $qid ?>][<?= $newL ?>]" value="<?= $origL ?>">
              <?php endforeach; ?>
            <?php endif; ?>

          <?php elseif ($qt === 'true_false' || $qt === 'tf'): ?>

            <div class="options-wrap">
              <?php foreach (['True','False'] as $val): ?>
                <?php $id = 'q'.$qid.'_'.$val; ?>
                <label class="option" for="<?= $id ?>">
                  <input type="radio"
                         class="answer-input"
                         id="<?= $id ?>"
                         name="ans_<?= $qid ?>"
                         data-qid="<?= $qid ?>"
                         data-type="tf"
                         value="<?= esc($val) ?>">
                  <span><?= esc($val) ?></span>
                </label>
              <?php endforeach; ?>
            </div>

          <?php elseif ($qt === 'fill' || $qt === 'fill_blank'): ?>

            <input type="text" class="form-control answer-input"
                   name="ans_<?= $qid ?>"
                   data-qid="<?= $qid ?>"
                   data-type="fill"
                   placeholder="Type your answer">

          <?php elseif ($qt === 'short' || $qt === 'short_answer'): ?>

            <textarea class="form-control answer-input"
                      name="ans_<?= $qid ?>"
                      rows="3"
                      data-qid="<?= $qid ?>"
                      data-type="short"
                      placeholder="Type your answer"></textarea>

          <?php elseif ($qt === 'mcq_multi'): ?>

            <?php
              $optionsToShow = [];
              if (!empty($row->options_json)) {
                  $json = json_decode($row->options_json, true);
                  if (!empty($json['options']) && is_array($json['options'])) {
                      $optionsToShow = $json['options'];
                  }
              }
              if (empty($optionsToShow)) {
                  $optionsToShow = [
                      'A' => $row->option_a ?? '',
                      'B' => $row->option_b ?? '',
                      'C' => $row->option_c ?? '',
                      'D' => $row->option_d ?? '',
                  ];
              }
            ?>

            <div class="options-wrap">
              <?php foreach ($optionsToShow as $letter => $label): ?>
                <?php if (trim($label) === '') continue; ?>
                <?php $id = 'q'.$qid.'_'.$letter; ?>
                <label class="option" for="<?= $id ?>">
                  <input type="checkbox"
                         id="<?= $id ?>"
                         class="answer-input"
                         name="ans_<?= $qid ?>[]"
                         data-qid="<?= $qid ?>"
                         data-type="mcq_multi"
                         value="<?= esc($letter) ?>">
                  <span><?= esc($letter) ?>) <?= esc($label) ?></span>
                </label>
              <?php endforeach; ?>
            </div>

          <?php elseif ($qt === 'match'): ?>

            <?php
              $pairs = [];
              if (!empty($row->options_json)) {
                  $decoded = json_decode($row->options_json, true);
                  if (is_array($decoded)) $pairs = $decoded;
              }
              $isDrag = (int)($row->is_drag ?? 0) === 1;
            ?>

            <?php if (!empty($pairs)): ?>
              <?php if ($isDrag): ?>

                <?php
                  $leftItems = [];
                  $rightItems = [];
                  foreach ($pairs as $p) {
                      $leftItems[]  = $p['left'] ?? '';
                      $rightItems[] = $p['right'] ?? '';
                  }
                  $shuffledRight = $rightItems;
                  shuffle($shuffledRight);
                ?>

                

                <div class="match-dnd" data-qid="<?= $qid ?>">
                  <div class="row">
                    <div class="col-md-6">
                      <?php foreach ($leftItems as $left): ?>
                        <div class="match-row d-flex align-items-center" data-left="<?= esc($left) ?>">
                          <div class="match-left-label pe-2">
                            <strong><?= esc($left) ?></strong>
                          </div>
                          <div class="match-dropzone flex-fill">
                            <span class="text-muted small">Drop answer here</span>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <div class="col-md-6">
                      <p class="text-muted small mb-1">Drag to match:</p>
                      <div class="match-bank">
                        <?php foreach ($shuffledRight as $r): ?>
                          <div class="match-chip" draggable="true" data-value="<?= esc($r) ?>">
                            <?= esc($r) ?>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                  <button type="button"
                          class="btn btn-sm btn-outline-secondary mt-2 match-reset"
                          data-qid="<?= $qid ?>">Reset</button>
                </div>

                <input type="hidden" class="answer-input match-store"
                       name="ans_<?= $qid ?>"
                       data-qid="<?= $qid ?>" data-type="match">

              <?php else: ?>

                <div class="match-wrapper">
                  <?php foreach ($pairs as $p): ?>
                    <div class="match-row d-flex align-items-center mb-2">
                      <div style="width:45%;"><strong><?= esc($p['left']) ?></strong></div>
                      <div class="flex-fill">
                        <input type="text"
                               class="form-control answer-input match-input"
                               name="ans_<?= $qid ?>[<?= esc($p['left']) ?>]"
                               data-qid="<?= $qid ?>" data-type="match"
                               data-left="<?= esc($p['left']) ?>"
                               placeholder="Match for: <?= esc($p['left']) ?>">
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>

              <?php endif; ?>
            <?php else: ?>
              <div class="text-muted">Match pairs not configured.</div>
            <?php endif; ?>

          <?php else: ?>
            <div class="text-muted">Unsupported question type: <?= esc($qt) ?></div>
          <?php endif; ?>

        </div>
      </div>

      <?php $qNo++; $index++; endforeach; ?>
    </div>

    <div class="quiz-footer">
      <div class="container-fluid px-2">
        <div class="d-flex justify-content-center align-items-center" style="gap:.75rem">
          <button type="button" class="btn btn-light btn-pill btn-prev" id="btnPrev">← Previous</button>
          <button type="button" class="btn btn-next btn-pill" id="btnNext" data-mode="next">Next →</button>
        </div>
      </div>
    </div>

    <!-- Zoom Modal -->
    <div class="modal fade" id="imgZoomModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:16px;overflow:hidden">
          <div class="modal-body p-0" style="background:#000">
            <img id="zoomImg" src="" alt="Zoom" style="width:100%;height:auto;display:block">
          </div>
          <div class="modal-footer py-2">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

  </form>
</section>

<script>

  window.__savedAnswers = <?= json_encode($savedAnswers ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
/* -----------------------------------
   TIMER (mm:ss, more prominent)
----------------------------------- */
(function () {
  var timerEl = document.getElementById('quiz-timer');
  if (!timerEl) return;

  var minutesSpan = document.getElementById('quiz-timer-minutes');
  if (!minutesSpan) return;

  var attr = timerEl.getAttribute('data-remaining');
  var remaining = parseInt(attr, 10);
  if (isNaN(remaining) || remaining <= 0) {
    minutesSpan.textContent = '0:00';
    return;
  }

  var total = remaining;

  function renderTime(sec) {
    if (sec < 0) sec = 0;
    var m = Math.floor(sec / 60);
    var s = sec % 60;
    var sStr = (s < 10 ? '0' + s : '' + s);
    minutesSpan.textContent = m + ':' + sStr;
  }

  function updateColor() {
    timerEl.classList.remove('timer-ok','timer-warn','timer-danger');
    var ratio = remaining / total;
    if (ratio <= 0.25) timerEl.classList.add('timer-danger');
    else if (ratio <= 0.5) timerEl.classList.add('timer-warn');
    else timerEl.classList.add('timer-ok');
  }

  renderTime(remaining);
  updateColor();

  var intervalId = setInterval(function () {
    remaining--;
    if (remaining <= 0) {
      clearInterval(intervalId);
      renderTime(0);
      updateColor();
      var quizForm = document.getElementById('attemptForm');
      if (quizForm) quizForm.submit();
      return;
    }
    renderTime(remaining);
    updateColor();
  }, 1000);
})();

/* -----------------------------------
   REMAINING QUESTIONS COUNTER
   (based on current question index)
----------------------------------- */
(function() {
  const counter = document.getElementById('quiz-question-counter');
  if (!counter) {
    // no counter on this page
    window.quizUpdateRemaining = function(){};
    return;
  }

  const total = parseInt(counter.getAttribute('data-total') || '0', 10);
  const elRemaining = document.getElementById('quiz-q-remaining');
  if (!elRemaining || !total) {
    window.quizUpdateRemaining = function(){};
    return;
  }

  // currentIndex is 0-based
  // remaining = total - (currentIndex + 1)
  window.quizUpdateRemaining = function(currentIndex) {
    currentIndex = parseInt(currentIndex || 0, 10);
    if (currentIndex < 0) currentIndex = 0;
    if (currentIndex >= total) currentIndex = total - 1;

    const remaining = Math.max(total - (currentIndex + 1), 0);
    elRemaining.textContent = remaining;
  };

  // initial: at Q1 (index 0) → remaining = total - 1
  quizUpdateRemaining(0);
})();

/* -----------------------------------
   ONE-BY-ONE NAVIGATION + SUBMIT MODE
----------------------------------- */
const blocks = Array.from(document.querySelectorAll('.question-block'));
const currentIndexInput = document.getElementById('currentIndex');
const btnPrev  = document.getElementById('btnPrev');
const btnNext  = document.getElementById('btnNext');
const totalQuestions = blocks.length;

function setCurrent(idx){
  if (!currentIndexInput) return;

  idx = Math.max(0, Math.min(idx, totalQuestions - 1));
  currentIndexInput.value = idx;
  blocks.forEach(b => b.classList.remove('active'));
  if (blocks[idx]) blocks[idx].classList.add('active');

  if (btnPrev) {
    btnPrev.disabled = (idx === 0);
  }

  if (btnNext) {
    if (idx === totalQuestions - 1) {
      btnNext.textContent = 'Submit Quiz ✅';
      btnNext.classList.remove('btn-next');
      btnNext.classList.add('btn-submit');
      btnNext.setAttribute('data-mode', 'submit');
    } else {
      btnNext.textContent = 'Next →';
      btnNext.classList.remove('btn-submit');
      btnNext.classList.add('btn-next');
      btnNext.setAttribute('data-mode', 'next');
    }
  }

  // 🔥 update remaining questions based on current index
  if (typeof window.quizUpdateRemaining === 'function') {
    window.quizUpdateRemaining(idx);
  }
}

// initial first question
setCurrent(0);

if (btnPrev) {
  btnPrev.addEventListener('click', () => {
    setCurrent(parseInt(currentIndexInput.value, 10) - 1);
  });
}

if (btnNext) {
  btnNext.addEventListener('click', () => {
    const mode = btnNext.getAttribute('data-mode') || 'next';

    if (mode === 'next') {
      setCurrent(parseInt(currentIndexInput.value, 10) + 1);
      return;
    }

    // mode === 'submit' → submit immediately (even if some questions are blank)
    const form = document.getElementById('attemptForm');
    if (form) form.submit();
  });
}

/* -----------------------------------
   AUTOSAVE & CHOICE HIGHLIGHT
----------------------------------- */
const autosaveDot  = document.getElementById('autosaveDot');
const autosaveText = document.getElementById('autosaveText');
let saveT;

function setAutosaveState(state){
  if (!autosaveDot || !autosaveText) return;
  autosaveDot.className = 'autosave-dot';
  if(state==='pending'){
    autosaveDot.classList.add('autosave-pending');
    autosaveText.textContent='Saving...';
  }
  else if(state==='ok'){
    autosaveDot.classList.add('autosave-ok');
    autosaveText.textContent='Saved';
  }
  else{
    autosaveDot.classList.add('autosave-fail');
    autosaveText.textContent='Save failed';
  }
  if(saveT) clearTimeout(saveT);
  if(state!=='pending') saveT = setTimeout(()=>{
    autosaveText.textContent='';
    autosaveDot.className='autosave-dot';
  }, 1800);
}

document.querySelectorAll('.option input').forEach(inp=>{
  inp.addEventListener('change', ()=>{
    const wrap = inp.closest('.q-card');
    if (!wrap) return;
    if(inp.type==='checkbox'){
      wrap.querySelectorAll('.option input[type="checkbox"]').forEach(cb=>{
        cb.closest('.option').classList.toggle('checked', cb.checked);
      });
    } else {
      wrap.querySelectorAll('.option').forEach(o=>o.classList.remove('checked'));
      inp.closest('.option').classList.add('checked');
    }
  });
});

document.querySelectorAll('input.answer-input[type="text"], textarea.answer-input').forEach(el=>{
  el.addEventListener('keyup', debounce(()=>{}, 200));
});

/* -----------------------------------
   AUTOSAVE (with MATCH support)
----------------------------------- */
document.querySelectorAll('.answer-input').forEach(el => {
  el.addEventListener('change', saveAnswer);
  el.addEventListener('keyup', debounce(saveAnswer, 600));
});

/* -----------------------------------
   MATCH drag & drop + reset (desktop + mobile)
----------------------------------- */
function initMatchDragDrop() {
  const wrappers = document.querySelectorAll('.match-dnd');
  let activeTouchChip = null;

  wrappers.forEach(wrapper => {
    const qid       = wrapper.getAttribute('data-qid');
    const chips     = wrapper.querySelectorAll('.match-chip');
    const dropzones = wrapper.querySelectorAll('.match-dropzone');
    const bank      = wrapper.querySelector('.match-bank');
    const hidden    = document.querySelector('.match-store[data-qid="'+qid+'"]');
    const resetBtn  = wrapper.querySelector('.match-reset');

    let draggedChip = null;

    function placeholderHtml() {
      return '<span class="text-muted small">Drop answer here</span>';
    }

    function triggerHiddenAutosave() {
      if (!hidden) return;
      const ev = new Event('change', { bubbles: true });
      hidden.dispatchEvent(ev);
    }

    function resetMatches() {
      if (!bank) return;
      dropzones.forEach(zone => {
        const chip = zone.querySelector('.match-chip');
        if (chip) bank.appendChild(chip);
        zone.innerHTML = placeholderHtml();
        zone.classList.remove('filled','over');
      });
      activeTouchChip = null;
      chips.forEach(c => c.classList.remove('dragging'));
      triggerHiddenAutosave();
    }

    function handleDrop(targetZone, chip) {
      if (!chip || !targetZone) return;
      dropzones.forEach(z => {
        if (z !== targetZone && z.contains(chip)) {
          z.innerHTML = placeholderHtml();
          z.classList.remove('filled');
        }
      });
      const existingChip = targetZone.querySelector('.match-chip');
      if (existingChip && bank) bank.appendChild(existingChip);

      targetZone.innerHTML = '';
      targetZone.appendChild(chip);
      targetZone.classList.add('filled');

      chips.forEach(c => c.classList.remove('dragging'));
      activeTouchChip = null;
      triggerHiddenAutosave();
    }

    if (resetBtn) resetBtn.addEventListener('click', resetMatches);

    chips.forEach(chip => {
      chip.addEventListener('dragstart', e => {
        draggedChip = chip;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', chip.getAttribute('data-value') || chip.textContent);
        chip.classList.add('dragging');
      });

      chip.addEventListener('dragend', () => {
        if (draggedChip) {
          draggedChip.classList.remove('dragging');
          draggedChip = null;
        }
      });

      chip.addEventListener('touchstart', e => {
        e.preventDefault();
        activeTouchChip = chip;
        chips.forEach(c => c.classList.remove('dragging'));
        chip.classList.add('dragging');
      }, { passive:false });
    });

    dropzones.forEach(zone => {
      zone.addEventListener('dragover', e => {
        e.preventDefault();
        zone.classList.add('over');
      });
      zone.addEventListener('dragleave', () => zone.classList.remove('over'));
      zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('over');
        if (!draggedChip) return;
        handleDrop(zone, draggedChip);
      });

      zone.addEventListener('touchstart', e => {
        if (!activeTouchChip) return;
        e.preventDefault();
        handleDrop(zone, activeTouchChip);
      }, { passive:false });
    });
  });
}
initMatchDragDrop();


/* -----------------------------------
   SAVE ANSWER
----------------------------------- */
function saveAnswer(e){
  
  const el        = e.target;
  const qid       = el.getAttribute('data-qid');
  const type      = el.getAttribute('data-type'); // This is the question_type
  const attemptId = <?= (int)$attemptId ?>;

  const form = new URLSearchParams();
  form.append('<?= csrf_token() ?>','<?= csrf_hash() ?>');
  form.append('attempt_id', attemptId);
  form.append('question_id', qid);
  form.append('question_type', type); // 🔥 ADD THIS LINE

  if (type === 'mcq_single' || type === 'tf') {
    const checked = document.querySelector(`input[name="ans_${qid}"]:checked`);
    if (!checked) return;
    if (type === 'mcq_single') form.append('selected_option', checked.value);
    else form.append('answer_text', checked.value);

  } else if (type === 'fill' || type === 'short') {
    form.append('answer_text', el.value);

  } else if (type === 'mcq_multi') {
    const vals = [];
    ['A','B','C','D'].forEach(L=>{
      const cb = document.querySelector(`#q${qid}_`+L);
      if (cb && cb.checked) vals.push(cb.value);
    });
    vals.forEach(v => form.append('selected_options[]', v));

  } else if (type === 'match') {
    const dataArr = [];
    const wrapperDnd = document.querySelector('.match-dnd[data-qid="'+qid+'"]');
    if (wrapperDnd) {
      wrapperDnd.querySelectorAll('.match-row').forEach(row => {
        const left = (row.getAttribute('data-left') || '').trim();
        let val = '';
        const chip = row.querySelector('.match-dropzone .match-chip');
        if (chip) {
          val = (chip.getAttribute('data-value') || chip.textContent || '').trim();
        }
        dataArr.push({ left: left, value: val });
      });
    } else {
      document.querySelectorAll('.match-input[data-qid="'+qid+'"]').forEach(inp => {
        const left = (inp.getAttribute('data-left') || '').trim();
        const val  = (inp.value || '').trim();
        dataArr.push({ left: left, value: val });
      });
    }
    form.append('answer_text', JSON.stringify(dataArr));
  }

  setAutosaveState('pending');
  fetch('<?= base_url('student/quizzes/save-answer') ?>', {
    method: 'POST',
    headers: {
      'X-Requested-With':'XMLHttpRequest',
      'Content-Type':'application/x-www-form-urlencoded'
    },
    body: form.toString(),
  })
    .then(r=>r.ok ? setAutosaveState('ok') : setAutosaveState('fail'))
    .catch(()=> setAutosaveState('fail'));
}


function debounce(fn, delay){
  let t; return function(...args){ clearTimeout(t); t=setTimeout(()=>fn.apply(this,args),delay); };
}

document.addEventListener('click', function(e){
  const img = e.target.closest('.question-img');
  if(!img) return;

  const zoom = document.getElementById('zoomImg');
  if(zoom) zoom.src = img.getAttribute('src') || '';

  if (window.jQuery && jQuery.fn.modal) {
    $('#imgZoomModal').modal('show');
  }
});

// ✅ Always select radio/checkbox correctly (dot will show)
document.addEventListener('click', function (e) {
  const opt = e.target.closest('label.option');
  if (!opt) return;

  const input = opt.querySelector('input[type="radio"], input[type="checkbox"]');
  if (!input) return;

  // If user clicked the input itself, let browser handle it
  if (e.target === input) return;

  // Trigger native click so UI (radio dot) is always correct
  input.click();
});


// ✅ Image zoom modal
document.addEventListener('click', function(e){
  const img = e.target.closest('.js-qimg-zoom');
  if (!img) return;

  const full = img.getAttribute('data-full') || img.src;
  const zoomImg = document.getElementById('zoomImg');
  if (zoomImg) zoomImg.src = full;

  if (window.jQuery && $('#imgZoomModal').length) {
    $('#imgZoomModal').modal('show');
  }
});

/* -----------------------------------
   PRE-FILL SAVED ANSWERS ON PAGE LOAD
----------------------------------- */
(function() {
    const savedAnswers = window.__savedAnswers || {};
    
    console.log('=== DEBUG: Pre-fill saved answers ===');
    console.log('Total saved answers:', Object.keys(savedAnswers).length);
    console.log('Saved answers data:', savedAnswers);
    
    if (Object.keys(savedAnswers).length === 0) {
        console.log('No saved answers found. Either no answers were saved or they are not loading from server.');
        return;
    }
    
    // Get all question IDs in the current view
    const allQuestionBlocks = document.querySelectorAll('.question-block');
    const questionIdsInView = [];
    allQuestionBlocks.forEach(block => {
        questionIdsInView.push(block.getAttribute('data-qid'));
    });
    
    console.log('Question IDs in view:', questionIdsInView);
    console.log('Question count in view:', questionIdsInView.length);
    
    Object.keys(savedAnswers).forEach(qid => {
        const answer = savedAnswers[qid];
        if (!answer) return;
        
        const qidNum = parseInt(qid);
        const type = answer.type || '';
        
        console.log(`\nProcessing QID ${qid}, type: ${type}`, answer);
        
        // Check if this question exists in the view
        if (!questionIdsInView.includes(qid)) {
            console.log(`❌ QID ${qid} not found in current view!`);
            return;
        }
        
        // For single choice (radio)
        if (type === 'mcq_single' || type === 'tf') {
            const value = answer.selected_option || answer.answer_text || '';
            console.log(`Looking for radio: name="ans_${qid}", value="${value}"`);
            
            if (value) {
                // Try multiple selector patterns
                let radio = document.querySelector(`input[name="ans_${qid}"][value="${value}"]`);
                
                // If not found, try case-insensitive
                if (!radio) {
                    const allRadios = document.querySelectorAll(`input[name="ans_${qid}"]`);
                    console.log(`Found ${allRadios.length} radios for QID ${qid}`);
                    
                    allRadios.forEach(r => {
                        if (r.value.toLowerCase() === value.toLowerCase()) {
                            radio = r;
                        }
                    });
                }
                
                if (radio) {
                    console.log(`✓ Found radio, setting checked:`, radio);
                    radio.checked = true;
                    
                    // Trigger the change event to update UI
                    setTimeout(() => {
                        const changeEvent = new Event('change', { bubbles: true });
                        radio.dispatchEvent(changeEvent);
                        
                        // Add visual indicator
                        const option = radio.closest('.option');
                        if (option) {
                            option.classList.add('has-answer');
                            console.log(`✓ Added has-answer class`);
                        }
                    }, 100);
                } else {
                    console.log(`✗ Radio not found for QID ${qid}, value "${value}"`);
                    console.log(`Available radios for QID ${qid}:`);
                    document.querySelectorAll(`input[name="ans_${qid}"]`).forEach((r, i) => {
                        console.log(`  ${i+1}. value="${r.value}"`);
                    });
                }
            }
        }
        // For multi-choice (checkbox)
        else if (type === 'mcq_multi') {
            const selected = answer.selected_options || [];
            console.log(`Processing multi-select for QID ${qid}:`, selected);
            
            selected.forEach(letter => {
                console.log(`Looking for checkbox: name="ans_${qid}[]", value="${letter}"`);
                
                // Try multiple selector patterns
                let checkbox = document.querySelector(`input[name="ans_${qid}[]"][value="${letter}"]`);
                
                if (!checkbox) {
                    checkbox = document.querySelector(`#q${qid}_${letter}`);
                }
                
                if (checkbox) {
                    console.log(`✓ Found checkbox, setting checked:`, checkbox);
                    checkbox.checked = true;
                    
                    setTimeout(() => {
                        const changeEvent = new Event('change', { bubbles: true });
                        checkbox.dispatchEvent(changeEvent);
                        
                        const option = checkbox.closest('.option');
                        if (option) {
                            option.classList.add('has-answer');
                        }
                    }, 100);
                } else {
                    console.log(`✗ Checkbox not found for QID ${qid}, letter "${letter}"`);
                }
            });
        }
        // For fill/short answer
        else if (type === 'fill' || type === 'short') {
            const text = answer.answer_text || '';
            console.log(`Processing text answer for QID ${qid}: "${text}"`);
            
            if (text) {
                // Try input first
                let input = document.querySelector(`input[name="ans_${qid}"]`);
                if (!input) {
                    // Try textarea
                    input = document.querySelector(`textarea[name="ans_${qid}"]`);
                }
                
                if (input) {
                    console.log(`✓ Found input, setting value:`, input);
                    input.value = text;
                    
                    setTimeout(() => {
                        const changeEvent = new Event('change', { bubbles: true });
                        input.dispatchEvent(changeEvent);
                        
                        input.classList.add('has-answer');
                    }, 100);
                } else {
                    console.log(`✗ Input/textarea not found for QID ${qid}`);
                }
            }
        }
        // For match questions
        else if (type === 'match') {
            try {
                const data = JSON.parse(answer.answer_text || '[]');
                console.log(`Processing match for QID ${qid}:`, data);
                
                const wrapperDnd = document.querySelector(`.match-dnd[data-qid="${qid}"]`);
                
                if (wrapperDnd && Array.isArray(data)) {
                    // For drag & drop match questions
                    data.forEach(item => {
                        const left = item.left || '';
                        const value = item.value || '';
                        
                        if (left && value) {
                            // Find the row with this left value
                            const row = wrapperDnd.querySelector(`.match-row[data-left="${left}"]`);
                            if (row) {
                                const dropzone = row.querySelector('.match-dropzone');
                                // Find chip by data-value attribute
                                let chip = wrapperDnd.querySelector(`.match-chip[data-value="${value}"]`);
                                
                                // If not found by data-value, search by text content
                                if (!chip) {
                                    const allChips = wrapperDnd.querySelectorAll('.match-chip');
                                    allChips.forEach(c => {
                                        if (c.textContent.trim() === value) {
                                            chip = c;
                                        }
                                    });
                                }
                                
                                if (dropzone && chip) {
                                    console.log(`✓ Setting match: ${left} -> ${value}`);
                                    
                                    // Remove from bank if it's there
                                    const bank = wrapperDnd.querySelector('.match-bank');
                                    if (bank && bank.contains(chip)) {
                                        bank.removeChild(chip);
                                    }
                                    
                                    // Clear any existing chip in dropzone
                                    const existingChip = dropzone.querySelector('.match-chip');
                                    if (existingChip && bank) {
                                        bank.appendChild(existingChip);
                                    }
                                    
                                    // Add the chip
                                    dropzone.innerHTML = '';
                                    dropzone.appendChild(chip);
                                    dropzone.classList.add('filled', 'has-answer');
                                }
                            }
                        }
                    });
                } else {
                    // For text input match questions
                    data.forEach(item => {
                        const left = item.left || '';
                        const value = item.value || '';
                        
                        if (left) {
                            // Try to find input with exact data-left attribute
                            let input = document.querySelector(`.match-input[data-qid="${qid}"][data-left="${left}"]`);
                            
                            if (!input) {
                                // Try to find by name pattern
                                input = document.querySelector(`input[name="ans_${qid}[${left}]"]`);
                            }
                            
                            if (input && value) {
                                console.log(`✓ Setting match input: ${left} -> ${value}`);
                                input.value = value;
                                
                                setTimeout(() => {
                                    const changeEvent = new Event('change', { bubbles: true });
                                    input.dispatchEvent(changeEvent);
                                    
                                    input.classList.add('has-answer');
                                }, 100);
                            }
                        }
                    });
                }
            } catch (e) {
                console.error('Error parsing match answer:', e);
            }
        }
    });
    
    console.log('=== Pre-fill completed ===');
    
    // Trigger autosave state update after pre-filling
    setTimeout(() => {
        setAutosaveState('ok');
    }, 500);
})();
</script>

<?= $this->endSection() ?>
