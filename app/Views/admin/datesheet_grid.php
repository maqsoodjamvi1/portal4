<?php
// Expected vars: $subjects, $dateRange, $existingMap, $exam
?>

<?php if (!empty($subjects) && !empty($dateRange)): ?>
  <style>
    /* Scroll container so sticky header has a scrolling ancestor */
    .ds-scroll {
      position: relative;
      max-height: 72vh;
      overflow-y: auto;
      overflow-x: hidden;
      border: 1px solid #e5e7eb;
      border-radius: .25rem;
      background: #fff;
    }

    /* Table basics */
    #datesheetTable {
      table-layout: fixed;
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
    }
    #datesheetTable.table-sm td,
    #datesheetTable.table-sm th {
      padding: .35rem .3rem;
      font-size: 12px;
      white-space: nowrap;
    }

    /* Sticky header row (day/date) */
    #datesheetTable thead th {
      position: sticky;
      top: 0;
      z-index: 3;
      background: #f8f9fa;          /* ensure no transparency over rows */
    }

    /* Sticky first column (subject) */
    .sticky-col {
      position: sticky;
      left: 0;
      z-index: 2;
      background: #f8f9fa;
    }

    /* Intersection cell needs highest z-index */
    #datesheetTable thead th.sticky-col { z-index: 5; }
    #datesheetTable tbody td.sticky-col { z-index: 4; }

    /* Subject column width */
    .subject-col { width: 120px; min-width: 120px; max-width: 140px; }
    .subject-name {
      overflow: hidden;
      text-overflow: ellipsis;
      font-weight: 600;
      font-size: 12px;
      line-height: 1;
    }

    /* ↓↓↓ Hard-enforced narrow date columns ↓↓↓ */
    .date-col {
      width: 30px;
      min-width: 30px;
      max-width: 30px;
      line-height: 1.1;
      text-align: center;
    }

    /* Marks column */
    .subject-meta {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: .25rem;
      margin-top: 0;
      white-space: nowrap;
    }
    .marks-inline {
      width: 46px;
      height: 20px;
      padding: .1rem .25rem;
      font-size: 11px;
      text-align: center;
      line-height: 1;
    }

    .vhead { line-height: 1; }
    .vhead .d { font-weight: 700; display: block; font-size: 12px; }
    .vhead .m { font-size: 10px; color: #6c757d; }

    .clickable-cell { cursor: pointer; transition: background .12s ease; }
    .clickable-cell:hover { background-color: #eef7ff !important; }
    .selected-cell { background-color: #28a745 !important; color: #fff !important; }
    .saving-cell {
      box-shadow: inset 0 0 0 2px #f59e0b;
    }

    .table td, .table th { border: 1px solid #e2e5e9; }
    .table thead th { border-bottom: 2px solid #d9dde3; }

    .small-muted { font-size: 12px; color: #6c757d; }
    .ds-stat-pill {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      border: 1px solid #d7dde5;
      border-radius: 999px;
      padding: .2rem .6rem;
      font-size: 12px;
      background: #fff;
      color: #334155;
    }
    .default-marks-box {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: .2rem;
      margin-top: .15rem;
      font-size: 10px;
      color: #eaf3ff;
    }
    .default-marks-input {
      width: 42px;
      height: 20px;
      text-align: center;
      font-size: 10px;
      padding: .05rem .15rem;
    }

    @media (max-width: 767.98px) {
      .subject-col { width: 108px; min-width: 108px; max-width: 124px; }
      .date-col    { width: 26px; min-width: 26px; max-width: 26px; }
      .vhead .d    { font-size: 12px; }
      .vhead .m    { font-size: 10px; }
      .marks-inline { width: 42px; }
    }

    /* Dense mode for larger working-day ranges */
    #datesheetTable.compact-fit td,
    #datesheetTable.compact-fit th {
      padding: .1rem .08rem;
      font-size: 11px;
    }
    #datesheetTable.compact-fit .vhead .m { font-size: 9px; }
    #datesheetTable.compact-fit .subject-col { width: 106px !important; min-width: 106px !important; max-width: 122px !important; }
    #datesheetTable.compact-fit .marks-inline { width: 42px; height: 18px; font-size: 10px; }
    #datesheetTable.compact-fit .default-marks-input { width: 38px; height: 18px; font-size: 10px; }
    #datesheetTable.compact-fit .vhead .d { font-size: 12px; }
    #datesheetTable.compact-fit .vhead .m { font-size: 10px; }

    #datesheetTable tbody td { vertical-align: middle; }
    #datesheetTable tbody tr { height: 26px; }
    .ds-save-status {
      position: fixed;
      right: 18px;
      bottom: 18px;
      z-index: 3000;
      min-width: 120px;
      padding: .35rem .65rem;
      border-radius: 999px;
      background: rgba(15, 23, 42, .92);
      color: #fff;
      font-size: 12px;
      text-align: center;
      box-shadow: 0 8px 20px rgba(0,0,0,.18);
      opacity: 0;
      transform: translateY(8px);
      pointer-events: none;
      transition: opacity .12s ease, transform .12s ease;
    }
    .ds-save-status.show {
      opacity: 1;
      transform: translateY(0);
    }
    .ds-save-status.error { background: rgba(185, 28, 28, .95); }
  </style>

  <div class="d-flex flex-wrap align-items-center justify-content-end mb-2" style="gap:.5rem;">
    <div class="d-flex flex-wrap" style="gap:.4rem;">
      <span class="ds-stat-pill"><strong><?= count($subjects) ?></strong> Subjects</span>
      <span class="ds-stat-pill"><strong><?= count($dateRange) ?></strong> Exam Days</span>
      <span class="ds-stat-pill"><span style="color:#16a34a;font-weight:700;">&#10003;</span> Selected</span>
      <span class="ds-stat-pill"><span style="color:#64748b;font-weight:700;">&#9675;</span> Not selected</span>
    </div>
  </div>

  <div class="ds-scroll">
    <table class="table table-bordered table-hover table-sm text-center" id="datesheetTable" data-exam-id="<?= (int)($exam->eid ?? 0) ?>">
      <thead class="table-light">
        <tr>
          <!-- Sticky top-left header -->
          <th class="sticky-col subject-col text-start align-middle bg-primary text-white">
            <span class="subject-name">Subject</span>
            <label class="default-marks-box mb-0" title="Set once to fill all rows">
              <input type="number" id="defaultMarksTop" class="form-control form-control-sm default-marks-input" min="1" max="999" maxlength="3" placeholder="50">
            </label>
          </th>

          <?php foreach ($dateRange as $d): $ts = strtotime($d['date']); ?>
            <th class="date-col text-center align-middle bg-light" title="<?= date('D, j M Y', $ts) ?>">
              <div class="vhead">
                <span class="d"><?= date('D', $ts) ?></span>
                <span class="m"><?= date('j M', $ts) ?></span>
              </div>
              <span class="d-none" data-date="<?= esc($d['date']) ?>"></span>
            </th>
          <?php endforeach; ?>

        </tr>
      </thead>

      <tbody>
        <?php foreach ($subjects as $subject):
          $subject_id = (int) $subject->subject_id;
          $sec_sub_id = (int) $subject->sec_sub_id;
          $marksSaved = $existingMap[$sec_sub_id]['total_marks'] ?? '';
          $savedDate  = $existingMap[$sec_sub_id]['exam_date']   ?? null;

          $cellTitle = trim(($subject->subject_name ?? '') .
                            (isset($subject->teacher_name) ? ' — Teacher: ' . $subject->teacher_name : ''));
        ?>
          <tr data-sec-sub-id="<?= $sec_sub_id ?>" data-subject-id="<?= $subject_id ?>">
            <td class="sticky-col subject-col text-start align-middle bg-light px-2" title="<?= esc($cellTitle) ?>">
              <?php
                $fullSubjectName = (string)($subject->subject_name ?? '');
                $dbShortSubjectName = trim((string)($subject->subject_short_name ?? ''));
                $displaySubject = $dbShortSubjectName !== '' ? $dbShortSubjectName : $fullSubjectName;
              ?>
              <div class="subject-meta">
                <input type="number"
                       name="total_marks[<?= $subject_id ?>]"
                       class="form-control form-control-sm marks-field marks-inline"
                       value="<?= esc($marksSaved) ?>"
                       min="1" max="999" maxlength="3" placeholder="50">
                <span class="subject-name" title="<?= esc($fullSubjectName) ?>"><?= esc($displaySubject) ?></span>
              </div>
              <input type="hidden" name="sec_sub_id[<?= $subject_id ?>]" value="<?= $sec_sub_id ?>">
            </td>

            <?php foreach ($dateRange as $d):
              $dateVal = $d['date'];
              $isSelected = ($savedDate && $savedDate === $dateVal);
              $cellClass = 'clickable-cell date-col';
              if ($isSelected) $cellClass .= ' selected-cell';
            ?>
              <td class="<?= $cellClass ?> align-middle" data-date="<?= esc($dateVal) ?>">
                <?= $isSelected ? '✓' : '○' ?>
              </td>
            <?php endforeach; ?>

          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div id="datesheetSaveStatus" class="ds-save-status">Saved</div>

  <script>
  (function () {
    const $table = $('#datesheetTable');
    const $scrollWrap = $table.closest('.ds-scroll');
    const $defaultMarksTop = $('#defaultMarksTop');
    const $saveStatus = $('#datesheetSaveStatus');
    const examId = $table.data('exam-id') || 0;
    let saveStatusTimer = null;

    <?php if (function_exists('csrf_token')): ?>
      const __csrfName = '<?= csrf_token() ?>';
      const __csrfHash = '<?= csrf_hash() ?>';
    <?php else: ?>
      const __csrfName = null, __csrfHash = null;
    <?php endif; ?>
    function withCsrf(payload) {
      if (__csrfName && __csrfHash) payload[__csrfName] = __csrfHash;
      return payload;
    }

    function toast(type, msg) {
      if (!window.toastr) return;
      toastr.options = {
        positionClass: 'toast-bottom-right',
        preventDuplicates: true,
        progressBar: true,
        timeOut: 1200
      };
      toastr[type](msg);
    }

    function saveStatus(message, isError) {
      clearTimeout(saveStatusTimer);
      $saveStatus
        .toggleClass('error', !!isError)
        .text(message)
        .addClass('show');
      saveStatusTimer = setTimeout(function () {
        $saveStatus.removeClass('show');
      }, isError ? 1800 : 900);
    }

    function applyFitToScreen() {
      if (!$table.length) return;
      const dateCount = $table.find('thead th.date-col').length;
      if (!dateCount) return;

      const wrapWidth = ($scrollWrap.innerWidth() || $table.parent().innerWidth() || window.innerWidth || 1200) - 6;
      let subjectW = 120;
      if (dateCount >= 12) subjectW = 112;
      if (dateCount >= 16) subjectW = 104;

      const remaining = Math.max(220, wrapWidth - subjectW);
      let dateW = Math.floor(remaining / dateCount);

      // Keep readable lower bounds while forcing fit without horizontal scroll
      if (dateW < 20) {
        subjectW = 100;
        dateW = Math.floor(Math.max(220, wrapWidth - subjectW) / dateCount);
      }
      if (dateW < 14) dateW = 14;
      if (dateW > 32) dateW = 32;

      const compact = (dateCount >= 12 || dateW <= 36);
      $table.toggleClass('compact-fit', compact);

      $table.find('.subject-col').css({
        width: subjectW + 'px',
        minWidth: subjectW + 'px',
        maxWidth: subjectW + 'px'
      });
      $table.find('.date-col').css({
        width: dateW + 'px',
        minWidth: dateW + 'px',
        maxWidth: dateW + 'px'
      });
    }

    // Click to pick date & save
    $table.on('click', 'tbody .date-col', function () {
      const $cell = $(this);
      const $row  = $cell.closest('tr');

      const subjectId = $row.data('subject-id');
      const secSubId  = $row.data('sec-sub-id');
      const dateVal   = $cell.data('date');
      const marks     = $row.find(`input[name="total_marks[${subjectId}]"]`).val();
      const clsSecId  = $('#cls_sec_id').val();

      if (!clsSecId || !secSubId || !dateVal) { toast('warning','Missing required data.'); return; }
      if (!marks) { toast('info','Enter marks before selecting a date.'); return; }

      const oldHtml = $cell.html();
      const $previousCell = $row.find('.date-col.selected-cell').not($cell);
      const previousHtml = $previousCell.html();
      $row.find('.date-col').removeClass('selected-cell').html('○');
      $cell.addClass('selected-cell saving-cell').html('✓');
      saveStatus('Saving...');

      $.ajax({
        url: "<?= base_url('admin/datesheet/save-single') ?>",
        method: "POST",
        data: withCsrf({
          cls_sec_id: clsSecId, sec_sub_id: secSubId, subject_id: subjectId,
          exam_id: examId,
          exam_date: dateVal, total_marks: marks
        })
      }).done(function (res) {
        if (res && res.success) {
          $cell.removeClass('saving-cell').html('✓');
          saveStatus('Saved');
          if (window.refreshDatesheetSummary) window.refreshDatesheetSummary();
        } else {
          saveStatus((res && res.message) || 'Failed to save.', true);
          $cell.removeClass('selected-cell saving-cell').html(oldHtml);
          if ($previousCell.length) $previousCell.addClass('selected-cell').html(previousHtml || '✓');
        }
      }).fail(function () {
        saveStatus('Server error while saving.', true);
        $cell.removeClass('selected-cell saving-cell').html(oldHtml);
        if ($previousCell.length) $previousCell.addClass('selected-cell').html(previousHtml || '✓');
      });
    });

    // Enforce max 3 digits for marks
    $table.on('input', '.marks-field', function () {
      let v = String($(this).val() || '').replace(/\D/g, '');
      if (v.length > 3) v = v.slice(0, 3);
      $(this).val(v);
    });

    // Top default marks: auto-fill all rows, still editable per row later
    $defaultMarksTop.on('input', function () {
      let v = String($(this).val() || '').replace(/\D/g, '');
      if (v.length > 3) v = v.slice(0, 3);
      $(this).val(v);
      if (v === '') return;
      $table.find('.marks-field').val(v);
    });

    // Persist marks change (requires a picked date)
    $table.on('change', '.marks-field', function () {
      const $row      = $(this).closest('tr');
      const subjectId = $row.data('subject-id');
      const secSubId  = $row.data('sec-sub-id');
      const clsSecId  = $('#cls_sec_id').val();
      const marks     = $(this).val();
      const $picked   = $row.find('.date-col.selected-cell');
      const dateVal   = $picked.data('date');

      if (!clsSecId || !secSubId || !marks) return;
      if (!dateVal) { toast('warning','Please pick a date first.'); return; }

      $.ajax({
        url: "<?= base_url('admin/datesheet/save-single') ?>",
        method: "POST",
        data: withCsrf({
          cls_sec_id: clsSecId, sec_sub_id: secSubId, subject_id: subjectId,
          exam_id: examId,
          exam_date: dateVal, total_marks: marks
        })
      }).done(function (res) {
        if (res && res.success) {
          saveStatus('Marks saved');
          if (window.refreshDatesheetSummary) window.refreshDatesheetSummary();
        } else {
          saveStatus((res && res.message) || 'Failed to update marks.', true);
        }
      }).fail(function () {
        saveStatus('Server error while updating marks.', true);
      });
    });

    applyFitToScreen();
    $(window).off('resize.dsfit').on('resize.dsfit', function () {
      applyFitToScreen();
    });
  })();
  </script>

<?php else: ?>
  <div class="alert alert-info mb-0">
    <i class="fas fa-info-circle me-1"></i>
    No subjects or exam days to display.
  </div>
<?php endif; ?>
