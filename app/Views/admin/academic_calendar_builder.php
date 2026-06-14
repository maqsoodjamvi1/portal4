<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('pageStyles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/academic_calendar_builder.css') ?>?v=3">
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?php
  $isNewSession = ! empty($isNewSession);
  $acbSessionLocked = false;
  $acbTermsCountLocked = ! $isFirstSession && ! $isEditing;
?>

<?php
$acbNewSessionBtn = (! $isFirstSession && ! $isNewSession)
    ? '<a href="' . esc(base_url('admin/academic-calendar/builder?new=1'), 'attr') . '" class="btn btn-outline-primary btn-sm"><i class="fas fa-plus"></i> New session</a>'
    : '';
$acbCancelNewBtn = ($isNewSession && ! $isFirstSession)
    ? '<a href="' . esc(base_url('admin/academic-calendar/builder'), 'attr') . '" class="btn btn-outline-secondary btn-sm ms-1"><i class="fas fa-times"></i> Cancel</a>'
    : '';
?>
<?= view('components/page_header', [
    'title' => 'Academic Calendar Builder',
    'icon' => 'far fa-calendar-alt',
    'subtitle' => $isNewSession
        ? 'Create the next academic session — save will add a new session.'
        : ($isEditing
            ? 'Editing the latest academic session — save will update it.'
            : 'Set up your first session in 3 short steps.'),
    'actionsHtml' => ($acbNewSessionBtn !== '' || $acbCancelNewBtn !== '')
        ? '<div class="text-sm-right">' . $acbNewSessionBtn . $acbCancelNewBtn . '</div>'
        : null,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Academic Calendar', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid">
    <?= view('admin/partials/setup_step_context', ['setup_step_id' => 'calendar']) ?>

    <div class="acb-legend no-print" aria-label="Field types">
      <span class="acb-legend__item"><span class="acb-legend__swatch acb-legend__swatch--required"></span> Required — fill in</span>
      <span class="acb-legend__item"><span class="acb-legend__swatch acb-legend__swatch--editable"></span> Editable</span>
      <span class="acb-legend__item"><span class="acb-legend__swatch acb-legend__swatch--locked"></span> Locked — cannot change</span>
      <span class="acb-legend__item"><span class="acb-legend__swatch acb-legend__swatch--auto"></span> Auto-calculated</span>
    </div>

    <?php $acbNotice = session()->getFlashdata('acb_notice'); ?>
    <?php if (! empty($acbNotice)): ?>
    <div class="alert alert-warning alert-dismissible fade show py-2" role="alert">
      <?= esc($acbNotice) ?>
      <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    <?php endif; ?>

    <?php if (! empty($allSessions) && ! $isFirstSession): ?>
    <div class="card card-outline shadow-sm mb-3 acb-sessions-card collapsed-card">
      <div class="card-header py-2">
        <h3 class="card-title mb-0 text-sm">
          <i class="fa fa-history"></i> Past &amp; future sessions
          <i class="fas fa-info-circle acb-tip ms-1" data-bs-toggle="tooltip"
             title="The latest session opens by default. Use New session to plan the next year."></i>
        </h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fa fa-plus"></i></button>
        </div>
      </div>
      <div class="card-body pt-2" style="display:none;">
        <div class="table-responsive acb-sessions-scroll border rounded">
          <table class="table table-sm table-hover table-striped mb-0">
            <thead class="table-light">
               <tr>
                <th>Session</th>
                <th>Start</th>
                <th>End</th>
                <th class="text-center">Terms</th>
                <th style="width:1%;" class="text-nowrap">Status / action</th>
               </tr>
            </thead>
            <tbody>
              <?php foreach ($allSessions as $sess): ?>
                <?php
                  $termsCountInSess = $sessionTermCounts[$sess->session_id] ?? 0;
                  $sessId = (int) $sess->session_id;
                  $today  = date('Y-m-d');
                  $sd     = (string) $sess->start_date;
                  $ed     = (string) $sess->end_date;
                  $curId = isset($current_session_id) ? (int) $current_session_id : 0;
                  $latestId = isset($latest_session_id) ? (int) $latest_session_id : 0;
                  $canEditRow = $latestId > 0 && $sessId === $latestId;
                  if ($today < $sd) {
                      $lockLabel = 'Future';
                  } elseif ($today > $ed) {
                      $lockLabel = 'Past';
                  } elseif ($curId > 0 && $sessId === $curId) {
                      $lockLabel = 'Current';
                  } else {
                      $lockLabel = '—';
                  }
                  $isActiveRow = $isEditing && isset($session_id) && (int) $session_id === $sessId;
                ?>
                <tr class="<?= $canEditRow ? 'table-primary' : '' ?>">
                  <td><?= esc($sess->session_name) ?></td>
                  <td><?= esc(date('M j, Y', strtotime($sd))) ?></td>
                  <td><?= esc(date('M j, Y', strtotime($ed))) ?></td>
                  <td class="text-center"><?= $termsCountInSess > 0 ? (int) $termsCountInSess : '—' ?></td>
                  <td>
                    <?php if ($canEditRow): ?>
                    <a href="<?= base_url('admin/academic-calendar/builder') ?>"
                       class="btn btn-sm <?= $isActiveRow ? 'btn-primary' : 'btn-outline-primary' ?>">
                      <i class="fa fa-edit"></i> <?= $isActiveRow ? 'Editing' : 'Edit latest' ?>
                    </a>
                    <?php elseif ($curId > 0 && $sessId === $curId): ?>
                    <span class="badge text-bg-success">Current</span>
                    <?php else: ?>
                    <span class="badge text-bg-secondary"><?= esc($lockLabel) ?></span>
                    <?php endif; ?>
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

    <!-- STEP 1 -->
    <div class="acb-step-card">
      <div class="acb-step-card__head acb-step-card__head--primary">
        <span class="acb-step-badge">1</span>
        <h3 class="acb-step-card__title">Academic session</h3>
        <?php if ($isEditing && ! $isNewSession): ?>
          <span class="badge text-bg-info">Editing <?= esc($session_name) ?></span>
        <?php elseif ($isNewSession): ?>
          <span class="badge text-bg-success">New session</span>
        <?php endif; ?>
      </div>
      <div class="acb-step-card__body">
        <div class="row">
          <div class="col-md-4">
            <div class="acb-field-group acb-input--<?= $acbSessionLocked ? 'locked' : 'required' ?>">
              <label class="acb-label" for="session_name">
                Session name
                <?php if (! $acbSessionLocked): ?><span class="acb-req">*</span><?php endif; ?>
                <i class="fas fa-question-circle acb-tip" data-bs-toggle="tooltip"
                   title="Format: 2026-27 (start year + end year)."></i>
                <?php if ($acbSessionLocked): ?>
                  <span class="acb-label__state acb-label__state--locked"><i class="fa fa-lock fa-xs"></i> Locked</span>
                <?php else: ?>
                  <span class="acb-label__state acb-label__state--required">Required</span>
                <?php endif; ?>
              </label>
              <input type="text"
                     class="form-control"
                     id="session_name"
                     name="session_name"
                     data-inputmask='"mask": "9999-99"'
                     data-mask
                     value="<?= esc($session_name); ?>"
                     placeholder="2026-27"
                     <?= $acbSessionLocked ? 'readonly' : '' ?>>
              <?php if ($acbSessionLocked): ?>
                <input type="hidden" name="session_name" value="<?= esc($session_name); ?>">
              <?php endif; ?>
            </div>
          </div>

          <div class="col-md-4">
            <div class="acb-field-group acb-input--<?= $acbSessionLocked ? 'locked' : 'required' ?>">
              <label class="acb-label" for="startdatepicker">
                Start date
                <?php if (! $acbSessionLocked): ?><span class="acb-req">*</span><?php endif; ?>
                <i class="fas fa-question-circle acb-tip" data-bs-toggle="tooltip"
                   title="Must be a Monday. School weeks run Monday to Sunday."></i>
                <?php if ($acbSessionLocked): ?>
                  <span class="acb-label__state acb-label__state--locked"><i class="fa fa-lock fa-xs"></i> Locked</span>
                <?php else: ?>
                  <span class="acb-label__state acb-label__state--required">Required</span>
                <?php endif; ?>
              </label>
              <div class="input-group date" id="sessionStartPicker" data-target-input="nearest">
                <input type="text"
                       class="form-control datetimepicker-input"
                       id="startdatepicker"
                       name="start_date"
                       data-bs-target="#sessionStartPicker"
                       autocomplete="off"
                       placeholder="YYYY-MM-DD"
                       value="<?= esc($start_date); ?>"
                       <?= $acbSessionLocked ? 'readonly' : '' ?>>
                <span class="input-group-text" data-bs-target="#sessionStartPicker" data-bs-toggle="datetimepicker"><i class="fa fa-calendar"></i></span>
              </div>
              <?php if ($acbSessionLocked): ?>
                <input type="hidden" name="start_date" value="<?= esc($start_date); ?>">
              <?php endif; ?>
            </div>
          </div>

          <div class="col-md-4">
            <div class="acb-field-group acb-input--<?= $acbSessionLocked ? 'locked' : 'required' ?>">
              <label class="acb-label" for="enddatepicker">
                End date
                <?php if (! $acbSessionLocked): ?><span class="acb-req">*</span><?php endif; ?>
                <i class="fas fa-question-circle acb-tip" data-bs-toggle="tooltip"
                   title="Must be a Sunday. Must be after the start date."></i>
                <?php if ($acbSessionLocked): ?>
                  <span class="acb-label__state acb-label__state--locked"><i class="fa fa-lock fa-xs"></i> Locked</span>
                <?php else: ?>
                  <span class="acb-label__state acb-label__state--required">Required</span>
                <?php endif; ?>
              </label>
              <div class="input-group date" id="sessionEndPicker" data-target-input="nearest">
                <input type="text"
                       class="form-control datetimepicker-input"
                       id="enddatepicker"
                       name="end_date"
                       data-bs-target="#sessionEndPicker"
                       autocomplete="off"
                       placeholder="YYYY-MM-DD"
                       value="<?= esc($end_date); ?>"
                       <?= $acbSessionLocked ? 'readonly' : '' ?>>
                <span class="input-group-text" data-bs-target="#sessionEndPicker" data-bs-toggle="datetimepicker"><i class="fa fa-calendar"></i></span>
              </div>
              <?php if ($acbSessionLocked): ?>
                <input type="hidden" name="end_date" value="<?= esc($end_date); ?>">
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="acb-stats-row">
          <div class="acb-stat-pill acb-input--auto">
            <div class="acb-stat-pill__label">
              Total days
              <i class="fas fa-info-circle acb-tip" data-bs-toggle="tooltip" title="Calculated from start and end dates (inclusive)."></i>
            </div>
            <div class="acb-stat-pill__value"><span id="sessDays">0</span></div>
          </div>
          <div class="acb-stat-pill acb-input--auto">
            <div class="acb-stat-pill__label">
              Full weeks (Mon–Sun)
              <i class="fas fa-info-circle acb-tip" data-bs-toggle="tooltip" title="Complete Monday–Sunday weeks inside the session."></i>
            </div>
            <div class="acb-stat-pill__value"><span id="sessWeeks">0</span></div>
          </div>
        </div>
      </div>
    </div>

    <!-- STEP 2 -->
    <div class="acb-step-card">
      <div class="acb-step-card__head acb-step-card__head--info">
        <span class="acb-step-badge">2</span>
        <h3 class="acb-step-card__title">Terms</h3>
        <?php if ($acbTermsCountLocked): ?>
          <span class="badge text-bg-secondary" data-bs-toggle="tooltip"
                title="Term count matches your previous session and cannot be changed.">
            <?= (int) $termsCount ?> terms
          </span>
        <?php endif; ?>
      </div>
      <div class="acb-step-card__body">

        <div class="acb-action-bar">
          <div class="acb-field-group acb-input--<?= $acbTermsCountLocked ? 'locked' : 'required' ?>">
            <label class="acb-label" for="termsCount">
              Number of terms
              <?php if (! $acbTermsCountLocked): ?><span class="acb-req">*</span><?php endif; ?>
              <i class="fas fa-question-circle acb-tip" data-bs-toggle="tooltip"
                 title="<?= $acbTermsCountLocked
                   ? 'Fixed to match your last session. Edit names and cut dates below.'
                   : 'How many terms split this session? Usually 2 or 3.' ?>"></i>
            </label>
            <input type="number"
                   id="termsCount"
                   class="form-control"
                   min="1"
                   max="8"
                   value="<?= $termsCount ?>"
                   <?= $acbTermsCountLocked ? 'readonly' : '' ?>>
          </div>
          <div class="acb-action-bar__btn">
            <button type="button" id="btnBuildTimeline" class="btn btn-primary w-100">
              <i class="fa fa-sitemap"></i> Build term timeline
            </button>
          </div>
        </div>
<!-- Cut Points Section for Editing Term Boundaries -->
<div id="cutPointsSection" style="display:none;" class="mb-3">
    <div class="cut-points-container">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0 fw-bold">
              <i class="fa fa-sliders-h"></i> Term boundaries
              <i class="fas fa-question-circle acb-tip" data-bs-toggle="tooltip"
                 title="Drag cut dates to split the session into terms. Each cut marks the end of a term (Sunday)."></i>
            </h6>
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
<div id="timelineWrapper" style="<?= (!$isFirstSession || $isEditing) ? 'display:block;' : 'display:none;' ?>">
    <div class="d-flex align-items-center mb-2">
        <strong class="me-2">Term details</strong>
        <i class="fas fa-question-circle acb-tip" data-bs-toggle="tooltip"
           title="<?= $isEditing
             ? 'Edit term names. Dates update from cut points above — click Build term timeline to refresh.'
             : ($acbTermsCountLocked
               ? 'Fill term names and short codes. Dates are set automatically from cut points.'
               : 'Enter term names, then build the timeline to auto-fill dates.') ?>"></i>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0 acb-term-table" id="termsSummaryTable">
            <thead class="table-light">
                 <tr>
                    <th>#</th>
                    <th>Term name <span class="acb-req">*</span></th>
                    <th>Short <i class="fas fa-info-circle acb-tip" data-bs-toggle="tooltip" title="Used in week codes, e.g. T1"></i></th>
                    <th>Start <span class="acb-label__state acb-label__state--auto d-inline">Auto</span></th>
                    <th>End <span class="acb-label__state acb-label__state--auto d-inline">Auto</span></th>
                    <th>Duration</th>
                 </tr>
            </thead>
            <tbody>
                <?php if (!empty($existingTerms)): ?>
                    <?php foreach ($existingTerms as $idx => $term): ?>
                        <?php
                            $startDateFormatted = !empty($term->start_date) ? date('d/m/Y', strtotime($term->start_date)) : '';
                            $endDateFormatted = !empty($term->end_date) ? date('d/m/Y', strtotime($term->end_date)) : '';
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
                            $termNamesLocked = false;
                        ?>
                        <tr data-term-index="<?= $idx + 1 ?>">
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td class="acb-input--<?= $termNamesLocked ? 'locked' : 'required' ?>">
                                <input type="text"
                                       name="term_name[<?= $idx + 1 ?>]"
                                       class="form-control form-control-sm term-name-input"
                                       value="<?= esc($term->name ?? '') ?>"
                                       placeholder="Term <?= $idx + 1 ?>"
                                       <?= $termNamesLocked ? 'readonly' : '' ?>>
                                <input type="hidden" name="term_id[<?= $idx + 1 ?>]" value="<?= $term->term_id ?? '' ?>">
                                <input type="hidden" name="term_session_id[<?= $idx + 1 ?>]" value="<?= (int) ($term->term_session_id ?? 0) ?>">
                            </td>
                            <td class="acb-input--<?= $termNamesLocked ? 'locked' : 'editable' ?>">
                                <input type="text"
                                       name="term_short[<?= $idx + 1 ?>]"
                                       class="form-control form-control-sm term-short-input"
                                       value="<?= esc($term->short_name ?? '') ?>"
                                       placeholder="T<?= $idx + 1 ?>"
                                       <?= $termNamesLocked ? 'readonly' : '' ?>>
                            </td>
                            <td class="acb-input--locked">
                                <input type="text"
                                       name="term_start[<?= $idx + 1 ?>]"
                                       class="form-control form-control-sm term-start-field datepicker-display"
                                       value="<?= $startDateFormatted ?>"
                                       data-original-date="<?= esc($term->start_date ?? '') ?>"
                                       readonly>
                            </td>
                            <td class="acb-input--locked">
                                <input type="text"
                                       name="term_end[<?= $idx + 1 ?>]"
                                       class="form-control form-control-sm term-end-field datepicker-display"
                                       value="<?= $endDateFormatted ?>"
                                       data-original-date="<?= esc($term->end_date ?? '') ?>"
                                       readonly>
                            </td>
                            <td class="text-center acb-input--auto">
                                <span class="term-days-weeks small" data-term-index="<?= $idx + 1 ?>"><?= $daysText ?>d / <?= $weeksText ?>w</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="js-placeholder">
                        <td colspan="6">
                          <div class="acb-empty-hint">
                            <i class="fa fa-arrow-up"></i>
                            Set session dates, enter term count, then click <strong>Build term timeline</strong>
                          </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
      </div>
    </div>

    <!-- STEP 3 -->
    <div class="acb-step-card collapsed-card" id="acbWeeksStep">
      <div class="acb-step-card__head acb-step-card__head--optional">
        <span class="acb-step-badge acb-step-badge--muted">3</span>
        <h3 class="acb-step-card__title">
          Week preview
          <span class="badge text-bg-light border ms-1">Optional</span>
          <i class="fas fa-question-circle acb-tip" data-bs-toggle="tooltip"
             title="Preview how weeks fall inside each term and assign week types (Study, Exam, etc.). Defaults apply if you skip this."></i>
        </h3>
        <div class="ms-auto">
          <button type="button" class="btn btn-sm btn-outline-primary" id="btnPreviewAllWeeks">
            <i class="fa fa-eye"></i> Preview weeks
          </button>
        </div>
      </div>
      <div class="acb-step-card__body pt-2" style="display:none;">
        <div id="weeksPreviewAll"></div>
      </div>
    </div>

    <!-- SAVE -->
    <div class="card shadow-sm acb-sticky-save">
      <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
        <div class="small text-muted mb-2 mb-md-0">
          <i class="fas fa-check-circle text-success"></i> Ready?
          <i class="fas fa-question-circle acb-tip" data-bs-toggle="tooltip"
             title="Saves session, terms, and generated week rows. You can adjust week types later."></i>
        </div>
        <button type="submit" class="btn btn-success btn-lg" id="saveCalendarBtn">
          <i class="fas fa-save"></i>
          <?= ($isEditing && ! $isNewSession) ? 'Save changes' : 'Save &amp; continue' ?>
        </button>
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
  var IS_NEW_SESSION = <?= $isNewSession ? 'true' : 'false' ?>;
  var IS_EDITING = <?= $isEditing ? 'true' : 'false' ?>;
  var TERMS_COUNT_LOCKED = <?= $acbTermsCountLocked ? 'true' : 'false' ?>;
  var SHOW_CUT_POINTS = true;

  var EXISTING_TERMS = <?= json_encode($existingTerms ?? [], JSON_UNESCAPED_UNICODE) ?>;

  function buildWeekTypeOptionsHtml(includeBlank, selectStudyDefault) {
      var html = includeBlank ? '<option value="">Select type</option>' : '';
      <?php if (! empty($weekTypes)): ?>
          <?php foreach ($weekTypes as $wt): ?>
              html += '<option value="<?= (int) $wt->type_id ?>"' +
                  <?php if (strtolower($wt->short_name) === 'study'): ?>
                  (selectStudyDefault ? ' selected' : '') +
                  <?php else: ?>
                  '' +
                  <?php endif; ?>
                  '><?= esc($wt->short_name) ?> - <?= esc($wt->type_name) ?></option>';
          <?php endforeach; ?>
      <?php endif; ?>
      return html;
  }

  var weekTypeOptionsForRows = buildWeekTypeOptionsHtml(true, true);
  var weekTypeOptionsForBulk = buildWeekTypeOptionsHtml(true, false);

  function buildWeekBulkBar(extraClass) {
      extraClass = extraClass || '';
      return '<div class="acb-week-bulk-bar d-flex align-items-center flex-wrap ' + extraClass + '">' +
          '<span class="small text-muted me-2 mb-1"><i class="fas fa-layer-group me-1"></i>Set week type for all rows:</span>' +
          '<select class="form-control form-control-sm acb-week-bulk-select me-2 mb-1" style="max-width:220px">' +
          weekTypeOptionsForBulk +
          '</select>' +
          '<button type="button" class="btn btn-sm btn-primary acb-week-bulk-apply mb-1">' +
          '<i class="fas fa-check-double me-1"></i>Apply to all</button>' +
          '</div>';
  }

  $(document).on('click', '.acb-week-bulk-apply', function () {
      var $bar = $(this).closest('.acb-week-bulk-bar');
      var val = $bar.find('.acb-week-bulk-select').val();
      if (!val) {
          toastr.warning('Select a week type first.');
          return;
      }
      var $scope = $bar.hasClass('acb-week-bulk-bar--global')
          ? $('#weeksPreviewAll')
          : $bar.closest('.acb-week-preview-card');
      $scope.find('select.week-type-select').val(val);
      toastr.success('Week type applied.');
  });

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

  // Session date pickers (editable for latest session and new session)
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
        var termSessionId = 0;
        var termStart = '';
        var termEnd = '';
        var termStartDisplay = '';
        var termEndDisplay = '';

        if (existingTerm) {
            termName = existingTerm.name || '';
            termShort = existingTerm.short_name || '';
            termId = existingTerm.term_id || 0;
            termSessionId = existingTerm.term_session_id || 0;
            termStart = existingTerm.start_date || '';
            termEnd = existingTerm.end_date || '';
            termStartDisplay = toDisplayDate(termStart);
            termEndDisplay = toDisplayDate(termEnd);
        }

        var rowHtml =
            '<tr data-term-index="' + t + '">' +
              '<td class="text-center">' + t + '<\/td>' +
              '<td class="acb-input--required">' +
                '<input type="text" ' +
                       'name="term_name[' + t + ']" ' +
                       'class="form-control form-control-sm term-name-input" ' +
                       'placeholder="Term ' + t + '" ' +
                       'value="' + escapeHtml(termName) + '">' +
                (termId ? '<input type="hidden" name="term_id[' + t + ']" value="' + termId + '">' : '') +
                (termSessionId ? '<input type="hidden" name="term_session_id[' + t + ']" value="' + termSessionId + '">' : '') +
              '<\/td>' +
              '<td class="acb-input--editable">' +
                '<input type="text" ' +
                       'name="term_short[' + t + ']" ' +
                       'class="form-control form-control-sm term-short-input" ' +
                       'placeholder="T' + t + '" ' +
                       'value="' + escapeHtml(termShort) + '">' +
              '<\/td>' +
              '<td class="acb-input--locked">' +
                '<input type="text" ' +
                       'name="term_start[' + t + ']" ' +
                       'class="form-control form-control-sm term-start-field datepicker-display" ' +
                       'placeholder="DD/MM/YYYY" ' +
                       'value="' + termStartDisplay + '" ' +
                       'data-original-date="' + termStart + '" ' +
                       'readonly>' +
              '<\/td>' +
              '<td class="acb-input--locked">' +
                '<input type="text" ' +
                       'name="term_end[' + t + ']" ' +
                       'class="form-control form-control-sm term-end-field datepicker-display" ' +
                       'placeholder="DD/MM/YYYY" ' +
                       'value="' + termEndDisplay + '" ' +
                       'data-original-date="' + termEnd + '" ' +
                       'readonly>' +
              '<\/td>' +
              '<td class="text-center acb-input--auto">' +
                '<span class="term-days-weeks small" data-term-index="' + t + '">—<\/span>' +
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
            if (existingTerm.term_session_id) {
                if ($('input[name="term_session_id[' + t + ']"]').length === 0) {
                    $('input[name="term_name[' + t + ']"]').after('<input type="hidden" name="term_session_id[' + t + ']" value="' + existingTerm.term_session_id + '">');
                } else {
                    $('input[name="term_session_id[' + t + ']"]').val(existingTerm.term_session_id);
                }
            }
            // Format dates for display
            var startDisplay = toDisplayDate(existingTerm.start_date);
            var endDisplay = toDisplayDate(existingTerm.end_date);
            $('input[name="term_start[' + t + ']"]').val(startDisplay).attr('data-original-date', existingTerm.start_date || '');
            $('input[name="term_end[' + t + ']"]').val(endDisplay).attr('data-original-date', existingTerm.end_date || '');
            $('input[name="term_start[' + t + ']"], input[name="term_end[' + t + ']"]').attr('readonly', true);
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
                $('.term-days-weeks[data-term-index="' + t + '"]').text(days + 'd / ' + weeks + 'w');
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
        $('.term-days-weeks[data-term-index="1"]').text(days + 'd / ' + weeks + 'w');
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
        $('.term-days-weeks[data-term-index="' + (idx+1) + '"]').text(days + 'd / ' + weeks + 'w');
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
            $('.term-days-weeks[data-term-index="' + t + '"]').text(days + 'd / ' + weeks + 'w');
        } else {
            $('.term-days-weeks[data-term-index="' + t + '"]').text('—');
        }
    }
}
 // ========= WEEKS PREVIEW =========
$('#btnPreviewAllWeeks').on('click', function(){
    $('#acbWeeksStep .acb-step-card__body').slideDown();
    $('#acbWeeksStep').removeClass('collapsed-card');
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

        var $card = $('<div class="acb-week-preview-card mt-2 border rounded p-2"></div>');
        $card.append('<div class="small fw-bold mb-2">' +
                     termName + ' (' + startDisplayVal + ' → ' + endDisplayVal + ')</div>');

        var table = '<div class="table-responsive">' +
                    '<table class="table table-sm table-bordered mb-0 acb-week-table">' +
                    '<thead class="table-light">' +
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

            table += '<tr>' +
                     '<td>' + weekNo + '</td>' +
                     '<td>' + weekName + '</td>' +
                     '<td>' + wStart.format('YYYY-MM-DD') + '</td>' +
                     '<td>' + wEnd.format('YYYY-MM-DD') + '</td>' +
                     '<td>' +
                     '<select name="week_type[' + t + '][' + weekNo + ']" ' +
                     'class="form-control form-control-sm week-type-select">' +
                     weekTypeOptionsForRows +
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

    if ($('#weeksPreviewAll .acb-week-preview-card').length > 0) {
        $('#weeksPreviewAll').prepend(
            '<div class="acb-week-global-bulk border rounded p-2 mb-3 bg-light">' +
            buildWeekBulkBar('acb-week-bulk-bar--global mb-0') +
            '</div>'
        );
    }
});
  // ========= FORM SUBMIT =========
  var saveBtnHtml = $('#saveCalendarBtn').html();

  $('#calendarBuilderForm').on('submit', function (e) {
    e.preventDefault();
    var $form = $(this);

    $('.term-start-field, .term-end-field').each(function () {
      var $inp = $(this);
      $inp.prop('disabled', false);
      $inp.siblings('input[type="hidden"][data-cal-sync="1"]').remove();
      var displayValue = $inp.val();
      if (displayValue && displayValue !== '') {
        var storageDate = toStorageDate(displayValue);
        if (storageDate) {
          $('<input type="hidden" data-cal-sync="1">')
            .attr('name', $inp.attr('name'))
            .val(storageDate)
            .insertAfter($inp);
          $inp.prop('disabled', true);
        }
      }
    });

    $('#saveCalendarBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    $.ajax({
      url: $form.attr('action'),
      method: 'POST',
      data: $form.serialize(),
      dataType: 'json',
      success: function (res) {
        if (res.success) {
          toastr.success(res.msg || 'Saved.');
          if (res.redirect) {
            window.location.href = res.redirect;
          }
        } else {
          toastr.error(res.msg || 'Save failed.');
        }
      },
      error: function () {
        toastr.error('Network or server error.');
      },
      complete: function () {
        $('.term-start-field, .term-end-field').each(function () {
          $(this).prop('disabled', false);
          $(this).siblings('input[type="hidden"][data-cal-sync="1"]').remove();
        });
        $('#saveCalendarBtn').prop('disabled', false).html(saveBtnHtml);
      }
    });
  });

  // Initialise timeline / cut points (safe when rows already exist: table body is preserved)
  $(function () {
    $('[data-bs-toggle="tooltip"]').tooltip({ container: 'body', trigger: 'hover focus' });
    $('#btnBuildTimeline').trigger('click');
  });

})(jQuery);
</script>

<?= $this->endSection() ?>
