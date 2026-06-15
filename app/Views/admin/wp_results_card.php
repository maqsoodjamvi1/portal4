<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
<style>
	.list-group-item{
	  	width: 33% !important;
	    float: left !important;
	    padding: 1px 10px !important;
	    border-end: 0 none;
	    border-start: 0 none;
	}
	table{
	background-color: transparent;
    border: 2px solid #000;
    margin-top: 2px;
    float: left;
	}
	.table-bordered>thead>tr>th, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>tbody>tr>td, .table-bordered>tfoot>tr>td{
		border:1px solid #333;
	}
	.heading2{
   			border:2px solid #000;float:left;width:100%;background:#800000;text-align:center;font-weight:bold;padding: 5px;font-size: 18px;color: #fff;line-height: 20px;
   		}
.heading{
   		border:2px solid #000;float:left;width:100%;text-align:center;font-weight:bold;padding: 5px; background:#800000;font-size: 18px;color: #fff;line-height: 20px;
   		}
	@media print {
		.table-bordered>thead>tr>th, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>tbody>tr>td, .table-bordered>tfoot>tr>td{
		border:1px solid #000;
	}
		body {-webkit-print-color-adjust: exact;}
   		.heading{
   			border:2px solid #000;float:left;width:100%;text-align:center;font-weight:bold;padding: 5px; background:maroon;font-size: 18px;color: #fff;line-height: 20px;background-color: #800000 !important;
        -webkit-print-color-adjust: exact;
   		}
   		.heading2{
   			border:2px solid #000;float:left;width:100%;background:maroon;text-align:center;font-weight:bold;padding: 5px;font-size: 18px;color: #fff;line-height: 20px;background-color: #800000 !important;
        -webkit-print-color-adjust: exact;
   		}

   	.no-print,.nav-tabs,.main-footer,.no-print *
      {
          display: none !important;
      }
  	}
</style>
<?= view('components/page_header', [
    'title' => 'Students Results',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Students Results', 'active' => true],
    ],
]) ?>

<!-- Main content -->
<section class="content"> 
<div class="row">
  <div class="col-lg-12">
     <div class="card card-primary card-outline card-tabs" style="background: #fff !important;">
       	<div class="card-header p-0 pt-1 border-bottom-0">
     	<ul class="nav nav-tabs .no-print">
     	<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/wp_std_weeekly_progress/add') ?>">Add Students Weekly Progress</a></li>		
 			<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/wp_results_card') ?>">View Results Cards</a></li>	
 		</ul>
		<div class="card-body">
		<div class="no-print">
		<div class="col-lg-12">
		<label for="class"><strong>Terms</strong></label><br>
		<ul class="list-group list-group-horizontal">
		<?php foreach ($terms_session_info as $key => $value) { ?>
			<li class="list-group-item">
				<div class="icheck-primary d-inline">
				<input type="checkbox" class="termsession_id" id="termsession_id<?= $value->term_session_id; ?>" name="termsession_id" value="<?= $value->term_session_id; ?>">
				 <label for="termsession_id<?= $value->term_session_id; ?>"><?= $value->term_name; ?></label>
				</div>
			</li>
				<script type="text/javascript">
					var termsession_id = [];
					$('#termsession_id<?php echo $value->term_session_id; ?>').on('click', function() {	

						$(this).each(function(i, e) {
						    termsession_id.push($(this).val());
						}); 
						  $.ajax({
	            url: '/admin/ajax/selectmul-terms-weeks',
	            type: "POST",
	            data:{termsession_id:termsession_id },
	            success:function(res){
			 			   $("#term_weeks").html(res);
			 			 	}
			        });	
					});
				 </script>
		<?php } ?>
		</ul>
			
		</div>
			<div class="row">
					<div class="col-lg-7">
					    <div class="form-group">
		              <label for="class">Term Weeks</label>
		              <div>
		               <select id="term_weeks" name="term_weeks" class="form-control select2" multiple ></select>
		              </div>
			        </div> 
					</div>
					<div class="col-lg-5 form-group">
						<label for="class"><strong>Class</strong></label><br>
							<select class="form-control" name="class_id" id="class_id">
								<option value="">All Classes</option>
							<?php if(isset($classes_info)){
								foreach ($classes_info as  $classvalue) {
							 ?>
							<option value="<?php echo $classvalue->class_id; ?>"><?php echo $classvalue->class_name; ?></option>
							<?php } ?>
							<?php } ?>	
							</select>
					</div>	
			</div>
			
		<div class="col-lg-12"><input style="line-height: 19px;margin: 10px 0px;" type="button" class="btn btn-primary float-end" value="View Result Card " name="View" id="ViewResutlt"></div>
	</div>
		<div id="loader-1" class="overlay col-md-12 text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
	<div id="resultContainer"></div>
    </div>
    </div>
</div>
</div>
</div>
</section>
<!-- /.content -->
<script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){
	$(".select2").select2({closeOnSelect:false});
	$('#ViewResutlt').on('click', function() {	
	$("#loader-1").css("display", "block");	
	var termsession_id = [];
	
	$(".termsession_id:checked").each(function(i, e) {
	    termsession_id.push($(this).val());
	});
	var term_week_id = $('#term_weeks').val(); 
	var class_id = $('#class_id').val();

	$.ajax({
            url: '/admin/wp-results-card/data',
            type: "POST",
            data:{termsession_id:termsession_id,class_id:class_id,term_week_id:term_week_id},
            success:function(res){
 			  // alert(res);		  
			   $("#resultContainer").html(res);
			   $("#loader-1").css("display", "none");
 			}
         });
	});
});
</script>

<?= $this->endSection() ?>