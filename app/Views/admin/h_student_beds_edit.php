<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Edit Student Beds';
		$id = $info->sec_id;
		$class_id = $info->class_id;
		$subject_id = intval($info->subject_id);			
	}else{
		$header = 'Add Student Beds';
		$id = 0;
		$class_id = '';
		$subject_id = '';
	}
?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
          Student Beds
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Student Beds</li>
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
         <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_enroll') ?>"> Enroll Students</a>
        <li class="nav-item"><a href="<?= base_url('admin/students_bulk_cnic') ?>" class="nav-link">Father Names</a></li>        
        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/studentsbulk') ?>">Fee Detail</a></li>
        <?php if($campus_info->a_flag == 1){ ?>
        <li class="nav-item"><a class="nav-link"  href="#/students_bulk_academy_fee">Academy Fee Detail</a></li>
        <?php } ?>
        <?php if($campus_info->h_flag == 1){ ?>
        <li class="nav-item"><a class="nav-link active"  href="#/h_student_beds?m=add">Student Bed</a></li>
        <?php } ?>
        <?php if($campus_info->t_flag == 1){ ?>
        <li class="nav-item"><a class="nav-link"  href="#/students_vehicle">Students Vehicle</a></li>
        <?php } ?>
        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_bulk_contacts') ?>"> Contact Numbers</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_bulk_info') ?>"> Other Student Info</a>
        </li>
        <li class="nav-item"><a class="nav-link"  href="#/students?m=addbulk">Entries through Excel</a></li>
      </ul>    
        <div class="card-body">		
        <div class="tab-content">
     		<div id="subjectsection"></div>
			 
      	</div>
      </div>
    </div>
  </div>
  </div>
  </div>
</section>
<!-- /.content -->
<script type="text/javascript">
$(function(){
	 $.ajax({
          url: 'admin.php?c=h_student_beds&m=data2', 
          type: "POST",
          data:{},
          success:function(res){
          	$("#subjectsection").html(res);
		     }
   });

});
</script>

<?= $this->endSection() ?>