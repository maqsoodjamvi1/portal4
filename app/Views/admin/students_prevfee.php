<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php 
  $status = ''; 
  if(!empty($_GET['status'])){
   $status = $_GET['status']; 
  }
?>
<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<!-- Content Header (Page header) -->
<section class="content-header">
      <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>
             Fee Detail
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Fee Detail</li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
</section>
    <!-- Main content -->
    <section class="content">
    <div class="row">
    <div class="col-lg-12">
    <div class="card card-primary card-outline card-tabs">
      <div class="card-header p-0 pt-1 border-bottom-0">
			<ul class="nav nav-tabs">   
              
        <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/studentsbulk') ?>">Fee Detail</a></li>
        
        </ul>    
  <div class="col-lg-12">
    <div class="row">
    <div class="col-lg-6 form-group">
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
    </div>
    <div class="col-lg-6 form-group">
      <label for="class"><strong>Month</strong></label><br>
        <select class="form-control" name="month" id="month">
          <option value="1">January</option>
          <option value="2">February</option>
          <option value="3">March</option>
          <option value="4">April</option>
          <option value="5">May</option>
          <option value="6">June</option>
          <option value="7">July</option>
          <option value="8">August</option>
          <option value="9">September</option>
          <option value="10">October</option>
          <option value="11">November</option>
          <option value="12">December</option>
        </select>
    </div>
    </div>
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
// $(function(){
//    $.ajax({
//             url: 'admin.php?c=studentsbulk&m=data', 
//             type: "POST",
//             data:{},
//             success:function(res){
//               $("#studentsList").html(res);
//            }
//    });
//  });
 </script>
 <script type="text/javascript">
$(function(){
  $('#month').on('change', function() {  
  $("#loader-1").css("display", "block"); 
  var cls_sec_id = $('#cls_sec_id').val();
  var month = $('#month').val();
  $.ajax({
            url: '/admin/students_prevfee/data', 
            type: "POST",
            data:{cls_sec_id:cls_sec_id,month:month},
            success:function(res){
             $("#studentsList").html(res);
             $("#loader-1").css("display", "none");
          }
      });
  });
});
</script>

<?= $this->endSection() ?>