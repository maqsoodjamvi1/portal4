<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => $title ?? 'Hifz Sections',
    'icon' => 'fas fa-quran',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Hifz Program'],
        ['label' => 'Sections', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="card sms-card card-primary card-outline">
    <div class="card-header">
      <h3 class="card-title">Hifz Sections — Current Session</h3>
      <div class="card-tools">
        <button type="button" class="btn btn-primary btn-sm" id="btnAddRow">
          <i class="fas fa-plus"></i> Add Row
        </button>
        <button type="button" class="btn btn-success btn-sm ms-1" id="btnSaveAll">
          <i class="fas fa-save"></i> Save All
        </button>
      </div>
    </div>
    <div class="card-body">
      <p class="text-muted small mb-3">
        Add or edit all Hifz sections on this page. Assign one teacher per section, then click <strong>Save All</strong>.
        Removing a row only removes it from this list until you save; existing sections in the database are not deleted automatically.
      </p>
      <div class="table-responsive">
        <table class="table table-bordered table-sm" id="hifz-sections-grid">
          <thead class="table-light">
            <tr>
              <th style="width:40px">#</th>
              <th>Section Name</th>
              <th style="width:100px">Order</th>
              <th style="min-width:220px">Teacher</th>
              <th style="width:90px">Students</th>
              <th style="width:80px">Active</th>
              <th style="width:50px"></th>
            </tr>
          </thead>
          <tbody>
            <tr><td colspan="7" class="text-center text-muted py-4">Loading…</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<script>
$(function () {
  var teachers = <?= json_encode($teachers ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  var dataUrl = '<?= base_url('admin/hifz/sections/data') ?>';
  var saveUrl = '<?= base_url('admin/hifz/sections/bulk-save') ?>';
  var csrfName = '<?= csrf_token() ?>';
  var csrfHash = '<?= csrf_hash() ?>';

  function escHtml(s) {
    return $('<div>').text(s == null ? '' : String(s)).html();
  }

  function teacherOptions(selectedId) {
    var html = '<option value="">— No teacher —</option>';
    var sel = parseInt(selectedId, 10) || 0;
    teachers.forEach(function (t) {
      var name = ((t.first_name || '') + ' ' + (t.last_name || '')).trim();
      var id = parseInt(t.id, 10);
      html += '<option value="' + id + '"' + (id === sel ? ' selected' : '') + '>' + escHtml(name) + '</option>';
    });
    return html;
  }

  function renumberRows() {
    $('#hifz-sections-grid tbody tr.hifz-sec-row').each(function (i) {
      $(this).find('.js-sno').text(i + 1);
    });
  }

  function nextSortOrder() {
    var max = 0;
    $('#hifz-sections-grid tbody tr.hifz-sec-row').each(function () {
      var v = parseInt($(this).find('.js-sort-order').val(), 10) || 0;
      if (v > max) max = v;
    });
    return max + 1;
  }

  function addRow(row) {
    row = row || {};
    var secId = parseInt(row.hifz_sec_id, 10) || 0;
    var name = row.section_name || '';
    var sort = row.sort_order != null ? row.sort_order : nextSortOrder();
    var teacherId = parseInt(row.teacher_id, 10) || 0;
    var status = parseInt(row.status, 10) !== 0 ? 1 : 0;
    var students = parseInt(row.student_count, 10) || 0;
    var isNew = secId <= 0;

    var $tr = $('<tr class="hifz-sec-row"></tr>');
    $tr.append('<td class="align-middle text-center js-sno"></td>');
    $tr.append(
      '<td class="align-middle">' +
        '<input type="hidden" class="js-sec-id" value="' + secId + '">' +
        '<input type="text" class="form-control form-control-sm js-section-name" maxlength="100" value="' + escHtml(name) + '" placeholder="e.g. Green Section">' +
      '</td>'
    );
    $tr.append(
      '<td class="align-middle">' +
        '<input type="number" class="form-control form-control-sm js-sort-order" min="0" max="9999" value="' + sort + '">' +
      '</td>'
    );
    $tr.append(
      '<td class="align-middle">' +
        '<select class="form-control form-control-sm js-teacher">' + teacherOptions(teacherId) + '</select>' +
      '</td>'
    );
    $tr.append(
      '<td class="align-middle text-center text-muted small">' +
        (isNew ? '—' : '<span class="js-student-count">' + students + '</span>') +
      '</td>'
    );
    $tr.append(
      '<td class="align-middle text-center">' +
        '<input type="checkbox" class="js-status" value="1"' + (status ? ' checked' : '') + '>' +
      '</td>'
    );
    $tr.append(
      '<td class="align-middle text-center">' +
        '<button type="button" class="btn btn-link btn-sm text-danger p-0 js-remove-row" title="Remove row">&times;</button>' +
      '</td>'
    );

    $('#hifz-sections-grid tbody').append($tr);
    renumberRows();
  }

  function loadGrid() {
    var $tb = $('#hifz-sections-grid tbody').html(
      '<tr><td colspan="7" class="text-center text-muted py-4">Loading…</td></tr>'
    );

    $.post(dataUrl, { [csrfName]: csrfHash })
      .done(function (res) {
        $tb.empty();
        var rows = (res && res.rows) ? res.rows : [];
        if (!rows.length) {
          addRow();
          return;
        }
        rows.forEach(function (r) { addRow(r); });
      })
      .fail(function (xhr) {
        var msg = (xhr.responseJSON && xhr.responseJSON.msg) ? xhr.responseJSON.msg : 'Failed to load sections.';
        $tb.html('<tr><td colspan="7" class="text-danger text-center py-4">' + escHtml(msg) + '</td></tr>');
        toastr.error(msg);
      });
  }

  function collectPayload() {
    var payload = {
      [csrfName]: csrfHash,
      'hifz_sec_id[]': [],
      'section_name[]': [],
      'sort_order[]': [],
      'status[]': [],
      'teacher_id[]': []
    };

    $('#hifz-sections-grid tbody tr.hifz-sec-row').each(function () {
      payload['hifz_sec_id[]'].push($(this).find('.js-sec-id').val() || '0');
      payload['section_name[]'].push($(this).find('.js-section-name').val() || '');
      payload['sort_order[]'].push($(this).find('.js-sort-order').val() || '0');
      payload['status[]'].push($(this).find('.js-status').is(':checked') ? '1' : '0');
      payload['teacher_id[]'].push($(this).find('.js-teacher').val() || '0');
    });

    return payload;
  }

  function renderFromRows(rows) {
    var $tb = $('#hifz-sections-grid tbody').empty();
    if (!rows.length) {
      addRow();
      return;
    }
    rows.forEach(function (r) { addRow(r); });
  }

  $('#btnAddRow').on('click', function () {
    addRow();
  });

  $('#hifz-sections-grid').on('click', '.js-remove-row', function () {
    var $rows = $('#hifz-sections-grid tbody tr.hifz-sec-row');
    if ($rows.length <= 1) {
      var $tr = $rows.first();
      $tr.find('.js-sec-id').val('0');
      $tr.find('.js-section-name').val('');
      $tr.find('.js-sort-order').val('0');
      $tr.find('.js-teacher').val('');
      $tr.find('.js-status').prop('checked', true);
      $tr.find('.js-student-count').parent().html('—');
      return;
    }
    $(this).closest('tr').remove();
    renumberRows();
  });

  $('#btnSaveAll').on('click', function () {
    var $btn = $(this).prop('disabled', true);
    $.post(saveUrl, collectPayload())
      .done(function (res) {
        if (res.success) {
          toastr.success(res.msg || 'Saved');
          if (res.rows) {
            renderFromRows(res.rows);
          } else {
            loadGrid();
          }
        } else {
          toastr.error(res.msg || 'Save failed');
        }
      })
      .fail(function (xhr) {
        var msg = (xhr.responseJSON && xhr.responseJSON.msg) ? xhr.responseJSON.msg : 'Request failed';
        toastr.error(msg);
      })
      .always(function () {
        $btn.prop('disabled', false);
      });
  });

  loadGrid();
});
</script>

<?= $this->endSection() ?>
