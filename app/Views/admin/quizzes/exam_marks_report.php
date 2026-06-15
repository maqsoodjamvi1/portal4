<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
.exam-marks-report .filter-card {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 16px rgba(0,0,0,.06);
  margin-bottom: 20px;
}
.exam-marks-report .term-badge {
  display: inline-block;
  background: #eef1ff;
  color: #4a4aff;
  padding: 6px 12px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
}
.exam-marks-report .matrix-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  max-width: 100%;
  border: 1px solid #dee2e6;
  border-radius: 8px;
}
.exam-marks-report .matrix-table {
  margin-bottom: 0;
  font-size: 13px;
  white-space: nowrap;
}
.exam-marks-report .matrix-table th,
.exam-marks-report .matrix-table td {
  vertical-align: middle;
  padding: 8px 10px;
}
.exam-marks-report .matrix-table thead th {
  background: #f8f9fa;
  position: sticky;
  top: 0;
  z-index: 2;
}
.exam-marks-report .sticky-col {
  position: sticky;
  left: 0;
  background: #fff;
  z-index: 3;
  box-shadow: 2px 0 4px rgba(0,0,0,.06);
}
.exam-marks-report .matrix-table thead .sticky-col {
  background: #f8f9fa;
  z-index: 4;
}
.exam-marks-report .quiz-col-header {
  max-width: 140px;
  white-space: normal;
  line-height: 1.25;
  font-size: 11px;
}
.exam-marks-report .quiz-col-header .subj {
  font-weight: 700;
  color: #333;
}
.exam-marks-report .score-cell {
  text-align: center;
  min-width: 56px;
}
.exam-marks-report .score-cell a {
  font-weight: 600;
}
.exam-marks-report .score-empty {
  color: #aaa;
}
@media print {
  .no-print { display: none !important; }
  .exam-marks-report .matrix-wrap { overflow: visible; border: none; }
}
</style>

<div class="exam-marks-report container-fluid">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Exam Quiz Marks Report</h4>
    <div class="no-print">
      <button type="button" class="btn btn-outline-secondary btn-sm" id="btnPrint" disabled>
        <i class="fa fa-print"></i> Print
      </button>
      <button type="button" class="btn btn-outline-success btn-sm" id="btnCsv" disabled>
        <i class="fa fa-download"></i> Download CSV
      </button>
    </div>
  </div>

  <?php if (empty($examQuizColumnReady)): ?>
    <div class="alert alert-warning">
      Exam-linked quizzes require the <code>exam_id</code> column on the quizzes table.
      Run <code>php spark migrate</code> on the server.
    </div>
  <?php else: ?>

  <div class="filter-card no-print">
    <?php if (!empty($sessionLabel)): ?>
      <div class="mb-3">
        <span class="text-muted me-2">Academic session:</span>
        <span class="term-badge"><?= esc($sessionLabel) ?></span>
      </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-md-4 form-group">
        <label for="termSelect"><strong>Term</strong></label>
        <select id="termSelect" class="form-control">
          <option value="">— Select term —</option>
          <?php foreach (($termOptions ?? []) as $tsid => $label): ?>
            <option value="<?= (int) $tsid ?>"><?= esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 form-group">
        <label for="clsSecSelect"><strong>Class &amp; Section</strong></label>
        <select id="clsSecSelect" class="form-control">
          <option value="">— Select class-section —</option>
          <?php foreach ($classSections as $cs): ?>
            <option value="<?= (int) $cs['cls_sec_id'] ?>">
              <?= esc($cs['class_name'] . ' ' . $cs['section_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 form-group">
        <label for="examSelect"><strong>Exam</strong></label>
        <select id="examSelect" class="form-control" disabled>
          <option value="">— Select term first —</option>
        </select>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12 d-flex justify-content-end">
        <button type="button" class="btn btn-primary" id="btnLoad" disabled>Load report</button>
      </div>
    </div>
  </div>

  <div id="reportMeta" class="mb-2 d-none"></div>
  <div id="reportEmpty" class="alert alert-info d-none"></div>
  <div id="reportMatrix" class="matrix-wrap d-none">
    <table class="table table-bordered table-sm matrix-table" id="marksMatrixTable">
      <thead></thead>
      <tbody></tbody>
    </table>
  </div>

  <?php endif; ?>
</div>

<?php if (!empty($examQuizColumnReady)): ?>
<script>
(function () {
  const termSelect = document.getElementById('termSelect');
  const examSelect = document.getElementById('examSelect');
  const clsSecSelect = document.getElementById('clsSecSelect');
  const btnLoad = document.getElementById('btnLoad');
  const btnPrint = document.getElementById('btnPrint');
  const btnCsv = document.getElementById('btnCsv');

  let matrixData = null;

  function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str == null ? '' : String(str);
    return d.innerHTML;
  }

  function updateLoadButton() {
    btnLoad.disabled = !(termSelect.value && clsSecSelect.value && examSelect.value);
  }

  function resetExamSelect(message) {
    examSelect.innerHTML = '<option value="">' + message + '</option>';
    examSelect.disabled = true;
    updateLoadButton();
  }

  function loadExams() {
    const termId = termSelect.value;
    if (!termId) {
      resetExamSelect('— Select term first —');
      return;
    }

    examSelect.disabled = true;
    examSelect.innerHTML = '<option value="">Loading exams…</option>';

    const url = '<?= site_url('admin/quizzes/ajax/exams-current-term') ?>'
      + '?term_session_id=' + encodeURIComponent(termId);

    fetch(url)
      .then(r => r.json())
      .then(data => {
        examSelect.innerHTML = '';
        if (!data.success || !data.exams || !data.exams.length) {
          resetExamSelect(data.message || 'No exams for this term');
          return;
        }
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = '— Select exam —';
        examSelect.appendChild(placeholder);

        data.exams.forEach(ex => {
          const label = ex.exam_name + (ex.short_name ? ' (' + ex.short_name + ')' : '');
          const opt = document.createElement('option');
          opt.value = ex.eid;
          opt.textContent = label;
          examSelect.appendChild(opt);
        });
        examSelect.disabled = false;
        updateLoadButton();
      })
      .catch(() => {
        resetExamSelect('Failed to load exams');
      });
  }

  function renderMatrix(data) {
    matrixData = data;
    const meta = document.getElementById('reportMeta');
    const empty = document.getElementById('reportEmpty');
    const wrap = document.getElementById('reportMatrix');
    const thead = document.querySelector('#marksMatrixTable thead');
    const tbody = document.querySelector('#marksMatrixTable tbody');

    const clsName = data.class_info?.cls_sec_name || '';
    const examName = data.exam?.exam_name || '';
    meta.classList.remove('d-none');
    meta.innerHTML = '<strong>' + escHtml(examName) + '</strong> · ' + escHtml(clsName)
      + (data.term_label ? ' · <span class="text-muted">' + escHtml(data.term_label) + '</span>' : '');

    if (!data.quizzes || !data.quizzes.length) {
      wrap.classList.add('d-none');
      empty.classList.remove('d-none');
      empty.textContent = 'No exam-linked quizzes found for this class-section and exam.';
      btnPrint.disabled = true;
      btnCsv.disabled = true;
      return;
    }

    if (!data.students || !data.students.length) {
      wrap.classList.add('d-none');
      empty.classList.remove('d-none');
      empty.textContent = 'No students enrolled in this class-section.';
      btnPrint.disabled = true;
      btnCsv.disabled = true;
      return;
    }

    empty.classList.add('d-none');
    wrap.classList.remove('d-none');

    let headRow = '<tr><th class="sticky-col">#</th><th class="sticky-col">Reg #</th><th class="sticky-col">Student</th>';
    data.quizzes.forEach(q => {
      headRow += '<th class="quiz-col-header"><span class="subj">' + escHtml(q.subject_name || '') + '</span><br>'
        + escHtml(q.title || 'Quiz') + '</th>';
    });
    headRow += '</tr>';
    thead.innerHTML = headRow;

    let bodyHtml = '';
    data.students.forEach((stu, idx) => {
      const sid = stu.student_id;
      bodyHtml += '<tr><td class="sticky-col">' + (idx + 1) + '</td>'
        + '<td class="sticky-col">' + escHtml(stu.reg_no || '') + '</td>'
        + '<td class="sticky-col">' + escHtml(stu.student_name || '') + '</td>';

      data.quizzes.forEach(q => {
        const qid = q.quiz_id;
        const cell = data.scores && data.scores[sid] && data.scores[sid][qid]
          ? data.scores[sid][qid]
          : null;
        if (cell && cell.attempt_id) {
          const reviewUrl = '<?= base_url('student/quizzes/review') ?>/' + cell.attempt_id;
          bodyHtml += '<td class="score-cell"><a href="' + reviewUrl + '" target="_blank" rel="noopener">'
            + escHtml(cell.score) + '</a></td>';
        } else if (cell) {
          bodyHtml += '<td class="score-cell">' + escHtml(cell.score) + '</td>';
        } else {
          bodyHtml += '<td class="score-cell score-empty">—</td>';
        }
      });
      bodyHtml += '</tr>';
    });
    tbody.innerHTML = bodyHtml;

    btnPrint.disabled = false;
    btnCsv.disabled = false;
  }

  function loadMatrix() {
    const clsSecId = clsSecSelect.value;
    const examId = examSelect.value;
    if (!clsSecId || !examId) return;

    document.getElementById('reportEmpty').classList.add('d-none');
    document.getElementById('reportMatrix').classList.add('d-none');
    document.getElementById('reportMeta').classList.add('d-none');

    const termId = termSelect.value;
    const url = '<?= site_url('admin/quizzes/ajax/exam-marks-matrix') ?>'
      + '?term_session_id=' + encodeURIComponent(termId)
      + '&cls_sec_id=' + encodeURIComponent(clsSecId)
      + '&exam_id=' + encodeURIComponent(examId);

    fetch(url)
      .then(r => r.json())
      .then(data => {
        if (!data.success) {
          document.getElementById('reportEmpty').classList.remove('d-none');
          document.getElementById('reportEmpty').textContent = data.message || 'Could not load report.';
          btnPrint.disabled = true;
          btnCsv.disabled = true;
          return;
        }
        renderMatrix(data);
      })
      .catch(() => {
        document.getElementById('reportEmpty').classList.remove('d-none');
        document.getElementById('reportEmpty').textContent = 'Server error while loading report.';
      });
  }

  function downloadCsv() {
    if (!matrixData || !matrixData.quizzes || !matrixData.students) return;

    const examName = matrixData.exam?.exam_name || 'exam';
    const clsName = matrixData.class_info?.cls_sec_name || '';
    const header = ['#', 'Reg #', 'Student'];
    matrixData.quizzes.forEach(q => {
      header.push((q.subject_name || '') + ' - ' + (q.title || 'Quiz'));
    });

    const lines = [header.map(c => '"' + String(c).replace(/"/g, '""') + '"').join(',')];

    matrixData.students.forEach((stu, idx) => {
      const sid = stu.student_id;
      const row = [idx + 1, stu.reg_no || '', stu.student_name || ''];
      matrixData.quizzes.forEach(q => {
        const cell = matrixData.scores && matrixData.scores[sid] && matrixData.scores[sid][q.quiz_id];
        row.push(cell ? cell.score : '—');
      });
      lines.push(row.map(c => '"' + String(c).replace(/"/g, '""') + '"').join(','));
    });

    const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = (examName + '_' + clsName).replace(/[^\w\-]+/g, '_') + '_marks.csv';
    a.click();
    URL.revokeObjectURL(a.href);
  }

  function printReport() {
    window.print();
  }

  termSelect.addEventListener('change', loadExams);
  clsSecSelect.addEventListener('change', updateLoadButton);
  examSelect.addEventListener('change', updateLoadButton);
  btnLoad.addEventListener('click', loadMatrix);
  btnPrint.addEventListener('click', printReport);
  btnCsv.addEventListener('click', downloadCsv);
})();
</script>
<?php endif; ?>

<?= $this->endSection() ?>
