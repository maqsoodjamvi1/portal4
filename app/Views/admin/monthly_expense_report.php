<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
if(isset($info)){

	$header = 'Edit Monthly Expense Report';
	$id = $info->term_session_id;
	$term_id = $info->term_id;
	$session_id = $info->session_id;
	$start_date = $info->start_date;
	$end_date = $info->end_date;

}else{
	$header = 'Add Monthly Expense Report';
	$id = '';
	$term_id = '';
	$session_id = '';
	$start_date = '';
	$end_date = '';

}
?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-8">
            <h1>
                Monthly Expense Report
            </h1>
          </div>
          <div class="col-sm-4">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Monthly Expense Report</li>
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
			
			 <div class="col-md-12 bg">
		      <div id="loader-1" class="overlay" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		   </div> 
		    <div id="termssessionarea" class="termssessionarea">
			</div>	
			</div>
		  </div>
        </div>
      </div>
  	</div>
  </section>
    <!-- /.content -->
<script type="text/javascript">
    var session_id = $('#session_id').val();
    if(session_id != ''){
        $("#loader-1").css("display", "block");
	     $.ajax({
            url:'<?php echo base_url('admin/monthly_expense_report/data'); ?>', 
            type: "POST",
            data:{session_id:session_id},
            success:function(res){
            $("#termssessionarea").html(res);
 			   		$("#loader-1").css("display", "none");
			 		}
     	});
	 }

</script>

<?= $this->endSection() ?>