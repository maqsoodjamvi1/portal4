<?php
// Helper to plain-text a diary cell
$renderPlain = static function($html) {
    $plain = trim(preg_replace(
        '/\s+/u',
        ' ',
        html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8')
    ));
    return esc($plain);
};

// Collect unique classes & subjects for filters
$allClasses  = [];   // [id => label]
$allSubjects = [];   // [label => true]

// Build a safe ID for a class (prefer cls_sec_id if present)
$mkClassId = static function($row) {
    if (!empty($row['cls_sec_id'])) return 'c'.$row['cls_sec_id'];
    $name = $row['class_full_name'] ?? $row['class'] ?? 'class';
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    return 'c'.trim($slug, '-');
};

foreach ($data as $row) {
    $classId   = $mkClassId($row);
    $className = $row['class_full_name'] ?? $row['class'] ?? 'Class';
    $allClasses[$classId] = $className;

    foreach ($row['result'] as $subject => $_) {
        if (is_string($subject) && $subject !== '') {
            $allSubjects[$subject] = true;
        }
    }
}
ksort($allClasses);
$allSubjects = array_keys($allSubjects);
sort($allSubjects);
?>

<?php if (empty($data)): ?>
  <div class="alert alert-info mb-0">No diary entries for this week.</div>
<?php else: ?>

  <!-- Toolbar (hidden on print) -->
  <div class="no-print mb-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
      <h4 class="mb-0">Weekly Diary</h4>
      <div class="btn-group">
        <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
          <i class="fas fa-print"></i> Print
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="card p-2">
      <div class="row">
        <!-- Class filters -->
        <div class="col-md-6">
          <div class="mb-1 d-flex justify-content-between align-items-center">
            <strong>Classes</strong>
            <div>
              <button type="button" class="btn btn-xs btn-link p-0 mr-2" onclick="checkAll('class-filter', true)">Select all</button>
              <button type="button" class="btn btn-xs btn-link p-0" onclick="checkAll('class-filter', false)">Clear</button>
            </div>
          </div>
          <div class="filters-wrap" id="classFilters">
            <?php foreach ($allClasses as $cid => $cname): ?>
              <label class="mr-3 mb-1">
                <input type="checkbox" class="class-filter" value="<?= esc($cid) ?>" checked>
                <span><?= esc($cname) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Subject filters -->
        <div class="col-md-6">
          <div class="mb-1 d-flex justify-content-between align-items-center">
            <strong>Subjects</strong>
            <div>
              <button type="button" class="btn btn-xs btn-link p-0 mr-2" onclick="checkAll('subject-filter', true)">Select all</button>
              <button type="button" class="btn btn-xs btn-link p-0" onclick="checkAll('subject-filter', false)">Clear</button>
            </div>
          </div>
          <div class="filters-wrap" id="subjectFilters">
            <?php foreach ($allSubjects as $s): ?>
              <label class="mr-3 mb-1">
                <input type="checkbox" class="subject-filter" value="<?= esc($s) ?>" checked>
                <span><?= esc($s) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Diaries container -->
  <div id="diaryContainer">

    <?php foreach ($data as $i => $row): ?>
      <?php
        // Build active dates (only days with any content)
        $hasContentByDate = [];
        foreach ($row['result'] as $subject => $byDate) {
            if (!is_array($byDate)) continue;
            foreach ($byDate as $d => $detail) {
                $plain = trim(preg_replace(
                    '/\s+/u', ' ',
                    html_entity_decode(strip_tags((string)$detail), ENT_QUOTES | ENT_HTML5, 'UTF-8')
                ));
                if ($plain !== '') $hasContentByDate[$d] = true;
            }
        }

        $activeDates = [];
        foreach ($row['week_dates'] as $d) {
            if (isset($hasContentByDate[$d])) $activeDates[] = $d;
        }
        if (empty($activeDates)) {
            // Skip class entirely if no diary exists
            continue;
        }

        $weekStart   = reset($row['week_dates']);
        $weekEnd     = end($row['week_dates']);
        $weekName    = $row['week_name'] ?? ''; // if you store explicit week name
        $classFull   = $row['class_full_name'] ?? $row['class'] ?? 'Class';
        $classId     = $mkClassId($row);
      ?>

      <div class="diary-class-block" data-class-id="<?= esc($classId) ?>" data-class-name="<?= esc($classFull) ?>">

        <table id="diary-table-<?= $i ?>" class="table table-bordered table-sm diary-table mb-4">
          <thead>
            <!-- Row 1: Weekly Diary + Class heading -->
            <tr class="class-heading">
              <th class="text-center" colspan="<?= 1 + count($activeDates) ?>">
                <div style="font-weight:800;font-size:18px;">Weekly Diary</div>
                <div style="font-weight:600;">
                  <?= esc($classFull) ?>
                </div>
                <?php if ($weekName || ($weekStart && $weekEnd)): ?>
                  <div style="font-size:12px;color:#6b7280;margin-top:2px;">
                    <?php if ($weekName): ?>
                      <?= esc($weekName) ?>
                    <?php endif; ?>
                    <?php if ($weekStart && $weekEnd): ?>
                      <?= $weekName ? ' — ' : '' ?>
                      <?= esc(date('d-m-Y', strtotime($weekStart))) ?>
                      &rarr;
                      <?= esc(date('d-m-Y', strtotime($weekEnd))) ?>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </th>
            </tr>

            <!-- Row 2: Date + Day headings -->
            <tr class="date-day-row">
              <th class="subj-col text-left">Subject</th>
              <?php foreach ($activeDates as $d): ?>
                <th class="text-center dd-cell">
                  <?= esc(date('d-m-Y (l)', strtotime($d))) ?>
                </th>
              <?php endforeach; ?>
            </tr>
          </thead>

          <tbody>
            <?php foreach ($row['result'] as $subject => $byDate): ?>
              <tr data-subject="<?= esc($subject) ?>">
                <td class="subj-col"><?= esc($subject) ?></td>
                <?php foreach ($activeDates as $d): ?>
                  <td class="dd-cell">
                    <?php if (isset($byDate[$d]) && trim(strip_tags((string)$byDate[$d])) !== ''): ?>
                      <?= $renderPlain($byDate[$d]) ?>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

      </div>
    <?php endforeach; ?>

  </div><!-- /#diaryContainer -->

<?php endif; ?>

<style>
  .filters-wrap {
    max-height: 180px;
    overflow:auto;
    border:1px dashed #e5e5e5;
    padding:.25rem .5rem;
  }
  .filters-wrap label { cursor: pointer; font-weight: 400; }
  .btn-xs { font-size: .78rem; }

  /* Diary table layout */
  .diary-table {
    border-collapse: collapse !important;
    table-layout: auto !important;
    width: 100%;
  }
  .diary-table th,
  .diary-table td {
    padding: .24rem .34rem !important;
    line-height: 1.28 !important;
    vertical-align: middle !important; /* center vertically */
  }

  /* Class heading row */
  .diary-table .class-heading th {
    text-align: center !important;
    font-weight: 700;
    background: #f8f9fa;
    padding: .35rem .35rem !important;
    line-height: 1.22 !important;
  }

  /* Subject column */
  .diary-table .subj-col {
    width: 1% !important;
    white-space: nowrap !important;
    text-align: left !important;
  }

  /* Header centered, body left-aligned (for Urdu as well) */
  .diary-table thead .dd-cell {
    text-align: center !important;
  }
  .diary-table tbody .dd-cell {
    text-align: left !important;
  }

  /* Strip extra spacing inside cells */
  .diary-table td * { margin: 0; }

  /* ---------- PRINT CLEANUP ---------- */
  @media print {
    html, body,
    .wrapper, .content-wrapper, .content, .container-fluid,
    .card, .card-body, .card-header,
    .diary-class-block {
      background: #fff !important;
      box-shadow: none !important;
    }

    .no-print,
    .nav-tabs,
    .card-header,
    .content-header,
    .breadcrumb,
    label[for="term_id"], #term_id,
    label[for="term_weeks"], #term_weeks,
    #viewBtn {
      display: none !important;
    }

    .diary-table .class-heading th { background: #fff !important; }

    .diary-class-block {
      page-break-after: always;
      break-inside: avoid;
      page-break-inside: avoid;
    }
    .diary-class-block.no-break-after {
      page-break-after: auto !important;
    }
  }
</style>

<script>
  // Check/Uncheck all helpers
  function checkAll(cls, checked) {
    document.querySelectorAll('input.'+cls).forEach(cb => { cb.checked = !!checked; });
    applyFilters();
  }

  // Apply filters (classes + subjects). Hide blocks/rows accordingly.
  function applyFilters() {
    const selectedClasses = Array.from(document.querySelectorAll('input.class-filter:checked')).map(i => i.value);
    const selectedSubjects = Array.from(document.querySelectorAll('input.subject-filter:checked')).map(i => i.value);
    const blocks = Array.from(document.querySelectorAll('.diary-class-block'));

    blocks.forEach(block => {
      const classId = block.getAttribute('data-class-id') || '';
      const table = block.querySelector('table');
      let anyRowVisible = false;

      // Class visibility
      const classMatch = selectedClasses.length ? selectedClasses.includes(classId) : true;
      if (!classMatch) {
        block.style.display = 'none';
        block.classList.remove('no-break-after');
        return;
      }

      // Subject rows visibility
      Array.from(table.querySelectorAll('tbody tr')).forEach(tr => {
        const subj = tr.getAttribute('data-subject') || '';
        const rowMatch = !selectedSubjects.length || selectedSubjects.includes(subj);
        tr.style.display = rowMatch ? '' : 'none';
        if (rowMatch) anyRowVisible = true;
      });

      // If class has no visible subject rows, hide whole block
      if (!anyRowVisible) {
        block.style.display = 'none';
        block.classList.remove('no-break-after');
      } else {
        block.style.display = '';
      }
    });

    markLastVisibleBlock();
  }

  // Mark last visible block to avoid page-break-after there
  function markLastVisibleBlock() {
    const blocks = Array.from(document.querySelectorAll('.diary-class-block'));
    let lastVisible = null;
    blocks.forEach(b => {
      b.classList.remove('no-break-after');
      if (b.style.display !== 'none') lastVisible = b;
    });
    if (lastVisible) lastVisible.classList.add('no-break-after');
  }

  // Bind filter change
  document.addEventListener('change', function(e){
    if (e.target.classList.contains('class-filter') || e.target.classList.contains('subject-filter')) {
      applyFilters();
    }
  });

  // Ensure no blank last page when user prints via browser
  if (window.matchMedia) {
    const mq = window.matchMedia('print');
    mq.addListener(function(mql) {
      if (mql.matches) { markLastVisibleBlock(); }
    });
  }
  window.onbeforeprint = markLastVisibleBlock;

  // Initial mark / filter
  document.addEventListener('DOMContentLoaded', function(){
    applyFilters();
  });

  // CSV export helpers (unchanged)
  function exportDiaryCSV(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const rows = [];
    table.querySelectorAll('tr').forEach(tr => {
      if (tr.style.display === 'none') return; // respect subject filter
      const cells = tr.querySelectorAll('th, td');
      const row = [];
      cells.forEach(td => {
        if (td.offsetParent === null) return; // skip hidden
        let t = (td.innerText || '').replace(/\s+/g, ' ').trim();
        t = '"' + t.replace(/"/g, '""') + '"';
        row.push(t);
      });
      if (row.length) rows.push(row.join(','));
    });

    const csv = rows.join('\r\n');
    const blob = new Blob([csv], {type: 'text/csv;charset=utf-8;'});
    const a = document.createElement('a');
    const ts = new Date().toISOString().slice(0,19).replace(/[:T]/g,'-');
    a.href = URL.createObjectURL(blob);
    a.download = tableId + '-' + ts + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  }

  function exportAllDiariesCSV() {
    const tables = document.querySelectorAll('.diary-table');
    if (!tables.length) return;

    const blocks = [];
    tables.forEach((table) => {
      const block = table.closest('.diary-class-block');
      if (block && block.style.display === 'none') return;

      const rows = [];
      table.querySelectorAll('tr').forEach(tr => {
        if (tr.style.display === 'none') return;
        const cells = tr.querySelectorAll('th, td');
        const row = [];
        cells.forEach(td => {
          if (td.offsetParent === null) return;
          let t = (td.innerText || '').replace(/\s+/g, ' ').trim();
          t = '"' + t.replace(/"/g, '""') + '"';
          row.push(t);
        });
        if (row.length) rows.push(row.join(','));
      });
      if (rows.length) blocks.push(rows.join('\r\n'));
    });

    if (!blocks.length) return;

    const csv = blocks.join('\r\n\r\n');
    const blob = new Blob([csv], {type: 'text/csv;charset=utf-8;'});
    const a = document.createElement('a');
    const ts = new Date().toISOString().slice(0,19).replace(/[:T]/g,'-');
    a.href = URL.createObjectURL(blob);
    a.download = 'class-diaries-' + ts + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  }
</script>
