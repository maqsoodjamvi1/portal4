<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-8">
        <h1><i class="fas fa-calendar-alt"></i> Add Terms</h1>
      </div>
      <div class="col-sm-4">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Terms</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="card card-primary">
    <div class="card-header p-2">
      <ul class="nav nav-pills">
        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/terms') ?>">Terms List</a></li>
        <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/terms/add') ?>">Add Terms</a></li>
      </ul>
    </div>
    <div class="card-body">
      <?= form_open(base_url('admin/terms/save'), ['id' => 'term-edit-form']) ?>
      <div class="table-responsive">
        <table class="table table-bordered table-striped" id="dynamic_field">
          <thead class="thead-light">
            <tr>
              <th>Term Name</th>
              <th>Short Name</th>
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
                    <input type="hidden" name="rowscount[]" value="1">
                    <input type="hidden" name="id<?= $i ?>" value="<?= $value->term_id ?>">
                    <input type="text" name="name<?= $i ?>" class="form-control" value="<?= esc($value->name) ?>" required>
                  </td>
                  <td>
                    <input type="text" name="short_name<?= $i ?>" class="form-control" value="<?= esc($value->short_name) ?>" required>
                  </td>
                   <td></td> <!-- no delete button for existing row -->
                </tr>
              <?php $i++; endforeach;
            else :
              for ($j = 0; $j < 3; $j++) : ?>
                <tr id="row<?= $j ?>">
                  <td>
                    <input type="hidden" name="rowscount[]" value="1">
                    <input type="hidden" name="id<?= $j ?>" value="0">
                    <input type="text" name="name<?= $j ?>" class="form-control" placeholder="e.g. Term <?= $j + 1 ?>" required>
                  </td>
                  <td>
                    <input type="text" name="short_name<?= $j ?>" class="form-control" placeholder="e.g. T<?= $j + 1 ?>" required>
                  </td>
                   <td>
        <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
      </td>
                </tr>
            <?php $i = $j + 1; endfor; endif; ?>
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
            <input type="text" name="name${i}" class="form-control" placeholder="Term Name" required>
          </td>
          <td>
            <input type="text" name="short_name${i}" class="form-control" placeholder="Short Name" required>
          </td>
           <td>
        <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
      </td>
        </tr>`;

      $('#dynamic_field tbody').append(newRow);
      i++;
      $('#total_rows').val(i);
    });


// Only remove dynamically added rows
$('#dynamic_field').on('click', '.remove-row', function () {
  $(this).closest('tr').remove();
  $('#total_rows').val($('#dynamic_field tbody tr').length);
});


    $('#term-edit-form').ajaxForm({
      beforeSubmit: function () {
        $('#total_rows').val($('#dynamic_field tbody tr').length);
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        $('#term-edit-form :input').prop('disabled', true);
      },

      
      success: function (responseText) {
        const json = responseText;
        
        $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save');
        $('#term-edit-form :input').prop('disabled', false);

        if(json.term_session_id == false){
            window.location.href = '<?php echo base_url(); ?>admin/terms_session/add';
            return;
        }
        
        if (json.success) {
          toastr.success(json.msg);
          window.location.href = "<?= base_url('admin/terms') ?>";
        } else {
          toastr.error(json.msg);
        }
      },
      error: function () {
        toastr.error('Something went wrong. Please try again.');
        $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save');
        $('#term-edit-form :input').prop('disabled', false);
      }
    });
  });
</script>

<?= $this->endSection() ?>
