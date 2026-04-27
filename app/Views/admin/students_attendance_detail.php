<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
if(isset($_GET['parent_id'])){
  $parent_id = $_GET['parent_id'];
}else{
  echo "Parent not found";
  exit;
}
?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
          Students Attdendance
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Students Attdendance</li>
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
       	 <div class="card-body">  
        <div class="tab-content">
		  <div class="row">          
		  <div class="col-lg-12">
         <div id="students_list_container" ></div>
		 </div>
		  </div>
		</div>
		</div>
      </div>
    </div>
  </div>
</section>
<!-- /.content -->
<script>
$( document ).ready(function() {
 		$("#loader-1").css("display", "block");
		//var campus_id = $('#campus_id').val();
		//var section_id = $('#section_id').val();
		//var date = $('#date').val();
    var parent_id = <?php echo $parent_id; ?>
		
 	      $.ajax({
            url: 'admin.php?c=students_attendance_detail&m=get_students_byabsentees',
            type: "POST",
            data:{parent_id:parent_id},
            success:function(res){
 			   $("#students_list_container").html(res);
 			    $("#loader-1").css("display", "none");
 			  }
         });
 });
</script>

<?= $this->endSection() ?>