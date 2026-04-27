<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>Event Managers</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('admin/sports/events') ?>">Sports Events</a></li>
          <li class="breadcrumb-item active">Managers</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
 <div class="row">
  <div class="col-lg-10">
   <div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Assign Manager</h3></div>
    <div class="card-body">
      <div class="form-row">
        <div class="form-group col-md-5">
          <label>Teacher</label>
          <select id="tid" class="form-control select2"></select>
        </div>
        <div class="form-group col-md-5">
          <label>House (optional)</label>
          <select id="house_id" class="form-control select2"><option value="">—</option></select>
        </div>
        <div class="form-group col-md-2">
          <label>&nbsp;</label>
          <button id="assignBtn" class="btn btn-primary btn-block"><i class="fas fa-user-tie"></i> Assign</button>
        </div>
      </div>

      <table class="table table-bordered table-striped">
        <thead><tr><th>#</th><th>Teacher</th><th>House</th><th width="80">Action</th></tr></thead>
        <tbody id="mgrRows">
          <?php $i=1; foreach(($rows??[]) as $r): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= esc(($r['first_name']??'').' '.($r['last_name']??'')) ?></td>
              <td><?= !empty($r['house_id']) ? esc($r['house_id']) : '-' ?></td>
              <td><button data-id="<?= $r['id'] ?>" class="btn btn-sm btn-danger btn-remove"><i class="fas fa-trash"></i></button></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
   </div>
  </div>
 </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';
const EVENT_ID = '<?= (int)($eventId ?? 0) ?>';

$(function(){
  $('.select2').select2({width:'100%'});

  function loadHouses(){
    $.post("<?= base_url('admin/sports/houses/data') ?>", {[CSRF_NAME]:CSRF_HASH}, function(resp){
      let opts = '<option value="">—</option>';
      (resp.data||[]).forEach(r => opts += `<option value="${r.house_id}">${r.house_name}</option>`);
      $('#house_id').html(opts);
    }, 'json');
  }
  function loadTeachers(){
    $.post("<?= base_url('admin/ajax/teachers-options') ?>", {[CSRF_NAME]:CSRF_HASH}, function(opts){
      $('#tid').html(opts);
    }, 'html');
  }
  loadHouses(); loadTeachers();

  $('#assignBtn').on('click', function(){
    $.post("<?= base_url('admin/sports/managers/assign') ?>", {
      [CSRF_NAME]:CSRF_HASH, event_id: EVENT_ID, tid: $('#tid').val(), house_id: $('#house_id').val()
    }, function(res){
      if(res.ok){ toastr.success('Assigned'); location.reload(); } else { toastr.error('Failed'); }
    }, 'json');
  });

  $('#mgrRows').on('click','.btn-remove', function(){
    const id = $(this).data('id');
    $.post("<?= base_url('admin/sports/managers/remove') ?>", {[CSRF_NAME]:CSRF_HASH, id}, function(res){
      if(res.ok){ toastr.success('Removed'); location.reload(); } else { toastr.error('Failed'); }
    }, 'json');
  });
});
</script>

<?= $this->endSection() ?>
