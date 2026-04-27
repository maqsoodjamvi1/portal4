<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>House Result Sheet</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('admin/sports/leaderboard') ?>">Leaderboard</a></li>
          <li class="breadcrumb-item active">House Sheet</li>
        </ol>
      </div>
    </div>
  </div>
</section>

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
