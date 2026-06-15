<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<div class="no-print">
<?= view('components/page_header', [
    'title' => 'House Result Sheet',
    'icon' => 'fas fa-file-alt',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Leaderboard', 'url' => base_url('admin/sports/leaderboard')],
        ['label' => 'House Sheet', 'active' => true],
    ],
]) ?>
</div>

<section class="content">
 <div class="row">
  <div class="col-lg-12">
    <div class="card card-outline card-info">
      <div class="card-header d-print-none">
        <button class="btn btn-secondary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
      </div>
      <div class="card-body">
        <table class="table table-bordered">
          <thead><tr><th>#</th><th>Event</th><th>Winner</th><th>Position</th><th>Points</th></tr></thead>
          <tbody>
            <?php $i=1; foreach(($rows??[]) as $r): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= esc($r['event_name']) ?></td>
                <td><?= $r['first_name'] ? esc($r['first_name'].' '.$r['last_name']) : esc($r['team_name']) ?></td>
                <td><?= (int)$r['position'] ?></td>
                <td><?= (int)$r['points'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
 </div>
</section>

<?= $this->endSection() ?>
