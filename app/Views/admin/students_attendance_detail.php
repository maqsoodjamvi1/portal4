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

<?= view('components/page_header', [
    'title' => 'Student Attendance Detail',
    'icon' => 'fas fa-info-circle',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Student Attendance', 'url' => base_url('admin/students_attendance')],
        ['label' => 'Detail', 'active' => true],
    ],
]) ?>
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