<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<!-- Keep all your existing CSS -->
<style>
  /* All your existing CSS stays here */
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
  /* ... rest of your CSS ... */
</style>

<?php
// Adaptive quiz specific variables
$isAdaptive = $quiz->is_adaptive ?? 0;
$currentLevel = $currentLevel ?? 1;
$totalLevels = $totalLevels ?? 1;
$levelInfo = $levelInfo ?? null;
$passingPercentage = $levelInfo->passing_percentage ?? 60;
$levelDifficulty = $levelInfo->base_difficulty ?? 'medium';
?>

<section class="quiz-topbar">
  <div class="d-flex align-items-center justify-content-between flex-wrap flex-md-nowrap">
    <div class="mb-1 mb-md-0">
      <h1 class="quiz-title mb-0">
        <?php if ($isAdaptive): ?>
          ?? Level <?= $currentLevel ?> of <?= $totalLevels ?>: 
        <?php endif; ?>
        <?= esc($quiz->title) ?>
      </h1>
      
      <?php if ($isAdaptive): ?>
        <div class="quiz-meta">
          <span class="badge badge-<?= $levelDifficulty === 'easy' ? 'success' : ($levelDifficulty === 'hard' ? 'danger' : 'warning') ?> mr-2">
            <?= ucfirst($levelDifficulty) ?> Difficulty
          </span>
          <span class="badge badge-info mr-2">
            Pass: <?= $passingPercentage ?>%
          </span>
          <span class="text-muted">
            Questions: <?= $quiz->questions_count ?? count($questions) ?>
          </span>
        </div>
      <?php else: ?>
        <!-- Keep your existing meta display -->
        <div class="quiz-meta">
          <?php if (!empty($classSection)): ?>
            <?= esc($classSection) ?> · 
          <?php endif; ?>
          <?= esc($subjectName ?? '') ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="d-flex align-items-center flex-wrap justify-content-end" style="gap:.5rem">
      <?php if ($isAdaptive): ?>
        <!-- Level Progress -->
        <div class="pill" style="background: rgba(103,89,255,0.15); color: var(--brand);">
          <i class="fas fa-layer-group"></i>
          Level <?= $currentLevel ?>/<?= $totalLevels ?>
        </div>
        
        <!-- Level Progress Bar -->
        <div class="progress" style="width: 120px; height: 20px;">
          <div class="progress-bar bg-success" 
               style="width: <?= ($currentLevel-1)/max($totalLevels,1)*100 ?>%">
            <small>Progress</small>
          </div>
        </div>
      <?php endif; ?>

      <?php $totalQ = count($questions ?? []); ?>
      <?php if ($totalQ > 0): ?>
        <div class="pill pill-questions" id="quiz-question-counter"
             data-total="<?= $totalQ ?>">
          <i class="fas fa-list-ol"></i>
          <span id="quiz-q-remaining"><?= $totalQ - 1 ?></span> questions left
        </div>
      <?php endif; ?>

      <?php if (!empty($quiz->time_limit_sec) && (int)$quiz->time_limit_sec > 0): ?>
        <div id="quiz-timer"
             class="timer-shell timer-ok"
             data-remaining="<?= (int)$quiz->time_limit_sec ?>">
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
  
  <?php if ($isAdaptive && $levelInfo): ?>
    <div class="mt-2 small">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <span class="text-info">
            <i class="fas fa-info-circle mr-1"></i>
            Score <?= $passingPercentage ?>% or more to unlock next level
          </span>
        </div>
        <?php if ($currentLevel > 1): ?>
          <div>
            <small class="text-muted">
              <i class="fas fa-trophy mr-1"></i>
              Previous level completed ?
            </small>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</section>

<section class="content">
  <form action="<?= base_url('student/quizzes/submit' . ($isAdaptive ? '-adaptive' : '')) ?>" 
        method="post" 
        id="attemptForm">
    <?= csrf_field() ?>
    <input type="hidden" name="attempt_id" value="<?= (int)$attemptId ?>">
    <input type="hidden" name="current_level" value="<?= (int)$currentLevel ?>">
    <input type="hidden" id="currentIndex" value="0">
    <input type="hidden" name="is_adaptive" value="<?= $isAdaptive ?>">

    <div class="quiz-wrap">
      <?php
        $qNo = 1; $index = 0;
        foreach ($questions as $row):
          $qid   = (int)$row->id ?? $row->question_id;
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
          <!-- Your existing question rendering logic stays the same -->
          <?php if (in_array($qt, ['mcq','mcq_single'])): ?>
            <!-- ... existing MCQ rendering ... -->
          <?php elseif ($qt === 'true_false' || $qt === 'tf'): ?>
            <!-- ... existing TF rendering ... -->
          <?php elseif ($qt === 'fill' || $qt === 'fill_blank'): ?>
            <!-- ... existing Fill rendering ... -->
          <?php elseif ($qt === 'short' || $qt === 'short_answer'): ?>
            <!-- ... existing Short Answer rendering ... -->
          <?php elseif ($qt === 'mcq_multi'): ?>
            <!-- ... existing MCQ Multi rendering ... -->
          <?php elseif ($qt === 'match'): ?>
            <!-- ... existing Match rendering ... -->
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
          <button type="button" class="btn btn-light btn-pill btn-prev" id="btnPrev">? Previous</button>
          
          <?php if ($isAdaptive): ?>
            <button type="button" class="btn btn-next btn-pill" id="btnSubmitLevel" 
                    data-mode="submit-level">
              Submit Level <?= $currentLevel ?>
            </button>
          <?php else: ?>
            <button type="button" class="btn btn-next btn-pill" id="btnNext" data-mode="next">Next ?</button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Zoom Modal (keep as is) -->
    <div class="modal fade" id="imgZoomModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:16px;overflow:hidden">
          <div class="modal-body p-0" style="background:#000">
            <img id="zoomImg" src="" alt="Zoom" style="width:100%;height:auto;display:block">
          </div>
          <div class="modal-footer py-2">
            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

  </form>
</section>

<!-- Level Result Modal (for adaptive quizzes) -->
<?php if ($isAdaptive): ?>
<div class="modal fade" id="levelResultModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius:20px; overflow:hidden">
      <div class="modal-body p-0">
        <!-- Header will be set dynamically -->
        <div class="text-center py-4 px-3" id="levelResultContent">
          <div class="mb-3" id="resultIcon">
            <!-- Icon will be set dynamically -->
          </div>
          <h4 id="resultTitle" class="mb-2"></h4>
          <h1 id="resultScore" class="display-4 mb-3"></h1>
          <p id="resultMessage" class="mb-3"></p>
          <p class="text-muted small mb-4" id="resultDetails"></p>
          
          <div class="d-flex justify-content-center gap-2" id="resultActions">
            <!-- Buttons will be set dynamically -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
// Adaptive Quiz Global Variables
const IS_ADAPTIVE = <?= $isAdaptive ? 'true' : 'false' ?>;
const CURRENT_LEVEL = <?= $currentLevel ?>;
const TOTAL_LEVELS = <?= $totalLevels ?>;
const PASSING_PERCENTAGE = <?= $passingPercentage ?>;
const ATTEMPT_ID = <?= $attemptId ?>;

/* -----------------------------------
   TIMER (same as before)
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
      // Handle timeout for adaptive quiz
      if (IS_ADAPTIVE) {
        submitLevel();
      } else {
        var quizForm = document.getElementById('attemptForm');
        if (quizForm) quizForm.submit();
      }
      return;
    }
    renderTime(remaining);
    updateColor();
  }, 1000);
})();

/* -----------------------------------
   NAVIGATION - ADAPTIVE VERSION
----------------------------------- */
const blocks = Array.from(document.querySelectorAll('.question-block'));
const currentIndexInput = document.getElementById('currentIndex');
const btnPrev  = document.getElementById('btnPrev');
const btnNextOrSubmit = IS_ADAPTIVE ? document.getElementById('btnSubmitLevel') : document.getElementById('btnNext');
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

  // For adaptive quizzes, only show submit button
  if (IS_ADAPTIVE && btnNextOrSubmit) {
    btnNextOrSubmit.textContent = `Submit Level ${CURRENT_LEVEL}`;
    btnNextOrSubmit.setAttribute('data-mode', 'submit-level');
  } else if (btnNextOrSubmit) {
    // Non-adaptive logic
    if (idx === totalQuestions - 1) {
      btnNextOrSubmit.textContent = 'Submit Quiz ?';
      btnNextOrSubmit.classList.remove('btn-next');
      btnNextOrSubmit.classList.add('btn-submit');
      btnNextOrSubmit.setAttribute('data-mode', 'submit');
    } else {
      btnNextOrSubmit.textContent = 'Next ?';
      btnNextOrSubmit.classList.remove('btn-submit');
      btnNextOrSubmit.classList.add('btn-next');
      btnNextOrSubmit.setAttribute('data-mode', 'next');
    }
  }

  // Update remaining questions counter
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

if (btnNextOrSubmit) {
  btnNextOrSubmit.addEventListener('click', () => {
    const mode = btnNextOrSubmit.getAttribute('data-mode') || 'next';

    if (mode === 'next') {
      setCurrent(parseInt(currentIndexInput.value, 10) + 1);
      return;
    }

    if (mode === 'submit-level') {
      submitLevel();
      return;
    }

    // mode === 'submit' ? submit non-adaptive quiz
    const form = document.getElementById('attemptForm');
    if (form) form.submit();
  });
}

/* -----------------------------------
   ADAPTIVE QUIZ: SUBMIT LEVEL
----------------------------------- */
async function submitLevel() {
  // Show loading state
  const submitBtn = document.getElementById('btnSubmitLevel');
  if (submitBtn) {
    const originalText = submitBtn.textContent;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Evaluating...';
    submitBtn.disabled = true;
  }

  try {
    // First, save all answers
    await saveAllAnswers();
    
    // Submit for evaluation
    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    formData.append('attempt_id', ATTEMPT_ID);
    formData.append('level_no', CURRENT_LEVEL);
    
    const response = await fetch('<?= base_url("student/quizzes/evaluate-level") ?>', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      showLevelResult(result.data);
    } else {
      alert('Error evaluating level: ' + (result.message || 'Unknown error'));
      if (submitBtn) {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      }
    }
  } catch (error) {
    console.error('Error submitting level:', error);
    alert('Network error. Please try again.');
    if (submitBtn) {
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
    }
  }
}

async function saveAllAnswers() {
  // Collect and save all answers before evaluation
  const answerPromises = [];
  
  document.querySelectorAll('.question-block').forEach(block => {
    const qid = block.getAttribute('data-qid');
    const type = getQuestionType(qid);
    const answer = collectAnswer(qid, type);
    
    if (answer !== null) {
      answerPromises.push(saveSingleAnswer(qid, type, answer));
    }
  });
  
  // Wait for all saves to complete
  await Promise.all(answerPromises);
}

function getQuestionType(qid) {
  // Get question type from the DOM
  const block = document.getElementById('qblock-' + qid);
  if (!block) return 'mcq_single';
  
  // Look for answer input to determine type
  const input = block.querySelector('.answer-input');
  if (input) {
    return input.getAttribute('data-type') || 'mcq_single';
  }
  
  return 'mcq_single';
}

function collectAnswer(qid, type) {
  const block = document.getElementById('qblock-' + qid);
  if (!block) return null;
  
  if (type === 'mcq_single' || type === 'tf') {
    const checked = block.querySelector(`input[name="ans_${qid}"]:checked`);
    return checked ? checked.value : null;
  }
  else if (type === 'mcq_multi') {
    const checkboxes = block.querySelectorAll(`input[name="ans_${qid}[]"]:checked`);
    const values = Array.from(checkboxes).map(cb => cb.value);
    return values.length > 0 ? values : null;
  }
  else if (type === 'fill' || type === 'short') {
    const input = block.querySelector(`input[name="ans_${qid}"], textarea[name="ans_${qid}"]`);
    return input ? input.value.trim() : null;
  }
  else if (type === 'match') {
    // Handle match questions
    const wrapper = block.querySelector('.match-dnd');
    if (wrapper) {
      const data = [];
      wrapper.querySelectorAll('.match-row').forEach(row => {
        const left = row.getAttribute('data-left') || '';
        const chip = row.querySelector('.match-chip');
        const value = chip ? (chip.getAttribute('data-value') || chip.textContent.trim()) : '';
        if (left && value) {
          data.push({ left, value });
        }
      });
      return data.length > 0 ? data : null;
    } else {
      // Text input match
      const data = [];
      block.querySelectorAll('.match-input').forEach(input => {
        const left = input.getAttribute('data-left') || '';
        const value = input.value.trim();
        if (left && value) {
          data.push({ left, value });
        }
      });
      return data.length > 0 ? data : null;
    }
  }
  
  return null;
}

async function saveSingleAnswer(qid, type, answer) {
  const formData = new URLSearchParams();
  formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
  formData.append('attempt_id', ATTEMPT_ID);
  formData.append('question_id', qid);
  formData.append('question_type', type);
  
  if (type === 'mcq_single' || type === 'tf') {
    formData.append('selected_option', answer);
  }
  else if (type === 'fill' || type === 'short') {
    formData.append('answer_text', answer);
  }
  else if (type === 'mcq_multi') {
    answer.forEach(val => formData.append('selected_options[]', val));
  }
  else if (type === 'match') {
    formData.append('answer_text', JSON.stringify(answer));
  }
  
  try {
    await fetch('<?= base_url("student/quizzes/save-answer") ?>', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: formData.toString()
    });
  } catch (error) {
    console.error('Error saving answer for Q' + qid, error);
  }
}

/* -----------------------------------
   SHOW LEVEL RESULT
----------------------------------- */
function showLevelResult(result) {
  const {
    score_percentage,
    passed,
    correct_count,
    total_questions,
    next_level_available,
    is_final_level
  } = result;
  
  const modal = document.getElementById('levelResultModal');
  const iconEl = document.getElementById('resultIcon');
  const titleEl = document.getElementById('resultTitle');
  const scoreEl = document.getElementById('resultScore');
  const messageEl = document.getElementById('resultMessage');
  const detailsEl = document.getElementById('resultDetails');
  const actionsEl = document.getElementById('resultActions');
  
  if (!modal) return;
  
  // Set content based on result
  if (passed) {
    iconEl.innerHTML = '<i class="fas fa-trophy fa-4x text-success"></i>';
    titleEl.textContent = 'Level Completed! ??';
    scoreEl.textContent = `${score_percentage}%`;
    scoreEl.className = 'display-4 mb-3 text-success';
    
    if (is_final_level) {
      messageEl.textContent = 'Congratulations! You have completed all levels!';
      detailsEl.textContent = `You scored ${score_percentage}% (${correct_count}/${total_questions} correct)`;
      
      actionsEl.innerHTML = `
        <button type="button" class="btn btn-success btn-lg" onclick="viewFinalResults()">
          View Final Results
        </button>
      `;
    } else {
      messageEl.textContent = 'You have unlocked the next level!';
      detailsEl.textContent = `You scored ${score_percentage}% (${correct_count}/${total_questions} correct). Required: ${PASSING_PERCENTAGE}%`;
      
      actionsEl.innerHTML = `
        <button type="button" class="btn btn-success btn-lg" onclick="proceedToNextLevel()">
          Proceed to Level ${CURRENT_LEVEL + 1} ?
        </button>
      `;
    }
  } else {
    iconEl.innerHTML = '<i class="fas fa-redo fa-4x text-warning"></i>';
    titleEl.textContent = 'Level Incomplete';
    scoreEl.textContent = `${score_percentage}%`;
    scoreEl.className = 'display-4 mb-3 text-warning';
    messageEl.textContent = `You need ${PASSING_PERCENTAGE}% to proceed`;
    detailsEl.textContent = `You scored ${score_percentage}% (${correct_count}/${total_questions} correct). Try again to improve your score!`;
    
    actionsEl.innerHTML = `
      <button type="button" class="btn btn-warning btn-lg mr-2" onclick="retryLevel()">
        <i class="fas fa-redo mr-1"></i> Try Again
      </button>
      <button type="button" class="btn btn-outline-secondary btn-lg" data-dismiss="modal">
        Review Answers
      </button>
    `;
  }
  
  // Show modal
  if (window.jQuery && jQuery.fn.modal) {
    jQuery(modal).modal('show');
  }
}

/* -----------------------------------
   LEVEL RESULT ACTIONS
----------------------------------- */
function proceedToNextLevel() {
  // Redirect to next level
  window.location.href = `<?= base_url("student/quizzes/play/") ?>${<?= $quiz->quiz_id ?>}?level=${CURRENT_LEVEL + 1}`;
}

function retryLevel() {
  // Reset current level attempt
  fetch('<?= base_url("student/quizzes/reset-level-attempt") ?>', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: new URLSearchParams({
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
      'attempt_id': ATTEMPT_ID,
      'level_no': CURRENT_LEVEL
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Reload the page to restart the level
      window.location.reload();
    } else {
      alert('Error resetting level: ' + (data.message || 'Unknown error'));
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Network error. Please try again.');
  });
}

function viewFinalResults() {
  window.location.href = `<?= base_url("student/quizzes/result/") ?>${ATTEMPT_ID}`;
}

/* -----------------------------------
   AUTOSAVE & OTHER FUNCTIONS
   (Keep your existing autosave, match drag & drop, etc.)
----------------------------------- */
// Your existing autosave, match drag & drop, and other functions remain the same
// ... (Keep all your existing JavaScript functions)

</script>

<?= $this->endSection() ?>