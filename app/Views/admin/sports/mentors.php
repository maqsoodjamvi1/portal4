<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>House Mentors</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Sports Mentors</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
 <div class="row">
  <div class="col-lg-10">
   <div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Assign Mentor</h3></div>
    <div class="card-body">
      <div class="form-row">
        <div class="form-group col-md-4">
          <label>House</label>
          <select id="house_id" class="form-control select2"></select>
        </div>
        <div class="form-group col-md-5">
          <label>Teacher</label>
          <select id="tid" class="form-control select2"></select>
        </div>
        <div class="form-group col-md-3">
          <label>Role</label>
          <select id="role" class="form-control">
            <option>Mentor</option>
            <option>Co-Mentor</option>
            <option>Captain</option>
          </select>
        </div>
      </div>
      <button id="assignBtn" class="btn btn-primary"><i class="fas fa-user-plus"></i> Assign</button>
    </div>
   </div>
  </div>
 </div>

 <div class="row">
  <div class="col-lg-12">
   <div class="card card-outline card-info">
    <div class="card-header"><h3 class="card-title">House-wise Mentors</h3></div>
    <div class="card-body" id="mentorList"><i class="fas fa-spinner fa-spin"></i> Loading…</div>
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

  function loadHouses(){
    $.post("<?= base_url('admin/sports/houses/data') ?>", {[CSRF_NAME]:CSRF_HASH}, function(resp){
      let opts = '';
      (resp.data||[]).forEach(r => opts += `<option value="${r.house_id}">${r.house_name} (${r.short_name})</option>`);
      $('#house_id').html(opts);
    }, 'json');
  }
  function loadTeachers(){
    $.post("<?= base_url('admin/ajax/teachers-options') ?>", {[CSRF_NAME]:CSRF_HASH}, function(opts){
      $('#tid').html(opts);
    }, 'html');
  }
  function loadList(){
    $('#mentorList').html('<i class="fas fa-spinner fa-spin"></i> Loading…');
    $.post("<?= base_url('admin/sports/mentors/list') ?>", {[CSRF_NAME]:CSRF_HASH}, function(html){
      $('#mentorList').html(html);
    }, 'html');
  }

  loadHouses(); loadTeachers(); loadList();

  $('#assignBtn').on('click', function(){
    $.post("<?= base_url('admin/sports/mentors/assign') ?>", {
      [CSRF_NAME]:CSRF_HASH, house_id: $('#house_id').val(), tid: $('#tid').val(), role: $('#role').val()
    }, function(res){
      if(res.ok){ toastr.success('Assigned'); loadList(); }
      else { toastr.error('Failed'); }
    }, 'json');
  });

  $('#mentorList').on('click','.btn-remove', function(){
    const id = $(this).data('id');
    $.post("<?= base_url('admin/sports/mentors/remove') ?>", {[CSRF_NAME]:CSRF_HASH, id}, function(res){
      if(res.ok){ toastr.success('Removed'); loadList(); }
      else { toastr.error('Failed'); }
    }, 'json');
  });
});
</script>

<?= $this->endSection() ?>
