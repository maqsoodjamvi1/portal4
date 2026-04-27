<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>
<style>
/* ============================================= */
/* 1. CSS VARIABLES & GLOBAL STYLES */
/* ============================================= */
:root {
  --bg: #050712;
  --card: #161a33;
  --brand: #ff7b00;
  --brand-2: #ffcc00;
  --ok: #20e3b2;
  --warn: #ffd32a;
  --danger: #ff4d4d;
  --ink: #f5f7ff;
}

body {
  background: var(--bg);
}

/* Hide old header */
.quiz-topbar {
  display: none;
}

/* ============================================= */
/* 2. COMPACT HEADER STYLES */
/* ============================================= */
.quiz-compact-header {
  position: sticky;
  top: 0;
  z-index: 1000;
  background: linear-gradient(90deg, #161a33, #1a1f3a);
  padding: 8px 0;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.4);
  border-bottom: 1px solid #ff7b00;
}

.compact-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 15px;
  max-width: 900px;
  margin: 0 auto;
}

/* Header Items */
.header-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 80px;
}

/* Timer */
.timer-item {
  align-items: flex-start;
}

.timer-item .timer-icon {
  font-size: 1rem;
  margin-bottom: 2px;
  filter: drop-shadow(0 0 3px rgba(255, 123, 0, 0.5));
}

.timer-display {
  font-family: 'Courier New', monospace;
  font-weight: 700;
  font-size: 1.1rem;
  color: #fff;
  letter-spacing: 1px;
  text-shadow: 0 0 5px rgba(255, 255, 255, 0.3);
}

/* Question Counter */
.question-item {
  align-items: center;
}

.question-counter {
  display: flex;
  align-items: baseline;
  font-weight: 900;
  color: #fff;
  margin-bottom: 2px;
}

.counter-current {
  font-size: 1.6rem;
  color: #ffd700;
  text-shadow: 0 0 8px rgba(255, 215, 0, 0.5);
}

.counter-separator {
  font-size: 1rem;
  margin: 0 3px;
  color: #888;
  font-weight: 600;
}

.counter-total {
  font-size: 1.2rem;
  color: #aaa;
  font-weight: 700;
}

.question-label {
  font-size: 0.7rem;
  color: #888;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

/* Score */
.score-item {
  align-items: flex-end;
}

.score-counter {
  display: flex;
  align-items: center;
  font-weight: 900;
  color: #fff;
  margin-bottom: 2px;
}

.score-icon {
  font-size: 1rem;
  margin-right: 5px;
  filter: drop-shadow(0 0 3px rgba(255, 215, 0, 0.5));
}

.score-current {
  font-size: 1.3rem;
  color: #20e3b2;
  text-shadow: 0 0 5px rgba(32, 227, 178, 0.5);
}

.score-separator {
  font-size: 0.9rem;
  margin: 0 3px;
  color: #888;
  font-weight: 600;
}

.score-total {
  font-size: 1.1rem;
  color: #aaa;
  font-weight: 700;
}

.score-label {
  font-size: 0.7rem;
  color: #888;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

/* ============================================= */
/* 3. QUIZ LAYOUT & QUESTION STYLES */
/* ============================================= */
.quiz-wrap {
  max-width: 900px;
  margin: 10px auto 70px;
  padding: 0 14px 20px;
}

.q-card {
  background: var(--card);
  border: 0;
  border-radius: 22px;
  overflow: hidden;
  box-shadow: 0 8px 22px rgba(0, 0, 0, 0.5);
  color: #fff;
  opacity: 0;
  transform: translateX(20px);
  transition: opacity 0.25s ease, transform 0.25s ease;
  display: none;
}

.question-block.active {
  display: block;
  opacity: 1;
  transform: translateX(0);
}

.q-header {
  background: linear-gradient(90deg, rgba(255, 123, 0, 0.25), rgba(255, 204, 0, 0.3));
  padding: 16px 18px;
  display: flex;
  gap: 12px;
}

.q-bubble {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: #ff9500;
  color: #fff;
  font-weight: 900;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 1.2rem;
  box-shadow: 0 4px 10px rgba(255, 149, 0, 0.4);
}

.q-text {
  color: #fff;
  font-size: 1.1rem;
  font-weight: 600;
}

.q-body {
  padding: 18px 18px 10px;
}

/* ============================================= */
/* 4. MCQ OPTIONS STYLES */
/* ============================================= */
.options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 1rem;
}

.option-card {
  background: #1f2649;
  border: 2px solid transparent;
  border-radius: 18px;
  padding: 1rem;
  cursor: pointer;
  color: #fff;
  display: flex;
  align-items: flex-start;
  gap: 0.7rem;
  min-height: 75px;
  transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s, border-color 0.15s;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
  position: relative;
}

.option-card:hover {
  transform: translateY(-3px) scale(1.02);
  box-shadow: 0 8px 18px rgba(0, 0, 0, 0.6);
}

.option-card:active {
  transform: scale(0.97) translateY(1px);
}

.option-card input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.opt-letter {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: #324a82;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 900;
  color: #ffd700;
  font-size: 1rem;
  flex: 0 0 auto;
}

.option-card.selected {
  border-color: #ffd700;
  background: #26326a;
}

.option-card.correct {
  border-color: var(--ok);
  background: rgba(32, 227, 178, 0.25);
}

.option-card.wrong {
  border-color: var(--danger);
  background: rgba(255, 77, 77, 0.25);
}

.option-card.show-correct {
  border-style: dashed;
  border-color: var(--ok);
}

/* ============================================= */
/* 5. FEEDBACK & PROGRESS STYLES */
/* ============================================= */
.feedback-zone {
  margin-top: 14px;
  min-height: 34px;
}

.feedback-chip {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 1rem;
  border-radius: 18px;
  font-size: 1rem;
  font-weight: 700;
  gap: 0.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

.feedback-correct {
  background: var(--ok);
  color: #003326;
}

.feedback-wrong {
  background: var(--danger);
}

.quiz-progress-bar {
  background: #333;
  border-radius: 999px;
  height: 10px;
  overflow: hidden;
}

.quiz-progress-bar-inner {
  background: linear-gradient(90deg, #ffd700, #00ff99);
  height: 100%;
  width: 0%;
  transition: width 0.3s;
}

/* ============================================= */
/* 6. MATCH QUESTION STYLES */
/* ============================================= */
.match-wrapper {
  max-width: 100%;
  overflow: hidden;
}

.match-instruction {
  background: linear-gradient(90deg, rgba(255, 123, 0, 0.2), rgba(255, 204, 0, 0.2));
  border-radius: 10px;
  padding: 8px 12px;
  margin-bottom: 12px;
  text-align: center;
  font-size: 0.85rem;
}

.instruction-text {
  color: #ffd700;
  font-weight: 600;
}

.mobile-text {
  display: none;
}

.match-columns {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  min-height: 350px;
  align-items: start;
}

.left-column,
.right-column {
  background: #1a234a;
  border-radius: 16px;
  padding: 15px;
  border: 2px solid #2a3360;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.left-column h6,
.right-column h6 {
  color: #ffd700;
  text-align: center;
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 2px solid #324a82;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.left-items,
.right-items {
  flex: 1;
  overflow-y: auto;
  padding-right: 5px;
}

/* Left items */
.left-item {
  background: #2a3360;
  border-radius: 10px;
  padding: 12px 15px;
  color: #fff;
  font-weight: 500;
  border: 2px solid transparent;
  display: block;
  word-break: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
}

/* Right items */
.right-item {
  background: linear-gradient(135deg, #3a4a8a, #2a3a7a);
  border: 2px solid #4a6acc;
  border-radius: 12px;
  padding: 12px 15px;
  margin-bottom: 10px;
  color: #fff;
  font-weight: 500;
  cursor: pointer;
  user-select: none;
  transition: all 0.3s ease;
  position: relative;
  word-break: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
  display: flex;
  align-items: flex-start;
}

.right-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
  border-color: #ffd700;
}

.right-item.selected {
  background: linear-gradient(135deg, #ff9500, #ffcc00);
  border-color: #ffd700;
  color: #000;
  font-weight: 700;
  transform: scale(0.98);
  box-shadow: 0 0 20px rgba(255, 149, 0, 0.5);
}

.right-item.used {
  opacity: 0.4;
  cursor: not-allowed;
  transform: none !important;
}

.right-item .item-icon {
  display: inline-block;
  margin-right: 10px;
  color: #ffd700;
  font-size: 0.9rem;
}

/* Drag zones */
.drag-zone {
  min-height: 65px;
  margin-bottom: 10px;
  border: 2px dashed #4a5aaa;
  border-radius: 12px;
  padding: 12px;
  transition: all 0.3s ease;
  background: rgba(42, 51, 96, 0.3);
  cursor: pointer;
  position: relative;
}

.drag-zone.empty {
  background: rgba(42, 51, 96, 0.3);
}

.drag-zone.highlight {
  background: rgba(255, 215, 0, 0.15);
  border-color: #ffd700;
  border-style: solid;
}

.drag-zone.matched {
  border-color: var(--ok);
  border-style: solid;
  background: rgba(32, 227, 178, 0.1);
}

.drag-zone.error {
  border-color: var(--danger);
  background: rgba(255, 77, 77, 0.1);
}

.drag-zone .left-item {
  margin: 0;
  pointer-events: none;
}

/* Dropped items */
.dropped-item {
  background: linear-gradient(135deg, #26326a, #1a2a5a);
  border: 2px solid #ffd700;
  border-radius: 10px;
  padding: 10px 12px;
  margin-top: 8px;
  color: #fff;
  font-weight: 500;
  animation: dropIn 0.3s ease;
  position: relative;
  font-size: 0.9rem;
  word-break: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
}

.dropped-item .remove-btn {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 22px;
  height: 22px;
  background: var(--danger);
  border: 2px solid #fff;
  border-radius: 50%;
  color: white;
  font-size: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 10;
}

/* Match status */
.match-status {
  padding: 8px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
  margin-top: 12px;
  font-size: 0.85rem;
}

.match-progress {
  background: linear-gradient(90deg, #ffd700, #00ff99);
  transition: width 0.3s ease;
}

/* Match result */
.match-result {
  margin-top: 8px;
  font-size: 0.85rem;
  padding: 5px 10px;
  border-radius: 6px;
}

.match-result.correct {
  background: rgba(32, 227, 178, 0.2);
  color: var(--ok);
}

.match-result.incorrect {
  background: rgba(255, 77, 77, 0.2);
  color: var(--danger);
}

/* ============================================= */
/* 7. COMPLETION SCREEN */
/* ============================================= */
.quiz-complete-screen {
  background: #1a1d2d;
  color: #fff;
  border-radius: 22px;
  padding: 32px 18px;
  margin-top: 40px;
  text-align: center;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
}

/* ============================================= */
/* 8. ANIMATIONS */
/* ============================================= */
@keyframes dropIn {
  0% {
    transform: scale(0.8);
    opacity: 0;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

@keyframes highlightPulse {
  0% {
    border-color: #ffd700;
    box-shadow: 0 0 0 0 rgba(255, 215, 0, 0.4);
  }
  50% {
    border-color: #ff9500;
    box-shadow: 0 0 0 5px rgba(255, 215, 0, 0);
  }
  100% {
    border-color: #ffd700;
    box-shadow: 0 0 0 0 rgba(255, 215, 0, 0);
  }
}

@keyframes confetti-fall {
  0% {
    transform: translate3d(0, 0, 0) rotateZ(0deg);
  }
  100% {
    transform: translate3d(var(--confetti-x, 0px), 110vh, 0) rotateZ(360deg);
  }
}

@keyframes popIn {
  0% {
    transform: scale(0.7);
    opacity: 0;
  }
  70% {
    transform: scale(1.05);
    opacity: 1;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.pop-in {
  animation: popIn 0.36s ease-out;
}

.confetti-piece {
  position: fixed;
  width: 8px;
  height: 14px;
  background: #ffd700;
  top: -20px;
  z-index: 2000;
  opacity: 0.9;
  border-radius: 2px;
  animation: confetti-fall 1.8s linear forwards;
}

/* ============================================= */
/* 9. MINIMALIST MATCH VARIANT */
/* ============================================= */
.minimal-match .match-columns {
  gap: 8px;
}

.minimal-match .left-column,
.minimal-match .right-column {
  padding: 12px;
  border-radius: 12px;
}

.minimal-match .left-column h6,
.minimal-match .right-column h6 {
  font-size: 0.85rem;
  margin-bottom: 12px;
  padding-bottom: 8px;
}

.minimal-match .drag-zone {
  min-height: 50px;
  padding: 10px;
  margin-bottom: 8px;
  border-radius: 10px;
}

.minimal-match .right-item {
  min-height: 50px;
  padding: 10px;
  margin-bottom: 8px;
  border-radius: 10px;
  font-size: 0.85rem;
}

.minimal-match .item-icon {
  display: none;
}

.minimal-match .left-item,
.minimal-match .right-item {
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  min-height: 46px;
  padding: 8px;
}

/* ============================================= */
/* 10. RESPONSIVE STYLES */
/* ============================================= */

/* Mobile styles */
@media (max-width: 768px) {
  /* Header */
  .compact-header-row {
    padding: 0 10px;
  }
  
  .header-item {
    min-width: 70px;
  }
  
  .timer-display {
    font-size: 1rem;
  }
  
  .counter-current {
    font-size: 1.4rem;
  }
  
  .counter-total {
    font-size: 1rem;
  }
  
  .score-current {
    font-size: 1.1rem;
  }
  
  .score-total {
    font-size: 1rem;
  }
  
  .question-label,
  .score-label {
    font-size: 0.65rem;
  }
  
  .mobile-text {
    display: inline;
  }
  
  .desktop-text {
    display: none;
  }
  
  /* Match questions */
  .match-columns {
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    min-height: 320px;
    width: 100%;
  }
  
  .left-column,
  .right-column {
    padding: 12px;
    height: 380px;
    min-width: 0;
  }
  
  .left-column h6,
  .right-column h6 {
    font-size: 0.85rem;
    margin-bottom: 12px;
    padding-bottom: 8px;
  }
  
  .drag-zone,
  .right-item {
    min-height: 55px;
    padding: 10px;
    margin-bottom: 8px;
    border-radius: 10px;
  }
  
  .drag-zone .left-item,
  .right-item {
    font-size: 0.85rem;
    line-height: 1.3;
  }
  
  .right-item .item-icon {
    margin-right: 8px;
    font-size: 0.8rem;
  }
  
  .dropped-item {
    padding: 8px 10px;
    font-size: 0.85rem;
  }
  
  .dropped-item .remove-btn {
    width: 20px;
    height: 20px;
    font-size: 10px;
    top: -6px;
    right: -6px;
  }
  
  .drag-zone.highlight {
    animation: none;
    border-style: solid;
  }
  
  /* Minimalist match on mobile */
  .minimal-match .match-columns {
    gap: 6px;
  }
  
  .minimal-match .left-column,
  .minimal-match .right-column {
    padding: 10px;
    height: 360px;
  }
  
  .minimal-match .drag-zone,
  .minimal-match .right-item {
    min-height: 45px;
    padding: 8px;
    font-size: 0.8rem;
  }
  
  .minimal-match .left-item,
  .minimal-match .right-item {
    min-height: 42px;
    padding: 6px;
  }
}

/* Very small phones */
@media (max-width: 480px) {
  .compact-header-row {
    padding: 0 8px;
  }
  
  .header-item {
    min-width: 60px;
  }
  
  .timer-display {
    font-size: 0.9rem;
  }
  
  .counter-current {
    font-size: 1.2rem;
  }
  
  .counter-total {
    font-size: 0.9rem;
  }
  
  .score-current {
    font-size: 1rem;
  }
  
  .score-total {
    font-size: 0.9rem;
  }
  
  .question-label,
  .score-label {
    font-size: 0.6rem;
  }
  
  .score-icon {
    font-size: 0.9rem;
    margin-right: 3px;
  }
}

@media (max-width: 375px) {
  .match-columns {
    gap: 6px;
  }
  
  .left-column,
  .right-column {
    padding: 10px;
    height: 350px;
  }
  
  .drag-zone,
  .right-item {
    min-height: 48px;
    padding: 8px;
    font-size: 0.8rem;
  }
  
  .right-item .item-icon {
    margin-right: 6px;
  }
}

/* Scrollbar styling */
.left-items::-webkit-scrollbar,
.right-items::-webkit-scrollbar {
  width: 4px;
}

@media (max-width: 768px) {
  .left-items::-webkit-scrollbar,
  .right-items::-webkit-scrollbar {
    width: 3px;
  }
}

.left-items::-webkit-scrollbar-track,
.right-items::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}

.left-items::-webkit-scrollbar-thumb,
.right-items::-webkit-scrollbar-thumb {
  background: var(--brand);
  border-radius: 10px;
}

/* ============================================= */
/* 11. SINGLE LINE HEADER VARIANT */
/* ============================================= */
.compact-header-row.single-line {
  gap: 20px;
}

.header-item.single-line {
  flex-direction: row;
  align-items: center;
  gap: 8px;
  min-width: auto;
}

.header-item.single-line .timer-icon,
.header-item.single-line .score-icon {
  margin: 0;
  font-size: 1.1rem;
}

.header-item.single-line .question-counter {
  margin: 0;
}

.header-item.single-line .question-label,
.header-item.single-line .score-label {
  display: none;
}

/* ============================================= */
/* 12. AUTO-PROGRESS INDICATOR */
/* ============================================= */
.auto-progress-indicator {
  position: absolute;
  bottom: 10px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 5px 15px;
  border-radius: 20px;
  font-size: 0.85rem;
  z-index: 10;
  display: flex;
  align-items: center;
  gap: 8px;
  animation: fadeInUp 0.3s ease;
}

.auto-progress-indicator .countdown {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: var(--ok);
  color: #000;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.8rem;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translate(-50%, 10px);
  }
  to {
    opacity: 1;
    transform: translate(-50%, 0);
  }
}
</style>
<?php
  $totalQuestions = count($qq);
  $subjectName = $quiz->subject_name ?? ($quiz->subject ?? 'Subject');
?>
<?php $totalQuestions = count($qq); ?>
<!-- Compact Header -->
<header class="quiz-compact-header">
  <div class="compact-header-row">
    <!-- Timer - Left -->
    <div class="header-item timer-item">
      <div class="timer-icon">⏱️</div>
      <div class="timer-display">
        <span id="timerMinutes">00</span>:<span id="timerSeconds">00</span>
      </div>
    </div>
    
    <!-- Question Counter - Center -->
    <div class="header-item question-item">
      <div class="question-counter">
        <span class="counter-current" id="currentQuestion">1</span>
        <span class="counter-separator">/</span>
        <span class="counter-total" id="totalQuestions"><?= $totalQuestions ?></span>
      </div>
      <div class="question-label">Question</div>
    </div>
    
    <!-- Score - Right -->
    <div class="header-item score-item">
      <div class="score-counter">
        <span class="score-icon">⭐</span>
        <span class="score-current" id="currentScore">0</span>
        <span class="score-separator">/</span>
        <span class="score-total"><?= $totalQuestions ?></span>
      </div>
      <div class="score-label">Score</div>
    </div>
  </div>
</header>
<section class="content">
  <div class="quiz-wrap">
    <div class="mb-3">
      <div class="quiz-progress-bar"><div class="quiz-progress-bar-inner" id="quizProgress"></div></div>
    </div>

    <?php $qNo = 1; $index = 0; foreach ($qq as $row): ?>
      <?php
        $qid = (int) ($row->question_id ?? 0);
        $qt  = strtolower(trim((string)($row->question_type ?? 'mcq_single')));
        $txt = (string)($row->question ?? '');

        // Normalize question types
        if (in_array($qt, ['mcq','single'])) $qt = 'mcq_single';
        if (in_array($qt, ['mcq_multiple','multiple'])) $qt = 'mcq_multi';
        if (in_array($qt, ['true_false','true/false'])) $qt = 'tf';
        if (in_array($qt, ['fill_blank','fill_blanks','fib'])) $qt = 'fill';
        if (in_array($qt, ['short_answer'])) $qt = 'short';
        if (in_array($qt, ['matching','match_the_column'])) $qt = 'match';

        // Options JSON
        $optionsJson = (string)($row->options_json ?? '');
        $safeOptionsJson = $optionsJson ?: '';

        // Correct for MCQ single
        $dataCorrect = '';
        if ($qt === 'mcq_single') {
          $dataCorrect = (string)($row->correct_option ?? '');
        }

        // For TF/fill/short if correct_answer exists
        if (in_array($qt, ['tf','fill','short']) && isset($row->answer_text)) {
          $dataCorrect = (string)($row->answer_text ?? '');
        }
      ?>

      <div class="q-card question-block mb-3"
           data-index="<?= $index ?>"
           data-qid="<?= $qid ?>"
           data-type="<?= esc($qt) ?>"
           data-correct="<?= esc($dataCorrect) ?>"
           data-options-json="<?= esc($safeOptionsJson) ?>"
           id="qblock-<?= $qid ?>">

        <div class="q-header">
          <div class="q-bubble"><?= $qNo ?></div>
          <p class="q-text mb-0"><?= nl2br(esc($txt)) ?></p>
        </div>

        <div class="q-body">

          <?php if ($qt === 'mcq_single'): ?>
            <?php
              $optionsToShow = [
                'A' => (string)($row->option_a ?? ''),
                'B' => (string)($row->option_b ?? ''),
                'C' => (string)($row->option_c ?? ''),
                'D' => (string)($row->option_d ?? ''),
              ];
            ?>
            <div class="options-grid">
              <?php foreach ($optionsToShow as $letter => $label): ?>
                <?php if ($label === '') continue; $id = 'q'.$qid.'_'.$letter; ?>
                <label class="option-card" data-letter="<?= esc($letter) ?>" for="<?= $id ?>">
                  <input type="radio" id="<?= $id ?>" name="ans_<?= $qid ?>" value="<?= esc($letter) ?>">
                  <div class="opt-letter"><?= esc($letter) ?></div>
                  <div class="opt-text"><?= esc($label) ?></div>
                </label>
              <?php endforeach; ?>
            </div>

          <?php elseif ($qt === 'tf'): ?>
            <div class="options-grid">
              <?php foreach (['True','False'] as $val): $id = 'q'.$qid.'_'.$val; ?>
                <label class="option-card" data-letter="<?= esc($val) ?>" for="<?= $id ?>">
                  <input type="radio" id="<?= $id ?>" name="ans_<?= $qid ?>" value="<?= esc($val) ?>">
                  <div class="opt-letter"><?= $val === 'True' ? '✔' : '✖' ?></div>
                  <div class="opt-text"><?= esc($val) ?></div>
                </label>
              <?php endforeach; ?>
            </div>

          <?php elseif ($qt === 'fill'): ?>
            <input type="text" class="form-control practice-text-answer"
                   data-qid="<?= $qid ?>"
                   placeholder="Type your answer and press Enter">

          <?php elseif ($qt === 'short'): ?>
            <textarea class="form-control practice-text-answer" rows="3"
                      data-qid="<?= $qid ?>"
                      placeholder="Type your answer and press Enter"></textarea>

          <?php elseif ($qt === 'mcq_multi'): ?>
            <div class="text-muted mb-2" style="font-size:.9rem">
              ✅ Select all correct options, then press <b>Check Answer</b>
            </div>
            <div class="options-grid mcq-multi-zone" data-qid="<?= $qid ?>">
              <!-- options are rendered by JS using options_json.options -->
            </div>
            <button class="btn btn-warning match-check-btn btn-check-multi" type="button" data-qid="<?= $qid ?>">
              ✅ Check Answer
            </button>
<?php elseif ($qt === 'match'): ?>
<div class="match-wrapper" data-qid="<?= $qid ?>">
    
    
    <div class="match-container" data-qid="<?= $qid ?>">
        <!-- Will be populated by JavaScript -->
    </div>
    
    <div class="match-status mt-2">
        <small class="text-muted d-block mb-1">
            Progress: <span class="match-count">0</span>/<span class="match-total">0</span>
            <span class="auto-notice" style="display:none; color:var(--ok);">
                <i class="fas fa-spinner fa-spin"></i> Checking...
            </span>
        </small>
    </div>
</div>


          <?php else: ?>
            <div class="text-muted">Unsupported question type for practice: <?= esc($qt) ?></div>
          <?php endif; ?>

          <div class="feedback-zone" aria-live="polite"></div>
        </div>
      </div>

    <?php $qNo++; $index++; endforeach; ?>

    <div id="quizComplete" class="quiz-complete-screen d-none pop-in">
      <h2 class="mb-3">🎉 Practice Complete!</h2>
      <p class="lead mb-2">You answered <strong><span id="finalScore">0</span> / <?= $totalQuestions ?></strong> correctly.</p>
      <p class="mb-3">Refresh the page to practice again.</p>
    </div>
  </div>
</section>

<audio id="soundCorrect" src="<?= base_url('assets/sounds/quiz-correct.wav') ?>"></audio>
<audio id="soundWrong"   src="<?= base_url('assets/sounds/quiz-wrong.wav') ?>"></audio>
<script>
(function(){
  // =============================================
  // 1. VARIABLES & ELEMENT SELECTORS
  // =============================================
  const blocks = Array.from(document.querySelectorAll('.question-block'));
  const total = blocks.length;
  
  // Header elements (using new structure)
  const currentQuestionEl = document.getElementById('currentQuestion');
  const totalQuestionsEl = document.getElementById('totalQuestions');
  const currentScoreEl = document.getElementById('currentScore');
  const timerMinutesEl = document.getElementById('timerMinutes');
  const timerSecondsEl = document.getElementById('timerSeconds');
  
  // Legacy elements (remove if not used)
  const qPos = document.getElementById('qPos');
  const progressEl = document.getElementById('quizProgress');
  const scoreNowEl = document.getElementById('scoreNow');
  const starStrip = document.getElementById('starStrip');
  
  // Other elements
  const completeBox = document.getElementById('quizComplete');
  const finalScoreEl = document.getElementById('finalScore');
  const soundCorrect = document.getElementById('soundCorrect');
  const soundWrong = document.getElementById('soundWrong');
  
  // Quiz state
  let currentIndex = 0;
  let score = 0;
  let quizTimer;
  let secondsElapsed = 0;
  let isTimerRunning = true;
  
  // =============================================
  // 2. UTILITY FUNCTIONS
  // =============================================
  
  /**
   * Safely parse JSON
   */
  function safeJsonParse(str) {
    try { 
      return JSON.parse(str); 
    } catch(e) { 
      return null; 
    }
  }
  
  /**
   * Play sound effect
   */
  function playSound(el) {
    if (!el) return;
    try { 
      el.currentTime = 0; 
      el.play().catch(() => {}); 
    } catch(e) {}
  }
  
  /**
   * Escape HTML special characters
   */
  function escapeHtml(str) {
    return (str ?? '').toString()
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", "&#039;");
  }
  
  /**
   * Escape attribute values
   */
  function escapeAttr(str) {
    return escapeHtml(str).replaceAll('`', '&#096;');
  }
  
  /**
   * Truncate text if too long
   */
  function truncateText(text, maxLength) {
    if (!text) return '';
    text = text.toString().trim();
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
  }
  
  /**
   * Check if device is mobile
   */
  function isMobileDevice() {
    return window.innerWidth <= 768 || 
           /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
  }
  
  /**
   * Shuffle array (Fisher-Yates)
   */
  function shuffleArray(array) {
    const newArray = [...array];
    for (let i = newArray.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [newArray[i], newArray[j]] = [newArray[j], newArray[i]];
    }
    return newArray;
  }
  
  // =============================================
  // 3. TIMER FUNCTIONS
  // =============================================
  
  /**
   * Start the quiz timer
   */
  function startTimer() {
    quizTimer = setInterval(() => {
      if (isTimerRunning) {
        secondsElapsed++;
        updateTimerDisplay();
      }
    }, 1000);
  }
  
  /**
   * Update timer display in header
   */
  function updateTimerDisplay() {
    if (!timerMinutesEl || !timerSecondsEl) return;
    
    const minutes = Math.floor(secondsElapsed / 60);
    const seconds = secondsElapsed % 60;
    
    timerMinutesEl.textContent = minutes.toString().padStart(2, '0');
    timerSecondsEl.textContent = seconds.toString().padStart(2, '0');
  }
  
  // =============================================
  // 4. HEADER UPDATE FUNCTIONS
  // =============================================
  
  /**
   * Update question counter in header
   */
  function updateQuestionCounter() {
    if (currentQuestionEl) {
      currentQuestionEl.textContent = currentIndex + 1;
    }
    if (totalQuestionsEl && !totalQuestionsEl.textContent) {
      totalQuestionsEl.textContent = total;
    }
  }
  
  /**
   * Update score display in header
   */
  function updateScoreDisplay() {
    if (currentScoreEl) {
      currentScoreEl.textContent = score;
    }
    // Legacy support
    if (scoreNowEl) {
      scoreNowEl.textContent = score;
    }
  }
  
  /**
   * Update star rating display
   */
  function updateStars() {
    if (!starStrip) return;
    
    const ratio = total > 0 ? score / total : 0;
    const starCount = Math.round(ratio * 5);
    
    starStrip.querySelectorAll('span').forEach(span => {
      const n = parseInt(span.getAttribute('data-star'), 10);
      span.textContent = (n <= starCount) ? '★' : '☆';
    });
  }
  
  // =============================================
  // 5. QUESTION NAVIGATION
  // =============================================
  
  /**
   * Show specific question block
   */
  function showBlock(idx) {
    blocks.forEach(b => b.classList.remove('active'));
    
    if (blocks[idx]) {
      blocks[idx].classList.add('active');
      currentIndex = idx;
      updateQuestionCounter();
      
      // Legacy progress bar
      if (progressEl) {
        const pct = total > 0 ? ((currentIndex) / total) * 100 : 0;
        progressEl.style.width = pct + '%';
      }
      
      // Legacy position label
      if (qPos) {
        qPos.textContent = `Q ${currentIndex + 1} / ${total}`;
      }
    }
  }
  
  /**
   * Move to next question
   */
  function goNext() {
    setTimeout(() => {
      const nextIdx = currentIndex + 1;
      if (nextIdx < total) {
        showBlock(nextIdx);
      } else {
        showCompletion();
      }
    }, 1600);
  }
  
  // =============================================
  // 6. FEEDBACK & UTILITIES
  // =============================================
  
  /**
   * Show feedback message for a question
   */
  function setFeedback(qBlock, isCorrect, message, extra) {
    const zone = qBlock.querySelector('.feedback-zone');
    if (!zone) return;
    
    const cls = isCorrect ? 'feedback-correct' : 'feedback-wrong';
    const icon = isCorrect ? '🎉' : '😅';
    
    zone.innerHTML = `
      <div class="feedback-chip ${cls} pop-in">
        <span>${icon}</span>
        <span>${message}${extra ? ' <small>' + extra + '</small>' : ''}</span>
      </div>
    `;
  }
  
  /**
   * Lock a question to prevent further interaction
   */
  function lockQuestion(qBlock) { 
    qBlock.dataset.locked = '1'; 
  }
  
  /**
   * Launch confetti animation
   */
  function launchConfetti(count) {
    for (let i = 0; i < count; i++) {
      const piece = document.createElement('div');
      piece.className = 'confetti-piece';
      piece.style.left = (Math.random() * 100) + 'vw';
      piece.style.setProperty('--confetti-x', (Math.random() * 200 - 100) + 'px');
      
      const colors = ['#ffcc00', '#ff5e7e', '#4cd3ff', '#20e3b2', '#ff9f1c'];
      piece.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
      
      document.body.appendChild(piece);
      setTimeout(() => piece.remove(), 1900);
    }
  }
  
  // =============================================
  // 7. MCQ SINGLE & TRUE/FALSE HANDLING
  // =============================================
  
  /**
   * Handle MCQ single and True/False option clicks
   */
  function handleOptionClick(card) {
    const qBlock = card.closest('.question-block');
    if (!qBlock || qBlock.dataset.locked === '1') return;
    
    const type = (qBlock.dataset.type || '').toLowerCase();
    if (!['mcq_single', 'tf'].includes(type)) return;
    
    const correct = (qBlock.dataset.correct || '').trim();
    
    // Clear previous selections
    qBlock.querySelectorAll('.option-card').forEach(c => {
      c.classList.remove('selected', 'correct', 'wrong', 'show-correct');
    });
    
    // Select clicked card
    card.classList.add('selected');
    
    // Get user's choice
    let chosen = (card.dataset.letter || '').trim();
    if (!chosen && card.querySelector('input')) {
      chosen = (card.querySelector('input').value || '').trim();
    }
    
    // Check if correct
    const isCorrect = correct && chosen.toLowerCase() === correct.toLowerCase();
    
    if (isCorrect) {
      card.classList.add('correct');
      score++;
      updateScoreDisplay();
      setFeedback(qBlock, true, 'Correct!', 'Nice job!');
      playSound(soundCorrect);
      launchConfetti(35);
    } else {
      card.classList.add('wrong');
      // Show correct answer
      if (correct) {
        qBlock.querySelectorAll('.option-card').forEach(c => {
          const letter = (c.dataset.letter || '').trim();
          if (letter.toLowerCase() === correct.toLowerCase()) {
            c.classList.add('show-correct');
          }
        });
      }
      setFeedback(qBlock, false, 'Oops!', correct ? `Correct answer: ${correct}` : '');
      playSound(soundWrong);
    }
    
    updateStars();
    lockQuestion(qBlock);
    goNext();
  }
  
  // =============================================
  // 8. TEXT ANSWER HANDLING (Fill/Short)
  // =============================================
  
  /**
   * Handle text input answers (fill in blanks, short answer)
   */
  function handleTextEnter(input) {
    const qBlock = input.closest('.question-block');
    if (!qBlock || qBlock.dataset.locked === '1') return;
    
    const type = (qBlock.dataset.type || '').toLowerCase();
    if (!['fill', 'short'].includes(type)) return;
    
    const correct = (qBlock.dataset.correct || '').trim().toLowerCase();
    const userAns = (input.value || '').trim().toLowerCase();
    
    const isCorrect = correct && userAns === correct;
    
    if (isCorrect) {
      score++;
      updateScoreDisplay();
      setFeedback(qBlock, true, 'Correct!', '');
      playSound(soundCorrect);
      launchConfetti(35);
    } else {
      setFeedback(qBlock, false, 'Nice try!', 
                 correct ? `Model answer: ${qBlock.dataset.correct}` : '');
      playSound(soundWrong);
    }
    
    updateStars();
    lockQuestion(qBlock);
    setTimeout(() => goNext(), 800);
  }
  
  // =============================================
  // 9. MCQ MULTIPLE HANDLING
  // =============================================
  
  /**
   * Parse correct answers for MCQ multiple questions
   */
  function parseMultiCorrect(qBlock) {
    const rawJson = qBlock.dataset.optionsJson || '';
    
    // Try parsing from JSON first
    if (rawJson) {
      const obj = safeJsonParse(rawJson);
      if (obj && Array.isArray(obj.correct_multi)) {
        return obj.correct_multi.map(x => (x || '').toString().trim().toUpperCase()).filter(Boolean);
      }
    }
    
    // Fallback to data-correct attribute
    const correctStr = (qBlock.dataset.correct || '').trim();
    if (!correctStr) return [];
    
    const js = safeJsonParse(correctStr);
    if (Array.isArray(js)) {
      return js.map(x => (x || '').toString().trim().toUpperCase()).filter(Boolean);
    }
    
    // Parse comma/pipe separated string
    return correctStr.split(/[,|]/).map(s => s.trim().toUpperCase()).filter(Boolean);
  }
  
  /**
   * Render MCQ multiple options from JSON
   */
  function renderMcqMulti(qBlock) {
    const zone = qBlock.querySelector('.mcq-multi-zone');
    if (!zone) return;
    
    zone.innerHTML = '';
    const obj = safeJsonParse(qBlock.dataset.optionsJson || '') || {};
    const options = obj.options || {};
    
    // Check if options exist
    const keys = Object.keys(options || {});
    if (!keys.length) {
      zone.innerHTML = '<div class="text-muted">Options JSON not found for MCQ multiple.</div>';
      return;
    }
    
    // Create option cards
    keys.forEach(letter => {
      const label = (options[letter] ?? '').toString();
      if (!label) return;
      
      const id = `q${qBlock.dataset.qid}_${letter}_multi`;
      const el = document.createElement('label');
      el.className = 'option-card';
      el.setAttribute('data-letter', letter.toUpperCase());
      el.setAttribute('for', id);
      
      el.innerHTML = `
        <input type="checkbox" id="${id}" name="ans_multi_${qBlock.dataset.qid}[]" value="${letter.toUpperCase()}">
        <div class="opt-letter">${letter.toUpperCase()}</div>
        <div class="opt-text">${escapeHtml(label)}</div>
      `;
      
      // Toggle selection on click
      el.addEventListener('click', function(e) {
        e.preventDefault();
        if (qBlock.dataset.locked === '1') return;
        
        const input = el.querySelector('input');
        input.checked = !input.checked;
        el.classList.toggle('selected', input.checked);
      });
      
      zone.appendChild(el);
    });
  }
  
  /**
   * Check MCQ multiple answers
   */
  function checkMcqMulti(qBlock) {
    if (qBlock.dataset.locked === '1') return;
    
    const correctArr = parseMultiCorrect(qBlock);
    const chosen = Array.from(qBlock.querySelectorAll('input[type="checkbox"]:checked'))
      .map(i => (i.value || '').toString().trim().toUpperCase())
      .filter(Boolean);
    
    // Compare sorted arrays for exact match
    const sortA = (arr) => arr.slice().sort().join(',');
    const isCorrect = correctArr.length && sortA(correctArr) === sortA(chosen);
    
    // Reset styling
    qBlock.querySelectorAll('.option-card').forEach(c => {
      c.classList.remove('correct', 'wrong', 'show-correct');
    });
    
    if (isCorrect) {
      // Mark chosen as correct
      qBlock.querySelectorAll('.option-card.selected').forEach(c => {
        c.classList.add('correct');
      });
      
      score++;
      updateScoreDisplay();
      setFeedback(qBlock, true, 'Correct multi!', 'Great selection!');
      playSound(soundCorrect);
      launchConfetti(45);
    } else {
      // Mark chosen as wrong
      qBlock.querySelectorAll('.option-card.selected').forEach(c => {
        c.classList.add('wrong');
      });
      
      // Show correct answers
      qBlock.querySelectorAll('.option-card').forEach(c => {
        const letter = (c.dataset.letter || '').toUpperCase();
        if (correctArr.includes(letter)) {
          c.classList.add('show-correct');
        }
      });
      
      setFeedback(qBlock, false, 'Not correct.', 
                 correctArr.length ? `Correct: ${correctArr.join(', ')}` : '');
      playSound(soundWrong);
    }
    
    updateStars();
    lockQuestion(qBlock);
    goNext();
  }
  
  // =============================================
  // 10. MATCH THE COLUMN HANDLING
  // =============================================
  
  /**
   * Normalize match question data from various formats
   */
  function normalizeMatchData(obj) {
    if (!obj) return null;
    
    // Format 1: Direct array of pairs
    if (Array.isArray(obj)) {
      const pairs = obj
        .filter(p => p && typeof p === 'object')
        .map(p => ({ 
          left: (p.left ?? p.key ?? p.statement ?? '').toString(), 
          right: (p.right ?? p.value ?? p.match ?? '').toString() 
        }))
        .filter(p => p.left && p.right);
      
      if (!pairs.length) return null;
      
      const left = pairs.map(p => p.left);
      const right = pairs.map(p => p.right); // Preserve duplicates
      const map = {};
      
      pairs.forEach((p, idx) => { 
        map[idx] = p.right;
      });
      
      return { 
        left, 
        right,
        mapType: 'value', 
        map,
        pairs: pairs
      };
    }
    
    // Format 2: Standard left/right arrays with answer_map
    if (Array.isArray(obj.left) && Array.isArray(obj.right)) {
      const left = obj.left.map(String);
      const right = obj.right.map(String); // Preserve duplicates
      
      if (obj.answer_map && typeof obj.answer_map === 'object') {
        const map = {};
        Object.keys(obj.answer_map).forEach(key => {
          map[parseInt(key)] = parseInt(obj.answer_map[key]);
        });
        return { left, right, mapType: 'index', map };
      } else if (obj.pairs && Array.isArray(obj.pairs)) {
        const map = {};
        obj.pairs.forEach((pair, idx) => {
          if (pair.left && pair.right) {
            map[idx] = pair.right.toString();
          }
        });
        return { left, right, mapType: 'value', map };
      }
    }
    
    // Format 3: Object with match property
    if (obj.match && typeof obj.match === 'object') {
      const left = Object.keys(obj.match).map(String);
      const rightValues = Object.values(obj.match).map(v => String(v));
      const right = obj.right && Array.isArray(obj.right)
        ? obj.right.map(String)
        : rightValues;
      
      const map = {};
      left.forEach((k, idx) => { 
        map[idx] = String(obj.match[k]); 
      });
      
      return { left, right, mapType: 'value', map };
    }
    
    return null;
  }
  
  /**
   * Render match question interface
   */
  function renderMatch(qBlock) {
    const container = qBlock.querySelector('.match-container');
    if (!container) return;
    
    // Parse data
    const obj = safeJsonParse(qBlock.dataset.optionsJson || '') || {};
    const data = normalizeMatchData(obj);
    
    if (!data) {
      container.innerHTML = '<div class="alert alert-warning">No match data available.</div>';
      return;
    }
    
    // Create right items with unique IDs for duplicates
    const rightItemsWithIds = [];
    const usedCounts = {};
    
    data.right.forEach((value, index) => {
      const count = (usedCounts[value] || 0) + 1;
      usedCounts[value] = count;
      const uniqueId = `${value.replace(/[^a-z0-9]/gi, '_')}_${count}`;
      
      rightItemsWithIds.push({
        id: uniqueId,
        value: value,
        displayText: value
      });
    });
    
    // Shuffle items
    const shuffledRightItems = shuffleArray(rightItemsWithIds);
    
    // Build HTML
    container.innerHTML = `
      <div class="match-columns">
        <!-- Left Column -->
        <div class="left-column">
          <h6>COLUMN A</h6>
          <div class="left-items">
            ${data.left.map((text, idx) => `
              <div class="drag-zone empty" 
                   data-index="${idx}"
                   data-expected="${escapeAttr(data.map[idx] || '')}"
                   onclick="handleDropZoneClick(this, event)">
                <div class="left-item">
                  ${escapeHtml(text)}
                </div>
              </div>
            `).join('')}
          </div>
        </div>
        
        <!-- Right Column -->
        <div class="right-column">
          <h6>COLUMN B</h6>
          <div class="right-items">
            ${shuffledRightItems.map((item, idx) => `
              <div class="right-item" 
                   data-id="${item.id}"
                   data-value="${escapeAttr(item.value)}"
                   onclick="handleRightItemClick(this, event)">
                ${escapeHtml(item.displayText)}
              </div>
            `).join('')}
          </div>
        </div>
      </div>
    `;
    
    // Store data
    qBlock._matchData = data;
    qBlock._userMatches = {};
    qBlock._selectedItem = null;
    qBlock._rightItemsMap = shuffledRightItems.reduce((map, item) => {
      map[item.id] = item;
      return map;
    }, {});
    
    // Initialize progress display
    updateMatchProgress(qBlock);
  }
  
  /**
   * Handle right item selection (for matching)
   */
  window.handleRightItemClick = function(item, event) {
    event.stopPropagation();
    
    const qBlock = item.closest('.question-block');
    if (!qBlock || item.classList.contains('used')) return;
    
    // Toggle selection
    if (qBlock._selectedItem === item) {
      item.classList.remove('selected');
      qBlock._selectedItem = null;
      qBlock.querySelectorAll('.drag-zone').forEach(z => {
        z.classList.remove('highlight');
      });
      return;
    }
    
    // Deselect previous
    if (qBlock._selectedItem) {
      qBlock._selectedItem.classList.remove('selected');
    }
    
    // Select this item
    item.classList.add('selected');
    qBlock._selectedItem = item;
    
    // Highlight empty drop zones
    qBlock.querySelectorAll('.drag-zone').forEach(zone => {
      if (!zone.querySelector('.dropped-item')) {
        zone.classList.add('highlight');
      }
    });
  };
  
  /**
   * Handle drop zone click (for matching)
   */
  window.handleDropZoneClick = function(zone, event) {
    event.stopPropagation();
    
    const qBlock = zone.closest('.question-block');
    if (!qBlock || !qBlock._selectedItem) return;
    
    const selectedItem = qBlock._selectedItem;
    const itemId = selectedItem.dataset.id;
    const itemValue = selectedItem.dataset.value;
    const leftIndex = parseInt(zone.dataset.index);
    
    // Remove existing match if any
    const existingDrop = zone.querySelector('.dropped-item');
    if (existingDrop) {
      const existingId = existingDrop.dataset.itemId;
      // Return item to right column
      qBlock.querySelectorAll('.right-item').forEach(item => {
        if (item.dataset.id === existingId) {
          item.classList.remove('used');
        }
      });
      existingDrop.remove();
      delete qBlock._userMatches[leftIndex];
    }
    
    // Mark selected item as used
    selectedItem.classList.add('used');
    selectedItem.classList.remove('selected');
    
    // Create dropped item
    const droppedItem = document.createElement('div');
    droppedItem.className = 'dropped-item';
    droppedItem.dataset.itemId = itemId;
    droppedItem.innerHTML = `
      <span class="item-text">${escapeHtml(truncateText(itemValue, 35))}</span>
      <button class="remove-btn" onclick="removeMatch(this)">
        <i class="fas fa-times"></i>
      </button>
    `;
    
    // Add to zone
    zone.appendChild(droppedItem);
    zone.classList.remove('highlight', 'empty');
    zone.classList.add('matched');
    
    // Store match
    qBlock._userMatches[leftIndex] = itemId;
    
    // Clear selection
    qBlock._selectedItem = null;
    qBlock.querySelectorAll('.drag-zone').forEach(z => {
      z.classList.remove('highlight');
    });
    
    // Update progress and check completion
    updateMatchProgress(qBlock);
  };
  
  /**
   * Update match progress display
   */
  function updateMatchProgress(qBlock) {
    const total = qBlock._matchData?.left?.length || 0;
    const matched = Object.keys(qBlock._userMatches || {}).length;
    
    // Update match count display if exists
    const countEl = qBlock.querySelector('.match-count');
    const totalEl = qBlock.querySelector('.match-total');
    
    if (countEl) countEl.textContent = matched;
    if (totalEl) totalEl.textContent = total;
    
    // Update zone states
    qBlock.querySelectorAll('.drag-zone').forEach(zone => {
      const idx = parseInt(zone.dataset.index);
      const hasMatch = qBlock._userMatches && qBlock._userMatches[idx];
      
      zone.classList.toggle('empty', !hasMatch);
      zone.classList.toggle('matched', hasMatch);
    });
    
    // Auto-check if all matches are complete
    if (total > 0 && matched === total && !qBlock.dataset.locked) {
      setTimeout(() => {
        autoCheckMatch(qBlock);
      }, 500);
    }
  }
  
  /**
   * Auto-check match answers when all items are matched
   */
  function autoCheckMatch(qBlock) {
    if (qBlock.dataset.locked === '1') return;
    
    const data = qBlock._matchData;
    const userMatches = qBlock._userMatches || {};
    
    if (!data) {
      setFeedback(qBlock, false, 'Match data missing', '');
      lockQuestion(qBlock);
      goNext();
      return;
    }
    
    let correctCount = 0;
    const results = [];
    
    // Check each match
    for (let i = 0; i < data.left.length; i++) {
      const userChoiceId = userMatches[i];
      let userValue = '';
      
      // Get actual value from unique ID
      if (userChoiceId && qBlock._rightItemsMap && qBlock._rightItemsMap[userChoiceId]) {
        userValue = qBlock._rightItemsMap[userChoiceId].value;
      }
      
      let correctAnswer = '';
      
      if (data.mapType === 'index') {
        const correctIdx = data.map[i];
        if (correctIdx !== undefined && data.right[correctIdx] !== undefined) {
          correctAnswer = data.right[correctIdx].toString();
        }
      } else {
        correctAnswer = (data.map[i] || '').toString();
      }
      
      const isCorrect = userValue && correctAnswer && 
                       userValue === correctAnswer;
      
      results.push({ 
        index: i, 
        isCorrect, 
        correctAnswer,
        userValue 
      });
      
      if (isCorrect) correctCount++;
    }
    
    // Visual feedback
    const zones = qBlock.querySelectorAll('.drag-zone');
    zones.forEach((zone, idx) => {
      const result = results[idx];
      
      if (result) {
        if (result.isCorrect) {
          zone.classList.add('matched-correct');
          zone.classList.remove('error');
        } else {
          zone.classList.add('error');
          zone.classList.remove('matched-correct');
          
          // Show correct answer
          const correctHint = document.createElement('div');
          correctHint.className = 'correct-hint';
          correctHint.innerHTML = `<small>✓ ${escapeHtml(result.correctAnswer)}</small>`;
          correctHint.style.color = 'var(--ok)';
          correctHint.style.marginTop = '5px';
          correctHint.style.fontSize = '0.85rem';
          zone.appendChild(correctHint);
        }
      }
    });
    
    // Score and feedback
    const allCorrect = correctCount === data.left.length;
    
    if (allCorrect) {
      score++;
      updateScoreDisplay();
      setFeedback(qBlock, true, 'Perfect! All matches correct ✅', 
                 'Moving to next question...');
      playSound(soundCorrect);
      launchConfetti(60);
    } else {
      setFeedback(qBlock, false, 'Not all matches are correct', 
                 `${correctCount} of ${data.left.length} correct. Moving to next question...`);
      playSound(soundWrong);
    }
    
    updateStars();
    lockQuestion(qBlock);
    
    // Auto-proceed to next question
    setTimeout(goNext, 2000);
  }
  
  /**
   * Remove a match (called from remove button)
   */
  window.removeMatch = function(removeBtn) {
    const droppedItem = removeBtn.closest('.dropped-item');
    const zone = droppedItem.closest('.drag-zone');
    const qBlock = zone.closest('.question-block');
    
    if (!droppedItem || !zone || !qBlock) return;
    
    const itemId = droppedItem.dataset.itemId;
    const leftIndex = parseInt(zone.dataset.index);
    
    // Return item to right column
    qBlock.querySelectorAll('.right-item').forEach(item => {
      if (item.dataset.id === itemId) {
        item.classList.remove('used');
      }
    });
    
    // Remove from matches
    delete qBlock._userMatches[leftIndex];
    
    // Remove dropped item
    droppedItem.remove();
    
    // Update zone state
    zone.classList.remove('matched');
    zone.classList.add('empty');
    
    // Update progress
    updateMatchProgress(qBlock);
  };
  
  // =============================================
  // 11. COMPLETION SCREEN
  // =============================================
  
  /**
   * Show completion screen
   */
  function showCompletion() {
    blocks.forEach(b => b.classList.remove('active'));
    completeBox.classList.remove('d-none');
    
    // Stop timer
    isTimerRunning = false;
    clearInterval(quizTimer);
    
    // Show final score
    if (finalScoreEl) {
      finalScoreEl.textContent = score;
    }
    
    // Show final time
    const finalTimeEl = document.getElementById('finalTime');
    if (finalTimeEl) {
      const minutes = Math.floor(secondsElapsed / 60);
      const seconds = secondsElapsed % 60;
      finalTimeEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
    
    // Update stars
    updateStars();
    
    // Legacy progress bar
    if (progressEl) {
      progressEl.style.width = '100%';
    }
    
    launchConfetti(120);
  }
  
  // =============================================
  // 12. EVENT LISTENERS & INITIALIZATION
  // =============================================
  
  /**
   * Attach event listeners
   */
  function attachEventListeners() {
    // MCQ single & TF option clicks
    document.querySelectorAll('.option-card').forEach(card => {
      card.addEventListener('click', function(e) {
        const qBlock = card.closest('.question-block');
        if (!qBlock) return;
        
        const type = (qBlock.dataset.type || '').toLowerCase();
        if (!['mcq_single', 'tf'].includes(type)) return;
        
        e.preventDefault();
        handleOptionClick(card);
      });
    });
    
    // Text answer input (Enter key)
    document.querySelectorAll('.practice-text-answer').forEach(input => {
      input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          handleTextEnter(input);
        }
      });
    });
    
    // MCQ multiple check button
    document.querySelectorAll('.btn-check-multi').forEach(btn => {
      btn.addEventListener('click', function() {
        const qid = btn.getAttribute('data-qid');
        const qBlock = document.querySelector(`.question-block[data-qid="${qid}"]`);
        if (qBlock) checkMcqMulti(qBlock);
      });
    });
  }
  
  /**
   * Initialize the quiz
   */
  function initQuiz() {
    if (blocks.length) {
      // Render dynamic content
      blocks.forEach(qBlock => {
        const type = (qBlock.dataset.type || '').toLowerCase();
        if (type === 'mcq_multi') renderMcqMulti(qBlock);
        if (type === 'match') renderMatch(qBlock);
      });
      
      // Show first question
      showBlock(0);
      
      // Start timer
      startTimer();
      
      // Initialize displays
      updateScoreDisplay();
      updateStars();
      
      // Attach event listeners
      attachEventListeners();
    }
  }
  
  // =============================================
  // 13. START THE QUIZ
  // =============================================
  
  // Initialize when DOM is ready
  document.addEventListener('DOMContentLoaded', function() {
    initQuiz();
  });
  
})();
</script>

<?= $this->endSection() ?>
