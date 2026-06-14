<?php
$types = $fee_types ?? [];
$i = 0;
$typeCount = (int) ($type_count ?? count($types));
$activeCount = (int) ($active_type_count ?? 0);
$monthlyLocked = !empty($monthly_fee_locked);
$monthlyTypeId = 0;
foreach ($types as $_ft) {
    if ((int) $_ft->is_monthly_fee === 1) {
        $monthlyTypeId = (int) $_ft->fee_type_id;
        break;
    }
}
?>
<div class="fee-setup-panel fee-setup-types">
  <?php if ($monthlyLocked) : ?>
  <div class="alert alert-secondary border mb-3 py-2 px-3 mb-3">
    <i class="fas fa-lock me-2"></i>
    <strong>Monthly fee type is locked.</strong> It is used for fee calculations and challans and cannot be changed from this screen after it has been set.
  </div>
  <?php endif; ?>
  <div class="setup-toolbar">
    <div class="setup-meta">
      <div class="meta-item">
        <label>Total fee types</label>
        <span class="meta-value"><?= $typeCount ?></span>
      </div>
      <div class="meta-item">
        <label>Active</label>
        <span class="meta-value"><?= $activeCount ?></span>
      </div>
      <div class="meta-item">
        <label>Monthly fee</label>
        <span class="meta-value <?= $monthlyLocked ? '' : 'text-muted' ?>"><?= $monthlyLocked ? 'Locked' : 'Choose one type' ?></span>
      </div>
    </div>
    <div class="setup-toolbar-actions">
      <button type="button" id="fee-setup-add-type-row" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-plus me-1"></i> Add row
      </button>
    </div>
  </div>

  <?= form_open(base_url('admin/fee_type/save'), ['id' => 'fee-setup-type-form']) ?>
  <div class="grid-card">
    <div class="grid-scroll grid-scroll--auto">
      <table class="table fee-setup-table mb-0" id="fee-setup-types-table">
        <thead>
          <tr>
            <th class="col-label">Fee type name</th>
            <th class="col-monthly text-center">Monthly fee</th>
            <th class="col-status text-center">Status</th>
            <th class="col-actions text-center"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($types)) :
            foreach ($types as $value) : ?>
          <tr>
            <td class="col-label">
              <input type="hidden" name="rowscount[]" value="<?= $i ?>">
              <input type="hidden" name="id<?= $i ?>" value="<?= (int) $value->fee_type_id ?>">
              <input type="text" name="fee_type_name<?= $i ?>" class="form-control form-control-sm" value="<?= esc($value->fee_type_name) ?>" required>
            </td>
            <td class="col-monthly text-center">
              <input type="radio" name="monthly_fee_ui" class="set-monthly-fee" data-id="<?= (int) $value->fee_type_id ?>"
                <?= (int) $value->is_monthly_fee === 1 ? 'checked' : '' ?>
                <?= $monthlyLocked ? 'disabled' : '' ?>
                aria-label="Set as monthly fee">
            </td>
            <td class="col-status text-center">
              <input type="checkbox" class="toggle-fee-type-status" data-id="<?= (int) $value->fee_type_id ?>" <?= (int) $value->status === 1 ? 'checked' : '' ?>
                data-bs-toggle="toggle" data-size="mini" data-on="On" data-off="Off" data-onstyle="success" data-offstyle="secondary">
            </td>
            <td class="col-actions text-center"></td>
          </tr>
          <?php $i++; endforeach;
          else :
            for ($j = 0; $j < 3; $j++) : ?>
          <tr>
            <td class="col-label">
              <input type="hidden" name="rowscount[]" value="<?= $j ?>">
              <input type="hidden" name="id<?= $j ?>" value="0">
              <input type="text" name="fee_type_name<?= $j ?>" class="form-control form-control-sm" placeholder="e.g. Tuition Fee" required>
            </td>
            <td class="col-monthly text-center">
              <?php if ($j === 0) : ?><span class="badge rounded-pill badge-setup">Default</span><?php endif; ?>
            </td>
            <td class="col-status text-center"></td>
            <td class="col-actions text-center">
              <?php if ($j > 0) : ?><button type="button" class="btn btn-sm btn-outline-danger remove-type-row" title="Remove"><i class="fas fa-times"></i></button><?php endif; ?>
            </td>
          </tr>
          <?php endfor; $i = $j; endif; ?>
        </tbody>
      </table>
    </div>
    <div class="setup-footer setup-wizard-footer-placeholder">
      <?= view('admin/partials/setup_wizard_footer', [
          'hint' => $monthlyLocked
              ? 'You can edit fee type names and status. The monthly fee designation is fixed for data integrity.'
              : 'Define fee heads for challans. Choose exactly one <strong>monthly fee</strong> type — after the first save it will be locked. Changes save when you navigate steps.',
          'show_prev' => !empty($wizard_prev_tab),
          'show_next' => !empty($wizard_next_tab),
          'show_finish' => !empty($wizard_is_last),
          'prev_tab' => $wizard_prev_tab ?? '',
          'next_tab' => $wizard_next_tab ?? '',
          'prev_label' => 'Previous',
          'next_label' => 'Next: Fee Structure',
      ]) ?>
    </div>
  </div>
  <?= form_close() ?>
</div>

<script>
$(function () {
  let typeRowIndex = <?= (int) $i ?>;

  $('#fee-setup-add-type-row').on('click', function () {
    const row = `
      <tr>
        <td class="col-label">
          <input type="hidden" name="rowscount[]" value="${typeRowIndex}">
          <input type="hidden" name="id${typeRowIndex}" value="0">
          <input type="text" name="fee_type_name${typeRowIndex}" class="form-control form-control-sm" placeholder="Fee type name" required>
        </td>
        <td class="col-monthly text-center"></td>
        <td class="col-status text-center"></td>
        <td class="col-actions text-center">
          <button type="button" class="btn btn-sm btn-outline-danger remove-type-row"><i class="fas fa-times"></i></button>
        </td>
      </tr>`;
    $('#fee-setup-types-table tbody').append(row);
    typeRowIndex++;
  });

  $('#fee-setup-types-table').on('click', '.remove-type-row', function () {
    $(this).closest('tr').remove();
  });

  $('.toggle-fee-type-status').bootstrapToggle();

  $('.toggle-fee-type-status').on('change', function () {
    const id = $(this).data('id');
    $.post('<?= site_url('admin/fee_type/toggle-status') ?>', {
      fee_type_id: id,
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    }, function (res) {
      if (res.success) toastr.success(res.msg || 'Status updated');
      else toastr.error(res.msg || 'Update failed');
    }, 'json');
  });

  $('.set-monthly-fee').on('change', function () {
    if (this.disabled) {
      return;
    }
    const id = $(this).data('id');
    const $radio = $(this);
    $.post('<?= base_url('admin/fee_type/set-monthly-fee') ?>', {
      id: id,
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    }, function (res) {
      if (res.success) {
        toastr.success(res.msg);
        if (res.apply_lock_ui) {
          window.location.reload();
        }
      } else {
        toastr.error(res.msg);
        (function () {
          var id = <?= (int) $monthlyTypeId ?>;
          $('.set-monthly-fee').prop('checked', false);
          if (id) {
            $('.set-monthly-fee[data-id="' + id + '"]').prop('checked', true);
          }
        })();
      }
    }, 'json').fail(function () {
      toastr.error('Request failed');
      (function () {
        var id = <?= (int) $monthlyTypeId ?>;
        $('.set-monthly-fee').prop('checked', false);
        if (id) {
          $('.set-monthly-fee[data-id="' + id + '"]').prop('checked', true);
        }
      })();
    });
  });

  window.saveFeeTypesData = function (options) {
    options = options || {};
    var defer = $.Deferred();
    var $form = $('#fee-setup-type-form');
    if (!$form.length) {
      defer.resolve();
      return defer.promise();
    }
    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      dataType: 'json'
    }).done(function (response) {
      if (response.amount_id === false) {
        if (!options.silent) {
          toastr.info(response.msg || 'Fee structure not set up yet.');
        }
        defer.resolve(response);
        return;
      }
      if (response.success) {
        if (!options.silent) {
          toastr.success(response.msg);
        }
        defer.resolve(response);
      } else {
        toastr.error(response.msg);
        defer.reject();
      }
    }).fail(function () {
      toastr.error('Could not save fee types.');
      defer.reject();
    });
    return defer.promise();
  };
});
</script>
