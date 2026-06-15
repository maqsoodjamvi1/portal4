<?php $uiNeedsDataTables = false; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
$feeTypeIsNew = empty($info->fee_type_id ?? null);
$feeTypeSetupBadge = $feeTypeIsNew
    ? '<span class="badge text-bg-success">Step 9 of 10: System Configuration</span>'
    : '';
?>
<?php if ($feeTypeIsNew): ?>
<audio autoplay controls hidden>
  <source src="audio/Step6Classes.m4a" type="audio/mpeg">
</audio>
<?php endif; ?>
<?= view('components/page_header', [
    'title' => $feeTypeIsNew ? 'Add Fee Type' : 'Edit Fee Type',
    'icon' => 'fas fa-coins',
    'actionsHtml' => $feeTypeSetupBadge !== '' ? '<div class="text-sm-right">' . $feeTypeSetupBadge . '</div>' : null,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Fee Type', 'url' => base_url('admin/fee_type')],
        ['label' => $feeTypeIsNew ? 'Add' : 'Edit', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="card sms-card card-primary">
    <div class="card-header p-2">
      <ul class="nav nav-pills">
        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/fee_type') ?>">Fee Type List</a></li>
        <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/fee_type/add') ?>">Add Fee Type</a></li>
      </ul>
    </div>
    <div class="card-body">
      <?= form_open(base_url('admin/fee_type/save'), ['id' => 'fee-type-form', 'class' => 'needs-validation', 'novalidate' => 'novalidate']) ?>
      <div class="table-responsive">
        <table class="table table-bordered table-striped" id="dynamic_field">
          <thead class="table-light">
            <tr>
              <th>Fee Type Name</th>
              <th>Action</th> <!-- Action for dynamic rows only -->
            </tr>
          </thead>
          <tbody>
            <?php
            $i = 0;
            if (!empty($info)) :
             foreach ($info as $value) : ?>
  <tr id="row<?= $i ?>">
    <td>
      <input type="hidden" name="rowscount[]" value="<?= $i ?>">
      <input type="hidden" name="id<?= $i ?>" value="<?= $value->fee_type_id ?>">
      <input type="text" name="fee_type_name<?= $i ?>" class="form-control" value="<?= esc($value->fee_type_name) ?>" required>
    </td>
    <td>
      <span class="badge text-bg-<?=  $value->is_monthly_fee ? 'info' : 'secondary' ?>">
        <?= $value->is_monthly_fee ? 'Monthly Fee' : 'Other Fee' ?>
      </span>
    </td>
  </tr>
<?php $i++; endforeach;
            else :
             for ($j = 0; $j < 3; $j++) : ?>
  <tr id="row<?= $j ?>">
    <td>
      <input type="hidden" name="rowscount[]" value="<?= $j ?>">
      <input type="hidden" name="id<?= $j ?>" value="0">
      <input type="text" name="fee_type_name<?= $j ?>" class="form-control" placeholder="e.g. Tuition Fee" required>
    </td>
    <td>
      <span class="badge text-bg-info"><?= $j === 0 ? 'Monthly Fee' : 'Other Fee' ?></span>
    </td>
  </tr>
            <?php endfor; $i = $j; endif; ?>
          </tbody>
        </table>
        <button type="button" name="add" id="add" class="btn btn-success"><i class="fas fa-plus"></i> Add Row</button>
      </div>

      <div class="mt-4 text-end">
        <button type="submit" id="submitBtn" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
        <button type="button" onclick="history.back();" class="btn btn-light">Cancel</button>
      </div>
      <input type="hidden" name="total_rows" id="total_rows" value="<?= $i ?>">
      <?= form_close() ?>
    </div>
  </div>
</section>
<script>
  $(document).ready(function () {
    let i = <?= $i ?>;

    $('#add').click(function () {
      const newRow = `
        <tr id="row${i}">
          <td>
            
            <input type="hidden" name="rowscount[]" value="${i}">
            <input type="hidden" name="id${i}" value="0">
            <input type="text" name="fee_type_name${i}" class="form-control" placeholder="Fee Type Name" required>
          </td>
          <td>
            <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
          </td>
        </tr>`;
      $('#dynamic_field tbody').append(newRow);
      i++;
      $('#total_rows').val(i);
    });

    // Remove dynamically added row
    $('#dynamic_field').on('click', '.remove-row', function () {
      $(this).closest('tr').remove();
      $('#total_rows').val($('#dynamic_field tbody tr').length);
    });

    // Handle form submission
    $('#fee-type-form').ajaxForm({
      dataType: 'json',
      beforeSubmit: function () {
        $('#total_rows').val($('#dynamic_field tbody tr').length);
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        $('#fee-type-form :input').prop('disabled', true);
      },
      success: function (response) {
        $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save');
        $('#fee-type-form :input').prop('disabled', false);


        if (response.amount_id === false) {
            window.location.href = '<?= base_url('admin/fee_setup?tab=amounts') ?>';
            return;
        }

        if (response.success) {
          toastr.success(response.msg);
          location.href = '<?= base_url('admin/fee_setup?tab=types') ?>';
        } else {
          toastr.error(response.msg);
        }
      },
      error: function () {
        toastr.error('Something went wrong. Please try again.');
        $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save');
        $('#fee-type-form :input').prop('disabled', false);
      }
    });
  });
</script>
<?= $this->endSection() ?>
