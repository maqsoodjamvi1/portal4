<?php
$months = $billing_months ?? [];
$plans  = $fee_plans ?? [];
$planCount = (int) ($plan_count ?? count($plans));
$activeSlots = (int) ($active_slots ?? 0);
$updateUrl = base_url('admin/fee_plan_months/updateFeePlanMonth');
$csrfName = csrf_token();
$csrfHash = csrf_hash();
?>
<div class="fee-setup-panel fee-setup-months">
  <div class="setup-toolbar">
    <div class="setup-meta">
      <div class="meta-item">
        <label>Fee plans</label>
        <span class="meta-value"><?= $planCount ?></span>
      </div>
      <div class="meta-item">
        <label>Months per year</label>
        <span class="meta-value">12</span>
      </div>
      <div class="meta-item">
        <label>Active selections</label>
        <span class="meta-value" id="fee-setup-active-slots"><?= $activeSlots ?></span>
      </div>
    </div>
    <div class="setup-toolbar-actions">
      <span class="text-muted small"><i class="fas fa-info-circle"></i> Saves automatically</span>
    </div>
  </div>

  <?php if (empty($plans)) : ?>
    <div class="alert alert-warning mb-0">
      <i class="fas fa-exclamation-triangle me-1"></i>
      No fee plans found. Configure fee plans before assigning billing months.
    </div>
  <?php else : ?>
    <div class="grid-card">
      <div class="grid-scroll">
        <table class="table fee-setup-table plan-months-table mb-0">
          <thead>
            <tr>
              <th class="col-plan">Fee plan</th>
              <?php foreach ($months as $month) : ?>
              <th class="col-month text-center">
                <span class="month-label"><?= esc($month) ?></span>
                <button type="button" class="btn btn-link btn-sm col-select-all p-0" data-month="<?= esc($month) ?>" title="Toggle all plans for <?= esc($month) ?>">all</button>
              </th>
              <?php endforeach; ?>
              <th class="col-actions text-center">Row</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($plans as $plan) : ?>
            <tr data-plan-id="<?= (int) $plan['plan_id'] ?>">
              <th scope="row" class="col-plan">
                <span class="plan-name"><?= esc($plan['plan_name']) ?></span>
                <span class="plan-active-count text-muted">(<?= (int) $plan['active_count'] ?>/12)</span>
              </th>
              <?php foreach ($months as $month) :
                $checked = !empty($plan['months'][$month]);
                $cellValue = esc($month) . '_' . (int) $plan['plan_id'];
              ?>
              <td class="col-month text-center">
                <div class="form-check form-check plan-month-check">
                  <input type="checkbox"
                    class="form-check-input plan-month-cb"
                    id="pm-<?= (int) $plan['plan_id'] ?>-<?= esc($month) ?>"
                    value="<?= $cellValue ?>"
                    data-plan-id="<?= (int) $plan['plan_id'] ?>"
                    data-month="<?= esc($month) ?>"
                    <?= $checked ? 'checked' : '' ?>>
                  <label class="form-check-label" for="pm-<?= (int) $plan['plan_id'] ?>-<?= esc($month) ?>"></label>
                </div>
              </td>
              <?php endforeach; ?>
              <td class="col-actions text-center">
                <button type="button" class="btn btn-sm btn-outline-primary row-select-all" data-plan-id="<?= (int) $plan['plan_id'] ?>">All</button>
                <button type="button" class="btn btn-sm btn-outline-secondary row-select-none" data-plan-id="<?= (int) $plan['plan_id'] ?>">None</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

  <div class="setup-footer setup-wizard-footer-placeholder mt-3">
    <?php if (!empty($standalone)) : ?>
    <p class="setup-hint mb-0">
      <?php if (empty($plans)) : ?>
        Configure fee plans to assign billing months. Changes save automatically when you tick or untick a cell.
      <?php else : ?>
        Tick a cell to include that month in the fee plan for challan generation. Untick to exclude.
        Use <strong>All</strong> / <strong>None</strong> on a row, or <strong>all</strong> under a month column for bulk changes.
      <?php endif; ?>
    </p>
    <?php else : ?>
    <?= view('admin/partials/setup_wizard_footer', [
        'hint' => empty($plans)
            ? 'Configure fee plans to assign billing months. You can still go back to previous steps.'
            : 'Tick a cell to include that month in the fee plan for challan generation. Untick to exclude. Use <strong>All</strong> / <strong>None</strong> on a row, or <strong>all</strong> under a month column for bulk changes.',
        'show_prev' => !empty($wizard_prev_tab),
        'show_next' => !empty($wizard_next_tab),
        'show_finish' => !empty($wizard_is_last),
        'prev_tab' => $wizard_prev_tab ?? '',
        'next_tab' => $wizard_next_tab ?? '',
        'prev_label' => 'Previous: Fee Structure',
        'finish_label' => 'Finish Setup',
    ]) ?>
    <?php endif; ?>
  </div>
</div>

<script>
$(function () {
  var updateUrl = <?= json_encode($updateUrl) ?>;
  var csrfName = <?= json_encode($csrfName) ?>;
  var csrfHash = <?= json_encode($csrfHash) ?>;
  var pendingSaves = 0;
  var toastShown = false;

  function refreshActiveCount() {
    var n = $('.plan-month-cb:checked').length;
    $('#fee-setup-active-slots').text(n);
  }

  function refreshRowCount($row) {
    var total = $row.find('.plan-month-cb').length;
    var on = $row.find('.plan-month-cb:checked').length;
    $row.find('.plan-active-count').text('(' + on + '/' + total + ')');
  }

  function saveCheckbox($cb, silentBatch) {
    var status = $cb.prop('checked') ? 1 : 0;
    var payload = { plan_month_id: $cb.val(), status: status };
    payload[csrfName] = csrfHash;

    pendingSaves++;
    $cb.prop('disabled', true);

    $.ajax({
      type: 'POST',
      url: updateUrl,
      data: payload,
      dataType: 'json'
    }).always(function () {
      $cb.prop('disabled', false);
      pendingSaves--;
      if (pendingSaves <= 0 && !silentBatch) {
        if (!toastShown) {
          toastShown = true;
          toastr.success('Plan months updated');
          setTimeout(function () { toastShown = false; }, 800);
        }
      }
    });
  }

  $('.plan-month-cb').on('change', function () {
    var $cb = $(this);
    var $row = $cb.closest('tr');
    saveCheckbox($cb, false);
    refreshActiveCount();
    refreshRowCount($row);
  });

  $('.row-select-all').on('click', function () {
    var planId = $(this).data('plan-id');
    var $row = $('tr[data-plan-id="' + planId + '"]');
    var $boxes = $row.find('.plan-month-cb').not(':checked');
    if (!$boxes.length) return;
    pendingSaves = 0;
    toastShown = true;
    $boxes.each(function () {
      $(this).prop('checked', true);
      saveCheckbox($(this), true);
    });
    refreshActiveCount();
    refreshRowCount($row);
    toastr.success('All months enabled for plan');
    setTimeout(function () { toastShown = false; }, 800);
  });

  $('.row-select-none').on('click', function () {
    var planId = $(this).data('plan-id');
    var $row = $('tr[data-plan-id="' + planId + '"]');
    var $boxes = $row.find('.plan-month-cb:checked');
    if (!$boxes.length) return;
    toastShown = true;
    $boxes.each(function () {
      $(this).prop('checked', false);
      saveCheckbox($(this), true);
    });
    refreshActiveCount();
    refreshRowCount($row);
    toastr.success('All months disabled for plan');
    setTimeout(function () { toastShown = false; }, 800);
  });

  $('.col-select-all').on('click', function () {
    var month = $(this).data('month');
    var $boxes = $('.plan-month-cb[data-month="' + month + '"]');
    var turnOn = $boxes.filter(':checked').length < $boxes.length;
    toastShown = true;
    $boxes.each(function () {
      var $cb = $(this);
      if ($cb.prop('checked') === turnOn) return;
      $cb.prop('checked', turnOn);
      saveCheckbox($cb, true);
    });
    $('tr[data-plan-id]').each(function () { refreshRowCount($(this)); });
    refreshActiveCount();
    toastr.success(turnOn ? 'Month enabled for all plans' : 'Month disabled for all plans');
    setTimeout(function () { toastShown = false; }, 800);
  });
});
</script>
