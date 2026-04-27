<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  // Expected variables:
  // $mode, $session_id, $session_name, $start_date, $end_date
  // $weekTypes, $allSessions, $existingTerms, $termsCount
  // $isFirstSession, $isEditing
?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2 align-items-center">
      <div class="col-sm-8">
        <h1 class="mb-0">Academic Calendar Builder</h1>
        <p class="text-muted mb-0 small">
          <?= $isFirstSession ? 'Create your first academic session' : 'Create a new session or edit existing one' ?>
        </p>
      </div>
      <div class="col-sm-4">
        <ol class="breadcrumb float-sm-right bg-transparent p-0 m-0">
          <li class="breadcrumb-item"><a href="<?= base_url('/admin'); ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Academic Calendar</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    
    <?php if (!empty($allSessions)): ?>
    <!-- Previous Sessions Section -->
    <div class="card card-default card-outline shadow-sm mb-3">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fa fa-history"></i> Previous Academic Sessions
        </h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fa fa-minus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
               <tr>
                <th>Session Name</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Terms Count</th>
                <th>Action</th>
               </tr>
            </thead>
            <tbody>
              <?php foreach ($allSessions as $sess): ?>
                <?php
                  // Count terms for this session
                 $termsCountInSess = isset($sessionTermCounts[$sess->session_id]) ? $sessionTermCounts[$sess->session_id] : '—';
                ?>
                <tr>
                  <td><?= esc($sess->session_name) ?></td>
                  <td><?= esc($sess->start_date) ?></td>
                  <td><?= esc($sess->end_date) ?></td>
                  <td><?= $termsCountInSess ?></td>
                  <td>
                    <a href="<?= base_url('admin/academic-calendar/builder?session_id=' . $sess->session_id) ?>" 
                       class="btn btn-xs btn-info">
                      <i class="fa fa-edit"></i> Edit
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <?php endif; ?>
    
    <?= form_open(base_url('admin/academic-calendar/save'), ['id' => 'calendarBuilderForm']) ?>
    <?= form_hidden('session_id', (string) ($session_id ?? '')) ?>


<!-- Temporary debug section - remove after testing -->
<?php if ($isEditing): ?>
<div class="alert alert-info">
    <strong>Debug Info:</strong><br>
    IS_EDITING: <?= var_export($isEditing, true) ?><br>
    Existing Terms Count: <?= count($existingTerms) ?><br>
    <?php if (!empty($existingTerms)): ?>
    <pre><?= print_r($existingTerms, true) ?></pre>
    <?php endif; ?>
</div>
<?php endif; ?>


    <!-- ========= STEP 1: ACADEMIC SESSION ========= -->
    <div class="card card-primary card-outline shadow-sm mb-3">
      <div class="card-header">
        <h3 class="card-title">
          Step 1: Academic Session
          <?php if ($isEditing): ?>
            <span class="badge badge-info ml-2">Editing: <?= esc($session_name) ?></span>
          <?php endif; ?>
        </h3>
      </div>
      <div class="card-body">
        <div class="row align-items-end">
          <!-- Session Name -->
          <div class="col-md-4">
            <div class="form-group mb-2">
              <label for="session_name">Session Name</label>
              <input type="text"
                     class="form-control"
                     id="session_name"
                     name="session_name"
                     data-inputmask='"mask": "9999-99"' 
                     data-mask
                     value="<?= esc($session_name); ?>"
                     <?= $isEditing ? 'disabled' : '' ?>>
              <?php if ($isEditing): ?>
                <input type="hidden" name="session_name" value="<?= esc($session_name); ?>">
                <small class="text-muted">Session name cannot be changed after creation</small>
              <?php endif; ?>
            </div>
          </div>

          <!-- Session Start -->
          <div class="col-md-4">
            <div class="form-group mb-2">
              <label class="d-block">&nbsp;</label>
              <div class="input-group date" id="sessionStartPicker" data-target-input="nearest">
                <input type="text"
                       class="form-control datetimepicker-input"
                       id="startdatepicker"
                       name="start_date"
                       data-target="#sessionStartPicker"
                       autocomplete="off"
                       placeholder="Start Date (Mon)"
                       value="<?= esc($start_date); ?>"
                       <?= $isEditing ? 'disabled' : '' ?>>
                <div class="input-group-append" data-target="#sessionStartPicker" data-toggle="datetimepicker">
                  <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
              </div>
              <?php if ($isEditing): ?>
                <input type="hidden" name="start_date" value="<?= esc($start_date); ?>">
              <?php endif; ?>
            </div>
          </div>

          <!-- Session End -->
          <div class="col-md-4">
            <div class="form-group mb-2">
              <label class="d-block">&nbsp;</label>
              <div class="input-group date" id="sessionEndPicker" data-target-input="nearest">
                <input type="text"
                       class="form-control datetimepicker-input"
                       id="enddatepicker"
                       name="end_date"
                       data-target="#sessionEndPicker"
                       autocomplete="off"
                       placeholder="End Date (Sun)"
                       value="<?= esc($end_date); ?>"
                       <?= $isEditing ? 'disabled' : '' ?>>
                <div class="input-group-append" data-target="#sessionEndPicker" data-toggle="datetimepicker">
                  <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
              </div>
              <?php if ($isEditing): ?>
                <input type="hidden" name="end_date" value="<?= esc($end_date); ?>">
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Summary -->
        <div class="row mt-2">
          <div class="col-md-6 mb-2">
            <div class="border rounded p-2">
              <div class="small text-muted">Total Days (inclusive)</div>
              <div><span id="sessDays" class="font-weight-bold">0</span></div>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="border rounded p-2">
              <div class="small text-muted">Total Full Weeks (Mon–Sun)</div>
              <div><span id="sessWeeks" class="font-weight-bold">0</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ========= STEP 2: TERMS ========= -->
    <div class="card card-outline card-info mb-3">
      <div class="card-header">
        <h3 class="card-title mb-0">Step 2: Define Terms</h3>
        <?php if (!$isFirstSession && !$isEditing): ?>
          <div class="card-tools">
            <span class="badge badge-warning">
              <i class="fa fa-info-circle"></i> Number of terms is fixed based on previous session
            </span>
          </div>
        <?php endif; ?>
      </div>
      <div class="card-body">
        
        <?php if (!$isFirstSession && !$isEditing): ?>
        <div class="alert alert-info mb-3">
          <i class="fa fa-info-circle"></i>
          This is not the first session. The number of terms (<?= $termsCount ?>) is fixed based on the previous session structure.
          You can update term names and dates, but cannot add or remove terms.
        </div>
        <?php endif; ?>
        
        <!-- Terms Count Input - Readonly for non-first sessions -->
        <div class="form-row align-items-end mb-3">
          <div class="col-md-3">
            <label for="termsCount" class="mb-1">Number of Terms</label>
            <input type="number"
                   id="termsCount"
                   class="form-control form-control-sm"
                   min="1"
                   max="8"
                   value="<?= $termsCount ?>"
                   <?= (!$isFirstSession && !$isEditing) ? 'readonly' : '' ?>>
          </div>
          <div class="col-md-6">
            <small class="text-muted d-block mb-1">
              <?= (!$isFirstSession && !$isEditing) ? 'Number of terms is fixed. Edit term details below.' : 'Define how many terms you want in this academic session.' ?>
            </small>
          </div>
          <div class="col-md-3">
            <button type="button"
                    id="btnBuildTimeline"
                    class="btn btn-sm btn-primary btn-block">
              <i class="fa fa-sitemap"></i> Build Term Timeline
            </button>
          </div>
        </div>
<!-- Cut Points Section for Editing Term Boundaries -->
<div id="cutPointsSection" style="display:none;" class="mb-3">
    <div class="cut-points-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0"><i class="fa fa-sliders-h"></i> Term Boundaries</h6>
            <small class="text-muted">Adjust cut dates to modify term boundaries</small>
        </div>
        
        <div class="cut-points-timeline" id="cutPointsTimeline">
            <!-- Session Start Point -->
            <div class="cut-point">
                <div class="cut-point-icon session-start-icon">
                    <i class="fa fa-play-circle"></i>
                </div>
                <div class="cut-point-label">SESSION START</div>
                <div class="cut-point-date" id="timelineSessionStartDate">—</div>
                <div class="cut-point-day" id="timelineSessionStartDay">—</div>
            </div>
            
            <div class="timeline-connector"></div>
            
            <!-- Cut Dates Container -->
            <div id="cutDatesContainer" class="d-flex" style="flex: 3;">
                <!-- JS will inject cut date points here -->
            </div>
            
            <div class="timeline-connector"></div>
            
            <!-- Session End Point -->
            <div class="cut-point">
                <div class="cut-point-icon session-end-icon">
                    <i class="fa fa-stop-circle"></i>
                </div>
                <div class="cut-point-label">SESSION END</div>
                <div class="cut-point-date" id="timelineSessionEndDate">—</div>
                <div class="cut-point-day" id="timelineSessionEndDay">—</div>
            </div>
        </div>
    </div>
</div>
        <!-- Term Ranges Table -->
    <!-- Term Ranges Table -->
<div id="timelineWrapper" style="<?= (!$isFirstSession || $isEditing) ? 'display:block;' : 'display:none;' ?>">
    <div class="mb-2">
        <strong>Term Details</strong>
        <div class="small text-muted">
            <?php if (!$isFirstSession && !$isEditing): ?>
                Terms from previous session have been loaded. Term names and dates are read-only.
            <?php elseif ($isEditing): ?>
                Term dates are read-only. To modify term dates, edit the cut points above and click "Build Term Timeline" again.
            <?php else: ?>
                Fill in term names and short names. Dates will auto-populate when you build the timeline.
            <?php endif; ?>
        </div>
    </div>
    
    <div class="table-responsive mt-2">
        <table class="table table-sm table-bordered mb-0" id="termsSummaryTable">
            <thead class="thead-light">
                 <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:25%;">Term Name</th>
                    <th style="width:15%;">Short Name</th>
                    <th style="width:20%;">Start Date</th>
                    <th style="width:20%;">End Date</th>
                    <th style="width:15%;">Days / Weeks</th>
                 </tr>
            </thead>
            <tbody>
                <?php if (!empty($existingTerms)): ?>
                    <?php foreach ($existingTerms as $idx => $term): ?>
                        <?php
                            // Format dates to dd/mm/yyyy for display
                            $startDateFormatted = !empty($term->start_date) ? date('d/m/Y', strtotime($term->start_date)) : '';
                            $endDateFormatted = !empty($term->end_date) ? date('d/m/Y', strtotime($term->end_date)) : '';
                            
                            // Calculate days and weeks
                            $daysText = '—';
                            $weeksText = '—';
                            if (!empty($term->start_date) && !empty($term->end_date)) {
                                $startDt = new DateTime($term->start_date);
                                $endDt = new DateTime($term->end_date);
                                $days = $endDt->diff($startDt)->days + 1;
                                $weeks = floor($days / 7);
                                $daysText = $days;
                                $weeksText = $weeks;
                            }
                            
                            // Make all fields readonly for existing terms (non-editing mode)
                            $readonlyAttr = 'readonly style="background-color:#f5f5f5;"';
                        ?>
                        <tr data-term-index="<?= $idx + 1 ?>">
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td>
                                <input type="text" 
                                       name="term_name[<?= $idx + 1 ?>]" 
                                       class="form-control form-control-sm term-name-input" 
                                       value="<?= esc($term->name ?? '') ?>"
                                       placeholder="Term <?= $idx + 1 ?>"
                                       <?= $readonlyAttr ?>>
                                <input type="hidden" name="term_id[<?= $idx + 1 ?>]" value="<?= $term->term_id ?? '' ?>">
                            </td>
                            <td>
                                <input type="text" 
                                       name="term_short[<?= $idx + 1 ?>]" 
                                       class="form-control form-control-sm term-short-input" 
                                       value="<?= esc($term->short_name ?? '') ?>"
                                       placeholder="T<?= $idx + 1 ?>"
                                       <?= $readonlyAttr ?>>
                            </td>
                            <td>
                                <input type="text" 
                                       name="term_start[<?= $idx + 1 ?>]" 
                                       class="form-control form-control-sm term-start-field datepicker-display" 
                                       value="<?= $startDateFormatted ?>"
                                       data-original-date="<?= esc($term->start_date ?? '') ?>"
                                       <?= $readonlyAttr ?>>
                            </td>
                            <td>
                                <input type="text" 
                                       name="term_end[<?= $idx + 1 ?>]" 
                                       class="form-control form-control-sm term-end-field datepicker-display" 
                                       value="<?= $endDateFormatted ?>"
                                       data-original-date="<?= esc($term->end_date ?? '') ?>"
                                       <?= $readonlyAttr ?>>
                            </td>
                            <td class="text-center">
                                <span class="term-days-weeks" data-term-index="<?= $idx + 1 ?>"><?= $daysText ?> days / <?= $weeksText ?> weeks</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- JS will inject term rows here for first session -->
                    <tr class="js-placeholder">
                        <td colspan="6" class="text-center text-muted">
                            Click "Build Term Timeline" to create terms
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
      </div>
    </div>

    <!-- ========= STEP 3: WEEKS PREVIEW ========= -->
    <div class="card card-warning card-outline shadow-sm mb-3">
      <div class="card-header">
        <h3 class="card-title mb-0">Step 3: Preview Weeks (Mon–Sun) & Week Types</h3>
      </div>
      <div class="card-body">
        <p class="small text-muted">
          After you define terms, click below to preview how weeks (Mon–Sun) fall inside each term.
          Each row shows the generated <b>Week Name</b> and lets you choose a <b>Week Type</b>.
        </p>
        <button type="button" class="btn btn-xs btn-outline-primary mb-2" id="btnPreviewAllWeeks">
          Preview Weeks for All Terms
        </button>

        <div id="weeksPreviewAll">
          <!-- JS will inject week tables here -->
        </div>
      </div>
    </div>

    <!-- ========= SAVE ========= -->
    <div class="card shadow-sm">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div class="small text-muted">
          Saving will:
          <ul class="mb-0 pl-3">
            <li>Update or create the academic session</li>
            <li>Save term definitions and generate weeks</li>
          </ul>
        </div>
        <div>
          <button type="submit" class="btn btn-success" id="saveCalendarBtn">
            Save Academic Calendar
          </button>
        </div>
      </div>
    </div>

    <?= form_close() ?>
  </div>
</section>

<script>
(function($){
  if (typeof moment === 'undefined' || !$.fn.datetimepicker) {
    console.error('moment or datetimepicker not loaded.');
    return;
  }

  moment.locale('en-gb');
  moment.updateLocale('en-gb', { week: { dow: 1 } });

  var CURRENT_TERMS_COUNT = <?= $termsCount ?>;
  var IS_FIRST_SESSION = <?= $isFirstSession ? 'true' : 'false' ?>;
  var IS_EDITING = <?= $isEditing ? 'true' : 'false' ?>;
  var SHOW_CUT_POINTS = true;
  
 // Replace this section:
<?php if ($isEditing && !empty($existingTerms)): ?>
var EXISTING_TERMS = <?= json_encode($existingTerms) ?>;
<?php else: ?>
var EXISTING_TERMS = [];
<?php endif; ?>

// With this more robust version:
var EXISTING_TERMS = [];

<?php if ($isEditing && isset($existingTerms) && !empty($existingTerms)): ?>
    console.log('Loading existing terms from PHP:', <?= json_encode($existingTerms) ?>);
    EXISTING_TERMS = <?= json_encode($existingTerms) ?>;
    console.log('EXISTING_TERMS loaded:', EXISTING_TERMS);
    console.log('EXISTING_TERMS count:', EXISTING_TERMS.length);
<?php else: ?>
    console.log('No existing terms found. IS_EDITING: <?= $isEditing ? 'true' : 'false' ?>, existingTerms count: <?= isset($existingTerms) ? count($existingTerms) : 0 ?>');
<?php endif; ?>

  // Format date with ordinal suffix and day name
  function formatDateWithSuffix(dateString) {
      if (!dateString) return { date: '—', day: '—' };
      var date = moment(dateString, 'YYYY-MM-DD');
      if (!date.isValid()) return { date: '—', day: '—' };
      
      var day = date.date();
      var suffix = getOrdinalSuffix(day);
      var formattedDate = day + suffix + ' ' + date.format('MMMM YYYY');
      var dayName = date.format('dddd');
      
      return {
          date: formattedDate,
          day: dayName
      };
  }


// Convert from YYYY-MM-DD to DD/MM/YYYY for display
function toDisplayDate(dateString) {
    if (!dateString) return '';
    var date = moment(dateString, 'YYYY-MM-DD');
    if (!date.isValid()) return '';
    return date.format('DD/MM/YYYY');
}

// Convert from DD/MM/YYYY to YYYY-MM-DD for storage
function toStorageDate(dateString) {
    if (!dateString) return '';
    var date = moment(dateString, 'DD/MM/YYYY');
    if (!date.isValid()) return '';
    return date.format('YYYY-MM-DD');
}


  function getOrdinalSuffix(day) {
      if (day > 3 && day < 21) return 'th';
      switch (day % 10) {
          case 1: return 'st';
          case 2: return 'nd';
          case 3: return 'rd';
          default: return 'th';
      }
  }

  // Helper function to update cut point display
  function updateCutPointDisplay(index, dateValue) {
      if (dateValue) {
          var formatted = formatDateWithSuffix(dateValue);
          $('#cutDateDisplay_' + index).text(formatted.date);
          $('#cutDayDisplay_' + index).text('(' + formatted.day + ')');
      } else {
          $('#cutDateDisplay_' + index).text('—');
          $('#cutDayDisplay_' + index).text('—');
      }
  }

  function toYMD(m) {
    return (m && m.isValid()) ? m.format('YYYY-MM-DD') : '';
  }

  function parseYMD(v) {
    return moment(v, 'YYYY-MM-DD', true);
  }

  function updateSessionSummary() {
    const sVal = $('#startdatepicker').val();
    const eVal = $('#enddatepicker').val();
    const s    = parseYMD(sVal);
    const e    = parseYMD(eVal);

    let days  = 0;
    let weeks = 0;

    if (s.isValid() && e.isValid() && !e.isBefore(s, 'day')) {
      days  = e.diff(s, 'days') + 1;
      weeks = Math.floor(days / 7);
    }

    $('#sessDays').text(days);
    $('#sessWeeks').text(weeks);
  }

  // Initialize date pickers only if not editing
  <?php if (!$isEditing): ?>
  $('#sessionStartPicker').datetimepicker({
    format: 'YYYY-MM-DD',
    useCurrent: false,
    ignoreReadonly: true
  });

  $('#sessionEndPicker').datetimepicker({
    format: 'YYYY-MM-DD',
    useCurrent: false,
    ignoreReadonly: true
  });

  $('#sessionStartPicker').on('change.datetimepicker', function(e) {
    let d = e.date || moment($('#startdatepicker').val(), 'YYYY-MM-DD', true);
    if (!d || !d.isValid()) return;

    let dow = d.isoWeekday();
    if (dow !== 1) {
      d = d.clone().isoWeekday(1);
    }

    $('#sessionStartPicker').datetimepicker('date', d);
    $('#startdatepicker').val(d.format('YYYY-MM-DD'));

    $('#sessionEndPicker').datetimepicker('minDate', d.clone().startOf('day'));

    updateSessionSummary();
  });

  $('#sessionEndPicker').on('change.datetimepicker', function(e) {
    let d = e.date || moment($('#enddatepicker').val(), 'YYYY-MM-DD', true);
    if (!d || !d.isValid()) return;

    let dow = d.isoWeekday();
    if (dow !== 7) {
      d = d.clone().isoWeekday(7);
    }

    let s = moment($('#startdatepicker').val(), 'YYYY-MM-DD', true);
    if (s.isValid() && d.isBefore(s, 'day')) {
      d = s.clone().isoWeekday(7);
      if (d.isBefore(s)) d.add(7, 'days');
    }

    $('#sessionEndPicker').datetimepicker('date', d);
    $('#enddatepicker').val(d.format('YYYY-MM-DD'));

    updateSessionSummary();
  });

  $('#startdatepicker, #enddatepicker').on('click focus', function(e){
    e.preventDefault();
    const target = $(this).attr('id') === 'startdatepicker'
      ? '#sessionStartPicker'
      : '#sessionEndPicker';
    $(target).datetimepicker('show');
  });
  <?php endif; ?>

  updateSessionSummary();

$('#btnBuildTimeline').on('click', function () {
    var requested = parseInt($('#termsCount').val(), 10);
    if (isNaN(requested) || requested <= 0) {
        toastr.error('Please enter a valid number of terms.');
        return;
    }
    if (requested > 8) {
        toastr.error('Please keep terms between 1 and 8.');
        return;
    }

    var s = parseYMD($('#startdatepicker').val());
    var e = parseYMD($('#enddatepicker').val());
    if (!s.isValid() || !e.isValid() || e.isBefore(s, 'day')) {
        toastr.error('Please set a valid session start and end date first.');
        return;
    }

    CURRENT_TERMS_COUNT = requested;
    $('#timelineWrapper').show();
    
    // Show cut points section
    $('#cutPointsSection').show();
    
    // Format and display session start date
    var startFormatted = formatDateWithSuffix($('#startdatepicker').val());
    $('#timelineSessionStartDate').text(startFormatted.date);
    $('#timelineSessionStartDay').text('(' + startFormatted.day + ')');
    
    // Format and display session end date
    var endFormatted = formatDateWithSuffix($('#enddatepicker').val());
    $('#timelineSessionEndDate').text(endFormatted.date);
    $('#timelineSessionEndDay').text('(' + endFormatted.day + ')');
    
    // Build cut dates inputs
    var cutCount = requested - 1;
    var $cutContainer = $('#cutDatesContainer');
    $cutContainer.empty();
    
    if (cutCount > 0) {
        for (var i = 1; i <= cutCount; i++) {
            var cutHtml = 
                '<div class="cut-point" style="flex: 1;">' +
                    '<div class="cut-point-icon cut-icon">' +
                        '<i class="fa fa-cut"></i>' +
                    '</div>' +
                    '<div class="cut-point-label">END OF TERM ' + i + '</div>' +
                    '<div class="cut-point-date" id="cutDateDisplay_' + i + '">—</div>' +
                    '<div class="cut-point-day" id="cutDayDisplay_' + i + '">—</div>' +
                    '<div class="cut-point-input mt-2">' +
                        '<input type="date" ' +
                            'class="form-control form-control-sm term-cut-input" ' +
                            'data-index="' + i + '" ' +
                            'data-term="' + i + '" ' +
                            'style="font-size: 11px; text-align: center;">' +
                    '</div>' +
                '</div>';
            
            // Add connector between cut points (except after last)
            if (i < cutCount) {
                cutHtml += '<div class="timeline-connector"></div>';
            }
            
            $cutContainer.append(cutHtml);
        }
        
        // If editing existing session, populate cut dates from existing terms
        if (IS_EDITING && EXISTING_TERMS.length > 0 && EXISTING_TERMS.length === requested) {
            for (var i = 1; i <= cutCount; i++) {
                var cutDate = EXISTING_TERMS[i-1].end_date;
                $('.term-cut-input[data-index="' + i + '"]').val(cutDate);
                updateCutPointDisplay(i, cutDate);
            }
        } else {
            // Suggest initial cut dates for new session
            suggestInitialCutDates();
        }
        
        // Add change event for cut inputs
        $(document).off('change', '.term-cut-input').on('change', '.term-cut-input', function() {
            var index = $(this).data('index');
            var dateValue = $(this).val();
            updateCutPointDisplay(index, dateValue);
            recalcTermRangesFromCuts();
            $('#weeksPreviewAll').empty();
        });
        
    } else {
        $cutContainer.html(
            '<div class="text-center text-muted py-3" style="flex: 1;">' +
                '<i class="fa fa-info-circle"></i> Single term - no cut points needed' +
            '</div>'
        );
    }

   // Build term rows
var $tbody = $('#termsSummaryTable tbody');
var currentRows = $tbody.find('tr:not(.js-placeholder)').length;

// Only rebuild if we don't have existing rows or if the count doesn't match
if (currentRows === 0 || currentRows !== requested) {
    $tbody.empty();

    for (var t = 1; t <= requested; t++) {
        var existingTerm = null;
        if (EXISTING_TERMS.length > 0 && EXISTING_TERMS.length >= t) {
            existingTerm = EXISTING_TERMS[t-1];
        }
        
        var termName = '';
        var termShort = '';
        var termId = 0;
        var termStart = '';
        var termEnd = '';
        var termStartDisplay = '';
        var termEndDisplay = '';
        
        if (existingTerm) {
            termName = existingTerm.name || '';
            termShort = existingTerm.short_name || '';
            termId = existingTerm.term_id || 0;
            termStart = existingTerm.start_date || '';
            termEnd = existingTerm.end_date || '';
            termStartDisplay = toDisplayDate(termStart);
            termEndDisplay = toDisplayDate(termEnd);
        }
        
        var readonlyAttr = (IS_EDITING && existingTerm) ? 'readonly style="background-color:#f5f5f5;"' : '';
        
        var rowHtml =
            '<tr data-term-index="' + t + '">' +
              '<td class="text-center">' + t + '<\/td>' +
              '<td>' +
                '<input type="text" ' +
                       'name="term_name[' + t + ']" ' +
                       'class="form-control form-control-sm term-name-input" ' +
                       'placeholder="Term ' + t + '" ' +
                       'value="' + escapeHtml(termName) + '">' +
                (termId ? '<input type="hidden" name="term_id[' + t + ']" value="' + termId + '">' : '') +
              '<\/td>' +
              '<td>' +
                '<input type="text" ' +
                       'name="term_short[' + t + ']" ' +
                       'class="form-control form-control-sm term-short-input" ' +
                       'placeholder="T' + t + '" ' +
                       'value="' + escapeHtml(termShort) + '">' +
              '<\/td>' +
              '<td>' +
                '<input type="text" ' +
                       'name="term_start[' + t + ']" ' +
                       'class="form-control form-control-sm term-start-field datepicker-display" ' +
                       'placeholder="DD/MM/YYYY" ' +
                       'value="' + termStartDisplay + '" ' +
                       'data-original-date="' + termStart + '" ' +
                       readonlyAttr + '>' +
              '<\/td>' +
              '<td>' +
                '<input type="text" ' +
                       'name="term_end[' + t + ']" ' +
                       'class="form-control form-control-sm term-end-field datepicker-display" ' +
                       'placeholder="DD/MM/YYYY" ' +
                       'value="' + termEndDisplay + '" ' +
                       'data-original-date="' + termEnd + '" ' +
                       readonlyAttr + '>' +
              '<\/td>' +
              '<td class="text-center">' +
                '<span class="term-days-weeks" data-term-index="' + t + '">—<\/span>' +
              '<\/td>' +
            '<\/tr>';

        $tbody.append(rowHtml);
    }
} else {
    // Update existing rows with existing term data and format dates
    for (var t = 1; t <= requested; t++) {
        var existingTerm = EXISTING_TERMS[t-1] || null;
        if (existingTerm) {
            $('input[name="term_name[' + t + ']"]').val(existingTerm.name || '');
            $('input[name="term_short[' + t + ']"]').val(existingTerm.short_name || '');
            if (existingTerm.term_id) {
                if ($('input[name="term_id[' + t + ']"]').length === 0) {
                    $('input[name="term_name[' + t + ']"]').after('<input type="hidden" name="term_id[' + t + ']" value="' + existingTerm.term_id + '">');
                } else {
                    $('input[name="term_id[' + t + ']"]').val(existingTerm.term_id);
                }
            }
            // Format dates for display
            var startDisplay = toDisplayDate(existingTerm.start_date);
            var endDisplay = toDisplayDate(existingTerm.end_date);
            $('input[name="term_start[' + t + ']"]').val(startDisplay).attr('data-original-date', existingTerm.start_date || '');
            $('input[name="term_end[' + t + ']"]').val(endDisplay).attr('data-original-date', existingTerm.end_date || '');
            if (IS_EDITING && existingTerm) {
                $('input[name="term_start[' + t + ']"]').attr('readonly', true).css('background-color', '#f5f5f5');
                $('input[name="term_end[' + t + ']"]').attr('readonly', true).css('background-color', '#f5f5f5');
            }
        }
    }
}

    // Recalculate term ranges from cuts
    if (IS_EDITING && EXISTING_TERMS.length > 0) {
        // If editing existing session, recalc from existing term dates
        recalcTermRangesFromExisting();
    } else {
        recalcTermRangesFromCuts();
    }
    
    $('#weeksPreviewAll').empty();
});


function recalcTermRangesFromExisting() {
    if (!CURRENT_TERMS_COUNT) return;

    var requested = CURRENT_TERMS_COUNT;
    
    for (var t = 1; t <= requested; t++) {
        var existingTerm = EXISTING_TERMS[t-1];
        if (existingTerm && existingTerm.start_date && existingTerm.end_date) {
            var start = parseYMD(existingTerm.start_date);
            var end = parseYMD(existingTerm.end_date);
            
            if (start.isValid() && end.isValid() && !end.isBefore(start, 'day')) {
                var days = end.diff(start, 'days') + 1;
                var weeks = Math.floor(days / 7);
                $('.term-days-weeks[data-term-index="' + t + '"]').text(days + ' days / ' + weeks + ' weeks');
            } else {
                $('.term-days-weeks[data-term-index="' + t + '"]').text('—');
            }
        } else {
            $('.term-days-weeks[data-term-index="' + t + '"]').text('—');
        }
    }
}

function recalcTermRangesFromCuts() {
    if (!CURRENT_TERMS_COUNT) return;

    var s = parseYMD($('#startdatepicker').val());
    var e = parseYMD($('#enddatepicker').val());
    if (!s.isValid() || !e.isValid() || e.isBefore(s, 'day')) return;

    var requested = CURRENT_TERMS_COUNT;
    var cutCount = requested - 1;
    
    if (cutCount <= 0) {
        // Single term
        var startDisplay = toDisplayDate(toYMD(s));
        var endDisplay = toDisplayDate(toYMD(e));
        $('input[name="term_start[1]"]').val(startDisplay).attr('data-original-date', toYMD(s));
        $('input[name="term_end[1]"]').val(endDisplay).attr('data-original-date', toYMD(e));
        
        // Calculate and update days/weeks
        var days = e.diff(s, 'days') + 1;
        var weeks = Math.floor(days / 7);
        $('.term-days-weeks[data-term-index="1"]').text(days + ' days / ' + weeks + ' weeks');
        return;
    }
    
    var cuts = [];
    $('.term-cut-input').each(function(){
        var raw = $(this).val();
        var m = parseYMD(raw);
        if (m.isValid()) {
            // Snap to Sunday
            if (m.isoWeekday() !== 7) {
                m = m.clone().isoWeekday(7);
                if (m.isAfter(e, 'day')) m = e.clone();
            }
            cuts.push(m);
        }
    });
    
    if (cuts.length === 0 && cutCount > 0) {
        suggestInitialCutDates();
        return;
    }
    
    if (cuts.length !== cutCount) return;
    
    cuts.sort(function(a,b){ return a.valueOf() - b.valueOf(); });
    
    for (var idx = 0; idx < requested; idx++) {
        var tStart, tEnd;
        
        if (idx === 0) {
            tStart = s.clone();
            tEnd = cuts[0].clone();
        } else if (idx === requested - 1) {
            tStart = cuts[cutCount - 1].clone().add(1, 'day');
            tEnd = e.clone();
        } else {
            tStart = cuts[idx - 1].clone().add(1, 'day');
            tEnd = cuts[idx].clone();
        }
        
        if (tStart.isoWeekday() !== 1) {
            tStart = tStart.clone().isoWeekday(1);
            if (tStart.isBefore(s, 'day')) tStart = s.clone();
        }
        
        var startDisplay = toDisplayDate(toYMD(tStart));
        var endDisplay = toDisplayDate(toYMD(tEnd));
        $('input[name="term_start[' + (idx+1) + ']"]').val(startDisplay).attr('data-original-date', toYMD(tStart));
        $('input[name="term_end[' + (idx+1) + ']"]').val(endDisplay).attr('data-original-date', toYMD(tEnd));
        
        // Calculate and update days/weeks for this term
        var days = tEnd.diff(tStart, 'days') + 1;
        var weeks = Math.floor(days / 7);
        $('.term-days-weeks[data-term-index="' + (idx+1) + '"]').text(days + ' days / ' + weeks + ' weeks');
    }
}

  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
      if (m === '&') return '&amp;';
      if (m === '<') return '&lt;';
      if (m === '>') return '&gt;';
      return m;
    });
  }

  function suggestInitialCutDates() {
    var s = parseYMD($('#startdatepicker').val());
    var e = parseYMD($('#enddatepicker').val());
    if (!s.isValid() || !e.isValid() || e.isBefore(s, 'day')) return;

    var requested = CURRENT_TERMS_COUNT;
    var totalDays = e.diff(s, 'days') + 1;
    var segment   = Math.floor(totalDays / requested);
    
    var cuts = [];
    for (var i = 1; i < requested; i++) {
        var approxEnd = s.clone().add(segment * i - 1, 'days');
        if (approxEnd.isAfter(e, 'day')) approxEnd = e.clone();
        
        if (approxEnd.isoWeekday() !== 7) {
            approxEnd = approxEnd.clone().isoWeekday(7);
            if (approxEnd.isAfter(e, 'day')) {
                approxEnd = e.clone();
            }
        }
        cuts.push(approxEnd);
    }
    
    // Update cut input fields with suggested dates and display
    for (var i = 0; i < cuts.length; i++) {
        var cutDate = toYMD(cuts[i]);
        $('.term-cut-input[data-index="' + (i+1) + '"]').val(cutDate);
        updateCutPointDisplay(i+1, cutDate);
    }
    
    applyCuts(cuts);
  }

  function applyCuts(cuts) {
    var s = parseYMD($('#startdatepicker').val());
    var e = parseYMD($('#enddatepicker').val());
    if (!s.isValid() || !e.isValid() || e.isBefore(s, 'day')) return;
    
    var requested = CURRENT_TERMS_COUNT;
    var starts = [];
    var ends = [];
    
    for (var idx = 0; idx < requested; idx++) {
      var tStart, tEnd;
      
      if (idx === 0) {
        tStart = s.clone();
        tEnd = (cuts.length > 0) ? cuts[0].clone() : e.clone();
      } else if (idx === requested - 1) {
        tStart = cuts[cuts.length - 1].clone().add(1, 'day');
        tEnd = e.clone();
      } else {
        tStart = cuts[idx - 1].clone().add(1, 'day');
        tEnd = cuts[idx].clone();
      }
      
      if (tStart.isoWeekday() !== 1) {
        tStart = tStart.clone().isoWeekday(1);
        if (tStart.isBefore(s, 'day')) tStart = s.clone();
      }
      if (tEnd.isoWeekday() !== 7) {
        tEnd = tEnd.clone().isoWeekday(7);
        if (tEnd.isAfter(e, 'day')) tEnd = e.clone();
      }
      
      starts.push(tStart);
      ends.push(tEnd);
    }
    
    for (var t = 1; t <= requested; t++) {
      $('input[name="term_start['+t+']"]').val(toYMD(starts[t-1]));
      $('input[name="term_end['+t+']"]').val(toYMD(ends[t-1]));
    }
  }
function recalcTermRanges() {
    if (!CURRENT_TERMS_COUNT) return;

    var requested = CURRENT_TERMS_COUNT;
    
    for (var t = 1; t <= requested; t++) {
        var startDisplayVal = $('input[name="term_start[' + t + ']"]').val();
        var endDisplayVal = $('input[name="term_end[' + t + ']"]').val();
        
        // Convert from display format (DD/MM/YYYY) to storage format for calculation
        var start = toStorageDate(startDisplayVal);
        var end = toStorageDate(endDisplayVal);
        
        var startDate = parseYMD(start);
        var endDate = parseYMD(end);
        
        if (startDate.isValid() && endDate.isValid() && !endDate.isBefore(startDate, 'day')) {
            var days = endDate.diff(startDate, 'days') + 1;
            var weeks = Math.floor(days / 7);
            $('.term-days-weeks[data-term-index="' + t + '"]').text(days + ' days / ' + weeks + ' weeks');
        } else {
            $('.term-days-weeks[data-term-index="' + t + '"]').text('—');
        }
    }
}
 // ========= WEEKS PREVIEW =========
$('#btnPreviewAllWeeks').on('click', function(){
    $('#weeksPreviewAll').empty();

    if (!CURRENT_TERMS_COUNT) {
        toastr.error('Please build the term timeline first.');
        return;
    }

    var sessionName = $('#session_name').val() || '';
    var suffix = sessionName.slice(-2);
    if (!suffix) suffix = 'YY';

    for (var t = 1; t <= CURRENT_TERMS_COUNT; t++) {
        // Get display values (DD/MM/YYYY format)
        var startDisplayVal = $('input[name="term_start[' + t + ']"]').val();
        var endDisplayVal = $('input[name="term_end[' + t + ']"]').val();
        
        // Convert to YYYY-MM-DD for calculation
        var start = toStorageDate(startDisplayVal);
        var end = toStorageDate(endDisplayVal);
        
        var startDate = parseYMD(start);
        var endDate = parseYMD(end);

        if (!startDate.isValid() || !endDate.isValid()) {
            console.log('Invalid dates for term ' + t + ': start=' + startDisplayVal + ', end=' + endDisplayVal);
            continue;
        }

        var termName = $('input[name="term_name[' + t + ']"]').val() || ('Term ' + t);
        var termShort = $('input[name="term_short[' + t + ']"]').val() || ('T' + t);

        var $card = $('<div class="mt-2 border rounded p-2"></div>');
        $card.append('<div class="small font-weight-bold mb-1">' +
                     termName + ' (' + startDisplayVal + ' → ' + endDisplayVal + ')</div>');

        var table = '<div class="table-responsive">' +
                    '<table class="table table-sm table-bordered mb-0">' +
                    '<thead class="thead-light">' +
                    '<tr><th style="width:8%;">#</th>' +
                    '<th style="width:22%;">Week Name</th>' +
                    '<th style="width:22%;">Start (Mon)</th>' +
                    '<th style="width:22%;">End (Sun)</th>' +
                    '<th style="width:26%;">Week Type</th></tr>' +
                    '</thead><tbody>';

        var cursor = startDate.clone();
        var weekNo = 1;

        // Snap cursor to Monday
        if (cursor.isoWeekday() !== 1) {
            cursor = cursor.clone().isoWeekday(1);
            if (cursor.isBefore(startDate, 'day')) {
                cursor.add(7, 'days');
            }
        }

        while (cursor.isSameOrBefore(endDate, 'day')) {
            var wStart = cursor.clone();
            var wEnd = cursor.clone().add(6, 'days');
            if (wEnd.isAfter(endDate, 'day')) break;

            var weekName = suffix + '-' + termShort + '-W' + weekNo;

            var optionsHtml = '<option value="">Select Type</option>';
            <?php if (!empty($weekTypes)): ?>
                <?php foreach ($weekTypes as $wt): ?>
                    optionsHtml += '<option value="<?= (int)$wt->type_id; ?>"' +
                        <?php if (strtolower($wt->short_name) === 'study'): ?>
                        ' selected' +
                        <?php endif; ?>
                        '><?= esc($wt->short_name); ?> - <?= esc($wt->type_name); ?></option>';
                <?php endforeach; ?>
            <?php endif; ?>

            table += '<tr>' +
                     '<td>' + weekNo + '</td>' +
                     '<td>' + weekName + '</td>' +
                     '<td>' + wStart.format('YYYY-MM-DD') + '</td>' +
                     '<td>' + wEnd.format('YYYY-MM-DD') + '</td>' +
                     '<td>' +
                     '<select name="week_type[' + t + '][' + weekNo + ']" ' +
                     'class="form-control form-control-sm">' +
                     optionsHtml +
                     '</select>' +
                     '</td>' +
                     '</tr>';

            weekNo++;
            cursor.add(7, 'days');
        }

        if (weekNo === 1) {
            table += '<tr><td colspan="5" class="text-center text-muted">No full Mon–Sun weeks in this term.</td></tr>';
        }

        table += '</tbody></table></div>';
        $card.append(table);
        $('#weeksPreviewAll').append($card);
    }
});
  // ========= FORM SUBMIT =========
 // ========= FORM SUBMIT =========
$('#calendarBuilderForm').on('submit', function(e){
    e.preventDefault();
    
    // Convert display dates back to YYYY-MM-DD format before submitting
    $('.term-start-field, .term-end-field').each(function() {
        var displayValue = $(this).val();
        if (displayValue && displayValue !== '') {
            var storageDate = toStorageDate(displayValue);
            // Create a hidden input with the correct format
            var originalName = $(this).attr('name');
            $(this).after('<input type="hidden" name="' + originalName + '" value="' + storageDate + '">');
            // Disable the display field so it doesn't submit
            $(this).prop('disabled', true);
        } else {
            // If empty, disable the field
            $(this).prop('disabled', true);
        }
    });

    $('#saveCalendarBtn').prop('disabled', true).text('Saving...');

    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res){
            $('#saveCalendarBtn').prop('disabled', false).text('Save Academic Calendar');

            if (res.success) {
                toastr.success(res.msg || 'Saved.');
                if (res.redirect) {
                    window.location.href = res.redirect;
                }
            } else {
                toastr.error(res.msg || 'Save failed.');
            }
        },
        error: function(){
            $('#saveCalendarBtn').prop('disabled', false).text('Save Academic Calendar');
            toastr.error('Network or server error.');
        }
    });
});
  // Auto-trigger build on page load
  $(document).ready(function() {
    $('#btnBuildTimeline').trigger('click');
  });

})(jQuery);
</script>
<style>

  .cut-points-container {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
}

.cut-points-timeline {
    display: flex;
    align-items: stretch;
    justify-content: space-between;
    position: relative;
}

.cut-point {
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 2;
    background: white;
    border-radius: 8px;
    padding: 10px;
    margin: 0 5px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.cut-point-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    margin-bottom: 8px;
}

.cut-point-date {
    font-size: 13px;
    font-weight: 600;
    color: #495057;
    margin-bottom: 4px;
}

.cut-point-day {
    font-size: 11px;
    color: #6c757d;
}

.cut-point-input {
    margin-top: 10px;
}

.cut-point-input input {
    text-align: center;
    font-size: 12px;
    padding: 4px 8px;
}

.timeline-connector {
    flex: 1;
    height: 2px;
    background: linear-gradient(90deg, #dee2e6 0%, #adb5bd 50%, #dee2e6 100%);
    margin: 0 5px;
    align-self: center;
    position: relative;
    top: -15px;
}

.cut-point-icon {
    font-size: 20px;
    margin-bottom: 5px;
}

.session-start-icon {
    color: #28a745;
}

.cut-icon {
    color: #ffc107;
}

.session-end-icon {
    color: #dc3545;
}


  .bootstrap-datetimepicker-widget {
    z-index: 3000 !important;
  }
</style>

<?= $this->endSection() ?>