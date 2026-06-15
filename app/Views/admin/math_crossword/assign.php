<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Assign Crossword to Class',
    'icon' => 'fas fa-users',
    'breadcrumbs' => [
        ['label' => 'Math Crossword', 'url' => base_url('admin/math-crossword')],
        ['label' => 'Assign', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid">
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success">
        <?= esc(session()->getFlashdata('success')) ?>
        <?php $aid = (int) session()->getFlashdata('assignment_id'); ?>
        <?php if ($aid > 0): ?>
          <a href="<?= site_url('admin/math-crossword/report/' . $aid) ?>" class="alert-link ms-2">View report</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php if (empty($tablesReady)): ?>
      <div class="alert alert-warning">
        Run migration <code>2026-06-08-120000_CreateCrosswordTables</code> first.
      </div>
    <?php else: ?>

    <div class="alert alert-info">
      <strong>Flow:</strong> Generate crossword → check <strong>Save to library</strong> → assign here → students solve at
      <a href="<?= site_url('student/crossword') ?>" target="_blank">Student Portal → Crossword</a>.
    </div>

    <div class="card card-primary card-outline">
      <div class="card-header"><h3 class="card-title mb-0">New assignment</h3></div>
      <form method="post" action="<?= site_url('admin/math-crossword/assign') ?>" id="assignForm">
        <?= csrf_field() ?>
        <div class="card-body row">
          <div class="col-md-6 form-group">
            <label for="set_id">Saved worksheet</label>
            <select name="set_id" id="set_id" class="form-control" required>
              <option value="">— Select —</option>
              <?php foreach ($savedSets as $set): ?>
                <option value="<?= (int) $set['id'] ?>" <?= (int) ($preselectedSetId ?? 0) === (int) $set['id'] ? 'selected' : '' ?>>
                  #<?= (int) $set['id'] ?> — <?= esc($set['title'] ?? '') ?> (<?= esc($set['puzzle_type'] ?? '') ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (empty($savedSets)): ?>
              <small class="text-danger">No saved worksheets. Generate one and check "Save to library".</small>
            <?php endif; ?>
          </div>
          <div class="col-md-4 form-group">
            <label for="cls_sec_id">Class section</label>
            <select name="cls_sec_id" id="cls_sec_id" class="form-control" required>
              <option value="">— Select —</option>
              <?php foreach ($classSections as $cs): ?>
                <option value="<?= (int) $cs['cls_sec_id'] ?>" <?= (int) ($preselectedClsSec ?? 0) === (int) $cs['cls_sec_id'] ? 'selected' : '' ?>>
                  <?= esc($cs['class_name'] . ' - ' . $cs['section_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2 form-group">
            <label for="due_date">Due date</label>
            <input type="date" name="due_date" id="due_date" class="form-control">
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-primary" id="assignBtn" <?= empty($savedSets) ? 'disabled' : '' ?>><i class="fas fa-paper-plane"></i> Assign to students</button>
        </div>
      </form>
    </div>

    <div class="card card-outline card-secondary">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title mb-0">Already assigned crosswords</h3>
        <small class="text-muted" id="filterHint">Select worksheet and/or class to filter</small>
      </div>
      <div class="card-body pb-2">
        <div class="row">
          <div class="col-md-4 form-group">
            <label for="filter_set_id">Filter by worksheet</label>
            <select id="filter_set_id" class="form-control form-control-sm">
              <option value="">All worksheets</option>
              <?php foreach ($savedSets as $set): ?>
                <option value="<?= (int) $set['id'] ?>" <?= (int) ($preselectedSetId ?? 0) === (int) $set['id'] ? 'selected' : '' ?>>
                  #<?= (int) $set['id'] ?> — <?= esc($set['title'] ?? '') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4 form-group">
            <label for="filter_cls_sec_id">Filter by class section</label>
            <select id="filter_cls_sec_id" class="form-control form-control-sm">
              <option value="">All classes</option>
              <?php foreach ($classSections as $cs): ?>
                <option value="<?= (int) $cs['cls_sec_id'] ?>" <?= (int) ($preselectedClsSec ?? 0) === (int) $cs['cls_sec_id'] ? 'selected' : '' ?>>
                  <?= esc($cs['class_name'] . ' - ' . $cs['section_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4 form-group d-flex align-items-end">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearFilters">Clear filters</button>
          </div>
        </div>
        <div id="duplicateWarning" class="alert alert-warning d-none mb-0">
          This worksheet is <strong>already assigned</strong> to the selected class section.
        </div>
      </div>
      <div class="card-body p-0 pt-0">
        <table class="table table-sm table-striped mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Worksheet</th>
              <th>Class</th>
              <th>Due</th>
              <th>Attempts</th>
              <th>Assigned</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="assignmentsBody">
            <?php if (empty($assignments)): ?>
              <tr id="assignmentsEmpty"><td colspan="7" class="text-center text-muted py-4">No assignments match the current filter.</td></tr>
            <?php else: ?>
              <?php foreach ($assignments as $a): ?>
              <tr data-set-id="<?= (int) $a['set_id'] ?>" data-cls-sec-id="<?= (int) $a['cls_sec_id'] ?>">
                <td><?= (int) $a['id'] ?></td>
                <td><?= esc($a['title'] ?? '') ?></td>
                <td><?= esc(trim(($a['class_name'] ?? '') . ' - ' . ($a['section_name'] ?? ''), ' -')) ?></td>
                <td><?= esc($a['due_date'] ?? '—') ?></td>
                <td><?= (int) ($a['attempt_count'] ?? 0) ?></td>
                <td><?= esc($a['created_at'] ?? '') ?></td>
                <td>
                  <a href="<?= site_url('admin/math-crossword/report/' . (int) $a['id']) ?>" class="btn btn-sm btn-outline-primary">Report</a>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php endif; ?>
  </div>
</section>

<script>
(function () {
  const allAssignments = <?= json_encode($allAssignments ?? []) ?>;
  const setSel = document.getElementById('set_id');
  const clsSel = document.getElementById('cls_sec_id');
  const filterSet = document.getElementById('filter_set_id');
  const filterCls = document.getElementById('filter_cls_sec_id');
  const tbody = document.getElementById('assignmentsBody');
  const dupWarn = document.getElementById('duplicateWarning');
  const filterHint = document.getElementById('filterHint');
  const assignBtn = document.getElementById('assignBtn');
  const reportBase = <?= json_encode(site_url('admin/math-crossword/report/')) ?>;

  function escHtml(s) {
    const d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function filteredRows(setId, clsSecId) {
    return allAssignments.filter(function (a) {
      if (setId && parseInt(a.set_id, 10) !== setId) return false;
      if (clsSecId && parseInt(a.cls_sec_id, 10) !== clsSecId) return false;
      return true;
    });
  }

  function renderTable(setId, clsSecId) {
    const rows = filteredRows(setId, clsSecId);
    if (!tbody) return;

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No assignments match the current filter.</td></tr>';
      return;
    }

    tbody.innerHTML = rows.map(function (a) {
      const cls = [a.class_name, a.section_name].filter(Boolean).join(' - ');
      return '<tr data-set-id="' + a.set_id + '" data-cls-sec-id="' + a.cls_sec_id + '">' +
        '<td>' + a.id + '</td>' +
        '<td>' + escHtml(a.title) + '</td>' +
        '<td>' + escHtml(cls) + '</td>' +
        '<td>' + escHtml(a.due_date || '—') + '</td>' +
        '<td>' + (a.attempt_count || 0) + '</td>' +
        '<td>' + escHtml(a.created_at) + '</td>' +
        '<td><a href="' + reportBase + a.id + '" class="btn btn-sm btn-outline-primary">Report</a></td>' +
        '</tr>';
    }).join('');
  }

  function syncFiltersFromForm() {
    if (setSel && filterSet) filterSet.value = setSel.value || '';
    if (clsSel && filterCls) filterCls.value = clsSel.value || '';
    applyFilters();
  }

  function applyFilters() {
    const setId = parseInt(filterSet.value, 10) || 0;
    const clsSecId = parseInt(filterCls.value, 10) || 0;
    renderTable(setId, clsSecId);

    const parts = [];
    if (setId) parts.push('worksheet #' + setId);
    if (clsSecId) parts.push('selected class');
    filterHint.textContent = parts.length
      ? 'Showing assignments for ' + parts.join(' and ')
      : 'Showing all assignments';

    const isDuplicate = setId && clsSecId && allAssignments.some(function (a) {
      return parseInt(a.set_id, 10) === setId && parseInt(a.cls_sec_id, 10) === clsSecId;
    });

    dupWarn.classList.toggle('d-none', !isDuplicate);
    if (assignBtn) assignBtn.disabled = isDuplicate || !setSel.value || !clsSel.value;
  }

  if (setSel) setSel.addEventListener('change', syncFiltersFromForm);
  if (clsSel) clsSel.addEventListener('change', syncFiltersFromForm);
  if (filterSet) filterSet.addEventListener('change', function () {
    if (setSel) setSel.value = filterSet.value;
    applyFilters();
  });
  if (filterCls) filterCls.addEventListener('change', function () {
    if (clsSel) clsSel.value = filterCls.value;
    applyFilters();
  });
  document.getElementById('clearFilters')?.addEventListener('click', function () {
    if (filterSet) filterSet.value = '';
    if (filterCls) filterCls.value = '';
    applyFilters();
  });

  applyFilters();
})();
</script>

<?= $this->endSection() ?>
