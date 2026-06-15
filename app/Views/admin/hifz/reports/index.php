<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Hifz Progress Reports',
    'icon' => 'fas fa-chart-line',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Hifz Program'],
        ['label' => 'Reports', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="card sms-card card-primary card-outline">
    <div class="card-header p-2">
      <ul class="nav nav-pills" id="hifz-report-tabs">
        <li class="nav-item"><a class="nav-link active" href="#tab-section" data-bs-toggle="tab">Section Summary</a></li>
        <li class="nav-item"><a class="nav-link" href="#tab-student" data-bs-toggle="tab">Student Timeline</a></li>
      </ul>
    </div>
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-md-3">
          <label class="small text-muted mb-1">From</label>
          <input type="date" id="report-date-from" class="form-control form-control-sm" value="<?= esc($defaultFrom ?? date('Y-m-01')) ?>">
        </div>
        <div class="col-md-3">
          <label class="small text-muted mb-1">To</label>
          <input type="date" id="report-date-to" class="form-control form-control-sm" value="<?= esc($defaultTo ?? date('Y-m-d')) ?>">
        </div>
      </div>

      <div class="tab-content">
        <div class="tab-pane active" id="tab-section">
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="small text-muted mb-1">Hifz Section</label>
              <select id="section-report-sec" class="form-control form-control-sm">
                <option value="">— Select section —</option>
                <?php foreach ($sections ?? [] as $sec): ?>
                  <option value="<?= (int) $sec['hifz_sec_id'] ?>"><?= esc($sec['section_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <button type="button" class="btn btn-primary btn-sm" id="btn-section-report">
                <i class="fas fa-search"></i> Load Report
              </button>
              <button type="button" class="btn btn-outline-secondary btn-sm ms-1" id="btn-section-export" title="Export CSV">
                <i class="fas fa-file-csv"></i> Export
              </button>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-sm table-hover" id="section-report-table">
              <thead class="table-light">
                <tr>
                  <th>Student</th>
                  <th>Reg No</th>
                  <th>Current Para</th>
                  <th>Mutalia L/day</th>
                  <th>Sabaq L/day</th>
                  <th>Days logged</th>
                  <th>Last entry</th>
                  <th>Last Sabaq</th>
                </tr>
              </thead>
              <tbody>
                <tr><td colspan="9" class="text-center text-muted py-4">Select a section and load report.</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="tab-pane" id="tab-student">
          <div class="row mb-3">
            <div class="col-md-5">
              <label class="small text-muted mb-1">Student</label>
              <select id="student-report-id" class="form-control form-control-sm">
                <option value="">— Select student —</option>
                <?php foreach ($students ?? [] as $st): ?>
                  <option value="<?= (int) $st['student_id'] ?>">
                    <?= esc(trim(($st['first_name'] ?? '') . ' ' . ($st['last_name'] ?? ''))) ?>
                    (<?= esc($st['reg_no'] ?? '') ?><?= ! empty($st['section_name']) ? ' · ' . esc($st['section_name']) : '' ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <button type="button" class="btn btn-primary btn-sm" id="btn-student-report">
                <i class="fas fa-search"></i> Load Timeline
              </button>
            </div>
          </div>

          <div id="student-summary-card" class="d-none mb-3">
            <div class="card card-outline card-info">
              <div class="card-body py-3" id="student-summary-body"></div>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered table-sm" id="student-log-table">
              <thead class="table-light">
                <tr>
                  <th>Date</th>
                  <th>Sabaq</th>
                  <th>Sabqi</th>
                  <th>Manzil</th>
                  <th>Mutalia</th>
                  <th>Qualities</th>
                </tr>
              </thead>
              <tbody>
                <tr><td colspan="6" class="text-center text-muted py-4">Select a student and load timeline.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
$(function () {
  var csrfName = '<?= csrf_token() ?>';
  var csrfHash = '<?= csrf_hash() ?>';
  var sectionUrl = '<?= base_url('admin/hifz/reports/section-data') ?>';
  var sectionExportUrl = '<?= base_url('admin/hifz/reports/export-section') ?>';
  var studentUrl = '<?= base_url('admin/hifz/reports/student-data') ?>';

  $('#btn-section-export').on('click', function () {
    var secId = $('#section-report-sec').val();
    if (!secId) {
      toastr.warning('Select a Hifz section.');
      return;
    }
    var $form = $('<form method="post"></form>').attr('action', sectionExportUrl);
    $form.append($('<input type="hidden" name="' + csrfName + '">').val(csrfHash));
    $form.append($('<input type="hidden" name="hifz_sec_id">').val(secId));
    $form.append($('<input type="hidden" name="date_from">').val($('#report-date-from').val()));
    $form.append($('<input type="hidden" name="date_to">').val($('#report-date-to').val()));
    $('body').append($form);
    $form.trigger('submit');
    $form.remove();
  });

  function esc(s) {
    return $('<div>').text(s || '').html();
  }

  function dateParams() {
    return {
      date_from: $('#report-date-from').val(),
      date_to: $('#report-date-to').val(),
      [csrfName]: csrfHash
    };
  }

  $('#btn-section-report').on('click', function () {
    var secId = $('#section-report-sec').val();
    if (!secId) {
      toastr.warning('Select a Hifz section.');
      return;
    }
    var $tb = $('#section-report-table tbody').html('<tr><td colspan="9" class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></td></tr>');
    $.post(sectionUrl, $.extend({ hifz_sec_id: secId }, dateParams()))
      .done(function (res) {
        $tb.empty();
        if (!res || !res.success) {
          toastr.error((res && res.msg) ? res.msg : 'Failed.');
          $tb.append('<tr><td colspan="9" class="text-center text-danger py-3">Error loading report.</td></tr>');
          return;
        }
        var rows = res.data || [];
        if (!rows.length) {
          $tb.append('<tr><td colspan="9" class="text-center text-muted py-4">No students in this section.</td></tr>');
          return;
        }
        rows.forEach(function (r) {
          $tb.append('<tr>' +
            '<td>' + esc(r.student_name) + '</td>' +
            '<td>' + esc(r.reg_no) + '</td>' +
            '<td class="text-center">' + (r.current_juz || 0) + '</td>' +
            '<td class="text-center">' + (r.mutalia_lines || 0) + '</td>' +
            '<td class="text-center">' + (r.sabaq_lines || 0) + '</td>' +
            '<td class="text-center">' + (r.days_logged || 0) + '</td>' +
            '<td>' + esc(r.last_date || '—') + '</td>' +
            '<td>' + esc(r.last_quality || '—') + '</td>' +
            '</tr>');
        });
      })
      .fail(function () {
        toastr.error('Request failed.');
      });
  });

  $('#btn-student-report').on('click', function () {
    var sid = $('#student-report-id').val();
    if (!sid) {
      toastr.warning('Select a student.');
      return;
    }
    var $tb = $('#student-log-table tbody').html('<tr><td colspan="6" class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></td></tr>');
    $('#student-summary-card').addClass('d-none');
    $.post(studentUrl, $.extend({ student_id: sid }, dateParams()))
      .done(function (res) {
        if (!res || !res.success) {
          toastr.error((res && res.msg) ? res.msg : 'Failed.');
          $tb.html('<tr><td colspan="6" class="text-center text-danger py-3">Error.</td></tr>');
          return;
        }
        var s = res.summary || {};
        $('#student-summary-body').html(
          '<div class="row">' +
          '<div class="col-md-4"><strong>' + esc(s.student_name) + '</strong><br><span class="text-muted small">' + esc(s.reg_no) + '</span></div>' +
          '<div class="col-md-4"><span class="text-muted small">Section</span><br>' + esc(s.section_name) + '<br><span class="text-muted small">Teacher:</span> ' + esc(s.teacher_name) + '</div>' +
          '<div class="col-md-4"><span class="text-muted small">Progress</span><br>' + esc(s.current_para_label || ('Para ' + (s.current_juz || 0))) +
          '<br><span class="text-muted small">' + (s.days_logged || 0) + ' days with entries in range</span></div>' +
          '</div>'
        );
        $('#student-summary-card').removeClass('d-none');

        $tb.empty();
        var log = s.log || [];
        if (!log.length) {
          $tb.append('<tr><td colspan="6" class="text-center text-muted py-4">No recitation entries in this date range.</td></tr>');
          return;
        }
        log.forEach(function (row) {
          var manzilMeta = (row.manzil_listener || '—') + (row.manzil_quality && row.manzil_quality !== '—'
            ? ' · ' + row.manzil_quality : '');
          var qual = ['S:' + (row.sabaq_quality || '—'), 'Sq:' + (row.sabqi_quality || '—'),
            'M:' + manzilMeta, 'Mu:' + (row.mutalia_label || '—')].join(' · ');
          $tb.append('<tr>' +
            '<td class="text-nowrap">' + esc(row.date) + '</td>' +
            '<td class="small">' + esc(row.sabaq_label) + '</td>' +
            '<td class="small">' + esc(row.sabqi_label) + '</td>' +
            '<td class="small">' + esc(row.manzil_label) + '</td>' +
            '<td class="small">' + esc(row.mutalia_label) + '</td>' +
            '<td class="small">' + esc(qual) + '</td>' +
            '</tr>');
        });
      })
      .fail(function () { toastr.error('Request failed.'); });
  });
});
</script>

<?= $this->endSection() ?>
