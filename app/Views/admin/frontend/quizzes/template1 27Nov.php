<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<style>
  /* ===== Theme (Playful / Gamey) ===== */
  :root{
    --bg: #f7fbff;
    --card: #ffffff;
    --brand: #6759ff;         /* primary (purple/indigo) */
    --brand-2:#00c4ff;        /* accent (cyan/sky) */
    --ok: #1cc88a;
    --warn: #f6c23e;
    --danger:#e74a3b;
    --ink:#223;
  }
  body{background:var(--bg)}

  /* Fun header ribbon */
  .quiz-topbar{
    position:sticky;top:0;z-index:1030;
    background: linear-gradient(90deg, var(--brand) 0%, var(--brand-2) 100%);
    padding:.8rem 1rem;color:#fff;box-shadow:0 2px 10px rgba(0,0,0,.08)
  }
  .quiz-title{font-weight:800;margin:0;font-size:1.15rem;letter-spacing:.3px}
  .pill{
    background:rgba(255,255,255,.15); border:0; border-radius:999px;
    padding:.35rem .75rem; font-weight:700; color:#fff;
  }
  .timer-pill{font-weight:800}
  .timer-ok{color:#e8fff4}.timer-warn{color:#fff7df}.timer-danger{color:#ffe5e3}

  /* Layout */
  .quiz-wrap{max-width:980px;margin:16px auto 80px; padding:0 12px}
  .question-block{display:none}
  .question-block.active{display:block}

  /* Card */
  .q-card{
    background:var(--card); border:0;
    border-radius:22px; overflow:hidden;
    box-shadow:0 8px 24px rgba(103,89,255,0.12);
  }
  .q-header{
    background:linear-gradient(90deg, rgba(103,89,255,.12) 0%, rgba(0,196,255,.12) 100%);
    padding:14px 18px; display:flex; align-items:center; gap:10px
  }
  .q-bubble{
    width:36px;height:36px;border-radius:50%;
    background:linear-gradient(135deg,var(--brand),var(--brand-2));
    color:#fff;display:flex;align-items:center;justify-content:center;
    font-weight:800;box-shadow:0 4px 10px rgba(0,0,0,.08)
  }
  .q-text{font-size:1.08rem;line-height:1.6;color:var(--ink);margin:0}

  .q-body{padding:16px 18px 6px}

  /* Options: chunky, touch-friendly */
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

  /* Footer (sticky) */
  .quiz-footer{
    position:sticky;bottom:0;background:rgba(255,255,255,.9);
    border-top:1px solid rgba(0,0,0,.06); backdrop-filter: blur(6px);
    padding:.6rem 0; z-index:1029
  }
  .btn-pill{
    border-radius:999px; font-weight:700; padding:.55rem 1rem
  }
  .btn-next{
    background:linear-gradient(90deg, var(--brand) 0%, var(--brand-2) 100%);
    color:#fff;border:0;
  }
  .btn-prev{border:2px solid rgba(0,0,0,.08); background:#fff}
  .btn-submit{background:var(--ok); color:#fff; border:0}

  /* Autosave */
  .autosave-dot{width:9px;height:9px;border-radius:50%;display:inline-block;margin-right:6px;background:#6c757d}
  .autosave-ok{background:var(--ok)}.autosave-pending{background:var(--warn)}.autosave-fail{background:var(--danger)}

    .match-wrapper{margin-top:.5rem}
  .match-row{margin-bottom:.5rem}
  .match-left-label{
  width: 45%;
}
  .match-dnd .match-row{
  margin-bottom: 8px;
}
  .match-bank{
  display:flex;
  flex-direction:column;   /* ⬅ vertical stack */
  gap:6px;
  margin-top:4px;
}
  .match-chip{
  display:block;
  width:100%;
  padding:6px 10px;
  margin:0;                /* no horizontal spacing */
  border-radius:999px;
  border:1px solid #d1d5db;
  background:#ffffff;
  cursor:grab;
  font-size:0.9rem;
  box-shadow:0 1px 3px rgba(0,0,0,.05);
}

  .match-dropzone{
  min-height: 38px;
  border: 1px dashed #cbd5e0;
  border-radius: 8px;
  padding: 4px 8px;
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
  .match-dropzone .match-chip{
    margin:0;
  }

  .match-chip.dragging{
  opacity:.7;
  box-shadow:0 4px 10px rgba(0,0,0,.12);
}
</style>

<section class="quiz-topbar">
  <div class="d-flex align-items-center justify-content-between flex-wrap flex-md-nowrap">
    <div class="mb-2 mb-md-0">
      <h1 class="quiz-title mb-0">🎮 <?= esc($quiz->title) ?></h1>
      <?php if (!empty($quiz->instructions)): ?>
        <small class="d-none d-md-inline" style="opacity:.9">
          <?= esc($quiz->instructions) ?>
        </small>
      <?php endif; ?>
    </div>

    <div class="d-flex align-items-center">
      <?php if (!empty($timeLimitSec) && (int)$timeLimitSec > 0): ?>
        <div id="quiz-timer"
             class="badge rounded-pill text-bg-light me-2"
             data-remaining="<?= (int)$timeLimitSec ?>">
          <i class="fas fa-clock"></i>
          <span id="quiz-timer-minutes"></span> min left
        </div>
      <?php endif; ?>
<?php if (!empty($timeLimitSec)): ?>
  <!-- DEBUG timeLimitSec: <?= (int)$timeLimitSec ?> seconds -->
<?php endif; ?>
      <span class="pill" style="background:rgba(0,0,0,.18)">
        <span class="autosave-dot" id="autosaveDot"></span>
        <span id="autosaveText">Saved</span>
      </span>
    </div>
  </div>

  <div class="mt-2 d-flex flex-column flex-md-row justify-content-between small">
    <div>
      <?php if (!empty($quiz->negative_mark_per_q) && (float)$quiz->negative_mark_per_q > 0): ?>
        <span class="text-warning">
          Negative marking:
          <?= (float)$quiz->negative_mark_per_q ?> mark(s) deducted for each wrong answer.
        </span>
      <?php endif; ?>
    </div>

  
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
          $qid = (int)$row->question_id;
          $qt  = $row->question_type ?? 'mcq_single';
          $txt = $row->question ?? 'Question text';
      ?>
      <div class="q-card question-block mb-3" data-index="<?= $index ?>" data-qid="<?= $qid ?>" id="qblock-<?= $qid ?>">
        
        <!-- Header -->
        <div class="q-header">
          <div class="q-bubble"><?= $qNo ?></div>
          <p class="q-text mb-0"><?= nl2br(esc($txt)) ?></p>
        </div>

        <div class="q-body">

          <!-- -------------------------------------------------------------
                MCQ SINGLE   (radio)
          -------------------------------------------------------------- -->
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
            ?>

            <?php foreach ($optionsToShow as $letter => $label): ?>
              <?php if ($label == '') continue; ?>
              <?php $id = 'q'.$qid.'_'.$letter; ?>
              <label class="option d-block" for="<?= $id ?>">
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

            <?php if (!empty($quiz->shuffle_options) && !empty($row->option_map)): ?>
              <?php foreach ($row->option_map as $newL => $origL): ?>
                <input type="hidden" name="optmap[<?= $qid ?>][<?= $newL ?>]" value="<?= $origL ?>">
              <?php endforeach; ?>
            <?php endif; ?>


          <!-- -------------------------------------------------------------
                TRUE / FALSE
          -------------------------------------------------------------- -->
          <?php elseif ($qt === 'true_false' || $qt === 'tf'): ?>
            <?php foreach (['True','False'] as $val): ?>
              <?php $id = 'q'.$qid.'_'.$val; ?>
              <label class="option d-block" for="<?= $id ?>">
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


          <!-- -------------------------------------------------------------
                FILL IN THE BLANK
          -------------------------------------------------------------- -->
          <?php elseif ($qt === 'fill' || $qt === 'fill_blank'): ?>
            <input type="text" class="form-control answer-input"
                   name="ans_<?= $qid ?>"
                   data-qid="<?= $qid ?>"
                   data-type="fill"
                   placeholder="Type your answer">


          <!-- -------------------------------------------------------------
                SHORT ANSWER
          -------------------------------------------------------------- -->
          <?php elseif ($qt === 'short' || $qt === 'short_answer'): ?>
            <textarea class="form-control answer-input"
                      name="ans_<?= $qid ?>"
                      rows="3"
                      data-qid="<?= $qid ?>"
                      data-type="short"
                      placeholder="Type your answer"></textarea>


          <!-- -------------------------------------------------------------
                MCQ MULTI   (checkbox)
                *** FIXED – NOW PROPERLY SUBMITS ARRAY ***
          -------------------------------------------------------------- -->
         <?php elseif ($qt === 'mcq_multi'): ?>

<?php
    // --- READ MULTI OPTIONS FROM options_json ---
    $optionsToShow = [];

    if (!empty($row->options_json)) {
        $json = json_decode($row->options_json, true);
        if (!empty($json['options']) && is_array($json['options'])) {
            $optionsToShow = $json['options'];   // ["A"=>"Text", "B"=>"Text"]
        }
    }

    // Fallback if no JSON options found
    if (empty($optionsToShow)) {
        $optionsToShow = [
            'A' => $row->option_a ?? '',
            'B' => $row->option_b ?? '',
            'C' => $row->option_c ?? '',
            'D' => $row->option_d ?? '',
        ];
    }

    // --- SHUFFLE OPTIONS IF ENABLED ---
    if (!empty($quiz->shuffle_options) && (int)$quiz->shuffle_options === 1) {
        $shuffled = $optionsToShow;
        $keys = array_keys($shuffled);
        shuffle($keys);

        $final = [];
        $map   = [];

        foreach ($keys as $newL) {
            $final[$newL] = $optionsToShow[$newL];
            // mapping new order → original letter
            $map[$newL] = $newL;
        }

        $optionsToShow = $final;
        // place mapping in $row->option_map so it matches single MCQ logic
        $row->option_map = $map;
    }
?>

<?php foreach ($optionsToShow as $letter => $label): ?>
    <?php if (trim($label) === '') continue; ?>
    <?php $id = 'q'.$qid.'_'.$letter; ?>
    <label class="option d-block" for="<?= $id ?>">
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

<?php if (!empty($row->option_map)): ?>
    <?php foreach ($row->option_map as $newL => $origL): ?>
        <input type="hidden"
               name="optmap[<?= (int)$qid ?>][<?= esc($newL) ?>]"
               value="<?= esc($origL) ?>">
    <?php endforeach; ?>
<?php endif; ?>




          <!-- -------------------------------------------------------------
                MATCH (drag or typing)
          -------------------------------------------------------------- -->
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

                    <!-- Left -->
                    <div class="col-md-6">
                      <?php foreach ($leftItems as $left): ?>
                        <div class="match-row d-flex align-items-center" data-left="<?= esc($left) ?>">
                          <div class="match-left-label pe-2" style="width:45%">
                            <strong><?= esc($left) ?></strong>
                          </div>
                          <div class="match-dropzone flex-fill">
                            <span class="text-muted small">Drop here</span>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>

                    <!-- Right draggable items -->
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

                <!-- Typing mode -->
                <div class="match-wrapper">
                  <?php foreach ($pairs as $p): ?>
                    <div class="match-row d-flex align-items-center mb-2">
                      <div style="width:45%;"><strong><?= esc($p['left']) ?></strong></div>
                      <div class="flex-fill">
                        <input type="text"
                               class="form-control answer-input match-input"
                               name="ans_<?= $qid ?>[<?= esc($p['left']) ?>]"
                               data-qid="<?= $qid ?>" data-type="match"
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

    <!-- Footer -->
    <div class="quiz-footer">
      <div class="container-fluid px-2">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex" style="gap:.5rem">
            <button type="button" class="btn btn-light btn-pill btn-prev" id="btnPrev">← Previous</button>
            <button type="button" class="btn btn-next btn-pill" id="btnNext">Next →</button>
          </div>
          <button class="btn btn-submit btn-pill" id="btnSubmit">Submit Attempt ✅</button>
        </div>
      </div>
    </div>

  </form>
</section>


<script>

/* -----------------------------------
   Timer
----------------------------------- */
(function () {
  var timerEl = document.getElementById('quiz-timer');
  if (!timerEl) {
    // No timer badge in DOM => no time limit set
    return;
  }

  var minutesSpan = document.getElementById('quiz-timer-minutes');
  if (!minutesSpan) {
    return;
  }

  // Read attribute safely
  var attr = timerEl.getAttribute('data-remaining');
  var remaining = parseInt(attr, 10);

  if (isNaN(remaining) || remaining <= 0) {
    minutesSpan.textContent = '—';
    return;
  }

  function renderTime(sec) {
    if (sec <= 0) {
      minutesSpan.textContent = '0:00';
      return;
    }
    var m = Math.floor(sec / 60);
    var s = sec % 60;

    // avoid padStart issues, do manual padding
    var sStr = (s < 10 ? '0' + s : '' + s);
    minutesSpan.textContent = m + ':' + sStr;
  }

  // initial render
  renderTime(remaining);

  var intervalId = setInterval(function () {
    remaining--;

    if (remaining <= 0) {
      clearInterval(intervalId);
      renderTime(0);

      // Auto-submit when time is up
      var quizForm = document.getElementById('attemptForm'); // ✅ correct ID
      if (quizForm) {
        quizForm.submit();
      }
      return;
    }

    renderTime(remaining);
  }, 1000);
})();


/* -----------------------------------
   One-by-one Navigation
----------------------------------- */
const blocks = Array.from(document.querySelectorAll('.question-block'));
const currentIndexInput = document.getElementById('currentIndex');
const qPos = document.getElementById('qPos');

function syncPosLabel() {
  var el = document.getElementById('quizPosLabel')   // or whatever ID it uses
           || document.getElementById('posLabel')    // keep your actual ID here
           || null;

  if (!el) {
    // No label in the DOM, just skip updating it and don't break Next/Prev
    return;
  }

  el.textContent = (currentIndex + 1) + ' / ' + totalQuestions;
}

function setCurrent(idx){
  idx = Math.max(0, Math.min(idx, blocks.length-1));
  currentIndexInput.value = idx;
  blocks.forEach(b=>b.classList.remove('active'));
  blocks[idx].classList.add('active');

  document.getElementById('btnPrev').disabled = (idx === 0);
  document.getElementById('btnNext').disabled = (idx === blocks.length-1);

  syncPosLabel(idx);
}
setCurrent(0);

document.getElementById('btnPrev').addEventListener('click',()=> setCurrent(parseInt(currentIndexInput.value,10)-1));
document.getElementById('btnNext').addEventListener('click',()=> setCurrent(parseInt(currentIndexInput.value,10)+1));

/* -----------------------------------
   Autosave & Choice highlight
----------------------------------- */
const autosaveDot  = document.getElementById('autosaveDot');
const autosaveText = document.getElementById('autosaveText');
let saveT;

function setAutosaveState(state){
  autosaveDot.className = 'autosave-dot';
  if(state==='pending'){ autosaveDot.classList.add('autosave-pending'); autosaveText.textContent='Saving...'; }
  else if(state==='ok'){ autosaveDot.classList.add('autosave-ok'); autosaveText.textContent='Saved'; }
  else { autosaveDot.classList.add('autosave-fail'); autosaveText.textContent='Save failed'; }
  if(saveT) clearTimeout(saveT);
  if(state!=='pending') saveT = setTimeout(()=>{ autosaveText.textContent=''; autosaveDot.className='autosave-dot'; }, 1800);
}

// visual selection
document.querySelectorAll('.option input').forEach(inp=>{
  inp.addEventListener('change', ()=>{
    const wrap = inp.closest('.q-card');
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

// text answers updates (no palette needed)
document.querySelectorAll('input.answer-input[type="text"], textarea.answer-input').forEach(el=>{
  el.addEventListener('keyup', debounce(()=>{}, 200));
});

/* -----------------------------------
   Submit
----------------------------------- */
document.getElementById('btnSubmit').addEventListener('click', (e)=>{
  e.preventDefault();
  document.getElementById('attemptForm').submit();
});

/* -----------------------------------
   Autosave calls (with MATCH support)
----------------------------------- */
document.querySelectorAll('.answer-input').forEach(el => {
  el.addEventListener('change', saveAnswer);
  el.addEventListener('keyup', debounce(saveAnswer, 600));
});

/* -----------------------------------
   Match drag & drop logic + Reset
----------------------------------- */
/* -----------------------------------
   Match drag & drop logic + Reset
   - Desktop: HTML5 drag & drop
   - Mobile: tap chip -> tap dropzone
----------------------------------- */
function initMatchDragDrop() {
  const wrappers = document.querySelectorAll('.match-dnd');
  let activeTouchChip = null; // for mobile tap-to-drop

  wrappers.forEach(wrapper => {
    const qid       = wrapper.getAttribute('data-qid');
    const chips     = wrapper.querySelectorAll('.match-chip');
    const dropzones = wrapper.querySelectorAll('.match-dropzone');
    const bank      = wrapper.querySelector('.match-bank');
    const hidden    = document.querySelector('.match-store[data-qid="'+qid+'"]');
    const resetBtn  = wrapper.querySelector('.match-reset');

    let draggedChip = null; // for desktop drag

    // ---- helpers ----
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

      // Move all chips from dropzones back to bank
      dropzones.forEach(zone => {
        const chip = zone.querySelector('.match-chip');
        if (chip) {
          bank.appendChild(chip);
        }
        zone.innerHTML = placeholderHtml();
        zone.classList.remove('filled','over');
      });

      // Clear active touch selection
      activeTouchChip = null;
      chips.forEach(c => c.classList.remove('dragging'));

      // Trigger autosave for cleared state
      triggerHiddenAutosave();
    }

    function handleDrop(targetZone, chip) {
      if (!chip || !targetZone) return;

      // each chip only once: remove from any previous zone
      dropzones.forEach(z => {
        if (z !== targetZone && z.contains(chip)) {
          z.innerHTML = placeholderHtml();
          z.classList.remove('filled');
        }
      });

      // if target zone already has a chip, move it back to bank
      const existingChip = targetZone.querySelector('.match-chip');
      if (existingChip && bank) {
        bank.appendChild(existingChip);
      }

      // Put chip into current zone
      targetZone.innerHTML = '';
      targetZone.appendChild(chip);
      targetZone.classList.add('filled');

      // clear touch selection highlight
      chips.forEach(c => c.classList.remove('dragging'));
      activeTouchChip = null;

      // trigger autosave
      triggerHiddenAutosave();
    }

    // ---- attach reset button ----
    if (resetBtn) {
      resetBtn.addEventListener('click', resetMatches);
    }

    // ---- DESKTOP: drag logic ----
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

      // ---- MOBILE: tap chip to select ----
      chip.addEventListener('touchstart', e => {
        // prevent scroll + long-press context
        e.preventDefault();
        activeTouchChip = chip;
        chips.forEach(c => c.classList.remove('dragging'));
        chip.classList.add('dragging');
      }, { passive: false });
    });

    // ---- DESKTOP: dropzones ----
    dropzones.forEach(zone => {
      zone.addEventListener('dragover', e => {
        e.preventDefault();
        zone.classList.add('over');
      });

      zone.addEventListener('dragleave', () => {
        zone.classList.remove('over');
      });

      zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('over');
        if (!draggedChip) return;
        handleDrop(zone, draggedChip);
      });

      // ---- MOBILE: tap dropzone to "drop" selected chip ----
      zone.addEventListener('touchstart', e => {
        if (!activeTouchChip) return; // nothing selected
        e.preventDefault();
        handleDrop(zone, activeTouchChip);
      }, { passive: false });
    });
  });
}

// Initialize on load
initMatchDragDrop();


//save
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
    // match: build array of {left, value} for this question
    const dataArr = [];

    // Drag mode wrapper?
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
      // Typing mode (inputs)
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

document.addEventListener('DOMContentLoaded', enableTouchDrag);
</script>

<?= $this->endSection() ?>
