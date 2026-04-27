<?php
/** @var array $dateRange [ ['date' => 'Y-m-d'], ... ] */
/** @var array $sectionLabels [cls_sec_id => 'Class — Section'] */
/** @var array $matrix [cls_sec_id][Y-m-d] => [subject_name, ...] */
?>
<style>
  #datesheetSummaryTable { table-layout: fixed; width: 100%; }
  #datesheetSummaryTable.table-sm td, #datesheetSummaryTable.table-sm th { padding: .35rem .25rem; font-size: 12px; }
  .sum-sticky { position: sticky; left: 0; z-index: 1; background: #f8f9fa; }
  .sum-sec { min-width: 160px; max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .sum-date, .sum-cell { width: 70px; min-width: 70px; max-width: 84px; }
  .vhead { writing-mode: vertical-rl; transform: rotate(180deg); white-space: nowrap; line-height: 1; }
  .vhead .d { font-weight: 600; }
  .vhead .m { font-size: 11px; color: #6c757d; }
  .chip { display: inline-block; padding: 1px 4px; margin: 1px 0; border-radius: 3px; background: #eef2f7; font-size: 11px; white-space: nowrap; max-width: 100%; overflow: hidden; text-overflow: ellipsis; }
  .sum-cell { vertical-align: top; }
  .table td, .table th { border: 1px solid #e2e5e9; }
  .table thead th { border-bottom: 2px solid #d9dde3; }
</style>

<div class="table-responsive">
  <table class="table table-bordered table-hover table-sm text-center" id="datesheetSummaryTable">
    <thead class="thead-light">
      <tr>
        <th class="sum-sticky sum-sec text-left align-middle bg-secondary text-white">Class — Section</th>
        <?php foreach ($dateRange as $d): $ts = strtotime($d['date']); ?>
          <th class="sum-date align-middle bg-light" title="<?= date('D, j M Y', $ts) ?>">
            <div class="vhead">
              <span class="d"><?= date('D', $ts) ?></span>
              <span class="m"><?= date('j M', $ts) ?></span>
            </div>
          </th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($sectionLabels as $sid => $label): ?>
        <tr>
          <td class="sum-sticky sum-sec text-left align-middle bg-light" title="<?= esc($label) ?>"><?= esc($label) ?></td>
          <?php foreach ($dateRange as $d): 
            $day = $d['date'];
            $subs = $matrix[$sid][$day] ?? [];
          ?>
            <td class="sum-cell text-left">
              <?php if (!empty($subs)): ?>
                <?php foreach ($subs as $s):
                  // keep chips compact (short label for display, full on title)
                  $short = mb_strimwidth($s, 0, 10, ''); ?>
                  <span class="chip" title="<?= esc($s) ?>"><?= esc($short) ?></span><br>
                <?php endforeach; ?>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
