<?php
$isAdaptive     = ! empty($isAdaptive) && ! empty($levelInfo);
$allLevels      = $allLevels ?? [];
$currentLevelNo = (int) ($currentLevelNo ?? 1);
$totalLevels    = (int) ($totalLevels ?? count($allLevels));
$passPct        = (float) ($levelInfo->passing_percentage ?? $levelInfo->min_pass_percentage ?? 60);
$difficulty     = ucfirst((string) ($levelInfo->base_difficulty ?? 'medium'));
$levelLabel     = ! empty($levelInfo->level_name)
    ? $levelInfo->level_name
    : ('Level ' . $currentLevelNo);
?>
<?php if ($isAdaptive): ?>
<style>
.adaptive-level-strip {
  background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
  color: #fff;
  border-radius: 14px;
  padding: 12px 16px;
  margin: 0 12px 12px;
  box-shadow: 0 6px 20px rgba(79, 70, 229, 0.25);
}
.adaptive-level-steps {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-top: 10px;
}
.adaptive-step {
  flex: 1 1 auto;
  min-width: 72px;
  text-align: center;
  padding: 6px 8px;
  border-radius: 10px;
  font-size: 0.78rem;
  font-weight: 600;
  background: rgba(255,255,255,0.15);
  border: 2px solid transparent;
}
.adaptive-step.done { background: rgba(16, 185, 129, 0.35); border-color: #6ee7b7; }
.adaptive-step.active { background: rgba(255,255,255,0.28); border-color: #fff; box-shadow: 0 0 0 2px rgba(255,255,255,0.35); }
.adaptive-step.locked { opacity: 0.55; }
.adaptive-level-hint {
  font-size: 0.85rem;
  opacity: 0.95;
  margin: 6px 0 0;
}
#adaptiveLevelResultModal .modal-content { border-radius: 18px; border: 0; }
#adaptiveLevelResultModal .result-icon { font-size: 3.5rem; }
#adaptiveLevelResultModal .score-box {
  background: #f8fafc;
  border-radius: 12px;
  padding: 12px;
}
#adaptiveLoadingOverlay.d-flex { display: flex !important; }
</style>

<?php if (session()->getFlashdata('msg')): ?>
  <div class="alert alert-success mx-3 mb-2" style="border-radius:12px;">
    <?= esc(session()->getFlashdata('msg')) ?>
  </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-warning mx-3 mb-2" style="border-radius:12px;">
    <?= esc(session()->getFlashdata('error')) ?>
  </div>
<?php endif; ?>

<div class="adaptive-level-strip" id="adaptiveLevelStrip">
  <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap:8px;">
    <div>
      <div class="fw-bold" style="font-size:1.05rem;">
        <i class="fas fa-layer-group me-1"></i>
        <?= esc($levelLabel) ?>
        <span class="badge text-bg-light text-dark ms-1"><?= $currentLevelNo ?> / <?= max(1, $totalLevels) ?></span>
      </div>
      <p class="adaptive-level-hint mb-0">
        Score at least <strong><?= esc((string) $passPct) ?>%</strong> to unlock the next level.
        <span class="d-none d-sm-inline"> · <?= esc($difficulty) ?> difficulty</span>
      </p>
    </div>
    <div class="text-end small">
      <span class="badge text-bg-light text-dark"><?= esc($difficulty) ?></span>
    </div>
  </div>

  <?php if ($totalLevels > 1): ?>
  <div class="adaptive-level-steps" role="list" aria-label="Quiz levels">
    <?php foreach ($allLevels as $lvl):
      $no = (int) ($lvl->level_no ?? 0);
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
      <div class="<?= $cls ?>" role="listitem" title="<?= esc($lbl) ?>">
        <?php if ($no < $currentLevelNo): ?><i class="fas fa-check"></i> <?php endif; ?>
        <?= esc($lbl) ?>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<div class="modal fade" id="adaptiveLevelResultModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body text-center p-4 p-md-5">
        <div id="adaptiveResultIcon" class="result-icon mb-2 text-success"><i class="fas fa-trophy"></i></div>
        <h4 id="adaptiveResultTitle" class="mb-2">Level result</h4>
        <p id="adaptiveResultMessage" class="text-muted mb-4"></p>
        <div class="row mb-4">
          <div class="col-6">
            <div class="score-box">
              <small class="text-muted d-block">Your score</small>
              <strong id="adaptiveYourScore" class="h4 mb-0">—</strong>
            </div>
          </div>
          <div class="col-6">
            <div class="score-box">
              <small class="text-muted d-block">Required</small>
              <strong id="adaptiveRequiredScore" class="h4 mb-0">—</strong>
            </div>
          </div>
        </div>
        <div id="adaptiveResultActions" class="d-flex flex-wrap justify-content-center" style="gap:8px;"></div>
      </div>
    </div>
  </div>
</div>

<div id="adaptiveLoadingOverlay" class="d-none position-fixed w-100 h-100" style="top:0;left:0;background:rgba(15,23,42,0.55);z-index:9999;align-items:center;justify-content:center;">
  <div class="text-center text-white">
    <div class="spinner-border mb-2" role="status"></div>
    <div id="adaptiveLoadingText">Please wait…</div>
  </div>
</div>
<?php endif; ?>
