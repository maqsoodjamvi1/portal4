<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Assign Word Search',
    'icon' => 'fas fa-users',
    'breadcrumbs' => [
        ['label' => 'Word Puzzle', 'url' => base_url('admin/word-search')],
        ['label' => 'Assign', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid">
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php if (empty($tablesReady)): ?>
      <div class="alert alert-warning">Run migration <code>2026-06-12-120000_CreateWordSearchTables</code> first.</div>
    <?php else: ?>

    <div class="card card-primary card-outline">
      <div class="card-header"><h3 class="card-title mb-0">New assignment</h3></div>
      <form method="post" action="<?= site_url('admin/word-search/assign') ?>">
        <?= csrf_field() ?>
        <div class="card-body row">
          <div class="col-md-6 form-group">
            <label for="set_id">Saved worksheet</label>
            <select name="set_id" id="set_id" class="form-control" required>
              <option value="">— Select —</option>
              <?php foreach ($savedSets as $set): ?>
                <option value="<?= (int) $set['id'] ?>" <?= (int) ($preselectedSetId ?? 0) === (int) $set['id'] ? 'selected' : '' ?>>
                  #<?= (int) $set['id'] ?> — <?= esc($set['title'] ?? '') ?>
                </option>
              <?php endforeach; ?>
            </select>
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
          <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Assign</button>
        </div>
      </form>
    </div>

    <div class="card card-outline card-secondary">
      <div class="card-header"><h3 class="card-title mb-0">Already assigned</h3></div>
      <div class="card-body p-0">
        <table class="table table-sm table-striped mb-0">
          <thead><tr><th>ID</th><th>Worksheet</th><th>Class</th><th>Due</th><th>Attempts</th><th></th></tr></thead>
          <tbody id="assignmentsBody">
            <?php if (empty($assignments)): ?>
              <tr><td colspan="6" class="text-center text-muted py-4">No assignments match filter.</td></tr>
            <?php else: ?>
              <?php foreach ($assignments as $a): ?>
              <tr>
                <td><?= (int) $a['id'] ?></td>
                <td><?= esc($a['title'] ?? '') ?></td>
                <td><?= esc(trim(($a['class_name'] ?? '') . ' - ' . ($a['section_name'] ?? ''), ' -')) ?></td>
                <td><?= esc($a['due_date'] ?? '—') ?></td>
                <td><?= (int) ($a['attempt_count'] ?? 0) ?></td>
                <td><a href="<?= site_url('admin/word-search/report/' . (int) $a['id']) ?>" class="btn btn-sm btn-outline-primary">Report</a></td>
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
  const tbody = document.getElementById('assignmentsBody');
  const reportBase = <?= json_encode(site_url('admin/word-search/report/')) ?>;

  function render() {
    const setId = parseInt(setSel?.value || '0', 10) || 0;
    const clsId = parseInt(clsSel?.value || '0', 10) || 0;
    const rows = allAssignments.filter(a => {
      if (setId && parseInt(a.set_id, 10) !== setId) return false;
      if (clsId && parseInt(a.cls_sec_id, 10) !== clsId) return false;
      return true;
    });
    if (!tbody) return;
    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No assignments match filter.</td></tr>';
      return;
    }
    tbody.innerHTML = rows.map(a => {
      const cls = [a.class_name, a.section_name].filter(Boolean).join(' - ');
      return '<tr><td>' + a.id + '</td><td>' + (a.title || '') + '</td><td>' + cls + '</td><td>' + (a.due_date || '—') + '</td><td>' + (a.attempt_count || 0) + '</td><td><a href="' + reportBase + a.id + '" class="btn btn-sm btn-outline-primary">Report</a></td></tr>';
    }).join('');
  }

  setSel?.addEventListener('change', render);
  clsSel?.addEventListener('change', render);
})();
</script>

<?= $this->endSection() ?>
