<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />

<?= view('components/page_header', [
    'title' => 'Assign Students to Houses',
    'icon' => 'fas fa-exchange-alt',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Sports Mapping', 'active' => true],
    ],
]) ?>

<section class="content">
 <div class="row">
  <div class="col-lg-12">
   <div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Bulk Assignment</h3></div>
    <div class="card-body">
      <div class="row">
        <div class="form-group col-md-3">
          <label>Class</label>
          <select id="class_id" class="form-control select2">
            <option value="">Select</option>
            <?php // reuse your classes list partial if you have; else fetch via AJAX in scripts ?>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Section</label>
          <select id="section_id" class="form-control select2"><option value="">Select</option></select>
        </div>
        <div class="form-group col-md-4">
          <label>Students</label>
          <select id="students" class="form-control select2" multiple="multiple" style="width:100%"></select>
        </div>
        <div class="form-group col-md-2">
          <label>House</label>
          <select id="house_id" class="form-control select2">
            <?php // server-render houses if passed, else load via AJAX ?>
          </select>
        </div>
      </div>
      <button id="assignBtn" class="btn btn-primary"><i class="fas fa-check"></i> Assign</button>
    </div>
   </div>
  </div>
 </div>

 <div class="row">
  <div class="col-lg-12">
   <div class="card card-outline card-info">
    <div class="card-header"><h3 class="card-title">Current House Badges</h3></div>
    <div class="card-body" id="currentMapping">
      <em>Select class/section to load students�</em>
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

  // Load sections when class changes (reuse your existing endpoints if any)
  $('#class_id').on('change', function(){
    const cid = $(this).val();
    $('#section_id').html('<option value="">Select</option>');
    $('#students').empty();
    if(!cid) return;
    $.post("<?= base_url('admin/ajax/sections-by-class') ?>", {[CSRF_NAME]:CSRF_HASH, class_id:cid}, function(opts){
      $('#section_id').html(opts);
    }, 'html');
  });

  // Load students when section changes
  $('#section_id').on('change', function(){
    const cid = $('#class_id').val(), sid = $(this).val();
    $('#students').empty();
    if(!cid || !sid) return;
    $.post("<?= base_url('admin/addbulkstudents/select-student-by-class-section') ?>", {[CSRF_NAME]:CSRF_HASH, cls_sec_id:sid}, function(html){
      // Expect <option value="student_id">RegNo - Name</option> list (like your bulk add page)
      $('#students').html(html);
      loadCurrent();
    }, 'html');
  });

  function loadCurrent(){
    const sid = $('#section_id').val();
    if(!sid) return;
    $('#currentMapping').html('<i class="fas fa-spinner fa-spin"></i> Loading�');
    $.post("<?= base_url('admin/sports/mapping/current') ?>", {[CSRF_NAME]:CSRF_HASH, cls_sec_id:sid}, function(html){
      $('#currentMapping').html(html);
    }, 'html');
  }

  $('#assignBtn').on('click', function(){
    const houseId = $('#house_id').val();
    const students = $('#students').val() || [];
    if(!houseId || students.length===0){ toastr.error('Select students and a house'); return; }
    $.post("<?= base_url('admin/sports/mapping/assign') ?>", {[CSRF_NAME]:CSRF_HASH, house_id:houseId, student_ids:students}, function(res){
      if(res.ok){ toastr.success('Assigned'); loadCurrent(); }
      else { toastr.error('Failed'); }
    }, 'json');
  });
});
</script>

<?= $this->endSection() ?>