<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-8">
        <h1><i class="fas fa-school"></i> Add Classes
          <?php if (empty($classes_info->class_id)) : ?>
            <span class="badge badge-success float-right">Step 4 of 10: System Configuration</span>
            <audio autoplay controls hidden>
              <source src="audio/Step6Classes.m4a" type="audio/mpeg">
            </audio>
          <?php endif; ?>
        </h1>
      </div>
      <div class="col-sm-4">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Classes</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="card card-primary">
    <div class="card-header p-2">
      <ul class="nav nav-pills">
        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/classes') ?>">Class List</a></li>
        <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/classes/add') ?>">Add Class</a></li>
      </ul>
    </div>
    <div class="card-body">
      <?= form_open(base_url('admin/classes/save'), ['id' => 'classes-edit-form']) ?>
      <div class="table-responsive">
        <table class="table table-bordered table-striped" id="dynamic_field">
          <thead class="thead-light">
            <tr>
              <th>Class Name</th>
              <th>Short Name</th>
              <th>Action</th>
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
                      <input type="hidden" name="id<?= $i ?>" value="<?= (int)$value->class_id ?>">
                      <input type="text" name="class_name<?= $i ?>" class="form-control"
                             value="<?= esc($value->class_name) ?>" required>
                    </td>
                    <td>
                      <input type="text" name="class_short_name<?= $i ?>" class="form-control"
                             value="<?= esc($value->class_short_name) ?>" required maxlength="10">
                    </td>
                    <td></td> <!-- No delete button for existing rows -->
                  </tr>
                <?php $i++; endforeach;
              else:
                // Default example rows when no data present
                $defaults = [
                  ['name' => 'Grade 1', 'short' => 'G1'],
                  ['name' => 'Grade 2', 'short' => 'G2'],
                  ['name' => 'Grade 3', 'short' => 'G3'],
                ];
                foreach ($defaults as $row) : ?>
                  <tr id="row<?= $i ?>">
                    <td>
                      <input type="hidden" name="rowscount[]" value="<?= $i ?>">
                      <input type="hidden" name="id<?= $i ?>" value="0">
                      <input type="text" name="class_name<?= $i ?>" class="form-control"
                             value="<?= esc($row['name']) ?>" required>
                    </td>
                    <td>
                      <input type="text" name="class_short_name<?= $i ?>" class="form-control"
                             value="<?= esc($row['short']) ?>" required maxlength="10">
                    </td>
                    <td>
                      <button type="button" class="btn btn-danger btn-sm remove-row">
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                <?php $i++; endforeach;
              endif;
            ?>
          </tbody>
        </table>

        <button type="button" name="add" id="add" class="btn btn-success">
          <i class="fas fa-plus"></i> Add Row
        </button>
      </div>

      <div class="mt-4 text-right">
        <button type="submit" id="submitBtn" class="btn btn-primary">
          <i class="fas fa-save"></i> Save
        </button>
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
    let i = <?= (int)$i ?>;

    $('#add').click(function () {
      $('#dynamic_field tbody').append(`
<tr id="row${i}">
  <td>
    <input type="hidden" name="rowscount[]" value="${i}">
    <input type="hidden" name="id${i}" value="0">
    <input type="text" name="class_name${i}" class="form-control" placeholder="Class Name" required>
  </td>
  <td>
    <input type="text" name="class_short_name${i}" class="form-control" placeholder="Short Name" required maxlength="10">
  </td>
  <td>
    <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
  </td>
</tr>`);
      i++;
      $('#total_rows').val(i);
    });

    $('#dynamic_field').on('click', '.remove-row', function () {
      $(this).closest('tr').remove();
      // Keep total_rows in sync with current number of rows
      $('#total_rows').val($('#dynamic_field tbody tr').length);
    });

    $('#classes-edit-form').ajaxForm({
      beforeSubmit: function () {
        $('#total_rows').val($('#dynamic_field tbody tr').length);
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        $('#classes-edit-form :input').prop('disabled', true);
      },
      success: function (responseText) {
        $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save');
        $('#classes-edit-form :input').prop('disabled', false);

        const json = responseText;
        // const json = $.parseJSON(responseText);

        if (json.section_id === false) {
          window.location.href = '<?= base_url('admin/sections/add') ?>';
          return;
        }

        if (json.success) {
          toastr.success(json.msg);
          location.href = '<?= base_url('admin/classes') ?>';
        } else {
          toastr.error(json.msg);
        }
      }
    });
  });
</script>

<?= $this->endSection() ?>