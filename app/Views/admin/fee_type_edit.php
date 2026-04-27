<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-8">
        <h1><i class="fas fa-coins"></i> Add Fee Type</h1>
        <?php if (empty($info->fee_type_id)) : ?>
            <span class="badge badge-success float-right">Step 9 of 10: System Configuration</span>
            <audio autoplay controls hidden>
              <source src="audio/Step6Classes.m4a" type="audio/mpeg">
            </audio>
          <?php endif; ?>
      </div>
      <div class="col-sm-4">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Fee Type</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="card card-primary">
    <div class="card-header p-2">
      <ul class="nav nav-pills">
        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/fee_type') ?>">Fee Type List</a></li>
        <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/fee_type/add') ?>">Add Fee Type</a></li>
      </ul>
    </div>
    <div class="card-body">
      <?= form_open(base_url('admin/fee_type/save'), ['id' => 'fee-type-form']) ?>
      <div class="table-responsive">
        <table class="table table-bordered table-striped" id="dynamic_field">
          <thead class="thead-light">
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
      <span class="badge badge-<?= $value->is_monthly_fee ? 'info' : 'secondary' ?>">
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
      <span class="badge badge-info"><?= $j === 0 ? 'Monthly Fee' : 'Other Fee' ?></span>
    </td>
  </tr>
            <?php endfor; $i = $j; endif; ?>
          </tbody>
        </table>
        <button type="button" name="add" id="add" class="btn btn-success"><i class="fas fa-plus"></i> Add Row</button>
      </div>

      <div class="mt-4 text-right">
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
            window.location.href = '<?= base_url('admin/fee_amount/add') ?>';
            return;
        }

        if (response.success) {
          toastr.success(response.msg);
          location.href = '<?= base_url('admin/fee_type') ?>';
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
