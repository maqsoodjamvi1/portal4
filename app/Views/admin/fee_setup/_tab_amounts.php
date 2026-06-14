<?php
if (empty($has_fee_types)) : ?>
  <div class="alert alert-warning mb-0">
    <i class="fas fa-exclamation-triangle me-1"></i>
    No active fee types found. Please <a href="<?= base_url('admin/fee_setup?tab=types') ?>">add fee types</a> first.
  </div>
<?php elseif (empty($has_session)) : ?>
  <div class="alert alert-warning mb-0">
    No academic session is configured. Set up a session before defining fee amounts.
  </div>
<?php else :
  $show_flag_selector = ($campus_flags->daycare_flag == 1 && $campus_flags->boarding_flag == 1);
  if ($campus_flags->daycare_flag == 1 && $campus_flags->boarding_flag == 1) {
      $default_flag = 1;
  } elseif ($campus_flags->daycare_flag == 1) {
      $default_flag = 1;
  } elseif ($campus_flags->boarding_flag == 1) {
      $default_flag = 2;
  } else {
      $default_flag = 0;
  }
  $classCount = count($classesinfo);
  $feeTypeCount = count($fee_type_info);
?>
<div class="fee-setup-panel fee-setup-amounts">
  <div class="setup-toolbar">
    <div class="setup-meta">
      <div class="meta-item">
        <label>Academic session</label>
        <span class="meta-value"><?= esc($current_academic_sessioninfo->session_name) ?></span>
      </div>
      <div class="meta-item">
        <label>Classes</label>
        <span class="meta-value"><?= (int) $classCount ?></span>
      </div>
      <div class="meta-item">
        <label>Fee types</label>
        <span class="meta-value"><?= (int) $feeTypeCount ?></span>
      </div>
      <div class="meta-item">
        <label>Max monthly fee</label>
        <span class="meta-value <?= $max_fee ? '' : 'text-muted' ?>"><?= $max_fee ? number_format((float) $max_fee) : 'Not set' ?></span>
      </div>
    </div>

    <?php if ($show_flag_selector) : ?>
    <div class="student-type-toggle">
      <div class="btn-group btn-group-sm btn-group-toggle" data-bs-toggle="buttons">
        <label class="btn btn-outline-primary <?= $fee_flag == 1 ? 'active' : '' ?>">
          <input type="radio" name="fee_flag_ui" value="1" autocomplete="off" <?= $fee_flag == 1 ? 'checked' : '' ?>> Daycare
        </label>
        <label class="btn btn-outline-primary <?= $fee_flag == 2 ? 'active' : '' ?>">
          <input type="radio" name="fee_flag_ui" value="2" autocomplete="off" <?= $fee_flag == 2 ? 'checked' : '' ?>> Boarding
        </label>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <?= form_open(base_url('admin/fee_amount/save'), ['id' => 'fee-setup-amount-form']) ?>
  <?= form_hidden('session_id', (string) $session_id) ?>
  <?= form_hidden('from_setup', '1') ?>
  <input type="hidden" name="std_type" id="fee-setup-std-type" value="<?= (int) ($fee_flag ?: $default_flag) ?>">
  <?php if (!$show_flag_selector) : ?>
    <input type="hidden" name="fee_flag" value="<?= (int) $default_flag ?>">
  <?php endif; ?>

  <div class="grid-card grid-card--full">
    <div class="table-responsive fee-amount-table-wrap">
      <table class="table fee-setup-table fee-structure-table mb-0">
        <thead>
          <tr class="fee-head-names">
            <th class="col-class" rowspan="2">Class</th>
            <?php foreach ($fee_type_info as $fee) : ?>
            <th class="text-center col-fee <?= $fee->is_monthly_fee ? 'col-fee--monthly' : '' ?>"
              <?= $fee->is_monthly_fee ? 'title="Monthly fee"' : '' ?>>
              <?= esc($fee->fee_type_name) ?>
            </th>
            <?php endforeach; ?>
          </tr>
          <tr class="fee-head-fill">
            <?php foreach ($fee_type_info as $fee) : ?>
            <th class="text-center col-fee col-fee-fill <?= $fee->is_monthly_fee ? 'col-fee--monthly' : '' ?>">
              <label class="fill-all-label mb-1 d-block">Fill all rows</label>
              <input type="number"
                class="form-control form-control-sm text-center repeat-input"
                data-fee-type="<?= (int) $fee->fee_type_id ?>"
                placeholder="Amount"
                min="0"
                step="1"
                tabindex="-1"
                aria-label="Fill all classes for <?= esc($fee->fee_type_name) ?>">
            </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($classesinfo as $class) : ?>
          <tr>
            <th scope="row" class="col-class"><?= esc($class->class_name) ?></th>
            <?php foreach ($fee_type_info as $fee) :
              $amountIdKey = $fee->fee_type_id . '_' . $class->class_id . '_amount_id';
              $current_amount = $current_fees[$fee->fee_type_id][$class->class_id] ?? '';
              $amount_id = $amount_ids[$fee->fee_type_id][$class->class_id] ?? 0;
            ?>
            <td class="text-center col-fee <?= $fee->is_monthly_fee ? 'col-fee--monthly' : '' ?>">
              <input type="hidden" name="<?= esc($amountIdKey) ?>" value="<?= (int) $amount_id ?>">
              <input type="number"
                class="form-control amount-input <?= $fee->is_monthly_fee ? 'monthly-fee' : '' ?>"
                name="ftv<?= (int) $fee->fee_type_id ?>_ci<?= (int) $class->class_id ?>_amount"
                value="<?= esc((string) $current_amount) ?>"
                min="0"
                step="1"
                placeholder="0"
                <?= ($fee->is_monthly_fee && $max_fee) ? 'max="' . (float) $max_fee . '"' : '' ?>>
              <?php if (isset($prev_fees[$fee->fee_type_id][$class->class_id])) : ?>
                <span class="prev-amount">Last session: <?= number_format((float) $prev_fees[$fee->fee_type_id][$class->class_id]) ?></span>
              <?php endif; ?>
            </td>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="setup-footer setup-wizard-footer-placeholder">
      <?= view('admin/partials/setup_wizard_footer', [
          'hint' => 'Enter amounts per class. Use <strong>Fill all rows</strong> in a column header to copy one value to every class.'
              . ($max_fee ? ' Monthly fee cannot exceed ' . number_format((float) $max_fee) . '.' : '')
              . ' Changes save when you navigate steps.',
          'show_prev' => !empty($wizard_prev_tab),
          'show_next' => !empty($wizard_next_tab),
          'show_finish' => !empty($wizard_is_last),
          'prev_tab' => $wizard_prev_tab ?? '',
          'next_tab' => $wizard_next_tab ?? '',
          'prev_label' => 'Previous: Fee Types',
          'next_label' => 'Next',
      ]) ?>
    </div>
  </div>
  <?= form_close() ?>
</div>

<script>
$(function () {
  $('input[name="fee_flag_ui"]').on('change', function () {
    window.location.href = '<?= base_url('admin/fee_setup?tab=amounts') ?>&force_flag=' + $(this).val();
  });

  $('.repeat-input').on('input', function () {
    const feeTypeId = $(this).data('fee-type');
    const val = $(this).val();
    $("input[name^='ftv" + feeTypeId + "_']").val(val).trigger('input');
  });

  window.saveFeeAmountsData = function (options) {
    options = options || {};
    var defer = $.Deferred();
    var form = $('#fee-setup-amount-form');
    if (!form.length) {
      defer.resolve();
      return defer.promise();
    }

    var valid = true;
    $('.monthly-fee').removeClass('is-invalid');
    $('.monthly-fee').each(function () {
      var max = parseFloat($(this).attr('max'));
      var value = parseFloat($(this).val()) || 0;
      if (max && value > max) {
        $(this).addClass('is-invalid');
        valid = false;
      }
    });
    if (!valid) {
      toastr.error('Some monthly fees exceed the campus limit.');
      defer.reject();
      return defer.promise();
    }

    var flagUi = $('input[name="fee_flag_ui"]:checked').val();
    var flagHidden = form.find('input[name="fee_flag"]').val();
    $('#fee-setup-std-type').val(flagUi || flagHidden || $('#fee-setup-std-type').val());

    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: form.serialize(),
      dataType: 'json'
    }).done(function (res) {
      if (res.success) {
        if (!options.silent) {
          toastr.success(res.msg);
        }
        defer.resolve(res);
      } else {
        toastr.error(res.msg || 'Save failed');
        defer.reject();
      }
    }).fail(function () {
      toastr.error('Could not save fee structure.');
      defer.reject();
    });
    return defer.promise();
  };
});
</script>
<?php endif; ?>
