<?php
$isAdaptive     = ! empty($isAdaptive) && ! empty($levelInfo);
$allLevels      = $allLevels ?? [];
$currentLevelNo = (int) ($currentLevelNo ?? 1);
$totalLevels    = (int) ($totalLevels ?? count($allLevels));
$totalQ         = isset($totalQuestions) ? (int) $totalQuestions : count($qq ?? []);
$passPct        = $isAdaptive
    ? (float) ($levelInfo->passing_percentage ?? $levelInfo->min_pass_percentage ?? 60)
    : 0;
$difficulty     = $isAdaptive ? ucfirst((string) ($levelInfo->base_difficulty ?? 'medium')) : '';
$levelLabel     = $isAdaptive
    ? (! empty($levelInfo->level_name) ? $levelInfo->level_name : ('Level ' . $currentLevelNo))
    : '';

$sessionSid = (int) (session('student_id') ?? 0);
$sidForUrl  = (int) ($studentIdForUrl ?? 0);
$dashUrl    = base_url('student/dashboard');
if ($sidForUrl > 0 && $sidForUrl !== $sessionSid) {
    $dashUrl .= '?sid=' . $sidForUrl;
}

$metaPieces = $metaPieces ?? [];
if (! empty($classSection)) {
    $metaPieces[] = $classSection;
}
if (! empty($subjectName)) {
    $metaPieces[] = $subjectName;
}
$topicSuffix = '';
if (! empty($topicList) && is_array($topicList)) {
    $topicSuffix = ' (' . implode(', ', $topicList) . ')';
}

$__langNav   = session('language') ?? 'en';
$__homeLabel = ($__langNav === 'ur') ? 'ڈیش بورڈ' : (($__langNav === 'ar') ? 'الرئيسية' : 'Home');
$__exitLabel = ($__langNav === 'ur') ? 'واپس' : (($__langNav === 'ar') ? 'رجوع' : 'Exit');
?>

<?php if (session()->getFlashdata('msg')): ?>
  <div class="alert alert-success mx-2 mt-2 mb-0 py-2" style="border-radius:10px;font-size:.9rem;">
    <?= esc(session()->getFlashdata('msg')) ?>
  </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-warning mx-2 mt-2 mb-0 py-2" style="border-radius:10px;font-size:.9rem;">
    <?= esc(session()->getFlashdata('error')) ?>
  </div>
<?php endif; ?>

<header class="quiz-topbar quiz-topbar--unified" id="quizUnifiedHeader">
  <div class="quiz-topbar-row quiz-topbar-row--main">
    <a href="<?= esc($dashUrl) ?>" class="quiz-exit-btn" title="<?= esc($__homeLabel) ?>" aria-label="<?= esc($__homeLabel) ?>"
       onclick="return confirm(<?= json_encode($__langNav === 'ur' ? 'کیا آپ کوئز چھوڑ کر ڈیش بورڈ پر جانا چاہتے ہیں؟' : 'Leave the quiz and go to dashboard?') ?>);">
      <i class="fas fa-arrow-left" aria-hidden="true"></i>
      <span class="d-none d-sm-inline"><?= esc($__exitLabel) ?></span>
    </a>

    <div class="quiz-topbar-title-block">
      <h1 class="quiz-title mb-0"><?= esc($quiz->title ?? 'Quiz') ?><?= esc($topicSuffix) ?></h1>
      <?php if ($metaPieces): ?>
        <div class="quiz-meta d-none d-md-block"><?= esc(implode(' · ', $metaPieces)) ?></div>
      <?php endif; ?>
      <?php if ($isAdaptive): ?>
        <div class="quiz-meta quiz-meta--adaptive d-md-none">
          <?= esc($levelLabel) ?> · <?= $currentLevelNo ?>/<?= max(1, $totalLevels) ?> · Pass <?= esc((string) $passPct) ?>%
        </div>
      <?php endif; ?>
    </div>

    <div class="quiz-topbar-stats">
      <?php if ($isAdaptive): ?>
        <span class="pill pill-level d-none d-md-inline-flex" title="Current level">
          <i class="fas fa-layer-group"></i>
          <span><?= esc($levelLabel) ?> (<?= $currentLevelNo ?>/<?= max(1, $totalLevels) ?>)</span>
        </span>
      <?php endif; ?>

      <?php if ($totalQ > 0): ?>
        <div class="pill pill-questions" id="quiz-question-counter" data-total="<?= $totalQ ?>">
          <i class="fas fa-list-ol"></i>
          <span id="quiz-q-remaining"><?= max(0, $totalQ - 1) ?></span>
          <span class="d-none d-sm-inline"> left</span>
        </div>
      <?php endif; ?>

      <?php if (! empty($timeLimitSec) && (int) $timeLimitSec > 0): ?>
        <div id="quiz-timer" class="timer-shell timer-ok" data-remaining="<?= (int) $timeLimitSec ?>">
          <i class="fas fa-clock"></i>
          <span id="quiz-timer-minutes"></span>
        </div>
      <?php endif; ?>

      <span class="pill pill-autosave" title="Autosave">
        <span class="autosave-dot" id="autosaveDot"></span>
        <i class="fas fa-cloud-upload-alt autosave-icon" id="autosaveIcon"></i>
        <span id="autosaveText"></span>
      </span>
    </div>
  </div>

  <?php if ($isAdaptive && $totalLevels > 1): ?>
  <div class="quiz-topbar-row quiz-topbar-row--levels" role="list" aria-label="Quiz levels">
    <?php foreach ($allLevels as $lvl):
      $no  = (int) ($lvl->level_no ?? 0);
      $cls = 'adaptive-step';
      if ($no < $currentLevelNo) {
          $cls .= ' done';
      } elseif ($no === $currentLevelNo) {
          $cls .= ' active';
      } else {
          $cls .= ' locked';
      }
      $lbl = ! empty($lvl->level_name) ? $lvl->level_name : ('L' . $no);
    ?>
      <div class="<?= $cls ?>" role="listitem"><?php if ($no < $currentLevelNo): ?><i class="fas fa-check"></i> <?php endif; ?><?= esc($lbl) ?></div>
    <?php endforeach; ?>
    <span class="adaptive-pass-hint d-none d-lg-inline">Pass <?= esc((string) $passPct) ?>% · <?= esc($difficulty) ?></span>
  </div>
  <?php elseif ($isAdaptive): ?>
  <div class="quiz-topbar-row quiz-topbar-row--levels quiz-topbar-row--levels-single">
    <span class="adaptive-pass-hint">Pass <?= esc((string) $passPct) ?>% to continue · <?= esc($difficulty) ?></span>
  </div>
  <?php endif; ?>

  <?php if (! empty($quiz->negative_mark_per_q) && (float) $quiz->negative_mark_per_q > 0): ?>
  <div class="quiz-topbar-row quiz-topbar-row--hint">
    <small class="text-warning mb-0">
      Negative marking: <?= (float) $quiz->negative_mark_per_q ?> per wrong answer
    </small>
  </div>
  <?php endif; ?>
</header>
