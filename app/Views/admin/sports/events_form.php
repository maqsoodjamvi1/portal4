<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><?= !empty($row) ? 'Edit Event' : 'Add Event' ?></h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('admin/sports/events') ?>">Sports Events</a></li>
          <li class="breadcrumb-item active"><?= !empty($row)?'Edit':'Add' ?></li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
 <div class="row">
  <div class="col-lg-10">
   <div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Event Details</h3></div>

    <form id="eventForm">
      <?= csrf_field() ?>
      <input type="hidden" name="event_id" value="<?= esc($row['event_id'] ?? '') ?>">

      <div class="card-body">
        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Event Name</label>
            <input type="text" name="event_name" class="form-control" required
                   value="<?= esc($row['event_name'] ?? '') ?>">
          </div>

          <div class="form-group col-md-3">
            <label>Event Type</label>
            <?php $t = $row['event_type'] ?? 'individual'; ?>
            <select name="event_type" class="form-control" required>
              <option value="individual" <?= $t==='individual'?'selected':'' ?>>Individual</option>
              <option value="team"       <?= $t==='team'?'selected':'' ?>>Team</option>
            </select>
          </div>

          <div class="form-group col-md-3">
            <label>Gender</label>
            <?php $g = $row['gender'] ?? 'all'; ?>
           <?php $g = $row['gender'] ?? 'mixed'; ?>
<select name="gender" class="form-control" required>
  <option value="mixed" <?= $g==='mixed'?'selected':'' ?>>Mixed</option>
  <option value="male"  <?= $g==='male'?'selected':'' ?>>male</option>
  <option value="female" <?= $g==='female'?'selected':'' ?>>female</option>
</select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Event Date</label>
            <input type="date" name="event_date" class="form-control" required
                   value="<?= !empty($row['event_date']) ? esc(date('Y-m-d', strtotime($row['event_date']))) : '' ?>">
          </div>

          <div class="form-group col-md-3">
            <label>Max Participants</label>
            <input type="number" name="max_participants" class="form-control" min="0" step="1"
                   value="<?= esc($row['max_participants'] ?? '') ?>">
          </div>
        </div>
      </div>

      <div class="card-footer">
        <a class="btn btn-secondary" href="<?= base_url('admin/sports/events') ?>">Back</a>
        <button class="btn btn-primary" type="submit">Save</button>
      </div>
    </form>
   </div>
  </div>
 </div>
</section>

<script>
$(function(){
  $('#eventForm').on('submit', function(e){
    e.preventDefault();
    $.post("<?= base_url('admin/sports/events/save') ?>", $(this).serialize(), function(res){
      if(res.ok){
        toastr.success('Saved');
        window.location = "<?= base_url('admin/sports/events') ?>";
      } else {
        const msg = res.errors ? Object.values(res.errors).join('<br>') : 'Failed';
        toastr.error(msg);
      }
    }, 'json');
  });
});
</script>

<?= $this->endSection() ?>
