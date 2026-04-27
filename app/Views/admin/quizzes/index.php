<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <h1 class="mb-3">Quizzes</h1>
  <a href="<?= base_url('admin/quizzes/create') ?>" class="btn btn-primary btn-sm">+ Create Quiz</a>
</section>


<section class="content">
  <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('msg')) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body p-0">
      <table class="table table-striped table-hover mb-0">
        <thead>
          <tr>
            <th style="width:80px;">ID</th>
            <th>Title</th>
            <th>Class / Subject</th>
            <th>Window</th>
            <th>Attempts</th>
            <th>Published</th>
            <th style="width:140px;">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($quizzes)): ?>
          <?php foreach ($quizzes as $q): ?>
            <tr>
              <td><?= (int)$q->quiz_id ?></td>
              <td><?= esc($q->title) ?></td>
              <td><?= esc($q->cls_sec_id) ?> / <?= esc($q->sec_sub_id) ?></td>
              <td>
                <?php if ($q->start_at): ?><div><small>From:</small> <?= esc($q->start_at) ?></div><?php endif; ?>
                <?php if ($q->end_at):   ?><div><small>To:</small>   <?= esc($q->end_at) ?></div><?php endif; ?>
              </td>
              <td><?= (int)$q->max_attempts ?></td>
              <td>
                <?php if ($q->is_published): ?>
                  <span class="badge badge-success">Yes</span>
                <?php else: ?>
                  <span class="badge badge-secondary">No</span>
                <?php endif; ?>
              </td>
              <td>
                <a class="btn btn-info btn-sm" href="<?= site_url('admin/quizzes/'.$q->quiz_id.'/results') ?>">Results</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center p-4">No quizzes yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>




<?= $this->endSection() ?>
