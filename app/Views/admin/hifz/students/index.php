<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Hifz Students',
    'icon' => 'fas fa-quran',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Hifz Program'],
        ['label' => 'Students', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="card sms-card card-primary card-outline">
    <div class="card-header">
      <h3 class="card-title">Enrolled Hifz Students</h3>
      <div class="card-tools">
        <a href="<?= base_url('admin/studentsbulk') ?>" class="btn btn-sm btn-outline-primary">
          <i class="fas fa-exchange-alt"></i> Class Change / Enroll
        </a>
      </div>
    </div>
    <div class="card-body">
      <?php if (empty($hifzSections)): ?>
        <div class="alert alert-warning">
          No Hifz sections for this session.
          <a href="<?= base_url('admin/hifz/sections') ?>">Create sections</a> first, then enroll students via Class Change.
        </div>
      <?php endif; ?>

      <div class="row mb-3">
        <div class="col-md-4">
          <label class="small text-muted mb-1">Filter by Hifz section</label>
          <select id="filter_hifz_sec" class="form-control form-control-sm">
            <option value="0">All sections</option>
            <?php foreach ($hifzSections as $sec): ?>
              <option value="<?= (int) $sec['hifz_sec_id'] ?>"><?= esc($sec['section_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <table id="hifz-students-table" class="table table-bordered table-hover table-sm" style="width:100%">
        <thead>
          <tr>
            <th>#</th>
            <th>Student</th>
            <th>Reg No</th>
            <th>Academic Class</th>
            <th>Hifz Section</th>
            <th>Sequence</th>
            <th>Sabaq L</th>
            <th>Mutalia L</th>
            <th>Manzil</th>
            <th>Line</th>
            <th>Juz</th>
            <th>Teacher</th>
            <th>Enrolled</th>
            <th style="width:110px">Actions</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</section>

<div class="modal fade" id="hifzEditModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Hifz Enrollment</h5>
        <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="edit_student_id">
        <p class="mb-2"><strong id="edit_student_name"></strong></p>
        <div class="form-group">
          <label>Hifz Section</label>
          <select id="edit_hifz_sec_id" class="form-control form-control-sm">
            <?php foreach ($hifzSections as $sec): ?>
              <option value="<?= (int) $sec['hifz_sec_id'] ?>"><?= esc($sec['section_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Memorization Sequence</label>
          <select id="edit_sequence" class="form-control form-control-sm">
            <?php foreach ($sequences as $code => $label): ?>
              <option value="<?= esc($code, 'attr') ?>"><?= esc($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="row">
          <div class="col-6 form-group">
            <label>Sabaq lines / day</label>
            <input type="number" id="edit_sabaq_lines" class="form-control form-control-sm" min="1" max="30">
          </div>
          <div class="col-6 form-group">
            <label>Mutalia lines / day</label>
            <input type="number" id="edit_mutalia_lines" class="form-control form-control-sm" min="1" max="30">
          </div>
        </div>
        <div class="form-group">
          <label>Manzil (paras per day)</label>
          <select id="edit_manzil_paras" class="form-control form-control-sm">
            <?php foreach ($manzilOptions ?? [1 => '1 Para / day'] as $n => $label): ?>
              <option value="<?= (int) $n ?>"><?= esc($label) ?></option>
            <?php endforeach; ?>
          </select>
          <small class="text-muted">Rotates through completed paras: Mon Para 1, Tue Para 2, … then repeats.</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary btn-sm" id="btnSaveHifzEdit">Save</button>
      </div>
    </div>
  </div>
</div>

<script>
$(function () {
  var table = $('#hifz-students-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('admin/hifz/students/data') ?>',
      type: 'POST',
      data: function (d) {
        d.hifz_sec_id = $('#filter_hifz_sec').val();
        d['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
      }
    },
    columns: [
      { data: 'sno' },
      { data: 'student_name' },
      { data: 'reg_no' },
      { data: 'academic_section' },
      { data: 'hifz_section_name' },
      { data: 'sequence_label' },
      { data: 'sabaq_lines' },
      { data: 'mutalia_lines' },
      { data: 'manzil_paras' },
      { data: 'cursor_line' },
      { data: 'current_juz' },
      { data: 'teacher_name' },
      { data: 'enrollment_date' },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          return '<button type="button" class="btn btn-sm btn-primary js-edit-hifz">Edit</button> '
            + '<button type="button" class="btn btn-sm btn-outline-danger js-withdraw-hifz" data-id="' + row.student_id + '">Withdraw</button>';
        }
      }
    ],
    order: [[1, 'asc']]
  });

  $('#filter_hifz_sec').on('change', function () {
    table.ajax.reload();
  });

  $('#hifz-students-table').on('click', '.js-edit-hifz', function () {
    var row = table.row($(this).closest('tr')).data();
    if (!row) return;
    $('#edit_student_id').val(row.student_id);
    $('#edit_student_name').text(row.student_name + (row.reg_no ? ' (' + row.reg_no + ')' : ''));
    $('#edit_hifz_sec_id').val(row.hifz_sec_id);
    $('#edit_sequence').val(row.memorization_sequence);
    $('#edit_sabaq_lines').val(row.sabaq_lines);
    $('#edit_mutalia_lines').val(row.mutalia_lines);
    $('#edit_manzil_paras').val(row.manzil_paras || 1);
    $('#hifzEditModal').modal('show');
  });

  $('#btnSaveHifzEdit').on('click', function () {
    var $btn = $(this).prop('disabled', true);
    $.post('<?= base_url('admin/hifz/students/save') ?>', {
      student_id: $('#edit_student_id').val(),
      hifz_sec_id: $('#edit_hifz_sec_id').val(),
      memorization_sequence: $('#edit_sequence').val(),
      sabaq_lines_per_day: $('#edit_sabaq_lines').val(),
      mutalia_lines_per_day: $('#edit_mutalia_lines').val(),
      manzil_paras_per_day: $('#edit_manzil_paras').val(),
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    }).done(function (res) {
      if (res.success) {
        toastr.success(res.msg || 'Saved');
        $('#hifzEditModal').modal('hide');
        table.ajax.reload(null, false);
      } else {
        toastr.error(res.msg || 'Save failed');
      }
    }).fail(function () {
      toastr.error('Request failed');
    }).always(function () {
      $btn.prop('disabled', false);
    });
  });

  $('#hifz-students-table').on('click', '.js-withdraw-hifz', function () {
    var sid = $(this).data('id');
    if (!confirm('Withdraw this student from the Hifz program?')) return;
    $.post('<?= base_url('admin/hifz/students/withdraw') ?>', {
      student_id: sid,
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    }).done(function (res) {
      if (res.success) {
        toastr.success(res.msg);
        table.ajax.reload(null, false);
      } else {
        toastr.error(res.msg || 'Failed');
      }
    }).fail(function () {
      toastr.error('Request failed');
    });
  });
});
</script>

<?= $this->endSection() ?>
