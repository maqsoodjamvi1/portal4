<form id="schoolWizardForm">
<div class="table-responsive">
  <table class="table table-bordered table-striped" id="dynamic_field">
    <thead class="table-light">
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
              <input type="hidden" name="id<?= $i ?>" value="<?= $value->class_id ?>">
              <input type="text" name="class_name<?= $i ?>" class="form-control" value="<?= esc($value->class_name) ?>" required>
            </td>
            <td>
              <input type="text" name="class_short_name<?= $i ?>" class="form-control" value="<?= esc($value->class_short_name) ?>" required>
            </td>
            <td></td>
          </tr>
      <?php $i++; endforeach;
      endif; ?>
    </tbody>
  </table>
  <button type="button" name="add" id="add" class="btn btn-success"><i class="fas fa-plus"></i> Add Row</button>
</div>

<input type="hidden" name="total_rows" id="total_rows" value="<?= $i ?>">
</form>

  <script>
    $(document).ready(function () {
      let i = <?= $i ?>;

      $('#add').click(function () {
        $('#dynamic_field tbody').append(`
        <tr id="row${i}">
          <td>
            <input type="hidden" name="rowscount[]" value="${i}">
            <input type="hidden" name="id${i}" value="0">
            <input type="text" name="class_name${i}" class="form-control" placeholder="Class Name" required>
          </td>
          <td>
            <input type="text" name="class_short_name${i}" class="form-control" placeholder="Short Name" required>
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
        $('#total_rows').val($('#dynamic_field tbody tr').length);
      });
    });
  </script>
