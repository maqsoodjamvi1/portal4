<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Word Search Library',
    'icon' => 'fas fa-folder-open',
    'breadcrumbs' => [
        ['label' => 'Word Puzzle', 'url' => base_url('admin/word-search')],
        ['label' => 'Library', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid">
    <?php if (empty($tablesReady)): ?>
      <div class="alert alert-warning">Run migration <code>2026-06-12-120000_CreateWordSearchTables</code> first.</div>
    <?php else: ?>
    <div class="card">
      <div class="card-body p-0">
        <table class="table table-striped mb-0">
          <thead><tr><th>ID</th><th>Title</th><th>Grade</th><th>Created</th><th>Actions</th></tr></thead>
          <tbody>
            <?php if (empty($savedSets)): ?>
              <tr><td colspan="5" class="text-center text-muted py-4">No saved worksheets yet.</td></tr>
            <?php else: ?>
              <?php foreach ($savedSets as $set): ?>
              <tr>
                <td><?= (int) $set['id'] ?></td>
                <td><?= esc($set['title'] ?? '') ?></td>
                <td><?= (int) ($set['grade'] ?? 0) ?></td>
                <td><?= esc($set['created_at'] ?? '') ?></td>
                <td>
                  <a href="<?= site_url('admin/word-search/reprint/' . (int) $set['id']) ?>" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-print"></i></a>
                  <a href="<?= site_url('admin/word-search/assign') ?>?set_id=<?= (int) $set['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-users"></i></a>
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

<?= $this->endSection() ?>
