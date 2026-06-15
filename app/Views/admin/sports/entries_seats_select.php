<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>Select Event — Seats (Per House)</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('admin/sports/events') ?>">Sports Events</a></li>
          <li class="breadcrumb-item active">Select Event</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">Choose an Event</h3></div>
    <div class="card-body">
      <form method="get" action="<?= base_url('admin/sports/entries/seats') ?>">
        <div class="row">
          <div class="form-group col-md-8">
            <label for="event_id">Event</label>
            <select id="event_id" class="form-control">
              <option value="">-- Select Event --</option>
              <?php foreach (($events ?? []) as $e): ?>
                <option value="<?= (int)$e['event_id'] ?>">
                  <?= esc($e['event_name']) ?>
                  <?php if (!empty($e['gender'])): ?>
                    (<?= esc(strtolower($e['gender'])) ?>)
                  <?php endif; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group col-md-4" style="margin-top:32px">
            <button type="button" id="goBtn" class="btn btn-primary w-100">
              Open Seats
            </button>
          </div>
        </div>
      </form>
      <?php if (empty($events)): ?>
        <div class="text-muted">No events found. Create an event first.</div>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
document.getElementById('goBtn').addEventListener('click', function(){
  var sel = document.getElementById('event_id');
  var id = sel && sel.value ? parseInt(sel.value, 10) : 0;
  if (!id) { alert('Please select an event'); return; }
  window.location.href = '<?= base_url('admin/sports/entries/seats') ?>/' + id;
});
</script>

<?= $this->endSection() ?>
