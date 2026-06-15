<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>
<style>
  :root{
    --bg:#f7fbff;
    --card:#ffffff;
    --brand:#6759ff;
    --brand2:#00c4ff;
    --ok:#1cc88a;
    --warn:#f6c23e;
    --danger:#e74a3b;
    --ink:#223;
  }

  body{ background:var(--bg); }

  /* =========================
     TOP BAR
  ========================= */
  .quiz-topbar{
    position:sticky; top:0; z-index:1030;
    background:linear-gradient(90deg,var(--brand) 0%,var(--brand2) 100%);
    padding:.7rem 1rem .5rem;
    color:#fff;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
  }
  .quiz-title{ font-weight:800; margin:0; font-size:1.15rem; letter-spacing:.3px; }
  .quiz-meta{ opacity:.92; font-size:.82rem; }

  .pill{
    border-radius:999px;
    padding:.25rem .6rem;
    display:inline-flex;
    align-items:center;
    gap:.35rem;
    background:rgba(0,0,0,.18);
    font-size:.8rem;
    border:0;
  }

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
  .timer-shell i{ font-size:1rem; }
  .timer-shell span{ font-size:1rem; }
  .timer-ok{ color:#e8fff4; }
  .timer-warn{ color:#fff7df; }
  .timer-danger{ color:#ffe5e3; }

  /* =========================
     LAYOUT + BASE CARD
  ========================= */
  .quiz-wrap{
    max-width:980px;
    margin:12px auto 80px;
    padding:0 12px;
    min-height:calc(100vh - 170px);
  }
  .question-block{ display:none; }
  .question-block.active{ display:block; }

  .q-card{
    background:var(--card);
    border:0;
    border-radius:22px;
    overflow:hidden;
    box-shadow:0 8px 24px rgba(103,89,255,.12);
    min-height:260px;
  }

  .q-header{
    background:linear-gradient(90deg, rgba(103,89,255,.12) 0%, rgba(0,196,255,.12) 100%);
    padding:14px 18px;
    display:flex;
    align-items:center;
    gap:10px;
    position:relative;
    z-index:1;
  }
  .q-bubble{
    width:36px; height:36px; border-radius:50%;
    background:linear-gradient(135deg,var(--brand),var(--brand2));
    color:#fff;
    display:flex; align-items:center; justify-content:center;
    font-weight:800;
    box-shadow:0 4px 10px rgba(0,0,0,.08);
    flex:0 0 auto;
  }
  .q-text{
    font-size:1.08rem;
    line-height:1.6;
    color:var(--ink);
    margin:0;
    width:100%;
    position:relative;
    z-index:1;
  }

  .q-body{
    padding:16px 18px 12px;
    position:relative;
    z-index:10;
  }

  /* =========================
     OPTIONS (NORMAL MODE)
  ========================= */
  .options-wrap{ display:block; }

  .option{
    border:2px solid transparent;
    border-radius:16px;
    padding:.7rem .9rem;
    margin-bottom:.6rem;
    cursor:pointer;
    background:#f9fbff;
    display:flex;
    align-items:center;
    gap:.55rem;
    transition:transform .06s ease, box-shadow .2s ease, background .2s ease, border-color .2s ease;
    position:relative;
    user-select:none;
    z-index:5;
  }
  .option:hover{ transform:translateY(-1px); box-shadow:0 6px 14px rgba(0,0,0,.06); }
  .option input{ margin-top:2px; }
  .option span{ font-size:1rem; }

  .option.checked{
    border-color:var(--brand);
    background:linear-gradient(0deg, rgba(103,89,255,.10), rgba(103,89,255,.10));
    box-shadow:0 6px 16px rgba(103,89,255,.18);
  }

 
  /* =========================
     AUTOSAVE
  ========================= */
  .autosave-dot{
    width:9px; height:9px; border-radius:50%;
    display:inline-block;
    background:#6c757d;
  }
  .autosave-ok{ background:var(--ok); }
  .autosave-pending{ background:var(--warn); }
  .autosave-fail{ background:var(--danger); }
  .autosave-icon{ font-size:.9rem; }
  #autosaveText{ display:none; }

  /* =========================
     MATCH (DRAG/DROP)
  ========================= */
  .match-wrapper{ margin-top:.5rem; }
  .match-row{ margin-bottom:.5rem; }
  .match-left-label{ width:45%; }
  .match-dnd .match-row{ margin-bottom:8px; }

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
    border-radius:999px;
    border:1px solid #d1d5db;
    background:#fff;
    cursor:grab;
    font-size:.9rem;
    box-shadow:0 1px 3px rgba(0,0,0,.05);
  }
  .match-chip.dragging{
    opacity:.7;
    box-shadow:0 4px 10px rgba(0,0,0,.12);
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
    font-size:.9rem;
  }
  .match-dropzone.over{
    background:#e0f2fe;
    border-color:#60a5fa;
  }
  .match-dropzone .match-chip{ margin:0; }

  /* =========================
     QUESTION IMAGE + IMAGE QUESTIONS
  ========================= */
  .question-media{ width:100%; }
  .question-img{
    width:100%;
    max-height:420px;
    object-fit:contain;
    display:block;
    border-radius:14px;
    background:#fff;
    border:1px solid rgba(0,0,0,.06);
    cursor:zoom-in;
  }

  /* Image questions: options in 4 columns */
  .q-image-question{ overflow:hidden; }
  .q-image-question .question-media{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:6px;
  }
  .q-image-question .question-img{
    max-width:100%;
    max-height:260px;
    width:auto;
    height:auto;
    object-fit:contain;
    margin:0 auto;
    border-radius:12px;
  }
  .q-image-question .q-header{ align-items:flex-start; padding-bottom:8px; }
  .q-image-question .q-body{ padding-top:10px; }

  .options-wrap.options-4col{
    display:grid;
    grid-template-columns:repeat(4, minmax(0, 1fr));
    gap:10px;
  }
  @media (max-width: 992px){
    .options-wrap.options-4col{ grid-template-columns:repeat(2, minmax(0, 1fr)); }
  }
  @media (max-width: 576px){
    .options-wrap.options-4col{ grid-template-columns:1fr; }
  }

  /* =========================
     KIDS MODE (CLEAN)
     Uses your existing HTML classes:
     .kids-wrap .kids-card .kids-header .kids-body
     .kids-word (with spans kids-ch)
     .kids-options-grid .kids-option .kids-opt-text
  ========================= */
 /* =========================
   KIDS MODE (CLEAN)
========================= */

.quiz-wrap.kids-wrap{
  max-width:1100px;
  margin:10px auto 40px;
  padding:0 10px;
  min-height:calc(100vh - 150px);
}

.question-block{display:none}
.question-block.active{display:block}

/* Full-screen feel */
.q-card.kids-card{
  border-radius:26px;
  overflow:hidden;
  box-shadow:0 14px 34px rgba(0,0,0,.10);
  min-height:calc(100vh - 210px);
  display:flex;
  flex-direction:column;
}

/* Header */
.kids-header{
  padding:18px 18px 14px;
  background:linear-gradient(90deg,
    rgba(255,193,7,.22),
    rgba(0,196,255,.18),
    rgba(103,89,255,.18)
  );
  display:flex;
  gap:12px;
  align-items:flex-start;
}
.kids-bubble{
  width:44px;height:44px;border-radius:50%;
  background:linear-gradient(135deg,#6759ff,#00c4ff);
  color:#fff;display:flex;align-items:center;justify-content:center;
  font-weight:900;
  box-shadow:0 6px 14px rgba(0,0,0,.10);
}

/* Question box (characters become colored spans via JS) */
.kids-word{
  width:100%;
  background:rgba(255,255,255,.75);
  border-radius:22px;
  padding:22px 16px;
  box-shadow:inset 0 0 0 2px rgba(103,89,255,.10);
  display:flex;
  justify-content:center;
  align-items:center;
  gap:12px;
  flex-wrap:wrap;
  user-select:none;
  font-family:"Comic Sans MS","Fredoka","Baloo 2",system-ui,sans-serif;
}
.kids-ch{
  font-size:clamp(58px, 7vw, 110px);
  font-weight:900;
  line-height:1;
  padding:10px 14px;
  border-radius:18px;
  background:rgba(255,255,255,.90);
  box-shadow:0 8px 18px rgba(0,0,0,.06);
}
.kids-ch:nth-child(1n){color:#1d4ed8}
.kids-ch:nth-child(2n){color:#db2777}
.kids-ch:nth-child(3n){color:#16a34a}
.kids-ch:nth-child(4n){color:#f59e0b}
.kids-ch:nth-child(5n){color:#7c3aed}

.kids-blank{
  min-width:90px;
  color:#111827 !important;
  background:rgba(17,24,39,.06) !important;
}
.kids-blank.filled{
  background:rgba(28,200,138,.14) !important;
  box-shadow:0 10px 22px rgba(28,200,138,.15);
}

/* Body pushes options to bottom */
.kids-body{
  flex:1;
  display:flex;
  flex-direction:column;
  justify-content:flex-end;
  padding:18px 18px 22px;
}

/* Options grid (always 4 options) */
.kids-options-grid{
  width:100%;
  display:grid;
  grid-template-columns:repeat(4, minmax(0,1fr));
  gap:16px;
  margin-top:16px;
}
@media (max-width:992px){
  .kids-options-grid{grid-template-columns:repeat(2, minmax(0,1fr))}
}

/* Option card */
.kids-option{
  position:relative;
  border-radius:24px;
  min-height:120px;
  padding:22px 14px;
  display:flex;
  align-items:center;
  justify-content:center;
  border:4px solid transparent;
  cursor:pointer;
  user-select:none;
  box-shadow:0 10px 24px rgba(0,0,0,.08);
  transition:transform .08s ease, box-shadow .2s ease, border-color .2s ease;
}
.kids-option:hover{transform:scale(1.03)}
.kids-option:active{transform:scale(.99)}

/* Hide real radio */
.kids-option input{
  position:absolute;
  opacity:0;
  pointer-events:none;
}

/* Big letter */
.kids-opt-text{
  font-family:"Comic Sans MS","Fredoka","Baloo 2",system-ui,sans-serif;
  font-size:clamp(44px, 5vw, 76px);
  font-weight:900;
  line-height:1;
  color:#111827;
}

/* colorful backgrounds */
.kids-option:nth-child(1){background:#fff7ed}
.kids-option:nth-child(2){background:#ecfeff}
.kids-option:nth-child(3){background:#f0fdf4}
.kids-option:nth-child(4){background:#fdf4ff}

/* correct / wrong marks */
.kids-option.correct{
  border-color:#1cc88a;
  box-shadow:0 16px 34px rgba(28,200,138,.22);
}
.kids-option.wrong{
  border-color:#e74a3b;
  box-shadow:0 16px 34px rgba(231,74,59,.18);
}
.kids-option.correct::after,
.kids-option.wrong::after{
  position:absolute;
  top:12px; right:14px;
  width:44px;height:44px;
  border-radius:50%;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:26px;
  font-weight:900;
  color:#fff;
}
.kids-option.correct::after{content:"✓"; background:#1cc88a;}
.kids-option.wrong::after{content:"✗"; background:#e74a3b;}

.kids-disabled .kids-option{
  pointer-events:none;
  opacity:.92;
}

label.kids-option.option .kids-opt-text{
  font-size: clamp(44px, 5vw, 76px) !important;
  font-weight: 900 !important;
  line-height: 1 !important;
  display: block;
  text-align: center;
}

/* Optional: make option cards a bit taller */
label.kids-option.option{
  min-height: 140px;
}

.question-block{ display:none !important; }
.question-block.active{ display:block !important; }
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
<div class="quiz-wrap kids-wrap">
  <?php
    $qNo = 1; $index = 0;
    foreach ($qq as $row):
      $qid   = (int)$row->question_id;
      $qt    = $row->question_type ?? 'mcq_single';
      $txt   = trim((string)($row->question ?? ''));
      $isQImg = !empty($row->question_image);
      $qImg   = $row->question_image ?? '';
      $imgName = $isQImg ? basename($qImg) : '';

      // For kids CVC like "c_t" => treat it as "word prompt"
      $isKidsWordPrompt = (!$isQImg && $txt !== '' && mb_strlen($txt) <= 10);
  ?>
<?php
  // use whatever field you have in your query/result:
  // commonly: $row->correct_option (A/B/C/D)
  $correct = strtoupper(trim((string)($row->correct_option ?? $row->correct_answer ?? '')));
?>
 <div class="q-card question-block mb-3 <?= $isQImg ? 'q-image-question' : '' ?> kids-card"
     data-index="<?= $index ?>"
     data-qid="<?= $qid ?>"
     data-correct="<?= esc($correct) ?>"
     id="qblock-<?= $qid ?>">

    <div class="q-header kids-header">
      <div class="q-bubble kids-bubble"><?= $qNo ?></div>

      <div class="q-text mb-0 kids-qtext" style="width:100%">

        <?php if ($isQImg): ?>
          <div class="question-media kids-media">
            <img
              src="<?= esc(base_url('media/qb/' . $imgName)) ?>"
              alt="Question <?= (int)$qNo ?>"
              class="question-img js-qimg-zoom kids-qimg"
              loading="lazy"
              data-full="<?= esc(base_url('media/qb/' . $imgName)) ?>">
            <div class="small text-muted">Tap image to zoom</div>
          </div>

        <?php else: ?>
          <?php if ($isKidsWordPrompt): ?>
           <div class="kids-word" data-kids-word="1">
  <?php
    $chars = preg_split('//u', $txt, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($chars as $c):
      $safe = esc($c);
      if ($c === '_' || $c === '—' || $c === '-') {
        echo '<span class="kids-ch kids-blank">'.$safe.'</span>';
      } else {
        echo '<span class="kids-ch">'.$safe.'</span>';
      }
    endforeach;
  ?>
</div>

          <?php else: ?>
            <div class="kids-sentence">
              <?= nl2br(esc($txt)) ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>

      </div>
    </div>

    <div class="q-body kids-body">

      <?php if (in_array($qt, ['mcq','mcq_single'], true)): ?>

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

          // kids mode: always show 4 columns (responsive fallbacks in CSS)
          $wrapClass = 'kids-options options-4col';
        ?>

       <?php
  // Build options list from whatever is filled (no DB changes)
  $opts = [];
  foreach ($optionsToShow as $letter => $label) {
    $label = trim((string)$label);
    if ($label !== '') $opts[$letter] = $label;
  }

  // count for responsive grid (3 options etc.)
  $optCount = count($opts);

  // data attribute used by CSS for mobile 3-column
  $gridClass = 'kids-options-grid';
?>

<div class="kids-options-grid">
  <?php $i=0; foreach ($opts as $letter => $label): ?>
    <?php $id = 'q'.$qid.'_'.$letter; ?>

   <label class="kids-option option" for="<?= $id ?>">
  <input type="radio"
         class="answer-input"
         id="<?= $id ?>"
         name="ans_<?= $qid ?>"
         data-qid="<?= $qid ?>"
         data-type="mcq_single"
         value="<?= esc($letter) ?>">
  <span class="kids-opt-text"><?= esc($label) ?></span>
</label>


  <?php $i++; endforeach; ?>
</div>

        <?php if (!empty($quiz->shuffle_options) && !empty($row->option_map)): ?>
          <?php foreach ($row->option_map as $newL => $origL): ?>
            <input type="hidden" name="optmap[<?= (int)$qid ?>][<?= esc($newL) ?>]" value="<?= esc($origL) ?>">
          <?php endforeach; ?>
        <?php endif; ?>

      <?php elseif ($qt === 'true_false' || $qt === 'tf'): ?>

        <div class="kids-options">
          <?php foreach (['True','False'] as $val): ?>
            <?php $id = 'q'.$qid.'_'.$val; ?>
            <label class="kids-option option" for="<?= $id ?>">
              <input type="radio"
                     class="answer-input kids-answer"
                     id="<?= $id ?>"
                     name="ans_<?= $qid ?>"
                     data-qid="<?= $qid ?>"
                     data-type="tf"
                     value="<?= esc($val) ?>">
              <span class="kids-opt-text"><?= esc($val) ?></span>
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
/* =========================================================
   READY-TO-PASTE JS (NO NEXT/PREV)
   - Shows 1 question at a time
   - On option click (mcq_single kids): fill blank, ✓/✗, autosave, after 5s next
   - Keeps your TIMER + COUNTER + AUTOSAVE + MATCH + saveAnswer
   IMPORTANT: Remove any other duplicated blocks/setCurrent code from page.
========================================================= */

/* -----------------------------------
   TIMER (mm:ss)
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
----------------------------------- */
(function() {
  const counter = document.getElementById('quiz-question-counter');
  if (!counter) {
    window.quizUpdateRemaining = function(){};
    return;
  }

  const total = parseInt(counter.getAttribute('data-total') || '0', 10);
  const elRemaining = document.getElementById('quiz-q-remaining');
  if (!elRemaining || !total) {
    window.quizUpdateRemaining = function(){};
    return;
  }

  window.quizUpdateRemaining = function(currentIndex) {
    currentIndex = parseInt(currentIndex || 0, 10);
    if (currentIndex < 0) currentIndex = 0;
    if (currentIndex >= total) currentIndex = total - 1;

    const remaining = Math.max(total - (currentIndex + 1), 0);
    elRemaining.textContent = remaining;
  };

  window.quizUpdateRemaining(0);
})();

/* -----------------------------------
   AUTOSAVE UI
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

function debounce(fn, delay){
  let t; return function(...args){ clearTimeout(t); t=setTimeout(()=>fn.apply(this,args),delay); };
}

/* -----------------------------------
   Highlight selected option (normal + kids)
----------------------------------- */
document.addEventListener('change', function(e){
  const inp = e.target;
  if (!inp.matches('.option input, input.answer-input')) return;

  const wrap = inp.closest('.q-card');
  if (!wrap) return;

  if (inp.type === 'checkbox') {
    wrap.querySelectorAll('.option input[type="checkbox"]').forEach(cb=>{
      cb.closest('.option')?.classList.toggle('checked', cb.checked);
    });
  } else if (inp.type === 'radio') {
    wrap.querySelectorAll('.option').forEach(o=>o.classList.remove('checked'));
    inp.closest('.option')?.classList.add('checked');
  }
});

/* -----------------------------------
   AUTOSAVE HOOKS
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
   SAVE ANSWER (your endpoint)
----------------------------------- */
function saveAnswer(e){
  const el        = e.target;
  const qid       = el.getAttribute('data-qid');
  const type      = el.getAttribute('data-type');
  const attemptId = <?= (int)$attemptId ?>;

  const form = new URLSearchParams();
  form.append('<?= csrf_token() ?>','<?= csrf_hash() ?>');
  form.append('attempt_id', attemptId);
  form.append('question_id', qid);

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
        if (chip) val = (chip.getAttribute('data-value') || chip.textContent || '').trim();
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

/* =========================================================
   ONE QUESTION AT A TIME + KIDS AUTO FLOW
========================================================= */
const blocks = Array.from(document.querySelectorAll('.question-block'));
let currentIndex = 0;

function setCurrent(idx){
  if (!blocks.length) return;

  idx = Math.max(0, Math.min(idx, blocks.length - 1));
  currentIndex = idx;

  blocks.forEach(b => b.classList.remove('active'));
  blocks[idx].classList.add('active');

  const currentIndexInput = document.getElementById('currentIndex');
  if (currentIndexInput) currentIndexInput.value = idx;

  if (typeof window.quizUpdateRemaining === 'function') {
    window.quizUpdateRemaining(idx);
  }
}

// init: show only first question
setCurrent(0);

// Fill blank helper (works with your generated spans)
function fillBlank(block, letterText){
  const blank =
    block.querySelector('.kids-ch.kids-blank[data-role="blank"]') ||
    block.querySelector('.kids-ch.kids-blank');
  if (!blank) return;
  blank.textContent = (letterText || '').trim();
  blank.classList.add('filled');
}

function lockBlock(block){ block.classList.add('kids-disabled'); }
function unlockBlock(block){
  block.classList.remove('kids-disabled');
  block.querySelectorAll('.kids-option').forEach(o => o.classList.remove('correct','wrong'));
}

let kidsBusy = false;

document.addEventListener('click', function(e){
  const opt = e.target.closest('label.kids-option');
  if (!opt) return;

  const block = opt.closest('.question-block');
  if (!block) return;

  const input = opt.querySelector('input[type="radio"].answer-input');
  if (!input) return;

  // Only for mcq_single auto-next behavior
  if ((input.dataset.type || '') !== 'mcq_single') return;

  if (kidsBusy) return;
  kidsBusy = true;

  // select
  input.checked = true;

  // clear old marks
  block.querySelectorAll('.kids-option').forEach(o => o.classList.remove('correct','wrong'));

  // fill blank with visible option text
  const shown = (opt.querySelector('.kids-opt-text')?.textContent || '').trim();
  fillBlank(block, shown);

  // correctness (data-correct is on your question wrapper)
  const correct = (block.getAttribute('data-correct') || '').toUpperCase().trim();
  const selected = (input.value || '').toUpperCase().trim();

  if (correct && selected === correct) opt.classList.add('correct');
  else opt.classList.add('wrong');

  // autosave
  input.dispatchEvent(new Event('change', { bubbles:true }));

  lockBlock(block);

  setTimeout(function(){
    // last -> submit
    if (currentIndex >= blocks.length - 1) {
      const form = document.getElementById('attemptForm');
      if (form) form.submit();
      return;
    }

    unlockBlock(block);
    setCurrent(currentIndex + 1);
    kidsBusy = false;
  }, 2000);
});
</script>




<?= $this->endSection() ?>
