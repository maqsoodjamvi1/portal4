<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"> <h1>
    <?= $event ? 'Teams for: '.esc($event['event_name']) : 'Teams (Team Events)' ?>
  </h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('admin/sports/events') ?>">Sports Events</a></li>
          <li class="breadcrumb-item active">Teams</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
 <div class="row">
  <div class="col-lg-8">
    <div class="card card-outline card-info">
      <div class="card-header"><h3 class="card-title">Existing Teams</h3></div>
      <div class="card-body table-responsive">
        <table class="table table-bordered table-striped">
          <thead>
            <tr><th>#</th><th>Team</th><th>House</th><th>Coach</th><th width="160">Actions</th></tr>
          </thead>
          <tbody>
          <?php $i=1; foreach(($teams ?? []) as $t): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= esc($t['team_name']) ?></td>
              <td><?= esc($t['house_name'] ?? $t['house_id']) ?></td>
              <td><?= esc($t['coach_name'] ?? '-') ?></td>
              <td>
                <a class="btn btn-sm btn-primary" href="<?= base_url('admin/sports/teams/'.$t['team_id'].'/members') ?>">
                  <i class="fas fa-users"></i> Members
                </a>
                <button class="btn btn-sm btn-danger del-team" data-id="<?= $t['team_id'] ?>">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card card-primary">
      <div class="card-header"><h3 class="card-title">Add Team</h3>
        <div class="form-group">
  <label>Event</label>
  <select id="event_id" class="form-control select2">
    <option value="">-- Select Team Event --</option>
    <?php foreach(($events ?? []) as $ev): ?>
      <option value="<?= (int)$ev['event_id'] ?>"
        <?= $event && (int)$event['event_id'] === (int)$ev['event_id'] ? 'selected' : '' ?>>
        <?= esc($ev['event_name']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <small class="form-text text-muted">
    You can filter the list using the dropdown in the page header or by visiting
    <code>/admin/sports/teams?event_id=ID</code>.
  </small>
</div>

      </div>
      <div class="card-body">
        <div class="form-group">
          <label>House</label>
          <select id="house_id" class="form-control select2">
            <?php foreach($houses as $h): ?>
              <option value="<?= (int)$h['house_id'] ?>"><?= esc($h['house_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Team Name</label>
          <input type="text" id="team_name" class="form-control" placeholder="e.g., Blue Falcons">
        </div>
        <div class="form-group">
          <label>Coach (optional)</label>
          <input type="text" id="coach_name" class="form-control" placeholder="">
        </div>
        <button id="saveTeam" class="btn btn-success"><i class="fas fa-plus"></i> Create Team</button>
      </div>
    </div>
  </div>
 </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';

$(function(){
  $('.select2').select2({width:'100%'});

  $('#saveTeam').on('click', function(){
    $.post("<?= base_url('admin/sports/teams/save') ?>", {
      [CSRF_NAME]: CSRF_HASH,
      event_id: $('#event_id').val(),   // <-- from dropdown now
      house_id: $('#house_id').val(),
      team_name: $('#team_name').val(),
      coach_name: $('#coach_name').val()
    }, function(res){
      if(res.ok){ toastr.success('Team created'); location.reload(); }
      else { toastr.error(res.msg || 'Failed'); }
    }, 'json');
  });

  // Optional: when user changes event in dropdown, navigate to scoped page
  $('#event_id').on('change', function(){
    const v = $(this).val();
    if (v) {
      window.location = "<?= base_url('admin/sports/teams/event') ?>/" + v;
    }
  });

  $('.del-team').on('click', function(){
    const id = $(this).data('id');
    if(!confirm('Delete this team?')) return;
    $.post("<?= base_url('admin/sports/teams/delete') ?>", {
      [CSRF_NAME]: CSRF_HASH,
      team_id: id
    }, function(res){
      if(res.ok){ toastr.success('Team deleted'); location.reload(); }
      else { toastr.error(res.msg || 'Failed'); }
    }, 'json');
  });
});
</script>
<?= $this->endSection() ?>
