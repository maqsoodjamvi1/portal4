<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php 
  $status = ''; 
  if(!empty($_GET['status'])){
   $status = $_GET['status']; 
  }
?>

<!-- Content Header -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2 align-items-center">
      <div class="col-sm-6">
        <h1>Student Names</h1>
      </div>
      <div class="col-sm-6 text-right">
        <ol class="breadcrumb float-sm-right bg-transparent p-0 m-0">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Student Names</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main Content -->
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header pb-0">
        <ul class="nav nav-tabs card-header-tabs">
          <li class="nav-item"><a class="nav-link " href="<?= base_url('admin/addbulkstudents/add') ?>">Student Names</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_enroll') ?>">Enroll Students</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_bulk_cnic') ?>">Father Names</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/studentsbulk') ?>">Fee Detail</a></li>
          <?php if (!empty($campus_info->a_flag)) : ?>
            <li class="nav-item"><a class="nav-link active" href="#/students_bulk_academy_fee">Academy Fee Detail</a></li>
          <?php endif; ?>
          <?php if (!empty($campus_info->h_flag)) : ?>
            <li class="nav-item"><a class="nav-link" href="#/h_student_beds?m=add">Student Bed</a></li>
          <?php endif; ?>
          <?php if (!empty($campus_info->t_flag)) : ?>
            <li class="nav-item"><a class="nav-link" href="#/students_vehicle">Students Vehicle</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_bulk_contacts') ?>">Contact Numbers</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_bulk_info') ?>">Other Student Info</a></li>
          <li class="nav-item"><a class="nav-link" href="#/students?m=addbulk">Entries through Excel</a></li>
        </ul>
      </div>

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
            url: 'admin.php?c=students_bulk_academy_fee&m=data', 
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