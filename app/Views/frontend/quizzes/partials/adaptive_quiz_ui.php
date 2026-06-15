<?php
$isAdaptive     = ! empty($isAdaptive) && ! empty($levelInfo);
$allLevels      = $allLevels ?? [];
$currentLevelNo = (int) ($currentLevelNo ?? 1);
$totalLevels    = (int) ($totalLevels ?? count($allLevels));
$passPct        = $isAdaptive
    ? (float) ($levelInfo->passing_percentage ?? $levelInfo->min_pass_percentage ?? 60)
    : 0;
$levelLabel     = $isAdaptive
    ? (! empty($levelInfo->level_name) ? $levelInfo->level_name : ('Level ' . $currentLevelNo))
    : '';
?>
<?php if ($isAdaptive): ?>
<style>
.quiz-adaptive-levels {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px;
  padding: 0 12px 10px;
  border-top: 1px solid rgba(255,255,255,.2);
  margin-top: 2px;
}
.quiz-adaptive-levels .adaptive-step {
  padding: 4px 10px;
  border-radius: 999px;
  font-size: .75rem;
  font-weight: 700;
  background: rgba(255,255,255,.15);
  color: #fff;
  border: 2px solid transparent;
}
.quiz-adaptive-levels .adaptive-step.done { background: rgba(16,185,129,.4); }
.quiz-adaptive-levels .adaptive-step.active {
  background: rgba(255,255,255,.28);
  border-color: #fff;
}
.quiz-adaptive-levels .adaptive-step.locked { opacity: .55; }
.quiz-adaptive-hint {
  margin-left: auto;
  font-size: .75rem;
  opacity: .92;
  color: #fff;
}
</style>

<?php if ($totalLevels > 0): ?>
<div class="quiz-adaptive-levels" role="list" aria-label="Quiz levels">
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
    $lbl = ! empty($lvl->level_name) ? $lvl->level_name : ('Level ' . $no);
  ?>
    <span class="<?= $cls ?>" role="listitem"><?php if ($no < $currentLevelNo): ?><i class="fas fa-check"></i> <?php endif; ?><?= esc($lbl) ?></span>
  <?php endforeach; ?>
  <span class="quiz-adaptive-hint d-none d-md-inline">Pass <?= esc((string) $passPct) ?>% &middot; <?= esc(ucfirst((string) ($levelInfo->base_difficulty ?? 'medium'))) ?></span>
</div>
<?php else: ?>
<div class="quiz-adaptive-levels">
  <span class="quiz-adaptive-hint">Adaptive &middot; Pass <?= esc((string) $passPct) ?>% on <?= esc($levelLabel) ?></span>
</div>
<?php endif; ?>
<?php endif; ?>
