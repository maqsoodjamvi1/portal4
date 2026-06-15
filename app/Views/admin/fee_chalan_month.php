<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<?= view('components/page_header', [
    'title' => 'Fee Chalan by Month',
    'icon' => 'fas fa-calendar',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Fee Chalan by Month', 'active' => true],
    ],
]) ?>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
    <div class="card sms-card card-primary card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
        <div class="card-body">
        <div class="tab-content">
    <div class="row">        
	<div class='col-md-3'>
        <div class="form-group">
		    <label>Fee Month:</label>
            <div class='input-group date1' id='datetimepicker8'>
            <input type='month' class="form-control"  name="fee_month" id="fee_month"  />
            </div>
        </div>
	</div>
	<div class="col-lg-2">
		<div class="form-group">
            <button onclick="gettotalfeebymonth();" class="btn btn-primary btn-flat" style="margin-top: 27px;height: 30px;line-height: 11px;">View</button>
        </div>
	</div>
    <div class="col-lg-12">
	   <div id="totalfeeinfobymonth"></div>
	</div>
</div>
</div>
</div>
<!-- /.box-body -->
</div>
<!-- /.box -->
</div>
</div>
</div>
</section>
<!-- /.content -->
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script src="<?php echo base_url();?>resource/js/jquery.autocomplete.js"></script>
<script type="text/javascript">
function gettotalfeebymonth(){
    var fee_month = $( "#fee_month" ).val();
	  //var fee_type_id = $( "#fee_type_id" ).val();
    //var fee_status = $( "#fee_status" ).val();
  	$.ajax({
            url: 'admin.php?c=fee_chalan_month&m=getTotalfeebymonth',
            type: "POST",
            data:{fee_month: fee_month},
            success:function(res){
			   $("#totalfeeinfobymonth").html(res);
 		  }
    });
}

function gettotalfee(){
	var paid_date_from = $( "#paid_date_from" ).val();
	var paid_date_to = $( "#paid_date_to" ).val();
		$.ajax({
            url: 'admin.php?c=fee_chalan_balance&m=getTotalfee',
            type: "POST",
            data:{paid_date_from: paid_date_from,paid_date_to: paid_date_to },
            success:function(res){
			   $("#totalfeeinfo").html(res);
 		  }
        });
}
</script>
<script type="text/javascript">
$(function(){
	 $('#datetimepicker8').datetimepicker({
		format: 'MM/YYYY',
	});
     //Date range picker
    $('#datepicker').datetimepicker({
       format: 'DD/MM/YYYY',
    });
    $('#datepicker2').datetimepicker({
		  format: 'DD/MM/YYYY',
    });

});
</script>

<?= $this->endSection() ?>