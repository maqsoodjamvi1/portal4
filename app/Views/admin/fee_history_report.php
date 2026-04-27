<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<style type="text/css">
  @media print {
    .bg-danger, .bg-danger>a{color: #000 !important;}
  }
</style>
<!-- Content Header (Page header) --> 
  <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
              Fee History Report
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Fee History Report</li>
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
			<div class="col-lg-12">
				<h4 class="text-center">Fee Payment History</h4>
					<strong>Father Name:</strong>
					<?php   echo $parentsinfo->f_name."&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<strong>Father Contact:</strong>".$parentsinfo->father_contact; ?>
					&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
					<strong>Total Unpaid Amount :</strong> <?php echo $unpaidtotal->total; ?>
					<div id="historysection"></div>
			</div>
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
  <!-- /.content -->
 <script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){
		 $.ajax({
            url:'<?php echo site_url('c=fee_history_report&m=data&parent_id='.$parent_id);?>', 
            type: "POST",
            data:{},
            success:function(res){
            	$("#historysection").html(res);
			  		}
   });
});
</script>

<?= $this->endSection() ?>