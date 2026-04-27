<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-book"></i> Vocabulary Report</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Vocabulary Report</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">

  <!-- ====================== FILTER CARD (NO PRINT / NO PDF) ====================== -->
  <div class="card card-primary no-print">
    <div class="card-header">
      <h3 class="card-title">Filter By Class / Subject / Topics</h3>
    </div>

    <div class="card-body">
      <div class="form-row">
        <!-- Class -->
        <div class="form-group col-md-4">
          <label for="class_id">Class</label>
          <select id="class_id" class="form-control">
            <option value="">-- Select Class --</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?= $c->class_id ?>"><?= esc($c->class_name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Subject -->
        <div class="form-group col-md-4">
          <label for="subject_id">Subject</label>
          <select id="subject_id" class="form-control">
            <option value="">-- Select Subject --</option>
          </select>
        </div>

        <!-- Topics as checkboxes -->
        <div class="form-group col-md-4">
          <label>Topics (one or many)</label>
          <div id="topicsContainer"
               class="border rounded p-2"
               style="max-height: 170px; overflow-y:auto; background:#f9f9f9; font-size:13px;">
            <small class="text-muted">Select Class &amp; Subject to load topics.</small>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-2">
        <!-- Column Visibility Toggles -->
        <div class="form-inline">
          <label class="mr-2 mb-0"><strong>Show / Hide Columns:</strong></label>

          <div class="form-check mr-2">
            <input class="form-check-input column-toggle" type="checkbox" id="toggle_en" data-col-class="col-meaning-en" checked>
            <label class="form-check-label" for="toggle_en">Meaning (EN)</label>
          </div>

          <div class="form-check mr-2">
            <input class="form-check-input column-toggle" type="checkbox" id="toggle_ur" data-col-class="col-meaning-ur" checked>
            <label class="form-check-label" for="toggle_ur">Meaning (UR)</label>
          </div>

          <div class="form-check mr-2">
            <input class="form-check-input column-toggle" type="checkbox" id="toggle_pos" data-col-class="col-pos" checked>
            <label class="form-check-label" for="toggle_pos">Part of Speech</label>
          </div>

          <div class="form-check mr-2">
            <input class="form-check-input column-toggle" type="checkbox" id="toggle_example" data-col-class="col-example" checked>
            <label class="form-check-label" for="toggle_example">Example Sentence</label>
          </div>
          <div class="form-check mr-2">
  <input class="form-check-input column-toggle" type="checkbox"
         data-col-class="col-syllables" checked>
  <label class="form-check-label">Syllables</label>
</div>

<div class="form-check mr-2">
  <input class="form-check-input column-toggle" type="checkbox"
         data-col-class="col-synonyms" checked>
  <label class="form-check-label">Synonyms</label>
</div>

<div class="form-check mr-2">
  <input class="form-check-input column-toggle" type="checkbox"
         data-col-class="col-antonyms" checked>
  <label class="form-check-label">Antonyms</label>
</div>

<div class="form-check mr-2">
  <input class="form-check-input column-toggle" type="checkbox"
         data-col-class="col-related" checked>
  <label class="form-check-label">Related Words</label>
</div>

<div class="form-check mr-2">
  <input class="form-check-input column-toggle" type="checkbox"
         data-col-class="col-confusing" checked>
  <label class="form-check-label">Confusing Pair</label>
</div>

<div class="form-check mr-2">
  <input class="form-check-input column-toggle" type="checkbox"
         data-col-class="col-confusing-diff" checked>
  <label class="form-check-label">Difference</label>
</div>

        </div>

        <div>
          <button type="button" id="btnShowReport" class="btn btn-primary">
            <i class="fas fa-search"></i> Show Report
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ====================== SUMMARY + EXPORT ====================== -->
  <div id="vocabSummaryWrapper" style="display:none;">
    <div class="row">
      <div class="col-md-4">
        <div class="small-box bg-info">
          <div class="inner">
            <h3 id="sumTotalWords">0</h3>
            <p>Total Words</p>
          </div>
          <div class="icon">
            <i class="fas fa-spell-check"></i>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <div class="card">
          <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <h3 class="card-title" style="font-size:14px;">
              <strong>Selected:</strong>
              Class: <span id="sumClassName"></span> |
              Subject: <span id="sumSubjectName"></span> |
              Topics: <span id="sumTopicName"></span>
            </h3>

            <!-- Export / Print Buttons (NO PRINT themselves) -->
            <div class="no-print">
              <button type="button" id="btnPrint" class="btn btn-default btn-sm">
                <i class="fa fa-print"></i> Print
              </button>
              <button type="button" id="btnPdf" class="btn btn-danger btn-sm">
                <i class="far fa-file-pdf"></i> PDF
              </button>
              <button type="button" id="btnExcel" class="btn btn-success btn-sm">
                <i class="far fa-file-excel"></i> Excel
              </button>
            </div>
          </div>
          <div class="card-body p-2">
            <strong>Parts of Speech Breakdown</strong>
            <div id="sumPosList" 
     style="font-size:13px; line-height:1.4; display:flex; flex-wrap:wrap; gap:8px;">
</div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ====================== REPORT TABLE ====================== -->
  <div class="card" id="vocabTableWrapper" style="display:none;">
    <div class="card-header">
      <h3 class="card-title">Vocabulary Details</h3>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm mb-0" id="vocabReportTable">
          <thead class="thead-light">
<tr>
  <th style="width:60px;">#</th>
  <th style="width:160px;">Word</th>

  <th class="col-meaning-en">Meaning (EN)</th>
  <th class="col-meaning-ur">Meaning (UR)</th>
  <th class="col-pos" style="width:120px;">Part of Speech</th>
  <th class="col-example">Example</th>

  <th class="col-syllables" style="width:100px;">Syllables</th>
  <th class="col-synonyms">Synonyms</th>
  <th class="col-antonyms">Antonyms</th>
  <th class="col-related">Related Words</th>
  <th class="col-confusing">Confusing Pair</th>
  <th class="col-confusing-diff">Difference</th>
</tr>
</thead>

          <tbody id="vocabReportBody">
            <!-- rows via JS -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

</section>

<!-- jsPDF + autoTable for PDF export (CDN) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.3/jspdf.plugin.autotable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

  const classSelect      = document.getElementById('class_id');
  const subjectSelect    = document.getElementById('subject_id');
  const topicsContainer  = document.getElementById('topicsContainer');
  const btnShow          = document.getElementById('btnShowReport');

  const wrapSummary      = document.getElementById('vocabSummaryWrapper');
  const sumTotal         = document.getElementById('sumTotalWords');
  const sumClassName     = document.getElementById('sumClassName');
  const sumSubjectName   = document.getElementById('sumSubjectName');
  const sumTopicName     = document.getElementById('sumTopicName');
  const sumPosList       = document.getElementById('sumPosList');

  const wrapTable        = document.getElementById('vocabTableWrapper');
  const tbody            = document.getElementById('vocabReportBody');

  const btnPrint         = document.getElementById('btnPrint');
  const btnPdf           = document.getElementById('btnPdf');
  const btnExcel         = document.getElementById('btnExcel');

  const columnToggles    = document.querySelectorAll('.column-toggle');

  function resetSubjects() {
    subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
  }

  function resetTopicsUI() {
    topicsContainer.innerHTML = '<small class="text-muted">Select Class &amp; Subject to load topics.</small>';
  }

  function resetReport() {
    wrapSummary.style.display = 'none';
    wrapTable.style.display   = 'none';
    tbody.innerHTML           = '';
    sumPosList.innerHTML      = '';
    sumTotal.textContent      = '0';
    sumClassName.textContent  = '';
    sumSubjectName.textContent= '';
    sumTopicName.textContent  = '';
  }

  // =============== LOAD SUBJECTS ON CLASS CHANGE ==================
  classSelect.addEventListener('change', function () {
    const cid = this.value;
    resetSubjects();
    resetTopicsUI();
    resetReport();

    if (!cid) return;

    fetch('<?= base_url('admin/question-bank/subjects') ?>?class_id=' + encodeURIComponent(cid))
      .then(r => r.json())
      .then(d => {
        const subjects = Array.isArray(d) ? d : (d.subjects || []);
        resetSubjects();
        subjects.forEach(s => {
          const o = document.createElement('option');
          o.value = s.subject_id;
          o.textContent = s.subject_name || s.subject_short_name || ('Subject ' + s.subject_id);
          subjectSelect.appendChild(o);
        });
      })
      .catch(err => {
        console.error('Vocab Report: load subjects error', err);
        resetSubjects();
      });
  });

  // =============== LOAD TOPICS ON SUBJECT CHANGE (CHECKBOXES) ==============
  subjectSelect.addEventListener('change', function () {
    const cid = classSelect.value;
    const sid = this.value;
    resetTopicsUI();
    resetReport();

    if (!cid || !sid) {
      return;
    }

    topicsContainer.innerHTML = '<small class="text-muted">Loading topics...</small>';

    fetch('<?= base_url('admin/vocab-bank/topics') ?>?class_id='
          + encodeURIComponent(cid)
          + '&subject_id=' + encodeURIComponent(sid))
      .then(r => r.json())
      .then(d => {
        const topics = Array.isArray(d) ? d : (d.topics || []);
        if (!topics.length) {
          topicsContainer.innerHTML = '<small class="text-danger">No topics found for this class &amp; subject.</small>';
          return;
        }

        topicsContainer.innerHTML = '';
        topics.forEach(t => {
          const id   = t.id;
          const name = t.topic_name || ('Topic ' + id);

          const wrapper = document.createElement('div');
          wrapper.className = 'form-check';

          wrapper.innerHTML = `
            <input class="form-check-input topic-checkbox" type="checkbox" value="${id}" id="topic_${id}">
            <label class="form-check-label" for="topic_${id}">${escapeHtml(name)}</label>
          `;
          topicsContainer.appendChild(wrapper);
        });
      })
      .catch(err => {
        console.error('Vocab Report: load topics error', err);
        topicsContainer.innerHTML = '<small class="text-danger">Error loading topics.</small>';
      });
  });

  // =============== SHOW REPORT BUTTON ==================
  btnShow.addEventListener('click', function () {
    const cid = classSelect.value;
    const sid = subjectSelect.value;

    const topicCheckboxes = document.querySelectorAll('.topic-checkbox:checked');
    const topicIds   = [];
    const topicNames = [];

    topicCheckboxes.forEach(cb => {
      topicIds.push(cb.value);
      const label = cb.closest('.form-check').querySelector('label');
      if (label) {
        topicNames.push(label.textContent.trim());
      }
    });

    if (!cid || !sid) {
      alert('Please select Class and Subject.');
      return;
    }
    if (!topicIds.length) {
      alert('Please select at least one Topic.');
      return;
    }

    resetReport();

    const url = '<?= base_url('admin/vocab-bank/report-data') ?>'
      + '?class_id='   + encodeURIComponent(cid)
      + '&subject_id=' + encodeURIComponent(sid)
      + '&topic_ids='  + encodeURIComponent(topicIds.join(','));

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(data => {
        if (!data || data.status !== 'ok') {
          alert(data && data.msg ? data.msg : 'Failed to load report.');
          return;
        }

        const items = Array.isArray(data.items) ? data.items : [];

        // ===== Summary =====
        sumTotal.textContent = data.header.total_words || items.length || 0;

        const classText   = classSelect.options[classSelect.selectedIndex]
                            ? classSelect.options[classSelect.selectedIndex].text
                            : (data.header.class_name || '');
        const subjectText = subjectSelect.options[subjectSelect.selectedIndex]
                            ? subjectSelect.options[subjectSelect.selectedIndex].text
                            : (data.header.subject_name || '');

        sumClassName.textContent   = classText;
        sumSubjectName.textContent = subjectText;
        sumTopicName.textContent   = topicNames.join(', ');

       sumPosList.innerHTML = '';
if (data.part_of_speech_counts) {

    const entries = Object.keys(data.part_of_speech_counts)
        .map(pos => `${pos} (${data.part_of_speech_counts[pos]})`);

    // Split into 2 rows if long
    let midpoint = Math.ceil(entries.length / 2);
    let row1 = entries.slice(0, midpoint).join(' | ');
    let row2 = entries.slice(midpoint).join(' | ');

    sumPosList.innerHTML = `
        <div>${row1}</div>
        ${row2 ? `<div>${row2}</div>` : ''}
    `;
}


        wrapSummary.style.display = 'block';

        // ===== Table =====
        tbody.innerHTML = '';

        if (!items.length) {
          const tr = document.createElement('tr');
          const td = document.createElement('td');
          td.colSpan = 12;
          td.className = 'text-center text-muted';
          td.textContent = 'No vocabulary found for selected topics.';
          tr.appendChild(td);
          tbody.appendChild(tr);
        } else {
          items.forEach((row, idx) => {
            const tr = document.createElement('tr');

           tr.innerHTML = `
  <td>${idx + 1}</td>
  <td><strong>${escapeHtml(row.word || '')}</strong></td>

  <td class="col-meaning-en">${escapeHtml(row.meaning_en || '')}</td>
  <td class="col-meaning-ur">${escapeHtml(row.meaning_ur || '')}</td>
  <td class="col-pos">${escapeHtml(row.part_of_speech || '')}</td>
  <td class="col-example">${escapeHtml(row.example_sentence || '')}</td>

  <td class="col-syllables">${escapeHtml(row.syllables || '')}</td>
  <td class="col-synonyms">${escapeHtml(row.synonyms || '')}</td>
  <td class="col-antonyms">${escapeHtml(row.antonyms || '')}</td>
  <td class="col-related">${escapeHtml(row.related_words || '')}</td>
  <td class="col-confusing">${escapeHtml(row.confusing_pair || '')}</td>
  <td class="col-confusing-diff">${escapeHtml(row.confusing_pair_difference || '')}</td>
`;

            tbody.appendChild(tr);
          });
        }

        wrapTable.style.display = 'block';

        // Apply current column visibility settings after re-render
        applyColumnVisibility();
      })
      .catch(err => {
        console.error('Vocab Report: fetch report error', err);
        alert('Error loading report.');
      });
  });

  // Simple HTML escape helper
  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // =============== COLUMN SHOW / HIDE ==================
  function applyColumnVisibility() {
    columnToggles.forEach(function (chk) {
      const colClass = chk.getAttribute('data-col-class');
      if (!colClass) return;

      const headerCells = document.querySelectorAll('#vocabReportTable th.' + colClass);
      const bodyCells   = document.querySelectorAll('#vocabReportTable td.' + colClass);

      [...headerCells, ...bodyCells].forEach(function (cell) {
        if (chk.checked) {
          cell.classList.remove('d-none');
        } else {
          cell.classList.add('d-none');
        }
      });
    });
  }

  columnToggles.forEach(function (chk) {
    chk.addEventListener('change', applyColumnVisibility);
  });

  // =============== PRINT / PDF / EXCEL ==================

  // Print – browser print (filters are .no-print so they won't appear)
  if (btnPrint) {
    btnPrint.addEventListener('click', function () {
      if (!wrapTable || wrapTable.style.display === 'none') {
        alert('Load the report first.');
        return;
      }
      window.print();
    });
  }

  // PDF export using jsPDF + autoTable
  if (btnPdf) {
    btnPdf.addEventListener('click', function () {
      if (!wrapTable || wrapTable.style.display === 'none') {
        alert('Load the report first.');
        return;
      }

      const { jsPDF } = window.jspdf;
      const doc = new jsPDF('p', 'pt', 'a4');

      const title = 'Vocabulary Report - '
        + (sumClassName.textContent || '')
        + ' | ' + (sumSubjectName.textContent || '')
        + ' | ' + (sumTopicName.textContent || '');

      doc.setFontSize(12);
      doc.text(title, 40, 40);

      // Build head & body arrays from visible columns only
      const table = document.getElementById('vocabReportTable');
      const head  = [];
      const body  = [];

      const ths = table.querySelectorAll('thead tr th');
      const visibleHead = [];
      ths.forEach(function (th) {
        if (!th.classList.contains('d-none')) {
          visibleHead.push(th.innerText || th.textContent);
        }
      });
      head.push(visibleHead);

      const trs = table.querySelectorAll('tbody tr');
      trs.forEach(function (tr) {
        const rowData = [];
        const tds = tr.querySelectorAll('td');
        tds.forEach(function (td) {
          if (!td.classList.contains('d-none')) {
            rowData.push(td.innerText || td.textContent);
          }
        });
        if (rowData.length) {
          body.push(rowData);
        }
      });

      doc.autoTable({
        head: head,
        body: body,
        startY: 60,
        styles: { fontSize: 8 }
      });

      doc.save('vocabulary-report.pdf');
    });
  }

  // Excel export – CSV, respecting visible columns
  if (btnExcel) {
    btnExcel.addEventListener('click', function () {
      if (!wrapTable || wrapTable.style.display === 'none') {
        alert('Load the report first.');
        return;
      }

      exportTableToCSV('vocabReportTable', 'vocabulary-report.csv');
    });
  }

  function exportTableToCSV(tableId, filename) {
    const rows = document.querySelectorAll('#' + tableId + ' tr');
    const csv = [];

    rows.forEach(function (row) {
      const cols = row.querySelectorAll('th, td');
      const rowData = [];

      cols.forEach(function (cell) {
        // Skip hidden columns
        if (cell.classList.contains('d-none')) return;

        let text = cell.innerText || cell.textContent || '';
        text = text.replace(/(\r\n|\n|\r)/gm, ' ').replace(/"/g, '""');
        rowData.push('"' + text + '"');
      });

      if (rowData.length) {
        csv.push(rowData.join(','));
      }
    });

    const csvString = csv.join('\n');
    const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });

    const link = document.createElement('a');
    if (link.download !== undefined) {
      const url = URL.createObjectURL(blob);
      link.setAttribute('href', url);
      link.setAttribute('download', filename);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  }

  // Initial – make sure column visibility matches default (all on)
  applyColumnVisibility();

});
</script>

<style>
/* Hide UI filters etc. from print (and only for this page) */
@media print {
  .no-print,
  .main-header,
  .main-sidebar,
  .main-footer,
  .content-header {
    display: none !important;
  }

  .content-wrapper,
  .content {
    margin: 0 !important;
    padding: 0 !important;
  }

  #vocabTableWrapper {
    page-break-after: auto;
  }
}
</style>

<?= $this->endSection() ?>
