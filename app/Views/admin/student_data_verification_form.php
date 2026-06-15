<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php 
  $status = ''; 
  if(!empty($_GET['status'])){
   $status = $_GET['status']; 
  }
?>
<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<style>
  @media print
  {
  .pagebreak { page-break-before: always; }
  }
  .table-bordered td, .table-bordered th {font-size: 11px !important;}
</style>
<?= view('components/page_header', [
    'title' => 'Student Data Verification',
    'icon' => 'fas fa-clipboard-check',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Data Verification', 'active' => true],
    ],
]) ?>
    <!-- Main content -->
    <section class="content">
    <div class="row">
    <div class="col-lg-12">
    <div class="card card-primary card-outline card-tabs">
      <div class="card-header p-0 pt-1 border-bottom-0">
			
    <div class="">
    <!-- <div class="col-lg-6 form-group">
      <label for="class"><strong>Class</strong></label><br>
        <select class="form-control" name="cls_sec_id" id="cls_sec_id">
          <option value="">All Classes</option>
        <?php if(isset($sectionsclassinfo)){
          foreach ($sectionsclassinfo as  $sectionvalue) {
         ?>
        <option value="<?php echo $sectionvalue['section_id']; ?>"><?php echo $sectionvalue['sectionclassname']; ?></option>
        <?php } ?>
        <?php } ?>  
        </select>
    </div> -->
    </div>
      <div class="card-body">

      <div id="studentsList"></div>
      </div>
    </div>
  </div>
    </div>
    <!-- /.box-body -->
    </div>
    <!-- /.box -->
    </div>
    </div>
    </section>
    <style type="text/css">
    	table.table-bordered th:last-child, table.table-bordered td:last-child{width: 50px;}
    </style>
    <!-- /.content -->
<script type="text/javascript">
$(function(){
  $("#loader-1").css("display", "block");
  $.ajax({
    url: '<?= site_url('admin/student_data_verification_form/data') ?>',
    type: 'POST',
    data: {
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    },
    success: function(res) {
      $("#studentsList").html(res);
      $("#loader-1").css("display", "none");
    },
    error: function() {
      $("#studentsList").html('<div class="alert alert-danger mb-0">Failed to load verification forms. Please try again.</div>');
      $("#loader-1").css("display", "none");
    }
  });
});
</script>

<?= $this->endSection() ?>