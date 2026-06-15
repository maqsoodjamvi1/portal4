<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<?php
  // Are we editing or adding?
  if (isset($info)) {
    // EDIT MODE
    $header        = 'Edit Academic Session';
    $id            = $info->session_id;
    $session_name  = $info->session_name;
    $start_date    = $info->start_date;
    $end_date      = $info->end_date;
    $hasPrevious   = true;        // start comes from existing record
  } else {
    // ADD MODE
    $header        = 'Add Academic Session';
    $id            = '';

    // Example: 2024-25
    $session_name  = date('Y') . '-' . (date('y') + 1);

    if (!empty($academic_session_info)) {
      // There is at least one previous session:
      // start = day after previous end, and it's LOCKED
      $date = new DateTime($academic_session_info->end_date);
      $date->modify('+1 day');
      $start_date  = $date->format('Y-m-d');
      $end_date    = '';              // user chooses
      $hasPrevious = true;            // lock start
    } else {
      // FIRST TIME: no academic_session rows
      // start = today (editable), end = today + 1 year - 1 day
      $start_date  = date('Y-m-d');
      $endObj      = new DateTime($start_date);
      $endObj->modify('+1 year')->modify('-1 day');
      $end_date    = $endObj->format('Y-m-d');
      $hasPrevious = false;           // allow editing start
    }
  }

  // Flag to control readonly in HTML
  $lockStartDate = $hasPrevious;  // true if we want start date locked
?>


<?= view('components/page_header', [
    'title' => $header ?? 'Academic Session',
    'icon' => 'fas fa-calendar',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Academic Session', 'url' => base_url('admin/academic_session')],
        ['label' => isset($info) ? 'Edit' : 'Add', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs">
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('admin/academic_session'); ?>">Academic Session</a>
          </li>
          <li class="nav-item">
            <?php if ($id === '') : ?>
              <a class="nav-link active" href="<?= base_url('/admin/academic_session/add'); ?>"><?= $header; ?></a>
            <?php else : ?>
              <a class="nav-link active" href="<?= base_url('/admin/academic_session/edit/id=' . $id); ?>"><?= $header; ?></a>
            <?php endif; ?>
          </li>
        </ul>
      </div>

      <div class="card-body">
        <!-- Live Summary -->
        <div class="row" id="dateSummaryRow">
          <div class="col-md-4 mb-3">
            <div class="border rounded p-3 h-100">
              <div class="text-muted small mb-1">Start Date</div>
              <div class="d-flex align-items-center">
                <span id="startDateText" class="fw-bold me-2">—</span>
                <span id="startDayBadge" class="badge text-bg-info">—</span>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <div class="border rounded p-3 h-100">
              <div class="text-muted small mb-1">End Date</div>
              <div class="d-flex align-items-center">
                <span id="endDateText" class="fw-bold me-2">—</span>
                <span id="endDayBadge" class="badge text-bg-info">—</span>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <div class="border rounded p-3 h-100 bg-light">
              <div class="text-muted small mb-1">Calculated Range</div>
              <div class="d-flex flex-wrap align-items-center">
                <span class="me-3">
                  <span class="text-muted">Days:</span>
                  <span id="totalDays" class="fw-bold">0</span>
                </span>
                <span>
                  <span class="text-muted">Weeks (inclusive):</span>
                  <span id="totalWeeks" class="fw-bold">0</span>
                </span>
              </div>
              <div id="rangeHint" class="small mt-1 text-muted">
                Weeks are calculated inclusive of both dates.
              </div>
            </div>
          </div>
        </div>

        <?php
          echo form_open(base_url('admin/academic_session/save'), 'role="form" id="academic-session-edit-form"');
          echo form_hidden('id', $id);
        ?>

        <div class="form-group">
          <label for="session_name">Session Name (e.g. 2019-20)</label>
          <input type="text"
                 class="form-control"
                 name="session_name"
                 id="session_name"
                 data-inputmask='"mask": "9999-99"' data-mask
                 value="<?= esc($session_name); ?>">
          <input type="hidden" id="originalsession_name" value="<?= esc($session_name); ?>">
        </div>

        <!-- Start Date (always TD markup) -->
      <div class="form-group">
  <label for="startdatepicker">Start Date</label>

  <div class="input-group date" id="sessionStartPicker" data-target-input="nearest">
    <input
      type="text"
      class="form-control datetimepicker-input"
      id="startdatepicker"
      name="start_date"
      data-bs-target="#sessionStartPicker"
      value="<?= esc($start_date); ?>"
      autocomplete="off"
      <?= $lockStartDate ? 'readonly tabindex="-1"' : '' ?>
    />
    <span class="input-group-text" data-bs-target="#sessionStartPicker" data-bs-toggle="datetimepicker"><i class="fa fa-calendar"></i></span>
  </div>

  <?php if ($lockStartDate): ?>
    <small class="form-text text-muted mb-0">
      This start date is set automatically to begin after your last academic session.
    </small>
  <?php else: ?>
    <small class="form-text text-muted mb-0">
      You can adjust the start date if needed (first academic session).
    </small>
  <?php endif; ?>
</div>

        <!-- End Date (always TD markup) -->
        <div class="form-group">
          <label for="enddatepicker">End Date</label>
         <div class="input-group date" id="reservationdate" data-target-input="nearest">
  <input type="text"
         class="form-control datetimepicker-input"
         id="enddatepicker"
         name="end_date"
         value="<?= esc($end_date); ?>"
         data-bs-target="#reservationdate"
         autocomplete="off"
         readonly />
  <span class="input-group-text" data-bs-target="#reservationdate" data-bs-toggle="datetimepicker"><i class="fa fa-calendar"></i></span>
</div>
          </div>
          <small id="endHelp" class="form-text text-muted">End date must not be earlier than start date.</small>
        </div>

        <div class="form-group d-flex flex-wrap gap-2">
          <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
          <button type="reset" class="btn btn-secondary">Reset</button>
          <button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
        </div>

        <?= form_close(); ?>
      </div>
    </div>
  </div>
</section>

<!-- Assets: Flatpickr datetimepicker compatibility is provided by the shared layout. -->
<script>
(function ($) {
  moment.locale('en-gb');

  function toYMD(m){ return m && m.isValid() ? m.format('YYYY-MM-DD') : ''; }

  function updateSummary(){
    const sVal = $('#startdatepicker').val();
    const eVal = $('#enddatepicker').val();
    const sd = moment(sVal, 'YYYY-MM-DD', true);
    const ed = moment(eVal, 'YYYY-MM-DD', true);

    $('#startDateText').text(sVal || '—');
    $('#endDateText').text(eVal || '—');
    $('#startDayBadge').text(sd.isValid() ? sd.format('dddd') : '—');
    $('#endDayBadge').text(ed.isValid() ? ed.format('dddd') : '—');

    let days = 0, weeks = 0, valid = false;
    if (sd.isValid() && ed.isValid()){
      days = ed.diff(sd, 'days') + 1; if (days < 0) days = 0;
      weeks = days > 0 ? Math.ceil(days/7) : 0;
      valid = ed.isSameOrAfter(sd, 'day');
    }
    $('#totalDays').text(days);
    $('#totalWeeks').text(weeks);
    $('#enddatepicker').toggleClass('is-invalid', !valid && eVal.length > 0);
    $('#submitBtn').prop('disabled', !valid);
  }

  // Init pickers
 $('#reservationdate1').datetimepicker({
  format: 'YYYY-MM-DD',
  icons: { time: 'far fa-clock' },
  useCurrent: false,
  ignoreReadonly: true
});

$('#reservationdate').datetimepicker({
  format: 'YYYY-MM-DD',
  icons: { time: 'far fa-clock' },
  useCurrent: false,
  ignoreReadonly: true
});

  // Start change → end = start + 1y - 1d, enforce minDate
  $('#reservationdate1').on('change.datetimepicker', function(e){
    const m = e.date || moment($('#startdatepicker').val(), 'YYYY-MM-DD', true);
    if (!m || !m.isValid()) return;
    const end = m.clone().add(1,'year').subtract(1,'day');
    $('#reservationdate').datetimepicker('minDate', m.clone().startOf('day'));
    $('#reservationdate').datetimepicker('date', end);
    $('#startdatepicker').val(toYMD(m));
    $('#enddatepicker').val(toYMD(end));
    updateSummary();
  });

  // End manual change
  $('#reservationdate').on('change.datetimepicker', function(e){
    const start = moment($('#startdatepicker').val(), 'YYYY-MM-DD', true);
    const end = e.date || moment($('#enddatepicker').val(), 'YYYY-MM-DD', true);
    if (start.isValid() && end.isValid() && end.isBefore(start,'day')){
      $('#reservationdate').datetimepicker('date', start.clone());
      $('#enddatepicker').val(start.format('YYYY-MM-DD'));
    } else {
      $('#enddatepicker').val(toYMD(end));
    }
    updateSummary();
  });

  // Hydrate initial values
  (function(){
    const s = $('#startdatepicker').val();
    const e = $('#enddatepicker').val();
    const sd = moment(s, 'YYYY-MM-DD', true);
    if (sd.isValid()){
      $('#reservationdate1').datetimepicker('date', sd);
      $('#reservationdate').datetimepicker('minDate', sd.clone().startOf('day'));
      const ed = moment(e, 'YYYY-MM-DD', true);
      if (ed.isValid()){
        $('#reservationdate').datetimepicker('date', ed);
      } else {
        const autoEnd = sd.clone().add(1,'year').subtract(1,'day');
        $('#reservationdate').datetimepicker('date', autoEnd);
        $('#enddatepicker').val(autoEnd.format('YYYY-MM-DD'));
      }
    }
    updateSummary();
  })();

  // Your other plugins
  $('[data-mask]').inputmask();
  $('#academic-session-edit-form').validate({
    rules: { session_name: { required:true } },
    messages: { session_name: { required:'Session is Required' } }
  });

  $('#academic-session-edit-form').ajaxForm({
    beforeSubmit: function(){
      if (!$('#academic-session-edit-form').valid()) return false;
      $('#submitBtn').text('Saving…').prop('disabled', true);
    },
    success: function(res){
      $('#submitBtn').text('Save').prop('disabled', false);
      const json = res;
      if (json.term_id === false) { window.location.href = '<?= base_url(); ?>admin/terms/add'; return; }
      if (json.success){
        toastr.success(json.msg || 'Saved successfully');
        <?php if ($id == ''): ?>
          location.href = '<?= base_url(); ?>admin/academic_session';
          location.reload();
        <?php else: ?>
          location.href = '<?= base_url(); ?>admin/academic_session/edit/id=<?= $id; ?>&after=edit';
        <?php endif; ?>
      } else {
        toastr.error(json.msg || 'Save failed. Please try again.');
      }
    },
    error: function(){
      $('#submitBtn').text('Save').prop('disabled', false);
      toastr.error('Network or server error. Please try again.');
    }
  });
})(jQuery);
</script>

<style>
  /* Make sure the calendar can appear above cards/modals/navbars */
  .bootstrap-datetimepicker-widget {
    z-index: 3000 !important;
  }
</style>
<script>
(function ($) {
  // Make Monday the first day of week and keep YYYY-MM-DD format
  if (typeof moment !== 'undefined') {
    moment.locale('en-gb'); // en-gb = Monday as first day
    moment.updateLocale('en-gb', { week: { dow: 1 } });
  }

  function toYMD(m){ return m && m.isValid() ? m.format('YYYY-MM-DD') : ''; }

  function updateSummary(){
    const sVal = $('#startdatepicker').val();
    const eVal = $('#enddatepicker').val();
    const sd = moment(sVal, 'YYYY-MM-DD', true);
    const ed = moment(eVal, 'YYYY-MM-DD', true);

    $('#startDateText').text(sVal || '—');
    $('#endDateText').text(eVal || '—');
    $('#startDayBadge').text(sd.isValid() ? sd.format('dddd') : '—');
    $('#endDayBadge').text(ed.isValid() ? ed.format('dddd') : '—');

    let days = 0, weeks = 0, valid = false;
    if (sd.isValid() && ed.isValid()){
      days = ed.diff(sd, 'days') + 1;
      if (days < 0) days = 0;
      weeks = days > 0 ? Math.ceil(days/7) : 0;
      valid = ed.isSameOrAfter(sd, 'day');
    }
    $('#totalDays').text(days);
    $('#totalWeeks').text(weeks);
    $('#enddatepicker').toggleClass('is-invalid', !valid && eVal.length > 0);
    $('#submitBtn').prop('disabled', !valid);
  }

  // ========== INIT DATE-ONLY PICKERS ==========
  if (!$.fn || !$.fn.datetimepicker) {
    console.error('Datetimepicker compatibility adapter not loaded (missing or wrong order).');
    return;
  }

  // Start picker (date only, Monday as first day via moment locale)
  $('#sessionStartPicker').datetimepicker({
    format: 'YYYY-MM-DD',
    useCurrent: false,
    ignoreReadonly: true
  });

  // End picker (date only)
  $('#reservationdate').datetimepicker({
    format: 'YYYY-MM-DD',
    useCurrent: false,
    ignoreReadonly: true
  });

  // ====== Start change → update start + adjust end ======
  $('#sessionStartPicker').on('change.datetimepicker', function(e){
    const m = e.date || moment($('#startdatepicker').val(), 'YYYY-MM-DD', true);
    if (!m || !m.isValid()) return;

    $('#startdatepicker').val(toYMD(m));

    // Keep end date at least >= start date
    const currentEnd = moment($('#enddatepicker').val(), 'YYYY-MM-DD', true);
    if (!currentEnd.isValid() || currentEnd.isBefore(m, 'day')) {
      const defaultEnd = m.clone().add(1,'year').subtract(1,'day');
      $('#reservationdate').datetimepicker('date', defaultEnd);
      $('#enddatepicker').val(toYMD(defaultEnd));
    }

    $('#reservationdate').datetimepicker('minDate', m.clone().startOf('day'));
    updateSummary();
  });

  // ====== End manual change ======
  $('#reservationdate').on('change.datetimepicker', function(e){
    const start = moment($('#startdatepicker').val(), 'YYYY-MM-DD', true);
    const end   = e.date || moment($('#enddatepicker').val(), 'YYYY-MM-DD', true);

    if (start.isValid() && end.isValid() && end.isBefore(start,'day')){
      $('#reservationdate').datetimepicker('date', start.clone());
      $('#enddatepicker').val(start.format('YYYY-MM-DD'));
    } else {
      $('#enddatepicker').val(toYMD(end));
    }
    updateSummary();
  });

  // ====== Force-open on input click (even if readonly) ======
  $('#startdatepicker').on('click focus', function (e) {
    e.preventDefault();
    $('#sessionStartPicker').datetimepicker('show');
  });
  $('#sessionStartPicker .input-group-text').on('click', function (e) {
    e.preventDefault();
    $('#sessionStartPicker').datetimepicker('show');
  });

  $('#enddatepicker').on('click focus', function (e) {
    e.preventDefault();
    $('#reservationdate').datetimepicker('show');
  });
  $('#reservationdate .input-group-text').on('click', function (e) {
    e.preventDefault();
    $('#reservationdate').datetimepicker('show');
  });

  // ====== Hydrate initial values ======
  (function(){
    const s = $('#startdatepicker').val();
    const e = $('#enddatepicker').val();
    const sd = moment(s, 'YYYY-MM-DD', true);

    if (sd.isValid()){
      $('#sessionStartPicker').datetimepicker('date', sd);
      $('#reservationdate').datetimepicker('minDate', sd.clone().startOf('day'));

      const ed = moment(e, 'YYYY-MM-DD', true);
      if (ed.isValid()){
        $('#reservationdate').datetimepicker('date', ed);
      } else {
        const autoEnd = sd.clone().add(1,'year').subtract(1,'day');
        $('#reservationdate').datetimepicker('date', autoEnd);
        $('#enddatepicker').val(autoEnd.format('YYYY-MM-DD'));
      }
    }
    updateSummary();
  })();

  // ====== Other plugins (unchanged) ======
  $('[data-mask]').inputmask();
  $('#academic-session-edit-form').validate({
    rules: { session_name: { required:true } },
    messages: { session_name: { required:'Session is Required' } }
  });

  $('#academic-session-edit-form').ajaxForm({
    beforeSubmit: function(){
      if (!$('#academic-session-edit-form').valid()) return false;
      $('#submitBtn').text('Saving…').prop('disabled', true);
    },
    success: function(res){
      $('#submitBtn').text('Save').prop('disabled', false);
      const json = res;
      if (json.term_id === false) {
        window.location.href = '<?= base_url(); ?>admin/terms/add';
        return;
      }
      if (json.success){
        toastr.success(json.msg || 'Saved successfully');
        <?php if ($id == ''): ?>
          location.href = '<?= base_url(); ?>admin/academic_session';
          location.reload();
        <?php else: ?>
          location.href = '<?= base_url(); ?>admin/academic_session/edit/id=<?= $id; ?>&after=edit';
        <?php endif; ?>
      } else {
        toastr.error(json.msg || 'Save failed. Please try again.');
      }
    },
    error: function(){
      $('#submitBtn').text('Save').prop('disabled', false);
      toastr.error('Network or server error. Please try again.');
    }
  });

})(jQuery);
</script>

<?= $this->endSection() ?>
