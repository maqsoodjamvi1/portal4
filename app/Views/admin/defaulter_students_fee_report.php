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
    font-size: 12px !important;
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
               Defaulter Student Fee Report
            </h1>
          </div>
          <div class="col-sm-4">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Defaulter Student Fee Report</li>
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
				<label for="cls_sec_id">Class / section</label>
				<select class="form-control" name="cls_sec_id" id="cls_sec_id">
					<option value="">— Select class —</option>
					<option value="all">All classes</option>
				<?php if(isset($sectionsclassinfo)){
					foreach ($sectionsclassinfo as  $sectionvalue) {
				 ?>
				<option value="<?php echo esc($sectionvalue['section_id']); ?>"><?php echo esc($sectionvalue['sectionclassname']); ?></option>
				<?php } ?>
				<?php } ?>	
				</select>
				<small class="form-text text-muted">Choose a class or <strong>All classes</strong> to load unpaid fee balances for the current academic session.</small>
			</div>
			 <div class="col-md-12 bg">
		      <div id="loader-1" class="overlay" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		   </div> 
		    <div id="termssessionarea" class="termssessionarea text-muted">
				<p class="mb-0">Select a class above to generate the defaulter report.</p>
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
            url:'<?php echo base_url('admin/defaulter_students_fee_report/data'); ?>', 
            type: "POST",
            data:{cls_sec_id:cls_sec_id },
            success:function(res){
 			   $("#termssessionarea").removeClass('text-muted').html(res);
 			   $("#loader-1").css("display", "none");
			 },
            error: function(xhr, status, err) {
               $("#loader-1").css("display", "none");
               var detail = '';
               if (xhr.responseText && xhr.responseText.length && xhr.responseText.length < 800) {
                  detail = xhr.responseText.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
               }
               if (!detail) {
                  detail = (xhr.status ? ('HTTP ' + xhr.status + ' ' + (xhr.statusText || '')) : '') + (err ? (' — ' + err) : '');
               }
               $("#termssessionarea").html('<div class="alert alert-danger">Could not load report. ' + (detail || 'Unknown error; check the server log.') + '</div>');
            }
         });
	 } else {
        $("#termssessionarea").addClass('text-muted').html('<p class="mb-0">Select a class above to generate the defaulter report.</p>');
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