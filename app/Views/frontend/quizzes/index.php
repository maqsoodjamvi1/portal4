<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'My Quizzes',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('student/dashboard')],
        ['label' => 'My Quizzes', 'active' => true],
    ],
]) ?>


<section class="content">
  <div class="row">
    <!-- Available -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header"><strong>Available (Unattempted)</strong></div>
        <div class="card-body p-0">
          <table class="table table-striped table-hover mb-0">
            <thead>
              <tr>
                <th>Title</th>
                <th>Window</th>
                <th style="width:120px;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($unattempted)): ?>
                <?php foreach ($unattempted as $q): ?>
                  <tr>
                    <td><?= esc($q->title) ?></td>
                    <td>
                      <?php if ($q->start_at): ?><small>From:</small> <?= esc($q->start_at) ?><br><?php endif; ?>
                      <?php if ($q->end_at):   ?><small>To:</small>   <?= esc($q->end_at) ?><?php endif; ?>
                    </td>
                    <td>
                      <a class="btn btn-primary btn-sm" href="<?= base_url('student/quizzes/start/'.$q->quiz_id) ?>">Start</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="3" class="text-center p-3">No quizzes available right now.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Attempted -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header"><strong>Attempted</strong></div>
        <div class="card-body p-0">
          <table class="table table-striped table-hover mb-0">
            <thead>
              <tr>
                <th>Quiz</th>
                <th>Score</th>
                <th>Status</th>
                <th>Submitted</th>
                <th style="width:120px;">Review</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($attempted)): ?>
                <?php foreach ($attempted as $a): ?>
                  <tr>
                    <td><?= esc($a->title) ?></td>
                    <td><?= esc($a->score_obtained) ?></td>
                    <td><span class="badge <?= $a->status==='submitted'?'text-bg-success':'text-bg-secondary' ?>"><?= esc($a->status) ?></span></td>
                    <td><?= esc($a->submitted_at) ?></td>
                    <td>
                      <a class="btn btn-info btn-sm" href="<?= base_url('student/quizzes/review/'.$a->attempt_id) ?>">Open</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center p-3">No attempts yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

<?= $this->endSection() ?>
