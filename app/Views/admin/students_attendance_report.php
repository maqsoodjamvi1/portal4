<?php $uiNeedsDataTables = false; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<?php helper('url'); ?>

<?= view('components/page_header', [
    'title' => 'Student Attendance Report',
    'icon' => 'fas fa-clipboard-list',
    'subtitle' => 'Card view by class and section.',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Student Attendance Report', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid">

    <!-- Filters -->
    <div class="card sms-card card-primary card-outline shadow-sm mb-2">
      <div class="card-body py-2">
        <div class="row align-items-end">
          <input type="hidden" id="campus_id" value="<?= (int)$campusId ?>">

          <div class="form-group col-sm-2 mb-2">
            <div class="form-check mb-0">
              <input type="checkbox" id="show_absent_today" class="form-check-input">
              <label for="show_absent_today" class="form-check-label">Show Today Absentees</label>
            </div>
          </div>

          <div class="form-group col-sm-2 mb-2">
            <div class="form-check mb-0">
              <input type="checkbox" id="family_wise" class="form-check-input dependent-filter">
              <label for="family_wise" class="form-check-label">Family-wise</label>
            </div>
          </div>

          <div class="form-group col-sm-2 mb-2">
            <label class="mb-1">Class</label>
            <select id="class_id" class="form-control form-control-sm dependent-filter">
              <option value="0">All</option>
              <?php foreach ($classes as $c): ?>
                <option value="<?= (int)$c->class_id ?>"><?= esc($c->class_name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group col-sm-2 mb-2">
            <label class="mb-1">Section</label>
            <select id="cls_sec_id" class="form-control form-control-sm dependent-filter">
              <option value="0">All</option>
              <?php foreach ($classSections as $cs): ?>
                <option value="<?= (int)$cs->cls_sec_id ?>">
                  <?= esc(($cs->class_name ?? 'Class') . ' (' . ($cs->section_name ?? 'Section') . ')') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group col-sm-2 mb-2">
            <label class="mb-1">Search Student</label>
            <input type="text" id="search_student" class="form-control form-control-sm dependent-filter" placeholder="Student name">
          </div>

          <div class="form-group col-sm-2 mb-2">
            <label class="mb-1">Search Parent</label>
            <input type="text" id="search_parent" class="form-control form-control-sm dependent-filter" placeholder="Father name">
          </div>

          <div class="form-group col-sm-2 mb-2">
            <label class="mb-1">Family (Parent ID)</label>
            <input type="number" id="parent_id" class="form-control form-control-sm dependent-filter" placeholder="e.g., 123">
          </div>

          <div class="form-group col-sm-4 ms-auto text-end mb-2">
            <button id="btnExport" class="btn btn-sm btn-outline-secondary me-1">Export CSV</button>
            <button id="btnPrint" class="btn btn-sm btn-primary">Print</button>
          </div>
        </div>
      </div>
    </div>

    <!-- PRINT HEADER (only visible on print) -->
    <div id="printHeader" class="print-only" style="display:none;">
      <div class="text-center" style="font-size:1rem; font-weight:600;">Student Attendance – Report (Card View)</div>
      <div class="d-flex justify-content-between" style="font-size:.9rem;">
        <div>Print Date: <span id="printDateVal">—</span></div>
        <div>Attendance Date: <span id="attendanceDateVal">—</span></div>
      </div>
      <hr style="margin:.4rem 0;border-top:1px solid #ccc;">
    </div>

    <!-- Results -->
    <div id="report_container" class="position-relative" style="min-height:120px;">
      <div id="loader" class="overlay text-center" style="display:none;">
        <i class="fas fa-2x fa-sync-alt fa-spin"></i>
      </div>
      <!-- cards injected here -->
    </div>

  </div>
</section>

<script>
(function () {
  const base = "<?= rtrim(base_url(), '/') ?>";

  // Elements
  const $campus        = $('#campus_id');
  const $class         = $('#class_id');
  const $section       = $('#cls_sec_id');
  const $parentId      = $('#parent_id');
  const $searchStudent = $('#search_student');
  const $searchParent  = $('#search_parent');
  const $familyWise    = $('#family_wise');
  const $absentOnly    = $('#show_absent_today');

  const $report        = $('#report_container');
  const $loader        = $('#loader');

  function toggleLoader(show) { $loader.css('display', show ? 'block' : 'none'); }

  function setDependentFiltersDisabled(disabled) {
    const $deps = $().add($class).add($section).add($parentId).add($searchStudent).add($searchParent);
    $deps.prop('disabled', disabled);
    $deps.closest('.form-group').toggleClass('disabled-filter', disabled);
  }

  function buildPayload(page) {
    const absentOnly = $absentOnly.is(':checked') ? 1 : 0;
    const fam        = $familyWise.is(':checked') ? 1 : 0;
    return {
      campus_id: $campus.val(),
      class_id:  $class.val(),
      cls_sec_id:$section.val(),
      parent_id: $parentId.val(),
      search_student: $searchStudent.val(),
      search_parent:  $searchParent.val(),
      family_wise:    fam,
      show_absent_today: absentOnly,
      page: page,
      per_page: (fam || absentOnly) ? 50 : 20
    };
  }

  // ————— Post-render tweaks —————
  function centerTermBadges(scope) {
    // Find any "Term:" badges and center them
    (scope || document).querySelectorAll('.card .badge.text-bg-secondary').forEach(function (el) {
      if (!/^\s*Term:/i.test(el.textContent)) return;
      const parent = el.parentElement;
      if (parent) {
        parent.style.textAlign = 'center';
        el.classList.add('term-badge');
        el.style.display = 'inline-block';
      }
    });
  }

  function setPrintHeaderDates() {
    // Print date: now
    const now = new Date();
    const dd = String(now.getDate()).padStart(2,'0');
    const mm = String(now.getMonth()+1).padStart(2,'0');
    const yyyy = now.getFullYear();
    $('#printDateVal').text(`${dd}-${mm}-${yyyy}`);

    // Attendance date:
    if ($absentOnly.is(':checked')) {
      $('#attendanceDateVal').text(`${dd}-${mm}-${yyyy}`); // today
    } else {
      // Try to read the first term badge
      const badge = document.querySelector('.term-badge');
      if (badge) {
        // Expect content like "Term: 2024-04-01 → 2024-06-30" or similar
        $('#attendanceDateVal').text(badge.textContent.replace(/^Term:\s*/i,''));
      } else {
        $('#attendanceDateVal').text('—');
      }
    }
  }

  function exportCSV() {
    const rows = [];
    // header
    rows.push([
      'Student Name','Student ID','Class-Section','Father Name','Parent ID','Contact',
      'Absentees Count','Leaves Count','Late Coming Count','Early Left Count',
      'Absentees','Leaves','Late Coming','Early Left'
    ]);

    // Walk each card
    document.querySelectorAll('.students-card-list .card').forEach(function(card){
      const header = card.querySelector('.card-body .flex-grow-1');
      if (!header) return;

      // Parse compact header line:
      // e.g. "Ali Khan (ID: 123) — Class (Sec)"
      let studentName = '';
      let studentId   = '';
      let clsLabel    = '';
      const hdrLine = header.querySelector('.fw-semibold');
      if (hdrLine) {
        const text = hdrLine.textContent.replace(/\s+/g,' ').trim();
        // Try to extract "(ID: NNN)"
        const idMatch = text.match(/\(ID:\s*([0-9]+)\)/i);
        studentId = idMatch ? idMatch[1] : '';
        // Split by "—"
        const parts = text.split('—');
        studentName = (parts[0] || '').replace(/\(ID:\s*[0-9]+\)/i,'').trim();
        clsLabel    = (parts[1] || '').trim();
      }

      let fatherName = '';
      let parentId   = '';
      let contact    = '';
      const subLine = header.querySelector('.small');
      if (subLine) {
        const s = subLine.textContent.replace(/\s+/g,' ').trim();
        // Father: NAME (PID: 12) • Contact: 03xx...
        const fMatch = s.match(/Father:\s*(.*?)\s*\(PID:\s*([0-9]+)\)/i);
        if (fMatch) { fatherName = fMatch[1].trim(); parentId = fMatch[2].trim(); }
        const cMatch = s.match(/Contact:\s*(.*)$/i);
        if (cMatch) { contact = cMatch[1].trim(); }
      }

      function parseSection(label) {
        const box = Array.from(card.querySelectorAll('.col-12.mb-2'))
          .find(x => x.querySelector('.mb-1') && x.querySelector('.mb-1').textContent.trim().toLowerCase() === label.toLowerCase());
        if (!box) return {count:0, list:[]};
        const chips = box.querySelectorAll('.d-flex.flex-wrap .badge');
        const items = Array.from(chips).map(x => x.textContent.trim());
        return {count: items.length, list: items};
      }

      const abs  = parseSection('Absentees');
      const lev  = parseSection('Leave');
      const late = parseSection('Late Coming');
      const el   = parseSection('Early Left');

      rows.push([
        studentName, studentId, clsLabel, fatherName, parentId, contact,
        abs.count, lev.count, late.count, el.count,
        abs.list.join('; '), lev.list.join('; '), late.list.join('; '), el.list.join('; ')
      ]);
    });

    const csv = rows.map(r => r.map(v => `"${String(v).replace(/"/g,'""')}"`).join(',')).join('\r\n');
    const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
    const a = document.createElement('a');
    const ts = new Date().toISOString().slice(0,19).replace(/[:T]/g,'-');
    a.href = URL.createObjectURL(blob);
    a.download = 'attendance-cards-' + ts + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  }

  function loadCards(page = 1, append = false) {
    toggleLoader(true);
    const payload = buildPayload(page);

    $.post(base + "/admin/students_attendance/report_cards", payload, function (html) {
      if (append) $report.append(html);
      else        $report.html(html);
      toggleLoader(false);

      // Post-render adjustments
      centerTermBadges(document);

      // Wire up "Load more" (server renders the button)
      $('#btnLoadMore').off('click').on('click', function () {
        const next = parseInt($(this).data('next-page') || '2', 10);
        $(this).closest('div').remove();
        loadCards(next, true);
      });
    }).fail(function (xhr) {
      toggleLoader(false);
      $report.html('<div class="alert alert-danger m-2">Error: ' + xhr.status + ' ' + xhr.statusText + '</div>');
    });
  }

  // Initial state
  setDependentFiltersDisabled($absentOnly.is(':checked'));
  loadCards(1, false);

  // Actions
  $('#btnFilter').on('click', () => loadCards(1,false));
  $('#btnReset').on('click', function(){
    $class.val('0'); $section.val('0'); $parentId.val('');
    $searchStudent.val(''); $searchParent.val('');
    $familyWise.prop('checked', false);
    $absentOnly.prop('checked', false);
    setDependentFiltersDisabled(false);
    loadCards(1,false);
  });

  $absentOnly.on('change', function(){
    setDependentFiltersDisabled(this.checked);
    loadCards(1,false);
  });
  $familyWise.on('change', function(){ loadCards(1,false); });

  // Export & Print
  $('#btnExport').on('click', exportCSV);
  $('#btnPrint').on('click', function(){
    setPrintHeaderDates();
    window.print();
  });

  // Ensure header values are fresh if user triggers Ctrl+P
  window.addEventListener('beforeprint', setPrintHeaderDates);
})();
</script>

<style>
/* ===== Screen (compact, crisp) ===== */

/* Compact cards & nice hover */
.students-card-list .card { transition: box-shadow .15s ease; }
.students-card-list .card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.08); }

/* Keep visuals tight */
.card-body.p-2 { padding: .5rem !important; }

.badge { font-weight: 500; }

/* Disabled filters dimming */
.disabled-filter { opacity:.6; pointer-events:none; }

/* Prevent card split */
.student-card,
.students-card-list .card {
  break-inside: avoid;
  page-break-inside: avoid;
}

/* Centered term badge (JS also reinforces) */
.term-badge { display:inline-block; }
.students-card-list .card .term-badge { margin: 0 auto; }

/* Loader overlay */
.overlay {
  position:absolute; inset:0; background:rgba(255,255,255,.6);
  display:flex; align-items:center; justify-content:center; z-index:5;
}


/* ===== Print (clear borders, spacing, no chrome) ===== */
@media print {
  /* Hide UI chrome; show print header */
  .no-print, .content-header, .card.card-primary.card-outline .card-body .row,
  #btnFilter, #btnReset, #btnExport, #btnPrint,
  .breadcrumb { display: none !important; }

  .print-only { display:block !important; }

  /* White background everywhere */
  html, body, .wrapper, .content-wrapper, .content, .container-fluid {
    background: #fff !important;
  }

  /* Keep some gutter between columns on paper */
  .students-card-list .col-md-6,
  .students-card-list .col-xl-4 {
    padding-left: 2mm !important;
    padding-right: 2mm !important;
    page-break-inside: avoid;
    break-inside: avoid;
  }

  /* Strong, print-friendly card borders + spacing */
  .students-card-list .card {
    background: #fff !important;
    border: 0.4mm solid #000 !important;   /* darker so it prints clearly */
    box-shadow: none !important;           /* shadows often don't print well */
    margin-bottom: 4mm !important;         /* space between cards vertically */
    page-break-inside: avoid;
    break-inside: avoid;
  }

  /* Roomier inner padding improves readability on paper */
  .students-card-list .card .card-body {
    padding: 5mm !important;
  }

  /* Visible divider on paper */
  .students-card-list .card hr {
    border-top: 0.3mm dashed #444 !important;
    margin: 3mm 0 !important;
  }

  /* Badges remain visible in grayscale */
  .students-card-list .badge {
    border: 0.2mm solid #222 !important;
    background: #f2f2f2 !important;
    color: #000 !important;
  }

  /* Center the Term tag reliably on print */
  .students-card-list .card .term-badge {
    display: inline-block !important;
    margin: 0 auto !important;
  }

  /* Never split a card across pages */
  .students-card-list .card,
  .students-card-list .col-md-6,
  .students-card-list .col-xl-4 {
    page-break-inside: avoid;
    break-inside: avoid;
  }

  /* Slightly tighter page margins */
  @page { margin: 12mm; }

  /* Ensure colors/tints print (Chrome, etc.) */
  * {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
}
</style>


<?= $this->endSection() ?>
