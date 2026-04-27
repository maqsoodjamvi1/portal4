<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<style>
  /* ===== Layout ===== */
  .quiz-wrap{display:grid;grid-template-columns:1fr 280px;grid-gap:1rem}
  @media(max-width:992px){.quiz-wrap{grid-template-columns:1fr}}
  .quiz-topbar{
    position:sticky;top:0;z-index:1030;background:#fff;border-bottom:1px solid #e9ecef;
    padding:.75rem 1rem;display:flex;align-items:center;justify-content:space-between
  }
  .quiz-title{font-weight:600;margin:0;font-size:1.15rem}
  .timer-pill{font-weight:700}
  .timer-ok{color:#28a745}.timer-warn{color:#ffc107}.timer-danger{color:#dc3545}

  /* ===== One-by-one Question View ===== */
  .question-block{display:none}
  .question-block.active{display:block}

  /* ===== Question Card ===== */
  .q-card .card-header{background:#fafbfc}
  .q-text{font-size:1.02rem;line-height:1.6}
  .option{border:1px solid #e9ecef;border-radius:.5rem;padding:.5rem .75rem;margin-bottom:.5rem;cursor:pointer}
  .option:hover{background:#f9fafb}
  .option input{margin-right:.5rem}
  .option.checked{border-color:#17a2b8;background:#f1fbfe}
  .option.correct-preview{border-color:#28a745;background:#f7fff9}

  /* ===== Palette ===== */
  .palette .card-body{padding:.75rem}
  .q-badge{
    width:40px;height:40px;border-radius:.5rem;border:1px solid #dee2e6;
    display:flex;align-items:center;justify-content:center;margin:.25rem;font-weight:600;cursor:pointer;
    user-select:none;background:#fff
  }
  .q-badge:hover{background:#f8f9fa}
  .q-current{border-color:#17a2b8;box-shadow:0 0 0 2px rgba(23,162,184,.2)}
  .q-answered{background:#eafff4;border-color:#28a745}
  .q-flag{position:relative}
  .q-flag::after{
    content:"";position:absolute;top:2px;right:4px;width:0;height:0;border-left:6px solid transparent;
    border-right:6px solid transparent;border-bottom:10px solid #ffc107
  }
  .palette-legend .legend{display:flex;align-items:center;margin-right:10px;font-size:.85rem}
  .legend .swatch{width:14px;height:14px;border:1px solid #dee2e6;margin-right:6px;border-radius:3px}
  .swatch-ans{background:#eafff4;border-color:#28a745}
  .swatch-cur{box-shadow:0 0 0 2px rgba(23,162,184,.2)}
  .swatch-flag{background:linear-gradient(135deg,#fff 60%, #ffc107 60%)}

  /* ===== Footer Bar ===== */
  .quiz-footer{
    position:sticky;bottom:0;background:#fff;border-top:1px solid #e9ecef;padding:.5rem 0;z-index:1029
  }

  /* Autosave */
  .autosave-dot{width:9px;height:9px;border-radius:50%;display:inline-block;margin-right:6px;background:#6c757d}
  .autosave-ok{background:#28a745}.autosave-pending{background:#ffc107}.autosave-fail{background:#dc3545}
</style>

<section class="quiz-topbar">
  <div>
    <h1 class="quiz-title mb-0"><?= esc($quiz->title) ?></h1>
    <?php if (!empty($quiz->instructions)): ?>
      <small class="text-muted d-none d-md-inline"><?= esc($quiz->instructions) ?></small>
    <?php endif; ?>
  </div>

  <div class="d-flex align-items-center">
    <div class="mr-3">
      <span class="badge badge-light px-3 py-2">
        <span id="qPos">Question 1 of <?= count($qq) ?></span>
      </span>
    </div>
    <?php if ($quiz->time_limit_sec): ?>
      <div class="mr-3">
        <span class="badge badge-light px-3 py-2">
          ⏱️ Time Left:
          <strong id="timeLeft" class="timer-pill timer-ok">--:--</strong>
        </span>
      </div>
    <?php endif; ?>
    <div id="autosaveState" class="text-muted small">
      <span class="autosave-dot" id="autosaveDot"></span><span id="autosaveText">Saved</span>
    </div>
  </div>
</section>

<section class="content">
  <form action="<?= base_url('student/quizzes/submit') ?>" method="post" id="attemptForm">
    <?= csrf_field() ?>
    <input type="hidden" name="attempt_id" value="<?= (int)$attemptId ?>">
    <input type="hidden" id="currentIndex" value="0">

    <div class="quiz-wrap">
      <!-- ===== Questions Area (one visible at a time) ===== -->
      <div id="questionsPane">
        <?php
          $qNo = 1; $index = 0;
          foreach ($qq as $row):
            $qid = (int)$row->question_id;
            $qt  = $row->question_type ?? 'mcq_single';
            $txt = $row->question ?? 'Question text';
        ?>
        <div class="card q-card mb-3 question-block" data-index="<?= $index ?>" data-qid="<?= $qid ?>" id="qblock-<?= $qid ?>">
          <div class="card-header d-flex align-items-center justify-content-between">
            <div>
              <strong>Q<?= $qNo ?>.</strong>
              <small class="text-muted ml-2">ID: <?= $qid ?></small>
              <?php if (isset($row->marks)): ?><span class="badge badge-info ml-2"><?= (float)$row->marks ?> mark(s)</span><?php endif; ?>
            </div>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input mark-flag" id="flag-<?= $qid ?>" data-qid="<?= $qid ?>">
              <label class="custom-control-label" for="flag-<?= $qid ?>">Mark for review</label>
            </div>
          </div>

          <div class="card-body">
            <div class="q-text mb-3"><?= nl2br(esc($txt)) ?></div>

            <?php if (in_array($qt, ['mcq','mcq_single'])): ?>
              <?php foreach (['A','B','C','D'] as $letter):
                    $id   = 'q'.$qid.'_'.$letter;
                    $label= $row->{'option_'.strtolower($letter)} ?? '';
              ?>
                <label class="option d-block" for="<?= $id ?>">
                  <input type="radio" class="answer-input"
                         id="<?= $id ?>" name="ans_<?= $qid ?>"
                         data-qid="<?= $qid ?>" data-type="mcq_single" value="<?= $letter ?>">
                  <span><?= $letter ?>) <?= esc($label) ?></span>
                </label>
              <?php endforeach; ?>

            <?php elseif ($qt === 'true_false' || $qt === 'tf'): ?>
              <?php foreach (['True','False'] as $val):
                    $id = 'q'.$qid.'_'.$val;
              ?>
                <label class="option d-block" for="<?= $id ?>">
                  <input type="radio" class="answer-input"
                         id="<?= $id ?>" name="ans_<?= $qid ?>"
                         data-qid="<?= $qid ?>" data-type="tf" value="<?= $val ?>">
                  <span><?= $val ?></span>
                </label>
              <?php endforeach; ?>

            <?php elseif ($qt === 'fill' || $qt === 'fill_blank'): ?>
              <input type="text" class="form-control answer-input"
                     data-qid="<?= $qid ?>" data-type="fill" placeholder="Type your answer">

            <?php elseif ($qt === 'short' || $qt === 'short_answer'): ?>
              <textarea class="form-control answer-input" rows="3"
                        data-qid="<?= $qid ?>" data-type="short" placeholder="Type your answer"></textarea>

            <?php elseif ($qt === 'mcq_multi'): ?>
              <?php foreach (['A','B','C','D'] as $letter):
                    $id = 'q'.$qid.'_'.$letter;
                    $label= $row->{'option_'.strtolower($letter)} ?? '';
              ?>
                <label class="option d-block" for="<?= $id ?>">
                  <input type="checkbox" class="answer-input"
                         id="<?= $id ?>" data-qid="<?= $qid ?>" data-type="mcq_multi" value="<?= $letter ?>">
                  <span><?= $letter ?>) <?= esc($label) ?></span>
                </label>
              <?php endforeach; ?>

            <?php else: ?>
              <div class="text-muted">Unsupported question type: <?= esc($qt) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <?php $qNo++; $index++; endforeach; ?>

        <!-- Footer bar -->
        <div class="quiz-footer">
          <div class="container-fluid px-0">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <button type="button" class="btn btn-outline-secondary" id="btnPrev">← Previous</button>
                <button type="button" class="btn btn-outline-secondary" id="btnNext">Next →</button>
              </div>
              <div>
                <button type="button" class="btn btn-warning" id="btnSummary">Summary</button>
                <button class="btn btn-success" id="btnSubmit">Submit Attempt</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== Palette / Sidebar ===== -->
      <div class="palette">
        <div class="card">
          <div class="card-header py-2">
            <strong>Question Palette</strong>
          </div>
          <div class="card-body">
            <div class="d-flex flex-wrap" id="paletteWrap">
              <?php for ($i=0;$i<count($qq);$i++): $qid=(int)$qq[$i]->question_id; ?>
                <div class="q-badge" data-index="<?= $i ?>" data-qid="<?= $qid ?>"><?= $i+1 ?></div>
              <?php endfor; ?>
            </div>
            <hr class="my-2">
            <div class="d-flex flex-wrap palette-legend">
              <div class="legend mr-3"><span class="swatch"></span> Not Answered</div>
              <div class="legend mr-3"><span class="swatch swatch-ans"></span> Answered</div>
              <div class="legend mr-3"><span class="swatch swatch-flag"></span> Marked</div>
              <div class="legend"><span class="swatch swatch-cur"></span> Current</div>
            </div>
          </div>
        </div>

        <?php if ($quiz->time_limit_sec): ?>
        <div class="card mt-2">
          <div class="card-body py-2">
            <div class="small text-muted mb-1">Tip</div>
            <div class="small">You can mark a question for review and come back later.</div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </form>
</section>

<!-- Summary Modal -->
<div class="modal fade" id="summaryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Attempt Summary</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body" id="summaryBody">
        <!-- filled by JS -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Continue</button>
        <button type="button" class="btn btn-success" id="summarySubmit">Submit Now</button>
      </div>
    </div>
  </div>
</div>

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
  // color state
  const cls = timeRemain <= 30 ? 'timer-danger' : (timeRemain <= 120 ? 'timer-warn':'timer-ok');
  tSpan.classList.remove('timer-ok','timer-warn','timer-danger'); tSpan.classList.add(cls);
  if (timeRemain<=0) document.getElementById('attemptForm').submit();
  timeRemain--;
};
timerTick();
setInterval(timerTick, 1000);
<?php endif; ?>

/* -----------------------------------
   One-by-one Navigation / Palette
----------------------------------- */
const blocks = Array.from(document.querySelectorAll('.question-block'));
const palette = document.getElementById('paletteWrap');
const badges = Array.from(palette.querySelectorAll('.q-badge'));
const currentIndexInput = document.getElementById('currentIndex');
const qPos = document.getElementById('qPos');

function syncPosLabel(idx){
  qPos.textContent = `Question ${idx+1} of ${blocks.length}`;
}

function setCurrent(idx){
  idx = Math.max(0, Math.min(idx, blocks.length-1));
  currentIndexInput.value = idx;

  // toggle active visibility
  blocks.forEach(b=>b.classList.remove('active'));
  badges.forEach(b=>b.classList.remove('q-current'));
  blocks[idx].classList.add('active');
  badges[idx].classList.add('q-current');

  // enable/disable nav
  document.getElementById('btnPrev').disabled = (idx === 0);
  document.getElementById('btnNext').disabled = (idx === blocks.length-1);

  syncPosLabel(idx);
}
setCurrent(0);

badges.forEach(b=>{
  b.addEventListener('click',()=> setCurrent(parseInt(b.dataset.index,10)));
});

document.getElementById('btnPrev').addEventListener('click',()=>{
  setCurrent(parseInt(currentIndexInput.value,10)-1);
});
document.getElementById('btnNext').addEventListener('click',()=>{
  setCurrent(parseInt(currentIndexInput.value,10)+1);
});

/* -----------------------------------
   Mark for review
----------------------------------- */
document.querySelectorAll('.mark-flag').forEach(cb=>{
  cb.addEventListener('change', e=>{
    const qid = e.target.getAttribute('data-qid');
    const badge = badges.find(x=>x.dataset.qid===qid);
    if(!badge) return;
    badge.classList.toggle('q-flag', e.target.checked);
  });
});

/* -----------------------------------
   Autosave & Answer state
----------------------------------- */
const autosaveDot  = document.getElementById('autosaveDot');
const autosaveText = document.getElementById('autosaveText');
let saveT;

function setAutosaveState(state){
  autosaveDot.classList.remove('autosave-ok','autosave-pending','autosave-fail');
  if(state==='pending'){ autosaveDot.classList.add('autosave-pending'); autosaveText.textContent='Saving...'; }
  else if(state==='ok'){ autosaveDot.classList.add('autosave-ok'); autosaveText.textContent='Saved'; }
  else { autosaveDot.classList.add('autosave-fail'); autosaveText.textContent='Save failed'; }
  if(saveT) clearTimeout(saveT);
  if(state!=='pending') saveT = setTimeout(()=>{ autosaveText.textContent=''; autosaveDot.className='autosave-dot'; }, 2000);
}

// click highlight for choices
document.querySelectorAll('.option input').forEach(inp=>{
  inp.addEventListener('change', ()=>{
    const wrap = inp.closest('.q-card');
    if(inp.type==='checkbox'){
      wrap.querySelectorAll('.option input[type="checkbox"]').forEach(cb=>{
        cb.closest('.option').classList.toggle('checked', cb.checked);
      });
    }else{
      wrap.querySelectorAll('.option').forEach(o=>o.classList.remove('checked'));
      inp.closest('.option').classList.add('checked');
    }
    updateBadgeAnswered(wrap.dataset.qid);
  });
});

// text answers updates
document.querySelectorAll('input.answer-input[type="text"], textarea.answer-input').forEach(el=>{
  el.addEventListener('keyup', debounce(()=> updateBadgeAnswered(el.dataset.qid), 200));
});

function updateBadgeAnswered(qid){
  const block = document.querySelector(`.question-block[data-qid="${qid}"]`);
  let answered = false;
  if(!block) return;
  const radios = block.querySelectorAll('input[type="radio"]');
  const checks = block.querySelectorAll('input[type="checkbox"]');
  const text   = block.querySelector('input[type="text"], textarea');

  if(radios.length) answered = !!block.querySelector('input[type="radio"]:checked');
  else if(checks.length) answered = Array.from(checks).some(c=>c.checked);
  else if(text) answered = text.value.trim().length>0;

  const badge = badges.find(x=>x.dataset.qid===qid);
  if(badge) badge.classList.toggle('q-answered', answered);
}

/* -----------------------------------
   Submit/Summary
----------------------------------- */
document.getElementById('btnSubmit').addEventListener('click', (e)=>{
  e.preventDefault();
  buildSummary();
  $('#summaryModal').modal('show');
});
document.getElementById('btnSummary').addEventListener('click', ()=>{
  buildSummary();
  $('#summaryModal').modal('show');
});
document.getElementById('summarySubmit').addEventListener('click', ()=>{
  document.getElementById('attemptForm').submit();
});

function buildSummary(){
  const answered = badges.filter(b=>b.classList.contains('q-answered')).length;
  const flagged  = badges.filter(b=>b.classList.contains('q-flag')).length;
  const total    = badges.length;
  const unans    = total - answered;
  const listUn   = badges.filter(b=>!b.classList.contains('q-answered')).map(b=>b.textContent.trim());
  const html = `
    <div class="d-flex justify-content-around text-center mb-3">
      <div><div class="h4 mb-0 text-success">${answered}</div><div class="small text-muted">Answered</div></div>
      <div><div class="h4 mb-0 text-warning">${flagged}</div><div class="small text-muted">Marked</div></div>
      <div><div class="h4 mb-0 text-secondary">${unans}</div><div class="small text-muted">Unanswered</div></div>
      <div><div class="h4 mb-0">${total}</div><div class="small text-muted">Total</div></div>
    </div>
    ${unans ? `<div class="small"><strong>Unanswered:</strong> ${listUn.join(', ')}</div>` : `<div class="small text-success">All questions answered.</div>`}
    <hr>
    <p class="mb-0">You can still jump to any question from the palette before submitting.</p>
  `;
  document.getElementById('summaryBody').innerHTML = html;
}

/* -----------------------------------
   Autosave calls
----------------------------------- */
document.querySelectorAll('.answer-input').forEach(el => {
  el.addEventListener('change', saveAnswer);
  el.addEventListener('keyup', debounce(saveAnswer, 600));
});

function saveAnswer(e){
  const el = e.target;
  const qid = el.getAttribute('data-qid');
  const type = el.getAttribute('data-type');
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
    document.querySelectorAll(`#q${qid}_A, #q${qid}_B, #q${qid}_C, #q${qid}_D`).forEach(cb => {
      if (cb.checked) vals.push(cb.value);
    });
    vals.forEach(v => form.append('selected_options[]', v));
  }

  setAutosaveState('pending');
  fetch('<?= base_url('student/quizzes/save-answer') ?>', {
    method: 'POST',
    headers: { 'X-Requested-With':'XMLHttpRequest', 'Content-Type':'application/x-www-form-urlencoded' },
    body: form.toString(),
  }).then(r=>r.ok ? setAutosaveState('ok') : setAutosaveState('fail'))
    .catch(()=> setAutosaveState('fail'));

  // refresh palette state
  updateBadgeAnswered(qid);
}

function debounce(fn, delay){
  let t; return function(...args){ clearTimeout(t); t=setTimeout(()=>fn.apply(this,args),delay); };
}
</script>

<?= $this->endSection() ?>
