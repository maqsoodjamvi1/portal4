<?php

/**
 * Hifz enrollment fields for Students Bulk / Class Change (auto Sabqi / Manzil pools).
 */

$juzCatalog = $juzCatalog ?? hifzJuzCatalog();
$sequenceOptions = $sequenceOptions ?? hifzParaOnlySequenceOptions();
$memorizationSequence = hifzNormalizeMemorizationSequence((string) ($memorizationSequence ?? 'para_forward'));
if (! isset($sequenceOptions[$memorizationSequence])) {
    $memorizationSequence = 'para_forward';
}
$currentPara = max(1, min(30, (int) ($currentPara ?? 1)));
$linesDoneInPara = max(0, min(hifzParaTotalLines(), (int) ($linesDoneInPara ?? 0)));
$manzilParasPerDay = max(1, min(3, (int) ($manzilParasPerDay ?? 1)));
$pools = hifzComputeEnrollmentPools($memorizationSequence, $currentPara, $linesDoneInPara);
?>

<div class="sb-hifz-panel<?= $isHifz ? '' : ' sb-hifz-panel-off' ?>" id="hifz_panel_<?= (int) $sid ?>">
  <div class="sb-hifz-row sb-hifz-row-top">
    <label class="sb-hifz-check">
      <input type="checkbox" class="js-hifz-toggle" id="hifz_toggle_<?= (int) $sid ?>" data-id="<?= (int) $sid ?>"<?= $isHifz ? ' checked' : '' ?>>
      <span>Hifz student</span>
    </label>

    <div class="sb-hifz-field">
      <label class="sb-hifz-label" for="hifz_sec_<?= (int) $sid ?>">Hifz section</label>
      <select class="form-control form-control-sm js-hifz-sec" id="hifz_sec_<?= (int) $sid ?>">
        <option value="">— Select —</option>
        <?php foreach ($hifzSections as $sec):
            $val = (int) ($sec['hifz_sec_id'] ?? 0);
            if ($val <= 0) {
                continue;
            } ?>
          <option value="<?= $val ?>"<?= $hifzSec === $val ? ' selected' : '' ?>><?= esc($sec['section_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="sb-hifz-field">
      <label class="sb-hifz-label" for="hifz_sequence_<?= (int) $sid ?>">Plan order</label>
      <select class="form-control form-control-sm js-hifz-sequence" id="hifz_sequence_<?= (int) $sid ?>">
        <?php foreach ($sequenceOptions as $code => $label): ?>
          <option value="<?= esc($code) ?>"<?= $memorizationSequence === $code ? ' selected' : '' ?>><?= esc($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="sb-hifz-row sb-hifz-row-inputs">
    <div class="sb-hifz-field">
      <label class="sb-hifz-label" for="hifz_current_para_<?= (int) $sid ?>">Current para (Mutalia)</label>
      <select class="form-control form-control-sm js-hifz-current-para" id="hifz_current_para_<?= (int) $sid ?>">
        <?php foreach ($juzCatalog as $j):
            $no = (int) ($j['juz_no'] ?? 0);
            if ($no < 1 || $no > 30) {
                continue;
            }
            $nameEn = trim((string) ($j['name_en'] ?? ''));
            $optLabel = 'Para ' . $no . ($nameEn !== '' ? ' — ' . $nameEn : ''); ?>
          <option value="<?= $no ?>"<?= $currentPara === $no ? ' selected' : '' ?>><?= esc($optLabel) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="sb-hifz-field">
      <label class="sb-hifz-label" for="hifz_lines_done_<?= (int) $sid ?>">Lines done</label>
      <input type="number" class="form-control form-control-sm js-hifz-lines-done" id="hifz_lines_done_<?= (int) $sid ?>"
             min="0" max="<?= hifzParaTotalLines() ?>" value="<?= $linesDoneInPara ?>">
    </div>

    <div class="sb-hifz-field">
      <label class="sb-hifz-label" for="hifz_manzil_per_day_<?= (int) $sid ?>">Manzil paras / day</label>
      <select class="form-control form-control-sm js-hifz-manzil-per-day" id="hifz_manzil_per_day_<?= (int) $sid ?>">
        <?php for ($n = 1; $n <= 3; $n++): ?>
          <option value="<?= $n ?>"<?= $manzilParasPerDay === $n ? ' selected' : '' ?>><?= $n ?></option>
        <?php endfor; ?>
      </select>
    </div>
  </div>

  <input type="hidden" class="js-hifz-manzil-pool" id="hifz_manzil_pool_<?= (int) $sid ?>" value="<?= esc(hifzFormatJuzList($pools['manzil_pool_paras'])) ?>">
  <input type="hidden" class="js-hifz-sabqi-list" id="hifz_sabqi_list_<?= (int) $sid ?>" value="<?= esc(hifzFormatJuzList($pools['sabqi_paras'])) ?>">

  <div class="row sb-hifz-computed-row">
    <div class="col-sm-6 mb-2">
      <div class="sb-hifz-pool-card sb-hifz-pool-sabqi">
        <label class="sb-hifz-label">Calculated Sabqi</label>
        <div class="sb-hifz-summary" id="hifz_sabqi_summary_<?= (int) $sid ?>"><?= esc($pools['summary_sabqi']) ?></div>
      </div>
    </div>
    <div class="col-sm-6 mb-2">
      <div class="sb-hifz-pool-card sb-hifz-pool-manzil">
        <label class="sb-hifz-label">Calculated Manzil pool</label>
        <div class="sb-hifz-summary" id="hifz_manzil_summary_<?= (int) $sid ?>"><?= esc($pools['summary_manzil']) ?></div>
      </div>
    </div>
  </div>
</div>
