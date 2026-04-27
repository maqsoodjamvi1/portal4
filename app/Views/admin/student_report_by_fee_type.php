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
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-8">
            <h1>
               Student Fee Report
            </h1>
          </div>
          <div class="col-sm-4">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Terms Session</li>
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
			<?php
			//echo form_open('c=terms_session&m=save', 'role="form" id="user-edit-form"');
			//echo form_hidden('id', $id);
			?>
			<!-- <div class="form-group">
				<select class="form-control" name="session_id" id="session_id">
				<option value="">Select Session</option>
				<?php if(isset($academic_session)){
					foreach($academic_session as $session){ 
				 ?>
				<option value="<?php echo $session->session_id; ?>"><?php echo $session->session_name; ?></option>
				<?php } ?>
				<?php } ?>	
				</select>
			</div> -->
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
		    <div id="termssessionarea" class="termssessionarea">
			</div>	
			  <!-- <div class="form-group">
                <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
				<button type="reset" class="btn btn-default">Reset</button>
				<button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
              </div> -->
            <?php //echo form_close();?>
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

	$('#user-edit-form').validate({
		
	});
	
	
})
</script>

<?= $this->endSection() ?>