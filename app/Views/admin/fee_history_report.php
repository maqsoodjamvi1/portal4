<?php $uiNeedsDataTables = false; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />
<style type="text/css">
  @media print {
    .bg-danger, .bg-danger>a{color: #000 !important;}
  }
</style>

<?= view('components/page_header', [
    'title' => 'Fee History Report',
    'icon' => 'fas fa-history',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Fee History Report', 'active' => true],
    ],
]) ?>
    <!-- Main content -->
    <section class="content">
      <div class="row">
      <div class="col-lg-12">
     <div class="card sms-card card-primary card-outline card-tabs">
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