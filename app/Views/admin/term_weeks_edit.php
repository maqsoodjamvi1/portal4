<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<?php
if(isset($info)){

	$header = 'Edit Term Week';
	$id = $info->term_weeks_id;
	$term_session_id = $info->term_session_id;
	$week_no = $info->week_no;
	$start_date = $info->start_date;
	$end_date = $info->end_date;
	$week_type = $info->week_type;
	$week_name = $info->week_name;

}else{
	$header = 'Add Term Week';
	$id = '';
	$term_session_id = '';
	$week_no = '';
	$start_date = '';
	$end_date = '';
	$week_type = '';
	$week_name = '';
}
?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-8">
            <h1>
               Terms Week
               <?php if(empty($termweeks_info->term_weeks_id)): ?>
							    <span style='background: green;color: #fff !important;float: right;padding: 5px 10px;margin-top: 0px;
							    font-size: 16px;'>Step 5 Of 12 To Complete System Configuration</span>
							<?php endif; ?>

            </h1>
          </div>
          <div class="col-sm-4">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Terms Week</li>
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
			<ul class="nav nav-tabs">
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/term_weeks') ?>">Term Weeks</a></li>
			<?php if($id == ''){ ?>
				<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/term_weeks/add') ?>"><?php echo $header;?></a></li>
				<?php }else{ ?>
				<li class="nav-item"><a class="nav-link active" href="<?php echo '#/term_weeks?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
		    <?php } ?>
			</ul>
			<div class="card-body">	
			<div class="tab-content">
			<?php
			echo form_open( base_url('admin/term_weeks/save') , 'role="form" id="user-edit-form"');
			echo form_hidden('id', $id);
			?>
                <div class="form-group" style="position: relative;">
                  <label for="term_session">Term Session</label>
				  <select  name="term_session" id="term_session" class="form-control">
				  	<option value="">Select Term Session</option>
				  <?php
				   foreach($terms_session_info as $sessioninfo){
				   ?>
				  <option  value="<?php echo $sessioninfo['term_session_id']; ?>"><?php echo $sessioninfo['name']; ?></option>
				  <?php } ?>
				  </select>
				</div>				
				<div class="col-md-12 bg">
		         <div id="loader-1" class="overlay" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		      </div>
				<div class="termweeksarea" id="termweeksarea"></div> 	

              <div class="form-group">
                <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
				<button type="reset" class="btn btn-default">Reset</button>
				<button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
              </div>
            <?php echo form_close();?>
			</div>
		  </div>
        </div>
      </div>
  </div>
</div>
</section>
<!-- /.content -->
<script type="text/javascript">
$(function(){
	$("#term_session").change(function(){
        var term_session = $('#term_session').val();
        $("#loader-1").css("display", "block");
	     $.ajax({
            url:'<?php echo base_url('admin/term_weeks/generate_term_weeks'); ?>', 
            type: "POST",
            data:{term_session:term_session },
            success:function(res){
            	//console.log(res);
 			   $("#termweeksarea").html(res);
 			   $("#loader-1").css("display", "none");
			 }
         });
    });
	// $('#term_session').select2();
	$('#user-edit-form').validate({
		
	});
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			//return $('#user-edit-form').valid();
			$('#submitBtn').html("Saving");
			$('#submitBtn').prop('disabled', true);
		},
		success:function(responseText, statusText, xhr, form){
			var json = $.parseJSON(responseText);
			$('#submitBtn').html("Save");
			$('#submitBtn').prop('disabled', false);
			if(json.class_id == false){
	        	window.location.href = '<?php echo base_url();?>admin/classes/add';
	        	return;
	      	}
			if(json.success){
				toastr.success(json.msg);
				<?php
				if($id == ''){
					?>
					location.href = '<?php echo base_url(); ?>admin/term_weeks';
					<?php
				}else{
					?>
					location.href = '<?php echo base_url();?>admin/term_weeks/edit/id/<?php echo $id;?>&after=edit';
					<?php
				}
				?>
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});
})
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>