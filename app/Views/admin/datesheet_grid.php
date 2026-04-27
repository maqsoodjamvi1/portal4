<?php
// Expected vars: $subjects, $dateRange, $existingMap, $exam
?>

<?php if (!empty($subjects) && !empty($dateRange)): ?>
  <style>
    /* Scroll container so sticky header has a scrolling ancestor */
    .ds-scroll {
      position: relative;
      max-height: 70vh;     /* adjust if you want more/less visible */
      overflow: auto;       /* both axes so sticky works reliably */
      border: 1px solid #e5e7eb;
      border-radius: .25rem;
    }

    /* Table basics */
    #datesheetTable {
      table-layout: fixed;          /* enforce fixed widths */
      width: max-content;           /* allow narrow columns without stretching full width */
      border-collapse: separate;
      border-spacing: 0;
    }
    #datesheetTable.table-sm td,
    #datesheetTable.table-sm th {
      padding: .22rem .12rem;
      font-size: 12px;
      white-space: nowrap;          /* prevent width blow-up on headers/cells */
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
    .subject-col {
      min-width: 100px;
      max-width: 120px;
    }
    .subject-name {
      overflow: hidden;
      text-overflow: ellipsis;
      font-weight: 600;
    }

    /* ↓↓↓ Hard-enforced narrow date columns ↓↓↓ */
    .date-col {
      width: 24px !important;
      min-width: 24px !important;
      max-width: 24px !important;
      line-height: 1;
      text-align: center;
    }

    /* Marks column */
    .marks-col {
      width: 68px !important;
      min-width: 68px !important;
      max-width: 86px !important;
      background: #f8f9fa;
    }

    .vhead { writing-mode: vertical-rl; transform: rotate(180deg); line-height: 1; }
    .vhead .d { font-weight: 600; }
    .vhead .m { font-size: 10px; color: #6c757d; }

    .clickable-cell { cursor: pointer; transition: background .12s ease; }
    .clickable-cell:hover { background-color: #eef7ff !important; }
    .selected-cell { background-color: #28a745 !important; color: #fff !important; }

    .table td, .table th { border: 1px solid #e2e5e9; }
    .table thead th { border-bottom: 2px solid #d9dde3; }

    .small-muted { font-size: 11px; color: #6c757d; }

    @media (max-width: 767.98px) {
      .subject-col { min-width: 92px; max-width: 112px; }
      .date-col    { width: 22px !important; min-width: 22px !important; max-width: 22px !important; }
      .vhead .m    { font-size: 9px; }
    }
  </style>

  <div class="d-flex align-items-center justify-content-between mb-2">
    <div class="small-muted">
      <i class="far fa-calendar-check mr-1"></i>
      <?= esc($exam->exam_name ?? 'Exam') ?> — only enabled exam days
    </div>

  </div>

  <div class="ds-scroll">
    <table class="table table-bordered table-hover table-sm text-center" id="datesheetTable" data-exam-id="<?= (int)($exam->eid ?? 0) ?>">
      <thead class="thead-light">
        <tr>
          <!-- Sticky top-left header -->
          <th class="sticky-col subject-col text-left align-middle bg-primary text-white">
            <span class="subject-name">Subject</span>
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

          <th class="text-center align-middle marks-col bg-light">
            <span class="font-weight-bold">Marks</span>

      
      <input type="number" id="default_marks" class="form-control form-control-sm" placeholder="e.g.50" min="1" max="200" style="width: 82px;">
    
          </th>
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
            <td class="sticky-col subject-col text-left align-middle bg-light px-2" title="<?= esc($cellTitle) ?>">
              <span class="subject-name"><?= esc($subject->subject_name) ?></span>
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

            <td class="marks-col align-middle">
              <input type="number"
                     name="total_marks[<?= $subject_id ?>]"
                     class="form-control form-control-sm marks-field"
                     value="<?= esc($marksSaved) ?>"
                     min="1" max="200" placeholder="—">
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <script>
  (function () {
    const $table = $('#datesheetTable');

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

    // Click to pick date & save
    $table.on('click', '.date-col', function () {
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
      $row.find('.date-col').removeClass('selected-cell').html('○');
      $cell.addClass('selected-cell').html('<span class="spinner-border spinner-border-sm"></span>');

      $.ajax({
        url: "<?= base_url('admin/datesheet/save-single') ?>",
        method: "POST",
        data: withCsrf({
          cls_sec_id: clsSecId, sec_sub_id: secSubId, subject_id: subjectId,
          exam_date: dateVal, total_marks: marks
        })
      }).done(function (res) {
        if (res && res.success) {
          toast('success', res.message || 'Saved.');
          $cell.html('✓');
          if (window.refreshDatesheetSummary) window.refreshDatesheetSummary();
        } else {
          toast('error', (res && res.message) || 'Failed to save.');
          $cell.removeClass('selected-cell').html(oldHtml);
        }
      }).fail(function () {
        toast('error', 'Server error while saving.');
        $cell.removeClass('selected-cell').html(oldHtml);
      });
    });

    // Default marks applies to all rows (doesn't save until date exists)
    $('#default_marks').on('input', function () {
      const v = $(this).val();
      if (v !== '') $('.marks-field').val(v).trigger('change');
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
          exam_date: dateVal, total_marks: marks
        })
      }).done(function (res) {
        if (res && res.success) {
          toast('success', 'Marks updated.');
          if (window.refreshDatesheetSummary) window.refreshDatesheetSummary();
        } else {
          toast('error', (res && res.message) || 'Failed to update marks.');
        }
      }).fail(function () {
        toast('error', 'Server error while updating marks.');
      });
    });
  })();
  </script>

<?php else: ?>
  <div class="alert alert-info mb-0">
    <i class="fas fa-info-circle mr-1"></i>
    No subjects or exam days to display.
  </div>
<?php endif; ?>
