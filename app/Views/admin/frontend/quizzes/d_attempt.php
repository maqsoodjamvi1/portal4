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
</style>

<section class="quiz-topbar">
  <div class="d-flex align-items-center justify-content-between">
    <div>
      <h1 class="quiz-title mb-0">🎮 <?= esc($quiz->title) ?></h1>
      <?php if (!empty($quiz->instructions)): ?>
        <small class="d-none d-md-inline" style="opacity:.9"><?= esc($quiz->instructions) ?></small>
      <?php endif; ?>
    </div>

    <div class="d-flex align-items-center" style="gap:.5rem">
      <span class="pill" id="qPos">Q 1 / <?= count($qq) ?></span>
      <?php if ($quiz->time_limit_sec): ?>
        <span class="pill">
          ⏱️ <strong id="timeLeft" class="timer-pill timer-ok">--:--</strong>
        </span>
      <?php endif; ?>
      <span class="pill" style="background:rgba(0,0,0,.18)">
        <span class="autosave-dot" id="autosaveDot"></span><span id="autosaveText">Saved</span>
      </span>
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
        <!-- Header with inline question number (no IDs / marks / review) -->
        <div class="q-header">
          <div class="q-bubble"><?= $qNo ?></div>
          <p class="q-text mb-0"><?= nl2br(esc($txt)) ?></p>
        </div>

        <div class="q-body">
          <?php if (in_array($qt, ['mcq','mcq_single'])): ?>

            <?php
              // Decide which options array to show (shuffled vs original)
              $optionsToShow = [];

              if (!empty($quiz->shuffle_options) && (int)$quiz->shuffle_options === 1 && !empty($row->shuffled_options)) {
                  // Use shuffled options prepared in controller: [newLetter => text]
                  $optionsToShow = $row->shuffled_options;
              } else {
                  // Fallback to original order A–D
                  $optionsToShow = [
                      'A' => $row->option_a ?? '',
                      'B' => $row->option_b ?? '',
                      'C' => $row->option_c ?? '',
                      'D' => $row->option_d ?? '',
                  ];
              }
            ?>

            <?php foreach ($optionsToShow as $letter => $label): ?>
              <?php if ($label === null || $label === '') continue; ?>
              <?php $id = 'q'.$qid.'_'. $letter; ?>
              <label class="option d-block" for="<?= $id ?>">
                <input type="radio" class="answer-input"
                       id="<?= $id ?>" name="ans_<?= $qid ?>"
                       data-qid="<?= $qid ?>" data-type="mcq_single" value="<?= esc($letter) ?>">
                <span><?= esc($letter) ?>) <?= esc($label) ?></span>
              </label>
            <?php endforeach; ?>

            <?php if (!empty($quiz->shuffle_options) && (int)$quiz->shuffle_options === 1 && !empty($row->option_map)): ?>
              <?php foreach ($row->option_map as $newL => $origL): ?>
                <input type="hidden"
                       name="optmap[<?= (int)$qid ?>][<?= esc($newL) ?>]"
                       value="<?= esc($origL) ?>">
              <?php endforeach; ?>
            <?php endif; ?>

          <?php elseif ($qt === 'true_false' || $qt === 'tf'): ?>

            <?php foreach (['True','False'] as $val):
                  $id = 'q'.$qid.'_'.$val;
            ?>
              <label class="option d-block" for="<?= $id ?>">
                <input type="radio" class="answer-input"
                       id="<?= $id ?>" name="ans_<?= $qid ?>"
                       data-qid="<?= $qid ?>" data-type="tf" value="<?= esc($val) ?>">
                <span><?= esc($val) ?></span>
              </label>
            <?php endforeach; ?>

          <?php elseif ($qt === 'fill' || $qt === 'fill_blank'): ?>

            <input type="text" class="form-control answer-input"
                   data-qid="<?= $qid ?>" data-type="fill" placeholder="Type your answer">

          <?php elseif ($qt === 'short' || $qt === 'short_answer'): ?>

            <textarea class="form-control answer-input" rows="3"
                      data-qid="<?= $qid ?>" data-type="short" placeholder="Type your answer"></textarea>

          <?php elseif ($qt === 'mcq_multi'): ?>

            <?php
              $optionsToShow = [];

              if (!empty($quiz->shuffle_options) && (int)$quiz->shuffle_options === 1 && !empty($row->shuffled_options)) {
                  $optionsToShow = $row->shuffled_options;   // [newLetter => text]
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
              <?php if ($label === null || $label === '') continue; ?>
              <?php $id = 'q'.$qid.'_'. $letter; ?>
              <label class="option d-block" for="<?= $id ?>">
                <input type="checkbox" class="answer-input"
                       id="<?= $id ?>" data-qid="<?= $qid ?>" data-type="mcq_multi" value="<?= esc($letter) ?>">
                <span><?= esc($letter) ?>) <?= esc($label) ?></span>
              </label>
            <?php endforeach; ?>

            <?php if (!empty($quiz->shuffle_options) && (int)$quiz->shuffle_options === 1 && !empty($row->option_map)): ?>
              <?php foreach ($row->option_map as $newL => $origL): ?>
                <input type="hidden"
                       name="optmap[<?= (int)$qid ?>][<?= esc($newL) ?>]"
                       value="<?= esc($origL) ?>">
              <?php endforeach; ?>
            <?php endif; ?>

          <?php else: ?>

            <div class="text-muted">Unsupported question type: <?= esc($qt) ?></div>

          <?php endif; ?>
        </div>
      </div>
      <?php $qNo++; $index++; endforeach; ?>
    </div>

    <!-- Sticky footer: Prev / Next / Submit (no Summary) -->
    <div class="quiz-footer">
      <div class="container-fluid px-2">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex" style="gap:.5rem">
            <button type="button" class="btn btn-light btn-pill btn-prev" id="btnPrev">← Previous</button>
            <button type="button" class="btn btn-next btn-pill" id="btnNext">Next →</button>
          </div>
          <div>
            <button class="btn btn-submit btn-pill" id="btnSubmit">Submit Attempt ✅</button>
          </div>
        </div>
      </div>
    </div>
  </form>
</section>

<script>
/* -----------------------------------
   Timer
----------------------------------- */
<?php if ($quiz->time_limit_sec): ?>
let timeRemain = <?= (int)$quiz->time_limit_sec ?>;
const tSpan = document.getElementById('timeLeft');
const timerTick = () => {
  const m = Math.floor(timeRemain/60), s = timeRemain%60;
  tSpan.textContent = `${m}:${String(s).padStart(2,'0')}`;
  const cls = timeRemain <= 30 ? 'timer-danger' : (timeRemain <= 120 ? 'timer-warn':'timer-ok');
  tSpan.classList.remove('timer-ok','timer-warn','timer-danger'); tSpan.classList.add(cls);
  if (timeRemain <= 0) {
    // Time up -> just submit form (backend should finalize without re-evaluating)
    document.getElementById('attemptForm').submit();
    return;
  }
  timeRemain--;
};
timerTick();
setInterval(timerTick, 1000);
<?php endif; ?>

/* -----------------------------------
   One-by-one Navigation
----------------------------------- */
const blocks = Array.from(document.querySelectorAll('.question-block'));
const currentIndexInput = document.getElementById('currentIndex');
const qPos = document.getElementById('qPos');
const btnPrev = document.getElementById('btnPrev');
const btnNext = document.getElementById('btnNext');

function syncPosLabel(idx){
  qPos.textContent = `Q ${idx+1} / ${blocks.length}`;
}

function setCurrent(idx){
  idx = Math.max(0, Math.min(idx, blocks.length-1));
  currentIndexInput.value = idx;
  blocks.forEach(b=>b.classList.remove('active'));
  if (blocks[idx]) {
    blocks[idx].classList.add('active');
  }
  btnPrev.disabled = (idx === 0);
  btnNext.disabled = (idx === blocks.length-1);
  syncPosLabel(idx);
}
setCurrent(0);

btnPrev.addEventListener('click', () => {
  const idx = parseInt(currentIndexInput.value, 10) || 0;
  setCurrent(idx - 1);
});

/* -----------------------------------
   Autosave indicator
----------------------------------- */
const autosaveDot  = document.getElementById('autosaveDot');
const autosaveText = document.getElementById('autosaveText');
let saveT;

function setAutosaveState(state){
  autosaveDot.className = 'autosave-dot';
  if (state === 'pending') {
    autosaveDot.classList.add('autosave-pending');
    autosaveText.textContent = 'Saving...';
  } else if (state === 'ok') {
    autosaveDot.classList.add('autosave-ok');
    autosaveText.textContent = 'Saved';
  } else if (state === 'fail') {
    autosaveDot.classList.add('autosave-fail');
    autosaveText.textContent = 'Save failed';
  }
  if (saveT) clearTimeout(saveT);
  if (state !== 'pending') {
    saveT = setTimeout(() => {
      autosaveText.textContent = '';
      autosaveDot.className = 'autosave-dot';
    }, 1800);
  }
}

/* -----------------------------------
   Visual selection highlight
----------------------------------- */
document.querySelectorAll('.option input').forEach(inp=>{
  inp.addEventListener('change', ()=>{
    const wrap = inp.closest('.q-card');
    if (inp.type === 'checkbox') {
      wrap.querySelectorAll('.option input[type="checkbox"]').forEach(cb=>{
        cb.closest('.option').classList.toggle('checked', cb.checked);
      });
    } else {
      wrap.querySelectorAll('.option').forEach(o=>o.classList.remove('checked'));
      inp.closest('.option').classList.add('checked');
    }
  });
});

/* -----------------------------------
   Save current question answer (on Next)
----------------------------------- */

function saveCurrentQuestionAndGoNext(){
  const idx   = parseInt(currentIndexInput.value, 10) || 0;
  const block = blocks[idx];
  if (!block) return;

  const qid  = block.getAttribute('data-qid');
  const anyInput = block.querySelector('.answer-input');
  const type = anyInput ? anyInput.getAttribute('data-type') : null;
  const attemptId = <?= (int)$attemptId ?>;

  if (!qid || !type || !attemptId) {
    // No question info; just move to next
    setCurrent(idx + 1);
    return;
  }

  const form = new URLSearchParams();
  form.append('<?= csrf_token() ?>','<?= csrf_hash() ?>');
  form.append('attempt_id', attemptId);
  form.append('question_id', qid);

  // Collect answer for this question only
  if (type === 'mcq_single' || type === 'tf') {
    const checked = block.querySelector(`input[name="ans_${qid}"]:checked`);
    if (checked) {
      if (type === 'mcq_single') {
        form.append('selected_option', checked.value);
      } else {
        form.append('answer_text', checked.value);
      }
    }
    // if nothing checked, we still send request so backend can treat as unanswered
  } else if (type === 'fill' || type === 'short') {
    const textEl = block.querySelector('.answer-input[data-qid="' + qid + '"]');
    if (textEl) {
      form.append('answer_text', textEl.value);
    }
  } else if (type === 'mcq_multi') {
    const cbs = block.querySelectorAll('input.answer-input[type="checkbox"][data-qid="' + qid + '"]:checked');
    cbs.forEach(cb => {
      form.append('selected_options[]', cb.value);
    });
  }

  // Include optmap for THIS question (needed for shuffled options grading)
  block.querySelectorAll('input[name^="optmap[' + qid + ']"]').forEach(h => {
    // h.name example: optmap[12][B]
    form.append(h.name, h.value);
  });

  setAutosaveState('pending');

  fetch('<?= base_url('student/quizzes/save-answer') ?>', {
    method: 'POST',
    headers: {
      'X-Requested-With':'XMLHttpRequest',
      'Content-Type':'application/x-www-form-urlencoded'
    },
    body: form.toString(),
  })
  .then(async (r) => {
    if (!r.ok) throw new Error('HTTP ' + r.status);
    // Optional: parse JSON and use total_score, etc.
    // const data = await r.json().catch(()=> ({}));
    setAutosaveState('ok');
    setCurrent(idx + 1);
  })
  .catch(() => {
    setAutosaveState('fail');
    // In case of failure, you might NOT want to move to next,
    // but for now we keep the user on same question.
  });
}

btnNext.addEventListener('click', saveCurrentQuestionAndGoNext);

/* -----------------------------------
   Final Submit (no re-evaluation)
----------------------------------- */
document.getElementById('btnSubmit').addEventListener('click', (e)=>{
  e.preventDefault();
  // You could optionally save the current question one last time here,
  // but since you want evaluation tied to Next, we'll just submit.
  document.getElementById('attemptForm').submit();
});

function debounce(fn, delay){
  let t; return function(...args){ clearTimeout(t); t=setTimeout(()=>fn.apply(this,args),delay); };
}
</script>

<?= $this->endSection() ?>
