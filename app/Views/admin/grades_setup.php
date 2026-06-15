<?php $uiNeedsDataTables = false; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Grades & Grading Policy',
    'icon' => 'fas fa-layer-group',
    'subtitle' => 'Define each grade letter and its percentage range on one screen.',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Grades', 'url' => base_url('admin/grades')],
        ['label' => 'Setup', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card sms-card card-primary card-outline">
        <div class="card-header">
          <h3 class="card-title mb-0"><i class="fas fa-layer-group me-1"></i> Grade bands</h3>
        </div>
        <div class="card-body">
          <div class="alert alert-light border mb-3">
            <strong>Tip:</strong> List grades from <strong>highest</strong> to <strong>lowest</strong> (e.g. A+ then A, B, … F).
            When you change <em>% To</em>, the next row’s <em>% From</em> updates automatically.
            Mark one grade as <em>Fail</em> if needed (usually the lowest).
          </div>

          <?= form_open(base_url('admin/grades/save-setup'), ['id' => 'grades-setup-form', 'role' => 'form']) ?>

          <div class="table-responsive">
            <table class="table table-bordered table-hover mb-2" id="grades-policy-table">
              <thead class="table-light">
                <tr>
                  <th style="width:120px">Grade</th>
                  <th>Detail <small class="text-muted">(optional)</small></th>
                  <th style="width:100px" class="text-center">% From</th>
                  <th style="width:100px" class="text-center">% To</th>
                  <th style="width:70px" class="text-center" title="Fail grade">Fail</th>
                  <th style="width:50px"></th>
                </tr>
              </thead>
              <tbody id="grades-policy-body">
                <?php foreach ($rows as $i => $row): ?>
                <tr class="grade-row" data-index="<?= (int) $i ?>">
                  <td>
                    <input type="hidden" name="rowscount[]" value="1">
                    <input type="hidden" name="gid[]" value="<?= (int) ($row->gid ?? 0) ?>">
                    <input type="hidden" name="gp_id[]" value="<?= (int) ($row->gp_id ?? 0) ?>">
                    <input type="text" name="name[]" class="form-control form-control-sm" placeholder="e.g. A+"
                           value="<?= esc($row->name ?? '') ?>" required>
                  </td>
                  <td>
                    <input type="text" name="detail[]" class="form-control form-control-sm" placeholder="Outstanding"
                           value="<?= esc($row->detail ?? '') ?>">
                  </td>
                  <td>
                    <input type="number" name="mark_from[]" class="form-control form-control-sm text-center mark-from"
                           min="0" max="100" value="<?= (int) ($row->mark_from ?? 0) ?>" readonly>
                  </td>
                  <td>
                    <input type="number" name="marks_to[]" class="form-control form-control-sm text-center mark-to"
                           min="0" max="100" value="<?= (int) ($row->mark_to ?? 0) ?>" required>
                  </td>
                  <td class="text-center align-middle">
                    <input type="radio" name="is_f" value="is_f_<?= (int) $i ?>"
                           <?= ((int) ($row->is_f ?? 0) === 1) ? 'checked' : '' ?>>
                  </td>
                  <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Remove row"
                            <?= count($rows) <= 1 ? 'disabled' : '' ?>>
                      <i class="fas fa-times"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <button type="button" class="btn btn-success btn-sm mb-3" id="btn-add-grade-row">
            <i class="fas fa-plus"></i> Add grade
          </button>

          <div class="form-group mb-0">
            <button type="submit" id="submitBtn" class="btn btn-primary">
              <i class="fas fa-save"></i> Save all
            </button>
            <a href="<?= base_url('admin/grades') ?>" class="btn btn-secondary">Cancel</a>
          </div>

          <?= form_close() ?>
        </div>
      </div>
    </div>
  </div>
</section>

<script type="text/javascript">
$(function () {
  var rowIndex = $('#grades-policy-body tr').length;

  function reindexRows() {
    $('#grades-policy-body tr').each(function (i) {
      $(this).attr('data-index', i);
      $(this).find('input[type=radio][name=is_f]').val('is_f_' + i);
    });
    rowIndex = $('#grades-policy-body tr').length;
    $('#grades-policy-body .btn-remove-row').prop('disabled', rowIndex <= 1);
    chainMarkFrom();
  }

  function chainMarkFrom() {
    $('#grades-policy-body tr').each(function (i) {
      var $from = $(this).find('.mark-from');
      if (i === 0) {
        if ($from.val() === '' || $from.val() === null) {
          $from.val(0);
        }
      } else {
        var prevTo = parseInt($('#grades-policy-body tr').eq(i - 1).find('.mark-to').val(), 10);
        if (!isNaN(prevTo)) {
          $from.val(Math.min(100, prevTo + 1));
        }
      }
    });
  }

  $(document).on('input', '.mark-to', function () {
    chainMarkFrom();
  });

  $('#btn-add-grade-row').on('click', function () {
    var i = rowIndex;
    var $row = $(
      '<tr class="grade-row" data-index="' + i + '">' +
        '<td><input type="hidden" name="rowscount[]" value="1">' +
        '<input type="hidden" name="gid[]" value="0">' +
        '<input type="hidden" name="gp_id[]" value="0">' +
        '<input type="text" name="name[]" class="form-control form-control-sm" placeholder="e.g. B" required></td>' +
        '<td><input type="text" name="detail[]" class="form-control form-control-sm" placeholder="Good"></td>' +
        '<td><input type="number" name="mark_from[]" class="form-control form-control-sm text-center mark-from" min="0" max="100" value="0" readonly></td>' +
        '<td><input type="number" name="marks_to[]" class="form-control form-control-sm text-center mark-to" min="0" max="100" value="" required></td>' +
        '<td class="text-center align-middle"><input type="radio" name="is_f" value="is_f_' + i + '"></td>' +
        '<td class="text-center align-middle"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="fas fa-times"></i></button></td>' +
      '</tr>'
    );
    $('#grades-policy-body').append($row);
    reindexRows();
  });

  $(document).on('click', '.btn-remove-row', function () {
    if ($('#grades-policy-body tr').length <= 1) {
      return;
    }
    $(this).closest('tr').remove();
    reindexRows();
  });

  $('#grades-setup-form').ajaxForm({
    beforeSubmit: function () {
      $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving…');
    },
    success: function (responseText) {
      $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save all');
      var json;
      try {
        json = typeof responseText === 'object' ? responseText : $.parseJSON(responseText);
      } catch (e) {
        toastr.error('Unexpected server response.');
        return false;
      }
      if (json.success) {
        toastr.success(json.msg);
        window.location.href = '<?= base_url('admin/grades') ?>';
      } else {
        toastr.error(json.msg || 'Save failed.');
      }
      return false;
    },
    error: function () {
      $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save all');
      toastr.error('Could not save. Please try again.');
    }
  });

  reindexRows();
});
</script>

<?= $this->endSection() ?>

