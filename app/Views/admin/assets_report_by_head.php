<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
if(isset($info)){

	$header = 'Edit Expense Report';
	$id = $info->term_session_id;
	$term_id = $info->term_id;
	$session_id = $info->session_id;
	$start_date = $info->start_date;
	$end_date = $info->end_date;

}else{
	$header = 'Add Expense Report';
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
                Expense Report
            </h1>
          </div>
          <div class="col-sm-4">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active"> Expense Report</li>
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
			 <div class="col-md-12 bg">
		      <div id="loader-1" class="overlay" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		   </div> 
		    <div id="termssessionarea" class="termssessionarea">
			</div>	
			  <!-- <div class="form-group">
                <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
				<button type="reset" class="btn btn-secondary">Reset</button>
				<button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
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

        var session_id = $('#session_id').val();
       if(session_id != ''){
        $("#loader-1").css("display", "block");
	     $.ajax({
            url:'<?php echo base_url('admin/expense_report/data'); ?>', 
            type: "POST",
            data:{session_id:session_id },
            success:function(res){
            	//console.log(res);
 			   $("#termssessionarea").html(res);
 			   $("#loader-1").css("display", "none");
			 }
         });
	 }
   // });    

$(function(){
 $('.datepicker').datetimepicker({
      format: 'DD/MM/YYYY'
    })

	$('#user-edit-form').validate({
		
	});
	
	// $('#user-edit-form').ajaxForm({
	// 	beforeSubmit:function(formData, jqForm, options){
	// 		//return $('#user-edit-form').valid();
	// 		$('#submitBtn').html("Ajax Request is Processing!");
	// 		$('#submitBtn').prop('disabled', true);
	// 	},
	// 	success:function(responseText, statusText, xhr, form){
	// 		var json = $.parseJSON(responseText);
	// 		$('#submitBtn').html("Submit");
	// 		$('#submitBtn').prop('disabled', false);
	// 		if(json.term_weeks_id == false){
	//         	window.location.href = '<?php echo base_url() . $this->config->item('index_page');?>#/term_weeks?m=add';
	//         	return;
	//       	}
	// 		if(json.success){
	// 			toastr.success(json.msg);
	// 			<?php
	// 			if($id == ''){
	// 				?>
	// 				location.href = '#/terms_session';
	// 				<?php
	// 			}else{
	// 				?>
	// 				location.href = '#/terms_session?m=edit&id=<?php echo $id;?>&after=edit';
	// 				<?php
	// 			}
	// 			?>
	// 		}else{
	// 			toastr.error(json.msg);
	// 		}
	// 		return false;
	// 	}
	// });
})
</script>

<?= $this->endSection() ?>