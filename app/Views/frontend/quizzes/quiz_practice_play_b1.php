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
  .pill-score{
    display:flex;align-items:center;gap:.35rem;
  }

  /* Layout */
  .quiz-wrap{max-width:980px;margin:16px auto 80px; padding:0 12px}
  .question-block{display:none}
  .question-block.active{display:block}

  /* Question card */
  .q-card{
    background:var(--card); border:0;
    border-radius:22px; overflow:hidden;
    box-shadow:0 8px 24px rgba(103,89,255,0.12);
    position:relative;
  }
  .q-header{
    background:linear-gradient(90deg, rgba(103,89,255,.14) 0%, rgba(0,196,255,.16) 100%);
    padding:14px 18px; display:flex; align-items:center; gap:10px
  }
  .q-bubble{
    width:40px;height:40px;border-radius:50%;
    background:linear-gradient(135deg,var(--brand),var(--brand-2));
    color:#fff;display:flex;align-items:center;justify-content:center;
    font-weight:800;box-shadow:0 4px 10px rgba(0,0,0,.08);
    font-size:1.1rem;
  }
  .q-text{font-size:1.1rem;line-height:1.6;color:var(--ink);margin:0}

  .q-body{padding:18px 18px 6px}

  /* Option cards: chunky, touch-friendly, game-like */
  .options-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:.75rem;
  }
  .option-card{
    border:3px solid transparent;
    border-radius:18px;
    padding:.85rem .9rem;
    cursor:pointer;
    background:#f9fbff;
    display:flex;
    align-items:flex-start;
    gap:.55rem;
    transition:
      transform .08s ease,
      box-shadow .2s ease,
      background .2s ease,
      border-color .2s ease;
    position:relative;
    min-height:70px;
  }
  .option-card:hover{
    transform:translateY(-1px) scale(1.01);
    box-shadow:0 8px 18px rgba(0,0,0,.06);
  }
  .option-card input{
    position:absolute;
    opacity:0;
    pointer-events:none;
  }
  .opt-letter{
    width:32px;height:32px;border-radius:50%;
    background:rgba(103,89,255,.12);
    display:flex;align-items:center;justify-content:center;
    font-weight:800;
    color:var(--brand);
    flex-shrink:0;
  }
  .opt-text{
    font-size:1rem;line-height:1.5;color:var(--ink);
  }

  .option-card.selected{
    border-color:var(--brand);
    background:linear-gradient(0deg, rgba(103,89,255,.08), rgba(103,89,255,.02));
    box-shadow:0 6px 16px rgba(103,89,255,.2);
  }
  .option-card.correct{
    border-color:var(--ok);
    background:linear-gradient(135deg, #baf5da, #e7fff4);
  }
  .option-card.wrong{
    border-color:var(--danger);
    background:linear-gradient(135deg, #ffd2d2, #fff0f0);
  }
  .option-card.show-correct{
    border-style:dashed;
    border-color:var(--ok);
  }

  /* Feedback bubble */
  .feedback-zone{
    margin-top:14px;
    min-height:32px;
  }
  .feedback-chip{
    display:inline-flex;
    align-items:center;
    gap:.4rem;
    border-radius:999px;
    padding:.4rem .9rem;
    font-weight:700;
    font-size:.96rem;
    box-shadow:0 4px 10px rgba(0,0,0,.08);
  }
  .feedback-correct{
    background:var(--ok); color:#fff;
  }
  .feedback-wrong{
    background:var(--danger); color:#fff;
  }
  .feedback-correct small,
  .feedback-wrong small{
    font-weight:500;opacity:.9;
  }

  /* Progress / completion */
  .quiz-progress-bar{
    width:100%;height:8px;border-radius:999px;
    background:rgba(255,255,255,.3);overflow:hidden;
  }
  .quiz-progress-bar-inner{
    height:100%;border-radius:999px;
    background:linear-gradient(90deg,#ffe066,#1cc88a);
    width:0%;
    transition:width .25s ease;
  }

  .quiz-complete-screen{
    max-width:520px;margin:40px auto;
    text-align:center;
    padding:24px 18px;
    background:#fff;
    border-radius:24px;
    box-shadow:0 10px 26px rgba(0,0,0,.12);
  }

  @keyframes popIn{
    0%{transform:scale(.7);opacity:0}
    70%{transform:scale(1.05);opacity:1}
    100%{transform:scale(1);opacity:1}
  }
  .pop-in{animation:popIn .36s ease-out}
</style>

<?php
  $totalQuestions = count($qq);
?>

<section class="quiz-topbar">
  <div class="d-flex align-items-center justify-content-between">
    <div>    <span class="subject-label">
        <?= esc($quiz->subject_name ?? 'Subject') ?>
    </span>

      <h1 class="quiz-title mb-0">🎮 Practice: <?= esc($quiz->title) ?></h1>
      <?php if (!empty($quiz->instructions)): ?>
        <small class="d-none d-md-inline" style="opacity:.9">
          <?= esc($quiz->instructions) ?>
        </small>
      <?php else: ?>
        <small class="d-none d-md-inline" style="opacity:.9">
          Tap the cards to see if you are right. It will jump to the next question automatically!
        </small>
      <?php endif; ?>
    </div>

    <div class="d-flex align-items-center" style="gap:.5rem">
      <span class="pill" id="qPos">Q 1 / <?= $totalQuestions ?></span>
      <span class="pill pill-score">
        ⭐ Score:
        <span id="scoreNow">0</span>/<span><?= $totalQuestions ?></span>
      </span>
    </div>
  </div>
</section>

<section class="content">
  <div class="quiz-wrap">
    <div class="mb-2">
      <div class="quiz-progress-bar">
        <div class="quiz-progress-bar-inner" id="quizProgress"></div>
      </div>
    </div>

    <?php
      $qNo = 1; $index = 0;
      foreach ($qq as $row):
        $qid = (int)$row->question_id;
        $qt  = $row->question_type ?? 'mcq_single';
        $txt = $row->question ?? 'Question text';

        // --- Determine correct answer for practice (adjust field names if different) ---
        $dataCorrect = '';
        if (in_array($qt, ['mcq','mcq_single'])) {
            // Assume original correct option letter stored as correct_option (A/B/C/D)
            $origCorrect = $row->correct_option ?? '';
            $displayCorrect = $origCorrect;

            if (!empty($quiz->shuffle_options)
                && (int)$quiz->shuffle_options === 1
                && !empty($row->option_map)
                && $origCorrect
            ) {
                // option_map: [newLetter => origLetter]; find displayed letter for the original correct one
                foreach ($row->option_map as $newL => $origL) {
                    if ($origL === $origCorrect) {
                        $displayCorrect = $newL;
                        break;
                    }
                }
            }
            $dataCorrect = $displayCorrect;
        } elseif ($qt === 'true_false' || $qt === 'tf') {
            // Assume correct_answer holds "True" or "False"
            $dataCorrect = $row->correct_answer ?? '';
        } elseif ($qt === 'fill' || $qt === 'fill_blank' || $qt === 'short' || $qt === 'short_answer') {
            // For text questions, you can store a model answer; here we just keep it for display on wrong
            $dataCorrect = $row->correct_answer ?? '';
        }
    ?>
    <div
      class="q-card question-block mb-3"
      data-index="<?= $index ?>"
      data-qid="<?= $qid ?>"
      data-type="<?= esc($qt) ?>"
      data-correct="<?= esc($dataCorrect) ?>"
      id="qblock-<?= $qid ?>"
    >
      <!-- Header -->
      <div class="q-header">
        <div class="q-bubble"><?= $qNo ?></div>
        <p class="q-text mb-0"><?= nl2br(esc($txt)) ?></p>
      </div>

      <div class="q-body">
        <?php if (in_array($qt, ['mcq','mcq_single'])): ?>

          <?php
            // Decide options to show (shuffled vs original)
            $optionsToShow = [];
            if (!empty($quiz->shuffle_options) && (int)$quiz->shuffle_options === 1 && !empty($row->shuffled_options)) {
                $optionsToShow = $row->shuffled_options; // [newLetter => text]
            } else {
                $optionsToShow = [
                    'A' => $row->option_a ?? '',
                    'B' => $row->option_b ?? '',
                    'C' => $row->option_c ?? '',
                    'D' => $row->option_d ?? '',
                ];
            }
          ?>

          <div class="options-grid">
            <?php foreach ($optionsToShow as $letter => $label): ?>
              <?php if ($label === null || $label === '') continue; ?>
              <?php $id = 'q'.$qid.'_'. $letter; ?>
              <label class="option-card" data-letter="<?= esc($letter) ?>" for="<?= $id ?>">
                <input type="radio"
                       id="<?= $id ?>"
                       name="ans_<?= $qid ?>"
                       value="<?= esc($letter) ?>"
                >
                <div class="opt-letter"><?= esc($letter) ?></div>
                <div class="opt-text"><?= esc($label) ?></div>
              </label>
            <?php endforeach; ?>
          </div>

          <?php if (!empty($quiz->shuffle_options) && (int)$quiz->shuffle_options === 1 && !empty($row->option_map)): ?>
            <?php foreach ($row->option_map as $newL => $origL): ?>
              <input type="hidden"
                     name="optmap[<?= (int)$qid ?>][<?= esc($newL) ?>]"
                     value="<?= esc($origL) ?>">
            <?php endforeach; ?>
          <?php endif; ?>

        <?php elseif ($qt === 'true_false' || $qt === 'tf'): ?>

          <div class="options-grid">
            <?php foreach (['True','False'] as $val):
                  $id = 'q'.$qid.'_'.$val;
            ?>
              <label class="option-card" data-letter="<?= esc($val) ?>" for="<?= $id ?>">
                <input type="radio"
                       id="<?= $id ?>"
                       name="ans_<?= $qid ?>"
                       value="<?= esc($val) ?>">
                <div class="opt-letter"><?= $val === 'True' ? '✔' : '✖' ?></div>
                <div class="opt-text"><?= esc($val) ?></div>
              </label>
            <?php endforeach; ?>
          </div>

        <?php elseif ($qt === 'fill' || $qt === 'fill_blank'): ?>

          <input type="text"
                 class="form-control practice-text-answer"
                 data-qid="<?= $qid ?>"
                 placeholder="Type your answer and press Enter">

        <?php elseif ($qt === 'short' || $qt === 'short_answer'): ?>

          <textarea class="form-control practice-text-answer"
                    rows="3"
                    data-qid="<?= $qid ?>"
                    placeholder="Type your answer and press Enter"></textarea>

        <?php else: ?>

          <div class="text-muted">
            Unsupported question type for practice: <?= esc($qt) ?>
          </div>

        <?php endif; ?>

        <div class="feedback-zone" aria-live="polite"></div>
      </div>
    </div>
    <?php $qNo++; $index++; endforeach; ?>

    <!-- Completion screen (hidden initially) -->
    <div id="quizComplete" class="quiz-complete-screen d-none pop-in">
      <h2 class="mb-3">🎉 Practice Complete!</h2>
      <p class="lead mb-2">
        You answered <strong><span id="finalScore">0</span> / <?= $totalQuestions ?></strong> correctly.
      </p>
      <p class="mb-3">Great job! You can refresh the page to play again.</p>
    </div>
  </div>
</section>

<script>
(function(){
  const blocks = Array.from(document.querySelectorAll('.question-block'));
  const total  = blocks.length;
  const qPos   = document.getElementById('qPos');
  const progressEl = document.getElementById('quizProgress');
  const scoreNowEl = document.getElementById('scoreNow');
  const completeBox = document.getElementById('quizComplete');
  const finalScoreEl = document.getElementById('finalScore');

  let currentIndex = 0;
  let score = 0;

  function syncPosLabel(){
    qPos.textContent = `Q ${currentIndex+1} / ${total}`;
  }
  function syncProgress(){
    const pct = total > 0 ? ((currentIndex) / total) * 100 : 0;
    progressEl.style.width = pct + '%';
  }

  function showBlock(idx){
    blocks.forEach(b => b.classList.remove('active'));
    if (blocks[idx]) {
      blocks[idx].classList.add('active');
      currentIndex = idx;
      syncPosLabel();
      syncProgress();
    }
  }

  function showCompletion(){
    blocks.forEach(b => b.classList.remove('active'));
    completeBox.classList.remove('d-none');
    finalScoreEl.textContent = String(score);
    // full progress bar
    progressEl.style.width = '100%';
  }

  function setFeedback(qBlock, isCorrect, message, extra){
    const zone = qBlock.querySelector('.feedback-zone');
    if (!zone) return;
    const cls = isCorrect ? 'feedback-correct' : 'feedback-wrong';
    const icon = isCorrect ? '🎉' : '😅';
    zone.innerHTML = `
      <div class="feedback-chip ${cls} pop-in">
        <span>${icon}</span>
        <span>${message}${extra ? ' <small>'+extra+'</small>' : ''}</span>
      </div>
    `;
  }

  function lockQuestion(qBlock){
    qBlock.dataset.locked = '1';
  }

  function handleOptionClick(card){
    const qBlock = card.closest('.question-block');
    if (!qBlock || qBlock.dataset.locked === '1') return;

    const type    = (qBlock.dataset.type || '').toLowerCase();
    const correct = (qBlock.dataset.correct || '').trim();

    // clear previous states in this question
    qBlock.querySelectorAll('.option-card').forEach(c => {
      c.classList.remove('selected','correct','wrong','show-correct');
    });

    card.classList.add('selected');

    let chosen = (card.dataset.letter || '').trim();
    if (!chosen && card.querySelector('input')){
      chosen = (card.querySelector('input').value || '').trim();
    }

    let isCorrect = false;

    if (type === 'mcq' || type === 'mcq_single' || type === 'true_false' || type === 'tf') {
      if (chosen.toLowerCase() === correct.toLowerCase()) {
        isCorrect = true;
      }
    }

    if (isCorrect) {
      card.classList.add('correct');
      score++;
      scoreNowEl.textContent = String(score);
      setFeedback(qBlock, true, 'Correct!', 'Nice job, keep going!');
    } else {
      card.classList.add('wrong');

      // highlight correct card (if we know it)
      if (correct) {
        qBlock.querySelectorAll('.option-card').forEach(c=>{
          const letter = (c.dataset.letter || '').trim();
          if (letter.toLowerCase() === correct.toLowerCase()) {
            c.classList.add('show-correct');
          }
        });
      }

      const extraText = correct
        ? `Correct answer: ${correct}`
        : '';
      setFeedback(qBlock, false, 'Oops, that was not right.', extraText);
    }

    lockQuestion(qBlock);

    // Auto move to next question after 5 seconds
    setTimeout(() => {
      const nextIdx = currentIndex + 1;
      if (nextIdx < total) {
        showBlock(nextIdx);
      } else {
        showCompletion();
      }
    }, 5000);
  }

  // attach listeners to option cards
  document.querySelectorAll('.option-card').forEach(card => {
    card.addEventListener('click', function(e){
      e.preventDefault();
      handleOptionClick(card);
    });
  });

  // Simple handler for text questions: check when Enter is pressed (basic equality)
  document.querySelectorAll('.practice-text-answer').forEach(input => {
    input.addEventListener('keydown', function(e){
      if (e.key === 'Enter') {
        e.preventDefault();
        const qBlock = input.closest('.question-block');
        if (!qBlock || qBlock.dataset.locked === '1') return;
        const correct = (qBlock.dataset.correct || '').trim().toLowerCase();
        const userAns = (input.value || '').trim().toLowerCase();
        const isCorrect = correct && userAns === correct;

        if (isCorrect) {
          score++;
          scoreNowEl.textContent = String(score);
          setFeedback(qBlock, true, 'Correct!', '');
        } else {
          const extraText = correct ? `Model answer: ${qBlock.dataset.correct}` : '';
          setFeedback(qBlock, false, 'Nice try!', extraText);
        }
        lockQuestion(qBlock);

        setTimeout(() => {
          const nextIdx = currentIndex + 1;
          if (nextIdx < total) showBlock(nextIdx);
          else showCompletion();
        }, 5000);
      }
    });
  });

  // Init
  if (blocks.length) {
    showBlock(0);
    syncPosLabel();
    syncProgress();
  }
})();
</script>

<?= $this->endSection() ?>
