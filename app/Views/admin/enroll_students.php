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
             Enroll Student
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Enroll Student</li>
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
        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/addbulkstudents/add') ?>"> Student Names</a></li> 
         <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/students_enroll') ?>"> Enroll Students</a>
        <li class="nav-item"><a href="<?= base_url('admin/students_bulk_cnic') ?>" class="nav-link">Father Names</a></li>        
        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/studentsbulk') ?>">Fee Detail</a></li>
        <?php if($campus_info->a_flag == 1){ ?>
        <li class="nav-item"><a class="nav-link"  href="/admin/students_bulk_academy_fee">Academy Fee Detail</a></li>
        <?php } ?>
        <?php if($campus_info->h_flag == 1){ ?>
        <li class="nav-item"><a class="nav-link"  href="/admin/h_student_beds?m=add">Student Bed</a></li>
        <?php } ?>
        <?php if($campus_info->t_flag == 1){ ?>
        <li class="nav-item"><a class="nav-link"  href="/admin/students_vehicle">Students Vehicle</a></li>
        <?php } ?>
        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_bulk_contacts') ?>"> Contact Numbers</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_bulk_info') ?>"> Other Student Info</a>
        </li>
        <li class="nav-item"><a class="nav-link"  href="/admin/students/addbulk">Entries through Excel</a></li>
      </ul>    
    <div class=""> 
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
  $('#cls_sec_id').on('change', function() {  
  $("#loader-1").css("display", "block"); 
  var cls_sec_id = $('#cls_sec_id').val();
  $.ajax({
            url: '/admin/students_enroll/data', 
            type: "POST",
            data:{cls_sec_id:cls_sec_id},
            success:function(res){
             $("#studentsList").html(res);
             $("#loader-1").css("display", "none");
          }
      });
  });
});
</script>

<?= $this->endSection() ?>