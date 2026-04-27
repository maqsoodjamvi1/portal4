<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-8">
        <h1><i class="fas fa-school"></i> Add Subject
          <?php if (empty($sections_info->sid)) : ?>
            <span class="badge badge-success float-right">Step 7 of 10: System Configuration</span>
            <audio autoplay controls hidden>
              <source src="audio/Step7subjects.m4a" type="audio/mpeg">
            </audio>
          <?php endif; ?>
        </h1>
      </div>
      <div class="col-sm-4">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Subject</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="card card-primary">
    <div class="card-header p-2">
      <ul class="nav nav-pills">
        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/subjects') ?>">Subject List</a></li>
        <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/subjects/add') ?>">Add Subject</a></li>
      </ul>
    </div>
    <div class="card-body">
      <?= form_open(base_url('admin/subjects/save'), ['id' => 'subjects-edit-form']) ?>
      <div class="table-responsive">

        <table class="table table-bordered table-striped" id="dynamic_field">
          <thead class="thead-light">
            <tr>
              <th>Subject Name</th>
              <th>Short Name</th>
               <th>Action</th> <!-- Action for dynamic rows only -->
              
            </tr>
          </thead>
          <tbody>
         <?php
$i = 0;
if (!empty($info)) :
    foreach ($info as $value) :

?>

    <tr id="row<?= $i ?>">
      <td>
        <input type="hidden" name="rowscount[]" value="1">
        <input type="hidden" name="id<?= $i ?>" value="<?= $value->sid ?>">
        <input type="text" name="subject_name<?= $i ?>" class="form-control" value="<?= esc($value->subject_name) ?>" required>
      </td>
      <td>
        <input type="text" name="subject_short_name<?= $i ?>" class="form-control" value="<?= esc($value->subject_short_name) ?>" required>
      </td>
      <td></td>
    </tr>
<?php
    $i++;
    endforeach;
else:
    $j = 0;
?>
    <tr id="row<?= $j ?>">
      <td>
        <input type="hidden" name="rowscount[]" value="1">
        <input type="hidden" name="id<?= $j ?>" value="0">
        <input type="text" name="subject_name<?= $j ?>" class="form-control" placeholder="e.g. Math <?= $j + 1 ?>" required>
      </td>
      <td>
        <input type="text" name="subject_short_name<?= $j ?>" class="form-control" placeholder="e.g. Mth<?= $j + 1 ?>" required>
      </td>
      <td>
        <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
      </td>
    </tr>
<?php
    $j++;
    for ($k = $j; $k < 3; $k++) :
?>
    <tr id="row<?= $k ?>">
      <td>
        <input type="hidden" name="rowscount[]" value="1">
        <input type="hidden" name="id<?= $k ?>" value="0">
        <input type="text" name="subject_name<?= $k ?>" class="form-control" placeholder="e.g. Subject <?= $k + 1 ?>" required>
      </td>
      <td>
        <input type="text" name="subject_short_name<?= $k ?>" class="form-control" placeholder="e.g. Sub<?= $k + 1 ?>" required>
      </td>
      <td>
        <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
      </td>
    </tr>
<?php endfor; $i = $k; endif; ?>

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
        <input type="hidden" name="rowscount[]" value="1">
        <input type="hidden" name="id${i}" value="0">
        <input type="text" name="subject_name${i}" class="form-control" placeholder="Subject Name" required>
      </td>
      <td>
        <input type="text" name="subject_short_name${i}" class="form-control" placeholder="Short Name" required>
      </td>
      <td>
        <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
      </td>
    </tr>`;
  
  $('#dynamic_field tbody').append(newRow); // ✅ Append only once
  i++;
  $('#total_rows').val(i); // ✅ Update hidden row count field
});



// Only remove dynamically added rows
$('#dynamic_field').on('click', '.remove-row', function () {
  $(this).closest('tr').remove();
  $('#total_rows').val($('#dynamic_field tbody tr').length);
});


$('#subjects-edit-form').ajaxForm({
  dataType: 'json',
  beforeSubmit: function () {
     $('#total_rows').val($('#dynamic_field tbody tr').length); // Ensure latest count
    $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    $('#subjects-edit-form :input').prop('disabled', true);
    
  },
success: function (json) {
  $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save');
  $('#subjects-edit-form :input').prop('disabled', false);

  if (json.sec_sub_id === false) {
        window.location.href = '<?= base_url('admin/section_subjects/add') ?>';
        return;
  }

  if (json.success) {
    toastr.success(json.msg);
    location.href = '<?= base_url('admin/subjects') ?>';
  } else {
    toastr.error(json.msg);
    // Optional: if you want to redirect even if sec_sub_id is missing
    // window.location.href = '<?= base_url('admin/subjects?m=add') ?>';
  }
}
});
});
 
</script>

<?= $this->endSection() ?>
