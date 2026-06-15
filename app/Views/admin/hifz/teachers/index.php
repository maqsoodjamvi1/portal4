<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Assign Hifz Teachers',
    'icon' => 'fas fa-quran',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Hifz Program'],
        ['label' => 'Teachers', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="card sms-card card-primary card-outline">
    <div class="card-header">
      <h3 class="card-title">One teacher per Hifz section</h3>
    </div>
    <div class="card-body p-0">
      <p class="px-3 pt-3 text-muted small mb-2">Assign the Hifz teacher responsible for daily recitation in each section.</p>
      <table class="table table-striped table-bordered mb-0" id="hifz-teachers-table">
        <thead>
          <tr>
            <th>Hifz Section</th>
            <th style="width:90px">Students</th>
            <th style="min-width:280px">Teacher</th>
            <th style="width:100px">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr><td colspan="4" class="text-center text-muted py-4">Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
$(function () {
  var teachers = <?= json_encode($teachers ?? []) ?>;

  function teacherOptions(selectedId) {
    var html = '<option value="">— No teacher —</option>';
    teachers.forEach(function (t) {
      var name = ((t.first_name || '') + ' ' + (t.last_name || '')).trim();
      var sel = parseInt(selectedId, 10) === parseInt(t.id, 10) ? ' selected' : '';
      html += '<option value="' + t.id + '"' + sel + '>' + name + '</option>';
    });
    return html;
  }

  function loadBoard() {
    $.post('<?= base_url('admin/hifz/teachers/data') ?>', {
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    }).done(function (res) {
      var rows = (res && res.data) ? res.data : [];
      var $tb = $('#hifz-teachers-table tbody').empty();
      if (!rows.length) {
        $tb.append('<tr><td colspan="4" class="text-center text-muted py-4">No active Hifz sections. Add sections first.</td></tr>');
        return;
      }
      rows.forEach(function (r) {
        var sid = r.hifz_sec_id;
        var $tr = $('<tr></tr>');
        $tr.append('<td class="align-middle"><strong>' + $('<div>').text(r.section_name).html() + '</strong></td>');
        $tr.append('<td class="align-middle text-center">' + (r.student_count || 0) + '</td>');
        $tr.append('<td class="align-middle"><select class="form-control form-control-sm js-teacher" data-sec="' + sid + '">' + teacherOptions(r.teacher_id) + '</select></td>');
        $tr.append('<td class="align-middle"><button type="button" class="btn btn-primary btn-sm js-save-teacher" data-sec="' + sid + '">Save</button></td>');
        $tb.append($tr);
      });
    }).fail(function () {
      toastr.error('Failed to load sections.');
    });
  }

  $('#hifz-teachers-table').on('click', '.js-save-teacher', function () {
    var $btn = $(this);
    var secId = $btn.data('sec');
    var teacherId = $btn.closest('tr').find('.js-teacher').val();
    $btn.prop('disabled', true);
    $.post('<?= base_url('admin/hifz/teachers/save') ?>', {
      hifz_sec_id: secId,
      teacher_id: teacherId || 0,
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    }).done(function (res) {
      if (res.success) {
        toastr.success(res.msg || 'Saved');
      } else {
        toastr.error(res.msg || 'Save failed');
      }
    }).fail(function () {
      toastr.error('Request failed');
    }).always(function () {
      $btn.prop('disabled', false);
    });
  });

  loadBoard();
});
</script>

<?= $this->endSection() ?>
