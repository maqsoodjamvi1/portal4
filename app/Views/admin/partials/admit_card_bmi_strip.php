<?php
$admit = $admit ?? [];
$ageY = $admit['student_age_years'] ?? null;
$h    = $admit['height_cm'] ?? null;
$w    = $admit['weight_kg'] ?? null;
$b    = $admit['bmi_value'] ?? null;
$cat  = trim((string) ($admit['bmi_category'] ?? ''));
$has  = $ageY !== null || $h !== null || $w !== null || $b !== null || $cat !== '';
if ($has) :
    $catLabel = $cat !== '' ? ucwords(str_replace('_', ' ', $cat)) : '';
    ?>
<div class="admit-bmi-strip english-text">
  <span class="admit-bmi-title"><i class="fas fa-heartbeat" aria-hidden="true"></i> Health (BMI)</span>
  <span class="admit-bmi-items">
    <?php if ($ageY !== null): ?>
      <span class="admit-bmi-item"><b>Age</b> <?= (int) $ageY ?> yrs</span>
    <?php endif; ?>
    <?php if ($h !== null): ?>
      <span class="admit-bmi-item"><b>Height</b> <?= esc((string) (round((float) $h, 1))) ?> cm</span>
    <?php endif; ?>
    <?php if ($w !== null): ?>
      <span class="admit-bmi-item"><b>Weight</b> <?= esc((string) (round((float) $w, 1))) ?> kg</span>
    <?php endif; ?>
    <?php if ($b !== null): ?>
      <span class="admit-bmi-item"><b>BMI</b> <?= esc((string) (round((float) $b, 2))) ?></span>
    <?php endif; ?>
    <?php if ($catLabel !== ''): ?>
      <span class="admit-bmi-item"><b>Category</b> <?= esc($catLabel) ?></span>
    <?php endif; ?>
  </span>
</div>
<?php endif; ?>
