<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
  <!-- Content Header (Page header) -->
  <section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
          Leave Application
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Leave Application</li>
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
				<div class="col-lg-4"> 
					<div class="form-group">
					<!-- <label>Select Status</label> -->
					<select name="leave_status" id="leave_status" class="form-control">
            <option value="">Select Status</option>
						<option value="0">Pending</option>
						<option value="1">Approved</option>
						<option value="2">Rejected</option>
					</select>
					</div>
				</div>	
        <div id="loader-1" class="overlay col-md-12 text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
				<div class="col-lg-12">
					<div id="leaveapplications"></div>
				</div>
        	</div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
      </div>
    </section>
    <!-- /.content -->
<script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){	
	$('#leave_status').on('change', function() {
    $("#loader-1").css("display", "block"); 
  	var id = this.value;
  		$.ajax({
                type: 'POST',
                url: '<?php echo base_url('admin/students_leaves/data'); ?>',
                data:{id: id},
                success: function(data) {
                	$('#leaveapplications').html(data); 
                  $("#loader-1").css("display", "none"); 
                }
            });
	});
});
</script>

<?= $this->endSection() ?>