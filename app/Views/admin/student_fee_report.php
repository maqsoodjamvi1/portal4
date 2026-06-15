<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
if(isset($info)){

	$header = 'Edit Term Session';
	$id = $info->term_session_id;
	$term_id = $info->term_id;
	$session_id = $info->session_id;
	$start_date = $info->start_date;
	$end_date = $info->end_date;

}else{
	$header = 'Add Term Session';
	$id = '';
	$term_id = '';
	$session_id = '';
	$start_date = '';
	$end_date = '';

}
?>
<style type="text/css">
	.table td, .table th{
		padding: 2px 0px;
    text-align: center;
    vertical-align: middle;
    font-size: 12px;
    border-top: 1px solid #dee2e6;
	}
	tr{border-bottom:2px solid #000;}
</style>
<?= view('components/page_header', [
    'title' => 'Student Fee Report',
    'icon' => 'fas fa-file-invoice-dollar',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Student Fee Report', 'active' => true],
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
			
			<div class="form-group">
				<select class="form-control" name="cls_sec_id" id="cls_sec_id">
					<option value="">Select Class</option>
				<?php if(isset($sectionsclassinfo)){
					foreach ($sectionsclassinfo as  $sectionvalue) {
				 ?>
				<option value="<?php echo $sectionvalue['section_id']; ?>"><?php echo $sectionvalue['sectionclassname']; ?></option>
				<?php } ?>
				<?php } ?>	
				</select>
			</div>
			<div class="col-md-12 bg">
		      <div id="loader-1" class="overlay" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		      </div> 
		   		<div id="termssessionarea" class="termssessionarea"></div>	
			 
					</div>
		  </div>
        </div>
      </div>
  	</div>
  </section>
    <!-- /.content -->
<script type="text/javascript">

// $("#session_id").change(function(){
$("#cls_sec_id").change(function(){
     
       //var session_id = $('#session_id').val();
       var cls_sec_id = $('#cls_sec_id').val();

       if(cls_sec_id != ''){
        $("#loader-1").css("display", "block");
	     $.ajax({
            url:'<?php echo base_url('admin/student_fee_report/data2'); ?>', 
            type: "POST",
            data:{cls_sec_id:cls_sec_id },
            success:function(res){
            	//console.log(res);
 			  		 	$("#termssessionarea").html(res);
 			   			$("#loader-1").css("display", "none");
			 			}
         });
	 }
  });    

$(function(){
 $('.datepicker').datetimepicker({
      format: 'DD/MM/YYYY'
    })
	
})
</script>

<?= $this->endSection() ?>